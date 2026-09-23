<?php
/**
 * /business-services/jurisdictions/{slug} — one template for every
 * jurisdiction. $bs_jurisdiction is resolved by the front controller.
 *
 * Jurisdiction-specific legal, tax and regulatory content is only shown
 * when it has been verified and added to the dataset ('overview',
 * 'structures', 'considerations'). Until then each section explains what
 * will be confirmed during consultation — nothing is auto-generated.
 */
require_once __DIR__ . '/../../includes/business-services-ui.php';

$j = $bs_jurisdiction;
$name = $j['name'];
$pos = bs_map_position($j);
$regionNames = array_map(fn ($r) => bs_regions()[$r] ?? $r, $j['regions']);
$enquiry = bs_enquiry_url($j['slug']);
$approved = bs_jurisdiction_approved($j);
// Research vs service: an unapproved jurisdiction shows jurisdiction information
// only — no service wording, no "Get a Quote", no incorporation process.
$promote = bs_jurisdiction_promotable($j);

$faqs = $approved ? [
    ["Can Paynancial help me incorporate in {$name}?", $approved
        ? "Yes. {$name} is one of the jurisdictions where we support international company incorporation. Share your requirements and we will outline the options, documents and next steps."
        : "Tell us about your plans for {$name} and our team will confirm whether and how we can support your incorporation there, before any work begins."],
    ["Which company structure should I use in {$name}?", "That depends on your business activity, ownership and plans. We explain the available structures and their ongoing obligations during your consultation."],
    ["How much does incorporation in {$name} cost and how long does it take?", "Government fees, registered agent charges and processing times vary. You receive a written quote and an expected timeline once we understand your case."],
    ['Do you provide tax advice?', "We recommend obtaining independent tax advice in your home country and in {$name} before you incorporate. We coordinate the incorporation itself."],
] : [
    ["Does Paynancial offer company incorporation in {$name}?", "Not confirmed. Paynancial has not confirmed incorporation support in {$name}. You can ask our team about availability; nothing on this page is an offer of service."],
    ["Where can I find official information about {$name}?", "From the official government sources for {$name}. Paynancial will summarise them here, with links and review dates, only once they have been verified."],
    ['Is this page legal or tax advice?', 'No. It is general jurisdiction information. Take independent professional advice in your home country and in the jurisdiction before you act.'],
];

$related = [];
foreach (bs_jurisdictions() as $slug => $other) {
    if ($slug !== $j['slug'] && array_intersect($other['regions'], $j['regions'])) {
        $related[$slug] = $other;
    }
}
$related = array_slice($related, 0, 3, true);

$bs_trail = [['Home', '/'], ['Business Services', bs_url()], ['Jurisdictions', bs_jurisdiction_url()], [$name, bs_jurisdiction_url($j['slug'])]];
$page_meta = bs_page_meta(
    $approved ? "Company Incorporation in {$name} | Paynancial Business Services" : "{$name}: Jurisdiction Information | Paynancial Business Services",
    $approved ? "Incorporate a company in {$name} with Paynancial. Structures, requirements, documents and process — with expert guidance from first conversation to incorporation."
        : "General information about {$name} as a company jurisdiction. Paynancial has not confirmed incorporation support in {$name}; official-source content is being prepared.",
    bs_jurisdiction_url($j['slug']),
    // FAQ markup only once the page is approved and its FAQs are jurisdiction-specific.
    $approved ? [bs_breadcrumb_schema($bs_trail), bs_faq_schema($faqs)] : [bs_breadcrumb_schema($bs_trail)]
);
if (!$approved) {
    // Pending the Jurisdiction Approval Matrix: usable by visitors, kept out of search.
    $page_meta['robots'] = 'noindex, follow';
}

$considerations = $j['considerations'] ?? [
    ['Business activity & licensing', "Some activities need a licence or approval in addition to incorporation. We identify what applies to your activity in {$name}."],
    ['Banking & payments', 'Opening accounts and accepting payments involve separate onboarding by banks and payment providers. Plan for these early.'],
    ['Local presence', 'Some jurisdictions and activities require a local registered office, agent, director or other local presence. We confirm what is needed.'],
    ['Tax & reporting', "Tax treatment depends on your circumstances. We recommend independent tax advice in your home country and in {$name}."],
];
?>

