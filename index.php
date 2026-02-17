<?php
// Protect this page - require user authentication
require_once __DIR__ . '/includes/user-auth.php';

// Get all documents for the "All Documents" section
try {
    $allDocuments = getAllDocuments();
} catch (Exception $e) {
    $allDocuments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N&E Innovations - Compliance Documentation Portal</title>
    <meta name="description" content="Access comprehensive environmental impact assessments, certifications, and compliance documentation for N&E Innovations products.">
    <meta name="robots" content="noindex, nofollow">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Privacy-friendly analytics -->
    <script defer src="https://plausible.io/js/pa-GG3eaoYtZaGluUY9M-pw0.js"></script>

    <!-- Favicon -->
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
                    <li><a href="/" class="nav-link active">Home</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Environmental Impact Documentation</h1>
                <p class="hero-description">
                    Comprehensive environmental assessments, certifications, and compliance documentation
                    for sustainable innovation in packaging and materials.
                </p>
                <div class="hero-cta">
                    <a href="#categories" class="btn btn-primary" onclick="scrollToSection('categories'); return false;">
                        Explore Documentation
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </a>
                    <a href="/pages/search.php" class="btn btn-secondary">
                        Search Documents
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="section" id="categories">
        <div class="container">
            <div class="text-center mb-2xl">
                <h2 class="scroll-animate">Documentation Categories</h2>
                <p class="text-large text-gray scroll-animate">
                    Browse through our comprehensive environmental documentation organized by category
                </p>
            </div>

            <div class="grid grid-2">
                <!-- LCA Reports -->
                <a href="/pages/category.php?slug=lca-reports" class="card scroll-animate" style="transition-delay: 0.1s">
                    <div class="card-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <h3 class="card-title">LCA Reports</h3>
                    <p class="card-description">
                        Comprehensive environmental impact analyses comparing carbon footprints and sustainability metrics
                        of our innovative products.
                    </p>
                    <div class="card-footer">
                        <div class="card-meta">
                            <div class="card-meta-label">Documents</div>
                            <div class="card-meta-value">3</div>
                        </div>
                        <div class="card-meta">
                            <div class="card-meta-label">Latest</div>
                            <div class="card-meta-value">Oct 2025</div>
                        </div>
                    </div>
                </a>

                <!-- Certifications -->
                <a href="/pages/category.php?slug=certifications" class="card scroll-animate" style="transition-delay: 0.2s">
                    <div class="card-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="7"></circle>
                            <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                        </svg>
                    </div>
                    <h3 class="card-title">Certifications</h3>
                    <p class="card-description">
                        Third-party certifications ensuring quality, safety, and environmental compliance
                        through rigorous testing standards.
                    </p>
                    <div class="card-footer">
                        <div class="card-meta">
                            <div class="card-meta-label">Documents</div>
                            <div class="card-meta-value">2</div>
                        </div>
                        <div class="card-meta">
                            <div class="card-meta-label">Status</div>
                            <div class="card-meta-value">Active</div>
                        </div>
                    </div>
                </a>

                <!-- Impact Studies - Coming Soon -->
                <div class="card scroll-animate card-disabled" style="transition-delay: 0.3s; position: relative; cursor: not-allowed; opacity: 0.7;">
                    <!-- Coming Soon Badge -->
                    <div style="position: absolute; top: -10px; right: -10px; z-index: 10;">
                        <div style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); color: white; padding: 0.5rem 1.25rem; border-radius: 999px; font-weight: 700; font-size: 0.875rem; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3); animation: pulse-badge 2s ease-in-out infinite;">
                            ✨ Coming Soon
                        </div>
                    </div>

                    <div class="card-icon" style="opacity: 0.6;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path>
                            <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                        </svg>
                    </div>
                    <h3 class="card-title" style="opacity: 0.8;">Impact Studies</h3>
                    <p class="card-description" style="opacity: 0.7;">
                        Detailed analyses of environmental benefits and sustainability advantages
                        of innovative materials and processes.
                    </p>
                    <div class="card-footer" style="opacity: 0.6;">
                        <div class="card-meta">
                            <div class="card-meta-label">Status</div>
                            <div class="card-meta-value">In Development</div>
                        </div>
                        <div class="card-meta">
                            <div class="card-meta-label">Launch</div>
                            <div class="card-meta-value">Q2 2026</div>
                        </div>
                    </div>
                </div>

                <!-- Technical Documentation -->
                <a href="/pages/category.php?slug=technical-docs" class="card scroll-animate" style="transition-delay: 0.4s">
                    <div class="card-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <h3 class="card-title">Technical Documentation</h3>
                    <p class="card-description">
                        Detailed specifications, testing protocols, and product performance data
                        for all our innovative materials.
                    </p>
                    <div class="card-footer">
                        <div class="card-meta">
                            <div class="card-meta-label">Documents</div>
                            <div class="card-meta-value">3</div>
                        </div>
                        <div class="card-meta">
                            <div class="card-meta-label">Type</div>
                            <div class="card-meta-value">Specs & Tests</div>
                        </div>
                    </div>
                </a>

                <!-- Compliance & Standards -->
                <a href="/pages/category.php?slug=compliance" class="card scroll-animate" style="transition-delay: 0.5s">
                    <div class="card-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h3 class="card-title">Compliance & Standards</h3>
                    <p class="card-description">
                        Regulatory compliance documentation and adherence to international
                        environmental and safety standards.
                    </p>
                    <div class="card-footer">
                        <div class="card-meta">
                            <div class="card-meta-label">Documents</div>
                            <div class="card-meta-value">2</div>
                        </div>
                        <div class="card-meta">
                            <div class="card-meta-label">Standards</div>
                            <div class="card-meta-value">FDA, REACH</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- All Documents Section -->
    <section class="section" id="all-documents">
        <div class="container">
            <div class="text-center mb-2xl">
                <h2 class="scroll-animate">All Documents</h2>
                <p class="text-large text-gray scroll-animate">
                    Browse all environmental documentation across every category
                </p>
            </div>

            <!-- Tag Filter Buttons -->
            <div class="tag-filters scroll-animate">
                <button class="tag-filter-btn active" data-tag="all">All</button>
                <button class="tag-filter-btn" data-tag="vikang">VIKANG</button>
                <button class="tag-filter-btn" data-tag="compostable">Compostable</button>
                <button class="tag-filter-btn" data-tag="biodegradable">Biodegradable</button>
            </div>

            <!-- Documents Grid -->
            <?php if (empty($allDocuments)): ?>
                <div class="text-center" style="padding: 3rem 0;">
                    <svg style="width: 64px; height: 64px; margin: 0 auto 1rem; color: var(--gray-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 style="color: var(--gray-700); margin-bottom: 0.5rem;">No documents found</h3>
                    <p style="color: var(--gray-500);">Documents will appear here once they are added</p>
                </div>
            <?php else: ?>
                <div class="grid grid-2" id="all-documents-grid">
                    <?php foreach ($allDocuments as $doc): ?>
                        <div class="card scroll-animate" data-status="<?php echo esc($doc['status']); ?>" data-tag="<?php echo esc($doc['tag'] ?? 'untagged'); ?>">
                            <!-- Tag Badge & Category -->
                            <div style="margin-bottom: var(--space-md); display: flex; align-items: center; gap: var(--space-sm);">
                                <?php echo getTagBadge($doc['tag']); ?>
                                <span style="font-size: 0.75rem; color: var(--gray-500);">
                                    <?php echo esc($doc['category_name']); ?>
                                </span>
                            </div>

                            <h3 class="card-title"><?php echo esc($doc['title']); ?></h3>
                            <p class="card-description"><?php echo esc($doc['description']); ?></p>

                            <?php if (!empty($doc['metadata'])): ?>
                                <div class="card-footer">
                                    <?php foreach ($doc['metadata'] as $meta): ?>
                                        <div class="card-meta">
                                            <div class="card-meta-label"><?php echo esc($meta['meta_key']); ?></div>
                                            <div class="card-meta-value"><?php echo esc($meta['meta_value']); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: var(--space-lg); display: flex; gap: var(--space-md); align-items: center; justify-content: space-between;">
                                <?php echo getStatusBadge($doc['status']); ?>
                                <?php if (($doc['document_type'] ?? '') === 'pdf' && !empty($doc['file_path'])): ?>
                                    <a href="/view-document.php?id=<?php echo $doc['id']; ?>"
                                       class="btn btn-ghost" style="padding: 0.5rem 1rem;"
                                       onclick="trackDocumentView(<?php echo $doc['id']; ?>)">
                                        View Document
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </a>
                                <?php elseif (($doc['document_type'] ?? '') === 'html' && !empty($doc['file_url'])): ?>
                                    <?php $htmlUrl = (strpos($doc['file_url'], '/') === 0) ? $doc['file_url'] : '/' . $doc['file_url']; ?>
                                    <a href="<?php echo esc($htmlUrl); ?>"
                                       class="btn btn-ghost" style="padding: 0.5rem 1rem;"
                                       onclick="trackDocumentView(<?php echo $doc['id']; ?>)">
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
                                    <a href="<?php echo esc($linkUrl); ?>"
                                       <?php echo $isExternal ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                                       class="btn btn-ghost" style="padding: 0.5rem 1rem;"
                                       onclick="trackDocumentView(<?php echo $doc['id']; ?>)">
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
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section bg-green">
        <div class="container container-narrow text-center">
            <h2 class="scroll-animate">Need More Information?</h2>
            <p class="text-large text-gray mb-xl scroll-animate">
                Contact our team for detailed environmental assessments, custom reports,
                or technical specifications.
            </p>
            <a href="https://vi-kang.com/contact/" target="_blank" class="btn btn-primary scroll-animate">
                Get in Touch
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
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

    <?php include __DIR__ . '/includes/legal-footer.php'; ?>

    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
