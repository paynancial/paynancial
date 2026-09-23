<?php
/** Payment APIs — /developers/payment-apis: the money-in side of the API. */
require_once __DIR__ . '/../../includes/faq-data.php';
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/developer-docs.php';

$faqs = faq_set('payment-apis');
$trail = [['Home', '/'], ['Developers', '/developers'], ['Payment APIs', '/developers/payment-apis']];
$page_meta = sp_meta([
    'title'       => 'Payment APIs | Accept Payments, Links, Collections & Refunds | Paynancial Developers',
    'description' => 'The Paynancial Payment APIs for accepting money in India: create payments, payment links and recurring collections, issue refunds and pull transaction reports — one REST API with API keys, idempotency keys and webhooks.',
    'path'        => '/developers/payment-apis',
    'h1'          => 'Payment APIs',
    'type'        => 'TechArticle',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$resources = dev_resources();
$moneyIn = array_intersect_key($resources, array_flip(['payments', 'payment_links', 'collections', 'refunds', 'reports']));
$pay = $resources['payments'];

sp_track_view('developer_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developers · Payment APIs',
    'h1'        => 'Payment APIs: accept money from inside your own product.',
    'lead'      => 'Create payments, share payment links, collect on a schedule and refund — all from one REST API, with a real-time status and a webhook for every transaction.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['Full API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => ['Payments', 'Payment links', 'Collections', 'Refunds'],
    'aside'     => sp_code(dev_resource_code($pay), 'Create a payment'),
]);
?>
<nav class="sp-index" aria-label="On this page">
  <div class="sp-wrap"><ul>
    <li><a href="#apis">The APIs</a></li>
    <li><a href="#choose">Which API</a></li>
    <li><a href="#lifecycle">Payment lifecycle</a></li>
    <li><a href="#create">Create a payment</a></li>
    <li><a href="#reliability">Reliability</a></li>
    <li><a href="#india">In India</a></li>
    <li><a href="#faq">FAQ</a></li>
  </ul></div>
</nav>

<?php sp_band_open('apis'); ?>
  <?php sp_head('apis', 'The APIs', 'Five resources for money coming in.', 'Every call goes to ' . DEV_API_BASE . ', authenticated with your API key. Each resource powers a Paynancial product you can also use from the dashboard.'); ?>
  <?php sp_table(['Resource', 'Endpoint', 'What it does', 'Product'], array_map(
      fn ($r) => [e($r['name']), '<code>POST ' . e($r['path']) . '</code>', e($r['does']), '<a class="inline-link" href="' . e($r['product'][1]) . '">' . e($r['product'][0]) . '</a>'],
      array_values($moneyIn)
  ), 'Paynancial Payment APIs'); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('choose', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('choose', 'Which API', 'Pick the call that matches the job.'); ?>
    <?php sp_table(['When you need to…', 'Use'], [
        ['Charge a customer at checkout in your website or app', '<a class="inline-link" href="/developers/api-reference#payments">Payments</a>'],
        ['Get paid without a checkout — on an invoice, by email or message', '<a class="inline-link" href="/developers/api-reference#payment-links">Payment Links</a>'],
        ['Charge the same customer every month, or in instalments', '<a class="inline-link" href="/developers/api-reference#collections">Collections</a>'],
        ['Give money back, in full or in part', '<a class="inline-link" href="/developers/api-reference#refunds">Refunds</a>'],
        ['Hand your finance team a period\'s transactions', '<a class="inline-link" href="/developers/api-reference#reports">Transaction reports</a>'],
    ], 'Choosing a Payment API'); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('lifecycle'); ?>
  <?php sp_head('lifecycle', 'Payment lifecycle', 'From API call to settled money.', 'What happens to a payment you create, and where your code comes in.'); ?>
  <?php sp_steps([
      ['Create', 'Your server creates the payment with an amount, currency and your own receipt reference, and reads its id.'],
      ['Checkout', 'The customer pays on the hosted checkout, or on a checkout UI you build on top of the API — by card, UPI, netbanking or wallet.'],
      ['Status', 'Paynancial returns a real-time status for the transaction.'],
      ['Webhook', 'A payment event reaches your webhook endpoint. Fulfil the order here, not on the customer\'s redirect.'],
      ['Settled', 'The transaction is tied to a settlement record you can reconcile against — and refunded through the Refunds API if needed.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('create', 'dim'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('create', 'Create a payment', 'The parameters in the published example.'); ?>
      <?php sp_table(['Parameter', 'Meaning'], array_map(fn ($p) => ['<code>' . e($p[0]) . '</code>', $p[1]], $pay['params']), 'Payments parameters'); ?>
      <p class="reveal" style="margin-top:18px;"><?= $pay['returns'] ?> For other parameters, see the <a class="inline-link" href="/developers/api-reference#payments">API Reference</a> or <a class="inline-link" href="<?= e(dev_support_url()) ?>">ask developer support</a>.</p>
    </div>
    <?= sp_code(dev_resource_code($resources['payment_links']), 'Create a payment link for an invoice') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('reliability', 'ink'); ?>
  <?php sp_head('reliability', 'Reliability', 'Build it so nothing is charged twice or missed.'); ?>
  <?php sp_answers([
      ['Idempotency keys', 'Send an idempotency key on every create call. A retry with the same key returns the original result instead of creating a duplicate.'],
      ['Webhooks, not redirects', 'Customers close tabs and lose signal. The payment webhook is the reliable signal that money arrived.'],
      ['Structured errors', 'An error code such as invalid_method tells your code whether to fix the request or retry; rate_limited means back off and retry with the same key.'],
      ['Sandbox, then live', 'Build and test with a sandbox key and no real funds, then switch to a live key. Nothing else in your code changes.'],
  ]); ?>
  <p class="reveal" style="margin-top:28px;"><a class="card-link" href="/developers/webhooks" style="color:var(--teal-300);">Webhooks →</a> &nbsp; <a class="card-link" href="/developers/authentication" style="color:var(--teal-300);">Authentication →</a></p>
<?php sp_band_close(); ?>

<?php sp_band_open('india'); ?>
  <div class="sp-split">
    <?php sp_head('india', 'In India', 'Accepting payments from customers in India.'); ?>
    <?php sp_answers([
        ['Amounts in paise', 'The API takes amounts in the smallest currency unit — 50000 is ₹500.00. Never send rupees with decimals.'],
        ['UPI alongside cards', 'UPI sits next to cards, netbanking and wallets at checkout; many customers in India reach for it first.'],
        ['No stored card numbers', 'Under RBI\'s card-on-file tokenisation rules, your systems should never store customers\' actual card numbers — let the checkout handle card details.'],
        ['Recurring needs a mandate', 'Recurring card and UPI payments run on an e-mandate the customer registers, with a notice before each debit.'],
    ]); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_regulatory('dev:payment-apis', 'accepting payments in India'); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'Payment API questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Keep building', 'Related developer resources.'); ?>
  <?php sp_related(dev_related('payment-apis', ['payout-apis', 'webhooks', 'sdks', 'sandbox'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Make your first payment in the Sandbox.', 'Request a sandbox key and create a test payment with no real money involved.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Contact Developer Support', dev_support_url(), 'cta_click'],
]); ?>