<!-- =============================================================== HERO -->
<section class="bs-detail-hero bs-jur-hero" aria-labelledby="bs-jur-title">
  <div class="container bs-detail-hero-grid">
    <div class="reveal">
      <?php bs_breadcrumb($bs_trail); ?>
      <span class="eyebrow"><?= $approved ? 'International Incorporation' : 'Jurisdiction information' ?></span>
      <h1 id="bs-jur-title"><span class="bs-jur-hero-flag"><?= bs_flag($j, 'bs-flag bs-flag-lg') ?></span><?= $approved ? 'Company Incorporation in ' . e($name) : e($name) . ': jurisdiction information' ?></h1>
      <p class="lead"><?= e($j['descriptor']) ?> <?= $approved
          ? 'Paynancial supports incorporation in ' . e($name) . ' with expert guidance, structured documentation and end-to-end coordination.'
          : 'This page holds general information about ' . e($name) . '. Paynancial has not confirmed incorporation support here.' ?></p>
      <div class="hero-actions">
        <?php if ($promote): ?>
        <a class="btn btn-primary" href="<?= e($enquiry) ?>"><?= e(cta_label()) ?> <?= bs_icon('arrow') ?></a>
        <a class="btn btn-outline" href="<?= e(bs_enquiry_url('quote')) ?>">Get a Quote</a>
        <?php else: ?>
        <a class="btn btn-outline" href="<?= e($enquiry) ?>">Ask about availability <?= bs_icon('arrow') ?></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="bs-jur-hero-visual reveal" style="--fx:<?= $pos['x'] ?>;--fy:<?= $pos['y'] ?>;" aria-hidden="true">
      <img class="bs-jur-map" src="<?= e(asset('images/business-services/world-dots.svg')) ?>" alt="">
      <span class="bs-jur-pin is-lg"></span>
      <span class="bs-jur-hero-tag"><?= bs_flag($j, 'bs-flag bs-flag-sm') ?><?= e($j['short'] ?? $name) ?></span>
      <span class="bs-jur-coords"><?= e(bs_format_coords($j)) ?></span>
    </div>
  </div>
</section>

<nav class="bs-subnav" aria-label="On this page">
  <div class="container">
    <a href="#overview">Overview</a>
    <?php if ($approved): ?>
    <a href="#structures">Structures</a>
    <a href="#requirements">Requirements</a>
    <a href="#process">Process</a>
    <a href="#considerations">Considerations</a>
    <a href="#compliance">Compliance</a>
    <?php else: ?>
    <a href="#availability">Service availability</a>
    <a href="#research">Research status</a>
    <?php endif; ?>
    <a href="#faqs">FAQs</a>
  </div>
</nav>

<!-- =========================================================== OVERVIEW -->
<section class="bs-section" id="overview" aria-labelledby="bs-ov-title">
  <div class="container bs-overview">
    <div class="reveal">
      <span class="eyebrow">Jurisdiction overview</span>
      <h2 id="bs-ov-title"><?= e($name) ?> at a glance</h2>
      <p class="bs-prose"><?= e($j['overview'] ?? ($approved
          ? $j['descriptor'] . ' Incorporation requirements, available structures and ongoing obligations depend on your business activity and plans — our team confirms these with you before you commit.'
          : $j['descriptor'] . ' Company structures, registration requirements, licensing, tax and ongoing obligations in ' . $name . ' will be summarised here from official sources once verified.')) ?></p>
    </div>
    <dl class="bs-facts reveal">
      <div><dt>Region</dt><dd><?= e(implode(', ', $regionNames)) ?></dd></div>
      <div><dt>Capital / seat of government</dt><dd><?= e($j['capital']) ?></dd></div>
      <div><dt>Coordinates</dt><dd class="mono"><?= e(bs_format_coords($j)) ?></dd></div>
      <div><dt>Paynancial service</dt><dd><?= $approved ? 'Incorporation, documentation &amp; post-incorporation assistance' : 'Not confirmed' ?></dd></div>
    </dl>
  </div>
</section>

