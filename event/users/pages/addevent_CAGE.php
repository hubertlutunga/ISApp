
		
<div class="wrapper"> 
	 

     <?php include('header.php');?>
      
    

        $currentUserId = (int) (($datasession['cod_user'] ?? ($_SESSION['cod_user'] ?? 0)) ?: 0);

        try {
            EventCreationService::createManagedEvent(
                $pdo,
                [
                    'cod_user' => $currentUserId > 0 ? $currentUserId : null,
                    'type_event' => $_POST['event'] ?? null,
                    'type_mar' => $_POST['weddingType'] ?? null,
                    'modele_inv' => $_POST['modele_inv'] ?? null,
                    'modele_chev' => $_POST['chevaletModel'] ?? null,
                    'date_event' => $_POST['dateHeure'] ?? null,
                    'lieu' => $_POST['lieu'] ?? null,
                    'adresse' => $_POST['adresse'] ?? null,
                    'prenom_epoux' => $_POST['prenomEpoux'] ?? null,
                    'nom_epoux' => $_POST['nomEpoux'] ?? null,
                    'prenom_epouse' => $_POST['prenomEpouse'] ?? null,
                    'nom_epouse' => $_POST['nomEpouse'] ?? null,
                    'nom_familleepoux' => $_POST['nomFamilleEpoux'] ?? null,
                    'nom_familleepouse' => $_POST['nomFamilleEpouse'] ?? null,
                    'nomfetard' => $_POST['nomsfetard'] ?? null,
                    'themeconf' => $_POST['themeConf'] ?? null,
                    'autres_precisions' => $_POST['details'] ?? null,
                    'initiale_mar' => EventUpdateService::buildInitialeFromRequest($_POST),
                ],
                $_POST['accessoires'] ?? [],
                $_FILES['photos'] ?? null,
                '../photosevent',
                $isAppConfig
            );

            error_log('Données reçues : ' . print_r($_POST, true));
        } catch (PDOException $e) {
            echo "Erreur lors de l'enregistrement : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return;
        }








 








	//-------------------------------------------------


    error_log('Données reçues : ' . print_r($_POST, true)); // Pour débogage
}

?>




<!-- barre de progression -->
<style>
.centered {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* Prend toute la hauteur de la fenêtre */
}
</style>

<!-- Conteneur principal -->
<div id="progressWrapper" class="centered" style="display: none;">

    <!-- barre de progression -->
    <div id="progressContainer" style="width: 100%; max-width: 600px; background: #f3f3f3; border: 1px solid #ccc; text-align: center;">
        <div id="progressBar" style="width: 0; height: 30px; background: #4caf50; display: inline-block;"></div>
        <span id="progressPercentage" style="display: block; margin-top: 5px;">Téléchargement des photos : 0%</span>
    </div>
    
    <div id="status" style="margin-top: 10px; text-align: center;"></div>
</div>

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
                <option value="Prédot">Prédot</option>
            </select>
        </div>
    </div>


        
    <!-- <fieldset class="border p-3 hidden" id="AccessoireGroup"> -->
    <fieldset class="border p-3">
    <i class="fas fa-shopping-cart" style="margin-left:-5px;margin-right:25px;"></i><label class="labaccessoire;">Que commandez-vous ?</label> <br><br>
        <div class="form-group">
            <?php 
            $reqmod = $pdo->prepare("SELECT * FROM modele_is where type_mod = :type_mod ORDER by cod_mod ASC");
            $reqmod->execute([':type_mod' => 'accessoires']);  
            while ($data_mod = $reqmod->fetch()) {
            ?>
            <div class="checkbox" style="margin-bottom:10px;margin-left:-5px;">
                <input type="checkbox" id="<?php echo $data_mod['cod_mod']?>" name="accessoires[]" value="<?php echo $data_mod['cod_mod']?>" class="text-primary" onchange="toggleFields()">
                <label for="<?php echo $data_mod['cod_mod']?>"><?php echo $data_mod['nom']?></label>
            </div>
            <?php } ?> 
        </div>
    </fieldset>



    <div class="form-group" id="ModInvitation" style="display:none;margin-top:20px;">
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
                        <img class="option-image" src="../images/modeleis/<?php echo $data_mod['image']?>" alt="Religieux">  
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

    <div class="form-group" id="ModChevalet" style="display: none;">
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



	
<!-- 	 
	<div class="input-group date chmpdate" style="margin-top:15px;">
		                     
			<div class="input-group mb-3 date">   
			<span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
			<input type="text" class="form-control pull-right" id="datepicker">
			</div> 
							 

	</div> -->
    

    
	
	<div class="input-group date chmpdate" style="margin-top:15px;">
		<div class="input-group mb-3">
			<span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
			<input type="datetime-local" name="dateHeure" class="form-control ps-15 bg-transparent" id="datepicker">
			<label class="placeholder-label">Date et heure</label>
		</div>
	</div>
	
 
