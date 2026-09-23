<?php
$page_meta = [
    'title'       => 'Paynancial | Smarter Payment Infrastructure for Growing Businesses',
    'description' => 'Paynancial helps growing businesses accept payments, collect dues, send payouts, and understand every transaction — through one platform and a developer-first API.',
    'schema'      => organization_schema(),
];

$services = [
    ['icon' => '◆', 'title' => 'Payment Gateway', 'benefit' => 'Accept cards, UPI, netbanking, and wallets.', 'desc' => 'One integration for every major payment method, with clear settlement reporting behind each transaction.', 'href' => '/products/payment-gateway'],
    ['icon' => '◆', 'title' => 'Payment Links', 'benefit' => 'Collect payments without writing code.', 'desc' => 'Generate a secure, shareable payment link in seconds and send it by email, chat, or SMS.', 'href' => '/products/payment-links'],
    ['icon' => '◆', 'title' => 'Payment Collection', 'benefit' => 'Automate recurring and bulk collections.', 'desc' => 'Run subscription, instalment, or batch collections on schedule, with reconciliation built in.', 'href' => '/products/payment-collection'],
    ['icon' => '◆', 'title' => 'Payouts', 'benefit' => 'Pay vendors, employees, and partners.', 'desc' => 'Send funds to bank accounts or UPI IDs directly from your dashboard or API.', 'href' => '/products/payouts'],
    ['icon' => '◆', 'title' => 'Payment Analytics', 'benefit' => 'See performance, not just totals.', 'desc' => 'Dashboards and exportable reports covering transactions, settlements, and reconciliation.', 'href' => '/products/payment-analytics'],
    ['icon' => '◆', 'title' => 'Payment APIs', 'benefit' => 'Build payments into your own product.', 'desc' => 'A documented REST API and webhooks so your engineering team can integrate on their own terms.', 'href' => '/developers'],
];

$industries = [
    'ecommerce'             => ['E-Commerce', 'An online store uses the Payment Gateway for checkout and Analytics to track conversion by method.'],
    'travel'                => ['Travel', 'A travel agency uses Payment Links for booking deposits and Payouts to settle with partners.'],
    'healthcare'            => ['Healthcare', 'A clinic uses Payment Collection to bill patients for recurring treatment plans.'],
    'education'             => ['Education', 'A training institute automates monthly fee billing across hundreds of students.'],
    'retail'                => ['Retail', 'A retail chain uses the Gateway online and Analytics to reconcile sales across stores.'],
    'hospitality'           => ['Hospitality', 'A hotel uses Payment Links for advance deposits and the Gateway for on-site payments.'],
    'professional-services' => ['Professional Services', 'A consulting firm invoices clients with Payment Links and pays associates via Payouts.'],
    'enterprise'            => ['Enterprise', 'A large enterprise runs Gateway, Collection, Payouts, and Analytics behind its own finance systems via the API.'],
];
?>
<section class="hero">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Paynancial Technology Pvt. Ltd.</span>
      <h1>Smarter Payment Infrastructure for Growing Businesses.</h1>
      <p class="lead">Accept payments, collect dues, send payouts, and understand every transaction — through one platform, a clear dashboard, and a developer-first API.</p>
      <div class="hero-actions">
        <a href="/contact" class="btn btn-primary">Get Started</a>
        <a href="/contact?intent=sales" class="btn btn-outline">Talk to Sales</a>
      </div>
    </div>

    <div class="reveal">
      <div class="dash-card" aria-hidden="true">
        <span class="badge-float top">⚡ Real-time processing</span>
        <div class="dash-card-head">
          <strong>Payment Operations</strong>
          <span class="dash-live">Product preview</span>
        </div>
        <div class="dash-grid">
          <div class="dash-stat"><span>Transactions</span><strong>Multi-method</strong></div>
          <div class="dash-stat accent"><span>Settlements</span><strong>Auto-reconciled</strong></div>
          <div class="dash-stat"><span>Payouts</span><strong>Bank &amp; UPI</strong></div>
          <div class="dash-stat"><span>Reports</span><strong>Exportable</strong></div>
        </div>
        <div class="dash-bars">
          <span style="height:40%"></span><span style="height:65%"></span><span style="height:52%"></span>
          <span style="height:80%"></span><span style="height:58%"></span><span style="height:90%"></span>
          <span style="height:70%"></span><span style="height:100%"></span>
        </div>
        <div class="dash-list">
          <div class="dash-row"><span>UPI · Retail order</span><span class="status success">Success</span></div>
          <div class="dash-row"><span>Card · Subscription renewal</span><span class="status success">Success</span></div>
          <div class="dash-row"><span>Netbanking · Vendor payout</span><span class="status pending">Pending</span></div>
        </div>
        <span class="badge-float bottom">✔ Reconciled automatically</span>
      </div>
    </div>
  </div>
