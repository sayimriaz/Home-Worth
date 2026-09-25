<?php

declare(strict_types=1);

if (!defined('HW_APP')) {
    http_response_code(403);
    exit;
}
?>
    <?php if (!in_array(($activePage ?? ''), ['contact', '404', 'thanks'], true)): ?>
    <section class="cta-band">
        <div class="container cta-inner">
            <h2>Contact Us to Sell your Property Today!</h2>
            <div class="dual-cta">
                <a href="<?php echo hw_e(hw_url('contact')); ?>">Contact us</a>
                <a href="tel:<?php echo hw_e(HW_PHONE_TEL); ?>"><?php echo hw_e(HW_PHONE_DISPLAY); ?></a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <img class="footer-logo" src="<?php echo hw_e(hw_url('assets/images/logo.png')); ?>" alt="Home Worth">
                <p class="copyright">Copyright © 2024 Home Worth</p>
            </div>
            <div>
                <h3>About Us</h3>
                <p>Your gateway to exceptional real estate services. Whether you’re selling your home or looking for top-notch guidance, Home Worth is your trusted partner. Unlock your property’s full potential with us.</p>
            </div>
            <div>
                <h3>Location</h3>
                <p><?php echo hw_e(HW_LOCATION); ?></p>
            </div>
            <div>
                <h3>Contact Info</h3>
                <p><a href="tel:<?php echo hw_e(HW_PHONE_TEL); ?>"><?php echo hw_e(HW_PHONE_DISPLAY); ?></a></p>
                <p><a href="mailto:<?php echo hw_e(HW_EMAIL_INFO); ?>"><?php echo hw_e(HW_EMAIL_INFO); ?></a></p>
            </div>
        </div>
        <div class="container footer-bottom">
            <div class="social">
                <span>Follow Us</span>
                <a class="social-btn" href="<?php echo hw_e(HW_FACEBOOK); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M14 9h3V6h-3c-2.2 0-4 1.6-4 3.8V12H8v3h2v6h3v-6h2.4l.6-3H13v-1.4c0-.8.4-1.6 1.8-1.6z"/></svg>
                </a>
                <a class="social-btn" href="<?php echo hw_e(HW_INSTAGRAM); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M8 3h8a5 5 0 0 1 5 5v8a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8a5 5 0 0 1 5-5zm8 2H8a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3zm-4 3.2A3.8 3.8 0 1 1 8.2 12 3.8 3.8 0 0 1 12 8.2zm0 1.8A2 2 0 1 0 14 12a2 2 0 0 0-2-2zm4.4-2.6a1 1 0 1 1-1 1 1 1 0 0 1 1-1z"/></svg>
                </a>
            </div>
            <a class="footer-privacy" href="<?php echo hw_e(hw_url('privacy-policy')); ?>">Privacy Policy</a>
        </div>
    </footer>
    <script src="<?php echo hw_e(hw_url('assets/js/main.js') . '?v=' . filemtime(HW_ROOT . '/assets/js/main.js')); ?>" defer></script>
</body>
</html>
