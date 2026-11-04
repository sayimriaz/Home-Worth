<?php

declare(strict_types=1);

if (!defined('HW_APP')) {
    http_response_code(403);
    exit;
}

function hw_boot_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = hw_is_https();
    $sessionPath = HW_ROOT . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0755, true);
    }
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', (string) HW_SESSION_IDLE);
    session_name('HWSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => hw_cookie_path(),
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();

    if (!isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = time();
        $_SESSION['last_active'] = time();
        session_regenerate_id(true);
    }

    if (time() - (int) $_SESSION['last_active'] > HW_SESSION_IDLE) {
        hw_logout();
        session_start();
        $_SESSION['created_at'] = time();
    }

    $_SESSION['last_active'] = time();

    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    if (!isset($_SESSION['form_ready_at'])) {
        $_SESSION['form_ready_at'] = time();
    }
}

function hw_send_security_headers(): void
{
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('X-XSS-Protection: 0');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'self'; object-src 'none'");
    if (hw_is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    if (is_string($requestPath) && hw_is_private_path($requestPath)) {
        header('X-Robots-Tag: noindex, nofollow, noarchive');
    }
}

function hw_is_private_path(string $path): bool
{
    if (preg_match('#/(admin|handlers|includes|storage)(/|$)#', $path)) {
        return true;
    }
    $slug = function_exists('hw_admin_slug') ? hw_admin_slug() : HW_ADMIN_SLUG_DEFAULT;
    return (bool) preg_match('#/' . preg_quote($slug, '#') . '(/|$)#', $path);
}

function hw_send_robots(string $robots): void
{
    header('X-Robots-Tag: ' . $robots);
}

function hw_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    return isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';
}

function hw_app_base(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = str_replace('\\', '/', dirname($script));
    $leaf = basename($dir);
    $strip = ['admin', 'handlers'];
    if (function_exists('hw_admin_slug')) {
        $strip[] = hw_admin_slug();
    } else {
        $strip[] = HW_ADMIN_SLUG_DEFAULT;
    }
    if (in_array($leaf, $strip, true)) {
        $dir = str_replace('\\', '/', dirname($dir));
    }
    $dir = rtrim($dir, '/');
    if ($dir === '' || $dir === '.' || $dir === '\\') {
        return '';
    }
    return $dir;
}

function hw_cookie_path(): string
{
    $base = hw_app_base();
    return $base === '' ? '/' : $base . '/';
}

function hw_base_url(): string
{
    return hw_app_base();
}

function hw_url(string $path = ''): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $base = hw_base_url();
    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $path;
}

function hw_admin_url(string $page = ''): string
{
    $slug = function_exists('hw_admin_slug') ? hw_admin_slug() : HW_ADMIN_SLUG_DEFAULT;
    $page = strtolower(trim(str_replace('\\', '/', $page), '/'));
    $page = (string) preg_replace('/\.php$/', '', $page);
    if ($page === '' || $page === 'index') {
        return hw_url($slug);
    }
    return hw_url($slug . '/' . $page);
}

