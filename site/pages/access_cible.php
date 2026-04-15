 

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
       

 

                 <div style="display: flex; justify-content: center;">
                     <a href="index.php?page=access&cod=<?php echo $codevent?>"><img src="../images/Logo_invitationSpeciale_1.png" width="300px;"></a>



                 </div>


                    <?php include('scanqr.php');?>


<!-- 

<div style='padding-left:20px;padding-right:20px;'>

                <form action="" method="POST" class="forms-sample"> 
                      
                    <input type="text" name="searchs" id="searchs" placeholder='Rechercher' style="width: 100%;height: 45px;text-align:center;">
                      
                </form>
                   
                    
                    
                         
      <div id="resultat" style="margin-left:-45px;">
          
                            <ul style="width:100%;"> </ul>    
                    
      </div>

                    <script src="jquery.min.js"></script> 
                    <script src="func.js"></script>
                    
</div>
 
    -->

    
         <section id="rsvp" class="bg-secondary spacer-one-top-lg o-hidden" >
            
            <div class="container spacer-one-bottom-lg">










 
                  





 



 
                <div class="col-md-12" style="display: flex; justify-content: center;margin-top:-50px;">
                    <div class="animate-box">

                        <?php 
                            $get = intval($_GET['codinv']); // Assurez-vous que c'est un entier
                            $reqinv = "SELECT * FROM invite WHERE id_inv = :id_inv AND cod_mar = :cod_mar";
                            $stmt = $pdo->prepare($reqinv);
                            $stmt->execute([':id_inv' => $get, ':cod_mar' => $codevent]);
                            $row_inv = $stmt->fetch(PDO::FETCH_ASSOC);


                            $nom_invite = $row_inv ? $row_inv['nom'] : '';

                            $reqtab = "SELECT * FROM tableevent WHERE cod_tab = :cod_tab AND cod_event = :cod_event";
                            $reqtab = $pdo->prepare($reqtab);
                            $reqtab->execute([':cod_tab' => $row_inv['siege'], ':cod_event' => $codevent]);
                            $row_tab = $reqtab->fetch(PDO::FETCH_ASSOC);

  
                            $nom_table= $row_tab ? $row_tab['nom_tab'] : '';




 



                              $couple = "C";
                              $reqci="SELECT count(*) as total_ci FROM invite where sing = '$couple' AND cod_mar = '$codevent' AND siege = '{$row_inv['siege']}'";
                              $ci=$pdo->query($reqci);
                              $row_ci=$ci->fetch();
                              
                              //nombre acces couple 
                              $reqcia="SELECT count(*) as total_ca FROM invite where sing = '$couple' AND acces = 'oui' AND cod_mar = '$codevent' AND siege = '{$row_inv['siege']}'";
                              $cia=$pdo->query($reqcia);
                              $row_accoupl=$cia->fetch();


                              $total_coupl_acces = $row_accoupl['total_ca'] * 2;
                              $total_coupl = $row_ci['total_ci'] * 2; 

                              $sing1 = "Mme";
                              $sing2 = "Mr";
                              
                              $reqci_s="SELECT count(*) as total_s FROM invite where (sing = '$sing1' OR sing = '$sing2') AND cod_mar = '$codevent' AND siege = '{$row_inv['siege']}'";
                              $ci_s=$pdo->query($reqci_s);
                              $row_ci_s=$ci_s->fetch();
                              
                              
                              //nombre acces sign 
                              $reqcias="SELECT count(*) as total_cas FROM invite where (sing = '$sing1' OR sing = '$sing2')  AND acces = 'oui' AND cod_mar = '$codevent' AND siege = '{$row_inv['siege']}'";
                              $cias=$pdo->query($reqcias);
                              $row_acsing=$cias->fetch();

                              $total_singl_sing = $row_acsing['total_cas'];
                              $total_singl = $row_ci_s['total_s'];

                              $nomb_inv = $total_coupl + $total_singl;
                              
                              
                              //nombre acces sign 
                              $nomb_acces = $total_coupl_acces + $total_singl_sing;
                              
                              //nombre des absents 
                              $nomb_absent = $nomb_inv - $nomb_acces;
                              


















                            if (isset($row_tab['plantable'])) {
                               $plan = $row_tab['plantable'];
                            }else{
                               $plan = 'noplan.png';
                            }


                            if ($row_inv['sing'] == 'C') {
                                $sing = 'Couple';
                                $invite = 'Invités';
                                $arrive = 'sont arrivés';
                            }elseif ($row_inv['sing'] == 'Mr'){
                                $sing = 'Monsieur';
                                $invite = 'Invité';
                                $arrive = 'est arrivé';
                            }elseif ($row_inv['sing'] == 'Mme'){
                                $sing = 'Madame';
                                $invite = 'Invitée';
                                $arrive = 'est arrivée';
                            }else{
                                $sing = '';
                                $invite = '';
                                $arrive = '';
                            }
                            
                        ?>
                          





                  <a href="index.php?page=access&cod=<?php echo $codevent?>"><h1 style="text-align:center;">Table <?php echo $nom_table;?></h1></a>
                  <h2 style="text-align:center;font-size:50px;font-family: 'Playfair Display"><?php echo $fetard; ?></h2>
                  <p style="text-align:center;letter-spacing:15px;"><span><?php echo date('d/m/Y',strtotime($date_event));?></span></p>
                


                  
                <div style="font-size: 1.3em; text-align: center;">
                    <span><b><?php echo $nomb_inv; ?></b></span> Invités <span style="color:#fff;">/</span>
                    <span style="color:#3bbc72;"><b><?php echo $nomb_acces; ?></b></span> Présents <span style="color:#fff;">/</span>
                    <span style="color:#F69D9D;"><b><?php echo $nomb_absent; ?></b></span> Absents
                </div>

                
                  <br> 




                        <h1 style="text-align:center;"><?php echo $invite;?></h1>
                        <h2 class="text-black" style="text-align:center;"><?php echo htmlspecialchars($sing.' '.$nom_invite); ?></h2>
                    
                        <br>
                        
                       
                       <div class="col-md-12" style="display: flex; justify-content: center;">
                            <img src="../event/pages/plantable/<?php echo htmlspecialchars($plan); ?>" width="100%" alt="Plan de table" style="max-width: 100%;">
                       </div> 
                       
 

                        <?php 

                            if (isset($_POST['submit'])) {
                                $acces = "oui";
                                $ha = date('Y-m-d H:i');

                                $sql = "UPDATE invite SET acces = :acces, heure_arrive = :heure_arrive WHERE id_inv = :id_inv";
                                $q = $pdo->prepare($sql);
                                $q->execute([
                                    ':acces' => $acces,
                                    ':heure_arrive' => $ha,
                                    ':id_inv' => $get
                                ]);
                                
                                echo "<script>window.location='index.php?page=gestion&cod=" . htmlspecialchars($_GET['cod']) . "';</script>";
                            }

                        ?>

                       




                                       

                                                                        
                                                                    
                            <?php 

                            if (!$row_inv['acces']) {
                            
                            ?>
                                        <div style="display: flex; justify-content: center;"> 
                                        
                                        


                                                                                <?php 
                                        // On compare la date de l'événement et la date du jour
                                        $today = date('Y-m-d');
                                        $date_event_jour = date('Y-m-d', strtotime($date_event));

                                        if (!$row_inv['acces']) {

                                            // Si la date de l'événement est aujourd'hui
                                            if ($date_event_jour === $today) {
                                        ?>
                                                <div style="display: flex; justify-content: center;"> 
                                                    <a class="btn btn-primary btn-lg" href="#" style="color:#fff;" title="Signaler l'arrivée" onclick="confirmAcces(event)">
                                                        <i class="fa fa-remove"></i> Confirmer l'arrivée
                                                    </a>
                                                </div>  

                                                <script>
                                                function confirmAcces(event) {
                                                    event.preventDefault(); // Empêche le lien de se déclencher
                                                    Swal.fire({
                                                        title: "Alert !",
                                                        text: "Voulez-vous confirmer l'arrivée de <?php echo addslashes($nom_invite); ?> ?",
                                                        icon: "warning",
                                                        showCancelButton: true,
                                                        confirmButtonText: "Oui",
                                                        cancelButtonText: "Non"
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            window.location.href = "index.php?page=pointacces&codinv=<?php echo $row_inv['id_inv']; ?>&cod=<?php echo $codevent; ?>";
                                                        }
                                                    });
                                                }
                                                </script> 
                                        <?php 
                                            } else {
                                                // Si la date est différente, bouton désactivé

                                                $cle_confirmation = "1234"; // <-- ta clé secrète
                                        ?>
 

                                                <div style="display: flex; justify-content: center;"> 
                                                    <a class="btn btn-primary btn-lg" href="#"
                                                        style="color:#fff;"
                                                        title="Signaler l'arrivée"
                                                        onclick="confirmAcces(event)">
                                                        <i class="fa fa-check"></i> Confirmer l'arrivée
                                                    </a>
 
                                                </div>







                                                <script>
                                                    function confirmAcces(event) {
                                                    event.preventDefault();

                                                    Swal.fire({
                                                        title: "Confirmer l’arrivée",
                                                        html: `
                                                        <div style="text-align:left;margin-top:10px;">
                                                            <label style="display:block;margin-bottom:6px;">Clé de confirmation</label>
                                                            <input id="cleConfirm" type="password" class="swal2-input"
                                                                placeholder="Entrez la clé"
                                                                autocomplete="off"
                                                                style="margin:0;width:100%;">
                                                        </div>
                                                        `,
                                                        icon: "question",
                                                        showCancelButton: true,
                                                        confirmButtonText: "Confirmer",
                                                        cancelButtonText: "Annuler",
                                                        focusConfirm: false,
                                                        preConfirm: () => {
                                                        const val = document.getElementById('cleConfirm').value.trim();
                                                        if (!val) {
                                                            Swal.showValidationMessage("Veuillez entrer la clé de confirmation.");
                                                            return false;
                                                        }
                                                        return val;
                                                        }
                                                    }).then((result) => {
                                                        if (!result.isConfirmed) return;

                                                        const cleSaisie = result.value;

                                                        // clé attendue (injectée depuis PHP)
                                                        const cleAttendue = <?php echo json_encode($cle_confirmation); ?>;

                                                        if (cleSaisie !== cleAttendue) {
                                                        Swal.fire({
                                                            icon: "error",
                                                            title: "Clé incorrecte",
                                                            text: "La clé de confirmation est invalide."
                                                        });
                                                        return;
                                                        }

                                                        // ✅ OK => redirection
                                                        window.location.href =
                                                        "index.php?page=pointacces&codinv=<?php echo (int)$row_inv['id_inv']; ?>&cod=<?php echo urlencode($codevent); ?>";
                                                    });
                                                    }
                                                    </script>




                                        <?php
                                            }

                                        } else {
                                        ?>
                                            <div style="display: flex; justify-content: center;">
                                                <a style="font-size: 1.3em;color:#3bbc72;" href="#" target="_blank">
                                                    <?php echo htmlspecialchars($sing.' '.$nom_invite.' '.$arrive); ?> à 
                                                    <?php echo date('H : i', strtotime($row_inv['heure_arrive'])); ?>
                                                </a>
                                            </div>
                                        <?php 
                                        }
                                        ?>







                                        </div>  











                                        <script>
                                            
                                            function confirmAcces(event) {
                                                event.preventDefault(); // Empêche le lien de se déclencher
                                                Swal.fire({
                                                    title: "Alert !",
                                                    text: "Voulez - vous confirmer l'arrivée de <?php echo $nom_invite;?> ?",
                                                    icon: "warning", // Utilisez "warning" pour une alerte de confirmation
                                                    showCancelButton: true,
                                                    confirmButtonText: "Oui",
                                                    cancelButtonText: "Non"
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        window.location.href = "index.php?page=pointacces&codinv=<?php echo $row_inv['id_inv'];?>&cod=<?php echo $codevent;?>";
                                                    }
                                                });
                                            }

                                        </script> 

