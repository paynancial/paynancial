<?php
/**
 * Product capability page — /products/{slug} for refunds, settlements,
 * reconciliation and upi-payments. $pc_page is set by the front
 * controller; content comes from includes/product-capabilities.php.
 */
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/product-capabilities.php';

$pc = $pc_page;
$noun = $pc['slug'] === 'upi-payments' ? 'UPI payments' : strtolower($pc['name']);
$faqs = faq_set('product:' . $pc['slug']);
$path = '/products/' . $pc['slug'];
$trail = [['Home', '/'], ['Products', '/products'], [$pc['name'], $path]];
$page_meta = sp_meta([
    'title'       => $pc['title'],
    'description' => $pc['description'],
    'path'        => $path,
    'h1'          => $pc['h1'],
    'trail'       => $trail,
    'faqs'        => $faqs,
]);

if (!empty($pc['aside_code'])) {
    $aside = sp_code($pc['aside_code'], $pc['aside_caption']);
} else {
    $aside = '<div class="sp-glance"><span class="sp-glance-label">In short</span><p>' . e($pc['answer']) . '</p></div>';
}

sp_track_view('product_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Products · ' . $pc['name'],
    'h1'        => $pc['h1'],
    'lead'      => $pc['lead'],
    'primary'   => [cta_label(), '/contact?intent=sales&product=' . $pc['slug'], 'cta_click'],
    'secondary' => ['Read the API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => $pc['values'],
    'aside'     => $aside,
]);
?>

<?php sp_band_open('overview'); ?>
  <div class="sp-split">
    <?php sp_head('overview', 'Overview', 'What ' . $noun . ' mean' . ($pc['slug'] === 'upi-payments' || str_ends_with($noun, 's') ? '' : 's') . ' on Paynancial.'); ?>
    <div class="sp-prose reveal">
      <?php if (!empty($pc['aside_code'])): ?><p class="sp-answer-lead"><strong><?= e($pc['answer']) ?></strong></p><?php endif; ?>
      <?php foreach ($pc['what'] as $para): ?><p><?= $para ?></p><?php endforeach; ?>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('how-it-works', 'dim'); ?>
  <?php sp_head('how-it-works', 'How it works', 'Step by step.'); ?>
  <?php sp_steps($pc['steps']); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('capabilities', 'ink'); ?>
  <?php sp_head('capabilities', 'Capabilities', 'What you can do.'); ?>
  <?php sp_answers($pc['capabilities']); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('where'); ?>
  <div class="sp-split">
    <?php sp_head('where', 'Where it lives', 'Across the Paynancial platform.', $pc['name'] . ' is not a separate system — it runs through the products and tools you already use.'); ?>
    <?php sp_table(['Where', 'What you get'], $pc['surfaces'], $pc['name'] . ' across Paynancial'); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('practices', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('practices', 'Good practice', 'Getting it right.'); ?>
    <?php sp_steps($pc['practices']); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('faq'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', $pc['name'] . ' questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related', 'dim'); ?>
  <?php sp_head('related', 'Related products', 'Works well with.'); ?>
  <?php sp_related(array_map('pc_related_card', $pc['related'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Talk to us about ' . $noun . '.', 'Tell us how your business takes and moves money, and we will show you how it fits.', [
    [cta_label(), '/contact?intent=sales&product=' . $pc['slug'], 'cta_click'],
    ['Explore all products', '/products', 'cta_click'],
]); ?>
