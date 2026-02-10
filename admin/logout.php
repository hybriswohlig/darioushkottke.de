<?php
require_once __DIR__ . '/../includes/functions.php';

// Log activity before destroying session
if (isAdminLoggedIn()) {
    logActivity('logout', null, null, 'Admin logged out');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: /admin/login.php');
exit;
