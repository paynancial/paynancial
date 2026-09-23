<?php
/** Admin — Customer Applications oversight: visibility across every partner's pipeline. */
$pdo = db();
$page_meta = ['title' => 'Customer Applications | Paynancial Admin', 'heading' => 'Customer Applications'];

$stageLabels = [
    'new_lead' => 'New Lead', 'contacted' => 'Contacted', 'qualified' => 'Qualified',
    'proposal_sent' => 'Proposal Sent', 'documents_pending' => 'Documents Pending', 'kyc_submitted' => 'KYC Submitted',
    'under_review' => 'Under Review', 'approved' => 'Approved', 'integration' => 'Integration', 'active' => 'Active',
    'lost' => 'Lost', 'rejected' => 'Rejected',
];
$stageBadge = ['active' => 'success', 'approved' => 'success', 'lost' => 'failed', 'rejected' => 'failed', 'under_review' => 'pending', 'documents_pending' => 'pending', 'kyc_submitted' => 'pending'];

$stageFilter = sanitize_input((string) ($_GET['stage'] ?? ''));
$search = sanitize_input((string) ($_GET['q'] ?? ''));

$sql = 'SELECT ca.application_code, ca.business_name, ca.customer_type, ca.pipeline_stage, ca.created_at, p.business_name AS partner_name, p.partner_code
        FROM customer_applications ca JOIN partners p ON p.id = ca.partner_id WHERE 1=1';
$params = [];
if (array_key_exists($stageFilter, $stageLabels)) {
    $sql .= ' AND ca.pipeline_stage = :stage';
    $params['stage'] = $stageFilter;
}
if ($search !== '') {
    $sql .= ' AND (ca.business_name LIKE :q1 OR p.business_name LIKE :q2 OR ca.application_code LIKE :q3)';
    $params['q1'] = $params['q2'] = $params['q3'] = '%' . $search . '%';
}
$sql .= ' ORDER BY ca.updated_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$totalsStmt = $pdo->query("SELECT COUNT(*) AS total, SUM(pipeline_stage = 'active') AS active_cnt FROM customer_applications");
$totals = $totalsStmt->fetch();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Customer Applications</span><strong class="value"><?= (int) $totals['total'] ?></strong></div>
  <div class="stat-card"><span class="label">Active Customers</span><strong class="value"><?= (int) $totals['active_cnt'] ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>All Customer Applications</h2></div>
  <form method="get" class="toolbar" style="margin-bottom:18px;">
    <input type="text" name="q" placeholder="Search customer, partner, or application ID" value="<?= e($search) ?>" style="min-width:240px;">
    <select name="stage">
      <option value="">All stages</option>
      <?php foreach ($stageLabels as $slug => $label): ?>
        <option value="<?= e($slug) ?>" <?= $stageFilter === $slug ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Application ID</th><th>Customer</th><th>Partner</th><th>Type</th><th>Stage</th><th>Updated</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6"><div class="empty-state">No customer applications match this filter.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr>
            <td class="mono"><?= e($row['application_code']) ?></td>
            <td><?= e($row['business_name']) ?></td>
            <td><?= e($row['partner_name']) ?> <span class="mono text-muted" style="font-size:0.72rem;">(<?= e($row['partner_code']) ?>)</span></td>
            <td><?= e(ucfirst(str_replace('_', ' ', $row['customer_type']))) ?></td>
            <td><span class="badge <?= e($stageBadge[$row['pipeline_stage']] ?? 'info') ?>"><?= e($stageLabels[$row['pipeline_stage']] ?? $row['pipeline_stage']) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $row['created_at']))) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
