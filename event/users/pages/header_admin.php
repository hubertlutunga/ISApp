 
<?php 
	if (!isset($_SESSION['user_phone'])) {
		header("Location: index.php?page=logout"); // Rediriger vers users.php si déjà connecté
		exit();
	}

	$stmtss = $pdo->prepare("SELECT * FROM is_users WHERE phone = ?");
	$stmtss->execute([$_SESSION['user_phone']]);
	$datasession = $stmtss->fetch(PDO::FETCH_ASSOC) ?: ['noms' => 'Utilisateur', 'type_user' => ''];
	$typeUser = (string) ($datasession['type_user'] ?? '');
	$isImpersonating = UserAccountService::isImpersonating();
	$headerLowQuotaNotifications = [];
	$headerLowQuotaThreshold = 50;

	if ($typeUser === '1' && class_exists('AdminClientManagementService')) {
		$headerLowQuotaThreshold = max(1, (int) ($_GET['quota_threshold'] ?? 50));
		try {
			$headerLowQuotaNotifications = AdminClientManagementService::buildLowQuotaNotifications($pdo, $headerLowQuotaThreshold);
		} catch (Throwable $exception) {
			$headerLowQuotaNotifications = [];
		}
	}

 
	if ((($_GET['page'] ?? '') === 'admin_accueil' OR ($_GET['page'] ?? '') === 'factures') && $typeUser !== '1') {
		header("Location: index.php?page=logout"); // Rediriger vers users.php si déjà connecté
		exit();
	}

?>

<style>
	.header-notif-menu .dropdown-menu {
		width: 360px;
		max-width: calc(100vw - 32px);
		border-radius: 16px;
		overflow: hidden;
		border: 1px solid #dbe4f0;
		box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
		padding: 0;
	}

	.header-notif-head {
		padding: 14px 16px;
		border-bottom: 1px solid #e2e8f0;
		background: linear-gradient(90deg, #ecfeff 0%, #f0f9ff 100%);
	}

	.header-notif-head strong {
		display: block;
		color: #0f172a;
		font-size: 14px;
		font-weight: 800;
	}

	.header-notif-head span {
		color: #475569;
		font-size: 12px;
	}

	.header-notif-list {
		max-height: 320px;
		overflow-y: auto;
	}

	.header-notif-item {
		display: block;
		padding: 12px 16px;
		text-decoration: none;
		border-bottom: 1px solid #f1f5f9;
	}

	.header-notif-item:hover,
	.header-notif-item:focus-visible {
		background: #f8fafc;
	}

	.header-notif-item strong {
		display: block;
		color: #0f172a;
		font-size: 13px;
		font-weight: 800;
		margin-bottom: 2px;
	}

	.header-notif-item span {
		display: block;
		color: #64748b;
		font-size: 12px;
	}

	.header-notif-foot {
		padding: 10px 14px;
		background: #fff;
	}

	.header-notif-foot a {
		display: block;
		text-align: center;
		font-size: 12px;
		font-weight: 800;
		text-decoration: none;
		color: #0f766e;
	}

	.header-notif-empty {
		padding: 18px 16px;
		color: #64748b;
		font-size: 12px;
	}

	.header-notif-badge {
		position: absolute;
		top: -4px;
		right: -6px;
		min-width: 18px;
		height: 18px;
		padding: 0 5px;
		border-radius: 999px;
		background: #ef4444;
		color: #fff;
		font-size: 10px;
		font-weight: 800;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border: 2px solid #0f172a;
	}

	.header-notif-trigger {
		position: relative;
	}
</style>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stop_impersonation'])) {
	$result = UserAccountService::stopImpersonation($pdo);
	header('Location: index.php?page=' . (!empty($result['success']) ? 'clients' : 'logout'));
	exit();
}
?>



  <header class="main-header" >
	  <?php if ($isImpersonating) { ?>
	  <div class="alert alert-warning mb-0 rounded-0 text-center">
		Vous consultez actuellement un compte client avec votre session administrateur.
		<form action="" method="post" style="display:inline-block; margin-left: 10px;">
			<input type="hidden" name="stop_impersonation" value="1">
			<button type="submit" class="btn btn-sm btn-dark">Revenir a mon compte admin</button>
		</form>
	  </div>
	  <?php } ?>
	  <div class="inside-header">
		<div class="d-flex align-items-center logo-box justify-content-start">
			<!-- Logo -->
			<a href="index.php?page=admin_accueil" class="logo">
			  <!-- logo-->
			  <div class="logo-lg">
				  <span class="light-logo"><img src="../images/Logo_invitationSpeciale_4.png" width="300px" alt="logo"></span>
				  <span class="dark-logo"><img src="../images/Logo_invitationSpeciale_4.png" width="300px" alt="logo"></span>
			  </div>
			</a>	
		</div>  
		<!-- Header Navbar -->
		<nav class="navbar navbar-static-top">
		  <!-- Sidebar toggle button-->
		  <div class="app-menu">
			<ul class="header-megamenu nav">
				<li class="btn-group d-md-inline-flex d-none">
					<!-- <div class="app-menu">
						<div class="search-bx mx-5">
							<form>
								<div class="input-group">
								  <input type="search" class="form-control" placeholder="Rechercher une date">
								  <div class="input-group-append">
									<button class="btn" type="date" id="button-addon3"><i class="icon-Search"><span class="path1"></span><span class="path2"></span></i></button>
								  </div>
								</div>
							</form>
						</div>
					</div> -->
				</li>
			</ul> 
		  </div>

		  <div class="navbar-custom-menu r-side">
			<ul class="nav navbar-nav phonemenupv">

			<li class="dropdown notifications-menu btn-group">
				<a href="index.php?page=accueil" class="waves-effect waves-light dropdown-toggle" title="Accueil">
					<i class="fas fa-home" style="color: white;"></i>
				</a>
			</li>

			<?php if ($isImpersonating) { ?>
			<li class="dropdown notifications-menu btn-group">
				<form action="" method="post" style="margin:0;">
					<input type="hidden" name="stop_impersonation" value="1">
					<button type="submit" class="waves-effect waves-light dropdown-toggle" title="Revenir admin" style="background:none;border:0;">
						<i class="fas fa-user-shield" style="color: white;"></i>
					</button>
				</form>
			</li>
			<?php } ?>


 


