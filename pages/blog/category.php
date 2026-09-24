<?php
/**
 * Blog category — /blog/category/{category}. Noindex until the blog-level
 * quality review (content-governance.php). Expects $blog_category from the
 * front controller; only categories with live articles (plus Regulatory
 * Insights, which explains the source-first policy) resolve.
 */
require_once __DIR__ . '/../../includes/blog-ui.php';

[$label, $blurb] = blog_categories()[$blog_category];
$path = blog_category_url($blog_category);
$trail = [['Home', '/'], ['Blog', '/blog'], [$label, $path]];
$articles = blog_live($blog_category);
$page_meta = [
    'title'       => $label . ' | Paynancial Insights',
    'description' => $blurb,
    'canonical'   => site_url(ltrim($path, '/')),
    'extra_css'   => blog_css(),
    'schema'      => blog_listing_schema($label . ' — Paynancial Insights', $blurb, $path, $trail, $articles),
];

blog_hero($trail, 'Paynancial Insights · ' . $label, $label, $blurb, $blog_category);
?>

<?php if ($articles): ?>
<section class="sp-band sp-band--paper" aria-labelledby="cat-list-title">
  <div class="sp-wrap">
    <h2 id="cat-list-title" class="sr-only"><?= e($label) ?> articles</h2>
    <div class="blog-grid">
      <?php foreach ($articles as $a) { blog_card($a); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($blog_category === 'regulatory'): ?>
<section class="sp-band sp-band--ink" aria-labelledby="regulatory-title">
  <div class="sp-wrap">
    <?php blog_regulatory_policy(); ?>
  </div>
</section>
<?php endif; ?>

<section class="sp-band sp-band--dim" aria-labelledby="more-title">
  <div class="sp-wrap">
    <div class="sp-head reveal"><span class="sp-kicker">More topics</span><h2 id="more-title">Keep exploring.</h2></div>
    <div class="blog-topic-grid">
      <?php foreach (blog_category_pages() as $slug => [$l, $b]): if ($slug === $blog_category) continue; ?>
      <a class="blog-topic reveal blog-art--<?= e($slug) ?>" href="<?= e(blog_category_url($slug)) ?>"><strong><?= e($l) ?></strong><span><?= e($b) ?></span></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
