CREATE TABLE IF NOT EXISTS confirmation_mail_log (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    confirmation_id INT NOT NULL,
    recipient_email VARCHAR(190) NOT NULL,
    sender_user_id INT NULL,
    sent_at DATETIME NOT NULL,
    INDEX idx_confirmation_mail_log_event_confirmation (event_id, confirmation_id),
    INDEX idx_confirmation_mail_log_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;