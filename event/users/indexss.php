<?php
 
include('../pages/bdd.php');


$page = htmlentities($_GET['page']);
$pages = scandir('pages');

if(!empty($page) && in_array($_GET['page'].".php",$pages)) {
  
    $content = 'pages/'.$_GET['page'].".php";
} 
   
   
 else{
  header("Location:index.php?page=login");
}


//------------PHPMailer---------
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
//-----------------------------
?>

<!DOCTYPE html>
<html lang="fr">

<head>


  <title>Invitation Spéciale</title>
   
  <meta charset="utf-8">
  
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../images/Logo_invitationSpeciale_2.png" />
      
    <!-- Vendors Style-->
	<link rel="stylesheet" href="html/template/horizontal/src/css/vendors_css.css">
    <!-- Style-->    
    
    <link rel="stylesheet" href="html/template/horizontal/src/css/horizontal-menu.css"> 
    <link rel="stylesheet" href="html/template/horizontal/src/css/style.css">
    <link rel="stylesheet" href="html/template/horizontal/src/css/skin_color.css">
    <link rel="stylesheet" href="html/template/horizontal/src/css/custom.css">
    <link rel="stylesheet" href="private.css">
     
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
 

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  
  </head>

<body class="contentbg">
   

 
 
      <?php
        include($content);
      ?>
       




</body>
</html>
