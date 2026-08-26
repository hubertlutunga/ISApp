
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
  $whatsAppHistoryPeriod = trim((string) ($_GET['history_period'] ?? 'all'));
  if (!in_array($whatsAppHistoryPeriod, ['all', 'today', 'month'], true)) {
    $whatsAppHistoryPeriod = 'all';
  }
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
  if ($whatsAppHistoryPeriod !== 'all') {
    $whatsAppHistoryBaseParams['history_period'] = $whatsAppHistoryPeriod;
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

      if ($whatsAppHistoryPeriod === 'today') {
        $historyWhereClauses[] = 'DATE(logs.sent_at) = CURRENT_DATE';
      } elseif ($whatsAppHistoryPeriod === 'month') {
        $historyWhereClauses[] = 'YEAR(logs.sent_at) = YEAR(CURRENT_DATE) AND MONTH(logs.sent_at) = MONTH(CURRENT_DATE)';
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
    :root {
      --is-primary: #146ef5;
      --is-primary-dark: #0b1f3a;
      --is-bg: #f5f7fb;
      --is-card: #ffffff;
      --is-text: #122033;
      --is-muted: #718096;
      --is-border: #e7ecf3;
      --is-success: #0f9d7a;
      --is-gold: #c7a764;
      --is-radius: 18px;
      --clients-ink-900: var(--is-text);
      --clients-ink-700: #334155;
      --clients-ink-500: var(--is-muted);
      --clients-primary: var(--is-success);
      --clients-primary-soft: #ccfbf1;
      --clients-accent: var(--is-primary);
      --clients-danger: #b91c1c;
      --clients-border: var(--is-border);
    }

    .content-wrapper {
      background:
        radial-gradient(circle at 10% -10%, rgba(20, 110, 245, 0.12) 0%, rgba(255, 255, 255, 0) 45%),
        radial-gradient(circle at 110% 10%, rgba(15, 157, 122, 0.08) 0%, rgba(255, 255, 255, 0) 35%),
        linear-gradient(180deg, var(--is-bg) 0%, #f9fbff 55%, #f4f7fc 100%);
    }

    .salut {
      margin-bottom: 8px;
    }

    .salut p {
      margin: 0;
      padding: 16px 18px;
      border-radius: 14px;
      border: 1px solid rgba(14, 165, 233, 0.22);
      background: linear-gradient(90deg, rgba(14, 165, 233, 0.1) 0%, rgba(20, 184, 166, 0.08) 100%);
      color: var(--clients-ink-700);
      font-weight: 700;
      letter-spacing: .01em;
    }

    .clients-admin-card {
      border: 0;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 24px 54px rgba(15, 23, 42, 0.1);
      background: linear-gradient(180deg, #ffffff 0%, #f8fcff 52%, #f8fafc 100%);
      border: 1px solid rgba(14, 165, 233, 0.16);
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

    .clients-admin-filters:focus,
    .clients-admin-form-grid input:focus,
    .clients-admin-search input:focus {
      border-color: var(--clients-accent);
      box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.18);
    }

    .clients-admin-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin: 14px 0 4px;
    }

    .clients-analytics-shell {
      border: 1px solid var(--is-border);
      border-radius: 22px;
      background: var(--is-card);
      box-shadow: 0 18px 42px rgba(11, 31, 58, 0.08);
      padding: 26px;
      margin-bottom: 20px;
    }

    .clients-analytics-hero {
      display: grid;
      grid-template-columns: 1.35fr 1fr;
      gap: 16px;
      align-items: stretch;
      margin-bottom: 18px;
    }

    .clients-analytics-hero-main h2 {
      margin: 0;
      color: var(--is-primary-dark);
      font-size: 34px;
      line-height: 1.12;
      font-weight: 900;
      letter-spacing: -0.02em;
    }

    .clients-analytics-hero-main p {
      margin: 10px 0 0;
      color: var(--is-muted);
      font-size: 15px;
      line-height: 1.75;
      max-width: 740px;
    }

    .clients-analytics-price-chip {
      margin-top: 14px;
      display: inline-flex;
      gap: 8px;
      align-items: center;
      border-radius: 999px;
      padding: 8px 14px;
      border: 1px solid #d7e2f3;
      background: #f8fbff;
      color: #2f425f;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .02em;
    }

    .clients-analytics-filter-card {
      border-radius: var(--is-radius);
      border: 1px solid var(--is-border);
      background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
      padding: 14px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 10px;
    }

    .clients-analytics-filter-label {
      margin: 0;
      color: var(--is-primary-dark);
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .08em;
    }

    .clients-analytics-filter {
      display: grid;
      grid-template-columns: 1.1fr 108px 128px;
      gap: 8px;
      align-items: center;
    }

    .clients-analytics-apply {
      height: 48px;
      border: 0;
      border-radius: 14px;
      background: linear-gradient(90deg, var(--is-primary) 0%, #2a8cff 100%);
      color: #fff;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: .01em;
      box-shadow: 0 14px 24px rgba(20, 110, 245, 0.28);
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .clients-analytics-apply:hover,
    .clients-analytics-apply:focus-visible {
      transform: translateY(-1px);
      box-shadow: 0 18px 28px rgba(20, 110, 245, 0.32);
    }

    .clients-analytics-apply.is-loading {
      opacity: .88;
      pointer-events: none;
    }

    .clients-analytics-export {
      border-radius: var(--is-radius);
      border: 1px solid var(--is-border);
      background: #fbfdff;
      padding: 14px;
      margin-bottom: 16px;
    }

    .clients-analytics-export-head {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
      color: var(--is-primary-dark);
      font-size: 14px;
      font-weight: 700;
    }

    .clients-analytics-export-groups {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px;
    }

    .clients-analytics-export-group {
      border: 1px solid var(--is-border);
      border-radius: 14px;
      background: #fff;
      padding: 10px;
      display: grid;
      gap: 8px;
    }

    .clients-analytics-export-group h6 {
      margin: 0;
      color: #41536c;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .clients-analytics-export-links {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .clients-analytics-export-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border: 1px solid #d8e3f2;
      border-radius: 11px;
      background: #fff;
      color: #355073;
      text-decoration: none;
      padding: 7px 10px;
      font-size: 12px;
      font-weight: 700;
      transition: all .2s ease;
    }

    .clients-analytics-export-btn:hover,
    .clients-analytics-export-btn:focus-visible {
      color: var(--is-primary);
      border-color: #9dc2ff;
      background: #f5f9ff;
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
      gap: 14px;
      margin-bottom: 14px;
    }

    .clients-analytics-chart {
      border: 1px solid var(--is-border);
      border-radius: var(--is-radius);
      background: #fff;
      padding: 16px;
      min-height: 350px;
      box-shadow: 0 10px 24px rgba(11, 31, 58, 0.05);
    }

    .clients-analytics-chart-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 8px;
    }

    .clients-analytics-chart-head h6 {
      margin: 0;
      color: var(--is-primary-dark);
      font-size: 15px;
      font-weight: 800;
    }

    .clients-analytics-chart-head p {
      margin: 4px 0 0;
      color: var(--is-muted);
      font-size: 13px;
    }

    .clients-analytics-chart-menu {
      border: 0;
      background: #f2f6fd;
      color: #486184;
      width: 30px;
      height: 30px;
      border-radius: 10px;
      font-weight: 900;
    }

    .clients-chart-empty {
      min-height: 260px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px dashed #cbd5e1;
      border-radius: 12px;
      color: #64748b;
      font-size: 13px;
      font-weight: 700;
      background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
      text-align: center;
      padding: 12px;
    }

    .clients-analytics-chart--full {
      grid-column: 1 / -1;
      min-height: 320px;
    }

    .clients-analytics-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
      margin-bottom: 14px;
    }

    .clients-analytics-kpi {
      border: 1px solid var(--is-border);
      border-radius: var(--is-radius);
      background: #fff;
      padding: 16px;
      min-height: 182px;
      box-shadow: 0 10px 24px rgba(11, 31, 58, 0.06);
      transition: transform .25s ease, box-shadow .25s ease;
    }

    .clients-analytics-kpi.is-clients {
      background: linear-gradient(135deg, #faf7ff 0%, #ffffff 72%);
      border-color: #ece7ff;
    }

    .clients-analytics-kpi.is-today {
      background: linear-gradient(135deg, #f3f8ff 0%, #ffffff 72%);
      border-color: #dfebff;
    }

    .clients-analytics-kpi.is-month {
      background: linear-gradient(135deg, #f5f5ff 0%, #ffffff 72%);
      border-color: #e5e5ff;
    }

    .clients-analytics-kpi.is-total {
      background: linear-gradient(135deg, #f1fdf9 0%, #ffffff 72%);
      border-color: #d9f6eb;
    }

    .clients-analytics-kpi:hover {
      transform: translateY(-3px);
      box-shadow: 0 16px 28px rgba(11, 31, 58, 0.1);
    }

    .clients-analytics-kpi-link {
      display: block;
      color: inherit;
      text-decoration: none;
      border-radius: var(--is-radius);
    }

    .clients-analytics-kpi-link:hover,
    .clients-analytics-kpi-link:focus-visible {
      color: inherit;
      transform: translateY(-2px);
      box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
    }

    .clients-analytics-kpi-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 8px;
      margin-bottom: 10px;
    }

    .clients-analytics-kpi-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      border: 1px solid transparent;
    }

    .clients-analytics-kpi.is-clients .clients-analytics-kpi-icon {
      color: #4f46e5;
      background: #eef2ff;
      border-color: #dfe3ff;
    }

    .clients-analytics-kpi.is-today .clients-analytics-kpi-icon {
      color: #146ef5;
      background: #edf4ff;
      border-color: #d9e7ff;
    }

    .clients-analytics-kpi.is-month .clients-analytics-kpi-icon {
      color: #5b5bd6;
      background: #f1f1ff;
      border-color: #e0e0ff;
    }

    .clients-analytics-kpi.is-total .clients-analytics-kpi-icon {
      color: #0f9d7a;
      background: #ebfbf6;
      border-color: #ccf3e8;
    }

    .clients-analytics-kpi span {
      display: block;
      color: var(--is-muted);
      font-size: 13px;
      margin-bottom: 8px;
      font-weight: 600;
    }

    .clients-analytics-kpi strong {
      display: block;
      color: var(--is-primary-dark);
      font-size: 36px;
      line-height: 1;
      font-weight: 800;
      letter-spacing: -0.02em;
    }

    .clients-analytics-kpi small {
      display: block;
      margin-top: 8px;
      color: #2f4d75;
      font-size: 13px;
      font-weight: 600;
    }

    .clients-analytics-kpi em {
      display: inline-block;
      margin-top: 10px;
      color: #375a82;
      font-size: 12px;
      font-style: normal;
      font-weight: 600;
      transition: color .2s ease;
    }

    .clients-analytics-kpi-link:hover em,
    .clients-analytics-kpi-link:focus-visible em {
      color: var(--is-primary);
    }

    .clients-analytics-insights {
      border: 1px solid var(--is-border);
      border-radius: var(--is-radius);
      background: #fff;
      padding: 14px;
      margin-bottom: 14px;
    }

    .clients-analytics-insights-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 10px;
    }

    .clients-analytics-insight {
      border: 1px solid #edf2f9;
      border-radius: 14px;
      background: #fbfdff;
      padding: 12px;
    }

    .clients-analytics-insight:nth-child(1) {
      background: linear-gradient(135deg, #f5f9ff 0%, #ffffff 90%);
      border-color: #e1ebfb;
    }

    .clients-analytics-insight:nth-child(2) {
      background: linear-gradient(135deg, #f4f6ff 0%, #ffffff 90%);
      border-color: #e3e7ff;
    }

    .clients-analytics-insight:nth-child(3) {
      background: linear-gradient(135deg, #f7f4ff 0%, #ffffff 90%);
      border-color: #e9e3ff;
    }

    .clients-analytics-insight:nth-child(4) {
      background: linear-gradient(135deg, #f2fbf7 0%, #ffffff 90%);
      border-color: #ddf3e9;
    }

    .clients-analytics-insight span {
      display: block;
      color: #6b7f99;
      font-size: 12px;
      margin-bottom: 6px;
      font-weight: 600;
    }

    .clients-analytics-insight strong {
      display: block;
      color: #0f284c;
      font-size: 20px;
      line-height: 1.2;
      font-weight: 700;
    }

    .clients-analytics-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .clients-analytics-table th,
    .clients-analytics-table td {
      border-bottom: 1px solid #edf2f9;
      padding: 10px 8px;
      text-align: left;
    }

    .clients-analytics-table th {
      color: #64809d;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .04em;
      font-size: 11px;
    }

    .clients-analytics-table td {
      color: #193457;
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
      width: 112px;
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

    .clients-admin-row.is-paged-out {
      display: none;
    }

    .clients-admin-table tbody tr {
      transition: background-color .18s ease;
    }

    .clients-admin-table tbody tr:hover {
      background: #f8fbff;
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

    .clients-admin-controls {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 14px;
      padding: 12px;
      border: 1px solid var(--clients-border);
      border-radius: 14px;
      background: #fff;
    }

    .clients-admin-view-switch {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px;
      border: 1px solid var(--clients-border);
      border-radius: 12px;
      background: #f8fafc;
    }

    .clients-admin-view-btn {
      border: 0;
      background: transparent;
      color: var(--clients-ink-700);
      font-size: 12px;
      font-weight: 800;
      border-radius: 9px;
      padding: 8px 10px;
    }

    .clients-admin-view-btn.is-active {
      background: linear-gradient(90deg, #0ea5e9 0%, #0f766e 100%);
      color: #fff;
      box-shadow: 0 10px 18px rgba(15, 118, 110, 0.22);
    }

    .clients-admin-sort-controls {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      margin-left: auto;
    }

    .clients-admin-sort-controls select {
      min-width: 145px;
      height: 40px;
      border: 1px solid var(--clients-border);
      border-radius: 10px;
      font-size: 13px;
      font-weight: 700;
      color: var(--clients-ink-700);
      background: #fff;
      padding: 0 10px;
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
      grid-template-columns: minmax(220px, 1.4fr) minmax(180px, 0.8fr) minmax(170px, 0.7fr) auto auto;
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

    .clients-admin-table-wrap.is-cards-view .clients-admin-table thead {
      display: none;
    }

    .clients-admin-table-wrap.is-cards-view .clients-admin-table tbody {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
      padding: 14px;
    }

    .clients-admin-table-wrap.is-cards-view .clients-admin-table tbody tr {
      display: block;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06);
    }

    .clients-admin-table-wrap.is-cards-view .clients-admin-table tbody td {
      display: block;
      width: 100%;
      padding: 14px;
      border-bottom: 1px solid #eef2f7;
      text-align: left;
    }

    .clients-admin-table-wrap.is-cards-view .clients-admin-table tbody td:last-child {
      border-bottom: 0;
      width: auto;
    }

    .clients-admin-table-wrap.is-cards-view .clients-admin-table tbody td::before {
      content: attr(data-label);
      display: block;
      color: #64748b;
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-bottom: 8px;
    }

    .clients-admin-table-wrap.is-cards-view .clients-admin-actions {
      text-align: left;
    }

    .clients-admin-js-pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      flex-wrap: wrap;
      margin-top: 14px;
      padding: 14px;
      border: 1px solid var(--clients-border);
      border-radius: 16px;
      background: #fff;
    }

    .clients-admin-js-pagination[hidden] {
      display: none;
    }

    .clients-admin-js-pagination-summary {
      color: var(--clients-ink-500);
      font-size: 13px;
      font-weight: 700;
    }

    .clients-admin-js-pagination-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .clients-admin-js-page {
      min-width: 38px;
      height: 38px;
      border-radius: 11px;
      border: 1px solid #dbe4f0;
      background: #fff;
      color: var(--clients-ink-700);
      font-size: 12px;
      font-weight: 800;
      padding: 0 10px;
    }

    .clients-admin-js-page:hover:not([disabled]) {
      border-color: #93c5fd;
      color: #0b4b8f;
      background: #eff6ff;
    }

    .clients-admin-js-page.is-active {
      border-color: #0f766e;
      background: #0f766e;
      color: #fff;
    }

    .clients-admin-js-page[disabled] {
      opacity: .45;
      cursor: not-allowed;
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

      .clients-analytics-shell {
        padding: 20px;
      }

      .clients-analytics-hero {
        grid-template-columns: 1fr;
      }

      .clients-analytics-hero-main h2 {
        font-size: 30px;
      }

      .clients-analytics-filter {
        grid-template-columns: 1fr 120px 138px;
      }

      .clients-analytics-export-groups {
        grid-template-columns: 1fr;
      }

      .clients-analytics-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .clients-analytics-insights-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
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

      .clients-admin-table-wrap.is-cards-view .clients-admin-table tbody {
        grid-template-columns: 1fr;
      }

      .clients-admin-detail-grid,
      .clients-admin-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 575px) {
      .clients-analytics-hero-main h2 {
        font-size: 25px;
      }

      .clients-analytics-filter {
        grid-template-columns: 1fr;
      }

      .clients-analytics-apply,
      .clients-analytics-filter .clients-admin-filters {
        width: 100%;
      }

      .clients-analytics-grid {
        grid-template-columns: 1fr;
      }

      .clients-analytics-insights-grid {
        grid-template-columns: 1fr;
      }

      .clients-admin-table thead th,
      .clients-admin-table tbody td {
        padding: 14px 12px;
      }

      .clients-admin-table th:last-child,
      .clients-admin-table td:last-child {
        width: 84px;
      }

      .clients-admin-actions .btn {
        padding-left: 10px;
        padding-right: 10px;
      }

      .clients-admin-stats {
        grid-template-columns: 1fr;
      }

      .clients-admin-sort-controls {
        width: 100%;
        margin-left: 0;
      }

      .clients-admin-sort-controls select {
        min-width: 0;
        width: 100%;
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
    if ($analyticsTopClients === []) {
      $globalInvitationAnalytics = AdminClientManagementService::buildInvitationAnalytics($pdo, 0);
      $analyticsTopClients = (array) ($globalInvitationAnalytics['top_clients'] ?? []);
    }
    $analyticsClients = (array) ($invitationAnalytics['clients'] ?? []);
    $analyticsScopeLabel = 'Tous les clients';
    $sentToday = (int) ($invitationAnalytics['sent_today'] ?? 0);
    $sentMonth = (int) ($invitationAnalytics['sent_month'] ?? 0);
    $sentTotal = (int) ($invitationAnalytics['sent_total'] ?? 0);
    $costToday = (float) ($invitationAnalytics['cost_today_usd'] ?? 0);
    $daysInCurrentMonth = max(1, (int) date('j'));
    $averageDailyThisMonth = $sentMonth / $daysInCurrentMonth;
    $monthSharePercent = $sentTotal > 0 ? (($sentMonth / $sentTotal) * 100) : 0;
    $topClientName = (string) (($analyticsTopClients[0]['client_name'] ?? '') ?: 'Aucun client');
    $topClientSentCount = (int) ($analyticsTopClients[0]['sent_count'] ?? 0);

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
              <select name="history_period" id="historyPeriodFilter" class="form-control clients-admin-filters">
                <option value="all" <?php echo $whatsAppHistoryPeriod === 'all' ? 'selected' : ''; ?>>Periode: Tout</option>
                <option value="today" <?php echo $whatsAppHistoryPeriod === 'today' ? 'selected' : ''; ?>>Periode: Aujourd hui</option>
                <option value="month" <?php echo $whatsAppHistoryPeriod === 'month' ? 'selected' : ''; ?>>Periode: Ce mois</option>
              </select>
              <button type="submit" class="btn btn-primary">Filtrer</button>
              <?php if ($whatsAppHistorySearch !== '' || $whatsAppHistoryUserId > 0 || $whatsAppHistoryPeriod !== 'all') { ?>
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
          <div class="clients-analytics-hero">
            <div class="clients-analytics-hero-main">
              <h2>Dashboard Invitations electroniques</h2>
              <p>Suivez vos envois, votre consommation Twilio et l activite de vos clients en temps reel.</p>
              <span class="clients-analytics-price-chip"><i class="fas fa-circle" style="font-size:8px;color:var(--is-gold);"></i> Cout Twilio : 0,005 USD / message</span>
            </div>
            <div class="clients-analytics-filter-card">
              <p class="clients-analytics-filter-label">Contexte analytique</p>
              <form id="clientsAnalyticsFilterForm" method="get" action="" class="clients-analytics-filter">
                <input type="hidden" name="page" value="clients">
                <input type="hidden" name="view" value="clients">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($clientSearch, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($clientFilter, ENT_QUOTES, 'UTF-8'); ?>">
                <select name="stats_client_id" class="form-control clients-admin-filters" title="Client cible">
                  <option value="0">Situation globale</option>
                  <?php foreach ($analyticsClients as $analyticsClient) { ?>
                  <option value="<?php echo (int) ($analyticsClient['cod_user'] ?? 0); ?>" <?php echo (int) ($analyticsClient['cod_user'] ?? 0) === $statsClientUserId ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string) ($analyticsClient['noms'] ?? 'Client'), ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                  <?php } ?>
                </select>
                <input type="number" name="quota_threshold" value="<?php echo (int) $quotaThreshold; ?>" class="form-control clients-admin-filters" min="1" title="Seuil quota">
                <button id="clientsAnalyticsApplyBtn" type="submit" class="clients-analytics-apply"><i class="fas fa-filter"></i> Appliquer</button>
              </form>
            </div>
          </div>

          <div class="clients-analytics-export">
            <div class="clients-analytics-export-head"><i class="fas fa-download"></i> Exporter les donnees</div>
            <div class="clients-analytics-export-groups">
              <div class="clients-analytics-export-group">
                <h6>Export journalier</h6>
                <div class="clients-analytics-export-links">
                  <a class="clients-analytics-export-btn" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'daily', 'format' => 'csv', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-file-alt"></i> CSV</a>
                  <a class="clients-analytics-export-btn" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'daily', 'format' => 'excel', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-file-excel"></i> Excel</a>
                </div>
              </div>
              <div class="clients-analytics-export-group">
                <h6>Export mensuel</h6>
                <div class="clients-analytics-export-links">
                  <a class="clients-analytics-export-btn" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'monthly', 'format' => 'csv', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-file-alt"></i> CSV</a>
                  <a class="clients-analytics-export-btn" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'monthly', 'format' => 'excel', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-file-excel"></i> Excel</a>
                </div>
              </div>
              <div class="clients-analytics-export-group">
                <h6>Export par client</h6>
                <div class="clients-analytics-export-links">
                  <a class="clients-analytics-export-btn" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'clients', 'format' => 'csv', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-file-alt"></i> CSV</a>
                  <a class="clients-analytics-export-btn" href="pages/clients_export.php?<?php echo htmlspecialchars(http_build_query(['scope' => 'clients', 'format' => 'excel', 'stats_client_id' => $statsClientUserId, 'quota_threshold' => $quotaThreshold]), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-file-excel"></i> Excel</a>
                </div>
              </div>
            </div>
          </div>

          <div class="clients-analytics-grid">
            <article class="clients-analytics-kpi is-clients">
              <div class="clients-analytics-kpi-top">
                <span>Clients actifs</span>
                <i class="fas fa-users clients-analytics-kpi-icon"></i>
              </div>
              <strong><?php echo (int) $total_ccli; ?></strong>
              <small>Base clients globale</small>
            </article>

            <a class="clients-analytics-kpi-link" href="index.php?<?php echo htmlspecialchars(http_build_query(['page' => 'clients', 'view' => 'whatsapp-sends', 'history_period' => 'today', 'history_user_id' => $statsClientUserId]), ENT_QUOTES, 'UTF-8'); ?>">
              <article class="clients-analytics-kpi is-today">
                <div class="clients-analytics-kpi-top">
                  <span>Envoyes aujourd hui</span>
                  <i class="fas fa-paper-plane clients-analytics-kpi-icon"></i>
                </div>
                <strong><?php echo $sentToday; ?></strong>
                <small><?php echo htmlspecialchars(number_format($costToday, 3, '.', ''), ENT_QUOTES, 'UTF-8'); ?> USD</small>
                <em>Voir les messages concernes <i class="fas fa-arrow-right"></i></em>
              </article>
            </a>

            <a class="clients-analytics-kpi-link" href="index.php?<?php echo htmlspecialchars(http_build_query(['page' => 'clients', 'view' => 'whatsapp-sends', 'history_period' => 'month', 'history_user_id' => $statsClientUserId]), ENT_QUOTES, 'UTF-8'); ?>">
              <article class="clients-analytics-kpi is-month">
                <div class="clients-analytics-kpi-top">
                  <span>Envoyes ce mois</span>
                  <i class="fas fa-calendar-alt clients-analytics-kpi-icon"></i>
                </div>
                <strong><?php echo $sentMonth; ?></strong>
                <small><?php echo htmlspecialchars((string) ($invitationAnalytics['cost_month_usd'] ?? '0.000'), ENT_QUOTES, 'UTF-8'); ?> USD</small>
                <em>Voir les messages concernes <i class="fas fa-arrow-right"></i></em>
              </article>
            </a>

            <a class="clients-analytics-kpi-link" href="index.php?<?php echo htmlspecialchars(http_build_query(['page' => 'clients', 'view' => 'whatsapp-sends', 'history_period' => 'all', 'history_user_id' => $statsClientUserId]), ENT_QUOTES, 'UTF-8'); ?>">
              <article class="clients-analytics-kpi is-total">
                <div class="clients-analytics-kpi-top">
                  <span>Envoyes au total</span>
                  <i class="fas fa-chart-line clients-analytics-kpi-icon"></i>
                </div>
                <strong><?php echo $sentTotal; ?></strong>
                <small><?php echo htmlspecialchars((string) ($invitationAnalytics['cost_total_usd'] ?? '0.000'), ENT_QUOTES, 'UTF-8'); ?> USD</small>
                <em>Voir les messages concernes <i class="fas fa-arrow-right"></i></em>
              </article>
            </a>
          </div>

          <section class="clients-analytics-insights">
            <div class="clients-analytics-insights-grid">
              <article class="clients-analytics-insight">
                <span>Cout moyen aujourd hui</span>
                <strong><?php echo htmlspecialchars(number_format($sentToday > 0 ? ($costToday / $sentToday) : 0.005, 3, '.', ''), ENT_QUOTES, 'UTF-8'); ?> USD / message</strong>
              </article>
              <article class="clients-analytics-insight">
                <span>Moyenne quotidienne ce mois</span>
                <strong><?php echo htmlspecialchars(number_format($averageDailyThisMonth, 1, '.', ''), ENT_QUOTES, 'UTF-8'); ?> messages</strong>
              </article>
              <article class="clients-analytics-insight">
                <span>Client le plus actif</span>
                <strong><?php echo htmlspecialchars($topClientName, ENT_QUOTES, 'UTF-8'); ?> (<?php echo $topClientSentCount; ?>)</strong>
              </article>
              <article class="clients-analytics-insight">
                <span>Part du mois dans le total</span>
                <strong><?php echo htmlspecialchars(number_format($monthSharePercent, 1, '.', ''), ENT_QUOTES, 'UTF-8'); ?> %</strong>
              </article>
            </div>
          </section>

          <div class="clients-analytics-charts">
            <div class="clients-analytics-chart clients-analytics-chart--full">
              <div class="clients-analytics-chart-head">
                <div>
                  <h6>Top clients (global)</h6>
                  <p>Comparatif des clients les plus actifs</p>
                </div>
                <button type="button" class="clients-analytics-chart-menu" aria-label="Options">...</button>
              </div>
              <div id="clientsTopHorizontalChart"></div>
            </div>
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

                <div class="clients-admin-controls">
                  <div class="clients-admin-view-switch" role="group" aria-label="Basculer l affichage clients">
                    <button type="button" class="clients-admin-view-btn is-active" id="clientsViewTableBtn">Vue tableau</button>
                    <button type="button" class="clients-admin-view-btn" id="clientsViewCardsBtn">Vue cartes</button>
                  </div>
                  <div class="clients-admin-sort-controls">
                    <select id="clientsSortColumn" title="Trier par">
                      <option value="name">Tri: Nom</option>
                      <option value="quota">Tri: Quota</option>
                      <option value="sent">Tri: Envois</option>
                      <option value="status">Tri: Statut</option>
                    </select>
                    <select id="clientsSortDirection" title="Ordre de tri">
                      <option value="asc">Ordre: Croissant</option>
                      <option value="desc">Ordre: Decroissant</option>
                    </select>
                    <select id="clientsPerPage" title="Elements par page">
                      <option value="10">10 / page</option>
                      <option value="25" selected>25 / page</option>
                      <option value="50">50 / page</option>
                    </select>
                  </div>
                </div>

                <div class="clients-admin-table-wrap" id="clientsAdminTableWrap">
                  <div class="table-responsive">
                    <table class="table clients-admin-table align-middle" id="clientsAdminGrid">
                      <thead>
                        <tr>
                          <th>Client</th>
                          <th>Quota</th>
                          <th>Envois</th>
                          <th>Statut</th>
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
                        $statusRank = $isClientBlocked ? 3 : ($isInvitationSuspended ? 2 : 1);
                        $statusLabel = $isClientBlocked ? 'Bloque' : ($isInvitationSuspended ? 'Suspendu' : 'Actif');
                        $editModalId = 'clientEditModal' . $clientId;
                        $deleteModalId = 'clientDeleteModal' . $clientId;
                        $clientSearchIndex = $formatSearchValue($clientName . ' ' . (string) ($row_client['email'] ?? '') . ' ' . (string) ($row_client['phone'] ?? ''));
                        foreach ($clientEvents as $clientEvent) {
                          $clientSearchIndex .= ' ' . $formatSearchValue((string) ($clientEvent['event_label'] ?? '') . ' ' . (string) ($clientEvent['event_code'] ?? ''));
                        }
                    ?>
                    <tr
                      class="clients-admin-row"
                      data-client-search="<?php echo htmlspecialchars($clientSearchIndex, ENT_QUOTES, 'UTF-8'); ?>"
                      data-client-name="<?php echo htmlspecialchars($formatSearchValue($clientName), ENT_QUOTES, 'UTF-8'); ?>"
                      data-total-quota="<?php echo (int) ($quotaOverview['total_quota'] ?? 0); ?>"
                      data-sent-count="<?php echo (int) ($quotaOverview['sent_count'] ?? 0); ?>"
                      data-status-rank="<?php echo (int) $statusRank; ?>"
                    >
                      <td data-label="Client">
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
                      <td data-label="Quota">
                        <div class="clients-admin-statstack">
                          <span><strong>Total:</strong> <?php echo (int) ($quotaOverview['total_quota'] ?? 0); ?></span>
                          <span><strong>Restants:</strong> <?php echo (int) ($quotaOverview['remaining_quota'] ?? 0); ?></span>
                        </div>
                      </td>
                      <td data-label="Envois">
                        <div class="clients-admin-statstack">
                          <span><strong><?php echo (int) ($quotaOverview['sent_count'] ?? 0); ?></strong> invitation(s)</span>
                        </div>
                      </td>
                      <td data-label="Statut">
                        <div class="clients-admin-badges">
                          <span class="clients-admin-badge <?php echo $statusRank > 1 ? 'is-alert' : 'is-success'; ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                      </td>
                      <td data-label="Actions" class="clients-admin-actions">
                        <div class="dropdown">
                          <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:18px;"></i></a>
                          <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="index.php?page=client_detail&amp;client_id=<?php echo $clientId; ?>">Detail</a>
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
                    <tr id="clientsAdminEmptyRow"><td colspan="5"><div class="clients-admin-empty" id="clientsAdminEmpty">Aucun client ne correspond a votre recherche ou a votre filtre.</div></td></tr>
                    <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="clients-admin-js-pagination" id="clientsAdminJsPagination" hidden>
                  <div class="clients-admin-js-pagination-summary" id="clientsAdminJsPaginationSummary"></div>
                  <div class="clients-admin-js-pagination-actions" id="clientsAdminJsPaginationActions"></div>
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

<?php
$analyticsTopClientsJson = json_encode(
  $analyticsTopClients,
  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
);

if ($analyticsTopClientsJson === false) {
  $analyticsTopClientsJson = '[]';
}
?>

<script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const topClientRows = <?php echo $analyticsTopClientsJson; ?>;
  const analyticsFilterForm = document.getElementById('clientsAnalyticsFilterForm');
  const analyticsApplyBtn = document.getElementById('clientsAnalyticsApplyBtn');

  if (analyticsFilterForm && analyticsApplyBtn) {
    analyticsFilterForm.addEventListener('submit', function () {
      analyticsApplyBtn.classList.add('is-loading');
      analyticsApplyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
    });
  }

  const chartNoData = function (elementId, message) {
    const el = document.getElementById(elementId);
    if (!el) {
      return;
    }
    el.innerHTML = '<div class="clients-chart-empty">' + message + '</div>';
  };

  const parseNumber = function (value) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
  };

  const initClientCharts = function () {
    if (typeof ApexCharts === 'undefined') {
      chartNoData('clientsTopHorizontalChart', 'ApexCharts indisponible. Verifiez le chargement de la librairie.');
      return;
    }

    const topChartElement = document.getElementById('clientsTopHorizontalChart');
    if (topChartElement && Array.isArray(topClientRows) && topClientRows.length > 0) {
      const topLabels = topClientRows.map(function (row) { return String(row.client_name || 'Client'); });
      const topSeries = topClientRows.map(function (row) { return parseNumber(row.sent_count || 0); });

      const topChart = new ApexCharts(topChartElement, {
        chart: { type: 'bar', height: 270, toolbar: { show: false }, zoom: { enabled: false } },
        series: [{ name: 'Invitations', data: topSeries }],
        colors: ['#0f9d7a'],
        plotOptions: { bar: { horizontal: true, borderRadius: 8, barHeight: '52%' } },
        dataLabels: { enabled: false },
        xaxis: { categories: topLabels, labels: { style: { colors: '#6b7f99' } } },
        yaxis: { labels: { style: { colors: '#6b7f99' } } },
        tooltip: { theme: 'light' },
        grid: { borderColor: '#edf2f9', strokeDashArray: 4 },
        noData: { text: 'Aucune donnee' }
      });
      topChart.render();
    } else if (topChartElement) {
      chartNoData('clientsTopHorizontalChart', 'Aucune donnee top clients pour cette vue.');
    }
  };

  initClientCharts();

  const input = document.getElementById('clientSearchInput');
  const cards = Array.from(document.querySelectorAll('[data-client-search]'));
  const emptyStateCandidates = Array.from(document.querySelectorAll('#clientsAdminEmpty'));
  const emptyState = emptyStateCandidates.length > 0 ? emptyStateCandidates[emptyStateCandidates.length - 1] : null;
  const visibleCounter = document.getElementById('clientsVisibleCounter');
  const paginationRoot = document.getElementById('clientsAdminJsPagination');
  const paginationSummary = document.getElementById('clientsAdminJsPaginationSummary');
  const paginationActions = document.getElementById('clientsAdminJsPaginationActions');
  const clientRows = Array.from(document.querySelectorAll('#clientsAdminGrid tbody .clients-admin-row'));
  const tableWrap = document.getElementById('clientsAdminTableWrap');
  const viewTableBtn = document.getElementById('clientsViewTableBtn');
  const viewCardsBtn = document.getElementById('clientsViewCardsBtn');
  const sortColumnSelect = document.getElementById('clientsSortColumn');
  const sortDirectionSelect = document.getElementById('clientsSortDirection');
  const perPageSelect = document.getElementById('clientsPerPage');
  const gridBody = document.querySelector('#clientsAdminGrid tbody');
  let perPage = 25;
  let currentPage = 1;
  let filteredRows = clientRows.slice();
  let currentSortColumn = sortColumnSelect ? sortColumnSelect.value : 'name';
  let currentSortDirection = sortDirectionSelect ? sortDirectionSelect.value : 'asc';

  if (!input || cards.length === 0 || !emptyState || !visibleCounter) {
    return;
  }

  const applyView = function (mode) {
    if (!tableWrap || !viewTableBtn || !viewCardsBtn) {
      return;
    }
    const isCards = mode === 'cards';
    tableWrap.classList.toggle('is-cards-view', isCards);
    viewTableBtn.classList.toggle('is-active', !isCards);
    viewCardsBtn.classList.toggle('is-active', isCards);
  };

  const getSortValue = function (row, column) {
    if (column === 'quota') {
      return Number(row.getAttribute('data-total-quota') || 0);
    }
    if (column === 'sent') {
      return Number(row.getAttribute('data-sent-count') || 0);
    }
    if (column === 'status') {
      return Number(row.getAttribute('data-status-rank') || 0);
    }
    return String(row.getAttribute('data-client-name') || '').toLocaleLowerCase();
  };

  const sortRows = function () {
    const directionMultiplier = currentSortDirection === 'desc' ? -1 : 1;
    filteredRows.sort(function (left, right) {
      const leftValue = getSortValue(left, currentSortColumn);
      const rightValue = getSortValue(right, currentSortColumn);

      if (typeof leftValue === 'string' || typeof rightValue === 'string') {
        return String(leftValue).localeCompare(String(rightValue)) * directionMultiplier;
      }

      if (leftValue === rightValue) {
        return 0;
      }

      return (leftValue > rightValue ? 1 : -1) * directionMultiplier;
    });

    if (gridBody) {
      filteredRows.forEach(function (row) {
        gridBody.appendChild(row);
      });
    }
  };

  const renderClientPagination = function () {
    if (!paginationRoot || !paginationSummary || !paginationActions) {
      return;
    }

    const totalRows = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / perPage));
    if (currentPage > totalPages) {
      currentPage = totalPages;
    }

    const startIndex = (currentPage - 1) * perPage;
    const endIndex = startIndex + perPage;

    filteredRows.forEach(function (row, index) {
      const isOnPage = index >= startIndex && index < endIndex;
      row.classList.toggle('is-paged-out', !isOnPage);
    });

    const visibleStart = totalRows === 0 ? 0 : startIndex + 1;
    const visibleEnd = totalRows === 0 ? 0 : Math.min(endIndex, totalRows);

    paginationSummary.textContent = 'Affichage ' + visibleStart + '-' + visibleEnd + ' sur ' + totalRows + ' client(s).';
    paginationActions.innerHTML = '';

    paginationRoot.hidden = totalRows <= perPage;

    const buildButton = function (label, page, disabled, active) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'clients-admin-js-page' + (active ? ' is-active' : '');
      button.textContent = label;
      if (disabled) {
        button.disabled = true;
      }
      button.addEventListener('click', function () {
        currentPage = page;
        renderClientPagination();
      });
      paginationActions.appendChild(button);
    };

    buildButton('Prev', Math.max(1, currentPage - 1), currentPage === 1, false);

    const pageWindowStart = Math.max(1, currentPage - 2);
    const pageWindowEnd = Math.min(totalPages, currentPage + 2);
    for (let pageNumber = pageWindowStart; pageNumber <= pageWindowEnd; pageNumber += 1) {
      buildButton(String(pageNumber), pageNumber, false, pageNumber === currentPage);
    }

    buildButton('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages, false);
  };

  const render = function () {
    const query = (input.value || '').toLocaleLowerCase();
    let visibleCount = 0;
    filteredRows = [];

    cards.forEach(function (card) {
      const haystack = (card.getAttribute('data-client-search') || '').toLocaleLowerCase();
      const visible = query === '' || haystack.indexOf(query) !== -1;
      card.classList.toggle('is-hidden', !visible);
      if (!visible) {
        card.classList.remove('is-paged-out');
      }
      if (visible) {
        filteredRows.push(card);
        visibleCount += 1;
      }
    });

    sortRows();
    currentPage = 1;
    renderClientPagination();
    visibleCounter.textContent = 'Visibles : ' + visibleCount;
    emptyState.style.display = visibleCount === 0 ? '' : 'none';
  };

  if (viewTableBtn && viewCardsBtn) {
    viewTableBtn.addEventListener('click', function () {
      applyView('table');
    });
    viewCardsBtn.addEventListener('click', function () {
      applyView('cards');
    });
  }

  if (sortColumnSelect) {
    sortColumnSelect.addEventListener('change', function () {
      currentSortColumn = sortColumnSelect.value || 'name';
      currentPage = 1;
      render();
    });
  }

  if (sortDirectionSelect) {
    sortDirectionSelect.addEventListener('change', function () {
      currentSortDirection = sortDirectionSelect.value || 'asc';
      currentPage = 1;
      render();
    });
  }

  if (perPageSelect) {
    perPageSelect.addEventListener('change', function () {
      const selectedPerPage = Number(perPageSelect.value || 25);
      perPage = selectedPerPage > 0 ? selectedPerPage : 25;
      currentPage = 1;
      renderClientPagination();
    });
  }

  applyView('table');

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
	  