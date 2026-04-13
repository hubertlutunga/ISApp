<!-- BEGIN PRELOADER -->
<div id="preloader">
		<div class="loading-heart">
			<svg viewBox="0 0 512 512" width="100">
				<path d="M462.3 62.6C407.5 15.9 326 24.3 275.7 76.2L256 96.5l-19.7-20.3C186.1 24.3 104.5 15.9 49.7 62.6c-62.8 53.6-66.1 149.8-9.9 207.9l193.5 199.8c12.5 12.9 32.8 12.9 45.3 0l193.5-199.8c56.3-58.1 53-154.3-9.8-207.9z" />
			</svg>
			<div class="preloader-title">
            <?php echo $datamariage['prenom_epouse'];?><br>
				<small>&</small><br>
				<?php echo $datamariage['prenom_epoux'];?>
			</div>
		</div>
	</div>
	<!-- END PRELOADER -->


	<!-- BEGIN WRAPPER -->
	<div id="wrapper">
	
		<!-- BEGIN HEADER -->
		<header id="header">
			<div class="nav-section">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
                        <a href="index.php?page=weddetail&cod=<?php echo $codmariage?>" class="nav-logo"><img src="images/<?php echo $datamariage['logo'];?>" alt="Logo" /></a>
							
							<!-- BEGIN MAIN MENU -->
							<nav class="navbar">
								
								<ul class="nav navbar-nav">
									<li><a href="#hero">accueil</a></li>
									  
									<li><a href="#rsvp-2">RSVP</a></li>
								</ul>
								
								<button id="nav-mobile-btn"><i class="fas fa-bars"></i></button><!-- Mobile menu button -->
							</nav>
							<!-- END MAIN MENU -->
							
						</div>
					</div>
				</div>
			</div>
		</header>
		<!-- END HEADER -->
        
        
        <style>
 

 /* #Sections with background image
 ================================================== */
   
 .bg-slideshowx {
   background-image: url('images/<?php echo $datamariage['photo']; ?>');
   background-size: cover;
   background-position: center;
   background-repeat: no-repeat;
   height: 100vh;
    
 }
  
  
         </style> 	
		
		<!-- BEGIN HERO SECTION -->
		<div id="hero" class="bg-slideshowx section-divider-bottom-1 section-divider-bg-color">
			<div class="container">
				<div class="row">
					<div class="col-sm-12">
						<div class="v-center">
						<div class="hero-divider-top light" data-animation-direction="from-top" data-animation-delay="700"></div>
						
						<h1 class="hero-title light">
							<span data-animation-direction="from-right" data-animation-delay="300"><?php echo $datamariage['prenom_epouse'];?></span>
							<small data-animation-direction="from-top" data-animation-delay="300">&</small> 
							<span data-animation-direction="from-left" data-animation-delay="300"><?php echo $datamariage['prenom_epoux'];?></span>
						</h1>
						
						<div class="hero-divider-bottom light" data-animation-direction="from-bottom" data-animation-delay="700"></div>
						
						<div class="hero-subtitle light">se marient dans

<?php
 
 
// Supposons que $datamariage['date_mar'] soit une chaîne de date valide, par exemple '2022-09-20 15:00:00'
$dateString = $datamariage['date_mar'];

// Créer un objet DateTime à partir de la chaîne de date
$date = new DateTime($dateString);

// Formater la date selon le format souhaité
$formattedDate = $date->format('Y/m/d h:i A');

// Afficher la date formatée

