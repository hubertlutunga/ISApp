<?php

require_once dirname(__DIR__, 2) . '/src/Support/EnvLoader.php';
EnvLoader::loadProjectEnv(dirname(__DIR__, 2));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$eventDbHost = getenv('EVENT_DB_HOST') ?: (getenv('ISAPP_DB_HOST') ?: 'localhost');
$eventDbName = getenv('EVENT_DB_NAME') ?: (getenv('ISAPP_DB_NAME') ?: '');
$eventDbUser = getenv('EVENT_DB_USER') ?: (getenv('ISAPP_DB_USER') ?: '');
$eventDbPassword = getenv('EVENT_DB_PASSWORD') ?: (getenv('ISAPP_DB_PASSWORD') ?: '');

if ($eventDbName === '' || $eventDbUser === '') {
    die('Configuration base de donnees manquante.');
}

try {
    $pdo = new PDO(
        'mysql:host=' . $eventDbHost . ';dbname=' . $eventDbName . ';charset=utf8',
        $eventDbUser,
        $eventDbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}