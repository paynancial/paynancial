<?php
/** Partner Hub — dashboard home. */
$context = require_partner_context();
$partnerId = $context['partner_id'];

$pdo = db();
$stmt = $pdo->prepare('SELECT partner_code, business_name, status, kyc_status FROM partners WHERE id = :pid');
$stmt->execute(['pid' => $partnerId]);
$partner = $stmt->fetch() ?: ['partner_code' => '—', 'business_name' => '', 'status' => 'active', 'kyc_status' => 'not_started'];

$page_meta = ['title' => 'Partner Dashboard | Paynancial', 'heading' => 'Dashboard'];

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

// ---- Customer pipeline KPIs ----
$custStmt = $pdo->prepare(
    "SELECT COUNT(*) AS total,
     SUM(CASE WHEN pipeline_stage = 'active' THEN 1 ELSE 0 END) AS active_cnt,
     SUM(CASE WHEN pipeline_stage NOT IN ('active','lost','rejected') THEN 1 ELSE 0 END) AS pending_cnt,
     SUM(CASE WHEN pipeline_stage = 'documents_pending' THEN 1 ELSE 0 END) AS docs_pending,
     SUM(CASE WHEN pipeline_stage = 'under_review' THEN 1 ELSE 0 END) AS under_review,
     SUM(CASE WHEN pipeline_stage = 'approved' THEN 1 ELSE 0 END) AS approved_cnt,
     SUM(CASE WHEN pipeline_stage = 'integration' THEN 1 ELSE 0 END) AS integration_cnt
     FROM customer_applications WHERE partner_id = :pid"
);
$custStmt->execute(['pid' => $partnerId]);
$cust = $custStmt->fetch();
$conversionRate = $cust['total'] > 0 ? round(($cust['active_cnt'] / $cust['total']) * 100, 1) : 0.0;

// ---- Transactions ----
$txnStmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total FROM transactions WHERE partner_id = :pid AND status = 'success'");
$txnStmt->execute(['pid' => $partnerId]);
$txn = $txnStmt->fetch();

$pendingSettleStmt = $pdo->prepare("SELECT COALESCE(SUM(net_amount),0) FROM settlements WHERE partner_id = :pid AND status IN ('pending','processing')");
$pendingSettleStmt->execute(['pid' => $partnerId]);
$pendingSettlement = (float) $pendingSettleStmt->fetchColumn();

