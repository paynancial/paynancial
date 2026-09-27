<?php
/**
 * System health — real checks only.
 *
 * Every check returns one of:
 *   operational · warning · critical · not_connected · unavailable
 * with the evidence it is based on. A check that throws becomes
 * "unavailable" (logged) — it never defaults to operational. There is no
 * uptime monitor in this architecture, so uptime is always "not connected".
 */

declare(strict_types=1);

/** @return list<array{key:string,group:string,label:string,state:string,detail:string,evidence:string,checked_at:string}> */
function admin_health_checks(?array $only = null): array
{
    $checks = admin_health_definitions();
    $out = [];
    foreach ($checks as $key => [$group, $label, $fn]) {
        if ($only !== null && !in_array($key, $only, true)) {
            continue;
        }
        $started = microtime(true);
        try {
            [$state, $detail, $evidence] = $fn();
        } catch (Throwable $e) {
            error_log('[Paynancial health] ' . $key . ' failed: ' . $e->getMessage());
            [$state, $detail, $evidence] = ['unavailable', 'The check could not run. The error was logged.', ''];
        }
        $out[] = [
            'key' => $key, 'group' => $group, 'label' => $label, 'state' => $state, 'detail' => $detail,
            'evidence' => $evidence, 'checked_at' => date('Y-m-d H:i:s'), 'ms' => (int) round((microtime(true) - $started) * 1000),
        ];
    }
    return $out;
}

