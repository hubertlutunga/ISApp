<?php

$home2SourcePath = dirname(__DIR__) . '/new template/ej_mariage_site_v2 2.html';
$home2Html = is_readable($home2SourcePath) ? (string) file_get_contents($home2SourcePath) : '';

$event = is_array($dataevent ?? null) ? $dataevent : [];
$settings = is_array($weddingSiteSettings ?? null) ? $weddingSiteSettings : WeddingWebsiteSettingsService::defaults($event);

$home2E = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};
$home2Upper = static function (string $value): string {
    return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
};
$home2Text = static function (string $field, string $fallback = '') use ($settings): string {
    return WeddingWebsiteSettingsService::text($settings, $field, $fallback);
};
$home2MonthName = static function (DateTimeInterface $date, bool $upper = false) use ($home2Upper): string {
    $months = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin',
        7 => 'juillet', 8 => 'août', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];
    $month = $months[(int) $date->format('n')] ?? '';
    return $upper ? $home2Upper($month) : $month;
};
$home2DayName = static function (DateTimeInterface $date, bool $upper = false) use ($home2Upper): string {
    $days = [
        1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche',
    ];
    $day = $days[(int) $date->format('N')] ?? '';
    return $upper ? $home2Upper($day) : $day;
};

$groom = trim((string) ($event['prenom_epoux'] ?? ''));
$bride = trim((string) ($event['prenom_epouse'] ?? ''));
if (($event['ordrepri'] ?? '') === 'm') {
    $couple = trim($groom . ' & ' . $bride, ' &');
} else {
    $couple = trim($bride . ' & ' . $groom, ' &');
}
if ($couple === '') {
    $couple = $home2Text('hero_title', 'Les mariés');
}

$eventDate = null;
try {
    $rawDate = trim((string) ($date_event ?? $event['date_event'] ?? ''));
    $eventDate = $rawDate !== '' ? new DateTime($rawDate) : null;
} catch (Throwable $exception) {
    $eventDate = null;
}

$eventPlace = trim($home2Text('location_1_title', (string) ($lieu ?? $event['lieu'] ?? '')));
if ($eventPlace === '') {
    $eventPlace = 'Lieu à confirmer';
}
$eventAddress = trim($home2Text('location_1_text', (string) ($event['adresse'] ?? '')));
$eventCity = 'Kinshasa';
if ($eventAddress !== '') {
    $addressParts = array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', $eventAddress))));
    if (!empty($addressParts)) {
        $eventCity = end($addressParts);
    }
}

if ($eventDate instanceof DateTimeInterface) {
    $eventYear = $eventDate->format('Y');
    $eventMonth = $home2MonthName($eventDate);
    $eventMonthTitle = ucfirst($eventMonth);
    $eventDay = (int) $eventDate->format('d');
    $eventTime = $eventDate->format('H\hi');
    $eventDateLong = ucfirst($home2DayName($eventDate)) . ' ' . $eventDay . ' ' . $eventMonth . ' ' . $eventYear;
    $eventDateLongUpper = $home2DayName($eventDate, true) . ' ' . $eventDay . ' ' . $home2MonthName($eventDate, true) . ' ' . $eventYear;
    $eventDateNoDay = $eventDay . ' ' . $eventMonthTitle . ' ' . $eventYear;
    $heroMeta = $eventCity . ' · ' . $eventMonthTitle . ' ' . $eventYear;
    $heroDate = $eventDateLong . ' · ' . $eventTime;
    $galaDateLine = $eventDateLong . ', à partir de ' . $eventTime;
    $countdownTarget = $eventDate->format('Y-m-d\TH:i:sP');
    $rsvpLimitDate = (clone $eventDate)->modify('-15 days');
    $rsvpLimitText = (int) $rsvpLimitDate->format('d') . ' ' . $home2MonthName($rsvpLimitDate) . ' ' . $rsvpLimitDate->format('Y');
} else {
    $eventYear = date('Y');
    $eventTime = '19h00';
    $eventDateLong = 'Date à confirmer';
    $eventDateLongUpper = 'DATE À CONFIRMER';
    $eventDateNoDay = 'Date à confirmer';
    $heroMeta = $eventCity . ' · ' . $eventYear;
    $heroDate = 'Date à confirmer · ' . $eventTime;
    $galaDateLine = 'Date à confirmer, à partir de ' . $eventTime;
    $countdownTarget = date('Y-m-d\TH:i:sP', strtotime('+30 days'));
    $rsvpLimitText = 'la date indiquée';
}

