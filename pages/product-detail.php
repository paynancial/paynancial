<?php
/**
 * Product detail template: /products/{slug}
 * $product_slug is set by the front controller. Sets $product_not_found
 * and returns early for an unknown slug so the controller can render 404.
 */
$products = [
    'payment-gateway' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payment Gateway',
        'headline' => 'Accept every way your customers want to pay.',
        'subhead'  => 'One integration for cards, UPI, netbanking and wallets — with clear, reconciled settlement reporting behind every transaction.',
        'who_for'  => 'For businesses taking payments on a website, app, or custom checkout.',
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
        'faqs' => [
            ['Which payment methods are supported?', 'Cards, UPI, netbanking and wallets through a single integration — see the API Reference for the full method list.'],
            ['Can I test before going live?', 'Yes — sandbox API keys let you run a complete integration test with no real funds involved.'],
        ],
    ],
    'payment-links' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payment Links',
        'headline' => 'Get paid without writing a line of code.',
        'subhead'  => 'Create a secure, shareable payment link in seconds and send it over email, chat, or SMS — no website required.',
        'who_for'  => 'For businesses that need to collect a one-off or occasional payment without building a checkout.',
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
        'faqs' => [
            ['Do I need a website to use payment links?', 'No — a payment link works entirely on its own; you only need a way to share the link, like email or chat.'],
            ['Can a link be reused?', 'A link stays active until it is paid, expires, or you disable it, so it can be shared with more than one customer if left open.'],
        ],
    ],
    'payment-collection' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payment Collection',
        'headline' => 'Automate recurring and bulk collections.',
        'subhead'  => 'Collect subscription payments, instalments, or bulk dues on schedule, with reconciliation built into every cycle.',
        'who_for'  => 'For businesses collecting recurring fees, instalments, or payments from many customers at once.',
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
        'faqs' => [
            ['What happens if a collection fails?', 'A failed attempt is recorded with a reason code and can be retried on your defined schedule.'],
            ['Can I collect from a batch of customers at once?', 'Yes — bulk collection lets you submit a batch and track each customer’s result individually.'],
        ],
    ],
    'payouts' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payouts',
        'headline' => 'Send money to vendors, employees and partners.',
        'subhead'  => 'Move funds out to bank accounts and UPI IDs directly from your dashboard or API, with a clear record of every payout.',
        'who_for'  => 'For businesses paying vendors, staff, freelancers, or channel partners.',
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
        'faqs' => [
            ['Can I pay more than one person at once?', 'Yes — bulk payouts let you submit a batch of transfers in a single request.'],
            ['What payout modes are supported?', 'Bank transfer and UPI are both supported for sending funds to a beneficiary.'],
        ],
    ],
    'payment-analytics' => [
        'icon'     => '◆',
        'eyebrow'  => 'Payment Analytics',
        'headline' => 'Understand every transaction, not just the total.',
        'subhead'  => 'Dashboards and exportable reports covering performance, settlements, and reconciliation — so your finance team spends less time chasing numbers.',
        'who_for'  => 'For finance and operations teams who need visibility into payment performance without building it themselves.',
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
        'faqs' => [
            ['Can I export data for my accounting system?', 'Yes — reports can be exported in common formats for use outside the dashboard.'],
            ['Does this show real customer data or sample data?', 'Analytics reflect your own account’s real transactions and settlements — there is no sample or simulated data in your dashboard.'],
        ],
    ],
];

if (!isset($products[$product_slug])) {
    $product_not_found = true;
    return;
}

$p = $products[$product_slug];
$solutionLabels = [
    'ecommerce' => 'E-Commerce', 'travel' => 'Travel', 'healthcare' => 'Healthcare', 'education' => 'Education',
    'retail' => 'Retail', 'hospitality' => 'Hospitality', 'professional-services' => 'Professional Services', 'enterprise' => 'Enterprise',
];
$productOrder = ['payment-gateway', 'payment-links', 'payment-collection', 'payouts', 'payment-analytics'];

