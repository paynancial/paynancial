<?php
/**
 * /about — the Paynancial company story.
 *
 * Every statement here is drawn from content already published on the
 * site (previous About copy, product pages, Developers, Trust Center,
 * Leadership, Contact) or from the published legal entity details. The
 * only dated milestone is incorporation in 2024, which is encoded in the
 * company's published CIN (U66190BR2024PTC067929 — "BR" = Bihar,
 * "2024" = year of incorporation). No funding, customer, volume, headcount,
 * certification or partnership claims are made.
 *
 * Layout: full-bleed sections (styles in assets/css/about.css) with inner
 * grids, rather than one narrow centred column.
 */
$page_meta = [
    'title'       => 'About Paynancial | Payment Infrastructure Built for Modern Business',
    'description' => 'Paynancial Technology Pvt. Ltd. builds secure, intelligent payment infrastructure that helps businesses accept, manage and understand their payments — from gateway to agent-ready APIs.',
    'canonical'   => site_url('/about'),
    'extra_css'   => 'css/about.css',
    'schema'      => [
        [
            '@context' => 'https://schema.org',
            '@type'    => 'AboutPage',
            'name'     => 'About Paynancial',
            'url'      => site_url('/about'),
            'about'    => ['@id' => site_url('/#organization')],
        ],
        [
            '@context'  => 'https://schema.org',
            '@type'     => 'Organization',
            '@id'       => site_url('/#organization'),
            'name'      => 'Paynancial',
            'legalName' => 'Paynancial Technology Private Limited',
            'url'       => APP_URL,
            'logo'      => site_url('/assets/images/paynancial-logo.png'),
            'email'     => 'hello@paynancial.com',
            'telephone' => '+91-612-2999382',
            'foundingDate' => '2024',
            'address'   => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => 'Sharda Mansion, Kailashpuri, Kankarbagh, Hanuman Nagar',
                'addressLocality' => 'Patna',
                'addressRegion'   => 'Bihar',
                'postalCode'      => '800020',
                'addressCountry'  => 'IN',
            ],
            'identifier' => ['@type' => 'PropertyValue', 'propertyID' => 'CIN', 'value' => 'U66190BR2024PTC067929'],
            'taxID'     => '10AAOCP5173C1ZO',
            'sameAs'    => [
                'https://in.linkedin.com/company/paynancialai',
                'https://x.com/paynancial',
                'https://www.facebook.com/paynancial',
                'https://www.instagram.com/paynancial/',
                'https://www.youtube.com/@paynancial',
                'https://in.pinterest.com/paynancial/',
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => site_url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'About', 'item' => site_url('/about')],
            ],
        ],
    ],
];

$milestones = [
    ['2024', 'Foundation', 'Paynancial Technology Pvt. Ltd. is incorporated in Bihar, India, to give businesses a payment gateway that treats security and usability as the same problem.', 'Source: company CIN'],
    ['Stage', 'Payments', 'One integration for cards, UPI, netbanking and wallets, with payment links and pages for teams that need to collect without building a checkout.', 'Payment Gateway · Links · Collection'],
    ['Stage', 'Payouts', 'Moving money out to vendors, employees and partners — to bank accounts or UPI IDs, from the dashboard or the API.', 'Payouts'],
    ['Stage', 'Financial operations', 'Transactions, settlements, refunds and reconciliation in one view, with exportable reports for finance teams.', 'Payment Analytics'],
    ['Stage', 'Agent-ready infrastructure', 'Idempotent requests, structured errors and detailed audit trails — so an AI agent can call the platform as safely as a person can.', 'Developers'],
    ['Now', 'Agentic finance', 'Applying the same discipline to a new kind of caller: agents that initiate payments, payouts and reconciliation within limits a business sets.', 'Agentic AI'],
    ['Next', 'Building further', 'Continuing to build the infrastructure an AI-native economy will depend on — reliable whoever, or whatever, is calling it.', 'Technology'],
];

$differentiators = [
    ['Every major payment method, one integration', 'Cards, UPI, netbanking and wallets through a single integration, with clear settlement reporting behind each transaction.', '/products/payment-gateway', 'Payment Gateway'],
    ['One connected platform', 'Collect, pay out, settle and reconcile in one place — use one service or the whole stack.', '/products', 'Products'],
    ['Developer-first', 'A documented REST API, webhooks and a sandbox, so engineering teams integrate on their own terms.', '/developers', 'Developers'],
    ['Security as the baseline', 'Traffic served over HTTPS/TLS, role-based access with audited dashboards and API keys, and ongoing monitoring for unusual activity.', '/security', 'Security'],
    ['Ready for agentic callers', 'Idempotent by default, structured errors an agent can act on, and audit trails that keep every autonomous action traceable.', '/agentic-ai', 'Agentic AI'],
    ['Support through go-live', 'A team that stays with you from integration through go-live and beyond.', '/support', 'Support'],
];

