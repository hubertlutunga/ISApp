
<div class="container h-p100" style="margin-top:20px;">
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
				<div class="row justify-content-center g-0">
					<div class="col-lg-5 col-md-5 col-12 boxcontent">
						<div class="bg-white rounded10 shadow-lg">
							<div class="content-top-agile p-20 pb-0"> 
                                <img src="../images/Logo_invitationSpeciale_1.png">





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

    echo "<span style='color:red;'>" . htmlspecialchars((string) ($registrationResult['message'] ?? ''), ENT_QUOTES, 'UTF-8') . "</span>";

        












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
    .option-container {
        border: 1px solid #000; 
        border-radius :  8px;
        padding: 10px 15px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: background-color 0.3s;
        font-family: Arial, sans-serif;
        width: 100%;
    }

    .option-container.selected {
        background-color: #06a578ff;
        color: white;
        border:none !important;
    }

    .radio-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #000;
        border-radius: 50%;
        margin-right: 10px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .radio-custom::after {
        content: '';
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #aaa;
        display: none;
    }

    .option-container.selected .radio-custom::after {
        display: block;
        background-color: white;
    }

    .description {
        font-size: 1em;
        opacity: 0.8;
    }

    input[type="radio"] {
        display: none;
    }

    label {
        font-size: 1.5em;
    }

    .btn.disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    h4{
        text-align:center;
        margin-top:-10px;
        margin-bottom:15px;
    }

    #submit-button{
        height:45px !important;
        border:none !important;
    }

</style>




                    <p class="mb-0 text-fade">Identification du client</p>							
							</div>
							<div class="p-40">

<form action="" method="post"> 
    <div class="input-group mb-3">
        <div class="option-container" onclick="selectOption(0)">
            <div class="radio-custom"></div>
            <input type="radio" name="choix" value="ancien_client" id="client">
            <div>
                <label for="client">Jesuis déjà un client</label><br>
                <span class="description">Je possède déjà un compte</span>
            </div>
        </div> 
    </div> 

    <div id="ancien_client" style="display: none;">

    
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

    <div class="new_client" style="display: none;">

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