function hw_origin(): string
{
    $host = hw_clean_header((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        $host = 'homeworthofficial.com';
    }
    return (hw_is_https() ? 'https://' : 'http://') . $host;
}

function hw_abs_url(string $path = ''): string
{
    return hw_origin() . hw_url($path);
}

function hw_admin_abs_url(string $page = ''): string
{
    return hw_origin() . hw_admin_url($page);
}

function hw_icon(string $name): string
{
    $icons = [
        'house' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 3.2 3 11h2v9h6v-6h2v6h6v-9h2L12 3.2z"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1zm8 7 7.5-6h-15L12 13zm0-2L4.8 7h14.4L12 11z"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M19.1 12.7a7.6 7.6 0 0 0 .1-1.4 7.6 7.6 0 0 0-.1-1.4l2-1.6-1.9-3.3-2.4 1a7.4 7.4 0 0 0-2.4-1.4l-.4-2.6h-3.8l-.4 2.6a7.4 7.4 0 0 0-2.4 1.4l-2.4-1-1.9 3.3 2 1.6a7.6 7.6 0 0 0-.1 1.4 7.6 7.6 0 0 0 .1 1.4l-2 1.6 1.9 3.3 2.4-1a7.4 7.4 0 0 0 2.4 1.4l.4 2.6h3.8l.4-2.6a7.4 7.4 0 0 0 2.4-1.4l2.4 1 1.9-3.3-2-1.6zM12 15.5A3.5 3.5 0 1 1 15.5 12 3.5 3.5 0 0 1 12 15.5z"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M10 4h8v16h-8v-2h6V6h-6V4zm-1.8 5.2L6.6 11H14v2H6.6l1.6 1.8L6.8 16 3 12l3.8-4 1.4 1.2z"/></svg>',
        'search' => '<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M10 3a7 7 0 0 1 5.5 11.3l4.1 4.1-1.4 1.4-4.1-4.1A7 7 0 1 1 10 3zm0 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10z"/></svg>',
        'view' => '<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M12 5c5 0 9.3 3.1 11 7-1.7 3.9-6 7-11 7S2.7 15.9 1 12c1.7-3.9 6-7 11-7zm0 2c-3.9 0-7.2 2.3-8.8 5 1.6 2.7 4.9 5 8.8 5s7.2-2.3 8.8-5C19.2 9.3 15.9 7 12 7zm0 2.5A2.5 2.5 0 1 1 9.5 12 2.5 2.5 0 0 1 12 9.5z"/></svg>',
        'download' => '<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M11 3h2v10.2l3.2-3.2 1.4 1.4L12 17.2 6.4 11.4l1.4-1.4L11 13.2V3zM4 19h16v2H4z"/></svg>',
        'trash' => '<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v9h-2V9zm4 0h2v9h-2V9zM7 9h2v9H7V9zM5 21h14l-1-11H6L5 21z"/></svg>',
        'export' => '<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M5 3h10l4 4v14H5V3zm10 1.8V8h3.2L15 4.8zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>',
        'close' => '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path fill="currentColor" d="M6.4 5 5 6.4 10.6 12 5 17.6 6.4 19 12 13.4 17.6 19 19 17.6 13.4 12 19 6.4 17.6 5 12 10.6 6.4 5z"/></svg>',
    ];
    return $icons[$name] ?? '';
}

function hw_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function hw_csrf_token(): string
{
    return (string) ($_SESSION['csrf'] ?? '');
}

function hw_csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . hw_e(hw_csrf_token()) . '">';
}

function hw_verify_csrf(?string $token): bool
{
    $session = (string) ($_SESSION['csrf'] ?? '');
    return $session !== '' && is_string($token) && hash_equals($session, $token);
}

