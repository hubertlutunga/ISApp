<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

drc_boot($pdo);
$logoPaths = drc_public_logo_paths();
$reference = drc_clean_text((string) ($_GET['ref'] ?? ''), 64);
$swalPayload = [
    'icon' => 'success',
    'title' => 'Candidature soumise',
    'text' => 'Votre dossier a été enregistré avec succès.',
    'confirmButtonColor' => '#002868',
];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmation | <?php echo drc_h(DRCALUMNI_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/public.css')); ?>">
</head>
<body>
<header class="hero">
    <div class="hero-header">
        <div class="hero-mark">
            <div class="hero-logo-shell">
                <img class="hero-logo" src="<?php echo drc_h(drc_asset($logoPaths['header'])); ?>" alt="Ambassade des États-Unis à Kinshasa">
            </div>
        </div>
    </div>
</header>
<main>
    <section class="section confirmation-section">
        <h2>Votre candidature a été soumise</h2>
        <p>Merci pour votre dossier. Nous avons bien enregistré votre candidature pour <?php echo drc_h(DRCALUMNI_EVENT_NAME); ?>.</p>
        <?php if ($reference !== ''): ?>
            <p class="confirmation-reference"><strong>Référence :</strong> <?php echo drc_h($reference); ?></p>
        <?php endif; ?>
        <p>La soumission ne confirme pas la sélection. Une communication officielle sera envoyée séparément par les organisateurs.</p>
        <p class="confirmation-cta-wrap"><a class="hero-cta confirmation-cta" href="<?php echo drc_h(drc_url()); ?>">Retour à l’accueil</a></p>
    </section>
    <footer class="footer">
        <div class="footer-logos">
            <img src="<?php echo drc_h(drc_asset($logoPaths['footer_freedom'])); ?>" alt="Freedom 250">
            <img src="<?php echo drc_h(drc_asset($logoPaths['footer_seal'])); ?>" alt="U.S. Embassy Kinshasa Seal">
        </div>
        <p>Section des Affaires publiques · Ambassade des États-Unis à Kinshasa</p>
    </footer>
</main>
<script src="<?php echo drc_h(drc_asset('sweet/sweetalert2.all.min.js')); ?>" onerror="this.onerror=null;this.src='/sweet/sweetalert2.all.min.js';"></script>
<script>if (!window.Swal) { document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\\/script>'); }</script>
<script>
window.addEventListener('DOMContentLoaded', function () {
    if (!window.Swal) return;
    Swal.fire(<?php echo json_encode($swalPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
});
</script>
</body>
</html>
