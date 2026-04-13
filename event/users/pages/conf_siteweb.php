<?php if (session_status() === PHP_SESSION_NONE) { session_start(); }?>

<div class="wrapper"> 
	 

<?php include('header.php');?>
      
    
<?php  include('editersite.php'); ?>   




<style>
.mb-siteconf-page{ padding:8px 0 34px; }
.mb-siteconf-hero{ padding:30px; border-radius:30px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 55%,#0ea5e9 100%); box-shadow:0 24px 50px rgba(15,23,42,.16); color:#fff; margin-bottom:24px; }
.mb-siteconf-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
.mb-siteconf-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
.mb-siteconf-copy{ margin:0; max-width:760px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
.mb-siteconf-actions{ display:flex; gap:12px; flex-wrap:wrap; margin-top:20px; }
.mb-siteconf-preview{ display:inline-flex; align-items:center; gap:8px; min-height:46px; padding:0 16px; border-radius:14px; background:#fff; color:#0f172a !important; text-decoration:none; font-weight:800; }
.content .box{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.08); }
.content .box-header{ padding:22px 24px; border-bottom:1px solid #eef2f7; }
.content .box-title{ font-size:22px; font-weight:800; color:#0f172a; }
.content .box-body{ padding:24px; }
.content .form-control{ border-radius:16px; border:1px solid #dbeafe; background:#f8fbff; box-shadow:none; }
.content textarea.form-control{ min-height:140px; }
.content .btn.btn-primary,
.content .btn.btn-warning{ min-height:52px; border-radius:16px; font-weight:800; }
.content .btnpic{ display:inline-flex; align-items:center; justify-content:center; min-height:52px; padding:0 18px; border-radius:16px; background:#eff6ff; color:#1d4ed8; font-weight:800; text-decoration:none; }
.box-body{
    display:none;
}

.box.open .box-body{
    display:block;
    animation: fadeIn 0.3s ease;
}

.box-header{
    cursor:pointer;
}

@keyframes fadeIn{
    from{opacity:0}
    to{opacity:1}
}

@media only screen and (max-width: 769px){
  .mb-siteconf-page{ padding:0 0 26px; }
  .mb-siteconf-hero{ padding:22px 20px; border-radius:24px; }
  .mb-siteconf-title{ font-size:28px; }
  .content .box-header,
  .content .box-body{ padding:18px; }
}
</style>



 <?php 

 // section affichage des photos de love story + texte

  $loveStory = LoveStoryService::getByEvent($pdo, (int) $codevent);
  $photolove1 = $loveStory['imgcoeur1'];
  $photolove2 = $loveStory['imgcoeur2'];
  $text_lovestory = $loveStory['text_lovestory'];
 
 ?>























 
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
      <div class="mb-siteconf-page">
        <div class="mb-siteconf-hero">
          <span class="mb-siteconf-kicker"><i class="mdi mdi-palette-outline"></i> Personnalisation du site</span>
          <h1 class="mb-siteconf-title">Façonnez une vitrine événementielle à la hauteur du parcours</h1>
          <p class="mb-siteconf-copy">Ajustez les visuels, les textes et les sections publiques depuis une interface plus cohérente avec le reste du tableau de bord.</p>
          <div class="mb-siteconf-actions">
            <a class="mb-siteconf-preview" target="_blank" href="https://invitationspeciale.com/site/index.php?page=accueil&cod=<?php echo $codevent; ?>"><i class="mdi mdi-eye-outline"></i> Prévisualiser le site</a>
          </div>
        </div>

		<!-- Main content -->
		<section class="content">
			<div class="row">
			  

<?php

$stmtsect = $pdo->prepare("SELECT statut FROM websitesection WHERE cod_event = :cod AND section = 'compteur' LIMIT 1");
$stmtsect->execute([':cod' => $codevent]);
$rowsection = $stmtsect->fetch(PDO::FETCH_ASSOC);

    if ($rowsection) {
        
         if ($rowsection['statut'] == "on") {
                $check = "on";
         }else{
                $check = "off";
         }
    }
           
?>



 
<!-- ========== SECTION COMPTEUR ========== -->
<div class="col-xl-6 col-12">
  <div class="box">
    
				  <div class="box-header">
					<h4 class="box-title">Section compteur</h4>
					<div class="box-controls pull-right">
						<label class="switch switch-border switch-primary">
						  <input type="checkbox" value="<?php echo $check;?>" checked>
						  <span class="switch-indicator"></span>
						  <span class="switch-description"></span>
						</label>
					</div>
				  </div>

    <div class="box-body">

      <form action="" method='post' enctype="multipart/form-data" class="space-y-2">
        <em>Image de background</em><br>
        <img id="preview-compteur" class="bg-image rounded" src="../../couple/images/<?php echo htmlspecialchars($photo); ?>" style="width:100%;max-height:360px;object-fit:cover" alt="Background compteur">

        <div class="mt-10 d-flex align-items-center gap-10">
          <input type="file" name="photo1" id="file-compteur" accept="image/*" class="form-control" style="max-width:320px">
          <button type="submit" name="submitimgback" class="btn btn-primary btn-sm" id="save-compteur" disabled>Enregistrer</button>
        </div>

        <input type="hidden" name="section" value="compteur_bg">
        <input type="hidden" name="codevent" value="<?php echo (int)$codevent; ?>">
      </form>

    </div>
  </div>
</div>








<!-- ========== SECTION SAVE THE DATE ========== -->
<div class="col-xl-6 col-12">
  <div class="box">
    
				  <div class="box-header">
					<h4 class="box-title">Section Save the date</h4>
					<div class="box-controls pull-right">
						<label class="switch switch-border switch-primary">
						  <input type="checkbox" checked>
						  <span class="switch-indicator"></span>
						  <span class="switch-description"></span>
						</label>
					</div>
				  </div>

    <div class="box-body">

      <form action="" method="post" enctype="multipart/form-data" class="space-y-2">
        <em>Image dans le cœur</em><br>
        <img id="preview-sdd" class="bg-image rounded" src="../../couple/images/<?php echo htmlspecialchars($photocoeur); ?>" style="width:100%;max-height:360px;object-fit:cover" alt="Image Save the Date">

        <div class="mt-10 d-flex align-items-center gap-10">

          <input type="file" name="photo2" id="file-sdd" accept="image/*" class="form-control" style="max-width:320px"> 

        </div>
 
 
      <hr class="my-15">
 
        <label class="mb-5"><strong>Texte “Save the date”</strong></label>
        <textarea name="text_sdd" id="text_sdd" class="form-control" rows="6"><?php echo htmlspecialchars($text_sdd); ?></textarea>

        <div class="mt-10">
          <button type="submit" name="submitimgcoeur" class="btn btn-primary btn-sm">Enregistrer</button>
        </div>

        <input type="hidden" name="section" value="sdd_text">
        <input type="hidden" name="codevent" value="<?php echo (int)$codevent; ?>">
      </form>

    </div>
  </div>
</div>





















 



<!-- ========== SECTION LOVE STORY ========== -->



<div class="col-xl-6 col-12">
  <div class="box">
    
				  <div class="box-header">
					<h4 class="box-title">Section Love Story</h4>
					<div class="box-controls pull-right">
						<label class="switch switch-border switch-primary">
						  <input type="checkbox" checked>
						  <span class="switch-indicator"></span>
						  <span class="switch-description"></span>
						</label>
					</div>
				  </div>

    <div class="box-body">

      <form action="" method="post" enctype="multipart/form-data" class="space-y-2">
        <em>ImageCoeur debut de histoire</em><br>
        <img id="preview-sdd" class="bg-image rounded" src="../../couple/images/<?php echo htmlspecialchars($photolove1); ?>" style="width:100%;max-height:360px;object-fit:cover" alt="Image Save the Date">

        <div class="mt-10 d-flex align-items-center gap-10">

          <input type="file" name="photo3" id="file-sdd" accept="image/*" class="form-control" style="max-width:320px"> 

        </div>
 
          <hr>

        <em>ImageCoeur actuelle de histoire</em><br>
        <img id="preview-sdd" class="bg-image rounded" src="../../couple/images/<?php echo htmlspecialchars($photolove2); ?>" style="width:100%;max-height:360px;object-fit:cover" alt="Image Save the Date">

        <div class="mt-10 d-flex align-items-center gap-10">

          <input type="file" name="photo4" id="file-sdd" accept="image/*" class="form-control" style="max-width:320px"> 

        </div>

 
      <hr class="my-15">
 
        <label class="mb-5"><strong>Texte Love Story</strong></label>
        <textarea name="text_lovestory" id="text_lovestory" class="form-control" rows="6"><?php echo htmlspecialchars($text_lovestory); ?></textarea>
 


                                <div class="row"> 
                                    <div class="col-12 text-center">
                                        <button type="submit" name="submit_lovestory" class="btn btn-primary w-p100 mt-10">Enregistrer</button>
                                    </div>
                                </div>


        <input type="hidden" name="section" value="text_lovestory">
        <input type="hidden" name="codevent" value="<?php echo (int)$codevent; ?>">
      </form>

      
      <hr>



        <label class="mb-5"><strong>Etapes Love Story</strong></label>

 
          <form action="" method="post" enctype="multipart/form-data" class="space-y-2">
              
          <div class="form-group">
              <div class="input-group mb-3">
                  <span class="input-group-text bg-transparent">
                      <i class="fas fa-calendar"></i>
                  </span>
                  <input type="month" required class="form-control ps-15 bg-transparent" name="dateetap" placeholder="Mois et année">
              </div>
          </div>

          <div class="form-group">
              <div class="input-group mb-3">
                  <span class="input-group-text bg-transparent">
                      <i class="fas fa-edit"></i>
                  </span>
                  <input type="text" required class="form-control ps-15 bg-transparent" name="etap" placeholder="Evénement étape">
              </div>
          </div>

          <div class="row"> 
              <div class="col-12 text-center">
                  <button type="submit" name="submitetaplove" class="btn btn-primary w-p100 mt-10">Ajouter</button>
              </div>
          </div>

      </form>















      <hr>

      <div class="text-center">
                                   
								
                                
                                
                                
                                
              <table width="100%" style="margin-bottom:100px;">
                    <tr style="margin-bottom: 45px;">
                   
                    </tr>

<?php 
$loveStorySteps = LoveStoryService::listSteps($pdo, (int) $codevent);

// Vérifie si des résultats sont disponibles
if (!empty($loveStorySteps)) {
  foreach ($loveStorySteps as $row_ls) {
        ?>
        <tr style="margin-bottom:15px;">
            <td align="left" style="border-bottom:1px solid #ddd; padding: 5px 0; height: 50px;">
                <?php echo htmlspecialchars($row_ls['event_etap']); ?> – <?php echo date('F Y', strtotime($row_ls['date_etap'])); ?>
            </td>
            <td align="right" style="border-bottom:1px solid #ddd">




 
 
  
 
 
 
											   <div class="list-icons d-inline-flex">
                          <div class="list-icons-item dropdown">
                                          
  
                           <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                                     <div class="dropdown-menu dropdown-menu-end">
  
 
                       
              <!-- <a class="dropdown-item" href="index.php?page=modtable&cod=<?php echo $row_table['cod_tab'];?>"  style="color:#aaa;">
              <i class="fa fa-share"></i> Modifier la table</a>  -->

				 
 
   
 <a class="dropdown-item"
   href="#"
   style="color:red;"
   title="Suppression"
   onclick="confirmSuppInv(
     event,
     '<?= (int)$row_ls['cod_ls'] ?>',
     '<?= htmlspecialchars($codevent, ENT_QUOTES) ?>',
     '<?= htmlspecialchars(ucfirst($row_ls['event_etap']), ENT_QUOTES) ?>'
   )">
  <i class="fa fa-remove"></i> Supprimer l'étape
</a>  





      
       
                        </div>
                         </div>
                       </div>





            </td>
        </tr>
        <?php 
    }
} else {
    // Ne rien afficher si aucun résultat
    echo '<tr><td colspan="1" style="text-align: left; padding: 5px 0;">Aucun étape trouvé</td></tr>';
}
?>
                    
                  </table>
                
                
                
                </div>



<script>
async function confirmSuppInv(e, idInv, codEvent, nom) {
  e.preventDefault();

  Swal.fire({
    title: "Supprimer ?",
    html: "Voulez-vous vraiment supprimer cette étape <b>" + nom + "</b> ?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Annuler",
    reverseButtons: true,
    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading(),
    preConfirm: async () => {
      try {
        const res = await fetch("pages/ajax_supprimer_etape.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ idinv: idInv, cod: codEvent })
        });
        if (!res.ok) throw new Error("Erreur serveur (" + res.status + ")");
        const data = await res.json();
        if (!data.success) throw new Error(data.message || "Suppression impossible.");
        return data; // passe à then(result)
      } catch (err) {
        Swal.showValidationMessage(err.message);
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      // Retirer la ligne sans recharger
      const row = document.getElementById("inv-" + idInv);
      if (row) row.remove();

      Swal.fire({
        title: "Supprimé",
        text: nom + " a été supprimé.",
        icon: "success",
        timer: 1800,
        showConfirmButton: false
      }).then(() => {
        // Actualiser la page après un court délai
        location.reload();
      });
    }
  });
}
</script>



    </div>
  </div>
</div>



























<!-- ========== SECTION GALERIE PHOTOS ========== -->



<style>
.image-container { position: relative; margin: 5px; }
.image-container img { width: 100px; height: 100px; object-fit: cover; }
.delete-icon {
    position: absolute; top: 0; right: 0;
    background-color: rgba(255, 0, 0, 0.7); color: white; border: none; cursor: pointer;
}

.photo-wrapper { position: relative; display: inline-block; }
.square-img { width: 100px; height: 100px; object-fit: cover; margin: 10px; }
.square-img-qr { width: 100px; height: 100px; object-fit: cover; margin: 10px; border:1px solid #000 !important; }
.delete-photoX {
    position: absolute; top: 2px; right: 2px;
    background-color: rgba(255, 0, 0, 0.8); border: none; color: white; border-radius: 50%;
    width: 22px; height: 22px; cursor: pointer; font-size: 14px; line-height: 20px; text-align: center;
}
</style>



<div class="col-xl-6 col-12">
  <div class="box">
    
				  <div class="box-header">
					<h4 class="box-title">Galerie Photos</h4>
					<div class="box-controls pull-right">
						<label class="switch switch-border switch-primary">
						  <input type="checkbox" checked>
						  <span class="switch-indicator"></span>
						  <span class="switch-description"></span>
						</label>
					</div>
				  </div>

    <div class="box-body">

      
 

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({ title: "Supprimé !", text: "La photo a bien été supprimée.", icon: "success", timer: 2000, showConfirmButton: false });
    setTimeout(function () {
        history.replaceState(null, null, window.location.pathname + window.location.search.replace(/&?deleted=1/, ''));
    }, 2000);
});
</script>
<?php endif; ?>



<?php 
$stmtimg = $pdo->prepare("SELECT * FROM galeriephotos WHERE cod_event = ? ORDER BY cod_gp DESC");
$stmtimg->execute([$codevent]); 
while ($dataphoto = $stmtimg->fetch(PDO::FETCH_ASSOC)) { 
?>
    <div class="photo-wrapper" data-photo="<?php echo (int)$dataphoto['cod_gp']; ?>">
        <img src="galeriephoto/<?php echo htmlspecialchars($dataphoto['nom_photo']); ?>" alt="<?php echo htmlspecialchars($dataphoto['nom_photo']); ?>" class="square-img">
        <button style="<?php echo $displayact; ?>" class="delete-photoX" onclick="confirmSuppEvent(event, '<?php echo htmlspecialchars($dataphoto['cod_gp']); ?>', '<?php echo htmlspecialchars($codevent); ?>')">✖</button>
    </div>
<?php } ?>

<script>
function confirmSuppEvent(event, codPhoto, codGetevent) {
    event.preventDefault();
    Swal.fire({
        title: "Supprimer !",
        text: "Êtes-vous sûr de vouloir supprimer cette photo ?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Non"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "index.php?page=supphotogalerie&cod=" + codPhoto + "&codevent=" + codGetevent;
        }
    });
}
</script>

<form id="galerieForm" action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <div class="input-group mb-3">
            <label for="fileInput" class="btnpic"><i class="fas fa-plus"></i> Importer les photos</label>
            <input type="file" name="photos[]" required class="form-control ps-15 bg-transparent" accept="image/*" id="fileInput" multiple style="display: none;">
            <div id="previewContainer" class="mt-2" style="display: flex; flex-wrap: wrap;"></div>
        </div>
    </div>
    <div class="row"> 
        <div class="col-12 text-center"> 
          <button type="submit" name="submit_photos" class="btn btn-warning w-p100 mt-10">Ajouter les photos</button>
       </div>
    </div>
</form>





    </div>
  </div>
</div>















<!-- ===== JS: aperçu + upload AJAX (jQuery + SweetAlert2) ===== -->
<script>
// ---- Aperçu instantané + activation bouton ----
function wirePreview(fileInputId, imgPreviewId, saveBtnId) {
  const fi = document.getElementById(fileInputId);
  const img = document.getElementById(imgPreviewId);
  const btn = document.getElementById(saveBtnId);
  fi.addEventListener('change', () => {
    if (fi.files && fi.files[0]) {
      img.src = URL.createObjectURL(fi.files[0]);
      btn.disabled = false;
    }
  });
}

wirePreview('file-compteur','preview-compteur','save-compteur');
wirePreview('file-sdd','preview-sdd','save-sdd');

// ---- Soumission image (compteur & sdd) ----
function wireImageForm(formId, previewId) {
  const form = document.getElementById(formId);
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    try {
      const res = await fetch('upload_event_image.php', { method:'POST', body: fd });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Erreur inconnue');
      // rafraîchir l’aperçu avec la version finale (cache-bust)
      const img = document.getElementById(previewId);
      img.src = json.public_url + (json.public_url.includes('?') ? '&' : '?') + 't=' + Date.now();

      Swal.fire({icon:'success', title:'Enregistré', text:'Image remplacée avec succès.'});
      // désactiver le bouton
      form.querySelector('button[type=submit]').disabled = true;
      // vider l’input file
      form.querySelector('input[type=file]').value = '';
    } catch(err) {
      Swal.fire({icon:'error', title:'Erreur', text: err.message});
    }
  });
}
wireImageForm('form-compteur','preview-compteur');
wireImageForm('form-sdd-image','preview-sdd');

// ---- Enregistrement texte SDD ----
document.getElementById('form-sdd-texte').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  try {
    const res = await fetch('update_event_text.php', { method:'POST', body: fd });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Erreur inconnue');
    Swal.fire({icon:'success', title:'Enregistré', text:'Texte mis à jour.'});
  } catch(err) {
    Swal.fire({icon:'error', title:'Erreur', text: err.message});
  }
});
</script>






