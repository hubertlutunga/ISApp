
         <?php
         $showWeddingPhoto = isset($showWeddingPhoto) ? (bool) $showWeddingPhoto : true;
         $showWeddingMap = isset($showWeddingMap) ? (bool) $showWeddingMap : true;
         $showWeddingParty = isset($showWeddingParty) ? (bool) $showWeddingParty : true;
         $weddingMapValue = isset($wedText) ? trim($wedText('wedding_map_iframe', '')) : '';
         $renderWeddingMap = static function (string $mapValue) use ($wedE): string {
            if ($mapValue === '') {
               return '';
            }
            if (preg_match('/src=["\']([^"\']+)["\']/i', $mapValue, $matches)) {
               return '<iframe src="' . $wedE($matches[1]) . '" width="100%" height="260" style="border:0;border-radius:18px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
            }
            return '<a href="' . $wedE($mapValue) . '" target="_blank" rel="noopener" class="hover-arrow">Voir sur Maps <span class="fa fa-arrow-right"></span></a>';
         };
         ?>
         <section id="wedding" class="bg-secondary spacer-one-top-lg">
            <!--Container-->
            <div class="container spacer-one-bottom-lg">
               <!--Row-->
               <div class="row justify-content-center">
                  <div class="col">
                     <div class=" text-center mb-5 pb-5">
                        <h1 class="display-4 mb-0"><?php echo isset($wedText, $wedE) ? $wedE($wedText('wedding_title', 'Wedding Events')) : 'Wedding Events'; ?></h1>
                        <p class="w-md-40 mb-0 mx-auto text-dark-gray opacity-8 "><?php echo isset($wedText, $wedE) ? $wedE($wedText('wedding_subtitle', 'Cérémonies principales - Fête de mariage')) : 'Cérémonies principales - Fête de mariage'; ?></p>
                     </div>
                  </div>
               </div>
               <!--End row-->
               <!--Row-->
               <div class="row">
                  <?php if ($showWeddingPhoto) { ?>
                  <div class="col-md-6 col-lg-4 d-flex mb-4 mb-lg-0 display-state ">
                     <div class="card card-icon-2 card-body justify-content-between">
                        <img class="bg-image" src="../couple/images/<?php echo isset($wedImage, $wedE) ? $wedE($wedImage('wedding_bg', (string) ($dataevent['photostory'] ?? 'defaulwed_1.png'))) : htmlspecialchars((string) ($dataevent['photostory'] ?? 'defaulwed_1.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="">
                     </div>
                  </div>
                  <?php } ?>
                  <?php if ($showWeddingMap) { ?>
                  <div class="col-md-6 col-lg-4 d-flex mb-4 mb-lg-0">
                     <div class="card card-body justify-content-between">
                        <h5 class="mb-4">Lieu de l’événement</h5>
                        <?php if ($weddingMapValue !== '') { ?>
                        <div><?php echo $renderWeddingMap($weddingMapValue); ?></div>
                        <?php } else { ?>
                        <p class="text-align-center mb-0">Ajoutez un iframe Google Maps depuis l’administration pour afficher la carte ici.</p>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
                  <?php if ($showWeddingParty) { ?>
                  <div class="col-md-6 col-lg-4 d-flex ">
                     <div class="card card-body justify-content-between bg-primary text-light">
                        <div class="icon-round mb-3 mb-md-5  bg-icon-white">
                           <svg class="icon-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 460" enable-background="new 0 0 512 460;" xml:space="preserve">
                              <g>
                                 <g>
                                    <path fill="#FFFFFF" d="M240.8,110.7c-0.1-4.5-3.1-8.3-7.4-9.4L138,76.1c0,0,0,0,0,0L102,66.7c-4.3-1.1-8.8,0.7-11.1,4.4
                                       c-14.4,23.3-25.7,49.3-32.7,75.1c-11.7,43-9.7,79.9,5.7,106.7c9,15.5,22.1,26.9,38.3,33.4L65.6,421.3l-53-14
                                       c-5.3-1.4-10.8,1.8-12.2,7.1c-1.4,5.3,1.8,10.8,7.1,12.2c0,0,62.5,16.5,62.5,16.5c0,0,62.7,16.5,62.7,16.5
                                       c0.9,0.2,1.7,0.3,2.6,0.3c4.4,0,8.5-3,9.7-7.5c1.4-5.3-1.8-10.8-7.1-12.2l-52.9-13.9l36.8-135c3.6,0.5,7.2,0.8,10.8,0.8
                                       c44.6,0,81.4-37.5,98.5-100.3C238,166.1,241.4,138,240.8,110.7z M211.7,186.6c-14.6,53.6-44.2,85.5-79.2,85.5
                                       c-5.1,0-10.3-0.7-15.4-2c-15.4-4.1-27.8-13.5-35.7-27.2c-9.4-16.3-12.6-37.8-9.5-63.1c13.4,2.2,26,3,37.9,3
                                       c19.7,0,37.6-2.2,54.2-4.3c18.8-2.3,35.5-4.4,50.3-2.5C213.5,179.5,212.6,183.1,211.7,186.6z M218,156.3
                                       c-17.7-2.4-36.6-0.1-56.4,2.4c-25.3,3.1-53.7,6.6-86.2,1.4c0.7-2.9,1.4-5.7,2.2-8.7c5.9-21.6,15.1-43.4,26.6-63.5l15.9,4.2
                                       c-2.9,5.6-5.5,11.3-7.6,15.9c-2.2,5,0,11,5.1,13.2c1.3,0.6,2.7,0.9,4.1,0.9c3.8,0,7.5-2.2,9.1-5.9c3.2-7.3,6.4-13.7,9.2-18.8
                                       l80.9,21.3C220.8,131.2,219.8,143.9,218,156.3z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <path fill="#FFFFFF" d="M256,0c-5.5,0-10,4.5-10,10v26.1c0,5.5,4.5,10,10,10c5.5,0,10-4.5,10-10V10C266,4.5,261.5,0,256,0z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <path fill="#FFFFFF" d="M223.6,49l-13.2-22.6c-2.8-4.8-8.9-6.4-13.7-3.6c-4.8,2.8-6.4,8.9-3.6,13.7l13.2,22.6
                                       c1.9,3.2,5.2,4.9,8.6,4.9c1.7,0,3.5-0.4,5.1-1.4C224.8,59.9,226.4,53.8,223.6,49z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <path fill="#FFFFFF" d="M315.3,22.9c-4.8-2.8-10.9-1.2-13.7,3.6L288.4,49c-2.8,4.8-1.2,10.9,3.6,13.7c1.6,0.9,3.3,1.4,5,1.4
                                       c3.4,0,6.8-1.8,8.6-4.9l13.2-22.6C321.7,31.8,320.1,25.7,315.3,22.9z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <path fill="#FFFFFF" d="M408.6,120.7C408.6,120.7,408.6,120.7,408.6,120.7c-1.7-5.3-7.2-8.2-12.5-6.7c-5.3,1.6-8.3,7.2-6.7,12.5
                                       c0,0.1,0,0.2,0.1,0.2c1.4,4.3,5.3,7,9.5,7c1,0,2-0.1,3-0.5C407.3,131.6,410.2,126,408.6,120.7z" />
                                 </g>
                              </g>
                              <g opacity="0.3">
                                 <g>
                                    <circle fill="#FFFFFF" cx="110" cy="138" r="10" />
                                 </g>
                              </g>
                              <g>
                                 <g>
                                    <g>
                                       <path fill="#FFFFFF" d="M442,443.1C442,443.1,442,443.1,442,443.1l-62.2,16.4C385.7,458,442,443.1,442,443.1z" />
                                       <path fill="#FFFFFF" d="M379.8,459.5c-0.3,0.1-0.5,0.1-0.5,0.1L379.8,459.5z" />
                                       <path fill="#FFFFFF" d="M511.7,414.4c-1.4-5.3-6.9-8.5-12.2-7.1l-53,14l-36.8-135.1c16.2-6.5,29.4-17.9,38.3-33.4
                                          c15.5-26.8,17.5-63.7,5.8-106.7c-7-25.8-18.3-51.7-32.7-75.1c-2.3-3.7-6.8-5.5-11.1-4.4l-131.3,34.6c-4.3,1.1-7.3,5-7.4,9.4
                                          c-0.5,24.6,2.2,49.7,7.8,73.3c0.1,0.3,0.1,0.7,0.2,1c0.6,2.3,1.1,4.6,1.8,6.8c17.1,62.8,53.9,100.3,98.5,100.3c0,0,0,0,0,0
                                          c3.6,0,7.2-0.3,10.8-0.8l36.8,135l-52.9,13.9c-5.3,1.4-8.5,6.9-7.1,12.2c1.2,4.5,5.2,7.5,9.7,7.5c0.8,0,1.7-0.1,2.6-0.3
                                          l62.7-16.5c0,0,0,0,0,0l62.5-16.5C509.9,425.2,513.1,419.8,511.7,414.4z M291.2,118.7l116.6-30.8c11.6,20,20.8,41.8,26.6,63.5
                                          c1.7,6.3,3.1,12.4,4.2,18.3c-6.1-0.6-12.3-0.7-18.6-0.4c-1.1-7.3-2.7-15-4-20.9c-1.2-5.4-6.6-8.8-12-7.5c-5.4,1.2-8.8,6.6-7.5,12
                                          c1.5,6.7,2.7,12.8,3.5,18.1c-4.5,0.5-9.1,1.1-13.7,1.7c-26,3.3-55.2,7-89.1,1C293.3,155.8,291.3,137.1,291.2,118.7z M379.5,272.1
                                          C379.5,272.1,379.5,272.1,379.5,272.1c-33.2,0-61.6-28.8-76.8-77.4c11.1,1.5,21.8,2.2,31.9,2.2c19.7,0,37.6-2.3,54.2-4.4
                                          c19.6-2.5,36.9-4.7,52.2-2.3c1.2,20.8-2.3,38.7-10.4,52.7c-7.9,13.7-20.3,23.1-35.7,27.2C389.8,271.4,384.6,272.1,379.5,272.1z" />
                                    </g>
                                 </g>
                              </g>
                              <path opacity="0.3" fill="#FFFFFF" d="M211.7,186.6c-14.6,53.6-44.2,85.5-79.2,85.5c-5.1,0-10.3-0.7-15.4-2c-15.4-4.1-27.8-13.5-35.7-27.2
                                 c-9.4-16.3-12.6-37.8-9.5-63.1c13.4,2.2,26,3,37.9,3c19.7,0,37.6-2.2,54.2-4.3c18.8-2.3,35.5-4.4,50.3-2.5
                                 C213.5,179.5,212.6,183.1,211.7,186.6z" />
                              <path opacity="0.3" fill="#FFFFFF" d="M379.5,272.1C379.5,272.1,379.5,272.1,379.5,272.1c-33.2,0-61.6-28.8-76.8-77.4c11.1,1.5,21.8,2.2,31.9,2.2
                                 c19.7,0,37.6-2.3,54.2-4.4c19.6-2.5,36.9-4.7,52.2-2.3c1.2,20.8-2.3,38.7-10.4,52.7c-7.9,13.7-20.3,23.1-35.7,27.2
                                 C389.8,271.4,384.6,272.1,379.5,272.1" />
                           </svg>
                        </div>


                        <h5 class="mb-0 text-white"><?php echo isset($wedText, $wedE) ? $wedE($wedText('party_title', 'Soirée dansante')) : 'Soirée dansante'; ?></h5>
                        <h6 class="mb-5 text-white"><?php echo isset($wedText, $wedE) ? $wedE($wedText('party_time', date('H:i', strtotime($dataevent['date_event'])))) : date('H:i', strtotime($dataevent['date_event'])); ?></h6>
                        <p class="text-align-center"><?php echo isset($wedText, $wedE) ? nl2br($wedE($wedText('party_place', trim((string) $lieu . ' ' . (string) ($dataevent['adresse'] ?? ''))))) : $lieu . ' ' . $dataevent['adresse'];?>
                        </p>
                     </div>
                  </div>
                  <?php } ?>
               </div>
               <!--End row-->
               <!--End container-->
            </div>
            <div class="curved-decoration">
               <svg width="100%" height="100%" class="bg-secondary-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 2560 168.6227" enable-background="new 0 0 2560 168.6227" xml:space="preserve">
<g>
</g>
<g>
   <path d="M0,0c0,0,219.6543,165.951,730.788,124.0771c383.3156-31.4028,827.2139-96.9514,1244.7139-96.9514
      c212.5106,0,438.9999,3.5,584.4982,1.5844v139.9126H0V0z"></path>
</g>
</svg>
            </div>
         </section>