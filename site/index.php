 

<?php

require __DIR__ . '/../bootstrap/app.php';

$codevent = $_GET['cod'];


$codevent = $_GET['cod'];

if ($codevent == "90") {
    $codevent = '290';
 }else{
    $codevent = $_GET['cod'];
 }


$stmt2 = $pdo->prepare("SELECT * from events WHERE cod_event = :cod_event");
$stmt2->execute([
    'cod_event' => $codevent
]); 
$dataevent = $stmt2->fetch(); 

 
 

if ($dataevent['type_event'] == "3") {
    header("Location: conference/index.php?page=accueil&cod=".$codevent); 
 }elseif ($dataevent['type_event'] == "2") {
    header("Location: anniversaire/index.php?page=accueil&cod=".$codevent); 
 }
 


if (!$dataevent['icone']) {
	$favicon = '../../images/Logo_invitationSpeciale_2.png';
}else {
	$favicon = '../../couple/images/'.$dataevent['icone'];
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
<html lang="fr">

<head>
  
<meta charset="utf-8">
<title><?php echo $dataevent['prenom_epoux'];?> & <?php echo $dataevent['prenom_epouse'];?> - Wedding</title>
     
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta http-equiv="X-UA-Compatible" content="ie=edge">
     <meta name="description" content="Le Mariage <?php echo $datamariage['type_mar'];?> de <?php echo $datamariage['prenom_epoux'];?> & <?php echo $datamariage['prenom_epouse'];?>">
     <meta name="keywords" content="Gestion des accès des invités">
     <meta name="author" content="inittheme">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <meta property="og:type" content="Application">
     <meta property="og:title" content="Gestion des accès des invités">
     <meta property="og:site_name" content="ADN"> 
     <meta property="og:description" content="Invitation Spéciale"> 

    <!-- Favicon 
    <link href="img/<?php // echo $datamariage['icone'];?>" rel="icon"> -->

	<link rel="icon" href="<?php echo $favicon;?>" sizes="any"> 



    	<!-- Mobile Meta Tag -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	 
 
      <!-- Css -->
      <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
      <link href="css/main.css" rel="stylesheet" type="text/css" media="all" />
      <link href="css/magnific-popup.css" rel="stylesheet" type="text/css" media="all" />
      <link href="css/fonts.css" rel="stylesheet" type="text/css" media="all" />
      <link href="https://fonts.googleapis.com/css?family=Playfair+Display" rel="stylesheet">
      <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300,400,600,700" rel="stylesheet">
      <link href="css/private.css" rel="stylesheet" type="text/css" media="all" />
	
	<!-- Google Web Fonts -->
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap" rel="stylesheet">


	<script src="../sweet/sweetalert2.all.min.js"></script>
	 

</head>

<body> 

 

      <?php
        include($content);
      ?>
         

 
</body>
</html>
