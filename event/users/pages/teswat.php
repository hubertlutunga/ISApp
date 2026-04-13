<?php
require_once '../../twilio-php-main/src/Twilio/autoload.php'; 
use Twilio\Rest\Client;

// Vos identifiants Twilio
$accountSid = 'AC5cbb94f85695ce16d97ce2ca2c3f7db0';
$authToken = '2fc99f87d42f61c691c01df995fb8290'; 
$twilionumber = 'whatsapp:+17167403177'; // Votre Messaging Service SID
$recipientNumber = 'whatsapp:+243852266590'; // Numéro du destinataire

// Créer une instance du client Twilio
$client = new Client($accountSid, $authToken);

// Envoi d'un message avec un modèle
$messageTemplate = $client->messages->create(
    $recipientNumber,
    [
        'from' => $twilionumber,
        //'template' => 'tempinvitation', // Nom du modèle
        'contentSid' => 'HX5e527d4a4e566b51065fcebade782c17',
        'templateData' => json_encode([
            'params' => [
                '1' => 'Hubert', // Nom
                '2' => 'cérémonie', // Événement
                '3' => 'www.invitationspeciale.com', // Lien
                '4' => 'www.invitationspeciale.com', // Invitation
            ],
        ]),
        'language' => 'fr', // Langue du modèle
    ]
);
 
 
echo "Message modèle envoyé avec l'ID : " . $messageTemplate->sid . "\n";
?>