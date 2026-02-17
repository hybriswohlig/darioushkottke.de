<?php
/**
 * Watermarked PDF Download Endpoint
 * Generates a PDF with a footer watermark on every page containing:
 * user's full name, company, email, and download date/time.
 * This enables tracking of document sharing.
 */

// Start output buffering early to catch any stray output from includes
ob_start();

require_once __DIR__ . '/../includes/user-auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Allow longer execution for large PDFs
set_time_limit(120);

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

// Verify file exists
if (empty($doc['file_path'])) {
    http_response_code(404);
    exit('PDF file path not found');
}

$fullPath = __DIR__ . '/../' . $doc['file_path'];
if (!file_exists($fullPath)) {
    error_log("PDF file not found for download: " . $fullPath);
    http_response_code(404);
    exit('PDF file not found');
}

// Get current user info for watermark
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    http_response_code(403);
    exit('User not found');
}

// Build watermark text
$downloadDate = date('Y-m-d H:i:s T');
$watermarkText = 'Downloaded by: ' . ($user['full_name'] ?? 'Unknown') .
    ' | ' . ($user['company'] ?? 'N/A') .
    ' | ' . ($user['email'] ?? 'N/A') .
    ' | ' . $downloadDate;

// Generate watermarked PDF using FPDI + TCPDF
try {
    $pdf = new Fpdi();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pageCount = $pdf->setSourceFile($fullPath);

    for ($i = 1; $i <= $pageCount; $i++) {
        $templateId = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($templateId);

        // Add page with same dimensions and orientation
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);

        // Draw watermark footer
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(128, 128, 128);
        $footerY = $size['height'] - 8; // 8mm from bottom
        $pdf->SetXY(10, $footerY);
        $pdf->Cell($size['width'] - 20, 5, $watermarkText, 0, 0, 'C');
    }

    // Generate PDF as string first (avoids header conflicts from prior output)
    $pdfContent = $pdf->Output('', 'S');

    // Log the download in document_downloads table
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO document_downloads (document_id, user_id, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([
            $id,
            $_SESSION['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (PDOException $e) {
        error_log("Download log error: " . $e->getMessage());
    }

    // Log in user activity
    logUserActivity('document_download', $_SERVER['REQUEST_URI'], 'document', $id, 'Watermarked PDF download');

    // Clean any output buffers that may have been started by includes
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Send clean headers and PDF content
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $doc['title']) . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    echo $pdfContent;
    exit;

} catch (Exception $e) {
    error_log("PDF watermarking error: " . $e->getMessage());
    // Clean output buffers before sending error
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to generate watermarked PDF. Please try again.']);
    exit;
}
