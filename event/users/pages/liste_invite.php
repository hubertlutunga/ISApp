   
   
	<?php 
	if (!$dataevent) {
		$linkallinv = '#';
	} else {
		$linkallinv = '../pages/liste_invites.php?event=' . $codevent;
	}
	$audienceLabels = EventWorkspaceService::audienceLabels((string) ($type_event ?? ''));
	$audienceWhatsAppLabel = ($audienceLabels['singular'] ?? 'invite') === 'participant'
		? 'WhatsApp du participant'
		: "WhatsApp de l'invite";
	$quotaClientUserId = WhatsAppQuotaService::resolveClientUserId((array) $dataevent, (int) ($datasession['cod_user'] ?? 0));
	$eventQuota = WhatsAppQuotaService::getEventQuota($pdo, (string) $codevent, $quotaClientUserId);
	?>

<style>
	.mb-invite-card{
		border:0;
		border-radius:28px;
		overflow:hidden;
		box-shadow:0 22px 48px rgba(15,23,42,.08);
		background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);
	}

	.mb-invite-header{
		padding:22px 24px 10px;
	}

	.mb-invite-title{
		margin:0;
		font-size:24px;
		font-weight:800;
		color:#0f172a;
	}

	.mb-invite-subtitle{
		margin:6px 0 0;
		font-size:14px;
		color:#64748b;
	}

	.mb-invite-quota{
		display:flex;
		flex-wrap:wrap;
		gap:10px;
		margin-top:14px;
	}

	.mb-invite-quota-chip{
		display:inline-flex;
		align-items:center;
		gap:8px;
		padding:10px 14px;
		border-radius:999px;
		background:#eff6ff;
		color:#1e3a8a;
		font-size:13px;
		font-weight:700;
	}

	.mb-invite-quota-chip.is-sent{
		background:#fff7ed;
		color:#9a3412;
	}

	.mb-invite-quota-chip.is-bonus{
		background:#ecfdf5;
		color:#166534;
	}

	.mb-invite-export{
		display:inline-flex;
		align-items:center;
		gap:8px;
		min-height:42px;
		padding:0 16px;
		border-radius:14px;
		border:1px solid #bfdbfe;
		background:#eff6ff;
		color:#1d4ed8 !important;
		font-weight:700;
	}

	.mb-invite-body{
		padding:0 24px 24px;
	}

	.mb-invite-toolbar{
		display:flex;
		align-items:center;
		justify-content:space-between;
		gap:16px;
		flex-wrap:wrap;
		margin-bottom:18px;
	}

	.mb-invite-stats{
		display:flex;
		gap:10px;
		flex-wrap:wrap;
	}

	.mb-invite-chip{
		display:inline-flex;
		align-items:center;
		gap:8px;
		padding:10px 14px;
		border-radius:999px;
		background:#eff6ff;
		border:1px solid #dbeafe;
		color:#0f172a;
		font-size:13px;
		font-weight:700;
	}

	.mb-invite-chip strong{
		font-size:14px;
		font-weight:800;
		color:#1d4ed8;
	}

	.mb-invite-search-wrap{
		position:relative;
		border:1px solid #e2e8f0;
		border-radius:16px;
		height:52px;
		background:#fff;
	}

	.mb-invite-search-icon{
		position:absolute;
		left:16px;
		top:50%;
		transform:translateY(-50%);
		font-size:16px;
		color:#94a3b8;
		pointer-events:none;
	}

	.mb-invite-filters{
		display:flex;
		align-items:center;
		gap:12px;
		flex-wrap:wrap;
		margin-bottom:14px;
	}

	.mb-invite-filter-control{
		display:flex;
		align-items:center;
		gap:10px;
		padding:10px 14px;
		border-radius:16px;
		border:1px solid #dbeafe;
		background:#f8fbff;
	}

	.mb-invite-filter-control label{
		margin:0;
		font-size:12px;
		font-weight:800;
		text-transform:uppercase;
		letter-spacing:.06em;
		color:#64748b;
	}

	.mb-invite-filter-select{
		min-width:220px;
		border:0 !important;
		background:transparent !important;
		box-shadow:none !important;
		padding:0;
		font-size:14px;
		font-weight:700;
		color:#0f172a;
	}

	.mb-invite-search{
		height:52px !important;
		border-radius:16px;
		border:1px solid #dbeafe !important;
		background:#f8fbff;
		box-shadow:none;
		margin-bottom:14px !important;
		padding:0 16px 0 46px;
		font-size:15px;
	}

	.mb-invite-search:focus{
		border-color:#93c5fd !important;
		box-shadow:0 0 0 4px rgba(59,130,246,.16);
	}

	.mb-invite-row td{
		padding:16px 0 !important;
		border-color:#eef2f7 !important;
		vertical-align:middle;
	}

	.mb-invite-row{
		transition:background-color .18s ease;
	}

	.mb-invite-row:hover{
		background:rgba(248,250,252,.7);
	}

	.mb-invite-table{
		margin:0;
	}

	.mb-invite-name-link{
		display:block;
		color:#0f172a;
		font-size:16px;
		font-weight:800;
		text-decoration:none;
	}

	.mb-invite-meta{
		display:block;
		margin-top:6px;
		color:#64748b;
		line-height:1.6;
		font-size:13px;
	}

	.mb-invite-tableline{
		display:inline-flex;
		align-items:center;
		gap:8px;
		margin-top:7px;
		font-size:13px;
		color:#475569;
	}

	.mb-invite-tableline i{
		color:#2563eb;
	}

	.mb-invite-inline-meta{
		display:block;
		margin-top:7px;
		font-size:13px;
		line-height:1.55;
		color:#64748b;
	}

	.mb-invite-inline-meta strong{
		font-weight:700;
		color:#334155;
	}

	.mb-invite-badges{
		display:flex;
		gap:8px;
		flex-wrap:wrap;
		margin-top:8px;
	}

	.mb-invite-hostline{
		display:inline-flex;
		align-items:center;
		gap:6px;
		margin-top:8px;
		font-size:12px;
		font-weight:500;
		color:#64748b;
	}

	.mb-invite-hostline strong{
		font-weight:600;
		color:#334155;
	}

	.mb-invite-badge{
		display:inline-flex;
		align-items:center;
		padding:7px 11px;
		border-radius:999px;
		font-size:12px;
		font-weight:700;
		line-height:1;
	}

	.mb-invite-badge.table{ background:#f8fafc; color:#334155; border:1px solid #e2e8f0; }
	.mb-invite-badge.type{ background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
	.mb-invite-badge.success{ background:#ecfdf5; color:#15803d; border:1px solid #bbf7d0; }
	.mb-invite-badge.muted{ background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
	.mb-invite-badge.host{ background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }

	.mb-invite-actions{
		display:inline-flex;
		align-items:center;
		justify-content:center;
		width:42px;
		height:42px;
		min-width:42px;
		flex-shrink:0;
		border-radius:14px;
		border:1px solid #fde68a;
		background:linear-gradient(180deg,#fff7e8 0%,#ffe7b8 100%);
		color:#8a5200 !important;
		box-shadow:0 10px 20px rgba(138,82,0,.14);
	}

	.mb-invite-empty{
		padding:28px 0 !important;
		color:#64748b;
		text-align:center;
	}

	.mb-invite-empty strong{
		display:block;
		font-size:16px;
		font-weight:800;
		color:#0f172a;
		margin-bottom:6px;
	}

	.modal{z-index: 8999 !important;}

	@media only screen and (max-width: 769px) {
		.mb-invite-card{
			overflow:visible;
		}

		.mb-invite-row{
			display:block;
			position:relative;
		}

		.mb-invite-toolbar{
			align-items:stretch;
		}

		.mb-invite-stats{
			width:100%;
		}

		.mb-invite-filters,
		.mb-invite-filter-control{
			width:100%;
		}

		.mb-invite-filter-control{
			align-items:flex-start;
			flex-direction:column;
		}

		.mb-invite-filter-select{
			width:100%;
			min-width:0;
		}

		.mb-invite-chip{
			width:100%;
			justify-content:space-between;
		}

		.mb-invite-header,
		.mb-invite-body{
			padding-left:18px;
			padding-right:18px;
		}

		.mb-invite-body{
			padding-bottom:96px;
		}

		.mb-invite-table{
			table-layout:fixed;
			width:100%;
		}

		.mb-invite-row{
			display:table-row;
		}

		.mb-invite-row td{
			display:table-cell;
			width:auto !important;
			padding:12px 0 !important;
			vertical-align:top;
		}

		.mb-invite-row td.pt-0.px-0.b-0{
			width:60% !important;
			min-width:0;
			padding-right:12px !important;
		}

		.mb-invite-row td.text-end.b-0.pt-0.px-0{
			position:static;
			width:40% !important;
			min-width:40%;
			padding:12px 0 12px 12px !important;
			text-align:right !important;
			vertical-align:top;
		}

		.mb-invite-row td.text-end .list-icons,
		.mb-invite-row td.text-end .list-icons-item{
			display:inline-flex !important;
			flex-wrap:nowrap;
			flex-shrink:0;
			justify-content:flex-end;
			width:100%;
		}

		.mb-invite-row td.text-end .dropdown,
		.mb-invite-row td.text-end .dropdown-toggle,
		.mb-invite-actions{
			width:42px;
			min-width:42px;
			height:42px;
		}
	}
</style>

<div class="row" id="mesinv">
	<div class="col-xxl-12 col-xl-12 col-lg-12">
		<div class="card rounded-4 mb-invite-card">
			<div class="box-header d-flex b-0 justify-content-between align-items-center mb-invite-header">
				<div>
					<h4 class="box-title mb-invite-title"><?php echo htmlspecialchars($audienceLabels['mine'], ENT_QUOTES, 'UTF-8'); ?></h4>
					<p class="mb-invite-subtitle"><?php echo htmlspecialchars($audienceLabels['manage_subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
					<div class="mb-invite-quota">
						<span class="mb-invite-quota-chip">Quota total : <?php echo (int) ($eventQuota['total_quota'] ?? 0); ?></span>
						<span class="mb-invite-quota-chip is-sent">Envoyes : <?php echo (int) ($eventQuota['sent_count'] ?? 0); ?></span>
						<span class="mb-invite-quota-chip">Restants : <?php echo (int) ($eventQuota['remaining_quota'] ?? 0); ?></span>
						<?php if ((int) ($eventQuota['bonus_quota'] ?? 0) > 0) { ?>
						<span class="mb-invite-quota-chip is-bonus">Bonus admin : +<?php echo (int) $eventQuota['bonus_quota']; ?></span>
						<?php } ?>
					</div>
				</div>
                <ul class="m-0" style="list-style: none;">
                    <li class="dropdown">
                        <a href="#" 
							   class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm mb-invite-export" 
                           data-bs-toggle="modal" 
                           data-bs-target="#modalPdfInvites">
                            <i class="fa fa-fw fa-arrow-down"></i> Exporter la liste en PDF
                        </a>
                    </li>
				</ul>
			</div>

			<!-- ================= MODALE PDF INVITES ================= -->
			<div class="modal fade" id="modalPdfInvites" tabindex="-1" aria-labelledby="modalPdfInvitesLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content rounded-3">
				<div class="modal-header">
					<h5 class="modal-title" id="modalPdfInvitesLabel"><?php echo htmlspecialchars($audienceLabels['pdf_title'], ENT_QUOTES, 'UTF-8'); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
				</div>
				<div class="modal-body text-center">
					<p>Choisissez le mode d’affichage :</p>
					<a href="<?php echo htmlspecialchars($pdfListByNameLink ?? ('../pages/liste_invites.php?event=' . urlencode((string) $codevent)), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-primary m-2">
						<?php echo htmlspecialchars($audienceLabels['pdf_by_name'], ENT_QUOTES, 'UTF-8'); ?>
					</a>
					<a href="<?php echo htmlspecialchars($pdfListByTableLink ?? ('../pages/liste_invites_partb.php?event=' . urlencode((string) $codevent)), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-success m-2">
						Classé par nom des Tables
					</a>
				</div> 
				</div>
			</div>
			</div>







   
			   <div class="card-body pt-0 mb-invite-body">
				   <?php 
							if (session_status() !== PHP_SESSION_ACTIVE) {
								session_start();
							}

								$currentUserId = (int) ($datasession['cod_user'] ?? 0);
							$filterSessionKey = 'invite_hote_filter';
							$eventFilterKey = (string) $codevent;

							if (!isset($_SESSION[$filterSessionKey]) || !is_array($_SESSION[$filterSessionKey])) {
								$_SESSION[$filterSessionKey] = [];
							}

								if (isset($_GET['page']) && $_GET['page'] === 'addinvite') {
									$tri = 'ORDER BY date_inv DESC';
								} else {
									$tri = 'ORDER BY nom ASC';
								}

							$hostStmt = $pdo->prepare("SELECT u.cod_user, u.noms, SUM(CASE WHEN i.sing = 'C' THEN 2 ELSE 1 END) AS invite_total FROM invite i LEFT JOIN is_users u ON u.cod_user = i.hote WHERE i.cod_mar = :codevent AND i.hote IS NOT NULL GROUP BY u.cod_user, u.noms ORDER BY u.noms ASC");
								$hostStmt->execute([':codevent' => $codevent]);
								$hostOptions = $hostStmt->fetchAll(PDO::FETCH_ASSOC);

							$extractFirstName = static function (?string $fullName): string {
								$normalized = trim((string) $fullName);

								if ($normalized === '') {
									return 'Hôte inconnu';
								}

								$parts = preg_split('/\s+/u', $normalized);

								return $parts && $parts[0] !== '' ? $parts[0] : $normalized;
							};

							$totalInviteCount = 0;
							$myInviteCount = 0;
							foreach ($hostOptions as $hostOption) {
								$hostInviteTotal = (int) ($hostOption['invite_total'] ?? 0);
								$totalInviteCount += $hostInviteTotal;
								if ((int) ($hostOption['cod_user'] ?? 0) === $currentUserId) {
									$myInviteCount = $hostInviteTotal;
								}
							}

							$storedHostFilter = isset($_SESSION[$filterSessionKey][$eventFilterKey]) ? (string) $_SESSION[$filterSessionKey][$eventFilterKey] : 'all';
							$selectedHostFilter = isset($_GET['hote_filter']) ? trim((string) $_GET['hote_filter']) : $storedHostFilter;
								$allowedHostIds = array_map(static function ($hostRow) {
									return (string) ($hostRow['cod_user'] ?? '');
								}, $hostOptions);

								$hostWhereClause = '';
								$queryParams = [':codevent' => $codevent];

								if ($selectedHostFilter === 'mine' && $currentUserId > 0) {
									$hostWhereClause = ' AND i.hote = :host_user';
									$queryParams[':host_user'] = $currentUserId;
								} elseif ($selectedHostFilter !== 'all' && in_array($selectedHostFilter, $allowedHostIds, true)) {
									$hostWhereClause = ' AND i.hote = :host_user';
									$queryParams[':host_user'] = (int) $selectedHostFilter;
								} else {
									$selectedHostFilter = 'all';
								}

								$_SESSION[$filterSessionKey][$eventFilterKey] = $selectedHostFilter;

								$confirmedNames = InviteStatusService::confirmedNamesIndex($pdo, (int) $codevent);
								$sentInviteIds = InviteStatusService::sentInviteIdsIndex($pdo, (string) $codevent);
								$pdfFilterQuery = http_build_query([
									'event' => $codevent,
									'hote_filter' => $selectedHostFilter,
								]);
								$pdfListByNameLink = '../pages/liste_invites.php?' . $pdfFilterQuery;
								$pdfListByTableLink = '../pages/liste_invites_partb.php?' . $pdfFilterQuery;
								$linkallinv = $pdfListByNameLink;

								$stmt = $pdo->prepare("SELECT i.*, u.noms AS hote_nom FROM invite i LEFT JOIN is_users u ON u.cod_user = i.hote WHERE i.cod_mar = :codevent$hostWhereClause $tri");
								$stmt->execute($queryParams);
								$inviteCount = (int) $stmt->rowCount();
								$confirmedCount = count($confirmedNames);
							?>

				 
 

				   <form method="get" class="mb-invite-filters">
					   <?php if (isset($_GET['page'])) { ?><input type="hidden" name="page" value="<?php echo htmlspecialchars((string) $_GET['page'], ENT_QUOTES, 'UTF-8'); ?>"><?php } ?>
					   <?php if (isset($_GET['codevent'])) { ?><input type="hidden" name="codevent" value="<?php echo htmlspecialchars((string) $_GET['codevent'], ENT_QUOTES, 'UTF-8'); ?>"><?php } ?>
					   <div class="mb-invite-filter-control" style="width:100%;">
						   <label for="hote_filter">Filtrer par administrateur</label>
						   <select id="hote_filter" name="hote_filter" class="form-select mb-invite-filter-select" onchange="this.form.submit()">
							   <option value="all" <?php echo $selectedHostFilter === 'all' ? 'selected' : ''; ?>><?php echo htmlspecialchars($audienceLabels['all'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo $totalInviteCount; ?>)</option>
							   <?php if ($currentUserId > 0) { ?>
							   <option value="mine" <?php echo $selectedHostFilter === 'mine' ? 'selected' : ''; ?>><?php echo htmlspecialchars($audienceLabels['mine'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo $myInviteCount; ?>)</option>
							   <?php } ?>
							   <?php foreach ($hostOptions as $hostOption) { ?>
							   <option value="<?php echo htmlspecialchars((string) $hostOption['cod_user'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedHostFilter === (string) $hostOption['cod_user'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($extractFirstName(isset($hostOption['noms']) ? (string) $hostOption['noms'] : 'Administrateur inconnu'), ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) ($hostOption['invite_total'] ?? 0); ?>)</option>
							   <?php } ?>
						   </select>
					   </div>
				   </form>

				   <div class="mb-invite-search-wrap">
					   <i class="mdi mdi-magnify mb-invite-search-icon"></i>
					   <input type="text" id="searchInput" class="form-control mb-invite-search" placeholder="<?php echo htmlspecialchars($audienceLabels['search'], ENT_QUOTES, 'UTF-8'); ?>">
				   </div>

				   <div class="table">
					   <table class="table mb-invite-table">
						   <tbody id="inviteList">

						  <?php 

   
							   if ($stmt->rowCount() > 0) {
								   while ($row_inv = $stmt->fetch(PDO::FETCH_ASSOC)) { 
									   $linkpdf = $dataevent['invit_religieux'] ? "../pages/invitation_speciale.php?cod=".$row_inv['id_inv']."&event=".$codevent : "#";
   
									   $seatName = EventTableService::findNameById($pdo, isset($row_inv['siege']) ? (int) $row_inv['siege'] : null);
									   $siege = $seatName ? ucfirst($seatName) : 'Non définie';
									   if (($row_inv['sing'] ?? '') === 'C') {
										   $sing = 'Couple';
										   $inviteAccord = (string) $audienceLabels['plural'];
									   } elseif (($row_inv['sing'] ?? '') === 'Mr') {
										   $sing = 'Monsieur';
										   $inviteAccord = (string) $audienceLabels['singular'];
									   } elseif (($row_inv['sing'] ?? '') === 'Mme') {
										   $sing = 'Madame';
										   $inviteAccord = (string) $audienceLabels['singular'];
									   } else {
										   $sing = 'Non défini';
										   $inviteAccord = (string) $audienceLabels['singular'];
									   }
									   $confirmed = isset($confirmedNames[InviteStatusService::normalizeName((string) $row_inv['nom'])]);
									$sentInvitation = isset($sentInviteIds[(int) ($row_inv['id_inv'] ?? 0)]);
									$reponseconf = InviteStatusService::invitationStatusLabel($confirmed, $sentInvitation, $row_inv['sing'] ?? null);
								$hasInvitationPreview = trim((string) ($dataevent['invit_religieux'] ?? '')) !== '';
									   $hoteNom = $extractFirstName(isset($row_inv['hote_nom']) ? (string) $row_inv['hote_nom'] : '');

							 
							 ?>
							    
											<tr id="inv-<?= (int)$row_inv['id_inv'] ?>" class="invite-item mb-invite-row">

											<td class="pt-0 px-0 b-0">
												<a class="invite-name mb-invite-name-link" href="index.php?page=modinv&idinv=<?php echo $row_inv['id_inv'];?>"><?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?></a>
												 <span class="mb-invite-inline-meta"><strong><?php echo htmlspecialchars($sing, ENT_QUOTES, 'UTF-8'); ?></strong>, <?php echo htmlspecialchars($inviteAccord, ENT_QUOTES, 'UTF-8'); ?> par <strong><?php echo htmlspecialchars($hoteNom, ENT_QUOTES, 'UTF-8'); ?></strong><br><?php echo $reponseconf; ?>, table : <?php echo htmlspecialchars($siege, ENT_QUOTES, 'UTF-8'); ?></span>
										   </td> 
   
											<td class="text-end b-0 pt-0 px-0"> 
											  



 
 
 
 
 
  
 
 
  
 
 
 
											   <div class="list-icons d-inline-flex">
                          <div class="list-icons-item dropdown">
                                          
  
											   <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle mb-invite-actions" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                                     <div class="dropdown-menu dropdown-menu-end">
  
 
                       
					   <a class="dropdown-item" href="#" onclick="<?php echo htmlspecialchars($hasInvitationPreview ? 'openModal(' . json_encode(ucfirst((string) $row_inv['nom']), JSON_UNESCAPED_UNICODE) . ', ' . json_encode((string) ((int) $row_inv['id_inv'])) . ')' : 'showInvitationDesignPending(event)', ENT_QUOTES, 'UTF-8'); ?>" style="color:<?php echo $hasInvitationPreview ? '#495057' : '#adb5bd'; ?>;">
					   <i class="fa fa-share"></i> Partager l'invitation</a> 

					   
		<a class="dropdown-item" <?php echo $hasInvitationPreview ? 'target="_blank"' : ''; ?>
			 href="<?php echo $hasInvitationPreview ? '../pages/invitation_speciale.php?cod=' . rawurlencode((string) $row_inv['id_inv']) . '&event=' . rawurlencode((string) $codevent) : '#'; ?>"
			 onclick="<?php echo $hasInvitationPreview ? '' : 'showInvitationDesignPending(event)'; ?>"
			 style="color:<?php echo $hasInvitationPreview ? '#495057' : '#adb5bd'; ?>;">
		<i class="fa fa-eye"></i> Aperçu de l'invitation
    </a>
 
 



											   <a class="dropdown-item" href="index.php?page=modinv&idinv=<?php echo $row_inv['id_inv'];?>"><i class="fa fa-edit"></i> <?php echo htmlspecialchars($audienceLabels['edit'], ENT_QUOTES, 'UTF-8'); ?></a>
   
 <a class="dropdown-item"
   href="#"
   style="color:red;"
   title="Suppression"
   onclick="confirmSuppInv(
     event,
     '<?= (int)$row_inv['id_inv'] ?>',
     '<?= htmlspecialchars($codevent, ENT_QUOTES) ?>',
     '<?= htmlspecialchars(ucfirst($row_inv['nom']), ENT_QUOTES) ?>'
   )">
					  <i class="fa fa-remove"></i> <?php echo htmlspecialchars($audienceLabels['delete'], ENT_QUOTES, 'UTF-8'); ?>
</a>  




      
       
                        </div>
                         </div>
                       </div>



										   </td>
									   </tr>
   
							   <?php 
   
								   }
   
							   } else {
								   echo '<tr class="mb-invite-row"><td colspan="3" class="mb-invite-empty"><strong>' . htmlspecialchars($audienceLabels['empty'], ENT_QUOTES, 'UTF-8') . '</strong>Ajoutez vos premiers ' . htmlspecialchars($audienceLabels['plural'], ENT_QUOTES, 'UTF-8') . ' pour commencer à suivre vos confirmations.</td></tr>';
							   }
   
							   ?>
   
						   </tbody>
					   </table>
				   </div>
			   </div>	
		   </div>
	   </div>





 



<script>
async function confirmSuppInv(e, idInv, codEvent, nom) {
  e.preventDefault();

  Swal.fire({
    title: "Supprimer ?",
    html: "Voulez-vous vraiment supprimer <b>" + nom + "</b> ?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Annuler",
    reverseButtons: true,
    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading(),
    preConfirm: async () => {
      try {
        const res = await fetch("pages/ajax_supprimer_invite.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ idinv: idInv, cod: codEvent })
        });
        if (!res.ok) throw new Error("Erreur serveur (" + res.status + ")");
        const data = await res.json();
        if (!data.success) throw new Error(data.message || "Suppression impossible.");
        return data; // passe à then(result)
      } catch (err) {
        Swal.showValidationMessage(err.message);
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      // Retirer la ligne sans recharger
      const row = document.getElementById("inv-" + idInv);
      if (row) row.remove();

      Swal.fire({
        title: "Supprimé",
        text: nom + " a été supprimé.",
        icon: "success",
        timer: 1800,
        showConfirmButton: false
      });
    }
  });
}
</script>


	   <!-- Fenêtre modale -->
	   <div id="shareModal" class="modalinv" style="display: none;">
		   <div class="modal-content">
			   <form action="" method="post">
   <?php 
   require_once '../../twilio-php-main/src/Twilio/autoload.php'; 
   require_once __DIR__ . '/whatsapp_template_sender.php';
	$sharePreviewContext = isapp_whatsapp_sender_preview_context($pdo, $codevent);

  if (isset($_POST['submitwhat'])) {
	   $shareErrorMessage = null;
	   $shareSuccessMessage = null;
	   try {
		   $result = isapp_whatsapp_send_template_invitation($pdo, [
			   'event_code' => $codevent,
			   'invite_id' => $_POST['inviteId'] ?? null,
			   'phone' => $_POST['phoneinv'] ?? '',
			   'invite_name' => $_POST['inviteName'] ?? 'Invite',
			   'pdf_link' => $_POST['pdf_link'] ?? '',
			   'success_redirect' => 'index.php?page=mb_accueil',
		   ]);
		   $shareSuccessMessage = $result['success_message'];
	   } catch (\Throwable $exception) {
		   $shareErrorMessage = (string) $exception->getMessage();
		   if ($shareErrorMessage === '') {
			   $shareErrorMessage = 'L’envoi de l’invitation WhatsApp a echoue.';
		   }
	   }

	   if ($shareSuccessMessage !== null) {
		   echo '<script>Swal.fire({title:"Notification !",text:' . json_encode($shareSuccessMessage) . ',icon:"success",confirmButtonText:"OK"}).then((result)=>{if(result.isConfirmed){window.location.href="index.php?page=mb_accueil";}});</script>';
	   }

	   if ($shareErrorMessage !== null) {
		   echo '<script>Swal.fire({title:"Échec de l’envoi",text:' . json_encode($shareErrorMessage) . ',icon:"error",confirmButtonText:"OK"});</script>';
	   }
  }
   ?>
			   <div class="form-group"> 
				   <span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
				   <h4 id="modalTitle">Envoyer l'invitation</h4> <br><br>
				   <label for="whatsappNumber" id="whatsappNumberLabel" style="display:block;margin-bottom:8px;color:#0f172a;font-size:13px;font-weight:700;"><?php echo htmlspecialchars($audienceWhatsAppLabel, ENT_QUOTES, 'UTF-8'); ?></label>
				   <input type="text" required pattern="^\+243\d{9}$" inputmode="tel" autocapitalize="off" autocorrect="off" spellcheck="false"
				   title="Veuillez entrer un numero WhatsApp valide au format +243XXXXXXXXX." id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="+243XXXXXXXXX" />
				   <button class="btn btn-light isapp-contact-picker" type="button" id="importContactButton" onclick="importPhoneContact()" style="width:100%;margin-top:10px;border:1px solid #cbd5e1;color:#0f172a;display:none;">Importer depuis mes contacts</button>
				   <p id="contactPickerHelp" style="display:none;margin:8px 0 0;color:#64748b;font-size:12px;">L'import du repertoire est disponible sur certains navigateurs mobiles compatibles.</p>
				   <input type="hidden" id="inviteName" name="inviteName" />
				   <input type="hidden" id="inviteId" name="inviteId" />
				   <input type="hidden" id="pdfLink" name="pdf_link" />
				   <button class="btn btn-primary" type="submit" name="submitwhat" style="width:100%">Envoyer l'invitation</button>
			   </div>
				   <p style="margin:12px 0 0;color:#475569;font-size:13px;">En validant cette action, l'invitation PDF sera envoyee sur WhatsApp au numero indique pour cet invite.</p>
				   <div style="margin-top:12px;padding:12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-size:13px;line-height:1.6;">
					   <strong style="display:block;margin-bottom:6px;color:#0f172a;">Exemple de message automatique</strong>
					   Bonjour <span id="previewInviteName">votre invite</span>,<br>
					   <br>
					   Nous avons le plaisir de vous transmettre votre invitation <?php echo htmlspecialchars($sharePreviewContext['event_label'], ENT_QUOTES, 'UTF-8'); ?> de <?php echo htmlspecialchars($sharePreviewContext['signature'], ENT_QUOTES, 'UTF-8'); ?>.<br>
					   <br>
					   Nous vous remercions de bien vouloir confirmer votre presence.<br>
					   <br>
					   Cordialement,<br>
					   Invitation Speciale,<br>
					   Merci.
				   </div>
			   </form>
		   </div>
	   </div>
   
	   <style>
		   .modalinv {
			   position: fixed;
			   top: 0;
			   left: 0;
			   width: 100%;
			   height: 100%;
			   background-color: rgba(0, 0, 0, 0.5);
			   display: none;
			   justify-content: center;
			   align-items: center;
			   overflow-y: auto;
			   padding: 24px 16px;
			   z-index: 3000;
		   }
   
		   .modal-content {
			   background-color: white;
			   padding: 20px;
			   border-radius: 5px;
			   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
			   position: relative;
			   max-height: calc(100vh - 48px);
			   overflow-y: auto;
		   }
   
		   .close {
			   position: absolute;
			   top: 10px;
			   right: 15px;
			   color: #aaa;
			   font-size: 24px;
		   }
   
		   .close:hover {
			   color: #000;
		   }
	   </style>
   
	   <script>
		   	const whatsappAudienceLabel = <?php echo json_encode($audienceWhatsAppLabel); ?>;

		   function showInvitationDesignPending(event) {
			   if (event) {
				   event.preventDefault();
			   }

			   Swal.fire({
				   title: "Invitation en cours de conception",
				   text: "Cette invitation n'est pas encore disponible.",
				   icon: "info",
				   confirmButtonText: "OK"
			   });
		   }

		   function sanitizeWhatsAppInput(value) {
			   const sanitizedValue = String(value || '').replace(/[^\d+]/g, '');
			   const hasLeadingPlus = sanitizedValue.startsWith('+');
			   const digitsOnly = sanitizedValue.replace(/\D+/g, '');

			   return (hasLeadingPlus ? '+' : '') + digitsOnly;
		   }

		   	function sanitizeImportedPhoneNumber(value) {
		   		if (!value) {
		   			return '';
		   		}

		   		let digits = String(value).replace(/\D+/g, '');

		   		if (digits.startsWith('00')) {
		   			digits = digits.slice(2);
		   		}

		   		if (digits.startsWith('243') && digits.length === 12) {
		   			return '+' + digits;
		   		}

		   		if (digits.startsWith('0') && digits.length === 10) {
		   			return '+243' + digits.slice(1);
		   		}

		   		if (digits.length === 9) {
		   			return '+243' + digits;
		   		}

		   		return value.trim();
		   	}

		   	async function importPhoneContact() {
		   		if (!('contacts' in navigator) || !('ContactsManager' in window)) {
		   			Swal.fire({
		   				title: 'Import non disponible',
		   				text: "Votre navigateur ne permet pas l'acces direct au repertoire.",
		   				icon: 'info',
		   				confirmButtonText: 'OK'
		   			});
		   			return;
		   		}

		   		try {
		   			const props = ['name', 'tel'];
		   			const options = { multiple: false };
		   			const contacts = await navigator.contacts.select(props, options);

		   			if (!Array.isArray(contacts) || contacts.length === 0 || !Array.isArray(contacts[0].tel) || contacts[0].tel.length === 0) {
		   				return;
		   			}

		   			const importedPhone = sanitizeImportedPhoneNumber(contacts[0].tel[0]);
		   			document.getElementById('whatsappNumber').value = importedPhone;
		   		} catch (error) {
		   			if (error && error.name === 'AbortError') {
		   				return;
		   			}

		   			Swal.fire({
		   				title: 'Import impossible',
		   				text: "Le numero n'a pas pu etre importe depuis le repertoire.",
		   				icon: 'error',
		   				confirmButtonText: 'OK'
		   			});
		   		}
		   	}

		   	function initContactPickerButton() {
		   		const importButton = document.getElementById('importContactButton');
		   		const importHelp = document.getElementById('contactPickerHelp');

		   		if (!importButton || !importHelp) {
		   			return;
		   		}

		   		if ('contacts' in navigator && 'ContactsManager' in window) {
		   			importButton.style.display = 'block';
		   			importHelp.style.display = 'block';
		   		}
		   	}

		   function openModal(inviteName, inviteId) {
			   document.getElementById('modalTitle').innerText = "Envoyer l'invitation a " + inviteName;
			   document.getElementById('previewInviteName').innerText = inviteName;
		   		document.getElementById('whatsappNumberLabel').innerText = whatsappAudienceLabel;
		   		document.getElementById('whatsappNumber').value = '';
			   document.getElementById('shareModal').style.display = 'flex';
			   const linkpdf = "../pages/invitation_speciale.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
			   document.getElementById('inviteName').value = inviteName;
			   document.getElementById('inviteId').value = inviteId;
			   document.getElementById('pdfLink').value = linkpdf;
		   }
   
		   function closeModal() {
			   document.getElementById('shareModal').style.display = 'none';
		   }

		   const whatsappNumberInput = document.getElementById('whatsappNumber');
		   if (whatsappNumberInput) {
			   whatsappNumberInput.addEventListener('keydown', function (event) {
				   const allowedControlKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];

				   if (event.ctrlKey || event.metaKey || allowedControlKeys.includes(event.key)) {
					   return;
				   }

				   const isDigit = /^\d$/.test(event.key);
				   const isLeadingPlus = event.key === '+' && this.selectionStart === 0 && !this.value.includes('+');

				   if (!isDigit && !isLeadingPlus) {
					   event.preventDefault();
				   }
			   });

			   whatsappNumberInput.addEventListener('input', function () {
				   this.value = sanitizeWhatsAppInput(this.value);
			   });

			   whatsappNumberInput.addEventListener('paste', function () {
				   const input = this;
				   requestAnimationFrame(function () {
					   input.value = sanitizeWhatsAppInput(input.value);
				   });
			   });
		   }

		   	initContactPickerButton();
	   </script>





	<!-- filtrer la recherche en temps reel -->

		<script>
		document.getElementById("searchInput").addEventListener("input", function () {
			let filter = this.value.toLowerCase();
			let rows = document.querySelectorAll("#inviteList .invite-item");

			rows.forEach(function (row) {
				let name = row.querySelector(".invite-name").textContent.toLowerCase();
				row.style.display = name.includes(filter) ? "" : "none";
			});
		});
		</script>


   </div>
   
   
   
   