<?php
/**
 * OTP generation/verification and device recognition.
 *
 * otp_verifications and the device-cookie mechanism below back two
 * separate things: OTP-gated login (mandatory for staff, triggered by
 * an unrecognized device or two_factor_enabled for customers/partners)
 * and, in future, OTP for other sensitive actions via the same
 * `purpose` column.
 */

declare(strict_types=1);

/** Mask an email or mobile number for display without exposing it fully. */
function mask_destination(string $value): string
{
    if (str_contains($value, '@')) {
        [$local, $domain] = explode('@', $value, 2);
        $visible = mb_substr($local, 0, 2);
        return $visible . str_repeat('•', max(2, mb_strlen($local) - 2)) . '@' . $domain;
    }
    $digits = preg_replace('/\D/', '', $value) ?? '';
    if (strlen($digits) < 4) {
        return str_repeat('•', strlen($value));
    }
    return str_repeat('•', strlen($digits) - 4) . substr($digits, -4);
}

/** Generate a numeric OTP, store its hash, and email it. Returns the otp_verifications row id. */
function generate_and_send_otp(PDO $pdo, array $user, string $purpose = 'login'): int
{
    $code = str_pad((string) random_int(0, (int) (10 ** OTP_LENGTH - 1)), OTP_LENGTH, '0', STR_PAD_LEFT);
    $destination = (string) $user['email'];

    $stmt = $pdo->prepare(
        'INSERT INTO otp_verifications (user_id, channel, destination, otp_hash, purpose, expires_at)
         VALUES (:uid, "email", :dest, :hash, :purpose, DATE_ADD(NOW(), INTERVAL :ttl MINUTE))'
    );
    $stmt->execute([
        'uid' => $user['id'], 'dest' => $destination, 'hash' => password_hash($code, PASSWORD_DEFAULT),
        'purpose' => $purpose, 'ttl' => OTP_EXPIRY_MINUTES,
    ]);
    $otpId = (int) $pdo->lastInsertId();

    $subject = 'Your Paynancial verification code';
    $message = "Your one-time verification code is: {$code}\n\nThis code expires in " . OTP_EXPIRY_MINUTES . " minutes. Never share this code with anyone — Paynancial staff will never ask for it.\n\nIf you did not request this, you can safely ignore this email.";
    @mail($destination, $subject, $message, 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>');

    return $otpId;
}

/** Verify a submitted code against a pending otp_verifications row. Enforces expiry and attempt limits. */
function verify_otp(PDO $pdo, int $otpId, string $code): bool
{
    $stmt = $pdo->prepare('SELECT * FROM otp_verifications WHERE id = :id');
    $stmt->execute(['id' => $otpId]);
    $row = $stmt->fetch();

    if (!$row || $row['consumed_at'] !== null || strtotime((string) $row['expires_at']) < time()) {
        return false;
    }
    if ((int) $row['attempts'] >= OTP_MAX_ATTEMPTS) {
        return false;
    }

    $pdo->prepare('UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = :id')->execute(['id' => $otpId]);

    if (!password_verify($code, $row['otp_hash'])) {
        return false;
    }

    $pdo->prepare('UPDATE otp_verifications SET consumed_at = NOW() WHERE id = :id')->execute(['id' => $otpId]);
    return true;
}

/** Read the device-recognition cookie, if present. */
function current_device_token(): ?string
{
    $token = $_COOKIE[DEVICE_COOKIE_NAME] ?? null;
    return is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) ? $token : null;
}

function device_is_known(PDO $pdo, int $userId, string $deviceToken): bool
{
    $stmt = $pdo->prepare('SELECT id FROM known_devices WHERE user_id = :uid AND device_token_hash = :hash');
    $stmt->execute(['uid' => $userId, 'hash' => hash('sha256', $deviceToken)]);
    return (bool) $stmt->fetchColumn();
}

/** Register (or refresh) a device for a user. Returns true if this device is newly seen. */
function register_known_device(PDO $pdo, int $userId, string $deviceToken): bool
{
    $hash = hash('sha256', $deviceToken);
    $label = browser_label((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    $stmt = $pdo->prepare('SELECT id FROM known_devices WHERE user_id = :uid AND device_token_hash = :hash');
    $stmt->execute(['uid' => $userId, 'hash' => $hash]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        $pdo->prepare('UPDATE known_devices SET last_seen_at = NOW(), ip_address = :ip WHERE id = :id')
            ->execute(['ip' => client_ip(), 'id' => $existing]);
        return false;
    }

    $pdo->prepare('INSERT INTO known_devices (user_id, device_token_hash, label, ip_address) VALUES (:uid, :hash, :label, :ip)')
        ->execute(['uid' => $userId, 'hash' => $hash, 'label' => $label, 'ip' => client_ip()]);
    return true;
}

/** Issue a fresh device-recognition cookie and return its plaintext value. */
function issue_device_cookie(): string
{
    $token = bin2hex(random_bytes(32));
    setcookie(DEVICE_COOKIE_NAME, $token, [
        'expires'  => time() + DEVICE_COOKIE_DAYS * 86400,
        'path'     => '/',
        'secure'   => SESSION_COOKIE_SECURE,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    return $token;
}

/** Very small, dependency-free user-agent summary for device labels. Not a fingerprinting library. */
function browser_label(string $userAgent): string
{
    $browser = 'A browser';
    foreach (['Edg' => 'Edge', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'] as $needle => $name) {
        if (str_contains($userAgent, $needle)) { $browser = $name; break; }
    }
    $os = 'an unknown device';
    foreach (['Windows' => 'Windows', 'Mac OS' => 'macOS', 'Android' => 'Android', 'iPhone' => 'iPhone', 'iPad' => 'iPad', 'Linux' => 'Linux'] as $needle => $name) {
        if (str_contains($userAgent, $needle)) { $os = $name; break; }
    }
    return $browser . ' on ' . $os;
}

/** Email the account holder that a new device just signed in. */
function send_new_device_alert(array $user): void
{
    $label = browser_label((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    $subject = 'New sign-in to your Paynancial account';
    $message = "Hello {$user['full_name']},\n\nYour account was just signed into from a new device ({$label}, IP " . client_ip() . ").\n\nIf this was you, no action is needed. If you don't recognize this activity, reset your password immediately and contact support.";
    @mail($user['email'], $subject, $message, 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>');
}
