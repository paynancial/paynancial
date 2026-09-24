<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'payment-integration-go-live-checklist',
    'category'    => 'developers',
    'type'        => 'general',
    'status'      => 'in_review',
    'created'     => '2026-09-23',
    'updated'     => '2026-09-23',
    'editor'      => null,
    'approved_by' => null,
    'approved_on' => null,
    'indexable'   => false,
    'sitemap'     => false,

    'title'       => 'Testing a payment integration: a sandbox-to-live checklist',
    'meta_title'  => 'Payment Integration Testing: Sandbox to Go-Live Checklist | Paynancial Insights',
    'description' => 'What to test in a payment sandbox before going live: success, decline and time-out paths, webhooks, refunds, idempotency, security of keys, monitoring, and a go-live day plan.',
    'dek'         => 'The happy path is the easy part. A payment integration is ready for real money when it behaves correctly on declines, time-outs, duplicates and late confirmations — and you have proved it in the sandbox first.',

    'question'    => 'How do you test a payment integration before going live?',
    'answer'      => 'Use the provider\'s sandbox to test every path, not just success: declined payments, abandoned and timed-out payments, late confirmations, refunds and duplicate webhook deliveries. Confirm your system records the correct final status in each case, that retries use idempotency keys, and that live keys are stored securely. Then go live with monitoring in place and a small, watched first batch of real payments.',
    'takeaways'   => [
        'Test failure paths as thoroughly as the success path.',
        'Your system\'s final recorded status must match the provider\'s in every scenario.',
        'Keep sandbox and live keys separate, and never ship keys in client-side code.',
        'Go live gradually and watch closely.',
    ],

    'sections' => [
        ['sandbox', 'Why the sandbox matters', <<<'HTML'
<p>A sandbox is a test environment that behaves like the live system but moves no real money. It lets you create payments, trigger outcomes and receive webhooks safely. Everything in this checklist should pass in the sandbox before a live key is used. Start at the <a href="/sandbox">Paynancial Sandbox</a>.</p>
HTML],
        ['payments', 'Payment scenarios to test', <<<'HTML'
<div class="blog-table"><table>
  <thead><tr><th>Scenario</th><th>What should happen in your system</th></tr></thead>
  <tbody>
    <tr><th>Successful payment</th><td>Order marked paid once; confirmation sent once.</td></tr>
    <tr><th>Declined payment</th><td>Order stays unpaid; customer sees a clear message and a way to retry.</td></tr>
    <tr><th>Customer abandons checkout</th><td>Order stays pending, then expires or is followed up; nothing is marked paid.</td></tr>
    <tr><th>Time-out, then success</th><td>Status shown as pending, then updated to paid when confirmed — no duplicate order.</td></tr>
    <tr><th>Customer closes the browser after paying</th><td>Order still marked paid, via webhook or status check.</td></tr>
    <tr><th>Customer pays twice</th><td>Duplicate detected and refunded; order not fulfilled twice.</td></tr>
  </tbody>
</table></div>
HTML],
        ['webhooks', 'Webhook scenarios to test', <<<'HTML'
<ul>
  <li>a valid webhook is accepted and processed once;</li>
  <li>a webhook with an invalid signature is rejected;</li>
  <li>the same webhook delivered twice has no extra effect;</li>
  <li>events arriving out of order do not move an order backwards;</li>
  <li>your endpoint being unavailable for a while is recovered by retries or your scheduled status check.</li>
</ul>
<p>Details in <a href="/blog/reliable-webhook-handler">Webhooks 101</a>.</p>
HTML],
        ['money-back', 'Refunds and payouts', <<<'HTML'
<ul>
  <li>full and partial refunds update the order and your records correctly;</li>
  <li>a refund request retried after a time-out does not refund twice (see <a href="/blog/idempotency-keys-payment-apis">idempotency keys</a>);</li>
  <li>for payouts: success, failure (for example invalid account details) and pending outcomes are each handled, and failed payouts are surfaced to a person.</li>
</ul>
HTML],
        ['security', 'Security checks', <<<'HTML'
<ul>
  <li>API keys and webhook secrets live in server-side configuration, never in source code, mobile apps or browser code;</li>
  <li>sandbox and live credentials are separate and cannot be mixed up by configuration mistakes;</li>
  <li>access to live keys is limited to the people and systems that need them;</li>
  <li>logs do not record full card numbers, secrets or other sensitive data;</li>
  <li>amounts and currency are set on your server, never trusted from the browser.</li>
</ul>
HTML],
        ['monitoring', 'Monitoring before you launch', <<<'HTML'
<ul>
  <li>alerts for a drop in payment success rate or a rise in errors;</li>
  <li>alerts for webhook delivery failures and a growing processing backlog;</li>
  <li>a daily view of payments still pending after a reasonable time;</li>
  <li>a named person on call for the first days after launch.</li>
</ul>
HTML],
        ['go-live', 'Go-live day', <<<'HTML'
<ol>
  <li>switch to live keys through configuration, not code changes;</li>
  <li>make a small real payment yourself and follow it end to end — order, webhook, settlement report;</li>
  <li>refund it and confirm the refund end to end;</li>
  <li>open to customers gradually if you can, and watch the first real payments closely;</li>
  <li>keep a simple rollback plan — for example, a switch back to your previous payment method.</li>
</ol>
HTML],
    ],

    'faqs' => [
        ['What is a payment sandbox?', 'A test environment provided by a payment provider that behaves like the live system but uses no real money, so you can build and test an integration safely.'],
        ['What should I test besides successful payments?', 'Declines, abandoned checkouts, time-outs followed by late success, duplicate payments, refunds, and webhook edge cases such as invalid signatures, duplicates and out-of-order events.'],
        ['Where should API keys be stored?', 'In server-side configuration or a secrets manager, never in source code, browser code or mobile apps. Keep sandbox and live keys separate.'],
        ['How do I know my integration is ready to go live?', 'When every scenario in your test plan passes in the sandbox, your recorded statuses always match the provider\'s, secrets are handled securely and monitoring is in place.'],
    ],

    'related' => ['reliable-webhook-handler', 'idempotency-keys-payment-apis', 'choosing-how-to-get-paid-online'],
    'links'   => [['Sandbox', '/sandbox'], ['Integration Guide', '/developers/integration-guide'], ['Authentication', '/developers/authentication']],
];