$stack = [
    ['Agentic AI', 'Agents acting within limits a business sets — with human oversight and auditability.', [['Agentic AI in Finance', '/agentic-ai'], ['Governance', '/agentic-ai#governance']]],
    ['Intelligence', 'Agent-ready APIs: idempotency, structured errors, audit trails.', [['Agent-Ready APIs', '/developers#agentic-ai']]],
    ['Financial operations', 'Settlements, refunds, reconciliation and reporting in one view.', [['Payment Analytics', '/products/payment-analytics']]],
    ['Payouts', 'Money out to vendors, employees and partners.', [['Payouts', '/products/payouts']]],
    ['Payments', 'Money in, through every major method.', [['Payment Gateway', '/products/payment-gateway'], ['Payment Links', '/products/payment-links'], ['Payment Collection', '/products/payment-collection']]],
];

$leaders = [
    ['Renuka Devi', 'Director', 'Directs strategy and governance.', ['Governance & Compliance', 'Strategic Direction'], 'images/leadership/renuka-devi.jpg'],
    ['Anisha Bharti', 'Director', 'Directs product, platform and day-to-day operations.', ['Product & Platform', 'Customer Experience'], 'images/leadership/anisha-bharti.jpg'],
];
?>

<!-- ================================================================ HERO -->
<section class="ab-hero" aria-labelledby="ab-title">
  <div class="container ab-hero-grid">
    <div class="ab-hero-copy">
      <nav class="breadcrumb ab-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span aria-hidden="true">/</span><span class="current" aria-current="page">About</span></nav>
      <span class="eyebrow">About Paynancial</span>
      <h1 id="ab-title">Building the Financial Infrastructure for Modern Business</h1>
      <p class="ab-lead">Paynancial Technology Pvt. Ltd. is a technology-first FinTech company. We build secure, intelligent infrastructure that helps businesses accept, manage and understand their payments — and, increasingly, lets the software acting for them do the same, safely.</p>
      <div class="ab-hero-actions">
        <a class="btn btn-primary" href="#journey">Read our story</a>
        <a class="btn btn-outline" href="/products">Explore the platform</a>
      </div>
    </div>
    <figure class="ab-flow" aria-label="How a payment moves through Paynancial: initiate, verify, settle">
      <svg viewBox="0 0 520 360" role="img" aria-hidden="true" focusable="false">
        <defs>
          <linearGradient id="ab-line" x1="0" x2="1"><stop offset="0" stop-color="#00a69d"/><stop offset="1" stop-color="#ff5500"/></linearGradient>
        </defs>
        <g class="ab-grid-lines">
          <?php for ($i = 0; $i <= 8; $i++): ?><line x1="<?= 20 + $i * 60 ?>" y1="20" x2="<?= 20 + $i * 60 ?>" y2="340"/><?php endfor; ?>
          <?php for ($i = 0; $i <= 5; $i++): ?><line x1="20" y1="<?= 20 + $i * 64 ?>" x2="500" y2="<?= 20 + $i * 64 ?>"/><?php endfor; ?>
        </g>
        <path class="ab-path" d="M40 280 C 140 280, 150 180, 250 180 S 360 90, 480 90" stroke="url(#ab-line)"/>
        <path class="ab-path ab-path-2" d="M40 300 C 150 300, 170 230, 260 230 S 380 170, 480 170"/>
        <g class="ab-node" transform="translate(40 280)"><circle r="7"/><text x="0" y="34">INITIATE</text></g>
        <g class="ab-node" transform="translate(250 180)"><circle r="7"/><text x="0" y="34">VERIFY</text></g>
        <g class="ab-node is-end" transform="translate(480 90)"><circle r="9"/><text x="0" y="-22">SETTLE</text></g>
      </svg>
      <figcaption>Initiate → Verify → Settle. The same guarantees, whoever makes the call.</figcaption>
    </figure>
  </div>
  <div class="container">
    <dl class="ab-entity">
      <div><dt>Legal entity</dt><dd>Paynancial Technology Private Limited</dd></div>
      <div><dt>Incorporated</dt><dd>2024 · Bihar, India</dd></div>
      <div><dt>Based in</dt><dd>Patna, Bihar</dd></div>
      <div><dt>CIN</dt><dd class="mono">U66190BR2024PTC067929</dd></div>
    </dl>
  </div>
</section>

