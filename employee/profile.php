<?php
/** Employee Portal — profile + security settings. */
$page_meta = ['title' => 'Profile & Security | Paynancial', 'heading' => 'Profile & Security'];

$pdo = db();
$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    } else {
        $formAction = (string) ($_POST['form_action'] ?? '');
        if ($formAction === 'change_password' || $formAction === 'logout_other_devices') {
            $result = handle_security_panel_post($pdo, $auth_user, $formAction);
            $errors = $result['errors'];
            $notice = $result['notice'];
        }
    }
}

$stmt = $pdo->prepare('SELECT u.full_name, u.email, u.mobile, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id');
$stmt->execute(['id' => $auth_user['id']]);
$user = $stmt->fetch();
?>
<?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
<?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Your Account</h2></div>
  <div class="ledger">
    <div class="ledger-row"><span class="ledger-tag">Name</span><h3 style="font-size:0.95rem;"><?= e($user['full_name']) ?></h3><span></span></div>
    <div class="ledger-row"><span class="ledger-tag">Email</span><h3 style="font-size:0.95rem;"><?= e($user['email']) ?></h3><span></span></div>
    <div class="ledger-row"><span class="ledger-tag">Mobile</span><h3 style="font-size:0.95rem;"><?= e($user['mobile'] ?: '—') ?></h3><span></span></div>
    <div class="ledger-row"><span class="ledger-tag">Role</span><h3 style="font-size:0.95rem;"><?= e($user['role_name']) ?></h3><span></span></div>
  </div>
  <p class="text-muted" style="font-size:0.82rem;margin-top:12px;">To update your name, mobile number or email, contact an administrator — these are sensitive account fields that go through the maker-checker change request process.</p>
</div>

<?php render_security_panel($pdo, $auth_user); ?>
