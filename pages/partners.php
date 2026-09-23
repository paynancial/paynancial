<?php
$page_meta = [
    'title' => 'Partners | Paynancial Partner Program',
    'description' => 'Grow with Paynancial as a reseller, referral or technology partner.',
];
?>
<section style="padding-top:56px;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Partners</span>
      <h1>Grow your business with the Paynancial Partner Program.</h1>
      <p class="lead">Partner with us as a reseller, referral or technology partner and unlock commission on every business you bring onboard — including platforms building the agentic AI features their own customers are starting to expect.</p>
      <div class="hero-actions">
        <a href="/partner/register" class="btn btn-primary">Join Paynancial Partner Network</a>
        <button type="button" class="btn btn-outline" data-login-open="partner">Partner Login</button>
      </div>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container grid grid-3">
    <div class="card reveal"><span class="card-icon">◆</span><h3>Reseller Partners</h3><p>Resell Paynancial products under your own commercial relationship with merchants.</p></div>
    <div class="card reveal"><span class="card-icon">◆</span><h3>Referral Partners</h3><p>Refer businesses to Paynancial and earn commission on successful onboarding.</p></div>
    <div class="card reveal"><span class="card-icon">◆</span><h3>Technology Partners</h3><p>Integrate Paynancial into your own software platform or marketplace.</p></div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Paynancial Partner Hub</span>
      <h2>Discover, enroll, recommend and grow — in one platform.</h2>
      <p>Enroll customers, get solution recommendations, submit KYC, track approvals, monitor transactions and manage commission without leaving the Partner Hub.</p>
    </div>
    <div class="journey reveal">
      <?php foreach (['Discover', 'Enroll', 'Recommend', 'Submit', 'Activate', 'Manage', 'Grow', 'Earn'] as $i => $step): ?>
        <div class="journey-step"><div class="num"><?= $i + 1 ?></div><strong><?= e($step) ?></strong></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">The Agentic AI Era</span>
      <h2>Partners are building the agentic AI layer for their own customers.</h2>
      <p class="lead">A technology partner embedding Paynancial isn't just adding a payment button — increasingly, they're giving their own customers an AI-assisted finance layer: automated billing retries, reconciled payouts, fraud detection, all running under their brand. Paynancial's job is to make sure the infrastructure underneath that is solid, whichever partner tier you build it through.</p>
    </div>
    <div class="grid grid-3">
      <div class="card reveal">
        <span class="card-icon">◆</span>
        <h3>Technology partners</h3>
        <p>SaaS platforms and marketplaces embed Payment, Payout and Billing APIs directly, then layer their own AI features — like an automated collections assistant — on top of infrastructure they don't have to build themselves.</p>
      </div>
      <div class="card reveal">
        <span class="card-icon">◆</span>
        <h3>Reseller partners</h3>
        <p>As more merchants ask about "AI-powered" payments, resellers can point to a real catalog — AI Fraud Detection, AI Reconciliation, AI Financial Assistant — instead of a marketing slide.</p>
      </div>
      <div class="card reveal">
        <span class="card-icon">◆</span>
        <h3>Referral partners</h3>
        <p>Every business you refer, from a two-person startup to an enterprise finance team, is somewhere on the same curve toward letting AI agents handle more of its day-to-day payment operations — and needs infrastructure built for that from day one.</p>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta-band reveal">
      <h2>Ready to become a Paynancial partner?</h2>
      <div class="hero-actions" style="justify-content:center;margin-top:24px;">
        <a href="/partner/register" class="btn btn-primary">Apply Now</a>
      </div>
    </div>
  </div>
</section>
