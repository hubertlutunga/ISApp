<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../../pages/bdd.php');

require_once('pdf/fpdf.php');
require_once('fpdi/src/autoload.php');
require_once __DIR__ . '/../../qrscan/phpqrcode/qrlib.php';

use setasign\Fpdi\Fpdi;

class MyPDF extends Fpdi {
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if ($style === 'F') {
            $op = 'f';
        } elseif ($style === 'FD' || $style === 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - ($y + $h - $r)) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $r) * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - ($y + $r)) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', 
            $x1 * $this->k, ($h - $y1) * $this->k,
            $x2 * $this->k, ($h - $y2) * $this->k,
            $x3 * $this->k, ($h - $y3) * $this->k));
    }
}

$stmtev = $pdo->prepare("SELECT * FROM events WHERE cod_event = ?");
$stmtev->execute([$_GET['event']]);
$dataevent = $stmtev->fetch();

$cheminfichier = $dataevent['invit_religieux'];
$pdf_source = 'fichiers/' . $cheminfichier;

// Créer un nouveau document PDF en utilisant votre classe personnalisée
$pdf = new MyPDF();
$pagecount = $pdf->setSourceFile($pdf_source);

// Initialiser la variable pour le nom du fichier
$nomdufichier = '';

// Boucle à travers chaque page
for ($i = 1; $i <= $pagecount; $i++) {
    $templateId = $pdf->importPage($i);
    $pdf->AddPage();
    $pdf->useTemplate($templateId, 0, 0, null, null, true);
    $pdf->SetFont('Arial', '', $dataevent['taillenominv']); 

    // Déterminer la couleur du texte
    $stmt = $pdo->prepare("SELECT * FROM invite WHERE id_inv = :id_inv");
    $stmt->execute([':id_inv' => $_GET['cod']]);
    $datainvite = $stmt->fetch();

    if ($datainvite['sing'] === 'C') {
        $sing = 'Couple';
    } elseif ($datainvite['sing'] === 'Mr') {
        $sing = 'Monsieur';
    } elseif ($datainvite['sing'] === 'Mme') {
        $sing = 'Madame';
    } else {
        $sing = '';
    }

    $nominvite = $sing . ' ' . ucfirst($datainvite['nom']);

    // Générer le nom du fichier selon le type d'événement
    if ($dataevent['type_event'] == "1") { 
        $nomdufichier = ($dataevent['ordrepri'] === "m") 
            ? $dataevent['prenom_epoux'] . ' & ' . $dataevent['prenom_epouse'] . ' - Invitation ' . $nominvite
            : $dataevent['prenom_epouse'] . ' & ' . $dataevent['prenom_epoux'] . ' - Invitation ' . $nominvite;
    } elseif ($dataevent['type_event'] == "2" || $dataevent['type_event'] == "3") { 
        $nomdufichier = $dataevent['nomfetard'] . ' - Invitation ' . $nominvite; 
    }else{ 
        $nomdufichier = $dataevent['nomfetard'] . ' - Invitation ' . $nominvite; 
    }







































    // Définir le lien de confirmation
    if ($dataevent['version'] === 'N' && empty($dataevent['nbbtn'])) {
        if ($dataevent['type_event'] === "1") {
            $lienconf = 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non';
            $lienplustard = 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=plustard';
        } elseif ($dataevent['type_event'] === "2") {
            $lienconf = 'https://invitationspeciale.com/site/anniversaire/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/anniversaire/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non';
            $lienplustard = 'https://invitationspeciale.com/site/anniversaire/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=plustard';
        } elseif ($dataevent['type_event'] === "3") {
            $lienconf = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non';
            $lienplustard = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=plustard';
        } elseif ($dataevent['type_event'] === "4") {
            $lienconf = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non';
            $lienplustard = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=plustard';
        } else {
            $lienconf = 'https://invitationspeciale.com/site/other/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/other/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non';
            $lienplustard = 'https://invitationspeciale.com/site/other/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=plustard';
        }

        // Afficher le lien de confirmation à la 4e page
        if (isset($dataevent['pagebouton'])) {
            $pagebouton = $dataevent['pagebouton']; 
        } else {
            $pagebouton = '3'; 
        }

        // Determine the language to use for the query
        $lang = $dataevent['lang'] ? $dataevent['lang'] : 'fr';

        // Prepare and execute the SQL statement
        $stmtbtn = $pdo->prepare("SELECT * FROM btninvitation WHERE lang = :lang");
        $stmtbtn->execute([':lang' => $lang]);

        // Fetch the result
        $databtn = $stmtbtn->fetch();

        if ($databtn) {
            $labelbtn1 = $databtn['conf1']; 
            $labelbtn2 = $databtn['conf2']; 
            $labelbtn3 = $databtn['conf3']; 
        } else {
            $labelbtn1 = $labelbtn2 = $labelbtn3 = null; 
        }

        if ($i == $pagebouton) {
            if (isset($dataevent['type_event'])) {
                $yy = !empty($dataevent['positionbtn']) ? $dataevent['positionbtn'] : '19';
                $buttonHeight = 20;
                $buttonWidth = 180;
                $descendreY = 8;
                $y = $yy + $descendreY;

                $pdf->SetTextColor(255, 255, 255);
                $x = ($pdf->GetPageWidth() - $buttonWidth) / 2;
                $pdf->SetFont('Arial', 'B', 20); 

                // === Bouton 1 : Je confirme ma présence ===
                $pdf->SetFillColor(0, 128, 139);
                $pdf->RoundedRect($x, $y, $buttonWidth, $buttonHeight, 9, 'F');
                $pdf->SetXY($x, $y + ($buttonHeight - 6) / 2); 
                $pdf->Cell($buttonWidth, 6, mb_convert_encoding($labelbtn1, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C', false, $lienconf);

                // === Bouton 2 ===
                $y += $buttonHeight + 4;
                $pdf->SetFillColor(140, 9, 9);
                $pdf->RoundedRect($x, $y, $buttonWidth, $buttonHeight, 9, 'F');
                $pdf->SetXY($x, $y + ($buttonHeight - 6) / 2);
                $pdf->Cell($buttonWidth, 6, mb_convert_encoding($labelbtn2, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C', false, $lienabsent);

                // === Bouton 3 ===
                $y += $buttonHeight + 4;
                $pdf->SetFillColor(50, 50, 50);
                $pdf->RoundedRect($x, $y, $buttonWidth, $buttonHeight, 9, 'F');
                $pdf->SetXY($x, $y + ($buttonHeight - 6) / 2);
                $pdf->Cell($buttonWidth, 6, mb_convert_encoding($labelbtn3, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C', false, $lienplustard);
            }
        }




























































    }elseif ($dataevent['version'] === 'N' && $dataevent['nbbtn'] === '2') {
        if ($dataevent['type_event'] === "1") {
            $lienconf = 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non'; 
        } elseif ($dataevent['type_event'] === "2") {
            $lienconf = 'https://invitationspeciale.com/site/anniversaire/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/anniversaire/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non'; 
        } elseif ($dataevent['type_event'] === "3") {
            $lienconf = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non'; 
        } elseif ($dataevent['type_event'] === "4") {
            $lienconf = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non'; 
        } else {
            $lienconf = 'https://invitationspeciale.com/site/other/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui';
            $lienabsent = 'https://invitationspeciale.com/site/other/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=non'; 
        }

        // Afficher le lien de confirmation à la 4e page
        if (isset($dataevent['pagebouton'])) {
            $pagebouton = $dataevent['pagebouton']; 
        } else {
            $pagebouton = '3'; 
        }

        // Determine the language to use for the query
        $lang = $dataevent['lang'] ? $dataevent['lang'] : 'fr';

        // Prepare and execute the SQL statement
        $stmtbtn = $pdo->prepare("SELECT * FROM btninvitation WHERE lang = :lang");
        $stmtbtn->execute([':lang' => $lang]);

        // Fetch the result
        $databtn = $stmtbtn->fetch();

        if ($databtn) {
            $labelbtn1 = $databtn['conf1']; 
            $labelbtn2 = $databtn['conf2'];  
        } else {
            $labelbtn1 = $labelbtn2 = $labelbtn3 = null; 
        }

        if ($i == $pagebouton) {
            if (isset($dataevent['type_event'])) {
                $yy = !empty($dataevent['positionbtn']) ? $dataevent['positionbtn'] : '19';
                $buttonHeight = 20;
                $buttonWidth = 180;
                $descendreY = 8;
                $y = $yy + $descendreY;

                $pdf->SetTextColor(255, 255, 255);
                $x = ($pdf->GetPageWidth() - $buttonWidth) / 2;
                $pdf->SetFont('Arial', 'B', 20); 

                // === Bouton 1 : Je confirme ma présence ===
                $pdf->SetFillColor(0, 128, 139);
                $pdf->RoundedRect($x, $y, $buttonWidth, $buttonHeight, 9, 'F');
                $pdf->SetXY($x, $y + ($buttonHeight - 6) / 2); 
                $pdf->Cell($buttonWidth, 6, mb_convert_encoding($labelbtn1, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C', false, $lienconf);

                // === Bouton 2 ===
                $y += $buttonHeight + 4;
                $pdf->SetFillColor(140, 9, 9);
                $pdf->RoundedRect($x, $y, $buttonWidth, $buttonHeight, 9, 'F');
                $pdf->SetXY($x, $y + ($buttonHeight - 6) / 2);
                $pdf->Cell($buttonWidth, 6, mb_convert_encoding($labelbtn2, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C', false, $lienabsent);

            }
        }




























































    } elseif($dataevent['version'] == 'N1'){
   
         if ($dataevent['type_event'] === "1") {
            $lienconf = 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui'; 
        } elseif ($dataevent['type_event'] === "2") {
            $lienconf = 'https://invitationspeciale.com/site/anniversaire/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui'; 
        } elseif ($dataevent['type_event'] === "3") {
            $lienconf = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui'; 
        } elseif ($dataevent['type_event'] === "4") {
            $lienconf = 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $_GET['event'] . '&idinv=' . $datainvite['id_inv'] . '&presence=oui'; 
        }

        // Afficher le lien de confirmation à la 4e page
        if (isset($dataevent['pagebouton'])) {
            $pagebouton = $dataevent['pagebouton']; 
        } else {
            $pagebouton = '3'; 
        }

        // Determine the language to use for the query
        $lang = $dataevent['lang'] ? $dataevent['lang'] : 'fr';

        // Prepare and execute the SQL statement
        $stmtbtn = $pdo->prepare("SELECT * FROM btninvitation WHERE lang = :lang");
        $stmtbtn->execute([':lang' => $lang]);

        // Fetch the result
        $databtn = $stmtbtn->fetch();

        if ($databtn) {
            $labelbtn1 = $databtn['conf1']; 
            $labelbtn2 = $databtn['conf2']; 
            $labelbtn3 = $databtn['conf3']; 
        } else {
            $labelbtn1 = $labelbtn2 = $labelbtn3 = null; 
        }

        if ($i == $pagebouton) {
            if (isset($dataevent['type_event'])) {
                $yy = !empty($dataevent['positionbtn']) ? $dataevent['positionbtn'] : '19';
                $buttonHeight = 20;
                $buttonWidth = 180;
                $descendreY = 8;
                $y = $yy + $descendreY;

                $pdf->SetTextColor(255, 255, 255);
                $x = ($pdf->GetPageWidth() - $buttonWidth) / 2;
                $pdf->SetFont('Arial', 'B', 20); 

                // === Bouton 1 : Je confirme ma présence ===
                $pdf->SetFillColor(0, 128, 139);
                $pdf->RoundedRect($x, $y, $buttonWidth, $buttonHeight, 9, 'F');
                $pdf->SetXY($x, $y + ($buttonHeight - 6) / 2); 
                $pdf->Cell($buttonWidth, 6, mb_convert_encoding($labelbtn1, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C', false, $lienconf);
 
            }
        }





















































        
    }else{
        $lienconf = "";
    }

    // Ajouter le nom de l'invité à la page 2
    if ($i == $dataevent['pagenom']) {
        $y = $dataevent['ajustenom'];
        if (($dataevent['alignnominv'] ?? '') === 'center') {
            $x = ($pdf->GetPageWidth() - $pdf->GetStringWidth($nominvite)) / 2;
        } elseif (($dataevent['alignnominv'] ?? '') === 'left') {
            $x = is_numeric($dataevent['bordgauchenominv']) ? (float) $dataevent['bordgauchenominv'] : 10;
        } else {
            $x = 10;
        }
        $pdf->Text($x, $y, mb_convert_encoding($nominvite, 'ISO-8859-1', 'UTF-8')); 
    }

    // page QRcode---------------------------------------
    if (isset($dataevent['qrcode'])) { 
        if ($i == $dataevent['pageqr']) {
            $PNG_TEMP_DIR = 'temp/';
            if (!file_exists($PNG_TEMP_DIR)) mkdir($PNG_TEMP_DIR);

            $codeString = 'https://invitationspeciale.com/site/index.php?page=access_cible&cod=' . $_GET['event'] . '&codinv=' . $datainvite['id_inv'];
            $filename = $PNG_TEMP_DIR . 'fp_qr' . md5($codeString) . '.png';
            QRcode::png($codeString, $filename);
            $hautqr = $dataevent['hautqr'];
            $gaucheqr = $dataevent['gaucheqr'];
            $tailleqr = $dataevent['tailleqr'];

            $pdf->Image($PNG_TEMP_DIR . basename($filename), $gaucheqr, $hautqr, $tailleqr);
        }   
    }

    if ($i == $dataevent['pagenom']) {
        // Définir la couleur en fonction de taillenominv
        if (!empty($dataevent['colornom']) && $dataevent['cod_event'] !== '375') {
            $pdf->SetTextColor(255, 255, 255);
        } elseif (!empty($dataevent['colornom']) && $dataevent['cod_event'] === '375') {
            $pdf->SetTextColor(195, 153, 107);
        } else {
            $pdf->SetTextColor(0, 0, 0);
        }

        $y = $dataevent['ajustenom'];
        if (($dataevent['alignnominv'] ?? '') === 'center') {
            $x = ($pdf->GetPageWidth() - $pdf->GetStringWidth($nominvite)) / 2;
        } elseif (($dataevent['alignnominv'] ?? '') === 'left') {
            $x = is_numeric($dataevent['bordgauchenominv']) ? (float) $dataevent['bordgauchenominv'] : 10;
        } else {
            $x = 10;
        }

        if (isset($dataevent['ajustenom'])) {
            $pdf->Text($x, $y, mb_convert_encoding($nominvite, 'ISO-8859-1', 'UTF-8'));
        }
    }
}

// Sortir le PDF
$nomdufichier = mb_convert_encoding($nomdufichier, 'UTF-8', 'UTF-8');
$nomdufichier = preg_replace('/[\/:*?"<>|]/', '', $nomdufichier);
$nomdufichier = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nomdufichier);
$nomdufichier = strtoupper($nomdufichier);
$pdf->Output($nomdufichier . '.pdf', 'I');
?>