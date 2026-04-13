 

<?php



include('../../pages/bdd.php');

$codevent = $_GET['cod'];

$stmt2 = $pdo->prepare("SELECT * from events WHERE cod_event = :cod_event");
$stmt2->execute([
    'cod_event' => $codevent
]);

$dataevent = $stmt2->fetch(); 

if (!$dataevent['icone']) {
	$favicon = '../images/Logo_invitationSpeciale_2.png';
}else {
	$favicon = 'images/'.$dataevent['icone'];
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
    $fetard = (($dataevent['prenom_epouse'] ?? '') . ' & ' . ($dataevent['prenom_epoux'] ?? '')) ?: 'Inconnu';
} elseif ($type_event == "2" || $type_event == "3") {
    $typeevent = $data_evenement;
    $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
	$displayvue = 'display:none;';
}



//------------PHPMailer---------
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
//-----------------------------

 



$page = htmlentities($_GET['page']);
$pages = scandir('pages');

if(!empty($page) && in_array($_GET['page'].".php",$pages)) {
  
    $content = 'pages/'.$_GET['page'].".php";
} 
   
   
 else{
  header("Location:index.php?page=accueil");
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  
<meta charset="utf-8">
<title><?php echo $dataan['noms'];?> Birthday</title>
     
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta http-equiv="X-UA-Compatible" content="ie=edge">
     <meta name="description" content="L'anniversaire de Mariage pour <?php echo $dataan['noms'];?>">
     <meta name="keywords" content="Gestion des invités">
     <meta name="author" content="inittheme">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <meta property="og:type" content="Application">
     <meta property="og:title" content="Gestion des invités">
     <meta property="og:site_name" content="Invitation Spéciale"> 
     <meta property="og:description" content="L'anniversaire de Mariage pour <?php echo $dataan['noms'];?>">
 

	<link rel="icon" href="images/<?php echo $dataan['icone'];?>"> 



    	<!-- Mobile Meta Tag -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	 
	
	<!-- IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script> 
	<![endif]-->
	
	<!-- Google Web Fonts -->
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap" rel="stylesheet">
	
	<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
	
	<!-- Bootstrap CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet" />
	
	<!-- FontAwesome CSS -->
	<link href="css/fontawesome-all.min.css" rel="stylesheet" />
	
	<!-- Neela Icon Set CSS -->
	<link href="css/neela-icon-set.css" rel="stylesheet" />
	
	<!-- Owl Carousel CSS -->
	<link href="css/owl.carousel.min.css" rel="stylesheet" />
	
	<!-- Template CSS -->
	<link href="css/style.css" rel="stylesheet" />
	
	<!-- Modernizr JS -->
	<script src="js/modernizr-3.6.0.min.js"></script>

</head>

<body> 

 

      <?php
        include($content);
      ?>
         

 

</body>
</html>
