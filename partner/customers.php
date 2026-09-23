<?php
/**
 * Partner Hub — Customer applications list (/partner/customers) and
 * customer profile (/partner/customers/{id}).
 *
 * CRITICAL: every query below filters by partner_id = $partnerId, the
 * value resolved from the authenticated session via
 * current_partner_context() — never from request input. This is what
 * stops Partner A from ever loading Partner B's customer record by
 * guessing an id in the URL.
 */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$pdo = db();

$stageLabels = [
    'new_lead' => 'New Lead', 'contacted' => 'Contacted', 'qualified' => 'Qualified',
    'proposal_sent' => 'Proposal Sent', 'documents_pending' => 'Documents Pending', 'kyc_submitted' => 'KYC Submitted',
    'under_review' => 'Under Review', 'approved' => 'Approved', 'integration' => 'Integration Pending',
    'active' => 'Active', 'lost' => 'Lost', 'rejected' => 'Rejected',
];
$stageBadgeClass = [
    'active' => 'success', 'approved' => 'success', 'lost' => 'failed', 'rejected' => 'failed',
    'under_review' => 'pending', 'documents_pending' => 'pending', 'kyc_submitted' => 'pending',
];
$docLabels = [
    'id_proof' => 'Passport / ID', 'business_registration' => 'Business Registration', 'tax_documents' => 'Tax Documents',
    'bank_documents' => 'Bank Documents', 'address_proof' => 'Address Proof', 'signatory_id' => 'Authorized Signatory', 'other' => 'Other Document',
];

$applicationId = $route_param !== null ? (int) $route_param : null;

