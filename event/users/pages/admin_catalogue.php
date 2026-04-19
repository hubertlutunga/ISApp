<?php
$datasession = UserAccountService::currentSessionUser($pdo) ?? [];
if ((string) ($datasession['type_user'] ?? '') !== '1') {
    PageRouter::redirect('index.php?page=logout');
}

EventOrderService::ensureCatalogInfrastructure($pdo);

$flash = null;
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['save_catalog_model'])) {
            EventOrderService::upsertCatalogModel(
                $pdo,
                $_POST,
                $_FILES['catalog_image'] ?? null,
                '../images/modeleis'
            );
            $flash = 'Catalogue mis a jour avec succes.';
        }
    } catch (Throwable $exception) {
        $flash = $exception->getMessage();
        $flashType = 'error';
    }
}

$catalogModels = EventOrderService::listCatalogModels($pdo);
$groupedCatalog = [
    'accessoires' => [],
    'invitation' => [],
    'chevalet' => [],
];

foreach ($catalogModels as $catalogModel) {
    $group = (string) ($catalogModel['type_mod'] ?? '');
    if (!isset($groupedCatalog[$group])) {
        $groupedCatalog[$group] = [];
    }
    $groupedCatalog[$group][] = $catalogModel;
}

function renderCatalogRows(array $rows, bool $showPrice): void
{
    foreach ($rows as $row) {
        $imagePath = trim((string) ($row['image'] ?? ''));
        $typeMod = (string) ($row['type_mod'] ?? '');
        ?>
        <form method="post" enctype="multipart/form-data" class="catalog-row">
            <input type="hidden" name="cod_mod" value="<?php echo (int) ($row['cod_mod'] ?? 0); ?>">
            <input type="hidden" name="save_catalog_model" value="1">

            <div class="catalog-row-main">
                <div class="catalog-preview">
                    <?php if ($imagePath !== '') { ?>
                    <img src="../images/modeleis/<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($row['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php } else { ?>
                    <div class="catalog-preview-empty">Apercu</div>
                    <?php } ?>
                </div>

                <div class="catalog-fields">
                    <div class="catalog-grid">
                        <label>
                            <span>Nom</span>
                            <input type="text" name="nom" value="<?php echo htmlspecialchars((string) ($row['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                        </label>

                        <label>
                            <span>Type</span>
                            <select name="type_mod" required>
                                <option value="accessoires" <?php echo $typeMod === 'accessoires' ? 'selected' : ''; ?>>Accessoire</option>
                                <option value="invitation" <?php echo $typeMod === 'invitation' ? 'selected' : ''; ?>>Modele invitation</option>
                                <option value="chevalet" <?php echo $typeMod === 'chevalet' ? 'selected' : ''; ?>>Modele chevalet</option>
                            </select>
                        </label>

                        <?php if ($showPrice) { ?>
                        <label>
                            <span>Prix unitaire ($)</span>
                            <input type="number" step="0.01" min="0" name="unit_price" value="<?php echo htmlspecialchars((string) ($row['unit_price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </label>
                        <?php } else { ?>
                        <input type="hidden" name="unit_price" value="<?php echo htmlspecialchars((string) ($row['unit_price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php } ?>

                        <label>
                            <span>Nouvelle image</span>
                            <input type="file" name="catalog_image" accept="image/png,image/jpeg,image/jpg,image/gif">
                        </label>
                    </div>

                    <div class="catalog-actions">
                        <label class="catalog-toggle">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" <?php echo ((int) ($row['is_active'] ?? 1)) === 1 ? 'checked' : ''; ?>>
                            <span>Actif</span>
                        </label>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </div>
        </form>
        <?php
    }
}
?>

<div class="wrapper">
    <?php include('header_admin.php'); ?>

    <div class="content-wrapper">
        <div class="container-full">
            <div class="container py-30">
                <div class="catalog-hero">
                    <span class="catalog-kicker">Administration</span>
                    <h1>Catalogue des modeles et tarifs</h1>
                    <p>Gerez les accessoires visibles au client, les prix unitaires et les modeles d'invitation ou de chevalet depuis un seul ecran.</p>
                </div>

                <?php if ($flash !== null) { ?>
                <div class="catalog-flash catalog-flash-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php } ?>

                <div class="catalog-layout">
                    <section class="catalog-card">
                        <div class="catalog-head">
                            <h2>Tarifs des accessoires</h2>
                            <p>Ces prix sont utilises dans l'etape de total de commande.</p>
                        </div>
                        <?php renderCatalogRows($groupedCatalog['accessoires'], true); ?>
                    </section>

                    <section class="catalog-card">
                        <div class="catalog-head">
                            <h2>Modeles d'invitation</h2>
                            <p>Ajoutez ou modifiez les modeles visibles dans la galerie de selection ainsi que leur prix unitaire.</p>
                        </div>
                        <?php renderCatalogRows($groupedCatalog['invitation'], true); ?>
                    </section>

                    <section class="catalog-card">
                        <div class="catalog-head">
                            <h2>Modeles de chevalet</h2>
                            <p>Administrez les variantes de chevalet de table disponibles a la commande.</p>
                        </div>
                        <?php renderCatalogRows($groupedCatalog['chevalet'], false); ?>
                    </section>

                    <section class="catalog-card">
                        <div class="catalog-head">
                            <h2>Ajouter un nouvel element</h2>
                            <p>Vous pouvez ajouter un nouvel accessoire facture ou un nouveau modele visuel.</p>
                        </div>

                        <form method="post" enctype="multipart/form-data" class="catalog-create-form">
                            <input type="hidden" name="save_catalog_model" value="1">

                            <div class="catalog-grid">
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
                                    <input type="file" name="catalog_image" accept="image/png,image/jpeg,image/jpg,image/gif">
                                </label>
                            </div>

                            <div class="catalog-actions">
                                <label class="catalog-toggle">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" checked>
                                    <span>Actif des la creation</span>
                                </label>
                                <button type="submit" class="btn btn-success">Ajouter</button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</div>

<style>
html, body{height:auto !important; overflow-y:auto !important}
body.layout-top-nav.fixed{overflow-y:auto !important}
.wrapper, .content-wrapper, .container-full{height:auto !important; min-height:0 !important; overflow:visible !important}
.catalog-hero{padding:28px 30px;border-radius:30px;background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);color:#fff;box-shadow:0 24px 48px rgba(15,23,42,.18);margin-bottom:24px}
.catalog-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-size:12px;font-weight:800;text-transform:uppercase}
.catalog-hero h1{margin:16px 0 8px;font-size:32px;font-weight:800}
.catalog-hero p{margin:0;max-width:760px;color:rgba(255,255,255,.84);line-height:1.6}
.catalog-layout{display:grid;gap:20px}
.catalog-card{background:#fff;border-radius:24px;padding:24px;box-shadow:0 18px 40px rgba(15,23,42,.08)}
.catalog-head h2{margin:0 0 6px;color:#0f172a;font-size:24px;font-weight:800}
.catalog-head p{margin:0 0 18px;color:#64748b}
.catalog-row,.catalog-create-form{border:1px solid rgba(148,163,184,.2);border-radius:20px;padding:18px;background:#f8fafc}
.catalog-row + .catalog-row{margin-top:14px}
.catalog-row-main{display:grid;grid-template-columns:120px minmax(0,1fr);gap:18px;align-items:start}
.catalog-preview img,.catalog-preview-empty{width:120px;height:120px;border-radius:18px;border:1px solid rgba(148,163,184,.2);object-fit:cover;background:#fff;display:flex;align-items:center;justify-content:center;color:#64748b}
.catalog-fields{display:flex;flex-direction:column;gap:14px}
.catalog-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.catalog-grid label{display:flex;flex-direction:column;gap:8px;color:#0f172a;font-weight:700}
.catalog-grid input,.catalog-grid select{min-height:48px;border:1px solid #d7deea;border-radius:14px;padding:0 14px;background:#fff}
.catalog-grid input[type=file]{padding:10px 14px}
.catalog-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.catalog-toggle{display:inline-flex;align-items:center;gap:10px;color:#334155;font-weight:700}
.catalog-flash{margin-bottom:16px;padding:14px 16px;border-radius:16px;font-weight:700}
.catalog-flash-success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
.catalog-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
@media (max-width: 900px){.catalog-row-main{grid-template-columns:1fr}.catalog-grid{grid-template-columns:1fr}.catalog-preview img,.catalog-preview-empty{width:100%;max-width:180px}}
</style>