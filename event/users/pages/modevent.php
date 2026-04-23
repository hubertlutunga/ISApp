<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$datasession = UserAccountService::currentSessionUser($pdo) ?? [];
$typeUser = (string) ($datasession['type_user'] ?? '');
$headerFile = $typeUser === '1' ? 'header_admin.php' : 'header.php';

$cod_getevent = isset($_GET['cod']) ? (int) $_GET['cod'] : 0;
if ($cod_getevent <= 0) {
    die('Evenement invalide');
}

EventOrderService::ensureCatalogInfrastructure($pdo);

$editContext = EventUpdateService::buildEditContext($pdo, $cod_getevent);
$datagetevent = $editContext['event'];
$type_event = $editContext['type_event'];
$data_evenementget = $editContext['event_label'];
$invitationModels = EventOrderService::loadInvitationModelsByEvent($pdo, $cod_getevent);
$checkoutData = EventOrderService::loadCheckoutByEvent($pdo, $cod_getevent);
$paymentTypeLabel = EventOrderService::paymentLabel($checkoutData['type_paiement'] ?? null);

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

$displayact = $displayact ?? '';
$pageError = null;
$postSuccessScript = null;

if (isset($_POST['submittext'])) {
    try {
        EventUpdateService::updateFromRequest($pdo, $cod_getevent, $_POST);
        $postSuccessScript = '<script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    title: "Evenement modifie",
                    text: "Les informations de votre evenement ont ete mises a jour avec succes.",
                    icon: "success",
                    confirmButtonText: "Terminer"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "index.php?page=' . htmlspecialchars($successRedirectPage, ENT_QUOTES, 'UTF-8') . '";
                    }
                });
            });
        </script>';
        $editContext = EventUpdateService::buildEditContext($pdo, $cod_getevent);
        $datagetevent = $editContext['event'];
        $type_event = $editContext['type_event'];
        $data_evenementget = $editContext['event_label'];
    } catch (Throwable $exception) {
        $pageError = $exception->getMessage();
    }
}

if (isset($_POST['submiacces'])) {
    try {
        $postedAccessories = array_map('intval', (array) ($_POST['accessoires'] ?? []));
        $selectedInvitationModel = (int) ($_POST['modele_inv'] ?? 0);
        $selectedChevaletModel = (int) ($_POST['chevaletModel'] ?? 0);

        foreach ($postedAccessories as $accessoryId) {
            if ($accessoryId <= 0) {
                continue;
            }

            $linkedModelId = null;
            if ($accessoryId === 1 && $selectedInvitationModel > 0) {
                $linkedModelId = $selectedInvitationModel;
            }
            if ($accessoryId === 3 && $selectedChevaletModel > 0) {
                $linkedModelId = $selectedChevaletModel;
            }

            $stmtAccessoire = $pdo->prepare('INSERT INTO accessoires_event (cod_event, cod_acc, modele_acc) VALUES (?, ?, ?)');
            $stmtAccessoire->execute([$cod_getevent, $accessoryId, $linkedModelId]);
        }

        $postSuccessScript = '<script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    title: "Commande ajoutee",
                    text: "Les accessoires ont ete ajoutes avec succes.",
                    icon: "success",
                    confirmButtonText: "Terminer"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "index.php?page=modevent&cod=' . $cod_getevent . '";
                    }
                });
            });
        </script>';
    } catch (Throwable $exception) {
        $pageError = $exception->getMessage();
    }
}

$eventTypeOptions = [];
$eventTypeStmt = $pdo->query('SELECT cod_event, nom FROM evenement ORDER BY nom ASC');
foreach ($eventTypeStmt->fetchAll(PDO::FETCH_ASSOC) as $eventTypeRow) {
    $eventTypeOptions[] = [
        'id' => (string) ($eventTypeRow['cod_event'] ?? ''),
        'label' => (string) ($eventTypeRow['nom'] ?? ''),
    ];
}

$accessoryCatalogRows = array_values(array_filter(
    EventOrderService::listCatalogModels($pdo, 'accessoires'),
    static fn(array $row): bool => ((int) ($row['is_active'] ?? 1)) === 1
));
$invitationCatalogRows = array_values(array_filter(
    EventOrderService::listCatalogModels($pdo, 'invitation'),
    static fn(array $row): bool => ((int) ($row['is_active'] ?? 1)) === 1
));
$chevaletCatalogRows = array_values(array_filter(
    EventOrderService::listCatalogModels($pdo, 'chevalet'),
    static fn(array $row): bool => ((int) ($row['is_active'] ?? 1)) === 1
));

$formatAmount = static function ($amount, string $currency = '$'): string {
    if ($amount === null || $amount === '') {
        return 'Non defini';
    }

    return number_format((float) $amount, 2, '.', ' ') . ' ' . $currency;
};

