<?php
/**
 * AI Financial Agents — /agentic-ai/financial-agents. Expanded from the
 * "AI Agents in Financial Operations" section of /agentic-ai, which now
 * summarises and links here. Capability names match the AI & Intelligence
 * products already listed in the header; no accuracy, savings or outcome
 * claims are made.
 */
require_once __DIR__ . '/../../includes/standalone-ui.php';

$ai = fn (string $slug) => '/contact?intent=sales&product=' . $slug;
$faqs = [
    ['What is an AI financial agent?', 'Software that monitors financial activity, understands the context of what it sees, and — within limits a business sets — takes the next step: flagging an anomaly, matching a settlement, routing an exception or retrying a failed payment. It differs from a report or a dashboard because it acts, and from fixed automation because it evaluates each situation rather than following one script.'],
    ['Do AI financial agents make decisions on their own?', 'Only inside the scope a business grants them. Every Paynancial AI capability surfaces a recommendation or takes a narrowly scoped action; anything above a set threshold, or matching a risk pattern, routes to a person before it completes.'],
    ['Which financial tasks are agents best suited to?', 'High-volume, repetitive work where most cases are routine and a few need judgement: payment monitoring, reconciliation, exception routing, fraud screening, cash-flow forecasting and answering routine payment questions.'],
    ['What stays with people?', 'Setting the limits, approving anything above them, handling genuine exceptions, and deciding when to widen or narrow what an agent may do. The agent reduces how much routine work reaches a person; it does not replace the person\'s authority.'],
    ['How do we start using AI agents in finance?', 'Start with one workflow where the agent only recommends — for example, surfacing reconciliation exceptions. Test it in the sandbox, set permissions and limits, and widen its scope as it earns trust.'],
];
$trail = [['Home', '/'], ['Agentic AI', '/agentic-ai'], ['AI Financial Agents', '/agentic-ai/financial-agents']];
$page_meta = sp_meta([
    'title'       => 'AI Financial Agents | Agentic AI for Finance Operations | Paynancial',
    'description' => 'How AI agents work in financial operations — payment monitoring, reconciliation, exception handling, fraud screening, cash-flow forecasting — and how they act only within limits a business sets.',
    'path'        => '/agentic-ai/financial-agents',
    'h1'          => 'AI agents that take on the routine work of finance.',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);

sp_track_view('agentic_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Agentic AI · Financial Agents',
    'h1'        => 'AI agents that take on the routine work of finance.',
    'lead'      => 'Monitoring payments, matching settlements, routing exceptions, screening for fraud and forecasting cash — handled continuously by agents acting inside limits your business sets, so your team spends its time on the exceptions.',
    'primary'   => ['Talk to a Specialist', '/contact?intent=sales&topic=agentic-ai', 'cta_click'],
    'secondary' => ['How agents are governed', '/ai-governance', 'cta_click'],
    'values'    => ['Detect', 'Analyse', 'Act within limits', 'Escalate to people'],
]);
?>

