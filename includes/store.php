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

function hw_admin_date_key(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    $time = strtotime($value);
    return $time ? date('Y-m-d', $time) : substr($value, 0, 10);
}

function hw_admin_filter_query(string $tab, string $q, string $from, string $to, int $page = 1): string
{
    $params = ['tab' => $tab];
    if ($q !== '') {
        $params['q'] = $q;
    }
    if ($from !== '') {
        $params['from'] = $from;
    }
    if ($to !== '') {
        $params['to'] = $to;
    }
    if ($page > 1) {
        $params['page'] = $page;
    }
    return '?' . http_build_query($params);
}

function hw_admin_filter_records(array $records, string $q, string $from, string $to): array
{
    $q = trim($q);
    $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : '';
    $to = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) ? $to : '';
    $qLower = function_exists('mb_strtolower') ? mb_strtolower($q) : strtolower($q);
    $qDigits = preg_replace('/\D+/', '', $q) ?? '';

    if ($q === '' && $from === '' && $to === '') {
        return array_values($records);
    }

    $out = [];
    foreach ($records as $row) {
        if (!is_array($row)) {
            continue;
        }
        $day = hw_admin_date_key(isset($row['created_at']) ? (string) $row['created_at'] : null);
        if ($from !== '' && ($day === '' || $day < $from)) {
            continue;
        }
        if ($to !== '' && ($day === '' || $day > $to)) {
            continue;
        }
        if ($q !== '') {
            $name = (string) ($row['name'] ?? '');
            $email = (string) ($row['email'] ?? '');
            $location = (string) ($row['location'] ?? '');
            $message = (string) ($row['message'] ?? '');
            $hay = function_exists('mb_strtolower')
                ? mb_strtolower($name . ' ' . $email . ' ' . $location . ' ' . $message)
                : strtolower($name . ' ' . $email . ' ' . $location . ' ' . $message);
            $phone = preg_replace('/\D+/', '', (string) ($row['phone'] ?? '')) ?? '';
            $textHit = $qLower !== '' && str_contains($hay, $qLower);
            $phoneHit = $qDigits !== '' && strlen($qDigits) >= 3 && str_contains($phone, $qDigits);
            $zipHit = preg_match('/^\d{5}(?:-\d{4})?$/', $q) === 1 && str_contains($location, $q);
            if (!$textHit && !$phoneHit && !$zipHit) {
                continue;
            }
        }
        $out[] = $row;
    }
    return $out;
}

function hw_admin_page_list(int $page, int $pages): array
{
    $pages = max(1, $pages);
    $page = max(1, min($page, $pages));
    if ($pages <= 7) {
        return range(1, $pages);
    }

    $start = max(2, $page - 1);
    $end = min($pages - 1, $page + 1);
    if ($page <= 3) {
        $start = 2;
        $end = 4;
    }
    if ($page >= $pages - 2) {
        $start = $pages - 3;
        $end = $pages - 1;
    }

    $items = [1];
    if ($start > 2) {
        $items[] = 0;
    }
    for ($i = $start; $i <= $end; $i++) {
        $items[] = $i;
    }
    if ($end < $pages - 1) {
        $items[] = 0;
    }
    $items[] = $pages;
    return $items;
}

function hw_admin_date(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    $time = strtotime($value);
    return $time ? date('M j, Y g:ia', $time) : '';
}

function hw_admin_multi_html(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    $parts = hw_split_multi($value);
    if (count($parts) <= 1) {
        return hw_e($parts[0] ?? $value);
    }
    $html = '<ul class="admin-multi">';
    foreach ($parts as $part) {
        $html .= '<li>' . hw_e($part) . '</li>';
    }
    return $html . '</ul>';
}

function hw_form_display_multi(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    $parts = hw_split_multi($value);
    if (count($parts) <= 1) {
        return $parts[0] ?? $value;
    }
    return implode("\n", $parts);
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
        if (count($wanted) >= HW_ADMIN_PER_PAGE) {
            break;
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

function hw_delete_records(string $path, array $ids): int
{
    if ($path !== HW_LEADS_FILE && $path !== HW_MESSAGES_FILE) {
        return 0;
    }

    $wanted = [];
    foreach ($ids as $id) {
        $id = (string) $id;
        if (preg_match('/^[a-f0-9]{16}$/', $id)) {
            $wanted[$id] = true;
        }
    }
    if ($wanted === []) {
        return 0;
    }

    $data = hw_read_json($path);
    $kept = [];
    $removed = 0;
    foreach ($data as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rowId = (string) ($row['id'] ?? '');
        if ($rowId !== '' && isset($wanted[$rowId])) {
            $removed++;
            continue;
        }
        $kept[] = $row;
    }

    if ($removed === 0) {
        return 0;
    }

    return hw_write_json($path, array_values($kept)) ? $removed : 0;
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
