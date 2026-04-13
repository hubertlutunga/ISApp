<?php 


$cod = $_GET['cod'];

// Suppression de la facture
$DeleteT = $pdo->prepare("DELETE FROM facture WHERE reference = :reference");
$DeleteT->bindParam(':reference', $cod);
$DeleteT->execute();	

// Suppression des détails de la facture
$DeleteT2 = $pdo->prepare("DELETE FROM details_fact WHERE cod_event = :cod_event");
$DeleteT2->bindParam(':cod_event', $cod);
$DeleteT2->execute();

// Mise à jour des événements
$update = $pdo->prepare("UPDATE events SET fact = :fact WHERE cod_event = :cod_event");
$factValue = ''; // Valeur vide pour 'fact'
$update->bindParam(':fact', $factValue);
$update->bindParam(':cod_event', $cod);
$update->execute();

?>


               <script>
                  window.location="index.php?page=factures";
               </script> 
