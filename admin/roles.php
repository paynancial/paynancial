<?php
/**
 * Admin: Roles & Permissions. View needs roles.view; every change needs
 * roles.manage (super admin by default). All changes are audited with old
 * and new values. Rules:
 *   - the Super Admin role always holds every permission (not editable);
 *   - nobody changes their own role;
 *   - only a super admin can grant or remove the Super Admin role;
 *   - the last active super admin cannot be demoted;
 *   - a role change signs the person out everywhere (session_version bump).
 */
require_once __DIR__ . '/../includes/admin/ui.php';

$page_meta = ['title' => 'Roles & Permissions | Paynancial Admin', 'own_head' => true];
$pdo = db();
$canManage = user_can($auth_user, 'roles.manage');
$isSuper = ($auth_user['role'] ?? '') === 'super_admin';
$staff = ADMIN_STAFF_ROLES;
$in = "'" . implode("','", $staff) . "'";

$back = static function (string $msg, bool $ok = true): never {
    flash($ok ? 'adm_ok' : 'adm_err', $msg);
    header('Location: /admin/roles' . ($_POST['anchor'] ?? ''), true, 303);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = (string) ($_POST['op'] ?? '');
    try {
        require_permission($auth_user, 'roles.manage');
        if ($op === 'toggle') {
            [$roleId, $permId, $grant] = array_map('intval', explode(':', (string) ($_POST['cell'] ?? '0:0:0')) + [0, 0, 0]);
            $role = $pdo->prepare("SELECT id, slug FROM roles WHERE id = :id AND slug IN ($in)");
            $role->execute(['id' => $roleId]);
            $role = $role->fetch();
            $perm = $pdo->prepare('SELECT id, slug FROM permissions WHERE id = :id');
            $perm->execute(['id' => $permId]);
            $perm = $perm->fetch();
            if (!$role || !$perm) {
                throw new RuntimeException('Unknown role or permission.');
            }
            if ($role['slug'] === 'super_admin') {
                throw new RuntimeException('The Super Admin role always holds every permission.');
            }
            if ($perm['slug'] === 'roles.manage' && !$isSuper) {
                throw new RuntimeException('Only a super admin can grant role management.');
            }
            $has = $pdo->prepare('SELECT 1 FROM role_permissions WHERE role_id = :r AND permission_id = :p');
            $has->execute(['r' => $roleId, 'p' => $permId]);
            $before = (bool) $has->fetchColumn();
            if ($before !== (bool) $grant) {
                $pdo->beginTransaction();
                if ($grant) {
                    $pdo->prepare('INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:r, :p)')->execute(['r' => $roleId, 'p' => $permId]);
                } else {
                    $pdo->prepare('DELETE FROM role_permissions WHERE role_id = :r AND permission_id = :p')->execute(['r' => $roleId, 'p' => $permId]);
                }
                audit_write($pdo, $grant ? 'permission.granted' : 'permission.revoked', 'role', (int) $roleId,
                    ['granted' => $before], ['granted' => (bool) $grant], ['role' => $role['slug'], 'permission' => $perm['slug'], 'title' => $role['slug'] . ' · ' . $perm['slug']]);
                $pdo->commit();
            }
            $back(($grant ? 'Granted ' : 'Removed ') . $perm['slug'] . ($grant ? ' to ' : ' from ') . admin_role_label($role['slug']) . '.');
        }
        if ($op === 'assign') {
            $uid = (int) ($_POST['user_id'] ?? 0);
            $newSlug = (string) ($_POST['role'] ?? '');
            if ($uid === (int) $auth_user['id']) {
                throw new RuntimeException('You cannot change your own role.');
            }
            if (!in_array($newSlug, $staff, true)) {
                throw new RuntimeException('Choose a staff role.');
            }
            $t = $pdo->prepare('SELECT u.id, u.full_name, u.status, r.slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id');
            $t->execute(['id' => $uid]);
            $target = $t->fetch();
            if (!$target || !in_array($target['slug'], array_merge($staff, ['employee', 'hr']), true)) {
                throw new RuntimeException('Only staff accounts can be assigned admin roles.');
            }
            if (($newSlug === 'super_admin' || $target['slug'] === 'super_admin') && !$isSuper) {
                throw new RuntimeException('Only a super admin can grant or remove the Super Admin role.');
            }
            if ($target['slug'] === 'super_admin' && $newSlug !== 'super_admin') {
                $left = (int) $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'super_admin' AND u.status = 'active'")->fetchColumn();
                if ($left <= 1) {
                    throw new RuntimeException('This is the last active super admin; the role cannot be removed.');
                }
            }
            if ($target['slug'] !== $newSlug) {
                $pdo->beginTransaction();
                $pdo->prepare('UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = :s), session_version = session_version + 1 WHERE id = :id')
                    ->execute(['s' => $newSlug, 'id' => $uid]);
                audit_write($pdo, 'role.changed', 'user', $uid, ['role' => $target['slug']], ['role' => $newSlug], ['name' => $target['full_name'], 'sessions_revoked' => true]);
                $pdo->commit();
            }
            $back($target['full_name'] . ' is now ' . admin_role_label($newSlug) . '. Their sessions were signed out.');
        }
        throw new RuntimeException('Unknown action.');
    } catch (CmsDenied | RuntimeException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $back($e->getMessage(), false);
    }
}

