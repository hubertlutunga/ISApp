 

<?php


include('../couple/pages/bdd.php');

$stmt2 = $pdo->prepare("SELECT * from mariages WHERE cod_mar = :cod_mar");
$stmt2->execute([
    'cod_mar' => $_GET['cod']
]);

$datamariage = $stmt2->fetch(); 

$codmariage = $_GET['cod'];





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
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <meta name="format-detection" content="telephone=no">
  <link rel="icon" href="../img/favicon-32x32.png" sizes="32x32" />
  <link rel="icon" href="../img/favicon-192x192.png" sizes="192x192" />
  <link rel="apple-touch-icon-precomposed" href="../img/favicon-180x180.png" />
  <meta name="msapplication-TileImage" content="../img/favicon-270x270.png" />
  <title><?php echo $datamariage['prenom_epoux'];?> & <?php echo $datamariage['prenom_epouse'];?> - Menu</title>
  <!-- FONTS -->
  <link href="https://fonts.googleapis.com/css?family=Karla:400,400i,700|Poppins:300,400,400i,600|Source+Sans+Pro:400,400i,600,700,900|Mukta:400,700,800|Oswald:400,600|Lato:400,400i,700|Roboto:300,400,500,700|Roboto+Slab:400,700|Playfair+Display:400,400i,700i,700|Catamaran:300,400,500,600,700,900|Merriweather:400,700|Montserrat:400,500,600|Nunito|Open+Sans:300,400,500,600|Caveat:400,700|Dancing+Script|Libre+Baskerville:400,700"
    rel="stylesheet">
  <!-- STYLES -->
  <!-- Main Style -->
  <link rel="stylesheet" id="style-main" href="../css/styles-main.css?v=3">
  <link rel="stylesheet" id="swiper-main" href="../vendors/swiper/swiper.min.css">
  <link rel="stylesheet" id="lity-main" href="../vendors/lity/lity.min.css">
  <link rel="stylesheet" id="mediaelementplayer" href="../vendors/mediaelement/mediaelementplayer.min.css">
  <link rel="stylesheet" id="range" href="../vendors/range/jquery.range.css">
  <link rel="stylesheet" id="lightgallery" href="../vendors/lightgallery/lightgallery.min.css">
  <link rel="stylesheet" id="style-link" href="../css/styles-7.css?v=35">
  <!-- SCRIPTS -->
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-migrate-1.3.0.js" integrity="sha256-/Gj+NlY1u/J2UGzM/B2QrWR01trK8ZZqrD5BdqQUsac=" crossorigin="anonymous"></script>
  <!-- Swiper -->
  <script src="../vendors/swiper/swiper.min.js"></script>
  <!-- Isotope -->
  <script src="../vendors/isotope/isotope.pkgd.min.js"></script>
  <!-- Images loaded library -->
  <script src="../vendors/lazyload/imagesloaded.pkgd.min.js"></script>
  <!-- MediaElement js library (only for Aheto HTML) -->
  <script src="../vendors/mediaelement/mediaelement.min.js"></script>
  <script src="../vendors/mediaelement/mediaelement-and-player.min.js"></script>
  <!-- Typed animation text -->
  <script src="../vendors/typed/typed.min.js"></script>
  <!-- Lity Lightbox -->
  <script src="../vendors/lity/lity.min.js"></script>
</head>

<body> 

 

      <?php
        include($content);
      ?>
         

 

</body>
</html>
