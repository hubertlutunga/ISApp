<?php 

				

		$cod = $_GET['cod'];
		$DeleteT = $pdo->prepare("DELETE from events WHERE cod_event = :codevent");
		$DeleteT->bindParam(':codevent', $cod);
		$DeleteT->execute();						 
		
			
		?>
               <script>
                  window.location="index.php?page=admin_accueil";
               </script> 
