-- Migration: add simplified portal roles and document visibility flag
-- Run this on existing installations after the base schema has been applied
-- Compatible with MySQL 5.7+

SET @has_users_access_role := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'access_role'
);
SET @sql := IF(
    @has_users_access_role = 0,
    "ALTER TABLE users ADD COLUMN access_role ENUM('normal', 'simplified') NOT NULL DEFAULT 'normal' AFTER status",
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_documents_visible := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'documents'
      AND COLUMN_NAME = 'visible_in_simplified'
);
SET @sql := IF(
    @has_documents_visible = 0,
    "ALTER TABLE documents ADD COLUMN visible_in_simplified TINYINT(1) NOT NULL DEFAULT 0 AFTER featured",
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_documents_visible_idx := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'documents'
      AND INDEX_NAME = 'idx_visible_in_simplified'
);
SET @sql := IF(
    @has_documents_visible_idx = 0,
    'ALTER TABLE documents ADD INDEX idx_visible_in_simplified (visible_in_simplified)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
