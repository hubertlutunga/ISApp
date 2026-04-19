<?php
// ====== ENTÊTE PAGE ======

$accessoryCatalog = EventOrderService::accessoryCatalog($pdo);
$promoCatalog = EventOrderService::promoCatalog($pdo);
$paymentOptions = EventOrderService::paymentOptions();

$invitationModelStmt = $pdo->prepare("SELECT * FROM modele_is WHERE type_mod = :type_mod AND is_active = 1 ORDER BY nom ASC");
$invitationModelStmt->execute([':type_mod' => 'invitation']);
$invitationModelRows = $invitationModelStmt->fetchAll(PDO::FETCH_ASSOC);

$postedAccessories = array_map('strval', $_POST['accessoires'] ?? []);
$requiresInvitationModelQuantity = in_array('1', $postedAccessories, true);
$selectedInvitationModels = EventOrderService::normalizeInvitationModels(
  array_map('strval', $_POST['invitation_models'] ?? []),
  (array) ($_POST['invitation_model_quantities'] ?? []),
  $requiresInvitationModelQuantity
);
$selectedInvitationModels = EventOrderService::hydrateInvitationModels($selectedInvitationModels, $invitationModelRows);

if ($selectedInvitationModels !== []) {
  $_POST['modele_inv'] = (string) $selectedInvitationModels[0]['model_id'];
}

if ($requiresInvitationModelQuantity && $selectedInvitationModels !== []) {
  $_POST['accessoire_quantities']['1'] = (string) array_sum(array_map(
    static fn(array $model): int => (int) ($model['quantity'] ?? 1),
    $selectedInvitationModels
  ));
}

$checkoutPreview = EventOrderService::summarizeSelection(
  $postedAccessories,
  (array) ($_POST['accessoire_quantities'] ?? []),
  $selectedInvitationModels,
  $accessoryCatalog,
  $promoCatalog,
  (string) ($_POST['promo_code'] ?? '')
);

$promoCodeHints = implode(', ', array_keys($promoCatalog));
$currentSessionUser = null;

