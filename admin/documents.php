<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$categories = getCategories();

// Get all documents
$stmt = $db->query("
    SELECT d.*, c.name as category_name
    FROM documents d
    JOIN categories c ON d.category_id = c.id
    ORDER BY d.created_at DESC
");
$documents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Documents - Admin</title>
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

        .action-buttons {
            display: flex;
            gap: var(--space-xs);
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
        .form-select,
        .form-textarea {
            width: 100%;
            padding: var(--space-md);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 1rem;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary-green);
        }

        .form-checkbox {
            margin-right: var(--space-xs);
        }

        .metadata-group {
            border: 1px solid var(--gray-200);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-md);
        }

        .metadata-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: var(--space-sm);
            margin-bottom: var(--space-sm);
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .tag-vikang { background: #16a34a; color: #fff; }
        .tag-compostable { background: #065f46; color: #d1fae5; }
        .tag-biodegradable { background: #0d9488; color: #fff; }
        .tag-untagged { background: #e5e7eb; color: #4b5563; }
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
                        <a href="/admin/documents.php" class="admin-nav-link active">
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
                <h1 class="admin-title">Manage Documents</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Document
                </button>
            </div>

            <!-- Documents Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Tag</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc($doc['title']); ?></strong>
                                    <?php if ($doc['featured']): ?>
                                        <span class="status-badge" style="background: #fbbf24; color: #78350f; margin-left: 0.5rem;">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc($doc['category_name']); ?></td>
                                <td><?php echo getStatusBadge($doc['status']); ?></td>
                                <td><?php echo getTagBadge($doc['tag']); ?></td>
                                <td><?php echo $doc['view_count']; ?></td>
                                <td><?php echo formatDate($doc['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-secondary btn-sm" onclick='editDocument(<?php echo json_encode($doc); ?>)'>
                                            Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteDocument(<?php echo $doc['id']; ?>, '<?php echo esc($doc['title']); ?>')">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="documentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Document</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>

            <form id="documentForm">
                <input type="hidden" id="documentId" name="id">

                <div class="form-group">
                    <label for="category_id" class="form-label">Category *</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo esc($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" id="title" name="title" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description *</label>
                    <textarea id="description" name="description" class="form-textarea" required></textarea>
                </div>

                <div class="form-group">
                    <label for="file_url" class="form-label">File URL (Google Drive, etc.)</label>
                    <input type="url" id="file_url" name="file_url" class="form-input" placeholder="https://">
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="published">Published</option>
                        <option value="under_review">Under Review</option>
                        <option value="in_progress">In Progress</option>
                        <option value="planned">Planned</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tag" class="form-label">Product Tag</label>
                    <select id="tag" name="tag" class="form-select">
                        <option value="">Untagged</option>
                        <option value="vikang">VIKANG</option>
                        <option value="compostable">Compostable</option>
                        <option value="biodegradable">Biodegradable</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="version" class="form-label">Version</label>
                    <input type="text" id="version" name="version" class="form-input" placeholder="1.0">
                </div>

                <div class="form-group">
                    <label for="date_published" class="form-label">Date Published</label>
                    <input type="date" id="date_published" name="date_published" class="form-input">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="featured" name="featured" class="form-checkbox">
                        <span class="form-label" style="display: inline;">Featured Document</span>
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-full">
                        Save Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let editMode = false;

        function openAddModal() {
            editMode = false;
            document.getElementById('modalTitle').textContent = 'Add New Document';
            document.getElementById('documentForm').reset();
            document.getElementById('documentId').value = '';
            document.getElementById('documentModal').classList.add('active');
        }

        function editDocument(doc) {
            editMode = true;
            document.getElementById('modalTitle').textContent = 'Edit Document';
            document.getElementById('documentId').value = doc.id;
            document.getElementById('category_id').value = doc.category_id;
            document.getElementById('title').value = doc.title;
            document.getElementById('description').value = doc.description;
            document.getElementById('file_url').value = doc.file_url || '';
            document.getElementById('status').value = doc.status;
            document.getElementById('version').value = doc.version || '';
            document.getElementById('date_published').value = doc.date_published || '';
            document.getElementById('featured').checked = doc.featured == 1;
            document.getElementById('tag').value = doc.tag || '';
            document.getElementById('documentModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('documentModal').classList.remove('active');
        }

        async function deleteDocument(id, title) {
            if (!confirm(`Are you sure you want to delete "${title}"?`)) {
                return;
            }

            try {
                const response = await fetch('/api/documents.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Document deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete document'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to delete document');
            }
        }

        document.getElementById('documentForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                category_id: formData.get('category_id'),
                title: formData.get('title'),
                description: formData.get('description'),
                file_url: formData.get('file_url') || null,
                status: formData.get('status'),
                tag: formData.get('tag') || null,
                version: formData.get('version') || null,
                date_published: formData.get('date_published') || null,
                featured: formData.get('featured') ? 1 : 0
            };

            if (editMode) {
                data.id = formData.get('id');
            }

            try {
                const response = await fetch('/api/documents.php', {
                    method: editMode ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(editMode ? 'Document updated successfully' : 'Document created successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (result.error || 'Failed to save document'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to save document');
            }
        });

        // Close modal when clicking outside
        document.getElementById('documentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
