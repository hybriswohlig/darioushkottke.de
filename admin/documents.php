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

        .metadata-section {
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            background: var(--gray-50);
        }
        .metadata-section h3 {
            margin: 0 0 var(--space-md) 0;
            font-size: 1rem;
            color: var(--gray-700);
        }
        .multi-select-container {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-xs);
        }
        .multi-select-container label {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.15s;
        }
        .multi-select-container label:has(input:checked) {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
        }
        .multi-select-container input[type="checkbox"] {
            display: none;
        }
        .freetext-multi-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-xs);
            padding: var(--space-sm);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            background: white;
            min-height: 42px;
            cursor: text;
        }
        .freetext-multi-wrapper:focus-within {
            border-color: var(--primary-green);
        }
        .freetext-tag {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--gray-100);
            border-radius: var(--radius-md);
            padding: 2px 8px;
            font-size: 0.875rem;
        }
        .freetext-tag button {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-500);
            font-size: 1rem;
            padding: 0;
            line-height: 1;
        }
        .freetext-multi-wrapper input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 120px;
            font-size: 0.875rem;
            padding: 4px;
        }
        .range-inputs {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        .range-inputs input {
            width: 100px;
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
                            <th>Type</th>
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
                                <td>
                                    <?php
                                    $typeLabels = ['link' => 'Link', 'pdf' => 'PDF', 'html' => 'HTML'];
                                    $type = $doc['document_type'] ?? 'link';
                                    echo '<span class="status-badge" style="background: ' .
                                        ($type === 'pdf' ? '#dc2626; color: #fff' : ($type === 'html' ? '#2563eb; color: #fff' : '#e5e7eb; color: #4b5563')) .
                                        ';">' . ($typeLabels[$type] ?? 'Link') . '</span>';
                                    ?>
                                </td>
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

            <form id="documentForm" novalidate>
                <input type="hidden" id="documentId" name="id">

                <div class="form-group">
                    <label for="category_id" class="form-label">Category *</label>
                    <select id="category_id" name="category_id" class="form-select" required onchange="loadMetadataFields()">
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
                    <label for="document_type" class="form-label">Document Type *</label>
                    <select id="document_type" name="document_type" class="form-select" onchange="toggleDocumentTypeFields()">
                        <option value="link">External Link (Google Drive, Dropbox, etc.)</option>
                        <option value="pdf">PDF Upload</option>
                        <option value="html">HTML Report (local .html file)</option>
                    </select>
                </div>

                <!-- External Link field -->
                <div class="form-group" id="field-link">
                    <label for="file_url" class="form-label">File URL</label>
                    <input type="url" id="file_url" name="file_url" class="form-input" placeholder="https://">
                </div>

                <!-- PDF Upload field -->
                <div class="form-group" id="field-pdf" style="display: none;">
                    <label for="pdf_file" class="form-label">Upload PDF (max 20MB)</label>
                    <input type="file" id="pdf_file" name="pdf_file" accept=".pdf,application/pdf" class="form-input">
                    <div id="pdf-upload-status" style="margin-top: var(--space-sm); font-size: 0.875rem; color: var(--gray-600);"></div>
                    <input type="hidden" id="file_path" name="file_path" value="">
                </div>

                <!-- HTML Report field -->
                <div class="form-group" id="field-html" style="display: none;">
                    <label for="html_file_url" class="form-label">HTML File Name</label>
                    <input type="text" id="html_file_url" name="html_file_url" class="form-input" placeholder="report-name.html">
                    <small style="color: var(--gray-500);">Enter the filename of the HTML report in the root directory</small>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="published">Published (visible to users)</option>
                        <option value="draft">Draft (hidden from users)</option>
                        <option value="under_review">Under Review (hidden)</option>
                        <option value="in_progress">In Progress (hidden)</option>
                        <option value="planned">Planned (hidden)</option>
                        <option value="pending">Pending (hidden)</option>
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

                <!-- Dynamic category-specific metadata fields -->
                <div id="metadata-section" class="metadata-section" style="display:none;">
                    <h3>Category Metadata</h3>
                    <div id="metadata-fields"></div>
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
        let currentSchema = [];
        let pendingMetaValues = null;

        function toggleDocumentTypeFields() {
            const type = document.getElementById('document_type').value;
            document.getElementById('field-link').style.display = (type === 'link') ? '' : 'none';
            document.getElementById('field-pdf').style.display = (type === 'pdf') ? '' : 'none';
            document.getElementById('field-html').style.display = (type === 'html') ? '' : 'none';
            if (type !== 'link') document.getElementById('file_url').value = '';
            if (type !== 'html') document.getElementById('html_file_url').value = '';
        }

        // ──────────────────────────────────────────────
        // Dynamic metadata fields
        // ──────────────────────────────────────────────

        async function loadMetadataFields() {
            const catId = document.getElementById('category_id').value;
            const section = document.getElementById('metadata-section');
            const container = document.getElementById('metadata-fields');
            container.innerHTML = '';
            currentSchema = [];

            if (!catId) {
                section.style.display = 'none';
                return;
            }

            try {
                const res = await fetch('/api/category-schema.php?category_id=' + catId);
                const data = await res.json();
                if (!data.success || !data.fields.length) {
                    section.style.display = 'none';
                    return;
                }
                currentSchema = data.fields;
                section.style.display = '';

                data.fields.forEach(f => {
                    const saved = pendingMetaValues ? pendingMetaValues[f.field_key] : null;
                    container.appendChild(buildField(f, saved));
                });
                pendingMetaValues = null;
            } catch (e) {
                console.error('Schema load error:', e);
                section.style.display = 'none';
            }
        }

        function buildField(field, savedValue) {
            const wrap = document.createElement('div');
            wrap.className = 'form-group';
            wrap.dataset.fieldKey = field.field_key;

            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = field.field_label + (field.is_required ? ' *' : '');
            wrap.appendChild(label);

            switch (field.field_type) {
                case 'text':
                    wrap.appendChild(makeTextInput(field, savedValue));
                    break;
                case 'dropdown_single':
                    wrap.appendChild(makeDropdownSingle(field, savedValue));
                    break;
                case 'dropdown_multi':
                    wrap.appendChild(makeDropdownMulti(field, savedValue));
                    break;
                case 'freetext_multi':
                    wrap.appendChild(makeFreetextMulti(field, savedValue));
                    break;
                case 'boolean':
                    wrap.appendChild(makeBoolean(field, savedValue));
                    break;
                case 'number_unit':
                    wrap.appendChild(makeNumberUnit(field, savedValue));
                    break;
                case 'range':
                    wrap.appendChild(makeRange(field, savedValue));
                    break;
                case 'date_from_doc':
                    const hint = document.createElement('small');
                    hint.style.color = 'var(--gray-500)';
                    hint.textContent = 'Automatically uses the document\'s "Date Published" field.';
                    wrap.appendChild(hint);
                    break;
            }
            return wrap;
        }

        function makeTextInput(field, saved) {
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'form-input';
            inp.name = 'meta_' + field.field_key;
            inp.value = saved || '';
            return inp;
        }

        function makeDropdownSingle(field, saved) {
            const sel = document.createElement('select');
            sel.className = 'form-select';
            sel.name = 'meta_' + field.field_key;
            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = '— Select —';
            sel.appendChild(blank);
            (field.field_options || []).forEach(opt => {
                const o = document.createElement('option');
                o.value = opt;
                o.textContent = opt;
                if (saved === opt) o.selected = true;
                sel.appendChild(o);
            });
            return sel;
        }

        function makeDropdownMulti(field, saved) {
            const parsed = tryParseArray(saved);
            const box = document.createElement('div');
            box.className = 'multi-select-container';
            (field.field_options || []).forEach(opt => {
                const lbl = document.createElement('label');
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.name = 'meta_' + field.field_key + '[]';
                cb.value = opt;
                if (parsed.includes(opt)) cb.checked = true;
                lbl.appendChild(cb);
                lbl.appendChild(document.createTextNode(opt));
                box.appendChild(lbl);
            });
            return box;
        }

        function makeFreetextMulti(field, saved) {
            const parsed = tryParseArray(saved);
            const wrapper = document.createElement('div');
            wrapper.className = 'freetext-multi-wrapper';
            wrapper.dataset.fieldKey = field.field_key;

            parsed.forEach(v => addFreetextTag(wrapper, v));

            const inp = document.createElement('input');
            inp.type = 'text';
            inp.placeholder = 'Type and press Enter…';
            inp.addEventListener('keydown', function(e) {
                if ((e.key === 'Enter' || e.key === ',') && this.value.trim()) {
                    e.preventDefault();
                    addFreetextTag(wrapper, this.value.trim());
                    this.value = '';
                }
            });
            wrapper.appendChild(inp);
            wrapper.addEventListener('click', () => inp.focus());
            return wrapper;
        }

        function addFreetextTag(wrapper, value) {
            const tag = document.createElement('span');
            tag.className = 'freetext-tag';
            tag.dataset.value = value;
            tag.innerHTML = escHtml(value) + ' <button type="button">&times;</button>';
            tag.querySelector('button').onclick = () => tag.remove();
            const inp = wrapper.querySelector('input');
            if (inp) wrapper.insertBefore(tag, inp);
            else wrapper.appendChild(tag);
        }

        function makeBoolean(field, saved) {
            const lbl = document.createElement('label');
            lbl.style.display = 'flex';
            lbl.style.alignItems = 'center';
            lbl.style.gap = '8px';
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.name = 'meta_' + field.field_key;
            cb.className = 'form-checkbox';
            cb.value = '1';
            if (saved === '1') cb.checked = true;
            lbl.appendChild(cb);
            lbl.appendChild(document.createTextNode('Yes'));
            return lbl;
        }

        function makeNumberUnit(field, saved) {
            const unit = (field.field_options && field.field_options.unit) || '';
            const box = document.createElement('div');
            box.style.display = 'flex';
            box.style.alignItems = 'center';
            box.style.gap = '8px';
            const inp = document.createElement('input');
            inp.type = 'number';
            inp.className = 'form-input';
            inp.name = 'meta_' + field.field_key;
            inp.value = saved || '';
            inp.step = 'any';
            inp.style.flex = '1';
            box.appendChild(inp);
            const span = document.createElement('span');
            span.textContent = unit;
            span.style.fontWeight = '600';
            box.appendChild(span);
            return box;
        }

        function makeRange(field, saved) {
            const unit = (field.field_options && field.field_options.unit) || '';
            const parts = saved ? saved.split('|') : ['', ''];
            const box = document.createElement('div');
            box.className = 'range-inputs';
            const inpMin = document.createElement('input');
            inpMin.type = 'number';
            inpMin.className = 'form-input';
            inpMin.name = 'meta_' + field.field_key + '_min';
            inpMin.placeholder = 'Min';
            inpMin.value = parts[0] || '';
            inpMin.step = 'any';
            const sep = document.createElement('span');
            sep.textContent = '–';
            const inpMax = document.createElement('input');
            inpMax.type = 'number';
            inpMax.className = 'form-input';
            inpMax.name = 'meta_' + field.field_key + '_max';
            inpMax.placeholder = 'Max';
            inpMax.value = parts[1] || '';
            inpMax.step = 'any';
            const unitSpan = document.createElement('span');
            unitSpan.textContent = unit;
            unitSpan.style.fontWeight = '600';
            box.append(inpMin, sep, inpMax, unitSpan);
            return box;
        }

        function tryParseArray(val) {
            if (!val) return [];
            try { const a = JSON.parse(val); return Array.isArray(a) ? a : []; }
            catch { return []; }
        }

        function escHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        // ──────────────────────────────────────────────
        // Collect metadata values from the form
        // ──────────────────────────────────────────────

        function collectMetadata() {
            const meta = [];
            currentSchema.forEach((field, idx) => {
                let value = null;

                switch (field.field_type) {
                    case 'date_from_doc':
                        value = '__date_from_doc__';
                        break;
                    case 'text':
                    case 'dropdown_single':
                    case 'number_unit': {
                        const el = document.querySelector(`[name="meta_${field.field_key}"]`);
                        value = el ? el.value.trim() : '';
                        break;
                    }
                    case 'boolean': {
                        const el = document.querySelector(`[name="meta_${field.field_key}"]`);
                        value = el && el.checked ? '1' : '0';
                        break;
                    }
                    case 'dropdown_multi': {
                        const checked = document.querySelectorAll(`[name="meta_${field.field_key}[]"]:checked`);
                        const arr = Array.from(checked).map(c => c.value);
                        value = arr.length ? JSON.stringify(arr) : '';
                        break;
                    }
                    case 'freetext_multi': {
                        const wrapper = document.querySelector(`.freetext-multi-wrapper[data-field-key="${field.field_key}"]`);
                        if (wrapper) {
                            const tags = Array.from(wrapper.querySelectorAll('.freetext-tag')).map(t => t.dataset.value);
                            value = tags.length ? JSON.stringify(tags) : '';
                        }
                        break;
                    }
                    case 'range': {
                        const minEl = document.querySelector(`[name="meta_${field.field_key}_min"]`);
                        const maxEl = document.querySelector(`[name="meta_${field.field_key}_max"]`);
                        const minV = minEl ? minEl.value.trim() : '';
                        const maxV = maxEl ? maxEl.value.trim() : '';
                        value = (minV || maxV) ? minV + '|' + maxV : '';
                        break;
                    }
                }

                if (value && value !== '' && value !== '0' && value !== '[]') {
                    meta.push({ key: field.field_key, value: value });
                }
            });
            return meta;
        }

        // ──────────────────────────────────────────────
        // Modal open / edit / close
        // ──────────────────────────────────────────────

        function openAddModal() {
            editMode = false;
            pendingMetaValues = null;
            document.getElementById('modalTitle').textContent = 'Add New Document';
            document.getElementById('documentForm').reset();
            document.getElementById('documentId').value = '';
            document.getElementById('file_path').value = '';
            document.getElementById('pdf-upload-status').textContent = '';
            document.getElementById('document_type').value = 'link';
            toggleDocumentTypeFields();
            loadMetadataFields();
            document.getElementById('documentModal').classList.add('active');
        }

        async function editDocument(doc) {
            editMode = true;
            document.getElementById('modalTitle').textContent = 'Edit Document';
            document.getElementById('documentId').value = doc.id;
            document.getElementById('category_id').value = doc.category_id;
            document.getElementById('title').value = doc.title;
            document.getElementById('description').value = doc.description;
            document.getElementById('status').value = doc.status;
            document.getElementById('version').value = doc.version || '';
            document.getElementById('date_published').value = doc.date_published || '';
            document.getElementById('featured').checked = doc.featured == 1;
            document.getElementById('tag').value = doc.tag || '';

            const docType = doc.document_type || 'link';
            document.getElementById('document_type').value = docType;
            document.getElementById('file_url').value = '';
            document.getElementById('html_file_url').value = '';
            document.getElementById('file_path').value = doc.file_path || '';
            document.getElementById('pdf-upload-status').textContent = '';

            if (docType === 'link') {
                document.getElementById('file_url').value = doc.file_url || '';
            } else if (docType === 'html') {
                let htmlVal = doc.file_url || '';
                if (htmlVal.startsWith('/')) htmlVal = htmlVal.substring(1);
                document.getElementById('html_file_url').value = htmlVal;
            } else if (docType === 'pdf' && doc.file_path) {
                document.getElementById('pdf-upload-status').textContent =
                    'Current file: ' + doc.file_path.split('/').pop();
            }

            toggleDocumentTypeFields();

            // Fetch existing metadata for this document to pre-fill
            try {
                const res = await fetch('/api/documents.php?id=' + doc.id);
                const result = await res.json();
                if (result.success && result.document && result.document.metadata) {
                    const map = {};
                    result.document.metadata.forEach(m => { map[m.meta_key] = m.meta_value; });
                    pendingMetaValues = map;
                }
            } catch (e) {
                console.error('Metadata fetch error:', e);
            }

            await loadMetadataFields();
            document.getElementById('documentModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('documentModal').classList.remove('active');
        }

        async function deleteDocument(id, title) {
            if (!confirm(`Are you sure you want to delete "${title}"?`)) return;

            try {
                const response = await fetch('/api/documents.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
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

        // ──────────────────────────────────────────────
        // Form submission
        // ──────────────────────────────────────────────

        document.getElementById('documentForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const docType = formData.get('document_type') || 'link';

            if (docType === 'pdf') {
                const pdfFile = document.getElementById('pdf_file').files[0];
                if (pdfFile) {
                    document.getElementById('pdf-upload-status').textContent = 'Uploading...';
                    const uploadData = new FormData();
                    uploadData.append('pdf_file', pdfFile);
                    try {
                        const uploadRes = await fetch('/api/upload-document.php', { method: 'POST', body: uploadData });
                        const uploadResult = await uploadRes.json();
                        if (!uploadResult.success) {
                            alert('Upload error: ' + (uploadResult.error || 'Failed to upload PDF'));
                            document.getElementById('pdf-upload-status').textContent = 'Upload failed.';
                            return;
                        }
                        document.getElementById('file_path').value = uploadResult.file_path;
                        document.getElementById('pdf-upload-status').textContent = 'Upload complete.';
                    } catch (error) {
                        console.error('Upload error:', error);
                        alert('Failed to upload PDF file');
                        document.getElementById('pdf-upload-status').textContent = 'Upload failed.';
                        return;
                    }
                }
                if (!document.getElementById('file_path').value) {
                    alert('Please select a PDF file to upload');
                    return;
                }
            }

            const data = {
                category_id: formData.get('category_id'),
                title: formData.get('title'),
                description: formData.get('description'),
                document_type: docType,
                status: formData.get('status'),
                tag: formData.get('tag') || null,
                version: formData.get('version') || null,
                date_published: formData.get('date_published') || null,
                featured: formData.get('featured') ? 1 : 0,
                metadata: collectMetadata()
            };

            if (docType === 'link') {
                data.file_url = formData.get('file_url') || null;
                data.file_path = null;
            } else if (docType === 'pdf') {
                data.file_url = null;
                data.file_path = document.getElementById('file_path').value;
            } else if (docType === 'html') {
                let htmlFile = formData.get('html_file_url') || null;
                if (htmlFile && !htmlFile.startsWith('/')) htmlFile = '/' + htmlFile;
                data.file_url = htmlFile;
                data.file_path = null;
            }

            if (editMode) {
                data.id = formData.get('id');
            }

            try {
                const response = await fetch('/api/documents.php', {
                    method: editMode ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
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

        document.getElementById('documentModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
