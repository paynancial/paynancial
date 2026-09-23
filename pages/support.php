<?php
$page_meta = [
    'title' => 'Support | Paynancial Help Center',
    'description' => 'Get help with Paynancial payment products. Browse FAQs or contact our support team.',
];
$faqs = [
    ['How do I get started with Paynancial?', 'Reach out through our contact form or create an account, and our team will guide you through onboarding and KYC.'],
    ['Which payment methods are supported?', 'Cards, UPI, netbanking and wallets are supported through the Payment Gateway product.'],
    ['How do refunds work?', 'Refunds can be initiated from your dashboard and are tracked through to settlement.'],
    ['How do I report a security concern?', 'Email hello@paynancial.com with details and our team will respond promptly.'],
    ['How can partners track commission?', 'Commission and settlement tracking are available in the Partner Portal.'],
];
?>
<section style="padding-top:56px;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Support</span>
      <h1>We're here to help.</h1>
      <p class="lead">Reach out for assistance with your Paynancial integration, account or a general question — including how agentic AI fits into your payment operations.</p>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container grid grid-3">
    <div class="card reveal">
      <span class="card-icon">◆</span>
      <h3>Sales</h3>
      <p>Talk to our team about accepting payments with Paynancial.</p>
      <a class="card-link" href="/contact?intent=sales">Contact Sales →</a>
    </div>
    <div class="card reveal">
      <span class="card-icon">◆</span>
      <h3>Help &amp; Support</h3>
      <p>Get help with an existing account or integration issue.</p>
      <a class="card-link" href="/contact?intent=support">Get Support →</a>
    </div>
    <div class="card reveal">
      <span class="card-icon">◆</span>
      <h3>Articles &amp; News</h3>
      <p>Read the latest on payment technology and product updates.</p>
      <a class="card-link" href="/blog">Read Articles →</a>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">The Agentic AI Era · A Primer</span>
      <h2>Understanding agentic AI in payments.</h2>
      <p class="lead">A resource for teams evaluating what it means to let AI act on their behalf financially — whether that's a two-person startup or an enterprise treasury desk.</p>
    </div>
    <div class="ledger reveal">
      <div class="ledger-row">
        <span class="ledger-tag">What it is</span>
        <h3>An agent takes an action, not just an answer</h3>
        <p>A chatbot tells you your invoice is overdue. An agent goes and retries the failed charge, updates the invoice status, and reconciles the result — without a person clicking through each step.</p>
      </div>
      <div class="ledger-row">
        <span class="ledger-tag">Why it matters here</span>
        <h3>Payments are where "acting" gets expensive to get wrong</h3>
        <p>A wrong answer from a chatbot is a bad sentence. A wrong action from a payment agent is a duplicate payout or a missed fraud signal — which is why idempotency, structured errors and audit trails matter more here than almost anywhere else an agent operates.</p>
      </div>
      <div class="ledger-row">
        <span class="ledger-tag">Questions worth asking</span>
        <h3>Before you let an agent move money</h3>
        <p>What's the agent's spending or payout limit, and who's notified when it's reached? Can every action it takes be traced back to a specific request? What happens if it retries a call it shouldn't have? A platform built agent-first — idempotent by default, with real-time webhooks and clear audit trails — answers these before you have to find out the hard way.</p>
      </div>
    </div>
    <p class="reveal" style="margin-top:18px;"><a class="card-link" href="/agentic-ai">Read the full Agentic AI in Finance guide →</a></p>
  </div>
</section>

<section class="section-subtle" id="faqs" aria-labelledby="faqs-heading">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">FAQs</span>
      <h2 id="faqs-heading">Frequently asked questions</h2>
    </div>
    <div class="grid" style="gap:14px;">
      <?php foreach ($faqs as $i => [$q, $a]): ?>
        <details class="card reveal" style="cursor:pointer;">
          <summary style="font-weight:650;list-style:none;"><?= e($q) ?></summary>
          <p class="text-muted" style="margin-top:12px;"><?= e($a) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="cta-band reveal">
      <h2>Still need help?</h2>
      <div class="hero-actions" style="justify-content:center;margin-top:24px;">
        <a href="/contact" class="btn btn-primary">Contact Support</a>
      </div>
    </div>
  </div>
</section>
