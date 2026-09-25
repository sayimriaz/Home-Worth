<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Home Worth – Unlocking Real Estate Success Together';
$pageDescription = 'Sell your property with Home Worth. No matter the condition. Instant cash offers and a transparent 3-step process.';
$activePage = 'home';
$bodyClass = 'page-home';
$canonicalPath = '';
$robots = 'index, follow';
$formError = hw_flash('form_error');
$old = hw_flash_old('lead');
$leadOk = hw_flash('lead_ok') === '1';

require __DIR__ . '/includes/header.php';
?>
<?php if ($leadOk): ?>
<div class="form-toast" id="lead-toast" role="status" aria-live="polite">
    <p>Thank you. Our team will be in contact with you shortly.</p>
    <button type="button" data-close aria-label="Close">×</button>
</div>
<style>
#lead-toast{position:fixed;top:96px;left:50%;transform:translateX(-50%);z-index:80;display:flex;align-items:center;gap:12px;width:min(460px,calc(100% - 32px));padding:14px 14px 14px 16px;background:#151d31;color:#fff;border-radius:14px;border-left:5px solid #f5c400;box-shadow:0 16px 40px rgba(0,0,0,.28)}
#lead-toast p{margin:0;flex:1;color:#fff;font-size:14px;font-weight:500;line-height:1.45}
#lead-toast [data-close]{flex:0 0 auto;width:32px;height:32px;border:0;border-radius:8px;background:rgba(255,255,255,.1);color:#fff;font-size:20px;line-height:1;cursor:pointer}
</style>
<?php endif; ?>

