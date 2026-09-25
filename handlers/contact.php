<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

hw_require_post();

$name = hw_clean_text((string) ($_POST['name'] ?? ''), 80);
$email = hw_clean_text((string) ($_POST['email'] ?? ''), 120);
$message = hw_clean_text((string) ($_POST['message'] ?? ''), 2000);
$old = [
    'name' => $name,
    'email' => $email,
    'message' => $message,
];

if (hw_form_blocked()) {
    hw_flash_old('contact', $old);
    hw_flash('form_error', 'Please try again.');
    hw_redirect('contact');
}

$errors = [];
if (!hw_validate_name($name)) {
    $errors['name'] = 'Enter a valid name using letters.';
}
if (!hw_validate_email($email)) {
    $errors['email'] = 'Enter a valid email address.';
}
if (strlen($message) < 10) {
    $errors['message'] = 'Enter a message of at least 10 characters.';
}

if ($errors !== []) {
    $old['_errors'] = $errors;
    hw_flash_old('contact', $old);
    hw_flash('form_error', (string) reset($errors));
    hw_redirect('contact');
}

if (hw_is_disposable_email($email) || hw_text_has_spam($name, $email, $message)) {
    hw_flash_old('contact', $old);
    hw_flash('form_error', 'Please try again.');
    hw_redirect('contact');
}

$record = [
    'type' => 'contact',
    'name' => $name,
    'email' => $email,
    'message' => $message,
];

hw_append_record(HW_MESSAGES_FILE, $record);
hw_send_mail('New Contact Message ( ' . $name . ' ) - Home Worth', hw_contact_email($record), $email);
hw_rotate_csrf();
$_SESSION['form_ready_at'] = time();
hw_flash('thanks', 'contact');
hw_redirect('thank-you');
