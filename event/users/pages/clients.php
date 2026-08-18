
  <?php
  $impersonationFlash = null;
  $quotaFlash = null;
  $clientFlash = null;
  $currentAdminUser = UserAccountService::currentSessionUser($pdo) ?? [];
  $currentAdminUserId = (int) ($currentAdminUser['cod_user'] ?? 0);

  if ((string) ($currentAdminUser['type_user'] ?? '') !== '1') {
    PageRouter::redirect('index.php?page=logout');
  }

  AdminClientManagementService::ensureControlTable($pdo);
  WhatsAppQuotaService::ensureTable($pdo);

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_client_submit'])) {
    $submittedPassword = trim((string) ($_POST['password'] ?? ''));
    $generatedPassword = '';

    if ($submittedPassword === '') {
      $generatedPassword = 'ISapp!' . bin2hex(random_bytes(4));
      $_POST['password'] = $generatedPassword;
      $_POST['confirm_password'] = $generatedPassword;
    }

    $result = UserAccountService::registerCustomer(
      $pdo,
      [
        'noms' => (string) ($_POST['noms'] ?? ''),
        'phone' => (string) ($_POST['phone'] ?? ''),
        'email' => (string) ($_POST['email'] ?? ''),
        'password' => (string) ($_POST['password'] ?? ''),
        'confirm_password' => (string) ($_POST['confirm_password'] ?? ''),
        'type_user' => '2',
      ],
      isset($mail) ? $mail : null,
      isset($isAppConfig) && is_array($isAppConfig) ? $isAppConfig : []
    );

    if (!empty($result['success'])) {
      $createdPhone = (string) (($result['user']['phone'] ?? '') ?: ($_POST['phone'] ?? ''));
      $createdUserStmt = $pdo->prepare('SELECT cod_user FROM is_users WHERE phone = ? LIMIT 1');
      $createdUserStmt->execute([$createdPhone]);
      $createdUserId = (int) $createdUserStmt->fetchColumn();
      $createdUserStmt->closeCursor();

      if ($createdUserId > 0) {
        AdminClientManagementService::ensureClientControl($pdo, $createdUserId);
      }
    }

    $clientFlashMessage = (string) ($result['message'] ?? 'Operation terminee.');
    if (!empty($result['success']) && $generatedPassword !== '') {
      $clientFlashMessage .= ' Mot de passe provisoire: ' . $generatedPassword;
    }

    $clientFlash = [
      'type' => !empty($result['success']) ? 'success' : 'danger',
      'message' => $clientFlashMessage,
    ];
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
    } catch (\Throwable $exception) {
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
    } catch (\Throwable $exception) {
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
      if ($clientUserId <= 0) {
        throw new RuntimeException('Informations de credit invalides.');
      }

      if ($quotaScope === 'global') {
        $globalBonusResult = AdminClientManagementService::addBonusQuotaToAllClientEvents($pdo, $clientUserId, $bonusQuotaAdd);
        $globalOverview = (array) ($globalBonusResult['overview'] ?? []);
        $quotaFlash = [
          'type' => 'success',
          'message' => 'Quota global mis a jour sur ' . (int) ($globalBonusResult['affected_events'] ?? 0) . ' evenement(s). Solde restant global : ' . (int) ($globalOverview['remaining_quota'] ?? 0) . '.',
        ];
      } else {
        if ($eventCode === '') {
          throw new RuntimeException('Code evenement manquant pour ce credit.');
        }

        $updatedQuota = WhatsAppQuotaService::addBonusQuota($pdo, $eventCode, $clientUserId, $bonusQuotaAdd);
        $quotaFlash = [
          'type' => 'success',
          'message' => 'Le credit WhatsApp a ete mis a jour. Nouveau solde restant : ' . (int) ($updatedQuota['remaining_quota'] ?? 0) . '.',
        ];
      }
    } catch (\Throwable $exception) {
      $quotaFlash = [
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

    $impersonationFlash = [
      'type' => 'danger',
      'message' => (string) ($result['message'] ?? 'Impossible de changer de compte.'),
    ];
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_client_id'])) {
    $result = UserAccountService::adminUpdateUserProfile(
      $pdo,
      $currentAdminUserId,
      (int) $_POST['save_client_id'],
      $_POST
    );

    $clientFlash = [
      'type' => !empty($result['success']) ? 'success' : 'danger',
      'message' => (string) ($result['message'] ?? 'Impossible de modifier ce client.'),
    ];
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_client_id'])) {
    $result = UserAccountService::adminDeleteClient(
      $pdo,
      $currentAdminUserId,
      (int) $_POST['delete_client_id']
    );

    $clientFlash = [
      'type' => !empty($result['success']) ? 'success' : 'danger',
      'message' => (string) ($result['message'] ?? 'Impossible de supprimer ce client.'),
    ];
  }

  $formatSearchValue = static function ($value): string {
    $value = trim((string) $value);

    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
  };

  $clientsView = trim((string) ($_GET['view'] ?? 'clients'));
  if (!in_array($clientsView, ['clients', 'whatsapp-sends'], true)) {
    $clientsView = 'clients';
  }

  $whatsAppHistoryRows = [];
  $whatsAppHistoryUsers = [];
  $whatsAppHistorySearch = trim((string) ($_GET['history_q'] ?? ''));
  $whatsAppHistoryUserId = max(0, (int) ($_GET['history_user_id'] ?? 0));
  $whatsAppHistoryPage = max(1, (int) ($_GET['history_page'] ?? 1));
  $whatsAppHistoryPerPage = 25;
  $whatsAppHistoryTotalRows = 0;
  $whatsAppHistoryTotalPages = 1;
  $whatsAppHistoryBaseParams = [
    'page' => 'clients',
    'view' => 'whatsapp-sends',
  ];
  if ($whatsAppHistorySearch !== '') {
    $whatsAppHistoryBaseParams['history_q'] = $whatsAppHistorySearch;
  }
  if ($whatsAppHistoryUserId > 0) {
    $whatsAppHistoryBaseParams['history_user_id'] = $whatsAppHistoryUserId;
  }

  if ($clientsView === 'whatsapp-sends') {
    require_once __DIR__ . '/whatsapp_template_sender.php';

    try {
      if (function_exists('isapp_whatsapp_sender_ensure_log_table')) {
        isapp_whatsapp_sender_ensure_log_table($pdo);
      }

      $historyFromSql = ' FROM whatsapp_message_logs logs
         LEFT JOIN events events ON events.cod_event = logs.event_code
         LEFT JOIN is_users users ON users.cod_user = COALESCE(NULLIF(events.cod_user, 0), NULLIF(events.cod_user2, 0))
         LEFT JOIN invite invites ON invites.id_inv = logs.invite_id';

      $historyUsersStmt = $pdo->query(
        'SELECT DISTINCT
            users.cod_user AS client_user_id,
            COALESCE(users.noms, "Utilisateur inconnu") AS client_name'
        . $historyFromSql . '
         WHERE users.cod_user IS NOT NULL
         ORDER BY client_name ASC'
      );
      $whatsAppHistoryUsers = $historyUsersStmt ? ($historyUsersStmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
      if ($historyUsersStmt) {
        $historyUsersStmt->closeCursor();
      }

      $historyWhereClauses = [];
      $historyBindings = [];
      if ($whatsAppHistoryUserId > 0) {
        $historyWhereClauses[] = 'COALESCE(NULLIF(events.cod_user, 0), NULLIF(events.cod_user2, 0)) = :history_user_id';
        $historyBindings['history_user_id'] = $whatsAppHistoryUserId;
      }
      if ($whatsAppHistorySearch !== '') {
        $historyWhereClauses[] = '(
          LOWER(COALESCE(users.noms, "")) LIKE :history_search
          OR LOWER(COALESCE(invites.nom, "")) LIKE :history_search
          OR LOWER(COALESCE(logs.recipient_name, "")) LIKE :history_search
          OR LOWER(COALESCE(logs.recipient_number, "")) LIKE :history_search
          OR LOWER(COALESCE(logs.event_code, "")) LIKE :history_search
        )';
        $historyBindings['history_search'] = '%' . $formatSearchValue($whatsAppHistorySearch) . '%';
      }

      $historyWhereSql = $historyWhereClauses !== [] ? ' WHERE ' . implode(' AND ', $historyWhereClauses) : '';

      $historyCountStmt = $pdo->prepare('SELECT COUNT(*)' . $historyFromSql . $historyWhereSql);
      foreach ($historyBindings as $historyBindingName => $historyBindingValue) {
        $historyCountStmt->bindValue(':' . $historyBindingName, $historyBindingValue, $historyBindingName === 'history_user_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
      }
      $historyCountStmt->execute();
      $whatsAppHistoryTotalRows = (int) $historyCountStmt->fetchColumn();
      $historyCountStmt->closeCursor();

      $whatsAppHistoryTotalPages = max(1, (int) ceil($whatsAppHistoryTotalRows / $whatsAppHistoryPerPage));
      if ($whatsAppHistoryPage > $whatsAppHistoryTotalPages) {
        $whatsAppHistoryPage = $whatsAppHistoryTotalPages;
      }
      $historyOffset = ($whatsAppHistoryPage - 1) * $whatsAppHistoryPerPage;

      $historyStmt = $pdo->prepare(
        'SELECT
            logs.id,
            logs.event_code,
            logs.invite_id,
            logs.recipient_number,
            logs.recipient_name,
            logs.media_url,
            logs.send_status,
            logs.sent_at,
            logs.error_message,
            COALESCE(users.noms, "Utilisateur inconnu") AS client_name,
            COALESCE(invites.nom, logs.recipient_name) AS invite_name'
        . $historyFromSql
        . $historyWhereSql . '
         ORDER BY logs.sent_at DESC, logs.id DESC
         LIMIT :history_limit OFFSET :history_offset'
      );
      foreach ($historyBindings as $historyBindingName => $historyBindingValue) {
        $historyStmt->bindValue(':' . $historyBindingName, $historyBindingValue, $historyBindingName === 'history_user_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
      }
      $historyStmt->bindValue(':history_limit', $whatsAppHistoryPerPage, PDO::PARAM_INT);
      $historyStmt->bindValue(':history_offset', $historyOffset, PDO::PARAM_INT);
      $historyStmt->execute();

      $whatsAppHistoryRows = $historyStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
      $historyStmt->closeCursor();
    } catch (\Throwable $exception) {
      $clientFlash = [
        'type' => 'danger',
        'message' => 'Impossible de charger l\'historique des envois WhatsApp.',
      ];
    }
  }
  ?>

  <style>
    .clients-admin-card {
      border: 0;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
      background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .clients-admin-toolbar {
      display: grid;
      grid-template-columns: minmax(240px, 1.7fr) minmax(170px, 0.7fr) auto auto;
      gap: 12px;
      align-items: center;
      margin-bottom: 18px;
    }

    .clients-admin-search {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 0 14px;
      height: 48px;
      border: 1px solid #dbe4f0;
      border-radius: 16px;
      background: #fff;
    }

    .clients-admin-search input {
      border: 0;
      outline: none;
      width: 100%;
      background: transparent;
      color: #0f172a;
      font-size: 14px;
    }

    .clients-admin-search i {
      color: #64748b;
    }

    .clients-admin-filters {
      height: 48px;
      border-radius: 16px;
      border: 1px solid #dbe4f0;
      background: #fff;
      color: #0f172a;
    }

    .clients-admin-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin: 14px 0 4px;
    }

    .clients-analytics-shell {
      border: 1px solid #dbe4f0;
      border-radius: 24px;
      background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
      box-shadow: 0 18px 42px rgba(15, 23, 42, 0.07);
      padding: 20px;
      margin-bottom: 18px;
    }

    .clients-analytics-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .clients-analytics-head h5 {
      margin: 0;
      color: #0f172a;
      font-size: 22px;
      font-weight: 900;
    }

    .clients-analytics-head p {
      margin: 6px 0 0;
      color: #64748b;
    }

    .clients-analytics-filter {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
    }

    .clients-analytics-export {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 10px;
    }

    .clients-analytics-export .btn {
      border-radius: 12px;
      font-size: 12px;
      font-weight: 800;
      padding: 8px 12px;
    }

    .clients-analytics-alerts {
      border: 1px solid #fed7aa;
      border-radius: 16px;
      background: #fff7ed;
      padding: 12px;
      margin-bottom: 14px;
    }

    .clients-analytics-alerts h6 {
      margin: 0 0 8px;
      color: #9a3412;
      font-size: 13px;
      font-weight: 900;
    }

    .clients-analytics-alert-list {
      display: grid;
      gap: 6px;
      margin: 0;
      padding-left: 18px;
      color: #7c2d12;
      font-size: 13px;
      font-weight: 700;
    }

    .clients-analytics-charts {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 12px;
    }

    .clients-analytics-chart {
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      background: #fff;
      padding: 10px;
      min-height: 320px;
    }

    .clients-analytics-chart--full {
      grid-column: 1 / -1;
      min-height: 300px;
    }

    .clients-analytics-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 14px;
    }

    .clients-analytics-kpi {
      border: 1px solid #dbe4f0;
      border-radius: 16px;
      background: #fff;
      padding: 14px;
    }

    .clients-analytics-kpi span {
      display: block;
      color: #64748b;
      font-size: 12px;
      margin-bottom: 8px;
    }

    .clients-analytics-kpi strong {
      display: block;
      color: #0f172a;
      font-size: 24px;
      line-height: 1;
      font-weight: 900;
    }

    .clients-analytics-kpi small {
      display: block;
      margin-top: 6px;
      color: #2563eb;
      font-size: 13px;
    }

    .clients-analytics-panels {
      display: grid;
      grid-template-columns: 1.35fr 1fr;
      gap: 12px;
    }

    .clients-analytics-panel {
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      background: #fff;
      padding: 14px;
    }

    .clients-analytics-panel h6 {
      margin: 0 0 10px;
      color: #0f172a;
      font-size: 14px;
      font-weight: 900;
    }

    .clients-analytics-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }

    .clients-analytics-table th,
    .clients-analytics-table td {
      border-bottom: 1px solid #eef2f7;
      padding: 8px 6px;
      text-align: left;
    }

    .clients-analytics-table th {
      color: #64748b;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .clients-analytics-table td {
      color: #0f172a;
      font-weight: 700;
    }

    .clients-admin-control-btn {
      width: 100%;
      margin-top: 8px;
      text-align: left;
    }

    .clients-admin-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 14px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
      background: #eff6ff;
      color: #1d4ed8;
    }

    .clients-admin-pill.is-warning {
      background: #fff7ed;
      color: #c2410c;
    }

    .clients-admin-pill.is-neutral {
      background: #f1f5f9;
      color: #334155;
    }

    .clients-admin-grid {
      display: grid;
      gap: 18px;
    }

    .clients-admin-table-wrap {
      border: 1px solid #e2e8f0;
      border-radius: 22px;
      overflow: hidden;
      background: #fff;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .clients-admin-table {
      width: 100%;
      margin: 0;
      min-width: 0;
      table-layout: fixed;
    }

    .clients-admin-table th:last-child,
    .clients-admin-table td:last-child {
      width: 88px;
    }

    .clients-admin-table thead th {
      padding: 16px 18px;
      border: 0;
      background: #eff6ff;
      color: #334155;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .04em;
      white-space: nowrap;
    }

    .clients-admin-table tbody td {
      padding: 18px;
      vertical-align: middle;
      border-color: #eef2f7;
      background: #fff;
    }

    .clients-admin-row.is-hidden {
      display: none;
    }

    .clients-admin-main {
      display: grid;
      gap: 6px;
    }

    .clients-admin-main strong {
      color: #0f172a;
      font-size: 15px;
    }

    .clients-admin-sub {
      color: #64748b;
      font-size: 13px;
      line-height: 1.6;
    }

    .clients-admin-statstack {
      display: grid;
      gap: 4px;
      color: #334155;
      font-size: 13px;
    }

    .clients-admin-actions {
      text-align: right;
      white-space: nowrap;
    }

    .clients-admin-detail-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 18px;
    }

    .clients-admin-detail-card {
      border-radius: 18px;
      padding: 16px;
      border: 1px solid #e2e8f0;
      background: #f8fafc;
    }

    .clients-admin-detail-card span {
      display: block;
      color: #64748b;
      font-size: 12px;
      margin-bottom: 6px;
    }

    .clients-admin-detail-card strong {
      display: block;
      color: #0f172a;
      font-size: 22px;
      line-height: 1;
    }

    .clients-admin-modal-events {
      display: grid;
      gap: 12px;
    }

    .clients-admin-form-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
    }

    .clients-admin-form-grid label {
      display: grid;
      gap: 8px;
      color: #0f172a;
      font-weight: 700;
    }

    .clients-admin-form-grid label span {
      font-size: 13px;
    }

    .clients-admin-form-grid input {
      min-height: 48px;
      border: 1px solid #dbe4f0;
      border-radius: 14px;
      padding: 0 14px;
    }

    .clients-admin-form-grid .is-full {
      grid-column: 1 / -1;
    }

    .clients-admin-danger-note {
      margin: 0 0 14px;
      padding: 14px 16px;
      border-radius: 14px;
      background: #fef2f2;
      color: #991b1b;
      border: 1px solid #fecaca;
      line-height: 1.6;
    }

    .clients-admin-client {
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      background: #fff;
      padding: 20px;
      box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
    }

    .clients-admin-client-head {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .clients-admin-client-name {
      margin: 0;
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
    }

    .clients-admin-client-contact {
      margin: 6px 0 0;
      color: #64748b;
      font-size: 13px;
      line-height: 1.7;
    }

    .clients-admin-badges {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .clients-admin-badge {
      display: inline-flex;
      align-items: center;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      background: #eff6ff;
      color: #1d4ed8;
    }

    .clients-admin-badge.is-alert {
      background: #fef2f2;
      color: #b91c1c;
    }

    .clients-admin-badge.is-success {
      background: #ecfdf5;
      color: #047857;
    }

    .clients-admin-stats {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 16px;
    }

    .clients-admin-stat {
      border-radius: 18px;
      padding: 14px 16px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
    }

    .clients-admin-stat-label {
      display: block;
      color: #64748b;
      font-size: 12px;
      margin-bottom: 6px;
    }

    .clients-admin-stat-value {
      display: block;
      color: #0f172a;
      font-size: 22px;
      line-height: 1;
      font-weight: 800;
    }

    .clients-admin-events {
      display: grid;
      gap: 12px;
      margin-bottom: 16px;
    }

    .clients-admin-event {
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      padding: 14px;
      background: #f8fafc;
    }

    .clients-admin-event-head {
      display: flex;
      justify-content: space-between;
      gap: 14px;
      align-items: flex-start;
      flex-wrap: wrap;
      margin-bottom: 10px;
    }

    .clients-admin-event-title {
      color: #0f172a;
      font-size: 14px;
      font-weight: 800;
      margin: 0;
    }

    .clients-admin-event-code {
      color: #64748b;
      font-size: 12px;
      margin-top: 4px;
    }

    .clients-admin-event-stats {
      color: #334155;
      font-size: 13px;
      font-weight: 600;
    }

    .clients-admin-progress {
      width: 100%;
      height: 10px;
      border-radius: 999px;
      background: #dbeafe;
      overflow: hidden;
      margin-bottom: 12px;
    }

    .clients-admin-progress-bar {
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
    }

    .clients-admin-empty {
      padding: 34px 20px;
      border: 1px dashed #cbd5e1;
      border-radius: 20px;
      text-align: center;
      color: #64748b;
      background: #fff;
    }

    .clients-admin-note {
      color: #64748b;
      font-size: 13px;
      margin: 0 0 16px;
    }

    .clients-admin-summary-link {
      display: block;
      color: inherit;
      text-decoration: none;
    }

    .clients-admin-summary-link:hover,
    .clients-admin-summary-link:focus-visible {
      color: inherit;
    }

    .clients-admin-summary-link .box-body {
      transition: transform .2s ease, box-shadow .2s ease;
      border-radius: 20px;
    }

    .clients-admin-summary-link:hover .box-body,
    .clients-admin-summary-link:focus-visible .box-body {
      transform: translateY(-2px);
      box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
    }

    .clients-admin-summary-link.is-active .box-body {
      box-shadow: inset 0 0 0 2px rgba(14, 165, 233, 0.24), 0 16px 32px rgba(15, 23, 42, 0.08);
      background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%);
    }

    .clients-admin-history-wrap {
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      background: #fff;
      box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
      overflow: hidden;
    }

    .clients-admin-history-head {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      flex-wrap: wrap;
      padding: 22px 24px 0;
    }

    .clients-admin-history-head h4 {
      margin: 0;
      color: #0f172a;
      font-size: 20px;
      font-weight: 900;
    }

    .clients-admin-history-head p {
      margin: 6px 0 0;
      color: #64748b;
      font-size: 14px;
    }

    .clients-admin-history-toolbar {
      display: grid;
      grid-template-columns: minmax(240px, 1.6fr) minmax(220px, 0.8fr) auto auto;
      gap: 12px;
      align-items: center;
      padding: 18px 24px 0;
    }

    .clients-admin-history-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      padding: 14px 24px 0;
    }

    .clients-admin-history-count {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 9px 14px;
      border-radius: 999px;
      background: #eff6ff;
      color: #1d4ed8;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .02em;
    }

    .clients-admin-history-table thead th {
      background: #f8fafc;
      color: #475569;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .05em;
      border-bottom: 1px solid #e2e8f0;
    }

    .clients-admin-history-table tbody td {
      border-color: #eef2f7;
      vertical-align: middle;
      padding: 16px 18px;
    }

    .clients-admin-history-user,
    .clients-admin-history-invite {
      display: grid;
      gap: 4px;
    }

    .clients-admin-history-user strong,
    .clients-admin-history-invite strong {
      color: #0f172a;
      font-size: 14px;
      font-weight: 800;
    }

    .clients-admin-history-user span,
    .clients-admin-history-invite span,
    .clients-admin-history-event,
    .clients-admin-history-date {
      color: #64748b;
      font-size: 12px;
      line-height: 1.5;
    }

    .clients-admin-status-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .04em;
      background: #f1f5f9;
      color: #334155;
    }

    .clients-admin-status-badge.is-success {
      background: #ecfdf5;
      color: #047857;
    }

    .clients-admin-status-badge.is-danger {
      background: #fef2f2;
      color: #b91c1c;
    }

    .clients-admin-link-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 12px;
      background: #eff6ff;
      color: #1d4ed8;
      font-size: 12px;
      font-weight: 800;
      text-decoration: none;
      white-space: nowrap;
    }

    .clients-admin-link-btn:hover,
    .clients-admin-link-btn:focus-visible {
      color: #1e3a8a;
      background: #dbeafe;
    }

    .clients-admin-pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      flex-wrap: wrap;
      padding: 18px 24px 24px;
      border-top: 1px solid #eef2f7;
    }

    .clients-admin-pagination-links {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .clients-admin-pagination-link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 40px;
      height: 40px;
      padding: 0 14px;
      border-radius: 12px;
      border: 1px solid #dbe4f0;
      background: #fff;
      color: #334155;
      font-size: 13px;
      font-weight: 800;
      text-decoration: none;
    }

    .clients-admin-pagination-link:hover,
    .clients-admin-pagination-link:focus-visible {
      border-color: #93c5fd;
      color: #1d4ed8;
      background: #eff6ff;
    }

    .clients-admin-pagination-link.is-active {
      border-color: #2563eb;
      background: #2563eb;
      color: #fff;
    }

    .clients-admin-pagination-summary {
      color: #64748b;
      font-size: 13px;
      font-weight: 700;
    }

    @media (max-width: 991px) {
      .clients-admin-toolbar {
        grid-template-columns: 1fr;
      }

      .clients-analytics-grid {
        grid-template-columns: 1fr;
      }

      .clients-analytics-panels {
        grid-template-columns: 1fr;
      }

      .clients-analytics-charts {
        grid-template-columns: 1fr;
      }

      .clients-admin-history-toolbar {
        grid-template-columns: 1fr;
      }

      .clients-admin-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .clients-admin-detail-grid,
      .clients-admin-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 575px) {
      .clients-admin-table thead th,
      .clients-admin-table tbody td {
        padding: 14px 12px;
      }

      .clients-admin-table th:last-child,
      .clients-admin-table td:last-child {
        width: 72px;
      }

      .clients-admin-actions .btn {
        padding-left: 10px;
        padding-right: 10px;
      }

      .clients-admin-stats {
        grid-template-columns: 1fr;
      }

      .clients-admin-detail-grid,
      .clients-admin-form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

	<div class="wrapper"> 
	 

  <?php include('header_admin.php');?>
   
 

  <!-- Content Wrapper. Contains page content -->
 	<div class="content-wrapper">
			<div class="container-full">
			<!-- Main content -->

<!-- 

		<div class="content-header text-center">
			<div class="d-flex align-items-center">
				<div class="me-auto">
					<h3 class="page-title">Weather widgets</h3>
					<div class="d-inline-block align-items-center">
						<nav>
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
								<li class="breadcrumb-item" aria-current="page">Widgets</li>
								<li class="breadcrumb-item active" aria-current="page">Weather widgets</li>
							</ol>
						</nav>
					</div>
				</div>
				
			</div>
		</div> -->

















				
<div class="row salut">

<?php 

$heure = date('H');

if ($heure < 12) {
$salut = 'Bonjour';
}elseif ($heure > 11 AND $heure < 15){
$salut = 'Bon Après-midi';
}elseif ($heure > 12){
$salut = 'Bonsoir';
}

 
?>

<p style="text-align:center;">
	<?php  // echo "La valeur de codevent est : " . $codevent; 
	echo $salut;?> <b>
	<?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, "UTF-8");?> </b>!
