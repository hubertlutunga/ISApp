<?php

$cod_event  = $codevent;
$cod_agent  = $datasession['cod_user'];

$conferenceSiteSettings = [
    'iframe' => '',
    'agency' => '',
    'phone' => '',
    'email' => '',
];

$isWeddingWebsiteEvent = (string) ($type_event ?? '') === '1';
$isAjaxRequest = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
$weddingSiteSettings = $isWeddingWebsiteEvent
    ? WeddingWebsiteSettingsService::get($pdo, (int) $cod_event, is_array($dataevent ?? null) ? $dataevent : [])
    : WeddingWebsiteSettingsService::defaults(is_array($dataevent ?? null) ? $dataevent : []);

$saveWeddingSitePartial = static function (array $content = [], array $images = []) use ($pdo, $cod_event, $isWeddingWebsiteEvent, &$weddingSiteSettings): void {
    if (!$isWeddingWebsiteEvent) {
        return;
    }

    foreach ($content as $field => $value) {
        if (array_key_exists($field, $weddingSiteSettings['content'])) {
            $weddingSiteSettings['content'][$field] = trim((string) $value);
        }
    }

    foreach ($images as $field => $value) {
        $value = trim((string) $value);
        if ($value !== '' && array_key_exists($field, $weddingSiteSettings['images'])) {
            $weddingSiteSettings['images'][$field] = $value;
        }
    }

    WeddingWebsiteSettingsService::save($pdo, (int) $cod_event, $weddingSiteSettings);
};

