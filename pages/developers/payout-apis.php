<?php
/** Payout APIs — /developers/payout-apis: the money-out side of the API. */
require_once __DIR__ . '/../../includes/faq-data.php';
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/developer-docs.php';

$faqs = faq_set('payout-apis');
$trail = [['Home', '/'], ['Developers', '/developers'], ['Payout APIs', '/developers/payout-apis']];
$page_meta = sp_meta([
    'title'       => 'Payout APIs | Send Payouts to Bank Accounts & UPI IDs | Paynancial Developers',
    'description' => 'The Paynancial Payout API: send money to bank accounts and UPI IDs in India from your own systems, singly or in bulk, with idempotency keys, structured errors and payout webhooks.',
    'path'        => '/developers/payout-apis',
    'h1'          => 'Payout APIs',
    'type'        => 'TechArticle',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$resources = dev_resources();
$po = $resources['payouts'];

sp_track_view('developer_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developers · Payout APIs',
    'h1'        => 'Payout APIs: send money from your own systems — exactly once.',
    'lead'      => 'Pay vendors, staff, sellers and partners to a bank account or UPI ID with a single call, follow every payout by webhook, and retry safely with an idempotency key.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['Full API Reference', '/developers/api-reference#payouts', 'api_reference_click'],
    'values'    => ['Bank accounts', 'UPI IDs', 'Single or bulk', 'Idempotent'],
    'aside'     => sp_code(dev_resource_code($po), 'A payout with an idempotency key'),
]);
?>
<nav class="sp-index" aria-label="On this page">
  <div class="sp-wrap"><ul>
    <li><a href="#request">The request</a></li>
    <li><a href="#lifecycle">Payout lifecycle</a></li>
    <li><a href="#idempotency">Idempotency</a></li>
    <li><a href="#errors">Errors</a></li>
    <li><a href="#bulk">Bulk</a></li>
    <li><a href="#india">In India</a></li>
    <li><a href="#faq">FAQ</a></li>
  </ul></div>
</nav>

<?php sp_band_open('request'); ?>
  <div class="sp-split">
    <?php sp_head('request', 'The request', 'POST ' . $po['path'] . ', in three parameters.', $po['does']); ?>
    <div>
      <?php sp_table(['Parameter', 'Meaning'], array_map(fn ($p) => ['<code>' . e($p[0]) . '</code>', $p[1]], $po['params']), 'Payout parameters'); ?>
      <p class="reveal" style="margin-top:18px;"><?= $po['returns'] ?> The idempotency key travels as an <code>Idempotency-Key</code> header in cURL, or as an option in the SDK. For parameters beyond the published example, <a class="inline-link" href="<?= e(dev_support_url()) ?>">ask developer support</a>.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('lifecycle', 'dim'); ?>
  <?php sp_head('lifecycle', 'Payout lifecycle', 'From beneficiary to payout report.'); ?>
  <?php sp_steps([
      ['Save the beneficiary', 'Store the recipient\'s bank account or UPI ID once, and reuse its beneficiary id.'],
      ['Create the payout', 'Call the Payout API with the beneficiary, the amount in paise, the mode and an idempotency key.'],
      ['Read the status', 'The response carries the payout\'s status; it moves from initiated to completed.'],
      ['Handle the webhook', 'A payout event arrives as the status changes — mark the vendor, employee or partner as paid here.'],
      ['Report', 'Completed payouts appear in your payout report for reconciliation.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('idempotency', 'ink'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('idempotency', 'Idempotency', 'Why every payout needs a key.', 'A payout that times out might have gone through. Without a key, retrying could pay twice; with the same key, the retry returns the original payout instead.'); ?>
      <?php sp_answers([
          ['Make the key deterministic', 'Build it from your own run and beneficiary — for example payout-run-2026-08-29-0417 — so a re-run produces the same key.'],
          ['One key per payout', 'Never reuse a key for a different payout; it identifies exactly one transfer.'],
      ]); ?>
    </div>
    <?= sp_code(['curl' => ['cURL', $po['curl']]], 'The Idempotency-Key header') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('errors'); ?>
  <div class="sp-split">
    <?php sp_head('errors', 'Errors', 'What to do when a payout is refused.', 'Errors come back with a structured code, so your code can decide what to do without parsing text.'); ?>
    <?php sp_table(['Code', 'Means', 'What to do'], dev_error_examples(), 'Payout error codes'); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('bulk', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('bulk', 'Bulk', 'Paying many beneficiaries at once.'); ?>
    <div class="sp-prose reveal">
      <p>Payouts supports bulk: a batch of transfers submitted in a single request, with every payout in the batch tracked individually and a clear reason for any that fail. The published example shows a single payout; ask <a class="inline-link" href="<?= e(dev_support_url()) ?>">developer support</a> for the batch request format.</p>
      <p>Whether you send one request per payout or a batch, give each payout its own idempotency key, and handle failures payout by payout — the rest of the batch has already moved. See <a class="inline-link" href="/products/bulk-payouts">Bulk Payouts</a>.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('india'); ?>
  <div class="sp-split">
    <?php sp_head('india', 'In India', 'Paying out in India.'); ?>
    <?php sp_answers([
        ['UPI or bank transfer', 'Set mode to upi to pay a UPI ID; bank transfer to an account is also supported.'],
        ['Amounts in paise', '250000 is ₹2,500.00. Convert once, at the edge of your system.'],
        ['Tax is yours to calculate', 'Payouts to vendors, contractors and sellers can attract TDS. Paynancial does not calculate or deduct it — send the net amount your CA agrees.'],
        ['Check the beneficiary', 'Verify a new beneficiary\'s details before sending a large first payout; a payout to the wrong account is hard to recover.'],
    ]); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_regulatory('dev:payout-apis', 'paying out in India'); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'Payout API questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Keep building', 'Related developer resources.'); ?>
  <?php sp_related(dev_related('payout-apis', ['payment-apis', 'webhooks', 'sdks', 'sandbox'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Send a test payout in the Sandbox.', 'Request a sandbox key and try a payout with no real money involved.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Explore Payouts', '/products/payouts', 'cta_click'],
]); ?>
