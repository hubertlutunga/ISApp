 
        
  <?php

    $bg = empty($dataevent['photo']) ? 'defaulwed_1.png' : $dataevent['photo'];
    $tableCode = isset($_GET['table']) && is_scalar($_GET['table']) ? (string) $_GET['table'] : '';
    $selectedMenuOption = isset($_POST['menu_option']) && is_scalar($_POST['menu_option']) ? (string) $_POST['menu_option'] : '';
    $selectedInvite = isset($_POST['invite']) && is_scalar($_POST['invite']) ? (string) $_POST['invite'] : '';

  ?>  
  <div class="aheto-titlebar aheto-titlebar--restaurant aheto-titlebar--height-500">
    <div class="aheto-titlebar__main   ">
      <img class="js-bg" alt="" src="../couple/images/<?php echo htmlspecialchars($bg, ENT_QUOTES, 'UTF-8'); ?>">
      <div class="aheto-titlebar__content w-1000">
        <div class="aheto-titlebar__text ">
          <p class="aheto-titlebar__subtitle t-white   t-medium t-center t-uppercase">Menu</p>
          <h1 class="aheto-titlebar__title  t-white t-semibold t-center  large-size"><?php echo htmlspecialchars($typeevent, ENT_QUOTES, 'UTF-8');?> </h1>
          <h1 class="aheto-titlebar__title  t-white t-center  large-size" style="font-family: 'Great Vibes', cursive;margin-top:15px;"><?php echo htmlspecialchars($fetard, ENT_QUOTES, 'UTF-8');?> </h1>
        </div>
      </div>
    </div>
  </div>




<form action="" method="post"> 
    
       	

 
  <div class="restaurant-menu-wrap rest-menu-salads padding-lg-90t padding-md-60t padding-xs-30t padding-lg-85b padding-md-20b padding-xs-10b">
     <div class="bg-text">Menu</div>
    <div class="container ">


  


<?php

if(isset($_POST['submit'])){

  $menu_option = trim((string) $selectedMenuOption); 
  $codevent = (string) ($codevent ?? ($_GET['cod'] ?? '')); 
  $table = trim((string) $tableCode);  
  $invite = trim((string) $selectedInvite);  
    
  if (!$table) {
    echo '<script>
    Swal.fire({
      title: "Table introuvable !",
      text: "Veuillez scanner à nouveau le QR code de votre table.",
      icon: "warning",
      confirmButtonText: "OK"
    });
    </script>';
  } elseif (!$menu_option) {
        echo '<script>
        Swal.fire({
      title: "Quel menu !",
      text: "Veuillez sélectionner un élément du menu.",
            icon: "warning",
            confirmButtonText: "OK"
        });
        </script>';
    } elseif (!$invite) {
        echo '<script>
        Swal.fire({
            title: "Votre Nom !",
            text: "Veuillez sélectionner votre nom.",
            icon: "warning",
            confirmButtonText: "OK"
        });
        </script>';
    }else{

                      $sql = 'INSERT INTO commandemenu (codmenu, codinv, codtable, codevent, date_enreg) 
                              VALUES (:codmenu, :codinv, :codtable, :codevent, NOW())';
                      $q = $pdo->prepare($sql);

                      $q->bindValue(':codmenu', $menu_option);
                      $q->bindValue(':codinv', $invite);
                      $q->bindValue(':codtable', $table);
                      $q->bindValue(':codevent', $codevent);


                      


              
                      // Exécutez la requête d'insertion
                        if ($q->execute()) {
                          $redirectUrl = 'index.php?page=accueil&cod=' . rawurlencode($codevent) . '&table=' . rawurlencode($table);
                          echo '<script>
                          Swal.fire({
                              title: "Commande !",
                              text: "Votre commande a été reçue avec succès.",
                              icon: "success",
                              confirmButtonText: "OK"
                          }).then((result) => {
                              if (result.isConfirmed) {
                                window.location.href = ' . json_encode($redirectUrl) . ';
                              }
                          });
                          </script>';
                      } else {
                          echo '<script>
                          Swal.fire({
                              title: "Erreur !",
                              text: "Une erreur est survenue lors de l\'enregistrement de votre commande.",
                              icon: "error",
                              confirmButtonText: "OK"
                          });
                          </script>';
                      }


              }

    }

 ?>



