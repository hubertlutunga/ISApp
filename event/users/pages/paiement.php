<?php
// ini_set('display_errors',1); ini_set('display_startup_errors',1); error_reporting(E_ALL);
?>
<div class="wrapper">

<?php
include('header_admin.php');

// Salutation
$h = (int)date('H');
$salut = ($h < 12) ? 'Bonjour' : (($h < 15) ? 'Bon Après-midi' : 'Bonsoir');
$nomSession = mb_convert_case((string) ($datasession['noms'] ?? ''), MB_CASE_TITLE, 'UTF-8');

// Param
$codget = isset($_GET['cod']) ? (int) $_GET['cod'] : 0;
$documentMode = (isset($_GET['mode']) && $_GET['mode'] === 'devis') ? 'devis' : 'facture';
$documentLabel = $documentMode === 'devis' ? 'Devis' : 'Facturation';

$stmt = $pdo->prepare("SELECT * FROM events where cod_event = :cod_event");
$stmt->execute(['cod_event' => $codget]);
$dataevent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

if (!$dataevent) {
  $codevent = 0;
  $date_event = '';
  $type_event = '';
  $display = 'none';
} else {
  $date_event = (string) ($dataevent['date_event'] ?? '');
  $type_event = (string) ($dataevent['type_event'] ?? '');
  $display = 'block';
  $codevent = (int) ($dataevent['cod_event'] ?? 0);
}

$checkoutData = EventOrderService::loadCheckoutByEvent($pdo, $codevent);
$invoiceSummary = EventOrderService::buildInvoiceSummaryForEvent($pdo, $codevent);
$invoiceLines = $invoiceSummary['lines'] ?? [];
$invoiceCurrency = (string) ($invoiceSummary['currency'] ?? ($checkoutData['devise'] ?? 'USD'));
$paymentTypeLabel = EventOrderService::paymentLabel($checkoutData['type_paiement'] ?? null);

$stmtnv = $pdo->prepare("SELECT * FROM evenement WHERE cod_event = ?");
$stmtnv->execute([$type_event]);
$data_evenement = $stmtnv->fetch(PDO::FETCH_ASSOC) ?: [];
$libelle_evenement = (string) ($data_evenement['nom'] ?? '');

if ($type_event == "1") {
  $typeevent = 'Mariage ' . ((string) ($dataevent['type_mar'] ?? 'Inconnu'));
  $fetard = trim(((string) ($dataevent['prenom_epouse'] ?? '')) . ' & ' . ((string) ($dataevent['prenom_epoux'] ?? '')));
  if ($fetard === '&' || $fetard === '') {
    $fetard = 'Inconnu';
  }
} elseif ($type_event == "2" || $type_event == "3") {
  $typeevent = $libelle_evenement !== '' ? $libelle_evenement : 'Événement';
  $fetard = (string) ($dataevent['nomfetard'] ?? 'Inconnu');
} else {
  $typeevent = $libelle_evenement !== '' ? $libelle_evenement : 'Événement';
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
$promoOptions = EventOrderService::promoCatalog($pdo);
$currentPromoCode = strtoupper(trim((string) ($checkoutData['promo_code'] ?? '')));
if ($currentPromoCode !== '' && !isset($promoOptions[$currentPromoCode])) {
  $promoOptions = [
    $currentPromoCode => [
      'label' => (string) ($checkoutData['promo_label'] ?? $currentPromoCode),
      'type' => (string) ($checkoutData['reduction_type'] ?? 'percent'),
      'value' => (float) ($checkoutData['reduction_value'] ?? 0),
    ],
  ] + $promoOptions;
}

$pageFeedbackScript = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dataevent) {
  $pageAction = (string) ($_POST['page_action'] ?? '');

  if ($pageAction === 'update_promo_code') {
    try {
      EventOrderService::updateCheckoutPromoCode($pdo, (int) $codget, (string) ($_POST['promo_code'] ?? ''));
      $checkoutData = EventOrderService::loadCheckoutByEvent($pdo, (int) ($codget ?: 0));
      $paymentTypeLabel = EventOrderService::paymentLabel($checkoutData['type_paiement'] ?? null);
      $invoiceSummary = EventOrderService::buildInvoiceSummaryForEvent($pdo, (int) ($codget ?: 0));
      $invoiceLines = $invoiceSummary['lines'] ?? [];
      $invoiceCurrency = (string) ($invoiceSummary['currency'] ?? ($checkoutData['devise'] ?? 'USD'));
      $currentPromoCode = strtoupper(trim((string) ($checkoutData['promo_code'] ?? '')));

      $pageFeedbackScript = '<script>
        Swal.fire({
          title:"Code promo",
          text:"Le code promo a ete mis a jour.",
          icon:"success",
          confirmButtonText:"OK"
        });
      </script>';
    } catch (RuntimeException $exception) {
      $pageFeedbackScript = '<script>
        Swal.fire({
          title:"Code promo",
          text:' . json_encode($exception->getMessage()) . ',
          icon:"warning",
          confirmButtonText:"OK"
        });
      </script>';
    }
  } elseif ($pageAction === 'prepare_invoice') {
    $generatedLines = EventOrderService::replaceDetailsFactForEvent($pdo, (int) $codget);

    if ($generatedLines !== []) {
      $pageFeedbackScript = '<script>
        Swal.fire({
          title:' . json_encode($documentLabel) . ',
          text:"Le ' . ($documentMode === 'devis' ? 'devis' : 'document de facturation') . ' a ete prepare automatiquement.",
          icon:"success",
          confirmButtonText:"Suivant >"
        }).then((r)=>{ if(r.isConfirmed){ window.location.href="index.php?page=paiement_fin&cod=' . htmlspecialchars($codget, ENT_QUOTES, 'UTF-8') . '&mode=' . rawurlencode($documentMode) . '"; }});
      </script>';
    } else {
      $pageFeedbackScript = '<script>
        Swal.fire({ title:"Information", text:"Aucune ligne valide a enregistrer.", icon:"info", confirmButtonText:"OK" });
      </script>';
    }
  }
}
?>

