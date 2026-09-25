<?php

declare(strict_types=1);

if (!defined('HW_APP')) {
    http_response_code(403);
    exit;
}

$pageTitle = $pageTitle ?? HW_SITE_NAME . ' – ' . HW_SITE_TAGLINE;
$pageDescription = $pageDescription ?? 'Sell your property with Home Worth. Instant cash offers, a transparent process, and a stress-free closing.';
$activePage = $activePage ?? 'home';
$bodyClass = $bodyClass ?? '';
$robots = $robots ?? 'index, follow';
$canonicalPath = $canonicalPath ?? '';
$noCanonical = $noCanonical ?? false;
$home = $activePage === 'home';
$canonical = $noCanonical ? '' : hw_abs_url($canonicalPath);
$ogImage = $ogImage ?? hw_abs_url('assets/images/hero.webp');
$ogType = $ogType ?? 'website';

if (str_contains($robots, 'noindex')) {
    hw_send_robots($robots);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo hw_e($pageTitle); ?></title>
    <meta name="description" content="<?php echo hw_e($pageDescription); ?>">
    <meta name="robots" content="<?php echo hw_e($robots); ?>">
    <meta name="googlebot" content="<?php echo hw_e($robots); ?>">
    <meta name="author" content="Home Worth">
    <meta name="theme-color" content="#151D31">
    <?php if ($canonical !== ''): ?>
        <link rel="canonical" href="<?php echo hw_e($canonical); ?>">
    <?php endif; ?>
    <link rel="icon" href="<?php echo hw_e(hw_url('assets/images/favicon.png')); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo hw_e(hw_url('assets/images/favicon.png')); ?>">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="<?php echo hw_e($ogType); ?>">
    <meta property="og:site_name" content="Home Worth">
    <meta property="og:title" content="<?php echo hw_e($pageTitle); ?>">
    <meta property="og:description" content="<?php echo hw_e($pageDescription); ?>">
    <?php if ($canonical !== ''): ?>
        <meta property="og:url" content="<?php echo hw_e($canonical); ?>">
    <?php endif; ?>
    <meta property="og:image" content="<?php echo hw_e($ogImage); ?>">
    <meta property="og:image:alt" content="Home Worth">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo hw_e($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo hw_e($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo hw_e($ogImage); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo hw_e(hw_url('assets/css/style.css') . '?v=' . filemtime(HW_ROOT . '/assets/css/style.css')); ?>">
    <?php if (!str_contains($robots, 'noindex')): ?>
    <script type="application/ld+json">
    <?php echo json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'RealEstateAgent',
                '@id' => hw_abs_url() . '#business',
                'name' => HW_SITE_NAME,
                'url' => hw_abs_url(),
                'image' => hw_abs_url('assets/images/logo.png'),
                'telephone' => HW_PHONE_TEL,
                'email' => HW_EMAIL_INFO,
                'description' => $pageDescription,
                'areaServed' => HW_LOCATION,
                'sameAs' => [HW_FACEBOOK, HW_INSTAGRAM],
            ],
            [
                '@type' => 'WebSite',
                '@id' => hw_abs_url() . '#website',
                'name' => HW_SITE_NAME,
                'url' => hw_abs_url(),
                'publisher' => ['@id' => hw_abs_url() . '#business'],
            ],
            [
                '@type' => 'WebPage',
                '@id' => ($canonical !== '' ? $canonical : hw_abs_url()) . '#webpage',
                'url' => $canonical !== '' ? $canonical : hw_abs_url(),
                'name' => $pageTitle,
                'description' => $pageDescription,
                'isPartOf' => ['@id' => hw_abs_url() . '#website'],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    <?php endif; ?>
</head>
<body class="<?php echo hw_e($bodyClass); ?>">
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="<?php echo hw_e(hw_url()); ?>" aria-label="Home Worth">
                <span class="brand-mark"><img src="<?php echo hw_e(hw_url('assets/images/logo.png')); ?>" alt=""></span>
                <span class="brand-text">Home Worth</span>
            </a>
            <nav id="site-nav" class="site-nav">
                <a href="<?php echo hw_e($home ? '#about' : hw_url() . '#about'); ?>">About</a>
                <a href="<?php echo hw_e($home ? '#why-us' : hw_url() . '#why-us'); ?>">Why Us?</a>
                <a href="<?php echo hw_e($home ? '#how-we-work' : hw_url() . '#how-we-work'); ?>">How we Work?</a>
                <a href="<?php echo hw_e($home ? '#testimonials' : hw_url() . '#testimonials'); ?>">Testimonials</a>
                <a href="<?php echo hw_e($home ? '#faqs' : hw_url() . '#faqs'); ?>">FAQ’s</a>
                <a href="<?php echo hw_e(hw_url('contact')); ?>" class="<?php echo $activePage === 'contact' ? 'is-active' : ''; ?>">Contact</a>
            </nav>
            <div class="header-actions">
                <a class="nav-call" href="tel:<?php echo hw_e(HW_PHONE_TEL); ?>">
                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.58 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 7a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .58 3.6 1 1 0 0 1-.25 1z"/></svg>
                    <span><?php echo hw_e(HW_PHONE_DISPLAY); ?></span>
                </a>
                <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Open menu">
                    <span></span><span></span>
                </button>
            </div>
        </div>
    </header>
