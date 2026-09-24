<?php
/**
 * Blog publishing gate test: php tests/blog-gate-test.php
 * Checks the article-level rules in includes/blog.php and their effect on
 * the publishing gate (content-governance.php).
 */
declare(strict_types=1);
require __DIR__ . '/../includes/content-governance.php';
require_once __DIR__ . '/../includes/blog.php';

$fail = 0;
$check = function (string $label, bool $ok) use (&$fail) { echo ($ok ? 'PASS ' : 'FAIL ') . $label . "\n"; $fail += $ok ? 0 : 1; };

$base = ['type' => 'general', 'status' => 'in_review', 'indexable' => false, 'sitemap' => false, 'approved_by' => null, 'approved_on' => null];

// Status rules.
$check('draft article is not live', !blog_is_live(['status' => 'draft'] + $base));
$check('in-review article is live', blog_is_live($base));

// Regulatory articles: never live without a verified, complete source record.
$reg = ['type' => 'regulatory', 'status' => 'published', 'workflow' => 'published'] + $base;
$check('regulatory article without source is not live', !blog_is_live($reg));
$source = array_fill_keys(array_keys(blog_source_fields()), 'x');
$check('regulatory article with unverified source is not live', !blog_is_live($reg + ['source' => ['status' => 'pending'] + $source]));
$verified = $reg + ['source' => ['status' => 'verified'] + $source];
$check('regulatory article with verified source + published workflow is live', blog_is_live($verified));
$check('regulatory article with verified source but workflow not published is not live', !blog_is_live(['workflow' => 'regulatory_review'] + $verified));
$missing = $verified; unset($missing['source']['number']);
$check('regulatory article missing a circular number is not live', !blog_is_live($missing));

// Every current article, the hub and the category pages are noindex and out of the sitemap.
$all = blog_all();
$check('at least 14 articles exist', count($all) >= 14);
// Every indexable / sitemap article carries its approval record and status.
foreach ($all as $slug => $a) {
    if (gov_indexable(blog_url($slug)) || gov_in_sitemap(blog_url($slug))) {
        $check("indexed article '$slug' has status indexable + approval record",
            $a['status'] === 'indexable' && $a['indexable'] === true && !empty($a['approved_by']) && !empty($a['approved_on']));
    }
}
$check('/blog indexable only with an approval record', !gov_indexable('/blog') || gov_approved(gov_item('/blog')));
$check('Regulatory Insights topic page stays noindex without verified articles', !gov_indexable(blog_category_url('regulatory')));
foreach (array_keys(blog_categories()) as $c) {
    if (gov_indexable(blog_category_url($c))) {
        $check("indexed topic '$c' has at least one indexable article",
            (bool) array_filter($all, fn ($a) => $a['category'] === $c && gov_indexable(blog_url($a['slug']))));
    }
}

// Approval record is required: flipping flags alone must not index an article.
$flagsOnly = ['status' => 'indexable', 'indexable' => true, 'sitemap' => true] + $base;
$check('status + flags without an approval record do not satisfy gov_approved', !gov_approved($flagsOnly));

// General articles make no regulatory claims: no circular numbers or regulator directives in their text.
foreach ($all as $slug => $a) {
    if ($a['type'] !== 'general') continue;
    $text = implode(' ', array_map(fn ($s) => strip_tags($s[2]), $a['sections']));
    $check("general article '$slug' cites no circular numbers", !preg_match('~\b(RBI|NPCI|SEBI)/\d{4}~', $text));
}

echo $fail ? "\n$fail check(s) failed.\n" : "\nAll checks passed.\n";
exit($fail ? 1 : 0);
