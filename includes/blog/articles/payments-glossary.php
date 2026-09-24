<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'payments-glossary',
    'category'    => 'learning',
    'type'        => 'general',
    'status'      => 'in_review',
    'created'     => '2026-09-24',
    'updated'     => '2026-09-24',
    'editor'      => null,
    'approved_by' => null,
    'approved_on' => null,
    'indexable'   => false,
    'sitemap'     => false,

    'title'       => 'Payments glossary: key terms every business team should know',
    'meta_title'  => 'Payments Glossary: Key Terms Explained Simply | Paynancial Insights',
    'description' => 'Plain-language definitions of the payment terms finance, product and support teams meet every day — from authorisation and capture to settlement, reconciliation, payouts, webhooks and idempotency.',
    'dek'         => 'Payments has its own vocabulary, and small misunderstandings — "paid" versus "settled", "declined" versus "failed" — cause real confusion between teams. This glossary gives everyone the same definitions.',

    'question'    => 'What are the most important payment terms to understand?',
    'answer'      => 'The core lifecycle terms are authorisation (the payer\'s bank approves the payment), capture (the merchant confirms it), settlement (the money is paid into the merchant\'s account) and reconciliation (matching payments, settlements and bank records). Around them sit terms for money going back — refunds and chargebacks — and for money going out, such as payouts and beneficiaries.',
    'takeaways'   => [
        '"Paid" and "settled" are different moments — agree which one your team means.',
        'A decline is a bank\'s decision; a failure is a technical problem.',
        'Refunds are started by the merchant; chargebacks by the customer\'s bank.',
        'Shared definitions prevent many support and finance mix-ups.',
    ],

    'sections' => [
        ['lifecycle', 'The payment lifecycle', <<<'HTML'
<dl>
  <dt>Authorisation</dt><dd>The payer's bank or issuer approving a payment request, after checking details, funds or limits, and risk.</dd>
  <dt>Authentication</dt><dd>A check that the person paying is the genuine account or card holder — for example a one-time password or approval in a banking app.</dd>
  <dt>Capture</dt><dd>The merchant's instruction to collect an authorised amount. Some businesses capture immediately; others capture when an order ships.</dd>
  <dt>Authorisation hold</dt><dd>An amount reserved on a customer's card after authorisation and before capture. It is released if the payment is never captured.</dd>
  <dt>Clearing</dt><dd>The exchange of transaction details between the banks involved, so the money can be moved.</dd>
  <dt>Settlement</dt><dd>The transfer of collected funds to the merchant's bank account, usually in batches and net of refunds, disputes and fees.</dd>
  <dt>T+n</dt><dd>Shorthand for a settlement schedule: T is the transaction day, n the number of working days until settlement.</dd>
</dl>
HTML],
        ['outcomes', 'Payment outcomes', <<<'HTML'
<dl>
  <dt>Successful payment</dt><dd>A payment that was authorised and completed.</dd>
  <dt>Declined payment</dt><dd>A payment the payer's bank received and refused — for example for insufficient funds or incorrect details.</dd>
  <dt>Failed payment</dt><dd>A payment that did not complete its journey, such as after a time-out, so no clear approval or refusal came back.</dd>
  <dt>Pending payment</dt><dd>A payment whose final outcome is not yet known. It should not be treated as failed until confirmed.</dd>
  <dt>Abandoned payment</dt><dd>A payment the customer started but did not finish.</dd>
  <dt>Decline code / reason code</dt><dd>A code returned with a decline explaining the reason, useful for diagnosing patterns.</dd>
</dl>
HTML],
        ['money-back', 'Money going back', <<<'HTML'
<dl>
  <dt>Refund</dt><dd>A return of money to the customer, started by the merchant. Can be full or partial.</dd>
  <dt>Chargeback</dt><dd>A reversal started by the customer's bank or card issuer after the customer disputes a payment.</dd>
  <dt>Dispute</dt><dd>A customer's formal challenge to a payment, which can lead to a chargeback.</dd>
  <dt>Reversal</dt><dd>A general term for undoing a transaction, for example when a payment is cancelled before settlement.</dd>
</dl>
HTML],
        ['money-out', 'Money going out', <<<'HTML'
<dl>
  <dt>Payout</dt><dd>A payment from a business to someone else — a vendor, employee, partner or customer.</dd>
  <dt>Bulk payout</dt><dd>Many payouts prepared and sent together as a batch.</dd>
  <dt>Beneficiary</dt><dd>The person or business receiving a payout, with their verified account details.</dd>
  <dt>Maker-checker</dt><dd>A control in which one person prepares a payment and a different person approves it.</dd>
  <dt>UTR</dt><dd>A unique reference number assigned to a bank transfer, used to trace and confirm it.</dd>
</dl>
HTML],
        ['operations', 'Finance operations', <<<'HTML'
<dl>
  <dt>Reconciliation</dt><dd>Matching orders or invoices, payment and settlement reports, and bank statements, and explaining every difference.</dd>
  <dt>Settlement report</dt><dd>A breakdown of what a settlement contains: payments, refunds, disputes and fees.</dd>
  <dt>Receivables</dt><dd>Money owed to the business by customers, for example unpaid invoices.</dd>
  <dt>Ageing report</dt><dd>A list of unpaid invoices grouped by how long they have been outstanding.</dd>
  <dt>Cash conversion cycle</dt><dd>How long cash is tied up between paying for work or stock and being paid for it.</dd>
  <dt>MDR (merchant discount rate)</dt><dd>A general term for the fee a merchant pays on a payment, often expressed as a percentage. What applies to you is set out in your agreement with your provider.</dd>
</dl>
HTML],
        ['technical', 'Technical terms', <<<'HTML'
<dl>
  <dt>Payment gateway</dt><dd>The technology that securely collects payment details at checkout and routes transactions onward.</dd>
  <dt>API</dt><dd>An interface that lets your software create payments, refunds and payouts and check their status.</dd>
  <dt>Sandbox</dt><dd>A test environment that behaves like the live system but moves no real money.</dd>
  <dt>Webhook</dt><dd>A message the payment provider sends to your system when an event happens, such as a payment succeeding.</dd>
  <dt>Idempotency key</dt><dd>A unique value sent with a request so that retrying it cannot create a duplicate payment, refund or payout.</dd>
  <dt>Tokenisation</dt><dd>Replacing sensitive payment details with a substitute value (a token) that is useless if stolen.</dd>
  <dt>KYC (Know Your Customer)</dt><dd>The process of verifying the identity of a customer or business before providing services.</dd>
</dl>
HTML],
    ],

    'faqs' => [
        ['What is the difference between paid and settled?', 'A payment is "paid" when it has been authorised and completed. It is "settled" when the money has actually been transferred into the merchant\'s bank account, which usually happens later.'],
        ['What is the difference between authorisation and capture?', 'Authorisation is the payer\'s bank approving and reserving the amount. Capture is the merchant confirming that the amount should be collected.'],
        ['What does a beneficiary mean in payouts?', 'The person or business that receives a payout, together with their verified bank or payment account details.'],
        ['What is tokenisation in payments?', 'Replacing sensitive payment details, such as a card number, with a substitute value called a token, so the real details do not need to be stored or shared.'],
    ],

    'related' => ['how-online-card-payments-work', 'payment-settlement-explained', 'payment-security-basics-small-business'],
    'links'   => [['All Resources', '/resources'], ['FAQs', '/resources/faqs'], ['Developer Documentation', '/developers']],
];
