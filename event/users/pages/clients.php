
  <?php
  $impersonationFlash = null;
  $quotaFlash = null;
  $clientFlash = null;
  $currentAdminUser = UserAccountService::currentSessionUser($pdo) ?? [];

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quota_event_code'], $_POST['quota_client_user_id'], $_POST['bonus_quota_add'])) {
    $eventCode = trim((string) $_POST['quota_event_code']);
    $clientUserId = (int) $_POST['quota_client_user_id'];
    $bonusQuotaAdd = (int) $_POST['bonus_quota_add'];

    try {
      if ($eventCode === '' || $clientUserId <= 0) {
        throw new RuntimeException('Informations de credit invalides.');
      }

      $updatedQuota = WhatsAppQuotaService::addBonusQuota($pdo, $eventCode, $clientUserId, $bonusQuotaAdd);
      $quotaFlash = [
        'type' => 'success',
        'message' => 'Le credit WhatsApp a ete mis a jour. Nouveau solde restant : ' . (int) ($updatedQuota['remaining_quota'] ?? 0) . '.',
      ];
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
      (int) ($currentAdminUser['cod_user'] ?? 0),
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
      (int) ($currentAdminUser['cod_user'] ?? 0),
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

    @media (max-width: 991px) {
      .clients-admin-toolbar {
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
    $clientSearch = trim((string) ($_GET['q'] ?? ''));
    $clientFilter = trim((string) ($_GET['filter'] ?? 'all'));
    $allowedClientFilters = ['all', 'with-events', 'without-events', 'low-credit', 'active-sends'];

    if (!in_array($clientFilter, $allowedClientFilters, true)) {
      $clientFilter = 'all';
    }

    $filteredClientQuotaRows = array_values(array_filter($clientQuotaRows, static function (array $row_client) use ($clientSearch, $clientFilter, $formatSearchValue): bool {
      $quotaOverview = (array) ($row_client['quota_overview'] ?? []);
      $clientEvents = (array) ($quotaOverview['events'] ?? []);
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

      return true;
    }));

    usort($filteredClientQuotaRows, static function (array $left, array $right): int {
      $leftOverview = (array) ($left['quota_overview'] ?? []);
      $rightOverview = (array) ($right['quota_overview'] ?? []);

      $remainingDiff = (int) ($leftOverview['remaining_quota'] ?? 0) <=> (int) ($rightOverview['remaining_quota'] ?? 0);
      if ($remainingDiff !== 0) {
        return $remainingDiff;
      }

      return (int) ($rightOverview['sent_count'] ?? 0) <=> (int) ($leftOverview['sent_count'] ?? 0);
    });

    $visibleClientCount = count($filteredClientQuotaRows);
    $clientsWithoutEventCount = 0;
    $clientsLowCreditCount = 0;

    foreach ($filteredClientQuotaRows as $clientQuotaRow) {
      $quotaOverview = (array) ($clientQuotaRow['quota_overview'] ?? []);
      if ((int) ($quotaOverview['event_count'] ?? 0) === 0) {
        $clientsWithoutEventCount++;
      }
      if ((int) ($quotaOverview['remaining_quota'] ?? 0) <= 50) {
        $clientsLowCreditCount++;
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
                                        <a href="index.php?page=mb_conf_list">
										<span class="text-fade fs-12">Clients</span>
										<h2 class="text-dark hover-primary m-0 fw-600"><?php echo $total_ccli; ?></h2>
                                        </a>
									</div>
								</div>
							</div>
						</div>  
            <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
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
        <div class="card rounded-4 clients-admin-card">
            <div class="box-header d-flex b-0 justify-content-between align-items-center flex-wrap" style="gap:16px;">
                <div>
                  <h4 class="box-title mb-0">Gestion des clients</h4>
                  <p class="mb-0" style="margin-top:6px;color:#64748b;font-size:14px;">Recherchez un client, suivez ses quotas WhatsApp et gerez ses evenements plus rapidement.</p>
                </div>
                <div class="clients-admin-meta">
                  <span class="clients-admin-pill" id="clientsVisibleCounter">Visibles : <?php echo (int) $visibleClientCount; ?></span>
                  <span class="clients-admin-pill is-warning">Credits faibles : <?php echo (int) $clientsLowCreditCount; ?></span>
                  <span class="clients-admin-pill is-neutral">Sans evenement : <?php echo (int) $clientsWithoutEventCount; ?></span>
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
	  