<!-- 
				<li class="dropdown notifications-menu btn-group">
					<label class="switch">
					  	<a  class="waves-effect waves-light btn-primary-light svg-bt-icon">
							<input type="checkbox" data-mainsidebarskin="toggle" id="toggle_left_sidebar_skin">
							<span class="switch-on"><i class="fas fa-moon" style="color: white;"></i></span>
							<span class="switch-off"><i class="fas fa-sun" style="color: white;"></i></span>
						</a>	
					</label>
	        	</li>  -->
<?php 
	if ($typeUser !== '3') {
       ?>
			<li class="dropdown notifications-menu btn-group">
				<a href="index.php?page=factures" class="waves-effect waves-light dropdown-toggle" title="Accueil">
					<i class="fas fa-arrow-down" style="color: white;"></i>
				</a>
			</li>

			<li class="dropdown notifications-menu btn-group">
				<a href="index.php?page=sorties" class="waves-effect waves-light dropdown-toggle" title="Sortie">
					<i class="fas fa-arrow-up" style="color: white;"></i>
				</a>
			</li>

			
			
		<?php 
	} 
?>
				<?php if ($typeUser === '1') {
					$notifCount = count($headerLowQuotaNotifications);
					$notifBadgeCount = min($notifCount, 99);
				?>
				<li class="dropdown notifications-menu btn-group header-notif-menu">
					<a href="#" class="waves-effect waves-light dropdown-toggle header-notif-trigger" data-bs-toggle="dropdown" title="Notifications quota">
						<i class="fas fa-bell" style="color: white;"></i>
						<?php if ($notifCount > 0) { ?>
						<span class="header-notif-badge"><?php echo (int) $notifBadgeCount; ?></span>
						<?php } ?>
					</a>
					<div class="dropdown-menu dropdown-menu-end">
						<div class="header-notif-head">
							<strong>Notifications quota faible</strong>
							<span>Seuil actuel: <?php echo (int) $headerLowQuotaThreshold; ?> - <?php echo (int) $notifCount; ?> client(s) concernes</span>
						</div>
						<div class="header-notif-list">
							<?php if ($notifCount > 0) {
								foreach (array_slice($headerLowQuotaNotifications, 0, 12) as $headerNotifRow) {
									$notifClientName = (string) ($headerNotifRow['client_name'] ?? 'Client');
									$notifRemaining = (int) ($headerNotifRow['remaining_quota'] ?? 0);
									$notifClientId = (int) ($headerNotifRow['client_user_id'] ?? 0);
								?>
							<a class="header-notif-item" href="index.php?<?php echo htmlspecialchars(http_build_query(['page' => 'clients', 'view' => 'clients', 'filter' => 'low-credit', 'stats_client_id' => $notifClientId, 'quota_threshold' => $headerLowQuotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>">
								<strong><?php echo htmlspecialchars($notifClientName, ENT_QUOTES, 'UTF-8'); ?></strong>
								<span><?php echo $notifRemaining; ?> invitation(s) restante(s)</span>
							</a>
							<?php }
							} else { ?>
							<div class="header-notif-empty">Aucune alerte quota faible pour le moment.</div>
							<?php } ?>
						</div>
						<div class="header-notif-foot">
							<a href="index.php?page=clients&filter=low-credit&view=clients&quota_threshold=<?php echo (int) $headerLowQuotaThreshold; ?>">Voir tous les clients a risque</a>
						</div>
					</div>
				</li>
				<?php } ?>

				<li class="btn-group nav-item d-xl-inline-flex d-none">
					<a href="#" class="waves-effect waves-light nav-link btn-primary-light svg-bt-icon" title="" id="live-chat">
					<i class="fas fa-envelope" style="color: white;"></i>
					</a>
				</li>
 

				<li class="btn-group nav-item d-xl-inline-flex d-none">
					<a href="#" data-provide="fullscreen" class="waves-effect waves-light nav-link btn-primary-light svg-bt-icon" title="Full Screen">
					<i class="fas fa-expand" style="color: white;"></i>
					</a>
				</li>

				<!-- User Account-->
				<!-- <li class="dropdown user user-menu">
					<a href="#" class="waves-effect waves-light dropdown-toggle w-auto l-h-12 bg-transparent p-0 no-shadow" title="User" data-bs-toggle="modal" data-bs-target="#quick_user_toggle">
						<img src="../images/default.jpg" class="avatar rounded-circle bg-primary-light h-40 w-40" alt="">
					</a>
				</li>	
				 -->
				