?>
 
                        </div>
						
						<!-- 
                        Countdown container 
						Use the data attribute "date" to set the countdown date. 
						E.g.: data-date="2022/09/20 3:00 PM"
                        
                        -->
						<div class="countdown" data-date="<?php echo $formattedDate;?>"></div>
					</div>
					</div>
				</div>
			</div>
		</div>
		<!-- END HERO SECTION -->
		
		
		<!-- BEGIN BRIDE & GROOM SECTION -->
		<section class="section-bg-color overflow-content-over no-padding-top">
		
			<div class="section-bg-color overflow-content no-padding">
				<div class="container">
					<div class="row">
						<div class="col overflow-image-wrapper">
						
							<div class="overflow-image-text extra-padding-top">
								<h2 class="title">Il lui a demandé et <br> elle a dit oui !</h2>
								<p class="center"><?php echo $datamariage['story'];?></p>
								<p class="center"><a class="btn btn-primary" href="#">Notre Love Story</a></p>
							</div>
							
							<div class="overflow-image flower">
								<img src="images/<?php echo $datamariage['photostory'];?>" alt="Couple Photo">
							</div>
						</div>
					</div>
				</div>
			</div>
			
		</section>
		<!-- END BRIDE & GROOM SECTION -->
		
		
		<!-- BEGIN WEDDING INVITE SECTION -->
		<section id="the-wedding" class="parallax-background bg-color-overlay padding-divider-top">
			<div class="section-divider-top-1 section-divider-bg-color off-section"></div><!-- The class "section-divider-top-1" can also be applied to the tag <section>. In this case, it was added on a new <div> because the tag <section> have all pseudo elements (::after and ::before) in use. -->
			<div class="container">
				<div class="row">
					<div class="col-sm-12">
						<h2 class="section-title light">Nous nous marions</h2>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 center">
						<div class="invite neela-style" data-animation-direction="from-left" data-animation-delay="100">
							<div class="invite_title">
								<div class="text">
									Save<small>the</small>Date
								</div>
							</div>
							
							<div class="invite_info">
								<h2><?php echo $datamariage['prenom_epouse'];?> <small>&</small> <?php echo $datamariage['prenom_epoux'];?></h2>
								
								<div class="uppercase">Demandent l'honneur de votre présence le jour de leur mariage
                                </div>
								<div class="date"><?php  echo $formattedDate; ?></small></div>
								<div class="uppercase"><?php  echo $datamariage['lieu']; ?></div>
								
								<h5>Réception à suivre</h5>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- END WEDDING INVITE SECTION -->
		
    <?php 
    
    if ($datamariage['type_mar'] === 'Coutumier') {
        # code...
    }else{

    
    
    ?>
		<!-- BEGIN WEDDING DETAILS SECTION -->
		<section id="wedding-details" class="bg-color">
			<div class="container">
				<div class="row">
					 <div class="col-md-4 wedding-details light">
						<i class="icon-big-church"></i>
						<h4>Ceremony<small>03:00 PM - 04:00 PM</small></h4>
						<p>BIRCHWOOD CHURCH<br>4181 Birchwood Ave Seal Beach, CA<br>33.776825, -118.059113</p>
						<a href="wedding-details.html" class="btn btn-light">Learn More</a>
					</div>
					
					<div class="col-md-4 wedding-details light">
						<i class="icon-photo-camera"></i>
						<h4>Photoshoot<small>05:00 PM - 06:30 PM</small></h4>
						<p>OLD RANCH COUNTRY CLUB<br>29033 West Lake Drive, Agoura Hills, CA<br>33.776025, -118.065314</p>
						<a href="wedding-details.html" class="btn btn-light">Learn More</a>
					</div> 
					
					<div class="col-md-4 wedding-details light">
						<i class="icon-champagne-glasses"></i>
						<h4>Reception<small><?php echo date('H:i',strtotime($datamariage['date_mar'])) ?></small></h4>
						<p><?php  echo $datamariage['lieu']; ?></p>
						<a href="#" class="btn btn-light">Voir plus</a>
					</div>
				</div>
			</div>
		</section>
		<!-- END WEDDING DETAILS SECTION -->
		
        <?php
        }
        ?>

		<!-- BEGIN CONTACTS SECTION -->
		<section id="rsvp-2" class="section-bg-color extra-padding-section">
			<div class="container">
				
				<div class="row">
					<div class="col-lg-10 offset-lg-1 col-xl-8 offset-xl-2  col-xxl-6 offset-xxl-3">
						
						<div class="form-wrapper flowers neela-style">
							<h2 class="section-title">Allez-vous y assister ?</h2>
				
                            
<?php


if(isset($_POST['submitrsvp'])){
    
    $nom = @$_POST['nom'];
    $email = @$_POST['email'];
    $phone = @$_POST['phone']; 
    $presence = @$_POST['presence']; 
    $repas = @$_POST['repas']; 
    $note = @$_POST['note']; 
  
  
    if (!$nom) {
      $error = 'Remplissez le nom';
     echo '<span style="color:red;">'.$error.'</span>';
     }elseif (!$phone) {
        $error = 'Remplissez le numéro de téléphone';
        echo '<span style="color:red;">'.$error.'</span>';
     }elseif (!$email) {
        $error = 'Remplissez votre Email';
        echo '<span style="color:red;">'.$error.'</span>';
     }elseif (!$presence) {
        $error = 'Confirmer votre presence ou absence';
        echo '<span style="color:red;">'.$error.'</span>';
     }else{
  
        
    $sql = 'INSERT INTO confirmation (
      cod_mar,
      noms, 
      email, 
      phone, 
      presence, 
      typerepas,
      note,
      date_enreg)
    values  (
      :cod_mar,
      :noms, 
      :email, 
      :phone, 
      :presence, 
      :typerepas,
      :note,
      NOW())';
    
    $q = $pdo->prepare($sql);
    $q->bindValue(':cod_mar', $_GET['cod']);
    $q->bindValue(':noms', $nom);
    $q->bindValue(':email', $email);
    $q->bindValue(':phone', $phone); 
    $q->bindValue(':presence', $presence); 
    $q->bindValue(':typerepas', $repas); 
    $q->bindValue(':note', $note); 
    $q->execute();
    $q->closeCursor(); 

    

         // Envoi de l'email
         $speudo = $noms;
         $email = $email;
         $subject = strtoupper($datamariage['initiale_mar'])." Reservation";
         $message = "Bonjour $speudo,\n\nVotre reservation nous est parvenue avec succès.\n\nMerci!";
         // Ajout des en-têtes pour le format et l'encodage
         $headers = "From: contact@invitationspeciale.com\r\n";  // Changez ceci avec votre adresse d'envoi
         $headers .= "MIME-Version: 1.0\r\n";
         $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
         $headers .= "Content-Transfer-Encoding: 8bit\r\n";
  
         if (mail($email, $subject, $message, $headers)) {
         //     // Afficher le SweetAlert ici
  
  
             echo '<script src="sweet/sweetalert2.all.min.js"></script>';
             echo '<script>
                     Swal.fire({
                     title: "Reservation !",
                     text: "Votre réservation est enregistrée avec succès.",
                     icon: "success",
                     confirmButtonText: "OK"
                     }).then((result) => {
                          if (result.isConfirmed) {
                             window.location.href = "index.php?page=weddetail&cod=' . htmlspecialchars($_GET['cod']) . '"; // Redirection vers la page de détails
                            }
                     });
             </script>';
  
            } else {
                echo '<span style="color:red;">Erreur lors de l\'envoi de l\'email.</span>';
            }

    	


  }
}

 





