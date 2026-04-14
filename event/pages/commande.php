
<?php
$feedbackMessage = null;
$feedbackType = 'error';
?>

<div class="container h-p100 commande-page-shell" style="margin-top:20px;">
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
				<div class="row justify-content-center g-0">
					<div class="col-lg-5 col-md-5 col-12 boxcontent">
                        <div class="bg-white rounded10 shadow-lg commande-card">
                            <div class="content-top-agile p-20 pb-0 commande-card-header"> 
                                <img src="../images/Logo_invitationSpeciale_SF.png" class="commande-logo" alt="Invitation Speciale">





<?php

$datasession = UserAccountService::currentSessionUser($pdo);
if ($datasession) {
    header('Location: ' . UserAccountService::dashboardUrl($datasession, $isAppConfig));
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inclure le fichier de connexion à la base de données 

    // Récupérer les données du formulaire
	 
    $radio = $_POST['choix'] ?? null; 



    if ($radio === "nouveau_client") {
   





    $registrationResult = UserAccountService::registerCustomer($pdo, [
        'noms' => $_POST['noms'] ?? null,
        'phone' => $_POST['phone'] ?? null,
        'email' => $_POST['email'] ?? null,
        'password' => $_POST['password_insc'] ?? null,
        'confirm_password' => $_POST['confirm_password'] ?? null,
        'type_user' => '2',
    ]);

    if (!empty($registrationResult['success'])) {
        $_SESSION['user_phone'] = $registrationResult['user']['phone'];
        $_SESSION['user_email'] = $registrationResult['user']['email'];

        header('Location: ' . rtrim((string) $isAppConfig['base_url'], '/') . '/event/users/index.php?page=addevent');
        exit();
    }

    $feedbackMessage = (string) ($registrationResult['message'] ?? '');
    $feedbackType = 'error';

        












    }else{
        include('scriptconnexion.php');
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

<style>
    .commande-page-shell {
        padding-bottom: 32px;
    }

    .commande-card {
        border: 0;
        border-radius: 28px;
        overflow: hidden;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.12);
    }

    .commande-card-header {
        padding: 28px 32px 12px;
        text-align: center;
        background:
            radial-gradient(circle at top left, rgba(14, 165, 233, 0.18), transparent 45%),
            radial-gradient(circle at top right, rgba(6, 149, 120, 0.16), transparent 42%),
            linear-gradient(135deg, #eff6ff 0%, #ffffff 55%, #f0fdf4 100%);
    }

    .commande-logo {
        width: clamp(150px, 42vw, 220px);
        max-width: 100%;
    }

    .commande-kicker {
        margin: 18px 0 8px;
        font-size: 12px;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #0891b2;
        font-weight: 800;
    }

    .commande-title {
        margin: 0;
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1.08;
        color: #0f172a;
        font-weight: 900;
    }

    .commande-subtitle {
        max-width: 460px;
        margin: 14px auto 0;
        color: #475569;
        font-size: 15px;
        line-height: 1.7;
    }

    .commande-card-body {
        padding: 28px 32px 36px;
    }

    .commande-section-caption {
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 700;
        color: #64748b;
        text-align: center;
    }

    .commande-feedback {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.6;
        border: 1px solid transparent;
    }

    .commande-feedback.error {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .option-container {
        border: 1px solid #dbe4f0; 
        border-radius : 20px;
        padding: 16px 18px;
        margin-bottom: 14px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        cursor: pointer;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        font-family: Arial, sans-serif;
        width: 100%;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .option-container.selected {
        background: linear-gradient(135deg, #0891b2 0%, #06a578 100%);
        color: white;
        border-color: transparent !important;
        box-shadow: 0 18px 38px rgba(6, 149, 120, 0.26);
        transform: translateY(-1px);
    }

    .radio-custom {
        width: 24px;
        min-width: 24px;
        height: 24px;
        border: 2px solid #0f172a;
        border-radius: 50%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
    }

    .radio-custom::after {
        content: '';
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #0f172a;
        display: none;
    }

    .option-container.selected .radio-custom {
        border-color: rgba(255,255,255,.9);
    }

    .option-container.selected .radio-custom::after {
        display: block;
        background-color: white;
    }

    .description {
        display: block;
        margin-top: 5px;
        font-size: 13px;
        line-height: 1.6;
        color: #64748b;
    }

    .option-container.selected .description {
        color: rgba(255,255,255,.84);
    }

    input[type="radio"] {
        display: none;
    }

    label {
        font-size: 18px;
        line-height: 1.3;
        font-weight: 800;
        margin: 0;
    }

    .btn.disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    h4{
        text-align:center;
        margin-top:4px;
        margin-bottom:18px;
        color: #0f172a;
        font-size: 22px;
        font-weight: 800;
    }

    #submit-button{
        height:54px !important;
        border:none !important;
        border-radius: 16px;
        background: linear-gradient(135deg, #0f172a 0%, #0891b2 100%);
        font-size: 15px;
        font-weight: 800;
        box-shadow: 0 18px 32px rgba(8, 145, 178, 0.22);
    }

    .commande-panel {
        padding: 20px 20px 4px;
        margin: 8px 0 14px;
        border-radius: 22px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .commande-panel .input-group-text {
        border-radius: 14px 0 0 14px;
        border-color: #dbe4f0;
        color: #0891b2;
        min-width: 48px;
        justify-content: center;
    }

    .commande-panel .form-control {
        border-color: #dbe4f0;
        border-radius: 0 14px 14px 0;
        min-height: 48px;
        color: #0f172a;
    }

    .commande-panel .form-control:focus {
        border-color: #7dd3fc;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.14);
    }

    .commande-links {
        text-align: center;
        padding-top: 8px;
    }

    .commande-links p {
        color: #64748b;
    }

    .commande-links a {
        color: #0891b2;
        font-weight: 700;
    }

    @media only screen and (max-width: 767px) {
        .commande-card-header,
        .commande-card-body {
            padding-left: 20px;
            padding-right: 20px;
        }

        .option-container {
            padding: 14px;
        }

        label {
            font-size: 16px;
        }
    }

</style>




                    <p class="commande-kicker">Commande et accès client</p>
                    <h1 class="commande-title">Commencez votre commande en quelques instants</h1>
                    <p class="commande-subtitle">Connectez-vous si vous avez deja un compte, ou creez-en un pour acceder a votre espace evenement et poursuivre votre commande sans interruption.</p>
							</div>
							<div class="p-40 commande-card-body">

<?php if (!empty($feedbackMessage)) { ?>
<div class="commande-feedback <?php echo htmlspecialchars($feedbackType, ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php } ?>

<p class="commande-section-caption">Choisissez votre parcours pour continuer.</p>

<form action="" method="post"> 
    <div class="input-group mb-3">
        <div class="option-container" onclick="selectOption(0)">
            <div class="radio-custom"></div>
            <input type="radio" name="choix" value="ancien_client" id="client">
            <div>
                <label for="client">Je suis deja client</label><br>
                <span class="description">Je possède déjà un compte</span>
            </div>
        </div> 
    </div> 

    <div id="ancien_client" class="commande-panel" style="display: none;">

    
    <h4>Connectez - vous</h4>

        <div class="form-group">
            <div class="input-group mb-3">
                <span class="input-group-text bg-transparent"><i class="fas fa-envelope"></i></span>
                <input type="text" name="identifiant" class="form-control ps-15 bg-transparent" 
                    placeholder="Téléphone ou Email">
            </div>
        </div>
        <div class="form-group">
            <div class="input-group mb-3">
                <span class="input-group-text bg-transparent"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control ps-15 bg-transparent" 
                    placeholder="Mot de passe">
            </div>
        </div> 
    </div>

    <div class="input-group mb-3">
        <div class="option-container" onclick="selectOption(1)">
            <div class="radio-custom"></div>
            <input type="radio" name="choix"  value="nouveau_client" id="pas-client">
            <div>
                <label for="pas-client">Je suis un nouveau client</label><br>
                <span class="description">Je ne possède pas un compte</span>
            </div>
        </div>
    </div> 

    <div class="new_client commande-panel" style="display: none;">

    <h4>Inscrivez - vous</h4>


        <div class="form-group">
            <div class="input-group mb-3">
                <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
                <input type="text" name="noms" value="<?php echo @$_POST['noms']?>" class="form-control ps-15 bg-transparent" placeholder="Noms">
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
                <input type="email" name="email"  value="<?php echo @$_POST['email']?>" class="form-control ps-15 bg-transparent" placeholder="Email" >
            </div>
        </div>
        <div class="form-group">
            <div class="input-group mb-3">
                <span class="input-group-text bg-transparent"><i class="fas fa-lock"></i></span>
                <input type="password" autocomplete="off" name="password_insc"  value="<?php echo @$_POST['password_insc']?>" class="form-control ps-15 bg-transparent" placeholder="Créer un mot de passe" >
            </div>
        </div>
        <div class="form-group">
            <div class="input-group mb-3">
                <span class="input-group-text bg-transparent"><i class="fas fa-lock"></i></span>
                <input type="password" autocomplete="off" name="confirm_password" value="<?php echo @$_POST['confirm_password']?>" class="form-control ps-15 bg-transparent" placeholder="Confirmer mot de passe" >
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group mb-3">
            <div class="input-group mb-3">
                <button type="submit" name="submit" class="btn btn-primary w-p100 mt-10" id="submit-button" disabled>Suivant <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
</form>

<script>
    const options = document.querySelectorAll('.option-container');
    const submitButton = document.getElementById('submit-button');
    const ancienClientDiv = document.getElementById('ancien_client');
    const newClientDiv = document.querySelector('.new_client');

    function selectOption(index) {
        options.forEach((option, i) => {
            const input = option.querySelector('input');
            if (i === index) {
                option.classList.add('selected');
                input.checked = true;
            } else {
                option.classList.remove('selected');
                input.checked = false;
            }
        });

        toggleSubmitButton();
        toggleClientDivs(index);
    }

    function toggleSubmitButton() {
        const isAnyOptionSelected = Array.from(options).some(option => option.classList.contains('selected'));
        submitButton.disabled = !isAnyOptionSelected;
        submitButton.classList.toggle('disabled', !isAnyOptionSelected);
    }

    function toggleClientDivs(index) {
        if (index === 0) { // Si "Je suis un client" est sélectionné
            ancienClientDiv.style.display = 'block';
            newClientDiv.style.display = 'none';
        } else if (index === 1) { // Si "Nouveau Client" est sélectionné
            ancienClientDiv.style.display = 'none';
            newClientDiv.style.display = 'block';
        }
    }


     // Ajout pour maintenir l'état après soumission
    document.addEventListener('DOMContentLoaded', function() {
        const choix = "<?php echo isset($_POST['choix']) ? $_POST['choix'] : ''; ?>";
        
        options.forEach((option, index) => {
            const input = option.querySelector('input');
            if (input.value === choix) {
                option.classList.add('selected');
                input.checked = true;
                selectOption(index); // Affiche le bon formulaire
            } else {
                option.classList.remove('selected');
            }
        });

        toggleSubmitButton(); // Met à jour l'état du bouton de soumission
    });
</script>
 








 





                                <div class="commande-links">
									<p class="mt-15 mb-0 text-fade">Vous avez déjà un compte ?<a href="index.php?page=login" class="text-primary ms-5">Se Connecter</a></p>
								</div>
								
                                <div class="commande-links">
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
