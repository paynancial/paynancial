<?php
/**
 * Dynamically generated sitemap.xml for public marketing routes.
 * Requested via public/.htaccess rewrite of /sitemap.xml.
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$routes = ['', 'about', 'solutions', 'products', 'pricing', 'developers', 'partners', 'support', 'contact', 'careers', 'security', 'blog'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($routes as $route) {
    echo '  <url><loc>' . htmlspecialchars(site_url($route)) . '</loc><changefreq>weekly</changefreq></url>' . "\n";
}
echo '</urlset>';
