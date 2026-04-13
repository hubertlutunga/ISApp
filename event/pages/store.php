<?php 

 
// pages/personnel/store.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

try {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  require_once __DIR__ . '/bdd.php'; // ajustez le chemin

  // Récup & nettoyage
  $nom        = isset($_POST['nom'])        ? trim($_POST['nom'])        : '';
  $postnom    = isset($_POST['postnom'])    ? trim($_POST['postnom'])    : '';
  $prenom     = isset($_POST['prenom'])     ? trim($_POST['prenom'])     : '';
  $date_nais  = isset($_POST['date_nais'])  ? trim($_POST['date_nais'])  : '';
  $adresse    = isset($_POST['adresse'])    ? trim($_POST['adresse'])    : '';
  $phone      = isset($_POST['phone'])      ? trim($_POST['phone'])      : '';
  $email      = isset($_POST['email'])      ? trim($_POST['email'])      : '';
  $num_compte = isset($_POST['num_compte']) ? trim($_POST['num_compte']) : '';

  // Validations minimales
  if ($nom === '' || $prenom === '' || $date_nais === '') {
    http_response_code(422);
    echo json_encode(['ok'=>false,'message'=>'Champs requis manquants (nom, prénom, date de naissance).']);
    exit;
  }
  // Date YYYY-MM-DD simple check
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_nais)) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'message'=>'Format de date invalide (YYYY-MM-DD requis).']);
    exit;
  }
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'message'=>'Email invalide.']);
    exit;
  }

  // INSERT (exclut photo, supprimer, salaire_brut, poste, fonction, type_contrat)
  $sql = "INSERT INTO personnel
            (nom, prenom, postnom, date_nais, date_enreg, adresse, phone, email, num_compte)
          VALUES
            (:nom, :prenom, :postnom, :date_nais, NOW(), :adresse, :phone, :email, :num_compte)";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':nom'        => $nom,
    ':prenom'     => $prenom,
    ':postnom'    => $postnom,
    ':date_nais'  => $date_nais,
    ':adresse'    => $adresse,
    ':phone'      => $phone,
    ':email'      => $email,
    ':num_compte' => $num_compte,
  ]);

  echo json_encode(['ok'=>true, 'cod_pers' => (int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'message'=>'Erreur serveur.']);
}

 