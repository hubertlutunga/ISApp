<?php

require_once __DIR__ . '/../src/Support/EnvLoader.php';
EnvLoader::loadProjectEnv(dirname(__DIR__));

$configuredBaseUrl = getenv('ISAPP_BASE_URL');

if (!$configuredBaseUrl && !empty($_SERVER['HTTP_HOST'])) {
    $https = $_SERVER['HTTPS'] ?? '';
    $scheme = ($https && $https !== 'off') ? 'https' : 'http';
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = preg_replace('#/(event/users|event|site|menu|couple)/index\.php$#', '', $scriptName);
    $basePath = preg_replace('#/index\.php$#', '', (string) $basePath);
    $configuredBaseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim((string) $basePath, '/');
}

$appConfig = [
    'base_url' => $configuredBaseUrl ?: 'https://invitationspeciale.com',
    'default_page' => 'accueil',
    'mail' => [
        'from_address' => getenv('ISAPP_MAIL_FROM') ?: 'eventpass@invitationspeciale.com',
        'from_name' => getenv('ISAPP_MAIL_FROM_NAME') ?: 'Invitation Speciale',
        'reply_to' => getenv('ISAPP_MAIL_REPLY_TO') ?: 'eventpass@invitationspeciale.com',
        'transport' => getenv('ISAPP_MAIL_TRANSPORT') ?: 'smtp',
        'host' => getenv('ISAPP_SMTP_HOST') ?: 'invitationspeciale.com',
        'port' => (int) (getenv('ISAPP_SMTP_PORT') ?: 587),
        'encryption' => getenv('ISAPP_SMTP_ENCRYPTION') ?: 'tls',
        'username' => getenv('ISAPP_SMTP_USERNAME') ?: 'eventpass@invitationspeciale.com',
        'password' => getenv('ISAPP_SMTP_PASSWORD') ?: 'Huberusbb_01',
    ],
];

$localConfigPath = __DIR__ . '/app.local.php';
if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (is_array($localConfig)) {
        $appConfig = array_replace_recursive($appConfig, $localConfig);
    }
}

return $appConfig;