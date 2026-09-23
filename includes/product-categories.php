<?php
/**
 * Product categories — /products/{category}: the five Products menu
 * columns as standalone pages (Accept & Collect, Pay & Move Money,
 * Financial Operations, AI & Intelligence, Embedded Finance).
 *
 * Every menu item in a category appears on its category page with one of
 * three statuses:
 *   page    — has its own product or capability page (link there);
 *   covered — part of an existing product page (link there);
 *   request — no published product detail yet. The page gives a general,
 *             plain-language definition of the term (not a Paynancial
 *             feature claim) and says to ask the team about availability.
 * Menu items without a page link to their entry here (#anchor).
 *
 * Content rule: Paynancial capabilities are only those already published
 * on the product, capability, Developer and Agentic AI pages. FAQs live in
 * faq-data.php (category:{slug}).
 */

declare(strict_types=1);

require_once __DIR__ . '/faq-data.php';

function cat_slugify(string $label): string
{
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(str_replace('&', 'and', $label))), '-');
}

function cat_url(string $slug, ?string $item = null): string
{
    // The two pillars live at their own top-level URLs.
    if (in_array($slug, ['pay-and-move-money', 'financial-operations'], true)) {
        return '/' . $slug . ($item !== null ? '#' . cat_slugify($item) : '');
    }
    if ($slug === 'ai-and-intelligence') {
        // Moved to its own hub; items have their own pages there.
        $children = ['Paynancial AI' => 'paynancial-ai', 'AI Fraud Detection' => 'fraud-detection', 'AI Reconciliation' => 'reconciliation',
            'AI Financial Assistant' => 'financial-assistant', 'AI Cash-Flow Intelligence' => 'cash-flow-intelligence', 'AI Revenue Forecasting' => 'revenue-forecasting'];
        return '/ai-intelligence' . ($item !== null && isset($children[$item]) ? '/' . $children[$item] : '');
    }
    return '/products/' . $slug . ($item !== null ? '#' . cat_slugify($item) : '');
}

