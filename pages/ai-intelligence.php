<?php
/**
 * AI & Intelligence — /ai-intelligence. The canonical hub for Paynancial's
 * AI capabilities (replaces /products/ai-and-intelligence, which 301s here).
 * Content rule: see includes/ai-intelligence.php.
 */
require_once __DIR__ . '/../includes/standalone-ui.php';
require_once __DIR__ . '/../includes/ai-intelligence.php';
require_once __DIR__ . '/../includes/solutions-data.php';

$children = ai_children();
$meta = ai_hub_meta();
$faqs = faq_set('ai:hub');
$trail = [['Home', '/'], ['AI & Intelligence', ai_url()]];
$page_meta = sp_meta([
    'title'       => 'AI & Financial Intelligence Platform | Paynancial',
    'description' => 'Explore Paynancial\'s AI and financial intelligence capabilities for fraud detection, reconciliation, financial assistance, cash-flow and revenue insights — governed by limits your business sets.',
    'path'        => ai_url(),
    'h1'          => 'Intelligent infrastructure for the financial operations of tomorrow.',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
// ItemList of the capabilities, for the entity relationships.
$page_meta['schema'][] = [
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'name'     => 'Paynancial AI & Intelligence capabilities',
    'itemListElement' => array_values(array_map(
        fn ($slug, $c, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'], 'url' => site_url(ltrim($c['path'], '/'))],
        array_keys($children), $children, array_keys(array_keys($children))
    )),
];

ob_start(); ?>
<figure class="ai-visual" aria-label="Diagram: payments data flowing from Paynancial's payment infrastructure into AI capabilities, then to people who decide.">
  <svg viewBox="0 0 520 420" role="img" aria-hidden="true" focusable="false">
    <defs>
      <linearGradient id="aiLine" x1="0" x2="1"><stop offset="0" stop-color="#00a69d" stop-opacity="0.15"/><stop offset="0.5" stop-color="#6dd5cd" stop-opacity="0.9"/><stop offset="1" stop-color="#ff5500" stop-opacity="0.5"/></linearGradient>
      <radialGradient id="aiNode"><stop offset="0" stop-color="#6dd5cd"/><stop offset="1" stop-color="#00a69d" stop-opacity="0.2"/></radialGradient>
    </defs>
    <g class="ai-grid" stroke="rgba(250,249,245,0.06)">
      <?php for ($x = 20; $x <= 500; $x += 40): ?><line x1="<?= $x ?>" y1="10" x2="<?= $x ?>" y2="410"/><?php endfor; ?>
      <?php for ($y = 10; $y <= 410; $y += 40): ?><line x1="20" y1="<?= $y ?>" x2="500" y2="<?= $y ?>"/><?php endfor; ?>
    </g>
    <?php $rows = [70, 130, 190, 250, 310, 370]; ?>
    <g class="ai-streams" fill="none" stroke="url(#aiLine)" stroke-width="1.4">
      <?php foreach ($rows as $i => $y): ?>
      <path class="ai-stream" style="animation-delay: <?= $i * 0.6 ?>s" d="M40 <?= $y ?> C 150 <?= $y ?>, 180 210, 260 210 S 380 <?= 420 - $y ?>, 480 <?= 420 - $y ?>"/>
      <?php endforeach; ?>
    </g>
    <g class="ai-in" font-family="JetBrains Mono, monospace" font-size="10" fill="rgba(250,249,245,0.62)">
      <?php foreach (['PAYMENTS', 'COLLECTIONS', 'PAYOUTS', 'REFUNDS', 'SETTLEMENTS', 'REPORTS'] as $i => $label): ?>
      <circle cx="40" cy="<?= $rows[$i] ?>" r="3.5" fill="#6dd5cd"/><text x="50" y="<?= $rows[$i] - 8 ?>"><?= $label ?></text>
      <?php endforeach; ?>
    </g>
    <circle class="ai-core-ring" cx="260" cy="210" r="54" fill="none" stroke="rgba(109,213,205,0.35)" stroke-dasharray="3 6"/>
    <circle cx="260" cy="210" r="34" fill="url(#aiNode)"/>
    <text x="260" y="206" text-anchor="middle" font-family="Fraunces, serif" font-size="13" fill="#faf9f5">Paynancial</text>
    <text x="260" y="222" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="9" letter-spacing="1.5" fill="#6dd5cd">AI</text>
    <g font-family="JetBrains Mono, monospace" font-size="10" fill="rgba(250,249,245,0.62)" text-anchor="end">
      <?php foreach (['RISK SIGNALS', 'EXCEPTIONS', 'ANSWERS', 'LIQUIDITY', 'REVENUE', 'PEOPLE DECIDE'] as $i => $label): ?>
      <circle cx="480" cy="<?= 420 - $rows[$i] ?>" r="3.5" fill="<?= $i === 5 ? '#ff5500' : '#6dd5cd' ?>"/><text x="470" y="<?= 420 - $rows[$i] - 8 ?>"><?= $label ?></text>
      <?php endforeach; ?>
    </g>
  </svg>
</figure>
<?php $visual = ob_get_clean();

sp_track_view('ai_intelligence_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'AI & Intelligence',
    'h1'        => 'Intelligent infrastructure for the financial operations of tomorrow.',
    'lead'      => 'Paynancial combines payment infrastructure with intelligence designed to help businesses understand, protect and operate their financial workflows — surfacing signals and narrowly scoped actions inside limits your business sets.',
    'primary'   => ['Explore Paynancial AI', '#paynancial-ai', 'cta_click'],
    'secondary' => [cta_label(), '/contact?intent=sales&product=ai-intelligence', 'cta_click'],
    'values'    => ['Built on your payments data', 'Business-set limits', 'Human oversight', 'Available on request'],
    'aside'     => $visual,
]);
?>
<nav class="sp-index" aria-label="On this page">
  <div class="sp-wrap"><ul>
    <li><a href="#platform">Platform</a></li>
    <li><a href="#capabilities">Capabilities</a></li>
    <li><a href="#architecture">Architecture</a></li>
    <li><a href="#use-cases">Use cases</a></li>
    <li><a href="#industries">Industries</a></li>
    <li><a href="#control">Control</a></li>
    <li><a href="#trust">Trust</a></li>
    <li><a href="#developers">Developers</a></li>
    <li><a href="#questions">Questions</a></li>
  </ul></div>
</nav>

<?php sp_band_open('platform'); ?>
  <?php sp_head('platform', 'Paynancial AI platform', 'From financial data to intelligent action.', 'Every Paynancial payment, payout, refund and settlement is recorded as it happens. That record is what the AI capabilities work on — and what they produce goes to people, or acts only inside the limits people set.'); ?>
  <ol class="ai-flow" aria-label="From financial data to intelligent action">
    <?php foreach ([
        ['Payments', 'Checkout, links, collections and payouts'],
        ['Financial data', 'Transactions, refunds and settlements, recorded as they happen'],
        ['AI intelligence', 'Fraud risk, matching, forecasting, answers'],
        ['Insights', 'Signals and recommendations for your team'],
        ['Action', 'By people — or narrowly scoped, within your limits'],
    ] as $i => [$t, $d]): ?>
    <li class="ai-flow-step reveal"><span class="ai-flow-num"><?= sprintf('%02d', $i + 1) ?></span><strong><?= e($t) ?></strong><span><?= e($d) ?></span></li>
    <?php endforeach; ?>
  </ol>
<?php sp_band_close(); ?>

<?php sp_band_open('capabilities', 'dim'); ?>
  <?php sp_head('capabilities', 'AI & Intelligence capabilities', 'One platform, five intelligence capabilities.', 'Each capability is described by what it does and what stays with people. All are available on request.'); ?>
  <div class="ai-caps">
    <?php foreach ($children as $slug => $c): [$ctaLabel, $people] = $meta[$slug]; ?>
    <article class="ai-cap reveal<?= $slug === 'paynancial-ai' ? ' ai-cap--platform' : '' ?>" id="<?= e($slug) ?>">
      <span class="ai-cap-kicker"><?= $slug === 'paynancial-ai' ? 'Primary AI platform' : 'Capability' ?> · On request</span>
      <h3><?= e($c['name']) ?></h3>
      <p><?= e($c['answer']) ?></p>
      <dl class="ai-cap-people"><dt>Stays with people</dt><dd><?= e($people) ?></dd></dl>
      <a class="sp-item-link" href="<?= e($c['path']) ?>" data-track="cta_click"><?= e($ctaLabel) ?> <span aria-hidden="true">→</span></a>
    </article>
    <?php endforeach; ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('architecture', 'ink'); ?>
  <?php sp_head('architecture', 'Architecture', 'Every capability sits on the same payment infrastructure.', 'The AI capabilities do not run on separate data. They read the records Paynancial\'s products already create — which is why they can be governed, logged and audited the same way.'); ?>
  <div class="ai-arch reveal" role="img" aria-label="Paynancial AI at the top, five capabilities in the middle, and the Paynancial payment infrastructure underneath them all.">
    <div class="ai-arch-top"><span>Paynancial AI</span><small>Governed by permissions, limits, oversight, authentication and audit</small></div>
    <div class="ai-arch-caps">
      <?php foreach ($children as $slug => $c): if ($slug === 'paynancial-ai') continue; ?>
      <a href="<?= e($c['path']) ?>"><?= e(str_replace('AI ', '', $c['name'])) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="ai-arch-base">
      <span class="ai-arch-label">Paynancial financial infrastructure</span>
      <div>
        <a href="/products/payment-gateway">Payment Gateway</a><a href="/products/payment-collection">Collections</a><a href="/products/payouts">Payouts</a><a href="/products/refunds">Refunds</a><a href="/products/settlements">Settlements</a><a href="/products/payment-analytics">Analytics</a>
      </div>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('layers'); ?>
  <div class="sp-split">
    <?php sp_head('layers', 'Across every financial layer', 'Intelligence across every financial layer.', 'Each layer is an existing Paynancial product. The AI capabilities add signals on top — they do not replace the product or the people using it.'); ?>
    <?php sp_table(['Layer', 'Paynancial product', 'Intelligence on top'], [
        ['Transactions', '<a class="inline-link" href="/products/payment-gateway">Payment Gateway</a>, <a class="inline-link" href="/products/payment-links">Payment Links</a>', '<a class="inline-link" href="' . e(ai_url('fraud-detection')) . '">AI Fraud Detection</a>'],
        ['Collections', '<a class="inline-link" href="/products/payment-collection">Smart Collections</a>', '<a class="inline-link" href="' . e(ai_url('financial-assistant')) . '">AI Financial Assistant</a> for failed-payment questions'],
        ['Payouts', '<a class="inline-link" href="/products/payouts">Payouts</a>', '<a class="inline-link" href="' . e(ai_url('cash-flow-intelligence')) . '">AI Cash-Flow Intelligence</a>'],
        ['Reconciliation', '<a class="inline-link" href="/products/reconciliation">Reconciliation</a>, <a class="inline-link" href="/products/settlements">Settlements</a>', '<a class="inline-link" href="' . e(ai_url('reconciliation')) . '">AI Reconciliation</a>'],
        ['Financial operations', '<a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>', '<a class="inline-link" href="' . e(ai_url('revenue-forecasting')) . '">AI Revenue Forecasting</a>'],
        ['Business action', 'Your team', 'Decides on everything above the limits you set'],
    ], 'Intelligence across Paynancial\'s financial layers'); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('use-cases', 'dim'); ?>
  <?php sp_head('use-cases', 'Use cases', 'Where intelligence helps.', 'Each use case in four parts: the problem, the intelligence, the result for your team, and where to go next.'); ?>
  <div class="ai-uses">
    <?php foreach ([
        ['Fraud monitoring', 'Fraudulent payments found after settlement have already moved money.', 'Transaction patterns are evaluated for fraud risk as payments happen.', 'Risky payments reach a person before they complete, not after.', ['AI Fraud Detection', ai_url('fraud-detection')]],
        ['Financial reconciliation', 'Most reconciliation time is spent confirming things that already match.', 'Settlements are matched against transactions automatically.', 'Your team works only on the genuine exceptions.', ['AI Reconciliation', ai_url('reconciliation')]],
        ['Cash-flow visibility', 'A monthly spreadsheet forecast is out of date before it is finished.', 'Near-term liquidity is forecast from live transaction data.', 'Decisions about cash are made on today\'s numbers.', ['AI Cash-Flow Intelligence', ai_url('cash-flow-intelligence')]],
        ['Payment intelligence', 'Transaction volume is detailed but noisy.', 'Raw volume is turned into forward-looking revenue numbers.', 'Finance leads plan with the numbers that matter.', ['AI Revenue Forecasting', ai_url('revenue-forecasting')]],
        ['Financial assistance', 'Routine payment questions become support tickets.', 'Questions like "why was this declined?" are answered from the payment records.', 'Answers in the moment; people handle the rest.', ['AI Financial Assistant', ai_url('financial-assistant')]],
    ] as [$title, $problem, $intel, $result, [$nextLabel, $nextHref]]): ?>
    <article class="ai-use reveal">
      <h3><?= e($title) ?></h3>
      <dl>
        <div><dt>Problem</dt><dd><?= e($problem) ?></dd></div>
        <div><dt>Intelligence</dt><dd><?= e($intel) ?></dd></div>
        <div><dt>Result</dt><dd><?= e($result) ?></dd></div>
      </dl>
      <a class="sp-item-link" href="<?= e($nextHref) ?>">Next: <?= e($nextLabel) ?> <span aria-hidden="true">→</span></a>
    </article>
    <?php endforeach; ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('industries'); ?>
  <?php sp_head('industries', 'Industry applications', 'Industry, challenge, capability.', 'Where each capability fits, based on the payment challenges described on Paynancial\'s industry pages.'); ?>
  <?php
  $ind = sol_industries();
  $map = [
      ['e-commerce', 'Returns and partial refunds; matching orders to settlements', ['fraud-detection', 'reconciliation']],
      ['travel', 'High-value bookings paid in stages; many partners to pay', ['fraud-detection', 'cash-flow-intelligence']],
      ['healthcare', 'Payments at different stages of care; reconciliation across departments', ['reconciliation', 'financial-assistant']],
      ['education', 'Recurring fees for many students; knowing who has paid', ['financial-assistant', 'revenue-forecasting']],
      ['retail', 'Many channels, many reports', ['reconciliation', 'revenue-forecasting']],
      ['hospitality', 'Deposits and final bills; paying vendors', ['cash-flow-intelligence', 'reconciliation']],
      ['enterprise', 'Many systems, one ledger; automation without losing control', ['reconciliation', 'cash-flow-intelligence']],
  ];
  sp_table(['Industry', 'Financial challenge', 'Relevant capability'], array_map(function ($row) use ($ind, $children) {
      [$slug, $challenge, $caps] = $row;
      return [
          '<a class="inline-link" href="' . e(sol_url($slug)) . '">' . e($ind[$slug]['name']) . '</a>',
          e($challenge),
          implode(' + ', array_map(fn ($c) => '<a class="inline-link" href="' . e($children[$c]['path']) . '">' . e($children[$c]['name']) . '</a>', $caps)),
      ];
  }, $map), 'AI capabilities by industry');
  ?>
<?php sp_band_close(); ?>

<?php sp_band_open('control', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('control', 'Human-in-the-loop', 'Intelligence with control.', 'For a financial business, how AI is governed matters as much as what it does. Every Paynancial AI capability follows the same five controls.'); ?>
    <div>
      <?php sp_steps([
          ['Permissions', 'A capability can only take actions explicitly granted to its role or API key. Nothing is enabled by default.'],
          ['Policy limits', 'Spending caps, beneficiary allow-lists and approval thresholds are set by your business.'],
          ['Human oversight', 'Actions above a threshold, or matching a risk pattern, go to a person before they complete.'],
          ['Authentication', 'Every request is tied to a specific API key or user session.'],
          ['Auditability', 'Every action is logged against the request, key and rule that authorised it.'],
      ]); ?>
      <div class="sp-note reveal">On explainability: Paynancial logs every action against the rule that authorised it, so you can always trace why something happened. It does not claim full model explainability, and a formally reviewed AI governance policy document has not yet been published.</div>
      <p class="reveal" style="margin-top:18px;"><a class="card-link" href="/ai-governance">Read AI Governance →</a></p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('trust', 'ink'); ?>
  <?php sp_head('trust', 'Security & trust', 'AI built on trusted financial infrastructure.', 'The AI capabilities work on data that is already protected by Paynancial\'s security foundations — listed as verified in the Trust Center.'); ?>
  <?php sp_answers([
      ['Encryption', 'Data in transit is encrypted with TLS 1.2 or higher; sensitive data at rest uses AES-256.'],
      ['Card data', 'Card data is tokenized on receipt and never stored raw on application servers, and cardholder systems run in a separate, access-restricted environment.'],
      ['Access', 'Internal access follows least privilege, is logged, and requires multi-factor authentication for privileged actions.'],
      ['Fraud monitoring', 'Transactions pass through real-time risk scoring before funds move.'],
  ]); ?>
  <p class="reveal" style="margin-top:28px;color:rgba(250,249,245,0.74);">Certifications and regulatory authorisations are not claimed here; the Trust Center lists what is verified and what is still to be confirmed. <a class="inline-link" href="/trust" style="color:var(--teal-300);">Trust Center</a> · <a class="inline-link" href="/security" style="color:var(--teal-300);">Security &amp; Compliance</a> · <a class="inline-link" href="/ai-governance" style="color:var(--teal-300);">AI Governance</a></p>
<?php sp_band_close(); ?>

<?php sp_band_open('agentic'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('agentic', 'Agentic AI', 'From intelligence to agent-ready infrastructure.', 'AI & Intelligence and Agentic AI are related, but different. AI & Intelligence is the set of capabilities that analyse and assist. Agentic AI is how agents act on a business\'s behalf — and the agent-ready infrastructure that makes that safe.'); ?>
      <p class="reveal"><a class="btn sp-btn-accent" href="/agentic-ai" data-track="cta_click">Explore Agentic AI <span aria-hidden="true">→</span></a></p>
    </div>
    <ol class="ai-flow ai-flow--vertical" aria-label="From AI & Intelligence to agent-ready infrastructure">
      <li class="ai-flow-step reveal"><span class="ai-flow-num">01</span><strong>AI & Intelligence</strong><span>Capabilities that analyse payments data and assist people.</span></li>
      <li class="ai-flow-step reveal"><span class="ai-flow-num">02</span><strong><a href="/agentic-ai/financial-agents">AI financial agents</a></strong><span>Agents that take narrowly scoped actions within limits.</span></li>
      <li class="ai-flow-step reveal"><span class="ai-flow-num">03</span><strong><a href="/agentic-ai/payment-orchestration">Agent-ready infrastructure</a></strong><span>Idempotency, structured errors, webhooks and business-set limits.</span></li>
    </ol>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('india', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('india', 'In India', 'AI for how businesses in India pay and get paid.', 'Built around the realities of Indian digital payments — with people, not models, accountable for decisions.'); ?>
    <?php sp_answers([
        ['UPI-scale volumes', 'UPI has made high-volume, low-value digital payments normal. AI helps surface the few transactions and mismatches that need a person, instead of reviewing every one.'],
        ['Fraud awareness', 'Customers can report cyber fraud on the national helpline 1930 or at cybercrime.gov.in, and a UPI PIN is never needed to receive money — two messages worth repeating to your customers.'],
        ['Personal data', 'Payment records carry personal data governed by the Digital Personal Data Protection Act, 2023, so AI that works on them has to respect consent and purpose limits.'],
        ['Planning to the Indian calendar', 'The financial year runs April to March, and festive seasons drive peaks — forecasts are most useful when they line up with both.'],
    ]); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_regulatory('ai:hub', 'AI in payments in India'); ?>

<?php sp_band_open('developers', 'dim'); ?>
  <?php sp_head('developers', 'Developer platform', 'Build with financial intelligence.', 'The AI capabilities work on the data your integration creates. Today, developers build on the published Paynancial API; APIs for the AI capabilities themselves are not published — ask our team about availability.'); ?>
  <?php sp_related([
      ['API Reference', 'Payments, refunds, payouts, links, collections and reports.', '/developers/api-reference'],
      ['Webhooks', 'Real-time events for payments, payouts, refunds and settlements.', '/developers/webhooks'],
      ['SDKs', 'PHP, JavaScript and Python.', '/developers/sdks'],
      ['Sandbox', 'Test with no real funds involved.', '/sandbox'],
  ]); ?>
  <p class="reveal" style="margin-top:24px;"><a class="card-link" href="/developers">Explore the Developer Platform →</a></p>
<?php sp_band_close(); ?>

<?php sp_band_open('questions'); ?>
  <?php sp_head('questions', 'Questions', 'AI & Intelligence, answered.'); ?>
  <?php sp_answers($faqs); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('related', 'dim'); ?>
  <?php sp_head('related', 'Related', 'Keep exploring.'); ?>
  <?php sp_related([
      ['Financial Operations', 'Reconciliation, settlements, refunds and reports.', '/financial-operations'],
      ['AI Financial Agents', 'Where agents work in financial operations.', '/agentic-ai/financial-agents'],
      ['Developer Hub', 'API, SDKs, webhooks and sandbox.', '/developers'],
      ['Trust Center', 'What is verified, and what is still to be confirmed.', '/trust'],
      ['E-Commerce solutions', 'Checkout, refunds and reconciliation for online stores.', '/solutions/e-commerce'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Turn financial data into intelligent action.', 'Talk to our team about Paynancial AI — what is available for your business, and the limits it should work within.', [
    ['Explore Paynancial AI', ai_url('paynancial-ai'), 'cta_click'],
    [cta_label(), '/contact?intent=sales&product=ai-intelligence', 'cta_click'],
]); ?>
