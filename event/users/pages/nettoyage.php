<?php
$datasession = UserAccountService::currentSessionUser($pdo) ?? [];
if ((string) ($datasession['type_user'] ?? '') !== '1') {
    PageRouter::redirect('index.php?page=logout');
}

$photoDir = realpath(__DIR__ . '/../../photosevent') ?: (__DIR__ . '/../../photosevent');
$currentYear = (int) date('Y');
$selectedYear = (int) ($_POST['cleanup_year'] ?? $_GET['year'] ?? $currentYear);
$flash = null;
$flashType = 'success';
$cleanupResult = null;

$formatBytes = static function (int $bytes): string {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2, ',', ' ') . ' Go';
    }
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2, ',', ' ') . ' Mo';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 2, ',', ' ') . ' Ko';
    }

    return $bytes . ' o';
};

try {
    $selectedYear = EventPhotoCleanupService::normalizeYear($selectedYear);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_photo_cleanup'])) {
        if (trim((string) ($_POST['confirm_cleanup'] ?? '')) !== 'SUPPRIMER') {
            throw new RuntimeException('Tapez SUPPRIMER pour confirmer le nettoyage.');
        }

        $cleanupResult = EventPhotoCleanupService::cleanupYear(
            $pdo,
            $photoDir,
            $selectedYear,
            isset($_POST['delete_orphan_files'])
        );

        $flash = 'Nettoyage termine : ' . (int) $cleanupResult['deleted_db_rows'] . ' ligne(s) BDD supprimee(s), '
            . (int) $cleanupResult['deleted_server_files'] . ' fichier(s) lie(s) supprime(s), '
            . (int) $cleanupResult['deleted_orphan_files'] . ' fichier(s) orphelin(s) supprime(s).';
    }
} catch (Throwable $exception) {
    $flash = $exception->getMessage();
    $flashType = 'error';
}

try {
    $summary = EventPhotoCleanupService::summarize($pdo, $photoDir, $selectedYear);
    $availableYears = EventPhotoCleanupService::availableYears($pdo);
} catch (Throwable $exception) {
    $summary = [
        'db_photo_count' => 0,
        'server_file_count' => 0,
        'server_file_bytes' => 0,
        'year_db_count' => 0,
        'year_linked_existing_count' => 0,
        'year_missing_file_count' => 0,
        'year_linked_bytes' => 0,
        'year_orphan_count' => 0,
        'year_orphan_bytes' => 0,
        'year_orphan_files' => [],
    ];
    $availableYears = [];
    $flash = $flash ?? $exception->getMessage();
    $flashType = 'error';
}

if (!in_array($selectedYear, $availableYears, true)) {
    array_unshift($availableYears, $selectedYear);
    $availableYears = array_values(array_unique($availableYears));
    rsort($availableYears);
}
?>

