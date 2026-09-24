<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'how-online-card-payments-work',
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
    'featured'    => true,

    'title'       => 'How an online card payment works, step by step',
    'meta_title'  => 'How Online Card Payments Work, Step by Step | Paynancial Insights',
    'description' => 'What happens between a customer clicking "Pay" and the money reaching your bank account: authorisation, authentication, capture, clearing and settlement, explained in plain language.',
    'dek'         => 'From the moment a customer clicks "Pay" to the day funds land in your account, several parties work together in a few seconds — and then over the following days. Here is who does what.',

    'question'    => 'What actually happens when a customer pays online by card?',
    'answer'      => 'The card details go from your checkout to a payment gateway, which passes an authorisation request through the acquiring side and the card network to the bank that issued the card. The issuing bank checks the card, the available balance or limit and its risk rules, may ask the customer to authenticate, and then approves or declines — usually within seconds. Approved payments are later captured, cleared between the banks and settled to the merchant\'s account.',
    'takeaways'   => [
        'Authorisation (is this payment allowed?) happens in seconds; settlement (moving the money) happens later.',
        'The issuing bank — not the merchant or the gateway — makes the approve-or-decline decision.',
        'A declined payment is a decision; a failed payment is usually a technical problem somewhere along the chain.',
        'Refunds and chargebacks travel back along the same chain, which is why they take time.',
    ],

    'sections' => [
        ['who', 'The parties in a card payment', <<<'HTML'
<p>Every online card payment involves the same core participants, even if the customer only ever sees your checkout page:</p>
<dl>
  <dt>Customer (cardholder)</dt><dd>The person paying, using a card issued to them by their bank.</dd>
  <dt>Merchant</dt><dd>Your business — the seller receiving the payment.</dd>
  <dt>Payment gateway</dt><dd>The technology that securely collects payment details at checkout and routes the transaction onward. It is the part of the chain your website or app talks to directly.</dd>
  <dt>Acquiring side</dt><dd>The bank or processor that handles card payments on the merchant's behalf and ultimately pays the merchant.</dd>
  <dt>Card network</dt><dd>The network whose logo is on the card. It carries messages between the acquiring side and the issuing bank and sets the rules both sides follow.</dd>
  <dt>Issuing bank</dt><dd>The customer's bank, which issued the card. It decides whether to approve the payment.</dd>
</dl>
HTML],
        ['authorisation', 'Step 1: Authorisation — is this payment allowed?', <<<'HTML'
<p>When the customer submits their card details, the payment gateway packages them into an authorisation request and sends it towards the issuing bank through the acquiring side and the card network.</p>
<p>The issuing bank then checks, among other things:</p>
<ul>
  <li>that the card number, expiry date and security code are valid;</li>
  <li>that the card is active and not reported lost or stolen;</li>
  <li>that there is enough balance or credit limit for the amount;</li>
  <li>whether the payment looks unusual against the customer's normal behaviour.</li>
</ul>
<p>The answer — approved or declined, with a reason code — travels back along the same route to your checkout. From the customer's point of view, all of this happens in a few seconds.</p>
HTML],
        ['authentication', 'Step 2: Authentication — is it really the cardholder?', <<<'HTML'
<p>Authorisation checks the card; authentication checks the person. For many online card payments the issuing bank asks the customer to prove they are the cardholder — for example by entering a one-time password sent to their phone or approving the payment in their banking app.</p>
<p>This step is where a noticeable share of checkout drop-off can happen: the customer might not receive the code, switch apps and not come back, or run out of time. A clear checkout that explains what will happen next, and keeps the customer's place if they switch apps, makes a real difference.</p>
HTML],
        ['capture', 'Step 3: Capture — confirming the sale', <<<'HTML'
<p>An approved authorisation reserves the amount on the customer's card. <strong>Capture</strong> is the instruction that says "go ahead and collect it". Many businesses capture immediately. Others authorise first and capture later — for example when an order ships or a booking is confirmed — and release the hold if the order is cancelled.</p>
<p>If an authorisation is never captured, the hold eventually lapses and the customer is not charged. This is a common source of "I was charged but the order failed" confusion: the customer sees a pending hold that is later released.</p>
HTML],
        ['settlement', 'Step 4: Clearing and settlement — moving the money', <<<'HTML'
<p>Captured payments are grouped and exchanged between the acquiring side and the issuing banks through the card network — this is <strong>clearing</strong>. The money then moves, and the acquiring side pays the merchant, net of any agreed fees — this is <strong>settlement</strong>.</p>
<p>Settlement is not instant. When funds reach your bank account depends on your agreement with your payment provider, bank working days and the payment method. That is why your sales total for a day and the amount that arrives in your bank account are rarely the same figure on the same day — and why <a href="/blog/payment-reconciliation-explained">reconciliation</a> matters.</p>
HTML],
        ['declines', 'Declines, failures and what they tell you', <<<'HTML'
<p>It helps to separate two outcomes that customers experience the same way:</p>
<div class="blog-table"><table>
  <thead><tr><th>Outcome</th><th>What it means</th><th>Typical causes</th></tr></thead>
  <tbody>
    <tr><th>Declined</th><td>The issuing bank received the request and said no.</td><td>Insufficient funds, incorrect details, card blocked, risk rules, authentication not completed.</td></tr>
    <tr><th>Failed</th><td>The request did not complete its journey or no clear answer came back.</td><td>Network time-outs, a bank or system being unavailable, the customer closing the page mid-payment.</td></tr>
  </tbody>
</table></div>
<p>Reason codes returned with declines are your best diagnostic tool. Tracking them over time shows whether a problem is with customers' cards, with authentication, or with something in your own checkout. Our guide to <a href="/blog/reduce-failed-payments">reducing failed payments</a> goes deeper.</p>
HTML],
        ['refunds', 'How refunds and chargebacks fit in', <<<'HTML'
<p>Money can also flow back to the customer, along the same chain in reverse:</p>
<ul>
  <li>A <strong>refund</strong> is started by the merchant — for a return, a cancelled order or a goodwill gesture.</li>
  <li>A <strong>chargeback</strong> is started by the customer through their issuing bank, when they dispute a payment.</li>
</ul>
<p>Because both involve the issuing bank and the network, the customer does not see the money instantly even when you act immediately. The difference between the two, and how to handle each, is covered in <a href="/blog/refunds-vs-chargebacks">Refunds vs chargebacks</a>.</p>
HTML],
    ],

    'faqs' => [
        ['How long does card authorisation take?', 'For the customer it usually takes a few seconds, including the round trip to the issuing bank. An authentication step, such as entering a one-time password, adds however long the customer takes to complete it.'],
        ['Why was the customer charged if the payment failed?', 'Often the customer is seeing an authorisation hold rather than a completed charge. If the payment was not captured, the hold is released; if it was captured but the order failed, the merchant needs to refund it.'],
        ['Who decides whether a card payment is approved?', 'The bank that issued the card. The merchant and the payment gateway pass the request on, but the approve-or-decline decision is the issuing bank\'s.'],
        ['Why does settlement take longer than the payment?', 'Authorisation only confirms the payment is allowed. Moving the money involves clearing between banks and paying the merchant according to the agreed settlement schedule, which follows bank working days.'],
    ],

    'related' => ['reduce-failed-payments', 'refunds-vs-chargebacks', 'payment-settlement-explained'],
    'links'   => [['Payment Gateway', '/products/payment-gateway'], ['Accept & Collect', '/products/accept-and-collect'], ['Settlements', '/products/settlements']],
];
