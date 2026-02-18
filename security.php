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
        <p class="legal-subtitle"><strong>Effective Date: February 18, 2026</strong></p>

        <h2>1. Our Commitment to Security</h2>
        <p>
            N&amp;E Innovations Pte. Ltd. takes the security of this restricted internal compliance documentation portal (the &quot;Portal&quot;) seriously. Protecting the <strong>confidentiality, integrity, and availability</strong> of the Portal and all materials made available to authorized business partners — including regulatory filings, quality certificates, product specifications, and <strong>Environmental Impact Assessments</strong> of N&amp;E Innovations products — is a core priority. We are committed to maintaining a secure environment that supports safe evaluation of our ViKANG technology offerings.
        </p>

        <h2>2. Scope</h2>
        <p>
            This policy applies to the entire Portal (hosted on <strong>Amazon Web Services (AWS Lightsail)</strong> in the <strong>Frankfurt (eu-central-1)</strong> region), associated systems, networks, and all compliance documentation accessible through it. It governs security practices for authorized users, security researchers, partners, and any third parties who may discover potential vulnerabilities.
        </p>

        <h2>3. Access Controls</h2>
        <p>
            Access to the Portal is <strong>restricted exclusively to authorized business users and partners</strong> of N&amp;E Innovations Pte. Ltd. Any unauthorized access or use is strictly prohibited and may result in immediate suspension or termination of access, as well as potential legal action. We employ robust authentication mechanisms, role-based permissions, and multi-factor authentication where appropriate to enforce these restrictions.
        </p>

        <h2>4. Monitoring and Logging</h2>
        <p>
            Security monitoring and logging are performed on an ongoing basis for abuse prevention, threat detection, fraud prevention, and IT security purposes. This includes server logs, access attempts, and system activity. All monitoring complies with our <a href="/privacy.php">Privacy Policy</a>; personal data in logs is processed only on the legal bases of <strong>Art. 6(1)(b)</strong> and <strong>Art. 6(1)(f) GDPR</strong>.
        </p>

        <h2>5. Technical and Organizational Security Measures</h2>
        <p>
            We implement appropriate technical and organizational measures (per <strong>GDPR Article 32</strong>) to protect the Portal, including encryption of data in transit and at rest, regular security patching, secure configuration of AWS Lightsail resources, access logging via AWS CloudTrail, and continuous vulnerability scanning. AWS acts as our processor under the AWS GDPR Data Processing Addendum.
        </p>

        <h2>6. Vulnerability Management</h2>
        <p>
            We maintain an internal vulnerability management program that includes regular scanning, risk assessment, and timely remediation. Authorized users and external security researchers play a valuable role in identifying potential issues.
        </p>

        <h2>7. Responsible Vulnerability Disclosure Program</h2>
        <p>
            We encourage the responsible and coordinated disclosure of security vulnerabilities. If you believe you have discovered a vulnerability, please follow these guidelines:
        </p>
        <ul>
            <li>Report it privately and in good faith to <a href="mailto:business@vi-kang.com">business@vi-kang.com</a> with sufficient details (description, steps to reproduce, potential impact, and any proof-of-concept if safe to share).</li>
            <li>Do not publicly disclose the vulnerability until we have had reasonable time to investigate and remediate it.</li>
            <li>We commit to: acknowledging your report within <strong>7 business days</strong>, providing a status update within <strong>30 days</strong>, and aiming to remediate critical issues within <strong>90 days</strong> (or provide a mutually agreed extension).</li>
            <li>We offer <strong>safe-harbor protection</strong>: good-faith researchers who comply with this policy will not face legal action.</li>
            <li>Upon validation and remediation, we may publicly credit you (unless you request anonymity).</li>
        </ul>

        <h2>8. Data Breach Response</h2>
        <p>
            In the unlikely event of a personal data breach, we will notify affected EU/EEA users and relevant supervisory authorities <strong>without undue delay</strong> (within <strong>72 hours</strong> where required by <strong>GDPR Article 33</strong>) and take all necessary steps to mitigate harm.
        </p>

        <h2>9. User Security Responsibilities &amp; Contact</h2>
        <p>
            You must not attempt to bypass authentication, probe, scan, or test the vulnerability of the Portal, or interfere with its operation in any way (see also our <a href="/terms.php">Terms of Use</a>). Please report any suspected security issues to <a href="mailto:business@vi-kang.com">business@vi-kang.com</a>. For general questions about this policy, contact the same address.
        </p>
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
