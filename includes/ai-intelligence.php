<?php
/**
 * AI & Intelligence — the /ai-intelligence hub and its child pages
 * (/ai-intelligence/{slug}).
 *
 * Content rule: each capability is described only by what the site
 * already publishes (the one-line capability descriptions on the Agentic
 * AI pages, the AI Governance model and the Trust Center's verified
 * security facts). No accuracy, detection rates, savings, model names,
 * certifications or customer claims. Availability of every AI capability
 * is "on request" until the business confirms it (ai_confirmed()); the
 * child pages are noindex until approved through content-governance.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/faq-data.php';

/** Set to true per capability once the business confirms it is available. */
function ai_confirmed(): array
{
    return [
        'paynancial-ai' => false, 'fraud-detection' => false, 'reconciliation' => false,
        'financial-assistant' => false, 'cash-flow-intelligence' => false, 'revenue-forecasting' => false,
    ];
}

/**
 * Child pages are governed by the publishing gate (content-governance.php):
 * live and linked, but noindex and out of the sitemap until the capability
 * is confirmed and approved. ai_confirmed() records business confirmation.
 */
function ai_child_indexable(string $slug): bool
{
    require_once __DIR__ . '/content-governance.php';
    return gov_indexable(ai_url($slug));
}

function ai_url(string $slug = ''): string
{
    return '/ai-intelligence' . ($slug !== '' ? '/' . $slug : '');
}

/** Hub card details per capability: [button label, what stays with people]. */
function ai_hub_meta(): array
{
    return [
        'paynancial-ai'          => ['Explore Paynancial AI', 'Setting permissions, limits and approval thresholds.'],
        'fraud-detection'        => ['Explore Fraud Detection', 'Deciding on flagged transactions above your thresholds.'],
        'reconciliation'         => ['Explore AI Reconciliation', 'Resolving exceptions and closing the books.'],
        'financial-assistant'    => ['Explore Financial Assistant', 'Anything the assistant cannot answer from the data.'],
        'cash-flow-intelligence' => ['Explore Cash-Flow Intelligence', 'Treasury and funding decisions.'],
        'revenue-forecasting'    => ['Explore Revenue Forecasting', 'Interpreting the numbers and acting on them.'],
    ];
}

