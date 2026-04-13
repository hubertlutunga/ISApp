
	
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

<p style="text-align:center;">
	<?php  // echo "La valeur de codevent est : " . $codevent; 
	echo $salut;?> <b>
	<?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, "UTF-8");?> </b>!
</p>



  
</div>


 









<?php 


		   $guestStats = EventWorkspaceService::getGuestStats($pdo, (string) $codevent, $date_event ?: null);
		   $confirmationStats = ConfirmationService::countSummary($pdo, (int) $codevent);
		   $row_citi = (int) ($guestStats['total_inv'] ?? 0);
$total_invconf = (int) ($confirmationStats['total'] ?? 0);
$total_invconfoui = (int) ($confirmationStats['oui'] ?? 0);
$total_invconfnon = (int) ($confirmationStats['non'] ?? 0);
$total_invconfpt = (int) ($confirmationStats['plustard'] ?? 0);

$total_nonreagi = $row_citi - $total_invconf;


?>






			<section class="content">
				<div class="box box-body">
					<div class="row"> 

						<?php include('statereponse.php')?>

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
				   <h4 class="box-title"><span class="text-primary"><?php echo $total_nonreagi; ?> n'ont pas encore réagi</span></h4>
				 
			   </div>
   
			   <div class="card-body pt-0">
				   <div class="table">
					   <table class="table mb-0">
						   <tbody id="inviteList">
 
								 <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un nom..." style="height:40px;font-size:16px;border:1px solid #ccc;margin-bottom:10px;">
   
								</td> 
						   

							   <?php 

							   //$stmt = $pdo->prepare("SELECT * FROM invite WHERE cod_mar = :codevent ORDER BY nom ASC");
							   //$stmt->execute([':codevent' => $codevent]);

                               $stmt = $pdo->prepare("
                                        SELECT * FROM invite 
                                        WHERE cod_mar = :codevent 
                                        AND nom NOT IN (
                                            SELECT noms FROM confirmation
                                            WHERE cod_mar = :codevent
                                        )
                                        ORDER BY nom ASC
                                    ");
                                $stmt->execute([':codevent' => $codevent]);
   
							   if ($stmt->rowCount() > 0) {
								   while ($row_inv = $stmt->fetch(PDO::FETCH_ASSOC)) { 
									   $linkpdf = $dataevent['invit_religieux'] ? "../pages/invitation_elect.php?cod=".$row_inv['id_inv']."&event=".$codevent : "#";
   
									   $seatName = EventTableService::findNameById($pdo, isset($row_inv['siege']) ? (int) $row_inv['siege'] : null);
									   $siege = $seatName ? ucfirst($seatName) : '<em>Non défini</em>';
									   $sing = $row_inv['sing'] === 'C' ? 'Couple' : ($row_inv['sing'] ? 'Singleton' : '<em>Non défini</em>');
							   ?>
							   
									    <tr class="invite-item">
											<td class="pt-0 px-0 b-0">
												<a style="padding-top:10px;" class="d-block fw-500 fs-14 invite-name" href="index.php?page=modinv&idinv=<?php echo $row_inv['id_inv'];?>"><?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?></a>
 
											   <span class="text-fade"><?php echo $siege; ?> - <?php echo $sing; ?></span>
										   </td> 
   
										   <td class="text-end b-0 pt-0 px-0" width="15%"> 
											  



 
 
 
 
 
  
 
 
  
 
 
 
											   <div class="list-icons d-inline-flex">
                          <div class="list-icons-item dropdown">
                                          
  
                           <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                                     <div class="dropdown-menu dropdown-menu-end">
  
 
                       
					   <a class="dropdown-item" href="#" onclick="openModal('<?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?>', '<?php echo $row_inv['id_inv']; ?>')" style="color:#aaa;">
					   <i class="fa fa-share"></i> Notifier l'invité</a> 

											   <a class="dropdown-item" href="../pages/invitation_elect.php?cod=<?php echo $row_inv['id_inv'];?>&event=<?php echo $codevent; ?>"><i class="fa fa-download"></i> Partager l'invitation</a>
											   
											   <a class="dropdown-item" href="index.php?page=modinv&idinv=<?php echo $row_inv['id_inv'];?>"><i class="fa fa-edit"></i> Modifier l'invité</a>
            
											   <!-- <a class="dropdown-item" href="#" style="color:red;" title="Suppression" onclick="confirmSuppInv(event)"><i class="fa fa-remove"></i> Supprimer l'invité</a> -->


<script>
    function confirmSuppInv(event) {
        event.preventDefault(); // Empêche le lien de se déclencher
        Swal.fire({
            title: "Supprimer !",
            text: "Êtes-vous sûr de vouloir supprimer <?php echo $row_inv['nom'];?> ?",
            icon: "warning", // Utilisez "warning" pour une alerte de confirmation
            showCancelButton: true,
            confirmButtonText: "Oui",
            cancelButtonText: "Non"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "index.php?page=supinv&idinv=<?php echo $row_inv['id_inv'];?>";
            }
        });
    }
</script> 
      
       
                        </div>
                         </div>
                       </div>



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
   
   if (isset($_POST['submitwhat'])) {
	   // Préparation de la requête pour récupérer les événements
	   $inviteName = $_POST['inviteName'];
	   $stmtevz = $pdo->prepare("SELECT * FROM events WHERE cod_event = :cod_event");
	   $stmtevz->execute(['cod_event' => $codevent]);
	   $dataeventv = $stmtevz->fetch();
   
	   if (!$dataeventv) {
		   $codevent = '';
		   $date_event = '';
		   $type_event = '';
		   $display = 'none';
	   } else {  
		   $codevent = $dataeventv['cod_event'];
		   $date_event = $dataeventv['date_event'];
		   $type_event = $dataeventv['type_event'];
		   $display = 'block';
	   }
   
	   // Détermination du type d'événement
	   if ($type_event == "1") {
		   $fetard = (($dataeventv['prenom_epouse'] ?? '') . ' & ' . ($dataeventv['prenom_epoux'] ?? '')) ?: 'Inconnu';
		   $typeevent = 'au Mariage ' . $dataeventv['type_mar'] .' de '.$fetard. ', le ' . date('d M Y à H:i', strtotime($dataeventv['date_event']));
	   } elseif ($type_event == "2") {
		   $fetard = $dataeventv['nomfetard'] ?? 'Inconnu';
		   $typeevent = "à l'anniversaire de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataeventv['date_event']));
	   } elseif ($type_event == "3") {
		   $fetard = $dataeventv['nomfetard'] ?? 'Inconnu';
		   $typeevent = "à la conférence de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataeventv['date_event']));
	   }
   

	    

		// Vos identifiants Twilio
		$accountSid = 'AC5cbb94f85695ce16d97ce2ca2c3f7db0';
		$authToken = '2fc99f87d42f61c691c01df995fb8290'; 
		$twilionumber = 'whatsapp:+17167403177'; // Votre Messaging Service SID
		$recipientNumber = 'whatsapp:+243852266590'; // Numéro du destinataire

		// Créer une instance du client Twilio
		$client = new Client($accountSid, $authToken);

		// Envoi d'un message avec un modèle
		$messageTemplate = $client->messages->create(
			$recipientNumber,
			[
				'from' => $twilionumber,
				//'template' => 'tempinvitation', // Nom du modèle
				//'contentSid' => 'HX89f7cc8f64f0b71ade4a22cdffcb744d', // NEW
				'contentSid' => 'HX5e527d4a4e566b51065fcebade782c17',
				'templateData' => json_encode([
					'params' => [
						'1' => $inviteName, // Nom
						'2' => $typeevent, // Événement
						'3' => 'www.invitationspeciale.com/site/index.php?page=accueil&cod='.$codevent, // Lien
						'4' => 'www.invitationspeciale.com/site/index.php?page=accueil&cod='.$codevent // Invitation
					],
				]),
				'language' => 'fr', // Langue du modèle
			]
		);
   
	   echo '<script>
	   Swal.fire({
		   title: "Notification !",
		   text: "Notification envoyée avec succès.",
		   icon: "success",
		   confirmButtonText: "OK"
	   }).then((result) => {
		   if (result.isConfirmed) {
			   window.location.href = "index.php?page=mb_accueil"; // Rédirection vers la page de détails
		   }
	   });
	   </script>';
   }
   ?>
			   <div class="form-group"> 
				   <span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
				   <h4 id="modalTitle">Notifier </h4> <br><br>
				   <input type="text" required pattern="^\+\d{1,3}\d{9,}$" 
				   title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
				   <input type="hidden" id="inviteName" name="inviteName" />
				   <button class="btn btn-primary" type="submit" name="submitwhat" style="border-radius:0px 0px 7px 7px;width:100%;">Notifier</button>
			   </div>
			   <br>
			   <a href="#" id="downloadLink">Télécharger l'invitation</a>
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
			   document.getElementById('modalTitle').innerText = 'Notifier ' + inviteName;
			   document.getElementById('shareModal').style.display = 'flex';
			   const linkpdf = "../pages/invitation_elect.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
			   document.getElementById('downloadLink').setAttribute('href', linkpdf);
			   document.getElementById('downloadLink').setAttribute('target', "_blank");
			   document.getElementById('inviteName').value = inviteName; // Récupération du nom de l'invité
		   }
   
		   function closeModal() {
			   document.getElementById('shareModal').style.display = 'none';
		   }
	   </script>





	<!-- filtrer la recherche en temps reel -->

		<script>
		document.getElementById("searchInput").addEventListener("input", function () {
			let filter = this.value.toLowerCase();
			let rows = document.querySelectorAll("#inviteList .invite-item");

			rows.forEach(function (row) {
				let name = row.querySelector(".invite-name").textContent.toLowerCase();
				row.style.display = name.includes(filter) ? "" : "none";
			});
		});
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
	
	
		

                
				<?php include ('chatsupport.php')?>

	
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
	  