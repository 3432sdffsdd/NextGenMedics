-- ============================================================
-- Migration 004: track who uploaded each lecture material
-- Safe to run once against an existing nextgen_medics database.
-- Adds uploaded_by only if the column is missing.
-- ============================================================

USE nextgen_medics;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'lecture_resources'
      AND COLUMN_NAME = 'uploaded_by'
);

SET @sql = IF(
    @col_exists = 0,
    'ALTER TABLE lecture_resources
        ADD COLUMN uploaded_by INT UNSIGNED NULL AFTER sort_order,
        ADD CONSTRAINT fk_resource_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
