<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

drc_boot($pdo);

$assetErrors = drc_verify_required_assets();
$logoPaths = drc_public_logo_paths();
$csrfKey = 'drcalumni_public_csrf';
$submissionKey = 'drcalumni_submission_nonce';
$csrfToken = drc_ensure_csrf($csrfKey);
if (empty($_SESSION[$submissionKey])) {
    $_SESSION[$submissionKey] = bin2hex(random_bytes(32));
}
$submissionNonce = (string) $_SESSION[$submissionKey];

$flashMessage = '';
$errors = [];
$currentStep = max(1, min(7, (int) ($_POST['current_step'] ?? 1)));
$registrationsOpen = drc_setting($pdo, 'registrations_open', '1') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentStep = max(1, min(7, (int) ($_POST['current_step'] ?? 1)));
    $submittedCsrf = (string) drc_posted('csrf_token');
    $submittedNonce = (string) drc_posted('submission_nonce');
    $honeypot = trim((string) drc_posted('website'));

    if ($honeypot !== '') {
        $errors['global'] = "La soumission n'a pas pu être traitée.";
    }
    if (!drc_validate_csrf($csrfKey, $submittedCsrf)) {
        $errors['global'] = 'Session expirée. Veuillez recharger la page.';
    }
    if (!hash_equals((string) $_SESSION[$submissionKey], $submittedNonce)) {
        $errors['global'] = 'Soumission déjà traitée ou expirée. Veuillez réessayer.';
    }
    if (!$registrationsOpen) {
        $errors['global'] = "Les candidatures sont actuellement fermées.";
    }

    $fullName = drc_clean_text((string) drc_posted('full_name'), 180);
    $email = trim((string) drc_posted('email'));
    $emailNormalized = drc_normalize_email($email);
    $phone = drc_clean_text((string) drc_posted('phone'), 80);
    $province = drc_clean_text((string) drc_posted('province'), 120);
    $cityTerritory = drc_clean_text((string) drc_posted('city_territory'), 180);
    $organizationName = drc_clean_text((string) drc_posted('organization_name'), 190);

    $sectors = array_values(array_filter((array) drc_posted('sectors', []), static fn ($item) => is_string($item)));
    $sectorsOther = drc_clean_text((string) drc_posted('sectors_other'), 190);
    $responsibilities = drc_clean_multiline((string) drc_posted('responsibilities'), 2000);

    $programs = array_values(array_filter((array) drc_posted('programs', []), static fn ($item) => is_string($item)));
    $programsOther = drc_clean_text((string) drc_posted('programs_other'), 190);
    $latestExchangeYear = (int) drc_posted('latest_exchange_year');

    $summitThemes = array_values(array_filter((array) drc_posted('summit_themes', []), static fn ($item) => is_string($item)));
    $cooperationDomains = drc_clean_multiline((string) drc_posted('cooperation_domains'), 1600);
    $washingtonAccords = drc_clean_multiline((string) drc_posted('washington_accords_importance'), 2400);
    $summitExpectations = drc_clean_multiline((string) drc_posted('summit_expectations'), 2400);
    $sharableExpertise = drc_clean_multiline((string) drc_posted('sharable_expertise'), 2400);
    $wantsWorkgroup = (string) drc_posted('wants_workgroup') === '1';
    $workgroupChoice = drc_clean_text((string) drc_posted('workgroup_choice'), 120);

    $wantsExpo = (string) drc_posted('wants_expo') === '1';
    $expoEntityName = drc_clean_text((string) drc_posted('expo_entity_name'), 190);
    $expoDescription = drc_clean_multiline((string) drc_posted('expo_description'), 1600);
    $expoLink = drc_clean_text((string) drc_posted('expo_link'), 255);

    $availability = drc_clean_text((string) drc_posted('availability'), 40);
    $needsAccommodation = (string) drc_posted('needs_accommodation') === '1';
    $accommodationDetails = drc_clean_multiline((string) drc_posted('accommodation_details'), 1200);
    $additionalComments = drc_clean_multiline((string) drc_posted('additional_comments'), 1200);
    $declarationTruth = (string) drc_posted('declaration_truth') === '1';
    $declarationUnderstanding = (string) drc_posted('declaration_understanding') === '1';

    if ($fullName === '') $errors['full_name'] = 'Le nom et prénom sont obligatoires.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Adresse e-mail invalide.';
    if ($phone === '') $errors['phone'] = 'Le numéro de téléphone est obligatoire.';
    if (!in_array($province, drc_provinces(), true)) $errors['province'] = 'Veuillez sélectionner une province valide.';
    if ($cityTerritory === '') $errors['city_territory'] = 'La ville ou le territoire est obligatoire.';
    if ($organizationName === '') $errors['organization_name'] = "Le nom de l'organisation est obligatoire.";

    $sectorOptions = drc_sector_options();
    $invalidSector = array_filter($sectors, static fn ($item) => !array_key_exists($item, $sectorOptions));
    if (count($sectors) < 2 || count($sectors) > 3 || $invalidSector !== []) {
        $errors['sectors'] = 'Veuillez sélectionner entre 2 et 3 secteurs.';
    }
    if (in_array('other', $sectors, true) && $sectorsOther === '') {
        $errors['sectors_other'] = 'Veuillez préciser le secteur "Autre".';
    }
    if ($responsibilities === '') $errors['responsibilities'] = 'Cette réponse est obligatoire.';
    drc_validate_max_words($errors, 'responsibilities', 'Q7', $responsibilities, 150);

    $programOptions = drc_program_options();
    $invalidProgram = array_filter($programs, static fn ($item) => !array_key_exists($item, $programOptions));
    if ($programs === [] || $invalidProgram !== []) $errors['programs'] = 'Veuillez sélectionner au moins un programme.';
    if (in_array('other', $programs, true) && $programsOther === '') $errors['programs_other'] = 'Veuillez préciser le programme "Autre".';
    $currentYear = (int) (new DateTimeImmutable('now'))->format('Y');
    if ($latestExchangeYear < 1950 || $latestExchangeYear > $currentYear) {
        $errors['latest_exchange_year'] = 'Veuillez indiquer une année valide et non future.';
    }

    $themeOptions = drc_theme_options();
    $invalidTheme = array_filter($summitThemes, static fn ($item) => !array_key_exists($item, $themeOptions));
    if (count($summitThemes) > 3 || $summitThemes === [] || $invalidTheme !== []) {
        $errors['summit_themes'] = 'Veuillez sélectionner entre 1 et 3 thèmes.';
    }
    if ($cooperationDomains === '') $errors['cooperation_domains'] = 'Cette réponse est obligatoire.';
    if ($washingtonAccords === '') $errors['washington_accords_importance'] = 'Cette réponse est obligatoire.';
    if ($summitExpectations === '') $errors['summit_expectations'] = 'Cette réponse est obligatoire.';
    if ($sharableExpertise === '') $errors['sharable_expertise'] = 'Cette réponse est obligatoire.';
    drc_validate_max_words($errors, 'cooperation_domains', 'Q11', $cooperationDomains, 80);
    drc_validate_max_words($errors, 'washington_accords_importance', 'Q12', $washingtonAccords, 150);
    drc_validate_max_words($errors, 'summit_expectations', 'Q13', $summitExpectations, 150);
    drc_validate_max_words($errors, 'sharable_expertise', 'Q14', $sharableExpertise, 150);

    if ($wantsWorkgroup) {
        if (!array_key_exists($workgroupChoice, drc_workgroup_options())) {
            $errors['workgroup_choice'] = 'Veuillez sélectionner un groupe de travail.';
        }
    } else {
        $workgroupChoice = '';
    }

    if ($wantsExpo) {
        if ($expoEntityName === '') $errors['expo_entity_name'] = "Le nom de l'initiative est obligatoire.";
        if ($expoDescription === '') $errors['expo_description'] = 'La description est obligatoire.';
        drc_validate_max_words($errors, 'expo_description', 'Q17', $expoDescription, 80);
    } else {
        $expoEntityName = '';
        $expoDescription = '';
        $expoLink = '';
    }

    if (!array_key_exists($availability, drc_availability_options())) {
        $errors['availability'] = 'Veuillez choisir votre disponibilité.';
    }
    if ($needsAccommodation && $accommodationDetails === '') {
        $errors['accommodation_details'] = "Veuillez préciser l'aménagement nécessaire.";
    }
    if (!$needsAccommodation) {
        $accommodationDetails = '';
    }
    drc_validate_max_words($errors, 'additional_comments', 'Q20', $additionalComments, 80);

    if (!$declarationTruth) $errors['declaration_truth'] = 'Cette déclaration est obligatoire.';
    if (!$declarationUnderstanding) $errors['declaration_understanding'] = 'Cette déclaration est obligatoire.';

    if ($errors === []) {
        $duplicateStmt = $pdo->prepare('SELECT id FROM drcalumni_applications WHERE email_normalized = :email LIMIT 1');
        $duplicateStmt->execute([':email' => $emailNormalized]);
        if ($duplicateStmt->fetch(PDO::FETCH_ASSOC)) {
            $errors['email'] = 'Une candidature existe déjà pour cette adresse e-mail.';
        }
    }

    if ($errors !== [] && !isset($errors['global'])) {
        $fieldStepMap = [
            'full_name' => 1,
            'email' => 1,
            'phone' => 1,
            'province' => 1,
            'city_territory' => 1,
            'organization_name' => 1,
            'sectors' => 2,
            'sectors_other' => 2,
            'responsibilities' => 2,
            'programs' => 3,
            'programs_other' => 3,
            'latest_exchange_year' => 3,
            'summit_themes' => 4,
            'cooperation_domains' => 4,
            'washington_accords_importance' => 4,
            'summit_expectations' => 4,
            'sharable_expertise' => 4,
            'workgroup_choice' => 4,
            'expo_entity_name' => 5,
            'expo_description' => 5,
            'availability' => 6,
            'accommodation_details' => 6,
            'additional_comments' => 6,
            'declaration_truth' => 6,
            'declaration_understanding' => 6,
        ];

        $firstFieldKey = array_key_first($errors);
        if ($firstFieldKey !== null) {
            $currentStep = $fieldStepMap[$firstFieldKey] ?? $currentStep;
            $errors['global'] = (string) $errors[$firstFieldKey];
        } else {
            $errors['global'] = 'Veuillez vérifier les informations du formulaire.';
        }
    }

    if ($errors === []) {
        $reference = drc_generate_reference();
        try {
            $pdo->beginTransaction();

            $insert = $pdo->prepare(
                'INSERT INTO drcalumni_applications (
                    reference_code, full_name, email, email_normalized, phone, province, city_territory, organization_name,
                    sectors_json, sectors_other, responsibilities, programs_json, programs_other, latest_exchange_year,
                    summit_themes_json, cooperation_domains, washington_accords_importance, summit_expectations, sharable_expertise,
                    wants_workgroup, workgroup_choice, wants_expo, expo_entity_name, expo_description, expo_link,
                    availability, needs_accommodation, accommodation_details, additional_comments,
                    declaration_truth, declaration_understanding, status, submission_token, submission_ip, submission_user_agent
                ) VALUES (
                    :reference_code, :full_name, :email, :email_normalized, :phone, :province, :city_territory, :organization_name,
                    :sectors_json, :sectors_other, :responsibilities, :programs_json, :programs_other, :latest_exchange_year,
                    :summit_themes_json, :cooperation_domains, :washington_accords_importance, :summit_expectations, :sharable_expertise,
                    :wants_workgroup, :workgroup_choice, :wants_expo, :expo_entity_name, :expo_description, :expo_link,
                    :availability, :needs_accommodation, :accommodation_details, :additional_comments,
                    :declaration_truth, :declaration_understanding, :status, :submission_token, :submission_ip, :submission_user_agent
                )'
            );
            $insert->execute([
                ':reference_code' => $reference,
                ':full_name' => $fullName,
                ':email' => $email,
                ':email_normalized' => $emailNormalized,
                ':phone' => $phone,
                ':province' => $province,
                ':city_territory' => $cityTerritory,
                ':organization_name' => $organizationName,
                ':sectors_json' => drc_json_encode($sectors),
                ':sectors_other' => $sectorsOther !== '' ? $sectorsOther : null,
                ':responsibilities' => $responsibilities,
                ':programs_json' => drc_json_encode($programs),
                ':programs_other' => $programsOther !== '' ? $programsOther : null,
                ':latest_exchange_year' => $latestExchangeYear,
                ':summit_themes_json' => drc_json_encode($summitThemes),
                ':cooperation_domains' => $cooperationDomains,
                ':washington_accords_importance' => $washingtonAccords,
                ':summit_expectations' => $summitExpectations,
                ':sharable_expertise' => $sharableExpertise,
                ':wants_workgroup' => $wantsWorkgroup ? 1 : 0,
                ':workgroup_choice' => $wantsWorkgroup ? $workgroupChoice : null,
                ':wants_expo' => $wantsExpo ? 1 : 0,
                ':expo_entity_name' => $wantsExpo ? $expoEntityName : null,
                ':expo_description' => $wantsExpo ? $expoDescription : null,
                ':expo_link' => $wantsExpo && $expoLink !== '' ? $expoLink : null,
                ':availability' => $availability,
                ':needs_accommodation' => $needsAccommodation ? 1 : 0,
                ':accommodation_details' => $needsAccommodation ? $accommodationDetails : null,
                ':additional_comments' => $additionalComments !== '' ? $additionalComments : null,
                ':declaration_truth' => $declarationTruth ? 1 : 0,
                ':declaration_understanding' => $declarationUnderstanding ? 1 : 0,
                ':status' => 'pending',
                ':submission_token' => $submittedNonce,
                ':submission_ip' => drc_clean_text((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 90),
                ':submission_user_agent' => drc_clean_text((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 255),
            ]);

            $applicationId = (int) $pdo->lastInsertId();
            drc_audit($pdo, null, 'application.submitted', 'application', (string) $applicationId, ['reference' => $reference, 'email' => drc_mask_sensitive_email($email)]);

            if (drc_setting($pdo, 'submission_ack_enabled', '1') === '1') {
                $subject = drc_setting($pdo, 'submission_ack_subject', 'Accusé de réception – DRC ALUMNI IMPACT 2026') ?? '';
                $rawBody = drc_setting($pdo, 'submission_ack_body', '') ?? '';
                $textBody = str_replace(['{name}', '{reference}'], [$fullName, $reference], $rawBody);
                drc_enqueue_email(
                    $pdo,
                    $applicationId,
                    'submission_ack',
                    $email,
                    $fullName,
                    $subject,
                    drc_official_mail_html($subject, $textBody),
                    $textBody,
                    'submission_ack_' . $applicationId
                );
            }

            $pdo->commit();

            $_SESSION[$submissionKey] = bin2hex(random_bytes(32));
            $_SESSION['drcalumni_last_reference'] = $reference;
            header('Location: ' . drc_url('confirmation/index.php?ref=' . rawurlencode($reference)));
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[DRC Alumni Submission] ' . $exception->getMessage());
            $errors['global'] = "Une erreur est survenue lors de l'enregistrement de votre dossier.";
        }
    }
}

