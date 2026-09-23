<?php
/** Partner Hub — Team Management: invite sub-users and manage their role/permissions. */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Team | Paynancial Partner Hub', 'heading' => 'Team'];

$pdo = db();

if (!partner_can('team', 'view')) {
    ?>
    <div class="panel"><div class="empty-state">You don't have permission to view team management. Contact your partner owner or admin.</div></div>
    <?php
    return;
}

$canEdit = partner_can('team', 'edit');
$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canEdit) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $action = (string) ($_POST['form_action'] ?? '');

        if ($action === 'invite') {
            $name = sanitize_input((string) ($_POST['full_name'] ?? ''));
            $email = sanitize_input((string) ($_POST['email'] ?? ''));
            $roleId = (int) ($_POST['role_id'] ?? 0);

            $roleCheck = $pdo->prepare("SELECT id FROM partner_roles WHERE id = :id AND slug != 'owner'");
            $roleCheck->execute(['id' => $roleId]);
            $existingUser = $pdo->prepare('SELECT id FROM users WHERE email = :email');
            $existingUser->execute(['email' => $email]);

            if ($name === '' || !is_valid_email($email)) {
                $errors[] = 'Please provide a valid name and email.';
            } elseif (!$roleCheck->fetchColumn()) {
                $errors[] = 'Please select a valid team role.';
            } elseif ($existingUser->fetchColumn()) {
                $errors[] = 'A user with this email already exists.';
            } else {
                $pdo->beginTransaction();
                try {
                    $partnerRoleId = $pdo->query("SELECT id FROM roles WHERE slug = 'partner'")->fetchColumn();
                    $userIns = $pdo->prepare(
                        'INSERT INTO users (uuid, role_id, full_name, email, password_hash, status, email_verified_at)
                         VALUES (UUID(), :role_id, :name, :email, :hash, "active", NULL)'
                    );
                    $userIns->execute(['role_id' => $partnerRoleId, 'name' => $name, 'email' => $email, 'hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)]);
                    $newUserId = (int) $pdo->lastInsertId();

                    $memberIns = $pdo->prepare('INSERT INTO partner_users (partner_id, user_id, role_id, status) VALUES (:pid, :uid, :rid, "invited")');
                    $memberIns->execute(['pid' => $partnerId, 'uid' => $newUserId, 'rid' => $roleId]);

                    $token = bin2hex(random_bytes(32));
                    $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)')
                        ->execute([
                            'uid' => $newUserId, 'hash' => hash('sha256', $token),
                            'exp' => (new DateTime())->modify('+' . PASSWORD_RESET_TOKEN_TTL_MINUTES . ' minutes')->format('Y-m-d H:i:s'),
                        ]);

                    log_partner_activity($pdo, $context, 'team.member_invited', 'user', $newUserId);
                    $pdo->commit();

                    $setLink = site_url('/reset-password?token=' . $token);
                    $subject = 'You have been invited to a Paynancial Partner team';
                    $message = "Hello {$name},\n\nYou've been added to a Paynancial Partner Hub team. Set your password to get started:\n{$setLink}\n\nThis link expires in " . PASSWORD_RESET_TOKEN_TTL_MINUTES . ' minutes.';
                    @mail($email, $subject, $message, 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>');

                    $notice = "{$name} has been invited. A password-set link has been emailed to {$email}.";
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    error_log('[Paynancial] Team invite failed: ' . $e->getMessage());
                    $errors[] = 'Something went wrong while sending this invite. Please try again.';
                }
            }
        } elseif ($action === 'update_role') {
            $memberId = (int) ($_POST['member_id'] ?? 0);
            $roleId = (int) ($_POST['role_id'] ?? 0);
            $roleCheck = $pdo->prepare("SELECT id FROM partner_roles WHERE id = :id AND slug != 'owner'");
            $roleCheck->execute(['id' => $roleId]);
            if ($roleCheck->fetchColumn()) {
                $pdo->prepare('UPDATE partner_users SET role_id = :rid WHERE id = :id AND partner_id = :pid')
                    ->execute(['rid' => $roleId, 'id' => $memberId, 'pid' => $partnerId]);
                log_partner_activity($pdo, $context, 'team.role_updated', 'partner_user', $memberId);
                $notice = 'Role updated.';
            }
        } elseif ($action === 'toggle_status') {
            $memberId = (int) ($_POST['member_id'] ?? 0);
            $newStatus = (string) ($_POST['new_status'] ?? '');
            if (in_array($newStatus, ['active', 'disabled'], true)) {
                $pdo->prepare('UPDATE partner_users SET status = :status WHERE id = :id AND partner_id = :pid')
                    ->execute(['status' => $newStatus, 'id' => $memberId, 'pid' => $partnerId]);
                log_partner_activity($pdo, $context, 'team.status_updated', 'partner_user', $memberId, ['status' => $newStatus]);
                $notice = 'Member status updated.';
            }
        }
    }
}

