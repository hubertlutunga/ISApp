<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

drc_boot($pdo);
drc_require_login([DRCALUMNI_ROLE_SUPER_ADMIN, DRCALUMNI_ROLE_REVIEWER, DRCALUMNI_ROLE_CHECKIN]);

$logoPaths = drc_public_logo_paths();
$user = drc_current_user();
assert($user !== null);
$day = drc_resolve_event_day((string) ($_GET['day'] ?? ''));
$message = '';
$error = '';

$token = drc_clean_text((string) ($_GET['token'] ?? ''), 200);
$reference = drc_clean_text((string) ($_GET['reference'] ?? ''), 100);

if ($token !== '' || $reference !== '') {
    $application = null;
    if ($token !== '') {
        $application = drc_find_application_by_access_token($pdo, $token);
    } elseif ($reference !== '') {
        $stmt = $pdo->prepare('SELECT * FROM drcalumni_applications WHERE reference_code = :reference LIMIT 1');
        $stmt->execute([':reference' => $reference]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!is_array($application)) {
        $error = 'QR/référence invalide.';
    } elseif ((string) $application['status'] !== 'selected') {
        $error = 'Ce candidat n’est pas sélectionné.';
    } elseif ((int) ($application['is_revoked'] ?? 0) === 1) {
        $error = 'Ce QR a été révoqué.';
    } else {
        $checkin = drc_checkin($pdo, (int) $application['id'], $day, (int) $user['id'], $token !== '' ? 'qr' : 'manual');
        if ($checkin['ok']) {
            $message = 'Présence confirmée pour ' . drc_checkin_day_label($day) . ' : ' . (string) $application['full_name'];
            drc_audit($pdo, (int) $user['id'], 'checkin.success', 'application', (string) $application['id'], ['day' => $day]);
        } else {
            $error = 'Déjà enregistré aujourd’hui (' . drc_checkin_day_label($day) . ').';
        }
    }
}

$selectedCount = (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_applications WHERE status = 'selected'")->fetchColumn();
$presentDay1 = (int) $pdo->query('SELECT COUNT(*) FROM drcalumni_checkins WHERE event_day = 1')->fetchColumn();
$presentDay2 = (int) $pdo->query('SELECT COUNT(*) FROM drcalumni_checkins WHERE event_day = 2')->fetchColumn();
$recentCheckins = $pdo->query(
    'SELECT c.event_day, c.checkin_at, c.checkin_method, a.reference_code, a.full_name, a.email, u.full_name AS operator_name
     FROM drcalumni_checkins c
     INNER JOIN drcalumni_applications a ON a.id = c.application_id
     INNER JOIN drcalumni_users u ON u.id = c.operator_id
     ORDER BY c.checkin_at DESC
     LIMIT 120'
)->fetchAll(PDO::FETCH_ASSOC);
$swalPayload = null;
if ($error !== '') {
    $swalPayload = [
        'icon' => 'error',
        'title' => 'Scan refusé',
        'text' => $error,
        'confirmButtonColor' => '#002868',
    ];
} elseif ($message !== '') {
    $swalPayload = [
        'icon' => 'success',
        'title' => 'Scan validé',
        'text' => $message,
        'confirmButtonColor' => '#002868',
    ];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan accès | <?php echo drc_h(DRCALUMNI_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/admin.css')); ?>">
</head>
<body>
<header class="admin-topbar">
    <div class="admin-topbar-inner">
        <div class="admin-brand">
            <img class="admin-brand-logo" src="<?php echo drc_h(drc_asset('images/use-kinshasa-flag-logo-vertical-color.png')); ?>" alt="Ambassade des États-Unis à Kinshasa">
            <strong>DRC ALUMNI IMPACT 2026</strong>
        </div>
        <nav class="admin-menu">
            <?php if ($user['role'] !== DRCALUMNI_ROLE_CHECKIN): ?>
                <a href="<?php echo drc_h(drc_url('admin/index.php')); ?>">Acceuil</a>
            <?php endif; ?>
            <?php if ($user['role'] !== DRCALUMNI_ROLE_CHECKIN): ?>
                <a href="<?php echo drc_h(drc_url('admin/presence.php')); ?>">Présence</a>
                <a href="<?php echo drc_h(drc_url('admin/register.php')); ?>">Utilisateurs</a>
                <a href="<?php echo drc_h(drc_url('admin/settings.php')); ?>">Paramètres</a>
            <?php endif; ?>
        </nav>
        <div class="admin-user">
            <span>Connecté: <strong><?php echo drc_h($user['name']); ?></strong></span>
            <a class="btn btn-soft" href="<?php echo drc_h(drc_url('admin/index.php?action=logout')); ?>">Déconnexion</a>
        </div>
    </div>
</header>
<main class="content" data-scan-app>
        <h2 class="section-title">Contrôle d’accès — <?php echo drc_h(drc_checkin_day_label($day)); ?></h2>
        <?php if ($message !== ''): ?><div class="alert alert-ok"><?php echo drc_h($message); ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-error"><?php echo drc_h($error); ?></div><?php endif; ?>

        <section class="stats">
            <article class="card stat"><strong><?php echo $selectedCount; ?></strong><span>Sélectionnés</span></article>
            <article class="card stat"><strong><?php echo $presentDay1; ?></strong><span>Présences jour 1</span></article>
            <article class="card stat"><strong><?php echo $presentDay2; ?></strong><span>Présences jour 2</span></article>
            <article class="card stat"><strong><?php echo max(0, $selectedCount - ($day === 1 ? $presentDay1 : $presentDay2)); ?></strong><span>Restants aujourd’hui</span></article>
        </section>

        <section class="scan-grid">
            <article class="card">
                <h3 style="margin-top:0;">Scanner QR</h3>
                <div class="field">
                    <label for="event_day">Journée</label>
                    <select id="event_day">
                        <option value="1" <?php echo $day === 1 ? 'selected' : ''; ?>>1er décembre 2026</option>
                        <option value="2" <?php echo $day === 2 ? 'selected' : ''; ?>>2 décembre 2026</option>
                    </select>
                </div>
                <div class="field">
                    <button id="scanStart" class="btn btn-primary" type="button">Démarrer le scan</button>
                    <button id="scanStop" class="btn btn-soft" type="button" style="display:none;">Arrêter</button>
                </div>
                <div id="scanStatus" class="small">Caméra arrêtée</div>
                <div id="qr-reader" style="display:none;margin-top:10px;"></div>
            </article>
            <article class="card">
                <h3 style="margin-top:0;">Saisie manuelle de secours</h3>
                <form method="get" action="index.php">
                    <div class="field">
                        <label for="reference">Référence candidat</label>
                        <input id="reference" name="reference" type="text" placeholder="DAI-...">
                    </div>
                    <div class="field">
                        <label for="day">Journée</label>
                        <select id="day" name="day">
                            <option value="1" <?php echo $day === 1 ? 'selected' : ''; ?>>1</option>
                            <option value="2" <?php echo $day === 2 ? 'selected' : ''; ?>>2</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Valider</button>
                </form>
            </article>
        </section>

        <section class="card" style="margin-top:12px;">
            <h3 style="margin-top:0;">Historique récent des présences</h3>
            <div class="table-wrap">
                <table style="min-width:900px;">
                    <thead>
                    <tr><th>Jour</th><th>Date/heure</th><th>Référence</th><th>Nom</th><th>Email</th><th>Méthode</th><th>Opérateur</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentCheckins as $checkin): ?>
                        <tr>
                            <td><?php echo drc_h(drc_checkin_day_label((int) $checkin['event_day'])); ?></td>
                            <td><?php echo drc_h((string) $checkin['checkin_at']); ?></td>
                            <td><?php echo drc_h((string) $checkin['reference_code']); ?></td>
                            <td><?php echo drc_h((string) $checkin['full_name']); ?></td>
                            <td><?php echo drc_h((string) $checkin['email']); ?></td>
                            <td><?php echo drc_h((string) $checkin['checkin_method']); ?></td>
                            <td><?php echo drc_h((string) $checkin['operator_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
</main>
<script src="https://unpkg.com/html5-qrcode"></script>
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
<script src="<?php echo drc_h(drc_asset('assets/js/scan.js')); ?>"></script>
</body>
</html>
