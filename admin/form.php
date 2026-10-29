<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$formLib = dirname(__DIR__) . '/includes/form_document.php';
if (!is_file($formLib)) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo 'Form template is missing. Upload includes/form_document.php.';
    exit;
}
require_once $formLib;

hw_require_admin();
header('X-Robots-Tag: noindex, nofollow, noarchive');

function hw_form_fail(string $message): void
{
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Form</title></head>';
    echo '<body style="margin:0;padding:24px;font-family:Poppins,Arial,sans-serif;color:#151d31;background:#e8edf4">';
    echo '<p style="margin:0;font-size:15px">' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    echo '</body></html>';
    exit;
}

if (!function_exists('hw_find_records')) {
    function hw_find_records(string $path, array $ids): array
    {
        if ($path !== HW_LEADS_FILE && $path !== HW_MESSAGES_FILE) {
            return [];
        }
        $wanted = [];
        foreach ($ids as $id) {
            $id = strtolower(trim((string) $id));
            if ($id !== '' && preg_match('/^[a-f0-9]{8,64}$/', $id)) {
                $wanted[$id] = true;
            }
        }
        if ($wanted === []) {
            return [];
        }
        $found = [];
        foreach (hw_read_json($path) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = strtolower((string) ($row['id'] ?? ''));
            if (isset($wanted[$id])) {
                $found[$id] = $row;
            }
        }
        $ordered = [];
        foreach (array_keys($wanted) as $id) {
            if (isset($found[$id])) {
                $ordered[] = $found[$id];
            }
        }
        return $ordered;
    }
}

try {
    $kind = hw_form_kind((string) ($_GET['type'] ?? 'leads'));
    $file = $kind === 'messages' ? HW_MESSAGES_FILE : HW_LEADS_FILE;
    $rawIds = (string) ($_GET['ids'] ?? ($_GET['id'] ?? ''));
    $ids = preg_split('/[,\s]+/', $rawIds, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $records = hw_find_records($file, $ids);
    $download = isset($_GET['download']) && (string) $_GET['download'] !== '0';

    if ($records === []) {
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo 'That form record was not found.';
        exit;
    }

    $filename = hw_form_filename($kind, $records, $download ? 'pdf' : 'html');
    $embed = isset($_GET['embed']) && (string) $_GET['embed'] !== '0';

    if ($download) {
        $pdf = hw_form_pdf_bytes($kind, $records);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');
        echo $pdf;
        exit;
    }

    echo hw_form_html($kind, $records, false, $embed);
} catch (Throwable $e) {
    hw_form_fail('Could not open this form. ' . $e->getMessage());
}
