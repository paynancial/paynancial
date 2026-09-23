<?php
/**
 * Product detail template: /products/{slug}
 * $product_slug is set by the front controller. Sets $product_not_found
 * and returns early for an unknown slug so the controller can render 404.
 */
require_once __DIR__ . '/../includes/faq-data.php';
$products = [
    'payment-gateway' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payment Gateway',
        'headline' => 'Accept every way your customers want to pay.',
        'subhead'  => 'One integration for cards, UPI, netbanking and wallets — with clear, reconciled settlement reporting behind every transaction.',
        'who_for'  => 'For businesses taking payments on a website, app, or custom checkout.',
        'title'    => 'Payment Gateway | Accept Cards, UPI, Netbanking & Wallets | Paynancial',
        'description' => 'Accept cards, UPI, netbanking and wallets through one Paynancial integration — hosted or custom checkout, real-time status, full or partial refunds and settlement reporting behind every transaction.',
        'answer'   => 'The Paynancial Payment Gateway accepts cards, UPI, netbanking and wallets through a single integration, on a hosted checkout page or your own custom checkout, returns a real-time status for every transaction and ties each one to a settlement record you can reconcile against.',
        'step_titles' => ['Customer pays', 'Real-time status', 'Queued for settlement', 'Reconciled'],
        'api'      => 'payments',
        'related'  => ['upi-payments', 'refunds', 'settlements', 'category:accept-and-collect'],
        'features' => [
            ['Multiple payment methods', 'Cards, UPI, netbanking and wallets through a single API and checkout.'],
            ['Hosted or custom checkout', 'Use our hosted checkout page or build your own UI on top of the API.'],
            ['Settlement reporting', 'Every transaction is tied to a settlement record you can reconcile against.'],
            ['Refunds built in', 'Issue full or partial refunds from the dashboard or API.'],
            ['Retry & failure handling', 'Clear status codes so failed payments can be retried or resolved quickly.'],
            ['Sandbox testing', 'Test your entire integration before going live, with no risk to real funds.'],
        ],
        'how_it_works' => [
            'Customer selects a payment method at your checkout.',
            'Paynancial routes the transaction and returns a real-time status.',
            'A successful payment is recorded and queued for settlement.',
            'You reconcile the transaction against your order in the dashboard or via webhook.',
        ],
        'code_php'  => "\$payment = \$client->payments->create([\n    'amount'   => 50000, // in paise\n    'currency' => 'INR',\n    'receipt'  => 'order_rcpt_101',\n]);\n\necho \$payment->id;",
        'code_curl' => "curl https://api.paynancial.com/v1/payments \\\n  -u YOUR_API_KEY: \\\n  -d amount=50000 \\\n  -d currency=INR \\\n  -d receipt=order_rcpt_101",
        'related_solutions' => ['ecommerce', 'retail', 'travel'],
        'faqs' => faq_set('product:payment-gateway'),
    ],
    'payment-links' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payment Links',
        'headline' => 'Get paid without writing a line of code.',
        'subhead'  => 'Create a secure, shareable payment link in seconds and send it over email, chat, or SMS — no website required.',
        'who_for'  => 'For businesses that need to collect a one-off or occasional payment without building a checkout.',
        'title'    => 'Payment Links | Get Paid Without a Website | Paynancial',
        'description' => 'Create a secure, shareable Paynancial payment link in under a minute — fixed or open amount, expiry date, branded payment page — and send it by email, WhatsApp, SMS or on an invoice.',
        'answer'   => 'Paynancial Payment Links let you create a secure, shareable link from the dashboard in under a minute, with a fixed or open amount and an optional expiry date, and send it by email, WhatsApp, SMS or on an invoice — no website or checkout needed.',
        'step_titles' => ['Create the link', 'Share it', 'Customer pays', 'See it reflected'],
        'api'      => 'payment_links',
        'related'  => ['product:payment-gateway', 'reconciliation', 'category:accept-and-collect', 'product:payment-analytics'],
        'features' => [
            ['No code required', 'Generate a link from the dashboard in under a minute.'],
            ['Fixed or open amount', 'Set a fixed price, or let the customer enter the amount they owe.'],
            ['Expiry control', 'Set an expiry date so a link stops accepting payment automatically.'],
            ['Branded payment page', 'The page a customer lands on carries your business name and the amount due.'],
            ['Status tracking', 'See at a glance whether a link is active, paid, expired, or disabled.'],
            ['Share anywhere', 'Send by email, WhatsApp, SMS, or embed in an invoice.'],
        ],
        'how_it_works' => [
            'Create a link from your dashboard with a title and amount.',
            'Share the link with your customer through any channel.',
            'The customer pays on a secure, branded payment page.',
            'You see the payment reflected against the link immediately.',
        ],
        'code_php'  => "\$link = \$client->paymentLinks->create([\n    'title'    => 'Invoice #204',\n    'amount'   => 500000, // in paise, or omit to let the customer enter it\n    'currency' => 'INR',\n]);\n\necho \$link->short_url;",
        'code_curl' => "curl https://api.paynancial.com/v1/payment_links \\\n  -u YOUR_API_KEY: \\\n  -d title='Invoice #204' \\\n  -d amount=500000 \\\n  -d currency=INR",
        'related_solutions' => ['professional-services', 'hospitality', 'education'],
        'faqs' => faq_set('product:payment-links'),
    ],
    'payment-collection' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payment Collection',
        'headline' => 'Automate recurring and bulk collections.',
        'subhead'  => 'Collect subscription payments, instalments, or bulk dues on schedule, with reconciliation built into every cycle.',
        'who_for'  => 'For businesses collecting recurring fees, instalments, or payments from many customers at once.',
        'title'    => 'Smart Collections | Recurring & Bulk Payment Collection | Paynancial',
        'description' => 'Automate recurring subscription, instalment and bulk collections with Paynancial — scheduled collection, retries for failed attempts, customer notifications and automatic reconciliation.',
        'answer'   => 'Paynancial Payment Collection (Smart Collections) collects subscription payments, instalments and bulk dues on a schedule, retries failed attempts on a defined schedule, keeps customers informed, and reconciles every collection against the customer and cycle it belongs to.',
        'step_titles' => ['Set the schedule', 'Collection runs', 'Results recorded', 'Reconciled report'],
        'api'      => 'collections',
        'related'  => ['reconciliation', 'category:accept-and-collect', 'product:payment-analytics', 'product:payment-links'],
        'features' => [
            ['Recurring collection', 'Set up a schedule for subscription or instalment payments.'],
            ['Bulk collection', 'Collect from a batch of customers in a single run.'],
            ['Automatic reconciliation', 'Each collection is matched against the customer and cycle it belongs to.'],
            ['Retry logic', 'Failed collection attempts can be retried on a defined schedule.'],
            ['Collection reports', 'See what was collected, what failed, and what is still pending.'],
            ['Customer notifications', 'Keep customers informed as a collection is due or completed.'],
        ],
        'how_it_works' => [
            'Set up a collection schedule or upload a batch of customers.',
            'Paynancial attempts collection on the defined date.',
            'Successful and failed attempts are recorded per customer.',
            'Reconciled results appear in your collection report.',
        ],
        'code_php'  => "\$collection = \$client->collections->create([\n    'customer_id' => 'cust_7Fk21',\n    'amount'      => 150000, // in paise\n    'schedule'    => 'monthly',\n]);\n\necho \$collection->id;",
        'code_curl' => "curl https://api.paynancial.com/v1/collections \\\n  -u YOUR_API_KEY: \\\n  -d customer_id=cust_7Fk21 \\\n  -d amount=150000 \\\n  -d schedule=monthly",
        'related_solutions' => ['education', 'healthcare', 'enterprise'],
        'faqs' => faq_set('product:payment-collection'),
    ],
    'payouts' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payouts',
        'headline' => 'Send money to vendors, employees and partners.',
        'subhead'  => 'Move funds out to bank accounts and UPI IDs directly from your dashboard or API, with a clear record of every payout.',
        'who_for'  => 'For businesses paying vendors, staff, freelancers, or channel partners.',
        'title'    => 'Payouts | Send Money to Bank Accounts & UPI IDs | Paynancial',
        'description' => 'Send single or bulk payouts to bank accounts and UPI IDs with Paynancial — from the dashboard or API, with saved beneficiaries, status tracking and clear failure reasons.',
        'answer'   => 'Paynancial Payouts sends money to bank accounts and UPI IDs — one payout at a time or a whole batch — from the dashboard or the API, with saved beneficiaries, status tracking from initiated to completed, and clear reasons when a payout fails.',
        'step_titles' => ['Choose the beneficiary', 'Send', 'Processed', 'Recorded'],
        'api'      => 'payouts',
        'related'  => ['upi-payments', 'category:pay-and-move-money', 'settlements', 'product:payment-analytics'],
        'features' => [
            ['Bank & UPI payouts', 'Send funds to a bank account or UPI ID.'],
            ['Single or bulk payouts', 'Pay one recipient or an entire batch in one action.'],
            ['Payout status tracking', 'Follow each payout from initiated through to completed.'],
            ['Beneficiary management', 'Save and reuse recipient details for repeat payouts.'],
            ['Failure handling', 'Clear reasons for a failed payout so it can be corrected and retried.'],
            ['API-first', 'Trigger payouts programmatically from your own systems.'],
        ],
        'how_it_works' => [
            'Add or select a beneficiary’s bank account or UPI ID.',
            'Initiate a single payout or submit a batch.',
            'Paynancial processes the transfer and returns a status.',
            'The payout appears in your payout report once completed.',
        ],
        'code_php'  => "\$payout = \$client->payouts->create([\n    'beneficiary_id' => 'bene_3Kd91',\n    'amount'         => 250000, // in paise\n    'mode'           => 'upi',\n]);\n\necho \$payout->status;",
        'code_curl' => "curl https://api.paynancial.com/v1/payouts \\\n  -u YOUR_API_KEY: \\\n  -d beneficiary_id=bene_3Kd91 \\\n  -d amount=250000 \\\n  -d mode=upi",
        'related_solutions' => ['enterprise', 'retail', 'travel'],
        'faqs' => faq_set('product:payouts'),
    ],
    'payment-analytics' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payment Analytics',
        'headline' => 'Understand every transaction, not just the total.',
        'subhead'  => 'Dashboards and exportable reports covering performance, settlements, and reconciliation — so your finance team spends less time chasing numbers.',
        'who_for'  => 'For finance and operations teams who need visibility into payment performance without building it themselves.',
        'title'    => 'Payment Analytics | Dashboards, Settlements & Reports | Paynancial',
        'description' => 'Paynancial Payment Analytics: transaction dashboards by method, status and period, settlement visibility, reconciliation views, refund tracking, and exportable or scheduled reports.',
        'answer'   => 'Paynancial Payment Analytics records every transaction, settlement and refund as it happens and organises them into dashboards you can filter by method, status and date — with settlement visibility, reconciliation views, refund tracking and reports you can export or schedule.',
        'step_titles' => ['Recorded', 'Organised', 'Reported', 'Reconciled'],
        'api'      => 'reports',
        'related'  => ['reconciliation', 'settlements', 'refunds', 'category:financial-operations'],
        'features' => [
            ['Transaction dashboards', 'Filter and break down transactions by method, status, and time period.'],
            ['Settlement visibility', 'See what has settled, what is pending, and when it is due.'],
            ['Reconciliation views', 'Match payments against settlements and refunds in one place.'],
            ['Exportable reports', 'Download reports in formats your finance team already works with.'],
            ['Refund tracking', 'Follow a refund from request through to completion.'],
            ['Scheduled reports', 'Have recurring reports delivered without manual effort.'],
        ],
        'how_it_works' => [
            'Every transaction, settlement, and refund is recorded as it happens.',
            'The dashboard organizes this into filterable views by method, status, and date.',
            'Reports can be exported or scheduled for delivery to your team.',
            'Discrepancies surface clearly so reconciliation stays manageable.',
        ],
        'code_php'  => "\$report = \$client->reports->transactions([\n    'from'   => '2026-08-01',\n    'to'     => '2026-08-31',\n    'format' => 'csv',\n]);\n\necho \$report->download_url;",
        'code_curl' => "curl https://api.paynancial.com/v1/reports/transactions \\\n  -u YOUR_API_KEY: \\\n  -d from=2026-08-01 \\\n  -d to=2026-08-31 \\\n  -d format=csv",
        'related_solutions' => ['enterprise', 'retail', 'ecommerce'],
        'faqs' => faq_set('product:payment-analytics'),
    ],
];

