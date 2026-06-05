<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Kinshasa');

require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/presence_support.php';

try {
    cbp_ensure_presence_schema($pdo);
    $participants = cbp_confirmed_participants($pdo);
    $setupError = '';
} catch (Throwable $exception) {
    error_log('[Creators Bomoko Présence] ' . $exception->getMessage());
    $participants = [];
    $setupError = 'Impossible de charger la liste de présence.';
}

$total = count($participants);
$present = count(array_filter($participants, static fn (array $participant): bool => (string) ($participant['acces'] ?? '') === 'oui'));
$pending = max(0, $total - $present);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste de présence | <?php echo cbp_h(CBOMOKO_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="images/favicom.png">
    <style>
        :root{--wood:#8b4a1f;--wood-dark:#35180b;--blue:#0a3a73;--cyan:#00a6a6;--paper:#fffaf1;--ink:#142033;--muted:#64748b;--line:#ead8bd;--shadow:0 24px 70px rgba(53,24,11,.14)}
        *{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:linear-gradient(145deg,#fffaf1,#eef7f8 55%,#fff);color:var(--ink)}a{color:inherit}.hero{padding:22px clamp(18px,4vw,52px);background:linear-gradient(135deg,var(--wood-dark),var(--wood) 55%,var(--blue));color:#fff}.hero-inner{width:min(1180px,100%);margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:18px}.brand{display:flex;align-items:center;gap:14px;text-decoration:none}.brand img{width:72px;height:72px;object-fit:contain}.brand-title{font-weight:950;font-size:clamp(22px,3vw,36px);letter-spacing:-.05em}.brand-sub{color:#fff4df;font-weight:850;letter-spacing:.08em;text-transform:uppercase;font-size:12px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:999px;padding:12px 18px;font-weight:950;text-decoration:none;cursor:pointer}.btn-soft{background:rgba(255,244,223,.14);border:1px solid rgba(255,244,223,.28);color:#fff}.shell{width:min(1180px,100%);margin:0 auto;padding:28px clamp(18px,4vw,52px) 58px}.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}.stat{border-radius:24px;padding:20px;color:#fff;box-shadow:var(--shadow);position:relative;overflow:hidden}.stat:after{content:"";position:absolute;right:-28px;top:-32px;width:90px;height:90px;border-radius:999px;background:rgba(255,255,255,.16)}.stat strong{display:block;font-size:42px;line-height:1;letter-spacing:-.08em}.stat span{display:block;margin-top:8px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;font-size:12px;color:rgba(255,255,255,.86)}.stat-total{background:linear-gradient(145deg,#0f4c81,#0f9ca8)}.stat-present{background:linear-gradient(145deg,#047857,#10b981)}.stat-pending{background:linear-gradient(145deg,#d97706,#f59e0b)}.card{background:rgba(255,250,241,.94);border:1px solid rgba(139,74,31,.14);border-radius:28px;box-shadow:var(--shadow);padding:24px}.list-head{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:18px}.list-head h1{margin:0;font-size:clamp(28px,4vw,48px);letter-spacing:-.06em}.muted{color:var(--muted);font-weight:750}.participants{display:grid;gap:12px}.participant{display:grid;grid-template-columns:1fr auto;gap:14px;align-items:center;padding:16px;border:1px solid var(--line);border-radius:20px;background:#fff;text-decoration:none;transition:.16s transform ease,.16s box-shadow ease,.16s border-color ease}.participant:hover{transform:translateY(-2px);border-color:rgba(0,166,166,.55);box-shadow:0 14px 36px rgba(10,58,115,.11)}.participant-name{font-size:18px;font-weight:950}.participant-meta{margin-top:5px;color:var(--muted);font-weight:700}.badge{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:8px 12px;font-weight:950;font-size:12px;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}.badge-ok{background:#dcfce7;color:#047857}.badge-wait{background:#fff7ed;color:#c2410c}.alert{padding:14px 16px;border-radius:18px;margin-bottom:18px;background:#fff1f0;color:#b42318;border:1px solid #ffccc7;font-weight:850}.empty{text-align:center;padding:34px;color:var(--muted);font-weight:850}.footer{margin-top:24px;text-align:center;color:var(--muted);font-weight:800}@media(max-width:760px){.hero-inner,.list-head{display:grid}.stats{grid-template-columns:1fr}.participant{grid-template-columns:1fr}.badge{justify-self:start}}
    </style>
</head>
<body>
<header class="hero">
    <div class="hero-inner">
        <a class="brand" href="index.php" aria-label="Creators Bomoko">
            <img src="images/Logo_cbomoko_White.png" alt="Creators Bomoko">
            <span>
                <span class="brand-title">Liste de présence</span><br>
                <span class="brand-sub"><?php echo cbp_h(CBOMOKO_EVENT_NAME); ?></span>
            </span>
        </a>
        <a class="btn btn-soft" href="index.php">← Formulaire</a>
    </div>
</header>

<main class="shell">
    <section class="stats" aria-label="Statistiques présence">
        <div class="stat stat-total"><strong><?php echo $total; ?></strong><span>Confirmés</span></div>
        <div class="stat stat-present"><strong><?php echo $present; ?></strong><span>Accès confirmés</span></div>
        <div class="stat stat-pending"><strong><?php echo $pending; ?></strong><span>En attente</span></div>
    </section>

    <section class="card">
        <div class="list-head">
            <div>
                <h1>Participants confirmés</h1>
                <div class="muted">Cliquez sur un participant pour ouvrir sa fiche d’accès.</div>
            </div>
            <div class="muted"><?php echo cbp_h(CBOMOKO_EVENT_DATES); ?> · <?php echo cbp_h(CBOMOKO_EVENT_LOCATION); ?></div>
        </div>

        <?php if ($setupError !== ''): ?>
            <div class="alert"><?php echo cbp_h($setupError); ?></div>
        <?php endif; ?>

        <div class="participants">
            <?php if ($participants === []): ?>
                <div class="empty">Aucun participant confirmé pour le moment.</div>
            <?php endif; ?>

            <?php foreach ($participants as $participant): ?>
                <?php $isPresent = (string) ($participant['acces'] ?? '') === 'oui'; ?>
                <a class="participant" href="presence_cible.php?id=<?php echo (int) $participant['id']; ?>">
                    <span>
                        <span class="participant-name"><?php echo cbp_h((string) $participant['nom_complet']); ?></span>
                        <span class="participant-meta">
                            <?php echo cbp_h((string) $participant['profession']); ?> · <?php echo cbp_h((string) $participant['ville']); ?><br>
                            <?php echo cbp_h((string) $participant['email']); ?> · <?php echo cbp_h((string) $participant['telephone']); ?>
                        </span>
                    </span>
                    <span class="badge <?php echo $isPresent ? 'badge-ok' : 'badge-wait'; ?>">
                        <?php echo $isPresent ? 'Présent' : 'À confirmer'; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="footer">Creators Bomoko powered by U.S Embassy Kinshasa · Designed by Hubert Solutions</div>
</main>
</body>
</html>
