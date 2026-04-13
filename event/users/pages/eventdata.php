 
<style>
.admin-actions-toggle{ width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; border:1px solid #f5d08a; background:linear-gradient(180deg,#fff7e8 0%,#ffe7b8 100%); color:#8a5200 !important; box-shadow:0 10px 24px rgba(138,82,0,.16); transition:transform .18s ease, box-shadow .18s ease, background .18s ease }
.admin-actions-toggle:hover{ transform:translateY(-1px); box-shadow:0 14px 28px rgba(138,82,0,.22); background:linear-gradient(180deg,#fffaf0 0%,#ffdd9c 100%) }
.admin-actions-toggle i{ font-size:18px !important }
.event-actions-menu{ min-width:240px; padding:10px; border:0; border-radius:18px; background:#fff; box-shadow:0 20px 45px rgba(15,23,42,.16) }
.event-actions-menu .dropdown-divider{ margin:8px 0; border-color:#edf2f7 }
.event-actions-menu .action-item{ display:flex; align-items:center; gap:10px; border-radius:12px; padding:10px 12px; font-weight:600; color:#1f2937; transition:background .16s ease, transform .16s ease, color .16s ease }
.event-actions-menu .action-item:hover{ transform:translateX(2px); background:#f8fafc; color:#111827 }
.event-actions-menu .action-item i{ width:18px; text-align:center }
.event-actions-menu .action-money{ color:#0f766e }
.event-actions-menu .action-doc{ color:#1d4ed8 }
.event-actions-menu .action-edit{ color:#7c3aed }
.event-actions-menu .action-client{ color:#0f172a }
.event-actions-menu .action-danger{ color:#b91c1c }
.event-actions-menu .action-state{ color:#0f766e }
.event-actions-menu .action-progress{ color:#1d4ed8 }
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

                            $fmt = new IntlDateFormatter(
                                'fr_FR',
                                IntlDateFormatter::LONG,
                                IntlDateFormatter::NONE,
                                null,
                                IntlDateFormatter::GREGORIAN,
                                'EEEE, dd/MM/yyyy à HH:mm'
                            );
 
                             if ($stmt->rowCount() > 0) {
 


                                 while ($dataevent = $stmt->fetch(PDO::FETCH_ASSOC)) { 
 
                                    // $stmtfact = $pdo->prepare("SELECT count(*) as total_fact FROM facture where reference = ?");
                                    // $stmtfact->execute([$dataevent['cod_event']]); 
                                    // $row_fact = $stmtfact->fetch(PDO::FETCH_ASSOC);

                                    $codevent = $dataevent['cod_event'] ?? '';
                                    $type_event = $dataevent['type_event'] ?? '';

                                    if ($dataevent['fact'] === 'oui') {
                                        $badgepaie = "<span></span>";
                                        $br = "<br>";
                                        $br2 = "";
                                    }else{
                                        $badgepaie = "<span style='position: relative; z-index:10; top: 4px; left: -15px; background-color:rgb(151, 152, 152); color: white; padding: 5px 10px 5px 25px; border-radius: 0 0 20px 0px; font-size: 14px; font-weight: bold;'>non payé</span>";
                            
                                        $br = "";
                                        $br2 = "<br>";
                                    }

                                    $eventDisplay = EventBackofficeService::decorateEvent(
                                        $pdo,
                                        $dataevent,
                                        $fmt,
                                        $GLOBALS['isAppConfig'] ?? ['base_url' => 'https://invitationspeciale.com']
                                    );

                                    $color2 = $eventDisplay['color2'];
                                    $icon = $eventDisplay['icon'];
                                    $typeevent = $eventDisplay['typeevent'];
                                    $fetard = $eventDisplay['fetard'];
                                    $displayvue = $eventDisplay['displayvue'];
                                    $formatted_date = $eventDisplay['formatted_date'];
                                    $qrFile = $eventDisplay['qrFile'];
                                    $stmtClientModal = $pdo->prepare("SELECT cod_user, type_user, noms, phone, email, recpass FROM is_users WHERE cod_user = ? LIMIT 1");
                                    $stmtClientModal->execute([$dataevent['cod_user']]);
                                    $dataClientModal = $stmtClientModal->fetch(PDO::FETCH_ASSOC) ?: [];

                                    if (($dataevent['crea'] ?? null) == '2') {
                                        $bg = 'background-color:#F6FCF5';
                                        $badge = "<span style='position: relative; top: 4px; left: 0px; background-color:rgb(0, 129, 97); color: white; padding: 5px 10px; border-radius: 0 0 20px 0px; font-size: 14px; font-weight: bold; z-index:20;'>Terminé</span>";
                                    } else {
                                        $bg = '';
                                        $badge = "<span style='position: relative; top: 4px; left: 0px; background-color: #FF5733; color: white; padding: 5px 10px; border-radius: 0 0 20px 0px; font-size: 14px; font-weight: bold; z-index:20;'>Nouveaux</span>" . $br;
                                    }
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
                                 $accessoireNomLower = function_exists('mb_strtolower') ? mb_strtolower($accessoire, 'UTF-8') : strtolower($accessoire);
                                 $accessoireNomNormalized = str_replace(['é', 'è', 'ê', 'ë'], 'e', $accessoireNomLower);
                                 $isInvitationElectronique = strpos($accessoireNomNormalized, 'invitation') !== false && strpos($accessoireNomNormalized, 'elect') !== false;
                                 $quantiteAccessoire = isset($dataae['quantite']) ? (int) $dataae['quantite'] : 1;
                                 $quantiteLabel = $isInvitationElectronique
                                     ? ' <span style="display:inline-flex;align-items:center;margin-left:6px;padding:2px 8px;border-radius:999px;background:#e0f2fe;color:#075985;font-size:12px;font-weight:700;">Illimité</span>'
                                     : ' <span style="display:inline-flex;align-items:center;margin-left:6px;padding:2px 8px;border-radius:999px;background:#ecfdf5;color:#166534;font-size:12px;font-weight:700;">Qté : ' . max(1, $quantiteAccessoire) . '</span>';
 
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
                 echo $accessoire . $quantiteLabel . ' <span class="hoverx-container"><a target="_blink" href="https://invitationspeciale.com/event/images/modeleis/'.$image_inv.'" class="modelx-link">'.$modele_inv.'</a>';
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
                            
                            
                            
                                    <img src="<?php echo $qrFile; ?>" alt="QR Code" class="square-img-qr">
                            
                            
                            
                            
                            
                            
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
                                

                                        foreach (EventPrintService::listFilesByEvent($pdo, (int) $dataevent['cod_event']) as $datafile) {
                                            echo '<a href="../pages/fichiersprint/' . htmlspecialchars($datafile['nom_fichier']) . '" download>' . htmlspecialchars($datafile['nom_fichier']) . '</a><br>';
                                        }
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
                                          
  
                           <a href="#" class="admin-actions-toggle list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                                            <div class="dropdown-menu dropdown-menu-end event-actions-menu">
 <?php 
     $paymentMeta = EventBackofficeService::resolvePaymentMeta($pdo, $dataevent);
     if ($datasession['type_user'] !== '3') {
         ?>

  
                              <?php if (!$paymentMeta['has_payment']) { ?>
                              <a class="dropdown-item action-item action-doc" href="index.php?page=paiement&cod=<?php echo $codevent?>&mode=devis"><i class="fa fa-file-text"></i> Devis</a>
                              <?php } ?>
                              <?php if (!$paymentMeta['is_fully_paid']) { ?>
                              <a class="dropdown-item action-item action-money" href="<?php echo htmlspecialchars($paymentMeta['payment_action_url']); ?>"><i class="fa fa-dollar-sign"></i> <?php echo htmlspecialchars($paymentMeta['payment_action_label']); ?></a>
                              <?php } ?>
                              <?php if ($paymentMeta['invoice_pdf_url'] !== null) { ?>
                              <a href="<?php echo htmlspecialchars($paymentMeta['invoice_pdf_url']); ?>" target="_blank" class="dropdown-item action-item action-doc"><i class="fa fa-print"></i> Facture</a>
                              <?php } ?>
                       <div class="dropdown-divider"></div>
                       <a href="index.php?page=modevent&cod=<?php echo htmlspecialchars($dataevent['cod_event']); ?>" class="dropdown-item action-item action-edit"><i class="fa fa-pencil"></i> Modifier</a> 
                              <a
                                  onclick="openModal2(this); return false;"
                                  class="dropdown-item action-item action-doc"
                                  href="#"
                                  data-cod-event="<?php echo htmlspecialchars((string) $dataevent['cod_event'], ENT_QUOTES, 'UTF-8'); ?>"
                                  data-invit-religieux="<?php echo htmlspecialchars((string) ($dataevent['invit_religieux'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-ajustenom="<?php echo htmlspecialchars((string) ($dataevent['ajustenom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-taillenominv="<?php echo htmlspecialchars((string) ($dataevent['taillenominv'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-alignnominv="<?php echo htmlspecialchars((string) ($dataevent['alignnominv'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-pagenom="<?php echo htmlspecialchars((string) ($dataevent['pagenom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-pagebouton="<?php echo htmlspecialchars((string) ($dataevent['pagebouton'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-colornom="<?php echo htmlspecialchars((string) ($dataevent['colornom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-bordgauchenominv="<?php echo htmlspecialchars((string) ($dataevent['bordgauchenominv'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-qrcode="<?php echo htmlspecialchars((string) ($dataevent['qrcode'] ?? 'non'), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-pageqr="<?php echo htmlspecialchars((string) ($dataevent['pageqr'] ?? '3'), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-hautqr="<?php echo htmlspecialchars((string) ($dataevent['hautqr'] ?? '18'), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-gaucheqr="<?php echo htmlspecialchars((string) ($dataevent['gaucheqr'] ?? '52'), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-tailleqr="<?php echo htmlspecialchars((string) ($dataevent['tailleqr'] ?? '90'), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-lang="<?php echo htmlspecialchars((string) ($dataevent['lang'] ?? 'fr'), ENT_QUOTES, 'UTF-8'); ?>"
                              ><i class="fa fa-file"></i> Inv électronique</a>
                              <a
                                  onclick="openClientModal(this); return false;"
                                  class="dropdown-item action-item action-client"
                                  href="#"
                                  data-client-code="<?php echo htmlspecialchars((string) ($dataClientModal['cod_user'] ?? $dataevent['cod_user'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-client-type="<?php echo htmlspecialchars((string) ($dataClientModal['type_user'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-client-name="<?php echo htmlspecialchars((string) ($dataClientModal['noms'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-client-phone="<?php echo htmlspecialchars((string) ($dataClientModal['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-client-email="<?php echo htmlspecialchars((string) ($dataClientModal['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  data-client-password="<?php echo htmlspecialchars((string) ($dataClientModal['recpass'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                              ><i class="fa fa-user"></i> Clients</a>
            
                       <a href="#" title="Suppression" onclick="confirmSuppEvent(event, '<?php echo htmlspecialchars(ucfirst($typeevent)); ?>', '<?php echo htmlspecialchars($fetard); ?>', '<?php echo htmlspecialchars($dataevent['cod_event']); ?>')" class="dropdown-item action-item action-danger">
                            <i class="fa fa-remove"></i> Supprimer</a>

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

                                function escapeClientModalHtml(value) {
                                    return String(value || '').replace(/[&<>"']/g, function (char) {
                                        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
                                    });
                                }

                                function openClientModal(trigger) {
                                    var dataset = trigger && trigger.dataset ? trigger.dataset : {};
                                    var typeLabels = { '1': 'Administrateur', '2': 'Client', '3': 'Créateur' };
                                    var rows = [
                                        ['Code client', dataset.clientCode || '-'],
                                        ['Nom', dataset.clientName || '-'],
                                        ['Téléphone', dataset.clientPhone || '-'],
                                        ['Email', dataset.clientEmail || '-'],
                                        ['Type de compte', typeLabels[dataset.clientType] || dataset.clientType || '-'],
                                        ['Mot de passe', dataset.clientPassword || 'Non renseigné']
                                    ].map(function (item) {
                                        return '<tr>' +
                                            '<th style="width:38%; background:#f8fafc; color:#334155; padding:10px 12px; border:1px solid #e2e8f0;">' + escapeClientModalHtml(item[0]) + '</th>' +
                                            '<td style="padding:10px 12px; border:1px solid #e2e8f0; color:#0f172a;">' + escapeClientModalHtml(item[1]) + '</td>' +
                                        '</tr>';
                                    }).join('');

                                    Swal.fire({
                                        title: 'Informations client',
                                        html: '<div style="text-align:left;"><table style="width:100%; border-collapse:collapse;">' + rows + '</table></div>',
                                        width: 680,
                                        confirmButtonText: 'Fermer'
                                    });
                                }
                            </script> 
      
      
      
      <?php
    }
 ?>
                        <a onclick="openModal('<?php echo htmlspecialchars(ucfirst($dataevent['cod_event'])); ?>', '<?php echo $dataevent['cod_event']; ?>')" class="dropdown-item action-item action-state" href="#"><i class="fa fa-check"></i>Terminer</a>
                        <span class="dropdown-item text-muted"><i class="fas fa-question-circle"></i>Signaler à implémenter</span> 
                         
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

                                                                 