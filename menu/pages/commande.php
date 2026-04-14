
	<div class="container h-p100" style="margin-top:20px;">
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
				<div class="row justify-content-center g-0">
					<div class="col-lg-5 col-md-5 col-12 boxcontent">
						<div class="bg-white rounded10 shadow-lg ">
							<div class="content-top-agile p-20 pb-0"> 
                                <img src="../images/Logo_invitationSpeciale_SF.png">
								<p class="mb-0 text-fade">Créer votre événements</p>							
							</div>
							<div class="p-40">



<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_event = $_POST['event'] ?? null;
    $nomsAnniv = $_POST['nomsAnniv'] ?? null;
    $prenomEpoux = $_POST['prenomEpoux'] ?? null;
    $prenomEpouse = $_POST['prenomEpouse'] ?? null;
    $initialemar = substr((string) $prenomEpouse, 0, 1) . '&' . substr((string) $prenomEpoux, 0, 1);

    try {
        $cod_event = EventCreationService::createLegacyEvent(
            $pdo,
            [
                'type_event' => $type_event,
                'type_mar' => $_POST['weddingType'] ?? null,
                'modele_inv' => $_POST['modele_inv'] ?? null,
                'modele_chev' => $_POST['chevaletModel'] ?? null,
                'date_event' => $_POST['dateHeure'] ?? null,
                'lieu' => $_POST['lieu'] ?? null,
                'adresse' => $_POST['adresse'] ?? null,
                'prenom_epoux' => $prenomEpoux,
                'nom_epoux' => $_POST['nomEpoux'] ?? null,
                'prenom_epouse' => $prenomEpouse,
                'nom_epouse' => $_POST['nomEpouse'] ?? null,
                'nom_familleepoux' => $_POST['nomFamilleEpoux'] ?? null,
                'nom_familleepouse' => $_POST['nomFamilleEpouse'] ?? null,
                'nomfetard' => $nomsAnniv,
                'themeconf' => $_POST['themeConf'] ?? null,
                'autres_precisions' => $_POST['details'] ?? null,
                'initiale_mar' => $initialemar,
            ],
            $_POST['accessoires'] ?? [],
            $_FILES['photos'] ?? null,
            'photosevent',
            $isAppConfig
        );

        error_log('Données reçues : ' . print_r($_POST, true));
    } catch (PDOException $e) {
        echo "<span style='color:red;'>Erreur lors de l'enregistrement : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</span>";
    }
}

?>



<!-- barre de progression -->

<div id="progressContainer" style="width: 100%; background: #f3f3f3; border: 1px solid #ccc; display: none; margin-top: 10px; margin-bottom: 50px;">
    <div id="progressBar" style="width: 0; height: 30px; background: #4caf50;"></div>
    <span id="progressPercentage" style="display: block; text-align: center; margin-top: 5px;">Téléchargement des photos : 0%</span>
</div>
<div id="status" style="margin-top: 10px;"></div>

<!-- fin barre -->




