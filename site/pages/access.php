<?php 
if ($dataevent['photostory'] === NULL) {
   $photo = 'defaulwed_1.png';
}else{
   $photo = $dataevent['photostory'];
}
?>  



<!-- Preloader -->
<div class="loader">
  <div class="loader-inner">
    <svg width="120" height="220" viewbox="0 0 100 100" class="loading-spinner" version="1.1" xmlns="http://www.w3.org/2000/svg">
      <circle class="spinner" cx="50" cy="50" r="21" fill="#ffffff" stroke-width="2" />
    </svg>
  </div>
  
</div>
<!-- End preloader-->

<div style="display: flex; justify-content: center;">
  <a href="index.php?page=access&cod=<?php echo $codevent?>"><img src="../images/Logo_invitationSpeciale_3.png" width="300px;"></a>
</div>

<section id="rsvp" class="bg-secondary spacer-one-top-lg o-hidden ">
  <!--Container-->
  <div class="container spacer-one-bottom-lg"  style="margin-top:-50px;">

    <h1 style="text-align:center;">The Wedding</h1>
    <h2 style="text-align:center;font-size:50px;font-family: 'Playfair Display"><?php echo $fetard; ?></h2>
    <p style="text-align:center;letter-spacing:15px;"><span><?php echo date('d/m/Y',strtotime($date_event));?></span></p>

    <?php 
      // ==== Compteurs ====
      $couple = "C";
      $reqci=$pdo->prepare("SELECT count(*) as total_ci FROM invite where sing = :s AND cod_mar = :c");
      $reqci->execute([':s'=>$couple, ':c'=>$codevent]);
      $row_ci=$reqci->fetch();

      $reqcia=$pdo->prepare("SELECT count(*) as total_ca FROM invite where sing = :s AND acces = 'oui' AND cod_mar = :c");
      $reqcia->execute([':s'=>$couple, ':c'=>$codevent]);
      $row_accoupl=$reqcia->fetch();

      $total_coupl_acces = ((int)$row_accoupl['total_ca']) * 2;
      $total_coupl       = ((int)$row_ci['total_ci']) * 2; 

      $sing1 = "Mme";
      $sing2 = "Mr"; 

      $reqci_s=$pdo->prepare("SELECT count(*) as total_s FROM invite where (sing = :s1 OR sing = :s2) AND cod_mar = :c");
      $reqci_s->execute([':s1'=>$sing1, ':s2'=>$sing2, ':c'=>$codevent]);
      $row_ci_s=$reqci_s->fetch();

      $reqcias=$pdo->prepare("SELECT count(*) as total_cas FROM invite where (sing = :s1 OR sing = :s2) AND acces = 'oui' AND cod_mar = :c");
      $reqcias->execute([':s1'=>$sing1, ':s2'=>$sing2, ':c'=>$codevent]);
      $row_acsing=$reqcias->fetch();

      $total_singl_sing = (int)$row_acsing['total_cas'];
      $total_singl      = (int)$row_ci_s['total_s'];

      $nomb_inv   = $total_coupl + $total_singl;
      $nomb_acces = $total_coupl_acces + $total_singl_sing;
      $nomb_absent= $nomb_inv - $nomb_acces;
    ?>

    <div style="font-size: 1.3em;text-align:center;">
      <span><b><?php echo $nomb_inv; ?></b></span> Invités <span style="color:#fff;">/</span>
      <span style ="color:#3bbc72;"><b><?php echo $nomb_acces; ?></b></span> Présents <span style="color:#fff;">/</span>
      <span style ="color:#F69D9D;"><b><?php echo $nomb_absent; ?></b></span> Absents
      &nbsp;&nbsp;<small style="color:#bbb">• Résultats: <span id="invitesCount">—</span></small>
    </div>

    







            <?php include('scanqr.php');?>








 

    <form action="" method="POST" class="forms-sample"> 
      <input type="text" name="searchs" id="searchs" placeholder="Rechercher par mom ou par table" style="width: 100%;height: 45px;text-align:center;" autocomplete="off">
    </form>

    <table width="100%" style="margin-bottom:100px;">
      <thead>
        <tr style="margin-bottom: 45px;">
          <th style="width: 55%;">NOMS</th>
          <th style="width: 5%;">TI</th>
          <th align="right" style="width: 40%;text-align: right;">TABLE</th>
        </tr>
      </thead>
      <tbody id="invitesBody">
        <?php 
          // ==== Liste initiale (sécurisée) ====
          $reqinv = $pdo->prepare("SELECT * FROM invite WHERE cod_mar = :cod ORDER BY nom ASC");
          $reqinv->execute([':cod'=>$codevent]);
          while($row_inv=$reqinv->fetch(PDO::FETCH_ASSOC)){

            if (empty($row_inv['acces'])) {
              $color = '';
            }elseif($row_inv['acces'] == "oui"){
              $color = '#3bbc72';
            } else {
              $color = '';
            }

            $reqtab = $pdo->prepare("SELECT nom_tab FROM tableevent WHERE cod_tab = :cod_tab AND cod_event = :cod_event");
            $reqtab->execute([':cod_tab' => $row_inv['siege'], ':cod_event' => $codevent]);
            $row_tab = $reqtab->fetch(PDO::FETCH_ASSOC);

            $nomtable = $row_tab ? $row_tab['nom_tab'] : 'Non définie';

            $sing = $row_inv['sing'] === 'C' ? 'Couple' : ($row_inv['sing'] ? 'Singleton' : '<em>Non défini</em>');
        ?> 
        <tr style="margin-bottom:15px;">
          <td align="left" style="border-bottom:1px solid #aaa;padding: 7px 0px;">
            <a href="index.php?page=access_cible&codinv=<?php echo (int)$row_inv['id_inv']; ?>&cod=<?php echo htmlspecialchars($codevent,ENT_QUOTES,'UTF-8'); ?>" style="color: <?php echo htmlspecialchars($color,ENT_QUOTES,'UTF-8'); ?>">
              <?php echo htmlspecialchars($row_inv['nom'],ENT_QUOTES,'UTF-8'); ?>
            </a>
          </td>
          <td align="left" style="border-bottom:1px solid #aaa;padding: 7px 0px;">
            <a href="index.php?page=access_cible&codinv=<?php echo (int)$row_inv['id_inv']; ?>&cod=<?php echo htmlspecialchars($codevent,ENT_QUOTES,'UTF-8'); ?>" style="color: <?php echo htmlspecialchars($color,ENT_QUOTES,'UTF-8'); ?>">
              <?php echo $sing; ?>
            </a>
          </td>
          <td align="right" style="border-bottom:1px solid #aaa;padding: 7px 0px;">
            <a href="index.php?page=access_cible&codinv=<?php echo (int)$row_inv['id_inv']; ?>&cod=<?php echo htmlspecialchars($codevent,ENT_QUOTES,'UTF-8'); ?>" style="color: <?php echo htmlspecialchars($color,ENT_QUOTES,'UTF-8'); ?>">
              <?php echo htmlspecialchars($nomtable,ENT_QUOTES,'UTF-8'); ?>
            </a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>

    <br><br>
    <div style="display: flex; justify-content: center;">
      <a style="font-size: 1.3em;" href="../event/pages/liste_invites.php?event=<?php echo htmlspecialchars($codevent,ENT_QUOTES,'UTF-8');?>" target="_blank">Imprimer la Liste</a>
    </div>

  </div>
</section>

<section class="footer-copyright spacer-double-sm bg-white text-center">
  <p class="text-uppercase small text-muted d-block mb-0">&copy; <?php echo date('Y')?> Hubert Solutions all right reserved</p>
  <p class="text-muted small d-block mb-0">Siteweb, branche de <a href="https://www.invitationspeciale.com">invitationspeciale.com</a><br> 
  Sous : <a href="https://hubertlutunga.com">Hubert Lutunga</a></p>
</section>







<script>
(function(){

  /* =========================
     LIVE SEARCH (inchangé)
  ========================== */
  const $input  = document.getElementById('searchs');
  const $tbody  = document.getElementById('invitesBody');
  const $count  = document.getElementById('invitesCount');

  const CODEVENT = <?php echo json_encode($codevent, JSON_UNESCAPED_UNICODE); ?>;

  if ($count) {
    $count.textContent = document.querySelectorAll('#invitesBody > tr').length;
  }

  const debounce = (fn, wait=250) => {
    let t; 
    return (...args)=>{ 
      clearTimeout(t); 
      t=setTimeout(()=>fn(...args), wait); 
    };
  };

  let lastController = null;
  let lastQuery = '';

  async function fetchResults(q){
    q = (q || '').trim();
    if (q === lastQuery) return;
    lastQuery = q;

    if (lastController) lastController.abort();
    lastController = new AbortController();

    try {
      const res = await fetch(
        'pages/search_invites.php?q=' + encodeURIComponent(q) +
        '&cod=' + encodeURIComponent(CODEVENT),
        {
          headers: { 'X-Requested-With':'XMLHttpRequest' },
          signal: lastController.signal
        }
      );

      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      $tbody.innerHTML = data.html || '';
      if ($count) $count.textContent = data.count ?? 0;

    } catch (e) {
      if (e.name !== 'AbortError') console.error(e);
    }
  }

  if ($input) {
    $input.addEventListener(
      'input',
      debounce(()=> fetchResults($input.value), 250)
    );
  }

})();
</script>









<!-- Tes JS existants -->
<script src="js/jquery-1.12.4.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/smooth-scroll.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/jquery.countdown.min.js"></script>
<script src="js/placeholders.min.js"></script>
<script src="js/instafeed.min.js"></script>
<script src="js/script.js"></script>
