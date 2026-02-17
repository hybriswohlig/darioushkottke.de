<?php
/**
 * PDF Document Viewer
 * Displays an uploaded PDF in an embedded viewer with site branding and download button
 */

require_once __DIR__ . '/includes/user-auth.php';

// Validate document ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /');
    exit;
}

// Fetch document
$doc = getDocument($id);
if (!$doc || ($doc['document_type'] ?? '') !== 'pdf' || $doc['status'] !== 'published') {
    header('Location: /');
    exit;
}

// Get category info for breadcrumb
$db = getDB();
$catStmt = $db->prepare("SELECT name, slug FROM categories WHERE id = ? LIMIT 1");
$catStmt->execute([$doc['category_id']]);
$category = $catStmt->fetch();

// Log the view
logUserActivity('document_view', $_SERVER['REQUEST_URI'], 'document', $id, 'PDF viewer page');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc($doc['title']); ?> - N&E Innovations</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="/file.svg">
</head>
<body>
    <?php if (isset($GLOBALS['user_expiry_warning_days'])): ?>
    <div id="expiry-banner" style="background: #fef3c7; color: #92400e; padding: 0.75rem 1rem; text-align: center; font-size: 0.875rem; border-bottom: 1px solid #fbbf24; position: relative;">
        <strong>Notice:</strong> Your portal access expires in <?php echo $GLOBALS['user_expiry_warning_days']; ?> day<?php echo $GLOBALS['user_expiry_warning_days'] !== 1 ? 's' : ''; ?>.
        Please contact <a href="mailto:business@vi-kang.com" style="color: #92400e; font-weight: 600; text-decoration: underline;">business@vi-kang.com</a> to extend your access.
        <button onclick="this.parentElement.style.display='none'" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #92400e; cursor: pointer; font-size: 1.25rem;">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="/" class="nav-brand">
                    <img src="/file.svg" alt="N&E Innovations" class="nav-logo" onerror="this.style.display='none'">
                    <div class="nav-title">
                        <h1>N&E Innovations</h1>
                        <p class="nav-tagline">Compliance Documentation Portal</p>
                    </div>
                </a>

                <ul class="nav-links">
                    <li><a href="/" class="nav-link">Home</a></li>
                    <li><a href="/pages/about.php" class="nav-link">About</a></li>
                    <li><a href="https://vi-kang.com/technology/" target="_blank" class="btn btn-ghost">About Vi-kang</a></li>
                    <li><a href="https://vi-kang.com/contact/" target="_blank" class="btn btn-secondary">Contact Us</a></li>
                    <li>
                        <span style="color: var(--gray-600); font-size: 0.875rem;"><?php echo esc($_SESSION['user_name'] ?? ''); ?></span>
                    </li>
                    <li>
                        <a href="/logout.php" class="btn btn-ghost" style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Document Viewer -->
    <section style="padding: var(--space-2xl) 0;">
        <div class="container">
            <!-- Breadcrumb -->
            <div style="margin-bottom: var(--space-lg); font-size: 0.875rem; color: var(--gray-500);">
                <a href="/" style="color: var(--gray-500); text-decoration: none;">Home</a>
                <?php if ($category): ?>
                    <span style="margin: 0 0.5rem;">/</span>
                    <a href="/pages/category.php?slug=<?php echo esc($category['slug']); ?>" style="color: var(--gray-500); text-decoration: none;"><?php echo esc($category['name']); ?></a>
                <?php endif; ?>
                <span style="margin: 0 0.5rem;">/</span>
                <span style="color: var(--gray-700);"><?php echo esc($doc['title']); ?></span>
            </div>

            <!-- Document Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-xl); flex-wrap: wrap; gap: var(--space-md);">
                <div style="flex: 1; min-width: 0;">
                    <h2 style="margin: 0 0 var(--space-sm) 0; font-size: 1.75rem;"><?php echo esc($doc['title']); ?></h2>
                    <p style="color: var(--gray-600); margin: 0;"><?php echo esc($doc['description']); ?></p>
                    <?php if (!empty($doc['metadata'])): ?>
                        <div style="display: flex; gap: var(--space-lg); margin-top: var(--space-md); flex-wrap: wrap;">
                            <?php foreach ($doc['metadata'] as $meta): ?>
                                <div style="font-size: 0.875rem;">
                                    <span style="color: var(--gray-500);"><?php echo esc($meta['meta_key']); ?>:</span>
                                    <span style="font-weight: 600; color: var(--gray-700);"><?php echo esc($meta['meta_value']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="/api/download-pdf.php?id=<?php echo $doc['id']; ?>"
                   class="btn btn-primary"
                   id="download-btn"
                   onclick="handleDownload(this)"
                   style="display: flex; align-items: center; gap: 0.5rem; white-space: nowrap;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </a>
            </div>

            <!-- PDF Viewer -->
            <div style="width: 100%; height: 80vh; border: 1px solid var(--gray-200); border-radius: var(--radius-lg); overflow: hidden; background: var(--gray-100);">
                <iframe
                    src="/api/serve-pdf.php?id=<?php echo $doc['id']; ?>"
                    style="width: 100%; height: 100%; border: none;"
                    title="<?php echo esc($doc['title']); ?>"
                ></iframe>
            </div>

            <!-- Back Link -->
            <div style="margin-top: var(--space-xl); text-align: center;">
                <a href="<?php echo $category ? '/pages/category.php?slug=' . esc($category['slug']) : '/'; ?>" class="btn btn-secondary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to <?php echo $category ? esc($category['name']) : 'Home'; ?>
                </a>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/includes/legal-footer.php'; ?>

    <script>
        function handleDownload(btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Preparing download...';
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.7';
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.style.pointerEvents = '';
                btn.style.opacity = '';
            }, 5000);
        }
    </script>

    <script src="/assets/js/main.js"></script>
</body>
</html>
