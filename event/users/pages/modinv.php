
		
<div class="wrapper"> 
	 

     <?php include('header.php');?>
     <?php
     $guestStats = EventWorkspaceService::getGuestStats($pdo, (string) $codevent, $date_event ?: null);
     extract($guestStats, EXTR_OVERWRITE);
     $totalTables = EventTableService::countByEvent($pdo, (int) $codevent);
     ?>
      
    <?php 
    
    
    
$cod_inv = $_GET['idinv'];
$stmtin = $pdo->prepare("SELECT * FROM invite WHERE id_inv = ?");
$stmtin->execute([$cod_inv]);
$datainvite = $stmtin->fetch(PDO::FETCH_ASSOC) ?: [];
$guestSeat = $datainvite['siege'] ?? null;
$guestCivilite = (string) ($datainvite['sing'] ?? '');

$stmttab = $pdo->prepare("SELECT * FROM tableevent WHERE cod_tab = ?");
$stmttab->execute([$guestSeat]);
$datatab = $stmttab->fetch(PDO::FETCH_ASSOC) ?: []; // Préciser le mode de récupération

$table = isset($datatab['nom_tab']) ? $datatab['nom_tab'] : 'Non défini'; // Vérification explicite
 
if ($guestCivilite === "C") {
    $radioc = 'checked';
}else{
    $radioc = '';
}

if ($guestCivilite === "Mr") {
    $radiom = 'checked';
}else{
    $radiom = '';
}

if ($guestCivilite === "Mme") {
    $radiomm = 'checked';
}else{
    $radiomm = '';
}

?>

