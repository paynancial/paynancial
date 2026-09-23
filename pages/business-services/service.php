<?php
/**
 * /business-services/{service} — one template for every service.
 * $bs_service is resolved by the front controller from bs_services().
 *
 * Company Incorporation uses the incorporation layout (structures, why
 * Paynancial); every other service uses the standard service layout
 * (what it is, who it is for). Both share requirements, documents,
 * process, pricing approach, FAQs, related services and CTA.
 */
require_once __DIR__ . '/../../includes/business-services-ui.php';

$s = $bs_service;
$services = bs_services();
$categories = bs_categories();
$cat = $categories[$s['category']];
$isIncorporation = $s['slug'] === 'company-incorporation';
$primaryLabel = $isIncorporation ? 'Start Incorporation' : 'Get a Quote';
$primaryHref = bs_enquiry_url($s['slug']);
$expertHref = bs_enquiry_url('expert');

$bs_trail = [['Home', '/'], ['Business Services', bs_url()], [$s['name'], bs_url($s['slug'])]];
$page_meta = bs_page_meta(
    ($isIncorporation ? 'Company Incorporation — Start Your Business with Confidence' : $s['name']) . ' | Paynancial Business Services',
    $s['summary'],
    bs_url($s['slug']),
    [
        bs_breadcrumb_schema($bs_trail),
        [
            '@context' => 'https://schema.org',
            '@type'    => 'Service',
            'name'     => $s['name'],
            'description' => $s['summary'],
            'provider' => organization_schema(),
            'url'      => site_url(bs_url($s['slug'])),
        ],
        bs_faq_schema($s['faqs']),
    ]
);

if (!bs_service_search_eligible($s)) {
    $page_meta['robots'] = 'noindex, follow';
}

$sections = $isIncorporation
    ? ['structures' => 'Structures', 'process' => 'Process', 'documents' => 'Documents', 'pricing' => 'Timeline & fees', 'faqs' => 'FAQs']
    : ['overview' => 'What it is', 'who' => 'Who it’s for', 'documents' => 'Requirements', 'process' => 'Process', 'pricing' => 'Timeline & fees', 'faqs' => 'FAQs'];
?>

<!-- =============================================================== HERO -->
<section class="bs-detail-hero" aria-labelledby="bs-detail-title">
  <div class="container bs-detail-hero-grid">
    <div class="reveal">
      <?php bs_breadcrumb($bs_trail); ?>
      <span class="eyebrow"><?= e($s['eyebrow'] ?? $cat['label']) ?></span>
      <h1 id="bs-detail-title"><?= e($s['headline'] ?? $s['name']) ?></h1>
      <p class="lead"><?= $isIncorporation
          ? 'Incorporate your company with expert guidance, structured documentation and end-to-end support — from choosing a structure and registration to compliance after incorporation.'
          : e($s['summary']) ?></p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?= e($primaryHref) ?>"><?= e($primaryLabel) ?> <?= bs_icon('arrow') ?></a>
        <a class="btn btn-outline" href="<?= e($expertHref) ?>"><?= e(cta_label()) ?></a>
      </div>
      <?php if ($isIncorporation): ?>
      <ul class="bs-trust-row">
        <li><?= bs_icon('check') ?>Expert Guidance</li>
        <li><?= bs_icon('check') ?>Transparent Process</li>
        <li><?= bs_icon('check') ?>End-to-End Support</li>
      </ul>
      <?php endif; ?>
    </div>
    <aside class="bs-glance reveal" aria-label="At a glance">
      <span class="bs-kicker">At a glance</span>
      <dl>
        <div><dt>Service</dt><dd><?= e($s['name']) ?></dd></div>
        <?php if (bs_framework_public($s)): ?><div><dt>Framework</dt><dd><?= e($s['framework']) ?></dd></div><?php endif; ?>
        <div><dt>Fees</dt><dd><?= $s['fees'] ? e($s['fees']) : 'Shared in your written quote' ?></dd></div>
        <div><dt>Timeline</dt><dd><?= $s['timeline'] ? e($s['timeline']) : 'Confirmed after we review your documents' ?></dd></div>
      </dl>
      <a class="bs-text-link" href="<?= e(bs_enquiry_url('quote')) ?>">Get a Quote <?= bs_icon('arrow') ?></a>
    </aside>
  </div>
