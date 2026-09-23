<?php
require_once __DIR__ . '/../includes/product-categories.php';
$page_meta = [
    'title'       => 'Products | Paynancial Payment Platform',
    'description' => 'Payment Gateway, Payouts, Financial Operations, AI & Intelligence, Embedded Finance and a full Developer Platform — the complete Paynancial product suite.',
];
$featured = [
    'payment-gateway'    => ['Payment Gateway', 'Accept cards, UPI, netbanking and wallets through a single, reliable integration.'],
    'payment-links'      => ['Payment Links', 'Collect payments without code — generate a secure, shareable link in seconds.'],
    'payment-collection' => ['Smart Collections', 'Automate recurring and bulk collections with reconciliation built in.'],
    'payouts'            => ['Payouts', 'Send payments to vendors, employees and partners from your dashboard or API.'],
    'payment-analytics'  => ['Payment Analytics', 'Track performance, settlements and reconciliation across every transaction.'],
];
$catalog = [
    'Accept & Collect' => [
        ['Payment Gateway', '/products/payment-gateway'],
        ['Payment Links', '/products/payment-links'],
        ['Payment Pages', '/products/payment-pages'],
        ['UPI Payments', '/products/upi-payments'],
        ['Recurring Payments', '/products/payment-collection'],
        ['Subscription Billing', '/products/payment-collection'],
        ['Smart Collections', '/products/payment-collection'],
    ],
    'Pay & Move Money' => [
        ['Payouts', '/products/payouts'],
        ['Bulk Payouts', '/products/bulk-payouts'],
        ['Vendor Payments', '/products/vendor-payments'],
        ['Employee Payments', '/products/employee-payments'],
        ['Partner Payments', '/products/partner-payments'],
        ['International Payments', '/products/international-payments'],
    ],
    'Financial Operations' => [
        ['Reconciliation', '/products/reconciliation'],
        ['Settlements', '/products/settlements'],
        ['Refunds', '/products/refunds'],
        ['Chargebacks', '/products/chargebacks'],
        ['Invoice Management', '/products/invoice-management'],
        ['Expense Management', '/products/expense-management'],
        ['Finance Analytics', '/products/payment-analytics'],
        ['MIS & Reports', '/products/mis-reports'],
    ],
    'AI & Intelligence' => [
        ['Paynancial AI', '/ai-intelligence/paynancial-ai'],
        ['AI Fraud Detection', '/ai-intelligence/fraud-detection'],
        ['AI Reconciliation', '/ai-intelligence/reconciliation'],
        ['AI Financial Assistant', '/ai-intelligence/financial-assistant'],
        ['AI Cash-Flow Intelligence', '/ai-intelligence/cash-flow-intelligence'],
        ['AI Revenue Forecasting', '/ai-intelligence/revenue-forecasting'],
    ],
    'Embedded Finance' => [
        ['Embedded Payments', '/products/embedded-payments'],
        ['Embedded Payouts', '/products/embedded-payouts'],
        ['Embedded Billing', '/products/embedded-billing'],
        ['Wallet Infrastructure', '/products/wallet-infrastructure'],
        ['Split Payments', '/products/split-payments'],
        ['White-Label Payments', '/products/white-label-payments'],
    ],
    'Developer Platform' => [
        ['Payment APIs', '/developers/payment-apis'],
        ['Payout APIs', '/developers/payout-apis'],
        ['SDKs', '/developers/sdks'],
        ['Webhooks', '/developers/webhooks'],
        ['Sandbox', '/sandbox'],
        ['Developer Hub', '/developers'],
    ],
];
?>
<section style="padding-top:56px;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Products</span>
      <h1>A complete, modular payments platform.</h1>
      <p class="lead">Adopt one product or the full Paynancial stack — every module is built to work together, and to be called just as reliably by an autonomous AI agent as by a person at a keyboard.</p>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container grid grid-3">
    <?php foreach ($featured as $slug => [$title, $desc]): ?>
      <a class="card reveal" href="/products/<?= e($slug) ?>">
        <span class="card-icon">◆</span>
        <h3><?= e($title) ?></h3>
        <p><?= e($desc) ?></p>
        <span class="card-link">Explore service →</span>
      </a>
    <?php endforeach; ?>
    <a class="card reveal" href="/developers">
      <span class="card-icon">◆</span>
      <h3>Payment APIs</h3>
      <p>Build custom payment experiences with developer-first APIs and webhooks.</p>
      <span class="card-link">Explore API documentation →</span>
    </a>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Full Catalog</span>
      <h2>Every product, by category.</h2>
    </div>
    <div class="ledger reveal catalog-list">
      <?php foreach ($catalog as $category => $items): ?>
        <div class="ledger-row catalog-row" id="cat-<?= e(strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($category)))) ?>">
          <?php $catSlug = cat_for_column($category); ?>
          <span class="ledger-tag"><?php if ($catSlug): ?><a href="<?= e(cat_url($catSlug)) ?>"><?= e($category) ?> →</a><?php elseif ($category === 'Developer Platform'): ?><a href="/developers"><?= e($category) ?> →</a><?php else: ?><?= e($category) ?><?php endif; ?></span>
          <div class="pill-list">
            <?php foreach ($items as [$label, $href]): ?>
              <a class="pill" href="<?= e($href) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">The Agentic AI Era</span>
      <h2>Built for infrastructure a human calls — and infrastructure an agent calls.</h2>
    </div>
    <div class="story">
      <p class="story-lead reveal">A growing share of the requests hitting a payment platform in the next few years won't come from a person clicking a button. They'll come from an AI agent — a bookkeeping assistant reconciling a ledger, a procurement bot approving a vendor invoice, a customer-support agent issuing a refund — acting on a business's behalf, inside limits the business set.</p>
      <div class="reveal">
        <p>That shift changes what "good API design" means. An agent retries a failed request without a human noticing, so idempotency has to be correct, not just documented. An agent parses an error programmatically, so error codes have to be structured and consistent, not prose written for a person to read once. An agent acts continuously, not in business-hours bursts, so webhooks and rate limits have to assume always-on traffic. Every product on this page — Payment Gateway through Payouts, Reconciliation through the AI &amp; Intelligence catalog above — is built API-first for exactly that reason, whether the caller today is a developer's script or tomorrow's autonomous finance agent.</p>
        <p>What that looks like changes with the size of the business behind it — the products stay the same, only how much of the work an agent is trusted to do on its own scales up.</p>
      </div>
    </div>
    <div class="journey reveal" style="margin-top:36px;">
      <div class="journey-step">
        <div class="num">1</div>
        <strong>Solo &amp; small business</strong>
        <span>An AI bookkeeping assistant reconciles the day's transactions against the bank feed and flags what looks wrong — the owner still approves anything unusual.</span>
      </div>
      <div class="journey-step">
        <div class="num">2</div>
        <strong>Growing business</strong>
        <span>An AI ops assistant routes and initiates vendor payouts within a pre-set limit, escalating anything above it — Payouts and AI Reconciliation doing the repetitive work.</span>
      </div>
      <div class="journey-step">
        <div class="num">3</div>
        <strong>Platform &amp; SaaS</strong>
        <span>Embedded, agent-driven billing recovers failed subscription payments and reconciles revenue automatically, end to end, across every customer on the platform.</span>
      </div>
      <div class="journey-step">
        <div class="num">4</div>
        <strong>Enterprise</strong>
        <span>Fleets of treasury and procurement agents call Payment, Payout and Reconciliation APIs directly, with AI Fraud Detection acting as the standing safety layer underneath.</span>
      </div>
    </div>
    <div class="compliance-note reveal">Paynancial's AI &amp; Intelligence products assist with detection, reconciliation and forecasting — they support a human or agent decision, they do not replace approval controls a business chooses to keep in place, and they do not guarantee fraud prevention or a specific financial outcome.</div>
    <p class="reveal" style="margin-top:18px;"><a class="card-link" href="/agentic-ai">Read more about agentic AI in finance →</a></p>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta-band reveal">
      <h2>Ready to start accepting payments?</h2>
      <div class="hero-actions" style="justify-content:center;margin-top:24px;">
        <a href="/contact" class="btn btn-primary">Get Started</a>
        <a href="/developers" class="btn btn-outline">Explore APIs</a>
      </div>
    </div>
  </div>
</section>
