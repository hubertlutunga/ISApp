 

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
} else {
    $typeevent = $data_evenement;
    $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
	$displayvue = 'display:none;';
}

if (!function_exists('normalize_public_event_type')) {
    function normalize_public_event_type(string $value): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));

        return strtr($value, [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ÿ' => 'y',
            'œ' => 'oe',
            'æ' => 'ae',
        ]);
    }
}

$normalizedPublicType = normalize_public_event_type((string) $data_evenement);
$publicEventLabels = [
    'logo_cta' => 'VENEZ PARTICIPER',
    'hero_cta' => 'Participer',
    'join_kicker' => 'REJOIGNEZ-NOUS',
    'join_title' => "Obtenir la direction vers le lieu de l'événement",
    'form_kicker' => 'Comment participer ?',
    'form_title' => "S'enregistrer",
    'form_submit' => 'Envoyer',
    'subject_suffix' => 'RSVP',
    'success_message' => 'Votre inscription a été confirmée avec succès',
    'email_message' => "Votre inscription nous est parvenue avec succès.",
    'duplicate_message' => "Cette adresse email ou ce numéro de téléphone a déjà été enregistré pour cet événement.",
];

if (strpos($normalizedPublicType, 'formation') !== false) {
    $publicEventLabels = [
        'logo_cta' => 'REJOIGNEZ LA FORMATION',
        'hero_cta' => "S'inscrire",
        'join_kicker' => 'FORMATION',
        'join_title' => 'Consulter le lieu et les informations de la session',
        'form_kicker' => 'Comment participer à la formation ?',
        'form_title' => "S'inscrire à la session",
        'form_submit' => 'Valider mon inscription',
        'subject_suffix' => 'INSCRIPTION',
        'success_message' => 'Votre inscription à la formation a été confirmée avec succès',
        'email_message' => "Votre inscription à la formation nous est parvenue avec succès.",
        'duplicate_message' => "Cette adresse email ou ce numéro de téléphone est déjà utilisé pour cette formation.",
    ];
} elseif (strpos($normalizedPublicType, 'gala') !== false) {
    $publicEventLabels = [
        'logo_cta' => 'PARTICIPEZ À LA SOIRÉE',
        'hero_cta' => 'Reserver',
        'join_kicker' => 'SOIREE DE GALA',
        'join_title' => 'Retrouver le lieu et les informations de la soirée',
        'form_kicker' => 'Comment reserver sa place ?',
        'form_title' => 'Confirmer ma presence',
        'form_submit' => 'Confirmer ma reservation',
        'subject_suffix' => 'RESERVATION',
        'success_message' => 'Votre réservation a été confirmée avec succès',
        'email_message' => "Votre réservation nous est parvenue avec succès.",
        'duplicate_message' => "Cette adresse email ou ce numéro de téléphone est déjà utilisé pour cette soirée.",
    ];
} elseif (strpos($normalizedPublicType, 'charite') !== false) {
    $publicEventLabels = [
        'logo_cta' => 'SOUTENEZ L\'ÉVÉNEMENT',
        'hero_cta' => 'Participer',
        'join_kicker' => 'SOIREE DE CHARITE',
        'join_title' => 'Retrouver le lieu et les informations de la soirée',
        'form_kicker' => 'Comment participer à la soirée ?',
        'form_title' => 'Confirmer ma presence',
        'form_submit' => 'Confirmer ma participation',
        'subject_suffix' => 'PARTICIPATION',
        'success_message' => 'Votre participation a été confirmée avec succès',
        'email_message' => "Votre participation nous est parvenue avec succès.",
        'duplicate_message' => "Cette adresse email ou ce numéro de téléphone est déjà utilisé pour cette soirée.",
    ];
}

$eventDetail = [];
$publicDetailItems = [];

try {
    $detailStmt = $pdo->prepare('SELECT * FROM event_details WHERE cod_event = ? LIMIT 1');
    $detailStmt->execute([$codevent]);
    $eventDetail = $detailStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $detailStmt->closeCursor();
} catch (Throwable $exception) {
    $eventDetail = [];
}

if (!function_exists('format_public_event_datetime')) {
    function format_public_event_datetime(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        try {
            $date = new DateTime($value);
        } catch (Throwable $exception) {
            return $value;
        }

        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT,
            null,
            IntlDateFormatter::GREGORIAN,
            'dd MMM yyyy à HH:mm'
        );

        return (string) $formatter->format($date);
    }
}

if (!empty($eventDetail)) {
    $dateDebutLabel = format_public_event_datetime($eventDetail['date_debut'] ?? null);
    $dateFinLabel = format_public_event_datetime($eventDetail['date_fin'] ?? null);
    $matiereLabel = trim((string) ($eventDetail['matiere'] ?? ''));
    $formateurLabel = trim((string) ($eventDetail['intervenant'] ?? ''));

    if ($dateDebutLabel !== '') {
        $publicDetailItems[] = ['label' => 'Début', 'value' => $dateDebutLabel];
    }

    if ($dateFinLabel !== '') {
        $publicDetailItems[] = ['label' => 'Fin', 'value' => $dateFinLabel];
    }

    if ($matiereLabel !== '') {
        $publicDetailItems[] = ['label' => 'Matière', 'value' => $matiereLabel];
    }

    if ($formateurLabel !== '') {
        $publicDetailItems[] = ['label' => 'Formateur', 'value' => $formateurLabel];
    }
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
    <title><?php echo htmlspecialchars((string) ($typeevent ?: 'Invitation Spéciale'), ENT_QUOTES, 'UTF-8'); ?></title>


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
