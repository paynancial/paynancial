<?php
/** Partner Hub — Performance analytics: acquisition funnel, transaction growth, commission growth. */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Performance | Paynancial Partner Hub', 'heading' => 'Performance'];

$pdo = db();

$funnelStages = [
    'new_lead' => 'New Lead', 'contacted' => 'Contacted', 'qualified' => 'Qualified',
    'proposal_sent' => 'Proposal Sent', 'documents_pending' => 'Documents Pending', 'kyc_submitted' => 'KYC Submitted',
    'under_review' => 'Under Review', 'approved' => 'Approved', 'integration' => 'Integration', 'active' => 'Active',
];

$funnelStmt = $pdo->prepare(
    'SELECT pipeline_stage, COUNT(*) AS cnt FROM customer_applications WHERE partner_id = :pid GROUP BY pipeline_stage'
);
$funnelStmt->execute(['pid' => $partnerId]);
$funnelCounts = array_column($funnelStmt->fetchAll(), 'cnt', 'pipeline_stage');

$totalsStmt = $pdo->prepare(
    "SELECT COUNT(*) AS total, SUM(CASE WHEN pipeline_stage = 'active' THEN 1 ELSE 0 END) AS active_cnt,
     SUM(CASE WHEN pipeline_stage IN ('lost','rejected') THEN 1 ELSE 0 END) AS lost_cnt
     FROM customer_applications WHERE partner_id = :pid"
);
$totalsStmt->execute(['pid' => $partnerId]);
$totals = $totalsStmt->fetch();
$conversionRate = $totals['total'] > 0 ? round(($totals['active_cnt'] / $totals['total']) * 100, 1) : 0.0;

$volumeStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE partner_id = :pid AND status = 'success'");
$volumeStmt->execute(['pid' => $partnerId]);
$lifetimeVolume = (float) $volumeStmt->fetchColumn();

$commissionStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM commissions WHERE partner_id = :pid AND status != 'reversed'");
$commissionStmt->execute(['pid' => $partnerId]);
$lifetimeCommission = (float) $commissionStmt->fetchColumn();

// ---- 12-month trends ----
$txnTrendStmt = $pdo->prepare(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS total
     FROM transactions WHERE partner_id = :pid AND status = 'success' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
     GROUP BY ym ORDER BY ym"
);
$txnTrendStmt->execute(['pid' => $partnerId]);
$txnTrend = $txnTrendStmt->fetchAll();

$commissionTrendStmt = $pdo->prepare(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS total
     FROM commissions WHERE partner_id = :pid AND status != 'reversed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
     GROUP BY ym ORDER BY ym"
);
$commissionTrendStmt->execute(['pid' => $partnerId]);
$commissionTrend = $commissionTrendStmt->fetchAll();

// ---- Top customers by transaction volume ----
$topCustomersStmt = $pdo->prepare(
    "SELECT ca.business_name, COALESCE(SUM(t.amount),0) AS volume, COUNT(t.id) AS txn_cnt
     FROM customer_applications ca
     JOIN transactions t ON t.customer_id = ca.assigned_customer_id AND t.status = 'success'
     WHERE ca.partner_id = :pid AND ca.assigned_customer_id IS NOT NULL
     GROUP BY ca.id ORDER BY volume DESC LIMIT 5"
);
$topCustomersStmt->execute(['pid' => $partnerId]);
$topCustomers = $topCustomersStmt->fetchAll();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Leads</span><strong class="value"><?= (int) $totals['total'] ?></strong></div>
  <div class="stat-card"><span class="label">Active Customers</span><strong class="value"><?= (int) $totals['active_cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Conversion Rate</span><strong class="value"><?= e($conversionRate) ?>%</strong></div>
  <div class="stat-card"><span class="label">Lost / Rejected</span><strong class="value"><?= (int) $totals['lost_cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Lifetime Transaction Volume</span><strong class="value"><?= e(format_amount($lifetimeVolume)) ?></strong></div>
  <div class="stat-card"><span class="label">Lifetime Commission Earned</span><strong class="value"><?= e(format_amount($lifetimeCommission)) ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Acquisition Funnel</h2></div>
  <canvas id="chart-funnel" height="100"></canvas>
</div>

<div class="grid-2" style="display:grid;gap:20px;grid-template-columns:1fr 1fr;margin-bottom:24px;">
  <div class="panel" style="margin-bottom:0;">
    <div class="panel-head"><h2>Transaction Growth (12 Months)</h2></div>
    <canvas id="chart-txn-growth" height="180"></canvas>
  </div>
  <div class="panel" style="margin-bottom:0;">
    <div class="panel-head"><h2>Commission Growth (12 Months)</h2></div>
    <canvas id="chart-commission-growth" height="180"></canvas>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Top Customers by Volume</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Customer</th><th>Transactions</th><th>Volume</th></tr></thead>
      <tbody>
        <?php if (empty($topCustomers)): ?>
          <tr><td colspan="3"><div class="empty-state">No transaction activity yet.</div></td></tr>
        <?php else: foreach ($topCustomers as $c): ?>
          <tr>
            <td><?= e($c['business_name']) ?></td>
            <td><?= (int) $c['txn_cnt'] ?></td>
            <td><?= e(format_amount((float) $c['volume'])) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script nonce="<?= csp_nonce() ?>">
(function () {
  var funnelLabels = <?= json_encode(array_values($funnelStages), JSON_UNESCAPED_SLASHES) ?>;
  var funnelKeys = <?= json_encode(array_keys($funnelStages), JSON_UNESCAPED_SLASHES) ?>;
  var funnelCounts = <?= json_encode($funnelCounts, JSON_UNESCAPED_SLASHES) ?>;
  var funnelData = funnelKeys.map(function (k) { return funnelCounts[k] || 0; });

  var txnTrend = <?= json_encode($txnTrend, JSON_UNESCAPED_SLASHES) ?>;
  var commissionTrend = <?= json_encode($commissionTrend, JSON_UNESCAPED_SLASHES) ?>;

  if (window.Chart) {
    new Chart(document.getElementById('chart-funnel'), {
      type: 'bar',
      data: { labels: funnelLabels, datasets: [{ data: funnelData, backgroundColor: '#00a69d' }] },
      options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    new Chart(document.getElementById('chart-txn-growth'), {
      type: 'line',
      data: { labels: txnTrend.map(function (r) { return r.ym; }), datasets: [{ data: txnTrend.map(function (r) { return r.total; }), borderColor: '#00a69d', backgroundColor: 'rgba(0,166,157,0.12)', fill: true, tension: 0.3 }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('chart-commission-growth'), {
      type: 'bar',
      data: { labels: commissionTrend.map(function (r) { return r.ym; }), datasets: [{ data: commissionTrend.map(function (r) { return r.total; }), backgroundColor: '#ff5500' }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
  }
})();
</script>