<style>

    .aht-pricing__line {
    border: 2px solid transparent;
    padding: 10px;
    border-radius: 5px;
    transition: border-color 0.3s;
    cursor: pointer;
}

  .aht-pricing__line.selected {
    border-color: #007bff; /* Couleur de la bordure lorsqu'il est sélectionné */
    background-color: #f0f8ff; /* Couleur de fond pour mettre en valeur */
}


</style>

      <div class="row">
        <div class="col-md-12 offset-md-1">
          <div class="aheto-heading t-center aheto-heading--restaurant">
            <h2 class="aheto-heading__title        f-40  t-medium " style="font-family: 'Great Vibes', cursive;font-size:70px">Menu</h2>
          </div>
        </div>
      </div>

      <div class="row margin-lg-80t margin-md-60t margin-xs-30t">
        <div class="col-md-12">
          <div class="aht-pricing aht-pricing--rest ">


            <div class="aht-pricing__item">

    <?php 

  $menuCategories = MenuCatalogService::listCategoryIdsByEvent($pdo, (int) $codevent);

  if (!empty($menuCategories)) {
    foreach ($menuCategories as $categoryId) {

  $nomcat = MenuCatalogService::findCategoryName($pdo, (string) $categoryId);
  $nomcat = $nomcat ?: 'Autres';
 



?>
              <h4 class="aht-pricing__title"><?php echo htmlspecialchars(strtoupper($nomcat), ENT_QUOTES, 'UTF-8');?></h4>
              <span class="aht-pricing__desc"></span>
              <ul class="aht-pricing__list"> 
<hr>

                  <!-- <div class="aht-pricing__special">
                    <div class="aht-pricing__special-text">Today Specialty</div>
                  </div>  -->


  <?php 

$menusByCategory = MenuCatalogService::listByEventAndCategory($pdo, (int) $codevent, (string) $categoryId);

if (!empty($menusByCategory)) {
    foreach ($menusByCategory as $row_menu2) {
        // Récupération du nom de la catégorie
     
$desc = $row_menu2['desc_menu'] ?? '';
$menuId = (string) ($row_menu2['cod_mev'] ?? '');
$menuInputId = 'menu_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $menuId);

if (!$desc) {
    $descmenu = 'Aucune description pour '.$row_menu2['nom'];
}else{
    $descmenu = $desc ;
}

                                        
?>
 
   
    <li class="aht-pricing__line" style="min-height:100px;"> 
      <div class="aht-pricing__price-ultraWrap">
        <div class="aht-pricing__price-wrap">
          <span class="aht-pricing__per" onclick="selectRadio(<?php echo htmlspecialchars(json_encode($menuId), ENT_QUOTES, 'UTF-8'); ?>)"><?php echo htmlspecialchars($row_menu2['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="aht-pricing__price">$ 0</span>
        </div>
        <div class="aht-pricing__composition" style="margin-top:5px;" onclick="selectRadio(<?php echo htmlspecialchars(json_encode($menuId), ENT_QUOTES, 'UTF-8'); ?>)"><?php echo htmlspecialchars($descmenu, ENT_QUOTES, 'UTF-8'); ?></div>
        <div>
          <input type="radio" name="menu_option" value="<?php echo htmlspecialchars($menuId, ENT_QUOTES, 'UTF-8'); ?>" 
            <?php echo ($selectedMenuOption == $menuId) ? 'checked' : ''; ?> 
            id="<?php echo htmlspecialchars($menuInputId, ENT_QUOTES, 'UTF-8'); ?>">
          <label for="<?php echo htmlspecialchars($menuInputId, ENT_QUOTES, 'UTF-8'); ?>"> Sélectionner</label>
        </div>
      </div>
    </li>
<hr> 
   
 
<?php 

    }

    } 

?>

              </ul>

<?php

    }

    } else {
      echo '<p class="t-center">Aucun élément du menu n’est disponible pour cet événement.</p>';
    }

?>
            </div>

 
          </div>
        </div>
      </div>
    </div>
  </div>
  



<script>
(function() {
  function markSelected(radio) {
    document.querySelectorAll('.aht-pricing__line').forEach((li) => {
      li.classList.remove('selected');
    });

    const parentLi = radio.closest('.aht-pricing__line');
    if (parentLi) {
      parentLi.classList.add('selected');
    }
  }

  window.selectRadio = function(value) {
    const radio = Array.from(document.querySelectorAll('input[name="menu_option"]')).find((item) => item.value === String(value));
    if (radio) {
      radio.checked = true;
      markSelected(radio);
    }
  };

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="menu_option"]').forEach((radio) => {
      radio.addEventListener('change', function() {
        markSelected(this);
      });

      if (radio.checked) {
        markSelected(radio);
      }
    });
  });
})();
</script>


		 <?php if ($tableCode === ''){

      $displayvue = 'display:none;';
      }else{
        $displayvue = 'display:block;';
      } 

     ?>

  <section class="rest-menu-form padding-lg-140t padding-md-0t" style="<?php echo $displayvue;?>">
    <img src="../img/restaurant/menu/menu_bg-04.png" class="w-100 js-bg" alt="single img">
    <div class="rest-reservation-container rest-reservation-container_bottom">
      <div class="rest-reservation-order">
        <div class="aheto-heading t-center aheto-heading--restaurant-contact">
          <h2 class="aheto-heading__title    f-style-italic    f-40  t-bold ">Merci pour votre commande</h2>
          <p class="aheto-heading__desc   ">Sélectionnez votre nom et l’élément du menu souhaité.</p>
        </div>
        <div class="aheto-single-img   ">
          <img src="../img/restaurant/pepper.jpg" class="  " alt="single img">
        </div>
      </div>
      <div class="aheto-heading t-center aheto-heading--restaurant-contact">
        <h2 class="aheto-heading__title    f-style-italic    f-40  t-bold ">Commander un menu</h2>
        <p class="aheto-heading__desc   ">Une sélection par commande</p>
      </div>

      <div class="form-rest-reservation-wrap margin-lg-60t">
        <div class="aheto-form aheto-form--default aheto-form--restaurant aheto-form--rest-reservation">
                                    
 
            <!-- <span class="col-12 col-md-6 col-lg-4 wpcf7-form-control-wrap input-icon input-icon-date">
              <input type="date" name="Date" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="Date" required>
            </span>
            <span class="col-12 col-md-6 col-lg-4 wpcf7-form-control-wrap input-icon input-icon-time">
              <input type="time" name="Time" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="Time" required>
            </span>
            <span class="col-12 col-md-6 col-lg-4 wpcf7-form-control-wrap">
              <input type="text" name="nom" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="Nom" required>
            </span> -->

