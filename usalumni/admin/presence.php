<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

drc_boot($pdo);
drc_require_login([DRCALUMNI_ROLE_SUPER_ADMIN, DRCALUMNI_ROLE_REVIEWER]);

$logoPaths = drc_public_logo_paths();
$currentUser = drc_current_user();
assert($currentUser !== null);

$export = (string) ($_GET['export'] ?? '');
if ($export !== '' && in_array($export, ['csv', 'pdf'], true)) {
    if ($export === 'csv') {
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
        $_SESSION['drc_presence_error'] = 'Export PDF indisponible : bibliothèque TCPDF manquante.';
        header('Location: ' . drc_url('admin/presence.php'));
        exit;
    }

    require_once $tcpdfPath;
    $rows = $pdo->query('SELECT c.event_day, c.checkin_at, a.reference_code, a.full_name, a.email FROM drcalumni_checkins c INNER JOIN drcalumni_applications a ON a.id = c.application_id ORDER BY c.checkin_at DESC')->fetchAll(PDO::FETCH_ASSOC);
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('DRC Alumni');
    $pdf->SetAuthor('DRC Alumni');
    $pdf->SetTitle('Présences DRC Alumni');
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 9);
    $html = '<h2>Présences DRC Alumni</h2><table border="1" cellpadding="4"><tr><th><b>Jour</b></th><th><b>Date/heure</b></th><th><b>Réf</b></th><th><b>Nom</b></th><th><b>Email</b></th></tr>';
    foreach ($rows as $row) {
        $html .= '<tr><td>' . drc_h(drc_checkin_day_label((int) $row['event_day'])) . '</td><td>' . drc_h((string) $row['checkin_at']) . '</td><td>' . drc_h((string) $row['reference_code']) . '</td><td>' . drc_h((string) $row['full_name']) . '</td><td>' . drc_h((string) $row['email']) . '</td></tr>';
    }
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('drcalumni-presences.pdf', 'D');
    exit;
}

$error = (string) ($_SESSION['drc_presence_error'] ?? '');
unset($_SESSION['drc_presence_error']);

$selectedCount = (int) $pdo->query("SELECT COUNT(*) FROM drcalumni_applications WHERE status = 'selected'")->fetchColumn();
$presentDay1 = (int) $pdo->query('SELECT COUNT(*) FROM drcalumni_checkins WHERE event_day = 1')->fetchColumn();
$presentDay2 = (int) $pdo->query('SELECT COUNT(*) FROM drcalumni_checkins WHERE event_day = 2')->fetchColumn();
$recentCheckins = $pdo->query(
    'SELECT c.event_day, c.checkin_at, c.checkin_method, a.reference_code, a.full_name, a.email, a.phone, a.province, u.full_name AS operator_name
     FROM drcalumni_checkins c
     INNER JOIN drcalumni_applications a ON a.id = c.application_id
     INNER JOIN drcalumni_users u ON u.id = c.operator_id
     ORDER BY c.checkin_at DESC
     LIMIT 500'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Présence | <?php echo drc_h(DRCALUMNI_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/admin.css')); ?>">
</head>
<body>
<header class="admin-topbar">
    <div class="admin-topbar-inner">
        <div class="admin-brand">
            <img class="admin-brand-logo" src="<?php echo drc_h(drc_asset('images/use-kinshasa-flag-logo-vertical-color.png')); ?>" alt="Ambassade des États-Unis à Kinshasa">
            <strong><?php echo drc_h(DRCALUMNI_EVENT_TOPBAR_NAME); ?></strong>
        </div>
        <nav class="admin-menu">
            <a href="<?php echo drc_h(drc_url('admin/index.php')); ?>">Acceuil</a>
            <a class="active" href="<?php echo drc_h(drc_url('admin/presence.php')); ?>">Présence</a>
            <a href="<?php echo drc_h(drc_url('admin/register.php')); ?>">Utilisateurs</a>
            <a href="<?php echo drc_h(drc_url('admin/settings.php')); ?>">Paramètres</a>
        </nav>
        <div class="admin-user">
            <span>Connecté: <strong><?php echo drc_h((string) $currentUser['name']); ?></strong></span>
            <a class="btn btn-soft" href="<?php echo drc_h(drc_url('admin/index.php?action=logout')); ?>">Déconnexion</a>
        </div>
    </div>
</header>
<main class="content">
    <div class="content-topbar">
        <h2 class="section-title">Présence</h2>
        <div>
            <a class="btn btn-soft" href="?export=csv">Export CSV</a>
            <a class="btn btn-soft" href="?export=pdf">Export PDF</a>
        </div>
    </div>

    <?php if ($error !== ''): ?><div class="alert alert-error"><?php echo drc_h($error); ?></div><?php endif; ?>

    <section class="stats">
        <article class="card stat"><strong><?php echo $selectedCount; ?></strong><span>Sélectionnés</span></article>
        <article class="card stat"><strong><?php echo $presentDay1; ?></strong><span>Présences jour 1</span></article>
        <article class="card stat"><strong><?php echo $presentDay2; ?></strong><span>Présences jour 2</span></article>
        <article class="card stat"><strong><?php echo max(0, $selectedCount - ($presentDay1 + $presentDay2)); ?></strong><span>Présences restantes</span></article>
    </section>

    <section class="card">
        <h3 style="margin-top:0;">Liste des présents</h3>
        <div class="table-wrap">
            <table style="min-width:950px;">
                <thead>
                <tr><th>Jour</th><th>Date/heure</th><th>Référence</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Province</th><th>Méthode</th><th>Opérateur</th></tr>
                </thead>
                <tbody>
                <?php if ($recentCheckins === []): ?>
                    <tr><td colspan="9">Aucune présence enregistrée.</td></tr>
                <?php endif; ?>
                <?php foreach ($recentCheckins as $checkin): ?>
                    <tr>
                        <td><?php echo drc_h(drc_checkin_day_label((int) $checkin['event_day'])); ?></td>
                        <td><?php echo drc_h((string) $checkin['checkin_at']); ?></td>
                        <td><?php echo drc_h((string) $checkin['reference_code']); ?></td>
                        <td><?php echo drc_h((string) $checkin['full_name']); ?></td>
                        <td><?php echo drc_h((string) $checkin['email']); ?></td>
                        <td><?php echo drc_h((string) $checkin['phone']); ?></td>
                        <td><?php echo drc_h((string) $checkin['province']); ?></td>
                        <td><?php echo drc_h((string) $checkin['checkin_method']); ?></td>
                        <td><?php echo drc_h((string) $checkin['operator_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
