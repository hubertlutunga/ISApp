<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$datasession = UserAccountService::currentSessionUser($pdo) ?? [];
$typeUser = (string) ($datasession['type_user'] ?? '');
$headerFile = $typeUser === '1' ? 'header_admin.php' : 'header.php';
?>

<div class="wrapper"> 

    <?php include($headerFile); ?>
    <?php include('modeedition.php'); ?>

    <?php 
    // ---------------- Sécurisation & chargement de l'événement ----------------
    $cod_getevent = isset($_GET['cod']) ? (int)$_GET['cod'] : 0;
    if ($cod_getevent <= 0) { die('Événement invalide'); }

    $editContext = EventUpdateService::buildEditContext($pdo, $cod_getevent);
    $datagetevent = $editContext['event'];
    $type_event = $editContext['type_event'];
    $data_evenementget = $editContext['event_label'];
    $allowedRedirectPages = [
        'admin_accueil',
        'admin_filcom',
        'admin_filcomcrea',
        'crea_accueil',
        'searchevent',
        'mb_accueil',
        'admin_event',
        'events',
    ];

    $successRedirectPage = $_GET['ret'] ?? null;
    if ($successRedirectPage === null && !empty($_SERVER['HTTP_REFERER'])) {
        $refererQuery = parse_url((string) $_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
        if (is_string($refererQuery)) {
            parse_str($refererQuery, $refererParams);
            $successRedirectPage = $refererParams['page'] ?? null;
        }
    }

    if ($typeUser === '1') {
        $successRedirectPage = 'admin_accueil';
    } elseif (!is_string($successRedirectPage) || !in_array($successRedirectPage, $allowedRedirectPages, true)) {
        $successRedirectPage = 'mb_accueil';
    }

    // Pour éviter un "undefined" si utilisé en inline style
    $displayact = $displayact ?? '';
    ?>

    <style>
    .mb-eventedit-page{ padding:24px 0 42px; }
    .mb-eventedit-hero{ padding:28px 30px; border-radius:30px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 55%,#2563eb 100%); box-shadow:0 24px 50px rgba(15,23,42,.18); color:#f8fafc; margin-bottom:26px; }
    .mb-eventedit-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; }
    .mb-eventedit-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
    .mb-eventedit-copy{ margin:0; max-width:760px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
    .mb-eventedit-card{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.10); }
    .mb-eventedit-card .content-top-agile{ padding:26px 28px 10px !important; }
    .mb-eventedit-card .p-40{ padding:18px 28px 30px !important; }
    .mb-eventedit-heading{ margin:0; font-size:28px; font-weight:800; color:#0f172a; }
    .mb-eventedit-subcopy{ margin:8px 0 0; font-size:14px; color:#64748b; }
    .mb-eventedit-card .form-group{ margin-bottom:16px; }
    .mb-eventedit-card .input-group{ border:1px solid #dbeafe; border-radius:18px; background:#f8fbff; overflow:hidden; }
    .mb-eventedit-card .input-group-text,
    .mb-eventedit-card .form-control{ border:0 !important; background:transparent !important; box-shadow:none !important; min-height:56px; }
    .mb-eventedit-card .input-group-text{ color:#2563eb; padding-left:16px; padding-right:8px; }
    .mb-eventedit-submit{ display:inline-flex; align-items:center; justify-content:center; min-height:58px; border:0; border-radius:18px; background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); box-shadow:0 18px 34px rgba(37,99,235,.20); font-size:15px; font-weight:800; }
    .mb-eventedit-section-title{ margin:26px 0 12px; font-size:24px; font-weight:800; color:#0f172a; }
    @media only screen and (max-width: 769px) {
      .mb-eventedit-page{ padding:18px 0 34px; }
      .mb-eventedit-hero{ padding:22px 20px; border-radius:24px; }
      .mb-eventedit-title{ font-size:28px; }
      .mb-eventedit-card .content-top-agile,
      .mb-eventedit-card .p-40{ padding-left:20px !important; padding-right:20px !important; }
    }
    </style>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="container-full">
            <!-- Main content -->
            <div class="container h-p100 mb-eventedit-page">
                <div class="mb-eventedit-hero">
                    <span class="mb-eventedit-kicker"><i class="mdi mdi-calendar-edit"></i> Détail de l'événement</span>
                    <h1 class="mb-eventedit-title">Modifiez les informations de l'événement sans casser sa cohérence</h1>
                    <p class="mb-eventedit-copy">Ajustez la date, le lieu, les noms et les précisions depuis une page plus lisible, tout en gardant le même ton visuel que le dashboard.</p>
                </div>
                <div class="row align-items-center justify-content-md-center h-p100">
                    <div class="col-12">
                        <div class="row justify-content-center g-4">
                            <div class="col-xl-6 col-lg-7 col-12 boxcontent">
                                <div class="bg-white rounded10 shadow-lg mb-eventedit-card">
                                    <div class="content-top-agile p-20 pb-0"> 
                                        <p class="mb-0 text-fade"><?php echo $title ?? '';?></p>							
                                        <h2 class="mb-eventedit-heading">Informations principales</h2>
                                        <p class="mb-eventedit-subcopy">Mettez à jour les données essentielles de l'événement avant de gérer les médias et les commandes.</p>
                                    </div>
                                    <div class="p-40">

<?php
// --------------- TRAITEMENTS PHP -----------------

if (isset($_POST['submittext'])) { 
    try {
        EventUpdateService::updateFromRequest($pdo, $cod_getevent, $_POST);

        echo '<script>
                Swal.fire({
                    title: "Événement modifié",
                    text: "Les informations de votre événement ont été mises à jour avec succès.",
                    icon: "success",
                    confirmButtonText: "Terminer"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "index.php?page=' . htmlspecialchars($successRedirectPage, ENT_QUOTES, 'UTF-8') . '";
                    }
                });
              </script>';
    } catch (PDOException $e) {
        echo 'Erreur lors de la mise à jour : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['submittext'])) { 
    // Gérer l'upload des fichiers (form #eventForm)

    $photos = $_FILES['photos'] ?? null;

    EventMediaService::storeEventPhotos($pdo, $cod_getevent, $photos, '../photosevent', '');

    error_log('Données reçues : ' . print_r($_POST, true));
}
?>

<!-- barre de progression -->
<style>
.centered {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
</style>

<div id="progressWrapper" class="centered" style="display: none;">
    <div id="progressContainer" style="width: 100%; max-width: 600px; background: #f3f3f3; border: 1px solid #ccc; text-align: center;">
        <div id="progressBar" style="width: 0; height: 30px; background: #4caf50; display: inline-block;"></div>
        <span id="progressPercentage" style="display: block; margin-top: 5px;">Téléchargement des photos : 0%</span>
    </div>
    <div id="status" style="margin-top: 10px; text-align: center;"></div>
</div>
<!-- fin barre -->

<form id="eventForms" action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-calendar-alt"></i></span>
            <select class="form-control ps-15 bg-transparent" name="event" id="eventType">
                <option style="color:#eee;" value="<?php echo htmlspecialchars($type_event); ?>">
                    <?php echo htmlspecialchars($data_evenementget); ?>
                </option>
            </select>
        </div>
    </div>
                
    <div class="form-group hidden" id="weddingTypeGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-ring"></i></span>
            <select class="form-control ps-15 bg-transparent" name="weddingType" id="weddingType">
                <option style="color:#eee;" value="<?php echo htmlspecialchars($datagetevent['type_mar'] ?? ''); ?>">
                    <?php echo htmlspecialchars($datagetevent['type_mar'] ?? ''); ?>
                </option>
                <option value="religieux">Religieux</option>
                <option value="coutumier">Coutumier</option>
                <option value="civil">Civil</option>
            </select>
        </div>
    </div>

    <div class="input-group date" style="margin-top:15px;">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
            <input type="datetime-local" id="dateHeure" name="dateHeure" value="<?php echo htmlspecialchars($datagetevent['date_event'] ?? ''); ?>" class="form-control ps-15 bg-transparent">
        </div>
    </div>

    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-map-marker-alt"></i></span>
            <input type="text" name="lieu" value="<?php echo htmlspecialchars($datagetevent['lieu'] ?? ''); ?>" class="form-control ps-15 bg-transparent" placeholder="Salle / Espace">
        </div>
    </div>
    
    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-map"></i></span>
            <input type="text" name="adresse" value="<?php echo htmlspecialchars($datagetevent['adresse'] ?? ''); ?>" class="form-control ps-15 bg-transparent" placeholder="Adresse">
        </div>
    </div>

    <div class="form-group hidden" id="NomsAnniv">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="nomsfetard" value="<?php echo htmlspecialchars($datagetevent['nomfetard'] ?? ''); ?>" class="form-control ps-15 bg-transparent" placeholder="Noms du fetard">
        </div>
    </div>
    
    <div class="form-group hidden" id="ThemeConf">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-comments"></i></span>
            <input type="text" name="themeConf" value="<?php echo htmlspecialchars($datagetevent['themeconf'] ?? ''); ?>" class="form-control ps-15 bg-transparent" placeholder="Thème de conférence">
        </div>
    </div>

    <div class="form-group hidden" id="NomepouxGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="prenomEpoux" value="<?php echo htmlspecialchars($datagetevent['prenom_epoux'] ?? ''); ?>" class="form-control ps-15 bg-transparent" placeholder="Prénom de l'époux">
        </div>
    </div>

    <div class="form-group hidden" id="PrenomepouxGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="nomEpoux" value="<?php echo htmlspecialchars($datagetevent['nom_epoux'] ?? ''); ?>" class="form-control ps-15 bg-transparent" placeholder="Nom de l'époux">
        </div>
    </div>

    <div class="form-group hidden" id="NomepouseGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="prenomEpouse" value="<?php echo htmlspecialchars($datagetevent['prenom_epouse'] ?? ''); ?>" class="form-control ps-15 bg-transparent" placeholder="Prénom de l'épouse">
        </div>
    </div>

    <div class="form-group hidden" id="PrenomepouseGroup">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
            <input type="text" name="nomEpouse" value="<?php echo htmlspecialchars($datagetevent['nom_epouse'] ?? ''); ?>" class="form-control ps-15 bg-transparent" placeholder="Nom de l'épouse">
        </div>
    </div>

    <div class="form-group">
        <div class="input-group mb-3">
            <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span> 
            <textarea name="details" class="form-control ps-15 bg-transparent" rows='5' placeholder="Autres précisions"><?php echo htmlspecialchars($datagetevent['autres_precisions'] ?? ''); ?></textarea>
        </div>
    </div>

    <div class="row"> 
        <div class="col-12 text-center">
            <button type="submit" name="submittext" class="btn btn-primary w-p100 mt-10 mb-eventedit-submit">Enregistrer les modifications</button>
        </div>
    </div>
</form>

<style>
.image-container { position: relative; margin: 5px; }
.image-container img { width: 100px; height: 100px; object-fit: cover; }
.delete-icon {
    position: absolute; top: 0; right: 0;
    background-color: rgba(255, 0, 0, 0.7); color: white; border: none; cursor: pointer;
}

.photo-wrapper { position: relative; display: inline-block; }
.square-img { width: 100px; height: 100px; object-fit: cover; margin: 10px; }
.square-img-qr { width: 100px; height: 100px; object-fit: cover; margin: 10px; border:1px solid #000 !important; }
.delete-photoX {
    position: absolute; top: 2px; right: 2px;
    background-color: rgba(255, 0, 0, 0.8); border: none; color: white; border-radius: 50%;
    width: 22px; height: 22px; cursor: pointer; font-size: 14px; line-height: 20px; text-align: center;
}
</style>

<br><br>
<h1 class="mb-eventedit-section-title">Galerie de l'événement</h1>
<hr>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({ title: "Supprimé !", text: "La photo a bien été supprimée.", icon: "success", timer: 2000, showConfirmButton: false });
    setTimeout(function () {
        history.replaceState(null, null, window.location.pathname + window.location.search.replace(/&?deleted=1/, ''));
    }, 2000);
});
</script>
<?php endif; ?>

<?php 
$stmtimg = $pdo->prepare("SELECT * FROM photos_event WHERE cod_event = ? ORDER BY cod_photo DESC");
$stmtimg->execute([$cod_getevent]); 
while ($dataphoto = $stmtimg->fetch(PDO::FETCH_ASSOC)) { 
?>
    <div class="photo-wrapper" data-photo="<?php echo (int)$dataphoto['cod_photo']; ?>">
        <img src="../photosevent/<?php echo htmlspecialchars($dataphoto['nom_photo']); ?>" alt="<?php echo htmlspecialchars($dataphoto['nom_photo']); ?>" class="square-img">
        <button style="<?php echo $displayact; ?>" class="delete-photoX" onclick="confirmSuppEvent(event, '<?php echo htmlspecialchars($dataphoto['cod_photo']); ?>', '<?php echo htmlspecialchars($cod_getevent); ?>')">✖</button>
    </div>
<?php } ?>

<script>
function confirmSuppEvent(event, codPhoto, codGetevent) {
    event.preventDefault();
    Swal.fire({
        title: "Supprimer !",
        text: "Êtes-vous sûr de vouloir supprimer cette photo ?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Non"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "index.php?page=supphoto&cod=" + codPhoto + "&codevent=" + codGetevent;
        }
    });
}
</script>

<form id="eventForm" action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <div class="input-group mb-3">
            <label for="fileInput" class="btnpic"><i class="fas fa-plus"></i> Importer les photos</label>
            <input type="file" name="photos[]" required class="form-control ps-15 bg-transparent" accept="image/*" id="fileInput" multiple style="display: none;">
            <div id="previewContainer" class="mt-2" style="display: flex; flex-wrap: wrap;"></div>
        </div>
    </div>
    <div class="row"> 
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-warning w-p100 mt-10">Ajouter les photos</button>
        </div>
    </div>
</form>

<br><br>

<h1 class="mb-eventedit-section-title">Commandes associées</h1>
<hr>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 2): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({ title: "Supprimé !", text: "La commande a bien été supprimée.", icon: "success", timer: 2000, showConfirmButton: false });
    setTimeout(function () {
        history.replaceState(null, null, window.location.pathname + window.location.search.replace(/&?deleted=1/, ''));
    }, 2000);
});
</script>
<?php endif; ?>

<?php 
$stmtae = $pdo->prepare("SELECT * FROM accessoires_event where cod_event = ? ORDER BY cod_accev DESC");
$stmtae->execute([$cod_getevent]); 

while ($dataae = $stmtae->fetch(PDO::FETCH_ASSOC)) {

    // nom de l’accessoire
    $stmtnv = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
    $stmtnv->execute([$dataae['cod_acc']]);
    $data_accessoire = $stmtnv->fetch();

    // détails fact (optionnel)
    $stmtdetailfact = $pdo->prepare("SELECT * FROM details_fact where cod_event = ? AND libelle = ?");
    $stmtdetailfact->execute([$cod_getevent, $data_accessoire['nom'] ?? '']); 
    $row_detailfact = $stmtdetailfact->fetch(PDO::FETCH_ASSOC);

    $accessoire = isset($data_accessoire['nom']) ? $data_accessoire['nom'] : '';

    // Si accessoire = invitation (1) on affiche le modèle choisi
    if ($dataae['cod_acc'] == "1") {
        $codmodeleinv = $dataae['modele_acc'] ?? '';
        $stmtmi = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
        $stmtmi->execute([$codmodeleinv]);
        $data_modele = $stmtmi->fetch();

        $modele_inv = isset($data_modele['nom']) ? '('.$data_modele['nom'].')' : '';
        $image_inv  = isset($data_modele['image']) ? $data_modele['image'] : '';

    } elseif ($dataae['cod_acc'] == "3") {
        // Chevalet : utilise modele_chev stocké sur events
        $stmtmc = $pdo->prepare("SELECT * FROM modele_is WHERE cod_mod = ?");
        $stmtmc->execute([$datagetevent['modele_chev'] ?? null]); // corrigé: $datagetevent
        $data_modelechev = $stmtmc->fetch();

        $modele_inv = isset($data_modelechev['nom']) ? '('.$data_modelechev['nom'].')' : '';
        $image_inv  = '';

    } else {
        $modele_inv = '';
        $image_inv  = '';
    }
    ?>
    <br>

    <style>
        .hoverx-image { display: none; position: absolute; z-index: 9000; max-width: 200px; border: 1px solid #ccc; background-color: white; padding: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .hoverx-container { position: relative; }
    </style> 

    <em style="margin-bottom:25px; display:block; clear:both;">
        <?php 
        echo '<span class="hoverx-container" style="float:left;">' . htmlspecialchars($accessoire) . 
             ' <a target="_blank" href="https://invitationspeciale.com/event/images/modeleis/' . htmlspecialchars($image_inv) . 
             '" class="modelx-link">' . htmlspecialchars($modele_inv) . '</a></span>';
        ?>
        <span style="float:right">
            <a href="#" style="color:red;<?php echo $displayact; ?>" onclick="confirmSuppAcc(event, '<?php echo htmlspecialchars($dataae['cod_accev']); ?>' , '<?php echo htmlspecialchars($cod_getevent); ?>')">Supprimer</a>
        </span>
    </em>

<?php } // fin while accessoires ?>
<script>
function confirmSuppAcc(event, codAccev, codGetevent) {
    event.preventDefault();
    Swal.fire({
        title: "Supprimer !",
        text: "Êtes-vous sûr de vouloir supprimer cette commande ?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Non"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "index.php?page=supcom&cod=" + codAccev + "&codevent=" + codGetevent;
        }
    });
}
</script>

<br><br>

<div class="row"> 
    <div class="col-12 text-center">
        <a href="#" onclick="openModal('<?php echo htmlspecialchars(ucfirst($cod_getevent)); ?>')" class="btn btn-success w-p100 mt-10">Ajouter les accessoires</a>
    </div>
</div>

<!-- Styles de la modale -->
<style>
.modalinv { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 77000; }
.modal-content { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); position: relative; width: 50vw; max-width: 50%; margin: auto; }
.close { position: absolute; top: 10px; right: 15px; color: #aaa; font-size: 24px; }
.close:hover { color: #000; }

@media only screen and (max-width: 769px) {
    .modal-content { width: 95vw; max-width: 95%; }
}
@media only screen and (min-width: 770px) and (max-width: 1024px) {
    .modal-content { width: 60vw; max-width: 60%; }
}
</style>

<!-- Fenêtre modale -->
<div id="shareModal" class="modalinv" style="display: none;">
    <div class="modal-content"> 
        <?php  
        if (isset($_POST['submiacces'])) {
            $accessoires = $_POST['accessoires'] ?? []; 
            $codevent = $_POST['codgetevent'] ?? null;
            $modeleInv = $_POST['modele_inv'] ?? null;

            if ($codevent) {
                foreach ($accessoires as $accessoire) {
                    $sqlAccessoire = "INSERT INTO accessoires_event (cod_event, cod_acc, modele_acc) VALUES (?, ?, ?)";
                    $stmtAccessoire = $pdo->prepare($sqlAccessoire);
                    $stmtAccessoire->execute([$codevent, $accessoire, $modeleInv]);
                }
            }

            echo '<script>
                Swal.fire({
                    title: "Evénement modifié !",
                    text: "Accessoires modifiés avec succès.",
                    icon: "success",
                    confirmButtonText: "Terminer"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "index.php?page=modevent&cod='.$cod_getevent.'";
                    }
                });
            </script>';
        }
        ?>

        <div class="form-group">  
            <span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
            <h4 id="modalTitle">Ajouter une commande</h4><br>

            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" id="codgetevent" name="codgetevent" />

                <!-- Groupe Accessoires -->
                <fieldset class="border p-3" id="AccessoireGroup">
                    <div class="form-group">
                        <?php 
                        $reqmod = $pdo->prepare("SELECT * FROM modele_is where type_mod = :type_mod ORDER by cod_mod ASC");
                        $reqmod->execute([':type_mod' => 'accessoires']);  
                        while ($data_mod = $reqmod->fetch()) {
                        ?>
                        <div class="checkbox" style="margin-bottom:12px; display: flex; align-items: center;">
                            <input 
                                type="checkbox" 
                                id="<?php echo (int)$data_mod['cod_mod']; ?>" 
                                name="accessoires[]" 
                                value="<?php echo (int)$data_mod['cod_mod']; ?>" 
                                style="margin-right:12px;" 
                                onchange="toggleFields()"
                            >
                            <label 
                                for="<?php echo (int)$data_mod['cod_mod']; ?>" 
                                style="background-color: #fcfcfc; text-indent:20px; padding: 8px 12px; border-radius: 5px; width: 100%; display: flex; align-items: center;"
                            >
                                <?php echo htmlspecialchars($data_mod['nom']); ?>
                            </label>
                        </div>
                        <?php } ?> 
                    </div>
                </fieldset>

                <div class="form-group" id="ModInvitation" style="display:none;margin-top:20px;">
                    <div class="input-group mb-3 champmod" id="dropdownToggle">
                        <span class="input-group-text bg-transparent spanmod"><i class="fas fa-ring"></i></span>
                        <div class="selected-option" style="margin-left:15px;margin-top:6px;">Modèle d'invitation</div>
                    </div>
                </div>

                <div id="myModal" class="modal" style="display:none;">
                    <div class="modal-content" style="max-width:700px;">
                        <span id="closeModal" style="cursor:pointer; float:right;">&times;</span>
                        <h2>Sélectionnez un modèle</h2>
                        <div class="dropdown-content" id="weddingTypeDropdown">
                            <input type="hidden" name="modele_inv" id="modeleInv" value="">
                            <?php 
                            $reqmod = $pdo->prepare("SELECT * FROM modele_is where type_mod = :type_mod ORDER by nom ASC");
                            $reqmod->execute([':type_mod' => 'invitation']);  
                            while ($data_mod = $reqmod->fetch()) {
                            ?>
                            <div data-value="<?php echo (int)$data_mod['cod_mod']; ?>">
                                <label><?php echo htmlspecialchars($data_mod['nom']); ?>
                                    <img class="option-image" src="../images/modeleis/<?php echo htmlspecialchars($data_mod['image']); ?>" alt="<?php echo htmlspecialchars($data_mod['nom']); ?>">  
                                </label>
                            </div>
                            <?php } ?>  
                        </div>
                    </div>
                </div>

                <div class="form-group" id="ModChevalet" style="display: none;">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-transparent"><i class="fas fa-gift"></i></span>
                        <select class="form-control ps-15 bg-transparent" name="chevaletModel">
                            <option style="color:#eee;" value="">Modèle de chevalet de table</option>
                            <?php 
                            $reqmod = $pdo->prepare("SELECT * FROM modele_is where type_mod = :type_mod ORDER by nom ASC");
                            $reqmod->execute([':type_mod' => 'chevalet']);  
                            while ($data_mod = $reqmod->fetch()) {
                            ?>
                            <option value="<?php echo (int)$data_mod['cod_mod']; ?>" <?php if(@$_POST['chevaletModel'] == $data_mod['cod_mod']){echo "selected";} ?>>
                                <?php echo htmlspecialchars($data_mod['nom']); ?>
                            </option>
                            <?php } ?>  
                        </select>
                    </div>
                </div>

                <button class="btn btn-success" type="submit" name="submiacces" style="border-radius:0px 0px 7px 7px;width:100%;">Ajouter une commande</button>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(codEvent) {
    document.getElementById("shareModal").style.display = "flex";
    document.getElementById("codgetevent").value = codEvent;
    document.getElementById("modalTitle").innerText = "Ajouter une commande";
}
function closeModal() {
    document.getElementById("shareModal").style.display = "none";
}
</script>

<!---------------- script pour afficher les modèles d'invitation ou chevalet si coché --------------->
<script>
function toggleFields() {
    const checkboxes = document.querySelectorAll('input[name="accessoires[]"]');
    let showInvitation = false;
    let showChevalet = false;

    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            if (checkbox.value == 1) showInvitation = true;
            else if (checkbox.value == 3) showChevalet = true;
        }
    });

    const inv = document.getElementById('ModInvitation');
    const chev = document.getElementById('ModChevalet');
    if (inv) inv.style.display = showInvitation ? 'block' : 'none';
    if (chev) chev.style.display = showChevalet ? 'block' : 'none';
}
</script>

