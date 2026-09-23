<?php
/** Partner Hub — Transactions: full history with filters, KPIs, and a volume trend chart. */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Transactions | Paynancial Partner Hub', 'heading' => 'Transactions'];

$pdo = db();

$search = sanitize_input((string) ($_GET['q'] ?? ''));
$statusFilter = sanitize_input((string) ($_GET['status'] ?? ''));
$allowedStatus = ['', 'initiated', 'pending', 'success', 'failed', 'refunded'];
if (!in_array($statusFilter, $allowedStatus, true)) { $statusFilter = ''; }

$sql = 'SELECT transaction_ref, amount, currency, payment_method, status, created_at FROM transactions WHERE partner_id = :pid';
$params = ['pid' => $partnerId];
if ($search !== '') { $sql .= ' AND transaction_ref LIKE :q'; $params['q'] = '%' . $search . '%'; }
if ($statusFilter !== '') { $sql .= ' AND status = :status'; $params['status'] = $statusFilter; }
$sql .= ' ORDER BY created_at DESC LIMIT 100';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// ---- KPIs ----
$kpiStmt = $pdo->prepare(
    "SELECT
       COUNT(*) AS total_cnt,
       SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_cnt,
       SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_cnt,
       SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) AS refunded_cnt,
       COALESCE(SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END), 0) AS success_volume,
       COALESCE(AVG(CASE WHEN status = 'success' THEN amount ELSE NULL END), 0) AS avg_ticket
     FROM transactions WHERE partner_id = :pid"
);
$kpiStmt->execute(['pid' => $partnerId]);
$kpi = $kpiStmt->fetch();
$successRate = $kpi['total_cnt'] > 0 ? round(($kpi['success_cnt'] / $kpi['total_cnt']) * 100, 1) : 0.0;

// ---- 6-month volume trend ----
$trendStmt = $pdo->prepare(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS total
     FROM transactions WHERE partner_id = :pid AND status = 'success' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY ym ORDER BY ym"
);
$trendStmt->execute(['pid' => $partnerId]);
$trendRows = $trendStmt->fetchAll();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Transactions</span><strong class="value"><?= (int) $kpi['total_cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Success Rate</span><strong class="value"><?= e($successRate) ?>%</strong></div>
  <div class="stat-card"><span class="label">Successful Volume</span><strong class="value"><?= e(format_amount((float) $kpi['success_volume'])) ?></strong></div>
  <div class="stat-card"><span class="label">Average Ticket Size</span><strong class="value"><?= e(format_amount((float) $kpi['avg_ticket'])) ?></strong></div>
  <div class="stat-card"><span class="label">Failed</span><strong class="value"><?= (int) $kpi['failed_cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Refunded</span><strong class="value"><?= (int) $kpi['refunded_cnt'] ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Volume Trend (Last 6 Months)</h2></div>
  <canvas id="chart-txn-volume" height="80"></canvas>
</div>

<div class="panel">
  <div class="panel-head"><h2>Transaction History</h2></div>
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
            <td class="mono"><?= e($row['transaction_ref']) ?></td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script nonce="<?= csp_nonce() ?>">
(function () {
  var trend = <?= json_encode($trendRows, JSON_UNESCAPED_SLASHES) ?>;
  if (window.Chart) {
    new Chart(document.getElementById('chart-txn-volume'), {
      type: 'line',
      data: { labels: trend.map(function (r) { return r.ym; }), datasets: [{ data: trend.map(function (r) { return r.total; }), borderColor: '#00a69d', backgroundColor: 'rgba(0,166,157,0.12)', fill: true, tension: 0.3 }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
  }
})();
</script>
