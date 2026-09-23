<?php
/**
 * /business-services — Business Services landing page.
 * All services and jurisdictions come from includes/business-services.php.
 */
require_once __DIR__ . '/../../includes/business-services-ui.php';

$bs_trail = [['Home', '/'], ['Business Services', bs_url()]];
$page_meta = bs_page_meta(
    'Business Services — Company Incorporation, Registration & Compliance | Paynancial',
    'Start, manage and grow your business with Paynancial Business Services: company incorporation in India and selected international jurisdictions, business registrations, trademarks and compliance support.',
    bs_url(),
    [
        bs_breadcrumb_schema($bs_trail),
        [
            '@context' => 'https://schema.org',
            '@type'    => 'Service',
            'name'     => 'Paynancial Business Services',
            'serviceType' => 'Company incorporation, business registration and compliance support',
            'provider' => organization_schema(),
            'url'      => site_url(bs_url()),
        ],
    ]
);

$popular = bs_popular_jurisdictions();

?>

<!-- =============================================================== HERO -->
<section class="bs-hero" aria-labelledby="bs-hero-title">
  <div class="container bs-hero-grid">
    <div class="bs-hero-copy reveal">
      <?php bs_breadcrumb($bs_trail); ?>
      <span class="eyebrow">Business Services</span>
      <h1 id="bs-hero-title">Start, Manage &amp; Grow Your Business</h1>
      <p class="lead">Company incorporation, registrations and compliance services designed for entrepreneurs and growing businesses — with expert guidance, a transparent process and end-to-end support from Paynancial.</p>
      <div class="hero-actions">
        <a href="<?= e(bs_url('company-incorporation')) ?>" class="btn btn-primary">Get Started Now <?= bs_icon('arrow') ?></a>
        <a href="<?= e(bs_enquiry_url('expert')) ?>" class="btn btn-outline">Talk to an Expert</a>
      </div>
      <ul class="bs-hero-points">
        <li><?= bs_icon('check') ?>Expert Guidance</li>
        <li><?= bs_icon('check') ?>Transparent Process</li>
        <li><?= bs_icon('check') ?>End-to-End Support</li>
        <li><?= bs_icon('check') ?>Global Business Support</li>
      </ul>
    </div>

    <?php include __DIR__ . '/_hero-map.php'; ?>
  </div>
</section>

<!-- =================================================== SERVICE CATEGORIES -->
<section class="bs-section" id="services" aria-labelledby="bs-services-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">What we help with</span>
      <h2 id="bs-services-title">Everything you need to set up and stay compliant</h2>
      <p>From choosing a structure to annual filings — one team, one process, and a single point of contact.</p>
    </div>
    <div class="bs-service-grid">
      <?php foreach (bs_categories() as $key => $cat) bs_service_card($key, $cat); ?>
    </div>
  </div>
</section>

<!-- ============================================ GLOBAL INCORPORATION + SEARCH -->
<section class="bs-section bs-section-tint" id="global" aria-labelledby="bs-global-title">
  <div class="container">
    <div class="bs-global">
      <div class="bs-global-copy reveal">
        <span class="eyebrow">Global Company Incorporation</span>
        <h2 id="bs-global-title">Incorporate Your Business Anywhere in the World</h2>
        <p>Paynancial provides incorporation support across selected international jurisdictions. Tell us where you want to operate and we will guide you through structure, documentation and filing — coordinated by one team.</p>
        <a class="bs-text-link" href="<?= e(bs_url('global-incorporation')) ?>">Explore Global Incorporation <?= bs_icon('arrow') ?></a>
      </div>
      <div class="bs-finder reveal">
        <h3 class="bs-finder-title">Where do you want to incorporate?</h3>
        <?php bs_jurisdiction_search('', 'bs-hero-search'); ?>
        <div class="bs-finder-foot">
          <span class="bs-finder-hint">Try “Singapore”, “UAE” or “Europe”</span>
          <a class="bs-text-link" href="<?= e(bs_jurisdiction_url()) ?>">Find a Jurisdiction <?= bs_icon('arrow') ?></a>
        </div>
      </div>
    </div>

    <div class="bs-head bs-head-row" id="popular">
      <div>
        <span class="eyebrow">Popular Locations</span>
        <h2>Incorporate in Leading Global Jurisdictions</h2>
        <p>Choose from our popular destinations for international company incorporation.</p>
      </div>
      <a class="bs-text-link" href="<?= e(bs_jurisdiction_url()) ?>">View All Jurisdictions <?= bs_icon('arrow') ?></a>
    </div>
    <div class="bs-jur-grid">
      <?php foreach ($popular as $slug => $j) bs_jurisdiction_card($slug, $j); ?>
    </div>

    <?php bs_other_jurisdiction_card(); ?>
  </div>
</section>

<!-- =================================================== EXPLORE BY REGION -->
<section class="bs-section" id="regions" aria-labelledby="bs-regions-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Explore by Region</span>
      <h2 id="bs-regions-title">Browse jurisdictions by region</h2>
      <p>Start from the part of the world you want to do business in.</p>
    </div>
    <?php bs_region_filter(); ?>
  </div>
</section>

<!-- ======================================================== HOW IT WORKS -->
<section class="bs-section bs-section-tint" id="process" aria-labelledby="bs-process-title">
  <div class="container">
    <div class="bs-head center">
      <span class="eyebrow">Simple &amp; Transparent Process</span>
      <h2 id="bs-process-title">How It Works</h2>
      <p>A clear path from first conversation to a fully incorporated business.</p>
    </div>
    <?php bs_process(bs_services()['company-incorporation']['process']); ?>
  </div>
</section>

<?php bs_why_paynancial(); ?>

<?php bs_payments_crosssell(); ?>

<?php bs_cta_band(
    'Ready to start your business?',
    'Share your requirements and our team will outline the right structure, documents and next steps.',
    'Start Your Business', bs_url('company-incorporation'),
    'Get a Quote', bs_enquiry_url('quote')
); ?>

<?php bs_script(); ?>
