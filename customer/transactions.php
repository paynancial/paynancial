<?php
/** Customer: full payment history with search + status filter. */
$page_meta = ['title' => 'Payment History | Paynancial', 'heading' => 'Payment History'];

$pdo = db();
$stmt = $pdo->prepare('SELECT id FROM customers WHERE user_id = :uid');
$stmt->execute(['uid' => $auth_user['id']]);
$customerId = (int) ($stmt->fetchColumn() ?: 0);

$search = sanitize_input((string) ($_GET['q'] ?? ''));
$statusFilter = sanitize_input((string) ($_GET['status'] ?? ''));
$allowedStatus = ['', 'initiated', 'pending', 'success', 'failed', 'refunded'];
if (!in_array($statusFilter, $allowedStatus, true)) { $statusFilter = ''; }

$sql = 'SELECT transaction_ref, amount, currency, payment_method, status, created_at FROM transactions WHERE customer_id = :cid';
$params = ['cid' => $customerId];
if ($search !== '') { $sql .= ' AND transaction_ref LIKE :q'; $params['q'] = '%' . $search . '%'; }
if ($statusFilter !== '') { $sql .= ' AND status = :status'; $params['status'] = $statusFilter; }
$sql .= ' ORDER BY created_at DESC LIMIT 100';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="panel">
  <div class="panel-head">
    <h2>Payment History</h2>
  </div>
  <form method="get" class="toolbar" style="margin-bottom:18px;">
    <input type="text" name="q" placeholder="Search by reference…" value="<?= e($search) ?>">
    <select name="status">
      <option value="">All statuses</option>
      <?php foreach (['initiated', 'pending', 'success', 'failed', 'refunded'] as $s): ?>
        <option value="<?= e($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Reference</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5"><div class="empty-state">No transactions match your filters.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr>
            <td><?= e($row['transaction_ref']) ?></td>
            <td><?= e($row['payment_method'] ?? '—') ?></td>
            <td><?= e(format_amount((float) $row['amount'], $row['currency'])) ?></td>
            <td><span class="badge <?= e($row['status'] === 'success' ? 'success' : ($row['status'] === 'failed' ? 'failed' : 'pending')) ?>"><?= e(ucfirst($row['status'])) ?></span></td>
            <td><?= e(date('d M Y, H:i', strtotime((string) $row['created_at']))) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
