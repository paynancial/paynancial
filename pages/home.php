<?php
$page_meta = [
    'title'       => 'Paynancial | Smarter Payment Infrastructure for Growing Businesses',
    'description' => 'Paynancial helps growing businesses accept payments, collect dues, send payouts, and understand every transaction — through one platform and a developer-first API.',
    'schema'      => organization_schema(),
];

$services = [
    ['icon' => '◆', 'title' => 'Payment Gateway', 'benefit' => 'Accept cards, UPI, netbanking, and wallets.', 'desc' => 'One integration for every major payment method, with clear settlement reporting behind each transaction.', 'href' => '/products/payment-gateway'],
    ['icon' => '◆', 'title' => 'Payment Links', 'benefit' => 'Collect payments without writing code.', 'desc' => 'Generate a secure, shareable payment link in seconds and send it by email, chat, or SMS.', 'href' => '/products/payment-links'],
    ['icon' => '◆', 'title' => 'Smart Collections', 'benefit' => 'Automate recurring and bulk collections.', 'desc' => 'Run subscription, instalment, or batch collections on schedule, with reconciliation built in.', 'href' => '/products/payment-collection'],
    ['icon' => '◆', 'title' => 'Payouts', 'benefit' => 'Pay vendors, employees, and partners.', 'desc' => 'Send funds to bank accounts or UPI IDs directly from your dashboard or API.', 'href' => '/products/payouts'],
    ['icon' => '◆', 'title' => 'Payment Analytics', 'benefit' => 'See performance, not just totals.', 'desc' => 'Dashboards and exportable reports covering transactions, settlements, and reconciliation.', 'href' => '/products/payment-analytics'],
    ['icon' => '◆', 'title' => 'Payment APIs', 'benefit' => 'Build payments into your own product.', 'desc' => 'A documented REST API and webhooks so your engineering team can integrate on their own terms.', 'href' => '/developers'],
];

