
	
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
 
   
<?php 

 
// ====== STATISTIQUES DEPENSES ======
date_default_timezone_set('Africa/Kinshasa');

// bornes temporelles
$now = new DateTime('now');
$jourStart  = (clone $now)->setTime(0,0,0)->format('Y-m-d H:i:s');
$jourEnd    = (clone $now)->setTime(23,59,59)->format('Y-m-d H:i:s');

$moisStart  = (new DateTime('first day of this month 00:00:00'))->format('Y-m-d H:i:s');
$moisEnd    = (new DateTime('last day of this month 23:59:59'))->format('Y-m-d H:i:s');

$anneeStart = (new DateTime(date('Y').'-01-01 00:00:00'))->format('Y-m-d H:i:s');
$anneeEnd   = (new DateTime(date('Y').'-12-31 23:59:59'))->format('Y-m-d H:i:s');

// On utilise la date d'opération si renseignée, sinon date d'enregistrement
$DATE_COL = "COALESCE(dateop, date_enreg)";

// Total global
$totGlobal = (float)($pdo->query("SELECT IFNULL(SUM(montant),0) FROM sortie_finance")->fetchColumn());

// Par jour
$qJour = $pdo->prepare("SELECT IFNULL(SUM(montant),0) FROM sortie_finance WHERE $DATE_COL BETWEEN :a AND :b");
$qJour->execute([':a'=>$jourStart, ':b'=>$jourEnd]);
$totJour = (float)$qJour->fetchColumn();

// Par mois
$qMois = $pdo->prepare("SELECT IFNULL(SUM(montant),0) FROM sortie_finance WHERE $DATE_COL BETWEEN :a AND :b");
$qMois->execute([':a'=>$moisStart, ':b'=>$moisEnd]);
$totMois = (float)$qMois->fetchColumn();

// Par année
$qAnnee = $pdo->prepare("SELECT IFNULL(SUM(montant),0) FROM sortie_finance WHERE $DATE_COL BETWEEN :a AND :b");
$qAnnee->execute([':a'=>$anneeStart, ':b'=>$anneeEnd]);
$totAnnee = (float)$qAnnee->fetchColumn();

// ====== Série "12 derniers mois" pour le graphique ======
$start12 = (new DateTime('first day of -11 months 00:00:00'))->format('Y-m-d H:i:s');
$end12   = (new DateTime('last day of this month 23:59:59'))->format('Y-m-d H:i:s');

$sql12 = "
  SELECT DATE_FORMAT($DATE_COL, '%Y-%m') ym, SUM(montant) total
  FROM sortie_finance
  WHERE $DATE_COL BETWEEN :s AND :e
  GROUP BY ym
  ORDER BY ym ASC";
$st12 = $pdo->prepare($sql12);
$st12->execute([':s'=>$start12, ':e'=>$end12]);
$rows12 = $st12->fetchAll(PDO::FETCH_KEY_PAIR); // [ '2025-01' => 123.45, ... ]

// Construit des libellés + valeurs, en comblant les mois manquants à 0
$labels = [];
$values = [];
$cursor = new DateTime('first day of -11 months 00:00:00');
for ($i=0; $i<12; $i++) {
  $key = $cursor->format('Y-m');
  $labels[] = $cursor->format('M Y'); // ex: Jan 2025
  $values[] = isset($rows12[$key]) ? (float)$rows12[$key] : 0.0;
  $cursor->modify('+1 month');
}

// Passer au JS
$STAT_LABELS = json_encode($labels, JSON_UNESCAPED_UNICODE);
$STAT_VALUES = json_encode($values, JSON_UNESCAPED_UNICODE);

// Formatage côté PHP pour l’affichage des cartes
function usd($n){ return number_format((float)$n, 2, '.', ' '); }



?>





   
   
   
   
   
   
   
   
   
    
   
<div class="row">
	<div class="col-xxl-12 col-xl-12 col-lg-12">
		<div class="card rounded-4">
			   <div class="box-header d-flex b-0 justify-content-between align-items-center">
				   <h4 class="box-title">Sorties</h4>
				  











