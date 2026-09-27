<?php
/**
 * Admin module registry — the ONE definition of the enterprise admin
 * platform. It drives:
 *   - routes          (public/index.php allow-lists exactly these pages)
 *   - authorisation   (admin_guard(): view permission for GET, action
 *                      permission for POST — enforced before a page runs)
 *   - sidebar         (admin_nav(): only implemented modules the user may view)
 *   - command palette (admin_palette_items())
 *   - breadcrumbs     (group › module)
 *   - quick actions   (admin_quick_actions())
 *
 * Only IMPLEMENTED modules are listed. Future modules of the approved IA
 * (Payment Gateway, Incorporation Cases, SEO Dashboard, Developer Center,
 * Reports …) are added here in the phase that builds them, so nothing ever
 * appears as a broken link.
 *
 * Keys of a module:
 *   label, group, icon  — navigation
 *   perm                — permission to open the page (GET)
 *   post                — permission for any POST; string, or [op => perm]
 *                         keyed by $_POST['op'] / $_POST['form_action'],
 *                         with 'default' as the fallback
 *   nav                 — false for sub-pages/endpoints (default true)
 *   parent              — module shown as active for sub-pages
 *   badge               — key of a badge counter (admin_badge_count())
 *   keywords            — extra words for search / palette
 */

declare(strict_types=1);

require_once __DIR__ . '/../permissions.php';

/** Sidebar groups, in order (approved IA). Empty groups are not rendered. */
function admin_groups(): array
{
    return [
        'command'    => 'Command Center',
        'payments'   => 'Payments & Products',
        'business'   => 'Business Services',
        'operations' => 'Operations',
        'partners'   => 'Partner Hub',
        'website'    => 'Website & Content',
        'seo'        => 'SEO / AEO',
        'developer'  => 'Developer Center',
        'reports'    => 'Reports',
        'admin'      => 'Administration',
    ];
}

/** Display names of the 12 staff roles. */
function admin_role_labels(): array
{
    return [
        'super_admin' => 'Super Admin', 'admin' => 'Administrator', 'operations_manager' => 'Operations Manager',
        'sales_manager' => 'Sales Manager', 'consultant' => 'Consultant', 'incorporation_consultant' => 'Incorporation Consultant',
        'content_manager' => 'Content Manager', 'seo_manager' => 'SEO Manager', 'compliance_reviewer' => 'Legal / Compliance Reviewer',
        'finance_manager' => 'Finance Manager', 'developer' => 'Developer', 'support' => 'Support',
    ];
}

function admin_role_label(string $slug): string
{
    return admin_role_labels()[$slug] ?? ucwords(str_replace('_', ' ', $slug));
}

