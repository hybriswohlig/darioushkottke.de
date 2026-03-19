<?php
/**
 * Authenticated HTML document viewer.
 * Streams local HTML report files only after portal access checks pass.
 */

require_once __DIR__ . '/includes/user-auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /');
    exit;
}

$doc = getAccessibleDocument($id);
if (!$doc || ($doc['document_type'] ?? '') !== 'html' || $doc['status'] !== 'published') {
    header('Location: /');
    exit;
}

$relativePath = ltrim((string) ($doc['file_url'] ?? ''), '/');
if ($relativePath === '' || strpos($relativePath, '..') !== false || substr($relativePath, -5) !== '.html') {
    http_response_code(404);
    exit('HTML document not found');
}

$fullPath = __DIR__ . '/' . $relativePath;
if (!is_file($fullPath)) {
    http_response_code(404);
    exit('HTML document not found');
}

logUserActivity('document_view', $_SERVER['REQUEST_URI'], 'document', $id, 'HTML document viewer');

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

readfile($fullPath);
exit;