if (!isset($products[$product_slug])) {
    $product_not_found = true;
    return;
}

require_once __DIR__ . '/../includes/standalone-ui.php';
require_once __DIR__ . '/../includes/developer-docs.php';
require_once __DIR__ . '/../includes/solutions-data.php';
require_once __DIR__ . '/../includes/product-capabilities.php';
require_once __DIR__ . '/../includes/product-categories.php';

$p = $products[$product_slug];
$faqs = $p['faqs'];
$path = '/products/' . $product_slug;
$parents = ['payouts' => ['Pay & Move Money', '/pay-and-move-money'], 'payment-analytics' => ['Financial Operations', '/financial-operations']];
$trail = [['Home', '/'], $parents[$product_slug] ?? ['Products', '/products'], [$p['eyebrow'], $path]];
$page_meta = sp_meta([
    'title'       => $p['title'],
    'description' => $p['description'],
    'path'        => $path,
    'h1'          => $p['headline'],
    'trail'       => $trail,
    'faqs'        => $faqs,
    'service'     => $p['eyebrow'],
]);
$resource = dev_resources()[$p['api']];
$apiAnchor = str_replace('_', '-', $p['api']);

// Industries that use this product, with the industry page's own example.
$industries = sol_industries();
$stem = rtrim($p['eyebrow'], 's');
$uses = [];
foreach ($p['related_solutions'] as $legacySlug) {
    $slug = sol_legacy_anchors()[$legacySlug] ?? $legacySlug;
    $ind = $industries[$slug];
    $text = $ind['lead'];
    foreach ($ind['scenarios'] as [$who, $what]) {
        if (stripos($what, $stem) !== false) {
            $text = $who . ': ' . lcfirst($what);
            break;
        }
    }
    $uses[] = [$ind['name'], $text, sol_url($slug)];
}