function admin_modules(): array
{
    return [
        // Command Center
        'dashboard'  => ['label' => 'Dashboard', 'group' => 'command', 'icon' => 'home', 'perm' => 'dashboard.view', 'post' => 'dashboard.view'],
        'approvals'  => ['label' => 'Approvals', 'group' => 'command', 'icon' => 'check', 'perm' => 'approvals.view', 'badge' => 'approvals', 'keywords' => 'review queue workflow'],
        'activity'   => ['label' => 'Activity', 'group' => 'command', 'icon' => 'pulse', 'perm' => 'dashboard.view', 'keywords' => 'timeline history'],

        // Payments & Products (content / read-only records — no processor connected, D3)
        'products'     => ['label' => 'Products', 'group' => 'payments', 'icon' => 'box', 'perm' => 'products.view', 'post' => 'products.manage', 'keywords' => 'solution catalog catalogue'],
        'transactions' => ['label' => 'Transactions', 'group' => 'payments', 'icon' => 'card', 'perm' => 'transactions.view', 'keywords' => 'payments records'],

        // Operations
        'customers' => ['label' => 'Customers', 'group' => 'operations', 'icon' => 'building', 'perm' => 'customers.view', 'post' => 'customers.create', 'keywords' => 'crm clients companies'],
        'enquiries' => ['label' => 'Enquiries', 'group' => 'operations', 'icon' => 'inbox', 'perm' => 'enquiries.view', 'badge' => 'enquiries',
                        'post' => ['default' => 'enquiries.manage', 'create' => 'enquiries.create'], 'keywords' => 'leads contact sales'],
        'documents' => ['label' => 'Documents', 'group' => 'operations', 'icon' => 'file', 'perm' => 'documents.view', 'keywords' => 'kyc files uploads'],

        // Partner Hub (existing back office)
        'partner-applications'  => ['label' => 'Partner Applications', 'group' => 'partners', 'icon' => 'handshake', 'perm' => 'partners.view', 'post' => 'partners.manage'],
        'customer-applications' => ['label' => 'Customer Applications', 'group' => 'partners', 'icon' => 'clipboard', 'perm' => 'customers.view', 'post' => 'customers.manage'],
        'customer-kyc'          => ['label' => 'Customer eKYC', 'group' => 'partners', 'icon' => 'shield', 'perm' => 'customers.view', 'post' => 'customers.manage', 'keywords' => 'kyc verification'],
        'commission-rules'      => ['label' => 'Commission Rules', 'group' => 'partners', 'icon' => 'percent', 'perm' => 'partners.view', 'post' => 'partners.manage'],

        // Website & Content (CMS pages keep their own fine-grained cms.* checks)
        'cms'          => ['label' => 'CMS Overview', 'group' => 'website', 'icon' => 'layout', 'perm' => 'cms.view', 'post' => 'cms.view', 'keywords' => 'content import access'],
        'cms-articles' => ['label' => 'Blog', 'group' => 'website', 'icon' => 'pen', 'perm' => 'cms.view', 'keywords' => 'articles insights posts'],
        'cms-article'  => ['label' => 'Article editor', 'group' => 'website', 'icon' => 'pen', 'perm' => 'cms.view', 'post' => 'cms.view', 'nav' => false, 'parent' => 'cms-articles'],
        'cms-hero'     => ['label' => 'Homepage Hero', 'group' => 'website', 'icon' => 'image', 'perm' => 'cms.view', 'post' => 'cms.view', 'keywords' => 'pages home'],
        'cms-preview'  => ['label' => 'Preview', 'group' => 'website', 'icon' => 'eye', 'perm' => 'cms.view', 'nav' => false, 'parent' => 'cms-articles'],

        // SEO / AEO
        'cms-seo' => ['label' => 'Page SEO', 'group' => 'seo', 'icon' => 'search', 'perm' => 'cms.view', 'post' => 'cms.view', 'keywords' => 'meta title description noindex'],

        // Administration
        'users'              => ['label' => 'Users', 'group' => 'admin', 'icon' => 'users', 'perm' => 'users.view', 'post' => 'users.manage'],
        'roles'              => ['label' => 'Roles & Permissions', 'group' => 'admin', 'icon' => 'key', 'perm' => 'roles.view', 'post' => 'roles.manage', 'keywords' => 'rbac access'],
        'audit-logs'         => ['label' => 'Audit Logs', 'group' => 'admin', 'icon' => 'list', 'perm' => 'audit.view'],
        'change-requests'    => ['label' => 'Change Requests', 'group' => 'admin', 'icon' => 'lock', 'perm' => 'security.view', 'post' => 'security.manage', 'badge' => 'change_requests', 'keywords' => 'security maker checker'],
        'anti-spam'          => ['label' => 'Anti-Spam', 'group' => 'admin', 'icon' => 'shield', 'perm' => 'security.view', 'post' => 'settings.manage', 'keywords' => 'turnstile captcha security'],
        'system-health'      => ['label' => 'System Health', 'group' => 'admin', 'icon' => 'heart', 'perm' => 'system.health.view', 'post' => 'system.health.view', 'keywords' => 'status database storage'],
        'content-governance' => ['label' => 'Content Governance', 'group' => 'admin', 'icon' => 'scale', 'perm' => 'governance.view', 'keywords' => 'publishing gate jurisdictions regulatory'],

        // Endpoints (no navigation)
        'search'      => ['label' => 'Search', 'group' => 'command', 'icon' => 'search', 'perm' => 'dashboard.view', 'nav' => false],
        'widget'      => ['label' => 'Widget', 'group' => 'command', 'icon' => 'home', 'perm' => 'dashboard.view', 'nav' => false],
        'preferences' => ['label' => 'Preferences', 'group' => 'command', 'icon' => 'home', 'perm' => 'dashboard.view', 'post' => 'dashboard.view', 'nav' => false],
    ];
}

function admin_module(string $key): ?array
{
    $m = admin_modules()[$key] ?? null;
    return $m === null ? null : $m + ['key' => $key, 'nav' => true, 'post' => $m['perm'], 'parent' => null, 'badge' => null, 'keywords' => ''];
}

/** Permission required for this request, or null when the module is unknown. */
function admin_required_permission(string $key, string $method, array $post = []): ?string
{
    $m = admin_module($key);
    if ($m === null) {
        return null;
    }
    if (strtoupper($method) !== 'POST') {
        return $m['perm'];
    }
    $rule = $m['post'];
    if (is_array($rule)) {
        $op = (string) ($post['op'] ?? $post['form_action'] ?? '');
        return $rule[$op] ?? $rule['default'];
    }
    return $rule;
}

/**
 * Central authorisation for every admin request. POST additionally needs
 * the page's view permission (you cannot act on what you cannot see).
 * Returns null when allowed, or the missing permission.
 */
function admin_guard(?array $user, string $key, string $method, array $post = []): ?string
{
    $m = admin_module($key);
    if ($m === null) {
        return 'unknown';
    }
    if (!user_can($user, $m['perm'])) {
        return $m['perm'];
    }
    $need = admin_required_permission($key, $method, $post);
    return user_can($user, $need) ? null : $need;
}

