
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
				.mb-confirm-table{ margin:0; }
				.mb-confirm-row td{ padding:18px 0 !important; border-color:#eef2f7 !important; vertical-align:top; }
				.mb-confirm-name{ display:block; font-size:17px; font-weight:800; color:#0f172a; text-decoration:none; }
				.mb-confirm-state{ display:inline-flex; align-items:center; margin-top:10px; padding:7px 11px; border-radius:999px; font-size:12px; font-weight:800; }
				.mb-confirm-state.is-yes{ background:#ecfdf5; color:#15803d; border:1px solid #bbf7d0; }
				.mb-confirm-state.is-no{ background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
				.mb-confirm-meals{ display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
				.mb-confirm-meal{ display:inline-flex; align-items:center; padding:7px 11px; border-radius:999px; background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; font-size:12px; font-weight:700; }
				.mb-confirm-note{ margin:12px 0 0; max-width:540px; color:#475569; line-height:1.7; font-size:14px; }
				.mb-confirm-meta{ margin-top:10px; color:#94a3b8; font-size:12px; display:block; }
				.mb-confirm-empty{ padding:28px 0 !important; color:#64748b; text-align:center; font-style:italic; }
				@media only screen and (max-width: 769px) {
					.mb-confirm-page{ padding:0 0 28px; }
					.mb-confirm-hero{ padding:22px 20px; border-radius:24px; }
					.mb-confirm-title{ font-size:28px; }
					.mb-confirm-header,
					.mb-confirm-body{ padding-left:18px; padding-right:18px; }
					.mb-confirm-row td{ display:block; width:100%; padding:14px 0 !important; }
				}
			</style>

			<section class="content">
				<div class="mb-confirm-page">
					<div class="mb-confirm-hero">
						<span class="mb-confirm-kicker"><i class="mdi mdi-message-reply-text-outline"></i> Confirmations</span>
						<h1 class="mb-confirm-title">Suivez les réponses des invités en un coup d'œil</h1>
						<p class="mb-confirm-copy">Analysez les présences, identifiez les absences et gardez un historique clair des réponses envoyées pour votre événement.</p>
						<div class="mb-confirm-summary">
							<span class="mb-confirm-pill"><i class="mdi mdi-account-group-outline"></i> Invités <strong><?php echo $row_citi; ?></strong></span>
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
										<p class="mb-confirm-subtitle">Retrouvez les messages de confirmation, les repas choisis et les notes laissées par vos invités.</p>
									</div>
								</div>

								<div class="card-body pt-0 mb-confirm-body">
									<div class="table-responsiveX">
										<table class="tables mb-0 mb-confirm-table">
											<tbody>
												<?php
												$presenceFilter = isset($_GET['reponse']) ? (string) $_GET['reponse'] : null;
												$confirmations = ConfirmationService::listByEvent($pdo, (int) $codevent, $presenceFilter);

												if (!empty($confirmations)) {
													foreach ($confirmations as $row_conf) {
														$presence = $row_conf['presence'] === 'oui' ? "Présence confirmée" : 'Absence confirmée';
														$presenceClass = $row_conf['presence'] === 'oui' ? 'is-yes' : 'is-no';
														$note = $row_conf['note'] ? nl2br(htmlspecialchars($row_conf['note'])) : '';
														$phone = isset($row_conf['phone']) && $row_conf['phone'] !== '' ? 'Téléphone : ' . htmlspecialchars((string) $row_conf['phone']) : '';
														?>
														<tr class="mb-confirm-row">
															<td>
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
									require_once '../../twilio-php-main/src/Twilio/autoload.php';

									if (isset($_POST['submitwhat'])) {
										$stmtevz = $pdo->prepare("SELECT * FROM events WHERE cod_event = :cod_event");
										$stmtevz->execute(['cod_event' => $codevent]);
										$dataeventv = $stmtevz->fetch();

										if (!$dataeventv) {
											$codevent = '';
											$date_event = '';
											$type_event = '';
										} else {
											$codevent = $dataeventv['cod_event'];
											$date_event = $dataeventv['date_event'];
											$type_event = $dataeventv['type_event'];
										}

										if ($type_event == '1') {
											$fetard = (($dataeventv['prenom_epouse'] ?? '') . ' & ' . ($dataeventv['prenom_epoux'] ?? '')) ?: 'Inconnu';
											$typeevent = 'au Mariage ' . $dataeventv['type_mar'] . ' de ' . $fetard . ', le ' . date('d M Y à H:i', strtotime($dataeventv['date_event']));
										} elseif ($type_event == '2') {
											$fetard = $dataeventv['nomfetard'] ?? 'Inconnu';
											$typeevent = "à l'anniversaire de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataeventv['date_event']));
										} else {
											$fetard = $dataeventv['nomfetard'] ?? 'Inconnu';
											$typeevent = "à la conférence de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataeventv['date_event']));
										}

										$twilio = new \Twilio\Rest\Client('HXb38395e719833595e0c4d0be1691bddc', '2fc99f87d42f61c691c01df995fb8290');
										$inviteName = htmlspecialchars(ucfirst($_POST['inviteName']));
										$linkpdf = substr($linkpdf, 3);
										$newlinkpdf = 'https://invitationspeciale.com/event/' . $linkpdf;
										$msgnotif = "Cher(e) $inviteName,\n\nVous êtes invité $typeevent.\n\nPour plus d'infos, visitez :\n https://invitationspeciale.com/site/index.php?page=accueil&cod=$codevent\n\nCi-dessous votre invitation :\n $newlinkpdf";

										$twilio->messages->create(
											'whatsapp:' . $_POST['phoneinv'],
											[
												'from' => 'whatsapp:+13612649244',
												'body' => $msgnotif,
											]
										);

										echo '<script>
										Swal.fire({
											title: "Notification !",
											text: "Votre invitation a été envoyée avec succès.",
											icon: "success",
											confirmButtonText: "OK"
										}).then((result) => {
											if (result.isConfirmed) {
												window.location.href = "index.php?page=mb_accueil";
											}
										});
										</script>';
									}
									?>
									<div class="form-group">
										<span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
										<h4 id="modalTitle">Partager avec </h4> <br><br>
										<input type="text" required pattern="^\+\d{1,3}\d{9,}$" title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
										<input type="hidden" id="inviteName" name="inviteName" />
										<button class="btn btn-primary" type="submit" name="submitwhat" style="border-radius:0px 0px 7px 7px;width:100%;">Partager</button>
									</div>
									<br>
									<a href="#" id="downloadLink">Télécharger le PDF</a>
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
								document.getElementById('modalTitle').innerText = 'Partager avec ' + inviteName;
								document.getElementById('shareModal').style.display = 'flex';
								const linkpdf = "../pages/invitation_elect.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
								document.getElementById('downloadLink').setAttribute('href', linkpdf);
								document.getElementById('downloadLink').setAttribute('target', '_blank');
								document.getElementById('inviteName').value = inviteName;
							}

							function closeModal() {
								document.getElementById('shareModal').style.display = 'none';
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
	  