<?php  
  
    if (isset($_POST['submitsortie'])) {

        $motif = @$_POST['motif'];
        $sousmotif = @$_POST['sousmotif'];
        $montant = @$_POST['montant'];
        $dateop = @$_POST['dateop'];
        $raison = @$_POST['justification'];
        $agent = $datasession['cod_user'];

        if (!$motif || !$sousmotif || !$montant) {
           
            echo "<div class='error' align=\"left\" style=\"color:red;font-weight:bold;text-align:center;\">Remplissez tous les champs obligatoires</div>";
        
        } else {
            
            // Insertion du nouvel enregistrement
            $sql = 'INSERT INTO sortie_finance (cod_cat, cod_s_cat, montant, description, dateop, date_enreg, agent)
                    VALUES (:cod_cat, :cod_s_cat, :montant, :description, :dateop, NOW(), :agent)';
            
            $q = $pdo->prepare($sql);
            $q->bindValue(':cod_cat', $motif);
            $q->bindValue(':cod_s_cat', $sousmotif);
            $q->bindValue(':montant', $montant);
            $q->bindValue(':description', $raison);
            $q->bindValue(':dateop', $dateop);
            $q->bindValue(':agent', $agent);
            $q->execute();
            $q->closeCursor();

            
            echo '<script>
              Swal.fire({
                  title: "Sortie !",
                  text: "Enregistrement effectué avec succès.",
                  icon: "success",
                  confirmButtonText: "OK"
              }).then((result) => {
                  if (result.isConfirmed) {
                      window.location.href = "index.php?page=sorties"; // Rédirection vers la page de détails
                  }
              });
              </script>';



        }
    }

?>











                        <ul class="m-0" style="list-style: none;">
                            <li class="dropdown">
                              
                            
                                <a href="#" id="btnSortie" onclick="return openModal4();" class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm">
                                    <i class="fa fa-fw fa-arrow-up"></i> Effectuer une sortie
                                </a>

 
                            </li>
                        </ul>
                        
			   </div>
   























			   <div class="card-body pt-0">
















               <div class="row">
  <!-- KPI Jour -->
  <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
    <div class="card rounded-4 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Dépenses journalières</div>
            <div class="h4 my-1"><?php echo usd($totJour); ?> $</div>
            <div class="small text-fade"><?php echo date('d M Y'); ?></div>
          </div>
          <div class="avatar bg-primary-light rounded-3 p-3">
            <i class="fas fa-calendar-day fa-lg text-primary"></i>
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
            <div class="text-muted small">Dépenses du mois</div>
            <div class="h4 my-1"><?php echo usd($totMois); ?> $</div>
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
            <div class="text-muted small">Dépenses de l'année</div>
            <div class="h4 my-1"><?php echo usd($totAnnee); ?> $</div>
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
            <div class="text-muted small">Dépenses (global)</div>
            <div class="h4 my-1"><?php echo usd($totGlobal); ?> $</div>
            <div class="small text-fade">Depuis le début</div>
          </div>
          <div class="avatar bg-danger-light rounded-3 p-3">
            <i class="fas fa-coins fa-lg text-danger"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Graphique 12 derniers mois -->
  <div class="col-12">
    <div class="card rounded-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Évolution des dépenses (12 derniers mois)</h5>
        <div class="text-muted small">
          <?php echo date('d M Y à H:i'); ?>
        </div>
      </div>
      <div class="card-body">
        <div id="depensesChart" style="height: 320px;"></div>
      </div>
    </div>
  </div>
</div>
















				   <div class="table-responsive">
					   <table class="table mb-0">
						   <tbody>


 <?php 


$stmtsorti = $pdo->prepare("SELECT * FROM sortie_finance ORDER BY date_enreg DESC");
$stmtsorti->execute();

