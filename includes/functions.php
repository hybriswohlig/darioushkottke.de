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
 * Supported portal access roles.
 */
function getSupportedUserAccessRoles() {
    return ['normal', 'simplified'];
}

/**
 * Check whether a user access role is supported.
 */
function isSupportedUserAccessRole($role) {
    return in_array($role, getSupportedUserAccessRoles(), true);
}

/**
 * Normalize a user access role to a safe default.
 */
function normalizeUserAccessRole($role) {
    return isSupportedUserAccessRole($role) ? $role : 'normal';
}

/**
 * Get the current authenticated user's access role.
 */
function getCurrentUserAccessRole() {
    return normalizeUserAccessRole($_SESSION['user_access_role'] ?? 'normal');
}

/**
 * Whether the current role is the simplified document view.
 */
function isSimplifiedUserRole($role = null) {
    return normalizeUserAccessRole($role ?? getCurrentUserAccessRole()) === 'simplified';
}

/**
 * SQL clause to hide non-simplified documents for simplified users.
 */
function getDocumentVisibilitySqlClause($tableAlias = 'd', $role = null) {
    if (!isSimplifiedUserRole($role)) {
        return '';
    }

    return " AND COALESCE({$tableAlias}.visible_in_simplified, 0) = 1";
}

/**
 * Check whether a document is visible to a given user role.
 */
