<?php
/** Admin — Solution Catalog management: add/edit products shown in the Partner Hub marketplace. */
$pdo = db();
$page_meta = ['title' => 'Solution Catalog | Paynancial Admin', 'heading' => 'Solution Catalog'];

$categoryLabels = [
    'payment_acceptance' => 'Payment Acceptance', 'payment_management' => 'Payment Management', 'payouts' => 'Payouts',
    'integration' => 'Integration', 'business_solutions' => 'Business Solutions', 'ai_intelligence' => 'AI & Intelligence',
];
$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (($_POST['form_action'] ?? '') === 'create') {
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $name = sanitize_input((string) ($_POST['name'] ?? ''));
        $category = sanitize_input((string) ($_POST['category'] ?? ''));
        $description = sanitize_input((string) ($_POST['short_description'] ?? ''));
        $complexity = sanitize_input((string) ($_POST['complexity'] ?? 'medium'));
        $pricingStatus = sanitize_input((string) ($_POST['pricing_status'] ?? 'talk_to_sales'));
        $commissionEligible = isset($_POST['commission_eligible']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if ($slug === '' || $name === '' || !array_key_exists($category, $categoryLabels)) {
            $errors[] = 'Slug, name, and a valid category are required.';
        } else {
            try {
                $ins = $pdo->prepare(
                    'INSERT INTO products (slug, name, category, short_description, complexity, pricing_status, commission_eligible, sort_order)
                     VALUES (:slug, :name, :cat, :desc, :complexity, :pricing, :commission, :sort)'
                );
                $ins->execute([
                    'slug' => $slug, 'name' => $name, 'cat' => $category, 'desc' => $description ?: null,
                    'complexity' => in_array($complexity, ['low', 'medium', 'high'], true) ? $complexity : 'medium',
                    'pricing' => $pricingStatus ?: 'talk_to_sales', 'commission' => $commissionEligible, 'sort' => $sortOrder,
                ]);
                $notice = "Solution \"{$name}\" created.";
            } catch (Throwable $e) {
                $errors[] = 'Could not create this solution — the slug may already be in use.';
            }
        }
    } elseif (($_POST['form_action'] ?? '') === 'update') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $name = sanitize_input((string) ($_POST['name'] ?? ''));
        $description = sanitize_input((string) ($_POST['short_description'] ?? ''));
        $complexity = sanitize_input((string) ($_POST['complexity'] ?? 'medium'));
        $pricingStatus = sanitize_input((string) ($_POST['pricing_status'] ?? 'talk_to_sales'));
        $commissionEligible = isset($_POST['commission_eligible']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $upd = $pdo->prepare(
            'UPDATE products SET name = :name, short_description = :desc, complexity = :complexity, pricing_status = :pricing,
             commission_eligible = :commission, is_active = :active, sort_order = :sort WHERE id = :id'
        );
        $upd->execute([
            'name' => $name, 'desc' => $description ?: null,
            'complexity' => in_array($complexity, ['low', 'medium', 'high'], true) ? $complexity : 'medium',
            'pricing' => $pricingStatus ?: 'talk_to_sales', 'commission' => $commissionEligible, 'active' => $isActive,
            'sort' => $sortOrder, 'id' => $productId,
        ]);
        $notice = 'Solution updated.';
    }
}

$stmt = $pdo->query('SELECT * FROM products ORDER BY category, sort_order');
$products = $stmt->fetchAll();
?>
<?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
<?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Add New Solution</h2></div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="create">
    <div class="field-grid">
      <div class="field"><label>Slug</label><input type="text" name="slug" placeholder="e.g. smart-routing" required></div>
      <div class="field"><label>Name</label><input type="text" name="name" required></div>
      <div class="field">
        <label>Category</label>
        <select name="category" required>
          <?php foreach ($categoryLabels as $slug => $label): ?><option value="<?= e($slug) ?>"><?= e($label) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Complexity</label>
        <select name="complexity"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select>
      </div>
      <div class="field"><label>Pricing Status</label><input type="text" name="pricing_status" value="talk_to_sales"></div>
      <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
    </div>
    <div class="field"><label>Short Description</label><textarea name="short_description" rows="2"></textarea></div>
    <label class="field-row"><input type="checkbox" name="commission_eligible" checked> Commission Eligible</label>
    <button type="submit" class="btn btn-primary" style="margin-top:12px;">Add Solution</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All Solutions</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Category</th><th>Description</th><th>Complexity</th><th>Pricing</th><th>Commission</th><th>Active</th><th>Sort</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): $fid = 'pf-' . (int) $p['id']; ?>
          <tr>
            <td><?= e($p['name']) ?><br><span class="mono text-muted" style="font-size:0.72rem;"><?= e($p['slug']) ?></span></td>
            <td><?= e($categoryLabels[$p['category']] ?? $p['category']) ?></td>
            <td><input form="<?= $fid ?>" type="text" name="short_description" value="<?= e($p['short_description'] ?? '') ?>" style="width:100%;min-width:180px;"></td>
            <td>
              <select form="<?= $fid ?>" name="complexity">
                <?php foreach (['low', 'medium', 'high'] as $c): ?><option value="<?= $c ?>" <?= $p['complexity'] === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option><?php endforeach; ?>
              </select>
            </td>
            <td><input form="<?= $fid ?>" type="text" name="pricing_status" value="<?= e($p['pricing_status']) ?>" style="width:130px;"></td>
            <td style="text-align:center;"><input form="<?= $fid ?>" type="checkbox" name="commission_eligible" <?= $p['commission_eligible'] ? 'checked' : '' ?>></td>
            <td style="text-align:center;"><input form="<?= $fid ?>" type="checkbox" name="is_active" <?= $p['is_active'] ? 'checked' : '' ?>></td>
            <td><input form="<?= $fid ?>" type="number" name="sort_order" value="<?= (int) $p['sort_order'] ?>" style="width:60px;"></td>
            <td>
              <button form="<?= $fid ?>" type="submit" class="btn btn-outline btn-sm">Save</button>
              <form id="<?= $fid ?>" method="post" style="display:none;">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="update">
                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
