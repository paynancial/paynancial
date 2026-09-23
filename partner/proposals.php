<?php
/**
 * Partner Hub — Proposal Builder (/partner/proposals, /partner/proposals/{id}).
 * A proposal bundles selected Paynancial solutions with partner-entered
 * pricing notes into a branded, printable document tracked through a
 * sales status. No prices are invented here — pricing_note defaults to
 * "Talk to Sales" and is only ever what the partner types in.
 */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$pdo = db();

$statusLabels = ['draft' => 'Draft', 'sent' => 'Sent', 'viewed' => 'Viewed', 'negotiation' => 'Negotiation', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'expired' => 'Expired'];
$statusBadge = ['accepted' => 'success', 'rejected' => 'failed', 'expired' => 'failed', 'sent' => 'info', 'viewed' => 'info', 'negotiation' => 'pending', 'draft' => 'neutral'];

$proposalId = $route_param !== null ? (int) $route_param : null;

// =====================================================================
// Detail / branded document view: /partner/proposals/{id}
// =====================================================================
if ($proposalId !== null) {
    $stmt = $pdo->prepare(
        'SELECT p.*, ca.business_name, ca.contact_person, ca.email AS customer_email, ca.application_code
         FROM proposals p JOIN customer_applications ca ON ca.id = p.customer_application_id
         WHERE p.id = :id AND p.partner_id = :pid'
    );
    $stmt->execute(['id' => $proposalId, 'pid' => $partnerId]);
    $proposal = $stmt->fetch();

    if (!$proposal) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify($_POST['csrf_token'] ?? null) && ($_POST['form_action'] ?? '') === 'update_status') {
        $newStatus = sanitize_input((string) ($_POST['status'] ?? ''));
        if (array_key_exists($newStatus, $statusLabels)) {
            $sentAtSql = ($newStatus === 'sent' && $proposal['sent_at'] === null) ? ', sent_at = NOW()' : '';
            $upd = $pdo->prepare("UPDATE proposals SET status = :status{$sentAtSql} WHERE id = :id AND partner_id = :pid");
            $upd->execute(['status' => $newStatus, 'id' => $proposalId, 'pid' => $partnerId]);
            log_partner_activity($pdo, $context, 'proposal.status_updated', 'proposal', $proposalId, ['status' => $newStatus]);
            $proposal['status'] = $newStatus;
        }
    }

    $itemsStmt = $pdo->prepare(
        'SELECT pi.pricing_note, pr.name, pr.short_description, pr.category FROM proposal_items pi
         JOIN products pr ON pr.id = pi.product_id WHERE pi.proposal_id = :id ORDER BY pi.sort_order'
    );
    $itemsStmt->execute(['id' => $proposalId]);
    $items = $itemsStmt->fetchAll();

    $partnerStmt = $pdo->prepare('SELECT business_name, partner_code FROM partners WHERE id = :pid');
    $partnerStmt->execute(['pid' => $partnerId]);
    $partnerInfo = $partnerStmt->fetch();

    $page_meta = ['title' => $proposal['title'] . ' | Paynancial Partner Hub', 'heading' => 'Proposal'];
    ?>
    <div class="panel no-print" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
      <div>
        <a href="/partner/proposals" class="text-muted" style="font-size:0.82rem;">&larr; All Proposals</a>
        <div class="flex gap-3" style="margin-top:8px;align-items:center;">
          <span class="mono text-muted" style="font-size:0.82rem;"><?= e($proposal['proposal_code']) ?></span>
          <span class="badge <?= e($statusBadge[$proposal['status']] ?? 'info') ?>"><?= e($statusLabels[$proposal['status']] ?? $proposal['status']) ?></span>
        </div>
      </div>
      <div class="flex gap-2">
        <form method="post" class="flex gap-2">
          <?= csrf_field() ?>
          <input type="hidden" name="form_action" value="update_status">
          <select name="status" class="js-auto-submit">
            <?php foreach ($statusLabels as $slug => $label): ?>
              <option value="<?= e($slug) ?>" <?= $proposal['status'] === $slug ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
        <button type="button" class="btn btn-primary js-print">Print / Save as PDF</button>
      </div>
    </div>

    <div class="panel proposal-doc">
      <div class="flex" style="justify-content:space-between;align-items:flex-start;border-bottom:2px solid var(--ink);padding-bottom:20px;margin-bottom:24px;">
        <div>
          <img src="<?= asset('images/paynancial-logo.png') ?>" alt="Paynancial" style="height:32px;">
          <p class="text-muted" style="margin-top:10px;font-size:0.82rem;">Proposed by <?= e($partnerInfo['business_name'] ?? '') ?> · <?= e($partnerInfo['partner_code'] ?? '') ?></p>
        </div>
        <div style="text-align:right;">
          <span class="mono text-muted" style="font-size:0.8rem;"><?= e($proposal['proposal_code']) ?></span><br>
          <span class="text-muted" style="font-size:0.8rem;">Date: <?= e(date('d M Y', strtotime((string) $proposal['created_at']))) ?></span>
          <?php if ($proposal['valid_until']): ?><br><span class="text-muted" style="font-size:0.8rem;">Valid Until: <?= e(date('d M Y', strtotime((string) $proposal['valid_until']))) ?></span><?php endif; ?>
        </div>
      </div>

      <h2 style="font-size:1.4rem;"><?= e($proposal['title']) ?></h2>
      <div class="ledger" style="margin-top:16px;">
        <div class="ledger-row"><span class="ledger-tag">Prepared For</span><h3 style="font-size:1rem;"><?= e($proposal['business_name']) ?></h3><span></span></div>
        <div class="ledger-row"><span class="ledger-tag">Contact</span><h3 style="font-size:1rem;"><?= e($proposal['contact_person']) ?> &middot; <?= e($proposal['customer_email']) ?></h3><span></span></div>
      </div>

      <h3 style="font-size:1rem;margin-top:32px;margin-bottom:14px;">Proposed Solutions</h3>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead><tr><th>Solution</th><th>Description</th><th>Pricing</th></tr></thead>
          <tbody>
            <?php if (empty($items)): ?>
              <tr><td colspan="3"><div class="empty-state">No solutions added to this proposal.</div></td></tr>
            <?php else: foreach ($items as $item): ?>
              <tr>
                <td><?= e($item['name']) ?></td>
                <td class="text-muted"><?= e($item['short_description']) ?></td>
                <td class="mono"><?= e($item['pricing_note']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <?php if (!empty($proposal['implementation_notes'])): ?>
        <h3 style="font-size:1rem;margin-top:32px;margin-bottom:10px;">Implementation Notes</h3>
        <p style="white-space:pre-line;"><?= e($proposal['implementation_notes']) ?></p>
      <?php endif; ?>

      <p class="text-muted" style="margin-top:40px;font-size:0.78rem;border-top:1px solid var(--border);padding-top:16px;">
        This proposal is issued by <?= e($partnerInfo['business_name'] ?? '') ?>, an authorized Paynancial partner, and is subject to final commercial terms confirmed at onboarding.
      </p>
    </div>
    <style>@media print { .no-print, .app-sidebar, .app-topbar { display: none !important; } .app-content { padding: 0 !important; } .proposal-doc { border: none !important; box-shadow: none !important; } }</style>
    <?php
    return;
}

