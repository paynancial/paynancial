<?php
/**
 * Product capability pages — /products/{refunds,settlements,
 * reconciliation,upi-payments}. These are capabilities that span several
 * Paynancial products, so each page gathers what is already published
 * about them on the product pages, the Developer pages and Payment
 * Analytics into one destination.
 *
 * Content rule: nothing here goes beyond facts already published on the
 * site — no settlement timings, fees, success rates, UPI AutoPay or
 * mandates, currencies or limits. FAQs live in faq-data.php
 * (product:{slug}) so the FAQ hub lists them.
 */

declare(strict_types=1);

require_once __DIR__ . '/developer-docs.php';
require_once __DIR__ . '/faq-data.php';

function pc_pages(): array
{
    $resources = dev_resources();
    return [
        'refunds' => [
            'name'  => 'Refunds',
            'title' => 'Refunds | Full & Partial Refunds by Dashboard or API | Paynancial',
            'description' => 'Issue full or partial refunds from the Paynancial dashboard or the Refunds API, track every refund from request to completion, and receive refund events by webhook.',
            'h1'    => 'Refund in full or in part — and see it through to completion.',
            'lead'  => 'Issue refunds from the dashboard or the API, against the original payment, and track each one from request to completion alongside your settlements.',
            'answer' => 'With Paynancial you can issue a full or partial refund against the original payment, from the dashboard or with the Refunds API, track it from request through to completion in Payment Analytics, and receive refund events by webhook.',
            'values' => ['Full or partial', 'Dashboard or API', 'Tracked to completion', 'Refund webhooks'],
            'aside_code' => dev_resource_code($resources['refunds']),
            'aside_caption' => 'POST /refunds',
            'what' => [
                'A refund returns all or part of a payment to the customer who made it. On Paynancial, every refund is issued against the original payment, so it stays traceable to the order, booking or invoice it belongs to.',
                'You can refund from the dashboard — useful for support teams handling one customer at a time — or from your own systems through the Refunds API, so returns and cancellations can be refunded automatically.',
            ],
            'steps' => [
                ['Find the payment', 'Locate the original payment in the dashboard, or use its payment ID in your system.'],
                ['Choose the amount', 'Refund the full amount, or a partial amount such as one item or a shipping charge.'],
                ['Issue the refund', 'From the dashboard, or with a POST to /refunds with the payment ID and amount.'],
                ['Get notified', 'A webhook tells your system as the refund changes state.'],
                ['Track to completion', 'Follow the refund from request to completion in Payment Analytics.'],
            ],
            'capabilities' => [
                ['Full or partial refunds', 'Refund the whole payment or any part of it.'],
                ['Dashboard or API', 'Support teams refund from the dashboard; systems refund through the Refunds API.'],
                ['Refund tracking', 'Follow each refund from request through to completion in Payment Analytics.'],
                ['Reconciliation views', 'See payments, refunds and settlements side by side in one place.'],
                ['Refund webhooks', 'Refunds are one of the four webhook event families, so your systems react as a refund changes state.'],
                ['Safe retries', 'Send an idempotency key with each refund request so a retried request never refunds twice.'],
            ],
            'surfaces' => [
                ['Dashboard', 'Issue a full or partial refund against a payment.'],
                ['API', '<code>POST /refunds</code> with <code>payment_id</code> and <code>amount</code> (in paise). See the <a class="inline-link" href="/developers/api-reference#refunds">API Reference</a>.'],
                ['Webhooks', 'Refund events as a refund changes state. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['Payment Analytics', 'Refund tracking and reconciliation views. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'practices' => [
                ['Refund against the original payment', 'It keeps every return traceable to its order, and your reconciliation clean.'],
                ['Use idempotency keys', 'A timeout on a refund request should never turn into two refunds.'],
                ['Publish your refund policy', 'Tell customers what is refundable and how before they pay, so refunds are predictable for both sides.'],
            ],
            'related' => ['settlements', 'reconciliation', 'product:payment-gateway', 'product:payment-analytics'],
        ],
        'settlements' => [
            'name'  => 'Settlements',
            'title' => 'Settlements | Settlement Reporting & Visibility | Paynancial',
            'description' => 'See which payments have settled, what is pending and when it is due. Every Paynancial transaction is tied to a settlement record you can reconcile against, with settlement webhooks.',
            'h1'    => 'Know what has settled, what is pending and when it is due.',
            'lead'  => 'Every successful payment is queued for settlement and tied to a settlement record — so your finance team can see exactly which transactions each settlement covers.',
            'answer' => 'On Paynancial, every successful payment is recorded and queued for settlement, and every transaction is tied to a settlement record. Payment Analytics shows what has settled, what is pending and when it is due, and settlement webhooks tell your systems when funds are settled to your account.',
            'values' => ['Settlement records', 'Pending and settled', 'Settlement webhooks', 'Exportable reports'],
            'what' => [
                'Settlement is the step where the money from your customers\' payments is paid out to your business account. Knowing which payments a settlement includes is what lets a finance team close the books with confidence.',
                'On Paynancial, a successful payment is recorded and queued for settlement, and every transaction is tied to a settlement record. That link — from payment to settlement — is what the rest of your reconciliation depends on.',
            ],
            'steps' => [
                ['Payment succeeds', 'A successful payment is recorded against your order.'],
                ['Queued for settlement', 'The payment is queued for settlement and linked to a settlement record.'],
                ['Funds are settled', 'A settlement webhook tells your system when funds are settled to your account.'],
                ['Visible in Analytics', 'Payment Analytics shows what has settled and what is still pending.'],
                ['Reconciled', 'Match each settlement to its payments and refunds, then export or schedule the report.'],
            ],
            'capabilities' => [
                ['Settlement records', 'Every transaction is tied to a settlement record you can reconcile against.'],
                ['Settlement visibility', 'See what has settled, what is pending, and when it is due.'],
                ['Settlement webhooks', 'Settlements are one of the four webhook event families.'],
                ['Reconciliation views', 'Match payments against settlements and refunds in one place.'],
                ['Exportable reports', 'Download settlement data in formats your finance team already works with.'],
                ['Scheduled reports', 'Have recurring reports delivered without manual effort.'],
            ],
            'surfaces' => [
                ['Payment Analytics', 'Settlement visibility, reconciliation views and reports. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
                ['Webhooks', 'An event when funds are settled to your account. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['Payment Gateway', 'Settlement reporting behind every transaction. See <a class="inline-link" href="/products/payment-gateway">Payment Gateway</a>.'],
            ],
            'practices' => [
                ['Reconcile against settlement records', 'Match each settlement to the transactions and refunds it covers, not to a bank-statement total.'],
                ['Automate with webhooks', 'Update your books when the settlement event arrives, instead of checking by hand.'],
                ['Schedule the report', 'Have your finance team receive settlement reports on a schedule.'],
            ],
            'related' => ['reconciliation', 'refunds', 'product:payment-analytics', 'product:payment-gateway'],
        ],
        'reconciliation' => [
            'name'  => 'Reconciliation',
            'title' => 'Payment Reconciliation | Match Payments, Refunds & Settlements | Paynancial',
            'description' => 'Reconcile payments, refunds and settlements in one place with Paynancial: settlement records for every transaction, automatic reconciliation of collections, discrepancies surfaced clearly, and exportable reports.',
            'h1'    => 'Payments, refunds and settlements — matched in one place.',
            'lead'  => 'Every transaction is tied to a settlement record, collections reconcile automatically, and discrepancies surface clearly — so month-end stops being a spreadsheet exercise.',
            'answer' => 'Paynancial ties every transaction to a settlement record, reconciles each collection automatically against the customer and cycle it belongs to, and shows payments, refunds and settlements side by side in Payment Analytics — with discrepancies surfaced clearly and reports you can export or schedule.',
            'values' => ['Settlement records', 'Automatic for collections', 'Discrepancies surfaced', 'Export or schedule'],
            'what' => [
                'Reconciliation is matching what you expected to be paid with what actually arrived — each payment to its order, each refund to its payment, and each settlement to the transactions it covers.',
                'Paynancial builds reconciliation into the records themselves. Every transaction is tied to a settlement record, every refund is issued against its payment, and every collection is matched to the customer and cycle it belongs to — so reconciling is reviewing, not rebuilding.',
            ],
            'steps' => [
                ['Record', 'Every transaction, settlement and refund is recorded as it happens.'],
                ['Link', 'Each transaction is tied to a settlement record; each collection to its customer and cycle.'],
                ['Match', 'Reconciliation views show payments against settlements and refunds in one place.'],
                ['Surface', 'Discrepancies surface clearly so they can be resolved.'],
                ['Report', 'Export reports or schedule them for your finance team.'],
            ],
            'capabilities' => [
                ['Reconciliation views', 'Match payments against settlements and refunds in one place (Payment Analytics).'],
                ['Automatic reconciliation for collections', 'Each collection is matched against the customer and cycle it belongs to (Payment Collection).'],
                ['Order-level reconciliation', 'Reconcile each transaction against your order in the dashboard or via webhook (Payment Gateway).'],
                ['Discrepancies surfaced', 'Discrepancies surface clearly so reconciliation stays manageable.'],
                ['Exportable and scheduled reports', 'Download reports or have them delivered on a schedule.'],
                ['AI Reconciliation', 'An AI capability that matches settlements against transactions and surfaces only genuine exceptions — ask our team about availability.'],
            ],
            'surfaces' => [
                ['Payment Analytics', 'Reconciliation views, discrepancies and reports. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
                ['Payment Collection', 'Automatic reconciliation of every collection cycle. See <a class="inline-link" href="/products/payment-collection">Payment Collection</a>.'],
                ['Webhooks', 'Payment, refund and settlement events to reconcile in your own systems. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['AI Reconciliation', 'Exception-first matching. See <a class="inline-link" href="/agentic-ai/financial-agents#capabilities">AI Financial Agents</a>.'],
            ],
            'practices' => [
                ['Use your own reference on every payment', 'An order, invoice or booking ID as the receipt makes matching straightforward.'],
                ['Reconcile daily, not monthly', 'Small, frequent reconciliation keeps discrepancies small and recent.'],
                ['Keep people on the exceptions', 'Let the matching run automatically and spend your team\'s time on what does not match.'],
            ],
            'related' => ['settlements', 'refunds', 'product:payment-analytics', 'product:payment-collection'],
        ],
        'upi-payments' => [
            'name'  => 'UPI Payments',
            'title' => 'UPI Payments | Accept UPI & Pay Out to UPI IDs | Paynancial',
            'description' => 'Accept UPI at checkout alongside cards, netbanking and wallets, and send payouts to UPI IDs — single or in bulk — through one Paynancial integration, with reporting and reconciliation behind every transaction.',
            'h1'    => 'Accept UPI, and pay out to UPI IDs, through one integration.',
            'lead'  => 'UPI sits alongside cards, netbanking and wallets at your checkout — and Payouts sends money straight to a beneficiary\'s UPI ID.',
            'answer' => 'With Paynancial you can accept UPI payments at your checkout through the Payment Gateway, alongside cards, netbanking and wallets, and send single or bulk payouts to UPI IDs with Payouts — all through one integration with reporting and reconciliation.',
            'values' => ['UPI at checkout', 'Payouts to UPI IDs', 'Single or bulk', 'One integration'],
            'aside_code' => dev_resource_code($resources['payouts']),
            'aside_caption' => 'A payout to a UPI beneficiary',
            'what' => [
                'UPI (Unified Payments Interface) lets customers in India pay directly from their bank account using a UPI app. For many customers it is the payment method they reach for first.',
                'Paynancial supports UPI in both directions: <strong>money in</strong>, as one of the payment methods on the Payment Gateway, and <strong>money out</strong>, as a payout mode for sending funds to a beneficiary\'s UPI ID.',
            ],
            'steps' => [
                ['Offer UPI at checkout', 'UPI appears alongside cards, netbanking and wallets on your hosted or custom checkout.'],
                ['Customer pays by UPI', 'The customer completes the payment in their UPI app.'],
                ['Get the result', 'Paynancial returns a real-time status and a webhook confirms the payment.'],
                ['Pay out by UPI', 'Add a beneficiary\'s UPI ID and send a single or bulk payout.'],
                ['Reconcile', 'Payments, payouts and settlements appear in your reports.'],
            ],
            'capabilities' => [
                ['UPI at checkout', 'UPI is one of the payment methods on the Payment Gateway, alongside cards, netbanking and wallets.'],
                ['Single or bulk payouts', 'Pay one beneficiary or submit a whole batch in one action.'],
                ['Payouts to UPI IDs', 'Send funds to a beneficiary\'s UPI ID, one at a time or in bulk.'],
                ['Beneficiary management', 'Save and reuse UPI beneficiaries for repeat payouts.'],
                ['Status tracking', 'Follow each payment and payout, with clear reasons when one fails.'],
                ['Reporting', 'Break down transactions by payment method in Payment Analytics.'],
            ],
            'surfaces' => [
                ['Payment Gateway', 'UPI as a checkout payment method. See <a class="inline-link" href="/products/payment-gateway">Payment Gateway</a>.'],
                ['Payouts', 'UPI as a payout mode (<code>mode=upi</code> in the API). See <a class="inline-link" href="/products/payouts">Payouts</a>.'],
                ['Payment Analytics', 'Transactions by method, status and period. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'practices' => [
                ['Show UPI prominently', 'Put the methods your customers use most where they see them first at checkout.'],
                ['Confirm by webhook', 'Mark an order paid when the payment webhook arrives, not when the customer returns to your site.'],
                ['Make payouts idempotent', 'Send an idempotency key with every payout so a retry never pays twice.'],
            ],
            'related' => ['refunds', 'settlements', 'product:payment-gateway', 'product:payouts'],
        ],
    ];
}

function pc_page(string $slug): ?array
{
    $all = pc_pages();
    return isset($all[$slug]) ? ['slug' => $slug] + $all[$slug] : null;
}

/** Related card: capability slug or product:{slug}. */
function pc_related_card(string $key): array
{
    $products = [
        'payment-gateway' => ['Payment Gateway', 'Cards, UPI, netbanking and wallets through one integration.'],
        'payment-links' => ['Payment Links', 'Get paid without a website or checkout.'],
        'payment-collection' => ['Smart Collections', 'Recurring and bulk collections with reconciliation built in.'],
        'payouts' => ['Payouts', 'Send money to bank accounts and UPI IDs.'],
        'payment-analytics' => ['Payment Analytics', 'Dashboards, settlements, reconciliation and reports.'],
    ];
    if (str_starts_with($key, 'category:')) {
        require_once __DIR__ . '/product-categories.php';
        $c = cat_page(substr($key, 9));
        return [$c['name'], $c['lead'], cat_url($c['slug'])];
    }
    if (str_starts_with($key, 'product:')) {
        $slug = substr($key, 8);
        return [$products[$slug][0], $products[$slug][1], '/products/' . $slug];
    }
    $p = pc_page($key);
    return [$p['name'], $p['lead'], '/products/' . $key];
}
