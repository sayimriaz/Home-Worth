<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Privacy Policy – Home Worth';
$pageDescription = 'Read how Home Worth collects, uses, and protects your personal information.';
$activePage = 'privacy';
$bodyClass = 'page-privacy';
$canonicalPath = 'privacy-policy';
$robots = 'index, follow';

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="hero-overlay"></div>
    <div class="container page-hero-copy">
        <h1>Privacy Policy</h1>
        <p>Home / Privacy Policy</p>
    </div>
</section>

<section class="section privacy-section">
    <div class="container privacy-wrap">
        <h2>1. Information We Collect</h2>
        <p><strong>Personal Information:</strong> We may collect personal information, such as your name, address, email address, and phone number when you voluntarily provide it to us.</p>
        <p><strong>Usage Data:</strong> We may also collect information on how the website is accessed and used. This usage data may include your IP address, browser type, operating system, and other similar technical information.</p>

        <h2>2. Use Of Collected Information</h2>
        <p>We use the information we collect to provide and maintain our services, improve our website, and communicate with you. Your personal information may be used to respond to inquiries, process orders, and provide customer support.</p>

        <h2>3. Data Security</h2>
        <p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction.</p>

        <h2>4. Data Sharing</h2>
        <p>We do not sell, trade, or otherwise transfer your personal information to outside parties unless we provide you with advance notice. This does not include website hosting partners and other parties who assist us in operating our website, conducting our business, or serving you, as long as those parties agree to keep this information confidential.</p>

        <h2>5. Cookies</h2>
        <p>We may use cookies to enhance your experience on our website. You have the option to accept or decline cookies. Most web browsers automatically accept cookies, but you can usually modify your browser settings to decline cookies if you prefer.</p>

        <h2>6. Third-Party Links</h2>
        <p>Our website may contain links to third-party websites. We have no control over the content and practices of these websites and are not responsible for their privacy policies.</p>

        <h2>7. Changes To This Privacy Policy</h2>
        <p>We reserve the right to modify this Privacy Policy at any time. Changes will be effective immediately upon posting on this page.</p>

        <h2>8. Contact Us</h2>
        <p>If you have any questions regarding this Privacy Policy, please contact us at <a href="mailto:<?php echo hw_e(HW_EMAIL_SUPPORT); ?>"><?php echo hw_e(HW_EMAIL_SUPPORT); ?></a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