$photosStmt = $pdo->prepare('SELECT * FROM photos_event WHERE cod_event = ? ORDER BY cod_photo DESC');
$photosStmt->execute([$cod_getevent]);
$photoRows = $photosStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$commandRows = [];
$accessoryEventsStmt = $pdo->prepare('SELECT * FROM accessoires_event WHERE cod_event = ? ORDER BY cod_accev DESC');
$accessoryEventsStmt->execute([$cod_getevent]);
foreach ($accessoryEventsStmt->fetchAll(PDO::FETCH_ASSOC) as $dataae) {
    $accessoryLookupStmt = $pdo->prepare('SELECT * FROM modele_is WHERE cod_mod = ?');
    $accessoryLookupStmt->execute([(int) ($dataae['cod_acc'] ?? 0)]);
    $data_accessoire = $accessoryLookupStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $detailStmt = $pdo->prepare('SELECT * FROM details_fact WHERE cod_event = ? AND libelle = ? LIMIT 1');
    $detailStmt->execute([$cod_getevent, $data_accessoire['nom'] ?? '']);
    $detailRow = $detailStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $accessoire = trim((string) ($data_accessoire['nom'] ?? 'Accessoire'));
    $modeleLabel = '';
    $modeleImage = '';

    if ((string) ($dataae['cod_acc'] ?? '') === '1') {
        if ($invitationModels !== []) {
            $modeleLabel = implode(', ', array_map(static fn(array $model): string => (string) ($model['nom'] ?? 'Modele'), $invitationModels));
            $modeleImage = (string) ($invitationModels[0]['image'] ?? '');
        } else {
            $modelStmt = $pdo->prepare('SELECT * FROM modele_is WHERE cod_mod = ?');
            $modelStmt->execute([(int) ($dataae['modele_acc'] ?? 0)]);
            $data_modele = $modelStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $modeleLabel = trim((string) ($data_modele['nom'] ?? ''));
            $modeleImage = trim((string) ($data_modele['image'] ?? ''));
        }
    } elseif ((string) ($dataae['cod_acc'] ?? '') === '3') {
        $chevaletStmt = $pdo->prepare('SELECT * FROM modele_is WHERE cod_mod = ?');
        $chevaletStmt->execute([(int) ($datagetevent['modele_chev'] ?? ($dataae['modele_acc'] ?? 0))]);
        $chevaletModel = $chevaletStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $modeleLabel = trim((string) ($chevaletModel['nom'] ?? ''));
        $modeleImage = trim((string) ($chevaletModel['image'] ?? ''));
    }

    $commandRows[] = [
        'id' => (int) ($dataae['cod_accev'] ?? 0),
        'label' => $accessoire,
        'model_label' => $modeleLabel,
        'model_image' => $modeleImage,
        'quantity' => max(1, (int) ($dataae['quantite'] ?? 1)),
        'unit_price' => $detailRow['pu'] ?? ($data_accessoire['unit_price'] ?? null),
        'line_total' => $detailRow['pt'] ?? null,
    ];
}

$currentInvitationModelId = '';
if ($invitationModels !== []) {
    $currentInvitationModelId = (string) ($invitationModels[0]['cod_mod'] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['submittext']) && !isset($_POST['submiacces']) && isset($_FILES['photos'])) {
    try {
        EventMediaService::storeEventPhotos($pdo, $cod_getevent, $_FILES['photos']);
        http_response_code(200);
        exit;
    } catch (Throwable $exception) {
        http_response_code(500);
        echo $exception->getMessage();
        exit;
    }
}
?>

