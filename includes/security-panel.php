<?php
/**
 * Shared "Security" section reused across the Customer, Partner and
 * Employee portals' profile pages: known devices + "log out of all
 * other devices" + change password. Handles its own POST actions so
 * each portal's profile.php only needs to call two functions.
 */

declare(strict_types=1);

/** Handle a security-panel POST action (change_password / logout_other_devices). Returns ['errors' => [...], 'notice' => ?string]. */
function handle_security_panel_post(PDO $pdo, array $auth_user, string $formAction): array
{
    $errors = [];
    $notice = null;

    if ($formAction === 'change_password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['new_password_confirm'] ?? '');

        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id');
        $stmt->execute(['id' => $auth_user['id']]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, (string) $hash)) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 10) {
            $errors[] = 'New password must be at least 10 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        } else {
            $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id')
                ->execute(['hash' => password_hash($new, PASSWORD_DEFAULT), 'id' => $auth_user['id']]);
            log_partner_activity($pdo, null, 'profile.password_changed', 'user', $auth_user['id']);
            $notice = 'Password updated.';
        }
    } elseif ($formAction === 'logout_other_devices') {
        $pdo->prepare('UPDATE users SET session_version = session_version + 1 WHERE id = :id')
            ->execute(['id' => $auth_user['id']]);
        $versionStmt = $pdo->prepare('SELECT session_version FROM users WHERE id = :id');
        $versionStmt->execute(['id' => $auth_user['id']]);
        // Keep THIS session alive by matching its cached version to the new one —
        // every other session's cached value is now stale and gets signed out
        // the next time require_role() checks it.
        $_SESSION['_session_version'] = (int) $versionStmt->fetchColumn();

        $currentToken = current_device_token();
        if ($currentToken !== null) {
            $pdo->prepare('DELETE FROM known_devices WHERE user_id = :uid AND device_token_hash != :hash')
                ->execute(['uid' => $auth_user['id'], 'hash' => hash('sha256', $currentToken)]);
        } else {
            $pdo->prepare('DELETE FROM known_devices WHERE user_id = :uid')->execute(['uid' => $auth_user['id']]);
        }

        log_partner_activity($pdo, null, 'profile.logged_out_other_devices', 'user', $auth_user['id']);
        $notice = 'You have been signed out of all other devices and sessions.';
    }

    return ['errors' => $errors, 'notice' => $notice];
}

/** Render the known-devices list, sign-out-other-devices action, and change-password form. */
function render_security_panel(PDO $pdo, array $auth_user): void
{
    $stmt = $pdo->prepare('SELECT device_token_hash, label, ip_address, last_seen_at FROM known_devices WHERE user_id = :uid ORDER BY last_seen_at DESC');
    $stmt->execute(['uid' => $auth_user['id']]);
    $devices = $stmt->fetchAll();

    $currentToken = current_device_token();
    $currentHash = $currentToken !== null ? hash('sha256', $currentToken) : null;
    ?>
    <div class="panel">
      <div class="panel-head"><h2>Signed-In Devices</h2></div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead><tr><th>Device</th><th>IP Address</th><th>Last Seen</th><th></th></tr></thead>
          <tbody>
            <?php if (empty($devices)): ?>
              <tr><td colspan="4"><div class="empty-state">No recognized devices yet — you'll see one here after your next sign-in.</div></td></tr>
            <?php else: foreach ($devices as $d): ?>
              <tr>
                <td><?= e($d['label'] ?: 'Unknown device') ?><?php if ($currentHash !== null && $d['device_token_hash'] === $currentHash): ?> <span class="badge success">This device</span><?php endif; ?></td>
                <td class="mono text-muted" style="font-size:0.8rem;"><?= e($d['ip_address'] ?: '—') ?></td>
                <td class="text-muted" style="font-size:0.82rem;"><?= e(date('d M Y, H:i', strtotime((string) $d['last_seen_at']))) ?></td>
                <td></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
      <form method="post" style="margin-top:16px;">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="logout_other_devices">
        <button type="submit" class="btn btn-outline js-confirm" data-confirm="Sign out of every other device and browser session? You'll stay signed in here, but other devices will need to sign in again with a fresh verification code.">Log Out of All Other Devices</button>
      </form>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Change Password</h2></div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="change_password">
        <div class="field-grid">
          <div class="field"><label>Current Password</label><input type="password" name="current_password" required></div>
          <div class="field"><label>New Password</label><input type="password" name="new_password" minlength="10" required></div>
          <div class="field"><label>Confirm New Password</label><input type="password" name="new_password_confirm" minlength="10" required></div>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
      </form>
    </div>
    <?php
}
