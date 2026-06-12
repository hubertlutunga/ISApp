<div class="body-inner">
      <!-- Header start -->
      <?php include('header.php');?>
      <!--/ Header end -->

      <!-- banner start-->
      <?php include('banner.php');?>
      <!-- banner end-->
       
      <!-- banner start-->
      <?php include('visuel.php');?>
      <!-- banner end-->

      


<?php


//------------iframe-------

$stmtframe = $pdo->prepare("SELECT * FROM websiteconference WHERE cod_event = ?");
$stmtframe->execute([$codevent]); // Correction ici pour utiliser $codevent
$data_frame = $stmtframe->fetch();

if (isset($data_frame)) { 


   if (isset($data_frame['iframe'])) {
      $dataframe = $data_frame['iframe'];
   } else {  
         $dataframe = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7957.042069035776!2d15.267748210248808!3d-4.31271399564315!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1a6a33cd8a49cd59%3A0x44c649ce70ff5c4a!2sFleuve%20Congo%20Hotel%20by%20Blazon%20Hotels!5e0!3m2!1sfr!2scd!4v1749658893544!5m2!1sfr!2scd" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'; 

   }


   if (isset($data_frame['agency'])) {
         $dataagency = '<p><strong>Agence : </strong>'.$data_frame['agency'].'</p>';
   } else {  
         $dataagency = ''; 

   }



   if (isset($data_frame['phone'])) {
         $dataphone = '<p><strong>Téléphone : </strong>'.$data_frame['phone'].'</p>';
   } else {  
         $dataphone = ''; 

   }


   if (isset($data_frame['email'])) {
         $dataemail = '<p><strong>Email : </strong>'.$data_frame['email'].'</p>';
   } else {  
         $dataemail = ''; 

   }

} else {  
		$dataframe = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7957.042069035776!2d15.267748210248808!3d-4.31271399564315!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1a6a33cd8a49cd59%3A0x44c649ce70ff5c4a!2sFleuve%20Congo%20Hotel%20by%20Blazon%20Hotels!5e0!3m2!1sfr!2scd!4v1749658893544!5m2!1sfr!2scd" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'; 

}