<?php if (!$approved): ?>
<!-- ================================================ SERVICE AVAILABILITY -->
<section class="bs-section bs-section-tint" id="availability" aria-labelledby="bs-av-title">
  <div class="container">
    <div class="bs-note-card reveal">
      <span class="bs-note-icon"><?= bs_icon('expert') ?></span>
      <div>
        <span class="eyebrow">Paynancial service availability</span>
        <h2 id="bs-av-title">Not confirmed for <?= e($name) ?></h2>
        <p>Paynancial has not confirmed that it provides company incorporation in <?= e($name) ?>. This page is jurisdiction information, not a service offer. If you are considering <?= e($name) ?>, you can ask our team whether support is available.</p>
      </div>
      <a class="bs-text-link" href="<?= e($enquiry) ?>">Ask about availability <?= bs_icon('arrow') ?></a>
    </div>
  </div>
</section>

<!-- ===================================================== RESEARCH STATUS -->
<section class="bs-section" id="research" aria-labelledby="bs-rs-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Research status</span>
      <h2 id="bs-rs-title">What this page will cover</h2>
      <p>Each topic is published only after it has been checked against the official sources for <?= e($name) ?>. Professional review: Pending.</p>
    </div>
    <ul class="bs-checklist bs-research-list">
      <?php foreach (['Regulatory authorities', 'Company types', 'Registration requirements and documents', 'Business activities and licensing', 'Tax framework', 'Ongoing compliance', 'Beneficial ownership and AML', 'Banking considerations', 'India-side considerations', 'Official sources'] as $topic): ?>
      <li><?= bs_icon('doc') ?><span><?= e($topic) ?> — source verification pending</span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php else: ?>
<!-- ========================================================= STRUCTURES -->
<section class="bs-section bs-section-tint" id="structures" aria-labelledby="bs-st-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Company structures</span>
      <h2 id="bs-st-title">Structures available in <?= e($name) ?></h2>
    </div>
    <?php if (!empty($j['structures'])): ?>
      <div class="bs-structures">
        <?php foreach ($j['structures'] as [$title, $text]): ?>
        <div class="bs-structure reveal"><h3><?= e($title) ?></h3><p><?= e($text) ?></p></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="bs-note-card reveal">
        <span class="bs-note-icon"><?= bs_icon('expert') ?></span>
        <div>
          <h3>Structures are confirmed with you</h3>
          <p>The right structure in <?= e($name) ?> depends on your business activity, ownership and where you plan to operate. During your consultation we outline the options that fit, and the obligations that come with each.</p>
        </div>
        <a class="bs-text-link" href="<?= e($enquiry) ?>"><?= e(cta_label()) ?> <?= bs_icon('arrow') ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============================================ REQUIREMENTS + DOCUMENTS -->
<section class="bs-section" id="requirements" aria-labelledby="bs-req-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Key requirements &amp; documents</span>
      <h2 id="bs-req-title">What you will typically need</h2>
      <p>A general checklist for international incorporation. Requirements vary based on company type and circumstances — the exact list for <?= e($name) ?> is confirmed for your case.</p>
    </div>
    <div class="bs-two-lists">
      <div class="bs-list-card reveal">
        <h3><?= bs_icon('expert') ?> Requirements</h3>
        <ul class="bs-checklist">
          <?php foreach ([
              'Details of proposed shareholders, directors and ultimate beneficial owners',
              'Proposed company name(s)',
              'A description of the business activity and intended markets',
              "A registered address or local agent in {$name}, where required",
          ] as $r) echo '<li>' . bs_icon('check') . '<span>' . e($r) . '</span></li>'; ?>
        </ul>
      </div>
      <div class="bs-list-card reveal">
        <h3><?= bs_icon('doc') ?> Documents</h3>
        <ul class="bs-checklist">
          <?php foreach ([
              'Passport copies of shareholders, directors and beneficial owners',
              'Proof of residential address',
              'Professional or bank references, where required',
              'Information on the source of funds, where required',
              'Certified, notarised or apostilled copies, where the jurisdiction requires them',
          ] as $d) echo '<li>' . bs_icon('check') . '<span>' . e($d) . '</span></li>'; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================ PROCESS -->
