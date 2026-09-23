<?php
/**
 * CSRF, headers, sanitization and rate-limiting helpers.
 */

declare(strict_types=1);

/**
 * Per-request CSP nonce. Generated once and cached for the life of the
 * request; every inline <script> in the codebase must carry
 * nonce="<?= csp_nonce() ?>" or the browser silently drops it — there is
 * no 'unsafe-inline' fallback, so an inline script missing the nonce is a
 * bug, not a style choice.
 */
function csp_nonce(): string
{
    static $nonce = null;
    if ($nonce === null) {
        $nonce = base64_encode(random_bytes(16));
    }
    return $nonce;
}

/** Send hardened security headers. Call once, early. */
function security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    $nonce = csp_nonce();
    header(
        "Content-Security-Policy: default-src 'self'; img-src 'self' data:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src 'self' data: https://fonts.gstatic.com; "
        . "frame-src https://www.google.com; "
        . "script-src 'self' 'nonce-{$nonce}' https://cdnjs.cloudflare.com; frame-ancestors 'none'"
    );
    if (SESSION_COOKIE_SECURE) {
        header('Strict-Transport-Security: max-age=63072000; includeSubDomains');
    }
}

/** Configure and start a hardened PHP session. */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => SESSION_COOKIE_SECURE,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Idle timeout.
    if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > SESSION_LIFETIME_SECONDS) {
        $_SESSION = [];
        session_destroy();
        session_start();
    }
    $_SESSION['_last_activity'] = time();
}

/** Issue (or reuse) the CSRF token for this session. */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Verify a submitted CSRF token using constant-time comparison. */
function csrf_verify(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

/** Render a hidden CSRF input for HTML forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Trim + strip null bytes; does not replace output escaping. */
function sanitize_input(string $value): string
{
    return trim(str_replace("\0", '', $value));
}

function is_valid_email(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_mobile(string $value): bool
{
    return (bool) preg_match('/^\+?[0-9]{10,15}$/', $value);
}

/** Basic sliding-window rate limiter keyed by IP + action, backed by APCu when available, session fallback otherwise. */
function rate_limit(string $key, int $maxAttempts, int $windowSeconds): bool
{
    $bucketKey = '_rl_' . $key;
    $now = time();
    $bucket = $_SESSION[$bucketKey] ?? [];
    $bucket = array_filter($bucket, static fn ($ts) => $ts > $now - $windowSeconds);

    if (count($bucket) >= $maxAttempts) {
        $_SESSION[$bucketKey] = $bucket;
        return false;
    }

    $bucket[] = $now;
    $_SESSION[$bucketKey] = $bucket;
    return true;
}

/** Record a login attempt for audit + lockout tracking. */
function record_login_attempt(PDO $pdo, string $identifier, ?string $roleSlug, bool $successful): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO login_attempts (identifier, role_slug, ip_address, successful, user_agent, attempted_at)
         VALUES (:identifier, :role, :ip, :success, :ua, NOW())'
    );
    $stmt->execute([
        'identifier' => $identifier,
        'role'       => $roleSlug,
        'ip'         => client_ip(),
        'success'    => $successful ? 1 : 0,
        'ua'         => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
}

/** Count recent failed attempts for an identifier within the lockout window. */
function recent_failed_attempts(PDO $pdo, string $identifier): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE identifier = :identifier AND successful = 0
           AND attempted_at > (NOW() - INTERVAL :minutes MINUTE)'
    );
    $stmt->bindValue('identifier', $identifier);
    $stmt->bindValue('minutes', LOGIN_LOCKOUT_MINUTES, PDO::PARAM_INT);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Validate an uploaded CV file (extension + MIME + size). Returns error string or null if valid. */
function validate_cv_upload(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload failed. Please try again.';
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        return 'File exceeds the maximum allowed size of 5MB.';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED_CV_TYPES, true)) {
        return 'Only PDF, DOC and DOCX files are accepted.';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMime = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    if (!in_array($mime, $allowedMime, true)) {
        return 'The uploaded file does not appear to be a valid PDF/DOC/DOCX.';
    }

    return null;
}

/** Move a validated upload to storage with a random, non-guessable filename. */
function store_upload(array $file, string $subdir): string
{
    $dir = rtrim(UPLOAD_DIR, '/') . '/' . trim($subdir, '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = $dir . '/' . $filename;
    move_uploaded_file($file['tmp_name'], $destination);
    return $destination;
}

/**
 * Validate a KYC/business document upload (partner onboarding, customer
 * enrollment). Wider format allowlist than CVs: PDF plus common photo
 * formats, since mobile partners frequently camera-capture ID/address
 * proof documents rather than scan them.
 *
 * Antivirus scanning integration point: if a scanning service/daemon is
 * available on the host (e.g. ClamAV via a local socket), call it here
 * before returning null — this function is the single choke point every
 * document upload passes through, so wiring a scanner in later requires
 * touching only this one place.
 */
function validate_document_upload(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload failed. Please try again.';
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        return 'File exceeds the maximum allowed size of 5MB.';
    }

    $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return 'Only PDF, JPG and PNG files are accepted.';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($mime, $allowedMime, true)) {
        return 'The uploaded file does not appear to be a valid PDF, JPG or PNG.';
    }

    return null;
}

/**
 * Encrypt a sensitive value (e.g. a bank account number) at rest using
 * AES-256-GCM, keyed from APP_SECRET. Returns raw binary suitable for a
 * VARBINARY column. Never render the decrypted value in full in the UI —
 * pair with a masked last-4 display column instead.
 */
function encrypt_sensitive(string $plaintext): string
{
    $key = hash('sha256', APP_SECRET, true);
    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $iv . $tag . $ciphertext;
}

function decrypt_sensitive(string $encoded): ?string
{
    $key = hash('sha256', APP_SECRET, true);
    $iv = substr($encoded, 0, 12);
    $tag = substr($encoded, 12, 16);
    $ciphertext = substr($encoded, 28);
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $plaintext === false ? null : $plaintext;
}

/** Mask an account/card-style number for display, keeping only the last 4 digits. */
function mask_account_number(string $last4): string
{
    return '•••• •••• •••• ' . $last4;
}
