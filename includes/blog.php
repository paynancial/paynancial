<?php
/**
 * Paynancial Blog / Insights — article registry, statuses and the
 * article-level publishing gate (decisions of 23 Sep 2026).
 *
 * Articles are version-controlled content files in includes/blog/articles/
 * (one file per article, returning an array). Changing an article's status
 * is a reviewed code change, like every other governed page.
 *
 * CMS status (every article):
 *   draft → in_review → approved → published → indexable
 *   - draft:      not live.
 *   - in_review:  live at its URL, noindex, not in the sitemap.
 *   - approved / published: live, still noindex.
 *   - indexable:  indexable only with approved_by + approved_on recorded
 *                 and indexable = true; enters the sitemap only when
 *                 sitemap = true as well. Never automatic on publish.
 *
 * Regulatory articles (type = 'regulatory') additionally follow the
 * source-first workflow and carry a source record. They are written only
 * from an official document the business supplies — never from memory —
 * and are not live at all until the workflow reaches 'published' with a
 * complete, verified source record.
 *
 * /blog itself and the category pages stay noindex (content-governance.php)
 * until a separate blog-level quality review approves them.
 */

declare(strict_types=1);

/** CMS statuses, in order. */
function blog_statuses(): array
{
    return ['draft' => 'Draft', 'in_review' => 'In review', 'approved' => 'Approved', 'published' => 'Published', 'indexable' => 'Indexable'];
}

/** Source-first workflow for regulatory articles, in order. */
function blog_regulatory_workflow(): array
{
    return [
        'source_verification_pending' => 'Source verification pending',
        'source_uploaded'     => 'Source uploaded',
        'source_extraction'   => 'Source extraction',
        'source_verification' => 'Source verification',
        'article_draft'       => 'Article draft',
        'editorial_review'    => 'Editorial review',
        'regulatory_review'   => 'Regulatory review',
        'seo_aeo_review'      => 'SEO / AEO review',
        'approved'            => 'Approved',
        'published'           => 'Published',
    ];
}

/** Every field a regulatory article's source record must carry before it can go live. */
function blog_source_fields(): array
{
    return [
        'regulator' => 'Regulator', 'instrument_type' => 'Instrument type', 'number' => 'Circular / notification number',
        'title' => 'Title', 'issue_date' => 'Issue date', 'effective_date' => 'Effective date',
        'official_url' => 'Official source', 'source_document' => 'Source document',
        'last_verified' => 'Last verified', 'reviewer' => 'Reviewer', 'status' => 'Status',
    ];
}

function blog_categories(): array
{
    return [
        'payments'         => ['Payments', 'How online payments work — methods, checkout, failures, refunds and settlement.'],
        'business-finance' => ['Business Finance', 'Practical guides to cash flow, collections, reconciliation and payouts for finance teams.'],
        'fintech-ai'       => ['Fintech & AI', 'Where financial technology and AI are heading, and how to adopt them with control.'],
        'developers'       => ['Developer Education', 'Building reliable payment integrations: webhooks, idempotency, testing and go-live.'],
        'learning'         => ['Learning & Development', 'Foundations for teams new to payments and finance operations.'],
        'regulatory'       => ['Regulatory Insights', 'Plain-language explainers of financial regulators\' circulars and notifications, written only from the official source.'],
    ];
}

/** All article records, keyed by slug (drafts included). */
function blog_all(): array
{
    static $all = null;
    if ($all !== null) {
        return $all;
    }
    $all = [];
    foreach (glob(__DIR__ . '/blog/articles/*.php') ?: [] as $file) {
        $a = require $file;
        if (is_array($a) && !empty($a['slug'])) {
            $a += ['type' => 'general', 'status' => 'draft', 'indexable' => false, 'sitemap' => false,
                'approved_by' => null, 'approved_on' => null, 'editor' => null, 'related' => [], 'links' => [], 'faqs' => []];
            $all[$a['slug']] = $a;
        }
    }
    return $all;
}

function blog_status_reached(array $a, string $status): bool
{
    $order = array_flip(array_keys(blog_statuses()));
    return ($order[$a['status']] ?? -1) >= $order[$status];
}

/** Source fields still missing from a regulatory article's record. */
function blog_source_missing(array $a): array
{
    $src = $a['source'] ?? [];
    return array_values(array_filter(array_keys(blog_source_fields()), fn ($k) => empty($src[$k])));
}

/** A regulatory article's source is verified only with every field present and status 'verified'. */
function blog_source_verified(array $a): bool
{
    return !blog_source_missing($a) && ($a['source']['status'] ?? '') === 'verified';
}

