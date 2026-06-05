<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Kinshasa');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/presence_support.php';

if (empty($_SESSION['cbomoko_presence_token'])) {
    $_SESSION['cbomoko_presence_token'] = bin2hex(random_bytes(32));
}

$identifier = trim((string) ($_GET['id'] ?? ''));
$flash = '';
$error = '';
$participant = null;
$accessCode = cbp_access_code();
$token = (string) $_SESSION['cbomoko_presence_token'];

try {
    cbp_ensure_presence_schema($pdo);

    if ($identifier !== '' && (string) ($_GET['action'] ?? '') === 'confirm') {
        $submittedToken = (string) ($_GET['token'] ?? '');
        if (!hash_equals($token, $submittedToken)) {
            $error = 'Session expirée. Veuillez réessayer.';
        } else {
            $participantToConfirm = cbp_find_confirmed_participant_by_identifier($pdo, $identifier);
            if ($participantToConfirm === null) {
                $error = 'Participant introuvable ou non confirmé.';
            } else {
                cbp_mark_access_confirmed($pdo, (int) $participantToConfirm['id']);
                $_SESSION['cbomoko_presence_token'] = bin2hex(random_bytes(32));
                header('Location: presence_cible.php?id=' . rawurlencode($identifier) . '&acces=ok');
                exit;
            }
        }
    }

    $participant = $identifier !== '' ? cbp_find_confirmed_participant_by_identifier($pdo, $identifier) : null;
    if ($participant === null && $error === '') {
        $error = 'Participant introuvable ou non confirmé.';
    }

    if ((string) ($_GET['acces'] ?? '') === 'ok') {
        $flash = 'Accès confirmé avec succès.';
    }
} catch (Throwable $exception) {
    error_log('[Creators Bomoko Accès] ' . $exception->getMessage());
    $error = 'Impossible de charger la fiche participant.';
}

