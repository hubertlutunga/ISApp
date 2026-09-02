<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Kinshasa');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/PHPMailer/src/Exception.php';
require_once dirname(__DIR__) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/PHPMailer/src/SMTP.php';
require_once dirname(__DIR__) . '/config/database.php';

$eventName = 'Creators Bomoko 2026';
$eventDates = '18–19 septembre 2026';
$eventLocation = 'Musée National de la RDC, Kinshasa';
$storageDir = dirname(__DIR__) . '/storage/creatorsbomoko';
$jsonlPath = $storageDir . '/candidatures.jsonl';
$csvPath = $storageDir . '/candidatures.csv';

$domainOptions = [
    'creation_contenu' => 'Création de contenu',
    'entrepreneuriat_numerique' => 'Entrepreneuriat numérique',
    'photo_video' => 'Photographie / Vidéographie',
    'influence_digitale' => 'Influence digitale',
    'musique_art_culture' => 'Musique / Art / Culture',
    'marketing_communication' => 'Marketing / Communication',
    'developpement_web_tech' => 'Développement web / Tech',
    'autre' => 'Autre',
];

$platformOptions = [
    'facebook' => 'Facebook',
    'instagram' => 'Instagram',
    'tiktok' => 'TikTok',
    'youtube' => 'YouTube',
    'linkedin' => 'LinkedIn',
    'x_twitter' => 'X / Twitter',
    'weibo' => 'Weibo',
    'podcast' => 'Podcast',
    'autre' => 'Autre',
];

$topicOptions = [
    'liberte_expression_reussite_numerique' => 'Liberté d’expression et réussite numérique',
    'monetisation_creativite_digitale' => 'Monétisation de la créativité digitale',
    'education_financiere_createurs' => 'Éducation financière pour les créateurs',
    'ecosysteme_numerique_rdc' => 'Connaître l’écosystème numérique de la RDC : lois, réglementations, pénétration numérique et créateurs de contenu',
    'collaboration_developper_marche' => 'Collaboration : comment développer le marché ?',
    'monetisation_conformite' => 'Monétisation : les étapes pour être en conformité',
    'abonnes_en_acheteurs' => 'Convertir les abonnés en acheteurs',
    'proteger_activite_ligne' => 'Protéger son activité en ligne',
    'sponsors_partenariats_marques' => 'Attirer des sponsors et développer des partenariats avec des marques',
    'podcasting_impact' => 'Le podcasting comme outil d’impact',
    'defis_contenu_personal_branding' => 'Défis de création de contenu et personal branding',
    'autre' => 'Autre',
];

$heardOptions = [
    'reseaux_sociaux' => 'Réseaux sociaux',
    'ami_collegue' => 'Ami / collègue',
    'espace_americain' => 'Espace américain',
    'universite_organisation' => 'Université / organisation',
    'ambassade_us' => 'Ambassade des États-Unis',
    'autre' => 'Autre',
];

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function posted(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function clean_text(string $value, int $maxLength = 500): string
{
    $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return substr($value, 0, $maxLength);
}

function clean_multiline(string $value, int $maxLength = 1800): string
{
    $value = trim(strip_tags($value));
    $value = preg_replace("/\r\n|\r/u", "\n", $value) ?? $value;
    $value = preg_replace("/\n{3,}/u", "\n\n", $value) ?? $value;
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return substr($value, 0, $maxLength);
}

function selected_label(array $options, string $key, string $other = ''): string
{
    if ($key === 'autre' && $other !== '') {
        return 'Autre : ' . $other;
    }

    return $options[$key] ?? $key;
}

function selected_labels(array $options, array $keys, string $other = ''): array
{
    $labels = [];
    foreach ($keys as $key) {
        $key = (string) $key;
        if (!isset($options[$key])) {
            continue;
        }
        $labels[] = selected_label($options, $key, $key === 'autre' ? $other : '');
    }

    return $labels;
}

function ensure_storage(string $storageDir): void
{
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0775, true);
    }

    $htaccessPath = $storageDir . '/.htaccess';
    if (!is_file($htaccessPath)) {
        file_put_contents($htaccessPath, "Require all denied\nDeny from all\n");
    }

    $indexPath = $storageDir . '/index.html';
    if (!is_file($indexPath)) {
        file_put_contents($indexPath, '');
    }
}

function save_application(string $jsonlPath, string $csvPath, array $data): void
{
    $jsonLine = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonLine === false) {
        throw new RuntimeException('Impossible de préparer la candidature.');
    }

    $jsonHandle = fopen($jsonlPath, 'ab');
    if ($jsonHandle === false) {
        throw new RuntimeException('Impossible d’ouvrir le fichier de stockage.');
    }

    try {
        if (!flock($jsonHandle, LOCK_EX)) {
            throw new RuntimeException('Impossible de verrouiller le fichier de stockage.');
        }
        fwrite($jsonHandle, $jsonLine . PHP_EOL);
        fflush($jsonHandle);
        flock($jsonHandle, LOCK_UN);
    } finally {
        fclose($jsonHandle);
    }

    $csvExists = is_file($csvPath) && filesize($csvPath) > 0;
    $csvHandle = fopen($csvPath, 'ab');
    if ($csvHandle === false) {
        throw new RuntimeException('Impossible d’ouvrir le fichier CSV.');
    }

    try {
        if (!flock($csvHandle, LOCK_EX)) {
            throw new RuntimeException('Impossible de verrouiller le fichier CSV.');
        }

        $columns = [
            'id', 'submitted_at', 'nom_complet', 'email', 'telephone', 'ville', 'age', 'profession',
            'organisation', 'domaine', 'plateformes', 'liens_plateformes', 'motivation', 'thematique',
            'disponible_presentiel', 'besoins_specifiques', 'source', 'ip', 'user_agent',
        ];

        if (!$csvExists) {
            fputcsv($csvHandle, $columns);
        }

        $row = [];
        foreach ($columns as $column) {
            $value = $data[$column] ?? '';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $row[] = (string) $value;
        }

        fputcsv($csvHandle, $row);
        fflush($csvHandle);
        flock($csvHandle, LOCK_UN);
    } finally {
        fclose($csvHandle);
    }
}

