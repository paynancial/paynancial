<?php
/** Authentication — /developers/authentication */
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/developer-docs.php';

$faqs = [
    ['How do I authenticate with the Paynancial API?', 'Send your API key using HTTP basic authentication: the key is the username and the password is left empty. In cURL that is -u YOUR_API_KEY: (note the trailing colon). The SDKs take the key when you create the client.'],
    ['What is the difference between a sandbox key and a live key?', 'A sandbox key runs requests in the sandbox, where no real money moves. A live key processes real payments and payouts. Build and test with a sandbox key; switch to a live key only when your integration is ready.'],
    ['Where do I manage my API keys?', 'Sandbox and live API keys are managed from your Paynancial dashboard.'],
    ['Can I use my API key in a browser or mobile app?', 'Not a live key. Anyone who can see the key can make requests as your business, so live keys belong on your server only. Your browser or app should call your server, and your server calls Paynancial.'],
    ['What should I do if a key is exposed?', 'Treat it as compromised: replace it from your dashboard, update your server with the new key and stop using the old one. If you are unsure what to do, contact developer support.'],
];
$trail = [['Home', '/'], ['Developers', '/developers'], ['Authentication', '/developers/authentication']];
$page_meta = sp_meta([
    'title'       => 'API Authentication | Paynancial Developers',
    'description' => 'How to authenticate with the Paynancial API: API keys over HTTP basic authentication, sandbox and live keys, and how to keep live keys server-side and safe.',
    'path'        => '/developers/authentication',
    'h1'          => 'Authentication',
    'type'        => 'TechArticle',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);

sp_track_view('developer_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developers · Authentication',
    'h1'        => 'Authenticate every request with an API key.',
    'lead'      => 'Paynancial uses API keys over HTTP basic authentication. A sandbox key lets you build and test; a live key moves real money and stays on your server.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => ['HTTP basic auth', 'Sandbox and live keys', 'Keys managed in your dashboard'],
    'aside'     => sp_code(dev_client_setup(), 'Authenticate a client'),
]);
?>

<?php sp_band_open('how-it-works'); ?>
  <div class="sp-split">
    <?php sp_head('how-it-works', 'How it works', 'Your API key is your identity.'); ?>
    <div class="sp-prose reveal">
      <p>Every request to <code><?= e(DEV_API_BASE) ?></code> carries your API key using <strong>HTTP basic authentication</strong>: the key is sent as the username and the password is left empty. With cURL you write <code>-u YOUR_API_KEY:</code> — the trailing colon tells cURL there is no password.</p>
      <p>The PHP, JavaScript and Python SDKs take the key once, when you create the client, and add it to every request for you.</p>
      <p>Because the key identifies your business, every write request — a payment, a payout, a refund — is tied to the key that made it. That is also what makes an audit trail possible: you can always tell which key, and therefore which system, initiated an action.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('environments', 'dim'); ?>
  <?php sp_head('environments', 'Environments', 'Two kinds of key, two environments.', 'Sandbox and live API keys are managed from your Paynancial dashboard.'); ?>
  <?php sp_table(['', 'Sandbox key', 'Live key'], [
      ['Purpose', 'Build and test an integration.', 'Process real payments and payouts.'],
      ['Money moved', 'None — no real funds are involved.', 'Real funds.'],
      ['Who should hold it', 'Developers building the integration.', 'Your production server only.'],
      ['When to use it', 'From your first request until testing is complete.', 'After the integration has been tested in the sandbox.'],
  ], 'Sandbox and live keys compared'); ?>
  <p class="reveal" style="margin-top:18px;"><a class="card-link" href="/sandbox">Learn how to test in the Sandbox →</a></p>
<?php sp_band_close(); ?>

<?php sp_band_open('key-safety', 'ink'); ?>
  <?php sp_head('key-safety', 'Keeping keys safe', 'Treat a live key like a password to your bank account.', 'Anyone holding a live key can make requests as your business. These practices keep it that way.'); ?>
  <?php sp_answers([
      ['Keep live keys server-side', 'Never put a live key in a web page, a mobile app, or anything a customer can download. Your front end calls your server; your server calls Paynancial.'],
      ['Keep keys out of source control', 'Load keys from environment variables or a secrets manager, not from code committed to a repository.'],
      ['Separate keys by environment', 'Use sandbox keys in development and testing, and the live key only in production, so a test can never move real money.'],
      ['Replace a key you suspect is exposed', 'If a key may have leaked, replace it from your dashboard and update your server. Do not wait to find out whether it was used.'],
      ['Give agents their own limits', 'When an AI agent or automated job calls the API, pair its access with the permissions and policy limits your business sets. See AI Governance.'],
      ['Never share keys with support', 'Developer support never needs your API key. Send the request and response instead, with the key removed.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'Authentication questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Keep building', 'Related developer resources.'); ?>
  <?php sp_related(dev_related('authentication', ['api-reference', 'sandbox', 'webhooks', 'integration-guide'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Start with a sandbox key.', 'Build and test with no real money involved, then switch to a live key when you are ready.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Contact Developer Support', dev_support_url(), 'cta_click'],
]); ?>
