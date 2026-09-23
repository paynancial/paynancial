<?php
/**
 * Partner Hub — Commissions.
 *
 * Rates shown here always come from the admin-managed commission_rules
 * table — never hardcoded. A partner cannot see or set their own rate;
 * only what admin has configured for each product / rule type.
 */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Commissions | Paynancial Partner Hub', 'heading' => 'Commissions'];

$pdo = db();

$kpiStmt = $pdo->prepare(
    "SELECT
       COALESCE(SUM(CASE WHEN status IN ('accrued','paid') THEN amount ELSE 0 END), 0) AS total_earned,
       COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) AS total_paid,
       COALESCE(SUM(CASE WHEN status = 'accrued' THEN amount ELSE 0 END), 0) AS pending_amount,
       COALESCE(SUM(CASE WHEN status != 'reversed' AND DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') THEN amount ELSE 0 END), 0) AS this_month
     FROM commissions WHERE partner_id = :pid"
);
$kpiStmt->execute(['pid' => $partnerId]);
$kpi = $kpiStmt->fetch();

$trendStmt = $pdo->prepare(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS total
     FROM commissions WHERE partner_id = :pid AND status != 'reversed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY ym ORDER BY ym"
);
$trendStmt->execute(['pid' => $partnerId]);
$trendRows = $trendStmt->fetchAll();

$historyStmt = $pdo->prepare(
    'SELECT amount, rate_applied, status, created_at FROM commissions WHERE partner_id = :pid ORDER BY created_at DESC LIMIT 50'
);
$historyStmt->execute(['pid' => $partnerId]);
$historyRows = $historyStmt->fetchAll();

$rulesStmt = $pdo->query(
    "SELECT cr.name, cr.rule_type, cr.rate_percent, cr.tier_min_volume, cr.tier_max_volume, p.name AS product_name
     FROM commission_rules cr LEFT JOIN products p ON p.id = cr.product_id
     WHERE cr.is_active = 1 ORDER BY cr.rule_type, p.sort_order"
);
$ruleRows = $rulesStmt->fetchAll();

$ruleTypeLabels = ['product_based' => 'Product-based', 'revenue_share' => 'Revenue Share', 'referral' => 'Referral', 'tiered' => 'Tiered Volume'];
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Earned</span><strong class="value"><?= e(format_amount((float) $kpi['total_earned'])) ?></strong></div>
  <div class="stat-card"><span class="label">Paid Out</span><strong class="value"><?= e(format_amount((float) $kpi['total_paid'])) ?></strong></div>
  <div class="stat-card"><span class="label">Pending (Accrued)</span><strong class="value"><?= e(format_amount((float) $kpi['pending_amount'])) ?></strong></div>
  <div class="stat-card"><span class="label">This Month</span><strong class="value"><?= e(format_amount((float) $kpi['this_month'])) ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Commission Trend (Last 6 Months)</h2></div>
  <canvas id="chart-commission-trend" height="80"></canvas>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Active Commission Rules</h2>
    <span class="text-muted" style="font-size:0.8rem;">Configured by Paynancial admin</span>
  </div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Rule</th><th>Type</th><th>Applies To</th><th>Rate</th><th>Volume Band</th></tr></thead>
      <tbody>
        <?php if (empty($ruleRows)): ?>
          <tr><td colspan="5"><div class="empty-state">No commission rules have been configured yet.</div></td></tr>
        <?php else: foreach ($ruleRows as $rule): ?>
          <tr>
            <td><?= e($rule['name']) ?></td>
            <td><span class="badge info"><?= e($ruleTypeLabels[$rule['rule_type']] ?? $rule['rule_type']) ?></span></td>
            <td><?= e($rule['product_name'] ?? 'All products') ?></td>
            <td class="mono"><?= e(number_format((float) $rule['rate_percent'], 2)) ?>%</td>
            <td>
              <?php if ($rule['tier_min_volume'] !== null || $rule['tier_max_volume'] !== null): ?>
                <?= e(format_amount((float) $rule['tier_min_volume'])) ?> &ndash; <?= $rule['tier_max_volume'] !== null ? e(format_amount((float) $rule['tier_max_volume'])) : 'above' ?>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Commission History</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Amount</th><th>Rate Applied</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (empty($historyRows)): ?>
          <tr><td colspan="4"><div class="empty-state">No commission activity yet.</div></td></tr>
        <?php else: foreach ($historyRows as $row): ?>
          <tr>
            <td><?= e(format_amount((float) $row['amount'])) ?></td>
            <td class="mono"><?= e(number_format((float) $row['rate_applied'], 2)) ?>%</td>
            <td><span class="badge <?= $row['status'] === 'paid' ? 'success' : ($row['status'] === 'reversed' ? 'failed' : 'info') ?>"><?= e(ucfirst($row['status'])) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $row['created_at']))) ?></td>
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
    new Chart(document.getElementById('chart-commission-trend'), {
      type: 'bar',
      data: { labels: trend.map(function (r) { return r.ym; }), datasets: [{ data: trend.map(function (r) { return r.total; }), backgroundColor: '#ff5500' }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
  }
})();
</script>
