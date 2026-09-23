<?php
/** Partner Hub — Settlements: payout periods and their status. */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Settlements | Paynancial Partner Hub', 'heading' => 'Settlements'];

$pdo = db();

$kpiStmt = $pdo->prepare(
    "SELECT
       COALESCE(SUM(CASE WHEN status = 'settled' THEN net_amount ELSE 0 END), 0) AS total_settled,
       COALESCE(SUM(CASE WHEN status IN ('pending','processing') THEN net_amount ELSE 0 END), 0) AS pending_amount,
       COALESCE(SUM(CASE WHEN status = 'on_hold' THEN net_amount ELSE 0 END), 0) AS on_hold_amount,
       MAX(CASE WHEN status = 'settled' THEN settled_at ELSE NULL END) AS last_settled_at
     FROM settlements WHERE partner_id = :pid"
);
$kpiStmt->execute(['pid' => $partnerId]);
$kpi = $kpiStmt->fetch();

$settlements = $pdo->prepare(
    'SELECT settlement_ref, period_start, period_end, gross_amount, fee_amount, net_amount, status, settled_at
     FROM settlements WHERE partner_id = :pid ORDER BY period_end DESC LIMIT 50'
);
$settlements->execute(['pid' => $partnerId]);
$settlementRows = $settlements->fetchAll();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Settled</span><strong class="value"><?= e(format_amount((float) $kpi['total_settled'])) ?></strong></div>
  <div class="stat-card"><span class="label">Pending Settlement</span><strong class="value"><?= e(format_amount((float) $kpi['pending_amount'])) ?></strong></div>
  <div class="stat-card"><span class="label">On Hold</span><strong class="value"><?= e(format_amount((float) $kpi['on_hold_amount'])) ?></strong></div>
  <div class="stat-card"><span class="label">Last Settled</span><strong class="value" style="font-size:1.1rem;"><?= $kpi['last_settled_at'] ? e(date('d M Y', strtotime((string) $kpi['last_settled_at']))) : '—' ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Settlement History</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Reference</th><th>Period</th><th>Gross</th><th>Fee</th><th>Net</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (empty($settlementRows)): ?>
          <tr><td colspan="6"><div class="empty-state">No settlements recorded yet.</div></td></tr>
        <?php else: foreach ($settlementRows as $row): ?>
          <tr>
            <td class="mono"><?= e($row['settlement_ref']) ?></td>
            <td><?= e(date('d M', strtotime((string) $row['period_start']))) ?> &ndash; <?= e(date('d M Y', strtotime((string) $row['period_end']))) ?></td>
            <td><?= e(format_amount((float) $row['gross_amount'])) ?></td>
            <td><?= e(format_amount((float) $row['fee_amount'])) ?></td>
            <td><?= e(format_amount((float) $row['net_amount'])) ?></td>
            <td><span class="badge <?= $row['status'] === 'settled' ? 'success' : ($row['status'] === 'on_hold' ? 'failed' : 'pending') ?>"><?= e(ucfirst(str_replace('_', ' ', $row['status']))) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
