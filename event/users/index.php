<?php

require __DIR__ . '/../../bootstrap/app.php';


$content = PageRouter::resolve($_GET['page'] ?? null, __DIR__ . '/pages');

if ($content === null) {
  PageRouter::redirect('index.php?page=logout');
}

$isAjaxRequest = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

if ($isAjaxRequest) {
  include($content);
  return;
}


//------------PHPMailer---------
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
//-----------------------------
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../images/Logo_invitationSpeciale_2.png" />

    <title>Invitation Spéciale</title>
    
	<!-- Vendors Style-->
	<link rel="stylesheet" href="html/template/horizontal/src/css/vendors_css.css">
	  
	<!-- Style-->    
	<link rel="stylesheet" href="html/template/horizontal/src/css/horizontal-menu.css"> 
	<link rel="stylesheet" href="html/template/horizontal/src/css/style.css">
	<link rel="stylesheet" href="html/template/horizontal/src/css/skin_color.css">
	<link rel="stylesheet" href="html/template/horizontal/src/css/custom.css">
  <link rel="stylesheet" href="../private.css">
     
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
 

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<!-- calendrier--> 

  <link href="https://unpkg.com/@fullcalendar/core/main.css" rel="stylesheet" />
  <link href="https://unpkg.com/@fullcalendar/daygrid/main.css" rel="stylesheet" />
  <script src="https://unpkg.com/@fullcalendar/core/main.js"></script>
  <script src="https://unpkg.com/@fullcalendar/daygrid/main.js"></script>
  <script src="https://unpkg.com/@fullcalendar/locales/fr.js"></script>

  

	<!-- sweet--> 
  <script src="../../sweet/sweetalert2.all.min.js"></script>


  </head>

<body class="layout-top-nav light-skin theme-primary fixed">


   <?php
        include($content);
   ?>
       
       
</body>
</html>
