<?php
/**
 * Developer documentation — single source for the Developer Hub
 * (/developers), its child pages (/developers/{slug}) and the Sandbox
 * (/sandbox).
 *
 * Content rule: every technical fact here was already published on
 * paynancial.com before these pages existed (developers, homepage and
 * product pages): the base URL, basic-auth API keys, sandbox and live
 * keys, the six example resources and their example parameters,
 * idempotency keys, the three example error codes, webhook event
 * categories and the PHP/JavaScript/Python SDKs. Nothing is added
 * beyond that — no extra endpoints, event names, parameters, limits,
 * test credentials, package names or version numbers. Where a developer
 * needs more, the pages say so and point to developer support.
 */

declare(strict_types=1);

const DEV_API_BASE = 'https://api.paynancial.com/v1';

/** Developer pages: slug => [title, short description, path]. */
function dev_pages(): array
{
    return [
        'integration-guide' => ['Integration Guide', 'The path from a sandbox key to a live integration, step by step.', '/developers/integration-guide'],
        'api-reference'     => ['API Reference', 'Base URL, resources, request conventions, idempotency and errors.', '/developers/api-reference'],
        'payment-apis'      => ['Payment APIs', 'Payments, payment links, collections, refunds and reports — the money-in APIs.', '/developers/payment-apis'],
        'payout-apis'       => ['Payout APIs', 'Send money to bank accounts and UPI IDs, exactly once, with idempotency keys.', '/developers/payout-apis'],
        'authentication'    => ['Authentication', 'API keys, sandbox and live environments, and keeping keys safe.', '/developers/authentication'],
        'webhooks'          => ['Webhooks', 'Real-time events for payments, payouts, refunds and settlements.', '/developers/webhooks'],
        'sdks'              => ['SDKs', 'Client libraries for PHP, JavaScript and Python.', '/developers/sdks'],
        'sandbox'           => ['Sandbox', 'Test your integration end to end with no real funds involved.', '/sandbox'],
    ];
}

/** Related-page cards for a developer page, excluding the current one. */
function dev_related(string $current, array $order): array
{
    $pages = dev_pages();
    $out = [];
    foreach ($order as $slug) {
        if ($slug !== $current && isset($pages[$slug])) {
            $out[] = $pages[$slug];
        }
    }
    return $out;
}

/** Enquiry targets. */
function dev_sandbox_request_url(): string
{
    return '/contact?intent=sales&product=sandbox-access';
}
function dev_support_url(): string
{
    return '/contact?intent=support';
}

/**
 * Published API resources, each with the example request already shown on
 * the site. 'params' lists only the parameters those examples use.
 */
