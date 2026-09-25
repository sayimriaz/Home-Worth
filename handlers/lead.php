<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

hw_require_post();

$name = hw_clean_text((string) ($_POST['name'] ?? ''), 80);
$phone = hw_clean_text((string) ($_POST['phone'] ?? ''), 20);
$email = hw_clean_text((string) ($_POST['email'] ?? ''), 120);
$location = hw_clean_text((string) ($_POST['location'] ?? ''), 120);
$propertyType = hw_clean_text((string) ($_POST['property_type'] ?? ''), 40);
$expectedPrice = hw_clean_text((string) ($_POST['expected_price'] ?? ''), 40);
$consent = isset($_POST['consent']);
$old = [
    'name' => $name,
    'phone' => $phone,
    'email' => $email,
    'location' => $location,
    'property_type' => $propertyType,
    'expected_price' => $expectedPrice,
    'consent' => $consent ? '1' : '',
];

if (hw_form_blocked()) {
    hw_flash_old('lead', $old);
    hw_flash('form_error', 'Please try again.');
    hw_redirect('');
}

if (hw_rate_limited('lead', HW_RATE_MAX_FORM)) {
    hw_flash_old('lead', $old);
    hw_flash('form_error', 'Too many submissions. Please wait a few minutes and try again.');
    hw_redirect('');
}

$valid = hw_validate_name($name)
    && hw_validate_phone($phone)
    && hw_validate_email($email)
    && strlen($location) >= 3
    && hw_in_list($propertyType, HW_PROPERTY_TYPES)
    && hw_in_list($expectedPrice, HW_PRICE_RANGES)
    && $consent;

if (!$valid) {
    hw_flash_old('lead', $old);
    hw_flash('form_error', 'Please complete every field with valid information and accept the consent checkbox.');
    hw_redirect('');
}

if (hw_is_disposable_email($email) || hw_text_has_spam($name, $email, $location)) {
    hw_flash_old('lead', $old);
    hw_flash('form_error', 'Please try again.');
    hw_redirect('');
}

$lead = [
    'type' => 'property_request',
    'name' => $name,
    'phone' => $phone,
    'email' => $email,
    'location' => $location,
    'property_type' => $propertyType,
    'expected_price' => $expectedPrice,
];

hw_append_record(HW_LEADS_FILE, $lead);
hw_rate_hit('lead');
hw_send_mail('New Property Request ( ' . $name . ' ) - Home Worth', hw_lead_email($lead), $email);
hw_rotate_csrf();
$_SESSION['form_ready_at'] = time();
hw_flash('thanks', 'lead');
hw_redirect('thank-you');
