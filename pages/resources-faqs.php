<?php
/**
 * FAQ hub — /resources/faqs. General questions are answered in full here
 * (and marked up as FAQPage). Topic questions are listed as links to the
 * page that answers them, so no answer is duplicated across the site.
 */
require_once __DIR__ . '/../includes/standalone-ui.php';
require_once __DIR__ . '/../includes/resources-data.php';

$general = faq_set('general');
$directory = res_faq_directory();
$total = count($general);
foreach ($directory as $group) {
    foreach ($group['pages'] as $page) {
        $total += count($page[3]);
    }
}
$trail = [['Home', '/'], ['Resources', '/resources'], ['FAQs', '/resources/faqs']];
$page_meta = sp_meta([
    'title'       => 'FAQs | Paynancial Help & Answers',
    'description' => 'Answers to common Paynancial questions — getting started, payment methods, refunds, security, sandbox testing — plus every question answered across the site, organised by topic.',
    'path'        => '/resources/faqs',
    'h1'          => 'Frequently asked questions',
    'trail'       => $trail,
    'faqs'        => $general,
]);

sp_track_view('faq_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Resources · FAQs',
    'h1'        => 'Frequently asked questions',
    'lead'      => 'Common questions answered here, and ' . $total . ' questions answered across Paynancial, organised by topic.',
    'primary'   => ['Contact Support', '/contact?intent=support', 'cta_click'],
    'secondary' => ['All Resources', '/resources', 'cta_click'],
    'values'    => array_merge(['General'], array_map(fn ($g) => $g['label'], $directory)),
]);
?>
<nav class="sp-index" aria-label="On this page">
  <div class="sp-wrap"><ul>
    <li><a href="#general">General</a></li>
    <?php foreach ($directory as $g): ?><li><a href="#<?= e($g['id']) ?>"><?= e($g['label']) ?></a></li><?php endforeach; ?>
  </ul></div>
</nav>

<section class="sp-band sp-band--paper sp-faq-search-band" aria-label="Search FAQs">
  <div class="sp-wrap">
    <label class="sp-faq-search" for="faq-filter">
      <span class="sp-kicker">Search all <?= (int) $total ?> questions</span>
      <input type="search" id="faq-filter" placeholder="Try &ldquo;refund&rdquo;, &ldquo;sandbox&rdquo; or &ldquo;UPI&rdquo;" autocomplete="off" data-faq-filter>
    </label>
    <p class="sp-faq-empty" data-faq-empty hidden>No questions match. <a class="inline-link" href="/contact?intent=support">Ask our team →</a></p>
  </div>
</section>

<?php sp_band_open('general', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('general', 'General', 'Getting started, payments and support.'); ?>
    <div data-faq-group>
      <?php sp_faq($general); ?>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php foreach ($directory as $i => $g): ?>
<?php sp_band_open($g['id'], $i % 2 ? 'dim' : 'paper'); ?>
  <?php sp_head($g['id'], 'Topic', $g['label'], 'Each question links to the page that answers it.'); ?>
  <div class="sp-faqdir" data-faq-group>
    <?php foreach ($g['pages'] as [$title, $url, $anchor, $questions]): ?>
    <div class="sp-faqdir-page reveal">
      <h3><a href="<?= e($url) ?>"><?= e($title) ?></a></h3>
      <ul>
        <?php foreach ($questions as $question): ?>
        <li data-faq-item><a href="<?= e($url . '#' . $anchor) ?>"><?= e($question) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>
  </div>
<?php sp_band_close(); ?>
<?php endforeach; ?>

<?php sp_cta('Still have a question?', 'Our team can help with accounts, payments, integrations and business services.', [
    ['Contact Support', '/contact?intent=support', 'cta_click'],
    [cta_label(), '/contact?intent=sales', 'cta_click'],
]); ?>