$page_meta = [
    'title'       => $p['eyebrow'] . ' | Paynancial',
    'description' => $p['subhead'],
];
?>
<section style="padding-top:56px;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow"><?= e($p['eyebrow']) ?></span>
      <h1><?= e($p['headline']) ?></h1>
      <p class="lead"><?= e($p['subhead']) ?></p>
      <p class="text-muted" style="margin-top:8px;"><?= e($p['who_for']) ?></p>
      <div class="hero-actions" style="margin-top:24px;">
        <a href="/contact?intent=sales&product=<?= e($product_slug) ?>" class="btn btn-primary">Get Started</a>
        <a href="/contact?intent=sales&product=<?= e($product_slug) ?>" class="btn btn-outline">Talk to Sales</a>
      </div>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="section-head reveal">
      <h2>What's included</h2>
    </div>
    <div class="grid grid-3">
      <?php foreach ($p['features'] as [$title, $desc]): ?>
        <div class="card reveal">
          <span class="card-icon"><?= e($p['icon']) ?></span>
          <h3><?= e($title) ?></h3>
          <p><?= e($desc) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">How it works</span>
      <h2>From setup to settlement</h2>
    </div>
    <div class="journey reveal">
      <?php foreach ($p['how_it_works'] as $i => $step): ?>
        <div class="journey-step">
          <div class="num"><?= $i + 1 ?></div>
          <strong><?= e($step) ?></strong>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle" id="api-sample">
  <div class="container">
    <div style="display:grid;gap:48px;grid-template-columns:1fr;align-items:center;" class="grid-2">
      <div class="reveal">
        <span class="eyebrow">For Developers</span>
        <h2>Call it directly from your own code</h2>
        <p class="lead">Every product on this page is also a documented API endpoint — build it into your own systems when the dashboard isn't enough.</p>
        <a href="/developers" class="btn btn-primary" style="margin-top:24px;">Explore API Documentation</a>
      </div>
      <div class="code-panel reveal">
        <div class="code-tabs">
          <button class="code-tab is-active" data-lang="php">PHP</button>
          <button class="code-tab" data-lang="curl">cURL</button>
        </div>
        <div class="code-body">
          <button class="copy-btn" type="button">Copy</button>
          <pre data-code-block="php"><code>$client = new Paynancial\Client('YOUR_API_KEY');

<?= $p['code_php'] ?></code></pre>
          <pre data-code-block="curl" style="display:none"><code><?= $p['code_curl'] ?></code></pre>
        </div>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">FAQs</span>
      <h2>Common questions</h2>
    </div>
    <div class="grid grid-2">
      <?php foreach ($p['faqs'] as [$q, $a]): ?>
        <div class="card reveal">
          <h3 style="font-size:1rem;"><?= e($q) ?></h3>
          <p style="margin-top:8px;"><?= e($a) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Explore More</span>
      <h2>Other Paynancial products</h2>
    </div>
    <div class="pill-list reveal">
      <?php foreach ($productOrder as $slug): if ($slug === $product_slug) continue; ?>
        <a class="pill" href="/products/<?= e($slug) ?>"><?= e($products[$slug]['eyebrow']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($p['related_solutions'])): ?>
      <div class="section-head reveal" style="margin-top:36px;">
        <span class="eyebrow">Used By</span>
        <h2>Common in these industries</h2>
      </div>
      <div class="pill-list reveal">
        <?php foreach ($p['related_solutions'] as $slug): ?>
          <a class="pill" href="/solutions#<?= e($slug) ?>"><?= e($solutionLabels[$slug] ?? $slug) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta-band reveal">
      <h2>Ready to start with <?= e($p['eyebrow']) ?>?</h2>
      <div class="hero-actions" style="justify-content:center;margin-top:24px;">
        <a href="/contact?intent=sales&product=<?= e($product_slug) ?>" class="btn btn-primary">Get Started</a>
        <a href="/contact?intent=sales&product=<?= e($product_slug) ?>" class="btn btn-outline">Talk to Sales</a>
      </div>
    </div>
  </div>
</section>
