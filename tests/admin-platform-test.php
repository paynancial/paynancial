<?php
/**
 * Enterprise admin platform (Phase 1) test — LOCAL / DEVELOPMENT DATABASE ONLY.
 *
 *   CMS_TEST_WRITE=1 php tests/admin-platform-test.php
 *
 * Needs config/config.php pointing at a local database with the schema,
 * seed, security, partner-hub, CMS (2026-09-25) and Phase 1 (2026-09-28)
 * migrations. Refuses APP_ENV=production. Creates users prefixed
 * adm-test- and removes them (and their audit rows) at the end.
 *
 * Covers: registry integrity, 12-role model, permission catalogue, central
 * guard (GET/POST, op-level permissions), role-aware navigation and palette,
 * audit helper (actor role, old/new diff, redaction, never throws), widgets
 * (real data, not connected, restricted, failed query ≠ 0, empty render),
 * health checks (success, failure, unavailable integration), preferences
 * allowlist, CRM customer without login, unified documents view.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}
require __DIR__ . '/../config/config.php';
if (APP_ENV === 'production' || getenv('CMS_TEST_WRITE') !== '1') {
    fwrite(STDERR, "Refusing to run: set CMS_TEST_WRITE=1 and use a non-production config.\n");
    exit(2);
}
require __DIR__ . '/../includes/database.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/security.php';
require __DIR__ . '/../includes/audit.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/admin/registry.php';
require __DIR__ . '/../includes/admin/prefs.php';
require __DIR__ . '/../includes/admin/dashboard-render.php';

$fail = 0;
$check = function (string $label, bool $ok) use (&$fail) { echo ($ok ? 'PASS ' : 'FAIL ') . $label . "\n"; $fail += $ok ? 0 : 1; };
$pdo = db();
$root = dirname(__DIR__);

/** Run PHP in a fresh process (optionally with a broken database connection). */
$fresh = function (string $code, bool $noDb = false, array $env = []) use ($root): mixed {
    $boot = $noDb
        ? "define('APP_ENV','development');define('APP_DEBUG',false);define('APP_URL','http://localhost');define('DB_HOST','127.0.0.1');define('DB_PORT','1');define('DB_NAME','x');define('DB_USER','x');define('DB_PASS','x');define('DB_CHARSET','utf8mb4');define('SESSION_COOKIE_SECURE',false);"
        : "require '$root/config/config.php';";
    $php = "<?php error_reporting(E_ALL & ~E_WARNING); $boot require '$root/includes/database.php'; require '$root/includes/functions.php'; require '$root/includes/security.php'; require '$root/includes/audit.php'; require '$root/includes/auth.php'; require '$root/includes/admin/dashboard-render.php'; echo json_encode((function () { $code })());";
    $file = tempnam(sys_get_temp_dir(), 'admt');
    file_put_contents($file, $php);
    $prefix = '';
    foreach ($env as $k => $v) {
        $prefix .= $k . '=' . escapeshellarg($v) . ' ';
    }
    $out = shell_exec($prefix . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($file) . ' 2>/dev/null');
    unlink($file);
    return json_decode((string) $out, true);
};

// ------------------------------------------------------------ registry integrity
$perms = $pdo->query('SELECT slug FROM permissions')->fetchAll(PDO::FETCH_COLUMN);
$missingFiles = $missingPerms = [];
foreach (admin_modules() as $key => $_) {
    $m = admin_module($key);
    if (!is_file("$root/admin/$key.php")) {
        $missingFiles[] = $key;
    }
    foreach (array_merge([$m['perm']], (array) $m['post']) as $p) {
        if (!in_array($p, $perms, true)) {
            $missingPerms[] = "$key:$p";
        }
    }
    if (!isset(admin_groups()[$m['group']])) {
        $missingPerms[] = "$key:group";
    }
}
$check('every registered module is implemented (file exists): ' . implode(',', $missingFiles), !$missingFiles);
$check('every module permission exists in the catalogue: ' . implode(',', $missingPerms), !$missingPerms);
$existing = array_map(fn ($f) => basename($f, '.php'), glob("$root/admin/*.php"));
$check('every existing admin page is registered (none bypasses the guard): ' . implode(',', array_diff($existing, array_keys(admin_modules()))), !array_diff($existing, array_keys(admin_modules())));
foreach (['view', 'create', 'manage', 'export', 'approve', 'publish'] as $a) {
    $check("catalogue has *.$a permissions", (bool) array_filter($perms, fn ($p) => str_ends_with($p, '.' . $a)));
}
$check('catalogue has settings (manage_settings) permission', in_array('settings.manage', $perms, true));

