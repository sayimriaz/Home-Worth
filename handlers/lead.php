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
    hw_flash('form_error', hw_form_block_reason());
    hw_redirect('');
}

$errors = [];
if (!hw_validate_name($name)) {
    $errors['name'] = 'Enter a valid name using letters.';
}
if (!hw_validate_phone($phone)) {
    $errors['phone'] = 'Enter a valid phone number with at least 10 digits.';
}
if (!hw_validate_email($email)) {
    $errors['email'] = 'Enter a valid email address.';
}
if (strlen($location) < 3) {
    $errors['location'] = 'Enter the property location.';
}
if (!hw_in_list($propertyType, HW_PROPERTY_TYPES)) {
    $errors['property_type'] = 'Select the type of property.';
}
if (!hw_in_list($expectedPrice, HW_PRICE_RANGES)) {
    $errors['expected_price'] = 'Select an expected price.';
}
if (!$consent) {
    $errors['consent'] = 'Please accept the consent checkbox.';
}

if ($errors !== []) {
    $old['_errors'] = $errors;
    hw_flash_old('lead', $old);
    hw_flash('form_error', (string) reset($errors));
    hw_redirect('');
}

if (hw_is_disposable_email($email)) {
    hw_flash_old('lead', $old);
    hw_flash('form_error', 'Use a regular email address.');
    hw_redirect('');
}
if (hw_text_has_spam($name, $email, $location)) {
    hw_flash_old('lead', $old);
    hw_flash('form_error', 'Please remove links or promotional text and try again.');
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
hw_send_mail('New Property Request ( ' . $name . ' ) - Home Worth', hw_lead_email($lead), $email);
hw_rotate_csrf();
$_SESSION['form_ready_at'] = time();
hw_flash('lead_ok', '1');
header('Location: ' . hw_url() . '#lead');
exit;
