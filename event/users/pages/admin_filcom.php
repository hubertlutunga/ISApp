
	
<div class="wrapper"> 
	 

   <?php 
   
         include('header_admin.php');
 

 
 
     
         include('../../qrscan/phpqrcode/qrlib.php');
     

 ?>
    
  
 
   <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
       <div class="container-full">
       <!-- Main content -->
 
   <!-- 
 
     <div class="content-header text-center">
       <div class="d-flex align-items-center">
         <div class="me-auto">
           <h3 class="page-title">Weather widgets</h3>
           <div class="d-inline-block align-items-center">
             <nav>
               <ol class="breadcrumb">
                 <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                 <li class="breadcrumb-item" aria-current="page">Widgets</li>
                 <li class="breadcrumb-item active" aria-current="page">Weather widgets</li>
               </ol>
             </nav>
           </div>
         </div>
         
       </div>
     </div> -->
 
 
 
 
 
 
 
 
 
 
 
 
 
 
         
 <div class="row salut">
 
 <?php 
 
 $heure = date('H');
 
 if ($heure < 12) {
 $salut = 'Bonjour';
 }elseif ($heure > 11 AND $heure < 15){
 $salut = 'Bon Après-midi';
 }elseif ($heure > 12){
 $salut = 'Bonsoir';
 }
 
 extract(AdminDashboardStatsService::build($pdo, $datasession ?? []), EXTR_OVERWRITE);
 
 ?>
 
 <p style="text-align:center;">
   <?php  // echo "La valeur de codevent est : " . $codevent; 
   echo $salut;?> <b>
   <?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, "UTF-8");?> </b>!
 </p>
 
 
 
   
 </div>
 
 
  
 

 
 <?php

    // Formater pour afficher deux décimales
    $fintotaldigital = number_format((float)$fintotaldigital, 2, '.', '');





     if ($datasession['type_user'] == '3') {
              $finance = "display:none;"; 
          } else { 
              $finance = "";  
     }
 ?>
       



 <section class="content">
        
    <div class="row" style="<?php echo $finance; ?>">
        <div class="col-12">
            <div class="box rounded-4">
                <div class="box-header d-flex b-0 justify-content-between align-items-center pb-0">
                    <h4 class="box-title">Finances </h4>
                    <ul class="m-0" style="list-style: none;">
                        <li class="dropdown">
                            <input class="input-switch" id="switch" type="checkbox" name="switch" />
                            <label for="switch">
                                <span class="grip"></span>
                                <span class="switch-label off">Masquer</span>
                                <span class="switch-label on">Afficher</span>
                            </label>
                        </li>
                    </ul>
                </div>
                <div class="box-body pt-0 summery-box">
                    <p class="mb-20 text-fade">Total des commandes</p>
                    <div id="vueFinance" style="display: none;"> <!-- Masqué par défaut -->
                    <div class="row" id="vueFinance"> <!-- Masqué par défaut -->



                        <div class="col-lg-3 col-md-6">
                            <div class="box pull-up mb-0 bg-info-light">
                                <div class="box-body " style="position:relative;">

                                    <div class="w-50 h-50 bg-info rounded-circle text-center"> 
                                        <i class="fa fa-area-chart fs-18 l-h-50"></i> <em style="position:absolute;right:20px;color:#ddd;font-size:20px;"><?php echo "Aujourd'hui";?></em> 
                                    </div>

                                    <h2 class="fw-600 mt-3"><?php echo $finjourmp . ' $';?></h2> 
                                    <span class="mb-2"><?php echo 'Digital - '.$finjourdigital.' $';?></span><br><?php echo 'Reste '.$restjour. ' $';?>
                                    <p class="mb-0 text-primary"><?php echo $comjour.' / '.$comtermjour;?></p>

                                </div>
                            </div>
                        </div>
                        

                        
                        <div class="col-lg-3 col-md-6">
                            <div class="box pull-up mb-sm-0 bg-warning-light">
                                <div class="box-body " style="position:relative;">
                                    <div class="w-50 h-50 bg-warning rounded-circle text-center"> 
                                        <i class="fa fa-area-chart fs-18 l-h-50"></i> <em style="position:absolute;right:20px;color:#ddd;font-size:20px;"><?php echo "Ce mois"?></em> 
                                    </div>
                                    <h2 class="fw-600 mt-3"><?php echo $finmoismp . ' $';?></h2>  
                                    <span class="mb-2"><?php echo 'Digital - '.$finmoisdigital.' $';?></span><br><?php echo 'Reste '.$restmois. ' $';?>
                                    <p class="mb-0 text-primary"><?php echo $commois.' / '.$comtermmois;?></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-3 col-md-6">
                            <div class="box pull-up mb-sm-0 bg-danger-light">
                                <div class="box-body " style="position:relative;">
                                    <div class="w-50 h-50 bg-danger rounded-circle text-center "> 
                                        <i class="fa fa-area-chart fs-18 l-h-50"></i> <em style="position:absolute;right:20px;color:#ddd;font-size:20px;"><?php echo date('Y')?></em> 
                                    </div>
                                    <h2 class="fw-600 mt-3"><?php echo $finanneemp . ' $';?></h2> 
                                    <span class="mb-2"><?php echo 'Digital - '.$finanneedigital.' $';?></span><br><?php echo 'Reste '.$restannee. ' $';?>
                                    <p class="mb-0 text-primary"><?php echo $comannee.' / '.$comtermannee;?></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-3 col-md-6">
                            <div class="box pull-up mb-0 bg-danger-light">
                                <div class="box-body ">
                                    <div class="w-50 h-50 bg-info rounded-circle text-center"> 
                                        <i class="fa fa-area-chart fs-18 l-h-50"></i> <em style="position:absolute;right:20px;color:#ddd;font-size:20px;"><?php echo "Total";?></em> 
                                    </div>

                                    <h2 class="fw-600 mt-3"><?php echo $fintotalmp . ' $';?></h2> 
                                    <span class="mb-2"><?php echo 'Digital - '.$fintotaldigital.' $';?></span><br><?php echo 'Reste '.$resttotal. ' $';?>
                                    <p class="mb-0 text-primary"><?php echo $comtotal.' / '.$comtermtotal;?></p>
                                </div>
                            </div>
                        </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.getElementById('switch').addEventListener('change', function () {
        const vueFinance = document.getElementById('vueFinance');
        if (this.checked) {
            vueFinance.style.display = 'block'; // Afficher
        } else {
            vueFinance.style.display = 'none'; // Masquer
        }
    });
