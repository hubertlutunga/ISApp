<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

drc_boot($pdo);

$activeDay = drc_resolve_event_day((string) ($_GET['day'] ?? null));
$flash = '';
$error = '';
$participants = [];

try {
    $stmt = $pdo->query(
        'SELECT
            a.id,
            a.reference_code,
            a.full_name,
            a.email,
            a.phone,
            a.province,
            MAX(CASE WHEN c.event_day = 1 THEN c.checkin_at END) AS day1_checkin_at,
            MAX(CASE WHEN c.event_day = 2 THEN c.checkin_at END) AS day2_checkin_at
        FROM drcalumni_applications a
        LEFT JOIN drcalumni_checkins c ON c.application_id = a.id
        WHERE a.status = "selected"
        GROUP BY a.id, a.reference_code, a.full_name, a.email, a.phone, a.province
        ORDER BY a.full_name ASC'
    );
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = (string) ($_GET['result'] ?? '');
    if ($result === 'ok') {
        $flash = 'Accès confirmé avec succès pour ' . drc_checkin_day_label($activeDay) . '.';
    } elseif ($result === 'exists') {
        $flash = 'Présence déjà enregistrée pour ' . drc_checkin_day_label($activeDay) . '.';
    }
} catch (Throwable $exception) {
    error_log('[US Alumni Presence Page] ' . $exception->getMessage());
    $error = 'Impossible de charger la page de présence.';
}

$total = count($participants);
$present = 0;
foreach ($participants as $participant) {
    $presentAt = $activeDay === 1 ? (string) ($participant['day1_checkin_at'] ?? '') : (string) ($participant['day2_checkin_at'] ?? '');
    if ($presentAt !== '') {
        $present++;
    }
}
$absent = max(0, $total - $present);
$logoPaths = drc_public_logo_paths();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Présence | <?php echo drc_h(DRCALUMNI_EVENT_TOPBAR_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/admin.css')); ?>">
    <style>
        .presence-shell{max-width:1200px;margin:0 auto}
        .presence-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px}
        .presence-stat{padding:14px;border:1px solid #dbe3ef;border-radius:14px;background:#fff}
        .presence-stat strong{display:block;font-size:28px;line-height:1}
        .presence-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:12px}
        .presence-search{max-width:360px;width:100%}
        .presence-search input{height:44px}
        #qr-reader{max-width:430px;margin:10px auto 0;display:none;border-radius:14px;overflow:hidden;border:1px solid #dbe3ef;background:#fff}
        @media (max-width: 900px){.presence-stats{grid-template-columns:1fr}}
    </style>
</head>
<body>
<header class="admin-topbar">
    <div class="admin-topbar-inner" style="justify-content:center;">
        <img class="admin-brand-logo-solo" src="<?php echo drc_h(drc_asset('images/Logo_invitationSpeciale_SF.png')); ?>" alt="Invitation Spéciale">
    </div>
