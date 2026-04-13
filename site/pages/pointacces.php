<?php 
 

  $codevent = $_GET['cod'];
  $cod = $_GET['codinv'];
  $acces = "oui";
  $ha = date('Y-m-d H:i');
 

   $sql = "UPDATE invite SET acces=:acces,heure_arrive=:heure_arrive where id_inv = '$cod'";
              
               $q = $pdo->prepare($sql);
               $q->bindValue(':acces',(empty($acces) ? 'NULL' : $acces));
               $q->bindValue(':heure_arrive',(empty($ha) ? 'NULL' : $ha));
               $q->execute();
               $q->closeCursor(); 


?>
      
               <script>
                  window.location="index.php?page=access&cod=<?php echo $codevent;?>";
               </script>
               
               
                