<section class="hero">
    <div class="container">
        <div class="hero-shell">
        <div class="hero-photo">
            <img src="<?php echo hw_e(hw_url('assets/images/hero.webp')); ?>" alt="Sell your property with Home Worth">
        </div>
        <div class="hero-board" id="lead">
            <p class="eyebrow">Home Worth - Real Estate</p>
            <h1>Sell Your Property with <span>Home Worth</span></h1>
            <p class="hero-sub">No Matter the Condition</p>
            <a class="hero-call" href="tel:<?php echo hw_e(HW_PHONE_TEL); ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.58 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 7a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .58 3.6 1 1 0 0 1-.25 1z"/></svg>
                <?php echo hw_e(HW_PHONE_DISPLAY); ?>
            </a>
            <p class="lead-intro">Please fill in the form below and our team will be in contact with you shortly.</p>
            <?php if ($formError): ?>
                <p class="form-alert" role="alert"><?php echo hw_e($formError); ?></p>
            <?php endif; ?>
            <form class="lead-form" action="<?php echo hw_e(hw_url('handlers/lead.php')); ?>" method="post" novalidate>
                <?php echo hw_form_shield_fields(); ?>
                <div class="lead-grid">
                    <label class="field<?php echo hw_field_error($old, 'name') !== '' ? ' has-error' : ''; ?>">
                        <span>Your Name</span>
                        <input type="text" name="name" placeholder="Full name" maxlength="80" required autocomplete="name" value="<?php echo hw_e(hw_old($old, 'name')); ?>">
                        <?php if (hw_field_error($old, 'name') !== ''): ?><small class="field-error"><?php echo hw_e(hw_field_error($old, 'name')); ?></small><?php endif; ?>
                    </label>
                    <label class="field<?php echo hw_field_error($old, 'phone') !== '' ? ' has-error' : ''; ?>">
                        <span>Phone</span>
                        <input type="tel" name="phone" placeholder="Phone number" maxlength="20" required autocomplete="tel" value="<?php echo hw_e(hw_old($old, 'phone')); ?>">
                        <?php if (hw_field_error($old, 'phone') !== ''): ?><small class="field-error"><?php echo hw_e(hw_field_error($old, 'phone')); ?></small><?php endif; ?>
                    </label>
                    <label class="field<?php echo hw_field_error($old, 'email') !== '' ? ' has-error' : ''; ?>">
                        <span>Your Email</span>
                        <input type="email" name="email" placeholder="Email address" maxlength="120" required autocomplete="email" value="<?php echo hw_e(hw_old($old, 'email')); ?>">
                        <?php if (hw_field_error($old, 'email') !== ''): ?><small class="field-error"><?php echo hw_e(hw_field_error($old, 'email')); ?></small><?php endif; ?>
                    </label>
                    <label class="field<?php echo hw_field_error($old, 'location') !== '' ? ' has-error' : ''; ?>">
                        <span>Property Location</span>
                        <input type="text" name="location" placeholder="City or address" maxlength="120" required value="<?php echo hw_e(hw_old($old, 'location')); ?>">
                        <?php if (hw_field_error($old, 'location') !== ''): ?><small class="field-error"><?php echo hw_e(hw_field_error($old, 'location')); ?></small><?php endif; ?>
                    </label>
                    <label class="field<?php echo hw_field_error($old, 'property_type') !== '' ? ' has-error' : ''; ?>">
                        <span>Type of Property</span>
                        <select name="property_type" required>
                            <option value="" disabled hidden<?php echo hw_old($old, 'property_type') === '' ? ' selected' : ''; ?>>Select type</option>
                            <?php foreach (HW_PROPERTY_TYPES as $type): ?>
                                <option value="<?php echo hw_e($type); ?>"<?php echo hw_old($old, 'property_type') === $type ? ' selected' : ''; ?>><?php echo hw_e($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (hw_field_error($old, 'property_type') !== ''): ?><small class="field-error"><?php echo hw_e(hw_field_error($old, 'property_type')); ?></small><?php endif; ?>
                    </label>
                    <label class="field<?php echo hw_field_error($old, 'expected_price') !== '' ? ' has-error' : ''; ?>">
                        <span>Expected Price</span>
                        <select name="expected_price" required>
                            <option value="" disabled hidden<?php echo hw_old($old, 'expected_price') === '' ? ' selected' : ''; ?>>Select range</option>
                            <?php foreach (HW_PRICE_RANGES as $range): ?>
                                <option value="<?php echo hw_e($range); ?>"<?php echo hw_old($old, 'expected_price') === $range ? ' selected' : ''; ?>><?php echo hw_e($range); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (hw_field_error($old, 'expected_price') !== ''): ?><small class="field-error"><?php echo hw_e(hw_field_error($old, 'expected_price')); ?></small><?php endif; ?>
                    </label>
                </div>
                <label class="consent<?php echo hw_field_error($old, 'consent') !== '' ? ' has-error' : ''; ?>">
                    <input type="checkbox" name="consent" value="1" required<?php echo hw_old($old, 'consent') === '1' ? ' checked' : ''; ?>>
                    <span class="consent-box" aria-hidden="true"></span>
                    <span class="consent-text"><em>*</em> By submitting this form, you agree to receive calls and messages from our team.</span>
                </label>
                <?php if (hw_field_error($old, 'consent') !== ''): ?><small class="field-error"><?php echo hw_e(hw_field_error($old, 'consent')); ?></small><?php endif; ?>
                <button class="btn btn-gold" type="submit">Get your price today!</button>
            </form>
        </div>
        </div>
    </div>
</section>

<section id="about" class="section about">
    <div class="container about-top">
        <div>
            <h2>About Home Worth</h2>
            <p class="section-kicker">Your Trusted Partner in Real Estate</p>
        </div>
        <div class="about-copy">
            <p>Your gateway to exceptional real estate services. Whether you’re selling your home or looking for top-notch guidance, Home Worth is your trusted partner. Unlock your property’s full potential with us.</p>
            <p>At Home Worth, we stand out as your top choice in real estate partners. With a team of seasoned experts and a deep understanding of local markets, we deliver innovative solutions, competitive rates, and transparent communication. our commitment to your success sets us apart.</p>
        </div>
    </div>
    <div class="container about-bottom">
        <img class="about-photo" src="<?php echo hw_e(hw_url('assets/images/about.jpg')); ?>" alt="Home Worth advisors meeting with a client">
        <div class="about-points">
            <div class="point"><span>Easy Process</span><b>1</b></div>
            <div class="point"><span>No Waiting</span><b>2</b></div>
            <div class="point"><span>Transparent</span><b>3</b></div>
            <div class="point"><span>Best Offers</span><b>4</b></div>
        </div>
    </div>
</section>

<section id="why-us" class="section why">
    <div class="container why-grid">
        <div>
            <h2>Why Choose Us?</h2>
            <p class="section-kicker light">We stand out as your top choice in real estate partners.</p>
            <ul class="why-list">
                <li>
                    <span class="dot" aria-hidden="true"></span>
                    <div>
                        <h3>Innovative Solutions</h3>
                        <p>Home Worth provides innovative solutions to address sellers' needs, offering unique approaches to selling properties effectively in today's market.</p>
                    </div>
                </li>
                <li>
                    <span class="dot" aria-hidden="true"></span>
                    <div>
                        <h3>Instant Cash</h3>
                        <p>With Home Worth sellers can enjoy instant cash offers, providing them with immediate financial solutions for their property.</p>
                    </div>
                </li>
                <li>
                    <span class="dot" aria-hidden="true"></span>
                    <div>
                        <h3>Best Offers</h3>
                        <p>We guarantee the best offers for sellers through our rigorous evaluation process, maximizing the value of their properties in the market.</p>
                    </div>
                </li>
            </ul>
        </div>
        <div class="why-media">
            <div class="sold-card">
                <strong data-count="9000" data-suffix=" +">0 +</strong>
                <span>Properties Sold</span>
            </div>
            <img src="<?php echo hw_e(hw_url('assets/images/why-us-1.jpg')); ?>" alt="Real estate advisors reviewing a property plan">
            <img class="why-wide" src="<?php echo hw_e(hw_url('assets/images/why-us-2.jpg')); ?>" alt="Modern home with a swimming pool">
        </div>
    </div>
</section>

<section class="stats">
    <div class="container stats-grid">
        <div><strong data-count="7" data-suffix=" +">0 +</strong><span>Years in the industry</span></div>
        <div><strong data-count="93" data-suffix=" %">0 %</strong><span>Customer satisfaction</span></div>
        <div><strong data-count="9000" data-suffix=" +">0 +</strong><span>Properties sold</span></div>
        <div><strong data-count="20" data-suffix=" +">0 +</strong><span>States covered</span></div>
    </div>
</section>

<section id="how-we-work" class="section process">
    <div class="container">
        <h2>How we Work?</h2>
        <p class="process-lead">Streamline your home selling journey with Home Worth’s simple 3-step process. From an in-home consultation to a transparent offer and a stress-free closing, we’re with you every step of the way.</p>
        <div class="steps">
            <article class="step">
                <div class="step-icon" aria-hidden="true">1</div>
                <h3>Step 1: In-Home Consultation</h3>
                <p>One of our home sales advisers will contact you, evaluate your house and learn more about your need and expectations.</p>
            </article>
            <article class="step">
                <div class="step-icon" aria-hidden="true">2</div>
                <h3>Step 2: Transparent Offers</h3>
                <p>You will receive a fair, no-obligation offer within 24 hours and we will explain how we calculated it</p>
            </article>
            <article class="step">
                <div class="step-icon" aria-hidden="true">3</div>
                <h3>Step 3: Stress-free Closing</h3>
                <p>Close on a date of your choice with no last-minute surprises. You walk away with your check and move on with your life, worry free.</p>
            </article>
        </div>
    </div>
</section>

<section id="testimonials" class="section testimonials">
    <div class="container" data-slider>
        <div class="testimonial-head">
            <div>
                <h2>Testimonials</h2>
                <p class="section-kicker light">Hear what they say</p>
            </div>
            <div class="slider-controls">
                <button type="button" class="slider-btn" data-prev aria-label="Previous testimonial">‹</button>
                <div class="slider-dots" data-dots></div>
                <button type="button" class="slider-btn" data-next aria-label="Next testimonial">›</button>
            </div>
        </div>
        <div class="testimonial-wrap">
            <img class="testimonial-photo" src="<?php echo hw_e(hw_url('assets/images/testimonials.jpg')); ?>" alt="Happy homeowners">
            <div class="theme-slider">
                <div class="slider-viewport">
                    <div class="slider-track">
                        <?php
                        $star = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.7 6.4 6.8.6-5.2 4.5 1.6 6.6L12 17.2 6.1 20.6l1.6-6.6L2.5 9.5l6.8-.6L12 2.5z"/></svg>';
                        $reviews = [
                            ['James K', 'avatar-james.jpg', 'My wife and I recently sold our family home through Home Worth, and the experience was seamless. Their team was professional, responsive, and guided us every step of the way. The house was on the market for only two weeks before they secured an offer above our asking price. We couldn’t be happier with the outcome.'],
                            ['Amanda', 'avatar-amanda.jpg', 'Working with Home Worth was an absolute pleasure. They were always available to answer our questions and provided expert advice that made the selling process stress-free. Our villa sold in record time, and we were thrilled with the final sale price. We highly recommend their services.'],
                            ['Michael P.', 'avatar-michael.jpg', 'Home Worth exceeded our expectations in every way. Their attention to detail and market knowledge were key in selling our condo quickly and at a great price. I appreciated their transparency and the ease with which they handled everything. We would gladly work with them again.'],
                            ['George W', 'avatar-george.jpg', 'I had a fantastic experience selling our estate with Home Worth. Their team was not only efficient but also very personable and understanding of our needs. The estate sold for a price we were very satisfied with, and the entire process was smooth and well-organized. Thank you, Home Worth!'],
                        ];
                        foreach ($reviews as $review):
                        ?>
                        <article class="quote slider-slide">
                            <span class="quote-mark" aria-hidden="true">“</span>
                            <p class="stars" aria-label="Rated 5 out of 5"><?php echo str_repeat($star, 5); ?></p>
                            <p class="quote-text"><?php echo hw_e($review[2]); ?></p>
                            <div class="quote-author">
                                <img src="<?php echo hw_e(hw_url('assets/images/' . $review[1])); ?>" alt="">
                                <span><?php echo hw_e($review[0]); ?></span>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="faqs" class="section faqs">
    <div class="container faq-grid">
        <div class="accordion" data-accordion>
            <?php
            $faqs = [
                ['What information do you collect from me?', 'We collect personal information, including your name, address, email address, and phone number when you voluntarily provide it to us. Additionally, we may gather usage data, such as IP addresses and browsing behavior.'],
                ['How do you use my information?', 'We use your information to provide and improve our services, communicate with you, process orders, and respond to inquiries. We may also use it for marketing purposes with your consent.'],
                ['Do you share my information with third parties?', 'We do not sell, trade, or otherwise transfer your personal information to outside parties unless we provide you with advance notice. However, we may share it with trusted third parties who assist us in operating our website and servicing you.'],
                ['How do you protect my data?', 'We have implemented various security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction. Our security protocols are regularly updated to ensure the safety of your data.'],
                ['Can I opt out of providing certain information?', 'In some cases, providing certain information may be necessary for us to provide you with our services. However, you can choose not to provide optional information. Please note that this may limit your access to certain features of our website.'],
                ['How will I know if there are changes to this Privacy Policy?', 'We will notify you of any changes to our Privacy Policy by posting the updated version on our website. We encourage you to review this page periodically to stay informed about how we are protecting your information.'],
                ['How can I contact you regarding my privacy concerns?', 'If you have any questions or concerns about our Privacy Policy or the handling of your personal information, please contact us at support@homeworthofficial.com'],
            ];
            foreach ($faqs as $i => $faq):
            ?>
            <div class="acc-item<?php echo $i === 0 ? ' is-open' : ''; ?>">
                <button class="acc-btn" type="button" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                    <?php echo hw_e($faq[0]); ?>
                    <span aria-hidden="true"></span>
                </button>
                <div class="acc-panel">
                    <p><?php echo hw_e($faq[1]); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <aside class="faq-side">
            <img src="<?php echo hw_e(hw_url('assets/images/faq.jpg')); ?>" alt="Home Worth support specialist">
            <h3>Frequently Asked Questions</h3>
            <p>Have other queries?</p>
            <a class="btn btn-dark" href="<?php echo hw_e(hw_url('contact')); ?>">Contact Us</a>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