<style>
    .mb-action-page{ padding:24px 0 42px; }
    .mb-action-hero{ padding:28px 30px; border-radius:30px; background:linear-gradient(135deg,#0b1324 0%,#13203a 58%,#2563eb 100%); box-shadow:0 24px 50px rgba(15,23,42,.18); color:#f8fafc; margin-bottom:26px; }
    .mb-action-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
    .mb-action-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
    .mb-action-subtitle{ margin:0; max-width:700px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
    .mb-action-stats{ display:flex; gap:12px; flex-wrap:wrap; margin-top:20px; }
    .mb-action-stat{ display:inline-flex; align-items:center; gap:10px; padding:12px 16px; border-radius:18px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.14); font-weight:700; }
    .mb-action-stat strong{ font-size:18px; font-weight:800; color:#fff; }
    .mb-action-card{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.10); }
    .mb-action-card .content-top-agile{ padding:26px 28px 10px !important; }
    .mb-action-card .p-40{ padding:18px 28px 30px !important; }
    .mb-action-heading{ margin:0; font-size:28px; font-weight:800; color:#0f172a; }
    .mb-action-copy{ margin:8px 0 0; font-size:14px; color:#64748b; }
    .mb-action-radio-grid{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .mb-action-option{ position:relative; }
    .mb-action-option-input{ position:absolute; opacity:0; pointer-events:none; }
    .mb-action-option-card{ display:flex; align-items:center; gap:10px; min-height:58px; padding:12px 14px; border-radius:16px; border:1px solid #dbeafe; background:#f8fbff; cursor:pointer; transition:border-color .18s ease, box-shadow .18s ease, transform .18s ease, background .18s ease; }
    .mb-action-option-card:hover{ transform:translateY(-1px); border-color:#93c5fd; box-shadow:0 10px 20px rgba(37,99,235,.08); }
    .mb-action-option-input:checked + .mb-action-option-card{ border-color:#2563eb; background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%); box-shadow:0 12px 24px rgba(37,99,235,.12); }
    .mb-action-option-icon{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:12px; background:#ffffff; color:#2563eb; font-size:16px; box-shadow:0 8px 16px rgba(15,23,42,.06); flex:0 0 34px; }
    .mb-action-option-copy strong{ display:block; font-size:14px; font-weight:800; color:#0f172a; }
    .mb-action-option-copy span{ display:block; margin-top:2px; font-size:11px; color:#64748b; line-height:1.4; }
    .mb-action-card .form-group{ margin-bottom:16px; }
    .mb-action-card .input-group{ border:1px solid #dbeafe; border-radius:18px; background:#f8fbff; overflow:hidden; }
    .mb-action-card .input-group-text,
    .mb-action-card .form-control,
    .mb-action-card select.form-control{ border:0 !important; background:transparent !important; box-shadow:none !important; height:auto; min-height:56px; }
    .mb-action-card .input-group-text{ color:#2563eb; padding-left:16px; padding-right:8px; }
    .mb-action-submit{ display:inline-flex; align-items:center; justify-content:center; min-height:58px; border:0; border-radius:18px; background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); box-shadow:0 18px 34px rgba(37,99,235,.20); font-size:15px; font-weight:800; }
    .mb-action-danger-link{ display:inline-flex; align-items:center; justify-content:center; gap:8px; margin-top:18px; color:#dc2626; text-decoration:none; font-weight:700; }
    @media only screen and (max-width: 769px) {
        .mb-action-page{ padding:18px 0 34px; }
        .mb-action-hero{ padding:22px 20px; border-radius:24px; }
        .mb-action-title{ font-size:28px; }
        .mb-action-card .content-top-agile,
        .mb-action-card .p-40{ padding-left:20px !important; padding-right:20px !important; }
        .mb-action-radio-grid{ grid-template-columns:repeat(3,minmax(0,1fr)); gap:8px; }
        .mb-action-option-card{ min-height:46px; padding:8px 8px; border-radius:14px; gap:6px; justify-content:center; }
        .mb-action-option-icon{ width:24px; height:24px; border-radius:8px; font-size:12px; flex:0 0 24px; box-shadow:none; }
        .mb-action-option-copy strong{ font-size:11px; line-height:1.1; }
        .mb-action-option-copy span{ display:none; }
    }
</style>




   
     <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
               <div class="container-full">
               <!-- Main content -->
                
               

<div class="container h-p100 mb-action-page">
        <div class="mb-action-hero">
            <span class="mb-action-kicker"><i class="mdi mdi-account-edit-outline"></i> Modification d'invite</span>
            <h1 class="mb-action-title">Mettez a jour votre invite sans perdre le rythme</h1>
            <p class="mb-action-subtitle">Corrigez le nom, le type ou la table associee en gardant une presentation claire et rapide a utiliser.</p>
            <div class="mb-action-stats">
                <span class="mb-action-stat"><i class="mdi mdi-account-group-outline"></i> Invites <strong><?php echo (int) $total_inv; ?></strong></span>
                <span class="mb-action-stat"><i class="mdi mdi-check-decagram-outline"></i> Confirmes <strong><?php echo (int) $total_invconf; ?></strong></span>
                <span class="mb-action-stat"><i class="mdi mdi-table-furniture"></i> Tables <strong><?php echo (int) $totalTables; ?></strong></span>
            </div>
        </div>
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
                <div class="row justify-content-center g-4">
                    <div class="col-xl-6 col-lg-7 col-12 boxcontent">
                        <div class="bg-white rounded10 shadow-lg mb-action-card">
							<div class="content-top-agile p-20 pb-0"> 
                                <p class="mb-0 text-fade">Modifier <?php echo htmlspecialchars((string) ($datainvite['nom'] ?? ''), ENT_QUOTES, 'UTF-8');?></p>
                                <?php $audienceLabels = EventWorkspaceService::audienceLabels((string) ($type_event ?? '')); ?>
                                <h2 class="mb-action-heading"><?php echo htmlspecialchars($audienceLabels['sheet'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="mb-action-copy"><?php echo htmlspecialchars($audienceLabels['edit_copy'], ENT_QUOTES, 'UTF-8'); ?></p>
                                
                                



<?php 

 
  
if(isset($_POST['submit'])){

    $invite = @$_POST['invite']; 
    $sing = @$_POST['type_invite']; 
    $nom_table = @$_POST['tableevent']; 
    
                            // $reqinvc="SELECT count(*) as total_ex FROM invite where nom = '$invite' AND id_inv != '$cod_inv'";
                            // $invc=$pdo->query($reqinvc);
                            // $row_invc=$invc->fetch();
 

  
    if(!$invite){
        echo "<div class='error' align=\"left\" style=\"color:red;font-weight:bold;text-align:center;\">" . htmlspecialchars($audienceLabels['name_required'], ENT_QUOTES, 'UTF-8') . "</div>";
  }else{

   

              $sql = "UPDATE invite SET nom=:nom,sing=:sing,siege=:siege where id_inv = :id_inv";
            
              $q = $pdo->prepare($sql); 
              $q->bindValue(':nom',  $invite); 
              $q->bindValue(':sing',  $sing); 
              $q->bindValue(':siege',  $nom_table); 
              $q->bindValue(':id_inv',$cod_inv);
              $q->execute();
              $q->closeCursor(); 

              echo '<script>
              Swal.fire({
                  title: "Modification !",
                  text: "La modification a été effectuée avec succès.",
                  icon: "success",
                  confirmButtonText: "OK"
              }).then((result) => {
                  if (result.isConfirmed) {
                      window.location.href = "index.php?page=mb_accueil"; // Rédirection vers la page de détails
                  }
              });
              </script>';

              }

  }

 ?>


							</div>



							<div class="p-40"> 

                            <form action="" method="post">  
                                <div class="mb-action-radio-grid">
                                    <div class="mb-action-option">
                                        <input class="mb-action-option-input" type="radio" name="type_invite" id="monsieur" value="Mr" <?php echo $radiom;?>>
                                        <label class="mb-action-option-card" for="monsieur">
                                            <span class="mb-action-option-icon"><i class="mdi mdi-account-tie"></i></span>
                                            <span class="mb-action-option-copy"><strong>Monsieur</strong><span>Invitation individuelle</span></span>
                                        </label>
                                    </div>
                                    <div class="mb-action-option">
                                        <input class="mb-action-option-input" type="radio" name="type_invite" id="madame" value="Mme" <?php echo $radiomm;?>>
                                        <label class="mb-action-option-card" for="madame">
                                            <span class="mb-action-option-icon"><i class="mdi mdi-account-star"></i></span>
                                            <span class="mb-action-option-copy"><strong>Madame</strong><span>Invitation individuelle</span></span>
                                        </label>
                                    </div>
                                    <div class="mb-action-option">
                                        <input class="mb-action-option-input" type="radio" name="type_invite" id="couple" value="C" <?php echo $radioc;?>>
                                        <label class="mb-action-option-card" for="couple">
                                            <span class="mb-action-option-icon"><i class="mdi mdi-heart-multiple"></i></span>
                                            <span class="mb-action-option-copy"><strong>Couple</strong><span>Invitation a deux personnes</span></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control ps-15 bg-transparent" value="<?php echo htmlspecialchars((string) ($datainvite['nom'] ?? ''), ENT_QUOTES, 'UTF-8');?>" name="invite" placeholder="Noms">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-chair"></i></span>
                                        <select class="form-control ps-15 bg-transparent" name="tableevent">
                                            <option style="color:#eee;" value="<?php echo htmlspecialchars((string) ($datainvite['siege'] ?? ''), ENT_QUOTES, 'UTF-8');?>"><?php echo htmlspecialchars((string) $table, ENT_QUOTES, 'UTF-8');?></option>
                                            <?php foreach (EventTableService::listByEvent($pdo, (int) $codevent) as $data_table) {
                                            ?>
                                            <option value="<?php echo $data_table['cod_tab']?>" <?php if(@$_POST['tableevent'] == $data_table['cod_tab']){echo "selected";} ?>><?php echo $data_table['nom_tab']?></option>
                                            <?php } ?>  
                                        </select>
                                    </div>
                                </div>


                                <div class="row"> 
                                    <div class="col-12 text-center">
										<button type="submit" name="submit" class="btn btn-primary w-p100 mt-10 mb-action-submit">Enregistrer les modifications</button>
                                    </div>
                                </div>

                                <a href="#" class="mb-action-danger-link" title="Suppression" onclick="confirmSuppInv(event)"><i class="mdi mdi-delete-outline"></i> Supprimer <?php echo htmlspecialchars((string) ($datainvite['nom'] ?? ''), ENT_QUOTES, 'UTF-8');?></a>
                            </form>			
								<div class="text-center">
                                   
								
<script>
    function confirmSuppInv(event) {
        event.preventDefault(); // Empêche le lien de se déclencher
        Swal.fire({
            title: "Supprimer !",
            text: "Êtes-vous sûr de vouloir supprimer <?php echo htmlspecialchars((string) ($datainvite['nom'] ?? ''), ENT_QUOTES, 'UTF-8');?> ?",
            icon: "warning", // Utilisez "warning" pour une alerte de confirmation
            showCancelButton: true,
            confirmButtonText: "Oui",
            cancelButtonText: "Non"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "index.php?page=supinv&idinv=<?php echo $cod_inv;?>";
            }
        });
    }
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
	 
	