// ------------------------------------------------------------ roles
$roles = $pdo->query('SELECT slug FROM roles')->fetchAll(PDO::FETCH_COLUMN);
$check('12-role model present: ' . implode(',', array_diff(ADMIN_STAFF_ROLES, $roles)), count(ADMIN_STAFF_ROLES) === 12 && !array_diff(ADMIN_STAFF_ROLES, $roles));
$check('no Travel role created', !array_filter($roles, fn ($r) => str_contains($r, 'travel')));
$check('admin role is displayed as Administrator', admin_role_label('admin') === 'Administrator'
    && $pdo->query("SELECT name FROM roles WHERE slug='admin'")->fetchColumn() === 'Administrator');
$check('staff roles sign in via the staff surface and land on the Command Center',
    DASHBOARD_BY_ROLE['consultant'] === '/admin/dashboard' && DASHBOARD_BY_ROLE['support'] === '/admin/dashboard');

// ------------------------------------------------------------ fixtures
$mk = function (string $slug) use ($pdo): array {
    $email = 'adm-test-' . $slug . '@test.invalid';
    $pdo->prepare('DELETE FROM users WHERE email = ?')->execute([$email]);
    $pdo->prepare("INSERT INTO users (uuid, role_id, full_name, email, password_hash, status) SELECT UUID(), id, ?, ?, 'x', 'active' FROM roles WHERE slug = ?")
        ->execute(['Adm Test ' . $slug, $email, $slug]);
    return ['id' => (int) $pdo->lastInsertId(), 'role' => $slug, 'name' => 'Adm Test ' . $slug];
};
$u = [];
foreach (ADMIN_STAFF_ROLES as $slug) {
    $u[$slug] = $mk($slug);
}
$u['customer'] = $mk('customer');

// ------------------------------------------------------------ central guard
$expect = [
    // role, page, method, post, allowed?
    ['consultant', 'dashboard', 'GET', [], true],
    ['consultant', 'enquiries', 'GET', [], true],
    ['consultant', 'enquiries', 'POST', ['op' => 'create'], true],
    ['consultant', 'enquiries', 'POST', ['op' => 'status'], false],
    ['consultant', 'users', 'GET', [], false],
    ['consultant', 'roles', 'GET', [], false],
    ['consultant', 'system-health', 'GET', [], false],
    ['support', 'enquiries', 'POST', ['op' => 'status'], true],
    ['finance_manager', 'transactions', 'GET', [], true],
    ['finance_manager', 'enquiries', 'GET', [], false],
    ['content_manager', 'cms-articles', 'GET', [], true],
    ['content_manager', 'customers', 'GET', [], false],
    ['seo_manager', 'cms-seo', 'GET', [], true],
    ['developer', 'system-health', 'GET', [], true],
    ['developer', 'audit-logs', 'GET', [], false],
    ['compliance_reviewer', 'audit-logs', 'GET', [], true],
    ['compliance_reviewer', 'documents', 'GET', [], true],
    ['incorporation_consultant', 'documents', 'GET', [], true],
    ['incorporation_consultant', 'customers', 'POST', ['op' => 'create'], false],
    ['admin', 'roles', 'GET', [], true],
    ['admin', 'roles', 'POST', ['op' => 'assign'], false],
    ['admin', 'anti-spam', 'GET', [], true],
    ['admin', 'anti-spam', 'POST', [], false],
    ['super_admin', 'roles', 'POST', ['op' => 'assign'], true],
    ['customer', 'dashboard', 'GET', [], false],
];
foreach ($expect as [$role, $page, $method, $post, $allowed]) {
    $res = admin_guard($u[$role], $page, $method, $post);
    $check(sprintf('guard: %s %s %s%s → %s', $role, $method, $page, $post ? ' (' . ($post['op'] ?? '') . ')' : '', $allowed ? 'allowed' : 'denied'), ($res === null) === $allowed);
}
$check('guard: unknown page denied', admin_guard($u['super_admin'], 'nope', 'GET') !== null);
$check('guard: POST needs view permission too (not just the action)', admin_guard(['id' => $u['consultant']['id'], 'role' => 'consultant'], 'users', 'POST') !== null);

