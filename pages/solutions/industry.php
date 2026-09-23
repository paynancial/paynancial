<?php
/**
 * Industry solution page — /solutions/{slug}. $sol_industry is set by the
 * front controller; all content comes from includes/solutions-data.php.
 */
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/solutions-data.php';

$ind = $sol_industry;
$products = sol_products();
$industries = sol_industries();
$trail = [['Home', '/'], ['Solutions', '/solutions'], [$ind['name'], sol_url($ind['slug'])]];
$page_meta = sp_meta([
    'title'       => $ind['title'],
    'description' => $ind['description'],
    'path'        => sol_url($ind['slug']),
    'h1'          => $ind['h1'],
    'trail'       => $trail,
    'faqs'        => $ind['faqs'],
]);

// Products this industry uses, in stack order, without repeats.
$used = [];
foreach ($ind['stack'] as [, $key]) {
    $used[$key] = $products[$key];
}

ob_start(); ?>
<div class="sp-glance">
  <span class="sp-glance-label">In short</span>
  <p><?= e($ind['answer']) ?></p>
  <ul>
    <?php foreach ($used as [$name, $href]): ?>
    <li><a href="<?= e($href) ?>"><?= e($name) ?> <span aria-hidden="true">→</span></a></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php $aside = ob_get_clean();

sp_track_view('solution_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Solutions · ' . $ind['name'],
    'h1'        => $ind['h1'],
    'lead'      => $ind['lead'],
    'primary'   => [cta_label(), '/contact?intent=sales&solution=' . $ind['slug'], 'cta_click'],
    'secondary' => ['Explore Products', '/products', 'cta_click'],
    'values'    => array_map(fn ($p) => $p[0], array_values($used)),
    'aside'     => $aside,
]);
?>

<?php sp_band_open('challenges'); ?>
  <?php sp_head('challenges', 'The challenge', 'How ' . strtolower($ind['name']) . ' businesses collect and move money — and where it gets hard.'); ?>
  <?php sp_answers($ind['challenges']); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('journey', 'dim'); ?>
  <?php sp_head('journey', 'The payment journey', 'From first payment to reconciled books.', 'How a typical ' . strtolower($ind['name']) . ' payment flows through Paynancial.'); ?>
  <?php sp_steps($ind['journey']); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('stack'); ?>
  <div class="sp-split">
    <?php sp_head('stack', 'Product stack', 'The Paynancial products that fit.', 'Each need maps to a product you can start with on its own and add to later.'); ?>
    <?php sp_table(['You need to…', 'Product', 'How it helps'], array_map(
        fn ($row) => [e($row[0]), '<a class="inline-link" href="' . e($products[$row[1]][1]) . '">' . e($products[$row[1]][0]) . '</a>', e($row[2])],
        $ind['stack']
    ), $ind['name'] . ' product stack'); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('use-cases', 'ink'); ?>
  <?php sp_head('use-cases', 'Use cases', 'What it looks like in practice.', 'Illustrative examples of how ' . strtolower($ind['name']) . ' businesses use Paynancial products.'); ?>
  <?php sp_answers($ind['scenarios']); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('considerations'); ?>
  <div class="sp-split">
    <?php sp_head('considerations', 'Before you start', 'Practical things to get right.'); ?>
    <?php sp_steps($ind['considerations']); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('integration', 'dim'); ?>
  <div class="sp-split sp-split--even">
    <div>
      <?php sp_head('integration', 'Integration & security', 'Built on the same API as every Paynancial product.'); ?>
      <div class="sp-prose reveal">
        <p>One REST API, idempotent requests and real-time webhooks — test it all in the sandbox before going live.</p>
      </div>
    </div>
    <div class="sp-prose reveal">
      <ul>
        <li><a class="inline-link" href="/developers/integration-guide">Integration Guide</a> — from sandbox key to first live payment.</li>
        <li><a class="inline-link" href="/developers/api-reference">API Reference</a> — resources, conventions and errors.</li>
        <li><a class="inline-link" href="/sandbox">Sandbox</a> — test without touching live payments.</li>
        <li><a class="inline-link" href="/security">Security &amp; Compliance</a> — how Paynancial approaches security.</li>
      </ul>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('faq'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', $ind['name'] . ' payments questions.'); ?>
    <?php sp_faq($ind['faqs']); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related', 'dim'); ?>
  <?php sp_head('related', 'Related industries', 'Solutions for similar businesses.'); ?>
  <?php sp_related(array_map(fn ($s) => [$industries[$s]['name'], $industries[$s]['lead'], sol_url($s)], $ind['related'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Talk to us about ' . strtolower($ind['name']) . ' payments.', 'Tell us how your business collects and pays out, and we will help you choose where to start.', [
    [cta_label(), '/contact?intent=sales&solution=' . $ind['slug'], 'cta_click'],
    ['Explore all solutions', '/solutions', 'cta_click'],
]); ?>
