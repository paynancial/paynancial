<?php
/**
 * Regulatory references — DRAFT / SOURCE VERIFICATION PENDING.
 *
 * Approved decision (23 Sep 2026): unsourced regulatory content is removed
 * from public pages until verified. The summaries below were drafted from
 * general knowledge, not from official sources, so every reference has
 * status 'source_verification_pending' and none is rendered.
 *
 * A reference becomes public only when reg_publishable() is true, i.e. it
 * has ALL of: official source, source URL, reference / circular / rule
 * number, issue date, effective date (or 'not applicable'), applicability,
 * last-verified date, reviewer status — and status 'verified'. Verification
 * means the official source was actually accessed and checked; it is never
 * inferred. Professional review stays 'pending' until a qualified
 * professional has reviewed the content (no reviewer is named anywhere).
 */

declare(strict_types=1);

const REG_RBI_URL  = 'https://www.rbi.org.in/';
const REG_NPCI_URL = 'https://www.npci.org.in/';

/**
 * Publishing workflow for a regulatory reference, in order. A reference is
 * shown publicly only at 'published', with every required field present
 * and status 'verified'.
 */
function reg_workflow(): array
{
    return [
        'draft'                   => 'Draft',
        'source_verification'     => 'Source verification',
        'editorial_review'        => 'Editorial review',
        'seo_aeo_review'          => 'SEO / AEO review',
        'legal_regulatory_review' => 'Legal / regulatory review (where required)',
        'approval'                => 'Approval',
        'published'               => 'Published',
    ];
}

