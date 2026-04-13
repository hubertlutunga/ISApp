<?php

////-----------------------------ENVOIS SMS-----------------------------------

require_once 'twilio-php-main/src/Twilio/autoload.php'; // Vérifiez ce chemin

use Twilio\Rest\Client; // Ceci est correct

// Vos informations d'identification Twilio
$sid = 'AC5cbb94f85695ce16d97ce2ca2c3f7db0'; // Remplacez par votre SID
$token = '2fc99f87d42f61c691c01df995fb8290'; // Remplacez par votre token
$twilio = new Client($sid, $token);

$codesec = time();
$msgnotif = "Vous avez une invitation spéciale au mariage de Naguy et Claris ce 19 Avril 2025, votre confiance, votre code secret est $codesec,<br> https://invitationspeciale.com/login";

// Informations sur le message
$from = 'INVSPECIALE'; // Remplacez par votre nom d'expéditeur enregistré
$to = '+243810678785'; // Remplacez par le numéro du destinataire
$body = $msgnotif; // Le message que vous souhaitez envoyer

try {
    // Envoi du SMS
    $message = $twilio->messages->create($to, [
        'from' => $from,
        'body' => $body
    ]);

    echo "Message envoyé avec succès : " . $message->sid;
} catch (Exception $e) {
    echo "Erreur lors de l'envoi du message : " . $e->getMessage();
}



 
 

?>