?>













  
 
      <!-- ts map direction start-->
      <section class="ts-map-direction wow fadeInUp" data-wow-duration="1.5s" data-wow-delay="400ms">
         <div class="container">
            <div class="row">
               <div class="col-lg-5">
                  <h2 class="column-title">
                     <span><?php echo htmlspecialchars((string) ($publicEventLabels['join_kicker'] ?? 'REJOIGNEZ-NOUS'), ENT_QUOTES, 'UTF-8'); ?></span>
                     <?php echo htmlspecialchars((string) ($publicEventLabels['join_title'] ?? "Obtenir la direction vers le lieu de l'événement"), ENT_QUOTES, 'UTF-8'); ?>
                  </h2>

                  <div class="ts-map-tabs">
                     <ul class="nav" role="tablist">
                        <li class="nav-item">
                           <a class="nav-link active" href="#profile" role="tab" data-toggle="tab">Lieu</a>
                        </li>
                        <li class="nav-item">
                           <a class="nav-link" href="#buzz" role="tab" data-toggle="tab"><?php echo date('H:i', strtotime($date_event))?></a>
                        </li> 
                     </ul>

                     <!-- Tab panes -->
                     <div class="tab-content direction-tabs">
                        <div role="tabpanel" class="tab-pane active" id="profile">
                           <div class="direction-tabs-content">
                              <h3><?php echo $lieu; ?></h3>
                              <p class="derecttion-vanue">
                              <?php echo $dataevent['adresse']; ?><br/>
                                 
                                       </p>
                                 <div class="row">
                                    <div class="col-md-12">
                                       <div class="contact-info-box">
                                          <h3>Infos</h3> 
                                          <?php echo $dataagency?> 
                                          <?php echo $dataphone?> 
                                          <?php echo $dataemail?> 
                                          <?php foreach ($publicDetailItems as $detailItem) { ?>
                                          <p><strong><?php echo htmlspecialchars((string) $detailItem['label'], ENT_QUOTES, 'UTF-8'); ?> :</strong> <?php echo htmlspecialchars((string) $detailItem['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                                          <?php } ?>
                                       </div>

                                    </div> 
                                 </div><!-- row end-->
                           </div><!-- direction tabs end-->
                        </div><!-- tab pane end-->
                        <div role="tabpanel" class="tab-pane fade" id="buzz">
                           <div class="direction-tabs-content">
                              <h3><?php echo date('H:i', strtotime($date_event))?></h3> 
                                 <div class="row">
                                    <div class="col-md-12">
                                       <div class="contact-info-box">
                                          <h3>Infos</h3>
                                          <p><strong>Département:</strong> CelCom</p> 
                                          <p><strong>Email: </strong> contact@regideso.cd</p>
                                       </div>

                                    </div> 
                                 </div><!-- row end-->
                           </div><!-- direction tabs end-->
                        </div> 
                     </div>

                  </div><!-- map tabs end-->

               </div><!-- col end-->
               <div class="col-lg-6 offset-lg-1">
                  <div class="ts-map">
                     <div class="mapouter">

                        <div class="gmap_canvas"> 
                             <?php echo $dataframe; ?>      
                        </div>

                     </div>
                  </div>
               </div>
            </div><!-- col end-->
         </div><!-- container end--> 
      </section>
      <!-- ts map direction end-->













      <section class="ts-contact-form" style="border-top:1px solid #ddd;" id="participer">
         <div class="container">
            <div class="row">
               <div class="col-lg-8 mx-auto">
                  <h2 class="section-title text-center">
                     <span><?php echo htmlspecialchars((string) ($publicEventLabels['form_kicker'] ?? 'Comment participer ?'), ENT_QUOTES, 'UTF-8'); ?></span>
                     <?php echo htmlspecialchars((string) ($publicEventLabels['form_title'] ?? "S'enregistrer"), ENT_QUOTES, 'UTF-8'); ?>
                  </h2>






<?php


if(isset($_POST['submit'])){
   

   $prenom = trim((string) (@$_POST['prenom'] ?? ''));
   $nom = trim((string) (@$_POST['nom'] ?? ''));

   $nomComplet = trim($prenom . ' ' . $nom);

   $phone = trim((string) (@$_POST['phone'] ?? ''));  
   $normalizedPhone = preg_replace('/[^0-9+]/', '', $phone);
    
    
    $emailinvite = trim((string) (@$_POST['email'] ?? ''));
    $normalizedEmail = strtolower($emailinvite);
   $presence = "oui";

 
 
   if (!$nomComplet) {
     $error = 'Remplissez le nom';
     echo '<span style="color:red;">'.$error.'</span>';
    }elseif (!$phone) {
       $error = 'Remplissez le numéro de téléphone';
       echo '<span style="color:red;">'.$error.'</span>';
    }elseif (!$emailinvite) {
       $error = "Remplissez l'adresse email";
       echo '<span style="color:red;">'.$error.'</span>';
    }elseif (!$presence) {
       $error = 'Confirmer votre presence ou absence';
       echo '<span style="color:red;">'.$error.'</span>';
    }else{

   $duplicateStmt = $pdo->prepare(
      "SELECT cod_conf
       FROM confirmation
       WHERE cod_mar = :cod_mar
         AND (
           LOWER(TRIM(email)) = :email
           OR REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(phone), ' ', ''), '-', ''), '.', ''), '(', ''), ')', '') = :phone
         )
       LIMIT 1"
   );
   $duplicateStmt->bindValue(':cod_mar', $codevent);
   $duplicateStmt->bindValue(':email', $normalizedEmail);
   $duplicateStmt->bindValue(':phone', $normalizedPhone);
   $duplicateStmt->execute();
   $alreadyRegistered = $duplicateStmt->fetch(PDO::FETCH_ASSOC);
   $duplicateStmt->closeCursor();

   if ($alreadyRegistered) {
      echo '<script>
         Swal.fire({
            title: "Déjà enregistré",
            text: '.json_encode((string) ($publicEventLabels['duplicate_message'] ?? "Cette adresse email ou ce numéro de téléphone a déjà été enregistré pour cet événement."), JSON_UNESCAPED_UNICODE).',
            icon: "info",
            confirmButtonText: "OK"
         });
      </script>';
   } else {
 
       
   $sql = 'INSERT INTO confirmation (
     cod_mar,
     noms, 
     email, 
     phone, 
     presence,   
     date_enreg)
   values  (
     :cod_mar,
     :noms, 
     :email, 
     :phone, 
     :presence,   
     NOW())';
   
   $q = $pdo->prepare($sql);
   $q->bindValue(':cod_mar', $codevent);
   $q->bindValue(':noms', $nomComplet);
   $q->bindValue(':email', $normalizedEmail);
   $q->bindValue(':phone', $normalizedPhone); 
   $q->bindValue(':presence', $presence);   
   $q->execute();
   $q->closeCursor(); 

   

    









        // L'inscription en base fait foi, l'email reste un accusé de réception facultatif.
        $speudo = $nom;
        $email = $emailinvite;
        $subject = strtoupper($fetard.' '.($publicEventLabels['subject_suffix'] ?? 'RSVP'));
        $message = "Bonjour $speudo,\n\n".($publicEventLabels['email_message'] ?? "Votre inscription nous est parvenue avec succès.")."\n\nMerci!";

        $headers = "From: contact@invitationspeciale.com\r\n";
        $headers .= "Reply-To: contact@invitationspeciale.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";

        @mail($email, $subject, $message, $headers);

        echo '<script>
           Swal.fire({
              title: "RSVP!",
              text: '.json_encode((string) ($publicEventLabels['success_message'] ?? 'Votre inscription a été confirmée avec succès'), JSON_UNESCAPED_UNICODE).',
              icon: "success",
              confirmButtonText: "OK"
           });
        </script>';
     
      

 }

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
                        <button type="submit" name="submit" class="btn" type="submit"><?php echo htmlspecialchars((string) ($publicEventLabels['form_submit'] ?? 'Envoyer'), ENT_QUOTES, 'UTF-8'); ?></button>
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











      <!-- Javascript COMPTE A REBOUR -->


      <script>
