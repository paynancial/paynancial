<?php
/**
 * Anti-spam for form submissions from the floating enquiry widget.
 *
 * Layers (all server-side; client checks are convenience only):
 *   1. Honeypot field + minimum fill time  — silently discard bots
 *   2. Rate limits per IP, session, email, phone (server-side store, not the
 *      visitor's session, so cookie-less bots cannot reset them)
 *   3. Strict validation and normalisation
 *   4. Cloudflare Turnstile token verified with Cloudflare (siteverify)
 *   5. Duplicate-submission guard
 * If Cloudflare cannot be reached, protection is NOT switched off: the
 * enquiry is accepted only under a much tighter per-IP limit, flagged, and
 * the outage is recorded for administrators (never shown to visitors).
 *
 * Configuration (secrets never in code or the CMS UI):
 *   TURNSTILE_SITE_KEY, TURNSTILE_SECRET_KEY — environment variables (or
 *   constants of the same name in config/config.php). Non-secret settings
 *   (enabled, rate limits, honeypot, notification email, active) come from
 *   the CMS `settings` table (admin → Floating Enquiry Anti-Spam), with safe
 *   defaults when the table or a row is missing.
 * WhatsApp, email and call links never touch this file: no CAPTCHA there.
 */

declare(strict_types=1);

const AS_SETTINGS_PREFIX = 'fe_antispam_';
const AS_TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
const AS_TURNSTILE_SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';

/** Environment variable first, then a config constant of the same name. */
function as_env(string $name): string
{
    $v = getenv($name);
    if ($v !== false && $v !== '') {
        return (string) $v;
    }
    return defined($name) ? (string) constant($name) : '';
}

/** Default non-secret settings (overridable in the CMS). */
function as_defaults(): array
{
    return [
        'provider'         => 'turnstile',
        'enabled'          => '1',      // CAPTCHA on. If turned off, the form is hidden — never unprotected
        'active'           => '1',      // "Request a Callback" form shown in the widget
        'honeypot_enabled' => '1',
        'site_key'         => '',       // optional CMS override of TURNSTILE_SITE_KEY (public value)
        'rl_ip_max'        => '5',  'rl_ip_window'      => '600',   // per IP: 5 per 10 min
        'rl_session_max'   => '5',  'rl_session_window' => '600',   // per session
        'rl_email_max'     => '3',  'rl_email_window'   => '3600',  // per email: 3 per hour
        'rl_phone_max'     => '3',  'rl_phone_window'   => '3600',  // per phone
        'rl_fallback_max'  => '2',  'rl_fallback_window'=> '3600',  // per IP while Cloudflare is unreachable
        'min_fill_ms'      => '2500',   // faster than this is treated as a bot
        'notify_to'        => '',       // defaults to MAIL_SALES_TO
    ];
}

/** Effective settings: defaults ← CMS settings table. Cached per request. */
function as_settings(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $s = as_defaults();
    try {
        $stmt = db()->prepare('SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE :p');
        $stmt->execute(['p' => AS_SETTINGS_PREFIX . '%']);
        foreach ($stmt->fetchAll() as $row) {
            $k = substr((string) $row['setting_key'], strlen(AS_SETTINGS_PREFIX));
            if (array_key_exists($k, $s) && $row['setting_value'] !== null) {
                $s[$k] = (string) $row['setting_value'];
            }
        }
    } catch (Throwable $e) {
        // No database / table yet: safe defaults apply.
    }
    return $cache = $s;
}

function as_site_key(): string
{
    $cms = trim(as_settings()['site_key']);
    return $cms !== '' ? $cms : as_env('TURNSTILE_SITE_KEY');
}

function as_secret_configured(): bool
{
    return as_env('TURNSTILE_SECRET_KEY') !== '';
}

/**
 * The callback form is offered only when it can be fully protected:
 * active, CAPTCHA enabled and both keys configured.
 */
function as_form_available(): bool
{
    $s = as_settings();
    return $s['active'] === '1' && $s['enabled'] === '1' && as_site_key() !== '' && as_secret_configured();
}

function as_notify_to(): string
{
    $to = trim(as_settings()['notify_to']);
    return ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) ? $to : (defined('MAIL_SALES_TO') ? MAIL_SALES_TO : '');
}

/** Keyed hash so the rate-limit store never holds raw IPs, emails or phones. */
function as_bucket(string $kind, string $value): string
{
    $secret = defined('APP_SECRET') ? APP_SECRET : 'paynancial';
    return hash_hmac('sha256', $kind . ':' . strtolower($value), $secret);
}

/**
 * Server-side sliding-window limiter. Records the hit and returns false when
 * the limit is exceeded. Store: `anti_spam_hits` table; file store under
 * storage/anti-spam if the table is unavailable.
 */
function as_rate_hit(string $kind, string $value, int $max, int $window): bool
{
    if ($value === '' || $max <= 0) {
        return true;
    }
    $bucket = as_bucket($kind, $value);
    try {
        $pdo = db();
        $pdo->prepare('DELETE FROM anti_spam_hits WHERE created_at < (NOW() - INTERVAL 1 DAY)')->execute();
        $count = $pdo->prepare('SELECT COUNT(*) FROM anti_spam_hits WHERE bucket = :b AND created_at > (NOW() - INTERVAL :w SECOND)');
        $count->bindValue('b', $bucket);
        $count->bindValue('w', $window, PDO::PARAM_INT);
        $count->execute();
        if ((int) $count->fetchColumn() >= $max) {
            return false;
        }
        $pdo->prepare('INSERT INTO anti_spam_hits (bucket, created_at) VALUES (:b, NOW())')->execute(['b' => $bucket]);
        return true;
    } catch (Throwable $e) {
        return as_rate_hit_file($bucket, $max, $window);
    }
}

