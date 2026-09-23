<?php
/**
 * Product category page — /products/{category}. $cat_page is set by the
 * front controller; content comes from includes/product-categories.php.
 */
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/product-categories.php';

$cat = $cat_page;
$faqs = faq_set('category:' . $cat['slug']);
$path = cat_url($cat['slug']);
$trail = [['Home', '/'], ['Products', '/products'], [$cat['name'], $path]];
$page_meta = sp_meta([
    'title'       => $cat['title'],
    'description' => $cat['description'],
    'path'        => $path,
    'h1'          => $cat['h1'],
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$statusLabel = ['page' => 'Product page', 'covered' => 'Included', 'request' => 'On request'];
$linked = array_values(array_filter($cat['items'], fn ($i) => $i[1] === 'page'));

ob_start(); ?>
<div class="sp-glance">
  <span class="sp-glance-label">In short</span>
  <p><?= e($cat['answer']) ?></p>
  <?php if ($linked): ?>
  <ul>
    <?php foreach ($linked as [$label, , $href]): ?>
    <li><a href="<?= e($href) ?>"><?= e($label) ?> <span aria-hidden="true">→</span></a></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
<?php $aside = ob_get_clean();

sp_track_view('product_category_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Products · ' . $cat['name'],
    'h1'        => $cat['h1'],
    'lead'      => $cat['lead'],
    'primary'   => [cta_label(), '/contact?intent=sales&product=' . $cat['slug'], 'cta_click'],
    'secondary' => ['Explore all products', '/products', 'cta_click'],
    'values'    => array_slice(array_map(fn ($i) => $i[0], $cat['items']), 0, 4),
    'aside'     => $aside,
]);
?>
<nav class="sp-index" aria-label="On this page">
  <div class="sp-wrap"><ul>
    <li><a href="#overview">Overview</a></li>
    <li><a href="#start">Where to start</a></li>
    <li><a href="#catalog">Everything in <?= e($cat['name']) ?></a></li>
    <li><a href="#faq">FAQ</a></li>
  </ul></div>
</nav>

<?php sp_band_open('overview'); ?>
  <div class="sp-split">
    <?php sp_head('overview', 'Overview', 'What ' . $cat['name'] . ' covers.'); ?>
    <div class="sp-prose reveal">
      <?php foreach ($cat['intro'] as $para): ?><p><?= e($para) ?></p><?php endforeach; ?>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('start', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('start', 'Where to start', 'Pick the one that matches your need.'); ?>
    <?php sp_table(['If…', 'Start with'], array_map(
        fn ($r) => [e($r[0]), '<a class="inline-link" href="' . e($r[2]) . '">' . e($r[1]) . ' →</a>'],
        $cat['choose']
    ), 'Where to start in ' . $cat['name']); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('catalog'); ?>
  <?php sp_head('catalog', 'The catalog', 'Everything in ' . $cat['name'] . '.', 'Products with their own page link straight to it. Items marked “On request” are available to discuss with our team.'); ?>
  <div class="sp-items">
    <?php foreach ($cat['items'] as [$label, $kind, $href, $desc]): $id = cat_slugify($label); ?>
    <article class="sp-item sp-item--<?= e($kind) ?> reveal" id="<?= e($id) ?>">
      <span class="sp-item-status"><?= e($statusLabel[$kind]) ?></span>
      <h3><?= e($label) ?></h3>
      <p><?= e($desc) ?></p>
      <?php if ($kind === 'request'): ?>
      <a class="sp-item-link" href="<?= e('/contact?intent=sales&product=' . $id) ?>" data-track="cta_click">Ask about <?= e($label) ?> <span aria-hidden="true">→</span></a>
      <?php else: ?>
      <a class="sp-item-link" href="<?= e($href) ?>">Explore <?= e($kind === 'covered' ? 'in ' . ($href === '/products/payment-collection' ? 'Smart Collections' : ($href === '/products/payouts' ? 'Payouts' : 'Payment Analytics')) : $label) ?> <span aria-hidden="true">→</span></a>
      <?php endif; ?>
    </article>
    <?php endforeach; ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('platform', 'ink'); ?>
  <?php sp_head('platform', 'One platform', 'Everything here runs on the same foundation.'); ?>
  <?php sp_answers([
      ['One API', 'A single REST API, authenticated with API keys, across every Paynancial product.'],
      ['Safe retries', 'Idempotency keys mean a retried request never charges, refunds or pays out twice.'],
      ['Real-time events', 'Webhooks for payments, payouts, refunds and settlements as they happen.'],
      ['Governed automation', 'Permissions, limits and human oversight for anything an agent or automated job does.'],
  ]); ?>
  <p class="reveal" style="margin-top:28px;"><a class="card-link" href="/developers" style="color:var(--teal-300);">Explore the Developer Hub →</a></p>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', $cat['name'] . ' questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'More products', 'Other product categories.'); ?>
  <?php sp_related(array_map(fn ($s) => [cat_page($s)['name'], cat_page($s)['lead'], cat_url($s)], $cat['related'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Talk to us about ' . $cat['name'] . '.', 'Tell us what you need to build, and we will show you where to start.', [
    [cta_label(), '/contact?intent=sales&product=' . $cat['slug'], 'cta_click'],
    ['Explore all products', '/products', 'cta_click'],
]); ?>