$isPresent = is_array($participant) && (string) ($participant['acces'] ?? '') === 'oui';
$arrivalTime = is_array($participant) ? cbp_format_datetime((string) ($participant['heure_arrive'] ?? '')) : '';
$confirmUrl = 'presence_cible.php?id=' . rawurlencode($identifier) . '&action=confirm&token=' . rawurlencode($token);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accès participant | <?php echo cbp_h(CBOMOKO_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="images/favicom.png">
    <style>
        :root{--wood:#8b4a1f;--wood-dark:#35180b;--blue:#0a3a73;--cyan:#00a6a6;--paper:#fffaf1;--ink:#142033;--muted:#64748b;--line:#ead8bd;--ok:#047857;--danger:#b42318;--shadow:0 24px 70px rgba(53,24,11,.14)}
        *{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:linear-gradient(145deg,#fffaf1,#eef7f8 55%,#fff);color:var(--ink)}a{color:inherit}.hero{padding:22px clamp(18px,4vw,52px);background:linear-gradient(135deg,var(--wood-dark),var(--wood) 55%,var(--blue));color:#fff}.hero-inner{width:min(980px,100%);margin:0 auto;display:flex;align-items:center;justify-content:center;gap:18px}.brand{display:flex;align-items:center;justify-content:center;gap:14px;text-align:left}.brand img{width:72px;height:72px;object-fit:contain}.brand-title{font-weight:950;font-size:clamp(22px,3vw,36px);letter-spacing:-.05em}.brand-sub{color:#fff4df;font-weight:850;letter-spacing:.08em;text-transform:uppercase;font-size:12px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:999px;padding:13px 20px;font-weight:950;text-decoration:none;cursor:pointer}.btn-primary{background:linear-gradient(135deg,#ef4444,var(--wood),var(--blue));color:#fff;box-shadow:0 16px 36px rgba(139,74,31,.28)}.btn-disabled{background:#e2e8f0;color:#64748b;cursor:not-allowed}.shell{width:min(980px,100%);margin:0 auto;padding:28px clamp(18px,4vw,52px) 58px}.card{background:rgba(255,250,241,.94);border:1px solid rgba(139,74,31,.14);border-radius:30px;box-shadow:var(--shadow);padding:clamp(22px,4vw,36px)}.profile-head{display:grid;grid-template-columns:1fr auto;gap:18px;align-items:start;border-bottom:1px solid var(--line);padding-bottom:22px;margin-bottom:22px}.profile-head h1{margin:0;font-size:clamp(34px,5vw,60px);letter-spacing:-.07em;line-height:.96}.muted{color:var(--muted);font-weight:750}.badge{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:9px 14px;font-weight:950;font-size:12px;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}.badge-ok{background:#dcfce7;color:#047857}.badge-wait{background:#fff7ed;color:#c2410c}.info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:24px}.info{padding:16px;border-radius:20px;background:#fff;border:1px solid var(--line)}.info span{display:block;color:var(--muted);font-weight:850;font-size:12px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:7px}.info strong{font-size:17px}.answers{display:grid;gap:12px;margin:22px 0}.answer{padding:16px;border-radius:20px;background:#f8fafc;border:1px solid #dbe4ee}.answer span{display:block;color:var(--muted);font-weight:850;font-size:12px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:7px}.actions{display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;margin-top:24px}.alert{padding:14px 16px;border-radius:18px;margin-bottom:18px;font-weight:850}.alert-error{background:#fff1f0;color:var(--danger);border:1px solid #ffccc7}.alert-ok{background:#ecfdf5;color:var(--ok);border:1px solid #a7f3d0}.footer{margin-top:28px;padding:18px 10px;text-align:center;color:var(--muted);font-weight:400;display:grid;justify-items:center;gap:12px}.footer-separator{width:100%;border:0;border-top:1px solid var(--line);margin:0 0 4px}.footer__logos{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap}.footer__logos img{width:86px;height:64px;object-fit:contain}.footer__logos img.is-footer-logo{width:150px}@media(max-width:720px){.hero-inner,.profile-head{display:grid}.info-grid{grid-template-columns:1fr}.actions .btn{width:100%}.footer__logos img.is-footer-logo{width:130px}}
    </style>
</head>
<body>
<header class="hero">
    <div class="hero-inner">
        <div class="brand" aria-label="Contrôle d’accès Creators Bomoko">
            <img src="images/Logo_cbomoko_White.png" alt="Creators Bomoko">
            <span>
                <span class="brand-title">Contrôle d’accès</span><br>
                <span class="brand-sub"><?php echo cbp_h(CBOMOKO_EVENT_NAME); ?></span>
            </span>
        </div>
    </div>
</header>

<main class="shell">
    <?php if ($flash !== ''): ?><div class="alert alert-ok"><?php echo cbp_h($flash); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?php echo cbp_h($error); ?></div><?php endif; ?>

    <?php if (is_array($participant)): ?>
        <section class="card">
            <div class="profile-head">
                <div>
                    <h1><?php echo cbp_h((string) $participant['nom_complet']); ?></h1>
                    <div class="muted">Réf. <?php echo cbp_h((string) $participant['submission_id']); ?></div>
                </div>
                <span class="badge <?php echo $isPresent ? 'badge-ok' : 'badge-wait'; ?>">
                    <?php echo $isPresent ? 'Accès confirmé' : 'À confirmer'; ?>
                </span>
            </div>

            <div class="info-grid">
                <div class="info"><span>E-mail</span><strong><?php echo cbp_h((string) $participant['email']); ?></strong></div>
                <div class="info"><span>Téléphone</span><strong><?php echo cbp_h((string) $participant['telephone']); ?></strong></div>
                <div class="info"><span>Ville</span><strong><?php echo cbp_h((string) $participant['ville']); ?></strong></div>
                <div class="info"><span>Âge</span><strong><?php echo (int) $participant['age']; ?> ans</strong></div>
                <div class="info"><span>Profession</span><strong><?php echo cbp_h((string) $participant['profession']); ?></strong></div>
                <div class="info"><span>Organisation</span><strong><?php echo cbp_h((string) ($participant['organisation'] ?: '—')); ?></strong></div>
                <div class="info"><span>Domaine</span><strong><?php echo cbp_h((string) $participant['domaine']); ?></strong></div>
                <div class="info"><span>Plateformes</span><strong><?php echo cbp_h((string) $participant['plateformes']); ?></strong></div>
            </div>

            <div class="answers">
                <div class="answer"><span>Motivation</span><?php echo nl2br(cbp_h((string) $participant['motivation'])); ?></div>
                <div class="answer"><span>Thématique</span><?php echo cbp_h((string) $participant['thematique']); ?></div>
                <div class="answer"><span>Besoins spécifiques</span><?php echo cbp_h((string) $participant['besoins_specifiques']); ?></div>
                <?php if ($arrivalTime !== ''): ?>
                    <div class="answer"><span>Heure d’arrivée</span><?php echo cbp_h($arrivalTime); ?></div>
                <?php endif; ?>
            </div>

            <div class="actions">
                <?php if ($isPresent): ?>
                    <button class="btn btn-disabled" type="button" disabled>Accès déjà confirmé</button>
                <?php else: ?>
                    <a class="btn btn-primary" href="#" onclick="confirmAcces(event)">✓ Confirmer l'accès</a>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <footer class="footer">
        <hr class="footer-separator">
        <div>©2026 Creators Bomoko powered by U.S Embassy Kinshasa · Designed by Hubert Solutions</div>
    </footer>
</main>

<script src="/sweet/sweetalert2.all.min.js"></script>
<script>
if (!window.Swal) {
    document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
}
</script>
<script>
function confirmAcces(event) {
    event.preventDefault();

    const confirmationCode = <?php echo json_encode($accessCode, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

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
                Swal.showValidationMessage("Veuillez entrer la clé de confirmation.");
                return false;
            }
            return val;
        }
    }).then((result) => {
        if (!result.isConfirmed) return;

        const cleSaisie = result.value;

        if (cleSaisie !== confirmationCode) {
            Swal.fire({
                icon: "error",
                title: "Code incorrect",
                text: "Le code de confirmation ne correspond pas au code de l'événement."
            });
            return;
        }

        window.location.href = <?php echo json_encode($confirmUrl, JSON_UNESCAPED_SLASHES); ?>;
    });
}
</script>
</body>
</html>
