<?php

declare(strict_types=1);

http_response_code(404);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Page not found – Home Worth';
$pageDescription = 'The page you requested could not be found.';
$activePage = '404';
$bodyClass = 'page-404';
$robots = 'noindex, nofollow';
$noCanonical = true;

require __DIR__ . '/includes/header.php';
?>

<section class="miss-page">
    <div class="container">
        <div class="miss-stage">
            <img src="<?php echo hw_e(hw_url('assets/images/hero.webp')); ?>" alt="">
            <div class="miss-overlay"></div>
            <div class="miss-content">
                <span class="miss-code">404</span>
                <h1>Page not found</h1>
                <p>The page you are looking for is not available.</p>
                <div class="miss-actions">
                    <a class="btn btn-gold" href="<?php echo hw_e(hw_url()); ?>">Back to Home</a>
                    <a class="btn btn-light" href="<?php echo hw_e(hw_url('contact')); ?>">Contact us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
