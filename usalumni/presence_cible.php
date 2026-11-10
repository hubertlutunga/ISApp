<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

drc_boot($pdo);

if (empty($_SESSION['drcalumni_presence_token'])) {
    $_SESSION['drcalumni_presence_token'] = bin2hex(random_bytes(32));
}

$token = trim((string) ($_GET['id'] ?? ''));
$day = drc_resolve_event_day((string) ($_GET['day'] ?? null));
$action = (string) ($_GET['action'] ?? '');
$sessionToken = (string) $_SESSION['drcalumni_presence_token'];
$flash = '';
$error = '';
$application = null;

try {
    if ($token !== '') {
        $application = drc_find_application_by_access_token($pdo, $token);
    }

    if (is_array($application) && (string) ($application['status'] ?? '') !== 'selected') {
        $application = null;
    }

    if ($application === null) {
        $error = 'Participant introuvable ou non sélectionné.';
    } elseif ($action === 'confirm') {
        $submittedSessionToken = (string) ($_GET['token'] ?? '');
        if (!hash_equals($sessionToken, $submittedSessionToken)) {
            $error = 'Session expirée. Veuillez rescanner le QR code.';
        } else {
            $operatorId = drc_public_checkin_operator_id($pdo);
            $checkin = drc_checkin($pdo, (int) $application['id'], $day, $operatorId, 'qr_public');
            $_SESSION['drcalumni_presence_token'] = bin2hex(random_bytes(32));
            $result = $checkin['already'] ? 'exists' : 'ok';
            header('Location: ' . drc_url('presence_cible.php?id=' . rawurlencode($token) . '&day=' . $day . '&result=' . $result));
            exit;
        }
    }

    $result = (string) ($_GET['result'] ?? '');
    if ($result === 'ok') {
        $flash = 'Présence confirmée avec succès pour ' . drc_checkin_day_label($day) . '.';
    } elseif ($result === 'exists') {
        $flash = 'Présence déjà enregistrée pour ' . drc_checkin_day_label($day) . '.';
    }
} catch (Throwable $exception) {
    error_log('[US Alumni Presence Cible] ' . $exception->getMessage());
    $error = 'Impossible de charger la fiche de présence.';
}

$confirmUrl = drc_url('presence_cible.php?id=' . rawurlencode($token) . '&day=' . $day . '&action=confirm&token=' . rawurlencode($sessionToken));
$logoPaths = drc_public_logo_paths();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validation présence | <?php echo drc_h(DRCALUMNI_EVENT_TOPBAR_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/admin.css')); ?>">
</head>
<body>
<main class="content" style="max-width:760px;margin:32px auto;">
    <section class="card">
        <h2 class="section-title" style="margin-top:0;">Validation présence — <?php echo drc_h(drc_checkin_day_label($day)); ?></h2>

        <?php if ($flash !== ''): ?><div class="alert alert-ok"><?php echo drc_h($flash); ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-error"><?php echo drc_h($error); ?></div><?php endif; ?>

        <?php if (is_array($application)): ?>
            <div class="table-wrap" style="margin-bottom:16px;">
                <table style="min-width:0;">
                    <tbody>
                    <tr><th>Référence</th><td><?php echo drc_h((string) $application['reference_code']); ?></td></tr>
                    <tr><th>Nom complet</th><td><?php echo drc_h((string) $application['full_name']); ?></td></tr>
                    <tr><th>E-mail</th><td><?php echo drc_h((string) $application['email']); ?></td></tr>
                    <tr><th>Téléphone</th><td><?php echo drc_h((string) $application['phone']); ?></td></tr>
                    </tbody>
                </table>
            </div>
            <a class="btn btn-primary" href="<?php echo drc_h($confirmUrl); ?>">Confirmer l'accès</a>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
