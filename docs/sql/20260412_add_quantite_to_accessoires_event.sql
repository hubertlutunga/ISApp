SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'accessoires_event'
      AND COLUMN_NAME = 'quantite'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE accessoires_event ADD COLUMN quantite INT NOT NULL DEFAULT 1 AFTER cod_acc',
    'SELECT "La colonne quantite existe deja dans accessoires_event." AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