</header>
<main class="content presence-shell">
    <div class="content-topbar">
        <h2 class="section-title">Présence participants sélectionnés</h2>
    </div>

    <?php if ($flash !== ''): ?><div class="alert alert-ok"><?php echo drc_h($flash); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?php echo drc_h($error); ?></div><?php endif; ?>

    <div class="day-switch" style="display:flex;gap:10px;margin-bottom:12px;">
        <a class="btn <?php echo $activeDay === 1 ? 'btn-primary' : 'btn-soft'; ?>" href="<?php echo drc_h(drc_url('presence.php?day=1')); ?>">Jour 1</a>
        <a class="btn <?php echo $activeDay === 2 ? 'btn-primary' : 'btn-soft'; ?>" href="<?php echo drc_h(drc_url('presence.php?day=2')); ?>">Jour 2</a>
    </div>

    <section class="presence-stats">
        <article class="presence-stat"><strong><?php echo $total; ?></strong><span>Total sélectionnés</span></article>
        <article class="presence-stat"><strong><?php echo $present; ?></strong><span>Présents <?php echo drc_h(drc_checkin_day_label($activeDay)); ?></span></article>
        <article class="presence-stat"><strong><?php echo $absent; ?></strong><span>Absents <?php echo drc_h(drc_checkin_day_label($activeDay)); ?></span></article>
    </section>

    <section class="card" style="margin-bottom:12px;">
        <div class="presence-tools">
            <div>
                <strong>Scan QR</strong><br>
                <span class="small">Scannez puis confirmez l’accès avec le code 026.</span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button id="btnStartQr" class="btn btn-primary" type="button">Démarrer caméra</button>
                <button id="btnStopQr" class="btn btn-soft" type="button" style="display:none;">Arrêter</button>
            </div>
        </div>
        <p id="qrStatus" class="small">Caméra arrêtée.</p>
        <div id="qr-reader"></div>
    </section>

    <section class="card">
        <div class="presence-tools">
            <h3 style="margin:0;">Liste des participants sélectionnés</h3>
            <div class="presence-search">
                <input id="participant-search" type="search" placeholder="Rechercher nom, prénom, email, référence..." />
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Référence</th>
                    <th>Nom complet</th>
                    <th>E-mail</th>
                    <th>Téléphone</th>
                    <th>Province</th>
                    <th>Présence J1</th>
                    <th>Présence J2</th>
                </tr>
                </thead>
                <tbody id="participants-body">
                <?php foreach ($participants as $participant): ?>
                    <?php
                    $day1 = (string) ($participant['day1_checkin_at'] ?? '');
                    $day2 = (string) ($participant['day2_checkin_at'] ?? '');
                    $identifier = (string) ($participant['reference_code'] ?? '');
                    $profileUrl = drc_url('presence_cible.php?id=' . rawurlencode($identifier) . '&day=' . $activeDay);
                    ?>
                    <tr class="click-row" data-href="<?php echo drc_h($profileUrl); ?>" tabindex="0">
                        <td><?php echo drc_h((string) $participant['reference_code']); ?></td>
                        <td><?php echo drc_h((string) $participant['full_name']); ?></td>
                        <td><?php echo drc_h((string) $participant['email']); ?></td>
                        <td><?php echo drc_h((string) $participant['phone']); ?></td>
                        <td><?php echo drc_h((string) $participant['province']); ?></td>
                        <td><?php echo drc_h($day1 !== '' ? $day1 : '—'); ?></td>
                        <td><?php echo drc_h($day2 !== '' ? $day2 : '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <footer class="admin-footer">
        <div class="admin-footer-logos">
            <img src="<?php echo drc_h(drc_asset($logoPaths['footer_freedom'])); ?>" alt="Freedom 250">
            <img src="<?php echo drc_h(drc_asset($logoPaths['footer_seal'])); ?>" alt="U.S. Embassy Kinshasa Seal">
        </div>
        <p>Section des Affaires publiques · Ambassade des États-Unis à Kinshasa · Designed by Invitation Speciale</p>
    </footer>
</main>

<script src="https://unpkg.com/html5-qrcode" onerror="window.__qrLibFailed = true;"></script>
<script>
(() => {
    const activeDay = <?php echo (int) $activeDay; ?>;
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
                window.location.href = <?php echo json_encode(drc_url('presence_cible.php?id='), JSON_UNESCAPED_SLASHES); ?> + encodeURIComponent(identifier) + '&day=' + encodeURIComponent(activeDay);
            },
            () => {}
        );
    };

    const startQr = async () => {
        if (!window.Html5Qrcode) {
            setStatus('Librairie QR non chargée.');
            return;
        }
        try {
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
                await startWithCamera({ facingMode: 'environment' }, config);
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
    const rows = Array.from(document.querySelectorAll('#participants-body tr.click-row'));
    searchInput?.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        rows.forEach((row) => {
            const haystack = row.textContent.toLowerCase();
            row.style.display = query === '' || haystack.includes(query) ? '' : 'none';
        });
    });

    rows.forEach((row) => {
        const target = row.getAttribute('data-href');
        if (!target) return;

        row.addEventListener('click', (event) => {
            const anchor = event.target.closest('a');
            if (anchor) return;
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
