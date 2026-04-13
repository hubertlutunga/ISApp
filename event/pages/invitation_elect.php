<?php
// === Debug (à désactiver en prod) ===
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('../../pages/bdd.php');

require_once('pdf/fpdf.php');
require_once('fpdi/src/autoload.php');
// require_once('../users/pages/pdf/phpqrcode/qrlib.php'); // non utilisé ici, commentez pour éviter un require inutile

use setasign\Fpdi\Fpdi;

/**
 * Petite aide pour convertir proprement vers ISO-8859-1 (FPDF)
 */
function toLatin1(?string $s): string {
    if ($s === null) return '';
    // Tente direct, sinon translit
    $try = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $s);
    if ($try !== false) return $try;
    // fallback
    return mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
}

/**
 * Construit le lien d’accès à la page publique selon le type d’événement
 */
function buildInviteLink(string $typeEvent, int $eventId): string {
    switch ($typeEvent) {
        case '1': // Mariage (par ex.)
            return 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $eventId;
        case '2': // Anniversaire
            return 'https://invitationspeciale.com/site/anniversaire/index.php?page=accueil&cod=' . $eventId;
        case '3': // Conférence
        case '4': // Autre → conférence (ajustez si besoin)
            return 'https://invitationspeciale.com/site/conference/index.php?page=accueil&cod=' . $eventId;
        default:
            return 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $eventId;
    }
}

/**
 * PDF personnalisé avec coins arrondis
 */
class MyPDF extends Fpdi {
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k  = $this->k;
        $hp = $this->h;
        if ($style == 'F') $op = 'f';
        elseif ($style == 'FD' || $style == 'DF') $op = 'B';
        else $op = 'S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x+$r)*$k, ($hp-$y)*$k ));
        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-($y+$h-$r))*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', ($x+$r)*$k, ($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $x*$k, ($hp-($y+$r))*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c ',
            $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k,
            $x3*$this->k, ($h-$y3)*$this->k
        ));
    }
}

/* ==== 1) Paramètres ==== */

// ID évènement (GET), sécurisé
$eventId = isset($_GET['event']) ? (int)$_GET['event'] : 0;
if ($eventId <= 0) {
    http_response_code(400);
    exit('Paramètre "event" manquant ou invalide.');
}

// PDF source (modèle)
$cheminFichier = 'Aicha&Ali_Invitation45.pdf';
$pdf_source    = 'fichiers/' . $cheminFichier;
if (!is_file($pdf_source)) {
    http_response_code(404);
    exit('PDF source introuvable.');
}

/* ==== 2) Données DB ==== */

$stmtev = $pdo->prepare("SELECT * FROM events WHERE cod_event = ?");
$stmtev->execute([$eventId]);
$dataevent = $stmtev->fetch(PDO::FETCH_ASSOC);

if (!$dataevent) {
    http_response_code(404);
    exit("Événement introuvable.");
}

$lang = !empty($dataevent['lang']) ? $dataevent['lang'] : 'fr';

$stmtbtn = $pdo->prepare("SELECT * FROM btninvitation WHERE lang = :lang");
$stmtbtn->execute([':lang' => $lang]);
$databtn = $stmtbtn->fetch(PDO::FETCH_ASSOC);

// Libellés par défaut si absent
$labelbtn1 = $databtn['conf1'] ?? 'Je confirme ma présence';
$labelbtn2 = $databtn['conf2'] ?? 'Je ne peux pas venir';      // pas utilisé ici mais prêt
$labelbtn3 = $databtn['conf3'] ?? 'Je viendrai avec un invité';// pas utilisé ici mais prêt

// Lien en fonction de la version et du type
$lienconf = '';
if (isset($dataevent['version']) && $dataevent['version'] === 'N') {
    $typeEvent = (string)($dataevent['type_event'] ?? '');
    $lienconf  = buildInviteLink($typeEvent, $eventId);
}

// Page où placer le bouton (1-based). Vérifiez dans votre modèle.
$pageBouton = 5; // ajustez si besoin

// Position Y depuis la DB (en mm), fallback à 19 + petit offset
$positionBaseY = isset($dataevent['positionbtn']) && $dataevent['positionbtn'] !== ''
    ? (float)$dataevent['positionbtn']
    : 19.0;
$descendreY   = 8.0;
$yButton      = $positionBaseY + $descendreY;

// Bouton (dimensions en mm)
$buttonWidth  = 180.0;
$buttonHeight = 20.0;
$buttonRadius = 9.0;

/* ==== 3) Construction du PDF ==== */

$pdf = new MyPDF();

// Charge le fichier source et compte les pages
try {
    $pagecount = $pdf->setSourceFile($pdf_source);
} catch (Exception $e) {
    http_response_code(500);
    exit('Erreur lors de l’ouverture du PDF source: ' . $e->getMessage());
}

if ($pageBouton > $pagecount) {
    // Sécurité: si la page de bouton n’existe pas, on la remet à la dernière
    $pageBouton = $pagecount;
}

// Police par défaut
$pdf->SetFont('Arial', '', 12);

// Parcourt chaque page, l’importe et l’affiche
for ($i = 1; $i <= $pagecount; $i++) {
    $tplId = $pdf->importPage($i);
    $size  = $pdf->getTemplateSize($tplId);

    // Crée une page avec la même orientation et taille que la source
    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
    $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height'], true);

    // Si c’est la page cible et qu’on a un lien de confirmation → dessiner le bouton
    if ($i === (int)$pageBouton && !empty($lienconf)) {
        // Centrage horizontal
        $x = ($pdf->GetPageWidth() - $buttonWidth) / 2;

        // Style du bouton
        $pdf->SetFillColor(0, 128, 139);     // teal foncé, visible
        $pdf->SetTextColor(255, 255, 255);   // texte blanc
        $pdf->SetFont('Arial', 'B', 20);

        // Dessin du bouton
        $pdf->RoundedRect($x, $yButton, $buttonWidth, $buttonHeight, $buttonRadius, 'F');

        // Texte centré (vertical approximé via offset)
        $pdf->SetXY($x, $yButton + ($buttonHeight - 8) / 2); // 8 ~ hauteur de police 20 pour FPDF
        $pdf->Cell(
            $buttonWidth,
            8,
            toLatin1($labelbtn1),
            0,
            0,
            'C',
            false,
            $lienconf // URL cliquable
        );
    }
}

/* ==== 4) Nom du fichier de sortie ==== */

// Essayons de bâtir un nom à partir de l’événement
$nomdufichier = $dataevent['nom_event'] ?? $dataevent['titre_event'] ?? 'INVITATION';

// Nettoyage
$nomdufichier = (string)$nomdufichier;
$nomdufichier = trim($nomdufichier);
$nomdufichier = preg_replace('/[\/:*?"<>|]/', '', $nomdufichier); // caractères interdits
$nomdufichier = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nomdufichier); // ASCII safe
$nomdufichier = strtoupper($nomdufichier);
if ($nomdufichier === '' || $nomdufichier === false) {
    $nomdufichier = 'INVITATION';
}

// Sortie à l’écran
$pdf->Output($nomdufichier . '.pdf', 'I');
