<?php
/**
 * Legal pages: /legal/{slug} — Privacy Policy, Terms & Conditions,
 * Refund Policy, Cookie Policy. Each entry's `sections` are rendered
 * as trusted, pre-authored static HTML (not user input) inside the
 * site's normal header/footer chrome; the TOC and section numbering
 * are generated from that same array so they can never drift apart.
 */
$legal_slug = $legal_slug ?? '';

$legalCompany   = 'Paynancial';
$legalName      = 'M/S Paynancial Technology Private Limited';
$legalEmail     = 'hello@paynancial.com';
$legalGst       = '10AAOCP5173C1ZO';

$legalPages = [
    'privacy-policy' => [
        'title'       => 'Privacy Policy',
        'hero_sub'    => "This policy explains what personal data {$legalCompany} collects, why we collect it, how it's used and protected, and the choices you have over it.",
        'effective'   => '1 September 2026',
        'updated'     => '29 August 2026',
        'intro'       => "At {$legalCompany}, we sit in the middle of a lot of sensitive information — payment instructions, KYC documents, bank details — because that's what it takes to move money safely. <strong>This policy is our account of that responsibility:</strong> what we collect, why, who we share it with, and what control you retain throughout.",
        'sections'    => [
            ['id' => 'introduction', 'title' => 'Introduction', 'html' => "
                <p>This Privacy Policy applies to all personal data processed by {$legalName} (<strong>\"{$legalCompany},\" \"we,\" \"us,\" or \"our\"</strong>) in connection with our website, mobile applications, APIs, dashboards, and payment infrastructure services (together, the \"Services\"). It covers data belonging to our merchants, their end-customers where we act as a payment processor, website visitors, job applicants, and business contacts.</p>
                <p>By using our Services, you agree to the collection and use of information described here. If you're interacting with {$legalCompany} as the customer of one of our merchants (for example, completing a checkout that runs on our payment gateway), that merchant's own privacy policy also applies to how they use your data — we act as their data processor for transaction data, and this policy explains our own obligations in that role.</p>
            "],
            ['id' => 'information-we-collect', 'title' => 'Information We Collect', 'html' => "
                <p>We collect information in three broad ways: what you give us directly, what we collect automatically, and what we receive from third parties.</p>
                <h3>Information you provide</h3>
                <ul>
                    <li><strong>Account &amp; KYC data</strong> — name, business name, PAN, GSTIN, bank account and IFSC details, director/authorised-signatory identity documents, and address proof submitted during merchant onboarding.</li>
                    <li><strong>Contact information</strong> — email address, phone number, and billing address.</li>
                    <li><strong>Transaction details</strong> — amounts, timestamps, order references, and payment method metadata processed through our gateway.</li>
                    <li><strong>Support communications</strong> — anything you share with us via email, chat, or phone when requesting help.</li>
                </ul>
                <h3>Information collected automatically</h3>
                <ul>
                    <li><strong>Device &amp; usage data</strong> — IP address, browser type, operating system, device identifiers, and pages viewed on our dashboard or website.</li>
                    <li><strong>Log data</strong> — API request logs, authentication events, and error logs generated as you use our Services.</li>
                    <li><strong>Cookies &amp; similar technologies</strong> — see our <a class=\"inline-link\" href=\"/legal/cookie-policy\">Cookie Policy</a> for full detail.</li>
                </ul>
                <h3>Information from third parties</h3>
                <ul>
                    <li><strong>Card networks, banks &amp; UPI apps</strong> that route payment confirmations back to us.</li>
                    <li><strong>Identity verification providers</strong> used to validate KYC submissions against government-issued ID databases.</li>
                    <li><strong>Credit bureaus or risk-scoring partners</strong>, where relevant to underwriting merchant accounts.</li>
                </ul>
                <div class=\"legal-callout\"><strong>What we don't collect:</strong> we never ask for your card's full magnetic-stripe data, CVV storage beyond the transaction window required by network rules, or your net banking password — no legitimate {$legalCompany} communication will ever request these.</div>
            "],
            ['id' => 'how-we-use-it', 'title' => 'How We Use Information', 'html' => "
                <p>We use the data described above to:</p>
                <ul>
                    <li>Process payments, payouts, settlements, and refunds accurately and on time.</li>
                    <li>Verify merchant identity and comply with RBI-mandated KYC and anti-money-laundering obligations.</li>
                    <li>Detect, investigate, and prevent fraud, unauthorised transactions, and platform abuse.</li>
                    <li>Provide customer support and respond to your requests.</li>
                    <li>Maintain and improve the reliability, security, and performance of our Services.</li>
                    <li>Send transactional notifications (payment confirmations, settlement alerts, security notices) and, where you've opted in, product updates.</li>
                    <li>Meet our statutory reporting, tax, and audit obligations.</li>
                </ul>
            "],
            ['id' => 'legal-basis', 'title' => 'Legal Basis for Processing', 'html' => "
                <p>Where the Digital Personal Data Protection Act, 2023 (\"DPDP Act\") applies, we process personal data on one or more of the following grounds: your consent, the necessity of processing to perform a contract with you, our legitimate interests in operating a secure payments platform, and compliance with legal obligations imposed by the Reserve Bank of India, the Prevention of Money Laundering Act, and other applicable financial regulation.</p>
                <p>Where consent is the basis for processing — for example, marketing communications — you may withdraw it at any time without affecting the lawfulness of processing carried out before withdrawal.</p>
            "],
            ['id' => 'sharing', 'title' => 'Data Sharing & Disclosure', 'html' => "
                <p>We do not sell personal data. We share it only in the following circumstances:</p>
                <ul>
                    <li><strong>Payment ecosystem partners</strong> — banks, card networks, UPI switches, and payment aggregators necessary to complete a transaction you've initiated.</li>
                    <li><strong>Service providers</strong> — cloud infrastructure, identity verification, SMS/email delivery, and analytics vendors who process data on our behalf under contractual confidentiality obligations.</li>
                    <li><strong>Regulators &amp; law enforcement</strong> — where required by the RBI, courts, or other authorities under applicable law.</li>
                    <li><strong>Corporate transactions</strong> — in connection with a merger, acquisition, or asset sale, subject to the same protections described in this policy.</li>
                </ul>
                <p>Every third party we share data with is bound by written agreements requiring them to protect it to a standard consistent with this policy.</p>
            "],
            ['id' => 'security', 'title' => 'Data Security', 'html' => "
                <p>We apply layered technical and organisational safeguards, including encryption of data in transit (TLS 1.2+) and at rest, role-based access controls, network segmentation, and continuous monitoring. Full detail on our security programme is available on our <a class=\"inline-link\" href=\"/security\">Security</a> page.</p>
                <p>No system is completely immune to risk. If a breach affecting your personal data occurs, we will notify affected users and the relevant authorities as required by law.</p>
            "],
            ['id' => 'retention', 'title' => 'Data Retention', 'html' => "
                <p>We retain personal data for as long as necessary to provide the Services and to meet our legal, accounting, and regulatory obligations. Transaction and KYC records are typically retained for a minimum of ten years from the date of the transaction, in line with RBI and PMLA record-keeping requirements. Data no longer required is securely deleted or anonymised.</p>
            "],
            ['id' => 'your-rights', 'title' => 'Your Rights', 'html' => "
                <p>Subject to applicable law, you have the right to:</p>
                <ul>
                    <li><strong>Access</strong> the personal data we hold about you.</li>
                    <li><strong>Correct</strong> inaccurate or incomplete data.</li>
                    <li><strong>Erase</strong> your data, where retention is not required by law.</li>
                    <li><strong>Withdraw consent</strong> for processing based on consent.</li>
                    <li><strong>Nominate</strong> another individual to exercise your rights in the event of death or incapacity, as provided under the DPDP Act.</li>
                    <li><strong>Grievance redressal</strong> — raise a complaint with our Grievance Officer before approaching the Data Protection Board of India.</li>
                </ul>
                <p>To exercise any of these rights, write to us at <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a>. We aim to respond within 30 days.</p>
            "],
            ['id' => 'cookies', 'title' => 'Cookies & Tracking', 'html' => "
                <p>Our website and dashboard use cookies for authentication, security, and analytics. You can manage your preferences at any time through our <a class=\"inline-link\" href=\"/legal/cookie-policy\">Cookie Policy</a>, which describes each category of cookie we use and how to opt out of non-essential ones.</p>
            "],
            ['id' => 'children', 'title' => "Children's Privacy", 'html' => "
                <p>Our Services are intended for businesses and individuals who are at least 18 years old and legally capable of entering into a contract. We do not knowingly collect personal data from children. If we become aware that we have inadvertently collected such data, we will delete it promptly.</p>
            "],
            ['id' => 'transfers', 'title' => 'International Data Transfers', 'html' => "
                <p>Personal data collected in India is primarily stored and processed within India in line with RBI data localisation requirements for payment system data. Where a limited transfer outside India is necessary — for instance, to a global service provider — we ensure appropriate contractual safeguards are in place consistent with the DPDP Act.</p>
            "],
            ['id' => 'changes', 'title' => 'Changes to This Policy', 'html' => "
                <p>We may update this policy from time to time to reflect changes in our practices or applicable law. Material changes will be notified via email or an in-product notice, and the \"Last updated\" date at the top of this page will always reflect the current version.</p>
            "],
            ['id' => 'contact', 'title' => 'Contact Us', 'html' => "
                <p>For any questions about this Privacy Policy or to exercise your data rights, reach our team at <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a>.</p>
            "],
        ],
    ],

    'terms-conditions' => [
        'title'       => 'Terms & Conditions',
        'hero_sub'    => "The rules that govern your use of {$legalCompany}'s website, dashboard, APIs, and payment infrastructure services.",
        'effective'   => '1 September 2026',
        'updated'     => '29 August 2026',
        'intro'       => "These Terms &amp; Conditions (\"Terms\") form a binding agreement between you and {$legalName} governing access to and use of our Services. <strong>Please read them carefully</strong> — by creating an account, integrating our APIs, or otherwise using the Services, you agree to be bound by them.",
        'sections'    => [
            ['id' => 'acceptance', 'title' => 'Acceptance of Terms', 'html' => "
                <p>By accessing or using any part of the Services, you confirm that you have read, understood, and agree to these Terms and our <a class=\"inline-link\" href=\"/legal/privacy-policy\">Privacy Policy</a>. If you're accepting on behalf of a business, you confirm that you have authority to bind that business, and \"you\" in these Terms refers to that business.</p>
            "],
            ['id' => 'services', 'title' => 'Description of Services', 'html' => "
                <p>{$legalCompany} provides payment processing infrastructure, including but not limited to a payment gateway, payment links, payment collection tools, payouts, and analytics, accessible via our dashboard and APIs. We may add, modify, or discontinue features at our discretion, with reasonable notice for any change that materially reduces functionality you rely on.</p>
            "],
            ['id' => 'eligibility', 'title' => 'Eligibility', 'html' => "
                <p>To use the Services you must be at least 18 years old, capable of entering into a legally binding contract under the Indian Contract Act, 1872, and, where registering as a merchant, operating a legally registered business in a category not listed as restricted under our risk and compliance policies (including, but not limited to, unlicensed lending, unregulated crypto trading, and gambling where prohibited by law).</p>
            "],
            ['id' => 'account', 'title' => 'Account Registration', 'html' => "
                <p>You're responsible for maintaining the confidentiality of your account credentials and API keys, and for all activity that occurs under your account. Notify us immediately at <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a> if you suspect unauthorised access.</p>
            "],
            ['id' => 'kyc', 'title' => 'Merchant Obligations & KYC', 'html' => "
                <p>As a condition of using our payment processing Services, merchants agree to:</p>
                <ul>
                    <li>Provide accurate, current business, ownership, and banking information, and update it promptly when it changes.</li>
                    <li>Complete KYC verification, including document submission, as required by RBI regulation before processing live transactions.</li>
                    <li>Only sell goods or services that are lawful, accurately described, and consistent with the business category declared at onboarding.</li>
                    <li>Maintain a clear refund and cancellation policy visible to their own customers.</li>
                    <li>Cooperate with any risk review, audit, or investigation we conduct in relation to your account.</li>
                </ul>
                <div class=\"legal-callout warn\"><strong>Note:</strong> we may suspend or hold settlements on any account where we detect a pattern of disputes, suspected fraud, or a mismatch between declared and actual business activity, pending review.</div>
            "],
            ['id' => 'fees', 'title' => 'Fees & Charges', 'html' => "
                <p>Applicable transaction fees, settlement cycles, and any fixed or subscription charges are set out in your merchant pricing agreement or the pricing published on our website at the time you sign up. Fees are exclusive of applicable taxes, including GST, which will be charged in addition. We may revise pricing with at least 30 days' notice for existing merchants.</p>
            "],
            ['id' => 'prohibited', 'title' => 'Prohibited Uses', 'html' => "
                <p>You agree not to use the Services to:</p>
                <ul>
                    <li>Process transactions for illegal goods or services, or to facilitate money laundering or terrorist financing.</li>
                    <li>Circumvent, disable, or interfere with any security-related feature of the Services.</li>
                    <li>Reverse-engineer, decompile, or attempt to extract the source code of our software, except as permitted by law.</li>
                    <li>Use automated means to access the Services outside of our published APIs and rate limits.</li>
                    <li>Misrepresent your identity, business, or transaction data to circumvent risk controls.</li>
                </ul>
            "],
            ['id' => 'ip', 'title' => 'Intellectual Property', 'html' => "
                <p>All rights, title, and interest in the Services — including our software, APIs, documentation, logos, and trademarks (including \"{$legalCompany}\") — remain our exclusive property or that of our licensors. These Terms grant you a limited, non-exclusive, non-transferable licence to use the Services for their intended purpose; nothing here transfers ownership of any intellectual property to you.</p>
            "],
            ['id' => 'liability', 'title' => 'Limitation of Liability', 'html' => "
                <p>To the maximum extent permitted by law, {$legalCompany} shall not be liable for any indirect, incidental, special, or consequential damages, including loss of profits, revenue, or data, arising from your use of the Services. Our aggregate liability for any claim arising out of these Terms shall not exceed the fees paid by you to us in the three months preceding the event giving rise to the claim.</p>
                <p>Nothing in this section limits liability that cannot be excluded under applicable Indian law.</p>
            "],
            ['id' => 'indemnity', 'title' => 'Indemnification', 'html' => "
                <p>You agree to indemnify and hold {$legalCompany}, its officers, directors, and employees harmless from any claim, loss, or demand, including reasonable legal fees, arising from your breach of these Terms, your use of the Services, or your violation of any applicable law or third-party right.</p>
            "],
            ['id' => 'termination', 'title' => 'Termination', 'html' => "
                <p>Either party may terminate this agreement with 30 days' written notice. We may suspend or terminate your access immediately, without notice, where we reasonably believe you've violated these Terms, engaged in fraudulent activity, or where required by a regulator or law enforcement directive. Provisions relating to fees owed, liability, and indemnification survive termination.</p>
            "],
            ['id' => 'governing-law', 'title' => 'Governing Law & Dispute Resolution', 'html' => "
                <p>These Terms are governed by the laws of India. Any dispute arising out of or in connection with these Terms shall first be attempted to be resolved through good-faith negotiation, failing which it shall be referred to arbitration under the Arbitration and Conciliation Act, 1996, seated in Patna, Bihar, with the courts at Patna having exclusive jurisdiction over any matter not subject to arbitration.</p>
            "],
            ['id' => 'changes', 'title' => 'Changes to These Terms', 'html' => "
                <p>We may revise these Terms from time to time. We'll notify you of material changes via email or an in-product notice at least 15 days before they take effect. Continued use of the Services after that date constitutes acceptance of the revised Terms.</p>
            "],
            ['id' => 'contact', 'title' => 'Contact Us', 'html' => "
                <p>Questions about these Terms can be sent to <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a>.</p>
            "],
        ],
    ],

    'refund-policy' => [
        'title'       => 'Refund Policy',
        'hero_sub'    => "How refunds, failed transactions, and disputed payments are handled across the {$legalCompany} platform.",
        'effective'   => '1 September 2026',
        'updated'     => '29 August 2026',
        'intro'       => "{$legalCompany} processes payments on behalf of merchants — we are not the seller of the goods or services you purchased. <strong>This policy explains our role in refunds</strong>, how failed or duplicate payments are handled, and what to do if a transaction didn't go the way it should have.",
        'sections'    => [
            ['id' => 'overview', 'title' => 'Overview', 'html' => "
                <p>When you pay a merchant through {$legalCompany}'s payment gateway, payment links, or collection tools, the contract for the goods or services is between you and that merchant — not with us. Refund eligibility for a purchase is governed by the merchant's own refund or cancellation policy. Where a refund is approved by the merchant, we facilitate the return of funds through the original payment method.</p>
            "],
            ['id' => 'eligibility', 'title' => 'Refund Eligibility', 'html' => "
                <p>Refunds are typically processed in the following situations:</p>
                <ul>
                    <li>The merchant approves a refund request under their published return/cancellation policy.</li>
                    <li>A payment was debited from your account but the merchant's order confirmation failed (a \"failed transaction\").</li>
                    <li>You were charged more than once for the same order (a \"duplicate transaction\").</li>
                    <li>A transaction is reversed following a chargeback ruling in your favour.</li>
                </ul>
                <div class=\"legal-callout\"><strong>Important:</strong> {$legalCompany} cannot independently approve a refund for a completed, undisputed purchase — that decision sits with the merchant you transacted with. Contact them first using the details on your order confirmation.</div>
            "],
            ['id' => 'timelines', 'title' => 'Refund Timelines', 'html' => "
                <p>Once a refund is initiated, funds are returned to your original payment method within the timelines below. Actual crediting time also depends on your bank or card issuer.</p>
                <table class=\"legal-table\">
                    <tr><th>Payment Method</th><th>Typical Refund Timeline</th></tr>
                    <tr><td><strong>UPI</strong></td><td>1–3 business days</td></tr>
                    <tr><td><strong>Debit / Credit Card</strong></td><td>5–7 business days</td></tr>
                    <tr><td><strong>Net Banking</strong></td><td>3–5 business days</td></tr>
                    <tr><td><strong>Wallets</strong></td><td>1–2 business days</td></tr>
                </table>
            "],
            ['id' => 'failed-transactions', 'title' => 'Failed & Duplicate Transactions', 'html' => "
                <p>If an amount was debited from your account but the merchant did not receive confirmation, the payment is automatically flagged for reconciliation. In most cases, the amount is auto-reversed to your original payment method within 5–7 business days without any action needed from you. If it isn't reversed automatically within that window, contact us with your transaction reference number.</p>
            "],
            ['id' => 'chargebacks', 'title' => 'Chargebacks & Disputes', 'html' => "
                <p>If you believe a transaction was unauthorised or the merchant failed to deliver as promised and hasn't resolved it directly, you may raise a chargeback with your card issuer or bank. Once we receive a chargeback notification, we'll share relevant transaction evidence with the merchant, who has the opportunity to respond within the network's applicable timeline (typically 7–10 days). The card network or bank makes the final determination on the dispute.</p>
            "],
            ['id' => 'non-refundable', 'title' => 'Non-Refundable Items', 'html' => "
                <p>Any payment gateway convenience fees, if separately charged and disclosed at checkout, are generally non-refundable even where the underlying order amount is refunded, unless required otherwise by applicable law or the specific merchant agreement. Digital goods or services explicitly marked non-refundable by the merchant at the time of purchase also fall outside standard refund eligibility.</p>
            "],
            ['id' => 'how-to-request', 'title' => 'How to Request a Refund', 'html' => "
                <ol class=\"legal-list\">
                    <li>Contact the merchant directly using the details on your order confirmation or receipt — most refund requests are resolved fastest this way.</li>
                    <li>If the merchant approves the refund but you don't see it processed within the timelines above, reach out to us with the transaction reference ID, date, and amount.</li>
                    <li>For failed or duplicate payments that haven't auto-reversed, email us at <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a> with your payment proof and UTR/transaction ID.</li>
                </ol>
            "],
            ['id' => 'merchant-responsibility', 'title' => 'Merchant Responsibilities', 'html' => "
                <p>Merchants using {$legalCompany} are required to publish a clear refund and cancellation policy on their own website or checkout flow, and to process approved refunds through their {$legalCompany} dashboard promptly. Merchants who fail to honour approved refunds may be subject to account review, settlement holds, or suspension under our <a class=\"inline-link\" href=\"/legal/terms-conditions\">Terms &amp; Conditions</a>.</p>
            "],
            ['id' => 'contact', 'title' => 'Contact Us', 'html' => "
                <p>For refund-related questions not resolved by the merchant, reach us at <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a>.</p>
            "],
        ],
    ],

    'cookie-policy' => [
        'title'       => 'Cookie Policy',
        'hero_sub'    => "What cookies {$legalCompany} uses, why, and how to control them.",
        'effective'   => '1 September 2026',
        'updated'     => '29 August 2026',
        'intro'       => "This Cookie Policy explains how {$legalCompany} uses cookies and similar technologies on our website and dashboard, what each category does, and how you can control the ones that aren't strictly necessary.",
        'sections'    => [
            ['id' => 'what-are-cookies', 'title' => 'What Are Cookies', 'html' => "
                <p>Cookies are small text files placed on your device when you visit a website. They help the site remember information about your visit — like whether you're logged in — which can make your next visit easier and the site more useful to you.</p>
            "],
            ['id' => 'types', 'title' => 'Types of Cookies We Use', 'html' => "
                <table class=\"legal-table\">
                    <tr><th>Category</th><th>Purpose</th><th>Can be disabled?</th></tr>
                    <tr><td><strong>Strictly Necessary</strong></td><td>Authentication, session security, fraud prevention, and load balancing. Required for the dashboard and checkout to function.</td><td>No</td></tr>
                    <tr><td><strong>Performance &amp; Analytics</strong></td><td>Understand how visitors use our site and dashboard so we can improve reliability and usability.</td><td>Yes</td></tr>
                    <tr><td><strong>Functional</strong></td><td>Remember preferences like language or dashboard layout so you don't have to reset them each visit.</td><td>Yes</td></tr>
                    <tr><td><strong>Marketing</strong></td><td>Measure the effectiveness of our own campaigns. We do not use third-party advertising trackers.</td><td>Yes</td></tr>
                </table>
            "],
            ['id' => 'manage', 'title' => 'Manage Your Preferences', 'html' => "
                <p>You can turn off non-essential cookie categories below. Strictly necessary cookies can't be disabled, as the Services won't function correctly without them.</p>
                <div class=\"cookie-pref\">
                    <div class=\"cookie-pref-info\"><h3>Strictly Necessary</h3><p>Required for login, security, and checkout. Always active.</p></div>
                    <button class=\"cookie-toggle locked\" aria-label=\"Strictly necessary cookies (always on)\" disabled></button>
                </div>
                <div class=\"cookie-pref\">
                    <div class=\"cookie-pref-info\"><h3>Performance &amp; Analytics</h3><p>Helps us understand usage patterns and fix issues faster.</p></div>
                    <button class=\"cookie-toggle on\" aria-label=\"Toggle performance cookies\"></button>
                </div>
                <div class=\"cookie-pref\">
                    <div class=\"cookie-pref-info\"><h3>Functional</h3><p>Remembers your preferences across visits.</p></div>
                    <button class=\"cookie-toggle on\" aria-label=\"Toggle functional cookies\"></button>
                </div>
                <div class=\"cookie-pref\">
                    <div class=\"cookie-pref-info\"><h3>Marketing</h3><p>Measures the performance of our own campaigns only.</p></div>
                    <button class=\"cookie-toggle\" aria-label=\"Toggle marketing cookies\"></button>
                </div>
                <p style=\"margin-top:16px;font-size:0.82rem;color:var(--text-muted);\">These toggles are illustrative — connect them to your consent-management platform to actually persist a visitor's choice.</p>
            "],
            ['id' => 'third-party', 'title' => 'Third-Party Cookies', 'html' => "
                <p>Some pages may set cookies from trusted service providers who help us run analytics or support live chat. These providers only receive the data necessary to perform their function and are contractually restricted from using it for their own purposes. We do not permit third-party advertising or cross-site tracking cookies on our site.</p>
            "],
            ['id' => 'browser-controls', 'title' => 'Browser Controls & Do Not Track', 'html' => "
                <p>Most browsers let you block or delete cookies through their settings. Note that blocking strictly necessary cookies will prevent you from logging in or completing payments on our platform. We currently do not respond to \"Do Not Track\" browser signals, as there is no common industry standard for interpreting them, but the toggles above give you direct control over non-essential categories.</p>
            "],
            ['id' => 'changes', 'title' => 'Changes to This Policy', 'html' => "
                <p>We may update this Cookie Policy as our use of cookies changes. Material updates will be reflected in the \"Last updated\" date above and, where required, we'll prompt you to review your preferences again.</p>
            "],
            ['id' => 'contact', 'title' => 'Contact Us', 'html' => "
                <p>Questions about our use of cookies can be sent to <a class=\"inline-link\" href=\"mailto:{$legalEmail}\">{$legalEmail}</a>.</p>
            "],
        ],
    ],
];

$page = $legalPages[$legal_slug] ?? null;

if ($page === null) {
    http_response_code(404);
    $page_meta = ['title' => 'Page Not Found | Paynancial'];
    ?>
    <section style="padding:96px 0;text-align:center;">
      <div class="container"><h1>We couldn't find that policy.</h1><a href="/" class="btn btn-primary" style="margin-top:24px;">Back to Home</a></div>
    </section>
    <?php
    return;
}

$page_meta = [
    'title' => $page['title'] . ' | Paynancial',
    'description' => $page['title'] . ' for ' . $legalName . '.',
];

$relatedLinks = [
    'privacy-policy'    => ['label' => 'Privacy Policy', 'href' => '/legal/privacy-policy'],
    'terms-conditions'  => ['label' => 'Terms & Conditions', 'href' => '/legal/terms-conditions'],
    'refund-policy'     => ['label' => 'Refund Policy', 'href' => '/legal/refund-policy'],
    'cookie-policy'     => ['label' => 'Cookie Policy', 'href' => '/legal/cookie-policy'],
];
$securityLink = ['label' => 'Security', 'href' => '/security'];

$sectionCount = count($page['sections']);
?>
<section class="hero" style="padding-top:56px;">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Legal</span>
      <h1><?= e($page['title']) ?></h1>
      <p class="lead"><?= e($page['hero_sub']) ?></p>
    </div>
  </div>
</section>

<section class="page-sec">
  <div class="container legal-wrap">
    <div class="legal-grid">

      <nav class="legal-toc" aria-label="Table of contents">
        <span class="legal-toc-label">On this page</span>
        <ol>
          <?php foreach ($page['sections'] as $i => $sec): ?>
            <li><a href="#<?= e($sec['id']) ?>"><span class="n"><?= sprintf('%02d', $i + 1) ?></span><?= e($sec['title']) ?></a></li>
          <?php endforeach; ?>
        </ol>
      </nav>

      <article class="legal-article">
        <div class="legal-meta">
          <span>Effective date: <strong><?= e($page['effective']) ?></strong></span>
          <span>Last updated: <strong><?= e($page['updated']) ?></strong></span>
          <span>Entity: <strong><?= e($legalName) ?></strong></span>
        </div>

        <p class="legal-intro"><?= $page['intro'] ?></p>

        <?php foreach ($page['sections'] as $i => $sec): ?>
          <div class="legal-section" id="<?= e($sec['id']) ?>">
            <span class="sec-num"><?= sprintf('%02d', $i + 1) ?> / <?= sprintf('%02d', $sectionCount) ?></span>
            <h2><?= e($sec['title']) ?></h2>
            <div class="legal-body"><?= $sec['html'] ?></div>
          </div>
        <?php endforeach; ?>

        <div class="legal-section" id="entity-details">
          <table class="legal-table">
            <tr><th>Entity</th><td><?= e($legalName) ?></td></tr>
            <tr><th>GST No.</th><td class="mono"><?= e($legalGst) ?></td></tr>
            <tr><th>Email</th><td><?= e($legalEmail) ?></td></tr>
          </table>
        </div>

        <div class="legal-related">
          <h4>Related policies</h4>
          <div class="legal-related-links">
            <?php foreach ($relatedLinks as $slug => $link): ?>
              <a href="<?= e($link['href']) ?>" class="<?= $slug === $legal_slug ? 'current' : '' ?>"><?= e($link['label']) ?></a>
            <?php endforeach; ?>
            <a href="<?= e($securityLink['href']) ?>"><?= e($securityLink['label']) ?></a>
          </div>
        </div>

      </article>
    </div>
  </div>
</section>
