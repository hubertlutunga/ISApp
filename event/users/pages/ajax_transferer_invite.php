<?php
header('Content-Type: application/json; charset=UTF-8');
include('../../../pages/bdd.php');

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
        exit;
    }

    $idinv = isset($payload['idinv']) ? (int) $payload['idinv'] : 0;
    $sourceEvent = isset($payload['cod']) ? trim((string) $payload['cod']) : '';
    $targetEvents = isset($payload['events']) && is_array($payload['events']) ? $payload['events'] : [];
    $currentUserId = '';

    if (!empty($_SESSION['user_phone'])) {
        $sessionUserStmt = $pdo->prepare('SELECT cod_user FROM is_users WHERE phone = ? LIMIT 1');
        $sessionUserStmt->execute([(string) $_SESSION['user_phone']]);
        $currentUserId = (string) ($sessionUserStmt->fetchColumn() ?: '');
    }

    if ($currentUserId === '' && !empty($_SESSION['cod_user'])) {
        $currentUserId = (string) $_SESSION['cod_user'];
    }

    if ($idinv <= 0 || $sourceEvent === '' || empty($targetEvents)) {
        echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
        exit;
    }

    if ($currentUserId === '') {
        echo json_encode(['success' => false, 'message' => 'Session expirée.']);
        exit;
    }

    $sourceEventStmt = $pdo->prepare('SELECT cod_event FROM events WHERE cod_event = ? AND (cod_user = ? OR cod_user2 = ?) LIMIT 1');
    $sourceEventStmt->execute([$sourceEvent, $currentUserId, $currentUserId]);
    if (!$sourceEventStmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => "Vous n'avez pas accès à cet événement."]);
        exit;
    }

    $inviteStmt = $pdo->prepare('SELECT * FROM invite WHERE id_inv = ? AND cod_mar = ? LIMIT 1');
    $inviteStmt->execute([$idinv, $sourceEvent]);
    $invite = $inviteStmt->fetch(PDO::FETCH_ASSOC);

    if (!$invite) {
        echo json_encode(['success' => false, 'message' => 'Invité introuvable.']);
        exit;
    }

    $normalizedTargetEvents = [];
    foreach ($targetEvents as $targetEvent) {
        $targetEvent = trim((string) $targetEvent);
        if ($targetEvent !== '' && $targetEvent !== $sourceEvent) {
            $normalizedTargetEvents[$targetEvent] = $targetEvent;
        }
    }

    if (empty($normalizedTargetEvents)) {
        echo json_encode(['success' => false, 'message' => 'Aucun événement cible valide.']);
        exit;
    }

    $insertStmt = $pdo->prepare('INSERT INTO invite (cod_mar, nom, sing, siege, date_inv, hote) VALUES (:cod_mar, :nom, :sing, :siege, NOW(), :hote)');
    $duplicateStmt = $pdo->prepare('SELECT COUNT(*) FROM invite WHERE cod_mar = ? AND LOWER(TRIM(nom)) = LOWER(TRIM(?))');
    $targetEventStmt = $pdo->prepare('SELECT cod_event FROM events WHERE cod_event = ? AND (cod_user = ? OR cod_user2 = ?) LIMIT 1');

    $inserted = 0;
    $skipped = 0;
    $invalid = 0;
    $details = [];
    $hote = isset($invite['hote']) && trim((string) $invite['hote']) !== '' ? $invite['hote'] : $currentUserId;

    foreach ($normalizedTargetEvents as $targetEvent) {
        $targetEventStmt->execute([$targetEvent, $currentUserId, $currentUserId]);
        if (!$targetEventStmt->fetchColumn()) {
            $invalid++;
            continue;
        }

        $duplicateStmt->execute([$targetEvent, (string) ($invite['nom'] ?? '')]);
        if ((int) $duplicateStmt->fetchColumn() > 0) {
            $skipped++;
            $details[] = ['event' => $targetEvent, 'status' => 'exists'];
            continue;
        }

        $insertStmt->execute([
            ':cod_mar' => $targetEvent,
            ':nom' => (string) ($invite['nom'] ?? ''),
            ':sing' => $invite['sing'] ?? null,
            ':siege' => null,
            ':hote' => $hote,
        ]);

        $inserted++;
        $details[] = ['event' => $targetEvent, 'status' => 'inserted'];
    }

    if ($inserted === 0 && $skipped === 0) {
        echo json_encode(['success' => false, 'message' => 'Aucun événement cible autorisé.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'inserted' => $inserted,
        'skipped' => $skipped,
        'invalid' => $invalid,
        'details' => $details,
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
