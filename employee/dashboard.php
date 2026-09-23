<?php
/** Employee dashboard overview. */
$page_meta = ['title' => 'Employee Dashboard | Paynancial', 'heading' => 'Dashboard'];

$pdo = db();

$myTicketsStmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE assigned_to = :uid AND status != 'closed'");
$myTicketsStmt->execute(['uid' => $auth_user['id']]);
$myTasks = (int) $myTicketsStmt->fetchColumn();

$pendingEnquiriesStmt = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'");
$pendingEnquiries = (int) $pendingEnquiriesStmt->fetchColumn();

$assignedCustomersStmt = $pdo->prepare("SELECT COUNT(DISTINCT customer_id) FROM transactions WHERE customer_id IS NOT NULL");
$assignedCustomersStmt->execute();
$assignedCustomers = (int) $assignedCustomersStmt->fetchColumn();

$todayActivityStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE user_id = :uid AND DATE(created_at) = CURDATE()");
$todayActivityStmt->execute(['uid' => $auth_user['id']]);
$todayActivities = (int) $todayActivityStmt->fetchColumn();

$notifStmt = $pdo->prepare('SELECT title, body, is_read, created_at FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 6');
$notifStmt->execute(['uid' => $auth_user['id']]);
$notifications = $notifStmt->fetchAll();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">My Tasks</span><strong class="value"><?= $myTasks ?></strong></div>
  <div class="stat-card"><span class="label">Pending Enquiries</span><strong class="value"><?= $pendingEnquiries ?></strong></div>
  <div class="stat-card"><span class="label">Assigned Customers</span><strong class="value"><?= $assignedCustomers ?></strong></div>
  <div class="stat-card"><span class="label">Today's Activities</span><strong class="value"><?= $todayActivities ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Notifications</h2></div>
  <?php if (empty($notifications)): ?>
    <div class="empty-state">No notifications yet.</div>
  <?php else: ?>
    <div class="data-table-wrap">
      <table class="data-table">
        <thead><tr><th>Title</th><th>Details</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($notifications as $n): ?>
            <tr>
              <td><?= e($n['title']) ?> <?= !$n['is_read'] ? '<span class="badge info">New</span>' : '' ?></td>
              <td><?= e($n['body']) ?></td>
              <td><?= e(date('d M Y', strtotime((string) $n['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="panel">
  <div class="panel-head"><h2>My Tasks &amp; Pending Enquiries</h2><a href="/employee/tasks" class="btn btn-outline btn-sm">Open Tasks</a></div>
  <p class="text-muted">Support tickets assigned to you and open enquiries are tracked on the Tasks page.</p>
</div>
