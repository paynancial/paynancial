<?php
/**
 * AI Governance — /ai-governance. The single destination for how
 * Paynancial's AI and agent-initiated actions are governed; the Agentic AI
 * page and the Trust Center summarise and link here.
 */
require_once __DIR__ . '/../includes/standalone-ui.php';

$faqs = [
    ['Does Paynancial\'s AI make financial decisions on its own?', 'No. Every AI capability Paynancial offers surfaces a recommendation or takes a narrowly scoped action inside limits a business sets. A person, or a policy a person configured, is always the authority.'],
    ['Can an AI agent move money without anyone approving it?', 'Only within limits a business explicitly configures — such as a payout ceiling, an approval workflow or a list of pre-authorised beneficiaries. Anything above a threshold, or matching a risk pattern, routes to a person before it completes.'],
    ['Who sets the limits?', 'The business using the platform, not Paynancial. It decides how much of a workflow an agent handles unattended, and can tighten or loosen that at any time.'],
    ['How can I tell what an agent did and why?', 'Every write action is tied to the specific API key or session that made the request, and webhooks give a real-time, timestamped record of every state change — the data an audit trail draws from.'],
    ['Is there a formal AI governance policy document?', 'Not yet. The principles on this page govern how Paynancial\'s AI & Intelligence products are designed to operate. A standalone, formally reviewed AI governance policy document has not been published.'],
];
$trail = [['Home', '/'], ['Trust Center', '/trust'], ['AI Governance', '/ai-governance']];
$page_meta = sp_meta([
    'title'       => 'AI Governance | Human Oversight for AI & Agents | Paynancial',
    'description' => 'How Paynancial governs AI and agent-initiated financial actions: permissions, policy limits, human oversight, authentication and a full audit trail — set by the business, not by the AI.',
    'path'        => '/ai-governance',
    'h1'          => 'AI that acts only inside the limits you set.',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);

sp_track_view('ai_governance_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Trust · AI Governance',
    'h1'        => 'AI that acts only inside the limits you set.',
    'lead'      => 'Letting any system — human or AI — initiate a financial transaction has real consequences if the controls around it are weak. Paynancial\'s AI is governed by five controls, and every one of them is set by your business.',
    'primary'   => ['Visit the Trust Center', '/trust', 'cta_click'],
    'secondary' => ['Explore Agentic AI', '/agentic-ai', 'cta_click'],
    'values'    => ['Permissions', 'Policy limits', 'Human oversight', 'Authentication', 'Auditability'],
]);
?>

<?php sp_band_open('principle'); ?>
  <div class="sp-split">
    <?php sp_head('principle', 'The principle', 'Not autonomous AI you have to trust blindly.'); ?>
    <div class="sp-prose reveal">
      <p>Paynancial's approach is closer to giving a new employee a defined scope of authority than to handing over the keys. An agent can do what it has been explicitly allowed to do, up to limits the business has set — and that scope can be widened as trust is earned and narrowed instantly if something looks wrong.</p>
      <p>No AI capability Paynancial offers is positioned as removing a business's ability to require human approval. A person, or a policy a person configured, is always the authority.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('controls', 'ink'); ?>
  <?php sp_head('controls', 'The five controls', 'How every AI and agent action is governed.'); ?>
  <?php sp_steps([
      ['Permissions', 'An agent — or a person — can only take actions explicitly granted to its role or API key. Nothing is enabled by default.'],
      ['Policy limits', 'Spending caps, beneficiary allow-lists and approval thresholds are configured by the business and can be changed at any time.'],
      ['Human oversight', 'Actions above a set threshold, or matching a risk pattern, route to a person before completing — not after.'],
      ['Authentication & authorisation', 'Every request is tied to a specific API key or user session, never an anonymous or implicit actor.'],
      ['Auditability', 'Every action is logged against the specific request, key and rule that authorised it, so "why did this happen" always has a traceable answer.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('oversight'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('oversight', 'Human oversight', 'Your business decides how much runs unattended.'); ?>
      <div class="sp-prose reveal">
        <p>Spending limits, beneficiary allow-lists and approval thresholds are configured by the business using the platform — not fixed by Paynancial. A business decides how much of a workflow an agent handles on its own, and can tighten or loosen that at any time.</p>
      </div>
    </div>
    <div>
      <?php sp_head('auditability', 'Auditability', 'Every action has a traceable answer.'); ?>
      <div class="sp-prose reveal" id="auditability">
        <p>Every write action against the API — a payment, a payout, a refund — is tied to the API key or session that made the request. Idempotency keys mean a retried request is recognised rather than treated as a new action, and webhooks provide a real-time, timestamped record of every state change: the same data an audit trail draws from.</p>
      </div>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('scope', 'dim'); ?>
  <?php sp_head('scope', 'Scope', 'What this governs today — and what is not yet published.'); ?>
  <?php sp_answers([
      ['Confirmed today', 'These principles govern how Paynancial\'s AI & Intelligence products — AI Fraud Detection, AI Reconciliation, AI Financial Assistant, AI Cash-Flow Intelligence and AI Revenue Forecasting — are designed to operate.'],
      ['Not yet published', 'A standalone, formally reviewed AI governance policy document, distinct from the product-level description on this page.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('faq'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'AI governance questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related', 'dim'); ?>
  <?php sp_head('related', 'Related', 'Where to read next.'); ?>
  <?php sp_related([
      ['Trust Center', 'Security foundations, privacy, continuity and how Paynancial labels what is confirmed.', '/trust'],
      ['Agentic AI', 'What agentic AI is, and how financial agents and payment orchestration work.', '/agentic-ai'],
      ['Security & Compliance', 'Company and entity details and Paynancial\'s security approach.', '/security'],
      ['Developer Hub', 'The API features that make agent-initiated actions safe: idempotency, errors and webhooks.', '/developers'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Questions about how AI is governed at Paynancial?', 'Talk to our team about permissions, limits and oversight for your workflows.', [
    ['Contact Paynancial', '/contact?intent=sales', 'cta_click'],
    ['Visit the Trust Center', '/trust', 'cta_click'],
]); ?>
