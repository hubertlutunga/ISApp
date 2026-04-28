   
   
	<?php 
	if (!$dataevent) {
		$linkallinv = '#';
	} else {
		$linkallinv = '../pages/liste_invites.php?event=' . $codevent;
	}
	$audienceLabels = EventWorkspaceService::audienceLabels((string) ($type_event ?? ''));
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
									   $reponseconf = InviteStatusService::confirmationLabel($confirmed, $row_inv['sing'] ?? null);
									   $responseClass = $confirmed ? 'success' : 'muted';
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
  
 
                       
					   <a class="dropdown-item" href="#" onclick="openModal('<?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?>', '<?php echo $row_inv['id_inv']; ?>')" style="color:#aaa;">
					   <i class="fa fa-share"></i> <?php echo htmlspecialchars($audienceLabels['notify'], ENT_QUOTES, 'UTF-8'); ?></a> 

					   
<?php if ($dataevent['invit_religieux'] !== NULL): ?>
    <a class="dropdown-item" target="_blank"
       href="../pages/invitation_speciale.php?cod=<?= htmlspecialchars($row_inv['id_inv']); ?>&event=<?= htmlspecialchars($codevent); ?>">
        <i class="fa fa-download"></i> Partager l'invitation
    </a>
<?php endif; ?>
 
 



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
   use Twilio\Rest\Client;
   
   if (isset($_POST['submitwhat'])) {
	   // Préparation de la requête pour récupérer les événements
	   $inviteName = $_POST['inviteName'];
	   $stmtevz = $pdo->prepare("SELECT * FROM events WHERE cod_event = :cod_event");
	   $stmtevz->execute(['cod_event' => $codevent]);
	   $dataeventv = $stmtevz->fetch();
   
	   if (!$dataeventv) {
		   $codevent = '';
		   $date_event = '';
		   $type_event = '';
		   $display = 'none';
	   } else {  
		   $codevent = $dataeventv['cod_event'];
		   $date_event = $dataeventv['date_event'];
		   $type_event = $dataeventv['type_event'];
		   $display = 'block';
	   }
   
	   // Détermination du type d'événement
	   if ($type_event == "1") {
		   $fetard = (($dataeventv['prenom_epouse'] ?? '') . ' & ' . ($dataeventv['prenom_epoux'] ?? '')) ?: 'Inconnu';
		   $typeevent = 'au Mariage ' . $dataeventv['type_mar'] .' de '.$fetard. ', le ' . date('d M Y à H:i', strtotime($dataeventv['date_event']));
	   } elseif ($type_event == "2") {
		   $fetard = $dataeventv['nomfetard'] ?? 'Inconnu';
		   $typeevent = "à l'anniversaire de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataeventv['date_event']));
	   } elseif ($type_event == "3") {
		   $fetard = $dataeventv['nomfetard'] ?? 'Inconnu';
		   $typeevent = "à la conférence de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataeventv['date_event']));
	   }
   

	    

		// Vos identifiants Twilio
		$accountSid = 'AC5cbb94f85695ce16d97ce2ca2c3f7db0';
		$authToken = '2fc99f87d42f61c691c01df995fb8290'; 
		$twilionumber = 'whatsapp:+17167403177'; // Votre Messaging Service SID
		$recipientNumber = 'whatsapp:+243852266590'; // Numéro du destinataire

		// Créer une instance du client Twilio
		$client = new Client($accountSid, $authToken);

		// Envoi d'un message avec un modèle
		$messageTemplate = $client->messages->create(
			$recipientNumber,
			[
				'from' => $twilionumber,
				//'template' => 'tempinvitation', // Nom du modèle
				//'contentSid' => 'HX89f7cc8f64f0b71ade4a22cdffcb744d', // NEW
				'contentSid' => 'HX5e527d4a4e566b51065fcebade782c17',
				'templateData' => json_encode([
					'params' => [
						'1' => $inviteName, // Nom
						'2' => $typeevent, // Événement
						'3' => 'www.invitationspeciale.com/site/index.php?page=accueil&cod='.$codevent, // Lien
						'4' => 'www.invitationspeciale.com/site/index.php?page=accueil&cod='.$codevent // Invitation
					],
				]),
				'language' => 'fr', // Langue du modèle
			]
		);
   
	   echo '<script>
	   Swal.fire({
		   title: "Notification !",
		   text: "Notification envoyée avec succès.",
		   icon: "success",
		   confirmButtonText: "OK"
	   }).then((result) => {
		   if (result.isConfirmed) {
			   window.location.href = "index.php?page=mb_accueil"; // Rédirection vers la page de détails
		   }
	   });
	   </script>';
   }
   ?>
			   <div class="form-group"> 
				   <span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
				   <h4 id="modalTitle">Notifier </h4> <br><br>
				   <input type="text" required pattern="^\+\d{1,3}\d{9,}$" 
				   title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
				   <input type="hidden" id="inviteName" name="inviteName" />
				   <button class="btn btn-primary" type="submit" name="submitwhat" style="border-radius:0px 0px 7px 7px;width:100%;">Notifier</button>
			   </div>
			   <br>
			   <a href="#" id="downloadLink">Télécharger l'invitation</a>
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
			   display: flex;
			   justify-content: center;
			   align-items: center;
			   z-index: 3000;
		   }
   
		   .modal-content {
			   background-color: white;
			   padding: 20px;
			   border-radius: 5px;
			   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
			   position: relative;
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
		   function openModal(inviteName, inviteId) {
			   document.getElementById('modalTitle').innerText = 'Notifier ' + inviteName;
			   document.getElementById('shareModal').style.display = 'flex';
			   const linkpdf = "../pages/invitation_speciale.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
			   document.getElementById('downloadLink').setAttribute('href', linkpdf);
			   document.getElementById('downloadLink').setAttribute('target', "_blank");
			   document.getElementById('inviteName').value = inviteName; // Récupération du nom de l'invité
		   }
   
		   function closeModal() {
			   document.getElementById('shareModal').style.display = 'none';
		   }
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
   
   
   
   