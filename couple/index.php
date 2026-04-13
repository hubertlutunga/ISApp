 

<?php

require __DIR__ . '/../bootstrap/app.php';

$stmt2 = $pdo->prepare("SELECT * from mariages WHERE cod_mar = :cod_mar");
$stmt2->execute([
    'cod_mar' => $_GET['cod']
]);

$datamariage = $stmt2->fetch(); 

$codmariage = $_GET['cod'];





$content = PageRouter::resolve($_GET['page'] ?? null, __DIR__ . '/pages');

if ($content === null) {
	PageRouter::redirect('index.php?page=accueil');
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  
<meta charset="utf-8">
<title><?php echo $datamariage['prenom_epoux'];?> & <?php echo $datamariage['prenom_epouse'];?> - Wedding</title>
     
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

	<link rel="icon" href="images/<?php echo $datamariage['icone'];?>" sizes="any"> 



    	<!-- Mobile Meta Tag -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	
	<!-- Fav and touch icons -->
	<link rel="apple-touch-icon" href="images/fav_touch_icons/apple-touch-icon-180x180.png">
	<link rel="manifest" href="images/fav_touch_icons/manifest.json">
	
	<!-- IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script> 
	<![endif]-->
	
	<!-- Google Web Fonts -->
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap" rel="stylesheet">
	
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
