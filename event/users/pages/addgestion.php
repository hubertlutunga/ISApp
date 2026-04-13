
		
<div class="wrapper"> 
	 

     <?php include('header.php');?>
      
    
   
     <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
               <div class="container-full">
               <!-- Main content -->
                
               

<div class="container h-p100 mb-admin-page">
    <div class="mb-admin-hero">
      <span class="mb-admin-kicker"><i class="mdi mdi-account-multiple-plus-outline"></i> Administration</span>
      <h1 class="mb-admin-title">Ajoutez un administrateur secondaire proprement</h1>
      <p class="mb-admin-copy">Recherchez un utilisateur par numéro ou par adresse e-mail, puis rattachez-le à l’événement sans perdre la lisibilité du partage.</p>
    </div>
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
        <div class="row justify-content-center g-4">
          <div class="col-xl-6 col-lg-7 col-12 boxcontent">
            <div class="bg-white rounded10 shadow-lg mb-admin-card">
							<div class="content-top-agile p-20 pb-0"> 
                                <p class="mb-0 text-fade">Ajouter un administrateur</p>
                                <h2 class="mb-admin-heading">Recherche d’utilisateur</h2>
                                <p class="mb-admin-subcopy">Utilisez un numéro ou une adresse e-mail pour retrouver rapidement la personne à ajouter.</p>
                                  
							</div>



<?php 
    $vue = (isset($dataevent['cod_user']) && isset($dataevent['cod_user2'])) ? "style='display:none;'" : "style='display:block;'";
?>

