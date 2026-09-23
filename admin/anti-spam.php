<?php
/**
 * Admin — Settings → Communication → Floating Enquiry → Anti-Spam.
 *
 * Non-secret settings are stored in the `settings` table (prefix
 * fe_antispam_). The Turnstile SECRET key is never shown or editable here:
 * it lives only in the server environment (TURNSTILE_SECRET_KEY), and this
 * page reports only whether it is configured.
 */
require_once __DIR__ . '/../includes/anti-spam.php';

$page_meta = ['title' => 'Floating Enquiry Anti-Spam | Paynancial Admin', 'heading' => 'Settings · Communication · Floating Enquiry · Anti-Spam'];

$errors = [];
$saved = false;
$limits = [
    'rl_ip'       => 'Per IP address',
    'rl_session'  => 'Per browser session',
    'rl_email'    => 'Per email address',
    'rl_phone'    => 'Per phone number',
    'rl_fallback' => 'Per IP while Cloudflare is unreachable',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    } else {
        $new = [
            'enabled'          => isset($_POST['enabled']) ? '1' : '0',
            'active'           => isset($_POST['active']) ? '1' : '0',
            'honeypot_enabled' => isset($_POST['honeypot_enabled']) ? '1' : '0',
            'site_key'         => trim((string) ($_POST['site_key'] ?? '')),
            'notify_to'        => trim((string) ($_POST['notify_to'] ?? '')),
        ];
        if ($new['site_key'] !== '' && !preg_match('/^[0-9A-Za-z_\-]{10,100}$/', $new['site_key'])) {
            $errors[] = 'The site key does not look valid.';
        }
        if ($new['notify_to'] !== '' && !filter_var($new['notify_to'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid notification email.';
        }
        foreach (array_keys($limits) as $k) {
            $max = (int) ($_POST[$k . '_max'] ?? 0);
            $win = (int) ($_POST[$k . '_window'] ?? 0);
            if ($max < 1 || $max > 100 || $win < 60 || $win > 86400) {
                $errors[] = $limits[$k] . ': use 1–100 enquiries per 60–86,400 seconds.';
            }
            $new[$k . '_max'] = (string) $max;
            $new[$k . '_window'] = (string) $win;
        }
        if (!$errors) {
            try {
                $stmt = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
                foreach ($new as $k => $v) {
                    $stmt->execute(['k' => AS_SETTINGS_PREFIX . $k, 'v' => $v]);
                }
                $saved = true;
                header('Location: /admin/anti-spam?saved=1', true, 303);
                exit;
            } catch (Throwable $e) {
                error_log('[Paynancial] Anti-spam settings save failed: ' . get_class($e));
                $errors[] = 'Settings could not be saved. Please try again.';
            }
        }
    }
}

$s = as_settings();
$available = as_form_available();
$events = as_recent_events(25);
$check = fn (string $k) => $s[$k] === '1' ? ' checked' : '';
?>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Settings saved.</div><?php endif; ?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="stat-grid">
  <div class="stat-card"><span class="label">Callback form</span><strong class="value"><?= $available ? 'Live' : 'Hidden' ?></strong></div>
  <div class="stat-card"><span class="label">CAPTCHA provider</span><strong class="value">Cloudflare Turnstile</strong></div>
  <div class="stat-card"><span class="label">Site key</span><strong class="value"><?= as_site_key() !== '' ? 'Configured' : 'Missing' ?></strong></div>
  <div class="stat-card"><span class="label">Secret key</span><strong class="value"><?= as_secret_configured() ? 'Configured (hidden)' : 'Missing' ?></strong></div>
</div>

<div class="panel">
  <p class="text-muted">The “Request a Callback” form in the floating widget appears only when it is active, the CAPTCHA is enabled and both keys are configured — it is never shown unprotected. WhatsApp, email and call links are never affected by these settings.</p>
  <form method="post" action="/admin/anti-spam" class="form-grid">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label><input type="checkbox" name="active"<?= $check('active') ?>> Active — show “Request a Callback” in the widget</label>
    <label><input type="checkbox" name="enabled"<?= $check('enabled') ?>> CAPTCHA enabled (turning it off hides the form)</label>
    <label><input type="checkbox" name="honeypot_enabled"<?= $check('honeypot_enabled') ?>> Honeypot and minimum fill time enabled</label>

    <label>CAPTCHA provider
      <input type="text" value="Cloudflare Turnstile (invisible, interaction only when needed)" disabled>
    </label>
    <label>Site key (public; leave blank to use TURNSTILE_SITE_KEY from the environment)
      <input type="text" name="site_key" value="<?= e($s['site_key']) ?>" autocomplete="off" spellcheck="false">
    </label>
    <label>Secret key
      <input type="text" value="<?= as_secret_configured() ? 'Set in the server environment — not shown' : 'Not set — add TURNSTILE_SECRET_KEY to the server environment' ?>" disabled>
    </label>
    <label>Risk threshold
      <input type="text" value="Not applicable — Turnstile returns pass / fail, no score" disabled>
    </label>
    <label>Notification email (blank = sales inbox)
      <input type="email" name="notify_to" value="<?= e($s['notify_to']) ?>">
    </label>

    <fieldset>
      <legend>Rate limits (enquiries allowed per window)</legend>
      <table class="data-table">
        <thead><tr><th>Limit</th><th>Maximum</th><th>Window (seconds)</th></tr></thead>
        <tbody>
          <?php foreach ($limits as $k => $label): ?>
          <tr>
            <td><?= e($label) ?></td>
            <td><input type="number" name="<?= e($k) ?>_max" min="1" max="100" value="<?= e($s[$k . '_max']) ?>"></td>
            <td><input type="number" name="<?= e($k) ?>_window" min="60" max="86400" value="<?= e($s[$k . '_window']) ?>"></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </fieldset>
    <button type="submit" class="btn btn-primary">Save settings</button>
  </form>
</div>

<div class="panel">
  <h2>Recent anti-abuse events</h2>
  <p class="text-muted">Discarded bots, failed security checks, rate limits and Cloudflare outages. No personal data is logged — the IP column is a keyed hash.</p>
  <?php if (!$events): ?>
    <p class="text-muted">No events recorded yet.</p>
  <?php else: ?>
  <table class="data-table">
    <thead><tr><th>Time (UTC)</th><th>Event</th><th>Reason</th><th>IP hash</th></tr></thead>
    <tbody>
      <?php foreach ($events as $ev): ?>
      <tr><td><?= e($ev['t'] ?? '') ?></td><td><?= e($ev['event'] ?? '') ?></td><td><?= e($ev['reason'] ?? '') ?></td><td class="mono"><?= e($ev['ip'] ?? '') ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
