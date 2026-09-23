<?php
/**
 * Public customer self-service signup: /signup (step 1 of 2).
 * Creates the account in a "pending" state, sends an email OTP, and
 * hands off to /signup/verify. The account only becomes active once
 * that code is confirmed — see pages/signup-verify.php.
 */

$page_meta = [
    'title' => 'Create Your Paynancial Account | Sign Up',
    'description' => 'Create a Paynancial customer account to start accepting payments — PayLinks, QR, UPI, cards, net banking and more.',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (!rate_limit('signup_' . client_ip(), 5, 3600)) {
        $errors[] = 'Too many sign-up attempts from this connection. Please try again later.';
    } else {
        $fullName = sanitize_input((string) ($_POST['full_name'] ?? ''));
        $email = sanitize_input((string) ($_POST['email'] ?? ''));
        $mobile = sanitize_input((string) ($_POST['mobile'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $termsAccepted = isset($_POST['terms_accepted']);

        if ($fullName === '') { $errors[] = 'Please enter your full name.'; }
        if (!is_valid_email($email)) { $errors[] = 'Please enter a valid email address.'; }
        if (!is_valid_mobile($mobile)) { $errors[] = 'Please enter a valid mobile number.'; }
        if (strlen($password) < 10) { $errors[] = 'Password must be at least 10 characters.'; }
        if ($password !== $passwordConfirm) { $errors[] = 'Passwords do not match.'; }
        if (!$termsAccepted) { $errors[] = 'Please accept the Terms & Conditions and Privacy Policy.'; }

        if (empty($errors)) {
            $pdo = db();
            $dupStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email OR mobile = :mobile');
            $dupStmt->execute(['email' => $email, 'mobile' => $mobile]);
            if ($dupStmt->fetchColumn()) {
                $errors[] = 'An account already exists with this email or mobile number. Please sign in instead.';
            }
        }

        if (empty($errors)) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE slug = 'customer'");
                $roleStmt->execute();
                $customerRoleId = $roleStmt->fetchColumn();
                if (!$customerRoleId) {
                    throw new RuntimeException('Customer sign-up is temporarily unavailable. Please contact support.');
                }

                $userIns = $pdo->prepare(
                    'INSERT INTO users (uuid, role_id, full_name, email, mobile, password_hash, status)
                     VALUES (UUID(), :role_id, :name, :email, :mobile, :hash, "pending")'
                );
                $userIns->execute([
                    'role_id' => $customerRoleId, 'name' => $fullName, 'email' => $email,
                    'mobile' => $mobile, 'hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $userId = (int) $pdo->lastInsertId();

                $customerCode = generate_sequential_code($pdo, 'customers', 'customer_code', 'PYN-CUS');
                $custIns = $pdo->prepare(
                    'INSERT INTO customers (user_id, customer_code, kyc_status, status) VALUES (:uid, :code, "not_started", "pending_verification")'
                );
                $custIns->execute(['uid' => $userId, 'code' => $customerCode]);

                $pdo->commit();

                $user = ['id' => $userId, 'email' => $email, 'full_name' => $fullName];
                $otpId = generate_and_send_otp($pdo, $user, 'signup_verify');
                $_SESSION['_signup_challenge'] = [
                    'user_id'    => $userId,
                    'otp_id'     => $otpId,
                    'expires_at' => time() + OTP_EXPIRY_MINUTES * 60,
                ];

                header('Location: /signup/verify');
                exit;
            } catch (Throwable $e) {
                $pdo->rollBack();
                error_log('[Paynancial] Signup failed: ' . $e->getMessage());
                $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'Something went wrong while creating your account. Please try again.';
            }
        }
    }
}
?>
<section class="hero" style="padding-top:72px;">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Create Your Account</span>
      <h1>Start accepting payments with Paynancial.</h1>
      <p class="lead">Create your customer account, verify your email, then complete a short business profile to activate PayLinks, UPI, cards, net banking and more.</p>
    </div>
  </div>
</section>

<section>
  <div class="container" style="max-width:560px;">
    <?php foreach ($errors as $err): ?>
      <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="panel-wizard">
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field"><label>Full Name</label><input type="text" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>"></div>
        <div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
        <div class="field"><label>Mobile Number</label><input type="text" name="mobile" required value="<?= e($_POST['mobile'] ?? '') ?>"></div>
        <div class="field"><label>Password</label><input type="password" name="password" required autocomplete="new-password"></div>
        <div class="field"><label>Confirm Password</label><input type="password" name="password_confirm" required autocomplete="new-password"></div>
        <p class="text-muted" style="font-size:0.8rem;margin-top:-8px;">Use at least 10 characters.</p>
        <label class="field-row" style="align-items:flex-start;">
          <input type="checkbox" name="terms_accepted" required style="margin-top:3px;">
          <span>I have read and accept the <a href="/legal/terms-conditions" target="_blank">Terms &amp; Conditions</a> and <a href="/legal/privacy-policy" target="_blank">Privacy Policy</a>, including how Paynancial processes my information for identity verification.</span>
        </label>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">Create Account</button>
        <p class="form-note">Already have an account? <a href="/?login=customer">Sign in</a></p>
      </form>
    </div>
  </div>
</section>