$stepIndex = max(0, min(6, $currentStep - 1));
$swalPayload = null;
if (isset($errors['global'])) {
    $swalPayload = [
        'icon' => 'error',
        'title' => 'Formulaire incomplet',
        'text' => (string) $errors['global'],
    ];
} elseif ($assetErrors !== []) {
    $swalPayload = [
        'icon' => 'warning',
        'title' => 'Vérification assets',
        'text' => 'Un ou plusieurs assets officiels sont introuvables.',
    ];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo drc_h(DRCALUMNI_EVENT_NAME); ?> | Candidature</title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/public.css')); ?>">
</head>
<body>
<header class="hero">
    <div class="hero-header">
        <div class="hero-mark">
            <div class="hero-logo-shell">
                <img class="hero-logo" src="<?php echo drc_h(drc_asset($logoPaths['header'])); ?>" alt="Ambassade des États-Unis à Kinshasa">
            </div>
        </div>
    </div>
    <div class="hero-main">
        <span class="eyebrow">Sommet officiel des alumni</span>
        <h1><?php echo drc_h(str_replace(' 2026', '', DRCALUMNI_EVENT_NAME)); ?> <span class="title-year">2026</span></h1>
        <p class="subtitle">« <?php echo drc_h(DRCALUMNI_EVENT_THEME); ?> »</p>
        <aside class="event-card">
            <strong>Informations pratiques</strong>
            <p><b>Date :</b> <?php echo drc_h(DRCALUMNI_EVENT_DATES); ?></p>
            <p><b>Heure de présence :</b> <?php echo drc_h(DRCALUMNI_EVENT_TIME); ?></p>
            <p><b>Lieu :</b> <?php echo drc_h(DRCALUMNI_EVENT_LOCATION); ?></p>
        </aside>
        <a href="#formulaire" class="hero-cta">Déposer ma candidature</a>
    </div>
</header>

<?php if ($assetErrors !== []): ?>
    <div class="error-banner">
        <?php foreach ($assetErrors as $error): ?>
            <div><?php echo drc_h($error); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main>
    <section class="section">
        <h2>À propos du sommet</h2>
        <p>Cette plateforme recueille les candidatures des anciens participants aux programmes d’échanges du gouvernement américain en République démocratique du Congo. La soumission d’une candidature ne garantit pas la participation.</p>
    </section>

    <section class="section" id="formulaire">
        <h2>Formulaire de candidature</h2>
        <p class="form-warning">Veuillez compléter les 20 questions en six étapes, puis vérifier votre récapitulatif avant soumission.</p>

        <?php if (isset($errors['global'])): ?>
            <div class="error-banner" style="margin:14px 0 0;"><?php echo drc_h($errors['global']); ?></div>
        <?php endif; ?>

        <?php if (!$registrationsOpen): ?>
            <div class="error-banner" style="margin:14px 0 0;">Les candidatures sont actuellement fermées. Merci de revenir ultérieurement.</div>
        <?php else: ?>
        <form method="post" action="index.php" class="wizard" data-wizard data-initial-step="<?php echo (int) $stepIndex; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
            <input type="hidden" name="submission_nonce" value="<?php echo drc_h($submissionNonce); ?>">
            <input type="hidden" name="current_step" value="<?php echo (int) $currentStep; ?>">
            <input type="text" name="website" class="field" style="position:absolute;left:-10000px;opacity:0" tabindex="-1" autocomplete="off">

            <div class="wizard-progress"><span data-progress></span></div>
            <div class="wizard-steps">
                <span class="wizard-step" data-step-pill>1. Infos personnelles</span>
                <span class="wizard-step" data-step-pill>2. Profil</span>
                <span class="wizard-step" data-step-pill>3. Programmes</span>
                <span class="wizard-step" data-step-pill>4. Intérêts</span>
                <span class="wizard-step" data-step-pill>5. Expo</span>
                <span class="wizard-step" data-step-pill>6. Disponibilité</span>
                <span class="wizard-step" data-step-pill>7. Récapitulatif</span>
            </div>

            <section class="step-panel" data-step-panel>
                <div class="grid-2">
                    <div class="field">
                        <label for="full_name">1. Nom et prénom</label>
                        <input id="full_name" name="full_name" type="text" required value="<?php echo drc_h((string) drc_posted('full_name')); ?>">
                        <?php if (isset($errors['full_name'])): ?><div class="error"><?php echo drc_h($errors['full_name']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="email">2. Adresse e-mail</label>
                        <input id="email" name="email" type="email" required value="<?php echo drc_h((string) drc_posted('email')); ?>">
                        <?php if (isset($errors['email'])): ?><div class="error"><?php echo drc_h($errors['email']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="phone">3. Numéro de téléphone / WhatsApp</label>
                        <input id="phone" name="phone" type="tel" required value="<?php echo drc_h((string) drc_posted('phone')); ?>">
                        <?php if (isset($errors['phone'])): ?><div class="error"><?php echo drc_h($errors['phone']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="province">4. Province de résidence</label>
                        <select id="province" name="province" required>
                            <option value="">Sélectionner</option>
                            <?php foreach (drc_provinces() as $provinceOption): ?>
                                <option value="<?php echo drc_h($provinceOption); ?>" <?php echo (string) drc_posted('province') === $provinceOption ? 'selected' : ''; ?>><?php echo drc_h($provinceOption); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['province'])): ?><div class="error"><?php echo drc_h($errors['province']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="city_territory">Ville ou territoire de résidence</label>
                        <input id="city_territory" name="city_territory" type="text" required value="<?php echo drc_h((string) drc_posted('city_territory')); ?>">
                        <?php if (isset($errors['city_territory'])): ?><div class="error"><?php echo drc_h($errors['city_territory']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="organization_name">5. Organisation ou institution actuelle</label>
                        <input id="organization_name" name="organization_name" type="text" required value="<?php echo drc_h((string) drc_posted('organization_name')); ?>">
                        <?php if (isset($errors['organization_name'])): ?><div class="error"><?php echo drc_h($errors['organization_name']); ?></div><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="step-panel" data-step-panel>
                <div class="field">
                    <label>6. Secteur d’activité (2 à 3 choix)</label>
                    <div class="option-grid">
                        <?php foreach (drc_sector_options() as $value => $label): ?>
                            <label class="option">
                                <input type="checkbox" name="sectors[]" value="<?php echo drc_h($value); ?>" <?php echo in_array($value, (array) drc_posted('sectors', []), true) ? 'checked' : ''; ?>>
                                <span><?php echo drc_h($label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['sectors'])): ?><div class="error"><?php echo drc_h($errors['sectors']); ?></div><?php endif; ?>
                </div>
                <div class="field" data-if-sector-other style="display:none;">
                    <label for="sectors_other">Précision secteur “Autre”</label>
                    <input id="sectors_other" name="sectors_other" type="text" value="<?php echo drc_h((string) drc_posted('sectors_other')); ?>">
                    <?php if (isset($errors['sectors_other'])): ?><div class="error"><?php echo drc_h($errors['sectors_other']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="responsibilities">7. Décrivez vos activités (max 150 mots)</label>
                    <textarea id="responsibilities" name="responsibilities" required data-max-words="150"><?php echo drc_h((string) drc_posted('responsibilities')); ?></textarea>
                    <div class="word-counter" data-counter-for="responsibilities"></div>
                    <?php if (isset($errors['responsibilities'])): ?><div class="error"><?php echo drc_h($errors['responsibilities']); ?></div><?php endif; ?>
                </div>
            </section>

            <section class="step-panel" data-step-panel>
                <div class="field">
                    <label>8. Programme(s) d’échange suivi(s)</label>
                    <div class="option-grid">
                        <?php foreach (drc_program_options() as $value => $label): ?>
                            <label class="option">
                                <input type="checkbox" name="programs[]" value="<?php echo drc_h($value); ?>" <?php echo in_array($value, (array) drc_posted('programs', []), true) ? 'checked' : ''; ?>>
                                <span><?php echo drc_h($label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['programs'])): ?><div class="error"><?php echo drc_h($errors['programs']); ?></div><?php endif; ?>
                </div>
                <div class="field" data-if-program-other style="display:none;">
                    <label for="programs_other">Précision programme “Autre”</label>
                    <input id="programs_other" name="programs_other" type="text" value="<?php echo drc_h((string) drc_posted('programs_other')); ?>">
                    <?php if (isset($errors['programs_other'])): ?><div class="error"><?php echo drc_h($errors['programs_other']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="latest_exchange_year">9. Année du programme le plus récent</label>
                    <input id="latest_exchange_year" name="latest_exchange_year" type="number" min="1950" max="<?php echo (int) date('Y'); ?>" required value="<?php echo drc_h((string) drc_posted('latest_exchange_year')); ?>">
                    <?php if (isset($errors['latest_exchange_year'])): ?><div class="error"><?php echo drc_h($errors['latest_exchange_year']); ?></div><?php endif; ?>
                </div>
            </section>

            <section class="step-panel" data-step-panel>
                <div class="field">
                    <label>10. Thèmes qui vous intéressent (max 3)</label>
                    <div class="option-grid">
                        <?php foreach (drc_theme_options() as $value => $label): ?>
                            <label class="option">
                                <input type="checkbox" name="summit_themes[]" value="<?php echo drc_h($value); ?>" <?php echo in_array($value, (array) drc_posted('summit_themes', []), true) ? 'checked' : ''; ?>>
                                <span><?php echo drc_h($label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['summit_themes'])): ?><div class="error"><?php echo drc_h($errors['summit_themes']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="cooperation_domains">11. Principaux domaines de coopération (max 80 mots)</label>
                    <textarea id="cooperation_domains" name="cooperation_domains" required data-max-words="80"><?php echo drc_h((string) drc_posted('cooperation_domains')); ?></textarea>
                    <div class="word-counter" data-counter-for="cooperation_domains"></div>
                    <?php if (isset($errors['cooperation_domains'])): ?><div class="error"><?php echo drc_h($errors['cooperation_domains']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="washington_accords_importance">12. Importance des accords de Washington (max 150 mots)</label>
                    <textarea id="washington_accords_importance" name="washington_accords_importance" required data-max-words="150"><?php echo drc_h((string) drc_posted('washington_accords_importance')); ?></textarea>
                    <div class="word-counter" data-counter-for="washington_accords_importance"></div>
                    <?php if (isset($errors['washington_accords_importance'])): ?><div class="error"><?php echo drc_h($errors['washington_accords_importance']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="summit_expectations">13. Vos attentes (max 150 mots)</label>
                    <textarea id="summit_expectations" name="summit_expectations" required data-max-words="150"><?php echo drc_h((string) drc_posted('summit_expectations')); ?></textarea>
                    <div class="word-counter" data-counter-for="summit_expectations"></div>
                    <?php if (isset($errors['summit_expectations'])): ?><div class="error"><?php echo drc_h($errors['summit_expectations']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="sharable_expertise">14. Expertise à partager (max 150 mots)</label>
                    <textarea id="sharable_expertise" name="sharable_expertise" required data-max-words="150"><?php echo drc_h((string) drc_posted('sharable_expertise')); ?></textarea>
                    <div class="word-counter" data-counter-for="sharable_expertise"></div>
                    <?php if (isset($errors['sharable_expertise'])): ?><div class="error"><?php echo drc_h($errors['sharable_expertise']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label>15. Souhaitez-vous participer à un groupe de travail ?</label>
                    <label class="option"><input type="radio" name="wants_workgroup" value="1" <?php echo (string) drc_posted('wants_workgroup', '') === '1' ? 'checked' : ''; ?>> Oui</label>
                    <label class="option"><input type="radio" name="wants_workgroup" value="0" <?php echo (string) drc_posted('wants_workgroup', '0') === '0' ? 'checked' : ''; ?>> Non</label>
                </div>
                <div class="field" data-if-workgroup style="display:none;">
                    <label for="workgroup_choice">16. Groupe de travail (si Oui à Q15)</label>
                    <select id="workgroup_choice" name="workgroup_choice">
                        <option value="">Sélectionner</option>
                        <?php foreach (drc_workgroup_options() as $value => $label): ?>
                            <option value="<?php echo drc_h($value); ?>" <?php echo (string) drc_posted('workgroup_choice') === $value ? 'selected' : ''; ?>><?php echo drc_h($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['workgroup_choice'])): ?><div class="error"><?php echo drc_h($errors['workgroup_choice']); ?></div><?php endif; ?>
                </div>
            </section>

            <section class="step-panel" data-step-panel>
                <div class="field">
                    <label>17. Souhaitez-vous présenter une initiative à l’Expo des Alumni ?</label>
                    <label class="option"><input type="radio" name="wants_expo" value="1" <?php echo (string) drc_posted('wants_expo', '') === '1' ? 'checked' : ''; ?>> Oui</label>
                    <label class="option"><input type="radio" name="wants_expo" value="0" <?php echo (string) drc_posted('wants_expo', '0') === '0' ? 'checked' : ''; ?>> Non</label>
                </div>
                <div data-if-expo style="display:none;">
                    <div class="field">
                        <label for="expo_entity_name">Nom de l’entreprise/organisation/initiative</label>
                        <input id="expo_entity_name" name="expo_entity_name" type="text" value="<?php echo drc_h((string) drc_posted('expo_entity_name')); ?>">
                        <?php if (isset($errors['expo_entity_name'])): ?><div class="error"><?php echo drc_h($errors['expo_entity_name']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="expo_description">Description (max 80 mots)</label>
                        <textarea id="expo_description" name="expo_description" data-max-words="80"><?php echo drc_h((string) drc_posted('expo_description')); ?></textarea>
                        <div class="word-counter" data-counter-for="expo_description"></div>
                        <?php if (isset($errors['expo_description'])): ?><div class="error"><?php echo drc_h($errors['expo_description']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="expo_link">Site web ou réseaux sociaux (facultatif)</label>
                        <input id="expo_link" name="expo_link" type="text" value="<?php echo drc_h((string) drc_posted('expo_link')); ?>">
                    </div>
                </div>
            </section>

            <section class="step-panel" data-step-panel>
                <div class="field">
                    <label for="availability">18. Disponibilité</label>
                    <select id="availability" name="availability" required>
                        <option value="">Sélectionner</option>
                        <?php foreach (drc_availability_options() as $value => $label): ?>
                            <option value="<?php echo drc_h($value); ?>" <?php echo (string) drc_posted('availability') === $value ? 'selected' : ''; ?>><?php echo drc_h($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['availability'])): ?><div class="error"><?php echo drc_h($errors['availability']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label>19. Avez-vous besoin d’un aménagement particulier ?</label>
                    <label class="option"><input type="radio" name="needs_accommodation" value="1" <?php echo (string) drc_posted('needs_accommodation', '') === '1' ? 'checked' : ''; ?>> Oui</label>
                    <label class="option"><input type="radio" name="needs_accommodation" value="0" <?php echo (string) drc_posted('needs_accommodation', '0') === '0' ? 'checked' : ''; ?>> Non</label>
                </div>
                <div class="field" data-if-accommodation style="display:none;">
                    <label for="accommodation_details">Précisez l’aménagement nécessaire</label>
                    <textarea id="accommodation_details" name="accommodation_details"><?php echo drc_h((string) drc_posted('accommodation_details')); ?></textarea>
                    <?php if (isset($errors['accommodation_details'])): ?><div class="error"><?php echo drc_h($errors['accommodation_details']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="additional_comments">20. Commentaire ou suggestion (facultatif, max 80 mots)</label>
                    <textarea id="additional_comments" name="additional_comments" data-max-words="80"><?php echo drc_h((string) drc_posted('additional_comments')); ?></textarea>
                    <div class="word-counter" data-counter-for="additional_comments"></div>
                    <?php if (isset($errors['additional_comments'])): ?><div class="error"><?php echo drc_h($errors['additional_comments']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label class="option"><input type="checkbox" name="declaration_truth" value="1" <?php echo (string) drc_posted('declaration_truth') === '1' ? 'checked' : ''; ?>> Je confirme avoir participé à un programme du gouvernement des États-Unis et certifie que les informations fournies sont exactes.</label>
                    <?php if (isset($errors['declaration_truth'])): ?><div class="error"><?php echo drc_h($errors['declaration_truth']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label class="option"><input type="checkbox" name="declaration_understanding" value="1" <?php echo (string) drc_posted('declaration_understanding') === '1' ? 'checked' : ''; ?>> Je comprends que la soumission de cette candidature ne garantit pas ma participation au Sommet et qu'une confirmation me sera communiquée séparément par les organisateurs.</label>
                    <?php if (isset($errors['declaration_understanding'])): ?><div class="error"><?php echo drc_h($errors['declaration_understanding']); ?></div><?php endif; ?>
                </div>
            </section>

            <section class="step-panel" data-step-panel>
                <div class="summary">
                    <h3>Récapitulatif avant soumission</h3>
                    <div data-summary></div>
                    <p class="hint">Après soumission, vous recevrez un accusé de réception. Cet accusé ne constitue pas une sélection.</p>
                </div>
            </section>

            <div class="wizard-actions">
                <button type="button" class="btn btn-secondary" data-step-prev>Précédent</button>
                <button type="button" class="btn btn-primary" data-step-next>Suivant</button>
                <button type="submit" class="btn btn-danger" data-step-submit style="display:none;">Soumettre ma candidature</button>
            </div>
        </form>
        <?php endif; ?>
    </section>

    <footer class="footer">
        <div class="footer-logos">
            <img src="<?php echo drc_h(drc_asset($logoPaths['footer_freedom'])); ?>" alt="Freedom 250">
            <img src="<?php echo drc_h(drc_asset($logoPaths['footer_seal'])); ?>" alt="U.S. Embassy Kinshasa Seal">
        </div>
        <p>Section des Affaires publiques · Ambassade des États-Unis à Kinshasa</p>
    </footer>
</main>
<script src="<?php echo drc_h(drc_asset('assets/js/form-wizard.js')); ?>"></script>
<script src="<?php echo drc_h(drc_asset('sweet/sweetalert2.all.min.js')); ?>" onerror="this.onerror=null;this.src='/sweet/sweetalert2.all.min.js';"></script>
<script>if (!window.Swal) { document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\\/script>'); }</script>
<?php if ($swalPayload !== null): ?>
<script>
window.addEventListener('DOMContentLoaded', function () {
    if (!window.Swal) return;
    Swal.fire(<?php echo json_encode($swalPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
});
</script>
<?php endif; ?>
</body>
</html>