function dev_resources(): array
{
    return [
        'payments' => [
            'name'   => 'Payments',
            'path'   => '/payments',
            'does'   => 'Create a payment to accept money from a customer.',
            'params' => [['amount', 'Amount in the smallest currency unit (paise for INR) — 50000 is ₹500.00'], ['currency', 'Currency code, e.g. INR'], ['receipt', 'Your own reference for the order']],
            'returns'=> 'A payment object; the examples read its <code>id</code>.',
            'product'=> ['Payment Gateway', '/products/payment-gateway'],
            'php'    => "\$payment = \$client->payments->create([\n    'amount'   => 50000, // in paise\n    'currency' => 'INR',\n    'receipt'  => 'order_rcpt_101',\n]);\n\necho \$payment->id;",
            'js'     => "const payment = await client.payments.create({\n  amount: 50000,\n  currency: 'INR',\n  receipt: 'order_rcpt_101',\n});\n\nconsole.log(payment.id);",
            'curl'   => "curl " . DEV_API_BASE . "/payments \\\n  -u YOUR_API_KEY: \\\n  -d amount=50000 \\\n  -d currency=INR \\\n  -d receipt=order_rcpt_101",
        ],
        'refunds' => [
            'name'   => 'Refunds',
            'path'   => '/refunds',
            'does'   => 'Refund all or part of an existing payment.',
            'params' => [['payment_id', 'The payment being refunded'], ['amount', 'Amount to refund, in paise']],
            'returns'=> 'A refund object.',
            'product'=> ['Refunds', '/products/refunds'],
            'php'    => "\$refund = \$client->refunds->create([\n    'payment_id' => 'pay_9F3kd82',\n    'amount'     => 20000,\n]);",
            'js'     => "const refund = await client.refunds.create({\n  payment_id: 'pay_9F3kd82',\n  amount: 20000,\n});",
            'python' => "refund = client.refunds.create(\n    payment_id='pay_9F3kd82',\n    amount=20000,\n)",
            'curl'   => "curl " . DEV_API_BASE . "/refunds \\\n  -u YOUR_API_KEY: \\\n  -d payment_id=pay_9F3kd82 \\\n  -d amount=20000",
        ],
        'payouts' => [
            'name'   => 'Payouts',
            'path'   => '/payouts',
            'does'   => 'Send money to a beneficiary\'s bank account or UPI ID.',
            'params' => [['beneficiary_id', 'The beneficiary receiving the funds'], ['amount', 'Amount in paise — 250000 is ₹2,500.00'], ['mode', 'Transfer mode; the published example uses <code>upi</code>. Bank transfer is also supported.']],
            'returns'=> 'A payout object; the examples read its <code>status</code>.',
            'product'=> ['Payouts', '/products/payouts'],
            'php'    => "\$payout = \$client->payouts->create([\n    'beneficiary_id' => 'bene_3Kd91',\n    'amount'         => 250000, // in paise\n    'mode'           => 'upi',\n], [\n    'idempotency_key' => 'payout-run-2026-08-29-0417',\n]);\n\necho \$payout->status;",
            'curl'   => "curl " . DEV_API_BASE . "/payouts \\\n  -u YOUR_API_KEY: \\\n  -H \"Idempotency-Key: payout-run-2026-08-29-0417\" \\\n  -d beneficiary_id=bene_3Kd91 \\\n  -d amount=250000 \\\n  -d mode=upi",
        ],
        'payment_links' => [
            'name'   => 'Payment Links',
            'path'   => '/payment_links',
            'does'   => 'Create a shareable link a customer can pay without a checkout on your site.',
            'params' => [['title', 'What the customer sees, e.g. an invoice number'], ['amount', 'Amount in paise; omit it to let the customer enter the amount'], ['currency', 'Currency code, e.g. INR']],
            'returns'=> 'A payment link object; the examples read its <code>short_url</code>.',
            'product'=> ['Payment Links', '/products/payment-links'],
            'php'    => "\$link = \$client->paymentLinks->create([\n    'title'    => 'Invoice #204',\n    'amount'   => 500000, // in paise, or omit to let the customer enter it\n    'currency' => 'INR',\n]);\n\necho \$link->short_url;",
            'curl'   => "curl " . DEV_API_BASE . "/payment_links \\\n  -u YOUR_API_KEY: \\\n  -d title='Invoice #204' \\\n  -d amount=500000 \\\n  -d currency=INR",
        ],
        'collections' => [
            'name'   => 'Collections',
            'path'   => '/collections',
            'does'   => 'Collect recurring or scheduled payments from a customer.',
            'params' => [['customer_id', 'The customer being charged'], ['amount', 'Amount per collection, in paise'], ['schedule', 'How often to collect; the published example uses <code>monthly</code>']],
            'returns'=> 'A collection object; the examples read its <code>id</code>.',
            'product'=> ['Payment Collection', '/products/payment-collection'],
            'php'    => "\$collection = \$client->collections->create([\n    'customer_id' => 'cust_7Fk21',\n    'amount'      => 150000, // in paise\n    'schedule'    => 'monthly',\n]);\n\necho \$collection->id;",
            'curl'   => "curl " . DEV_API_BASE . "/collections \\\n  -u YOUR_API_KEY: \\\n  -d customer_id=cust_7Fk21 \\\n  -d amount=150000 \\\n  -d schedule=monthly",
        ],
        'reports' => [
            'name'   => 'Transaction reports',
            'path'   => '/reports/transactions',
            'does'   => 'Generate a transaction report for a date range, for your finance team or accounting system.',
            'params' => [['from', 'Start date, e.g. 2026-08-01'], ['to', 'End date, e.g. 2026-08-31'], ['format', 'Report format; the published example uses <code>csv</code>']],
            'returns'=> 'A report object; the examples read its <code>download_url</code>.',
            'product'=> ['Payment Analytics', '/products/payment-analytics'],
            'php'    => "\$report = \$client->reports->transactions([\n    'from'   => '2026-08-01',\n    'to'     => '2026-08-31',\n    'format' => 'csv',\n]);\n\necho \$report->download_url;",
            'curl'   => "curl " . DEV_API_BASE . "/reports/transactions \\\n  -u YOUR_API_KEY: \\\n  -d from=2026-08-01 \\\n  -d to=2026-08-31 \\\n  -d format=csv",
        ],
    ];
}

/** Code blocks for one resource, in the languages published for it. */
function dev_resource_code(array $r): array
{
    $labels = ['php' => 'PHP', 'js' => 'JavaScript', 'python' => 'Python', 'curl' => 'cURL'];
    $out = [];
    foreach ($labels as $lang => $label) {
        if (!empty($r[$lang])) {
            $out[$lang] = [$label, $r[$lang]];
        }
    }
    return $out;
}

/** Client set-up snippets as published. */
function dev_client_setup(): array
{
    return [
        'php'    => ['PHP', "\$client = new Paynancial\\Client('YOUR_API_KEY');"],
        'js'     => ['JavaScript', "const client = new Paynancial({ key: 'YOUR_API_KEY' });"],
        'python' => ['Python', "client = paynancial.Client('YOUR_API_KEY')"],
        'curl'   => ['cURL', "curl " . DEV_API_BASE . "/payments \\\n  -u YOUR_API_KEY:"],
    ];
}

/** The three published error codes. */
function dev_error_examples(): array
{
    return [
        ['<code>insufficient_funds</code>', 'The balance available is not enough for the requested payout or refund.', 'Do not retry automatically; top up or reduce the amount, then try again.'],
        ['<code>invalid_method</code>', 'The payment method or mode in the request is not valid for this call.', 'Correct the request. Retrying the same request will fail the same way.'],
        ['<code>rate_limited</code>', 'Too many requests in a short period.', 'Back off and retry later with the same idempotency key.'],
    ];
}

/** Webhook event categories as published. */
function dev_webhook_categories(): array
{
    return [
        ['Payments', 'A payment changes state, for example when it succeeds or fails.', 'Fulfil the order or release the service.'],
        ['Payouts', 'A payout changes state.', 'Mark the vendor, employee or partner as paid.'],
        ['Refunds', 'A refund changes state.', 'Update the customer\'s order and notify them.'],
        ['Settlements', 'Funds are settled to your account.', 'Reconcile settled amounts against your books.'],
    ];
}
