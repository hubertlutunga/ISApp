-- Index recommandés pour accélérer le back-office admin event.
-- A exécuter une fois en production sur la base ISApp.

DELIMITER //

CREATE PROCEDURE isapp_add_index_if_missing(IN p_table VARCHAR(64), IN p_index VARCHAR(64), IN p_ddl TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table
          AND INDEX_NAME = p_index
    ) THEN
        SET @isapp_sql = p_ddl;
        PREPARE isapp_stmt FROM @isapp_sql;
        EXECUTE isapp_stmt;
        DEALLOCATE PREPARE isapp_stmt;
    END IF;
END//

DELIMITER ;

CALL isapp_add_index_if_missing('events', 'idx_events_date_enreg', 'ALTER TABLE `events` ADD INDEX `idx_events_date_enreg` (`date_enreg`)');
CALL isapp_add_index_if_missing('events', 'idx_events_fact_crea_date', 'ALTER TABLE `events` ADD INDEX `idx_events_fact_crea_date` (`fact`, `crea`, `date_enreg`)');
CALL isapp_add_index_if_missing('events', 'idx_events_user_date', 'ALTER TABLE `events` ADD INDEX `idx_events_user_date` (`cod_user`, `date_enreg`)');
CALL isapp_add_index_if_missing('events', 'idx_events_user2_date', 'ALTER TABLE `events` ADD INDEX `idx_events_user2_date` (`cod_user2`, `date_enreg`)');

CALL isapp_add_index_if_missing('facture', 'idx_facture_reference', 'ALTER TABLE `facture` ADD INDEX `idx_facture_reference` (`reference`)');
CALL isapp_add_index_if_missing('facture', 'idx_facture_date_enreg', 'ALTER TABLE `facture` ADD INDEX `idx_facture_date_enreg` (`date_enreg`)');

CALL isapp_add_index_if_missing('details_fact', 'idx_details_fact_libelle_date', 'ALTER TABLE `details_fact` ADD INDEX `idx_details_fact_libelle_date` (`libelle`, `date_enreg`)');
CALL isapp_add_index_if_missing('details_fact', 'idx_details_fact_event_libelle', 'ALTER TABLE `details_fact` ADD INDEX `idx_details_fact_event_libelle` (`cod_event`, `libelle`)');

CALL isapp_add_index_if_missing('creaevent', 'idx_creaevent_date_user', 'ALTER TABLE `creaevent` ADD INDEX `idx_creaevent_date_user` (`date_enreg`, `cod_user`)');
CALL isapp_add_index_if_missing('creaevent', 'idx_creaevent_event_user', 'ALTER TABLE `creaevent` ADD INDEX `idx_creaevent_event_user` (`cod_event`, `cod_user`)');

CALL isapp_add_index_if_missing('whatsapp_message_logs', 'idx_whatsapp_logs_status_event_sent', 'ALTER TABLE `whatsapp_message_logs` ADD INDEX `idx_whatsapp_logs_status_event_sent` (`send_status`, `event_code`, `sent_at`)');
CALL isapp_add_index_if_missing('whatsapp_event_credits', 'idx_whatsapp_credits_client_event', 'ALTER TABLE `whatsapp_event_credits` ADD INDEX `idx_whatsapp_credits_client_event` (`client_user_id`, `event_code`)');

CALL isapp_add_index_if_missing('accessoires_event', 'idx_accessoires_event_event', 'ALTER TABLE `accessoires_event` ADD INDEX `idx_accessoires_event_event` (`cod_event`)');
CALL isapp_add_index_if_missing('photos_event', 'idx_photos_event_event', 'ALTER TABLE `photos_event` ADD INDEX `idx_photos_event_event` (`cod_event`)');
CALL isapp_add_index_if_missing('fichiers_impression', 'idx_fichiers_impression_event', 'ALTER TABLE `fichiers_impression` ADD INDEX `idx_fichiers_impression_event` (`cod_event`)');
CALL isapp_add_index_if_missing('amorcage_dossier', 'idx_amorcage_dossier_event', 'ALTER TABLE `amorcage_dossier` ADD INDEX `idx_amorcage_dossier_event` (`cod_event`)');
CALL isapp_add_index_if_missing('url_shortener', 'idx_url_shortener_event', 'ALTER TABLE `url_shortener` ADD INDEX `idx_url_shortener_event` (`cod_event`)');

DROP PROCEDURE isapp_add_index_if_missing;
