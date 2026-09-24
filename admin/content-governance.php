<?php
/**
 * Admin — Content Governance. Read-only view of the publishing gate:
 * governed pages, jurisdiction approval flags, regulatory references and the
 * manual review queue. The gate itself lives in code (see
 * includes/content-governance.php); changing a status requires a reviewed
 * code change with an approval record, so nothing here can make a page
 * indexable by accident.
 */
require_once __DIR__ . '/../includes/content-governance.php';
require_once __DIR__ . '/../includes/regulatory-context.php';
require_once __DIR__ . '/../includes/business-services.php';
require_once __DIR__ . '/../includes/blog.php';

$page_meta = ['title' => 'Content Governance | Paynancial Admin', 'heading' => 'Content Governance'];

$items = array_filter(gov_content_items(), fn ($p) => !str_starts_with($p, '/blog/'), ARRAY_FILTER_USE_KEY);
$articles = blog_all();
$blogStatuses = blog_statuses();
$workflow = blog_regulatory_workflow();
$labels = gov_stage_labels();
$refs = reg_references();
$queue = array_filter($refs, fn ($r) => !reg_publishable($r));
$jurisdictions = bs_jurisdictions();
$yn = fn ($v) => $v ? 'Yes' : 'No';
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Governed pages (live, not approved)</span><strong class="value"><?= count($items) ?></strong></div>
  <div class="stat-card"><span class="label">Regulatory verification gate</span><strong class="value">Disabled</strong></div>
  <div class="stat-card"><span class="label">Jurisdictions approved</span><strong class="value"><?= count(array_filter($jurisdictions, 'bs_jurisdiction_approved')) ?> / <?= count($jurisdictions) ?></strong></div>
  <div class="stat-card"><span class="label">Blog articles (live / indexable)</span><strong class="value"><?= count(blog_live()) ?> / <?= count(array_filter(array_keys($articles), fn ($s) => gov_indexable(blog_url($s)))) ?></strong></div>
  <div class="stat-card"><span class="label">Professional review</span><strong class="value"><?= e(ucfirst(GOV_PROFESSIONAL_REVIEW)) ?></strong></div>
</div>

<div class="panel">
  <h2>Publishing workflow</h2>
  <p class="text-muted"><?= e(implode(' → ', $labels)) ?>. No stage may be skipped. A page is indexable only when its stage has reached “Indexable”, its indexable flag is on and an approval (by whom, when) is recorded.</p>
</div>