<script>
$(document).ready(function() {
    $('#datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function(e) {
        // Optionnel : mettre à jour l'input avec le format souhaité
        const time = '15:30'; // Mettez l'heure souhaitée ici
        $(this).val(e.format() + ' ' + time);
    });
});
</script>
<!-- 

    <div class="form-group">
			<label class="form-label">Date</label>

			<div class="input-group date">
					<div class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</div> 
                    
			    <input type="datetime-local" name="dateHeure" class="form-control pull-right ps-15 bg-transparent" id="datepicker" required>
			</div> 
	</div> -->




<style>
 

    .sallexx:focus {
    outline: none;
    box-shadow: none;
    border: none; /* désactiver le border */
    resize: none !important;
    }

    input,
    textarea,
    select {
        font-size: 16px !important; /* Empêche le zoom sur iOS */
    }

</style>

    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-map-marker-alt"></i></span>
            <input type="text" name="lieu" class="form-control ps-15 bg-transparent sallexx" placeholder="Salle / Espace">
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
            <input type="text" name="nomsfetard" class="form-control ps-15 bg-transparent" placeholder="Noms du fetard">
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
            <button type="submit" id="BtnEvent" class="btn btn-primary w-p100 mt-10">Créer</button>
        </div>
    </div>
</form>
	






























<!---------------- script pour afficher les modèles d'invitation ou chevalet si coché --------------->
<script>
function toggleFields() {
    const checkboxes = document.querySelectorAll('input[name="accessoires[]"]');
    let showInvitation = false;
    let showChevalet = false;

    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            if (checkbox.value == 1) {
                showInvitation = true;
            } else if (checkbox.value == 3) {
                showChevalet = true;
            }
        }
    });

    document.getElementById('ModInvitation').style.display = showInvitation ? 'block' : 'none';
    document.getElementById('ModChevalet').style.display = showChevalet ? 'block' : 'none';
}
</script>