$uploadWeddingGalleryPhotos = static function (array $galleryFiles) use ($pdo, $cod_event): int {
    if (!isset($galleryFiles['tmp_name']) || !is_array($galleryFiles['tmp_name'])) {
        return 0;
    }

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM galeriephotos WHERE cod_event = ?');
    $countStmt->execute([(int) $cod_event]);
    $currentGalleryCount = (int) $countStmt->fetchColumn();
    $remainingGallerySlots = max(0, 5 - $currentGalleryCount);
    $insertGalleryPhoto = $pdo->prepare('INSERT INTO galeriephotos (cod_event, nom_photo) VALUES (?, ?)');
    $importedCount = 0;

    $galleryTargetDir = __DIR__ . '/../galeriephoto';
    if (!is_dir($galleryTargetDir) && !mkdir($galleryTargetDir, 0775, true) && !is_dir($galleryTargetDir)) {
        throw new RuntimeException('Le dossier de destination de la galerie est introuvable.');
    }

    foreach ($galleryFiles['tmp_name'] as $key => $tmpName) {
        if ($remainingGallerySlots <= 0) {
            break;
        }
        if (!is_uploaded_file((string) $tmpName) || (($galleryFiles['error'][$key] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)) {
            continue;
        }

        $singleGalleryFile = [
            'name' => $galleryFiles['name'][$key] ?? 'galerie.jpg',
            'type' => $galleryFiles['type'][$key] ?? '',
            'tmp_name' => $tmpName,
            'error' => $galleryFiles['error'][$key] ?? UPLOAD_ERR_NO_FILE,
            'size' => $galleryFiles['size'][$key] ?? 0,
        ];
        $galleryPhotoName = EventMediaService::storeUploadedImage($singleGalleryFile, $galleryTargetDir, 'galerie_');
        if ($galleryPhotoName !== null) {
            $insertGalleryPhoto->execute([(int) $cod_event, $galleryPhotoName]);
            $remainingGallerySlots--;
            $importedCount++;
        }
    }

    if ($importedCount === 0 && count(array_filter($galleryFiles['name'] ?? [])) > 0) {
        throw new RuntimeException('Aucune photo n’a pu être enregistrée dans la galerie.');
    }

    return $importedCount;
};

try {
    $conferenceSiteStmt = $pdo->prepare('SELECT iframe, agency, phone, email FROM websiteconference WHERE cod_event = ? LIMIT 1');
    $conferenceSiteStmt->execute([(int) $cod_event]);
    $conferenceSiteSettings = array_merge($conferenceSiteSettings, $conferenceSiteStmt->fetch(PDO::FETCH_ASSOC) ?: []);
    $conferenceSiteStmt->closeCursor();
} catch (Throwable $exception) {
    $conferenceSiteSettings = $conferenceSiteSettings;
}

if ($isWeddingWebsiteEvent && $isAjaxRequest && isset($_POST['submit_wedding_gallery_upload'])) {
    try {
        $importedCount = $uploadWeddingGalleryPhotos($_FILES['gallery_photos'] ?? []);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'importedCount' => $importedCount,
            'redirect' => 'index.php?page=conf_siteweb&codevent=' . (int) $cod_event . '&okwedding=1',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    } catch (RuntimeException $exception) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $exception->getMessage(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    } catch (Throwable $exception) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue pendant l’importation des photos de la galerie.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if ($isWeddingWebsiteEvent && isset($_POST['submit_wedding_site_settings'])) {
    try {
        $weddingSiteSettings = WeddingWebsiteSettingsService::fromPost($_POST, $weddingSiteSettings);
        $weddingSiteSettings = WeddingWebsiteSettingsService::uploadImages($_FILES, $weddingSiteSettings, '../../couple/images');

        $saveDateText = trim((string) ($_POST['save_text'] ?? ''));
        EventMediaService::upsertWebsiteGeneralText($pdo, (int) $cod_event, 'text_sdd', $saveDateText);

        $lovePhotoStart = null;
        $lovePhotoEnd = null;
        if (isset($_FILES['photo3']) && ($_FILES['photo3']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $lovePhotoStart = EventMediaService::storeUploadedImage($_FILES['photo3'], '../../couple/images');
        }
        if (isset($_FILES['photo4']) && ($_FILES['photo4']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $lovePhotoEnd = EventMediaService::storeUploadedImage($_FILES['photo4'], '../../couple/images');
        }
        LoveStoryService::upsert(
            $pdo,
            (int) $cod_event,
            (int) $cod_agent,
            (string) ($_POST['text_lovestory'] ?? ''),
            $lovePhotoStart ?: null,
            $lovePhotoEnd ?: null
        );

        $deletedLoveStepIds = isset($_POST['delete_love_step_ids']) && is_array($_POST['delete_love_step_ids']) ? array_map('intval', $_POST['delete_love_step_ids']) : [];
        foreach ($deletedLoveStepIds as $deletedLoveStepId) {
            if ($deletedLoveStepId > 0) {
                LoveStoryService::deleteStep($pdo, $deletedLoveStepId, (int) $cod_event);
            }
        }

        $existingLoveStepIds = isset($_POST['existing_love_step_ids']) && is_array($_POST['existing_love_step_ids']) ? $_POST['existing_love_step_ids'] : [];
        $existingLoveStepTitles = isset($_POST['existing_love_step_titles']) && is_array($_POST['existing_love_step_titles']) ? $_POST['existing_love_step_titles'] : [];
        $existingLoveStepDates = isset($_POST['existing_love_step_dates']) && is_array($_POST['existing_love_step_dates']) ? $_POST['existing_love_step_dates'] : [];
        foreach ($existingLoveStepIds as $stepIndex => $stepId) {
            $stepId = (int) $stepId;
            if ($stepId <= 0 || in_array($stepId, $deletedLoveStepIds, true)) {
                continue;
            }

            LoveStoryService::updateStep(
                $pdo,
                $stepId,
                (int) $cod_event,
                (int) $cod_agent,
                (string) ($existingLoveStepTitles[$stepIndex] ?? ''),
                (string) ($existingLoveStepDates[$stepIndex] ?? '')
            );
        }

        $loveStepTitles = isset($_POST['love_step_titles']) && is_array($_POST['love_step_titles']) ? $_POST['love_step_titles'] : [];
        $loveStepDates = isset($_POST['love_step_dates']) && is_array($_POST['love_step_dates']) ? $_POST['love_step_dates'] : [];
        if ($loveStepTitles === [] && $loveStepDates === []) {
            $loveStepTitles = [$_POST['love_step_title'] ?? ''];
            $loveStepDates = [$_POST['love_step_date'] ?? ''];
        }

        foreach ($loveStepTitles as $stepIndex => $stepTitle) {
            $loveStepTitle = trim((string) $stepTitle);
            $loveStepDate = trim((string) ($loveStepDates[$stepIndex] ?? ''));
            if ($loveStepTitle !== '' && $loveStepDate !== '') {
                LoveStoryService::addStep($pdo, (int) $cod_event, (int) $cod_agent, $loveStepTitle, $loveStepDate);
            }
        }

        $uploadWeddingGalleryPhotos($_FILES['gallery_photos'] ?? []);

        WeddingWebsiteSettingsService::save($pdo, (int) $cod_event, $weddingSiteSettings);

        echo '<script>window.location="index.php?page=conf_siteweb&codevent='.(int) $cod_event.'&okwedding=1";</script>';
    } catch (RuntimeException $exception) {
        echo '<script>Swal.fire({title:"Impossible d\'enregistrer",text:'.json_encode($exception->getMessage(), JSON_UNESCAPED_UNICODE).',icon:"error"});</script>';
    } catch (Throwable $exception) {
        echo '<script>Swal.fire({title:"Impossible d\'enregistrer",text:"Une erreur est survenue pendant la mise à jour du site mariage.",icon:"error"});</script>';
    }
}

if (isset($_GET['okwedding']) && $_GET['okwedding'] == 1) {
?>
<script>
Swal.fire({
title:'Succès !',
text:'La personnalisation du site mariage a été mise à jour.',
icon:'success',
timer:1500,
showConfirmButton:false
});

if(window.history.replaceState){
const url = new URL(window.location);
url.searchParams.delete('okwedding');
window.history.replaceState({}, document.title, url);
}
</script>
<?php }

if (isset($_POST['submit_public_site_customization'])) {
    $iframeEncoded = trim((string) ($_POST['public_iframe_b64'] ?? ''));
    $iframe = '';
    if ($iframeEncoded !== '') {
        $decodedIframe = base64_decode($iframeEncoded, true);
        if (is_string($decodedIframe)) {
            $iframe = trim($decodedIframe);
        }
    }
    if ($iframe === '') {
        $iframe = trim((string) ($_POST['public_iframe'] ?? ''));
    }
    $agency = trim((string) ($_POST['public_agency'] ?? ''));
    $phone = trim((string) ($_POST['public_phone'] ?? ''));
    $email = trim((string) ($_POST['public_email'] ?? ''));

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        echo '<script>Swal.fire({title:"Adresse invalide",text:"Veuillez renseigner une adresse email valide.",icon:"warning"});</script>';
    } else {
        try {
            $existsStmt = $pdo->prepare('SELECT COUNT(*) FROM websiteconference WHERE cod_event = ?');
            $existsStmt->execute([(int) $cod_event]);
            $conferenceSiteExists = (bool) $existsStmt->fetchColumn();
            $existsStmt->closeCursor();

            if ($conferenceSiteExists) {
                $writeConferenceSite = $pdo->prepare('UPDATE websiteconference SET iframe = :iframe, agency = :agency, phone = :phone, email = :email WHERE cod_event = :cod_event');
            } else {
                $writeConferenceSite = $pdo->prepare('INSERT INTO websiteconference (cod_event, iframe, agency, phone, email) VALUES (:cod_event, :iframe, :agency, :phone, :email)');
            }

            $writeConferenceSite->execute([
                ':cod_event' => (int) $cod_event,
                ':iframe' => $iframe !== '' ? $iframe : null,
                ':agency' => $agency !== '' ? $agency : null,
                ':phone' => $phone !== '' ? $phone : null,
                ':email' => $email !== '' ? $email : null,
            ]);

            $eventFieldUpdates = [];

            if (isset($_FILES['public_logo']) && ($_FILES['public_logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $eventFieldUpdates['logo'] = EventMediaService::storeUploadedImage($_FILES['public_logo'], '../../couple/images');
            }

            if (isset($_FILES['public_photo']) && ($_FILES['public_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $eventFieldUpdates['photo'] = EventMediaService::storeUploadedImage($_FILES['public_photo'], '../../couple/images');
            }

            if ($eventFieldUpdates !== []) {
                EventMediaService::updateEventFields($pdo, (int) $cod_event, $eventFieldUpdates);
            }

            echo '<script>window.location="index.php?page=conf_siteweb&codevent='.(int) $cod_event.'&okpublic=1";</script>';
        } catch (RuntimeException $exception) {
            echo '<script>Swal.fire({title:"Impossible d\'enregistrer",text:'.json_encode($exception->getMessage(), JSON_UNESCAPED_UNICODE).',icon:"error"});</script>';
        } catch (Throwable $exception) {
            echo '<script>Swal.fire({title:"Impossible d\'enregistrer",text:"Une erreur est survenue pendant la mise à jour du mini-site.",icon:"error"});</script>';
        }
    }
}

if (isset($_GET['okpublic']) && $_GET['okpublic'] == 1) {
?>
<script>
Swal.fire({
title:'Succès !',
text:'Le mini-site public a été mis à jour.',
icon:'success',
timer:1500,
showConfirmButton:false
});

if(window.history.replaceState){
const url = new URL(window.location);
url.searchParams.delete('okpublic');
window.history.replaceState({}, document.title, url);
}
</script>
<?php }


/* =====================================================
UPLOAD IMAGE FOND STORY
===================================================== */

if (isset($_POST['submitimgback'])) {
    try {
        $fileName = '';
        if (isset($_FILES['photo1']) && ($_FILES['photo1']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $fileName = EventMediaService::storeUploadedImage($_FILES['photo1'], '../../couple/images');
            EventMediaService::updateEventFields($pdo, (int) $cod_event, ['photostory' => $fileName]);
        }

        $saveWeddingSitePartial([
            'hero_title' => $_POST['hero_title'] ?? ($weddingSiteSettings['content']['hero_title'] ?? ''),
            'hero_subtitle' => $_POST['hero_subtitle'] ?? ($weddingSiteSettings['content']['hero_subtitle'] ?? ''),
            'hero_button' => $_POST['hero_button'] ?? ($weddingSiteSettings['content']['hero_button'] ?? ''),
        ], $fileName !== '' ? ['hero_bg' => $fileName] : []);

        echo '<script>window.location="index.php?page=conf_siteweb&ok=1";</script>';
    } catch (RuntimeException $e) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }

}


if (isset($_GET['ok']) && $_GET['ok']==1){
?>

<script>
Swal.fire({
title:'Succès !',
text:'La photo de fond a été modifiée.',
icon:'success',
timer:1500,
showConfirmButton:false
});

if (window.history.replaceState){
const url = new URL(window.location);
url.searchParams.delete('ok');
window.history.replaceState({},document.title,url);
}
</script>

<?php } ?>


<!-- =====================================================
IMAGE COEUR + TEXTE SAVE THE DATE
===================================================== -->

<?php

if (isset($_POST['submitimgcoeur'])) {

$textsdd = $_POST['text_sdd'] ?? '';
$uploadedSaveDateImage = '';

if(isset($_FILES['photo2']) && $_FILES['photo2']['error'] !== UPLOAD_ERR_NO_FILE){
    try {
        $fileName = EventMediaService::storeUploadedImage($_FILES['photo2'], '../../couple/images');
        EventMediaService::updateEventFields($pdo, (int) $cod_event, ['photo' => $fileName]);
        $uploadedSaveDateImage = $fileName;
    } catch (RuntimeException $e) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}


if(!empty($textsdd)){
    EventMediaService::upsertWebsiteGeneralText($pdo, (int) $cod_event, 'text_sdd', $textsdd);

}

$saveWeddingSitePartial([
    'save_title' => $_POST['save_title'] ?? ($weddingSiteSettings['content']['save_title'] ?? 'Save the date'),
    'save_text' => $textsdd,
], $uploadedSaveDateImage !== '' ? ['save_heart' => $uploadedSaveDateImage] : []);

echo '<script>window.location="index.php?page=conf_siteweb&ok=2";</script>';

}


if (isset($_GET['ok']) && $_GET['ok']==2){
?>

<script>

Swal.fire({
title:'Succès !',
text:'Modification effectuée.',
icon:'success',
timer:1500,
showConfirmButton:false
});

if(window.history.replaceState){
const url=new URL(window.location);
url.searchParams.delete('ok');
window.history.replaceState({},document.title,url);
}

</script>

<?php } ?>



<!-- =====================================================
SECTION LOVE STORY PHOTO + TEXTE
===================================================== -->

<?php

if(isset($_POST['submit_lovestory'])){

$textlovestory = $_POST['text_lovestory'] ?? '';

$fileName1 = '';
$fileName2 = '';

/* ======================
UPLOAD PHOTO 3
====================== */

if(isset($_FILES['photo3']) && $_FILES['photo3']['error'] !== UPLOAD_ERR_NO_FILE){
    try {
        $uploadedName = EventMediaService::storeUploadedImage($_FILES['photo3'], '../../couple/images');
        if ($uploadedName !== null) {
            $fileName1 = $uploadedName;
        }
    } catch (RuntimeException $e) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }

}


/* ======================
UPLOAD PHOTO 4
====================== */

if(isset($_FILES['photo4']) && $_FILES['photo4']['error'] !== UPLOAD_ERR_NO_FILE){
    try {
        $uploadedName = EventMediaService::storeUploadedImage($_FILES['photo4'], '../../couple/images');
        if ($uploadedName !== null) {
            $fileName2 = $uploadedName;
        }
    } catch (RuntimeException $e) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }

}


/* ======================
VERIFIER SI EXISTE
====================== */
LoveStoryService::upsert($pdo, (int) $cod_event, (int) $cod_agent, (string) $textlovestory, $fileName1 ?: null, $fileName2 ?: null);

$saveWeddingSitePartial([
    'love_title' => $_POST['love_title'] ?? ($weddingSiteSettings['content']['love_title'] ?? 'Love Story'),
    'love_subtitle' => $_POST['love_subtitle'] ?? ($weddingSiteSettings['content']['love_subtitle'] ?? ''),
    'love_end_title' => $_POST['love_end_title'] ?? ($weddingSiteSettings['content']['love_end_title'] ?? ''),
    'love_end_subtitle' => $_POST['love_end_subtitle'] ?? ($weddingSiteSettings['content']['love_end_subtitle'] ?? ''),
]);


echo '<script>window.location="index.php?page=conf_siteweb&oklove=3";</script>';

}


if(isset($_GET['oklove']) && $_GET['oklove']==3){
?>

<script>

Swal.fire({
title:'Succès !',
text:'Love Story enregistrée avec succès.',
icon:'success',
showConfirmButton:false,
timer:1500
});

if(window.history.replaceState){
const url=new URL(window.location);
url.searchParams.delete('oklove');
window.history.replaceState({},document.title,url);
}

</script>

<?php } ?>




<script>

Swal.fire({
title:'Succès !',
text:'Enregistrement effectué.',
icon:'success',
timer:1500,
showConfirmButton:false
});

if(window.history.replaceState){
const url=new URL(window.location);
url.searchParams.delete('oklove');
window.history.replaceState({},document.title,url);
}

</script>
 



<!-- =====================================================
ETAPE LOVE STORY ETAPES
===================================================== -->

<?php

if(isset($_POST['submitetaplove'])){
LoveStoryService::addStep(
    $pdo,
    (int) $cod_event,
    (int) $cod_agent,
    (string) ($_POST['etap'] ?? ''),
    (string) ($_POST['dateetap'] ?? '')
);

echo "<script>window.location.replace('".$_SERVER['REQUEST_URI']."&success=1');</script>";
exit;

}


if(isset($_GET['success'])){
?>

<script>

Swal.fire({
icon:'success',
title:'Étape ajoutée',
text:'Love story enregistrée',
timer:2000,
showConfirmButton:false
});

</script>

<?php } ?>