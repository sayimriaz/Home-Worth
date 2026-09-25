<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$pages = [
    ['path' => '', 'file' => HW_ROOT . '/index.php', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['path' => 'contact', 'file' => HW_ROOT . '/contact.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['path' => 'privacy-policy', 'file' => HW_ROOT . '/privacy.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $page) {
    $lastmod = is_file($page['file']) ? date('Y-m-d', (int) filemtime($page['file'])) : date('Y-m-d');
    echo '  <url>' . "\n";
    echo '    <loc>' . hw_e(hw_abs_url($page['path'])) . '</loc>' . "\n";
    echo '    <lastmod>' . hw_e($lastmod) . '</lastmod>' . "\n";
    echo '    <changefreq>' . hw_e($page['changefreq']) . '</changefreq>' . "\n";
    echo '    <priority>' . hw_e($page['priority']) . '</priority>' . "\n";
    echo '  </url>' . "\n";
}
echo '</urlset>';