</section>

<nav class="bs-subnav" aria-label="On this page">
  <div class="container">
    <?php foreach ($sections as $id => $label): ?><a href="#<?= e($id) ?>"><?= e($label) ?></a><?php endforeach; ?>
  </div>
</nav>

<?php if ($isIncorporation): ?>
<!-- ========================================================= STRUCTURES -->
<section class="bs-section" id="structures" aria-labelledby="bs-structures-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Business Structures</span>
      <h2 id="bs-structures-title">Choose the structure that fits your business</h2>
      <p>Each structure balances liability, ownership and compliance differently. We explain the trade-offs for your situation before you decide.</p>
    </div>
    <div class="bs-structures bs-structures-detailed">
      <?php foreach ($s['structures'] as $slug): $st = $services[$slug]; ?>
      <article class="bs-structure reveal">
        <h3><?= e($st['short']) ?></h3>
        <p><?= e($st['summary']) ?></p>
        <dl class="bs-structure-meta">
          <div><dt>Suitable for</dt><dd><?= e($st['suitable']) ?></dd></div>
          <div><dt>Key requirements</dt><dd><ul><?php foreach (array_slice($st['requirements'], 0, 2) as $r) echo '<li>' . e($r) . '</li>'; ?></ul></dd></div>
        </dl>
        <a class="bs-text-link" href="<?= e(bs_url($slug)) ?>">Explore <?= e($st['short']) ?> <?= bs_icon('arrow') ?></a>
      </article>
      <?php endforeach; ?>
    </div>
    <a class="bs-global-strip reveal" href="<?= e(bs_url('global-incorporation')) ?>">
      <span class="bs-global-strip-icon"><?= bs_icon('globe') ?></span>
      <span><strong>Incorporating outside India?</strong> See what to consider, and ask us to confirm what support is available for your plans.</span>
      <span class="bs-text-link">Explore Global Incorporation <?= bs_icon('arrow') ?></span>
    </a>
  </div>
</section>

<!-- ============================================================ PROCESS -->
<section class="bs-section bs-section-tint" id="process" aria-labelledby="bs-process-title">
  <div class="container">
    <div class="bs-head center">
      <span class="eyebrow">Simple &amp; Transparent Process</span>
      <h2 id="bs-process-title">How It Works</h2>
    </div>
    <?php bs_process($s['process']); ?>
  </div>
</section>
<?php else: ?>
<!-- ========================================================= WHAT IT IS -->
<section class="bs-section" id="overview" aria-labelledby="bs-overview-title">
  <div class="container bs-overview">
    <div class="reveal">
      <span class="eyebrow">What it is</span>
      <h2 id="bs-overview-title">About <?= e($s['short']) ?></h2>
      <p class="bs-prose"><?= e($s['summary']) ?></p>
      <?php if (bs_framework_public($s)): ?><p class="bs-prose bs-framework"><?= bs_icon('register') ?><span><?= e($s['framework']) ?></span></p><?php endif; ?>
    </div>
    <ul class="bs-benefit-list">
      <?php foreach ($s['why'] as [$title, $text]): ?>
      <li class="reveal"><?= bs_icon('check') ?><div><strong><?= e($title) ?></strong><p><?= e($text) ?></p></div></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- ======================================================== WHO IT'S FOR -->
<section class="bs-section bs-section-tint" id="who" aria-labelledby="bs-who-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Who it’s for</span>
      <h2 id="bs-who-title">Is <?= e($s['short']) ?> right for you?</h2>
    </div>
    <div class="bs-who">
      <?php foreach ($s['who'] as $i => $who): ?>
      <div class="bs-who-item reveal"><span class="bs-benefit-num"><?= sprintf('%02d', $i + 1) ?></span><p><?= e($who) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================================ REQUIREMENTS + DOCUMENTS -->
