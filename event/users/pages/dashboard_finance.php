  <?php
// dashboard_finance.php 
date_default_timezone_set('Africa/Kinshasa');
 

// ===== Helpers =====
function usd($n){ return number_format((float)$n, 2, '.', ' '); }

// ===== Colonne de date =====
$DATE_SORTIE = "COALESCE(dateop, date_enreg)"; // sorties_finance
$DATE_ENTREE = "date_enreg";                    // facture (tu n’as pas date_paie)

// ===== Bornes temporelles =====
$now        = new DateTime('now');
$jourStart  = (clone $now)->setTime(0,0,0)->format('Y-m-d H:i:s');
$jourEnd    = (clone $now)->setTime(23,59,59)->format('Y-m-d H:i:s');

$moisStart  = (new DateTime('first day of this month 00:00:00'))->format('Y-m-d H:i:s');
$moisEnd    = (new DateTime('last day of this month 23:59:59'))->format('Y-m-d H:i:s');

$anneeStart = (new DateTime(date('Y').'-01-01 00:00:00'))->format('Y-m-d H:i:s');
$anneeEnd   = (new DateTime(date('Y').'-12-31 23:59:59'))->format('Y-m-d H:i:s');

// ===== Totaux GLOBAUX =====
$totEntGlobal = (float)$pdo->query("SELECT IFNULL(SUM(montant_paye),0) FROM facture")->fetchColumn();
$totSortGlobal = (float)$pdo->query("SELECT IFNULL(SUM(montant),0) FROM sortie_finance")->fetchColumn();
$soldeGlobal = $totEntGlobal - $totSortGlobal;

// ===== Totaux MOIS =====
$qEntM = $pdo->prepare("SELECT IFNULL(SUM(montant_paye),0) FROM facture WHERE $DATE_ENTREE BETWEEN :a AND :b");
$qEntM->execute([':a'=>$moisStart, ':b'=>$moisEnd]);
$totEntMois = (float)$qEntM->fetchColumn();

$qSortM = $pdo->prepare("SELECT IFNULL(SUM(montant),0) FROM sortie_finance WHERE $DATE_SORTIE BETWEEN :a AND :b");
$qSortM->execute([':a'=>$moisStart, ':b'=>$moisEnd]);
$totSortMois = (float)$qSortM->fetchColumn();

$soldeMois = $totEntMois - $totSortMois;

// ===== Totaux JOUR (si tu veux les afficher plus tard) =====
$qEntJ = $pdo->prepare("SELECT IFNULL(SUM(montant_paye),0) FROM facture WHERE $DATE_ENTREE BETWEEN :a AND :b");
$qEntJ->execute([':a'=>$jourStart, ':b'=>$jourEnd]);
$totEntJour = (float)$qEntJ->fetchColumn();

$qSortJ = $pdo->prepare("SELECT IFNULL(SUM(montant),0) FROM sortie_finance WHERE $DATE_SORTIE BETWEEN :a AND :b");
$qSortJ->execute([':a'=>$jourStart, ':b'=>$jourEnd]);
$totSortJour = (float)$qSortJ->fetchColumn();

$soldeJour = $totEntJour - $totSortJour;

// ===== Répartition Année =====
$qEntA = $pdo->prepare("SELECT IFNULL(SUM(montant_paye),0) FROM facture WHERE $DATE_ENTREE BETWEEN :a AND :b");
$qEntA->execute([':a'=>$anneeStart, ':b'=>$anneeEnd]);
$totEntAnnee = (float)$qEntA->fetchColumn();

$qSortA = $pdo->prepare("SELECT IFNULL(SUM(montant),0) FROM sortie_finance WHERE $DATE_SORTIE BETWEEN :a AND :b");
$qSortA->execute([':a'=>$anneeStart, ':b'=>$anneeEnd]);
$totSortAnnee = (float)$qSortA->fetchColumn();

// ===== Série 12 derniers mois (comparatif Entrées/Sorties) =====
$start12 = (new DateTime('first day of -11 months 00:00:00'))->format('Y-m-d H:i:s');
$end12   = (new DateTime('last day of this month 23:59:59'))->format('Y-m-d H:i:s');

$sql12E = "
  SELECT DATE_FORMAT($DATE_ENTREE, '%Y-%m') ym, SUM(montant_paye) total
  FROM facture
  WHERE $DATE_ENTREE BETWEEN :s AND :e
  GROUP BY ym
  ORDER BY ym";
