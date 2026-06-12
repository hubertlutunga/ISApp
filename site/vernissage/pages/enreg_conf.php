<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');

require __DIR__ . '/../../../bootstrap/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submiconf'])) {
    header('Location: ../index.php?page=accueil&cod=' . urlencode($_GET['cod'] ?? '') . '&idinv=' . urlencode($_GET['idinv'] ?? '') . '&presence=' . urlencode($_GET['presence'] ?? ''));
    exit;
}

$cod_mar    = $_POST['cod_mar'] ?? null;
$idinv      = isset($_POST['idinv']) ? (int)$_POST['idinv'] : null;
$inviteName = RsvpService::cleanText($_POST['inviteName'] ?? '');
$phone      = RsvpService::cleanText($_POST['phone'] ?? '');
$email      = RsvpService::cleanText($_POST['email'] ?? '');
$note       = RsvpService::cleanText($_POST['note'] ?? '');
$presence   = 'oui';

if ($inviteName === '' && $idinv) {
    $invite = RsvpService::findInviteById($pdo, $idinv);
    if ($invite) {
        $inviteName = RsvpService::buildInviteDisplayName($invite);
    }
}

$nomCle = RsvpService::normalizeConfirmationName($inviteName);

if (!$cod_mar || $nomCle === '' || $phone === '') {
    header('Location: ../index.php?page=accueil&cod=' . urlencode((string)$cod_mar) . '&idinv=' . urlencode((string)($idinv ?? '')) . '&presence=oui&err=1');
    exit;
}

try {
    RsvpService::registerConfirmation($pdo, [
        'cod_mar' => (string) $cod_mar,
        'noms' => $nomCle,
        'email' => $email,
        'phone' => $phone,
        'presence' => $presence,
        'note' => $note,
    ]);

    header('Location: ../index.php?page=accueil&cod='
        . urlencode((string)$cod_mar)
        . '&idinv=' . urlencode((string)($idinv ?? ''))
        . '&presence=oui&ok=1');
    exit;
} catch (Throwable $e) {
    header('Location: ../index.php?page=accueil&cod='
        . urlencode((string)$cod_mar)
        . '&idinv=' . urlencode((string)($idinv ?? ''))
        . '&presence=oui&err=1');
    exit;
}
