<?php
// ini_set('display_errors',1); ini_set('display_startup_errors',1); error_reporting(E_ALL);
?>
<div class="wrapper">

<?php
include('header_admin.php');
include('../../qrscan/phpqrcode/qrlib.php');

// Salutation
$h = (int)date('H');
$salut = ($h < 12) ? 'Bonjour' : (($h < 15) ? 'Bon Après-midi' : 'Bonsoir');

// Param
$codget = isset($_GET['cod']) ? $_GET['cod'] : '';
$documentMode = (isset($_GET['mode']) && $_GET['mode'] === 'devis') ? 'devis' : 'facture';
$documentLabel = $documentMode === 'devis' ? 'Devis' : 'Facturation';

// Nom session (si dispo via header_admin.php)
$nomSession = isset($datasession['noms'])
    ? mb_convert_case($datasession['noms'], MB_CASE_TITLE, 'UTF-8')
    : 'Utilisateur';

// ---- Récup event
$stmt = $pdo->prepare("SELECT * FROM events WHERE cod_event = :cod_event");
$stmt->execute([':cod_event' => $codget]);
$dataevent = $stmt->fetch(PDO::FETCH_ASSOC);

// Valeurs par défaut
$codevent = $dataevent['cod_event'] ?? '';
$date_event = $dataevent['date_event'] ?? null;
$type_event = $dataevent['type_event'] ?? '';
$crea = $dataevent['crea'] ?? '';

// Type libellé
$stmtnv = $pdo->prepare("SELECT nom FROM evenement WHERE cod_event = ?");
$stmtnv->execute([$type_event]);
$libelle_evenement = ($row = $stmtnv->fetch(PDO::FETCH_ASSOC)) ? ($row['nom'] ?? '') : '';

if ($type_event === "1") {
    $typeevent = 'Mariage ' . ($dataevent['type_mar'] ?? 'Inconnu');
    $fetard = trim(($dataevent['prenom_epouse'] ?? '') . ' & ' . ($dataevent['prenom_epoux'] ?? ''));
    if ($fetard === '&') $fetard = 'Inconnu';
} elseif ($type_event === "2" || $type_event === "3") {
    $typeevent = $libelle_evenement ?: 'Événement';
    $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
} else {
    $typeevent = $libelle_evenement ?: 'Événement';
    $fetard = 'Inconnu';
}

// Date FR
if ($date_event) {
    try { $d = new DateTime($date_event); } catch (Exception $e) { $d = new DateTime(); }
} else { $d = new DateTime(); }
$fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, IntlDateFormatter::GREGORIAN, 'EEEE, dd/MM/yyyy à HH:mm');
$formatted_date = ucfirst($fmt->format($d));

// Couleurs statut
if ($crea === "2") {
    $color2 = 'color:#34A37B';
    $icon = '<i class="fas fa-check fs-24 l-h-50" style="color:#34A37B;"></i>';
    $bg_td = 'background-color:#F6FCF5';
} else {
    $color2 = '';
    $icon = '';
    $bg_td = '';
}

// Modèles utilisés par l’événement
$modeleinv1  = $dataevent['modele_inv']  ?? '';
$modelechev2 = $dataevent['modele_chev'] ?? '';
?>