/** Is the article reachable at its URL? */
function blog_is_live(array $a): bool
{
    if (!blog_status_reached($a, 'in_review')) {
        return false;
    }
    if ($a['type'] === 'regulatory') {
        return ($a['workflow'] ?? '') === 'published' && blog_status_reached($a, 'published') && blog_source_verified($a);
    }
    return true;
}

/** Live articles, newest first. Optional category filter. */
function blog_live(?string $category = null): array
{
    $list = array_filter(blog_all(), fn ($a) => blog_is_live($a) && ($category === null || $a['category'] === $category));
    uasort($list, fn ($x, $y) => [$y['updated'], $x['title']] <=> [$x['updated'], $y['title']]);
    return $list;
}

function blog_article(string $slug): ?array
{
    $a = blog_all()[$slug] ?? null;
    return ($a !== null && blog_is_live($a)) ? $a : null;
}

function blog_url(string $slug): string
{
    return '/blog/' . $slug;
}

function blog_category_url(string $cat): string
{
    return '/blog/category/' . $cat;
}

/** Categories that have a page: those with live articles, plus Regulatory Insights (explains the source-first policy). */
function blog_category_pages(): array
{
    return array_filter(blog_categories(), fn ($c, $slug) => $slug === 'regulatory' || blog_live($slug), ARRAY_FILTER_USE_BOTH);
}

function blog_words(array $a): int
{
    $text = $a['answer'] . ' ' . implode(' ', $a['takeaways'] ?? []);
    foreach ($a['sections'] as $s) {
        $text .= ' ' . strip_tags($s[2]);
    }
    foreach ($a['faqs'] as [$q, $ans]) {
        $text .= ' ' . $q . ' ' . $ans;
    }
    return str_word_count(html_entity_decode($text));
}

function blog_reading_minutes(array $a): int
{
    return max(1, (int) round(blog_words($a) / 220));
}

function blog_date(string $ymd): string
{
    return date('j M Y', strtotime($ymd));
}

/**
 * Publishing-gate records for content-governance.php, so articles and
 * category pages use the same indexable / sitemap rules as every other
 * governed page.
 */
function blog_gov_items(): array
{
    $items = [];
    $stageFor = [
        'draft' => 'draft', 'in_review' => 'content_review', 'approved' => 'seo_review',
        'published' => 'seo_review', 'indexable' => 'indexable',
    ];
    foreach (blog_all() as $slug => $a) {
        $regulatory = $a['type'] === 'regulatory';
        $verified = !$regulatory || blog_source_verified($a);
        $stage = $stageFor[$a['status']] ?? 'draft';
        if ($stage === 'indexable' && $a['sitemap'] === true) {
            $stage = 'sitemap';
        }
        if (!$verified) {
            $stage = 'source_verification';
        }
        $items[blog_url($slug)] = [
            'stage' => $stage,
            'indexable' => $verified && $a['status'] === 'indexable' && $a['indexable'] === true,
            'sitemap' => $a['sitemap'] === true,
            'service_promotion' => false,
            'professional_review' => $regulatory ? GOV_PROFESSIONAL_REVIEW : 'not_applicable',
            'approved_by' => $a['approved_by'],
            'approved_on' => $a['approved_on'],
            'reason' => ($regulatory ? 'Regulatory article — ' . ($verified ? 'source verified' : 'source verification pending') . '. ' : 'Blog article. ')
                . 'CMS status: ' . (blog_statuses()[$a['status']] ?? $a['status']) . '. Indexable only after editorial, SEO/AEO and indexing approval.',
        ];
    }
    // Topic pages approved in the blog-level review (24 Sep 2026). A topic is
    // indexable only while it has at least one indexable article; Regulatory
    // Insights is not approved (no articles yet).
    $approvedTopics = ['payments', 'business-finance', 'fintech-ai', 'developers', 'learning'];
    foreach (array_keys(blog_categories()) as $cat) {
        $hasIndexable = (bool) array_filter(blog_all(), fn ($a) => $a['category'] === $cat && blog_is_live($a)
            && $a['status'] === 'indexable' && $a['indexable'] === true && !empty($a['approved_by']));
        $approved = in_array($cat, $approvedTopics, true) && $hasIndexable;
        $items[blog_category_url($cat)] = [
            'stage' => $approved ? 'sitemap' : 'content_review', 'indexable' => $approved, 'sitemap' => $approved,
            'service_promotion' => false, 'professional_review' => 'not_applicable',
            'approved_by' => $approved ? 'Paynancial Editorial Team' : null, 'approved_on' => $approved ? '2026-09-24' : null,
            'reason' => $approved ? 'Blog topic page — approved in the blog-level review.' : 'Blog topic page — noindex (not approved, or no indexable articles).',
        ];
    }
    return $items;
}
