<?php
/** Customer: profile + security settings. */
$page_meta = ['title' => 'Profile | Paynancial', 'heading' => 'Profile & Security'];

$pdo = db();
$errors = [];
$success = false;

$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    } else {
        $formAction = (string) ($_POST['form_action'] ?? 'update_profile');

        if ($formAction === 'change_password' || $formAction === 'logout_other_devices') {
            $result = handle_security_panel_post($pdo, $auth_user, $formAction);
            $errors = $result['errors'];
            $notice = $result['notice'];
        } else {
            $fullName = sanitize_input((string) ($_POST['full_name'] ?? ''));
            $mobile = sanitize_input((string) ($_POST['mobile'] ?? ''));
            if ($fullName === '') { $errors[] = 'Name is required.'; }
            if ($mobile !== '' && !is_valid_mobile($mobile)) { $errors[] = 'Please enter a valid mobile number.'; }

            if (empty($errors)) {
                $upd = $pdo->prepare('UPDATE users SET full_name = :name, mobile = :mobile WHERE id = :id');
                $upd->execute(['name' => $fullName, 'mobile' => $mobile ?: null, 'id' => $auth_user['id']]);
                $_SESSION['user']['name'] = $fullName;
                $auth_user['name'] = $fullName;
                $success = true;
            }
        }
    }
}

$stmt = $pdo->prepare('SELECT full_name, email, mobile FROM users WHERE id = :id');
$stmt->execute(['id' => $auth_user['id']]);
$user = $stmt->fetch();
?>
<div class="panel" style="max-width:560px;">
  <div class="panel-head"><h2>Profile</h2></div>
  <?php if ($success): ?><div class="badge success" style="margin-bottom:16px;">Profile updated</div><?php endif; ?>
  <?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>
  <?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:12px;"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" style="display:grid;gap:16px;">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="update_profile">
    <div class="field"><label>Full Name</label><input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required></div>
    <div class="field"><label>Email</label><input type="email" value="<?= e($user['email']) ?>" disabled></div>
    <div class="field"><label>Mobile</label><input type="text" name="mobile" value="<?= e($user['mobile'] ?? '') ?>"></div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>
</div>

<?php render_security_panel($pdo, $auth_user); ?>
