<?php

declare(strict_types=1);

if (!defined('HW_APP')) {
    http_response_code(403);
    exit;
}

function hw_ensure_storage(): void
{
    $dirs = [HW_STORAGE_DIR, HW_ROOT . '/storage/sessions'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    foreach ([HW_LEADS_FILE, HW_MESSAGES_FILE, HW_RATE_FILE, HW_LOGIN_LOCK_FILE] as $file) {
        if (!is_file($file)) {
            hw_write_json($file, []);
        }
    }
}

function hw_read_json(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $handle = fopen($path, 'rb');
    if ($handle === false) {
        return [];
    }

    flock($handle, LOCK_SH);
    $raw = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $data = json_decode((string) $raw, true);
    return is_array($data) ? $data : [];
}

function hw_write_json(string $path, array $data): bool
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }

    $tmp = $path . '.tmp';
    $handle = fopen($tmp, 'c+b');
    if ($handle === false) {
        return false;
    }

    flock($handle, LOCK_EX);
    ftruncate($handle, 0);
    rewind($handle);
    $ok = fwrite($handle, $json) !== false;
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    if (!$ok) {
        return false;
    }

    return rename($tmp, $path);
}

function hw_append_record(string $path, array $record): bool
{
    $data = hw_read_json($path);
    if ($data !== []) {
        $data = array_values($data);
    }
    $record['id'] = bin2hex(random_bytes(8));
    $record['created_at'] = gmdate('c');
    $data[] = $record;
    return hw_write_json($path, $data);
}

function hw_admin_exists(): bool
{
    $admin = hw_read_json(HW_ADMIN_FILE);
    return isset($admin['username'], $admin['password_hash']) && $admin['username'] !== '' && $admin['password_hash'] !== '';
}

function hw_create_admin(string $username, string $password): bool
{
    if (hw_admin_exists()) {
        return false;
    }

    return hw_write_json(HW_ADMIN_FILE, [
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'path' => HW_ADMIN_SLUG_DEFAULT,
        'created_at' => gmdate('c'),
    ]);
}

function hw_admin_slug_ok(string $slug): bool
{
    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{2,39}$/', $slug)) {
        return false;
    }
    return !in_array(strtolower($slug), HW_ADMIN_SLUG_RESERVED, true);
}

function hw_admin_slug(): string
{
    $admin = hw_read_json(HW_ADMIN_FILE);
    $slug = (string) ($admin['path'] ?? HW_ADMIN_SLUG_DEFAULT);
    return hw_admin_slug_ok($slug) ? $slug : HW_ADMIN_SLUG_DEFAULT;
}

function hw_update_admin_path(string $slug): bool
{
    if (!hw_admin_slug_ok($slug)) {
        return false;
    }
    $admin = hw_read_json(HW_ADMIN_FILE);
    if (!isset($admin['username'])) {
        return false;
    }
    $admin['path'] = $slug;
    $admin['updated_at'] = gmdate('c');
    return hw_write_json(HW_ADMIN_FILE, $admin);
}

function hw_verify_admin(string $username, string $password): bool
{
    $admin = hw_read_json(HW_ADMIN_FILE);
    if (!isset($admin['username'], $admin['password_hash'])) {
        return false;
    }

    $userOk = hash_equals((string) $admin['username'], $username);
    $passOk = password_verify($password, (string) $admin['password_hash']);
    return $userOk && $passOk;
}

function hw_export_csv(string $filename, array $headers, array $rows): void
{
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '', $filename) ?: 'export.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');

    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    if ($out === false) {
        exit;
    }

    fputcsv($out, $headers);
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

function hw_admin_date(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    $time = strtotime($value);
    return $time ? date('M j, Y g:ia', $time) : '';
}

function hw_preview(string $text, int $length = 72): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    if (function_exists('mb_strlen') && mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '…';
    }
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '…';
    }
    return $text;
}

function hw_delete_record(string $path, string $id): bool
{
    if (!preg_match('/^[a-f0-9]{16}$/', $id)) {
        return false;
    }
    if ($path !== HW_LEADS_FILE && $path !== HW_MESSAGES_FILE) {
        return false;
    }

    $data = hw_read_json($path);
    $kept = [];
    $found = false;
    foreach ($data as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rowId = (string) ($row['id'] ?? '');
        if ($rowId !== '' && hash_equals($rowId, $id)) {
            $found = true;
            continue;
        }
        $kept[] = $row;
    }

    return $found && hw_write_json($path, array_values($kept));
}

function hw_update_admin_password(string $password): bool
{
    $admin = hw_read_json(HW_ADMIN_FILE);
    if (!isset($admin['username'])) {
        return false;
    }
    $admin['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    if (empty($admin['path']) || !hw_admin_slug_ok((string) $admin['path'])) {
        $admin['path'] = HW_ADMIN_SLUG_DEFAULT;
    }
    $admin['updated_at'] = gmdate('c');
    return hw_write_json(HW_ADMIN_FILE, $admin);
}