function ensure_cbomoko_tables(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS participants_cbomoko (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    submission_id VARCHAR(60) NOT NULL,
    submitted_at DATETIME NOT NULL,
    nom_complet VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL,
    telephone VARCHAR(60) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    profession VARCHAR(160) NOT NULL,
    organisation VARCHAR(180) DEFAULT NULL,
    domaine VARCHAR(220) NOT NULL,
    plateformes TEXT NOT NULL,
    liens_plateformes TEXT NOT NULL,
    experience VARCHAR(80) DEFAULT NULL,
    participation_similaire VARCHAR(10) DEFAULT NULL,
    motivation TEXT NOT NULL,
    thematique VARCHAR(240) NOT NULL,
    attentes TEXT DEFAULT NULL,
    disponible_presentiel VARCHAR(10) NOT NULL,
    infos_futures VARCHAR(10) DEFAULT NULL,
    besoins_specifiques VARCHAR(280) NOT NULL,
    source VARCHAR(220) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'nouvelle',
    notes_admin TEXT DEFAULT NULL,
    acces VARCHAR(10) DEFAULT NULL,
    heure_arrive DATETIME DEFAULT NULL,
    ip VARCHAR(80) DEFAULT NULL,
    user_agent VARCHAR(240) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_participants_cbomoko_submission_id (submission_id),
    KEY idx_participants_cbomoko_email (email),
    KEY idx_participants_cbomoko_status (status),
    KEY idx_participants_cbomoko_acces (acces),
    KEY idx_participants_cbomoko_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $stmt = $pdo->prepare('SHOW COLUMNS FROM participants_cbomoko LIKE :column_name');
    $stmt->execute([':column_name' => 'acces']);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec('ALTER TABLE participants_cbomoko ADD COLUMN acces VARCHAR(10) DEFAULT NULL AFTER notes_admin');
    }

    $stmt = $pdo->prepare('SHOW COLUMNS FROM participants_cbomoko LIKE :column_name');
    $stmt->execute([':column_name' => 'heure_arrive']);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec('ALTER TABLE participants_cbomoko ADD COLUMN heure_arrive DATETIME DEFAULT NULL AFTER acces');
    }

    $stmt = $pdo->prepare('SHOW INDEX FROM participants_cbomoko WHERE Key_name = :index_name');
    $stmt->execute([':index_name' => 'idx_participants_cbomoko_acces']);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec('ALTER TABLE participants_cbomoko ADD INDEX idx_participants_cbomoko_acces (acces)');
    }

    $legacyNullableColumns = [
        'experience' => 'experience VARCHAR(80) DEFAULT NULL',
        'participation_similaire' => 'participation_similaire VARCHAR(10) DEFAULT NULL',
        'attentes' => 'attentes TEXT DEFAULT NULL',
        'infos_futures' => 'infos_futures VARCHAR(10) DEFAULT NULL',
    ];

    foreach ($legacyNullableColumns as $columnName => $definition) {
        $stmt = $pdo->prepare('SHOW COLUMNS FROM participants_cbomoko LIKE :column_name');
        $stmt->execute([':column_name' => $columnName]);
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($column) && strtoupper((string) ($column['Null'] ?? '')) === 'NO') {
            $pdo->exec('ALTER TABLE participants_cbomoko MODIFY COLUMN ' . $definition);
        }
    }

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users_cbomoko (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_cbomoko_email (email),
    KEY idx_users_cbomoko_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
}

function save_application_to_database(PDO $pdo, array $data): void
{
    $sql = 'INSERT INTO participants_cbomoko (
        submission_id, submitted_at, nom_complet, email, telephone, ville, age, profession,
        organisation, domaine, plateformes, liens_plateformes, experience, participation_similaire,
        motivation, thematique, attentes, disponible_presentiel, infos_futures, besoins_specifiques,
        source, ip, user_agent
    ) VALUES (
        :submission_id, :submitted_at, :nom_complet, :email, :telephone, :ville, :age, :profession,
        :organisation, :domaine, :plateformes, :liens_plateformes, :experience, :participation_similaire,
        :motivation, :thematique, :attentes, :disponible_presentiel, :infos_futures, :besoins_specifiques,
        :source, :ip, :user_agent
    )';

    $stmt = $pdo->prepare($sql);
    $submittedAt = strtotime((string) ($data['submitted_at'] ?? 'now'));
    $stmt->execute([
        ':submission_id' => (string) ($data['id'] ?? ''),
        ':submitted_at' => date('Y-m-d H:i:s', $submittedAt ?: time()),
        ':nom_complet' => (string) ($data['nom_complet'] ?? ''),
        ':email' => (string) ($data['email'] ?? ''),
        ':telephone' => (string) ($data['telephone'] ?? ''),
        ':ville' => (string) ($data['ville'] ?? ''),
        ':age' => (int) ($data['age'] ?? 0),
        ':profession' => (string) ($data['profession'] ?? ''),
        ':organisation' => (string) ($data['organisation'] ?? ''),
        ':domaine' => (string) ($data['domaine'] ?? ''),
        ':plateformes' => is_array($data['plateformes'] ?? null) ? implode(', ', $data['plateformes']) : (string) ($data['plateformes'] ?? ''),
        ':liens_plateformes' => (string) ($data['liens_plateformes'] ?? ''),
        ':experience' => array_key_exists('experience', $data) ? (string) $data['experience'] : null,
        ':participation_similaire' => array_key_exists('participation_similaire', $data) ? (string) $data['participation_similaire'] : null,
        ':motivation' => (string) ($data['motivation'] ?? ''),
        ':thematique' => (string) ($data['thematique'] ?? ''),
        ':attentes' => array_key_exists('attentes', $data) ? (string) $data['attentes'] : null,
        ':disponible_presentiel' => (string) ($data['disponible_presentiel'] ?? ''),
        ':infos_futures' => array_key_exists('infos_futures', $data) ? (string) $data['infos_futures'] : null,
        ':besoins_specifiques' => (string) ($data['besoins_specifiques'] ?? ''),
        ':source' => (string) ($data['source'] ?? ''),
        ':ip' => (string) ($data['ip'] ?? ''),
        ':user_agent' => (string) ($data['user_agent'] ?? ''),
    ]);
}

