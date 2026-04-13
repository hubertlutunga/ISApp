<?php
// ====== ENTÊTE PAGE ======
?>
<div class="wrapper">
  <?php include('header.php');?>

  <div class="content-wrapper">
    <div class="container-full">
      <div class="container h-p100">
        <div class="row align-items-center justify-content-md-center h-p100">
          <div class="col-12">
            <div class="row justify-content-center g-0">
              <div class="col-12 boxcontent event-builder-shell">
                <div class="bg-white rounded10 shadow-lg event-builder-card">
                  <div class="content-top-agile p-20 pb-0 event-builder-header">
                    <span class="event-builder-kicker">Nouvelle commande</span>
                    <h2 class="event-builder-title">Créez votre événement</h2>
                    <p class="mb-0 event-builder-subtitle">Choisissez votre type d'événement, ajoutez vos détails et finalisez votre commande en quelques étapes.</p>
                  </div>
                  <div class="p-40 event-builder-form">

<?php
// ================== TRAITEMENT PHP (inchangé) ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type_event = $_POST['event'] ?? null;
  $nomsAnniv = $_POST['nomsfetard'] ?? null;
  $prenomEpoux = $_POST['prenomEpoux'] ?? null;
  $prenomEpouse = $_POST['prenomEpouse'] ?? null;

  $initialemar = substr((string) $prenomEpouse, 0, 1) . '&' . substr((string) $prenomEpoux, 0, 1);
  if ($type_event === '2' || $type_event === '3') {
    $initialemar = (string) $nomsAnniv;
  }

  try {
    $cod_event = EventCreationService::createManagedEvent(
      $pdo,
      [
        'cod_user' => $datasession['cod_user'] ?? null,
        'type_event' => $type_event,
        'type_mar' => $_POST['weddingType'] ?? null,
        'modele_inv' => $_POST['modele_inv'] ?? null,
        'modele_chev' => $_POST['chevaletModel'] ?? null,
        'date_event' => $_POST['dateHeure'] ?? null,
        'lieu' => $_POST['lieu'] ?? null,
        'adresse' => $_POST['adresse'] ?? null,
        'prenom_epoux' => $prenomEpoux,
        'nom_epoux' => $_POST['nomEpoux'] ?? null,
        'prenom_epouse' => $prenomEpouse,
        'nom_epouse' => $_POST['nomEpouse'] ?? null,
        'nomfetard' => $nomsAnniv,
        'themeconf' => $_POST['themeConf'] ?? null,
        'autres_precisions' => $_POST['details'] ?? null,
        'initiale_mar' => $initialemar,
        'accessoire_quantities' => $_POST['accessoire_quantities'] ?? [],
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






 
 

<!-- ================== CSS MINIMAL (wizard + modal + preview) ================== -->
<style>
  .event-builder-card{
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 28px 70px rgba(15, 23, 42, 0.12);
  }
  @media (min-width: 992px){
    .event-builder-shell{
      display:flex;
      justify-content:center;
    }
    .event-builder-card{
      width: 800px;
      max-width: 100%;
      margin-left: 0;
      margin-right: 0;
    }
  }
  .event-builder-header{
    padding: 28px 32px 8px !important;
    background:
      radial-gradient(circle at top right, rgba(59, 130, 246, 0.18), transparent 34%),
      linear-gradient(135deg, #f8fbff 0%, #f4f9f7 100%);
    border-bottom: 1px solid rgba(148, 163, 184, 0.16);
  }
  .event-builder-kicker{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.10);
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
  .event-builder-title{
    margin: 14px 0 8px;
    color: #0f172a;
    font-size: 30px;
    font-weight: 800;
    line-height: 1.1;
  }
  .event-builder-subtitle{
    color: #475569;
    font-size: 14px;
    line-height: 1.6;
  }
  .event-builder-form{
    padding: 28px 32px 34px !important;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  }
  .section-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:16px;
    margin-bottom:18px;
    padding-bottom:14px;
    border-bottom:1px solid rgba(148,163,184,.16);
  }
  .section-head h3{
    margin:0;
    color:#0f172a;
    font-size:20px;
    font-weight:800;
  }
  .section-head p{
    margin:6px 0 0;
    color:#64748b;
    font-size:14px;
    line-height:1.5;
  }
  .section-step-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:38px;
    height:38px;
    border-radius:999px;
    background:rgba(15,118,110,.10);
    color:#0f766e;
    font-weight:800;
    flex-shrink:0;
  }
  .hidden{display:none!important}
  .step{display:none}
  .step.active{display:block}
  .wizard-nav{display:flex;gap:10px;justify-content:center;margin:12px 0 24px}
  .wizard-dot{width:12px;height:12px;border-radius:50%;background:#d8e1ef;display:inline-block;box-shadow: inset 0 0 0 1px rgba(148,163,184,.25)}
  .wizard-dot.active{background:#0f766e;box-shadow:0 0 0 6px rgba(15,118,110,.12)}

  /* Actions: Retour en haut, Suivant/Commander en bas, full width */
  .wizard-actions{
    display:flex;
    flex-direction:column;
    gap:14px;
    margin-top:12px;
  }
  .wizard-actions .btn{ width:100% !important; min-height: 52px; border-radius: 16px; font-weight: 700; }

  .form-group{margin-bottom:18px}
  .form-group .input-group{position:relative}
  .form-grid{display:grid;gap:16px}
  .form-grid.two-col{grid-template-columns:repeat(2, minmax(0, 1fr))}
  .form-span-full{grid-column:1 / -1}
  .placeholder-label{position:absolute;left:48px;top:-8px;background:#fff;padding:0 6px;font-size:12px;color:#888}
  .form-label{margin-bottom:8px;color:#0f172a;font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
  input,textarea,select{font-size:16px!important;border:1px solid #d7deea !important;border-radius: 0 14px 14px 0 !important;background:#fff !important;min-height:52px;color:#0f172a;}
  textarea.form-control{min-height:140px;padding-top:14px}
  input:focus,textarea:focus,select:focus{border-color:#0f766e !important;box-shadow:0 0 0 4px rgba(15,118,110,.10) !important}
  .input-group-text{border-top:1px solid #d7deea !important;border-bottom:1px solid #d7deea !important;border-left:1px solid #d7deea !important;border-radius:14px 0 0 14px !important;background:#f8fafc !important;color:#0f766e;min-width:52px;justify-content:center;}
  .sallexx:focus{outline:none;box-shadow:none;border:none;resize:none!important}
  .btn-primary{background:linear-gradient(135deg,#0f766e 0%,#0f9f85 100%) !important;border:none !important;box-shadow:0 16px 30px rgba(15,118,110,.22)}
  .btn-outline-secondary{border:1px solid #cbd5e1 !important;color:#334155 !important;background:#fff !important}
  .btn-outline-secondary:hover{background:#f8fafc !important;color:#0f172a !important}
  #eventForm > .form-group,
  #eventForm > fieldset,
  #eventForm > .step,
  #eventForm > #singleStepOthers{
    background:#fff;
    border:1px solid rgba(148,163,184,.18);
    border-radius:22px;
    padding:20px;
    margin-bottom:18px;
    box-shadow:0 12px 26px rgba(15,23,42,.05);
  }
  #eventForm > .step,
  #eventForm > #singleStepOthers{
    background:linear-gradient(180deg,#ffffff 0%,#fbfdff 100%);
  }
  .labaccessoire{font-size:14px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:#0f172a}
  .checkbox{padding:16px 18px;border:1px solid rgba(148,163,184,.2);border-radius:16px;background:#f8fafc}
  #AccessoireGroup .checkbox{
    transition:border-color .2s ease, transform .2s ease, box-shadow .2s ease, background .2s ease;
  }
  #AccessoireGroup .checkbox:hover{
    border-color:rgba(15,118,110,.32);
    transform:translateY(-1px);
    box-shadow:0 14px 24px rgba(15,118,110,.08);
  }
  .accessory-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:14px;
    margin-top:14px;
  }
  .accessory-option{
    position:relative;
    margin:0;
  }
  .accessory-option input[type="checkbox"]{
    position:absolute;
    opacity:0;
    pointer-events:none;
  }
  .accessory-card{
    display:flex;
    align-items:flex-start;
    gap:14px;
    min-height:100%;
    padding:16px;
    border:1px solid rgba(148,163,184,.2);
    border-radius:20px;
    background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
    box-shadow:0 14px 30px rgba(15,23,42,.06);
    cursor:pointer;
    transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease,background .2s ease;
  }
  .accessory-option:hover .accessory-card{
    transform:translateY(-2px);
    border-color:rgba(15,118,110,.28);
    box-shadow:0 18px 34px rgba(15,118,110,.10);
  }
  .accessory-option input[type="checkbox"]:checked + .accessory-card{
    border-color:#0f766e;
    background:linear-gradient(180deg,#ecfeff 0%,#f7fffd 100%);
    box-shadow:0 22px 38px rgba(15,118,110,.16);
  }
  .accessory-icon{
    width:48px;
    height:48px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:16px;
    background:rgba(15,118,110,.10);
    color:#0f766e;
    flex-shrink:0;
    font-size:18px;
  }
  .accessory-content{
    display:flex;
    flex-direction:column;
    gap:8px;
    min-width:0;
  }
  .accessory-title{
    color:#0f172a;
    font-size:15px;
    font-weight:800;
    line-height:1.35;
  }
  .accessory-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    align-self:flex-start;
    margin-top:2px;
    padding:6px 10px;
    border-radius:999px;
    background:rgba(15,23,42,.06);
    color:#334155;
    font-size:11px;
    font-weight:800;
    letter-spacing:.06em;
    text-transform:uppercase;
  }
  .accessory-option input[type="checkbox"]:checked + .accessory-card .accessory-badge{
    background:rgba(15,118,110,.14);
    color:#0f766e;
  }
  .accessory-quantity{
    display:none;
    align-items:center;
    gap:10px;
    margin-top:4px;
  }
  .accessory-quantity span{
    color:#334155;
    font-size:12px;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
    white-space:nowrap;
  }
  .accessory-quantity input{
    min-height:42px;
    max-width:110px;
    border-radius:12px !important;
    border:1px solid #d7deea !important;
    text-align:center;
    font-weight:700;
  }
  .btnpic{display:inline-flex;align-items:center;gap:8px;padding:13px 18px;border-radius:14px;background:#ecfeff;color:#0f766e;font-weight:700;border:1px dashed rgba(15,118,110,.35);cursor:pointer}
  .selected-option{padding:13px 0;color:#334155;font-weight:600}
  .champmod{border:1px solid #d7deea !important;border-radius:14px;overflow:hidden;background:#fff}
  .field-help{margin-top:-6px;color:#64748b;font-size:13px;line-height:1.5}
  .model-picker-summary{display:flex;flex-direction:column;gap:4px;padding:11px 0}
  .model-picker-label{color:#0f172a;font-weight:700}
  .model-picker-meta{color:#64748b;font-size:13px}
  .selected-model-preview{
    display:none;
    grid-template-columns:150px minmax(0, 1fr);
    gap:16px;
    align-items:center;
    margin-top:14px;
    padding:14px;
    border:1px solid rgba(15,118,110,.18);
    border-radius:20px;
    background:linear-gradient(180deg,#f8fffe 0%,#ffffff 100%);
    box-shadow:0 16px 30px rgba(15,118,110,.08);
  }
  .selected-model-thumb{
    width:100%;
    height:140px;
    object-fit:cover;
    border-radius:16px;
    border:1px solid rgba(148,163,184,.18);
    background:#fff;
  }
  .selected-model-copy{
    display:flex;
    flex-direction:column;
    gap:8px;
    min-width:0;
  }
  .selected-model-kicker{
    display:inline-flex;
    align-self:flex-start;
    padding:6px 10px;
    border-radius:999px;
    background:rgba(15,118,110,.12);
    color:#0f766e;
    font-size:11px;
    font-weight:800;
    letter-spacing:.06em;
    text-transform:uppercase;
  }
  .selected-model-title{
    color:#0f172a;
    font-size:18px;
    font-weight:800;
    line-height:1.3;
  }
  .selected-model-meta{
    color:#64748b;
    font-size:14px;
    line-height:1.6;
  }
  .selected-model-actions{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    margin-top:4px;
  }
  .selected-model-chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:7px 11px;
    border-radius:999px;
    background:rgba(15,23,42,.06);
    color:#334155;
    font-size:12px;
    font-weight:700;
  }
  .modal-content{background:#fff;margin:60px auto;padding:24px;border:1px solid #dbe3ef;width:95%;max-width:760px;border-radius:24px;box-shadow:0 28px 80px rgba(15,23,42,.18)}
  .model-modal-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:16px;
    margin-bottom:18px;
  }
  .model-modal-head h2{
    margin:0;
    color:#0f172a;
    font-size:24px;
    font-weight:800;
  }
  .model-modal-head p{
    margin:8px 0 0;
    color:#64748b;
    font-size:14px;
    line-height:1.6;
  }
  .dropdown-content div{cursor:pointer;border:1px solid #e2e8f0;border-radius:16px;padding:10px;text-align:center;transition:transform .2s ease, box-shadow .2s ease,border-color .2s ease}
  .dropdown-content div:hover{transform:translateY(-2px);border-color:#0f766e;box-shadow:0 16px 28px rgba(15,118,110,.12)}
  .dropdown-content div.is-selected{border-color:#0f766e;background:linear-gradient(180deg,#ecfeff 0%,#f8fffe 100%);box-shadow:0 16px 28px rgba(15,118,110,.12)}
  .dropdown-content label{display:block;color:#0f172a;font-weight:700;font-size:14px}
  .model-option-card{
    display:flex;
    flex-direction:column;
    gap:10px;
    text-align:left;
  }
  .model-option-copy{
    display:flex;
    flex-direction:column;
    gap:5px;
  }
  .model-option-title{
    color:#0f172a;
    font-size:15px;
    font-weight:800;
    line-height:1.35;
  }
  .model-option-meta{
    color:#64748b;
    font-size:13px;
    line-height:1.5;
  }
  .option-image{width:100%;height:190px;object-fit:cover;border-radius:14px;border:1px solid rgba(148,163,184,.16);margin-top:0}
  @media (max-width: 767px){
    .event-builder-header{padding:24px 22px 6px !important}
    .event-builder-form{padding:22px 20px 28px !important}
    .event-builder-title{font-size:24px}
    .section-head{flex-direction:column}
    .form-grid.two-col{grid-template-columns:minmax(0, 1fr)}
    .accessory-grid{grid-template-columns:minmax(0, 1fr)}
    .selected-model-preview{grid-template-columns:minmax(0, 1fr)}
    #eventForm > .form-group,
    #eventForm > fieldset,
    #eventForm > .step,
    #eventForm > #singleStepOthers{padding:16px}
  }

  /* Modal modèles */
  .modal{display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;overflow:auto;background:rgba(0,0,0,.4)}
  .dropdown-content{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-top:10px}
  .option-image{width:100%;height:110px;object-fit:cover;border-radius:6px;margin-top:6px}
  #closeModal{font-size:24px}
  .btnpic{cursor:pointer}
  .image-container{position:relative;margin:6px}
  .image-container img{width:120px;height:120px;object-fit:cover;border-radius:8px}
  .delete-icon{position:absolute;top:6px;right:6px;border:none;background:#fff;border-radius:50%;padding:2px 6px;cursor:pointer}
  .centered{display:flex;justify-content:center;align-items:center;height:100vh}
</style>

<!-- ================== BARRE DE PROGRESSION (hors form) ================== -->
<div id="progressWrapper" class="centered" style="display:none;">
  <div id="progressContainer" style="width:100%;max-width:600px;background:#f3f3f3;border:1px solid #ccc;text-align:center;">
    <div id="progressBar" style="width:0;height:30px;background:#4caf50;display:inline-block;"></div>
    <span id="progressPercentage" style="display:block;margin-top:5px;">Téléchargement des photos : 0%</span>
  </div>
  <div id="status" style="margin-top:10px;text-align:center;"></div>
</div>

<!-- ========================= FORMULAIRE COMPLET ========================= -->
<form id="eventForm" action="" method="post" enctype="multipart/form-data">

  <div class="form-group">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">
      <div>
        <div class="event-builder-kicker">Assistant de commande</div>
        <p style="margin:12px 0 0; color:#475569; max-width:560px;">Le formulaire s'adapte automatiquement selon le type d'événement. Commencez par choisir votre événement, puis laissez-vous guider.</p>
      </div>
    </div>
  </div>

  <!-- Type d'événement -->
  <div class="form-group">
    <label for="eventType" class="form-label">Type d'événement</label>
    <div class="input-group mb-3">
      <span class="input-group-text bg-transparent"><i class="fas fa-calendar-alt"></i></span>
      <select class="form-control ps-15 bg-transparent" name="event" id="eventType">
        <option value="">-- Sélectionner --</option>
        <?php 
          $reqevent = $pdo->prepare("SELECT * FROM evenement ORDER BY cod_event ASC");
          $reqevent->execute();  
          while ($data_event = $reqevent->fetch()) {
        ?>
        <option value="<?php echo $data_event['cod_event']?>" <?php if(@$_POST['event'] == $data_event['cod_event']){echo "selected";} ?>>
          <?php echo $data_event['nom']?>
        </option>
        <?php } ?>  
      </select>
    </div>
  </div>

  <!-- Stepper (AFFICHÉ UNIQUEMENT pour Mariage) -->
  <div id="wizardStepper" class="wizard-nav hidden" aria-hidden="true">
    <span class="wizard-dot" data-stepdot="1"></span>
    <span class="wizard-dot" data-stepdot="2"></span>
    <span class="wizard-dot" data-stepdot="3"></span>
  </div>





  
     <fieldset class="border p-3" id="AccessoireGroup" style="display:none;margin-bottom:15px;border-radius:5px;">
        <div class="section-head" style="margin-bottom:6px;">
          <div>
            <h3>Accessoires</h3>
          </div>
          <span class="section-step-badge"><i class="fas fa-shopping-bag"></i></span>
        </div>
        <label class="labaccessoire">Que commandez-vous ?</label>
        <div class="accessory-grid">
        <?php 
          $reqmod = $pdo->prepare("SELECT * FROM modele_is WHERE type_mod = :type_mod ORDER BY CASE WHEN REPLACE(REPLACE(REPLACE(LOWER(nom), 'é', 'e'), 'è', 'e'), 'ê', 'e') LIKE 'invitation imprim%' OR REPLACE(REPLACE(REPLACE(LOWER(nom), 'é', 'e'), 'è', 'e'), 'ê', 'e') LIKE 'invitations imprim%' THEN 0 WHEN REPLACE(REPLACE(REPLACE(LOWER(nom), 'é', 'e'), 'è', 'e'), 'ê', 'e') LIKE 'invitation electron%' OR REPLACE(REPLACE(REPLACE(LOWER(nom), 'é', 'e'), 'è', 'e'), 'ê', 'e') LIKE 'invitations electron%' THEN 1 ELSE 2 END, nom ASC");
          $reqmod->execute([':type_mod' => 'accessoires']);  
          while ($data_mod = $reqmod->fetch()) {
            $accessoireNom = (string) ($data_mod['nom'] ?? '');
            $accessoireNomLower = function_exists('mb_strtolower') ? mb_strtolower($accessoireNom, 'UTF-8') : strtolower($accessoireNom);
            $accessoireNomNormalized = str_replace(['é', 'è', 'ê', 'ë'], 'e', $accessoireNomLower);
            $accessoireIcon = 'fa-gift';
            $requiresQuantity = true;

            if (strpos($accessoireNomNormalized, 'invitation') !== false && strpos($accessoireNomNormalized, 'elect') !== false) {
              $accessoireIcon = 'fa-mobile-screen-button';
              $requiresQuantity = false;
            } elseif (strpos($accessoireNomNormalized, 'invitation') !== false) {
              $accessoireIcon = 'fa-print';
            } elseif (strpos($accessoireNomNormalized, 'chevalet') !== false) {
              $accessoireIcon = 'fa-table-cells-large';
            }

            $isChecked = in_array((string) $data_mod['cod_mod'], array_map('strval', $_POST['accessoires'] ?? []), true);
            $postedQuantity = $_POST['accessoire_quantities'][(string) $data_mod['cod_mod']] ?? '1';
        ?>
        <label class="accessory-option" for="acc_<?php echo $data_mod['cod_mod']?>">
          <input type="checkbox" id="acc_<?php echo $data_mod['cod_mod']?>" name="accessoires[]" value="<?php echo $data_mod['cod_mod']?>" class="text-primary" data-requires-quantity="<?php echo $requiresQuantity ? '1' : '0'; ?>" data-quantity-target="acc_qty_<?php echo $data_mod['cod_mod']?>" onchange="toggleFields()" <?php echo $isChecked ? 'checked' : '';?>>
          <span class="accessory-card">
            <span class="accessory-icon"><i class="fas <?php echo $accessoireIcon; ?>"></i></span>
            <span class="accessory-content">
              <span class="accessory-title"><?php echo htmlspecialchars($accessoireNom, ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="accessory-badge">Sélectionner</span>
              <?php if ($requiresQuantity) { ?>
              <span class="accessory-quantity" id="acc_qty_<?php echo $data_mod['cod_mod']?>">
                <span>Quantité</span>
                <input type="number" min="1" step="1" inputmode="numeric" name="accessoire_quantities[<?php echo $data_mod['cod_mod']?>]" value="<?php echo htmlspecialchars((string) $postedQuantity, ENT_QUOTES, 'UTF-8'); ?>">
              </span>
              <?php } ?>
            </span>
          </span>
        </label>
        <?php } ?> 
      </div>


    </fieldset>

 
      
<!-- ✅ Déplacés ici : communs à tous les types -->
<div class="form-group" id="ModInvitation" style="display:none;margin-top:20px;">
  <label class="form-label">Modèle d'invitation</label>
  <div class="input-group mb-3 champmod" id="dropdownToggle" style="cursor:pointer;border:1px solid #ccc;">
    <span class="input-group-text bg-transparent spanmod"><i class="fas fa-ring"></i></span>
    <div class="selected-option" style="margin-left:15px;">
      <div class="model-picker-summary">
        <span class="model-picker-label">Choisir un modèle…</span>
        <span class="model-picker-meta">Ouvrez la galerie pour sélectionner le design d'invitation.</span>
      </div>
    </div>
  </div>
  <p class="field-help">La génération automatique d’une invitation personnalisée pour chaque invité avec son nom inscrit.</p>
  <div id="selectedModelPreview" class="selected-model-preview">
    <img id="selectedModelImage" class="selected-model-thumb" src="" alt="Aperçu du modèle sélectionné">
    <div class="selected-model-copy">
      <span class="selected-model-kicker">Aperçu sélectionné</span>
      <div id="selectedModelTitle" class="selected-model-title"></div>
      <div id="selectedModelMeta" class="selected-model-meta">La génération automatique d’une invitation personnalisée pour chaque invité avec son nom inscrit.</div>
      <div class="selected-model-actions">
        <span class="selected-model-chip"><i class="fas fa-check-circle"></i> Prêt à l'emploi</span>
        <span class="selected-model-chip"><i class="fas fa-images"></i> Galerie active</span>
      </div>
    </div>
  </div>
  <input type="hidden" name="modele_inv" id="modeleInv" value="">
</div>

<div class="form-group" id="ModChevalet" style="display:none;">
  <label for="chevaletModel" class="form-label">Modèle de chevalet de table</label>
  <div class="input-group mb-3">
    <span class="input-group-text bg-transparent"><i class="fas fa-gift"></i></span>
    <select class="form-control ps-15 bg-transparent" name="chevaletModel" id="chevaletModel">
      <option value="">-- Sélectionner --</option>
      <?php 
        $reqmod = $pdo->prepare("SELECT * FROM modele_is WHERE type_mod = :type_mod ORDER BY nom ASC");
        $reqmod->execute([':type_mod' => 'chevalet']);  
        while ($data_mod = $reqmod->fetch()) {
      ?>
      <option value="<?php echo $data_mod['cod_mod']?>" <?php if(@$_POST['chevaletModel'] == $data_mod['cod_mod']){echo "selected";} ?>>
        <?php echo $data_mod['nom']?>
      </option>
      <?php } ?>  
    </select>
  </div>
</div>

 


    
  <!-- ===================== WIZARD MARIAGE (3 étapes) ===================== -->
  <!-- Étape 1 : couple + accessoires -->
  <div class="step" id="step1">
    <div class="section-head">
      <div>
        <h3>Les mariés</h3>
        <p>Définissez le type de mariage et les informations principales du couple.</p>
      </div>
      <span class="section-step-badge">1</span>
    </div>

    <div class="form-grid two-col">
      <div class="form-group form-span-full" id="weddingTypeGroup">
        <label for="weddingType" class="form-label">Type de mariage</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-ring"></i></span>
          <select class="form-control ps-15 bg-transparent" name="weddingType" id="weddingType">
            <option value="">-- Sélectionner --</option>
            <option value="religieux">Religieux</option>
            <option value="coutumier">Coutumier</option>
            <option value="civil">Civil</option>
            <option value="Prédot">Prédot</option>
          </select>
        </div>
      </div>

      <div class="form-group" id="NomepouxGroup">
        <label for="prenomEpoux" class="form-label">Prénom de l'époux</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
          <input type="text" name="prenomEpoux" id="prenomEpoux" class="form-control ps-15 bg-transparent" placeholder="Ex. Samuel">
        </div>
      </div>

      <div class="form-group" id="PrenomepouxGroup">
        <label for="nomEpoux" class="form-label">Nom de l'époux</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
          <input type="text" name="nomEpoux" id="nomEpoux" class="form-control ps-15 bg-transparent" placeholder="Ex. Lutunga">
        </div>
      </div>

      <div class="form-group" id="NomepouseGroup">
        <label for="prenomEpouse" class="form-label">Prénom de l'épouse</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
          <input type="text" name="prenomEpouse" id="prenomEpouse" class="form-control ps-15 bg-transparent" placeholder="Ex. Esther">
        </div>
      </div>

      <div class="form-group" id="PrenomepouseGroup">
        <label for="nomEpouse" class="form-label">Nom de l'épouse</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
          <input type="text" name="nomEpouse" id="nomEpouse" class="form-control ps-15 bg-transparent" placeholder="Ex. Kanku">
        </div>
      </div>
    </div>


    <div class="wizard-actions">
      <button type="button" class="btn btn-primary" id="btnNext1">Suivant</button>
    </div>
  </div>

  <!-- Étape 2 : date/lieu/adresse + photos -->
  <div class="step" id="step2">
    <div class="wizard-actions">
      <button type="button" class="btn btn-outline-secondary" id="btnPrev2">Retour</button>
      <!-- Retour en haut -->

      <div class="section-head">
        <div>
          <h3>Lieu et médias</h3>
          <p>Indiquez quand et où l'événement aura lieu, puis ajoutez vos photos si vous en avez.</p>
        </div>
        <span class="section-step-badge">2</span>
      </div>

      <div class="form-grid two-col">
        <div class="input-group date chmpdate form-span-full" style="margin-top:15px;">
          <label for="datepicker" class="form-label">Date et heure</label>
          <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
            <input type="datetime-local" name="dateHeure" class="form-control ps-15 bg-transparent" id="datepicker">
          </div>
        </div>

        <div class="form-group">
          <label for="lieu" class="form-label">Salle / Espace</label>
          <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-map-marker-alt"></i></span>
            <input type="text" name="lieu" id="lieu" class="form-control ps-15 bg-transparent sallexx" placeholder="Ex. Salle Béatrice Hôtel">
          </div>
        </div>

        <div class="form-group">
          <label for="adresse" class="form-label">Adresse</label>
          <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-map"></i></span>
            <input type="text" name="adresse" id="adresse" class="form-control ps-15 bg-transparent" placeholder="Ex. Gombe, Kinshasa">
          </div>
        </div>

        <div class="form-group form-span-full">
          <label for="fileInput" class="form-label">Photos (facultatif)</label>
          <div class="input-group mb-3">
            <label for="fileInput" class="btnpic"><i class="fas fa-plus"></i> Importer les photos</label>
            <input type="file" name="photos[]" class="form-control ps-15 bg-transparent" accept="image/*" id="fileInput" multiple style="display:none;">
          </div>
          <p class="field-help">Ajoutez quelques visuels pour personnaliser plus rapidement votre commande.</p>
          <div id="previewContainer" class="mt-2" style="display:flex;flex-wrap:wrap;"></div>
        </div>
      </div>

      <button type="button" class="btn btn-primary" id="btnNext2">Suivant</button>
      <!-- Suivant en bas -->
    </div>
  </div>

  <!-- Étape 3 : familles + précisions + CGU + Commander -->
  <div class="step" id="step3">
    <div class="wizard-actions">
      <button type="button" class="btn btn-outline-secondary" id="btnPrev3">Retour</button>
      <!-- Retour en haut -->

      <div class="section-head">
        <div>
          <h3>Finalisation</h3>
          <p>Ajoutez vos précisions finales et confirmez votre commande.</p>
        </div>
        <span class="section-step-badge">3</span>
      </div>

      <div class="form-group">
        <label for="details" class="form-label">Autres précisions</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span>
          <textarea name="details" id="details" class="form-control ps-15 bg-transparent" rows="5" placeholder="Précisez ici les informations utiles pour notre équipe..."></textarea>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="checkbox">
            <input type="checkbox" id="basic_checkbox_1">
            <label for="basic_checkbox_1">J'accepte les <a href="../index.php?page=termes_conditions" target="_blank" rel="noopener noreferrer" class="text-primary">termes et conditions</a></label>
          </div>
        </div>
      </div>

      <button type="submit" id="BtnEvent" class="btn btn-primary">Commander</button>
      <!-- Commander en bas -->
    </div>
  </div>

  <!-- ===================== ÉCRAN UNIQUE POUR AUTRES TYPES ===================== -->
  <div id="singleStepOthers" style="display:none;">
    <div class="section-head">
      <div>
        <h3>Détails de votre événement</h3>
        <p>Renseignez les informations clés de votre commande en une seule étape.</p>
      </div>
      <span class="section-step-badge">1</span>
    </div>
    <!-- Anniversaire -->
    <div class="form-group" id="NomsAnnivGroup" style="display:none;">
      <label for="nomsfetard" class="form-label">Nom du fêté / de la fêtée</label>
      <div class="input-group mb-3">
        <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
        <input type="text" name="nomsfetard" id="nomsfetard" class="form-control ps-15 bg-transparent" placeholder="Ex. Grâce Mbuyi">
      </div>
    </div>

    <!-- Conférence -->
    <div class="form-group" id="ThemeConfGroup" style="display:none;">
      <label for="themeConf" class="form-label">Thème de la conférence</label>
      <div class="input-group mb-3">
        <span class="input-group-text bg-transparent"><i class="fas fa-comments"></i></span>
        <input type="text" name="themeConf" id="themeConf" class="form-control ps-15 bg-transparent" placeholder="Ex. Leadership et innovation">
      </div>
    </div>

    <!-- Commun (date/lieu/adresse/…/photos/CGU) -->
    <div class="form-grid two-col">
      <div class="input-group date chmpdate form-span-full" style="margin-top:15px;">
        <label for="datepicker2" class="form-label">Date et heure</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
          <input type="datetime-local" name="dateHeure" class="form-control ps-15 bg-transparent" id="datepicker2">
        </div>
      </div>

      <div class="form-group">
        <label for="lieu2" class="form-label">Salle / Espace</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-map-marker-alt"></i></span>
          <input type="text" name="lieu" id="lieu2" class="form-control ps-15 bg-transparent" placeholder="Ex. Salle Béatrice Hôtel">
        </div>
      </div>

      <div class="form-group">
        <label for="adresse2" class="form-label">Adresse</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-map"></i></span>
          <input type="text" name="adresse" id="adresse2" class="form-control ps-15 bg-transparent" placeholder="Ex. Gombe, Kinshasa">
        </div>
      </div>

      <div class="form-group form-span-full">
        <label for="fileInput2" class="form-label">Photos (facultatif)</label>
        <div class="input-group mb-3">
          <label for="fileInput2" class="btnpic"><i class="fas fa-plus"></i> Importer les photos</label>
          <input type="file" name="photos[]" class="form-control ps-15 bg-transparent" accept="image/*" id="fileInput2" multiple style="display:none;">
        </div>
        <p class="field-help">Ajoutez quelques visuels pour orienter la mise en page de votre invitation.</p>
        <div id="previewContainer2" class="mt-2" style="display:flex;flex-wrap:wrap;"></div>
      </div>

      <div class="form-group form-span-full">
        <label for="details2" class="form-label">Autres précisions</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span>
          <textarea name="details" id="details2" class="form-control ps-15 bg-transparent" rows="5" placeholder="Précisez ici les informations utiles pour notre équipe..."></textarea>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="checkbox">
          <input type="checkbox" id="basic_checkbox_1_o">
          <label for="basic_checkbox_1_o">J'accepte les <a href="../index.php?page=termes_conditions" target="_blank" rel="noopener noreferrer" class="text-primary">termes et conditions</a></label>
        </div>
      </div>
    </div>

    <div class="wizard-actions">
      <button type="submit" id="BtnEvent_o" class="btn btn-primary">Commander</button>
    </div>
  </div>

</form>

<!-- ============ MODAL (UN SEUL BLOC) : LISTE DES MODÈLES D'INVITATION ============ -->
<div id="myModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span id="closeModal" style="cursor:pointer; float:right;">&times;</span>
    <div class="model-modal-head">
      <div>
        <h2>Sélectionnez un modèle</h2>
        <p>Choisissez le design qui servira de base à votre invitation électronique. L’aperçu choisi restera visible dans le formulaire.</p>
      </div>
    </div>
    <div class="dropdown-content" id="weddingTypeDropdown">
      <?php 
        $reqmod = $pdo->prepare("SELECT * FROM modele_is WHERE type_mod = :type_mod ORDER BY nom ASC");
        $reqmod->execute([':type_mod' => 'invitation']);  
        while ($data_mod = $reqmod->fetch()) {
          $modelImage = '../images/modeleis/' . $data_mod['image'];
      ?>
      <div data-value="<?php echo $data_mod['cod_mod']?>" data-label="<?php echo htmlspecialchars($data_mod['nom'], ENT_QUOTES, 'UTF-8')?>" data-image="<?php echo htmlspecialchars($modelImage, ENT_QUOTES, 'UTF-8')?>">
        <div class="model-option-card">
          <img class="option-image" src="<?php echo $modelImage; ?>" alt="<?php echo htmlspecialchars($data_mod['nom'])?>">
          <div class="model-option-copy">
            <span class="model-option-title"><?php echo htmlspecialchars($data_mod['nom'], ENT_QUOTES, 'UTF-8')?></span>
            <span class="model-option-meta">Cliquez pour utiliser ce modèle dans votre commande.</span>
          </div>
        </div>
      </div>
      <?php } ?>  
    </div>
  </div>
</div>
<script>
// ========= RÉFÉRENCES =========
const eventTypeSelect   = document.getElementById('eventType');

// Wizard (mariage)
const wizardStepper     = document.getElementById('wizardStepper');
const dots              = [...document.querySelectorAll('[data-stepdot]')];
const step1             = document.getElementById('step1');
const step2             = document.getElementById('step2');
const step3             = document.getElementById('step3');
const steps             = [step1, step2, step3];

// Groupes mariage
const weddingTypeGroup  = document.getElementById('weddingTypeGroup');
const AccessoireGroup   = document.getElementById('AccessoireGroup');
const NomepouxGroup     = document.getElementById('NomepouxGroup');
const PrenomepouxGroup  = document.getElementById('PrenomepouxGroup');
const NomepouseGroup    = document.getElementById('NomepouseGroup');
const PrenomepouseGroup = document.getElementById('PrenomepouseGroup');

// Champs mariage
const weddingType  = document.getElementById('weddingType');
const prenomEpoux  = document.getElementById('prenomEpoux');
const prenomEpouse = document.getElementById('prenomEpouse');

// Étape 2 (wizard)
const datepicker   = document.getElementById('datepicker');
const lieu         = document.getElementById('lieu');
const adresse      = document.getElementById('adresse');

// Single-step pour autres types
const singleStepOthers = document.getElementById('singleStepOthers');
const NomsAnnivGroup  = document.getElementById('NomsAnnivGroup');
const ThemeConfGroup  = document.getElementById('ThemeConfGroup');
const nomsfetard      = document.getElementById('nomsfetard');
const themeConf       = document.getElementById('themeConf');

// Boutons wizard
const btnNext1 = document.getElementById('btnNext1');
const btnNext2 = document.getElementById('btnNext2');
const btnPrev2 = document.getElementById('btnPrev2');
const btnPrev3 = document.getElementById('btnPrev3');

// Modèle d’invitation (modal)
const dropdownToggle = document.getElementById('dropdownToggle');
const modal          = document.getElementById('myModal');
const closeModal     = document.getElementById('closeModal');
const dropdownContent= document.getElementById('weddingTypeDropdown');
const selectedOption = document.querySelector('.selected-option');
const modeleInvInput = document.getElementById('modeleInv');
const selectedModelPreview = document.getElementById('selectedModelPreview');
const selectedModelImage = document.getElementById('selectedModelImage');
const selectedModelTitle = document.getElementById('selectedModelTitle');
const selectedModelMeta = document.getElementById('selectedModelMeta');

// Upload preview (wizard)
const fileInput = document.getElementById('fileInput');
const previewContainer = document.getElementById('previewContainer');
// Upload preview (autres types)
const fileInput2 = document.getElementById('fileInput2');
const previewContainer2 = document.getElementById('previewContainer2');

// ========= OUTILS =========
function showStep(n){
  steps.forEach((s,idx)=> s.classList.toggle('active', idx === n-1));
  dots.forEach((d,idx)=> d.classList.toggle('active', idx <= n-1));
}
function showWizard(show){
  wizardStepper.classList.toggle('hidden', !show);
  wizardStepper.setAttribute('aria-hidden', show ? 'false' : 'true');
  if(show){
    singleStepOthers.style.display = 'none';
    showStep(1);
  }else{
    steps.forEach(s=> s.classList.remove('active'));
  }
}
function toggle(el, show){
  if(!el) return;
  el.style.display = show ? '' : 'none';
}

function resetSelectedModelPreview() {
  if (selectedModelPreview) {
    selectedModelPreview.style.display = 'none';
  }
  if (selectedModelImage) {
    selectedModelImage.src = '';
  }
  if (selectedModelTitle) {
    selectedModelTitle.textContent = '';
  }
  if (selectedModelMeta) {
    selectedModelMeta.textContent = 'La génération automatique d’une invitation personnalisée pour chaque invité avec son nom inscrit.';
  }
}

function applySelectedModelPreview(label, image) {
  if (!selectedModelPreview || !selectedModelImage || !selectedModelTitle) {
    return;
  }

  selectedModelImage.src = image || '';
  selectedModelTitle.textContent = label || '';
  selectedModelMeta.textContent = 'La génération automatique d’une invitation personnalisée pour chaque invité avec son nom inscrit.';
  selectedModelPreview.style.display = 'grid';
}

// Désactiver/activer tous les champs d’une section
function setSectionEnabled(sectionEl, enabled){
  if(!sectionEl) return;
  const controls = sectionEl.querySelectorAll('input, select, textarea, button');
  controls.forEach(ctrl => {
    if (ctrl.type === 'button' && ctrl.closest('.wizard-actions')) return;
    ctrl.disabled = !enabled;
  });
}

// Active Mariage et désactive l’autre, ou l’inverse
function applyMode(mode) {
  const enableWedding = (mode === 'wedding');

  if (enableWedding) {
    showWizard(true);
    singleStepOthers.style.display = 'none';
    toggle(weddingTypeGroup, true);
    toggle(NomepouxGroup, true);
    toggle(PrenomepouxGroup, true);
    toggle(NomepouseGroup, true);
    toggle(PrenomepouseGroup, true);
  } else {
    showWizard(false);
    singleStepOthers.style.display = '';
  }

  setSectionEnabled(step1, enableWedding);
  setSectionEnabled(step2, enableWedding);
  setSectionEnabled(step3, enableWedding);
  setSectionEnabled(singleStepOthers, !enableWedding);
}

// ========= LOGIQUE TYPE =========
eventTypeSelect.addEventListener('change', function(){
  const v = this.value;

  if (v) {
    // Afficher Accessoires pour tout type
    toggle(AccessoireGroup, true);

    if (v === '1'){             // Mariage
      applyMode('wedding');
    } else {                    // Autres
      applyMode('single');
      toggle(NomsAnnivGroup, v === '2'); // Anniversaire
      toggle(ThemeConfGroup, v === '3'); // Conférence
    }
  } else {
    // Aucun type sélectionné : on masque tout
    toggle(AccessoireGroup, false);
    showWizard(false);
    singleStepOthers.style.display = 'none';
    setSectionEnabled(step1, false);
    setSectionEnabled(step2, false);
    setSectionEnabled(step3, false);
    setSectionEnabled(singleStepOthers, false);
  }
});

// ========= WIZARD NAV =========
btnNext1?.addEventListener('click', function(){
  if(!weddingType.value){ alert("Sélectionnez le type de mariage."); return; }
  if(!prenomEpoux.value?.trim()){ alert("Prénom de l'époux requis."); return; }
  if(!prenomEpouse.value?.trim()){ alert("Prénom de l'épouse requis."); return; }
  showStep(2);
});
btnNext2?.addEventListener('click', function(){
  if(!datepicker.value){ alert("Date et heure requises."); return; }
  if(!lieu.value?.trim()){ alert("Lieu requis."); return; }
  if(!adresse.value?.trim()){ alert("Adresse requise."); return; }
  showStep(3);
});
btnPrev2?.addEventListener('click', ()=> showStep(1));
btnPrev3?.addEventListener('click', ()=> showStep(2));

// ========= Accessoires -> afficher sélecteurs modèles =========
function toggleFields() {
  const checkboxes = document.querySelectorAll('input[name="accessoires[]"]');
  let showInvitation = false, showChevalet = false;
  checkboxes.forEach(cb=>{
    if(cb.checked){
      if (cb.value == 1) showInvitation = true;
      if (cb.value == 3) showChevalet  = true;
    }
  });
  document.getElementById('ModInvitation').style.display = showInvitation ? 'block' : 'none';
  document.getElementById('ModChevalet').style.display  = showChevalet ? 'block' : 'none';

  checkboxes.forEach(cb => {
    const quantityTarget = cb.dataset.quantityTarget;
    const requiresQuantity = cb.dataset.requiresQuantity === '1';
    const wrapper = quantityTarget ? document.getElementById(quantityTarget) : null;
    const quantityInput = wrapper ? wrapper.querySelector('input') : null;

    if (!wrapper || !quantityInput) {
      return;
    }

    const shouldShow = requiresQuantity && cb.checked;
    wrapper.style.display = shouldShow ? 'flex' : 'none';
    quantityInput.disabled = !shouldShow;

    if (shouldShow && (!quantityInput.value || Number(quantityInput.value) < 1)) {
      quantityInput.value = '1';
    }
  });

  if (!showInvitation) {
    modeleInvInput.value = '';
    selectedOption.innerHTML = '<div class="model-picker-summary"><span class="model-picker-label">Choisir un modèle…</span><span class="model-picker-meta">Ouvrez la galerie pour sélectionner le design d\'invitation.</span></div>';
    dropdownContent?.querySelectorAll('div[data-value]').forEach(item => item.classList.remove('is-selected'));
    resetSelectedModelPreview();
  }
}


window.toggleFields = toggleFields;

// ========= Modal modèle invitation =========
dropdownToggle?.addEventListener('click', ()=> modal.style.display = 'block');
closeModal?.addEventListener('click', ()=> modal.style.display = 'none');
window.addEventListener('click', (e)=>{ if(e.target === modal){ modal.style.display = 'none'; } });
dropdownContent?.addEventListener('click', function(event){
  const cell = event.target.closest('div[data-value]');
  if(!cell) return;
  dropdownContent.querySelectorAll('div[data-value]').forEach(item => item.classList.remove('is-selected'));
  cell.classList.add('is-selected');
  const value = cell.getAttribute('data-value');
  const label = cell.getAttribute('data-label') || 'Modèle sélectionné';
  const image = cell.getAttribute('data-image') || '';
  modeleInvInput.value = value;
  selectedOption.innerHTML = '<div class="model-picker-summary"><span class="model-picker-label">' + label + '</span><span class="model-picker-meta">Modèle prêt à être appliqué à votre commande.</span></div>';
  applySelectedModelPreview(label, image);
  modal.style.display = 'none';
});

// ========= Preview images =========
fileInput?.addEventListener('change', function(event){
  const files = Array.from(event.target.files);
  previewContainer.innerHTML = '';
  files.forEach(file=>{
    const reader = new FileReader();
    reader.onload = function(e){
      const wrap = document.createElement('div');
      wrap.className = 'image-container';
      const img = document.createElement('img');
      img.src = e.target.result;
      const del = document.createElement('button');
      del.innerHTML = '✖';
      del.className = 'delete-icon';
      del.addEventListener('click', ()=> wrap.remove());
      wrap.appendChild(img); wrap.appendChild(del);
      previewContainer.appendChild(wrap);
    };
    reader.readAsDataURL(file);
  });
});
fileInput2?.addEventListener('change', function(event){
  const files = Array.from(event.target.files);
  previewContainer2.innerHTML = '';
  files.forEach(file=>{
    const reader = new FileReader();
    reader.onload = function(e){
      const wrap = document.createElement('div');
      wrap.className = 'image-container';
      const img = document.createElement('img');
      img.src = e.target.result;
      const del = document.createElement('button');
      del.innerHTML = '✖';
      del.className = 'delete-icon';
      del.addEventListener('click', ()=> wrap.remove());
      wrap.appendChild(img); wrap.appendChild(del);
      previewContainer2.appendChild(wrap);
    };
    reader.readAsDataURL(file);
  });
});

// ========= CGU (wizard & autres) =========
document.addEventListener("DOMContentLoaded", function() {
  const submitButton = document.getElementById("BtnEvent");
  const checkbox     = document.getElementById("basic_checkbox_1");
  if(submitButton && checkbox){
    submitButton.disabled = true;
    checkbox.addEventListener("change", function() {
      submitButton.disabled = !checkbox.checked;
    });
  }

  const submitButtonO = document.getElementById("BtnEvent_o");
  const checkboxO     = document.getElementById("basic_checkbox_1_o");
  if(submitButtonO && checkboxO){
    submitButtonO.disabled = true;
    checkboxO.addEventListener("change", function() {
      submitButtonO.disabled = !checkboxO.checked;
    });
  }
});

// ========= SUBMIT AJAX + Progress =========
document.getElementById('eventForm').addEventListener('submit', function(ev) {
  ev.preventDefault();

  const selectedAccessoires = Array.from(document.querySelectorAll('input[name="accessoires[]"]:checked'));
  for (const accessoire of selectedAccessoires) {
    if (accessoire.dataset.requiresQuantity !== '1') {
      continue;
    }

    const quantityWrapper = document.getElementById(accessoire.dataset.quantityTarget || '');
    const quantityInput = quantityWrapper ? quantityWrapper.querySelector('input') : null;
    const quantityValue = quantityInput ? Number(quantityInput.value) : 0;

    if (!quantityInput || !Number.isFinite(quantityValue) || quantityValue < 1) {
      alert("Veuillez renseigner une quantité valide pour chaque accessoire sélectionné.");
      quantityInput?.focus();
      return;
    }
  }

  const typeEvent = eventTypeSelect.value;
  if (!typeEvent) { alert("Veuillez sélectionner le type de l'événement."); return; }

  if (typeEvent === '1'){ // Mariage
    if (!weddingType.value) { alert("Type de mariage requis."); return; }
    if (!prenomEpoux.value) { alert("Prénom de l'époux requis."); return; }
    if (!prenomEpouse.value){ alert("Prénom de l'épouse requis."); return; }
    if (!datepicker.value)  { alert("Date/heure requises."); return; }
    if (!lieu.value)        { alert("Lieu requis."); return; }
    if (!adresse.value)     { alert("Adresse requise."); return; }
    if (!document.getElementById('basic_checkbox_1').checked){ alert("Veuillez accepter les termes."); return; }
  } else {
    const date2 = document.getElementById('datepicker2').value;
    const lieu2 = document.getElementById('lieu2').value;
    const adr2  = document.getElementById('adresse2').value;

    if (typeEvent === '2'){ if (!nomsfetard.value?.trim()){ alert("Nom du/de la fêté(e) requis."); return; } }
    if (typeEvent === '3'){ if (!themeConf.value?.trim()){ alert("Thème de la conférence requis."); return; } }

    if (!date2){ alert("Date/heure requises."); return; }
    if (!lieu2){ alert("Lieu requis."); return; }
    if (!adr2){  alert("Adresse requise."); return; }
    if (!document.getElementById('basic_checkbox_1_o').checked){ alert("Veuillez accepter les termes."); return; }
  }

  // Affichage barre
  this.style.display = 'none';
  const progressWrapper = document.getElementById('progressWrapper');
  progressWrapper.style.display = 'flex';
  progressWrapper.classList.add('centered');
  document.getElementById('progressContainer').style.display = 'block';
  document.getElementById('progressBar').style.width = '0%';

  const formData = new FormData(this);
  const xhr = new XMLHttpRequest();
  xhr.open('POST', '', true);

  xhr.upload.onprogress = function(e){
    if(e.lengthComputable){
      const pct = (e.loaded / e.total) * 100;
      document.getElementById('progressBar').style.width = pct + '%';
      document.getElementById('progressPercentage').textContent = 'Téléchargement des photos : ' + Math.round(pct) + '%';
    }
  };

  xhr.onload = function(){
    if (xhr.status === 200) {
      Swal.fire({
        title: "Evénement créé !",
        text: "Votre événement est ajouté avec succès.",
        icon: "success",
        confirmButtonText: "Terminer"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "index.php?page=mb_accueil";
        }
      });
    } else {
      document.getElementById('status').innerHTML = 'Erreur lors du traitement.';
    }
  };

  xhr.send(formData);
});

// ========= INIT =========
(function init(){
  toggle(AccessoireGroup, false);
  showWizard(false);
  singleStepOthers.style.display = 'none';
  toggleFields();
})();
</script>



<br><br>

                  </div>
                </div>
              </div><!-- /col -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include('footer.php')?>

<!-- ====== VENDORS (garde ton stack) ====== -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
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
<script src="html/template/horizontal/src/js/demo.js"></script>
<script src="html/template/horizontal/src/js/jquery.smartmenus.js"></script>
<script src="html/template/horizontal/src/js/menus.js"></script>
<script src="html/template/horizontal/src/js/template.js"></script>
<script src="html/template/horizontal/src/js/pages/dashboard.js"></script>
<script src="html/template/horizontal/src/js/pages/slider.js"></script>
<script src="html/assets/vendor_components/full-calendar/moment.js"></script>
<script src="html/assets/vendor_components/full-calendar/fullcalendar.min.js"></script>
<script src="html/template/horizontal/src/js/pages/advanced-form-element.js"></script>
