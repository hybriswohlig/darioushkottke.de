<?php
require_once __DIR__ . '/includes/config.php';

$reason = $_GET['reason'] ?? 'expired';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Unavailable - N&E Innovations</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="/file.svg">
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
            text-align: center;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #f59e0b 0%, #f97316 100%);
        }

        .expired-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto var(--space-xl);
            background: #fef3c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f59e0b;
        }

        .expired-title {
            font-size: 1.5rem;
            margin-bottom: var(--space-md);
            color: var(--gray-900);
        }

        .expired-description {
            color: var(--gray-600);
            margin-bottom: var(--space-2xl);
            line-height: 1.6;
        }

        .contact-box {
            background: var(--gray-50);
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-xl);
        }

        .contact-box a {
            color: var(--primary-green);
            font-weight: 600;
        }

        .login-footer {
            text-align: center;
            margin-top: var(--space-2xl);
            color: var(--gray-600);
            font-size: 0.875rem;
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
    <div class="bg-decoration bg-decoration-1"></div>
    <div class="bg-decoration bg-decoration-2"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="expired-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php if ($reason === 'inactive'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    <?php endif; ?>
                </svg>
            </div>

            <?php if ($reason === 'inactive'): ?>
                <h1 class="expired-title">Account Deactivated</h1>
                <p class="expired-description">
                    Your account has been deactivated and you can no longer access the portal.
                    Please contact the administrator to reactivate your account.
                </p>
            <?php else: ?>
                <h1 class="expired-title">Access Expired</h1>
                <p class="expired-description">
                    Your access to the Environmental Documentation Portal has expired.
                    Please contact the administrator to renew your access.
                </p>
            <?php endif; ?>

            <div class="contact-box">
                <p style="margin-bottom: var(--space-sm); font-weight: 600; color: var(--gray-800);">Contact Administrator</p>
                <a href="mailto:business@vi-kang.com">business@vi-kang.com</a>
            </div>

            <a href="/login.php" class="btn btn-secondary" style="width: 100%;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Login
            </a>
        </div>

        <div class="login-footer">
            <p>N&E Innovations Pte Ltd &bull; Environmental Impact Documentation</p>
        </div>
    </div>
</body>
</html>