</p>



  
</div>


 









<?php 


		 

		// ----------------tous les invités confirmés--------------------
    $stmtccli = $pdo->prepare("SELECT COUNT(*) as total_client FROM is_users WHERE type_user = :type_user");
    $stmtccli->execute([':type_user' => '2']);

		// Récupération du résultat
		$row_ccli = $stmtccli->fetch(PDO::FETCH_ASSOC);

		// Retourne 0 si aucun invité n'est trouvé, sinon retourne le total
		$total_ccli = $row_ccli ? (int)$row_ccli['total_client'] : 0;
    $adminQuotaOverview = WhatsAppQuotaService::buildAdminOverview($pdo);
    $adminQuotaTotals = (array) ($adminQuotaOverview['totals'] ?? []);
    $clientQuotaRows = (array) ($adminQuotaOverview['clients'] ?? []);
    $clientControlMap = AdminClientManagementService::listClientControlsByIds(
      $pdo,
      array_map(static fn(array $row): int => (int) ($row['cod_user'] ?? 0), $clientQuotaRows)
    );

    foreach ($clientQuotaRows as $clientQuotaIndex => $clientQuotaRow) {
      $clientId = (int) ($clientQuotaRow['cod_user'] ?? 0);
      $clientQuotaRows[$clientQuotaIndex]['control'] = $clientControlMap[$clientId] ?? [
        'account_status' => 'active',
        'invitation_sending_suspended' => false,
      ];
    }

    $statsClientUserId = max(0, (int) ($_GET['stats_client_id'] ?? 0));
    $quotaThreshold = max(1, (int) ($_GET['quota_threshold'] ?? 50));
    $invitationAnalytics = AdminClientManagementService::buildInvitationAnalytics($pdo, $statsClientUserId);
    $lowQuotaNotifications = AdminClientManagementService::buildLowQuotaNotifications($pdo, $quotaThreshold);
    $analyticsDailyRows = (array) ($invitationAnalytics['daily_rows'] ?? []);
    $analyticsMonthlyRows = (array) ($invitationAnalytics['monthly_rows'] ?? []);
    $analyticsTopClients = (array) ($invitationAnalytics['top_clients'] ?? []);
    $analyticsClients = (array) ($invitationAnalytics['clients'] ?? []);
    $analyticsScopeLabel = 'Tous les clients';

    if ($statsClientUserId > 0) {
      foreach ($analyticsClients as $analyticsClient) {
        if ((int) ($analyticsClient['cod_user'] ?? 0) === $statsClientUserId) {
          $analyticsScopeLabel = (string) ($analyticsClient['noms'] ?? 'Client #' . $statsClientUserId);
          break;
        }
      }
    }
    $clientSearch = trim((string) ($_GET['q'] ?? ''));
    $clientFilter = trim((string) ($_GET['filter'] ?? 'all'));
    $allowedClientFilters = ['all', 'with-events', 'without-events', 'low-credit', 'active-sends', 'blocked', 'invitation-suspended'];

    if (!in_array($clientFilter, $allowedClientFilters, true)) {
      $clientFilter = 'all';
    }

    $filteredClientQuotaRows = array_values(array_filter($clientQuotaRows, static function (array $row_client) use ($clientSearch, $clientFilter, $formatSearchValue): bool {
      $quotaOverview = (array) ($row_client['quota_overview'] ?? []);
      $clientEvents = (array) ($quotaOverview['events'] ?? []);
      $clientControl = (array) ($row_client['control'] ?? []);
      $searchableFields = [
        (string) ($row_client['noms'] ?? ''),
        (string) ($row_client['email'] ?? ''),
        (string) ($row_client['phone'] ?? ''),
      ];

      foreach ($clientEvents as $clientEvent) {
        $searchableFields[] = (string) ($clientEvent['event_label'] ?? '');
        $searchableFields[] = (string) ($clientEvent['event_code'] ?? '');
      }

      if ($clientSearch !== '') {
        $haystack = $formatSearchValue(implode(' ', $searchableFields));
        if (strpos($haystack, $formatSearchValue($clientSearch)) === false) {
          return false;
        }
      }

      if ($clientFilter === 'with-events') {
        return count($clientEvents) > 0;
      }

      if ($clientFilter === 'without-events') {
        return count($clientEvents) === 0;
      }

      if ($clientFilter === 'low-credit') {
        return (int) ($quotaOverview['remaining_quota'] ?? 0) <= 50;
      }

      if ($clientFilter === 'active-sends') {
        return (int) ($quotaOverview['sent_count'] ?? 0) > 0;
      }

      if ($clientFilter === 'blocked') {
        return (string) ($clientControl['account_status'] ?? 'active') === 'blocked';
      }

      if ($clientFilter === 'invitation-suspended') {
        return !empty($clientControl['invitation_sending_suspended']);
      }

      return true;
    }));

    usort($filteredClientQuotaRows, static function (array $left, array $right): int {
      $leftName = trim((string) ($left['noms'] ?? ''));
      $rightName = trim((string) ($right['noms'] ?? ''));

      return strcasecmp($leftName, $rightName);
    });

    $visibleClientCount = count($filteredClientQuotaRows);
    $clientsWithoutEventCount = 0;
    $clientsLowCreditCount = 0;
    $clientsBlockedCount = 0;
    $clientsInvitationSuspendedCount = 0;

    foreach ($filteredClientQuotaRows as $clientQuotaRow) {
      $quotaOverview = (array) ($clientQuotaRow['quota_overview'] ?? []);
      $clientControl = (array) ($clientQuotaRow['control'] ?? []);
      if ((int) ($quotaOverview['event_count'] ?? 0) === 0) {
        $clientsWithoutEventCount++;
      }
      if ((int) ($quotaOverview['remaining_quota'] ?? 0) <= 50) {
        $clientsLowCreditCount++;
      }
      if ((string) ($clientControl['account_status'] ?? 'active') === 'blocked') {
        $clientsBlockedCount++;
      }
      if (!empty($clientControl['invitation_sending_suspended'])) {
        $clientsInvitationSuspendedCount++;
      }
    }


  
