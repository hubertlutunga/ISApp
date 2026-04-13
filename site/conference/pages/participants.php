<div class="body-inner">
      <!-- Header start -->
      <?php include('header.php');?>
      <!--/ Header end -->

  <?php 
  
  
  $creq = "SELECT COUNT(DISTINCT email) AS total_su FROM participants";
  $part = $pdo->query($creq);
  $row_t_part = $part->fetch();
  
  ?>    


      <section class="ts-contact-form">
         <div class="container">
            <div class="row">
               <div class="col-lg-8 mx-auto" aligne="center">
                  <h2 class="section-title text-center">
                     <span>Liste de souscripteurs</span>
                    <?php echo $row_t_part['total_su'].' Participants' ?>
                  </h2>






<?php
 

 

   


?>

               </div><!-- col end-->
            </div>









            <div class="row">
               <div class="col-lg-12 mx-auto" align="center">
                   

               <a href="pages/pdf/mudimu_participants.php" target="_blink">Obtenir la liste en PDF</a>






               <div class="table-responsive pt-3">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>
                            #
                          </th> 
                          <th>
                            Noms
                          </th> 
                          <th>
                            Contact
                          </th> 
                        </tr>
                      </thead>
                      <tbody>

    <?php 
 
 $id = 0;
  if ($row_t_part['total_su'] >= 1) {

/*


      $sqlx = "SELECT DISTINCT p.cod_par, p.nom, p.prenom, p.phone
FROM participants p
INNER JOIN (
    SELECT MIN(cod_par) AS min_id
    FROM participants
    GROUP BY nom, prenom, phone
    HAVING COUNT(*) > 1
) p2 ON p.cod_par = p2.min_id";
 
 */


      $sqlx = "SELECT DISTINCT email FROM participants order by email";
      $reqx = $pdo->query($sqlx);  
      while ($row_part = $reqx->fetch()) {


      $sqlxc = "SELECT * FROM participants where email = '{$row_part['email']}'";
      $reqxc = $pdo->query($sqlxc);  
      $row_partlist = $reqxc->fetch();

  $id++

        ?>
                        <tr>
                            <td>
                            <?php echo $id?>
                            </td>
                          <td>
                            <?php echo ucfirst($row_partlist['prenom'].' '.$row_partlist['nom']); ?>
                          </td>
                          <td>
                            <?php echo $row_partlist['phone']; ?>
                          </td> 
                        </tr>
                        
                 
        <?php 
      } 






    }else{

      ?>
                        <tr>
                          <td colspan="5"> 

                            Aucun enregistrement
                  
                          </td>
                         
                        </tr>


      <?php

        }


   ?>
         

                      </tbody>
                    </table>

<a style='text-align:center;' href="pages/pdf/mudimu_participants.php" target="_blink">Obtenir la liste en PDF</a>

                  </div>
                </div>
              </div>
            </div>


              </div>
            </div>

















               </div>
            </div>
         </div>
         <div class="speaker-shap">
            <img class="shap1" src="images/shap/home_schedule_memphis2.png" alt="">
         </div>
		</section>
		
	
        <?php include('footer.php');?>

     

      <!-- Javascript Files
            ================================================== -->
      <!-- initialize jQuery Library -->
      <script src="js/jquery.js"></script>

      <script src="js/popper.min.js"></script>
      <!-- Bootstrap jQuery -->
      <script src="js/bootstrap.min.js"></script>
      <!-- Counter -->
      <script src="js/jquery.appear.min.js"></script>
      <!-- Countdown -->
      <script src="js/jquery.jCounter.js"></script>
      <!-- magnific-popup -->
      <script src="js/jquery.magnific-popup.min.js"></script>
      <!-- carousel -->
      <script src="js/owl.carousel.min.js"></script>
      <!-- Waypoints -->
      <script src="js/wow.min.js"></script>
      <!-- isotop -->
      <script src="js/isotope.pkgd.min.js"></script>

      <!-- Template custom -->
      <script src="js/main.js"></script>

   </div>
   <!-- Body inner end -->