<script>
    function confirmLogout(event) {
        event.preventDefault(); // Empêche le lien de se déclencher
        Swal.fire({
            title: "Déconnexion !",
            text: "Êtes-vous sûr de vouloir vous déconnecter ?",
            icon: "warning", // Utilisez "warning" pour une alerte de confirmation
            showCancelButton: true,
            confirmButtonText: "Oui",
            cancelButtonText: "Non"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "index.php?page=logout";
            }
        });
    }
</script>

				<!-- deconnexion -->
			<li class="dropdown notifications-menu btn-group">
				<a href="#" class="waves-effect waves-light dropdown-toggle" title="Déconnexion" onclick="confirmLogout(event)">
					<i class="fas fa-sign-out-alt" style="color: white;"></i>
				</a>
			</li>



			    <!-- Control Sidebar Toggle Button -->
			    <li class="btn-group nav-item d-xl-inline-flex d-none">
				  <a href="#" data-toggle="control-sidebar" title="Setting" class="waves-effect waves-light nav-link btn-primary-light svg-bt-icon me-0">
				  <i class="fas fa-arrow-right" style="color: white;"></i>
				  </a>
			    </li>

			</ul>
		  </div>
		</nav>
	  </div>
  </header>
  
  <nav class="main-nav" role="navigation">

	  <!-- Mobile menu toggle button (hamburger/x icon) -->
	  <input id="main-menu-state" type="checkbox" />
	  <label class="main-menu-btn" for="main-menu-state">
		<span class="main-menu-btn-icon"></span> Toggle main menu visibility
	  </label>

	  <!-- Sample menu definition -->
	  <ul id="main-menu" class="sm sm-blue">		
		<li><a href="index.php?page=admin_accueil"><i data-feather="home"><span class="path1"></span><span class="path2"></span></i>Accueil</a> 
		</li>  
		<?php if ($isImpersonating) { ?>
		<li>
			<form action="" method="post" style="padding: 12px 18px;">
				<input type="hidden" name="stop_impersonation" value="1">
				<button type="submit" style="background:none;border:0;padding:0;color:inherit;">
					<i data-feather="shield"></i>Retour admin
				</button>
			</form>
		</li>
		<?php } ?>
		<li><a href="#"><i data-feather="calendar"></i>Evénements</a> 
		</li>
		<li><a href="#"><i data-feather="shopping-cart"></i>Commandes</a> 
		</li>

		<li><a href="index.php?page=admin_catalogue"><i data-feather="sliders"></i>Catalogue</a>
		</li>
		<?php if ($typeUser === '1') { ?>
		<li><a href="index.php?page=nettoyage"><i data-feather="trash-2"></i>Nettoyage</a>
		</li>
		<?php } ?>
		<li><a href="index.php?page=admin_promos"><i data-feather="percent"></i>Codes promo</a>
		</li>

<?php 
	if ($typeUser !== '3') {
       ?>

		<li><a href="index.php?page=factures"><i data-feather="arrow-down"></i>Entrées</a> 
		</li>

		<li><a href="index.php?page=sorties"><i data-feather="arrow-up"></i>Sorties</a></li>

		<li><a href="index.php?page=dashboard_finance"><i data-feather="arrow-up"></i>Finances</a></li>


		<li><a href="index.php?page=clients"><i data-feather="user"></i>Clients</a> 
		</li>

		<?php if ($typeUser === '1') { ?>
		<li><a href="index.php?page=messages_clients"><i data-feather="message-circle"></i>Messages clients</a>
		</li>
		<?php } ?>

		<li><a href="index.php?page=apprenants"><i data-feather="users"></i>Apprenants</a> 
		</li>
	
		<?php 
		
	} 
?>
		  
	  </ul>
	</nav>




