<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .action-buttons {
            display: flex;
            gap: var(--space-xs);
            flex-wrap: wrap;
        }

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

        .expiry-warning {
            color: #f59e0b;
            font-weight: 600;
        }

        .expiry-expired {
            color: #dc2626;
            font-weight: 600;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: var(--space-2xl);
            border-radius: var(--radius-xl);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .modal-title {
            font-size: 1.5rem;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-500);
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

        .form-input,
        .form-select {
            width: 100%;
            padding: var(--space-md);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 1rem;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-green);
        }

        /* Password display modal */
        .password-display {
            background: var(--gray-50);
            padding: var(--space-lg);
            border-radius: var(--radius-md);
            margin: var(--space-lg) 0;
            text-align: center;
        }

        .password-display .temp-password {
            font-size: 1.5rem;
            font-weight: 700;
            font-family: monospace;
            color: var(--primary-green);
            letter-spacing: 0.1em;
            user-select: all;
            margin: var(--space-md) 0;
        }

        .password-display .copy-btn {
            cursor: pointer;
            color: var(--primary-green);
            font-weight: 600;
            background: none;
            border: 1px solid var(--primary-green);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
        }

        .password-display .copy-btn:hover {
            background: var(--green-50);
        }

        .must-change-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
            background: #fef3c7;
            color: #92400e;
            margin-left: 0.5rem;
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
                <h1 class="admin-title">Manage Users</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New User
                </button>
            </div>

            <!-- Users Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Expiry</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                                    No users yet. Click "Add New User" to create the first account.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc($user['full_name']); ?></strong>
                                        <?php if ($user['must_change_password']): ?>
                                            <span class="must-change-badge">Temp Password</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc($user['email']); ?></td>
                                    <td><?php echo esc($user['company'] ?? '-'); ?></td>
                                    <td>
                                        <span class="status-<?php echo $user['status']; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['expiry_date']): ?>
                                            <?php
                                            $expiryTs = strtotime($user['expiry_date']);
                                            $daysLeft = (int)ceil(($expiryTs - strtotime('today')) / 86400);
                                            $cssClass = '';
                                            if ($daysLeft <= 0) $cssClass = 'expiry-expired';
                                            elseif ($daysLeft <= 7) $cssClass = 'expiry-warning';
                                            ?>
                                            <span class="<?php echo $cssClass; ?>">
                                                <?php echo formatDate($user['expiry_date']); ?>
                                                <?php if ($daysLeft <= 0): ?> (Expired)
                                                <?php elseif ($daysLeft <= 7): ?> (<?php echo $daysLeft; ?>d left)
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--gray-400);">No expiry</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y H:i') : '<span style="color: var(--gray-400);">Never</span>'; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-secondary btn-sm" onclick='editUser(<?php echo json_encode($user); ?>)'>Edit</button>
                                            <button class="btn btn-warning btn-sm" onclick="resetPassword(<?php echo $user['id']; ?>, '<?php echo esc($user['full_name']); ?>')">Reset PW</button>
                                            <button class="btn btn-sm <?php echo $user['status'] === 'active' ? 'btn-danger' : 'btn-primary'; ?>" onclick="toggleStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>', '<?php echo esc($user['full_name']); ?>')">
                                                <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                            <a href="/admin/user-activity.php?user_id=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm">Activity</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New User</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>

            <form id="userForm">
                <input type="hidden" id="userId" name="id">

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="company" class="form-label">Company</label>
                    <input type="text" id="company" name="company" class="form-input">
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="expiry_date" class="form-label">Expiry Date (optional)</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-input">
                    <small style="color: var(--gray-500); display: block; margin-top: var(--space-xs);">Leave empty for no expiration</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Display Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="passwordModalTitle">Temporary Password</h2>
                <button class="modal-close" onclick="closePasswordModal()">&times;</button>
            </div>

            <p id="passwordModalMessage" style="color: var(--gray-600); margin-bottom: var(--space-md);"></p>

            <div class="password-display">
                <p style="font-size: 0.875rem; color: var(--gray-600);">Temporary Password:</p>
                <div class="temp-password" id="tempPasswordDisplay"></div>
                <button class="copy-btn" onclick="copyPassword()">Copy to Clipboard</button>
            </div>

            <div style="background: #fef3c7; padding: var(--space-md); border-radius: var(--radius-md); margin-top: var(--space-md);">
                <p style="font-size: 0.875rem; color: #92400e; margin: 0;">
                    <strong>Important:</strong> Share this password securely with the user. They will be required to change it on their first login.
                </p>
            </div>

            <button class="btn btn-secondary" style="width: 100%; margin-top: var(--space-lg);" onclick="closePasswordModal()">Close</button>
        </div>
    </div>

    <script>
        let editMode = false;

        function openAddModal() {
            editMode = false;
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('email').removeAttribute('readonly');
            document.getElementById('userModal').classList.add('active');
        }

        function editUser(user) {
            editMode = true;
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = user.id;
            document.getElementById('full_name').value = user.full_name;
            document.getElementById('email').value = user.email;
            document.getElementById('company').value = user.company || '';
            document.getElementById('status').value = user.status;
            document.getElementById('expiry_date').value = user.expiry_date || '';
            document.getElementById('userModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.remove('active');
        }

        function showPasswordModal(title, message, password) {
            document.getElementById('passwordModalTitle').textContent = title;
            document.getElementById('passwordModalMessage').textContent = message;
            document.getElementById('tempPasswordDisplay').textContent = password;
            document.getElementById('passwordModal').classList.add('active');
        }

        function copyPassword() {
            const password = document.getElementById('tempPasswordDisplay').textContent;
            navigator.clipboard.writeText(password).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = 'Copy to Clipboard'; }, 2000);
            });
        }

        async function resetPassword(userId, userName) {
            if (!confirm(`Reset password for "${userName}"? A new temporary password will be generated.`)) {
                return;
            }

            try {
                const response = await fetch('/api/users.php?action=reset_password', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId })
                });

                const data = await response.json();

                if (data.success) {
                    showPasswordModal(
                        'Password Reset',
                        `New temporary password for ${userName}:`,
                        data.temp_password
                    );
                } else {
                    alert('Error: ' + (data.error || 'Failed to reset password'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to reset password');
            }
        }

        async function toggleStatus(userId, currentStatus, userName) {
            const action = currentStatus === 'active' ? 'deactivate' : 'activate';
            if (!confirm(`Are you sure you want to ${action} "${userName}"?`)) {
                return;
            }

            try {
                const response = await fetch('/api/users.php?action=toggle_status', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId })
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to toggle status'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to toggle status');
            }
        }

        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                full_name: formData.get('full_name'),
                email: formData.get('email'),
                company: formData.get('company') || null,
                status: formData.get('status'),
                expiry_date: formData.get('expiry_date') || ''
            };

            if (editMode) {
                data.id = formData.get('id');
            }

            try {
                const response = await fetch('/api/users.php', {
                    method: editMode ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    closeModal();

                    if (!editMode && result.temp_password) {
                        showPasswordModal(
                            'User Created',
                            `Account created for ${data.full_name} (${data.email}):`,
                            result.temp_password
                        );
                        // Reload when password modal is closed
                        document.getElementById('passwordModal').addEventListener('click', function handler(e) {
                            if (e.target === this || e.target.classList.contains('modal-close') || e.target.closest('.btn-secondary')) {
                                location.reload();
                                this.removeEventListener('click', handler);
                            }
                        });
                    } else {
                        location.reload();
                    }
                } else {
                    alert('Error: ' + (result.error || 'Failed to save user'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to save user');
            }
        });

        // Close modals when clicking outside
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if (e.target === this) closePasswordModal();
        });
    </script>
</body>
</html>
