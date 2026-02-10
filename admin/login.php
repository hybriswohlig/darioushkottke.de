<?php
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: /admin/');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $user = verifyAdminLogin($username, $password);

        if ($user) {
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['last_activity'] = time();

            // Log activity
            logActivity('login', null, null, 'Admin logged in');

            // Redirect to dashboard
            header('Location: /admin/');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - N&E Innovations</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--green-50) 0%, #ffffff 100%);
            padding: var(--space-xl);
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: white;
            padding: var(--space-3xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--space-2xl);
        }

        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto var(--space-lg);
        }

        .login-title {
            font-size: 1.875rem;
            margin-bottom: var(--space-sm);
        }

        .login-subtitle {
            color: var(--gray-600);
        }

        .form-group {
            margin-bottom: var(--space-lg);
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: var(--space-xs);
            color: var(--gray-700);
        }

        .form-input {
            width: 100%;
            padding: var(--space-md);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all var(--transition-fast);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            font-size: 0.875rem;
        }

        .btn-full {
            width: 100%;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: var(--space-lg);
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .back-link:hover {
            color: var(--primary-green);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="/file.svg" alt="N&E Innovations" class="login-logo" onerror="this.style.display='none'">
                <h1 class="login-title">Admin Login</h1>
                <p class="login-subtitle">N&E Innovations Portal</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo esc($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="/admin/login.php">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Sign In
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <a href="/" class="back-link">← Back to Portal</a>
        </div>
    </div>
</body>
</html>
