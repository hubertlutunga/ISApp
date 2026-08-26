
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

    .login-progress-shell {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: rgba(15, 23, 42, 0.12);
        opacity: 0;
        visibility: hidden;
        z-index: 9999;
        transition: opacity 0.2s ease;
    }

    .login-progress-shell.is-active {
        opacity: 1;
        visibility: visible;
    }

    .login-progress-fill {
        width: 0;
        height: 100%;
        background: linear-gradient(90deg, #1a73e8 0%, #4dabff 55%, #7ec8ff 100%);
        box-shadow: 0 0 12px rgba(26, 115, 232, 0.6);
        transition: width 0.18s ease;
    }

    .login-progress-toast {
        position: fixed;
        top: 14px;
        right: 14px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.88);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.01em;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        transition: opacity 0.2s ease, transform 0.2s ease;
        z-index: 9999;
    }

    .login-progress-shell.is-active + .login-progress-toast {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
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

    <div class="login-progress-shell" id="loginProgressShell" aria-hidden="true">
        <div class="login-progress-fill" id="loginProgressFill"></div>
    </div>
    <p class="login-progress-toast" id="loginProgressToast" role="status" aria-live="polite">Connexion... 0%</p>


	<!-- Vendor JS -->
    <script src="users/html/template/horizontal/src/js/vendors.min.js"></script>
    <script src="users/html/template/horizontal/src/js/pages/chat-popup.js"></script>
    <script src="users/assets/icons/feather-icons/feather.min.js"></script>	
    <script>
    (function () {
        var form = document.getElementById('loginForm');
        var progressShell = document.getElementById('loginProgressShell');
        var progressFill = document.getElementById('loginProgressFill');
        var progressToast = document.getElementById('loginProgressToast');
        var submitButton = document.getElementById('loginSubmitBtn');
        var progressTimer = null;
        var progressValue = 0;

        if (!form || !progressShell || !progressFill || !progressToast || !submitButton) {
            return;
        }

        var updateProgress = function (value) {
            progressValue = Math.max(0, Math.min(100, value));
            progressFill.style.width = progressValue + '%';
            progressToast.textContent = 'Connexion... ' + progressValue + '%';
        };

        var startProgress = function () {
            progressShell.classList.add('is-active');
            progressShell.setAttribute('aria-hidden', 'false');
            updateProgress(12);

            progressTimer = window.setInterval(function () {
                if (progressValue >= 92) {
                    return;
                }

                var step = progressValue < 55 ? 8 : 3;
                updateProgress(progressValue + step);
            }, 170);
        };

        form.addEventListener('submit', function () {
            if (!form.checkValidity()) {
                return;
            }

            startProgress();
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.textContent = 'Connexion en cours...';
        });

        window.addEventListener('beforeunload', function () {
            if (!progressShell.classList.contains('is-active')) {
                return;
            }

            updateProgress(100);
            if (progressTimer !== null) {
                window.clearInterval(progressTimer);
            }
        });
    })();
    </script>
	
	
</body>
</html>
