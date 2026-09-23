<?php
/**
 * Admin — Maker-Checker Change Requests.
 * Sensitive user-record fields (email, mobile, full name) are never
 * edited directly: a request is raised with a reason, and a SECOND
 * authorized admin/super_admin must approve it before it is applied.
 * Every step is written to audit_logs.
 */
$pdo = db();

$fieldLabels = ['email' => 'Email', 'mobile' => 'Mobile Number', 'full_name' => 'Full Name'];
$statusLabels = ['pending' => 'Pending Approval', 'approved' => 'Approved', 'rejected' => 'Rejected', 'applied' => 'Applied'];
$statusBadge = ['pending' => 'pending', 'approved' => 'info', 'rejected' => 'failed', 'applied' => 'success'];

$requestId = $route_param !== null ? (int) $route_param : null;

// =====================================================================
// Detail + approve/reject: /admin/change-requests/{id}
// =====================================================================
if ($requestId !== null) {
    $stmt = $pdo->prepare(
        'SELECT cr.*, tu.full_name AS target_name, tu.email AS target_email,
                ru.full_name AS requester_name, du.full_name AS decider_name
         FROM change_requests cr
         JOIN users tu ON tu.id = cr.target_user_id
         JOIN users ru ON ru.id = cr.requested_by
         LEFT JOIN users du ON du.id = cr.decided_by
         WHERE cr.id = :id'
    );
    $stmt->execute(['id' => $requestId]);
    $req = $stmt->fetch();

    if (!$req) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
        exit;
    }

    $errors = [];
    $notice = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $req['status'] === 'pending') {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Your session expired. Please refresh the page and try again.';
        } elseif ((int) $req['requested_by'] === (int) $auth_user['id']) {
            $errors[] = 'Maker-checker requires a different authorized approver — you cannot approve your own request.';
        } else {
            $action = (string) ($_POST['form_action'] ?? '');
            $decisionNote = sanitize_input((string) ($_POST['decision_note'] ?? ''));

            if ($action === 'approve') {
                $pdo->beginTransaction();
                try {
                    if (!array_key_exists($req['field_name'], $fieldLabels)) {
                        throw new RuntimeException('Unsupported field — cannot apply automatically.');
                    }
                    $column = $req['field_name'];
                    $pdo->prepare("UPDATE users SET {$column} = :val WHERE id = :id")
                        ->execute(['val' => $req['new_value'], 'id' => $req['target_user_id']]);
                    $pdo->prepare(
                        'UPDATE change_requests SET status = "applied", decided_by = :decider, decision_note = :note, decided_at = NOW() WHERE id = :id'
                    )->execute(['decider' => $auth_user['id'], 'note' => $decisionNote ?: null, 'id' => $requestId]);

                    log_partner_activity($pdo, null, 'admin.change_request.approved', 'change_request', $requestId, [
                        'target_user_id' => $req['target_user_id'], 'field' => $req['field_name'], 'approver_id' => $auth_user['id'],
                    ]);
                    $pdo->commit();

                    $req['status'] = 'applied';
                    $notice = 'Change approved and applied.';
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    error_log('[Paynancial] Change request approval failed: ' . $e->getMessage());
                    $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'Something went wrong while applying this change.';
                }
            } elseif ($action === 'reject') {
                if ($decisionNote === '') {
                    $errors[] = 'A reason is required to reject a change request.';
                } else {
                    $pdo->prepare(
                        'UPDATE change_requests SET status = "rejected", decided_by = :decider, decision_note = :note, decided_at = NOW() WHERE id = :id'
                    )->execute(['decider' => $auth_user['id'], 'note' => $decisionNote, 'id' => $requestId]);
                    log_partner_activity($pdo, null, 'admin.change_request.rejected', 'change_request', $requestId, [
                        'target_user_id' => $req['target_user_id'], 'field' => $req['field_name'], 'approver_id' => $auth_user['id'],
                    ]);
                    $req['status'] = 'rejected';
                    $notice = 'Change request rejected.';
                }
            }
        }
    }

    $page_meta = ['title' => 'Change Request ' . $req['request_code'] . ' | Paynancial Admin', 'heading' => 'Change Request'];
    ?>
    <div class="panel" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
      <div>
        <a href="/admin/change-requests" class="text-muted" style="font-size:0.82rem;">&larr; All Change Requests</a>
        <h2 style="font-size:1.25rem;margin-top:8px;"><?= e($fieldLabels[$req['field_name']] ?? $req['field_name']) ?> change for <?= e($req['target_name']) ?></h2>
        <div class="flex gap-3" style="margin-top:8px;align-items:center;">
          <span class="mono text-muted" style="font-size:0.82rem;"><?= e($req['request_code']) ?></span>
          <span class="badge <?= e($statusBadge[$req['status']] ?? 'info') ?>"><?= e($statusLabels[$req['status']] ?? $req['status']) ?></span>
        </div>
      </div>
    </div>

    <?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
    <?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

    <div class="panel">
      <div class="panel-head"><h2>Request Details</h2></div>
      <div class="ledger">
        <div class="ledger-row"><span class="ledger-tag">Target Account</span><h3 style="font-size:0.95rem;"><?= e($req['target_name']) ?> &middot; <?= e($req['target_email']) ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Field</span><h3 style="font-size:0.95rem;"><?= e($fieldLabels[$req['field_name']] ?? $req['field_name']) ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Current Value (masked)</span><h3 style="font-size:0.95rem;"><?= e($req['old_value_masked'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Requested New Value</span><h3 style="font-size:0.95rem;"><?= e($req['new_value']) ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Reason</span><h3 style="font-size:0.95rem;"><?= e($req['reason']) ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Requested By</span><h3 style="font-size:0.95rem;"><?= e($req['requester_name']) ?> &middot; <?= e(date('d M Y, H:i', strtotime($req['created_at']))) ?></h3><span></span></div>
        <?php if ($req['decided_by']): ?>
        <div class="ledger-row"><span class="ledger-tag">Decided By</span><h3 style="font-size:0.95rem;"><?= e($req['decider_name']) ?> &middot; <?= e(date('d M Y, H:i', strtotime($req['decided_at']))) ?></h3><span></span></div>
        <?php endif; ?>
        <?php if ($req['decision_note']): ?>
        <div class="ledger-row"><span class="ledger-tag">Decision Note</span><h3 style="font-size:0.95rem;"><?= e($req['decision_note']) ?></h3><span></span></div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($req['status'] === 'pending'): ?>
    <div class="panel">
      <div class="panel-head"><h2>Approve or Reject</h2></div>
      <?php if ((int) $req['requested_by'] === (int) $auth_user['id']): ?>
        <p class="text-muted">You raised this request. A different authorized admin must review and decide it — maker and checker cannot be the same person.</p>
      <?php else: ?>
      <form method="post" class="flex gap-2" style="align-items:flex-end;flex-wrap:wrap;">
        <?= csrf_field() ?>
        <div class="field" style="flex:1;min-width:220px;"><label>Decision Note (required to reject)</label><input type="text" name="decision_note" placeholder="Note"></div>
        <button type="submit" name="form_action" value="approve" class="btn btn-primary btn-sm js-confirm" data-confirm="Approve and apply this change now?">Approve &amp; Apply</button>
        <button type="submit" name="form_action" value="reject" class="btn btn-outline btn-sm js-confirm" data-confirm="Reject this change request?">Reject</button>
      </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php
    return;
}

// =====================================================================
// List + initiate: /admin/change-requests
// =====================================================================
$page_meta = ['title' => 'Change Requests | Paynancial Admin', 'heading' => 'Change Requests'];

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $targetEmail = sanitize_input((string) ($_POST['target_email'] ?? ''));
        $fieldName = sanitize_input((string) ($_POST['field_name'] ?? ''));
        $newValue = sanitize_input((string) ($_POST['new_value'] ?? ''));
        $reason = sanitize_input((string) ($_POST['reason'] ?? ''));

        if (!array_key_exists($fieldName, $fieldLabels)) { $errors[] = 'Please select a valid field.'; }
        if ($newValue === '') { $errors[] = 'Please enter the new value.'; }
        if ($reason === '') { $errors[] = 'A reason is required for every change request.'; }

        $targetUser = null;
        if (empty($errors)) {
            $userStmt = $pdo->prepare('SELECT id, email, mobile, full_name FROM users WHERE email = :email');
            $userStmt->execute(['email' => $targetEmail]);
            $targetUser = $userStmt->fetch();
            if (!$targetUser) { $errors[] = 'No account found with that email.'; }
        }

        if (empty($errors) && $fieldName === 'email' && !is_valid_email($newValue)) { $errors[] = 'Please enter a valid email address.'; }
        if (empty($errors) && $fieldName === 'mobile' && !is_valid_mobile($newValue)) { $errors[] = 'Please enter a valid mobile number.'; }

        if (empty($errors)) {
            $currentValue = (string) ($targetUser[$fieldName] ?? '');
            $oldMasked = in_array($fieldName, ['email', 'mobile'], true) && $currentValue !== '' ? mask_destination($currentValue) : $currentValue;

            $requestCode = generate_sequential_code($pdo, 'change_requests', 'request_code', 'PYN-CHG');
            $pdo->prepare(
                'INSERT INTO change_requests (request_code, target_user_id, field_name, old_value_masked, new_value, reason, requested_by)
                 VALUES (:code, :tid, :field, :old, :new, :reason, :requester)'
            )->execute([
                'code' => $requestCode, 'tid' => $targetUser['id'], 'field' => $fieldName,
                'old' => $oldMasked ?: null, 'new' => $newValue, 'reason' => $reason, 'requester' => $auth_user['id'],
            ]);
            $newRequestId = (int) $pdo->lastInsertId();

            log_partner_activity($pdo, null, 'admin.change_request.raised', 'change_request', $newRequestId, [
                'target_user_id' => $targetUser['id'], 'field' => $fieldName, 'requester_id' => $auth_user['id'],
            ]);

            $notice = "Change request {$requestCode} raised and is awaiting a second approver.";
        }
    }
}