<section class="bs-section bs-section-tint" id="process" aria-labelledby="bs-pr-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Process</span>
      <h2 id="bs-pr-title">Incorporation process in <?= e($name) ?></h2>
    </div>
    <?php bs_process([
        ['Share your requirements', 'Tell us about your business, the people involved and your plans for ' . $name . '.'],
        ['Confirm structure', 'We outline the structures that fit and what each involves.'],
        ['Complete documentation', 'We send a tailored checklist and review every document before submission.'],
        ['Incorporation & filing', 'The application is filed with the relevant registry, working with local partners where required.'],
        ['Post-incorporation support', 'Next steps such as registered office renewals, compliance and — when you are ready — payments.'],
    ]); ?>
  </div>
</section>

<!-- ===================================================== CONSIDERATIONS -->
<section class="bs-section" id="considerations" aria-labelledby="bs-kc-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Key considerations</span>
      <h2 id="bs-kc-title">Things to plan for</h2>
    </div>
    <div class="bs-considerations">
      <?php foreach ($considerations as [$title, $text]): ?>
      <div class="bs-consideration reveal"><h3><?= e($title) ?></h3><p><?= e($text) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========================================================= COMPLIANCE -->
<section class="bs-section bs-section-tint" id="compliance" aria-labelledby="bs-co-title">
  <div class="container">
    <div class="bs-note-card reveal">
      <span class="bs-note-icon"><?= bs_icon('comply') ?></span>
      <div>
        <span class="eyebrow">Compliance considerations</span>
        <h2 id="bs-co-title">Staying compliant after incorporation</h2>
        <p>Ongoing obligations — such as annual returns, registered office or agent renewals, accounting records and filings — vary by jurisdiction and structure. We set out what applies in <?= e($name) ?> before you incorporate, so there are no surprises later.</p>
      </div>
    </div>
  </div>
</section>

<?php endif; ?>

<!-- =============================================================== FAQS -->
<section class="bs-section" id="faqs" aria-labelledby="bs-faq-title">
  <div class="container bs-faq-wrap">
    <div class="bs-head">
      <span class="eyebrow">FAQs</span>
      <h2 id="bs-faq-title">Incorporating in <?= e($name) ?>: common questions</h2>
    </div>
    <?php bs_faq($faqs); ?>
  </div>
</section>

<!-- ============================================================ RELATED -->
<section class="bs-section bs-section-tint" aria-labelledby="bs-rel-title">
  <div class="container">
    <div class="bs-head bs-head-row">
      <div>
        <span class="eyebrow">Related</span>
        <h2 id="bs-rel-title"><?= $related ? 'Other jurisdictions in ' . e($regionNames[0]) : 'Related' ?></h2>
      </div>
      <a class="bs-text-link" href="<?= e(bs_jurisdiction_url()) ?>">View All Jurisdictions <?= bs_icon('arrow') ?></a>
    </div>
    <?php if ($related): ?>
    <div class="bs-jur-grid">
      <?php foreach ($related as $slug => $other) bs_jurisdiction_card($slug, $other); ?>
    </div>
    <?php endif; ?>
    <div class="bs-related bs-related-spaced">
      <?php foreach ([
          ['International', 'Global Incorporation', bs_url('global-incorporation')],
          ['Start a Business', 'Company Incorporation', bs_url('company-incorporation')],
          ['Jurisdiction Directory', 'Find another jurisdiction', bs_jurisdiction_url()],
      ] as [$kicker, $label, $href]): ?>
      <a class="bs-related-card reveal" href="<?= e($href) ?>">
        <span class="bs-kicker"><?= e($kicker) ?></span>
        <strong><?= e($label) ?></strong>
        <span class="bs-related-go" aria-hidden="true"><?= bs_icon('arrow') ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php bs_payments_crosssell(); ?>

<?php if ($promote): bs_cta_band(
    "Planning to incorporate in {$name}?",
    'Tell us about your business and our team will outline the structure, documents and next steps.',
    cta_label(), $enquiry,
    'Get a Quote', bs_enquiry_url('quote')
); else: bs_cta_band(
    "Considering {$name}?",
    "Paynancial has not confirmed incorporation support in {$name}. Ask our team what is available.",
    'Ask about availability', $enquiry,
    'View all jurisdictions', bs_jurisdiction_url()
); endif; ?>
