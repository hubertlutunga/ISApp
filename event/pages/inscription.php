
<div class="container h-p100" style="margin-top:20px;">
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
				<div class="row justify-content-center g-0">
					<div class="col-lg-5 col-md-5 col-12 boxcontent">
						<div class="bg-white rounded10 shadow-lg">
							<div class="content-top-agile p-20 pb-0"> 
                                <img src="../images/Logo_invitationSpeciale_1.png">





<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registrationResult = UserAccountService::registerCustomer($pdo, [
        'noms' => $_POST['noms'] ?? null,
        'phone' => $_POST['phone'] ?? null,
        'email' => $_POST['email'] ?? null,
        'password' => $_POST['password'] ?? null,
        'confirm_password' => $_POST['confirm_password'] ?? null,
        'type_user' => '2',
    ]);

    if (!empty($registrationResult['success'])) {
        echo '<script src="../sweet/sweetalert2.all.min.js"></script>';
        echo '<script>
                Swal.fire({
                title: "Compte cree",
                text: "Votre compte est cree avec succes. Votre identifiant a ete envoye par email.",
                icon: "success",
                confirmButtonText: "Terminer"
                }).then((result) => {
                     if (result.isConfirmed) {
                        window.location.href = "index.php?page=login";
                       }
                });
        </script>';
    } else {
        echo "<span style='color:red;'>" . htmlspecialchars((string) ($registrationResult['message'] ?? ''), ENT_QUOTES, 'UTF-8') . "</span>";
    }
}
?>


 <style>
    input,
    textarea,
    select {
        font-size: 16px !important; /* Empêche le zoom sur iOS */
    }
</style>




								<p class="mb-0 text-fade">Créer votre compte</p>							
							</div>
							<div class="p-40">
<form action="" method="post">
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="noms" value="<?php echo @$_POST['noms']?>" class="form-control ps-15 bg-transparent" placeholder="Noms" required>
        </div>
    </div>
    
    <div class="form-group">
    <div class="input-group mb-3">
        <span class="input-group-text bg-transparent"><i class="fas fa-phone"></i></span>
        <input type="text" name="phone" value="<?php echo @$_POST['phone'] ?>" 
       class="form-control ps-15 bg-transparent" placeholder="Téléphone" >
    </div>
    </div>

    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-envelope"></i></span>
            <input type="email" name="email"  value="<?php echo @$_POST['email']?>" class="form-control ps-15 bg-transparent" placeholder="Email" required>
        </div>
    </div>
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-lock"></i></span>
            <input type="password" autocomplete="off" name="password"  value="<?php echo @$_POST['password']?>" class="form-control ps-15 bg-transparent" placeholder="Mot de passe" required>
        </div>
    </div>
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-lock"></i></span>
            <input type="password" autocomplete="off" name="confirm_password" value="<?php echo @$_POST['confirm_password']?>" class="form-control ps-15 bg-transparent" placeholder="Confirmer mot de passe" required>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="checkbox">
                <input type="checkbox" id="basic_checkbox_1" required>
                <label for="basic_checkbox_1">J'accepte les <a href="index.php?page=termes_conditions" target="_blank" rel="noopener noreferrer" class="text-primary">termes et conditions</a></label>
            </div>
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary w-p100 mt-10">Créer</button>
        </div>
    </div>



</form>		











<!---------------- script pour imposer d'accepter les condition avant de soumettre --------------->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const checkbox = document.getElementById("basic_checkbox_1");
    const submitButton = document.querySelector("button[type='submit']");

    // Désactiver le bouton au chargement
    submitButton.disabled = true;

    // Écouter les changements sur la case à cocher
    checkbox.addEventListener("change", function() {
        submitButton.disabled = !checkbox.checked; // Activer/désactiver le bouton
    });
});
</script>






								<div class="text-center">
									<p class="mt-15 mb-0 text-fade">Vous avez déjà un compte ?<a href="index.php?page=login" class="text-primary ms-5">Se Connecter</a></p>
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