$statusFilter = sanitize_input((string) ($_GET['status'] ?? ''));
$sql = 'SELECT cr.id, cr.request_code, cr.field_name, cr.status, cr.created_at, tu.full_name AS target_name, ru.full_name AS requester_name
        FROM change_requests cr
        JOIN users tu ON tu.id = cr.target_user_id
        JOIN users ru ON ru.id = cr.requested_by
        WHERE 1=1';
$params = [];
if (array_key_exists($statusFilter, $statusLabels)) {
    $sql .= ' AND cr.status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY cr.created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pendingCount = (int) $pdo->query("SELECT COUNT(*) FROM change_requests WHERE status = 'pending'")->fetchColumn();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Pending Approval</span><strong class="value"><?= $pendingCount ?></strong></div>
</div>

<?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
<?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Raise a Change Request</h2></div>
  <p class="text-muted" style="margin-bottom:16px;">Sensitive account fields are never edited directly — every change needs a reason and a second admin's approval.</p>
  <form method="post" class="field-grid" style="align-items:flex-end;">
    <?= csrf_field() ?>
    <div class="field"><label>Target Account Email</label><input type="email" name="target_email" required></div>
    <div class="field"><label>Field to Change</label>
      <select name="field_name" required>
        <option value="">Select field</option>
        <?php foreach ($fieldLabels as $slug => $label): ?><option value="<?= e($slug) ?>"><?= e($label) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label>New Value</label><input type="text" name="new_value" required></div>
    <div class="field" style="grid-column:1 / -1;"><label>Reason</label><input type="text" name="reason" required placeholder="Why is this change needed?"></div>
    <div style="grid-column:1 / -1;"><button type="submit" class="btn btn-primary">Submit for Approval</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All Change Requests</h2></div>
  <form method="get" class="toolbar" style="margin-bottom:18px;">
    <select name="status">
      <option value="">All statuses</option>
      <?php foreach ($statusLabels as $slug => $label): ?>
        <option value="<?= e($slug) ?>" <?= $statusFilter === $slug ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Request ID</th><th>Target</th><th>Field</th><th>Requested By</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7"><div class="empty-state">No change requests yet.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr>
            <td class="mono"><?= e($row['request_code']) ?></td>
            <td><?= e($row['target_name']) ?></td>
            <td><?= e($fieldLabels[$row['field_name']] ?? $row['field_name']) ?></td>
            <td><?= e($row['requester_name']) ?></td>
            <td><span class="badge <?= e($statusBadge[$row['status']] ?? 'info') ?>"><?= e($statusLabels[$row['status']] ?? $row['status']) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $row['created_at']))) ?></td>
            <td><a href="/admin/change-requests/<?= (int) $row['id'] ?>" class="btn btn-outline btn-sm">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
