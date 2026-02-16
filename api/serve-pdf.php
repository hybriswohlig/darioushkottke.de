<?php
/**
 * Serve PDF Endpoint
 * Authenticates the user and streams a PDF file for the embedded viewer.
 * PDFs are not directly accessible — they must go through this script.
 */

require_once __DIR__ . '/../includes/user-auth.php';

// Validate document ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid document ID');
}

// Fetch document
$doc = getDocument($id);
if (!$doc) {
    http_response_code(404);
    exit('Document not found');
}

// Verify it's a PDF document and published
if (($doc['document_type'] ?? '') !== 'pdf') {
    http_response_code(400);
    exit('This document is not a PDF');
}

if ($doc['status'] !== 'published') {
    http_response_code(403);
    exit('This document is not available');
}

// Verify file exists on disk
if (empty($doc['file_path'])) {
    http_response_code(404);
    exit('PDF file path not found');
}

$fullPath = __DIR__ . '/../' . $doc['file_path'];
if (!file_exists($fullPath)) {
    error_log("PDF file not found on disk: " . $fullPath);
    http_response_code(404);
    exit('PDF file not found');
}

// Increment view count
incrementViewCount($id);

// Log the view
logUserActivity('document_view', $_SERVER['REQUEST_URI'], 'document', $id, 'PDF viewer');

// Send the PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . preg_replace('/[^a-zA-Z0-9._-]/', '', $doc['title']) . '.pdf"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

readfile($fullPath);
exit;
