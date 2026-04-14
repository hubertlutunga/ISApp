
		<div class="container h-p100" style="margin-top:20px;">
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
				<div class="row justify-content-center g-0">
					<div class="col-lg-5 col-md-5 col-12 boxcontent">
						<div class="bg-white rounded10 shadow-lg">
							<div class="content-top-agile p-20 pb-0"> 
                                <img src="../images/Logo_invitationSpeciale_SF.png">





<?php
$datasession = UserAccountService::currentSessionUser($pdo);
if ($datasession) {
    header('Location: ' . UserAccountService::dashboardUrl($datasession, $isAppConfig));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginResult = UserAccountService::authenticate(
        $pdo,
        $_POST['identifiant'] ?? null,
        $_POST['password'] ?? null
    );

    if (!empty($loginResult['success'])) {
        header('Location: ' . UserAccountService::dashboardUrl($loginResult['user'], $isAppConfig));
        exit();
    }

    echo "<span style='color:red;'>" . htmlspecialchars((string) ($loginResult['message'] ?? 'Identifiant ou mot de passe incorrect.'), ENT_QUOTES, 'UTF-8') . "</span>";
}
?>







								<p class="mb-0 text-fade">Connexion</p>							
							</div>
							<div class="p-40">
  <form action="" method="post">
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
    <div class="row">
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary w-p100 mt-10">Se Connecter</button>
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


	<!-- Vendor JS -->
	<script src="users/src/js/vendors.min.js"></script>
	<script src="users/src/js/pages/chat-popup.js"></script>
    <script src="users/assets/icons/feather-icons/feather.min.js"></script>	
	
	
</body>
</html>