<form id="eventForm" action="" method="post" enctype="multipart/form-data">




 




    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-calendar-alt"></i></span>
            <select class="form-control ps-15 bg-transparent" name="event" id="eventType">
                <option style="color:#eee;" value="">Type d'événement</option>
                <?php 
                $reqevent = $pdo->prepare("SELECT * FROM evenement ORDER by cod_event ASC");
                $reqevent->execute();  
                while ($data_event = $reqevent->fetch()) {
                ?>
                <option value="<?php echo $data_event['cod_event']?>" <?php if(@$_POST['event'] == $data_event['cod_event']){echo "selected";} ?>><?php echo $data_event['nom']?></option>
                <?php } ?>  
            </select>
        </div>
    </div>

    <div class="form-group hidden" id="weddingTypeGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-ring"></i></span>
            <select class="form-control ps-15 bg-transparent" name="weddingType" id="weddingType">
                <option style="color:#eee;" value="">Type de mariage</option>
                <option value="religieux">Religieux</option>
                <option value="coutumier">Coutumier</option>
                <option value="civil">Civil</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group mb-3 champmod" id="dropdownToggle">
            <span class="input-group-text bg-transparent spanmod"><i class="fas fa-ring"></i></span>
            <div class="selected-option" style="margin-left:15px;margin-top:6px;">Modèle d'invitation</div>
        </div>
    </div>

    <div id="myModal" class="modal">
        <div class="modal-content">
            <span id="closeModal" style="cursor:pointer; float:right;">&times;</span>
            <h2>Sélectionnez un modèle</h2>
            <div class="dropdown-content" id="weddingTypeDropdown">
			<input type="hidden" name="modele_inv" id="modeleInv" value="">
                <?php 
                $reqmod = $pdo->prepare("SELECT * FROM modele_is where type_mod = :type_mod ORDER by nom ASC");
                $reqmod->execute([':type_mod' => 'invitation']);  
                while ($data_mod = $reqmod->fetch()) {
                ?>
                <div data-value="<?php echo $data_mod['cod_mod']?>">
                    <label><?php echo $data_mod['nom']?>
                        <img class="option-image" src="images/<?php echo $data_mod['image']?>" alt="Religieux">  
                    </label>
                </div>
                <?php } ?>  
            </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const dropdownItems = document.querySelectorAll("#weddingTypeDropdown div");
    const modeleInvInput = document.getElementById("modeleInv");

    dropdownItems.forEach(item => {
        item.addEventListener("click", function() {
            const value = this.getAttribute("data-value");
            modeleInvInput.value = value; // Met à jour le champ caché
        });
    });
});
</script>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-gift"></i></span>
            <select class="form-control ps-15 bg-transparent" name="chevaletModel">
                <option style="color:#eee;" value="">Modèle de chevalet de table</option>
                <?php 
                $reqmod = $pdo->prepare("SELECT * FROM modele_is where type_mod = :type_mod ORDER by nom ASC");
                $reqmod->execute([':type_mod' => 'chevalet']);  
                while ($data_mod = $reqmod->fetch()) {
                ?>
                <option value="<?php echo $data_mod['cod_mod']?>" <?php if(@$_POST['chevaletModel'] == $data_mod['cod_mod']){echo "selected";} ?>><?php echo $data_mod['nom']?></option>
                <?php } ?>  
            </select>
        </div>
    </div>

    <fieldset class="border p-3 hidden" id="AccessoireGroup">
        <legend style="width:80px;">Accessoires</legend>
        <div class="form-group">
			<?php 
			$reqmod = $pdo->prepare("SELECT * FROM modele_is where type_mod = :type_mod ORDER by nom ASC");
			$reqmod->execute([':type_mod' => 'accessoires']);  
			while ($data_mod = $reqmod->fetch()) {
			?>
			<div class="checkbox" style="margin-bottom:10px;">
				<input type="checkbox" id="<?php echo $data_mod['cod_mod']?>" name="accessoires[]" value="<?php echo $data_mod['cod_mod']?>" class="text-primary">
				<label for="<?php echo $data_mod['cod_mod']?>"><?php echo $data_mod['nom']?></label>
			</div>
			<?php } ?> 
        </div>
    </fieldset>


	
	 

	
	<div class="form-group chmpdate" style="margin-top:15px;">
		<div class="input-group mb-3">
			<span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
			<input type="datetime-local" name="dateHeure" class="form-control ps-15 bg-transparent" required>
			<label class="placeholder-label">Date et heure</label>
		</div>
	</div>
	
 



    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-map-marker-alt"></i></span>
            <input type="text" name="lieu" class="form-control ps-15 bg-transparent" placeholder="Lieu">
        </div>
    </div>
    
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-map"></i></span>
            <input type="text" name="adresse" class="form-control ps-15 bg-transparent" placeholder="Adresse">
        </div>
    </div>

    <div class="form-group hidden" id="NomsAnniv">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="nomsAnniv" class="form-control ps-15 bg-transparent" placeholder="Noms du fetard">
        </div>
    </div>
    
    <div class="form-group hidden" id="ThemeConf">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-comments"></i></span>
            <input type="text" name="themeConf" class="form-control ps-15 bg-transparent" placeholder="Thème de conférence">
        </div>
    </div>

    <div class="form-group hidden" id="NomepouxGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="prenomEpoux" class="form-control ps-15 bg-transparent" placeholder="Prénom de l'époux">
        </div>
    </div>

    <div class="form-group hidden" id="PrenomepouxGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="nomEpoux" class="form-control ps-15 bg-transparent" placeholder="Nom de l'époux">
        </div>
    </div>

    <div class="form-group hidden" id="NomepouseGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="prenomEpouse" class="form-control ps-15 bg-transparent" placeholder="Prénom de l'épouse">
        </div>
    </div>

    <div class="form-group hidden" id="PrenomepouseGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="nomEpouse" class="form-control ps-15 bg-transparent" placeholder="Nom de l'épouse">
        </div>
    </div>

    <div class="form-group hidden" id="NomfamilleepouxGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="nomFamilleEpoux" class="form-control ps-15 bg-transparent" placeholder="Nom de la Famille de l'époux">
        </div>
    </div>

    <div class="form-group hidden" id="NomfamilleepouseGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="nomFamilleEpouse" class="form-control ps-15 bg-transparent" placeholder="Nom de la Famille de l'épouse">
        </div>
    </div>

    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span> 
            <textarea name="details" class="form-control ps-15 bg-transparent" rows='5' placeholder="Autres précisions"></textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group mb-3">
            <label for="fileInput" class="btnpic"><i class="fas fa-plus"></i> Importer les photos</label>
            <input type="file" name="photos[]" class="form-control ps-15 bg-transparent" accept="image/*" id="fileInput" multiple style="display: none;">
            <div id="previewContainer" class="mt-2" style="display: flex; flex-wrap: wrap;"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="checkbox">
                <input type="checkbox" id="basic_checkbox_1">
                <label for="basic_checkbox_1">J'accepte les <a href="#" class="text-primary">termes et conditions</a></label>
            </div>
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary w-p100 mt-10">Créer</button>
        </div>
    </div>
