<?php
/**
 * Permission checks (RBAC) — roles → role_permissions, with per-person
 * overrides in user_permissions (effect = grant | revoke).
 *
 * super_admin always passes. Every other role holds only what the database
 * grants it. Checks are server-side; hiding a button is never the control.
 */

declare(strict_types=1);

/** Permission slugs held by a user (cached per request). */
function user_permission_slugs(array $user): array
{
    static $cache = [];
    $id = (int) ($user['id'] ?? 0);
    if (isset($cache[$id])) {
        return $cache[$id];
    }
    $pdo = db();
    $role = $pdo->prepare(
        'SELECT p.slug FROM users u
         JOIN role_permissions rp ON rp.role_id = u.role_id
         JOIN permissions p ON p.id = rp.permission_id
         WHERE u.id = :id'
    );
    $role->execute(['id' => $id]);
    $slugs = array_fill_keys($role->fetchAll(PDO::FETCH_COLUMN), true);

    $own = $pdo->prepare(
        'SELECT p.slug, up.effect FROM user_permissions up
         JOIN permissions p ON p.id = up.permission_id WHERE up.user_id = :id'
    );
    $own->execute(['id' => $id]);
    foreach ($own->fetchAll() as $row) {
        if ($row['effect'] === 'revoke') {
            unset($slugs[$row['slug']]);
        } else {
            $slugs[$row['slug']] = true;
        }
    }
    return $cache[$id] = array_keys($slugs);
}

function user_can(?array $user, string $permission): bool
{
    if (!$user || empty($user['id'])) {
        return false;
    }
    if (($user['role'] ?? '') === 'super_admin') {
        return true;
    }
    return in_array($permission, user_permission_slugs($user), true);
}

/** Throw CmsDenied unless the user holds the permission (callers show the message). */
function require_permission(?array $user, string $permission): void
{
    if (!user_can($user, $permission)) {
        throw new CmsDenied('You do not have permission to do this (' . $permission . ').');
    }
}

final class CmsDenied extends RuntimeException
{
}
