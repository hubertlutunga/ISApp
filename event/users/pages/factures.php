
	
<div class="wrapper"> 
	 

	 <?php include('header_admin.php');?>
	  
	
   
	 <!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			   <div class="container-full">
			   <!-- Main content -->

			   <section class="content">
   
   
   
   
			   <div class="row">
				   <div class="col-12">
   
   
   
				   <div class="row">
   
   
   
<?php
// ====== STATISTIQUES ENTREE (FACTURES) ======
date_default_timezone_set('Africa/Kinshasa');

// IMPORTANT : on utilise uniquement date_enreg
$DATE_COL_ENT = "date_enreg";

// BORNES
$now = new DateTime('now');
$jourStart  = (clone $now)->setTime(0,0,0)->format('Y-m-d H:i:s');
$jourEnd    = (clone $now)->setTime(23,59,59)->format('Y-m-d H:i:s');
$moisStart  = (new DateTime('first day of this month 00:00:00'))->format('Y-m-d H:i:s');
$moisEnd    = (new DateTime('last day of this month 23:59:59'))->format('Y-m-d H:i:s');
$anneeStart = (new DateTime(date('Y').'-01-01 00:00:00'))->format('Y-m-d H:i:s');
$anneeEnd   = (new DateTime(date('Y').'-12-31 23:59:59'))->format('Y-m-d H:i:s');

// Total global (montant_paye)
$totGlobalEnt = (float)$pdo->query("SELECT IFNULL(SUM(montant_paye),0) FROM facture")->fetchColumn();

// Par jour
$qJourEnt = $pdo->prepare("SELECT IFNULL(SUM(montant_paye),0) FROM facture WHERE $DATE_COL_ENT BETWEEN :a AND :b");
$qJourEnt->execute([':a'=>$jourStart, ':b'=>$jourEnd]);
$totJourEnt = (float)$qJourEnt->fetchColumn();

// Par mois
$qMoisEnt = $pdo->prepare("SELECT IFNULL(SUM(montant_paye),0) FROM facture WHERE $DATE_COL_ENT BETWEEN :a AND :b");
$qMoisEnt->execute([':a'=>$moisStart, ':b'=>$moisEnd]);
$totMoisEnt = (float)$qMoisEnt->fetchColumn();

// Par année
$qAnneeEnt = $pdo->prepare("SELECT IFNULL(SUM(montant_paye),0) FROM facture WHERE $DATE_COL_ENT BETWEEN :a AND :b");
$qAnneeEnt->execute([':a'=>$anneeStart, ':b'=>$anneeEnd]);
$totAnneeEnt = (float)$qAnneeEnt->fetchColumn();

// ====== Série 12 mois (entrées) ======
$start12 = (new DateTime('first day of -11 months 00:00:00'))->format('Y-m-d H:i:s');
$end12   = (new DateTime('last day of this month 23:59:59'))->format('Y-m-d H:i:s');

$sql12Ent = "
  SELECT DATE_FORMAT($DATE_COL_ENT, '%Y-%m') ym, SUM(montant_paye) total
  FROM facture
  WHERE $DATE_COL_ENT BETWEEN :s AND :e
  GROUP BY ym
  ORDER BY ym ASC";
$st12Ent = $pdo->prepare($sql12Ent);
$st12Ent->execute([':s'=>$start12, ':e'=>$end12]);
$rows12Ent = $st12Ent->fetchAll(PDO::FETCH_KEY_PAIR);

// Construit labels/values complets (0 si mois manquant)
$labelsEnt = [];
$valuesEnt = [];
$cursor = new DateTime('first day of -11 months 00:00:00');
for ($i=0; $i<12; $i++) {
  $key = $cursor->format('Y-m');
  $labelsEnt[] = $cursor->format('M Y');
  $valuesEnt[] = isset($rows12Ent[$key]) ? (float)$rows12Ent[$key] : 0.0;
  $cursor->modify('+1 month');
}

// Expose au JS
$STAT_LABELS_ENT = json_encode($labelsEnt, JSON_UNESCAPED_UNICODE);
$STAT_VALUES_ENT = json_encode($valuesEnt, JSON_UNESCAPED_UNICODE);