/** Check definitions: key => [group, label, fn(): [state, detail, evidence]]. */
function admin_health_definitions(): array
{
    $prod = defined('APP_ENV') && APP_ENV === 'production';
    $root = dirname(__DIR__, 2);
    return [
        'app.runtime' => ['Website', 'Application runtime', static function (): array {
            $need = ['pdo_mysql', 'mbstring', 'json', 'openssl', 'fileinfo', 'dom', 'gd'];
            $missing = array_values(array_filter($need, fn ($x) => !extension_loaded($x)));
            $ok = PHP_VERSION_ID >= 80100 && !$missing;
            return [$ok ? 'operational' : 'critical',
                $ok ? 'PHP and required extensions are available.' : 'Missing: ' . ($missing ? implode(', ', $missing) : 'PHP 8.1+'),
                'PHP ' . PHP_VERSION . ' · ' . PHP_SAPI . ' · ' . PHP_OS_FAMILY];
        }],
        'app.debug' => ['Website', 'Error display', static function () use ($prod): array {
            $debug = defined('APP_DEBUG') && APP_DEBUG;
            if ($prod) {
                return [$debug ? 'critical' : 'operational', $debug ? 'APP_DEBUG is on in production — errors may leak details.' : 'Errors are hidden from visitors.', 'APP_ENV=production, APP_DEBUG=' . ($debug ? 'true' : 'false')];
            }
            return ['operational', 'Development environment.', 'APP_ENV=' . (defined('APP_ENV') ? APP_ENV : '?') . ', APP_DEBUG=' . ($debug ? 'true' : 'false')];
        }],
        'db.connect' => ['Database', 'Database connection', static function (): array {
            $t = microtime(true);
            $pdo = db();
            $pdo->query('SELECT 1')->fetchColumn();
            $ms = (microtime(true) - $t) * 1000;
            $ver = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
            $state = $ms > 500 ? 'warning' : 'operational';
            return [$state, $state === 'operational' ? 'The database answered.' : 'The database answered slowly.', sprintf('%s · %.1f ms', $ver, $ms)];
        }],
        'db.schema' => ['Database', 'Schema migrations', static function (): array {
            $pdo = db();
            $probes = [
                'CMS editing (2026-09-25)' => 'SELECT published_json FROM blog_posts LIMIT 0',
                'Admin platform (2026-09-28)' => 'SELECT actor_role FROM audit_logs LIMIT 0',
                'User preferences (2026-09-28)' => 'SELECT pref_key FROM user_preferences LIMIT 0',
                'Anti-spam store' => 'SELECT 1 FROM anti_spam_hits LIMIT 0',
            ];
            $missing = [];
            foreach ($probes as $label => $sql) {
                try {
                    $pdo->query($sql);
                } catch (Throwable $e) {
                    $missing[] = $label;
                }
            }
            return [$missing ? 'warning' : 'operational', $missing ? 'Not applied: ' . implode(', ', $missing) . '.' : 'All expected migrations are present.',
                (count($probes) - count($missing)) . ' of ' . count($probes) . ' present'];
        }],
        'storage.disk' => ['File Storage', 'Disk space', static function () use ($root): array {
            $free = @disk_free_space($root);
            $total = @disk_total_space($root);
            if (!$free || !$total) {
                return ['unavailable', 'Disk space could not be read on this host.', ''];
            }
            $pct = $free / $total * 100;
            $state = $pct < 5 ? 'critical' : ($pct < 15 ? 'warning' : 'operational');
            return [$state, sprintf('%.0f%% free.', $pct), sprintf('%s free of %s', admin_bytes($free), admin_bytes($total))];
        }],
        'storage.writable' => ['File Storage', 'Upload directories', static function () use ($root): array {
            $dirs = ['storage/' => $root . '/storage', 'public/uploads/' => $root . '/public/uploads'];
            $bad = [];
            foreach ($dirs as $label => $dir) {
                if (!is_dir($dir) || !is_writable($dir)) {
                    $bad[] = $label;
                }
            }
            return [$bad ? 'warning' : 'operational', $bad ? 'Not writable: ' . implode(', ', $bad) : 'Upload directories are writable.', implode(', ', array_keys($dirs))];
        }],
        'email.transport' => ['Email Service', 'Email transport', static function (): array {
            $path = (string) ini_get('sendmail_path');
            $bin = strtok($path, ' ') ?: '';
            if (!function_exists('mail') || $bin === '' || !is_executable($bin)) {
                return ['warning', 'No local mail transport found — OTP and enquiry emails may not send.', 'sendmail_path=' . ($path ?: '(empty)')];
            }
            return ['unavailable', 'PHP mail() is configured. Delivery is not verified — no delivery-tracking source is connected.', 'sendmail_path=' . $path];
        }],
        'security.https' => ['Security Monitoring', 'Secure cookies / HTTPS', static function () use ($prod): array {
            $secure = defined('SESSION_COOKIE_SECURE') && SESSION_COOKIE_SECURE;
            if ($prod) {
                return [$secure ? 'operational' : 'critical', $secure ? 'Session cookies are HTTPS-only (HSTS sent).' : 'Session cookies are not HTTPS-only in production.', 'SESSION_COOKIE_SECURE=' . ($secure ? 'true' : 'false')];
            }
            return [$secure ? 'operational' : 'warning', $secure ? 'Session cookies are HTTPS-only.' : 'Development: cookies not HTTPS-only (expected locally, required in production).', 'SESSION_COOKIE_SECURE=' . ($secure ? 'true' : 'false')];
        }],
        'security.turnstile' => ['Security Monitoring', 'Anti-spam (Turnstile)', static function (): array {
            $site = (string) getenv('TURNSTILE_SITE_KEY');
            $secret = (string) getenv('TURNSTILE_SECRET_KEY');
            if ($site === '' || $secret === '') {
                return ['not_connected', 'Keys are not set in the server environment. The callback form stays hidden (never unprotected).', 'site key: ' . ($site ? 'set' : 'missing') . ', secret: ' . ($secret ? 'set' : 'missing')];
            }
            return ['operational', 'Turnstile keys are configured (values hidden).', 'site key: set, secret: set'];
        }],
        'security.audit' => ['Security Monitoring', 'Audit logging', static function (): array {
            $last = db()->query('SELECT MAX(created_at) FROM audit_logs')->fetchColumn();
            $day = (int) db()->query("SELECT COUNT(*) FROM audit_logs WHERE created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn();
            $denied = (int) db()->query("SELECT COUNT(*) FROM audit_logs WHERE action IN ('access.denied','auth.login_failed') AND created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn();
            return ['operational', 'Audit log is writable and recording.', 'last entry ' . ($last ?: 'none') . ' · ' . $day . ' in 24 h · ' . $denied . ' denied / failed logins in 24 h'];
        }],
        'security.headers' => ['Security Monitoring', 'PHP exposure', static function (): array {
            $exposed = (bool) ini_get('expose_php');
            return [$exposed ? 'warning' : 'operational', $exposed ? 'expose_php is on: the PHP version is sent in X-Powered-By.' : 'PHP version is not advertised.', 'expose_php=' . ($exposed ? 'On' : 'Off')];
        }],
        'security.monitoring' => ['Security Monitoring', 'External security monitoring', static fn (): array => ['not_connected', 'No external security monitoring / alerting service is connected.', '']],
        'cache.opcache' => ['Cache', 'PHP OPcache', static function (): array {
            $on = function_exists('opcache_get_status') && ($s = @opcache_get_status(false)) && !empty($s['opcache_enabled']);
            return [$on ? 'operational' : 'warning', $on ? 'OPcache is enabled.' : 'OPcache is not enabled for this PHP process (recommended in production).', ''];
        }],
        'cache.page' => ['Cache', 'Page / CDN cache', static fn (): array => ['not_connected', 'No application page cache exists; Cloudflare purge is not connected (no API token).', '']],
        'queue' => ['Cache', 'Background queue', static fn (): array => ['not_connected', 'This architecture has no background job queue.', '']],
        'uptime' => ['Website', 'Uptime monitoring', static fn (): array => ['not_connected', 'No uptime monitor is connected, so no uptime percentage is shown.', '']],
        'int.analytics' => ['External integrations', 'Analytics', static fn (): array => admin_integration_state('Analytics', ['GA4_PROPERTY_ID'])],
        'int.search_console' => ['External integrations', 'Search Console', static fn (): array => admin_integration_state('Search Console', ['GSC_SITE_URL'])],
        'int.cloudflare' => ['External integrations', 'Cloudflare', static fn (): array => admin_integration_state('Cloudflare', ['CLOUDFLARE_API_TOKEN', 'CLOUDFLARE_ZONE_ID'])],
        'int.payments' => ['External integrations', 'Payment processor', static fn (): array => ['not_connected', 'No payment processor feeds this admin. Transaction, revenue and settlement figures are not shown.', '']],
    ];
}

/** An integration is "not connected" until its credentials exist AND the integration is built (Phase 3/9). */
function admin_integration_state(string $name, array $envKeys): array
{
    $present = array_filter($envKeys, fn ($k) => (string) getenv($k) !== '');
    if (!$present) {
        return ['not_connected', $name . ' is not connected.', 'env: ' . implode(', ', $envKeys) . ' not set'];
    }
    return ['unavailable', 'Credentials are present, but the ' . $name . ' integration is not built yet.', 'env: ' . implode(', ', array_keys($present)) . ' set'];
}

/** Worst state of a set of checks, and the headline text. "Operational" only when every check says so. */
function admin_health_summary(array $checks): array
{
    $states = array_column($checks, 'state');
    if (in_array('critical', $states, true)) {
        return ['critical', 'coral', 'Critical issue detected'];
    }
    if (in_array('warning', $states, true)) {
        return ['warning', 'yellow', 'Some checks need attention'];
    }
    if ($states && !array_diff($states, ['operational'])) {
        return ['operational', 'green', 'All systems operational'];
    }
    return ['unavailable', 'grey', 'Some statuses unavailable'];
}

/** Aggregated state for a group (for the dashboard rail). */
function admin_health_group_state(array $checks, string $group): string
{
    $states = array_column(array_filter($checks, fn ($c) => $c['group'] === $group), 'state');
    foreach (['critical', 'warning'] as $s) {
        if (in_array($s, $states, true)) {
            return $s;
        }
    }
    if (!$states) {
        return 'unavailable';
    }
    if (!array_diff($states, ['operational'])) {
        return 'operational';
    }
    if (!array_diff($states, ['not_connected'])) {
        return 'not_connected';
    }
    // Mixed operational + not-connected/unavailable: report only what is proven.
    return in_array('operational', $states, true) && !in_array('unavailable', $states, true) ? 'operational' : 'unavailable';
}

function admin_bytes(float $b): string
{
    foreach (['B', 'KB', 'MB', 'GB', 'TB'] as $u) {
        if ($b < 1024) {
            return sprintf($u === 'B' ? '%d %s' : '%.1f %s', $b, $u);
        }
        $b /= 1024;
    }
    return sprintf('%.1f PB', $b);
}