function send_candidate_acknowledgement(string $recipientEmail, string $recipientName, string $submissionId, string $eventName, string $eventDates, string $eventLocation): array
{
    try {
        $smtpHost = getenv('CREATORSBOMOKO_SMTP_HOST') ?: 'invitationspeciale.com';
        $smtpUser = getenv('CREATORSBOMOKO_SMTP_USER') ?: 'creatorsbomoko@invitationspeciale.com';
        $smtpPassword = getenv('CREATORSBOMOKO_SMTP_PASSWORD') ?: '';
        $smtpPort = (int) (getenv('CREATORSBOMOKO_SMTP_PORT') ?: 587);
        if ($smtpPassword === '') {
            return ['success' => false, 'message' => 'Configuration SMTP manquante.'];
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->Port = $smtpPort;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($smtpUser, 'Creators Bomoko');
        $mail->addReplyTo($smtpUser, 'Creators Bomoko');
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->isHTML(true);

        $safeName = h($recipientName !== '' ? $recipientName : 'cher candidat');
        $safeSubmissionId = h($submissionId);
        $safeEventName = h($eventName);
        $safeEventDates = h($eventDates);
        $safeEventLocation = h($eventLocation);

        $mail->Subject = 'Accusé de réception - ' . $eventName;
        $mail->Body = '
            <div style="font-family:Inter,Arial,sans-serif;font-size:15px;line-height:1.7;color:#25140b;background:#fffaf1;padding:24px;border-radius:18px;">
                <h1 style="margin:0 0 14px;color:#8b4a1f;font-size:24px;">Candidature reçue</h1>
                <p>Bonjour ' . $safeName . ',</p>
                <p>Nous accusons réception de votre candidature à <strong>' . $safeEventName . '</strong>.</p>
                <ul>
                    <li><strong>Référence :</strong> ' . $safeSubmissionId . '</li>
                    <li><strong>Dates :</strong> ' . $safeEventDates . '</li>
                    <li><strong>Lieu :</strong> ' . $safeEventLocation . '</li>
                </ul>
                <p>Les places étant limitées, seules les personnes sélectionnées recevront une confirmation officielle de participation.</p>
                <p style="margin-top:24px;">Merci,<br><strong>L’équipe Creators Bomoko</strong></p>
            </div>';
        $mail->AltBody = "Bonjour {$recipientName},\n\nNous accusons réception de votre candidature à {$eventName}.\n\nRéférence : {$submissionId}\nDates : {$eventDates}\nLieu : {$eventLocation}\n\nLes places étant limitées, seules les personnes sélectionnées recevront une confirmation officielle de participation.\n\nMerci,\nL'équipe Creators Bomoko";
        $mail->send();

        return ['success' => true];
    } catch (Throwable $exception) {
        error_log('[Creators Bomoko Mail] ' . $exception->getMessage());

        return [
            'success' => false,
            'message' => $exception->getMessage(),
        ];
    }
}

function cbomoko_partner_logos(): array
{
    $directory = __DIR__ . '/images/parteners';
    if (!is_dir($directory)) {
        return [];
    }

    $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
    $filesByBaseName = [];
    foreach (scandir($directory) ?: [] as $file) {
        if ($file === '.' || $file === '..' || str_starts_with($file, '.')) {
            continue;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            continue;
        }

        $filesByBaseName[trim(pathinfo($file, PATHINFO_FILENAME))] = $file;
    }

    $logos = [];
    $orderedRows = [
        'featured' => ['7', '8'],
        'standard' => ['2', '5', '4', '3', '1', '6'],
    ];

    foreach ($orderedRows as $tier => $baseNames) {
        foreach ($baseNames as $baseName) {
            if (!isset($filesByBaseName[$baseName])) {
                continue;
            }

            $file = $filesByBaseName[$baseName];
            $logos[] = [
                'src' => 'images/parteners/' . rawurlencode($file),
                'name' => $baseName,
                'tier' => $tier,
            ];
            unset($filesByBaseName[$baseName]);
        }
    }

    foreach ($filesByBaseName as $baseName => $file) {
        $logos[] = [
            'src' => 'images/parteners/' . rawurlencode($file),
            'name' => $baseName,
            'tier' => 'standard',
        ];
    }

    return $logos;
}

if (empty($_SESSION['creatorsbomoko_csrf'])) {
    $_SESSION['creatorsbomoko_csrf'] = bin2hex(random_bytes(32));
}

$errors = [];
$successId = clean_text((string) ($_GET['merci'] ?? ''), 40);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string) posted('csrf_token');
    $honeypot = trim((string) posted('website'));

    if ($honeypot !== '') {
        $errors['global'] = 'Votre candidature n’a pas pu être envoyée.';
    }

    if (!hash_equals((string) $_SESSION['creatorsbomoko_csrf'], $csrfToken)) {
        $errors['global'] = 'Session expirée. Veuillez réessayer.';
    }

    $fullName = clean_text((string) posted('nom_complet'), 180);
    $email = filter_var(trim((string) posted('email')), FILTER_SANITIZE_EMAIL);
    $phone = clean_text((string) posted('telephone'), 60);
    $city = clean_text((string) posted('ville'), 100);
    $age = (int) posted('age');
    $profession = clean_text((string) posted('profession'), 160);
    $organization = clean_text((string) posted('organisation'), 180);

    $domain = clean_text((string) posted('domaine'), 80);
    $domainOther = clean_text((string) posted('domaine_autre'), 160);
    $platforms = array_values(array_filter((array) posted('plateformes', []), static fn ($value): bool => is_string($value)));
    $platformOther = clean_text((string) posted('plateforme_autre'), 160);
    $platformLinks = clean_multiline((string) posted('liens_plateformes'), 1200);
    $motivation = clean_multiline((string) posted('motivation'), 1800);
    $topic = clean_text((string) posted('thematique'), 100);
    $topicOther = clean_text((string) posted('thematique_autre'), 180);
    $available = clean_text((string) posted('disponible_presentiel'), 10);
    $specificNeeds = clean_text((string) posted('besoins_specifiques'), 10);
    $specificNeedsDetails = clean_text((string) posted('besoins_specifiques_detail'), 240);
    $heard = clean_text((string) posted('source'), 100);
    $heardOther = clean_text((string) posted('source_autre'), 160);

    if ($fullName === '') {
        $errors['nom_complet'] = 'Indiquez votre nom complet.';
    }
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Indiquez une adresse e-mail valide.';
    }
    if ($phone === '') {
        $errors['telephone'] = 'Indiquez votre numéro de téléphone / WhatsApp.';
    }
    if ($city === '') {
        $errors['ville'] = 'Indiquez votre ville de résidence.';
    }
    if ($age < 13 || $age > 99) {
        $errors['age'] = 'Indiquez un âge valide.';
    }
    if ($profession === '') {
        $errors['profession'] = 'Indiquez votre profession ou activité principale.';
    }
    if (!isset($domainOptions[$domain])) {
        $errors['domaine'] = 'Choisissez un domaine principal.';
    } elseif ($domain === 'autre' && $domainOther === '') {
        $errors['domaine_autre'] = 'Précisez votre domaine.';
    }
    if ($platforms === []) {
        $errors['plateformes'] = 'Choisissez au moins une plateforme.';
    } else {
        foreach ($platforms as $platform) {
            if (!isset($platformOptions[$platform])) {
                $errors['plateformes'] = 'Choix de plateforme invalide.';
                break;
            }
        }
        if (in_array('autre', $platforms, true) && $platformOther === '') {
            $errors['plateforme_autre'] = 'Précisez l’autre plateforme.';
        }
    }
    if ($platformLinks === '') {
        $errors['liens_plateformes'] = 'Partagez au moins un lien vers vos plateformes ou pages professionnelles.';
    }
    if ($motivation === '') {
        $errors['motivation'] = 'Expliquez pourquoi vous souhaitez participer.';
    }
    if (!isset($topicOptions[$topic])) {
        $errors['thematique'] = 'Choisissez la thématique qui vous intéresse le plus.';
    } elseif ($topic === 'autre' && $topicOther === '') {
        $errors['thematique_autre'] = 'Précisez la thématique.';
    }
    if (!in_array($available, ['oui', 'non'], true)) {
        $errors['disponible_presentiel'] = 'Confirmez votre disponibilité.';
    }
    if (!in_array($specificNeeds, ['oui', 'non'], true)) {
        $errors['besoins_specifiques'] = 'Choisissez une réponse.';
    } elseif ($specificNeeds === 'oui' && $specificNeedsDetails === '') {
        $errors['besoins_specifiques_detail'] = 'Précisez vos besoins spécifiques.';
    }
    if (!isset($heardOptions[$heard])) {
        $errors['source'] = 'Choisissez comment vous avez entendu parler de l’événement.';
    } elseif ($heard === 'autre' && $heardOther === '') {
        $errors['source_autre'] = 'Précisez la source.';
    }

    if ($errors === []) {
        try {
            ensure_storage($storageDir);
            ensure_cbomoko_tables($pdo);

            $submissionId = 'CBK-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $application = [
                'id' => $submissionId,
                'submitted_at' => date('c'),
                'nom_complet' => $fullName,
                'email' => (string) $email,
                'telephone' => $phone,
                'ville' => $city,
                'age' => (string) $age,
                'profession' => $profession,
                'organisation' => $organization,
                'domaine' => selected_label($domainOptions, $domain, $domainOther),
                'plateformes' => selected_labels($platformOptions, $platforms, $platformOther),
                'liens_plateformes' => $platformLinks,
                'motivation' => $motivation,
                'thematique' => selected_label($topicOptions, $topic, $topicOther),
                'disponible_presentiel' => $available === 'oui' ? 'Oui' : 'Non',
                'besoins_specifiques' => $specificNeeds === 'oui' ? 'Oui : ' . $specificNeedsDetails : 'Non',
                'source' => selected_label($heardOptions, $heard, $heardOther),
                'ip' => clean_text((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 80),
                'user_agent' => clean_text((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 240),
            ];

            save_application_to_database($pdo, $application);
            save_application($jsonlPath, $csvPath, $application);
            $mailResult = send_candidate_acknowledgement((string) $email, $fullName, $submissionId, $eventName, $eventDates, $eventLocation);
            $_SESSION['creatorsbomoko_flash'] = [
                'icon' => $mailResult['success'] ? 'success' : 'warning',
                'title' => 'Candidature reçue !',
                'text' => $mailResult['success']
                    ? 'Votre candidature a été enregistrée. Un accusé de réception a été envoyé à votre adresse e-mail.'
                    : 'Votre candidature a été enregistrée, mais l’accusé de réception par e-mail n’a pas pu être envoyé automatiquement.',
            ];
            $_SESSION['creatorsbomoko_csrf'] = bin2hex(random_bytes(32));

            $redirectPath = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/creatorsbomoko/'), '?') ?: '/creatorsbomoko/';
            header('Location: ' . $redirectPath . '?merci=' . rawurlencode($submissionId));
            exit;
        } catch (Throwable $exception) {
            error_log('[Creators Bomoko] ' . $exception->getMessage());
            $errors['global'] = 'Une erreur est survenue pendant l’envoi. Veuillez réessayer plus tard.';
        }
    }
}