// Helper d'affichage
function usd($n){ return number_format((float)$n, 2, '.', ' '); }
?>


   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
    
   
   <div class="row" id='mesinv'>
	   <div class="col-xxl-12 col-xl-12 col-lg-12">
		   <div class="card rounded-4">
			   <div class="box-header d-flex b-0 justify-content-between align-items-center">
				   <h4 class="box-title">Entrées</h4>
				  
			   </div>
   








			   <div class="card-body pt-0">















			   <div class="row">
  <!-- KPI Jour -->
  <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
    <div class="card rounded-4 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Entrées journalières</div>
            <div class="h4 my-1"><?php echo usd($totJourEnt); ?> $</div>
            <div class="small text-fade"><?php echo date('d M Y'); ?></div>
          </div>
          <div class="avatar bg-success-light rounded-3 p-3">
            <i class="fas fa-calendar-day fa-lg text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- KPI Mois -->
  <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
    <div class="card rounded-4 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Entrées du mois</div>
            <div class="h4 my-1"><?php echo usd($totMoisEnt); ?> $</div>
            <div class="small text-fade"><?php echo ucfirst((new DateTime())->format('F Y')); ?></div>
          </div>
          <div class="avatar bg-info-light rounded-3 p-3">
            <i class="fas fa-calendar-alt fa-lg text-info"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- KPI Année -->
  <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
    <div class="card rounded-4 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Entrées de l'année</div>
            <div class="h4 my-1"><?php echo usd($totAnneeEnt); ?> $</div>
            <div class="small text-fade"><?php echo date('Y'); ?></div>
          </div>
          <div class="avatar bg-warning-light rounded-3 p-3">
            <i class="fas fa-calendar fa-lg text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- KPI Global -->
  <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
    <div class="card rounded-4 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Entrées (global)</div>
            <div class="h4 my-1"><?php echo usd($totGlobalEnt); ?> $</div>
            <div class="small text-fade">Depuis le début</div>
          </div>
          <div class="avatar bg-primary-light rounded-3 p-3">
            <i class="fas fa-coins fa-lg text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Graphique 12 derniers mois -->
  <div class="col-12">
    <div class="card rounded-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Évolution des entrées (12 derniers mois)</h5>
        <div class="text-muted small">
          <?php echo date('d M Y à H:i'); ?>
        </div>
      </div>
      <div class="card-body">
        <div id="entreesChart" style="height: 320px;"></div>
      </div>
    </div>
  </div>
</div>




				   <div class="table-responsive">
					   <table class="table mb-0">
						   <tbody>


 <?php 
$stmtfact = $pdo->prepare("SELECT * FROM facture ORDER BY date_enreg DESC");
$stmtfact->execute();

if ($stmtfact->rowCount() > 0) {
    while ($row_fact = $stmtfact->fetch(PDO::FETCH_ASSOC)) { 
        $stmtus = $pdo->prepare("SELECT * FROM is_users WHERE cod_user = :cod_user");
        $stmtus->execute(['cod_user' => $row_fact['cod_cli']]); 
        $datauser = $stmtus->fetch(PDO::FETCH_ASSOC); 
?>
        <tr>
            <td class="pt-0 px-0 b-0">
                <a class="d-block fw-500 fs-14" href="#"><?php echo htmlspecialchars(ucfirst($datauser['noms'])).', <em>'.$row_fact['type_paie'].'</em> '.number_format($row_fact['montant_paye'], 2, ' ', ' ').' $'; ?></a>
                <span class="text-fade"><?php echo 'Référence : '.$row_fact['reference']; ?>, payé le <?php echo date('d M Y à H:i', strtotime($row_fact['date_enreg'])); ?></span>
            </td> 

            <td class="text-end b-0 pt-0 px-0" width="15%"> 
				

				<!-- 
				<iframe src="pages/pdf/facture_hs.php?cod=<?php // echo $row_fact['reference']; ?>" id="rapvent_<?php // echo $row_fact['reference']; ?>" style="display: none;"></iframe>    

				<a onclick="print_rapvent('<?php // echo $row_fact['reference']; ?>')" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm" href="#" style="color:#aaa;"> 
					Imprimer <i class="fas fa-print"></i>
				</a>  
				 -->









 
											   <div class="list-icons d-inline-flex">
                          <div class="list-icons-item dropdown">
                                          
   
                           <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                               <div class="dropdown-menu dropdown-menu-end">
   
											   <a class="dropdown-item" target="_blink" href="pages/pdf/facture_hs.php?cod=<?php echo $row_fact['reference']; ?>"><i class="fas fa-print"></i> Imprimer </a>
											    
            
											 <a class="dropdown-item" href="#" style="color:red;" title="Suppression" onclick="confirmSuppFact(event, '<?php echo $row_fact['reference']; ?>')">
												<i class="fa fa-remove"></i> Supprimer
											</a>

<script>
    function confirmSuppFact(event, cod) {
        event.preventDefault(); // Empêche le lien de se déclencher
        Swal.fire({
            title: "Supprimer !",
            text: "Êtes-vous sûr de vouloir supprimer cette facture ?",
            icon: "warning", // Utilisez "warning" pour une alerte de confirmation
            showCancelButton: true,
            confirmButtonText: "Oui",
            cancelButtonText: "Non"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "index.php?page=supfact&cod=" + encodeURIComponent(cod);
            }
        });
    }
</script>
      
       
                        </div>
                         </div>
                       </div>

            </td>
        </tr>
<?php 
    }
} else {
    echo '<tr><td colspan="3" class="text-left" style="font-style:italic;">Aucune entrée trouvé</td></tr>';
}
?>

