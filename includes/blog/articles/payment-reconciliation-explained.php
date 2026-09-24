<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'payment-reconciliation-explained',
    'category'    => 'business-finance',
    'type'        => 'general',
    'status'      => 'in_review',
    'created'     => '2026-09-23',
    'updated'     => '2026-09-23',
    'editor'      => null,
    'approved_by' => null,
    'approved_on' => null,
    'indexable'   => false,
    'sitemap'     => false,

    'title'       => 'Payment reconciliation explained: a practical guide for finance teams',
    'meta_title'  => 'Payment Reconciliation Explained: A Practical Guide | Paynancial Insights',
    'description' => 'What payment reconciliation is, the three-way match between orders, payments and bank credits, common reasons records do not match, and how to build a reconciliation routine that scales.',
    'dek'         => 'Reconciliation is how a business proves that every payment it expected actually arrived — and that every rupee in the bank can be explained. Done well, it is routine. Done late, it becomes a monthly crisis.',

    'question'    => 'What is payment reconciliation?',
    'answer'      => 'Payment reconciliation is the process of matching three sets of records — what you expected to be paid (orders or invoices), what your payment provider says was collected and settled, and what actually arrived in your bank account — and investigating every difference. The aim is that each bank credit can be traced to specific payments, and each payment to a specific order or invoice.',
    'takeaways'   => [
        'Reconcile three records: orders or invoices, payment and settlement reports, and bank statements.',
        'Most mismatches come from timing, fees, refunds, partial payments and missing references.',
        'A shared reference that flows from order to payment to settlement makes matching far easier.',
        'Reconcile daily or weekly; the longer you wait, the harder differences are to explain.',
    ],

    'sections' => [
        ['three-way', 'The three-way match', <<<'HTML'
<p>Reconciliation compares three sources, each answering a different question:</p>
<div class="blog-table"><table>
  <thead><tr><th>Source</th><th>Question it answers</th></tr></thead>
  <tbody>
    <tr><th>Orders or invoices</th><td>What were we supposed to be paid, by whom, for what?</td></tr>
    <tr><th>Payment and settlement reports</th><td>What did the payment provider collect, deduct and pay out?</td></tr>
    <tr><th>Bank statement</th><td>What actually reached our account, and when?</td></tr>
  </tbody>
</table></div>
<p>A payment is fully reconciled when all three agree — the order is marked paid, the payment appears in a settlement, and that settlement matches a credit on the bank statement.</p>
HTML],
        ['mismatches', 'Why records do not match', <<<'HTML'
<p>Differences are normal. The skill is explaining them quickly. The usual suspects:</p>
<ul>
  <li><strong>Timing.</strong> A payment made today may be settled on a later working day, so it appears in different periods in different records.</li>
  <li><strong>Fees.</strong> Settlements are net of fees, so the bank credit is lower than the sum of payments.</li>
  <li><strong>Refunds and chargebacks</strong> deducted from settlements.</li>
  <li><strong>Partial or combined payments.</strong> A customer pays part of an invoice, or one payment covers several invoices.</li>
  <li><strong>Missing or wrong references.</strong> A bank transfer arrives with no invoice number, or the wrong one.</li>
  <li><strong>Duplicates.</strong> A customer pays twice, or an order is recorded twice.</li>
</ul>
HTML],
        ['routine', 'A reconciliation routine that works', <<<'HTML'
<ol>
  <li><strong>Collect the three sources</strong> for the same period: orders or invoices, the provider's settlement reports, and the bank statement.</li>
  <li><strong>Match settlements to bank credits</strong> using the settlement reference. This confirms the provider paid what it reported.</li>
  <li><strong>Match payments within each settlement to orders or invoices</strong> using your order or invoice reference.</li>
  <li><strong>Account for deductions</strong> — fees, refunds and disputes — line by line.</li>
  <li><strong>List what is left.</strong> Unmatched payments, unpaid orders and unexplained bank credits go on an exceptions list with an owner and a next step.</li>
  <li><strong>Close the period</strong> only when every exception is explained or has a documented follow-up.</li>
</ol>
HTML],
        ['references', 'The single most useful habit: consistent references', <<<'HTML'
<p>Matching is only as good as the references that connect records. If every order or invoice has a unique ID, and that ID is passed into the payment request, it will appear in the provider's reports — and matching becomes largely automatic.</p>
<p>For bank transfers, ask customers to quote the invoice number, or use virtual account numbers or payment links that carry the reference for them. Every payment that arrives without a reference becomes a manual investigation.</p>
HTML],
        ['automate', 'When to automate', <<<'HTML'
<p>Spreadsheet reconciliation works at low volumes. Signs it is time to automate:</p>
<ul>
  <li>closing the books takes days because of payment matching;</li>
  <li>the exceptions list keeps growing month to month;</li>
  <li>you take payments across several methods or providers;</li>
  <li>customers are chased for invoices they have already paid.</li>
</ul>
<p>Automated reconciliation applies matching rules to reports and bank data continuously, leaving people to handle only the genuine exceptions.</p>
HTML],
    ],

    'faqs' => [
        ['How often should a business reconcile payments?', 'As often as your volume requires — daily for businesses taking many payments, weekly for smaller ones. Reconciling frequently keeps differences small and easy to explain.'],
        ['What is a three-way match in payment reconciliation?', 'It is matching the same money across three records: the order or invoice, the payment provider\'s payment and settlement reports, and the bank statement.'],
        ['Why does my settlement not match my sales?', 'Settlements are usually net of fees, refunds and chargebacks, and can include payments from more than one day because of settlement timing.'],
        ['What should I do with unmatched payments?', 'Record them on an exceptions list with an owner, check for missing or incorrect references, contact the payer if needed, and resolve them before closing the period.'],
    ],

    'related' => ['payment-settlement-explained', 'reduce-overdue-invoices', 'vendor-payout-process'],
    'links'   => [['Reconciliation', '/products/reconciliation'], ['Settlements', '/products/settlements'], ['MIS & Reports', '/products/mis-reports']],
];