// =====================================================================
// Detail view: /partner/customers/{id}
// =====================================================================
if ($applicationId !== null) {
    $stmt = $pdo->prepare('SELECT * FROM customer_applications WHERE id = :id AND partner_id = :pid');
    $stmt->execute(['id' => $applicationId, 'pid' => $partnerId]);
    $app = $stmt->fetch();

    if (!$app) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
        exit;
    }

    $formErrors = [];
    $formNotice = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $formErrors[] = 'Your session expired. Please refresh the page and try again.';
        } elseif (($_POST['form_action'] ?? '') === 'add_note') {
            $note = trim((string) ($_POST['note'] ?? ''));
            if ($note === '') {
                $formErrors[] = 'Note cannot be empty.';
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO customer_application_notes (customer_application_id, author_user_id, note) VALUES (:aid, :uid, :note)'
                );
                $ins->execute(['aid' => $applicationId, 'uid' => $auth_user['id'], 'note' => sanitize_input($note)]);
                log_partner_activity($pdo, $context, 'customer.note_added', 'customer_application', $applicationId);
                $formNotice = 'Note added.';
            }
        } elseif (($_POST['form_action'] ?? '') === 'update_stage') {
            $newStage = sanitize_input((string) ($_POST['pipeline_stage'] ?? ''));
            if (array_key_exists($newStage, $stageLabels)) {
                $upd = $pdo->prepare('UPDATE customer_applications SET pipeline_stage = :stage WHERE id = :id AND partner_id = :pid');
                $upd->execute(['stage' => $newStage, 'id' => $applicationId, 'pid' => $partnerId]);
                $app['pipeline_stage'] = $newStage;
                log_partner_activity($pdo, $context, 'customer.stage_updated', 'customer_application', $applicationId, ['stage' => $newStage]);
                $formNotice = 'Pipeline stage updated.';
            } else {
                $formErrors[] = 'Invalid pipeline stage.';
            }
        }
    }

    $docStmt = $pdo->prepare('SELECT * FROM customer_application_documents WHERE customer_application_id = :aid ORDER BY uploaded_at DESC');
    $docStmt->execute(['aid' => $applicationId]);
    $documents = $docStmt->fetchAll();

    $prodStmt = $pdo->prepare(
        'SELECT cap.status AS link_status, p.* FROM customer_application_products cap
         JOIN products p ON p.id = cap.product_id WHERE cap.customer_application_id = :aid ORDER BY p.sort_order'
    );
    $prodStmt->execute(['aid' => $applicationId]);
    $products = $prodStmt->fetchAll();

    $noteStmt = $pdo->prepare(
        'SELECT n.*, u.full_name AS author_name FROM customer_application_notes n
         JOIN users u ON u.id = n.author_user_id WHERE n.customer_application_id = :aid ORDER BY n.created_at DESC'
    );
    $noteStmt->execute(['aid' => $applicationId]);
    $notes = $noteStmt->fetchAll();

    // Financial tabs only populate once this lead becomes an activated portal customer.
    $txnRows = [];
    $settlementRows = [];
    if ($app['assigned_customer_id']) {
        $txnStmt = $pdo->prepare(
            'SELECT transaction_ref, amount, currency, status, created_at FROM transactions
             WHERE customer_id = :cid AND partner_id = :pid ORDER BY created_at DESC LIMIT 20'
        );
        $txnStmt->execute(['cid' => $app['assigned_customer_id'], 'pid' => $partnerId]);
        $txnRows = $txnStmt->fetchAll();

        $setStmt = $pdo->prepare(
            'SELECT settlement_ref, period_start, period_end, net_amount, status FROM settlements
             WHERE customer_id = :cid AND partner_id = :pid ORDER BY period_start DESC LIMIT 20'
        );
        $setStmt->execute(['cid' => $app['assigned_customer_id'], 'pid' => $partnerId]);
        $settlementRows = $setStmt->fetchAll();
    }

    $linkStmt = $pdo->prepare('SELECT link_ref, title, amount, currency, status, created_at FROM payment_links WHERE customer_application_id = :aid ORDER BY created_at DESC');
    $linkStmt->execute(['aid' => $applicationId]);
    $paymentLinks = $linkStmt->fetchAll();

    $requirements = json_decode((string) $app['requirements_json'], true) ?: [];

    $page_meta = ['title' => e($app['business_name']) . ' | Paynancial Partner Hub', 'heading' => 'Customer Profile'];
    ?>
    <div class="panel" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
      <div>
        <a href="/partner/customers" class="text-muted" style="font-size:0.82rem;">&larr; All Customers</a>
        <h2 style="font-size:1.3rem;margin-top:8px;"><?= e($app['business_name']) ?></h2>
        <div class="flex gap-3" style="margin-top:8px;align-items:center;">
          <span class="mono text-muted" style="font-size:0.82rem;"><?= e($app['application_code']) ?></span>
          <span class="badge <?= e($stageBadgeClass[$app['pipeline_stage']] ?? 'info') ?>"><?= e($stageLabels[$app['pipeline_stage']] ?? $app['pipeline_stage']) ?></span>
        </div>
      </div>
      <form method="post" class="flex gap-2">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="update_stage">
        <select name="pipeline_stage" class="js-auto-submit">
          <?php foreach ($stageLabels as $slug => $label): ?>
            <option value="<?= e($slug) ?>" <?= $app['pipeline_stage'] === $slug ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <?php foreach ($formErrors as $err): ?>
      <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
    <?php endforeach; ?>
    <?php if ($formNotice): ?>
      <div class="badge success" style="margin-bottom:16px;"><?= e($formNotice) ?></div>
    <?php endif; ?>

    <div class="panel">
      <nav class="profile-tabs" id="profile-tabs">
        <button type="button" class="profile-tab is-active" data-tab="overview">Overview</button>
        <button type="button" class="profile-tab" data-tab="documents">Documents (<?= count($documents) ?>)</button>
        <button type="button" class="profile-tab" data-tab="products">Solutions (<?= count($products) ?>)</button>
        <button type="button" class="profile-tab" data-tab="finance">Transactions &amp; Settlements</button>
        <button type="button" class="profile-tab" data-tab="links">Payment Links (<?= count($paymentLinks) ?>)</button>
        <button type="button" class="profile-tab" data-tab="notes">Notes &amp; Activity (<?= count($notes) ?>)</button>
      </nav>

      <div class="tab-panel is-active" data-tab-panel="overview">
        <div class="stat-grid">
          <div class="stat-card"><span class="label">Customer Type</span><strong class="value" style="font-size:1.05rem;"><?= e(ucfirst(str_replace('_', ' ', $app['customer_type']))) ?></strong></div>
          <div class="stat-card"><span class="label">Monthly GMV</span><strong class="value" style="font-size:1.05rem;"><?= e($app['monthly_gmv'] ?: '—') ?></strong></div>
          <div class="stat-card"><span class="label">Avg. Transaction Value</span><strong class="value" style="font-size:1.05rem;"><?= e($app['avg_transaction_value'] ?: '—') ?></strong></div>
          <div class="stat-card"><span class="label">Settlement Frequency</span><strong class="value" style="font-size:1.05rem;"><?= e($app['settlement_frequency'] ? ucfirst($app['settlement_frequency']) : '—') ?></strong></div>
        </div>
        <div class="ledger">
          <div class="ledger-row"><span class="ledger-tag">Contact Person</span><h3 style="font-size:0.95rem;"><?= e($app['contact_person']) ?></h3><span></span></div>
          <div class="ledger-row"><span class="ledger-tag">Email</span><h3 style="font-size:0.95rem;"><?= e($app['email']) ?></h3><span></span></div>
          <div class="ledger-row"><span class="ledger-tag">Mobile</span><h3 style="font-size:0.95rem;"><?= e($app['mobile']) ?></h3><span></span></div>
          <div class="ledger-row"><span class="ledger-tag">Website</span><h3 style="font-size:0.95rem;"><?= e($app['website'] ?: '—') ?></h3><span></span></div>
          <div class="ledger-row"><span class="ledger-tag">Country</span><h3 style="font-size:0.95rem;"><?= e($app['country'] ?: '—') ?></h3><span></span></div>
          <div class="ledger-row"><span class="ledger-tag">Industry</span><h3 style="font-size:0.95rem;"><?= e($app['industry'] ?: '—') ?></h3><span></span></div>
          <div class="ledger-row"><span class="ledger-tag">Address</span><h3 style="font-size:0.95rem;"><?= e($app['address'] ?: '—') ?></h3><span></span></div>
          <div class="ledger-row"><span class="ledger-tag">International</span><h3 style="font-size:0.95rem;"><?= $app['is_international'] ? 'Yes' : 'No' ?></h3><span></span></div>
          <div class="ledger-row"><span class="ledger-tag">Requirements</span><h3 style="font-size:0.95rem;"><?= e(implode(', ', $requirements) ?: '—') ?></h3><span></span></div>
        </div>
      </div>

      <div class="tab-panel" data-tab-panel="documents">
        <div class="data-table-wrap">
          <table class="data-table">
            <thead><tr><th>Document</th><th>Status</th><th>Uploaded</th></tr></thead>
            <tbody>
              <?php if (empty($documents)): ?>
                <tr><td colspan="3"><div class="empty-state">No documents uploaded yet.</div></td></tr>
              <?php else: foreach ($documents as $doc): ?>
                <tr>
                  <td><?= e($docLabels[$doc['doc_type']] ?? $doc['doc_type']) ?></td>
                  <td><span class="badge info"><?= e(ucfirst($doc['status'])) ?></span></td>
                  <td><?= e(date('d M Y', strtotime((string) $doc['uploaded_at']))) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-panel" data-tab-panel="products">
        <?php if (empty($products)): ?>
          <div class="empty-state">No solutions selected for this customer yet. <a href="/partner/products">Browse the Solution Catalog</a>.</div>
        <?php else: ?>
          <div class="grid grid-2">
            <?php foreach ($products as $p): ?>
              <div class="card">
                <h3 style="font-size:1rem;"><?= e($p['name']) ?></h3>
                <p style="margin-top:6px;"><?= e($p['short_description']) ?></p>
                <span class="badge info" style="margin-top:10px;"><?= e(ucfirst($p['link_status'])) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="tab-panel" data-tab-panel="finance">
        <?php if (!$app['assigned_customer_id']): ?>
          <div class="empty-state">Transactions and settlements will appear here once this customer's Paynancial account is activated.</div>
        <?php else: ?>
          <h3 style="font-size:0.95rem;margin-bottom:10px;">Recent Transactions</h3>
          <div class="data-table-wrap" style="margin-bottom:24px;">
            <table class="data-table">
              <thead><tr><th>Reference</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>
                <?php if (empty($txnRows)): ?><tr><td colspan="4"><div class="empty-state">No transactions yet.</div></td></tr>
                <?php else: foreach ($txnRows as $t): ?>
                  <tr>
                    <td class="mono"><?= e($t['transaction_ref']) ?></td>
                    <td><?= e(format_amount((float) $t['amount'], $t['currency'])) ?></td>
                    <td><span class="badge <?= $t['status'] === 'success' ? 'success' : ($t['status'] === 'failed' ? 'failed' : 'pending') ?>"><?= e(ucfirst($t['status'])) ?></span></td>
                    <td><?= e(date('d M Y', strtotime((string) $t['created_at']))) ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
          <h3 style="font-size:0.95rem;margin-bottom:10px;">Settlements</h3>
          <div class="data-table-wrap">
            <table class="data-table">
              <thead><tr><th>Reference</th><th>Period</th><th>Net Amount</th><th>Status</th></tr></thead>
              <tbody>
                <?php if (empty($settlementRows)): ?><tr><td colspan="4"><div class="empty-state">No settlements yet.</div></td></tr>
                <?php else: foreach ($settlementRows as $s): ?>
                  <tr>
                    <td class="mono"><?= e($s['settlement_ref']) ?></td>
                    <td><?= e(date('d M', strtotime((string) $s['period_start']))) ?> &ndash; <?= e(date('d M Y', strtotime((string) $s['period_end']))) ?></td>
                    <td><?= e(format_amount((float) $s['net_amount'])) ?></td>
                    <td><span class="badge <?= $s['status'] === 'settled' ? 'success' : 'pending' ?>"><?= e(ucfirst($s['status'])) ?></span></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <div class="tab-panel" data-tab-panel="links">
        <div class="data-table-wrap">
          <table class="data-table">
            <thead><tr><th>Reference</th><th>Title</th><th>Amount</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
              <?php if (empty($paymentLinks)): ?>
                <tr><td colspan="5"><div class="empty-state">No payment links created for this customer yet. <a href="/partner/payment-links">Create one</a>.</div></td></tr>
              <?php else: foreach ($paymentLinks as $link): ?>
                <tr>
                  <td class="mono"><?= e($link['link_ref']) ?></td>
                  <td><?= e($link['title']) ?></td>
                  <td><?= $link['amount'] !== null ? e(format_amount((float) $link['amount'], $link['currency'])) : 'Customer enters amount' ?></td>
                  <td><span class="badge <?= $link['status'] === 'paid' ? 'success' : ($link['status'] === 'active' ? 'info' : 'neutral') ?>"><?= e(ucfirst($link['status'])) ?></span></td>
                  <td><?= e(date('d M Y', strtotime((string) $link['created_at']))) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-panel" data-tab-panel="notes">
        <form method="post" style="margin-bottom:20px;">
          <?= csrf_field() ?>
          <input type="hidden" name="form_action" value="add_note">
          <div class="field"><label>Add a note</label><textarea name="note" rows="3" required></textarea></div>
          <button type="submit" class="btn btn-primary btn-sm">Add Note</button>
        </form>
        <?php if (empty($notes)): ?>
          <div class="empty-state">No notes yet.</div>
        <?php else: foreach ($notes as $note): ?>
          <div class="ledger-row" style="display:block;padding:14px 0;">
            <div class="flex" style="justify-content:space-between;">
              <strong style="font-size:0.85rem;"><?= e($note['author_name']) ?></strong>
              <span class="text-muted" style="font-size:0.78rem;"><?= e(date('d M Y, g:i a', strtotime((string) $note['created_at']))) ?></span>
            </div>
            <p style="margin-top:6px;"><?= nl2br(e($note['note'])) ?></p>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <script nonce="<?= csp_nonce() ?>">
    (function () {
      var tabs = document.querySelectorAll('#profile-tabs .profile-tab');
      var panels = document.querySelectorAll('[data-tab-panel]');
      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          tabs.forEach(function (t) { t.classList.remove('is-active'); });
          tab.classList.add('is-active');
          var target = tab.getAttribute('data-tab');
          panels.forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-tab-panel') === target); });
        });
      });
    })();
    </script>
    <?php
    return;
}

