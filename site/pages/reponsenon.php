<?php

$inviteId = isset($_GET['idinv']) ? (int) $_GET['idinv'] : 0;
$eventCode = (string) ($_GET['cod'] ?? '');
$invite = $inviteId > 0 ? RsvpService::findInviteById($pdo, $inviteId) : null;
$displayName = $invite ? RsvpService::buildInviteDisplayName($invite) : '';
$confirmationName = RsvpService::normalizeConfirmationName($displayName);

if ($eventCode !== '' && $confirmationName !== '') {
    RsvpService::registerConfirmation($pdo, [
        'cod_mar' => $eventCode,
        'noms' => $confirmationName,
        'presence' => 'non',
        'email' => '',
        'phone' => '',
        'note' => '',
    ]);
}

?>


 
      
               <script>
                  window.location="index.php?page=accueil&cod=<?php echo htmlspecialchars((string) ($_GET['cod'] ?? ''), ENT_QUOTES, 'UTF-8')?>&idinv=<?php echo htmlspecialchars((string) ($_GET['idinv'] ?? ''), ENT_QUOTES, 'UTF-8')?>";
               </script>

     