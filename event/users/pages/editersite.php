<?php

$cod_event  = $codevent;
$cod_agent  = $datasession['cod_user'];


/* =====================================================
UPLOAD IMAGE FOND STORY
===================================================== */

if (isset($_POST['submitimgback'])) {
    try {
        $fileName = EventMediaService::storeUploadedImage($_FILES['photo1'] ?? [], '../../couple/images');
        EventMediaService::updateEventFields($pdo, (int) $cod_event, ['photostory' => $fileName]);

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

if(isset($_FILES['photo2']) && $_FILES['photo2']['error'] !== UPLOAD_ERR_NO_FILE){
    try {
        $fileName = EventMediaService::storeUploadedImage($_FILES['photo2'], '../../couple/images');
        EventMediaService::updateEventFields($pdo, (int) $cod_event, ['photo' => $fileName]);
    } catch (RuntimeException $e) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}


if(!empty($textsdd)){
    EventMediaService::upsertWebsiteGeneralText($pdo, (int) $cod_event, 'text_sdd', $textsdd);

}

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