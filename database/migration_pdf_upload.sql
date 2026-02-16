-- Migration: Add PDF upload support to documents table
-- Run this AFTER schema.sql and any existing migrations

-- Add document_type column with default 'link' for backward compatibility
ALTER TABLE documents
    ADD COLUMN document_type ENUM('link', 'pdf', 'html') NOT NULL DEFAULT 'link' AFTER file_url,
    ADD COLUMN file_path VARCHAR(500) DEFAULT NULL AFTER document_type,
    ADD INDEX idx_document_type (document_type);

-- Backfill existing data based on current file_url patterns
UPDATE documents SET document_type = 'html' WHERE file_url LIKE '%.html';
UPDATE documents SET document_type = 'link' WHERE file_url LIKE 'http%';

-- Download audit trail for tracking watermarked PDF downloads
CREATE TABLE IF NOT EXISTS document_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_document (document_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
