<div class="body-inner">
      <!-- Header start -->
      <?php include('header.php');?>
      <!--/ Header end -->

     
      <div id="page-banner-area" class="page-banner-area" style="background-image:url(./images/hero_area/banner_bg.jpg)">
         <!-- Subpage title start -->
         <div class="page-banner-title">
            <div class="text-center">
               <h2>Nous Contacter</h2>
               <ol class="breadcrumb">
                  <li>
                     <a href="#">Accueil /</a>
                  </li>
                  <li>
                     Contact
                  </li>
               </ol>
            </div>
         </div><!-- Subpage title end -->
      </div><!-- Page Banner end -->
 
      <section class="ts-contact-map no-padding">
         <div class="container-fluid">
            <div class="row">
               <div class="col-lg-12 no-padding">
                  <div class="mapouter">
                     <div class="gmap_canvas">
								<!-- <iframe width="100%" height="500" id="gmap_canvas" src="https://maps.google.com/maps?q=Park%20Street%2C%20Jacksonville%2C%20IL%2C%20USA&t=&z=13&ie=UTF8&iwloc=&output=embed"
									frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe> -->
									<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.072788135513!2d23.58373047494123!3d-6.1209052938657535!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19896de56771dd07%3A0x99d3f840244c11dd!2sClub%20MIBA!5e0!3m2!1sfr!2scd!4v1721044042576!5m2!1sfr!2scd" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                        <!-- <a href="https://www.pureblack.de">werbeagentur</a></div> -->
           
                  </div>
               </div>
            </div>
         </div>
      </section>

      <section class="ts-contact-form">
         <div class="container">
            <div class="row">
               <div class="col-lg-8 mx-auto">
                  <h2 class="section-title text-center">
                     <span>Vous avez des Questions?</span>
                     Envoyer un message
                  </h2>
               </div><!-- col end-->













               <?php

if(isset($_POST['submit'])){
//Add your information here
$recipient = "mmk@mudimu.com";
//$recipient = "admilutunga@gmail.com";

//Don't edit anything below this line

//import form information
$name = $_POST['name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];

$name=stripslashes($name);
$email=stripslashes($email);
$subject=stripslashes($subject);
$message=stripslashes($message); 
$message= "Name: $name, Subject: $subject \n\n Message: $message";

/*
Simple form validation
check to see if an email and message were entered
*/

//if no message entered and no email entered print an error
if (empty($message) && empty($email)){
print "No email address and no message was entered. <br>Please include an email and a message";
}
//if no message entered send print an error
elseif (empty($message)){
print "No message was entered.<br>Please include a message.<br>";
}
//if no email entered send print an error
elseif (empty($email)){
print "No email address was entered.<br>Please include your email. <br>";
}


//mail the form contents
if(mail("$recipient", "$subject", "$message", "From: $email" )) {

	// Email has sent successfully, echo a success page.

	echo '<div class="alert alert-success alert-dismissable fade in">
		<button type = "button" class = "close" data-dismiss = "alert" aria-hidden = "true">&times;</button>
    
		<p>Votre message a été envoyé avec succès</p></div>';

	} else {

	echo 'ERROR!';

	}
}



 ?>
 
            </div>
            <div class="row">
               <div class="col-lg-8 mx-auto">
                  <form id="contact-form" class="contact-form" action="" method="post">
                     <div class="error-container"></div>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control form-control-name" placeholder="Prénom" name="name" id="f-name"
                                 type="text" required>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control form-control-name" placeholder="Nom" name="name" id="l-name"
                                 type="text" required>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control form-control-subject" placeholder="Sujet" name="subject" id="subject"
                                 required>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control form-control-email" placeholder="Email" name="email" id="email"
                                 type="email" required>
                           </div>
                        </div>

                     </div>
                     <div class="form-group">
                        <textarea class="form-control form-control-message" name="message" id="message" placeholder="Votre message...*"
                           rows="6" required></textarea>
                     </div>
                     <div class="text-center"><br>
                        <button class="btn" type="submit">Envoyer</button>
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