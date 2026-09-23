<?php
/** Resources hub — /resources. Navigational: every resource, grouped by what you need. */
require_once __DIR__ . '/../includes/standalone-ui.php';
require_once __DIR__ . '/../includes/resources-data.php';

$trail = [['Home', '/'], ['Resources', '/resources']];
$sections = res_hub_sections();
$page_meta = sp_meta([
    'title'       => 'Resources | FAQs, Guides, Developer Docs & Support | Paynancial',
    'description' => 'Paynancial resources in one place: FAQs, support, developer guides and API documentation, agentic AI explainers, industry solutions, trust and legal policies.',
    'path'        => '/resources',
    'h1'          => 'Everything you need to work with Paynancial.',
    'trail'       => $trail,
]);

sp_track_view('resources_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Resources',
    'h1'        => 'Everything you need to work with Paynancial.',
    'lead'      => 'Answers, developer guides, explainers and policies — grouped by what you are trying to do.',
    'primary'   => ['Browse FAQs', '/resources/faqs', 'cta_click'],
    'secondary' => ['Visit the Developer Hub', '/developers', 'documentation_click'],
    'values'    => array_map(fn ($s) => $s[1], $sections),
]);
?>
<nav class="sp-index" aria-label="On this page">
  <div class="sp-wrap"><ul>
    <?php foreach ($sections as [$id, $kicker]): ?><li><a href="#<?= e($id) ?>"><?= e($kicker) ?></a></li><?php endforeach; ?>
  </ul></div>
</nav>

<?php foreach ($sections as $i => [$id, $kicker, $title, $intro, $links]): ?>
<?php sp_band_open($id, $i % 2 ? 'dim' : 'paper'); ?>
  <?php sp_head($id, $kicker, $title, $intro); ?>
  <div class="sp-cards">
    <?php foreach ($links as $n => [$name, $text, $href]): ?>
    <a class="sp-card reveal" href="<?= e($href) ?>">
      <span class="sp-card-num"><?= sprintf('%02d', $n + 1) ?></span>
      <h3><?= e($name) ?></h3>
      <p><?= e($text) ?></p>
      <span class="sp-card-link">Explore <span aria-hidden="true">→</span></span>
    </a>
    <?php endforeach; ?>
  </div>
<?php sp_band_close(); ?>
<?php endforeach; ?>

<?php sp_cta('Can\'t find what you are looking for?', 'Talk to our team — sales, support or developer help.', [
    ['Contact Paynancial', '/contact', 'cta_click'],
    ['Browse FAQs', '/resources/faqs', 'cta_click'],
]); ?>
