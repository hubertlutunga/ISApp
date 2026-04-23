<?php
$datasession = UserAccountService::currentSessionUser($pdo) ?? [];
if ((string) ($datasession['type_user'] ?? '') !== '1') {
    PageRouter::redirect('index.php?page=logout');
}

EventOrderService::ensureCatalogInfrastructure($pdo);

$catalogImageUploadDir = __DIR__ . '/../../images/modeleis';
$catalogImagePreviewBase = '../images/modeleis/';

$flash = null;
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['save_catalog_model'])) {
            EventOrderService::upsertCatalogModel(
                $pdo,
                $_POST,
                $_FILES['catalog_image'] ?? null,
                $catalogImageUploadDir
            );
            $flash = 'Catalogue mis a jour avec succes.';
        }

        if (isset($_POST['delete_catalog_model'])) {
            $deleteResult = EventOrderService::deleteCatalogModel($pdo, (int) ($_POST['cod_mod'] ?? 0));
            $flash = $deleteResult === 'archived'
                ? 'Catalogue archive car il est deja utilise dans des commandes ou des evenements.'
                : 'Catalogue supprime avec succes.';
        }
    } catch (Throwable $exception) {
        $flash = $exception->getMessage();
        $flashType = 'error';
    }
}

$catalogModels = EventOrderService::listCatalogModels($pdo);
$catalogTypeLabels = [
    'accessoires' => 'Accessoire',
    'invitation' => 'Modele invitation',
    'chevalet' => 'Modele chevalet',
];

$catalogTypeClasses = [
    'accessoires' => 'catalog-badge-accessoire',
    'invitation' => 'catalog-badge-invitation',
    'chevalet' => 'catalog-badge-chevalet',
];

$catalogTypeOrder = [
    'invitation' => 1,
    'accessoires' => 2,
    'chevalet' => 3,
];

usort($catalogModels, static function (array $leftRow, array $rightRow) use ($catalogTypeOrder): int {
    $leftType = (string) ($leftRow['type_mod'] ?? '');
    $rightType = (string) ($rightRow['type_mod'] ?? '');
    $leftWeight = $catalogTypeOrder[$leftType] ?? 99;
    $rightWeight = $catalogTypeOrder[$rightType] ?? 99;

    if ($leftWeight !== $rightWeight) {
        return $leftWeight <=> $rightWeight;
    }

    return strcmp(
        mb_strtolower(trim((string) ($leftRow['nom'] ?? ''))),
        mb_strtolower(trim((string) ($rightRow['nom'] ?? '')))
    );
});

$catalogTotal = count($catalogModels);
$catalogActiveTotal = count(array_filter($catalogModels, static fn(array $row): bool => ((int) ($row['is_active'] ?? 1)) === 1));
$catalogImageTotal = count(array_filter($catalogModels, static fn(array $row): bool => trim((string) ($row['image'] ?? '')) !== ''));
?>

