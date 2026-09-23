<?php
/**
 * Regulatory context — the RBI and NPCI frameworks (and the two Acts) that
 * apply to payments in India, in plain language, for the "Regulatory
 * context" band on product, capability and pillar pages.
 *
 * Content rules:
 * - General information about the frameworks, not legal advice and not a
 *   statement of Paynancial's own authorisations. PCI DSS and RBI Payment
 *   Aggregator authorisation are "Verify" in the Trust Center, so nothing
 *   here says Paynancial holds them or complies with a given circular.
 * - No circular numbers, dates, thresholds or timelines: those change by
 *   circular, so each entry names the framework and the page links to the
 *   regulators' own sites for the current text.
 * - Have a compliance professional review this file before relying on it.
 */

declare(strict_types=1);

const REG_RBI_URL  = 'https://www.rbi.org.in/';
const REG_NPCI_URL = 'https://www.npci.org.in/';

/** id => [name, regulator, what it covers]. */
function reg_items(): array
{
    return [
        'pss-act' => ['Payment and Settlement Systems Act, 2007', 'Act of Parliament · RBI', 'The law under which the Reserve Bank of India regulates and supervises payment systems in India. Operating a payment system requires RBI authorisation under this Act.'],
        'pa-pg' => ['Payment Aggregator and Payment Gateway guidelines', 'RBI', 'RBI\'s framework for payment aggregators — businesses that collect payments on behalf of merchants — covering authorisation, merchant due diligence, how merchant funds are held and settled, and grievance handling. Payment gateways are treated as technology providers.'],
        'pa-cb' => ['Cross-border Payment Aggregator (PA-CB) framework', 'RBI', 'Extends payment aggregator regulation to cross-border payments for the import and export of goods and services.'],
        'fema' => ['Foreign Exchange Management Act, 1999 (FEMA)', 'Act of Parliament · RBI', 'Governs foreign exchange transactions in India. Cross-border payments are generally routed through banks authorised by the RBI to deal in foreign exchange, with the purpose of each remittance documented.'],
        'tat' => ['Turn Around Time (TAT) for failed transactions', 'RBI', 'Sets the time within which failed digital transactions — for example, a customer debited without the merchant being credited — must be reversed or resolved across authorised payment systems such as UPI, cards and IMPS, and the compensation due to the customer when that time is missed.'],
        'odr' => ['Online Dispute Resolution (ODR) for digital payments', 'RBI', 'Requires authorised payment system operators and participants to offer customers an online, rule-based way to raise and resolve disputes and grievances about digital payments.'],
        'udir' => ['UPI Online Dispute Resolution (UDIR)', 'NPCI', 'NPCI\'s mechanism for customers to raise and track UPI complaints — such as a payment debited but not credited — from within their UPI app.'],
        'upi' => ['UPI procedural guidelines and circulars', 'NPCI', 'NPCI operates UPI and sets its rules for member banks, UPI apps and other participants through procedural guidelines and circulars, including transaction limits, dispute handling and security requirements.'],
        'upi-autopay' => ['UPI AutoPay', 'NPCI', 'Lets a customer set up, view, pause and cancel recurring UPI mandates in their UPI app, for subscriptions and other repeating payments.'],
        'emandate' => ['E-mandates for recurring payments', 'RBI', 'Recurring payments on cards, UPI and prepaid instruments need an e-mandate the customer registers with additional authentication, a notification before each debit, and the option to withdraw the mandate.'],
        'nach' => ['National Automated Clearing House (NACH)', 'NPCI', 'NPCI\'s system for high-volume, repetitive bank transfers — bulk credits such as salaries and dividends, and debits against mandates such as loan instalments.'],
        'imps' => ['Immediate Payment Service (IMPS)', 'NPCI', 'NPCI\'s round-the-clock instant interbank transfer service, to a bank account or mobile number.'],
        'tokenisation' => ['Card-on-File tokenisation', 'RBI', 'Merchants and payment aggregators may not store customers\' actual card numbers. A card saved for repeat payments is replaced by a token, created with the customer\'s consent.'],
        'security' => ['Digital Payment Security Controls', 'RBI', 'RBI\'s expectations for governance, secure application design, authentication and fraud-risk monitoring in digital payment products offered by regulated entities.'],
        'kyc' => ['Know Your Customer (KYC) Direction', 'RBI', 'Customer due diligence obligations for RBI-regulated entities. Payment aggregators apply due diligence to the merchants they onboard.'],
        'free-ai' => ['Responsible and ethical AI in the financial sector (FREE-AI)', 'RBI', 'An RBI framework for the responsible and ethical enablement of AI in financial services, centred on governance, accountability, fairness, transparency, consumer protection and managing AI risk.'],
        'fraud-rm' => ['Fraud risk management directions', 'RBI', 'RBI directions requiring regulated entities to run fraud risk management — early warning signals, monitoring, reporting and board oversight — across their products, including digital payments.'],
        'ombudsman' => ['Reserve Bank – Integrated Ombudsman Scheme', 'RBI', 'Customers of RBI-regulated entities can escalate a complaint that is not resolved satisfactorily to the RBI Ombudsman, online at cms.rbi.org.in.'],
        'dpdp' => ['Digital Personal Data Protection Act, 2023', 'Act of Parliament', 'India\'s law on processing digital personal data — including the personal data in payment records — based on consent, purpose limitation and security safeguards.'],
    ];
}

