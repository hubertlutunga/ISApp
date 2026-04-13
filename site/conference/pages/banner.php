<?php
 
$date = new DateTime($date_event);

// Création d'un formatteur pour le français
$formatter = new IntlDateFormatter(
    'fr_FR',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    null,
    IntlDateFormatter::GREGORIAN,
    'EEEE, dd MMMM yyyy à HH:mm' // Utiliser HH pour le format 24 heures
);

// Formatage de la date
$formatted_date = $formatter->format($date);

// Met la première lettre en majuscule
$formatted_date = ucfirst($formatted_date); 



?>

<section class="hero-area centerd-item">
         <div class="banner-item" style="background-image:url(./images/hero_area/banner2.jpg)">
            <div class="container">
               <div class="row">
                  <div class="col-lg-8 mx-auto">
                     <div class="banner-content-wrap text-center">

                        <p class="banner-info"><?php echo $formatted_date; ?>, <?php echo $lieu; ?></p>
                        <h1 class="banner-title"><?php echo $dataevent['themeconf']; ?></h1>

                       
                        













                        <?php
// Assure le bon fuseau
date_default_timezone_set('Africa/Kinshasa');

// Exemple: $dataevent['date_event'] = "2025-10-10 14:00:00"
$eventDate = isset($dataevent['date_event'])
  ? date('Y/m/d H:i:s', strtotime($dataevent['date_event']))  // <-- format sûr pour JS
  : date('Y/m/d H:i:s', strtotime('+1 day')); // fallback
?>




<div class="countdown" data-date="<?php echo htmlspecialchars($eventDate, ENT_QUOTES); ?>">
  <div class="counter-item">
    <i class="icon icon-ring-1Asset-1"></i>
    <span class="days"></span>
    <div class="smalltext">Jours</div>
  </div>
  <div class="counter-item">
    <i class="icon icon-ring-4Asset-3"></i>
    <span class="hours"></span>
    <div class="smalltext">Heures</div>
  </div>
  <div class="counter-item">
    <i class="icon icon-ring-3Asset-2"></i>
    <span class="minutes"></span>
    <div class="smalltext">Minutes</div>
  </div>
  <div class="counter-item">
    <i class="icon icon-ring-4Asset-3"></i>
    <span class="seconds"></span>
    <div class="smalltext">Secondes</div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


                        <!-- Countdown end -->
                        <div class="banner-btn">
                           <a href="#participer" class="btn">Participer</a>
                        </div>

                     </div>
                     <!-- Banner content wrap end -->
                  </div><!-- col end-->

               </div><!-- row end-->
            </div>
            <!-- Container end -->
         </div>

      </section>

