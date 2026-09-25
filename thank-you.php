<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$thanks = hw_flash('thanks');
if ($thanks === null) {
    hw_redirect('');
}

$pageTitle = 'Thank You – Home Worth';
$pageDescription = 'Thank you for contacting Home Worth.';
$activePage = 'thanks';
$bodyClass = 'page-thanks';
$canonicalPath = 'thank-you';
$robots = 'noindex, nofollow, noarchive';

require __DIR__ . '/includes/header.php';
?>

<section class="miss-page">
    <div class="container">
        <div class="miss-stage">
            <img src="<?php echo hw_e(hw_url('assets/images/hero.webp')); ?>" alt="">
            <div class="miss-overlay"></div>
            <div class="miss-content">
                <h1>Thank you</h1>
                <?php if ($thanks === 'lead'): ?>
                    <p>Our team will be in contact with you shortly.</p>
                <?php else: ?>
                    <p>We will get in touch with you in 24 working hours.</p>
                <?php endif; ?>
                <div class="miss-actions">
                    <a class="btn btn-gold" href="<?php echo hw_e(hw_url()); ?>">Back to Home</a>
                    <a class="btn btn-light" href="<?php echo hw_e(hw_url('contact')); ?>">Contact us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
