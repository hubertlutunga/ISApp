<?php
header('Content-Type: application/json; charset=UTF-8');
include('../../../pages/bdd.php');
require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    $confirmationId = isset($payload['confirmation_id']) ? (int) $payload['confirmation_id'] : 0;
    $eventId = isset($payload['cod']) ? (int) $payload['cod'] : 0;

    if ($confirmationId <= 0 || $eventId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Parametres manquants.']);
        exit;
    }

    $deleted = ConfirmationService::deleteById($pdo, $eventId, $confirmationId);

    if ($deleted) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune reponse supprimee.']);
    }
} catch (Throwable $exception) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $exception->getMessage()]);
}