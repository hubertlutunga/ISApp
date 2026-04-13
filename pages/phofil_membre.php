
  <?php 
        $valide = "oui";
        
        $req="SELECT * FROM membre where id_membre='{$_GET['id']}'";
        $rs= $pdo ->query($req);
        $row_membre_auth = $rs -> fetch();


        $req="SELECT COUNT(id_membre) AS total_n FROM membre where id_membre='{$_GET['id']}'";
        $rs= $pdo ->query($req);
        $row_c_id = $rs -> fetch();
   ?>
 
    <div class="mains" style="margin-top: -80px;">
      <div class="containers a-containers" id="a-containers" style="padding-bottom: 20px;">
        <form class="form" id="a-form" method="post" action="">

          <span class="form__span" style="color:#fff;margin-top: 0px;text-align: center;margin-bottom:10px ;">


            <?php if ($row_c_id['total_n'] == 0 OR $row_membre_auth['valide'] == 'non') {
             
?> 

          <a href="index.php?page=accueil"><img src="img/logo_forcep.png" width="150px;" style="margin-top:50px;" alt=""></a> <br>

          <h2 class="form_title title" style="color:#fff;">Force du Progrès</h2>
          <h2 style="color:yellow;" style="margin-top: -30px;">Carte non valide</h2><br>
            Cette personne n'est pas reconnue comme membre de la Force du Progrès
<?php
            }else{
              ?>

          <a href="index.php?page=accueil"><img src="img/logo_forcep.png" width="80px;" style="margin-top:120px;" alt=""></a> 
          <img src="fpadmin/photoperso/<?php echo $row_membre_auth ['photo']; ?>" width="70px;"  height="70px;" style="border-radius:50%;" alt="" class="pic">
          <br>

          <h2 class="form_title title" style="color:#fff;">Force du Progrès</h2>
          <h2 style="color:yellow;margin-top: -15px;">Carte authentique</h2><br>


             <?php echo $row_membre_auth["prenom"].' '.$row_membre_auth["nom"]; ?> est <?php echo $row_membre_auth['fonction']; ?> de la Force du Progrès<br>  Les informations ci-dessous doivent correspondre <br>  à celles de la carte et le lien doit etre dans  <br> le siteweb officiel (www.forceduprogres.org).

          </span>

     
          <input class="form__input" disabled value="Nom: <?php echo $row_membre_auth['nom']; ?>">
          <input class="form__input" disabled value="Postnom: <?php echo $row_membre_auth['postnom']; ?>">
          <input class="form__input" disabled value="Prénom: <?php echo $row_membre_auth['prenom']; ?>">
          <input class="form__input" disabled value="Matricule: <?php echo $row_membre_auth['matr_membre']; ?>">

          <input class="form__input"  disabled value="Section: <?php echo $row_membre_auth['section']; ?>">
          <input class="form__input" disabled  value="Fédération: <?php echo $row_membre_auth['feder']; ?>">
          <input class="form__input" disabled value="Fonction: <?php echo $row_membre_auth['fonction']; ?>">
     



              <?php
            } 
            ?>





        </form>
      </div>
      
    </div>
    <script src="main.js"></script>

<!-- partial -->
  <script  src="./script.js"></script>

