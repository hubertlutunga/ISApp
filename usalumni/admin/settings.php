<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

drc_boot($pdo);
drc_require_login([DRCALUMNI_ROLE_SUPER_ADMIN]);

$logoPaths = drc_public_logo_paths();
$csrfKey = 'drcalumni_admin_settings_csrf';
$csrfToken = drc_ensure_csrf($csrfKey);
$currentUser = drc_current_user();
assert($currentUser !== null);

$flash = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'toggle_registrations') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée.';
    } else {
        $targetState = (string) ($_POST['target_state'] ?? '') === 'open' ? '1' : '0';
        drc_set_setting($pdo, 'registrations_open', $targetState);
        drc_audit(
            $pdo,
            (int) $currentUser['id'],
            'registrations.' . ($targetState === '1' ? 'opened' : 'closed'),
            'settings',
            null,
            null
        );
        $flash = $targetState === '1'
            ? 'Les candidatures ont été ré-ouvertes.'
            : 'Les candidatures ont été clôturées.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'process_emails') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée.';
    } else {
        $batch = max(1, min(100, (int) (drc_setting($pdo, 'queue_batch_size', '20') ?? '20')));
        $result = drc_process_outbox($pdo, $batch, (int) $currentUser['id']);
        $flash = "Outbox traitée: {$result['processed']} message(s), {$result['sent']} envoyé(s), {$result['failed']} échec(s).";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'smtp_test') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée.';
    } else {
        $testEmail = drc_normalize_email((string) ($_POST['smtp_test_email'] ?? ''));
        if ($testEmail === '' || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail de test invalide.';
        } else {
            $subject = 'Test SMTP — U.S. Alumni Impact 2026';
            $text = "Bonjour,\n\nCe message confirme que la configuration SMTP US Alumni fonctionne.\n\nDate: " . date('Y-m-d H:i:s');
            $result = drc_send_email_transport(
                $testEmail,
                $testEmail,
                $subject,
                drc_official_mail_html($subject, $text),
                $text
            );

            if ($result['success']) {
                $flash = "E-mail de test envoyé à {$testEmail}.";
            } else {
                $error = 'Échec test SMTP: ' . (string) ($result['message'] ?? 'erreur inconnue');
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'update_settings') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée.';
    } else {
        drc_set_setting($pdo, 'selection_capacity', (string) max(1, (int) ($_POST['selection_capacity'] ?? 250)));
        drc_set_setting($pdo, 'queue_batch_size', (string) max(1, min(100, (int) ($_POST['queue_batch_size'] ?? 20))));
        drc_set_setting($pdo, 'submission_ack_enabled', (string) ((string) ($_POST['submission_ack_enabled'] ?? '0') === '1' ? '1' : '0'));
        drc_set_setting($pdo, 'submission_ack_subject', drc_clean_text((string) ($_POST['submission_ack_subject'] ?? ''), 255));
        drc_set_setting($pdo, 'submission_ack_body', drc_clean_multiline((string) ($_POST['submission_ack_body'] ?? ''), 6000));
        drc_set_setting($pdo, 'selection_subject', drc_clean_text((string) ($_POST['selection_subject'] ?? ''), 255));
        drc_set_setting($pdo, 'selection_body', drc_clean_multiline((string) ($_POST['selection_body'] ?? ''), 6000));
        drc_set_setting($pdo, 'rejection_subject', drc_clean_text((string) ($_POST['rejection_subject'] ?? ''), 255));
        drc_set_setting($pdo, 'rejection_body', drc_clean_multiline((string) ($_POST['rejection_body'] ?? ''), 6000));
        drc_audit($pdo, (int) $currentUser['id'], 'settings.updated', 'settings', null, null);
        $flash = 'Paramètres enregistrés.';
    }
}

