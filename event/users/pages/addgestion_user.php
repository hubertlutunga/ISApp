<?php 
 
		$codus = $_GET['codus'];
		$codevent = $_GET['codevent'];
		$DeleteT = $pdo->prepare("UPDATE events SET cod_user2 = :cod_user2 WHERE cod_event = :cod_event");
		$DeleteT->bindParam(':cod_user2', $codus);
		$DeleteT->bindParam(':cod_event', $codevent);
		$DeleteT->execute();						 
		
			
		?>
               <script>
                  window.location="index.php?page=addgestion&ok=1";
               </script> 