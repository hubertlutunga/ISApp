<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');

require __DIR__ . '/../../../bootstrap/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submiconf'])) {
    header('Location: ../index.php?page=accueil&cod=' . urlencode((string) ($_GET['cod'] ?? '')));
    exit;
}

$codMar = (string) ($_POST['cod_mar'] ?? '');
$inviteId = isset($_POST['idinv']) ? (int) $_POST['idinv'] : 0;
$inviteName = RsvpService::cleanText($_POST['inviteName'] ?? '');
$phone = RsvpService::cleanText($_POST['phone'] ?? '');
$email = RsvpService::cleanText($_POST['email'] ?? '');
$note = RsvpService::cleanText($_POST['note'] ?? '');
$presence = 'oui';

if ($inviteName === '' && $inviteId > 0) {
    $invite = RsvpService::findInviteById($pdo, $inviteId);
    if ($invite) {
        $inviteName = RsvpService::buildInviteDisplayName($invite);
    }
}

$normalizedName = RsvpService::normalizeConfirmationName($inviteName);
if ($codMar === '' || $normalizedName === '') {
    header('Location: ../index.php?page=accueil&cod=' . urlencode($codMar) . '&err=1');
    exit;
}

try {
    RsvpService::registerConfirmation($pdo, [
        'cod_mar' => $codMar,
        'noms' => $normalizedName,
        'email' => $email,
        'phone' => $phone,
        'presence' => $presence,
        'note' => $note,
    ]);
} catch (Throwable $exception) {
    // L'utilisateur doit recevoir un feedback stable même si l'insert existe déjà.
}

header('Location: ../index.php?page=accueil&cod=' . urlencode($codMar) . '&idinv=' . urlencode((string) ($_GET['idinv'] ?? '')) . '&ok=1');
exit;
