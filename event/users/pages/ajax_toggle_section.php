<?php
// pages/ajax_toggle_section.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '../../../pages/bdd.php'; // <-- adapte ton include PDO

try {
  $input = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

  $cod_event = isset($input['cod_event']) ? trim((string)$input['cod_event']) : '';
  $column    = isset($input['column'])    ? trim((string)$input['column'])    : '';
  $enabled   = isset($input['enabled'])   ? (int)$input['enabled']            : 0;

  if ($cod_event === '' || $column === '') {
    throw new RuntimeException('Paramètres manquants.');
  }

  // Liste blanche des colonnes modifiables
  $allowed = ['show_bg','show_save_date','show_coeur','show_story','show_gallery'];
  if (!in_array($column, $allowed, true)) {
    throw new RuntimeException('Colonne invalide.');
  }

  // Optionnel: vérifier que l’utilisateur connecté a droit de modifier ce cod_event
  // if (!user_can_edit_event($_SESSION['id'], $cod_event)) { throw new RuntimeException('Non autorisé.'); }

  $sql = "UPDATE events SET {$column} = :enabled, updated_at = NOW() WHERE cod_event = :cod";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':enabled' => $enabled ? 1 : 0,
    ':cod'     => $cod_event,
  ]);

  if ($stmt->rowCount() >= 0) {
    echo json_encode([
      'success' => true,
      'message' => $enabled ? 'Section activée.' : 'Section désactivée.',
    ]);
  } else {
    throw new RuntimeException('Aucune mise à jour effectuée.');
  }

} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage(),
  ]);
}
