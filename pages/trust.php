<?php
$page_meta = [
    'title'       => 'Trust Center | Paynancial',
    'description' => 'Where Paynancial\'s security, privacy and AI governance stand today — clearly labeled as verified, in progress, or awaiting confirmation. Nothing here is claimed before it\'s true.',
];
$sections = [
    ['id' => 'at-a-glance', 'title' => 'Trust, At a Glance'],
    ['id' => 'security-foundations', 'title' => 'Security Foundations'],
    ['id' => 'privacy', 'title' => 'Privacy'],
    ['id' => 'continuity', 'title' => 'Business Continuity &amp; Disaster Recovery'],
    ['id' => 'ai-governance', 'title' => 'AI Governance'],
    ['id' => 'human-oversight', 'title' => 'Human Oversight'],
    ['id' => 'auditability', 'title' => 'Auditability'],
    ['id' => 'how-we-label', 'title' => 'How We Label Claims on This Page'],
];
$sectionCount = count($sections);
?>
<section style="padding-top:56px;">
  <div class="container">
    <nav class="breadcrumb reveal" aria-label="Breadcrumb">
      <a href="/">Home</a><span aria-hidden="true">/</span>
      <span class="current">Trust Center</span>
    </nav>
    <div class="section-head reveal">
      <span class="eyebrow">Trust Center</span>
      <h1>What we can tell you today, stated plainly.</h1>
      <p class="lead">A trust page that overstates itself defeats its own purpose. Everything below is labeled Verified, In Progress, or Verify — and nothing moves to Verified until it's actually confirmed, the same discipline our Security page has run under since it was written.</p>
    </div>
  </div>
</section>

