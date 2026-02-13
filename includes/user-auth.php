<?php
/**
 * User Authentication System
 * Protects all pages - users must log in with individual accounts
 * Replaces the old shared visitor password system (visitor-auth.php)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Pages that don't require authentication
$publicPages = [
    '/login.php',
    '/logout.php',
    '/account-expired.php'
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

// Global variable for expiry warning banner
$GLOBALS['user_expiry_warning_days'] = null;

// If not a public page, enforce authentication
if (!$isPublicPage) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }

    // Check session timeout (4 hours)
    if (isset($_SESSION['user_last_activity'])) {
        $inactiveTime = time() - $_SESSION['user_last_activity'];
        if ($inactiveTime > 14400) {
            session_unset();
            session_destroy();
            header('Location: /login.php?timeout=1');
            exit;
        }
    }

    // Check account status and expiry from database (real-time enforcement)
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT status, expiry_date FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $userRow = $stmt->fetch();

        if (!$userRow || $userRow['status'] === 'inactive') {
            session_unset();
            session_destroy();
            header('Location: /account-expired.php?reason=inactive');
            exit;
        }

        if ($userRow['expiry_date'] !== null && strtotime($userRow['expiry_date']) < strtotime('today')) {
            session_unset();
            session_destroy();
            header('Location: /account-expired.php?reason=expired');
            exit;
        }

        // Calculate expiry warning (7 days)
        if ($userRow['expiry_date'] !== null) {
            $expiryTimestamp = strtotime($userRow['expiry_date']);
            $daysRemaining = (int)ceil(($expiryTimestamp - strtotime('today')) / 86400);
            if ($daysRemaining <= 7 && $daysRemaining > 0) {
                $GLOBALS['user_expiry_warning_days'] = $daysRemaining;
            }
        }
    } catch (PDOException $e) {
        error_log("User auth check error: " . $e->getMessage());
    }

    // Force password change if required
    if (!empty($_SESSION['user_must_change_password']) && strpos($currentPage, '/change-password.php') === false) {
        header('Location: /change-password.php');
        exit;
    }

    // Update last activity
    $_SESSION['user_last_activity'] = time();

    // Log page view activity
    logUserActivity('page_view', $_SERVER['REQUEST_URI']);
}
