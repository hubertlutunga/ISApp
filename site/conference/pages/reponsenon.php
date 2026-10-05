<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');

require __DIR__ . '/../../../bootstrap/app.php';

$inviteId = isset($_GET['idinv']) ? (int) $_GET['idinv'] : 0;
$eventCode = (string) ($_GET['cod'] ?? '');

$invite = $inviteId > 0 ? RsvpService::findInviteById($pdo, $inviteId) : null;
$displayName = $invite ? RsvpService::buildInviteDisplayName($invite) : '';
$confirmationName = RsvpService::normalizeConfirmationName($displayName);

if ($eventCode !== '' && $confirmationName !== '') {
    try {
        RsvpService::registerConfirmation($pdo, [
            'cod_mar' => $eventCode,
            'noms' => $confirmationName,
            'presence' => 'non',
            'email' => '',
            'phone' => '',
            'note' => '',
        ]);
    } catch (Throwable $exception) {
        // La redirection doit rester fluide même si la réponse existe déjà.
    }
}

header('Location: ../index.php?page=accueil&cod=' . urlencode($eventCode) . '&idinv=' . urlencode((string) $inviteId));
exit;
