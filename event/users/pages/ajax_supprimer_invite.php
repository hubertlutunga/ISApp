<?php
header('Content-Type: application/json; charset=UTF-8');
//require_once __DIR__ . '../../../pages/bdd.php'; // adapte le chemin à ton projet
include('../../../pages/bdd.php');



 
/*

try
{
  session_start();
    $pdo = new PDO("mysql:host=localhost;dbname=invizfxg_is;charset=utf8", 'invizfxg_hubert', 'Huberusbb01');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e)
{
        die('Erreur : '.$e->getMessage());
        return false;
}
 
error_reporting(E_ALL); ini_set("display_errors", 1);

*/


try {
    // Récupère le JSON
    $payload = json_decode(file_get_contents('php://input'), true);
    $idinv = isset($payload['idinv']) ? (int)$payload['idinv'] : 0;
    $cod   = isset($payload['cod']) ? trim($payload['cod']) : '';

    if ($idinv <= 0 || $cod === '') {
        echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
        exit;
    }

    // Suppression sécurisée
    $stmt = $pdo->prepare("DELETE FROM invite WHERE id_inv = ? AND cod_mar = ?");
    $ok = $stmt->execute([$idinv, $cod]);

    if ($ok && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
    } else {
        echo json_encode(['success' => false, 'message' => "Aucun enregistrement supprimé."]);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