?>






			<section class="content">
        <?php if ($impersonationFlash !== null) { ?>
        <div class="alert alert-<?php echo htmlspecialchars($impersonationFlash['type'], ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($impersonationFlash['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php } ?>
        <?php if ($quotaFlash !== null) { ?>
        <div class="alert alert-<?php echo htmlspecialchars($quotaFlash['type'], ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($quotaFlash['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php } ?>
        <?php if ($clientFlash !== null) { ?>
        <div class="alert alert-<?php echo htmlspecialchars($clientFlash['type'], ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($clientFlash['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php } ?>
				<div class="box box-body">
					<div class="row"> 
						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
							<div class="box-body rounded-0 p-0 pb-lg-0 pb-sm-15 pb-xs-15 be-1 fill-icon">
								<div class="d-flex align-items-center">
									<div class="w-70 h-70 me-15 bg-info-light rounded-circle text-center p-10">
										<div class="w-50 h-50 bg-info rounded-circle">
										  <i class="fas fa-user fs-24 l-h-50"></i>
										</div>		
									</div>
									<div class="d-flex flex-column">
                                        <a href="index.php?page=clients">
										<span class="text-fade fs-12">Clients</span>
										<h2 class="text-dark hover-primary m-0 fw-600"><?php echo $total_ccli; ?></h2>
                                        </a>
									</div>
								</div>
							</div>
						</div>  
            <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
              <a href="index.php?page=clients&view=whatsapp-sends" class="clients-admin-summary-link <?php echo $clientsView === 'whatsapp-sends' ? 'is-active' : ''; ?>">
              <div class="box-body rounded-0 p-0 pb-lg-0 pb-sm-15 pb-xs-15 be-1 fill-icon">
                <div class="d-flex align-items-center">
                  <div class="w-70 h-70 me-15 bg-success-light rounded-circle text-center p-10">
                    <div class="w-50 h-50 bg-success rounded-circle">
                      <i class="fas fa-paper-plane fs-24 l-h-50"></i>
                    </div>
                  </div>
                  <div class="d-flex flex-column">
                    <span class="text-fade fs-12">Envois WhatsApp</span>
                    <h2 class="text-dark m-0 fw-600"><?php echo (int) ($adminQuotaTotals['sent_count'] ?? 0); ?></h2>
                  </div>
                </div>
              </div>
              </a>
            </div>
            <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
              <div class="box-body rounded-0 p-0 pb-lg-0 pb-sm-15 pb-xs-15 be-1 fill-icon">
                <div class="d-flex align-items-center">
                  <div class="w-70 h-70 me-15 bg-primary-light rounded-circle text-center p-10">
                    <div class="w-50 h-50 bg-primary rounded-circle">
                      <i class="fas fa-layer-group fs-24 l-h-50"></i>
                    </div>
                  </div>
                  <div class="d-flex flex-column">
                    <span class="text-fade fs-12">Quota total</span>
                    <h2 class="text-dark m-0 fw-600"><?php echo (int) ($adminQuotaTotals['total_quota'] ?? 0); ?></h2>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
              <div class="box-body rounded-0 p-0 pb-lg-0 pb-sm-15 pb-xs-15 be-1 fill-icon">
                <div class="d-flex align-items-center">
                  <div class="w-70 h-70 me-15 bg-warning-light rounded-circle text-center p-10">
                    <div class="w-50 h-50 bg-warning rounded-circle">
                      <i class="fas fa-battery-three-quarters fs-24 l-h-50"></i>
                    </div>
                  </div>
                  <div class="d-flex flex-column">
                    <span class="text-fade fs-12">Restants</span>
                    <h2 class="text-dark m-0 fw-600"><?php echo (int) ($adminQuotaTotals['remaining_quota'] ?? 0); ?></h2>
                  </div>
                </div>
              </div>
            </div>
					</div>
				</div>









































 
<div class="row" id='mesinv'>
    <div class="col-xxl-12 col-xl-12 col-lg-12">
        <?php if ($clientsView === 'whatsapp-sends') { ?>
        <div class="clients-admin-history-wrap">
            <div class="clients-admin-history-head">
                <div>
                  <h4>Historique des envois WhatsApp</h4>
                  <p>Consultez les derniers envois avec l'utilisateur, l'invite, son numero et le lien de consultation de l'invitation.</p>
                </div>
                <a href="index.php?page=clients" class="btn btn-outline btn-secondary">Retour aux clients</a>
            </div>
            <form action="" method="get" class="clients-admin-history-toolbar">
              <input type="hidden" name="page" value="clients">
              <input type="hidden" name="view" value="whatsapp-sends">
              <label class="clients-admin-search" for="historySearchInput">
                <i class="fas fa-search"></i>
                <input type="text" id="historySearchInput" name="history_q" value="<?php echo htmlspecialchars($whatsAppHistorySearch, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Rechercher par utilisateur, invite, numero ou code evenement">
              </label>
              <select name="history_user_id" id="historyUserFilter" class="form-control clients-admin-filters">
                <option value="0">Tous les utilisateurs</option>
                <?php foreach ($whatsAppHistoryUsers as $whatsAppHistoryUser) { ?>
                <option value="<?php echo (int) ($whatsAppHistoryUser['client_user_id'] ?? 0); ?>" <?php echo (int) ($whatsAppHistoryUser['client_user_id'] ?? 0) === $whatsAppHistoryUserId ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) ($whatsAppHistoryUser['client_name'] ?? 'Utilisateur inconnu'), ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
              </select>
              <button type="submit" class="btn btn-primary">Filtrer</button>
              <?php if ($whatsAppHistorySearch !== '' || $whatsAppHistoryUserId > 0) { ?>
              <a href="index.php?page=clients&view=whatsapp-sends" class="btn btn-outline btn-secondary">Reinitialiser</a>
              <?php } else { ?>
              <span></span>
              <?php } ?>
            </form>
            <div class="clients-admin-history-meta">
              <span class="clients-admin-history-count"><?php echo (int) $whatsAppHistoryTotalRows; ?> envoi(s)</span>
              <span class="clients-admin-history-count">Page <?php echo (int) $whatsAppHistoryPage; ?> / <?php echo (int) $whatsAppHistoryTotalPages; ?></span>
            </div>
            <div class="card-body pt-20">
              <div class="table-responsive">
                <table class="table clients-admin-history-table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Date et heure d'envoi</th>
                      <th>Nom d'utilisateur</th>
                      <th>Invite et telephone</th>
                      <th>Evenement</th>
                      <th>Invitation</th>
                      <th>Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($whatsAppHistoryRows !== []) {
                      foreach ($whatsAppHistoryRows as $historyRow) {
                        $historyRecipientNumber = str_replace('whatsapp:', '', (string) ($historyRow['recipient_number'] ?? ''));
                        $historyMediaUrl = trim((string) ($historyRow['media_url'] ?? ''));
                        $historySentAtRaw = trim((string) ($historyRow['sent_at'] ?? ''));
                        $historySentAtLabel = $historySentAtRaw;
                        if ($historySentAtRaw !== '') {
                          try {
                            $historySentAtLabel = (new DateTimeImmutable($historySentAtRaw))->format('d/m/Y a H:i');
                          } catch (Exception $exception) {
                            $historySentAtLabel = $historySentAtRaw;
                          }
                        }
                        $historyFallbackUrl = '';
                        if ((int) ($historyRow['invite_id'] ?? 0) > 0 && (string) ($historyRow['event_code'] ?? '') !== '') {
                          $historyFallbackUrl = '../pages/invitation_elect.php?cod=' . urlencode((string) $historyRow['invite_id']) . '&event=' . urlencode((string) $historyRow['event_code']);
                        }
                        $historyInvitationUrl = $historyMediaUrl !== '' ? $historyMediaUrl : $historyFallbackUrl;
                        $historyStatus = (string) ($historyRow['send_status'] ?? 'inconnu');
                        $historyStatusClass = $historyStatus === 'sent' ? 'is-success' : ($historyStatus === 'failed' ? 'is-danger' : '');
                    ?>
                    <tr>
                      <td>
                        <span class="clients-admin-history-date"><?php echo htmlspecialchars($historySentAtLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                      </td>
                      <td>
                        <div class="clients-admin-history-user">
                          <strong><?php echo htmlspecialchars((string) ($historyRow['client_name'] ?? 'Utilisateur inconnu'), ENT_QUOTES, 'UTF-8'); ?></strong>
                          <span>Code evenement : <?php echo htmlspecialchars((string) ($historyRow['event_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                      </td>
                      <td>
                        <div class="clients-admin-history-invite">
                          <strong><?php echo htmlspecialchars((string) ($historyRow['invite_name'] ?? $historyRow['recipient_name'] ?? 'Invite'), ENT_QUOTES, 'UTF-8'); ?></strong>
                          <span><?php echo htmlspecialchars($historyRecipientNumber !== '' ? $historyRecipientNumber : 'Numero indisponible', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                      </td>
                      <td>
                        <span class="clients-admin-history-event">Invite ID : <?php echo (int) ($historyRow['invite_id'] ?? 0); ?></span>
                      </td>
                      <td>
                        <?php if ($historyInvitationUrl !== '') { ?>
                        <a href="<?php echo htmlspecialchars($historyInvitationUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="clients-admin-link-btn">
                          <i class="fas fa-eye"></i>
                          Voir l'invitation
                        </a>
                        <?php } else { ?>
                        <span class="clients-admin-history-event">Lien indisponible</span>
                        <?php } ?>
                      </td>
                      <td>
                        <span class="clients-admin-status-badge <?php echo $historyStatusClass; ?>"><?php echo htmlspecialchars($historyStatus === 'sent' ? 'Envoye' : ($historyStatus === 'failed' ? 'Echoue' : $historyStatus), ENT_QUOTES, 'UTF-8'); ?></span>
                      </td>
                    </tr>
                    <?php }
                    } else { ?>
                    <tr>
                      <td colspan="6"><div class="clients-admin-empty">Aucun envoi WhatsApp n'a encore ete historise.</div></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
            <?php if ($whatsAppHistoryTotalRows > 0) {
              $whatsAppHistoryRangeStart = (($whatsAppHistoryPage - 1) * $whatsAppHistoryPerPage) + 1;
              $whatsAppHistoryRangeEnd = min($whatsAppHistoryTotalRows, $whatsAppHistoryPage * $whatsAppHistoryPerPage);
              $whatsAppHistoryPageStart = max(1, $whatsAppHistoryPage - 2);
              $whatsAppHistoryPageEnd = min($whatsAppHistoryTotalPages, $whatsAppHistoryPage + 2);
            ?>
            <div class="clients-admin-pagination">
              <div class="clients-admin-pagination-summary">Affichage de <?php echo $whatsAppHistoryRangeStart; ?> a <?php echo $whatsAppHistoryRangeEnd; ?> sur <?php echo (int) $whatsAppHistoryTotalRows; ?> envoi(s).</div>
              <div class="clients-admin-pagination-links">
                <?php if ($whatsAppHistoryPage > 1) { ?>
                <a href="index.php?<?php echo htmlspecialchars(http_build_query($whatsAppHistoryBaseParams + ['history_page' => $whatsAppHistoryPage - 1]), ENT_QUOTES, 'UTF-8'); ?>" class="clients-admin-pagination-link">Precedent</a>
                <?php } ?>
                <?php for ($historyPageNumber = $whatsAppHistoryPageStart; $historyPageNumber <= $whatsAppHistoryPageEnd; $historyPageNumber++) { ?>
                <a href="index.php?<?php echo htmlspecialchars(http_build_query($whatsAppHistoryBaseParams + ['history_page' => $historyPageNumber]), ENT_QUOTES, 'UTF-8'); ?>" class="clients-admin-pagination-link <?php echo $historyPageNumber === $whatsAppHistoryPage ? 'is-active' : ''; ?>"><?php echo $historyPageNumber; ?></a>
                <?php } ?>
                <?php if ($whatsAppHistoryPage < $whatsAppHistoryTotalPages) { ?>
                <a href="index.php?<?php echo htmlspecialchars(http_build_query($whatsAppHistoryBaseParams + ['history_page' => $whatsAppHistoryPage + 1]), ENT_QUOTES, 'UTF-8'); ?>" class="clients-admin-pagination-link">Suivant</a>
                <?php } ?>
              </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="clients-analytics-shell">
          <div class="clients-analytics-head">
            <div>
              <h5>Dashboard invitations electroniques</h5>
              <p>Vue <?php echo htmlspecialchars($analyticsScopeLabel, ENT_QUOTES, 'UTF-8'); ?> - cout Twilio fixe a 0,005 USD par message envoye.</p>
            </div>
            <form method="get" action="" class="clients-analytics-filter">
              <input type="hidden" name="page" value="clients">
              <input type="hidden" name="view" value="clients">
              <input type="hidden" name="q" value="<?php echo htmlspecialchars($clientSearch, ENT_QUOTES, 'UTF-8'); ?>">
              <input type="hidden" name="filter" value="<?php echo htmlspecialchars($clientFilter, ENT_QUOTES, 'UTF-8'); ?>">
              <select name="stats_client_id" class="form-control clients-admin-filters">
                <option value="0">Situation globale</option>
                <?php foreach ($analyticsClients as $analyticsClient) { ?>
                <option value="<?php echo (int) ($analyticsClient['cod_user'] ?? 0); ?>" <?php echo (int) ($analyticsClient['cod_user'] ?? 0) === $statsClientUserId ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars((string) ($analyticsClient['noms'] ?? 'Client'), ENT_QUOTES, 'UTF-8'); ?>
                </option>
                <?php } ?>
              </select>
              <input type="number" name="quota_threshold" value="<?php echo (int) $quotaThreshold; ?>" class="form-control clients-admin-filters" min="1" style="max-width:120px;" title="Seuil quota">
              <button type="submit" class="btn btn-primary">Appliquer</button>
            </form>
          </div>

          <div class="clients-analytics-export">
            <a class="btn btn-outline btn-primary" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'daily', 'format' => 'csv', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>">CSV journalier</a>
            <a class="btn btn-outline btn-info" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'daily', 'format' => 'excel', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>">Excel journalier</a>
            <a class="btn btn-outline btn-primary" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'monthly', 'format' => 'csv', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>">CSV mensuel</a>
            <a class="btn btn-outline btn-info" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'monthly', 'format' => 'excel', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>">Excel mensuel</a>
            <a class="btn btn-outline btn-primary" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'clients', 'format' => 'csv', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>">CSV par client</a>
            <a class="btn btn-outline btn-info" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'clients', 'format' => 'excel', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>">Excel par client</a>
          </div>

          <?php if ($lowQuotaNotifications !== []) { ?>
          <div class="clients-analytics-alerts" id="lowQuotaAlertsBox" data-threshold="<?php echo (int) $quotaThreshold; ?>">
            <h6>Notifications automatiques quota faible (seuil <?php echo (int) $quotaThreshold; ?>)</h6>
            <ul class="clients-analytics-alert-list">
              <?php foreach (array_slice($lowQuotaNotifications, 0, 8) as $lowQuotaAlert) { ?>
              <li>
                <?php echo htmlspecialchars((string) ($lowQuotaAlert['client_name'] ?? 'Client'), ENT_QUOTES, 'UTF-8'); ?> :
                <?php echo (int) ($lowQuotaAlert['remaining_quota'] ?? 0); ?> restant(s)
                <?php if (!empty($lowQuotaAlert['invitation_sending_suspended'])) { ?>
                  (envoi suspendu)
                <?php } ?>
              </li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>

          <div class="clients-analytics-grid">
            <div class="clients-analytics-kpi">
              <span>Envoyes aujourd hui</span>
              <strong><?php echo (int) ($invitationAnalytics['sent_today'] ?? 0); ?></strong>
              <small>USD <?php echo htmlspecialchars((string) ($invitationAnalytics['cost_today_usd'] ?? '0.000'), ENT_QUOTES, 'UTF-8'); ?></small>
            </div>
            <div class="clients-analytics-kpi">
              <span>Envoyes ce mois</span>
              <strong><?php echo (int) ($invitationAnalytics['sent_month'] ?? 0); ?></strong>
              <small>USD <?php echo htmlspecialchars((string) ($invitationAnalytics['cost_month_usd'] ?? '0.000'), ENT_QUOTES, 'UTF-8'); ?></small>
            </div>
            <div class="clients-analytics-kpi">
              <span>Envoyes au total</span>
              <strong><?php echo (int) ($invitationAnalytics['sent_total'] ?? 0); ?></strong>
              <small>USD <?php echo htmlspecialchars((string) ($invitationAnalytics['cost_total_usd'] ?? '0.000'), ENT_QUOTES, 'UTF-8'); ?></small>
            </div>
          </div>

          <div class="clients-analytics-charts">
            <div class="clients-analytics-chart">
              <div id="clientsDailyAreaChart"></div>
            </div>
            <div class="clients-analytics-chart">
              <div id="clientsMonthlyBarChart"></div>
            </div>
            <?php if ($analyticsTopClients !== []) { ?>
            <div class="clients-analytics-chart clients-analytics-chart--full">
              <div id="clientsTopHorizontalChart"></div>
            </div>
            <?php } ?>
          </div>

          <div class="clients-analytics-panels">
            <section class="clients-analytics-panel">
              <h6>Evolution journaliere (30 jours)</h6>
              <div class="table-responsive">
                <table class="clients-analytics-table">
                  <thead>
                    <tr>
                      <th>Jour</th>
                      <th>Envois</th>
                      <th>Cout USD</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($analyticsDailyRows !== []) { ?>
                    <?php foreach ($analyticsDailyRows as $dailyRow) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string) ($dailyRow['day_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo (int) ($dailyRow['sent_count'] ?? 0); ?></td>
                      <td><?php echo htmlspecialchars((string) ($dailyRow['cost_usd'] ?? '0.000'), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr><td colspan="3">Aucune donnee</td></tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </section>

            <section class="clients-analytics-panel">
              <h6>Evolution mensuelle (12 mois)</h6>
              <div class="table-responsive">
                <table class="clients-analytics-table">
                  <thead>
                    <tr>
                      <th>Mois</th>
                      <th>Envois</th>
                      <th>Cout USD</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($analyticsMonthlyRows !== []) { ?>
                    <?php foreach ($analyticsMonthlyRows as $monthlyRow) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string) ($monthlyRow['month_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo (int) ($monthlyRow['sent_count'] ?? 0); ?></td>
                      <td><?php echo htmlspecialchars((string) ($monthlyRow['cost_usd'] ?? '0.000'), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr><td colspan="3">Aucune donnee</td></tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>

              <?php if ($analyticsTopClients !== []) { ?>
              <h6 style="margin-top:14px;">Top clients (global)</h6>
              <div class="table-responsive">
                <table class="clients-analytics-table">
                  <thead>
                    <tr>
                      <th>Client</th>
                      <th>Envois</th>
                      <th>USD</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($analyticsTopClients as $topClientRow) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string) ($topClientRow['client_name'] ?? 'Client'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo (int) ($topClientRow['sent_count'] ?? 0); ?></td>
                      <td><?php echo htmlspecialchars((string) ($topClientRow['cost_usd'] ?? '0.000'), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
              <?php } ?>
            </section>
          </div>
        </div>

        <div class="card rounded-4 clients-admin-card">
            <div class="box-header d-flex b-0 justify-content-between align-items-center flex-wrap" style="gap:16px;">
                <div>
                  <h4 class="box-title mb-0">Gestion des clients</h4>
                  <p class="mb-0" style="margin-top:6px;color:#64748b;font-size:14px;">Recherchez un client, suivez ses quotas WhatsApp et gerez ses evenements plus rapidement.</p>
                </div>
                <div>
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClientModal">Nouveau client</button>
                </div>
                <div class="clients-admin-meta">
                  <span class="clients-admin-pill" id="clientsVisibleCounter">Visibles : <?php echo (int) $visibleClientCount; ?></span>
                  <span class="clients-admin-pill is-warning">Credits faibles : <?php echo (int) $clientsLowCreditCount; ?></span>
                  <span class="clients-admin-pill is-neutral">Sans evenement : <?php echo (int) $clientsWithoutEventCount; ?></span>
                  <span class="clients-admin-pill is-neutral">Bloques : <?php echo (int) $clientsBlockedCount; ?></span>
                  <span class="clients-admin-pill is-neutral">Invitations suspendues : <?php echo (int) $clientsInvitationSuspendedCount; ?></span>
                </div>
            </div>

            <div class="card-body pt-0">
                <form action="" method="get" class="clients-admin-toolbar">
                  <input type="hidden" name="page" value="clients">
                  <label class="clients-admin-search" for="clientSearchInput">
                    <i class="fas fa-search"></i>
                    <input type="text" id="clientSearchInput" name="q" value="<?php echo htmlspecialchars($clientSearch, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Rechercher par nom, email, telephone ou code evenement">
                  </label>
                  <select name="filter" id="clientFilterSelect" class="form-control clients-admin-filters">
                    <option value="all" <?php echo $clientFilter === 'all' ? 'selected' : ''; ?>>Tous les clients</option>
                    <option value="with-events" <?php echo $clientFilter === 'with-events' ? 'selected' : ''; ?>>Avec evenement</option>
                    <option value="without-events" <?php echo $clientFilter === 'without-events' ? 'selected' : ''; ?>>Sans evenement</option>
                    <option value="low-credit" <?php echo $clientFilter === 'low-credit' ? 'selected' : ''; ?>>Credits faibles</option>
                    <option value="active-sends" <?php echo $clientFilter === 'active-sends' ? 'selected' : ''; ?>>Envois actifs</option>
                    <option value="blocked" <?php echo $clientFilter === 'blocked' ? 'selected' : ''; ?>>Clients bloques</option>
                    <option value="invitation-suspended" <?php echo $clientFilter === 'invitation-suspended' ? 'selected' : ''; ?>>Envois suspendus</option>
                  </select>
                  <button type="submit" class="btn btn-primary">Filtrer</button>
                  <?php if ($clientSearch !== '' || $clientFilter !== 'all') { ?>
                  <a href="index.php?page=clients" class="btn btn-outline btn-secondary">Reinitialiser</a>
                  <?php } else { ?>
                  <span></span>
                  <?php } ?>
                </form>

                <p class="clients-admin-note">Astuce : la recherche agit deja sur le nom, l'email, le telephone et les codes d'evenements affiches.</p>

                <div class="clients-admin-table-wrap">
                  <div class="table-responsive">
                    <table class="table clients-admin-table align-middle" id="clientsAdminGrid">
                      <thead>
                        <tr>
                          <th>Client</th>
                          <th class="text-end">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                    <?php if ($filteredClientQuotaRows !== []) {
                      foreach ($filteredClientQuotaRows as $row_client) {
                        $quotaOverview = (array) ($row_client['quota_overview'] ?? []);
                        $clientEvents = (array) ($quotaOverview['events'] ?? []);
                        $clientName = ucfirst((string) ($row_client['noms'] ?? 'Client'));
                        $clientId = (int) ($row_client['cod_user'] ?? 0);
                        $clientControl = (array) ($row_client['control'] ?? []);
                        $isClientBlocked = (string) ($clientControl['account_status'] ?? 'active') === 'blocked';
                        $isInvitationSuspended = !empty($clientControl['invitation_sending_suspended']);
                        $detailModalId = 'clientDetailModal' . $clientId;
                        $editModalId = 'clientEditModal' . $clientId;
                        $deleteModalId = 'clientDeleteModal' . $clientId;
                        $clientSearchIndex = $formatSearchValue($clientName . ' ' . (string) ($row_client['email'] ?? '') . ' ' . (string) ($row_client['phone'] ?? ''));
                        foreach ($clientEvents as $clientEvent) {
                          $clientSearchIndex .= ' ' . $formatSearchValue((string) ($clientEvent['event_label'] ?? '') . ' ' . (string) ($clientEvent['event_code'] ?? ''));
                        }
                    ?>
                    <tr class="clients-admin-row" data-client-search="<?php echo htmlspecialchars($clientSearchIndex, ENT_QUOTES, 'UTF-8'); ?>">
                      <td>
                        <div class="clients-admin-main">
                          <strong><?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></strong>
                          <span class="clients-admin-sub">Compte client #<?php echo $clientId; ?></span>
                          <span class="clients-admin-sub"><?php echo htmlspecialchars((string) ($row_client['email'] ?? 'Aucun email'), ENT_QUOTES, 'UTF-8'); ?><br><?php echo htmlspecialchars((string) ($row_client['phone'] ?? 'Aucun telephone'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="clients-admin-badges">
                          <span class="clients-admin-badge"><?php echo (int) ($quotaOverview['event_count'] ?? 0); ?> evenement(s)</span>
                          <?php if ((int) ($quotaOverview['sent_count'] ?? 0) > 0) { ?>
                          <span class="clients-admin-badge is-success"><?php echo (int) $quotaOverview['sent_count']; ?> envoi(s)</span>
                          <?php } ?>
                          <?php if ((int) ($quotaOverview['remaining_quota'] ?? 0) <= 50) { ?>
                          <span class="clients-admin-badge is-alert">Credit faible</span>
                          <?php } ?>
                          <?php if ($isClientBlocked) { ?>
                          <span class="clients-admin-badge is-alert">Compte bloque</span>
                          <?php } ?>
                          <?php if ($isInvitationSuspended) { ?>
                          <span class="clients-admin-badge is-alert">Invitations suspendues</span>
                          <?php } ?>
                          <?php if ((int) ($quotaOverview['event_count'] ?? 0) === 0) { ?>
                          <span class="clients-admin-badge is-neutral">Sans evenement</span>
                          <?php } ?>
                        </div>
                      </td>
                      <td class="clients-admin-actions">
                        <div class="dropdown">
                          <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:18px;"></i></a>
                          <div class="dropdown-menu dropdown-menu-end">
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#<?php echo $detailModalId; ?>">Detail</button>
                            <?php if ((string) ($row_client['type_user'] ?? '') === '2') { ?>
                            <form action="" method="post">
                              <input type="hidden" name="impersonate_user_id" value="<?php echo $clientId; ?>">
                              <button type="submit" class="dropdown-item">Se connecter</button>
                            </form>
                            <?php } ?>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#<?php echo $editModalId; ?>">Modifier</button>
                            <form action="" method="post">
                              <input type="hidden" name="toggle_block_client_id" value="<?php echo $clientId; ?>">
                              <input type="hidden" name="block_state" value="<?php echo $isClientBlocked ? '0' : '1'; ?>">
                              <input type="hidden" name="control_reason" value="Action admin depuis module clients">
                              <button type="submit" class="dropdown-item"><?php echo $isClientBlocked ? 'Activer le compte' : 'Bloquer le compte'; ?></button>
                            </form>
                            <form action="" method="post">
                              <input type="hidden" name="toggle_invitation_suspend_client_id" value="<?php echo $clientId; ?>">
                              <input type="hidden" name="suspend_state" value="<?php echo $isInvitationSuspended ? '0' : '1'; ?>">
                              <input type="hidden" name="control_reason" value="Action admin depuis module clients">
                              <button type="submit" class="dropdown-item"><?php echo $isInvitationSuspended ? 'Reprendre les envois' : 'Suspendre les envois'; ?></button>
                            </form>
                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#<?php echo $deleteModalId; ?>">Supprimer</button>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <div class="modal fade" id="<?php echo $detailModalId; ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content rounded-4">
                          <div class="modal-header">
                            <div>
                              <h5 class="modal-title mb-0"><?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></h5>
                              <small class="text-muted">Vue detaillee du client et de ses quotas.</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                          </div>
                          <div class="modal-body">
                            <div class="clients-admin-detail-grid">
                              <div class="clients-admin-detail-card"><span>Envois</span><strong><?php echo (int) ($quotaOverview['sent_count'] ?? 0); ?></strong></div>
                              <div class="clients-admin-detail-card"><span>Quota total</span><strong><?php echo (int) ($quotaOverview['total_quota'] ?? 0); ?></strong></div>
                              <div class="clients-admin-detail-card"><span>Restants</span><strong><?php echo (int) ($quotaOverview['remaining_quota'] ?? 0); ?></strong></div>
                              <div class="clients-admin-detail-card"><span>Evenements</span><strong><?php echo (int) ($quotaOverview['event_count'] ?? 0); ?></strong></div>
                            </div>
                            <div class="clients-admin-main" style="margin-bottom:18px;">
                              <strong><?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></strong>
                              <strong><?php echo htmlspecialchars((string) ($row_client['email'] ?? 'Aucun email'), ENT_QUOTES, 'UTF-8'); ?></strong>
                              <span class="clients-admin-sub"><?php echo htmlspecialchars((string) ($row_client['phone'] ?? 'Aucun telephone'), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="clients-admin-badges" style="margin-bottom:18px;">
                              <span class="clients-admin-badge"><?php echo (int) ($quotaOverview['event_count'] ?? 0); ?> evenement(s)</span>
                              <?php if ((int) ($quotaOverview['sent_count'] ?? 0) > 0) { ?>
                              <span class="clients-admin-badge is-success"><?php echo (int) ($quotaOverview['sent_count'] ?? 0); ?> envoi(s)</span>
                              <?php } ?>
                              <?php if ((int) ($quotaOverview['remaining_quota'] ?? 0) <= 50) { ?>
                              <span class="clients-admin-badge is-alert">Credit faible</span>
                              <?php } ?>
                              <?php if ((int) ($quotaOverview['event_count'] ?? 0) === 0) { ?>
                              <span class="clients-admin-badge is-neutral">Sans evenement</span>
                              <?php } ?>
                            </div>
                            <?php if ($clientEvents !== []) { ?>
                            <form action="" method="post" class="d-flex align-items-center flex-wrap" style="gap:8px; margin-bottom: 16px;">
                              <input type="hidden" name="quota_scope" value="global">
                              <input type="hidden" name="quota_event_code" value="ALL">
                              <input type="hidden" name="quota_client_user_id" value="<?php echo $clientId; ?>">
                              <input type="number" name="bonus_quota_add" class="form-control" min="1" step="1" value="100" style="max-width:180px;" required>
                              <button type="submit" class="btn btn-sm btn-success">Ajouter un quota global</button>
                              <small class="text-muted">Applique le bonus a tous les evenements du client.</small>
                            </form>
                            <?php } ?>
                            <?php if ($clientEvents !== []) { ?>
                            <div class="clients-admin-modal-events">
                              <?php foreach ($clientEvents as $clientEvent) {
                                $eventTotalQuota = max(1, (int) ($clientEvent['total_quota'] ?? 0));
                                $eventRemainingQuota = (int) ($clientEvent['remaining_quota'] ?? 0);
                                $eventUsagePercent = min(100, max(0, (int) round(($eventRemainingQuota / $eventTotalQuota) * 100)));
                              ?>
                              <section class="clients-admin-event">
                                <div class="clients-admin-event-head">
                                  <div>
                                    <h6 class="clients-admin-event-title"><?php echo htmlspecialchars((string) ($clientEvent['event_label'] ?? 'Evenement'), ENT_QUOTES, 'UTF-8'); ?></h6>
                                    <div class="clients-admin-event-code">Code evenement : <?php echo htmlspecialchars((string) ($clientEvent['event_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                  </div>
                                  <div class="clients-admin-event-stats">
                                    Envoyes <strong><?php echo (int) ($clientEvent['sent_count'] ?? 0); ?></strong>
                                    | Restants <strong><?php echo $eventRemainingQuota; ?></strong>
                                    | Bonus <strong>+<?php echo (int) ($clientEvent['bonus_quota'] ?? 0); ?></strong>
                                  </div>
                                </div>
                                <div class="clients-admin-progress">
                                  <div class="clients-admin-progress-bar" style="width: <?php echo $eventUsagePercent; ?>%;"></div>
                                </div>
                                <form action="" method="post" class="d-flex align-items-center flex-wrap" style="gap:8px;">
                                  <input type="hidden" name="quota_scope" value="event">
                                  <input type="hidden" name="quota_event_code" value="<?php echo htmlspecialchars((string) ($clientEvent['event_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                  <input type="hidden" name="quota_client_user_id" value="<?php echo $clientId; ?>">
                                  <input type="number" name="bonus_quota_add" class="form-control" min="1" step="1" value="50" style="max-width:150px;" required>
                                  <button type="submit" class="btn btn-sm btn-outline btn-success">Ajouter du credit</button>
                                </form>
                              </section>
                              <?php } ?>
                            </div>
                            <?php } else { ?>
                            <div class="clients-admin-empty">Aucun evenement rattache a ce client pour le moment.</div>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="modal fade" id="<?php echo $editModalId; ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                          <div class="modal-header">
                            <h5 class="modal-title mb-0">Modifier <?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                          </div>
                          <div class="modal-body">
                            <form action="" method="post" class="clients-admin-form-grid">
                              <input type="hidden" name="save_client_id" value="<?php echo $clientId; ?>">
                              <label class="is-full">
                                <span>Noms</span>
                                <input type="text" name="noms" value="<?php echo htmlspecialchars((string) ($row_client['noms'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                              </label>
                              <label>
                                <span>Telephone</span>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars((string) ($row_client['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                              </label>
                              <label>
                                <span>Email</span>
                                <input type="email" name="email" value="<?php echo htmlspecialchars((string) ($row_client['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                              </label>
                              <div class="is-full d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="modal fade" id="<?php echo $deleteModalId; ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                          <div class="modal-header">
                            <h5 class="modal-title mb-0">Supprimer <?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                          </div>
                          <div class="modal-body">
                            <p class="clients-admin-danger-note">Cette suppression est definitive. Elle reste bloquee si le client possede encore des evenements.</p>
                            <form action="" method="post" class="d-flex justify-content-end gap-2">
                              <input type="hidden" name="delete_client_id" value="<?php echo $clientId; ?>">
                              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                              <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php }
                    } else { ?>
                    <tr id="clientsAdminEmptyRow"><td colspan="2"><div class="clients-admin-empty" id="clientsAdminEmpty">Aucun client ne correspond a votre recherche ou a votre filtre.</div></td></tr>
                    <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <?php if ($filteredClientQuotaRows !== []) { ?>
                <div class="clients-admin-empty" id="clientsAdminEmpty" style="display:none;">Aucun client ne correspond a la recherche en cours.</div>
                <?php } ?>
            </div>	
        </div>

        <div class="modal fade" id="createClientModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
              <div class="modal-header">
                <h5 class="modal-title mb-0">Creer un nouveau client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <p class="clients-admin-note">Si vous laissez le mot de passe vide, un mot de passe provisoire sera genere automatiquement.</p>
                <form action="" method="post" class="clients-admin-form-grid">
                  <input type="hidden" name="create_client_submit" value="1">
                  <label class="is-full">
                    <span>Noms et prenoms</span>
                    <input type="text" name="noms" required>
                  </label>
                  <label>
                    <span>Telephone (format +243...)</span>
                    <input type="text" name="phone" required>
                  </label>
                  <label>
                    <span>Email</span>
                    <input type="email" name="email" required>
                  </label>
                  <label>
                    <span>Mot de passe (optionnel)</span>
                    <input type="text" name="password" autocomplete="new-password">
                  </label>
                  <label>
                    <span>Confirmation mot de passe</span>
                    <input type="text" name="confirm_password" autocomplete="new-password">
                  </label>
                  <div class="is-full d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Creer le client</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
          <?php } ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const dailyRows = <?php echo json_encode($analyticsDailyRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const monthlyRows = <?php echo json_encode($analyticsMonthlyRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const topClientRows = <?php echo json_encode($analyticsTopClients, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const lowQuotaRows = <?php echo json_encode($lowQuotaNotifications, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const quotaThreshold = <?php echo (int) $quotaThreshold; ?>;

  if (typeof ApexCharts !== 'undefined') {
    const dailyChartElement = document.getElementById('clientsDailyAreaChart');
    if (dailyChartElement) {
      const dailyLabels = dailyRows.map(function (row) { return String(row.day_key || ''); });
      const dailySeries = dailyRows.map(function (row) { return Number(row.sent_count || 0); });
      const dailyCostSeries = dailyRows.map(function (row) { return Number(row.cost_usd || 0); });

      const dailyChart = new ApexCharts(dailyChartElement, {
        chart: { type: 'area', height: 290, toolbar: { show: false } },
        series: [
          { name: 'Invitations', data: dailySeries },
          { name: 'Cout USD', data: dailyCostSeries }
        ],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: [3, 2] },
        colors: ['#2563eb', '#0f766e'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.34, opacityTo: 0.05 } },
        xaxis: { categories: dailyLabels },
        yaxis: [
          { title: { text: 'Invitations' } },
          { opposite: true, title: { text: 'USD' } }
        ],
        title: { text: 'Tendance journaliere', align: 'left', style: { fontSize: '14px', fontWeight: '700' } },
        grid: { borderColor: '#eef2f7' }
      });
      dailyChart.render();
    }

    const monthlyChartElement = document.getElementById('clientsMonthlyBarChart');
    if (monthlyChartElement) {
      const monthlyLabels = monthlyRows.map(function (row) { return String(row.month_key || ''); });
      const monthlySeries = monthlyRows.map(function (row) { return Number(row.sent_count || 0); });

      const monthlyChart = new ApexCharts(monthlyChartElement, {
        chart: { type: 'bar', height: 290, toolbar: { show: false } },
        series: [{ name: 'Invitations', data: monthlySeries }],
        colors: ['#0ea5e9'],
        plotOptions: { bar: { borderRadius: 8, columnWidth: '46%' } },
        dataLabels: { enabled: false },
        xaxis: { categories: monthlyLabels },
        title: { text: 'Volume mensuel', align: 'left', style: { fontSize: '14px', fontWeight: '700' } },
        grid: { borderColor: '#eef2f7' }
      });
      monthlyChart.render();
    }

    const topChartElement = document.getElementById('clientsTopHorizontalChart');
    if (topChartElement && Array.isArray(topClientRows) && topClientRows.length > 0) {
      const topLabels = topClientRows.map(function (row) { return String(row.client_name || 'Client'); });
      const topSeries = topClientRows.map(function (row) { return Number(row.sent_count || 0); });

      const topChart = new ApexCharts(topChartElement, {
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
        series: [{ name: 'Invitations', data: topSeries }],
        colors: ['#f59e0b'],
        plotOptions: { bar: { horizontal: true, borderRadius: 8 } },
        dataLabels: { enabled: false },
        xaxis: { categories: topLabels },
        title: { text: 'Top clients consommation', align: 'left', style: { fontSize: '14px', fontWeight: '700' } },
        grid: { borderColor: '#eef2f7' }
      });
      topChart.render();
    }
  }

  if (Array.isArray(lowQuotaRows) && lowQuotaRows.length > 0) {
    const alertFingerprint = lowQuotaRows
      .slice(0, 10)
      .map(function (row) { return String(row.client_user_id || 0) + ':' + String(row.remaining_quota || 0); })
      .join('|');
    const todayKey = new Date().toISOString().slice(0, 10);
    const storageKey = 'isapp_low_quota_alerts_' + todayKey + '_' + String(quotaThreshold);
    const previousFingerprint = window.localStorage ? localStorage.getItem(storageKey) : null;

    if (previousFingerprint !== alertFingerprint) {
      if (window.localStorage) {
        localStorage.setItem(storageKey, alertFingerprint);
      }

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'warning',
          title: 'Alerte quota faible',
          text: lowQuotaRows.length + ' client(s) sont en dessous du seuil de ' + quotaThreshold + ' invitations.',
          confirmButtonText: 'Voir'
        });
      }
    }
  }

  const input = document.getElementById('clientSearchInput');
  const cards = Array.from(document.querySelectorAll('[data-client-search]'));
  const emptyState = document.getElementById('clientsAdminEmpty');
  const visibleCounter = document.getElementById('clientsVisibleCounter');

  if (!input || cards.length === 0 || !emptyState || !visibleCounter) {
    return;
  }

  const render = function () {
    const query = (input.value || '').toLocaleLowerCase();
    let visibleCount = 0;

    cards.forEach(function (card) {
      const haystack = (card.getAttribute('data-client-search') || '').toLocaleLowerCase();
      const visible = query === '' || haystack.indexOf(query) !== -1;
      card.classList.toggle('is-hidden', !visible);
      if (visible) {
        visibleCount += 1;
      }
    });

    visibleCounter.textContent = 'Visibles : ' + visibleCount;
    emptyState.style.display = visibleCount === 0 ? '' : 'none';
  };

  input.addEventListener('input', render);
  render();
});
</script>















 






























  
					</div>

					
				</div> 
			</section>
			<!-- /.content -->
		</div>
  <!-- /.content-wrapper -->
	<?php include('footer.php')?>
  <!-- Side panel --> 
  <!-- quick_user_toggle -->
  <div class="modal modal-right fade" id="quick_user_toggle" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content slim-scroll3">
		  <div class="modal-body p-30 bg-white">
			<div class="d-flex align-items-center justify-content-between pb-30">
				<h4 class="m-0">User Profile
				<small class="text-fade fs-12 ms-5">12 messages</small></h4>
				<a href="#" class="btn btn-icon btn-danger-light btn-sm no-shadow" data-bs-dismiss="modal">
					<span class="fa fa-close"></span>
				</a>
			</div>
            <div>
                <div class="d-flex flex-row">
                    <div class=""><img src="html/images/avatar/avatar-2.png" alt="user" class="rounded bg-danger-light w-150" width="100"></div>
                    <div class="ps-20">
                        <h5 class="mb-0">Nil Yeager</h5>
                        <p class="my-5 text-fade">Web Designer</p>
                        <a href="mailto:dummy@gmail.com"><span class="icon-Mail-notification me-5 text-success"><span class="path1"></span><span class="path2"></span></span> dummy@gmail.com</a>
                        <button class="btn btn-success-light btn-sm mt-5"><i class="ti-plus"></i> Follow</button>
                    </div>
                </div>
			</div>
              <div class="dropdown-divider my-30"></div>
              <div>
                <div class="d-flex align-items-center mb-30">
                    <div class="me-15 bg-primary-light h-50 w-50 l-h-60 rounded text-center">
                          <span class="icon-Library fs-24"><span class="path1"></span><span class="path2"></span></span>
                    </div>
                    <div class="d-flex flex-column fw-500">
                        <a href="extra_profile.html" class="text-dark hover-primary mb-1 fs-16">My Profile</a>
                        <span class="text-fade">Account settings and more</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-30">
                    <div class="me-15 bg-danger-light h-50 w-50 l-h-60 rounded text-center">
                        <span class="icon-Write fs-24"><span class="path1"></span><span class="path2"></span></span>
                    </div>
                    <div class="d-flex flex-column fw-500">
                        <a href="mailbox.html" class="text-dark hover-danger mb-1 fs-16">My Messages</a>
                        <span class="text-fade">Inbox and tasks</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-30">
                    <div class="me-15 bg-success-light h-50 w-50 l-h-60 rounded text-center">
                        <span class="icon-Group-chat fs-24"><span class="path1"></span><span class="path2"></span></span>
                    </div>
                    <div class="d-flex flex-column fw-500">
                        <a href="setting.html" class="text-dark hover-success mb-1 fs-16">Settings</a>
                        <span class="text-fade">Accout Settings</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-30">
                    <div class="me-15 bg-info-light h-50 w-50 l-h-60 rounded text-center">
                        <span class="icon-Attachment1 fs-24"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                    </div>
                    <div class="d-flex flex-column fw-500">
                        <a href="extra_taskboard.html" class="text-dark hover-info mb-1 fs-16">Project</a>
                        <span class="text-fade">latest tasks and projects</span>
                    </div>
                </div>
              </div>
              <div class="dropdown-divider my-30"></div>
              <div>
                <div class="media-list">
                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">10:10</h4>
                      <div class="media-body ps-15 bs-5 rounded border-primary">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Johne</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">08:40</h4>
                      <div class="media-body ps-15 bs-5 rounded border-success">
                        <p>Proin iaculis eros non odio ornare efficitur.</p>
                        <span class="text-fade">by Amla</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">07:10</h4>
                      <div class="media-body ps-15 bs-5 rounded border-info">
                        <p>In mattis mi ut posuere consectetur.</p>
                        <span class="text-fade">by Josef</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">01:15</h4>
                      <div class="media-body ps-15 bs-5 rounded border-danger">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Rima</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">23:12</h4>
                      <div class="media-body ps-15 bs-5 rounded border-warning">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Alaxa</span>
                      </div>
                    </a>
                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">10:10</h4>
                      <div class="media-body ps-15 bs-5 rounded border-primary">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Johne</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">08:40</h4>
                      <div class="media-body ps-15 bs-5 rounded border-success">
                        <p>Proin iaculis eros non odio ornare efficitur.</p>
                        <span class="text-fade">by Amla</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">07:10</h4>
                      <div class="media-body ps-15 bs-5 rounded border-info">
                        <p>In mattis mi ut posuere consectetur.</p>
                        <span class="text-fade">by Josef</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">01:15</h4>
                      <div class="media-body ps-15 bs-5 rounded border-danger">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Rima</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">23:12</h4>
                      <div class="media-body ps-15 bs-5 rounded border-warning">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Alaxa</span>
                      </div>
                    </a>
                  </div>
            </div>
		  </div>
		</div>
	  </div>
  </div>
  <!-- /quick_user_toggle --> 
    

  <!-- Control Sidebar -->
  <aside class="control-sidebar">
	  
	<div class="rpanel-title"><span class="pull-right btn btn-circle btn-danger" data-toggle="control-sidebar"><i class="ion ion-close text-white" ></i></span> </div>  <!-- Create the tabs -->
    <ul class="nav nav-tabs control-sidebar-tabs">
      <li class="nav-item"><a href="#control-sidebar-home-tab" data-bs-toggle="tab" ><i class="mdi mdi-message-text"></i></a></li>
      <li class="nav-item"><a href="#control-sidebar-settings-tab" data-bs-toggle="tab"><i class="mdi mdi-playlist-check"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
      <!-- Home tab content -->
      <div class="tab-pane" id="control-sidebar-home-tab">
          <div class="flexbox">
			<a href="javascript:void(0)" class="text-grey">
				<i class="ti-more"></i>
			</a>	
			<p>Users</p>
			<a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
		  </div>
		  <div class="lookup lookup-sm lookup-right d-none d-lg-block">
			<input type="text" name="s" placeholder="Search" class="w-p100">
		  </div>
          <div class="media-list media-list-hover mt-20">
			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-success" href="#">
				<img src="html/images/avatar/1.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Tyler</strong></a>
				</p>
				<p>Praesent tristique diam...</p>
				  <span>Just now</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-danger" href="#">
				<img src="html/images/avatar/2.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Luke</strong></a>
				</p>
				<p>Cras tempor diam ...</p>
				  <span>33 min ago</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-warning" href="#">
				<img src="html/images/avatar/3.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Evan</strong></a>
				</p>
				<p>In posuere tortor vel...</p>
				  <span>42 min ago</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-primary" href="#">
				<img src="html/images/avatar/4.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Evan</strong></a>
				</p>
				<p>In posuere tortor vel...</p>
				  <span>42 min ago</span>
			  </div>
			</div>			
			
			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-success" href="#">
				<img src="html/images/avatar/1.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Tyler</strong></a>
				</p>
				<p>Praesent tristique diam...</p>
				  <span>Just now</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-danger" href="#">
				<img src="html/images/avatar/2.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Luke</strong></a>
				</p>
				<p>Cras tempor diam ...</p>
				  <span>33 min ago</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-warning" href="#">
				<img src="html/images/avatar/3.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Evan</strong></a>
				</p>
				<p>In posuere tortor vel...</p>
				  <span>42 min ago</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-primary" href="#">
				<img src="html/images/avatar/4.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Evan</strong></a>
				</p>
				<p>In posuere tortor vel...</p>
				  <span>42 min ago</span>
			  </div>
			</div>
			  
		  </div>

      </div>
      <!-- /.tab-pane -->
      <!-- Settings tab content -->
      <div class="tab-pane" id="control-sidebar-settings-tab">
          <div class="flexbox">
			<a href="javascript:void(0)" class="text-grey">
				<i class="ti-more"></i>
			</a>	
			<p>Todo List</p>
			<a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
		  </div>
        <ul class="todo-list mt-20">
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_1" class="filled-in">
			  <label for="basic_checkbox_1" class="mb-0 h-15"></label>
			  <!-- todo text -->
			  <span class="text-line">Nulla vitae purus</span>
			  <!-- Emphasis label -->
			  <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
			  <!-- General tools such as edit or delete-->
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_2" class="filled-in">
			  <label for="basic_checkbox_2" class="mb-0 h-15"></label>
			  <span class="text-line">Phasellus interdum</span>
			  <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_3" class="filled-in">
			  <label for="basic_checkbox_3" class="mb-0 h-15"></label>
			  <span class="text-line">Quisque sodales</span>
			  <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_4" class="filled-in">
			  <label for="basic_checkbox_4" class="mb-0 h-15"></label>
			  <span class="text-line">Proin nec mi porta</span>
			  <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_5" class="filled-in">
			  <label for="basic_checkbox_5" class="mb-0 h-15"></label>
			  <span class="text-line">Maecenas scelerisque</span>
			  <small class="badge bg-primary"><i class="fa fa-clock-o"></i> 1 week</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_6" class="filled-in">
			  <label for="basic_checkbox_6" class="mb-0 h-15"></label>
			  <span class="text-line">Vivamus nec orci</span>
			  <small class="badge bg-info"><i class="fa fa-clock-o"></i> 1 month</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_7" class="filled-in">
			  <label for="basic_checkbox_7" class="mb-0 h-15"></label>
			  <!-- todo text -->
			  <span class="text-line">Nulla vitae purus</span>
			  <!-- Emphasis label -->
			  <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
			  <!-- General tools such as edit or delete-->
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_8" class="filled-in">
			  <label for="basic_checkbox_8" class="mb-0 h-15"></label>
			  <span class="text-line">Phasellus interdum</span>
			  <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_9" class="filled-in">
			  <label for="basic_checkbox_9" class="mb-0 h-15"></label>
			  <span class="text-line">Quisque sodales</span>
			  <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_10" class="filled-in">
			  <label for="basic_checkbox_10" class="mb-0 h-15"></label>
			  <span class="text-line">Proin nec mi porta</span>
			  <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
		  </ul>
      </div>
      <!-- /.tab-pane -->
    </div>
  </aside>
  <!-- /.control-sidebar -->
  
  <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>     
  

  
  
</div>
<!-- ./wrapper -->
	
	
		
	<div id="chat-box-body">
		<div id="chat-circle" class="waves-effect waves-circle btn btn-circle btn-sm btn-warning l-h-50">
            <div id="chat-overlay"></div>
            <span class="icon-Group-chat fs-18"><span class="path1"></span><span class="path2"></span></span>
		</div>

		<div class="chat-box">
            <div class="chat-box-header p-15 d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button class="waves-effect waves-circle btn btn-circle btn-primary-light h-40 w-40 rounded-circle l-h-45" type="button" data-bs-toggle="dropdown">
                      <span class="icon-Add-user fs-22"><span class="path1"></span><span class="path2"></span></span>
                  </button>
                  <div class="dropdown-menu min-w-200">
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Color me-15"></span>
                        New Group</a>
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Clipboard me-15"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                        Contacts</a>
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Group me-15"><span class="path1"></span><span class="path2"></span></span>
                        Groups</a>
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Active-call me-15"><span class="path1"></span><span class="path2"></span></span>
                        Calls</a>
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Settings1 me-15"><span class="path1"></span><span class="path2"></span></span>
                        Settings</a>
                    <div class="dropdown-divider"></div>
					<a class="dropdown-item fs-16" href="#">
                        <span class="icon-Question-circle me-15"><span class="path1"></span><span class="path2"></span></span>
                        Help</a>
					<a class="dropdown-item fs-16" href="#">
                        <span class="icon-Notifications me-15"><span class="path1"></span><span class="path2"></span></span> 
                        Privacy</a>
                  </div>
                </div>
                <div class="text-center flex-grow-1">
                    <div class="text-dark fs-18">Support</div>
                    <div>
                        <span class="badge badge-sm badge-dot badge-primary"></span>
                        <span class="text-muted fs-12">Active</span>
                    </div>
                </div>
                <div class="chat-box-toggle">
                    <button id="chat-box-toggle" class="waves-effect waves-circle btn btn-circle btn-danger-light h-40 w-40 rounded-circle l-h-45" type="button">
                      <span class="icon-Close fs-22"><span class="path1"></span><span class="path2"></span></span>
                    </button>                    
                </div>
            </div>
            <div class="chat-box-body">
                
				<?php // include ('chatsupport.php')?>

            </div>
            <div class="chat-input">      
                <form>
                    <input type="text" id="chat-input" placeholder="Besoin d'aide ?"/>
                    <button type="submit" class="chat-submit" id="chat-submit">
                        <span class="icon-Send fs-22"></span>
                    </button>
                </form>      
            </div>
		</div>
	</div>
	
	<!-- Page Content overlay -->
	
	
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
	<!-- Vendor JS -->
	<script src="html/template/horizontal/src/js/vendors.min.js"></script>
	<script src="html/template/horizontal/src/js/pages/chat-popup.js"></script>
  	<script src="html/assets/icons/feather-icons/feather.min.js"></script>
  	<script src="html/assets/vendor_components/Flot/jquery.flot.js"></script>
	<script src="html/assets/vendor_components/Flot/jquery.flot.resize.js"></script>
	<script src="html/assets/vendor_components/Flot/jquery.flot.pie.js"></script>
	<script src="html/assets/vendor_components/Flot/jquery.flot.categories.js"></script>
	<script src="html/assets/vendor_components/echarts/dist/echarts-en.min.js"></script>
	<script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
	<script src="html/assets/vendor_plugins/bootstrap-slider/bootstrap-slider.js"></script>
	<script src="html/assets/vendor_components/OwlCarousel2/dist/owl.carousel.js"></script>
	<script src="html/assets/vendor_components/flexslider/jquery.flexslider.js"></script>
	<script src="html/assets/vendor_components/Web-Ticker-master/jquery.webticker.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

	
	<!-- Cartiy Admin App -->
	<script src="html/template/horizontal/src/js/demo.js"></script>
	<script src="html/template/horizontal/src/js/jquery.smartmenus.js"></script>
	<script src="html/template/horizontal/src/js/menus.js"></script>
	<script src="html/template/horizontal/src/js/template.js"></script>
	<script src="html/template/horizontal/src/js/pages/dashboard.js"></script>
	<script src="html/template/horizontal/src/js/pages/slider.js"></script>

	
	<!-- Vendor JS --> 
	<script src="html/assets/vendor_components/full-calendar/moment.js"></script>
	<script src="html/assets/vendor_components/full-calendar/fullcalendar.min.js"></script> 

	
	
	<!-- selecter JS --> 
	<script src="html/assets/vendor_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
	<script src="html/assets/vendor_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
	<script src="html/assets/vendor_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
	<script src="html/assets/vendor_components/select2/dist/js/select2.full.js"></script>
	<script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.js"></script>
	<script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
	<script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.extensions.js"></script>
	<script src="html/assets/vendor_components/moment/min/moment.min.js"></script>
	<script src="html/assets/vendor_components/bootstrap-daterangepicker/daterangepicker.js"></script>
	<script src="html/assets/vendor_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	<script src="html/assets/vendor_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
	<script src="html/assets/vendor_plugins/timepicker/bootstrap-timepicker.min.js"></script>
	<script src="html/assets/vendor_plugins/iCheck/icheck.min.js"></script>
	 
	<script src="html/template/horizontal/src/js/pages/advanced-form-element.js"></script>
	  