/*!
 * jCounter (mini) — autonome pour Jours/Heures/Minutes/Secondes
 * API: $(selector).jCounter({ date: 'YYYY/MM/DD HH:MM:SS', fallback: fn })
 * Option alternative: mettre data-date="YYYY/MM/DD HH:MM:SS" sur l'élément.
 */

(function ($) {
  $.fn.jCounter = function (opts) {
    const defaults = {
      date: null,            // 'YYYY/MM/DD HH:MM:SS'
      fallback: function(){},// appelé quand le compte atteint 0
      autostart: true
    };
    const settings = $.extend({}, defaults, opts);

    function pad(n){ return (n < 10 ? '0' : '') + n; }

    return this.each(function () {
      const $root = $(this);
      const targetStr = settings.date || $root.attr('data-date');

      if (!targetStr) {
        console.warn('[jCounter] Aucune date fournie. Utilise option "date" ou attribut data-date.');
        return;
      }

      // Normalise 'YYYY/MM/DD HH:MM:SS' pour compatibilité Safari/iOS (éviter les "-")
      const target = new Date(String(targetStr).replace(/-/g, '/'));
      if (isNaN(target.getTime())) {
        console.warn('[jCounter] Date invalide :', targetStr);
        return;
      }

      let timer = null;

      function render(msLeft) {
        if (msLeft < 0) msLeft = 0;
        const totalSec = Math.floor(msLeft / 1000);

        const days    = Math.floor(totalSec / 86400);
        const hours   = Math.floor((totalSec % 86400) / 3600);
        const minutes = Math.floor((totalSec % 3600) / 60);
        const seconds = totalSec % 60;

        $root.find('.days').text(pad(days));
        $root.find('.hours').text(pad(hours));
        $root.find('.minutes').text(pad(minutes));
        $root.find('.seconds').text(pad(seconds));
      }

      function tick() {
        const now = new Date();
        const diff = target - now;

        if (diff <= 0) {
          render(0);
          clearInterval(timer);
          timer = null;
          // Déclenche un event + callback
          $root.trigger('jcounter:finish');
          try { settings.fallback.call($root[0]); } catch (e) {}
          return;
        }
        render(diff);
      }

      // Premier rendu + intervalle
      tick();
      timer = setInterval(tick, 1000);

      // Stocke l'interval ID si tu veux l'arrêter plus tard
      $root.data('jcounter:timer', timer);
    });
  };
})(jQuery);

// ====== Initialisation ======
$(function () {
  // 1) Si TU veux imposer une même date à tous les compteurs:
  // $('.countdown').jCounter({
  //   date: '<?php echo $eventDate; ?>',
  //   fallback: function () {
  //     if (window.Swal) { Swal.fire("Événement terminé !"); }
  //     else { console.log("count finished!"); }
  //   }
  // });

  // 2) Ou laisser chaque .countdown lire sa propre date via data-date (recommandé ici)
  $('.countdown').jCounter({
    fallback: function () {
      if (window.Swal) { Swal.fire("Événement terminé !"); }
      else { console.log("count finished!"); }
    }
  });
});
</script>




   </div>
   <!-- Body inner end -->