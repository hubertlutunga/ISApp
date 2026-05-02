ALTER TABLE invite
    ADD COLUMN statut_envoi_whatsapp VARCHAR(32) NULL AFTER sing,
    ADD COLUMN twilio_message_sid VARCHAR(64) NULL AFTER statut_envoi_whatsapp,
    ADD COLUMN date_envoi_whatsapp DATETIME NULL AFTER twilio_message_sid,
    ADD COLUMN erreur_twilio TEXT NULL AFTER date_envoi_whatsapp;