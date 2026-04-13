

<div class="wrapper"> 
	<?php include('header.php');?>

	<div class="content-wrapper">
		<div class="container-full">
			<?php 
			$guestStats = EventWorkspaceService::getGuestStats($pdo, (string) $codevent, $date_event ?: null);
			extract($guestStats, EXTR_OVERWRITE);

			$eventIdentity = EventWorkspaceService::resolveEventIdentity(
				is_array($dataevent) ? $dataevent : [],
				(string) $type_event,
				(string) $data_evenement
			);
			extract($eventIdentity, EXTR_OVERWRITE);

			$formatted_date = EventWorkspaceService::formatEventDate($date_event ?: null);
			$heure = date('H');

			if ($heure < 12) {
				$salut = 'Bonjour';
			} elseif ($heure > 11 && $heure < 15) {
				$salut = 'Bon Après-midi';
			} else {
				$salut = 'Bonsoir';
			}

			$date_formatted = $date_event ? (new DateTime($date_event))->format('Y-m-d H:i') : '';
			$eventOptions = EventWorkspaceService::getUserEventOptions($pdo, (string) $vuoption, (string) ($datasession['cod_user'] ?? ''));
			$activeWeddingType = isset($dataevent['type_mar']) ? trim((string) $dataevent['type_mar']) : '';
			$activeEventTitle = trim((string) $data_evenement . ' ' . $activeWeddingType);
			$activeEventTitle = $activeEventTitle !== '' ? $activeEventTitle : 'Aucun événement actif';
			$activeEventDateLabel = $date_event ? date('d/m/Y à H:i', strtotime($date_event)) : 'Date non définie';
			$eventOptionsCount = count($eventOptions);
			$siteWebSummary = trim((string) ($text_sdd ?? ''));
			$siteWebSummary = $siteWebSummary !== '' ? $siteWebSummary : 'Présentez votre événement avec un mini-site élégant, vos informations essentielles et vos confirmations en ligne.';
			$eventLocationLabel = trim((string) ($dataevent['lieu'] ?? ''));
			$eventLocationLabel = $eventLocationLabel !== '' ? $eventLocationLabel : 'Lieu à confirmer';
			?>

			<div class="row salut">
				<p style="text-align:center;">
					<?php echo $salut; ?> <b><?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, 'UTF-8'); ?></b>!
				</p>
			</div>

			<section class="content">
				<div class="row">
					<div class="col-12">
						<div class="row">
							<div class="col-md-12">
								<div class="event-switcher-card">
									<div class="event-switcher-copy">
										<span class="event-switcher-kicker">Espace client</span>
										<div class="event-switcher-head">
											<div>
												<h3><?php echo htmlspecialchars($activeEventTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
												<p>Événement actif • <?php echo htmlspecialchars($activeEventDateLabel, ENT_QUOTES, 'UTF-8'); ?></p>
											</div>
											<span class="event-switcher-badge"><?php echo $eventOptionsCount + ($dataevent ? 1 : 0); ?> événement<?php echo ($eventOptionsCount + ($dataevent ? 1 : 0)) > 1 ? 's' : ''; ?></span>
										</div>
									</div>

									<?php if ($dataevent && $eventOptionsCount > 0) { ?>
									<div class="event-switcher-control">
										<label for="mbEventSwitcher">Changer d'événement</label>
										<div class="event-switcher-select-wrap">
											<i class="mdi mdi-calendar-switch event-switcher-icon"></i>
											<select id="mbEventSwitcher" class="form-select event-switcher-select" onchange="if(this.value){window.location.href=this.value;}">
												<option value="">Basculer vers un autre événement</option>
												<?php foreach ($eventOptions as $eventOption) { ?>
												<option value="index.php?page=mb_accueil&codevent=<?php echo $eventOption['cod_event']; ?>"><?php echo htmlspecialchars($eventOption['label'], ENT_QUOTES, 'UTF-8'); ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<?php } elseif ($dataevent) { ?>
									<div class="event-switcher-empty">Cet espace ne contient pas encore d’autre événement à afficher.</div>
									<?php } else { ?>
									<div class="event-switcher-empty">Aucun événement trouvé pour ce compte.</div>
									<?php } ?>
								</div>
							</div>
						</div>
 

							   <style>
								.event-switcher-card{
									display:flex;
									align-items:flex-end;
									justify-content:space-between;
									gap:22px;
									padding:22px 24px;
									border-radius:24px;
									background:linear-gradient(135deg,#0b1324 0%,#13203a 55%,#1d4ed8 100%);
									box-shadow:0 24px 50px rgba(15,23,42,.18);
									color:#f8fafc;
									margin-bottom:25px;
								}

								.event-switcher-copy{
									flex:1 1 auto;
									min-width:0;
								}

								.event-switcher-kicker{
									display:inline-flex;
									align-items:center;
									padding:6px 12px;
									border-radius:999px;
									background:rgba(255,255,255,.14);
									color:rgba(255,255,255,.82);
									font-size:12px;
									font-weight:700;
									letter-spacing:.08em;
									text-transform:uppercase;
									margin-bottom:14px;
								}

								.event-switcher-head{
									display:flex;
									align-items:flex-start;
									justify-content:space-between;
									gap:16px;
								}

								.event-switcher-head h3{
									margin:0;
									font-size:28px;
									font-weight:800;
									line-height:1.15;
									color:#fff;
								}

								.event-switcher-head p{
									margin:8px 0 0;
									color:rgba(226,232,240,.78);
									font-size:14px;
								}

								.event-switcher-badge{
									display:inline-flex;
									align-items:center;
									white-space:nowrap;
									padding:8px 14px;
									border-radius:999px;
									background:rgba(255,255,255,.16);
									border:1px solid rgba(255,255,255,.18);
									font-size:12px;
									font-weight:700;
									color:#fff;
								}

								.event-switcher-control{
									flex:0 0 360px;
									max-width:100%;
								}

								.event-switcher-control label{
									display:block;
									margin-bottom:8px;
									font-size:13px;
									font-weight:700;
									color:rgba(226,232,240,.82);
								}

								.event-switcher-select-wrap{
									position:relative;
								}

								.event-switcher-icon{
									position:absolute;
									left:16px;
									top:50%;
									transform:translateY(-50%);
									color:#1d4ed8;
									font-size:18px;
									pointer-events:none;
								}

								.event-switcher-select{
									height:56px;
									padding:0 48px 0 48px;
									border-radius:18px;
									border:1px solid rgba(191,219,254,.65);
									background:#f8fbff;
									color:#0f172a;
									font-weight:600;
									box-shadow:none;
								}

								.event-switcher-select:focus{
									border-color:#93c5fd;
									box-shadow:0 0 0 4px rgba(59,130,246,.18);
								}

								.event-switcher-empty{
									flex:0 0 360px;
									max-width:100%;
									padding:16px 18px;
									border-radius:18px;
									background:rgba(255,255,255,.12);
									border:1px solid rgba(255,255,255,.14);
									color:rgba(226,232,240,.86);
									font-size:14px;
									font-weight:600;
								}

								.mb-primary-cta{
									display:flex;
									align-items:center;
									justify-content:center;
									gap:10px;
									height:58px;
									border:none;
									border-radius:18px;
									background:linear-gradient(135deg,#ef4444 0%,#b91c1c 100%);
									box-shadow:0 18px 40px rgba(185,28,28,.22);
									font-size:16px;
									font-weight:800;
									letter-spacing:.01em;
									margin-bottom:25px;
								}

								.mb-dashboard-shell{ margin-top:8px; }
								.mb-event-hero,
								.mb-side-panel,
								.mb-stat-card{
									border:0;
									border-radius:28px;
									overflow:hidden;
									box-shadow:0 22px 48px rgba(15,23,42,.10);
								}
								.mb-event-hero{ position:relative; min-height:100%; background:linear-gradient(180deg,rgba(11,19,36,.08),rgba(11,19,36,.08)); }
								.mb-event-hero-cover{ position:relative; padding:32px 28px 24px; background-size:cover; background-position:center; color:#fff; }
								.mb-event-hero-cover::before{ content:''; position:absolute; inset:0; background:linear-gradient(180deg,rgba(15,23,42,.22) 0%,rgba(15,23,42,.55) 58%,rgba(15,23,42,.82) 100%); }
								.mb-event-hero-inner{ position:relative; z-index:1; }
								.mb-hero-topline{ display:flex; align-items:flex-start; justify-content:space-between; gap:18px; margin-bottom:28px; }
								.mb-hero-pill,
								.mb-hero-date{ display:inline-flex; align-items:center; gap:8px; padding:9px 14px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.18); backdrop-filter:blur(10px); font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
								.mb-hero-profile{ display:flex; align-items:center; gap:18px; }
								.mb-hero-avatar{ width:106px; height:106px; flex:0 0 106px; border-radius:28px; background-size:cover; background-position:center; border:4px solid rgba(255,255,255,.18); box-shadow:0 18px 28px rgba(15,23,42,.28); }
								.mb-hero-title{ margin:0; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
								.mb-hero-type{ margin:8px 0 0; font-size:15px; color:rgba(226,232,240,.90); }
								.mb-hero-location{ margin:14px 0 0; display:inline-flex; align-items:center; gap:10px; padding:10px 14px; border-radius:16px; background:rgba(15,23,42,.26); color:#e2e8f0; font-size:14px; font-weight:600; }
								.mb-hero-metrics{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-top:26px; }
								.mb-hero-metric{ padding:16px 18px; border-radius:18px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.12); }
								.mb-hero-metric span{ display:block; font-size:12px; font-weight:700; color:rgba(226,232,240,.78); text-transform:uppercase; letter-spacing:.06em; }
								.mb-hero-metric strong{ display:block; margin-top:10px; font-size:28px; line-height:1; font-weight:800; color:#fff; }
								.mb-site-panel{ padding:24px 28px 28px; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); }
								.mb-panel-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
								.mb-site-panel h5,
								.mb-side-panel h5{ margin:14px 0 10px; font-size:24px; font-weight:800; color:#0f172a; }
								.mb-site-panel p,
								.mb-side-panel p{ margin:0; color:#475569; font-size:14px; line-height:1.7; }
								.mb-inline-actions{ display:flex; flex-wrap:wrap; gap:12px; margin-top:20px; }
								.mb-outline-action,
								.mb-solid-action{ display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:46px; padding:0 16px; border-radius:14px; font-weight:700; text-decoration:none; }
								.mb-solid-action{ background:linear-gradient(135deg,#0ea5e9 0%,#2563eb 100%); color:#fff !important; box-shadow:0 14px 28px rgba(37,99,235,.18); }
								.mb-outline-action{ background:#fff; border:1px solid #dbeafe; color:#0f172a !important; }
								.mb-side-panel{ padding:26px; background:linear-gradient(180deg,#f8fbff 0%,#ffffff 100%); }
								.mb-side-panel + .mb-side-panel{ margin-top:18px; }
								.mb-countdown-shell{ padding:22px; margin-top:18px; border-radius:22px; background:linear-gradient(135deg,#e0f2fe 0%,#dbeafe 100%); }
								.mb-side-actions{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin-top:18px; }
								.mb-quick-btn{ display:inline-flex; align-items:center; justify-content:center; gap:10px; min-height:58px; padding:0 18px; border-radius:18px; text-decoration:none; font-size:15px; font-weight:800; color:#fff !important; box-shadow:0 16px 30px rgba(15,23,42,.16); transition:transform .18s ease, box-shadow .18s ease, filter .18s ease; }
								.mb-quick-btn:hover{ transform:translateY(-2px); box-shadow:0 20px 36px rgba(15,23,42,.20); filter:saturate(1.05); }
								.mb-quick-btn i{ font-size:18px; margin-right:8px; }
								.mb-quick-btn-invite{ background:linear-gradient(135deg,#16a34a 0%,#15803d 100%); }
								.mb-quick-btn-table{ background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); }
								.mb-quick-btn-menu{ background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%); }
								.mb-quick-btn-gift{ background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%); }
								.mb-stat-grid{ margin-top:6px; }
								.mb-stat-card{ padding:22px; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); }
								.mb-stat-card-inner{ display:flex; align-items:center; gap:16px; }
								.mb-stat-icon{ width:62px; height:62px; flex:0 0 62px; display:inline-flex; align-items:center; justify-content:center; border-radius:20px; color:#fff; font-size:22px; box-shadow:0 14px 24px rgba(15,23,42,.14); }
								.mb-stat-icon.primary{ background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); }
								.mb-stat-icon.info{ background:linear-gradient(135deg,#06b6d4 0%,#0284c7 100%); }
								.mb-stat-icon.success{ background:linear-gradient(135deg,#22c55e 0%,#15803d 100%); }
								.mb-stat-icon.warning{ background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%); }
								.mb-stat-copy span{ display:block; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
								.mb-stat-copy strong{ display:block; margin-top:8px; font-size:30px; line-height:1; font-weight:800; color:#0f172a; }
								.mb-stat-copy a{ text-decoration:none; }

								@media only screen and (max-width: 769px) {
									.event-switcher-card{ flex-direction:column; align-items:stretch; padding:18px; border-radius:20px; }
									.event-switcher-head{ flex-direction:column; }
									.event-switcher-head h3{ font-size:22px; }
									.event-switcher-control,
									.event-switcher-empty{ flex-basis:auto; width:100%; }
									.col-xl-7,
									.col-xl-5{ margin-bottom:25px; }
									.mb-hero-topline,
									.mb-hero-profile{ flex-direction:column; align-items:flex-start; }
									.mb-hero-title{ font-size:28px; }
									.mb-hero-metrics,
									.mb-side-actions{ grid-template-columns:1fr; }
									.mb-site-panel,
									.mb-side-panel,
									.mb-event-hero-cover{ padding:20px; }
									#calendrier_mobile{ display:none; }
								}
							   </style>
   
 
   



						   <div class="col-xl-12">

							   <div class="d-grid">
									<a href="index.php?page=addevent" class="btn btn-danger mb-primary-cta">
										<i class="mdi mdi-plus me-2"></i> Ajouter un événement
									</a>
								</div>
							   
						   </div>
 







   <div style="display:<?php echo $display;?>">
   


					   <div class="row" style="margin-bottom:25px;">
						   <!-- <div class="col-xl-9" id="calendrier_mobile"> -->


  



						   
							   <div class="col-xl-7">
								   <div class="mb-event-hero"  style="background-color:#fff;">
									   <div class="mb-event-hero-cover" style="background-image:url('../../couple/images/<?php echo htmlspecialchars($photo, ENT_QUOTES, 'UTF-8'); ?>');">
										   <div class="mb-event-hero-inner">
											   <div class="mb-hero-topline">
												   <span class="mb-hero-pill"><i class="mdi mdi-star-circle"></i> Tableau de bord</span>
												   <span class="mb-hero-date"><i class="mdi mdi-calendar-clock"></i> <?php echo htmlspecialchars($formatted_date, ENT_QUOTES, 'UTF-8'); ?></span>
											   </div>
											   <div class="mb-hero-profile">
												   <div class="mb-hero-avatar" style="background-image:url('../../couple/images/<?php echo htmlspecialchars($photocoeur, ENT_QUOTES, 'UTF-8'); ?>');"></div>
												   <div>
													   <p class="mb-hero-type"><?php echo htmlspecialchars($typeevent, ENT_QUOTES, 'UTF-8'); ?></p>
													   <span class="mb-hero-location"><i class="mdi mdi-map-marker-radius"></i> <?php echo htmlspecialchars($eventLocationLabel, ENT_QUOTES, 'UTF-8'); ?></span>
												   </div>
											   </div>
											   <div class="mb-hero-metrics">
												   <div class="mb-hero-metric">
													   <span>Invités</span>
													   <strong><?php echo (int) $total_inv; ?></strong>
												   </div>
												   <div class="mb-hero-metric">

												   	   <a href="index.php?page=mb_conf_list"> 
													   <span>Réponses</span>
													   <strong><?php echo (int) $total_invconf; ?></strong>
													   </a>
												   </div>
												   <div class="mb-hero-metric">

												   	   <a href="https://invitationspeciale.com/site/index.php?page=access&cod=<?php echo $codevent; ?>"> 

													   <span>Présences</span>
													   <strong><?php echo (int) $total_invpre; ?></strong>

													   </a>
												   </div>
											   </div>
										   </div>
									   </div>
									   <div class="mb-site-panel">
										   <span class="mb-panel-kicker"><i class="mdi mdi-web"></i> Site web</span>
										   <h5>Votre vitrine événementielle est prête</h5>
										   <p><?php echo htmlspecialchars($siteWebSummary, ENT_QUOTES, 'UTF-8'); ?></p>
										   <div class="mb-inline-actions">
											   <a class="mb-solid-action" href="index.php?page=conf_siteweb"><i class="mdi mdi-palette-outline"></i> Personnaliser</a>
											   <a class="mb-outline-action" href="https://invitationspeciale.com/site/index.php?page=accueil&cod=<?php echo $codevent; ?>" target="_blank"><i class="mdi mdi-eye-outline"></i> Prévisualiser le site</a>
											   <a class="mb-outline-action" href="https://invitationspeciale.com/menu/index.php?page=accueil&cod=<?php echo $codevent; ?>" target="_blank"><i class="mdi mdi-food"></i> Voir le menu</a>
										   </div>
									   </div>
								   </div>
							   </div>

							   <div class="col-xl-5">
								   <div class="mb-side-panel text-center">
									   <span class="mb-panel-kicker"><i class="mdi mdi-timer-sand"></i> Compte à rebours</span>
									   <h5>Préparez la journée avec précision</h5>
									   <p>L’essentiel de l’événement reste accessible en un coup d’œil, avec un accès rapide à vos réglages.</p>
									   <div class="mb-countdown-shell">
										   <?php include('comptearebour.php');?>
									   </div>
									   <div class="mb-inline-actions" style="justify-content:center;">
										   <a class="mb-solid-action" href="index.php?page=modevent&cod=<?php echo $codevent?>"><i class="mdi mdi-cog-outline"></i> Modifier l'événement</a>
										   <a class="mb-outline-action" href="index.php?page=addgestion" target="_blank"><i class="mdi mdi-account-plus-outline"></i> Ajouter un administrateur</a>
									   </div>
								   </div>

								   <div class="mb-side-panel">
									   <span class="mb-panel-kicker"><i class="mdi mdi-flash-outline"></i> Actions rapides</span>
									   <h5>Accélérez l’organisation</h5>
									   <p>Accédez directement aux tâches les plus utilisées pour faire avancer votre événement.</p>
									   <div class="mb-side-actions">
										   <a class="mb-quick-btn mb-quick-btn-invite" href="index.php?page=addinvite&codevent=<?php echo $codevent?>">
											   <i class="mdi mdi-plus me-2"></i> Ajouter les invités
										   </a>
										   <a class="mb-quick-btn mb-quick-btn-table" href="index.php?page=addtable&codevent=<?php echo $codevent?>">
											   <i class="mdi mdi-plus me-2"></i> Ajouter les tables
										   </a>
										   <a class="mb-quick-btn mb-quick-btn-menu" href="index.php?page=addmenu&codevent=<?php echo $codevent?>">
											   <i class="mdi mdi-plus me-2"></i> Ajouter le menu
										   </a>
										   <?php if ($displayvue === 'display:block;') { ?>
										   <a class="mb-quick-btn mb-quick-btn-gift" href="javascript:void(0)">
											   <i class="mdi mdi-plus me-2"></i> Liste des cadeaux
										   </a>
										   <?php } ?>
									   </div>
								   </div>
							   </div>
						   </div> 
 
						   <div class="row">
							   <div class="col-12">
								   <?php include('liste_invite.php')?>
							   </div>
						   </div>
									   </div>
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
		 