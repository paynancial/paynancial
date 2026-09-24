<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'idempotency-keys-payment-apis',
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

    'title'       => 'Idempotency keys: why payment APIs need them and how to use them',
    'meta_title'  => 'Idempotency Keys in Payment APIs Explained | Paynancial Insights',
    'description' => 'What idempotency means, why network time-outs make it essential for payments and payouts, how idempotency keys work, and practical rules for generating, storing and retrying with them.',
    'dek'         => 'A request times out. Did the payment go through? Retry and you might charge the customer twice; do not retry and you might lose the sale. Idempotency keys remove the dilemma.',

    'question'    => 'What is an idempotency key in a payment API?',
    'answer'      => 'An idempotency key is a unique value your system sends with a request that creates something — a payment, refund or payout. If the same request is sent again with the same key, the API returns the result of the first request instead of performing the action a second time. That makes it safe to retry after a time-out without risking duplicate charges or payouts.',
    'takeaways'   => [
        'An operation is idempotent if doing it twice has the same effect as doing it once.',
        'Time-outs leave you not knowing whether a request succeeded — retries are only safe with idempotency.',
        'Generate one key per intended operation, and reuse it for every retry of that operation.',
        'Never reuse a key for a different payment.',
    ],

    'sections' => [
        ['problem', 'The problem: "did it work?"', <<<'HTML'
<p>Your server asks the payment API to create a payout. The network is slow, and your request times out before the response arrives. There are two possibilities:</p>
<ul>
  <li>the request never reached the API, so no payout was created; or</li>
  <li>the payout was created, but the response was lost on the way back.</li>
</ul>
<p>From your side, the two look identical. Retrying fixes the first case and causes a duplicate payout in the second. For money movement, both outcomes — losing the operation or doing it twice — are unacceptable.</p>
HTML],
        ['how', 'How idempotency keys solve it', <<<'HTML'
<p>With an idempotency key, the retry is safe in both cases:</p>
<ol>
  <li>Before sending the request, your system generates a unique key for this specific operation and stores it alongside your own record (for example, the payout row in your database).</li>
  <li>It sends the request with the key, typically in a request header.</li>
  <li>If the request times out, it retries with <strong>the same key</strong>.</li>
  <li>If the API already processed that key, it returns the original result instead of creating a second payout. If it never saw the key, it processes the request normally.</li>
</ol>
<pre><code>POST /payouts
Idempotency-Key: 5f6d2c1e-8b3a-4c2f-9e71-2a0d4b9c7e13
{ "amount": 250000, "beneficiary_id": "ben_123", "reference": "INV-2041" }</code></pre>
<p>The header name and format vary by provider — check the <a href="/developers/api-reference">API reference</a> for the exact details.</p>
HTML],
        ['rules', 'Rules for using idempotency keys', <<<'HTML'
<ul>
  <li><strong>One key per intended operation.</strong> The key represents "this payout for invoice INV-2041", not "a payout".</li>
  <li><strong>Store the key before sending.</strong> If your own process crashes mid-request, you can recover the key and retry safely.</li>
  <li><strong>Reuse it for every retry</strong> of that operation — and only that operation.</li>
  <li><strong>Keep the request body identical</strong> on retries. Many APIs reject a reused key with different parameters, to protect you from mistakes.</li>
  <li><strong>Use unguessable values</strong>, such as UUIDs, rather than sequential numbers.</li>
  <li><strong>Back off between retries</strong>, and stop after a sensible number of attempts; then check status through the API.</li>
</ul>
HTML],
        ['where', 'Where you need them', <<<'HTML'
<div class="blog-table"><table>
  <thead><tr><th>Request</th><th>Idempotency key?</th></tr></thead>
  <tbody>
    <tr><th>Create a payment or order</th><td>Yes</td></tr>
    <tr><th>Create a refund</th><td>Yes — a duplicate refund is money lost</td></tr>
    <tr><th>Create a payout</th><td>Yes — the highest-risk case</td></tr>
    <tr><th>Fetch a payment's status</th><td>Not needed — reading is naturally safe to repeat</td></tr>
  </tbody>
</table></div>
HTML],
        ['beyond', 'Idempotency on your side too', <<<'HTML'
<p>The same idea applies inside your own system. Webhook handlers should be idempotent by event ID (see <a href="/blog/reliable-webhook-handler">Webhooks 101</a>), and background jobs that trigger payments should check whether the payment already exists before creating it. Together, these make duplicate money movement structurally impossible rather than merely unlikely.</p>
HTML],
    ],

    'faqs' => [
        ['What does idempotent mean?', 'An operation is idempotent if performing it more than once has the same effect as performing it once.'],
        ['When should I generate a new idempotency key?', 'For each new operation you intend to perform — each new payment, refund or payout. Reuse the same key only when retrying that same operation.'],
        ['What happens if I reuse a key with a different amount?', 'Behaviour depends on the provider, but many APIs reject the request because the parameters do not match the original. Never reuse a key for a different operation.'],
        ['Do I need idempotency keys for GET requests?', 'Generally no. Reading data, such as fetching a payment\'s status, does not change anything and is already safe to repeat.'],
    ],

    'related' => ['reliable-webhook-handler', 'payment-integration-go-live-checklist', 'reduce-failed-payments'],
    'links'   => [['API Reference', '/developers/api-reference'], ['Payout APIs', '/developers/payout-apis'], ['Payment APIs', '/developers/payment-apis']],
];
