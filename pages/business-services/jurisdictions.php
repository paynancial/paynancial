<?php
/**
 * /business-services/jurisdictions — jurisdiction directory / finder.
 * Filters (text, region, objective, structure) are applied server-side
 * from the query string, so results work without JS and are linkable;
 * business-services.js applies the same filters live.
 */
require_once __DIR__ . '/../../includes/business-services-ui.php';

$regions = bs_regions();
$objectives = bs_objectives();
$structures = bs_structure_options();

$q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);
$region = (string) ($_GET['region'] ?? '');
$objective = (string) ($_GET['objective'] ?? '');
$structure = (string) ($_GET['structure'] ?? '');
if (!isset($regions[$region])) $region = '';
if (!isset($objectives[$objective])) $objective = '';
if (!isset($structures[$structure])) $structure = '';
$isFiltered = $q !== '' || $region !== '' || $objective !== '' || $structure !== '';

$all = bs_jurisdictions();
$results = bs_filter_jurisdictions($region, $q, $objective, $structure);

$bs_trail = [['Home', '/'], ['Business Services', bs_url()], ['Jurisdictions', bs_jurisdiction_url()]];
// Filtered views are variations of the directory — canonical stays on the directory.
$page_meta = bs_page_meta(
    'Jurisdiction Directory — Where Would You Like to Incorporate? | Paynancial',
    'Search and filter international jurisdictions for company incorporation by region and business objective, then speak with Paynancial about your requirements.',
    bs_jurisdiction_url(),
    [bs_breadcrumb_schema($bs_trail), [
        '@context' => 'https://schema.org',
        '@type'    => 'ItemList',
        'name'     => 'International incorporation jurisdictions',
        'itemListElement' => array_values(array_map(
            fn ($slug, $j, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $j['name'], 'url' => site_url(bs_jurisdiction_url($slug))],
            array_keys($all), $all, array_keys(array_keys($all))
        )),
    ]]
);
if (!bs_international_confirmed()) {
    // No jurisdiction is confirmed as served yet — keep out of search.
    $page_meta['robots'] = 'noindex, follow';
}
?>

<section class="bs-detail-hero bs-dir-hero" aria-labelledby="bs-dir-title">
  <div class="container">
    <div class="bs-dir-intro reveal">
      <?php bs_breadcrumb($bs_trail); ?>
      <span class="eyebrow">Jurisdiction Directory</span>
      <h1 id="bs-dir-title">Where Would You Like to Incorporate?</h1>
      <p class="lead">Search for a country or jurisdiction, or narrow the list by region and business objective. Each jurisdiction page covers what to plan for; our team confirms availability when you enquire.</p>
    </div>

    <form class="bs-filter reveal" action="<?= e(bs_jurisdiction_url()) ?>" method="get" role="search" aria-label="Find a jurisdiction" data-jur-filter>
      <div class="bs-filter-search bs-search-field">
        <?= bs_icon('search') ?>
        <label class="sr-only" for="bs-dir-q">Search country or jurisdiction</label>
        <input id="bs-dir-q" name="q" type="search" value="<?= e($q) ?>" placeholder="Search country or jurisdiction" autocomplete="off">
      </div>
      <div class="bs-filter-selects">
        <div class="bs-select">
          <label for="bs-dir-region">Region</label>
          <select id="bs-dir-region" name="region">
            <option value="">All regions</option>
            <?php foreach ($regions as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= $region === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="bs-select">
          <label for="bs-dir-objective">Business objective</label>
          <select id="bs-dir-objective" name="objective">
            <option value="">Any objective</option>
            <?php foreach ($objectives as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= $objective === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($structures): ?>
        <div class="bs-select">
          <label for="bs-dir-structure">Company structure</label>
          <select id="bs-dir-structure" name="structure">
            <option value="">Any structure</option>
            <?php foreach ($structures as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= $structure === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php else: ?>
        <div class="bs-select is-static">
          <span class="bs-select-label">Company structure</span>
          <a class="bs-select-static" href="<?= e(bs_enquiry_url('expert')) ?>">Confirmed with an expert <?= bs_icon('arrow') ?></a>
        </div>
        <?php endif; ?>
      </div>
      <div class="bs-filter-actions">
        <button type="submit" class="btn btn-primary">Find a Jurisdiction <?= bs_icon('arrow') ?></button>
        <a class="bs-text-link is-muted" href="<?= e(bs_jurisdiction_url()) ?>" data-jur-reset<?= $isFiltered ? '' : ' hidden' ?>>Clear filters</a>
      </div>
    </form>
  </div>
</section>

<section class="bs-section" aria-labelledby="bs-results-title">
  <div class="container">
    <div class="bs-head bs-head-row">
      <div>
        <span class="eyebrow">International Jurisdictions</span>
        <h2 id="bs-results-title">Explore International Jurisdictions</h2>
      </div>
      <p class="bs-results-meta" aria-live="polite" data-jur-status><?php
        if ($isFiltered) echo count($results) === 1 ? '1 jurisdiction matches your filters.' : count($results) . ' jurisdictions match your filters.';
      ?></p>
    </div>

    <div class="bs-jur-grid" data-jur-grid>
      <?php foreach ($all as $slug => $j) bs_jurisdiction_card($slug, $j, 'h3', !isset($results[$slug])); ?>
    </div>

    <div class="bs-empty" data-jur-empty<?= $results ? ' hidden' : '' ?>>
      <span class="bs-empty-icon"><?= bs_icon('pin') ?></span>
      <h3>No listed jurisdiction matches your search yet</h3>
      <p>Requirements and availability vary. Tell us where you want to incorporate and our team will get back to you.</p>
      <a class="btn btn-primary" href="<?= e(bs_enquiry_url('jurisdiction-other')) ?>"><?= e(cta_label()) ?> <?= bs_icon('arrow') ?></a>
    </div>

    <?php bs_other_jurisdiction_card(); ?>
  </div>
</section>

<section class="bs-section bs-section-tint" id="regions" aria-labelledby="bs-regions-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Explore by Region</span>
      <h2 id="bs-regions-title">Browse jurisdictions by region</h2>
    </div>
    <?php bs_region_filter(); ?>
  </div>
</section>

<?php bs_cta_band(
    'Not sure which jurisdiction is right?',
    'Speak with our team about your business, markets and plans before you decide.',
    cta_label(), bs_enquiry_url('expert'),
    'Explore Global Incorporation', bs_url('global-incorporation')
); ?>

<?php bs_script(); ?>
