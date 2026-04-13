
		
<div class="wrapper"> 
	 

     <?php include('header.php');?>
	 <?php
	 $menus = MenuCatalogService::listByEvent($pdo, (int) $codevent);
	 $totalMenus = count($menus);
	 $menuCategories = count(MenuCatalogService::listCategories($pdo));
	 ?>
      
    
   
     <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
               <div class="container-full">
               <!-- Main content -->
                
			<style>
				.mb-action-page{
					padding:24px 0 42px;
				}

				.mb-action-hero{
					padding:28px 30px;
					border-radius:30px;
					background:linear-gradient(135deg,#1f2937 0%,#0f172a 55%,#d97706 100%);
					box-shadow:0 24px 50px rgba(15,23,42,.18);
					color:#f8fafc;
					margin-bottom:26px;
				}

				.mb-action-kicker{
					display:inline-flex;
					align-items:center;
					gap:8px;
					padding:7px 12px;
					border-radius:999px;
					background:rgba(255,255,255,.14);
					border:1px solid rgba(255,255,255,.16);
					font-size:12px;
					font-weight:800;
					text-transform:uppercase;
				}

				.mb-action-title{
					margin:16px 0 10px;
					font-size:34px;
					line-height:1.05;
					font-weight:800;
					color:#fff;
				}

				.mb-action-subtitle{
					margin:0;
					max-width:700px;
					color:rgba(226,232,240,.88);
					font-size:15px;
					line-height:1.7;
				}

				.mb-action-stats{ display:flex; gap:12px; flex-wrap:wrap; margin-top:20px; }
				.mb-action-stat{ display:inline-flex; align-items:center; gap:10px; padding:12px 16px; border-radius:18px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.14); font-weight:700; }
				.mb-action-stat strong{ font-size:18px; font-weight:800; color:#fff; }
				.mb-action-card{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.10); }
				.mb-action-card .content-top-agile{ padding:26px 28px 10px !important; }
				.mb-action-card .p-40{ padding:18px 28px 30px !important; }
				.mb-action-heading{ margin:0; font-size:28px; font-weight:800; color:#0f172a; }
				.mb-action-copy{ margin:8px 0 0; font-size:14px; color:#64748b; }
				.mb-action-card .form-group{ margin-bottom:16px; }
				.mb-action-card .input-group{ border:1px solid #fde7c7; border-radius:18px; background:#fffaf2; overflow:hidden; }
				.mb-action-card .input-group-text,
				.mb-action-card .form-control,
				.mb-action-card textarea.form-control,
				.mb-action-card select.form-control{ border:0 !important; background:transparent !important; box-shadow:none !important; min-height:56px; }
				.mb-action-card .input-group-text{ color:#d97706; padding-left:16px; padding-right:8px; }
				.mb-action-card textarea.form-control{ padding-top:16px; padding-bottom:16px; }
				.mb-action-submit{ display:inline-flex; align-items:center; justify-content:center; min-height:58px; border:0; border-radius:18px; background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%); box-shadow:0 18px 34px rgba(217,119,6,.20); font-size:15px; font-weight:800; }
				.mb-action-list-shell{ margin-top:24px; }
				.mb-action-list-title{ margin:0; font-size:24px; font-weight:800; color:#0f172a; }
				.mb-action-list-copy{ margin:8px 0 18px; color:#64748b; font-size:14px; }
				.mb-action-menu-row td{ padding:16px 0 !important; border-color:#eef2f7 !important; vertical-align:middle; }
				.mb-action-menu-name{ display:block; color:#0f172a; font-size:16px; font-weight:800; text-decoration:none; }
				.mb-action-menu-meta{ display:inline-flex; align-items:center; margin-top:8px; padding:7px 11px; border-radius:999px; background:#fff7ed; border:1px solid #fed7aa; color:#c2410c; font-size:12px; font-weight:700; }
				.mb-action-empty{ color:#64748b; font-style:italic; text-align:left; }

				@media only screen and (max-width: 769px) {
					.mb-action-page{ padding:18px 0 34px; }
					.mb-action-hero{ padding:22px 20px; border-radius:24px; }
					.mb-action-title{ font-size:28px; }
					.mb-action-card .content-top-agile,
					.mb-action-card .p-40{ padding-left:20px !important; padding-right:20px !important; }
				}
			</style>

			<div class="container h-p100 mb-action-page">
					<div class="mb-action-hero">
						<span class="mb-action-kicker"><i class="mdi mdi-silverware-fork-knife"></i> Gestion du menu</span>
						<h1 class="mb-action-title">Composez un menu clair et appétissant</h1>
						<p class="mb-action-subtitle">Ajoutez les éléments du menu par catégorie pour structurer l’offre et faciliter l’affichage côté invités.</p>
						<div class="mb-action-stats">
							<span class="mb-action-stat"><i class="mdi mdi-food-outline"></i> Menus <strong><?php echo $totalMenus; ?></strong></span>
							<span class="mb-action-stat"><i class="mdi mdi-shape-outline"></i> Catégories <strong><?php echo $menuCategories; ?></strong></span>
						</div>
					</div>
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
							<div class="row justify-content-center g-4">
								<div class="col-xl-6 col-lg-7 col-12 boxcontent">
									<div class="bg-white rounded10 shadow-lg mb-action-card">
							<div class="content-top-agile p-20 pb-0"> 
                                <p class="mb-0 text-fade">Ajouter au Menu</p>
											<h2 class="mb-action-heading">Nouvel élément</h2>
											<p class="mb-action-copy">Choisissez la catégorie, renseignez le nom du plat ou de la boisson et ajoutez une description utile pour l’affichage.</p>
                                
                                



<?php 

 
  
if (isset($_POST['submitmenu'])) {
    $catmenu = @$_POST['catmenu'];
    $nom = @$_POST['nom'];
    $desc_menu = @$_POST['desc_menu'];
    $hote = $datasession['cod_user'];

    if (!$catmenu || !$nom) {
        echo "<div class='error' align=\"left\" style=\"color:red;font-weight:bold;text-align:center;\">Le champ catégorie et nom sont obligatoires</div>";
    } else {
		if (MenuCatalogService::existsByCategoryAndName($pdo, (int) $codevent, (string) $catmenu, (string) $nom)) {
            echo "<div class='error' align=\"left\" style=\"color:red;font-weight:bold;text-align:center;\">Ce menu existe déjà.</div>";
        } else {
			MenuCatalogService::create($pdo, (int) $codevent, (string) $catmenu, (string) $nom, (string) $desc_menu, (string) $hote);

            echo "<div class='success' align=\"left\" style=\"color:green;font-weight:bold;text-align:center;\">Menu ajouté avec succès.</div>";
        }
    }
}

?>
							</div>



							<div class="p-40"> 

							<form action="" method="post">   

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-chair"></i></span>
                                        <select class="form-control ps-15 bg-transparent" name="catmenu">
                                            <option style="color:#eee;" value="">Catégorie</option>
											<?php foreach (MenuCatalogService::listCategories($pdo) as $data_catmenu) {
                                            ?>
                                            <option value="<?php echo $data_catmenu['cod_cm']?>" <?php if(@$_POST['catmenu'] == $data_catmenu['cod_cm']){echo "selected";} ?>><?php echo $data_catmenu['nom']?></option>
                                            <?php } ?>  
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control ps-15 bg-transparent" name="nom" placeholder="Noms">
                                    </div>
                                </div>


                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-align-center"></i></span> 
                                        <textarea name="desc_menu" class="form-control ps-15 bg-transparent" placeholder="Description"></textarea>
                                    </div>
                                </div>



                                <div class="row"> 
                                    <div class="col-12 text-center">
										<button type="submit" name="submitmenu" class="btn btn-primary w-p100 mt-10 mb-action-submit">Ajouter au menu</button>
                                    </div>
                                </div>
                            </form>			
							 	
							</div> 


































   
   <?php 
   
   
   
   if (!$dataevent) {
	   $linkallinv = "#";
   }else{
	   $linkallinv = "../pages/liste_invites.php?event=".$codevent;
   } 
   
   ?>
    
	   <div class="col-xxl-12 col-xl-12 col-lg-12 mb-action-list-shell">
		   <div class="card rounded-4 mb-action-card">
			   <div class="box-header d-flex b-0 justify-content-between align-items-center">
				   <div>
					   <h4 class="box-title mb-action-list-title">Menu</h4>
					   <p class="mb-action-list-copy">Consultez et modifiez les éléments déjà publiés pour votre événement.</p>
				   </div>
				   
			   </div>
   
			   <div class="card-body pt-0">
				   <div class="table">
					   <table class="table mb-0">
						   <tbody>
							   <?php 
if (!empty($menus)) {
    foreach ($menus as $row_menu) {

$nomcat = $row_menu['categorie_nom'] ?? '';
         
							   ?>
									   <tr class="mb-action-menu-row">
										   <td class="pt-0 px-0 b-0">
											   <a class="mb-action-menu-name" href="#"><?php echo htmlspecialchars(ucfirst($row_menu['nom'])); ?></a>
											   <span class="mb-action-menu-meta"><?php echo $nomcat; ?></span>
										   </td> 
   
										   <td class="text-end b-0 pt-0 px-0" width="15%"> 
											  



 
 
 
 
 
  
 
 
  
 
 
 
											   <div class="list-icons d-inline-flex">
                          <div class="list-icons-item dropdown">
                                          
  
                           <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                                     <div class="dropdown-menu dropdown-menu-end">
  
   
											   <a class="dropdown-item" href="index.php?page=modmenu&cod=<?php echo $row_menu['cod_mev']; ?>"><i class="fa fa-edit"></i> Modifier</a>
											   <a class="dropdown-item" href="#" style="color:red;" title="Suppression" onclick="confirmSuppMenu(event, <?php echo (int) $row_menu['cod_mev']; ?>, '<?php echo htmlspecialchars(addslashes((string) $row_menu['nom'])); ?>')"><i class="fa fa-remove"></i> Supprimer</a>
      
       
                        </div>
                         </div>
                       </div>



										   </td>
									   </tr>
   
							   <?php 
   
								   }
   
							   } else {
								   echo '<tr class="mb-action-menu-row"><td colspan="3" class="mb-action-empty">Aucun menu trouvé</td></tr>';
							   }
   
							   ?>
   
						   </tbody>
					   </table>
				   </div>
			   </div>	
		   </div>
	   </div>
   
	   <!-- Fenêtre modale -->
	   <script>
		function confirmSuppMenu(event, menuId, menuName) {
			event.preventDefault();
			Swal.fire({
				title: "Supprimer !",
				text: "Êtes-vous sûr de vouloir supprimer " + menuName + " ?",
				icon: "warning",
				showCancelButton: true,
				confirmButtonText: "Oui",
				cancelButtonText: "Non"
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = "index.php?page=supmenu&cod=" + encodeURIComponent(menuId) + "&codevent=<?php echo urlencode((string) $codevent); ?>";
				}
			});
		}
	   </script>

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
		   text: "Votre invitation a été envoyée avec succès.",
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
				   <h4 id="modalTitle">Partager avec </h4> <br><br>
				   <input type="text" required pattern="^\+\d{1,3}\d{9,}$" 
				   title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
				   <input type="hidden" id="inviteName" name="inviteName" />
				   <button class="btn btn-primary" type="submit" name="submitwhat" style="border-radius:0px 0px 7px 7px;width:100%;">Partager</button>
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
			   document.getElementById('modalTitle').innerText = 'Partager avec ' + inviteName;
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
   
   
   
   
   
   
   
   
   
   
   




				</div>
			</div>			









					</div>

					</div>













		</div>
	</div>



    























    


    
			</div>			
		</div>
	</div>

 
  <!-- /.content-wrapper -->
  <?php include('footer.php')?>
  <!-- Side panel --> 
  <!-- quick_user_toggle -->
	
 
	
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
	<!-- Vendor JS -->
	<script src="html/template/horizontal/src/js/vendors.min.js"></script>
	<script src="html/template/horizontal/src/js/pages/chat-popup.js"></script>
  	<script src="../../../assets/icons/feather-icons/feather.min.js"></script>
  	<script src="../../../assets/vendor_components/Flot/jquery.flot.js"></script>
	<script src="../../../assets/vendor_components/Flot/jquery.flot.resize.js"></script>
	<script src="../../../assets/vendor_components/Flot/jquery.flot.pie.js"></script>
	<script src="../../../assets/vendor_components/Flot/jquery.flot.categories.js"></script>
	<script src="../../../assets/vendor_components/echarts/dist/echarts-en.min.js"></script>
	<script src="../../../assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
	<script src="../../../assets/vendor_plugins/bootstrap-slider/bootstrap-slider.js"></script>
	<script src="../../../assets/vendor_components/OwlCarousel2/dist/owl.carousel.js"></script>
	<script src="../../../assets/vendor_components/flexslider/jquery.flexslider.js"></script>
	<script src="../assets/vendor_components/Web-Ticker-master/jquery.webticker.min.js"></script>
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
	 
	