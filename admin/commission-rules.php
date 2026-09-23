<?php
/**
 * Admin — Commission Rule configuration. Every rate a partner ever
 * sees (partner/commissions.php) comes from this table. There is no
 * hardcoded commission percentage anywhere in the codebase — admin
 * sets every rate here, and new commission-eligible products are
 * seeded at 0.00% until an admin configures them.
 */
$pdo = db();
$page_meta = ['title' => 'Commission Rules | Paynancial Admin', 'heading' => 'Commission Rules'];

$ruleTypeLabels = ['product_based' => 'Product-based', 'revenue_share' => 'Revenue Share', 'referral' => 'Referral', 'tiered' => 'Tiered Volume'];
$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (($_POST['form_action'] ?? '') === 'create') {
        $name = sanitize_input((string) ($_POST['name'] ?? ''));
        $ruleType = sanitize_input((string) ($_POST['rule_type'] ?? ''));
        $productId = (int) ($_POST['product_id'] ?? 0);
        $rate = (string) ($_POST['rate_percent'] ?? '0');
        $tierMin = trim((string) ($_POST['tier_min_volume'] ?? ''));
        $tierMax = trim((string) ($_POST['tier_max_volume'] ?? ''));

        if ($name === '' || !array_key_exists($ruleType, $ruleTypeLabels) || !is_numeric($rate)) {
            $errors[] = 'Name, a valid rule type, and a numeric rate are required.';
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO commission_rules (name, rule_type, product_id, rate_percent, tier_min_volume, tier_max_volume, is_active)
                 VALUES (:name, :type, :pid, :rate, :tmin, :tmax, 1)'
            );
            $ins->execute([
                'name' => $name, 'type' => $ruleType, 'pid' => $ruleType === 'product_based' && $productId > 0 ? $productId : null,
                'rate' => $rate, 'tmin' => $tierMin !== '' ? $tierMin : null, 'tmax' => $tierMax !== '' ? $tierMax : null,
            ]);
            $notice = "Commission rule \"{$name}\" created.";
        }
    } elseif (($_POST['form_action'] ?? '') === 'update') {
        $ruleId = (int) ($_POST['rule_id'] ?? 0);
        $rate = (string) ($_POST['rate_percent'] ?? '0');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        if (is_numeric($rate)) {
            $pdo->prepare('UPDATE commission_rules SET rate_percent = :rate, is_active = :active WHERE id = :id')
                ->execute(['rate' => $rate, 'active' => $isActive, 'id' => $ruleId]);
            $notice = 'Commission rule updated.';
        } else {
            $errors[] = 'Rate must be numeric.';
        }
    }
}

$rulesStmt = $pdo->query(
    'SELECT cr.*, p.name AS product_name FROM commission_rules cr LEFT JOIN products p ON p.id = cr.product_id ORDER BY cr.rule_type, p.sort_order'
);
$rules = $rulesStmt->fetchAll();

$productsStmt = $pdo->query("SELECT id, name FROM products WHERE is_active = 1 AND commission_eligible = 1 ORDER BY sort_order");
$products = $productsStmt->fetchAll();
?>
<?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
<?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Add Commission Rule</h2></div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="create">
    <div class="field-grid">
      <div class="field"><label>Rule Name</label><input type="text" name="name" required></div>
      <div class="field">
        <label>Rule Type</label>
        <select name="rule_type" required>
          <?php foreach ($ruleTypeLabels as $slug => $label): ?><option value="<?= e($slug) ?>"><?= e($label) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Product (for Product-based rules)</label>
        <select name="product_id">
          <option value="0">— None —</option>
          <?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Rate (%)</label><input type="text" name="rate_percent" placeholder="e.g. 1.50" required></div>
      <div class="field"><label>Tier Min Volume (optional)</label><input type="text" name="tier_min_volume" placeholder="e.g. 0"></div>
      <div class="field"><label>Tier Max Volume (optional)</label><input type="text" name="tier_max_volume" placeholder="e.g. 500000"></div>
    </div>
    <button type="submit" class="btn btn-primary">Add Rule</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All Commission Rules</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Rule</th><th>Type</th><th>Applies To</th><th>Rate (%)</th><th>Volume Band</th><th>Active</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rules)): ?>
          <tr><td colspan="7"><div class="empty-state">No commission rules configured yet.</div></td></tr>
        <?php else: foreach ($rules as $rule): $fid = 'crf-' . (int) $rule['id']; ?>
          <tr>
            <td><?= e($rule['name']) ?></td>
            <td><span class="badge info"><?= e($ruleTypeLabels[$rule['rule_type']] ?? $rule['rule_type']) ?></span></td>
            <td><?= e($rule['product_name'] ?? 'All products') ?></td>
            <td><input form="<?= $fid ?>" type="text" name="rate_percent" value="<?= e(number_format((float) $rule['rate_percent'], 2)) ?>" style="width:90px;"></td>
            <td>
              <?php if ($rule['tier_min_volume'] !== null || $rule['tier_max_volume'] !== null): ?>
                <?= e(format_amount((float) $rule['tier_min_volume'])) ?> &ndash; <?= $rule['tier_max_volume'] !== null ? e(format_amount((float) $rule['tier_max_volume'])) : 'above' ?>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td style="text-align:center;"><input form="<?= $fid ?>" type="checkbox" name="is_active" <?= $rule['is_active'] ? 'checked' : '' ?>></td>
            <td>
              <button form="<?= $fid ?>" type="submit" class="btn btn-outline btn-sm">Save</button>
              <form id="<?= $fid ?>" method="post" style="display:none;">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="update">
                <input type="hidden" name="rule_id" value="<?= (int) $rule['id'] ?>">
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