<div class="wrapper">
    <?php include('header_admin.php'); ?>

    <div class="content-wrapper">
        <div class="container-full">
            <div class="container py-30">
                <div class="catalog-hero">
                    <div class="catalog-hero-copy">
                        <span class="catalog-kicker">Administration</span>
                        <h1>Gestion complete du catalogue</h1>
                        <p>Administrez tous les catalogues depuis une seule interface claire: liste en tableau, ajout rapide, edition en modale et suppression confirmee.</p>
                    </div>

                    <div class="catalog-hero-actions">
                        <div class="catalog-stats">
                            <article class="catalog-stat-pill">
                                <strong><?php echo (int) $catalogTotal; ?></strong>
                                <span>Elements</span>
                            </article>
                            <article class="catalog-stat-pill">
                                <strong><?php echo (int) $catalogActiveTotal; ?></strong>
                                <span>Actifs</span>
                            </article>
                            <article class="catalog-stat-pill">
                                <strong><?php echo (int) $catalogImageTotal; ?></strong>
                                <span>Avec image</span>
                            </article>
                        </div>

                        <button type="button" class="catalog-primary-action" data-open-modal="catalogCreateModal">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            <span>Ajouter un catalogue</span>
                        </button>
                    </div>
                </div>

                <?php if ($flash !== null) { ?>
                <div class="catalog-flash catalog-flash-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php } ?>

                <section class="catalog-card">
                    <div class="catalog-head catalog-head-inline">
                        <div>
                            <h2>Liste des catalogues</h2>
                            <p>Chaque ligne peut etre modifiee en modale ou supprimee via une confirmation SweetAlert.</p>
                        </div>
                        <label class="catalog-search-box" aria-label="Recherche catalogue">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            <input type="search" id="catalogTableSearch" placeholder="Rechercher un catalogue, un type ou un prix...">
                        </label>
                    </div>

                    <div class="catalog-table-shell">
                        <table class="catalog-table">
                            <thead>
                                <tr>
                                    <th>Apercu</th>
                                    <th>Catalogue</th>
                                    <th>Type</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th class="catalog-actions-column">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($catalogModels === []) { ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="catalog-empty-state">
                                            <strong>Aucun catalogue disponible.</strong>
                                            <span>Ajoutez votre premier element pour commencer l’administration.</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>

                                <?php foreach ($catalogModels as $catalogModel) {
                                    $modelId = (int) ($catalogModel['cod_mod'] ?? 0);
                                    $modelName = trim((string) ($catalogModel['nom'] ?? ''));
                                    $modelType = (string) ($catalogModel['type_mod'] ?? '');
                                    $modelImage = trim((string) ($catalogModel['image'] ?? ''));
                                    $modelPrice = $catalogModel['unit_price'];
                                    $isActive = ((int) ($catalogModel['is_active'] ?? 1)) === 1;
                                    $typeLabel = $catalogTypeLabels[$modelType] ?? 'Autre';
                                    $typeClass = $catalogTypeClasses[$modelType] ?? 'catalog-badge-accessoire';
                                    $previewUrl = $modelImage !== '' ? $catalogImagePreviewBase . rawurlencode($modelImage) : '';
                                    $searchIndex = mb_strtolower(trim(implode(' ', [
                                        $modelName,
                                        $typeLabel,
                                        (string) ($modelPrice ?? ''),
                                        $isActive ? 'actif' : 'masque',
                                        '#' . $modelId,
                                    ])));
                                ?>
                                <tr class="catalog-data-row" data-search-index="<?php echo htmlspecialchars($searchIndex, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td data-label="Apercu">
                                        <div class="catalog-preview-mini">
                                            <?php if ($previewUrl !== '') { ?>
                                            <img src="<?php echo htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php } else { ?>
                                            <span><?php echo htmlspecialchars(strtoupper(substr($typeLabel, 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td data-label="Catalogue">
                                        <div class="catalog-cell-title">
                                            <strong><?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small>#<?php echo $modelId; ?></small>
                                        </div>
                                    </td>
                                    <td data-label="Type">
                                        <span class="catalog-badge <?php echo htmlspecialchars($typeClass, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td data-label="Prix">
                                        <span class="catalog-price-pill">
                                            <?php echo ($modelPrice !== null && $modelPrice !== '')
                                                ? htmlspecialchars(number_format((float) $modelPrice, 2, '.', ' '), ENT_QUOTES, 'UTF-8') . ' $'
                                                : 'Non defini'; ?>
                                        </span>
                                    </td>
                                    <td data-label="Statut">
                                        <span class="catalog-status <?php echo $isActive ? 'catalog-status-active' : 'catalog-status-inactive'; ?>">
                                            <?php echo $isActive ? 'Actif' : 'Masque'; ?>
                                        </span>
                                    </td>
                                    <td data-label="Actions" class="catalog-row-actions">
                                        <button
                                            type="button"
                                            class="catalog-icon-button catalog-edit-trigger"
                                            data-open-modal="catalogEditModal"
                                            data-cod-mod="<?php echo $modelId; ?>"
                                            data-name="<?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-type="<?php echo htmlspecialchars($modelType, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-unit-price="<?php echo htmlspecialchars((string) ($modelPrice ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-is-active="<?php echo $isActive ? '1' : '0'; ?>"
                                            data-image="<?php echo htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                            aria-label="Modifier <?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Modifier"
                                        >
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="catalog-icon-button catalog-icon-danger catalog-delete-trigger"
                                            data-form-id="catalogDeleteForm<?php echo $modelId; ?>"
                                            data-name="<?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?>"
                                            aria-label="Supprimer <?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Supprimer"
                                        >
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>

                                        <form method="post" id="catalogDeleteForm<?php echo $modelId; ?>" class="catalog-hidden-form">
                                            <input type="hidden" name="cod_mod" value="<?php echo $modelId; ?>">
                                            <input type="hidden" name="delete_catalog_model" value="1">
                                        </form>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr id="catalogNoSearchResult" hidden>
                                    <td colspan="6">
                                        <div class="catalog-empty-state">
                                            <strong>Aucun resultat.</strong>
                                            <span>Essayez un autre nom, type ou mot-cle.</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</div>

<div class="catalog-modal" id="catalogCreateModal" hidden>
    <div class="catalog-modal-backdrop" data-close-modal></div>
    <div class="catalog-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="catalogCreateTitle">
        <button type="button" class="catalog-modal-close" data-close-modal aria-label="Fermer">&times;</button>
        <div class="catalog-modal-header">
            <span class="catalog-modal-kicker">Nouveau catalogue</span>
            <h2 id="catalogCreateTitle">Ajouter un catalogue</h2>
            <p>Créez un nouvel element de catalogue avec son type, son prix et son image.</p>
        </div>

        <form method="post" enctype="multipart/form-data" class="catalog-modal-form">
            <input type="hidden" name="save_catalog_model" value="1">

            <div class="catalog-modal-grid">
                <label>
                    <span>Nom</span>
                    <input type="text" name="nom" required>
                </label>

                <label>
                    <span>Type</span>
                    <select name="type_mod" required>
                        <option value="accessoires">Accessoire</option>
                        <option value="invitation">Modele invitation</option>
                        <option value="chevalet">Modele chevalet</option>
                    </select>
                </label>

                <label>
                    <span>Prix unitaire ($)</span>
                    <input type="number" step="0.01" min="0" name="unit_price">
                </label>

                <label>
                    <span>Image</span>
                    <input type="file" name="catalog_image" accept="image/png,image/jpeg,image/jpg,image/gif" data-preview-target="catalogCreatePreviewImage" data-preview-empty="catalogCreatePreviewEmpty">
                </label>
            </div>

            <div class="catalog-modal-preview">
                <div class="catalog-modal-preview-box">
                    <img id="catalogCreatePreviewImage" alt="Apercu du catalogue" hidden>
                    <span id="catalogCreatePreviewEmpty">Apercu image</span>
                </div>
            </div>

            <div class="catalog-modal-actions">
                <label class="catalog-toggle">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Actif des la creation</span>
                </label>

                <div class="catalog-modal-action-row">
                    <button type="button" class="catalog-ghost-action" data-close-modal>Annuler</button>
                    <button type="submit" class="catalog-primary-submit">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="catalog-modal" id="catalogEditModal" hidden>
    <div class="catalog-modal-backdrop" data-close-modal></div>
    <div class="catalog-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="catalogEditTitle">
        <button type="button" class="catalog-modal-close" data-close-modal aria-label="Fermer">&times;</button>
        <div class="catalog-modal-header">
            <span class="catalog-modal-kicker">Edition</span>
            <h2 id="catalogEditTitle">Modifier le catalogue</h2>
            <p>Ajustez les informations de l’element puis validez depuis cette modale.</p>
        </div>

        <form method="post" enctype="multipart/form-data" class="catalog-modal-form" id="catalogEditForm">
            <input type="hidden" name="save_catalog_model" value="1">
            <input type="hidden" name="cod_mod" id="catalogEditId" value="0">

            <div class="catalog-modal-grid">
                <label>
                    <span>Nom</span>
                    <input type="text" name="nom" id="catalogEditName" required>
                </label>

                <label>
                    <span>Type</span>
                    <select name="type_mod" id="catalogEditType" required>
                        <option value="accessoires">Accessoire</option>
                        <option value="invitation">Modele invitation</option>
                        <option value="chevalet">Modele chevalet</option>
                    </select>
                </label>

                <label>
                    <span>Prix unitaire ($)</span>
                    <input type="number" step="0.01" min="0" name="unit_price" id="catalogEditPrice">
                </label>

                <label>
                    <span>Nouvelle image</span>
                    <input type="file" name="catalog_image" accept="image/png,image/jpeg,image/jpg,image/gif" data-preview-target="catalogEditPreviewImage" data-preview-empty="catalogEditPreviewEmpty">
                </label>
            </div>

            <div class="catalog-modal-preview">
                <div class="catalog-modal-preview-box">
                    <img id="catalogEditPreviewImage" alt="Apercu du catalogue">
                    <span id="catalogEditPreviewEmpty">Apercu image</span>
                </div>
            </div>

            <div class="catalog-modal-actions">
                <label class="catalog-toggle">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="catalogEditActive" checked>
                    <span>Catalogue actif</span>
                </label>

                <div class="catalog-modal-action-row">
                    <button type="button" class="catalog-ghost-action" data-close-modal>Annuler</button>
                    <button type="submit" class="catalog-primary-submit">Mettre a jour</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
html, body{height:auto !important; overflow-y:auto !important}
body.layout-top-nav.fixed{overflow-y:auto !important}
.wrapper, .content-wrapper, .container-full{height:auto !important; min-height:0 !important; overflow:visible !important}
.catalog-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;flex-wrap:wrap;padding:32px;border-radius:32px;background:radial-gradient(circle at top left,rgba(255,255,255,.18),transparent 34%),linear-gradient(135deg,#0f172a 0%,#1e293b 42%,#0f766e 100%);color:#fff;box-shadow:0 28px 60px rgba(15,23,42,.18);margin-bottom:24px}
.catalog-hero-copy{max-width:760px}
.catalog-hero-actions{display:grid;gap:16px;justify-items:end}
.catalog-stats{display:flex;gap:12px;flex-wrap:wrap;justify-content:flex-end}
.catalog-stat-pill{min-width:102px;padding:14px 16px;border-radius:20px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);display:flex;flex-direction:column;gap:4px;text-align:left}
.catalog-stat-pill strong{font-size:24px;line-height:1;font-weight:800}
.catalog-stat-pill span{font-size:12px;color:rgba(255,255,255,.78);text-transform:uppercase;letter-spacing:.04em}
.catalog-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-size:12px;font-weight:800;text-transform:uppercase}
.catalog-hero h1{margin:16px 0 8px;font-size:32px;font-weight:800}
.catalog-hero p{margin:0;max-width:760px;color:rgba(255,255,255,.84);line-height:1.6}
.catalog-primary-action,.catalog-primary-submit,.catalog-ghost-action{border:0;border-radius:16px;display:inline-flex;align-items:center;gap:10px;font-weight:800;min-height:48px;padding:0 18px;cursor:pointer;text-decoration:none}
.catalog-primary-action,.catalog-primary-submit{background:#f97316;color:#fff;box-shadow:0 16px 30px rgba(249,115,22,.28)}
.catalog-ghost-action{background:#eef2ff;color:#334155}
.catalog-card{background:#fff;border-radius:28px;padding:26px;box-shadow:0 20px 48px rgba(15,23,42,.08)}
.catalog-head h2{margin:0 0 6px;color:#0f172a;font-size:24px;font-weight:800}
.catalog-head p{margin:0 0 18px;color:#64748b}
.catalog-head-inline{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap}
.catalog-search-box{min-height:52px;min-width:min(360px,100%);display:inline-flex;align-items:center;gap:12px;padding:0 16px;border:1px solid rgba(148,163,184,.24);border-radius:18px;background:#fff;box-shadow:0 14px 28px rgba(15,23,42,.06)}
.catalog-search-box i{color:#64748b}
.catalog-search-box input{width:100%;border:0;outline:0;background:transparent;color:#0f172a}
.catalog-search-box input::placeholder{color:#94a3b8}
.catalog-table-shell{overflow-x:auto;border:1px solid rgba(148,163,184,.18);border-radius:24px;background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%)}
.catalog-table{width:100%;border-collapse:separate;border-spacing:0}
.catalog-table thead th{padding:18px 20px;border-bottom:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#64748b;background:rgba(248,250,252,.88);text-align:left}
.catalog-table tbody td{padding:18px 20px;border-bottom:1px solid rgba(226,232,240,.8);vertical-align:middle;color:#0f172a}
.catalog-table tbody tr:last-child td{border-bottom:0}
.catalog-preview-mini{width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#e2e8f0 0%,#cbd5e1 100%);border:1px solid rgba(148,163,184,.2);display:flex;align-items:center;justify-content:center;overflow:hidden;color:#0f172a;font-weight:800}
.catalog-preview-mini img{width:100%;height:100%;object-fit:cover}
.catalog-cell-title{display:flex;flex-direction:column;gap:4px}
.catalog-cell-title strong{font-size:16px;font-weight:800}
.catalog-cell-title small{color:#64748b}
.catalog-badge{display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:800}
.catalog-badge-invitation{background:#eff6ff;color:#1d4ed8}
.catalog-badge-accessoire{background:#fff7ed;color:#ea580c}
.catalog-badge-chevalet{background:#ecfeff;color:#0f766e}
.catalog-price-pill{display:inline-flex;padding:8px 12px;border-radius:999px;background:#f8fafc;color:#0f172a;font-weight:800}
.catalog-status{display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:800}
.catalog-status-active{background:#ecfdf5;color:#166534}
.catalog-status-inactive{background:#fef2f2;color:#991b1b}
.catalog-row-actions{display:flex;align-items:center;gap:10px}
.catalog-actions-column{width:120px}
.catalog-icon-button{width:42px;height:42px;border-radius:14px;border:1px solid rgba(148,163,184,.24);background:#fff;color:#0f172a;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease}
.catalog-icon-button:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(15,23,42,.10);border-color:rgba(15,23,42,.18)}
.catalog-icon-danger{color:#b91c1c;border-color:rgba(239,68,68,.24)}
.catalog-hidden-form{display:none}
.catalog-empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:48px 16px;color:#64748b;text-align:center}
.catalog-toggle{display:inline-flex;align-items:center;gap:10px;color:#334155;font-weight:700}
.catalog-flash{margin-bottom:16px;padding:14px 16px;border-radius:16px;font-weight:700}
.catalog-flash-success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
.catalog-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.catalog-modal{position:fixed;inset:0;z-index:1200;display:flex;align-items:center;justify-content:center;padding:24px}
.catalog-modal[hidden]{display:none}
.catalog-modal-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.62);backdrop-filter:blur(6px)}
.catalog-modal-dialog{position:relative;width:min(760px,100%);max-height:calc(100vh - 48px);overflow:auto;border-radius:28px;background:#fff;padding:28px;box-shadow:0 30px 80px rgba(15,23,42,.28)}
.catalog-modal-close{position:absolute;top:18px;right:18px;width:40px;height:40px;border:0;border-radius:999px;background:#f8fafc;color:#0f172a;font-size:26px;line-height:1;cursor:pointer}
.catalog-modal-header{margin-bottom:22px}
.catalog-modal-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:#ecfeff;color:#0f766e;font-size:12px;font-weight:800;text-transform:uppercase}
.catalog-modal-header h2{margin:14px 0 8px;font-size:28px;font-weight:800;color:#0f172a}
.catalog-modal-header p{margin:0;color:#64748b;line-height:1.6}
.catalog-modal-form{display:grid;gap:18px}
.catalog-modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.catalog-modal-grid label{display:flex;flex-direction:column;gap:8px;color:#0f172a;font-weight:700}
.catalog-modal-grid input,.catalog-modal-grid select{min-height:50px;border:1px solid #d7deea;border-radius:16px;padding:0 14px;background:#fff}
.catalog-modal-grid input[type=file]{padding:12px 14px}
.catalog-modal-preview{display:flex;justify-content:flex-start}
.catalog-modal-preview-box{width:132px;height:132px;border-radius:22px;border:1px dashed rgba(148,163,184,.42);background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#64748b;font-weight:700;text-align:center;padding:12px}
.catalog-modal-preview-box img{width:100%;height:100%;object-fit:cover}
.catalog-modal-actions{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.catalog-modal-action-row{display:flex;gap:12px;flex-wrap:wrap}
body.catalog-modal-open{overflow:hidden}
@media (max-width: 960px){
    .catalog-hero-actions{justify-items:start}
    .catalog-stats{justify-content:flex-start}
}
@media (max-width: 820px){
    .catalog-modal-grid{grid-template-columns:1fr}
    .catalog-modal-actions{align-items:flex-start;flex-direction:column}
}
@media (max-width: 760px){
    .catalog-head-inline{align-items:flex-start}
    .catalog-search-box{min-width:100%}
    .catalog-table thead{display:none}
    .catalog-table,.catalog-table tbody,.catalog-table tr,.catalog-table td{display:block;width:100%}
    .catalog-table tbody tr{padding:16px;border-bottom:1px solid rgba(226,232,240,.8)}
    .catalog-table tbody tr:last-child{border-bottom:0}
    .catalog-table tbody td{padding:10px 0;border-bottom:0}
    .catalog-table tbody td::before{content:attr(data-label);display:block;margin-bottom:6px;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const modals = Array.from(document.querySelectorAll('.catalog-modal'));
    const editModal = document.getElementById('catalogEditModal');
    const editIdInput = document.getElementById('catalogEditId');
    const editNameInput = document.getElementById('catalogEditName');
    const editTypeInput = document.getElementById('catalogEditType');
    const editPriceInput = document.getElementById('catalogEditPrice');
    const editActiveInput = document.getElementById('catalogEditActive');
    const editPreviewImage = document.getElementById('catalogEditPreviewImage');
    const editPreviewEmpty = document.getElementById('catalogEditPreviewEmpty');

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }

        modal.hidden = false;
        body.classList.add('catalog-modal-open');
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.hidden = true;

        if (!modals.some(function (item) { return !item.hidden; })) {
            body.classList.remove('catalog-modal-open');
        }
    }

    function setPreview(imageElement, emptyElement, source) {
        if (!imageElement || !emptyElement) {
            return;
        }

        if (source) {
            imageElement.src = source;
            imageElement.hidden = false;
            emptyElement.hidden = true;
        } else {
            imageElement.removeAttribute('src');
            imageElement.hidden = true;
            emptyElement.hidden = false;
        }
    }

    document.querySelectorAll('[data-open-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            const modalId = trigger.getAttribute('data-open-modal');

            if (modalId === 'catalogEditModal') {
                editIdInput.value = trigger.getAttribute('data-cod-mod') || '0';
                editNameInput.value = trigger.getAttribute('data-name') || '';
                editTypeInput.value = trigger.getAttribute('data-type') || 'accessoires';
                editPriceInput.value = trigger.getAttribute('data-unit-price') || '';
                editActiveInput.checked = (trigger.getAttribute('data-is-active') || '1') === '1';
                setPreview(editPreviewImage, editPreviewEmpty, trigger.getAttribute('data-image') || '');
            }

            openModal(modalId);
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            closeModal(trigger.closest('.catalog-modal'));
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            modals.forEach(function (modal) {
                if (!modal.hidden) {
                    closeModal(modal);
                }
            });
        }
    });

    document.querySelectorAll('input[type="file"][data-preview-target]').forEach(function (input) {
        input.addEventListener('change', function () {
            const previewId = input.getAttribute('data-preview-target');
            const emptyId = input.getAttribute('data-preview-empty');
            const previewImage = document.getElementById(previewId);
            const previewEmpty = document.getElementById(emptyId);
            const file = input.files && input.files[0] ? input.files[0] : null;

            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (loadEvent) {
                setPreview(previewImage, previewEmpty, loadEvent.target ? loadEvent.target.result : '');
            };
            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('.catalog-delete-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            const formId = trigger.getAttribute('data-form-id');
            const form = document.getElementById(formId);
            const itemName = trigger.getAttribute('data-name') || 'ce catalogue';

            if (!form) {
                return;
            }

            if (typeof Swal === 'undefined') {
                if (window.confirm('Supprimer ' + itemName + ' ?')) {
                    form.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Supprimer ? ',
                text: 'Cette action supprimera l\'element si possible, sinon il sera archive s\'il est deja utilise.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, continuer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    const searchInput = document.getElementById('catalogTableSearch');
    const searchableRows = Array.from(document.querySelectorAll('.catalog-data-row'));
    const noSearchResultRow = document.getElementById('catalogNoSearchResult');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = (searchInput.value || '').trim().toLowerCase();
            let visibleCount = 0;

            searchableRows.forEach(function (row) {
                const haystack = (row.getAttribute('data-search-index') || '').toLowerCase();
                const isVisible = query === '' || haystack.indexOf(query) !== -1;
                row.hidden = !isVisible;

                if (isVisible) {
                    visibleCount += 1;
                }
            });

            if (noSearchResultRow) {
                noSearchResultRow.hidden = visibleCount !== 0 || query === '';
            }
        });
    }
});
</script>