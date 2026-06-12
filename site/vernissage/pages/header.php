<header id="header" class="header header-classic">
     

<style>
   .navbar {
    display: flex !important;
    justify-content: center !important; /* Centre horizontalement */
    align-items: center !important; /* Centre verticalement, si nécessaire */
   }
</style>

<?php  

   if ($dataevent['logo']) {
      $logo = '<img src="../../couple/images/'.$dataevent['logo'].'" width="300px" alt="logo">';
   }else{
       $logo = '<span style = "text-align:center;font-weight:bold;">'.htmlspecialchars((string) ($publicEventLabels['logo_cta'] ?? 'VENEZ PARTICIPER'), ENT_QUOTES, 'UTF-8').'<span>';
   }

?>

      <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
               <!-- logo-->
               <a class="navbar-brand" href="index.php?page=accueil&cod=<?php echo $codevent;?>">
                  <?php echo $logo;?>
               </a> 
            </nav>
         </div><!-- container end-->
      </header>