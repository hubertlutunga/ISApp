<?php

require __DIR__ . '/../bootstrap/app.php';

$codevent = $_GET['cod'];

$stmt2 = $pdo->prepare("SELECT * from events WHERE cod_event = :cod_event");
$stmt2->execute([
    'cod_event' => $codevent
]); 
$dataevent = $stmt2->fetch(); 

 


if (!$dataevent['icone']) {
	$favicon = '../images/Logo_invitationSpeciale_2.png';
}else {
	$favicon = '../couple/images/'.$dataevent['icone'];
}



if (!$dataevent) {
	$codevent = '';
	$date_event = '';
	$type_event = '';
	$lieu = '';
	$display = 'none';
} else {  
	
	$codevent = $dataevent['cod_event'];
	$date_event = $dataevent['date_event'];
	$type_event = $dataevent['type_event'];
	$lieu = $dataevent['lieu'];
	$display = 'block';
}

//--------------------------------

$stmtnv = $pdo->prepare("SELECT * FROM evenement WHERE cod_event = ?");
$stmtnv->execute([$type_event]); // Correction ici pour utiliser $codevent
$data_evenement = $stmtnv->fetch();

if (!$data_evenement) {
	$data_evenement = ''; 
} else {  
	
	$data_evenement = $data_evenement['nom'];
}


if ($type_event == "1") {
    $typeevent = 'Mariage ' . ($dataevent['type_mar'] ?? 'Inconnu');
	$displayvue = 'display:block;';


    if (isset($dataevent['ordrepri']) && $dataevent['ordrepri'] === "m") {
            
        $fetard = (($dataevent['prenom_epoux'] ?? '') . ' & ' . ($dataevent['prenom_epouse'] ?? '')) ?: 'Inconnu';

    }else{

        $fetard = (($dataevent['prenom_epouse'] ?? '') . ' & ' . ($dataevent['prenom_epoux'] ?? '')) ?: 'Inconnu';

    }


} elseif ($type_event == "2" || $type_event == "3") {
    $typeevent = $data_evenement;
    $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
	$displayvue = 'display:none;';
}

 $fetard = ucwords(strtolower($fetard));

//------------PHPMailer---------
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';


require_once '../twilio-php-main/src/Twilio/autoload.php'; // Vérifiez ce chemin

use Twilio\Rest\Client; // pour les SMS


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
//-----------------------------


$content = PageRouter::resolve($_GET['page'] ?? null, __DIR__ . '/pages');

if ($content === null) {
  PageRouter::redirect('index.php?page=accueil');
}


?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <meta name="format-detection" content="telephone=no"> 
  
  <link rel="shortcut icon" href="<?php echo $favicon;?>" />
  
  <link rel="apple-touch-icon-precomposed" href="<?php echo $favicon;?>" />
  <meta name="msapplication-TileImage" content="<?php echo $favicon;?>" />
  <title>Menu - Invitation Spéciale</title>
  <!-- FONTS -->
  <link href="https://fonts.googleapis.com/css?family=Karla:400,400i,700|Poppins:300,400,400i,600|Source+Sans+Pro:400,400i,600,700,900|Mukta:400,700,800|Oswald:400,600|Lato:400,400i,700|Roboto:300,400,500,700|Roboto+Slab:400,700|Playfair+Display:400,400i,700i,700|Catamaran:300,400,500,600,700,900|Merriweather:400,700|Montserrat:400,500,600|Nunito|Open+Sans:300,400,500,600|Caveat:400,700|Dancing+Script|Libre+Baskerville:400,700"
    rel="stylesheet">
  <!-- STYLES -->
  <!-- Main Style -->
  <link rel="stylesheet" id="style-main" href="css/styles-main.css?v=3">
  <link rel="stylesheet" id="swiper-main" href="vendors/swiper/swiper.min.css">
  <link rel="stylesheet" id="lity-main" href="vendors/lity/lity.min.css">
  <link rel="stylesheet" id="mediaelementplayer" href="vendors/mediaelement/mediaelementplayer.min.css">
  <link rel="stylesheet" id="range" href="vendors/range/jquery.range.css">
  <link rel="stylesheet" id="lightgallery" href="vendors/lightgallery/lightgallery.min.css">
  <link rel="stylesheet" id="style-link" href="css/styles-7.css?v=35">
  <!-- SCRIPTS -->
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-migrate-1.3.0.js" integrity="sha256-/Gj+NlY1u/J2UGzM/B2QrWR01trK8ZZqrD5BdqQUsac=" crossorigin="anonymous"></script>
  <!-- Swiper -->
  <script src="vendors/swiper/swiper.min.js"></script>
  <!-- Isotope -->
  <script src="vendors/isotope/isotope.pkgd.min.js"></script>
  <!-- Images loaded library -->
  <script src="vendors/lazyload/imagesloaded.pkgd.min.js"></script>
  <!-- MediaElement js library (only for Aheto HTML) -->
  <script src="vendors/mediaelement/mediaelement.min.js"></script>
  <script src="vendors/mediaelement/mediaelement-and-player.min.js"></script>
  <!-- Typed animation text -->
  <script src="vendors/typed/typed.min.js"></script>
  <!-- Lity Lightbox -->
  <script src="vendors/lity/lity.min.js"></script>

	<script src="../sweet/sweetalert2.all.min.js"></script>

  </head>

<body class="contentbg">
   

 
 
      <?php
        include($content);
      ?>
       




</body>
</html>
