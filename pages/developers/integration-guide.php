<?php
/** Integration Guide — /developers/integration-guide */
require_once __DIR__ . '/../../includes/faq-data.php';
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/developer-docs.php';

$faqs = faq_set('integration-guide');
$trail = [['Home', '/'], ['Developers', '/developers'], ['Integration Guide', '/developers/integration-guide']];
$page_meta = sp_meta([
    'title'       => 'Integration Guide | From Sandbox to Live | Paynancial Developers',
    'description' => 'Step-by-step guide to integrating Paynancial: get a sandbox key, make your first API call, receive webhooks, handle errors and retries, and go live safely.',
    'path'        => '/developers/integration-guide',
    'h1'          => 'Integration Guide',
    'type'        => 'TechArticle',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$resources = dev_resources();

sp_track_view('developer_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developers · Integration Guide',
    'h1'        => 'From sandbox key to first live payment.',
    'lead'      => 'The steps every Paynancial integration goes through — in order, with the page that covers each one in detail.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => ['Seven steps', 'Sandbox first', 'Go-live checklist'],
]);
?>

<?php sp_band_open('choose'); ?>
  <?php sp_head('choose', 'Before you start', 'Choose what you are integrating.', 'Each product maps to one API resource. Start with the one that matches how your business gets paid or pays out.'); ?>
  <?php sp_table(['If you want to…', 'Integrate', 'API resource'], array_map(fn ($r) => [e($r['does']), '<a class="inline-link" href="' . e($r['product'][1]) . '">' . e($r['product'][0]) . '</a>', '<code>POST ' . e($r['path']) . '</code>'], array_values($resources)), 'Products and their API resources'); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('steps', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('steps', 'Step by step', 'Seven steps to a production integration.'); ?>
    <ol class="sp-steps sp-steps--vertical">
      <?php foreach ([
          ['Request sandbox access', 'Ask for a sandbox API key. Everything you build before going live runs against the sandbox, with no real money involved.', ['Sandbox', '/sandbox']],
          ['Authenticate', 'Store the key on your server and send it with every request using HTTP basic authentication.', ['Authentication', '/developers/authentication']],
          ['Make your first call', 'Create a payment (or a payment link, payout or collection) and read the resource the API returns.', ['API Reference', '/developers/api-reference']],
          ['Receive webhooks', 'Set up an HTTPS endpoint so you learn about payments, payouts, refunds and settlements as they happen.', ['Webhooks', '/developers/webhooks']],
          ['Handle errors and retries', 'Branch on the error code, and send an idempotency key with every write so a retry never creates a duplicate.', ['Errors & idempotency', '/developers/api-reference#errors']],
          ['Test the unhappy paths', 'Timeouts, declined payments, duplicate webhooks and rate limits — test each one in the sandbox.', ['Test scenarios', '/sandbox#scenarios']],
          ['Go live', 'Swap in your live key on your server, confirm the go-live checklist below, and monitor your first live transactions closely.', ['Checklist', '#go-live']],
      ] as $i => [$title, $text, [$linkLabel, $href]]): ?>
      <li class="sp-step reveal"><span class="sp-step-num"><?= sprintf('%02d', $i + 1) ?></span><div><strong><?= e($title) ?></strong><p><?= e($text) ?> <a class="inline-link" href="<?= e($href) ?>"><?= e($linkLabel) ?> →</a></p></div></li>
      <?php endforeach; ?>
    </ol>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('first-call'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('first-call', 'Step 3 in detail', 'Your first API call.', 'Set up a client with your sandbox key and create a payment. Amounts are in paise, so 50000 is ₹500.00, and receipt is your own order reference.'); ?>
      <?= sp_code(dev_client_setup(), 'Set up a client') ?>
    </div>
    <?= sp_code(dev_resource_code($resources['payments']), 'Create a payment') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('go-live', 'ink'); ?>
  <?php sp_head('go-live', 'Go-live checklist', 'Before you switch to a live key.'); ?>
  <?php sp_answers([
      ['Live key on the server only', 'The live key is loaded from an environment variable or secrets manager, never from code in a repository or anything a customer can download.'],
      ['Sandbox and live kept apart', 'Development and test environments still use sandbox keys, so a test can never move real money.'],
      ['Every write is idempotent', 'Payments, refunds and payouts are sent with an idempotency key, and retries reuse it.'],
      ['Errors are handled by code', 'Your integration branches on error codes such as insufficient_funds, invalid_method and rate_limited.'],
      ['Webhook handler is robust', 'It responds quickly, skips duplicates, does not depend on arrival order, and you have confirmed with support how to verify the source.'],
      ['Someone is watching', 'You know who monitors the first live transactions, and how to reach Paynancial developer support if something looks wrong.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'Integration questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Keep building', 'Related developer resources.'); ?>
  <?php sp_related(dev_related('integration-guide', ['sandbox', 'api-reference', 'webhooks', 'sdks'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Start step one today.', 'Request a sandbox key and build your integration with no real money involved.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Contact Developer Support', dev_support_url(), 'cta_click'],
]); ?>
