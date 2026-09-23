<?php
$page_meta = [
    'title'       => 'Solutions | AI-Powered Financial Infrastructure | Paynancial',
    'description' => 'Payment infrastructure for startups, SaaS, SMEs, marketplaces and enterprises — across e-commerce, travel, healthcare, FinTech and more.',
];
$businessTypes = [
    'startups'           => ['Startups & New Businesses', 'Launch fast with payment infrastructure that grows with you.', '/solutions/startups'],
    'saas'               => ['SaaS & Subscription Businesses', 'Recurring billing, automated collections and reconciliation, built in.', '/solutions/saas'],
    'sme'                => ['SMEs & Growing Businesses', 'Room to scale into new volume without switching providers.', '/contact?intent=sales&solution=sme'],
    'marketplaces'       => ['Marketplaces & Platforms', 'Collect from customers and split funds to sellers and partners automatically.', '/contact?intent=sales&solution=marketplaces'],
    'digital-businesses' => ['Digital Businesses', 'Payment infrastructure for products that live entirely online.', '/contact?intent=sales&solution=digital-businesses'],
    'agencies'           => ['Agencies & Professional Services', 'Simple invoicing and collection for client-based businesses.', '/contact?intent=sales&solution=agencies'],
];
$industries = [
    'ecommerce'             => ['E-Commerce', 'Fast, reliable checkout with support for the payment methods your customers already trust.', 'An online store uses the Payment Gateway for checkout and Payment Analytics to track conversion by payment method.'],
    'travel'                => ['Travel', 'Handle high-value, multi-currency bookings with confidence.', 'A travel agency uses Payment Links to collect booking deposits and Payouts to settle with hotel and airline partners.'],
    'healthcare'            => ['Healthcare', 'Secure, compliant billing flows for clinics and hospitals.', 'A clinic uses Payment Collection to bill patients for recurring treatment plans and reconcile insurance co-payments.'],
    'education'             => ['Education', 'Simplify fee collection for schools, colleges and training institutes.', 'A training institute uses Payment Collection to automate monthly fee billing across hundreds of students.'],
    'retail'                => ['Retail', 'Unified payment flows across online and in-store channels.', 'A retail chain uses the Payment Gateway online and Payment Analytics to reconcile sales across every store.'],
    'hospitality'           => ['Hospitality', 'Flexible collection for bookings, deposits and on-site payments.', 'A hotel uses Payment Links for advance booking deposits and the Payment Gateway for on-site guest payments.'],
    'professional-services' => ['Professional Services', 'Simple invoicing and collection for professional service businesses.', 'A consulting firm uses Payment Links to invoice clients and Payouts to pay freelance associates.'],
    'fintech'               => ['FinTech', 'Infrastructure for FinTech products that need payments without becoming a payments company.', 'A lending platform uses Payouts to disburse loans and Reconciliation to match repayments automatically.'],
    'logistics'             => ['Logistics & Transportation', 'Collect on delivery and pay fleet partners without manual reconciliation.', 'A logistics operator uses Payment Collection for cash-on-delivery orders and Payouts to settle with drivers weekly.'],
    'real-estate'           => ['Real Estate & PropTech', 'Handle large-ticket collections and vendor payouts for property businesses.', 'A property manager uses Payment Links for rent collection and Payouts to settle with maintenance vendors.'],
    'insurance'             => ['Insurance & InsurTech', 'Premium collection and claims payouts on one reconciled platform.', 'An insurer uses Recurring Payments for premium collection and Payouts to settle approved claims.'],
    'gaming'                => ['Gaming & Digital Services', 'Fast, high-volume payments for digital and in-app purchases.', 'A gaming platform uses the Payment Gateway for in-app purchases and Payment Analytics to track spend by title.'],
    'nonprofits'            => ['NGOs & Non-Profits', 'Transparent donation collection with clear reconciliation for donors and auditors.', 'An NGO uses Payment Pages for donation drives and Payment Analytics to report fund utilization.'],
    'enterprise'            => ['Enterprise', 'Custom infrastructure, dedicated support and volume-ready architecture.', 'A large enterprise uses the full platform — Gateway, Collection, Payouts and Analytics — behind its own finance systems via the API.'],
];
?>
<section style="padding-top:56px;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Solutions</span>
      <h1>AI-powered financial infrastructure, shaped by your business.</h1>
      <p class="lead">Payments, payouts, billing, reconciliation and financial intelligence — connected through one platform, built for a world where agentic AI is starting to run a growing share of the work in between.</p>
    </div>
  </div>
