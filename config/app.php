<?php

$configuredBaseUrl = getenv('ISAPP_BASE_URL');

if (!$configuredBaseUrl && !empty($_SERVER['HTTP_HOST'])) {
    $https = $_SERVER['HTTPS'] ?? '';
    $scheme = ($https && $https !== 'off') ? 'https' : 'http';
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = preg_replace('#/(event/users|event|site|menu|couple)/index\.php$#', '', $scriptName);
    $basePath = preg_replace('#/index\.php$#', '', (string) $basePath);
    $configuredBaseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim((string) $basePath, '/');
}

return [
    'base_url' => $configuredBaseUrl ?: 'https://invitationspeciale.com',
    'default_page' => 'accueil',
];