<!---------------- script de la barre de progression --------------->
<script>
document.getElementById('eventForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche le rechargement de la page

    var typeEvent = document.getElementById('eventType').value; // Récupère la valeur du select
    if (!typeEvent) {
        alert("Veuillez sélectionner le type de l'événement."); // Message d'erreur
        return; // Empêche l'envoi du formulaire
    }

    // Vérification des conditions basées sur le type d'événement
    
    // Vérification des conditions basées sur le type d'événement
    if (typeEvent === '1') {
        var typeMar = document.getElementById('weddingType').value;
        var dateHeure = document.getElementById('datepicker').value; // Assurez-vous que l'ID est correct
        var lieu = document.getElementsByName('lieu')[0].value;
        var adresse = document.getElementsByName('adresse')[0].value;
        var prenomEpoux = document.getElementsByName('prenomEpoux')[0].value;
        var prenomEpouse = document.getElementsByName('prenomEpouse')[0].value;

        if (!typeMar) {
            alert("Veuillez sélectionner le type du mariage.");
            return;
        }
        if (!dateHeure) {
            alert("Veuillez entrer la date et l'heure du mariage.");
            return;
        }
        if (!lieu) {
            alert("Veuillez entrer le lieu du mariage.");
            return;
        }
        if (!adresse) {
            alert("Veuillez entrer l'adresse du mariage.");
            return;
        }
        if (!prenomEpoux) {
            alert("Veuillez entrer le prénom de l'époux.");
            return;
        }
        if (!prenomEpouse) {
            alert("Veuillez entrer le prénom de l'épouse.");
            return;
        }
    } else if (typeEvent === '2') {
        var dateHeure = document.getElementById('datepicker').value;
        var lieu = document.getElementsByName('lieu')[0].value;
        var adresse = document.getElementsByName('adresse')[0].value;
        var nomsAnniv = document.getElementsByName('nomsfetard')[0].value;

        if (!dateHeure) {
            alert("Veuillez entrer la date et l'heure.");
            return;
        }
        if (!lieu) {
            alert("Veuillez entrer le lieu.");
            return;
        }
        if (!adresse) {
            alert("Veuillez entrer l'adresse.");
            return;
        }
        if (!nomsAnniv) {
            alert("Veuillez entrer le nom de celui ou celle qui fête son anniversaire.");
            return;
        }
    } else if (typeEvent === '3') {
        var dateHeure = document.getElementById('datepicker').value;
        var lieu = document.getElementsByName('lieu')[0].value;
        var adresse = document.getElementsByName('adresse')[0].value;
        var themeConf = document.getElementsByName('themeConf')[0].value;

        if (!dateHeure) {
            alert("Veuillez entrer la date et l'heure.");
            return;
        }
        if (!lieu) {
            alert("Veuillez entrer le lieu.");
            return;
        }
        if (!adresse) {
            alert("Veuillez entrer l'adresse.");
            return;
        }
        if (!themeConf) {
            alert("Veuillez entrer le thème de la conférence.");
            return;
        }
    }


    // Masquer le formulaire et afficher la barre de progression
    this.style.display = 'none'; // Masque le formulaire
    var progressWrapper = document.getElementById('progressWrapper');
    progressWrapper.style.display = 'flex'; // Affiche le conteneur de la barre de progression
    progressWrapper.classList.add('centered'); // Ajoute la classe pour le centrage

    // Initialisation de la barre de progression
    document.getElementById('progressContainer').style.display = 'block'; // Affiche le conteneur de la barre de progression
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
                    window.location.href = "index.php?page=mb_accueil"; // Redirection
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
    const submitButton = document.getElementById("BtnEvent"); // Utilisation de l'ID BtnEvent

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





 
							</div>
						</div>	
					</div>
				</div>
			</div>			
		</div>
	</div>

    
			</div>			
		</div>
	</div>

 
  <!-- /.content-wrapper -->
  <?php include('footer.php')?>
  <!-- Side panel --> 
  <!-- quick_user_toggle -->
	
 
	
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
	<!-- Vendor JS -->
	<script src="html/template/horizontal/src/js/vendors.min.js"></script>
	<script src="html/template/horizontal/src/js/pages/chat-popup.js"></script>
  	<script src="../../../assets/icons/feather-icons/feather.min.js"></script>
  	<script src="../../../assets/vendor_components/Flot/jquery.flot.js"></script>
	<script src="../../../assets/vendor_components/Flot/jquery.flot.resize.js"></script>
	<script src="../../../assets/vendor_components/Flot/jquery.flot.pie.js"></script>
	<script src="../../../assets/vendor_components/Flot/jquery.flot.categories.js"></script>
	<script src="../../../assets/vendor_components/echarts/dist/echarts-en.min.js"></script>
	<script src="../../../assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
	<script src="../../../assets/vendor_plugins/bootstrap-slider/bootstrap-slider.js"></script>
	<script src="../../../assets/vendor_components/OwlCarousel2/dist/owl.carousel.js"></script>
	<script src="../../../assets/vendor_components/flexslider/jquery.flexslider.js"></script>
	<script src="../assets/vendor_components/Web-Ticker-master/jquery.webticker.min.js"></script>
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
	 
	  
	<!-- Cartiy Admin App -->     
	<script src="html/template/horizontal/src/js/pages/advanced-form-element.js"></script>