// ---- Commission ----
$commissionStmt = $pdo->prepare(
    "SELECT
       COALESCE(SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') THEN amount ELSE 0 END),0) AS this_month,
       COALESCE(SUM(CASE WHEN status = 'accrued' THEN amount ELSE 0 END),0) AS pending
     FROM commissions WHERE partner_id = :pid"
);
$commissionStmt->execute(['pid' => $partnerId]);
$commission = $commissionStmt->fetch();

// ---- Charts: last 6 months acquisition + transaction volume ----
$acquisition = $pdo->prepare(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt FROM customer_applications
     WHERE partner_id = :pid AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY ym ORDER BY ym"
);
$acquisition->execute(['pid' => $partnerId]);
$acquisitionRows = $acquisition->fetchAll();

$volumeStmt = $pdo->prepare(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS total FROM transactions
     WHERE partner_id = :pid AND status = 'success' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY ym ORDER BY ym"
);
$volumeStmt->execute(['pid' => $partnerId]);
$volumeRows = $volumeStmt->fetchAll();

// ---- Recent customer activity ----
$recentActivity = $pdo->prepare(
    'SELECT application_code, business_name, pipeline_stage, updated_at FROM customer_applications
     WHERE partner_id = :pid ORDER BY updated_at DESC LIMIT 6'
);
$recentActivity->execute(['pid' => $partnerId]);
$recentRows = $recentActivity->fetchAll();

// ---- Support snapshot ----
$openTicketsStmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id = :uid AND status != 'closed'");
$openTicketsStmt->execute(['uid' => $auth_user['id']]);
$openTickets = (int) $openTicketsStmt->fetchColumn();

$stageLabels = [
    'new_lead' => 'New Lead', 'contacted' => 'Contacted', 'qualified' => 'Qualified',
    'proposal_sent' => 'Proposal Sent', 'documents_pending' => 'Documents Pending', 'kyc_submitted' => 'KYC Submitted',
    'under_review' => 'Under Review', 'approved' => 'Approved', 'integration' => 'Integration Pending',
    'active' => 'Active', 'lost' => 'Lost', 'rejected' => 'Rejected',
];
?>
<div class="panel" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px;">
  <div>
    <h2 style="font-size:1.3rem;"><?= e($greeting) ?>, <?= e($auth_user['name']) ?></h2>
    <p class="text-muted" style="margin-top:6px;">Welcome to the Paynancial Partner Hub.</p>
    <div class="flex gap-3" style="margin-top:12px;align-items:center;">
      <span class="mono text-muted" style="font-size:0.82rem;">Partner ID: <?= e($partner['partner_code']) ?></span>
      <span class="badge <?= $partner['status'] === 'active' ? 'success' : 'failed' ?>"><?= e(strtoupper($partner['status'])) ?></span>
    </div>
  </div>
  <a href="/partner/enroll-customer" class="btn btn-primary" style="font-size:1rem;padding:16px 28px;">+ Enroll New Customer</a>
</div>

<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Customers</span><strong class="value"><?= (int) $cust['total'] ?></strong></div>
  <div class="stat-card"><span class="label">Active Customers</span><strong class="value"><?= (int) $cust['active_cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Pending Applications</span><strong class="value"><?= (int) $cust['pending_cnt'] ?></strong></div>
  <div class="stat-card"><span class="label">Transaction Value</span><strong class="value"><?= e(format_amount((float) $txn['total'])) ?></strong></div>
  <div class="stat-card"><span class="label">Pending Settlements</span><strong class="value"><?= e(format_amount($pendingSettlement)) ?></strong></div>
  <div class="stat-card"><span class="label">Commission (This Month)</span><strong class="value"><?= e(format_amount((float) $commission['this_month'])) ?></strong></div>
  <div class="stat-card"><span class="label">Pending Commission</span><strong class="value"><?= e(format_amount((float) $commission['pending'])) ?></strong></div>
  <div class="stat-card"><span class="label">Conversion Rate</span><strong class="value"><?= e($conversionRate) ?>%</strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Quick Actions</h2></div>
  <div class="pill-list">
    <a class="pill" href="/partner/enroll-customer">Enroll Customer</a>
    <a class="pill" href="/partner/proposals">Create Proposal</a>
    <a class="pill" href="/partner/payment-links">Create Payment Link</a>
    <a class="pill" href="/partner/products">Explore Solutions</a>
    <a class="pill" href="/partner/transactions">View Transactions</a>
    <a class="pill" href="/partner/support">Contact Support</a>
  </div>
</div>

<div class="grid-2" style="display:grid;gap:20px;grid-template-columns:1fr 1fr;margin-bottom:24px;">
  <div class="panel" style="margin-bottom:0;">
    <div class="panel-head"><h2>Customer Acquisition</h2></div>
    <canvas id="chart-acquisition" height="180"></canvas>
  </div>
  <div class="panel" style="margin-bottom:0;">
    <div class="panel-head"><h2>Transaction Volume</h2></div>
    <canvas id="chart-volume" height="180"></canvas>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Customer Onboarding</h2>
    <a href="/partner/customers" class="btn btn-outline btn-sm">View Applications</a>
  </div>
  <div class="stat-grid" style="margin-bottom:0;">
    <div class="stat-card"><span class="label">Documents Pending</span><strong class="value"><?= (int) $cust['docs_pending'] ?></strong></div>
    <div class="stat-card"><span class="label">Under Review</span><strong class="value"><?= (int) $cust['under_review'] ?></strong></div>
    <div class="stat-card"><span class="label">Approved</span><strong class="value"><?= (int) $cust['approved_cnt'] ?></strong></div>
    <div class="stat-card"><span class="label">Integration Pending</span><strong class="value"><?= (int) $cust['integration_cnt'] ?></strong></div>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Recent Customer Activity</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Customer</th><th>Application ID</th><th>Stage</th><th>Updated</th></tr></thead>
      <tbody>
        <?php if (empty($recentRows)): ?>
          <tr><td colspan="4"><div class="empty-state">No customer activity yet. <a href="/partner/enroll-customer">Enroll your first customer</a>.</div></td></tr>
        <?php else: foreach ($recentRows as $row): ?>
          <tr>
            <td><?= e($row['business_name']) ?></td>
            <td class="mono"><?= e($row['application_code']) ?></td>
            <td><span class="badge info"><?= e($stageLabels[$row['pipeline_stage']] ?? $row['pipeline_stage']) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $row['updated_at']))) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel" style="display:flex;justify-content:space-between;align-items:center;">
  <div>
    <h2 style="font-size:1.05rem;">Support</h2>
    <p class="text-muted" style="margin-top:4px;">Open Tickets: <strong><?= $openTickets ?></strong></p>
  </div>
  <a href="/partner/support" class="btn btn-outline btn-sm">View Support</a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script nonce="<?= csp_nonce() ?>">
(function () {
  var acquisitionData = <?= json_encode($acquisitionRows, JSON_UNESCAPED_SLASHES) ?>;
  var volumeData = <?= json_encode($volumeRows, JSON_UNESCAPED_SLASHES) ?>;

  function chartOpts() {
    return { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } };
  }

  if (window.Chart) {
    new Chart(document.getElementById('chart-acquisition'), {
      type: 'bar',
      data: { labels: acquisitionData.map(function (r) { return r.ym; }), datasets: [{ data: acquisitionData.map(function (r) { return r.cnt; }), backgroundColor: '#00a69d' }] },
      options: chartOpts()
    });
    new Chart(document.getElementById('chart-volume'), {
      type: 'line',
      data: { labels: volumeData.map(function (r) { return r.ym; }), datasets: [{ data: volumeData.map(function (r) { return r.total; }), borderColor: '#ff5500', backgroundColor: 'rgba(255,85,0,0.1)', fill: true, tension: 0.3 }] },
      options: chartOpts()
    });
  }
})();
</script>
