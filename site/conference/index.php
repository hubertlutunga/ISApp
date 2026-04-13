 

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
<html lang="en">

<head>
   <!-- Basic Page Needs ================================================== -->
   <meta charset="utf-8">

   <!-- Mobile Specific Metas ================================================== -->
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

   <!-- Site Title -->
  <title>Invitation Spéciale</title>


   <!-- CSS
         ================================================== -->
   <!-- Bootstrap -->
   <link rel="stylesheet" href="css/bootstrap.min.css">

   <!-- FontAwesome -->
   <link rel="stylesheet" href="css/font-awesome.min.css">
   <!-- Animation -->
   <link rel="stylesheet" href="css/animate.css">
   <!-- magnific -->
   <link rel="stylesheet" href="css/magnific-popup.css">
   <!-- carousel -->
   <link rel="stylesheet" href="css/owl.carousel.min.css">
   <!-- isotop -->
   <link rel="stylesheet" href="css/isotop.css">
   <!-- ico fonts -->
   <link rel="stylesheet" href="css/xsIcon.css">
   <!-- Template styles-->
   <link rel="stylesheet" href="css/style.css">
   <!-- Responsive styles-->
   <link rel="stylesheet" href="css/responsive.css">

   <link rel="stylesheet" href="css/private.css">


   <link rel="shortcut icon" href="images/Logo_invitationSpeciale_2.png" />

   <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
   <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
   <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->

	<script src="../../sweet/sweetalert2.all.min.js"></script>
</head>

<body> 

 

      <?php
        include($content);
      ?>
         

 

</body>
</html>
