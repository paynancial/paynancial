<?php
/**
 * /reset-password?token=... — completes the flow started by
 * api/auth/forgot-password.php (and by the admin partner-approval
 * flow, which issues the same kind of token for a brand-new account).
 */
$page_meta = ['title' => 'Reset Password | Paynancial'];

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$tokenHash = $token !== '' ? hash('sha256', $token) : '';
$errors = [];
$success = false;

$pdo = db();

$lookup = static function (PDO $pdo, string $tokenHash): ?array {
    if ($tokenHash === '') { return null; }
    $stmt = $pdo->prepare(
        'SELECT pr.id AS reset_id, pr.expires_at, pr.used_at, u.id AS user_id, u.full_name, u.email
         FROM password_resets pr JOIN users u ON u.id = pr.user_id
         WHERE pr.token_hash = :hash ORDER BY pr.id DESC LIMIT 1'
    );
    $stmt->execute(['hash' => $tokenHash]);
    return $stmt->fetch() ?: null;
};

$reset = $lookup($pdo, $tokenHash);
$isValid = $reset && $reset['used_at'] === null && strtotime((string) $reset['expires_at']) > time();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        if (strlen($password) < 10) {
            $errors[] = 'Password must be at least 10 characters.';
        } elseif ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id')
                    ->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $reset['user_id']]);
                $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id')
                    ->execute(['id' => $reset['reset_id']]);
                $pdo->commit();
                $success = true;
            } catch (Throwable $e) {
                $pdo->rollBack();
                error_log('[Paynancial] Password reset failed: ' . $e->getMessage());
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<section style="min-height:60vh;display:flex;align-items:center;border-bottom:none;">
  <div class="container" style="max-width:440px;">
    <div class="panel" style="border:1px solid var(--border);padding:36px;">
      <?php if ($success): ?>
        <h2>Password Updated</h2>
        <p class="text-muted" style="margin-top:10px;">Your password has been changed. You can now log in from the button in the top navigation.</p>
      <?php elseif (!$isValid): ?>
        <h2>Link Expired or Invalid</h2>
        <p class="text-muted" style="margin-top:10px;">This password reset link is no longer valid. Please request a new one.</p>
      <?php else: ?>
        <h2>Set a New Password</h2>
        <p class="text-muted" style="margin-top:8px;">for <?= e($reset['email']) ?></p>
        <?php foreach ($errors as $err): ?>
          <div class="form-error is-visible" style="margin-top:16px;"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post" style="margin-top:20px;">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <div class="field"><label>New Password</label><input type="password" name="password" minlength="10" required></div>
          <div class="field"><label>Confirm Password</label><input type="password" name="password_confirm" minlength="10" required></div>
          <button type="submit" class="btn btn-primary btn-block">Update Password</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