?>






							<form id="contact-form" class="contact-form" action="" method="post">
								
								<div class="form-floating">
									<input type="text" name="nom" id="name" value="<?php echo @$_POST['nom']?>" placeholder="Votre Noms*" class="form-control required fromName">
									<label for="name">Votre noms *</label>
								</div>
								
								<div class="form-floating">
									<input type="email" name="email" id="email" value="<?php echo @$_POST['email']?>" placeholder="E-mail*" class="form-control required fromEmail">
									<label for="email">E-mail*</label>
								</div>
								
								<div class="form-floating">
									<input type="text" name="phone" id="phone" value="<?php echo @$_POST['phone']?>" placeholder="Téléphone*" class="form-control required fromEmail">
									<label for="phone">Téléphone*</label>
								</div>
								
								<div class="form-check-wrapper">
									<div class="form-check form-check-inline">
										<input class="form-check-input required" type="radio" name="presence" value="oui" id="attend_wedding_yes">
										<label for="attend_wedding_yes">Oui, je vais y assister.</label>
									</div>
									
									<div class="form-check form-check-inline">
										<input class="form-check-input required" type="radio" name="presence" value="non" id="attend_wedding_no">
										<label for="attend_wedding_no">Désolé, je ne peux pas.</label>
									</div>
								</div>
								
							 
								<fieldset class="form-check-wrapper required" name="repas" id="meal_pref">
									<label>Préférences des repas:</label>
									
									<div class="form-check">
										<input class="form-check-input" name="repas" type="checkbox" value="Viande" id="meal_meat">
										<label for="meal_meat">
                                        Viande
										</label>
									</div>
									
									<div class="form-check">
										<input class="form-check-input" name="repas" type="checkbox" value="Poisson" id="meal_fish">
										<label for="meal_fish">
											Poisson
										</label>
									</div>
									
									<div class="form-check">
										<input class="form-check-input" name="repas" type="checkbox" value="Végétarien" id="meal_vegetarian">
										<label for="meal_vegetarian">
                                            Végétarien
										</label>
									</div>
									
									<div class="form-check">
										<input class="form-check-input" name="repas" type="checkbox" value="Sans Gluten" id="meal_gluten_free">
										<label for="meal_gluten_free">
											Sans Gluten
										</label>
									</div>
								</fieldset>
								
								<div class="form-floating">
									<textarea id="message" name="note" placeholder="Message" class="form-control" rows="4"><?php echo @$_POST['note']?></textarea>
									<label for="message">Message</label>
								</div>

                                <!--<div class="form_status_message"></div>-->
								
								<div class="center">
									<button type="submit" name="submitrsvp" class="btn btn-primary">Envoyer</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- END CONTACTS SECTION -->
		
		
		<!-- BEGIN FOOTER -->
		<footer id="footer">
			  
			<div class="copyright">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
							&copy; <?php echo date('Y')?> <a href="https://www.invitationspeciale.com">InvitationSpéciale</a>, All Rights Reserved.
						</div>
					</div>
				</div>
			</div>
		</footer>
		<!-- END FOOTER -->
		
	</div>
	<!-- END WRAPPER -->
	
	
	<!-- Google Maps API and Map Richmarker Library -->
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBHOXsTqoSDPQ5eC5TChvgOf3pAVGapYog"></script>
	<script src="js/richmarker.js"></script>
	
	<!-- Libs -->
	<script src="js/jquery-3.6.0.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	<script src="js/jquery-migrate-3.3.2.min.js"></script>
	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/jquery.placeholder.min.js"></script>
	<script src="js/ismobile.js"></script>
	<script src="js/retina.min.js"></script>
	<script src="js/waypoints.min.js"></script>
	<script src="js/waypoints-sticky.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/lightbox.min.js"></script>
    
    <!-- Nicescroll script to handle gallery section touch slide -->
	<script src="js/jquery.nicescroll.js"></script>
    
    <!-- Hero Background Slideshow Script -->
	<script src="js/jquery.zoomslider.js"></script>
	
	<!-- Template Scripts -->
	<script src="js/variables.js"></script>
	<script src="js/scripts.js"></script>
	 