<div class="content-wrapper">
  <div class="container-full">

    <div class="row salut">
      <p style="text-align:center;">
        <?= $salut; ?> <b><?= htmlspecialchars($nomSession, ENT_QUOTES, 'UTF-8'); ?></b> !
      </p>
    </div>

    <div class="row" id="mesinv">
      <div class="col-xxl-12 col-xl-12 col-lg-12">
        <div class="card rounded-4">

          <div class="box-header d-flex b-0 justify-content-between align-items-center">
            <h4 class="box-title">Commande N°<?= htmlspecialchars($codget, ENT_QUOTES, 'UTF-8'); ?></h4>
          </div>

          <div class="card-body pt-0" style="margin-top:-40px;">
            <!-- ===== Résumé (TABLE propre) ===== -->
            <div class="table-responsive">
              <table class="table mb-0">
                <tbody>
                  <?php if ($dataevent): ?>
                  <tr>
                    <td class="pt-0 px-0" style="margin-bottom:25px;border-bottom:1px solid #ccc;<?= $bg_td ?>;">
                      <div class="py-3 px-2">
                        <a class="d-block fw-500 fs-14" style="<?= $color2 ?>" href="#">
                          <?= htmlspecialchars(ucfirst($typeevent), ENT_QUOTES, 'UTF-8'); ?>,
                          <span class="text-fade" style="<?= $color2 ?>">
                            <?= htmlspecialchars($fetard, ENT_QUOTES, 'UTF-8'); ?>
                          </span>
                          <?= $icon ?>
                        </a>
                        <span><?= htmlspecialchars($formatted_date, ENT_QUOTES, 'UTF-8'); ?></span>
                      </div> 
                    </td>
                  </tr>
                  <?php else: ?>
                  <tr><td>Aucune commande trouvée.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <br>
            <!-- ===== FIN du tableau ===== -->

            <?php
            // ===== Soumission : insert details_fact =====
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dataevent) {
              $stmtaesub = $pdo->prepare("SELECT cod_accev, cod_acc, quantite FROM accessoires_event WHERE cod_event = ? ORDER BY cod_accev DESC");
                $stmtaesub->execute([$codget]);

              $detailRows = [];
                $ins = 0;
                while ($rowAcc = $stmtaesub->fetch(PDO::FETCH_ASSOC)) {
                    $k = (string)$rowAcc['cod_accev'];
                $qte = $_POST['qte'.$k] ?? '';
                    $pu  = $_POST['pu'.$k]  ?? '';
                    $lib = $_POST['libelle'.$k] ?? '';
                $quantiteParDefaut = isset($rowAcc['quantite']) ? (int) $rowAcc['quantite'] : 1;

                if ($qte === '') {
                  $qte = (string) max(1, $quantiteParDefaut);
                }

                    $qte = trim($qte); $pu = trim($pu); $lib = trim($lib);
                    if ($qte === '' || $pu === '' || $lib === '') continue;

                    $qteNum = (int)$qte;
                    $puNum  = (float)str_replace(',', '.', $pu);
                    $pt     = $qteNum * $puNum;

                $detailRows[] = [$codget, $lib, $qteNum, $puNum, $pt];
              }

              if (!empty($detailRows)) {
                $deleteDetails = $pdo->prepare("DELETE FROM details_fact WHERE cod_event = ?");
                $deleteDetails->execute([$codget]);

                $sql = "INSERT INTO details_fact (cod_event, libelle, qtecom, pu, pt, date_enreg)
                    VALUES (?, ?, ?, ?, ?, NOW())";
                $stmtIns = $pdo->prepare($sql);

                foreach ($detailRows as $detailRow) {
                  $stmtIns->execute($detailRow);
                  $ins++;
                }
                }

                if ($ins > 0) {
                    echo '<script>
                    Swal.fire({
                      title:' . json_encode($documentLabel) . ',
                        text:"Les détails sont enregistrés avec succès.",
                        icon:"success",
                        confirmButtonText:"Suivant >"
                    }).then((r)=>{ if(r.isConfirmed){ window.location.href="index.php?page=paiement_fin&cod='.htmlspecialchars($codget, ENT_QUOTES, 'UTF-8').'&mode=' . rawurlencode($documentMode) . '"; }});
                    </script>';
                } else {
                    echo '<script>
                    Swal.fire({ title:"Information", text:"Aucune ligne valide à enregistrer.", icon:"info", confirmButtonText:"OK" });
                    </script>';
                }
            }
            ?>

            <?php if ($dataevent): ?>
            <!-- ===== FORMULAIRE (en dehors du tableau) ===== -->
            <form id="eventForm" action="" method="post" enctype="multipart/form-data">
              <?php
                $stmtae = $pdo->prepare("SELECT cod_accev, cod_acc, quantite FROM accessoires_event WHERE cod_event = ? ORDER BY cod_accev DESC");
              $stmtae->execute([$codget]);

              while ($dataae = $stmtae->fetch(PDO::FETCH_ASSOC)):
                  $cod_accev = $dataae['cod_accev'];
                  $cod_acc   = $dataae['cod_acc'];

                  // Nom accessoire
                  $stmtnv = $pdo->prepare("SELECT nom FROM modele_is WHERE cod_mod = ?");
                  $stmtnv->execute([$cod_acc]);
                  $data_accessoire = $stmtnv->fetch(PDO::FETCH_ASSOC);
                  $accessoire = $data_accessoire['nom'] ?? '';
                    $accessoireNomLower = function_exists('mb_strtolower') ? mb_strtolower($accessoire, 'UTF-8') : strtolower($accessoire);
                    $accessoireNomNormalized = str_replace(['é', 'è', 'ê', 'ë'], 'e', $accessoireNomLower);
                    $isInvitationElectronique = strpos($accessoireNomNormalized, 'invitation') !== false && strpos($accessoireNomNormalized, 'elect') !== false;
                    $quantiteDemandee = isset($dataae['quantite']) ? (int) $dataae['quantite'] : 1;

                    $stmtDetail = $pdo->prepare("SELECT qtecom, pu FROM details_fact WHERE cod_event = ? AND libelle = ? ORDER BY cod_df DESC LIMIT 1");
                    $stmtDetail->execute([$codget, $accessoire]);
                    $detailLigne = $stmtDetail->fetch(PDO::FETCH_ASSOC) ?: [];
                    $quantiteInitiale = $isInvitationElectronique
                      ? 'Illimité'
                      : (string) max(1, (int) ($detailLigne['qtecom'] ?? $quantiteDemandee));
                    $prixUnitaireInitial = isset($detailLigne['pu']) ? (string) $detailLigne['pu'] : '';

                  // Modèle + image selon type
                  $modele_inv_txt = '';
                  $image_row = '';

                  if ($cod_acc == "1" && $modeleinv1) {
                      $stmtmi = $pdo->prepare("SELECT nom, image FROM modele_is WHERE cod_mod = ?");
                      $stmtmi->execute([$modeleinv1]);
                      if ($m = $stmtmi->fetch(PDO::FETCH_ASSOC)) {
                          $modele_inv_txt = '(' . $m['nom'] . ')';
                          $image_row = $m['image'] ?? '';
                      }
                  } elseif ($cod_acc == "3" && $modelechev2) {
                      $stmtmc = $pdo->prepare("SELECT nom FROM modele_is WHERE cod_mod = ?");
                      $stmtmc->execute([$modelechev2]);
                      if ($m = $stmtmc->fetch(PDO::FETCH_ASSOC)) {
                          $modele_inv_txt = '(' . $m['nom'] . ')';
                          $image_row = '';
                      }
                  }
              ?>
              <div class="form-group">

                <span>
                  <span class="hoverx-container" style="float:left">
                    <?= htmlspecialchars($accessoire, ENT_QUOTES, 'UTF-8'); ?>
                    <?php if ($modele_inv_txt): ?>
                      <?php if ($image_row): ?>
                        <a target="_blank"
                           href="https://invitationspeciale.com/event/images/modeleis/<?= htmlspecialchars($image_row, ENT_QUOTES, 'UTF-8'); ?>"
                           class="modelx-link"><?= htmlspecialchars($modele_inv_txt, ENT_QUOTES, 'UTF-8'); ?></a>
                      <?php else: ?>
                        <span class="modelx-link"><?= htmlspecialchars($modele_inv_txt, ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php endif; ?>
                    <?php endif; ?>
                  </span>

                  <span style="float:right">
                    <a href="#"
                       style="color:red;"
                       onclick="confirmSuppAcc(event, '<?= htmlspecialchars($cod_accev, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($codevent, ENT_QUOTES, 'UTF-8'); ?>')">
                       Supprimer
                    </a>
                  </span>
                </span>

                <input type="hidden" name="<?= 'libelle'.$cod_accev; ?>" value="<?= htmlspecialchars($accessoire, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($isInvitationElectronique): ?>
                <input type="hidden" name="<?= 'qte'.$cod_accev; ?>" value="1">
                <?php endif; ?>

                <div class="input-group mb-3">
                  <span class="input-group-text bg-transparent"><i class="fas fa-shopping-cart"></i></span>
                  <input type="<?php echo $isInvitationElectronique ? 'text' : 'number'; ?>" name="<?= 'qte'.$cod_accev; ?>" class="form-control ps-15 bg-transparent" placeholder="Quantité" inputmode="numeric" pattern="[0-9]*" value="<?= htmlspecialchars($quantiteInitiale, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isInvitationElectronique ? 'readonly disabled' : 'min="1" step="1"'; ?>>
                </div>

                <div class="input-group mb-3">
                  <span class="input-group-text bg-transparent" style="width:40px;"><i class="fas fa-dollar-sign"></i></span>
                  <input type="text" name="<?= 'pu'.$cod_accev; ?>" class="form-control ps-15 bg-transparent" placeholder="Prix Unitaire" inputmode="decimal" value="<?= htmlspecialchars($prixUnitaireInitial, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
              </div>
              <?php endwhile; ?>

              <div class="row">
                <div class="col-12 text-center">
                  <button type="submit" id="BtnEvent" class="btn btn-primary w-p100 mt-10">Enregistrer</button>
                </div>
              </div>
            </form>
            <?php endif; ?>

          </div><!-- /.card-body -->
        </div>
      </div>
    </div>

  </div><!-- /.container-full -->
</div><!-- /.content-wrapper -->

<?php include('footer.php'); ?>

</div><!-- /.wrapper -->

<script>
function confirmSuppAcc(event, codAccev, codEv) {
  event.preventDefault();
  Swal.fire({
    title: "Supprimer !",
    text: "Êtes-vous sûr de vouloir supprimer cette commande ?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Non"
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "index.php?page=supcomadmin&cod=" + encodeURIComponent(codAccev) + "&codevent=" + encodeURIComponent(codEv);
    }
  });
}
</script>

