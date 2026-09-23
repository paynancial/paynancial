<?php
/**
 * Security page: /security
 * Same TOC + article template as pages/legal.php. The "Cardholder Data
 * Handling" section deliberately describes practices rather than
 * asserting a formal PCI DSS certification — see the "no certifications
 * claimed before formally confirmed" rule this project was built under.
 * If Paynancial holds current PCI DSS certification, strengthen that
 * section's wording accordingly.
 */
$legalCompany = 'Paynancial';
$legalName    = 'M/S Paynancial Technology Private Limited';
$legalEmail   = 'hello@paynancial.com';
$legalGst     = '10AAOCP5173C1ZO';
$updated      = '29 August 2026';

$page_meta = [
    'title' => 'Security | Paynancial',
    'description' => "How {$legalCompany} protects the infrastructure that moves your money.",
];

$sections = [
    ['id' => 'commitment', 'title' => 'Our Commitment', 'html' => "
        <p>{$legalCompany} processes financial data at scale, which means our security posture has to hold up under real adversarial pressure, not just pass a checklist. We design for defence in depth — assuming any single control can fail, and layering others behind it — across our infrastructure, application code, and internal processes.</p>
    "],
    ['id' => 'encryption', 'title' => 'Encryption & Data Protection', 'html' => "
        <p>All data in transit between your browser, our APIs, and downstream banking partners is encrypted using TLS 1.2 or higher. Sensitive data at rest — including KYC documents and bank account details — is encrypted using AES-256. Card data is tokenised immediately on receipt; raw card numbers are never stored on our application servers.</p>
    "],
    ['id' => 'card-data', 'title' => 'Cardholder Data Handling', 'html' => "
        <p>Systems that handle cardholder data run in a separate, restricted-access environment, isolated from the rest of our infrastructure by network segmentation. Access to that environment is logged and limited to the roles that require it, and we run regular vulnerability scanning against it.</p>
        <div class=\"legal-callout\"><strong>On certification:</strong> formal compliance certifications (including PCI DSS) are published on this page only once actually issued and confirmed — see our approach to compliance claims below.</div>
    "],
    ['id' => 'infrastructure', 'title' => 'Infrastructure Security', 'html' => "
        <p>Our production infrastructure runs in access-controlled cloud environments with network firewalls, intrusion detection, and DDoS mitigation in front of every public-facing endpoint. Deployments go through automated testing and code review before reaching production, and infrastructure changes are version-controlled and auditable.</p>
    "],
    ['id' => 'fraud', 'title' => 'Fraud Monitoring', 'html' => "
        <p>Transactions pass through automated risk scoring that evaluates velocity, device fingerprint, geolocation, and behavioural signals in real time. Suspicious transactions can be held for manual review or declined outright before funds move, and merchant accounts showing anomalous patterns are flagged for our risk team.</p>
    "],
    ['id' => 'access', 'title' => 'Access Controls', 'html' => "
        <p>Internal access to production systems and customer data follows the principle of least privilege: employees are granted only the access their role requires, all access is logged, and privileged actions require multi-factor authentication. Access is reviewed periodically and revoked immediately on role change or offboarding.</p>
    "],
    ['id' => 'incident', 'title' => 'Incident Response', 'html' => "
        <p>We maintain a documented incident response plan covering detection, containment, eradication, and recovery. In the event of a security incident affecting personal or transaction data, we notify affected merchants and, where required, the relevant regulator, in line with our obligations under the <a class=\"inline-link\" href=\"/legal/privacy-policy\">Privacy Policy</a> and applicable law.</p>
    "],
    ['id' => 'certifications', 'title' => 'Certifications & Compliance', 'html' => "
        <p>We list a certification or regulatory approval on this page only once it has been formally issued and confirmed — not before. This section will be updated as certifications are obtained.</p>
    "],
    ['id' => 'disclosure', 'title' => 'Responsible Disclosure', 'html' => "
        <p>If you believe you've found a security vulnerability in our Services, we want to hear about it before anyone else does. Please report it to us privately rather than disclosing it publicly, and give us a reasonable window to investigate and remediate before any public disclosure.</p>
        <div class=\"legal-callout\"><strong>Report a vulnerability:</strong> email <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a> with steps to reproduce, affected endpoints, and any supporting evidence. Please avoid testing that could degrade service for real merchants or their customers — automated scanning of production systems without prior coordination with us is not permitted.</div>
    "],
    ['id' => 'contact', 'title' => 'Contact Our Security Team', 'html' => "
        <p>For security questions, vulnerability reports, or to request our latest compliance documentation, reach us at <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a>.</p>
    "],
];
$sectionCount = count($sections);
?>
<section class="hero" style="padding-top:56px;">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Trust & Safety</span>
      <h1>Security</h1>
      <p class="lead">How we protect the infrastructure that moves your money, and what to do if you find a problem.</p>
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
            <li><a href="#<?= e($sec['id']) ?>"><span class="n"><?= sprintf('%02d', $i + 1) ?></span><?= e($sec['title']) ?></a></li>
          <?php endforeach; ?>
        </ol>
      </nav>

      <article class="legal-article">
        <div class="legal-meta">
          <span>Last updated: <strong><?= e($updated) ?></strong></span>
          <span>Entity: <strong><?= e($legalName) ?></strong></span>
        </div>

        <p class="legal-intro">Security isn't a feature we bolt on — it's the reason payment infrastructure gets trusted with real money in the first place. This page lays out the controls behind <?= e($legalCompany) ?>, in plain terms.</p>

        <div class="security-pillars">
          <div class="security-pillar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
            <h3>Encrypted by default</h3>
            <p>TLS 1.2+ in transit, AES-256 at rest.</p>
          </div>
          <div class="security-pillar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4 7v5c0 4.5 3.4 8.7 8 10 4.6-1.3 8-5.5 8-10V7l-8-4Z"/></svg>
            <h3>Segmented card data</h3>
            <p>Cardholder data isolated in a restricted environment.</p>
          </div>
          <div class="security-pillar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
            <h3>24/7 monitoring</h3>
            <p>Automated fraud and anomaly detection, always on.</p>
          </div>
          <div class="security-pillar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            <h3>Least-privilege access</h3>
            <p>Role-based controls on every system that touches data.</p>
          </div>
        </div>

        <?php foreach ($sections as $i => $sec): ?>
          <div class="legal-section" id="<?= e($sec['id']) ?>">
            <span class="sec-num"><?= sprintf('%02d', $i + 1) ?> / <?= sprintf('%02d', $sectionCount) ?></span>
            <h2><?= e($sec['title']) ?></h2>
            <div class="legal-body"><?= $sec['html'] ?></div>
          </div>
        <?php endforeach; ?>

        <div class="legal-section" id="entity-details">
          <table class="legal-table">
            <tr><th>Entity</th><td><?= e($legalName) ?></td></tr>
            <tr><th>GST No.</th><td class="mono"><?= e($legalGst) ?></td></tr>
            <tr><th>Email</th><td><?= e($legalEmail) ?></td></tr>
          </table>
        </div>

        <div class="legal-related">
          <h4>Related policies</h4>
          <div class="legal-related-links">
            <a href="/legal/privacy-policy">Privacy Policy</a>
            <a href="/legal/terms-conditions">Terms &amp; Conditions</a>
            <a href="/legal/refund-policy">Refund Policy</a>
            <a href="/legal/cookie-policy">Cookie Policy</a>
            <a href="/security" class="current">Security</a>
          </div>
        </div>

      </article>
    </div>
  </div>
</section>
