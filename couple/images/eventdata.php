 
<style>
 
 </style>   
 
 <?php 
                          if ($datasession['type_user'] == '2') {
                                $condition = "WHERE fact IS NOT NULL";
                            } elseif ($datasession['type_user'] == '1') {
                                $condition = "";
                            } else { 
                                $condition = "WHERE fact IS NOT NULL";
                            }
                            
                            $stmt = $pdo->prepare("SELECT * FROM events $condition ORDER BY cod_event DESC");
                            $stmt->execute();
 
                             if ($stmt->rowCount() > 0) {
 


                                 while ($dataevent = $stmt->fetch(PDO::FETCH_ASSOC)) { 
 
                                    // $stmtfact = $pdo->prepare("SELECT count(*) as total_fact FROM facture where reference = ?");
                                    // $stmtfact->execute([$dataevent['cod_event']]); 
                                    // $row_fact = $stmtfact->fetch(PDO::FETCH_ASSOC);

                                    if ($dataevent['fact'] === 'oui') {
                                        $badgepaie = "<span></span>";
                                        $br = "<br>";
                                        $br2 = "";
                                    }else{
                                        $badgepaie = "<span style='position: relative; top: 4px; left: -15px; background-color:rgb(151, 152, 152); color: white; padding: 5px 10px 5px 25px; border-radius: 0 0 20px 0px; font-size: 14px; font-weight: bold;'>non payé</span>";
                            
                                        $br = "";
                                        $br2 = "<br>";
                                    }

                                  if ($dataevent['crea'] == "2") {
                                    $color = 'color:#16A542;';
                                    $color2 = 'color:#34A37B';
                                    $icon = '<i class="fas fa-check fs-24 l-h-50" style="color:#34A37B;"></i>';
                                    $bg = 'background-color:#F6FCF5';
                                    $badge = "<span style='position: relative; top: 4px; left: 0px; background-color:rgb(0, 129, 97); color: white; padding: 5px 10px; border-radius: 0 0 20px 0px; font-size: 14px; font-weight: bold;z-index:10;'>Terminé</span>";
                                 
                                  }else{
                                    $color = '';
                                    $color2 = '';
                                    $icon = '';
                                    $bg = '';
                                    $badge = "<span style='position: relative; top: 4px; left: 0px; background-color: #FF5733; color: white; padding: 5px 10px; border-radius: 0 0 20px 0px; font-size: 14px; font-weight: bold;z-index:10;'>Nouveaux</span>".$br;
                                 }


                                     if (!$dataevent) {
 
                                         $codevent = '';
                                         $date_event = '';
                                         $type_event = '';
                                         $display = 'none';
                         
                                     } else {  
                                         
                                         $date_event = $dataevent['date_event'];
                                         $type_event = $dataevent['type_event'];
                                         $display = 'block';
                                         $codevent = $dataevent['cod_event'];
                         
                                     }
                         
                                     
                                     $stmtnv = $pdo->prepare("SELECT * FROM evenement WHERE cod_event = ?");
                                     $stmtnv->execute([$type_event]); // Correction ici pour utiliser $codevent
                                     $data_evenement = $stmtnv->fetch();
                         
                                     if (!$data_evenement) {
                                         $data_evenement = ''; 
                                     } else {  
                                         
                                         $data_evenement = $data_evenement['nom'];
                                     }
 
 
                                     if ($type_event == "1") {
                                         $typeevent = 'Mariage ' . ($dataevent['type_mar'] ?? 'Inconnu');
                                         $displayvue = 'display:block;';
                                         $fetard = (($dataevent['prenom_epouse'] ?? '') . ' & ' . ($dataevent['prenom_epoux'] ?? '')) ?: 'Inconnu';
                                     } elseif ($type_event == "2" || $type_event == "3") {
                                         $typeevent = $data_evenement;
                                         $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
                                         $displayvue = 'display:none;';
                                     }
                             
 
 
 // Assure-toi que l'extension Intl est activée
 //$date_event = $date_event; // Format : 'Y-m-d H:i:s'
 
 $dateStr = $date_event; // Votre variable de date
 
 if ($dateStr !== null) {
     $date = new DateTime($dateStr);
 } else {
     // Gérer le cas où la date est nulle
     $date = new DateTime(); // Crée une date par défaut (date actuelle)
 }
  
 
 // Création d'un formatteur pour le français
 $formatter = new IntlDateFormatter(
     'fr_FR',
     IntlDateFormatter::LONG,
     IntlDateFormatter::NONE,
     null,
     IntlDateFormatter::GREGORIAN,
     'EEEE, dd/MM/yyyy à HH:mm' // Utilisez MM pour les mois
 );
 
 // Formatage de la date
 $formatted_date = $formatter->format($date);
 
 // Met la première lettre en majuscule
 $formatted_date = ucfirst($formatted_date); 
                             ?>
 
 
 
 
                                     <tr>
 
                                         <td class="pt-0 px-0" style=" position: relative;padding-left:10px;border-botton:1px solid #000;padding-top:30px;">
                                      
                                     
                                  <?php echo $badge.$badgepaie.$br2;?> <br>

                                         
                                         <div class="row">
                <div class="col-md-6 col-12">
                                 
                               
                         <a class="d-block fw-500 fs-14" style="<?php echo $color2;?>" href="#"><?php echo '('.$dataevent['cod_event'].') '.htmlspecialchars(ucfirst($typeevent)); ?>, <span class="text-fade" style="<?php echo $color2;?>"><?php echo $fetard; ?></span> <?php echo $icon?></a>
                                             
                                             <span><?php echo $formatted_date; ?></span>
 
                             <?php 
                                             
                                             
                             $stmtae = $pdo->prepare("SELECT * FROM accessoires_event where cod_event = ? ORDER BY cod_accev DESC");
                             $stmtae->execute([$dataevent['cod_event']]); 
 
                             while ($dataae = $stmtae->fetch(PDO::FETCH_ASSOC)) {
 
 
                                 $stmtnv = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
                                 $stmtnv->execute([$dataae['cod_acc']]); // Correction ici pour utiliser $codevent
                                 $data_accessoire = $stmtnv->fetch();
 
                                 $accessoire = isset($data_accessoire['nom']) ? $data_accessoire['nom'] : '';
 
                                 if ($dataae['cod_acc'] == "1") {
 
                                     $stmtmi = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
                                     $stmtmi->execute([$dataevent['modele_inv']]); // Correction ici pour utiliser $codevent
                                     $data_modele = $stmtmi->fetch();
 
                                     $modele_inv = isset($data_modele['nom']) ? '('.$data_modele['nom'].')' : '';
                                     $image_inv = isset($data_modele['image']) ? $data_modele['image'] : '';
 
                                 }elseif ($dataae['cod_acc'] == "3") {
 
                                     $stmtmc = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
                                     $stmtmc->execute([$dataevent['modele_chev']]); // Correction ici pour utiliser $codevent
                                     $data_modelechev = $stmtmc->fetch();
 
                                     $modele_inv = isset($data_modelechev['nom']) ? '('.$data_modelechev['nom'].')' : '';
 
                                     $image_inv = '';
 
                                 }else{
                                     $modele_inv = '';
 
                                     $image_inv = '';
                                 }
 
                                 ?>
                                 <br> 
 
 
 
 <!--  Hover Image -->
 
      <style>
         .hoverx-image {
             display: none; /* Cache l'image par défaut */
             position: absolute;
             z-index: 9000;
             max-width: 200px; /* Ajustez la taille maximale de l'image */
             border: 1px solid #ccc;
             background-color: white;
             padding: 5px;
             box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); 
         }
         .hoverx-container {
             position: relative; /* Permet à l'image d'être positionnée par rapport à son conteneur */
         }
     </style> 
 
 
 
                 <em>
                 <?php 
                 echo $accessoire.' <span class="hoverx-container"><a target="_blink" href="https://invitationspeciale.com/event/images/modeleis/'.$image_inv.'" class="modelx-link">'.$modele_inv.'</a>';
                 ?>
                 <img class="hoverx-image" src="../images/modeleis/<?php echo $image_inv; ?>" alt="Image" />
                 </em>
 
 
 
 
 
 
 <script>
     // Sélectionne le lien et l'image
     const modelLink = document.querySelector('.modelx-link');
     const hoverImage = document.querySelector('.hoverx-image');
 
     // Affiche l'image au survol
     modelLink.addEventListener('mouseover', function(event) {
         hoverImage.style.display = 'block'; // Affiche l'image
         hoverImage.style.top = event.target.getBoundingClientRect().bottom + 'px'; // Positionne l'image
         hoverImage.style.left = event.target.getBoundingClientRect().left + 'px'; // Positionne l'image
     });
 
     // Cache l'image lorsque le curseur quitte le lien
     modelLink.addEventListener('mouseout', function() {
         hoverImage.style.display = 'none'; // Cache l'image
     });
 </script>
 
 <!-- Fin Hover Image -->
 
 
                                 <?php
 
                                 }
                                                 
                                 ?>
 
                     <?php if ($type_event == "1") { ?>
 
                                 <br> <span><b>Epoux : </b><?php echo $dataevent['prenom_epoux'].' '.$dataevent['nom_epoux'];?></span>
                                 <br> <span><b>Epouse : </b><?php echo $dataevent['prenom_epouse'].' '.$dataevent['nom_epouse'];?></span>
                                 <br> <span><b>Famille epoux : </b><?php echo $dataevent['nom_familleepoux'];?></span>
                                 <br> <span><b>Famille epouse : </b><?php echo $dataevent['nom_familleepouse'];?></span>
 
                     <?php } elseif ($type_event == "3"){ ?>
 
                                 <br> <span><b>Theme : </b><?php echo $dataevent['themeconf'];?></span>
 
                     <?php } ?>                                
                                 
                                 <br> <span><b>Autres précisions : </b><?php echo nl2br(htmlspecialchars($dataevent['autres_precisions']));?></span>
                                 <br> <span><b>Lieu : </b><?php echo $dataevent['lieu']?></span>
                                 <br> <span><b>Adresse : </b><?php echo isset($dataevent['adresse']) ? '('.$dataevent['adresse'].')' : 'Non défini';?></span>
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
                                 <br>
                                 <br>
 
 
 
         </div>
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
         <div class="col-md-6 col-12">
            <div class="row">
                <div class="col-md-12">
 
                            
                                    <style>
                                        .square-img {
                                            width: 100px; /* Largeur fixe */
                                            height: 100px; /* Hauteur fixe */
                                            object-fit: cover; /* Recadre l'image pour remplir le carré sans déformation */
                                            float: left;
                                            margin-right: 15px;
                                            margin-bottom: 15px;
                                        } 
                                        .square-img-qr {
                                            width: 100px; /* Largeur fixe */
                                            height: 100px; /* Hauteur fixe */
                                            object-fit: cover; /* Recadre l'image pour remplir le carré sans déformation */
                                            float: left;
                                            margin-right: 15px;
                                            margin-bottom: 15px;
                                            border:1px solid #000 !important;
                                        } 
                                    </style>
                            
                            
                            
                                    <?php
                            
                            
                                    // Remplacez 'temp/' par 'mesqrcode/'
                                    $PNG_TEMP_DIR = 'mesqrcode/';
                            
                                    // Vérifiez si le dossier existe
                                    if (!file_exists($PNG_TEMP_DIR)) {
                                        echo "Le dossier 'mesqrcode' n'existe pas.";
                                        exit; // Arrêtez le script si le dossier n'existe pas
                                    }
                                    
                                    $codeString = 'https://invitationspeciale.com/site/index.php?page=accueil&cod=' . $codevent . '&extra_data=' . str_repeat('data', 10); // Ajout de données
                                    $filename = $PNG_TEMP_DIR . 'fp_qr' . md5($codeString) . '.png';
                                    
                                    // Vérifiez si le QR code a déjà été généré
                                    if (!file_exists($filename)) {
                                        // Spécifiez les paramètres pour augmenter la taille et la qualité
                                        $errorCorrectionLevel = 'H'; // Niveau de correction d'erreur (H pour haute)
                                        $matrixPointSize = 15; // Augmenter à 15 ou plus
                                        $margin = 4; // Marge autour du QR code
                                    
                                        // Générer le QR code avec des paramètres de haute résolution
                                        QRcode::png($codeString, $filename, $errorCorrectionLevel, $matrixPointSize, $margin);
                                    } else {
                                        // echo "Le QR code existe déjà.";
                                    }
                                    
                            
                            ?>
                                    
                            
                                    <img src="<?php echo $PNG_TEMP_DIR . 'fp_qr' . md5($codeString) . '.png'; ?>" alt="QR Code" class="square-img-qr">
                            
                            
                            
                            
                            
                            
                                    <?php 
                                    $stmtimg = $pdo->prepare("SELECT * FROM photos_event where cod_event = ? ORDER BY cod_photo DESC");
                                    $stmtimg->execute([$dataevent['cod_event']]); 
                            
                                    while ($dataphoto = $stmtimg->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <img src="../photosevent/<?php echo $dataphoto['nom_photo']?>" alt="<?php echo $dataphoto['nom_photo']?>" class="square-img">
                                    <?php } ?>
                            
                            
                </div>

                <div class="col-md-12">
                 
                                    <?php 
                                        if ($dataevent['crea'] == '2') {
                                            
                                    $stmtrap = $pdo->prepare("SELECT * FROM creaevent where cod_event = ?");
                                    $stmtrap->execute([$dataevent['cod_event']]); 
                                    $datarapport = $stmtrap->fetch(PDO::FETCH_ASSOC);

                                    if($datarapport){ 

                                        $obser = $datarapport['observation'] ? : '';

                                        $stmtrecus = $pdo->prepare("SELECT * FROM is_users WHERE cod_user = ?");
                                        $stmtrecus->execute([$datarapport['cod_user']]);
                                        $datarecuser = $stmtrecus->fetch();



                                        echo '<br><br><em>Réalisé par '.$datarecuser['noms'].'</em><br>';
                                        echo '<p>'.$obser.'</p>';
                                

                                        $stmtfile = $pdo->prepare("SELECT * FROM fichiers_impression where cod_event = ?");
                                        $stmtfile->execute([$dataevent['cod_event']]);

                                        while ($datafile = $stmtfile->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <a href="../pages/fichiersprint/<?php echo $datafile['nom_fichier']?>" download><?php echo $datafile['nom_fichier']?></a><br>
                                            <?php } ?>


                                                <?php
                                            }

                                    }
                                    ?>
                
                </div>

            </div>                               
                            
                            
                            
                    
    </div>
            </div>
       
                                        </td> 
                                        
                                        <td class="text-end pt-0 px-0" width="15%" style="padding-right:10px;border-botton:1px solid #000;"> 
                                                        
                                         
 
 
 
 
 
 
 
 
  
 
 
  
 
 
 
                                             <div class="list-icons d-inline-flex">
                          <div class="list-icons-item dropdown">
                                          
  
                           <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                                     <div class="dropdown-menu dropdown-menu-end">
 <?php 
    if ($datasession['type_user'] !== '3') {
       ?>

  
                       <a class="dropdown-item" href="index.php?page=paiement&cod=<?php echo $codevent?>"><i class="fa fa-dollar-sign"></i>Confirmer</a>
                       
                       <a href="#" class="dropdown-item"><i class="fas fa-question-circle"></i>Instruction</a>
                       <a href="#" class="dropdown-item"><i class="fa fa-print"></i> Facture</a>
                       <div class="dropdown-divider"></div>
                       <a href="#" class="dropdown-item"><i class="fa fa-pencil"></i> Modifier</a>
            
            
                       <a href="#" title="Suppression" onclick="confirmSuppEvent(event, '<?php echo htmlspecialchars(ucfirst($typeevent)); ?>', '<?php echo htmlspecialchars($fetard); ?>', '<?php echo htmlspecialchars($dataevent['cod_event']); ?>')" class="dropdown-item">
    <i class="fa fa-remove"></i> Supprimer
</a>

<script>
    function confirmSuppEvent(event, typeEvent, fetard, codeEvent) {
        event.preventDefault(); // Empêche le lien de se déclencher
        Swal.fire({
            title: "Supprimer !",
            text: "Êtes-vous sûr de vouloir supprimer (" + typeEvent + " de " + fetard + ", code: " + codeEvent + ") ?",
            icon: "warning", // Utilisez "warning" pour une alerte de confirmation
            showCancelButton: true,
            confirmButtonText: "Oui",
            cancelButtonText: "Non"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "index.php?page=supevent&cod=" + codeEvent;
            }
        });
    }
</script> 
      
      
      
      <?php
    }
 ?>
                        <a onclick="openModal('<?php echo htmlspecialchars(ucfirst($dataevent['cod_event'])); ?>', '<?php echo $dataevent['cod_event']; ?>')" class="dropdown-item" href="#"><i class="fa fa-check"></i>Terminer</a>
                        <a href="#" class="dropdown-item"><i class="fas fa-question-circle"></i>Signaler</a> 
                         
                        </div>
                         </div>
                       </div>
 
 
                                         </td>
                                     </tr>

                                
                            <?php 
 
                             }

                             ?>

                             <?php
 
                             } else {
                                 echo '<tr><td colspan="3" class="text-left" style="font-style:italic;">Aucune commande</td></tr>';
                             }
 
                            ?>

                                                                 