<?php
/**
 * Per-user admin UI preferences (user_preferences). Only a fixed allowlist
 * of keys and values is accepted. Before the Phase 1 migration the table
 * does not exist: defaults are used and nothing is saved.
 */

declare(strict_types=1);

require_once __DIR__ . '/registry.php';

function admin_pref_defaults(): array
{
    return ['sidebar' => 'expanded', 'hero_collapsed' => false, 'nav_groups' => [], 'quick_actions' => ADMIN_DEFAULT_QUICK_ACTIONS];
}

function admin_prefs(?array $user): array
{
    static $cache = [];
    $uid = (int) ($user['id'] ?? 0);
    if (isset($cache[$uid])) {
        return $cache[$uid];
    }
    $prefs = admin_pref_defaults();
    if ($uid) {
        try {
            $stmt = db()->prepare('SELECT pref_key, value_json FROM user_preferences WHERE user_id = :u');
            $stmt->execute(['u' => $uid]);
            foreach ($stmt->fetchAll() as $row) {
                $v = admin_pref_clean($row['pref_key'], json_decode((string) $row['value_json'], true));
                if ($v !== null) {
                    $prefs[$row['pref_key']] = $v;
                }
            }
        } catch (Throwable $e) {
            error_log('[Paynancial admin] preferences unavailable: ' . $e->getMessage());
        }
    }
    return $cache[$uid] = $prefs;
}

/** Validate a preference value; null = rejected. */
function admin_pref_clean(string $key, mixed $value): mixed
{
    return match ($key) {
        'sidebar' => in_array($value, ['expanded', 'collapsed'], true) ? $value : null,
        'hero_collapsed' => is_bool($value) ? $value : null,
        'nav_groups' => is_array($value)
            ? array_filter(array_map(fn ($v) => (bool) $v, array_intersect_key($value, admin_groups())), fn () => true) : null,
        'quick_actions' => is_array($value)
            ? array_slice(array_values(array_unique(array_filter($value, fn ($k) => is_string($k) && isset(admin_quick_actions()[$k])))), 0, 5) : null,
        default => null,
    };
}

function admin_pref_set(array $user, string $key, mixed $value): bool
{
    $clean = admin_pref_clean($key, $value);
    if ($clean === null) {
        return false;
    }
    db()->prepare('INSERT INTO user_preferences (user_id, pref_key, value_json) VALUES (:u, :k, :v)
                   ON DUPLICATE KEY UPDATE value_json = VALUES(value_json)')
        ->execute(['u' => (int) $user['id'], 'k' => $key, 'v' => json_encode($clean)]);
    return true;
}
