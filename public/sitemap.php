<?php
/**
 * Dynamically generated sitemap.xml for public marketing routes.
 * Requested via public/.htaccess rewrite of /sitemap.xml.
 *
 * Lists every indexable public page, with <lastmod> taken from the page
 * template's modification time. Deliberately excluded: utility pages
 * (login, password reset, signup verification), payment-link pages,
 * dashboards, and the /business-services pages (linked from the header;
 * sitemap inclusion pending approval; jurisdiction pages are noindex).
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$pages = __DIR__ . '/../pages/';
$entries = [
    ''                             => 'home.php',
    'products'                     => 'products.php',
    'products/payment-gateway'     => 'product-detail.php',
    'products/payment-links'       => 'product-detail.php',
    'products/payment-collection'  => 'product-detail.php',
    'products/payouts'             => 'product-detail.php',
    'products/payment-analytics'   => 'product-detail.php',
    'solutions'                    => 'solutions.php',
    'pricing'                      => 'pricing.php',
    'developers'                   => 'developers.php',
    'agentic-ai'                   => 'agenticai.php',
    'technology'                   => 'technology.php',
    'security'                     => 'security-compliance.php',
    'trust'                        => 'trust.php',
    'support'                      => 'support.php',
    'blog'                         => 'blog.php',
    'about'                        => 'about.php',
    'leadership'                   => 'leadership.php',
    'careers'                      => 'careers.php',
    'partners'                     => 'partners.php',
    'partner/register'             => 'partner-register.php',
    'contact'                      => 'contact.php',
    'signup'                       => 'signup.php',
    'legal/privacy-policy'         => 'legal.php',
    'legal/terms-conditions'       => 'legal.php',
    'legal/refund-policy'          => 'legal.php',
    'legal/cookie-policy'          => 'legal.php',
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($entries as $route => $template) {
    $file = $pages . $template;
    if (!is_file($file)) {
        continue; // never list a URL whose template is missing
    }
    echo '  <url><loc>' . htmlspecialchars(site_url($route)) . '</loc>'
        . '<lastmod>' . gmdate('Y-m-d', (int) filemtime($file)) . '</lastmod></url>' . "\n";
}
echo '</urlset>';