// =====================================================================
// List view: /partner/customers
// =====================================================================
$page_meta = ['title' => 'Customers | Paynancial Partner Hub', 'heading' => 'Customers'];

$search = sanitize_input((string) ($_GET['q'] ?? ''));
$stageFilter = sanitize_input((string) ($_GET['stage'] ?? ''));
$typeFilter = sanitize_input((string) ($_GET['type'] ?? ''));

$sql = 'SELECT id, application_code, business_name, customer_type, pipeline_stage, created_at FROM customer_applications WHERE partner_id = :pid';
$params = ['pid' => $partnerId];

if ($search !== '') {
    $sql .= ' AND (business_name LIKE :q1 OR application_code LIKE :q2 OR email LIKE :q3)';
    $params['q1'] = $params['q2'] = $params['q3'] = '%' . $search . '%';
}
if (array_key_exists($stageFilter, $stageLabels)) {
    $sql .= ' AND pipeline_stage = :stage';
    $params['stage'] = $stageFilter;
}
if ($typeFilter !== '') {
    $sql .= ' AND customer_type = :type';
    $params['type'] = $typeFilter;
}
$sql .= ' ORDER BY updated_at DESC LIMIT 100';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$typeStmt = $pdo->prepare('SELECT DISTINCT customer_type FROM customer_applications WHERE partner_id = :pid ORDER BY customer_type');
$typeStmt->execute(['pid' => $partnerId]);
$availableTypes = $typeStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="panel" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
  <h2 style="font-size:1.2rem;">Customers</h2>
  <a href="/partner/enroll-customer" class="btn btn-primary">+ Enroll New Customer</a>