function hw_rotate_csrf(): void
{
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function hw_client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function hw_ip_hash(): string
{
    return hash('sha256', hw_client_ip() . (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
}

function hw_require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit;
    }
}

function hw_same_origin(): bool
{
    $host = hw_norm_host((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return false;
    }

    $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $check = $origin !== '' ? $origin : $referer;
    if ($check === '') {
        return true;
    }

    $parts = parse_url($check);
    $checkHost = hw_norm_host((string) ($parts['host'] ?? ''));
    $port = (int) ($parts['port'] ?? 0);
    if ($port && $port !== 80 && $port !== 443) {
        $checkHost .= ':' . $port;
    }

    return hash_equals($host, $checkHost);
}

function hw_norm_host(string $host): string
{
    $host = strtolower($host);
    if (strpos($host, 'www.') === 0) {
        $host = substr($host, 4);
    }
    return (string) preg_replace('/:(80|443)$/', '', $host);
}

function hw_form_stamp(): string
{
    if (empty($_SESSION['form_key'])) {
        $_SESSION['form_key'] = bin2hex(random_bytes(32));
    }
    $issued = (string) time();
    return $issued . '.' . hash_hmac('sha256', $issued, (string) $_SESSION['form_key']);
}

function hw_verify_form_stamp(?string $stamp): bool
{
    if (!is_string($stamp) || !str_contains($stamp, '.')) {
        return false;
    }

    [$issued, $sig] = explode('.', $stamp, 2);
    if ($issued === '' || $sig === '' || !ctype_digit($issued) || empty($_SESSION['form_key'])) {
        return false;
    }

    $issuedAt = (int) $issued;
    $age = time() - $issuedAt;
    if ($age < 0 || $age > HW_FORM_MAX_AGE) {
        return false;
    }

    $expected = hash_hmac('sha256', (string) $issuedAt, (string) $_SESSION['form_key']);
    return hash_equals($expected, $sig);
}

function hw_form_shield_fields(): string
{
    return hw_csrf_field()
        . '<div class="hp" aria-hidden="true">'
        . '<label>Leave blank<input type="text" name="hw_hp_a" tabindex="-1" autocomplete="off"></label>'
        . '<label>Leave blank<input type="text" name="hw_hp_b" tabindex="-1" autocomplete="off"></label>'
        . '<label>Leave blank<input type="text" name="hw_hp_c" tabindex="-1" autocomplete="off"></label>'
        . '</div>'
        . '<input type="hidden" name="form_stamp" value="' . hw_e(hw_form_stamp()) . '">'
        . '<input type="hidden" name="form_js" value="ok">';
}

function hw_honeypot_tripped(): bool
{
    foreach (['hw_hp_a', 'hw_hp_b', 'hw_hp_c'] as $field) {
        if (trim((string) ($_POST[$field] ?? '')) !== '') {
            return true;
        }
    }
    return false;
}

function hw_verify_js_proof(): bool
{
    return ($_POST['form_js'] ?? '') === 'ok';
}

function hw_empty_client(): bool
{
    $agent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    return $agent === '' || strlen($agent) < 10;
}

function hw_form_blocked(): bool
{
    return !hw_same_origin()
        || !hw_verify_csrf($_POST['csrf'] ?? null)
        || hw_honeypot_tripped()
        || !hw_verify_form_stamp($_POST['form_stamp'] ?? null)
        || hw_empty_client();
}

function hw_form_block_reason(): string
{
    if (hw_honeypot_tripped()) {
        return 'Please try again.';
    }
    if (!hw_verify_csrf($_POST['csrf'] ?? null) || !hw_verify_form_stamp($_POST['form_stamp'] ?? null)) {
        return 'Your session expired. Submit the form again.';
    }
    if (!hw_same_origin() || hw_empty_client()) {
        return 'Please submit the form from this website.';
    }
    return 'Please try again.';
}

function hw_is_disposable_email(string $email): bool
{
    $domain = strtolower((string) substr((string) strrchr($email, '@'), 1));
    if ($domain === '') {
        return true;
    }

    $blocked = [
        'mailinator.com', 'guerrillamail.com', 'sharklasers.com', 'guerrillamailblock.com',
        'grr.la', 'pokemail.net', 'spam4.me', 'tempmail.com', 'temp-mail.org',
        '10minutemail.com', '10minutemail.net', 'yopmail.com', 'trashmail.com',
        'getnada.com', 'emailondeck.com', 'discard.email', 'fakeinbox.com',
        'maildrop.cc', 'moakt.com', 'throwaway.email', 'trash-mail.com',
    ];

    return in_array($domain, $blocked, true);
}

function hw_text_has_spam(string ...$parts): bool
{
    $text = strtolower(implode("\n", $parts));
    $links = preg_match_all('/https?:\/\/|www\./i', $text);
    if ($links > 2) {
        return true;
    }

    if (preg_match('/<\s*script|\[url=|\[link/i', $text)) {
        return true;
    }

    $needles = [
        'viagra', 'cialis', 'casino', 'crypto investment', 'seo backlink',
        'buy followers', 'porn', 'xxx', 'bitcoin wallet', 'forex robot',
    ];
    foreach ($needles as $needle) {
        if (str_contains($text, $needle)) {
            return true;
        }
    }

    return false;
}

function hw_too_fast(): bool
{
    $ready = (int) ($_SESSION['form_ready_at'] ?? 0);
    return $ready === 0 || (time() - $ready) < HW_FORM_MIN_SECONDS;
}

function hw_login_ip_key(): string
{
    return hash('sha256', 'hw-login|' . hw_client_ip());
}

function hw_login_lock_data(): array
{
    $data = hw_read_json(HW_LOGIN_LOCK_FILE);
    $now = time();
    $fresh = [];

    foreach ($data as $key => $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $until = (int) ($entry['until'] ?? 0);
        $fails = (int) ($entry['fails'] ?? 0);
        $locks = (int) ($entry['locks'] ?? 0);
        if ($until > $now || $fails > 0 || $locks > 0) {
            $fresh[$key] = [
                'fails' => $fails,
                'locks' => $locks,
                'until' => $until,
            ];
        }
    }

    return $fresh;
}

function hw_login_state(): array
{
    $data = hw_login_lock_data();
    $key = hw_login_ip_key();
    return $data[$key] ?? ['fails' => 0, 'locks' => 0, 'until' => 0];
}

function hw_login_save_state(array $state): void
{
    $data = hw_login_lock_data();
    $data[hw_login_ip_key()] = [
        'fails' => max(0, (int) ($state['fails'] ?? 0)),
        'locks' => max(0, (int) ($state['locks'] ?? 0)),
        'until' => max(0, (int) ($state['until'] ?? 0)),
    ];
    hw_write_json(HW_LOGIN_LOCK_FILE, $data);
}

function hw_login_wait_message(int $seconds): string
{
    if ($seconds >= 3600) {
        $hours = (int) ceil($seconds / 3600);
        return 'Too many failed attempts. Try again in ' . $hours . ' hour' . ($hours === 1 ? '' : 's') . '.';
    }
    $minutes = max(1, (int) ceil($seconds / 60));
    return 'Too many failed attempts. Try again in ' . $minutes . ' minute' . ($minutes === 1 ? '' : 's') . '.';
}

function hw_login_blocked(): ?string
{
    $until = (int) (hw_login_state()['until'] ?? 0);
    $left = $until - time();
    if ($left <= 0) {
        return null;
    }
    return hw_login_wait_message($left);
}

function hw_login_fail(): ?string
{
    $state = hw_login_state();
    $now = time();
    if ((int) $state['until'] > $now) {
        return hw_login_wait_message((int) $state['until'] - $now);
    }

    $state['until'] = 0;
    $state['fails'] = (int) $state['fails'] + 1;

    if ((int) $state['fails'] < HW_LOGIN_FAILS) {
        hw_login_save_state($state);
        return null;
    }

    $hours = HW_LOGIN_LOCK_HOURS;
    $index = min((int) $state['locks'], count($hours) - 1);
    $state['locks'] = (int) $state['locks'] + 1;
    $state['fails'] = 0;
    $state['until'] = $now + ((int) $hours[$index] * 3600);
    hw_login_save_state($state);

    return hw_login_wait_message((int) $state['until'] - $now);
}

function hw_login_success(): void
{
    $data = hw_login_lock_data();
    unset($data[hw_login_ip_key()]);
    hw_write_json(HW_LOGIN_LOCK_FILE, $data);
}

function hw_rate_state(string $bucket): array
{
    $key = $bucket . ':' . hw_ip_hash();
    $now = time();
    $data = hw_read_json(HW_RATE_FILE);
    $fresh = [];

    foreach ($data as $k => $entry) {
        if (!is_array($entry) || !isset($entry['hits'], $entry['start'])) {
            continue;
        }
        if ($now - (int) $entry['start'] < HW_RATE_WINDOW) {
            $fresh[$k] = $entry;
        }
    }

    $current = $fresh[$key] ?? ['hits' => 0, 'start' => $now];
    if ($now - (int) $current['start'] >= HW_RATE_WINDOW) {
        $current = ['hits' => 0, 'start' => $now];
    }

    return [$key, $fresh, $current];
}

function hw_rate_limited(string $bucket, int $max): bool
{
    [, , $current] = hw_rate_state($bucket);
    return (int) $current['hits'] >= $max;
}

function hw_rate_hit(string $bucket): void
{
    [$key, $fresh, $current] = hw_rate_state($bucket);
    $current['hits'] = (int) $current['hits'] + 1;
    $fresh[$key] = $current;
    hw_write_json(HW_RATE_FILE, $fresh);
}

function hw_clean_text(string $value, int $max): string
{
    $value = trim(str_replace(["\r", "\0"], '', $value));
    $value = preg_replace('/[^\P{C}\n\t]/u', '', $value) ?? '';
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max);
    }
    return substr($value, 0, $max);
}

