
		<div class="container h-p100" style="margin-top:20px;">
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
				<div class="row justify-content-center g-0">
					<div class="col-lg-5 col-md-5 col-12 boxcontent">
						<div class="bg-white rounded10 shadow-lg">
							<div class="content-top-agile p-20 pb-0"> 
                                <img src="../images/Logo_invitationSpeciale_1.png">





<?php

if (isset($_SESSION['user_phone'])) {

	$stmtss = $pdo->prepare("SELECT * FROM is_users WHERE phone = ?");
	$stmtss->execute([$_SESSION['user_phone']]);
	$datasession = $stmtss->fetch();

    if ($datasession['type_user'] == '1') {
        header("Location: users/index.php?page=admin_accueil"); 
    }elseif ($datasession['type_user'] == '2'){
        header("Location: users/index.php?page=mb_accueil"); 
    }elseif ($datasession['type_user'] == '3'){
        header("Location: users/index.php?page=crea_accueil"); 
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    
  include('scriptconnexion.php');

}
?>



 <style>
    input,
    textarea,
    select {
        font-size: 16px !important; /* Empêche le zoom sur iOS */
    }

    .login-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(8, 15, 34, 0.65);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease;
        z-index: 9999;
    }

    .login-loading-overlay.is-active {
        opacity: 1;
        visibility: visible;
    }

    .login-loading-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px 24px;
        min-width: 240px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 18px 40px rgba(2, 6, 23, 0.25);
    }

    .login-loading-spinner {
        width: 24px;
        height: 24px;
        border: 3px solid #dbe4ff;
        border-top-color: #1a73e8;
        border-radius: 50%;
        animation: login-spin 0.8s linear infinite;
    }

    .login-loading-text {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    @keyframes login-spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>



								<p class="mb-0 text-fade">Connexion</p>							
							</div>
							<div class="p-40">
  <form action="" method="post" id="loginForm">
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-envelope"></i></span>
            <input type="text" name="identifiant" class="form-control ps-15 bg-transparent" 
                   placeholder="Téléphone ou Email" required>
        </div>
    </div>
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" class="form-control ps-15 bg-transparent" 
                   placeholder="Mot de passe" required>
        </div>
    </div> 
    <div class="text-end mb-15">
        <a href="index.php?page=forgot_password" class="text-primary">Mot de passe oublié ?</a>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary w-p100 mt-10" id="loginSubmitBtn">Se Connecter</button>
        </div>
    </div>
</form>









 





								<div class="text-center">
									<p class="mt-15 mb-0 text-fade">Pas de compte ?<a href="index.php?page=inscription" class="text-primary ms-5">S'inscrire </a></p>
								</div>
								
								<div class="text-center">
								  <p class="mt-20 text-fade">- Nos réseaux -</p>
								  <p class="gap-items-2 mb-0">
								  <a class="waves-effect waves-circle btn btn-social-icon btn-circle btn-twitter-light" href="#"><i class="fab fa-tiktok"></i></a>
								  <a class="waves-effect waves-circle btn btn-social-icon btn-circle btn-instagram-light" href="#"><i class="fab fa-instagram"></i></a>
									  <a class="waves-effect waves-circle btn btn-social-icon btn-circle btn-facebook-light" href="#"><i class="fab fa-facebook"></i></a>
									</p>	
								</div>
							</div>
						</div>	
					</div>
				</div>
			</div>			
		</div>
	</div>

    <div class="login-loading-overlay" id="loginLoadingOverlay" aria-hidden="true">
        <div class="login-loading-card" role="status" aria-live="polite">
            <span class="login-loading-spinner" aria-hidden="true"></span>
            <p class="login-loading-text">Connexion en cours...</p>
        </div>
    </div>


	<!-- Vendor JS -->
    <script src="users/html/template/horizontal/src/js/vendors.min.js"></script>
    <script src="users/html/template/horizontal/src/js/pages/chat-popup.js"></script>
    <script src="users/assets/icons/feather-icons/feather.min.js"></script>	
    <script>
    (function () {
        var form = document.getElementById('loginForm');
        var overlay = document.getElementById('loginLoadingOverlay');
        var submitButton = document.getElementById('loginSubmitBtn');

        if (!form || !overlay || !submitButton) {
            return;
        }

        form.addEventListener('submit', function () {
            if (!form.checkValidity()) {
                return;
            }

            overlay.classList.add('is-active');
            overlay.setAttribute('aria-hidden', 'false');
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.textContent = 'Connexion...';
        });
    })();
    </script>
	
	
</body>
</html>
