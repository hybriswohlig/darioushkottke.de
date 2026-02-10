<?php
require_once __DIR__ . '/includes/visitor-auth.php';

// Logout visitor
logoutVisitor();

// Redirect to login page
header('Location: /visitor-login.php');
exit;
