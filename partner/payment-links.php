<?php
/** Partner Hub — Payment Links: create and manage shareable payment link references for enrolled customers. */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Payment Links | Paynancial Partner Hub', 'heading' => 'Payment Links'];

$pdo = db();
$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (($_POST['form_action'] ?? '') === 'create') {
        $customerApplicationId = (int) ($_POST['customer_application_id'] ?? 0);
        $title = sanitize_input((string) ($_POST['title'] ?? ''));
        $amountInput = trim((string) ($_POST['amount'] ?? ''));
        $expiresInput = sanitize_input((string) ($_POST['expires_at'] ?? ''));

        // Isolation check: the selected customer application must belong to this partner.
        $checkStmt = $pdo->prepare('SELECT id FROM customer_applications WHERE id = :id AND partner_id = :pid');
        $checkStmt->execute(['id' => $customerApplicationId, 'pid' => $partnerId]);
        $validCustomer = (bool) $checkStmt->fetchColumn();

        if ($title === '') {
            $errors[] = 'Title is required.';
        } elseif ($customerApplicationId > 0 && !$validCustomer) {
            $errors[] = 'Invalid customer selection.';
        } elseif ($amountInput !== '' && !is_numeric($amountInput)) {
            $errors[] = 'Amount must be a number.';
        } else {
            $linkRef = generate_payment_link_ref($pdo);
            $expiresAt = $expiresInput !== '' ? $expiresInput . ' 23:59:59' : null;
            $ins = $pdo->prepare(
                'INSERT INTO payment_links (link_ref, created_by, partner_id, customer_application_id, title, amount, currency, status, expires_at)
                 VALUES (:ref, :uid, :pid, :caid, :title, :amount, "INR", "active", :exp)'
            );
            $ins->execute([
                'ref' => $linkRef, 'uid' => $auth_user['id'], 'pid' => $partnerId,
                'caid' => $customerApplicationId > 0 ? $customerApplicationId : null,
                'title' => $title, 'amount' => $amountInput !== '' ? $amountInput : null, 'exp' => $expiresAt,
            ]);
            log_partner_activity($pdo, $context, 'payment_link.created', 'payment_link', (int) $pdo->lastInsertId());
            $notice = "Payment link {$linkRef} created.";
        }
    } elseif (($_POST['form_action'] ?? '') === 'disable') {
        $linkId = (int) ($_POST['link_id'] ?? 0);
        $upd = $pdo->prepare("UPDATE payment_links SET status = 'disabled' WHERE id = :id AND partner_id = :pid AND status = 'active'");
        $upd->execute(['id' => $linkId, 'pid' => $partnerId]);
        log_partner_activity($pdo, $context, 'payment_link.disabled', 'payment_link', $linkId);
        $notice = 'Payment link disabled.';
    }
}

$customersStmt = $pdo->prepare(
    "SELECT id, application_code, business_name FROM customer_applications
     WHERE partner_id = :pid AND pipeline_stage NOT IN ('lost','rejected') ORDER BY business_name"
);
$customersStmt->execute(['pid' => $partnerId]);
$customerOptions = $customersStmt->fetchAll();

$linksStmt = $pdo->prepare(
    "SELECT pl.*, ca.business_name FROM payment_links pl
     LEFT JOIN customer_applications ca ON ca.id = pl.customer_application_id
     WHERE pl.partner_id = :pid ORDER BY pl.created_at DESC LIMIT 100"
);
$linksStmt->execute(['pid' => $partnerId]);
$links = $linksStmt->fetchAll();
?>
<?php foreach ($errors as $err): ?>
  <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
<?php endforeach; ?>
<?php if ($notice): ?>
  <div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Create Payment Link</h2></div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="create">
    <div class="field-grid">
      <div class="field">
        <label>Customer (optional)</label>
        <select name="customer_application_id">
          <option value="0">General / not linked to a customer</option>
          <?php foreach ($customerOptions as $c): ?>
            <option value="<?= (int) $c['id'] ?>"><?= e($c['business_name']) ?> (<?= e($c['application_code']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Title</label><input type="text" name="title" placeholder="e.g. Setup fee, Invoice #204" required></div>
      <div class="field"><label>Amount (leave blank to let customer enter amount)</label><input type="text" name="amount" placeholder="e.g. 5000.00"></div>
      <div class="field"><label>Expires On (optional)</label><input type="date" name="expires_at"></div>
    </div>
    <button type="submit" class="btn btn-primary">Create Payment Link</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>Payment Links</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Reference</th><th>Title</th><th>Customer</th><th>Amount</th><th>Status</th><th>Created</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($links)): ?>
          <tr><td colspan="7"><div class="empty-state">No payment links created yet.</div></td></tr>
        <?php else: foreach ($links as $link): ?>
          <tr>
            <td class="mono"><?= e($link['link_ref']) ?></td>
            <td><?= e($link['title']) ?></td>
            <td><?= e($link['business_name'] ?? '—') ?></td>
            <td><?= $link['amount'] !== null ? e(format_amount((float) $link['amount'], $link['currency'])) : 'Customer enters amount' ?></td>
            <td><span class="badge <?= $link['status'] === 'paid' ? 'success' : ($link['status'] === 'active' ? 'info' : 'neutral') ?>"><?= e(ucfirst($link['status'])) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $link['created_at']))) ?></td>
            <td class="flex gap-2">
              <button type="button" class="btn btn-outline btn-sm js-copy-link" data-link="<?= e(site_url('/pay/' . $link['link_ref'])) ?>">Copy Link</button>
              <?php if ($link['status'] === 'active'): ?>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="form_action" value="disable">
                  <input type="hidden" name="link_id" value="<?= (int) $link['id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm js-confirm" data-confirm="Disable this payment link?">Disable</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script nonce="<?= csp_nonce() ?>">
document.querySelectorAll('.js-copy-link').forEach(function (btn) {
  btn.addEventListener('click', function () {
    navigator.clipboard.writeText(btn.getAttribute('data-link')).then(function () {
      var original = btn.textContent;
      btn.textContent = 'Copied!';
      setTimeout(function () { btn.textContent = original; }, 1600);
    });
  });
});
</script>
