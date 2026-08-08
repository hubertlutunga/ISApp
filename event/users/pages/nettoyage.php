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
$isCleanupJsonRequest = $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['run_photo_cleanup'])
    && (string) ($_POST['cleanup_ajax'] ?? '') === '1';

$sendCleanupJson = static function (bool $success, string $message, ?array $result = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'result' => $result,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

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

        if ($isCleanupJsonRequest) {
            $sendCleanupJson(true, $flash, $cleanupResult);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_event_photo_cleanup'])) {
        $selectedEventId = (int) ($_POST['cleanup_event_id'] ?? 0);
        if (trim((string) ($_POST['confirm_event_cleanup'] ?? '')) !== 'SUPPRIMER') {
            throw new RuntimeException('Tapez SUPPRIMER pour confirmer la suppression des photos de cet evenement.');
        }

        $cleanupResult = EventPhotoCleanupService::cleanupEvent(
            $pdo,
            $photoDir,
            $selectedEventId,
            isset($_POST['delete_event_files'])
        );

        $flash = 'Evenement #' . $selectedEventId . ' nettoye : '
            . (int) $cleanupResult['deleted_db_rows'] . ' ligne(s) BDD supprimee(s), '
            . (int) $cleanupResult['deleted_server_files'] . ' fichier(s) serveur supprime(s).';
    }
} catch (Throwable $exception) {
    if ($isCleanupJsonRequest) {
        $sendCleanupJson(false, $exception->getMessage(), null, 500);
    }

    $flash = $exception->getMessage();
    $flashType = 'error';
}