sp_track_view('product_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Products · ' . $p['eyebrow'],
    'h1'        => $p['headline'],
    'lead'      => $p['subhead'],
    'primary'   => [cta_label(), '/contact?intent=sales&product=' . $product_slug, 'cta_click'],
    'secondary' => ['Read the API docs', '/developers/api-reference#' . $apiAnchor, 'api_reference_click'],
    'values'    => array_slice(array_map(fn ($f) => $f[0], $p['features']), 0, 4),
    'aside'     => sp_code(dev_resource_code($resource), 'POST ' . $resource['path']),
]);
?>

<?php sp_band_open('overview'); ?>
  <div class="sp-split">
    <?php sp_head('overview', 'Overview', 'What ' . $p['eyebrow'] . ' does.'); ?>
    <div class="sp-prose reveal">
      <p><strong><?= e($p['answer']) ?></strong></p>
      <p><?= e($p['who_for']) ?></p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('features', 'ink'); ?>
  <?php sp_head('features', 'What\'s included', 'Everything ' . $p['eyebrow'] . ' gives you.'); ?>
  <?php sp_answers($p['features']); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('how-it-works', 'dim'); ?>
  <?php sp_head('how-it-works', 'How it works', 'From setup to settlement.'); ?>
  <?php sp_steps(array_map(null, $p['step_titles'], $p['how_it_works'])); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('industries'); ?>
  <?php sp_head('industries', 'Built for your industry', 'Where ' . $p['eyebrow'] . ' is used.', 'Illustrative examples from Paynancial\'s industry pages.'); ?>
  <?php sp_related($uses); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('developers', 'dim'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('developers', 'For developers', 'Call it from your own code.', $resource['does'] . ' The same API powers the dashboard, your systems and any agent working for you.'); ?>
      <div class="sp-prose reveal">
        <ul>
          <li><a class="inline-link" href="/developers/api-reference#<?= e($apiAnchor) ?>">API Reference: <?= e($resource['name']) ?></a></li>
          <li><a class="inline-link" href="/developers/webhooks">Webhooks</a> — real-time events for payments, payouts, refunds and settlements.</li>
          <li><a class="inline-link" href="/sandbox">Sandbox</a> — test with no real funds involved.</li>
        </ul>
      </div>
    </div>
    <?php sp_table(['Parameter', 'Description'], array_map(fn ($x) => ['<code>' . e($x[0]) . '</code>', $x[1]], $resource['params']), $resource['name'] . ' parameters'); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_regulatory('product:' . $product_slug, strtolower($p['eyebrow']) . ' in India'); ?>

<?php sp_band_open('faq'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', $p['eyebrow'] . ' questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related', 'dim'); ?>
  <?php sp_head('related', 'Related', 'Works well with.'); ?>
  <?php sp_related(array_map('pc_related_card', $p['related'])); ?>
  <?php if ($product_slug === 'payment-gateway'): ?>
  <?php business_services_crosslink('Need to set up your business first?', 'Paynancial Business Services can help with company incorporation and the registrations a business typically needs before going live with payments.'); ?>
  <?php endif; ?>
<?php sp_band_close(); ?>

<?php sp_cta('Ready to start with ' . $p['eyebrow'] . '?', 'Tell us how your business takes and moves money, and we will help you get started.', [
    [cta_label(), '/contact?intent=sales&product=' . $product_slug, 'cta_click'],
    ['Explore all products', '/products', 'cta_click'],
]); ?>
