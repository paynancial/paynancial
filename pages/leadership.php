<?php
$page_meta = [
    'title' => 'Leadership | Paynancial',
    'description' => 'Meet the leadership team at Paynancial Technology Pvt. Ltd.',
    'schema' => organization_schema(),
];

function paynancial_initials(string $name): string
{
    $initials = '';
    foreach (explode(' ', trim($name)) as $part) {
        if ($part !== '') {
            $initials .= strtoupper($part[0]);
        }
    }
    return $initials;
}

$leaders = [
    [
        'slug'     => 'renuka-devi',
        'number'   => '01',
        'name'     => 'Renuka Devi',
        'title'    => 'Director',
        'linkedin' => 'https://www.linkedin.com/in/coolrenukadevi/',
        'quote'    => "Governance isn't paperwork bolted onto a business — it's the discipline that lets a fintech move fast without ever putting customer trust at risk.",
        'bio'      => [
            'Renuka Devi directs strategy and governance at Paynancial Technology Pvt. Ltd., setting the standards the company holds itself to before a regulator, a partner or a customer ever has to ask.',
            "Her work sits at the intersection of compliance and growth — reviewing new products, partnerships and processes for accountability by design, so the platform can scale into new markets without the trust it depends on ever becoming negotiable.",
        ],
        'focus'    => ['Governance & Compliance', 'Strategic Direction', 'Regulatory Accountability', 'Partner Trust'],
        'journey'  => [
            ['Sets direction', 'Defines the strategy and standards the company is measured against.'],
            ['Owns governance', 'Accountable for compliance, internal controls and risk oversight.'],
            ['Reviews every launch', 'Signs off on new products and partnerships before they reach customers.'],
            ['Builds long-term trust', 'Works directly with regulators, banking partners and enterprise clients on accountability.'],
        ],
    ],
    [
        'slug'     => 'anisha-bharti',
        'number'   => '02',
        'name'     => 'Anisha Bharti',
        'title'    => 'Director',
        'linkedin' => 'https://www.linkedin.com/in/anishabharti',
        'quote'    => "A payments platform is judged in the moments customers don't see — how fast support responds, how clearly a dashboard explains a settlement.",
        'bio'      => [
            "Anisha Bharti directs product, platform and day-to-day operations at Paynancial, translating the company's strategy into the software merchants and partners actually use.",
            'She focuses on the experience layer of the business — onboarding, dashboards, support — treating every point where a customer touches the platform as a measure of whether the company is keeping its promises.',
        ],
        'focus'    => ['Product & Platform', 'Operations', 'Customer Experience', 'Team & Culture'],
        'journey'  => [
            ['Shapes the roadmap', 'Prioritises what gets built next across the platform.'],
            ['Runs operations', 'Owns the day-to-day processes that keep the business running.'],
            ['Champions the customer', 'Reviews onboarding, dashboards and support from the user\'s side.'],
            ['Grows the team', 'Builds the internal culture that shows up in how customers are treated.'],
        ],
    ],
];
?>
<section class="hero" style="padding-top:72px;">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Leadership</span>
      <h1>The people accountable for how Paynancial is run.</h1>
      <p class="lead">Governance, strategy and oversight at Paynancial Technology Pvt. Ltd. — two directors, two different vantage points on the same business.</p>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Leadership</span>
      <h2>Board of Directors</h2>
    </div>
    <div class="leader-list">
      <?php foreach ($leaders as $leader): ?>
        <article class="leader-card reveal">
          <div class="leader-card-top">
            <div class="leader-avatar">
              <img src="/assets/images/leadership/<?= e($leader['slug']) ?>.jpg" class="js-avatar-photo"
                   alt="<?= e($leader['name']) ?>, <?= e($leader['title']) ?> at Paynancial" loading="lazy">
              <span class="leader-avatar-fallback"><?= e(paynancial_initials($leader['name'])) ?></span>
            </div>
            <div class="leader-heading">
              <span class="leader-num"><?= e($leader['number']) ?></span>
              <h3><?= e($leader['name']) ?></h3>
              <span class="role"><?= e($leader['title']) ?></span>
              <a class="li-link" href="<?= e($leader['linkedin']) ?>" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.03-1.85-3.03-1.85 0-2.14 1.45-2.14 2.94v5.66H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45z"/></svg>
                View LinkedIn profile
              </a>
            </div>
          </div>

          <blockquote class="leader-quote">&ldquo;<?= e($leader['quote']) ?>&rdquo;</blockquote>

          <div class="leader-bio-text">
            <?php foreach ($leader['bio'] as $para): ?>
              <p><?= e($para) ?></p>
            <?php endforeach; ?>
          </div>

          <div class="focus-tags">
            <?php foreach ($leader['focus'] as $tag): ?>
              <span class="focus-tag"><?= e($tag) ?></span>
            <?php endforeach; ?>
          </div>

          <div class="leader-journey-head">
            <span class="eyebrow">Leadership Journey</span>
          </div>
          <div class="journey journey-mini">
            <?php foreach ($leader['journey'] as $i => $step): ?>
              <div class="journey-step">
                <div class="num"><?= $i + 1 ?></div>
                <strong><?= e($step[0]) ?></strong>
                <span><?= e($step[1]) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="registry-strip cta-only">
  <div class="container">
    <div class="reveal">
      <h2>Get in touch</h2>
      <p>Questions for the leadership team or the company at large — reach out and someone will get back to you.</p>
      <div class="hero-actions" style="margin-top:24px;">
        <a href="/contact" class="btn btn-primary">Contact Us</a>
      </div>
    </div>
  </div>
</div>
