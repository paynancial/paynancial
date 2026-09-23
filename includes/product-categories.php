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
    // The pillars live at their own top-level URLs.
    if (in_array($slug, ['pay-and-move-money', 'financial-operations', 'embedded-finance'], true)) {
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
                ['International Payments', 'guide', '/products/international-payments', 'What cross-border payments involve for a business in India, what Paynancial publishes today, and what to confirm with our team.'],
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
                ['MIS & Reports', 'page', '/products/mis-reports', 'Transaction, settlement and refund reports — exported, scheduled or pulled through the Reports API.'],
                ['Chargebacks', 'page', '/products/chargebacks', 'How disputes work under Paynancial\'s Refund Policy: evidence shared with you, time to respond, and a decision by the network or bank.', ['Read the Refund Policy', '/legal/refund-policy#chargebacks']],
                ['Invoice Management', 'guide', '/products/invoice-management', 'Put a Payment Link on every invoice so customers pay in one step, and track each link\'s status.'],
                ['Expense Management', 'guide', '/products/expense-management', 'The payment side of business spending: pay approved bills and claims through Payouts, tracked and reported.'],
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
            // Evidence (baseline site): the Partners page — technology partners
            // "integrate Paynancial into your own software platform or
            // marketplace" and "embed Payment, Payout and Billing APIs
            // directly" — plus the published API, webhooks, Payouts and Smart
            // Collections. Solutions and Technology narrative (splitting funds
            // to sellers, "under their brand") is use-case framing, not
            // product evidence, so no embedded item has its own page: each is
            // a section here, per the approved "split by evidence" rule.
            'pillar' => true,
            'secondary' => ['Explore the Developer Hub', '/developers', 'cta_click'],
            'hero_nodes' => [['Your platform', 'Your product, your users'], ['Paynancial API', 'One REST API, API keys'], ['Payments · Collections · Payouts', 'Embedded in your workflow'], ['Webhooks', 'Events back to your product']],
            'flow' => [
                ['Get a sandbox key', 'Build and test against the API with no real funds involved.'],
                ['Embed the APIs', 'Call the Payments, Collections and Payouts APIs from your own product, with an idempotency key on every request that moves money.'],
                ['Listen for events', 'Webhooks tell your product as payments, payouts, refunds and settlements change state.'],
                ['Go live', 'Swap sandbox keys for live keys once your integration is reviewed.'],
                ['Reconcile and report', 'Settlement records and transaction reports keep your platform\'s books straight.'],
            ],
            'dev' => [['Payments API', 'Accept payments from inside your product.', '/developers/api-reference#payments'], ['Collections API', 'Recurring and scheduled payments.', '/developers/api-reference#collections'], ['Payouts API', 'Pay out to bank accounts and UPI IDs.', '/developers/api-reference#payouts'], ['Webhooks', 'Real-time events for your product.', '/developers/webhooks'], ['Integration Guide', 'From a sandbox key to a live integration.', '/developers/integration-guide'], ['Sandbox', 'Test with no real funds.', '/sandbox']],
            'ai' => [['AI & Intelligence', 'AI capabilities that work on the same payments data.', '/ai-intelligence'], ['AI Governance', 'Permissions, limits and oversight for anything automated.', '/ai-governance'], ['AI Financial Agents', 'Agents that act within limits a business sets.', '/agentic-ai/financial-agents']],
            'cross' => ['pay-and-move-money', 'Paying sellers, creators or partners from your platform runs on Payouts — to bank accounts and UPI IDs, singly or in bulk.'],
            'name'  => 'Embedded Finance',
            'title' => 'Embedded Finance | Payment, Payout & Billing APIs for Platforms | Paynancial',
            'description' => 'Embedded finance with Paynancial: SaaS platforms and marketplaces embed Paynancial\'s Payment, Payout and Billing APIs in their own product. See what is published today, how an integration works, and what to confirm with our team.',
            'h1'    => 'Build payments into your own product.',
            'lead'  => 'SaaS platforms and marketplaces embed Paynancial\'s Payment, Payout and Billing APIs directly, so money moves inside their own product instead of on someone else\'s.',
            'answer' => 'Embedded finance puts payments, payouts and billing inside a platform\'s own product. On Paynancial, technology partners — SaaS platforms and marketplaces — embed the Payment, Payout and Billing APIs directly, using one REST API, idempotent requests and real-time webhooks. Specific embedded offerings such as wallets, split payments and white-label payments are not yet described on this site; ask our team.',
            'intro' => [
                'Platforms, marketplaces and software products increasingly want money to move inside their own experience: a marketplace paying its sellers, a SaaS product billing its customers, an app that collects without sending anyone to a separate checkout. That is embedded finance.',
                'On Paynancial it starts with the same pieces as any integration — the Payments, Collections and Payouts APIs, API keys, idempotency keys and webhooks — embedded by technology partners in their own software. Each item below says exactly what is published today and what to confirm with our team before you build.',
            ],
            'choose' => [
                ['You want to take payments inside your product', 'Payments API', '/developers/api-reference#payments'],
                ['You bill your customers on a schedule', 'Payment Collection', '/products/payment-collection'],
                ['You pay sellers, creators or partners', 'Payouts', '/products/payouts'],
                ['You want to build on Paynancial as a technology partner', 'Partners', '/partners'],
                ['You want to test first', 'Sandbox', '/sandbox'],
            ],
            'items' => [
                ['Embedded Payments', 'section', null, 'What is published today: technology partners embed Paynancial\'s Payment APIs directly in their own software platform or marketplace, with API keys, idempotent requests and payment webhooks. Onboarding your platform\'s own users as merchants, and how funds and responsibilities are arranged between you and them, are not described on this site. Ask our team.', ['See the Payments API', '/developers/api-reference#payments']],
                ['Embedded Payouts', 'section', null, 'What is published today: technology partners embed the Payout API, which sends funds to bank accounts and UPI IDs with saved beneficiaries, idempotency keys and payout webhooks — so a platform can pay from its own systems. An embedded payouts offering beyond the Payouts product is not described on this site. Ask our team.', ['Explore Payouts', '/products/payouts']],
                ['Embedded Billing', 'section', null, 'What is published today: technology partners embed Billing APIs, and the Collections API collects recurring or scheduled payments from a customer, with retries for failed attempts through Smart Collections. Billing on behalf of your platform\'s own users is not described on this site. Ask our team.', ['Explore Payment Collection', '/products/payment-collection']],
                ['Wallet Infrastructure', 'section', null, 'Wallet infrastructure — holding and showing a balance for each of a platform\'s users — is not described on this site, so no capabilities are claimed here. Ask our team about your requirements.'],
                ['Split Payments', 'section', null, 'Splitting one customer payment between several recipients at the point of collection is not described as a product on this site. What is published today: after you collect, Payouts can pay sellers and partners to bank accounts and UPI IDs, singly or in bulk. Ask our team about splitting.', ['Explore Bulk Payouts', '/products/bulk-payouts']],
                ['White-Label Payments', 'section', null, 'White-label payments — presented under your platform\'s brand instead of Paynancial\'s — are not described on this site, so no capabilities are claimed here. Ask our team about your requirements.'],
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
