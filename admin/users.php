<?php
/** Admin: platform user management. */
$page_meta = ['title' => 'Users | Paynancial Admin', 'heading' => 'User Management'];

$pdo = db();
$search = sanitize_input((string) ($_GET['q'] ?? ''));
$roleFilter = sanitize_input((string) ($_GET['role'] ?? ''));

$sql = "SELECT u.uuid, u.full_name, u.email, u.mobile, u.status, u.last_login_at, r.slug AS role_slug, r.name AS role_name
        FROM users u JOIN roles r ON r.id = u.role_id WHERE 1=1";
$params = [];
if ($search !== '') { $sql .= ' AND (u.full_name LIKE :q1 OR u.email LIKE :q2)'; $params['q1'] = $params['q2'] = '%' . $search . '%'; }
if ($roleFilter !== '') { $sql .= ' AND r.slug = :role'; $params['role'] = $roleFilter; }
$sql .= ' ORDER BY u.created_at DESC LIMIT 100';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$roles = $pdo->query('SELECT slug, name FROM roles ORDER BY name')->fetchAll();
?>
<div class="panel">
  <div class="panel-head"><h2>Platform Users</h2></div>
  <form method="get" class="toolbar" style="margin-bottom:18px;">
    <input type="text" name="q" placeholder="Search name or email…" value="<?= e($search) ?>">
    <select name="role">
      <option value="">All roles</option>
      <?php foreach ($roles as $r): ?>
        <option value="<?= e($r['slug']) ?>" <?= $roleFilter === $r['slug'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Role</th><th>Status</th><th>Last Login</th></tr></thead>
      <tbody>
        <?php if (empty($users)): ?><tr><td colspan="6"><div class="empty-state">No users found.</div></td></tr>
        <?php else: foreach ($users as $u): ?>
          <tr>
            <td><?= e($u['full_name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['mobile'] ?? '—') ?></td>
            <td><span class="badge info"><?= e($u['role_name']) ?></span></td>
            <td><span class="badge <?= $u['status'] === 'active' ? 'success' : 'neutral' ?>"><?= e(ucfirst($u['status'])) ?></span></td>
            <td><?= $u['last_login_at'] ? e(date('d M Y, H:i', strtotime((string) $u['last_login_at']))) : '—' ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
