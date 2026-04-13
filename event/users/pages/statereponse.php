
						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
							<a href="index.php?page=mb_conf_list" class="mb-confirm-stat-card is-info">
								<span class="mb-confirm-stat-icon"><i class="fas fa-comments"></i></span>
								<span class="mb-confirm-stat-copy">
									<small>Toutes les réponses</small>
									<strong><?php echo $total_invconf; ?></strong>
								</span>
							</a>
						</div>

						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
							<a href="index.php?page=mb_conf_list&reponse=oui" class="mb-confirm-stat-card is-success">
								<span class="mb-confirm-stat-icon"><i class="fas fa-check-circle"></i></span>
								<span class="mb-confirm-stat-copy">
									<small>Présences confirmées</small>
									<strong><?php echo $total_invconfoui; ?></strong>
								</span>
							</a>
						</div>

						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
							<a href="index.php?page=mb_conf_list&reponse=non" class="mb-confirm-stat-card is-danger">
								<span class="mb-confirm-stat-icon"><i class="fas fa-times-circle"></i></span>
								<span class="mb-confirm-stat-copy">
									<small>Absences confirmées</small>
									<strong><?php echo $total_invconfnon; ?></strong>
								</span>
							</a>
						</div>

						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
							<a href="index.php?page=mb_conf_list&reponse=plustard" class="mb-confirm-stat-card is-warning">
								<span class="mb-confirm-stat-icon"><i class="fas fa-clock"></i></span>
								<span class="mb-confirm-stat-copy">
									<small>Réponses en attente</small>
									<strong><?php echo $total_invconfpt; ?></strong>
								</span>
							</a>
						</div>

                        <?php if ($codevent === '287') {  ?>
						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
							<a href="index.php?page=mb_nonreaction" class="mb-confirm-stat-card is-primary">
								<span class="mb-confirm-stat-icon"><i class="fas fa-question"></i></span>
								<span class="mb-confirm-stat-copy">
									<small>Sans réaction</small>
									<strong><?php echo $total_nonreagi; ?></strong>
								</span>
							</a>
						</div>
                        <?php } ?>