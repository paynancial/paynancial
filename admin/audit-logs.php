<?php
/** Admin — Audit Log viewer. Read-only. Every sensitive action across the platform writes here via log_partner_activity(). */
$pdo = db();
$page_meta = ['title' => 'Audit Logs | Paynancial Admin', 'heading' => 'Audit Logs'];

$actionFilter = sanitize_input((string) ($_GET['action'] ?? ''));
$userFilter = sanitize_input((string) ($_GET['user'] ?? ''));
$fromDate = sanitize_input((string) ($_GET['from'] ?? ''));
$toDate = sanitize_input((string) ($_GET['to'] ?? ''));

$sql = 'SELECT al.id, al.action, al.entity_type, al.entity_id, al.ip_address, al.meta_json, al.created_at, u.full_name, u.email
        FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id WHERE 1=1';
$params = [];
if ($actionFilter !== '') { $sql .= ' AND al.action LIKE :action'; $params['action'] = '%' . $actionFilter . '%'; }
if ($userFilter !== '') { $sql .= ' AND (u.full_name LIKE :u1 OR u.email LIKE :u2)'; $params['u1'] = $params['u2'] = '%' . $userFilter . '%'; }
if ($fromDate !== '') { $sql .= ' AND al.created_at >= :from'; $params['from'] = $fromDate . ' 00:00:00'; }
if ($toDate !== '') { $sql .= ' AND al.created_at <= :to'; $params['to'] = $toDate . ' 23:59:59'; }
$sql .= ' ORDER BY al.created_at DESC LIMIT 300';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$totalCount = (int) $pdo->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Logged Events</span><strong class="value"><?= $totalCount ?></strong></div>
  <div class="stat-card"><span class="label">Shown (most recent)</span><strong class="value"><?= count($rows) ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Audit Trail</h2></div>
  <form method="get" class="toolbar" style="margin-bottom:18px;flex-wrap:wrap;">
    <input type="text" name="action" placeholder="Filter by action (e.g. change_request)" value="<?= e($actionFilter) ?>" style="min-width:220px;">
    <input type="text" name="user" placeholder="Filter by user name or email" value="<?= e($userFilter) ?>" style="min-width:200px;">
    <input type="date" name="from" value="<?= e($fromDate) ?>">
    <input type="date" name="to" value="<?= e($toDate) ?>">
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    <?php if ($actionFilter || $userFilter || $fromDate || $toDate): ?><a href="/admin/audit-logs" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Entity</th><th>IP</th><th>Details</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6"><div class="empty-state">No audit log entries match this filter.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <?php $meta = $row['meta_json'] ? json_decode((string) $row['meta_json'], true) : null; ?>
          <tr>
            <td class="text-muted" style="font-size:0.8rem;white-space:nowrap;"><?= e(date('d M Y, H:i:s', strtotime((string) $row['created_at']))) ?></td>
            <td><?= $row['full_name'] ? e($row['full_name']) . '<br><span class="text-muted" style="font-size:0.75rem;">' . e($row['email']) . '</span>' : '<span class="text-muted">System</span>' ?></td>
            <td class="mono" style="font-size:0.8rem;"><?= e($row['action']) ?></td>
            <td class="text-muted" style="font-size:0.8rem;"><?= e($row['entity_type'] ?: '—') ?><?= $row['entity_id'] ? ' #' . (int) $row['entity_id'] : '' ?></td>
            <td class="mono text-muted" style="font-size:0.75rem;"><?= e($row['ip_address'] ?: '—') ?></td>
            <td class="text-muted" style="font-size:0.75rem;max-width:260px;overflow-wrap:anywhere;"><?= $meta ? e(json_encode($meta, JSON_UNESCAPED_SLASHES)) : '—' ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if (count($rows) === 300): ?>
    <p class="text-muted" style="font-size:0.8rem;margin-top:12px;">Showing the most recent 300 matching entries. Narrow your filters to see older activity.</p>
  <?php endif; ?>
</div>
