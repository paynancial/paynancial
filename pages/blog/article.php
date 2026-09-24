<?php
/**
 * Blog article — /blog/{slug}. Expects $blog_article (a live article) from
 * the front controller.
 *
 * Layout: header → quick answer (question → direct answer) → key takeaways
 * → sections (H2) → FAQs → related. Regulatory articles also show the
 * source record prominently and keep the official requirement, Paynancial's
 * explanation and professional advice visibly separate.
 *
 * Indexing is decided by the publishing gate, never by this template.
 */
require_once __DIR__ . '/../../includes/blog-ui.php';

$a = $blog_article;
$path = blog_url($a['slug']);
$url = site_url(ltrim($path, '/'));
$catLabel = blog_category_label($a['category']);
$trail = [['Home', '/'], ['Blog', '/blog'], [$catLabel, blog_category_url($a['category'])], [$a['title'], $path]];
$regulatory = $a['type'] === 'regulatory';
$src = $a['source'] ?? [];

$schema = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $a['title'],
        'description' => $a['description'],
        'url' => $url,
        'mainEntityOfPage' => $url,
        'datePublished' => $a['created'],
        'dateModified' => $a['updated'],
        'inLanguage' => 'en-IN',
        'articleSection' => $catLabel,
        'wordCount' => blog_words($a),
        'author' => ['@type' => 'Organization', 'name' => BLOG_AUTHOR, 'url' => APP_URL],
        'publisher' => organization_schema(),
        'image' => site_url('/assets/images/paynancial-logo.png'),
    ],
    bs_breadcrumb_schema($trail),
];
if ($a['faqs']) {
    $schema[] = bs_faq_schema($a['faqs']);
}
$page_meta = [
    'title'       => $a['meta_title'],
    'description' => $a['description'],
    'canonical'   => $url,
    'og_type'     => 'article',
    'article'     => ['published_time' => $a['created'], 'modified_time' => $a['updated'], 'section' => $catLabel],
    'extra_css'   => blog_css(),
    'schema'      => $schema,
];

$related = array_values(array_filter(array_map('blog_article', $a['related'])));
if (count($related) < 3) {
    foreach (blog_live($a['category']) as $slug => $other) {
        if ($slug !== $a['slug'] && !in_array($other, $related, true) && count($related) < 3) {
            $related[] = $other;
        }
    }
}
?>
<article class="blog-article">
  <header class="blog-article-head">
    <div class="sp-wrap">
      <?php bs_breadcrumb($trail); ?>
      <a class="blog-chip" href="<?= e(blog_category_url($a['category'])) ?>"><?= e($catLabel) ?></a>
      <h1><?= e($a['title']) ?></h1>
      <p class="blog-dek"><?= e($a['dek']) ?></p>
      <p class="blog-byline">
        <span>By <?= e(BLOG_AUTHOR) ?></span>
        <span>Updated <time datetime="<?= e($a['updated']) ?>"><?= e(blog_date($a['updated'])) ?></time></span>
        <span><?= blog_reading_minutes($a) ?> min read</span>
      </p>
    </div>
  </header>

  <div class="sp-wrap blog-article-grid">
    <aside class="blog-toc" aria-label="On this page">
      <span class="blog-toc-label">On this page</span>
      <ol>
        <?php if ($regulatory): ?><li><a href="#source">Official source</a></li><?php endif; ?>
        <li><a href="#quick-answer">Quick answer</a></li>
        <?php foreach ($a['sections'] as [$id, $h2]): ?><li><a href="#<?= e($id) ?>"><?= e($h2) ?></a></li><?php endforeach; ?>
        <?php if ($a['faqs']): ?><li><a href="#faq">FAQs</a></li><?php endif; ?>
      </ol>
    </aside>

    <div class="blog-body">
      <?php if ($regulatory): ?>
      <section class="blog-source" id="source" aria-labelledby="source-title">
        <h2 id="source-title">Official source</h2>
        <dl>
          <div><dt>Source</dt><dd><?= e($src['regulator']) ?></dd></div>
          <div><dt><?= e($src['instrument_type']) ?></dt><dd><?= e($src['number']) ?></dd></div>
          <div><dt>Title</dt><dd><?= e($src['title']) ?></dd></div>
          <div><dt>Issued</dt><dd><?= e(blog_date($src['issue_date'])) ?></dd></div>
          <div><dt>Effective</dt><dd><?= e($src['effective_date']) ?></dd></div>
          <div><dt>Official source</dt><dd><a href="<?= e($src['official_url']) ?>" rel="noopener" target="_blank"><?= e(parse_url($src['official_url'], PHP_URL_HOST) ?: $src['official_url']) ?></a></dd></div>
          <div><dt>Last reviewed</dt><dd><?= e(blog_date($src['last_verified'])) ?></dd></div>
        </dl>
        <p class="blog-source-note">This article explains the official document in plain language. <strong>Official requirement</strong> blocks restate what the regulator says; everything else is Paynancial's explanation. It is not legal, tax or financial advice — for decisions about your business, consult a qualified professional.</p>
      </section>
      <?php endif; ?>

      <section class="blog-answer" id="quick-answer" aria-labelledby="quick-answer-title">
        <h2 id="quick-answer-title" class="blog-answer-q"><?= e($a['question']) ?></h2>
        <p><?= e($a['answer']) ?></p>
        <?php if (!empty($a['takeaways'])): ?>
        <div class="blog-takeaways">
          <span>Key takeaways</span>
          <ul><?php foreach ($a['takeaways'] as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
      </section>

      <?php foreach ($a['sections'] as [$id, $h2, $html]): ?>
      <section class="blog-section" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-h">
        <h2 id="<?= e($id) ?>-h"><?= e($h2) ?></h2>
        <div class="blog-prose"><?= $html /* authored, version-controlled content */ ?></div>
      </section>
      <?php endforeach; ?>

      <?php if ($a['faqs']): ?>
      <section class="blog-section" id="faq" aria-labelledby="faq-h">
        <h2 id="faq-h">Frequently asked questions</h2>
        <?php sp_faq($a['faqs']); ?>
      </section>
      <?php endif; ?>

      <?php if ($a['links']): ?>
      <aside class="blog-links" aria-label="Related on Paynancial">
        <span>Related on Paynancial</span>
        <ul><?php foreach ($a['links'] as [$label, $href]): ?><li><a href="<?= e($href) ?>"><?= e($label) ?> <span aria-hidden="true">→</span></a></li><?php endforeach; ?></ul>
      </aside>
      <?php endif; ?>

      <p class="blog-disclaimer"><?= $regulatory ? '' : 'This article is general information to explain concepts and good practice. It is not legal, tax, accounting or financial advice. ' ?>Last updated <?= e(blog_date($a['updated'])) ?>. Found something unclear or out of date? <a href="/contact?intent=support&amp;topic=blog">Tell us</a>.</p>
    </div>
  </div>
</article>

<?php if ($related): ?>
<section class="sp-band sp-band--dim" aria-labelledby="related-title">
  <div class="sp-wrap">
    <div class="sp-head reveal"><span class="sp-kicker">Keep reading</span><h2 id="related-title">Related articles.</h2></div>
    <div class="blog-grid">
      <?php foreach ($related as $r) { blog_card($r); } ?>
    </div>
  </div>
</section>
<?php endif; ?>
