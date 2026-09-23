<?php
/**
 * Business Services — reusable view components. Each function echoes one
 * component (ServiceCard, JurisdictionCard, JurisdictionSearch,
 * RegionFilter, ProcessStep list, FAQ, CTA, cross-sell) so pages stay
 * declarative and every instance looks identical. Styles live in
 * assets/css/business-services.css and behaviour in
 * assets/js/business-services.js — both loaded only on these pages.
 */

declare(strict_types=1);

require_once __DIR__ . '/business-services.php';

/** Small line icons — one visual weight, used sparingly. */
function bs_icon(string $name): string
{
    $paths = [
        'launch'   => '<path d="M5 19c1.5-1.5 3-2 3-2m-3 2 2-5m-2 5 5-2"/><path d="M12 15 9 12c1.4-4.2 4.8-7.6 11-8-.4 6.2-3.8 9.6-8 11z"/><circle cx="15" cy="9" r="1.6"/>',
        'register' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'protect'  => '<path d="M12 3 5 6v5c0 4.5 3 8.3 7 10 4-1.7 7-5.5 7-10V6z"/><path d="m9 12 2 2 4-4"/>',
        'comply'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/><path d="m9 15 2 2 4-4"/>',
        'check'    => '<path d="m5 12 4.5 4.5L19 7"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z"/>',
        'expert'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>',
        'doc'      => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
        'eye'      => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'support'  => '<path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v5h-5"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'quote'    => '<path d="M4 7h16M4 12h10M4 17h7"/><circle cx="18" cy="16" r="3"/>',
        'pin'      => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'card'     => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>',
    ];
    $d = $paths[$name] ?? $paths['arrow'];
    return '<svg class="bs-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}

/** Flag image (flag-icons, MIT — see assets/images/flags/LICENSE.txt). */
function bs_flag(array $j, string $class = 'bs-flag'): string
{
    return '<img class="' . e($class) . '" src="' . e(asset('images/flags/' . $j['iso'] . '.svg')) . '" alt="" width="28" height="21" loading="lazy">';
}

/** Breadcrumb trail of [label, path]; the last item renders as the current page. */
function bs_breadcrumb(array $trail): void
{
    $last = count($trail) - 1;
    echo '<nav class="breadcrumb bs-breadcrumb" aria-label="Breadcrumb">';
    foreach (array_values($trail) as $i => [$label, $path]) {
        if ($i === $last) {
            echo '<span class="current" aria-current="page">' . e($label) . '</span>';
        } else {
            echo '<a href="' . e($path) . '">' . e($label) . '</a><span aria-hidden="true">/</span>';
        }
    }
    echo '</nav>';
}

/** ServiceCard — one category with its service links. */
function bs_service_card(string $key, array $cat): void
{
    $services = bs_services();
    $lead = $cat['services'][0];
    ?>
    <article class="bs-service-card reveal">
      <span class="bs-service-icon"><?= bs_icon($cat['icon']) ?></span>
      <h3><?= e($cat['label']) ?></h3>
      <p><?= e($cat['desc']) ?></p>
      <ul class="bs-service-links">
        <?php foreach ($cat['services'] as $slug): ?>
        <li><a href="<?= e(bs_url($slug)) ?>"><?= e($services[$slug]['short']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <a class="bs-text-link" href="<?= e(bs_url($lead)) ?>">Explore Services <?= bs_icon('arrow') ?></a>
    </article>
    <?php
}

/** JurisdictionCard — map crop centred on the jurisdiction, flag, name, descriptor. */
function bs_jurisdiction_card(string $slug, array $j, string $headingTag = 'h3', bool $hidden = false): void
{
    $pos = bs_map_position($j);
    $regionNames = array_map(fn ($r) => bs_regions()[$r] ?? $r, $j['regions']);
    ?>
    <a class="bs-jur-card reveal"<?= $hidden ? ' hidden' : '' ?> href="<?= e(bs_jurisdiction_url($slug)) ?>" data-jur-card data-search="<?= e(bs_search_text($j)) ?>" data-regions="<?= e(implode(' ', $j['regions'])) ?>" data-groups="<?= e(implode(' ', $j['groups'] ?? [])) ?>" data-structures="<?= e(implode(' ', array_map(fn ($st) => bs_slugify($st[0]), $j['structures'] ?? []))) ?>">
      <span class="bs-jur-visual" style="--fx:<?= $pos['x'] ?>;--fy:<?= $pos['y'] ?>;" aria-hidden="true">
        <img class="bs-jur-map" src="<?= e(asset('images/business-services/world-dots.svg')) ?>" alt="" loading="lazy">
        <span class="bs-jur-pin"></span>
        <span class="bs-jur-coords"><?= e(bs_format_coords($j)) ?></span>
      </span>
      <span class="bs-jur-body">
        <span class="bs-jur-flag"><?= bs_flag($j) ?></span>
        <<?= $headingTag ?> class="bs-jur-name"><?= e($j['name']) ?></<?= $headingTag ?>>
        <span class="bs-jur-desc"><?= e($j['descriptor']) ?></span>
        <span class="bs-jur-foot">
          <span class="bs-jur-region"><?= e(implode(' · ', $regionNames)) ?></span>
          <span class="bs-jur-cta">View Jurisdiction <?= bs_icon('arrow') ?></span>
        </span>
      </span>
    </a>
    <?php
}

/** "49.45° N · 2.54° W" — factual coordinates for the card visual. */
function bs_format_coords(array $j): string
{
    $lat = abs($j['lat']) . '° ' . ($j['lat'] >= 0 ? 'N' : 'S');
    $lon = abs($j['lon']) . '° ' . ($j['lon'] >= 0 ? 'E' : 'W');
    return $lat . ' · ' . $lon;
}

/**
 * JurisdictionSearch — works without JS (GET to the jurisdictions index,
 * filtered server-side); main.js adds live suggestions from data-options.
 */
function bs_jurisdiction_search(string $value = '', string $id = 'bs-jur-search', bool $compact = false): void
{
    $options = [];
    foreach (bs_jurisdictions() as $slug => $j) {
        $options[] = ['n' => $j['name'], 's' => bs_search_text($j), 'u' => bs_jurisdiction_url($slug), 'f' => asset('images/flags/' . $j['iso'] . '.svg')];
    }
    ?>
    <form class="bs-search<?= $compact ? ' is-compact' : '' ?>" action="<?= e(bs_jurisdiction_url()) ?>" method="get" role="search" data-jur-search data-options="<?= e(json_encode($options, JSON_UNESCAPED_SLASHES)) ?>">
      <label class="sr-only" for="<?= e($id) ?>">Search country or jurisdiction</label>
      <div class="bs-search-field">
        <?= bs_icon('search') ?>
        <input id="<?= e($id) ?>" name="q" type="search" value="<?= e($value) ?>" placeholder="Search country or jurisdiction..." autocomplete="off"
               role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="<?= e($id) ?>-list">
        <ul class="bs-search-list" id="<?= e($id) ?>-list" role="listbox" aria-label="Matching jurisdictions" hidden></ul>
      </div>
      <button type="submit" class="btn btn-primary bs-search-btn">Search</button>
    </form>
    <?php
}

/** RegionFilter — region tiles; links work without JS. */
function bs_region_filter(): void
{
    $byRegion = [];
    foreach (bs_jurisdictions() as $slug => $j) {
        foreach ($j['regions'] as $r) {
            $byRegion[$r][$slug] = $j;
        }
    }
    echo '<div class="bs-region-grid">';
    foreach (bs_regions() as $key => $label) {
        $list = $byRegion[$key] ?? [];
        ?>
        <a class="bs-region-tile reveal" href="<?= e(bs_jurisdiction_url() . '?region=' . $key) ?>">
          <span class="bs-region-name"><?= e($label) ?></span>
          <?php if ($list): ?>
            <span class="bs-region-flags" aria-hidden="true">
              <?php foreach (array_slice($list, 0, 5, true) as $j) echo bs_flag($j, 'bs-flag bs-flag-sm'); ?>
            </span>
            <span class="bs-region-list"><?= e(implode(', ', array_map(fn ($j) => $j['short'] ?? $j['name'], $list))) ?></span>
          <?php else: ?>
            <span class="bs-region-list">Speak with our team about incorporating in <?= e($label) ?>.</span>
          <?php endif; ?>
          <span class="bs-region-go" aria-hidden="true"><?= bs_icon('arrow') ?></span>
        </a>
        <?php
    }
    echo '</div>';
}

/** ProcessStep list — numbered steps with connectors. */
function bs_process(array $steps): void
{
    echo '<ol class="bs-process">';
    foreach (array_values($steps) as $i => [$title, $text]) {
        echo '<li class="bs-step reveal"><span class="bs-step-num">' . sprintf('%02d', $i + 1) . '</span>'
            . '<strong>' . e($title) . '</strong><span>' . e($text) . '</span></li>';
    }
    echo '</ol>';
}

/** FAQ accordion (native <details> — keyboard and screen-reader friendly). */
function bs_faq(array $faqs): void
{
    echo '<div class="bs-faq">';
    foreach ($faqs as $i => [$q, $a]) {
        echo '<details class="bs-faq-item reveal"' . ($i === 0 ? ' open' : '') . '><summary>' . e($q)
            . '<span class="bs-faq-toggle" aria-hidden="true"></span></summary><p>' . e($a) . '</p></details>';
    }
    echo '</div>';
}

/** "Don't see your preferred jurisdiction?" support card. */
function bs_other_jurisdiction_card(): void
{
    ?>
    <div class="bs-other reveal">
      <span class="bs-other-icon"><?= bs_icon('globe') ?></span>
      <div>
        <h3>Don't see your preferred jurisdiction?</h3>
        <p>Speak with our team about your specific incorporation requirements.</p>
      </div>
      <a class="btn btn-outline bs-btn-on-ink" href="<?= e(bs_enquiry_url('jurisdiction-other')) ?>">Talk to an Expert <?= bs_icon('arrow') ?></a>
    </div>
    <?php
}

/** Cross-sell into the core payments business. */
function bs_payments_crosssell(): void
{
    ?>
    <section class="bs-section bs-crosssell-wrap" aria-labelledby="bs-crosssell-title">
      <div class="container">
        <div class="bs-crosssell reveal">
          <span class="bs-crosssell-icon"><?= bs_icon('card') ?></span>
          <div class="bs-crosssell-copy">
            <span class="bs-kicker">Paynancial Payments</span>
            <h2 id="bs-crosssell-title">Once your business is ready, start accepting online payments with Paynancial.</h2>
            <p>Cards, UPI, netbanking and wallets through one integration — with clear settlement reporting behind every transaction.</p>
          </div>
          <a class="btn btn-dark" href="/products/payment-gateway">Explore Payment Gateway <?= bs_icon('arrow') ?></a>
        </div>
      </div>
    </section>
    <?php
}

/** Closing CTA band. */
function bs_cta_band(string $title, string $text, string $primaryLabel, string $primaryHref, string $secondaryLabel = 'Talk to an Expert', string $secondaryHref = ''): void
{
    $secondaryHref = $secondaryHref !== '' ? $secondaryHref : bs_enquiry_url('expert');
    ?>
    <section class="bs-section">
      <div class="container">
        <div class="bs-cta reveal">
          <img class="bs-cta-map" src="<?= e(asset('images/business-services/world-dots-light.svg')) ?>" alt="" aria-hidden="true" loading="lazy">
          <div class="bs-cta-copy">
            <h2><?= e($title) ?></h2>
            <p><?= e($text) ?></p>
          </div>
          <div class="bs-cta-actions">
            <a class="btn bs-btn-accent" href="<?= e($primaryHref) ?>"><?= e($primaryLabel) ?> <?= bs_icon('arrow') ?></a>
            <a class="btn btn-outline bs-btn-on-ink" href="<?= e($secondaryHref) ?>"><?= e($secondaryLabel) ?> <?= bs_icon('arrow') ?></a>
          </div>
        </div>
      </div>
    </section>
    <?php
}

/** Standard page meta for Business Services pages (canonical without query string). */
function bs_page_meta(string $title, string $description, string $path, array $schema = []): array
{
    return [
        'title'       => $title,
        'description' => $description,
        'canonical'   => site_url($path),
        'image'       => site_url('/assets/images/paynancial-logo.png'),
        'schema'      => $schema,
        'extra_css'   => 'css/business-services.css',
    ];
}

/** Page behaviour (search suggestions, directory filtering). Loaded once per page. */
function bs_script(): void
{
    echo '<script src="' . e(asset('js/business-services.js')) . '" defer></script>';
}

/** "Why Choose Paynancial" — dark section shared by the landing and incorporation pages. */
function bs_why_paynancial(): void
{
    $why = [
        ['expert',  'Expert Guidance',               'A team that explains structures, requirements and trade-offs in plain language before you commit.'],
        ['eye',     'Transparent Process',           'A clear scope, a written quote and visibility at every stage.'],
        ['doc',     'Structured Documentation',      'Tailored checklists and a review of every document before anything is filed.'],
        ['globe',   'Global Business Support',       'Incorporation support in India and across selected international jurisdictions.'],
        ['support', 'Post-Incorporation Assistance', 'Registrations, compliance and company changes after incorporation — and payments when you are ready.'],
    ];
    ?>
    <section class="bs-section bs-section-ink" id="why-paynancial" aria-labelledby="bs-why-p-title">
      <div class="container bs-why">
        <div class="bs-why-copy reveal">
          <span class="eyebrow">Why Paynancial</span>
          <h2 id="bs-why-p-title">Why Choose Paynancial</h2>
          <p>Business services from the team building payment infrastructure for growing businesses — so the company you set up today is ready to transact tomorrow.</p>
          <a href="<?= e(bs_enquiry_url('expert')) ?>" class="btn btn-outline bs-btn-on-ink">Talk to an Expert <?= bs_icon('arrow') ?></a>
        </div>
        <ul class="bs-why-list">
          <?php foreach ($why as [$icon, $title, $text]): ?>
          <li class="reveal"><span class="bs-why-icon"><?= bs_icon($icon) ?></span><div><strong><?= e($title) ?></strong><p><?= e($text) ?></p></div></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php
}
