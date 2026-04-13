<?php 

				

		$cod = $_GET['cod'];
		$event = $_GET['codevent'];
		$DeleteT = $pdo->prepare("DELETE from accessoires_event WHERE cod_accev = :cod_accev");
		$DeleteT->bindParam(':cod_accev', $cod);
		$DeleteT->execute();						 
		
			
		?>
               <script>
                  window.location="index.php?page=modevent&cod=<?php echo $event;?>&deleted=2";
               </script> 