</script>

<style>
    .input-switch + label {
        position: relative;
        display: inline-block;
        height: 25px;
        width: 110px;
        border: none;
        border-radius: 15px;
        background-color: #666;
        line-height: 25px;
        color: #fff;
        text-align: left;
        transition: background-color 0.5s;
    }
    .input-switch {
        display: none;
    }
    .input-switch + label span.grip {
        position: absolute;
        top: 0;
        left: 0;
        display: inline-block;
        width: 25px;
        height: 25px;
        background-color: #EEE;
        border-radius: 18px;
        transition: left 0.5s;
    }
    .input-switch + label span.switch-label {
        position: absolute;
        display: inline-block;
        top: 0;
        left: 17px;
        width: 90px;
        overflow: hidden;
        text-align: center;
        transition: opacity 0.5s;
    }
    .input-switch + label span.switch-label.on {
        left: 2px;
        opacity: 0;
    }
    .input-switch:checked + label {
        background-color: #17720A;
    }
    .input-switch:checked + label span.grip {
        left: 84px;
    }
    .input-switch:checked + label span.switch-label {
        opacity: 0;
    }
    .input-switch:checked + label span.switch-label.on {
        opacity: 1;
    }

    .theme-primary [type=checkbox] + label:before {
    border: none !important; 
    }

 
