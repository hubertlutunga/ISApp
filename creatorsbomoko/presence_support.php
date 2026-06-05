<?php

declare(strict_types=1);

const CBOMOKO_EVENT_NAME = 'Creators Bomoko 2026';
const CBOMOKO_EVENT_DATES = '5–6 juin 2026';
const CBOMOKO_EVENT_LOCATION = 'Musée National de la RDC, Kinshasa';

function cbp_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cbp_access_code(): string
{
    $code = getenv('CREATORS_BOMOKO_ACCESS_CODE');
    if (is_string($code) && trim($code) !== '') {
        return trim($code);
    }

    $envCode = $_ENV['CREATORS_BOMOKO_ACCESS_CODE'] ?? $_SERVER['CREATORS_BOMOKO_ACCESS_CODE'] ?? '';
    if (is_string($envCode) && trim($envCode) !== '') {
        return trim($envCode);
    }

    return 'CBOMOKO2026';
}

function cbp_add_column_if_missing(PDO $pdo, string $column, string $definition): void
{
    $stmt = $pdo->prepare('SHOW COLUMNS FROM participants_cbomoko LIKE :column_name');
    $stmt->execute([':column_name' => $column]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return;
    }

    $pdo->exec('ALTER TABLE participants_cbomoko ADD COLUMN ' . $definition);
}

function cbp_add_index_if_missing(PDO $pdo, string $indexName, string $definition): void
{
    $stmt = $pdo->prepare('SHOW INDEX FROM participants_cbomoko WHERE Key_name = :index_name');
    $stmt->execute([':index_name' => $indexName]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return;
    }

    $pdo->exec('ALTER TABLE participants_cbomoko ADD INDEX ' . $definition);
}

function cbp_ensure_presence_schema(PDO $pdo): void
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
    experience VARCHAR(80) NOT NULL,
    participation_similaire VARCHAR(10) NOT NULL,
    motivation TEXT NOT NULL,
    thematique VARCHAR(240) NOT NULL,
    attentes TEXT NOT NULL,
    disponible_presentiel VARCHAR(10) NOT NULL,
    infos_futures VARCHAR(10) NOT NULL,
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

    cbp_add_column_if_missing($pdo, 'acces', 'acces VARCHAR(10) DEFAULT NULL AFTER notes_admin');
    cbp_add_column_if_missing($pdo, 'heure_arrive', 'heure_arrive DATETIME DEFAULT NULL AFTER acces');
    cbp_add_index_if_missing($pdo, 'idx_participants_cbomoko_acces', 'idx_participants_cbomoko_acces (acces)');
}

function cbp_confirmed_participants(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id, submission_id, nom_complet, email, telephone, ville, profession, organisation, domaine, plateformes, acces, heure_arrive, updated_at FROM participants_cbomoko WHERE status = 'confirmee' ORDER BY nom_complet ASC");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cbp_find_confirmed_participant(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM participants_cbomoko WHERE id = :id AND status = 'confirmee' LIMIT 1");
    $stmt->execute([':id' => $id]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    return is_array($participant) ? $participant : null;
}

function cbp_find_confirmed_participant_by_identifier(PDO $pdo, string $identifier): ?array
{
    $identifier = trim($identifier);
    if ($identifier === '') {
        return null;
    }

    if (ctype_digit($identifier)) {
        return cbp_find_confirmed_participant($pdo, (int) $identifier);
    }

    $stmt = $pdo->prepare("SELECT * FROM participants_cbomoko WHERE submission_id = :submission_id AND status = 'confirmee' LIMIT 1");
    $stmt->execute([':submission_id' => $identifier]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    return is_array($participant) ? $participant : null;
}

function cbp_mark_access_confirmed(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare("UPDATE participants_cbomoko SET acces = 'oui', heure_arrive = COALESCE(heure_arrive, NOW()) WHERE id = :id AND status = 'confirmee'");
    $stmt->execute([':id' => $id]);
}

function cbp_format_datetime(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y H:i');
    } catch (Throwable) {
        return $value;
    }
}
