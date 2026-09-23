<?php
/**
 * Content governance — the publishing gate for pages whose claims are not
 * yet approved (approved decisions, 23 Sep 2026; docs/audit/master-audit.md).
 *
 * Publishing workflow, in order — no stage can be skipped:
 *   draft → source_verification → content_review → regulatory_review →
 *   business_approval → seo_review → indexable → sitemap → published
 *
 * A governed page is indexable only when ALL of these are true:
 *   - its stage has reached 'indexable';
 *   - its 'indexable' flag is true;
 *   - 'approved_by' and 'approved_on' record who approved it and when.
 * It enters the sitemap only when, in addition, its stage has reached
 * 'sitemap' and its 'sitemap' flag is true. So a page cannot become
 * indexable by flipping one flag: the approval record is required too.
 *
 * Pages not listed here are ordinary, already-approved pages.
 *
 * Professional review is 'pending' everywhere: no CA, CS or lawyer has
 * reviewed any content, so no page may say it was professionally reviewed.
 */

declare(strict_types=1);

function gov_stages(): array
{
    return ['draft', 'source_verification', 'content_review', 'regulatory_review',
        'business_approval', 'seo_review', 'indexable', 'sitemap', 'published'];
}

function gov_stage_labels(): array
{
    return [
        'draft' => 'Draft', 'source_verification' => 'Source verification', 'content_review' => 'Content review',
        'regulatory_review' => 'Regulatory review', 'business_approval' => 'Business service approval',
        'seo_review' => 'SEO / AEO review', 'indexable' => 'Indexable', 'sitemap' => 'Sitemap', 'published' => 'Published',
    ];
}

/** Professional review status for all regulatory / Business Services content. */
const GOV_PROFESSIONAL_REVIEW = 'pending';

/**
 * Governed pages: live, but not approved for search or promotion.
 * path => readiness record.
 */
function gov_content_items(): array
{
    $unconfirmed = static fn (string $what) => [
        'stage'             => 'business_approval',   // waiting for the business to confirm the capability
        'indexable'         => false,
        'sitemap'           => false,
        'service_promotion' => false,
        'professional_review' => GOV_PROFESSIONAL_REVIEW,
        'approved_by'       => null,
        'approved_on'       => null,
        'reason'            => $what . ' is not a confirmed Paynancial product capability.',
    ];
    $items = [];
    foreach ([
        'international-payments' => 'International Payments', 'invoice-management' => 'Invoice Management',
        'expense-management' => 'Expense Management', 'payment-pages' => 'A standalone payment page builder',
        'embedded-payments' => 'Embedded Payments', 'embedded-payouts' => 'Embedded Payouts',
        'embedded-billing' => 'Embedded Billing', 'wallet-infrastructure' => 'Wallet infrastructure',
        'split-payments' => 'Split payments', 'white-label-payments' => 'White-label payments',
    ] as $slug => $what) {
        $items['/products/' . $slug] = $unconfirmed($what);
    }
    foreach ([
        'paynancial-ai' => 'Paynancial AI', 'fraud-detection' => 'AI Fraud Detection', 'reconciliation' => 'AI Reconciliation',
        'financial-assistant' => 'AI Financial Assistant', 'cash-flow-intelligence' => 'AI Cash-Flow Intelligence',
        'revenue-forecasting' => 'AI Revenue Forecasting',
    ] as $slug => $what) {
        $items['/ai-intelligence/' . $slug] = $unconfirmed($what);
    }
    // Utility / placeholder pages: live and crawlable (not blocked in
    // robots.txt, so the noindex is seen), but not organic landing pages.
    $utility = static fn (string $reason) => [
        'stage' => 'content_review', 'indexable' => false, 'sitemap' => false, 'service_promotion' => false,
        'professional_review' => 'not_applicable', 'approved_by' => null, 'approved_on' => null, 'reason' => $reason,
    ];
    $items['/blog'] = $utility('Empty placeholder for the future Paynancial Insights / Regulatory Insights programme; indexable only once it has substantial original content and passes review.');
    $items['/signup'] = $utility('Account sign-up form — a utility page, not an organic landing page.');
    $items['/partner/register'] = $utility('Partner application form — a conversion endpoint. The search-visible page is /partner-program.');
    // Grievance Officer named by the business; the process wording awaits
    // qualified legal / regulatory review before the page is indexed.
    $items['/grievance-redressal'] = [
        'stage' => 'regulatory_review', 'indexable' => false, 'sitemap' => false, 'service_promotion' => false,
        'professional_review' => GOV_PROFESSIONAL_REVIEW, 'approved_by' => null, 'approved_on' => null,
        'reason' => 'Grievance Redressal page: officer name and contact email confirmed by the business; response timelines, escalation levels and regulatory routes not yet confirmed; legal review pending.',
    ];
    return $items;
}

/**
 * Legal documents under review. Content is held unchanged (no edits to the
 * RBI / KYC / DPDP wording) until a qualified legal and regulatory review is
 * completed; the pages stay live and indexable. No reviewer is named.
 */
function gov_review_items(): array
{
    $pending = ['legal_review' => 'pending', 'regulatory_review' => 'pending', 'reviewer' => null,
        'note' => 'Hold content changes. After review: verified wording, correct regulatory references, applicability, dates, sources and Paynancial-specific obligations.'];
    return [
        '/legal/privacy-policy'   => ['title' => 'Privacy Policy'] + ['note' => $pending['note'] . ' Also add: enquiry-form submissions (floating "Request a Callback") and anti-abuse processing by Cloudflare Turnstile — a short notice is shown on the form meanwhile.'] + $pending,
        '/legal/terms-conditions' => ['title' => 'Terms & Conditions'] + $pending,
    ];
}

function gov_item(string $path): ?array
{
    return gov_content_items()[$path] ?? null;
}

function gov_stage_reached(array $item, string $stage): bool
{
    $order = array_flip(gov_stages());
    return ($order[$item['stage']] ?? -1) >= $order[$stage];
}

function gov_approved(array $item): bool
{
    return !empty($item['approved_by']) && !empty($item['approved_on']);
}

/** May search engines index this path? Ungoverned paths: yes. */
function gov_indexable(string $path): bool
{
    $i = gov_item($path);
    if ($i === null) {
        return true;
    }
    return $i['indexable'] === true && gov_stage_reached($i, 'indexable') && gov_approved($i);
}

/** May this path be listed in the XML sitemap? */
function gov_in_sitemap(string $path): bool
{
    $i = gov_item($path);
    if ($i === null) {
        return true;
    }
    return gov_indexable($path) && $i['sitemap'] === true && gov_stage_reached($i, 'sitemap');
}

/** May this page promote the capability as a Paynancial service (Service schema, service CTAs)? */
function gov_service_promotion(string $path): bool
{
    $i = gov_item($path);
    if ($i === null) {
        return true;
    }
    return gov_indexable($path) && $i['service_promotion'] === true;
}

/**
 * "In India" context bands contain regulatory statements (RBI, NPCI, FEMA,
 * GST, TDS, tokenisation, e-mandates…) that have not been verified against
 * official sources. They stay in the page data as drafts and are not shown
 * publicly until source verification is complete.
 */
function gov_india_context_public(): bool
{
    return false;
}
