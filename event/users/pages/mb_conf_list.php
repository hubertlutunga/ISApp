<?php
$confirmationMailFeedback = null;

if (!function_exists('mbConfirmPreviewValue')) {
	function mbConfirmPreviewValue(string $value, string $fallback = 'Non defini'): string
	{
		$value = trim($value);

		return $value !== '' ? $value : $fallback;
	}
}

if (!function_exists('mbConfirmEventSummary')) {
	function mbConfirmEventSummary(array $event): string
	{
		$eventType = (string) ($event['type_event'] ?? '');

		if ($eventType === '1') {
			$firstName = trim((string) ($event['prenom_epoux'] ?? ''));
			$secondName = trim((string) ($event['prenom_epouse'] ?? ''));
			if ((string) ($event['ordrepri'] ?? '') !== 'm') {
				$firstName = trim((string) ($event['prenom_epouse'] ?? ''));
				$secondName = trim((string) ($event['prenom_epoux'] ?? ''));
			}

			$weddingType = trim((string) ($event['type_mar'] ?? ''));
			$peopleLabel = trim($firstName . ' & ' . $secondName, ' &');
			if ($weddingType === '' && $peopleLabel === '') {
				return 'Evenement';
			}

			return trim('Mariage ' . $weddingType . ' de ' . $peopleLabel);
		}

		if ($eventType === '2') {
			$name = trim((string) ($event['nomfetard'] ?? ''));

			return $name !== '' ? trim('Anniversaire de ' . $name) : 'Evenement';
		}

		if ($eventType === '3') {
			$name = trim((string) ($event['nomfetard'] ?? ''));

			return $name !== '' ? trim('Conference ' . $name) : 'Evenement';
		}

		$name = trim((string) ($event['nomfetard'] ?? ''));

		return $name !== '' ? $name : 'Evenement';
	}
}

$eventPreviewSource = is_array($dataevent ?? null) ? $dataevent : [];
if ($eventPreviewSource === [] && !empty($codevent)) {
	$eventPreviewStmt = $pdo->prepare('SELECT * FROM events WHERE cod_event = :cod_event LIMIT 1');
	$eventPreviewStmt->execute([':cod_event' => (int) $codevent]);
	$eventPreviewSource = $eventPreviewStmt->fetch(PDO::FETCH_ASSOC) ?: [];
	$eventPreviewStmt->closeCursor();
}

$confirmationPreviewEvent = [
	'type_event' => (string) ($eventPreviewSource['type_event'] ?? ($type_event ?? '')),
	'prenom_epoux' => (string) ($eventPreviewSource['prenom_epoux'] ?? ($prenom_epoux ?? '')),
	'prenom_epouse' => (string) ($eventPreviewSource['prenom_epouse'] ?? ($prenom_epouse ?? '')),
	'ordrepri' => (string) ($eventPreviewSource['ordrepri'] ?? ($ordrepri ?? '')),
	'type_mar' => (string) ($eventPreviewSource['type_mar'] ?? ($type_mar ?? '')),
	'nomfetard' => (string) ($eventPreviewSource['nomfetard'] ?? ($nomfetard ?? '')),
];

$previewEventSummary = mbConfirmPreviewValue(mbConfirmEventSummary($confirmationPreviewEvent), 'Evenement');
$previewDateSource = (string) ($eventPreviewSource['date_event'] ?? ($date_event ?? ''));
$previewEventDate = $previewDateSource !== '' && strtotime($previewDateSource) !== false
	? date('d/m/Y a H:i', strtotime($previewDateSource))
	: 'Date non definie';
$previewEventLieu = mbConfirmPreviewValue((string) ($eventPreviewSource['lieu'] ?? ($lieu ?? '')));
$previewEventAdresse = mbConfirmPreviewValue((string) ($eventPreviewSource['adresse'] ?? ($adresse ?? '')));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_confirmation_mail'])) {
	$confirmationId = (int) ($_POST['confirmation_id'] ?? 0);
	$confirmationMailFeedback = GuestConfirmationMailService::sendForConfirmation(
		$pdo,
		$mail,
		$isAppConfig,
		(int) $codevent,
		$confirmationId,
		isset($datasession['cod_user']) ? (int) $datasession['cod_user'] : null
	);
}
?>