<?php 


            $codtabele = $tableCode;
            $row_tab = null;
            if ($codtabele !== '') {
              $reqtab = "SELECT * FROM tableevent WHERE cod_tab = :cod_tab AND cod_event = :cod_event";
              $reqtab = $pdo->prepare($reqtab);
              $reqtab->execute([':cod_tab' => $codtabele, ':cod_event' => $codevent]);
              $row_tab = $reqtab->fetch(PDO::FETCH_ASSOC);
            }

            $nomtable = $row_tab ? $row_tab['nom_tab'] : 'Non définie';
                            
?>


            <div class="col-12 col-md-6 col-lg-4 wpcf7-form-control-wrap input-icon input-icon-th-large">
               <input type="text" disabled name="table" value="<?php echo htmlspecialchars('Table '.$nomtable, ENT_QUOTES, 'UTF-8');?>" size="40" class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-tel" aria-invalid="false" placeholder="Table" required>
            </div> 

            <div class="col-12 col-md-6 col-lg-4 wpcf7-form-control-wrap input-icon input-icon-persons"> 
            
              <select  class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-tel" name="invite">
                                            <option style="color:#eee;" value="">Votre Nom</option>
                                            <?php 
                                            
                              $invites = [];
                              if ($codtabele !== '') {
                                $reqinv = $pdo->prepare("SELECT id_inv, nom FROM invite WHERE cod_mar = :cod_mar AND siege = :siege ORDER BY nom ASC");
                                $reqinv->execute([':cod_mar' => $codevent, ':siege' => $codtabele]);
                                $invites = $reqinv->fetchAll(PDO::FETCH_ASSOC);
                              }
                                            foreach ($invites as $row_inv) {
                                            ?>
                                            <option value="<?php echo htmlspecialchars($row_inv['id_inv'], ENT_QUOTES, 'UTF-8')?>" <?php if($selectedInvite == $row_inv['id_inv']){echo "selected";} ?>><?php echo htmlspecialchars($row_inv['nom'], ENT_QUOTES, 'UTF-8')?></option>
                                            <?php } ?>  
              </select>
            
            </div>






            <div class="col-12 col-md-6 col-lg-4 wpcf7-form-control-wrap form-bth-holder" style="width:100%;">
              <button type="submit" name="submit" class="wpcf7-form-control wpcf7-submit rest-reserv-btn" style="width:100%;">Commander</button>
            </div>
          </div>
        </div>
      </div>

 </form>

    </div>
  </section>

 

          

  <footer class="aheto-footer aheto-footer-7" style="background-image: url('../img/restaurant/footer_bg.jpg')">
    <div class="aheto-footer-7__main">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="widget widget_aheto t-center">
              <div class="widget_aheto__logo">
                <img src="../event/images/Logo_invitationSpeciale_4.png" width="300px" alt="footer">
              </div>
            </div>
          </div> 
          <div class="col-md-12">
            <div class="aht-socials aht-socials--retreat t-center">
              <a class="aht-socials__link aht-btn--dark aht-btn--trans " href="#">
                <i class="aht-socials__icon icon ion-social-facebook"></i>
              </a>
              <a class="aht-socials__link aht-btn--dark aht-btn--trans " href="#">
                <i class="aht-socials__icon icon ion-social-tumblr"></i>
              </a>
              <a class="aht-socials__link aht-btn--dark aht-btn--trans " href="#">
                <i class="aht-socials__icon icon ion-social-twitter"></i>
              </a>
              <a class="aht-socials__link aht-btn--dark aht-btn--trans " href="#">
                <i class="aht-socials__icon icon ion-social-youtube"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="aheto-footer-7__bottom">
      <div class="container">
        <div class="row">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="aheto-footer-7__cr">


            <p class="aheto-footer-7__cr-text">&copy; <?php echo date('Y')?> Hubert Solutions All right reserved <br>
            Plateforme, branche de </span> <a  href="https://www.invitationspeciale.com">Invitation Spéciale</a><br> 
			      Sous : <a href="https://hubertlutunga.com">Hubert Lutunga</a> <br>
               <a href="https://wa.me/243810678785" target="_blinck">Nous contacter</a>
            </p>
 
            
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
  <div class="site-search" id="search-box">
    <button class="close-btn js-close-search"><i class="fa fa-times" aria-hidden="true"></i></button>
    <div class="form-container">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <form role="search" method="get" class="search-form" action="http://ahetopro/" autocomplete="off">
              <div class="input-group">
                <input type="search" value="" name="s" class="search-field" placeholder="Enter Keyword" required="">
              </div>
            </form>
            <p class="search-description">Input your search keywords and press Enter.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Magnific popup -->
  <script src="vendors/magnific/jquery.magnific-popup.min.js"></script>
  <!-- anm -->
  <script src="vendors/animation/anm.min.js"></script>
  <!-- Google maps -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyARwCmK-LlGIH8Mv1ac4VyceMYUgg9vStM&amp;#038;&language=en"></script>
  <script src="vendors/googlemap/google-maps.js?v=1"></script>
  <!-- FullCalendar -->
  <!-- Parallax -->
  <script src="vendors/parallax/parallax.min.js"></script>
  <!-- asRange -->
  <script src="vendors/range/jquery.range-min.js"></script>
  <!-- lightgallery -->
  <script src="vendors/lightgallery/lightgallery.min.js"></script>
  <!-- Main script -->
  <script src="vendors/script.js?v=1"></script>
  <script src="vendors/spectragram/spectragram.min.js"></script>
  <script>
    $(document).ready(function() {
      jQuery.fn.spectragram.accessData = {
        accessToken: '4058508404.1677ed0.f87c0182df0d4512a9e01def0c53adb7'
      }

      $('.instafeed').spectragram('getUserFeed', {
        size: 'big',
        max: 6
      });
    });
  </script>
</body>

</html>