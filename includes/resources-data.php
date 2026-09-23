<?php
/**
 * Resources — data for the Resources hub (/resources) and the FAQ hub
 * (/resources/faqs). The FAQ directory is built from the same FAQ sources
 * the pages themselves render (faq-data.php, solutions-data.php,
 * business-services.php), so questions can never drift out of sync. The
 * FAQ hub shows each topic question as a link to the page that answers it
 * rather than copying the answer.
 */

declare(strict_types=1);

require_once __DIR__ . '/faq-data.php';
require_once __DIR__ . '/solutions-data.php';
require_once __DIR__ . '/business-services.php';
require_once __DIR__ . '/product-categories.php';
require_once __DIR__ . '/ai-intelligence.php';

/**
 * FAQ directory: topic groups, each a list of pages with their questions.
 * Page entry: [title, url, anchor, questions (list of strings)].
 */
function res_faq_directory(): array
{
    $q = fn (string $key) => array_map(fn ($f) => $f[0], faq_set($key));
    $products = [
        'payment-gateway' => 'Payment Gateway', 'payment-links' => 'Payment Links',
        'payment-collection' => 'Payment Collection', 'payouts' => 'Payouts', 'payment-analytics' => 'Payment Analytics',
        'refunds' => 'Refunds', 'settlements' => 'Settlements', 'reconciliation' => 'Reconciliation', 'upi-payments' => 'UPI Payments',
        'bulk-payouts' => 'Bulk Payouts', 'vendor-payments' => 'Vendor Payments', 'employee-payments' => 'Employee Payments', 'partner-payments' => 'Partner Payments',
        'mis-reports' => 'MIS & Reports', 'chargebacks' => 'Chargebacks', 'international-payments' => 'International Payments', 'invoice-management' => 'Invoice Management', 'expense-management' => 'Expense Management',
        'recurring-payments' => 'Recurring Payments', 'subscription-billing' => 'Subscription Billing', 'payment-pages' => 'Payment Pages', 'embedded-payments' => 'Embedded Payments', 'embedded-payouts' => 'Embedded Payouts', 'embedded-billing' => 'Embedded Billing', 'wallet-infrastructure' => 'Wallet Infrastructure', 'split-payments' => 'Split Payments', 'white-label-payments' => 'White-Label Payments',
    ];
    $productPages = [];
    foreach (cat_pages() as $slug => $c) {
        if ($slug === 'ai-and-intelligence') {
            continue; // now the /ai-intelligence hub, listed under AI below
        }
        $productPages[] = [$c['name'], cat_url($slug), 'faq', $q('category:' . $slug)];
    }
    foreach ($products as $slug => $name) {
        $productPages[] = [$name, '/products/' . $slug, 'faq', $q('product:' . $slug)];
    }
    $industryPages = [];
    foreach (sol_industries() as $slug => $ind) {
        $industryPages[] = [$ind['name'], sol_url($slug), 'faq', array_map(fn ($f) => $f[0], $ind['faqs'])];
    }
    $bsPages = [];
    foreach (bs_services() as $slug => $svc) {
        if (!empty($svc['faqs'])) {
            $bsPages[] = [$svc['short'], bs_url($slug), 'faqs', array_map(fn ($f) => $f[0], $svc['faqs'])];
        }
    }

    return [
        ['id' => 'products', 'label' => 'Payments & products', 'pages' => $productPages],
        ['id' => 'developers', 'label' => 'Developers', 'pages' => [
            ['Developer Hub', '/developers', 'support', $q('developers')],
            ['API Reference', '/developers/api-reference', 'faq', $q('api-reference')],
            ['Payment APIs', '/developers/payment-apis', 'faq', $q('payment-apis')],
            ['Payout APIs', '/developers/payout-apis', 'faq', $q('payout-apis')],
            ['Authentication', '/developers/authentication', 'faq', $q('authentication')],
            ['Webhooks', '/developers/webhooks', 'faq', $q('webhooks')],
            ['SDKs', '/developers/sdks', 'faq', $q('sdks')],
            ['Integration Guide', '/developers/integration-guide', 'faq', $q('integration-guide')],
            ['Sandbox', '/sandbox', 'faq', $q('sandbox')],
        ]],
        ['id' => 'solutions', 'label' => 'Industry solutions', 'pages' => $industryPages],
        ['id' => 'ai-intelligence', 'label' => 'AI & Intelligence', 'pages' => array_merge(
            [['AI & Intelligence', ai_url(), 'questions', $q('ai:hub')]],
            array_values(array_map(fn ($slug, $c) => [$c['name'], $c['path'], 'faq', $q('ai:' . $slug)], array_keys(ai_children()), ai_children()))
        )],
        ['id' => 'agentic-ai', 'label' => 'Agentic AI & governance', 'pages' => [
            ['Agentic AI in Finance', '/agentic-ai', 'faqs', $q('agentic-ai')],
            ['AI Financial Agents', '/agentic-ai/financial-agents', 'faq', $q('financial-agents')],
            ['AI Payment Orchestration', '/agentic-ai/payment-orchestration', 'faq', $q('payment-orchestration')],
            ['AI Governance', '/ai-governance', 'faq', $q('ai-governance')],
        ]],
        ['id' => 'business-services', 'label' => 'Business Services', 'pages' => $bsPages],
    ];
}