// =====================================================================
// List + create view: /partner/proposals
// =====================================================================
$page_meta = ['title' => 'Proposals | Paynancial Partner Hub', 'heading' => 'Proposals'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $customerApplicationId = (int) ($_POST['customer_application_id'] ?? 0);
        $title = sanitize_input((string) ($_POST['title'] ?? ''));
        $validUntil = sanitize_input((string) ($_POST['valid_until'] ?? ''));
        $notes = trim((string) ($_POST['implementation_notes'] ?? ''));
        $productIds = array_map('intval', (array) ($_POST['product_ids'] ?? []));
        $pricingNotes = (array) ($_POST['pricing_note'] ?? []);

        $checkStmt = $pdo->prepare('SELECT id FROM customer_applications WHERE id = :id AND partner_id = :pid');
        $checkStmt->execute(['id' => $customerApplicationId, 'pid' => $partnerId]);

        if ($title === '') {
            $errors[] = 'Proposal title is required.';
        } elseif (!$checkStmt->fetchColumn()) {
            $errors[] = 'Please select a valid customer.';
        } elseif (empty($productIds)) {
            $errors[] = 'Select at least one solution to include.';
        } else {
            $pdo->beginTransaction();
            try {
                $proposalCode = generate_proposal_code($pdo);
                $ins = $pdo->prepare(
                    'INSERT INTO proposals (proposal_code, partner_id, customer_application_id, title, implementation_notes, valid_until)
                     VALUES (:code, :pid, :caid, :title, :notes, :valid)'
                );
                $ins->execute([
                    'code' => $proposalCode, 'pid' => $partnerId, 'caid' => $customerApplicationId,
                    'title' => $title, 'notes' => sanitize_input($notes) ?: null, 'valid' => $validUntil ?: null,
                ]);
                $newProposalId = (int) $pdo->lastInsertId();

                $itemStmt = $pdo->prepare('INSERT INTO proposal_items (proposal_id, product_id, pricing_note, sort_order) VALUES (:pid, :prodid, :note, :sort)');
                foreach ($productIds as $i => $productId) {
                    $note = sanitize_input((string) ($pricingNotes[$productId] ?? 'Talk to Sales'));
                    $itemStmt->execute(['pid' => $newProposalId, 'prodid' => $productId, 'note' => $note !== '' ? $note : 'Talk to Sales', 'sort' => $i]);
                }

                log_partner_activity($pdo, $context, 'proposal.created', 'proposal', $newProposalId);
                $pdo->commit();
                header('Location: /partner/proposals/' . $newProposalId);
                exit;
            } catch (Throwable $e) {
                $pdo->rollBack();
                error_log('[Paynancial] Proposal creation failed: ' . $e->getMessage());
                $errors[] = 'Something went wrong while creating this proposal. Please try again.';
            }
        }
    }
}