<!--
			  <div class="col-xl-6 col-12">
				<div class="box box-slided-up">
				  <div class="box-header with-border">
					<h4 class="box-title">Section Love Story</h4>

					<ul class="box-controls pull-right">


						<label class="switch switch-border switch-primary">
						  <input type="checkbox" checked>
						  <span class="switch-indicator"></span>
						  <span class="switch-description"></span>
						</label> 

					  <li><a class="box-btn-slide" href="#"></a></li> 

					</ul> 
				  </div>


				  <div class="box-body"> 

                       <em>Image du debut LoveStory</em><br>
					   <img class="bg-image" src="../../couple/images/<?php echo $photocoeur;?>" width="100%" alt="">
                  
                       <em>Image de la fin LoveStory</em><br>
					   <img class="bg-image" src="../../couple/images/<?php echo $photocoeur;?>" width="100%" alt="">
                  
                       
					<div class="box-body">
 

					</div>
				  </div>


				</div>
			  </div>
-->













			</div>
		</section>
		<!-- /.content -->
	  </div>
  </div>
 
</div>
<!-- ./wrapper -->
	
	
	 













































 
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
	 
	


<script>
document.addEventListener("DOMContentLoaded", function(){

    const boxes = document.querySelectorAll(".box");

    boxes.forEach(box => {

        const header = box.querySelector(".box-header");

        header.addEventListener("click", function(e){

            // évite le clic sur les switch
            if(e.target.closest(".switch")) return;

            box.classList.toggle("open");

        });

    });

});
</script>






<!-- traitement upload photos + alert JS -->

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    icon: '<?= $_SESSION['alert']['type']; ?>',
    title: '<?= $_SESSION['alert']['type'] === "success" ? "Succès" : ($_SESSION['alert']['type'] === "warning" ? "Attention" : "Erreur"); ?>',
    text: '<?= $_SESSION['alert']['message']; ?>',
    confirmButtonColor: '#3085d6'
  });
});
</script>
<?php unset($_SESSION['alert']); endif; ?>






<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Preview (miniatures)
document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('fileInput');
  const preview = document.getElementById('previewContainer');
  const form = document.getElementById('galerieForm');

  if (input && preview) {
    input.addEventListener('change', function () {
      preview.innerHTML = '';
      const files = Array.from(input.files || []);
      files.forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = (e) => {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.style.width = '70px';
          img.style.height = '70px';
          img.style.objectFit = 'cover';
          img.style.borderRadius = '10px';
          img.style.marginRight = '8px';
          img.style.marginTop = '8px';
          preview.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    });
  }

  // Loader au submit
  if (form) {
    form.addEventListener('submit', function () {
      Swal.fire({
        title: 'Téléchargement...',
        html: 'Compression et enregistrement des photos en cours.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    });
  }
});
</script>