$home2Block = static function (string $value) use ($home2E): string {
    return nl2br($home2E($value), false);
};
$home2Couple = $home2Text('home2_couple_names', $couple);
$home2HeroMeta = $home2Text('home2_hero_meta', $heroMeta);
$home2GalaSubtitle = $home2Text('home2_gala_subtitle', $galaDateLine . ' — ' . $eventPlace . '.');
$home2GalaPrimaryValue = $home2Text('home2_gala_primary_value', $eventPlace);
$home2ProgramEyebrow = $home2Text('home2_program_event_eyebrow', $eventDateLong . ' · Soir');
$home2ProgramTimeValue = $home2Text('home2_program_time_value', $eventTime);
$home2ProgramLocationValue = $home2Text('home2_program_location_value', $eventPlace . ($eventCity !== '' ? "\n" . $eventCity : ''));
$home2MainPlaceText = $home2Text('home2_main_place_text', $eventPlace . ($eventAddress !== '' ? "\n" . $eventAddress : ''));
$home2RsvpSubtitle = $home2Text('home2_rsvp_subtitle', 'Merci de confirmer votre présence avant le <em>' . $home2E($rsvpLimitText) . '</em>. Cette invitation est personnelle et non transmissible.');
$home2FooterMeta = $home2Text('home2_footer_meta', $eventDateNoDay . ' · ' . $eventCity);
$home2BackgroundMusicMarkup = WeddingWebsiteSettingsService::backgroundMusicMarkup($settings, '../couple/audio/');