if (!empty($_SESSION['user_phone'])) {
  $sessionUserStmt = $pdo->prepare("SELECT cod_user FROM is_users WHERE phone = ? LIMIT 1");
  $sessionUserStmt->execute([$_SESSION['user_phone']]);
  $currentSessionUser = $sessionUserStmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $isAjaxRequest = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
  $type_event = $_POST['event'] ?? null;
  $nomsAnniv = $_POST['nomsfetard'] ?? null;
  $prenomEpoux = $_POST['prenomEpoux'] ?? null;
  $prenomEpouse = $_POST['prenomEpouse'] ?? null;
  $primaryInvitationModel = $_POST['modele_inv'] ?? null;
  $currentUserId = (int) (($currentSessionUser['cod_user'] ?? ($_SESSION['cod_user'] ?? 0)) ?: 0);
  $initialemar = EventUpdateService::buildInitialeFromRequest($_POST);

  try {
    $cod_event = EventCreationService::createManagedEvent(
      $pdo,
      [
        'cod_user' => $currentUserId > 0 ? $currentUserId : null,
        'type_event' => $type_event,
        'type_mar' => $_POST['weddingType'] ?? null,
        'modele_inv' => $primaryInvitationModel,
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
        'lang' => $_POST['invitation_lang'] ?? null,
        'ordrepri' => $_POST['nameOrder'] ?? null,
        'accessoire_quantities' => $_POST['accessoire_quantities'] ?? [],
        'invitation_models' => $selectedInvitationModels,
        'checkout' => array_merge($checkoutPreview, [
          'payment_type' => $_POST['payment_type'] ?? null,
        ]),
      ],
      $_POST['accessoires'] ?? [],
      $_FILES['photos'] ?? null,
      '../photosevent',
      $isAppConfig
    );

    if ($isAjaxRequest) {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'success' => true,
        'cod_event' => $cod_event,
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      return;
    }
  } catch (PDOException $e) {
    if ($isAjaxRequest) {
      http_response_code(500);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      return;
    }

    echo "Erreur lors de l'enregistrement : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    return;
  }
}
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
    cursor:zoom-in;
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
  .selected-model-price{
    color:#0f766e;
    font-size:16px;
    font-weight:800;
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
  .selected-model-stack{
    display:flex;
    flex-direction:column;
    gap:14px;
    margin-top:16px;
  }
  .selected-model-item{
    display:grid;
    grid-template-columns:72px minmax(0, 1fr) auto;
    gap:14px;
    align-items:center;
    padding:14px;
    border:1px solid rgba(148,163,184,.2);
    border-radius:18px;
    background:#fff;
    box-shadow:0 12px 24px rgba(15,23,42,.05);
  }
  .selected-model-item img{
    width:72px;
    height:72px;
    object-fit:cover;
    border-radius:16px;
    border:1px solid rgba(148,163,184,.18);
    background:#fff;
    cursor:zoom-in;
  }
  .selected-model-item-copy{
    display:flex;
    flex-direction:column;
    gap:8px;
    min-width:0;
  }
  .selected-model-item-title{
    color:#0f172a;
    font-size:15px;
    font-weight:800;
  }
  .selected-model-item-price{
    color:#0f766e;
    font-size:13px;
    font-weight:800;
  }
  .selected-model-item-meta{
    color:#64748b;
    font-size:13px;
    line-height:1.5;
  }
  .selected-model-item-controls{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
  }
  .selected-model-item-controls input{
    width:96px;
    min-height:42px;
    border-radius:12px !important;
    text-align:center;
  }
  .selected-model-item-remove{
    border:none;
    background:rgba(220,38,38,.08);
    color:#b91c1c;
    border-radius:999px;
    padding:10px 14px;
    font-size:12px;
    font-weight:800;
  }
  .checkout-panel{
    display:flex;
    flex-direction:column;
    gap:14px;
  }
  .checkout-summary{
    display:flex;
    flex-direction:column;
    gap:12px;
  }
  .checkout-summary-card{
    padding:16px 18px;
    border:1px solid rgba(148,163,184,.18);
    border-radius:18px;
    background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
    box-shadow:0 16px 28px rgba(15,23,42,.05);
  }
  .checkout-line{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:16px;
    color:#0f172a;
    font-size:14px;
  }
  .checkout-line + .checkout-line{
    margin-top:10px;
    padding-top:10px;
    border-top:1px dashed rgba(148,163,184,.25);
  }
  .checkout-line small{
    display:block;
    margin-top:4px;
    color:#64748b;
    font-size:12px;
  }
  .checkout-total{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:16px 18px;
    border-radius:18px;
    background:linear-gradient(135deg,#0f766e 0%,#0f9f85 100%);
    color:#fff;
    box-shadow:0 18px 30px rgba(15,118,110,.18);
  }
  .checkout-total span{
    font-size:13px;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
    opacity:.82;
  }
  .checkout-total strong{
    font-size:26px;
    font-weight:800;
  }
  .checkout-empty{
    padding:18px;
    border-radius:18px;
    background:#f8fafc;
    border:1px dashed rgba(148,163,184,.32);
    color:#64748b;
    font-size:14px;
    line-height:1.6;
  }
  .promo-note{
    margin-top:-4px;
    color:#64748b;
    font-size:13px;
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
  .model-option-image-trigger{
    position:relative;
    display:block;
    width:100%;
    border:none;
    padding:0;
    border-radius:14px;
    overflow:hidden;
    background:#f8fafc;
    cursor:zoom-in;
  }
  .model-option-image-trigger::after{
    content:'Agrandir';
    position:absolute;
    right:10px;
    bottom:10px;
    padding:6px 10px;
    border-radius:999px;
    background:rgba(15,23,42,.72);
    color:#fff;
    font-size:11px;
    font-weight:700;
    letter-spacing:.04em;
  }
  .model-option-copy{
    display:flex;
    flex-direction:column;
    gap:5px;
  }
  .model-option-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:10px;
  }
  .model-option-title{
    color:#0f172a;
    font-size:15px;
    font-weight:800;
    line-height:1.35;
  }
  .model-option-reference{
    display:inline-flex;
    align-self:flex-start;
    padding:5px 9px;
    border-radius:999px;
    background:#eff6ff;
    color:#1d4ed8;
    font-size:11px;
    font-weight:800;
    letter-spacing:.05em;
    text-transform:uppercase;
  }
  .model-option-price{
    color:#0f766e;
    font-size:14px;
    font-weight:800;
    white-space:nowrap;
  }
  .model-option-meta{
    color:#64748b;
    font-size:13px;
    line-height:1.5;
  }
  .model-option-hint{
    color:#94a3b8;
    font-size:11px;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
  }
  .option-image{display:block;width:100%;height:190px;object-fit:cover;border-radius:14px;border:1px solid rgba(148,163,184,.16);margin-top:0}
  .image-lightbox{
    position:fixed;
    inset:0;
    z-index:10001;
    display:none;
    align-items:center;
    justify-content:center;
    padding:22px;
    background:rgba(15,23,42,.86);
  }
  .image-lightbox.is-open{
    display:flex;
  }
  .image-lightbox-dialog{
    position:relative;
    width:min(920px, 100%);
    max-height:100%;
    padding:18px;
    border-radius:24px;
    background:#fff;
    box-shadow:0 30px 80px rgba(15,23,42,.32);
  }
  .image-lightbox-close{
    position:absolute;
    top:14px;
    right:14px;
    width:42px;
    height:42px;
    border:none;
    border-radius:999px;
    background:rgba(15,23,42,.08);
    color:#0f172a;
    font-size:24px;
    line-height:1;
    cursor:pointer;
  }
  .image-lightbox-figure{
    margin:0;
    display:flex;
    flex-direction:column;
    gap:14px;
  }
  .image-lightbox-figure img{
    width:100%;
    max-height:min(78vh, 820px);
    object-fit:contain;
    border-radius:18px;
    background:#f8fafc;
  }
  .image-lightbox-caption{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
  }
  .image-lightbox-title{
    color:#0f172a;
    font-size:18px;
    font-weight:800;
  }
  .image-lightbox-meta{
    color:#0f766e;
    font-size:14px;
    font-weight:800;
  }
  @media (max-width: 767px){
    .event-builder-header{padding:24px 22px 6px !important}
    .event-builder-form{padding:22px 20px 28px !important}
    .event-builder-title{font-size:24px}
    .section-head{flex-direction:column}
    .form-grid.two-col{grid-template-columns:minmax(0, 1fr)}
    .accessory-grid{grid-template-columns:minmax(0, 1fr)}
    .selected-model-preview,
    .selected-model-item{grid-template-columns:minmax(0, 1fr)}
    #eventForm > .form-group,
    #eventForm > fieldset,
    #eventForm > .step,
    #eventForm > #singleStepOthers{padding:16px}
  }

  /* Modal modèles */
  .modal{display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;overflow:auto;background:rgba(0,0,0,.4)}
  .dropdown-content{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-top:10px}
  .option-image{width:100%;height:110px;object-fit:cover;border-radius:6px;margin-top:6px}
  .image-lightbox-dialog{padding:14px}
  .image-lightbox-caption{align-items:flex-start}
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
    <span class="wizard-dot" data-stepdot="4"></span>
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
          $reqmod = $pdo->prepare("SELECT * FROM modele_is WHERE type_mod = :type_mod AND is_active = 1 ORDER BY CASE WHEN REPLACE(REPLACE(REPLACE(LOWER(nom), 'é', 'e'), 'è', 'e'), 'ê', 'e') LIKE 'invitation imprim%' OR REPLACE(REPLACE(REPLACE(LOWER(nom), 'é', 'e'), 'è', 'e'), 'ê', 'e') LIKE 'invitations imprim%' THEN 0 WHEN REPLACE(REPLACE(REPLACE(LOWER(nom), 'é', 'e'), 'è', 'e'), 'ê', 'e') LIKE 'invitation electron%' OR REPLACE(REPLACE(REPLACE(LOWER(nom), 'é', 'e'), 'è', 'e'), 'ê', 'e') LIKE 'invitations electron%' THEN 1 ELSE 2 END, nom ASC");
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
  <label class="form-label">Modèles d'invitation</label>
  <div class="input-group mb-3 champmod" id="dropdownToggle" style="cursor:pointer;border:1px solid #ccc;">
    <span class="input-group-text bg-transparent spanmod"><i class="fas fa-ring"></i></span>
    <div class="selected-option" style="margin-left:15px;">
      <div class="model-picker-summary">
        <span class="model-picker-label">Choisir un ou plusieurs modèles…</span>
        <span class="model-picker-meta">Ouvrez la galerie pour composer votre sélection d'invitations.</span>
      </div>
    </div>
  </div>
  <p class="field-help">Vous pouvez commander un, deux ou plusieurs modèles. Pour l’invitation imprimée, la quantité se définit modèle par modèle.</p>
  <div id="selectedModelPreview" class="selected-model-preview">
    <img id="selectedModelImage" class="selected-model-thumb" src="" alt="Aperçu du modèle sélectionné">
    <div class="selected-model-copy">
      <span class="selected-model-kicker">Aperçu principal</span>
      <div id="selectedModelTitle" class="selected-model-title"></div>
      <div id="selectedModelPrice" class="selected-model-price"></div>
      <div id="selectedModelMeta" class="selected-model-meta">La génération automatique d’une invitation personnalisée pour chaque invité avec son nom inscrit.</div>
      <div class="selected-model-actions">
        <span class="selected-model-chip"><i class="fas fa-check-circle"></i> Prêt à l'emploi</span>
        <span class="selected-model-chip"><i class="fas fa-images"></i> Sélection multiple</span>
      </div>
    </div>
  </div>
  <div id="selectedModelsList" class="selected-model-stack"></div>
  <input type="hidden" name="modele_inv" id="modeleInv" value="<?php echo htmlspecialchars((string) ($_POST['modele_inv'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
</div>

<div class="form-group" id="InvitationLanguageGroup" style="display:none;">
  <label for="invitationLang" class="form-label">Langue du texte sur l'invitation</label>
  <div class="input-group mb-3">
    <span class="input-group-text bg-transparent"><i class="fas fa-language"></i></span>
    <select class="form-control ps-15 bg-transparent" name="invitation_lang" id="invitationLang">
      <option value="">-- Sélectionner --</option>
      <option value="fr" <?php if (($_POST['invitation_lang'] ?? '') === 'fr') { echo 'selected'; } ?>>Français</option>
      <option value="en" <?php if (($_POST['invitation_lang'] ?? '') === 'en') { echo 'selected'; } ?>>Anglais</option>
    </select>
  </div>
</div>

<div class="form-group" id="ModChevalet" style="display:none;">
  <label for="chevaletModel" class="form-label">Modèle de chevalet de table</label>
  <div class="input-group mb-3">
    <span class="input-group-text bg-transparent"><i class="fas fa-gift"></i></span>
    <select class="form-control ps-15 bg-transparent" name="chevaletModel" id="chevaletModel">
      <option value="">-- Sélectionner --</option>
      <?php 
        $reqmod = $pdo->prepare("SELECT * FROM modele_is WHERE type_mod = :type_mod AND is_active = 1 ORDER BY nom ASC");
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
            <option value="Mariage Coutumier" <?php if (($_POST['weddingType'] ?? '') === 'Mariage Coutumier') { echo 'selected'; } ?>>Mariage Coutumier</option>
            <option value="Mariage Civil" <?php if (($_POST['weddingType'] ?? '') === 'Mariage Civil') { echo 'selected'; } ?>>Mariage Civil</option>
            <option value="Mariage religieux" <?php if (($_POST['weddingType'] ?? '') === 'Mariage religieux') { echo 'selected'; } ?>>Mariage religieux</option>
            <option value="Soirée dansante" <?php if (($_POST['weddingType'] ?? '') === 'Soirée dansante') { echo 'selected'; } ?>>Soirée dansante</option>
          </select>
        </div>
      </div>

      <div class="form-group form-span-full" id="NameOrderGroup">
        <label for="nameOrder" class="form-label">Ordre des prénoms sur l'invitation</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-sort-alpha-down"></i></span>
          <select class="form-control ps-15 bg-transparent" name="nameOrder" id="nameOrder">
            <option value="">-- Sélectionner --</option>
            <option value="f" <?php if (($_POST['nameOrder'] ?? '') === 'f') { echo 'selected'; } ?>>Commencer par le prénom de la femme</option>
            <option value="m" <?php if (($_POST['nameOrder'] ?? '') === 'm') { echo 'selected'; } ?>>Commencer par le prénom de l'homme</option>
          </select>
        </div>
      </div>

      <div class="form-group" id="NomepouxGroup">
        <label for="prenomEpoux" class="form-label">Prénom de l'époux</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
          <input type="text" name="prenomEpoux" id="prenomEpoux" class="form-control ps-15 bg-transparent" placeholder="Ex. Samuel" value="<?php echo htmlspecialchars((string) ($_POST['prenomEpoux'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>
      </div>

      <div class="form-group" id="PrenomepouxGroup">
        <label for="nomEpoux" class="form-label">Nom de l'époux</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
          <input type="text" name="nomEpoux" id="nomEpoux" class="form-control ps-15 bg-transparent" placeholder="Ex. Lutunga" value="<?php echo htmlspecialchars((string) ($_POST['nomEpoux'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>
      </div>

      <div class="form-group" id="NomepouseGroup">
        <label for="prenomEpouse" class="form-label">Prénom de l'épouse</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
          <input type="text" name="prenomEpouse" id="prenomEpouse" class="form-control ps-15 bg-transparent" placeholder="Ex. Ursule" value="<?php echo htmlspecialchars((string) ($_POST['prenomEpouse'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>
      </div>

      <div class="form-group" id="PrenomepouseGroup">
        <label for="nomEpouse" class="form-label">Nom de l'épouse</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
          <input type="text" name="nomEpouse" id="nomEpouse" class="form-control ps-15 bg-transparent" placeholder="Ex. Mpia" value="<?php echo htmlspecialchars((string) ($_POST['nomEpouse'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
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
          <textarea name="details" id="details" class="form-control ps-15 bg-transparent" rows="5" placeholder="Précisez ici les informations utiles pour notre équipe..."><?php echo htmlspecialchars((string) ($_POST['details'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
      </div>

      <button type="button" id="btnNext3" class="btn btn-primary">Voir le montant</button>
    </div>
  </div>

  <div class="step" id="step4">
    <div class="wizard-actions">
      <button type="button" class="btn btn-outline-secondary" id="btnPrev4">Retour</button>

      <div class="section-head">
        <div>
          <h3>Montant et paiement</h3>
          <p>Vérifiez votre commande, appliquez un code promo si nécessaire et choisissez votre mode de paiement.</p>
        </div>
        <span class="section-step-badge">4</span>
      </div>

      <div class="checkout-panel">
        <div id="checkoutSummaryWedding" class="checkout-summary"></div>

        <div class="form-grid two-col">
          <div class="form-group">
            <label for="promoCode" class="form-label">Code promo</label>
            <div class="input-group mb-3">
              <span class="input-group-text bg-transparent"><i class="fas fa-ticket-alt"></i></span>
              <input type="text" name="promo_code" id="promoCode" class="form-control ps-15 bg-transparent" placeholder="Ex. ISWELCOME" value="<?php echo htmlspecialchars((string) ($_POST['promo_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <p class="promo-note">Codes actifs: <?php echo htmlspecialchars($promoCodeHints, ENT_QUOTES, 'UTF-8'); ?></p>
          </div>

          <div class="form-group">
            <label for="paymentType" class="form-label">Type de paiement</label>
            <div class="input-group mb-3">
              <span class="input-group-text bg-transparent"><i class="fas fa-credit-card"></i></span>
              <select class="form-control ps-15 bg-transparent" name="payment_type" id="paymentType">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($paymentOptions as $paymentValue => $paymentLabel) { ?>
                <option value="<?php echo htmlspecialchars($paymentValue, ENT_QUOTES, 'UTF-8'); ?>" <?php if (($_POST['payment_type'] ?? '') === $paymentValue) { echo 'selected'; } ?>><?php echo htmlspecialchars($paymentLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
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
          <textarea name="details" id="details2" class="form-control ps-15 bg-transparent" rows="5" placeholder="Précisez ici les informations utiles pour notre équipe..."><?php echo htmlspecialchars((string) ($_POST['details'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
      </div>

      <div class="form-group form-span-full">
        <div class="checkout-panel">
          <div id="checkoutSummaryOther" class="checkout-summary"></div>

          <div class="form-grid two-col">
            <div class="form-group">
              <label for="promoCodeOther" class="form-label">Code promo</label>
              <div class="input-group mb-3">
                <span class="input-group-text bg-transparent"><i class="fas fa-ticket-alt"></i></span>
                <input type="text" name="promo_code" id="promoCodeOther" class="form-control ps-15 bg-transparent" placeholder="Ex. ISWELCOME" value="<?php echo htmlspecialchars((string) ($_POST['promo_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <p class="promo-note">Codes actifs: <?php echo htmlspecialchars($promoCodeHints, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="form-group">
              <label for="paymentTypeOther" class="form-label">Type de paiement</label>
              <div class="input-group mb-3">
                <span class="input-group-text bg-transparent"><i class="fas fa-credit-card"></i></span>
                <select class="form-control ps-15 bg-transparent" name="payment_type" id="paymentTypeOther">
                  <option value="">-- Sélectionner --</option>
                  <?php foreach ($paymentOptions as $paymentValue => $paymentLabel) { ?>
                  <option value="<?php echo htmlspecialchars($paymentValue, ENT_QUOTES, 'UTF-8'); ?>" <?php if (($_POST['payment_type'] ?? '') === $paymentValue) { echo 'selected'; } ?>><?php echo htmlspecialchars($paymentLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>
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
      <button type="button" id="finishModelSelection" class="btn btn-primary" style="width:auto !important; min-height:44px; padding:0 18px;">Terminer</button>
    </div>
    <div class="dropdown-content" id="weddingTypeDropdown">
      <?php foreach ($invitationModelRows as $data_mod) {
          $modelImage = '../images/modeleis/' . $data_mod['image'];
          $modelReference = 'INV-' . str_pad((string) max(1, (int) ($data_mod['cod_mod'] ?? 0)), 3, '0', STR_PAD_LEFT);
          $modelPriceValue = round((float) ($data_mod['unit_price'] ?? 0), 2);
          $modelPriceLabel = $modelPriceValue > 0 ? number_format($modelPriceValue, 2, '.', ' ') . ' $' : 'Sur demande';
      ?>
      <div data-value="<?php echo $data_mod['cod_mod']?>" data-label="<?php echo htmlspecialchars($data_mod['nom'], ENT_QUOTES, 'UTF-8')?>" data-image="<?php echo htmlspecialchars($modelImage, ENT_QUOTES, 'UTF-8')?>" data-price="<?php echo htmlspecialchars($modelPriceLabel, ENT_QUOTES, 'UTF-8')?>">
        <div class="model-option-card">
          <button type="button" class="model-option-image-trigger" data-preview-image="<?php echo htmlspecialchars($modelImage, ENT_QUOTES, 'UTF-8')?>" data-preview-title="<?php echo htmlspecialchars($data_mod['nom'], ENT_QUOTES, 'UTF-8')?>" data-preview-price="<?php echo htmlspecialchars($modelPriceLabel, ENT_QUOTES, 'UTF-8')?>">
            <img class="option-image" src="<?php echo $modelImage; ?>" alt="<?php echo htmlspecialchars($data_mod['nom'])?>">
          </button>
          <div class="model-option-copy">
            <div class="model-option-head">
              <span class="model-option-title"><?php echo htmlspecialchars($data_mod['nom'], ENT_QUOTES, 'UTF-8')?></span>
              <span class="model-option-price"><?php echo htmlspecialchars($modelPriceLabel, ENT_QUOTES, 'UTF-8')?></span>
            </div>
            <span class="model-option-reference"><?php echo htmlspecialchars($modelReference, ENT_QUOTES, 'UTF-8')?></span>
            <span class="model-option-meta">Cliquez pour ajouter ou retirer ce modèle dans votre commande.</span>
            <span class="model-option-hint">Touchez la photo pour un aperçu agrandi.</span>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<div id="modelImageLightbox" class="image-lightbox" aria-hidden="true">
  <div class="image-lightbox-dialog" role="dialog" aria-modal="true" aria-labelledby="modelImageLightboxTitle">
    <button type="button" class="image-lightbox-close" id="closeModelImageLightbox" aria-label="Fermer l'aperçu">&times;</button>
    <figure class="image-lightbox-figure">
      <img id="modelImageLightboxImg" src="" alt="">
      <figcaption class="image-lightbox-caption">
        <span id="modelImageLightboxTitle" class="image-lightbox-title"></span>
        <span id="modelImageLightboxMeta" class="image-lightbox-meta"></span>
      </figcaption>
    </figure>
  </div>
</div>
<script>
  const accessoryCatalog = <?php echo json_encode($accessoryCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const promoCatalog = <?php echo json_encode($promoCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const invitationModelCatalog = <?php echo json_encode(array_map(static fn(array $row): array => [
    'id' => (string) ($row['cod_mod'] ?? ''),
    'label' => (string) ($row['nom'] ?? ''),
    'image' => '../images/modeleis/' . (string) ($row['image'] ?? ''),
    'unitPrice' => round((float) ($row['unit_price'] ?? 0), 2),
  ], $invitationModelRows), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const initialSelectedInvitationModels = <?php echo json_encode($selectedInvitationModels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

  const eventTypeSelect = document.getElementById('eventType');
  const wizardStepper = document.getElementById('wizardStepper');
  const dots = [...document.querySelectorAll('[data-stepdot]')];
  const step1 = document.getElementById('step1');
  const step2 = document.getElementById('step2');
  const step3 = document.getElementById('step3');
  const step4 = document.getElementById('step4');
  const steps = [step1, step2, step3, step4];

  const weddingTypeGroup = document.getElementById('weddingTypeGroup');
  const invitationLanguageGroup = document.getElementById('InvitationLanguageGroup');
  const accessoireGroup = document.getElementById('AccessoireGroup');
  const nameOrderGroup = document.getElementById('NameOrderGroup');
  const nomEpouxGroup = document.getElementById('NomepouxGroup');
  const prenomEpouxGroup = document.getElementById('PrenomepouxGroup');
  const nomEpouseGroup = document.getElementById('NomepouseGroup');
  const prenomEpouseGroup = document.getElementById('PrenomepouseGroup');

  const weddingType = document.getElementById('weddingType');
  const invitationLang = document.getElementById('invitationLang');
  const nameOrder = document.getElementById('nameOrder');
  const prenomEpoux = document.getElementById('prenomEpoux');
  const prenomEpouse = document.getElementById('prenomEpouse');

  const datepicker = document.getElementById('datepicker');
  const lieu = document.getElementById('lieu');
  const adresse = document.getElementById('adresse');

  const singleStepOthers = document.getElementById('singleStepOthers');
  const nomsAnnivGroup = document.getElementById('NomsAnnivGroup');
  const themeConfGroup = document.getElementById('ThemeConfGroup');
  const nomsfetard = document.getElementById('nomsfetard');
  const themeConf = document.getElementById('themeConf');

  const btnNext1 = document.getElementById('btnNext1');
  const btnNext2 = document.getElementById('btnNext2');
  const btnNext3 = document.getElementById('btnNext3');
  const btnPrev2 = document.getElementById('btnPrev2');
  const btnPrev3 = document.getElementById('btnPrev3');
  const btnPrev4 = document.getElementById('btnPrev4');

  const dropdownToggle = document.getElementById('dropdownToggle');
  const modal = document.getElementById('myModal');
  const closeModal = document.getElementById('closeModal');
  const finishModelSelection = document.getElementById('finishModelSelection');
  const dropdownContent = document.getElementById('weddingTypeDropdown');
  const selectedOption = document.querySelector('.selected-option');
  const selectedModelsList = document.getElementById('selectedModelsList');
  const modeleInvInput = document.getElementById('modeleInv');
  const selectedModelPreview = document.getElementById('selectedModelPreview');
  const selectedModelImage = document.getElementById('selectedModelImage');
  const selectedModelTitle = document.getElementById('selectedModelTitle');
  const selectedModelPrice = document.getElementById('selectedModelPrice');
  const selectedModelMeta = document.getElementById('selectedModelMeta');
  const modelImageLightbox = document.getElementById('modelImageLightbox');
  const modelImageLightboxImg = document.getElementById('modelImageLightboxImg');
  const modelImageLightboxTitle = document.getElementById('modelImageLightboxTitle');
  const modelImageLightboxMeta = document.getElementById('modelImageLightboxMeta');
  const closeModelImageLightbox = document.getElementById('closeModelImageLightbox');

  const checkoutSummaryWedding = document.getElementById('checkoutSummaryWedding');
  const checkoutSummaryOther = document.getElementById('checkoutSummaryOther');
  const promoCodeInput = document.getElementById('promoCode');
  const promoCodeOtherInput = document.getElementById('promoCodeOther');
  const paymentTypeInput = document.getElementById('paymentType');
  const paymentTypeOtherInput = document.getElementById('paymentTypeOther');

  const fileInput = document.getElementById('fileInput');
  const previewContainer = document.getElementById('previewContainer');
  const fileInput2 = document.getElementById('fileInput2');
  const previewContainer2 = document.getElementById('previewContainer2');

  const modelCatalogById = new Map(invitationModelCatalog.map((model) => [String(model.id), model]));
  const selectedInvitationModels = new Map();

  initialSelectedInvitationModels.forEach((model) => {
    const modelId = String(model.model_id || '');
    const catalogItem = modelCatalogById.get(modelId);
    if (!catalogItem) {
      return;
    }

    selectedInvitationModels.set(modelId, {
      id: modelId,
      label: catalogItem.label,
      image: catalogItem.image,
      unitPrice: Number(catalogItem.unitPrice || 0),
      quantity: Math.max(1, Number(model.quantity || 1)),
    });
  });

  function showStep(stepNumber) {
    steps.forEach((step, index) => step.classList.toggle('active', index === stepNumber - 1));
    dots.forEach((dot, index) => dot.classList.toggle('active', index <= stepNumber - 1));
  }

  function toggle(element, shouldShow) {
    if (!element) {
      return;
    }

    element.style.display = shouldShow ? '' : 'none';
  }

  function showWizard(shouldShow) {
    wizardStepper.classList.toggle('hidden', !shouldShow);
    wizardStepper.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');

    if (shouldShow) {
      singleStepOthers.style.display = 'none';
      showStep(1);
      return;
    }

    steps.forEach((step) => step.classList.remove('active'));
  }

  function setSectionEnabled(sectionEl, enabled) {
    if (!sectionEl) {
      return;
    }

    sectionEl.querySelectorAll('input, select, textarea, button').forEach((control) => {
      if (control.type === 'button' && control.closest('.wizard-actions')) {
        return;
      }

      control.disabled = !enabled;
    });
  }

  function resetSelectedModelPreview() {
    selectedModelPreview.style.display = 'none';
    selectedModelImage.src = '';
    selectedModelTitle.textContent = '';
    selectedModelPrice.textContent = '';
    selectedModelMeta.textContent = 'La génération automatique d’une invitation personnalisée pour chaque invité avec son nom inscrit.';
  }

  function formatCatalogPrice(amount) {
    const normalizedAmount = Number(amount || 0);
    return normalizedAmount > 0 ? formatMoney(normalizedAmount) : 'Sur demande';
  }

  function applySelectedModelPreview(label, image, count, priceText) {
    selectedModelImage.src = image || '';
    selectedModelImage.alt = label || 'Aperçu du modèle sélectionné';
    selectedModelTitle.textContent = label || '';
    selectedModelPrice.textContent = priceText || '';
    selectedModelMeta.textContent = count > 1
      ? `${count} modèles sélectionnés pour cette commande.`
      : 'La génération automatique d’une invitation personnalisée pour chaque invité avec son nom inscrit.';
    selectedModelPreview.style.display = 'grid';
  }

  function openModelImageLightbox(image, title, meta) {
    if (!image) {
      return;
    }

    modelImageLightboxImg.src = image;
    modelImageLightboxImg.alt = title || 'Aperçu du modèle';
    modelImageLightboxTitle.textContent = title || 'Aperçu du modèle';
    modelImageLightboxMeta.textContent = meta || '';
    modelImageLightbox.classList.add('is-open');
    modelImageLightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModelPreviewLightbox() {
    modelImageLightbox.classList.remove('is-open');
    modelImageLightbox.setAttribute('aria-hidden', 'true');
    modelImageLightboxImg.src = '';
    modelImageLightboxImg.alt = '';
    document.body.style.overflow = '';
  }

  function formatMoney(amount) {
    return `${Number(amount || 0).toFixed(2)} $`;
  }

  function getSelectedAccessoireCheckboxes() {
    return [...document.querySelectorAll('input[name="accessoires[]"]:checked')];
  }

  function getSelectedAccessoryIds() {
    return getSelectedAccessoireCheckboxes().map((checkbox) => String(checkbox.value));
  }

  function hasInvitationAccessory() {
    const selectedIds = getSelectedAccessoryIds();
    return selectedIds.includes('1') || selectedIds.includes('2');
  }

  function invitationModelsNeedQuantity() {
    return getSelectedAccessoryIds().includes('1');
  }

  function getPrintedInvitationQuantity() {
    let totalQuantity = 0;
    selectedInvitationModels.forEach((model) => {
      totalQuantity += Math.max(1, Number(model.quantity || 1));
    });

    return totalQuantity;
  }

  function getAccessoryQuantity(accessoryId) {
    if (accessoryId === '1') {
      return getPrintedInvitationQuantity();
    }

    const checkbox = document.querySelector(`input[name="accessoires[]"][value="${accessoryId}"]`);
    if (!checkbox || !checkbox.checked) {
      return 0;
    }

    const wrapper = document.getElementById(checkbox.dataset.quantityTarget || '');
    const quantityInput = wrapper ? wrapper.querySelector('input') : null;
    const quantityMode = accessoryCatalog[accessoryId]?.quantity_mode || 'variable';

    if (quantityMode === 'fixed') {
      return 1;
    }

    return Math.max(1, Number(quantityInput?.value || 1));
  }

  function getPromoCodeValue() {
    const activeInput = [promoCodeInput, promoCodeOtherInput].find((input) => input && !input.disabled);
    return (activeInput?.value || '').trim().toUpperCase();
  }

  function getPaymentTypeValue() {
    const activeInput = [paymentTypeInput, paymentTypeOtherInput].find((input) => input && !input.disabled);
    return activeInput?.value || '';
  }

  function buildCheckoutSummary() {
    const selectedIds = getSelectedAccessoryIds();
    const lines = [];
    let subtotal = 0;

    selectedIds.forEach((accessoryId) => {
      const catalogItem = accessoryCatalog[accessoryId] || {label: 'Accessoire', unit_price: 0, quantity_mode: 'variable'};

      if (accessoryId === '1' && selectedInvitationModels.size > 0) {
        selectedInvitationModels.forEach((model) => {
          const quantity = Math.max(1, Number(model.quantity || 1));
          const unitPrice = Number(model.unitPrice || 0);
          const lineTotal = unitPrice * quantity;

          subtotal += lineTotal;
          lines.push({
            id: `${accessoryId}-${model.id}`,
            label: catalogItem.label,
            modelLabel: model.label,
            quantity,
            unitPrice,
            lineTotal,
            modelsCount: selectedInvitationModels.size,
          });
        });

        return;
      }

      const quantity = accessoryId === '1' ? getPrintedInvitationQuantity() : getAccessoryQuantity(accessoryId);
      const normalizedQuantity = catalogItem.quantity_mode === 'fixed' ? 1 : quantity;
      const lineQuantity = accessoryId === '1' && normalizedQuantity === 0 ? 0 : Math.max(1, normalizedQuantity || 0);
      const lineTotal = Number(catalogItem.unit_price || 0) * lineQuantity;

      subtotal += lineTotal;
      lines.push({
        id: accessoryId,
        label: catalogItem.label,
        quantity: lineQuantity,
        unitPrice: Number(catalogItem.unit_price || 0),
        lineTotal,
        modelsCount: accessoryId === '1' ? selectedInvitationModels.size : 0,
      });
    });

    const promoCode = getPromoCodeValue();
    const promoDefinition = promoCatalog[promoCode] || null;
    let discountAmount = 0;
    let promoLabel = '';

    if (promoDefinition && subtotal > 0) {
      promoLabel = promoDefinition.label || promoCode;
      discountAmount = promoDefinition.type === 'fixed'
        ? Math.min(subtotal, Number(promoDefinition.value || 0))
        : Math.min(subtotal, subtotal * (Number(promoDefinition.value || 0) / 100));
    }

    return {
      lines,
      subtotal,
      discountAmount,
      promoCode,
      promoLabel,
      total: Math.max(0, subtotal - discountAmount),
    };
  }

  function renderCheckoutSummary() {
    const summary = buildCheckoutSummary();
    const targets = [checkoutSummaryWedding, checkoutSummaryOther];

    targets.forEach((target) => {
      if (!target) {
        return;
      }

      if (summary.lines.length === 0) {
        target.innerHTML = '<div class="checkout-empty">Sélectionnez au moins un accessoire pour voir le montant total de la commande.</div>';
        return;
      }

      const linesHtml = summary.lines.map((line) => {
        const modelInfo = line.modelLabel
          ? `<small>${line.modelLabel}</small>`
          : line.modelsCount > 0
          ? `<small>${line.modelsCount} modèle(s) d\'invitation sélectionné(s)</small>`
          : '';

        return `
          <div class="checkout-line">
            <div>
              <strong>${line.label}</strong>
              <small>Quantité: ${line.quantity} x ${formatMoney(line.unitPrice)}</small>
              ${modelInfo}
            </div>
            <div>${formatMoney(line.lineTotal)}</div>
          </div>
        `;
      }).join('');

      const promoHtml = summary.discountAmount > 0
        ? `<div class="checkout-line"><div><strong>Réduction promo</strong><small>${summary.promoLabel} (${summary.promoCode})</small></div><div>- ${formatMoney(summary.discountAmount)}</div></div>`
        : summary.promoCode
          ? `<div class="checkout-line"><div><strong>Code promo</strong><small>${summary.promoCode} non reconnu, aucune réduction appliquée.</small></div><div>0.00 $</div></div>`
          : '';

      target.innerHTML = `
        <div class="checkout-summary-card">
          ${linesHtml}
          <div class="checkout-line"><div><strong>Sous-total</strong></div><div>${formatMoney(summary.subtotal)}</div></div>
          ${promoHtml}
        </div>
        <div class="checkout-total">
          <div><span>Total à payer</span></div>
          <strong>${formatMoney(summary.total)}</strong>
        </div>
      `;
    });
  }

  function syncSelectedModelsUI() {
    const selectedModels = [...selectedInvitationModels.values()];
    modeleInvInput.value = selectedModels[0]?.id || '';

    if (selectedModels.length === 0) {
      selectedOption.innerHTML = '<div class="model-picker-summary"><span class="model-picker-label">Choisir un ou plusieurs modèles…</span><span class="model-picker-meta">Ouvrez la galerie pour composer votre sélection d\'invitations.</span></div>';
      selectedModelsList.innerHTML = '';
      dropdownContent.querySelectorAll('div[data-value]').forEach((item) => item.classList.remove('is-selected'));
      resetSelectedModelPreview();
      renderCheckoutSummary();
      return;
    }

    selectedOption.innerHTML = `<div class="model-picker-summary"><span class="model-picker-label">${selectedModels.length} modèle(s) sélectionné(s)</span><span class="model-picker-meta">Vous pouvez continuer à ajouter ou retirer des designs dans la galerie.</span></div>`;
    applySelectedModelPreview(selectedModels[0].label, selectedModels[0].image, selectedModels.length, formatCatalogPrice(selectedModels[0].unitPrice));

    dropdownContent.querySelectorAll('div[data-value]').forEach((item) => {
      item.classList.toggle('is-selected', selectedInvitationModels.has(item.getAttribute('data-value') || ''));
    });

    selectedModelsList.innerHTML = '';
    selectedModels.forEach((model) => {
      const item = document.createElement('div');
      item.className = 'selected-model-item';
      item.innerHTML = `
        <img src="${model.image}" alt="${model.label}">
        <div class="selected-model-item-copy">
          <input type="hidden" name="invitation_models[]" value="${model.id}">
          <span class="selected-model-item-title">${model.label}</span>
          <span class="selected-model-item-price">${formatCatalogPrice(model.unitPrice)}</span>
          <span class="selected-model-item-meta">${invitationModelsNeedQuantity() ? 'Définissez la quantité pour ce modèle imprimé.' : 'Ce modèle sera utilisé pour votre invitation.'}</span>
          <div class="selected-model-item-controls">
            ${invitationModelsNeedQuantity()
              ? `<label>Quantité <input type="number" min="1" step="1" name="invitation_model_quantities[${model.id}]" value="${Math.max(1, Number(model.quantity || 1))}" data-model-quantity="${model.id}"></label>`
              : `<input type="hidden" name="invitation_model_quantities[${model.id}]" value="1">`}
          </div>
        </div>
        <button type="button" class="selected-model-item-remove" data-remove-model="${model.id}">Retirer</button>
      `;
      selectedModelsList.appendChild(item);
    });

    selectedModelsList.querySelectorAll('[data-model-quantity]').forEach((input) => {
      input.addEventListener('input', () => {
        const modelId = input.getAttribute('data-model-quantity') || '';
        const model = selectedInvitationModels.get(modelId);
        if (!model) {
          return;
        }

        model.quantity = Math.max(1, Number(input.value || 1));
        input.value = String(model.quantity);
        renderCheckoutSummary();
      });
    });

    selectedModelsList.querySelectorAll('[data-remove-model]').forEach((button) => {
      button.addEventListener('click', () => {
        const modelId = button.getAttribute('data-remove-model') || '';
        selectedInvitationModels.delete(modelId);
        syncSelectedModelsUI();
      });
    });

    selectedModelsList.querySelectorAll('img').forEach((image) => {
      image.addEventListener('click', () => {
        const card = image.closest('.selected-model-item');
        const title = card?.querySelector('.selected-model-item-title')?.textContent || image.alt || 'Aperçu du modèle';
        const price = card?.querySelector('.selected-model-item-price')?.textContent || '';
        openModelImageLightbox(image.src, title, price);
      });
    });

    renderCheckoutSummary();
  }

  function clearInvitationModels() {
    selectedInvitationModels.clear();
    syncSelectedModelsUI();
  }

  function toggleFields() {
    const selectedIds = getSelectedAccessoryIds();
    const showInvitation = selectedIds.includes('1') || selectedIds.includes('2');
    const showChevalet = selectedIds.includes('3');

    toggle(document.getElementById('ModInvitation'), showInvitation);
    toggle(document.getElementById('ModChevalet'), showChevalet);

    document.querySelectorAll('input[name="accessoires[]"]').forEach((checkbox) => {
      const quantityTarget = checkbox.dataset.quantityTarget;
      const wrapper = quantityTarget ? document.getElementById(quantityTarget) : null;
      const quantityInput = wrapper ? wrapper.querySelector('input') : null;
      const requiresQuantity = checkbox.dataset.requiresQuantity === '1';
      const managedByModelSelection = checkbox.value === '1';

      if (!wrapper || !quantityInput) {
        return;
      }

      const shouldShow = checkbox.checked && requiresQuantity && !managedByModelSelection;
      wrapper.style.display = shouldShow ? 'flex' : 'none';
      quantityInput.disabled = !shouldShow;

      if (shouldShow && (!quantityInput.value || Number(quantityInput.value) < 1)) {
        quantityInput.value = '1';
      }
    });

    if (!showInvitation) {
      clearInvitationModels();
    } else {
      syncSelectedModelsUI();
    }

    renderCheckoutSummary();
  }

  window.toggleFields = toggleFields;

  function applyMode(mode) {
    const enableWedding = mode === 'wedding';

    toggle(invitationLanguageGroup, !!eventTypeSelect.value);

    if (enableWedding) {
      showWizard(true);
      singleStepOthers.style.display = 'none';
      toggle(weddingTypeGroup, true);
      toggle(nameOrderGroup, true);
      toggle(nomEpouxGroup, true);
      toggle(prenomEpouxGroup, true);
      toggle(nomEpouseGroup, true);
      toggle(prenomEpouseGroup, true);
    } else {
      showWizard(false);
      singleStepOthers.style.display = '';
    }

    setSectionEnabled(step1, enableWedding);
    setSectionEnabled(step2, enableWedding);
    setSectionEnabled(step3, enableWedding);
    setSectionEnabled(step4, enableWedding);
    setSectionEnabled(singleStepOthers, !enableWedding);
  }

  function validateInvitationModels() {
    if (!hasInvitationAccessory()) {
      return true;
    }

    if (selectedInvitationModels.size === 0) {
      alert('Sélectionnez au moins un modèle d\'invitation.');
      dropdownToggle?.focus();
      return false;
    }

    if (!invitationModelsNeedQuantity()) {
      return true;
    }

    let invalidQuantity = false;
    selectedInvitationModels.forEach((model) => {
      if (!Number.isFinite(Number(model.quantity)) || Number(model.quantity) < 1) {
        invalidQuantity = true;
      }
    });

    if (invalidQuantity) {
      alert('Renseignez une quantité valide pour chaque modèle imprimé sélectionné.');
      return false;
    }

    return true;
  }

  function bindMirroredFields(first, second) {
    if (!first || !second) {
      return;
    }

    const syncFrom = (source, target) => {
      target.value = source.value;
      renderCheckoutSummary();
    };

    ['input', 'change'].forEach((eventName) => {
      first.addEventListener(eventName, () => syncFrom(first, second));
      second.addEventListener(eventName, () => syncFrom(second, first));
    });
  }

  eventTypeSelect.addEventListener('change', function onTypeChange() {
    const value = this.value;

    if (!value) {
      toggle(accessoireGroup, false);
      toggle(invitationLanguageGroup, false);
      showWizard(false);
      singleStepOthers.style.display = 'none';
      setSectionEnabled(step1, false);
      setSectionEnabled(step2, false);
      setSectionEnabled(step3, false);
      setSectionEnabled(step4, false);
      setSectionEnabled(singleStepOthers, false);
      renderCheckoutSummary();
      return;
    }

    toggle(accessoireGroup, true);
    toggle(invitationLanguageGroup, true);

    if (value === '1') {
      applyMode('wedding');
      toggle(nomsAnnivGroup, false);
      toggle(themeConfGroup, false);
    } else {
      applyMode('single');
      toggle(nomsAnnivGroup, value === '2');
      toggle(themeConfGroup, value === '3');
    }

    toggleFields();
  });

  btnNext1?.addEventListener('click', () => {
    if (!weddingType.value) { alert('Sélectionnez le type de mariage.'); return; }
    if (!invitationLang.value) { alert('Sélectionnez la langue de l\'invitation.'); return; }
    if (!nameOrder.value) { alert('Choisissez l\'ordre des prénoms sur l\'invitation.'); return; }
    if (!prenomEpoux.value.trim()) { alert('Prénom de l\'époux requis.'); return; }
    if (!prenomEpouse.value.trim()) { alert('Prénom de l\'épouse requis.'); return; }
    if (!validateInvitationModels()) { return; }
    showStep(2);
  });

  btnNext2?.addEventListener('click', () => {
    if (!datepicker.value) { alert('Date et heure requises.'); return; }
    if (!lieu.value.trim()) { alert('Lieu requis.'); return; }
    if (!adresse.value.trim()) { alert('Adresse requise.'); return; }
    showStep(3);
  });

  btnNext3?.addEventListener('click', () => {
    if (!validateInvitationModels()) { return; }
    showStep(4);
    renderCheckoutSummary();
  });

  btnPrev2?.addEventListener('click', () => showStep(1));
  btnPrev3?.addEventListener('click', () => showStep(2));
  btnPrev4?.addEventListener('click', () => showStep(3));

  dropdownToggle?.addEventListener('click', () => {
    if (!hasInvitationAccessory()) {
      alert('Sélectionnez d\'abord une invitation imprimée ou électronique.');
      return;
    }

    modal.style.display = 'block';
  });

  closeModal?.addEventListener('click', () => { modal.style.display = 'none'; });
  finishModelSelection?.addEventListener('click', () => { modal.style.display = 'none'; });
  window.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.style.display = 'none';
    }

    if (event.target === modelImageLightbox) {
      closeModelPreviewLightbox();
    }
  });

  dropdownContent?.addEventListener('click', (event) => {
    const previewTrigger = event.target.closest('[data-preview-image]');
    if (previewTrigger) {
      event.stopPropagation();
      openModelImageLightbox(
        previewTrigger.getAttribute('data-preview-image') || '',
        previewTrigger.getAttribute('data-preview-title') || 'Aperçu du modèle',
        previewTrigger.getAttribute('data-preview-price') || ''
      );
      return;
    }

    const cell = event.target.closest('div[data-value]');
    if (!cell) {
      return;
    }

    const modelId = cell.getAttribute('data-value') || '';
    const label = cell.getAttribute('data-label') || 'Modèle sélectionné';
    const image = cell.getAttribute('data-image') || '';
    const priceLabel = cell.getAttribute('data-price') || '';

    if (selectedInvitationModels.has(modelId)) {
      selectedInvitationModels.delete(modelId);
    } else {
      selectedInvitationModels.set(modelId, {
        id: modelId,
        label,
        image,
        unitPrice: Number(modelCatalogById.get(modelId)?.unitPrice || 0),
        quantity: 1,
      });
    }

    syncSelectedModelsUI();
  });

  selectedModelImage?.addEventListener('click', () => {
    if (!selectedModelImage.src) {
      return;
    }

    openModelImageLightbox(selectedModelImage.src, selectedModelTitle.textContent || 'Aperçu du modèle', selectedModelPrice.textContent || '');
  });

  closeModelImageLightbox?.addEventListener('click', closeModelPreviewLightbox);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modelImageLightbox.classList.contains('is-open')) {
      closeModelPreviewLightbox();
    }
  });

  function attachImagePreview(input, container) {
    input?.addEventListener('change', (event) => {
      const files = Array.from(event.target.files || []);
      container.innerHTML = '';
      files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (loadEvent) => {
          const wrapper = document.createElement('div');
          wrapper.className = 'image-container';
          const image = document.createElement('img');
          image.src = String(loadEvent.target?.result || '');
          const removeButton = document.createElement('button');
          removeButton.innerHTML = '✖';
          removeButton.className = 'delete-icon';
          removeButton.addEventListener('click', () => wrapper.remove());
          wrapper.appendChild(image);
          wrapper.appendChild(removeButton);
          container.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
      });
    });
  }

  attachImagePreview(fileInput, previewContainer);
  attachImagePreview(fileInput2, previewContainer2);

  document.addEventListener('DOMContentLoaded', () => {
    const submitButton = document.getElementById('BtnEvent');
    const checkbox = document.getElementById('basic_checkbox_1');
    if (submitButton && checkbox) {
      submitButton.disabled = true;
      checkbox.addEventListener('change', () => {
        submitButton.disabled = !checkbox.checked;
      });
    }

    const submitButtonOther = document.getElementById('BtnEvent_o');
    const checkboxOther = document.getElementById('basic_checkbox_1_o');
    if (submitButtonOther && checkboxOther) {
      submitButtonOther.disabled = true;
      checkboxOther.addEventListener('change', () => {
        submitButtonOther.disabled = !checkboxOther.checked;
      });
    }
  });

  bindMirroredFields(promoCodeInput, promoCodeOtherInput);
  bindMirroredFields(paymentTypeInput, paymentTypeOtherInput);

  [promoCodeInput, promoCodeOtherInput].forEach((input) => input?.addEventListener('input', renderCheckoutSummary));
  [paymentTypeInput, paymentTypeOtherInput].forEach((input) => input?.addEventListener('change', renderCheckoutSummary));
  document.querySelectorAll('input[name^="accessoire_quantities["]').forEach((input) => {
    input.addEventListener('input', renderCheckoutSummary);
  });

  document.getElementById('eventForm').addEventListener('submit', function onSubmit(event) {
    event.preventDefault();

    const selectedAccessoires = getSelectedAccessoireCheckboxes();
    if (selectedAccessoires.length === 0) {
      alert('Sélectionnez au moins un accessoire pour votre commande.');
      return;
    }

    if (!invitationLang.value) {
      alert('Sélectionnez la langue du texte sur l\'invitation.');
      return;
    }

    if (!validateInvitationModels()) {
      return;
    }

    for (const accessoire of selectedAccessoires) {
      if (accessoire.dataset.requiresQuantity !== '1' || accessoire.value === '1') {
        continue;
      }

      const quantityWrapper = document.getElementById(accessoire.dataset.quantityTarget || '');
      const quantityInput = quantityWrapper ? quantityWrapper.querySelector('input') : null;
      const quantityValue = quantityInput ? Number(quantityInput.value) : 0;

      if (!quantityInput || !Number.isFinite(quantityValue) || quantityValue < 1) {
        alert('Veuillez renseigner une quantité valide pour chaque accessoire sélectionné.');
        quantityInput?.focus();
        return;
      }
    }

    const typeEvent = eventTypeSelect.value;
    if (!typeEvent) { alert('Veuillez sélectionner le type de l\'événement.'); return; }
    if (!getPaymentTypeValue()) { alert('Choisissez un type de paiement.'); return; }

    if (typeEvent === '1') {
      if (!weddingType.value) { alert('Type de mariage requis.'); return; }
      if (!nameOrder.value) { alert('Choisissez l\'ordre des prénoms sur l\'invitation.'); return; }
      if (!prenomEpoux.value.trim()) { alert('Prénom de l\'époux requis.'); return; }
      if (!prenomEpouse.value.trim()) { alert('Prénom de l\'épouse requis.'); return; }
      if (!datepicker.value) { alert('Date/heure requises.'); return; }
      if (!lieu.value.trim()) { alert('Lieu requis.'); return; }
      if (!adresse.value.trim()) { alert('Adresse requise.'); return; }
      if (!document.getElementById('basic_checkbox_1').checked) { alert('Veuillez accepter les termes.'); return; }
    } else {
      const date2 = document.getElementById('datepicker2').value;
      const lieu2 = document.getElementById('lieu2').value;
      const adresse2 = document.getElementById('adresse2').value;

      if (typeEvent === '2' && !nomsfetard.value.trim()) { alert('Nom du/de la fêté(e) requis.'); return; }
      if (typeEvent === '3' && !themeConf.value.trim()) { alert('Thème de la conférence requis.'); return; }
      if (!date2) { alert('Date/heure requises.'); return; }
      if (!lieu2.trim()) { alert('Lieu requis.'); return; }
      if (!adresse2.trim()) { alert('Adresse requise.'); return; }
      if (!document.getElementById('basic_checkbox_1_o').checked) { alert('Veuillez accepter les termes.'); return; }
    }

    this.style.display = 'none';
    const progressWrapper = document.getElementById('progressWrapper');
    progressWrapper.style.display = 'flex';
    progressWrapper.classList.add('centered');
    document.getElementById('progressContainer').style.display = 'block';
    document.getElementById('progressBar').style.width = '0%';

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.upload.onprogress = function onProgress(progressEvent) {
      if (progressEvent.lengthComputable) {
        const percent = (progressEvent.loaded / progressEvent.total) * 100;
        document.getElementById('progressBar').style.width = `${percent}%`;
        document.getElementById('progressPercentage').textContent = `Téléchargement des photos : ${Math.round(percent)}%`;
      }
    };

    xhr.onload = function onLoad() {
      let payload = null;
      try {
        payload = JSON.parse(xhr.responseText);
      } catch (error) {
        payload = null;
      }

      if (xhr.status === 200 && payload?.success) {
        Swal.fire({
          title: 'Evénement créé !',
          text: 'Votre événement est ajouté avec succès.',
          icon: 'success',
          confirmButtonText: 'Terminer'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = 'index.php?page=mb_accueil';
          }
        });
        return;
      }

      const message = payload?.message || 'Erreur lors du traitement.';
      document.getElementById('status').innerHTML = message;
      Swal.fire({
        title: 'Enregistrement impossible',
        text: message,
        icon: 'error',
        confirmButtonText: 'Fermer'
      });
      document.getElementById('eventForm').style.display = '';
      document.getElementById('progressWrapper').style.display = 'none';
    };

    xhr.send(formData);
  });

  (function init() {
    toggle(accessoireGroup, false);
    toggle(invitationLanguageGroup, false);
    showWizard(false);
    singleStepOthers.style.display = 'none';
    toggleFields();
    renderCheckoutSummary();

    if (eventTypeSelect.value) {
      eventTypeSelect.dispatchEvent(new Event('change'));
    }
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