// ------------------------------------------------------------ role-aware navigation
$navKeys = fn (array $user) => array_merge(...array_map(fn ($g) => array_column($g, 'key'), array_values(admin_nav($user)) ?: [[]]));
$c = $navKeys($u['consultant']);
$check('consultant nav: dashboard + enquiries + customers, no users/roles/cms', in_array('enquiries', $c, true) && in_array('customers', $c, true)
    && !array_intersect(['users', 'roles', 'cms', 'audit-logs', 'system-health'], $c));
$cm = $navKeys($u['content_manager']);
$check('content manager nav: CMS modules, no enquiries/customers', in_array('cms-articles', $cm, true) && !array_intersect(['enquiries', 'customers', 'transactions'], $cm));
$check('super admin nav contains every navigable module', count($navKeys($u['super_admin'])) === count(array_filter(admin_modules(), fn ($m) => ($m['nav'] ?? true))));
$check('sub-pages and endpoints never appear in navigation', !array_intersect(['cms-article', 'cms-preview', 'search', 'widget', 'preferences'], $navKeys($u['super_admin'])));
$pal = array_column(admin_palette_items($u['consultant']), 'url');
$check('command palette offers only permitted modules/actions', in_array('/admin/enquiries?new=1', $pal, true) && !in_array('/admin/roles', $pal, true) && !in_array('/admin/cms-article/new', $pal, true));
$check('breadcrumbs from the registry', admin_breadcrumbs('cms-article', 'Edit') === [['Website & Content', null], ['Blog', '/admin/cms-articles'], ['Edit', null]]);

// ------------------------------------------------------------ audit helper
$before = (int) $pdo->query('SELECT MAX(id) FROM audit_logs')->fetchColumn();
audit('test.update', 'test_entity', 7, ['status' => 'new', 'name' => 'A', 'password' => 'hunter2'], ['status' => 'closed', 'name' => 'A', 'password' => 'x', 'api_key' => 'sk_live'],
    ['token' => 'abc', 'note' => 'n'], $pdo, ['id' => $u['admin']['id'], 'role' => 'admin']);
$row = $pdo->query("SELECT * FROM audit_logs WHERE id > $before AND action = 'test.update'")->fetch();
$meta = json_decode((string) $row['meta_json'], true);
$check('audit: user, role, action, entity recorded', (int) $row['user_id'] === $u['admin']['id'] && $row['actor_role'] === 'admin' && $row['entity_type'] === 'test_entity' && (int) $row['entity_id'] === 7);
$check('audit: only changed fields kept as old/new', ($meta['old']['status'] ?? null) === 'new' && ($meta['new']['status'] ?? null) === 'closed' && !isset($meta['new']['name']));
$check('audit: secrets redacted (password, api key, token)', !str_contains((string) $row['meta_json'], 'hunter2') && !str_contains((string) $row['meta_json'], 'sk_live')
    && !str_contains((string) $row['meta_json'], 'abc') && ($meta['token'] ?? '') === '[redacted]');
$check('audit: IP and user agent columns written', $row['ip_address'] === 'cli' && $row['user_agent'] === 'cli');
$bad = new PDO('sqlite::memory:');
$check('audit() never throws and reports failure', audit('test.fail', null, null, [], [], [], $bad) === false);
$threw = false;
try { audit_write($bad, 'test.fail'); } catch (Throwable $e) { $threw = true; }
$check('audit_write() (strict) throws so a transaction can roll back', $threw);
$check('login identifier masked', audit_mask_identifier('vikash@paynancial.com') === 'vi***@paynancial.com');

