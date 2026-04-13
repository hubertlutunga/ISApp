<?php 

				

		$cod = $_GET['idinv'];
		$DeleteT = $pdo->prepare("DELETE from invite WHERE id_inv = :id_inv");
		$DeleteT->bindParam(':id_inv', $cod);
		$DeleteT->execute();						 
		
			
		?>
               <script>
                  window.location="index.php?page=mb_accueil";
               </script> 