$st12E = $pdo->prepare($sql12E);
$st12E->execute([':s'=>$start12, ':e'=>$end12]);
$rowsE = $st12E->fetchAll(PDO::FETCH_KEY_PAIR); // ['2025-01'=>xxx]

$sql12S = "
  SELECT DATE_FORMAT($DATE_SORTIE, '%Y-%m') ym, SUM(montant) total
  FROM sortie_finance
  WHERE $DATE_SORTIE BETWEEN :s AND :e
  GROUP BY ym
  ORDER BY ym";
$st12S = $pdo->prepare($sql12S);
$st12S->execute([':s'=>$start12, ':e'=>$end12]);
$rowsS = $st12S->fetchAll(PDO::FETCH_KEY_PAIR);

// Construit labels + valeurs (0 si manquant)
$labels = [];
$valuesE = [];
$valuesS = [];
$cursor = new DateTime('first day of -11 months 00:00:00');
for ($i=0; $i<12; $i++) {
  $key = $cursor->format('Y-m');
  $labels[]  = $cursor->format('M Y');     // affichage
  $valuesE[] = isset($rowsE[$key]) ? (float)$rowsE[$key] : 0.0;
  $valuesS[] = isset($rowsS[$key]) ? (float)$rowsS[$key] : 0.0;
  $cursor->modify('+1 month');
}

// Expose au JS
$JS_LABELS  = json_encode($labels, JSON_UNESCAPED_UNICODE);
$JS_SER_ENT = json_encode($valuesE, JSON_UNESCAPED_UNICODE);
$JS_SER_SOR = json_encode($valuesS, JSON_UNESCAPED_UNICODE);
?>







































<?php
date_default_timezone_set('Africa/Kinshasa'); 

// === AJAX: recalcul sur une période ===
if (isset($_GET['range_stats'])) {
  header('Content-Type: application/json; charset=utf-8');

  // Sécurise les entrées (YYYY-MM-DD)
  $d1 = $_POST['date_deb'] ?? '';
  $d2 = $_POST['date_fin'] ?? '';
  if (!$d1 || !$d2) {
    echo json_encode(['ok'=>false,'msg'=>'Dates manquantes']); exit;
  }

  // borne début = 00:00:00, fin = 23:59:59
  $debut = date('Y-m-d 00:00:00', strtotime($d1));
  $fin   = date('Y-m-d 23:59:59', strtotime($d2));

  // colonnes
  $DATE_SORTIE = "COALESCE(dateop, date_enreg)";
  $DATE_ENTREE = "date_enreg";

  // KPIs période
  $qE = $pdo->prepare("SELECT IFNULL(SUM(montant_paye),0) FROM facture WHERE $DATE_ENTREE BETWEEN :a AND :b");
  $qE->execute([':a'=>$debut, ':b'=>$fin]);
  $ent = (float)$qE->fetchColumn();

  $qS = $pdo->prepare("SELECT IFNULL(SUM(montant),0) FROM sortie_finance WHERE $DATE_SORTIE BETWEEN :a AND :b");
  $qS->execute([':a'=>$debut, ':b'=>$fin]);
  $sor = (float)$qS->fetchColumn();

  $solde = $ent - $sor;

  // Séries par MOIS dans la période (max 24 mois conseillé)
  $sqlE = "
    SELECT DATE_FORMAT($DATE_ENTREE, '%Y-%m') ym, SUM(montant_paye) total
    FROM facture
    WHERE $DATE_ENTREE BETWEEN :a AND :b
    GROUP BY ym ORDER BY ym";
  $stE = $pdo->prepare($sqlE); $stE->execute([':a'=>$debut, ':b'=>$fin]);
  $rowsE = $stE->fetchAll(PDO::FETCH_KEY_PAIR);

  $sqlS = "
    SELECT DATE_FORMAT($DATE_SORTIE, '%Y-%m') ym, SUM(montant) total
    FROM sortie_finance
    WHERE $DATE_SORTIE BETWEEN :a AND :b
    GROUP BY ym ORDER BY ym";
  $stS = $pdo->prepare($sqlS); $stS->execute([':a'=>$debut, ':b'=>$fin]);
  $rowsS = $stS->fetchAll(PDO::FETCH_KEY_PAIR);

  // construit les mois entre d1 et d2
  $labels = []; $serieE = []; $serieS = [];
  $cur = (new DateTime(date('Y-m-01', strtotime($d1))));
  $end = (new DateTime(date('Y-m-01', strtotime($d2)))); $end->modify('+1 month'); // exclusif

  $i=0; $guard=0;
  while ($cur < $end && $guard < 48) { // garde-fou 48 mois
    $k = $cur->format('Y-m');
    $labels[] = $cur->format('M Y');
    $serieE[] = isset($rowsE[$k]) ? (float)$rowsE[$k] : 0.0;
    $serieS[] = isset($rowsS[$k]) ? (float)$rowsS[$k] : 0.0;
    $cur->modify('+1 month'); $guard++;
  }

  echo json_encode([
    'ok'=>true,
    'kpi'=>['ent'=>$ent, 'sor'=>$sor, 'solde'=>$solde],
    'labels'=>$labels,
    'serieE'=>$serieE,
    'serieS'=>$serieS
  ], JSON_UNESCAPED_UNICODE);
  exit;
}
?>
	
