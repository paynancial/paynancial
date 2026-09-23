<?php
/** Admin: enquiry management (sales/partner/support/general/career). */
$page_meta = ['title' => 'Enquiries | Paynancial Admin', 'heading' => 'Enquiries'];

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enquiry_id'])) {
    if (csrf_verify($_POST['csrf_token'] ?? null)) {
        $newStatus = sanitize_input((string) ($_POST['status'] ?? ''));
        if (in_array($newStatus, ['new', 'in_progress', 'responded', 'closed'], true)) {
            $upd = $pdo->prepare('UPDATE enquiries SET status = :status, assigned_to = :uid WHERE id = :id');
            $upd->execute(['status' => $newStatus, 'uid' => $auth_user['id'], 'id' => (int) $_POST['enquiry_id']]);
        }
    }
}

$typeFilter = sanitize_input((string) ($_GET['type'] ?? ''));
$sql = 'SELECT id, enquiry_code, type, name, company, email, subject, status, created_at FROM enquiries WHERE 1=1';
$params = [];
if (in_array($typeFilter, ['sales', 'partner', 'support', 'general', 'career'], true)) {
    $sql .= ' AND type = :type'; $params['type'] = $typeFilter;
}
$sql .= ' ORDER BY created_at DESC LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="panel">
  <div class="panel-head"><h2>Enquiries</h2></div>
  <form method="get" class="toolbar" style="margin-bottom:18px;">
    <select name="type">
      <option value="">All types</option>
      <?php foreach (['sales', 'partner', 'support', 'general', 'career'] as $t): ?>
        <option value="<?= e($t) ?>" <?= $typeFilter === $t ? 'selected' : '' ?>><?= e(ucfirst($t)) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Enquiry ID</th><th>Type</th><th>Name / Company</th><th>Email</th><th>Subject</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?><tr><td colspan="6"><div class="empty-state">No enquiries yet.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr>
            <td><?= e($row['enquiry_code']) ?></td>
            <td><span class="badge info"><?= e(ucfirst($row['type'])) ?></span></td>
            <td><?= e($row['name']) ?><?= $row['company'] ? ' · ' . e($row['company']) : '' ?></td>
            <td><?= e($row['email']) ?></td>
            <td><?= e($row['subject'] ?? '—') ?></td>
            <td>
              <form method="post" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="enquiry_id" value="<?= (int) $row['id'] ?>">
                <select name="status" class="js-auto-submit">
                  <?php foreach (['new', 'in_progress', 'responded', 'closed'] as $s): ?>
                    <option value="<?= e($s) ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
