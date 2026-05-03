<?php

require_once __DIR__ . '/../src/Support/EnvLoader.php';
EnvLoader::loadProjectEnv(dirname(__DIR__));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$serverHost = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
$homeDirectory = (string) (getenv('HOME') ?: '');
$isCpanelProduction = str_contains($serverHost, 'invitationspeciale.com')
    || str_contains($homeDirectory, '/home/invizfxg');

$databaseConfig = [
    'host' => getenv('ISAPP_DB_HOST') ?: ($isCpanelProduction ? 'localhost' : '127.0.0.1'),
    'name' => getenv('ISAPP_DB_NAME') ?: 'invizfxg_is',
    'charset' => getenv('ISAPP_DB_CHARSET') ?: 'utf8mb4',
    'user' => getenv('ISAPP_DB_USER') ?: ($isCpanelProduction ? 'invizfxg_hubert' : 'root'),
    'password' => getenv('ISAPP_DB_PASSWORD') ?: ($isCpanelProduction ? 'Huberusbb01' : 'Root_2023'),
    'display_errors' => getenv('ISAPP_DISPLAY_ERRORS') === '1' || !$isCpanelProduction,
];

$localConfigPath = __DIR__ . '/database.local.php';
if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (is_array($localConfig)) {
        $databaseConfig = array_merge($databaseConfig, $localConfig);
    }
}

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $databaseConfig['host'],
    $databaseConfig['name'],
    $databaseConfig['charset']
);

try {
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $exception) {
    die('Erreur de connexion a la base de donnees : ' . $exception->getMessage());
}

error_reporting(E_ALL);
ini_set('display_errors', $databaseConfig['display_errors'] ? '1' : '0');
