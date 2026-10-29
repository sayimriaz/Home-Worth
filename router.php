<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($uri) ? rawurldecode($uri) : '/';

if (preg_match('#^/(storage|includes)(/|$)#', $path)) {
    http_response_code(403);
    exit;
}

if (preg_match('#^/admin(/|$)#', $path)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return true;
}

if (preg_match('#^/handlers(/|$)#', $path) && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$map = [
    '/' => '/index.php',
    '/contact' => '/contact.php',
    '/contact/' => '/contact.php',
    '/privacy' => '/privacy.php',
    '/privacy/' => '/privacy.php',
    '/privacy-policy' => '/privacy.php',
    '/privacy-policy/' => '/privacy.php',
    '/thank-you' => '/thank-you.php',
    '/thank-you/' => '/thank-you.php',
    '/sitemap.xml' => '/sitemap.php',
];

if (isset($map[$path])) {
    require __DIR__ . $map[$path];
    return true;
}

$adminSlug = 'bGGIqmm';
$adminFile = __DIR__ . '/storage/data/admin.json';
if (is_file($adminFile)) {
    $adminData = json_decode((string) file_get_contents($adminFile), true);
    $saved = is_array($adminData) ? (string) ($adminData['path'] ?? '') : '';
    if (preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{2,39}$/', $saved)) {
        $adminSlug = $saved;
    }
}

if (preg_match('#^/' . preg_quote($adminSlug, '#') . '/?$#', $path)) {
    require __DIR__ . '/admin/index.php';
    return true;
}
if (preg_match('#^/' . preg_quote($adminSlug, '#') . '/dashboard(?:\\.php)?/?$#', $path)) {
    require __DIR__ . '/admin/dashboard.php';
    return true;
}
if (preg_match('#^/' . preg_quote($adminSlug, '#') . '/logout(?:\\.php)?/?$#', $path)) {
    require __DIR__ . '/admin/logout.php';
    return true;
}
if (preg_match('#^/' . preg_quote($adminSlug, '#') . '/form(?:\\.php)?/?$#', $path)) {
    require __DIR__ . '/admin/form.php';
    return true;
}

$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

http_response_code(404);
require __DIR__ . '/404.php';
return true;
