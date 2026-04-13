<?php
header('Content-Type: application/json; charset=UTF-8');
//require_once __DIR__ . '../../../pages/bdd.php'; // adapte le chemin à ton projet
include('../../../pages/bdd.php');
require_once __DIR__ . '/../../../bootstrap/app.php';


 

try {
    // Récupère le JSON
    $payload = json_decode(file_get_contents('php://input'), true);
    $codtab = isset($payload['idinv']) ? (int)$payload['idinv'] : 0;
    $cod   = isset($payload['cod']) ? (int) $payload['cod'] : 0;

    if ($codtab <= 0 || $cod <= 0) {
        echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
        exit;
    }

    $deleted = LoveStoryService::deleteStep($pdo, $codtab, $cod);

    if ($deleted > 0) {
        echo json_encode(['success' => true, 'deleted' => $deleted]);
    } else {
        echo json_encode(['success' => false, 'message' => "Aucun enregistrement supprimé."]);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
