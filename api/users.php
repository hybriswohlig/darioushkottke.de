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
        handleGet();
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
            INSERT INTO users (full_name, email, company, password_hash, must_change_password, status, expiry_date)
            VALUES (?, ?, ?, ?, 1, ?, ?)
        ");
        $stmt->execute([
            $input['full_name'],
            $input['email'],
            $input['company'] ?? null,
            $hash,
            $input['status'] ?? 'active',
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

    try {
        $updateFields = [];
        $params = [];

        $allowedFields = ['full_name', 'email', 'company', 'status', 'expiry_date'];

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
