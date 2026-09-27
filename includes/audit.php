<?php
/**
 * Central audit helper — one way to write audit_logs for every module.
 *
 *   audit('enquiry.status_changed', 'enquiry', 42, ['status' => 'new'], ['status' => 'closed']);
 *
 * Records: user, role at the time, action, entity type + id, old and new
 * values (changed fields only), IP, user agent and timestamp. Secrets are
 * never stored: any key that looks like a password, token, secret, OTP,
 * API key, hash or account number is replaced with "[redacted]".
 *
 * An audit write never breaks the action being audited: failures are sent
 * to the PHP error log. Works before and after the Phase 1 migration
 * (actor_role / user_agent columns are used only when present).
 */

declare(strict_types=1);

const AUDIT_REDACT_PATTERN = '/pass(word)?|token|secret|otp|api[_-]?key|hash|account_number|acct|cvv|pin$|card_number|csrf|signature|private/i';

function audit_redact(mixed $value, string $key = ''): mixed
{
    if ($key !== '' && preg_match(AUDIT_REDACT_PATTERN, $key)) {
        return '[redacted]';
    }
    if (is_array($value)) {
        $out = [];
        foreach ($value as $k => $v) {
            $out[$k] = audit_redact($v, (string) $k);
        }
        return $out;
    }
    if (is_string($value) && mb_strlen($value) > 500) {
        return mb_substr($value, 0, 500) . '…';
    }
    return $value;
}

/** Only the fields that changed: [old, new]. With no $old (a create) the whole $new is kept. */
function audit_diff(array $old, array $new): array
{
    if (!$old) {
        return [[], $new];
    }
    $o = $n = [];
    foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $k) {
        $a = $old[$k] ?? null;
        $b = $new[$k] ?? null;
        if ((string) json_encode($a) !== (string) json_encode($b)) {
            $o[$k] = $a;
            $n[$k] = $b;
        }
    }
    return [$o, $n];
}

function audit_has_context_columns(PDO $pdo): bool
{
    static $has = null;
    if ($has === null) {
        try {
            $pdo->query('SELECT actor_role, user_agent FROM audit_logs LIMIT 0');
            $has = true;
        } catch (Throwable $e) {
            $has = false;
        }
    }
    return $has;
}

/**
 * Write one audit record. $actor defaults to the signed-in user
 * (['id' => …, 'role' => …]). Returns false (and logs) on failure — use
 * audit_write() where a failed audit must abort the surrounding transaction.
 */
function audit(string $action, ?string $entityType = null, ?int $entityId = null, array $old = [], array $new = [],
    array $meta = [], ?PDO $pdo = null, ?array $actor = null): bool
{
    try {
        audit_write($pdo ?? db(), $action, $entityType, $entityId, $old, $new, $meta, $actor);
        return true;
    } catch (Throwable $e) {
        error_log('[Paynancial audit] write failed for ' . $action . ': ' . $e->getMessage());
        return false;
    }
}

/** Strict variant: throws on failure (used inside approval / publish transactions). */
function audit_write(PDO $pdo, string $action, ?string $entityType = null, ?int $entityId = null, array $old = [],
    array $new = [], array $meta = [], ?array $actor = null): void
{
    $actor ??= function_exists('current_user') ? current_user() : null;
    [$o, $n] = audit_diff(audit_redact($old), audit_redact($new));
    $payload = audit_redact($meta);
    if ($o) {
        $payload['old'] = $o;
    }
    if ($n) {
        $payload['new'] = $n;
    }
    $cli = PHP_SAPI === 'cli';
    $row = [
        'uid' => isset($actor['id']) ? (int) $actor['id'] : null,
        'action' => mb_substr($action, 0, 100),
        'type' => $entityType !== null ? mb_substr($entityType, 0, 80) : null,
        'eid' => $entityId,
        'ip' => $cli ? 'cli' : (function_exists('client_ip') ? client_ip() : ($_SERVER['REMOTE_ADDR'] ?? null)),
        'meta' => $payload ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) : null,
    ];
    if (audit_has_context_columns($pdo)) {
        $pdo->prepare(
            'INSERT INTO audit_logs (user_id, actor_role, action, entity_type, entity_id, ip_address, user_agent, meta_json)
             VALUES (:uid, :role, :action, :type, :eid, :ip, :ua, :meta)'
        )->execute($row + [
            'role' => isset($actor['role']) ? mb_substr((string) $actor['role'], 0, 50) : null,
            'ua' => $cli ? 'cli' : mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    } else {
        $pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, meta_json)
             VALUES (:uid, :action, :type, :eid, :ip, :meta)'
        )->execute($row);
    }
}

/** Mask an email / phone for login-failure records ("vi***@paynancial.com"). */
function audit_mask_identifier(string $id): string
{
    if (str_contains($id, '@')) {
        [$local, $domain] = explode('@', $id, 2);
        return mb_substr($local, 0, 2) . '***@' . $domain;
    }
    return strlen($id) > 4 ? str_repeat('*', strlen($id) - 4) . substr($id, -4) : '****';
}
