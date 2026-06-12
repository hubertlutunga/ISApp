<div class="body-inner">
      <!-- Header start -->
      <?php include('header.php');?>
      <!--/ Header end -->

      

      <section class="ts-contact-form">
         <div class="container">
            <div class="row">
               <div class="col-lg-8 mx-auto">
                  <h2 class="section-title text-center">
                     <span>Comment participer ?</span>
                     S'enregistrer
                  </h2>






<?php


if(isset($_POST['submit'])){
  
  $nom = @$_POST['nom'];
  $prenom = @$_POST['prenom'];
  $phone = @$_POST['phone']; 
  $email = @$_POST['email']; 


  if (!$prenom) {
    $error = 'Remplissez le prénom';
    echo '<span style="color:red;">'.$error.'</span>';
  }elseif (!$nom) {
   $error = 'Remplissez le nom';
   echo '<span style="color:red;">'.$error.'</span>';
   }elseif (!$phone) {
      $error = 'Remplissez le numéro de téléphone';
      echo '<span style="color:red;">'.$error.'</span>';
   }elseif (!$email) {
      $error = 'Remplissez votre Email';
      echo '<span style="color:red;">'.$error.'</span>';
   }else{

      
  $sql = 'INSERT INTO participants (
    prenom,
    nom,
    phone,
    email, 
    date_enreg)
  values  (
    :prenom,
    :nom,
    :phone,
    :email, 
    NOW())';
  
  $q = $pdo->prepare($sql);
  $q->bindValue(':prenom', $prenom);
  $q->bindValue(':nom', $nom);
  $q->bindValue(':phone', $phone);
  $q->bindValue(':email', $email); 
  $q->execute();
  $q->closeCursor(); 

   
   echo "<h6 class='text-center'><span style='color:green;'>Réservation envoyé avec succès</span></h6><br><br><br>";   



  }

 

  
  }



?>

               </div><!-- col end-->
            </div>









            <div class="row">
               <div class="col-lg-8 mx-auto">
                  <form id="contact-form" class="contact-form" action="" method="post">
                     <div class="error-container"></div>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control form-control-name" placeholder="Prenom" name="prenom" id="f-name"
                                 type="text" required>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control form-control-name" placeholder="Nom" name="nom" id="l-name"
                                 type="text" required>
                           </div>
                        </div> 
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control form-control-email" placeholder="Téléphone" name="phone" id="phone"
                                 type="text" required>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control form-control-email" placeholder="Email" name="email" id="email"
                                 type="email" required>
                           </div>
                        </div>

                     </div>
                   <!--  
                     <div class="form-group">
                        <textarea class="form-control form-control-message" name="message" id="message" placeholder="Your message...*"
                           rows="6" required></textarea>
                     </div>
                  !-->

                     <div class="text-center"><br>
                        <button type="submit" name="submit" class="btn" type="submit">Envoyer</button>
                     </div>
                  </form><!-- Contact form end -->
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