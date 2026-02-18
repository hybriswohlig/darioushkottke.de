<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// If already authenticated, redirect
if (isset($_SESSION['user_id'])) {
    $redirect = $_SESSION['redirect_after_login'] ?? '/';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;
}

$error = '';
$showTimeout = isset($_GET['timeout']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, full_name, email, password_hash, must_change_password, status, expiry_date FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid email or password.';
        } elseif ($user['status'] === 'inactive') {
            $error = 'Your account has been deactivated. Please contact the administrator.';
        } elseif ($user['expiry_date'] !== null && strtotime($user['expiry_date']) < strtotime('today')) {
            $error = 'Your access has expired. Please contact the administrator.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = 'Invalid email or password.';
        } else {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_must_change_password'] = (bool)$user['must_change_password'];
            $_SESSION['user_last_activity'] = time();

            // Update last_login
            $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            // Log login activity
            $logStmt = $db->prepare("INSERT INTO user_activity_log (user_id, action, ip_address) VALUES (?, 'login', ?)");
            $logStmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? null]);

            // Redirect
            if ($user['must_change_password']) {
                header('Location: /change-password.php');
            } else {
                $redirect = $_SESSION['redirect_after_login'] ?? '/';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - N&E Innovations</title>
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
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-logo-fallback {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
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

        /* Top-left Region Jurisdiction Indicator */
        .region-badge-container {
            position: absolute;
            top: 48px;
            left: 48px;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 10px 16px;
            background: rgba(0, 0, 0, 0.15); /* Slightly darker for contrast */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50px; /* Full pill shape */
            z-index: 10;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .region-flags-group {
            display: flex;
            align-items: center;
            gap: -8px; /* Negative margin for the 'stacked' look, or 8px for separate */
        }

        .region-flag {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #003399; /* Fallback for EU */
            position: relative;
        }

        /* Specific fix for UK flag to ensure circle crop looks good */
        .region-flag.uk-flag {
            background: #00247d;
        }

        .region-divider {
            width: 1px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
        }

        .region-text-group {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .region-label-small {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 600;
            margin-bottom: 2px;
            line-height: 1;
        }

        .region-label-main {
            font-size: 0.85rem;
            font-weight: 500;
            color: #ffffff;
            letter-spacing: 0.02em;
            line-height: 1;
        }
    </style>
</head>
<body>
    <div class="split-auth-page">

        <!-- LEFT: Branding / Story Panel -->
        <div class="branding-panel">
            <div class="region-badge-container" aria-hidden="true">
                <div class="region-flags-group">
                    <div class="region-flag">
                        <svg viewBox="0 0 512 512" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                            <rect width="512" height="512" fill="#003399"/>
                            <g fill="#FFCC00" transform="translate(256, 256)">
                                <g id="star">
                                    <path d="M0,-170 L10.53,-137.6 L45.03,-137.6 L17.11,-117.3 L27.78,-84.4 L0,-104.6 L-27.78,-84.4 L-17.11,-117.3 L-45.03,-137.6 L-10.53,-137.6 Z"/>
                                </g>
                                <use href="#star" transform="rotate(30)"/>
                                <use href="#star" transform="rotate(60)"/>
                                <use href="#star" transform="rotate(90)"/>
                                <use href="#star" transform="rotate(120)"/>
                                <use href="#star" transform="rotate(150)"/>
                                <use href="#star" transform="rotate(180)"/>
                                <use href="#star" transform="rotate(210)"/>
                                <use href="#star" transform="rotate(240)"/>
                                <use href="#star" transform="rotate(270)"/>
                                <use href="#star" transform="rotate(300)"/>
                                <use href="#star" transform="rotate(330)"/>
                            </g>
                        </svg>
                    </div>

                    <div class="region-flag uk-flag">
                        <svg viewBox="0 0 60 30" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                            <clipPath id="t">
                                <path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/>
                            </clipPath>
                            <path d="M0,0 v30 h60 v-30 z" fill="#00247d"/>
                            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
                            <path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#t)" stroke="#cf142b" stroke-width="4"/>
                            <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
                            <path d="M30,0 v30 M0,15 h60" stroke="#cf142b" stroke-width="6"/>
                        </svg>
                    </div>
                </div>

                <div class="region-divider"></div>

                <div class="region-text-group">
                    <span class="region-label-small">Operating Region</span>
                    <span class="region-label-main">Europe & United Kingdom</span>
                </div>
            </div>
            <div class="branding-overlay"></div>
            
            <div class="branding-content">
                <div class="branding-logo-large">
                    <img src="/file.svg" alt="N&E Innovations" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="login-logo-fallback" style="display:none;">N&E</span>
                </div>
                
                <h1 class="branding-title">European<br>Compliance Portal</h1>
                <p class="branding-subtitle">Secure access to regulatory, certification and sustainability documents</p>
                
                <div class="trust-badges">
                    <div class="trust-item">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 8.944 11.922.42.095.858.143 1.295.143a3 3 0 01.296-.006"></path></svg>
                        ISO 14001 Aligned
                    </div>
                    <div class="trust-item">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                        Enterprise-Grade Security
                    </div>
                </div>
            </div>

            <!-- Subtle eco leaf illustration -->
            <div class="eco-illustration">
                <svg width="320" height="320" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M70 60 Q90 30 120 55 Q150 40 170 75 Q150 120 110 130 Q70 110 70 60" fill="#4ade80" opacity="0.25"/>
                    <circle cx="105" cy="85" r="18" fill="#16a34a" opacity="0.2"/>
                </svg>
            </div>
        </div>

        <!-- RIGHT: Login Form -->
        <div class="login-panel">
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-logo">
                            <img src="/file.svg" alt="N&E Innovations" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="login-logo-fallback" style="display:none;">N&E</span>
                        </div>
                        <div class="eco-badge">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-9h6m-6 6h6"></path>
                            </svg>
                            N&E Compliance
                        </div>
                        <h1 class="login-title">Welcome back</h1>
                        <p class="login-subtitle">Sign in to your secure dashboard</p>
                    </div>

                    <?php if ($showTimeout): ?>
                        <div class="timeout-message">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Your session has expired. Please sign in again.
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

                    <form method="POST" action="/login.php">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2.01 2.01 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2"></path>
                                    </svg>
                                </div>
                                <input type="email" id="email" name="email" class="form-input" placeholder="you@company.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus autocomplete="email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5 16.477 5 20.268 7.943 21.542 12 20.268 16.057 16.477 19 12 19 7.523 19 3.732 16.057 2.458 12z"></path>
                                    </svg>
                                </div>
                                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
                                <button type="button" id="toggle-password" class="password-toggle">
                                    <svg id="eye" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5 16.477 5 20.268 7.943 21.542 12 20.268 16.057 16.477 19 12 19 7.523 19 3.732 16.057 2.458 12z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-row">
                            <label class="remember-me">
                                <input type="checkbox" name="remember"> Remember me
                            </label>
                            <a href="#" id="forgot-password-link" class="forgot-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">
                            Sign In
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </form>

                    <div class="security-badge">
                        <svg class="lock-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Individual Secure Access • AES-256 Encrypted
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/legal-footer.php'; ?>

    <script>
    // Password visibility toggle
    document.getElementById('toggle-password').addEventListener('click', function () {
        const pw = document.getElementById('password');
        const eye = document.getElementById('eye');
        if (pw.type === 'password') {
            pw.type = 'text';
            eye.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908l3.42 3.42m-3.42-3.42l-3.42 3.42"></path>`;
        } else {
            pw.type = 'password';
            eye.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5 16.477 5 20.268 7.943 21.542 12 20.268 16.057 16.477 19 12 19 7.523 19 3.732 16.057 2.458 12z"></path>`;
        }
    });

    // Forgot password: open email to request reset
    document.getElementById('forgot-password-link').addEventListener('click', function (e) {
        e.preventDefault();
        var subject = encodeURIComponent('Password forgotten - reset request');
        var body = 'I have forgotten my password and request a password reset for my account.';
        var email = document.getElementById('email').value.trim();
        if (email) {
            body += '\n\nMy account email: ' + email;
        }
        body = encodeURIComponent(body);
        window.location.href = 'mailto:business@vi-kang.com?subject=' + subject + '&body=' + body;
    });
    </script>
</body>
</html>
