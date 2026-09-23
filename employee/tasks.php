<?php
/** Employee: assigned support tickets ("tasks") + open enquiries. */
$page_meta = ['title' => 'My Tasks | Paynancial', 'heading' => 'My Tasks'];

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    if (csrf_verify($_POST['csrf_token'] ?? null)) {
        $newStatus = sanitize_input((string) ($_POST['status'] ?? ''));
        if (in_array($newStatus, ['open', 'in_progress', 'resolved', 'closed'], true)) {
            $upd = $pdo->prepare('UPDATE support_tickets SET status = :status WHERE id = :id AND assigned_to = :uid');
            $upd->execute(['status' => $newStatus, 'id' => (int) $_POST['ticket_id'], 'uid' => $auth_user['id']]);
        }
    }
}

$tickets = $pdo->prepare(
    'SELECT id, ticket_code, subject, priority, status, created_at FROM support_tickets
     WHERE assigned_to = :uid ORDER BY FIELD(status,"open","in_progress","resolved","closed"), created_at DESC'
);
$tickets->execute(['uid' => $auth_user['id']]);
$myTickets = $tickets->fetchAll();

$enquiries = $pdo->query(
    "SELECT enquiry_code, type, name, subject, status, created_at FROM enquiries WHERE status = 'new' ORDER BY created_at DESC LIMIT 15"
)->fetchAll();
?>
<div class="panel">
  <div class="panel-head"><h2>My Assigned Tickets</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Ticket</th><th>Subject</th><th>Priority</th><th>Status</th><th>Update</th></tr></thead>
      <tbody>
        <?php if (empty($myTickets)): ?>
          <tr><td colspan="5"><div class="empty-state">No tickets assigned to you.</div></td></tr>
        <?php else: foreach ($myTickets as $t): ?>
          <tr>
            <td><?= e($t['ticket_code']) ?></td>
            <td><?= e($t['subject']) ?></td>
            <td><span class="badge neutral"><?= e(ucfirst($t['priority'])) ?></span></td>
            <td><span class="badge <?= $t['status'] === 'resolved' || $t['status'] === 'closed' ? 'success' : 'pending' ?>"><?= e(ucfirst(str_replace('_', ' ', $t['status']))) ?></span></td>
            <td>
              <form method="post" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="ticket_id" value="<?= (int) $t['id'] ?>">
                <select name="status" class="js-auto-submit">
                  <?php foreach (['open', 'in_progress', 'resolved', 'closed'] as $s): ?>
                    <option value="<?= e($s) ?>" <?= $t['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Open Enquiries</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Enquiry ID</th><th>Type</th><th>Name</th><th>Subject</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (empty($enquiries)): ?>
          <tr><td colspan="5"><div class="empty-state">No open enquiries.</div></td></tr>
        <?php else: foreach ($enquiries as $en): ?>
          <tr>
            <td><?= e($en['enquiry_code']) ?></td>
            <td><span class="badge info"><?= e(ucfirst($en['type'])) ?></span></td>
            <td><?= e($en['name']) ?></td>
            <td><?= e($en['subject'] ?? '—') ?></td>
            <td><?= e(date('d M Y', strtotime((string) $en['created_at']))) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
