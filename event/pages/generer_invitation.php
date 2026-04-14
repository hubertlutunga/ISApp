
<div class="container h-p100">
		<div class="row align-items-center justify-content-md-center h-p100">
			
			<div class="col-12">
				<div class="row justify-content-center g-0">
					<div class="col-lg-5 col-md-5 col-12">
						<div class="bg-white rounded10 shadow-lg">
							<div class="content-top-agile p-20 pb-0"> 
								<img src="../images/Logo_invitationSpeciale_SF.png">
								<p class="mb-0 text-fade">Générer l'invitation</p>
                                
                                



<?php 


$codmariage= '133';
  
if(isset($_POST['submit'])){
  

$invite = @$_POST['invite']; 

                            $reqinvc="SELECT count(*) as total_ex FROM invite where nom = '$invite' AND cod_mar = '$codmariage'";
                            $invc=$pdo->query($reqinvc);
                            $row_invc=$invc->fetch();

$hote = "Hezir";




  if(!$invite){
    echo "<div class='error' align=\"left\" style=\"color:red;font-weight:bold\">Remplissez le nom de l'invité</div>";
  }elseif($row_invc['total_ex'] > 0){

    echo "<div class='error' align=\"left\" style=\"color:red;font-weight:bold\">Cet invité existe déjà</div>";

  }else{

   

              $sql = 'INSERT INTO invite (cod_mar,nom,date_inv,hote)
              values  (:cod_mar,:nom,NOW(),:hote)';
            
              $q = $pdo->prepare($sql);
              $q->bindValue(':cod_mar',  $codmariage); 
              $q->bindValue(':nom',  $invite); 
              $q->bindValue(':hote',$hote);
              $q->execute();
              $q->closeCursor(); 

   

      }

  }

 ?>






							</div>
							<div class="p-40">
								<form action="" method="post">
									<div class="form-group">
										<div class="input-group mb-3">
											<span class="input-group-text bg-transparent"><i class="fas fa-user"></i></i></span>
											<input type="text" class="form-control ps-15 bg-transparent" name="invite" placeholder="Noms">
										</div>
									</div>

									  <div class="row"> 
										<!-- /.col -->
										<div class="col-12 text-center">
											<button type="submit" name="submit" class="btn btn-primary w-p100 mt-10">Générer</button>
										</div>
										<!-- /.col -->
									  </div>
								</form>				
								<div class="text-center">
                                    <hr>
                                    <h4>Liste des invités</h4>
								
                                
                                
                                
                                
                                    <table width="100%" style="margin-bottom:100px;">
                    <tr style="margin-bottom: 45px;">
                   
                    </tr>

                    <?php 

                              $reqinv="SELECT * FROM invite where cod_mar = '$codmariage' ORDER by id_inv DESC";
                              $inv=$pdo->query($reqinv);
                              while($row_inv=$inv->fetch()){


                     ?>

                    <tr style="margin-bottom:15px;">
                      <td  align="left" style="border-bottom:1px solid #aaa;padding: 5px 0px 5px 0px;height:50px;"><?php echo $row_inv['nom']; ?></td>
                       <td  align="right" style="border-bottom:1px solid #aaa;padding: 5px 0px 5px 0px;"><a href="pages/invitation.php?cod=<?php echo $row_inv['id_inv']?>" target="_blink" style="color:#0766d8;"><i class="fas fa-file"></i></a></td>
                    </tr>
                              <?php 
                                  
                              }
                              
                              ?> 
                    
                  </table>
                
                
                
                </div>
								
								<div class="text-center">
								  <p class="mt-20 text-fade">- Nos réseaux -</p>
								  <p class="gap-items-2 mb-0">
									  <a class="waves-effect waves-circle btn btn-social-icon btn-circle btn-facebook-light" href="#"><i class="fab fa-facebook"></i></a>
									  <a class="waves-effect waves-circle btn btn-social-icon btn-circle btn-twitter-light" href="#"><i class="fab fa-tiktok"></i></a>
									  <a class="waves-effect waves-circle btn btn-social-icon btn-circle btn-instagram-light" href="#"><i class="fab fa-instagram"></i></a>
									</p>	
								</div>
							</div>
						</div>	
					</div>
				</div>
			</div>			
		</div>
	</div>


	<!-- Vendor JS -->
	<script src="src/js/vendors.min.js"></script>
	<script src="src/js/pages/chat-popup.js"></script>
    <script src="assets/icons/feather-icons/feather.min.js"></script>	
	
	
</body>
</html>