</section>

<section class="section-subtle" id="business">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">By Business Type</span>
      <h2>Built for how your business actually operates.</h2>
    </div>
    <div class="grid grid-3">
      <?php foreach ($businessTypes as $slug => [$title, $desc, $href]): ?>
        <a class="card reveal" id="<?= e($slug) ?>" href="<?= e($href) ?>">
          <span class="card-icon">◆</span>
          <h3><?= e($title) ?></h3>
          <p><?= e($desc) ?></p>
          <span class="card-link"><?= str_starts_with($href, '/solutions/') ? 'Explore this solution' : 'Talk to sales' ?> →</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="industries">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">By Industry</span>
      <h2>Solutions shaped by how your industry collects and manages payments.</h2>
    </div>
    <div class="grid grid-3">
      <?php foreach ($industries as $slug => [$title, $desc, $useCase]): ?>
        <div class="card reveal" id="<?= e($slug) ?>">
          <span class="card-icon">◆</span>
          <h3><?= e($title) ?></h3>
          <p><?= e($desc) ?></p>
          <p class="text-muted" style="margin-top:10px;font-size:0.85rem;"><?= e($useCase) ?></p>
          <a class="card-link" href="/contact?intent=sales&solution=<?= e($slug) ?>">Talk to sales →</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">The Agentic AI Era</span>
      <h2>What changes when AI agents start acting on a business's behalf.</h2>
      <p>Agentic AI — software that doesn't just answer a question but takes an action, like approving an invoice or retrying a failed charge — is moving from experiment to default across every business model on this page. What it disrupts, and how much a business lets it do unsupervised, scales with the size and shape of the business.</p>
    </div>
    <div class="ledger reveal">
      <div class="ledger-row">
        <span class="ledger-tag">Startups</span>
        <h3>Speed becomes the default, not the risk</h3>
        <p>A founder without a finance team can run an AI assistant that reconciles Stripe-style transaction noise and flags what needs a human — turning a chore that used to eat a weekend into a standing background process.</p>
      </div>
      <div class="ledger-row">
        <span class="ledger-tag">SaaS &amp; Subscription</span>
        <h3>Billing stops being a support ticket</h3>
        <p>Failed-payment retries, dunning emails and revenue reconciliation move from a support queue to an agent that acts within rules the business sets — freeing the team to build product instead of chasing renewals.</p>
      </div>
      <div class="ledger-row">
        <span class="ledger-tag">SMEs &amp; Growing Businesses</span>
        <h3>The finance team's job shifts from doing to approving</h3>
        <p>An AI ops assistant routes vendor payouts and flags anomalies; the owner or finance lead spends their time on the exceptions an agent surfaces, not the routine transactions an agent already handled.</p>
      </div>
      <div class="ledger-row">
        <span class="ledger-tag">Marketplaces &amp; Platforms</span>
        <h3>Every seller gets a finance department they never hired</h3>
        <p>Automated split payments, payouts and reconciliation, running per transaction across thousands of sellers, are the kind of scale that was previously reserved for platforms big enough to build it themselves.</p>
      </div>
      <div class="ledger-row">
        <span class="ledger-tag">Enterprise</span>
        <h3>Treasury and procurement start running on standing agents</h3>
        <p>Fleets of AI agents initiate payouts, run reconciliation and monitor cash flow continuously against policy limits a business defines — with fraud detection and audit trails as the non-negotiable layer underneath.</p>
      </div>
    </div>
    <div class="compliance-note reveal">Paynancial's AI-assisted capabilities support the decisions a business or its agents make — they do not replace a business's own approval controls, and they do not guarantee a specific fraud, revenue or approval outcome.</div>
    <p class="reveal" style="margin-top:18px;"><a class="card-link" href="/agentic-ai">Read more about agentic AI in finance →</a></p>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta-band reveal">
      <h2>Not sure which solution fits your business?</h2>
      <div class="hero-actions" style="justify-content:center;margin-top:24px;">
        <a href="/contact?intent=sales" class="btn btn-primary">Talk to Sales</a>
      </div>
    </div>
  </div>
</section>