try {
    $summary = EventPhotoCleanupService::summarize($pdo, $photoDir, $selectedYear);
    $availableYears = EventPhotoCleanupService::availableYears($pdo);
    $eventPhotoGroups = EventPhotoCleanupService::eventPhotoGroupsByYear($pdo, $selectedYear);
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
    $eventPhotoGroups = [];
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
                            <input type="hidden" name="cleanup_ajax" value="0" id="cleanupAjaxField">

                            <label class="cleanup-check">
                                <input type="checkbox" name="delete_orphan_files" value="1" checked>
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

                <section class="cleanup-card cleanup-event-card">
                    <div class="cleanup-card-head cleanup-card-inline">
                        <div>
                            <h2>Supprimer les photos par événement</h2>
                            <p>Alternative plus précise : choisissez une commande/un événement et supprimez uniquement ses photos.</p>
                        </div>
                        <span class="cleanup-pill"><?php echo count($eventPhotoGroups); ?> événement(s)</span>
                    </div>

                    <form method="post" class="cleanup-event-form">
                        <input type="hidden" name="cleanup_year" value="<?php echo (int) $selectedYear; ?>">
                        <input type="hidden" name="run_event_photo_cleanup" value="1">

                        <div>
                            <label for="cleanup_event_id">Événement à nettoyer</label>
                            <select name="cleanup_event_id" id="cleanup_event_id" <?php echo $eventPhotoGroups === [] ? 'disabled' : ''; ?>>
                                <?php foreach ($eventPhotoGroups as $eventGroup) { ?>
                                <?php
                                    $eventLabelParts = array_filter([
                                        '#' . (int) ($eventGroup['cod_event'] ?? 0),
                                        trim((string) ($eventGroup['type_event'] ?? '')),
                                        trim((string) ($eventGroup['type_mar'] ?? '')),
                                        trim((string) ($eventGroup['lieu'] ?? '')),
                                        (string) ($eventGroup['reference_date'] ?? ''),
                                        (int) ($eventGroup['photo_count'] ?? 0) . ' photo(s)',
                                    ], static fn($value): bool => trim((string) $value) !== '');
                                    $eventLabel = implode(' — ', $eventLabelParts);
                                ?>
                                <option value="<?php echo (int) ($eventGroup['cod_event'] ?? 0); ?>">
                                    <?php echo htmlspecialchars($eventLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>

                        <label class="cleanup-check">
                            <input type="checkbox" name="delete_event_files" value="1" checked>
                            <span>Supprimer aussi les fichiers physiques de cet événement dans le dossier serveur</span>
                        </label>

                        <div>
                            <label for="confirm_event_cleanup">Confirmation</label>
                            <input type="text" id="confirm_event_cleanup" name="confirm_event_cleanup" placeholder="Tapez SUPPRIMER" autocomplete="off">
                        </div>

                        <button type="submit" class="cleanup-delete-button" <?php echo $eventPhotoGroups === [] ? 'disabled' : ''; ?>>
                            <i class="fa fa-trash" aria-hidden="true"></i>
                            Supprimer les photos de l'événement sélectionné
                        </button>
                    </form>
                </section>

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

<div class="cleanup-native-modal" id="cleanupProgressModal" aria-live="polite" aria-hidden="true">
    <div class="cleanup-native-dialog">
        <div class="cleanup-native-icon"><i class="fa fa-trash" aria-hidden="true"></i></div>
        <h3>Suppression en cours</h3>
        <p>Veuillez patienter pendant le nettoyage des photos.</p>
        <div class="cleanup-native-progress-track">
            <div class="cleanup-native-progress-bar" id="cleanupProgressBar"></div>
        </div>
        <strong id="cleanupProgressLabel">0%</strong>
    </div>
</div>

<style>
body.fixed .cleanup-wrapper{height:auto!important;min-height:100vh!important;overflow-x:hidden!important;overflow-y:visible!important}.cleanup-content-wrapper{height:auto!important;min-height:calc(100vh - 104px)!important;overflow:visible!important;padding-bottom:56px}.cleanup-content-wrapper .container-full{min-height:inherit;overflow:visible}.cleanup-page{padding-bottom:56px}
.cleanup-page{color:#0f172a}.cleanup-hero{display:grid;grid-template-columns:1fr auto;gap:24px;align-items:stretch;padding:30px;border-radius:30px;background:linear-gradient(135deg,#0f172a 0%,#164e63 58%,#0f766e 100%);box-shadow:0 24px 60px rgba(15,23,42,.22);color:#fff;margin-bottom:22px}.cleanup-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-weight:800;text-transform:uppercase;letter-spacing:.08em;font-size:12px}.cleanup-hero h1{margin:14px 0 8px;font-size:clamp(32px,4vw,54px);font-weight:900;letter-spacing:-.04em;color:#fff}.cleanup-hero p{max-width:780px;margin:0 0 16px;color:rgba(255,255,255,.82);font-weight:600}.cleanup-path{display:inline-flex;max-width:100%;padding:10px 14px;border-radius:14px;background:rgba(255,255,255,.12);word-break:break-all;color:#fff}.cleanup-year-card{min-width:190px;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.12);border-radius:24px;padding:22px;display:flex;flex-direction:column;justify-content:center;align-items:center;backdrop-filter:blur(10px)}.cleanup-year-card span{color:rgba(255,255,255,.72);font-weight:800}.cleanup-year-card strong{font-size:52px;line-height:1;color:#fff}.cleanup-flash{border-radius:18px;padding:14px 16px;margin-bottom:18px;font-weight:800}.cleanup-flash-success{background:#ecfdf5;color:#065f46;border:1px solid #bbf7d0}.cleanup-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.cleanup-stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:18px}.cleanup-stat-card,.cleanup-card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;box-shadow:0 16px 36px rgba(15,23,42,.07)}.cleanup-stat-card{padding:20px}.cleanup-stat-card span{display:block;color:#64748b;font-weight:900;text-transform:uppercase;letter-spacing:.06em;font-size:12px}.cleanup-stat-card strong{display:block;font-size:40px;line-height:1;margin:10px 0 4px;font-weight:900}.cleanup-stat-card small{color:#64748b;font-weight:700}.cleanup-stat-danger{background:linear-gradient(180deg,#fff 0%,#fef2f2 100%)}.cleanup-stat-warning{background:linear-gradient(180deg,#fff 0%,#fffbeb 100%)}.cleanup-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}.cleanup-card{padding:22px}.cleanup-card-head h2{margin:0 0 6px;font-weight:900;color:#0f172a}.cleanup-card-head p{margin:0 0 18px;color:#64748b;font-weight:700}.cleanup-card-inline{display:flex;align-items:flex-start;justify-content:space-between;gap:14px}.cleanup-filter-form,.cleanup-danger-zone form{display:grid;gap:12px}.cleanup-filter-form label,.cleanup-danger-zone label{font-weight:900;color:#334155}.cleanup-filter-form select,.cleanup-danger-zone input[type=text]{height:52px;border:1px solid #cbd5e1;border-radius:16px;padding:0 14px;font-weight:800;color:#0f172a;background:#fff}.cleanup-filter-form button,.cleanup-delete-button{border:0;border-radius:16px;min-height:52px;font-weight:900;color:#fff;box-shadow:0 14px 28px rgba(15,118,110,.18);cursor:pointer}.cleanup-filter-form button{background:linear-gradient(135deg,#0f766e,#14b8a6)}.cleanup-delete-button{display:inline-flex;align-items:center;justify-content:center;gap:10px;background:linear-gradient(135deg,#dc2626,#991b1b)}.cleanup-delete-button:disabled{background:#cbd5e1;cursor:not-allowed;box-shadow:none}.cleanup-check{display:flex!important;align-items:flex-start;gap:10px;padding:14px;border-radius:16px;background:#fff7ed;border:1px solid #fed7aa}.cleanup-check input{margin-top:4px}.cleanup-pill{display:inline-flex;white-space:nowrap;padding:8px 12px;border-radius:999px;background:#e0f2fe;color:#075985;font-weight:900}.cleanup-table-shell{overflow:auto;border-radius:18px;border:1px solid #e2e8f0}.cleanup-table{width:100%;border-collapse:collapse}.cleanup-table th{background:#f8fafc;color:#334155;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.06em}.cleanup-table th,.cleanup-table td{padding:14px;border-bottom:1px solid #e2e8f0;font-weight:700}.cleanup-empty{text-align:center;color:#64748b;padding:24px!important}@media (max-width: 992px){.cleanup-hero,.cleanup-grid{grid-template-columns:1fr}.cleanup-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width: 640px){.cleanup-stats-grid{grid-template-columns:1fr}.cleanup-hero{padding:22px}.cleanup-card-inline{display:block}}
.cleanup-native-modal{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.68);backdrop-filter:blur(8px);padding:18px}.cleanup-native-modal.is-visible{display:flex}.cleanup-native-dialog{width:min(440px,100%);border-radius:28px;background:#fff;padding:30px;box-shadow:0 30px 90px rgba(15,23,42,.35);text-align:center}.cleanup-native-icon{width:64px;height:64px;border-radius:22px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;background:#fee2e2;color:#dc2626;font-size:24px}.cleanup-native-dialog h3{margin:0 0 8px;color:#0f172a;font-weight:900}.cleanup-native-dialog p{margin:0 0 18px;color:#64748b;font-weight:700}.cleanup-native-progress-track{height:16px;border-radius:999px;background:#e2e8f0;overflow:hidden}.cleanup-native-progress-bar{height:100%;width:0%;border-radius:999px;background:linear-gradient(135deg,#dc2626,#f97316);transition:width .18s ease}.cleanup-native-dialog strong{display:block;margin-top:12px;font-size:24px;color:#0f172a;font-weight:900}
.cleanup-event-card{margin-bottom:18px}.cleanup-event-form{display:grid;grid-template-columns:1.4fr .9fr .8fr auto;gap:12px;align-items:end}.cleanup-event-form label{display:block;margin-bottom:8px;font-weight:900;color:#334155}.cleanup-event-form select,.cleanup-event-form input[type=text]{width:100%;height:52px;border:1px solid #cbd5e1;border-radius:16px;padding:0 14px;font-weight:800;color:#0f172a;background:#fff}.cleanup-event-form .cleanup-check{margin:0;height:52px;align-items:center!important}.cleanup-event-form .cleanup-delete-button{height:52px;padding:0 18px}@media (max-width: 1200px){.cleanup-event-form{grid-template-columns:1fr 1fr}}@media (max-width: 768px){.cleanup-event-form{grid-template-columns:1fr}}
</style>

<script>
(function () {
    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    ready(function () {
        var form = document.getElementById('cleanupDeleteForm');
        var input = document.getElementById('confirm_cleanup');
        var ajaxField = document.getElementById('cleanupAjaxField');
        var modal = document.getElementById('cleanupProgressModal');
        var nativeBar = document.getElementById('cleanupProgressBar');
        var nativeLabel = document.getElementById('cleanupProgressLabel');
        var timer = null;
        var percent = 0;
        var sending = false;

        if (!form) {
            return;
        }

        function setProgress(value) {
            var swalBar;
            var swalLabel;

            percent = Math.max(0, Math.min(100, Math.round(value)));
            if (nativeBar) {
                nativeBar.style.width = percent + '%';
            }
            if (nativeLabel) {
                nativeLabel.innerHTML = percent + '%';
            }

            swalBar = document.getElementById('swalCleanupProgressBar');
            swalLabel = document.getElementById('swalCleanupProgressLabel');
            if (swalBar) {
                swalBar.style.width = percent + '%';
            }
            if (swalLabel) {
                swalLabel.innerHTML = percent + '%';
            }
        }

        function showLoading() {
            if (modal) {
                modal.className = modal.className.replace(' is-visible', '') + ' is-visible';
                modal.setAttribute('aria-hidden', 'false');
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Suppression en cours',
                    html: '<div style="height:14px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-top:12px;"><div id="swalCleanupProgressBar" style="height:100%;width:0%;background:linear-gradient(135deg,#dc2626,#f97316);transition:width .18s ease;"></div></div><div id="swalCleanupProgressLabel" style="margin-top:10px;font-weight:900;color:#0f172a;">0%</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });
            }

            setProgress(0);
            timer = window.setInterval(function () {
                if (percent < 94) {
                    setProgress(percent + Math.max(1, Math.round((96 - percent) / 10)));
                }
            }, 180);
        }

        function hideNativeLoading() {
            if (modal) {
                modal.className = modal.className.replace(' is-visible', '');
                modal.setAttribute('aria-hidden', 'true');
            }
        }

        function finish(callback) {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
            setProgress(100);
            window.setTimeout(callback, 350);
        }

        function showError(message) {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
            hideNativeLoading();
            sending = false;

            if (typeof Swal !== 'undefined') {
                Swal.fire({title:'Suppression impossible', text:message, icon:'error', confirmButtonText:'OK'});
            } else {
                alert(message);
            }
        }

        function reloadPage() {
            window.location.href = 'index.php?page=nettoyage&year=' + encodeURIComponent(String(<?php echo (int) $selectedYear; ?>));
        }

        function submitWithAjax() {
            var xhr;

            if (!window.XMLHttpRequest || !window.FormData) {
                if (ajaxField) {
                    ajaxField.value = '0';
                }
                showLoading();
                window.setTimeout(function () {
                    form.submit();
                }, 900);
                return;
            }

            if (ajaxField) {
                ajaxField.value = '1';
            }

            showLoading();

            xhr = new XMLHttpRequest();
            xhr.open('POST', window.location.href, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onreadystatechange = function () {
                var payload;

                if (xhr.readyState !== 4) {
                    return;
                }

                if (xhr.status < 200 || xhr.status >= 300) {
                    showError('La suppression a échoué. Code HTTP ' + xhr.status + '.');
                    return;
                }

                try {
                    payload = JSON.parse(xhr.responseText);
                } catch (error) {
                    showError('Réponse serveur invalide pendant la suppression.');
                    return;
                }

                if (!payload || !payload.success) {
                    showError(payload && payload.message ? payload.message : 'La suppression a échoué.');
                    return;
                }

                finish(function () {
                    hideNativeLoading();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Nettoyage terminé',
                            text: payload.message || 'Suppression terminée.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(reloadPage);
                    } else {
                        alert(payload.message || 'Suppression terminée.');
                        reloadPage();
                    }
                });
            };
            xhr.onerror = function () {
                showError('La suppression a échoué. Vérifiez votre connexion puis réessayez.');
            };
            xhr.send(new FormData(form));
        }

        function askConfirmAndSubmit() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Supprimer définitivement ?',
                    text: 'Les photos ciblées seront supprimées dans la BDD et sur le serveur.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#dc2626'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        sending = true;
                        submitWithAjax();
                    }
                });
                return;
            }

            if (window.confirm('Supprimer définitivement les photos ciblées ?')) {
                sending = true;
                submitWithAjax();
            }
        }

        form.onsubmit = function (event) {
            var confirmation = input ? input.value : '';

            if (event && event.preventDefault) {
                event.preventDefault();
            }

            if (sending) {
                return false;
            }

            if (String(confirmation).replace(/^\s+|\s+$/g, '') !== 'SUPPRIMER') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({title:'Confirmation requise', text:'Tapez SUPPRIMER pour confirmer.', icon:'warning', confirmButtonText:'OK'});
                } else {
                    alert('Tapez SUPPRIMER pour confirmer.');
                }
                return false;
            }

            askConfirmAndSubmit();
            return false;
        };
    });
}());
</script>
