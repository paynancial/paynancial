<?php
/** Webhooks — /developers/webhooks */
require_once __DIR__ . '/../../includes/faq-data.php';
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/developer-docs.php';

$faqs = faq_set('webhooks');
$trail = [['Home', '/'], ['Developers', '/developers'], ['Webhooks', '/developers/webhooks']];
$page_meta = sp_meta([
    'title'       => 'Webhooks | Real-Time Payment Events | Paynancial Developers',
    'description' => 'Paynancial webhooks push payment, payout, refund and settlement events to your server as they happen. Learn how they work and how to build a reliable webhook handler.',
    'path'        => '/developers/webhooks',
    'h1'          => 'Webhooks',
    'type'        => 'TechArticle',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);

sp_track_view('developer_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developers · Webhooks',
    'h1'        => 'React to every payment event as it happens.',
    'lead'      => 'Webhooks push payment, payout, refund and settlement events to your server in real time — so your systems, and any AI agent working for you, act on changes instead of polling for them.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => ['Payments', 'Payouts', 'Refunds', 'Settlements'],
]);
?>

<?php sp_band_open('what'); ?>
  <div class="sp-split">
    <?php sp_head('what', 'What webhooks are', 'Paynancial tells you when something changes.'); ?>
    <div class="sp-prose reveal">
      <p>When a payment, payout, refund or settlement changes state, Paynancial sends an HTTPS request to an endpoint on your server describing what happened. Your server responds, and then does whatever that event means for your business — fulfilling an order, marking a vendor as paid, updating your books.</p>
      <p>Webhooks also give you a real-time, timestamped record of every state change in your account. That is the same data an audit trail draws from, and the reason an automated workflow can react to events instead of checking for them on a schedule.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('events', 'dim'); ?>
  <?php sp_head('events', 'Events', 'Four families of events.', 'For the exact event names and payload fields for your account, contact developer support.'); ?>
  <?php sp_table(['Event family', 'Sent when', 'Typical action in your system'], dev_webhook_categories(), 'Webhook event families'); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('handler', 'ink'); ?>
  <?php sp_head('handler', 'Building a reliable handler', 'Six rules for a reliable webhook endpoint.', 'These are general practices for receiving webhooks from any payments platform, and they apply to Paynancial.'); ?>
  <?php sp_steps([
      ['Use an HTTPS endpoint', 'Receive events on a URL served over HTTPS, reachable from the internet.'],
      ['Respond quickly', 'Acknowledge the request as soon as you have stored it, then do slow work — emails, fulfilment — afterwards.'],
      ['Expect duplicates', 'Record which events you have processed and skip any you have already handled.'],
      ['Do not rely on order', 'Do not assume events arrive in the order they happened. Act on the state each event describes.'],
      ['Verify the source', 'Before going live, confirm with developer support how to check that a request came from Paynancial.'],
      ['Log everything', 'Keep the raw event and your response. When something goes wrong, the log is the first thing you will need.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('agents'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('agents', 'Event-driven automation', 'Why agents need webhooks.'); ?>
      <div class="sp-prose reveal">
        <p>An AI agent reconciling a ledger or retrying a failed collection needs to know the moment something changes. Webhooks give it that signal without polling, and the idempotency keys it sends on write requests mean acting on an event twice cannot create a duplicate payment or payout.</p>
        <p>Pair event-driven agents with the permissions, policy limits and approval thresholds described on the <a class="inline-link" href="/ai-governance">AI Governance</a> page.</p>
      </div>
    </div>
    <?php sp_answers([
        ['Without webhooks', 'The agent asks on a timer whether anything changed, reacts late, and spends most requests learning nothing.'],
        ['With webhooks', 'The agent is told the moment a payment, payout, refund or settlement changes, and acts on it immediately.'],
    ]); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'Webhook questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Keep building', 'Related developer resources.'); ?>
  <?php sp_related(dev_related('webhooks', ['api-reference', 'sandbox', 'integration-guide', 'authentication'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Test your webhook handler in the Sandbox.', 'Build and test your integration with no real money involved before going live.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Contact Developer Support', dev_support_url(), 'cta_click'],
]); ?>