// ------------------------------------------------------------ widgets
$w = admin_widget('kpi.leads_today', $u['sales_manager']);
$today = (int) $pdo->query('SELECT COUNT(*) FROM enquiries WHERE created_at >= CURDATE()')->fetchColumn();
$check('KPI new leads = real count from enquiries', $w['state'] === 'ok' && $w['data']['value'] === $today && $w['source'] !== null);
$pdo->prepare("INSERT INTO enquiries (enquiry_code, type, name, email, message, status) VALUES ('ADM-TEST-1','sales','Adm Test','adm@test.invalid','x','new')")->execute();
$w2 = admin_widget('kpi.leads_today', $u['sales_manager']);
$check('KPI reflects a new enquiry (+1)', $w2['data']['value'] === $today + 1);
$pdo->exec("DELETE FROM enquiries WHERE enquiry_code = 'ADM-TEST-1'");
foreach (['kpi.incorporation', 'chart.traffic', 'chart.revenue', 'chart.conversions', 'chart.seo', 'table.top_pages', 'pipeline.incorporation'] as $k) {
    $x = admin_widget($k, $u['super_admin']);
    $check("$k → not connected (no data)", $x['state'] === 'not_connected' && $x['data'] === null && $x['source'] === 'not connected');
}
$check('widget restricted without permission (consultant → content KPI)', admin_widget('kpi.content_review', $u['consultant'])['state'] === 'restricted');
$fr = $fresh("\$u=['id'=>1,'role'=>'super_admin']; \$o=[]; foreach (['kpi.leads_today','kpi.content_review','chart.enquiries','pipeline.leads','rail.environment'] as \$k) { \$w=admin_widget(\$k,\$u); \$o[\$k]=[\$w['state'],\$w['data']]; } \$o['html']=admin_render_kpi(admin_widget('kpi.leads_today',\$u)); return \$o;", true);
$allError = true;
foreach (['kpi.leads_today', 'kpi.content_review', 'chart.enquiries', 'pipeline.leads', 'rail.environment'] as $k) {
    $allError = $allError && ($fr[$k][0] ?? '') === 'error' && array_key_exists(1, $fr[$k] ?? []) && $fr[$k][1] === null;
}
$check('failed query → error state with no value (never 0)', $allError);
$check('failed KPI renders "Unable to load" + Retry, not a number', str_contains((string) $fr['html'], 'Unable to load this metric') && str_contains((string) $fr['html'], 'data-widget-retry')
    && !preg_match('/<strong>\d/', (string) $fr['html']));
$empty = admin_render_recent_enquiries(['key' => 'table.recent_enquiries', 'title' => 'Recent Enquiries', 'state' => 'ok', 'source' => 'enquiries', 'updated_at' => date('Y-m-d H:i:s'), 'data' => ['rows' => [], 'total' => 0]]);
$check('empty data → empty state, not a fake row', str_contains($empty, 'No enquiries yet') && !str_contains($empty, '<table'));
$check('loading state markup (skeleton)', str_contains(adm_skeleton(), 'adm-skel') && str_contains(adm_skeleton(), 'Loading'));
$check('every widget declares a data source or not-connected', !array_filter(admin_widget_definitions(), fn ($d) => !array_key_exists('source', $d) || ($d['source'] === null && ($d['connected'] ?? true) !== false)));

