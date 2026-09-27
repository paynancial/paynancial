<?php
/**
 * Admin: Activity — a readable timeline of audit_logs. Everyone sees their
 * own actions; activity.view shows the whole team. The raw log (with IP,
 * user agent and full JSON) stays in Audit Logs (audit.view).
 */
require_once __DIR__ . '/../includes/admin/widgets.php';
require_once __DIR__ . '/../includes/admin/ui.php';

$page_meta = ['title' => 'Activity | Paynancial Admin', 'own_head' => true];
$pdo = db();
$all = user_can($auth_user, 'activity.view');
$st = adm_table_state(['time' => 'al.id'], 'time', 'desc', 25);
$area = (string) ($_GET['area'] ?? '');
$areas = ['auth' => 'Sign-in', 'cms' => 'Content', 'enquiry' => 'Enquiries', 'customer' => 'Customers', 'role' => 'Roles', 'permission' => 'Permissions', 'access' => 'Access denied', 'settings' => 'Settings'];

$where = ["al.action <> 'admin.request'"];
$params = [];
if (!$all) {
    $where[] = 'al.user_id = :me';
    $params['me'] = (int) $auth_user['id'];
}
if (isset($areas[$area])) {
    $where[] = 'al.action LIKE :area';
    $params['area'] = $area . '%';
}
$rows = null;
$total = 0;
try {
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM audit_logs al WHERE ' . implode(' AND ', $where));
    $cnt->execute($params);
    $total = (int) $cnt->fetchColumn();
    $stmt = $pdo->prepare('SELECT al.action, al.entity_type, al.entity_id, al.meta_json, al.created_at, al.actor_role, u.full_name
        FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id WHERE ' . implode(' AND ', $where)
        . ' ORDER BY ' . $st['order_sql'] . ' LIMIT ' . $st['per'] . ' OFFSET ' . (($st['page'] - 1) * $st['per']));
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[Paynancial admin] activity unavailable: ' . $e->getMessage());
}
echo adm_page_head('Activity', $all ? 'Recent actions across the team.' : 'Your recent actions. (Team-wide activity needs the activity.view permission.)');
?>
<section class="adm-card">
  <form method="get" class="adm-table-bar">
    <label class="sr-only" for="act-area">Area</label>
    <select id="act-area" name="area"><option value="">All areas</option>
      <?php foreach ($areas as $k => $l): ?><option value="<?= e($k) ?>" <?= $area === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
    </select>
    <button class="adm-btn adm-btn--sm" type="submit"><?= adm_icon('filter', 14) ?>Filter</button>
    <?php if (user_can($auth_user, 'audit.view')): ?><a class="adm-btn adm-btn--sm adm-btn--ghost" href="/admin/audit-logs">Open raw audit log</a><?php endif; ?>
  </form>
  <?php if ($rows === null): ?>
    <?= adm_widget_state(['state' => 'error', 'key' => '']) ?>
  <?php elseif (!$rows): ?>
    <div class="adm-state"><?= adm_icon('pulse', 20) ?><div><strong>No activity recorded yet</strong></div></div>
  <?php else: ?>
    <ol class="adm-tl" style="gap:16px">
      <?php foreach ($rows as $r): $m = json_decode((string) $r['meta_json'], true) ?: []; ?>
        <li><div>
          <strong><?= e(admin_action_label($r['action'], $m)) ?></strong>
          <span><?= e($r['full_name'] ?? 'System') ?><?= $r['actor_role'] ? ' · ' . e(admin_role_label($r['actor_role'])) : '' ?> · <?= adm_ago($r['created_at']) ?>
            <?php if ($r['entity_type']): ?> · <?= e($r['entity_type']) ?><?= $r['entity_id'] ? ' #' . (int) $r['entity_id'] : '' ?><?php endif; ?></span>
          <?php if (!empty($m['new']) || !empty($m['reason'])): ?>
            <span style="margin-top:4px;color:var(--a-text-2)">
              <?php if (!empty($m['reason'])): ?>Reason: <?= e((string) $m['reason']) ?><?php endif; ?>
              <?php foreach (array_slice((array) ($m['new'] ?? []), 0, 4, true) as $k => $v): ?>
                <?= e((string) $k) ?>: <?= e(is_scalar($m['old'][$k] ?? null) ? (string) $m['old'][$k] : '—') ?> → <?= e(is_scalar($v) ? (string) $v : json_encode($v)) ?>;
              <?php endforeach; ?>
            </span>
          <?php endif; ?>
        </div></li>
      <?php endforeach; ?>
    </ol>
    <?= adm_pagination($total, $st) ?>
  <?php endif; ?>
</section>