<div class="wrapper">
    <?php include($headerFile); ?>
    <?php include('modeedition.php'); ?>

    <div class="content-wrapper">
        <div class="container-full">
            <div class="container py-30 me-page">
                <section class="me-hero">
                    <div class="me-hero-copy">
                        <span class="me-kicker"><i class="mdi mdi-calendar-edit"></i> Edition complete</span>
                        <h1 class="me-title">Refonte propre de l'evenement, du contenu jusqu'aux commandes</h1>
                        <p class="me-copy">Modifiez les informations principales, la galerie photo et les commandes associees dans une interface plus claire, plus elegante et plus proche du nouveau design de creation.</p>
                    </div>

                    <div class="me-hero-stats">
                        <article class="me-stat-card">
                            <strong><?php echo htmlspecialchars((string) ($data_evenementget ?: 'Evenement'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span>Type actuel</span>
                        </article>
                        <article class="me-stat-card">
                            <strong><?php echo count($photoRows); ?></strong>
                            <span>Photos</span>
                        </article>
                        <article class="me-stat-card">
                            <strong><?php echo count($commandRows); ?></strong>
                            <span>Commandes</span>
                        </article>
                    </div>
                </section>

                <?php if ($pageError !== null) { ?>
                <div class="me-alert me-alert-error"><?php echo htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php } ?>

                <div class="me-layout">
                    <section class="me-card me-form-card">
                        <div class="me-card-head">
                            <div>
                                <span class="me-card-kicker">Informations</span>
                                <h2>Parametres generaux de l'evenement</h2>
                                <p>Conservez tous les champs utiles dans une structure plus propre et plus facile a mettre a jour.</p>
                            </div>
                        </div>

                        <form id="eventForms" action="" method="post" enctype="multipart/form-data" class="me-form-shell">
                            <div class="me-form-grid me-form-grid-two">
                                <label class="me-field">
                                    <span class="me-field-label">Type d'evenement</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-calendar-alt"></i>
                                        <select name="event" id="eventType" class="me-input" required>
                                            <?php foreach ($eventTypeOptions as $eventOption) { ?>
                                            <option value="<?php echo htmlspecialchars($eventOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $eventOption['id'] === (string) $type_event ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($eventOption['label'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </label>

                                <label class="me-field me-conditional-field" id="weddingTypeGroup">
                                    <span class="me-field-label">Type de mariage</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-ring"></i>
                                        <select name="weddingType" id="weddingType" class="me-input">
                                            <option value="">Choisir un type</option>
                                            <option value="religieux" <?php echo (($datagetevent['type_mar'] ?? '') === 'religieux') ? 'selected' : ''; ?>>Religieux</option>
                                            <option value="coutumier" <?php echo (($datagetevent['type_mar'] ?? '') === 'coutumier') ? 'selected' : ''; ?>>Coutumier</option>
                                            <option value="civil" <?php echo (($datagetevent['type_mar'] ?? '') === 'civil') ? 'selected' : ''; ?>>Civil</option>
                                        </select>
                                    </div>
                                </label>

                                <label class="me-field">
                                    <span class="me-field-label">Date et heure</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-calendar"></i>
                                        <input type="datetime-local" id="dateHeure" name="dateHeure" value="<?php echo htmlspecialchars((string) ($datagetevent['date_event'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input">
                                    </div>
                                </label>

                                <label class="me-field">
                                    <span class="me-field-label">Lieu</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <input type="text" name="lieu" value="<?php echo htmlspecialchars((string) ($datagetevent['lieu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input" placeholder="Salle / Espace">
                                    </div>
                                </label>

                                <label class="me-field me-field-full">
                                    <span class="me-field-label">Adresse</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-map"></i>
                                        <input type="text" name="adresse" value="<?php echo htmlspecialchars((string) ($datagetevent['adresse'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input" placeholder="Adresse complete">
                                    </div>
                                </label>

                                <label class="me-field me-conditional-field" id="NomsAnniv">
                                    <span class="me-field-label">Nom du fetard</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-user"></i>
                                        <input type="text" name="nomsfetard" value="<?php echo htmlspecialchars((string) ($datagetevent['nomfetard'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input" placeholder="Nom du fetard">
                                    </div>
                                </label>

                                <label class="me-field me-conditional-field" id="ThemeConf">
                                    <span class="me-field-label">Theme de conference</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-comments"></i>
                                        <input type="text" name="themeConf" value="<?php echo htmlspecialchars((string) ($datagetevent['themeconf'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input" placeholder="Theme de conference">
                                    </div>
                                </label>

                                <label class="me-field me-conditional-field" id="NomepouxGroup">
                                    <span class="me-field-label">Prenom de l'epoux</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-user"></i>
                                        <input type="text" name="prenomEpoux" value="<?php echo htmlspecialchars((string) ($datagetevent['prenom_epoux'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input" placeholder="Prenom de l'epoux">
                                    </div>
                                </label>

                                <label class="me-field me-conditional-field" id="PrenomepouxGroup">
                                    <span class="me-field-label">Nom de l'epoux</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-user"></i>
                                        <input type="text" name="nomEpoux" value="<?php echo htmlspecialchars((string) ($datagetevent['nom_epoux'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input" placeholder="Nom de l'epoux">
                                    </div>
                                </label>

                                <label class="me-field me-conditional-field" id="NomepouseGroup">
                                    <span class="me-field-label">Prenom de l'epouse</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-user"></i>
                                        <input type="text" name="prenomEpouse" value="<?php echo htmlspecialchars((string) ($datagetevent['prenom_epouse'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input" placeholder="Prenom de l'epouse">
                                    </div>
                                </label>

                                <label class="me-field me-conditional-field" id="PrenomepouseGroup">
                                    <span class="me-field-label">Nom de l'epouse</span>
                                    <div class="me-input-wrap">
                                        <i class="fas fa-user"></i>
                                        <input type="text" name="nomEpouse" value="<?php echo htmlspecialchars((string) ($datagetevent['nom_epouse'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-input" placeholder="Nom de l'epouse">
                                    </div>
                                </label>

                                <label class="me-field me-field-full">
                                    <span class="me-field-label">Autres precisions</span>
                                    <div class="me-textarea-wrap">
                                        <i class="fas fa-edit"></i>
                                        <textarea name="details" class="me-textarea" rows="5" placeholder="Autres precisions"><?php echo htmlspecialchars((string) ($datagetevent['autres_precisions'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </label>
                            </div>

                            <div class="me-form-actions">
                                <button type="submit" name="submittext" class="me-primary-button">
                                    <i class="fas fa-save"></i>
                                    <span>Enregistrer les modifications</span>
                                </button>
                            </div>
                        </form>
                    </section>

                    <aside class="me-sidebar-stack">
                        <section class="me-card me-summary-card">
                            <div class="me-card-head">
                                <div>
                                    <span class="me-card-kicker">Vue d'ensemble</span>
                                    <h2>Resume de la commande</h2>
                                    <p>Gardez sous les yeux les informations de commande et les modeles deja attaches a l'evenement.</p>
                                </div>
                            </div>

                            <div class="me-meta-list">
                                <div class="me-meta-line"><span>Langue invitation</span><strong><?php echo htmlspecialchars((string) ($datagetevent['lang'] ?? 'Non renseignee'), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                <div class="me-meta-line"><span>Ordre des prenoms</span><strong><?php echo (($datagetevent['ordrepri'] ?? '') === 'm') ? 'Homme en premier' : ((($datagetevent['ordrepri'] ?? '') === 'f') ? 'Femme en premier' : 'Non renseigne'); ?></strong></div>
                                <div class="me-meta-line"><span>Type de paiement</span><strong><?php echo htmlspecialchars($paymentTypeLabel, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                <div class="me-meta-line"><span>Code promo</span><strong><?php echo htmlspecialchars((string) ($checkoutData['promo_code'] ?? 'Aucun'), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                <div class="me-meta-line"><span>Total commande</span><strong><?php echo htmlspecialchars(number_format((float) ($checkoutData['total'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars((string) ($checkoutData['devise'] ?? 'USD'), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            </div>
                        </section>

                        <section class="me-card me-summary-card">
                            <div class="me-card-head">
                                <div>
                                    <span class="me-card-kicker">Modeles</span>
                                    <h2>Invitations selectionnees</h2>
                                    <p>Retrouvez rapidement les modeles actuellement attaches a cette commande.</p>
                                </div>
                            </div>

                            <div class="me-model-list">
                                <?php if ($invitationModels !== []) { ?>
                                    <?php foreach ($invitationModels as $model) { ?>
                                    <article class="me-model-item">
                                        <?php if (!empty($model['image'])) { ?>
                                        <img src="../images/modeleis/<?php echo htmlspecialchars((string) $model['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($model['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php } else { ?>
                                        <div class="me-model-fallback">Modele</div>
                                        <?php } ?>

                                        <div>
                                            <strong><?php echo htmlspecialchars((string) ($model['nom'] ?? 'Modele'), ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <span>Quantite : <?php echo (int) ($model['quantite'] ?? 1); ?></span>
                                            <small><?php echo $formatAmount($model['unit_price'] ?? null); ?></small>
                                        </div>
                                    </article>
                                    <?php } ?>
                                <?php } else { ?>
                                <div class="me-empty-block">
                                    <strong>Aucun modele multiple enregistre.</strong>
                                    <span>Les modeles ajoutes a la commande apparaitront ici.</span>
                                </div>
                                <?php } ?>
                            </div>
                        </section>
                    </aside>
                </div>

                <section class="me-card me-gallery-card">
                    <div class="me-card-head me-card-head-inline">
                        <div>
                            <span class="me-card-kicker">Galerie</span>
                            <h2>Photos de l'evenement</h2>
                            <p>Supprimez les visuels existants, ajoutez-en de nouveaux et gardez un apercu clair avant l'envoi.</p>
                        </div>
                    </div>

                    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1') { ?>
                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Supprime',
                            text: 'La photo a bien ete supprimee.',
                            icon: 'success',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    });
                    </script>
                    <?php } ?>

                    <div class="me-gallery-grid">
                        <?php if ($photoRows === []) { ?>
                        <div class="me-empty-block me-empty-block-wide">
                            <strong>Aucune photo dans la galerie.</strong>
                            <span>Ajoutez des photos ci-dessous pour enrichir la presentation de l'evenement.</span>
                        </div>
                        <?php } ?>

                        <?php foreach ($photoRows as $dataphoto) { ?>
                        <article class="me-gallery-item" data-photo="<?php echo (int) $dataphoto['cod_photo']; ?>">
                            <img src="../photosevent/<?php echo htmlspecialchars((string) $dataphoto['nom_photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $dataphoto['nom_photo'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="button" style="<?php echo $displayact; ?>" class="me-gallery-delete" onclick="confirmSuppEvent(event, '<?php echo (int) $dataphoto['cod_photo']; ?>', '<?php echo (int) $cod_getevent; ?>')">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </article>
                        <?php } ?>
                    </div>

                    <form id="eventForm" action="" method="post" enctype="multipart/form-data" class="me-upload-card">
                        <div class="me-upload-head">
                            <div>
                                <h3>Ajouter de nouvelles photos</h3>
                                <p>Selection multiple, apercu direct et progression pendant l'envoi.</p>
                            </div>
                            <label for="fileInput" class="me-upload-trigger">
                                <i class="fas fa-plus"></i>
                                <span>Importer les photos</span>
                            </label>
                        </div>

                        <input type="file" name="photos[]" class="me-hidden-input" accept="image/*" id="fileInput" multiple>

                        <div id="previewContainer" class="me-upload-preview"></div>

                        <div id="progressWrapper" class="me-progress-shell" hidden>
                            <div class="me-progress-card">
                                <div class="me-progress-track">
                                    <div id="progressBar" class="me-progress-bar"></div>
                                </div>
                                <span id="progressPercentage" class="me-progress-label">Telechargement des photos : 0%</span>
                                <div id="status" class="me-progress-status"></div>
                            </div>
                        </div>

                        <div class="me-form-actions">
                            <button type="submit" class="me-secondary-button">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Ajouter les photos</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section class="me-card me-orders-card">
                    <div class="me-card-head me-card-head-inline">
                        <div>
                            <span class="me-card-kicker">Commandes</span>
                            <h2>Accessoires et options associes</h2>
                            <p>Supprimez une ligne existante ou ajoutez rapidement une nouvelle commande via une modale plus propre.</p>
                        </div>

                        <button type="button" class="me-primary-button" data-open-command-modal>
                            <i class="fas fa-plus"></i>
                            <span>Ajouter une commande</span>
                        </button>
                    </div>

                    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '2') { ?>
                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Supprime',
                            text: 'La commande a bien ete supprimee.',
                            icon: 'success',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    });
                    </script>
                    <?php } ?>

                    <div class="me-command-grid">
                        <?php if ($commandRows === []) { ?>
                        <div class="me-empty-block me-empty-block-wide">
                            <strong>Aucune commande associee.</strong>
                            <span>Ajoutez des accessoires ou des modeles a cette commande via la modale ci-dessous.</span>
                        </div>
                        <?php } ?>

                        <?php foreach ($commandRows as $commandRow) { ?>
                        <article class="me-command-item">
                            <div class="me-command-main">
                                <?php if ($commandRow['model_image'] !== '') { ?>
                                <img src="../images/modeleis/<?php echo htmlspecialchars($commandRow['model_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($commandRow['model_label'], ENT_QUOTES, 'UTF-8'); ?>" class="me-command-image">
                                <?php } else { ?>
                                <div class="me-command-image me-command-image-fallback"><i class="fas fa-gift"></i></div>
                                <?php } ?>

                                <div class="me-command-copy">
                                    <div class="me-command-head">
                                        <strong><?php echo htmlspecialchars($commandRow['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span class="me-command-badge">Qte <?php echo (int) $commandRow['quantity']; ?></span>
                                    </div>

                                    <?php if ($commandRow['model_label'] !== '') { ?>
                                    <span class="me-command-model"><?php echo htmlspecialchars($commandRow['model_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } ?>

                                    <div class="me-command-meta">
                                        <span>Prix unitaire : <?php echo htmlspecialchars($formatAmount($commandRow['unit_price']), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ($commandRow['line_total'] !== null && $commandRow['line_total'] !== '') { ?>
                                        <span>Total ligne : <?php echo htmlspecialchars($formatAmount($commandRow['line_total']), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="me-command-delete" onclick="confirmSuppAcc(event, '<?php echo (int) $commandRow['id']; ?>', '<?php echo (int) $cod_getevent; ?>')">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>
                        </article>
                        <?php } ?>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div id="commandModal" class="me-modal" hidden>
        <div class="me-modal-backdrop" data-close-command-modal></div>
        <div class="me-modal-dialog">
            <button type="button" class="me-modal-close" data-close-command-modal aria-label="Fermer">&times;</button>

            <div class="me-modal-header">
                <span class="me-card-kicker">Nouvelle commande</span>
                <h2>Ajouter des accessoires a l'evenement</h2>
                <p>Selectionnez les accessoires, puis choisissez un modele d'invitation ou de chevalet si necessaire.</p>
            </div>

            <form action="" method="post" enctype="multipart/form-data" class="me-modal-form">
                <input type="hidden" name="codgetevent" value="<?php echo (int) $cod_getevent; ?>">

                <div class="me-accessory-grid" id="AccessoireGroup">
                    <?php foreach ($accessoryCatalogRows as $accessoryRow) {
                        $accessoryId = (int) ($accessoryRow['cod_mod'] ?? 0);
                        $accessoryName = (string) ($accessoryRow['nom'] ?? 'Accessoire');
                        $accessoryPrice = $formatAmount($accessoryRow['unit_price'] ?? null);
                    ?>
                    <label class="me-accessory-card">
                        <input type="checkbox" name="accessoires[]" value="<?php echo $accessoryId; ?>" data-accessory-checkbox>
                        <span class="me-accessory-card-copy">
                            <strong><?php echo htmlspecialchars($accessoryName, ENT_QUOTES, 'UTF-8'); ?></strong>
                            <small><?php echo htmlspecialchars($accessoryPrice, ENT_QUOTES, 'UTF-8'); ?></small>
                        </span>
                    </label>
                    <?php } ?>
                </div>

                <div class="me-modal-dependent is-hidden" id="ModInvitation">
                    <div class="me-modal-block-head">
                        <h3>Choisir un modele d'invitation</h3>
                        <p>Inspire du nouveau design de creation, avec vue en cartes et selection directe.</p>
                    </div>

                    <input type="hidden" name="modele_inv" id="modeleInv" value="<?php echo htmlspecialchars($currentInvitationModelId, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="me-model-choice-grid" id="invitationModelChoiceGrid">
                        <?php foreach ($invitationCatalogRows as $data_mod) {
                            $modelId = (int) ($data_mod['cod_mod'] ?? 0);
                            $modelImage = trim((string) ($data_mod['image'] ?? ''));
                            $isSelected = (string) $modelId === $currentInvitationModelId;
                        ?>
                        <button type="button" class="me-model-choice-card<?php echo $isSelected ? ' is-selected' : ''; ?>" data-model-value="<?php echo $modelId; ?>">
                            <div class="me-model-choice-image-wrap">
                                <?php if ($modelImage !== '') { ?>
                                <img src="../images/modeleis/<?php echo htmlspecialchars($modelImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($data_mod['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="me-model-choice-image">
                                <?php } else { ?>
                                <div class="me-model-choice-image me-model-choice-fallback">Modele</div>
                                <?php } ?>
                            </div>
                            <div class="me-model-choice-copy">
                                <div class="me-model-choice-head">
                                    <span class="me-model-choice-title"><?php echo htmlspecialchars((string) ($data_mod['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="me-model-choice-price"><?php echo htmlspecialchars($formatAmount($data_mod['unit_price'] ?? null), ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <span class="me-model-choice-ref">Modele #<?php echo $modelId; ?></span>
                            </div>
                        </button>
                        <?php } ?>
                    </div>
                </div>

                <div class="me-modal-dependent is-hidden" id="ModChevalet">
                    <div class="me-modal-block-head">
                        <h3>Choisir un modele de chevalet</h3>
                        <p>Selectionnez la variante de chevalet de table a lier a cette commande.</p>
                    </div>

                    <label class="me-field me-field-full">
                        <span class="me-field-label">Modele de chevalet</span>
                        <div class="me-input-wrap">
                            <i class="fas fa-image"></i>
                            <select class="me-input" name="chevaletModel" id="chevaletModel">
                                <option value="">Choisir un modele</option>
                                <?php foreach ($chevaletCatalogRows as $data_mod) { ?>
                                <option value="<?php echo (int) ($data_mod['cod_mod'] ?? 0); ?>"><?php echo htmlspecialchars((string) ($data_mod['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </label>
                </div>

                <div class="me-form-actions">
                    <button class="me-primary-button" type="submit" name="submiacces">
                        <i class="fas fa-plus-circle"></i>
                        <span>Ajouter la commande</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
    html, body{height:auto !important;overflow-y:auto !important}
    body.layout-top-nav.fixed{overflow-y:auto !important}
    .wrapper,.content-wrapper,.container-full{height:auto !important;min-height:0 !important;overflow:visible !important}
    .me-page{padding:24px 0 42px}
    .me-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;flex-wrap:wrap;padding:34px;border-radius:34px;background:radial-gradient(circle at top left,rgba(255,255,255,.18),transparent 32%),linear-gradient(135deg,#231815 0%,#5a3425 44%,#8b633e 100%);box-shadow:0 28px 60px rgba(48,29,23,.18);color:#fffaf4;margin-bottom:24px}
    .me-hero-copy{max-width:780px}
    .me-kicker,.me-card-kicker{display:inline-flex;align-items:center;gap:8px;padding:7px 12px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
    .me-kicker{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.16)}
    .me-card-kicker{background:#fff1e6;color:#8b633e}
    .me-title{margin:18px 0 10px;font-size:36px;line-height:1.05;font-weight:800;color:#fff}
    .me-copy{margin:0;color:rgba(255,250,244,.84);font-size:15px;line-height:1.75}
    .me-hero-stats{display:flex;gap:14px;flex-wrap:wrap;justify-content:flex-end}
    .me-stat-card{min-width:130px;padding:16px 18px;border-radius:22px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);display:flex;flex-direction:column;gap:6px}
    .me-stat-card strong{font-size:22px;font-weight:800;line-height:1.1}
    .me-stat-card span{font-size:12px;color:rgba(255,250,244,.78);text-transform:uppercase;letter-spacing:.05em}
    .me-alert{margin:0 0 18px;padding:14px 16px;border-radius:18px;font-weight:700}
    .me-alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    .me-layout{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:20px;align-items:start}
    .me-sidebar-stack{display:grid;gap:20px}
    .me-card{background:linear-gradient(180deg,#ffffff 0%,#fffaf5 100%);border:1px solid rgba(145,91,45,.12);border-radius:30px;padding:26px;box-shadow:0 22px 48px rgba(48,29,23,.08);margin-top:20px}
    .me-layout .me-card{margin-top:0}
    .me-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:20px}
    .me-card-head-inline{flex-wrap:wrap;align-items:flex-end}
    .me-card-head h2{margin:10px 0 8px;font-size:28px;font-weight:800;color:#231815}
    .me-card-head h3{margin:0 0 6px;font-size:20px;font-weight:800;color:#231815}
    .me-card-head p,.me-modal-header p,.me-modal-block-head p{margin:0;color:#6d5a50;font-size:14px;line-height:1.7}
    .me-form-shell{display:grid;gap:20px}
    .me-form-grid{display:grid;gap:16px}
    .me-form-grid-two{grid-template-columns:repeat(2,minmax(0,1fr))}
    .me-field{display:flex;flex-direction:column;gap:8px}
    .me-field-full{grid-column:1/-1}
    .me-field-label{font-size:13px;font-weight:800;letter-spacing:.03em;color:#5a3425;text-transform:uppercase}
    .me-input-wrap,.me-textarea-wrap{display:flex;align-items:center;gap:12px;min-height:58px;padding:0 16px;border-radius:20px;border:1px solid rgba(145,91,45,.14);background:#fffdf9;box-shadow:inset 0 1px 0 rgba(255,255,255,.6)}
    .me-textarea-wrap{align-items:flex-start;padding:16px}
    .me-input-wrap i,.me-textarea-wrap i{color:#8b633e;flex:0 0 auto}
    .me-input,.me-textarea{width:100%;border:0;background:transparent;outline:0;color:#231815;font-size:15px;box-shadow:none}
    .me-input{min-height:56px}
    .me-textarea{min-height:130px;resize:vertical;padding:0}
    .me-form-actions{display:flex;align-items:center;justify-content:flex-start;gap:12px;flex-wrap:wrap}
    .me-primary-button,.me-secondary-button{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:54px;padding:0 22px;border:0;border-radius:18px;font-size:15px;font-weight:800;cursor:pointer;text-decoration:none}
    .me-primary-button{background:linear-gradient(135deg,#7a3a27 0%,#a05439 100%);color:#fff;box-shadow:0 18px 34px rgba(122,58,39,.20)}
    .me-secondary-button{background:#f0dfcf;color:#5a3425}
    .me-meta-list{display:grid;gap:10px}
    .me-meta-line{display:flex;justify-content:space-between;gap:16px;padding-top:10px;border-top:1px dashed rgba(145,91,45,.16)}
    .me-meta-line:first-child{padding-top:0;border-top:0}
    .me-meta-line span{color:#6d5a50}
    .me-meta-line strong{color:#231815;text-align:right}
    .me-model-list{display:grid;gap:12px}
    .me-model-item{display:grid;grid-template-columns:64px minmax(0,1fr);gap:14px;align-items:center;padding:14px;border-radius:22px;background:#fff;border:1px solid rgba(145,91,45,.12)}
    .me-model-item img,.me-model-fallback{width:64px;height:64px;border-radius:18px;object-fit:cover;background:#f0dfcf;border:1px solid rgba(145,91,45,.12);display:flex;align-items:center;justify-content:center;color:#8b633e;font-weight:800}
    .me-model-item strong,.me-command-head strong{display:block;font-size:16px;color:#231815}
    .me-model-item span,.me-model-item small{display:block;color:#6d5a50}
    .me-empty-block{display:grid;gap:6px;padding:18px;border:1px dashed rgba(145,91,45,.2);border-radius:20px;background:#fffdf9;color:#6d5a50;text-align:center}
    .me-empty-block strong{color:#231815}
    .me-empty-block-wide{min-height:160px;align-content:center}
    .me-gallery-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;margin-bottom:18px}
    .me-gallery-item{position:relative;overflow:hidden;border-radius:24px;background:#f0dfcf;aspect-ratio:1/1;border:1px solid rgba(145,91,45,.14)}
    .me-gallery-item img{width:100%;height:100%;object-fit:cover}
    .me-gallery-delete{position:absolute;top:12px;right:12px;width:40px;height:40px;border:0;border-radius:999px;background:rgba(185,28,28,.92);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 12px 24px rgba(127,29,29,.22)}
    .me-upload-card{padding:20px;border-radius:26px;background:#fff;border:1px solid rgba(145,91,45,.12);display:grid;gap:18px}
    .me-upload-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .me-upload-head h3{margin:0 0 6px;font-size:22px;font-weight:800;color:#231815}
    .me-upload-trigger{display:inline-flex;align-items:center;gap:10px;min-height:48px;padding:0 18px;border-radius:18px;background:#231815;color:#fffaf4;cursor:pointer;font-weight:800}
    .me-hidden-input{display:none}
    .me-upload-preview{display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:14px}
    .me-upload-thumb{position:relative;border-radius:20px;overflow:hidden;aspect-ratio:1/1;background:#f0dfcf;border:1px solid rgba(145,91,45,.12)}
    .me-upload-thumb img{width:100%;height:100%;object-fit:cover}
    .me-upload-thumb button{position:absolute;top:8px;right:8px;width:32px;height:32px;border:0;border-radius:999px;background:rgba(15,23,42,.72);color:#fff;cursor:pointer}
    .me-progress-shell{display:block}
    .me-progress-card{padding:18px;border-radius:20px;background:#fff7ed;border:1px solid rgba(249,115,22,.18)}
    .me-progress-track{height:12px;border-radius:999px;background:#fed7aa;overflow:hidden}
    .me-progress-bar{width:0;height:100%;border-radius:999px;background:linear-gradient(90deg,#f97316 0%,#fb923c 100%);transition:width .2s ease}
    .me-progress-label,.me-progress-status{display:block;margin-top:10px;color:#9a3412;font-weight:700}
    .me-command-grid{display:grid;gap:14px}
    .me-command-item{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:18px;border-radius:24px;background:#fff;border:1px solid rgba(145,91,45,.12)}
    .me-command-main{display:grid;grid-template-columns:72px minmax(0,1fr);gap:16px;align-items:center;min-width:0}
    .me-command-image{width:72px;height:72px;border-radius:20px;object-fit:cover;background:#f0dfcf;border:1px solid rgba(145,91,45,.12)}
    .me-command-image-fallback{display:flex;align-items:center;justify-content:center;color:#8b633e}
    .me-command-copy{display:grid;gap:6px;min-width:0}
    .me-command-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .me-command-badge{display:inline-flex;padding:7px 10px;border-radius:999px;background:#fff1e6;color:#8b633e;font-size:12px;font-weight:800}
    .me-command-model{color:#7a3a27;font-weight:700}
    .me-command-meta{display:flex;flex-wrap:wrap;gap:10px 16px;color:#6d5a50;font-size:13px}
    .me-command-delete{width:44px;height:44px;border:0;border-radius:16px;background:#fef2f2;color:#b91c1c;display:flex;align-items:center;justify-content:center;cursor:pointer;flex:0 0 auto}
    .me-modal{position:fixed;inset:0;z-index:12000;display:flex;align-items:center;justify-content:center;padding:20px}
    .me-modal[hidden]{display:none}
    .me-modal-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.62);backdrop-filter:blur(6px)}
    .me-modal-dialog{position:relative;width:min(980px,100%);max-height:calc(100vh - 40px);overflow:auto;padding:28px;border-radius:30px;background:#fffdf9;box-shadow:0 34px 80px rgba(15,23,42,.28)}
    .me-modal-close{position:absolute;top:16px;right:16px;width:42px;height:42px;border:0;border-radius:999px;background:rgba(15,23,42,.08);color:#231815;font-size:26px;line-height:1;cursor:pointer}
    .me-modal-header{margin-bottom:20px}
    .me-modal-header h2{margin:12px 0 8px;font-size:28px;font-weight:800;color:#231815}
    .me-modal-form{display:grid;gap:18px}
    .me-accessory-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}
    .me-accessory-card{position:relative;display:flex;align-items:stretch;cursor:pointer}
    .me-accessory-card input{position:absolute;opacity:0;pointer-events:none}
    .me-accessory-card-copy{display:grid;gap:6px;width:100%;padding:18px;border:1px solid rgba(145,91,45,.12);border-radius:24px;background:#fff;box-shadow:0 16px 30px rgba(109,68,39,.08);transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease}
    .me-accessory-card-copy strong{font-size:16px;color:#231815}
    .me-accessory-card-copy small{color:#8b633e;font-weight:800}
    .me-accessory-card input:checked + .me-accessory-card-copy{border-color:#7a3a27;background:linear-gradient(180deg,#fffaf4 0%,#f8efe4 100%);transform:translateY(-1px);box-shadow:0 18px 32px rgba(122,58,39,.16)}
    .me-modal-dependent{display:grid;gap:14px}
    .me-modal-block-head h3{margin:0 0 6px;font-size:22px;font-weight:800;color:#231815}
    .me-model-choice-grid{display:grid;gap:16px}
    .me-model-choice-card{display:grid;grid-template-columns:132px minmax(0,1fr);gap:0;border:1px solid rgba(145,91,45,.10);border-radius:28px;padding:0;overflow:hidden;background:#fff;box-shadow:0 22px 44px rgba(109,68,39,.10);text-align:left;cursor:pointer;transition:border-color .2s ease, box-shadow .2s ease, transform .2s ease}
    .me-model-choice-card.is-selected{border-color:#7a3a27;background:linear-gradient(180deg,#fffaf4 0%,#f8efe4 100%);box-shadow:0 24px 48px rgba(122,58,39,.14)}
    .me-model-choice-image-wrap{background:linear-gradient(180deg,#f0dfcf 0%,#f7efe7 100%)}
    .me-model-choice-image,.me-model-choice-fallback{display:block;width:100%;height:100%;min-height:180px;object-fit:cover}
    .me-model-choice-fallback{display:flex;align-items:center;justify-content:center;color:#8b633e;font-weight:800}
    .me-model-choice-copy{display:grid;gap:10px;padding:20px 22px 18px}
    .me-model-choice-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px}
    .me-model-choice-title{font-size:20px;font-weight:800;line-height:1.3;color:#231815}
    .me-model-choice-price{white-space:nowrap;font-size:17px;font-weight:800;color:#7a3a27}
    .me-model-choice-ref{display:inline-flex;align-self:flex-start;padding:8px 12px;border-radius:999px;background:#fff6ea;color:#8b633e;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
    .is-hidden{display:none !important}
    body.me-modal-open{overflow:hidden}
    @media (max-width: 1100px){
        .me-layout{grid-template-columns:minmax(0,1fr)}
    }
    @media (max-width: 820px){
        .me-page{padding:18px 0 34px}
        .me-hero{padding:24px;border-radius:28px}
        .me-title{font-size:30px}
        .me-form-grid-two{grid-template-columns:minmax(0,1fr)}
        .me-model-choice-card{grid-template-columns:96px minmax(0,1fr)}
        .me-model-choice-image,.me-model-choice-fallback{min-height:132px}
        .me-model-choice-copy{padding:14px}
        .me-model-choice-title{font-size:16px}
        .me-command-item{flex-direction:column}
        .me-command-main{grid-template-columns:60px minmax(0,1fr)}
        .me-modal-dialog{padding:22px}
    }
    @media (max-width: 640px){
        .me-hero-stats{justify-content:flex-start}
        .me-stat-card{min-width:110px}
        .me-card{padding:20px;border-radius:24px}
        .me-gallery-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
        .me-accessory-grid{grid-template-columns:minmax(0,1fr)}
        .me-form-actions{flex-direction:column;align-items:stretch}
        .me-primary-button,.me-secondary-button{width:100%}
    }
    </style>

    <?php include('footer.php'); ?>
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

    <?php if ($postSuccessScript !== null) {
        echo $postSuccessScript;
    } ?>

    <script>
    function confirmSuppEvent(event, codPhoto, codGetevent) {
        event.preventDefault();
        Swal.fire({
            title: 'Supprimer ? ',
            text: 'Etes-vous sur de vouloir supprimer cette photo ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php?page=supphoto&cod=' + codPhoto + '&codevent=' + codGetevent;
            }
        });
    }

    function confirmSuppAcc(event, codAccev, codGetevent) {
        event.preventDefault();
        Swal.fire({
            title: 'Supprimer ? ',
            text: 'Etes-vous sur de vouloir supprimer cette commande ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php?page=supcom&cod=' + codAccev + '&codevent=' + codGetevent;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;
        const eventTypeSelect = document.getElementById('eventType');
        const weddingTypeGroup = document.getElementById('weddingTypeGroup');
        const weddingNameGroups = [
            document.getElementById('NomepouxGroup'),
            document.getElementById('PrenomepouxGroup'),
            document.getElementById('NomepouseGroup'),
            document.getElementById('PrenomepouseGroup')
        ];
        const birthdayGroup = document.getElementById('NomsAnniv');
        const conferenceGroup = document.getElementById('ThemeConf');

        function toggleConditionalField(element, shouldShow) {
            if (!element) {
                return;
            }
            element.classList[shouldShow ? 'remove' : 'add']('is-hidden');
        }

        function syncEventFields() {
            const currentType = eventTypeSelect ? String(eventTypeSelect.value || '') : '';
            const isWedding = currentType === '1';
            const isBirthday = currentType === '2';
            const isConference = currentType === '3';

            toggleConditionalField(weddingTypeGroup, isWedding);
            weddingNameGroups.forEach((group) => toggleConditionalField(group, isWedding));
            toggleConditionalField(birthdayGroup, isBirthday);
            toggleConditionalField(conferenceGroup, isConference);
        }

        syncEventFields();
        if (eventTypeSelect) {
            eventTypeSelect.addEventListener('change', syncEventFields);
        }

        const commandModal = document.getElementById('commandModal');
        const commandModalTriggers = document.querySelectorAll('[data-open-command-modal]');
        const commandModalClosers = document.querySelectorAll('[data-close-command-modal]');

        function openCommandModal() {
            if (!commandModal) {
                return;
            }
            commandModal.hidden = false;
            body.classList.add('me-modal-open');
        }

        function closeCommandModal() {
            if (!commandModal) {
                return;
            }
            commandModal.hidden = true;
            body.classList.remove('me-modal-open');
        }

        commandModalTriggers.forEach((trigger) => {
            trigger.addEventListener('click', openCommandModal);
        });

        commandModalClosers.forEach((trigger) => {
            trigger.addEventListener('click', closeCommandModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeCommandModal();
            }
        });

        const accessoryCheckboxes = document.querySelectorAll('[data-accessory-checkbox]');
        const invitationDependency = document.getElementById('ModInvitation');
        const chevaletDependency = document.getElementById('ModChevalet');

        function syncAccessoryDependencies() {
            let showInvitation = false;
            let showChevalet = false;

            accessoryCheckboxes.forEach((checkbox) => {
                if (!checkbox.checked) {
                    return;
                }

                if (String(checkbox.value) === '1') {
                    showInvitation = true;
                }
                if (String(checkbox.value) === '3') {
                    showChevalet = true;
                }
            });

            toggleConditionalField(invitationDependency, showInvitation);
            toggleConditionalField(chevaletDependency, showChevalet);
        }

        accessoryCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', syncAccessoryDependencies);
        });
        syncAccessoryDependencies();

        const modeleInvInput = document.getElementById('modeleInv');
        const invitationModelCards = document.querySelectorAll('[data-model-value]');
        invitationModelCards.forEach((card) => {
            card.addEventListener('click', function () {
                const selectedValue = card.getAttribute('data-model-value') || '';
                if (modeleInvInput) {
                    modeleInvInput.value = selectedValue;
                }

                invitationModelCards.forEach((otherCard) => {
                    otherCard.classList.toggle('is-selected', otherCard === card);
                });
            });
        });

        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('previewContainer');
        if (fileInput && previewContainer) {
            fileInput.addEventListener('change', function (event) {
                previewContainer.innerHTML = '';
                const files = Array.from(event.target.files || []);

                files.forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function (loadEvent) {
                        const thumb = document.createElement('div');
                        thumb.className = 'me-upload-thumb';

                        const image = document.createElement('img');
                        image.src = loadEvent.target ? loadEvent.target.result : '';
                        image.alt = file.name;

                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.innerHTML = '&times;';
                        removeButton.addEventListener('click', function () {
                            thumb.remove();
                        });

                        thumb.appendChild(image);
                        thumb.appendChild(removeButton);
                        previewContainer.appendChild(thumb);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        const uploadForm = document.getElementById('eventForm');
        const progressWrapper = document.getElementById('progressWrapper');
        const progressBar = document.getElementById('progressBar');
        const progressPercentage = document.getElementById('progressPercentage');
        const progressStatus = document.getElementById('status');

        if (uploadForm) {
            uploadForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    Swal.fire({
                        title: 'Photos manquantes',
                        text: 'Selectionnez au moins une photo avant de lancer l\'envoi.',
                        icon: 'warning',
                        confirmButtonText: 'Compris'
                    });
                    return;
                }

                if (progressWrapper) {
                    progressWrapper.hidden = false;
                }
                if (progressBar) {
                    progressBar.style.width = '0%';
                }
                if (progressPercentage) {
                    progressPercentage.textContent = 'Telechargement des photos : 0%';
                }
                if (progressStatus) {
                    progressStatus.textContent = '';
                }

                const formData = new FormData(uploadForm);
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);

                xhr.upload.onprogress = function (progressEvent) {
                    if (!progressEvent.lengthComputable) {
                        return;
                    }

                    const percentComplete = (progressEvent.loaded / progressEvent.total) * 100;
                    if (progressBar) {
                        progressBar.style.width = percentComplete + '%';
                    }
                    if (progressPercentage) {
                        progressPercentage.textContent = 'Telechargement des photos : ' + Math.round(percentComplete) + '%';
                    }
                };

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        Swal.fire({
                            title: 'Photos ajoutees',
                            text: 'La galerie a ete mise a jour avec succes.',
                            icon: 'success',
                            confirmButtonText: 'Actualiser'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'index.php?page=modevent&cod=<?php echo (int) $cod_getevent; ?>';
                            }
                        });
                        return;
                    }

                    if (progressStatus) {
                        progressStatus.textContent = 'Erreur lors du traitement des photos.';
                    }
                };

                xhr.send(formData);
            });
        }
    });
    </script>
</div>
