<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

drc_boot($pdo);

if (empty($_SESSION['drcalumni_presence_token'])) {
    $_SESSION['drcalumni_presence_token'] = bin2hex(random_bytes(32));
}

$identifier = trim((string) ($_GET['id'] ?? ''));
$day = drc_resolve_event_day((string) ($_GET['day'] ?? null));
$action = (string) ($_GET['action'] ?? '');
$sessionToken = (string) $_SESSION['drcalumni_presence_token'];
$flash = '';
$error = '';
$application = null;
$accessCode = '026';

try {
    if ($identifier !== '') {
        $application = drc_find_application_by_access_token($pdo, $identifier);

        if (!is_array($application)) {
            $byReference = $pdo->prepare('SELECT * FROM drcalumni_applications WHERE reference_code = :reference LIMIT 1');
            $byReference->execute([':reference' => $identifier]);
            $candidate = $byReference->fetch(PDO::FETCH_ASSOC);
            if (is_array($candidate)) {
                $application = $candidate;
            }
        }
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
            header('Location: ' . drc_url('presence.php?day=' . $day . '&result=' . $result));
            exit;
        }
    }
} catch (Throwable $exception) {
    error_log('[US Alumni Presence Cible] ' . $exception->getMessage());
    $error = 'Impossible de charger la fiche de présence.';
}

$confirmUrl = drc_url('presence_cible.php?id=' . rawurlencode($identifier) . '&day=' . $day . '&action=confirm&token=' . rawurlencode($sessionToken));
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
<header class="admin-topbar">
    <div class="admin-topbar-inner" style="justify-content:center;">
        <img class="admin-brand-logo-solo" src="<?php echo drc_h(drc_asset('images/Logo_invitationSpeciale_SF.png')); ?>" alt="Invitation Spéciale">
    </div>
</header>
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
            <div style="display:flex;justify-content:center;margin-top:8px;">
                <a class="btn btn-primary" style="min-width:280px;text-align:center;padding:13px 18px;font-size:15px;" href="#" onclick="confirmAcces(event)">
                    ✓ Confirmer l'accès <?php echo drc_h(drc_checkin_day_label($day)); ?>
                </a>
            </div>
        <?php endif; ?>
    </section>
    <footer class="admin-footer">
        <div class="admin-footer-logos">
            <img src="<?php echo drc_h(drc_asset($logoPaths['footer_freedom'])); ?>" alt="Freedom 250">
            <img src="<?php echo drc_h(drc_asset($logoPaths['footer_seal'])); ?>" alt="U.S. Embassy Kinshasa Seal">
        </div>
        <p>Section des Affaires publiques · Ambassade des États-Unis à Kinshasa · Designed by Invitation Speciale</p>
    </footer>
</main>

<script src="<?php echo drc_h(drc_asset('sweet/sweetalert2.all.min.js')); ?>" onerror="this.onerror=null;this.src='/sweet/sweetalert2.all.min.js';"></script>
<script>if (!window.Swal) { document.write('<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"><\\/script>'); }</script>
<script>
function confirmAcces(event) {
    event.preventDefault();
    const confirmationCode = <?php echo json_encode($accessCode, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    Swal.fire({
        title: "Confirmer l’accès",
        html: `
        <div style="text-align:left;margin-top:10px;">
            <label style="display:block;margin-bottom:6px;">Code de confirmation</label>
            <input id="cleConfirm" type="text" class="swal2-input"
                placeholder="Entrez le code"
                autocomplete="off"
                style="margin:0;width:100%;">
        </div>
        `,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Confirmer",
        cancelButtonText: "Annuler",
        focusConfirm: false,
        preConfirm: () => {
            const val = document.getElementById('cleConfirm').value.trim();
            if (!val) {
                Swal.showValidationMessage("Veuillez entrer le code de confirmation.");
                return false;
            }
            return val;
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        if (result.value !== confirmationCode) {
            Swal.fire({
                icon: "error",
                title: "Code incorrect",
                text: "Le code de confirmation ne correspond pas."
            });
            return;
        }
        window.location.href = <?php echo json_encode($confirmUrl, JSON_UNESCAPED_SLASHES); ?>;
    });
}
</script>
</body>
</html>
