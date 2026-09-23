<?php
/**
 * Authentication + role-based access control.
 */

declare(strict_types=1);

const DASHBOARD_BY_ROLE = [
    'customer'    => '/customer/dashboard',
    'partner'     => '/partner/dashboard',
    'employee'    => '/employee/dashboard',
    'hr'          => '/hrms/dashboard',
    'admin'       => '/admin/dashboard',
    'super_admin' => '/super-admin/dashboard',
];

/** Attempt to authenticate a user against a specific login "surface" (role group). */
function attempt_login(PDO $pdo, string $identifier, string $password, string $roleGroup): array
{
    $identifier = sanitize_input($identifier);

    // roleGroup: customer | partner | employee | hr (hrms uses the hr role)
    $allowedRoles = match ($roleGroup) {
        'customer' => ['customer'],
        'partner'  => ['partner'],
        'employee' => ['employee', 'admin', 'super_admin'],
        'hr'       => ['hr', 'admin', 'super_admin'],
        default    => [],
    };

    if (empty($allowedRoles)) {
        return ['ok' => false, 'error' => 'Invalid login type.'];
    }

    if (recent_failed_attempts($pdo, $identifier) >= LOGIN_MAX_ATTEMPTS) {
        return ['ok' => false, 'error' => 'Too many failed attempts. Please try again in ' . LOGIN_LOCKOUT_MINUTES . ' minutes or reset your password.'];
    }

    $placeholders = implode(',', array_fill(0, count($allowedRoles), '?'));
    $stmt = $pdo->prepare(
        "SELECT u.*, r.slug AS role_slug FROM users u
         JOIN roles r ON r.id = u.role_id
         WHERE (u.email = ? OR u.mobile = ?) AND r.slug IN ($placeholders)
         LIMIT 1"
    );
    $stmt->execute(array_merge([$identifier, $identifier], $allowedRoles));
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        record_login_attempt($pdo, $identifier, $roleGroup, false);
        return ['ok' => false, 'error' => 'Invalid credentials.'];
    }

    if ($user['status'] !== 'active') {
        record_login_attempt($pdo, $identifier, $roleGroup, false);
        return ['ok' => false, 'error' => 'This account is not active. Please contact support.'];
    }

    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        return ['ok' => false, 'error' => 'Account temporarily locked. Please try again later.'];
    }

    record_login_attempt($pdo, $identifier, $roleGroup, true);

    // Staff accounts always verify with OTP. Customers/partners only do
    // when they've opted into two-factor, or this device hasn't been
    // seen before for this account.
    $staffRoles = ['employee', 'hr', 'admin', 'super_admin'];
    $deviceToken = current_device_token();
    $deviceKnown = $deviceToken !== null && device_is_known($pdo, (int) $user['id'], $deviceToken);
    $otpRequired = in_array($user['role_slug'], $staffRoles, true) || (bool) $user['two_factor_enabled'] || !$deviceKnown;

    if ($otpRequired) {
        $otpId = generate_and_send_otp($pdo, $user, 'login');
        $_SESSION['_login_challenge'] = [
            'user_id'    => (int) $user['id'],
            'role_group' => $roleGroup,
            'otp_id'     => $otpId,
            'expires_at' => time() + OTP_EXPIRY_MINUTES * 60,
        ];
        return ['ok' => true, 'otp_required' => true, 'destination_masked' => mask_destination($user['email'])];
    }

    finalize_login($pdo, $user);
    return ['ok' => true, 'role' => $user['role_slug'], 'redirect' => DASHBOARD_BY_ROLE[$user['role_slug']] ?? '/'];
}

/** Complete a login that required an OTP step. */
function complete_otp_login(PDO $pdo, string $code): array
{
    $challenge = $_SESSION['_login_challenge'] ?? null;
    if (!$challenge || time() > $challenge['expires_at']) {
        unset($_SESSION['_login_challenge']);
        return ['ok' => false, 'error' => 'This code has expired. Please sign in again.'];
    }

    if (!verify_otp($pdo, (int) $challenge['otp_id'], $code)) {
        return ['ok' => false, 'error' => 'Incorrect or expired code. Please try again.'];
    }

    $stmt = $pdo->prepare('SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id');
    $stmt->execute(['id' => $challenge['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        return ['ok' => false, 'error' => 'Account not found.'];
    }

    $isNewDevice = finalize_login($pdo, $user);
    unset($_SESSION['_login_challenge']);
    if ($isNewDevice) {
        send_new_device_alert($user);
    }

    return ['ok' => true, 'redirect' => DASHBOARD_BY_ROLE[$user['role_slug']] ?? '/'];
}

/** Resend the OTP for a pending login challenge. */
function resend_login_otp(PDO $pdo): array
{
    $challenge = $_SESSION['_login_challenge'] ?? null;
    if (!$challenge) {
        return ['ok' => false, 'error' => 'No sign-in in progress.'];
    }
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $challenge['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        return ['ok' => false, 'error' => 'Account not found.'];
    }
    $otpId = generate_and_send_otp($pdo, $user, 'login');
    $_SESSION['_login_challenge']['otp_id'] = $otpId;
    $_SESSION['_login_challenge']['expires_at'] = time() + OTP_EXPIRY_MINUTES * 60;
    return ['ok' => true, 'destination_masked' => mask_destination($user['email'])];
}

/** Finalize a verified login: rotate the session, persist it, and record the device. Returns true if the device is newly seen. */
function finalize_login(PDO $pdo, array $user): bool
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'    => (int) $user['id'],
        'uuid'  => $user['uuid'],
        'name'  => $user['full_name'],
        'email' => $user['email'],
        'role'  => $user['role_slug'],
    ];
    $_SESSION['_session_version'] = (int) $user['session_version'];

    $pdo->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = :ip, failed_login_count = 0 WHERE id = :id')
        ->execute(['ip' => client_ip(), 'id' => $user['id']]);

    $deviceToken = current_device_token() ?? issue_device_cookie();
    return register_known_device($pdo, (int) $user['id'], $deviceToken);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

/** Require one of the given roles; redirects to home (with login prompt) otherwise. */
function require_role(array $roles): array
{
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        header('Location: /?login=required');
        exit;
    }

    // If session_version has been bumped since this session was issued
    // (e.g. "log out of all other devices"), this session is stale.
    $currentVersion = db()->prepare('SELECT session_version FROM users WHERE id = :id');
    $currentVersion->execute(['id' => $user['id']]);
    $dbVersion = $currentVersion->fetchColumn();
    if ($dbVersion === false || (int) $dbVersion !== (int) ($_SESSION['_session_version'] ?? -1)) {
        logout_user();
        header('Location: /?login=required');
        exit;
    }

    return $user;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
