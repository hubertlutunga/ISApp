 
<?php 

	$stmtss = $pdo->prepare("SELECT * FROM is_users WHERE phone = ?");
	$stmtss->execute([$_SESSION['user_phone']]);
	$datasession = $stmtss->fetch();

	if (!isset($_SESSION['user_phone'])) {
		header("Location: index.php?page=logout");
		exit();
	}

	$eventContext = EventWorkspaceService::resolveCurrentEventContext($pdo, $datasession ?: [], $_GET);
	extract($eventContext, EXTR_OVERWRITE);

?>

  <header class="main-header">
	  <div class="inside-header">
		<div class="d-flex align-items-center logo-box justify-content-start">
			<a href="index.php?page=mb_accueil" class="logo">
			  <div class="logo-lg">
				  <span class="light-logo"><img src="../images/Logo_invitationSpeciale_4.png" width="300px" alt="logo"></span>
				  <span class="dark-logo"><img src="../images/Logo_invitationSpeciale_4.png" width="300px" alt="logo"></span>
			  </div>
			</a>
		</div>

		<nav class="navbar navbar-static-top">
		  <div class="app-menu">
			<ul class="header-megamenu nav">
				<li class="btn-group d-md-inline-flex d-none"></li>
			</ul>
		  </div>

		  <div class="navbar-custom-menu r-side">
			<ul class="nav navbar-nav phonemenupv">

			<li class="dropdown notifications-menu btn-group">
				<a href="index.php?page=mb_accueil" class="waves-effect waves-light dropdown-toggle" title="Accueil">
					<i class="fas fa-home" style="color: white;"></i>
				</a>
			</li>


			<li class="dropdown notifications-menu btn-group">
				<a href="index.php?page=events" class="waves-effect waves-light dropdown-toggle" title="Accueil">
					<i class="fas fa-calendar" style="color: white;"></i>
				</a>
			</li>

 

				<li class="dropdown notifications-menu btn-group">
					<label class="switch">
					  	<a  class="waves-effect waves-light btn-primary-light svg-bt-icon">
							<input type="checkbox" data-mainsidebarskin="toggle" id="toggle_left_sidebar_skin">
							<span class="switch-on"><i class="fas fa-moon" style="color: white;"></i></span>
							<span class="switch-off"><i class="fas fa-sun" style="color: white;"></i></span>
						</a>	
					</label>
	        	</li> 
<!--
				<li class="dropdown notifications-menu btn-group">
				<a href="#" class="waves-effect waves-light btn-primary-light svg-bt-icon" data-bs-toggle="dropdown" title="Notifications">
					<i class="fas fa-bell" style="color: white;"></i>
					<div class="pulse-wave"></div>
				</a>
					<ul class="dropdown-menu animated bounceIn">
					  <li class="header">
						<div class="p-20">
							<div class="flexbox">
								<div>
									<h4 class="mb-0 mt-0">Notifications</h4>
								</div>
								<div>
									<a href="#" class="text-danger">Vider</a>
								</div>
							</div>
						</div>
					  </li>
					  <li> 
						<ul class="menu sm-scrol">
						  <li>
							<a href="#" class="listenot">
							  <i class="fa fa-users text-info"></i> Hubert Lutunga - J'y serai.
							</a>
						  </li>   
						</ul>
					  </li>
					  <li class="footer">
						  <a href="#" class="listenot">Voir toutes</a>
					  </li>
					</ul>
				</li>

-->

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
		<li><a href="index.php?page=mb_accueil"><i data-feather="home"><span class="path1"></span><span class="path2"></span></i>Accueil</a> 
		</li>  
		<li><a href="index.php?page=addinvite"><i data-feather="shopping-cart"></i>Invités</a> 
		</li>
		<li><a href="index.php?page=addtable"><i data-feather="shopping-cart"></i>Tables</a> 
		</li>
		  
		<li><a href="#"><i data-feather="calendar"></i>Evénements</a> 
		</li>
		<li><a href="#"><i data-feather="shopping-cart"></i>Commandes</a> 
		</li>
	  </ul>
	</nav>