function canUserAccessDocument($doc, $role = null) {
    if (!$doc) {
        return false;
    }

    if (!isSimplifiedUserRole($role)) {
        return true;
    }

    return !empty($doc['visible_in_simplified']);
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

    $sql .= getDocumentVisibilitySqlClause('d');

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
 * Get document metadata (raw key-value pairs)
 */
function getDocumentMetadata($documentId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT meta_key, meta_value FROM document_metadata WHERE document_id = ? ORDER BY display_order ASC");
    $stmt->execute([$documentId]);
    return $stmt->fetchAll();
}

/**
 * Get the metadata field schema for a category
 */
function getCategoryMetadataSchema($categoryId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM category_metadata_fields WHERE category_id = ? ORDER BY display_order ASC");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}

/**
 * Format a single metadata value for display using its field definition.
 * Returns null if the value is empty so the caller can skip it.
 */
function formatMetadataValue($fieldDef, $rawValue, $doc = null) {
    if ($rawValue === null || $rawValue === '') {
        return null;
    }

    $type = $fieldDef['field_type'];
    $opts = $fieldDef['field_options'] ? json_decode($fieldDef['field_options'], true) : [];

    switch ($type) {
        case 'date_from_doc':
            $date = $doc['date_published'] ?? null;
            return $date ? formatDate($date) : null;

        case 'number_unit':
            $unit = $opts['unit'] ?? '';
            return esc($rawValue) . ' ' . esc($unit);

        case 'range':
            $unit = $opts['unit'] ?? '';
            $parts = explode('|', $rawValue);
            if (count($parts) === 2) {
                return esc($parts[0]) . ' – ' . esc($parts[1]) . ' ' . esc($unit);
            }
            return esc($rawValue) . ' ' . esc($unit);

        case 'boolean':
            return $rawValue === '1' ? 'Yes' : 'No';

        case 'dropdown_multi':
        case 'freetext_multi':
            $arr = json_decode($rawValue, true);
            if (!is_array($arr) || empty($arr)) {
                return null;
            }
            return implode(', ', array_map('esc', $arr));

        default:
            return esc($rawValue);
    }
}

/**
 * Logo filenames for Issuer / Issuing Body / Done by (frontend display only).
 * Key = exact value as stored in document_metadata.
 */
function getIssuerBodyLogoMap() {
    return [
        'N&E Innovations' => 'N&E LOGO Colour.svg',
        'SGS' => 'SGS_SA.svg',
        'SGS Thailand' => 'SGS_SA.svg',
        'SGS Singapore' => 'SGS_SA.svg',
        'TÜV Austria' => 'tuev austria.svg',
        'Intertek' => 'Logo_Intertek_01.svg',
    ];
}

/**
 * Get frontend logo path for an Issuer, Issuing Body or Done by value, or null if no logo.
 * For use only when displaying documents on the compliance portal (not in admin).
 */
function getIssuerBodyLogoPath($rawValue) {
    if ($rawValue === null || $rawValue === '') {
        return null;
    }
    $map = getIssuerBodyLogoMap();
    if (!isset($map[$rawValue])) {
        return null;
    }
    $filename = $map[$rawValue];
    $base = '/assets/images/logos/';
    return $base . rawurlencode($filename);
}

/**
 * Display order for card metadata: Done by, Issuer, Issuing Body first, then rest in schema order.
 */
function getMetadataDisplayOrder() {
    return ['done_by' => 0, 'issuer' => 1, 'issuing_body' => 2];
}

/**
 * Build a display-ready metadata array for a document.
 * Only includes fields that have a value. Uses the category schema for labels and formatting.
 * Done by / Issuer / Issuing Body are always ordered first in the card; logo_path is set when a logo exists (frontend only).
 */
function getFormattedDocumentMetadata($doc) {
    $schema = getCategoryMetadataSchema($doc['category_id']);
    if (empty($schema)) {
        return [];
    }

    $rawMeta = [];
    if (!empty($doc['metadata'])) {
        foreach ($doc['metadata'] as $m) {
            $rawMeta[$m['meta_key']] = $m['meta_value'];
        }
    }

    $order = getMetadataDisplayOrder();
    $result = [];
    $idx = 0;
    foreach ($schema as $field) {
        $key = $field['field_key'];

        if ($field['field_type'] === 'date_from_doc') {
            $formatted = formatMetadataValue($field, 'auto', $doc);
        } else {
            $raw = $rawMeta[$key] ?? null;
            $formatted = formatMetadataValue($field, $raw, $doc);
        }

        if ($formatted !== null) {
            $entry = [
                'field_key' => $key,
                'label' => $field['field_label'],
                'value' => $formatted,
                '_sort' => $order[$key] ?? 99,
                '_idx' => $idx++,
            ];
            // Add logo path for Done by / Issuer / Issuing Body when we have a logo (frontend only)
            if (($key === 'done_by' || $key === 'issuer' || $key === 'issuing_body') && $raw !== null) {
                $logoPath = getIssuerBodyLogoPath($raw);
                if ($logoPath !== null) {
                    $entry['logo_path'] = $logoPath;
                }
            }
            $result[] = $entry;
        }
    }

    usort($result, function ($a, $b) {
        if ($a['_sort'] !== $b['_sort']) {
            return $a['_sort'] - $b['_sort'];
        }
        return $a['_idx'] - $b['_idx'];
    });

    // Remove sort keys before returning
    foreach ($result as &$e) {
        unset($e['_sort'], $e['_idx']);
    }

    return $result;
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
 * Get a single document only if the current user role can access it.
 */
function getAccessibleDocument($id, $role = null) {
    $doc = getDocument($id);
    if (!$doc || !canUserAccessDocument($doc, $role)) {
        return null;
    }

    return $doc;
}

/**
 * Search documents across all categories
 * Uses same status filter as portal (published, planned, in_progress) so search results match what users see when browsing.
 */
function searchDocuments($query, $categoryId = null) {
    $db = getDB();
    $query = trim($query);
    if ($query === '') {
        return [];
    }

    $sql = "SELECT d.*, c.name as category_name, c.slug as category_slug
            FROM documents d
            JOIN categories c ON d.category_id = c.id
            WHERE (d.title LIKE ? OR d.description LIKE ?) AND d.status IN ('published', 'planned', 'in_progress')";
    $sql .= getDocumentVisibilitySqlClause('d');

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
    $sql .= getDocumentVisibilitySqlClause('d');
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
    $sql = "SELECT c.slug, COUNT(d.id) as doc_count
            FROM categories c
            LEFT JOIN documents d ON d.category_id = c.id
                AND d.status IN ('published', 'planned', 'in_progress')";
    if (isSimplifiedUserRole()) {
        $sql .= " AND COALESCE(d.visible_in_simplified, 0) = 1";
    }
    $sql .= " GROUP BY c.id, c.slug";
    $stmt = $db->query($sql);
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
 * Get user access role badge HTML for admin screens.
 */
function getUserAccessRoleBadge($role) {
    $role = normalizeUserAccessRole($role);
    $badges = [
        'normal' => '<span class="status-badge" style="background: #dcfce7; color: #166534;">Normal</span>',
        'simplified' => '<span class="status-badge" style="background: #dbeafe; color: #1d4ed8;">Simplified</span>',
    ];

    return $badges[$role];
}

/**
 * Get simplified visibility badge HTML for admin screens.
 */
function getSimplifiedVisibilityBadge($visible) {
    if ((int) $visible === 1) {
        return '<span class="status-badge" style="background: #dcfce7; color: #166534;">Yes</span>';
    }

    return '<span class="status-badge" style="background: #f3f4f6; color: #4b5563;">No</span>';
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
    $stmt = $db->prepare("SELECT id, full_name, email, company, status, access_role, expiry_date, must_change_password, last_login, created_at, updated_at FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Get all users
 */
function getAllUsers() {
    $db = getDB();
    $stmt = $db->query("SELECT id, full_name, email, company, status, access_role, expiry_date, must_change_password, last_login, created_at FROM users ORDER BY created_at DESC");
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
