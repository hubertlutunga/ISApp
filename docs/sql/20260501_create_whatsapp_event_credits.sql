CREATE TABLE IF NOT EXISTS whatsapp_event_credits (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    event_code VARCHAR(191) NOT NULL,
    client_user_id INT UNSIGNED NOT NULL,
    base_quota INT UNSIGNED NOT NULL DEFAULT 500,
    bonus_quota INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_whatsapp_event_client (event_code, client_user_id),
    KEY idx_whatsapp_credit_client (client_user_id),
    KEY idx_whatsapp_credit_event (event_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
