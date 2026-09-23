<?php
/** Sandbox — /sandbox (standalone; transactional developer intent). */
require_once __DIR__ . '/../includes/faq-data.php';
require_once __DIR__ . '/../includes/standalone-ui.php';
require_once __DIR__ . '/../includes/developer-docs.php';

$faqs = faq_set('sandbox');
$trail = [['Home', '/'], ['Developers', '/developers'], ['Sandbox', '/sandbox']];
$page_meta = sp_meta([
    'title'       => 'Sandbox | Test Paynancial Without Live Payments',
    'description' => 'The Paynancial Sandbox lets you build and test your payments integration with sandbox API keys and no real funds — including failures, retries, idempotency and rate limits — before going live.',
    'path'        => '/sandbox',
    'h1'          => 'Test Paynancial without touching live payments.',
    'type'        => 'WebPage',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$resources = dev_resources();

sp_track_view('sandbox_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developers · Sandbox',
    'h1'        => 'Test Paynancial without touching live payments.',
    'lead'      => 'Build and test your whole integration with a sandbox API key — payments, payouts, webhooks, failures and retries — with no real funds involved. When it works, switch the key and go live.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['Read the API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => ['No real funds', 'Same API calls as production', 'Built for testing agents'],
    'aside'     => sp_code(dev_resource_code($resources['payments']), 'A sandbox request, authenticated with a sandbox key'),
]);
?>

<?php sp_band_open('what'); ?>
  <div class="sp-split">
    <?php sp_head('what', 'What is the Sandbox?', 'A safe place to get it wrong.'); ?>
    <div class="sp-prose reveal">
      <p>The Paynancial Sandbox is a test environment for your integration. Requests authenticated with a <strong>sandbox API key</strong> run in the sandbox, so you can run a complete integration test with <strong>no real funds involved</strong>.</p>
      <p>You make the same API calls, with the same SDKs, that you will make in production — authenticated with a sandbox key while you build and test, and a live key once you are ready.</p>
      <p>That makes the sandbox the right place to find out what happens when things go wrong — a timeout, a declined payment, a duplicate request — before a real customer or a real payout is affected.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('how', 'dim'); ?>
  <?php sp_head('how', 'How it works', 'Four steps from access to production.'); ?>
  <?php sp_steps([
      ['Request access', 'Ask for sandbox access through the contact page.'],
      ['Get your sandbox key', 'Sandbox and live API keys are managed from your Paynancial dashboard.'],
      ['Build and test', 'Call the API with your sandbox key and test every path your integration can take.'],
      ['Switch to live', 'When testing is complete, replace the sandbox key with your live key on your server.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('credentials'); ?>
  <?php sp_head('credentials', 'Sandbox credentials', 'Sandbox key or live key?', 'Your API key decides whether a request is a test or real. Keep them apart.'); ?>
  <?php sp_table(['', 'Sandbox key', 'Live key'], [
      ['Real money', 'No', 'Yes'],
      ['Use it for', 'Building, testing, demos, agent trials', 'Production traffic only'],
      ['Where it lives', 'Development and test environments', 'Your production server only'],
  ], 'Sandbox and live keys'); ?>
  <p class="reveal" style="margin-top:18px;"><a class="card-link" href="/developers/authentication">How authentication works →</a></p>
<?php sp_band_close(); ?>

<?php sp_band_open('test-payments', 'dim'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('test-payments', 'Test payments and payouts', 'Exercise every product you use.', 'Make the same calls you will make in production, authenticated with your sandbox key.'); ?>
      <?php sp_table(['Test', 'Call'], array_map(fn ($r) => [e($r['name']), '<code>POST ' . e($r['path']) . '</code>'], array_values($resources)), 'Calls to test in the sandbox'); ?>
    </div>
    <?= sp_code(dev_resource_code($resources['payouts']), 'Test a payout') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('scenarios', 'ink'); ?>
  <?php sp_head('scenarios', 'Test scenarios', 'What to test before you go live.', 'The sandbox is the recommended place to test retry behaviour, rate limits and failure handling. Work through each of these. Ask developer support which failure scenarios can be simulated for your account.'); ?>
  <?php sp_answers([
      ['Error handling', 'Does your code branch on the error code — insufficient_funds, invalid_method, rate_limited — rather than the message, and tell the user something useful?'],
      ['Idempotency', 'Send the same write twice with the same idempotency key. You should get the original result back, not a second payment or payout.'],
      ['Timeouts and retries', 'When a request times out, does your code retry with the same idempotency key, and give up gracefully after a sensible number of attempts?'],
      ['Rate limits', 'When you receive rate_limited, does your code back off and retry later instead of hammering the API?'],
      ['Webhooks', 'Does your handler respond quickly, skip events it has already processed, and cope with events arriving in any order?'],
      ['Agent workflows', 'If an AI agent or automated job calls the API, does it stay inside the limits you set, and stop when it should?'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('production'); ?>
  <div class="sp-split">
    <?php sp_head('production', 'Moving to production', 'Going live starts with a key change.'); ?>
    <div class="sp-prose reveal">
      <p>Because you test with the same API calls you will make in production, going live means replacing your sandbox key with your live key — on your production server only — and keeping sandbox keys in every other environment. Confirm with developer support whether anything else changes for your account.</p>
      <ul>
        <li>Store the live key in an environment variable or secrets manager, never in code.</li>
        <li>Confirm with developer support how to verify that webhook requests come from Paynancial.</li>
        <li>Watch your first live transactions closely.</li>
      </ul>
      <p>The <a class="inline-link" href="/developers/integration-guide#go-live">go-live checklist</a> in the Integration Guide covers each item in detail.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('security', 'dim'); ?>
  <?php sp_head('security', 'Security considerations', 'Test data is still your data.'); ?>
  <?php sp_answers([
      ['Treat sandbox keys as secrets', 'A sandbox key cannot move real money, but it still identifies your account. Keep it out of public repositories.'],
      ['Never mix keys', 'Do not load a live key in a development or test environment, even briefly.'],
      ['Do not use real customer data', 'Use made-up names, emails and references when testing.'],
      ['Test permissions too', 'If agents or automated jobs will call the API, test the limits and approval thresholds you plan to give them. See AI Governance.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('faq'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'Sandbox questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related', 'dim'); ?>
  <?php sp_head('related', 'Keep building', 'Related developer resources.'); ?>
  <?php sp_related(dev_related('sandbox', ['api-reference', 'integration-guide', 'webhooks', 'authentication'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Get your sandbox key.', 'Request sandbox access and start building with no real money involved.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Contact Developer Support', dev_support_url(), 'cta_click'],
]); ?>
