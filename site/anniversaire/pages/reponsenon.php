<?php 

    $stmt = $pdo->prepare("SELECT * FROM invite WHERE id_inv = :id_inv");
    $stmt->execute([':id_inv' => $_GET['idinv']]);
    $datainvite = $stmt->fetch();

	$nominv = $datainvite ? $datainvite['nom'] : '';

	 
	$nom = $nominv; 
    $presence = "non";   
     
 
        
    $sql = 'INSERT INTO confirmation (
      cod_mar,
      noms,  
      presence,  
      date_enreg)

    values (

      :cod_mar,
      :noms,  
      :presence,   
      NOW())';
    
    $q = $pdo->prepare($sql);
    $q->bindValue(':cod_mar', $_GET['cod']);
    $q->bindValue(':noms', $nom); 
    $q->bindValue(':presence', $presence);  
    $q->execute();
    $q->closeCursor(); 
 

?>


 
      
               <script>
                  window.location="index.php?page=accueil&cod=<?php echo $_GET['cod']?>&idinv=<?php echo $_GET['idinv']?>";
               </script>

     