<?php
/**
 * TEMPLATE — regulatory article (Regulatory Insights). NOT loaded by the site.
 *
 * Copy to includes/blog/articles/{slug}.php only once the business has
 * supplied the official source document (PDF or source text, or an official
 * URL that can be accessed). Write ONLY from that document:
 *   - never reconstruct circular/notification numbers, dates, requirements,
 *     provisions, effective dates, penalties, thresholds or obligations from
 *     memory or general knowledge;
 *   - if the source does not contain a fact, leave it out and flag it;
 *   - do not reproduce the circular verbatim — explain it in original
 *     language that preserves its legal meaning;
 *   - wrap restatements of what the regulator requires in
 *     <div class="blog-official">…</div>; everything else is Paynancial's
 *     explanation. Never individual legal, tax or financial advice.
 *
 * The article is not live until workflow = 'published', status is at least
 * 'published', and every source field is filled with status 'verified'.
 * It is not indexable until status = 'indexable' with approved_by/approved_on
 * recorded (see includes/blog.php and docs/blog-publishing.md).
 */
return [
    'slug'        => 'REPLACE-with-search-intent-slug',
    'category'    => 'regulatory',
    'type'        => 'regulatory',
    'status'      => 'draft',                       // draft → in_review → approved → published → indexable
    'workflow'    => 'source_verification_pending', // see blog_regulatory_workflow()
    'created'     => 'YYYY-MM-DD',
    'updated'     => 'YYYY-MM-DD',
    'editor'      => null,
    'approved_by' => null,
    'approved_on' => null,
    'indexable'   => false,
    'sitemap'     => false,

    // ARTICLE SOURCE RECORD — every field comes from the supplied official document.
    'source' => [
        'regulator'       => '',   // e.g. the issuing regulator, as named in the document
        'instrument_type' => '',   // Circular / Notification / Master Direction / Guidelines …
        'number'          => '',   // exactly as printed on the document
        'title'           => '',
        'issue_date'      => '',   // YYYY-MM-DD, from the document
        'effective_date'  => '',   // as stated in the document; "Not stated in the source" if absent
        'official_url'    => '',   // official regulator URL, supplied or verified
        'source_document' => '',   // file name / reference of the document supplied
        'last_verified'   => '',   // YYYY-MM-DD
        'reviewer'        => '',   // who verified the article against the source
        'status'          => 'pending', // 'verified' only after source verification
    ],

    'title'       => '',  // unique H1, genuine search intent, no keyword stuffing
    'meta_title'  => '',
    'description' => '',
    'dek'         => '',
    'question'    => '',  // e.g. "What does this circular mean for businesses?"
    'answer'      => '',  // concise direct answer (AEO), from the source
    'takeaways'   => [],

    // The ten required sections, in this order.
    'sections' => [
        ['what-changed', 'What changed?', ''],
        ['who-is-affected', 'Who does it affect?', ''],
        ['what-regulator-says', 'What does the regulator say?', ''],   // use .blog-official blocks
        ['in-practice', 'What does it mean in practical terms?', ''],
        ['for-businesses', 'What should businesses do?', ''],
        ['for-customers', 'What should customers know?', ''],
        ['paynancial-relevance', 'Paynancial relevance', ''],           // only confirmed Paynancial capabilities
        ['important-dates', 'Important dates', ''],                     // only dates in the source
        ['official-source', 'Official source', ''],
    ],
    'faqs'    => [],  // section 9: Frequently Asked Questions (question → direct answer)
    'related' => [],
    'links'   => [],
];
