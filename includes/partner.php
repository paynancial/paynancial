<?php
/**
 * Partner Hub: data-isolation helpers, team permissions, and the
 * solution recommendation engine.
 *
 * CRITICAL: every partner-scoped query in the app must filter by the
 * partner_id returned from current_partner_id() — never trust a
 * partner_id passed in a request. current_partner_id() is the single
 * source of truth for "which partner organization is this session".
 */

declare(strict_types=1);

/**
 * Resolve the current session user to a partner organization + role.
 * A user is either the original partner account (implicit "owner"), or
 * a sub-user listed in partner_users with an explicit partner_role.
 */
function current_partner_context(): ?array
{
    static $context = false; // false = not yet resolved, null = resolved to "no partner"

    if ($context !== false) {
        return $context;
    }

    $user = current_user();
    if (!$user) {
        $context = null;
        return $context;
    }

    $pdo = db();

    $stmt = $pdo->prepare('SELECT id, status FROM partners WHERE user_id = :uid');
    $stmt->execute(['uid' => $user['id']]);
    $partner = $stmt->fetch();

    if ($partner) {
        $context = [
            'partner_id'   => (int) $partner['id'],
            'role_slug'    => 'owner',
            'is_owner'     => true,
            'account_status' => $partner['status'],
        ];
        return $context;
    }

    $stmt = $pdo->prepare(
        'SELECT pu.partner_id, pu.status AS membership_status, r.slug AS role_slug, p.status AS account_status
         FROM partner_users pu
         JOIN partner_roles r ON r.id = pu.role_id
         JOIN partners p ON p.id = pu.partner_id
         WHERE pu.user_id = :uid'
    );
    $stmt->execute(['uid' => $user['id']]);
    $membership = $stmt->fetch();

    if ($membership) {
        $context = [
            'partner_id'      => (int) $membership['partner_id'],
            'role_slug'       => $membership['role_slug'],
            'is_owner'        => $membership['role_slug'] === 'owner',
            'account_status'  => $membership['account_status'],
        ];
        return $context;
    }

    $context = null;
    return $context;
}

function current_partner_id(): ?int
{
    $context = current_partner_context();
    return $context['partner_id'] ?? null;
}

/** Enforce a resolvable, active partner context or redirect home. Use at the top of every /partner/* page. */
function require_partner_context(): array
{
    $context = current_partner_context();
    if (!$context) {
        header('Location: /?login=required');
        exit;
    }
    if ($context['account_status'] === 'suspended' && $context['role_slug'] !== 'owner') {
        // Suspended partner orgs: owner can still see status/support, sub-users are locked out.
        header('Location: /partner/onboarding');
        exit;
    }
    return $context;
}

/** Module-level capability check for the current partner user. Owner/admin always pass. */
function partner_can(string $module, string $capability = 'view'): bool
{
    $context = current_partner_context();
    if (!$context) {
        return false;
    }
    if (in_array($context['role_slug'], ['owner', 'admin'], true)) {
        return true;
    }

    static $cache = [];
    $roleSlug = $context['role_slug'];
    if (!isset($cache[$roleSlug])) {
        $stmt = db()->prepare(
            'SELECT prp.module, prp.can_view, prp.can_edit FROM partner_role_permissions prp
             JOIN partner_roles r ON r.id = prp.role_id WHERE r.slug = :slug'
        );
        $stmt->execute(['slug' => $roleSlug]);
        $cache[$roleSlug] = [];
        foreach ($stmt->fetchAll() as $row) {
            $cache[$roleSlug][$row['module']] = ['view' => (bool) $row['can_view'], 'edit' => (bool) $row['can_edit']];
        }
    }

    return (bool) ($cache[$roleSlug][$module][$capability] ?? false);
}

/**
 * Record a partner-side action to the shared audit_logs table (reused
 * as the partner activity log — see partner_hub_schema.sql). $context
 * is the array returned by current_partner_context(); the acting user's
 * id is what current_user() resolves to, and the partner_id + role are
 * folded into meta_json so activity stays attributable per partner org.
 */
function log_partner_activity(PDO $pdo, ?array $context, string $action, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
{
    $user = current_user();
    $meta['partner_id'] = $context['partner_id'] ?? null;
    $meta['role_slug'] = $context['role_slug'] ?? null;

    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, meta_json)
         VALUES (:user_id, :action, :entity_type, :entity_id, :ip_address, :meta_json)'
    );
    $stmt->execute([
        'user_id'     => $user['id'] ?? null,
        'action'      => $action,
        'entity_type' => $entityType,
        'entity_id'   => $entityId,
        'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
        'meta_json'   => json_encode($meta, JSON_UNESCAPED_SLASHES),
    ]);
}

/**
 * Rules-based solution recommendation engine.
 *
 * $attributes:
 *   customer_type    string   e.g. 'ecommerce'
 *   requirements     string[] selected requirement checkboxes
 *   no_website       bool
 *   is_international bool
 *   is_enterprise    bool
 *
 * Returns a list of ['product' => row, 'reasons' => string[]] — a
 * product can be recommended by more than one matching rule, in which
 * case every reason is shown.
 */
function recommend_products_for_customer(PDO $pdo, array $attributes): array
{
    $conditions = [];
    if (!empty($attributes['customer_type'])) {
        $conditions[] = ['customer_type', (string) $attributes['customer_type']];
    }
    foreach ($attributes['requirements'] ?? [] as $requirement) {
        $conditions[] = ['requirement', (string) $requirement];
    }
    if (!empty($attributes['no_website'])) {
        $conditions[] = ['no_website', '1'];
    }
    if (!empty($attributes['is_international'])) {
        $conditions[] = ['is_international', '1'];
    }
    if (!empty($attributes['is_enterprise'])) {
        $conditions[] = ['is_enterprise', '1'];
    }

    if (empty($conditions)) {
        return [];
    }

    $placeholders = [];
    $params = [];
    foreach ($conditions as $i => [$key, $value]) {
        $placeholders[] = "(:k{$i}, :v{$i})";
        $params["k{$i}"] = $key;
        $params["v{$i}"] = $value;
    }

    $sql = "SELECT rr.reason_text, p.* FROM recommendation_rules rr
            JOIN products p ON p.id = rr.product_id
            WHERE rr.is_active = 1 AND p.is_active = 1
              AND (rr.condition_key, rr.condition_value) IN (" . implode(',', $placeholders) . ")
            ORDER BY p.sort_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $results = [];
    foreach ($stmt->fetchAll() as $row) {
        $productId = (int) $row['id'];
        if (!isset($results[$productId])) {
            $results[$productId] = ['product' => $row, 'reasons' => []];
        }
        $results[$productId]['reasons'][] = $row['reason_text'];
    }

    return array_values($results);
}