</style>






   
 
 
  
         
 
 
 
 
 
 
 
  
  
 
 
 
 <?php include('menustat.php')?>



 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
  <?php
 
          if ($_GET['page'] === 'admin_filcom') {
                                if ($_GET['type'] === 'npaye') { 
                                    $titlelis = "Les commandes non payées";
                                } elseif ($_GET['type'] === 'enattente') {
                                     $titlelis = "Les commandes en attentes";
                                } elseif ($_GET['type'] === 'realise') {  
                                    $titlelis = "Les commandes réalisées";
                                }
                            } else {
                           

                                    $titlelis = "Les commandes";
                            }

 
 ?>
 
  
 
 <div class="row" id='mesinv'>
     <div class="col-xxl-12 col-xl-12 col-lg-12">
         <div class="card rounded-4">
 
             <div class="box-header d-flex b-0 justify-content-between align-items-center">
                 <h4 class="box-title"><?php echo $titlelis;?></h4>
                 <ul class="m-0" style="list-style: none;">
                     <li class="dropdown">
                         <a target="_blank" href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm">
                             <i class="fa fa-fw fa-arrow-down"></i> Obtenir le PDF
                         </a>
                     </li>
                 </ul>
             </div>



             
 
             <div class="card-body pt-0">
                 <div class="table-responsive">
                     <table class="table mb-0">
                         <tbody>
                        
                                    <img id="loader" src="../images/Loading_icon.gif" width="10%" alt="Loading..." style="display: none;">
                           
                         
                                    <div id="content" style="display: none;">
                                        <?php include('eventbloc.php'); ?>
                                    </div>
                           





                            <script>
                                // Afficher le loader lorsque la page commence à se charger
                                document.getElementById('loader').style.display = 'block';

                                // Lorsque le contenu est chargé, masquer le loader et afficher le contenu
                                window.onload = function() {
                                    document.getElementById('loader').style.display = 'none';
                                    document.getElementById('content').style.display = 'block';
                                };
                            </script>
 
                         </tbody>
 

                         
                     </table>


               
                            <div id="pagination-controls" class="text-center" style="margin-top:20px;">
                                    <button id="prevPage" class="btn btn-primary" onclick="changePage(-1)">Précédent</button>
                                    <span id="pageInfo"></span>
                                    <button id="nextPage" class="btn btn-primary" onclick="changePage(1)">Suivant</button>
                            </div>
             
                 </div>
             </div>	
 
 
 
 
 
 
  <script>
    const rowsPerPage = 100; // Set the number of rows per page
    let currentPage = 1;

    const tableRows = document.querySelectorAll('table tbody tr');
    const totalPages = Math.ceil(tableRows.length / rowsPerPage);

    function displayPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        tableRows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });

        document.getElementById('pageInfo').innerText = `Page ${page} sur ${totalPages}`;
        document.getElementById('prevPage').disabled = page === 1;
        document.getElementById('nextPage').disabled = page === totalPages;
    }

    function changePage(direction) {
        currentPage += direction;
        displayPage(currentPage);
    }

    // Initial display
    displayPage(currentPage);
</script>
 
 
         </div>
     </div>
 
 
 
 
     
 
