<?php
// Protect this page - require user authentication
require_once __DIR__ . '/../includes/user-auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - N&E Innovations</title>
    <meta name="description" content="Learn more about N&E Innovations' environmental documentation portal">
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
                    <li><a href="/pages/about.php" class="nav-link active">About</a></li>
                    <li><a href="https://vi-kang.com/contact/" target="_blank" class="btn btn-primary">Contact Us</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero (same as landing page) -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Environmental Impact Documentation</h1>
                <p class="hero-description">
                    Comprehensive environmental assessments, certifications, and compliance documentation
                    for sustainable innovation in packaging and materials.
                </p>
                <div class="hero-cta">
                    <a href="/#categories" class="btn btn-primary">
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

    <!-- Content -->
    <section class="section">
        <div class="container container-narrow">
            <div class="card">
                <h2>Our Commitment to Transparency</h2>
                <p>
                    N&E Innovations is dedicated to environmental sustainability and transparency.
                    This portal provides comprehensive access to our environmental impact assessments,
                    certifications, and compliance documentation.
                </p>

                <h3 class="mt-xl">What You'll Find Here</h3>
                <div class="grid grid-2 mt-lg">
                    <div>
                        <h4>Life Cycle Assessments</h4>
                        <p>Comprehensive environmental impact analyses comparing our innovative products against conventional alternatives.</p>
                    </div>
                    <div>
                        <h4>Certifications</h4>
                        <p>Third-party certifications ensuring quality, safety, and environmental compliance.</p>
                    </div>
                    <div>
                        <h4>Impact Studies</h4>
                        <p>Detailed analyses of environmental benefits and sustainability advantages.</p>
                    </div>
                    <div>
                        <h4>Technical Documentation</h4>
                        <p>Specifications, testing protocols, and product performance data.</p>
                    </div>
                </div>

                <h3 class="mt-xl">About N&E Innovations</h3>
                <p>
                    N&E Innovations Pte Ltd develops sustainable materials and packaging solutions
                    that reduce environmental impact while maintaining high performance standards.
                    Our innovations include bio-based additives for plastics and advanced
                    antibacterial films for food preservation.
                </p>

                <div class="mt-xl text-center">
                    <a href="https://vi-kang.com/contact/" target="_blank" class="btn btn-primary">
                        Contact Us
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
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