<style>
  .mb-admin-page{ padding:24px 0 42px; }
  .mb-admin-hero{ padding:28px 30px; border-radius:30px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 55%,#0ea5e9 100%); box-shadow:0 24px 50px rgba(15,23,42,.18); color:#f8fafc; margin-bottom:26px; }
  .mb-admin-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; }
  .mb-admin-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
  .mb-admin-copy{ margin:0; max-width:700px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
  .mb-admin-card{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.10); }
  .mb-admin-card .content-top-agile{ padding:26px 28px 10px !important; }
  .mb-admin-card .p-40{ padding:18px 28px 30px !important; }
  .mb-admin-heading{ margin:0; font-size:28px; font-weight:800; color:#0f172a; }
  .mb-admin-subcopy{ margin:8px 0 0; font-size:14px; color:#64748b; }
  .mb-admin-card .form-group{ margin-bottom:16px; }
  .mb-admin-card .input-group{ border:1px solid #dbeafe; border-radius:18px; background:#f8fbff; overflow:hidden; }
  .mb-admin-card .input-group-text,
  .mb-admin-card .form-control{ border:0 !important; background:transparent !important; box-shadow:none !important; min-height:56px; }
  .mb-admin-card .input-group-text{ color:#2563eb; padding-left:16px; padding-right:8px; }
  .mb-admin-submit,
  .mb-admin-cta,
  .mb-admin-cancel{ display:inline-flex; align-items:center; justify-content:center; min-height:56px; border-radius:18px; font-weight:800; text-decoration:none !important; }
  .mb-admin-submit,
  .mb-admin-cta{ background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); border:0; box-shadow:0 18px 34px rgba(37,99,235,.20); }
  .mb-admin-cancel{ background:#fee2e2; color:#b91c1c !important; border:1px solid #fecaca; }
  .mb-admin-result{ margin-top:18px; padding:18px; border-radius:20px; background:#f8fbff; border:1px solid #dbeafe; }
  .mb-admin-user-name{ margin:0; font-size:22px; font-weight:800; color:#0f172a; }
  .mb-admin-user-meta{ display:block; margin-top:6px; color:#64748b; }
  .mb-admin-list{ margin-top:28px; padding-top:24px; border-top:1px solid #eef2f7; }
  .mb-admin-list-title{ margin:0 0 14px; font-size:22px; font-weight:800; color:#0f172a; }
  .mb-admin-table{ width:100%; margin-bottom:0; }
  .mb-admin-table td{ padding:16px 0; border-bottom:1px solid #eef2f7; vertical-align:middle; }
  .mb-admin-table tr:last-child td{ border-bottom:0; }
  .mb-admin-empty{ color:#64748b; font-style:italic; }
  @media only screen and (max-width: 769px) {
    .mb-admin-page{ padding:18px 0 34px; }
    .mb-admin-hero{ padding:22px 20px; border-radius:24px; }
    .mb-admin-title{ font-size:28px; }
    .mb-admin-card .content-top-agile,
    .mb-admin-card .p-40{ padding-left:20px !important; padding-right:20px !important; }
    .mb-admin-table td{ display:block; width:100%; padding:12px 0; }
    .mb-admin-table td[align="right"]{ text-align:left !important; padding-top:0 !important; }
  }
</style>

              <div class="p-40">

							<form action="" method="post" enctype="multipart/form-data" <?php echo $vue;?>> 

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-table"></i></span>
                                        <input type="text" class="form-control ps-15 bg-transparent" name="search" placeholder="Numéro ou adresse e-mail">
                                    </div>
                                </div> 




 


 


<?php 

if (isset($_POST['submitsearch'])) { 

    $search = trim($_POST['search']);

        $stmtresult = $pdo->prepare("SELECT * FROM is_users WHERE email = ? OR phone = ?");
        $stmtresult->execute([$search, $search]);
        $userresult = $stmtresult->fetch();

        if ($userresult) {

        ?> 
                <div class="mb-admin-result">
                <h4 class="mb-admin-user-name"><?php echo htmlspecialchars($userresult['noms']); ?></h4>
                <span class="mb-admin-user-meta"><?php echo htmlspecialchars($userresult['phone']); ?></span>
                <span class="mb-admin-user-meta"><?php echo htmlspecialchars($userresult['email']); ?></span>

                <a href="index.php?page=addgestion_user&codus=<?php echo htmlspecialchars($userresult['cod_user']); ?>&codevent=<?php echo $codevent; ?>" class="btn btn-primary w-p100 mt-10 mb-admin-cta">Ajouter comme administrateur</a><br>
                <a href="index.php?page=addgestion" class="btn btn-danger w-p100 mt-10 mb-admin-cancel">Annuler</a>
                </div>
                
        <?php

        $vuebtn = 'display:none !important';

        }else{
            ?>
                <em>Aucun résultat trouvé</em><br>
            <?php

        $vuebtn = 'display:block !important';
        }


 
    }else{
        $vuebtn = 'display:block !important';
    }
    
?>





                                <div class="row" style="<?php echo $vuebtn;?>"> 
                                    <div class="col-12 text-center">
                										<button type="submit" name="submitsearch" class="btn btn-primary w-p100 mt-10 mb-admin-submit">Rechercher</button>
                                    </div>
                                </div>
                            </form>			



<br>
<br>
 


<br>

								<div class="text-center mb-admin-list"> 
                <h3 class="mb-admin-list-title">Administrateurs de l’événement</h3>
                                
                                
                  <table width="100%" class="mb-admin-table">
                    <tr style="margin-bottom: 45px;">
                   
                    </tr>

<?php 
$reqgest = "SELECT * FROM events WHERE cod_event = :codevent ORDER BY cod_event DESC";
$reqgest = $pdo->prepare($reqgest); // Correction de $reqtable en $reqgest
$reqgest->execute([':codevent' => $codevent]); // Ajout de ":" pour le paramètre

// Vérifie si des résultats sont disponibles

if ($reqgest->rowCount() > 0) {

    $row_gest = $reqgest->fetch(PDO::FETCH_ASSOC);

        

        $requser = "SELECT * FROM is_users WHERE cod_user = :cod_user1 OR cod_user = :cod_user2 ORDER by cod_user ASC";
        $requser = $pdo->prepare($requser);
        $requser->execute([
            ':cod_user1' => $row_gest['cod_user'],
            ':cod_user2' => $row_gest['cod_user2']
        ]);

        while ($row_user = $requser->fetch(PDO::FETCH_ASSOC)) {

        ?>

        <tr style="margin-bottom: 15px;">

            <td align="left">
                <h4><?php echo htmlspecialchars($row_user['noms']); ?></h4>
                <span><?php echo htmlspecialchars($row_user['phone']); ?></span><br>
                <span><?php echo htmlspecialchars($row_user['email']); ?></span>
            </td>

           
            <td align="right">
            <?php if ($row_user['cod_user'] == $row_gest['cod_user2']): ?>
                <div class="list-icons d-inline-flex">
                    <div class="list-icons-item dropdown">
                        <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-h" style="font-size:20px;"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item"
                            href="#"
                            style="color:red;"
                            title="Suppression"
                            onclick="confirmSuppInv(
                                event,
                                '<?= (int)$row_user['cod_user'] ?>',
                                '<?= htmlspecialchars($codevent, ENT_QUOTES) ?>',
                                '<?= htmlspecialchars(ucfirst($row_user['noms']), ENT_QUOTES) ?>'
                            )">
                            <i class="fa fa-remove"></i> Retirer l'administrateur
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </td>





        </tr>

        <?php

        }

 



} else {
    // Ne rien afficher si aucun résultat
    echo '<tr><td colspan="1" class="mb-admin-empty">Aucun administrateur secondaire ajouté pour le moment.</td></tr>';
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
    title: "Retirer ?",
    html: "Voulez-vous vraiment retirer <b>" + nom + "</b> ?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Oui, retirer",
    cancelButtonText: "Annuler",
    reverseButtons: true,
    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading(),
    preConfirm: async () => {
      try {
        const res = await fetch("pages/ajax_retirer_admin.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ idinv: idInv, cod: codEvent })
        });
        if (!res.ok) throw new Error("Erreur serveur (" + res.status + ")");
        const data = await res.json();
        if (!data.success) throw new Error(data.message || "Modification impossible.");
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
        title: "Retirer",
        text: nom + " a été retiré.",
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
	 
	