/** Resources hub sections: [id, kicker, title, intro, links [title, text, href]]. */
function res_hub_sections(): array
{
    $industries = array_map(fn ($slug, $ind) => [$ind['name'], $ind['lead'], sol_url($slug)], array_keys(sol_industries()), sol_industries());
    return [
        ['help', 'Help & answers', 'Get an answer fast.', 'Start here if you have a question about your account, a payment or an integration.', [
            ['FAQs', 'Answers to common questions, plus every question answered across the site, by topic.', '/resources/faqs'],
            ['Support Center', 'Contact options and help with an existing account or integration.', '/support'],
            ['Contact', 'Talk to sales or support, or email hello@paynancial.com.', '/contact'],
        ]],
        ['build', 'Build', 'Developer guides and documentation.', 'Everything a developer needs to integrate Paynancial, from first key to go-live.', [
            ['Developer Hub', 'Overview of the API, SDKs, webhooks and sandbox.', '/developers'],
            ['Integration Guide', 'Seven steps from sandbox key to first live payment.', '/developers/integration-guide'],
            ['API Reference', 'Base URL, resources, idempotency and error codes.', '/developers/api-reference'],
            ['Sandbox', 'Test your integration with no real funds involved.', '/sandbox'],
        ]],
        ['learn', 'Learn', 'Guides to agentic AI in finance.', 'Plain-language explainers for teams deciding what to trust AI with.', [
            ['Agentic AI in Finance', 'What agentic AI is, and how it differs from generative AI and automation.', '/agentic-ai'],
            ['AI Financial Agents', 'Where agents work in financial operations, and what stays with people.', '/agentic-ai/financial-agents'],
            ['AI Payment Orchestration', 'How agent-initiated payments stay safe: idempotency, errors, webhooks and limits.', '/agentic-ai/payment-orchestration'],
            ['Financial Technology in the Agentic AI Era', 'How financial technology is changing as software starts to act.', '/technology'],
        ]],
        ['industries', 'By industry', 'Payments for your industry.', 'How businesses in each industry collect and move money with Paynancial.', $industries],
        ['trust', 'Trust & legal', 'Security, governance and policies.', 'How Paynancial approaches security and AI governance, and the policies that apply to you.', [
            ['Trust Center', 'Security foundations, privacy, continuity and how we label what is confirmed.', '/trust'],
            ['AI Governance', 'Permissions, policy limits, human oversight and auditability for AI.', '/ai-governance'],
            ['Security & Compliance', 'Company and entity details and our security approach.', '/security'],
            ['Privacy Policy', 'How Paynancial collects and uses personal data.', '/legal/privacy-policy'],
            ['Terms & Conditions', 'The terms that apply to using Paynancial.', '/legal/terms-conditions'],
            ['Refund Policy', 'How refunds are handled.', '/legal/refund-policy'],
        ]],
        ['more', 'More from Paynancial', 'Insights and services.', '', [
            ['Blog / Insights', 'Articles on payment technology and product updates.', '/blog'],
            ['Business Services', 'Company incorporation, registrations, trademarks and compliance.', '/business-services'],
            ['Pricing', 'How Paynancial pricing works.', '/pricing'],
        ]],
    ];
}