</form>
								


<!---------------- script de la barre de progression --------------->
<script src="../sweet/sweetalert2.all.min.js"></script>
<script> 
document.getElementById('eventForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche le rechargement de la page

    // Masquer le formulaire et afficher la barre de progression
    this.style.display = 'none'; // Masque le formulaire
    document.getElementById('progressContainer').style.display = 'block'; // Affiche la barre de progression
    document.getElementById('progressBar').style.width = '0%'; // Réinitialise la barre

    var formData = new FormData(this); // Récupère les données du formulaire
    var xhr = new XMLHttpRequest();
    
    xhr.open('POST', '', true); // Envoie à la même page

    xhr.upload.onprogress = function(event) {
        if (event.lengthComputable) {
            var percentComplete = (event.loaded / event.total) * 100;
            document.getElementById('progressBar').style.width = percentComplete + '%';
            document.getElementById('progressPercentage').textContent = 'Téléchargement des photos : ' + Math.round(percentComplete) + '%'; // Met à jour le texte
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            // Affiche SweetAlert après que la barre soit à 100%
            Swal.fire({
                title: "Evénement créé !",
                text: "Votre événement est ajouté avec succès.",
                icon: "success",
                confirmButtonText: "Terminer"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "index.php?page=commande"; // Redirection
                }
            });
        } else {
            document.getElementById('status').innerHTML = 'Erreur lors du traitement.';
        }
    };

    xhr.send(formData); // Envoi des données du formulaire
}); 
</script>

<!---------------- fin script --------------->


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

 
<!---------------- script pour la liste des modeles d'invitations --------------->

<script>
        const dropdownToggle = document.getElementById('dropdownToggle');
        const modal = document.getElementById('myModal');
        const closeModal = document.getElementById('closeModal');
        const dropdownContent = document.getElementById('weddingTypeDropdown');
        const selectedOption = document.querySelector('.selected-option');

        dropdownToggle.addEventListener('click', function() {
            modal.style.display = 'block';
        });

        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        dropdownContent.addEventListener('click', function(event) {
            const value = event.target.closest('div').dataset.value;
            if (value) {
                selectedOption.textContent = event.target.closest('div').textContent;
                modal.style.display = 'none'; // Masquer la modale après sélection
                console.log(value); // Affiche la valeur sélectionnée
            }
        });

        // Fermer la modale si l'utilisateur clique en dehors de celle-ci
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
</script>




<!---------------- script pour masquer les champs --------------->



<script>

