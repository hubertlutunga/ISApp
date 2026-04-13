
	
<div class="wrapper"> 
	 

     <?php 
     
           include('header_admin.php');
       
           include('../../qrscan/phpqrcode/qrlib.php');
       
   ?>
      
    
   
     <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
         <div class="container-full">
         <!-- Main content -->
   
     <!-- 
   
       <div class="content-header text-center">
         <div class="d-flex align-items-center">
           <div class="me-auto">
             <h3 class="page-title">Weather widgets</h3>
             <div class="d-inline-block align-items-center">
               <nav>
                 <ol class="breadcrumb">
                   <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                   <li class="breadcrumb-item" aria-current="page">Widgets</li>
                   <li class="breadcrumb-item active" aria-current="page">Weather widgets</li>
                 </ol>
               </nav>
             </div>
           </div>
           
         </div>
       </div> -->
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
           
   <div class="row salut">
   
   <?php 
   
   $heure = date('H');
   
   if ($heure < 12) {
   $salut = 'Bonjour';
   }elseif ($heure > 11 AND $heure < 15){
   $salut = 'Bon Après-midi';
   }elseif ($heure > 12){
   $salut = 'Bonsoir';
   }
    
   $codget = $_GET['cod'];
    $documentMode = (isset($_GET['mode']) && $_GET['mode'] === 'devis') ? 'devis' : 'facture';
    $isDevisMode = $documentMode === 'devis';
    $documentLabel = $isDevisMode ? 'Devis' : 'Paiement';
   
   ?>
   <p style="text-align:center;">
     <?php  // echo "La valeur de codevent est : " . $codevent; 
     echo $salut;?> <b>
     <?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, "UTF-8");?> </b>!
   </p>

   <?php
                             $stmt = $pdo->prepare("SELECT * FROM events where cod_event = :cod_event");
                             $stmt->execute(['cod_event' => $codget]);
  
 
                                 while ($dataevent = $stmt->fetch(PDO::FETCH_ASSOC)) { 
 
                                  if ($dataevent['crea'] == "2") {
                                    $color = 'color:#16A542;';
                                    $color2 = 'color:#34A37B';
                                    $icon = '<i class="fas fa-check fs-24 l-h-50" style="color:#34A37B;"></i>';
                                    $bg = 'background-color:#F6FCF5';
                                  }else{
                                    $color = '';
                                    $color2 = '';
                                    $icon = '';
                                    $bg = '';
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

                                    $paymentMeta = EventBackofficeService::resolvePaymentMeta($pdo, ['cod_event' => $codevent]);
                                    $currentPaid = (float) $paymentMeta['paid'];
                                    $currentRemaining = (float) $paymentMeta['remaining'];
                                    $currentInstruction = (string) ($dataevent['instruction'] ?? '');
                                    $currentDeliveryDate = (string) ($dataevent['date_livraison'] ?? '');
                         
                                     
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
 
                                         <td class="pt-0 px-0" style="padding-left:10px;border-botton:1px solid #000;background-color:DCF4F7;"><br>
                                          
                                         
                                         
            <div class="row">
                <div class="col-md-6 col-12">
                                 
                               
                         <a class="d-block fw-500 fs-14" style="<?php echo $color2;?>" href="#"><?php echo '('.$dataevent['cod_event'].') '.htmlspecialchars(ucfirst($typeevent)); ?>, <span class="text-fade" style="<?php echo $color2;?>"><?php echo $fetard; ?></span> <?php echo $icon?></a>
                                              
 
                             <?php 
                                             
                                             
                             $stmtae = $pdo->prepare("SELECT * FROM accessoires_event where cod_event = ? ORDER BY cod_accev DESC");
                             $stmtae->execute([$codget]); 
 
                             $dataae = $stmtae->fetch(PDO::FETCH_ASSOC);
 
 
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
 
  
 
  
        
 
 
 
 
 
 
 
 
 
 
 
 
  




















<?php 
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    $stmtmt = $pdo->prepare("SELECT SUM(pt) AS totalpt FROM details_fact WHERE cod_event = ?");
    $stmtmt->execute([$codget]); 
    $datamontanttotal = $stmtmt->fetch(PDO::FETCH_ASSOC);  
    $montant_total = $datamontanttotal ? (float) $datamontanttotal['totalpt'] : 0.0;

    $instruction = trim((string) ($_POST['observation'] ?? '')); 
    $dateliv = trim((string) ($_POST['dateliv'] ?? '')); 
    $type_paie = (string) ($_POST['type_paie'] ?? 'solde');  
    $paymentSubmitMode = (string) ($_POST['payment_submit_mode'] ?? '');
    $user = $dataevent['cod_user'];  
    $alreadyPaid = (float) ($currentPaid ?? 0.0);
    $remainingBefore = max($montant_total - $alreadyPaid, 0.0);
    $paymentStep = 0.0;

    $currentInstruction = $instruction;
    $currentDeliveryDate = $dateliv;

    if ($isDevisMode) {
        if ($montant_total <= 0) {
            echo '<script>Swal.fire({title:"Devis",text:"Aucun montant de commande n\'est disponible.",icon:"warning",confirmButtonText:"OK"});</script>';
        } elseif (!$dateliv) {
            echo 'Determiner la date de livraison';
        } else {
            try {
                $pdo->beginTransaction();

                $deleteDevis = $pdo->prepare("DELETE FROM facture WHERE reference = ? AND type_fact = 'Devis'");
                $deleteDevis->execute([$codget]);

                $insertDevis = $pdo->prepare(
                    "INSERT INTO facture (type_fact, reference, cod_cli, type_paie, montant_total, montant_paye, devise, date_enreg) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
                );
                $insertDevis->execute([
                    'Devis',
                    $codget,
                    $user,
                    'devis',
                    $montant_total,
                    0,
                    'USD',
                ]);

                $q = $pdo->prepare("UPDATE events SET instruction = :instruction, date_livraison = :date_livraison WHERE cod_event = :codevent");
                $q->bindValue(':instruction', $instruction);
                $q->bindValue(':date_livraison', $dateliv);
                $q->bindValue(':codevent', $codget);
                $q->execute();
                $q->closeCursor();

                $pdo->commit();

                echo '<script>
                Swal.fire({
                    title: "Devis",
                    text: "Le devis a été généré avec succès.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "pages/pdf/facture_hs.php?cod=' . rawurlencode((string) $codget) . '&type=devis";
                    }
                });
                </script>';
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                echo "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    } elseif ($montant_total <= 0) {
        echo '<script>Swal.fire({title:"Paiement",text:"Aucun montant de commande n\'est disponible.",icon:"warning",confirmButtonText:"OK"});</script>';
    } elseif ($remainingBefore <= 0) {
        echo '<script>Swal.fire({title:"Paiement",text:"Cette commande est déjà totalement réglée.",icon:"info",confirmButtonText:"OK"});</script>';
    } elseif (!$dateliv) {
        echo 'Determiner la date de livraison';
    } else {
        if ($type_paie === 'acompte') {
            $paymentStep = (float) str_replace(',', '.', (string) ($_POST['acompte'] ?? '0'));

            if ($paymentStep <= 0) {
                echo '<script>Swal.fire({title:"Paiement",text:"Le montant de l\'acompte doit être supérieur à 0.",icon:"warning",confirmButtonText:"OK"});</script>';
                $paymentStep = -1.0;
            } elseif ($paymentStep > $remainingBefore) {
                echo '<script>Swal.fire({title:"Paiement",text:"Le montant saisi dépasse le reste à encaisser.",icon:"warning",confirmButtonText:"OK"});</script>';
                $paymentStep = -1.0;
            }
        } else {
            $paymentStep = $remainingBefore;
        }

        if ($paymentStep > 0) {
            $newPaidAmount = min($alreadyPaid + $paymentStep, $montant_total);
            $effectiveType = $newPaidAmount < $montant_total ? 'acompte' : 'solde';
            $isFullyPaidNow = $newPaidAmount >= $montant_total;
            $shouldRedirectToFactures = $paymentSubmitMode === 'encaisser';

            try {
                $pdo->beginTransaction();

                $insertFacture = $pdo->prepare(
                    "INSERT INTO facture (type_fact, reference, cod_cli, type_paie, montant_total, montant_paye, devise, date_enreg) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
                );
                $insertFacture->execute([
                    'Facture',
                    $codget,
                    $user,
                    $effectiveType,
                    $montant_total,
                    $paymentStep,
                    'USD',
                ]);

                $q = $pdo->prepare("UPDATE events SET fact = :fact, instruction = :instruction, date_livraison = :date_livraison WHERE cod_event = :codevent"); 
                $q->bindValue(':fact', 'oui'); 
                $q->bindValue(':instruction', $instruction); 
                $q->bindValue(':date_livraison', $dateliv);  
                $q->bindValue(':codevent', $codget);  
                $q->execute();
                $q->closeCursor(); 

                $pdo->commit();

                $successText = $isFullyPaidNow
                    ? 'Le paiement a été soldé avec succès. La facture globale est à jour.'
                    : 'Le paiement a été enregistré. L\'historique des encaissements est conservé.';
                $redirectUrl = $shouldRedirectToFactures
                    ? 'index.php?page=factures'
                    : ($isFullyPaidNow
                        ? 'pages/pdf/facture_hs.php?cod=' . rawurlencode((string) $codget)
                        : 'index.php?page=paiement_fin&cod=' . rawurlencode((string) $codget));

                echo '<script>
                Swal.fire({
                    title: "Paiement",
                    text: ' . json_encode($successText) . ',
                    icon: "success",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = ' . json_encode($redirectUrl) . ';
                    }
                });
                </script>';
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                echo "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    }



}

}

?>






<form id="eventForm" action="" method="post" enctype="multipart/form-data">

 
<?php
$total = 0; // Initialisation de la variable pour stocker le montant total

$stmtdf = $pdo->prepare("SELECT * FROM details_fact WHERE cod_event = ?");
$stmtdf->execute([$codget]); // Remplacez par $codevent si nécessaire

while ($data_fact = $stmtdf->fetch()) {
    // Affichez les informations sur chaque facture
    echo '<p>' . htmlspecialchars($data_fact['libelle']) . ' / ' . htmlspecialchars($data_fact['qtecom']) . ' / ' . htmlspecialchars($data_fact['pu']) . ' / ' . htmlspecialchars($data_fact['pt']) . '</p>';
    
    // Ajoutez le montant à la variable totale
    $total += $data_fact['pt'];
}

// Affichez le montant total après la boucle
echo '<p><strong>Total : ' . htmlspecialchars(number_format($total, 2, ',', ' ')) . ' $ </strong></p>';
if (!$isDevisMode) {
echo '<p><strong>Déjà encaissé : ' . htmlspecialchars(number_format($currentPaid, 2, ',', ' ')) . ' $ </strong></p>';
echo '<p><strong>Reste à encaisser : ' . htmlspecialchars(number_format(max($total - $currentPaid, 0), 2, ',', ' ')) . ' $ </strong></p>';
}
?>




  <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span> 
            <textarea name="observation" class="form-control ps-15 bg-transparent" rows='5' placeholder="Observation"><?php echo htmlspecialchars($currentInstruction); ?></textarea>
        </div>
    </div>
  
    
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
            <input type="date" name="dateliv" class="form-control ps-15 bg-transparent" placeholder="Date de livraison" value="<?php echo htmlspecialchars($currentDeliveryDate); ?>" required>
        </div>
    </div>  
    
    
    <?php if (!$isDevisMode) { ?>
    <div class="form-group" style="border:1px solid #efefef;padding:10px 0 0 10px;height:45px;border-radius:5px;"> 
        <div class="demo-radio-button">
            <input name="type_paie" type="radio" id="solde" value = "solde" class="with-gap radio-col-primary" checked onchange="toggleAcompte()" />
            <label for="solde">Solde</label>
            <input name="type_paie" type="radio" id="acompte" value = "acompte" class="with-gap radio-col-success" onchange="toggleAcompte()" />
            <label for="acompte">Acompte</label>
        </div> 
    </div>

<div class="form-group" id="acompteField" style="display: none;">
    <div class="input-group mb-3">
        <span class="input-group-text bg-transparent"><i class="fas fa-dollar-sign"></i></span>
        <input type="text" name="acompte" class="form-control ps-15 bg-transparent" placeholder="Montant">
    </div>
</div>

<script>
function toggleAcompte() {
    const acompteField = document.getElementById('acompteField');
    const isAcompteChecked = document.getElementById('acompte').checked;

    if (isAcompteChecked) {
        acompteField.style.display = 'block';
    } else {
        acompteField.style.display = 'none';
    }
}

// Initialiser l'état à l'ouverture
toggleAcompte();
</script>
    <?php } ?>
    

    <div class="row"> 
        <div class="col-12 text-center">
            <input type="hidden" name="payment_submit_mode" value="<?php echo $isDevisMode ? 'devis' : ($currentPaid > 0 ? 'encaisser' : 'terminer'); ?>">
            <button type="submit" id="BtnEvent" class="btn btn-primary w-p100 mt-10"><?php echo $isDevisMode ? 'Générer le devis' : ($currentPaid > 0 ? 'Encaisser' : 'Terminer'); ?></button>
        </div>
    </div>
</form>
                    
         </div>

 
 
 
         </div>
 
 
 
  
 
 
 
         <div class="col-md-6 col-12">
                         
         

    </div>
       
                                      












                                    </td> 
                                        
                                       
                                     </tr>
 
                                 
                                    
   
                           </tbody>
                       </table>
                   </div>
               </div>	
   
   
   
   
   
   
   
   
   
           </div>
       </div>
   
   
   
   
        
   
   
   
    
   
   
   
   
   
   
  </section>
   
   
   
   
   
   
   
   
   
   
   
   
     
             </div>
   
             
           </div> 
         <!-- /.content -->
       </div>
     <!-- /.content-wrapper -->
     <?php include('footer.php')?> 

   </div>
   <!-- ./wrapper -->
     
     
        
     
     <!-- Page Content overlay -->
     
     
     <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
     <!-- Vendor JS -->
     <script src="html/template/horizontal/src/js/vendors.min.js"></script>
     <script src="html/template/horizontal/src/js/pages/chat-popup.js"></script>
       <script src="html/assets/icons/feather-icons/feather.min.js"></script>
       <script src="html/assets/vendor_components/Flot/jquery.flot.js"></script>
     <script src="html/assets/vendor_components/Flot/jquery.flot.resize.js"></script>
     <script src="html/assets/vendor_components/Flot/jquery.flot.pie.js"></script>
     <script src="html/assets/vendor_components/Flot/jquery.flot.categories.js"></script>
     <script src="html/assets/vendor_components/echarts/dist/echarts-en.min.js"></script>
     <script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
     <script src="html/assets/vendor_plugins/bootstrap-slider/bootstrap-slider.js"></script>
     <script src="html/assets/vendor_components/OwlCarousel2/dist/owl.carousel.js"></script>
     <script src="html/assets/vendor_components/flexslider/jquery.flexslider.js"></script>
     <script src="html/assets/vendor_components/Web-Ticker-master/jquery.webticker.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
   
     
     <!-- Cartiy Admin App -->
     <script src="html/template/horizontal/src/js/demo.js"></script>
     <script src="html/template/horizontal/src/js/jquery.smartmenus.js"></script>
     <script src="html/template/horizontal/src/js/menus.js"></script>
     <script src="html/template/horizontal/src/js/template.js"></script>
     <script src="html/template/horizontal/src/js/pages/dashboard.js"></script>
     <script src="html/template/horizontal/src/js/pages/slider.js"></script>
   
     
     <!-- Vendor JS --> 
     <script src="html/assets/vendor_components/full-calendar/moment.js"></script>
     <script src="html/assets/vendor_components/full-calendar/fullcalendar.min.js"></script> 
   
     
     
     <!-- selecter JS --> 
     <script src="html/assets/vendor_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
     <script src="html/assets/vendor_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
     <script src="html/assets/vendor_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
     <script src="html/assets/vendor_components/select2/dist/js/select2.full.js"></script>
     <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.js"></script>
     <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
     <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.extensions.js"></script>
     <script src="html/assets/vendor_components/moment/min/moment.min.js"></script>
     <script src="html/assets/vendor_components/bootstrap-daterangepicker/daterangepicker.js"></script>
     <script src="html/assets/vendor_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
     <script src="html/assets/vendor_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
     <script src="html/assets/vendor_plugins/timepicker/bootstrap-timepicker.min.js"></script>
     <script src="html/assets/vendor_plugins/iCheck/icheck.min.js"></script>
      
     <script src="html/template/horizontal/src/js/pages/advanced-form-element.js"></script>
       