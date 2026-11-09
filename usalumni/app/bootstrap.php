<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Kinshasa');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/PHPMailer/src/Exception.php';
require_once dirname(__DIR__, 2) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__, 2) . '/PHPMailer/src/SMTP.php';

if (!defined('DRCALUMNI_BOOTSTRAPPED')) {
    define('DRCALUMNI_BOOTSTRAPPED', true);
}

const DRCALUMNI_EVENT_NAME = 'U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity';
const DRCALUMNI_PUBLIC_EVENT_NAME = 'U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity';
const DRCALUMNI_EVENT_THEME = 'Faire progresser le partenariat stratégique et les opportunités économiques entre les États-Unis et la RDC';
const DRCALUMNI_EVENT_DATES = '1er et 2 décembre 2026';
const DRCALUMNI_EVENT_TIME = '9h00';
const DRCALUMNI_EVENT_LOCATION = "The Garden, situé en face de l'entrée de l'hôtel Pullman donnant sur l'avenue Colonel Tshatshi, Kinshasa.";

const DRCALUMNI_ROLE_SUPER_ADMIN = 'super_admin';
const DRCALUMNI_ROLE_REVIEWER = 'reviewer';
const DRCALUMNI_ROLE_CHECKIN = 'checkin_operator';

function drc_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function drc_roles(): array
{
    return [
        DRCALUMNI_ROLE_SUPER_ADMIN => 'Super administrateur',
        DRCALUMNI_ROLE_REVIEWER => 'Reviewer',
        DRCALUMNI_ROLE_CHECKIN => "Opérateur d'accueil",
    ];
}

function drc_statuses(): array
{
    return [
        'pending' => 'En attente',
        'selected' => 'Sélectionnée',
        'rejected' => 'Non retenue',
    ];
}

function drc_provinces(): array
{
    return [
        'Bas-Uélé', 'Équateur', 'Haut-Katanga', 'Haut-Lomami', 'Haut-Uélé', 'Ituri', 'Kasaï',
        'Kasaï-Central', 'Kasaï-Oriental', 'Kinshasa', 'Kongo-Central', 'Kwango', 'Kwilu', 'Lomami',
        'Lualaba', 'Mai-Ndombe', 'Maniema', 'Mongala', 'Nord-Kivu', 'Nord-Ubangi', 'Sankuru',
        'Sud-Kivu', 'Sud-Ubangi', 'Tanganyika', 'Tshopo', 'Tshuapa',
    ];
}

function drc_sector_options(): array
{
    return [
        'public' => 'Gouvernement / Secteur public',
        'private' => 'Secteur privé',
        'entrepreneurship' => 'Entrepreneuriat',
        'civil_society' => 'Société civile / ONG',
        'education' => 'Enseignement / Recherche',
        'media' => 'Médias / Communication',
        'technology' => 'Technologie / Numérique',
        'arts' => 'Arts / Culture / Industries créatives',
        'other' => 'Autre (précisez)',
    ];
}

function drc_program_options(): array
{
    return [
        'fulbright' => 'Fulbright',
        'humphrey' => 'Hubert H. Humphrey Fellowship',
        'yali' => 'Mandela Washington Fellowship (YALI)',
        'ivlp' => 'International Visitor Leadership Program (IVLP)',
        'access' => 'English Access Scholarship Program (Access)',
        'paylp' => 'Pan Africa Youth Leadership Program (PAYLP)',
        'other' => 'Autre (précisez)',
    ];
}

function drc_theme_options(): array
{
    return [
        'partenariat_strategique' => 'Partenariat stratégique États-Unis–RDC',
        'diplomatie_commerciale' => 'Diplomatie commerciale et investissement',
        'minerais_critiques' => 'Minerais critiques et chaînes de valeur',
        'gouvernance' => 'Gouvernance et État de droit',
        'entrepreneuriat_innovation' => 'Entrepreneuriat et innovation',
        'ppp' => 'Partenariats public-privé',
        'economie_numerique' => 'Économie numérique et technologies',
    ];
}

function drc_workgroup_options(): array
{
    return [
        'diplomatie_commerciale' => 'Diplomatie commerciale et investissement',
        'gouvernance' => 'Gouvernance et État de droit',
        'entrepreneuriat_innovation' => 'Entrepreneuriat et innovation',
    ];
}

