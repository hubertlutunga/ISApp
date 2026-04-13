<?php 


$cod = $_GET['cod'];

// Suppression de la facture
$DeleteT = $pdo->prepare("DELETE FROM sortie_finance WHERE cod_sortie = :cod_sortie");
$DeleteT->bindParam(':cod_sortie', $cod);
$DeleteT->execute();	
 
?>


               <script>
                  window.location="index.php?page=sorties";
               </script> 
