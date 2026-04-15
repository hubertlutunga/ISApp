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
  <a href="index.php?page=access&cod=<?php echo $codevent?>"><img src="../images/Logo_invitationSpeciale_1.png" width="300px;"></a>
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
      $show_absents = strtotime(date('Y-m-d')) >= strtotime(date('Y-m-d', strtotime($date_event)));
    ?>

    <div style="font-size: 1.3em;text-align:center;">
      <span><b><?php echo $nomb_inv; ?></b></span> Invités <span style="color:#fff;">/</span>
      <span style ="color:#3bbc72;"><b><?php echo $nomb_acces; ?></b></span> Présents
      <?php if ($show_absents) { ?>
      <span style="color:#fff;">/</span>
      <span style ="color:#F69D9D;"><b><?php echo $nomb_absent; ?></b></span> Absents
      <?php } ?>
    </div>

    







            <?php include('scanqr.php');?>








    <?php
    if ($dataevent['photostory'] === NULL) {
       $photo = 'defaulwed_1.png';
    } else {
       $photo = $dataevent['photostory'];
    }
    ?>

    <style>
      .access-shell {
        max-width: 1120px;
        margin: 0 auto;
        padding: 42px 18px 110px;
      }

      .access-brand {
        display: flex;
        justify-content: center;
        margin-bottom: 26px;
      }

      .access-brand img {
        width: min(320px, 78vw);
        height: auto;
        filter: drop-shadow(0 20px 28px rgba(15, 23, 42, 0.12));
      }

      .access-search-form {
        margin-top: 16px;
      }

      .access-search-input {
        width: 100%;
        height: 56px;
        padding: 0 18px;
        border-radius: 18px;
        border: 1px solid #dbeafe;
        background: #f8fbff;
        color: #0f172a;
        font-size: 15px;
        font-weight: 700;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
      }

      .access-search-input:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
      }

      #qrWrap {
        margin: 16px 0 0 !important;
      }

      #qrStatus {
        color: #64748b !important;
        font-weight: 700;
      }

      #qr-reader {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.95);
        background: #fff;
        box-shadow: 0 16px 28px rgba(15, 23, 42, 0.08);
      }

      .access-list-wrap {
        margin-top: 22px;
        border-radius: 28px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 24px 44px rgba(15, 23, 42, 0.08);
        overflow: hidden;
      }

      .access-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 22px 22px 14px;
      }

      .access-list-head h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 900;
        color: #0f172a;
      }

      .access-list-head p {
        margin: 4px 0 0;
        font-size: 14px;
        color: #64748b;
      }

      .access-print-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 0 16px;
        border-radius: 16px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
      }

      .access-table-holder {
        padding: 0 22px 12px;
      }

      .access-list-search {
        margin-bottom: 16px;
      }

      .access-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
      }

      .access-table thead th {
        padding: 0 0 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
      }

      .access-table tbody tr {
        transition: background-color 0.18s ease;
      }

      .access-table tbody tr:hover {
        background: rgba(248, 250, 252, 0.72);
      }

      .access-table tbody td {
        border-bottom: 1px solid #eef2f7;
        padding: 14px 0;
        vertical-align: middle;
      }

      .access-table tbody tr:last-child td {
        border-bottom: 0;
      }

      .access-table a {
        text-decoration: none;
        font-weight: 800;
      }

      .access-table td:first-child a {
        color: inherit;
        font-size: 15px;
      }

      .access-type-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
      }

      .access-table-name {
        font-weight: 700;
        color: #334155;
      }

      @media only screen and (max-width: 980px) {
        .access-shell {
          padding-top: 12px;
        }
      }

      @media only screen and (max-width: 769px) {
        .access-shell {
          padding: 0 14px 88px;
        }

        .access-list-head,
        .access-table-holder {
          padding-left: 16px;
          padding-right: 16px;
        }

        .access-list-head {
          flex-direction: column;
          align-items: stretch;
        }

        .access-print-link {
          width: 100%;
        }

        .access-table,
        .access-table thead,
        .access-table tbody,
        .access-table th,
        .access-table td,
        .access-table tr {
          display: block;
        }

        .access-table thead {
          display: none;
        }

        .access-table tbody tr {
          padding: 14px 0;
          border-bottom: 1px solid #eef2f7;
        }

        .access-table tbody td {
          border: 0;
          padding: 4px 0;
          text-align: left !important;
        }

        .access-table tbody td::before {
          display: block;
          margin-bottom: 4px;
          font-size: 11px;
          font-weight: 900;
          letter-spacing: 0.12em;
          text-transform: uppercase;
          color: #94a3b8;
        }

        .access-table tbody td:nth-child(1)::before {
          content: 'Invite';
        }

        .access-table tbody td:nth-child(2)::before {
          content: 'Type';
        }

        .access-table tbody td:nth-child(3)::before {
          content: 'Table';
        }

      }
    </style>

    <div class="loader">
      <div class="loader-inner">
        <svg width="120" height="220" viewbox="0 0 100 100" class="loading-spinner" version="1.1" xmlns="http://www.w3.org/2000/svg">
          <circle class="spinner" cx="50" cy="50" r="21" fill="#ffffff" stroke-width="2" />
        </svg>
      </div>
    </div>

    <section id="rsvp">
      <div class="access-shell">
       
        <div class="access-list-wrap">
          <div class="access-list-head">
            <div>
              <h3>Liste des invites</h3>
              <p>Consultez les acces en temps reel et ouvrez chaque fiche de verification.</p>
            </div>
            <a class="access-print-link" href="../event/pages/liste_invites.php?event=<?php echo htmlspecialchars($codevent, ENT_QUOTES, 'UTF-8');?>" target="_blank">Imprimer la liste</a>
          </div>

          <div class="access-table-holder">
            <form action="" method="POST" class="access-search-form access-list-search">
              <input type="text" name="searchs" id="searchs" class="access-search-input" placeholder="Rechercher par nom ou par table" autocomplete="off">
            </form>

            <table class="access-table">
              <thead>
                <tr>
                  <th style="width: 55%;">Noms</th>
                  <th style="width: 15%;">Type</th>
                  <th align="right" style="width: 30%;text-align: right;">Table</th>
                </tr>
              </thead>
              <tbody id="invitesBody">
                <?php
                  $reqinv = $pdo->prepare("SELECT * FROM invite WHERE cod_mar = :cod ORDER BY nom ASC");
                  $reqinv->execute([':cod' => $codevent]);
                  while ($row_inv = $reqinv->fetch(PDO::FETCH_ASSOC)) {
                    if (empty($row_inv['acces'])) {
                      $color = '';
                    } elseif ($row_inv['acces'] == "oui") {
                      $color = '#3bbc72';
                    } else {
                      $color = '';
                    }

                    $reqtab = $pdo->prepare("SELECT nom_tab FROM tableevent WHERE cod_tab = :cod_tab AND cod_event = :cod_event");
                    $reqtab->execute([':cod_tab' => $row_inv['siege'], ':cod_event' => $codevent]);
                    $row_tab = $reqtab->fetch(PDO::FETCH_ASSOC);

                    $nomtable = $row_tab ? $row_tab['nom_tab'] : 'Non definie';
                    $sing = $row_inv['sing'] === 'C' ? 'Couple' : ($row_inv['sing'] ? 'Singleton' : 'Non defini');
                ?>
                <tr>
                  <td align="left" style="border-bottom:1px solid #aaa;padding: 7px 0px;">
                    <a href="index.php?page=access_cible&codinv=<?php echo (int) $row_inv['id_inv']; ?>&cod=<?php echo htmlspecialchars($codevent, ENT_QUOTES, 'UTF-8'); ?>" style="color: <?php echo htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>">
                      <?php echo htmlspecialchars($row_inv['nom'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  </td>
                  <td align="left" style="border-bottom:1px solid #aaa;padding: 7px 0px;">
                    <a href="index.php?page=access_cible&codinv=<?php echo (int) $row_inv['id_inv']; ?>&cod=<?php echo htmlspecialchars($codevent, ENT_QUOTES, 'UTF-8'); ?>" style="color: <?php echo htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>">
                      <span class="access-type-pill"><?php echo htmlspecialchars($sing, ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                  </td>
                  <td align="right" style="border-bottom:1px solid #aaa;padding: 7px 0px;">
                    <a href="index.php?page=access_cible&codinv=<?php echo (int) $row_inv['id_inv']; ?>&cod=<?php echo htmlspecialchars($codevent, ENT_QUOTES, 'UTF-8'); ?>" style="color: <?php echo htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>" class="access-table-name">
                      <?php echo htmlspecialchars($nomtable, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
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
      const $input  = document.getElementById('searchs');
      const $tbody  = document.getElementById('invitesBody');
      const $count  = document.getElementById('invitesCount');

      const CODEVENT = <?php echo json_encode($codevent, JSON_UNESCAPED_UNICODE); ?>;

      if ($count) {
        $count.textContent = document.querySelectorAll('#invitesBody > tr').length;
      }

      const debounce = (fn, wait = 250) => {
        let timer;
        return (...args) => {
          clearTimeout(timer);
          timer = setTimeout(() => fn(...args), wait);
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
              headers: { 'X-Requested-With': 'XMLHttpRequest' },
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
        $input.addEventListener('input', debounce(() => fetchResults($input.value), 250));
      }
    })();
    </script>

    <script src="js/jquery-1.12.4.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/smooth-scroll.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/jquery.countdown.min.js"></script>
    <script src="js/placeholders.min.js"></script>
    <script src="js/instafeed.min.js"></script>
    <script src="js/script.js"></script>
