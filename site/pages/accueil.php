<?php 

if ($dataevent['photostory'] === NULL) {
   $photo = 'defaulwed_1.png';
   $photocoeur = 'defaulwed_1.png';
}else{
   $photo = $dataevent['photostory'];
   $photocoeur = $dataevent['photo'];
}

?>  



<!-- Preloader -->
<div class="loader">
         <!-- Preloader inner -->
         <div class="loader-inner">
            <svg width="120" height="220" viewbox="0 0 100 100" class="loading-spinner" version="1.1" xmlns="http://www.w3.org/2000/svg">
               <circle class="spinner" cx="50" cy="50" r="21" fill="#ffffff" stroke-width="2" />
            </svg>
         </div>
         <!-- End preloader inner -->
      </div>
      <!-- End preloader-->
       
      <!--Wrapper-->
      <div class="wrapper">
         <!--Header-->
         <header class="header default">
            <div class="header-section">
               <!--Container-->
               <div class="container-fluid ">
                  <nav class="navbar navbar-expand-lg header-navbar ml-0 ml-lg-5">
                     <a class=" navbar-brand navbar-logo scroll" href="#wrapper">
                     <p class="mb-0 logo-light" style="color:#fff;font-size:30px;font-family: 'Playfair Display'"><?php echo $dataevent['initiale_mar']?></p>
                     <p class="mb-0 logo-dark" style="color:#000;font-size:30px;font-family: 'Playfair Display'"><?php echo $dataevent['initiale_mar']?></p>
                     
					 
					</a>
                     <button class="navbar-toggler btn-navbar-toggler" type="button" data-toggle="collapse" data-target=".nav-menu" aria-expanded="true" aria-label="Toggle navigation">
                     <span class="fa fa-bars"></span>
                     </button>
                     <div class="nav-menu collapse navbar-collapse navbar-collapse justify-content-end mr-5 ">
                        <ul class=" navbar-nav  header-navbar-nav">
                           <li><a class=" nav-link scroll" href="#resto">Date</a></li>
                           <li><a class=" nav-link scroll" href="#story">Love Story</a></li>
                           <li><a class=" nav-link scroll" href="#wedding">Mariage</a></li>
                           <li><a class=" nav-link scroll" href="#gallery">Gallerie</a></li>
                           <li><a class=" nav-link scroll" href="#gift">Liste de cadeaux</a></li>
                           <li><a class=" nav-link scroll" href="#friends">Amis</a></li>
                           <li><a class=" nav-link scroll" href="#rsvp">Rsvp</a></li>
                           <li><a class=" nav-link scroll" href="#location">Adresse</a></li>
                        </ul>
                     </div>
                  </nav>
               </div>
               <!--End container-->
            </div>
         </header>
         <!--End header-->
         <!--Hero section-->

<?php
  
		// if(!$dataevent['photo']){
		// 	$bg = 'bgstand2_1.png';
		// }else{
		// 	$bg = $dataevent['photo'];
		// }

?>      
			  
<style>
.feneteconf-root{
  position: fixed;        /* hors du flux et au niveau viewport */
  inset: 0;               /* top/right/bottom/left: 0 */
  display: flex; 
  align-items: center; 
  justify-content: center;
  z-index: 2147483647 !important;
  pointer-events: none;   /* le conteneur n’attrape pas les clics */
}
.feneteconf-root *{ pointer-events: auto; }  /* mais le contenu oui */

/* Si c’est un modal Bootstrap */
.modal{ z-index: 2147483647 !important; }
.modal-backdrop{ z-index: 2147483646 !important; }

/* Sécurité : rien d'autre ne doit capter les clics au-dessus */
.curved-decoration,
.svg-decoration-container,
.gradient-overlay::before { pointer-events:none; }
</style>


         <section class="gradient-overlay gradient-overlay-dark ">
            <img class="bg-image" src="../couple/images/<?php echo $photo; ?>" alt="">
            <div class="container">
               <!--row-->
               <div class="row min-vh-80 align-items-center z-index-2 position-relative text-center">
                  <div class="col-12">

    




<?php

if (isset($_GET['idinv'])) {

    // Préparer la requête pour récupérer l'invité
    $stmt2x = $pdo->prepare("SELECT * FROM invite WHERE id_inv = :id_inv");
    $stmt2x->execute([':id_inv' => $_GET['idinv']]);
    $datainvite = $stmt2x->fetch(PDO::FETCH_ASSOC);

    if ($datainvite) {

        $sing = match ($datainvite['sing']) {
            'C' => 'Couple',
            'Mr' => 'Monsieur',
            'Mme' => 'Madame',
            default => '',
        };

        // Construire le nom
        $nominv = $sing . ' ' . ucfirst($datainvite['nom']);

        // ✅ IMPORTANT : décoder les entités HTML (&amp; → &)
        $nominv = html_entity_decode($nominv, ENT_QUOTES, 'UTF-8');

        // Vérifier si l'utilisateur a déjà confirmé
        $stmt3x = $pdo->prepare("SELECT presence, date_enreg FROM confirmation WHERE noms = :noms AND cod_mar = :cod_mar");
        $stmt3x->bindValue(':noms', $datainvite['nom']);
        $stmt3x->bindValue(':cod_mar', $_GET['cod']); 
        $stmt3x->execute();
        $dataconfinv = $stmt3x->fetch(PDO::FETCH_ASSOC);

        $presence = $dataconfinv['presence'] ?? '';

        $confirmationMessage = match ($presence) {
            'oui' => 'votre présence avait déjà été confirmée pour le mariage de',
            'non' => 'votre non participation avait déjà été enregistrée pour le mariage de',
            default => 'statut de présence inconnu',
        };

        // Date d'enregistrement sécurisée
        $dateEnreg = isset($dataconfinv['date_enreg']) 
            ? date('d/m/Y', strtotime($dataconfinv['date_enreg'])) 
            : 'date inconnue';

        // ✅ Affichage sécurisé (évite XSS)
        echo '<h5 style="color:#fff;text-align:center;"><b>' 
            . htmlspecialchars($nominv, ENT_QUOTES, 'UTF-8') 
            . '</b>, ' . $confirmationMessage . '</h5>';

    }

}
?>










                     <h1 class="display-2  text-white mb-4"><?php echo $fetard; ?></h1>
 

					<p class="font-weight-300 text-light  lead mb-5 pcompteeur">Se marient dans</p>
                    
 
					  <?php include('comptearebour.php')?>


 
					  <a href="#rsvp" class="btn btn-primary btn-wide-sm btn-sm  scroll">RSVP</a>
                  </div>
               </div>
               <!--End row-->
            </div>
            <!--End container-->
            <div class="curved-decoration ">


              <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
    viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M2560,0c0,0-219.6543,165.951-730.788,124.0771c-383.3156-31.4028-827.2138-96.9514-1244.7139-96.9514
      c-212.5106,0-439,3.5-584.4982,1.5844l0,139.9126h2560V0z"/>
</g>
</svg>
            </div>
         </section>
         <!--End hero section-->






 
      
      
 




<?php
 
$date = new DateTime($date_event);

// Création d'un formatteur pour le français
$formatter = new IntlDateFormatter(
    'fr_FR',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    null,
    IntlDateFormatter::GREGORIAN,
    'EEEE, dd MMMM yyyy à HH:mm' // Utiliser HH pour le format 24 heures
);

// Formatage de la date
$formatted_date = $formatter->format($date);

// Met la première lettre en majuscule
$formatted_date = ucfirst($formatted_date); 



?>

         <!--Date section-->
         <section id="resto" class=" ">
            <div class="container spacer-double-lg">
               <div class="row justify-content-lg-between align-items-center">
                  <div class="col-md-6 mb-5 mb-md-0">
                     <div class="pr-md-4">
                        <h1 class="font-secondary display-4">Save the date</h1>
                        <p class="lead font-weight-300 text-dark-gray opacity-8"><?php echo $formatted_date; ?><br> <?php echo $lieu; ?></p>
                        
<?php 
// Préparation et exécution de la requête
$stmtss = $pdo->prepare("SELECT * FROM websitewedgeneral WHERE cod_event = :cod_event");
$stmtss->execute(['cod_event' => $codevent]);

// Récupération du résultat
$savetexte = $stmtss->fetch(PDO::FETCH_ASSOC);

if ($savetexte && !empty($savetexte['text_sdd'])): ?>
    
    <p class="mb-4 pr-5">
        <?= nl2br(htmlspecialchars($savetexte['text_sdd'], ENT_QUOTES, 'UTF-8')) ?>
    </p>

<?php else: ?>

    <p class="mb-4 pr-5">
        Le moment tant attendu est enfin arrivé, 
        <?= htmlspecialchars($dataevent['prenom_epoux'], ENT_QUOTES, 'UTF-8') ?> 
        a pris son courage et a demandé la main de 
        <?= htmlspecialchars($dataevent['prenom_epouse'], ENT_QUOTES, 'UTF-8') ?>. 
        Ce geste, chargé d’émotion et symbolique, marque le début d’un nouveau chapitre de leur histoire.
    </p>

