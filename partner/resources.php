<?php
/** Partner Hub — Knowledge Center: admin-curated guides and documentation. */
$context = require_partner_context();
$page_meta = ['title' => 'Knowledge Center | Paynancial Partner Hub', 'heading' => 'Knowledge Center'];

$pdo = db();
$categoryLabels = [
    'getting_started'          => 'Getting Started',
    'product_guides'           => 'Product Guides',
    'payment_gateway_guide'    => 'Payment Gateway Guide',
    'integration_guide'        => 'Integration Guide',
    'api_documentation'        => 'API Documentation',
    'customer_onboarding_guide'=> 'Customer Onboarding Guide',
    'kyc_guide'                => 'KYC Guide',
    'commission_guide'         => 'Commission Guide',
    'faq'                      => 'FAQ',
];

$stmt = $pdo->query('SELECT * FROM partner_resources WHERE is_active = 1 ORDER BY category, sort_order');
$grouped = [];
foreach ($stmt->fetchAll() as $row) {
    $grouped[$row['category']][] = $row;
}
?>
<div class="panel">
  <h2 style="font-size:1.2rem;">Knowledge Center</h2>
  <p class="text-muted" style="margin-top:6px;">Guides and documentation to help you sell and support Paynancial solutions.</p>
</div>

<?php if (empty($grouped)): ?>
  <div class="panel"><div class="empty-state">No resources have been published yet. Check back soon.</div></div>
<?php else: foreach ($grouped as $categorySlug => $resources): ?>
  <div class="panel">
    <div class="panel-head"><h2><?= e($categoryLabels[$categorySlug] ?? ucfirst(str_replace('_', ' ', $categorySlug))) ?></h2></div>
    <div class="grid grid-2">
      <?php foreach ($resources as $r): ?>
        <div class="card">
          <h3 style="font-size:1rem;"><?= e($r['title']) ?></h3>
          <?php if ($r['description']): ?><p style="margin-top:8px;"><?= e($r['description']) ?></p><?php endif; ?>
          <?php if ($r['external_url']): ?>
            <div class="hero-actions" style="margin-top:14px;">
              <a href="<?= e($r['external_url']) ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer">Open</a>
            </div>
          <?php elseif ($r['file_path']): ?>
            <p class="text-muted" style="margin-top:14px;font-size:0.78rem;">Contact your partner manager for this document.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; endif; ?>
