
	
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
  
 
 ?>
 
 <p style="text-align:center;">
   <?php  // echo "La valeur de codevent est : " . $codevent; 
   echo $salut;?> <b>
   <?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, "UTF-8");?> </b>!
 </p>
 
 
 
   
 </div>
 
 
  
 
 


 
 
 <?php
 
     // Total des événements
     $stmtne = $pdo->prepare("
     SELECT COUNT(*) as total_event
     FROM events
     ");
     $stmtne->execute(); 
     $datane = $stmtne->fetch(PDO::FETCH_ASSOC); 
     $datanbevent = $datane ? (int)$datane['total_event'] : 0;
 
     // Réalisée
     $stmtre = $pdo->prepare("
     SELECT COUNT(*) as total_event
     FROM events 
     WHERE fact IS NOT NULL AND crea = ?
     ");
     $stmtre->execute(['2']); 
     $datare = $stmtre->fetch(PDO::FETCH_ASSOC); 
     $datarealise = $datare ? (int)$datare['total_event'] : 0;
 
     // non payé
     $stmtinc = $pdo->prepare("
     SELECT COUNT(*) as total_event
     FROM events 
     WHERE fact IS NULL
     ");
     $stmtinc->execute(); 
     $datainc = $stmtinc->fetch(PDO::FETCH_ASSOC); 
     $dataincomple = $datainc ? (int)$datainc['total_event'] : 0;
 
     // En attente
     $stmtatt = $pdo->prepare("
     SELECT COUNT(*) as total_event
     FROM events 
     WHERE fact IS NOT NULL AND (crea IS NULL OR crea = '1')
     ");
     $stmtatt->execute(); 
     $dataatt = $stmtatt->fetch(PDO::FETCH_ASSOC); 
     $dataattente = $dataatt ? (int)$dataatt['total_event'] : 0;
         


     //-----------------------------------finances annee en cours-------------------------------


    $stmtfinanneemp = $pdo->prepare("
        SELECT SUM(montant_paye) as total_fin
          FROM facture 
          WHERE YEAR(date_enreg) = YEAR(CURRENT_DATE())
      ");
    $stmtfinanneemp->execute(); 
    $finanneemp = $stmtfinanneemp->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $finanneemp = number_format((float)$finanneemp, 2, '.', '');


      // finance
      $stmtfinannee = $pdo->prepare("
          SELECT SUM(montant_total) as total_fin
          FROM facture 
          WHERE YEAR(date_enreg) = YEAR(CURRENT_DATE())
      ");
      $stmtfinannee->execute(); 
      $datafinannee = $stmtfinannee->fetch(PDO::FETCH_ASSOC); 

      $finannee = isset($datafinannee['total_fin']) ? (float)$datafinannee['total_fin'] : 0;

      // Formater pour afficher deux décimales
      $finannee = number_format($finannee, 2, '.', '');


      $restannee= $finannee - $finanneemp; 
      $restannee = number_format($restannee, 2, '.', '');

     //-----------------------------------commende annee en cours-------------------------------

     $stmtcomannee = $pdo->prepare("
     SELECT COUNT(*) as total_com
     FROM events 
     WHERE fact IS NOT NULL AND YEAR(date_enreg) = YEAR(CURRENT_DATE())
     ");
     $stmtcomannee->execute(); 
     $datacomannee = $stmtcomannee->fetch(PDO::FETCH_ASSOC); 

     $comannee = $datacomannee ? (int)$datacomannee['total_com'] : 0;

     if ($comannee > 1) {
        $comannee = $comannee.' evenements';
     }else{
        $comannee = $comannee.' evenement';
     }


     //-----------------------------------commende terminé annee en cours-------------------------------

     $stmtcomtermannee = $pdo->prepare("
     SELECT COUNT(*) as total_com
     FROM events 
     WHERE crea = '2' AND YEAR(date_enreg) = YEAR(CURRENT_DATE())
     ");
     $stmtcomtermannee->execute(); 
     $datacomtermannee = $stmtcomtermannee->fetch(PDO::FETCH_ASSOC); 

     $comtermannee = $datacomtermannee ? (int)$datacomtermannee['total_com'] : 0;


     if ($comtermannee > 1) {
        $comtermannee = $comtermannee.' terminés';
     }else{
        $comtermannee = $comtermannee.' terminé';
     }



 
     //-----------------------------------commende mois en cours-------------------------------


    $stmtfinanneedigital = $pdo->prepare("
        SELECT SUM(pt) AS total_fin
        FROM details_fact 
        WHERE YEAR(date_enreg) = YEAR(CURRENT_DATE()) AND libelle = 'Invitation électronique'
    ");
    $stmtfinanneedigital->execute(); 
    $finanneedigital = $stmtfinanneedigital->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $finanneedigital = number_format((float)$finanneedigital, 2, '.', '');



















     //-----------------------------------finances mois en cours-------------------------------

    
    $stmtfinmoismp = $pdo->prepare("
        SELECT SUM(montant_paye) as total_fin
          FROM facture 
          WHERE MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())
      ");
    $stmtfinmoismp->execute(); 
    $finmoismp = $stmtfinmoismp->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $finmoismp = number_format((float)$finmoismp, 2, '.', '');



      // finance
      $stmtfinmois = $pdo->prepare("
          SELECT SUM(montant_total) as total_fin
          FROM facture 
          WHERE MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())
      ");
      $stmtfinmois->execute(); 
      $datafinmois = $stmtfinmois->fetch(PDO::FETCH_ASSOC); 

      $finmois = isset($datafinmois['total_fin']) ? (float)$datafinmois['total_fin'] : 0;

      // Formater pour afficher deux décimales
      $finmois = number_format($finmois, 2, '.', '');


      $restmois= $finmois - $finmoismp;
      $restmois = number_format($restmois, 2, '.', '');

     //-----------------------------------commende mois en cours-------------------------------

     $stmtcommois = $pdo->prepare("
     SELECT COUNT(*) as total_com
     FROM events 
     WHERE fact IS NOT NULL AND MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())
     ");
     $stmtcommois->execute(); 
     $datacommois = $stmtcommois->fetch(PDO::FETCH_ASSOC); 

     $commois = $datacommois ? (int)$datacommois['total_com'] : 0;


     if ($commois > 1) {
        $commois = $commois.' evenements';
     }else{
        $commois = $commois.' evenement';
     }



     //-----------------------------------commende terminé mois en cours-------------------------------

     $stmtcomtermmois = $pdo->prepare("
     SELECT COUNT(*) as total_com
     FROM events 
     WHERE crea = '2' AND MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())
     ");
     $stmtcomtermmois->execute(); 
     $datacomtermmois = $stmtcomtermmois->fetch(PDO::FETCH_ASSOC); 

     $comtermmois = $datacomtermmois ? (int)$datacomtermmois['total_com'] : 0;


     if ($comtermmois > 1) {
        $comtermmois = $comtermmois.' terminés';
     }else{
        $comtermmois = $comtermmois.' terminé';
     }

 
     //-----------------------------------commende mois en cours-------------------------------


    $stmtfinmoisdigital = $pdo->prepare("
        SELECT SUM(pt) AS total_fin
        FROM details_fact 
        WHERE MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE()) AND libelle = 'Invitation électronique'
    ");
    $stmtfinmoisdigital->execute(); 
    $finmoisdigital = $stmtfinmoisdigital->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $finmoisdigital = number_format((float)$finmoisdigital, 2, '.', '');



















     //---------------------------------------finances jours-----------------------------------

    
    $stmtfinjourmp = $pdo->prepare("
        SELECT SUM(montant_paye) AS total_fin
        FROM facture 
        WHERE DATE(date_enreg) = CURRENT_DATE()
    ");
    $stmtfinjourmp->execute(); 
    $finjourmp = $stmtfinjourmp->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $finjourmp = number_format((float)$finjourmp, 2, '.', '');



    $stmtfinjour = $pdo->prepare("
        SELECT SUM(montant_total) AS total_fin
        FROM facture 
        WHERE DATE(date_enreg) = CURRENT_DATE()
    ");
    $stmtfinjour->execute(); 
    $finjour = $stmtfinjour->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $finjour = number_format((float)$finjour, 2, '.', '');

    $restjour = $finjour - $finjourmp;
    $restjour = number_format($restjour, 2, '.', '');

     //-----------------------------------commende mois en cours-------------------------------

     $stmtcomjour = $pdo->prepare("
     SELECT COUNT(*) as total_com
     FROM events 
     WHERE fact IS NOT NULL AND DAY(date_enreg) = DAY(CURRENT_DATE()) AND MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())
     ");
     $stmtcomjour->execute(); 
     $datacomjour = $stmtcomjour->fetch(PDO::FETCH_ASSOC); 

     $comjour = $datacomjour ? (int)$datacomjour['total_com'] : 0;


     if ($comjour > 1) {
        $comjour = $comjour.' evenements';
     }else{
        $comjour = $comjour.' evenement';
     }



     //-----------------------------------commende terminé mois en cours-------------------------------

     $stmtcomtermjour = $pdo->prepare("
     SELECT COUNT(*) as total_com
     FROM events 
     WHERE crea = '2' AND DAY(date_enreg) = DAY(CURRENT_DATE()) AND MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())
     ");
     $stmtcomtermjour->execute(); 
     $datacomtermjour = $stmtcomtermjour->fetch(PDO::FETCH_ASSOC); 

     $comtermjour = $datacomtermjour ? (int)$datacomtermjour['total_com'] : 0;


     if ($comtermjour > 1) {
        $comtermjour = $comtermjour.' terminés';
     }else{
        $comtermjour = $comtermjour.' terminé';
     }


 

     //-----------------------------------commende mois en cours-------------------------------


    $stmtfinjourdigital = $pdo->prepare("
        SELECT SUM(pt) AS total_fin
        FROM details_fact 
        WHERE DATE(date_enreg) = CURRENT_DATE() AND libelle = 'Invitation électronique'
    ");
    $stmtfinjourdigital->execute(); 
    $finjourdigital = $stmtfinjourdigital->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $finjourdigital = number_format((float)$finjourdigital, 2, '.', '');
 



























     //---------------------------------------finances jours-----------------------------------

    
    $stmtfintotalmp = $pdo->prepare("
        SELECT SUM(montant_paye) AS total_fin
        FROM facture
    ");
    $stmtfintotalmp->execute(); 
    $fintotalmp = $stmtfintotalmp->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $fintotalmp = number_format((float)$fintotalmp, 2, '.', '');



    $stmtfintotal = $pdo->prepare("
        SELECT SUM(montant_total) AS total_fin
        FROM facture
    ");
    $stmtfintotal->execute(); 
    $fintotal = $stmtfintotal->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $fintotal = number_format((float)$fintotal, 2, '.', '');

    $resttotal = $fintotal - $fintotalmp;
    $resttotal = number_format($resttotal, 2, '.', '');

     //-----------------------------------commende mois en cours-------------------------------

     $stmtcomtotal = $pdo->prepare("
     SELECT COUNT(*) as total_com
     FROM events 
     ");
     $stmtcomtotal->execute(); 
     $datacomtotal = $stmtcomtotal->fetch(PDO::FETCH_ASSOC); 

     $comtotal = $datacomtotal ? (int)$datacomtotal['total_com'] : 0;


     if ($comtotal > 1) {
        $comtotal = $comtotal.' evenements';
     }else{
        $comtotal = $comtotal.' evenement';
     }



     //-----------------------------------commende terminé mois en cours-------------------------------

     $stmtcomtermtotal = $pdo->prepare("
     SELECT COUNT(*) as total_com
     FROM events WHERE crea = '2'
     ");
     $stmtcomtermtotal->execute(); 
     $datacomtermtotal = $stmtcomtermtotal->fetch(PDO::FETCH_ASSOC); 

     $comtermtotal = $datacomtermtotal ? (int)$datacomtermtotal['total_com'] : 0;


     if ($comtermtotal > 1) {
        $comtermtotal = $comtermtotal.' terminés';
     }else{
        $comtermtotal = $comtermtotal.' terminé';
     }


 

     //-----------------------------------commende mois en cours-------------------------------


    $stmtfintotaldigital = $pdo->prepare("
        SELECT SUM(pt) AS total_fin
        FROM details_fact 
        WHERE libelle = 'Invitation électronique'
    ");
    $stmtfintotaldigital->execute(); 
    $fintotaldigital = $stmtfintotaldigital->fetchColumn() ?: 0;

    // Formater pour afficher deux décimales
    $fintotaldigital = number_format((float)$fintotaldigital, 2, '.', '');

 ?>
       
       <section class="content">
        
    <div class="row">
        <div class="col-12">
      <div class="box rounded-4 finance-panel">
                <div class="box-header d-flex b-0 justify-content-between align-items-center pb-0">
                    <h4 class="box-title">Finances</h4>
          <button type="button" id="financeToggle" class="finance-toggle-btn" aria-expanded="false">
            <span class="finance-toggle-icon"><i class="fa fa-eye"></i></span>
            <span class="finance-toggle-text">Afficher</span>
          </button>
                </div>
                <div class="box-body pt-0 summery-box">
          <p class="mb-20 finance-subtitle">Vue synthétique des encaissements et commandes</p>
          <div id="vueFinance" style="display: none;">
            <div class="row g-3 finance-grid">
              <div class="col-lg-3 col-md-6">
                <div class="box pull-up mb-0 finance-card finance-card-info">
                  <div class="box-body finance-card-body">
                    <div class="finance-card-top">
                      <div class="finance-icon-wrap finance-icon-info">
                        <i class="fa fa-area-chart fs-18"></i>
                      </div>
                      <span class="finance-period"><?php echo "Aujourd'hui"; ?></span>
                    </div>
                    <h2 class="fw-600 mt-3 finance-amount"><?php echo $finjourmp . ' $'; ?></h2>
                    <p class="finance-detail"><?php echo 'Digital - ' . $finjourdigital . ' $'; ?></p>
                    <p class="finance-detail"><?php echo 'Reste ' . $restjour . ' $'; ?></p>
                    <p class="mb-0 finance-counter"><?php echo $comjour . ' / ' . $comtermjour; ?></p>
                  </div>
                </div>
              </div>

              <div class="col-lg-3 col-md-6">
                <div class="box pull-up mb-sm-0 finance-card finance-card-warning">
                  <div class="box-body finance-card-body">
                    <div class="finance-card-top">
                      <div class="finance-icon-wrap finance-icon-warning">
                        <i class="fa fa-area-chart fs-18"></i>
                      </div>
                      <span class="finance-period"><?php echo "Ce mois"; ?></span>
                    </div>
                    <h2 class="fw-600 mt-3 finance-amount"><?php echo $finmoismp . ' $'; ?></h2>
                    <p class="finance-detail"><?php echo 'Digital - ' . $finmoisdigital . ' $'; ?></p>
                    <p class="finance-detail"><?php echo 'Reste ' . $restmois . ' $'; ?></p>
                    <p class="mb-0 finance-counter"><?php echo $commois . ' / ' . $comtermmois; ?></p>
                  </div>
                </div>
              </div>

              <div class="col-lg-3 col-md-6">
                <div class="box pull-up mb-sm-0 finance-card finance-card-danger">
                  <div class="box-body finance-card-body">
                    <div class="finance-card-top">
                      <div class="finance-icon-wrap finance-icon-danger">
                        <i class="fa fa-area-chart fs-18"></i>
                      </div>
                      <span class="finance-period"><?php echo date('Y'); ?></span>
                    </div>
                    <h2 class="fw-600 mt-3 finance-amount"><?php echo $finanneemp . ' $'; ?></h2>
                    <p class="finance-detail"><?php echo 'Digital - ' . $finanneedigital . ' $'; ?></p>
                    <p class="finance-detail"><?php echo 'Reste ' . $restannee . ' $'; ?></p>
                    <p class="mb-0 finance-counter"><?php echo $comannee . ' / ' . $comtermannee; ?></p>
                  </div>
                </div>
              </div>

              <div class="col-lg-3 col-md-6">
                <div class="box pull-up mb-0 finance-card finance-card-dark">
                  <div class="box-body finance-card-body">
                    <div class="finance-card-top">
                      <div class="finance-icon-wrap finance-icon-dark">
                        <i class="fa fa-area-chart fs-18"></i>
                      </div>
                      <span class="finance-period"><?php echo "Total"; ?></span>
                    </div>
                    <h2 class="fw-600 mt-3 finance-amount"><?php echo $fintotalmp . ' $'; ?></h2>
                    <p class="finance-detail"><?php echo 'Digital - ' . $fintotaldigital . ' $'; ?></p>
                    <p class="finance-detail"><?php echo 'Reste ' . $resttotal . ' $'; ?></p>
                    <p class="mb-0 finance-counter"><?php echo $comtotal . ' / ' . $comtermtotal; ?></p>
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
  document.getElementById('financeToggle').addEventListener('click', function () {
        const vueFinance = document.getElementById('vueFinance');
    const toggleText = this.querySelector('.finance-toggle-text');
    const toggleIcon = this.querySelector('.finance-toggle-icon i');
    const isVisible = vueFinance.style.display === 'block';

    vueFinance.style.display = isVisible ? 'none' : 'block';
    toggleText.textContent = isVisible ? 'Afficher' : 'Masquer';
    toggleIcon.className = isVisible ? 'fa fa-eye' : 'fa fa-eye-slash';
    this.setAttribute('aria-expanded', isVisible ? 'false' : 'true');
    this.classList.toggle('is-active', !isVisible);
    });
</script>

<style>
  .finance-panel {
    position: relative;
    overflow: hidden;
    border: 0;
    border-radius: 28px;
    background:
      radial-gradient(circle at top left, rgba(34, 197, 94, 0.14), transparent 28%),
      radial-gradient(circle at top right, rgba(59, 130, 246, 0.20), transparent 32%),
      linear-gradient(135deg, #081120 0%, #101a2e 52%, #0f2242 100%);
    box-shadow: 0 26px 60px rgba(2, 6, 23, 0.28);
  }

  .finance-panel::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), transparent 42%, rgba(255, 255, 255, 0.03));
    pointer-events: none;
  }

  .finance-panel .box-header {
    position: relative;
    z-index: 1;
    padding: 26px 28px 6px;
  }

  .finance-panel .box-title {
    margin: 0;
    color: #f8fafc;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 0.01em;
  }

  .finance-panel .summery-box {
    position: relative;
    z-index: 1;
    padding: 0 28px 28px;
  }

  .finance-subtitle {
    color: rgba(226, 232, 240, 0.72);
    font-size: 14px;
    margin-bottom: 20px;
  }

  .finance-counter {
    font-weight: 400;
  }

  .finance-grid {
    row-gap: 18px;
  }

  .finance-card {
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    overflow: hidden;
    min-height: 100%;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.10), 0 16px 36px rgba(15, 23, 42, 0.25);
  }

  .finance-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.10), transparent 45%);
    pointer-events: none;
  }

  .finance-card-info {
    background: linear-gradient(160deg, #ecfeff 0%, #dbeafe 42%, #eff6ff 100%);
  }

  .finance-card-warning {
    background: linear-gradient(160deg, #fffbeb 0%, #ffedd5 45%, #fff7ed 100%);
  }

  .finance-card-danger {
    background: linear-gradient(160deg, #fff1f2 0%, #ffe4e6 44%, #fff7f7 100%);
  }

  .finance-card-dark {
    background: linear-gradient(160deg, #e2e8f0 0%, #dbeafe 35%, #f8fafc 100%);
  }

  .finance-card-body {
    position: relative;
    z-index: 1;
    padding: 22px;
  }

  .finance-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
  }

  .finance-icon-wrap {
    width: 58px;
    height: 58px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    color: #fff;
    box-shadow: 0 12px 22px rgba(15, 23, 42, 0.16);
  }

  .finance-icon-info { background: linear-gradient(135deg, #06b6d4 0%, #2563eb 100%); }
  .finance-icon-warning { background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); }
  .finance-icon-danger { background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%); }
  .finance-icon-dark { background: linear-gradient(135deg, #334155 0%, #0f172a 100%); }

  .finance-period {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.72);
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    backdrop-filter: blur(6px);
  }

  .finance-amount {
    color: #0f172a;
    font-size: 32px;
    line-height: 1;
    margin-bottom: 12px;
  }

  .finance-detail {
    margin: 0 0 6px;
    color: #475569;
    font-size: 14px;
  }

  .finance-counter {
    display: inline-flex;
    align-items: center;
    margin-top: 14px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.06);
    color: #0f172a;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .finance-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 16px;
    background: linear-gradient(180deg, rgba(255,255,255,0.16) 0%, rgba(255,255,255,0.08) 100%);
    color: #f8fafc;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.10), 0 12px 28px rgba(2, 6, 23, 0.20);
    cursor: pointer;
    user-select: none;
    backdrop-filter: blur(14px);
    transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .finance-toggle-btn:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, 0.14);
    border-color: rgba(255, 255, 255, 0.28);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12), 0 14px 30px rgba(2, 6, 23, 0.24);
  }

  .finance-toggle-btn.is-active {
    background: linear-gradient(180deg, rgba(52, 211, 153, 0.24) 0%, rgba(52, 211, 153, 0.12) 100%);
    border-color: rgba(52, 211, 153, 0.34);
  }

  .finance-toggle-icon {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    color: #f8fafc;
    flex-shrink: 0;
  }

  .finance-toggle-text {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #f8fafc;
  }

 
</style>






   
 
 
  
         
 
 
 
 
 
 
 
  
  
 
 
 <?php include('menustat.php')?>


 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
  
 
 <div class="row" id='mesinv'>
     <div class="col-xxl-12 col-xl-12 col-lg-12">
         <div class="card rounded-4">
 
             <div class="box-header d-flex b-0 justify-content-between align-items-center">
                 <h4 class="box-title">Les Commandes</h4>
                 <ul class="m-0" style="list-style: none;">
                     <li class="dropdown">
                         <a href="javascript:void(0)" onclick="openSearchModal()" 
                            class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm">
                              <i class="fa fa-search"></i> Rechercher
                          </a>
                    </li>
                 </ul>
             </div>
 


<script>
function openSearchModal() {
    Swal.fire({
        title: 'Rechercher un événement',
        html: `
        <form method="get" action="index.php" id="searchForm">
          <input type="hidden" name="page" value="searchevent">

          <input type="text" name="search" class="swal2-input"
                placeholder="Prénom des mariés" required style="width:80%"
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

          <button type="submit" class="swal2-confirm swal2-styled"
                  style="display:inline-block;margin-top:10px;width:80%;">
            Rechercher
          </button>
        </form>
        `,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Annuler'
    });
}
</script>


 
             <div class="card-body pt-0">
                 <div class="table-responsive">
                     <table class="table mb-0">
                         <tbody>
                        
                                    <img id="loader" src="../images/Loading_icon.gif" width="10%" alt="Loading..." style="display: none;">
                           
                         
                                    <div id="content" style="display: none;">
                                        <?php include('blocsearchevent.php'); ?>
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
     