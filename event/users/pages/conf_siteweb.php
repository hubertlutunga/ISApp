<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>

<div class="wrapper">

<?php include('header.php'); ?>
<?php include('editersite.php'); ?>

<?php
$sitePreviewUrl = EventUrlService::publicUrl(
  is_array($dataevent ?? null) ? $dataevent : ['cod_event' => $codevent, 'type_event' => $type_event],
  $isAppConfig
);

$isPublicProgramEvent = (string) $type_event !== '1';
$publicLogoPreview = trim((string) ($dataevent['logo'] ?? ''));
$publicPhotoPreview = trim((string) ($dataevent['photo'] ?? ''));

$loveStory = LoveStoryService::getByEvent($pdo, (int) $codevent);
$photolove1 = (string) ($loveStory['imgcoeur1'] ?? '');
$photolove2 = (string) ($loveStory['imgcoeur2'] ?? '');
$text_lovestory = (string) ($loveStory['text_lovestory'] ?? '');
$loveStorySteps = LoveStoryService::listSteps($pdo, (int) $codevent);

$text_sdd = (string) ($weddingSiteSettings['content']['save_text'] ?? '');
try {
  $saveDateStmt = $pdo->prepare("SELECT text_sdd FROM websitewedgeneral WHERE cod_event = ? LIMIT 1");
  $saveDateStmt->execute([(int) $codevent]);
  $saveDateRow = $saveDateStmt->fetch(PDO::FETCH_ASSOC);
  if ($text_sdd === '' && !empty($saveDateRow['text_sdd'])) {
    $text_sdd = (string) $saveDateRow['text_sdd'];
  }
  $saveDateStmt->closeCursor();
} catch (Throwable $exception) {
  $text_sdd = $text_sdd;
}
if (trim((string) ($weddingSiteSettings['content']['save_text'] ?? '')) === '' && $text_sdd !== '') {
  $weddingSiteSettings['content']['save_text'] = $text_sdd;
}

$weddingSiteFieldLabels = [
  'hero_title' => 'Titre principal',
  'hero_subtitle' => 'Sous-titre / phrase compteur',
  'hero_button' => 'Texte du bouton',
  'save_title' => 'Titre',
  'save_text' => 'Texte “Save the date”',
  'wedding_title' => 'Titre',
  'wedding_subtitle' => 'Sous-titre',
  'ceremony_title' => 'Titre cérémonie',
  'ceremony_time' => 'Heure cérémonie',
  'ceremony_place' => 'Lieu cérémonie',
  'party_title' => 'Titre fête',
  'party_time' => 'Heure fête',
  'party_place' => 'Lieu fête',
  'gift_title' => 'Titre cadeaux',
  'gift_text' => 'Texte cadeaux',
  'gift_items' => 'Liste des besoins / cadeaux',
  'friends_title' => 'Titre invités',
  'friends_subtitle' => 'Sous-titre invités',
  'guest_empty_text' => 'Texte si aucun message n’est sélectionné',
  'rsvp_title' => 'Titre RSVP',
  'rsvp_subtitle' => 'Sous-titre RSVP',
  'location_title' => 'Titre adresse',
  'location_subtitle' => 'Sous-titre adresse',
];

$weddingSiteImageFields = [
  'hero_bg' => 'Image Accueil / Compteur',
  'save_heart' => 'Image Save the date',
  'wedding_bg' => 'Image Wedding Events',
];

$weddingSiteSectionPanels = [
  'hero' => ['title' => 'Accueil / Compteur', 'fields' => ['hero_title', 'hero_subtitle', 'hero_button'], 'images' => ['hero_bg']],
  'save_date' => ['title' => 'Save the date', 'fields' => ['save_title', 'save_text'], 'images' => ['save_heart']],
  'love_story' => ['title' => 'Love Story', 'fields' => [], 'images' => []],
  'wedding_events' => ['title' => 'Wedding Events', 'fields' => ['wedding_title', 'wedding_subtitle', 'ceremony_title', 'ceremony_time', 'ceremony_place', 'party_title', 'party_time', 'party_place'], 'images' => ['wedding_bg']],
  'gift' => ['title' => 'Cadeaux', 'fields' => ['gift_title', 'gift_text', 'gift_items'], 'images' => []],
  'friends' => ['title' => 'Nos invités', 'fields' => ['friends_title', 'friends_subtitle', 'guest_empty_text'], 'images' => []],
  'rsvp' => ['title' => 'RSVP', 'fields' => ['rsvp_title', 'rsvp_subtitle'], 'images' => []],
  'location' => ['title' => 'Where To Stay', 'fields' => ['location_title', 'location_subtitle'], 'images' => []],
  'gallery' => ['title' => 'Galeries', 'fields' => [], 'images' => []],
];

$weddingSiteImageUrl = static function (string $imageName): string {
  $imageName = trim($imageName);
  return $imageName !== '' ? '../../couple/images/' . htmlspecialchars($imageName, ENT_QUOTES, 'UTF-8') : '';
};

$guestConfirmationChoices = [];
$selectedGuestConfirmationIds = array_values(array_filter(array_map('intval', preg_split('/[\s,;]+/', (string) ($weddingSiteSettings['content']['guest_confirmation_ids'] ?? '')))));
try {
  $guestConfirmationStmt = $pdo->prepare("SELECT cod_conf, noms, note, presence, date_enreg FROM confirmation WHERE cod_mar = ? AND note IS NOT NULL AND TRIM(note) <> '' ORDER BY date_enreg DESC LIMIT 80");
  $guestConfirmationStmt->execute([(int) $codevent]);
  $guestConfirmationChoices = $guestConfirmationStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  $guestConfirmationStmt->closeCursor();
} catch (Throwable $exception) {
  $guestConfirmationChoices = [];
}

