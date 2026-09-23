<?php
/** Partner Hub — onboarding application status pipeline. */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Onboarding Status | Paynancial Partner Hub', 'heading' => 'Onboarding Status'];

$pdo = db();
$stmt = $pdo->prepare('SELECT application_id, status AS account_status, kyc_status, partner_code, onboarded_at FROM partners WHERE id = :pid');
$stmt->execute(['pid' => $partnerId]);
$partner = $stmt->fetch();

$application = null;
$documentCount = 0;
$approvedDocCount = 0;
$manager = null;

if (!empty($partner['application_id'])) {
    $appStmt = $pdo->prepare('SELECT * FROM partner_applications WHERE id = :id');
    $appStmt->execute(['id' => $partner['application_id']]);
    $application = $appStmt->fetch();

    if ($application) {
        $docStmt = $pdo->prepare(
            "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved
             FROM partner_application_documents WHERE application_id = :aid"
        );
        $docStmt->execute(['aid' => $application['id']]);
        $docRow = $docStmt->fetch();
        $documentCount = (int) $docRow['total'];
        $approvedDocCount = (int) $docRow['approved'];

        if (!empty($application['assigned_manager_id'])) {
            $mgrStmt = $pdo->prepare('SELECT full_name, email, mobile FROM users WHERE id = :id');
            $mgrStmt->execute(['id' => $application['assigned_manager_id']]);
            $manager = $mgrStmt->fetch();
        }
    }
}

$steps = [];
if ($application) {
    $status = $application['status'];
    $steps = [
        ['label' => 'Registration', 'state' => 'done'],
        ['label' => 'Business Information', 'state' => 'done'],
        ['label' => 'Documents', 'state' => $documentCount > 0 ? 'done' : 'action'],
        ['label' => 'KYC Verification', 'state' => $approvedDocCount > 0 ? 'done' : ($documentCount > 0 ? 'current' : 'pending')],
        ['label' => 'Compliance Review', 'state' => $status === 'under_review' ? 'current' : (in_array($status, ['approved', 'rejected'], true) ? 'done' : 'pending')],
        ['label' => 'Agreement', 'state' => $application['agreements_accepted'] ? 'done' : 'pending'],
        ['label' => 'Approval', 'state' => $status === 'approved' ? 'done' : ($status === 'rejected' ? 'rejected' : 'pending')],
        ['label' => 'Activated', 'state' => !empty($application['created_partner_id']) ? 'done' : 'pending'],
    ];
}
?>
<?php if (!$application): ?>
  <div class="panel">
    <div class="panel-head"><h2>Account Status</h2></div>
    <div class="stat-grid" style="margin-bottom:0;">
      <div class="stat-card"><span class="label">Partner ID</span><strong class="value" style="font-size:1.1rem;" class="mono"><?= e($partner['partner_code']) ?></strong></div>
      <div class="stat-card"><span class="label">Account Status</span><strong class="value" style="font-size:1.1rem;text-transform:capitalize;"><?= e($partner['account_status']) ?></strong></div>
      <div class="stat-card"><span class="label">KYC Status</span><strong class="value" style="font-size:1.1rem;text-transform:capitalize;"><?= e(str_replace('_', ' ', $partner['kyc_status'])) ?></strong></div>
      <div class="stat-card"><span class="label">Partner Since</span><strong class="value" style="font-size:1.1rem;"><?= $partner['onboarded_at'] ? e(date('d M Y', strtotime($partner['onboarded_at']))) : '—' ?></strong></div>
    </div>
  </div>
  <div class="module-stub">
    <strong>No onboarding application on file</strong>
    This account was activated directly. If you need to update KYC documents or bank details, contact your Partner Manager via Support.
  </div>
<?php else: ?>
  <div class="panel">
    <div class="panel-head">
      <h2>Application <span class="mono"><?= e($application['application_code']) ?></span></h2>
      <span class="badge <?= $application['status'] === 'approved' ? 'success' : ($application['status'] === 'rejected' ? 'failed' : 'pending') ?>"><?= e(strtoupper(str_replace('_', ' ', $application['status']))) ?></span>
    </div>
    <div class="stat-grid" style="margin-bottom:0;">
      <div class="stat-card"><span class="label">Submitted</span><strong class="value" style="font-size:1.1rem;"><?= e(date('d M Y', strtotime($application['submitted_at']))) ?></strong></div>
      <div class="stat-card"><span class="label">Partner Type</span><strong class="value" style="font-size:1.1rem;text-transform:capitalize;"><?= e(str_replace('_', ' ', $application['partner_type'])) ?></strong></div>
      <div class="stat-card"><span class="label">Assigned Manager</span><strong class="value" style="font-size:1.1rem;"><?= $manager ? e($manager['full_name']) : 'Not yet assigned' ?></strong></div>
      <div class="stat-card"><span class="label">Documents Uploaded</span><strong class="value" style="font-size:1.1rem;"><?= $documentCount ?></strong></div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Pipeline</h2></div>
    <div class="onboarding-pipeline">
      <?php foreach ($steps as $step): ?>
        <div class="pipeline-node pipeline-<?= e($step['state']) ?>">
          <span class="pipeline-icon">
            <?= $step['state'] === 'done' ? '✓' : ($step['state'] === 'rejected' ? '✕' : ($step['state'] === 'current' ? '→' : '○')) ?>
          </span>
          <span><?= e($step['label']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($documentCount === 0): ?>
      <div class="form-error is-visible" style="margin-top:20px;">Action Required: no documents uploaded yet. Contact your Partner Manager or Support to submit KYC documents for this application.</div>
    <?php endif; ?>
    <?php if ($application['status'] === 'info_required'): ?>
      <div class="form-error is-visible" style="margin-top:20px;">Action Required: <?= e($application['status_note'] ?: 'Paynancial has requested more information for this application.') ?></div>
    <?php endif; ?>
  </div>

  <?php if ($manager): ?>
  <div class="panel" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
    <div>
      <h2 style="font-size:1.05rem;">Your Paynancial Partner Manager</h2>
      <p class="text-muted" style="margin-top:6px;"><?= e($manager['full_name']) ?> · <?= e($manager['email']) ?><?= $manager['mobile'] ? ' · ' . e($manager['mobile']) : '' ?></p>
    </div>
    <a href="/partner/support" class="btn btn-outline btn-sm">Contact Partner Manager</a>
  </div>
  <?php endif; ?>
<?php endif; ?>
