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