$home2ValidColor = static function (string $field) use ($settings): string {
    $value = trim((string) ($settings['content'][$field] ?? ''));
    if ($value === '' || !preg_match('/^#(?:[0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $value)) {
        return '';
    }

    return strtoupper($value);
};
$home2SectionBackgroundCss = '';
foreach ([
    'home2_hero_background_color' => '.hero',
    'home2_gala_background_color' => '.s-gala',
    'home2_program_background_color' => '.s-cer',
    'home2_story_background_color' => '.s-love',
    'home2_presence_background_color' => '.s-pres',
    'home2_location_background_color' => '.s-adr',
    'home2_rsvp_background_color' => '.s-rsvp',
    'home2_footer_background_color' => '.footer',
] as $colorField => $sectionSelector) {
    $colorValue = $home2ValidColor($colorField);
    if ($colorValue !== '') {
        $home2SectionBackgroundCss .= $sectionSelector . '{background:' . $colorValue . '!important;background-image:none!important;}' . "\n";
    }
}

$home2SectionTextColorCss = '';
foreach ([
    'home2_hero_text_color' => '.hero',
    'home2_gala_text_color' => '.s-gala',
    'home2_program_text_color' => '.s-cer',
    'home2_story_text_color' => '.s-love',
    'home2_presence_text_color' => '.s-pres',
    'home2_location_text_color' => '.s-adr',
    'home2_rsvp_text_color' => '.s-rsvp',
    'home2_footer_text_color' => '.footer',
] as $colorField => $sectionSelector) {
    $colorValue = $home2ValidColor($colorField);
    if ($colorValue !== '') {
        $home2SectionTextColorCss .= $sectionSelector . ',' . $sectionSelector . ' :where(h1,h2,h3,h4,h5,h6,p,span,a,em,strong,small,li,div,label,input,textarea,button){color:' . $colorValue . '!important;}' . "\n";
        $home2SectionTextColorCss .= $sectionSelector . ' :where(input,textarea)::placeholder{color:' . $colorValue . '!important;opacity:.68;}' . "\n";
    }
}

$home2MapIframe = '';
$home2MapIframeEncoded = trim((string) ($settings['content']['wedding_map_iframe_b64'] ?? ''));
if ($home2MapIframeEncoded !== '') {
    $decodedHome2MapIframe = base64_decode($home2MapIframeEncoded, true);
    if (is_string($decodedHome2MapIframe)) {
        $home2MapIframe = trim($decodedHome2MapIframe);
    }
}
if ($home2MapIframe === '') {
    $home2MapIframe = trim((string) ($settings['content']['wedding_map_iframe'] ?? ''));
}

$home2InviteId = isset($_GET['idinv']) ? (int) $_GET['idinv'] : 0;
$home2Invite = $home2InviteId > 0 ? RsvpService::findInviteById($pdo, $home2InviteId) : null;
$home2InviteName = $home2Invite ? RsvpService::buildInviteDisplayName($home2Invite) : '';
$home2ConfirmationName = $home2InviteName !== '' ? RsvpService::normalizeConfirmationName($home2InviteName) : '';
$home2ExistingConfirmation = $home2ConfirmationName !== ''
    ? RsvpService::findConfirmation($pdo, (string) $codevent, $home2ConfirmationName)
    : null;
$home2RequestedPresence = strtolower(trim((string) ($_GET['presence'] ?? '')));

$rsvpFeedback = '';
if ($home2InviteId <= 0 && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submitrsvp'])) {
    $guestName = trim((string) ($_POST['nom'] ?? ''));
    $guestPhone = trim((string) ($_POST['phone'] ?? ''));
    $guestEmail = trim((string) ($_POST['email'] ?? ''));
    $guestPresence = trim((string) ($_POST['presence'] ?? ''));
    $guestNote = trim((string) ($_POST['note'] ?? ''));

    if ($guestName === '' || $guestPhone === '' || !in_array($guestPresence, ['oui', 'non'], true)) {
        $rsvpFeedback = '<p class="home2-rsvp-alert home2-rsvp-alert-error">Merci de renseigner votre nom, votre téléphone et votre réponse.</p>';
    } else {
        try {
            $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM confirmation WHERE cod_mar = :cod_mar AND (phone = :phone OR (email <> "" AND email = :email))');
            $checkStmt->execute([
                ':cod_mar' => (string) $codevent,
                ':phone' => $guestPhone,
                ':email' => $guestEmail !== '' ? $guestEmail : 'nonmail@is.com',
            ]);
            $alreadyConfirmed = (int) $checkStmt->fetchColumn() > 0;
            $checkStmt->closeCursor();

            if ($alreadyConfirmed) {
                $rsvpFeedback = '<p class="home2-rsvp-alert home2-rsvp-alert-error">Cette présence a déjà été confirmée.</p>';
            } else {
                $insertStmt = $pdo->prepare('INSERT INTO confirmation (cod_mar, noms, email, phone, presence, note, date_enreg) VALUES (:cod_mar, :noms, :email, :phone, :presence, :note, NOW())');
                $insertStmt->execute([
                    ':cod_mar' => (string) $codevent,
                    ':noms' => $guestName,
                    ':email' => $guestEmail !== '' ? $guestEmail : 'nonmail@is.com',
                    ':phone' => $guestPhone,
                    ':presence' => $guestPresence,
                    ':note' => $guestNote,
                ]);
                $insertStmt->closeCursor();
                $rsvpFeedback = '<p class="home2-rsvp-alert home2-rsvp-alert-success">Votre réponse a été enregistrée avec succès.</p>';
            }
        } catch (Throwable $exception) {
            PublicSiteTraceService::exception('site_wedding_home2_rsvp_exception', $exception, [
                'event_id' => (int) $codevent,
            ]);
            $rsvpFeedback = '<p class="home2-rsvp-alert home2-rsvp-alert-error">Impossible d’enregistrer votre réponse pour le moment.</p>';
        }
    }
}

$home2ModalMarkup = '';
$home2ModalScript = '';
$home2GuestHeroNotice = '';

if ($home2InviteId > 0) {
    $home2SafeInviteName = $home2InviteName !== '' ? $home2E($home2InviteName) : 'Invité';
    $home2StatusText = 'Votre invitation personnelle a été reconnue. Merci d’utiliser la fenêtre de confirmation pour enregistrer votre réponse.';

    if (is_array($home2ExistingConfirmation)) {
        $home2ExistingPresence = (string) ($home2ExistingConfirmation['presence'] ?? '');
        $home2StatusText = match ($home2ExistingPresence) {
            'oui' => 'Votre présence avait déjà été confirmée pour ce mariage.',
            'non' => 'Votre non participation avait déjà été enregistrée pour ce mariage.',
            default => 'Votre réponse avait déjà été enregistrée pour ce mariage.',
        };
    } elseif ($home2RequestedPresence === '') {
        $home2StatusText = 'Votre invitation personnelle est bien chargée. Utilisez les liens de confirmation reçus avec votre invitation pour répondre.';
    }

    $home2GuestHeroNotice = '<p class="hero-invite-name"><strong>' . $home2SafeInviteName . '</strong><br><span>' . $home2E($home2StatusText) . '</span></p>';
    $rsvpForm = '<div class="home2-rsvp-guest-card"><p class="home2-rsvp-guest-kicker">Invitation personnelle</p><p class="home2-rsvp-guest-name">' . $home2SafeInviteName . '</p><p>' . $home2E($home2StatusText) . '</p></div>';

    ob_start();
    include __DIR__ . '/modalreponse.php';
    $home2ModalMarkup = (string) ob_get_clean();

    $home2JsonInviteName = json_encode(html_entity_decode($home2InviteName, ENT_QUOTES, 'UTF-8'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $home2JsonEventCode = json_encode((string) $codevent, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $home2JsonInviteId = json_encode((string) $home2InviteId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $home2JsonRedirectNo = json_encode('index.php?page=reponsenon&cod=' . rawurlencode((string) $codevent) . '&idinv=' . rawurlencode((string) $home2InviteId), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    if (isset($_GET['ok'])) {
        $home2ModalScript .= '<script src="../sweet/sweetalert2.all.min.js"></script><script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({title:"RSVP",text:"Votre confirmation est enregistrée.",icon:"success"});});</script>';
    } elseif (is_array($home2ExistingConfirmation) && in_array($home2RequestedPresence, ['oui', 'non'], true)) {
        $home2ModalScript .= '<script src="../sweet/sweetalert2.all.min.js"></script><script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({title:' . $home2JsonInviteName . ',text:"Votre réponse a déjà été enregistrée.",icon:"info",confirmButtonText:"OK"});});</script>';
    } elseif ($home2InviteName !== '' && $home2RequestedPresence === 'oui') {
        $home2ModalScript .= '<script>document.addEventListener("DOMContentLoaded",function(){openModal(' . $home2JsonInviteName . ',' . $home2JsonEventCode . ',' . $home2JsonInviteId . ',"form");});</script>';
    } elseif ($home2InviteName !== '' && $home2RequestedPresence === 'non') {
        $home2ModalScript .= '<script src="../sweet/sweetalert2.all.min.js"></script><script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({title:' . $home2JsonInviteName . ',text:"Vous êtes sur le point de répondre NON à cette invitation. Confirmez-vous ?",icon:"warning",showCancelButton:true,confirmButtonText:"Oui, confirmer",cancelButtonText:"Annuler"}).then(function(result){if(result.isConfirmed){window.location.href=' . $home2JsonRedirectNo . ';}});});</script>';
    } elseif ($home2InviteName !== '' && $home2RequestedPresence === 'plustard') {
        $home2ModalScript .= '<script src="../sweet/sweetalert2.all.min.js"></script><script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({title:' . $home2JsonInviteName . ',text:"Pour des raisons de logistique, nous vous prions de bien vouloir confirmer votre participation, ou pas, quelques jours avant la cérémonie.",icon:"warning",confirmButtonText:"OK"});});</script>';
    }
} else {
    $rsvpNameValue = $home2E((string) ($_POST['nom'] ?? ''));
    $rsvpPhoneValue = $home2E((string) ($_POST['phone'] ?? ''));
    $rsvpEmailValue = $home2E((string) ($_POST['email'] ?? ''));
    $rsvpNoteValue = $home2E((string) ($_POST['note'] ?? ''));
    $rsvpPresenceOui = ((string) ($_POST['presence'] ?? '')) === 'oui' ? 'checked' : '';
    $rsvpPresenceNon = ((string) ($_POST['presence'] ?? '')) === 'non' ? 'checked' : '';
    $rsvpForm = $rsvpFeedback . <<<HTML
            <form method="post" class="home2-rsvp-form">
                <input type="text" name="nom" placeholder="Votre prénom et nom" value="{$rsvpNameValue}" required>
                <input type="tel" name="phone" placeholder="Votre téléphone" value="{$rsvpPhoneValue}" required>
                <input type="email" name="email" placeholder="Votre e-mail" value="{$rsvpEmailValue}">
                <div class="home2-rsvp-choice">
                    <label><input type="radio" name="presence" value="oui" {$rsvpPresenceOui} required> Je serai présent(e)</label>
                    <label><input type="radio" name="presence" value="non" {$rsvpPresenceNon}> Je ne pourrai pas venir</label>
                </div>
                <textarea name="note" placeholder="Message aux mariés (facultatif)">{$rsvpNoteValue}</textarea>
                <button type="submit" name="submitrsvp">Confirmer ma réponse</button>
            </form>
HTML;
}

if ($home2Html === '') {
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . $home2E($couple) . '</title></head><body>' . $home2BackgroundMusicMarkup . '<h1>' . $home2E($couple) . '</h1>' . $home2GuestHeroNotice . '<p>' . $home2E($heroDate) . '</p><p>' . $home2E($eventPlace) . '</p>' . $rsvpForm . $home2ModalMarkup . $home2ModalScript . '</body></html>';
    return;
}

$home2ExtraCss = <<<CSS
@font-face{font-family:'Birds of Paradise';src:local('Birds of Paradise'),local('BirdsOfParadise');font-display:swap}
.nav-monogram{width:37.5px!important;height:37.5px!important;min-width:37.5px!important}
.nav-wordmark,.hero-names,.footer-names{font-family:'Birds of Paradise','SignPainter','Cormorant Garamond',cursive!important;font-style:normal!important;font-weight:400!important;letter-spacing:.02em}
.nav-wordmark{font-size:22px!important;letter-spacing:.03em!important;text-transform:none!important;line-height:1!important}
{$home2SectionBackgroundCss}
.home2-rsvp-form{display:grid;gap:.9rem;max-width:620px;margin:1.2rem auto 0;text-align:left}
.home2-rsvp-form input,.home2-rsvp-form textarea{width:100%;border:1px solid rgba(201,168,76,.45);border-radius:0;background:rgba(247,243,236,.92);color:#3C0B1A;font:600 15px/1.4 Cormorant Garamond,serif;padding:.9rem 1rem;outline:none}
.home2-rsvp-form textarea{min-height:100px;resize:vertical}
.home2-rsvp-form input:focus,.home2-rsvp-form textarea:focus{border-color:#C9A84C;box-shadow:0 0 0 3px rgba(201,168,76,.14)}
.home2-rsvp-choice{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem;color:#F7F3EC;text-align:left}
.home2-rsvp-choice label{border:1px solid rgba(201,168,76,.42);padding:.85rem 1rem;background:rgba(60,11,26,.42);cursor:pointer;font:600 14px/1.4 Cormorant Garamond,serif}
.home2-rsvp-choice input{width:auto;margin-right:.45rem}
.home2-rsvp-form button{border:0;background:#C9A84C;color:#3C0B1A;text-transform:uppercase;letter-spacing:.18em;font:700 12px/1 Cormorant SC,serif;padding:1rem 1.2rem;cursor:pointer}
.home2-rsvp-alert{max-width:620px;margin:1rem auto;padding:.9rem 1rem;border:1px solid;text-align:center;font:700 15px/1.5 Cormorant Garamond,serif}
.home2-rsvp-alert-success{color:#14532d;background:#dcfce7;border-color:#86efac}
.home2-rsvp-alert-error{color:#7f1d1d;background:#fee2e2;border-color:#fecaca}
.hero-invite-name{margin:0 auto 1.35rem;max-width:720px;color:#F7F3EC;font:600 18px/1.45 Cormorant Garamond,serif;letter-spacing:.02em;text-align:center;text-shadow:0 2px 14px rgba(0,0,0,.28)}
.hero-invite-name strong{display:block;color:#C9A84C;font:700 24px/1.2 Cormorant SC,serif;text-transform:uppercase;letter-spacing:.08em}
.hero-invite-name span{opacity:.95}
.home2-rsvp-guest-card{max-width:620px;margin:1.2rem auto 0;border:1px solid rgba(201,168,76,.5);background:rgba(247,243,236,.94);color:#3C0B1A;padding:1.4rem 1.2rem;text-align:center;box-shadow:0 18px 50px rgba(0,0,0,.18)}
.home2-rsvp-guest-kicker{margin:0 0 .35rem;color:#7A1028;text-transform:uppercase;letter-spacing:.2em;font:700 11px/1 Cormorant SC,serif}
.home2-rsvp-guest-name{margin:.15rem 0 .65rem;color:#3C0B1A;font:700 28px/1.15 Cormorant Garamond,serif}
.modalinv{font-family:Cormorant Garamond,serif;color:#3C0B1A}.modalinv .modal-content{z-index:77001}.modalinv .form-control{box-sizing:border-box;width:100%;border:1px solid rgba(60,11,26,.18);padding:.85rem 1rem;margin:.3rem 0 .9rem}.modalinv .btn{border:0;background:#C9A84C;color:#3C0B1A;padding:.85rem 1rem;cursor:pointer}.modalinv .alert{padding:1rem;background:#fff7d6;border:1px solid #f5d36a;color:#3C0B1A}
{$home2SectionTextColorCss}
.home2-map-frame{padding:0;overflow:hidden;background:#F7F3EC}
.home2-map-frame iframe{display:block;width:100%;min-height:360px;border:0}
@media (max-width:640px){.home2-rsvp-choice{grid-template-columns:1fr}}
CSS;
$home2Html = str_replace('</style>', $home2ExtraCss . "\n</style>", $home2Html);

$home2Html = strtr($home2Html, [
    'Eliel &amp; Josianne — Mariage · Kinshasa 2026' => $home2E($home2Couple) . ' — Mariage · ' . $home2E($home2HeroMeta),
    'Eliel &amp; Josianne' => $home2E($home2Couple),
    "Les familles Kasenga et Kalala Wa Kalala<br>\n    ont l'honneur de vous convier au mariage de leurs enfants" => $home2Block($home2Text('home2_honor_text')),
    'Kinshasa · Juillet 2026' => $home2E($home2HeroMeta),
    'Confirmer ma présence' => $home2E($home2Text('home2_button_text', 'Confirmer ma présence')),
    'La soirée' => $home2E($home2Text('home2_gala_label', 'La soirée')),
    'Soirée de Gala' => $home2E($home2Text('home2_gala_title', 'Soirée de Gala')),
    'Samedi 25 juillet 2026 · 19h00' => $home2E($heroDate),
    'Samedi 25 juillet 2026, à partir de 19h00 — New Ball Room, Hôtel du Fleuve, Kinshasa.' => $home2E($home2GalaSubtitle),
    'Samedi 25 juillet 2026, à partir de 19h00' => $home2E($galaDateLine),
    'SAMEDI 25 JUILLET 2026' => $home2E($eventDateLongUpper),
    'À PARTIR DE 19H00' => 'À PARTIR DE ' . $home2E($home2Upper($eventTime)),
    'Le cadre' => $home2E($home2Text('home2_gala_primary_label', 'Le cadre')),
    'New Ball Room · Hôtel du Fleuve' => $home2E($home2GalaPrimaryValue),
    'Après les cérémonies du jour, nous aurons le plaisir de vous accueillir pour une soirée de gala conçue comme une célébration intime et mémorable, dans un cadre raffiné au bord du fleuve Congo.' => $home2E($home2Text('home2_gala_primary_text')),
    "L'esprit de la soirée" => $home2E($home2Text('home2_gala_secondary_label', 'L’esprit de la soirée')),
    'Musique · Gastronomie · Festivité' => $home2E($home2Text('home2_gala_secondary_value', 'Musique · Gastronomie · Festivité')),
    'Entourés de nos familles et amis les plus chers, nous célébrerons cette union dans une atmosphère alliant émotion, élégance et joie partagée. Réservez cette date — nous comptons sur votre présence.' => $home2E($home2Text('home2_gala_secondary_text')),
    'Votre invitation' => $home2E($home2Text('home2_program_label', 'Votre invitation')),
    'Programme' => $home2E($home2Text('home2_program_title', 'Programme')),
    "Retrouvez ci-dessous le déroulé de l'événement auquel vous êtes convié." => $home2E($home2Text('home2_program_subtitle')),
    'Samedi 25 juillet 2026 · Soir' => $home2E($home2ProgramEyebrow),
    '<h3 class="cer-name">Soirée de Gala</h3>' => '<h3 class="cer-name">' . $home2E($home2Text('home2_program_event_title', 'Soirée de Gala')) . '</h3>',
    'Gala' => $home2E($home2Text('home2_program_event_badge', 'Gala')),
    "Heure d'accueil" => $home2E($home2Text('home2_program_time_label', "Heure d'accueil")),
    '19h00' => $home2E($home2ProgramTimeValue),
    'Lieu' => $home2E($home2Text('home2_program_location_label', 'Lieu')),
    'New Ball Room<br>Hôtel du Fleuve, Kinshasa' => $home2Block($home2ProgramLocationValue),
    'Dress code' => $home2E($home2Text('home2_program_dress_label', 'Dress code')),
    'Tenue de soirée<br>Élégance requise' => $home2Block($home2Text('home2_program_dress_value')),
    'Réception officielle, dîner de gala, animations et grande célébration.' => $home2E($home2Text('home2_program_description')),
    'Notre histoire' => $home2E($home2Text('home2_story_label', 'Notre histoire')),
    'Love Story' => $home2E($home2Text('home2_story_title', 'Love Story')),
    "Chaque étape de notre parcours nous a conduits, avec une certitude tranquille, vers cette célébration. Ce que nous fêtons n'est pas seulement notre union — c'est la joie de réunir autour de nous toutes les personnes qui ont compté dans cette histoire." => $home2E($home2Text('home2_story_text_1')),
    "Nous vous invitons à partager avec nous une soirée empreinte d'élégance, de chaleur et de moments qui restent." => $home2E($home2Text('home2_story_text_2')),
    'Fin heureuse — nous nous marions.' => $home2E($home2Text('home2_story_coda')),
    'Présences &amp; attentions' => $home2E($home2Text('home2_presence_label', 'Présences & attentions')),
    'Votre présence nous honore' => $home2E($home2Text('home2_presence_title')),
    'Votre venue est le plus beau des cadeaux. Pour ceux qui souhaitent nous accompagner davantage dans cette nouvelle étape, une urne sera mise à disposition le soir du gala.' => $home2E($home2Text('home2_presence_subtitle')),
    'Participation à la réception' => $home2E($home2Text('home2_presence_card1_title')),
    "Dîner, animations et grande célébration avec l'ensemble des convives de la soirée." => $home2E($home2Text('home2_presence_card1_text')),
    'Invitation personnelle' => $home2E($home2Text('home2_presence_card2_title')),
    'Cette invitation est strictement personnelle et non transmissible. Merci de confirmer votre présence via le formulaire RSVP.' => $home2E($home2Text('home2_presence_card2_text')),
    'Logistique' => $home2E($home2Text('home2_location_label', 'Logistique')),
    'Adresse &amp; hébergement' => $home2E($home2Text('home2_location_title', 'Adresse & hébergement')),
    'Informations pratiques pour préparer votre venue à Kinshasa, RDC.' => $home2E($home2Text('home2_location_subtitle')),
    'Lieu principal' => $home2E($home2Text('home2_main_place_title')),
    'Dress code : tenue de soirée, élégance requise. Accès et instructions détaillés dans votre carte d\'accompagnement.' => $home2E($home2Text('home2_main_place_note')),
    'Hébergements recommandés' => $home2E($home2Text('home2_accommodation_title')),
    "Fleuve Congo Hôtel<br>\n          The First MK<br>\n          Hôtel du Fleuve" => $home2Block($home2Text('home2_accommodation_text')),
    "Pour toute question logistique, contactez l'équipe via le formulaire ci-dessous ou au numéro figurant sur votre invitation." => $home2E($home2Text('home2_accommodation_note')),
    'Carte Google Maps — à intégrer depuis l\'espace d\'administration' => $home2E($home2Text('home2_map_placeholder')),
    'Confirmation' => $home2E($home2Text('home2_rsvp_label', 'Confirmation')),
    '<h2 class="section-title">RSVP</h2>' => '<h2 class="section-title">' . $home2E($home2Text('home2_rsvp_title', 'RSVP')) . '</h2>',
    'Merci de confirmer votre présence avant le <em>10 juillet 2026</em>. Cette invitation est personnelle et non transmissible.' => $home2RsvpSubtitle === $home2Text('home2_rsvp_subtitle', '') ? $home2E($home2RsvpSubtitle) : $home2RsvpSubtitle,
    '25 Juillet 2026 &nbsp;·&nbsp; Kinshasa, République Démocratique du Congo' => $home2E($home2FooterMeta),
    'New Ball Room' => $home2E($eventPlace),
    '25 Juillet 2026' => $home2E($eventDateNoDay),
    '10 juillet 2026' => $home2E($rsvpLimitText),
    "new Date('2026-07-25T19:00:00+02:00')" => "new Date('" . $home2E($countdownTarget) . "')",
]);

if ($home2GuestHeroNotice !== '') {
    $home2Html = preg_replace(
        '#(<p class="hero-honor">.*?</p>)#su',
        '$1' . $home2GuestHeroNotice,
        $home2Html,
        1
    ) ?? $home2Html;
}

$home2MainPlaceBlock = '<div class="adr-block"><h3>' . $home2E($home2Text('home2_main_place_title', 'Lieu principal')) . '</h3><p>' . $home2Block($home2MainPlaceText) . '</p><p class="adr-note">' . $home2E($home2Text('home2_main_place_note')) . '</p></div>';
$home2AccommodationBlock = '<div class="adr-block"><h3>' . $home2E($home2Text('home2_accommodation_title', 'Hébergements recommandés')) . '</h3><p>' . $home2Block($home2Text('home2_accommodation_text')) . '</p><p class="adr-note">' . $home2E($home2Text('home2_accommodation_note')) . '</p></div>';
$home2AddressBlockIndex = 0;
$home2Html = preg_replace_callback(
    '#<div class="adr-block">\s*<h3>.*?</h3>\s*<p>.*?</p>\s*<p class="adr-note">.*?</p>\s*</div>#su',
    static function (array $matches) use (&$home2AddressBlockIndex, $home2MainPlaceBlock, $home2AccommodationBlock): string {
        $home2AddressBlockIndex++;
        if ($home2AddressBlockIndex === 1) {
            return $home2MainPlaceBlock;
        }
        if ($home2AddressBlockIndex === 2) {
            return $home2AccommodationBlock;
        }

        return (string) ($matches[0] ?? '');
    },
    $home2Html,
    2
) ?? $home2Html;

if ($home2MapIframe !== '') {
    $home2MapHtml = '<div class="map-placeholder home2-map-frame">' . $home2MapIframe . '</div>';
    $home2Html = preg_replace('#<div class="map-placeholder">\s*.*?\s*</div>#su', $home2MapHtml, $home2Html, 1) ?? $home2Html;
}

$home2Monogram = WeddingWebsiteSettingsService::image($settings, 'home2_monogram', '');
if ($home2Monogram !== '') {
    $home2MonogramUrl = '../couple/images/' . rawurlencode($home2Monogram);
    $home2Html = preg_replace('#<img src="data:image/png;base64,[^"]+" alt="([^"]*)">#u', '<img src="' . $home2MonogramUrl . '" alt="$1">', $home2Html) ?? $home2Html;
}

if ($eventAddress !== '') {
    $home2AddressHtml = nl2br($home2E($eventAddress));
    $home2Html = preg_replace(
        '#<p>\s*' . preg_quote($home2E($eventPlace), '#') . '<br>\s*Hôtel du Fleuve<br>\s*Kinshasa, République Démocratique du Congo\s*</p>#u',
        '<p>' . $home2E($eventPlace) . '<br>' . $home2AddressHtml . '</p>',
        $home2Html,
        1
    ) ?? $home2Html;
}

$home2Html = preg_replace(
    '#<p class="rsvp-body">\s*Votre présence a été enregistrée\. Pour toute modification ou demande d\'information complémentaire, merci de nous contacter directement\.\s*</p>\s*<p class="rsvp-confirm">Nous vous attendons avec joie\.</p>#u',
    $rsvpForm,
    $home2Html,
    1
) ?? $home2Html;

if ($home2BackgroundMusicMarkup !== '') {
    if (str_contains($home2Html, '</body>')) {
        $home2Html = str_replace('</body>', $home2BackgroundMusicMarkup . "\n</body>", $home2Html);
    } else {
        $home2Html .= $home2BackgroundMusicMarkup;
    }
}

$home2ModalOutput = $home2ModalMarkup . $home2ModalScript;
if ($home2ModalOutput !== '') {
    if (str_contains($home2Html, '</body>')) {
        $home2Html = str_replace('</body>', $home2ModalOutput . "\n</body>", $home2Html);
    } else {
        $home2Html .= $home2ModalOutput;
    }
}

echo $home2Html;