$ownerStmt = $pdo->prepare('SELECT u.full_name, u.email FROM partners p JOIN users u ON u.id = p.user_id WHERE p.id = :pid');
$ownerStmt->execute(['pid' => $partnerId]);
$owner = $ownerStmt->fetch();

$membersStmt = $pdo->prepare(
    'SELECT pu.id, pu.status, u.full_name, u.email, r.id AS role_id, r.name AS role_name
     FROM partner_users pu JOIN users u ON u.id = pu.user_id JOIN partner_roles r ON r.id = pu.role_id
     WHERE pu.partner_id = :pid ORDER BY u.full_name'
);
$membersStmt->execute(['pid' => $partnerId]);
$members = $membersStmt->fetchAll();

$rolesStmt = $pdo->query("SELECT id, slug, name FROM partner_roles WHERE slug != 'owner' ORDER BY id");
$roles = $rolesStmt->fetchAll();

$matrixStmt = $pdo->query(
    'SELECT r.name AS role_name, prp.module, prp.can_view, prp.can_edit FROM partner_role_permissions prp
     JOIN partner_roles r ON r.id = prp.role_id ORDER BY r.id, prp.module'
);
$matrix = [];
foreach ($matrixStmt->fetchAll() as $row) {
    $matrix[$row['role_name']][$row['module']] = $row;
}
$modules = ['customers', 'applications', 'transactions', 'settlements', 'commission', 'reports', 'support', 'team', 'documents'];
?>
<?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
<?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Owner</h2></div>
  <p><?= e($owner['full_name'] ?? '') ?> &middot; <?= e($owner['email'] ?? '') ?> <span class="badge success" style="margin-left:8px;">Owner</span></p>
</div>

<?php if ($canEdit): ?>
<div class="panel">
  <div class="panel-head"><h2>Invite Team Member</h2></div>
  <form method="post" class="flex gap-2" style="align-items:flex-end;flex-wrap:wrap;">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="invite">
    <div class="field" style="min-width:180px;"><label>Full Name</label><input type="text" name="full_name" required></div>
    <div class="field" style="min-width:220px;"><label>Email</label><input type="email" name="email" required></div>
    <div class="field" style="min-width:180px;">
      <label>Role</label>
      <select name="role_id" required>
        <?php foreach ($roles as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Send Invite</button>
  </form>
</div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Team Members</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><?php if ($canEdit): ?><th></th><?php endif; ?></tr></thead>
      <tbody>
        <?php if (empty($members)): ?>
          <tr><td colspan="<?= $canEdit ? 5 : 4 ?>"><div class="empty-state">No team members yet.</div></td></tr>
        <?php else: foreach ($members as $m): $fid = 'tm-' . (int) $m['id']; ?>
          <tr>
            <td><?= e($m['full_name']) ?></td>
            <td><?= e($m['email']) ?></td>
            <td>
              <?php if ($canEdit): ?>
                <select form="<?= $fid ?>" name="role_id" class="js-auto-submit">
                  <?php foreach ($roles as $r): ?><option value="<?= (int) $r['id'] ?>" <?= $m['role_id'] === $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
                </select>
              <?php else: ?>
                <?= e($m['role_name']) ?>
              <?php endif; ?>
            </td>
            <td><span class="badge <?= $m['status'] === 'active' ? 'success' : ($m['status'] === 'disabled' ? 'failed' : 'pending') ?>"><?= e(ucfirst($m['status'])) ?></span></td>
            <?php if ($canEdit): ?>
              <td>
                <form id="<?= $fid ?>" method="post" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="form_action" value="update_role">
                  <input type="hidden" name="member_id" value="<?= (int) $m['id'] ?>">
                </form>
                <form method="post" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="form_action" value="toggle_status">
                  <input type="hidden" name="member_id" value="<?= (int) $m['id'] ?>">
                  <input type="hidden" name="new_status" value="<?= $m['status'] === 'disabled' ? 'active' : 'disabled' ?>">
                  <button type="submit" class="btn btn-outline btn-sm"><?= $m['status'] === 'disabled' ? 'Reactivate' : 'Disable' ?></button>
                </form>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Role Permission Matrix</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Role</th><?php foreach ($modules as $mod): ?><th><?= e(ucfirst($mod)) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php foreach ($matrix as $roleName => $perms): ?>
          <tr>
            <td><?= e($roleName) ?></td>
            <?php foreach ($modules as $mod): $p = $perms[$mod] ?? null; ?>
              <td style="text-align:center;">
                <?php if (!$p): ?><span class="text-muted">—</span>
                <?php elseif ($p['can_edit']): ?><span class="badge success">Edit</span>
                <?php elseif ($p['can_view']): ?><span class="badge info">View</span>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