<!---------------- script de la barre de progression --------------->
<script>
document.getElementById('eventForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var typeEvent = document.getElementById('eventType').value;
    if (!typeEvent) {
        alert("Veuillez sélectionner le type de l'événement.");
        return;
    }

    if (typeEvent === '1') {
        var typeMar = document.getElementById('weddingType').value;
        var dateHeure = document.getElementById('dateHeure').value;
        var lieu = document.getElementsByName('lieu')[0].value;
        var adresse = document.getElementsByName('adresse')[0].value;
        var prenomEpoux = document.getElementsByName('prenomEpoux')[0].value;
        var prenomEpouse = document.getElementsByName('prenomEpouse')[0].value;

        if (!typeMar) { alert("Veuillez sélectionner le type du mariage."); return; }
        if (!dateHeure) { alert("Veuillez entrer la date et l'heure du mariage."); return; }
        if (!lieu) { alert("Veuillez entrer le lieu du mariage."); return; }
        if (!adresse) { alert("Veuillez entrer l'adresse du mariage."); return; }
        if (!prenomEpoux) { alert("Veuillez entrer le prénom de l'époux."); return; }
        if (!prenomEpouse) { alert("Veuillez entrer le prénom de l'épouse."); return; }

    } else if (typeEvent === '2') {
        var dateHeure = document.getElementById('dateHeure').value;
        var lieu = document.getElementsByName('lieu')[0].value;
        var adresse = document.getElementsByName('adresse')[0].value;
        var nomsAnniv = document.getElementsByName('nomsfetard')[0].value;

        if (!dateHeure) { alert("Veuillez entrer la date et l'heure."); return; }
        if (!lieu) { alert("Veuillez entrer le lieu."); return; }
        if (!adresse) { alert("Veuillez entrer l'adresse."); return; }
        if (!nomsAnniv) { alert("Veuillez entrer le nom de celui ou celle qui fête son anniversaire."); return; }

    } else if (typeEvent === '3') {
        var dateHeure = document.getElementById('dateHeure').value;
        var lieu = document.getElementsByName('lieu')[0].value;
        var adresse = document.getElementsByName('adresse')[0].value;
        var themeConf = document.getElementsByName('themeConf')[0].value;

        if (!dateHeure) { alert("Veuillez entrer la date et l'heure."); return; }
        if (!lieu) { alert("Veuillez entrer le lieu."); return; }
        if (!adresse) { alert("Veuillez entrer l'adresse."); return; }
        if (!themeConf) { alert("Veuillez entrer le thème de la conférence."); return; }
    }

    // UI progression
    this.style.display = 'none';
    var progressWrapper = document.getElementById('progressWrapper');
    progressWrapper.style.display = 'flex';
    progressWrapper.classList.add('centered');
    document.getElementById('progressContainer').style.display = 'block';
    document.getElementById('progressBar').style.width = '0%';

    var formData = new FormData(this);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);

    xhr.upload.onprogress = function(event) {
        if (event.lengthComputable) {
            var percentComplete = (event.loaded / event.total) * 100;
            document.getElementById('progressBar').style.width = percentComplete + '%';
            document.getElementById('progressPercentage').textContent = 'Téléchargement des photos : ' + Math.round(percentComplete) + '%';
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            Swal.fire({
                title: "Evénement modifié !",
                text: "Votre événement est modifié avec succès.",
                icon: "success",
                confirmButtonText: "Terminer"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "index.php?page=<?php echo $successRedirectPage; ?>";
                }
            });
        } else {
            document.getElementById('status').innerHTML = 'Erreur lors du traitement.';
        }
    };

    xhr.send(formData);
});
</script>

