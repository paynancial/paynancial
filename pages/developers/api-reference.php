<?php
/** API Reference — /developers/api-reference */
require_once __DIR__ . '/../../includes/faq-data.php';
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/developer-docs.php';

$faqs = faq_set('api-reference');
$trail = [['Home', '/'], ['Developers', '/developers'], ['API Reference', '/developers/api-reference']];
$page_meta = sp_meta([
    'title'       => 'API Reference | Paynancial Developers',
    'description' => 'Paynancial API Reference: base URL, authentication, payments, refunds, payouts, payment links and collections, idempotency keys and error codes, with PHP, JavaScript and cURL examples.',
    'path'        => '/developers/api-reference',
    'h1'          => 'API Reference',
    'type'        => 'TechArticle',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$resources = dev_resources();

sp_track_view('api_reference_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developers · API Reference',
    'h1'        => 'API Reference',
    'lead'      => 'The resources, request conventions and error handling behind every Paynancial integration — with working examples in PHP, JavaScript and cURL.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['Integration Guide', '/developers/integration-guide', 'documentation_click'],
    'values'    => ['Base URL ' . DEV_API_BASE, 'Basic auth with an API key', 'Amounts in paise'],
]);
?>
<nav class="sp-index" aria-label="On this page">
  <div class="sp-wrap"><ul>
    <li><a href="#overview">Overview</a></li>
    <li><a href="#conventions">Conventions</a></li>
    <?php foreach ($resources as $key => $r): ?><li><a href="#<?= e(str_replace('_', '-', $key)) ?>"><?= e($r['name']) ?></a></li><?php endforeach; ?>
    <li><a href="#idempotency">Idempotency</a></li>
    <li><a href="#errors">Errors</a></li>
    <li><a href="#faq">FAQ</a></li>
  </ul></div>
</nav>

<?php sp_band_open('overview'); ?>
  <div class="sp-split">
    <?php sp_head('overview', 'Overview', 'What the Paynancial API is.'); ?>
    <div class="sp-prose reveal">
      <p>The Paynancial API is a REST API over HTTPS for accepting payments, refunding them, sending payouts, creating payment links and running scheduled collections. Every request is authenticated with an API key and returns the resource it created or changed.</p>
      <p>The same API is used by a developer's server, by Paynancial's SDKs and by AI agents acting for a business. Write endpoints accept an idempotency key so any caller can retry safely.</p>
      <div class="sp-note">This reference covers the resources and parameters used in Paynancial's published examples. For endpoints, parameters or response fields not listed here, <a class="inline-link" href="<?= e(dev_support_url()) ?>">contact developer support</a>.</div>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('conventions', 'dim'); ?>
  <?php sp_head('conventions', 'Request conventions', 'How every request is made.'); ?>
  <?php sp_table(['Convention', 'Detail'], [
      ['Base URL', '<code>' . e(DEV_API_BASE) . '</code>'],
      ['Transport', 'HTTPS only.'],
      ['Authentication', 'HTTP basic authentication with your API key as the username and an empty password (<code>-u YOUR_API_KEY:</code> in cURL). See <a class="inline-link" href="/developers/authentication">Authentication</a>.'],
      ['Environments', 'A sandbox key runs requests in the sandbox; a live key moves real money. See <a class="inline-link" href="/sandbox">Sandbox</a>.'],
      ['Amounts', 'Integers in the smallest currency unit — paise for INR.'],
      ['Request body', 'Form-encoded fields, as in the cURL examples; the SDKs build the request for you.'],
      ['Idempotency', '<code>Idempotency-Key</code> header on write requests (the SDKs take an <code>idempotency_key</code> option).'],
      ['Errors', 'A stable error code your code can branch on, alongside a readable message.'],
  ], 'Request conventions'); ?>
<?php sp_band_close(); ?>

<?php $i = 0; foreach ($resources as $key => $r): $i++; $id = str_replace('_', '-', $key); ?>
<?php sp_band_open($id, $i % 2 ? 'paper' : 'dim'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head($id, 'POST ' . $r['path'], $r['name'], $r['does']); ?>
      <?php sp_table(['Parameter', 'Description'], array_map(fn ($p) => ['<code>' . e($p[0]) . '</code>', $p[1]], $r['params']), $r['name'] . ' parameters'); ?>
      <p class="reveal" style="margin-top:16px;color:var(--text-muted);"><?= $r['returns'] ?> Used by <a class="inline-link" href="<?= e($r['product'][1]) ?>"><?= e($r['product'][0]) ?></a>.</p>
    </div>
    <?= sp_code(dev_resource_code($r), 'POST ' . $r['path']) ?>
  </div>
<?php sp_band_close(); ?>
<?php endforeach; ?>

<?php sp_band_open('idempotency', 'ink'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('idempotency', 'Idempotency', 'Retry without paying twice.', 'Networks time out. When they do, you cannot tell whether the request reached Paynancial. Send an idempotency key with every write request, and a retry with the same key returns the original result rather than creating a second payment, refund or payout.'); ?>
      <div class="sp-prose reveal">
        <ul>
          <li>Use one unique key per intended action — for example your payout run ID.</li>
          <li>Reuse the same key only when retrying that same action.</li>
          <li>This matters most for automated callers and AI agents, which retry without asking a person first.</li>
        </ul>
      </div>
    </div>
    <?= sp_code(dev_resource_code($resources['payouts']), 'Payout with an idempotency key') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('errors'); ?>
  <?php sp_head('errors', 'Errors', 'Errors your code can act on.', 'Error responses carry a stable code and a readable message. Branch on the code, not the message. These are the codes shown in Paynancial\'s published examples:'); ?>
  <?php sp_table(['Code', 'Meaning', 'What to do'], dev_error_examples(), 'Example error codes'); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'API Reference questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Keep building', 'Related developer resources.'); ?>
  <?php sp_related(dev_related('api-reference', ['authentication', 'webhooks', 'sdks', 'sandbox'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Test every call before it touches real money.', 'Request a sandbox key and run these examples end to end.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Contact Developer Support', dev_support_url(), 'cta_click'],
]); ?>
