<?php
// whatsapp_auto_reply.php

header('Content-Type: text/xml; charset=utf-8');

// Numéro de l'expéditeur reçu depuis Twilio
$from = $_POST['From'] ?? '';

// Fichier où on garde les numéros déjà répondus
$storageFile = __DIR__ . '/whatsapp_auto_reply_history.json';

// Si le fichier n'existe pas, on le crée
if (!file_exists($storageFile)) {
    file_put_contents($storageFile, json_encode([]));
}

// Lire l'historique
$history = json_decode(file_get_contents($storageFile), true);

if (!is_array($history)) {
    $history = [];
}

// Nettoyer le numéro
$from = trim($from);

// Vérifier si ce numéro a déjà reçu l'auto-réponse
$dejaRepondu = isset($history[$from]);

// Si le numéro a déjà reçu l'auto-réponse, on ne répond rien
if ($dejaRepondu) {
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Response></Response>';
    exit;
}

// Message automatique
$message = "Merci pour votre retour.\n\n";
$message .= "Ce numéro est utilisé uniquement pour l’envoi automatique des invitations digitales. ";
$message .= "Les réponses ne sont pas traitées sur ce canal.\n\n";
$message .= "Cordialement,\nInvitation Spéciale";

// Enregistrer ce numéro comme déjà répondu
$history[$from] = [
    'date' => date('Y-m-d H:i:s'),
    'message' => $_POST['Body'] ?? ''
];

file_put_contents(
    $storageFile,
    json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// Répondre une seule fois
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<Response>
    <Message><?= htmlspecialchars($message, ENT_XML1 | ENT_COMPAT, 'UTF-8'); ?></Message>
</Response>