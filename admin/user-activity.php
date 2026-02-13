<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$userId = (int)($_GET['user_id'] ?? 0);
if (!$userId) {
    header('Location: /admin/users.php');
    exit;
}

$db = getDB();

// Get user info
$user = getUserById($userId);
if (!$user) {
    header('Location: /admin/users.php');
    exit;
}

// Get activity log
$stmt = $db->prepare("
    SELECT * FROM user_activity_log
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 200
");
$stmt->execute([$userId]);
$activities = $stmt->fetchAll();

// Human-readable action labels
$actionLabels = [
    'login' => 'Logged In',
    'logout' => 'Logged Out',
    'page_view' => 'Viewed Page',
    'document_view' => 'Viewed Document',
    'search' => 'Searched',
    'filter' => 'Used Filter',
    'password_change' => 'Changed Password',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity - <?php echo esc($user['full_name']); ?> - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .admin-sidebar {
            background: var(--gray-900);
            color: white;
            padding: var(--space-xl);
        }

        .admin-brand {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: var(--space-2xl);
            padding-bottom: var(--space-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-nav { list-style: none; }
        .admin-nav-item { margin-bottom: var(--space-sm); }
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            color: rgba(255, 255, 255, 0.7);
            transition: all var(--transition-fast);
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .admin-main {
            background: var(--gray-50);
            padding: var(--space-2xl);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-2xl);
        }

        .admin-title {
            font-size: 2rem;
            margin: 0;
        }

        .user-info-card {
            background: white;
            padding: var(--space-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-2xl);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-lg);
        }

        .user-info-item {
            display: flex;
            flex-direction: column;
        }

        .user-info-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-500);
            margin-bottom: var(--space-xs);
        }

        .user-info-value {
            font-size: 1rem;
            color: var(--gray-900);
            font-weight: 500;
        }

        .data-table {
            width: 100%;
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: var(--gray-50);
            padding: var(--space-md);
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table td {
            padding: var(--space-md);
            border-bottom: 1px solid var(--gray-100);
        }

        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover { background: var(--gray-50); }

        .action-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .action-login { background: #dcfce7; color: #166534; }
        .action-logout { background: #fee2e2; color: #991b1b; }
        .action-page_view { background: #dbeafe; color: #1e40af; }
        .action-document_view { background: #fef3c7; color: #92400e; }
        .action-search { background: #e0e7ff; color: #3730a3; }
        .action-filter { background: #f3e8ff; color: #6b21a8; }
        .action-password_change { background: #fce7f3; color: #9d174d; }

        .status-active {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-brand">N&E Admin</div>
            <nav>
                <ul class="admin-nav">
                    <li class="admin-nav-item">
                        <a href="/admin/" class="admin-nav-link">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="/admin/documents.php" class="admin-nav-link">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Documents
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="/admin/users.php" class="admin-nav-link active">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Users
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="/" class="admin-nav-link" target="_blank">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            View Portal
                        </a>
                    </li>
                    <li class="admin-nav-item" style="margin-top: var(--space-xl);">
                        <a href="/admin/logout.php" class="admin-nav-link">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">User Activity</h1>
                <a href="/admin/users.php" class="btn btn-secondary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Users
                </a>
            </div>

            <!-- User Info Card -->
            <div class="user-info-card">
                <div class="user-info-item">
                    <span class="user-info-label">Name</span>
                    <span class="user-info-value"><?php echo esc($user['full_name']); ?></span>
                </div>
                <div class="user-info-item">
                    <span class="user-info-label">Email</span>
                    <span class="user-info-value"><?php echo esc($user['email']); ?></span>
                </div>
                <div class="user-info-item">
                    <span class="user-info-label">Company</span>
                    <span class="user-info-value"><?php echo esc($user['company'] ?? '-'); ?></span>
                </div>
                <div class="user-info-item">
                    <span class="user-info-label">Status</span>
                    <span class="user-info-value">
                        <span class="status-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
                    </span>
                </div>
                <div class="user-info-item">
                    <span class="user-info-label">Last Login</span>
                    <span class="user-info-value"><?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y H:i') : 'Never'; ?></span>
                </div>
                <div class="user-info-item">
                    <span class="user-info-label">Created</span>
                    <span class="user-info-value"><?php echo formatDate($user['created_at'], 'M d, Y'); ?></span>
                </div>
            </div>

            <!-- Activity Log -->
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-lg);">
                Activity Log
                <span style="font-weight: 400; font-size: 0.875rem; color: var(--gray-500);">(last 200 entries)</span>
            </h2>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Page / Document</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activities)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                                    No activity recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td>
                                        <span class="action-badge action-<?php echo esc($activity['action']); ?>">
                                            <?php echo esc($actionLabels[$activity['action']] ?? $activity['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc($activity['page'] ?? '-'); ?></td>
                                    <td><?php echo esc($activity['details'] ?? '-'); ?></td>
                                    <td style="font-family: monospace; font-size: 0.875rem;"><?php echo esc($activity['ip_address'] ?? '-'); ?></td>
                                    <td><?php echo formatDate($activity['created_at'], 'M d, Y H:i:s'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