<?php endif; ?>
                     

                        
                  
                  <div class="countdown row text-center"></div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="svg-mask-container">
                        <svg version="1.1" id="p.svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 980.2914 884.5557" enable-background="new 0 0 980.2914 884.5557" xml:space="preserve">
                           <path opacity="0.05" fill="#E35D5D" d="M493.9887,130.1238L493.9887,130.1238C419.5373,4.797,257.8407-37.3296,131.6182,36.1312
                              C4.3062,110.2269-37.5245,274.4576,36.6059,401.6698l215.8423,370.3983c28.1165,48.2495,90.0233,64.5706,138.2729,36.4541
                              l368.4115-214.6845c127.2958-74.1791,172.7084-240.1479,98.7059-367.5465C783.976,99.1337,621.0024,56.1091,493.9887,130.1238z" />
                           <path fill="none" stroke="#E35D5D" stroke-width="3.1978" stroke-miterlimit="10" d="M151.0414,716.6817L151.0414,716.6817
                              c-2.6854-4.5204-8.5175-6.0399-13.0702-3.3902c-4.592,2.6725-6.1008,8.5961-3.427,13.1844l7.7851,13.3598
                              c1.0141,1.7403,3.247,2.3289,4.9873,1.3148l13.2881-7.7433c4.5914-2.6756,6.2294-8.6618,3.5602-13.257
                              C161.5008,715.5639,155.6226,714.0121,151.0414,716.6817z" />
                           <path fill="none" stroke="#E35D5D" stroke-width="2.7402" stroke-miterlimit="10" d="M186.6779,44.1803L186.6779,44.1803
                              c-1.4613-2.4599-4.635-3.2867-7.1125-1.8449c-2.4988,1.4543-3.3199,4.6778-1.8649,7.1747l4.2365,7.2701
                              c0.5519,0.947,1.767,1.2674,2.714,0.7155l7.2311-4.2138c2.4985-1.456,3.3899-4.7136,1.9374-7.2141
                              C192.3697,43.572,189.1709,42.7276,186.6779,44.1803z" />
                           <path fill="none" stroke="#E35D5D" stroke-width="2.5752" stroke-miterlimit="10" d="M978.3951,400.8979
                              c-0.5042,0.934-0.5042,2.0591,0,2.993c0.3052,0.4453,0.5839,0.9083,0.835,1.3862c0.5513,1.3705-0.4096,2.8669-1.3862,3.9854
                              c-0.6144,0.7089-1.3075,1.6697-0.8979,2.5203c0.1919,0.3133,0.4371,0.5904,0.7246,0.8192c2.0887,2.0325,2.1343,5.3734,0.1018,7.4622
                              c-0.5895,0.6058-1.3159,1.0612-2.1182,1.3277" />
                           <defs>
                              <mask id="mask">
                                 <path fill="#ffffff" d="M528.4855,192.3897L528.4855,192.3897C454.034,67.0629,292.3375,24.9363,166.115,98.3971
                                    C38.8029,172.4928-3.0277,336.7236,71.1027,463.9358L286.945,834.3341c28.1165,48.2495,90.0233,64.5706,138.2729,36.4541
                                    l368.4115-214.6845c127.2958-74.1792,172.7084-240.1479,98.7059-367.5465C818.4728,161.3996,655.4992,118.375,528.4855,192.3897z" />
                              </mask>
                           </defs>
                        
                           <image mask="url(#mask)" style="width:950px;" xlink:href="../couple/images/<?php echo $photocoeur;?>" />
                           <g>
                              <path fill="none" stroke="#E35D5D" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M747.8805,524.349
                                 c108.1955-68.637,139.1927-213.677,70.5558-321.8725S606.4696,62.2462,498.2741,130.8832l0,0" />
                           </g>
                        </svg>
                     </div>
                  </div>
               </div>
            </div>
            <div class="curved-decoration">
<svg  width="100%" height="100%" class="bg-secondary-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
    viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M0,0c0,0,219.6543,165.951,730.788,124.0771c383.3156-31.4028,827.2139-96.9514,1244.7139-96.9514
      c212.5106,0,438.9999,3.5,584.4982,1.5844v139.9126H0V0z"/>
</g>
</svg>
            </div>
         </section>









		<?php // include('histoire.php')?>
		<?php // include('benediction.php')?>
		<?php // include('cadeaux.php')?>
 

<?php 

$stmtlove = $pdo->prepare("SELECT * FROM lovestory WHERE cod_event = ?");
$stmtlove->execute([$codevent]);
$datalove = $stmtlove->fetch(PDO::FETCH_ASSOC);

   if ($datalove) {

?>

    <?php include('lovestory.php'); ?>
    <?php include('sectiongalerie.php'); ?>

<?php 

   }

?>



		








	
      <?php 					
						
         if (isset($_GET['idinv'])) {
            
		?>
 		

         		
		<?php

         }else{

		?>
 
         
         <section id="rsvp" class="bg-secondary spacer-one-top-lg o-hidden ">
            <!--Container-->
            <div class="container spacer-one-bottom-lg">
               <!--Row-->
               <div class="row justify-content-center">
                  <div class="col">
                     <div class=" mb-5 pb-5 text-center">
                        <h1 class="display-4 ">RSVP</h1>
                        <p class="w-md-40 mb-0 mx-auto text-dark-gray opacity-8">Allez-vous y assister ?</p>








						                 
<?php


if(isset($_POST['submitrsvp'])){
    
   $nom = @$_POST['nom'];
   $phone = @$_POST['phone'];
   $presence = @$_POST['presence'];
   $note = @$_POST['note'];
   
   $codmar = trim($_GET['cod']);
   $emailinvite = @$_POST['email'];
   
   if (!$nom) {
       $error = 'Remplissez le nom';
       echo '<span style="color:red;">'.$error.'</span>';
   } elseif (!$phone) {
       $error = 'Remplissez le numéro de téléphone';
       echo '<span style="color:red;">'.$error.'</span>';
   } elseif (!$emailinvite) {
       $emailinvite = 'nonmail@is.com';
   } elseif (!$presence) {
       $error = 'Confirmer votre présence ou absence';
       echo '<span style="color:red;">'.$error.'</span>';
   } else {
       // Vérifier si l'utilisateur a déjà confirmé
       $checkSql = 'SELECT COUNT(*) FROM confirmation WHERE email = :email OR phone = :phone';
       $checkQ = $pdo->prepare($checkSql);
       $checkQ->bindValue(':email', $emailinvite);
       $checkQ->bindValue(':phone', $phone);
       $checkQ->execute();
       $count = $checkQ->fetchColumn();
       $checkQ->closeCursor();
   
       if ($count > 0) {
           echo '<span style="color:red;">Vous avez déjà confirmé votre présence.</span>';
       } else {
           $sql = 'INSERT INTO confirmation (
               cod_mar,
               noms,
               email,
               phone,
               presence,
               note,
               date_enreg)
           VALUES (
               :cod_mar,
               :noms,
               :email,
               :phone,
               :presence,
               :note,
               NOW())';
   
           $q = $pdo->prepare($sql);
           $q->bindValue(':cod_mar', $codmar);
           $q->bindValue(':noms', $nom);
           $q->bindValue(':email', $emailinvite);
           $q->bindValue(':phone', $phone);
           $q->bindValue(':presence', $presence);
           $q->bindValue(':note', $note);
           $q->execute();
           $q->closeCursor();




         // Envoi de l'email
         $speudo = $nom;
         $email = $emailinvite; // Assurez-vous que cette variable contient une adresse email valide
         $subject = strtoupper($fetard.' RSVP');
         $message = "Bonjour $speudo,\n\nVotre reservation nous est parvenue avec succès.\n\nMerci!";
         
         // Ajout des en-têtes pour le format et l'encodage
         $headers = "From: contact@invitationspeciale.com\r\n";  // Adresse d'envoi
         $headers .= "Reply-To: contact@invitationspeciale.com\r\n"; // Adresse de réponse
         $headers .= "MIME-Version: 1.0\r\n";
         $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
         $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    
         if (mail($email, $subject, $message, $headers)) {

				// Si l'envoi est réussi, afficher l'alerte 
				echo '<script>
					Swal.fire({
						title: "RSVP!",
						text: "Votre présence a été confirmée avec succès",
						icon: "success",
						confirmButtonText: "OK"
					});
				</script>';
 
			}else{
				// Si l'envoi échoue, afficher l'erreur
				echo "Le message n'a pas pu être envoyé"; 
				echo '<script>
					Swal.fire({
						title: "Erreur!",
						text: "Le message n\'a pas pu être envoyé",
						icon: "error",
						confirmButtonText: "OK"
					});
				</script>';
			}
		
    
       }
   }
     


 






    	

 
}

 





?>











                     </div>
                  </div>
               </div>
               <!--End row-->




















               






         
               <div class="row justify-content-center">
                  <div class="col-xl-5 col-lg-5 col-md-6">
                     <form method="post" action="">
                        <div class="form-group">
                           <label >Nom et prénom</label>
                           <input name="nom" type="text" class="form-control form-control-lg" value="<?php echo @$_POST['nom']?>"  placeholder="Votre prénom et nom">
                        </div>
                        <div class="form-group">
                           <label>Téléphone</label> 
							<input type="text" name="phone" value="<?php echo @$_POST['phone'] ?>" 
								
								value="<?php echo @$_POST['phone']?>"  class="form-control form-control-lg" placeholder="Votre téléphone">
								
                        </div>
                        <div class="form-group">
                           <label>Adresse e-mail</label>
                           <input name="email" type="email" value="<?php echo @$_POST['email']?>"  class="form-control form-control-lg" placeholder="Votre email">
                        </div>
                        <div class="row">
                           <div class="col-12">
                              <div class="form-group mb-1">
                                 <label>Participer</label>
                              </div>
                           </div>
                           <div class="col">
                              <div class="form-group rounded bg-white p-2 border">
                                 <div class="custom-control custom-radio">
                                    <input type="radio" id="atttending-yes" name="presence" value="oui" class="custom-control-input">
                                    <label class="custom-control-label" for="atttending-yes">Oui, j'y serai</label>
                                 </div>
                              </div>
                           </div>
                           <div class="col">
                              <div class="form-group rounded bg-white p-2 border">
                                 <div class="custom-control custom-radio">
                                    <input type="radio" id="atttending-no" name="presence" value="non" class="custom-control-input">
                                    <label class="custom-control-label" for="atttending-no">Désolé, je n'y serai pas</label>
                                 </div>
                              </div>
                           </div>
                        </div>
                        
               <style>

                  textarea.form-control {
                  height: auto; /* Laissez la hauteur automatique pour que rows fonctionne */
                  min-height: 100px; /* Définissez une hauteur minimale */
                  resize: none; /* Empêche le redimensionnement */
                  }

               </style>

                        <div class="form-group">
                           <label>Message</label>
                           <textarea name="note" class="form-control form-control-lg" rows="3" placeholder="Votre message"><?php echo @$_POST['note']?></textarea>
                        </div>

                        <div class="form-group text-center">
                           <button class="btn btn-lg btn-block btn-primary" name="submitrsvp" type="submit">Repondre</button>
                           <!-- <small class="mt-2 text-dark-gray opacity-8">Vous recevrez un email de confirmation.</small> -->
                        </div>
                     </form>
                  </div>
               </div>
            </div>
            <!--End container-->
            <div class="curved-decoration">
               <svg width="100%" height="100%" class="bg-white-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M0,0c0,0,219.6543,165.951,730.788,124.0771c383.3156-31.4028,827.2139-96.9514,1244.7139-96.9514
      c212.5106,0,438.9999,3.5,584.4982,1.5844v139.9126H0V0z"></path>
