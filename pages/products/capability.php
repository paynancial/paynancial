<?php
/**
 * Product capability page — /products/{slug} for refunds, settlements,
 * reconciliation and upi-payments. $pc_page is set by the front
 * controller; content comes from includes/product-capabilities.php.
 */
require_once __DIR__ . '/../../includes/standalone-ui.php';
require_once __DIR__ . '/../../includes/product-capabilities.php';

$pc = $pc_page;
$noun = $pc['slug'] === 'upi-payments' ? 'UPI payments' : (($pc['family'] ?? '') === 'ai' ? $pc['name'] : strtolower($pc['name']));
$faqs = faq_set($pc['faq_key'] ?? 'product:' . $pc['slug']);
$path = $pc['path'] ?? '/products/' . $pc['slug'];
$parent = $pc['parent'] ?? ['Products', '/products'];
$trail = [['Home', '/'], $parent, [$pc['name'], $path]];
$page_meta = sp_meta([
    'title'       => $pc['title'],
    'description' => $pc['description'],
    'path'        => $path,
    'h1'          => $pc['h1'],
    'trail'       => $trail,
    'faqs'        => $faqs,
    // Governed (unconfirmed) pages: live, noindex, no Service schema — content-governance.php.
    'robots'      => gov_indexable($path) ? ($pc['robots'] ?? '') : 'noindex, follow',
    'service'     => (($pc['family'] ?? '') === 'ai' || !empty($pc['guide']) || !gov_service_promotion($path)) ? '' : $pc['name'],
]);
$promote = gov_service_promotion($path);
// "In India" bands carry unverified regulatory statements: drafts only until verified.
$india = gov_india_context_public() ? ($pc['india'] ?? null) : null;
$tone = fn (string $a, string $b) => $india ? $b : $a; // keep bands alternating when the India band is present

if (!empty($pc['aside_code'])) {
    $aside = sp_code($pc['aside_code'], $pc['aside_caption']);
} else {
    $aside = '<div class="sp-glance"><span class="sp-glance-label">In short</span><p>' . e($pc['answer']) . '</p></div>';
}

sp_track_view('product_page_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => $parent[0] . ' · ' . $pc['name'],
    'h1'        => $pc['h1'],
    'lead'      => $pc['lead'],
    'primary'   => $promote ? [cta_label(), '/contact?intent=sales&product=' . $pc['slug'], 'cta_click'] : ['Ask about availability', '/contact?intent=sales&product=' . $pc['slug'], 'cta_click'],
    'secondary' => $pc['secondary'] ?? ['Read the API Reference', '/developers/api-reference', 'api_reference_click'],
    'values'    => $pc['values'],
    'aside'     => $aside,
]);
?>

<?php sp_band_open('overview'); ?>
  <div class="sp-split">
    <?php sp_head('overview', 'Overview', (($pc['family'] ?? '') === 'ai' ? 'What ' . $noun . ' does.' : 'What ' . $noun . ' mean' . ($pc['slug'] === 'upi-payments' || str_ends_with($noun, 's') ? '' : 's') . ' on Paynancial.')); ?>
    <div class="sp-prose reveal">
      <?php if (!empty($pc['aside_code'])): ?><p class="sp-answer-lead"><strong><?= e($pc['answer']) ?></strong></p><?php endif; ?>
      <?php foreach ($pc['what'] as $para): ?><p><?= $para ?></p><?php endforeach; ?>
      <?php if (!empty($pc['availability'])): ?><div class="sp-note"><strong>Availability:</strong> <?= e($pc['availability']) ?></div><?php endif; ?>
      <?php if (!empty($pc['confirm'])): ?>
      <div class="sp-note sp-confirm"><strong>Confirm with our team before you plan:</strong>
        <ul><?php foreach ($pc['confirm'] as $c): ?><li><?= e($c) ?></li><?php endforeach; ?></ul>
      </div>
      <?php endif; ?>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('how-it-works', 'dim'); ?>
  <?php sp_head('how-it-works', 'How it works', 'Step by step.'); ?>
  <?php sp_steps($pc['steps']); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('capabilities', 'ink'); ?>
  <?php [$capLabel, $capTitle, $capNote] = ($pc['caps_head'] ?? []) + ['Capabilities', 'What you can do.', '']; sp_head('capabilities', $capLabel, $capTitle, $capNote); ?>
  <?php sp_answers($pc['capabilities']); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('where'); ?>
  <div class="sp-split">
    <?php sp_head('where', 'Where it lives', 'Across the Paynancial platform.', $pc['name'] . ' is not a separate system — it runs through the products and tools you already use.'); ?>
    <?php sp_table($pc['surfaces_head'] ?? ['Where', 'What you get'], $pc['surfaces'], $pc['name'] . ' across Paynancial'); ?>
  </div>
<?php sp_band_close(); ?>

<?php if ($india): ?>
<?php sp_band_open('india', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('india', 'In India', $india['title'], trim(($india['note'] ?? '') . ' ' . GOV_REG_DISCLAIMER)); ?>
    <?php sp_answers($india['items']); ?>
  </div>
<?php sp_band_close(); ?>
<?php endif; ?>

<?php sp_regulatory((($pc['family'] ?? '') === 'ai' ? 'ai:' : '') . $pc['slug'], (($pc['family'] ?? '') === 'ai' ? 'AI in payments' : $noun) . ' in India'); ?>

<?php sp_band_open('practices', $tone('dim', 'paper')); ?>
  <div class="sp-split">
    <?php sp_head('practices', 'Good practice', 'Getting it right.'); ?>
    <?php sp_steps($pc['practices']); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', $tone('paper', 'dim')); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', $pc['name'] . ' questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related', $tone('dim', 'paper')); ?>
  <?php sp_head('related', ($pc['family'] ?? '') === 'ai' ? 'Related' : 'Related products', 'Works well with.'); ?>
  <?php sp_related(array_map('pc_related_card', $pc['related'])); ?>
<?php sp_band_close(); ?>

<?php sp_cta($promote ? 'Talk to us about ' . $noun . '.' : 'Ask about ' . $noun . '.', $promote ? 'Tell us how your business takes and moves money, and we will show you how it fits.' : 'This capability is not confirmed as a Paynancial product. Tell us what you need and our team will tell you what is available.', [
    [$promote ? cta_label() : 'Ask about availability', '/contact?intent=sales&product=' . $pc['slug'], 'cta_click'],
    ['Explore all products', '/products', 'cta_click'],
]); ?>