<div class="wrapper"> 
	<?php include('header.php');?>

	<div class="content-wrapper">
		<div class="container-full">
			<div class="row salut">
				<?php
				$heure = date('H');

				if ($heure < 12) {
					$salut = 'Bonjour';
				} elseif ($heure > 11 && $heure < 15) {
					$salut = 'Bon Après-midi';
				} else {
					$salut = 'Bonsoir';
				}
				?>

				<p style="text-align:center;">
					<?php echo $salut; ?> <b><?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, 'UTF-8'); ?></b>!
				</p>
			</div>

			<?php
			$guestStats = EventWorkspaceService::getGuestStats($pdo, (string) $codevent, $date_event ?: null);
			$confirmationStats = ConfirmationService::countSummary($pdo, (int) $codevent);
			$row_citi = (int) ($guestStats['total_inv'] ?? 0);
			$total_invconf = (int) ($confirmationStats['total'] ?? 0);
			$total_invconfoui = (int) ($confirmationStats['oui'] ?? 0);
			$total_invconfnon = (int) ($confirmationStats['non'] ?? 0);
			$total_invconfpt = (int) ($confirmationStats['plustard'] ?? 0);
			$total_nonreagi = $row_citi - $total_invconf;
			$audienceLabels = EventWorkspaceService::audienceLabels((string) ($type_event ?? ''));

			if (!isset($_GET['reponse'])) {
				$h4 = '<span class="text-info">Toutes les réponses</span>';
			} elseif ($_GET['reponse'] == 'oui') {
				$h4 = '<span class="text-success">Présences confirmées</span>';
			} elseif ($_GET['reponse'] == 'non') {
				$h4 = '<span class="text-danger">Absences confirmées</span>';
			} elseif ($_GET['reponse'] == 'plustard') {
				$h4 = '<span class="text-warning">Réponses en attente</span>';
			} else {
				$h4 = 'Réponses';
			}
			?>

			<style>
				.mb-confirm-alert{ margin:0 0 20px; padding:14px 18px; border-radius:18px; font-weight:700; }
				.mb-confirm-alert.is-success{ background:#ecfdf5; border:1px solid #bbf7d0; color:#166534; }
				.mb-confirm-alert.is-error{ background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
				.mb-confirm-page{ padding:6px 0 34px; }
				.mb-confirm-hero{ padding:30px; border-radius:30px; background:linear-gradient(135deg,#0f172a 0%,#13203a 56%,#0ea5e9 100%); color:#fff; box-shadow:0 24px 50px rgba(15,23,42,.16); margin-bottom:24px; }
				.mb-confirm-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
				.mb-confirm-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
				.mb-confirm-copy{ margin:0; max-width:760px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
				.mb-confirm-summary{ display:flex; gap:12px; flex-wrap:wrap; margin-top:20px; }
				.mb-confirm-pill{ display:inline-flex; align-items:center; gap:10px; padding:12px 16px; border-radius:18px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.14); font-weight:700; }
				.mb-confirm-pill strong{ font-size:18px; font-weight:800; color:#fff; }
				.mb-confirm-grid{ row-gap:16px; margin-bottom:18px; }
				.mb-confirm-stat-card{ display:flex; align-items:center; gap:14px; min-height:96px; padding:18px; border-radius:24px; text-decoration:none; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 20px 42px rgba(15,23,42,.08); border:1px solid #eef2f7; transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
				.mb-confirm-stat-card:hover{ transform:translateY(-2px); box-shadow:0 24px 46px rgba(15,23,42,.12); }
				.mb-confirm-stat-icon{ display:inline-flex; align-items:center; justify-content:center; width:58px; height:58px; border-radius:20px; color:#fff; font-size:22px; flex:0 0 58px; }
				.mb-confirm-stat-copy small{ display:block; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
				.mb-confirm-stat-copy strong{ display:block; margin-top:6px; font-size:28px; line-height:1; font-weight:800; color:#0f172a; }
				.mb-confirm-stat-card.is-info .mb-confirm-stat-icon{ background:linear-gradient(135deg,#06b6d4 0%,#0284c7 100%); }
				.mb-confirm-stat-card.is-success .mb-confirm-stat-icon{ background:linear-gradient(135deg,#22c55e 0%,#15803d 100%); }
				.mb-confirm-stat-card.is-danger .mb-confirm-stat-icon{ background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%); }
				.mb-confirm-stat-card.is-warning .mb-confirm-stat-icon{ background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%); }
				.mb-confirm-stat-card.is-primary .mb-confirm-stat-icon{ background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); }
				.mb-confirm-card{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.08); }
				.mb-confirm-header{ padding:24px 26px 8px; }
				.mb-confirm-subtitle{ margin:8px 0 0; color:#64748b; font-size:14px; }
				.mb-confirm-body{ padding:0 26px 26px; }
				.mb-confirm-toolbar{ display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; flex-wrap:wrap; }
				.mb-confirm-search-wrap{ position:relative; width:100%; max-width:420px; }
				.mb-confirm-search-icon{ position:absolute; top:50%; left:16px; transform:translateY(-50%); color:#94a3b8; font-size:18px; pointer-events:none; }
				.mb-confirm-search{ min-height:50px; padding-left:46px; border-radius:16px; border:1px solid #dbeafe; background:#f8fbff; box-shadow:none; }
				.mb-confirm-table{ width:100%; margin:0; table-layout:fixed; }
				.mb-confirm-row td{ padding:18px 0 !important; border-color:#eef2f7 !important; vertical-align:top; }
				.mb-confirm-main-cell{ padding-right:18px !important; }
				.mb-confirm-action-cell{ width:1%; white-space:nowrap; vertical-align:middle !important; }
				.mb-confirm-name{ display:block; font-size:17px; font-weight:800; color:#0f172a; text-decoration:none; }
				.mb-confirm-state{ display:inline-flex; align-items:center; margin-top:10px; padding:7px 11px; border-radius:999px; font-size:12px; font-weight:800; }
				.mb-confirm-state.is-yes{ background:#ecfdf5; color:#15803d; border:1px solid #bbf7d0; }
				.mb-confirm-state.is-no{ background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
				.mb-confirm-meals{ display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
				.mb-confirm-meal{ display:inline-flex; align-items:center; padding:7px 11px; border-radius:999px; background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; font-size:12px; font-weight:700; }
				.mb-confirm-note{ margin:12px 0 0; max-width:540px; color:#475569; line-height:1.7; font-size:14px; }
				.mb-confirm-meta{ margin-top:10px; color:#94a3b8; font-size:12px; display:block; }
				.mb-confirm-actions{ display:flex; align-items:center; justify-content:flex-end; gap:10px; }
				.mb-confirm-mail-form{ margin:0; }
				.mb-confirm-action-toggle{ display:inline-flex; align-items:center; justify-content:center; min-width:44px; min-height:36px; padding:0 12px; border-radius:999px; border:1px solid #fbbf24; background:#fff7ed; color:#b45309; }
				.mb-confirm-action-toggle:hover{ background:#ffedd5; color:#92400e; }
				.mb-confirm-action-menu{ border:0; border-radius:16px; box-shadow:0 20px 40px rgba(15,23,42,.16); padding:8px; min-width:220px; }
				.mb-confirm-action-menu .dropdown-item{ display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:12px; font-weight:700; color:#475569; }
				.mb-confirm-action-menu .dropdown-item:hover{ background:#f8fafc; color:#0f172a; }
				.mb-confirm-mail-muted{ font-size:12px; font-weight:700; color:#94a3b8; }
				.mb-confirm-mail-status{ display:inline-flex; align-items:center; gap:8px; margin-top:12px; padding:7px 11px; border-radius:999px; font-size:12px; font-weight:800; }
				.mb-confirm-mail-status.is-sent{ background:#ecfeff; color:#0f766e; border:1px solid #a5f3fc; }
				.mb-confirm-mail-status.is-pending{ background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
				.mb-confirm-empty{ padding:28px 0 !important; color:#64748b; text-align:center; font-style:italic; }
				@media only screen and (max-width: 769px) {
					.mb-confirm-page{ padding:0 0 28px; }
					.mb-confirm-hero{ padding:22px 20px; border-radius:24px; }
					.mb-confirm-title{ font-size:28px; }
					.mb-confirm-toolbar{ align-items:stretch; }
					.mb-confirm-search-wrap{ max-width:none; }
					.mb-confirm-header,
					.mb-confirm-body{ padding-left:18px; padding-right:18px; }
					.mb-confirm-row td{ display:block; width:100%; padding:14px 0 !important; }
					.mb-confirm-main-cell{ padding-right:0 !important; }
					.mb-confirm-action-cell{ width:100%; white-space:normal; vertical-align:top !important; }
					.mb-confirm-actions{ justify-content:flex-start; }
				}
			</style>

			<section class="content">
				<div class="mb-confirm-page">
					<?php if ($confirmationMailFeedback !== null) { ?>
					<div class="mb-confirm-alert <?php echo !empty($confirmationMailFeedback['success']) ? 'is-success' : 'is-error'; ?>">
						<?php echo htmlspecialchars((string) ($confirmationMailFeedback['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
					</div>
					<?php } ?>
					<div class="mb-confirm-hero">
						<span class="mb-confirm-kicker"><i class="mdi mdi-message-reply-text-outline"></i> Confirmations</span>
						<h1 class="mb-confirm-title"><?php echo htmlspecialchars($audienceLabels['confirm_title'], ENT_QUOTES, 'UTF-8'); ?></h1>
						<p class="mb-confirm-copy"><?php echo htmlspecialchars($audienceLabels['confirm_copy'], ENT_QUOTES, 'UTF-8'); ?></p>
						<div class="mb-confirm-summary">
							<span class="mb-confirm-pill"><i class="mdi mdi-account-group-outline"></i> <?php echo htmlspecialchars($audienceLabels['confirm_summary'], ENT_QUOTES, 'UTF-8'); ?> <strong><?php echo $row_citi; ?></strong></span>
							<span class="mb-confirm-pill"><i class="mdi mdi-email-check-outline"></i> Réponses <strong><?php echo $total_invconf; ?></strong></span>
							<span class="mb-confirm-pill"><i class="mdi mdi-account-alert-outline"></i> Sans réponse <strong><?php echo $total_nonreagi; ?></strong></span>
						</div>
					</div>
					<div class="row mb-confirm-grid">
						<?php include('statereponse.php')?>
					</div>

					<div class="row" id='mesinv'>
						<div class="col-xxl-12 col-xl-12 col-lg-12">
							<div class="card rounded-4 mb-confirm-card">
								<div class="box-header d-flex b-0 justify-content-between align-items-center mb-confirm-header">
									<div>
										<h4 class="box-title"><?php echo $h4; ?></h4>
										<p class="mb-confirm-subtitle"><?php echo htmlspecialchars($audienceLabels['confirm_subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
									</div>
								</div>

								<div class="card-body pt-0 mb-confirm-body">
									<div class="mb-confirm-toolbar">
										<div class="mb-confirm-search-wrap">
											<i class="mdi mdi-magnify mb-confirm-search-icon"></i>
											<input type="text" id="confirmationSearchInput" class="form-control mb-confirm-search" placeholder="Rechercher un invite, un email, un telephone ou un repas">
										</div>
									</div>
									<div class="table-responsiveX">
										<table class="tables mb-0 mb-confirm-table">
											<tbody id="confirmationList">
												<?php
												$presenceFilter = isset($_GET['reponse']) ? (string) $_GET['reponse'] : null;
												$confirmations = ConfirmationService::listByEvent($pdo, (int) $codevent, $presenceFilter);

												if (!empty($confirmations)) {
													foreach ($confirmations as $row_conf) {
														$presence = $row_conf['presence'] === 'oui' ? "Présence confirmée" : 'Absence confirmée';
														$presenceClass = $row_conf['presence'] === 'oui' ? 'is-yes' : 'is-no';
														$note = $row_conf['note'] ? nl2br(htmlspecialchars($row_conf['note'])) : '';
														$email = trim((string) ($row_conf['email'] ?? ''));
														$hasEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
														$phone = isset($row_conf['phone']) && $row_conf['phone'] !== '' ? 'Téléphone : ' . htmlspecialchars((string) $row_conf['phone']) : '';
														$mailSendCount = (int) ($row_conf['mail_send_count'] ?? 0);
														$searchHaystack = implode(' ', array_filter([
															(string) ($row_conf['noms'] ?? ''),
															$email,
															(string) ($row_conf['phone'] ?? ''),
															implode(' ', $row_conf['meal_names'] ?? []),
															(string) ($row_conf['presence'] ?? ''),
														]));
														?>
														<tr id="conf-<?php echo (int) ($row_conf['cod_conf'] ?? 0); ?>" class="mb-confirm-row confirmation-item" data-search="<?php echo htmlspecialchars(mb_strtolower($searchHaystack, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>">
															<td class="pt-0 px-0 b-0 mb-confirm-main-cell">
																<a class="mb-confirm-name" href="#"><?php echo htmlspecialchars(ucfirst($row_conf['noms'])); ?></a>
																<span class="mb-confirm-state <?php echo $presenceClass; ?>"><?php echo $presence; ?></span>
																<?php if (!empty($row_conf['meal_names'])) { ?>
																<div class="mb-confirm-meals">
																<?php foreach ($row_conf['meal_names'] as $mealName) { ?>
																	<span class="mb-confirm-meal"><?php echo htmlspecialchars($mealName); ?></span>
																<?php } ?>
																</div>
																<?php } ?>
																<?php if ($note !== '') { ?><p class="mb-confirm-note"><?php echo $note; ?></p><?php } ?>
																<span class="mb-confirm-meta"><?php echo $phone; ?><?php echo $phone !== '' ? ' • ' : ''; ?><?php echo 'Réponse envoyée le ' . date('d/m/Y', strtotime($row_conf['date_enreg'])); ?></span>
																<?php if ($mailSendCount > 0) { ?>
																<span class="mb-confirm-mail-status <?php echo $mailSendCount > 0 ? 'is-sent' : 'is-pending'; ?>">
																	<i class="mdi mdi-email-check-outline"></i>
																	<span>Mail envoyé</span>
																</span>
																<?php } ?>
															</td>
															<td class="text-end b-0 pt-0 px-0 mb-confirm-action-cell">
																<div class="mb-confirm-actions">
																<div class="list-icons d-inline-flex">
																			<div class="list-icons-item dropdown">
																				<a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle mb-confirm-action-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:18px;"></i></a>
																				<div class="dropdown-menu dropdown-menu-end mb-confirm-action-menu">
																				<?php if ($hasEmail) { ?>
																					<a class="dropdown-item js-confirm-mail-trigger" href="#" data-recipient="<?php echo htmlspecialchars((string) ucfirst($row_conf['noms']), ENT_QUOTES, 'UTF-8'); ?>" data-email="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" data-event-summary="<?php echo htmlspecialchars($previewEventSummary, ENT_QUOTES, 'UTF-8'); ?>" data-event-date="<?php echo htmlspecialchars($previewEventDate, ENT_QUOTES, 'UTF-8'); ?>" data-event-lieu="<?php echo htmlspecialchars($previewEventLieu, ENT_QUOTES, 'UTF-8'); ?>" data-event-adresse="<?php echo htmlspecialchars($previewEventAdresse, ENT_QUOTES, 'UTF-8'); ?>" data-form-id="confirm-mail-<?php echo (int) ($row_conf['cod_conf'] ?? 0); ?>">
																						<i class="mdi mdi-email-fast-outline"></i> Envoyer le mail
																					</a>
																				<?php } else { ?>
																				<span class="dropdown-item" style="color:#94a3b8;cursor:not-allowed;"><i class="mdi mdi-email-off-outline"></i> Aucune adresse email</span>
																				<?php } ?>
																				<a class="dropdown-item" href="#" style="color:red;" onclick="confirmSuppConfirmation(event, '<?php echo (int) ($row_conf['cod_conf'] ?? 0); ?>', '<?php echo htmlspecialchars((string) $codevent, ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(ucfirst((string) ($row_conf['noms'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>')"><i class="fa fa-remove"></i> Supprimer la reponse</a>
																			</div>
																			</div>
																		</div>
																	<form method="post" id="confirm-mail-<?php echo (int) ($row_conf['cod_conf'] ?? 0); ?>" class="mb-confirm-mail-form" style="display:none;">
																			<input type="hidden" name="confirmation_id" value="<?php echo (int) ($row_conf['cod_conf'] ?? 0); ?>">
																			<input type="hidden" name="send_confirmation_mail" value="1">
																		</form>
																</div>
															</td>
														</tr>
														<?php
													}
												} else {
													echo '<tr class="mb-confirm-row"><td colspan="3" class="mb-confirm-empty">Aucune réponse trouvée pour ce filtre.</td></tr>';
												}
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>

						<div id="shareModal" class="modalinv" style="display: none;">
							<div class="modal-content">
								<form action="" method="post">
									<?php
									require_once __DIR__ . '/whatsapp_template_sender.php';

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
											echo '<script>
											Swal.fire({
												title: "Notification !",
												text: ' . json_encode($shareSuccessMessage) . ',
												icon: "success",
												confirmButtonText: "OK"
											}).then((result) => {
												if (result.isConfirmed) {
													window.location.href = "index.php?page=mb_accueil";
												}
											});
											</script>';
										}

										if ($shareErrorMessage !== null) {
											echo '<script>
											Swal.fire({
												title: "Échec de l’envoi",
												text: ' . json_encode($shareErrorMessage) . ',
												icon: "error",
												confirmButtonText: "OK"
											});
											</script>';
										}
									}
									?>
									<div class="form-group">
										<span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
										<h4 id="modalTitle">Envoyer l'invitation</h4> <br><br>
										<input type="text" required pattern="^\+\d{1,3}\d{9,}$" title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
										<input type="hidden" id="inviteName" name="inviteName" />
										<input type="hidden" id="inviteId" name="inviteId" />
										<input type="hidden" id="pdfLink" name="pdf_link" />
										<button class="btn btn-primary" type="submit" name="submitwhat" style="width:100%;">Envoyer l'invitation</button>
									</div>
									<p style="margin:12px 0 0;color:#475569;font-size:13px;">En validant cette action, l'invitation PDF sera envoyee sur WhatsApp au numero indique pour cet invite.</p>
									<div style="margin-top:12px;padding:12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-size:13px;line-height:1.6;">
										<strong style="display:block;margin-bottom:6px;color:#0f172a;">Exemple de message automatique</strong>
										Bonjour <span id="previewInviteName">votre invite</span>,<br>
										<br>
										Nous avons le plaisir de vous transmettre votre invitation a <?php echo htmlspecialchars(isapp_whatsapp_sender_preview_context($pdo, $codevent)['event_label'], ENT_QUOTES, 'UTF-8'); ?>.<br>
										<br>
										Nous vous remercions de bien vouloir confirmer votre presence.<br>
										<br>
										Cordialement,<br>
										<?php echo htmlspecialchars(isapp_whatsapp_sender_preview_context($pdo, $codevent)['signature'], ENT_QUOTES, 'UTF-8'); ?>.<br>
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
							function openModal(inviteName, inviteId) {
								document.getElementById('modalTitle').innerText = "Envoyer l'invitation a " + inviteName;
								document.getElementById('previewInviteName').innerText = inviteName;
								document.getElementById('shareModal').style.display = 'flex';
								const linkpdf = "../pages/invitation_elect.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
								document.getElementById('inviteName').value = inviteName;
								document.getElementById('inviteId').value = inviteId;
								document.getElementById('pdfLink').value = linkpdf;
							}

							function closeModal() {
								document.getElementById('shareModal').style.display = 'none';
							}

							async function confirmSuppConfirmation(e, confirmationId, codEvent, nom) {
								e.preventDefault();

								if (typeof Swal === 'undefined') {
									if (!window.confirm('Voulez-vous vraiment supprimer la reponse de ' + nom + ' ?')) {
										return;
									}
								} else {
									const result = await Swal.fire({
										title: 'Supprimer ?',
										html: 'Voulez-vous vraiment supprimer la reponse de <b>' + nom + '</b> ?',
										icon: 'warning',
										showCancelButton: true,
										confirmButtonText: 'Oui, supprimer',
										cancelButtonText: 'Annuler',
										reverseButtons: true
									});

									if (!result.isConfirmed) {
										return;
									}
								}

								try {
									const response = await fetch('pages/ajax_supprimer_confirmation.php', {
										method: 'POST',
										headers: { 'Content-Type': 'application/json' },
										body: JSON.stringify({ confirmation_id: confirmationId, cod: codEvent })
									});
									const data = await response.json();

									if (!response.ok || !data.success) {
										throw new Error(data.message || 'Suppression impossible.');
									}

									const row = document.getElementById('conf-' + confirmationId);
									if (row) {
										row.remove();
									}

									if (typeof Swal !== 'undefined') {
										Swal.fire({
											title: 'Supprime',
											text: nom + ' a ete retire de la liste des reponses.',
											icon: 'success',
											timer: 1800,
											showConfirmButton: false
										});
									}
								} catch (error) {
									if (typeof Swal !== 'undefined') {
										Swal.fire({
											title: 'Erreur',
											text: error.message,
											icon: 'error'
										});
									} else {
										window.alert(error.message);
									}
								}
							}

							document.querySelectorAll('.js-confirm-mail-trigger').forEach(function(trigger) {
								trigger.addEventListener('click', function(event) {
									event.preventDefault();

									const data = trigger.dataset;
									const form = document.getElementById(data.formId);
									if (!form) {
										return;
									}

									if (typeof Swal === 'undefined') {
										if (window.confirm('Envoyer le mail a ' + data.recipient + ' (' + data.email + ') ?')) {
											form.submit();
										}
										return;
									}

									Swal.fire({
										title: 'Confirmer l envoi du mail',
										text: 'Envoyer le mail a ' + data.recipient + ' (' + data.email + ') ?',
										icon: 'question',
										showCancelButton: true,
										confirmButtonText: 'Envoyer maintenant',
										cancelButtonText: 'Annuler',
										focusCancel: true,
										confirmButtonColor: '#0f766e',
										cancelButtonColor: '#94a3b8'
									}).then(function(result) {
										if (result.isConfirmed) {
											form.submit();
										}
									});
								});
							});

							const confirmationSearchInput = document.getElementById('confirmationSearchInput');
							if (confirmationSearchInput) {
								confirmationSearchInput.addEventListener('input', function() {
									const term = (confirmationSearchInput.value || '').toLowerCase().trim();
									document.querySelectorAll('.confirmation-item').forEach(function(row) {
										const haystack = row.getAttribute('data-search') || '';
										row.style.display = term === '' || haystack.indexOf(term) !== -1 ? '' : 'none';
									});
								});
							}
						</script>
</div>















 






























  
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
	
	
		

                
				<?php include ('chatsupport.php')?>

	
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
	  