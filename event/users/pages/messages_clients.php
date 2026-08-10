<?php
$datasession = UserAccountService::currentSessionUser($pdo) ?? [];
if ((string) ($datasession['type_user'] ?? '') !== '1') {
    PageRouter::redirect('index.php?page=logout');
}

$templates = AdminClientMessageService::templates();
$clients = AdminClientMessageService::listClients($pdo);
$recentLogs = [];
$flash = null;
$flashType = 'success';
$sendResult = null;
$selectedTemplateKey = (string) ($_POST['message_template'] ?? array_key_first($templates));
$selectedClientIds = array_map('intval', (array) ($_POST['client_ids'] ?? []));

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_admin_client_message'])) {
        $sendResult = AdminClientMessageService::sendBulk(
            $pdo,
            (int) ($datasession['cod_user'] ?? 0),
            $selectedClientIds,
            $selectedTemplateKey
        );

        $flash = 'Envoi terminé : ' . (int) $sendResult['sent'] . ' message(s) envoyé(s), ' . (int) $sendResult['failed'] . ' échec(s).';
        $flashType = (int) $sendResult['failed'] > 0 ? 'warning' : 'success';
    }

    $recentLogs = AdminClientMessageService::recentLogs($pdo, 30);
} catch (Throwable $exception) {
    $flash = $exception->getMessage();
    $flashType = 'danger';
}

if (!isset($templates[$selectedTemplateKey])) {
    $selectedTemplateKey = array_key_first($templates);
}

$templatesForJs = [];
foreach ($templates as $templateKey => $template) {
    $templatesForJs[$templateKey] = [
        'label' => (string) $template['label'],
        'sid' => (string) $template['sid'],
        'message' => (string) $template['message'],
    ];
}

$normalizeSearch = static function (string $value): string {
    $value = trim($value);
    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
};
?>

