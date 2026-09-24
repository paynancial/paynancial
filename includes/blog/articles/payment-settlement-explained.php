<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'payment-settlement-explained',
    'category'    => 'payments',
    'type'        => 'general',
    'status'      => 'in_review',
    'created'     => '2026-09-23',
    'updated'     => '2026-09-23',
    'editor'      => null,
    'approved_by' => null,
    'approved_on' => null,
    'indexable'   => false,
    'sitemap'     => false,

    'title'       => 'Payment settlement explained: when does the money reach your bank?',
    'meta_title'  => 'Payment Settlement Explained for Businesses | Paynancial Insights',
    'description' => 'What payment settlement is, why it is not instant, what "T+1" means, why settlement amounts differ from sales, and how to read a settlement report.',
    'dek'         => 'A customer pays today; your bank balance changes later, by a different amount. Settlement is the step that explains the gap — and understanding it makes cash planning and reconciliation much easier.',

    'question'    => 'What is payment settlement?',
    'answer'      => 'Settlement is the transfer of the money you have collected from customers into your business bank account, after the payments have been confirmed and cleared between the banks involved. It happens on a schedule agreed with your payment provider, usually in batches, and the amount settled is typically your collections minus refunds, chargebacks and agreed fees.',
    'takeaways'   => [
        'A successful payment and a settled payment are two different moments.',
        'Settlement schedules are usually written as T+n, where T is the day of the transaction.',
        'Settlement amounts are net figures: collections minus refunds, disputes and fees.',
        'Every settlement should come with a report that lets you trace it back to individual payments.',
    ],

    'sections' => [
        ['why-not-instant', 'Why settlement is not instant', <<<'HTML'
<p>When a customer pays, what you see immediately is confirmation that the payment was approved. The money itself still has to move between the customer's bank, the payment networks and the banks on the merchant's side. That movement happens in organised cycles, on bank working days, so that large numbers of payments can be exchanged and checked efficiently.</p>
<p>Your payment provider then pays out to your bank account according to your settlement schedule. The result: money collected on a given day typically reaches your account on a later day.</p>
HTML],
        ['t-plus', 'What "T+1" and "T+2" mean', <<<'HTML'
<p>Settlement timings are commonly written as <strong>T+n</strong>. <strong>T</strong> is the day of the transaction, and <strong>n</strong> is the number of working days after it that the money is settled. So a T+1 schedule means payments collected on one working day are settled on the next working day.</p>
<p>Your actual schedule depends on your agreement with your payment provider and can differ by payment method. Weekends and bank holidays usually push settlement to the next working day, which is why Monday settlements often include several days of sales.</p>
HTML],
        ['net', 'Why the settled amount differs from your sales', <<<'HTML'
<p>A settlement is rarely equal to the sales you recorded for the same period. Typical differences include:</p>
<ul>
  <li><strong>Fees</strong> agreed with your payment provider, and any taxes charged on those fees;</li>
  <li><strong>Refunds</strong> you issued during the period;</li>
  <li><strong>Chargebacks</strong> and dispute amounts held or deducted;</li>
  <li><strong>Timing</strong> — payments made late in the day or before a holiday may fall into a later settlement;</li>
  <li><strong>Reserves or holds</strong>, if your agreement includes them.</li>
</ul>
HTML],
        ['report', 'How to read a settlement report', <<<'HTML'
<p>Each settlement should come with a report that breaks the total down. A useful report lets you answer three questions:</p>
<ol>
  <li><strong>Which payments are included?</strong> Each payment should appear with a reference you can match to your own order or invoice.</li>
  <li><strong>What was deducted, and why?</strong> Fees, refunds and disputes should be listed individually, not only as totals.</li>
  <li><strong>Which bank credit does this correspond to?</strong> The settlement should carry a reference that appears on your bank statement.</li>
</ol>
<p>With those three links in place, reconciling settlements against your books becomes a matching exercise rather than detective work. Our guide to <a href="/blog/payment-reconciliation-explained">payment reconciliation</a> walks through the process.</p>
HTML],
        ['planning', 'Planning cash flow around settlement', <<<'HTML'
<p>Because settlement lags behind sales, it matters for cash planning:</p>
<ul>
  <li>plan supplier and salary payments around when money actually arrives, not when sales happen;</li>
  <li>expect lower settlements after days with many refunds, and larger ones after weekends and holidays;</li>
  <li>keep a small buffer for disputes that may be deducted from a future settlement.</li>
</ul>
<p>More on this in <a href="/blog/cash-flow-basics-for-growing-businesses">Cash flow basics for growing businesses</a>.</p>
HTML],
    ],

    'faqs' => [
        ['Why did my settlement arrive later than expected?', 'Common reasons are weekends and bank holidays, payments made close to the daily cut-off time, or a hold on part of the amount. Your settlement report and your provider can confirm which applies.'],
        ['Why is my settlement lower than my sales?', 'Settlements are net amounts. Refunds, chargebacks and fees agreed with your payment provider are deducted before the money is paid to your bank account.'],
        ['What does T+1 settlement mean?', 'It means payments collected on a working day (T) are settled one working day later. The schedule that applies to you is set out in your agreement with your payment provider.'],
        ['How do I match a settlement to my bank statement?', 'Use the settlement reference or UTR shown in your settlement report. The same reference normally appears against the credit on your bank statement.'],
    ],

    'related' => ['payment-reconciliation-explained', 'how-online-card-payments-work', 'cash-flow-basics-for-growing-businesses'],
    'links'   => [['Settlements', '/products/settlements'], ['Reconciliation', '/products/reconciliation'], ['MIS & Reports', '/products/mis-reports']],
];