$stats = [
    'emails_pending' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_email_outbox WHERE status = 'pending'")->fetchColumn(),
    'emails_sent' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_email_outbox WHERE status = 'sent'")->fetchColumn(),
    'emails_failed' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_email_outbox WHERE status = 'failed'")->fetchColumn(),
];
$outboxRecent = $pdo->query('SELECT mail_type, recipient_email, status, attempts, queued_at, last_error FROM drcalumni_email_outbox ORDER BY id DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
$smtpTransport = drc_mailer_transport();
$smtpDebug = [
    'host' => (string) ($smtpTransport['host'] ?? ''),
    'port' => (string) ($smtpTransport['port'] ?? ''),
    'user' => (string) ($smtpTransport['user'] ?? ''),
    'encryption' => (string) ($smtpTransport['encryption'] ?? ''),
    'password_configured' => trim((string) ($smtpTransport['password'] ?? '')) !== '' ? 'oui' : 'non',
];

$selectionDefaultSubject = 'Confirmation de sélection – U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity';
$rejectionDefaultSubject = 'Information relative à votre candidature – U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity';
$submissionAckDefaultBody = "Bonjour {name},\n\nNous confirmons la réception de votre candidature à U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity.\n\nRéférence: {reference}\n\nVotre dossier est en cours d’examen. Cette confirmation de dépôt ne signifie pas une sélection.\n\nCordialement,\nSection des Affaires publiques\nAmbassade des États-Unis à Kinshasa";
$settings = [
    'registrations_open' => drc_setting($pdo, 'registrations_open', '1'),
    'selection_capacity' => drc_setting($pdo, 'selection_capacity', '250'),
    'queue_batch_size' => drc_setting($pdo, 'queue_batch_size', '20'),
    'submission_ack_enabled' => drc_setting($pdo, 'submission_ack_enabled', '1'),
    'submission_ack_subject' => drc_setting($pdo, 'submission_ack_subject', 'Accusé de réception – U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity'),
    'submission_ack_body' => drc_setting($pdo, 'submission_ack_body', $submissionAckDefaultBody),
    'selection_subject' => drc_setting($pdo, 'selection_subject', $selectionDefaultSubject),
    'selection_body' => drc_setting($pdo, 'selection_body', drc_selection_email_text('{name}')),
    'rejection_subject' => drc_setting($pdo, 'rejection_subject', $rejectionDefaultSubject),
    'rejection_body' => drc_setting($pdo, 'rejection_body', drc_rejection_email_text('{name}')),
];
$registrationsOpen = $settings['registrations_open'] === '1';

$swalPayload = null;
if ($error !== '') {
    $swalPayload = [
        'icon' => 'error',
        'title' => 'Action échouée',
        'text' => $error,
        'confirmButtonColor' => '#002868',
    ];
} elseif ($flash !== '') {
    $swalPayload = [
        'icon' => 'success',
        'title' => 'Action réussie',
        'text' => $flash,
        'confirmButtonColor' => '#002868',
    ];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paramètres | <?php echo drc_h(DRCALUMNI_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/admin.css')); ?>">
</head>
<body>
<header class="admin-topbar">
    <div class="admin-topbar-inner">
        <div class="admin-brand">
            <img class="admin-brand-logo" src="<?php echo drc_h(drc_asset('images/use-kinshasa-flag-logo-vertical-color.png')); ?>" alt="Ambassade des États-Unis à Kinshasa">
            <strong>U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity</strong>
        </div>
        <nav class="admin-menu">
            <a href="<?php echo drc_h(drc_url('admin/index.php')); ?>">Acceuil</a>
            <a href="<?php echo drc_h(drc_url('admin/presence.php')); ?>">Présence</a>
            <a href="<?php echo drc_h(drc_url('admin/register.php')); ?>">Utilisateurs</a>
            <a class="active" href="<?php echo drc_h(drc_url('admin/settings.php')); ?>">Paramètres</a>
        </nav>
        <div class="admin-user">
            <span>Connecté: <strong><?php echo drc_h((string) $currentUser['name']); ?></strong></span>
            <a class="btn btn-soft" href="<?php echo drc_h(drc_url('admin/index.php?action=logout')); ?>">Déconnexion</a>
        </div>
    </div>
</header>
<main class="content">
    <div class="content-topbar">
        <h2 class="section-title">Paramètres</h2>
    </div>

    <section class="card" style="display:flex;gap:16px;align-items:center;justify-content:space-between;flex-wrap:wrap;margin-bottom:12px;">
        <div>
            <div style="font-weight:900;font-size:18px;line-height:1.3;margin-bottom:8px;">Formulaire de candidature</div>
            <div class="small" style="font-weight:800;">
                État actuel :
                <span class="badge" style="margin-left:6px;background:<?php echo $registrationsOpen ? '#dcfce7' : '#ffe5e3'; ?>;color:<?php echo $registrationsOpen ? '#166534' : '#b42318'; ?>;">
                    <?php echo $registrationsOpen ? 'Ouvert' : 'Clôturé'; ?>
                </span>
            </div>
        </div>
        <form method="post" action="settings.php" style="margin:0;display:flex;align-items:center;gap:10px;">
            <input type="hidden" name="form_action" value="toggle_registrations">
            <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
            <input type="hidden" name="target_state" value="<?php echo $registrationsOpen ? 'closed' : 'open'; ?>">
            <button class="btn <?php echo $registrationsOpen ? 'btn-soft' : 'btn-primary'; ?>" type="submit">
                <?php echo $registrationsOpen ? 'Clôturer les candidatures' : 'Ré-ouvrir les candidatures'; ?>
            </button>
        </form>
    </section>

    <section class="card" style="margin-bottom:12px;">
        <h3 style="margin-top:0;">E-mails outbox</h3>
        <div class="grid-2">
            <div>
                <strong>État de la file</strong><br>
                <span class="small">En attente: <?php echo $stats['emails_pending']; ?> · Envoyés: <?php echo $stats['emails_sent']; ?> · Échoués: <?php echo $stats['emails_failed']; ?></span>
            </div>
            <div>
                <strong>Traitement</strong><br>
                <span class="small">Le traitement enverra les e-mails selon la taille de lot configurée.</span>
            </div>
        </div>
        <form method="post" action="settings.php" style="margin-top:10px;">
            <input type="hidden" name="form_action" value="process_emails">
            <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
            <button class="btn btn-primary" type="submit">Traiter la file d’e-mails</button>
        </form>
        <div style="margin-top:14px;padding-top:10px;border-top:1px solid #dbe3ef;">
            <strong>Diagnostic SMTP actif</strong>
            <div class="small" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;margin-top:6px;">
                <span><strong>Host:</strong> <?php echo drc_h($smtpDebug['host']); ?></span>
                <span><strong>Port:</strong> <?php echo drc_h($smtpDebug['port']); ?></span>
                <span><strong>User:</strong> <?php echo drc_h($smtpDebug['user']); ?></span>
                <span><strong>Encryption:</strong> <?php echo drc_h($smtpDebug['encryption']); ?></span>
                <span><strong>Mot de passe:</strong> <?php echo drc_h($smtpDebug['password_configured']); ?></span>
            </div>
            <form method="post" action="settings.php" style="margin-top:10px;display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
                <input type="hidden" name="form_action" value="smtp_test">
                <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
                <div style="min-width:260px;flex:1;">
                    <label for="smtp_test_email">E-mail de test</label>
                    <input id="smtp_test_email" name="smtp_test_email" type="email" required placeholder="vous@example.com">
                </div>
                <button class="btn btn-soft" type="submit">Envoyer un test SMTP</button>
            </form>
        </div>
    </section>

    <section class="card" style="margin-bottom:12px;">
        <h3 style="margin-top:0;">Paramètres</h3>
        <form method="post" action="settings.php">
            <input type="hidden" name="form_action" value="update_settings">
            <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
            <div class="grid-2">
                <div>
                    <label for="selection_capacity">Capacité de sélection</label>
                    <input id="selection_capacity" name="selection_capacity" type="number" min="1" value="<?php echo drc_h((string) $settings['selection_capacity']); ?>">
                </div>
                <div>
                    <label for="queue_batch_size">Taille de lot e-mail</label>
                    <input id="queue_batch_size" name="queue_batch_size" type="number" min="1" max="100" value="<?php echo drc_h((string) $settings['queue_batch_size']); ?>">
                </div>
                <div>
                    <label><input type="checkbox" name="submission_ack_enabled" value="1" <?php echo $settings['submission_ack_enabled'] === '1' ? 'checked' : ''; ?>> Envoyer l’accusé de réception</label>
                </div>
                <div class="field">
                    <label for="submission_ack_subject">Objet accusé de réception</label>
                    <input id="submission_ack_subject" name="submission_ack_subject" type="text" value="<?php echo drc_h((string) $settings['submission_ack_subject']); ?>">
                </div>
                <div class="field">
                    <label for="selection_subject">Objet e-mail de sélection</label>
                    <input id="selection_subject" name="selection_subject" type="text" value="<?php echo drc_h((string) $settings['selection_subject']); ?>">
                </div>
                <div class="field">
                    <label for="rejection_subject">Objet e-mail de non-sélection</label>
                    <input id="rejection_subject" name="rejection_subject" type="text" value="<?php echo drc_h((string) $settings['rejection_subject']); ?>">
                </div>
            </div>
            <div class="field">
                <label for="submission_ack_body">Texte accusé de réception ({name}, {reference})</label>
                <textarea id="submission_ack_body" name="submission_ack_body"><?php echo drc_h((string) $settings['submission_ack_body']); ?></textarea>
            </div>
            <div class="field">
                <label for="selection_body">Texte e-mail de sélection ({name}, {reference})</label>
                <textarea id="selection_body" name="selection_body"><?php echo drc_h((string) $settings['selection_body']); ?></textarea>
            </div>
            <div class="field">
                <label for="rejection_body">Texte e-mail de non-sélection ({name}, {reference})</label>
                <textarea id="rejection_body" name="rejection_body"><?php echo drc_h((string) $settings['rejection_body']); ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </form>
    </section>

    <section class="card" style="margin-top:12px;">
        <h3 style="margin-top:0;">File d’envoi (20 derniers)</h3>
        <div class="table-wrap">
            <table style="min-width:0;">
                <thead>
                    <tr><th>Type</th><th>Destinataire</th><th>Statut</th><th>Tentatives</th><th>Date</th><th>Erreur</th></tr>
                </thead>
                <tbody>
                <?php foreach ($outboxRecent as $mail): ?>
                    <tr>
                        <td><?php echo drc_h((string) $mail['mail_type']); ?></td>
                        <td><?php echo drc_h((string) $mail['recipient_email']); ?></td>
                        <td><?php echo drc_h((string) $mail['status']); ?></td>
                        <td><?php echo (int) $mail['attempts']; ?></td>
                        <td><?php echo drc_h((string) $mail['queued_at']); ?></td>
                        <td><?php echo drc_h((string) ($mail['last_error'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src="<?php echo drc_h(drc_asset('sweet/sweetalert2.all.min.js')); ?>" onerror="this.onerror=null;this.src='/sweet/sweetalert2.all.min.js';"></script>
<script>if (!window.Swal) { document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\\/script>'); }</script>
<?php if ($swalPayload !== null): ?>
<script>
window.addEventListener('DOMContentLoaded', function () {
    if (!window.Swal) return;
    Swal.fire(<?php echo json_encode($swalPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
});
</script>
<?php endif; ?>
</body>
</html>