<section class="bs-section" id="documents" aria-labelledby="bs-req-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow"><?= $isIncorporation ? 'Documents Required' : 'Requirements & documents' ?></span>
      <h2 id="bs-req-title">What you will need</h2>
      <p>A typical checklist. Requirements vary based on company type and circumstances, so we confirm the exact list for your case before anything is filed.</p>
    </div>
    <div class="bs-two-lists">
      <div class="bs-list-card reveal">
        <h3><?= bs_icon('expert') ?> Key requirements</h3>
        <ul class="bs-checklist"><?php foreach ($s['requirements'] as $r) echo '<li>' . bs_icon('check') . '<span>' . e($r) . '</span></li>'; ?></ul>
      </div>
      <div class="bs-list-card reveal">
        <h3><?= bs_icon('doc') ?> Documents</h3>
        <ul class="bs-checklist"><?php foreach ($s['documents'] as $d) echo '<li>' . bs_icon('check') . '<span>' . e($d) . '</span></li>'; ?></ul>
      </div>
    </div>
  </div>
</section>

<?php if ($isIncorporation): ?>
<?php bs_why_paynancial(); ?>
<?php else: ?>
<!-- ============================================================ PROCESS -->
<section class="bs-section bs-section-tint" id="process" aria-labelledby="bs-process-title">
  <div class="container">
    <div class="bs-head">
      <span class="eyebrow">Process</span>
      <h2 id="bs-process-title">How it works</h2>
    </div>
    <?php bs_process($s['process']); ?>
  </div>
</section>
<?php endif; ?>

<!-- ================================================== TIMELINE & FEES -->
<section class="bs-section" id="pricing" aria-labelledby="bs-pricing-title">
  <div class="container">
    <div class="bs-pricing">
      <div class="bs-pricing-intro reveal">
        <span class="eyebrow">Timeline &amp; pricing approach</span>
        <h2 id="bs-pricing-title">Clear scope, written quote</h2>
        <p>Government fees and processing times depend on the structure and authority involved. We do not quote a number until we understand your case.</p>
      </div>
      <div class="bs-pricing-card reveal">
        <span class="bs-pricing-icon"><?= bs_icon('clock') ?></span>
        <h3>Timeline</h3>
        <p><?= $s['timeline'] ? e($s['timeline']) : 'We share an expected timeline once your documents are reviewed, and keep you updated at each stage with the authority.' ?></p>
      </div>
      <div class="bs-pricing-card reveal">
        <span class="bs-pricing-icon"><?= bs_icon('quote') ?></span>
        <h3>Fees</h3>
        <p><?= $s['fees'] ? e($s['fees']) : 'A written quote that separates our professional fee from government and third-party charges, before any work begins.' ?></p>
        <a class="bs-text-link" href="<?= e(bs_enquiry_url('quote')) ?>">Get a Quote <?= bs_icon('arrow') ?></a>
      </div>
    </div>
  </div>
</section>

<!-- =============================================================== FAQS -->
<section class="bs-section bs-section-tint" id="faqs" aria-labelledby="bs-faq-title">
  <div class="container bs-faq-wrap">
    <div class="bs-head">
      <span class="eyebrow">FAQs</span>
      <h2 id="bs-faq-title">Frequently asked questions</h2>
      <p>Have a question that is not answered here? <a class="bs-inline-link" href="<?= e($expertHref) ?>">Talk to an expert</a>.</p>
    </div>
    <?php bs_faq($s['faqs']); ?>
  </div>
</section>

<!-- ==================================================== RELATED SERVICES -->
<section class="bs-section" aria-labelledby="bs-related-title">
  <div class="container">
    <div class="bs-head bs-head-row">
      <div>
        <span class="eyebrow">Related services</span>
        <h2 id="bs-related-title">You may also need</h2>
      </div>
      <a class="bs-text-link" href="<?= e(bs_url()) ?>#services">All Business Services <?= bs_icon('arrow') ?></a>
    </div>
    <div class="bs-related">
      <?php foreach ($s['related'] as $slug): $r = $services[$slug]; ?>
      <a class="bs-related-card reveal" href="<?= e(bs_url($slug)) ?>">
        <span class="bs-kicker"><?= e($categories[$r['category']]['label']) ?></span>
        <strong><?= e($r['name']) ?></strong>
        <span class="bs-related-go" aria-hidden="true"><?= bs_icon('arrow') ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php bs_payments_crosssell(); ?>

<?php bs_cta_band(
    $isIncorporation ? 'Ready to Start Your Business?' : 'Get started with ' . $s['short'],
    'Share your requirements and we will come back with the right checklist, a written quote and next steps.',
    $primaryLabel, $primaryHref,
    cta_label(), $expertHref
); ?>