$roles = $pdo->query("SELECT id, slug, name FROM roles WHERE slug IN ($in)")->fetchAll();
usort($roles, fn ($a, $b) => array_search($a['slug'], $staff, true) <=> array_search($b['slug'], $staff, true));
$perms = $pdo->query('SELECT id, slug, name, module FROM permissions ORDER BY module, slug')->fetchAll();
$grants = [];
foreach ($pdo->query('SELECT role_id, permission_id FROM role_permissions') as $g) {
    $grants[$g['role_id'] . ':' . $g['permission_id']] = true;
}
$users = $pdo->query("SELECT u.id, u.full_name, u.email, u.status, u.last_login_at, r.slug FROM users u JOIN roles r ON r.id = u.role_id
    WHERE r.slug IN ($in) ORDER BY FIELD(r.slug, $in), u.full_name")->fetchAll();
$overrides = $pdo->query('SELECT u.full_name, p.slug, up.effect FROM user_permissions up JOIN users u ON u.id = up.user_id JOIN permissions p ON p.id = up.permission_id ORDER BY u.full_name, p.slug')->fetchAll();
$ok = flash('adm_ok');
$err = flash('adm_err');

echo adm_page_head('Roles & Permissions', '12 staff roles. Permissions are enforced on the server for every page and action — hiding a button is never the control.');
?>
<?php if ($ok): ?><div class="adm-alert adm-alert--ok" role="status"><?= adm_icon('check', 16) ?><?= e($ok) ?></div><?php endif; ?>
<?php if ($err): ?><div class="adm-alert adm-alert--err" role="alert"><?= adm_icon('alert', 16) ?><?= e($err) ?></div><?php endif; ?>

<section class="adm-card" id="people" aria-labelledby="ppl-h">
  <div class="adm-card-h"><div><h2 id="ppl-h">People</h2><p>Staff accounts and their role. Changing a role signs that person out everywhere.</p></div></div>
  <?php if (!$users): ?><div class="adm-state"><?= adm_icon('users', 20) ?><div><strong>No staff accounts yet</strong></div></div><?php else: ?>
  <div class="adm-table-wrap"><table class="adm-table">
    <thead><tr><th scope="col">Name</th><th scope="col">Status</th><th scope="col">Last sign-in</th><th scope="col">Role</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): $self = (int) $u['id'] === (int) $auth_user['id']; ?>
      <tr>
        <td><strong><?= e($u['full_name']) ?></strong><span class="adm-row-sub"><?= e($u['email']) ?></span></td>
        <td><?= adm_pill(ucfirst($u['status']), $u['status'] === 'active' ? 'green' : 'grey') ?></td>
        <td><?= adm_ago($u['last_login_at']) ?></td>
        <td>
          <?php if ($canManage && !$self && ($isSuper || $u['slug'] !== 'super_admin')): ?>
            <form method="post" class="toolbar" data-confirm-submit="Change this person's role? They will be signed out everywhere.">
              <?= csrf_field() ?><input type="hidden" name="op" value="assign"><input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>"><input type="hidden" name="anchor" value="#people">
              <label class="sr-only" for="role-<?= (int) $u['id'] ?>">Role for <?= e($u['full_name']) ?></label>
              <select id="role-<?= (int) $u['id'] ?>" name="role">
                <?php foreach ($staff as $slug): if ($slug === 'super_admin' && !$isSuper) { continue; } ?>
                  <option value="<?= e($slug) ?>" <?= $u['slug'] === $slug ? 'selected' : '' ?>><?= e(admin_role_label($slug)) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="adm-btn adm-btn--sm">Save</button>
            </form>
          <?php else: ?>
            <?= adm_pill(admin_role_label($u['slug']), 'blue', false) ?><?= $self ? ' <span class="adm-muted">(you)</span>' : '' ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</section>

