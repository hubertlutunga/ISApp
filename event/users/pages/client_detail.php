<?php
$clientFlash = null;
$datasession = UserAccountService::currentSessionUser($pdo) ?? [];
$currentAdminUserId = (int) ($datasession['cod_user'] ?? 0);

if ((string) ($datasession['type_user'] ?? '') !== '1') {
    PageRouter::redirect('index.php?page=logout');
}

AdminClientManagementService::ensureControlTable($pdo);
WhatsAppQuotaService::ensureTable($pdo);

$clientId = max(0, (int) ($_GET['client_id'] ?? 0));
if ($clientId <= 0) {
    PageRouter::redirect('index.php?page=clients');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_block_client_id'])) {
    $targetClientId = (int) $_POST['toggle_block_client_id'];
    $blockState = (int) ($_POST['block_state'] ?? 0) === 1;
    $reason = trim((string) ($_POST['control_reason'] ?? ''));

    try {
        AdminClientManagementService::setClientBlocked($pdo, $currentAdminUserId, $targetClientId, $blockState, $reason);
        $clientFlash = [
            'type' => 'success',
            'message' => $blockState ? 'Le client a ete bloque.' : 'Le client a ete reactive.',
        ];
    } catch (Throwable $exception) {
        $clientFlash = [
            'type' => 'danger',
            'message' => (string) ($exception->getMessage() ?: 'Impossible de changer l etat du client.'),
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_invitation_suspend_client_id'])) {
    $targetClientId = (int) $_POST['toggle_invitation_suspend_client_id'];
    $suspendState = (int) ($_POST['suspend_state'] ?? 0) === 1;
    $reason = trim((string) ($_POST['control_reason'] ?? ''));

    try {
        AdminClientManagementService::setInvitationSuspended($pdo, $currentAdminUserId, $targetClientId, $suspendState, $reason);
        $clientFlash = [
            'type' => 'success',
            'message' => $suspendState ? 'Envoi des invitations suspendu pour ce client.' : 'Envoi des invitations reactive pour ce client.',
        ];
    } catch (Throwable $exception) {
        $clientFlash = [
            'type' => 'danger',
            'message' => (string) ($exception->getMessage() ?: 'Impossible de modifier la suspension des invitations.'),
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quota_event_code'], $_POST['quota_client_user_id'], $_POST['bonus_quota_add'])) {
    $eventCode = trim((string) $_POST['quota_event_code']);
    $clientUserId = (int) $_POST['quota_client_user_id'];
    $bonusQuotaAdd = (int) $_POST['bonus_quota_add'];
    $quotaScope = trim((string) ($_POST['quota_scope'] ?? 'event'));

    try {
        if ($clientUserId <= 0 || $clientUserId !== $clientId) {
            throw new RuntimeException('Informations de credit invalides.');
        }

        if ($quotaScope === 'global') {
            $globalBonusResult = AdminClientManagementService::addBonusQuotaToAllClientEvents($pdo, $clientUserId, $bonusQuotaAdd);
            $globalOverview = (array) ($globalBonusResult['overview'] ?? []);
            $clientFlash = [
                'type' => 'success',
                'message' => 'Quota global mis a jour sur ' . (int) ($globalBonusResult['affected_events'] ?? 0) . ' evenement(s). Solde restant global : ' . (int) ($globalOverview['remaining_quota'] ?? 0) . '.',
            ];
        } else {
            if ($eventCode === '') {
                throw new RuntimeException('Code evenement manquant pour ce credit.');
            }

            $updatedQuota = WhatsAppQuotaService::addBonusQuota($pdo, $eventCode, $clientUserId, $bonusQuotaAdd);
            $clientFlash = [
                'type' => 'success',
                'message' => 'Le credit WhatsApp a ete mis a jour. Nouveau solde restant : ' . (int) ($updatedQuota['remaining_quota'] ?? 0) . '.',
            ];
        }
    } catch (Throwable $exception) {
        $clientFlash = [
            'type' => 'danger',
            'message' => (string) ($exception->getMessage() ?: 'Impossible de modifier le credit WhatsApp.'),
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['impersonate_user_id'])) {
    $result = UserAccountService::startImpersonation($pdo, (int) $_POST['impersonate_user_id']);

    if (!empty($result['success'])) {
        header('Location: index.php?page=mb_accueil');
        exit();
    }

    $clientFlash = [
        'type' => 'danger',
        'message' => (string) ($result['message'] ?? 'Impossible de changer de compte.'),
    ];
}

$adminQuotaOverview = WhatsAppQuotaService::buildAdminOverview($pdo);
$clientQuotaRows = (array) ($adminQuotaOverview['clients'] ?? []);
$clientControlMap = AdminClientManagementService::listClientControlsByIds(
    $pdo,
    array_map(static fn(array $row): int => (int) ($row['cod_user'] ?? 0), $clientQuotaRows)
);

foreach ($clientQuotaRows as $clientQuotaIndex => $clientQuotaRow) {
    $mappedClientId = (int) ($clientQuotaRow['cod_user'] ?? 0);
    $clientQuotaRows[$clientQuotaIndex]['control'] = $clientControlMap[$mappedClientId] ?? [
        'account_status' => 'active',
        'invitation_sending_suspended' => false,
    ];
}

$clientRow = null;
foreach ($clientQuotaRows as $row) {
    if ((int) ($row['cod_user'] ?? 0) === $clientId) {
        $clientRow = $row;
        break;
    }
}
?>

<div class="wrapper client-detail-wrapper">
  <?php include('header_admin.php'); ?>

  <div class="content-wrapper client-detail-content-wrapper">
    <div class="container-full">
      <div class="container py-30">
        <style>
          .client-detail-shell {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #dbe4f0;
            border-radius: 24px;
            box-shadow: 0 22px 45px rgba(15, 23, 42, 0.08);
            padding: 22px;
          }

          .client-detail-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
            flex-wrap: wrap;
          }

          .client-detail-head h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
          }

          .client-detail-meta {
            color: #64748b;
            font-size: 14px;
            margin-top: 6px;
          }

          .client-detail-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
          }

          .client-detail-stat {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
            padding: 14px;
          }

          .client-detail-stat span {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            font-weight: 700;
          }

          .client-detail-stat strong {
            display: block;
            margin-top: 8px;
            font-size: 24px;
            color: #0f172a;
          }

          .client-detail-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 18px;
          }

          .client-detail-events {
            display: grid;
            gap: 12px;
          }

          .client-detail-event {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px;
            background: #fff;
          }

          .client-detail-event-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 10px;
          }

          .client-detail-event h5 {
            margin: 0;
            color: #0f172a;
            font-size: 16px;
            font-weight: 800;
          }

          .client-detail-event small {
            color: #64748b;
          }

          .client-detail-progress {
            height: 9px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
            margin-bottom: 12px;
          }

          .client-detail-progress span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #14b8a6 0%, #0ea5e9 100%);
          }
        </style>

        <?php if ($clientFlash !== null) { ?>
          <div class="alert alert-<?php echo htmlspecialchars((string) ($clientFlash['type'] ?? 'info'), ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars((string) ($clientFlash['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php } ?>

        <div class="mb-20">
          <a href="index.php?page=clients" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour aux clients</a>
        </div>

        <div class="client-detail-shell">
          <?php if ($clientRow === null) { ?>
            <div class="alert alert-warning mb-0">Client introuvable ou supprime.</div>
          <?php } else {
            $clientName = ucfirst((string) ($clientRow['noms'] ?? 'Client'));
            $quotaOverview = (array) ($clientRow['quota_overview'] ?? []);
            $clientEvents = (array) ($quotaOverview['events'] ?? []);
            $clientControl = (array) ($clientRow['control'] ?? []);
            $isClientBlocked = (string) ($clientControl['account_status'] ?? 'active') === 'blocked';
            $isInvitationSuspended = !empty($clientControl['invitation_sending_suspended']);
          ?>
          <div class="client-detail-head">
            <div>
              <h2><?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></h2>
              <div class="client-detail-meta">
                Compte #<?php echo (int) ($clientRow['cod_user'] ?? 0); ?>
                · <?php echo htmlspecialchars((string) ($clientRow['email'] ?? 'Aucun email'), ENT_QUOTES, 'UTF-8'); ?>
                · <?php echo htmlspecialchars((string) ($clientRow['phone'] ?? 'Aucun telephone'), ENT_QUOTES, 'UTF-8'); ?>
              </div>
            </div>
            <div>
              <a href="index.php?page=clients" class="btn btn-outline btn-primary btn-sm">Ouvrir la liste</a>
            </div>
          </div>

          <div class="client-detail-stats">
            <div class="client-detail-stat"><span>Envois</span><strong><?php echo (int) ($quotaOverview['sent_count'] ?? 0); ?></strong></div>
            <div class="client-detail-stat"><span>Quota total</span><strong><?php echo (int) ($quotaOverview['total_quota'] ?? 0); ?></strong></div>
            <div class="client-detail-stat"><span>Restants</span><strong><?php echo (int) ($quotaOverview['remaining_quota'] ?? 0); ?></strong></div>
            <div class="client-detail-stat"><span>Evenements</span><strong><?php echo (int) ($quotaOverview['event_count'] ?? 0); ?></strong></div>
          </div>

          <div class="client-detail-actions">
            <?php if ((string) ($clientRow['type_user'] ?? '') === '2') { ?>
            <form action="" method="post">
              <input type="hidden" name="impersonate_user_id" value="<?php echo (int) $clientId; ?>">
              <button type="submit" class="btn btn-outline btn-info btn-sm">Se connecter au compte client</button>
            </form>
            <?php } ?>

            <form action="" method="post">
              <input type="hidden" name="toggle_block_client_id" value="<?php echo (int) $clientId; ?>">
              <input type="hidden" name="block_state" value="<?php echo $isClientBlocked ? '0' : '1'; ?>">
              <input type="hidden" name="control_reason" value="Action admin depuis la fiche client">
              <button type="submit" class="btn btn-outline <?php echo $isClientBlocked ? 'btn-success' : 'btn-danger'; ?> btn-sm js-confirm-action">
                <?php echo $isClientBlocked ? 'Activer le compte' : 'Bloquer le compte'; ?>
              </button>
            </form>

            <form action="" method="post">
              <input type="hidden" name="toggle_invitation_suspend_client_id" value="<?php echo (int) $clientId; ?>">
              <input type="hidden" name="suspend_state" value="<?php echo $isInvitationSuspended ? '0' : '1'; ?>">
              <input type="hidden" name="control_reason" value="Action admin depuis la fiche client">
              <button type="submit" class="btn btn-outline <?php echo $isInvitationSuspended ? 'btn-success' : 'btn-warning'; ?> btn-sm js-confirm-action">
                <?php echo $isInvitationSuspended ? 'Reprendre les envois' : 'Suspendre les envois'; ?>
              </button>
            </form>
          </div>

          <?php if ($clientEvents !== []) { ?>
          <form action="" method="post" class="d-flex align-items-center flex-wrap mb-18" style="gap:8px;">
            <input type="hidden" name="quota_scope" value="global">
            <input type="hidden" name="quota_event_code" value="ALL">
            <input type="hidden" name="quota_client_user_id" value="<?php echo (int) $clientId; ?>">
            <input type="number" name="bonus_quota_add" class="form-control" min="1" step="1" value="100" style="max-width:180px;" required>
            <button type="submit" class="btn btn-success btn-sm">Ajouter un quota global</button>
            <small class="text-muted">Applique le bonus a tous les evenements du client.</small>
          </form>

          <div class="client-detail-events">
            <?php foreach ($clientEvents as $clientEvent) {
              $eventTotalQuota = max(1, (int) ($clientEvent['total_quota'] ?? 0));
              $eventRemainingQuota = (int) ($clientEvent['remaining_quota'] ?? 0);
              $eventUsagePercent = min(100, max(0, (int) round(($eventRemainingQuota / $eventTotalQuota) * 100)));
            ?>
            <section class="client-detail-event">
              <div class="client-detail-event-head">
                <div>
                  <h5><?php echo htmlspecialchars((string) ($clientEvent['event_label'] ?? 'Evenement'), ENT_QUOTES, 'UTF-8'); ?></h5>
                  <small>Code evenement : <?php echo htmlspecialchars((string) ($clientEvent['event_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
                </div>
                <div class="text-muted" style="font-size:13px;">
                  Envoyes <strong><?php echo (int) ($clientEvent['sent_count'] ?? 0); ?></strong>
                  | Restants <strong><?php echo $eventRemainingQuota; ?></strong>
                  | Bonus <strong>+<?php echo (int) ($clientEvent['bonus_quota'] ?? 0); ?></strong>
                </div>
              </div>

              <div class="client-detail-progress">
                <span style="width: <?php echo $eventUsagePercent; ?>%;"></span>
              </div>

              <form action="" method="post" class="d-flex align-items-center flex-wrap" style="gap:8px;">
                <input type="hidden" name="quota_scope" value="event">
                <input type="hidden" name="quota_event_code" value="<?php echo htmlspecialchars((string) ($clientEvent['event_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="quota_client_user_id" value="<?php echo (int) $clientId; ?>">
                <input type="number" name="bonus_quota_add" class="form-control" min="1" step="1" value="50" style="max-width:150px;" required>
                <button type="submit" class="btn btn-outline btn-success btn-sm">Ajouter du credit</button>
              </form>
            </section>
            <?php } ?>
          </div>
          <?php } else { ?>
          <div class="alert alert-light mb-0">Aucun evenement rattache a ce client pour le moment.</div>
          <?php } ?>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

  <?php include('footer.php'); ?>
</div>

<script src="html/template/horizontal/src/js/vendors.min.js"></script>
<script src="html/template/horizontal/src/js/pages/chat-popup.js"></script>
<script src="html/assets/icons/feather-icons/feather.min.js"></script>
<script src="html/template/horizontal/src/js/demo.js"></script>
<script src="html/template/horizontal/src/js/jquery.smartmenus.js"></script>
<script src="html/template/horizontal/src/js/menus.js"></script>
<script src="html/template/horizontal/src/js/template.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var confirmButtons = document.querySelectorAll('.js-confirm-action');
    confirmButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            if (!window.confirm('Confirmer cette action ?')) {
                event.preventDefault();
            }
        });
    });
});
</script>