const eventTypeSelect = document.getElementById('eventType');
const weddingTypeGroup = document.getElementById('weddingTypeGroup');
const NomepouxGroup = document.getElementById('NomepouxGroup');
const PrenomepouxGroup = document.getElementById('PrenomepouxGroup');
const NomepouseGroup = document.getElementById('NomepouseGroup');
const PrenomepouseGroup = document.getElementById('PrenomepouseGroup'); 
const AccessoireGroup = document.getElementById('AccessoireGroup');
const NomfamilleepouseGroup = document.getElementById('NomfamilleepouseGroup');
const NomfamilleepouxGroup = document.getElementById('NomfamilleepouxGroup');
const NomsAnniv = document.getElementById('NomsAnniv');
const ThemeConf = document.getElementById('ThemeConf');

eventTypeSelect.addEventListener('change', function() {
    if (this.value === '1') { // Assurez-vous que '1' est la valeur pour "Mariage"
        weddingTypeGroup.classList.remove('hidden');
        NomepouxGroup.classList.remove('hidden');
        PrenomepouxGroup.classList.remove('hidden');
        NomepouseGroup.classList.remove('hidden');
        PrenomepouseGroup.classList.remove('hidden'); 
        AccessoireGroup.classList.remove('hidden');
        NomfamilleepouseGroup.classList.remove('hidden');
        NomfamilleepouxGroup.classList.remove('hidden');
        ThemeConf.classList.add('hidden'); // Masquer pour le mariage
        NomsAnniv.classList.add('hidden'); // Masquer pour le mariage
    } else if (this.value === '2') { // Assurez-vous que '2' est la valeur pour "Anniversaire"
        NomsAnniv.classList.remove('hidden'); 
        ThemeConf.classList.add('hidden'); 
        weddingTypeGroup.classList.add('hidden');
        NomepouxGroup.classList.add('hidden');
        PrenomepouxGroup.classList.add('hidden');
        NomepouseGroup.classList.add('hidden');
        PrenomepouseGroup.classList.add('hidden'); 
        AccessoireGroup.classList.add('hidden');
        NomfamilleepouseGroup.classList.add('hidden');
        NomfamilleepouxGroup.classList.add('hidden');
    } else if (this.value === '3') { // Assurez-vous que '2' est la valeur pour "Anniversaire"   
        ThemeConf.classList.remove('hidden'); 
		NomsAnniv.classList.add('hidden'); 
        weddingTypeGroup.classList.add('hidden');
        NomepouxGroup.classList.add('hidden');
        PrenomepouxGroup.classList.add('hidden');
        NomepouseGroup.classList.add('hidden');
        PrenomepouseGroup.classList.add('hidden'); 
        AccessoireGroup.classList.add('hidden');
        NomfamilleepouseGroup.classList.add('hidden');
        NomfamilleepouxGroup.classList.add('hidden');
    } else {
        NomsAnniv.classList.add('hidden');
        ThemeConf.classList.add('hidden');
        weddingTypeGroup.classList.add('hidden');
        NomepouxGroup.classList.add('hidden');
        PrenomepouxGroup.classList.add('hidden');
        NomepouseGroup.classList.add('hidden');
        PrenomepouseGroup.classList.add('hidden'); 
        AccessoireGroup.classList.add('hidden');
        NomfamilleepouseGroup.classList.add('hidden');
        NomfamilleepouxGroup.classList.add('hidden');
    }
});

//--------------------------------JS IMAGES--------------------------------------


    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');

    fileInput.addEventListener('change', function(event) {
        const files = Array.from(event.target.files);
        
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.classList.add('image-container');

                const img = document.createElement('img');
                img.src = e.target.result;

                const deleteIcon = document.createElement('button');
                deleteIcon.innerHTML = '✖'; // Icône de suppression
                deleteIcon.classList.add('delete-icon');

                deleteIcon.addEventListener('click', function() {
                    imgContainer.remove(); // Supprime l'aperçu
                });

                imgContainer.appendChild(img);
                imgContainer.appendChild(deleteIcon);
                previewContainer.appendChild(imgContainer);
            };
            reader.readAsDataURL(file);
        });
    });
</script>










								<div class="text-center">
									<p class="mt-15 mb-0 text-fade">Vous avez déjà un compte ?<a href="auth_login.html" class="text-primary ms-5">Se Connecter</a></p>
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
	<script src="src/js/vendors.min.js"></script>
	<script src="src/js/pages/chat-popup.js"></script>
    <script src="assets/icons/feather-icons/feather.min.js"></script>	
	
 