<?php
/** SDKs — /developers/sdks */
require_once __DIR__ . '/../../includes/faq-data.php';
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/developer-docs.php';

$faqs = faq_set('sdks');
$trail = [['Home', '/'], ['Developers', '/developers'], ['SDKs', '/developers/sdks']];
$page_meta = sp_meta([
    'title'       => 'SDKs for PHP, JavaScript & Python | Paynancial Developers',
    'description' => 'Paynancial SDKs for PHP, JavaScript and Python: create a client with your API key and call payments, refunds, payouts, payment links and collections with a few lines of code.',
    'path'        => '/developers/sdks',
    'h1'          => 'SDKs',
    'type'        => 'TechArticle',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$resources = dev_resources();
$setup = dev_client_setup();

sp_track_view('developer_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developers · SDKs',
    'h1'        => 'Official SDKs for PHP, JavaScript and Python.',
    'lead'      => 'Create a client with your API key and call the Paynancial API in the idiom of your language — the SDK handles authentication and request formatting.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => ['PHP', 'JavaScript', 'Python'],
    'aside'     => sp_code(dev_resource_code($resources['refunds']), 'Create a refund'),
]);
?>

<?php sp_band_open('languages'); ?>
  <?php sp_head('languages', 'Languages', 'Three SDKs, one API.', 'Each SDK wraps the same REST API. Resources are available on the client object: payments, refunds, payouts, payment links and collections.'); ?>
  <?php sp_table(['Language', 'Create a client', 'Call style'], [
      ['PHP', '<code>' . e($setup['php'][1]) . '</code>', '<code>$client-&gt;payments-&gt;create([...])</code>'],
      ['JavaScript', '<code>' . e($setup['js'][1]) . '</code>', '<code>await client.payments.create({...})</code>'],
      ['Python', '<code>' . e($setup['python'][1]) . '</code>', '<code>client.refunds.create(...)</code>'],
  ], 'Paynancial SDKs'); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('examples', 'dim'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('examples', 'Examples', 'The same call in every language.', 'Creating a payment is a single call. Amounts are in paise, so 50000 is ₹500.00.'); ?>
      <div class="sp-prose reveal">
        <ul>
          <li>The SDK adds your API key to every request.</li>
          <li>It builds the request body from the parameters you pass.</li>
          <li>It returns the created resource, so you can read fields like <code>id</code> or <code>status</code>.</li>
        </ul>
      </div>
    </div>
    <?= sp_code(dev_resource_code($resources['payments']), 'Create a payment') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('idempotency', 'ink'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('idempotency', 'Safe retries', 'Idempotency keys in the SDK.', 'Pass an idempotency key as an option on any create call. If a request times out and you retry with the same key, you get the original result instead of a duplicate payout.'); ?>
    </div>
    <?= sp_code(dev_resource_code($resources['payouts']), 'Payout with an idempotency key') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('rest'); ?>
  <div class="sp-split">
    <?php sp_head('rest', 'No SDK for your language?', 'Call the REST API directly.'); ?>
    <div class="sp-prose reveal">
      <p>The SDKs are a convenience, not a requirement. From any language that can make an HTTPS request, send form-encoded fields to <code><?= e(DEV_API_BASE) ?></code> with your API key as the basic-auth username — exactly what the cURL examples throughout the <a class="inline-link" href="/developers/api-reference">API Reference</a> do.</p>
      <p>For SDK installation details for your language and environment, <a class="inline-link" href="<?= e(dev_support_url()) ?>">contact developer support</a>.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'SDK questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Keep building', 'Related developer resources.'); ?>
  <?php sp_related(dev_related('sdks', ['api-reference', 'authentication', 'integration-guide', 'sandbox'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Try the SDKs against the Sandbox.', 'Request a sandbox key and run your first call with no real money involved.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Contact Developer Support', dev_support_url(), 'cta_click'],
]); ?>
