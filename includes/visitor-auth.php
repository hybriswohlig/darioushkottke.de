<?php
/**
 * Visitor Authentication System
 * Protects all pages - visitors must enter password to view content
 */

require_once __DIR__ . '/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Visitor password - CHANGE THIS!
define('VISITOR_PASSWORD', 'CompliancePortal2026');

// Pages that don't require authentication (only login page)
$publicPages = [
    '/visitor-login.php',
    '/visitor-logout.php'
];

// Get current page
$currentPage = $_SERVER['PHP_SELF'];

// Check if current page is public
$isPublicPage = false;
foreach ($publicPages as $page) {
    if (strpos($currentPage, $page) !== false) {
        $isPublicPage = true;
        break;
    }
}

// If not a public page and not authenticated, redirect to login
if (!$isPublicPage) {
    if (!isset($_SESSION['visitor_authenticated']) || $_SESSION['visitor_authenticated'] !== true) {
        // Store the intended destination
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /visitor-login.php');
        exit;
    }

    // Check session timeout (4 hours)
    if (isset($_SESSION['visitor_last_activity'])) {
        $inactiveTime = time() - $_SESSION['visitor_last_activity'];
        if ($inactiveTime > 14400) { // 4 hours
            session_unset();
            session_destroy();
            header('Location: /visitor-login.php?timeout=1');
            exit;
        }
    }

    // Update last activity
    $_SESSION['visitor_last_activity'] = time();
}

/**
 * Verify visitor password
 */
function verifyVisitorPassword($password) {
    return $password === VISITOR_PASSWORD;
}

/**
 * Authenticate visitor
 */
function authenticateVisitor() {
    $_SESSION['visitor_authenticated'] = true;
    $_SESSION['visitor_last_activity'] = time();
    $_SESSION['visitor_login_time'] = time();
}

/**
 * Logout visitor
 */
function logoutVisitor() {
    session_unset();
    session_destroy();
}