$industries = [
    'ecommerce'             => ['E-Commerce', 'An online store uses the Payment Gateway for checkout and Analytics to track conversion by method.'],
    'travel'                => ['Travel', 'A travel agency uses Payment Links for booking deposits and Payouts to settle with partners.'],
    'healthcare'            => ['Healthcare', 'A clinic uses Smart Collections to bill patients for recurring treatment plans.'],
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
        <a href="/contact?intent=sales" class="btn btn-outline"><?= e(cta_label()) ?></a>
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
    <?php business_services_crosslink('Starting a new business?', 'Paynancial Business Services supports company incorporation, registrations and compliance — so your business is ready before you start accepting payments.'); ?>
  </div>
</section>

<section class="section-subtle" aria-labelledby="workflow-heading">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow">How It Works Together</span>
      <h2 id="workflow-heading">One Flow, From Payment to Insight</h2>
      <p>Gateway, Collection, Payouts, and Analytics aren't separate tools — they're one connected flow.</p>
      <p style="margin-top:10px;"><a class="card-link" href="/ai-intelligence">See how AI & Intelligence works on this flow →</a></p>
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
        <a class="card reveal" href="/solutions/<?= e($slug === 'ecommerce' ? 'e-commerce' : $slug) ?>">
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

<?php
require_once __DIR__ . '/../includes/business-services-ui.php';
// Company incorporation selector. India is a confirmed Business Services
// offering; the international tabs are research jurisdictions (not approved
// as served — see bs_jurisdiction_approved()), so they only link to the
// jurisdiction information page and an availability enquiry.
$incorpTabs = [
    'india' => [
        'name' => 'India', 'iso' => 'in', 'served' => true,
        'title' => 'Company incorporation in India',
        'lead' => 'Incorporate in India with structured documentation and expert guidance — from choosing a structure to your certificate, then GST, trademarks and compliance.',
        'options' => array_map(fn ($slug) => [bs_service($slug)['short'] ?? bs_service($slug)['name'], bs_service($slug)['summary'], bs_url($slug)],
            ['private-limited-company', 'llp-registration', 'opc-registration', 'partnership-registration']),
        'primary' => ['Start Your Business', bs_url('company-incorporation')],
        'secondary' => ['All Business Services', bs_url()],
    ],
];
foreach ([
    'uae' => [['Mainland company', 'Ask about setting up on the UAE mainland.', 'uae-mainland'], ['Free zone company', 'Ask about setting up in a UAE free zone.', 'uae-free-zone']],
    'singapore' => [['New company', 'Ask about setting up a new company in Singapore.', 'singapore-new-company'], ['Expand from India', 'Ask about taking your Indian business to Singapore.', 'singapore-expansion']],
    'hong-kong' => [['New company', 'Ask about setting up a new company in Hong Kong.', 'hong-kong-new-company'], ['Expand from India', 'Ask about taking your Indian business to Hong Kong.', 'hong-kong-expansion']],
    'united-kingdom' => [['New company', 'Ask about setting up a new company in the UK.', 'uk-new-company'], ['Expand from India', 'Ask about taking your Indian business to the UK.', 'uk-expansion']],
] as $slug => $opts) {
    $j = bs_jurisdiction($slug);
    $label = $j['short'] ?? $j['name'];
    $served = bs_jurisdiction_approved($j);
    $incorpTabs[$slug] = [
        'name' => $label, 'iso' => $j['iso'], 'served' => $served,
        'title' => $served ? 'Company incorporation in ' . $j['name'] : 'Setting up in ' . $j['name'] . '?',
        'lead' => $j['descriptor'] . ' Requirements differ by setup route and business activity. Our team confirms what is available for your plans before any work begins.',
        'options' => array_map(fn ($o) => [$o[0], $o[1], bs_enquiry_url($o[2])], $opts),
        'primary' => ['Ask about availability', bs_enquiry_url($slug)],
        'secondary' => [$label . ' jurisdiction information', bs_jurisdiction_url($slug)],
    ];
}
?>
<section class="section-subtle home-incorp" aria-labelledby="incorp-heading">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow">Business Services</span>
      <h2 id="incorp-heading">Start your company — in India or abroad.</h2>
      <p>Company incorporation and registrations in India, and guidance for founders looking at the UAE, Singapore, Hong Kong or the UK.</p>
    </div>

    <div class="incorp reveal" data-tabs>
      <div class="incorp-tabs" role="tablist" aria-label="Choose where to incorporate">
        <?php $first = true; foreach ($incorpTabs as $slug => $t): ?>
        <button type="button" class="incorp-tab" role="tab" id="incorp-tab-<?= e($slug) ?>" aria-controls="incorp-panel-<?= e($slug) ?>" aria-selected="<?= $first ? 'true' : 'false' ?>"<?= $first ? '' : ' tabindex="-1"' ?>>
          <?= bs_flag(['iso' => $t['iso']], 'incorp-flag') ?>
          <span><?= e($t['name']) ?></span>
        </button>
        <?php $first = false; endforeach; ?>
      </div>

      <?php foreach ($incorpTabs as $slug => $t): ?>
      <div class="incorp-panel" role="tabpanel" id="incorp-panel-<?= e($slug) ?>" aria-labelledby="incorp-tab-<?= e($slug) ?>" tabindex="0">
        <div class="incorp-intro">
          <span class="incorp-status<?= $t['served'] ? ' is-available' : '' ?>"><?= $t['served'] ? 'Available' : 'Availability confirmed on enquiry' ?></span>
          <h3><?= e($t['title']) ?></h3>
          <p><?= e($t['lead']) ?></p>
          <div class="hero-actions">
            <a class="btn btn-primary" href="<?= e($t['primary'][1]) ?>"><?= e($t['primary'][0]) ?></a>
            <a class="btn btn-outline" href="<?= e($t['secondary'][1]) ?>"><?= e($t['secondary'][0]) ?></a>
          </div>
        </div>
        <ul class="incorp-options">
          <?php foreach ($t['options'] as [$label, $desc, $href]): ?>
          <li><a class="incorp-option" href="<?= e($href) ?>"><strong><?= e($label) ?></strong><span><?= e($desc) ?></span><span class="incorp-go" aria-hidden="true">→</span></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>

    <p class="text-center reveal" style="margin-top:28px;"><a class="card-link" href="<?= e(bs_jurisdiction_url()) ?>">Explore all jurisdictions →</a></p>
  </div>
</section>

