<?php
/** Customer dashboard overview. */
$page_meta = ['title' => 'Customer Dashboard | Paynancial', 'heading' => 'Dashboard'];

$pdo = db();
$stmt = $pdo->prepare('SELECT id, customer_code FROM customers WHERE user_id = :uid');
$stmt->execute(['uid' => $auth_user['id']]);
$customer = $stmt->fetch();
$customerId = $customer['id'] ?? 0;

function customer_stat(PDO $pdo, int $customerId, string $status = null): array
{
    $sql = 'SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total FROM transactions WHERE customer_id = :cid';
    if ($status) { $sql .= ' AND status = :status'; }
    $stmt = $pdo->prepare($sql);
    $params = ['cid' => $customerId];
    if ($status) { $params['status'] = $status; }
    $stmt->execute($params);
    return $stmt->fetch();
}

$total = customer_stat($pdo, $customerId);
$success = customer_stat($pdo, $customerId, 'success');
$pending = customer_stat($pdo, $customerId, 'pending');
$refundStmt = $pdo->prepare(
    'SELECT COUNT(*) AS cnt, COALESCE(SUM(r.amount),0) AS total FROM refunds r
     JOIN transactions t ON t.id = r.transaction_id WHERE t.customer_id = :cid'
);
$refundStmt->execute(['cid' => $customerId]);
$refunds = $refundStmt->fetch();

$recentStmt = $pdo->prepare(
    'SELECT transaction_ref, amount, currency, status, created_at FROM transactions
     WHERE customer_id = :cid ORDER BY created_at DESC LIMIT 8'
);
$recentStmt->execute(['cid' => $customerId]);
$recent = $recentStmt->fetchAll();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Payments</span><strong class="value"><?= (int) $total['cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Successful Transactions</span><strong class="value"><?= (int) $success['cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Pending Payments</span><strong class="value"><?= (int) $pending['cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Refunds</span><strong class="value"><?= (int) $refunds['cnt'] ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Recent Transactions</h2>
    <a href="/customer/transactions" class="btn btn-outline btn-sm">View All</a>
  </div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Reference</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (empty($recent)): ?>
          <tr><td colspan="4"><div class="empty-state">No transactions yet.</div></td></tr>
        <?php else: foreach ($recent as $row): ?>
          <tr>
            <td><?= e($row['transaction_ref']) ?></td>
            <td><?= e(format_amount((float) $row['amount'], $row['currency'])) ?></td>
            <td><span class="badge <?= e($row['status'] === 'success' ? 'success' : ($row['status'] === 'failed' ? 'failed' : 'pending')) ?>"><?= e(ucfirst($row['status'])) ?></span></td>
            <td><?= e(date('d M Y, H:i', strtotime((string) $row['created_at']))) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