</section>

<section id="products" aria-labelledby="products-heading">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Platform</span>
      <h2 id="products-heading">Payment Services Built for Every Business</h2>
      <p>A modular payments stack — use one service or the entire platform.</p>
    </div>
    <div class="grid grid-3">
      <?php foreach ($services as $s): ?>
        <div class="card reveal">
          <span class="card-icon"><?= e($s['icon']) ?></span>
          <h3><?= e($s['title']) ?></h3>
          <p style="font-weight:600;color:var(--text);margin-top:4px;"><?= e($s['benefit']) ?></p>
          <p style="margin-top:6px;"><?= e($s['desc']) ?></p>
          <a class="card-link" href="<?= e($s['href']) ?>">Explore service →</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle" aria-labelledby="workflow-heading">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow">How It Works Together</span>
      <h2 id="workflow-heading">One Flow, From Payment to Insight</h2>
      <p>Gateway, Collection, Payouts, and Analytics aren't separate tools — they're one connected flow.</p>
    </div>
    <div class="journey reveal">
      <?php
      $workflow = ['Customer pays', 'Payment accepted', 'Collection reconciled', 'Funds settled', 'Analytics reviewed'];
      foreach ($workflow as $i => $step): ?>
        <div class="journey-step">
          <div class="num"><?= $i + 1 ?></div>
          <strong><?= e($step) ?></strong>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section aria-labelledby="industries-heading">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Solutions</span>
      <h2 id="industries-heading">Built for Every Business</h2>
      <p>Payment infrastructure shaped around how your industry actually works.</p>
    </div>
    <div class="grid grid-4">
      <?php foreach ($industries as $slug => [$title, $useCase]): ?>
        <a class="card reveal" href="/solutions#<?= e($slug) ?>">
          <h3 style="font-size:1.05rem;"><?= e($title) ?></h3>
          <p style="margin-top:8px;font-size:0.85rem;"><?= e($useCase) ?></p>
          <span class="card-link" style="margin-top:12px;">View solution →</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle" aria-labelledby="developers-heading">
  <div class="container">
    <div style="display:grid;gap:48px;grid-template-columns:1fr;align-items:center;" class="grid-2">
      <div class="reveal">
        <span class="eyebrow">Developers</span>
        <h2 id="developers-heading">Built for Developers</h2>
        <p class="lead">Integrate payments in minutes with clear API documentation, sandbox access, webhooks, and hands-on integration support.</p>
        <a href="/developers" class="btn btn-primary" style="margin-top:24px;">Explore API Documentation</a>
      </div>
      <div class="code-panel reveal">
        <div class="code-tabs">
          <button class="code-tab is-active" data-lang="php">PHP</button>
          <button class="code-tab" data-lang="js">JavaScript</button>
          <button class="code-tab" data-lang="curl">cURL</button>
        </div>
        <div class="code-body">
          <button class="copy-btn" type="button">Copy</button>
          <pre data-code-block="php"><code>$client = new Paynancial\Client('YOUR_API_KEY');

$payment = $client->payments->create([
    'amount'   =&gt; 50000, // in paise
    'currency' =&gt; 'INR',
    'receipt'  =&gt; 'order_rcpt_101',
]);

