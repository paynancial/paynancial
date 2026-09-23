<?php
/**
 * Standalone page template — shared components for full-width content
 * pages (Developer Hub and its children, Sandbox, AI Governance, …).
 *
 * Every section is a full-viewport-width band (.sp-band) with a wide inner
 * grid (.sp-wrap), so pages read edge to edge rather than as a narrow card
 * on a blank background. Styles: assets/css/standalone.css.
 *
 * Content rule: these helpers only render what a page passes in — pages
 * must state published, verifiable facts only (see README "Content &
 * claims policy").
 */

declare(strict_types=1);

require_once __DIR__ . '/business-services.php';    // breadcrumb / FAQ schema builders
require_once __DIR__ . '/business-services-ui.php'; // bs_breadcrumb()
require_once __DIR__ . '/regulatory-context.php';  // sp_regulatory() content
require_once __DIR__ . '/content-governance.php';  // publishing gate

/**
 * Page meta for a standalone page: canonical, OG, and JSON-LD
 * (WebPage or TechArticle, BreadcrumbList, optional FAQPage).
 *
 * $o keys: title, description, path, trail ([label, path] list),
 * type ('WebPage' | 'TechArticle'), faqs ([q, a] list), robots.
 */
function sp_meta(array $o): array
{
    $url = site_url(ltrim($o['path'], '/'));
    $page = [
        '@context'    => 'https://schema.org',
        '@type'       => $o['type'] ?? 'WebPage',
        'name'        => $o['h1'] ?? $o['title'],
        'headline'    => $o['h1'] ?? $o['title'],
        'description' => $o['description'],
        'url'         => $url,
        'inLanguage'  => 'en-IN',
        'publisher'   => organization_schema(),
        'isPartOf'    => ['@type' => 'WebSite', 'name' => 'Paynancial', 'url' => APP_URL],
    ];
    $schema = [$page, bs_breadcrumb_schema($o['trail'])];
    if (!empty($o['service'])) {
        // Local relevance: every Paynancial product is offered to businesses in India.
        $schema[] = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'name'        => $o['service'],
            'serviceType' => $o['service'],
            'description' => $o['description'],
            'url'         => $url,
            'provider'    => organization_schema(),
            'areaServed'  => ['@type' => 'Country', 'name' => 'India'],
        ];
    }
    if (!empty($o['faqs'])) {
        $schema[] = bs_faq_schema($o['faqs']);
    }
    $meta = [
        'title'       => $o['title'],
        'description' => $o['description'],
        'canonical'   => $url,
        'image'       => site_url('/assets/images/paynancial-logo.png'),
        'schema'      => $schema,
        'extra_css'   => 'css/standalone.css',
    ];
    if (!empty($o['robots'])) {
        $meta['robots'] = $o['robots'];
    }
    return $meta;
}

/**
 * Full-width hero. $o keys: trail, eyebrow, h1, lead, primary [label, href,
 * track], secondary [label, href, track], values (list of short strings),
 * aside (raw HTML for the right-hand panel).
 */
