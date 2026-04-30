
	
<div class="wrapper"> 
	 

     <?php include('header.php');?>
      
    
   
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
   
   $date_formatted = (new DateTime($date_event))->format('Y-m-d H:i');
   
   ?>

  <style>
  .mb-adminevent-page{ padding:6px 0 34px; }
  .mb-adminevent-hero{ padding:30px; border-radius:30px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 55%,#2563eb 100%); box-shadow:0 24px 50px rgba(15,23,42,.16); color:#fff; margin-bottom:24px; }
  .mb-adminevent-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
  .mb-adminevent-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
  .mb-adminevent-copy{ margin:0; max-width:760px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
  .mb-adminevent-summary{ display:flex; gap:12px; flex-wrap:wrap; margin-top:20px; }
  .mb-adminevent-pill{ display:inline-flex; align-items:center; gap:10px; padding:12px 16px; border-radius:18px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.14); font-weight:700; }
  .mb-adminevent-pill strong{ font-size:18px; font-weight:800; color:#fff; }
  @media only screen and (max-width: 769px){
    .mb-adminevent-page{ padding:0 0 26px; }
    .mb-adminevent-hero{ padding:22px 20px; border-radius:24px; }
    .mb-adminevent-title{ font-size:28px; }
  }
  </style>
   
   <p style="text-align:center;">
       <?php  // echo "La valeur de codevent est : " . $codevent; 
       echo $salut;?> <b>
       <?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, "UTF-8");?> </b>!
   </p>
   
   
   
     
   </div>
   
   
   
               <section class="content mb-adminevent-page">
               <div class="mb-adminevent-hero">
                 <span class="mb-adminevent-kicker"><i class="mdi mdi-monitor-dashboard"></i> Pilotage administrateur</span>
                 <h1 class="mb-adminevent-title">Supervisez l'événement avec une vue plus claire</h1>
                 <p class="mb-adminevent-copy">Retrouvez les indicateurs clés, les accès rapides et les réglages principaux depuis une interface mieux alignée avec le nouveau parcours client.</p>
                 <div class="mb-adminevent-summary">
                   <span class="mb-adminevent-pill"><i class="mdi mdi-account-group-outline"></i> Invités <strong><?php echo (int) $total_inv; ?></strong></span>
                   <span class="mb-adminevent-pill"><i class="mdi mdi-email-check-outline"></i> Réponses <strong><?php echo (int) $total_invconf; ?></strong></span>
                   <span class="mb-adminevent-pill"><i class="mdi mdi-check-circle-outline"></i> Présences <strong><?php echo (int) $total_invpre; ?></strong></span>
                 </div>
               </div>
   
   
   
   
               <div class="row">
                   <div class="col-12">
   
   
    
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   <?php 
   
   
     $total_inv = InviteStatsService::weightedCount($pdo, (int) $codevent);
   
   
   
   // ----------------tous les invités confirmés--------------------
  $total_invconf = ConfirmationService::countByPresence($pdo, (int) $codevent);
   
   
   //---------------tous les présents----------------
   
     $total_invpre = InviteStatsService::weightedCount($pdo, (int) $codevent, 'oui');
   
   
   //---------------tous les absent----------------
   
   $current_date = date('Y-m-d H:i:s'); // Obtenir la date actuelle
   
   if ($current_date >= $date_event) {
       $total_invabs = InviteStatsService::weightedCount($pdo, (int) $codevent, null, true);
   } else {
       $total_invabs = '0';
   }
   ?>
   
   
   
   
    
    
   
   
   
   
   
   
                       <div class="row">
                       <div class="col-xxxl-12 col-xxl-12 col-xl-12 col-lg-12">
                               <div class="box rounded-4">
                                   <div class="box-header d-flex b-0 justify-content-between align-items-center pb-0">
                                   <h4 class="box-title">Vue d'ensemble de l'événement</h4>
                                       <ul class="m-0" style="list-style: none;">
                                           <li class="dropdown">
                                       <button class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm" href="#"><i class="fa fa-fw fa-arrow-down"> </i> Exporter</button>
                                           </li>
                                       </ul>
                                   </div>
                                   <div class="box-body pt-0 summery-box">
                                   <p class="mb-20 text-fade">Indicateurs clés de l'événement en temps réel</p>
                                       <div class="row">
                                           <div class="col-lg-3 col-md-6">
                                               <div class="box pull-up mb-sm-0 bg-danger-light">
                                                     <div class="box-body ">
                                                       <div class="w-50 h-50 bg-danger rounded-circle text-center "> 
                                                           <i class="fa fa-area-chart fs-18 l-h-50"></i>
                                                       </div>
                                           <h2	 class="fw-600 mt-3"><?php echo (int) $total_inv; ?></h2	>
                                           <p class="text-fade fw-500 mb-2">Invités pondérés</p>
                                           <p class="mb-0 text-primary">Vue globale de la capacité</p>
                                                     </div>
                                               </div>
                                           </div>
                                           <div class="col-lg-3 col-md-6">
                                               <div class="box pull-up mb-sm-0 bg-warning-light">
                                                     <div class="box-body ">
                                                       <div class="w-50 h-50 bg-warning rounded-circle text-center"> 
                                                           <i class="fa fa-file fs-18 l-h-50"></i>
                                                       </div>
                                           <h2	 class="fw-600 mt-3"><?php echo (int) $total_invconf; ?></h2	>
                                           <p class="text-fade fw-500 mb-2">Réponses reçues</p>
                                           <p class="mb-0 text-primary">Confirmations déjà enregistrées</p>
                                                     </div>
                                               </div>
                                           </div>
                                           <div class="col-lg-3 col-md-6">
                                               <div class="box pull-up mb-0 bg-info-light">
                                                     <div class="box-body ">
                                                       <div class="w-50 h-50 bg-info rounded-circle text-center"> 
                                                           <i class="fa fa-tag fs-18 l-h-50"></i>
                                                       </div>
                                           <h2	 class="fw-600 mt-3"><?php echo (int) $total_invpre; ?></h2	>
                                           <p class="text-fade fw-500 mb-2">Présences confirmées</p>
                                           <p class="mb-0 text-primary">Invités qui ont répondu oui</p>
                                                     </div>
                                               </div>
                                           </div>
                                           <div class="col-lg-3 col-md-6">
                                               <div class="box pull-up mb-0 bg-danger-light">
                                                     <div class="box-body ">
                                                       <div class="w-50 h-50 bg-danger rounded-circle text-center"> 
                                                           <i class="fa fa-user fs-20 l-h-50"></i>
                                                       </div>
                                           <h2	 class="fw-600 mt-3"><?php echo (int) $total_invabs; ?></h2	>
                                           <p class="text-fade fw-500 mb-2">Absences constatées</p>
                                           <p class="mb-0 text-primary">Calculées selon la date de l'événement</p>
                                                     </div>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                               </div>
                           </div>
                        </div>
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
                       <div class="row">
                           <div class="col-xl-9">									
                               <div class="card">
                                   <div class="card-body">
                                       <div class="mb-30 mb-xl-0"> 
                                           
                       <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.2/main.min.css' rel='stylesheet' />
                       <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.2/main.min.js'></script>
   
                       <div id="calendar"></div>
   
   <script>
       document.addEventListener('DOMContentLoaded', function() {
           var calendarEl = document.getElementById('calendar');
   
           var calendar = new FullCalendar.Calendar(calendarEl, {
               locale: 'fr',
               initialView: 'dayGridMonth',
               headerToolbar: {
                   left: 'prev,next today',
                   center: 'title',
                   right: 'dayGridMonth,timeGridWeek,timeGridDay'
               },
               buttonText: {
                   today: 'Aujourd\'hui',
                   month: 'Mois',
                   week: 'Semaine',
                   day: 'Jour'
               },
               events: [
                   {
                       title: '<?php echo htmlspecialchars($data_evenement); ?>',
                       start: '<?php echo htmlspecialchars($date_event); ?>',
                       color: '#ffcc00'
                   }
               ]
           });
   
           calendar.render();
       });
   </script>
   
   
                                       </div>
                                   </div>
                               </div>
                           </div>							
                           <div class="col-xl-3">
                               <div class="d-grid">
                                   <a href="index.php?page=addevent" class="btn btn-danger align-items-center" style="height:60px;padding-top:16px;">Ajouter un événement</a>
                               </div>
   
   
   <div style="display:<?php echo $display;?>">
   
   
   
   <?php 
   // Assure-toi que l'extension Intl est activée
   $date_event = $date_event; // Format : 'Y-m-d H:i:s'
   $date = new DateTime($date_event);
   
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
   
   //-------------------------------------
   
   
   if ($type_event == "1") {
       $typeevent = 'Mariage ' . ($dataevent['type_mar'] ?? 'Inconnu');
       $displayvue = 'display:block;';
       $fetard = (($dataevent['prenom_epouse'] ?? '') . ' & ' . ($dataevent['prenom_epoux'] ?? '')) ?: 'Inconnu';
   } elseif ($type_event == "2" || $type_event == "3") {
       $typeevent = $data_evenement;
       $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
       $displayvue = 'display:none;';
   }

       $sitePreviewUrl = EventUrlService::publicUrl(
         is_array($dataevent ?? null) ? $dataevent : ['cod_event' => $codevent, 'type_event' => $type_event],
         $isAppConfig
       );
   ?> 
   
   
   
   
                               <div class="col-xl-12" style="margin-top:25px;">
                                   <div class="box">
                                       <div class="box-body text-center" style="background-color:#ebf3f7;">
                                           <div>
   
                                           <p class="text-primary m-0"><?php echo $formatted_date; ?></p>
                                               <br>
                                           <?php include('comptearebour.php');?>
   
                                           <p class="text-primary m-0"><?php echo $typeevent?></p>
                                           <h3 class="mb-0"><?php echo $fetard; ?></h3>
                                           <p class="mb-1"><?php echo $dataevent['lieu']; ?></p>
   
    
                                              <a href="index.php?page=modevent&cod=<?php echo $codevent; ?>" class="btn btn-primary" type="button">Modifier l'événement</a>
                                            
            
                                           </div>
                                       </div>
                                       </div>
                                   </div>
   
   
   
   
   
                               <div id="external-events" class="mt-20">
                                
                                
   
   <?php
   
       if (!$dataevent) {
           echo '<p class="text-muted">Aucun événement trouvé</p>';
       } else {
           ?>
   
   
   
           <?php
       }
   ?>
   
   
                           <!-- 
                             <a class="box box-link-shadow text-center" href="javascript:void(0)">
                               <div class="box-body">
                                   <div class="fs-24">175</div>
                                   <span>Responded</span>
                               </div>
                               <div class="box-body bg-warning btsr-0 bter-0">
                                   <p>
                                       <span class="mdi mdi-message-reply-text fs-30"></span>
                                   </p>
                               </div>
                             </a> -->
   
   
                                   <div class="col-xl-12">
                                   <div class="box">
                                       <div class="box-body text-center">
                                           <div>
                                               <h5>Site web</h5>
                                              <p class="text-fade">Accès rapide à la vitrine publique et à ses réglages</p>
                                               <div class="btn-group">
                                              <button class="btn btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown">Site web de l'événement</button>
                                               <div class="dropdown-menu">
                                                <a class="dropdown-item" href="index.php?page=conf_siteweb">Personnaliser</a>
                                                <a class="dropdown-item" href="<?php echo htmlspecialchars($sitePreviewUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blink">Prévisualiser</a>
                                               </div>
           
                                               </div>
                                           </div>
                                       </div>
                                       </div>
                                   </div>
   
   
                                   <div class="external-event bg-success" data-class="bg-success">
                                       <a href="index.php?page=addinvite&codevent=<?php echo $codevent?>" style="color:white;"><i class="mdi mdi-plus me-2 vertical-middle"></i>Ajouter les invités</a>
                                   </div>
                                   <div class="external-event bg-primary" data-class="bg-primary">
                                       <a href="index.php?page=addtable&codevent=<?php echo $codevent?>" style="color:white;">
                                       <i class="mdi mdi-plus me-2 vertical-middle"></i>Ajouter les tables</a>
                                   </div> 
   
                                   <div class="external-event bg-warning" data-class="bg-warning">
                                   <a href="index.php?page=addmenu&codevent=<?php echo $codevent?>" style="color:white;">
                                   <i class="mdi mdi-plus me-2 vertical-middle"></i>Ajouter le menu</a>
                                   </div>
                                   
                                   <div class="external-event bg-danger" style="<?php echo $displayvue;?>" data-class="bg-danger">
                                       <i class="mdi mdi-plus me-2 vertical-middle"></i>Élaborer la liste de cadeaux
                                   </div> 
   
                                   <!-- 
                                   <div class="external-event bg-info" data-class="bg-info">
                                   <a href="#" style="color:white;"><i class="fas fa-bell m-2 vertical-middle"></i>Notifier les invités</a>
                                   </div> 
                                   -->
   
                               </div>
                       
   </div>			
   
   
   
   
                           </div>
                       </div>					
                       
                       <div class="modal fade" id="event-modal" tabindex="-1">
                           <div class="modal-dialog">
                               <div class="modal-content">
                                   <form class="needs-validation" name="event-form" id="form-event" novalidate>
                                       <div class="modal-header py-3 px-4 border-bottom-0">
                                           <h5 class="modal-title" id="modal-title">Event</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                       </div>
                                       <div class="modal-body px-4 pb-4 pt-0">
                                           <div class="row">
                                               <div class="col-12">
                                                   <div class="mb-3">
                                                       <label class="control-label form-label">Event Name</label>
                                                       <input class="form-control" placeholder="Insert Event Name" type="text" name="title" id="event-title" required />
                                                       <div class="invalid-feedback">Please provide a valid event name</div>
                                                   </div>
                                               </div>
                                               <div class="col-12">
                                                   <div class="mb-3">
                                                       <label class="control-label form-label">Category</label>
                                                       <select class="form-select" name="category" id="event-category" required>
                                                           <option value="bg-danger" selected>Danger</option>
                                                           <option value="bg-success">Success</option>
                                                           <option value="bg-primary">Primary</option>
                                                           <option value="bg-info">Info</option>
                                                           <option value="bg-dark">Dark</option>
                                                           <option value="bg-warning">Warning</option>
                                                       </select>
                                                       <div class="invalid-feedback">Please select a valid event category</div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="row">
                                               <div class="col-6">
                                                   <button type="button" class="btn btn-danger" id="btn-delete-event">Delete</button>
                                               </div>
                                               <div class="col-6 text-end">
                                                   <button type="button" class="btn btn-danger-light me-1" data-bs-dismiss="modal">Close</button>
                                                   <button type="submit" class="btn btn-success" id="btn-save-event">Save</button>
                                               </div>
                                           </div>
                                       </div>
                                   </form>
                               </div>
                           </div> 
                       </div>					
                   </div>				
               </div>  
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   <?php 
   
   
   
   if (!$dataevent) {
       $linkallinv = "#";
   }else{
       $linkallinv = "../pages/liste_invites.php?event=".$codevent;
   } 
   
   ?>
   
   <div class="row" id='mesinv'>
       <div class="col-xxl-12 col-xl-12 col-lg-12">
           <div class="card rounded-4">
               <div class="box-header d-flex b-0 justify-content-between align-items-center">
                   <h4 class="box-title">Mes invités</h4>
                   <ul class="m-0" style="list-style: none;">
                       <li class="dropdown">
                           <a target="_blank" href="<?php echo $linkallinv; ?>" class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm">
                               <i class="fa fa-fw fa-arrow-down"></i> Obtenir le PDF
                           </a>
                       </li>
                   </ul>
               </div>
   
               <div class="card-body pt-0">
                   <div class="table-responsive">
                       <table class="table mb-0">
                           <tbody>
                               <?php 
                               $stmt = $pdo->prepare("SELECT * FROM invite WHERE cod_mar = :codevent ORDER BY id_inv DESC");
                               $stmt->execute([':codevent' => $codevent]);
   
                               if ($stmt->rowCount() > 0) {
                                   while ($row_inv = $stmt->fetch(PDO::FETCH_ASSOC)) { 
                                       $linkpdf = $dataevent['invit_religieux'] ? "../pages/invitation_elect.php?cod=".$row_inv['id_inv']."&event=".$codevent : "#";
   
                                     $seatName = EventTableService::findNameById($pdo, isset($row_inv['siege']) ? (int) $row_inv['siege'] : null);
                                     $siege = $seatName ? ucfirst($seatName) : '<em>Non défini</em>';
                                       $sing = $row_inv['sing'] === 'C' ? 'Couple' : ($row_inv['sing'] ? 'Singleton' : '<em>Non défini</em>');
                               ?>
                                       <tr>
                                           <td class="pt-0 px-0 b-0">
                                               <a class="d-block fw-500 fs-14" href="index.php?page=modinv&idinv=<?php echo $row_inv['id_inv'];?>"><?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?></a>
                                               <span class="text-fade"><?php echo $siege; ?> - <?php echo $sing; ?></span>
                                           </td> 
   
                                           <td class="text-end b-0 pt-0 px-0" width="15%"> 
                                               <a class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm" href="#" onclick="openModal('<?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?>', '<?php echo $row_inv['id_inv']; ?>')" style="color:#aaa;">
                                                   <i class="fas fa-share"></i>
                                               </a>  
                                           </td>
                                       </tr>
   
                               <?php 
   
                                   }
   
                               } else {
                                   echo '<tr><td colspan="3" class="text-left" style="font-style:italic;">Aucun invité trouvé</td></tr>';
                               }
   
                               ?>
   
                           </tbody>
                       </table>
                   </div>
               </div>	
           </div>
       </div>
   
       <!-- Fenêtre modale -->
       <div id="shareModal" class="modalinv" style="display: none;">
           <div class="modal-content">
               <form action="" method="post">
   <?php 
   require_once '../../twilio-php-main/src/Twilio/autoload.php'; 
   use Twilio\Rest\Client;
   
     require_once __DIR__ . '/whatsapp_template_sender.php';
  $sharePreviewContext = isapp_whatsapp_sender_preview_context($pdo, $codevent);

     if (isset($_POST['submitwhat'])) {
       $shareErrorMessage = null;
       $shareSuccessMessage = null;

       try {
         $result = isapp_whatsapp_send_template_invitation($pdo, [
           'event_code' => $codevent,
           'invite_id' => $_POST['inviteId'] ?? null,
           'phone' => $_POST['phoneinv'] ?? '',
           'invite_name' => $_POST['inviteName'] ?? 'Invite',
           'pdf_link' => $_POST['pdf_link'] ?? '',
           'success_redirect' => 'index.php?page=mb_accueil',
         ]);
         $shareSuccessMessage = $result['success_message'];
       } catch (\Throwable $exception) {
         $shareErrorMessage = (string) $exception->getMessage();
         if ($shareErrorMessage === '') {
           $shareErrorMessage = 'L’envoi de l’invitation WhatsApp a echoue.';
         }
       }

       if ($shareSuccessMessage !== null) {
         echo '<script>
         Swal.fire({
           title: "Notification !",
           text: ' . json_encode($shareSuccessMessage) . ',
           icon: "success",
           confirmButtonText: "OK"
         }).then((result) => {
           if (result.isConfirmed) {
             window.location.href = "index.php?page=mb_accueil";
           }
         });
         </script>';
       }

       if ($shareErrorMessage !== null) {
         echo '<script>
         Swal.fire({
           title: "Échec de l’envoi",
           text: ' . json_encode($shareErrorMessage) . ',
           icon: "error",
           confirmButtonText: "OK"
         });
         </script>';
       }
     }
   ?>
               <div class="form-group"> 
                   <span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
                   <h4 id="modalTitle">Envoyer l'invitation</h4> <br><br>
                   <input type="text" required pattern="^\+\d{1,3}\d{9,}$" 
                   title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
                   <input type="hidden" id="inviteName" name="inviteName" />
                     <input type="hidden" id="inviteId" name="inviteId" />
                     <input type="hidden" id="pdfLink" name="pdf_link" />
                     <button class="btn btn-primary" type="submit" name="submitwhat" style="border-radius:0px 0px 7px 7px;width:100%;">Envoyer l'invitation</button>
               </div>
         <p style="margin:12px 0 0;color:#475569;font-size:13px;">En validant cette action, l'invitation PDF sera envoyee sur WhatsApp au numero indique pour cet invite.</p>
         <div style="margin-top:12px;padding:12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-size:13px;line-height:1.6;">
           <strong style="display:block;margin-bottom:6px;color:#0f172a;">Exemple de message automatique</strong>
           Bonjour <span id="previewInviteName">votre invite</span>,<br>
           Nous avons le plaisir de vous transmettre votre invitation a <?php echo htmlspecialchars($sharePreviewContext['event_label'], ENT_QUOTES, 'UTF-8'); ?>.<br><br>
           Nous vous remercions de bien vouloir confirmer votre presence.<br><br>
           Cordialement,<br>
           <?php echo htmlspecialchars($sharePreviewContext['signature'], ENT_QUOTES, 'UTF-8'); ?>.<br>
           Merci.
         </div>
               <br>
               <a href="#" id="downloadLink">Télécharger le PDF</a>
               </form>
           </div>
       </div>
   
       <style>
           .modalinv {
               position: fixed;
               top: 0;
               left: 0;
               width: 100%;
               height: 100%;
               background-color: rgba(0, 0, 0, 0.5);
               display: flex;
               justify-content: center;
               align-items: center;
               z-index: 3000;
           }
   
           .modal-content {
               background-color: white;
               padding: 20px;
               border-radius: 5px;
               box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
               position: relative;
           }
   
           .close {
               position: absolute;
               top: 10px;
               right: 15px;
               color: #aaa;
               font-size: 24px;
           }
   
           .close:hover {
               color: #000;
           }
       </style>
   
       <script>
           function openModal(inviteName, inviteId) {
               document.getElementById('modalTitle').innerText = "Envoyer l'invitation a " + inviteName;
               document.getElementById('previewInviteName').innerText = inviteName;
               document.getElementById('shareModal').style.display = 'flex';
               const linkpdf = "../pages/invitation_elect.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
               document.getElementById('downloadLink').setAttribute('href', linkpdf);
               document.getElementById('downloadLink').setAttribute('target', "_blank");
               document.getElementById('inviteName').value = inviteName;
               document.getElementById('inviteId').value = inviteId;
               document.getElementById('pdfLink').value = linkpdf;
           }
   
           function closeModal() {
               document.getElementById('shareModal').style.display = 'none';
           }
       </script>
   </div>
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
    
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
     
                       </div>
   
                       
                   </div> 
               </section>
               <!-- /.content -->
           </div>
     <!-- /.content-wrapper -->
       <?php include('footer.php')?>
     <!-- Side panel --> 
     <!-- quick_user_toggle -->
     <div class="modal modal-right fade" id="quick_user_toggle" tabindex="-1">
         <div class="modal-dialog">
           <div class="modal-content slim-scroll3">
             <div class="modal-body p-30 bg-white">
               <div class="d-flex align-items-center justify-content-between pb-30">
                   <h4 class="m-0">User Profile
                   <small class="text-fade fs-12 ms-5">12 messages</small></h4>
                   <a href="#" class="btn btn-icon btn-danger-light btn-sm no-shadow" data-bs-dismiss="modal">
                       <span class="fa fa-close"></span>
                   </a>
               </div>
               <div>
                   <div class="d-flex flex-row">
                       <div class=""><img src="html/images/avatar/avatar-2.png" alt="user" class="rounded bg-danger-light w-150" width="100"></div>
                       <div class="ps-20">
                           <h5 class="mb-0">Nil Yeager</h5>
                           <p class="my-5 text-fade">Web Designer</p>
                           <a href="mailto:dummy@gmail.com"><span class="icon-Mail-notification me-5 text-success"><span class="path1"></span><span class="path2"></span></span> dummy@gmail.com</a>
                           <button class="btn btn-success-light btn-sm mt-5"><i class="ti-plus"></i> Follow</button>
                       </div>
                   </div>
               </div>
                 <div class="dropdown-divider my-30"></div>
                 <div>
                   <div class="d-flex align-items-center mb-30">
                       <div class="me-15 bg-primary-light h-50 w-50 l-h-60 rounded text-center">
                             <span class="icon-Library fs-24"><span class="path1"></span><span class="path2"></span></span>
                       </div>
                       <div class="d-flex flex-column fw-500">
                           <a href="extra_profile.html" class="text-dark hover-primary mb-1 fs-16">My Profile</a>
                           <span class="text-fade">Account settings and more</span>
                       </div>
                   </div>
                   <div class="d-flex align-items-center mb-30">
                       <div class="me-15 bg-danger-light h-50 w-50 l-h-60 rounded text-center">
                           <span class="icon-Write fs-24"><span class="path1"></span><span class="path2"></span></span>
                       </div>
                       <div class="d-flex flex-column fw-500">
                           <a href="mailbox.html" class="text-dark hover-danger mb-1 fs-16">My Messages</a>
                           <span class="text-fade">Inbox and tasks</span>
                       </div>
                   </div>
                   <div class="d-flex align-items-center mb-30">
                       <div class="me-15 bg-success-light h-50 w-50 l-h-60 rounded text-center">
                           <span class="icon-Group-chat fs-24"><span class="path1"></span><span class="path2"></span></span>
                       </div>
                       <div class="d-flex flex-column fw-500">
                           <a href="setting.html" class="text-dark hover-success mb-1 fs-16">Settings</a>
                           <span class="text-fade">Accout Settings</span>
                       </div>
                   </div>
                   <div class="d-flex align-items-center mb-30">
                       <div class="me-15 bg-info-light h-50 w-50 l-h-60 rounded text-center">
                           <span class="icon-Attachment1 fs-24"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                       </div>
                       <div class="d-flex flex-column fw-500">
                           <a href="extra_taskboard.html" class="text-dark hover-info mb-1 fs-16">Project</a>
                           <span class="text-fade">latest tasks and projects</span>
                       </div>
                   </div>
                 </div>
                 <div class="dropdown-divider my-30"></div>
                 <div>
                   <div class="media-list">
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">10:10</h4>
                         <div class="media-body ps-15 bs-5 rounded border-primary">
                           <p>Morbi quis ex eu arcu auctor sagittis.</p>
                           <span class="text-fade">by Johne</span>
                         </div>
                       </a>
   
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">08:40</h4>
                         <div class="media-body ps-15 bs-5 rounded border-success">
                           <p>Proin iaculis eros non odio ornare efficitur.</p>
                           <span class="text-fade">by Amla</span>
                         </div>
                       </a>
   
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">07:10</h4>
                         <div class="media-body ps-15 bs-5 rounded border-info">
                           <p>In mattis mi ut posuere consectetur.</p>
                           <span class="text-fade">by Josef</span>
                         </div>
                       </a>
   
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">01:15</h4>
                         <div class="media-body ps-15 bs-5 rounded border-danger">
                           <p>Morbi quis ex eu arcu auctor sagittis.</p>
                           <span class="text-fade">by Rima</span>
                         </div>
                       </a>
   
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">23:12</h4>
                         <div class="media-body ps-15 bs-5 rounded border-warning">
                           <p>Morbi quis ex eu arcu auctor sagittis.</p>
                           <span class="text-fade">by Alaxa</span>
                         </div>
                       </a>
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">10:10</h4>
                         <div class="media-body ps-15 bs-5 rounded border-primary">
                           <p>Morbi quis ex eu arcu auctor sagittis.</p>
                           <span class="text-fade">by Johne</span>
                         </div>
                       </a>
   
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">08:40</h4>
                         <div class="media-body ps-15 bs-5 rounded border-success">
                           <p>Proin iaculis eros non odio ornare efficitur.</p>
                           <span class="text-fade">by Amla</span>
                         </div>
                       </a>
   
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">07:10</h4>
                         <div class="media-body ps-15 bs-5 rounded border-info">
                           <p>In mattis mi ut posuere consectetur.</p>
                           <span class="text-fade">by Josef</span>
                         </div>
                       </a>
   
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">01:15</h4>
                         <div class="media-body ps-15 bs-5 rounded border-danger">
                           <p>Morbi quis ex eu arcu auctor sagittis.</p>
                           <span class="text-fade">by Rima</span>
                         </div>
                       </a>
   
                       <a class="media media-single px-0" href="#">
                         <h4 class="w-50 text-gray fw-500">23:12</h4>
                         <div class="media-body ps-15 bs-5 rounded border-warning">
                           <p>Morbi quis ex eu arcu auctor sagittis.</p>
                           <span class="text-fade">by Alaxa</span>
                         </div>
                       </a>
                     </div>
               </div>
             </div>
           </div>
         </div>
     </div>
     <!-- /quick_user_toggle --> 
       
   
     <!-- Control Sidebar -->
     <aside class="control-sidebar">
         
       <div class="rpanel-title"><span class="pull-right btn btn-circle btn-danger" data-toggle="control-sidebar"><i class="ion ion-close text-white" ></i></span> </div>  <!-- Create the tabs -->
       <ul class="nav nav-tabs control-sidebar-tabs">
         <li class="nav-item"><a href="#control-sidebar-home-tab" data-bs-toggle="tab" ><i class="mdi mdi-message-text"></i></a></li>
         <li class="nav-item"><a href="#control-sidebar-settings-tab" data-bs-toggle="tab"><i class="mdi mdi-playlist-check"></i></a></li>
       </ul>
       <!-- Tab panes -->
       <div class="tab-content">
         <!-- Home tab content -->
         <div class="tab-pane" id="control-sidebar-home-tab">
             <div class="flexbox">
               <a href="javascript:void(0)" class="text-grey">
                   <i class="ti-more"></i>
               </a>	
               <p>Users</p>
               <a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
             </div>
             <div class="lookup lookup-sm lookup-right d-none d-lg-block">
               <input type="text" name="s" placeholder="Search" class="w-p100">
             </div>
             <div class="media-list media-list-hover mt-20">
               <div class="media py-10 px-0">
                 <a class="avatar avatar-lg status-success" href="#">
                   <img src="html/images/avatar/1.jpg" alt="...">
                 </a>
                 <div class="media-body">
                   <p class="fs-16">
                     <a class="hover-primary" href="#"><strong>Tyler</strong></a>
                   </p>
                   <p>Praesent tristique diam...</p>
                     <span>Just now</span>
                 </div>
               </div>
   
               <div class="media py-10 px-0">
                 <a class="avatar avatar-lg status-danger" href="#">
                   <img src="html/images/avatar/2.jpg" alt="...">
                 </a>
                 <div class="media-body">
                   <p class="fs-16">
                     <a class="hover-primary" href="#"><strong>Luke</strong></a>
                   </p>
                   <p>Cras tempor diam ...</p>
                     <span>33 min ago</span>
                 </div>
               </div>
   
               <div class="media py-10 px-0">
                 <a class="avatar avatar-lg status-warning" href="#">
                   <img src="html/images/avatar/3.jpg" alt="...">
                 </a>
                 <div class="media-body">
                   <p class="fs-16">
                     <a class="hover-primary" href="#"><strong>Evan</strong></a>
                   </p>
                   <p>In posuere tortor vel...</p>
                     <span>42 min ago</span>
                 </div>
               </div>
   
               <div class="media py-10 px-0">
                 <a class="avatar avatar-lg status-primary" href="#">
                   <img src="html/images/avatar/4.jpg" alt="...">
                 </a>
                 <div class="media-body">
                   <p class="fs-16">
                     <a class="hover-primary" href="#"><strong>Evan</strong></a>
                   </p>
                   <p>In posuere tortor vel...</p>
                     <span>42 min ago</span>
                 </div>
               </div>			
               
               <div class="media py-10 px-0">
                 <a class="avatar avatar-lg status-success" href="#">
                   <img src="html/images/avatar/1.jpg" alt="...">
                 </a>
                 <div class="media-body">
                   <p class="fs-16">
                     <a class="hover-primary" href="#"><strong>Tyler</strong></a>
                   </p>
                   <p>Praesent tristique diam...</p>
                     <span>Just now</span>
                 </div>
               </div>
   
               <div class="media py-10 px-0">
                 <a class="avatar avatar-lg status-danger" href="#">
                   <img src="html/images/avatar/2.jpg" alt="...">
                 </a>
                 <div class="media-body">
                   <p class="fs-16">
                     <a class="hover-primary" href="#"><strong>Luke</strong></a>
                   </p>
                   <p>Cras tempor diam ...</p>
                     <span>33 min ago</span>
                 </div>
               </div>
   
               <div class="media py-10 px-0">
                 <a class="avatar avatar-lg status-warning" href="#">
                   <img src="html/images/avatar/3.jpg" alt="...">
                 </a>
                 <div class="media-body">
                   <p class="fs-16">
                     <a class="hover-primary" href="#"><strong>Evan</strong></a>
                   </p>
                   <p>In posuere tortor vel...</p>
                     <span>42 min ago</span>
                 </div>
               </div>
   
               <div class="media py-10 px-0">
                 <a class="avatar avatar-lg status-primary" href="#">
                   <img src="html/images/avatar/4.jpg" alt="...">
                 </a>
                 <div class="media-body">
                   <p class="fs-16">
                     <a class="hover-primary" href="#"><strong>Evan</strong></a>
                   </p>
                   <p>In posuere tortor vel...</p>
                     <span>42 min ago</span>
                 </div>
               </div>
                 
             </div>
   
         </div>
         <!-- /.tab-pane -->
         <!-- Settings tab content -->
         <div class="tab-pane" id="control-sidebar-settings-tab">
             <div class="flexbox">
               <a href="javascript:void(0)" class="text-grey">
                   <i class="ti-more"></i>
               </a>	
               <p>Todo List</p>
               <a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
             </div>
           <ul class="todo-list mt-20">
               <li class="py-15 px-5 by-1">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_1" class="filled-in">
                 <label for="basic_checkbox_1" class="mb-0 h-15"></label>
                 <!-- todo text -->
                 <span class="text-line">Nulla vitae purus</span>
                 <!-- Emphasis label -->
                 <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
                 <!-- General tools such as edit or delete-->
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_2" class="filled-in">
                 <label for="basic_checkbox_2" class="mb-0 h-15"></label>
                 <span class="text-line">Phasellus interdum</span>
                 <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5 by-1">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_3" class="filled-in">
                 <label for="basic_checkbox_3" class="mb-0 h-15"></label>
                 <span class="text-line">Quisque sodales</span>
                 <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_4" class="filled-in">
                 <label for="basic_checkbox_4" class="mb-0 h-15"></label>
                 <span class="text-line">Proin nec mi porta</span>
                 <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5 by-1">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_5" class="filled-in">
                 <label for="basic_checkbox_5" class="mb-0 h-15"></label>
                 <span class="text-line">Maecenas scelerisque</span>
                 <small class="badge bg-primary"><i class="fa fa-clock-o"></i> 1 week</small>
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_6" class="filled-in">
                 <label for="basic_checkbox_6" class="mb-0 h-15"></label>
                 <span class="text-line">Vivamus nec orci</span>
                 <small class="badge bg-info"><i class="fa fa-clock-o"></i> 1 month</small>
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5 by-1">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_7" class="filled-in">
                 <label for="basic_checkbox_7" class="mb-0 h-15"></label>
                 <!-- todo text -->
                 <span class="text-line">Nulla vitae purus</span>
                 <!-- Emphasis label -->
                 <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
                 <!-- General tools such as edit or delete-->
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_8" class="filled-in">
                 <label for="basic_checkbox_8" class="mb-0 h-15"></label>
                 <span class="text-line">Phasellus interdum</span>
                 <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5 by-1">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_9" class="filled-in">
                 <label for="basic_checkbox_9" class="mb-0 h-15"></label>
                 <span class="text-line">Quisque sodales</span>
                 <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
               <li class="py-15 px-5">
                 <!-- checkbox -->
                 <input type="checkbox" id="basic_checkbox_10" class="filled-in">
                 <label for="basic_checkbox_10" class="mb-0 h-15"></label>
                 <span class="text-line">Proin nec mi porta</span>
                 <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
                 <div class="tools">
                   <i class="fa fa-edit"></i>
                   <i class="fa fa-trash-o"></i>
                 </div>
               </li>
             </ul>
         </div>
         <!-- /.tab-pane -->
       </div>
     </aside>
     <!-- /.control-sidebar -->
     
     <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
     <div class="control-sidebar-bg"></div>     
     
   
     
     
   </div>
   <!-- ./wrapper -->
       
       
           
       <div id="chat-box-body">
           <div id="chat-circle" class="waves-effect waves-circle btn btn-circle btn-sm btn-warning l-h-50">
               <div id="chat-overlay"></div>
               <span class="icon-Group-chat fs-18"><span class="path1"></span><span class="path2"></span></span>
           </div>
   
           <div class="chat-box">
               <div class="chat-box-header p-15 d-flex justify-content-between align-items-center">
                   <div class="btn-group">
                     <button class="waves-effect waves-circle btn btn-circle btn-primary-light h-40 w-40 rounded-circle l-h-45" type="button" data-bs-toggle="dropdown">
                         <span class="icon-Add-user fs-22"><span class="path1"></span><span class="path2"></span></span>
                     </button>
                     <div class="dropdown-menu min-w-200">
                       <a class="dropdown-item fs-16" href="#">
                           <span class="icon-Color me-15"></span>
                           New Group</a>
                       <a class="dropdown-item fs-16" href="#">
                           <span class="icon-Clipboard me-15"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                           Contacts</a>
                       <a class="dropdown-item fs-16" href="#">
                           <span class="icon-Group me-15"><span class="path1"></span><span class="path2"></span></span>
                           Groups</a>
                       <a class="dropdown-item fs-16" href="#">
                           <span class="icon-Active-call me-15"><span class="path1"></span><span class="path2"></span></span>
                           Calls</a>
                       <a class="dropdown-item fs-16" href="#">
                           <span class="icon-Settings1 me-15"><span class="path1"></span><span class="path2"></span></span>
                           Settings</a>
                       <div class="dropdown-divider"></div>
                       <a class="dropdown-item fs-16" href="#">
                           <span class="icon-Question-circle me-15"><span class="path1"></span><span class="path2"></span></span>
                           Help</a>
                       <a class="dropdown-item fs-16" href="#">
                           <span class="icon-Notifications me-15"><span class="path1"></span><span class="path2"></span></span> 
                           Privacy</a>
                     </div>
                   </div>
                   <div class="text-center flex-grow-1">
                       <div class="text-dark fs-18">Support</div>
                       <div>
                           <span class="badge badge-sm badge-dot badge-primary"></span>
                           <span class="text-muted fs-12">Active</span>
                       </div>
                   </div>
                   <div class="chat-box-toggle">
                       <button id="chat-box-toggle" class="waves-effect waves-circle btn btn-circle btn-danger-light h-40 w-40 rounded-circle l-h-45" type="button">
                         <span class="icon-Close fs-22"><span class="path1"></span><span class="path2"></span></span>
                       </button>                    
                   </div>
               </div>
               <div class="chat-box-body">
                   
                   <?php // include ('chatsupport.php')?>
   
               </div>
               <div class="chat-input">      
                   <form>
                       <input type="text" id="chat-input" placeholder="Besoin d'aide ?"/>
                       <button type="submit" class="chat-submit" id="chat-submit">
                           <span class="icon-Send fs-22"></span>
                       </button>
                   </form>      
               </div>
           </div>
       </div>
       
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
         