<style>
.modalinv {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 3000;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    width: 400px; /* Ajustez la largeur selon vos besoins */
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.close {
    cursor: pointer;
    font-size: 24px;
}

.close:hover {
    color: #000;
}

.modal-body {
    margin-top: 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
</style>

<?php include('modaleterminer.php')?>
<?php  include('modalecherger.php')?>
<?php  include('modaleinserer.php')?>
 


<script>
//--------------modale 1-------------------------
function openModal(codEvent) {
    document.getElementById('modalTitle').innerText = 'Evénement N° ' + codEvent;
    document.getElementById('codevent').value = codEvent; // Stocker le codEvent dans le champ caché
    document.getElementById('shareModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('shareModal').style.display = 'none';
}



//--------------modale 2-------------------------
function toggleQrCodeFields2(value) {
  const wrapper = document.getElementById('qrCodeFields2');
  const form = document.getElementById('eventForm2');

  if (!wrapper || !form) {
    return;
  }

  const isEnabled = value === 'oui';
  wrapper.style.display = isEnabled ? 'block' : 'none';

  form.querySelectorAll('input[name="pageqr"], input[name="hautqr"], input[name="gaucheqr"], input[name="tailleqr"]').forEach(function(field) {
    field.disabled = !isEnabled;
  });
}

function openModal2(source) {
  const dataset = source && source.dataset ? source.dataset : {};
  const codEvent = dataset.codEvent || source;
  const form = document.getElementById('eventForm2');
  const setValue = (selector, value) => {
    const field = form.querySelector(selector);
    if (field) {
      field.value = value || '';
    }
  };

  document.getElementById('modalTitle2').innerText = 'Inv électronique pour N° ' + codEvent;
  document.getElementById('codevent2').value = codEvent;
  setValue('input[name="fichers[]"]', '');
  setValue('input[name="ajustenom"]', dataset.ajustenom);
  setValue('input[name="taillenominv"]', dataset.taillenominv);
  setValue('select[name="alignnominv"]', dataset.alignnominv || '');
  setValue('input[name="pagenom"]', dataset.pagenom);
  setValue('input[name="pagebouton"]', dataset.pagebouton);
  setValue('input[name="colornom"]', dataset.colornom);
  setValue('input[name="bordgauchenominv"]', dataset.bordgauchenominv);
  setValue('input[name="pageqr"]', dataset.pageqr || '3');
  setValue('input[name="hautqr"]', dataset.hautqr || '18');
  setValue('input[name="gaucheqr"]', dataset.gaucheqr || '52');
  setValue('input[name="tailleqr"]', dataset.tailleqr || '90');
  setValue('select[name="lang"]', dataset.lang || 'fr');

  const qrcodeValue = ((dataset.qrcode || 'non') + '').toLowerCase() === 'oui' ? 'oui' : 'non';
  form.querySelectorAll('input[name="qrcode"]').forEach(function(field) {
    field.checked = field.value === qrcodeValue;
  });
  toggleQrCodeFields2(qrcodeValue);

  const currentInvitation = document.getElementById('currentInvitation2');
  if (currentInvitation) {
    currentInvitation.textContent = dataset.invitReligieux ? 'Fichier actuel : ' + dataset.invitReligieux : 'Aucun fichier actuel';
  }

  document.getElementById('shareModal2').style.display = 'flex';
}

function closeModal2() {
    document.getElementById('shareModal2').style.display = 'none';
}




//--------------modale 3-------------------------
function openModal3(codEvent) {
    document.getElementById('modalTitle3').innerText = 'Evénement N° ' + codEvent;
    document.getElementById('codevent3').value = codEvent; // Stocker le codEvent dans le champ caché
    document.getElementById('shareModal3').style.display = 'flex';
}

function closeModal3() {
    document.getElementById('shareModal3').style.display = 'none';
}





//------------------------Soumission du Formulaire 1-------------------------

document.getElementById('eventForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche le rechargement de la page

    this.style.display = 'none'; // Masque le formulaire
    document.getElementById('progressContainer').style.display = 'block'; // Affiche la barre de progression
    document.getElementById('progressBar').style.width = '0%'; // Réinitialise la barre

    var formData = new FormData(this); // Récupère les données du formulaire
    var xhr = new XMLHttpRequest();
    
    xhr.open('POST', 'pages/traitement_termierstatut.php', true); // URL pour le traitement du formulaire 1

    xhr.upload.onprogress = function(event) {
        if (event.lengthComputable) {
            var percentComplete = (event.loaded / event.total) * 100;
            document.getElementById('progressBar').style.width = percentComplete + '%';
            document.getElementById('progressPercentage').textContent = 'Téléchargement : ' + Math.round(percentComplete) + '%'; // Met à jour le texte
        }
    };

 
  
    xhr.onload = function() {
        if (xhr.status === 200) {
            closeModal();
            Swal.fire({
                title:"Rapport !",
                text: "Statut changé et fichier ajouté avec succès.",
                icon: "success",
                confirmButtonText: "Terminer"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "index.php?page=admin_accueil"; // Redirection
                }
            });
        } else {
            document.getElementById('status').innerHTML = 'Erreur lors du traitement.';
        }
    };



    





    xhr.send(formData); // Envoi des données du formulaire
});






//------------------------Soumission du Formulaire 2-------------------------


 
document.getElementById('eventForm2').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche le rechargement de la page

    this.style.display = 'none'; // Masque le formulaire
    document.getElementById('progressContainer2').style.display = 'block'; // Affiche la barre de progression
    document.getElementById('progressBar2').style.width = '0%'; // Réinitialise la barre

    var formData = new FormData(this); // Récupère les données du formulaire
    var xhr = new XMLHttpRequest();

    xhr.open('POST', 'pages/traitement_chargerinv.php', true); // URL pour le traitement du formulaire 2

    xhr.upload.onprogress = function(event) {
        if (event.lengthComputable) {
            var percentComplete = (event.loaded / event.total) * 100;
            document.getElementById('progressBar2').style.width = percentComplete + '%';
            document.getElementById('progressPercentage2').textContent = 'Téléchargement : ' + Math.round(percentComplete) + '%'; // Met à jour le texte
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            closeModal2();
            Swal.fire({
                title:"Rapport !",
                text: "Invitation electronique chargée avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "index.php?page=admin_accueil"; // Redirection
                }
            });
        } else {
            document.getElementById('status2').innerHTML = 'Erreur lors du traitement.';
        }
    };

    xhr.send(formData); // Envoi des données du formulaire
});


















