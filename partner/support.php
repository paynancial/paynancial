<?php
/** Partner Hub — Support: raise tickets and reply in a threaded conversation. */
$context = require_partner_context();
$pdo = db();

$priorityLabels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];
$statusBadge = ['open' => 'info', 'in_progress' => 'pending', 'resolved' => 'success', 'closed' => 'neutral'];

$ticketId = $route_param !== null ? (int) $route_param : null;

// =====================================================================
// Ticket thread view: /partner/support/{id}
// =====================================================================
if ($ticketId !== null) {
    $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE id = :id AND user_id = :uid');
    $stmt->execute(['id' => $ticketId, 'uid' => $auth_user['id']]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = trim((string) ($_POST['message'] ?? ''));
        if ($message !== '') {
            $ins = $pdo->prepare('INSERT INTO support_messages (ticket_id, sender_user_id, message) VALUES (:tid, :uid, :msg)');
            $ins->execute(['tid' => $ticketId, 'uid' => $auth_user['id'], 'msg' => sanitize_input($message)]);
            $pdo->prepare("UPDATE support_tickets SET status = 'open' WHERE id = :id AND status = 'resolved'")->execute(['id' => $ticketId]);
            log_partner_activity($pdo, $context, 'support.reply_added', 'support_ticket', $ticketId);
            header('Location: /partner/support/' . $ticketId);
            exit;
        }
    }

    $msgStmt = $pdo->prepare(
        'SELECT m.*, u.full_name AS sender_name FROM support_messages m
         JOIN users u ON u.id = m.sender_user_id WHERE m.ticket_id = :tid ORDER BY m.created_at ASC'
    );
    $msgStmt->execute(['tid' => $ticketId]);
    $messages = $msgStmt->fetchAll();

    $page_meta = ['title' => $ticket['ticket_code'] . ' | Paynancial Partner Hub', 'heading' => 'Support Ticket'];
    ?>
    <div class="panel" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
      <div>
        <a href="/partner/support" class="text-muted" style="font-size:0.82rem;">&larr; All Tickets</a>
        <h2 style="font-size:1.2rem;margin-top:8px;"><?= e($ticket['subject']) ?></h2>
        <div class="flex gap-3" style="margin-top:8px;align-items:center;">
          <span class="mono text-muted" style="font-size:0.82rem;"><?= e($ticket['ticket_code']) ?></span>
          <span class="badge <?= e($statusBadge[$ticket['status']] ?? 'info') ?>"><?= e(ucfirst(str_replace('_', ' ', $ticket['status']))) ?></span>
          <span class="badge neutral"><?= e($priorityLabels[$ticket['priority']] ?? $ticket['priority']) ?> Priority</span>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="ledger-row" style="display:block;padding:14px 0;">
        <strong style="font-size:0.85rem;">Original Request</strong>
        <p style="margin-top:8px;white-space:pre-line;"><?= e($ticket['description']) ?></p>
      </div>
      <?php foreach ($messages as $msg): ?>
        <div class="ledger-row" style="display:block;padding:14px 0;">
          <div class="flex" style="justify-content:space-between;">
            <strong style="font-size:0.85rem;"><?= e($msg['sender_name']) ?></strong>
            <span class="text-muted" style="font-size:0.78rem;"><?= e(date('d M Y, g:i a', strtotime((string) $msg['created_at']))) ?></span>
          </div>
          <p style="margin-top:6px;white-space:pre-line;"><?= nl2br(e($msg['message'])) ?></p>
        </div>
      <?php endforeach; ?>

      <?php if ($ticket['status'] !== 'closed'): ?>
        <form method="post" style="margin-top:20px;">
          <?= csrf_field() ?>
          <div class="field"><label>Reply</label><textarea name="message" rows="3" required></textarea></div>
          <button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
        </form>
      <?php else: ?>
        <p class="text-muted" style="margin-top:16px;">This ticket is closed.</p>
      <?php endif; ?>
    </div>
    <?php
    return;
}

// =====================================================================
// List + create view: /partner/support
// =====================================================================
$page_meta = ['title' => 'Support | Paynancial Partner Hub', 'heading' => 'Support'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $subject = sanitize_input((string) ($_POST['subject'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $priority = sanitize_input((string) ($_POST['priority'] ?? 'medium'));
        if (!array_key_exists($priority, $priorityLabels)) { $priority = 'medium'; }

        if ($subject === '' || $description === '') {
            $errors[] = 'Please fill in both subject and description.';
        } else {
            $ticketCode = generate_ticket_code($pdo);
            $ins = $pdo->prepare(
                'INSERT INTO support_tickets (ticket_code, user_id, subject, description, priority, status) VALUES (:code, :uid, :subject, :desc, :priority, "open")'
            );
            $ins->execute(['code' => $ticketCode, 'uid' => $auth_user['id'], 'subject' => $subject, 'desc' => sanitize_input($description), 'priority' => $priority]);
            $newTicketId = (int) $pdo->lastInsertId();
            log_partner_activity($pdo, $context, 'support.ticket_created', 'support_ticket', $newTicketId);
            header('Location: /partner/support/' . $newTicketId);
            exit;
        }
    }
}

$ticketsStmt = $pdo->prepare('SELECT id, ticket_code, subject, priority, status, created_at FROM support_tickets WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50');
$ticketsStmt->execute(['uid' => $auth_user['id']]);
$tickets = $ticketsStmt->fetchAll();
?>
<?php foreach ($errors as $err): ?>
  <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="panel">
  <div class="panel-head"><h2>Raise a Support Ticket</h2></div>
  <form method="post">
    <?= csrf_field() ?>
    <div class="field-grid">
      <div class="field"><label>Subject</label><input type="text" name="subject" required></div>
      <div class="field">
        <label>Priority</label>
        <select name="priority">
          <?php foreach ($priorityLabels as $slug => $label): ?>
            <option value="<?= e($slug) ?>" <?= $slug === 'medium' ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="field"><label>Description</label><textarea name="description" rows="4" required></textarea></div>
    <button type="submit" class="btn btn-primary">Submit Ticket</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>Your Tickets</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Ticket ID</th><th>Subject</th><th>Priority</th><th>Status</th><th>Created</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($tickets)): ?>
          <tr><td colspan="6"><div class="empty-state">No support tickets yet.</div></td></tr>
        <?php else: foreach ($tickets as $t): ?>
          <tr>
            <td class="mono"><?= e($t['ticket_code']) ?></td>
            <td><?= e($t['subject']) ?></td>
            <td><?= e($priorityLabels[$t['priority']] ?? $t['priority']) ?></td>
            <td><span class="badge <?= e($statusBadge[$t['status']] ?? 'info') ?>"><?= e(ucfirst(str_replace('_', ' ', $t['status']))) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $t['created_at']))) ?></td>
            <td><a href="/partner/support/<?= (int) $t['id'] ?>" class="btn btn-outline btn-sm">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