$csrfToken = (string) $_SESSION['creatorsbomoko_csrf'];
$basePath = rtrim(str_replace('index.php', '', (string) ($_SERVER['SCRIPT_NAME'] ?? '/creatorsbomoko/')), '/');
$canonicalUrl = '';
if (!empty($_SERVER['HTTP_HOST'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $canonicalUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . ($basePath !== '' ? $basePath : '/creatorsbomoko');
}

$partnerLogos = cbomoko_partner_logos();

$sweetAlert = null;
if (isset($_SESSION['creatorsbomoko_flash']) && is_array($_SESSION['creatorsbomoko_flash'])) {
    $sweetAlert = [
        'icon' => (string) ($_SESSION['creatorsbomoko_flash']['icon'] ?? 'success'),
        'title' => (string) ($_SESSION['creatorsbomoko_flash']['title'] ?? 'Candidature reçue !'),
        'text' => (string) ($_SESSION['creatorsbomoko_flash']['text'] ?? 'Votre candidature a bien été enregistrée.'),
        'confirmButtonText' => 'OK',
        'confirmButtonColor' => '#8b4a1f',
    ];
    unset($_SESSION['creatorsbomoko_flash']);
} elseif ($successId !== '') {
    $sweetAlert = [
        'icon' => 'success',
        'title' => 'Candidature reçue !',
        'text' => 'Votre candidature a bien été enregistrée. Référence : ' . $successId,
        'confirmButtonText' => 'OK',
        'confirmButtonColor' => '#8b4a1f',
    ];
} elseif ($errors !== []) {
    $firstError = (string) ($errors['global'] ?? reset($errors));
    $sweetAlert = [
        'icon' => 'warning',
        'title' => 'Formulaire incomplet',
        'text' => $firstError,
        'confirmButtonText' => 'OK',
        'confirmButtonColor' => '#8b4a1f',
    ];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($eventName); ?> | Formulaire de candidature</title>
    <meta name="description" content="Formulaire de candidature pour Creators Bomoko 2026, conférence numérique organisée à Kinshasa du 18 au 19 septembre 2026.">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="theme-color" content="#3d2010">
    <link rel="icon" type="image/png" href="images/favicom.png">
    <link rel="shortcut icon" type="image/png" href="images/favicom.png">
    <link rel="apple-touch-icon" href="images/favicom.png">
    <?php if ($canonicalUrl !== ''): ?>
        <link rel="canonical" href="<?php echo h($canonicalUrl); ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?php echo h($eventName); ?> | Candidature">
    <meta property="og:description" content="Conférence numérique à Kinshasa pour créateurs de contenu, entrepreneurs, innovateurs et acteurs des industries créatives.">
    <meta property="og:type" content="website">
    <?php if ($canonicalUrl !== ''): ?>
        <meta property="og:url" content="<?php echo h($canonicalUrl); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --ink:#25140b;
            --muted:#725c45;
            --line:#ead7bd;
            --paper:#fffaf1;
            --bg:#f8efe2;
            --blue:#0a3a73;
            --blue-2:#1162b5;
            --red:#d7354a;
            --cyan:#16b5a8;
            --gold:#d88b2f;
            --wood:#8b4a1f;
            --wood-dark:#35180b;
            --wood-mid:#b66a28;
            --cream:#fff4df;
            --ok:#0f766e;
            --danger:#b42318;
            --radius:28px;
            --shadow:0 24px 80px rgba(67, 36, 15, .16);
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            margin:0;
            font-family:Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color:var(--ink);
            background:
                radial-gradient(circle at 10% 0%, rgba(22,181,168,.17), transparent 32rem),
                radial-gradient(circle at 85% 10%, rgba(215,53,74,.14), transparent 28rem),
                radial-gradient(circle at 50% 52%, rgba(216,139,47,.16), transparent 24rem),
                linear-gradient(180deg, #fff9ee 0%, #f4e6d2 48%, #fbf7ef 100%);
            line-height:1.55;
        }
        a{color:inherit}
        .page-shell{overflow:hidden; min-height:100vh}
        .hero{
            position:relative;
            padding:32px clamp(18px, 4vw, 56px) 70px;
            color:#fff;
            background:
                radial-gradient(circle at 80% 12%, rgba(22,181,168,.28), transparent 24rem),
                radial-gradient(circle at 18% 76%, rgba(215,53,74,.22), transparent 24rem),
                repeating-linear-gradient(105deg, rgba(255,255,255,.035) 0 2px, transparent 2px 13px),
                linear-gradient(135deg, rgba(53,24,11,.98), rgba(139,74,31,.94) 46%, rgba(10,58,115,.9));
            isolation:isolate;
        }
        .hero:before,
        .hero:after{
            content:"";
            position:absolute;
            border-radius:999px;
            filter:blur(2px);
            opacity:.78;
            z-index:-1;
        }
        .hero:before{width:360px;height:360px;right:-110px;top:-90px;background:rgba(22,181,168,.34)}
        .hero:after{width:300px;height:300px;left:-110px;bottom:-120px;background:rgba(216,139,47,.28)}
        .nav{
            width:min(1180px, 100%);
            margin:0 auto 52px;
            display:none;
            align-items:center;
            justify-content:space-between;
            gap:20px;
        }
        .brand{display:flex;align-items:center;gap:14px;font-weight:900;letter-spacing:-.03em;color:#fff;text-decoration:none}
        .brand-logo-frame{
            width:clamp(360px, 44vw, 680px);border-radius:34px;
            display:grid;place-items:center;
            padding:0;
            background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,250,241,.92));
            border:1px solid rgba(255,255,255,.55);
            box-shadow:0 24px 70px rgba(0,0,0,.3), inset 0 1px 0 rgba(255,255,255,.9);
            overflow:hidden;
        }
        .brand-logo{width:100%;height:auto;object-fit:contain;display:block}
        .brand-text{display:none}
        .nav-pill{
            display:inline-flex;align-items:center;gap:9px;
            padding:10px 15px;border:1px solid rgba(255,244,223,.28);
            border-radius:999px;background:rgba(255,244,223,.12);
            color:#fff4df;font-weight:700;font-size:13px;text-decoration:none;
        }
        .hero-grid{
            width:min(1180px, 100%);
            margin:0 auto;
            display:grid;
            grid-template-columns:minmax(0, 1.08fr) minmax(360px, 420px);
            gap:40px;
            align-items:center;
        }
        .eyebrow{
            display:inline-flex;align-items:center;gap:10px;
            padding:8px 12px;border-radius:999px;
            background:rgba(255,244,223,.14);border:1px solid rgba(255,244,223,.22);
            color:#fff4df;font-weight:800;font-size:13px;text-transform:uppercase;letter-spacing:.08em;
        }
        .dot{width:9px;height:9px;border-radius:99px;background:var(--cyan);box-shadow:0 0 0 8px rgba(22,181,168,.18)}
        h1{
            font-family:"Space Grotesk", Inter, sans-serif;
            font-size:clamp(42px, 7vw, 86px);
            line-height:.92;
            letter-spacing:-.07em;
            margin:28px 0 24px;
        }
        .lead{font-size:clamp(17px, 2vw, 22px);max-width:780px;color:#fff2df;margin:0 0 28px}
        .hero-actions{display:flex;gap:14px;flex-wrap:wrap;align-items:center}
        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:10px;
            border:0;border-radius:999px;padding:15px 22px;
            font-weight:900;text-decoration:none;cursor:pointer;font-size:15px;
            transition:.2s transform ease, .2s box-shadow ease, .2s background ease;
        }
        .btn:hover{transform:translateY(-2px)}
        .btn-primary{background:var(--cream);color:var(--wood-dark);box-shadow:0 16px 40px rgba(0,0,0,.2)}
        .btn-secondary{background:rgba(255,244,223,.12);color:#fff;border:1px solid rgba(255,244,223,.22)}
        .hero-visual{display:flex;align-items:stretch}
        .hero-logo-card{
            position:relative;
            display:grid;
            align-items:center;
            margin:0 0 24px;
            border-radius:28px;
            padding:18px 20px;
            background:
                radial-gradient(circle at 96% 8%, rgba(22,181,168,.18), transparent 8rem),
                linear-gradient(135deg, rgba(255,255,255,.98), rgba(255,250,241,.86));
            border:1px solid rgba(255,244,223,.54);
            box-shadow:inset 0 1px 0 rgba(255,255,255,.86), 0 22px 58px rgba(0,0,0,.22);
            overflow:hidden;
        }
        .hero-logo-card:before{
            content:"";
            position:absolute;
            width:130px;height:130px;
            right:-56px;top:-66px;
            border-radius:999px;
            background:rgba(215,53,74,.12);
            pointer-events:none;
        }
        .hero-logo{
            position:relative;z-index:1;
            display:block;width:100%;height:auto;object-fit:contain;
            margin:0 auto;padding:0;border-radius:0;
            background:transparent;
            filter:drop-shadow(0 10px 18px rgba(53,24,11,.16));
        }
        .logo-copy{display:none}
        .logo-tag{margin:0;color:var(--wood-dark);font-family:"Space Grotesk", Inter, sans-serif;font-size:22px;line-height:1;font-weight:900;letter-spacing:-.04em}
        .logo-subtag{display:block;margin-top:6px;color:#6b4a31;font-weight:800;font-size:12px;text-transform:uppercase;letter-spacing:.08em}
        .event-card{
            width:100%;
            text-align:left;
            background:rgba(44,21,9,.55);
            border:1px solid rgba(255,244,223,.22);
            border-radius:var(--radius);
            padding:28px;
            backdrop-filter:blur(22px);
            box-shadow:0 24px 70px rgba(0,0,0,.18);
        }
        .event-card h2{margin:0 0 18px;font-size:24px;letter-spacing:-.03em}
        .facts{display:grid;gap:14px;margin:0;padding:0;list-style:none}
        .facts li{display:flex;gap:12px;align-items:flex-start;color:#fff8ed}
        .fact-icon{width:34px;height:34px;flex:0 0 34px;border-radius:12px;display:grid;place-items:center;background:rgba(255,244,223,.16)}
        .notice{margin-top:22px;padding:15px;border-radius:20px;background:rgba(216,139,47,.18);border:1px solid rgba(216,139,47,.34);color:#fff7ed;font-weight:700}
        main{padding:0 clamp(18px, 4vw, 56px) 70px}
        .intro{
            width:min(1180px, 100%);
            margin:-38px auto 30px;
            position:relative;
            z-index:5;
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:16px;
        }
        .intro-card{
            background:var(--paper);
            border:1px solid rgba(15,23,42,.08);
            border-radius:24px;
            padding:22px;
            box-shadow:var(--shadow);
        }
        .intro-card strong{display:block;font-size:26px;letter-spacing:-.04em;color:var(--wood)}
        .intro-card span{color:var(--muted);font-weight:650}
        .form-wrap{
            width:min(1180px, 100%);
            margin:0 auto;
            display:grid;
            grid-template-columns:minmax(0, .82fr) minmax(0, 1.18fr);
            gap:28px;
            align-items:start;
        }
        .side-panel,
        form{
            background:rgba(255,250,241,.94);
            border:1px solid rgba(15,23,42,.08);
            border-radius:var(--radius);
            box-shadow:var(--shadow);
        }
        .side-panel{position:sticky;top:18px;padding:28px}
        .side-panel h2,
        form h2{font-family:"Space Grotesk", Inter, sans-serif;letter-spacing:-.05em;line-height:1.05;margin:0 0 14px;font-size:clamp(28px,3vw,42px)}
        .side-panel p{color:var(--muted);margin:0 0 18px}
        .chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}
        .chip{padding:9px 12px;border-radius:999px;background:#f3dfc2;color:var(--wood-dark);font-weight:800;font-size:13px}
        form{padding:clamp(22px,4vw,38px)}
        .form-section{padding:26px 0;border-top:1px solid var(--line)}
        .form-section:first-of-type{border-top:0;padding-top:6px}
        .section-title{display:flex;align-items:center;gap:12px;margin:0 0 18px;font-size:20px;font-weight:900;letter-spacing:-.02em}
        .num{width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,var(--wood),var(--cyan));color:#fff;display:grid;place-items:center;font-weight:900;font-size:14px}
        .grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .field{margin-bottom:16px}
        label{display:block;font-weight:850;margin:0 0 8px;color:#16243a}
        .hint{font-size:13px;color:var(--muted);margin-top:-4px;margin-bottom:8px}
        input[type="text"],input[type="email"],input[type="tel"],input[type="number"],textarea{
            width:100%;border:1px solid #cbd5e1;border-radius:16px;background:#fff;
            padding:14px 15px;font:inherit;color:var(--ink);outline:0;transition:.16s border ease,.16s box-shadow ease;
        }
        textarea{min-height:126px;resize:vertical}
        input:focus,textarea:focus{border-color:var(--wood-mid);box-shadow:0 0 0 4px rgba(182,106,40,.14)}
        .options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
        .option{
            display:flex;align-items:flex-start;gap:10px;
            padding:12px;border:1px solid #dbe4ee;border-radius:16px;background:#fff;
            font-weight:750;color:#20304a;cursor:pointer;
        }
        .option:hover{border-color:rgba(139,74,31,.42);background:#fff8ed}
        .option input{margin-top:4px;accent-color:var(--wood-mid)}
        .inline-other{margin-top:10px}
        .conditional-field{display:none}
        .conditional-field.is-visible{display:block}
        .error{color:var(--danger);font-weight:800;font-size:13px;margin-top:7px}
        .alert{border-radius:22px;padding:16px 18px;margin:0 0 22px;font-weight:750}
        .alert-error{background:#fff1f0;border:1px solid #ffccc7;color:var(--danger)}
        .alert-success{background:#ecfdf5;border:1px solid #a7f3d0;color:var(--ok)}
        .consent-box{background:#f8fafc;border:1px solid #dbe4ee;border-radius:22px;padding:18px;color:#475569;font-size:14px}
        .submit-row{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-top:24px;flex-wrap:wrap}
        .submit-row p{margin:0;color:var(--muted);font-size:13px;max-width:430px}
        .btn-submit{background:linear-gradient(135deg,var(--red),var(--wood),var(--blue));color:#fff;box-shadow:0 16px 36px rgba(139,74,31,.28)}
        .hidden-field{position:absolute;left:-9999px;opacity:0;pointer-events:none}
        .partners-section{width:min(1180px,100%);margin:30px auto 18px;display:grid;gap:16px}
        .partners-grid{display:flex;justify-content:center;align-items:stretch;gap:14px;width:100%}
        .partner-logo-card{display:grid;place-items:center;text-align:center;padding:16px;border-radius:22px;background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 14px 34px rgba(67,36,15,.08)}
        .partner-logo-card img{max-width:100%;max-height:78px;object-fit:contain;filter:saturate(1.04)}
        .partners-grid--featured .partner-logo-card{width:min(360px,calc(50% - 7px));min-height:150px;padding:22px}
        .partners-grid--featured .partner-logo-card img{max-height:114px}
        .partners-grid--standard{gap:12px}
        .partners-grid--standard .partner-logo-card{width:150px;min-height:96px;padding:12px;border-radius:18px}
        .partners-grid--standard .partner-logo-card img{max-height:62px}
        .public-footer{width:min(1180px,100%);margin:0 auto;padding:24px clamp(18px,4vw,34px);color:var(--muted);display:grid;justify-items:center;text-align:center;gap:12px;font-weight:400}
        .footer-separator{width:100%;border:0;border-top:1px solid var(--line);margin:0 0 4px}
        .public-footer__text{line-height:1.55}
        .public-footer__logos{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap}
        .public-footer__logos img{width:90px;height:66px;object-fit:contain;background:transparent;border-radius:0;padding:0}
        .public-footer__logos img.is-footer-logo{width:150px}
        @media (max-width: 980px){
            .hero-grid,.form-wrap{grid-template-columns:1fr}
            .intro{grid-template-columns:1fr}
            .side-panel{position:static}
        }
        @media (max-width: 680px){
            .hero{padding-top:20px}
            .nav{margin-bottom:34px;display:grid;justify-items:center;text-align:center}
            .brand{width:100%;justify-content:center}
            .brand-logo-frame{width:min(100%, 360px);padding:0;border-radius:22px;margin:0 auto}
            .brand-logo{margin:0 auto}
            .nav-pill{display:none}
            .hero-grid{justify-items:center;text-align:center}
            .eyebrow{margin:0 auto;justify-content:center}
            .hero-actions{justify-content:center}
            .hero-logo-card{display:none}
            .event-card{text-align:left}
            .hero-logo{height:auto}
            .logo-tag{font-size:19px}
            .logo-subtag{font-size:10px}
            .grid-2,.options{grid-template-columns:1fr}
            .event-card{padding:22px}
            .partners-section{gap:10px}
            .partners-grid{gap:8px}
            .partners-grid--featured .partner-logo-card{width:calc(50% - 4px);min-height:96px;padding:10px;border-radius:16px}
            .partners-grid--featured .partner-logo-card img{max-height:72px}
            .partners-grid--standard{gap:6px}
            .partners-grid--standard .partner-logo-card{width:calc((100% - 30px) / 6);min-height:58px;padding:5px;border-radius:12px}
            .partners-grid--standard .partner-logo-card img{max-height:40px}
            .submit-row .btn{width:100%}
            .public-footer{text-align:center}
            .public-footer__logos img.is-footer-logo{width:130px}
        }
    </style>
</head>
<body>
<div class="page-shell">
    <header class="hero">
        <nav class="nav" aria-label="Navigation principale">
            <a class="brand" href="index.php" aria-label="Rafraîchir la page Creators Bomoko">
                <span class="brand-logo-frame" aria-hidden="true">
                    <img class="brand-logo" src="images/CB Horizontal Dark BG.png" alt="">
                </span>
                <span class="brand-text">Creators Bomoko</span>
            </a>
            <a class="nav-pill" href="#formulaire">Soumettre ma candidature</a>
        </nav>

        <div class="hero-grid">
            <div>
                <span class="eyebrow"><span class="dot"></span> Formulaire de candidature</span>
                <h1>Creators Bomoko 2026</h1>
                <p class="lead">Êtes-vous créateur de contenu ?<br>Aspirez-vous à devenir créateur de contenu ?<br><br>L’ambassade des États-Unis à Kinshasa vous invite à Creators Bomoko !<br><br>Un événement de deux jours pour transformer votre passion en entreprise, développer votre influence et rejoindre un mouvement qui connecte les créateurs congolais aux opportunités mondiales.<br><br>Rejoignez le mouvement.<br><br>Lieu : Musée National Congolais, Kinshasa<br><br>Date : 18-19 Septembre 2026<br>Participation gratuite mais inscription Obligatoire !</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="#formulaire">Candidater maintenant</a>
                    <a class="btn btn-secondary" href="#details">Voir les détails</a>
                </div>
            </div>
            <div class="hero-visual">
                <aside class="event-card" id="details">
                    <div class="hero-logo-card" aria-label="Logo Creators Bomoko">
                        <img class="hero-logo" src="images/CB Horizontal White BG.png" alt="Creators Bomoko">
                        <div class="logo-copy">
                            <p class="logo-tag">Creators Bomoko</p>
                            <span class="logo-subtag">U.S. Embassy Kinshasa</span>
                        </div>
                    </div>
                    <h2>Informations clés</h2>
                    <ul class="facts">
                        <li><span class="fact-icon">📅</span><span><strong>Dates</strong><br><?php echo h($eventDates); ?></span></li>
                        <li><span class="fact-icon">📍</span><span><strong>Lieu</strong><br><?php echo h($eventLocation); ?></span></li>
                        <li><span class="fact-icon">🎙️</span><span><strong>Programme</strong><br>Conversations inspirantes, ateliers interactifs, rencontres inédites et exploration des nouvelles tendances, plateformes digitales et opportunités de l’économie créative.</span></li>
                    </ul>
                    <div class="notice">⚠️ Les places sont limitées : seules les personnes sélectionnées recevront une confirmation officielle de participation.</div>
                </aside>
            </div>
        </div>
    </header>

    <main>
        <section class="intro" aria-label="Résumé de l’événement">
            <div class="intro-card"><strong>2 jours</strong><span>de panels et ateliers pratiques</span></div>
            <div class="intro-card"><strong>Kinshasa</strong><span>au Musée National de la RDC</span></div>
            <div class="intro-card"><strong>Sélection</strong><span>sur base des candidatures reçues</span></div>
        </section>

        <section class="form-wrap" id="formulaire">
            <aside class="side-panel">
                <h2>Soumettre une candidature</h2>
                <p>Vous avez une voix, une vision ou un projet qui mérite d’être vu ? Creators Bomoko est fait pour vous.</p>
                <p>Remplissez ce formulaire pour proposer votre candidature et rejoindre une nouvelle génération de créateurs qui inventent, influencent et font bouger les lignes.</p>
                <div class="chips">
                    <span class="chip">Créateurs</span>
                    <span class="chip">Entrepreneurs</span>
                    <span class="chip">Tech</span>
                    <span class="chip">Culture</span>
                    <span class="chip">Innovation</span>
                </div>
            </aside>

            <form method="post" action="#formulaire" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                <div class="hidden-field" aria-hidden="true">
                    <label for="website">Site web</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <h2>Formulaire de candidature</h2>
                <?php if ($successId !== ''): ?>
                    <div class="alert alert-success">Votre candidature a bien été enregistrée. Référence : <strong><?php echo h($successId); ?></strong>.</div>
                <?php endif; ?>
                <?php if (isset($errors['global'])): ?>
                    <div class="alert alert-error"><?php echo h($errors['global']); ?></div>
                <?php endif; ?>

                <div class="form-section">
                    <h3 class="section-title"><span class="num">1</span> Informations personnelles</h3>
                    <div class="grid-2">
                        <div class="field">
                            <label for="nom_complet">Nom complet *</label>
                            <input type="text" id="nom_complet" name="nom_complet" value="<?php echo h((string) posted('nom_complet')); ?>" autocomplete="name" required>
                            <?php if (isset($errors['nom_complet'])): ?><div class="error"><?php echo h($errors['nom_complet']); ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="email">Adresse e-mail *</label>
                            <input type="email" id="email" name="email" value="<?php echo h((string) posted('email')); ?>" autocomplete="email" required>
                            <?php if (isset($errors['email'])): ?><div class="error"><?php echo h($errors['email']); ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="telephone">Numéro de téléphone / WhatsApp *</label>
                            <input type="tel" id="telephone" name="telephone" value="<?php echo h((string) posted('telephone')); ?>" autocomplete="tel" required>
                            <?php if (isset($errors['telephone'])): ?><div class="error"><?php echo h($errors['telephone']); ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="ville">Ville de résidence *</label>
                            <input type="text" id="ville" name="ville" value="<?php echo h((string) posted('ville')); ?>" required>
                            <?php if (isset($errors['ville'])): ?><div class="error"><?php echo h($errors['ville']); ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="age">Âge *</label>
                            <input type="number" id="age" name="age" min="13" max="99" value="<?php echo h((string) posted('age')); ?>" required>
                            <?php if (isset($errors['age'])): ?><div class="error"><?php echo h($errors['age']); ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="profession">Profession / activité principale *</label>
                            <input type="text" id="profession" name="profession" value="<?php echo h((string) posted('profession')); ?>" required>
                            <?php if (isset($errors['profession'])): ?><div class="error"><?php echo h($errors['profession']); ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="field">
                        <label for="organisation">Organisation, entreprise ou plateforme représentée</label>
                        <input type="text" id="organisation" name="organisation" value="<?php echo h((string) posted('organisation')); ?>" placeholder="Si applicable">
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><span class="num">2</span> Profil numérique et créatif</h3>
                    <div class="field">
                        <label>Dans quel domaine évoluez-vous principalement ? *</label>
                        <div class="options">
                            <?php foreach ($domainOptions as $value => $label): ?>
                                <label class="option"><input type="radio" name="domaine" value="<?php echo h($value); ?>" <?php echo posted('domaine') === $value ? 'checked' : ''; ?>> <span><?php echo h($label); ?></span></label>
                            <?php endforeach; ?>
                        </div>
                        <input class="inline-other conditional-field <?php echo posted('domaine') === 'autre' ? 'is-visible' : ''; ?>" data-conditional-field="domaine_autre" type="text" name="domaine_autre" value="<?php echo h((string) posted('domaine_autre')); ?>" placeholder="Si autre, précisez" <?php echo posted('domaine') === 'autre' ? '' : 'disabled'; ?>>
                        <?php if (isset($errors['domaine'])): ?><div class="error"><?php echo h($errors['domaine']); ?></div><?php endif; ?>
                        <?php if (isset($errors['domaine_autre'])): ?><div class="error"><?php echo h($errors['domaine_autre']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label>Quelles plateformes utilisez-vous principalement ? *</label>
                        <div class="options">
                            <?php $postedPlatforms = (array) posted('plateformes', []); ?>
                            <?php foreach ($platformOptions as $value => $label): ?>
                                <label class="option"><input type="checkbox" name="plateformes[]" value="<?php echo h($value); ?>" <?php echo in_array($value, $postedPlatforms, true) ? 'checked' : ''; ?>> <span><?php echo h($label); ?></span></label>
                            <?php endforeach; ?>
                        </div>
                        <input class="inline-other conditional-field <?php echo in_array('autre', $postedPlatforms, true) ? 'is-visible' : ''; ?>" data-conditional-field="plateforme_autre" type="text" name="plateforme_autre" value="<?php echo h((string) posted('plateforme_autre')); ?>" placeholder="Si autre, précisez" <?php echo in_array('autre', $postedPlatforms, true) ? '' : 'disabled'; ?>>
                        <?php if (isset($errors['plateformes'])): ?><div class="error"><?php echo h($errors['plateformes']); ?></div><?php endif; ?>
                        <?php if (isset($errors['plateforme_autre'])): ?><div class="error"><?php echo h($errors['plateforme_autre']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="liens_plateformes">Liens de vos plateformes ou pages professionnelles *</label>
                        <p class="hint">Ajoutez un lien par ligne si possible.</p>
                        <textarea id="liens_plateformes" name="liens_plateformes" required><?php echo h((string) posted('liens_plateformes')); ?></textarea>
                        <?php if (isset($errors['liens_plateformes'])): ?><div class="error"><?php echo h($errors['liens_plateformes']); ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><span class="num">3</span> Motivation et thématique</h3>
                    <div class="field">
                        <label for="motivation">Pourquoi souhaitez-vous participer à Creators Bomoko ? *</label>
                        <textarea id="motivation" name="motivation" required><?php echo h((string) posted('motivation')); ?></textarea>
                        <?php if (isset($errors['motivation'])): ?><div class="error"><?php echo h($errors['motivation']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label>Quel sujet ou thématique vous intéresse le plus durant la conférence ? *</label>
                        <div class="options">
                            <?php foreach ($topicOptions as $value => $label): ?>
                                <label class="option"><input type="radio" name="thematique" value="<?php echo h($value); ?>" <?php echo posted('thematique') === $value ? 'checked' : ''; ?>> <span><?php echo h($label); ?></span></label>
                            <?php endforeach; ?>
                        </div>
                        <input class="inline-other conditional-field <?php echo posted('thematique') === 'autre' ? 'is-visible' : ''; ?>" data-conditional-field="thematique_autre" type="text" name="thematique_autre" value="<?php echo h((string) posted('thematique_autre')); ?>" placeholder="Si autre, précisez" <?php echo posted('thematique') === 'autre' ? '' : 'disabled'; ?>>
                        <?php if (isset($errors['thematique'])): ?><div class="error"><?php echo h($errors['thematique']); ?></div><?php endif; ?>
                        <?php if (isset($errors['thematique_autre'])): ?><div class="error"><?php echo h($errors['thematique_autre']); ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><span class="num">4</span> Participation et suivi</h3>
                    <div class="field">
                        <label>Êtes-vous disponible pour participer en présentiel à Kinshasa du 18 au 19 septembre 2026 ? *</label>
                        <div class="options">
                            <label class="option"><input type="radio" name="disponible_presentiel" value="oui" <?php echo posted('disponible_presentiel') === 'oui' ? 'checked' : ''; ?>> <span>Oui</span></label>
                            <label class="option"><input type="radio" name="disponible_presentiel" value="non" <?php echo posted('disponible_presentiel') === 'non' ? 'checked' : ''; ?>> <span>Non</span></label>
                        </div>
                        <?php if (isset($errors['disponible_presentiel'])): ?><div class="error"><?php echo h($errors['disponible_presentiel']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label>Avez-vous des besoins spécifiques liés à votre participation ? *</label>
                        <div class="options">
                            <label class="option"><input type="radio" name="besoins_specifiques" value="oui" <?php echo posted('besoins_specifiques') === 'oui' ? 'checked' : ''; ?>> <span>Oui</span></label>
                            <label class="option"><input type="radio" name="besoins_specifiques" value="non" <?php echo posted('besoins_specifiques') === 'non' ? 'checked' : ''; ?>> <span>Non</span></label>
                        </div>
                        <input class="inline-other conditional-field <?php echo posted('besoins_specifiques') === 'oui' ? 'is-visible' : ''; ?>" data-conditional-field="besoins_specifiques_detail" type="text" name="besoins_specifiques_detail" value="<?php echo h((string) posted('besoins_specifiques_detail')); ?>" placeholder="Si oui, précisez" <?php echo posted('besoins_specifiques') === 'oui' ? '' : 'disabled'; ?>>
                        <?php if (isset($errors['besoins_specifiques'])): ?><div class="error"><?php echo h($errors['besoins_specifiques']); ?></div><?php endif; ?>
                        <?php if (isset($errors['besoins_specifiques_detail'])): ?><div class="error"><?php echo h($errors['besoins_specifiques_detail']); ?></div><?php endif; ?>
                    </div>
                    <div class="field">
                        <label>Comment avez-vous entendu parler de Creators Bomoko ? *</label>
                        <div class="options">
                            <?php foreach ($heardOptions as $value => $label): ?>
                                <label class="option"><input type="radio" name="source" value="<?php echo h($value); ?>" <?php echo posted('source') === $value ? 'checked' : ''; ?>> <span><?php echo h($label); ?></span></label>
                            <?php endforeach; ?>
                        </div>
                        <input class="inline-other conditional-field <?php echo posted('source') === 'autre' ? 'is-visible' : ''; ?>" data-conditional-field="source_autre" type="text" name="source_autre" value="<?php echo h((string) posted('source_autre')); ?>" placeholder="Si autre, précisez" <?php echo posted('source') === 'autre' ? '' : 'disabled'; ?>>
                        <?php if (isset($errors['source'])): ?><div class="error"><?php echo h($errors['source']); ?></div><?php endif; ?>
                        <?php if (isset($errors['source_autre'])): ?><div class="error"><?php echo h($errors['source_autre']); ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="consent-box">
                    En soumettant ce formulaire, vous acceptez que les informations fournies soient utilisées pour l’évaluation de votre candidature à <?php echo h($eventName); ?>.
                </div>

                <div class="submit-row">
                    <p>Vérifiez vos informations avant l’envoi. Une référence sera générée après soumission.</p>
                    <button class="btn btn-submit" type="submit">Envoyer ma candidature</button>
                </div>
            </form>
        </section>

        <section class="partners-section" aria-label="Partenaires">
            <?php
            $featuredPartnerLogos = array_values(array_filter($partnerLogos, static fn (array $partnerLogo): bool => ($partnerLogo['tier'] ?? '') === 'featured'));
            $standardPartnerLogos = array_values(array_filter($partnerLogos, static fn (array $partnerLogo): bool => ($partnerLogo['tier'] ?? '') !== 'featured'));
            ?>
            <?php if ($partnerLogos === []): ?>
                <div class="partners-grid partners-grid--standard">
                    <div class="partner-logo-card">Aucun logo partenaire disponible.</div>
                </div>
            <?php endif; ?>
            <?php if ($featuredPartnerLogos !== []): ?>
                <div class="partners-grid partners-grid--featured">
                    <?php foreach ($featuredPartnerLogos as $partnerLogo): ?>
                        <div class="partner-logo-card">
                            <img src="<?php echo h((string) $partnerLogo['src']); ?>" alt="<?php echo h((string) $partnerLogo['name']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($standardPartnerLogos !== []): ?>
                <div class="partners-grid partners-grid--standard">
                    <?php foreach ($standardPartnerLogos as $partnerLogo): ?>
                        <div class="partner-logo-card">
                            <img src="<?php echo h((string) $partnerLogo['src']); ?>" alt="<?php echo h((string) $partnerLogo['name']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <footer class="public-footer">
            <hr class="footer-separator">
            <div class="public-footer__text">©2026 Creators Bomoko powered by U.S Embassy Kinshasa · Designed by Hubert Solutions</div>
        </footer>
    </main>
</div>
<script src="/sweet/sweetalert2.all.min.js"></script>
<script>
if (!window.Swal) {
    document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sweetAlertPayload = <?php echo json_encode($sweetAlert, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    function setConditionalField(fieldName, isVisible) {
        const field = document.querySelector('[data-conditional-field="' + fieldName + '"]');
        if (!field) {
            return;
        }

        field.classList.toggle('is-visible', isVisible);
        field.disabled = !isVisible;
        field.setAttribute('aria-hidden', isVisible ? 'false' : 'true');

        if (!isVisible) {
            field.value = '';
        }
    }

    function isChecked(selector) {
        const field = document.querySelector(selector);
        return Boolean(field && field.checked);
    }

    function updateConditionalFields() {
        setConditionalField('domaine_autre', isChecked('input[name="domaine"][value="autre"]'));
        setConditionalField('plateforme_autre', isChecked('input[name="plateformes[]"][value="autre"]'));
        setConditionalField('thematique_autre', isChecked('input[name="thematique"][value="autre"]'));
        setConditionalField('besoins_specifiques_detail', isChecked('input[name="besoins_specifiques"][value="oui"]'));
        setConditionalField('source_autre', isChecked('input[name="source"][value="autre"]'));
    }

    document.querySelectorAll('input[name="domaine"], input[name="plateformes[]"], input[name="thematique"], input[name="besoins_specifiques"], input[name="source"]').forEach(function(field) {
        field.addEventListener('change', updateConditionalFields);
    });

    updateConditionalFields();

    if (sweetAlertPayload && window.Swal) {
        Swal.fire(sweetAlertPayload);
    }
});
</script>
</body>
</html>