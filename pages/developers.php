<?php
/**
 * Developer Hub — summaries of each developer resource with a link to its
 * standalone page. Deliberately does not repeat the child pages in full.
 */
require_once __DIR__ . '/../includes/faq-data.php';
require_once __DIR__ . '/../includes/standalone-ui.php';
require_once __DIR__ . '/../includes/developer-docs.php';

$faqs = faq_set('developers');
$trail = [['Home', '/'], ['Developers', '/developers']];
$page_meta = sp_meta([
    'title'       => 'Developers | Paynancial API, SDKs, Webhooks & Sandbox',
    'description' => 'The Paynancial Developer Hub: API Reference, authentication, webhooks, SDKs for PHP, JavaScript and Python, an integration guide and a sandbox for testing.',
    'path'        => '/developers',
    'h1'          => 'Build payments into your product with the Paynancial API.',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$pages = dev_pages();
$resources = dev_resources();

sp_track_view('developer_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Developer Hub',
    'h1'        => 'Build payments into your product with the Paynancial API.',
    'lead'      => 'A REST API, webhooks and SDKs for PHP, JavaScript and Python — built to be called as reliably by an AI agent as by a person, from a single sandbox key to an enterprise integration.',
    'primary'   => ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    'secondary' => ['Read the API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => ['REST over HTTPS', 'Sandbox and live keys', 'Idempotent writes', 'Real-time webhooks'],
    'aside'     => sp_code(dev_resource_code($resources['payments']), 'Create a payment'),
]);
?>

<?php sp_band_open('resources'); ?>
  <?php sp_head('resources', 'Developer resources', 'Everything you need, one page each.', 'Each resource has its own page. Start with the Integration Guide if this is your first integration, or go straight to the API Reference.'); ?>
  <div class="sp-cards">
    <?php $n = 0; foreach ($pages as $slug => [$title, $desc, $href]): $n++; ?>
    <a class="sp-card reveal" href="<?= e($href) ?>" data-track="documentation_click">
      <span class="sp-card-num"><?= sprintf('%02d', $n) ?></span>
      <h3><?= e($title) ?></h3>
      <p><?= e($desc) ?></p>
      <span class="sp-card-link">Explore <span aria-hidden="true">→</span></span>
    </a>
    <?php endforeach; ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('pathways', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('pathways', 'Integration pathways', 'From first key to first live payment.', 'Most integrations follow the same four stages. Each links to the page that covers it in detail.'); ?>
    <?php sp_steps([
        ['Get a sandbox key', 'Request sandbox access and authenticate with your sandbox API key. See Authentication.'],
        ['Make your first call', 'Create a payment, payment link, payout or collection. See the API Reference.'],
        ['Listen for events', 'Receive webhooks for payments, payouts, refunds and settlements instead of polling. See Webhooks.'],
        ['Test, then go live', 'Test failures, retries and duplicates in the Sandbox, then switch to a live key. See the Integration Guide.'],
    ]); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('api-overview'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('api-overview', 'API overview', 'One REST API for money in and money out.'); ?>
      <div class="sp-prose reveal">
        <p>Every request goes to <code><?= e(DEV_API_BASE) ?></code> over HTTPS and is authenticated with an API key. The same API covers the full payment lifecycle:</p>
        <ul>
          <?php foreach ($resources as $r): ?><li><strong><?= e($r['name']) ?></strong> — <?= e($r['does']) ?></li><?php endforeach; ?>
        </ul>
        <p style="margin-top:22px;"><a class="card-link" href="/developers/api-reference" data-track="api_reference_click">Explore the API Reference →</a></p>
      </div>
    </div>
    <?= sp_code(dev_client_setup(), 'Set up a client') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('agent-ready', 'ink'); ?>
  <?php sp_head('agent-ready', 'Agent-ready API', 'Designed for the caller to be an agent, not just a person.', 'A growing share of calls to a payments API come from software acting on its own — retrying a failed charge, reconciling a ledger, releasing a payout inside limits a business set. Three properties make that safe.'); ?>
  <?php sp_answers([
      ['Safe to retry', 'Every write endpoint accepts an idempotency key, so a retried request returns the original result instead of creating a duplicate payment or payout.'],
      ['Machine-readable errors', 'Errors carry a stable code an agent can branch on — insufficient_funds, invalid_method, rate_limited — not only a human-readable message.'],
      ['Event-driven', 'Webhooks push payment, payout, refund and settlement events as they happen, so a workflow reacts to state changes instead of polling.'],
      ['Governed', 'Permissions, policy limits and human approval thresholds are set by the business. Read how on the AI Governance page.'],
  ]); ?>
  <p class="reveal" style="margin-top:32px;"><a class="card-link" href="/ai-governance" style="color:var(--teal-300);">Read about AI governance →</a></p>
<?php sp_band_close(); ?>

<?php sp_band_open('support', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('support', 'Technical support', 'Talk to someone who can read your logs.', 'Developer support is handled through the contact page. Send the request you made and the response you got — never your API key.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_cta('Ready to start integrating?', 'Request a sandbox key and build your integration with no real funds involved.', [
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
    ['Contact Developer Support', dev_support_url(), 'cta_click'],
]); ?>