<script>
function print_rapvent(reference) {
    var frame = document.getElementById('rapvent_' + reference);
    frame.contentWindow.focus();
    frame.contentWindow.print();
}
</script>
 
						   </tbody>
					   </table>
				   </div>
			   </div>	
		   </div>
	   </div>
   </div>
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
	
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
	 
					   </div>
   
					   
				   </div> 
			   </section>
			   <!-- /.content -->
		   </div>
	 <!-- /.content-wrapper -->
	   <?php include('footer.php')?>
	 <!-- Side panel --> 
 
 
   
	 
	 
   </div>
   <!-- ./wrapper -->
	   
	   
		   
	   <div id="chat-box-body">
		   <div id="chat-circle" class="waves-effect waves-circle btn btn-circle btn-sm btn-warning l-h-50">
			   <div id="chat-overlay"></div>
			   <span class="icon-Group-chat fs-18"><span class="path1"></span><span class="path2"></span></span>
		   </div>
   
		   <div class="chat-box">
			   <div class="chat-box-header p-15 d-flex justify-content-between align-items-center">
				   <div class="btn-group">
					 <button class="waves-effect waves-circle btn btn-circle btn-primary-light h-40 w-40 rounded-circle l-h-45" type="button" data-bs-toggle="dropdown">
						 <span class="icon-Add-user fs-22"><span class="path1"></span><span class="path2"></span></span>
					 </button>
					 <div class="dropdown-menu min-w-200">
					   <a class="dropdown-item fs-16" href="#">
						   <span class="icon-Color me-15"></span>
						   New Group</a>
					   <a class="dropdown-item fs-16" href="#">
						   <span class="icon-Clipboard me-15"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
						   Contacts</a>
					   <a class="dropdown-item fs-16" href="#">
						   <span class="icon-Group me-15"><span class="path1"></span><span class="path2"></span></span>
						   Groups</a>
					   <a class="dropdown-item fs-16" href="#">
						   <span class="icon-Active-call me-15"><span class="path1"></span><span class="path2"></span></span>
						   Calls</a>
					   <a class="dropdown-item fs-16" href="#">
						   <span class="icon-Settings1 me-15"><span class="path1"></span><span class="path2"></span></span>
						   Settings</a>
					   <div class="dropdown-divider"></div>
					   <a class="dropdown-item fs-16" href="#">
						   <span class="icon-Question-circle me-15"><span class="path1"></span><span class="path2"></span></span>
						   Help</a>
					   <a class="dropdown-item fs-16" href="#">
						   <span class="icon-Notifications me-15"><span class="path1"></span><span class="path2"></span></span> 
						   Privacy</a>
					 </div>
				   </div>
				   <div class="text-center flex-grow-1">
					   <div class="text-dark fs-18">Support</div>
					   <div>
						   <span class="badge badge-sm badge-dot badge-primary"></span>
						   <span class="text-muted fs-12">Active</span>
					   </div>
				   </div>
				   <div class="chat-box-toggle">
					   <button id="chat-box-toggle" class="waves-effect waves-circle btn btn-circle btn-danger-light h-40 w-40 rounded-circle l-h-45" type="button">
						 <span class="icon-Close fs-22"><span class="path1"></span><span class="path2"></span></span>
					   </button>                    
				   </div>
			   </div>
			   <div class="chat-box-body">
				   
				   <?php // include ('chatsupport.php')?>
   
			   </div>
			   <div class="chat-input">      
				   <form>
					   <input type="text" id="chat-input" placeholder="Besoin d'aide ?"/>
					   <button type="submit" class="chat-submit" id="chat-submit">
						   <span class="icon-Send fs-22"></span>
					   </button>
				   </form>      
			   </div>
		   </div>
	   </div>
	   
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











<script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {
  const labels = <?php echo $STAT_LABELS_ENT ?? '[]'; ?>;
  const values = <?php echo $STAT_VALUES_ENT ?? '[]'; ?>;

  const container = document.querySelector('#entreesChart');
  if (!container) return;

  const isEmpty = !Array.isArray(values) || values.length === 0 || values.every(v => Number(v) === 0);
  if (isEmpty) {
    container.innerHTML = '<div style="padding:8px;color:#999;">Aucune entrée sur la période.</div>';
  }

  try {
    const chart = new ApexCharts(container, {
      chart: { type: 'area', height: 320, toolbar: { show: false } },
      series: [{ name: 'Entrées', data: values }],
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2 },
      xaxis: { categories: labels },
      yaxis: { labels: { formatter: v => Number(v).toLocaleString('fr-FR') + ' $' } },
      tooltip: { y: { formatter: v => Number(v).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' $' } },
      fill: { opacity: 0.25 }
    });
    chart.render();
  } catch (e) {
    console.error('Erreur ApexCharts (Entrées):', e);
    container.innerHTML = '<div style="padding:8px;color:#c00;">Erreur d’affichage du graphique.</div>';
  }
});
</script>

		 