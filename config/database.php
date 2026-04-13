<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$databaseConfig = [
    'host' => getenv('ISAPP_DB_HOST') ?: '127.0.0.1',
    'name' => getenv('ISAPP_DB_NAME') ?: 'isapp_db',
    'charset' => getenv('ISAPP_DB_CHARSET') ?: 'utf8mb4',
    'user' => getenv('ISAPP_DB_USER') ?: 'root',
    'password' => getenv('ISAPP_DB_PASSWORD') ?: 'Root_2023',
    'display_errors' => getenv('ISAPP_DISPLAY_ERRORS') === '1',
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