/** Page key => regulatory item ids, most relevant first. */
function reg_page_map(): array
{
    $payouts = ['pss-act', 'imps', 'nach', 'upi', 'tat', 'udir'];
    return [
        'product:payment-gateway'   => ['pa-pg', 'tokenisation', 'security', 'tat', 'odr', 'dpdp'],
        'product:payment-links'     => ['pa-pg', 'upi', 'tat', 'odr'],
        'product:payment-collection'=> ['emandate', 'upi-autopay', 'nach', 'pa-pg'],
        'product:payouts'           => $payouts,
        'product:payment-analytics' => ['pa-pg', 'tat', 'dpdp'],
        'bulk-payouts'              => ['nach', 'imps', 'upi', 'tat'],
        'vendor-payments'           => $payouts,
        'employee-payments'         => ['nach', 'imps', 'upi', 'tat', 'dpdp'],
        'partner-payments'          => $payouts,
        'international-payments'    => ['fema', 'pa-cb', 'pss-act', 'kyc'],
        'refunds'                   => ['tat', 'odr', 'udir', 'pa-pg'],
        'settlements'               => ['pa-pg', 'pss-act', 'tat'],
        'reconciliation'            => ['tat', 'pa-pg', 'odr'],
        'chargebacks'               => ['odr', 'udir', 'tat', 'pa-pg'],
        'invoice-management'        => ['pa-pg', 'emandate', 'upi-autopay'],
        'expense-management'        => ['pss-act', 'upi', 'imps', 'dpdp'],
        'mis-reports'               => ['dpdp', 'pa-pg', 'tat'],
        'upi-payments'              => ['upi', 'udir', 'upi-autopay', 'tat'],
        'pillar:pay-and-move-money' => ['pss-act', 'upi', 'imps', 'nach', 'tat', 'fema'],
        'pillar:financial-operations' => ['pa-pg', 'tat', 'odr', 'udir', 'dpdp'],
        'pillar:embedded-finance'   => ['pss-act', 'pa-pg', 'kyc', 'tokenisation', 'dpdp'],
        'ai:hub'                    => ['free-ai', 'fraud-rm', 'security', 'dpdp', 'odr', 'ombudsman'],
        'ai:paynancial-ai'          => ['free-ai', 'security', 'fraud-rm', 'dpdp'],
        'ai:fraud-detection'        => ['fraud-rm', 'security', 'udir', 'tokenisation', 'dpdp'],
        'ai:reconciliation'         => ['tat', 'pa-pg', 'free-ai', 'dpdp'],
        'ai:financial-assistant'    => ['odr', 'ombudsman', 'udir', 'free-ai', 'dpdp'],
        'ai:cash-flow-intelligence' => ['free-ai', 'pa-pg', 'dpdp'],
        'ai:revenue-forecasting'    => ['free-ai', 'dpdp'],
        'pillar:accept-and-collect' => ['pa-pg', 'tokenisation', 'emandate', 'upi', 'tat'],
    ];
}

/**
 * Full-width "Regulatory context" band. $key is a reg_page_map() key; $tone
 * the band tone. Renders nothing for an unmapped key.
 */
function sp_regulatory(string $key, string $subject, string $tone = 'ink'): void
{
    $ids = reg_page_map()[$key] ?? [];
    if (!$ids) {
        return;
    }
    $items = reg_items();
    sp_band_open('regulatory', $tone);
    sp_head('regulatory', 'Compliance in India', 'The RBI and NPCI rules around ' . $subject . '.', 'A plain-language guide to the frameworks that apply. It is general information, not legal advice, and not a statement of Paynancial\'s own licences or authorisations.');
    echo '<div class="sp-reg">';
    foreach ($ids as $id) {
        [$name, $by, $what] = $items[$id];
        echo '<article class="sp-reg-item reveal"><span class="sp-reg-by">' . e($by) . '</span><h3>' . e($name) . '</h3><p>' . e($what) . '</p></article>';
    }
    echo '</div>';
    echo '<p class="sp-reg-foot reveal">Regulators update these rules by circular, so always check the current text at the source: '
        . '<a class="inline-link" href="' . REG_RBI_URL . '" rel="noopener" target="_blank">Reserve Bank of India</a> · '
        . '<a class="inline-link" href="' . REG_NPCI_URL . '" rel="noopener" target="_blank">NPCI</a>. '
        . 'For Paynancial\'s own security and compliance posture, see the <a class="inline-link" href="/trust">Trust Center</a>.</p>';
    sp_band_close();
}
