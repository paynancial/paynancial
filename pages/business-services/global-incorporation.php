<?php
/**
 * /business-services/global-incorporation — international incorporation
 * overview. Jurisdictions come from bs_jurisdictions(); no jurisdiction-
 * specific legal, tax or regulatory claims are made on this page.
 */
require_once __DIR__ . '/../../includes/business-services-ui.php';

$bs_trail = [['Home', '/'], ['Business Services', bs_url()], ['Global Incorporation', bs_url('global-incorporation')]];

$faqs = [
    ['Which jurisdictions do you support?', 'The jurisdictions listed in our directory are the popular destinations we support today. If the one you are considering is not listed, speak with our team about your requirements.'],
    ['Which company structure will I need?', 'Structures vary by jurisdiction and depend on your business activity, ownership and plans. We outline the options that fit during your consultation.'],
    ['How much does international incorporation cost and how long does it take?', 'Government fees, registered agent charges and processing times differ between jurisdictions. You receive a written quote and an expected timeline once we understand your case.'],
    ['Will incorporating abroad give me a bank account, residency or a visa?', 'No. Incorporation is separate from banking, residency and visa processes, each of which has its own requirements and approvals. We can explain what is involved so you can plan for it.'],
    ['Do you provide tax advice?', 'We coordinate the incorporation itself. We recommend obtaining independent tax advice in your home country and in the jurisdiction you choose.'],
];

$page_meta = bs_page_meta(
    'Global Company Incorporation — Incorporate Your Business Globally | Paynancial',
    'Explore international jurisdictions with structured incorporation support and expert guidance from Paynancial — from the UAE and Singapore to the United Kingdom.',
    bs_url('global-incorporation'),
    [
        bs_breadcrumb_schema($bs_trail),
        [
            '@context' => 'https://schema.org',
            '@type'    => 'Service',
            'name'     => 'International company incorporation',
            'provider' => organization_schema(),
            'url'      => site_url(bs_url('global-incorporation')),
        ],
        bs_faq_schema($faqs),
    ]
);

$popular = bs_popular_jurisdictions();
$featured = array_slice($popular, 0, 8, true);
?>

<!-- =============================================================== HERO -->
<section class="bs-hero bs-hero-global" aria-labelledby="bs-gi-title">
  <div class="container bs-hero-grid">
    <div class="bs-hero-copy reveal">
      <?php bs_breadcrumb($bs_trail); ?>
      <span class="eyebrow">Global Company Incorporation</span>
      <h1 id="bs-gi-title">Incorporate Your Business Globally</h1>
      <p class="lead">Explore international jurisdictions with structured incorporation support and expert guidance — coordinated end to end by one Paynancial team.</p>
      <div class="bs-hero-finder">
        <p class="bs-hero-finder-label">Where do you want to incorporate?</p>
        <?php bs_jurisdiction_search('', 'bs-gi-search'); ?>
      </div>
      <div class="bs-hero-links">
        <a class="bs-text-link" href="<?= e(bs_jurisdiction_url()) ?>">Find a Jurisdiction <?= bs_icon('arrow') ?></a>
        <a class="bs-text-link" href="<?= e(bs_enquiry_url('expert')) ?>">Talk to an Expert <?= bs_icon('arrow') ?></a>
      </div>
    </div>
    <?php include __DIR__ . '/_hero-map.php'; ?>
  </div>
</section>

<!-- ==================================================== POPULAR LOCATIONS -->
<section class="bs-section" id="popular" aria-labelledby="bs-pop-title">
  <div class="container">
    <div class="bs-head bs-head-row">
      <div>
        <span class="eyebrow">Popular Locations</span>
        <h2 id="bs-pop-title">Incorporate in Leading Global Jurisdictions</h2>
        <p>Choose from our popular destinations for international company incorporation.</p>
      </div>
      <a class="bs-text-link" href="<?= e(bs_jurisdiction_url()) ?>">View All Jurisdictions <?= bs_icon('arrow') ?></a>
    </div>
    <div class="bs-jur-grid">
      <?php foreach ($featured as $slug => $j) bs_jurisdiction_card($slug, $j); ?>
    </div>
    <?php bs_other_jurisdiction_card(); ?>
  </div>
</section>

<!-- =================================================== BROWSE BY REGION -->
<section class="bs-section bs-section-tint" id="regions" aria-labelledby="bs-reg-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Browse by Region</span>
      <h2 id="bs-reg-title">Explore jurisdictions by region</h2>
      <p>Start from the part of the world you want to do business in.</p>
    </div>
    <?php bs_region_filter(); ?>
  </div>
</section>

