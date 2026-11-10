<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

drc_boot($pdo);
$logoPaths = drc_public_logo_paths();
$csrfKey = 'drcalumni_admin_csrf';
$csrfToken = drc_ensure_csrf($csrfKey);
$statuses = drc_statuses();
$roles = drc_roles();

if ((string) ($_GET['action'] ?? '') === 'logout') {
    drc_logout_user();
    header('Location: ' . drc_url('admin/index.php'));
    exit;
}

$flash = '';
$error = '';

$userCount = (int) $pdo->query('SELECT COUNT(*) FROM drcalumni_users')->fetchColumn();
if ($userCount === 0) {
    header('Location: ' . drc_url('admin/register.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'login') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée.';
    } else {
        $email = drc_normalize_email((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $stmt = $pdo->prepare('SELECT * FROM drcalumni_users WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($user) && password_verify($password, (string) $user['password_hash'])) {
            drc_login_user($user);
            $update = $pdo->prepare('UPDATE drcalumni_users SET last_login_at = :at WHERE id = :id');
            $update->execute([':at' => drc_db_now(), ':id' => (int) $user['id']]);
            drc_audit($pdo, (int) $user['id'], 'auth.login', 'user', (string) $user['id']);
            header('Location: ' . drc_url('admin/index.php'));
            exit;
        }
        $error = 'Identifiants invalides.';
    }
}

if (!drc_is_logged_in()) {
    ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion admin | <?php echo drc_h(DRCALUMNI_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/admin.css')); ?>">
</head>
<body class="admin-login-body">
<div class="login-wrap card login-card">
    <img class="login-logo" src="<?php echo drc_h(drc_asset('images/use-kinshasa-flag-logo-vertical-color-whitetext.png')); ?>" alt="Ambassade des États-Unis à Kinshasa">
    <h1 class="login-title">DRC Almuni</h1>
    <p class="login-subtitle">Connexion sécurisée au back-office</p>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?php echo drc_h($error); ?></div><?php endif; ?>
    <form method="post" action="index.php">
        <input type="hidden" name="form_action" value="login">
        <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
        <div class="field">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" required>
        </div>
        <div class="field">
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" required>
        </div>
        <button class="btn btn-primary login-submit" type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>
<?php
    exit;
}

$currentUser = drc_current_user();
assert($currentUser !== null);

if ($currentUser['role'] === DRCALUMNI_ROLE_CHECKIN) {
    header('Location: ' . drc_url('admin/scan/index.php'));
    exit;
}

function drc_admin_build_selection_mail(PDO $pdo, array $application): array
{
    $subject = drc_setting($pdo, 'selection_subject', 'Confirmation de sélection – U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity') ?? '';
    $template = drc_setting($pdo, 'selection_body', drc_selection_email_text('{name}')) ?? '';
    $text = str_replace(
        ['{name}', '{reference}'],
        [(string) $application['full_name'], (string) $application['reference_code']],
        $template
    );
    $ref = (string) $application['reference_code'];
    $qrCid = 'selection_qr_' . (int) $application['id'];
    $extra = '<hr style="border:none;border-top:1px solid #DBE3EF;margin:20px 0;">
    <h2 style="color:#002868;margin:0 0 10px;">Votre code d’accès</h2>
    <p style="margin:0 0 8px;"><strong>Nom :</strong> ' . drc_h((string) $application['full_name']) . '</p>
    <p style="margin:0 0 8px;"><strong>Référence :</strong> ' . drc_h($ref) . '</p>
    <p style="margin:0 0 8px;">Présentez ce QR code à l’accueil.</p>
    <div style="text-align:center;border:1px dashed #002868;border-radius:12px;padding:12px 10px;background:#fff;">
        <img src="cid:' . drc_h($qrCid) . '" alt="QR code d’accès" style="width:220px;height:220px;max-width:100%;display:inline-block;">
    </div>';
    $html = drc_official_mail_html($subject, $text, $extra);
    return ['subject' => $subject, 'text' => $text, 'html' => $html];
}

function drc_admin_build_rejection_mail(PDO $pdo, array $application): array
{
    $subject = drc_setting($pdo, 'rejection_subject', 'Information relative à votre candidature – U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity') ?? '';
    $template = drc_setting($pdo, 'rejection_body', drc_rejection_email_text('{name}')) ?? '';
    $text = str_replace(
        ['{name}', '{reference}'],
        [(string) $application['full_name'], (string) $application['reference_code']],
        $template
    );
    $html = drc_official_mail_html($subject, $text);
    return ['subject' => $subject, 'text' => $text, 'html' => $html];
}

function drc_admin_selected_count(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_applications WHERE status = 'selected'")->fetchColumn();
}

function drc_admin_update_status(
    PDO $pdo,
    array $currentUser,
    int $applicationId,
    string $status,
    string $decisionNote = '',
    bool $force = false,
    bool $processEmailsNow = true
): string
{
    $allowed = ['pending', 'selected', 'rejected'];
    if (!in_array($status, $allowed, true)) {
        return 'Statut invalide.';
    }

    $query = $pdo->prepare('SELECT * FROM drcalumni_applications WHERE id = :id LIMIT 1');
    $query->execute([':id' => $applicationId]);
    $application = $query->fetch(PDO::FETCH_ASSOC);
    if (!is_array($application)) {
        return 'Candidature introuvable.';
    }

    if ($status === 'selected') {
        $capacity = (int) (drc_setting($pdo, 'selection_capacity', '250') ?? '250');
        $selectedCount = drc_admin_selected_count($pdo);
        if ($application['status'] !== 'selected' && $selectedCount >= $capacity && !($force && $currentUser['role'] === DRCALUMNI_ROLE_SUPER_ADMIN)) {
            return 'Capacité atteinte. Action bloquée.';
        }
    }

    $shouldProcessOutboxNow = false;
    $pdo->beginTransaction();
    try {
        $selectedAt = $status === 'selected' ? drc_db_now() : null;
        $rejectedAt = $status === 'rejected' ? drc_db_now() : null;
        $update = $pdo->prepare(
            'UPDATE drcalumni_applications
             SET status = :status, selected_at = :selected_at, rejected_at = :rejected_at, decision_by = :decision_by, decision_note = :note
             WHERE id = :id'
        );
        $update->execute([
            ':status' => $status,
            ':selected_at' => $selectedAt,
            ':rejected_at' => $rejectedAt,
            ':decision_by' => $currentUser['id'],
            ':note' => $decisionNote !== '' ? $decisionNote : null,
            ':id' => $applicationId,
        ]);

        if ($status === 'selected') {
            $token = drc_generate_access_token();
            drc_store_access_token($pdo, $applicationId, $token);
            drc_qr_png_path($applicationId, $token);
            $mail = drc_admin_build_selection_mail($pdo, $application);
            drc_enqueue_email(
                $pdo,
                $applicationId,
                'selection',
                (string) $application['email'],
                (string) $application['full_name'],
                $mail['subject'],
                $mail['html'],
                $mail['text'],
                'selection_' . $applicationId
            );
            $shouldProcessOutboxNow = true;
        } elseif ($status === 'rejected') {
            $mail = drc_admin_build_rejection_mail($pdo, $application);
            drc_enqueue_email(
                $pdo,
                $applicationId,
                'rejection',
                (string) $application['email'],
                (string) $application['full_name'],
                $mail['subject'],
                $mail['html'],
                $mail['text'],
                'rejection_' . $applicationId
            );
            $shouldProcessOutboxNow = true;
        }

        if ($shouldProcessOutboxNow && $processEmailsNow) {
            $batch = max(1, min(100, (int) (drc_setting($pdo, 'queue_batch_size', '20') ?? '20')));
            drc_process_outbox($pdo, $batch, (int) $currentUser['id']);
        }

        drc_audit($pdo, (int) $currentUser['id'], 'application.status_changed', 'application', (string) $applicationId, ['to' => $status, 'force' => $force]);
        $pdo->commit();
        return 'ok';
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[DRC Alumni Admin Status] ' . $exception->getMessage());
        return "Erreur pendant la mise à jour du statut.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'update_settings') {
    if ($currentUser['role'] !== DRCALUMNI_ROLE_SUPER_ADMIN) {
        $error = 'Accès refusé.';
    } elseif (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée.';
    } else {
        drc_set_setting($pdo, 'selection_capacity', (string) max(1, (int) ($_POST['selection_capacity'] ?? 250)));
        drc_set_setting($pdo, 'queue_batch_size', (string) max(1, min(100, (int) ($_POST['queue_batch_size'] ?? 20))));
        drc_set_setting($pdo, 'rejection_subject', drc_clean_text((string) ($_POST['rejection_subject'] ?? ''), 255));
        drc_set_setting($pdo, 'submission_ack_enabled', (string) ((string) ($_POST['submission_ack_enabled'] ?? '0') === '1' ? '1' : '0'));
        drc_set_setting($pdo, 'submission_ack_subject', drc_clean_text((string) ($_POST['submission_ack_subject'] ?? ''), 255));
        drc_set_setting($pdo, 'submission_ack_body', drc_clean_multiline((string) ($_POST['submission_ack_body'] ?? ''), 4000));
        drc_audit($pdo, (int) $currentUser['id'], 'settings.updated', 'settings', null, null);
        $flash = 'Paramètres enregistrés.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'toggle_registrations') {
    if ($currentUser['role'] !== DRCALUMNI_ROLE_SUPER_ADMIN) {
        $error = 'Accès refusé.';
    } elseif (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'update_application') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée.';
    } else {
        $result = drc_admin_update_status(
            $pdo,
            $currentUser,
            (int) ($_POST['application_id'] ?? 0),
            (string) ($_POST['status'] ?? 'pending'),
            drc_clean_multiline((string) ($_POST['decision_note'] ?? ''), 2000),
            false,
            true
        );
        if ($result === 'ok') {
            $flash = 'Candidature mise à jour.';
        } else {
            $error = $result;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'bulk_update') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée.';
    } else {
        $status = (string) ($_POST['bulk_status'] ?? '');
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['application_ids'] ?? [])), static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            $error = 'Sélectionnez au moins une candidature.';
        } else {
            $okCount = 0;
            $fails = [];
            foreach ($ids as $id) {
                $result = drc_admin_update_status($pdo, $currentUser, $id, $status, '', false, false);
                if ($result === 'ok') {
                    $okCount++;
                } else {
                    $fails[] = "ID {$id}: {$result}";
                }
            }
            $flash = "{$okCount} candidature(s) traitée(s).";
            if ($fails !== []) {
                $error = implode(' ', $fails);
            }
        }
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

$export = (string) ($_GET['export'] ?? '');
if ($export !== '' && in_array($export, ['csv', 'pdf', 'presence_csv', 'presence_pdf'], true)) {
    drc_require_login([DRCALUMNI_ROLE_SUPER_ADMIN, DRCALUMNI_ROLE_REVIEWER]);
    if ($export === 'csv') {
        $rows = $pdo->query('SELECT * FROM drcalumni_applications ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="drcalumni-candidatures.csv"');
        $out = fopen('php://output', 'wb');
        fputcsv($out, ['Référence', 'Nom', 'Email', 'Province', 'Organisation', 'Statut', 'Date']);
        foreach ($rows as $row) {
            fputcsv($out, [
                drc_csv_safe((string) $row['reference_code']),
                drc_csv_safe((string) $row['full_name']),
                drc_csv_safe((string) $row['email']),
                drc_csv_safe((string) $row['province']),
                drc_csv_safe((string) $row['organization_name']),
                drc_csv_safe((string) $row['status']),
                drc_csv_safe((string) $row['created_at']),
            ]);
        }
        fclose($out);
        exit;
    }
    if ($export === 'presence_csv') {
        $rows = $pdo->query('SELECT c.event_day, c.checkin_at, a.reference_code, a.full_name, a.email, a.phone, a.province FROM drcalumni_checkins c INNER JOIN drcalumni_applications a ON a.id = c.application_id ORDER BY c.checkin_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="drcalumni-presences.csv"');
        $out = fopen('php://output', 'wb');
        fputcsv($out, ['Jour', 'Date/heure', 'Référence', 'Nom', 'Email', 'Téléphone', 'Province']);
        foreach ($rows as $row) {
            fputcsv($out, [
                drc_checkin_day_label((int) $row['event_day']),
                drc_csv_safe((string) $row['checkin_at']),
                drc_csv_safe((string) $row['reference_code']),
                drc_csv_safe((string) $row['full_name']),
                drc_csv_safe((string) $row['email']),
                drc_csv_safe((string) $row['phone']),
                drc_csv_safe((string) $row['province']),
            ]);
        }
        fclose($out);
        exit;
    }

    $tcpdfPath = dirname(__DIR__, 2) . '/event/pages/tcpdf/tcpdf.php';
    if (!is_file($tcpdfPath)) {
        $error = 'Export PDF indisponible : bibliothèque TCPDF manquante.';
    } else {
        require_once $tcpdfPath;
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('DRC Alumni');
        $pdf->SetAuthor('DRC Alumni');
        $pdf->SetTitle('Rapport DRC Alumni');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 9);
        if ($export === 'pdf') {
            $rows = $pdo->query('SELECT reference_code, full_name, email, province, status, created_at FROM drcalumni_applications ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
            $html = '<h2>Candidatures DRC Alumni</h2><table border="1" cellpadding="4"><tr><th><b>Réf</b></th><th><b>Nom</b></th><th><b>Email</b></th><th><b>Province</b></th><th><b>Statut</b></th></tr>';
            foreach ($rows as $row) {
                $html .= '<tr><td>' . drc_h((string) $row['reference_code']) . '</td><td>' . drc_h((string) $row['full_name']) . '</td><td>' . drc_h((string) $row['email']) . '</td><td>' . drc_h((string) $row['province']) . '</td><td>' . drc_h((string) $row['status']) . '</td></tr>';
            }
            $html .= '</table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('drcalumni-candidatures.pdf', 'D');
            exit;
        }
        if ($export === 'presence_pdf') {
            $rows = $pdo->query('SELECT c.event_day, c.checkin_at, a.reference_code, a.full_name, a.email FROM drcalumni_checkins c INNER JOIN drcalumni_applications a ON a.id = c.application_id ORDER BY c.checkin_at DESC')->fetchAll(PDO::FETCH_ASSOC);
            $html = '<h2>Présences DRC Alumni</h2><table border="1" cellpadding="4"><tr><th><b>Jour</b></th><th><b>Date/heure</b></th><th><b>Réf</b></th><th><b>Nom</b></th><th><b>Email</b></th></tr>';
            foreach ($rows as $row) {
                $html .= '<tr><td>' . drc_h(drc_checkin_day_label((int) $row['event_day'])) . '</td><td>' . drc_h((string) $row['checkin_at']) . '</td><td>' . drc_h((string) $row['reference_code']) . '</td><td>' . drc_h((string) $row['full_name']) . '</td><td>' . drc_h((string) $row['email']) . '</td></tr>';
            }
            $html .= '</table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('drcalumni-presences.pdf', 'D');
            exit;
        }
    }
}

$query = drc_clean_text((string) ($_GET['q'] ?? ''), 120);
$statusFilter = drc_clean_text((string) ($_GET['status'] ?? ''), 30);
$provinceFilter = drc_clean_text((string) ($_GET['province'] ?? ''), 120);
$programFilter = drc_clean_text((string) ($_GET['program'] ?? ''), 60);
$where = [];
$params = [];

if ($query !== '') {
    $where[] = '(full_name LIKE :q OR email LIKE :q OR phone LIKE :q OR reference_code LIKE :q)';
    $params[':q'] = '%' . $query . '%';
}
if (isset($statuses[$statusFilter])) {
    $where[] = 'status = :status';
    $params[':status'] = $statusFilter;
}
if ($provinceFilter !== '') {
    $where[] = 'province = :province';
    $params[':province'] = $provinceFilter;
}
if ($programFilter !== '') {
    $where[] = 'JSON_SEARCH(programs_json, "one", :programFilter) IS NOT NULL';
    $params[':programFilter'] = $programFilter;
}

$sql = 'SELECT * FROM drcalumni_applications';
if ($where !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC LIMIT 300';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stats = [
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM drcalumni_applications')->fetchColumn(),
    'pending' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_applications WHERE status = 'pending'")->fetchColumn(),
    'selected' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_applications WHERE status = 'selected'")->fetchColumn(),
    'rejected' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_applications WHERE status = 'rejected'")->fetchColumn(),
    'emails_pending' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_email_outbox WHERE status = 'pending'")->fetchColumn(),
    'emails_sent' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_email_outbox WHERE status = 'sent'")->fetchColumn(),
    'emails_failed' => (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_email_outbox WHERE status = 'failed'")->fetchColumn(),
];
$capacity = (int) (drc_setting($pdo, 'selection_capacity', '250') ?? '250');
$remaining = max(0, $capacity - $stats['selected']);
$byProvince = $pdo->query('SELECT province, COUNT(*) AS total FROM drcalumni_applications GROUP BY province ORDER BY total DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
$checkinsByDay = $pdo->query('SELECT event_day, COUNT(*) AS total FROM drcalumni_checkins GROUP BY event_day')->fetchAll(PDO::FETCH_ASSOC);
$outboxRecent = $pdo->query('SELECT mail_type, recipient_email, status, attempts, queued_at, last_error FROM drcalumni_email_outbox ORDER BY id DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
$settings = [
    'registrations_open' => drc_setting($pdo, 'registrations_open', '1'),
    'selection_capacity' => drc_setting($pdo, 'selection_capacity', '250'),
    'queue_batch_size' => drc_setting($pdo, 'queue_batch_size', '20'),
    'rejection_subject' => drc_setting($pdo, 'rejection_subject', ''),
    'submission_ack_enabled' => drc_setting($pdo, 'submission_ack_enabled', '1'),
    'submission_ack_subject' => drc_setting($pdo, 'submission_ack_subject', ''),
    'submission_ack_body' => drc_setting($pdo, 'submission_ack_body', ''),
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
    <title>Back-office | <?php echo drc_h(DRCALUMNI_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/admin.css')); ?>">
</head>
<body>
<header class="admin-topbar">
    <div class="admin-topbar-inner">
        <div class="admin-brand">
            <img class="admin-brand-logo" src="<?php echo drc_h(drc_asset('images/use-kinshasa-flag-logo-vertical-color-whitetext.png')); ?>" alt="Ambassade des États-Unis à Kinshasa">
            <img class="admin-brand-logo-secondary" src="<?php echo drc_h(drc_asset('images/use-kinshasa-seal-color.png')); ?>" alt="U.S. Embassy Kinshasa Seal">
            <strong><?php echo drc_h(DRCALUMNI_EVENT_TOPBAR_NAME); ?></strong>
        </div>
        <nav class="admin-menu">
            <a class="active" href="<?php echo drc_h(drc_url('admin/index.php')); ?>">Acceuil</a>
            <a href="<?php echo drc_h(drc_url('admin/presence.php')); ?>">Présence</a>
            <a href="<?php echo drc_h(drc_url('admin/register.php')); ?>">Utilisateurs</a>
            <a href="<?php echo drc_h(drc_url('admin/settings.php')); ?>">Paramètres</a>
        </nav>
        <div class="admin-user">
            <span>Connecté: <strong><?php echo drc_h($currentUser['name']); ?></strong></span>
            <a class="btn btn-soft" href="<?php echo drc_h(drc_url('admin/index.php?action=logout')); ?>">Déconnexion</a>
        </div>
    </div>
</header>
<main class="content">
        <div class="content-topbar">
            <h2 class="section-title">Tableau de bord</h2>
            <div>
                <a class="btn btn-soft" href="?export=csv">Export CSV</a>
                <a class="btn btn-soft" href="?export=pdf">Export PDF</a>
            </div>
        </div>

        <?php if ($flash !== ''): ?><div class="alert alert-ok"><?php echo drc_h($flash); ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-error"><?php echo drc_h($error); ?></div><?php endif; ?>

        <section class="stats">
            <article class="card stat stat-total"><strong><?php echo $stats['total']; ?></strong><span>Total candidatures</span></article>
            <article class="card stat stat-pending"><strong><?php echo $stats['pending']; ?></strong><span>En attente</span></article>
            <article class="card stat stat-selected"><strong><?php echo $stats['selected']; ?></strong><span>Sélectionnées</span></article>
            <article class="card stat stat-rejected"><strong><?php echo $stats['rejected']; ?></strong><span>Non retenues</span></article>
        </section>

        <section class="card" style="margin-bottom:12px;">
            <h3 style="margin-top:0;">Filtres</h3>
            <form class="filters" method="get" action="index.php">
                <div>
                    <label for="q">Recherche</label>
                    <input id="q" name="q" type="search" value="<?php echo drc_h($query); ?>" placeholder="Nom, e-mail, téléphone, référence">
                </div>
                <div>
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="">Tous</option>
                        <?php foreach ($statuses as $value => $label): ?>
                            <option value="<?php echo drc_h($value); ?>" <?php echo $statusFilter === $value ? 'selected' : ''; ?>><?php echo drc_h($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="province">Province</label>
                    <select id="province" name="province">
                        <option value="">Toutes</option>
                        <?php foreach (drc_provinces() as $province): ?>
                            <option value="<?php echo drc_h($province); ?>" <?php echo $provinceFilter === $province ? 'selected' : ''; ?>><?php echo drc_h($province); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="program">Programme</label>
                    <select id="program" name="program">
                        <option value="">Tous</option>
                        <?php foreach (drc_program_options() as $programKey => $programLabel): ?>
                            <option value="<?php echo drc_h($programKey); ?>" <?php echo $programFilter === $programKey ? 'selected' : ''; ?>><?php echo drc_h($programLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><button class="btn btn-primary" type="submit">Appliquer</button></div>
            </form>
        </section>

        <section class="card" style="margin-bottom:12px;">
            <h3 style="margin-top:0;">Liste des candidatures & actions par lot</h3>
            <form id="bulk-update-form" method="post" action="index.php" style="margin-bottom:10px;">
                <input type="hidden" name="form_action" value="bulk_update">
                <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
                <div class="bulk-actions-inline">
                    <div class="bulk-status-field">
                        <label for="bulk_status">Nouveau statut</label>
                        <select id="bulk_status" name="bulk_status" required>
                            <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?php echo drc_h($value); ?>"><?php echo drc_h($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Appliquer aux candidatures cochées</button>
                </div>
            </form>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th></th>
                        <th>Référence / candidat</th>
                        <th>Q1–Q10</th>
                        <th>Q11–Q20</th>
                        <th>Décision</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($applications === []): ?>
                        <tr><td colspan="6">Aucune candidature.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($applications as $application): ?>
                        <?php
                        $status = (string) $application['status'];
                        $badgeClass = $status === 'selected' ? 'badge-selected' : ($status === 'rejected' ? 'badge-rejected' : 'badge-pending');
                        $sectors = drc_selected_labels(drc_sector_options(), drc_json_decode_array((string) $application['sectors_json']), (string) ($application['sectors_other'] ?? ''));
                        $programs = drc_selected_labels(drc_program_options(), drc_json_decode_array((string) $application['programs_json']), (string) ($application['programs_other'] ?? ''));
                        $themes = drc_selected_labels(drc_theme_options(), drc_json_decode_array((string) $application['summit_themes_json']));
                        ?>
                        <tr class="<?php echo $status === 'selected' ? 'candidate-row-selected' : ''; ?>">
                            <td><input type="checkbox" name="application_ids[]" value="<?php echo (int) $application['id']; ?>" form="bulk-update-form"></td>
                            <td>
                                <strong><?php echo drc_h((string) $application['reference_code']); ?></strong><br>
                                <?php echo drc_h((string) $application['full_name']); ?><br>
                                <span class="small"><?php echo drc_h((string) $application['email']); ?> · <?php echo drc_h((string) $application['phone']); ?></span><br>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo drc_h($statuses[$status] ?? $status); ?></span><br>
                                <button type="button" class="btn btn-soft candidate-toggle" data-expanded="0">Voir plus</button>
                            </td>
                            <td class="small">
                                <div class="candidate-preview">Réponses masquées. Cliquez sur « Voir plus ».</div>
                                <div class="candidate-full" hidden>
                                    <strong>Province:</strong> <?php echo drc_h((string) $application['province']); ?><br>
                                    <strong>Ville:</strong> <?php echo drc_h((string) $application['city_territory']); ?><br>
                                    <strong>Organisation:</strong> <?php echo drc_h((string) $application['organization_name']); ?><br>
                                    <strong>Q6:</strong> <?php echo drc_h(implode(', ', $sectors)); ?><br>
                                    <strong>Q7:</strong> <?php echo nl2br(drc_h((string) $application['responsibilities'])); ?><br>
                                    <strong>Q8:</strong> <?php echo drc_h(implode(', ', $programs)); ?><br>
                                    <strong>Q9:</strong> <?php echo (int) $application['latest_exchange_year']; ?><br>
                                    <strong>Q10:</strong> <?php echo drc_h(implode(', ', $themes)); ?>
                                </div>
                            </td>
                            <td class="small">
                                <div class="candidate-preview">Réponses masquées. Cliquez sur « Voir plus ».</div>
                                <div class="candidate-full" hidden>
                                    <strong>Q11:</strong> <?php echo nl2br(drc_h((string) $application['cooperation_domains'])); ?><br>
                                    <strong>Q12:</strong> <?php echo nl2br(drc_h((string) $application['washington_accords_importance'])); ?><br>
                                    <strong>Q13:</strong> <?php echo nl2br(drc_h((string) $application['summit_expectations'])); ?><br>
                                    <strong>Q14:</strong> <?php echo nl2br(drc_h((string) $application['sharable_expertise'])); ?><br>
                                    <strong>Q15/Q16:</strong> <?php echo (int) $application['wants_workgroup'] === 1 ? 'Oui - ' . drc_h((string) $application['workgroup_choice']) : 'Non'; ?><br>
                                    <strong>Q17:</strong> <?php echo (int) $application['wants_expo'] === 1 ? 'Oui' : 'Non'; ?><br>
                                    <?php if ((int) $application['wants_expo'] === 1): ?>
                                        <strong>Expo:</strong> <?php echo drc_h((string) $application['expo_entity_name']); ?><br>
                                        <strong>Description:</strong> <?php echo nl2br(drc_h((string) $application['expo_description'])); ?><br>
                                        <strong>Lien:</strong> <?php echo drc_h((string) $application['expo_link']); ?><br>
                                    <?php endif; ?>
                                    <strong>Q18:</strong> <?php echo drc_h(drc_availability_options()[(string) $application['availability']] ?? (string) $application['availability']); ?><br>
                                    <div class="sensitive">
                                        <strong>Q19:</strong> <?php echo (int) $application['needs_accommodation'] === 1 ? 'Oui - ' . drc_h((string) $application['accommodation_details']) : 'Non'; ?>
                                    </div>
                                    <strong>Q20:</strong> <?php echo nl2br(drc_h((string) $application['additional_comments'])); ?><br>
                                    <strong>Déclarations:</strong> <?php echo (int) $application['declaration_truth'] === 1 && (int) $application['declaration_understanding'] === 1 ? 'Validées' : 'Incomplètes'; ?>
                                </div>
                                
                            </td>
                            <td class="small">
                                <strong>Décision:</strong> <?php echo drc_h((string) ($application['decision_note'] ?? '')); ?><br>
                                <strong>Sélection:</strong> <?php echo drc_h((string) ($application['selected_at'] ?? '—')); ?><br>
                                <strong>Rejet:</strong> <?php echo drc_h((string) ($application['rejected_at'] ?? '—')); ?>
                            </td>
                            <td>
                                <form method="post" action="index.php">
                                    <input type="hidden" name="form_action" value="update_application">
                                    <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
                                    <input type="hidden" name="application_id" value="<?php echo (int) $application['id']; ?>">
                                    <select name="status">
                                        <?php foreach ($statuses as $statusValue => $statusLabel): ?>
                                            <option value="<?php echo drc_h($statusValue); ?>" <?php echo $statusValue === $status ? 'selected' : ''; ?>><?php echo drc_h($statusLabel); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="decision-note-wrap" data-decision-note-wrap <?php echo in_array($status, ['selected', 'rejected'], true) ? '' : 'hidden'; ?>>
                                        <textarea name="decision_note" placeholder="Note interne"><?php echo drc_h((string) ($application['decision_note'] ?? '')); ?></textarea>
                                    </div>
                                    <button class="btn btn-primary update-action-btn" type="submit">Mettre à jour</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid-2">
            <article class="card">
                <h3 style="margin-top:0;">Répartition par province</h3>
                <?php foreach ($byProvince as $row): ?>
                    <div class="small"><?php echo drc_h((string) $row['province']); ?>: <?php echo (int) $row['total']; ?></div>
                <?php endforeach; ?>
            </article>
            <article class="card">
                <h3 style="margin-top:0;">Présences par journée</h3>
                <?php foreach ($checkinsByDay as $row): ?>
                    <div class="small"><?php echo drc_h(drc_checkin_day_label((int) $row['event_day'])); ?>: <?php echo (int) $row['total']; ?></div>
                <?php endforeach; ?>
            </article>
        </section>

        <footer class="admin-footer">
            <div class="admin-footer-logos">
                <img src="<?php echo drc_h(drc_asset($logoPaths['footer_freedom'])); ?>" alt="Freedom 250">
                <img src="<?php echo drc_h(drc_asset($logoPaths['footer_seal'])); ?>" alt="U.S. Embassy Kinshasa Seal">
            </div>
            <p>Section des Affaires publiques · Ambassade des États-Unis à Kinshasa · Designed by Invitation Speciale</p>
        </footer>
</main>
<script>
document.addEventListener('click', function (event) {
    const toggle = event.target.closest('.candidate-toggle');
    if (!toggle) return;

    const row = toggle.closest('tr');
    if (!row) return;

    const details = row.querySelectorAll('.candidate-full');
    const expanded = toggle.getAttribute('data-expanded') === '1';

    details.forEach((detail) => {
        detail.hidden = expanded;
    });

    toggle.setAttribute('data-expanded', expanded ? '0' : '1');
    toggle.textContent = expanded ? 'Voir plus' : 'Voir moins';
});

document.querySelectorAll('form[action="index.php"] select[name="status"]').forEach((select) => {
    const updateNoteVisibility = () => {
        const form = select.closest('form');
        if (!form) return;
        const noteWrap = form.querySelector('[data-decision-note-wrap]');
        if (!noteWrap) return;
        const visible = select.value === 'selected' || select.value === 'rejected';
        noteWrap.hidden = !visible;
    };

    select.addEventListener('change', updateNoteVisibility);
    updateNoteVisibility();
});
</script>
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
