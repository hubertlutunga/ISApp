<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

try {
  // include 'db.php'; // Assure-toi d'initialiser $pdo ici
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false, 'error'=>'Méthode non autorisée']);
    exit;
  }

  $cod_event = isset($_POST['cod_event']) ? (int)$_POST['cod_event'] : 0;
  $section   = $_POST['section'] ?? '';
  $statut    = $_POST['statut']  ?? '';

  if ($cod_event <= 0 || $section !== 'compteur' || !in_array($statut, ['on','off'], true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'Paramètres invalides']);
    exit;
  }

  // Vérifie si la ligne existe
  $stmt = $pdo->prepare("SELECT 1 FROM websitesection WHERE cod_event = :cod AND section = 'compteur' LIMIT 1");
  $stmt->execute([':cod' => $cod_event]);
  $exists = (bool)$stmt->fetchColumn();

  if ($exists) {
    $sql = "UPDATE websitesection SET statut = :statut, updated_at = NOW() WHERE cod_event = :cod AND section = 'compteur'";
  } else {
    $sql = "INSERT INTO websitesection (cod_event, section, statut, updated_at) VALUES (:cod, 'compteur', :statut, NOW())";
  }

  $pdo->prepare($sql)->execute([':cod'=>$cod_event, ':statut'=>$statut]);

  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'Serveur: '.$e->getMessage()]);
}