<!---------------- script pour la liste des modeles d'invitations --------------->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const dropdownToggle = document.getElementById('dropdownToggle');
    const modal = document.getElementById('myModal');
    const closeModal = document.getElementById('closeModal');
    const dropdownContent = document.getElementById('weddingTypeDropdown');
    const selectedOption = document.querySelector('.selected-option');

    if (!dropdownToggle || !modal || !closeModal || !dropdownContent || !selectedOption) return;

    dropdownToggle.addEventListener('click', function () {
        modal.style.display = 'block';
    });

    closeModal.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    dropdownContent.addEventListener('click', function (event) {
        const container = event.target.closest('div[data-value]');
        if (container) {
            const value = container.dataset.value;
            document.getElementById("modeleInv").value = value; // set hidden input
            selectedOption.textContent = container.textContent.trim();
            modal.style.display = 'none';
            console.log(value);
        }
    });

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Masquage/Affichage sections selon type_event initial
    const eventTypeSelect = document.getElementById('eventType');
    const weddingTypeGroup = document.getElementById('weddingTypeGroup');
    const NomepouxGroup = document.getElementById('NomepouxGroup');
    const PrenomepouxGroup = document.getElementById('PrenomepouxGroup');
    const NomepouseGroup = document.getElementById('NomepouseGroup'); 
    const PrenomepouseGroup = document.getElementById('PrenomepouseGroup'); 
    const AccessoireGroup = document.getElementById('AccessoireGroup');
    const NomfamilleepouseGroup = document.getElementById('NomfamilleepouseGroup');
    const NomfamilleepouxGroup = document.getElementById('NomfamilleepouxGroup');
    const NomsAnniv = document.getElementById('NomsAnniv');
    const ThemeConf = document.getElementById('ThemeConf');

    function updateVisibility() {
        const val = eventTypeSelect ? eventTypeSelect.value : '';

        const show = (el, on) => { if (el) el.classList[on ? 'remove' : 'add']('hidden'); }

        if (val === '1') { // Mariage
            show(weddingTypeGroup, true);
            show(NomepouxGroup, true);
            show(PrenomepouxGroup, true);
            show(NomepouseGroup, true);
            show(PrenomepouseGroup, true);
            show(AccessoireGroup, true);
            show(NomfamilleepouseGroup, true);
            show(NomfamilleepouxGroup, true);
            show(ThemeConf, false);
            show(NomsAnniv, false);

        } else if (val === '2') { // Anniversaire
            show(NomsAnniv, true);
            show(weddingTypeGroup, false);
            show(NomepouxGroup, false);
            show(PrenomepouxGroup, false);
            show(NomepouseGroup, false);
            show(PrenomepouseGroup, false);
            show(AccessoireGroup, false);
            show(NomfamilleepouseGroup, false);
            show(NomfamilleepouxGroup, false);
            show(ThemeConf, false);

        } else if (val === '3') { // Conférence
            show(ThemeConf, true);
            show(weddingTypeGroup, false);
            show(NomepouxGroup, false);
            show(PrenomepouxGroup, false);
            show(NomepouseGroup, false);
            show(PrenomepouseGroup, false);
            show(AccessoireGroup, false);
            show(NomfamilleepouseGroup, false);
            show(NomfamilleepouxGroup, false);
            show(NomsAnniv, false);

        } else {
            // Masquer tous
            [weddingTypeGroup, NomepouxGroup, PrenomepouxGroup, NomepouseGroup, PrenomepouseGroup,
             AccessoireGroup, NomfamilleepouseGroup, NomfamilleepouxGroup, NomsAnniv, ThemeConf].forEach(el => {
                if (el) el.classList.add('hidden');
            });
        }
    }

    updateVisibility();
    if (eventTypeSelect) eventTypeSelect.addEventListener('change', updateVisibility);

    // Aperçu images
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');

    if (fileInput && previewContainer) {
        fileInput.addEventListener('change', function (event) {
            previewContainer.innerHTML = '';
            const files = Array.from(event.target.files);

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const imgContainer = document.createElement('div');
                    imgContainer.classList.add('image-container');

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.margin = '5px';

                    const deleteIcon = document.createElement('button');
                    deleteIcon.textContent = '✖';
                    deleteIcon.className = 'delete-icon';
                    deleteIcon.style.margin = '5px';

                    deleteIcon.addEventListener('click', () => {
                        imgContainer.remove();
                    });

                    imgContainer.appendChild(img);
                    imgContainer.appendChild(deleteIcon);
                    previewContainer.appendChild(imgContainer);
                };
                reader.readAsDataURL(file);
            });
        });
    }
});
</script>

                                    </div>
                                </div>	
                            </div>
                        </div>
                    </div>			
                </div>
            </div>

        </div>			
    </div>

    <!-- /.content-wrapper -->
    <?php include('footer.php')?>
    <!-- scripts -->
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

</div>