<!-- ========================================================= STRUCTURES -->
<section class="bs-section" id="structures" aria-labelledby="bs-gst-title">
  <div class="container bs-overview">
    <div class="reveal">
      <span class="eyebrow">Company Structures</span>
      <h2 id="bs-gst-title">The right structure depends on the jurisdiction — and on you</h2>
      <p class="bs-prose">Requirements vary based on company type and circumstances. Each jurisdiction offers its own set of company forms, and the right one depends on what the company will do, who will own and manage it, and where it will operate.</p>
      <p class="bs-prose">Before anything is filed, we outline the structures available in your chosen jurisdiction and the ongoing obligations that come with each.</p>
    </div>
    <div class="bs-considerations bs-considerations-stack">
      <div class="bs-consideration reveal"><h3>Business activity</h3><p>What the company will do determines which forms are available and whether any licence is needed.</p></div>
      <div class="bs-consideration reveal"><h3>Ownership &amp; management</h3><p>Who the shareholders, directors and beneficial owners are — and where they are based.</p></div>
      <div class="bs-consideration reveal"><h3>Where you will operate</h3><p>Your customers, suppliers and team locations shape which setup is practical.</p></div>
    </div>
  </div>
</section>

<!-- ============================================================ PROCESS -->
<section class="bs-section bs-section-tint" id="process" aria-labelledby="bs-gpr-title">
  <div class="container">
    <div class="bs-head center">
      <span class="eyebrow">Simple &amp; Transparent Process</span>
      <h2 id="bs-gpr-title">How International Incorporation Works</h2>
    </div>
    <?php bs_process([
        ['Share Requirements', 'Tell us about your business, the people involved and where you want to operate.'],
        ['Choose Jurisdiction', 'We help you compare the jurisdictions you are considering against your plans.'],
        ['Documentation', 'A tailored checklist, with every document reviewed before submission.'],
        ['Incorporation & Filing', 'The application is filed with the relevant registry, working with local partners where required.'],
        ['Post-Incorporation Support', 'Ongoing compliance, company changes and — when you are ready — accepting payments with Paynancial.'],
    ]); ?>
  </div>
</section>

<!-- ============================================ DOCUMENTATION + COMPLIANCE -->
<section class="bs-section" id="documentation" aria-labelledby="bs-gdoc-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Documentation &amp; Compliance</span>
      <h2 id="bs-gdoc-title">What to prepare, and what comes after</h2>
      <p>A general guide. The exact list is confirmed for your jurisdiction and structure.</p>
    </div>
    <div class="bs-two-lists">
      <div class="bs-list-card reveal">
        <h3><?= bs_icon('doc') ?> Documentation</h3>
        <ul class="bs-checklist">
          <?php foreach ([
              'Passport copies of shareholders, directors and beneficial owners',
              'Proof of residential address',
              'A description of the business activity and intended markets',
              'Professional or bank references and source-of-funds information, where required',
              'Certified, notarised or apostilled copies, where the jurisdiction requires them',
          ] as $d) echo '<li>' . bs_icon('check') . '<span>' . e($d) . '</span></li>'; ?>
        </ul>
      </div>
      <div class="bs-list-card reveal">
        <h3><?= bs_icon('comply') ?> Ongoing compliance</h3>
        <ul class="bs-checklist">
          <?php foreach ([
              'Annual returns or renewals with the registry, where applicable',
              'Registered office or registered agent renewals, where applicable',
              'Accounting records and financial filings, as the jurisdiction requires',
              'Keeping shareholder, director and beneficial-owner records up to date',
          ] as $d) echo '<li>' . bs_icon('check') . '<span>' . e($d) . '</span></li>'; ?>
        </ul>
        <p class="bs-list-note">Obligations vary by jurisdiction and structure — we set out what applies before you incorporate.</p>
      </div>
    </div>
  </div>
</section>

<!-- =============================================================== FAQS -->
<section class="bs-section bs-section-tint" id="faqs" aria-labelledby="bs-gfaq-title">
  <div class="container bs-faq-wrap">
    <div class="bs-head">
      <span class="eyebrow">FAQs</span>
      <h2 id="bs-gfaq-title">International incorporation: common questions</h2>
    </div>
    <?php bs_faq($faqs); ?>
  </div>
</section>

<?php bs_payments_crosssell(); ?>

<?php bs_cta_band(
    'Planning to incorporate internationally?',
    'Tell us where you want to operate and our team will outline the structure, documents and next steps.',
    'Talk to an Expert', bs_enquiry_url('expert'),
    'Find a Jurisdiction', bs_jurisdiction_url()
); ?>

<?php bs_script(); ?>
