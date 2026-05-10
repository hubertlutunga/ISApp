<?php
$wedSectionEnabled = $wedSectionEnabled ?? static function ($section) { return true; };
$wedText = $wedText ?? static function ($field, $fallback = '') { return $fallback; };
$wedE = $wedE ?? static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };

$giftItemsText = $wedText('gift_items', "Participation à la réception\nDécoration du foyer\nVoyage de noces");
$giftItems = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $giftItemsText))));

$selectedGuestConfirmationIds = array_values(array_filter(array_map('intval', preg_split('/[\s,;]+/', $wedText('guest_confirmation_ids', '')))));
$guestMessages = [];
if (!empty($selectedGuestConfirmationIds) && isset($pdo, $codevent)) {
    try {
        $placeholders = implode(',', array_fill(0, count($selectedGuestConfirmationIds), '?'));
        $guestMessageStmt = $pdo->prepare("SELECT cod_conf, noms, note, presence, date_enreg FROM confirmation WHERE cod_mar = ? AND cod_conf IN ($placeholders) AND note IS NOT NULL AND TRIM(note) <> '' ORDER BY date_enreg DESC");
        $guestMessageStmt->execute(array_merge([(int) $codevent], $selectedGuestConfirmationIds));
        $guestMessages = $guestMessageStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $guestMessageStmt->closeCursor();
    } catch (Throwable $exception) {
        $guestMessages = [];
    }
}
?>
<?php if ($wedSectionEnabled('gift')) { ?>
<section id="gift" class="bg-primary">
    <div class="container spacer-double-lg">
        <div class="row justify-content-between z-index-3 position-relative align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <h1 class="display-4 text-white"><?php echo $wedE($wedText('gift_title', 'Cadeaux')); ?></h1>
                <p class="lead text-white mb-0"><?php echo nl2br($wedE($wedText('gift_text', 'Votre présence est le plus beau des cadeaux.'))); ?></p>
            </div>
            <div class="col-md-5">
                <?php if (!empty($giftItems)) { ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($giftItems as $giftItem) { ?>
                    <li class="d-flex align-items-start mb-3 text-white">
                        <span class="mr-3" style="display:inline-flex;align-items:center;justify-content:center;min-width:34px;width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.18);font-weight:900;line-height:1;">✓</span>
                        <span class="lead mb-0" style="line-height:1.45;"><?php echo $wedE($giftItem); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } else { ?>
                <p class="lead text-white mb-0">La liste des besoins sera affichée ici.</p>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="curved-decoration z-index-3">
        <svg width="100%" height="100%" class="bg-white-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2560 168.6227" xml:space="preserve">
            <g><path d="M0,0c0,0,219.6543,165.951,730.788,124.0771c383.3156-31.4028,827.2139-96.9514,1244.7139-96.9514c212.5106,0,438.9999,3.5,584.4982,1.5844v139.9126H0V0z"></path></g>
        </svg>
    </div>
</section>
<?php } ?>

<?php if ($wedSectionEnabled('friends')) { ?>
<section id="friends" class="text-center">
    <div class="container spacer-double-lg">
        <div class="row justify-content-center">
            <div class="col">
                <div class="mb-5 pb-5">
                    <h1 class="display-4 mb-0"><?php echo $wedE($wedText('friends_title', 'Nos invités')); ?></h1>
                    <p class="w-md-40 mb-0 mx-auto text-dark-gray opacity-8"><?php echo $wedE($wedText('friends_subtitle', 'Quelques mots laissés lors des confirmations')); ?></p>
                </div>
            </div>
        </div>
        <?php if (!empty($guestMessages)) { ?>
        <div class="row justify-content-center">
            <?php foreach ($guestMessages as $guestMessage) { ?>
            <div class="col-md-4 mb-4 d-flex">
                <div class="w-100 bg-white" style="border-radius:24px;padding:28px;box-shadow:0 18px 40px rgba(15,23,42,.08);">
                    <div class="mx-auto icon-round mb-4 mb-md-4 bg-icon-primary" style="width:58px;height:58px;">
                        <span style="font-size:28px;line-height:58px;color:#E25D5D;">“</span>
                    </div>
                    <blockquote style="font-size:17px;line-height:1.7;"><?php echo nl2br($wedE((string) ($guestMessage['note'] ?? ''))); ?></blockquote>
                    <small class="text-uppercase font-weight-600 text-dark upper-letter-space"><?php echo $wedE((string) ($guestMessage['noms'] ?? 'Invité')); ?></small>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="row justify-content-center">
            <div class="col-md-7">
                <p class="text-dark-gray opacity-8 mb-0"><?php echo $wedE($wedText('guest_empty_text', 'Sélectionnez les confirmations à afficher depuis votre espace de personnalisation.')); ?></p>
            </div>
        </div>
        <?php } ?>
    </div>
    <div class="curved-decoration">
        <svg width="100%" height="100%" class="bg-secondary-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2560 168.6227" xml:space="preserve">
            <g><path d="M2560,0c0,0-219.6543,165.951-730.788,124.0771c-383.3156-31.4028-827.2138-96.9514-1244.7139-96.9514c-212.5106,0-439,3.5-584.4982,1.5844l0,139.9126h2560V0z"></path></g>
        </svg>
    </div>
</section>
<?php } ?>
