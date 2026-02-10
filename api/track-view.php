<?php
/**
 * Track Document View API Endpoint
 * Increments view count for a document
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($input['document_id']) || !is_numeric($input['document_id'])) {
    jsonResponse(['error' => 'Invalid document ID'], 400);
}

$documentId = (int)$input['document_id'];

// Verify document exists
$document = getDocument($documentId);
if (!$document) {
    jsonResponse(['error' => 'Document not found'], 404);
}

// Increment view count
if (incrementViewCount($documentId)) {
    jsonResponse([
        'success' => true,
        'document_id' => $documentId,
        'view_count' => $document['view_count'] + 1
    ]);
} else {
    jsonResponse(['error' => 'Failed to update view count'], 500);
}
