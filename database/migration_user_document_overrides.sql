-- Migration: add per-user document access overrides
-- Allows admins to manually allow/deny specific documents per user.
-- Compatible with MySQL 5.7+

CREATE TABLE IF NOT EXISTS user_document_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_id INT NOT NULL,
    access_state ENUM('allow', 'deny') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_document_access_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_document_access_document
        FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_document_access (user_id, document_id),
    INDEX idx_user_access (user_id, access_state),
    INDEX idx_document_access (document_id, access_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
