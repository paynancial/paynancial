<?php
/**
 * POST /api/auth/forgot-password
 * Body: { csrf_token, login_type, identifier }
 * Always returns a generic success message to avoid leaking account existence.
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = json_body();

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

if (!rate_limit('forgot_pw_' . client_ip(), 10, 600)) {
    json_response(['ok' => false, 'error' => 'Too many requests. Please try again later.'], 429);
}

$identifier = sanitize_input((string) ($body['identifier'] ?? ''));
if ($identifier === '') {
    json_response(['ok' => false, 'error' => 'Please enter your email or mobile number.'], 422);
}

$pdo = db();
$stmt = $pdo->prepare('SELECT id, email, full_name FROM users WHERE email = :i1 OR mobile = :i2 LIMIT 1');
$stmt->execute(['i1' => $identifier, 'i2' => $identifier]);
$user = $stmt->fetch();

if ($user) {
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expires = (new DateTime())->modify('+' . PASSWORD_RESET_TOKEN_TTL_MINUTES . ' minutes')->format('Y-m-d H:i:s');

    $insert = $pdo->prepare(
        'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)'
    );
    $insert->execute(['uid' => $user['id'], 'hash' => $tokenHash, 'exp' => $expires]);

    $resetLink = site_url('/reset-password?token=' . $token);
    $subject = 'Reset your Paynancial password';
    $message = "Hello {$user['full_name']},\n\nA password reset was requested for your Paynancial account. This link expires in " . PASSWORD_RESET_TOKEN_TTL_MINUTES . " minutes:\n{$resetLink}\n\nIf you did not request this, you can safely ignore this email.";
    $headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';

    // Best-effort send; failures never leak account existence to the caller.
    @mail($user['email'], $subject, $message, $headers);
}

json_response(['ok' => true, 'message' => 'If an account matches, reset instructions have been sent.']);
