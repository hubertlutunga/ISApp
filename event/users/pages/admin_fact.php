
		
<div class="wrapper"> 
	 

     <?php include('header_admin.php');?>
      
<?php 



$codget = isset($_GET['cod']) ? (int) $_GET['cod'] : 0;
$dataevent = $codget > 0 ? EventUpdateService::findEventById($pdo, $codget) : [];


?>   



   
     <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
               <div class="container-full">
               <!-- Main content -->
                
                
               
               <div class="container h-p100">
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
				<div class="row justify-content-center g-0">
					<div class="col-lg-5 col-md-5 col-12 boxcontent">
						<div class="bg-white rounded10 shadow-lg ">
							<div class="content-top-agile p-20 pb-0"> 
                                <p class="mb-0 text-fade">Commande N°<?php echo $codget;?></p>							
							</div>
							<div class="p-40">


<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $currentUserId = (int) (($datasession['cod_user'] ?? ($_SESSION['cod_user'] ?? 0)) ?: 0);

    try {
        EventCreationService::createLegacyEvent(
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
}

?>

 



<form id="eventForm" action="" method="post" enctype="multipart/form-data">

 








<?php 
                                            
                                            
                                            $stmtae = $pdo->prepare("SELECT * FROM accessoires_event where cod_event = ? ORDER BY cod_accev DESC");
                                            $stmtae->execute([$codget]); 
                
                                            while ($dataae = $stmtae->fetch(PDO::FETCH_ASSOC)) {
                
                
                                                $stmtnv = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
                                                $stmtnv->execute([$dataae['cod_acc']]); // Correction ici pour utiliser $codevent
                                                $data_accessoire = $stmtnv->fetch();
                
                                                $accessoire = isset($data_accessoire['nom']) ? $data_accessoire['nom'] : '';
                                                $accessoireNomLower = function_exists('mb_strtolower') ? mb_strtolower($accessoire, 'UTF-8') : strtolower($accessoire);
                                                $accessoireNomNormalized = str_replace(['é', 'è', 'ê', 'ë'], 'e', $accessoireNomLower);
                                                $isInvitationElectronique = strpos($accessoireNomNormalized, 'invitation') !== false && strpos($accessoireNomNormalized, 'elect') !== false;
                                                $quantiteAccessoire = isset($dataae['quantite']) ? (int) $dataae['quantite'] : 1;
                
                                                if ($dataae['cod_acc'] == "1") {
                
                                                    $stmtmi = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
                                                    $stmtmi->execute([$dataevent['modele_inv']]); // Correction ici pour utiliser $codevent
                                                    $data_modele = $stmtmi->fetch();
                
                                                    $modele_inv = isset($data_modele['nom']) ? '('.$data_modele['nom'].')' : '';
                
                                                }elseif ($dataae['cod_acc'] == "3") {
                
                                                    $stmtmc = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
                                                    $stmtmc->execute([$dataevent['modele_chev']]); // Correction ici pour utiliser $codevent
                                                    $data_modelechev = $stmtmc->fetch();
                
                                                    $modele_inv = isset($data_modelechev['nom']) ? '('.$data_modelechev['nom'].')' : '';
                
                                                }else{
                                                    $modele_inv = '';
                                                }
                
                                                ?>  
                                               
                                                <div class="form-group">
                                                <span for=""><?php echo $accessoire.' '.$modele_inv?></span> 
                                                    <div class="input-group mb-3"> 
                                                    <span class="input-group-text bg-transparent"><i class="fas fa-shopping-cart"></i></span>
                                                        <input type="<?php echo $isInvitationElectronique ? 'text' : 'number'; ?>" name="adresse" class="form-control ps-15 bg-transparent" placeholder="Quantité" value="<?php echo $isInvitationElectronique ? 'Illimité' : max(1, $quantiteAccessoire); ?>" <?php echo $isInvitationElectronique ? 'readonly' : 'min="1" step="1"'; ?>>
                                                    </div>


                                                    <div class="input-group mb-3"> 
                                                    <span class="input-group-text bg-transparent"><i class="fas fa-dollar-sign"></i></span>
                                                        <input type="text" name="adresse" class="form-control ps-15 bg-transparent" placeholder="Prix Unitaire">
                                                    </div>
                                                </div>

 
                                               
                                               <?php
                
                                                }
                                                                
                                                ?>

       

 
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span> 
            <textarea name="details" class="form-control ps-15 bg-transparent" rows='5' placeholder="Instruction"></textarea>
        </div>
    </div>
  
    
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
            <input type="date" name="dateliv" class="form-control ps-15 bg-transparent" placeholder="Date de livraison">
        </div>
    </div>

    <div class="row"> 
        <div class="col-12 text-center">
            <button type="submit" id="BtnEvent" class="btn btn-primary w-p100 mt-10">Enregistrer</button>
        </div>
    </div>
</form>
			
 
  
 
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