echo $payment->id;</code></pre>
          <pre data-code-block="js" style="display:none"><code>const client = new Paynancial({ key: 'YOUR_API_KEY' });

const payment = await client.payments.create({
  amount: 50000,
  currency: 'INR',
  receipt: 'order_rcpt_101',
});

console.log(payment.id);</code></pre>
          <pre data-code-block="curl" style="display:none"><code>curl https://api.paynancial.com/v1/payments \
  -u YOUR_API_KEY: \
  -d amount=50000 \
  -d currency=INR \
  -d receipt=order_rcpt_101</code></pre>
        </div>
      </div>
    </div>
  </div>
</section>

<section aria-labelledby="security-heading">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Trust &amp; Security</span>
      <h2 id="security-heading">Security Built Into Every Transaction</h2>
      <p>Protecting customer data and payment integrity is foundational to how we build.</p>
    </div>
    <div class="grid grid-4">
      <?php
      $sec = [
          ['Encrypted Communication', 'All traffic between your business, your customers, and Paynancial is served over HTTPS/TLS.'],
          ['Controlled Access', 'Role-based permissions and audited access across every dashboard and API key.'],
          ['Transaction Monitoring', 'Ongoing monitoring of platform activity for unusual behaviour.'],
          ['Fraud-Risk Controls', 'Layered checks designed to reduce fraudulent transaction attempts.'],
      ];
      foreach ($sec as [$title, $desc]): ?>
        <div class="card reveal">
          <span class="card-icon">🛡</span>
          <h3><?= e($title) ?></h3>
          <p><?= e($desc) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="text-muted reveal" style="margin-top:24px;font-size:0.85rem;">Security and compliance information is published as it is formally verified — see our <a href="/security">Security &amp; Compliance</a> page for current status.</p>
  </div>
</section>

<section class="section-subtle" aria-labelledby="visibility-heading">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow">Analytics &amp; Operations</span>
      <h2 id="visibility-heading">Complete Payment Visibility</h2>
      <p>Every transaction, settlement, refund, and reconciliation record — in one console, with reports and alerts when something needs attention.</p>
    </div>
    <div class="grid grid-3">
      <?php
      $ops = [
          ['Transactions', 'Every payment attempt, with method, status, and timestamp.'],
          ['Settlements', 'When funds move to your account and what period they cover.'],
          ['Refunds', 'Track a refund from request through to completion.'],
          ['Reconciliation', 'Match payments against settlements and refunds in one view.'],
          ['Reports', 'Exportable reports in the formats your finance team already uses.'],
          ['Alerts', 'Be notified when a transaction, settlement, or payout needs attention.'],
      ];
      foreach ($ops as [$title, $desc]): ?>
        <div class="card reveal">
          <span class="card-icon">▣</span>
          <h3><?= e($title) ?></h3>
          <p><?= e($desc) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center reveal" style="margin-top:32px;">
      <a href="/products/payment-analytics" class="btn btn-outline">Explore Payment Analytics</a>
    </div>
  </div>
</section>

<section aria-labelledby="proof-heading">
  <div class="container">
    <div class="cta-band reveal" style="text-align:center;">
      <h2 id="proof-heading">Built for businesses that need reliable payment operations.</h2>
      <p class="lead" style="max-width:560px;margin-inline:auto;margin-top:14px;">We publish real customer stories as they're approved — not placeholder quotes.</p>
    </div>
  </div>
</section>

<section aria-labelledby="cta-heading">
  <div class="container">
    <div class="cta-band reveal">
      <h2 id="cta-heading">Start accepting and managing payments with Paynancial.</h2>
      <p class="lead" style="max-width:560px;margin-inline:auto;margin-top:14px;">Talk to our team or get started with a Paynancial account today.</p>
      <div class="hero-actions" style="justify-content:center;margin-top:28px;">
        <a href="/contact" class="btn btn-primary">Get Started</a>
        <a href="/contact?intent=sales" class="btn btn-outline">Talk to Sales</a>
      </div>
    </div>
  </div>
</section>
