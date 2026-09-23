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

$page_meta = ['title' => 'Content Governance | Paynancial Admin', 'heading' => 'Content Governance'];

$items = gov_content_items();
$labels = gov_stage_labels();
$refs = reg_references();
$queue = array_filter($refs, fn ($r) => !reg_publishable($r));
$jurisdictions = bs_jurisdictions();
$yn = fn ($v) => $v ? 'Yes' : 'No';
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Governed pages (live, not approved)</span><strong class="value"><?= count($items) ?></strong></div>
  <div class="stat-card"><span class="label">Regulatory references pending verification</span><strong class="value"><?= count($queue) ?> / <?= count($refs) ?></strong></div>
  <div class="stat-card"><span class="label">Jurisdictions approved</span><strong class="value"><?= count(array_filter($jurisdictions, 'bs_jurisdiction_approved')) ?> / <?= count($jurisdictions) ?></strong></div>
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
  <p class="text-muted">Not shown on any public page until verified with every required field: <?= e(implode(', ', reg_required_fields())) ?>.</p>
  <table class="data-table">
    <thead><tr><th>Reference</th><th>Authority</th><th>Status</th><th>Missing fields</th><th>Used on</th></tr></thead>
    <tbody>
      <?php $map = reg_page_map(); foreach ($refs as $id => $r): $used = count(array_filter($map, fn ($ids) => in_array($id, $ids, true))); ?>
      <tr>
        <td><?= e($r['title']) ?></td>
        <td><?= e($r['authority']) ?></td>
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
  <h2>Drafts held back from public pages</h2>
  <ul>
    <li>“Compliance in India” bands — all references above (source verification pending).</li>
    <li>“In India” context bands on product, AI and developer pages — contain regulatory statements (source verification pending).</li>
    <li>Business Services “Framework” statements (e.g. Companies Act references) — need an official source and a last-verified date.</li>
  </ul>
  <p class="text-muted">Schema for moving this into the CMS database: <code>database/content_governance_schema.sql</code>.</p>
</div>
