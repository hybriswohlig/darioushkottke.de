<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

// Get statistics
$stats = [];

// Total documents
$stmt = $db->query("SELECT COUNT(*) as count FROM documents");
$stats['total_documents'] = $stmt->fetch()['count'];

// Published documents
$stmt = $db->query("SELECT COUNT(*) as count FROM documents WHERE status = 'published'");
$stats['published_documents'] = $stmt->fetch()['count'];

// Total views
$stmt = $db->query("SELECT SUM(view_count) as total FROM documents");
$stats['total_views'] = $stmt->fetch()['total'] ?? 0;

// Categories
$categories = getCategories();

// Active users
$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
$stats['active_users'] = $stmt->fetch()['count'];

// Recent user activity
$recentUserActivity = [];
try {
    $stmt = $db->query("
        SELECT ual.*, u.full_name, u.email
        FROM user_activity_log ual
        JOIN users u ON ual.user_id = u.id
        ORDER BY ual.created_at DESC
        LIMIT 10
    ");
    $recentUserActivity = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table might not exist yet during migration
}

// Recent documents
$stmt = $db->query("
    SELECT d.*, c.name as category_name
    FROM documents d
    JOIN categories c ON d.category_id = c.id
    ORDER BY d.created_at DESC
    LIMIT 10
");
$recentDocs = $stmt->fetchAll();

// Recent activity
$stmt = $db->query("
    SELECT a.*, u.username
    FROM activity_log a
    LEFT JOIN admin_users u ON a.admin_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 10
");
$recentActivity = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - N&E Innovations</title>
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

        .admin-nav {
            list-style: none;
        }

        .admin-nav-item {
            margin-bottom: var(--space-sm);
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            color: rgba(255, 255, 255, 0.7);
            transition: all var(--transition-fast);
        }

        .admin-nav-link:hover,
        .admin-nav-link.active {
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

        .admin-user {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-2xl);
        }

        .stat-card {
            background: white;
            padding: var(--space-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: var(--space-xs);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-green);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: var(--space-lg);
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

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background: var(--gray-50);
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
                        <a href="/admin/" class="admin-nav-link active">
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
                        <a href="/admin/users.php" class="admin-nav-link">
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
                <h1 class="admin-title">Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, <strong><?php echo esc($_SESSION['admin_username']); ?></strong></span>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Documents</div>
                    <div class="stat-value"><?php echo $stats['total_documents']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Published</div>
                    <div class="stat-value"><?php echo $stats['published_documents']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Views</div>
                    <div class="stat-value"><?php echo number_format($stats['total_views']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Categories</div>
                    <div class="stat-value"><?php echo count($categories); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Users</div>
                    <div class="stat-value"><?php echo $stats['active_users']; ?></div>
                </div>
            </div>

            <!-- Recent Documents -->
            <section style="margin-bottom: var(--space-2xl);">
                <h2 class="section-title">Recent Documents</h2>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDocs as $doc): ?>
                                <tr>
                                    <td><strong><?php echo esc($doc['title']); ?></strong></td>
                                    <td><?php echo esc($doc['category_name']); ?></td>
                                    <td><?php echo getStatusBadge($doc['status']); ?></td>
                                    <td><?php echo $doc['view_count']; ?></td>
                                    <td><?php echo formatDate($doc['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Recent User Activity -->
            <?php if (!empty($recentUserActivity)): ?>
            <section style="margin-bottom: var(--space-2xl);">
                <h2 class="section-title">Recent User Activity</h2>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Page</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUserActivity as $ua): ?>
                                <tr>
                                    <td><strong><?php echo esc($ua['full_name']); ?></strong></td>
                                    <td><?php echo esc($ua['action']); ?></td>
                                    <td><?php echo esc($ua['page'] ?? $ua['details'] ?? '-'); ?></td>
                                    <td><?php echo formatDate($ua['created_at'], 'M d, Y H:i'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Recent Admin Activity -->
            <section>
                <h2 class="section-title">Recent Admin Activity</h2>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>User</th>
                                <th>Details</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivity as $activity): ?>
                                <tr>
                                    <td><strong><?php echo esc($activity['action']); ?></strong></td>
                                    <td><?php echo esc($activity['username'] ?? 'System'); ?></td>
                                    <td><?php echo esc($activity['details'] ?? '-'); ?></td>
                                    <td><?php echo formatDate($activity['created_at'], 'M d, Y H:i'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