function sp_hero(array $o): void
{
    ?>
    <section class="sp-hero" aria-labelledby="sp-h1">
      <div class="sp-hero-grid" aria-hidden="true"></div>
      <div class="sp-wrap sp-hero-inner<?= empty($o['aside']) ? ' is-single' : '' ?>">
        <div class="sp-hero-copy">
          <?php bs_breadcrumb($o['trail']); ?>
          <?= brand_lockup() ?>
          <span class="sp-eyebrow"><?= e($o['eyebrow']) ?></span>
          <h1 id="sp-h1"><?= e($o['h1']) ?></h1>
          <p class="sp-lead"><?= e($o['lead']) ?></p>
          <div class="sp-actions">
            <?php if (!empty($o['primary'])): ?>
            <a class="btn sp-btn-accent" href="<?= e($o['primary'][1]) ?>"<?= !empty($o['primary'][2]) ? ' data-track="' . e($o['primary'][2]) . '"' : '' ?>><?= e($o['primary'][0]) ?> <span aria-hidden="true">→</span></a>
            <?php endif; ?>
            <?php if (!empty($o['secondary'])): ?>
            <a class="btn sp-btn-ghost" href="<?= e($o['secondary'][1]) ?>"<?= !empty($o['secondary'][2]) ? ' data-track="' . e($o['secondary'][2]) . '"' : '' ?>><?= e($o['secondary'][0]) ?> <span aria-hidden="true">→</span></a>
            <?php endif; ?>
          </div>
          <?php if (!empty($o['values'])): ?>
          <ul class="sp-values">
            <?php foreach ($o['values'] as $v): ?><li><?= e($v) ?></li><?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php if (!empty($o['aside'])): ?>
        <div class="sp-hero-aside"><?= $o['aside'] ?></div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

/** Opening of a full-width band. $tone: paper | dim | ink. */
function sp_band_open(string $id, string $tone = 'paper', string $extra = ''): void
{
    echo '<section class="sp-band sp-band--' . e($tone) . ($extra !== '' ? ' ' . e($extra) : '') . '" id="' . e($id) . '" aria-labelledby="' . e($id) . '-title"><div class="sp-wrap">';
}

function sp_band_close(): void
{
    echo '</div></section>';
}

/** Section heading: kicker, H2 (id = band id + "-title") and optional intro. */
function sp_head(string $bandId, string $kicker, string $title, string $intro = ''): void
{
    echo '<div class="sp-head reveal"><span class="sp-kicker">' . e($kicker) . '</span>'
        . '<h2 id="' . e($bandId) . '-title">' . e($title) . '</h2>'
        . ($intro !== '' ? '<p>' . e($intro) . '</p>' : '') . '</div>';
}

/**
 * Tabbed code panel (uses the site's existing .code-panel behaviour in
 * main.js). $blocks: [lang => [label, code]]; code is plain text.
 */
function sp_code(array $blocks, string $caption = ''): string
{
    $html = '<figure class="sp-code">';
    if ($caption !== '') {
        $html .= '<figcaption>' . e($caption) . '</figcaption>';
    }
    $html .= '<div class="code-panel"><div class="code-tabs">';
    $first = true;
    foreach ($blocks as $lang => [$label]) {
        $html .= '<button type="button" class="code-tab' . ($first ? ' is-active' : '') . '" data-lang="' . e($lang) . '">' . e($label) . '</button>';
        $first = false;
    }
    $html .= '</div><div class="code-body"><button class="copy-btn" type="button">Copy</button>';
    $first = true;
    foreach ($blocks as $lang => [, $code]) {
        $html .= '<pre data-code-block="' . e($lang) . '"' . ($first ? '' : ' style="display:none"') . '><code>' . e($code) . '</code></pre>';
        $first = false;
    }
    return $html . '</div></div></figure>';
}

/** Question-and-answer blocks for answer engines: [question, answer] list. */
function sp_answers(array $qa): void
{
    echo '<dl class="sp-answers">';
    foreach ($qa as [$q, $a]) {
        echo '<div class="sp-answer reveal"><dt>' . e($q) . '</dt><dd>' . e($a) . '</dd></div>';
    }
    echo '</dl>';
}

/** Scrollable data table. $head: column labels; $rows: list of cell lists. */
function sp_table(array $head, array $rows, string $caption): void
{
    echo '<div class="sp-table-wrap reveal" role="region" aria-label="' . e($caption) . '" tabindex="0"><table class="sp-table"><caption>' . e($caption) . '</caption><thead><tr>';
    foreach ($head as $h) {
        echo '<th scope="col">' . e($h) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach (array_values($row) as $i => $cell) {
            echo $i === 0 ? '<th scope="row">' . $cell . '</th>' : '<td>' . $cell . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}

/** Numbered steps / timeline: [title, text] list. */
function sp_steps(array $steps): void
{
    echo '<ol class="sp-steps">';
    foreach (array_values($steps) as $i => [$title, $text]) {
        echo '<li class="sp-step reveal"><span class="sp-step-num">' . sprintf('%02d', $i + 1) . '</span><div><strong>' . e($title) . '</strong><p>' . e($text) . '</p></div></li>';
    }
    echo '</ol>';
}

/** FAQ accordion (native <details>). */
function sp_faq(array $faqs): void
{
    echo '<div class="sp-faq">';
    foreach ($faqs as $i => [$q, $a]) {
        echo '<details class="sp-faq-item reveal"' . ($i === 0 ? ' open' : '') . '><summary>' . e($q)
            . '<span class="sp-faq-toggle" aria-hidden="true"></span></summary><p>' . e($a) . '</p></details>';
    }
    echo '</div>';
}

/** Related pages: [title, text, href] list. */
function sp_related(array $items): void
{
    echo '<div class="sp-related">';
    foreach ($items as [$title, $text, $href]) {
        echo '<a class="sp-related-card reveal" href="' . e($href) . '"><strong>' . e($title) . '</strong><span>' . e($text) . '</span><em>Explore <span aria-hidden="true">→</span></em></a>';
    }
    echo '</div>';
}

/** Closing full-width CTA band. Actions: [label, href, track] list. */
function sp_cta(string $title, string $text, array $actions): void
{
    ?>
    <section class="sp-band sp-band--cta" aria-labelledby="sp-cta-title">
      <div class="sp-wrap sp-cta">
        <div>
          <h2 id="sp-cta-title"><?= e($title) ?></h2>
          <p><?= e($text) ?></p>
        </div>
        <div class="sp-actions">
          <?php foreach ($actions as $i => [$label, $href, $track]): ?>
          <a class="btn <?= $i === 0 ? 'sp-btn-accent' : 'sp-btn-ghost' ?>" href="<?= e($href) ?>" data-track="<?= e($track) ?>"><?= e($label) ?> <span aria-hidden="true">→</span></a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}

/** Page-view analytics hook, read by main.js (paynancial:track event). */
function sp_track_view(string $event): void
{
    echo '<span hidden data-track-view="' . e($event) . '"></span>';
}
