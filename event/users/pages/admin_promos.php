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

usort($promoCodes, static function (array $leftRow, array $rightRow): int {
    return strcmp(
        mb_strtolower(trim((string) ($leftRow['code'] ?? ''))),
        mb_strtolower(trim((string) ($rightRow['code'] ?? '')))
    );
});

$promoTotal = count($promoCodes);
$promoActiveTotal = count(array_filter($promoCodes, static fn(array $row): bool => ((int) ($row['is_active'] ?? 1)) === 1));
$promoAverageReduction = $promoTotal > 0
    ? array_sum(array_map(static fn(array $row): float => (float) ($row['reduction_percent'] ?? 0), $promoCodes)) / $promoTotal
    : 0;
?>

<div class="wrapper">
    <?php include('header_admin.php'); ?>

    <div class="content-wrapper">
        <div class="container-full">
            <div class="container py-30">
                <div class="promo-admin-hero">
                    <div class="promo-admin-hero-copy">
                        <span class="promo-admin-kicker">Administration</span>
                        <h1>Gestion complete des codes promo</h1>
                        <p>Gerez vos promotions dans une seule interface claire : tableau global, ajout rapide, modification en modale et suppression confirmee.</p>
                    </div>

                    <div class="promo-admin-hero-actions">
                        <div class="promo-admin-stats">
                            <article class="promo-admin-stat-pill">
                                <strong><?php echo (int) $promoTotal; ?></strong>
                                <span>Codes</span>
                            </article>
                            <article class="promo-admin-stat-pill">
                                <strong><?php echo (int) $promoActiveTotal; ?></strong>
                                <span>Actifs</span>
                            </article>
                            <article class="promo-admin-stat-pill">
                                <strong><?php echo htmlspecialchars(number_format($promoAverageReduction, 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?>%</strong>
                                <span>Moyenne</span>
                            </article>
                        </div>

                        <button type="button" class="promo-admin-primary-action" data-open-modal="promoCreateModal">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            <span>Ajouter un code promo</span>
                        </button>
                    </div>
                </div>

                <?php if ($flash !== null) { ?>
                <div class="promo-admin-flash promo-admin-flash-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php } ?>

                <section class="promo-admin-card">
                    <div class="promo-admin-head promo-admin-head-inline">
                        <div>
                            <h2>Liste des promotions</h2>
                            <p>Modifiez chaque ligne en modale, activez ou desactivez un code, et supprimez-le avec confirmation.</p>
                        </div>

                        <label class="promo-admin-search-box" aria-label="Recherche promotions">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            <input type="search" id="promoTableSearch" placeholder="Rechercher un code, un libelle ou un statut...">
                        </label>
                    </div>

                    <div class="promo-admin-table-shell">
                        <table class="promo-admin-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Libelle</th>
                                    <th>Reduction</th>
                                    <th>Statut</th>
                                    <th>Mise a jour</th>
                                    <th class="promo-admin-actions-column">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($promoCodes === []) { ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="promo-admin-empty-state">
                                            <strong>Aucun code promo disponible.</strong>
                                            <span>Ajoutez votre premiere promotion pour commencer l’administration.</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>

                                <?php foreach ($promoCodes as $promoCode) {
                                    $promoId = (int) ($promoCode['cod_promo'] ?? 0);
                                    $promoCodeValue = trim((string) ($promoCode['code'] ?? ''));
                                    $promoLabel = trim((string) ($promoCode['label'] ?? ''));
                                    $promoReduction = (float) ($promoCode['reduction_percent'] ?? 0);
                                    $promoActive = ((int) ($promoCode['is_active'] ?? 1)) === 1;
                                    $promoUpdatedAt = trim((string) ($promoCode['updated_at'] ?? ''));
                                    $promoUpdatedDisplay = $promoUpdatedAt !== '' ? date('d/m/Y H:i', strtotime($promoUpdatedAt)) : 'Non renseignee';
                                    $searchIndex = mb_strtolower(trim(implode(' ', [
                                        $promoCodeValue,
                                        $promoLabel,
                                        number_format($promoReduction, 2, '.', ' '),
                                        $promoActive ? 'actif active' : 'inactif desactive',
                                        '#' . $promoId,
                                    ])));
                                ?>
                                <tr class="promo-admin-data-row" data-search-index="<?php echo htmlspecialchars($searchIndex, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td data-label="Code">
                                        <div class="promo-admin-cell-title">
                                            <strong><?php echo htmlspecialchars($promoCodeValue, ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small>#<?php echo $promoId; ?></small>
                                        </div>
                                    </td>
                                    <td data-label="Libelle">
                                        <?php echo htmlspecialchars($promoLabel !== '' ? $promoLabel : $promoCodeValue, ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td data-label="Reduction">
                                        <span class="promo-admin-percent-pill"><?php echo htmlspecialchars(number_format($promoReduction, 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?>%</span>
                                    </td>
                                    <td data-label="Statut">
                                        <span class="promo-admin-status <?php echo $promoActive ? 'promo-admin-status-active' : 'promo-admin-status-inactive'; ?>">
                                            <?php echo $promoActive ? 'Actif' : 'Inactif'; ?>
                                        </span>
                                    </td>
                                    <td data-label="Mise a jour">
                                        <?php echo htmlspecialchars($promoUpdatedDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td data-label="Actions" class="promo-admin-row-actions">
                                        <button
                                            type="button"
                                            class="promo-admin-icon-button promo-admin-edit-trigger"
                                            data-open-modal="promoEditModal"
                                            data-cod-promo="<?php echo $promoId; ?>"
                                            data-code="<?php echo htmlspecialchars($promoCodeValue, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-label="<?php echo htmlspecialchars($promoLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-reduction-percent="<?php echo htmlspecialchars((string) ($promoCode['reduction_percent'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-is-active="<?php echo $promoActive ? '1' : '0'; ?>"
                                            aria-label="Modifier <?php echo htmlspecialchars($promoCodeValue, ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Modifier"
                                        >
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="promo-admin-icon-button promo-admin-icon-danger promo-admin-delete-trigger"
                                            data-form-id="promoDeleteForm<?php echo $promoId; ?>"
                                            data-name="<?php echo htmlspecialchars($promoCodeValue, ENT_QUOTES, 'UTF-8'); ?>"
                                            aria-label="Supprimer <?php echo htmlspecialchars($promoCodeValue, ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Supprimer"
                                        >
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>

                                        <form method="post" id="promoDeleteForm<?php echo $promoId; ?>" class="promo-admin-hidden-form">
                                            <input type="hidden" name="cod_promo" value="<?php echo $promoId; ?>">
                                            <input type="hidden" name="delete_promo_code" value="1">
                                        </form>
                                    </td>
                                </tr>
                                <?php } ?>

                                <tr id="promoNoSearchResult" hidden>
                                    <td colspan="6">
                                        <div class="promo-admin-empty-state">
                                            <strong>Aucun resultat.</strong>
                                            <span>Essayez un autre code, libelle ou mot-cle.</span>
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

<div class="promo-admin-modal" id="promoCreateModal" hidden>
    <div class="promo-admin-modal-backdrop" data-close-modal></div>
    <div class="promo-admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="promoCreateTitle">
        <button type="button" class="promo-admin-modal-close" data-close-modal aria-label="Fermer">&times;</button>
        <div class="promo-admin-modal-header">
            <span class="promo-admin-modal-kicker">Nouveau code promo</span>
            <h2 id="promoCreateTitle">Ajouter un code promo</h2>
            <p>Creez une nouvelle reduction applicable au formulaire de commande.</p>
        </div>

        <form method="post" class="promo-admin-modal-form">
            <input type="hidden" name="save_promo_code" value="1">

            <div class="promo-admin-modal-grid">
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

            <div class="promo-admin-modal-actions">
                <label class="promo-admin-toggle">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Actif des la creation</span>
                </label>

                <div class="promo-admin-modal-action-row">
                    <button type="button" class="promo-admin-ghost-action" data-close-modal>Annuler</button>
                    <button type="submit" class="promo-admin-primary-submit">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="promo-admin-modal" id="promoEditModal" hidden>
    <div class="promo-admin-modal-backdrop" data-close-modal></div>
    <div class="promo-admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="promoEditTitle">
        <button type="button" class="promo-admin-modal-close" data-close-modal aria-label="Fermer">&times;</button>
        <div class="promo-admin-modal-header">
            <span class="promo-admin-modal-kicker">Edition</span>
            <h2 id="promoEditTitle">Modifier le code promo</h2>
            <p>Ajustez le code, le libelle, le pourcentage ou son statut depuis cette modale.</p>
        </div>

        <form method="post" class="promo-admin-modal-form" id="promoEditForm">
            <input type="hidden" name="save_promo_code" value="1">
            <input type="hidden" name="cod_promo" id="promoEditId" value="0">

            <div class="promo-admin-modal-grid">
                <label>
                    <span>Code</span>
                    <input type="text" name="code" id="promoEditCode" required>
                </label>

                <label>
                    <span>Libelle</span>
                    <input type="text" name="label" id="promoEditLabel">
                </label>

                <label>
                    <span>Reduction (%)</span>
                    <input type="number" name="reduction_percent" id="promoEditReduction" step="0.01" min="0.01" max="100" required>
                </label>
            </div>

            <div class="promo-admin-modal-actions">
                <label class="promo-admin-toggle">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="promoEditActive">
                    <span>Code actif</span>
                </label>

                <div class="promo-admin-modal-action-row">
                    <button type="button" class="promo-admin-ghost-action" data-close-modal>Annuler</button>
                    <button type="submit" class="promo-admin-primary-submit">Mettre a jour</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
html, body{height:auto !important;overflow-y:auto !important}
body.layout-top-nav.fixed{overflow-y:auto !important}
.wrapper,.content-wrapper,.container-full{height:auto !important;min-height:0 !important;overflow:visible !important}
.promo-admin-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;flex-wrap:wrap;padding:32px;border-radius:32px;background:radial-gradient(circle at top left,rgba(255,255,255,.16),transparent 34%),linear-gradient(135deg,#093b3b 0%,#0e5c5c 50%,#16948a 100%);color:#f8fffe;box-shadow:0 28px 56px rgba(9,59,59,.18);margin-bottom:24px}
.promo-admin-hero-copy{max-width:760px}
.promo-admin-kicker,.promo-admin-modal-kicker{display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.promo-admin-kicker{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.16)}
.promo-admin-modal-kicker{background:#dffaf6;color:#0f766e}
.promo-admin-hero h1{margin:16px 0 8px;font-size:34px;font-weight:800;color:#fff}
.promo-admin-hero p{margin:0;color:rgba(248,255,254,.86);line-height:1.7}
.promo-admin-hero-actions{display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;justify-content:flex-end}
.promo-admin-stats{display:flex;gap:12px;flex-wrap:wrap}
.promo-admin-stat-pill{min-width:118px;padding:14px 16px;border-radius:20px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);display:flex;flex-direction:column;gap:4px}
.promo-admin-stat-pill strong{font-size:22px;font-weight:800;line-height:1.1}
.promo-admin-stat-pill span{font-size:12px;color:rgba(248,255,254,.76);text-transform:uppercase;letter-spacing:.05em}
.promo-admin-primary-action,.promo-admin-primary-submit{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:52px;padding:0 22px;border:0;border-radius:18px;background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);color:#fff;font-size:15px;font-weight:800;box-shadow:0 18px 34px rgba(249,115,22,.22);cursor:pointer}
.promo-admin-card{background:linear-gradient(180deg,#ffffff 0%,#fbfefd 100%);border:1px solid rgba(15,118,110,.12);border-radius:28px;padding:24px;box-shadow:0 20px 46px rgba(15,23,42,.08)}
.promo-admin-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px}
.promo-admin-head-inline{flex-wrap:wrap;align-items:flex-end}
.promo-admin-head h2{margin:0 0 6px;font-size:28px;font-weight:800;color:#0f172a}
.promo-admin-head p{margin:0;color:#64748b;line-height:1.6}
.promo-admin-search-box{display:inline-flex;align-items:center;gap:10px;min-height:52px;padding:0 16px;border-radius:18px;border:1px solid rgba(15,118,110,.14);background:#f8fffe;min-width:320px}
.promo-admin-search-box i{color:#0f766e}
.promo-admin-search-box input{width:100%;border:0;background:transparent;outline:0;box-shadow:none}
.promo-admin-table-shell{border:1px solid rgba(148,163,184,.18);border-radius:22px;overflow:hidden;background:#fff}
.promo-admin-table{width:100%;border-collapse:collapse}
.promo-admin-table thead th{padding:16px 18px;background:#f0fdfa;color:#0f172a;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;text-align:left}
.promo-admin-table tbody td{padding:18px;border-top:1px solid rgba(226,232,240,.9);vertical-align:middle;color:#1e293b}
.promo-admin-cell-title{display:flex;flex-direction:column;gap:4px}
.promo-admin-cell-title strong{font-size:15px;color:#0f172a}
.promo-admin-cell-title small{color:#64748b}
.promo-admin-percent-pill{display:inline-flex;padding:8px 12px;border-radius:999px;background:#fff7ed;color:#c2410c;font-weight:800}
.promo-admin-status{display:inline-flex;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.promo-admin-status-active{background:#dcfce7;color:#166534}
.promo-admin-status-inactive{background:#fef2f2;color:#991b1b}
.promo-admin-row-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px}
.promo-admin-actions-column{text-align:right}
.promo-admin-icon-button{width:42px;height:42px;border:0;border-radius:14px;background:#ecfeff;color:#155e75;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .18s ease, box-shadow .18s ease}
.promo-admin-icon-button:hover{transform:translateY(-1px);box-shadow:0 12px 24px rgba(15,118,110,.16)}
.promo-admin-icon-danger{background:#fef2f2;color:#b91c1c}
.promo-admin-hidden-form{display:none}
.promo-admin-empty-state{display:grid;gap:6px;min-height:170px;align-content:center;justify-items:center;padding:22px;text-align:center;color:#64748b}
.promo-admin-empty-state strong{font-size:18px;color:#0f172a}
.promo-admin-flash{margin-bottom:16px;padding:14px 16px;border-radius:16px;font-weight:700}
.promo-admin-flash-success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
.promo-admin-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.promo-admin-modal{position:fixed;inset:0;z-index:12000;display:flex;align-items:center;justify-content:center;padding:20px}
.promo-admin-modal[hidden]{display:none}
.promo-admin-modal-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.64);backdrop-filter:blur(6px)}
.promo-admin-modal-dialog{position:relative;width:min(760px,100%);max-height:calc(100vh - 40px);overflow:auto;padding:28px;border-radius:28px;background:#fff;box-shadow:0 34px 80px rgba(15,23,42,.28)}
.promo-admin-modal-close{position:absolute;top:14px;right:14px;width:42px;height:42px;border:0;border-radius:999px;background:rgba(15,23,42,.06);color:#0f172a;font-size:26px;line-height:1;cursor:pointer}
.promo-admin-modal-header{margin-bottom:20px}
.promo-admin-modal-header h2{margin:12px 0 8px;font-size:28px;font-weight:800;color:#0f172a}
.promo-admin-modal-header p{margin:0;color:#64748b;line-height:1.7}
.promo-admin-modal-form{display:grid;gap:18px}
.promo-admin-modal-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.promo-admin-modal-grid label{display:flex;flex-direction:column;gap:8px;color:#0f172a;font-weight:700}
.promo-admin-modal-grid input{min-height:52px;border:1px solid rgba(148,163,184,.24);border-radius:16px;padding:0 14px;background:#fff;box-shadow:none}
.promo-admin-modal-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.promo-admin-modal-action-row{display:flex;gap:10px;flex-wrap:wrap}
.promo-admin-toggle{display:inline-flex;align-items:center;gap:10px;color:#334155;font-weight:700}
.promo-admin-ghost-action{display:inline-flex;align-items:center;justify-content:center;min-height:52px;padding:0 18px;border:1px solid rgba(148,163,184,.24);border-radius:18px;background:#fff;color:#0f172a;font-weight:800;cursor:pointer}
body.promo-admin-modal-open{overflow:hidden}
@media (max-width: 980px){
    .promo-admin-modal-grid{grid-template-columns:1fr}
}
@media (max-width: 860px){
    .promo-admin-search-box{min-width:100%}
    .promo-admin-table thead{display:none}
    .promo-admin-table tbody,.promo-admin-table tr,.promo-admin-table td{display:block;width:100%}
    .promo-admin-table tr{border-top:1px solid rgba(226,232,240,.9)}
    .promo-admin-table tbody td{border-top:0;padding:10px 18px}
    .promo-admin-table tbody td::before{content:attr(data-label);display:block;margin-bottom:6px;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
    .promo-admin-row-actions{justify-content:flex-start;padding-top:4px}
}
@media (max-width: 640px){
    .promo-admin-hero{padding:24px;border-radius:24px}
    .promo-admin-hero h1{font-size:28px}
    .promo-admin-card{padding:18px;border-radius:24px}
    .promo-admin-modal-dialog{padding:22px}
    .promo-admin-primary-action,.promo-admin-primary-submit,.promo-admin-ghost-action{width:100%}
    .promo-admin-modal-actions,.promo-admin-modal-action-row{flex-direction:column;align-items:stretch}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const modals = document.querySelectorAll('.promo-admin-modal');
    const openTriggers = document.querySelectorAll('[data-open-modal]');
    const closeTriggers = document.querySelectorAll('[data-close-modal]');
    const editButtons = document.querySelectorAll('.promo-admin-edit-trigger');
    const deleteButtons = document.querySelectorAll('.promo-admin-delete-trigger');
    const searchInput = document.getElementById('promoTableSearch');
    const dataRows = Array.from(document.querySelectorAll('.promo-admin-data-row'));
    const noResultRow = document.getElementById('promoNoSearchResult');

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }

        modal.hidden = false;
        body.classList.add('promo-admin-modal-open');
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        if (!Array.from(modals).some((candidate) => !candidate.hidden)) {
            body.classList.remove('promo-admin-modal-open');
        }
    }

    openTriggers.forEach((trigger) => {
        trigger.addEventListener('click', function () {
            const modalId = trigger.getAttribute('data-open-modal');
            openModal(modalId);
        });
    });

    closeTriggers.forEach((trigger) => {
        trigger.addEventListener('click', function () {
            closeModal(trigger.closest('.promo-admin-modal'));
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        modals.forEach((modal) => {
            if (!modal.hidden) {
                closeModal(modal);
            }
        });
    });

    editButtons.forEach((button) => {
        button.addEventListener('click', function () {
            document.getElementById('promoEditId').value = button.getAttribute('data-cod-promo') || '0';
            document.getElementById('promoEditCode').value = button.getAttribute('data-code') || '';
            document.getElementById('promoEditLabel').value = button.getAttribute('data-label') || '';
            document.getElementById('promoEditReduction').value = button.getAttribute('data-reduction-percent') || '';
            document.getElementById('promoEditActive').checked = (button.getAttribute('data-is-active') || '0') === '1';
        });
    });

    deleteButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const formId = button.getAttribute('data-form-id');
            const promoName = button.getAttribute('data-name') || 'ce code promo';
            const form = formId ? document.getElementById(formId) : null;
            if (!form) {
                return;
            }

            Swal.fire({
                title: 'Supprimer ce code promo ?',
                text: 'Le code ' + promoName + ' sera retire definitivement.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = (searchInput.value || '').trim().toLowerCase();
            let visibleCount = 0;

            dataRows.forEach((row) => {
                const haystack = (row.getAttribute('data-search-index') || '').toLowerCase();
                const matches = query === '' || haystack.indexOf(query) !== -1;
                row.hidden = !matches;
                if (matches) {
                    visibleCount += 1;
                }
            });

            if (noResultRow) {
                noResultRow.hidden = visibleCount > 0 || query === '';
            }
        });
    }
});
</script>