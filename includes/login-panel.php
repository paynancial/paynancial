<?php
/**
 * Left-side off-canvas login panel: Customer / Partner / Employee / HRMS.
 * Authenticates via AJAX to /api/auth/login (see api/auth/login.php).
 */
?>
<div class="login-overlay" data-login-overlay></div>
<aside class="login-panel" data-login-panel role="dialog" aria-modal="true" aria-labelledby="login-panel-title">
  <div class="login-panel-head">
    <div>
      <h2 id="login-panel-title">Paynancial</h2>
      <p>Secure access to your account</p>
    </div>
    <button type="button" class="login-close" data-login-close aria-label="Close login panel">&times;</button>
  </div>

  <div class="login-panel-body">
    <div class="role-tabs" role="tablist" aria-label="Choose login type">
      <button type="button" class="role-tab is-active" data-role="customer" role="tab" aria-selected="true">
        <strong>Customer</strong><span>Manage payments &amp; collections</span>
      </button>
      <button type="button" class="role-tab" data-role="partner" role="tab" aria-selected="false">
        <strong>Partner</strong><span>Partner &amp; reseller portal</span>
      </button>
      <button type="button" class="role-tab" data-role="employee" role="tab" aria-selected="false">
        <strong>Employee</strong><span>Internal staff access</span>
      </button>
      <button type="button" class="role-tab" data-role="hr" role="tab" aria-selected="false">
        <strong>HRMS</strong><span>HR &amp; people platform</span>
      </button>
    </div>

    <!-- Customer -->
    <form class="login-form is-active" data-role-form="customer" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="login_type" value="customer">
      <div class="form-error" role="alert"></div>
      <div class="field">
        <label for="customer-identifier">Email / Mobile Number</label>
        <input id="customer-identifier" name="identifier" type="text" autocomplete="username" required>
      </div>
      <div class="field">
        <label for="customer-password">Password</label>
        <input id="customer-password" name="password" type="password" autocomplete="current-password" required>
      </div>
      <div class="field-row">
        <label><input type="checkbox" name="remember"> Remember me</label>
        <a href="/forgot-password?type=customer">Forgot Password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
      <p class="form-note">Don't have an account? <a href="/signup">Create Customer Account</a></p>
    </form>

    <!-- Partner -->
    <form class="login-form" data-role-form="partner" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="login_type" value="partner">
      <div class="form-error" role="alert"></div>
      <div class="field">
        <label for="partner-identifier">Partner ID / Email</label>
        <input id="partner-identifier" name="identifier" type="text" autocomplete="username" required>
      </div>
      <div class="field">
        <label for="partner-password">Password</label>
        <input id="partner-password" name="password" type="password" autocomplete="current-password" required>
      </div>
      <div class="field-row">
        <label><input type="checkbox" name="remember"> Remember me</label>
        <a href="/forgot-password?type=partner">Forgot Password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Partner Login</button>
      <p class="form-note">Want to become a partner? <a href="/partner/register">Apply here</a></p>
    </form>

    <!-- Employee -->
    <form class="login-form" data-role-form="employee" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="login_type" value="employee">
      <div class="form-error" role="alert"></div>
      <div class="field">
        <label for="employee-identifier">Employee ID / Email</label>
        <input id="employee-identifier" name="identifier" type="text" autocomplete="username" required>
      </div>
      <div class="field">
        <label for="employee-password">Password</label>
        <input id="employee-password" name="password" type="password" autocomplete="current-password" required>
      </div>
      <div class="field-row">
        <label><input type="checkbox" name="remember"> Remember me</label>
        <a href="/forgot-password?type=employee">Forgot Password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Employee Login</button>
    </form>

    <!-- HRMS -->
    <form class="login-form" data-role-form="hr" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="login_type" value="hr">
      <div class="form-error" role="alert"></div>
      <div class="field">
        <label for="hr-identifier">Employee ID</label>
        <input id="hr-identifier" name="identifier" type="text" autocomplete="username" required>
      </div>
      <div class="field">
        <label for="hr-password">Password</label>
        <input id="hr-password" name="password" type="password" autocomplete="current-password" required>
      </div>
      <div class="field-row">
        <label></label>
        <a href="/forgot-password?type=hr">Forgot Password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-block">HRMS Login</button>
    </form>

    <!-- OTP verification step, shared across all four login forms -->
    <form class="login-form" data-role-form="otp" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="form-error" role="alert"></div>
      <p class="form-note" style="margin-top:0;">Enter the <?= (int) OTP_LENGTH ?>-digit code sent to <strong data-otp-destination></strong></p>
      <div class="field">
        <label for="otp-code">Verification code</label>
        <input id="otp-code" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="<?= (int) OTP_LENGTH ?>" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Verify &amp; continue</button>
      <p class="form-note">
        <button type="button" class="btn-link-like" data-otp-resend style="background:none;border:none;color:inherit;padding:0;font:inherit;cursor:pointer;text-decoration:underline;">Resend code</button>
        &nbsp;·&nbsp;
        <button type="button" class="btn-link-like" data-otp-back style="background:none;border:none;color:inherit;padding:0;font:inherit;cursor:pointer;text-decoration:underline;">Back to sign in</button>
      </p>
    </form>

    <div class="login-security-msg">
      <span aria-hidden="true">🔒</span>
      <span>Your information is protected with secure authentication.</span>
    </div>
  </div>
</aside>