<div class="wrapper cleanup-wrapper">
    <?php include('header_admin.php'); ?>

    <div class="content-wrapper cleanup-content-wrapper">
        <div class="container-full">
            <div class="container py-30 cleanup-page">
                <section class="cleanup-hero">
                    <div>
                        <span class="cleanup-kicker">Administration</span>
                        <h1>Nettoyage des photos</h1>
                        <p>Supprimez les photos d'une annee dans la base de donnees et dans le dossier serveur des commandes.</p>
                        <strong class="cleanup-path"><?php echo htmlspecialchars($photoDir, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <div class="cleanup-year-card">
                        <span>Année sélectionnée</span>
                        <strong><?php echo (int) $selectedYear; ?></strong>
                    </div>
                </section>

                <?php if ($flash !== null) { ?>
                <div class="cleanup-flash cleanup-flash-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php } ?>

                <section class="cleanup-stats-grid">
                    <article class="cleanup-stat-card">
                        <span>Total BDD</span>
                        <strong><?php echo (int) $summary['db_photo_count']; ?></strong>
                        <small>lignes dans photos_event</small>
                    </article>
                    <article class="cleanup-stat-card">
                        <span>Total serveur</span>
                        <strong><?php echo (int) $summary['server_file_count']; ?></strong>
                        <small><?php echo htmlspecialchars($formatBytes((int) $summary['server_file_bytes']), ENT_QUOTES, 'UTF-8'); ?></small>
                    </article>
                    <article class="cleanup-stat-card cleanup-stat-danger">
                        <span>BDD <?php echo (int) $selectedYear; ?></span>
                        <strong><?php echo (int) $summary['year_db_count']; ?></strong>
                        <small><?php echo (int) $summary['year_linked_existing_count']; ?> fichier(s) trouve(s), <?php echo (int) $summary['year_missing_file_count']; ?> absent(s)</small>
                    </article>
                    <article class="cleanup-stat-card cleanup-stat-warning">
                        <span>Orphelins serveur <?php echo (int) $selectedYear; ?></span>
                        <strong><?php echo (int) $summary['year_orphan_count']; ?></strong>
                        <small><?php echo htmlspecialchars($formatBytes((int) $summary['year_orphan_bytes']), ENT_QUOTES, 'UTF-8'); ?></small>
                    </article>
                </section>

                <?php if ($cleanupResult !== null && (!empty($cleanupResult['failed_server_files']) || !empty($cleanupResult['failed_orphan_files']))) { ?>
                <div class="cleanup-flash cleanup-flash-error">
                    Certains fichiers n'ont pas pu etre supprimes. Verifiez les permissions du dossier serveur.
                </div>
                <?php } ?>

                <div class="cleanup-grid">
                    <section class="cleanup-card">
                        <div class="cleanup-card-head">
                            <h2>Choisir l'année</h2>
                            <p>La selection BDD se base sur la date de creation de la commande/evenement. Les fichiers orphelins se basent sur la date du fichier.</p>
                        </div>

                        <form method="get" class="cleanup-filter-form">
                            <input type="hidden" name="page" value="nettoyage">
                            <label for="year">Année à analyser</label>
                            <select name="year" id="year">
                                <?php foreach ($availableYears as $yearOption) { ?>
                                <option value="<?php echo (int) $yearOption; ?>" <?php echo (int) $yearOption === $selectedYear ? 'selected' : ''; ?>><?php echo (int) $yearOption; ?></option>
                                <?php } ?>
                            </select>
                            <button type="submit">Analyser</button>
                        </form>
                    </section>

                    <section class="cleanup-card cleanup-danger-zone">
                        <div class="cleanup-card-head">
                            <h2>Suppression définitive</h2>
                            <p>Cette action supprime les lignes BDD concernées et les fichiers correspondants dans le serveur.</p>
                        </div>

                        <form method="post" id="cleanupDeleteForm">
                            <input type="hidden" name="cleanup_year" value="<?php echo (int) $selectedYear; ?>">
                            <input type="hidden" name="run_photo_cleanup" value="1">

                            <label class="cleanup-check">
                                <input type="checkbox" name="delete_orphan_files" value="1">
                                <span>Supprimer aussi les fichiers orphelins du serveur pour <?php echo (int) $selectedYear; ?></span>
                            </label>

                            <label for="confirm_cleanup">Confirmation</label>
                            <input type="text" id="confirm_cleanup" name="confirm_cleanup" placeholder="Tapez SUPPRIMER" autocomplete="off">

                            <button type="submit" class="cleanup-delete-button" <?php echo ((int) $summary['year_db_count'] + (int) $summary['year_orphan_count']) <= 0 ? 'disabled' : ''; ?>>
                                <i class="fa fa-trash" aria-hidden="true"></i>
                                Supprimer les photos de <?php echo (int) $selectedYear; ?>
                            </button>
                        </form>
                    </section>
                </div>

                <section class="cleanup-card cleanup-table-card">
                    <div class="cleanup-card-head cleanup-card-inline">
                        <div>
                            <h2>Aperçu avant suppression</h2>
                            <p>Les 30 premières lignes BDD ciblées pour <?php echo (int) $selectedYear; ?>.</p>
                        </div>
                        <span class="cleanup-pill"><?php echo (int) $summary['year_db_count']; ?> ligne(s)</span>
                    </div>

                    <div class="cleanup-table-shell">
                        <table class="cleanup-table">
                            <thead>
                                <tr>
                                    <th>ID photo</th>
                                    <th>Evenement</th>
                                    <th>Fichier</th>
                                    <th>Date repère</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($summary['rows'] ?? [], 0, 30) as $photoRow) { ?>
                                <tr>
                                    <td>#<?php echo (int) ($photoRow['cod_photo'] ?? 0); ?></td>
                                    <td><?php echo (int) ($photoRow['cod_event'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($photoRow['nom_photo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($photoRow['reference_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <?php } ?>
                                <?php if (($summary['rows'] ?? []) === []) { ?>
                                <tr><td colspan="4" class="cleanup-empty">Aucune photo BDD pour cette année.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<style>
body.fixed .cleanup-wrapper{height:auto!important;min-height:100vh!important;overflow-x:hidden!important;overflow-y:visible!important}.cleanup-content-wrapper{height:auto!important;min-height:calc(100vh - 104px)!important;overflow:visible!important;padding-bottom:56px}.cleanup-content-wrapper .container-full{min-height:inherit;overflow:visible}.cleanup-page{padding-bottom:56px}
.cleanup-page{color:#0f172a}.cleanup-hero{display:grid;grid-template-columns:1fr auto;gap:24px;align-items:stretch;padding:30px;border-radius:30px;background:linear-gradient(135deg,#0f172a 0%,#164e63 58%,#0f766e 100%);box-shadow:0 24px 60px rgba(15,23,42,.22);color:#fff;margin-bottom:22px}.cleanup-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-weight:800;text-transform:uppercase;letter-spacing:.08em;font-size:12px}.cleanup-hero h1{margin:14px 0 8px;font-size:clamp(32px,4vw,54px);font-weight:900;letter-spacing:-.04em;color:#fff}.cleanup-hero p{max-width:780px;margin:0 0 16px;color:rgba(255,255,255,.82);font-weight:600}.cleanup-path{display:inline-flex;max-width:100%;padding:10px 14px;border-radius:14px;background:rgba(255,255,255,.12);word-break:break-all;color:#fff}.cleanup-year-card{min-width:190px;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.12);border-radius:24px;padding:22px;display:flex;flex-direction:column;justify-content:center;align-items:center;backdrop-filter:blur(10px)}.cleanup-year-card span{color:rgba(255,255,255,.72);font-weight:800}.cleanup-year-card strong{font-size:52px;line-height:1;color:#fff}.cleanup-flash{border-radius:18px;padding:14px 16px;margin-bottom:18px;font-weight:800}.cleanup-flash-success{background:#ecfdf5;color:#065f46;border:1px solid #bbf7d0}.cleanup-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.cleanup-stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:18px}.cleanup-stat-card,.cleanup-card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;box-shadow:0 16px 36px rgba(15,23,42,.07)}.cleanup-stat-card{padding:20px}.cleanup-stat-card span{display:block;color:#64748b;font-weight:900;text-transform:uppercase;letter-spacing:.06em;font-size:12px}.cleanup-stat-card strong{display:block;font-size:40px;line-height:1;margin:10px 0 4px;font-weight:900}.cleanup-stat-card small{color:#64748b;font-weight:700}.cleanup-stat-danger{background:linear-gradient(180deg,#fff 0%,#fef2f2 100%)}.cleanup-stat-warning{background:linear-gradient(180deg,#fff 0%,#fffbeb 100%)}.cleanup-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}.cleanup-card{padding:22px}.cleanup-card-head h2{margin:0 0 6px;font-weight:900;color:#0f172a}.cleanup-card-head p{margin:0 0 18px;color:#64748b;font-weight:700}.cleanup-card-inline{display:flex;align-items:flex-start;justify-content:space-between;gap:14px}.cleanup-filter-form,.cleanup-danger-zone form{display:grid;gap:12px}.cleanup-filter-form label,.cleanup-danger-zone label{font-weight:900;color:#334155}.cleanup-filter-form select,.cleanup-danger-zone input[type=text]{height:52px;border:1px solid #cbd5e1;border-radius:16px;padding:0 14px;font-weight:800;color:#0f172a;background:#fff}.cleanup-filter-form button,.cleanup-delete-button{border:0;border-radius:16px;min-height:52px;font-weight:900;color:#fff;box-shadow:0 14px 28px rgba(15,118,110,.18);cursor:pointer}.cleanup-filter-form button{background:linear-gradient(135deg,#0f766e,#14b8a6)}.cleanup-delete-button{display:inline-flex;align-items:center;justify-content:center;gap:10px;background:linear-gradient(135deg,#dc2626,#991b1b)}.cleanup-delete-button:disabled{background:#cbd5e1;cursor:not-allowed;box-shadow:none}.cleanup-check{display:flex!important;align-items:flex-start;gap:10px;padding:14px;border-radius:16px;background:#fff7ed;border:1px solid #fed7aa}.cleanup-check input{margin-top:4px}.cleanup-pill{display:inline-flex;white-space:nowrap;padding:8px 12px;border-radius:999px;background:#e0f2fe;color:#075985;font-weight:900}.cleanup-table-shell{overflow:auto;border-radius:18px;border:1px solid #e2e8f0}.cleanup-table{width:100%;border-collapse:collapse}.cleanup-table th{background:#f8fafc;color:#334155;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.06em}.cleanup-table th,.cleanup-table td{padding:14px;border-bottom:1px solid #e2e8f0;font-weight:700}.cleanup-empty{text-align:center;color:#64748b;padding:24px!important}@media (max-width: 992px){.cleanup-hero,.cleanup-grid{grid-template-columns:1fr}.cleanup-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width: 640px){.cleanup-stats-grid{grid-template-columns:1fr}.cleanup-hero{padding:22px}.cleanup-card-inline{display:block}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cleanupDeleteForm = document.getElementById('cleanupDeleteForm');
    if (!cleanupDeleteForm) {
        return;
    }

    let cleanupProgressTimer = null;

    function showCleanupWarning(title, text) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({title, text, icon:'warning', confirmButtonText:'OK'});
            return;
        }

        alert(text);
    }

    function confirmCleanup() {
        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                title: 'Supprimer définitivement ?',
                text: 'Les photos ciblées seront supprimées dans la BDD et sur le serveur.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#dc2626'
            }).then(function (result) {
                return Boolean(result.isConfirmed);
            });
        }

        return Promise.resolve(window.confirm('Supprimer définitivement les photos ciblées ?'));
    }

    function renderCleanupProgress(percent) {
        const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
        const bar = document.getElementById('cleanupProgressBar');
        const label = document.getElementById('cleanupProgressLabel');

        if (bar) {
            bar.style.width = safePercent + '%';
        }

        if (label) {
            label.textContent = safePercent + '%';
        }
    }

    function showCleanupProgressModal() {
        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            title: 'Suppression en cours',
            html: '<div style="text-align:left;font-weight:800;color:#334155;margin-bottom:10px;">Veuillez patienter pendant le nettoyage.</div><div style="height:14px;background:#e2e8f0;border-radius:999px;overflow:hidden;"><div id="cleanupProgressBar" style="height:100%;width:0%;background:linear-gradient(135deg,#dc2626,#f97316);transition:width .25s ease;"></div></div><div id="cleanupProgressLabel" style="margin-top:10px;font-weight:900;color:#0f172a;">0%</div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () {
                renderCleanupProgress(0);
            }
        });
    }

    function startCleanupProgress() {
        showCleanupProgressModal();
        let percent = 0;
        renderCleanupProgress(percent);

        cleanupProgressTimer = window.setInterval(function () {
            if (percent < 88) {
                percent += Math.max(1, Math.round((90 - percent) / 12));
                renderCleanupProgress(percent);
            }
        }, 220);
    }

    function stopCleanupProgress() {
        if (cleanupProgressTimer !== null) {
            window.clearInterval(cleanupProgressTimer);
            cleanupProgressTimer = null;
        }

        renderCleanupProgress(100);
    }

    function submitCleanupWithProgress() {
        startCleanupProgress();

        if (!window.fetch || !window.FormData) {
            cleanupDeleteForm.dataset.confirmed = '1';
            cleanupDeleteForm.submit();
            return;
        }

        fetch(window.location.href, {
            method: 'POST',
            body: new FormData(cleanupDeleteForm),
            credentials: 'same-origin'
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('La suppression a echoue. Code HTTP ' + response.status + '.');
            }

            return response.text();
        }).then(function (html) {
            stopCleanupProgress();
            window.setTimeout(function () {
                document.open();
                document.write(html);
                document.close();
            }, 450);
        }).catch(function (error) {
            stopCleanupProgress();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Suppression impossible',
                    text: error.message || 'Une erreur est survenue pendant la suppression.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            alert(error.message || 'Une erreur est survenue pendant la suppression.');
        });
    }

    cleanupDeleteForm.addEventListener('submit', function (event) {
        const confirmation = document.getElementById('confirm_cleanup')?.value || '';
        if (confirmation.trim() !== 'SUPPRIMER') {
            event.preventDefault();
            showCleanupWarning('Confirmation requise', 'Tapez SUPPRIMER pour confirmer.');
            return;
        }

        if (cleanupDeleteForm.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();
        confirmCleanup().then(function (confirmed) {
            if (confirmed) {
                submitCleanupWithProgress();
            }
        });
    });
});
</script>
