

<div class="wrapper"> 

     <?php 
     
           include('header_admin.php');
       
           include('../../qrscan/phpqrcode/qrlib.php');

           $heure = date('H');
           if ($heure < 12) {
               $salut = 'Bonjour';
           } elseif ($heure < 18) {
               $salut = 'Bon Après-midi';
           } else {
               $salut = 'Bonsoir';
           }

           $codget = isset($_GET['cod']) ? (int) $_GET['cod'] : 0;
           $documentMode = (isset($_GET['mode']) && $_GET['mode'] === 'devis') ? 'devis' : 'facture';
           $isDevisMode = $documentMode === 'devis';
           $documentLabel = $isDevisMode ? 'Devis' : 'Paiement';
           $pageFeedbackScript = '';
       
   ?>

     <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
         <div class="container-full">
   <div class="row salut">

   <p style="text-align:center;">
     <?php echo $salut; ?> <b><?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, 'UTF-8'); ?></b>!
   </p>

    <style>
    .order-checkout-meta{display:grid;gap:12px;margin:14px 0 22px}
    .order-checkout-card{padding:16px 18px;border:1px solid rgba(148,163,184,.18);border-radius:18px;background:#f8fafc}
    .order-checkout-line{display:flex;justify-content:space-between;gap:14px;padding-top:10px;border-top:1px dashed rgba(148,163,184,.24)}
    .order-checkout-line:first-child{padding-top:0;border-top:0}
    .order-checkout-inline-action{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .order-promo-trigger{border:1px solid rgba(37,99,235,.18);background:#fff;color:#1d4ed8;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer}
    .order-promo-trigger:hover{background:#eff6ff}
    .payment-finish-shell{display:grid;grid-template-columns:minmax(0,1.12fr) minmax(320px,.88fr);gap:24px;margin-top:18px}
    .payment-panel{border:1px solid rgba(148,163,184,.18);border-radius:24px;background:#fff;box-shadow:0 18px 45px rgba(15,23,42,.08)}
    .payment-panel-body{padding:22px}
    .payment-panel-title{margin:0 0 14px;font-size:20px;font-weight:800;color:#0f172a}
    .payment-hero{padding:22px;border-radius:24px;background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 100%);color:#fff;box-shadow:0 20px 42px rgba(15,23,42,.18);margin-bottom:18px}
    .payment-hero-title{margin:0;font-size:24px;font-weight:800}
    .payment-hero-copy{margin:8px 0 0;color:rgba(255,255,255,.8)}
    .payment-lines{display:grid;gap:10px}
    .payment-line{display:flex;justify-content:space-between;gap:14px;padding:12px 0;border-top:1px dashed rgba(148,163,184,.24)}
    .payment-line:first-child{border-top:0;padding-top:0}
    .payment-line small{display:block;margin-top:4px;color:#64748b}
    .payment-totals{display:grid;gap:10px;margin-top:18px}
    .payment-total-line{display:flex;justify-content:space-between;gap:14px;padding:14px 16px;border-radius:18px;background:#f8fafc;color:#0f172a}
    .payment-total-line strong{font-weight:800}
    .payment-total-line.is-highlight{background:#0f172a;color:#fff}
    .payment-total-line.is-success{background:#ecfdf5;color:#166534}
    .payment-total-line.is-danger{background:#fef2f2;color:#b91c1c}
    .payment-form-grid{display:grid;gap:14px}
    .payment-input-shell{border:1px solid #dbeafe;border-radius:18px;background:#f8fbff;overflow:hidden}
    .payment-input-shell .input-group-text,.payment-input-shell .form-control{border:0 !important;background:transparent !important;box-shadow:none !important;min-height:56px}
    .payment-input-shell .input-group-text{color:#2563eb;padding-left:16px;padding-right:10px}
    .payment-choice-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .payment-choice{position:relative}
    .payment-choice input{position:absolute;opacity:0;pointer-events:none}
    .payment-choice label{display:flex;align-items:center;justify-content:center;min-height:54px;border-radius:18px;border:1px solid #dbeafe;background:#f8fbff;font-weight:700;color:#0f172a;cursor:pointer;transition:border-color .16s ease,box-shadow .16s ease,transform .16s ease}
    .payment-choice input:checked + label{border-color:#2563eb;background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);box-shadow:0 14px 28px rgba(37,99,235,.14);transform:translateY(-1px)}
    .promo-modal-note{margin:10px 0 0;color:#64748b;font-size:12px;line-height:1.5}
    @media (max-width: 991px){.payment-finish-shell{grid-template-columns:1fr}}
    </style>

   <?php
                             $stmt = $pdo->prepare("SELECT * FROM events where cod_event = :cod_event");
                             $stmt->execute(['cod_event' => $codget]);

                             while ($dataevent = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                     if (!$dataevent) {

                                         $codevent = '';
                                         $date_event = '';
                                         $type_event = '';
                                         $display = 'none';

                                     } else {

                                         $date_event = $dataevent['date_event'];
                                         $type_event = $dataevent['type_event'];
                                         $display = 'block';
                                         $codevent = $dataevent['cod_event'];

                                     }

                                    $paymentMeta = EventBackofficeService::resolvePaymentMeta($pdo, ['cod_event' => $codevent]);
                                    $invitationModels = EventOrderService::loadInvitationModelsByEvent($pdo, (int) $codevent);
                                    $checkoutData = EventOrderService::loadCheckoutByEvent($pdo, (int) $codevent);
                                    $invoiceSummary = EventOrderService::buildInvoiceSummaryForEvent($pdo, (int) $codevent);
                                    $invoiceLines = $invoiceSummary['lines'] ?? [];
                                    $paymentTypeLabel = EventOrderService::paymentLabel($checkoutData['type_paiement'] ?? null);
                                    $currentPaid = (float) $paymentMeta['paid'];
                                    $currentRemaining = max((float) ($invoiceSummary['total'] ?? 0) - $currentPaid, 0.0);
                                    $currentInstruction = (string) ($dataevent['instruction'] ?? '');
                                    $currentDeliveryDate = (string) ($dataevent['date_livraison'] ?? '');
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

                                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ((string) ($_POST['page_action'] ?? '')) === 'update_promo_code') {
                                        try {
                                            EventOrderService::updateCheckoutPromoCode($pdo, (int) $codevent, (string) ($_POST['promo_code'] ?? ''));
                                            $checkoutData = EventOrderService::loadCheckoutByEvent($pdo, (int) $codevent);
                                            $invoiceSummary = EventOrderService::buildInvoiceSummaryForEvent($pdo, (int) $codevent);
                                            $invoiceLines = $invoiceSummary['lines'] ?? [];
                                            $paymentTypeLabel = EventOrderService::paymentLabel($checkoutData['type_paiement'] ?? null);
                                            $currentRemaining = max((float) ($invoiceSummary['total'] ?? 0) - $currentPaid, 0.0);
                                            $currentPromoCode = strtoupper(trim((string) ($checkoutData['promo_code'] ?? '')));
                                            $pageFeedbackScript = '<script>Swal.fire({title:"Code promo",text:"Le code promo a ete mis a jour.",icon:"success",confirmButtonText:"OK"});</script>';
                                        } catch (RuntimeException $exception) {
                                            $pageFeedbackScript = '<script>Swal.fire({title:"Code promo",text:' . json_encode($exception->getMessage()) . ',icon:"warning",confirmButtonText:"OK"});</script>';
                                        }
                                    }

                                     $stmtnv = $pdo->prepare("SELECT * FROM evenement WHERE cod_event = ?");
                                     $stmtnv->execute([$type_event]);
                                     $data_evenement = $stmtnv->fetch(PDO::FETCH_ASSOC) ?: [];

                                     $data_evenement = (string) ($data_evenement['nom'] ?? '');

                                     if ($type_event == "1") {
                                         $typeevent = 'Mariage ' . ($dataevent['type_mar'] ?? 'Inconnu');
                                         $displayvue = 'display:block;';
                                         $fetard = (($dataevent['prenom_epouse'] ?? '') . ' & ' . ($dataevent['prenom_epoux'] ?? '')) ?: 'Inconnu';
                                     } elseif ($type_event == "2" || $type_event == "3") {
                                         $typeevent = $data_evenement;
                                         $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
                                         $displayvue = 'display:none;';
                                     }

                                     $dateStr = $date_event;

                                     if ($dateStr !== null && $dateStr !== '') {
                                         $date = new DateTime($dateStr);
                                     } else {
                                         $date = new DateTime();
                                     }

                                     $formatter = new IntlDateFormatter(
                                         'fr_FR',
                                         IntlDateFormatter::LONG,
                                         IntlDateFormatter::NONE,
                                         null,
                                         IntlDateFormatter::GREGORIAN,
                                         'EEEE, dd/MM/yyyy à HH:mm'
                                     );

                                     $formatted_date = ucfirst((string) $formatter->format($date));
                             ?>
 
 
 
 
                                     <tr>
 
                                         <td class="pt-0 px-0" style="padding-left:10px;border-botton:1px solid #000;background-color:DCF4F7;"><br>
                                          
                                         
                                         
            <div class="row">
                <div class="col-12">
                                                                                                 <?php echo $pageFeedbackScript; ?>

                                                                                                 <div class="payment-hero">
                                                                                                         <h3 class="payment-hero-title"><?php echo htmlspecialchars($documentLabel, ENT_QUOTES, 'UTF-8'); ?> de la commande #<?php echo htmlspecialchars((string) $codevent, ENT_QUOTES, 'UTF-8'); ?></h3>
                                                                                                         <p class="payment-hero-copy">Ajustez le code promo si besoin, controlez les lignes de facturation et confirmez le document avec un affichage plus propre.</p>
                                                                                                 </div>

                                                                                                 <div class="order-checkout-meta">
                                                                                                         <div class="order-checkout-card">
                                                                                                                 <div class="order-checkout-line"><span>Type de paiement choisi</span><strong><?php echo htmlspecialchars($paymentTypeLabel, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                                                                                 <div class="order-checkout-line">
                                                                                                                         <span>Code promo</span>
                                                                                                                         <div class="order-checkout-inline-action">
                                                                                                                                 <strong><?php echo htmlspecialchars((string) ($checkoutData['promo_code'] ?? 'Aucun'), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                                                                                                 <button
                                                                                                                                     type="button"
                                                                                                                                     class="order-promo-trigger"
                                                                                                                                     data-promo-swal="1"
                                                                                                                                     data-current-promo="<?php echo htmlspecialchars((string) $currentPromoCode, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                                                                     data-promo-options="<?php echo htmlspecialchars((string) json_encode($promoOptions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>"
                                                                                                                                 ><?php echo !empty($checkoutData['promo_code']) ? 'Editer' : 'Ajouter'; ?></button>
                                                                                                                         </div>
                                                                                                                 </div>
                                                                                                                 <div class="order-checkout-line"><span>Sous-total</span><strong><?php echo htmlspecialchars(number_format((float) ($invoiceSummary['subtotal'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars((string) ($invoiceSummary['currency'] ?? 'USD'), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                                                                                 <div class="order-checkout-line"><span>Remise</span><strong><?php echo htmlspecialchars(number_format((float) ($invoiceSummary['discount_amount'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars((string) ($invoiceSummary['currency'] ?? 'USD'), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                                                                                 <div class="order-checkout-line"><span>Total a payer</span><strong><?php echo htmlspecialchars(number_format((float) ($invoiceSummary['total'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars((string) ($invoiceSummary['currency'] ?? 'USD'), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                                                                         </div>
                                                                                                 </div>

                                                                                                 <form id="promoCodeForm" method="post" action="" style="display:none;">
                                                                                                     <input type="hidden" name="page_action" value="update_promo_code">
                                                                                                     <input type="hidden" name="promo_code" id="promoCodeValue" value="<?php echo htmlspecialchars((string) $currentPromoCode, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                                 </form>

<?php 
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ((string) ($_POST['page_action'] ?? 'process_payment')) === 'process_payment') {  
    $invoiceSummary = EventOrderService::buildInvoiceSummaryForEvent($pdo, (int) $codget);
    $montant_total = (float) ($invoiceSummary['total'] ?? 0.0);

    $instruction = trim((string) ($_POST['observation'] ?? '')); 
    $dateliv = trim((string) ($_POST['dateliv'] ?? '')); 
    $type_paie = (string) ($_POST['type_paie'] ?? 'solde');  
    $paymentSubmitMode = (string) ($_POST['payment_submit_mode'] ?? '');
    $user = $dataevent['cod_user'];  
    $alreadyPaid = (float) ($currentPaid ?? 0.0);
    $remainingBefore = max($montant_total - $alreadyPaid, 0.0);
    $paymentStep = 0.0;

    $currentInstruction = $instruction;
    $currentDeliveryDate = $dateliv;

    if ($isDevisMode) {
        if ($montant_total <= 0) {
            echo '<script>Swal.fire({title:"Devis",text:"Aucun montant de commande n\'est disponible.",icon:"warning",confirmButtonText:"OK"});</script>';
        } elseif (!$dateliv) {
            echo 'Determiner la date de livraison';
        } else {
            try {
                $pdo->beginTransaction();

                $deleteDevis = $pdo->prepare("DELETE FROM facture WHERE reference = ? AND type_fact = 'Devis'");
                $deleteDevis->execute([$codget]);

                $insertDevis = $pdo->prepare(
                    "INSERT INTO facture (type_fact, reference, cod_cli, type_paie, montant_total, montant_paye, devise, date_enreg) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
                );
                $insertDevis->execute([
                    'Devis',
                    $codget,
                    $user,
                    'devis',
                    $montant_total,
                    0,
                    'USD',
                ]);

                $q = $pdo->prepare("UPDATE events SET instruction = :instruction, date_livraison = :date_livraison WHERE cod_event = :codevent");
                $q->bindValue(':instruction', $instruction);
                $q->bindValue(':date_livraison', $dateliv);
                $q->bindValue(':codevent', $codget);
                $q->execute();
                $q->closeCursor();

                $pdo->commit();

                echo '<script>
                Swal.fire({
                    title: "Devis",
                    text: "Le devis a été généré avec succès.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "pages/pdf/facture_hs.php?cod=' . rawurlencode((string) $codget) . '&type=devis";
                    }
                });
                </script>';
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                echo "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    } elseif ($montant_total <= 0) {
        echo '<script>Swal.fire({title:"Paiement",text:"Aucun montant de commande n\'est disponible.",icon:"warning",confirmButtonText:"OK"});</script>';
    } elseif ($remainingBefore <= 0) {
        echo '<script>Swal.fire({title:"Paiement",text:"Cette commande est déjà totalement réglée.",icon:"info",confirmButtonText:"OK"});</script>';
    } elseif (!$dateliv) {
        echo 'Determiner la date de livraison';
    } else {
        if ($type_paie === 'acompte') {
            $paymentStep = (float) str_replace(',', '.', (string) ($_POST['acompte'] ?? '0'));

            if ($paymentStep <= 0) {
                echo '<script>Swal.fire({title:"Paiement",text:"Le montant de l\'acompte doit être supérieur à 0.",icon:"warning",confirmButtonText:"OK"});</script>';
                $paymentStep = -1.0;
            } elseif ($paymentStep > $remainingBefore) {
                echo '<script>Swal.fire({title:"Paiement",text:"Le montant saisi dépasse le reste à encaisser.",icon:"warning",confirmButtonText:"OK"});</script>';
                $paymentStep = -1.0;
            }
        } else {
            $paymentStep = $remainingBefore;
        }

        if ($paymentStep > 0) {
            $newPaidAmount = min($alreadyPaid + $paymentStep, $montant_total);
            $effectiveType = $newPaidAmount < $montant_total ? 'acompte' : 'solde';
            $isFullyPaidNow = $newPaidAmount >= $montant_total;
            $shouldRedirectToFactures = $paymentSubmitMode !== 'devis';

            try {
                $pdo->beginTransaction();

                $insertFacture = $pdo->prepare(
                    "INSERT INTO facture (type_fact, reference, cod_cli, type_paie, montant_total, montant_paye, devise, date_enreg) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
                );
                $insertFacture->execute([
                    'Facture',
                    $codget,
                    $user,
                    $effectiveType,
                    $montant_total,
                    $paymentStep,
                    'USD',
                ]);

                $q = $pdo->prepare("UPDATE events SET fact = :fact, instruction = :instruction, date_livraison = :date_livraison WHERE cod_event = :codevent"); 
                $q->bindValue(':fact', 'oui'); 
                $q->bindValue(':instruction', $instruction); 
                $q->bindValue(':date_livraison', $dateliv);  
                $q->bindValue(':codevent', $codget);  
                $q->execute();
                $q->closeCursor(); 

                $pdo->commit();

                $successText = $isFullyPaidNow
                    ? 'Le paiement a été soldé avec succès. La facture globale est à jour.'
                    : 'Le paiement a été enregistré. L\'historique des encaissements est conservé.';
                $redirectUrl = $shouldRedirectToFactures
                    ? 'index.php?page=factures'
                    : ($isFullyPaidNow
                        ? 'pages/pdf/facture_hs.php?cod=' . rawurlencode((string) $codget)
                        : 'index.php?page=paiement_fin&cod=' . rawurlencode((string) $codget));

                echo '<script>
                Swal.fire({
                    title: "Paiement",
                    text: ' . json_encode($successText) . ',
                    icon: "success",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = ' . json_encode($redirectUrl) . ';
                    }
                });
                </script>';
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                echo "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    }



}
}

?>






<form id="eventForm" action="" method="post" enctype="multipart/form-data">

<input type="hidden" name="page_action" value="process_payment">

 
<?php
$total = 0; // Initialisation de la variable pour stocker le montant total
$invoiceSummary = EventOrderService::buildInvoiceSummaryForEvent($pdo, (int) $codget);
$invoiceCurrency = (string) ($invoiceSummary['currency'] ?? 'USD');

$stmtdf = $pdo->prepare("SELECT * FROM details_fact WHERE cod_event = ?");
$stmtdf->execute([$codget]); // Remplacez par $codevent si nécessaire

while ($data_fact = $stmtdf->fetch()) {
    $total += $data_fact['pt'];
}
?>

<div class="payment-finish-shell">
    <div class="payment-panel">
        <div class="payment-panel-body">
            <h3 class="payment-panel-title">Lignes de facturation</h3>
            <div class="payment-lines">
                <?php if ($invoiceLines !== []) { ?>
                    <?php foreach ($invoiceLines as $invoiceLine) { ?>
                        <div class="payment-line">
                            <div>
                                <strong><?php echo htmlspecialchars((string) ($invoiceLine['label'] ?? 'Ligne'), ENT_QUOTES, 'UTF-8'); ?></strong>
                                <small>Quantite : <?php echo (int) ($invoiceLine['quantity'] ?? 1); ?> x <?php echo htmlspecialchars(number_format((float) ($invoiceLine['unit_price'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <div><?php echo htmlspecialchars(number_format((float) ($invoiceLine['line_total'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="payment-line"><div>Aucune ligne tarifaire disponible.</div></div>
                <?php } ?>
            </div>

            <div class="payment-totals">
                <div class="payment-total-line"><span>Sous-total</span><strong><?php echo htmlspecialchars(number_format((float) ($invoiceSummary['subtotal'] ?? $total), 2, ',', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="payment-total-line"><span>Remise</span><strong><?php echo htmlspecialchars(number_format((float) ($invoiceSummary['discount_amount'] ?? 0), 2, ',', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="payment-total-line is-highlight"><span>Total a payer</span><strong><?php echo htmlspecialchars(number_format((float) ($invoiceSummary['total'] ?? $total), 2, ',', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <?php if (!$isDevisMode) { ?>
                    <div class="payment-total-line is-success"><span>Deja encaisse</span><strong><?php echo htmlspecialchars(number_format($currentPaid, 2, ',', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                    <div class="payment-total-line is-danger"><span>Reste a encaisser</span><strong><?php echo htmlspecialchars(number_format(max(((float) ($invoiceSummary['total'] ?? $total)) - $currentPaid, 0), 2, ',', ' '), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($invoiceCurrency, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="payment-panel">
        <div class="payment-panel-body">
            <h3 class="payment-panel-title"><?php echo $isDevisMode ? 'Finaliser le devis' : 'Confirmer le paiement'; ?></h3>
            <div class="payment-form-grid">
                <div class="payment-input-shell">
                    <div class="input-group mb-0">
                        <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span>
                        <textarea name="observation" class="form-control ps-15 bg-transparent" rows='5' placeholder="Observation"><?php echo htmlspecialchars($currentInstruction, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                </div>

                <div class="payment-input-shell">
                    <div class="input-group mb-0">
                        <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
                        <input type="date" name="dateliv" class="form-control ps-15 bg-transparent" placeholder="Date de livraison" value="<?php echo htmlspecialchars($currentDeliveryDate, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <?php if (!$isDevisMode) { ?>
                <div class="payment-choice-row">
                    <div class="payment-choice">
                        <input name="type_paie" type="radio" id="solde" value="solde" checked onchange="toggleAcompte()" />
                        <label for="solde">Solde</label>
                    </div>
                    <div class="payment-choice">
                        <input name="type_paie" type="radio" id="acompte" value="acompte" onchange="toggleAcompte()" />
                        <label for="acompte">Acompte</label>
                    </div>
                </div>

                <div class="payment-input-shell" id="acompteField" style="display: none;">
                    <div class="input-group mb-0">
                        <span class="input-group-text bg-transparent"><i class="fas fa-dollar-sign"></i></span>
                        <input type="text" name="acompte" class="form-control ps-15 bg-transparent" placeholder="Montant de l'acompte">
                    </div>
                </div>

<script>
function toggleAcompte() {
    const acompteField = document.getElementById('acompteField');
    const isAcompteChecked = document.getElementById('acompte').checked;

    if (isAcompteChecked) {
        acompteField.style.display = 'block';
    } else {
        acompteField.style.display = 'none';
    }
}

// Initialiser l'état à l'ouverture
toggleAcompte();

document.addEventListener('DOMContentLoaded', function () {
    var promoTrigger = document.querySelector('[data-promo-swal="1"]');
    var promoForm = document.getElementById('promoCodeForm');
    var promoValueInput = document.getElementById('promoCodeValue');

    if (!promoTrigger || !promoForm || !promoValueInput || typeof Swal === 'undefined') {
        return;
    }

    var promoOptions = {};
    try {
        promoOptions = JSON.parse(promoTrigger.getAttribute('data-promo-options') || '{}');
    } catch (error) {
        promoOptions = {};
    }

    var inputOptions = { '': 'Aucun code promo' };
    Object.keys(promoOptions).forEach(function (promoCode) {
        var definition = promoOptions[promoCode] || {};
        var label = definition.label || promoCode;
        var value = Number(definition.value || 0).toFixed(2);
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
</script>
                <?php } ?>

                <div class="row">
                    <div class="col-12 text-center">
                        <input type="hidden" name="payment_submit_mode" value="<?php echo $isDevisMode ? 'devis' : ($currentPaid > 0 ? 'encaisser' : 'terminer'); ?>">
                        <button type="submit" id="BtnEvent" class="btn btn-primary w-p100 mt-10"><?php echo $isDevisMode ? 'Generer le devis' : ($currentPaid > 0 ? 'Encaisser' : 'Terminer'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
                    
         </div>

 
 
 
         </div>
 
 
 
  

                                      












                                    </td> 
                                        
                                       
                                     </tr>
 
                                 
                                    
   
                           </tbody>
                       </table>
                   </div>
               </div>	
   
   
   
   
   
   
   
   
   
           </div>
       </div>
   
   
   
   
        
   
   
   
    
   
   
   
   
   
   
  </section>
   
   
   
   
   
   
   
   
   
   
   
   
     
             </div>
   
             
           </div> 
         <!-- /.content -->
       </div>
     <!-- /.content-wrapper -->
     <?php include('footer.php')?> 

   </div>
   <!-- ./wrapper -->
     
     
        
     
     <!-- Page Content overlay -->
     
     
     <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
     <!-- Vendor JS -->
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
   
     
     <!-- Cartiy Admin App -->
     <script src="html/template/horizontal/src/js/demo.js"></script>
     <script src="html/template/horizontal/src/js/jquery.smartmenus.js"></script>
     <script src="html/template/horizontal/src/js/menus.js"></script>
     <script src="html/template/horizontal/src/js/template.js"></script>
     <script src="html/template/horizontal/src/js/pages/dashboard.js"></script>
     <script src="html/template/horizontal/src/js/pages/slider.js"></script>
   
     
     <!-- Vendor JS --> 
     <script src="html/assets/vendor_components/full-calendar/moment.js"></script>
     <script src="html/assets/vendor_components/full-calendar/fullcalendar.min.js"></script> 
   
     
     
     <!-- selecter JS --> 
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
       