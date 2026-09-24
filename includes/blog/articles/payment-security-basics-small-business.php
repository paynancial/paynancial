<?php
/** Blog article — general (non-regulatory). See includes/blog.php for status rules. */
return [
    'slug'        => 'payment-security-basics-small-business',
    'category'    => 'learning',
    'type'        => 'general',
    'status'      => 'indexable',
    'created'     => '2026-09-24',
    'updated'     => '2026-09-24',
    'editor'      => null,
    'approved_by' => 'Paynancial Editorial Team',
    'approved_on' => '2026-09-24',
    'indexable'   => true,
    'sitemap'     => true,

    'title'       => 'Payment security basics for small businesses',
    'meta_title'  => 'Payment Security Basics for Small Businesses | Paynancial Insights',
    'description' => 'Practical payment security for small teams: protecting accounts and API keys, spotting phishing and fake payment requests, handling customer data safely, and what to do if something goes wrong.',
    'dek'         => 'Most payment fraud against small businesses does not involve sophisticated hacking. It involves a convincing message, a reused password or a single person able to move money alone. The defences are simple — if they are actually in place.',

    'question'    => 'How can a small business keep its payments secure?',
    'answer'      => 'Protect access first: unique strong passwords, multi-factor authentication and the minimum access each person needs. Require a second person to approve payouts and bank-detail changes, verify any payment request through a known contact, keep API keys on your server only, avoid collecting card details yourself where a hosted payment page can do it, and train your team to recognise phishing and fake payment requests.',
    'takeaways'   => [
        'Turn on multi-factor authentication for every payment and banking account.',
        'No one person should be able to add a payee and send money alone.',
        'Verify payment requests and bank-detail changes through a contact you already know.',
        'Never ask for, or store, customers\' card PINs, CVVs, OTPs or UPI PINs.',
    ],

    'sections' => [
        ['threats', 'The threats small businesses actually face', <<<'HTML'
<ul>
  <li><strong>Phishing</strong> — emails, messages or calls that trick someone into sharing a password or one-time code.</li>
  <li><strong>Fake payment requests</strong> — a message that appears to come from a director or supplier, asking for an urgent transfer.</li>
  <li><strong>Changed bank details</strong> — a "supplier" asking you to pay a new account.</li>
  <li><strong>Account takeover</strong> — a reused or weak password giving someone access to your dashboard.</li>
  <li><strong>Leaked keys</strong> — API keys committed to code, shared in chat or embedded in an app.</li>
  <li><strong>Fake payment confirmations</strong> — a customer showing a doctored screenshot instead of paying.</li>
</ul>
HTML],
        ['access', 'Protect access', <<<'HTML'
<ol>
  <li><strong>Unique, strong passwords</strong> for every payment, banking and email account, kept in a password manager.</li>
  <li><strong>Multi-factor authentication</strong> everywhere it is offered — especially email, because email resets everything else.</li>
  <li><strong>Individual logins.</strong> Never share one account between staff; you lose the ability to see who did what.</li>
  <li><strong>Least privilege.</strong> Give each person only the access their role needs, and remove access the day someone leaves.</li>
</ol>
HTML],
        ['money-out', 'Control money going out', <<<'HTML'
<ul>
  <li>require a second person to approve payouts, and to approve adding or changing a beneficiary;</li>
  <li>set limits on amounts that can be sent without extra approval;</li>
  <li>confirm any request to change bank details, or any unusual payment request, by calling a number you already have on file — never one given in the request;</li>
  <li>be most careful with urgency: "pay this now, and keep it quiet" is a classic warning sign.</li>
</ul>
<p>More detail in <a href="/blog/vendor-payout-process">Paying vendors at scale</a>.</p>
HTML],
        ['money-in', 'Check money coming in', <<<'HTML'
<ul>
  <li>confirm payments in your payment dashboard or bank account — never on the strength of a customer's screenshot or message;</li>
  <li>be wary of overpayments followed by a request to refund the difference to a different account;</li>
  <li>refund only to the original payment method.</li>
</ul>
HTML],
        ['data', 'Handle customer data carefully', <<<'HTML'
<ul>
  <li><strong>Do not collect card details yourself</strong> by phone, email or form if a hosted payment page or payment link can collect them instead.</li>
  <li><strong>Never ask customers for</strong> their card PIN, CVV, OTP, UPI PIN or banking passwords.</li>
  <li><strong>Keep only what you need</strong>, for as long as you need it, and restrict who can see it.</li>
  <li><strong>Keep API keys and webhook secrets on your server</strong>, never in website code, mobile apps, spreadsheets or chat messages.</li>
</ul>
HTML],
        ['training', 'Make security part of the routine', <<<'HTML'
<p>Short, regular reminders work better than a single long training session. Share real examples of phishing messages, agree on one simple rule — "any request involving money or bank details is verified by phone" — and make it easy and blame-free for staff to report something suspicious.</p>
HTML],
        ['incident', 'If something goes wrong', <<<'HTML'
<ol>
  <li><strong>Act fast.</strong> Contact your bank and payment provider immediately — speed improves the chance of stopping or recovering a payment.</li>
  <li><strong>Secure accounts.</strong> Change passwords, revoke sessions and rotate any exposed API keys.</li>
  <li><strong>Preserve evidence</strong> — messages, emails, transaction references and times.</li>
  <li><strong>Report it</strong> through the appropriate official channels, and take professional advice where needed.</li>
  <li><strong>Learn from it.</strong> Identify which control would have stopped it, and put it in place.</li>
</ol>
HTML],
    ],

    'faqs' => [
        ['What is the single most effective security step for a small business?', 'Turning on multi-factor authentication for email, banking and payment accounts, combined with requiring a second person to approve payouts and bank-detail changes.'],
        ['How do I spot a fake payment request?', 'Warning signs include urgency, secrecy, a new or changed bank account, and a request that arrives by email or message rather than through your normal process. Verify it by calling a number you already have on file.'],
        ['Should I accept card details over the phone or email?', 'It is safer to send the customer a payment link or hosted payment page so they enter their details directly, rather than collecting and handling card details yourself.'],
        ['What should I do if an API key is exposed?', 'Rotate the key immediately through your provider\'s dashboard, review recent activity for anything unexpected, and remove the key from wherever it was exposed.'],
    ],

    'related' => ['vendor-payout-process', 'payments-glossary', 'payment-integration-go-live-checklist'],
    'links'   => [['Security & Compliance', '/security'], ['Trust Center', '/trust'], ['Payment Links', '/products/payment-links']],
];
