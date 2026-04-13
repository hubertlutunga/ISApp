
		
<div class="wrapper"> 
	 

     <?php include('header.php');?>
      
    
   
     <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
               <div class="container-full">
               <!-- Main content -->

<?php
 
$codtab = isset($_GET['cod']) ? (int) $_GET['cod'] : 0;
$datatab = EventTableService::findById($pdo, $codtab);

?>             

<style>
    .mb-action-page{ padding:24px 0 42px; }
    .mb-action-hero{ padding:28px 30px; border-radius:30px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 55%,#2563eb 100%); box-shadow:0 24px 50px rgba(15,23,42,.18); color:#f8fafc; margin-bottom:26px; }
    .mb-action-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; }
    .mb-action-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
    .mb-action-subtitle{ margin:0; max-width:700px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
    .mb-action-card{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.10); }
    .mb-action-card .content-top-agile{ padding:26px 28px 10px !important; }
    .mb-action-card .p-40{ padding:18px 28px 30px !important; }
    .mb-action-heading{ margin:0; font-size:28px; font-weight:800; color:#0f172a; }
    .mb-action-copy{ margin:8px 0 0; font-size:14px; color:#64748b; }
    .mb-action-card .form-group{ margin-bottom:16px; }
    .mb-action-card .input-group{ border:1px solid #dbeafe; border-radius:18px; background:#f8fbff; overflow:hidden; }
    .mb-action-card .input-group-text,
    .mb-action-card .form-control{ border:0 !important; background:transparent !important; box-shadow:none !important; min-height:56px; }
    .mb-action-card .input-group-text{ color:#2563eb; padding-left:16px; padding-right:8px; }
    .mb-action-submit{ display:inline-flex; align-items:center; justify-content:center; min-height:58px; border:0; border-radius:18px; background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); box-shadow:0 18px 34px rgba(37,99,235,.20); font-size:15px; font-weight:800; }
    .mb-action-list-shell{ margin-top:24px; padding-top:24px; border-top:1px solid #eef2f7; }
    .mb-action-list-head{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px; }
    .mb-action-list-title{ margin:0; font-size:22px; font-weight:800; color:#0f172a; }
    .mb-action-list-badge{ display:inline-flex; align-items:center; padding:8px 12px; border-radius:999px; background:#eff6ff; border:1px solid #dbeafe; color:#1d4ed8; font-size:12px; font-weight:800; }
    .mb-action-table-list{ width:100%; margin-bottom:0; }
    .mb-action-table-list td{ padding:16px 0; border-bottom:1px solid #eef2f7; vertical-align:middle; }
    .mb-action-table-row:last-child td{ border-bottom:0; }
    .mb-action-table-name{ font-size:16px; font-weight:800; color:#0f172a; }
    .mb-action-empty{ color:#64748b; font-style:italic; }
    @media only screen and (max-width: 769px) {
        .mb-action-page{ padding:18px 0 34px; }
        .mb-action-hero{ padding:22px 20px; border-radius:24px; }
        .mb-action-title{ font-size:28px; }
        .mb-action-card .content-top-agile,
        .mb-action-card .p-40{ padding-left:20px !important; padding-right:20px !important; }
    }
</style>
               

<div class="container h-p100 mb-action-page">
        <div class="mb-action-hero">
            <span class="mb-action-kicker"><i class="mdi mdi-table-edit"></i> Modification de table</span>
            <h1 class="mb-action-title">Ajustez votre plan de table avec precision</h1>
            <p class="mb-action-subtitle">Renommez une table ou mettez a jour son visuel sans perdre la coherence du plan de salle.</p>
        </div>
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
                <div class="row justify-content-center g-4">
                    <div class="col-xl-6 col-lg-7 col-12 boxcontent">
                        <div class="bg-white rounded10 shadow-lg mb-action-card">
							<div class="content-top-agile p-20 pb-0"> 
                                <p class="mb-0 text-fade">Modifier la table</p>
                                <h2 class="mb-action-heading"><?php echo htmlspecialchars((string) ($datatab['nom_tab'] ?? 'Table')); ?></h2>
                                <p class="mb-action-copy">Mettez a jour le nom ou le plan associe depuis une fiche plus propre.</p>
                                
                                


                                <?php 
if (isset($_POST['submit'])) {
    $nom_tab = trim($_POST['nom_tab']); // Utilisation de trim pour enlever les espaces

    // Vérification des erreurs
    if (empty($nom_tab)) {
        echo "<div class='error' style='color:red; font-weight:bold; text-align:center;'>Remplissez le nom de la table</div>";
    } elseif (EventTableService::nameExists($pdo, $nom_tab, (int) $codevent, $codtab)) {
        echo "<div class='error' style='color:red; font-weight:bold;'>Cette table existe déjà</div>";
    } else {


       $fileName = $datatab['plantable'] ?? 'plan_defaul_1.png';

        if ((int) $codevent === 128 && isset($_FILES['photo1']) && ($_FILES['photo1']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $uploadedName = EventMediaService::storeUploadedImage($_FILES['photo1'], '../pages/plantable', null, 2097152);
                if ($uploadedName !== null) {
                    $fileName = $uploadedName;
                }
            } catch (RuntimeException $e) {
                echo "<div class='error' style='color:red; font-weight:bold;'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
            }
        }          
    EventTableService::update($pdo, $codtab, $nom_tab, $fileName);



              echo '<script>
              Swal.fire({
                  title: "Modification !",
                  text: "La modification a été effectuée avec succès.",
                  icon: "success",
                  confirmButtonText: "OK"
              }).then((result) => {
                  if (result.isConfirmed) {
                      window.location.href = "index.php?page=addtable"; // Rédirection vers la page de détails
                  }
              });
              </script>';


    }

    }
?>






							</div>
							<div class="p-40">
							<form action="" method="post" enctype="multipart/form-data"> 

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-table"></i></span>
                                        <input type="text" class="form-control ps-15 bg-transparent" name="nom_tab" value="<?php echo $datatab['nom_tab']?>" placeholder="Nom de table">
                                    </div>
                                </div> 














    <?php 

        if ($codevent === '128') {
            $vue = 'display:block;';
        }else{
            $vue = 'display:none;';
        }
    
    ?>




                                <div class="form-group" style="<?php echo $vue;?>">
                                    <div class="input-group mb-3">
                      



    <style>
 
      #prev img {
          vertical-align: middle;
          border:1px solid #ccc;width:130px;
      }
 
      #prev {
          margin-top: 10px;
      }
    </style>


      
<label style="border:1px solid #eee; ?>;padding: 5px 15px 5px 15px; background-color: #ccc;color:#000;width:100%;" for="file">Importer le plan de table</label>



<div><input id="file" name="photo1" type="file" style="display:none;"/></div> 
   




<div id="prev"></div>



    <script>
    (function() {
 
        function createThumbnail(file) {
 
            var reader = new FileReader();
 
            reader.addEventListener('load', function() {
 
                var imgElement = document.createElement('img');
                imgElement.style.maxWidth = '150px';
                imgElement.style.maxHeight = '150px';
                imgElement.src = this.result;
                prev.appendChild(imgElement);
 
            }, false);
 
            reader.readAsDataURL(file);
 
        }
 
        var allowedTypes = ['png', 'jpg', 'jpeg', 'gif'],
            fileInput = document.querySelector('#file'),
            prev = document.querySelector('#prev');
 
        fileInput.addEventListener('change', function() {
 
            var files = this.files,
                filesLen = files.length,
                imgType;
 
            for (var i = 0 ; i < filesLen ; i++) {
 
                imgType = files[i].name.split('.');
                imgType = imgType[imgType.length - 1];
 
                if(allowedTypes.indexOf(imgType) != -1) {
                    createThumbnail(files[i]);
                }
 
            }
 
        }, false);
 
    })();
    </script>








                    </div>
                    </div>
















                                <div class="row"> 
                                    <div class="col-12 text-center">
                                        <button type="submit" name="submit" class="btn btn-primary w-p100 mt-10 mb-action-submit">Enregistrer la table</button>
                                    </div>
                                </div>
                            </form>			
                                <div class="text-center mb-action-list-shell">
                                    <div class="mb-action-list-head">
                                        <h3 class="mb-action-list-title">Autres tables</h3>
                                        <span class="mb-action-list-badge">Salle organisee</span>
                                    </div>
                                   
								
                                
                                
                                
                                
                                    <table width="100%" class="mb-action-table-list">
                    <tr style="margin-bottom: 45px;">
                   
                    </tr>

<?php 
$reqtable = "SELECT * FROM tableevent WHERE cod_event = :codevent AND cod_tab != :cod_tab ORDER BY nom_tab ASC";
$reqtable = $pdo->prepare($reqtable);
$reqtable->execute(['codevent' => $codevent, 'cod_tab' => $codtab]);

// Vérifie si des résultats sont disponibles
if ($reqtable->rowCount() > 0) {
    while ($row_table = $reqtable->fetch(PDO::FETCH_ASSOC)) {
        ?>
                                        <tr class="mb-action-table-row">
                                            <td align="left">
                                                <span class="mb-action-table-name"><?php echo htmlspecialchars($row_table['nom_tab']); ?></span>
            </td>
            <td align="right">




 
 
  
 
 
 
											   <div class="list-icons d-inline-flex">
                          <div class="list-icons-item dropdown">
                                          
  
                           <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                                     <div class="dropdown-menu dropdown-menu-end">
  
 
                       
					   <a class="dropdown-item" href="index.php?page=modtable&cod=<?php echo $row_table['cod_tab'];?>"  style="color:#aaa;">
					   <i class="fa fa-share"></i> Modifier la table</a> 

				 
 
   
 <a class="dropdown-item"
   href="#"
   style="color:red;"
   title="Suppression"
   onclick="confirmSuppInv(
     event,
     '<?= (int)$row_table['cod_tab'] ?>',
     '<?= htmlspecialchars($codevent, ENT_QUOTES) ?>',
     '<?= htmlspecialchars(ucfirst($row_table['nom_tab']), ENT_QUOTES) ?>'
   )">
  <i class="fa fa-remove"></i> Supprimer la table
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
    echo '<tr><td colspan="1" class="mb-action-empty">Aucune autre table trouvee.</td></tr>';
}
?>
                    
                  </table>
                
                
                
                </div>
								
							</div>
						</div>	
					</div>
				</div>
			</div>			
		</div>
	</div>



    


<script>
async function confirmSuppInv(e, idInv, codEvent, nom) {
  e.preventDefault();

  Swal.fire({
    title: "Supprimer ?",
    html: "Voulez-vous vraiment supprimer la table <b>" + nom + "</b> ?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Annuler",
    reverseButtons: true,
    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading(),
    preConfirm: async () => {
      try {
        const res = await fetch("pages/ajax_supprimer_tab.php", {
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
	 
	