/** Category pages. Item: [label, kind, href (null for request), description]. */
function cat_pages(): array
{
    $ask = 'Ask our team about availability for your business.';
    return [
        'accept-and-collect' => [
            'name'  => 'Accept & Collect',
            'title' => 'Accept & Collect Payments | Gateway, Links, UPI & Collections | Paynancial',
            'description' => 'Every way to get paid with Paynancial: the Payment Gateway for checkout, Payment Links without a website, UPI, and scheduled recurring and bulk collections — with reconciliation behind each one.',
            'h1'    => 'Every way your business gets paid, on one platform.',
            'lead'  => 'Take payments at checkout, get paid by link without a website, accept UPI, and collect recurring or bulk dues on schedule — with every payment reconciled against the order it belongs to.',
            'answer' => 'Accept & Collect is the part of Paynancial that brings money in: the Payment Gateway for cards, UPI, netbanking and wallets at checkout; Payment Links for getting paid without a website; and Smart Collections for recurring and bulk payments on a schedule.',
            'intro' => [
                'Businesses get paid in different moments: a customer checking out on a website, a client paying an invoice sent over WhatsApp, a subscriber renewing every month, a school collecting term fees from hundreds of families. Accept & Collect covers each of those with a product built for it.',
                'All of them share the same foundation — one integration, real-time payment status, webhooks, and a settlement record behind every transaction — so you can start with one and add the others without a second integration.',
            ],
            'choose' => [
                ['You sell on a website or app', 'Payment Gateway', '/products/payment-gateway'],
                ['You want to get paid without a website', 'Payment Links', '/products/payment-links'],
                ['Your customers pay by UPI', 'UPI Payments', '/products/upi-payments'],
                ['You bill on a schedule or collect from many customers', 'Smart Collections', '/products/payment-collection'],
            ],
            'items' => [
                ['Payment Gateway', 'page', '/products/payment-gateway', 'Cards, UPI, netbanking and wallets through one integration, with a hosted or custom checkout and settlement reporting.'],
                ['Payment Links', 'page', '/products/payment-links', 'Shareable links with a fixed or open amount and an expiry date — no website needed.'],
                ['UPI Payments', 'page', '/products/upi-payments', 'UPI alongside cards, netbanking and wallets at checkout.'],
                ['Smart Collections', 'page', '/products/payment-collection', 'Recurring and bulk collection on a schedule, with retries, notifications and automatic reconciliation.'],
                ['Recurring Payments', 'covered', '/products/payment-collection', 'Part of Smart Collections: set up a schedule for subscription or instalment payments.'],
                ['Subscription Billing', 'covered', '/products/payment-collection', 'Part of Smart Collections: collect subscription payments on schedule, with retries for failed attempts.'],
                ['Payment Pages', 'request', null, 'A payment page is a hosted page, such as a donation or event page, where anyone with the link can pay. ' . $ask],
            ],
            'related' => ['pay-and-move-money', 'financial-operations', 'ai-and-intelligence'],
        ],
        'pay-and-move-money' => [
            'pillar' => true,
            'secondary' => ['Explore Payouts', '/products/payouts', 'cta_click'],
            'hero_nodes' => [['Your business', 'Dashboard or API'], ['Payouts', 'Single or batch'], ['Bank accounts', 'and UPI IDs'], ['Vendors · staff · partners', 'Paid and recorded']],
            'flow' => [
                ['Add the beneficiary', 'Save a bank account or UPI ID once and reuse it.'],
                ['Initiate', 'Send a single payout or submit a batch, from the dashboard or API.'],
                ['Processed', 'Paynancial processes each transfer and returns a status.'],
                ['Tracked', 'Follow each payout from initiated to completed, with clear failure reasons.'],
                ['Reported', 'Completed payouts appear in your payout report for reconciliation.'],
            ],
            'dev' => [['Payouts API', 'Create payouts with an idempotency key.', '/developers/api-reference#payouts'], ['Webhooks', 'Payout events as each payout changes state.', '/developers/webhooks'], ['Sandbox', 'Test payouts with no real funds.', '/sandbox'], ['SDKs', 'PHP, JavaScript and Python.', '/developers/sdks']],
            'ai' => [['AI Cash-Flow Intelligence', 'Near-term liquidity forecasts that take money going out into account.', '/ai-intelligence/cash-flow-intelligence'], ['AI Financial Agents', 'Agents that route payouts within limits you set.', '/agentic-ai/financial-agents']],
            'cross' => ['financial-operations', 'Every payout lands in the same reports and reconciliation as your incoming payments.'],
            'name'  => 'Pay & Move Money',
            'title' => 'Pay & Move Money | Payouts, Bulk, Vendor & Partner Payments | Paynancial',
            'description' => 'Send money out with Paynancial Payouts: single or bulk payouts to bank accounts and UPI IDs for vendors, employees and partners, from the dashboard or API, with status tracking and clear failure reasons.',
            'h1'    => 'Move money with confidence — to vendors, staff and partners.',
            'lead'  => 'Send single or bulk payouts to bank accounts and UPI IDs, from the dashboard or straight from your own systems, and follow every payout to completion.',
            'answer' => 'Pay & Move Money is the part of Paynancial that sends money out. Payouts sends funds to bank accounts and UPI IDs — one at a time or in bulk — for vendors, employees, freelancers and partners, from the dashboard or the API, with status tracking and clear reasons when a payout fails.',
            'intro' => [
                'Money going out is as important as money coming in: suppliers to pay, staff and freelancers to settle, partners to share revenue with. Doing it from a bank portal one transfer at a time does not scale, and it leaves no clean record.',
                'Payouts brings it into the same platform as your collections. Save beneficiaries once, pay them singly or in a batch, and see every payout from initiated to completed — with the idempotency keys and webhooks that make automated payouts safe.',
            ],
            'choose' => [
                ['You pay one beneficiary at a time', 'Payouts', '/products/payouts'],
                ['You pay many people at once', 'Bulk Payouts', '/products/bulk-payouts'],
                ['You pay to UPI IDs', 'UPI Payments', '/products/upi-payments'],
                ['You want to automate payouts from your systems', 'Payout API', '/developers/api-reference#payouts'],
            ],
            'items' => [
                ['Payouts', 'page', '/products/payouts', 'Send funds to a bank account or UPI ID from the dashboard or API, with saved beneficiaries and status tracking.'],
                ['Bulk Payouts', 'page', '/products/bulk-payouts', 'Submit a batch of payouts in one action or a single API request, and track every payout in it individually.'],
                ['Vendor Payments', 'page', '/products/vendor-payments', 'Pay suppliers by bank transfer or UPI, one at a time or in a batch, with a record of every payment.'],
                ['Employee Payments', 'page', '/products/employee-payments', 'Pay staff and freelancers the amounts your payroll process calculates. Paynancial is not a payroll system.'],
                ['Partner Payments', 'page', '/products/partner-payments', 'Pay channel and business partners by bank transfer or UPI, triggered by your own systems through the API.'],
                ['International Payments', 'section', null, 'What is published today: Payouts send funds to bank accounts and UPI IDs. Countries, currencies, foreign exchange and cross-border regulatory availability are not published. Talk to our team about your requirements before you plan around international payments.'],
            ],
            'related' => ['accept-and-collect', 'financial-operations', 'embedded-finance'],
        ],
        'financial-operations' => [
            'pillar' => true,
            'secondary' => ['Explore Reconciliation', '/products/reconciliation', 'cta_click'],
            'hero_nodes' => [['Transactions', 'Recorded as they happen'], ['Settlements', 'Settled and pending'], ['Reconciliation', 'Matched in one place'], ['Reports', 'Exported or scheduled']],
            'flow' => [
                ['Recorded', 'Every transaction, settlement and refund is recorded as it happens.'],
                ['Settled', 'Each transaction is tied to a settlement record.'],
                ['Refunded', 'Refunds are issued against the original payment and tracked to completion.'],
                ['Reconciled', 'Reconciliation views match payments against settlements and refunds.'],
                ['Reported', 'Dashboards and reports you can export or schedule.'],
            ],
            'dev' => [['Refunds API', 'Full or partial refunds from your systems.', '/developers/api-reference#refunds'], ['Reports API', 'Transaction reports for a date range.', '/developers/api-reference#reports'], ['Webhooks', 'Refund and settlement events.', '/developers/webhooks'], ['Sandbox', 'Test with no real funds.', '/sandbox']],
            'ai' => [['AI Reconciliation', 'Exception-first matching of settlements to transactions.', '/ai-intelligence/reconciliation'], ['AI Cash-Flow Intelligence', 'Near-term liquidity from live transaction data.', '/ai-intelligence/cash-flow-intelligence'], ['AI Revenue Forecasting', 'Forward-looking revenue numbers.', '/ai-intelligence/revenue-forecasting']],
            'cross' => ['pay-and-move-money', 'Payouts you send are reported and reconciled alongside the payments you receive.'],
            'name'  => 'Financial Operations',
            'title' => 'Financial Operations | Reconciliation, Settlements, Refunds & Reports | Paynancial',
            'description' => 'The back office of your payments: reconciliation, settlements, refunds, analytics and reports on Paynancial — every transaction tied to a settlement record, with exportable and scheduled reports.',
            'h1'    => 'Bring financial operations into one connected workflow.',
            'lead'  => 'Reconcile payments against settlements and refunds, see what has settled and what is pending, issue refunds, and send your finance team the reports they need.',
            'answer' => 'Financial Operations is the part of Paynancial that happens after a payment: reconciliation, settlements, refunds, analytics and reporting. Every transaction is tied to a settlement record, refunds are tracked to completion, and reports can be exported or scheduled.',
            'intro' => [
                'Taking a payment is only the start. Someone has to confirm it settled, match it to the order, refund it when a customer returns something, and report it to the people who run the business. That work is where finance teams lose the most time.',
                'Paynancial builds it into the records themselves — settlement records for every transaction, refunds issued against the original payment, collections reconciled automatically — and puts it all in Payment Analytics.',
            ],
            'choose' => [
                ['You need to close the books', 'Reconciliation', '/products/reconciliation'],
                ['You need to know what has been paid out to you', 'Settlements', '/products/settlements'],
                ['You handle returns and cancellations', 'Refunds', '/products/refunds'],
                ['You report to management', 'Payment Analytics', '/products/payment-analytics'],
            ],
            'items' => [
                ['Reconciliation', 'page', '/products/reconciliation', 'Payments, refunds and settlements matched in one place, with discrepancies surfaced clearly.'],
                ['Settlements', 'page', '/products/settlements', 'See what has settled, what is pending and when it is due.'],
                ['Refunds', 'page', '/products/refunds', 'Full or partial refunds from the dashboard or API, tracked to completion.'],
                ['Finance Analytics', 'page', '/products/payment-analytics', 'Transaction dashboards by method, status and period (Payment Analytics).'],
                ['MIS & Reports', 'covered', '/products/payment-analytics', 'Part of Payment Analytics: exportable reports and scheduled delivery.'],
                ['Chargebacks', 'section', null, 'What is published today: every refund is tracked from request to completion, and Paynancial\'s Refund Policy sets out how chargebacks and disputes are handled under its terms. A chargeback management product is not yet described on this site. Ask our team.', ['Read the Refund Policy', '/legal/refund-policy#chargebacks']],
                ['Invoice Management', 'section', null, 'What is published today: a Payment Link can be added to an invoice so the customer pays in one step, and each link shows whether it is active, paid, expired or disabled. Invoice creation and tracking as a product is not yet described on this site. Ask our team.', ['Explore Payment Links', '/products/payment-links']],
                ['Expense Management', 'section', null, 'Expense management is not yet described on this site, so no capabilities are claimed here. Ask our team about your requirements.'],
            ],
            'related' => ['accept-and-collect', 'ai-and-intelligence', 'pay-and-move-money'],
        ],
        'ai-and-intelligence' => [
            'name'  => 'AI & Intelligence',
            'title' => 'AI & Intelligence | AI Fraud Detection, Reconciliation & Forecasting | Paynancial',
            'description' => 'Paynancial AI & Intelligence: AI Fraud Detection, AI Reconciliation, AI Financial Assistant, AI Cash-Flow Intelligence and AI Revenue Forecasting — each working within limits your business sets.',
            'h1'    => 'AI that works on your payments data — inside your limits.',
            'lead'  => 'Fraud screening, exception-first reconciliation, cash-flow and revenue forecasting, and answers to routine payment questions — each one surfacing a recommendation or a narrowly scoped action your business controls.',
            'answer' => 'AI & Intelligence is the family of Paynancial AI capabilities that work on your payments data: AI Fraud Detection, AI Reconciliation, AI Financial Assistant, AI Cash-Flow Intelligence and AI Revenue Forecasting. Each one surfaces a recommendation or takes a narrowly scoped action within limits a business sets — a person, or a policy a person configured, stays the authority.',
            'intro' => [
                'Payments data says a lot about a business: which transactions look unusual, which settlements do not match, how much cash will be available next week. AI & Intelligence turns that data into signals a finance team can act on, instead of reports they have to read.',
                'Every capability here follows the same governance model: permissions define what it can do, policy limits cap how much, human oversight reviews anything above a threshold, and every action is logged against the rule that authorised it.',
            ],
            'choose' => [
                ['You worry about fraudulent payments', 'AI Fraud Detection', '#ai-fraud-detection'],
                ['Reconciliation takes too long', 'AI Reconciliation', '#ai-reconciliation'],
                ['You need to plan cash', 'AI Cash-Flow Intelligence', '#ai-cash-flow-intelligence'],
                ['You want to understand the governance first', 'AI Governance', '/ai-governance'],
            ],
            'items' => [
                ['AI Fraud Detection', 'request', null, 'Evaluates transaction patterns for fraud risk as they happen, rather than after settlement. People decide on flagged transactions above your thresholds. ' . $ask],
                ['AI Reconciliation', 'request', null, 'Matches settlements against transactions automatically and surfaces only the genuine exceptions for a person to resolve. ' . $ask],
                ['AI Financial Assistant', 'request', null, 'Answers routine questions such as "why was this transaction declined?" from your payments data, without a support ticket. ' . $ask],
                ['AI Cash-Flow Intelligence', 'request', null, 'Forecasts near-term liquidity from live transaction data instead of a monthly spreadsheet. Treasury decisions stay with people. ' . $ask],
                ['AI Revenue Forecasting', 'request', null, 'Turns raw transaction volume into the forward-looking revenue numbers a finance lead needs. ' . $ask],
                ['Paynancial AI', 'request', null, 'Paynancial AI is the name for these capabilities together. ' . $ask],
            ],
            'related' => ['financial-operations', 'accept-and-collect', 'embedded-finance'],
        ],
        'embedded-finance' => [
            'name'  => 'Embedded Finance',
            'title' => 'Embedded Finance | Payments Inside Your Platform | Paynancial',
            'description' => 'Embedded finance with Paynancial: what embedded payments, payouts, billing, wallets, split payments and white-label payments are, the API foundation they build on, and how to talk to our team about availability.',
            'h1'    => 'Put payments inside your own product.',
            'lead'  => 'Embedded finance means your customers pay, get paid and see their money inside your platform — not on someone else\'s. Paynancial\'s API is the foundation it builds on.',
            'answer' => 'Embedded finance puts financial services — payments, payouts, billing, wallets — directly inside a platform\'s own product, so its users never leave it. Paynancial\'s API, webhooks and payouts are the foundation; the specific embedded offerings are available to discuss with our team.',
            'intro' => [
                'Platforms, marketplaces and software products increasingly want money to move inside their own experience: a marketplace paying its sellers, a SaaS product collecting from its customers\' customers, an app holding a balance for its users. That is embedded finance.',
                'It is built on the same pieces as any Paynancial integration — a REST API, idempotent requests, real-time webhooks and payouts to bank accounts and UPI IDs. The embedded offerings below are available to discuss with our team, so you can confirm what fits your platform before you build.',
            ],
            'choose' => [
                ['You want to understand the API foundation', 'Developer Hub', '/developers'],
                ['You need to pay many users or sellers', 'Payouts', '/products/payouts'],
                ['You want to test first', 'Sandbox', '/sandbox'],
                ['You are ready to discuss your platform', 'Talk to our team', '/contact?intent=sales&product=embedded-finance'],
            ],
            'items' => [
                ['Embedded Payments', 'request', null, 'Embedded payments let a platform\'s users take payments inside the platform itself, rather than through a separate provider account. ' . $ask],
                ['Embedded Payouts', 'request', null, 'Embedded payouts let a platform pay its users — sellers, drivers, creators — from inside its own product. ' . $ask],
                ['Embedded Billing', 'request', null, 'Embedded billing lets a platform invoice and charge on behalf of its users, inside its own product. ' . $ask],
                ['Wallet Infrastructure', 'request', null, 'Wallet infrastructure lets a platform hold and show a balance for each of its users. ' . $ask],
                ['Split Payments', 'request', null, 'Split payments divide one customer payment between several recipients, such as a marketplace and its sellers. ' . $ask],
                ['White-Label Payments', 'request', null, 'White-label payments are presented under a platform\'s own brand instead of the payment provider\'s. ' . $ask],
            ],
            'related' => ['pay-and-move-money', 'accept-and-collect', 'ai-and-intelligence'],
        ],
    ];
}

function cat_page(string $slug): ?array
{
    $all = cat_pages();
    return isset($all[$slug]) ? ['slug' => $slug] + $all[$slug] : null;
}

/** Menu column title → category slug. */
function cat_for_column(string $title): ?string
{
    foreach (cat_pages() as $slug => $c) {
        if ($c['name'] === $title) {
            return $slug;
        }
    }
    return null;
}