<!-- ======================================================== OUR JOURNEY -->
<section class="ab-story" id="journey" aria-labelledby="ab-journey-title">
  <div class="container ab-story-grid">
    <div class="ab-story-head reveal">
      <span class="eyebrow">Our Journey</span>
      <h2 id="ab-journey-title">From a payment gateway to infrastructure for a new kind of caller</h2>
    </div>
    <div class="ab-story-body reveal">
      <p class="ab-dropcap">Payment processing has historically forced businesses to choose between security, speed and simplicity. Paynancial was founded to remove that trade-off — to give businesses a payment gateway that treats security and usability as the same problem, not competing priorities.</p>
      <p>That meant robust processing, encrypted by default, without the integration friction that slows teams down. From there the platform grew to span the whole journey of a payment: collecting it, moving payouts, tracking settlements and understanding transaction data end to end.</p>
      <p>What the platform has to be reliable <em>for</em> is changing. Increasingly the thing calling our API isn't only a developer's checkout page — it's an AI agent reconciling a ledger or retrying a subscription charge on a business's behalf. We built for that shift early, and we are building further into it now.</p>
    </div>
  </div>
</section>

<!-- =========================================================== TIMELINE -->
<section class="ab-timeline" aria-labelledby="ab-timeline-title">
  <div class="container ab-timeline-head reveal">
    <span class="eyebrow">Milestones</span>
    <h2 id="ab-timeline-title">How the platform has taken shape</h2>
    <p>Stages of the platform, in order. Only the founding year is dated — we don't publish dates we can't point to.</p>
  </div>
  <ol class="ab-track">
    <?php foreach ($milestones as $i => [$when, $title, $text, $tag]): ?>
    <li class="ab-milestone reveal<?= $when === 'Next' ? ' is-next' : '' ?><?= $when === 'Now' ? ' is-now' : '' ?>">
      <span class="ab-when"><?= e($when) ?></span>
      <span class="ab-dot" aria-hidden="true"></span>
      <h3><?= e($title) ?></h3>
      <p><?= e($text) ?></p>
      <span class="ab-tag"><?= e($tag) ?></span>
    </li>
    <?php endforeach; ?>
  </ol>
</section>

<!-- ======================================================== WHY WE EXIST -->
<section class="ab-why" aria-labelledby="ab-why-title">
  <div class="container ab-why-grid">
    <div class="reveal">
      <span class="eyebrow">Why We Exist</span>
      <h2 id="ab-why-title">Because moving money shouldn't mean trading one priority for another.</h2>
    </div>
    <ol class="ab-themes">
      <li class="reveal"><h3>Remove the trade-off</h3><p>Security, speed and simplicity shouldn't compete. We treat them as one design problem.</p></li>
      <li class="reveal"><h3>Make trust the baseline</h3><p>Reliability and data protection are how the platform is built, not a premium feature added later.</p></li>
      <li class="reveal"><h3>Show the whole picture</h3><p>Transaction status, fees and settlement timing belong in the dashboard — not buried in a statement at month's end.</p></li>
      <li class="reveal"><h3>Be ready for what calls next</h3><p>Infrastructure should outlast whoever — or whatever — is calling it.</p></li>
    </ol>
  </div>
</section>

<!-- =================================================== VISION & MISSION -->
<section class="ab-vm" aria-label="Vision and mission">
  <div class="ab-vm-half ab-vision reveal">
    <div class="ab-vm-inner">
      <span class="eyebrow">Our Vision</span>
      <h2>Financial infrastructure every business can rely on — and every agent acting for it, too.</h2>
    </div>
  </div>
  <div class="ab-vm-half ab-mission reveal">
    <div class="ab-vm-inner">
      <span class="eyebrow">Our Mission</span>
      <h2>Help businesses accept, move and understand their money — securely, reliably and without integration friction.</h2>
      <p>Every day that means clear status and settlement reporting, APIs that behave predictably under retries, and a team that stays with customers from integration to go-live.</p>
    </div>
  </div>
</section>

<!-- ==================================================== WHY PAYNANCIAL -->
<section class="ab-diff" aria-labelledby="ab-diff-title">
  <div class="container">
    <div class="ab-section-head reveal">
      <span class="eyebrow">Why Choose Paynancial</span>
      <h2 id="ab-diff-title">What we can show you, not just tell you</h2>
      <p>Each point links to where it is documented on this site.</p>
    </div>
    <ol class="ab-diff-list">
      <?php foreach ($differentiators as $i => [$title, $text, $href, $label]): ?>
      <li class="reveal">
        <span class="ab-num"><?= sprintf('%02d', $i + 1) ?></span>
        <h3><?= e($title) ?></h3>
        <p><?= e($text) ?></p>
        <a href="<?= e($href) ?>">See <?= e($label) ?> <span aria-hidden="true">→</span></a>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- ================================================== PRODUCT EVOLUTION -->
