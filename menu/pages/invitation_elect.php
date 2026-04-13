<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../../pages/bdd.php');

require_once('pdf/fpdf.php');
require_once('fpdi/src/autoload.php');

use setasign\Fpdi\Fpdi;


$stmtev = $pdo->prepare("SELECT * FROM events WHERE cod_event = ?");
$stmtev->execute([$_GET['event']]);
$dataevent = $stmtev->fetch();

$cheminfichier = $dataevent['invit_religieux'];

// Le chemin vers ton PDF existant
$pdf_source = 'fichiers/'.$cheminfichier;



 
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
    $pdf->SetFont('Arial', '', $dataevent['taillenominv']);

    // Définir la couleur du texte (en RGB)



    if (isset($dataevent['colornom']) && !empty($dataevent['colornom'])) {  
        // Si la couleur est définie et non vide, appliquer la couleur
        $color = $dataevent['colornom'];
    
        // Séparer les valeurs RVB
        $rgbValues = explode(',', $color);
        
        // Vérifier que nous avons bien trois valeurs
        if (count($rgbValues) === 3) {
            $r = intval(trim($rgbValues[0])); // Rouge
            $g = intval(trim($rgbValues[1])); // Vert
            $b = intval(trim($rgbValues[2])); // Bleu
    
            // Assurez-vous que les valeurs RVB sont dans la plage valide
            if ($r >= 0 && $r <= 255 && $g >= 0 && $g <= 255 && $b >= 0 && $b <= 255) {
                $pdf->SetTextColor($r, $g, $b); // Appliquer la couleur RVB
            } else {
                // Valeurs hors limites, mettre en noir par défaut
                $pdf->SetTextColor(0, 0, 0); // Noir par défaut
            }
        } else {
            // Format invalide, mettre en noir par défaut
            $pdf->SetTextColor(0, 0, 0); // Noir par défaut
        }
    } else {
        // Si aucune couleur n'est définie, mettre en noir par défaut
        $pdf->SetTextColor(0, 0, 0); // Noir par défaut
    }
    

    $stmt = $pdo->prepare("SELECT * FROM invite WHERE id_inv = :id_inv");
    $stmt->execute([ 
    ':id_inv' => $_GET['cod']
    ]);
    $datainvite = $stmt->fetch();


    if ($datainvite['sing'] == 'C') {
        $sing = 'Couple';
    }elseif ($datainvite['sing'] == 'Mr'){
        $sing = 'Monsieur';
    }elseif ($datainvite['sing'] == 'Mme'){
        $sing = 'Madame';
    }else{
        $sing = '';
    }


    $nominvite = $sing.' '.ucfirst($datainvite['nom']) ? : '';
     
 
    if ($dataevent['type_event'] == "1") { 

        if (isset($dataevent['ordrepri']) && $dataevent['ordrepri'] === "m") {
            
            $nomdufichier = $dataevent['prenom_epoux'].' & '.$dataevent['prenom_epouse'].' - Invitation '.$nominvite;
   
        }else{
            $nomdufichier = $dataevent['prenom_epouse'].' & '.$dataevent['prenom_epoux'].' - Invitation '.$nominvite;
   
        }

     } elseif ($dataevent['type_event'] == "2" || $dataevent['type_event'] == "3") { 
        $nomdufichier = $dataevent['nomfetard'].' - Invitation '.$nominvite; 
    }
    

    

    // Définir le texte à ajouter (encodage UTF-8) 
    $nom_invite = mb_convert_encoding($nominvite, 'ISO-8859-1', 'UTF-8');
    // Remplacez par le nom réel de l'invité

    
    // Positionnement du texte (UNIQUEMENT sur la page 2)
    if ($i == $dataevent['pagenom']) { // Si c'est la deuxième page
        $y = $dataevent['ajustenom'];   // Position Y approximative (à ajuster)

        if ($dataevent['alignnominv'] == 'center') {

            // Calculer la position X pour centrer le texte
            $pageWidth = $pdf->GetPageWidth();
            $textWidth = $pdf->GetStringWidth($nom_invite);
            $x = ($pageWidth - $textWidth) / 2;
    
            // Écrire le texte (UNIQUEMENT sur la page 2)
            $pdf->Text($x, $y, $nom_invite);
    
        }elseif($dataevent['alignnominv'] == 'left') {
           
                        // Position X fixe
                $x = $dataevent['bordgauchenominv']; // Par exemple, 10 mm à partir du bord gauche

                // Écrire le texte (UNIQUEMENT sur la page 2)
                $pdf->Text($x, $y, $nom_invite);
        }
        


    }
}
// Sortir le PDF (I = afficher dans le navigateur, D = télécharger)
$nomdufichier = mb_convert_encoding($nomdufichier, 'UTF-8', 'UTF-8');
$nomdufichier = preg_replace('/[\/:*?"<>|]/', '', $nomdufichier); // Supprimer les caractères invalides

// Supprimer les accents et mettre tout en majuscules
$nomdufichier = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nomdufichier);
$nomdufichier = strtoupper($nomdufichier);

$pdf->Output($nomdufichier.'.pdf', 'I');

?>