/** Draft summaries: id => [title, authority, draft summary]. Not public. */
function reg_items(): array
{
    return [
        'pss-act' => ['Payment and Settlement Systems Act, 2007', 'Act of Parliament · RBI', 'The law under which the Reserve Bank of India regulates and supervises payment systems in India, including the authorisation of payment system operators. Businesses should consider the applicable requirements under this Act.'],
        'pa-pg' => ['Payment Aggregator and Payment Gateway guidelines', 'RBI', 'RBI\'s framework for payment aggregators — businesses that collect payments on behalf of merchants — covering authorisation, merchant due diligence, how merchant funds are held and settled, and grievance handling.'],
        'pa-cb' => ['Cross-border Payment Aggregator (PA-CB) framework', 'RBI', 'Extends payment aggregator regulation to cross-border payments for the import and export of goods and services.'],
        'fema' => ['Foreign Exchange Management Act, 1999 (FEMA)', 'Act of Parliament · RBI', 'Governs foreign exchange transactions in India. Cross-border payments are generally routed through banks authorised by the RBI to deal in foreign exchange, which usually ask for the purpose of a remittance.'],
        'tat' => ['Turn Around Time (TAT) for failed transactions', 'RBI', 'Covers the time within which failed digital transactions — for example, a customer debited without the merchant being credited — are expected to be reversed or resolved across authorised payment systems such as UPI, cards and IMPS, and compensation to the customer when that time is missed.'],
        'odr' => ['Online Dispute Resolution (ODR) for digital payments', 'RBI', 'Covers how authorised payment system operators and participants offer customers an online, rule-based way to raise and resolve disputes and grievances about digital payments.'],
        'udir' => ['UPI Online Dispute Resolution (UDIR)', 'NPCI', 'NPCI\'s mechanism for customers to raise and track UPI complaints — such as a payment debited but not credited — from within their UPI app.'],
        'upi' => ['UPI procedural guidelines and circulars', 'NPCI', 'NPCI operates UPI and sets its rules for member banks, UPI apps and other participants through procedural guidelines and circulars, including transaction limits, dispute handling and security requirements.'],
        'upi-autopay' => ['UPI AutoPay', 'NPCI', 'Lets a customer set up, view, pause and cancel recurring UPI mandates in their UPI app, for subscriptions and other repeating payments.'],
        'emandate' => ['E-mandates for recurring payments', 'RBI', 'Covers e-mandates for recurring payments on cards, UPI and prepaid instruments, including registration by the customer with additional authentication, a notification before each debit, and the option to withdraw the mandate.'],
        'nach' => ['National Automated Clearing House (NACH)', 'NPCI', 'NPCI\'s system for high-volume, repetitive bank transfers — bulk credits such as salaries and dividends, and debits against mandates such as loan instalments.'],
        'imps' => ['Immediate Payment Service (IMPS)', 'NPCI', 'NPCI\'s round-the-clock instant interbank transfer service, to a bank account or mobile number.'],
        'tokenisation' => ['Card-on-File tokenisation', 'RBI', 'Covers card-on-file tokenisation: a card saved for repeat payments is replaced by a token, created with the customer\'s consent, instead of the actual card number being stored.'],
        'security' => ['Digital Payment Security Controls', 'RBI', 'RBI\'s expectations for governance, secure application design, authentication and fraud-risk monitoring in digital payment products offered by regulated entities.'],
        'kyc' => ['Know Your Customer (KYC) Direction', 'RBI', 'Customer due diligence obligations for RBI-regulated entities. Payment aggregators apply due diligence to the merchants they onboard.'],
        'ppi' => ['Prepaid Payment Instruments (PPI) Master Direction', 'RBI', 'RBI\'s rules for wallets and prepaid cards: who may issue them, KYC levels and the limits that go with them, how balances are loaded and used, and interoperability.'],
        'free-ai' => ['Responsible and ethical AI in the financial sector (FREE-AI)', 'RBI', 'An RBI framework for the responsible and ethical enablement of AI in financial services, centred on governance, accountability, fairness, transparency, consumer protection and managing AI risk.'],
        'fraud-rm' => ['Fraud risk management directions', 'RBI', 'RBI directions on fraud risk management for regulated entities — early warning signals, monitoring, reporting and board oversight — across their products, including digital payments.'],
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
        'embedded-payments'         => ['pa-pg', 'kyc', 'tokenisation', 'security', 'dpdp'],
        'embedded-payouts'          => ['pss-act', 'imps', 'upi', 'nach', 'tat'],
        'embedded-billing'          => ['emandate', 'upi-autopay', 'nach', 'pa-pg'],
        'wallet-infrastructure'     => ['ppi', 'pss-act', 'kyc', 'dpdp'],
        'split-payments'            => ['pa-pg', 'kyc', 'tat', 'odr'],
        'white-label-payments'      => ['pa-pg', 'pss-act', 'odr', 'ombudsman', 'dpdp'],
        'dev:payment-apis'          => ['pa-pg', 'tokenisation', 'emandate', 'tat', 'security'],
        'dev:payout-apis'           => ['pss-act', 'imps', 'upi', 'nach', 'tat'],
        'recurring-payments'        => ['emandate', 'upi-autopay', 'nach'],
        'subscription-billing'      => ['emandate', 'upi-autopay', 'odr'],
        'payment-pages'             => ['pa-pg', 'upi', 'tat', 'odr'],
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
 * Full reference records. Source fields are null until the official source
 * has been accessed and checked; fill them here (or, later, from the CMS
 * regulatory_references table — see database/content_governance_schema.sql).
 */
function reg_references(): array
{
    $verified = []; // id => ['official_source' => …, 'source_url' => …, 'reference' => …, 'issue_date' => …,
                    //        'effective_date' => …, 'applicability' => …, 'last_verified' => …, 'reviewer_status' => …, 'status' => 'verified']
    $out = [];
    foreach (reg_items() as $id => [$title, $authority, $summary]) {
        $out[$id] = ($verified[$id] ?? []) + [
            'title'           => $title,
            'authority'       => $authority,
            'summary'         => $summary,
            'regulator'       => $authority,
            'instrument'      => null,   // Act, Master Direction, circular, notification…
            'product'         => null,   // Paynancial products it applies to
            'service'         => null,   // Business Services it applies to
            'customer_impact' => null,
            'next_review'     => null,
            'reviewer'        => null,   // named only once someone has actually reviewed it
            'workflow'        => 'source_verification',
            'official_source' => null,
            'source_url'      => null,
            'reference'       => null,
            'issue_date'      => null,
            'effective_date'  => null,
            'applicability'   => null,
            'last_verified'   => null,
            'reviewer_status' => 'Professional review: Pending',
            'status'          => 'source_verification_pending',
        ];
    }
    return $out;
}

/** Fields a reference must have before it can be shown publicly. */
function reg_required_fields(): array
{
    return ['regulator', 'instrument', 'reference', 'title', 'official_source', 'source_url', 'issue_date', 'effective_date',
        'applicability', 'last_verified', 'next_review', 'reviewer', 'reviewer_status'];
}

/** Missing required fields for a reference (empty = complete). */
function reg_missing_fields(array $r): array
{
    return array_values(array_filter(reg_required_fields(), fn ($f) => empty($r[$f])));
}

function reg_publishable(array $r): bool
{
    return $r['status'] === 'verified' && ($r['workflow'] ?? '') === 'published' && reg_missing_fields($r) === [];
}

/**
 * Full-width "Regulatory context" band. $key is a reg_page_map() key; $tone
 * the band tone. Renders nothing for an unmapped key.
 */
function sp_regulatory(string $key, string $subject, string $tone = 'ink'): void
{
    $refs = reg_references();
    // Regulatory verification gate disabled by editorial decision (24 Sep
    // 2026): every mapped reference is shown as general information, never
    // labelled as verified and without source metadata.
    $ids = array_values(array_filter(reg_page_map()[$key] ?? [], fn ($id) => isset($refs[$id])));
    if (!$ids) {
        return;
    }
    sp_band_open('regulatory', $tone);
    sp_head('regulatory', 'Compliance in India', 'The RBI and NPCI rules around ' . $subject . '.', 'A plain-language guide to the frameworks that may apply. General information only. Not legal, tax, financial or regulatory advice. It is not a statement of Paynancial\'s own licences or authorisations.');
    echo '<div class="sp-reg">';
    foreach ($ids as $id) {
        $r = $refs[$id];
        // No source/verification metadata is rendered (regulatory verification gate disabled).
        echo '<article class="sp-reg-item reveal"><span class="sp-reg-by">' . e($r['authority']) . '</span><h3>' . e($r['title']) . '</h3><p>' . e($r['summary']) . '</p></article>';
    }
    echo '</div>';
    echo '<p class="sp-reg-foot reveal">Regulators update these rules by circular, so always check the current text at the source: '
        . '<a class="inline-link" href="' . REG_RBI_URL . '" rel="noopener" target="_blank">Reserve Bank of India</a> · '
        . '<a class="inline-link" href="' . REG_NPCI_URL . '" rel="noopener" target="_blank">NPCI</a>. '
        . 'For Paynancial\'s own security and compliance posture, see the <a class="inline-link" href="/trust">Trust Center</a>.</p>';
    sp_band_close();
}