<section class="ab-stack-section" aria-labelledby="ab-stack-title">
  <div class="container ab-stack-grid">
    <div class="reveal">
      <span class="eyebrow">The Platform</span>
      <h2 id="ab-stack-title">Each layer builds on the one beneath it</h2>
      <p class="ab-muted">Payments bring money in. Payouts move it out. Financial operations make sense of both. Intelligence and agentic capabilities let software act on that picture — within limits a business sets.</p>
      <a class="ab-link" href="/products">Explore all products <span aria-hidden="true">→</span></a>
    </div>
    <ol class="ab-stack" reversed>
      <?php foreach ($stack as $i => [$title, $text, $links]): ?>
      <li class="ab-layer reveal" style="--i:<?= $i ?>">
        <div class="ab-layer-copy"><h3><?= e($title) ?></h3><p><?= e($text) ?></p></div>
        <div class="ab-layer-links"><?php foreach ($links as [$l, $h]): ?><a href="<?= e($h) ?>"><?= e($l) ?></a><?php endforeach; ?></div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- ============================================================ PEOPLE -->
<section class="ab-people" aria-labelledby="ab-people-title">
  <div class="container ab-people-grid">
    <div class="reveal">
      <span class="eyebrow">Leadership</span>
      <h2 id="ab-people-title">People Behind Paynancial</h2>
      <p class="ab-muted">The directors accountable for how Paynancial is run.</p>
      <a class="ab-link" href="/leadership">Meet Our Leadership <span aria-hidden="true">→</span></a>
    </div>
    <ul class="ab-leaders">
      <?php foreach ($leaders as [$name, $role, $line, $focus, $img]): ?>
      <li class="ab-leader reveal">
        <img src="<?= asset($img) ?>" alt="<?= e($name) ?>, <?= e($role) ?>, Paynancial" width="500" height="500" loading="lazy" decoding="async">
        <div>
          <h3><?= e($name) ?></h3>
          <p class="ab-role"><?= e($role) ?></p>
          <p><?= e($line) ?></p>
          <ul class="ab-focus"><?php foreach ($focus as $f) echo '<li>' . e($f) . '</li>'; ?></ul>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- ============================================================= TRUST -->
<section class="ab-trust" aria-labelledby="ab-trust-title">
  <div class="container ab-trust-grid">
    <div class="reveal">
      <span class="eyebrow">Trust</span>
      <h2 id="ab-trust-title">Built with Trust at the Core</h2>
      <p>Our Trust Center labels where security, privacy and AI governance stand today — verified, in progress, or awaiting confirmation. Nothing there is claimed before it is true.</p>
      <a class="btn btn-primary" href="/trust">Explore Trust Center</a>
    </div>
    <ul class="ab-trust-links reveal">
      <li><a href="/security"><strong>Security &amp; Compliance</strong><span>How the platform is protected</span></a></li>
      <li><a href="/trust#ai-governance"><strong>AI Governance</strong><span>Human oversight and auditability</span></a></li>
      <li><a href="/legal/privacy-policy"><strong>Privacy Policy</strong><span>How we handle personal data</span></a></li>
      <li><a href="/legal/terms-conditions"><strong>Terms &amp; Conditions</strong><span>The terms of using Paynancial</span></a></li>
    </ul>
  </div>
</section>

<!-- ======================================================== WHAT'S NEXT -->
<section class="ab-next" aria-labelledby="ab-next-title">
  <div class="container ab-next-grid">
    <div class="reveal">
      <span class="eyebrow">Looking Ahead</span>
      <h2 id="ab-next-title">Building What's Next</h2>
    </div>
    <ul class="ab-next-list">
      <li class="reveal"><h3>Intelligent payments</h3><p>Payments that carry the context software needs to act on them correctly.</p></li>
      <li class="reveal"><h3>Agentic finance</h3><p>Agents that initiate and reconcile within limits a business sets — with a human in the loop where it matters.</p></li>
      <li class="reveal"><h3>Connected financial operations</h3><p>Collections, payouts, settlements and reporting that read as one system.</p></li>
      <li class="reveal"><h3>Developer infrastructure</h3><p>APIs that stay predictable under retries, errors and scale.</p></li>
    </ul>
    <a class="ab-link reveal" href="/technology">Read: Financial Technology in the Era of Agentic AI <span aria-hidden="true">→</span></a>
  </div>
</section>

<!-- ========================================================= FINAL CTA -->
<section class="ab-final" aria-labelledby="ab-final-title">
  <div class="container ab-final-inner reveal">
    <h2 id="ab-final-title">Let's Build the Future of Business Finance.</h2>
    <p>Whether you are taking your first online payment, running payouts at scale or wiring an agent into your finance stack — start with a conversation.</p>
    <div class="ab-hero-actions">
      <a class="btn btn-primary" href="/products">Explore Paynancial →</a>
      <a class="btn btn-outline" href="/contact?intent=sales">Talk to Sales →</a>
    </div>
    <p class="ab-final-links"><a href="/careers">Careers</a><span aria-hidden="true">·</span><a href="/contact">Contact</a><span aria-hidden="true">·</span><a href="/solutions">Solutions</a></p>
  </div>
</section>
