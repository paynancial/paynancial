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
    'products/refunds'             => 'products/capability.php',
    'products/settlements'         => 'products/capability.php',
    'products/reconciliation'      => 'products/capability.php',
    'products/upi-payments'        => 'products/capability.php',
    'products/recurring-payments'  => 'products/capability.php',
    'products/subscription-billing' => 'products/capability.php',
    'products/bulk-payouts'        => 'products/capability.php',
    'products/vendor-payments'     => 'products/capability.php',
    'products/employee-payments'   => 'products/capability.php',
    'products/partner-payments'    => 'products/capability.php',
    'products/mis-reports'         => 'products/capability.php',
    'products/chargebacks'         => 'products/capability.php',
    'products/international-payments' => 'products/capability.php',
    'products/invoice-management'  => 'products/capability.php',
    'products/expense-management'  => 'products/capability.php',
    'products/payment-pages'       => 'products/capability.php',
    'products/embedded-payments' => 'products/capability.php',
    'products/embedded-payouts' => 'products/capability.php',
    'products/embedded-billing' => 'products/capability.php',
    'products/wallet-infrastructure' => 'products/capability.php',
    'products/split-payments' => 'products/capability.php',
    'products/white-label-payments' => 'products/capability.php',
    'pay-and-move-money'           => 'products/category.php',
    'financial-operations'         => 'products/category.php',
    'embedded-finance'             => 'products/category.php',
    'products/accept-and-collect'  => 'products/category.php',
    'solutions'                    => 'solutions.php',
    'pricing'                      => 'pricing.php',
    'developers'                   => 'developers.php',
    'developers/api-reference'     => 'developers/api-reference.php',
    'developers/payment-apis'      => 'developers/payment-apis.php',
    'developers/payout-apis'       => 'developers/payout-apis.php',
    'developers/authentication'    => 'developers/authentication.php',
    'developers/webhooks'          => 'developers/webhooks.php',
    'developers/sdks'              => 'developers/sdks.php',
    'developers/integration-guide' => 'developers/integration-guide.php',
    'sandbox'                      => 'sandbox.php',
    'agentic-ai'                   => 'agenticai.php',
    'agentic-ai/financial-agents'  => 'agentic/financial-agents.php',
    'agentic-ai/payment-orchestration' => 'agentic/payment-orchestration.php',
    'technology'                   => 'technology.php',
    'security'                     => 'security-compliance.php',
    'trust'                        => 'trust.php',
    'ai-governance'                => 'ai-governance.php',
    'ai-intelligence'              => 'ai-intelligence.php',
    'ai-intelligence/paynancial-ai' => 'products/capability.php',
    'ai-intelligence/fraud-detection' => 'products/capability.php',
    'ai-intelligence/reconciliation' => 'products/capability.php',
    'ai-intelligence/financial-assistant' => 'products/capability.php',
    'ai-intelligence/cash-flow-intelligence' => 'products/capability.php',
    'ai-intelligence/revenue-forecasting' => 'products/capability.php',
    'support'                      => 'support.php',
    'resources'                    => 'resources.php',
    'resources/faqs'               => 'resources-faqs.php',
    'blog'                         => 'blog.php',
    'about'                        => 'about.php',
    'leadership'                   => 'leadership.php',
    'careers'                      => 'careers.php',
    'partner-program'              => 'partner-program.php',
    'grievance-redressal'          => 'grievance-redressal.php',
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
require_once __DIR__ . '/../includes/content-governance.php';
foreach ($entries as $route => $template) {
    if (!gov_in_sitemap('/' . $route)) {
        continue; // governed page not approved for the sitemap
    }
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
// Blog: only articles and category pages the publishing gate has approved
// for the sitemap (none until editorial, SEO/AEO and indexing approval).
require_once __DIR__ . '/../includes/blog.php';
foreach (blog_live() as $slug => $a) {
    if (gov_in_sitemap(blog_url($slug))) {
        echo '  <url><loc>' . htmlspecialchars(site_url(ltrim(blog_url($slug), '/'))) . '</loc>'
            . '<lastmod>' . htmlspecialchars($a['updated']) . '</lastmod></url>' . "\n";
    }
}
foreach (array_keys(blog_category_pages()) as $cat) {
    if (gov_in_sitemap(blog_category_url($cat))) {
        echo '  <url><loc>' . htmlspecialchars(site_url(ltrim(blog_category_url($cat), '/'))) . '</loc></url>' . "\n";
    }
}
echo '</urlset>';