/** Navigation for this user: [group label => [module …]] — implemented + permitted only. */
function admin_nav(?array $user): array
{
    $out = [];
    foreach (admin_modules() as $key => $_) {
        $m = admin_module($key);
        if ($m['nav'] && user_can($user, $m['perm'])) {
            $out[admin_groups()[$m['group']]][] = $m;
        }
    }
    return array_filter(array_replace(array_fill_keys(array_values(admin_groups()), []), $out));
}

/** Breadcrumb trail for a page: [[label, href|null], …]. */
function admin_breadcrumbs(string $key, ?string $leaf = null): array
{
    $m = admin_module($key);
    if ($m === null) {
        return [];
    }
    $trail = [[admin_groups()[$m['group']], null]];
    if ($m['parent'] && ($p = admin_module($m['parent']))) {
        $trail[] = [$p['label'], '/admin/' . $p['key']];
    }
    $trail[] = [$leaf ?? $m['label'], null];
    return $trail;
}

/**
 * Quick actions (max five shown; the rest via Customize). Unavailable
 * actions say which phase delivers them instead of linking nowhere.
 */
function admin_quick_actions(): array
{
    return [
        'new-enquiry'   => ['label' => 'New Enquiry', 'help' => 'Create a new lead or enquiry', 'icon' => 'inbox', 'url' => '/admin/enquiries?new=1', 'perm' => 'enquiries.create'],
        'new-case'      => ['label' => 'New Incorporation Case', 'help' => 'Start a new incorporation case', 'icon' => 'briefcase', 'url' => null, 'perm' => 'dashboard.view', 'phase' => 'Phase 5'],
        'upload-doc'    => ['label' => 'Upload Document', 'help' => 'Add a customer or case document', 'icon' => 'upload', 'url' => null, 'perm' => 'dashboard.view', 'phase' => 'Phase 6'],
        'create-invoice' => ['label' => 'Create Invoice', 'help' => 'Generate invoice / payment link', 'icon' => 'receipt', 'url' => null, 'perm' => 'dashboard.view', 'phase' => 'Phase 7'],
        'add-customer'  => ['label' => 'Add Customer', 'help' => 'Create a new customer record', 'icon' => 'building', 'url' => '/admin/customers?new=1', 'perm' => 'customers.create'],
        'new-article'   => ['label' => 'New Article', 'help' => 'Draft a blog article for review', 'icon' => 'pen', 'url' => '/admin/cms-article/new', 'perm' => 'cms.create'],
        'review-queue'  => ['label' => 'Review Approvals', 'help' => 'Open the approvals inbox', 'icon' => 'check', 'url' => '/admin/approvals', 'perm' => 'approvals.view'],
        'system-health' => ['label' => 'System Health', 'help' => 'Run the platform health checks', 'icon' => 'heart', 'url' => '/admin/system-health', 'perm' => 'system.health.view'],
        'audit-trail'   => ['label' => 'Audit Trail', 'help' => 'Review recent sensitive actions', 'icon' => 'list', 'url' => '/admin/audit-logs', 'perm' => 'audit.view'],
    ];
}

const ADMIN_DEFAULT_QUICK_ACTIONS = ['new-enquiry', 'new-case', 'upload-doc', 'create-invoice', 'add-customer'];

/** Command palette entries for this user: modules they can open + actions they may perform. */
function admin_palette_items(?array $user): array
{
    $items = [];
    foreach (admin_nav($user) as $group => $mods) {
        foreach ($mods as $m) {
            $items[] = ['type' => 'Go to', 'label' => $m['label'], 'hint' => $group, 'url' => '/admin/' . $m['key'], 'k' => $m['keywords']];
        }
    }
    foreach (admin_quick_actions() as $a) {
        if ($a['url'] !== null && user_can($user, $a['perm'])) {
            $items[] = ['type' => 'Action', 'label' => $a['label'], 'hint' => $a['help'], 'url' => $a['url'], 'k' => ''];
        }
    }
    return $items;
}

/** Sidebar badge counts (real queries; a failure hides the badge, it never shows 0 as fact). */
function admin_badge_count(string $badge, ?array $user): ?int
{
    static $cache = [];
    if (array_key_exists($badge, $cache)) {
        return $cache[$badge];
    }
    $sql = match ($badge) {
        'enquiries'       => "SELECT COUNT(*) FROM enquiries WHERE status = 'new'",
        'approvals'       => "SELECT (SELECT COUNT(*) FROM blog_posts WHERE status IN ('editorial_review','seo_review','business_legal_review','approved'))
                                   + (SELECT COUNT(*) FROM cms_pages WHERE workflow_status IN ('editorial_review','seo_review','business_legal_review','approved'))",
        'change_requests' => "SELECT COUNT(*) FROM change_requests WHERE status = 'pending'",
        default           => null,
    };
    if ($sql === null) {
        return $cache[$badge] = null;
    }
    try {
        return $cache[$badge] = (int) db()->query($sql)->fetchColumn();
    } catch (Throwable $e) {
        error_log('[Paynancial admin] badge ' . $badge . ' unavailable: ' . $e->getMessage());
        return $cache[$badge] = null;
    }
}
