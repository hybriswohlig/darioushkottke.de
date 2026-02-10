<?php
require_once __DIR__ . '/includes/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already authenticated, redirect to home
if (isset($_SESSION['visitor_authenticated']) && $_SESSION['visitor_authenticated'] === true) {
    $redirect = $_SESSION['redirect_after_login'] ?? '/';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;
}

$error = '';
$showTimeout = isset($_GET['timeout']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    // Load visitor auth functions
    require_once __DIR__ . '/includes/visitor-auth.php';

    if (verifyVisitorPassword($password)) {
        authenticateVisitor();

        // Redirect to intended page or home
        $redirect = $_SESSION['redirect_after_login'] ?? '/';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'Invalid password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Portal - N&E Innovations</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--green-50) 0%, #ffffff 50%, var(--green-50) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-xl);
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
            font-size: 2rem;
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

        .timeout-message {
            background: #fef3c7;
            color: #92400e;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-xl);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            border-left: 4px solid #f59e0b;
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

        .login-footer {
            text-align: center;
            margin-top: var(--space-2xl);
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
            margin-top: var(--space-xl);
            padding: var(--space-md);
            background: var(--gray-50);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .lock-icon {
            color: var(--primary-green);
        }

        /* Decorative elements */
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
    <!-- Background decorations -->
    <div class="bg-decoration bg-decoration-1"></div>
    <div class="bg-decoration bg-decoration-2"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">N&E</div>
                <h1 class="login-title">Welcome</h1>
                <p class="login-subtitle">Environmental Documentation Portal</p>
            </div>

            <?php if ($showTimeout): ?>
                <div class="timeout-message">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Your session has expired. Please log in again.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/visitor-login.php">
                <div class="form-group">
                    <label for="password" class="form-label">Access Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Enter portal access password"
                        required
                        autofocus
                        autocomplete="off"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                    Access Portal
                </button>
            </form>

            <div class="security-badge">
                <svg class="lock-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Secure Access • Session Timeout: 4 Hours
            </div>
        </div>

        <div class="login-footer">
            <p>N&E Innovations Pte Ltd • Environmental Impact Documentation</p>
            <p style="margin-top: var(--space-sm); color: var(--gray-500);">
                For access credentials, contact
                <a href="mailto:business@vi-kang.com" style="color: var(--primary-green); font-weight: 600;">business@vi-kang.com</a>
            </p>
        </div>
    </div>
</body>
</html>
