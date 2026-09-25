<?php

declare(strict_types=1);

if (!defined('HW_APP')) {
    http_response_code(403);
    exit;
}

function hw_send_mail(string $subject, string $body, ?string $replyTo = null): bool
{
    $to = HW_MAIL_TO;
    $from = HW_EMAIL_INFO;
    $subject = hw_clean_header($subject);
    $replyTo = $replyTo ? hw_clean_header($replyTo) : null;

    if ($subject === '' || !hw_validate_email($to) || !hw_validate_email($from)) {
        return false;
    }

    if ($replyTo && !hw_validate_email($replyTo)) {
        $replyTo = null;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . HW_SITE_NAME . ' <' . $from . '>',
        'Sender: ' . $from,
        'Date: ' . date('r'),
        'X-Mailer: HomeWorth',
    ];

    if ($replyTo) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $payload = str_replace(["\r\n", "\r"], "\n", $body);
    $headerLine = implode("\r\n", $headers);
    $isWindows = strncasecmp(PHP_OS, 'WIN', 3) === 0;

    if ($isWindows) {
        return @mail($to, $encodedSubject, $payload, $headerLine);
    }

    return @mail($to, $encodedSubject, $payload, $headerLine, '-f' . $from);
}

function hw_lead_email(array $lead): string
{
    return "New property request from the Home Worth website\n\n"
        . "Name: {$lead['name']}\n"
        . "Phone: {$lead['phone']}\n"
        . "Email: {$lead['email']}\n"
        . "Property Location: {$lead['location']}\n"
        . "Type of Property: {$lead['property_type']}\n"
        . "Expected Price: {$lead['expected_price']}\n"
        . "Consent: Yes\n";
}

function hw_contact_email(array $message): string
{
    return "New contact message from the Home Worth website\n\n"
        . "Name: {$message['name']}\n"
        . "Email: {$message['email']}\n\n"
        . "Message:\n{$message['message']}\n";
}