<section class="page-sec">
  <div class="container legal-wrap">
    <div class="legal-grid">

      <nav class="legal-toc" aria-label="Table of contents">
        <span class="legal-toc-label">On this page</span>
        <ol>
          <?php foreach ($sections as $i => $sec): ?>
            <li><a href="#<?= e($sec['id']) ?>"><span class="n"><?= sprintf('%02d', $i + 1) ?></span><?= $sec['title'] ?></a></li>
          <?php endforeach; ?>
        </ol>
      </nav>

      <article class="legal-article">
        <div class="legal-meta"><span>Last updated: <strong>30 August 2026</strong></span><span>Entity: <strong>M/S Paynancial Technology Private Limited</strong></span></div>

        <p class="legal-intro">This page exists alongside our <a class="inline-link" href="/security">Security</a> page, not instead of it — Security covers the technical detail; this page is the at-a-glance status of everything a business evaluating Paynancial would want to check before trusting it with real transactions, including the parts that aren't fully documented yet.</p>

        <div class="legal-section" id="at-a-glance">
          <span class="sec-num">01 / <?= sprintf('%02d', $sectionCount) ?></span>
          <h2>Trust, At a Glance</h2>
          <div class="legal-body">
            <div class="status-grid">
              <div class="status-item"><span>Encryption in transit &amp; at rest</span><span class="status-pill verified">Verified</span></div>
              <div class="status-item"><span>Cardholder data segmentation</span><span class="status-pill verified">Verified</span></div>
              <div class="status-item"><span>Real-time fraud monitoring</span><span class="status-pill verified">Verified</span></div>
              <div class="status-item"><span>Least-privilege access controls</span><span class="status-pill verified">Verified</span></div>
              <div class="status-item"><span>Documented incident response plan</span><span class="status-pill verified">Verified</span></div>
              <div class="status-item"><span>PCI DSS certification</span><span class="status-pill verify">Verify</span></div>
              <div class="status-item"><span>RBI Payment Aggregator authorization</span><span class="status-pill verify">Verify</span></div>
              <div class="status-item"><span>Formal business continuity plan</span><span class="status-pill verify">Verify</span></div>
              <div class="status-item"><span>AI governance framework (written)</span><span class="status-pill verify">Verify</span></div>
            </div>
            <p>"Verified" means confirmed and already described in detail on our Security page. "Verify" means it needs a formal confirmation — a certificate, a signed document, a specific regulatory status — before we're willing to state it here as fact. A "Verify" label is not a claim that something is missing; it's a statement that we haven't published a claim about it yet.</p>
          </div>
        </div>

        <div class="legal-section" id="security-foundations">
          <span class="sec-num">02 / <?= sprintf('%02d', $sectionCount) ?></span>
          <h2>Security Foundations</h2>
          <div class="legal-body">
            <p>All data in transit is encrypted with TLS 1.2 or higher; sensitive data at rest, including KYC documents and bank details, uses AES-256. Card data is tokenized on receipt and never stored raw on our application servers. Systems handling cardholder data run in a separate, access-restricted environment with regular vulnerability scanning. Internal access follows least privilege, is logged, and requires multi-factor authentication for privileged actions. Transactions pass through real-time risk scoring before funds move.</p>
            <p>Full detail on each of these — plus how to report a vulnerability — lives on the <a class="inline-link" href="/security">Security</a> page, which this Trust Center doesn't duplicate.</p>
          </div>
        </div>

        <div class="legal-section" id="privacy">
          <span class="sec-num">03 / <?= sprintf('%02d', $sectionCount) ?></span>
          <h2>Privacy</h2>
          <div class="legal-body">
            <p>What data we collect, why, and how long we keep it is set out in full in our <a class="inline-link" href="/legal/privacy-policy">Privacy Policy</a> — this section exists so Privacy has a visible home in the Trust Center rather than only being reachable from the legal footer.</p>
          </div>
        </div>

        <div class="legal-section" id="continuity">
          <span class="sec-num">04 / <?= sprintf('%02d', $sectionCount) ?></span>
          <h2>Business Continuity &amp; Disaster Recovery</h2>
          <div class="legal-body">
            <p>Our production infrastructure already runs in access-controlled cloud environments with network firewalls and DDoS mitigation in front of every public endpoint, and deployments are version-controlled and auditable — see Security Foundations above. A formal, published business continuity and disaster recovery plan — covering specific recovery time objectives and failover procedures — is not yet documented on this page.</p>
            <div class="legal-callout warn"><strong>Verify before publication:</strong> this section will state specific recovery objectives and failover architecture once confirmed internally — not before.</div>
          </div>
        </div>

        <div class="legal-section" id="ai-governance">
          <span class="sec-num">05 / <?= sprintf('%02d', $sectionCount) ?></span>
          <h2>AI Governance</h2>
          <div class="legal-body">
            <p>The operating principle behind every AI feature Paynancial offers is described in full on the <a class="inline-link" href="/agentic-ai#governance">Agentic AI</a> page: permissions define what an agent can do, policy limits cap how much, human oversight reviews anything above a threshold, every action is authenticated to a specific key or session, and every action is logged against the rule that authorized it.</p>
            <p>What's confirmed today: this principle governs how our AI &amp; Intelligence products (AI Fraud Detection, AI Reconciliation, AI Financial Assistant, AI Cash-Flow Intelligence, AI Revenue Forecasting) are designed to operate. What's not yet published: a standalone, formally reviewed AI governance policy document, distinct from the product-level description above.</p>
          </div>
        </div>

        <div class="legal-section" id="human-oversight">
          <span class="sec-num">06 / <?= sprintf('%02d', $sectionCount) ?></span>
          <h2>Human Oversight</h2>
          <div class="legal-body">
            <p>No AI capability on this site is positioned as removing a business's ability to require human approval. Spending limits, beneficiary allow-lists and approval thresholds are configured by the business using the platform, not fixed by Paynancial — a business decides how much of a workflow an agent handles unattended, and can tighten or loosen that at any time.</p>
          </div>
        </div>

        <div class="legal-section" id="auditability">
          <span class="sec-num">07 / <?= sprintf('%02d', $sectionCount) ?></span>
          <h2>Auditability</h2>
          <div class="legal-body">
            <p>Every write action against the API — a payment, a payout, a refund — is tied to the specific API key or session that made the request, and idempotency keys mean a retried request is recognized rather than treated as a new action. Webhooks provide a real-time, timestamped record of every state change, which is the same data an audit trail draws from.</p>
          </div>
        </div>

        <div class="legal-section" id="how-we-label">
          <span class="sec-num">08 / <?= sprintf('%02d', $sectionCount) ?></span>
          <h2>How We Label Claims on This Page</h2>
          <div class="legal-body">
            <table class="legal-table">
              <tr><th>Label</th><th>What it means</th></tr>
              <tr><td><span class="status-pill verified">Verified</span></td><td>Confirmed practice, described in detail elsewhere on this site (typically the Security page).</td></tr>
              <tr><td><span class="status-pill progress">In Progress</span></td><td>Actively being built or documented — used only once we can say that truthfully.</td></tr>
              <tr><td><span class="status-pill planned">Planned</span></td><td>On the roadmap with a committed timeline — used only once one exists.</td></tr>
              <tr><td><span class="status-pill verify">Verify</span></td><td>Not yet confirmed internally. We'd rather show this label than guess.</td></tr>
            </table>
            <p>If you need a specific answer for a procurement or compliance review that isn't covered above, <a class="inline-link" href="/contact?intent=support&topic=trust">contact our team</a> directly rather than relying on this page alone.</p>
          </div>
        </div>

        <div class="legal-related">
          <h4>Related pages</h4>
          <div class="legal-related-links">
            <a href="/security">Security</a>
            <a href="/agentic-ai#governance">Agentic AI Governance</a>
            <a href="/legal/privacy-policy">Privacy Policy</a>
            <a href="/legal/terms-conditions">Terms &amp; Conditions</a>
          </div>
        </div>

      </article>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="cta-band reveal">
      <h2>Need something confirmed for a review or audit?</h2>
      <div class="hero-actions" style="justify-content:center;margin-top:24px;">
        <a href="/contact?intent=support&topic=trust" class="btn btn-primary">Contact Our Team</a>
      </div>
    </div>
  </div>
</section>
