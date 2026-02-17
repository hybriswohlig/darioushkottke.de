<?php
/**
 * Common Utility Functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Sanitize output to prevent XSS attacks
 */
function esc($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get all categories
 */
function getCategories() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM categories ORDER BY display_order ASC");
    return $stmt->fetchAll();
}

/**
 * Get category by slug
 */
function getCategoryBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get documents by category with metadata
 */
function getDocumentsByCategory($categoryId, $filters = []) {
    $db = getDB();

    $sql = "SELECT d.* FROM documents d WHERE d.category_id = ?";
    $params = [$categoryId];

    // Public view: show published + planned + in_progress unless caller explicitly requests a specific status
    if (array_key_exists('status', $filters) && $filters['status'] !== '') {
        $sql .= " AND d.status = ?";
        $params[] = $filters['status'];
    } else {
        $sql .= " AND d.status IN ('published', 'planned', 'in_progress')";
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (d.title LIKE ? OR d.description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY d.featured DESC, d.date_published DESC, d.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();

    // Get metadata for each document
    foreach ($documents as &$doc) {
        $doc['metadata'] = getDocumentMetadata($doc['id']);
    }

    return $documents;
}

/**
 * Get document metadata
 */
function getDocumentMetadata($documentId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT meta_key, meta_value FROM document_metadata WHERE document_id = ? ORDER BY display_order ASC");
    $stmt->execute([$documentId]);
    return $stmt->fetchAll();
}

/**
 * Get single document by ID
 */
function getDocument($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM documents WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $doc = $stmt->fetch();

    if ($doc) {
        $doc['metadata'] = getDocumentMetadata($doc['id']);
    }

    return $doc;
}

/**
 * Search documents across all categories
 */
function searchDocuments($query, $categoryId = null) {
    $db = getDB();

    $sql = "SELECT d.*, c.name as category_name, c.slug as category_slug
            FROM documents d
            JOIN categories c ON d.category_id = c.id
            WHERE (d.title LIKE ? OR d.description LIKE ?) AND d.status = 'published'";

    $searchTerm = '%' . $query . '%';
    $params = [$searchTerm, $searchTerm];

    if ($categoryId) {
        $sql .= " AND d.category_id = ?";
        $params[] = $categoryId;
    }

    $sql .= " ORDER BY d.featured DESC, d.date_published DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();

    // Get metadata for each document
    foreach ($documents as &$doc) {
        $doc['metadata'] = getDocumentMetadata($doc['id']);
    }

    return $documents;
}

/**
 * Get all documents across all categories, optionally filtered by tag
 * Includes planned and in_progress documents for preview
 */
function getAllDocuments($tagFilter = null) {
    $db = getDB();

    $sql = "SELECT d.*, c.name as category_name, c.slug as category_slug
            FROM documents d
            JOIN categories c ON d.category_id = c.id
            WHERE d.status IN ('published', 'planned', 'in_progress')";
    $params = [];

    if ($tagFilter !== null && $tagFilter !== '') {
        $sql .= " AND d.tag = ?";
        $params[] = $tagFilter;
    }

    $sql .= " ORDER BY d.featured DESC, d.date_published DESC, d.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();

    foreach ($documents as &$doc) {
        $doc['metadata'] = getDocumentMetadata($doc['id']);
    }

    return $documents;
}

/**
 * Get document counts per category (published + planned + in_progress)
 */
function getCategoryDocumentCounts() {
    $db = getDB();
    $stmt = $db->query("SELECT c.slug, COUNT(d.id) as doc_count
                         FROM categories c
                         LEFT JOIN documents d ON d.category_id = c.id AND d.status IN ('published', 'planned', 'in_progress')
                         GROUP BY c.id, c.slug");
    $rows = $stmt->fetchAll();
    $counts = [];
    foreach ($rows as $row) {
        $counts[$row['slug']] = (int) $row['doc_count'];
    }
    return $counts;
}

/**
 * Get tag badge HTML
 */
function getTagBadge($tag) {
    $badges = [
        'vikang'        => '<span class="tag-badge tag-vikang">VIKANG</span>',
        'compostable'   => '<span class="tag-badge tag-compostable">Compostable</span>',
        'biodegradable' => '<span class="tag-badge tag-biodegradable">Biodegradable</span>',
    ];

    return $badges[$tag ?? ''] ?? '<span class="tag-badge tag-untagged">Untagged</span>';
}

/**
 * Increment document view count
 */
function incrementViewCount($documentId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE documents SET view_count = view_count + 1 WHERE id = ?");
    return $stmt->execute([$documentId]);
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) &&
           isset($_SESSION['admin_username']) &&
           isset($_SESSION['last_activity']) &&
           (time() - $_SESSION['last_activity'] < ADMIN_SESSION_TIMEOUT);
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

/**
 * Verify admin credentials
 */
function verifyAdminLogin($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Update last login
        $updateStmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);

        return $user;
    }

    return false;
}

/**
 * Log admin activity
 */
function logActivity($action, $entityType = null, $entityId = null, $details = null) {
    if (!isset($_SESSION['admin_id'])) return;

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO activity_log (admin_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['admin_id'],
        $action,
        $entityType,
        $entityId,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'published' => '<span class="status-badge status-published">Published</span>',
        'draft' => '<span class="status-badge status-draft">Draft (hidden)</span>',
        'under_review' => '<span class="status-badge status-review">Under Review</span>',
        'in_progress' => '<span class="status-badge status-progress">In Progress</span>',
        'planned' => '<span class="status-badge status-planned">Planned</span>',
        'pending' => '<span class="status-badge status-pending">Pending</span>',
    ];

    return $badges[$status] ?? '<span class="status-badge">' . esc($status) . '</span>';
}

/**
 * Generate JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Generate a temporary password for new user accounts
 */
function generateTempPassword($length = 10) {
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    if (!preg_match('/[0-9]/', $password)) {
        $password[strlen($password) - 1] = (string)random_int(2, 9);
    }
    return $password;
}

/**
 * Validate password against rules (min 8 chars, at least 1 number)
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must contain at least one number.';
    }
    return null;
}

/**
 * Get user by ID
 */
function getUserById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, full_name, email, company, status, expiry_date, must_change_password, last_login, created_at, updated_at FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Get all users
 */
function getAllUsers() {
    $db = getDB();
    $stmt = $db->query("SELECT id, full_name, email, company, status, expiry_date, must_change_password, last_login, created_at FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Log user activity
 */
function logUserActivity($action, $page = null, $entityType = null, $entityId = null, $details = null) {
    if (!isset($_SESSION['user_id'])) return;

    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO user_activity_log (user_id, action, page, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $page,
            $entityType,
            $entityId,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (PDOException $e) {
        error_log("User activity log error: " . $e->getMessage());
    }
}
