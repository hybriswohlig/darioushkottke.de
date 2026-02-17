<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// If user doesn't need to change password, redirect to home
if (empty($_SESSION['user_must_change_password'])) {
    header('Location: /');
    exit;
}

$error = '';
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $validationError = validatePassword($newPassword);

    if ($validationError) {
        $error = $validationError;
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, must_change_password = 0, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hash, $userId]);

        $_SESSION['user_must_change_password'] = false;

        // Log activity
        $logStmt = $db->prepare("INSERT INTO user_activity_log (user_id, action, details, ip_address) VALUES (?, 'password_change', 'Initial password change', ?)");
        $logStmt->execute([$userId, $_SERVER['REMOTE_ADDR'] ?? null]);

        // Redirect to intended page or home
        $redirect = $_SESSION['redirect_after_login'] ?? '/';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - N&E Innovations</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="/file.svg">
    <style>
        body {
            background: linear-gradient(135deg, var(--green-50) 0%, #ffffff 50%, var(--green-50) 100%);
        }

        .login-container {
            width: 100%;
            max-width: 480px;
        }

        .login-card {
            background: white;
            padding: var(--space-3xl);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-2xl);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-green) 0%, var(--accent-green) 100%);
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--space-2xl);
        }

        .login-logo {
            width: 96px;
            height: 96px;
            margin: 0 auto var(--space-lg);
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
            box-shadow: var(--shadow-lg);
        }

        .login-title {
            font-size: 1.75rem;
            margin-bottom: var(--space-sm);
            color: var(--gray-900);
        }

        .login-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: var(--space-xl);
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: var(--space-sm);
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input {
            width: 100%;
            padding: var(--space-lg);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            transition: all var(--transition-fast);
            font-family: var(--font-sans);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.1);
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-xl);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            border-left: 4px solid #dc2626;
            animation: shake 0.3s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .btn-full {
            width: 100%;
            padding: var(--space-lg);
            font-size: 1.125rem;
        }

        .password-requirements {
            background: var(--gray-50);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-xl);
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .password-requirements strong {
            color: var(--gray-800);
        }

        .password-requirements ul {
            margin: var(--space-sm) 0 0 var(--space-lg);
            padding: 0;
        }

        .password-requirements li {
            margin-bottom: var(--space-xs);
        }

        .bg-decoration {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            opacity: 0.05;
            z-index: 0;
            pointer-events: none;
        }

        .bg-decoration-1 {
            background: radial-gradient(circle, var(--primary-green) 0%, transparent 70%);
            top: -200px;
            right: -200px;
        }

        .bg-decoration-2 {
            background: radial-gradient(circle, var(--accent-green) 0%, transparent 70%);
            bottom: -200px;
            left: -200px;
        }
    </style>
</head>
<body>
    <div class="auth-page-content">
    <div class="bg-decoration bg-decoration-1"></div>
    <div class="bg-decoration bg-decoration-2"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">N&E</div>
                <h1 class="login-title">Set Your Password</h1>
                <p class="login-subtitle">You must set a new password before continuing</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="password-requirements">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>Minimum 8 characters</li>
                    <li>At least 1 number</li>
                </ul>
            </div>

            <form method="POST" action="/change-password.php">
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        class="form-input"
                        placeholder="Enter your new password"
                        required
                        autofocus
                        autocomplete="new-password"
                        minlength="8"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-input"
                        placeholder="Confirm your new password"
                        required
                        autocomplete="new-password"
                        minlength="8"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Set Password & Continue
                </button>
            </form>
        </div>
    </div>
    </div>

    <?php include __DIR__ . '/includes/legal-footer.php'; ?>
</body>
</html>