// ------------------------------------------------------------ health
$h = admin_health_checks();
$by = array_column($h, 'state', 'key');
$check('health: database check operational with evidence', $by['db.connect'] === 'operational');
$check('health: uptime is never claimed (not connected)', $by['uptime'] === 'not_connected');
$check('health: payment processor not connected', $by['int.payments'] === 'not_connected');
$check('health: analytics / search console / cloudflare not connected', $by['int.analytics'] === 'not_connected' && $by['int.search_console'] === 'not_connected' && $by['int.cloudflare'] === 'not_connected');
$check('health: email without delivery tracking is not "operational"', $by['email.transport'] !== 'operational');
$fh = $fresh("\$h = admin_health_checks(['db.connect','db.schema','security.audit']); return array_column(\$h,'state','key');", true);
$check('health: failed database → not operational (unavailable)', ($fh['db.connect'] ?? '') === 'unavailable' && ($fh['security.audit'] ?? '') === 'unavailable');
$fi = $fresh("return array_column(admin_health_checks(['int.analytics']),'state','key');", false, ['GA4_PROPERTY_ID' => 'G-TEST']);
$check('health: credentials present but integration unbuilt → status unavailable', ($fi['int.analytics'] ?? '') === 'unavailable');
$check('health summary is "All systems operational" only when every check is', admin_health_summary([['state' => 'operational'], ['state' => 'unavailable']])[0] === 'unavailable'
    && admin_health_summary([['state' => 'operational']])[2] === 'All systems operational');

// ------------------------------------------------------------ preferences
$check('prefs: valid values accepted', admin_pref_clean('sidebar', 'collapsed') === 'collapsed' && admin_pref_clean('hero_collapsed', true) === true);
$check('prefs: invalid values rejected', admin_pref_clean('sidebar', '<script>') === null && admin_pref_clean('evil', 1) === null);
$check('prefs: quick actions limited to 5 known keys', admin_pref_clean('quick_actions', ['new-enquiry', 'x', 'add-customer', 'new-case', 'upload-doc', 'create-invoice', 'new-article']) === ['new-enquiry', 'add-customer', 'new-case', 'upload-doc', 'create-invoice']);
admin_pref_set($u['consultant'], 'hero_collapsed', true);
$check('prefs: saved per user', (bool) $pdo->query('SELECT COUNT(*) FROM user_preferences WHERE user_id = ' . $u['consultant']['id'])->fetchColumn());

// ------------------------------------------------------------ CRM customer without login (D4)
$pdo->prepare("INSERT INTO customers (user_id, customer_code, company_name, contact_name, contact_email, source) VALUES (NULL, 'PYN-CUS-ADMTEST', 'Adm Test Ltd', 'A', 'crm@test.invalid', 'admin')")->execute();
$cid = (int) $pdo->lastInsertId();
$check('customer can exist without a login (user_id NULL)', $pdo->query("SELECT user_id IS NULL FROM customers WHERE id = $cid")->fetchColumn() == 1);
$check('no login account was created for it', !$pdo->query("SELECT COUNT(*) FROM users WHERE email = 'crm@test.invalid'")->fetchColumn());
$pdo->exec("DELETE FROM customers WHERE id = $cid");

// ------------------------------------------------------------ documents view (D5)
foreach (['customer_kyc_documents', 'customer_application_documents', 'partner_application_documents', 'partner_documents', 'employee_documents'] as $t) {
    $check("document table $t still exists", (bool) $pdo->query("SHOW TABLES LIKE '$t'")->fetchColumn());
}
$v = $pdo->query('SELECT COUNT(*) FROM admin_documents_v')->fetchColumn();
$sum = 0;
foreach (['customer_kyc_documents', 'customer_application_documents', 'partner_application_documents', 'partner_documents', 'employee_documents'] as $t) {
    $sum += (int) $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
}
$check('unified documents view covers all five tables', (int) $v === $sum);
$statuses = $pdo->query('SELECT DISTINCT status FROM admin_documents_v')->fetchAll(PDO::FETCH_COLUMN);
$check('view statuses are normalised', !array_diff($statuses, ['pending', 'uploaded', 'under_review', 'verified', 'rejected', 'expired']));

// ------------------------------------------------------------ cleanup
$ids = implode(',', array_column($u, 'id'));
$pdo->exec("DELETE FROM audit_logs WHERE user_id IN ($ids) OR action IN ('test.update','test.fail')");
$pdo->exec("DELETE FROM user_preferences WHERE user_id IN ($ids)");
$pdo->exec("DELETE FROM users WHERE id IN ($ids)");

echo $fail ? "\n$fail check(s) FAILED\n" : "\nAll admin platform checks passed.\n";
exit($fail ? 1 : 0);