function as_rate_hit_file(string $bucket, int $max, int $window): bool
{
    $dir = __DIR__ . '/../storage/anti-spam';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $fh = @fopen($dir . '/' . $bucket . '.json', 'c+');
    if ($fh === false) {
        as_log('rate_store_unavailable');
        return true; // other layers (session limit, CAPTCHA, honeypot) still apply
    }
    flock($fh, LOCK_EX);
    $now = time();
    $hits = json_decode((string) stream_get_contents($fh), true);
    $hits = array_values(array_filter(is_array($hits) ? $hits : [], fn ($t) => is_int($t) && $t > $now - $window));
    $allowed = count($hits) < $max;
    if ($allowed) {
        $hits[] = $now;
    }
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($hits));
    flock($fh, LOCK_UN);
    fclose($fh);
    return $allowed;
}

/**
 * Verify a Turnstile token with Cloudflare.
 * Returns 'ok', 'failed' (missing, invalid, expired or reused token) or
 * 'unavailable' (Cloudflare unreachable / internal error).
 */
function as_verify_turnstile(string $token, string $ip): string
{
    if ($token === '' || strlen($token) > 2048) {
        return 'failed';
    }
    $secret = as_env('TURNSTILE_SECRET_KEY');
    if ($secret === '') {
        return 'unavailable';
    }
    // A verify-URL override exists only for local testing, never in production.
    $url = AS_TURNSTILE_VERIFY_URL;
    if ((defined('APP_ENV') ? APP_ENV : 'production') !== 'production' && as_env('TURNSTILE_VERIFY_URL') !== '') {
        $url = as_env('TURNSTILE_VERIFY_URL');
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => $ip, 'idempotency_key' => bin2hex(random_bytes(16))]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 6,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($raw === false || $status >= 500 || $status === 0) {
        return 'unavailable';
    }
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        return 'unavailable';
    }
    if (($data['success'] ?? false) === true) {
        return 'ok';
    }
    $codes = is_array($data['error-codes'] ?? null) ? $data['error-codes'] : [];
    return in_array('internal-error', $codes, true) ? 'unavailable' : 'failed';
}

/**
 * Anti-abuse event log for administrators. No personal data: event name,
 * time, a keyed hash of the IP and a short machine reason only.
 */
function as_log(string $event, string $reason = ''): void
{
    $dir = __DIR__ . '/../storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $line = json_encode([
        't'      => gmdate('c'),
        'event'  => $event,
        'reason' => substr($reason, 0, 60),
        'ip'     => substr(as_bucket('ip', client_ip()), 0, 12),
    ]) . "\n";
    @file_put_contents($dir . '/anti-spam.log', $line, FILE_APPEND | LOCK_EX);
}

/** Most recent anti-abuse events (newest first) for the admin view. */
function as_recent_events(int $limit = 20): array
{
    $file = __DIR__ . '/../storage/logs/anti-spam.log';
    if (!is_file($file)) {
        return [];
    }
    $lines = array_slice(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [], -$limit);
    return array_reverse(array_values(array_filter(array_map(fn ($l) => json_decode($l, true), $lines))));
}

/** Requirement options for the callback form: value => label. */
function as_requirements(): array
{
    return [
        'payment-gateway'   => 'Payment Gateway',
        'payment-links'     => 'Payment Links',
        'collections'       => 'Recurring collections',
        'payouts'           => 'Payouts',
        'analytics'         => 'Reports & reconciliation',
        'business-services' => 'Business Services (incorporation, registrations)',
        'partnership'       => 'Partnership',
        'other'             => 'Something else',
    ];
}

/**
 * Validate and normalise a callback submission.
 * Returns [clean fields, field => error message].
 */
function as_validate(array $in): array
{
    $str = fn (string $k) => trim(str_replace("\0", '', (string) ($in[$k] ?? '')));
    $errors = [];

    $name = preg_replace('/\s+/u', ' ', $str('name'));
    if ($name === '') {
        $errors['name'] = 'Please enter your name.';
    } elseif (mb_strlen($name) > 100 || !preg_match("/^[\\p{L}\\p{M}][\\p{L}\\p{M} .'\\-]*$/u", $name)) {
        $errors['name'] = 'Please enter a valid name.';
    }

    $email = strtolower($str('email'));
    if ($email === '') {
        $errors['email'] = 'Please enter your business email.';
    } elseif (mb_strlen($email) > 190 || preg_match('/[\r\n]/', $email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    $phoneRaw = $str('phone');
    $phone = preg_replace('/[\s\-().]/', '', $phoneRaw);
    if ($phoneRaw === '') {
        $errors['phone'] = 'Please enter your phone number.';
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', (string) $phone)) {
        $errors['phone'] = 'Please enter a valid phone number, with country code if outside India.';
    }

    $company = preg_replace('/\s+/u', ' ', $str('company'));
    if (mb_strlen($company) > 150 || preg_match('/[\r\n<>]/', $company)) {
        $errors['company'] = 'Please shorten the company name.';
    }

    $requirement = $str('requirement');
    if ($requirement !== '' && !array_key_exists($requirement, as_requirements())) {
        $errors['requirement'] = 'Please choose an option from the list.';
    }

    $message = $str('message');
    if (mb_strlen($message) > 2000) {
        $errors['message'] = 'Please keep your message under 2,000 characters.';
    }

    return [compact('name', 'email', 'phone', 'company', 'requirement', 'message'), $errors];
}
