<?php
/** Admin — Customer eKYC Review: self-service customer signups (no partner). */
$pdo = db();

$docLabels = [
    'pan_card'              => 'PAN Card',
    'business_registration' => 'Business Registration / GST Certificate',
    'address_proof'         => 'Business Address Proof',
    'signatory_id'          => 'Authorized Signatory ID Proof',
    'bank_proof'            => 'Cancelled Cheque / Bank Proof',
];
$docStatusLabels = ['uploaded' => 'Uploaded', 'under_review' => 'Under Review', 'info_required' => 'Info Required', 'verified' => 'Verified', 'rejected' => 'Rejected'];
$docStatusBadge = ['uploaded' => 'info', 'under_review' => 'pending', 'info_required' => 'pending', 'verified' => 'success', 'rejected' => 'failed'];
$statusLabels = ['pending_verification' => 'Verification Pending', 'active' => 'Active', 'suspended' => 'Suspended'];
$statusBadge = ['pending_verification' => 'pending', 'active' => 'success', 'suspended' => 'failed'];

$customerId = $route_param !== null ? (int) $route_param : null;

// =====================================================================
// Detail + review actions: /admin/customer-kyc/{customer_id}
// =====================================================================
if ($customerId !== null) {
    $stmt = $pdo->prepare(
        'SELECT c.*, u.full_name, u.email, u.mobile FROM customers c JOIN users u ON u.id = c.user_id WHERE c.id = :id'
    );
    $stmt->execute(['id' => $customerId]);
    $customer = $stmt->fetch();

    $profileStmt = $pdo->prepare('SELECT * FROM customer_kyc_profiles WHERE customer_id = :cid');
    $profileStmt->execute(['cid' => $customerId]);
    $profile = $profileStmt->fetch();

    if (!$customer || !$profile) {
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

            if ($action === 'doc_status') {
                $docType = sanitize_input((string) ($_POST['doc_type'] ?? ''));
                $newDocStatus = sanitize_input((string) ($_POST['doc_status'] ?? ''));
                $note = sanitize_input((string) ($_POST['status_note'] ?? ''));
                if (array_key_exists($docType, $docLabels) && array_key_exists($newDocStatus, $docStatusLabels)) {
                    $pdo->prepare(
                        'UPDATE customer_kyc_documents SET status = :status, status_note = :note, reviewed_at = NOW()
                         WHERE customer_id = :cid AND doc_type = :type'
                    )->execute(['status' => $newDocStatus, 'note' => $note ?: null, 'cid' => $customerId, 'type' => $docType]);
                    $notice = 'Document status updated.';
                }
            } elseif ($action === 'activate') {
                $pdo->beginTransaction();
                try {
                    $pdo->prepare("UPDATE customers SET status = 'active', kyc_status = 'verified' WHERE id = :id")
                        ->execute(['id' => $customerId]);
                    $pdo->prepare("UPDATE customer_product_activations SET status = 'active' WHERE customer_id = :cid AND status IN ('requested','pending_kyc')")
                        ->execute(['cid' => $customerId]);
                    $pdo->prepare("UPDATE customer_bank_accounts SET status = 'verified' WHERE customer_id = :cid")
                        ->execute(['cid' => $customerId]);
                    $pdo->commit();

                    @mail($customer['email'], 'Your Paynancial account is verified',
                        "Hello {$customer['full_name']},\n\nYour Paynancial business profile has been verified and your account is now active. You can sign in and start using your activated products.\n\n— Paynancial",
                        'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>');

                    $customer['status'] = 'active';
                    $customer['kyc_status'] = 'verified';
                    $notice = 'Customer verified and account activated.';
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    error_log('[Paynancial] Customer KYC activation failed: ' . $e->getMessage());
                    $errors[] = 'Something went wrong while activating this account. Please try again.';
                }
            } elseif ($action === 'suspend') {
                $note = sanitize_input((string) ($_POST['status_note'] ?? ''));
                $pdo->prepare("UPDATE customers SET status = 'suspended' WHERE id = :id")->execute(['id' => $customerId]);
                @mail($customer['email'], 'Update on your Paynancial account',
                    "Hello {$customer['full_name']},\n\nWe were unable to verify your business profile" . ($note ? " ({$note})" : '') . ". Please contact support for next steps.\n\n— Paynancial",
                    'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>');
                $customer['status'] = 'suspended';
                $notice = 'Customer account suspended and notified.';
            }
        }
    }

    $docStmt = $pdo->prepare('SELECT * FROM customer_kyc_documents WHERE customer_id = :cid');
    $docStmt->execute(['cid' => $customerId]);
    $docByType = [];
    foreach ($docStmt->fetchAll() as $d) { $docByType[$d['doc_type']] = $d; }

    $bankStmt = $pdo->prepare('SELECT bank_name, account_holder, account_number_last4, ifsc, status FROM customer_bank_accounts WHERE customer_id = :cid LIMIT 1');
    $bankStmt->execute(['cid' => $customerId]);
    $bank = $bankStmt->fetch();

    $productsStmt = $pdo->query('SELECT slug, name FROM products');
    $productNameBySlug = [];
    foreach ($productsStmt->fetchAll() as $p) { $productNameBySlug[$p['slug']] = $p['name']; }

    $actStmt = $pdo->prepare('SELECT product_slug, status FROM customer_product_activations WHERE customer_id = :cid');
    $actStmt->execute(['cid' => $customerId]);
    $activations = $actStmt->fetchAll();

    $page_meta = ['title' => $profile['legal_business_name'] . ' | Paynancial Admin', 'heading' => 'Customer eKYC Review'];
    ?>
    <div class="panel" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
      <div>
        <a href="/admin/customer-kyc" class="text-muted" style="font-size:0.82rem;">&larr; All Customer eKYC</a>
        <h2 style="font-size:1.25rem;margin-top:8px;"><?= e($profile['legal_business_name']) ?></h2>
        <div class="flex gap-3" style="margin-top:8px;align-items:center;">
          <span class="mono text-muted" style="font-size:0.82rem;"><?= e($customer['customer_code']) ?></span>
          <span class="badge <?= e($statusBadge[$customer['status']] ?? 'info') ?>"><?= e($statusLabels[$customer['status']] ?? $customer['status']) ?></span>
        </div>
      </div>
      <?php if ($customer['status'] !== 'active'): ?>
        <div class="flex gap-2">
          <form method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="activate">
            <button type="submit" class="btn btn-primary js-confirm" data-confirm="Verify this customer's KYC and activate their account?">Verify KYC &amp; Activate</button>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
    <?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

    <div class="panel">
      <div class="panel-head"><h2>Business &amp; Contact Details</h2></div>
      <div class="ledger">
        <div class="ledger-row"><span class="ledger-tag">Contact</span><h3 style="font-size:0.95rem;"><?= e($customer['full_name']) ?> &middot; <?= e($customer['email']) ?><?= $customer['mobile'] ? ' · ' . e($customer['mobile']) : '' ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Business Type</span><h3 style="font-size:0.95rem;"><?= e($profile['business_type'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Industry</span><h3 style="font-size:0.95rem;"><?= e($profile['industry'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Website</span><h3 style="font-size:0.95rem;"><?= e($profile['website_url'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Registered Address</span><h3 style="font-size:0.95rem;"><?= e($profile['registered_address'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">PAN</span><h3 style="font-size:0.95rem;"><?= e($profile['pan'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">GSTIN</span><h3 style="font-size:0.95rem;"><?= e($profile['gstin'] ?: '—') ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Authorized Signatory</span><h3 style="font-size:0.95rem;"><?= e($profile['signatory_name']) ?><?= $profile['signatory_designation'] ? ' (' . e($profile['signatory_designation']) . ')' : '' ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Expected Volume</span><h3 style="font-size:0.95rem;"><?= e($profile['monthly_volume_band'] ?: '—') ?></h3><span></span></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Documents</h2></div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead><tr><th>Document</th><th>Status</th><th>Uploaded</th><th>Update</th></tr></thead>
          <tbody>
            <?php foreach ($docLabels as $type => $label): $doc = $docByType[$type] ?? null; ?>
              <tr>
                <td><?= e($label) ?></td>
                <td><?php if ($doc): ?><span class="badge <?= e($docStatusBadge[$doc['status']] ?? 'info') ?>"><?= e($docStatusLabels[$doc['status']] ?? $doc['status']) ?></span><?php else: ?><span class="badge failed">Not Uploaded</span><?php endif; ?></td>
                <td class="text-muted" style="font-size:0.8rem;"><?= $doc ? e(date('d M Y', strtotime((string) $doc['uploaded_at']))) : '—' ?></td>
                <td>
                  <?php if ($doc): ?>
                  <form method="post" class="flex gap-2" style="align-items:center;flex-wrap:wrap;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="doc_status">
                    <input type="hidden" name="doc_type" value="<?= e($type) ?>">
                    <select name="doc_status" style="font-size:0.8rem;">
                      <?php foreach ($docStatusLabels as $slug => $l): ?>
                        <option value="<?= e($slug) ?>" <?= $doc['status'] === $slug ? 'selected' : '' ?>><?= e($l) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <input type="text" name="status_note" placeholder="Note (optional)" style="font-size:0.8rem;min-width:140px;">
                    <button type="submit" class="btn btn-outline btn-sm">Save</button>
                  </form>
                  <?php else: ?>—<?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($bank): ?>
        <h3 style="font-size:0.9rem;margin-top:20px;margin-bottom:10px;">Bank Details</h3>
        <p class="text-muted" style="font-size:0.85rem;"><?= e($bank['bank_name']) ?> &middot; <?= e($bank['account_holder']) ?> &middot; Account ending <?= e($bank['account_number_last4']) ?><?= $bank['ifsc'] ? ' · ' . e($bank['ifsc']) : '' ?> &middot; <span class="badge <?= $bank['status'] === 'verified' ? 'success' : 'pending' ?>"><?= e(ucfirst(str_replace('_', ' ', $bank['status']))) ?></span></p>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Requested Products</h2></div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead><tr><th>Product</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($activations)): ?>
              <tr><td colspan="2"><div class="empty-state">No products requested.</div></td></tr>
            <?php else: foreach ($activations as $a): ?>
              <tr><td><?= e($productNameBySlug[$a['product_slug']] ?? $a['product_slug']) ?></td><td><span class="badge <?= $a['status'] === 'active' ? 'success' : 'pending' ?>"><?= e(ucfirst(str_replace('_', ' ', $a['status']))) ?></span></td></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($customer['status'] !== 'suspended'): ?>
    <div class="panel">
      <div class="panel-head"><h2>Suspend Account</h2></div>
      <form method="post" class="flex gap-2" style="align-items:flex-end;flex-wrap:wrap;">
        <?= csrf_field() ?>
        <div class="field" style="flex:1;min-width:220px;"><label>Reason</label><input type="text" name="status_note" placeholder="Reason for suspension"></div>
        <button type="submit" name="form_action" value="suspend" class="btn btn-outline btn-sm js-confirm" data-confirm="Suspend this customer's account?">Suspend Account</button>
      </form>
    </div>
    <?php endif; ?>
    <?php
    return;
}

// =====================================================================
// List view: /admin/customer-kyc
// =====================================================================
$page_meta = ['title' => 'Customer eKYC | Paynancial Admin', 'heading' => 'Customer eKYC'];

$statusFilter = sanitize_input((string) ($_GET['status'] ?? ''));
$sql = "SELECT c.id, c.customer_code, c.status, c.kyc_status, ckp.legal_business_name, ckp.created_at, u.full_name, u.email
        FROM customers c
        JOIN customer_kyc_profiles ckp ON ckp.customer_id = c.id
        JOIN users u ON u.id = c.user_id WHERE 1=1";
$params = [];
if (array_key_exists($statusFilter, $statusLabels)) {
    $sql .= ' AND c.status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY ckp.created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$totals = $pdo->query("SELECT COUNT(*) AS total, SUM(status = 'pending_verification') AS pending, SUM(status = 'active') AS active FROM customers c JOIN customer_kyc_profiles ckp ON ckp.customer_id = c.id")->fetch();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Submitted Profiles</span><strong class="value"><?= (int) $totals['total'] ?></strong></div>
  <div class="stat-card"><span class="label">Verification Pending</span><strong class="value"><?= (int) $totals['pending'] ?></strong></div>
  <div class="stat-card"><span class="label">Active</span><strong class="value"><?= (int) $totals['active'] ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Self-Service Customer eKYC</h2></div>
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
      <thead><tr><th>Customer ID</th><th>Business Name</th><th>Contact</th><th>Status</th><th>KYC</th><th>Submitted</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7"><div class="empty-state">No self-service customer sign-ups yet.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr>
            <td class="mono"><?= e($row['customer_code']) ?></td>
            <td><?= e($row['legal_business_name']) ?></td>
            <td><?= e($row['full_name']) ?><br><span class="text-muted" style="font-size:0.78rem;"><?= e($row['email']) ?></span></td>
            <td><span class="badge <?= e($statusBadge[$row['status']] ?? 'info') ?>"><?= e($statusLabels[$row['status']] ?? $row['status']) ?></span></td>
            <td class="text-muted" style="font-size:0.82rem;text-transform:capitalize;"><?= e(str_replace('_', ' ', $row['kyc_status'])) ?></td>
            <td><?= e(date('d M Y', strtotime((string) $row['created_at']))) ?></td>
            <td><a href="/admin/customer-kyc/<?= (int) $row['id'] ?>" class="btn btn-outline btn-sm">Review</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
