-- U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity schema
-- Exécuter uniquement sur la base cible ISApp.

CREATE TABLE IF NOT EXISTS drcalumni_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(120) NOT NULL,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_drcalumni_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    UNIQUE KEY uq_drcalumni_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    CONSTRAINT fk_drcalumni_decision_user FOREIGN KEY (decision_by) REFERENCES drcalumni_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    CONSTRAINT fk_drcalumni_outbox_application FOREIGN KEY (application_id) REFERENCES drcalumni_applications (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS drcalumni_email_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    outbox_id BIGINT UNSIGNED NOT NULL,
    attempt_no INT UNSIGNED NOT NULL,
    transport_response TEXT DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    was_successful TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_drcalumni_attempt_outbox (outbox_id),
    CONSTRAINT fk_drcalumni_attempt_outbox FOREIGN KEY (outbox_id) REFERENCES drcalumni_email_outbox (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    CONSTRAINT fk_drcalumni_token_application FOREIGN KEY (application_id) REFERENCES drcalumni_applications (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    CONSTRAINT fk_drcalumni_checkin_application FOREIGN KEY (application_id) REFERENCES drcalumni_applications (id) ON DELETE CASCADE,
    CONSTRAINT fk_drcalumni_checkin_operator FOREIGN KEY (operator_id) REFERENCES drcalumni_users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS drcalumni_audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id BIGINT UNSIGNED DEFAULT NULL,
    action_type VARCHAR(80) NOT NULL,
    target_type VARCHAR(40) DEFAULT NULL,
    target_id VARCHAR(80) DEFAULT NULL,
    metadata_json JSON DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_drcalumni_audit_actor FOREIGN KEY (actor_user_id) REFERENCES drcalumni_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