function drc_availability_options(): array
{
    return [
        'both_days' => 'Oui, les deux journées',
        'day_1' => 'Uniquement le 1er décembre',
        'day_2' => 'Uniquement le 2 décembre',
        'not_available' => 'Non, je ne serai pas disponible',
    ];
}

function drc_base_url(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $position = strpos($scriptName, '/usalumni/');
    $path = $position === false ? '/usalumni' : substr($scriptName, 0, $position + strlen('/usalumni'));
    $cached = rtrim($path, '/');

    return $cached === '' ? '/usalumni' : $cached;
}

function drc_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return drc_base_url() . ($path === '' ? '/' : '/' . $path);
}

function drc_asset(string $path): string
{
    return drc_url(ltrim($path, '/'));
}

function drc_absolute_url(string $path): string
{
    $relative = drc_url($path);
    $relativeParts = parse_url($relative);
    if (is_array($relativeParts) && isset($relativeParts['path'])) {
        $segments = explode('/', (string) $relativeParts['path']);
        $encodedSegments = array_map(
            static fn (string $segment): string => rawurlencode(rawurldecode($segment)),
            $segments
        );
        $encodedPath = implode('/', $encodedSegments);
        $query = isset($relativeParts['query']) ? '?' . $relativeParts['query'] : '';
        $fragment = isset($relativeParts['fragment']) ? '#' . $relativeParts['fragment'] : '';
        $relative = $encodedPath . $query . $fragment;
    }

    if (str_starts_with($relative, 'http://') || str_starts_with($relative, 'https://')) {
        return $relative;
    }

    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    if ($host === '') {
        return $relative;
    }

    $https = (string) ($_SERVER['HTTPS'] ?? '');
    $scheme = ($https !== '' && strtolower($https) !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host . $relative;
}

function drc_public_logo_paths(): array
{
    return [
        'favicon' => 'images/use-kinshasa-flag-logo-vertical-color-whitetext.png',
        'header' => 'images/use-kinshasa-flag-logo-vertical-color-whitetext.png',
        'footer_freedom' => 'images/Freedom 250 Icon - RGB - Full Color - Gold.png',
        'footer_seal' => 'images/use-kinshasa-seal-color.png',
    ];
}

function drc_verify_required_assets(): array
{
    $errors = [];
    foreach (drc_public_logo_paths() as $label => $relativePath) {
        $absolutePath = dirname(__DIR__) . '/' . $relativePath;
        if (!is_file($absolutePath)) {
            $errors[] = "Asset manquant : {$label} ({$relativePath})";
        }
    }

    return $errors;
}

function drc_clean_text(string $value, int $maxLength = 500): string
{
    $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
    return mb_substr($value, 0, $maxLength, 'UTF-8');
}

function drc_clean_multiline(string $value, int $maxLength = 4000): string
{
    $value = trim(strip_tags($value));
    $value = preg_replace("/\r\n|\r/u", "\n", $value) ?? $value;
    $value = preg_replace("/\n{3,}/u", "\n\n", $value) ?? $value;
    return mb_substr($value, 0, $maxLength, 'UTF-8');
}

function drc_normalize_email(string $email): string
{
    return mb_strtolower(trim($email), 'UTF-8');
}

function drc_word_count(string $value): int
{
    if ($value === '') {
        return 0;
    }

    preg_match_all("/[\p{L}\p{N}]+(?:[’'`-][\p{L}\p{N}]+)*/u", $value, $matches);
    return count($matches[0] ?? []);
}

function drc_validate_max_words(array &$errors, string $field, string $label, string $value, int $maxWords): void
{
    if (drc_word_count($value) > $maxWords) {
        $errors[$field] = "{$label} : maximum {$maxWords} mots.";
    }
}

function drc_posted(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function drc_generate_reference(): string
{
    return 'DAI-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function drc_ensure_storage(): void
{
    $paths = [
        dirname(__DIR__) . '/storage',
        dirname(__DIR__) . '/storage/qrcodes',
        dirname(__DIR__) . '/storage/logs',
        dirname(__DIR__) . '/database/migrations',
    ];

    foreach ($paths as $path) {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        $indexPath = rtrim($path, '/') . '/index.html';
        if (!is_file($indexPath)) {
            file_put_contents($indexPath, '');
        }
    }

    $htaccessPath = dirname(__DIR__) . '/storage/.htaccess';
    if (!is_file($htaccessPath)) {
        file_put_contents($htaccessPath, "Require all denied\nDeny from all\n");
    }
}

function drc_setting(PDO $pdo, string $key, ?string $default = null): ?string
{
    $stmt = $pdo->prepare('SELECT setting_value FROM drcalumni_settings WHERE setting_key = :k LIMIT 1');
    $stmt->execute([':k' => $key]);
    $value = $stmt->fetchColumn();
    if ($value === false) {
        return $default;
    }
    return (string) $value;
}

function drc_set_setting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO drcalumni_settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([':k' => $key, ':v' => $value]);
}

function drc_boot_defaults(PDO $pdo): void
{
    $defaults = [
        'registrations_open' => '1',
        'selection_capacity' => '250',
        'event_title' => DRCALUMNI_EVENT_NAME,
        'event_theme' => DRCALUMNI_EVENT_THEME,
        'event_dates' => DRCALUMNI_EVENT_DATES,
        'event_time' => DRCALUMNI_EVENT_TIME,
        'event_location' => DRCALUMNI_EVENT_LOCATION,
        'contact_emails' => 'LungunguVK@state.gov,KazadiNN@state.gov',
        'submission_ack_enabled' => '1',
        'submission_ack_subject' => 'Accusé de réception – U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity',
        'submission_ack_body' => "Bonjour {name},\n\nNous confirmons la réception de votre candidature à U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity.\n\nRéférence: {reference}\n\nVotre dossier est en cours d’examen. Cette confirmation de dépôt ne signifie pas une sélection.\n\nCordialement,\nSection des Affaires publiques\nAmbassade des États-Unis à Kinshasa",
        'selection_subject' => 'Confirmation de sélection – U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity',
        'selection_body' => drc_selection_email_text('{name}'),
        'rejection_subject' => 'Information relative à votre candidature – U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity',
        'rejection_body' => drc_rejection_email_text('{name}'),
        'queue_batch_size' => '20',
    ];

    foreach ($defaults as $key => $value) {
        if (drc_setting($pdo, $key) === null) {
            drc_set_setting($pdo, $key, $value);
        }
    }
}

function drc_ensure_schema(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS drcalumni_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(120) NOT NULL,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_drcalumni_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS drcalumni_users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'reviewer',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_drcalumni_users_email (email),
    KEY idx_drcalumni_users_role (role),
    KEY idx_drcalumni_users_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS drcalumni_applications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference_code VARCHAR(64) NOT NULL,
    full_name VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL,
    email_normalized VARCHAR(190) NOT NULL,
    phone VARCHAR(80) NOT NULL,
    province VARCHAR(120) NOT NULL,
    city_territory VARCHAR(180) NOT NULL,
    organization_name VARCHAR(190) NOT NULL,
    sectors_json JSON NOT NULL,
    sectors_other VARCHAR(190) DEFAULT NULL,
    responsibilities TEXT NOT NULL,
    programs_json JSON NOT NULL,
    programs_other VARCHAR(190) DEFAULT NULL,
    latest_exchange_year SMALLINT UNSIGNED NOT NULL,
    summit_themes_json JSON NOT NULL,
    cooperation_domains TEXT NOT NULL,
    washington_accords_importance TEXT NOT NULL,
    summit_expectations TEXT NOT NULL,
    sharable_expertise TEXT NOT NULL,
    wants_workgroup TINYINT(1) NOT NULL DEFAULT 0,
    workgroup_choice VARCHAR(120) DEFAULT NULL,
    wants_expo TINYINT(1) NOT NULL DEFAULT 0,
    expo_entity_name VARCHAR(190) DEFAULT NULL,
    expo_description TEXT DEFAULT NULL,
    expo_link VARCHAR(255) DEFAULT NULL,
    availability VARCHAR(40) NOT NULL,
    needs_accommodation TINYINT(1) NOT NULL DEFAULT 0,
    accommodation_details TEXT DEFAULT NULL,
    additional_comments TEXT DEFAULT NULL,
    declaration_truth TINYINT(1) NOT NULL DEFAULT 0,
    declaration_understanding TINYINT(1) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    selected_at DATETIME DEFAULT NULL,
    rejected_at DATETIME DEFAULT NULL,
    decision_by BIGINT UNSIGNED DEFAULT NULL,
    decision_note TEXT DEFAULT NULL,
    submission_token VARCHAR(100) NOT NULL,
    submission_ip VARCHAR(90) DEFAULT NULL,
    submission_user_agent VARCHAR(255) DEFAULT NULL,
    ack_email_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    selection_email_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    rejection_email_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_drcalumni_reference_code (reference_code),
    UNIQUE KEY uq_drcalumni_email_norm (email_normalized),
    UNIQUE KEY uq_drcalumni_submission_token (submission_token),
    KEY idx_drcalumni_status (status),
    KEY idx_drcalumni_province (province),
    KEY idx_drcalumni_created_at (created_at),
    KEY idx_drcalumni_decision_by (decision_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS drcalumni_email_outbox (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED NOT NULL,
    mail_type VARCHAR(30) NOT NULL,
    recipient_email VARCHAR(190) NOT NULL,
    recipient_name VARCHAR(180) NOT NULL,
    subject_line VARCHAR(255) NOT NULL,
    body_html MEDIUMTEXT NOT NULL,
    body_text MEDIUMTEXT NOT NULL,
    idempotency_key VARCHAR(190) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    last_error TEXT DEFAULT NULL,
    queued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at DATETIME DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_drcalumni_outbox_key (idempotency_key),
    KEY idx_drcalumni_outbox_status (status),
    KEY idx_drcalumni_outbox_app (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS drcalumni_email_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    outbox_id BIGINT UNSIGNED NOT NULL,
    attempt_no INT UNSIGNED NOT NULL,
    transport_response TEXT DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    was_successful TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_drcalumni_attempt_outbox (outbox_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS drcalumni_access_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    token_hint VARCHAR(32) NOT NULL,
    is_revoked TINYINT(1) NOT NULL DEFAULT 0,
    revoked_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_drcalumni_token_hash (token_hash),
    UNIQUE KEY uq_drcalumni_token_application (application_id),
    KEY idx_drcalumni_token_revoked (is_revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS drcalumni_checkins (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED NOT NULL,
    event_day TINYINT UNSIGNED NOT NULL,
    checkin_at DATETIME NOT NULL,
    operator_id BIGINT UNSIGNED NOT NULL,
    checkin_method VARCHAR(20) NOT NULL,
    result_status VARCHAR(20) NOT NULL DEFAULT 'success',
    notes VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_drcalumni_checkin_app_day (application_id, event_day),
    KEY idx_drcalumni_checkin_day (event_day),
    KEY idx_drcalumni_checkin_operator (operator_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS drcalumni_audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id BIGINT UNSIGNED DEFAULT NULL,
    action_type VARCHAR(80) NOT NULL,
    target_type VARCHAR(40) DEFAULT NULL,
    target_id VARCHAR(80) DEFAULT NULL,
    metadata_json JSON DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_drcalumni_audit_actor (actor_user_id),
    KEY idx_drcalumni_audit_action (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
}

function drc_boot(PDO $pdo): void
{
    drc_ensure_storage();
    drc_ensure_schema($pdo);
    drc_boot_defaults($pdo);
}

function drc_ensure_csrf(string $key): string
{
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION[$key];
}

function drc_validate_csrf(string $key, string $provided): bool
{
    $token = (string) ($_SESSION[$key] ?? '');
    return $token !== '' && hash_equals($token, $provided);
}

function drc_current_user(): ?array
{
    if (!isset($_SESSION['drcalumni_user_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['drcalumni_user_id'],
        'name' => (string) ($_SESSION['drcalumni_user_name'] ?? ''),
        'email' => (string) ($_SESSION['drcalumni_user_email'] ?? ''),
        'role' => (string) ($_SESSION['drcalumni_user_role'] ?? ''),
    ];
}

function drc_is_logged_in(): bool
{
    return drc_current_user() !== null;
}

function drc_user_has_role(array $roles): bool
{
    $user = drc_current_user();
    return $user !== null && in_array($user['role'], $roles, true);
}

function drc_require_login(array $roles = []): void
{
    if (!drc_is_logged_in()) {
        header('Location: ' . drc_url('admin/index.php'));
        exit;
    }

    if ($roles !== [] && !drc_user_has_role($roles)) {
        http_response_code(403);
        echo '<h1>403</h1><p>Accès non autorisé.</p>';
        exit;
    }
}

function drc_login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['drcalumni_user_id'] = (int) $user['id'];
    $_SESSION['drcalumni_user_name'] = (string) $user['full_name'];
    $_SESSION['drcalumni_user_email'] = (string) $user['email'];
    $_SESSION['drcalumni_user_role'] = (string) $user['role'];
}

function drc_logout_user(): void
{
    unset($_SESSION['drcalumni_user_id'], $_SESSION['drcalumni_user_name'], $_SESSION['drcalumni_user_email'], $_SESSION['drcalumni_user_role']);
    session_regenerate_id(true);
}

function drc_audit(PDO $pdo, ?int $actorUserId, string $action, ?string $targetType = null, ?string $targetId = null, ?array $metadata = null): void
{
    $stmt = $pdo->prepare('INSERT INTO drcalumni_audit_logs (actor_user_id, action_type, target_type, target_id, metadata_json) VALUES (:u, :a, :tt, :ti, :m)');
    $stmt->execute([
        ':u' => $actorUserId,
        ':a' => $action,
        ':tt' => $targetType,
        ':ti' => $targetId,
        ':m' => $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
    ]);
}

function drc_db_now(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('Africa/Kinshasa')))->format('Y-m-d H:i:s');
}

function drc_json_encode(array $value): string
{
    return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function drc_json_decode_array(?string $value): array
{
    if ($value === null || trim($value) === '') {
        return [];
    }
    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : [];
}

function drc_mask_sensitive_email(string $email): string
{
    $parts = explode('@', $email, 2);
    if (count($parts) !== 2) {
        return $email;
    }
    $local = $parts[0];
    if (mb_strlen($local, 'UTF-8') <= 2) {
        $local = mb_substr($local, 0, 1, 'UTF-8') . '*';
    } else {
        $local = mb_substr($local, 0, 2, 'UTF-8') . str_repeat('*', max(1, mb_strlen($local, 'UTF-8') - 2));
    }
    return $local . '@' . $parts[1];
}

function drc_selected_labels(array $options, array $keys, ?string $other = null): array
{
    $labels = [];
    foreach ($keys as $key) {
        $key = (string) $key;
        if (!isset($options[$key])) {
            continue;
        }
        if ($key === 'other' && $other !== null && trim($other) !== '') {
            $labels[] = 'Autre : ' . trim($other);
            continue;
        }
        $labels[] = $options[$key];
    }
    return $labels;
}

function drc_enqueue_email(
    PDO $pdo,
    int $applicationId,
    string $mailType,
    string $toEmail,
    string $toName,
    string $subject,
    string $html,
    string $text,
    string $idempotencyKey
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO drcalumni_email_outbox
        (application_id, mail_type, recipient_email, recipient_name, subject_line, body_html, body_text, idempotency_key, status, queued_at)
        VALUES (:app, :type, :email, :name, :subject, :html, :text, :idem, :status, :queued)
        ON DUPLICATE KEY UPDATE id = id'
    );
    $stmt->execute([
        ':app' => $applicationId,
        ':type' => $mailType,
        ':email' => $toEmail,
        ':name' => $toName,
        ':subject' => $subject,
        ':html' => $html,
        ':text' => $text,
        ':idem' => $idempotencyKey,
        ':status' => 'pending',
        ':queued' => drc_db_now(),
    ]);
}

function drc_mailer_transport(): array
{
    return [
        'host' => getenv('USALUMNI_SMTP_HOST')
            ?: (getenv('DRCALUMNI_SMTP_HOST')
            ?: (getenv('CREATORSBOMOKO_SMTP_HOST')
                ?: (getenv('ISAPP_SMTP_HOST') ?: 'invitationspeciale.com'))),
        'user' => getenv('USALUMNI_SMTP_USER')
            ?: (getenv('DRCALUMNI_SMTP_USER')
            ?: (getenv('CREATORSBOMOKO_SMTP_USER')
                ?: (getenv('ISAPP_SMTP_USERNAME') ?: 'eventpass@invitationspeciale.com'))),
        'password' => getenv('USALUMNI_SMTP_PASSWORD')
            ?: (getenv('DRCALUMNI_SMTP_PASSWORD')
            ?: (getenv('CREATORSBOMOKO_SMTP_PASSWORD')
                ?: (getenv('ISAPP_SMTP_PASSWORD') ?: ''))),
        'port' => (int) (getenv('USALUMNI_SMTP_PORT')
            ?: (getenv('DRCALUMNI_SMTP_PORT')
            ?: (getenv('CREATORSBOMOKO_SMTP_PORT')
                ?: (getenv('ISAPP_SMTP_PORT') ?: 587)))),
        'encryption' => getenv('USALUMNI_SMTP_ENCRYPTION') ?: (getenv('DRCALUMNI_SMTP_ENCRYPTION') ?: (getenv('ISAPP_SMTP_ENCRYPTION') ?: 'tls')),
        'from_name' => getenv('USALUMNI_SMTP_FROM_NAME') ?: (getenv('DRCALUMNI_SMTP_FROM_NAME') ?: 'Section des Affaires publiques'),
        'reply_to' => getenv('USALUMNI_SMTP_REPLY_TO') ?: (getenv('DRCALUMNI_SMTP_REPLY_TO') ?: ''),
    ];
}

function drc_send_email_transport(string $toEmail, string $toName, string $subject, string $html, string $text): array
{
    $transport = drc_mailer_transport();
    if (trim($transport['password']) === '') {
        return ['success' => false, 'message' => 'SMTP non configuré (mot de passe manquant).'];
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $transport['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $transport['user'];
        $mail->Password = $transport['password'];
        $mail->Port = $transport['port'];
        $mail->SMTPSecure = $transport['encryption'] === 'ssl'
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->setFrom($transport['user'], $transport['from_name']);
        if ($transport['reply_to'] !== '') {
            $mail->addReplyTo($transport['reply_to'], $transport['from_name']);
        }
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $text;
        $mail->send();

        return ['success' => true, 'message' => 'sent'];
    } catch (Throwable $exception) {
        error_log('[DRC Alumni Mail] ' . $exception->getMessage());
        return ['success' => false, 'message' => $exception->getMessage()];
    }
}

function drc_process_outbox(PDO $pdo, int $limit, ?int $actorUserId = null): array
{
    $stmt = $pdo->prepare(
        "SELECT * FROM drcalumni_email_outbox
         WHERE status IN ('pending', 'failed')
         ORDER BY queued_at ASC
         LIMIT :lim"
    );
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sent = 0;
    $failed = 0;
    foreach ($rows as $row) {
        $outboxId = (int) $row['id'];
        $attemptNo = ((int) $row['attempts']) + 1;
        $result = drc_send_email_transport(
            (string) $row['recipient_email'],
            (string) $row['recipient_name'],
            (string) $row['subject_line'],
            (string) $row['body_html'],
            (string) $row['body_text']
        );

        $status = $result['success'] ? 'sent' : 'failed';
        $update = $pdo->prepare(
            'UPDATE drcalumni_email_outbox
             SET status = :status, attempts = :attempts, last_attempt_at = :attempted, sent_at = :sent_at, last_error = :err
             WHERE id = :id'
        );
        $update->execute([
            ':status' => $status,
            ':attempts' => $attemptNo,
            ':attempted' => drc_db_now(),
            ':sent_at' => $result['success'] ? drc_db_now() : null,
            ':err' => $result['success'] ? null : (string) $result['message'],
            ':id' => $outboxId,
        ]);

        $attemptInsert = $pdo->prepare(
            'INSERT INTO drcalumni_email_attempts (outbox_id, attempt_no, transport_response, error_message, was_successful)
             VALUES (:outbox_id, :attempt_no, :response, :error, :ok)'
        );
        $attemptInsert->execute([
            ':outbox_id' => $outboxId,
            ':attempt_no' => $attemptNo,
            ':response' => $result['success'] ? 'sent' : null,
            ':error' => $result['success'] ? null : (string) $result['message'],
            ':ok' => $result['success'] ? 1 : 0,
        ]);

        $applicationStatusField = null;
        if ((string) $row['mail_type'] === 'submission_ack') {
            $applicationStatusField = 'ack_email_status';
        } elseif ((string) $row['mail_type'] === 'selection') {
            $applicationStatusField = 'selection_email_status';
        } elseif ((string) $row['mail_type'] === 'rejection') {
            $applicationStatusField = 'rejection_email_status';
        }

        if ($applicationStatusField !== null) {
            $appStmt = $pdo->prepare("UPDATE drcalumni_applications SET {$applicationStatusField} = :status WHERE id = :id");
            $appStmt->execute([
                ':status' => $result['success'] ? 'sent' : 'failed',
                ':id' => (int) $row['application_id'],
            ]);
        }

        drc_audit(
            $pdo,
            $actorUserId,
            $result['success'] ? 'email.sent' : 'email.failed',
            'outbox',
            (string) $outboxId,
            ['mail_type' => $row['mail_type'], 'recipient' => $row['recipient_email'], 'error' => $result['success'] ? null : $result['message']]
        );

        if ($result['success']) {
            $sent++;
        } else {
            $failed++;
        }
    }

    return ['processed' => count($rows), 'sent' => $sent, 'failed' => $failed];
}

function drc_generate_access_token(): string
{
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
}

function drc_token_hash(string $token): string
{
    return hash('sha256', $token);
}

function drc_store_access_token(PDO $pdo, int $applicationId, string $token): void
{
    $hash = drc_token_hash($token);
    $hint = substr($token, 0, 12);

    $stmt = $pdo->prepare(
        'INSERT INTO drcalumni_access_tokens (application_id, token_hash, token_hint, is_revoked, revoked_at)
         VALUES (:app, :hash, :hint, 0, NULL)
         ON DUPLICATE KEY UPDATE token_hash = VALUES(token_hash), token_hint = VALUES(token_hint), is_revoked = 0, revoked_at = NULL'
    );
    $stmt->execute([
        ':app' => $applicationId,
        ':hash' => $hash,
        ':hint' => $hint,
    ]);
}

function drc_find_application_by_access_token(PDO $pdo, string $token): ?array
{
    $hash = drc_token_hash($token);
    $stmt = $pdo->prepare(
        'SELECT a.*, t.is_revoked
         FROM drcalumni_access_tokens t
         INNER JOIN drcalumni_applications a ON a.id = t.application_id
         WHERE t.token_hash = :hash
         LIMIT 1'
    );
    $stmt->execute([':hash' => $hash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function drc_checkin_day_label(int $day): string
{
    return $day === 2 ? '2 décembre 2026' : '1er décembre 2026';
}

function drc_resolve_event_day(?string $raw): int
{
    if ($raw === '1' || $raw === '2') {
        return (int) $raw;
    }

    $today = (new DateTimeImmutable('now', new DateTimeZone('Africa/Kinshasa')))->format('Y-m-d');
    return $today >= '2026-12-02' ? 2 : 1;
}

function drc_checkin(PDO $pdo, int $applicationId, int $day, int $operatorId, string $method): array
{
    $exists = $pdo->prepare('SELECT id, checkin_at FROM drcalumni_checkins WHERE application_id = :app AND event_day = :day LIMIT 1');
    $exists->execute([':app' => $applicationId, ':day' => $day]);
    $row = $exists->fetch(PDO::FETCH_ASSOC);
    if (is_array($row)) {
        return ['ok' => false, 'already' => true, 'checked_at' => (string) ($row['checkin_at'] ?? '')];
    }

    $insert = $pdo->prepare(
        'INSERT INTO drcalumni_checkins (application_id, event_day, checkin_at, operator_id, checkin_method, result_status)
         VALUES (:app, :day, :at, :operator, :method, :status)'
    );
    $insert->execute([
        ':app' => $applicationId,
        ':day' => $day,
        ':at' => drc_db_now(),
        ':operator' => $operatorId,
        ':method' => $method,
        ':status' => 'success',
    ]);

    return ['ok' => true, 'already' => false];
}

function drc_csv_safe(string $value): string
{
    $dangerous = ['=', '+', '-', '@'];
    if ($value !== '' && in_array($value[0], $dangerous, true)) {
        return "'" . $value;
    }
    return $value;
}

function drc_render_text_mail(string $body): string
{
    return nl2br(drc_h($body));
}

function drc_selection_email_text(string $name): string
{
    return "Cher/Chère {$name},\n\nL’Ambassade des États-Unis à Kinshasa a le plaisir de vous annoncer que votre candidature a été retenue pour participer à U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity.\n\nCe sommet réunira 250 anciens participants aux programmes d’échanges du gouvernement américain, venus des différentes régions de la République démocratique du Congo. Il constituera une plateforme privilégiée de dialogue, de réseautage et de collaboration, permettant de renforcer les liens au sein de la communauté des alumni et d'explorer de nouvelles perspectives de partenariat au service des relations stratégiques entre les États-Unis et la RDC.\n\nINFORMATIONS PRATIQUES\n\nDates : Du 1er au 2 décembre 2026\nLieu : The Garden, situé en face de l'entrée de l'hôtel Pullman donnant sur l'avenue Colonel Tshatshi, Kinshasa.\nDurée : Deux jours\n\nNous vous prions de bien vouloir prendre vos dispositions afin d'être sur place à 9h00. L'agenda détaillé du Sommet vous sera communiqué prochainement.\n\nFélicitations pour votre sélection ! Nous sommes ravis de vous accueillir à U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity.\n\nPour toute question ou information complémentaire, n'hésitez pas à nous contacter aux adresses suivantes :\n\nLungunguVK@state.gov\nKazadiNN@state.gov\n\nCordialement,\n\nSection des Affaires publiques\nAmbassade des États-Unis à Kinshasa";
}

function drc_rejection_email_text(string $name): string
{
    return "Cher/Chère {$name},\n\nL’Ambassade des États-Unis à Kinshasa tient à vous remercier sincèrement pour l’intérêt que vous avez manifesté à l’égard de U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity, ainsi que pour le temps consacré à la soumission de votre candidature.\n\nNous avons reçu un grand nombre de candidatures témoignant de l’engagement et du dynamisme de la communauté des anciens participants aux programmes d’échanges du gouvernement américain en République démocratique du Congo.\n\nCompte tenu du nombre limité de places disponibles, nous regrettons de vous informer que votre candidature n’a malheureusement pas été retenue pour cette édition du Sommet.\n\nNous vous encourageons vivement à rester actif au sein de la communauté des alumni et à saisir les prochaines occasions de réseautage, de renforcement des capacités et de collaboration proposées par l’Ambassade des États-Unis.\n\nNous vous remercions une nouvelle fois pour votre intérêt, votre compréhension et votre engagement continu. Nous espérons avoir l’occasion de vous retrouver dans le cadre de prochaines activités et initiatives destinées aux alumni.\n\nCordialement,\n\nSection des Affaires publiques\nAmbassade des États-Unis à Kinshasa";
}

function drc_official_mail_html(string $title, string $text, ?string $extraHtml = null): string
{
    $body = drc_render_text_mail($text);
    $extra = $extraHtml ?? '';
    $logoHeader = drc_absolute_url('images/use-kinshasa-flag-logo-vertical-color.png');
    $logoFreedom = drc_absolute_url('images/Freedom 250 Icon - RGB - Full Color - Gold.png');
    $logoSeal = drc_absolute_url('images/use-kinshasa-seal-color.png');

    return '<div style="font-family:Inter,Arial,sans-serif;background:#f2f6fc;color:#17253B;padding:24px;">
        <div style="max-width:720px;margin:0 auto;background:#ffffff;border:1px solid #DBE3EF;border-radius:16px;overflow:hidden">
            <div style="background:#ffffff;color:#17253B;padding:18px 28px 10px;text-align:center;border-bottom:1px solid #DBE3EF;">
                <img src="' . drc_h($logoHeader) . '" alt="Ambassade des États-Unis à Kinshasa" style="width:112px;height:auto;display:inline-block;">
            </div>
            <div style="background:#002868;color:#ffffff;padding:20px 28px;">
                <h1 style="margin:0;font-size:22px;line-height:1.3;">' . drc_h($title) . '</h1>
            </div>
            <div style="padding:24px 28px;font-size:15px;line-height:1.7;">' . $body . $extra . '</div>
            <div style="padding:14px 28px 22px;border-top:1px solid #DBE3EF;text-align:center;background:#f9fbff;">
                <img src="' . drc_h($logoFreedom) . '" alt="Freedom 250" style="width:84px;height:auto;display:inline-block;margin:0 10px;vertical-align:middle;">
                <img src="' . drc_h($logoSeal) . '" alt="U.S. Embassy Kinshasa Seal" style="width:84px;height:auto;display:inline-block;margin:0 10px;vertical-align:middle;">
            </div>
        </div>
    </div>';
}

function drc_qr_public_url(string $token): string
{
    return drc_url('admin/scan/index.php?token=' . rawurlencode($token));
}

function drc_qr_png_path(int $applicationId, string $token): string
{
    $directory = dirname(__DIR__) . '/storage/qrcodes';
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
    $path = $directory . '/usalumni_' . $applicationId . '.png';

    require_once dirname(__DIR__, 2) . '/qrscan/phpqrcode/qrlib.php';
    QRcode::png(drc_qr_public_url($token), $path, QR_ECLEVEL_M, 8, 2);
    return $path;
}

function drc_qr_public_image_url(int $applicationId): string
{
    return drc_absolute_url('storage/qrcodes/usalumni_' . $applicationId . '.png');
}
