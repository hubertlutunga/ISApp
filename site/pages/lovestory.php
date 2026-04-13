<?php 


        $stmtlove = $pdo->prepare("SELECT * FROM lovestory WHERE cod_event = ? LIMIT 1");
        $stmtlove->execute([$codevent]);
        $datalove = $stmtlove->fetch(PDO::FETCH_ASSOC);
        
        
        ?>
 <section id="story" class=" bg-secondary spacer-one-top-lg">
            <!--Container-->
            <div class="container spacer-one-bottom-lg">
               <!--Row-->
               <div class="row justify-content-center">
                  <div class="col">
                     <div class=" text-center mb-5 pb-5">
                        <h1 class="display-4 mb-0">Love Story</h1>
                        <p class="w-md-40 mb-0 mx-auto text-dark-gray opacity-8 ">
                          <?php echo $datalove['text_lovestory']; ?> 

</p>
 
                     </div>
                  </div>
               </div>
               <!--End row-->
               <div class="row  justify-content-center">
                 
                  <div class="col-md-3 d-flex flex-column align-items-center">
                     <div class="mb-3 pb-3 px-4">
                        <div class="svg-mask-container">
                           <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 607 532" enable-background="new 0 0 607 532" xml:space="preserve">
                              <defs>
                                 <mask id="mask-small-1">
                                    <path fill="#FFFFFF" d="M303.2954,55.7655L303.2954,55.7655c-68.9887-68.3032-180.269-68.3601-249.3155,0.1402
                                       c-69.6423,69.0921-69.0838,181.9562,0.0139,251.5284l201.1888,202.5704c26.2076,26.3876,68.8445,26.5335,95.2321,0.3258
                                       l201.4838-200.1096c69.6179-69.1431,71.0768-183.7278,2.0303-253.4415C485.013-12.8027,372.7591-13.2244,303.2954,55.7655z" />
                                 </mask>
                              </defs>
                              <image mask="url(#mask-small-1)" width="100%" xlink:href="../couple/images/<?php echo $datalove['imgcoeur1']; ?>" />
                           </svg>
                        </div>
                     </div>
                  </div>
               </div>



               <div class="row">
                  <div class="col d-flex flex-column align-items-center">
                     <ol class="story mb-0">


                     <?php 
                      
$reqtls = "SELECT * FROM lovestory_etap WHERE cod_event = :codevent ORDER BY cod_ls ASC";
$reqtls = $pdo->prepare($reqtls);
$reqtls->execute(['codevent' => $codevent]);

// Vérifie si des résultats sont disponibles
if ($reqtls->rowCount() > 0) {
    while ($row_ls = $reqtls->fetch(PDO::FETCH_ASSOC)) {
        
                    ?>
                        <li>
                           <div class="story-icon bg-icon-primary">
                              <svg version="1.1" class="icon-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 107 93" enable-background="new 0 0 107 93" xml:space="preserve">
                                 <path fill="#E25D5D" d="M53.6825,9.4436L53.6825,9.4436C41.6532-2.4793,22.2392-2.5,10.1866,9.4439
                                    c-12.1565,12.0471-12.07,31.7375-0.0219,43.8819l35.08,35.3601c4.5697,4.6061,12.0081,4.6357,16.6142,0.0661l35.1704-34.8918
                                    c12.1523-12.056,12.4179-32.0464,0.3787-44.2154C85.3917-2.5013,65.8078-2.5857,53.6825,9.4436z" />
                              </svg>
                           </div>
                           <div>
                              <h5 class="mb-0"><?php echo htmlspecialchars($row_ls['event_etap']); ?></h5>
                              <span class="small text-primary"><?php echo date('F Y', strtotime($row_ls['date_etap'])); ?></span>
                           </div>
                        </li>
                         
                         
                        
                         
                        <?php 
                    }
                } else {
                    // Ne rien afficher si aucun résultat
                    echo '<tr><td colspan="1" style="text-align: left; padding: 5px 0;">Aucun étape trouvé</td></tr>';
                }
                ?>




                     </ol>
                  </div>
               </div>
               <div class="row  justify-content-center">
                  <div class="col-md-3 d-flex flex-column align-items-center">
                     <div class="mb-3 pb-3 mt-3 pt-3 px-4">
                        <div class="svg-mask-container">
                           <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 607 532" enable-background="new 0 0 607 532" xml:space="preserve">
                              <defs>
                                 <mask id="mask-small-2">
                                    <path fill="#FFFFFF" d="M303.2954,55.7655L303.2954,55.7655c-68.9887-68.3032-180.269-68.3601-249.3155,0.1402
                                       c-69.6423,69.0921-69.0838,181.9562,0.0139,251.5284l201.1888,202.5704c26.2076,26.3876,68.8445,26.5335,95.2321,0.3258
                                       l201.4838-200.1096c69.6179-69.1431,71.0768-183.7278,2.0303-253.4415C485.013-12.8027,372.7591-13.2244,303.2954,55.7655z" />
                                 </mask>
                              </defs>
                              <image mask="url(#mask-small-2)" width="100%" xlink:href="../couple/images/<?php echo $datalove['imgcoeur2']; ?>" />
                           </svg>
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-12 text-center">
                     <div class="">
                        <h5 class="mb-0"> Fin heureuse, nous nous marions</h5>
                        <span class="small text-dark-gray opacity-8">Comptez les jours...</span>
                     </div>
                  </div>
               </div>
            </div>
            <!--End container-->
            <div class="curved-decoration ">
               <svg width="100%" height="100%" class="bg-white-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
    viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M2560,0c0,0-219.6543,165.951-730.788,124.0771c-383.3156-31.4028-827.2138-96.9514-1244.7139-96.9514
      c-212.5106,0-439,3.5-584.4982,1.5844l0,139.9126h2560V0z"/>
</g>
</svg>
            </div>
         </section>

