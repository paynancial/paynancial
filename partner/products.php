<?php
/** Partner Hub — Solution Catalog (/partner/products): the product marketplace partners browse to enroll customers into. */
$context = require_partner_context();
$pdo = db();

$page_meta = ['title' => 'Solution Catalog | Paynancial Partner Hub', 'heading' => 'Solution Catalog'];

$categoryLabels = [
    'payment_acceptance' => 'Payment Acceptance',
    'payment_management' => 'Payment Management',
    'payouts'             => 'Payouts',
    'integration'         => 'Integration',
    'business_solutions'  => 'Business Solutions',
    'ai_intelligence'     => 'AI & Intelligence',
];
$complexityLabel = ['low' => 'Easy Setup', 'medium' => 'Standard Setup', 'high' => 'Guided Setup'];

$categoryFilter = sanitize_input((string) ($_GET['category'] ?? ''));

$sql = 'SELECT * FROM products WHERE is_active = 1';
$params = [];
if (array_key_exists($categoryFilter, $categoryLabels)) {
    $sql .= ' AND category = :cat';
    $params['cat'] = $categoryFilter;
}
$sql .= ' ORDER BY category, sort_order';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$featureStmt = $pdo->query('SELECT product_id, feature FROM product_features ORDER BY product_id, sort_order');
$featuresByProduct = [];
foreach ($featureStmt->fetchAll() as $row) {
    $featuresByProduct[$row['product_id']][] = $row['feature'];
}

$grouped = [];
foreach ($products as $p) {
    $grouped[$p['category']][] = $p;
}

$availableCategories = array_keys($grouped);
if ($categoryFilter !== '' && array_key_exists($categoryFilter, $categoryLabels)) {
    $availableCategories = [$categoryFilter];
} else {
    // Preserve a stable, predefined order rather than DB grouping order.
    $availableCategories = array_values(array_intersect(array_keys($categoryLabels), array_keys($grouped)));
}
?>
<div class="panel" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
  <div>
    <h2 style="font-size:1.2rem;">Solution Catalog</h2>
    <p class="text-muted" style="margin-top:6px;">Browse the Paynancial product line to match the right solution to each customer.</p>
  </div>
  <a href="/partner/enroll-customer" class="btn btn-primary">+ Enroll New Customer</a>
</div>

<div class="panel">
  <div class="pill-list">
    <a href="/partner/products" class="pill <?= $categoryFilter === '' ? 'is-active' : '' ?>">All Categories</a>
    <?php foreach ($categoryLabels as $slug => $label): if (empty($grouped[$slug]) && $categoryFilter !== $slug) continue; ?>
      <a href="/partner/products?category=<?= e($slug) ?>" class="pill <?= $categoryFilter === $slug ? 'is-active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (empty($products)): ?>
  <div class="panel"><div class="empty-state">No solutions found in this category.</div></div>
<?php else: foreach ($availableCategories as $categorySlug): ?>
  <div class="panel">
    <div class="panel-head"><h2><?= e($categoryLabels[$categorySlug] ?? ucfirst($categorySlug)) ?></h2></div>
    <div class="grid grid-2">
      <?php foreach ($grouped[$categorySlug] as $p): ?>
        <div class="card">
          <div class="flex" style="justify-content:space-between;align-items:flex-start;gap:10px;">
            <h3 style="font-size:1.05rem;"><?= e($p['name']) ?></h3>
            <?php if ($p['commission_eligible']): ?><span class="badge success">Commission Eligible</span><?php endif; ?>
          </div>
          <p style="margin-top:8px;"><?= e($p['short_description']) ?></p>
          <?php if (!empty($featuresByProduct[$p['id']])): ?>
            <ul style="margin-top:14px;padding-left:18px;font-size:0.85rem;color:var(--text-muted);">
              <?php foreach ($featuresByProduct[$p['id']] as $feature): ?>
                <li style="margin-bottom:4px;"><?= e($feature) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <div class="pill-list" style="margin-top:14px;">
            <span class="pill"><?= e($complexityLabel[$p['complexity']] ?? ucfirst($p['complexity'])) ?></span>
            <span class="pill mono"><?= e($p['pricing_status'] === 'talk_to_sales' ? 'Talk to Sales for Pricing' : $p['pricing_status']) ?></span>
          </div>
          <div class="hero-actions" style="margin-top:16px;">
            <a href="/partner/enroll-customer" class="btn btn-outline btn-sm">Enroll a Customer</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; endif; ?>
