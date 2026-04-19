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
        if (isset($_POST['save_promo_code'])) {
            EventOrderService::upsertPromoCode($pdo, $_POST);
            $flash = 'Code promo enregistre avec succes.';
        }

        if (isset($_POST['delete_promo_code'])) {
            EventOrderService::deletePromoCode($pdo, (int) ($_POST['cod_promo'] ?? 0));
            $flash = 'Code promo supprime avec succes.';
        }
    } catch (Throwable $exception) {
        $flash = $exception->getMessage();
        $flashType = 'error';
    }
}

$promoCodes = EventOrderService::listPromoCodes($pdo);
?>

<div class="wrapper">
    <?php include('header_admin.php'); ?>

    <div class="content-wrapper">
        <div class="container-full">
            <div class="container py-30">
                <div class="promo-hero">
                    <span class="promo-kicker">Administration</span>
                    <h1>Codes promo</h1>
                    <p>Ajoutez, modifiez ou desactivez des reductions par pourcentage utilisees dans le formulaire de commande.</p>
                </div>

                <?php if ($flash !== null) { ?>
                <div class="promo-flash promo-flash-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php } ?>

                <div class="promo-layout">
                    <section class="promo-card">
                        <div class="promo-head">
                            <h2>Ajouter un code promo</h2>
                            <p>Le pourcentage est applique sur le sous-total de la commande.</p>
                        </div>

                        <form method="post" class="promo-form">
                            <input type="hidden" name="save_promo_code" value="1">

                            <div class="promo-grid">
                                <label>
                                    <span>Code</span>
                                    <input type="text" name="code" placeholder="Ex. BIENVENUE10" required>
                                </label>

                                <label>
                                    <span>Libelle</span>
                                    <input type="text" name="label" placeholder="Ex. Campagne bienvenue">
                                </label>

                                <label>
                                    <span>Reduction (%)</span>
                                    <input type="number" name="reduction_percent" step="0.01" min="0.01" max="100" placeholder="10" required>
                                </label>
                            </div>

                            <div class="promo-actions">
                                <label class="promo-toggle">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" checked>
                                    <span>Actif des la creation</span>
                                </label>
                                <button type="submit" class="btn btn-success">Ajouter</button>
                            </div>
                        </form>
                    </section>

                    <section class="promo-card">
                        <div class="promo-head">
                            <h2>Codes promo existants</h2>
                            <p>Vous pouvez corriger un pourcentage, renommer un code ou le desactiver.</p>
                        </div>

                        <div class="promo-list">
                            <?php foreach ($promoCodes as $promoCode) { ?>
                            <form method="post" class="promo-row">
                                <input type="hidden" name="cod_promo" value="<?php echo (int) ($promoCode['cod_promo'] ?? 0); ?>">
                                <input type="hidden" name="save_promo_code" value="1">

                                <div class="promo-grid">
                                    <label>
                                        <span>Code</span>
                                        <input type="text" name="code" value="<?php echo htmlspecialchars((string) ($promoCode['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </label>

                                    <label>
                                        <span>Libelle</span>
                                        <input type="text" name="label" value="<?php echo htmlspecialchars((string) ($promoCode['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    </label>

                                    <label>
                                        <span>Reduction (%)</span>
                                        <input type="number" name="reduction_percent" step="0.01" min="0.01" max="100" value="<?php echo htmlspecialchars((string) ($promoCode['reduction_percent'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </label>
                                </div>

                                <div class="promo-actions">
                                    <label class="promo-toggle">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" <?php echo ((int) ($promoCode['is_active'] ?? 1)) === 1 ? 'checked' : ''; ?>>
                                        <span>Actif</span>
                                    </label>

                                    <div class="promo-button-row">
                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        <button type="submit" name="delete_promo_code" value="1" class="btn btn-danger" onclick="return confirm('Supprimer ce code promo ?');">Supprimer</button>
                                    </div>
                                </div>
                            </form>
                            <?php } ?>
                        </div>
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
.promo-hero{padding:28px 30px;border-radius:30px;background:linear-gradient(135deg,#14532d 0%,#15803d 100%);color:#fff;box-shadow:0 24px 48px rgba(20,83,45,.18);margin-bottom:24px}
.promo-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-size:12px;font-weight:800;text-transform:uppercase}
.promo-hero h1{margin:16px 0 8px;font-size:32px;font-weight:800}
.promo-hero p{margin:0;max-width:760px;color:rgba(255,255,255,.84);line-height:1.6}
.promo-layout{display:grid;gap:20px}
.promo-card{background:#fff;border-radius:24px;padding:24px;box-shadow:0 18px 40px rgba(15,23,42,.08)}
.promo-head h2{margin:0 0 6px;color:#0f172a;font-size:24px;font-weight:800}
.promo-head p{margin:0 0 18px;color:#64748b}
.promo-form,.promo-row{border:1px solid rgba(148,163,184,.2);border-radius:20px;padding:18px;background:#f8fafc}
.promo-row + .promo-row{margin-top:14px}
.promo-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.promo-grid label{display:flex;flex-direction:column;gap:8px;color:#0f172a;font-weight:700}
.promo-grid input{min-height:48px;border:1px solid #d7deea;border-radius:14px;padding:0 14px;background:#fff}
.promo-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:16px}
.promo-button-row{display:flex;gap:10px;flex-wrap:wrap}
.promo-toggle{display:inline-flex;align-items:center;gap:10px;color:#334155;font-weight:700}
.promo-flash{margin-bottom:16px;padding:14px 16px;border-radius:16px;font-weight:700}
.promo-flash-success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
.promo-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
@media (max-width: 900px){.promo-grid{grid-template-columns:1fr}}
</style>