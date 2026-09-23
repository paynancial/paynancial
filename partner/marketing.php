<?php
/** Partner Hub — Marketing Hub: admin-curated brochures, creatives, and templates for partners to use in outreach. */
$context = require_partner_context();
$page_meta = ['title' => 'Marketing Hub | Paynancial Partner Hub', 'heading' => 'Marketing Hub'];

$pdo = db();
$categoryLabels = [
    'brochure'          => 'Brochures',
    'presentation'      => 'Presentations',
    'social_creative'   => 'Social Media Creatives',
    'email_template'    => 'Email Templates',
    'proposal_template' => 'Proposal Templates',
    'video'             => 'Videos',
    'brand_asset'       => 'Brand Assets',
];

$stmt = $pdo->query('SELECT * FROM marketing_assets WHERE is_active = 1 ORDER BY category, sort_order');
$grouped = [];
foreach ($stmt->fetchAll() as $row) {
    $grouped[$row['category']][] = $row;
}
?>
<div class="panel">
  <h2 style="font-size:1.2rem;">Marketing Hub</h2>
  <p class="text-muted" style="margin-top:6px;">Ready-to-use brochures, creatives, and templates to help you pitch Paynancial solutions.</p>
</div>

<?php if (empty($grouped)): ?>
  <div class="panel"><div class="empty-state">No marketing assets have been published yet. Check back soon.</div></div>
<?php else: foreach ($grouped as $categorySlug => $assets): ?>
  <div class="panel">
    <div class="panel-head"><h2><?= e($categoryLabels[$categorySlug] ?? ucfirst(str_replace('_', ' ', $categorySlug))) ?></h2></div>
    <div class="grid grid-2">
      <?php foreach ($assets as $a): ?>
        <div class="card">
          <h3 style="font-size:1rem;"><?= e($a['title']) ?></h3>
          <?php if ($a['description']): ?><p style="margin-top:8px;"><?= e($a['description']) ?></p><?php endif; ?>
          <?php if ($a['file_path']): ?>
            <p class="text-muted" style="margin-top:14px;font-size:0.78rem;">Contact your partner manager for this asset.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; endif; ?>