$locationAccommodationsJson = (string) ($weddingSiteSettings['content']['location_accommodations'] ?? '[]');
$locationAccommodations = json_decode($locationAccommodationsJson, true);
if (!is_array($locationAccommodations)) {
  $locationAccommodations = [];
  $locationAccommodationsJson = '[]';
}

$galleryPhotos = [];
try {
  $galleryStmt = $pdo->prepare("SELECT cod_gp, nom_photo FROM galeriephotos WHERE cod_event = ? ORDER BY cod_gp DESC LIMIT 5");
  $galleryStmt->execute([(int) $codevent]);
  $galleryPhotos = $galleryStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  $galleryStmt->closeCursor();
} catch (Throwable $exception) {
  $galleryPhotos = [];
}
$galleryRemainingSlots = max(0, 5 - count($galleryPhotos));
?>

<style>
html,body{ height:auto !important; min-height:100% !important; overflow-y:auto !important; }
.wrapper,.content-wrapper,.container-full{ height:auto !important; min-height:0 !important; overflow:visible !important; }
.content-wrapper{ padding-bottom:40px; }
.mb-siteconf-page{ padding:8px 0 34px; }
.mb-siteconf-hero{ padding:30px; border-radius:30px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 55%,#0ea5e9 100%); box-shadow:0 24px 50px rgba(15,23,42,.16); color:#fff; margin-bottom:24px; }
.mb-siteconf-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
.mb-siteconf-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
.mb-siteconf-copy{ margin:0; max-width:760px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
.mb-siteconf-actions{ display:flex; gap:12px; flex-wrap:wrap; margin-top:20px; }
.mb-siteconf-preview{ display:inline-flex; align-items:center; gap:8px; min-height:46px; padding:0 16px; border-radius:14px; background:#fff; color:#0f172a !important; text-decoration:none; font-weight:800; }
.content .box{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.08); }
.content .box-header{ padding:22px 24px; border-bottom:1px solid #eef2f7; cursor:pointer; }
.content .box-title{ font-size:22px; font-weight:800; color:#0f172a; }
.content .box-body{ padding:24px; display:none; }
.content .box.open .box-body{ display:block; animation:fadeIn .3s ease; }
.content .form-control{ border-radius:16px; border:1px solid #dbeafe; background:#f8fbff; box-shadow:none; }
.content textarea.form-control{ min-height:140px; }
.content .btn.btn-primary,.content .btn.btn-warning,.content .btn.btn-success{ min-height:52px; border-radius:16px; font-weight:800; }
.mb-wedconf-grid{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
.mb-wedconf-radio-group{ display:flex; flex-wrap:wrap; gap:8px; }
.mb-wedconf-radio{ display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border:1px solid #dbeafe; border-radius:999px; background:#fff; color:#334155; font-size:12px; font-weight:800; }
.mb-wedconf-radio input{ width:16px; height:16px; }
.mb-wedconf-locked{ display:inline-flex; width:max-content; align-items:center; padding:7px 10px; border-radius:999px; background:#dcfce7; color:#166534; font-size:12px; font-weight:900; }
.mb-wedconf-toggle{ display:flex; flex-direction:column; gap:10px; padding:12px 14px; border:1px solid #dbeafe; border-radius:16px; background:#f8fbff; color:#0f172a; }
.mb-wedconf-toggle-title{ font-weight:900; }
.mb-wedconf-field{ margin-bottom:12px; }
.mb-wedconf-field label{ display:block; margin-bottom:6px; color:#334155; font-weight:800; font-size:13px; }
.mb-wedconf-image-grid{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
.mb-wedconf-image-card{ border:1px solid #e2e8f0; border-radius:18px; padding:12px; background:#f8fafc; }
.mb-wedconf-image-card strong{ display:block; margin-bottom:8px; color:#0f172a; font-size:13px; }
.mb-wedconf-image-card img{ width:100%; height:120px; object-fit:cover; border-radius:14px; border:1px solid #e2e8f0; margin-bottom:10px; background:#fff; }
.mb-wedconf-empty-img{ display:flex; align-items:center; justify-content:center; min-height:120px; border:1px dashed #cbd5e1; border-radius:14px; color:#64748b; font-weight:800; margin-bottom:10px; background:#fff; }
.mb-wedconf-list{ display:grid; gap:10px; }
.mb-wedconf-list-card{ border:1px solid #e2e8f0; border-radius:16px; padding:12px; background:#f8fafc; }
.mb-wedconf-list-card strong{ display:block; color:#0f172a; font-weight:900; }
.mb-wedconf-list-card span{ display:block; color:#64748b; font-weight:700; font-size:13px; margin-top:4px; }
.mb-wedconf-list-actions{ display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
.mb-wedconf-modal{ position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:9999; background:rgba(15,23,42,.62); padding:18px; }
.mb-wedconf-modal.is-open{ display:flex; }
.mb-wedconf-modal-card{ width:min(760px,100%); max-height:92vh; overflow:auto; border-radius:24px; background:#fff; padding:22px; box-shadow:0 24px 70px rgba(15,23,42,.24); }
.mb-wedconf-modal-head{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px; }
.mb-wedconf-modal-head h5{ margin:0; font-weight:900; color:#0f172a; }
.mb-wedconf-guest-actions{ display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
.mb-wedconf-guest-btn{ border:1px solid #dbeafe; border-radius:999px; padding:8px 12px; background:#fff; color:#334155; font-weight:900; cursor:pointer; }
.mb-wedconf-guest-btn.is-active{ background:#0f172a; color:#fff; border-color:#0f172a; }
.mb-wedconf-section-card{ border:1px solid #e2e8f0; border-radius:24px; overflow:hidden; background:#fff; box-shadow:0 16px 34px rgba(15,23,42,.06); margin-bottom:16px; }
.mb-wedconf-section-head{ display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 18px; background:linear-gradient(135deg,#f8fafc 0%,#eff6ff 100%); border-bottom:1px solid #e2e8f0; }
.mb-wedconf-section-title{ display:flex; align-items:center; gap:10px; color:#0f172a; font-size:18px; font-weight:900; }
.mb-wedconf-section-index{ display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:50%; background:#0f172a; color:#fff; font-size:13px; font-weight:900; }
.mb-wedconf-section-body{ padding:18px; }
@keyframes fadeIn{ from{opacity:0} to{opacity:1} }
@media only screen and (max-width:769px){ .mb-siteconf-hero{ padding:22px 20px; border-radius:24px; } .mb-siteconf-title{ font-size:28px; } .content .box-header,.content .box-body{ padding:18px; } .mb-wedconf-grid,.mb-wedconf-image-grid{ grid-template-columns:1fr; } .mb-wedconf-section-head{ align-items:flex-start; flex-direction:column; } }
</style>

<div class="content-wrapper">
  <div class="container-full">
    <div class="mb-siteconf-page">
      <div class="mb-siteconf-hero">
        <span class="mb-siteconf-kicker"><i class="mdi mdi-palette-outline"></i> Personnalisation du site</span>
        <h1 class="mb-siteconf-title">Façonnez une vitrine événementielle à la hauteur du parcours</h1>
        <p class="mb-siteconf-copy">Ajustez les visuels, les textes et les sections publiques depuis une interface plus cohérente avec le reste du tableau de bord.</p>
        <div class="mb-siteconf-actions">
          <a class="mb-siteconf-preview" target="_blank" href="<?php echo htmlspecialchars($sitePreviewUrl, ENT_QUOTES, 'UTF-8'); ?>"><i class="mdi mdi-eye-outline"></i> Prévisualiser le site</a>
        </div>
      </div>

      <section class="content">
        <div class="row">
          <?php if ((string) $type_event !== '1') { ?>
          <div class="col-12">
            <div class="box open">
              <div class="box-header"><h4 class="box-title">Site mariage indisponible</h4></div>
              <div class="box-body"><div class="alert alert-warning mb-0">Cette personnalisation avancée correspond uniquement au modèle complet de site web mariage.</div></div>
            </div>
          </div>
          <?php } ?>

          <?php if ((string) $type_event === '1') { ?>
          <div class="col-12">
            <div class="box open">
              <div class="box-header"><h4 class="box-title">Modèle complet mariage</h4></div>
              <div class="box-body">
                <form action="" method="post" enctype="multipart/form-data" id="weddingSiteForm">
                  <p style="margin-bottom:18px;color:#64748b;font-weight:700;">Par défaut, toutes les sections sont affichées. Masquez seulement les sections que vous ne souhaitez pas publier.</p>
                  <?php $sectionCounter = 1; foreach ($weddingSiteSectionPanels as $sectionKey => $sectionPanel) { $sectionEnabled = WeddingWebsiteSettingsService::sectionEnabled($weddingSiteSettings, $sectionKey); $sectionLocked = in_array($sectionKey, WeddingWebsiteSettingsService::ALWAYS_VISIBLE_SECTIONS, true); ?>
                  <div class="mb-wedconf-section-card">
                    <div class="mb-wedconf-section-head">
                      <div class="mb-wedconf-section-title"><span class="mb-wedconf-section-index"><?php echo $sectionCounter; ?></span><?php echo htmlspecialchars($sectionPanel['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                      <?php if ($sectionLocked) { ?>
                      <input type="hidden" name="wedding_sections[<?php echo htmlspecialchars($sectionKey, ENT_QUOTES, 'UTF-8'); ?>]" value="show"><span class="mb-wedconf-locked">Toujours affichée</span>
                      <?php } else { ?>
                      <div class="mb-wedconf-radio-group"><label class="mb-wedconf-radio"><input type="radio" name="wedding_sections[<?php echo htmlspecialchars($sectionKey, ENT_QUOTES, 'UTF-8'); ?>]" value="show" <?php echo $sectionEnabled ? 'checked' : ''; ?>><span>Afficher</span></label><label class="mb-wedconf-radio"><input type="radio" name="wedding_sections[<?php echo htmlspecialchars($sectionKey, ENT_QUOTES, 'UTF-8'); ?>]" value="hide" <?php echo !$sectionEnabled ? 'checked' : ''; ?>><span>Masquer</span></label></div>
                      <?php } ?>
                    </div>
                    <div class="mb-wedconf-section-body">
                      <?php if (!empty($sectionPanel['fields'])) { ?>
                      <div class="mb-wedconf-grid">
                        <?php foreach ($sectionPanel['fields'] as $fieldName) { $fieldValue = (string) ($weddingSiteSettings['content'][$fieldName] ?? ''); $isLongField = str_contains($fieldName, 'text') || str_contains($fieldName, 'place') || str_contains($fieldName, 'items'); ?>
                        <div class="mb-wedconf-field"><label for="wed_<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($weddingSiteFieldLabels[$fieldName] ?? $fieldName, ENT_QUOTES, 'UTF-8'); ?></label><?php if ($isLongField) { ?><textarea id="wed_<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" rows="4"><?php echo htmlspecialchars($fieldValue, ENT_QUOTES, 'UTF-8'); ?></textarea><?php } else { ?><input id="wed_<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>" type="text" name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" value="<?php echo htmlspecialchars($fieldValue, ENT_QUOTES, 'UTF-8'); ?>"><?php } ?></div>
                        <?php } ?>
                      </div>
                      <?php } ?>

                      <?php if (!empty($sectionPanel['images'])) { ?>
                      <div class="mb-wedconf-image-grid" style="margin-top:12px;">
                        <?php foreach ($sectionPanel['images'] as $imageField) { $imageUrl = $weddingSiteImageUrl((string) ($weddingSiteSettings['images'][$imageField] ?? '')); ?>
                        <div class="mb-wedconf-image-card"><strong><?php echo htmlspecialchars($weddingSiteImageFields[$imageField] ?? $imageField, ENT_QUOTES, 'UTF-8'); ?></strong><?php if ($imageUrl !== '') { ?><img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($weddingSiteImageFields[$imageField] ?? $imageField, ENT_QUOTES, 'UTF-8'); ?>"><?php } else { ?><div class="mb-wedconf-empty-img">Aucune image</div><?php } ?><input type="file" name="wedding_image_<?php echo htmlspecialchars($imageField, ENT_QUOTES, 'UTF-8'); ?>" accept="image/*" class="form-control"></div>
                        <?php } ?>
                      </div>
                      <?php } ?>

                      <?php if ($sectionKey === 'love_story') { ?>
                      <div class="alert alert-light" style="border-radius:16px;font-weight:700;">Configurez le texte, les photos de début/fin et les étapes dans une fenêtre modale.</div><button type="button" class="btn btn-primary" id="openLoveStoryModal">Configurer la Love Story</button>
                      <?php if (!empty($loveStorySteps)) { ?><div class="mb-wedconf-list" style="margin-top:14px;"><?php foreach ($loveStorySteps as $row_ls) { ?><div class="mb-wedconf-list-card"><strong><?php echo htmlspecialchars((string) $row_ls['event_etap'], ENT_QUOTES, 'UTF-8'); ?></strong><span><?php echo htmlspecialchars(date('F Y', strtotime((string) $row_ls['date_etap'])), ENT_QUOTES, 'UTF-8'); ?></span></div><?php } ?></div><?php } ?>
                      <?php } ?>

                      <?php if ($sectionKey === 'friends') { ?>
                      <div style="margin-top:14px;"><h5>Messages des invités à afficher</h5><p style="margin-top:-6px;color:#64748b;font-weight:700;">Choisissez les confirmations dont les mots doivent apparaître dans la section “Nos invités”.</p><?php if (!empty($guestConfirmationChoices)) { ?><div class="mb-wedconf-grid"><?php foreach ($guestConfirmationChoices as $guestConfirmation) { $confirmationId = (int) ($guestConfirmation['cod_conf'] ?? 0); ?><div class="mb-wedconf-toggle"><span class="mb-wedconf-toggle-title"><input class="guest-message-checkbox" id="guest_message_<?php echo $confirmationId; ?>" type="checkbox" name="guest_confirmation_ids[]" value="<?php echo $confirmationId; ?>" <?php echo in_array($confirmationId, $selectedGuestConfirmationIds, true) ? 'checked' : ''; ?> style="width:16px;height:16px;margin-right:8px;"><?php echo htmlspecialchars((string) ($guestConfirmation['noms'] ?? 'Invité'), ENT_QUOTES, 'UTF-8'); ?></span><span style="color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;"><?php echo htmlspecialchars((string) ($guestConfirmation['presence'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span><span style="color:#334155;line-height:1.5;"><?php echo nl2br(htmlspecialchars((string) ($guestConfirmation['note'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></span><div class="mb-wedconf-guest-actions"><button type="button" class="mb-wedconf-guest-btn" data-guest-action="show" data-target="guest_message_<?php echo $confirmationId; ?>">Afficher</button><button type="button" class="mb-wedconf-guest-btn" data-guest-action="hide" data-target="guest_message_<?php echo $confirmationId; ?>">Retirer</button></div></div><?php } ?></div><?php } else { ?><div class="alert alert-light mb-0" style="border-radius:16px;">Aucun message de confirmation n’est disponible pour le moment.</div><?php } ?></div>
                      <?php } ?>

                      <?php if ($sectionKey === 'location') { ?>
                      <div style="margin-top:14px;"><h5>Hébergements recommandés</h5><p style="margin-top:-6px;color:#64748b;font-weight:700;">Ajoutez les hôtels ou logements conseillés avec le nom, l’adresse et éventuellement un iframe Google Maps.</p><textarea id="wed_location_accommodations" name="location_accommodations" style="display:none;"><?php echo htmlspecialchars($locationAccommodationsJson, ENT_QUOTES, 'UTF-8'); ?></textarea><div id="wedAccommodationList" class="mb-wedconf-list"></div><div class="mt-15"><button type="button" class="btn btn-primary" id="openAccommodationModal">Ajouter un hébergement</button></div></div>
                      <?php } ?>

                      <?php if ($sectionKey === 'gallery') { ?>
                      <div class="alert alert-light" style="border-radius:16px;font-weight:700;">La galerie publique affiche au maximum 5 photos. Places disponibles : <?php echo (int) $galleryRemainingSlots; ?>.</div><?php if (!empty($galleryPhotos)) { ?><div class="mb-wedconf-image-grid" style="margin-bottom:12px;"><?php foreach ($galleryPhotos as $dataphoto) { ?><div class="mb-wedconf-image-card"><img src="galeriephoto/<?php echo htmlspecialchars((string) $dataphoto['nom_photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Photo galerie"><button type="button" class="btn btn-warning btn-sm w-p100" onclick="confirmSuppEvent(event, '<?php echo (int) $dataphoto['cod_gp']; ?>', '<?php echo (int) $codevent; ?>')">Supprimer</button></div><?php } ?></div><?php } else { ?><div class="mb-wedconf-empty-img">Aucune photo dans la galerie</div><?php } ?><div class="mb-wedconf-field mb-0"><label for="fileInput">Importer les photos de la galerie</label><input type="file" name="gallery_photos[]" class="form-control" accept="image/*" id="fileInput" multiple <?php echo $galleryRemainingSlots <= 0 ? 'disabled' : ''; ?> data-max-files="<?php echo (int) $galleryRemainingSlots; ?>"><small style="display:block;margin-top:8px;color:#64748b;font-weight:700;">Vous pouvez ajouter seulement <?php echo (int) $galleryRemainingSlots; ?> photo(s) supplémentaire(s).</small><div id="previewContainer" class="mt-2" style="display:flex;flex-wrap:wrap;"></div></div>
                      <?php } ?>
                    </div>
                  </div>
                  <?php $sectionCounter++; } ?>

                  <div class="mb-wedconf-modal" id="accommodationModal" aria-hidden="true"><div class="mb-wedconf-modal-card"><div class="mb-wedconf-modal-head"><h5 id="accommodationModalTitle">Ajouter un hébergement</h5><button type="button" class="btn btn-warning btn-sm" id="closeAccommodationModal">Fermer</button></div><input type="hidden" id="accommodationIndex" value=""><div class="mb-wedconf-field"><label for="accommodationName">Nom de l’hébergement</label><input type="text" id="accommodationName" class="form-control" placeholder="Ex: Hôtel du Fleuve"></div><div class="mb-wedconf-field"><label for="accommodationAddress">Adresse</label><textarea id="accommodationAddress" class="form-control" rows="3" placeholder="Adresse complète"></textarea></div><div class="mb-wedconf-field"><label for="accommodationMap">Maps / iframe Google Maps</label><textarea id="accommodationMap" class="form-control" rows="4" placeholder="Collez ici le code iframe Google Maps ou un lien Maps"></textarea></div><div class="text-center mt-15"><button type="button" class="btn btn-primary" id="saveAccommodation">Enregistrer l’hébergement</button></div></div></div>

                  <div class="mb-wedconf-modal" id="loveStoryModal" aria-hidden="true"><div class="mb-wedconf-modal-card"><div class="mb-wedconf-modal-head"><h5>Configurer la Love Story</h5><button type="button" class="btn btn-warning btn-sm" id="closeLoveStoryModal">Fermer</button></div><div class="mb-wedconf-grid"><div class="mb-wedconf-field"><label for="love_title_modal">Titre de la section</label><input type="text" id="love_title_modal" name="love_title" class="form-control" value="<?php echo htmlspecialchars((string) ($weddingSiteSettings['content']['love_title'] ?? 'Love Story'), ENT_QUOTES, 'UTF-8'); ?>"></div><div class="mb-wedconf-field"><label for="love_subtitle_modal">Sous-titre de la section</label><input type="text" id="love_subtitle_modal" name="love_subtitle" class="form-control" value="<?php echo htmlspecialchars((string) ($weddingSiteSettings['content']['love_subtitle'] ?? 'Notre histoire d’amour et le mariage'), ENT_QUOTES, 'UTF-8'); ?>"></div></div><div class="mb-wedconf-image-grid"><div class="mb-wedconf-image-card"><strong>Photo début Love Story</strong><?php if ($photolove1 !== '') { ?><img src="../../couple/images/<?php echo htmlspecialchars($photolove1, ENT_QUOTES, 'UTF-8'); ?>" alt="Photo début Love Story"><?php } else { ?><div class="mb-wedconf-empty-img">Aucune image</div><?php } ?><input type="file" name="photo3" accept="image/*" class="form-control"></div><div class="mb-wedconf-image-card"><strong>Photo fin Love Story</strong><?php if ($photolove2 !== '') { ?><img src="../../couple/images/<?php echo htmlspecialchars($photolove2, ENT_QUOTES, 'UTF-8'); ?>" alt="Photo fin Love Story"><?php } else { ?><div class="mb-wedconf-empty-img">Aucune image</div><?php } ?><input type="file" name="photo4" accept="image/*" class="form-control"></div></div><div class="mb-wedconf-field" style="margin-top:12px;"><label for="text_lovestory_modal">Texte Love Story</label><textarea name="text_lovestory" id="text_lovestory_modal" class="form-control" rows="6"><?php echo htmlspecialchars($text_lovestory, ENT_QUOTES, 'UTF-8'); ?></textarea></div><div class="mb-wedconf-grid"><div class="mb-wedconf-field"><label for="love_end_title_modal">Titre final</label><input type="text" id="love_end_title_modal" name="love_end_title" class="form-control" value="<?php echo htmlspecialchars((string) ($weddingSiteSettings['content']['love_end_title'] ?? 'Fin heureuse, nous nous marions'), ENT_QUOTES, 'UTF-8'); ?>"></div><div class="mb-wedconf-field"><label for="love_end_subtitle_modal">Sous-titre final</label><input type="text" id="love_end_subtitle_modal" name="love_end_subtitle" class="form-control" value="<?php echo htmlspecialchars((string) ($weddingSiteSettings['content']['love_end_subtitle'] ?? 'Comptez les jours...'), ENT_QUOTES, 'UTF-8'); ?>"></div></div><hr><h5>Étapes enregistrées</h5><p style="color:#64748b;font-weight:700;">Modifiez les étapes déjà enregistrées ou supprimez celles qui ne doivent plus apparaître.</p><div id="deletedLoveStepFields"></div><div class="mb-wedconf-list" id="existingLoveStepList"><?php if (!empty($loveStorySteps)) { foreach ($loveStorySteps as $row_ls) { $stepId = (int) ($row_ls['cod_ls'] ?? 0); $stepDateValue = !empty($row_ls['date_etap']) ? date('Y-m', strtotime((string) $row_ls['date_etap'])) : ''; ?><div class="mb-wedconf-list-card" data-existing-love-step="<?php echo $stepId; ?>"><input type="hidden" name="existing_love_step_ids[]" value="<?php echo $stepId; ?>"><div class="mb-wedconf-grid"><div class="mb-wedconf-field mb-0"><label>Mois et année</label><input type="month" name="existing_love_step_dates[]" class="form-control" value="<?php echo htmlspecialchars($stepDateValue, ENT_QUOTES, 'UTF-8'); ?>"></div><div class="mb-wedconf-field mb-0"><label>Événement étape</label><input type="text" name="existing_love_step_titles[]" class="form-control" value="<?php echo htmlspecialchars((string) ($row_ls['event_etap'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div></div><div class="mb-wedconf-list-actions"><button type="button" class="btn btn-warning btn-sm" data-delete-existing-love-step="<?php echo $stepId; ?>">Supprimer</button></div></div><?php } } else { ?><div class="alert alert-light mb-0" style="border-radius:16px;">Aucune étape enregistrée pour le moment.</div><?php } ?></div><hr><h5>Ajouter des étapes</h5><p style="color:#64748b;font-weight:700;">Ajoutez autant d’étapes que nécessaire, puis cliquez sur “Enregistrer les modifications”.</p><div class="mb-wedconf-grid"><div class="mb-wedconf-field"><label for="love_step_date">Mois et année</label><input type="month" id="love_step_date" class="form-control"></div><div class="mb-wedconf-field"><label for="love_step_title">Événement étape</label><input type="text" id="love_step_title" class="form-control" placeholder="Ex: Première rencontre"></div></div><button type="button" class="btn btn-primary" id="addLoveStoryStep">Ajouter cette étape</button><div id="loveStepHiddenFields"></div><div id="loveStepPendingList" class="mb-wedconf-list" style="margin-top:12px;"></div></div></div>

                  <div class="text-center mt-20"><button type="submit" name="submit_wedding_site_settings" class="btn btn-success w-p100">Enregistrer les modifications</button></div>
                </form>
              </div>
            </div>
          </div>
          <?php } ?>

          <?php if ($isPublicProgramEvent) { ?>
          <div class="col-12"><div class="box open"><div class="box-header"><h4 class="box-title">Mini-site public</h4></div><div class="box-body"><form action="" method="post" enctype="multipart/form-data" class="row g-3"><div class="col-lg-6"><label class="mb-5"><strong>Logo du site</strong></label><?php if ($publicLogoPreview !== '') { ?><img src="../../couple/images/<?php echo htmlspecialchars($publicLogoPreview, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo actuel" style="display:block;width:100%;max-width:280px;max-height:140px;object-fit:contain;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:12px;margin-bottom:12px;"><?php } ?><input type="file" name="public_logo" accept="image/*" class="form-control"></div><div class="col-lg-6"><label class="mb-5"><strong>Photo principale</strong></label><?php if ($publicPhotoPreview !== '') { ?><img src="../../couple/images/<?php echo htmlspecialchars($publicPhotoPreview, ENT_QUOTES, 'UTF-8'); ?>" alt="Visuel actuel" style="display:block;width:100%;max-width:320px;max-height:180px;object-fit:cover;background:#fff;border:1px solid #e2e8f0;border-radius:16px;margin-bottom:12px;"><?php } ?><input type="file" name="public_photo" accept="image/*" class="form-control"></div><div class="col-lg-6"><label for="public_agency" class="mb-5"><strong>Nom de l'agence / organisateur</strong></label><input type="text" id="public_agency" name="public_agency" class="form-control" value="<?php echo htmlspecialchars((string) ($conferenceSiteSettings['agency'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div><div class="col-lg-3"><label for="public_phone" class="mb-5"><strong>Téléphone</strong></label><input type="text" id="public_phone" name="public_phone" class="form-control" value="<?php echo htmlspecialchars((string) ($conferenceSiteSettings['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div><div class="col-lg-3"><label for="public_email" class="mb-5"><strong>Email</strong></label><input type="email" id="public_email" name="public_email" class="form-control" value="<?php echo htmlspecialchars((string) ($conferenceSiteSettings['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div><div class="col-12"><label for="public_iframe" class="mb-5"><strong>Iframe de localisation</strong></label><textarea id="public_iframe" name="public_iframe" class="form-control" rows="6"><?php echo htmlspecialchars((string) ($conferenceSiteSettings['iframe'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea></div><div class="col-12"><button type="submit" name="submit_public_site_customization" class="btn btn-primary">Enregistrer le mini-site</button></div></form></div></div></div>
          <?php } ?>
        </div>
      </section>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmSuppEvent(event, codPhoto, codGetevent) {
  event.preventDefault();
  Swal.fire({title:'Supprimer !', text:'Êtes-vous sûr de vouloir supprimer cette photo ?', icon:'warning', showCancelButton:true, confirmButtonText:'Oui, supprimer', cancelButtonText:'Non'}).then((result) => {
    if (result.isConfirmed) window.location.href = 'index.php?page=supphotogalerie&cod=' + codPhoto + '&codevent=' + codGetevent;
  });
}
(function(){ document.querySelectorAll('.box').forEach(function(box){ const header = box.querySelector('.box-header'); if (!header) return; header.addEventListener('click', function(e){ if (!e.target.closest('button,a,input,label,textarea,select')) box.classList.toggle('open'); }); }); })();
(function(){ const openBtn = document.getElementById('openLoveStoryModal'); const closeBtn = document.getElementById('closeLoveStoryModal'); const modal = document.getElementById('loveStoryModal'); if (!openBtn || !closeBtn || !modal) return; openBtn.addEventListener('click', function(){ modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false'); }); closeBtn.addEventListener('click', function(){ modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true'); }); modal.addEventListener('click', function(event){ if (event.target === modal) closeBtn.click(); }); })();
(function(){ const list = document.getElementById('existingLoveStepList'); const deletedFields = document.getElementById('deletedLoveStepFields'); if (!list || !deletedFields) return; list.addEventListener('click', function(event){ const button = event.target.closest('[data-delete-existing-love-step]'); if (!button) return; const stepId = button.getAttribute('data-delete-existing-love-step'); if (!stepId) return; const input = document.createElement('input'); input.type = 'hidden'; input.name = 'delete_love_step_ids[]'; input.value = stepId; deletedFields.appendChild(input); const row = button.closest('[data-existing-love-step]'); if (row) row.remove(); if (!list.querySelector('[data-existing-love-step]')) list.insertAdjacentHTML('beforeend', '<div class="alert alert-light mb-0" style="border-radius:16px;">Toutes les étapes enregistrées seront supprimées après enregistrement.</div>'); }); })();
(function(){ const addBtn = document.getElementById('addLoveStoryStep'); const dateInput = document.getElementById('love_step_date'); const titleInput = document.getElementById('love_step_title'); const hiddenFields = document.getElementById('loveStepHiddenFields'); const pendingList = document.getElementById('loveStepPendingList'); if (!addBtn || !dateInput || !titleInput || !hiddenFields || !pendingList) return; const steps = []; function escapeText(value){ return String(value || '').replace(/[&<>"]/g, function(char){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[char]; }); } function renderSteps(){ hiddenFields.innerHTML = steps.map(function(step){ return '<input type="hidden" name="love_step_dates[]" value="' + escapeText(step.date) + '"><input type="hidden" name="love_step_titles[]" value="' + escapeText(step.title) + '">'; }).join(''); pendingList.innerHTML = steps.length ? steps.map(function(step, index){ return '<div class="mb-wedconf-list-card"><strong>' + escapeText(step.title) + '</strong><span>' + escapeText(step.date) + '</span><div class="mb-wedconf-list-actions"><button type="button" class="btn btn-warning btn-sm" data-remove-love-step="' + index + '">Retirer</button></div></div>'; }).join('') : '<div class="alert alert-light mb-0" style="border-radius:16px;">Aucune nouvelle étape ajoutée pour cette sauvegarde.</div>'; } addBtn.addEventListener('click', function(){ const date = dateInput.value.trim(); const title = titleInput.value.trim(); if (!date || !title) { Swal.fire({icon:'warning', title:'Étape incomplète', text:'Renseignez le mois/année et le texte de l’étape.'}); return; } steps.push({date:date, title:title}); dateInput.value = ''; titleInput.value = ''; renderSteps(); }); pendingList.addEventListener('click', function(event){ const button = event.target.closest('[data-remove-love-step]'); if (!button) return; steps.splice(parseInt(button.getAttribute('data-remove-love-step'), 10), 1); renderSteps(); }); renderSteps(); })();
(function(){ function refreshGuestMessageButtons(checkbox){ const card = checkbox.closest('.mb-wedconf-toggle'); if (!card) return; card.querySelectorAll('[data-guest-action]').forEach(function(button){ const action = button.getAttribute('data-guest-action'); button.classList.toggle('is-active', (action === 'show' && checkbox.checked) || (action === 'hide' && !checkbox.checked)); }); } document.querySelectorAll('.guest-message-checkbox').forEach(function(checkbox){ refreshGuestMessageButtons(checkbox); checkbox.addEventListener('change', function(){ refreshGuestMessageButtons(checkbox); }); }); document.querySelectorAll('[data-guest-action][data-target]').forEach(function(button){ button.addEventListener('click', function(){ const checkbox = document.getElementById(button.getAttribute('data-target')); if (!checkbox) return; checkbox.checked = button.getAttribute('data-guest-action') === 'show'; refreshGuestMessageButtons(checkbox); }); }); })();
(function(){
  const list = document.getElementById('wedAccommodationList');
  const storage = document.getElementById('wed_location_accommodations');
  const modal = document.getElementById('accommodationModal');
  const openBtn = document.getElementById('openAccommodationModal');
  const closeBtn = document.getElementById('closeAccommodationModal');
  const saveBtn = document.getElementById('saveAccommodation');
  const indexInput = document.getElementById('accommodationIndex');
  const nameInput = document.getElementById('accommodationName');
  const addressInput = document.getElementById('accommodationAddress');
  const mapInput = document.getElementById('accommodationMap');
  const title = document.getElementById('accommodationModalTitle');
  if (!list || !storage || !modal || !openBtn || !closeBtn || !saveBtn) return;
  let accommodations = [];
  function encodeMap(value){
    return btoa(unescape(encodeURIComponent(String(value || ''))));
  }
  function decodeMap(value){
    if (!value) return '';
    try { return decodeURIComponent(escape(atob(String(value)))); } catch (error) { return ''; }
  }
  function normalizeItem(item){
    return {
      name: String(item && item.name ? item.name : ''),
      address: String(item && item.address ? item.address : ''),
      map: String(item && item.map ? item.map : decodeMap(item && item.map_b64 ? item.map_b64 : '')),
    };
  }
  try {
    const parsed = JSON.parse(storage.value || '[]');
    accommodations = Array.isArray(parsed) ? parsed.map(normalizeItem) : [];
  } catch (error) {
    accommodations = [];
  }
  function sync(){
    storage.value = JSON.stringify(accommodations.map(function(item){ return {name:item.name || '', address:item.address || '', map_b64:encodeMap(item.map || '')}; }));
  }
  function escapeText(value){ return String(value || '').replace(/[&<>"]/g, function(char){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[char]; }); }
  function openModal(index){
    const item = Number.isInteger(index) ? accommodations[index] : {name:'', address:'', map:''};
    indexInput.value = Number.isInteger(index) ? String(index) : '';
    nameInput.value = item.name || '';
    addressInput.value = item.address || '';
    mapInput.value = item.map || '';
    title.textContent = Number.isInteger(index) ? 'Modifier l’hébergement' : 'Ajouter un hébergement';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden','false');
  }
  function closeModal(){ modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true'); }
  function render(){
    sync();
    if (!accommodations.length) {
      list.innerHTML = '<div class="alert alert-light mb-0" style="border-radius:16px;">Aucun hébergement ajouté.</div>';
      return;
    }
    list.innerHTML = accommodations.map(function(item, index){ return '<div class="mb-wedconf-list-card"><strong>' + escapeText(item.name || 'Hébergement') + '</strong><span>' + escapeText(item.address || 'Adresse non renseignée') + '</span>' + (item.map ? '<span>Maps renseigné</span>' : '<span>Maps non renseigné</span>') + '<div class="mb-wedconf-list-actions"><button type="button" class="btn btn-primary btn-sm" data-edit="' + index + '">Modifier</button><button type="button" class="btn btn-warning btn-sm" data-delete="' + index + '">Supprimer</button></div></div>'; }).join('');
  }
  openBtn.addEventListener('click', function(){ openModal(null); });
  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function(event){ if (event.target === modal) closeModal(); });
  saveBtn.addEventListener('click', function(){
    const item = {name:nameInput.value.trim(), address:addressInput.value.trim(), map:mapInput.value.trim()};
    if (!item.name && !item.address && !item.map) {
      Swal.fire({title:'Hébergement vide', text:'Renseignez au moins un nom, une adresse ou une carte.', icon:'warning'});
      return;
    }
    const index = indexInput.value !== '' ? parseInt(indexInput.value, 10) : -1;
    if (index >= 0 && accommodations[index]) accommodations[index] = item; else accommodations.push(item);
    render();
    closeModal();
  });
  list.addEventListener('click', function(event){
    const editBtn = event.target.closest('[data-edit]');
    const deleteBtn = event.target.closest('[data-delete]');
    if (editBtn) openModal(parseInt(editBtn.getAttribute('data-edit'), 10));
    if (deleteBtn) { accommodations.splice(parseInt(deleteBtn.getAttribute('data-delete'), 10), 1); render(); }
  });
  render();
})();
document.addEventListener('DOMContentLoaded', function(){ const input = document.getElementById('fileInput'); const preview = document.getElementById('previewContainer'); if (input && preview) { input.addEventListener('change', function(){ preview.innerHTML = ''; const maxFiles = parseInt(input.getAttribute('data-max-files') || '5', 10); const files = Array.from(input.files || []); if (files.length > maxFiles) { Swal.fire({icon:'warning', title:'Limite atteinte', text:'La galerie ne peut pas dépasser 5 photos.'}); input.value = ''; return; } files.forEach(function(file){ if (!file.type.startsWith('image/')) return; const reader = new FileReader(); reader.onload = function(e){ const img = document.createElement('img'); img.src = e.target.result; img.style.width = '70px'; img.style.height = '70px'; img.style.objectFit = 'cover'; img.style.borderRadius = '10px'; img.style.marginRight = '8px'; img.style.marginTop = '8px'; preview.appendChild(img); }; reader.readAsDataURL(file); }); }); } });
</script>

<?php if (isset($_SESSION['alert'])): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({icon:'<?= $_SESSION['alert']['type']; ?>', title:'<?= $_SESSION['alert']['type'] === "success" ? "Succès" : ($_SESSION['alert']['type'] === "warning" ? "Attention" : "Erreur"); ?>', text:'<?= $_SESSION['alert']['message']; ?>', confirmButtonColor:'#3085d6'}); });</script>
<?php unset($_SESSION['alert']); endif; ?>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({title:'Supprimé !', text:'La photo a bien été supprimée.', icon:'success', timer:2000, showConfirmButton:false}); });</script>
<?php endif; ?>
