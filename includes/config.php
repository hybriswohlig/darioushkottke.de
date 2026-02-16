<?php
/**
 * Configuration File for N&E Innovations Compliance Portal
 *
 * IMPORTANT: Update these values with your actual database credentials from your AWS Lightsail instance
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'N&E Innovations');
define('SITE_TAGLINE', 'Compliance Documentation Portal');
define('SITE_URL', 'https://your-domain.com'); // Update with your actual domain
define('CONTACT_EMAIL', 'business@vi-kang.com');
define('CONTACT_URL', 'https://vi-kang.com/contact/');

// Admin Configuration
define('ADMIN_SESSION_NAME', 'ne_admin_session');
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Security Settings
define('SECURE_KEY', 'CHANGE_THIS_TO_A_RANDOM_STRING'); // Change this to a random string for security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);   // Set to 1 if using HTTPS
ini_set('session.use_strict_mode', 1);

// File Upload Settings
define('MAX_FILE_SIZE', 20971520); // 20MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('UPLOAD_DIR', __DIR__ . '/../uploads/documents/');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Timezone
date_default_timezone_set('Asia/Singapore');

// Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_name(ADMIN_SESSION_NAME);
    session_start();
}
