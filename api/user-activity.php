<?php
/**
 * User Activity Tracking API
 * Client-side activity tracking endpoint (document views, searches, filters)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require user authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$allowedActions = ['document_view', 'search', 'filter'];

if (!in_array($action, $allowedActions)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO user_activity_log (user_id, action, page, entity_type, entity_id, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $action,
        $input['page'] ?? null,
        $input['entity_type'] ?? null,
        !empty($input['entity_id']) ? (int)$input['entity_id'] : null,
        $input['details'] ?? null,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    // If document_view, also increment the document's view_count
    if ($action === 'document_view' && !empty($input['entity_id'])) {
        $stmt = $db->prepare("UPDATE documents SET view_count = view_count + 1 WHERE id = ?");
        $stmt->execute([(int)$input['entity_id']]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log("User activity tracking error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to track activity']);
}
