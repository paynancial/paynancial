<?php
/** Admin — Partner Application Review: approve/reject partner onboarding applications. */
$pdo = db();

$statusLabels = ['submitted' => 'Submitted', 'under_review' => 'Under Review', 'info_required' => 'Info Required', 'approved' => 'Approved', 'rejected' => 'Rejected'];
$statusBadge = ['approved' => 'success', 'rejected' => 'failed', 'info_required' => 'pending', 'under_review' => 'pending', 'submitted' => 'info'];
$docLabels = [
    'company_registration' => 'Company Registration', 'tax_registration' => 'Tax Registration', 'business_license' => 'Business License',
    'pan_tax_id' => 'PAN / Tax ID', 'gst_vat' => 'GST / VAT Registration', 'bank_details' => 'Bank Details Proof',
    'signatory_id' => 'Authorized Signatory ID', 'address_proof' => 'Address Proof', 'other' => 'Other Document',
];

$applicationId = $route_param !== null ? (int) $route_param : null;

// =====================================================================
// Detail + review actions: /admin/partner-applications/{id}
// =====================================================================
if ($applicationId !== null) {
    $stmt = $pdo->prepare('SELECT * FROM partner_applications WHERE id = :id');
    $stmt->execute(['id' => $applicationId]);
    $app = $stmt->fetch();

    if (!$app) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
        exit;
    }

    $errors = [];
    $notice = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Your session expired. Please refresh the page and try again.';
        } else {
            $action = (string) ($_POST['form_action'] ?? '');

            if ($action === 'reject' || $action === 'request_info') {
                $note = sanitize_input((string) ($_POST['status_note'] ?? ''));
                $newStatus = $action === 'reject' ? 'rejected' : 'info_required';
                $pdo->prepare('UPDATE partner_applications SET status = :status, status_note = :note WHERE id = :id')
                    ->execute(['status' => $newStatus, 'note' => $note ?: null, 'id' => $applicationId]);
                log_partner_activity($pdo, null, 'admin.partner_application.' . $newStatus, 'partner_application', $applicationId, ['admin_id' => $auth_user['id']]);
                $app['status'] = $newStatus;
                $notice = 'Application updated.';
            } elseif ($action === 'approve' && $app['status'] !== 'approved') {
                $existingUser = $pdo->prepare('SELECT id FROM users WHERE email = :email');
                $existingUser->execute(['email' => $app['email']]);
                if ($existingUser->fetchColumn()) {
                    $errors[] = 'A user account with this email already exists — cannot auto-create a duplicate. Please resolve manually.';
                } else {
                    $pdo->beginTransaction();
                    try {
                        $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE slug = 'partner'");
                        $roleStmt->execute();
                        $partnerRoleId = (int) $roleStmt->fetchColumn();

                        $randomPassword = bin2hex(random_bytes(16));
                        $userIns = $pdo->prepare(
                            'INSERT INTO users (uuid, role_id, full_name, email, mobile, password_hash, status, email_verified_at)
                             VALUES (UUID(), :role_id, :name, :email, :mobile, :hash, "active", NOW())'
                        );
                        $userIns->execute([
                            'role_id' => $partnerRoleId, 'name' => $app['contact_person'], 'email' => $app['email'],
                            'mobile' => $app['mobile'], 'hash' => password_hash($randomPassword, PASSWORD_DEFAULT),
                        ]);
                        $newUserId = (int) $pdo->lastInsertId();

                        $partnerCode = generate_sequential_code($pdo, 'partners', 'partner_code', 'PYN-PTR');
                        $partnerIns = $pdo->prepare(
                            'INSERT INTO partners (user_id, application_id, partner_code, business_name, website, country, partner_type, kyc_status, status, manager_user_id, onboarded_at)
                             VALUES (:uid, :appid, :code, :bname, :website, :country, :ptype, "pending", "active", :manager, NOW())'
                        );
                        $partnerIns->execute([
                            'uid' => $newUserId, 'appid' => $applicationId, 'code' => $partnerCode, 'bname' => $app['business_name'],
                            'website' => $app['website'], 'country' => $app['country'], 'ptype' => $app['partner_type'],
                            'manager' => $app['assigned_manager_id'],
                        ]);
                        $newPartnerId = (int) $pdo->lastInsertId();

                        $pdo->prepare('UPDATE partner_bank_accounts SET partner_id = :pid WHERE application_id = :appid')
                            ->execute(['pid' => $newPartnerId, 'appid' => $applicationId]);
                        $pdo->prepare('UPDATE partner_agreements SET partner_id = :pid WHERE application_id = :appid')
                            ->execute(['pid' => $newPartnerId, 'appid' => $applicationId]);
                        $pdo->prepare('UPDATE partner_applications SET status = "approved", created_partner_id = :pid WHERE id = :id')
                            ->execute(['pid' => $newPartnerId, 'id' => $applicationId]);

                        // Issue a password-set link via the same mechanism as forgot-password, rather than emailing a raw password.
                        $token = bin2hex(random_bytes(32));
                        $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)')
                            ->execute([
                                'uid' => $newUserId, 'hash' => hash('sha256', $token),
                                'exp' => (new DateTime())->modify('+' . PASSWORD_RESET_TOKEN_TTL_MINUTES . ' minutes')->format('Y-m-d H:i:s'),
                            ]);

                        log_partner_activity($pdo, ['partner_id' => $newPartnerId, 'role_slug' => 'owner'], 'admin.partner_application.approved', 'partner_application', $applicationId, ['admin_id' => $auth_user['id']]);
                        $pdo->commit();

                        $setLink = site_url('/reset-password?token=' . $token);
                        $subject = 'Your Paynancial Partner account is approved';
                        $message = "Hello {$app['contact_person']},\n\nYour Paynancial partner application ({$app['application_code']}) has been approved. Set your password to access the Partner Hub:\n{$setLink}\n\nThis link expires in " . PASSWORD_RESET_TOKEN_TTL_MINUTES . " minutes.\n\nPartner ID: {$partnerCode}";
                        $headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
                        @mail($app['email'], $subject, $message, $headers);

                        $app['status'] = 'approved';
                        $notice = "Partner approved and account created ({$partnerCode}). A password-set link has been emailed to {$app['email']}.";
                    } catch (Throwable $e) {
                        $pdo->rollBack();
                        error_log('[Paynancial] Partner approval failed: ' . $e->getMessage());
                        $errors[] = 'Something went wrong while approving this partner. Please try again.';
                    }
                }
            }
        }
    }

    $docStmt = $pdo->prepare('SELECT * FROM partner_application_documents WHERE application_id = :id ORDER BY uploaded_at');
    $docStmt->execute(['id' => $applicationId]);
    $documents = $docStmt->fetchAll();

    $bankStmt = $pdo->prepare('SELECT bank_name, account_holder, account_number_last4, routing_code FROM partner_bank_accounts WHERE application_id = :id LIMIT 1');
    $bankStmt->execute(['id' => $applicationId]);
    $bank = $bankStmt->fetch();

    $page_meta = ['title' => $app['business_name'] . ' | Paynancial Admin', 'heading' => 'Partner Application'];
    ?>
    <div class="panel" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
      <div>
        <a href="/admin/partner-applications" class="text-muted" style="font-size:0.82rem;">&larr; All Applications</a>
        <h2 style="font-size:1.25rem;margin-top:8px;"><?= e($app['business_name']) ?></h2>
        <div class="flex gap-3" style="margin-top:8px;align-items:center;">
          <span class="mono text-muted" style="font-size:0.82rem;"><?= e($app['application_code']) ?></span>
          <span class="badge <?= e($statusBadge[$app['status']] ?? 'info') ?>"><?= e($statusLabels[$app['status']] ?? $app['status']) ?></span>
        </div>
      </div>
      <?php if (!in_array($app['status'], ['approved'], true)): ?>
        <div class="flex gap-2">
          <form method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="approve">
            <button type="submit" class="btn btn-primary js-confirm" data-confirm="Approve this partner and create their login account?">Approve &amp; Create Account</button>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
    <?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

    <div class="panel">
      <div class="panel-head"><h2>Business Details</h2></div>
      <div class="ledger">
        <div class="ledger-row"><span class="ledger-tag">Partner Type</span><h3 style="font-size:0.95rem;"><?= e(ucwords(str_replace('_', ' ', $app['partner_type']))) ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Contact</span><h3 style="font-size:0.95rem;"><?= e($app['contact_person']) ?> &middot; <?= e($app['email']) ?> &middot; <?= e($app['mobile']) ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Location</span><h3 style="font-size:0.95rem;"><?= e(implode(', ', array_filter([$app['city'], $app['state'], $app['country']]))) ?: '—' ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Website</span><h3 style="font-size:0.95rem;"><?= e($app['website'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Business Type</span><h3 style="font-size:0.95rem;"><?= e($app['business_type'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Industry</span><h3 style="font-size:0.95rem;"><?= e($app['industry'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Years in Business</span><h3 style="font-size:0.95rem;"><?= e($app['years_in_business'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Expected Monthly Volume</span><h3 style="font-size:0.95rem;"><?= e($app['expected_monthly_volume'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Engagement Model</span><h3 style="font-size:0.95rem;"><?= e($app['engagement_model'] ? ucwords(str_replace('_', ' ', $app['engagement_model'])) : '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Address</span><h3 style="font-size:0.95rem;"><?= e($app['business_address'] ?: '—') ?></h3><span></span></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Documents</h2></div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead><tr><th>Document</th><th>Status</th><th>Uploaded</th></tr></thead>
          <tbody>
            <?php if (empty($documents)): ?><tr><td colspan="3"><div class="empty-state">No documents uploaded.</div></td></tr>
            <?php else: foreach ($documents as $doc): ?>
              <tr><td><?= e($docLabels[$doc['doc_type']] ?? $doc['doc_type']) ?></td><td><span class="badge info"><?= e(ucfirst($doc['status'])) ?></span></td><td><?= e(date('d M Y', strtotime((string) $doc['uploaded_at']))) ?></td></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($bank): ?>
        <h3 style="font-size:0.9rem;margin-top:20px;margin-bottom:10px;">Bank Details</h3>
        <p class="text-muted" style="font-size:0.85rem;"><?= e($bank['bank_name']) ?> &middot; <?= e($bank['account_holder']) ?> &middot; Account ending <?= e($bank['account_number_last4']) ?><?= $bank['routing_code'] ? ' · ' . e($bank['routing_code']) : '' ?></p>
      <?php endif; ?>
    </div>

    <?php if (!in_array($app['status'], ['approved'], true)): ?>
    <div class="panel">
      <div class="panel-head"><h2>Request Info / Reject</h2></div>
      <form method="post" class="flex gap-2" style="align-items:flex-end;flex-wrap:wrap;">
        <?= csrf_field() ?>
        <div class="field" style="flex:1;min-width:220px;"><label>Note</label><input type="text" name="status_note" placeholder="Reason or what's missing"></div>
        <button type="submit" name="form_action" value="request_info" class="btn btn-outline btn-sm">Request More Info</button>
        <button type="submit" name="form_action" value="reject" class="btn btn-outline btn-sm js-confirm" data-confirm="Reject this application?">Reject</button>
      </form>
    </div>
    <?php endif; ?>
    <?php
    return;
}

// =====================================================================
// List view: /admin/partner-applications
// =====================================================================
$page_meta = ['title' => 'Partner Applications | Paynancial Admin', 'heading' => 'Partner Applications'];

$statusFilter = sanitize_input((string) ($_GET['status'] ?? ''));
$sql = 'SELECT id, application_code, business_name, partner_type, contact_person, email, status, submitted_at FROM partner_applications WHERE 1=1';
$params = [];
if (array_key_exists($statusFilter, $statusLabels)) {
    $sql .= ' AND status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY submitted_at DESC LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="panel">
  <div class="panel-head"><h2>Partner Applications</h2></div>
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
      <thead><tr><th>Application ID</th><th>Business Name</th><th>Type</th><th>Contact</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7"><div class="empty-state">No partner applications yet.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr>
            <td class="mono"><?= e($row['application_code']) ?></td>
            <td><?= e($row['business_name']) ?></td>
            <td><?= e(ucwords(str_replace('_', ' ', $row['partner_type']))) ?></td>
            <td><?= e($row['contact_person']) ?><br><span class="text-muted" style="font-size:0.78rem;"><?= e($row['email']) ?></span></td>
            <td><span class="badge <?= e($statusBadge[$row['status']] ?? 'info') ?>"><?= e($statusLabels[$row['status']] ?? $row['status']) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $row['submitted_at']))) ?></td>
            <td><a href="/admin/partner-applications/<?= (int) $row['id'] ?>" class="btn btn-outline btn-sm">Review</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