<!-- Tes JS -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<script src="html/template/horizontal/src/js/vendors.min.js"></script>
<script src="html/template/horizontal/src/js/pages/chat-popup.js"></script>
<script src="html/assets/icons/feather-icons/feather.min.js"></script>
<script src="html/assets/vendor_components/Flot/jquery.flot.js"></script>
<script src="html/assets/vendor_components/Flot/jquery.flot.resize.js"></script>
<script src="html/assets/vendor_components/Flot/jquery.flot.pie.js"></script>
<script src="html/assets/vendor_components/Flot/jquery.flot.categories.js"></script>
<script src="html/assets/vendor_components/echarts/dist/echarts-en.min.js"></script>
<script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
<script src="html/assets/vendor_plugins/bootstrap-slider/bootstrap-slider.js"></script>
<script src="html/assets/vendor_components/OwlCarousel2/dist/owl.carousel.js"></script>
<script src="html/assets/vendor_components/flexslider/jquery.flexslider.js"></script>
<script src="html/assets/vendor_components/Web-Ticker-master/jquery.webticker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="html/template/horizontal/src/js/demo.js"></script>
<script src="html/template/horizontal/src/js/jquery.smartmenus.js"></script>
<script src="html/template/horizontal/src/js/menus.js"></script>
<script src="html/template/horizontal/src/js/template.js"></script>
<script src="html/template/horizontal/src/js/pages/dashboard.js"></script>
<script src="html/template/horizontal/src/js/pages/slider.js"></script>
<script src="html/assets/vendor_components/full-calendar/moment.js"></script>
<script src="html/assets/vendor_components/full-calendar/fullcalendar.min.js"></script>
<script src="html/assets/vendor_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
<script src="html/assets/vendor_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
<script src="html/assets/vendor_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
<script src="html/assets/vendor_components/select2/dist/js/select2.full.js"></script>
<script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.js"></script>
<script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.extensions.js"></script>
<script src="html/assets/vendor_components/moment/min/moment.min.js"></script>
<script src="html/assets/vendor_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<script src="html/assets/vendor_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="html/assets/vendor_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
<script src="html/assets/vendor_plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script src="html/assets/vendor_plugins/iCheck/icheck.min.js"></script>
<script src="html/template/horizontal/src/js/pages/advanced-form-element.js"></script>
