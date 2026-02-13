<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

if (isset($_SESSION['user_id'])) {
    // Log logout activity
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO user_activity_log (user_id, action, ip_address) VALUES (?, 'logout', ?)");
        $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Exception $e) {
        error_log("Logout log error: " . $e->getMessage());
    }
}

session_unset();
session_destroy();

header('Location: /login.php');
exit;
