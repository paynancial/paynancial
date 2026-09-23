<?php
$page_meta = [
    'title' => 'Developers | Agent-Ready Payment APIs | Paynancial',
    'description' => 'Integrate Paynancial payments with clear API documentation, SDKs and a sandbox environment — designed for human developers and AI agents alike.',
];
?>
<section style="padding-top:56px;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Developers</span>
      <h1>Build with the Paynancial API.</h1>
      <p class="lead">A REST API, webhooks and SDKs designed to get payments into your product quickly and securely — built to be called just as reliably by an AI agent as by a person, from a single sandbox key to an enterprise integration processing millions of calls.</p>
    </div>
  </div>
</section>

<section class="section-subtle" id="docs">
  <div class="container grid grid-3">
    <div class="card reveal" id="integration-guide"><span class="card-icon">◆</span><h3>Integration Guide</h3><p>Step-by-step setup instructions for common stacks and frameworks.</p></div>
    <div class="card reveal" id="sdks"><span class="card-icon">◆</span><h3>SDKs</h3><p>Official client libraries for PHP, JavaScript and Python.</p></div>
    <div class="card reveal" id="api-reference"><span class="card-icon">◆</span><h3>API Reference</h3><p>Every endpoint, parameter and response documented in full.</p></div>
    <div class="card reveal" id="sandbox"><span class="card-icon">◆</span><h3>Sandbox</h3><p>Test your integration safely with sandbox API keys before going live.</p></div>
    <div class="card reveal" id="webhooks"><span class="card-icon">◆</span><h3>Webhook Documentation</h3><p>Subscribe to real-time events for payments, refunds and settlements.</p></div>
    <div class="card reveal"><span class="card-icon">◆</span><h3>API Keys</h3><p>Manage sandbox and live API keys from your dashboard.</p></div>
  </div>
</section>

<section id="authentication">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Authentication</span>
      <h2>Authenticate with an API key</h2>
      <p>All requests are authenticated using an API key passed as basic-auth username. Keep live keys server-side only.</p>
    </div>
    <div class="code-panel reveal">
      <div class="code-tabs">
        <button class="code-tab is-active" data-lang="php">PHP</button>
        <button class="code-tab" data-lang="js">JavaScript</button>
        <button class="code-tab" data-lang="python">Python</button>
        <button class="code-tab" data-lang="curl">cURL</button>
      </div>
      <div class="code-body">
        <button class="copy-btn" type="button">Copy</button>
        <pre data-code-block="php"><code>$client = new Paynancial\Client('YOUR_API_KEY');
$refund = $client->refunds->create([
    'payment_id' =&gt; 'pay_9F3kd82',
    'amount'     =&gt; 20000,
]);</code></pre>
        <pre data-code-block="js" style="display:none"><code>const client = new Paynancial({ key: 'YOUR_API_KEY' });
const refund = await client.refunds.create({
  payment_id: 'pay_9F3kd82',
  amount: 20000,
});</code></pre>
        <pre data-code-block="python" style="display:none"><code>client = paynancial.Client('YOUR_API_KEY')
refund = client.refunds.create(
    payment_id='pay_9F3kd82',
    amount=20000,
)</code></pre>
        <pre data-code-block="curl" style="display:none"><code>curl https://api.paynancial.com/v1/refunds \
  -u YOUR_API_KEY: \
  -d payment_id=pay_9F3kd82 \
  -d amount=20000</code></pre>
      </div>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container grid grid-4">
    <?php foreach (['Payment API', 'Payout API', 'Transaction API', 'Refund API'] as $api): ?>
      <div class="card reveal"><h3><?= e($api) ?></h3><p style="font-size:0.85rem;">Full reference documentation and examples.</p></div>
    <?php endforeach; ?>
  </div>
</section>

<section id="agentic-ai">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">The Agentic AI Era</span>
      <h2>Designed for the caller to be an agent, not just a person.</h2>
      <p class="lead">A growing share of integrations calling this API won't be a person clicking through a checkout — they'll be an AI agent retrying a failed charge, reconciling a ledger, or approving a payout inside limits a business set. That changes what "reliable" means for an API, at any scale from a solo developer's script to an enterprise's agent fleet.</p>
    </div>
    <div class="grid grid-3">
      <div class="card reveal">
        <span class="card-icon">◆</span>
        <h3>Safe to retry, by default</h3>
        <p>An agent retries on timeout without asking a human first. Every write endpoint accepts an idempotency key, so a retried request is recognized and returns the original result instead of creating a duplicate payment or payout.</p>
      </div>
      <div class="card reveal">
        <span class="card-icon">◆</span>
        <h3>Structured, machine-readable errors</h3>
        <p>Error responses carry a stable code and category an agent can branch on programmatically — insufficient_funds vs. invalid_method vs. rate_limited — not just a human-readable message an agent has to guess at.</p>
      </div>
      <div class="card reveal">
        <span class="card-icon">◆</span>
        <h3>Event-driven, not poll-driven</h3>
        <p>Webhooks push payment, payout, refund and settlement events as they happen, so an agent orchestrating a workflow reacts to state changes in real time instead of polling on a schedule.</p>
      </div>
    </div>
    <div class="code-panel reveal" style="margin-top:32px;">
      <div class="code-tabs">
        <button class="code-tab is-active" data-lang="php">PHP</button>
        <button class="code-tab" data-lang="curl">cURL</button>
      </div>
      <div class="code-body">
        <button class="copy-btn" type="button">Copy</button>
        <pre data-code-block="php"><code>// An agent-safe payout: the idempotency key means a retried
// call after a timeout returns the original result, not a
// second payout.
$payout = $client->payouts->create([
    'beneficiary_id' => 'bene_3Kd91',
    'amount'         => 250000, // in paise
    'mode'           => 'upi',
], [
    'idempotency_key' => 'agent-payout-run-2026-08-29-0417',
]);

echo $payout->status;</code></pre>
        <pre data-code-block="curl" style="display:none"><code>curl https://api.paynancial.com/v1/payouts \
  -u YOUR_API_KEY: \
  -H "Idempotency-Key: agent-payout-run-2026-08-29-0417" \
  -d beneficiary_id=bene_3Kd91 \
  -d amount=250000 \
  -d mode=upi</code></pre>
      </div>
    </div>
    <div class="compliance-note reveal">The sandbox is the recommended place to test any agent-driven or autonomous workflow — including retry behavior, rate limits and failure handling — before it's given a live key.</div>
    <p class="reveal" style="margin-top:18px;"><a class="card-link" href="/agentic-ai#payment-orchestration">Read more about AI payment orchestration and governance →</a></p>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta-band reveal">
      <h2>Ready to start integrating?</h2>
      <div class="hero-actions" style="justify-content:center;margin-top:24px;">
        <a href="/contact?intent=sales" class="btn btn-primary">Request Sandbox Access</a>
      </div>
    </div>
  </div>
</section>
