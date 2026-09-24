<?php
/**
 * Blog hub — /blog (Paynancial Insights).
 *
 * Live but noindex (content-governance.php) until a separate blog-level
 * quality review. Lists live articles only (includes/blog.php); regulatory
 * articles appear only once their official source is verified.
 */
require_once __DIR__ . '/../includes/blog-ui.php';

$path = '/blog';
$trail = [['Home', '/'], ['Blog', $path]];
$articles = blog_live();
$description = 'Paynancial Insights: practical guides to online payments, business finance, reconciliation, developer integration and financial technology, plus source-first regulatory explainers.';
$page_meta = [
    'title'       => 'Paynancial Insights | Payments, Business Finance & Fintech Guides',
    'description' => $description,
    'canonical'   => site_url('blog'),
    'extra_css'   => blog_css(),
    'schema'      => blog_listing_schema('Paynancial Insights', $description, $path, $trail, $articles),
];

// A live article marked 'featured' leads the page; otherwise the newest.
$featuredSlug = array_key_first(array_filter($articles, fn ($a) => !empty($a['featured']))) ?? array_key_first($articles);
$featured = $featuredSlug !== null ? $articles[$featuredSlug] : null;
unset($articles[$featuredSlug]);
$counts = [];
foreach (blog_live() as $a) {
    $counts[$a['category']] = ($counts[$a['category']] ?? 0) + 1;
}

blog_hero($trail, 'Paynancial Insights', 'Insights on payments, business finance and financial technology.',
    'Practical, plain-language guides for founders, finance teams and developers — how payments work, how to run collections and payouts well, and how to build reliable integrations.', null);
?>

<?php if ($featured): ?>
<section class="sp-band sp-band--paper" aria-labelledby="latest-title">
  <div class="sp-wrap">
    <h2 id="latest-title" class="sr-only">Latest articles</h2>
    <div class="blog-lead">
      <?php blog_card($featured, 'lg'); ?>
      <div class="blog-lead-side">
        <?php foreach (array_slice($articles, 0, 3, true) as $a) { blog_card($a, 'sm'); } ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="sp-band sp-band--dim" id="topics" aria-labelledby="topics-title">
  <div class="sp-wrap">
    <?php sp_head('topics', 'Browse by topic', 'Find guides for your role.'); ?>
    <div class="blog-topic-grid">
      <?php foreach (blog_category_pages() as $slug => [$label, $blurb]): ?>
      <a class="blog-topic reveal blog-art--<?= e($slug) ?>" href="<?= e(blog_category_url($slug)) ?>">
        <strong><?= e($label) ?></strong>
        <span><?= e($blurb) ?></span>
        <em><?= $slug === 'regulatory' && empty($counts[$slug]) ? 'Source-first · coming as sources are verified' : e(($counts[$slug] ?? 0) . ' article' . (($counts[$slug] ?? 0) === 1 ? '' : 's')) ?></em>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if (count($articles) > 3): ?>
<section class="sp-band sp-band--paper" id="all" aria-labelledby="all-title">
  <div class="sp-wrap">
    <?php sp_head('all', 'All articles', 'More from Paynancial Insights.'); ?>
    <div class="blog-grid">
      <?php foreach (array_slice($articles, 3, null, true) as $a) { blog_card($a); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="sp-band sp-band--ink" id="regulatory" aria-labelledby="regulatory-title">
  <div class="sp-wrap">
    <?php blog_regulatory_policy(); ?>
  </div>
</section>

<section class="sp-band sp-band--paper" id="standards" aria-labelledby="standards-title">
  <div class="sp-wrap">
    <div class="sp-split">
      <?php sp_head('standards', 'Editorial standards', 'How we write Paynancial Insights.'); ?>
      <div class="sp-prose reveal">
        <p>Articles are written by the Paynancial Editorial Team to explain concepts and good practice. They are general information, not legal, tax, accounting or financial advice — for decisions about your business, speak to a qualified professional.</p>
        <p>We do not publish invented statistics, and we describe Paynancial products only as they are documented on their own product pages. Every article shows when it was last updated.</p>
      </div>
    </div>
  </div>
</section>