<div class="wrapper"> 
	 

	 <?php include('header_admin.php');?>
	  
	
   
	 <!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			   <div class="container-full">
			   <!-- Main content -->

			   <section class="content">
   
    
    
   <style>
    input,textarea,select{font-size:16px!important}
   </style>
 
  


   
   
   
   




















 <!-- Bouton existe déjà: #btnSortie -->

<!-- Modal période -->
<div id="periodeModal" class="modalinv" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h4 style="margin:0;">Consulter une période</h4>
      <button type="button" class="close" aria-label="Fermer" onclick="closePeriode()">&times;</button>
    </div>
    <div class="modal-body" style="margin-top:10px;">
      <form id="periodeForm">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Du</label>
            <input type="date" name="date_deb" id="date_deb" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Au</label>
            <input type="date" name="date_fin" id="date_fin" class="form-control">
          </div>
        </div>
        <div class="text-end">
          <button type="button" class="btn btn-secondary" onclick="closePeriode()">Annuler</button>
          <button type="submit" class="btn btn-primary">Afficher</button>
        </div>
      </form>
      <div id="periodeMsg" class="small text-muted mt-2"></div>
    </div>
  </div>
</div>

<style>
.modalinv{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.45);z-index:9999;}
.modalinv .modal-content{max-width:640px;width:95%;background:#fff;border-radius:12px;padding:16px;box-shadow:0 10px 40px rgba(0,0,0,.25);}
.modalinv .modal-header{display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eee;}
.modalinv .close{background:transparent;border:0;font-size:28px;line-height:1;cursor:pointer;}
</style>




























   
   
   
   
   
    
   
<div class="row">
	<div class="col-xxl-12 col-xl-12 col-lg-12">
		<div class="card rounded-4">
			   <div class="box-header d-flex b-0 justify-content-between align-items-center">
				   <h4 class="box-title">Dashboard Financier</h4>
				  

                        <ul class="m-0" style="list-style: none;">
                            <li class="dropdown">
                              
                            
                                <a href="#" id="btnSortie"  class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm">
                                    <i class="fa fa-fw fa-calendar"></i> Consulter une période
                                </a>

 
                            </li>
                        </ul>
  
                        
			   </div>
   





 

			   <div class="card-body pt-0">




 

        <!-- ===== KPIs principaux ===== -->
        <div class="row">
          <!-- Entrées (Mois) -->
          <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
            <div class="card rounded-4 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-muted small">Entrées du mois</div>
                    <div class="h4 my-1"><span id="kpiEnt"><?php echo usd($totEntMois); ?></span> $</div>
                    <div class="small text-fade"><?php echo ucfirst((new DateTime())->format('F Y')); ?></div>
                  </div>
                  <div class="avatar bg-success-light rounded-3 p-3">
                    <i class="fas fa-arrow-down fa-lg text-success"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sorties (Mois) -->
          <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
            <div class="card rounded-4 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-muted small">Sorties du mois</div>
                    <div class="h4 my-1"><span id="kpiSor"><?php echo usd($totSortMois); ?></span> $</div>
                    <div class="small text-fade"><?php echo ucfirst((new DateTime())->format('F Y')); ?></div>
                  </div>
                  <div class="avatar bg-danger-light rounded-3 p-3">
                    <i class="fas fa-arrow-up fa-lg text-danger"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Solde (Mois) -->
          <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
            <div class="card rounded-4 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-muted small">Reste à la caisse (mois)</div>
                    <div class="h4 my-1"><span id="kpiSolde"><?php echo usd($soldeMois); ?></span> $</div>
                    <div class="small text-fade">Entrées - Sorties</div>
                  </div>
                  <div class="avatar bg-info-light rounded-3 p-3">
                    <i class="fas fa-balance-scale fa-lg text-info"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Solde Global -->
          <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
            <div class="card rounded-4 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-muted small">Reste à la caisse (global)</div>
                    <div class="h4 my-1"><?php echo usd($soldeGlobal); ?> $</div>
                    <div class="small text-fade">Depuis le début</div>
                  </div>
                  <div class="avatar bg-primary-light rounded-3 p-3">
                    <i class="fas fa-piggy-bank fa-lg text-primary"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== Graphiques ===== -->
        <div class="row">
          <!-- Comparatif 12 mois -->
          <div class="col-xxl-8 col-xl-7 col-lg-12">
            <div class="card rounded-4 shadow-sm">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Entrées vs Sorties (12 derniers mois)</h5>
                <div class="text-muted small"><?php echo date('d M Y à H:i'); ?></div>
              </div>
              <div class="card-body">
                <div id="chart12mois" style="height: 360px;"></div>
              </div>
            </div>
          </div>

          <!-- Répartition Année -->
          <div class="col-xxl-4 col-xl-5 col-lg-12">
            <div class="card rounded-4 shadow-sm">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Année <?php echo date('Y'); ?> — Répartition</h5>
              </div>
              <div class="card-body">
                <div class="mb-3 small text-fade">
                  Entrées : <strong><?php echo usd($totEntAnnee); ?> $</strong> &nbsp;|&nbsp;
                  Sorties : <strong><?php echo usd($totSortAnnee); ?> $</strong>
                </div>
                <div id="chartDonut" style="height: 320px;"></div>
              </div>
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
 
	 <!-- /.control-sidebar -->
 
	 
	 
   </div>
   <!-- ./wrapper -->
	   
	   
 
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
		 







<script>
(function(){
  // Ouverture/fermeture modal
  const modal = document.getElementById('periodeModal');
  const btn   = document.getElementById('btnSortie');
  const form  = document.getElementById('periodeForm');
  const msg   = document.getElementById('periodeMsg');

  function openPeriode(){
    // Préremplir par défaut: du 1er du mois courant à aujourd'hui
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth()+1).padStart(2,'0');
    const dd = String(now.getDate()).padStart(2,'0');
    const first = `${yyyy}-${mm}-01`;
    const today = `${yyyy}-${mm}-${dd}`;
    document.getElementById('date_deb').value ||= first;
    document.getElementById('date_fin').value ||= today;

    modal.style.display='flex';
    setTimeout(()=> document.getElementById('date_deb').focus(), 0);
  }
  function closePeriode(){ modal.style.display='none'; msg.textContent=''; }
  window.closePeriode = closePeriode;

  if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); openPeriode(); });
  modal.addEventListener('click', e=>{ if(e.target===modal) closePeriode(); });
  document.addEventListener('keydown', e=>{ if(e.key==='Escape') closePeriode(); });

  // === INSTANCE DES CHARTS EXISTANTS (on réutilise pour update) ===
  // On capture les charts créés ailleurs ? Sinon on (re)crée proprement ici.
  let chart12, chartDonut;

  function ensureCharts(labelsInit, serieEInit, serieSInit, totEntAnnee, totSortAnnee){
    // 12 mois
    const el12 = document.querySelector('#chart12mois');
    if (el12 && !chart12) {
      chart12 = new ApexCharts(el12, {
        chart: { type:'bar', height:360, toolbar:{show:false} },
        series: [
          { name:'Entrées', data: serieEInit || [] },
          { name:'Sorties', data: serieSInit || [] }
        ],
        plotOptions:{ bar:{ horizontal:false, columnWidth:'45%', endingShape:'rounded' } },
        dataLabels:{ enabled:false },
        stroke:{ show:true, width:2, colors:['transparent'] },
        xaxis:{ categories: labelsInit || [] },
        yaxis:{ labels:{ formatter:v=>Number(v).toLocaleString('fr-FR')+' $' } },
        tooltip:{ y:{ formatter:v=>Number(v).toLocaleString('fr-FR',{minimumFractionDigits:2, maximumFractionDigits:2})+' $' } },
        legend:{ position:'top' },
        fill:{ opacity:0.9 }
      });
      chart12.render();
    }
    // Donut
    const elD = document.querySelector('#chartDonut');
    if (elD && !chartDonut) {
      chartDonut = new ApexCharts(elD, {
        chart:{ type:'donut', height:320 },
        labels:['Entrées','Sorties'],
        series:[Number(totEntAnnee||0), Number(totSortAnnee||0)],
        dataLabels:{ enabled:true },
        legend:{ position:'bottom' },
        tooltip:{ y:{ formatter:v=>Number(v).toLocaleString('fr-FR',{minimumFractionDigits:2, maximumFractionDigits:2})+' $' } }
      });
      chartDonut.render();
    }
  }

  // Si tes charts sont déjà construits ailleurs, tu peux commenter l'appel suivant.
  // ensureCharts(<?php echo $JS_LABELS; ?>, <?php echo $JS_SER_ENT; ?>, <?php echo $JS_SER_SOR; ?>, <?php echo json_encode($totEntAnnee); ?>, <?php echo json_encode($totSortAnnee); ?>);

  // Soumission du formulaire (AJAX)
  if (form) {
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      msg.textContent = 'Calcul en cours…';
      const fd = new FormData(form);
      try {
        const resp = await fetch('?range_stats=1', { method:'POST', body: fd });
        const data = await resp.json();
        if (!data.ok) throw new Error(data.msg || 'Erreur inconnue');

        // MAJ KPIs (formatage fr-FR avec 2 décimales)
        const fmt = n => Number(n).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2});
        const kEnt = document.getElementById('kpiEnt');
        const kSor = document.getElementById('kpiSor');
        const kSol = document.getElementById('kpiSolde');
        if (kEnt) kEnt.textContent = fmt(data.kpi.ent);
        if (kSor) kSor.textContent = fmt(data.kpi.sor);
        if (kSol) kSol.textContent = fmt(data.kpi.solde);

        // MAJ graphe colonnes (catégories = labels, séries Entrées/Sorties)
        if (chart12) {
          await chart12.updateOptions({ xaxis: { categories: data.labels || [] } });
          await chart12.updateSeries([
            { name:'Entrées', data: data.serieE || [] },
            { name:'Sorties', data: data.serieS || [] }
          ]);
        }

        // MAJ donut = total période (plus parlant)
        if (chartDonut) {
          await chartDonut.updateSeries([ Number(data.kpi.ent||0), Number(data.kpi.sor||0) ]);
        }

        msg.textContent = 'Période appliquée.';
        setTimeout(()=>{ msg.textContent=''; closePeriode(); }, 600);
      } catch (err) {
        console.error(err);
        msg.textContent = 'Erreur: ' + err.message;
      }
    });
  }
})();
</script>

























 
<!-- ===== JS (garde UN SEUL import ApexCharts) ===== -->
<script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Données depuis PHP
  const labels  = <?php echo $JS_LABELS; ?>;
  const serieE  = <?php echo $JS_SER_ENT; ?>; // Entrées
  const serieS  = <?php echo $JS_SER_SOR; ?>; // Sorties

  // --- Chart 12 mois (colonnes groupées) ---
  (function(){
    const el = document.querySelector('#chart12mois');
    if(!el) return;
    const options = {
      chart: { type: 'bar', height: 360, toolbar: { show: false } },
      series: [
        { name: 'Entrées', data: serieE },
        { name: 'Sorties', data: serieS }
      ],
      plotOptions: {
        bar: { horizontal: false, columnWidth: '45%', endingShape: 'rounded' }
      },
      dataLabels: { enabled: false },
      stroke: { show: true, width: 2, colors: ['transparent'] },
      xaxis: { categories: labels },
      yaxis: {
        labels: { formatter: v => Number(v).toLocaleString('fr-FR') + ' $' }
      },
      tooltip: {
        y: { formatter: v => Number(v).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' $' }
      },
      legend: { position: 'top' },
      fill: { opacity: 0.9 }
    };
    const chart = new ApexCharts(el, options);
    chart.render();
  })();

  // --- Donut Année courante ---
  (function(){
    const el = document.querySelector('#chartDonut');
    if(!el) return;
    const totEntA = <?php echo json_encode($totEntAnnee); ?>;
    const totSortA = <?php echo json_encode($totSortAnnee); ?>;
    const options = {
      chart: { type: 'donut', height: 320 },
      labels: ['Entrées', 'Sorties'],
      series: [Number(totEntA), Number(totSortA)],
      dataLabels: { enabled: true },
      legend: { position: 'bottom' },
      tooltip: {
        y: { formatter: v => Number(v).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' $' }
      }
    };
    const chart = new ApexCharts(el, options);
    chart.render();
  })();
});
</script>
<!-- Tes autres JS de template (vendors, bootstrap, etc.) -->
<script src="html/template/horizontal/src/js/vendors.min.js"></script>
<script src="html/template/horizontal/src/js/template.js"></script>
 