//------------------------Soumission du Formulaire 2-------------------------


 
document.getElementById('eventForm3').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche le rechargement de la page

    this.style.display = 'none'; // Masque le formulaire
    document.getElementById('progressContainer3').style.display = 'block'; // Affiche la barre de progression
    document.getElementById('progressBar3').style.width = '0%'; // Réinitialise la barre

    var formData = new FormData(this); // Récupère les données du formulaire
    var xhr = new XMLHttpRequest();

    xhr.open('POST', 'pages/traitement_insererphoto.php', true); // URL pour le traitement du formulaire 2

    xhr.upload.onprogress = function(event) {
        if (event.lengthComputable) {
            var percentComplete = (event.loaded / event.total) * 100;
            document.getElementById('progressBar3').style.width = percentComplete + '%';
            document.getElementById('progressPercentage3').textContent = 'Téléchargement : ' + Math.round(percentComplete) + '%'; // Met à jour le texte
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            closeModal3();
            Swal.fire({
                title:"Rapport !",
                text: "Photo inserée avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "index.php?page=admin_accueil"; // Redirection
                }
            });
        } else {
            document.getElementById('status3').innerHTML = 'Erreur lors du traitement.';
        }
    };

    xhr.send(formData); // Envoi des données du formulaire
});
</script>









 
 
 
 
 
 
 
 
 



 
 
 
  
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
</section>
 
 
 
 
 
 
 
 
 
 
 
 
   
           </div>
 
           
         </div> 
       <!-- /.content -->
     </div>
   <!-- /.content-wrapper -->
   <?php include('footer.php')?>
   <!-- Side panel --> 
   <!-- quick_user_toggle -->
   <div class="modal modal-right fade" id="quick_user_toggle" tabindex="-1">
     <div class="modal-dialog">
     <div class="modal-content slim-scroll3">
       <div class="modal-body p-30 bg-white">
       <div class="d-flex align-items-center justify-content-between pb-30">
         <h4 class="m-0">User Profile
         <small class="text-fade fs-12 ms-5">12 messages</small></h4>
         <a href="#" class="btn btn-icon btn-danger-light btn-sm no-shadow" data-bs-dismiss="modal">
           <span class="fa fa-close"></span>
         </a>
       </div>
             <div>
                 <div class="d-flex flex-row">
                     <div class=""><img src="html/images/avatar/avatar-2.png" alt="user" class="rounded bg-danger-light w-150" width="100"></div>
                     <div class="ps-20">
                         <h5 class="mb-0">Nil Yeager</h5>
                         <p class="my-5 text-fade">Web Designer</p>
                         <a href="mailto:dummy@gmail.com"><span class="icon-Mail-notification me-5 text-success"><span class="path1"></span><span class="path2"></span></span> dummy@gmail.com</a>
                         <button class="btn btn-success-light btn-sm mt-5"><i class="ti-plus"></i> Follow</button>
                     </div>
                 </div>
       </div>
               <div class="dropdown-divider my-30"></div>
               <div>
                 <div class="d-flex align-items-center mb-30">
                     <div class="me-15 bg-primary-light h-50 w-50 l-h-60 rounded text-center">
                           <span class="icon-Library fs-24"><span class="path1"></span><span class="path2"></span></span>
                     </div>
                     <div class="d-flex flex-column fw-500">
                         <a href="extra_profile.html" class="text-dark hover-primary mb-1 fs-16">My Profile</a>
                         <span class="text-fade">Account settings and more</span>
                     </div>
                 </div>
                 <div class="d-flex align-items-center mb-30">
                     <div class="me-15 bg-danger-light h-50 w-50 l-h-60 rounded text-center">
                         <span class="icon-Write fs-24"><span class="path1"></span><span class="path2"></span></span>
                     </div>
                     <div class="d-flex flex-column fw-500">
                         <a href="mailbox.html" class="text-dark hover-danger mb-1 fs-16">My Messages</a>
                         <span class="text-fade">Inbox and tasks</span>
                     </div>
                 </div>
                 <div class="d-flex align-items-center mb-30">
                     <div class="me-15 bg-success-light h-50 w-50 l-h-60 rounded text-center">
                         <span class="icon-Group-chat fs-24"><span class="path1"></span><span class="path2"></span></span>
                     </div>
                     <div class="d-flex flex-column fw-500">
                         <a href="setting.html" class="text-dark hover-success mb-1 fs-16">Settings</a>
                         <span class="text-fade">Accout Settings</span>
                     </div>
                 </div>
                 <div class="d-flex align-items-center mb-30">
                     <div class="me-15 bg-info-light h-50 w-50 l-h-60 rounded text-center">
                         <span class="icon-Attachment1 fs-24"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                     </div>
                     <div class="d-flex flex-column fw-500">
                         <a href="extra_taskboard.html" class="text-dark hover-info mb-1 fs-16">Project</a>
                         <span class="text-fade">latest tasks and projects</span>
                     </div>
                 </div>
               </div>
               <div class="dropdown-divider my-30"></div>
               <div>
                 <div class="media-list">
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">10:10</h4>
                       <div class="media-body ps-15 bs-5 rounded border-primary">
                         <p>Morbi quis ex eu arcu auctor sagittis.</p>
                         <span class="text-fade">by Johne</span>
                       </div>
                     </a>
 
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">08:40</h4>
                       <div class="media-body ps-15 bs-5 rounded border-success">
                         <p>Proin iaculis eros non odio ornare efficitur.</p>
                         <span class="text-fade">by Amla</span>
                       </div>
                     </a>
 
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">07:10</h4>
                       <div class="media-body ps-15 bs-5 rounded border-info">
                         <p>In mattis mi ut posuere consectetur.</p>
                         <span class="text-fade">by Josef</span>
                       </div>
                     </a>
 
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">01:15</h4>
                       <div class="media-body ps-15 bs-5 rounded border-danger">
                         <p>Morbi quis ex eu arcu auctor sagittis.</p>
                         <span class="text-fade">by Rima</span>
                       </div>
                     </a>
 
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">23:12</h4>
                       <div class="media-body ps-15 bs-5 rounded border-warning">
                         <p>Morbi quis ex eu arcu auctor sagittis.</p>
                         <span class="text-fade">by Alaxa</span>
                       </div>
                     </a>
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">10:10</h4>
                       <div class="media-body ps-15 bs-5 rounded border-primary">
                         <p>Morbi quis ex eu arcu auctor sagittis.</p>
                         <span class="text-fade">by Johne</span>
                       </div>
                     </a>
 
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">08:40</h4>
                       <div class="media-body ps-15 bs-5 rounded border-success">
                         <p>Proin iaculis eros non odio ornare efficitur.</p>
                         <span class="text-fade">by Amla</span>
                       </div>
                     </a>
 
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">07:10</h4>
                       <div class="media-body ps-15 bs-5 rounded border-info">
                         <p>In mattis mi ut posuere consectetur.</p>
                         <span class="text-fade">by Josef</span>
                       </div>
                     </a>
 
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">01:15</h4>
                       <div class="media-body ps-15 bs-5 rounded border-danger">
                         <p>Morbi quis ex eu arcu auctor sagittis.</p>
                         <span class="text-fade">by Rima</span>
                       </div>
                     </a>
 
                     <a class="media media-single px-0" href="#">
                       <h4 class="w-50 text-gray fw-500">23:12</h4>
                       <div class="media-body ps-15 bs-5 rounded border-warning">
                         <p>Morbi quis ex eu arcu auctor sagittis.</p>
                         <span class="text-fade">by Alaxa</span>
                       </div>
                     </a>
                   </div>
             </div>
       </div>
     </div>
     </div>
   </div>
   <!-- /quick_user_toggle --> 
     
 
   <!-- Control Sidebar -->
   <aside class="control-sidebar">
     
   <div class="rpanel-title"><span class="pull-right btn btn-circle btn-danger" data-toggle="control-sidebar"><i class="ion ion-close text-white" ></i></span> </div>  <!-- Create the tabs -->
     <ul class="nav nav-tabs control-sidebar-tabs">
       <li class="nav-item"><a href="#control-sidebar-home-tab" data-bs-toggle="tab" ><i class="mdi mdi-message-text"></i></a></li>
       <li class="nav-item"><a href="#control-sidebar-settings-tab" data-bs-toggle="tab"><i class="mdi mdi-playlist-check"></i></a></li>
     </ul>
     <!-- Tab panes -->
     <div class="tab-content">
       <!-- Home tab content -->
       <div class="tab-pane" id="control-sidebar-home-tab">
           <div class="flexbox">
       <a href="javascript:void(0)" class="text-grey">
         <i class="ti-more"></i>
       </a>	
       <p>Users</p>
       <a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
       </div>
       <div class="lookup lookup-sm lookup-right d-none d-lg-block">
       <input type="text" name="s" placeholder="Search" class="w-p100">
       </div>
           <div class="media-list media-list-hover mt-20">
       <div class="media py-10 px-0">
         <a class="avatar avatar-lg status-success" href="#">
         <img src="html/images/avatar/1.jpg" alt="...">
         </a>
         <div class="media-body">
         <p class="fs-16">
           <a class="hover-primary" href="#"><strong>Tyler</strong></a>
         </p>
         <p>Praesent tristique diam...</p>
           <span>Just now</span>
         </div>
       </div>
 
       <div class="media py-10 px-0">
         <a class="avatar avatar-lg status-danger" href="#">
         <img src="html/images/avatar/2.jpg" alt="...">
         </a>
         <div class="media-body">
         <p class="fs-16">
           <a class="hover-primary" href="#"><strong>Luke</strong></a>
         </p>
         <p>Cras tempor diam ...</p>
           <span>33 min ago</span>
         </div>
       </div>
 
       <div class="media py-10 px-0">
         <a class="avatar avatar-lg status-warning" href="#">
         <img src="html/images/avatar/3.jpg" alt="...">
         </a>
         <div class="media-body">
         <p class="fs-16">
           <a class="hover-primary" href="#"><strong>Evan</strong></a>
         </p>
         <p>In posuere tortor vel...</p>
           <span>42 min ago</span>
         </div>
       </div>
 
       <div class="media py-10 px-0">
         <a class="avatar avatar-lg status-primary" href="#">
         <img src="html/images/avatar/4.jpg" alt="...">
         </a>
         <div class="media-body">
         <p class="fs-16">
           <a class="hover-primary" href="#"><strong>Evan</strong></a>
         </p>
         <p>In posuere tortor vel...</p>
           <span>42 min ago</span>
         </div>
       </div>			
       
       <div class="media py-10 px-0">
         <a class="avatar avatar-lg status-success" href="#">
         <img src="html/images/avatar/1.jpg" alt="...">
         </a>
         <div class="media-body">
         <p class="fs-16">
           <a class="hover-primary" href="#"><strong>Tyler</strong></a>
         </p>
         <p>Praesent tristique diam...</p>
           <span>Just now</span>
         </div>
       </div>
 
       <div class="media py-10 px-0">
         <a class="avatar avatar-lg status-danger" href="#">
         <img src="html/images/avatar/2.jpg" alt="...">
         </a>
         <div class="media-body">
         <p class="fs-16">
           <a class="hover-primary" href="#"><strong>Luke</strong></a>
         </p>
         <p>Cras tempor diam ...</p>
           <span>33 min ago</span>
         </div>
       </div>
 
       <div class="media py-10 px-0">
         <a class="avatar avatar-lg status-warning" href="#">
         <img src="html/images/avatar/3.jpg" alt="...">
         </a>
         <div class="media-body">
         <p class="fs-16">
           <a class="hover-primary" href="#"><strong>Evan</strong></a>
         </p>
         <p>In posuere tortor vel...</p>
           <span>42 min ago</span>
         </div>
       </div>
 
       <div class="media py-10 px-0">
         <a class="avatar avatar-lg status-primary" href="#">
         <img src="html/images/avatar/4.jpg" alt="...">
         </a>
         <div class="media-body">
         <p class="fs-16">
           <a class="hover-primary" href="#"><strong>Evan</strong></a>
         </p>
         <p>In posuere tortor vel...</p>
           <span>42 min ago</span>
         </div>
       </div>
         
       </div>
 
       </div>
       <!-- /.tab-pane -->
       <!-- Settings tab content -->
       <div class="tab-pane" id="control-sidebar-settings-tab">
           <div class="flexbox">
       <a href="javascript:void(0)" class="text-grey">
         <i class="ti-more"></i>
       </a>	
       <p>Todo List</p>
       <a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
       </div>
         <ul class="todo-list mt-20">
       <li class="py-15 px-5 by-1">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_1" class="filled-in">
         <label for="basic_checkbox_1" class="mb-0 h-15"></label>
         <!-- todo text -->
         <span class="text-line">Nulla vitae purus</span>
         <!-- Emphasis label -->
         <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
         <!-- General tools such as edit or delete-->
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_2" class="filled-in">
         <label for="basic_checkbox_2" class="mb-0 h-15"></label>
         <span class="text-line">Phasellus interdum</span>
         <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5 by-1">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_3" class="filled-in">
         <label for="basic_checkbox_3" class="mb-0 h-15"></label>
         <span class="text-line">Quisque sodales</span>
         <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_4" class="filled-in">
         <label for="basic_checkbox_4" class="mb-0 h-15"></label>
         <span class="text-line">Proin nec mi porta</span>
         <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5 by-1">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_5" class="filled-in">
         <label for="basic_checkbox_5" class="mb-0 h-15"></label>
         <span class="text-line">Maecenas scelerisque</span>
         <small class="badge bg-primary"><i class="fa fa-clock-o"></i> 1 week</small>
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_6" class="filled-in">
         <label for="basic_checkbox_6" class="mb-0 h-15"></label>
         <span class="text-line">Vivamus nec orci</span>
         <small class="badge bg-info"><i class="fa fa-clock-o"></i> 1 month</small>
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5 by-1">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_7" class="filled-in">
         <label for="basic_checkbox_7" class="mb-0 h-15"></label>
         <!-- todo text -->
         <span class="text-line">Nulla vitae purus</span>
         <!-- Emphasis label -->
         <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
         <!-- General tools such as edit or delete-->
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_8" class="filled-in">
         <label for="basic_checkbox_8" class="mb-0 h-15"></label>
         <span class="text-line">Phasellus interdum</span>
         <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5 by-1">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_9" class="filled-in">
         <label for="basic_checkbox_9" class="mb-0 h-15"></label>
         <span class="text-line">Quisque sodales</span>
         <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       <li class="py-15 px-5">
         <!-- checkbox -->
         <input type="checkbox" id="basic_checkbox_10" class="filled-in">
         <label for="basic_checkbox_10" class="mb-0 h-15"></label>
         <span class="text-line">Proin nec mi porta</span>
         <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
         <div class="tools">
         <i class="fa fa-edit"></i>
         <i class="fa fa-trash-o"></i>
         </div>
       </li>
       </ul>
       </div>
       <!-- /.tab-pane -->
     </div>
   </aside>
   <!-- /.control-sidebar -->
   
   <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
   <div class="control-sidebar-bg"></div>     
   
 
   
   
 </div>
 <!-- ./wrapper -->
   
   
  
                 
         <?php include ('chatsupport.php')?>
 
 
   <!-- Page Content overlay -->
   
   
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
   <!-- Vendor JS -->
   <script src="html/template/horizontal/src/js/vendors.min.js"></script>
   <script src="html/template/horizontal/src/js/pages/chat-popup.js"></script>
     <script src="html/assets/icons/feather-icons/feather.min.js"></script>
     <script src="html/assets/vendor_components/Flot/jquery.flot.js"></script>
   <script src="html/assets/vendor_components/Flot/jquery.flot.resize.js"></script>
   <script src="html/assets/vendor_components/Flot/jquery.flot.pie.js"></script>
   <script src="html/assets/vendor_components/Flot/jquery.flot.categories.js"></script>
   <script src="html/assets/vendor_components/echarts/dist/echarts-en.min.js"></script>
   <script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
   <script src="html/assets/vendor_plugins/bootstrap-slider/bootstrap-slider.js"></script>
   <script src="html/assets/vendor_components/OwlCarousel2/dist/owl.carousel.js"></script>
   <script src="html/assets/vendor_components/flexslider/jquery.flexslider.js"></script>
   <script src="html/assets/vendor_components/Web-Ticker-master/jquery.webticker.min.js"></script>
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
 
   
   
   <!-- selecter JS --> 
   <script src="html/assets/vendor_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
   <script src="html/assets/vendor_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
   <script src="html/assets/vendor_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
   <script src="html/assets/vendor_components/select2/dist/js/select2.full.js"></script>
   <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.js"></script>
   <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
   <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.extensions.js"></script>
   <script src="html/assets/vendor_components/moment/min/moment.min.js"></script>
   <script src="html/assets/vendor_components/bootstrap-daterangepicker/daterangepicker.js"></script>
   <script src="html/assets/vendor_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
   <script src="html/assets/vendor_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
   <script src="html/assets/vendor_plugins/timepicker/bootstrap-timepicker.min.js"></script>
   <script src="html/assets/vendor_plugins/iCheck/icheck.min.js"></script>
    
   <script src="html/template/horizontal/src/js/pages/advanced-form-element.js"></script>
     