function hw_clean_header(string $value): string
{
    return trim(str_replace(["\r", "\n", "\0"], '', $value));
}

function hw_validate_name(string $name): bool
{
    return (bool) preg_match('/^[\p{L}\p{M} .\'-]{2,80}$/u', $name);
}

function hw_split_multi(string $value): array
{
    $value = trim(str_replace(["\r\n", "\r"], "\n", $value));
    if ($value === '') {
        return [];
    }
    $parts = preg_split('/[\n,;|\/]+|\s+(?:and|&)\s+/i', $value) ?: [];
    $out = [];
    foreach ($parts as $part) {
        $part = trim((string) $part);
        if ($part !== '') {
            $out[] = $part;
        }
    }
    return $out;
}

function hw_join_multi(array $parts): string
{
    return implode(', ', $parts);
}

function hw_normalize_multi_field(string $value, int $max, int $maxParts = 8): string
{
    $parts = hw_split_multi($value);
    if ($parts === []) {
        return hw_clean_text($value, $max);
    }
    if (count($parts) > $maxParts) {
        $parts = array_slice($parts, 0, $maxParts);
    }
    return hw_clean_text(hw_join_multi($parts), $max);
}

function hw_validate_phone_segment(string $segment, int $minDigits = 10): bool
{
    $segment = trim($segment);
    if ($segment === '') {
        return false;
    }
    $digits = preg_replace('/\D+/', '', $segment) ?? '';
    if (strlen($digits) < $minDigits || strlen($digits) > 15) {
        return false;
    }
    return (bool) preg_match('/^[0-9+\-\s().xXext]+$/', $segment);
}

