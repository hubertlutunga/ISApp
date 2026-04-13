<?php 



include('../pages/bdd.php');

    if (isset($_GET['cod'])) {
        $codevent = htmlspecialchars($_GET['cod']);
    }else{
        $codevent = '303';
    }


	if(isset($_POST['searchs']) && !empty($_POST['searchs']))
	{
		$search = $_POST['searchs'];
		
		$ou ="nom LIKE '%$search%'";
		
		$query = $pdo -> query("SELECT * FROM invite WHERE $ou AND cod_mar = '$codevent'");

		?>
		 
		 
                  <table width="100%" style="margin-top:0px;">
                    <tr style="margin-bottom: 45px;">
                      <th colspan="3" style="color:#000;">
                        Resultat de la recherche
                      </th> 
                    
		 
		 <?php

		while($rows = $query -> fetch())
			
			{

				?> 
                    
                    
                <tr style="margin-bottom:15px;background-color:#fce0d9;">
                      <td  align="left" style="border-bottom:1px solid #aaa;padding: 7px 7px 7px 7px;">
                    <a href="index.php?page=access_cible&codinv=<?php echo $rows['id_inv']; ?>&cod=<?php echo $codevent; ?>" style="color:#000;">
                      <?php echo $rows['nom']; ?>
                        
                      </a></td>
                      <td  align="left" style="border-bottom:1px solid #aaa;padding: 7px 7px 7px 7px;">
                    <a href="index.php?page=access_cible&codinv=<?php echo $rows['id_inv']; ?>&cod=<?php echo $codevent; ?>" style="color:#000;">
                      <?php echo $rows['sing']; ?>
                    </a></td>
                      <td  align="right" style="border-bottom:1px solid #aaa;padding: 7px 7px 7px 7px;">
                    <a href="index.php?page=access_cible&codinv=<?php echo $rows['id_inv']; ?>&cod=<?php echo $codevent; ?>" style="color:#000;">
                      <?php echo $rows['siege']; ?>
                    </a></td>
                      
                </tr>
                     
                    
                    
				<?php
			}
			
			?>
			      </table>
			<?php
		
	}
?>
 
          