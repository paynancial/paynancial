<?php
/**
 * AI Payment Orchestration — /agentic-ai/payment-orchestration. Expanded
 * from the section of the same name on /agentic-ai, which now summarises
 * and links here. Technical facts come from includes/developer-docs.php.
 */
require_once __DIR__ . '/../../includes/faq-data.php';
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/developer-docs.php';

$faqs = faq_set('payment-orchestration');
$trail = [['Home', '/'], ['Agentic AI', '/agentic-ai'], ['AI Payment Orchestration', '/agentic-ai/payment-orchestration']];
$page_meta = sp_meta([
    'title'       => 'AI Payment Orchestration | Safe Rails for Agent Payments | Paynancial',
    'description' => 'How AI agents initiate, retry and track payments and payouts safely: the same rails as a person\'s click, with idempotency keys, structured error codes, webhooks, sandbox testing and business-set limits.',
    'path'        => '/agentic-ai/payment-orchestration',
    'h1'          => 'The same payment rails, built for callers that never stop.',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$resources = dev_resources();

sp_track_view('agentic_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Agentic AI · Payment Orchestration',
    'h1'        => 'The same payment rails, built for callers that never stop.',
    'lead'      => 'When an agent clears a vendor invoice under a pre-set limit, the payout travels through exactly the same infrastructure as a person\'s click. What changes is how often, and at what hour, requests arrive — and orchestration is what keeps that reliable.',
    'primary'   => ['Talk to a Specialist', '/contact?intent=sales&topic=agentic-ai', 'cta_click'],
    'secondary' => ['Test in the Sandbox', '/sandbox', 'cta_click'],
    'values'    => ['Idempotent writes', 'Structured errors', 'Real-time webhooks', 'Business-set limits'],
    'aside'     => sp_code(dev_resource_code($resources['payouts']), 'An agent-safe payout'),
]);
?>

<?php sp_band_open('definition'); ?>
  <div class="sp-split">
    <?php sp_head('definition', 'Definition', 'What payment orchestration means here.'); ?>
    <div class="sp-prose reveal">
      <p><strong>AI payment orchestration</strong> is the sequencing and safety rails that let software initiate, retry and track payments and payouts reliably — whether the caller is a developer's job, an AI operations assistant or a fleet of enterprise agents.</p>
      <p>What changes with agents isn't the payment rail. It is the <strong>frequency and origin</strong> of requests. An agent may check status, retry or re-verify far more often than a person would, on a schedule that doesn't stop at 6pm. Orchestration is what makes that pattern safe rather than risky.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('rails', 'dim'); ?>
  <?php sp_head('rails', 'The safety rails', 'Five rails every agent-initiated payment runs on.'); ?>
  <?php sp_table(['Rail', 'What it does', 'Why it matters for agents', 'Details'], [
      ['Idempotency keys', 'A retried request with the same key returns the original result.', 'Agents retry on timeout without asking a person first — a retry can never create a duplicate payment or payout.', '<a class="inline-link" href="/developers/api-reference#idempotency">API Reference</a>'],
      ['Structured error codes', 'Errors carry a stable code alongside a readable message.', 'An agent branches on <code>insufficient_funds</code>, <code>invalid_method</code> or <code>rate_limited</code> instead of guessing from text.', '<a class="inline-link" href="/developers/api-reference#errors">Errors</a>'],
      ['Webhooks', 'Events fire the moment a payment, payout, refund or settlement changes state.', 'The agent reacts to state changes instead of polling on a schedule.', '<a class="inline-link" href="/developers/webhooks">Webhooks</a>'],
      ['Rate limits', 'Limits on how many requests can be made in a period.', 'Sized for continuous traffic, not just business-hours bursts; an agent that hits one backs off and retries.', '<a class="inline-link" href="/sandbox#scenarios">Test scenarios</a>'],
      ['Keys and limits', 'Every request is tied to an API key; the business sets permissions, caps and approval thresholds.', 'An agent can only do what its key allows, up to limits a person set.', '<a class="inline-link" href="/ai-governance">AI Governance</a>'],
  ], 'Safety rails for agent-initiated payments'); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('sequence', 'ink'); ?>
  <?php sp_head('sequence', 'Orchestration in practice', 'A failed subscription payment, handled end to end.', 'Multi-step finance processes are where orchestration earns its keep. A typical retry-then-remind-then-cancel flow, with people in the loop where the business wants them:'); ?>
  <?php sp_steps([
      ['Payment fails', 'A webhook tells the agent a scheduled collection failed, with a reason code.'],
      ['Agent decides', 'It branches on the code: retry later, or stop because retrying cannot succeed.'],
      ['Safe retry', 'It retries on the schedule the business defined, with the same idempotency key.'],
      ['Customer reminded', 'If retries fail, the customer is notified to update how they pay.'],
      ['Person decides', 'Cancelling the subscription, or anything above a threshold, goes to a person.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('code'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('code', 'For developers', 'An agent calls the same API you do.', 'There is no separate agent API. The payout on the right is the same call a developer makes — the idempotency key is what makes it safe for an agent to retry.'); ?>
      <div class="sp-prose reveal">
        <ul>
          <li>Give each agent or automated job its own API key, so every action is traceable to its source.</li>
          <li>Use one idempotency key per intended action, such as a payout run ID.</li>
          <li>Handle every error code explicitly, and back off on <code>rate_limited</code>.</li>
        </ul>
        <p style="margin-top:16px;"><a class="inline-link" href="/developers">Visit the Developer Hub →</a></p>
      </div>
    </div>
    <?= sp_code(dev_resource_code($resources['payouts']), 'POST /payouts with an idempotency key') ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('testing', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('testing', 'Before going live', 'Test the agent, not just the integration.'); ?>
    <?php sp_answers([
        ['Retries', 'Does the agent reuse the same idempotency key when it retries, and stop after a sensible number of attempts?'],
        ['Errors', 'Does it handle each error code differently, and escalate the ones it cannot resolve?'],
        ['Rate limits', 'Does it back off when it receives rate_limited, instead of retrying immediately?'],
        ['Limits', 'Does it stay inside the permissions, caps and thresholds you set — and stop when it should?'],
    ]); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('faq'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'Payment orchestration questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related', 'dim'); ?>
  <?php sp_head('related', 'Related', 'Keep reading.'); ?>
  <?php sp_related([
      ['AI Financial Agents', 'Where agents work across financial operations, and what stays with people.', '/agentic-ai/financial-agents'],
      ['AI Governance', 'Permissions, policy limits, human oversight and auditability.', '/ai-governance'],
      ['Sandbox', 'Test an agent\'s retries, errors and limits with no real funds.', '/sandbox'],
      ['API Reference', 'Idempotency, errors and every published resource.', '/developers/api-reference'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Put an agent on safe payment rails.', 'Talk to a specialist about orchestrating agent-initiated payments and payouts within limits you control.', [
    ['Talk to a Specialist', '/contact?intent=sales&topic=agentic-ai', 'cta_click'],
    ['Request Sandbox Access', dev_sandbox_request_url(), 'request_sandbox_access'],
]); ?>
