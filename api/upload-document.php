<?php
/**
 * PDF Upload Endpoint
 * Handles file upload for PDF documents (admin only)
 * Accepts multipart/form-data with a 'pdf_file' field
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require admin authentication
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Check that a file was uploaded
if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] === UPLOAD_ERR_NO_FILE) {
    jsonResponse(['error' => 'No file uploaded'], 400);
}

$file = $_FILES['pdf_file'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension',
    ];
    $msg = $errorMessages[$file['error']] ?? 'Unknown upload error';
    jsonResponse(['error' => $msg], 400);
}

// Validate file size
if ($file['size'] > MAX_FILE_SIZE) {
    $maxMB = round(MAX_FILE_SIZE / 1048576);
    jsonResponse(['error' => "File size exceeds {$maxMB}MB limit"], 400);
}

// Validate MIME type using finfo (server-side, not client-reported)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if ($mimeType !== 'application/pdf') {
    jsonResponse(['error' => 'Invalid file type. Only PDF files are allowed'], 400);
}

// Validate PDF magic bytes
$handle = fopen($file['tmp_name'], 'rb');
$header = fread($handle, 5);
fclose($handle);
if ($header !== '%PDF-') {
    jsonResponse(['error' => 'File does not appear to be a valid PDF'], 400);
}

// Validate file extension
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($extension !== 'pdf') {
    jsonResponse(['error' => 'File must have a .pdf extension'], 400);
}

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        error_log("Failed to create upload directory: " . UPLOAD_DIR);
        jsonResponse(['error' => 'Server configuration error'], 500);
    }
}

// Generate unique filename: timestamp + sanitized original name
$sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
$sanitizedName = substr($sanitizedName, 0, 100); // Limit length
$uniqueName = time() . '_' . $sanitizedName . '.pdf';
$destinationPath = UPLOAD_DIR . $uniqueName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
    error_log("Failed to move uploaded file to: " . $destinationPath);
    jsonResponse(['error' => 'Failed to save uploaded file'], 500);
}

// Log activity
logActivity('upload_pdf', 'document', null, "Uploaded PDF: {$file['name']}");

// Return the relative path for storage in database
jsonResponse([
    'success' => true,
    'file_path' => 'uploads/documents/' . $uniqueName,
    'original_name' => $file['name'],
    'file_size' => $file['size']
], 201);