<?php 


 }else{
  ?>

                 <div style="display: flex; justify-content: center;">
                    <a style="font-size: 1.3em;color:#3bbc72;" href="" target="_blank"><?php echo htmlspecialchars($sing.' '.$nom_invite.' '.$arrive); ?> à <?php echo date('H : i',strtotime($row_inv['heure_arrive'])) ?></a>
                 </div>

    <?php
 } 

 ?>

      



           <br>


           


           <table width="100%" style="margin-bottom:140px;">
                    


                    <tr>
                      <th style="text-align:center !IMPORTANT;text-transform:uppercase;">
                        Autres invités
                      </th> 
                    </tr>
    
                     
                    
                    <?php 

                              $reqinvget="SELECT * FROM invite where cod_mar = '$codevent' and siege = '{$row_inv['siege']}' AND id_inv != '$get' ORDER by nom ASC";
                              $invget=$pdo->query($reqinvget);
                              while($row_invget=$invget->fetch()){

                                if (!$row_invget['acces']) {
                                  $color = '';
                                }elseif($row_invget['acces'] == "oui"){
                                  $color = '#3bbc72';
                                }
  
                     ?>

                    <tr style="margin-bottom:15px;">
                      <td  align="center" style="border-bottom:1px solid #eee;padding: 7px 0px 7px 0px;">
                    <a href="index.php?page=access_cible&codinv=<?php echo $row_invget['id_inv']; ?>&cod=<?php echo $codevent; ?>" style="color: <?php echo $color; ?>">
                      <?php echo $row_invget['nom']; ?>
                        
                      </a></td> 
                      
                    </tr>
                              <?php 
                                  
                              }
                              
                              ?> 
                  </table>

                    </div>
                </div> 







            </div>
         </section>
      
































         <!--End hero section-->
        
         <section class="footer-copyright spacer-double-sm bg-white text-center">
            <p class="text-uppercase small text-muted d-block mb-0">&copy; <?php echo date('Y')?> Hubert Solutions all right reserved</p>
            <p class="text-muted small d-block mb-0">Siteweb, branche de </span> <a  href="https://www.invitationspeciale.com">invitationspeciale.com</a><br> 
			Sous : <a href="https://hubertlutunga.com">Hubert Lutunga</a></p>
         </section>
         <!--To the top-->
    
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