<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Security & Responsible Disclosure - N&E Innovations</title>
    <link rel="stylesheet" href="/assets/css/style.css">
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
                        <p class="nav-tagline">Compliance Documentation Portal</p>
                    </div>
                </a>
                <ul class="nav-links">
                    <li><a href="/" class="nav-link">Home</a></li>
                    <li><a href="/pages/about" class="nav-link">About</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Content -->
    <div class="legal-page">
        <div class="legal-breadcrumb">
            <a href="/">Home</a><span>/</span> Security
        </div>

        <h1>Security &amp; Responsible Disclosure</h1>

        <p>
            N&amp;E Innovations Pte. Ltd. takes the security of this portal seriously.
        </p>

        <ul>
            <li>Access is restricted to authorized users.</li>
            <li>Security monitoring and logging may be performed for abuse prevention.</li>
            <li>If you believe you have found a vulnerability, please report it to: <a href="mailto:business@vi-kang.com">business@vi-kang.com</a></li>
            <li>Please do not publicly disclose without allowing reasonable time for remediation.</li>
        </ul>
    </div>

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

    <?php include __DIR__ . '/includes/legal-footer.php'; ?>
</body>
</html>
