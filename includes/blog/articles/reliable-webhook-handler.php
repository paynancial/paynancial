<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'reliable-webhook-handler',
    'category'    => 'developers',
    'type'        => 'general',
    'status'      => 'indexable',
    'created'     => '2026-09-23',
    'updated'     => '2026-09-23',
    'editor'      => null,
    'approved_by' => 'Paynancial Editorial Team',
    'approved_on' => '2026-09-24',
    'indexable'   => true,
    'sitemap'     => true,

    'title'       => 'Webhooks 101: building a reliable webhook handler for payments',
    'meta_title'  => 'How to Build a Reliable Payment Webhook Handler | Paynancial Insights',
    'description' => 'How payment webhooks work and how to handle them safely: verify signatures, respond quickly, process asynchronously, handle duplicates and out-of-order events, and reconcile with the status API.',
    'dek'         => 'Webhooks are how a payment provider tells your system that something happened — a payment succeeded, a refund completed, a payout failed. Handling them well is the difference between an integration that quietly works and one that quietly loses orders.',

    'question'    => 'How do you handle payment webhooks reliably?',
    'answer'      => 'Verify every webhook\'s signature before trusting it, acknowledge it quickly with a success response, and do the real processing asynchronously. Make processing idempotent so duplicate deliveries have no extra effect, do not assume events arrive in order, and periodically check payment status through the API to catch anything a webhook missed.',
    'takeaways'   => [
        'Verify the signature on every webhook before acting on it.',
        'Respond fast, then process in the background.',
        'Expect duplicates and out-of-order events — design for both.',
        'Webhooks are a notification, not your only source of truth: reconcile with the status API.',
    ],

    'sections' => [
        ['what', 'What a webhook is', <<<'HTML'
<p>A webhook is an HTTP request that the payment provider sends to a URL you choose when an event happens. Instead of your system repeatedly asking "has this payment completed yet?", the provider tells you as soon as it knows.</p>
<p>A typical event contains an event type (for example <code>payment.captured</code>), an event ID, a timestamp and details of the object it relates to — the payment, refund or payout.</p>
HTML],
        ['verify', 'Rule 1: Verify before you trust', <<<'HTML'
<p>Your webhook URL is reachable from the internet, so anyone could send it a request that looks like a payment success. Providers sign webhooks — typically with an HMAC of the raw request body using a secret shared with you — so you can prove the request is genuine.</p>
<pre><code>raw_body  = read the request body exactly as received
expected  = HMAC_SHA256(webhook_secret, raw_body)
received  = request header carrying the signature
if not constant_time_equals(expected, received):
    respond 400 and stop</code></pre>
<ul>
  <li>compute the signature over the <strong>raw</strong> body, before any JSON parsing or re-formatting;</li>
  <li>use a constant-time comparison;</li>
  <li>keep the webhook secret in server configuration, never in code or client-side apps;</li>
  <li>reject events with timestamps far outside your tolerance, to limit replay.</li>
</ul>
<p>Check your provider's documentation for the exact header name and signing method — see <a href="/developers/webhooks">Paynancial webhooks</a>.</p>
HTML],
        ['fast', 'Rule 2: Acknowledge quickly, process later', <<<'HTML'
<p>Providers expect a fast success response. If your endpoint is slow or times out, the provider will treat the delivery as failed and retry it — creating duplicates and a growing backlog.</p>
<ol>
  <li>verify the signature;</li>
  <li>store the event (or put it on a queue);</li>
  <li>respond with a 2xx status straight away;</li>
  <li>process the event in a background worker.</li>
</ol>
HTML],
        ['duplicates', 'Rule 3: Expect duplicates', <<<'HTML'
<p>Retries mean the same event can arrive more than once. Your processing must be <strong>idempotent</strong>: handling an event twice has the same effect as handling it once.</p>
<ul>
  <li>record each event ID when you process it, and skip IDs you have already seen;</li>
  <li>make state changes conditional — "mark order paid if not already paid" rather than "add a payment";</li>
  <li>never trigger side effects such as emails or shipments twice for the same event.</li>
</ul>
HTML],
        ['order', 'Rule 4: Do not rely on order', <<<'HTML'
<p>Events can arrive out of sequence — a refund event could be processed before the capture event it relates to, if deliveries are retried. Protect against this by:</p>
<ul>
  <li>treating the object's current status as the truth, fetching it from the API if in doubt;</li>
  <li>allowing only valid status transitions, and ignoring updates that would move an object backwards;</li>
  <li>using timestamps or version numbers on objects, where available.</li>
</ul>
HTML],
        ['reconcile', 'Rule 5: Reconcile with the status API', <<<'HTML'
<p>Webhooks can be delayed or, if your endpoint was down long enough, missed. A scheduled job that checks the status of recent payments that are still pending in your system catches anything that slipped through. It is also the right fallback when a customer returns from checkout before the webhook arrives.</p>
HTML],
        ['checklist', 'Webhook handler checklist', <<<'HTML'
<ul>
  <li>HTTPS endpoint, separate secrets for sandbox and live;</li>
  <li>signature verified on the raw body with a constant-time comparison;</li>
  <li>event stored and acknowledged with a 2xx quickly;</li>
  <li>processing in the background, idempotent by event ID;</li>
  <li>status transitions validated; out-of-order events handled;</li>
  <li>alerting when deliveries fail or the backlog grows;</li>
  <li>a scheduled status check for payments still pending.</li>
</ul>
HTML],
    ],

    'faqs' => [
        ['Why do I receive the same webhook more than once?', 'Providers retry deliveries that are not acknowledged in time or that fail, so the same event can arrive more than once. Make your processing idempotent by recording event IDs and skipping ones already handled.'],
        ['How do I verify a webhook signature?', 'Compute the signature over the raw request body using your webhook secret, following your provider\'s documented method, and compare it with the signature header using a constant-time comparison. Reject the request if they differ.'],
        ['What should my webhook endpoint return?', 'A 2xx success status as soon as the event is verified and stored. Do slow processing in the background so the response is not delayed.'],
        ['Can I rely only on webhooks for payment status?', 'Use them as your main notification, but also check the status of pending payments through the API on a schedule, in case a webhook is delayed or missed.'],
    ],

    'related' => ['idempotency-keys-payment-apis', 'payment-integration-go-live-checklist', 'reduce-failed-payments'],
    'links'   => [['Webhooks', '/developers/webhooks'], ['API Reference', '/developers/api-reference'], ['Sandbox', '/sandbox']],
];