<div class="panel">
  <h2>Governed pages</h2>
  <table class="data-table">
    <thead><tr><th>Page</th><th>Stage</th><th>Indexable</th><th>Sitemap</th><th>Service promotion</th><th>Professional review</th><th>Reason</th></tr></thead>
    <tbody>
      <?php foreach ($items as $path => $i): ?>
      <tr>
        <td><a href="<?= e($path) ?>" target="_blank" rel="noopener"><?= e($path) ?></a></td>
        <td><?= e($labels[$i['stage']] ?? $i['stage']) ?></td>
        <td><?= $yn(gov_indexable($path)) ?></td>
        <td><?= $yn(gov_in_sitemap($path)) ?></td>
        <td><?= $yn(gov_service_promotion($path)) ?></td>
        <td><?= e(ucfirst($i['professional_review'])) ?></td>
        <td><?= e($i['reason']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <h2>Blog / Insights — articles</h2>
  <p class="text-muted">CMS status: <?= e(implode(' → ', $blogStatuses)) ?>. “In review” articles are live but noindex. An article is indexable only with status “Indexable”, the indexable flag on, and an approval (by whom, when) recorded; it enters the sitemap only with the sitemap flag on too. /blog and the category pages stay noindex until a separate blog-level review.</p>
  <p class="text-muted">Regulatory articles: written only from an official source the business supplies — <?= e(implode(' → ', array_slice($workflow, 1))) ?>. Not live until the workflow is “Published” and the source record is complete and verified (<?= e(implode(', ', blog_source_fields())) ?>).</p>
  <table class="data-table">
    <thead><tr><th>Article</th><th>Category</th><th>Type</th><th>CMS status</th><th>Source</th><th>Live</th><th>Indexable</th><th>Sitemap</th><th>Approved</th><th>Updated</th></tr></thead>
    <tbody>
      <?php foreach ($articles as $slug => $a): $regulatory = $a['type'] === 'regulatory'; ?>
      <tr>
        <td><?php if (blog_is_live($a)): ?><a href="<?= e(blog_url($slug)) ?>" target="_blank" rel="noopener"><?= e($a['title']) ?></a><?php else: ?><?= e($a['title']) ?><?php endif; ?></td>
        <td><?= e(blog_categories()[$a['category']][0] ?? $a['category']) ?></td>
        <td><?= $regulatory ? 'Regulatory' : 'General' ?></td>
        <td><?= e($blogStatuses[$a['status']] ?? $a['status']) ?><?= $regulatory ? ' · ' . e($workflow[$a['workflow'] ?? ''] ?? 'Workflow not set') : '' ?></td>
        <td><?= $regulatory ? (blog_source_verified($a) ? 'Verified' : 'Missing: ' . e(implode(', ', blog_source_missing($a)) ?: 'verification')) : 'Not applicable' ?></td>
        <td><?= $yn(blog_is_live($a)) ?></td>
        <td><?= $yn(gov_indexable(blog_url($slug))) ?></td>
        <td><?= $yn(gov_in_sitemap(blog_url($slug))) ?></td>
        <td><?= $a['approved_by'] ? e($a['approved_by'] . ' · ' . $a['approved_on']) : '—' ?></td>
        <td><?= e($a['updated']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <h2>Jurisdictions</h2>
  <p class="text-muted">Approved only when service_enabled and indexable are both true. Unapproved jurisdictions show jurisdiction information only — no service CTAs.</p>
  <table class="data-table">
    <thead><tr><th>Jurisdiction</th><th>Research</th><th>Service enabled</th><th>Indexable</th><th>Sitemap</th><th>Service promotion</th><th>Approved</th></tr></thead>
    <tbody>
      <?php foreach ($jurisdictions as $slug => $j): ?>
      <tr>
        <td><a href="<?= e(bs_jurisdiction_url($slug)) ?>" target="_blank" rel="noopener"><?= e($j['name']) ?></a></td>
        <td><?= $yn($j['research'] ?? false) ?></td>
        <td><?= $yn(($j['service_enabled'] ?? false) === true) ?></td>
        <td><?= $yn(($j['indexable'] ?? false) === true) ?></td>
        <td><?= $yn(($j['sitemap'] ?? false) === true) ?></td>
        <td><?= $yn(($j['service_promotion'] ?? false) === true) ?></td>
        <td><?= $yn(bs_jurisdiction_approved($j)) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <h2>Regulatory references — review queue</h2>
  <p class="text-muted">Workflow: <?= e(implode(' → ', reg_workflow())) ?>. The verification gate is disabled, so these references are public as general information regardless of workflow stage; the fields (<?= e(implode(', ', reg_required_fields())) ?>) are kept for internal record-keeping and are never shown as verification metadata.</p>
  <table class="data-table">
    <thead><tr><th>Reference</th><th>Regulator</th><th>Workflow</th><th>Status</th><th>Missing fields</th><th>Used on</th></tr></thead>
    <tbody>
      <?php $map = reg_page_map(); foreach ($refs as $id => $r): $used = count(array_filter($map, fn ($ids) => in_array($id, $ids, true))); ?>
      <tr>
        <td><?= e($r['title']) ?></td>
        <td><?= e($r['regulator']) ?></td>
        <td><?= e(reg_workflow()[$r['workflow']] ?? $r['workflow']) ?></td>
        <td><?= e(str_replace('_', ' ', $r['status'])) ?></td>
        <td><?= e(implode(', ', reg_missing_fields($r)) ?: '—') ?></td>
        <td><?= $used ?> page<?= $used === 1 ? '' : 's' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <h2>Legal documents — review status</h2>
  <p class="text-muted">Live and unchanged. No edits to legal or regulatory wording until qualified review is completed; no reviewer is named until one has actually reviewed them.</p>
  <table class="data-table">
    <thead><tr><th>Document</th><th>Legal review</th><th>Regulatory review</th><th>Reviewer</th><th>Next step</th></tr></thead>
    <tbody>
      <?php foreach (gov_review_items() as $path => $d): ?>
      <tr>
        <td><a href="<?= e($path) ?>" target="_blank" rel="noopener"><?= e($d['title']) ?></a></td>
        <td><?= e(ucfirst($d['legal_review'])) ?></td>
        <td><?= e(ucfirst($d['regulatory_review'])) ?></td>
        <td><?= e($d['reviewer'] ?? '—') ?></td>
        <td><?= e($d['note']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <h2>Regulatory verification gate: disabled</h2>
  <p><strong>Regulatory verification gate disabled by editorial decision. Regulatory content is general informational material and must not be represented as verified regulatory advice.</strong></p>
  <p class="text-muted">Decision date: 24 Sep 2026. Public as general information, each with “General information only. Not legal, tax, financial or regulatory advice.”, never labelled as verified and without source metadata:</p>
  <ul>
    <li>“Compliance in India” reference cards on product pages (source details line shown only for references with a verified record).</li>
    <li>“In India” context bands on product, AI and developer pages.</li>
    <li>Business Services “Framework” statements (e.g. Companies Act references).</li>
  </ul>
  <p><strong>Scope:</strong> general Indian regulatory context only. Foreign jurisdiction-specific claims remain pending until actual business/service evidence and source research exist — the jurisdiction gate below is unchanged.</p>
  <p class="text-muted">Regulatory Insights blog articles still follow the source-first workflow (none written yet). Schema for moving this into the CMS database: <code>database/content_governance_schema.sql</code>.</p>
</div>
