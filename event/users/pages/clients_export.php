<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap/app.php';

if (!isset($_SESSION['user_phone'])) {
    http_response_code(403);
    exit('Acces refuse.');
}

$stmt = $pdo->prepare('SELECT cod_user, type_user FROM is_users WHERE phone = ? LIMIT 1');
$stmt->execute([$_SESSION['user_phone']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$stmt->closeCursor();

if ((string) ($currentUser['type_user'] ?? '') !== '1') {
    http_response_code(403);
    exit('Acces reserve a l administration.');
}

$scope = trim((string) ($_GET['scope'] ?? 'daily'));
$format = trim((string) ($_GET['format'] ?? 'csv'));
$statsClientUserId = max(0, (int) ($_GET['stats_client_id'] ?? 0));
$lowQuotaThreshold = max(1, (int) ($_GET['quota_threshold'] ?? 50));

$allowedScopes = ['daily', 'monthly', 'clients'];
$allowedFormats = ['csv', 'excel'];

if (!in_array($scope, $allowedScopes, true) || !in_array($format, $allowedFormats, true)) {
    http_response_code(400);
    exit('Parametres invalides.');
}

function output_export_file(string $fileName, array $headers, array $rows, string $format): void
{
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName) ?: 'export';

    if ($format === 'excel') {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $safeName . '.xls"');
        echo "\xEF\xBB\xBF";
        echo implode("\t", $headers) . "\n";

        foreach ($rows as $row) {
            $line = [];
            foreach ($row as $cell) {
                $value = str_replace(["\t", "\r", "\n"], ' ', (string) $cell);
                $line[] = $value;
            }
            echo implode("\t", $line) . "\n";
        }

        exit;
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $safeName . '.csv"');
    $output = fopen('php://output', 'wb');
    if ($output === false) {
        http_response_code(500);
        exit('Impossible de generer le fichier.');
    }

    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers, ',');
    foreach ($rows as $row) {
        fputcsv($output, $row, ',');
    }
    fclose($output);
    exit;
}

$analytics = AdminClientManagementService::buildInvitationAnalytics($pdo, $statsClientUserId);
$clientRows = AdminClientManagementService::buildClientConsumptionStats($pdo, $lowQuotaThreshold);

if ($scope === 'daily') {
    $rows = [];
    foreach ((array) ($analytics['daily_rows'] ?? []) as $dailyRow) {
        $rows[] = [
            (string) ($dailyRow['day_key'] ?? ''),
            (int) ($dailyRow['sent_count'] ?? 0),
            (string) ($dailyRow['cost_usd'] ?? '0.000'),
        ];
    }

    output_export_file(
        'stats_journalieres_invitations_' . date('Ymd_His'),
        ['Jour', 'Invitations envoyees', 'Cout USD'],
        $rows,
        $format
    );
}

if ($scope === 'monthly') {
    $rows = [];
    foreach ((array) ($analytics['monthly_rows'] ?? []) as $monthlyRow) {
        $rows[] = [
            (string) ($monthlyRow['month_key'] ?? ''),
            (int) ($monthlyRow['sent_count'] ?? 0),
            (string) ($monthlyRow['cost_usd'] ?? '0.000'),
        ];
    }

    output_export_file(
        'stats_mensuelles_invitations_' . date('Ymd_His'),
        ['Mois', 'Invitations envoyees', 'Cout USD'],
        $rows,
        $format
    );
}

$rows = [];
foreach ($clientRows as $clientRow) {
    if ($statsClientUserId > 0 && (int) ($clientRow['client_user_id'] ?? 0) !== $statsClientUserId) {
        continue;
    }

    $rows[] = [
        (int) ($clientRow['client_user_id'] ?? 0),
        (string) ($clientRow['client_name'] ?? ''),
        (string) ($clientRow['email'] ?? ''),
        (string) ($clientRow['phone'] ?? ''),
        (int) ($clientRow['event_count'] ?? 0),
        (int) ($clientRow['sent_today'] ?? 0),
        (int) ($clientRow['sent_month'] ?? 0),
        (int) ($clientRow['sent_total'] ?? 0),
        (string) ($clientRow['cost_today_usd'] ?? '0.000'),
        (string) ($clientRow['cost_month_usd'] ?? '0.000'),
        (string) ($clientRow['cost_total_usd'] ?? '0.000'),
        (int) ($clientRow['remaining_quota'] ?? 0),
        (int) ($clientRow['total_quota'] ?? 0),
        (string) ($clientRow['account_status'] ?? 'active'),
        !empty($clientRow['invitation_sending_suspended']) ? 'oui' : 'non',
    ];
}

output_export_file(
    'stats_par_client_invitations_' . date('Ymd_His'),
    [
        'ID client',
        'Nom client',
        'Email',
        'Telephone',
        'Nombre d evenements',
        'Envoyes aujourd hui',
        'Envoyes ce mois',
        'Envoyes total',
        'Cout USD jour',
        'Cout USD mois',
        'Cout USD total',
        'Quota restant',
        'Quota total',
        'Statut compte',
        'Envois suspendus',
    ],
    $rows,
    $format
);
