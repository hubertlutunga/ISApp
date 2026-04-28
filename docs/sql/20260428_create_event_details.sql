CREATE TABLE IF NOT EXISTS event_details (
    cod_detail INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cod_event INT NOT NULL,
    detail_type VARCHAR(60) NULL,
    date_debut DATETIME NULL,
    date_fin DATETIME NULL,
    matiere VARCHAR(255) NULL,
    intervenant VARCHAR(255) NULL,
    date_enreg DATETIME NOT NULL,
    date_maj DATETIME NOT NULL,
    UNIQUE KEY uniq_event_detail (cod_event),
    KEY idx_event_detail_type (detail_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;