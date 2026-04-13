<!-- BEGIN PRELOADER -->
<div id="preloader">
		<div class="loading-heart">
			<svg viewBox="0 0 512 512" width="100">
				<path d="M462.3 62.6C407.5 15.9 326 24.3 275.7 76.2L256 96.5l-19.7-20.3C186.1 24.3 104.5 15.9 49.7 62.6c-62.8 53.6-66.1 149.8-9.9 207.9l193.5 199.8c12.5 12.9 32.8 12.9 45.3 0l193.5-199.8c56.3-58.1 53-154.3-9.8-207.9z" />
			</svg>
			<div class="preloader-title">
            <?php echo $datamariage['prenom_epouse'];?><br>
				<small>&</small><br>
				<?php echo $datamariage['prenom_epoux'];?>
			</div>
		</div>
	</div>
	<!-- END PRELOADER -->


	<!-- BEGIN WRAPPER -->
	<div id="wrapper">
	
		<!-- BEGIN HEADER -->
		<header id="header">
			<div class="nav-section">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
                        <a style="font-family: 'Great Vibes', cursive;font-size:40px;margin-top:-2px;" href="index.php?page=accueil&cod=<?php echo $codmariage?>" class="nav-logo"><?php echo $datamariage['initiale_mar'];?></a>
							
							<!-- BEGIN MAIN MENU -->
							<nav class="navbar">
								
								<ul class="nav navbar-nav">
									<li><a href="#hero">accueil</a></li>
									  
									<li><a href="#rsvp-2">RSVP</a></li>
								</ul>
								
								<button id="nav-mobile-btn"><i class="fas fa-bars"></i></button><!-- Mobile menu button -->
							</nav>
							<!-- END MAIN MENU -->
							
						</div>
					</div>
				</div>
			</div>
		</header>
		<!-- END HEADER -->
        
        
        <style>
 

 /* #Sections with background image
 ================================================== */
   
 .bg-slideshowx {
   background-image: url('images/<?php echo $datamariage['photo']; ?>');
   background-size: cover;
   background-position: center;
   background-repeat: no-repeat;
   height: 100vh;
    
 }
  
  
         </style> 	
		 
		
		
	 
		
		
		<!-- BEGIN WEDDING INVITE SECTION -->
		<section id="the-wedding" class="parallax-background bg-color-overlay padding-divider-top">
			<div class="section-divider-top-1 section-divider-bg-color off-section"></div><!-- The class "section-divider-top-1" can also be applied to the tag <section>. In this case, it was added on a new <div> because the tag <section> have all pseudo elements (::after and ::before) in use. -->
			<div class="container">
				<div class="row">
					<div class="col-sm-12">
						<h2 class="section-title light">Liste des invités confirmés</h2>
					</div>
				</div>
				 
			</div>
		</section>
		<!-- END WEDDING INVITE SECTION -->
		 










        <table width="80%" style="margin-bottom:100px;" align="center">
                    <tr style="margin-bottom: 45px;">
                      <th style="width: 5%;color:#fff;">
                        #
                      </th>
                      <th style="width: 55%;color:#fff;">
                        NOMS
                      </th>
                      <th style="width: 45%;color:#fff;">
                        Presence
                      </th> 
                    </tr>
                    <?php 

                    $id = 0;
                              $reqinv="SELECT * FROM confirmation where cod_mar = '{$_GET['cod']}'  ORDER by noms ASC";
                              $inv=$pdo->query($reqinv);
                              while($row_inv=$inv->fetch()){
 
                                if ($row_inv['presence'] == "oui") {
                                    $presence = "J'y serai";
                                  }else{
                                    $presence = 'Désolé';
                                  }


                                  $id++;
                     ?>

                    <tr style="margin-bottom:15px;">
                      <td  align="left" style="border-bottom:1px solid #aaa;padding: 7px 0px 7px 0px;">
					  <a href="index.php?page=invdetail&cod=<?php echo $_GET['cod']?>&id=<?php echo $row_inv['cod_conf']?>"><?php echo $id; ?></a>
                        
                      <td  align="left" style="border-bottom:1px solid #aaa;padding: 7px 0px 7px 0px;">
					  <a href="index.php?page=invdetail&cod=<?php echo $_GET['cod']?>&id=<?php echo $row_inv['cod_conf']?>"><?php echo $row_inv['noms']; ?></a>
                        
                      </td>
                      <td  align="right" style="border-bottom:1px solid #aaa;padding: 7px 0px 7px 0px;">
                      	<a href="index.php?page=invdetail&cod=&cod=<?php echo $_GET['cod']?>&id=<?php echo $row_inv['cod_conf']?>"><?php echo $presence; ?></a> 
                      </td> 
                      
                    </tr>
                              <?php 
                                  
                              }
                              
                              ?> 
                  </table>

















		
		<!-- BEGIN FOOTER -->
		<footer id="footer">
			  
			<div class="copyright">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
							&copy; <?php echo date('Y')?> <a href="https://www.invitationspeciale.com">InvitationSpéciale</a>, All Rights Reserved.
						</div>
					</div>
				</div>
			</div>
		</footer>
		<!-- END FOOTER -->
		
	</div>
	<!-- END WRAPPER -->
	
	
	<!-- Google Maps API and Map Richmarker Library -->
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBHOXsTqoSDPQ5eC5TChvgOf3pAVGapYog"></script>
	<script src="js/richmarker.js"></script>
	
	<!-- Libs -->
	<script src="js/jquery-3.6.0.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	<script src="js/jquery-migrate-3.3.2.min.js"></script>
	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/jquery.placeholder.min.js"></script>
	<script src="js/ismobile.js"></script>
	<script src="js/retina.min.js"></script>
	<script src="js/waypoints.min.js"></script>
	<script src="js/waypoints-sticky.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/lightbox.min.js"></script>
    
    <!-- Nicescroll script to handle gallery section touch slide -->
	<script src="js/jquery.nicescroll.js"></script>
    
    <!-- Hero Background Slideshow Script -->
	<script src="js/jquery.zoomslider.js"></script>
	
	<!-- Template Scripts -->
	<script src="js/variables.js"></script>
	<script src="js/scripts.js"></script>
	 