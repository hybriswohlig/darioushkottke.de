<?php
// Protect this page - require visitor authentication
require_once __DIR__ . '/../includes/visitor-auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Get search query
$query = $_GET['q'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

// Get all categories for filter
$categories = getCategories();

// Perform search if query exists
$results = [];
if (!empty($query) && strlen($query) >= 2) {
    $results = searchDocuments($query, $categoryFilter);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Documents - N&E Innovations</title>
    <meta name="description" content="Search through our comprehensive environmental documentation">

    <link rel="stylesheet" href="/assets/css/style.css">
    <script defer src="https://plausible.io/js/pa-GG3eaoYtZaGluUY9M-pw0.js"></script>
    <link rel="icon" type="image/svg+xml" href="/file.svg">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="/" class="nav-brand">
                    <img src="/file.svg" alt="N&E Innovations" class="nav-logo" onerror="this.style.display='none'">
                    <div class="nav-title">
                        <h1>N&E Innovations</h1>
                        <p class="nav-tagline">Environmental Documentation Portal</p>
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

    <!-- Search Hero -->
    <section class="hero" style="padding: 4rem 0;">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title" style="font-size: clamp(2.5rem, 5vw, 3.5rem);">
                    Search Documentation
                </h1>
                <p class="hero-description">
                    Find the environmental assessments, certifications, and technical documentation you need
                </p>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="section" style="padding-top: 0;">
        <div class="container">
            <!-- Search Form -->
            <form method="GET" action="/pages/search.php" class="search-bar">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35"></path>
                </svg>
                <input
                    type="text"
                    name="q"
                    class="search-input"
                    placeholder="Search for documents, certifications, reports..."
                    value="<?php echo esc($query); ?>"
                    required
                    autofocus
                >
            </form>

            <!-- Category Filters -->
            <div class="filters">
                <a href="/pages/search.php?q=<?php echo urlencode($query); ?>" class="filter-btn <?php echo empty($categoryFilter) ? 'active' : ''; ?>">
                    All Categories
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a
                        href="/pages/search.php?q=<?php echo urlencode($query); ?>&category=<?php echo $cat['id']; ?>"
                        class="filter-btn <?php echo $categoryFilter == $cat['id'] ? 'active' : ''; ?>"
                    >
                        <?php echo esc($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search Results -->
            <?php if (!empty($query)): ?>
                <?php if (strlen($query) < 2): ?>
                    <div class="text-center" style="padding: 3rem 0;">
                        <svg style="width: 64px; height: 64px; margin: 0 auto 1rem; color: var(--gray-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <h3 style="color: var(--gray-700); margin-bottom: 0.5rem;">Search query too short</h3>
                        <p style="color: var(--gray-500);">Please enter at least 2 characters</p>
                    </div>
                <?php elseif (empty($results)): ?>
                    <div class="text-center" style="padding: 3rem 0;">
                        <svg style="width: 64px; height: 64px; margin: 0 auto 1rem; color: var(--gray-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 style="color: var(--gray-700); margin-bottom: 0.5rem;">No results found</h3>
                        <p style="color: var(--gray-500);">Try adjusting your search terms or browse by category</p>
                        <a href="/" class="btn btn-secondary mt-lg">Browse Categories</a>
                    </div>
                <?php else: ?>
                    <!-- Results Count -->
                    <div style="margin-bottom: var(--space-xl); text-align: center; color: var(--gray-600);">
                        Found <strong><?php echo count($results); ?></strong> result<?php echo count($results) !== 1 ? 's' : ''; ?> for "<strong><?php echo esc($query); ?></strong>"
                    </div>

                    <!-- Results Grid -->
                    <div class="grid grid-2">
                        <?php foreach ($results as $doc): ?>
                            <div class="card scroll-animate">
                                <!-- Category Badge -->
                                <div style="margin-bottom: var(--space-md);">
                                    <a
                                        href="/pages/category.php?slug=<?php echo esc($doc['category_slug']); ?>"
                                        class="status-badge"
                                        style="background: var(--green-50); color: var(--primary-green);"
                                    >
                                        <?php echo esc($doc['category_name']); ?>
                                    </a>
                                </div>

                                <h3 class="card-title"><?php echo esc($doc['title']); ?></h3>
                                <p class="card-description"><?php echo esc($doc['description']); ?></p>

                                <!-- Document Metadata -->
                                <?php if (!empty($doc['metadata'])): ?>
                                    <div class="card-footer">
                                        <?php foreach (array_slice($doc['metadata'], 0, 3) as $meta): ?>
                                            <div class="card-meta">
                                                <div class="card-meta-label"><?php echo esc($meta['meta_key']); ?></div>
                                                <div class="card-meta-value"><?php echo esc($meta['meta_value']); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Document Actions -->
                                <div style="margin-top: var(--space-lg); display: flex; gap: var(--space-md); align-items: center; justify-content: space-between;">
                                    <?php echo getStatusBadge($doc['status']); ?>

                                    <?php if (!empty($doc['file_url'])): ?>
                                        <a
                                            href="<?php echo esc($doc['file_url']); ?>"
                                            <?php echo (strpos($doc['file_url'], 'http') === 0) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                                            class="btn btn-ghost"
                                            style="padding: 0.5rem 1rem;"
                                            onclick="trackDocumentView(<?php echo $doc['id']; ?>)"
                                        >
                                            View Document
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center" style="padding: 3rem 0;">
                    <svg style="width: 64px; height: 64px; margin: 0 auto 1rem; color: var(--gray-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35"></path>
                    </svg>
                    <h3 style="color: var(--gray-700); margin-bottom: 0.5rem;">Start searching</h3>
                    <p style="color: var(--gray-500);">Enter keywords to find relevant documents</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <img src="/file.svg" alt="N&E Innovations" class="footer-logo" onerror="this.style.display='none'">
                <h3 class="footer-title">N&E Innovations Pte Ltd</h3>
                <p class="footer-description">
                    Environmental Impact Assessments of N&E Innovations Products
                </p>
                <div class="footer-contact">
                    For more information, contact us at
                    <a href="mailto:business@vi-kang.com">business@vi-kang.com</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