$customersStmt = $pdo->prepare(
    "SELECT id, application_code, business_name FROM customer_applications
     WHERE partner_id = :pid AND pipeline_stage NOT IN ('lost','rejected') ORDER BY business_name"
);
$customersStmt->execute(['pid' => $partnerId]);
$customerOptions = $customersStmt->fetchAll();

$productsStmt = $pdo->query('SELECT id, name, category FROM products WHERE is_active = 1 ORDER BY category, sort_order');
$productOptions = $productsStmt->fetchAll();

$listStmt = $pdo->prepare(
    'SELECT p.id, p.proposal_code, p.title, p.status, p.created_at, ca.business_name
     FROM proposals p JOIN customer_applications ca ON ca.id = p.customer_application_id
     WHERE p.partner_id = :pid ORDER BY p.updated_at DESC LIMIT 100'
);
$listStmt->execute(['pid' => $partnerId]);
$proposalRows = $listStmt->fetchAll();
?>
<?php foreach ($errors as $err): ?>
  <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="panel">
  <div class="panel-head"><h2>New Proposal</h2></div>
  <form method="post">
    <?= csrf_field() ?>
    <div class="field-grid">
      <div class="field">
        <label>Customer</label>
        <select name="customer_application_id" required>
          <option value="">Select a customer</option>
          <?php foreach ($customerOptions as $c): ?>
            <option value="<?= (int) $c['id'] ?>"><?= e($c['business_name']) ?> (<?= e($c['application_code']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Proposal Title</label><input type="text" name="title" placeholder="e.g. Payment Infrastructure Proposal" required></div>
      <div class="field"><label>Valid Until (optional)</label><input type="date" name="valid_until"></div>
    </div>

    <label style="display:block;margin:18px 0 10px;font-weight:600;font-size:0.9rem;">Include Solutions</label>
    <div class="data-table-wrap">
      <table class="data-table">
        <thead><tr><th style="width:40px;"></th><th>Solution</th><th>Pricing Note</th></tr></thead>
        <tbody>
          <?php foreach ($productOptions as $p): ?>
            <tr>
              <td><input type="checkbox" name="product_ids[]" value="<?= (int) $p['id'] ?>"></td>
              <td><?= e($p['name']) ?></td>
              <td><input type="text" name="pricing_note[<?= (int) $p['id'] ?>]" value="Talk to Sales" style="width:100%;"></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="field" style="margin-top:16px;"><label>Implementation Notes (optional)</label><textarea name="implementation_notes" rows="3"></textarea></div>
    <button type="submit" class="btn btn-primary" style="margin-top:10px;">Create Proposal</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All Proposals</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Code</th><th>Title</th><th>Customer</th><th>Status</th><th>Created</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($proposalRows)): ?>
          <tr><td colspan="6"><div class="empty-state">No proposals created yet.</div></td></tr>
        <?php else: foreach ($proposalRows as $row): ?>
          <tr>
            <td class="mono"><?= e($row['proposal_code']) ?></td>
            <td><?= e($row['title']) ?></td>
            <td><?= e($row['business_name']) ?></td>
            <td><span class="badge <?= e($statusBadge[$row['status']] ?? 'info') ?>"><?= e($statusLabels[$row['status']] ?? $row['status']) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $row['created_at']))) ?></td>
            <td><a href="/partner/proposals/<?= (int) $row['id'] ?>" class="btn btn-outline btn-sm">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
