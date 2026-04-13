<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('pdf/fpdf.php');
require_once('fpdi/src/autoload.php');

use setasign\Fpdi\Fpdi;

// Le chemin vers ton PDF existant
$pdf_source = 'Hezir&Charly_Invitation.pdf';

// Créer un nouveau document PDF en utilisant FPDI
$pdf = new Fpdi();

// Récupérer le nombre de pages du PDF source
$pagecount = $pdf->setSourceFile($pdf_source);

// Boucle à travers chaque page
for ($i = 1; $i <= $pagecount; $i++) {
    // Importer la page
    $templateId = $pdf->importPage($i);

    // Ajouter une page
    $pdf->AddPage();

    // Utiliser la page importée comme modèle
    $pdf->useTemplate($templateId, 0, 0, null, null, true);

    // Définir la police
    $pdf->SetFont('Arial', '', 30);

    // Définir la couleur du texte (en RGB)
    $pdf->SetTextColor(0, 0, 0); // Noir

    // Définir le texte à ajouter (encodage UTF-8) 
    $nom_invite = mb_convert_encoding('Texte sur la page 2', 'ISO-8859-1', 'UTF-8') . $i;
// Remplacez par le nom réel de l'invité

    // Positionnement du texte (UNIQUEMENT sur la page 2)
    if ($i == 2) { // Si c'est la deuxième page
        $y = 80;   // Position Y approximative (à ajuster)

        // Calculer la position X pour centrer le texte
        $pageWidth = $pdf->GetPageWidth();
        $textWidth = $pdf->GetStringWidth($nom_invite);
        $x = ($pageWidth - $textWidth) / 2;

        // Écrire le texte (UNIQUEMENT sur la page 2)
        $pdf->Text($x, $y, $nom_invite);
    }
}

// Sortir le PDF (I = afficher dans le navigateur, D = télécharger)
$pdf->Output('pdf_modifie_fpdf.pdf', 'I');

?>