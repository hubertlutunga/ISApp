<?php
// sms_incoming_webhook.php
declare(strict_types=1);

date_default_timezone_set('Africa/Kinshasa');

header('Content-Type: text/xml; charset=utf-8');

$from       = $_POST['From'] ?? '';
$to         = $_POST['To'] ?? '';
$body       = $_POST['Body'] ?? '';
$messageSid = $_POST['MessageSid'] ?? '';
$date       = date('Y-m-d H:i:s');

$logFile = __DIR__ . '/sms_incoming_log.json';

$logs = [];

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $decoded = json_decode($content, true);

    if (is_array($decoded)) {
        $logs = $decoded;
    }
}

$logs[] = [
    'date' => $date,
    'from' => $from,
    'to' => $to,
    'body' => $body,
    'message_sid' => $messageSid,
    'raw_post' => $_POST
];

file_put_contents(
    $logFile,
    json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// Réponse vide : on ne répond pas automatiquement au SMS
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<Response></Response>