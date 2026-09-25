<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Contact Us – Home Worth';
$pageDescription = 'Get in touch with Home Worth. We will contact you within 24 working hours.';
$activePage = 'contact';
$bodyClass = 'page-contact';
$canonicalPath = 'contact';
$robots = 'index, follow';
$formError = hw_flash('form_error');
$old = hw_flash_old('contact');

require __DIR__ . '/includes/header.php';
?>

<section class="contact-page">
    <div class="container">
        <div class="contact-intro">
            <p class="contact-crumb"><a href="<?php echo hw_e(hw_url()); ?>">Home</a> / Contact Us</p>
            <h1>Contact Us</h1>
            <p>Fill out the form bellow. We will get in touch with you in 24 working hours.</p>
        </div>
        <div class="contact-shell">
            <aside class="contact-aside">
                <h2>Get In Touch</h2>

                <div class="contact-tiles">
                    <a class="contact-tile" href="mailto:<?php echo hw_e(HW_EMAIL_INFO); ?>">
                        <span class="contact-tile-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5L4 8V6l8 5 8-5z"/></svg>
                        </span>
                        <span>
                            <small>Email</small>
                            <?php echo hw_e(HW_EMAIL_INFO); ?></span>
                    </a>
                    <a class="contact-tile" href="tel:<?php echo hw_e(HW_PHONE_TEL); ?>">
                        <span class="contact-tile-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.58 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 7a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .58 3.6 1 1 0 0 1-.25 1z"/></svg>
                        </span>
                        <span>
                            <small>Phone</small>
                            <?php echo hw_e(HW_PHONE_DISPLAY); ?></span>
                    </a>
                </div>

                <div class="contact-aside-media" aria-hidden="true">
                    <img src="<?php echo hw_e(hw_url('assets/images/contact-hero.jpg')); ?>" alt="">
                </div>
            </aside>

            <div class="contact-panel">
                <h2>Send Message</h2>
                <?php if ($formError): ?>
                    <p class="form-alert" role="alert"><?php echo hw_e($formError); ?></p>
                <?php endif; ?>
                <form class="contact-form" action="<?php echo hw_e(hw_url('handlers/contact.php')); ?>" method="post" novalidate>
                    <?php echo hw_form_shield_fields(); ?>
                    <label class="field">
                        <span>Your Name</span>
                        <input type="text" name="name" placeholder="Your Name" maxlength="80" required autocomplete="name" value="<?php echo hw_e(hw_old($old, 'name')); ?>">
                    </label>
                    <label class="field">
                        <span>Your Email</span>
                        <input type="email" name="email" placeholder="Your Email" maxlength="120" required autocomplete="email" value="<?php echo hw_e(hw_old($old, 'email')); ?>">
                    </label>
                    <label class="field">
                        <span>Write Your Message Here...</span>
                        <textarea name="message" rows="6" placeholder="Write Your Message Here..." maxlength="2000" required><?php echo hw_e(hw_old($old, 'message')); ?></textarea>
                    </label>
                    <button class="btn btn-gold full" type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
