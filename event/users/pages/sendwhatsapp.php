<?php 

// Préparation de la requête pour récupérer les événements
$stmtev = $pdo->prepare("SELECT * FROM events WHERE cod_event = :cod_event");
$stmtev->execute(['cod_event' => $_GET['cod']]);
$dataevent = $stmtev->fetch();

if (!$dataevent) {
    $codevent = '';
    $date_event = '';
    $type_event = '';
    $display = 'none';
} else {  
    $codevent = $dataevent['cod_event'];
    $date_event = $dataevent['date_event'];
    $type_event = $dataevent['type_event'];
    $display = 'block';
}

//--------------------------------

// Préparation de la requête pour récupérer le nom de l'événement
$stmtnv = $pdo->prepare("SELECT * FROM evenement WHERE cod_event = ?");
$stmtnv->execute([$codevent]); // Correction ici pour utiliser $codevent
$data_evenement = $stmtnv->fetch();

if (!$data_evenement) {
    $data_evenement = ''; 
} else {  
    $data_evenement = $data_evenement['nom'];
}

// Détermination du type d'événement
if ($type_event == "1") {
    $fetard = (($dataevent['prenom_epouse'] ?? '') . ' & ' . ($dataevent['prenom_epoux'] ?? '')) ?: 'Inconnu';
    $typeevent = 'au Mariage ' . $dataevent['type_mar'] .' de '.$fetard. ', le ' . date('d M Y à H:i', strtotime($dataevent['date_event']));
    $displayvue = 'display:block;';
} elseif ($type_event == "2") {
    $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
    $typeevent = "à l'anniversaire de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataevent['date_event']));
    $displayvue = 'display:none;';
} elseif ($type_event == "3") {
    $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
    $typeevent = "à la conférence de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataevent['date_event']));
    $displayvue = 'display:none;';
}

// Configuration Twilio
require_once '../../twilio-php-main/src/Twilio/autoload.php'; // Vérifiez ce chemin
use Twilio\Rest\Client;

$sid    = "AC5cbb94f85695ce16d97ce2ca2c3f7db0";
$token  = "2fc99f87d42f61c691c01df995fb8290";
$twilio = new Client($sid, $token); 

$msgnotif = "Cher Hubert Lutunga,\n\nVous êtes invité $typeevent. \n\nPour plus d'infos, visitez : https://invitationspeciale.com/site/index.php?page=accueil&cod=$codevent \n\n Ci-deoous votre invitation :";

// Message à envoyer
$message = $twilio->messages
    ->create("whatsapp:+243810678785", // à
        array(
            "from" => "whatsapp:+14155238886", // votre numéro Twilio
            "body" => $msgnotif // Utilisez le nouveau message ici
        )
    );

print($message->sid);
?>