</div>

<div class="panel">
  <form method="get" class="toolbar" style="margin-bottom:18px;">
    <input type="text" name="q" placeholder="Search business, email, application ID" value="<?= e($search) ?>" style="min-width:240px;">
    <select name="stage">
      <option value="">All stages</option>
      <?php foreach ($stageLabels as $slug => $label): ?>
        <option value="<?= e($slug) ?>" <?= $stageFilter === $slug ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="type">
      <option value="">All types</option>
      <?php foreach ($availableTypes as $t): ?>
        <option value="<?= e($t) ?>" <?= $typeFilter === $t ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $t))) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    <?php if ($search !== '' || $stageFilter !== '' || $typeFilter !== ''): ?>
      <a href="/partner/customers" class="btn btn-outline btn-sm">Clear</a>
    <?php endif; ?>
  </form>

  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Application ID</th><th>Business Name</th><th>Type</th><th>Stage</th><th>Created</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6"><div class="empty-state">No customers match this filter. <a href="/partner/enroll-customer">Enroll your first customer</a>.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr>
            <td class="mono"><?= e($row['application_code']) ?></td>
            <td><?= e($row['business_name']) ?></td>
            <td><?= e(ucfirst(str_replace('_', ' ', $row['customer_type']))) ?></td>
            <td><span class="badge <?= e($stageBadgeClass[$row['pipeline_stage']] ?? 'info') ?>"><?= e($stageLabels[$row['pipeline_stage']] ?? $row['pipeline_stage']) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $row['created_at']))) ?></td>
            <td><a href="/partner/customers/<?= (int) $row['id'] ?>" class="btn btn-outline btn-sm">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