<?php sp_band_open('definition'); ?>
  <div class="sp-split">
    <?php sp_head('definition', 'Definition', 'What an AI financial agent is.'); ?>
    <div class="sp-prose reveal">
      <p>An <strong>AI financial agent</strong> is software that watches financial activity, understands the context of what it sees, and takes the next step — within limits a business defines. It sits between two things finance teams already know:</p>
      <ul>
        <li><strong>A dashboard or report</strong> shows you what happened. An agent also does something about it: flags it, routes it, matches it or retries it.</li>
        <li><strong>Fixed automation</strong> follows one script: if X, then always Y. An agent evaluates each case — is this failed payment worth retrying now, or does it need a person?</li>
      </ul>
      <p>The value is not that an agent is cleverer than a finance team. It is that an agent can look at every transaction, all the time, and send only the cases that need judgement to a person.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('capabilities', 'dim'); ?>
  <?php sp_head('capabilities', 'Where agents work', 'Eight places agents show up in financial operations.', 'Each is tied to a capability in the Paynancial catalog. For every one, the agent handles the routine cases and a person keeps the decision on the rest.'); ?>
  <?php sp_table(['Pattern', 'What the agent does', 'What stays with people', 'Related capability'], [
      ['Payment monitoring', 'Reviews transactions continuously rather than in a daily batch, and surfaces anomalies as they happen.', 'Deciding what an anomaly means and what to do about it.', '<a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>'],
      ['Reconciliation assistance', 'Matches settlements against transactions and surfaces only the genuine exceptions.', 'Resolving exceptions and closing the books.', '<a class="inline-link" href="' . e($ai('ai-reconciliation')) . '">AI Reconciliation</a>'],
      ['Exception handling', 'Routes a failed payment, disputed charge or mismatched invoice to the right next step instead of a shared queue.', 'Handling the cases that need judgement.', '<a class="inline-link" href="/products/payment-collection">Payment Collection</a>'],
      ['Transaction analysis', 'Evaluates transaction patterns for fraud risk as they happen, not after settlement.', 'Deciding on flagged transactions above your thresholds.', '<a class="inline-link" href="' . e($ai('ai-fraud-detection')) . '">AI Fraud Detection</a>'],
      ['Cash-flow intelligence', 'Forecasts near-term liquidity from live transaction data instead of a monthly spreadsheet.', 'Treasury and funding decisions.', '<a class="inline-link" href="' . e($ai('ai-cash-flow-intelligence')) . '">AI Cash-Flow Intelligence</a>'],
      ['Workflow orchestration', 'Sequences multi-step processes, such as retry, then remind, then cancel for a failed subscription payment.', 'Setting the sequence and its limits.', '<a class="inline-link" href="/agentic-ai/payment-orchestration">AI Payment Orchestration</a>'],
      ['Risk signals & reporting', 'Turns raw transaction volume into the specific numbers a finance lead needs.', 'Interpreting the numbers and acting on them.', '<a class="inline-link" href="' . e($ai('ai-revenue-forecasting')) . '">AI Revenue Forecasting</a>'],
      ['Support & operational alerts', 'Answers routine questions such as "why was this transaction declined?" without a support ticket.', 'Anything the assistant cannot answer from the data.', '<a class="inline-link" href="' . e($ai('ai-financial-assistant')) . '">AI Financial Assistant</a>'],
  ], 'Where AI agents work in financial operations'); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('path', 'ink'); ?>
  <?php sp_head('path', 'The path to agentic finance', 'Every business is somewhere on the same path.', 'From a two-person startup to an enterprise treasury desk, financial operations move through the same stages. Most businesses do not need to jump to the end.'); ?>
  <?php sp_steps([
      ['Manual', 'Payments and reconciliation done by hand, in spreadsheets and bank portals.'],
      ['Digital', 'Payments and records move online, but people still connect the pieces.'],
      ['Automated', 'Fixed rules handle predictable tasks: if X, then always Y.'],
      ['AI-assisted', 'Models analyse activity and recommend; people decide and act.'],
      ['Agentic', 'Agents take narrowly scoped actions inside limits the business sets, and escalate the rest.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('boundaries'); ?>
  <div class="sp-split">
    <?php sp_head('boundaries', 'The boundary', 'What agents do not do.'); ?>
    <div class="sp-prose reveal">
      <p>Every pattern on this page assists with detection, analysis or a narrowly scoped action. None of them removes the authorisation and audit controls a business puts in place.</p>
      <ul>
        <li>An agent can only take actions explicitly granted to its role or API key. Nothing is enabled by default.</li>
        <li>Spending caps, beneficiary allow-lists and approval thresholds are set by the business, and can be tightened at any time.</li>
        <li>Actions above a threshold, or matching a risk pattern, route to a person before they complete.</li>
        <li>Every action is logged against the request, key and rule that authorised it.</li>
      </ul>
      <p><a class="inline-link" href="/ai-governance">Read the full AI Governance model →</a></p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('by-size', 'dim'); ?>
  <?php sp_head('by-size', 'By business size', 'What an agent looks like at your scale.'); ?>
  <?php sp_answers([
      ['Small business', 'An AI bookkeeping assistant reconciles the day\'s transactions and flags what looks wrong — technology that used to need a finance team, without enterprise complexity.'],
      ['Growing business', 'An AI operations assistant routes vendor payouts and flags anomalies; the owner or finance lead spends their time on the exceptions it surfaces.'],
      ['Mid-market and platforms', 'Agent-driven billing and reconciliation run across every customer on the platform, not one account at a time.'],
      ['Enterprise', 'Standing agents initiate payouts, run reconciliation and monitor cash flow continuously against policy limits, with fraud screening and audit trails underneath.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('start'); ?>
  <div class="sp-split">
    <?php sp_head('start', 'Getting started', 'Start narrow, widen as trust is earned.'); ?>
    <?php sp_steps([
        ['Pick one workflow', 'Choose a high-volume, low-risk task — reconciliation exceptions are a common first step.'],
        ['Recommend before acting', 'Let the agent recommend while people decide, so you can see how it behaves.'],
        ['Test in the sandbox', 'Test retries, errors and edge cases with no real funds involved.'],
        ['Set limits', 'Grant only the permissions the task needs, with caps and approval thresholds.'],
        ['Widen the scope', 'Extend what the agent may do as it earns trust — and narrow it instantly if something looks wrong.'],
    ]); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'AI financial agent questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Related', 'Keep reading.'); ?>
  <?php sp_related([
      ['AI Payment Orchestration', 'How agent-initiated payments travel through the same safe rails as a person\'s click.', '/agentic-ai/payment-orchestration'],
      ['AI Governance', 'Permissions, policy limits, human oversight and auditability.', '/ai-governance'],
      ['Agentic AI in Finance', 'What agentic AI is, and how it differs from generative AI and automation.', '/agentic-ai'],
      ['Sandbox', 'Test an agent\'s behaviour with no real funds involved.', '/sandbox'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Find the first workflow worth handing to an agent.', 'Talk to a specialist about where agents fit in your financial operations — and the limits they should work within.', [
    ['Talk to a Specialist', '/contact?intent=sales&topic=agentic-ai', 'cta_click'],
    ['Read AI Governance', '/ai-governance', 'cta_click'],
]); ?>