<section class="adm-card" id="matrix" aria-labelledby="mx-h">
  <div class="adm-card-h"><div><h2 id="mx-h">Permission matrix</h2><p><?= $canManage ? 'Select a cell to grant or remove that permission for the whole role.' : 'Read-only: changing permissions needs roles.manage.' ?></p></div></div>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="op" value="toggle"><input type="hidden" name="anchor" value="#matrix">
    <div class="adm-table-wrap"><table class="adm-table adm-matrix">
      <caption class="sr-only">Permissions by role</caption>
      <thead><tr><th scope="col">Permission</th><?php foreach ($roles as $r): ?><th scope="col" title="<?= e(admin_role_label($r['slug'])) ?>" style="white-space:normal;min-width:84px;text-transform:none;letter-spacing:0"><?= e(admin_role_label($r['slug'])) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
      <?php $lastModule = null; foreach ($perms as $p): if ($p['module'] !== $lastModule): $lastModule = $p['module']; ?>
        <tr class="adm-matrix-grp"><td colspan="<?= count($roles) + 1 ?>"><?= e(ucfirst($p['module'])) ?></td></tr>
      <?php endif; ?>
        <tr>
          <th scope="row" style="text-transform:none;letter-spacing:0;font-size:13px;color:var(--a-text);background:#fff;font-weight:500"><code><?= e($p['slug']) ?></code><span class="adm-row-sub"><?= e($p['name']) ?></span></th>
          <?php foreach ($roles as $r): $on = $r['slug'] === 'super_admin' || isset($grants[$r['id'] . ':' . $p['id']]); $label = ($on ? 'Granted' : 'Not granted') . ': ' . $p['slug'] . ' for ' . admin_role_label($r['slug']); ?>
            <td>
              <?php if ($canManage && $r['slug'] !== 'super_admin'): ?>
                <button type="submit" name="cell" value="<?= (int) $r['id'] ?>:<?= (int) $p['id'] ?>:<?= $on ? 0 : 1 ?>" class="adm-btn adm-btn--sm adm-btn--ghost <?= $on ? 'adm-yes' : 'adm-no' ?>" aria-label="<?= e($label . '. Select to ' . ($on ? 'remove' : 'grant')) ?>" title="<?= e($label) ?>"><?= $on ? '✓' : '—' ?></button>
              <?php else: ?>
                <span class="<?= $on ? 'adm-yes' : 'adm-no' ?>" aria-label="<?= e($label) ?>"><?= $on ? '✓' : '—' ?></span>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </form>
</section>

<section class="adm-card" aria-labelledby="ov-h">
  <div class="adm-card-h"><div><h2 id="ov-h">Per-person overrides</h2><p>Grants or revokes for one person on top of their role (CMS permissions are managed in CMS Overview → Access).</p></div></div>
  <?php if (!$overrides): ?><p class="adm-muted" style="margin:0">No per-person overrides.</p><?php else: ?>
  <div class="adm-table-wrap"><table class="adm-table"><thead><tr><th scope="col">Person</th><th scope="col">Permission</th><th scope="col">Effect</th></tr></thead><tbody>
    <?php foreach ($overrides as $o): ?><tr><td><?= e($o['full_name']) ?></td><td><code><?= e($o['slug']) ?></code></td><td><?= $o['effect'] === 'grant' ? adm_pill('Grant', 'green') : adm_pill('Revoke', 'coral') ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</section>
