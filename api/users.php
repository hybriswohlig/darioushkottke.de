<?php
/**
 * Users API Endpoint
 * CRUD operations for user accounts (admin only)
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require admin authentication
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        if ($action === 'document_access') {
            handleGetDocumentAccess();
        } else {
            handleGet();
        }
        break;

    case 'POST':
        handleCreate($input);
        break;

    case 'PUT':
        handleUpdate($input);
        break;

    case 'PATCH':
        $action = $_GET['action'] ?? '';
        if ($action === 'reset_password') {
            handleResetPassword($input);
        } elseif ($action === 'toggle_status') {
            handleToggleStatus($input);
        } elseif ($action === 'document_access') {
            handleUpdateDocumentAccess($input);
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

/**
 * Get users
 */
function handleGet() {
    if (isset($_GET['id'])) {
        $user = getUserById((int)$_GET['id']);
        if ($user) {
            jsonResponse(['success' => true, 'user' => $user]);
        } else {
            jsonResponse(['error' => 'User not found'], 404);
        }
    }

    $users = getAllUsers();
    jsonResponse(['success' => true, 'users' => $users]);
}

/**
 * Create new user
 */
function handleCreate($input) {
    $required = ['full_name', 'email'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonResponse(['error' => "Missing required field: $field"], 400);
        }
    }

    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Invalid email address'], 400);
    }

    $accessRole = $input['access_role'] ?? 'normal';
    if (!isSupportedUserAccessRole($accessRole)) {
        jsonResponse(['error' => 'Invalid access role'], 400);
    }

    $db = getDB();

    // Check email uniqueness
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()['count'] > 0) {
        jsonResponse(['error' => 'A user with this email already exists'], 400);
    }

    // Generate temp password
    $tempPassword = generateTempPassword();
    $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("
            INSERT INTO users (full_name, email, company, password_hash, must_change_password, status, access_role, expiry_date)
            VALUES (?, ?, ?, ?, 1, ?, ?, ?)
        ");
        $stmt->execute([
            $input['full_name'],
            $input['email'],
            $input['company'] ?? null,
            $hash,
            $input['status'] ?? 'active',
            $accessRole,
            !empty($input['expiry_date']) ? $input['expiry_date'] : null
        ]);

        $userId = $db->lastInsertId();

        logActivity('create_user', 'user', $userId, "Created user: {$input['full_name']} ({$input['email']})");

        jsonResponse([
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $userId,
            'temp_password' => $tempPassword
        ], 201);

    } catch (PDOException $e) {
        error_log("User creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to create user'], 500);
    }
}

/**
 * Update user details
 */
function handleUpdate($input) {
    if (empty($input['id'])) {
        jsonResponse(['error' => 'Missing user ID'], 400);
    }

    $db = getDB();

    // Check user exists
    $existing = getUserById((int)$input['id']);
    if (!$existing) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    // If email is being changed, check uniqueness
    if (!empty($input['email']) && $input['email'] !== $existing['email']) {
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Invalid email address'], 400);
        }
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$input['email'], $input['id']]);
        if ($stmt->fetch()['count'] > 0) {
            jsonResponse(['error' => 'A user with this email already exists'], 400);
        }
    }

    if (isset($input['access_role']) && !isSupportedUserAccessRole($input['access_role'])) {
        jsonResponse(['error' => 'Invalid access role'], 400);
    }

    try {
        $updateFields = [];
        $params = [];

        $allowedFields = ['full_name', 'email', 'company', 'status', 'access_role', 'expiry_date'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                if ($field === 'expiry_date' && $input[$field] === '') {
                    $updateFields[] = "$field = NULL";
                } else {
                    $updateFields[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
        }

        if (empty($updateFields)) {
            jsonResponse(['error' => 'No fields to update'], 400);
        }

        $params[] = $input['id'];

        $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        logActivity('update_user', 'user', $input['id'], "Updated user: {$input['full_name']}");

        jsonResponse([
            'success' => true,
            'message' => 'User updated successfully'
        ]);

    } catch (PDOException $e) {
        error_log("User update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update user'], 500);
    }
}

/**
 * Reset user password
 */
function handleResetPassword($input) {
    if (empty($input['id'])) {
        jsonResponse(['error' => 'Missing user ID'], 400);
    }

    $db = getDB();
    $tempPassword = generateTempPassword();
    $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, must_change_password = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hash, $input['id']]);

        logActivity('reset_user_password', 'user', $input['id'], "Reset password for user");

        jsonResponse([
            'success' => true,
            'temp_password' => $tempPassword
        ]);

    } catch (PDOException $e) {
        error_log("Password reset error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to reset password'], 500);
    }
}

/**
 * Toggle user active/inactive status
 */
