<?php
header('Content-Type: application/json; charset=UTF-8');
//require_once __DIR__ . '../../../pages/bdd.php'; // adapte le chemin à ton projet
include('../../../pages/bdd.php');


 

try {
    // Récupère le JSON
    $payload = json_decode(file_get_contents('php://input'), true);
    $codtab = isset($payload['idinv']) ? (int)$payload['idinv'] : 0;
    $cod   = isset($payload['cod']) ? trim($payload['cod']) : '';

    if ($codtab <= 0 || $cod === '') {
        echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
        exit;
    }

    // modification sécurisée
    $stmt = $pdo->prepare("UPDATE events SET cod_user2 = NULL WHERE cod_event = :cod_event");
    $ok = $stmt->execute([
        ':cod_event' => $cod
    ]);

    if ($ok && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
    } else {
        echo json_encode(['success' => false, 'message' => "Aucune modification effectuée."]);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
