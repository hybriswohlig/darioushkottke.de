<?php
// Protect this page - require user authentication
require_once __DIR__ . '/../includes/user-auth.php';

// Get category slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /');
    exit;
}

// Get category details
$category = getCategoryBySlug($slug);

if (!$category) {
    header('Location: /');
    exit;
}

// Get filter parameters (status is not exposed to users - only published docs are shown)
$searchQuery = $_GET['search'] ?? '';

// Get documents for this category (only published are shown to normal users)
$filters = [
    'search' => $searchQuery
];
$documents = getDocumentsByCategory($category['id'], $filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc($category['name']); ?> - N&E Innovations</title>
    <meta name="description" content="<?php echo esc($category['description']); ?>">
    <meta name="robots" content="noindex, nofollow">

    <link rel="stylesheet" href="/assets/css/style.css">
    <script defer src="https://plausible.io/js/pa-GG3eaoYtZaGluUY9M-pw0.js"></script>
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
                    <li><a href="https://vi-kang.com/contact/" target="_blank" class="btn btn-primary">Contact Us</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Category Hero -->
    <section class="hero" style="padding: 4rem 0;">
        <div class="container">
            <div class="hero-content">
                <div class="card-icon" style="margin: 0 auto 1.5rem; width: 64px; height: 64px;">
                    <?php echo $category['icon_svg']; ?>
                </div>
                <h1 class="hero-title" style="font-size: clamp(2.5rem, 5vw, 3.5rem);">
                    <?php echo esc($category['name']); ?>
                </h1>
                <p class="hero-description">
                    <?php echo esc($category['description']); ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Search & Filters -->
    <section class="section" style="padding-top: 0;">
        <div class="container">
            <!-- Search Bar -->
            <div class="search-bar">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35"></path>
                </svg>
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Search documents..."
                    value="<?php echo esc($searchQuery); ?>"
                >
            </div>

            <!-- Status Filters -->
            <div class="filters">
                <button class="filter-btn <?php echo empty($statusFilter) ? 'active' : ''; ?>" data-filter="all">
                    All Documents
                </button>
                <button class="filter-btn <?php echo $statusFilter === 'published' ? 'active' : ''; ?>" data-filter="published">
                    Published
                </button>
                <button class="filter-btn <?php echo $statusFilter === 'under_review' ? 'active' : ''; ?>" data-filter="under_review">
                    Under Review
                </button>
                <button class="filter-btn <?php echo $statusFilter === 'in_progress' ? 'active' : ''; ?>" data-filter="in_progress">
                    In Progress
                </button>
            </div>

            <!-- Documents Grid -->
            <?php if (empty($documents)): ?>
                <div class="text-center" style="padding: 3rem 0;">
                    <svg style="width: 64px; height: 64px; margin: 0 auto 1rem; color: var(--gray-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 style="color: var(--gray-700); margin-bottom: 0.5rem;">No documents available</h3>
                    <p style="color: var(--gray-500);">Documents will appear here once they are added</p>
                </div>
            <?php else: ?>
                <div class="grid grid-2">
                    <?php foreach ($documents as $doc):
                        $isPreview = in_array($doc['status'], ['planned', 'in_progress']);
                    ?>
                        <div class="card scroll-animate<?php echo $isPreview ? ' card-preview' : ''; ?>" data-status="<?php echo esc($doc['status']); ?>" data-tag="<?php echo esc($doc['tag'] ?? 'untagged'); ?>" style="position: relative;">
                            <?php if ($isPreview): ?>
                                <!-- Coming Soon Badge -->
                                <div style="position: absolute; top: -10px; right: -10px; z-index: 10;">
                                    <div class="coming-soon-badge">Coming Soon</div>
                                </div>
                            <?php elseif ($doc['featured']): ?>
                                <div style="position: absolute; top: 1rem; right: 1rem; background: var(--accent-green); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600;">
                                    Featured
                                </div>
                            <?php endif; ?>

                            <div style="margin-bottom: var(--space-sm);">
                                <?php echo getTagBadge($doc['tag']); ?>
                            </div>

                            <h3 class="card-title"><?php echo esc($doc['title']); ?></h3>
                            <p class="card-description"><?php echo esc($doc['description']); ?></p>

                            <!-- Document Metadata -->
                            <?php $displayMeta = getFormattedDocumentMetadata($doc); ?>
                            <?php if (!empty($displayMeta)): ?>
                                <div class="card-footer">
                                    <?php foreach ($displayMeta as $meta): ?>
                                        <div class="card-meta">
                                            <div class="card-meta-label"><?php echo esc($meta['label']); ?></div>
                                            <div class="card-meta-value"><?php echo $meta['value']; ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Document Actions -->
                            <div style="margin-top: var(--space-lg); display: flex; gap: var(--space-md); align-items: center; justify-content: space-between;">
                                <?php echo getStatusBadge($doc['status']); ?>

                                <?php if ($isPreview): ?>
                                    <span style="font-size: 0.875rem; color: var(--gray-400); font-weight: 500;">Available soon</span>
                                <?php elseif (($doc['document_type'] ?? '') === 'pdf' && !empty($doc['file_path'])): ?>
                                    <a
                                        href="/view-document.php?id=<?php echo $doc['id']; ?>"
                                        class="btn btn-ghost"
                                        style="padding: 0.5rem 1rem;"
                                        onclick="trackDocumentView(<?php echo $doc['id']; ?>)"
                                    >
                                        View Document
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </a>
                                <?php elseif (($doc['document_type'] ?? '') === 'html' && !empty($doc['file_url'])): ?>
                                    <?php $htmlUrl = (strpos($doc['file_url'], '/') === 0) ? $doc['file_url'] : '/' . $doc['file_url']; ?>
                                    <a
                                        href="<?php echo esc($htmlUrl); ?>"
                                        class="btn btn-ghost"
                                        style="padding: 0.5rem 1rem;"
                                        onclick="trackDocumentView(<?php echo $doc['id']; ?>)"
                                    >
                                        View Document
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </a>
                                <?php elseif (!empty($doc['file_url'])): ?>
                                    <?php
                                        $linkUrl = $doc['file_url'];
                                        $isExternal = (strpos($linkUrl, 'http') === 0);
                                        if (!$isExternal && strpos($linkUrl, '/') !== 0) {
                                            $linkUrl = '/' . $linkUrl;
                                        }
                                    ?>
                                    <a
                                        href="<?php echo esc($linkUrl); ?>"
                                        <?php echo $isExternal ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                                        class="btn btn-ghost"
                                        style="padding: 0.5rem 1rem;"
                                        onclick="trackDocumentView(<?php echo $doc['id']; ?>)"
                                    >
                                        View Document
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <?php if ($isExternal): ?>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            <?php else: ?>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            <?php endif; ?>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Back to Categories -->
            <div class="text-center mt-2xl">
                <a href="/" class="btn btn-secondary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Categories
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <img src="/file.svg" alt="N&E Innovations" class="footer-logo" onerror="this.style.display='none'">
                <h3 class="footer-title">N&E Innovations Pte Ltd</h3>
                <p class="footer-description">
                    Restricted internal compliance portal for authorized partners only. Unauthorized access prohibited.
                </p>
                <div class="footer-contact">
                    For more information, contact us at
                    <a href="mailto:business@vi-kang.com">business@vi-kang.com</a>
                </div>
            </div>
        </div>
    </footer>

    <?php include __DIR__ . '/../includes/legal-footer.php'; ?>

    <script src="/assets/js/main.js"></script>
</body>
</html>