<div class="wrapper admin-messages-wrapper">
    <?php include('header_admin.php'); ?>

    <div class="content-wrapper admin-messages-content-wrapper">
        <div class="container-full">
            <div class="container py-30 admin-messages-page">
                <section class="am-hero">
                    <div>
                        <span class="am-kicker">Communication clients</span>
                        <h1>Envoyer un message WhatsApp</h1>
                        <p>Sélectionnez un ou plusieurs clients, choisissez un template approuvé Twilio, puis lancez l’envoi.</p>
                    </div>
                    <div class="am-hero-card">
                        <span>Clients disponibles</span>
                        <strong><?php echo count($clients); ?></strong>
                    </div>
                </section>

                <?php if ($flash !== null) { ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?> am-alert">
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php } ?>

                <form method="post" id="adminClientMessageForm" class="am-layout">
                    <input type="hidden" name="send_admin_client_message" value="1">

                    <section class="am-card am-composer-card">
                        <div class="am-card-head">
                            <h2>1. Choisir le message</h2>
                            <p>La variable <strong>{{1}}</strong> sera automatiquement remplacée par le nom du client.</p>
                        </div>

                        <label for="message_template">Template WhatsApp</label>
                        <select name="message_template" id="message_template" class="am-select">
                            <?php foreach ($templates as $templateKey => $template) { ?>
                            <option value="<?php echo htmlspecialchars($templateKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $templateKey === $selectedTemplateKey ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars((string) $template['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php } ?>
                        </select>

                        <div class="am-preview">
                            <span>Aperçu du message</span>
                            <pre id="templateMessagePreview"><?php echo htmlspecialchars((string) $templates[$selectedTemplateKey]['message'], ENT_QUOTES, 'UTF-8'); ?></pre>
                        </div>
                    </section>

                    <section class="am-card am-clients-card">
                        <div class="am-card-head am-card-inline">
                            <div>
                                <h2>2. Sélectionner les clients</h2>
                                <p>Cochez un ou plusieurs clients. Les numéros doivent être au format international.</p>
                            </div>
                            <span class="am-pill"><span id="selectedClientCount">0</span> sélectionné(s)</span>
                        </div>

                        <div class="am-toolbar">
                            <div class="am-search">
                                <i class="fa fa-search" aria-hidden="true"></i>
                                <input type="search" id="clientSearchInput" placeholder="Rechercher nom, téléphone ou email">
                            </div>
                            <button type="button" id="selectVisibleClients" class="am-secondary-btn">Tout sélectionner</button>
                            <button type="button" id="clearSelectedClients" class="am-light-btn">Effacer</button>
                        </div>

                        <div class="am-client-list" id="clientList">
                            <?php foreach ($clients as $client) { ?>
                            <?php
                                $clientId = (int) ($client['cod_user'] ?? 0);
                                $clientName = trim((string) ($client['noms'] ?? 'Client')) ?: 'Client';
                                $clientPhone = trim((string) ($client['phone'] ?? ''));
                                $clientEmail = trim((string) ($client['email'] ?? ''));
                                $clientSearch = $normalizeSearch($clientName . ' ' . $clientPhone . ' ' . $clientEmail);
                                $isSelected = in_array($clientId, $selectedClientIds, true);
                            ?>
                            <label class="am-client-row" data-client-search="<?php echo htmlspecialchars($clientSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="checkbox" name="client_ids[]" value="<?php echo $clientId; ?>" class="client-checkbox" <?php echo $isSelected ? 'checked' : ''; ?>>
                                <span class="am-client-avatar"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($clientName, 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="am-client-info">
                                    <strong><?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <small><?php echo htmlspecialchars($clientPhone !== '' ? $clientPhone : 'Téléphone manquant', ENT_QUOTES, 'UTF-8'); ?><?php echo $clientEmail !== '' ? ' · ' . htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8') : ''; ?></small>
                                </span>
                            </label>
                            <?php } ?>

                            <?php if ($clients === []) { ?>
                            <div class="am-empty">Aucun client disponible.</div>
                            <?php } ?>
                        </div>

                        <button type="submit" class="am-send-btn" <?php echo $clients === [] ? 'disabled' : ''; ?>>
                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                            Envoyer le message
                        </button>
                    </section>
                </form>

                <?php if ($sendResult !== null) { ?>
                <section class="am-card am-results-card">
                    <div class="am-card-head am-card-inline">
                        <div>
                            <h2>Résultat du dernier envoi</h2>
                            <p><?php echo (int) $sendResult['processed']; ?> client(s) traité(s).</p>
                        </div>
                        <span class="am-pill am-pill-success"><?php echo (int) $sendResult['sent']; ?> envoyé(s)</span>
                        <span class="am-pill am-pill-danger"><?php echo (int) $sendResult['failed']; ?> échec(s)</span>
                    </div>
                    <div class="am-table-shell">
                        <table class="am-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Statut</th>
                                    <th>Détail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ((array) $sendResult['results'] as $resultRow) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string) ($resultRow['client_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($resultRow['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="am-status am-status-<?php echo ((string) ($resultRow['status'] ?? 'failed')) === 'sent' ? 'sent' : 'failed'; ?>"><?php echo ((string) ($resultRow['status'] ?? 'failed')) === 'sent' ? 'Envoyé' : 'Échec'; ?></span></td>
                                    <td><?php echo htmlspecialchars((string) (($resultRow['twilio_sid'] ?? '') ?: ($resultRow['error'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php } ?>

                <section class="am-card am-history-card">
                    <div class="am-card-head am-card-inline">
                        <div>
                            <h2>Historique récent</h2>
                            <p>Les 30 derniers messages administratifs envoyés aux clients.</p>
                        </div>
                    </div>

                    <div class="am-table-shell">
                        <table class="am-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Template</th>
                                    <th>Statut</th>
                                    <th>Détail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogs as $logRow) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string) ($logRow['sent_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($logRow['client_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><br><small><?php echo htmlspecialchars((string) ($logRow['recipient_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small></td>
                                    <td><?php echo htmlspecialchars((string) ($logRow['template_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="am-status am-status-<?php echo ((string) ($logRow['send_status'] ?? 'failed')) === 'sent' ? 'sent' : 'failed'; ?>"><?php echo ((string) ($logRow['send_status'] ?? 'failed')) === 'sent' ? 'Envoyé' : 'Échec'; ?></span></td>
                                    <td><?php echo htmlspecialchars((string) (($logRow['twilio_message_sid'] ?? '') ?: ($logRow['error_message'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <?php } ?>
                                <?php if ($recentLogs === []) { ?>
                                <tr><td colspan="5" class="am-empty">Aucun historique pour le moment.</td></tr>
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
body.fixed .admin-messages-wrapper{height:auto!important;min-height:100vh!important;overflow-x:hidden!important;overflow-y:visible!important}.admin-messages-content-wrapper{height:auto!important;min-height:calc(100vh - 104px)!important;overflow:visible!important;padding-bottom:56px}.admin-messages-page{color:#0f172a;padding-bottom:60px}.am-hero{display:grid;grid-template-columns:1fr auto;gap:22px;align-items:stretch;padding:30px;border-radius:30px;background:linear-gradient(135deg,#111827 0%,#1d4ed8 55%,#14b8a6 100%);color:#fff;box-shadow:0 24px 60px rgba(15,23,42,.22);margin-bottom:20px}.am-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-weight:900;text-transform:uppercase;letter-spacing:.08em;font-size:12px}.am-hero h1{margin:14px 0 8px;font-size:clamp(32px,4vw,54px);font-weight:900;letter-spacing:-.04em;color:#fff}.am-hero p{margin:0;color:rgba(255,255,255,.82);font-weight:700}.am-hero-card{min-width:180px;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.12);border-radius:24px;padding:22px;display:flex;flex-direction:column;align-items:center;justify-content:center}.am-hero-card span{font-weight:800;color:rgba(255,255,255,.75)}.am-hero-card strong{font-size:48px;line-height:1;color:#fff}.am-alert{border-radius:18px;font-weight:800}.am-layout{display:grid;grid-template-columns:minmax(300px,.78fr) minmax(420px,1.22fr);gap:18px;margin-bottom:18px}.am-card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;box-shadow:0 16px 36px rgba(15,23,42,.07);padding:22px}.am-card-head h2{margin:0 0 6px;color:#0f172a;font-weight:900}.am-card-head p{margin:0 0 18px;color:#64748b;font-weight:700}.am-card-inline{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap}.am-composer-card label,.am-clients-card label{font-weight:900;color:#334155}.am-select{width:100%;height:54px;border:1px solid #cbd5e1;border-radius:16px;padding:0 14px;background:#fff;color:#0f172a;font-weight:800}.am-template-meta{margin:14px 0;padding:14px;border-radius:16px;background:#eff6ff;border:1px solid #bfdbfe}.am-template-meta span{display:block;color:#1d4ed8;font-weight:900;text-transform:uppercase;font-size:12px}.am-template-meta strong{display:block;margin-top:4px;word-break:break-all;color:#0f172a}.am-preview{border:1px solid #e2e8f0;border-radius:18px;overflow:hidden}.am-preview span{display:block;padding:12px 14px;background:#f8fafc;color:#475569;font-weight:900}.am-preview pre{white-space:pre-wrap;margin:0;padding:16px;min-height:250px;color:#0f172a;font-family:inherit;font-weight:650;line-height:1.55}.am-pill{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:999px;background:#e0f2fe;color:#075985;font-weight:900}.am-pill-success{background:#dcfce7;color:#166534}.am-pill-danger{background:#fee2e2;color:#991b1b}.am-toolbar{display:grid;grid-template-columns:1fr auto auto;gap:10px;margin-bottom:14px}.am-search{display:flex;align-items:center;gap:10px;height:50px;border:1px solid #cbd5e1;border-radius:16px;padding:0 14px;background:#fff}.am-search input{width:100%;border:0;outline:0;background:transparent;color:#0f172a;font-weight:700}.am-secondary-btn,.am-light-btn,.am-send-btn{border:0;border-radius:16px;font-weight:900;cursor:pointer}.am-secondary-btn{padding:0 16px;color:#fff;background:linear-gradient(135deg,#2563eb,#0891b2)}.am-light-btn{padding:0 16px;color:#334155;background:#e2e8f0}.am-client-list{max-height:560px;overflow:auto;display:grid;gap:10px;padding-right:4px}.am-client-row{display:grid;grid-template-columns:auto 46px 1fr;gap:12px;align-items:center;padding:12px;border:1px solid #e2e8f0;border-radius:18px;background:#fff;cursor:pointer;transition:.16s ease}.am-client-row:hover{border-color:#93c5fd;box-shadow:0 10px 24px rgba(37,99,235,.08)}.am-client-row.is-hidden{display:none}.am-client-row input{width:18px;height:18px}.am-client-avatar{display:flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:16px;background:linear-gradient(135deg,#dbeafe,#ccfbf1);color:#1d4ed8;font-weight:900}.am-client-info strong{display:block;color:#0f172a;font-weight:900}.am-client-info small{display:block;color:#64748b;font-weight:700}.am-send-btn{width:100%;min-height:56px;margin-top:16px;color:#fff;background:linear-gradient(135deg,#16a34a,#0f766e);box-shadow:0 14px 28px rgba(15,118,110,.18)}.am-send-btn:disabled{background:#cbd5e1;box-shadow:none;cursor:not-allowed}.am-results-card,.am-history-card{margin-bottom:18px}.am-table-shell{overflow:auto;border:1px solid #e2e8f0;border-radius:18px}.am-table{width:100%;border-collapse:collapse}.am-table th{background:#f8fafc;color:#334155;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.06em}.am-table th,.am-table td{padding:13px 14px;border-bottom:1px solid #e2e8f0;font-weight:700;vertical-align:top}.am-table small{color:#64748b}.am-status{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:900}.am-status-sent{background:#dcfce7;color:#166534}.am-status-failed{background:#fee2e2;color:#991b1b}.am-empty{text-align:center;color:#64748b;font-weight:800;padding:22px!important}@media (max-width: 1100px){.am-layout,.am-hero{grid-template-columns:1fr}.am-toolbar{grid-template-columns:1fr}}@media (max-width: 640px){.am-card-inline{display:block}.am-toolbar{grid-template-columns:1fr}.am-secondary-btn,.am-light-btn{height:46px}}
.am-hero p,.am-hero-card span,.am-alert,.am-card-head p,.am-composer-card label,.am-clients-card label,.am-select,.am-search input,.am-client-info small,.am-table td,.am-table small,.am-empty{font-weight:400}.am-card-head p strong{font-weight:600}.am-preview span{font-weight:500}.am-preview pre{font-weight:400}.am-pill,.am-status{font-weight:600}.am-client-info strong{font-weight:500}.am-table th{font-weight:700}.am-secondary-btn,.am-light-btn,.am-send-btn,.am-card-head h2,.am-hero h1,.am-kicker{font-weight:900}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const templates = <?php echo json_encode($templatesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const templateSelect = document.getElementById('message_template');
    const messagePreview = document.getElementById('templateMessagePreview');
    const searchInput = document.getElementById('clientSearchInput');
    const checkboxes = Array.from(document.querySelectorAll('.client-checkbox'));
    const rows = Array.from(document.querySelectorAll('.am-client-row'));
    const selectedCount = document.getElementById('selectedClientCount');
    const selectVisibleButton = document.getElementById('selectVisibleClients');
    const clearButton = document.getElementById('clearSelectedClients');
    const form = document.getElementById('adminClientMessageForm');

    function updateTemplatePreview() {
        const template = templates[templateSelect?.value || ''] || null;
        if (!template) {
            return;
        }
        if (messagePreview) {
            messagePreview.textContent = template.message;
        }
    }

    function normalize(value) {
        return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function updateSelectedCount() {
        if (selectedCount) {
            selectedCount.textContent = String(checkboxes.filter((checkbox) => checkbox.checked).length);
        }
    }

    function filterClients() {
        const query = normalize(searchInput?.value || '');
        rows.forEach((row) => {
            const haystack = normalize(row.dataset.clientSearch || '');
            row.classList.toggle('is-hidden', query !== '' && !haystack.includes(query));
        });
    }

    templateSelect?.addEventListener('change', updateTemplatePreview);
    searchInput?.addEventListener('input', filterClients);
    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelectedCount));

    selectVisibleButton?.addEventListener('click', function () {
        rows.forEach((row) => {
            if (!row.classList.contains('is-hidden')) {
                const checkbox = row.querySelector('.client-checkbox');
                if (checkbox) {
                    checkbox.checked = true;
                }
            }
        });
        updateSelectedCount();
    });

    clearButton?.addEventListener('click', function () {
        checkboxes.forEach((checkbox) => { checkbox.checked = false; });
        updateSelectedCount();
    });

    form?.addEventListener('submit', function (event) {
        const count = checkboxes.filter((checkbox) => checkbox.checked).length;
        if (count <= 0) {
            event.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({title:'Aucun client sélectionné', text:'Sélectionnez au moins un client avant l’envoi.', icon:'warning', confirmButtonText:'OK'});
            } else {
                alert('Sélectionnez au moins un client avant l’envoi.');
            }
            return;
        }

        if (!window.confirm('Confirmer l’envoi du message à ' + count + ' client(s) ?')) {
            event.preventDefault();
        }
    });

    updateTemplatePreview();
    updateSelectedCount();
});
</script>