</g>
</svg>
            </div>
            <div class="svg-decoration-container">
               <div class="svg-decoration middle-left ">
                  <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 135.7427 125.4471" enable-background="new 0 0 135.7427 125.4471" xml:space="preserve">
                     <g>
                        <path fill="#E25D5D" d="M46.3709,123.4824c-0.718-1.036-1.9904-1.1435-3.1008-1.5854c-1.2856-0.5117-2.4638-1.1784-3.5942-1.9727
                           c-1.5318-1.0764-3.2602,1.2297-1.742,2.334c1.2495,0.9086,2.5876,1.7014,4.0061,2.3174c1.2267,0.5326,2.7301,1.2758,4.0077,0.6047
                           C46.6049,124.8354,46.7955,124.0952,46.3709,123.4824z" />
                        <path fill="#E25D5D" d="M53.0453,115.3059c-4.4957-1.9741-16.933-9.8289-17.1288,0.5154c-0.0176,0.9304-1.339,0.8885-0.5229,1.3398
                           c4.3032,2.3803,8.8232,4.1526,13.4673,5.7454C50.2921,123.3976,54.4329,115.9152,53.0453,115.3059z" />
                        <path fill="#E25D5D" d="M62.0153,116.0416c-2.2559-8.7056-23.1343-17.659-29.0229-12.8535c-1.0698,0.873-2.0786,1.41-0.7908,2.0262
                           c9.4616,4.5257,18.9397,8.7919,28.2448,13.6547C62.0641,119.7142,62.4759,117.819,62.0153,116.0416z" />
                        <path fill="#E25D5D" d="M76.0122,107.4455c-5.951-10.7841-44.442-25.2437-50.1732-15.1986
                           c-0.8961,1.5705-2.7192,1.8645-1.033,2.647c15.2403,7.0732,31.1254,13.0623,46.6307,19.5559
                           C73.8316,115.4529,77.2432,109.6764,76.0122,107.4455z" />
                        <path fill="#E25D5D" d="M83.7416,106.7034C78.3731,94.7113,29.5014,64.5428,19.746,80.4405
                           c-0.7308,1.191-1.9928,1.3809-0.778,1.9935c20.5406,10.3589,42.2243,18.8136,63.4472,27.668
                           C84.5853,111.0076,84.6997,108.8435,83.7416,106.7034z" />
                        <path fill="#E25D5D" d="M98.5164,101.8162C75.9046,83.6435,29.9653,64.7263,11.4555,66.8863
                           c-1.4692,0.1715-2.1442,1.4899-0.829,2.1242C38.748,82.571,68.3512,93.2941,97.2411,105.084
                           C99.3326,105.9376,100.2773,103.2313,98.5164,101.8162z" />
                        <path fill="#E25D5D" d="M107.1592,96.1165C83.8287,79.3572,34.7819,55.1261,5.8419,55.6944
                           c-1.5715,0.0309-2.194,1.5156-0.8673,2.2222c31.9451,17.0142,66.9155,29.2015,100.7179,41.958
                           C108.1199,100.7907,109.2719,97.6342,107.1592,96.1165z" />
                        <path fill="#E25D5D" d="M114.6054,91.6748c-18.5585-7.6446-37.8352-19.8791-56.4415-27.4064
                           c-18.5205-7.4928-36.2257-11.488-55.3145-17.4242c-1.1381-0.3538-1.693,1.1593-0.6632,1.6994
                           c17.7171,9.2916,36.8515,16.1962,55.2638,23.9894c18.4876,7.8251,37,15.5839,55.5358,23.2921
                           C115.6339,96.9261,117.2585,92.7675,114.6054,91.6748z" />
                        <path fill="#E25D5D" d="M122.1404,85.1791c-20.1722-8.1227-46.2771-24.6321-66.3275-33.0562
                           C35.8548,43.7373,22.0684,42.4468,1.59,35.409c-1.1488-0.3948-2.2602,1.2992-1.0966,1.976
                           c18.2497,10.615,38.3164,18.5188,57.6704,26.8833c20.5605,8.8859,41.2186,17.6126,62.1146,25.6818
                           C123.3536,91.1378,125.2017,86.4118,122.1404,85.1791z" />
                        <path fill="#E25D5D" d="M125.8786,79.2283c-20.4912-8.5128-39.3423-23.3619-59.7019-32.1852
                           c-20.2289-8.7668-41.822-12.9998-62.7254-20.0257c-1.4301-0.4806-2.8188,1.6116-1.3664,2.4625
                           c18.5942,10.8928,39.283,18.7518,58.9938,27.3905c20.9225,9.1696,41.9389,18.119,63.0272,26.9002
                           C127.0041,84.9774,128.7778,80.4327,125.8786,79.2283z" />
                        <path fill="#E25D5D" d="M130.2242,73.6163c-20.2535-10.3398-41.0253-19.7456-61.8777-28.8054
                           C47.6527,35.82,26.5998,25.9044,4.8377,19.8079c-1.5911-0.4457-2.4066,1.6343-0.931,2.3856
                           c20.225,10.2978,41.8862,18.0369,62.5945,27.3457c20.7709,9.3368,41.2042,19.3354,61.7207,29.2078
                           C131.4383,80.2947,133.338,75.2059,130.2242,73.6163z" />
                        <path fill="#E25D5D" d="M133.7529,64.5225C92.9384,44.6669,51.2215,25.7029,7.7763,12.2997
                           c-2.0049-0.6184-3.1193,2.1105-1.1989,3.0718c41.237,20.6429,83.5807,36.8986,125.1858,54.2489
                           C134.9886,70.9654,136.9225,66.0644,133.7529,64.5225z" />
                        <path fill="#E25D5D" d="M134.2905,55.923C94.9692,38.3296,54.6778,19.4975,13.2622,7.3866
                           c-1.3778-0.4028-2.1423,1.4799-0.8162,2.0915c39.4496,18.1936,80.5137,32.9042,120.0718,50.9872
                           C135.3698,61.7691,137.153,57.2038,134.2905,55.923z" />
                        <path fill="#E25D5D" d="M67.0403,21.4345C51.8222,14.2468,35.3193,7.9447,19.1524,3.2534
                           c-1.6513-0.4791-2.5117,1.7162-0.9693,2.4837c15.0681,7.4986,31.4775,14.0419,47.5436,19.0634
                           C67.9489,25.4949,69.1491,22.4304,67.0403,21.4345z" />
                        <path fill="#E25D5D" d="M57.3122,11.6418C57.4051,8.9267,34.2234-3.2668,28.3663,0.8357c-1.0556,0.7394-1.444,1.5195-0.4906,2.0276
                           c8.8657,4.7246,18.7788,8.4473,28.1739,12.0138C58.1769,15.6846,57.2371,13.8362,57.3122,11.6418z" />
                        <path fill="#E25D5D" d="M133.1648,47.7784c-14.3374-6.7709-29.4575-12.6714-44.4443-17.8448
                           c-1.916-0.6615-2.9336,2.0409-1.1351,2.9084c14.2838,6.8894,29.1755,13.3631,44.1255,18.6617
                           C134.1552,52.3701,135.4792,48.8715,133.1648,47.7784z" />
                        <path fill="#E25D5D" d="M128.0374,37.178c-8.7472-3.7643-17.9131-7.8857-27.2702-9.8305c-1.6177-0.336-1.8544,1.9524-0.63,2.6019
                           c8.3756,4.4434,17.5773,7.6076,26.2422,11.4769C129.0562,42.6215,130.7358,38.3392,128.0374,37.178z" />
                     </g>
                  </svg>
               </div>
               <div class="svg-decoration bottom-right">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 61.0903 58.3236" enable-background="new 0 0 61.0903 58.3236" xml:space="preserve">
                     <g>
                        <g>
                           <path fill="#E25D5D" d="M35.9444,45.3305c-10.4863,0.4059-23.3154,1.1544-24.786-9.9408
                              c-0.0596-0.4498-2.4276,1.2219-2.4493,0.7422c-0.0445-0.9828-2.2473-1.9201-2.3009-2.8204c-0.026-0.4362,0.95-5.4905,0.925-5.9088
                              c-0.613-10.2666,2.9679-15.6386,18.7795-22.1382" />
                           <g>
                              <path fill="#E25D5D" d="M17.5292,2.3522c-3.0514,1.7301-4.9803,2.7768-5.9291,3.8411c-2.3624,2.6499-2.0799,2.8104-3.8628,5.1073
                                 c-0.9299,1.198-2.1649,3.3742-2.8958,4.6846c-1.5784,2.8298-3.161,5.0214-4.1949,7.9994
                                 c-0.1643,0.4732-0.287,1.1995-0.344,1.6845c-0.6135,5.2182-0.392,10.2644,1.5788,15.0433
                                 c1.0541,2.556,2.3881,4.0704,4.1426,6.0135c1.5003,1.6615,2.9967,2.7417,4.9881,4.0083
                                 c1.9914,1.2666,4.2598,2.4221,6.6644,3.3156c4.0993,1.5233,8.6838,2.8517,13.0389,3.4698
                                 c0.9472,0.1344,1.3667,0.3425,1.8013-0.118c0.1304-0.1381-0.2267,0.08,0,0c0.2267-0.08,0.8197,0.0119,1.2083,0.0417
                                 c0.1177,0.009,1.3635,0.1445,1.5,0.1667c0.3615,0.0587-0.5167-0.1693,0,0c0.2272,0.0744,0.5132-0.1127,0.7193-0.0417
                                 c0.2106,0.0726,0.7986-0.0688,0.9891,0c0.1507,0.0544,3.2895,0.7708,0.9236-0.1166c-0.4224-0.1584,1.9838,0.8688,3.0906,0.8717
                                 c7.957,0.0214,10.8035-2.7496,18.2347-5.2135C68.111,50.1496,44.2658-12.8068,17.5292,2.3522z M20.8936,43.3265
                                 c-0.1999-0.0728-0.7859,0.0706-0.9847-0.0044c-0.1874-0.0707-0.199-1.1271-0.3852-1.1999
                                 c-0.1944-0.076-0.3881-0.153-0.5808-0.2313c-0.1956-0.0794-0.3902-0.16-0.5839-0.2419c-0.2364-0.1,21.2113,0.4893,20.9783,0.3854
                                 c-0.1281-0.0571-21.9761-0.7566-22.0651-0.8643c-3.3676-4.0733-8.5622-5.1499-9.5219-9.8847
                                 c-0.4181-2.0628,0.3013-3.9592,0.1423-5.306c-0.0724-0.6131,0.9769-1.2124,0.9649-1.7231
                                 c-0.0128-0.5442-1.7365-1.1933-1.5226-1.5587c0.3137-0.5356,0.7814-0.721,1.003-1.2618
                                 c0.1555-0.3795-0.7241-0.8316-0.4942-1.2088c0.1826-0.2997,0.3894-0.6032,0.6213-0.9176c0.2154-0.292,0.4525-0.5934,0.7121-0.91
                                 c0.2411-0.294,0.5017-0.6011,0.7823-0.9259c0.2952-0.3416,0.6127-0.7028,0.9532-1.0889
                                 c0.2928-0.3319,0.6025-0.6823,0.9298-1.0544c0.2545-0.2894,0.5196-0.5919,0.7955-0.9092
                                 c0.3291-0.3784,0.6735-0.7779,1.0337-1.2011c0.4514-0.5303-0.1945-1.491,0.3072-2.1011
                                 c0.3813-0.4636,2.0382,0.6636,2.4493,0.1484c0.4176-0.5232,0.7385,0.2101,1.1876-0.3711
                                 c0.2728-0.353,1.1256-1.4054,1.4102-1.7813 M30.2825,41.0306c0,0-1.2261,4.206-1.5216,4.1193
                                 c-0.2425-0.0711-1.2009-0.4472-1.4473-0.5196c-0.1911-0.0561-0.4377-0.0544-0.6309-0.1113c-0.2012-0.0593,0.2031,0.0603,0,0
                                 c-0.5116-0.152-1.5851-0.8654-2.1052-1.0255c-0.1648-0.0507-0.3299-0.1019-0.4952-0.1536
                                 c-0.3719-0.1163,4.1062,2.3197,3.7138,2.3295c-0.1685,0.0042-5.1664-2.7959-5.3336-2.8516 M60.157,48.2302" />
                           </g>
                        </g>
                     </g>
                  </svg>
               </div>
            </div>
         </section>
     
         



		 
							
		<?php  

               }


                  ?>




























 <?php 	 if ($codevent === '375') { ?>


 <section id="story" class=" bg-secondary spacer-one-top-lg">
            <!--Container-->
            <div class="container spacer-one-bottom-lg">
               <!--Row-->
               <div class="row justify-content-center">
                  <div class="col">
                     <div class=" text-center mb-5 pb-5">
                        <h1 class="display-4 mb-0">Love Story</h1>
                        <p class="w-md-40 mb-0 mx-auto text-dark-gray opacity-8 ">
                           Il était une fois, Julia et David… 14 Mai 2022.
C’est ici que tout a commencé, Une rencontre de courtoisie qui a donné naissance à une belle histoire d’amour…
Deux regards tournés vers l’avenir pour ne plus jamais se quitter …

</p>
 
                     </div>
                  </div>
               </div>
               <!--End row-->
               <div class="row  justify-content-center">
                  <div class="col-lg-12 text-center">
                     <div class="mb-3 pb-3">
                        <h5 class="mb-0"> Quand <?php echo $dataevent['prenom_epoux']?> a rencontré <?php echo $dataevent['prenom_epouse']?> </h5>
                        <span class="small text-dark-gray opacity-8">Ce jour a tout changé</span>
                     </div>
                  </div>
                  <div class="col-md-3 d-flex flex-column align-items-center">
                     <div class="mb-3 pb-3 px-4">
                        <div class="svg-mask-container">
                           <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 607 532" enable-background="new 0 0 607 532" xml:space="preserve">
                              <defs>
                                 <mask id="mask-small-1">
                                    <path fill="#FFFFFF" d="M303.2954,55.7655L303.2954,55.7655c-68.9887-68.3032-180.269-68.3601-249.3155,0.1402
                                       c-69.6423,69.0921-69.0838,181.9562,0.0139,251.5284l201.1888,202.5704c26.2076,26.3876,68.8445,26.5335,95.2321,0.3258
                                       l201.4838-200.1096c69.6179-69.1431,71.0768-183.7278,2.0303-253.4415C485.013-12.8027,372.7591-13.2244,303.2954,55.7655z" />
                                 </mask>
                              </defs>
                              <image mask="url(#mask-small-1)" width="100%" xlink:href="../couple/images/imagejulia01.png" />
                           </svg>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col d-flex flex-column align-items-center">
                     <ol class="story mb-0">
                        <li>
                           <div class="story-icon bg-icon-primary">
                              <svg version="1.1" class="icon-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 107 93" enable-background="new 0 0 107 93" xml:space="preserve">
                                 <path fill="#E25D5D" d="M53.6825,9.4436L53.6825,9.4436C41.6532-2.4793,22.2392-2.5,10.1866,9.4439
                                    c-12.1565,12.0471-12.07,31.7375-0.0219,43.8819l35.08,35.3601c4.5697,4.6061,12.0081,4.6357,16.6142,0.0661l35.1704-34.8918
                                    c12.1523-12.056,12.4179-32.0464,0.3787-44.2154C85.3917-2.5013,65.8078-2.5857,53.6825,9.4436z" />
                              </svg>
                           </div>
                           <div>
                              <h5 class="mb-0">Première rencontre</h5>
                              <span class="small text-primary">Avril 2022</span>
                           </div>
                        </li>
                        <li>
                           <div class="story-icon bg-icon-primary">
                              <svg version="1.1" class="icon-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 107 93" enable-background="new 0 0 107 93" xml:space="preserve">
                                 <path fill="#E25D5D" d="M53.6825,9.4436L53.6825,9.4436C41.6532-2.4793,22.2392-2.5,10.1866,9.4439
                                    c-12.1565,12.0471-12.07,31.7375-0.0219,43.8819l35.08,35.3601c4.5697,4.6061,12.0081,4.6357,16.6142,0.0661l35.1704-34.8918
                                    c12.1523-12.056,12.4179-32.0464,0.3787-44.2154C85.3917-2.5013,65.8078-2.5857,53.6825,9.4436z" />
                              </svg>
                           </div>
                           <div>
                              <h5 class="mb-0">Premier rendez-vous</h5>
                              <span class="small text-primary">Mai 2022</span>
                           </div>
                        </li>
                        <li>
                           <div class="story-icon bg-icon-primary">
                              <svg  version="1.1" class="icon-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 107 93" enable-background="new 0 0 107 93" xml:space="preserve">
                                 <path fill="#E25D5D" d="M53.6825,9.4436L53.6825,9.4436C41.6532-2.4793,22.2392-2.5,10.1866,9.4439
                                    c-12.1565,12.0471-12.07,31.7375-0.0219,43.8819l35.08,35.3601c4.5697,4.6061,12.0081,4.6357,16.6142,0.0661l35.1704-34.8918
                                    c12.1523-12.056,12.4179-32.0464,0.3787-44.2154C85.3917-2.5013,65.8078-2.5857,53.6825,9.4436z" />
                              </svg>
                           </div>
                           <div>
                              <h5 class="mb-0">L'amour a commencé</h5>
                              <span class="small text-primary">Mai 2022</span>
                           </div>
                        </li>
                        <li>
                           <div class="story-icon bg-icon-primary">
                              <svg version="1.1" class="icon-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 107 93" enable-background="new 0 0 107 93" xml:space="preserve">
                                 <path fill="#E25D5D" d="M53.6825,9.4436L53.6825,9.4436C41.6532-2.4793,22.2392-2.5,10.1866,9.4439
                                    c-12.1565,12.0471-12.07,31.7375-0.0219,43.8819l35.08,35.3601c4.5697,4.6061,12.0081,4.6357,16.6142,0.0661l35.1704-34.8918
                                    c12.1523-12.056,12.4179-32.0464,0.3787-44.2154C85.3917-2.5013,65.8078-2.5857,53.6825,9.4436z" />
                              </svg>
                           </div>
                           <div>
                              <h5 class="mb-0">Ensemble depuis</h5>
                              <span class="small text-primary">Juin 2022</span>
                           </div>
                        </li>
                        <li>
                           <div class="story-icon bg-icon-primary">
                              <svg version="1.1" class="icon-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 107 93" enable-background="new 0 0 107 93" xml:space="preserve">
                                 <path fill="#E25D5D" d="M53.6825,9.4436L53.6825,9.4436C41.6532-2.4793,22.2392-2.5,10.1866,9.4439
                                    c-12.1565,12.0471-12.07,31.7375-0.0219,43.8819l35.08,35.3601c4.5697,4.6061,12.0081,4.6357,16.6142,0.0661l35.1704-34.8918
                                    c12.1523-12.056,12.4179-32.0464,0.3787-44.2154C85.3917-2.5013,65.8078-2.5857,53.6825,9.4436z" />
                              </svg>
                           </div>
                           <div>

                              <h5 class="mb-0">Il a proposé</h5>
                              <span class="small text-primary">Juillet 2025</span>

                           </div>
                        </li>
                     </ol>
                  </div>
               </div>
               <div class="row  justify-content-center">
                  <div class="col-md-3 d-flex flex-column align-items-center">
                     <div class="mb-3 pb-3 mt-3 pt-3 px-4">
                        <div class="svg-mask-container">
                           <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 607 532" enable-background="new 0 0 607 532" xml:space="preserve">
                              <defs>
                                 <mask id="mask-small-2">
                                    <path fill="#FFFFFF" d="M303.2954,55.7655L303.2954,55.7655c-68.9887-68.3032-180.269-68.3601-249.3155,0.1402
                                       c-69.6423,69.0921-69.0838,181.9562,0.0139,251.5284l201.1888,202.5704c26.2076,26.3876,68.8445,26.5335,95.2321,0.3258
                                       l201.4838-200.1096c69.6179-69.1431,71.0768-183.7278,2.0303-253.4415C485.013-12.8027,372.7591-13.2244,303.2954,55.7655z" />
                                 </mask>
                              </defs>
                              <image mask="url(#mask-small-2)" width="100%" xlink:href="../couple/images/DSC03547.jpg" />
                           </svg>
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-12 text-center">
                     <div class="">
                        <h5 class="mb-0"> Fin heureuse, nous nous marions</h5>
                        <span class="small text-dark-gray opacity-8">Comptez les jours...</span>
                     </div>
                  </div>
               </div>
            </div>
            <!--End container-->
            <div class="curved-decoration ">
               <svg width="100%" height="100%" class="bg-white-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
    viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M2560,0c0,0-219.6543,165.951-730.788,124.0771c-383.3156-31.4028-827.2138-96.9514-1244.7139-96.9514
      c-212.5106,0-439,3.5-584.4982,1.5844l0,139.9126h2560V0z"/>
</g>
</svg>
            </div>
         </section>
























           <section id="wedding" class="spacer-one-top-lg">
            <!--Container-->
            <div class="container spacer-one-bottom-lg">
               <!--Row-->
               <div class="row justify-content-center">
                  <div class="col">
                     <div class=" text-center mb-5 pb-5">
                        <h1 class="display-4 mb-0">Événements de mariage</h1>
                        <p class="w-md-40 mb-0 mx-auto text-dark-gray opacity-8 ">Cérémonie principale - Fête de mariage</p>
                     </div>
                  </div>
               </div>
               <!--End row-->
               <!--Row-->
               <div class="row">
                  <div class="col-md-6 col-lg-4 d-flex mb-4 mb-lg-0 display-state ">
                     <div class="card card-icon-2 card-body justify-content-between">
                        <img class="bg-image" src="../couple/images/<?php echo $photocoeur;?>" alt="">
                     </div>
                  </div>
                  <div class="col-md-6 col-lg-4 d-flex mb-4 mb-lg-0">
                     <div class="card card-body justify-content-between">
                        <div class="icon-round mb-5 mb-md-5  bg-icon-primary">
                           <svg class="icon-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 156.016 144" enable-background="new 0 0 156.016 144" xml:space="preserve">
                              <g>
                                 <g>
                                    <g>
                                       <path fill="#E25D5D" d="M46.0314,41.7797l-2.7211-2.7211c-1.0296-1.0296-2.6989-1.0296-3.7282,0
                                          c-1.0296,1.0296-1.0296,2.6986,0,3.7282l2.7211,2.7211c0.5147,0.515,1.1896,0.7722,1.864,0.7722
                                          c0.6743,0,1.3496-0.2572,1.864-0.7722C47.0611,44.4783,47.0611,42.8093,46.0314,41.7797z" />
                                    </g>
                                 </g>
                                 <g>
                                    <g>
                                       <path fill="#E25D5D" d="M45.5076,109.9846c-1.029-1.0296-2.6983-1.0296-3.7282,0l-2.0709,2.0706
                                          c-1.0296,1.0296-1.0296,2.6989,0,3.7285c0.5147,0.515,1.1896,0.7722,1.864,0.7722c0.6743,0,1.3496-0.2572,1.864-0.7722
                                          l2.0709-2.0703C46.5372,112.6834,46.5372,111.0142,45.5076,109.9846z" />
                                    </g>
                                 </g>
                                 <g>
                                    <g>
                                       <g>
                                          <path fill="#E25D5D" d="M108.9037,65.5264H90.4893V47.7202c0-1.4559-1.1799-2.6364-2.6364-2.6364H68.1625
                                             c-1.4559,0-2.6364,1.1805-2.6364,2.6364v17.8062H47.1113c-1.4559,0-2.6364,1.1805-2.6364,2.6364v19.69
                                             c0,1.456,1.1805,2.6364,2.6364,2.6364h18.4148v50.8743c0,1.4559,1.1805,2.6364,2.6364,2.6364h19.6903
                                             c1.4562,0,2.6364-1.1805,2.6364-2.6364v-31.784c0-1.4559-1.1799-2.6364-2.6364-2.6364c-1.4566,0-2.6364,1.1805-2.6364,2.6364
                                             v29.1476H70.7986V87.8528c0-1.4559-1.1805-2.6364-2.6364-2.6364H49.7474V70.7992h18.4148c1.4559,0,2.6364-1.1805,2.6364-2.6364
                                             V50.3566h14.4178v17.8062c0,1.4559,1.1799,2.6364,2.6364,2.6364h18.4145v14.4175H87.8528c-1.4563,0-2.6364,1.1805-2.6364,2.6364
                                             v13.759c0,1.4559,1.1799,2.6364,2.6364,2.6364c1.4566,0,2.6364-1.1805,2.6364-2.6364V90.4896h18.4145
                                             c1.4562,0,2.6364-1.1805,2.6364-2.6364v-19.69C111.5401,66.7069,110.36,65.5264,108.9037,65.5264z" />
                                       </g>
                                    </g>
                                    <g opacity="0.3">
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M78.3782,0c-1.4559,0-2.6364,1.1805-2.6364,2.6364v27.1434c0,1.4559,1.1805,2.6364,2.6364,2.6364
                                                c1.4563,0,2.6364-1.1805,2.6364-2.6364V2.6364C81.0146,1.1802,79.8348,0,78.3782,0z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M153.3795,75.7418h-10.9263c-1.4562,0-2.6364,1.1805-2.6364,2.6364
                                                c0,1.4559,1.1802,2.6364,2.6364,2.6364h10.9263c1.4563,0,2.6364-1.1805,2.6364-2.6364
                                                C156.016,76.9223,154.8355,75.7418,153.3795,75.7418z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M135.1888,75.7418h-8.9526c-1.4562,0-2.6364,1.1805-2.6364,2.6364
                                                c0,1.4559,1.1802,2.6364,2.6364,2.6364h8.9526c1.4562,0,2.6364-1.1805,2.6364-2.6364
                                                C137.8252,76.9223,136.645,75.7418,135.1888,75.7418z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M133.4302,23.1099c-1.03-1.0296-2.6989-1.0296-3.7285,0l-19.1933,19.1933
                                                c-1.0296,1.0296-1.0296,2.6986,0,3.7282c0.5147,0.515,1.1896,0.7722,1.864,0.7722c0.6746,0,1.3496-0.2572,1.8646-0.7722
                                                l19.1933-19.1933C134.4599,25.8085,134.4599,24.1389,133.4302,23.1099z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M37.6864,33.434l-10.848-10.848c-1.0293-1.0296-2.6986-1.0296-3.7282,0
                                                c-1.0296,1.0296-1.0296,2.6986,0,3.7282l10.848,10.848c0.5147,0.515,1.1896,0.7722,1.864,0.7722
                                                c0.6743,0,1.3496-0.2572,1.864-0.7722C38.716,36.1326,38.716,34.4637,37.6864,33.434z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M29.7799,75.0013H2.6364C1.1805,75.0013,0,76.1818,0,77.6378c0,1.4559,1.1805,2.6364,2.6364,2.6364
                                                h27.1434c1.4559,0,2.6364-1.1805,2.6364-2.6364C32.4163,76.1818,31.2358,75.0013,29.7799,75.0013z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M36.8758,118.6163c-1.0293-1.0293-2.6986-1.0296-3.7282,0l-10.5612,10.5612
                                                c-1.0296,1.0296-1.0296,2.6986,0,3.7282c0.5147,0.515,1.1896,0.7722,1.864,0.7722s1.3496-0.2572,1.864-0.7722l10.5612-10.5612
                                                C37.9055,121.3149,37.9055,119.646,36.8758,118.6163z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M132.9067,129.7014l-19.1936-19.1933c-1.03-1.0293-2.6989-1.0296-3.7285,0
                                                c-1.0296,1.0296-1.0296,2.6986,0,3.7282l19.1933,19.1933c0.515,0.515,1.1896,0.7722,1.8643,0.7722s1.3496-0.2572,1.8646-0.7722
                                                C133.9363,132.4,133.9363,130.7307,132.9067,129.7014z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M62.3311,32.3014l-6.4067-15.4602c-0.557-1.345-2.0998-1.9837-3.4448-1.4264
                                                c-1.3453,0.5576-1.9837,2.0995-1.4264,3.4448l6.4067,15.4602c0.4208,1.015,1.4023,1.6278,2.4368,1.6278
                                                c0.3364,0,0.6783-0.0646,1.008-0.2014C62.2498,35.1889,62.8881,33.6467,62.3311,32.3014z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M140.6036,52.4826c-0.5576-1.3453-2.0992-1.9843-3.4448-1.4267l-15.4599,6.4027
                                                c-1.345,0.557-1.9843,2.0992-1.4273,3.4445c0.4208,1.0156,1.4023,1.6281,2.4371,1.6281c0.3361,0,0.678-0.0646,1.0077-0.2014
                                                l15.4599-6.4027C140.5213,55.3698,141.1606,53.8279,140.6036,52.4826z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M104.2208,15.6942c-1.3438-0.5567-2.8869,0.0811-3.4445,1.4264l-6.4067,15.4639
                                                c-0.557,1.3453,0.0817,2.8878,1.4267,3.4448c0.3297,0.1368,0.6713,0.2014,1.0077,0.2014c1.0345,0,2.016-0.6128,2.4368-1.6278
                                                l6.4067-15.4639C106.2051,17.7937,105.5664,16.2519,104.2208,15.6942z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M34.5999,56.774l-15.4602-6.4031c-1.345-0.5567-2.8872,0.0817-3.4445,1.4267
                                                c-0.557,1.3453,0.082,2.8872,1.427,3.4445l15.4602,6.4031c0.3297,0.1368,0.6713,0.2014,1.0077,0.2014
                                                c1.0345,0,2.0163-0.6131,2.4368-1.6281C36.5842,58.8732,35.9455,57.331,34.5999,56.774z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M35.7441,95.114c-0.557-1.3453-2.0998-1.984-3.4448-1.4267L16.843,100.09
                                                c-1.3453,0.557-1.984,2.0995-1.4267,3.4445c0.4208,1.0153,1.4023,1.6281,2.4368,1.6281c0.3364,0,0.678-0.0646,1.008-0.2014
                                                l15.4562-6.4027C35.6624,98.0015,36.3011,96.4593,35.7441,95.114z" />
                                          </g>
                                       </g>
                                       <g>
                                          <g>
                                             <path fill="#E25D5D" d="M138.8941,100.7756l-15.4599-6.4067c-1.3441-0.5567-2.8872,0.0808-3.4448,1.4261
                                                c-0.5576,1.3453,0.0807,2.8872,1.4264,3.4448l15.4599,6.4067c0.3297,0.1368,0.6716,0.2014,1.008,0.2014
                                                c1.0342,0,2.016-0.6128,2.4368-1.6278C140.8775,102.8752,140.2395,101.333,138.8941,100.7756z" />
                                          </g>
                                       </g>
                                    </g>
                                 </g>
                              </g>
                           </svg>
                        </div>
                        <h5 class="mb-0">Mariage civil et religieux </h5>
                        <h6 class="mb-5">10h30-13h30</h6>
                        <p class="text-align-center">
                          

19 Septembre 2025,<br>
Résidence familiale des Kayeye <br>
Commune de Barumbu <br><br>

+243 828 145 627 <br>
                        </p>
                     </div>
                  </div>
                  <div class="col-md-6 col-lg-4 d-flex ">
                     <div class="card card-body justify-content-between bg-primary text-light">
                        <div class="icon-round mb-3 mb-md-5  bg-icon-white">
                           <svg class="icon-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 460" enable-background="new 0 0 512 460;" xml:space="preserve">
                              <g>
                                 <g>
                                    <path fill="#FFFFFF" d="M240.8,110.7c-0.1-4.5-3.1-8.3-7.4-9.4L138,76.1c0,0,0,0,0,0L102,66.7c-4.3-1.1-8.8,0.7-11.1,4.4
                                       c-14.4,23.3-25.7,49.3-32.7,75.1c-11.7,43-9.7,79.9,5.7,106.7c9,15.5,22.1,26.9,38.3,33.4L65.6,421.3l-53-14
                                       c-5.3-1.4-10.8,1.8-12.2,7.1c-1.4,5.3,1.8,10.8,7.1,12.2c0,0,62.5,16.5,62.5,16.5c0,0,62.7,16.5,62.7,16.5
                                       c0.9,0.2,1.7,0.3,2.6,0.3c4.4,0,8.5-3,9.7-7.5c1.4-5.3-1.8-10.8-7.1-12.2l-52.9-13.9l36.8-135c3.6,0.5,7.2,0.8,10.8,0.8
                                       c44.6,0,81.4-37.5,98.5-100.3C238,166.1,241.4,138,240.8,110.7z M211.7,186.6c-14.6,53.6-44.2,85.5-79.2,85.5
                                       c-5.1,0-10.3-0.7-15.4-2c-15.4-4.1-27.8-13.5-35.7-27.2c-9.4-16.3-12.6-37.8-9.5-63.1c13.4,2.2,26,3,37.9,3
                                       c19.7,0,37.6-2.2,54.2-4.3c18.8-2.3,35.5-4.4,50.3-2.5C213.5,179.5,212.6,183.1,211.7,186.6z M218,156.3
                                       c-17.7-2.4-36.6-0.1-56.4,2.4c-25.3,3.1-53.7,6.6-86.2,1.4c0.7-2.9,1.4-5.7,2.2-8.7c5.9-21.6,15.1-43.4,26.6-63.5l15.9,4.2
                                       c-2.9,5.6-5.5,11.3-7.6,15.9c-2.2,5,0,11,5.1,13.2c1.3,0.6,2.7,0.9,4.1,0.9c3.8,0,7.5-2.2,9.1-5.9c3.2-7.3,6.4-13.7,9.2-18.8
                                       l80.9,21.3C220.8,131.2,219.8,143.9,218,156.3z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <path fill="#FFFFFF" d="M256,0c-5.5,0-10,4.5-10,10v26.1c0,5.5,4.5,10,10,10c5.5,0,10-4.5,10-10V10C266,4.5,261.5,0,256,0z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <path fill="#FFFFFF" d="M223.6,49l-13.2-22.6c-2.8-4.8-8.9-6.4-13.7-3.6c-4.8,2.8-6.4,8.9-3.6,13.7l13.2,22.6
                                       c1.9,3.2,5.2,4.9,8.6,4.9c1.7,0,3.5-0.4,5.1-1.4C224.8,59.9,226.4,53.8,223.6,49z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <path fill="#FFFFFF" d="M315.3,22.9c-4.8-2.8-10.9-1.2-13.7,3.6L288.4,49c-2.8,4.8-1.2,10.9,3.6,13.7c1.6,0.9,3.3,1.4,5,1.4
                                       c3.4,0,6.8-1.8,8.6-4.9l13.2-22.6C321.7,31.8,320.1,25.7,315.3,22.9z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <path fill="#FFFFFF" d="M408.6,120.7C408.6,120.7,408.6,120.7,408.6,120.7c-1.7-5.3-7.2-8.2-12.5-6.7c-5.3,1.6-8.3,7.2-6.7,12.5
                                       c0,0.1,0,0.2,0.1,0.2c1.4,4.3,5.3,7,9.5,7c1,0,2-0.1,3-0.5C407.3,131.6,410.2,126,408.6,120.7z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <circle fill="#FFFFFF" cx="110" cy="138" r="10" />
                                 </g>
                              </g>
                              <g>
                                 <g>
                                    <g>
                                       <path fill="#FFFFFF" d="M442,443.1C442,443.1,442,443.1,442,443.1l-62.2,16.4C385.7,458,442,443.1,442,443.1z" />
                                       <path fill="#FFFFFF" d="M379.8,459.5c-0.3,0.1-0.5,0.1-0.5,0.1L379.8,459.5z" />
                                       <path fill="#FFFFFF" d="M511.7,414.4c-1.4-5.3-6.9-8.5-12.2-7.1l-53,14l-36.8-135.1c16.2-6.5,29.4-17.9,38.3-33.4
                                          c15.5-26.8,17.5-63.7,5.8-106.7c-7-25.8-18.3-51.7-32.7-75.1c-2.3-3.7-6.8-5.5-11.1-4.4l-131.3,34.6c-4.3,1.1-7.3,5-7.4,9.4
                                          c-0.5,24.6,2.2,49.7,7.8,73.3c0.1,0.3,0.1,0.7,0.2,1c0.6,2.3,1.1,4.6,1.8,6.8c17.1,62.8,53.9,100.3,98.5,100.3c0,0,0,0,0,0
                                          c3.6,0,7.2-0.3,10.8-0.8l36.8,135l-52.9,13.9c-5.3,1.4-8.5,6.9-7.1,12.2c1.2,4.5,5.2,7.5,9.7,7.5c0.8,0,1.7-0.1,2.6-0.3
                                          l62.7-16.5c0,0,0,0,0,0l62.5-16.5C509.9,425.2,513.1,419.8,511.7,414.4z M291.2,118.7l116.6-30.8c11.6,20,20.8,41.8,26.6,63.5
                                          c1.7,6.3,3.1,12.4,4.2,18.3c-6.1-0.6-12.3-0.7-18.6-0.4c-1.1-7.3-2.7-15-4-20.9c-1.2-5.4-6.6-8.8-12-7.5c-5.4,1.2-8.8,6.6-7.5,12
                                          c1.5,6.7,2.7,12.8,3.5,18.1c-4.5,0.5-9.1,1.1-13.7,1.7c-26,3.3-55.2,7-89.1,1C293.3,155.8,291.3,137.1,291.2,118.7z M379.5,272.1
                                          C379.5,272.1,379.5,272.1,379.5,272.1c-33.2,0-61.6-28.8-76.8-77.4c11.1,1.5,21.8,2.2,31.9,2.2c19.7,0,37.6-2.3,54.2-4.4
                                          c19.6-2.5,36.9-4.7,52.2-2.3c1.2,20.8-2.3,38.7-10.4,52.7c-7.9,13.7-20.3,23.1-35.7,27.2C389.8,271.4,384.6,272.1,379.5,272.1z" />
                                    </g>
                                 </g>
                              </g>
                              <path opacity="0.3" fill="#FFFFFF" d="M211.7,186.6c-14.6,53.6-44.2,85.5-79.2,85.5c-5.1,0-10.3-0.7-15.4-2c-15.4-4.1-27.8-13.5-35.7-27.2
                                 c-9.4-16.3-12.6-37.8-9.5-63.1c13.4,2.2,26,3,37.9,3c19.7,0,37.6-2.2,54.2-4.3c18.8-2.3,35.5-4.4,50.3-2.5
                                 C213.5,179.5,212.6,183.1,211.7,186.6z" />
                              <path opacity="0.3" fill="#FFFFFF" d="M379.5,272.1C379.5,272.1,379.5,272.1,379.5,272.1c-33.2,0-61.6-28.8-76.8-77.4c11.1,1.5,21.8,2.2,31.9,2.2
                                 c19.7,0,37.6-2.3,54.2-4.4c19.6-2.5,36.9-4.7,52.2-2.3c1.2,20.8-2.3,38.7-10.4,52.7c-7.9,13.7-20.3,23.1-35.7,27.2
                                 C389.8,271.4,384.6,272.1,379.5,272.1" />
                           </svg>
                        </div>
                        <h5 class="mb-0 text-white">Soirée dansante</h5>
                        <h6 class="mb-5 text-white">18h30-00h00</h6>
                        <p class="text-align-center">
20 Septembre 2025,<br>
                           Salon-Congo<br>
Pullman Hotel <br>
Kinshasa <br>
                        </p>
                     </div>
                  </div>
               </div>
               <!--End row-->
               <!--Row-->
               <div class="row">
                  <div class="col">
                     <div class=" text-center mt-5 pt-5">
                        <a href="#" class="btn btn-primary btn-wide-sm btn-sm  scroll">RSVP</a>
                     </div>
                  </div>
                  <!--End row-->
               </div>
               <!--End container-->
            </div>
            <div class="curved-decoration">
               <svg width="100%" height="100%" class="bg-secondary-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M0,0c0,0,219.6543,165.951,730.788,124.0771c383.3156-31.4028,827.2139-96.9514,1244.7139-96.9514
      c212.5106,0,438.9999,3.5,584.4982,1.5844v139.9126H0V0z"></path>
</g>
</svg>
            </div>
         </section>
























          <section id="gallery" class="bg-secondary ">
            <div class="container spacer-double-lg">
               <div class="col-lg-11 mx-lg-auto">
                  <div class="row justify-content-center card-gutters">
                     <div class="col-7 mb-3">
                        <a href="../couple/images/15.png" class="popup-image hover-effect d-block">
                           <img class="img-fluid" src="../couple/images/15.png" alt="">
                           <span class="hover-effect-container">
                              <span class="hover-effect-icon has-svg">
                                 <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                    viewBox="0 0 73.6404 64.5606" enable-background="new 0 0 73.6404 64.5606" xml:space="preserve">
                                    <g>
                                       <path fill="#E25D5D" d="M36.904,6.66L36.904,6.66C28.5329-1.7395,14.9401-1.8373,6.4502,6.4736
                                          c-8.5632,8.3827-8.5871,22.1694-0.2037,30.724l24.4097,24.9081c3.1797,3.2446,8.3876,3.2973,11.6323,0.1176l24.7745-24.2787
                                          c8.5602-8.3889,8.832-22.3842,0.4549-30.956C59.1567-1.5672,45.4453-1.7103,36.904,6.66z"/>
                                       <path fill="#FFFFFF" d="M44.695,28.8005c0-0.4883-0.3996-0.8879-0.8879-0.8879h-4.4394v-4.4394
                                          c0-0.4883-0.3996-0.8879-0.8879-0.8879h-2.9596c-0.4883,0-0.8879,0.3996-0.8879,0.8879v4.4394h-4.4394
                                          c-0.4883,0-0.8879,0.3995-0.8879,0.8879v2.9596c0,0.4883,0.3996,0.8879,0.8879,0.8879h4.4394v4.4394
                                          c0,0.4883,0.3996,0.8879,0.8879,0.8879h2.9596c0.4883,0,0.8879-0.3996,0.8879-0.8879V32.648h4.4394
                                          c0.4883,0,0.8879-0.3995,0.8879-0.8879V28.8005z"/>
                                    </g>
                                 </svg>
                              </span>
                           </span>
                        </a>
                     </div>
                     <div class="col-5 align-self-end mb-3">
                        <a href="../couple/images/12G.png" class="popup-image hover-effect d-block">
                           <img class="img-fluid" src="../couple/images/12G.png" alt="">
                           <span class="hover-effect-container">
                              <span class="hover-effect-icon has-svg">
                                 <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                    viewBox="0 0 73.6404 64.5606" enable-background="new 0 0 73.6404 64.5606" xml:space="preserve">
                                    <g>
                                       <path fill="#E25D5D" d="M36.904,6.66L36.904,6.66C28.5329-1.7395,14.9401-1.8373,6.4502,6.4736
                                          c-8.5632,8.3827-8.5871,22.1694-0.2037,30.724l24.4097,24.9081c3.1797,3.2446,8.3876,3.2973,11.6323,0.1176l24.7745-24.2787
                                          c8.5602-8.3889,8.832-22.3842,0.4549-30.956C59.1567-1.5672,45.4453-1.7103,36.904,6.66z"/>
                                       <path fill="#FFFFFF" d="M44.695,28.8005c0-0.4883-0.3996-0.8879-0.8879-0.8879h-4.4394v-4.4394
                                          c0-0.4883-0.3996-0.8879-0.8879-0.8879h-2.9596c-0.4883,0-0.8879,0.3996-0.8879,0.8879v4.4394h-4.4394
                                          c-0.4883,0-0.8879,0.3995-0.8879,0.8879v2.9596c0,0.4883,0.3996,0.8879,0.8879,0.8879h4.4394v4.4394
                                          c0,0.4883,0.3996,0.8879,0.8879,0.8879h2.9596c0.4883,0,0.8879-0.3996,0.8879-0.8879V32.648h4.4394
                                          c0.4883,0,0.8879-0.3995,0.8879-0.8879V28.8005z"/>
                                    </g>
                                 </svg>
                              </span>
                           </span>
                        </a>
                     </div>
                     <div class="col-5">
                        <a href="../couple/images/23D.png" class="popup-image hover-effect d-block">
                           <img class="img-fluid" src="../couple/images/23D.png" alt="">
                           <span class="hover-effect-container">
                              <span class="hover-effect-icon has-svg">
                                 <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                    viewBox="0 0 73.6404 64.5606" enable-background="new 0 0 73.6404 64.5606" xml:space="preserve">
                                    <g>
                                       <path fill="#E25D5D" d="M36.904,6.66L36.904,6.66C28.5329-1.7395,14.9401-1.8373,6.4502,6.4736
                                          c-8.5632,8.3827-8.5871,22.1694-0.2037,30.724l24.4097,24.9081c3.1797,3.2446,8.3876,3.2973,11.6323,0.1176l24.7745-24.2787
                                          c8.5602-8.3889,8.832-22.3842,0.4549-30.956C59.1567-1.5672,45.4453-1.7103,36.904,6.66z"/>
                                       <path fill="#FFFFFF" d="M44.695,28.8005c0-0.4883-0.3996-0.8879-0.8879-0.8879h-4.4394v-4.4394
                                          c0-0.4883-0.3996-0.8879-0.8879-0.8879h-2.9596c-0.4883,0-0.8879,0.3996-0.8879,0.8879v4.4394h-4.4394
                                          c-0.4883,0-0.8879,0.3995-0.8879,0.8879v2.9596c0,0.4883,0.3996,0.8879,0.8879,0.8879h4.4394v4.4394
                                          c0,0.4883,0.3996,0.8879,0.8879,0.8879h2.9596c0.4883,0,0.8879-0.3996,0.8879-0.8879V32.648h4.4394
                                          c0.4883,0,0.8879-0.3995,0.8879-0.8879V28.8005z"/>
                                    </g>
                                 </svg>
                              </span>
                           </span>
                        </a>
                     </div>
                     <div class="col-3">
                        <a href="../couple/images/12JH.png " class="popup-image hover-effect d-block">
                           <img class="img-fluid" src="../couple/images/12JH.png" alt="">
                           <span class="hover-effect-container">
                              <span class="hover-effect-icon has-svg">
                                 <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                    viewBox="0 0 73.6404 64.5606" enable-background="new 0 0 73.6404 64.5606" xml:space="preserve">
                                    <g>
                                       <path fill="#E25D5D" d="M36.904,6.66L36.904,6.66C28.5329-1.7395,14.9401-1.8373,6.4502,6.4736
                                          c-8.5632,8.3827-8.5871,22.1694-0.2037,30.724l24.4097,24.9081c3.1797,3.2446,8.3876,3.2973,11.6323,0.1176l24.7745-24.2787
                                          c8.5602-8.3889,8.832-22.3842,0.4549-30.956C59.1567-1.5672,45.4453-1.7103,36.904,6.66z"/>
                                       <path fill="#FFFFFF" d="M44.695,28.8005c0-0.4883-0.3996-0.8879-0.8879-0.8879h-4.4394v-4.4394
                                          c0-0.4883-0.3996-0.8879-0.8879-0.8879h-2.9596c-0.4883,0-0.8879,0.3996-0.8879,0.8879v4.4394h-4.4394
                                          c-0.4883,0-0.8879,0.3995-0.8879,0.8879v2.9596c0,0.4883,0.3996,0.8879,0.8879,0.8879h4.4394v4.4394
                                          c0,0.4883,0.3996,0.8879,0.8879,0.8879h2.9596c0.4883,0,0.8879-0.3996,0.8879-0.8879V32.648h4.4394
                                          c0.4883,0,0.8879-0.3995,0.8879-0.8879V28.8005z"/>
                                    </g>
                                 </svg>
                              </span>
                           </span>
                        </a>
                     </div>
                     <div class="col-3">
                        <a href="../couple/images/13.png" class="popup-image hover-effect d-block">
                           <img class="img-fluid" src="../couple/images/13.png" alt="">
                           <span class="hover-effect-container">
                              <span class="hover-effect-icon has-svg">
                                 <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                    viewBox="0 0 73.6404 64.5606" enable-background="new 0 0 73.6404 64.5606" xml:space="preserve">
                                    <g>
                                       <path fill="#E25D5D" d="M36.904,6.66L36.904,6.66C28.5329-1.7395,14.9401-1.8373,6.4502,6.4736
                                          c-8.5632,8.3827-8.5871,22.1694-0.2037,30.724l24.4097,24.9081c3.1797,3.2446,8.3876,3.2973,11.6323,0.1176l24.7745-24.2787
                                          c8.5602-8.3889,8.832-22.3842,0.4549-30.956C59.1567-1.5672,45.4453-1.7103,36.904,6.66z"/>
                                       <path fill="#FFFFFF" d="M44.695,28.8005c0-0.4883-0.3996-0.8879-0.8879-0.8879h-4.4394v-4.4394
                                          c0-0.4883-0.3996-0.8879-0.8879-0.8879h-2.9596c-0.4883,0-0.8879,0.3996-0.8879,0.8879v4.4394h-4.4394
                                          c-0.4883,0-0.8879,0.3995-0.8879,0.8879v2.9596c0,0.4883,0.3996,0.8879,0.8879,0.8879h4.4394v4.4394
                                          c0,0.4883,0.3996,0.8879,0.8879,0.8879h2.9596c0.4883,0,0.8879-0.3996,0.8879-0.8879V32.648h4.4394
                                          c0.4883,0,0.8879-0.3995,0.8879-0.8879V28.8005z"/>
                                    </g>
                                 </svg>
                              </span>
                           </span>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
            <div class="curved-decoration ">
               <svg width="100%" height="100%" class="bg-primary-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M2560,0c0,0-219.6543,165.951-730.788,124.0771c-383.3156-31.4028-827.2138-96.9514-1244.7139-96.9514
      c-212.5106,0-439,3.5-584.4982,1.5844l0,139.9126h2560V0z"></path>
</g>
</svg>
            </div>
         </section>






















<!-- 

           <section id="friends" class="text-center">
           
            <div class="container spacer-double-lg">
                
               <div class="row justify-content-center">
                  <div class="col">
                     <div class=" mb-5 pb-5">
                        <h1 class="display-4 mb-0">Our Best Friends</h1>
                        <p class="w-md-40 mb-0 mx-auto text-dark-gray opacity-8 ">We are lucky to have them in our side</p>
                     </div>
                  </div>
               </div>
              
               <div class="row justify-content-center">
                  <div class="col-md-4 d-flex flex-column align-items-center">
                     <div class="px-5 mb-4 pb-4 ">
                        <div class="svg-mask-container px-3">
                        <svg  width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 607 532" enable-background="new 0 0 607 532" xml:space="preserve">
                           <defs>
                              <mask id="mask-middle-1">
                                 <path fill="#FFFFFF" d="M303.2954,55.7655L303.2954,55.7655c-68.9887-68.3032-180.269-68.3601-249.3155,0.1402
                                    c-69.6423,69.0921-69.0838,181.9562,0.0139,251.5284l201.1888,202.5704c26.2076,26.3876,68.8445,26.5335,95.2321,0.3258
                                    l201.4838-200.1096c69.6179-69.1431,71.0768-183.7278,2.0303-253.4415C485.013-12.8027,372.7591-13.2244,303.2954,55.7655z"></path>
                              </mask>
                           </defs>
                           <image mask="url(#mask-middle-1)" width="607" height="532" xlink:href="../couple/images/<?php echo $photocoeur;?>"></image>
                        </svg>
                     </div>
                        <div class=" mt-4">
                           <h5 class="mb-0"><?php echo $dataevent['prenom_epouse']?></h5>
                           <span class="small">Bridesmaid </span>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-4 d-flex flex-column align-items-center">
                     <div class=" px-5 mb-4 pb-4 ">
                        <div class="svg-mask-container px-3">
                        <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 607 532" enable-background="new 0 0 607 532" xml:space="preserve">
                           <defs>
                              <mask id="mask-middle-2">
                                 <path fill="#FFFFFF" d="M303.2954,55.7655L303.2954,55.7655c-68.9887-68.3032-180.269-68.3601-249.3155,0.1402
                                    c-69.6423,69.0921-69.0838,181.9562,0.0139,251.5284l201.1888,202.5704c26.2076,26.3876,68.8445,26.5335,95.2321,0.3258
                                    l201.4838-200.1096c69.6179-69.1431,71.0768-183.7278,2.0303-253.4415C485.013-12.8027,372.7591-13.2244,303.2954,55.7655z"></path>
                              </mask>
                           </defs>
                           <image mask="url(#mask-middle-2)" width="607" height="532" xlink:href="../couple/images/<?php echo $photocoeur;?>"></image>
                        </svg>
                     </div>
                        <div class=" mt-4">
                           <h5 class="mb-0"><?php echo $dataevent['prenom_epoux']?></h5>
                           <span class="small">Groomsman </span>
                        </div>
                     </div>
                  </div>
               </div>
             
               <div class="row justify-content-center">
                  <div class="col-md-6">
                     <div class="text-center">
                        <div class="mx-auto icon-round mb-4 mb-md-4  bg-icon-primary">
                           <svg class="icon-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 70.8 102.2" enable-background="new 0 0 70.8 102.2;" xml:space="preserve">
                              <path class="st0" opacity="0.3" d="M35.4,70.8c5.4,0,10-1.9,13.9-5.8c3.8-3.8,5.8-8.5,5.8-13.9V19.7c0-5.4-1.9-10-5.8-13.9C45.4,1.9,40.8,0,35.4,0
                                 c-5.4,0-10,1.9-13.9,5.8c-3.8,3.8-5.8,8.5-5.8,13.9v31.5c0,5.4,1.9,10,5.8,13.9C25.4,68.8,30,70.8,35.4,70.8z" fill="#E25D5D" />
                              <path class="st1" d="M69.6,40.5c-0.8-0.8-1.7-1.2-2.8-1.2c-1.1,0-2,0.4-2.8,1.2c-0.8,0.8-1.2,1.7-1.2,2.8v7.9
                                 c0,7.6-2.7,14.1-8.1,19.4c-5.4,5.4-11.9,8.1-19.4,8.1c-7.6,0-14.1-2.7-19.4-8.1c-5.4-5.4-8.1-11.9-8.1-19.4v-7.9
                                 c0-1.1-0.4-2-1.2-2.8c-0.8-0.8-1.7-1.2-2.8-1.2s-2,0.4-2.8,1.2C0.4,41.3,0,42.2,0,43.2v7.9C0,60.2,3,68,9.1,74.7
                                 c6,6.7,13.5,10.5,22.4,11.5v8.1H15.7c-1.1,0-2,0.4-2.8,1.2c-0.8,0.8-1.2,1.7-1.2,2.8c0,1.1,0.4,2,1.2,2.8c0.8,0.8,1.7,1.2,2.8,1.2
                                 H55c1.1,0,2-0.4,2.8-1.2c0.8-0.8,1.2-1.7,1.2-2.8c0-1.1-0.4-2-1.2-2.8c-0.8-0.8-1.7-1.2-2.8-1.2H39.3v-8.1c8.9-1,16.3-4.8,22.4-11.5
                                 c6-6.7,9.1-14.6,9.1-23.6v-7.9C70.8,42.2,70.4,41.3,69.6,40.5z" fill="#E25D5D" />
                           </svg>
                        </div>
                        <blockquote>Ces trois dernières années, notre histoire s’est tissée d’amour, d’amitié, de complicité, de pardon et de confiance.

Aujourd’hui, cette belle aventure s’écrit avec vous*, à travers ce livre.  
Alors, partagez avec nous vos plus beaux souhaits pour ce nouveau chapitre.</blockquote>
                        <small class="text-uppercase font-weight-600 text-dark upper-letter-space">Nous vous aimons <?php echo $fetard; ?></small>
                     </div>
                  </div>
               </div>
            </div>
            
            <div class="curved-decoration ">
               <svg width="100%" height="100%" class="bg-secondary-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M2560,0c0,0-219.6543,165.951-730.788,124.0771c-383.3156-31.4028-827.2138-96.9514-1244.7139-96.9514
      c-212.5106,0-439,3.5-584.4982,1.5844l0,139.9126h2560V0z"></path>
</g>
</svg>
            </div>
         </section> -->


 <?php 	 }else{ } ?>



























         <!--End hero section-->
        
         <section class="footer-copyright spacer-double-sm bg-white text-center">

            <p class="text-uppercase small text-muted d-block mb-0">&copy; <?php echo date('Y')?> Hubert Solutions All right reserved</p>
            <p class="text-muted small d-block mb-0">Plateforme, branche de </span> <a  href="https://www.invitationspeciale.com">Invitation Spéciale</a><br> 
			      Sous : <a href="https://hubertlutunga.com">Hubert Lutunga</a> <br>
               <a href="https://wa.me/243810678785" target="_blinck">Nous contacter</a>
            </p>
               

         </section>
         <!--To the top-->
         <a class="scroll-to-top scroll" href="#wrapper">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 273.1091 238.2098" enable-background="new 0 0 273.1091 238.2098" xml:space="preserve">
               <path fill="#E25D5D" d="M136.7428,23.457L136.7428,23.457C105.6324-7.6197,55.2286-7.8699,23.8161,23.0177
                  c-31.6835,31.1545-31.6582,82.277-0.501,113.9287l90.7191,92.1591c11.8174,12.005,31.1293,12.1571,43.1343,0.3396l91.6648-90.2325
                  c31.6726-31.1777,32.5645-83.0754,1.4308-114.7912C219.1893-7.2342,168.3452-7.6516,136.7428,23.457z" />
               <path fill="#FFFFFF" d="M110.7149,123.5662l-2.9542-2.9542c-1.2509-1.2509-1.2509-3.2736,0-4.5112l25.8562-25.8695
                  c1.2509-1.2509,3.2736-1.2509,4.5112,0l25.8562,25.8562c1.2509,1.2509,1.2509,3.2736,0,4.5112l-2.9542,2.9542
                  c-1.2642,1.2642-3.3268,1.2376-4.5644-0.0532l-15.2635-16.0221v38.2454c0,1.7699-1.4239,3.1938-3.1938,3.1938H133.75
                  c-1.7699,0-3.1938-1.4239-3.1938-3.1938v-38.2454l-15.2769,16.0354C114.0418,124.8171,111.9791,124.8437,110.7149,123.5662z" />
            </svg>
         </a>
         <!-- End to the top -->
      </div>
      <!-- End wrapper-->
      <!--Javascript-->
      <script src="js/jquery-1.12.4.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/smooth-scroll.js"></script>
      <script src="js/jquery.magnific-popup.min.js"></script>
      <script src="js/jquery.countdown.min.js"></script>
      <script src="js/placeholders.min.js"></script>
      <script src="js/instafeed.min.js"></script>
      <script src="js/script.js"></script>
      <!-- Google analytics -->
      <!-- End google analytics -->





<?php if (isset($_GET['idinv'])): ?>
  <div id="feneteconf-root" class="feneteconf-root">
    <?php include('confscript_wedding.php'); ?>
    <?php include('modalreponse.php'); ?>
  </div>
<?php endif; ?>



<?php if (isset($_GET['ok'])): ?>
  <script src="sweet/sweetalert2.all.min.js"></script>
  <script>
    Swal.fire({title:"RSVP", text:"Votre confirmation est enregistrée.", icon:"success"});
  </script>
<?php endif; ?>