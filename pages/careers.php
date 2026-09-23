<?php
$page_meta = [
    'title' => 'Careers | Paynancial Technology Pvt. Ltd.',
    'description' => 'Open roles at Paynancial across Technology, Finance, Admin, HR and Legal.',
];

$departments = ['Technology', 'Finance', 'Admin', 'HR', 'Legal'];

$jobs = [];
try {
    $jobs = db()->query(
        "SELECT id, title, department, location, employment_type FROM job_posts WHERE status = 'open' ORDER BY created_at DESC LIMIT 100"
    )->fetchAll();
} catch (Throwable $e) {
    $jobs = [];
}

$counts = ['All' => count($jobs)];
foreach ($departments as $d) {
    $counts[$d] = 0;
}
foreach ($jobs as $job) {
    $dept = $job['department'] ?? '';
    if (isset($counts[$dept])) {
        $counts[$dept]++;
    }
}
?>
<section class="hero" style="padding-top:72px;">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Careers</span>
      <h1>Help build the plumbing behind digital payments.</h1>
      <p class="lead">We're a technology-first FinTech company hiring across engineering, finance, admin, HR and legal — people who want real ownership over what they build and run.</p>
      <div class="hero-trust">
        <div class="hero-stat"><span class="num mono"><?= count($jobs) ?></span><div class="label">Open Roles</div></div>
        <div class="hero-stat"><span class="num mono"><?= count($departments) ?></span><div class="label">Departments</div></div>
        <div class="hero-stat"><span class="num mono">IN</span><div class="label">Based in India</div></div>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Overview</span>
      <h2>Departments we hire across</h2>
    </div>
    <div class="dept-grid reveal">
      <?php foreach ($departments as $d): ?>
        <div class="dept-card">
          <span class="mono"><?= $counts[$d] ?></span>
          <div class="name"><?= e($d) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow"><?= count($jobs) ?> Positions</span>
      <h2>Current openings</h2>
    </div>

    <?php if (empty($jobs)): ?>
      <div class="module-stub reveal">
        <strong>No open positions listed right now</strong>
        Check back soon, or send your resume to hello@paynancial.com and we'll keep it on file.
      </div>
    <?php else: ?>
      <div class="filters reveal" role="tablist" aria-label="Filter roles by department">
        <button type="button" class="filter-btn active" data-filter="All">All <span class="count">(<?= $counts['All'] ?>)</span></button>
        <?php foreach ($departments as $d): ?>
          <button type="button" class="filter-btn" data-filter="<?= e($d) ?>"><?= e($d) ?> <span class="count">(<?= $counts[$d] ?>)</span></button>
        <?php endforeach; ?>
      </div>

      <div class="jobs reveal">
        <?php foreach ($jobs as $job): ?>
          <div class="job-row" data-dept="<?= e($job['department'] ?? '') ?>">
            <span class="job-title"><?= e($job['title']) ?></span>
            <span class="job-dept"><?= e($job['department'] ?? '—') ?></span>
            <span class="job-location"><?= e($job['location'] ?? '—') ?></span>
            <span class="job-type"><?= e($job['employment_type'] ?? '—') ?></span>
            <a class="job-apply" href="/contact?intent=career&job=<?= (int) $job['id'] ?>">Apply →</a>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="empty-state" data-jobs-empty style="display:none;">No roles open in this department right now — check back soon.</div>
    <?php endif; ?>
  </div>
</section>

<div class="registry-strip cta-only">
  <div class="container">
    <div class="reveal">
      <h2>Don't see your role?</h2>
      <p>Send us your resume anyway — we keep every application on file and reach out when something fits.</p>
      <div class="hero-actions" style="margin-top:24px;">
        <a href="/contact?intent=career" class="btn btn-primary">Send Your Resume</a>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($jobs)): ?>
<script nonce="<?= csp_nonce() ?>">
(function () {
  var buttons = document.querySelectorAll('.filter-btn');
  var rows = document.querySelectorAll('.job-row');
  var emptyState = document.querySelector('[data-jobs-empty]');
  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      buttons.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      var filter = btn.getAttribute('data-filter');
      var visibleCount = 0;
      rows.forEach(function (row) {
        var match = filter === 'All' || row.getAttribute('data-dept') === filter;
        row.classList.toggle('is-hidden', !match);
        if (match) visibleCount++;
      });
      if (emptyState) emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    });
  });
})();
</script>
<?php endif; ?>
