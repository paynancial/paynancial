<?php
$page_meta = [
    'title' => 'Reset Your Password | Paynancial',
    'description' => 'Reset your Paynancial account password securely.',
];
$type = $_GET['type'] ?? 'customer';
$typeLabels = ['customer' => 'Customer', 'partner' => 'Partner', 'employee' => 'Employee', 'hr' => 'HRMS'];
$label = $typeLabels[$type] ?? 'Account';
?>
<section style="padding:80px 0;">
  <div class="container" style="max-width:480px;">
    <div class="card reveal">
      <span class="eyebrow"><?= e($label) ?> Account</span>
      <h1 style="margin-top:10px;font-size:1.6rem;">Reset your password</h1>
      <p class="text-muted" style="margin-top:10px;">Enter the email or mobile number linked to your account. If it matches, we'll send instructions to reset your password.</p>

      <form id="forgot-password-form" style="margin-top:24px;display:grid;gap:16px;" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="login_type" value="<?= e($type) ?>">
        <div class="field">
          <label for="fp-identifier">Email / Mobile Number</label>
          <input id="fp-identifier" name="identifier" type="text" required>
        </div>
        <div class="form-error" role="alert"></div>
        <div class="form-note" id="fp-success" style="display:none;color:var(--success);">If an account matches, reset instructions have been sent.</div>
        <button type="submit" class="btn btn-primary btn-block">Send Reset Instructions</button>
      </form>
    </div>
  </div>
</section>
<script nonce="<?= csp_nonce() ?>">
document.getElementById('forgot-password-form').addEventListener('submit', function (e) {
  e.preventDefault();
  var form = e.target;
  var errorBox = form.querySelector('.form-error');
  var successBox = document.getElementById('fp-success');
  errorBox.classList.remove('is-visible');
  successBox.style.display = 'none';
  var payload = Object.fromEntries(new FormData(form).entries());
  fetch('/api/auth/forgot-password', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload), credentials: 'same-origin'
  }).then(function (r) { return r.json(); }).then(function (data) {
    if (data.ok) { successBox.style.display = 'block'; form.reset(); }
    else { errorBox.textContent = data.error || 'Something went wrong.'; errorBox.classList.add('is-visible'); }
  }).catch(function () { errorBox.textContent = 'Network error. Please try again.'; errorBox.classList.add('is-visible'); });
});
</script>
