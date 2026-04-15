<?php

// =====================
//  CONDITIONS & PAGINATION
// =====================
$eventListing = EventBackofficeService::findPaginatedEvents($pdo, $_GET, $datasession ?? [], 50);
$events = $eventListing['events'];
$page_i = $eventListing['page'];
$pages = $eventListing['pages'];

// =====================
//  FORMATTEUR DE DATE (une seule fois)
// =====================
$fmt = new IntlDateFormatter(
    'fr_FR',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    null,
    IntlDateFormatter::GREGORIAN,
    'EEEE, dd/MM/yyyy à HH:mm'
);

// =====================
//  CSS MINIMAL + LIGHTBOX
// =====================
?>
<style>
.table-events { width:100%; border-collapse:separate; border-spacing:0 18px; }
.table-events td { vertical-align: top; border:0; background:transparent; }

.event-row { position:relative; }
.event-card-cell { padding:0 !important; }
.event-card-shell {
  background:linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  border:1px solid #e2e8f0;
  border-radius:28px;
  padding:24px;
  box-shadow:0 20px 40px rgba(15, 23, 42, 0.08);
}
.event-card-shell.event-card-overdue {
  background:linear-gradient(180deg, #fff7f7 0%, #fff1f2 100%);
  border-color:#fecaca;
  box-shadow:0 20px 40px rgba(239, 68, 68, 0.10);
}
.event-card-topbar {
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
  margin-bottom:16px;
}
.event-card-flags { display:flex; flex-wrap:wrap; gap:10px; }

.badge-flag,
.badge-partial,
.badge-unpaid,
.badge-livr {
  position:static;
  top:auto;
  left:auto;
  display:inline-flex;
  align-items:center;
  padding:7px 14px;
  border-radius:999px;
  font-size:12px;
  font-weight:700;
  line-height:1;
  box-shadow:0 10px 20px rgba(15, 23, 42, 0.10);
}
.badge-new{ background:#FF5733; color:#fff; }
.badge-done{ background:rgb(0,129,97); color:#fff; }
.badge-progress{ background:rgb(15,99,233); color:#fff; }
.badge-partial{ background:#d98a00; color:#fff; }
.badge-overdue{ background:#dc2626; color:#fff; }
.badge-unpaid{ background:#6b7280; color:#fff; }
.badge-livr{ background:#fff3e8; color:rgb(149,27,0); }

.event-body-grid { margin:0; row-gap:18px; }
.event-main-column,
.event-side-column { padding-left:0; padding-right:0; }

.event-card-title {
  display:inline-flex;
  align-items:center;
  gap:8px;
  margin-bottom:8px;
  color:#0f172a !important;
  font-size:20px;
  font-weight:800;
  text-decoration:none;
}
.event-card-title .text-fade { color:#475569 !important; }
.event-card-date {
  display:block;
  color:#64748b;
  font-size:13px;
  margin-bottom:16px;
}

.event-accessories {
}
.event-accessories br { display:none; }
.event-accessories > em {
  display:flex;
  align-items:center;
  gap:8px;
  margin-top:6px;
  color:#334155;
  font-style:normal;
}
.event-accessories > em::before {
  content:'';
  flex:0 0 auto;
  width:8px;
  height:8px;
  border-radius:999px;
  background:linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
  box-shadow:0 0 0 4px rgba(245, 158, 11, 0.14);
}
.event-accessories > em:first-child { margin-top:0; }

.event-detail-stack {
  margin-top:10px;
  display:grid;
  gap:3px;
}
.event-detail-stack br { display:none; }
.event-detail-stack span,
.event-detail-stack em {
  display:block;
  margin:0;
  line-height:1.25;
  color:#475569;
  font-style:normal;
}
.event-detail-stack b {
  color:#0f172a;
  font-weight:700;
}
.event-detail-stack a {
  color:#1d4ed8;
  text-decoration:none;
}

.event-payment-stack {
  margin-top:10px;
  width:96%;
  padding:14px 16px;
  border-radius:18px;
  background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  box-shadow:0 16px 28px rgba(15, 23, 42, 0.18);
}
.event-payment-stack span {
  display:block;
  margin-top:3px;
  line-height:1.25;
  color:#e2e8f0;
}
.event-payment-stack span:first-child { margin-top:0; }
.event-payment-stack b { color:#94a3b8; font-weight:600; }
.event-payment-stack a { color:#fff; text-decoration:none; }

.event-media-panel {
  height:100%;
  padding:18px;
  border-radius:22px;
  border:1px solid #e2e8f0;
  background:linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}
.event-media-strip {
  display:flex;
  flex-wrap:wrap;
  gap:12px;
}

.square-img, .square-img-qr {
  width:100px;
  height:100px;
  object-fit:cover;
  float:none;
  margin:0;
  border-radius:18px;
  box-shadow:0 12px 22px rgba(15, 23, 42, 0.12);
  background:#fff;
}
.square-img { cursor:pointer; }
.square-img-qr{
  border:1px solid #dbe4f0 !important;
  padding:6px;
}

.event-report-card {
  margin-top:10px;
  padding:16px 18px;
  border-radius:18px;
  background:linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
  border:1px solid #dbeafe;
}
.event-report-card em {
  display:block;
  color:#1d4ed8;
  font-style:normal;
  font-weight:700;
  margin-bottom:4px;
}
.event-report-card p {
  margin:0 0 3px;
  line-height:1.22;
  color:#334155;
}
.event-report-card p:last-child { margin-bottom:0; }
.event-report-files {
  display:flex;
  flex-wrap:wrap;
  gap:10px;
}
.event-report-files a {
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  background:#fff;
  border:1px solid #cbd5e1;
  color:#0f172a;
  text-decoration:none;
}

.hoverx-container{ position:relative; display:inline-flex; min-width:420px; max-width:100%; white-space:nowrap }
.hoverx-container .modelx-link{ display:inline-flex; align-items:center; white-space:nowrap }
.hoverx-image{ display:none; position:absolute; z-index:9000; max-width:320px; border:1px solid #ccc; background:#fff; padding:5px; box-shadow:0 4px 10px rgba(0,0,0,.3) }
.hoverx-container:hover .hoverx-image{ display:block }

.admin-actions-toggle{ width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; border:1px solid #f5d08a; background:linear-gradient(180deg,#fff7e8 0%,#ffe7b8 100%); color:#8a5200 !important; box-shadow:0 10px 24px rgba(138,82,0,.16); transition:transform .18s ease, box-shadow .18s ease, background .18s ease }
.admin-actions-toggle:hover{ transform:translateY(-1px); box-shadow:0 14px 28px rgba(138,82,0,.22); background:linear-gradient(180deg,#fffaf0 0%,#ffdd9c 100%) }
.admin-actions-toggle i{ font-size:18px !important }
.event-actions-menu{ min-width:240px; padding:10px; border:0; border-radius:18px; background:#fff; box-shadow:0 20px 45px rgba(15,23,42,.16) }
.event-actions-menu .dropdown-divider{ margin:8px 0; border-color:#edf2f7 }
.event-actions-menu .action-item{ display:flex; align-items:center; gap:10px; border-radius:12px; padding:10px 12px; font-weight:600; color:#1f2937; transition:background .16s ease, transform .16s ease, color .16s ease }
.event-actions-menu .action-item:hover{ transform:translateX(2px); background:#f8fafc; color:#111827 }
.event-actions-menu .action-item i{ width:18px; text-align:center }
.event-actions-menu .action-money{ color:#0f766e }
.event-actions-menu .action-doc{ color:#1d4ed8 }
.event-actions-menu .action-edit{ color:#7c3aed }
.event-actions-menu .action-client{ color:#0f172a }
.event-actions-menu .action-danger{ color:#b91c1c }
.event-actions-menu .action-state{ color:#0f766e }
.event-actions-menu .action-progress{ color:#1d4ed8 }

.event-actions-wrap {
  display:flex;
  justify-content:flex-end;
  flex:0 0 auto;
}

.pagination { gap:8px; flex-wrap:wrap; }
.pagination .page-link {
  border-radius:12px;
  border:1px solid #e2e8f0;
  color:#0f172a;
  padding:8px 12px;
  box-shadow:0 8px 16px rgba(15, 23, 42, 0.05);
}
.pagination .page-item.active .page-link {
  background:#0f172a;
  border-color:#0f172a;
  color:#fff;
}

@media (max-width: 991px) {
  .event-card-shell { padding:18px; border-radius:22px; }
  .event-card-topbar { flex-direction:column; align-items:stretch; }
  .event-actions-wrap { justify-content:flex-start; }
  .hoverx-container { min-width:100%; }
}

/* Lightbox (popup) */
#imgLightboxBackdrop{
  display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:10500;
}
#imgLightbox{
  position:fixed; inset:auto 5% 5% 5%; top:5%; z-index:10501; display:none;
  background:#111; border-radius:12px; padding:12px; max-height:90vh; overflow:auto; text-align:center;
}
#imgLightbox img{ max-width:100%; height:auto }
#imgLightbox .closeBtn{
  position:absolute; top:8px; right:12px; font-size:26px; color:#fff; cursor:pointer; line-height:1;
}
</style>

<!-- Lightbox HTML -->
<div id="imgLightboxBackdrop" onclick="closePhoto()"></div>
<div id="imgLightbox" role="dialog" aria-modal="true" aria-label="Agrandir la photo">
  <span class="closeBtn" onclick="closePhoto()" title="Fermer">&times;</span>
  <img id="imgLightboxImg" src="" alt="">
  <div id="imgLightboxCaption" style="color:#fff; margin-top:8px; font-size:14px;"></div>
</div>

<script>
function openPhoto(src, altText){
  const bg = document.getElementById('imgLightboxBackdrop');
  const box = document.getElementById('imgLightbox');
  const img = document.getElementById('imgLightboxImg');
  const cap = document.getElementById('imgLightboxCaption');
  img.src = src;
  img.alt = altText || '';
  cap.textContent = altText || '';
  bg.style.display = 'block';
  box.style.display = 'block';
  document.body.style.overflow = 'hidden';
}
function closePhoto(){
  document.getElementById('imgLightboxBackdrop').style.display='none';
  document.getElementById('imgLightbox').style.display='none';
  document.getElementById('imgLightboxImg').src='';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closePhoto(); });
</script>

<table class="table table-events" style="width:100%">
  <tbody>
<?php
if (!empty($events)) {
  foreach ($events as $dataevent) {

  $eventDisplay = EventBackofficeService::decorateEvent(
    $pdo,
    $dataevent,
    $fmt,
    $GLOBALS['isAppConfig'] ?? ['base_url' => 'https://invitationspeciale.com']
  );

  $client = $eventDisplay['client'];
  $phone = $eventDisplay['phone'];
  $paye = $eventDisplay['paye'];
  $reste = $eventDisplay['reste'];
  $badge = $eventDisplay['badge'];
  $icon = $eventDisplay['icon'];
  $color2 = $eventDisplay['color2'];
  $badgepaie = $eventDisplay['badgepaie'];
  $typeevent = $eventDisplay['typeevent'];
  $fetard = $eventDisplay['fetard'];
  $displayvue = $eventDisplay['displayvue'];
  $formatted_date = $eventDisplay['formatted_date'];
  $publicUrl = $eventDisplay['publicUrl'];
  $qrFile = $eventDisplay['qrFile'];
  $paymentMeta = $eventDisplay['payment'];
  $deliveryTimestamp = !empty($dataevent['date_livraison']) ? strtotime((string) $dataevent['date_livraison']) : false;
  $isOverdue = $deliveryTimestamp !== false
    && ($dataevent['crea'] ?? null) !== '2'
    && $deliveryTimestamp < strtotime(date('Y-m-d'));
  $overdueBadge = $isOverdue
    ? '<span class="badge-flag badge-overdue">En retard</span>'
    : '';









    // ----------- Affichage ligne -----------
    ?>
    <tr class="event-row">
      <td class="event-card-cell">
        <div class="event-card-shell<?php echo $isOverdue ? ' event-card-overdue' : ''; ?>">
        <div class="event-card-topbar">
          <div class="event-card-flags"><?= $badge . ' ' . $badgepaie . ' ' . $overdueBadge ?></div>
          <div class="event-actions-wrap">
            <div class="list-icons d-inline-flex">
              <div class="list-icons-item dropdown">
                <a href="#" class="admin-actions-toggle list-icons-item dropdown-toggle" data-bs-toggle="dropdown">
                  <i class="fas fa-ellipsis-h" style="font-size:20px;"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end event-actions-menu">
                  <?php if (!empty($datasession['type_user']) && $datasession['type_user'] === '1') { ?>
                    <?php if (!$paymentMeta['has_payment']) { ?>
                      <a class="dropdown-item action-item action-doc" href="index.php?page=paiement&cod=<?= htmlspecialchars($dataevent['cod_event']) ?>&mode=devis"><i class="fa fa-file-text"></i> Devis</a>
                    <?php } ?>
                    <?php if (!$paymentMeta['is_fully_paid']) { ?>
                      <a class="dropdown-item action-item action-money" href="<?= htmlspecialchars($paymentMeta['payment_action_url']) ?>"><i class="fa fa-dollar-sign"></i> <?= htmlspecialchars($paymentMeta['payment_action_label']) ?></a>
                    <?php } ?>
                    <?php if ($paymentMeta['invoice_pdf_url'] !== null) { ?>
                      <a href="<?= htmlspecialchars($paymentMeta['invoice_pdf_url']) ?>" target="_blank" class="dropdown-item action-item action-doc"><i class="fa fa-print"></i> Facture</a>
                    <?php } ?>
                    <div class="dropdown-divider"></div>
                    <a href="index.php?page=modevent&cod=<?= htmlspecialchars($dataevent['cod_event']) ?>" class="dropdown-item action-item action-edit"><i class="fa fa-pencil"></i> Modifier</a>
                    <a
                      onclick="openModal2(this); return false;"
                      class="dropdown-item action-item action-doc"
                      href="#"
                      data-cod-event="<?= htmlspecialchars((string) $dataevent['cod_event'], ENT_QUOTES, 'UTF-8') ?>"
                      data-invit-religieux="<?= htmlspecialchars((string) ($dataevent['invit_religieux'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-ajustenom="<?= htmlspecialchars((string) ($dataevent['ajustenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-taillenominv="<?= htmlspecialchars((string) ($dataevent['taillenominv'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-alignnominv="<?= htmlspecialchars((string) ($dataevent['alignnominv'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-pagenom="<?= htmlspecialchars((string) ($dataevent['pagenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-pagebouton="<?= htmlspecialchars((string) ($dataevent['pagebouton'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-colornom="<?= htmlspecialchars((string) ($dataevent['colornom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-bordgauchenominv="<?= htmlspecialchars((string) ($dataevent['bordgauchenominv'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-qrcode="<?= htmlspecialchars((string) ($dataevent['qrcode'] ?? 'non'), ENT_QUOTES, 'UTF-8') ?>"
                      data-pageqr="<?= htmlspecialchars((string) ($dataevent['pageqr'] ?? '3'), ENT_QUOTES, 'UTF-8') ?>"
                      data-hautqr="<?= htmlspecialchars((string) ($dataevent['hautqr'] ?? '18'), ENT_QUOTES, 'UTF-8') ?>"
                      data-gaucheqr="<?= htmlspecialchars((string) ($dataevent['gaucheqr'] ?? '52'), ENT_QUOTES, 'UTF-8') ?>"
                      data-tailleqr="<?= htmlspecialchars((string) ($dataevent['tailleqr'] ?? '90'), ENT_QUOTES, 'UTF-8') ?>"
                      data-lang="<?= htmlspecialchars((string) ($dataevent['lang'] ?? 'fr'), ENT_QUOTES, 'UTF-8') ?>"
                    ><i class="fa fa-file"></i> Inv électronique</a>
                    <a
                      onclick="openClientModal(this); return false;"
                      class="dropdown-item action-item action-client"
                      href="#"
                      data-client-code="<?= htmlspecialchars((string) ($dataevent['client_code'] ?? $dataevent['cod_user'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-client-type="<?= htmlspecialchars((string) ($dataevent['client_type_user'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-client-name="<?= htmlspecialchars((string) ($dataevent['client_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-client-phone="<?= htmlspecialchars((string) ($dataevent['client_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-client-email="<?= htmlspecialchars((string) ($dataevent['client_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      data-client-password="<?= htmlspecialchars((string) ($dataevent['client_recpass'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    ><i class="fa fa-user"></i> Clients</a>
                    <a href="#" title="Suppression" onclick="confirmSuppEvent(event,'<?= htmlspecialchars($typeevent) ?>','<?= htmlspecialchars($fetard) ?>','<?= htmlspecialchars($dataevent['cod_event']) ?>')" class="dropdown-item action-item action-danger">
                      <i class="fa fa-remove"></i> Supprimer
                    </a>
                  <?php } ?>
                  <a onclick="openModal('<?= htmlspecialchars($dataevent['cod_event']) ?>','<?= htmlspecialchars($dataevent['cod_event']) ?>')" class="dropdown-item action-item action-state" href="#"><i class="fa fa-check"></i> Terminer</a>
                  <a href="#" title="Amorcer" onclick="amorcerEvent(event,'<?= htmlspecialchars($typeevent) ?>','<?= htmlspecialchars($fetard) ?>','<?= htmlspecialchars($dataevent['cod_event']) ?>')" class="dropdown-item action-item action-progress">
                    <i class="fa fa-spinner fa-spin"></i> En cours
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row event-body-grid">
          <div class="col-md-6 col-12 event-main-column">
            <a class="event-card-title" style="<?= $color2 ?>" href="#">
              (<?= htmlspecialchars($dataevent['cod_event']) ?>) 
              <?= htmlspecialchars(ucfirst($typeevent)) ?>,
              <span class="text-fade" style="<?= $color2 ?>"><?= htmlspecialchars($fetard) ?></span> <?= $icon ?>
            </a>
            <span class="event-card-date"><?= htmlspecialchars($formatted_date) ?></span>

            <?php
            // ---------- Accessoires (avec image de modèle en hover CSS) ----------
            $stmtae = $pdo->prepare("SELECT * FROM accessoires_event WHERE cod_event = ? ORDER BY cod_accev DESC");
            $stmtae->execute([$dataevent['cod_event']]);
            $accessoiresEvent = $stmtae->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($accessoiresEvent)) {
              echo '<div class="event-accessories">';
            }
            foreach ($accessoiresEvent as $dataae) {
                $stmtMod = $pdo->prepare("SELECT nom, image FROM modele_is WHERE cod_mod = ?");
                $stmtMod->execute([$dataae['cod_acc']]);
                $data_accessoire = $stmtMod->fetch(PDO::FETCH_ASSOC);
                $accessoire = $data_accessoire['nom'] ?? '';
                $image_inv  = $data_accessoire['image'] ?? '';
              $accessoireNomLower = function_exists('mb_strtolower') ? mb_strtolower($accessoire, 'UTF-8') : strtolower($accessoire);
              $accessoireNomNormalized = str_replace(['é', 'è', 'ê', 'ë'], 'e', $accessoireNomLower);
              $isInvitationElectronique = strpos($accessoireNomNormalized, 'invitation') !== false && strpos($accessoireNomNormalized, 'elect') !== false;

              $stmtdetailfact = $pdo->prepare("SELECT qtecom FROM details_fact WHERE cod_event = ? AND libelle = ? ORDER BY cod_df DESC LIMIT 1");
                $stmtdetailfact->execute([$dataae['cod_event'], $accessoire]);
                $qteRow = $stmtdetailfact->fetch(PDO::FETCH_ASSOC);
              $quantiteCommande = $qteRow
                ? (int) $qteRow['qtecom']
                : (isset($dataae['quantite']) ? (int) $dataae['quantite'] : 1);
              $qtecom = $isInvitationElectronique
                ? ' <em>(Illimité)</em>'
                : ' <em>(' . max(1, $quantiteCommande) . ')</em>';

                // Si l’accessoire correspond à une maquette d’invitation/chevalet, récupérer le nom du modèle choisi
                $modele_inv = '';
                if ($dataae['cod_acc'] == "1") {
                    $stmtmi = $pdo->prepare("SELECT nom, image FROM modele_is WHERE cod_mod = ?");
                    $stmtmi->execute([$dataevent['modele_inv']]);
                    if ($mi = $stmtmi->fetch(PDO::FETCH_ASSOC)) {
                        $modele_inv = '(' . $mi['nom'] . ')';
                        // si tu veux afficher l’image correspondante, remplace $image_inv
                        $image_inv = $mi['image'] ?? $image_inv;
                    }
                } elseif ($dataae['cod_acc'] == "3") {
                    $stmtmc = $pdo->prepare("SELECT nom FROM modele_is WHERE cod_mod = ?");
                    $stmtmc->execute([$dataevent['modele_chev']]);
                    if ($mc = $stmtmc->fetch(PDO::FETCH_ASSOC)) {
                        $modele_inv = '(' . $mc['nom'] . ')';
                    }
                }
                ?>
                <em>
                  <?= htmlspecialchars($accessoire) ?>
                  <span class="hoverx-container">
                    <a target="_blank" href="<?= 'https://invitationspeciale.com/event/images/modeleis/' . rawurlencode($image_inv) ?>" class="modelx-link">
                      <?= htmlspecialchars($modele_inv) ?><?= $qtecom ?>
                    </a>
                    <?php if (!empty($image_inv)): ?>
                      <img class="hoverx-image" src="<?= '../images/modeleis/' . htmlspecialchars($image_inv) ?>" alt="">
                    <?php endif; ?>
                  </span>
                </em>
                <?php
            }
            if (!empty($accessoiresEvent)) {
              echo '</div>';
            }
            ?>

            <div class="event-detail-stack">
            <?php if ($dataevent['type_event'] == "1") { ?>
              <span><b>Epoux :</b> <?= htmlspecialchars(($dataevent['prenom_epoux']??'').' '.($dataevent['nom_epoux']??'')) ?></span>
              <span><b>Epouse :</b> <?= htmlspecialchars(($dataevent['prenom_epouse']??'').' '.($dataevent['nom_epouse']??'')) ?></span>
            <?php } elseif ($dataevent['type_event'] == "3") { ?>
              <span><b>Thème :</b> <?= htmlspecialchars($dataevent['themeconf'] ?? '') ?></span>
            <?php } ?>

            <span><b>Autres précisions :</b> <?= nl2br(htmlspecialchars($dataevent['autres_precisions'] ?? '')) ?></span>
            <span><b>Lieu :</b> <?= htmlspecialchars($dataevent['lieu'] ?? '') ?></span>
            <span><b>Adresse :</b> <?= isset($dataevent['adresse']) ? '(' . htmlspecialchars($dataevent['adresse']) . ')' : 'Non défini' ?></span>
            <span><b>Site web :</b> <a target="_blank" href="<?= htmlspecialchars($publicUrl) ?>"><?= htmlspecialchars($publicUrl) ?></a></span>
            <span><b>Enregistré le</b> <?= date('d/m/Y', strtotime($dataevent['date_enreg'])) ?></span>
            </div>

            <?php if (!empty($datasession['type_user']) && $datasession['type_user'] === "1") { ?>
              <div class="event-payment-stack">
                <span><b>Whatsapp client :</b> <a href="http://wa.me/<?= htmlspecialchars($phone) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($client) ?></a></span>
                <span><b>Payé :</b> <?= $paye ?></span>
                <span><b>Reste :</b> <?= $reste ?></span>
              </div>
            <?php } ?>
          </div>

          <div class="col-md-6 col-12 event-side-column">
            <div class="event-media-panel">
              <div class="event-media-strip">
                <!-- QR -->
                <img src="<?= htmlspecialchars($qrFile) ?>" alt="QR Code" class="square-img-qr" width="100" height="100" loading="lazy">

                <?php
                // PHOTOS EVENT — on affiche des vignettes cliquables (lightbox)
                $stmtimg = $pdo->prepare("SELECT nom_photo FROM photos_event WHERE cod_event = ? ORDER BY cod_photo DESC");
                $stmtimg->execute([$dataevent['cod_event']]);
                while ($ph = $stmtimg->fetch(PDO::FETCH_ASSOC)) {
                    $origRel = '../photosevent/' . $ph['nom_photo']; // original pour affichage en grand
                    $thumb   = EventThumbnailService::photoThumbPath($ph['nom_photo'], 100, 100) ?? $origRel; // fallback original
                    $alt     = $ph['nom_photo'];
                    ?> 
                    <a href="#" onclick="IS_openPhoto('<?= htmlspecialchars($origRel) ?>','<?= htmlspecialchars($alt) ?>'); return false;" title="Agrandir">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($alt) ?>" class="square-img" width="100" height="100" loading="lazy">
                    </a>
                    <?php
                }
                ?>
              </div>

                <?php
                // Rapport si terminé
                if ($dataevent['crea'] == '2') {
                    $stmtrap = $pdo->prepare("SELECT observation, cod_user FROM creaevent WHERE cod_event = ?");
                    $stmtrap->execute([$dataevent['cod_event']]);
                    if ($datarapport = $stmtrap->fetch(PDO::FETCH_ASSOC)) {
                        $obser = $datarapport['observation'] ?? '';
                        $stmtrecus = $pdo->prepare("SELECT noms FROM is_users WHERE cod_user = ?");
                        $stmtrecus->execute([$datarapport['cod_user']]);
                        $realNom = $stmtrecus->fetchColumn();
                        echo '<div class="event-report-card">';
                        echo '<em>Réalisé par ' . htmlspecialchars($realNom) . '</em>';
                        echo '<p>' . nl2br(htmlspecialchars($obser)) . '</p>';

                        // Fichiers impression
                        echo '<div class="event-report-files">';
                        foreach (EventPrintService::listFilesByEvent($pdo, (int) $dataevent['cod_event']) as $datafile) {
                            $f = $datafile['nom_fichier'];
                            echo '<a href="../pages/fichiersprint/' . htmlspecialchars($f) . '" download>' . htmlspecialchars($f) . "</a>";
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
          </div>
        </div>
        </div>
      </td>

        <script>
        function confirmSuppEvent(event, typeEvent, fetard, codeEvent) {
          event.preventDefault();
          Swal.fire({
            title: "Supprimer !",
            text: "Êtes-vous sûr de vouloir supprimer (" + typeEvent + " de " + fetard + ", code: " + codeEvent + ") ?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Oui",
            cancelButtonText: "Non"
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = "index.php?page=supevent&cod=" + codeEvent;
            }
          });
        }
        function amorcerEvent(event, typeEvent, fetard, codeEvent) {
          event.preventDefault();
          Swal.fire({
            title: "Amorcer !",
            text: typeEvent + " de " + fetard + ", code: " + codeEvent,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Amorcer",
            cancelButtonText: "Annuler"
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = "index.php?page=modstatut&cod=" + codeEvent;
            }
          });
        }

        function escapeClientModalHtml(value) {
          return String(value || '').replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
          });
        }

        function openClientModal(trigger) {
          const dataset = trigger && trigger.dataset ? trigger.dataset : {};
          const typeLabels = { '1': 'Administrateur', '2': 'Client', '3': 'Créateur' };
          const rows = [
            ['Code client', dataset.clientCode || '-'],
            ['Nom', dataset.clientName || '-'],
            ['Téléphone', dataset.clientPhone || '-'],
            ['Email', dataset.clientEmail || '-'],
            ['Type de compte', typeLabels[dataset.clientType] || dataset.clientType || '-'],
            ['Mot de passe', dataset.clientPassword || 'Non renseigné'],
          ].map(function (item) {
            return '<tr>' +
              '<th style="width:38%; background:#f8fafc; color:#334155; padding:10px 12px; border:1px solid #e2e8f0;">' + escapeClientModalHtml(item[0]) + '</th>' +
              '<td style="padding:10px 12px; border:1px solid #e2e8f0; color:#0f172a;">' + escapeClientModalHtml(item[1]) + '</td>' +
            '</tr>';
          }).join('');

          Swal.fire({
            title: 'Informations client',
            html: '<div style="text-align:left;"><table style="width:100%; border-collapse:collapse;">' + rows + '</table></div>',
            width: 680,
            confirmButtonText: 'Fermer'
          });
        }
        </script>
      </td>
    </tr>
    <?php
  } // fin while
} else {
  echo '<tr><td colspan="1" class="text-left" style="font-style:italic;">Aucune commande</td></tr>';
}
?>
  </tbody>
</table>

<!-- PAGINATION SIMPLE -->
<nav aria-label="Pagination">
  <ul class="pagination">
    <?php
    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
    // Construit la query string courante en conservant les filtres
    $qs = $_GET;
    for ($i=1; $i<=$pages; $i++) {
        $qs['page_i'] = $i;
        $url = htmlspecialchars($baseUrl . '?' . http_build_query($qs));
        $active = ($i === $page_i) ? 'active' : '';
        echo "<li class='page-item $active'><a class='page-link' href='$url'>$i</a></li>";
    }
    ?>
  </ul>
</nav>

<!-- STATISTIQUES PAR UTILISATEUR (créaevent) : total et par mois -->
<?php
// Statistiques globales (total distinct cod_event par utilisateur)
$stmtStats = $pdo->query("SELECT cod_user, COUNT(DISTINCT cod_event) as total FROM creaevent GROUP BY cod_user ORDER BY total DESC");
$userStats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

// Statistiques du mois en cours (distinct cod_event par utilisateur)
$mois = date('m');
$annee = date('Y');
$stmtStatsMois = $pdo->prepare("SELECT cod_user, COUNT(DISTINCT cod_event) as total_mois FROM creaevent WHERE MONTH(date_enreg) = ? AND YEAR(date_enreg) = ? GROUP BY cod_user ORDER BY total_mois DESC");
$stmtStatsMois->execute([$mois, $annee]);
$userStatsMois = [];
foreach ($stmtStatsMois->fetchAll(PDO::FETCH_ASSOC) as $row) {
  $userStatsMois[$row['cod_user']] = $row['total_mois'];
}

if ($userStats) {
  echo '<div style="margin:32px 0 0 0; padding:18px; background:#f8fafc; border-radius:18px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">';
  echo '<h5 style="margin-bottom:16px; color:#1e293b;">Statistiques des réalisations par utilisateur</h5>';
  echo '<table style="width:auto; min-width:420px; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.03);">';
  echo '<tr style="background:#e2e8f0; color:#334155; font-weight:700;"><th style="padding:10px 18px;">Utilisateur</th><th style="padding:10px 18px;">Total réalisés</th><th style="padding:10px 18px;">Ce mois</th></tr>';
  foreach ($userStats as $row) {
    // Récupérer le nom de l'utilisateur
    $stmtNom = $pdo->prepare("SELECT noms FROM is_users WHERE cod_user = ?");
    $stmtNom->execute([$row['cod_user']]);
    $nom = $stmtNom->fetchColumn() ?: $row['cod_user'];
    $totalMois = $userStatsMois[$row['cod_user']] ?? 0;
    echo '<tr>';
    echo '<td style="padding:8px 18px; border-bottom:1px solid #e2e8f0;">' . htmlspecialchars($nom) . '</td>';
    echo '<td style="padding:8px 18px; border-bottom:1px solid #e2e8f0; text-align:center; font-weight:600; color:#0f172a;">' . (int)$row['total'] . '</td>';
    echo '<td style="padding:8px 18px; border-bottom:1px solid #e2e8f0; text-align:center; color:#2563eb;">' . (int)$totalMois . '</td>';
    echo '</tr>';
  }
  echo '</table>';
  echo '</div>';
}
?>