function handleToggleStatus($input) {
    if (empty($input['id'])) {
        jsonResponse(['error' => 'Missing user ID'], 400);
    }

    $db = getDB();

    try {
        $stmt = $db->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$input['id']]);
        $user = $stmt->fetch();

        if (!$user) {
            jsonResponse(['error' => 'User not found'], 404);
        }

        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        $updateStmt = $db->prepare("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$newStatus, $input['id']]);

        logActivity('toggle_user_status', 'user', $input['id'], "Changed status to: $newStatus");

        jsonResponse(['success' => true, 'new_status' => $newStatus]);

    } catch (PDOException $e) {
        error_log("Status toggle error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to toggle status'], 500);
    }
}

/**
 * Get effective document access list for a user.
 */
function handleGetDocumentAccess() {
    if (!hasUserDocumentAccessTable()) {
        jsonResponse(['error' => 'Document access overrides are not available. Run migration_user_document_overrides.sql first.'], 400);
    }

    $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
    if ($userId <= 0) {
        jsonResponse(['error' => 'Missing user ID'], 400);
    }

    $user = getUserById($userId);
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    $db = getDB();
    $stmt = $db->prepare("
        SELECT d.id, d.title, d.status, c.name AS category_name,
               COALESCE(d.visible_in_simplified, 0) AS visible_in_simplified,
               uda.access_state
        FROM documents d
        JOIN categories c ON c.id = d.category_id
        LEFT JOIN user_document_access uda
            ON uda.user_id = ? AND uda.document_id = d.id
        WHERE d.status IN ('published', 'planned', 'in_progress')
        ORDER BY c.name ASC, d.title ASC
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $role = normalizeUserAccessRole($user['access_role'] ?? 'normal');
    $documents = [];
    foreach ($rows as $row) {
        $override = $row['access_state'] ?: null;
        $defaultAllowed = $role === 'normal' ? true : ((int)$row['visible_in_simplified'] === 1);
        $effective = $defaultAllowed;
        if ($override === 'allow') {
            $effective = true;
        } elseif ($override === 'deny') {
            $effective = false;
        }

        $documents[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'category_name' => $row['category_name'],
            'status' => $row['status'],
            'visible_in_simplified' => (int) $row['visible_in_simplified'],
            'default_allowed' => $defaultAllowed,
            'override_state' => $override,
            'effective_allowed' => $effective,
        ];
    }

    jsonResponse([
        'success' => true,
        'user' => [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'access_role' => $role,
        ],
        'documents' => $documents,
    ]);
}

/**
 * Replace user-specific document access overrides.
 */
function handleUpdateDocumentAccess($input) {
    if (!hasUserDocumentAccessTable()) {
        jsonResponse(['error' => 'Document access overrides are not available. Run migration_user_document_overrides.sql first.'], 400);
    }

    $userId = isset($input['user_id']) ? (int) $input['user_id'] : 0;
    if ($userId <= 0) {
        jsonResponse(['error' => 'Missing user ID'], 400);
    }
    if (!isset($input['enabled_document_ids']) || !is_array($input['enabled_document_ids'])) {
        jsonResponse(['error' => 'enabled_document_ids must be an array'], 400);
    }

    $user = getUserById($userId);
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    $enabled = [];
    foreach ($input['enabled_document_ids'] as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $enabled[$id] = true;
        }
    }
    $enabledIds = array_keys($enabled);

    $db = getDB();
    try {
        $db->beginTransaction();

        $stmt = $db->prepare("
            SELECT id, COALESCE(visible_in_simplified, 0) AS visible_in_simplified
            FROM documents
            WHERE status IN ('published', 'planned', 'in_progress')
        ");
        $stmt->execute();
        $docs = $stmt->fetchAll();

        $role = normalizeUserAccessRole($user['access_role'] ?? 'normal');
        $upsert = $db->prepare("
            INSERT INTO user_document_access (user_id, document_id, access_state)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE access_state = VALUES(access_state), updated_at = CURRENT_TIMESTAMP
        ");
        $delete = $db->prepare("DELETE FROM user_document_access WHERE user_id = ? AND document_id = ?");

        foreach ($docs as $doc) {
            $docId = (int) $doc['id'];
            $defaultAllowed = $role === 'normal' ? true : ((int) $doc['visible_in_simplified'] === 1);
            $shouldAllow = isset($enabled[$docId]);

            if ($shouldAllow === $defaultAllowed) {
                $delete->execute([$userId, $docId]);
                continue;
            }

            $state = $shouldAllow ? 'allow' : 'deny';
            $upsert->execute([$userId, $docId, $state]);
        }

        $db->commit();
        logActivity('update_user_document_access', 'user', $userId, "Updated document access overrides");
        jsonResponse(['success' => true, 'message' => 'Document access updated']);
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Document access update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update document access'], 500);
    }
}
