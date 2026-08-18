-- Durcissement des privileges SQL pour le compte applicatif ISApp
-- Objectif: eviter DROP en routine et limiter les droits au strict necessaire.

-- 1) Creer un compte applicatif dedie (a adapter)
CREATE USER IF NOT EXISTS 'isapp_app'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';

-- 2) Nettoyer les privileges globaux
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'isapp_app'@'localhost';

-- 3) Attribuer uniquement les droits operationnels usuels de l'application
-- IMPORTANT: pas de privilege DROP.
GRANT
  SELECT,
  INSERT,
  UPDATE,
  DELETE,
  CREATE,
  ALTER,
  INDEX,
  CREATE TEMPORARY TABLES,
  LOCK TABLES,
  EXECUTE
ON `invizfxg_is`.* TO 'isapp_app'@'localhost';

-- 4) Optionnel: retirer explicitement DROP d'un compte legacy si besoin
-- REVOKE DROP ON `invizfxg_is`.* FROM 'invizfxg_hubert'@'localhost';
-- REVOKE DROP ON `invizfxg_is`.* FROM 'root'@'localhost'; -- NE PAS FAIRE EN PROD SANS ANALYSE

FLUSH PRIVILEGES;

-- 5) Verification rapide
-- SHOW GRANTS FOR 'isapp_app'@'localhost';