<style>
.order-checkout-meta{display:grid;gap:12px;margin:14px 0 22px}
.order-checkout-card{padding:16px 18px;border:1px solid rgba(148,163,184,.18);border-radius:18px;background:#f8fafc}
.order-checkout-line{display:flex;justify-content:space-between;gap:14px;padding-top:10px;border-top:1px dashed rgba(148,163,184,.24)}
.order-checkout-line:first-child{padding-top:0;border-top:0}
.order-checkout-inline-action{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
.order-promo-trigger{border:1px solid rgba(37,99,235,.18);background:#fff;color:#1d4ed8;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer}
.order-promo-trigger:hover{background:#eff6ff}
.invoice-preview{display:grid;gap:12px;margin:18px 0 24px}
.invoice-preview-card{padding:18px;border:1px solid rgba(148,163,184,.18);border-radius:18px;background:#fff}
.invoice-preview-line{display:flex;justify-content:space-between;gap:14px;padding-top:10px;border-top:1px dashed rgba(148,163,184,.24)}
.invoice-preview-line:first-child{padding-top:0;border-top:0}
.invoice-preview-line small{display:block;margin-top:4px;color:#64748b}
.invoice-preview-summary{padding:16px 18px;border-radius:18px;background:#0f172a;color:#fff}
.invoice-preview-summary-line{display:flex;justify-content:space-between;gap:14px;padding-top:10px;border-top:1px solid rgba(255,255,255,.15)}
.invoice-preview-summary-line:first-child{padding-top:0;border-top:0}
.invoice-preview-summary-line strong{font-weight:800}
.promo-modal-note{margin:10px 0 0;color:#64748b;font-size:12px;line-height:1.5}
</style>

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
            <div class="order-checkout-meta">
              <div class="order-checkout-card">
                <div class="order-checkout-line"><span>Type de paiement choisi</span><strong><?= htmlspecialchars($paymentTypeLabel, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="order-checkout-line">
                  <span>Code promo</span>
                  <div class="order-checkout-inline-action">
                    <strong><?= htmlspecialchars((string) ($checkoutData['promo_code'] ?? 'Aucun'), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <button
                      type="button"
                      class="order-promo-trigger"
                      data-promo-swal="1"
                      data-current-promo="<?= htmlspecialchars((string) $currentPromoCode, ENT_QUOTES, 'UTF-8'); ?>"
                      data-promo-options="<?= htmlspecialchars((string) json_encode($promoOptions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>">
                      <?= !empty($checkoutData['promo_code']) ? 'Editer' : 'Ajouter'; ?>
                    </button>
                  </div>
                </div>
                <div class="order-checkout-line"><span>Sous-total</span><strong><?= htmlspecialchars(number_format((float) ($invoiceSummary['subtotal'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="order-checkout-line"><span>Remise</span><strong><?= htmlspecialchars(number_format((float) ($invoiceSummary['discount_amount'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="order-checkout-line"><span>Total a payer</span><strong><?= htmlspecialchars(number_format((float) ($invoiceSummary['total'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
              </div>
            </div>
            <!-- ===== FIN du tableau ===== -->
            <form id="promoCodeForm" method="post" action="" style="display:none;">
              <input type="hidden" name="page_action" value="update_promo_code">
              <input type="hidden" name="promo_code" id="promoCodeValue" value="<?= htmlspecialchars((string) $currentPromoCode, ENT_QUOTES, 'UTF-8'); ?>">
            </form>

            <?= $pageFeedbackScript ?>

            <?php if ($dataevent): ?>
            <div class="invoice-preview">
              <div class="invoice-preview-card">
                <?php if ($invoiceLines !== []) { ?>
                  <?php foreach ($invoiceLines as $invoiceLine) { ?>
                  <div class="invoice-preview-line">
                    <div>
                      <strong><?= htmlspecialchars((string) ($invoiceLine['label'] ?? 'Ligne'), ENT_QUOTES, 'UTF-8'); ?></strong>
                      <small>Quantité: <?= (int) ($invoiceLine['quantity'] ?? 1); ?> x <?= htmlspecialchars(number_format((float) ($invoiceLine['unit_price'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></small>
                    </div>
                    <div><?= htmlspecialchars(number_format((float) ($invoiceLine['line_total'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <?php } ?>
                <?php } else { ?>
                  <div class="invoice-preview-line">
                    <div>Aucune ligne tarifaire disponible pour cette commande.</div>
                  </div>
                <?php } ?>
              </div>

              <div class="invoice-preview-summary">
                <div class="invoice-preview-summary-line"><span>Sous-total</span><strong><?= htmlspecialchars(number_format((float) ($invoiceSummary['subtotal'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="invoice-preview-summary-line"><span>Remise promo</span><strong>- <?= htmlspecialchars(number_format((float) ($invoiceSummary['discount_amount'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="invoice-preview-summary-line"><span>Total automatique</span><strong><?= htmlspecialchars(number_format((float) ($invoiceSummary['total'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
              </div>
            </div>

            <form id="eventForm" action="" method="post">
              <input type="hidden" name="page_action" value="prepare_invoice">
              <div class="row">
                <div class="col-12 text-center">
                  <button type="submit" id="BtnEvent" class="btn btn-primary w-p100 mt-10">
                    <?= $documentMode === 'devis' ? 'Generer automatiquement le devis' : 'Confirmer la facture'; ?>
                  </button>
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
document.addEventListener('DOMContentLoaded', function () {
  const promoTrigger = document.querySelector('[data-promo-swal="1"]');
  const promoForm = document.getElementById('promoCodeForm');
  const promoValueInput = document.getElementById('promoCodeValue');

  if (!promoTrigger || !promoForm || !promoValueInput || typeof Swal === 'undefined') {
    return;
  }

  let promoOptions = {};
  try {
    promoOptions = JSON.parse(promoTrigger.getAttribute('data-promo-options') || '{}');
  } catch (error) {
    promoOptions = {};
  }

  const inputOptions = { '': 'Aucun code promo' };
  Object.keys(promoOptions).forEach(function (promoCode) {
    const definition = promoOptions[promoCode] || {};
    const label = definition.label || promoCode;
    const value = Number(definition.value || 0).toFixed(2);
    inputOptions[promoCode] = promoCode + ' - ' + label + ' (' + value + '%)';
  });

  promoTrigger.addEventListener('click', function (event) {
    event.preventDefault();

    Swal.fire({
      title: 'Code promo du client',
      input: 'select',
      inputOptions: inputOptions,
      inputValue: promoTrigger.getAttribute('data-current-promo') || '',
      inputPlaceholder: 'Selectionner un code promo',
      showCancelButton: true,
      confirmButtonText: 'Enregistrer',
      cancelButtonText: 'Annuler'
    }).then(function (result) {
      if (!result.isConfirmed) {
        return;
      }

      promoValueInput.value = result.value || '';
      promoForm.submit();
    });
  });
});

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