if ($stmtsorti->rowCount() > 0) {

    while ($row_sorti = $stmtsorti->fetch(PDO::FETCH_ASSOC)) { 


        $stmtus = $pdo->prepare("SELECT * FROM is_users WHERE cod_user = :cod_user");
        $stmtus->execute(['cod_user' => $row_sorti['agent']]); 
      $datauser = $stmtus->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmtcat = $pdo->prepare("SELECT * FROM categorie_sortie WHERE cod_cat = :cod_cat");
        $stmtcat->execute(['cod_cat' => $row_sorti['cod_cat']]); 
      $datacat = $stmtcat->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmtscat = $pdo->prepare("SELECT * FROM sous_cat_sortie WHERE cod_s_cat = :cod_s_cat");
        $stmtscat->execute(['cod_s_cat' => $row_sorti['cod_s_cat']]); 
      $datascat = $stmtscat->fetch(PDO::FETCH_ASSOC) ?: [];

      $agentName = trim((string) ($datauser['noms'] ?? 'Utilisateur introuvable'));
      $categoryName = trim((string) ($datacat['nom_cat'] ?? 'Categorie inconnue'));
      $subCategoryName = trim((string) ($datascat['nom_s_cat'] ?? 'Sous-categorie inconnue'));

         if (!empty($row_sorti['description'])) {
            $description = '<br><em>' . htmlspecialchars($row_sorti['description']) . '</em><br>';
        } else {
            $description = '<br>';
        }

?>
        <tr>
            <td class="pt-0 px-0 b-0">
        <a class="d-block fw-500 fs-14" href="#"><?php echo htmlspecialchars(ucfirst($categoryName), ENT_QUOTES, 'UTF-8').', <em>'.htmlspecialchars($subCategoryName, ENT_QUOTES, 'UTF-8').'</em> '.number_format((float) ($row_sorti['montant'] ?? 0), 2, '.', ' ').' $'; ?></a>
                <em class="text-fade">Sortie le <?php echo date('d M Y à H:i', strtotime($row_sorti['date_enreg'])); ?></em>
                
                <?php echo $description; ?>
               
        <span class="text-fade"> Par <?php echo htmlspecialchars(ucfirst($agentName), ENT_QUOTES, 'UTF-8'); ?></span>
                
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
   
											   <!-- <a class="dropdown-item" target="_blink" href="pages/pdf/facture_hs.php?cod=<?php echo $row_sorti['cod_sortie']; ?>"><i class="fas fa-print"></i> Imprimer </a>
											     -->
            
											 <a class="dropdown-item" href="#" style="color:red;" title="Suppression" onclick="confirmSuppFact(event, '<?php echo $row_sorti['cod_sortie']; ?>')">
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
                window.location.href = "index.php?page=supsorti&cod=" + encodeURIComponent(cod);
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
    echo '<tr><td colspan="3" class="text-left" style="font-style:italic;">Aucune sortie trouvée</td></tr>';
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
   
   
   
   
   
   
   
   
   
   
   
   
    <!-- MODAL -->
<div id="shareModal4" class="modalinv" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h4 id="modalTitle4" style="margin:0;">Effectuer une sortie</h4>
      <button type="button" class="close" aria-label="Fermer" onclick="closeModal4()">&times;</button>
    </div>

    <div id="status" style="margin:10px 0;"></div>

    <form id="sortieForm" action="" method="post" enctype="multipart/form-data">
      <!-- Motif -->
      <div class="form-group mb-3"> 
            <label style="background:transparent;" for="motif">Motif</label>
        <div class="input-group">
          <span class="input-group-text bg-transparent"><i class="fas fa-list"></i></span>
          <select name="motif" id="motif" class="form-control bg-transparent">
            <option value="">— Sélectionner —</option>
            <?php 
              $reqcs = $pdo->prepare("SELECT cod_cat, nom_cat FROM categorie_sortie ORDER BY cod_cat ASC");
              $reqcs->execute();
              while ($data_cs = $reqcs->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="'.htmlspecialchars($data_cs['cod_cat']).'">'.htmlspecialchars($data_cs['nom_cat']).'</option>';
              }
            ?>
          </select>
        </div>
      </div>

      <!-- Sous motif -->
      <div class="form-group mb-3"> 
            <label style="background:transparent;" for="sousmotif">Sous Motif</label>
        <div class="input-group">
          <span class="input-group-text bg-transparent"><i class="fas fa-list-ul"></i></span>
          <select name="sousmotif" id="sousmotif" class="form-control bg-transparent">
            <option value="">— Sélectionner —</option>
          </select>
        </div>
      </div>




         <!-- beneficiaire -->
      <div class="form-group mb-3"> 
            <label style="background:transparent;" for="benef">Bénéficiaire</label>
        <div class="input-group">
          <span class="input-group-text bg-transparent"><i class="fas fa-list"></i></span>
          <select name="benef" id="benef" class="form-control bg-transparent">
            <option value="">— Sélectionner —</option>
            <?php 
              $reqpers = $pdo->prepare("SELECT * FROM personnel where supprimer is NULL ORDER BY prenom ASC");
              $reqpers->execute();
              while ($data_pers = $reqpers->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="'.htmlspecialchars($data_pers['cod_pers']).'">'.htmlspecialchars($data_pers['prenom'].' '.$data_pers['nom']).'</option>';
              }
            ?>
          </select>
        </div>
      </div>




      <!-- Montant -->
      <div class="form-group mb-3"> 
            <label style="background:transparent;" for="montant">Montant</label>
        <div class="input-group">
          <span class="input-group-text bg-transparent"><i class="fas fa-dollar-sign"></i></span>
          <input type="number" name="montant" id="montant" class="form-control bg-transparent" placeholder="Ex: 150.00" step="0.01" min="0">
        </div>
      </div>


      <!-- Montant -->
        <div class="form-group mb-3"> 
            <label style="background:transparent;" for="dateop">Date et heure</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent"><i class="fas fa-calendar-alt"></i></span>
                <input type="datetime-local" 
                    value="<?php echo date('Y-m-d\TH:i'); ?>" 
                    name="dateop" 
                    id="dateop" 
                    class="form-control bg-transparent" 
                    step="0.01">
            </div>
        </div>


      <!-- Justification -->
      <div class="form-group mb-3"> 
            <label style="background:transparent;" for="justification">Observation</label>
        <div class="input-group">
          <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span>
          <textarea name="justification" id="justification" class="form-control ps-15 bg-transparent" rows="4" placeholder="Détaillez la raison de la sortie..."></textarea>
        </div>
      </div>

      <div class="text-center">
        <button type="submit" name="submitsortie" id="btnSaveSortie" class="btn btn-primary w-p100 mt-10">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<!-- CSS minimal pour garantir l’affichage -->
<style>
.modalinv{
  position:fixed; inset:0; display:none;
  align-items:center; justify-content:center;
  background:rgba(0,0,0,.45); z-index:9999;
}
.modalinv .modal-content{
  width:95%; max-width:680px; background:#fff; border-radius:12px;
  padding:16px; box-shadow:0 10px 40px rgba(0,0,0,.25);
}
.modalinv .modal-header{
  display:flex; align-items:center; justify-content:space-between;
  gap:12px; padding:0 0 8px 0; border-bottom:1px solid #eee;
}
.modalinv .close{
  background:transparent; border:0; font-size:28px; line-height:1; cursor:pointer;
}
</style>

<script>
// ====== Fonctions globales (disponibles pour onclick) ======
function openModal4(title){
  const modal = document.getElementById('shareModal4');
  const h = document.getElementById('modalTitle4');
  h.textContent = title ? ('Evénement N° ' + title) : 'Effectuer une sortie';
  modal.style.display = 'flex';
  // focus piégé pour accessibilité
  setTimeout(()=> document.getElementById('motif')?.focus(), 0);
  return false; // empêche navigation du lien
}
function closeModal4(){
  document.getElementById('shareModal4').style.display = 'none';
}

// ====== Attacher proprement APRÈS chargement du DOM ======
document.addEventListener('DOMContentLoaded', function(){
  const btn = document.getElementById('btnSortie');
  if (btn){
    btn.addEventListener('click', function(e){
      e.preventDefault();
      openModal4();
    });
  }

  // fermer en cliquant en dehors du contenu
  const overlay = document.getElementById('shareModal4');
  overlay.addEventListener('click', function(e){
    if (e.target === overlay) closeModal4();
  });

  // fermer sur Échap
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeModal4();
  });

  // Remplissage dynamique des sous-motifs
  const SUBS_BY_CAT = <?php
    $reqSub = $pdo->query("SELECT cod_s_cat, cod_cat, nom_s_cat FROM sous_cat_sortie ORDER BY nom_s_cat ASC");
    $map = [];
    foreach ($reqSub->fetchAll(PDO::FETCH_ASSOC) as $s) {
      $map[$s['cod_cat']][] = ['cod_s_cat'=>$s['cod_s_cat'], 'nom_s_cat'=>$s['nom_s_cat']];
    }
    echo json_encode($map, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  ?>;

  const motifSel = document.getElementById('motif');
  const sousSel  = document.getElementById('sousmotif');

  function fillSousMotifs(cat){
    sousSel.innerHTML = '<option value="">— Sélectionner le sous motif —</option>';
    (SUBS_BY_CAT[cat] || []).forEach(item=>{
      const o = document.createElement('option');
      o.value = item.cod_s_cat;
      o.textContent = item.nom_s_cat;
      sousSel.appendChild(o);
    });
  }
  motifSel.addEventListener('change', function(){ fillSousMotifs(this.value); });

  // si déjà sélectionné (postback), préremplir
  if (motifSel.value) fillSousMotifs(motifSel.value);
});
</script>



 
<style>

    .modalinv{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.45);z-index:9999;}
    .modalinv .modal-content{max-width:700px;width:95%;background:#fff;border-radius:12px;padding:16px;}

</style>
   
   
   
   
   
   
   
   
   
   
   
   
   
   
    
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
		 
















<script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Données transmises par PHP
  const labels = <?php echo $STAT_LABELS; ?>;   // ["Dec 2024", ..., "Nov 2025"]
  const values = <?php echo $STAT_VALUES; ?>;   // [120, 0, 90, ...]

  var options = {
    chart: {
      type: 'area',
      height: 320,
      toolbar: { show: false }
    },
    series: [{
      name: 'Dépenses',
      data: values
    }],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: labels },
    yaxis: {
      labels: {
        formatter: function (val) { return val.toLocaleString('fr-FR') + ' $'; }
      }
    },
    tooltip: {
      y: {
        formatter: function (val) { return val.toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' $'; }
      }
    },
    fill: { opacity: 0.25 }
  };

  var chart = new ApexCharts(document.querySelector("#depensesChart"), options);
  chart.render();
});
</script>



 