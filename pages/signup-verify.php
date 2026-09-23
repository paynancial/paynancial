<?php
/**
 * Public customer self-service signup: /signup/verify (step 2 of 2).
 * Confirms the email OTP sent by pages/signup.php, activates the
 * account, logs the customer in, and sends them to onboarding.
 */

$page_meta = [
    'title' => 'Verify Your Email | Paynancial',
];

$challenge = $_SESSION['_signup_challenge'] ?? null;
if (!$challenge) {
    header('Location: /signup');
    exit;
}

$errors = [];
$resent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (isset($_POST['resend'])) {
        if (!rate_limit('signup_resend_' . client_ip(), 5, 300)) {
            $errors[] = 'Too many requests. Please wait a few minutes and try again.';
        } elseif (time() > $challenge['expires_at'] + 3600) {
            // Challenge is very stale (session hung around); force a fresh signup.
            unset($_SESSION['_signup_challenge']);
            header('Location: /signup');
            exit;
        } else {
            $pdo = db();
            $stmt = $pdo->prepare('SELECT id, email, full_name FROM users WHERE id = :id');
            $stmt->execute(['id' => $challenge['user_id']]);
            $user = $stmt->fetch();
            if ($user) {
                $otpId = generate_and_send_otp($pdo, $user, 'signup_verify');
                $_SESSION['_signup_challenge']['otp_id'] = $otpId;
                $_SESSION['_signup_challenge']['expires_at'] = time() + OTP_EXPIRY_MINUTES * 60;
                $challenge = $_SESSION['_signup_challenge'];
                $resent = true;
            }
        }
    } elseif (!rate_limit('signup_verify_' . client_ip(), 10, 300)) {
        $errors[] = 'Too many attempts. Please wait a few minutes and try again.';
    } else {
        $code = sanitize_input((string) ($_POST['otp'] ?? ''));

        if (time() > $challenge['expires_at']) {
            $errors[] = 'This code has expired. Please request a new one.';
        } elseif (!preg_match('/^\d{' . OTP_LENGTH . '}$/', $code)) {
            $errors[] = 'Please enter the ' . OTP_LENGTH . '-digit code from your email.';
        } else {
            $pdo = db();
            if (!verify_otp($pdo, (int) $challenge['otp_id'], $code)) {
                $errors[] = 'Incorrect or expired code. Please try again.';
            } else {
                $stmt = $pdo->prepare('SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id');
                $stmt->execute(['id' => $challenge['user_id']]);
                $user = $stmt->fetch();
                if (!$user) {
                    $errors[] = 'Account not found. Please sign up again.';
                } else {
                    $pdo->prepare('UPDATE users SET status = "active", email_verified_at = NOW() WHERE id = :id')
                        ->execute(['id' => $user['id']]);
                    $user['status'] = 'active';

                    finalize_login($pdo, $user);
                    unset($_SESSION['_signup_challenge']);

                    header('Location: /customer/onboarding');
                    exit;
                }
            }
        }
    }
}

$destinationMasked = null;
try {
    $stmt = db()->prepare('SELECT email FROM users WHERE id = :id');
    $stmt->execute(['id' => $challenge['user_id']]);
    $email = $stmt->fetchColumn();
    if ($email) { $destinationMasked = mask_destination((string) $email); }
} catch (Throwable $e) {
    // Non-fatal; the message below just omits the destination.
}
?>
<section class="hero" style="padding-top:72px;">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Verify Your Email</span>
      <h1>One more step to activate your account.</h1>
      <p class="lead">Enter the <?= (int) OTP_LENGTH ?>-digit code we sent to <?= $destinationMasked ? '<strong>' . e($destinationMasked) . '</strong>' : 'your email' ?>.</p>
    </div>
  </div>
</section>

<section>
  <div class="container" style="max-width:420px;">
    <?php if ($resent): ?>
      <div class="badge success" style="margin-bottom:16px;display:inline-block;">A new code has been sent.</div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
      <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="panel-wizard">
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field">
          <label for="signup-otp">Verification code</label>
          <input id="signup-otp" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="<?= (int) OTP_LENGTH ?>" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Verify &amp; Continue</button>
      </form>
      <form method="post" style="margin-top:12px;">
        <?= csrf_field() ?>
        <button type="submit" name="resend" value="1" class="btn-link-like" style="background:none;border:none;color:inherit;padding:0;font:inherit;cursor:pointer;text-decoration:underline;">Resend code</button>
      </form>
    </div>
  </div>
</section>
