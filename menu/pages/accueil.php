 
        
  <?php
  
    if(!$dataevent['photo']){
        $bg = 'defaulwed_1.png';
    }else{
        $bg = $dataevent['photo'];
    }
  
  ?>  
  <div class="aheto-titlebar aheto-titlebar--restaurant aheto-titlebar--height-500">
    <div class="aheto-titlebar__main   ">
      <img class="js-bg" alt="" src="../couple/images/<?php echo $bg; ?>">
      <div class="aheto-titlebar__content w-1000">
        <div class="aheto-titlebar__text ">
          <p class="aheto-titlebar__subtitle t-white   t-medium t-center t-uppercase">Menu</p>
          <h1 class="aheto-titlebar__title  t-white t-semibold t-center  large-size"><?php echo $typeevent;?> </h1>
          <h1 class="aheto-titlebar__title  t-white t-center  large-size" style="font-family: 'Great Vibes', cursive;margin-top:15px;"><?php echo $fetard;?> </h1>
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

    $menu_option = @$_POST['menu_option']; 
    $codevent = $_GET['cod']; 
    $table = $_GET['table'];  
    $invite = @$_POST['invite'];  
    
    if (!$menu_option) {
        echo '<script>
        Swal.fire({
            title: "Quelle boisson !",
            text: "Veuillez sélectionner une boisson.",
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
                          echo '<script>
                          Swal.fire({
                              title: "Commande !",
                              text: "Votre commande a été reçue avec succès.",
                              icon: "success",
                              confirmButtonText: "OK"
                          }).then((result) => {
                              if (result.isConfirmed) {
                                  window.location.href = "index.php?page=accueil&cod=' . $codevent . '&table=' . htmlspecialchars($_GET['table']) . '"; // Rédirection vers la page de détails
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

  .aht-pricing__lin {
    border: 2px solid transparent;
    padding: 10px;
    border-radius: 5px;
    transition: border-color 0.3s;
}

.aht-pricing__lin.selected {
    border-color: #007bff; /* Couleur de la bordure lorsqu'il est sélectionné */
    background-color: #f0f8ff; /* Couleur de fond pour mettre en valeur */
}


</style>


<script>
document.querySelectorAll('input[name="menu_option"]').forEach((radio) => {
    radio.addEventListener('change', function() {
        // Retirer la classe 'selected' de tous les éléments <li>
        document.querySelectorAll('.aht-pricing__lin').forEach((li) => {
            li.classList.remove('selected');
        });

        // Ajouter la classe 'selected' au parent <li> du radio sélectionné
        const parentLi = this.closest('.aht-pricing__lin');
        if (parentLi) {
            parentLi.classList.add('selected');
        }
    });
});
</script>

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
 



?>


              <h4 class="aht-pricing__title"><?php echo  strtoupper($nomcat);?></h4>
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

if (!$desc) {
    $descmenu = 'Aucune description pour '.$row_menu2['nom'];
}else{
    $descmenu = $desc ;
}

                                        
?>
 
   
    <li class="aht-pricing__lin" style="height:100px;"> 
      <div class="aht-pricing__price-ultraWrap">
        <div class="aht-pricing__price-wrap">
          <span class="aht-pricing__per" onclick="selectRadio('<?php echo htmlspecialchars($row_menu2['cod_mev']); ?>')"><?php echo htmlspecialchars($row_menu2['nom']); ?></span>
          <span class="aht-pricing__price">$ 0</span>
        </div>
        <div class="aht-pricing__composition" style="margin-top:5px;" onclick="selectRadio('<?php echo htmlspecialchars($row_menu2['cod_mev']); ?>')"><?php echo htmlspecialchars($descmenu); ?></div>
        <div>
          <input type="radio" name="menu_option" value="<?php echo htmlspecialchars($row_menu2['cod_mev']); ?>" 
            <?php echo (isset($_POST['submit']) && @$_POST['menu_option'] == $row_menu2['cod_mev']) ? 'checked' : ''; ?> 
            id="menu_<?php echo $row_menu2['cod_mev']; ?>">
          <label for="menu_<?php echo $row_menu2['cod_mev']; ?>"> Sélectionner</label>
        </div>
      </div>
    </li>
    <hr>
<hr> 
   
 
<?php 

    }

    } 


    }

    } 

?>

              </ul>
            </div>

 
          </div>
        </div>
      </div>
    </div>
  </div>
  



<style>
.aht-pricing__lin {
    border: 2px solid transparent;
    padding: 10px;
    border-radius: 5px;
    transition: border-color 0.3s;
}

.aht-pricing__lin.selected {
    border-color: #007bff; /* Couleur de la bordure lorsqu'il est sélectionné */
    background-color: #f0f8ff; /* Couleur de fond pour mettre en valeur */
}
</style>

<script>
function selectRadio(value) {
    const radio = document.querySelector(`input[name="menu_option"][value="${value}"]`);
    if (radio) {
        radio.checked = true; // Sélectionne le bouton radio
        const parentLi = radio.closest('.aht-pricing__lin');
        
        // Retirer la classe 'selected' de tous les éléments <li>
        document.querySelectorAll('.aht-pricing__lin').forEach((li) => {
            li.classList.remove('selected');
        });

        // Ajouter la classe 'selected' au parent <li>
        if (parentLi) {
            parentLi.classList.add('selected');
        }
    }
}

document.querySelectorAll('input[name="menu_option"]').forEach((radio) => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.aht-pricing__lin').forEach((li) => {
            li.classList.remove('selected');
        });

        const parentLi = this.closest('.aht-pricing__lin');
        if (parentLi) {
            parentLi.classList.add('selected');
        }
    });
});
</script>


		 <?php if (!isset($_GET['table'])){

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
          <h2 class="aheto-heading__title    f-style-italic    f-40  t-bold ">Thanks For the Reservation</h2>
          <p class="aheto-heading__desc   ">Your application has been accepted, the confirmation will come to the email you specified</p>
        </div>
        <div class="aheto-single-img   ">
          <img src="../img/restaurant/pepper.jpg" class="  " alt="single img">
        </div>
      </div>
      <div class="aheto-heading t-center aheto-heading--restaurant-contact">
        <h2 class="aheto-heading__title    f-style-italic    f-40  t-bold ">Commande une boisson</h2>
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


            $codtabele = $_GET['table'];
            $reqtab = "SELECT * FROM tableevent WHERE cod_tab = :cod_tab AND cod_event = :cod_event";
            $reqtab = $pdo->prepare($reqtab);
            $reqtab->execute([':cod_tab' => $codtabele, ':cod_event' => $codevent]);
            $row_tab = $reqtab->fetch(PDO::FETCH_ASSOC);

            $nomtable = $row_tab ? $row_tab['nom_tab'] : 'Non définie';
                            
?>


            <div class="col-12 col-md-6 col-lg-4 wpcf7-form-control-wrap input-icon input-icon-th-large">
               <input type="text" disabled name="table" value="<?php echo 'Table '.$nomtable;?>" size="40" class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-tel" aria-invalid="false" placeholder="Table" required>
            </div> 

            <div class="col-12 col-md-6 col-lg-4 wpcf7-form-control-wrap input-icon input-icon-persons"> 
            
              <select  class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-tel" name="invite">
                                            <option style="color:#eee;" value="">Votre Nom</option>
                                            <?php 
                                            
                              $reqinv="SELECT * FROM invite where cod_mar = '$codevent' and siege = '$codtabele' ORDER by nom ASC";
                              $inv=$pdo->query($reqinv); 
                                            while ($row_inv=$inv->fetch()) {
                                            ?>
                                            <option value="<?php echo $row_inv['id_inv']?>" <?php if(@$_POST['invite'] == $row_inv['id_inv']){echo "selected";} ?>><?php echo $row_inv['nom']?></option>
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