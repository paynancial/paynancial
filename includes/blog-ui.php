<?php
/**
 * Blog presentation helpers shared by /blog, category and article pages.
 */
require_once __DIR__ . '/blog.php';
require_once __DIR__ . '/business-services.php';
require_once __DIR__ . '/business-services-ui.php';
require_once __DIR__ . '/standalone-ui.php';

const BLOG_AUTHOR = 'Paynancial Editorial Team';

function blog_css(): array
{
    return ['css/standalone.css', 'css/blog.css'];
}

function blog_category_label(string $cat): string
{
    return blog_categories()[$cat][0] ?? ucfirst($cat);
}

/** Article card. $size: 'md' | 'lg' (featured). */
function blog_card(array $a, string $size = 'md'): void
{
    $cat = blog_category_label($a['category']);
    ?>
    <article class="blog-card blog-card--<?= e($size) ?> reveal">
      <a class="blog-card-link" href="<?= e(blog_url($a['slug'])) ?>">
        <span class="blog-card-art blog-art--<?= e($a['category']) ?>" aria-hidden="true"><span><?= e($cat) ?></span></span>
        <span class="blog-card-body">
          <span class="blog-card-cat"><?= e($cat) ?></span>
          <strong class="blog-card-title"><?= e($a['title']) ?></strong>
          <span class="blog-card-dek"><?= e($a['dek']) ?></span>
          <span class="blog-card-meta"><?= e(blog_date($a['updated'])) ?> · <?= blog_reading_minutes($a) ?> min read</span>
        </span>
      </a>
    </article>
    <?php
}

/** Topic chips linking to category pages; $current highlights one. */
function blog_topic_nav(?string $current = null): void
{
    ?>
    <nav class="blog-topics" aria-label="Blog topics">
      <a href="/blog"<?= $current === null ? ' aria-current="page"' : '' ?>>All articles</a>
      <?php foreach (blog_category_pages() as $slug => [$label]): ?>
      <a href="<?= e(blog_category_url($slug)) ?>"<?= $current === $slug ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <?php
}

/** The source-first explainer shown on the hub and the Regulatory Insights page. */
function blog_regulatory_policy(): void
{
    ?>
    <div class="blog-policy">
      <div class="blog-policy-copy reveal">
        <span class="sp-kicker">Regulatory Insights</span>
        <h2 id="regulatory-title">Source-first explainers of financial regulation.</h2>
        <p>Regulatory Insights will explain circulars, notifications and guidelines from India's financial regulators in plain language: what changed, who it affects and what businesses and customers should know.</p>
        <p>Each article is written only from the official document itself — never from memory or secondary summaries — and is published only after the source has been verified and the article has passed editorial and regulatory review. Articles will appear here as that work is completed.</p>
      </div>
      <dl class="blog-policy-list reveal">
        <div><dt>Official source, always shown</dt><dd>Regulator, circular or notification number, issue date, effective date where applicable, and a link to the official document.</dd></div>
        <div><dt>Requirement, explanation and advice kept apart</dt><dd>What the regulator requires is shown separately from Paynancial's explanation. Neither is legal, tax or financial advice.</dd></div>
        <div><dt>Nothing filled in from memory</dt><dd>If a fact is not in the official source, it is not in the article.</dd></div>
        <div><dt>Reviewed and dated</dt><dd>Every article carries the date its source was last reviewed.</dd></div>
      </dl>
    </div>
    <?php
}

/** Blog hub / category hero. */
function blog_hero(array $trail, string $kicker, string $h1, string $lead, ?string $current): void
{
    ?>
    <section class="blog-hero" aria-labelledby="blog-h1">
      <div class="sp-wrap">
        <?php bs_breadcrumb($trail); ?>
        <span class="sp-kicker"><?= e($kicker) ?></span>
        <h1 id="blog-h1"><?= e($h1) ?></h1>
        <p class="blog-hero-lead"><?= e($lead) ?></p>
        <?php blog_topic_nav($current); ?>
      </div>
    </section>
    <?php
}

/** Blog-level schema (hub and category pages). */
function blog_listing_schema(string $name, string $description, string $path, array $trail, array $articles): array
{
    $url = site_url(ltrim($path, '/'));
    return [
        [
            '@context' => 'https://schema.org', '@type' => 'Blog', 'name' => $name, 'description' => $description,
            'url' => $url, 'inLanguage' => 'en-IN', 'publisher' => organization_schema(),
            'blogPost' => array_values(array_map(fn ($a) => [
                '@type' => 'BlogPosting', 'headline' => $a['title'], 'url' => site_url(ltrim(blog_url($a['slug']), '/')),
                'dateModified' => $a['updated'],
            ], $articles)),
        ],
        bs_breadcrumb_schema($trail),
    ];
}
