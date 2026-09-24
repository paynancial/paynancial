<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'refunds-vs-chargebacks',
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

    'title'       => 'Refunds vs chargebacks: what merchants need to know',
    'meta_title'  => 'Refunds vs Chargebacks: A Guide for Merchants | Paynancial Insights',
    'description' => 'The difference between a refund and a chargeback, why chargebacks cost more, how to respond to a dispute, and practical ways to prevent chargebacks before they start.',
    'dek'         => 'Both send money back to the customer. One is your decision; the other is a dispute raised against you. Knowing the difference — and preventing the second — protects your revenue and your reputation.',

    'question'    => 'What is the difference between a refund and a chargeback?',
    'answer'      => 'A refund is initiated by the merchant, who chooses to return money to the customer — for a return, a cancellation or a mistake. A chargeback is initiated by the customer through their bank or card issuer, who disputes the payment and asks for the money back. Refunds are within your control; chargebacks follow a formal dispute process with deadlines, evidence requirements and usually extra fees.',
    'takeaways'   => [
        'Refunds are started by the merchant; chargebacks are started by the customer through their bank.',
        'Chargebacks follow card network and bank rules, with deadlines you must meet to respond.',
        'Many chargebacks come from confusion, not fraud: unclear billing names, slow refunds, poor communication.',
        'A fast, easy refund is often cheaper than defending a chargeback.',
    ],

    'sections' => [
        ['refunds', 'What a refund is', <<<'HTML'
<p>A refund is a payment back to the customer that <strong>you</strong> start. Common reasons include a returned product, a cancelled order or booking, a service that was not delivered, or a billing mistake. Refunds can be full or partial.</p>
<p>The refund travels back along the same route as the original payment, so even if you process it immediately the customer may not see the money straight away. Telling the customer when to expect it avoids a lot of follow-up messages — and prevents them from disputing the payment while the refund is still on its way.</p>
HTML],
        ['chargebacks', 'What a chargeback is', <<<'HTML'
<p>A chargeback starts when a customer contacts their bank or card issuer and disputes a payment. The issuer can reverse the payment and pass the dispute to the merchant's side, and the merchant is asked to respond.</p>
<p>Customers dispute payments for several reasons:</p>
<ul>
  <li><strong>They do not recognise the charge</strong> — often because the name on their statement does not match the brand they bought from.</li>
  <li><strong>The order did not arrive</strong>, or arrived damaged or different from what was described.</li>
  <li><strong>A promised refund did not appear</strong>, or took longer than they expected.</li>
  <li><strong>They were charged more than once</strong>, or after cancelling a subscription.</li>
  <li><strong>Genuine fraud</strong> — the card was used without the cardholder's permission.</li>
</ul>
HTML],
        ['compare', 'Refund vs chargeback at a glance', <<<'HTML'
<div class="blog-table"><table>
  <thead><tr><th></th><th>Refund</th><th>Chargeback</th></tr></thead>
  <tbody>
    <tr><th>Started by</th><td>Merchant</td><td>Customer, through their bank or card issuer</td></tr>
    <tr><th>Your control</th><td>Full — you decide</td><td>Limited — you can respond with evidence</td></tr>
    <tr><th>Process</th><td>Simple reversal of the payment</td><td>Formal dispute with deadlines and evidence requirements</td></tr>
    <tr><th>Cost</th><td>The refunded amount</td><td>The disputed amount, usually plus dispute fees, plus the time to respond</td></tr>
    <tr><th>Customer relationship</th><td>Often preserved</td><td>Often strained</td></tr>
  </tbody>
</table></div>
<p>High chargeback levels can also affect your relationship with your payment provider, which is another reason to keep them low.</p>
HTML],
        ['respond', 'How to respond to a chargeback', <<<'HTML'
<ol>
  <li><strong>Act quickly.</strong> Disputes come with response deadlines set by the card network and bank rules. Missing one usually means losing the dispute by default.</li>
  <li><strong>Understand the reason.</strong> Each dispute carries a reason. Your evidence needs to answer that specific reason.</li>
  <li><strong>Gather evidence.</strong> Order and payment records, delivery confirmation, the customer's acceptance of your terms, communication with the customer, and records of service usage or login activity are typical examples.</li>
  <li><strong>Decide whether to accept or contest.</strong> If the customer is right — the item never arrived, or a refund was missed — accepting the dispute and fixing the underlying problem is often the better choice.</li>
  <li><strong>Learn from it.</strong> Record why each dispute happened. Patterns point to the fixes that matter most.</li>
</ol>
HTML],
        ['prevent', 'Preventing chargebacks', <<<'HTML'
<p>Most chargebacks are easier to prevent than to win:</p>
<ul>
  <li><strong>Use a recognisable billing name</strong>, so customers know the charge on their statement is you.</li>
  <li><strong>Make your refund policy easy to find</strong> and your refund process easy to use.</li>
  <li><strong>Refund promptly</strong> once you have agreed to, and tell the customer when to expect the money.</li>
  <li><strong>Send clear confirmations</strong> for orders, shipments, renewals and cancellations.</li>
  <li><strong>Make contacting you easier than contacting the bank</strong> — a visible support email or phone number stops many disputes before they start.</li>
  <li><strong>For subscriptions, remind customers before renewals</strong> and make cancelling straightforward.</li>
</ul>
HTML],
    ],

    'faqs' => [
        ['Can a customer raise a chargeback after I have refunded them?', 'It can happen, for example if the customer raised the dispute before the refund reached them. Keep records of every refund so you can show it was already processed if you need to respond to the dispute.'],
        ['Is it better to refund or to fight a chargeback?', 'If the customer\'s complaint is valid, a prompt refund is usually cheaper and better for the relationship. Contest a chargeback when you have clear evidence that the payment was authorised and the goods or service were delivered as described.'],
        ['How long do I have to respond to a chargeback?', 'Response deadlines are set by the card network and bank rules and can vary. Your payment provider will tell you the deadline for each dispute — treat it as firm.'],
        ['What is "friendly fraud"?', 'It is a term for disputes where the cardholder made the purchase but disputes it anyway — sometimes deliberately, often because they did not recognise the charge or forgot a subscription. Clear billing names and reminders reduce it.'],
    ],

    'related' => ['how-online-card-payments-work', 'payment-settlement-explained', 'payment-reconciliation-explained'],
    'links'   => [['Refunds', '/products/refunds'], ['Chargebacks', '/products/chargebacks'], ['Recurring Payments', '/products/recurring-payments']],
];
