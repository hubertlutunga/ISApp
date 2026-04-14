<?php
  
                                if ($datasession['type_user'] == '3') {
                                    $linkall = "index.php?page=crea_accueil";
                                    $linkrea = "index.php?page=admin_filcom&type=realise";
                                    $linknp = "#";
                                    $linkatt = "index.php?page=admin_filcom&type=enattente";
                                } else { 
                                    $linkall = "index.php?page=admin_accueil";
                                    $linkrea = "index.php?page=admin_filcom&type=realise";
                                    $linknp = "index.php?page=admin_filcom&type=npaye";
                                    $linkatt = "index.php?page=admin_filcom&type=enattente"; 
                                }

// --- Statistiques du mois en cours ---
$mois = date('m');
$annee = date('Y');

// Réalisées ce mois
$stmtMois = $pdo->prepare("SELECT COUNT(*) FROM events WHERE crea = '2' AND MONTH(date_enreg) = ? AND YEAR(date_enreg) = ?");
$stmtMois->execute([$mois, $annee]);
$nbRealiseMois = (int)$stmtMois->fetchColumn();

// Evénements créés ce mois
$stmtEvtMois = $pdo->prepare("SELECT COUNT(*) FROM events WHERE MONTH(date_enreg) = ? AND YEAR(date_enreg) = ?");
$stmtEvtMois->execute([$mois, $annee]);
$nbEvtMois = (int)$stmtEvtMois->fetchColumn();

// Non payés ce mois
$stmtNpMois = $pdo->prepare("SELECT COUNT(*) FROM events WHERE fact IS NULL AND MONTH(date_enreg) = ? AND YEAR(date_enreg) = ?");
$stmtNpMois->execute([$mois, $annee]);
$nbNpMois = (int)$stmtNpMois->fetchColumn();

// En attente ce mois
$stmtAttMois = $pdo->prepare("SELECT COUNT(*) FROM events WHERE fact = 'oui' AND (crea IS NULL OR crea = '4') AND MONTH(date_enreg) = ? AND YEAR(date_enreg) = ?");
$stmtAttMois->execute([$mois, $annee]);
$nbAttMois = (int)$stmtAttMois->fetchColumn();

// Réalisations par agent pour le mois en cours
$stmtAgents = $pdo->prepare("SELECT u.noms AS agent, COUNT(*) AS total FROM events e JOIN is_users u ON u.cod_user = e.cod_user WHERE e.crea = '2' AND MONTH(e.date_enreg) = ? AND YEAR(e.date_enreg) = ? GROUP BY u.noms ORDER BY total DESC");
$stmtAgents->execute([$mois, $annee]);
$agentsStats = $stmtAgents->fetchAll(PDO::FETCH_ASSOC);
?>
  
				<div class="box box-body">
					<div class="row">
						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
                  <a href="<?php echo $linkall;?>">

							<div class="box-body rounded-0 p-0 pb-lg-0 pb-sm-15 pb-xs-15 be-1 fill-icon">
								<div class="d-flex align-items-center">

									<div class="w-70 h-70 me-15 bg-primary-light rounded-circle text-center p-10">
										<div class="w-50 h-50 bg-primary rounded-circle">
											<i class="fas fa-calendar fs-24 l-h-50"></i>
										</div>
									</div>
									<div class="d-flex flex-column">
										<span class="text-fade fs-12">Evénements</span>
										<h2 class="text-dark hover-primary m-0 fw-600"><?php echo $datanbevent; ?></h2>
									</div>
								</div>
							</div>

                  </a>

						</div>
						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">

                  <a href="<?php echo $linkrea;?>">
							<div class="box-body rounded-0 p-0 pb-lg-0 pb-sm-15 pb-xs-15 be-1 fill-icon">
								<div class="d-flex align-items-center">


									<div class="w-70 h-70 me-15 bg-info-light rounded-circle text-center p-10">
										<div class="w-50 h-50 bg-info rounded-circle">
										  <i class="fas fa-check-circle fs-24 l-h-50"></i>
										</div>		
									</div>
									<div class="d-flex flex-column">
										<span class="text-fade fs-12">Réalisées</span>
										<h2 class="text-dark hover-primary m-0 fw-600"><?php echo $datarealise; ?></h2>
									</div>

								</div>
							</div>
                  </a>

						</div>
						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
                  <a href="<?php echo $linknp;?>">

							<div class="box-body rounded-0 p-0 pb-lg-0 pb-xs-15 be-1 fill-icon">
								<div class="d-flex align-items-center">

									<div class="w-70 h-70 me-15 bg-danger-light rounded-circle text-center p-10">
										<div class="w-50 h-50 bg-danger rounded-circle">
										<i class="fas fa-folder fs-24 l-h-50"></i>	
										</div>		


									</div>
									<div class="d-flex flex-column">
										<span class="text-fade fs-12">Non payés</span>
										<h2 class="text-dark hover-primary m-0 fw-600"><?php echo $dataincomple; ?></h2>
									</div>
								</div>
							</div>
                  </a>
						</div>
						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">

                  <a href="<?php echo $linkatt;?>">
							<div class="box-body rounded-0 p-0 fill-icon">
								<div class="d-flex align-items-center">

									<div class="w-70 h-70 me-15 bg-warning-light rounded-circle text-center p-10">
										<div class="w-50 h-50 bg-warning rounded-circle">
                                        <i class="fas fa-clock fs-24 l-h-50" title="En attente"></i>
										</div>	
									</div>
									<div class="d-flex flex-column"> 
										<span class="text-fade fs-12">En attentes</span>
										<h2 class="text-dark hover-primary m-0 fw-600"><?php echo $dataattente; ?></h2>
									</div>


								</div>
							</div>
                  </a>
						</div>	
					</div>
				</div>

<!-- Réalisations par agent pour le mois en cours -->
<div class="row mt-3">
  <div class="col-12">
    <div class="box box-body" style="border-radius:18px; background:#f8fafc;">
      <h5 class="mb-2" style="color:#1f2a44;">Réalisations par agent (mois en cours)</h5>
      <div class="d-flex flex-wrap" style="gap:18px;">
        <?php foreach ($agentsStats as $agent): ?>
          <div style="min-width:120px; background:#fff; border-radius:12px; box-shadow:0 2px 8px #e0e7ef; padding:14px 18px; margin-bottom:8px; text-align:center;">
            <div style="font-size:22px; font-weight:700; color:#0f2242; line-height:1;">
              <?php echo htmlspecialchars($agent['total']); ?>
            </div>
            <div style="font-size:13px; color:#475569; margin-top:2px;">
              <?php echo htmlspecialchars($agent['agent']); ?>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($agentsStats)): ?>
          <span style="color:#64748b;">Aucune réalisation ce mois-ci.</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>



