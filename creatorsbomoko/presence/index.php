<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Kinshasa');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__) . '/presence_support.php';

if (empty($_SESSION['cbomoko_presence_token'])) {
    $_SESSION['cbomoko_presence_token'] = bin2hex(random_bytes(32));
}

$flash = '';
$error = '';
$participants = [];

try {
    cbp_ensure_presence_schema($pdo);

    $participants = cbp_confirmed_participants($pdo);

    if ((string) ($_GET['acces'] ?? '') === 'ok') {
        $flash = 'Accès confirmé avec succès.';
    }
} catch (Throwable $exception) {
    error_log('[Creators Bomoko Presence Page] ' . $exception->getMessage());
    $error = 'Impossible de charger la page de présence pour le moment.';
}

$total = count($participants);
$present = count(array_filter($participants, static fn (array $participant): bool => (string) ($participant['acces'] ?? '') === 'oui'));
$absent = max(0, $total - $present);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Présence | <?php echo cbp_h(CBOMOKO_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="../images/favicom.png">
    <style>
        :root{--wood:#8b4a1f;--wood-dark:#35180b;--blue:#0a3a73;--cyan:#00a6a6;--paper:#fffaf1;--ink:#162339;--muted:#64748b;--line:#ead8bd;--ok:#047857;--ok-bg:#dcfce7;--warn:#c2410c;--warn-bg:#fff7ed;--danger:#b42318;--shadow:0 24px 70px rgba(53,24,11,.14)}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:linear-gradient(145deg,#fffaf1,#eef7f8 55%,#fff);color:var(--ink)}
        a{color:inherit}
        .hero{padding:26px clamp(14px,4vw,46px);background:linear-gradient(135deg,var(--wood-dark),var(--wood) 54%,var(--blue));color:#fff}
        .hero-inner{width:min(1240px,100%);margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .hero-logo{display:grid;gap:8px;text-decoration:none}
        .hero-logo img{width:min(200px,76vw);height:auto;object-fit:contain;background:linear-gradient(180deg,#fff,#fffaf1);border-radius:24px;box-shadow:0 18px 46px rgba(0,0,0,.24)}
        .is-logo{width:min(200px,52vw);height:auto;object-fit:contain;filter:drop-shadow(0 10px 24px rgba(0,0,0,.22))}
        .shell{width:min(1240px,100%);margin:0 auto;padding:24px clamp(14px,4vw,46px) 56px}
        .page-title{margin:2px 0 14px;text-align:center;font-size:clamp(26px,4vw,40px);letter-spacing:-.05em;color:var(--wood-dark)}
        .alert{padding:14px 16px;border-radius:18px;margin-bottom:16px;font-weight:850}
        .alert-error{background:#fff1f0;color:var(--danger);border:1px solid #ffccc7}
        .alert-ok{background:#ecfdf5;color:var(--ok);border:1px solid #a7f3d0}
        .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px}
        .stat{position:relative;overflow:hidden;border-radius:24px;padding:18px;color:#fff;box-shadow:var(--shadow)}
        .stat:after{content:"";position:absolute;right:-24px;top:-30px;width:88px;height:88px;border-radius:999px;background:rgba(255,255,255,.16)}
        .stat strong{display:block;font-size:clamp(34px,4vw,46px);line-height:1;letter-spacing:-.08em}
        .stat span{display:block;margin-top:8px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;font-size:12px;color:rgba(255,255,255,.88)}
        .stat-total{background:linear-gradient(145deg,#0f4c81,#0f9ca8)}
        .stat-present{background:linear-gradient(145deg,#047857,#10b981)}
        .stat-absent{background:linear-gradient(145deg,#b45309,#f59e0b)}
        .qr-card,.table-card,.details-card{background:rgba(255,250,241,.94);border:1px solid rgba(139,74,31,.14);border-radius:26px;box-shadow:var(--shadow)}
        .qr-card{padding:18px;margin-bottom:16px}
        .qr-head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:10px}
        .qr-title{font-size:20px;font-weight:950;letter-spacing:-.03em}
        .qr-note{color:var(--muted);font-weight:750;font-size:14px}
        .qr-controls{display:flex;gap:10px;flex-wrap:wrap}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:999px;padding:11px 16px;font-weight:900;cursor:pointer;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,#ef4444,var(--wood),var(--blue));color:#fff}
        .btn-soft{background:#f3dfc2;color:var(--wood-dark)}
        #qrStatus{margin:8px 0 0;color:var(--muted);font-weight:800}
        #qr-reader{max-width:440px;margin:12px auto 0;display:none;border-radius:20px;overflow:hidden;border:1px solid #e2e8f0;background:#fff;box-shadow:0 16px 28px rgba(15,23,42,.08)}
        .table-card{padding:18px;overflow:hidden}
        .table-head{display:flex;align-items:end;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:12px}
        .table-head h2{margin:0;font-size:26px;letter-spacing:-.05em}
        .muted{color:var(--muted);font-weight:750}
        .search-box{margin-bottom:12px}
        .search-input{width:100%;height:48px;border:1px solid #d9c5a8;border-radius:14px;background:#fff;padding:0 14px;font:inherit;color:var(--ink)}
        .search-input:focus{outline:none;border-color:#b66a28;box-shadow:0 0 0 4px rgba(182,106,40,.14)}
        .table-wrap{overflow:auto;border:1px solid var(--line);border-radius:18px;background:#fff}
        table{width:100%;border-collapse:collapse;min-width:800px}
        th,td{padding:13px;border-bottom:1px solid #f1e1cc;text-align:left;vertical-align:middle}
        th{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#745139;background:#fff8ed}
        tr.row-present{background:rgba(220,252,231,.62)}
        tr.row-present:hover{background:rgba(187,247,208,.72)}
        tr:hover{background:rgba(255,248,237,.62)}
        .name-link{text-decoration:none;font-weight:900;color:#0f172a}
        .name-link:hover{text-decoration:underline}
        .name-link.is-present{color:#166534}
        .click-row{cursor:pointer}
        .footer{margin-top:26px;padding:18px 10px;text-align:center;color:var(--muted);font-weight:400;display:grid;justify-items:center;gap:12px}
        .footer-separator{width:100%;border:0;border-top:1px solid var(--line);margin:0 0 4px}
        @media(max-width:900px){.stats{grid-template-columns:1fr}table{min-width:620px}}
        @media(max-width:680px){.hero-inner{justify-content:center}.table-card,.qr-card{padding:14px}}
    </style>
</head>
<body>
<header class="hero">
    <div class="hero-inner">
        <a class="hero-logo" href="/creatorsbomoko/presence/" aria-label="Retour à la page présence">
            <img src="../images/CB Horizontal Dark BG.png" alt="Creators Bomoko">
        </a>
        <img class="is-logo" src="../../event/images/Logo_invitationSpeciale_4.png" alt="Invitation Spéciale">
    </div>
</header>

<main class="shell">
    <?php if ($flash !== ''): ?><div class="alert alert-ok"><?php echo cbp_h($flash); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?php echo cbp_h($error); ?></div><?php endif; ?>
    <h2 class="page-title">Contrôle de présence</h2>

    <section class="stats" aria-label="Statistiques">
        <div class="stat stat-total"><strong><?php echo $total; ?></strong><span>Confirmés</span></div>
        <div class="stat stat-present"><strong><?php echo $present; ?></strong><span>Présents</span></div>
        <div class="stat stat-absent"><strong><?php echo $absent; ?></strong><span>Absents</span></div>
    </section>

    <section class="qr-card" aria-label="Scanner QR">
        <div class="qr-head">
            <div>
                <div class="qr-title">Scanner le QR Code</div>
                <div class="qr-note">Le scan ouvre directement la fiche du participant.</div>
            </div>
            <div class="qr-controls">
                <button type="button" id="btnStartQr" class="btn btn-soft">Scanner</button>
                <button type="button" id="btnStopQr" class="btn btn-soft" style="display:none;">Arrêter</button>
            </div>
        </div>
        <div id="qrStatus">Caméra arrêtée</div>
        <div id="qr-reader"></div>
    </section>

    <section class="table-card" aria-label="Tableau des participants confirmés">
        <div class="table-head">
            <h2>Liste des confirmés</h2>
            <div class="muted">Cliquer sur une ligne pour ouvrir la fiche complète.</div>
        </div>
        <div class="search-box">
            <input id="participant-search" class="search-input" type="search" placeholder="Rechercher un nom ou un profil..." aria-label="Rechercher un participant">
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Profil</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($participants === []): ?>
                    <tr><td colspan="2">Aucun participant confirmé pour le moment.</td></tr>
                <?php endif; ?>
                <?php foreach ($participants as $participant): ?>
                    <?php
                    $identifier = trim((string) ($participant['submission_id'] ?? '')) !== ''
                        ? (string) $participant['submission_id']
                        : (string) ((int) ($participant['id'] ?? 0));
                    $isPresent = (string) ($participant['acces'] ?? '') === 'oui';
                    $targetUrl = '../presence_cible.php?id=' . rawurlencode($identifier);
                    ?>
                    <tr class="click-row <?php echo $isPresent ? 'row-present' : ''; ?>" data-href="<?php echo cbp_h($targetUrl); ?>" tabindex="0" role="link">
                        <td>
                            <a class="name-link <?php echo $isPresent ? 'is-present' : ''; ?>" href="<?php echo cbp_h($targetUrl); ?>">
                                <?php echo cbp_h((string) $participant['nom_complet']); ?>
                            </a>
                        </td>
                        <td><?php echo cbp_h((string) $participant['profession']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="footer">
        <hr class="footer-separator">
        <div>©2026 Creators Bomoko powered by U.S Embassy Kinshasa · Designed by Hubert Solutions</div>
    </footer>
</main>

<script src="/sweet/sweetalert2.all.min.js"></script>
<script>
if (!window.Swal) {
    document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\\/script>');
}
</script>
<script src="https://unpkg.com/html5-qrcode" onerror="window.__qrLibFailed = true;"></script>
<script>
(() => {
    const qrReader = document.getElementById('qr-reader');
    const btnStart = document.getElementById('btnStartQr');
    const btnStop = document.getElementById('btnStopQr');
    const status = document.getElementById('qrStatus');

    let qr = null;
    let isRunning = false;

    const setStatus = (text) => {
        if (status) status.textContent = text;
    };

    const extractIdentifier = (decodedText) => {
        const text = (decodedText || '').trim();
        if (!text) return '';

        if (/^https?:\/\//i.test(text)) {
            try {
                const url = new URL(text);
                const fromId = (url.searchParams.get('id') || '').trim();
                if (fromId !== '') return fromId;
            } catch (error) {
                return '';
            }
        }

        if (/^[A-Za-z0-9._-]+$/.test(text)) {
            return text;
        }

        return '';
    };

    const ensureQrLibrary = async () => {
        if (window.Html5Qrcode) {
            return true;
        }

        if (window.__qrLibFailed) {
            const fallbackScript = document.createElement('script');
            fallbackScript.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
            fallbackScript.async = true;
            document.head.appendChild(fallbackScript);

            await new Promise((resolve) => {
                fallbackScript.onload = resolve;
                fallbackScript.onerror = resolve;
            });
        }

        return !!window.Html5Qrcode;
    };

    const startWithCamera = async (cameraConfig, config) => {
        await qr.start(
            cameraConfig,
            config,
            async (decodedText) => {
                if (!isRunning) return;
                isRunning = false;

                const identifier = extractIdentifier(decodedText);
                if (!identifier) {
                    setStatus('QR détecté mais identifiant invalide.');
                    return;
                }

                setStatus('QR détecté. Ouverture de la fiche...');
                await stopQr();
                window.location.href = '../presence_cible.php?id=' + encodeURIComponent(identifier);
            },
            () => {}
        );
    };

    const startQr = async () => {
        try {
            const isReady = await ensureQrLibrary();
            if (!isReady) {
                setStatus('Librairie QR non chargée.');
                return;
            }

            if (!qr) qr = new Html5Qrcode('qr-reader');

            qrReader.style.display = 'block';
            btnStart.style.display = 'none';
            btnStop.style.display = 'inline-flex';
            setStatus('Ouverture caméra...');

            const config = { fps: 10, qrbox: { width: 260, height: 260 } };
            isRunning = true;

            try {
                await startWithCamera({ facingMode: { exact: 'environment' } }, config);
            } catch (environmentError) {
                const cameras = await Html5Qrcode.getCameras();
                const backCam = cameras.length
                    ? (cameras.find((camera) => /back|rear|environment|traseira|arriere/i.test(camera.label)) || cameras[0])
                    : null;

                if (backCam) {
                    try {
                        await startWithCamera({ deviceId: { exact: backCam.id } }, config);
                    } catch (deviceError) {
                        await startWithCamera({ facingMode: 'environment' }, config);
                    }
                } else {
                    await startWithCamera({ facingMode: 'environment' }, config);
                }
            }

            setStatus('Caméra active. Scannez un QR...');
        } catch (error) {
            console.error(error);
            setStatus('Caméra refusée ou indisponible.');
            await stopQr();
        }
    };

    const stopQr = async () => {
        try {
            if (qr) {
                await qr.stop();
                await qr.clear();
            }
        } catch (error) {}

        isRunning = false;
        qrReader.style.display = 'none';
        btnStart.style.display = 'inline-flex';
        btnStop.style.display = 'none';
        setStatus('Caméra arrêtée');
    };

    btnStart?.addEventListener('click', startQr);
    btnStop?.addEventListener('click', stopQr);

    const searchInput = document.getElementById('participant-search');
    const rows = Array.from(document.querySelectorAll('tbody tr.click-row'));

    searchInput?.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        rows.forEach((row) => {
            const haystack = row.textContent.toLowerCase();
            row.style.display = query === '' || haystack.includes(query) ? '' : 'none';
        });
    });

    rows.forEach((row) => {
        const target = row.getAttribute('data-href');
        if (!target) {
            return;
        }

        row.addEventListener('click', (event) => {
            const anchor = event.target.closest('a');
            if (anchor) {
                return;
            }
            window.location.href = target;
        });

        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                window.location.href = target;
            }
        });
    });
})();
</script>
</body>
</html>