function hw_validate_phone(string $phone): bool
{
    $phone = trim($phone);
    if ($phone === '') {
        return true;
    }
    $parts = hw_split_multi($phone);
    if ($parts === []) {
        $parts = [$phone];
    }
    $minDigits = count($parts) > 1 ? 7 : 10;
    foreach ($parts as $part) {
        if (!hw_validate_phone_segment($part, $minDigits)) {
            return false;
        }
    }
    return true;
}

function hw_validate_email(string $email): bool
{
    $email = trim($email);
    if ($email === '' || strlen($email) > 120) {
        return false;
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return (bool) preg_match(
        "/^[A-Za-z0-9.!#\$%&'*+\/=?^_`{|}~-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)+$/",
        $email
    );
}

function hw_in_list(string $value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}

function hw_require_admin(): void
{
    if (empty($_SESSION['admin_ok']) || $_SESSION['admin_ok'] !== true) {
        header('Location: ' . hw_admin_url());
        exit;
    }
}

function hw_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'secure' => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => 'Strict',
        ]);
    }
    session_destroy();
}

function hw_flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return $value;
    }
    $out = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return is_string($out) ? $out : null;
}

function hw_flash_old(string $form, ?array $data = null): array
{
    $key = 'old_' . $form;
    if ($data !== null) {
        $_SESSION['flash'][$key] = $data;
        return $data;
    }
    $out = $_SESSION['flash'][$key] ?? [];
    unset($_SESSION['flash'][$key]);
    return is_array($out) ? $out : [];
}

function hw_old(array $old, string $key): string
{
    $value = $old[$key] ?? '';
    return is_string($value) ? $value : '';
}

function hw_field_error(array $old, string $key): string
{
    $errors = $old['_errors'] ?? null;
    if (!is_array($errors)) {
        return '';
    }
    $msg = $errors[$key] ?? '';
    return is_string($msg) ? $msg : '';
}

function hw_redirect(string $path): void
{
    header('Location: ' . hw_url($path));
    exit;
}
