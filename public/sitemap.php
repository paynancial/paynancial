<?php
/**
 * Dynamically generated sitemap.xml for public marketing routes.
 * Requested via public/.htaccess rewrite of /sitemap.xml.
 *
 * Lists every indexable public page, with <lastmod> taken from the page
 * template's modification time. Deliberately excluded: utility pages
 * (login, password reset, signup verification), payment-link pages,
 * dashboards. Business Services pages are added only when eligible
 * (bs_sitemap_paths(): indexable, substantial, confirmed service, not a
 * duplicate; jurisdiction pages only once approved).
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
    'developers/api-reference'     => 'developers/api-reference.php',
    'developers/authentication'    => 'developers/authentication.php',
    'developers/webhooks'          => 'developers/webhooks.php',
    'developers/sdks'              => 'developers/sdks.php',
    'developers/integration-guide' => 'developers/integration-guide.php',
    'sandbox'                      => 'sandbox.php',
    'agentic-ai'                   => 'agenticai.php',
    'technology'                   => 'technology.php',
    'security'                     => 'security-compliance.php',
    'trust'                        => 'trust.php',
    'ai-governance'                => 'ai-governance.php',
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
require_once __DIR__ . '/../includes/solutions-data.php';
$industryMtime = max((int) filemtime($pages . 'solutions/industry.php'), (int) filemtime(__DIR__ . '/../includes/solutions-data.php'));
foreach (array_keys(sol_industries()) as $slug) {
    echo '  <url><loc>' . htmlspecialchars(site_url('solutions/' . $slug)) . '</loc>'
        . '<lastmod>' . gmdate('Y-m-d', $industryMtime) . '</lastmod></url>' . "\n";
}
require_once __DIR__ . '/../includes/business-services.php';
$bsPages = __DIR__ . '/../pages/business-services/';
foreach (bs_sitemap_paths() as $path) {
    $parts = explode('/', trim($path, '/'));
    $template = match (true) {
        count($parts) === 1                                       => 'index.php',
        ($parts[1] ?? '') === 'global-incorporation'              => 'global-incorporation.php',
        ($parts[1] ?? '') === 'jurisdictions' && count($parts) === 2 => 'jurisdictions.php',
        ($parts[1] ?? '') === 'jurisdictions'                     => 'jurisdiction.php',
        default                                                   => 'service.php',
    };
    // Content lives in the data file as well as the template: use the newer.
    $mtime = max((int) filemtime($bsPages . $template), (int) filemtime(__DIR__ . '/../includes/business-services.php'));
    echo '  <url><loc>' . htmlspecialchars(site_url(ltrim($path, '/'))) . '</loc>'
        . '<lastmod>' . gmdate('Y-m-d', $mtime) . '</lastmod></url>' . "\n";
}
echo '</urlset>';