/** Child pages, rendered by pages/products/capability.php. */
function ai_children(): array
{
    $pages = [
        // ---- AI & Intelligence ------------------------------------------
        // Each capability states only its published one-line description and
        // the published governance model. Availability is on request.
        'paynancial-ai' => [
            'name'  => 'Paynancial AI',
            'family' => 'ai',
            'title' => 'Paynancial AI | Governed AI for Payments & Finance | Paynancial',
            'description' => 'Paynancial AI is the family of AI capabilities that work on your payments data — fraud screening, reconciliation, a financial assistant, cash-flow and revenue forecasting — each acting only within limits your business sets.',
            'h1'    => 'AI for your payments — governed by your rules.',
            'lead'  => 'Paynancial AI brings fraud screening, exception-first reconciliation, forecasting and answers to routine payment questions to your payments data — and every capability works inside limits your business sets.',
            'answer' => 'Paynancial AI is the name for Paynancial\'s AI & Intelligence capabilities: AI Fraud Detection, AI Reconciliation, AI Financial Assistant, AI Cash-Flow Intelligence and AI Revenue Forecasting. Each one surfaces a recommendation or takes a narrowly scoped action within limits a business sets — a person, or a policy a person configured, is always the authority.',
            'values' => ['Five capabilities', 'Your payments data', 'Business-set limits', 'Human oversight'],
            'what' => [
                'Payments data says a lot about a business: which transactions look unusual, which settlements do not match, how much cash will be there next week, why a customer\'s payment was declined. Paynancial AI turns that data into signals and narrowly scoped actions a finance team can use.',
                'It is deliberately not "autonomous AI you have to trust blindly". Every capability is governed by the same five controls — permissions, policy limits, human oversight, authentication and auditability — and none of them removes a business\'s ability to require human approval.',
            ],
            'steps' => [
                ['Payments data', 'Transactions, settlements and refunds are recorded as they happen.'],
                ['Analysis', 'Each capability analyses the part of that data it is built for.'],
                ['Recommendation or action', 'It surfaces a recommendation, or takes a narrowly scoped action within your limits.'],
                ['People decide', 'Anything above a threshold, or matching a risk pattern, goes to a person first.'],
                ['Logged', 'Every action is logged against the request, key and rule that authorised it.'],
            ],
            'capabilities' => [
                ['AI Fraud Detection', 'Evaluates transaction patterns for fraud risk as they happen, rather than after settlement.'],
                ['AI Reconciliation', 'Matches settlements against transactions and surfaces only the genuine exceptions.'],
                ['AI Financial Assistant', 'Answers routine questions such as "why was this transaction declined?" without a support ticket.'],
                ['AI Cash-Flow Intelligence', 'Forecasts near-term liquidity from live transaction data instead of a monthly spreadsheet.'],
                ['AI Revenue Forecasting', 'Turns raw transaction volume into the forward-looking numbers a finance lead needs.'],
                ['One governance model', 'Permissions, policy limits, human oversight, authentication and auditability apply to all of them.'],
            ],
            'surfaces' => [
                ['AI Fraud Detection', '<a class="inline-link" href="/ai-intelligence/fraud-detection">Screening payments for fraud risk</a>'],
                ['AI Reconciliation', '<a class="inline-link" href="/ai-intelligence/reconciliation">Exception-first reconciliation</a>'],
                ['AI Financial Assistant', '<a class="inline-link" href="/ai-intelligence/financial-assistant">Answers to routine payment questions</a>'],
                ['AI Cash-Flow Intelligence', '<a class="inline-link" href="/ai-intelligence/cash-flow-intelligence">Near-term liquidity forecasts</a>'],
                ['AI Revenue Forecasting', '<a class="inline-link" href="/ai-intelligence/revenue-forecasting">Forward-looking revenue numbers</a>'],
            ],
            'surfaces_head' => ['Capability', 'What it is for'],
            'practices' => [
                ['Start with a recommendation, not an action', 'Let a capability recommend while people decide, and widen its scope as it earns trust.'],
                ['Set limits before anything acts', 'Define permissions, caps and approval thresholds first.'],
                ['Review the audit trail', 'Every action is logged against the rule that authorised it — use it.'],
            ],
            'india' => [
                'title' => 'AI for how India pays.',
                'items' => [
                    ['High volumes, small tickets', 'UPI has made high-volume, low-value digital payments normal in India. Reviewing each one by hand does not scale — AI helps surface the few that need a person\'s attention.'],
                    ['Personal data', 'Payment records carry personal data governed by the Digital Personal Data Protection Act, 2023, so AI that works on them has to respect consent and purpose limits.'],
                    ['Accountability stays with people', 'Every Paynancial AI capability works within permissions and limits a business sets, with a person — or a policy a person configured — as the authority.'],
                    ['Available on request', 'These capabilities are available to discuss with our team, so you can confirm what fits your business before you plan around them.'],
                ],
            ],
            'related' => ['ai:hub', 'ai:fraud-detection', 'ai:reconciliation', 'ai:cash-flow-intelligence'],
        ],
        'fraud-detection' => [
            'name'  => 'AI Fraud Detection',
            'family' => 'ai',
            'title' => 'AI Fraud Detection | Real-Time Fraud Risk Screening | Paynancial',
            'description' => 'Paynancial AI Fraud Detection evaluates transaction patterns for fraud risk as payments happen, not after settlement — and routes flagged transactions above your thresholds to a person.',
            'h1'    => 'Spot fraud risk while the payment is happening.',
            'lead'  => 'AI Fraud Detection evaluates transaction patterns for fraud risk in real time, rather than after settlement — and anything above the thresholds you set goes to a person before it completes.',
            'answer' => 'Paynancial AI Fraud Detection evaluates transaction patterns for fraud risk as payments happen rather than after settlement. It surfaces risk for people to act on: transactions above the thresholds your business sets, or matching a risk pattern, route to a person before they complete.',
            'values' => ['Real-time evaluation', 'Your thresholds', 'People decide', 'Logged'],
            'what' => [
                'Fraud detection is the work of spotting payments that are likely to be fraudulent — a stolen card, an account takeover, an unusual pattern — so they can be stopped or reviewed. The earlier that happens, the less it costs: a fraudulent payment caught after settlement has already moved money.',
                'AI Fraud Detection evaluates patterns for fraud risk in real time, rather than after settlement. It does not remove your controls: it surfaces risk, and the decision on anything above your thresholds stays with a person.',
                'This builds on a verified part of Paynancial\'s security foundations: real-time fraud monitoring, with transactions passing through real-time risk scoring before funds move (see the Trust Center).',
            ],
            'steps' => [
                ['Payment arrives', 'A customer pays through your checkout.'],
                ['Pattern evaluated', 'The transaction is evaluated for fraud risk as it happens.'],
                ['Risk surfaced', 'Transactions matching a risk pattern are flagged.'],
                ['Person decides', 'Flagged transactions above your thresholds go to a person before they complete.'],
                ['Logged', 'The decision is recorded against the rule that triggered it.'],
            ],
            'capabilities' => [
                ['Real-time risk scoring', 'Transactions pass through real-time risk scoring before funds move — a verified part of Paynancial\'s security foundations.'],
                ['Real-time evaluation', 'Transaction patterns are evaluated for fraud risk as they happen, not after settlement.'],
                ['Business-set thresholds', 'Your business decides what is flagged for review and what is not.'],
                ['Human decisions', 'Flagged transactions above your thresholds route to a person before completing.'],
                ['Auditability', 'Every action is logged against the request, key and rule that authorised it.'],
                ['Part of the payments flow', 'Works on the payments you already take through Paynancial.'],
                ],
            'surfaces' => [
                ['Payment Gateway', 'The payments being evaluated. See <a class="inline-link" href="/products/payment-gateway">Payment Gateway</a>.'],
                ['AI Governance', 'How thresholds, oversight and audit work. See <a class="inline-link" href="/ai-governance">AI Governance</a>.'],
                ['Trust Center', 'Real-time fraud monitoring, listed as verified. See <a class="inline-link" href="/trust">Trust Center</a>.'],
                ['Payment Analytics', 'Transactions by method, status and period. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'practices' => [
                ['Decide your thresholds first', 'Agree what should always go to a person before switching anything on.'],
                ['Keep a person on the flagged cases', 'The value is in reviewing the few that matter, quickly.'],
                ['Review decisions regularly', 'Use the audit trail to tune thresholds over time.'],
            ],
            'india' => [
                'title' => 'Payment fraud in India.',
                'items' => [
                    ['Report cyber fraud fast', 'Customers in India can report cyber fraud on the national cybercrime helpline 1930 or at cybercrime.gov.in. Speed matters for recovering funds.'],
                    ['A UPI PIN is only for paying', 'You never need to enter a UPI PIN to receive money. Fake requests that ask for one are a common scam.'],
                    ['Social engineering', 'Phishing links, fake customer-care numbers and impersonation are frequent routes to fraud — pattern-based screening looks at behaviour, not just amounts.'],
                    ['People decide', 'Transactions above the thresholds your business sets, or matching a risk pattern, go to a person before they complete.'],
                ],
            ],
            'related' => ['ai:paynancial-ai', 'ai:reconciliation', 'product:payment-gateway', 'ai:hub'],
        ],
        'reconciliation' => [
            'name'  => 'AI Reconciliation',
            'family' => 'ai',
            'title' => 'AI Reconciliation | Exception-First Payment Reconciliation | Paynancial',
            'description' => 'Paynancial AI Reconciliation matches settlements against transactions automatically and surfaces only the genuine exceptions for your finance team to resolve.',
            'h1'    => 'Reconciliation that only shows you what doesn\'t match.',
            'lead'  => 'AI Reconciliation matches settlements against transactions automatically and surfaces only the genuine exceptions — so your team spends its time resolving problems, not ticking off matches.',
            'answer' => 'Paynancial AI Reconciliation matches settlements against transactions automatically and surfaces only the genuine exceptions for a person to resolve. It builds on the settlement records, refunds and reconciliation views already in Paynancial, and resolving exceptions and closing the books stay with your team.',
            'values' => ['Automatic matching', 'Exceptions only', 'People resolve', 'Logged'],
            'what' => [
                'Most reconciliation work is confirming things that already match. The value is in the handful that do not — a short settlement, a refund against the wrong payment, a duplicate. AI Reconciliation is built around that: match automatically, and surface only the genuine exceptions.',
                'It works on the same foundation as Paynancial\'s reconciliation today — every transaction tied to a settlement record, every refund issued against its payment — and leaves resolving exceptions and closing the books with your finance team.',
            ],
            'steps' => [
                ['Records arrive', 'Transactions, settlements and refunds are recorded as they happen.'],
                ['Automatic matching', 'Settlements are matched against the transactions they cover.'],
                ['Exceptions surfaced', 'Only the genuine exceptions are surfaced.'],
                ['People resolve', 'Your team resolves each exception.'],
                ['Books closed', 'Reconciled results feed your reports.'],
            ],
            'capabilities' => [
                ['Automatic matching', 'Settlements matched against transactions without manual ticking.'],
                ['Exception-first', 'Only the genuine exceptions reach a person.'],
                ['Built on settlement records', 'Every transaction is already tied to a settlement record.'],
                ['Refund-aware', 'Refunds are issued against the original payment, so they are traceable.'],
                ['People keep the decision', 'Resolving exceptions and closing the books stay with your team.'],
                ['Auditability', 'Every action is logged against the rule that authorised it.'],
            ],
            'surfaces' => [
                ['Reconciliation', 'Payments, refunds and settlements matched in one place. See <a class="inline-link" href="/products/reconciliation">Reconciliation</a>.'],
                ['Settlements', 'What has settled and what is pending. See <a class="inline-link" href="/products/settlements">Settlements</a>.'],
                ['AI Governance', 'How oversight and audit work. See <a class="inline-link" href="/ai-governance">AI Governance</a>.'],
            ],
            'practices' => [
                ['Use a reference on every payment', 'Order, invoice or booking IDs make matching and exceptions easy to read.'],
                ['Resolve exceptions daily', 'Small, recent exceptions are quicker to resolve than a month\'s backlog.'],
                ['Keep the audit trail', 'Every match and exception is traceable to the rule behind it.'],
            ],
            'india' => [
                'title' => 'Reconciliation for businesses in India.',
                'items' => [
                    ['Many small transactions', 'With UPI, a day\'s takings can be thousands of small payments. Exception-first matching lets your team look only at what does not match.'],
                    ['Failed transaction reversals', 'Failed digital transactions have RBI turnaround times for reversal, so spotting an unmatched debit early matters for your customers.'],
                    ['Books your CA can use', 'Matched payment, settlement and refund records give your accountant or CA a clean starting point at month-end.'],
                    ['Amounts to the paisa', 'The API works in paise for INR, so matching happens on exact amounts.'],
                ],
            ],
            'related' => ['reconciliation', 'settlements', 'ai:paynancial-ai', 'category:financial-operations'],
        ],
        'financial-assistant' => [
            'name'  => 'AI Financial Assistant',
            'family' => 'ai',
            'title' => 'AI Financial Assistant | Answers to Payment Questions | Paynancial',
            'description' => 'Paynancial AI Financial Assistant answers routine payment questions — such as "why was this transaction declined?" — without a support ticket, and raises operational alerts.',
            'h1'    => 'Ask why a payment was declined — and get an answer.',
            'lead'  => 'The AI Financial Assistant answers routine questions such as "why was this transaction declined?" without a support ticket, so your team gets answers in the moment instead of waiting on a queue.',
            'answer' => 'Paynancial AI Financial Assistant answers routine payment questions — for example "why was this transaction declined?" — without a support ticket, and supports operational alerts. Anything it cannot answer goes to a person.',
            'values' => ['Routine questions answered', 'No ticket needed', 'Operational alerts', 'People for the rest'],
            'what' => [
                'Finance and support teams spend a lot of time on the same questions: why a payment failed, whether a refund went through, what happened to a payout. The answers usually sit in the payment records — finding them is the slow part.',
                'The AI Financial Assistant answers routine questions like "why was this transaction declined?" without a support ticket, and supports operational alerts. Questions it cannot answer from the data go to a person.',
            ],
            'steps' => [
                ['You ask', 'A team member asks a routine question about a payment.'],
                ['The assistant looks it up', 'It answers from the payment records.'],
                ['You get an answer', 'No support ticket, no queue.'],
                ['Alerts', 'Operational alerts surface issues as they happen.'],
                ['People for the rest', 'Anything it cannot answer goes to a person.'],
            ],
            'capabilities' => [
                ['Routine questions', 'Answers questions such as "why was this transaction declined?"'],
                ['No support ticket', 'Answers arrive in the moment rather than through a queue.'],
                ['Operational alerts', 'Supports alerts on payment operations.'],
                ['Escalation to people', 'Questions it cannot answer from the data go to a person.'],
                ['Clear status codes underneath', 'Failed payments carry clear status codes, which is what makes the answers possible.'],
                ['Governed like every AI capability', 'Permissions, limits and oversight set by your business.'],
            ],
            'surfaces' => [
                ['Payment Gateway', 'Clear status codes for failed payments. See <a class="inline-link" href="/products/payment-gateway">Payment Gateway</a>.'],
                ['Support Center', 'For anything the assistant cannot answer. See <a class="inline-link" href="/support">Support Center</a>.'],
                ['AI Governance', 'How the assistant is governed. See <a class="inline-link" href="/ai-governance">AI Governance</a>.'],
            ],
            'practices' => [
                ['Start with the questions you get most', 'Declines, refunds and payout status are the usual first ones.'],
                ['Keep a route to a person', 'Make sure unanswered questions reach someone quickly.'],
                ['Never share API keys in questions', 'The assistant never needs your keys.'],
            ],
            'india' => [
                'title' => 'Payment questions in India.',
                'items' => [
                    ['UPI complaints in the app', 'For a UPI payment debited but not credited, a customer can raise a complaint from within their UPI app through NPCI\'s dispute mechanism.'],
                    ['The RBI Ombudsman', 'If a complaint to an RBI-regulated entity is not resolved satisfactorily, the customer can escalate it to the RBI Ombudsman at cms.rbi.org.in.'],
                    ['Not advice', 'The assistant answers routine payment questions. It does not give legal, accounting or investment advice.'],
                    ['Answers without a ticket', 'Questions like "why was this transaction declined?" are answered from your payments data, without a support ticket.'],
                ],
            ],
            'related' => ['ai:paynancial-ai', 'ai:fraud-detection', 'product:payment-gateway', 'ai:hub'],
        ],
        'cash-flow-intelligence' => [
            'name'  => 'AI Cash-Flow Intelligence',
            'family' => 'ai',
            'title' => 'AI Cash-Flow Intelligence | Near-Term Liquidity Forecasts | Paynancial',
            'description' => 'Paynancial AI Cash-Flow Intelligence forecasts near-term liquidity from live transaction data instead of a monthly spreadsheet, while treasury and funding decisions stay with your team.',
            'h1'    => 'Know your cash position before the month ends.',
            'lead'  => 'AI Cash-Flow Intelligence forecasts near-term liquidity from live transaction data instead of a monthly spreadsheet — so decisions about cash are made on today\'s numbers.',
            'answer' => 'Paynancial AI Cash-Flow Intelligence forecasts near-term liquidity from live transaction data rather than a monthly spreadsheet. It informs treasury and funding decisions; the decisions themselves stay with your team.',
            'values' => ['Live transaction data', 'Near-term forecasts', 'No monthly spreadsheet', 'People decide'],
            'what' => [
                'Cash-flow forecasting estimates how much cash a business will have over the coming days and weeks. Done in a monthly spreadsheet, it is out of date by the time it is finished — and a surprise shortfall is found too late.',
                'AI Cash-Flow Intelligence forecasts near-term liquidity from live transaction data: the payments coming in, the payouts going out and the settlements in between. It informs decisions; treasury and funding decisions stay with people.',
            ],
            'steps' => [
                ['Live data', 'Payments, payouts and settlements are recorded as they happen.'],
                ['Forecast', 'Near-term liquidity is forecast from that live data.'],
                ['Surfaced', 'The forecast is surfaced to your finance team.'],
                ['People decide', 'Treasury and funding decisions stay with your team.'],
                ['Updated', 'The forecast moves as new transactions arrive.'],
            ],
            'capabilities' => [
                ['Live transaction data', 'Forecasts from what is actually happening, not last month\'s export.'],
                ['Near-term focus', 'Built for the coming days and weeks, where decisions are made.'],
                ['Settlement-aware', 'Settlements show what has settled and what is still pending.'],
                ['Payout-aware', 'Payouts are tracked from initiated to completed.'],
                ['People keep the decision', 'Treasury and funding decisions stay with your team.'],
                ['Governed like every AI capability', 'Permissions, limits and oversight set by your business.'],
            ],
            'surfaces' => [
                ['Settlements', 'What has settled, what is pending and when it is due. See <a class="inline-link" href="/products/settlements">Settlements</a>.'],
                ['Payouts', 'Money going out, tracked to completion. See <a class="inline-link" href="/products/payouts">Payouts</a>.'],
                ['Payment Analytics', 'Dashboards and reports. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'practices' => [
                ['Use it for decisions, not just reports', 'The value is acting early on a shortfall or surplus.'],
                ['Keep payouts and collections on Paynancial', 'The more of your money movement it sees, the more complete the picture.'],
                ['Keep people on funding decisions', 'A forecast informs; your team decides.'],
            ],
            'india' => [
                'title' => 'Cash planning for businesses in India.',
                'items' => [
                    ['Settlements set the timing', 'Cash you can use depends on when settlements arrive, which is why forecasts start from settlement records, not just sales.'],
                    ['Plan for GST and TDS', 'Tax payments such as GST and TDS are fixed outflows. A forecast built on payment data does not see them, so add them to your own plan.'],
                    ['Festive peaks', 'Many Indian businesses see seasonal peaks around festivals; live transaction data shows the build-up as it happens, not a month later.'],
                    ['Decisions stay with people', 'Forecasts inform treasury decisions; they do not make them.'],
                ],
            ],
            'related' => ['ai:revenue-forecasting', 'settlements', 'ai:paynancial-ai', 'ai:hub'],
        ],
        'revenue-forecasting' => [
            'name'  => 'AI Revenue Forecasting',
            'family' => 'ai',
            'title' => 'AI Revenue Forecasting | Revenue Signals from Payments Data | Paynancial',
            'description' => 'Paynancial AI Revenue Forecasting turns raw transaction volume into the specific, forward-looking numbers a finance lead needs, alongside Payment Analytics reporting.',
            'h1'    => 'Turn transaction volume into the numbers you plan with.',
            'lead'  => 'AI Revenue Forecasting turns raw transaction volume into the specific numbers a finance lead actually needs — forward-looking, from your own payments data.',
            'answer' => 'Paynancial AI Revenue Forecasting turns raw transaction volume into the specific, forward-looking revenue numbers a finance lead needs, working alongside the reporting in Payment Analytics. Interpreting the numbers and acting on them stays with people.',
            'values' => ['From transaction volume', 'Forward-looking', 'With Payment Analytics', 'People interpret'],
            'what' => [
                'Transaction data is detailed but noisy: thousands of payments, refunds and settlements. Revenue forecasting turns that volume into a few forward-looking numbers a finance lead can plan with.',
                'AI Revenue Forecasting works on your Paynancial transaction data, alongside the dashboards and reports in Payment Analytics. It produces the numbers; interpreting them and acting on them stays with people.',
            ],
            'steps' => [
                ['Transactions recorded', 'Every payment, refund and settlement is recorded as it happens.'],
                ['Signals extracted', 'Raw volume is turned into the numbers that matter.'],
                ['Forecast', 'Forward-looking revenue numbers are produced.'],
                ['Reported', 'Numbers sit alongside Payment Analytics reporting.'],
                ['People interpret', 'Your team decides what the numbers mean for the plan.'],
            ],
            'capabilities' => [
                ['From your transaction data', 'Built on the payments, refunds and settlements already in Paynancial.'],
                ['Forward-looking numbers', 'Specific numbers a finance lead needs, not raw volume.'],
                ['Alongside Payment Analytics', 'Next to the dashboards and exportable reports you already use.'],
                ['Refund-aware', 'Refunds are tracked, so revenue is not overstated by returns.'],
                ['People interpret', 'Interpreting the numbers and acting on them stays with your team.'],
                ['Governed like every AI capability', 'Permissions, limits and oversight set by your business.'],
            ],
            'surfaces' => [
                ['Payment Analytics', 'Dashboards, exports and scheduled reports. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
                ['Refunds', 'Tracked to completion. See <a class="inline-link" href="/products/refunds">Refunds</a>.'],
                ['AI Governance', 'How AI capabilities are governed. See <a class="inline-link" href="/ai-governance">AI Governance</a>.'],
            ],
            'practices' => [
                ['Agree the numbers you plan with', 'Decide which revenue figures matter to your plan first.'],
                ['Combine with cash-flow', 'Revenue and liquidity together give the fuller picture.'],
                ['Keep people on the plan', 'A forecast informs the plan; people own it.'],
            ],
            'india' => [
                'title' => 'Revenue planning in India.',
                'items' => [
                    ['April to March', 'India\'s financial year runs from April to March. Forecasts are most useful when they line up with it.'],
                    ['Advance tax', 'Businesses in India pay advance tax in instalments during the year based on estimated income. A revenue forecast is one input your CA can use.'],
                    ['Festive and seasonal cycles', 'Seasonal peaks around festivals and sales events show up in transaction volume first.'],
                    ['A forecast, not a promise', 'Forecasts are estimates from past and current transaction data. They are not guarantees of future revenue.'],
                ],
            ],
            'related' => ['ai:cash-flow-intelligence', 'ai:paynancial-ai', 'product:payment-analytics', 'ai:hub'],
        ],
    ];
    foreach ($pages as $slug => &$p) {
        $p['path']      = ai_url($slug);
        $p['parent']    = ['AI & Intelligence', ai_url()];
        $p['faq_key']   = 'ai:' . $slug;
        $p['secondary'] = ['How AI is governed', '/ai-governance', 'cta_click'];
        $p['availability'] = $p['name'] . ' is available on request. Ask our team whether it is available for your business.';
        if (!ai_child_indexable($slug)) {
            $p['robots'] = 'noindex, follow';
        }
    }
    unset($p);
    return $pages;
}

function ai_child(string $slug): ?array
{
    $all = ai_children();
    return isset($all[$slug]) ? ['slug' => $slug] + $all[$slug] : null;
}
