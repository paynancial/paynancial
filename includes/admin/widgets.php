<?php
/**
 * Command Center widgets — data provenance is a hard requirement.
 *
 * Every widget is declared with:
 *   source        — where its numbers come from ("enquiries table", or null)
 *   perm          — permission needed to see it
 *   connected     — false when no data source exists (the UI then says
 *                   "Not connected" and explains why — no numbers)
 *   fn            — loads the data; throwing = error state (logged, "Retry")
 *
 * admin_widget() returns:
 *   ['key','title','source','state','updated_at','data','note', …]
 *   state: ok | empty | error | not_connected | restricted
 * A failed query can never become 0, empty or "healthy".
 */

declare(strict_types=1);

require_once __DIR__ . '/../permissions.php';
require_once __DIR__ . '/health.php';

function admin_widget_definitions(): array
{
    $stageCount = static function (): array {
        $pdo = db();
        $rows = [];
        foreach (['blog_posts' => 'status', 'cms_pages' => 'workflow_status'] as $t => $col) {
            foreach ($pdo->query("SELECT $col AS s, COUNT(*) n FROM $t WHERE $col IN ('editorial_review','seo_review','business_legal_review','approved') GROUP BY $col") as $r) {
                $rows[$r['s']] = ($rows[$r['s']] ?? 0) + (int) $r['n'];
            }
        }
        return $rows;
    };
    return [
        'kpi.content_review' => [
            'title' => 'Content Awaiting Review', 'source' => 'blog_posts + cms_pages (workflow status)', 'perm' => 'cms.view',
            'fn' => static function () use ($stageCount): array {
                $s = $stageCount();
                return ['value' => ($s['editorial_review'] ?? 0) + ($s['seo_review'] ?? 0) + ($s['business_legal_review'] ?? 0),
                    'breakdown' => ['Editorial' => $s['editorial_review'] ?? 0, 'SEO' => $s['seo_review'] ?? 0, 'Legal' => $s['business_legal_review'] ?? 0],
                    'approved' => $s['approved'] ?? 0];
            },
        ],
        'kpi.leads_today' => [
            'title' => 'New Leads Today', 'source' => 'enquiries (created today vs yesterday)', 'perm' => 'enquiries.view',
            'fn' => static function (): array {
                $pdo = db();
                $today = (int) $pdo->query('SELECT COUNT(*) FROM enquiries WHERE created_at >= CURDATE()')->fetchColumn();
                $yday = (int) $pdo->query('SELECT COUNT(*) FROM enquiries WHERE created_at >= CURDATE() - INTERVAL 1 DAY AND created_at < CURDATE()')->fetchColumn();
                $by = [];
                foreach ($pdo->query('SELECT type, COUNT(*) n FROM enquiries WHERE created_at >= CURDATE() GROUP BY type ORDER BY n DESC') as $r) {
                    $by[ucfirst($r['type'])] = (int) $r['n'];
                }
                return ['value' => $today, 'previous' => $yday, 'breakdown' => $by];
            },
        ],
        'kpi.incorporation' => [
            'title' => 'Active Incorporation Cases', 'source' => null, 'perm' => 'dashboard.view', 'connected' => false,
            'nc_title' => 'Not connected', 'note' => 'Incorporation case management is delivered in Phase 5. No case figures are shown until then.',
        ],
        'kpi.website_health' => [
            'title' => 'Website Health', 'source' => 'application health checks (System Health)', 'perm' => 'dashboard.view',
            'fn' => static function (): array {
                $checks = admin_health_checks(['app.runtime', 'app.debug', 'db.connect', 'db.schema', 'storage.disk', 'storage.writable', 'security.https', 'security.audit']);
                $pass = count(array_filter($checks, fn ($c) => $c['state'] === 'operational'));
                return ['value' => $pass, 'total' => count($checks), 'summary' => admin_health_summary($checks)];
            },
        ],
        'chart.enquiries' => [
            'title' => 'Enquiries', 'source' => 'enquiries (created_at, last 30 days)', 'perm' => 'enquiries.view',
            'fn' => static function (): array {
                $stmt = db()->query('SELECT DATE(created_at) d, COUNT(*) n FROM enquiries WHERE created_at >= CURDATE() - INTERVAL 29 DAY GROUP BY DATE(created_at)');
                return admin_daily_series($stmt->fetchAll(PDO::FETCH_KEY_PAIR), 30);
            },
        ],
        'chart.content' => [
            'title' => 'Content', 'source' => 'audit_logs (CMS workflow actions, last 30 days)', 'perm' => 'cms.view',
            'fn' => static function (): array {
                $stmt = db()->query("SELECT DATE(created_at) d, COUNT(*) n FROM audit_logs WHERE action LIKE 'cms.%' AND action NOT IN ('cms.access') AND created_at >= CURDATE() - INTERVAL 29 DAY GROUP BY DATE(created_at)");
                return admin_daily_series($stmt->fetchAll(PDO::FETCH_KEY_PAIR), 30);
            },
        ],
        'chart.traffic' => ['title' => 'Website Traffic', 'source' => null, 'perm' => 'dashboard.view', 'connected' => false, 'nc_title' => 'Analytics not connected',
            'note' => 'Connect a supported analytics source to populate this panel. No visitor numbers are shown until then.'],
        'chart.conversions' => ['title' => 'Conversions', 'source' => null, 'perm' => 'dashboard.view', 'connected' => false, 'nc_title' => 'Analytics not connected',
            'note' => 'Conversion rates need an analytics source. None is connected.'],
        'chart.incorporation' => ['title' => 'Incorporation', 'source' => null, 'perm' => 'dashboard.view', 'connected' => false, 'nc_title' => 'Not connected',
            'note' => 'Incorporation cases arrive in Phase 5.'],
        'chart.revenue' => ['title' => 'Revenue', 'source' => null, 'perm' => 'dashboard.view', 'connected' => false, 'nc_title' => 'Not connected',
            'note' => 'No payment processor or ledger feeds this admin, so no revenue is shown.'],
        'chart.seo' => ['title' => 'SEO', 'source' => null, 'perm' => 'dashboard.view', 'connected' => false, 'nc_title' => 'Search Console not connected',
            'note' => 'Impressions, clicks and CTR need Google Search Console. It is not connected.'],
        'table.top_pages' => ['title' => 'Top Performing Pages', 'source' => null, 'perm' => 'dashboard.view', 'connected' => false, 'nc_title' => 'Analytics not connected',
            'note' => 'Page views, CTR and conversions per URL need an analytics source. None is connected.'],
        'pipeline.leads' => [
            'title' => 'Leads Pipeline', 'source' => 'enquiries (status)', 'perm' => 'enquiries.view',
            'fn' => static function (): array {
                $st = array_fill_keys(['new', 'in_progress', 'responded', 'closed'], 0);
                foreach (db()->query('SELECT status, COUNT(*) n FROM enquiries GROUP BY status') as $r) {
                    $st[$r['status']] = (int) $r['n'];
                }
                return ['stages' => $st];
            },
        ],
        'pipeline.incorporation' => ['title' => 'Incorporation Pipeline', 'source' => null, 'perm' => 'dashboard.view', 'connected' => false, 'nc_title' => 'Not connected',
            'note' => 'Case stages (New → Documents → Filing → Authority Review → Incorporated) are tracked from Phase 5.'],
        'table.recent_enquiries' => [
            'title' => 'Recent Enquiries', 'source' => 'enquiries + contact_submissions + users', 'perm' => 'enquiries.view',
            'fn' => static function (): array {
                $rows = db()->query(
                    "SELECT e.id, e.enquiry_code, e.type, e.name, e.company, e.status, e.updated_at, u.full_name AS assignee,
                            (SELECT cs.form_type FROM contact_submissions cs WHERE cs.enquiry_id = e.id ORDER BY cs.id LIMIT 1) AS source
                     FROM enquiries e LEFT JOIN users u ON u.id = e.assigned_to ORDER BY e.created_at DESC LIMIT 5"
                )->fetchAll();
                $total = (int) db()->query('SELECT COUNT(*) FROM enquiries')->fetchColumn();
                return ['rows' => $rows, 'total' => $total];
            },
        ],
        'rail.environment' => [
            'title' => 'Environment Status', 'source' => 'runtime + database', 'perm' => 'dashboard.view',
            'fn' => static function (): array {
                $t = microtime(true);
                $ver = (string) db()->query('SELECT VERSION()')->fetchColumn();
                return ['env' => defined('APP_ENV') && APP_ENV === 'production' ? 'Production' : 'Development',
                    'db' => $ver, 'db_ms' => (microtime(true) - $t) * 1000, 'server' => 'PHP ' . PHP_VERSION . ' · ' . PHP_OS_FAMILY];
            },
        ],
        'rail.approvals' => [
            'title' => 'Pending Approvals', 'source' => 'blog_posts + cms_pages (workflow status)', 'perm' => 'approvals.view',
            'fn' => static fn (): array => ['stages' => $stageCount()],
        ],
        'rail.activity' => [
            'title' => 'Recent Activity', 'source' => 'audit_logs', 'perm' => 'dashboard.view',
            'fn' => static function (): array {
                $u = current_user();
                $all = user_can($u, 'activity.view');
                $sql = 'SELECT al.action, al.entity_type, al.entity_id, al.meta_json, al.created_at, us.full_name
                        FROM audit_logs al LEFT JOIN users us ON us.id = al.user_id'
                    . " WHERE al.action NOT IN ('admin.request')" . ($all ? '' : ' AND al.user_id = :u') . ' ORDER BY al.id DESC LIMIT 6';
                $stmt = db()->prepare($sql);
                $stmt->execute($all ? [] : ['u' => (int) ($u['id'] ?? 0)]);
                return ['rows' => $stmt->fetchAll(), 'scope' => $all ? 'all' : 'own'];
            },
        ],
        'rail.health' => [
            'title' => 'System Health', 'source' => 'System Health checks', 'perm' => 'dashboard.view',
            'fn' => static fn (): array => ['checks' => admin_health_checks()],
        ],
    ];
}

/** Run one widget with its guard rails. */
function admin_widget(string $key, ?array $user): array
{
    $def = admin_widget_definitions()[$key] ?? null;
    if ($def === null) {
        return ['key' => $key, 'title' => $key, 'state' => 'error', 'source' => null, 'updated_at' => null, 'data' => null];
    }
    $w = ['key' => $key, 'title' => $def['title'], 'source' => $def['source'] ?? 'not connected', 'updated_at' => date('Y-m-d H:i:s'),
        'data' => null, 'note' => $def['note'] ?? null, 'nc_title' => $def['nc_title'] ?? null];
    if (!user_can($user, $def['perm'])) {
        return $w + ['state' => 'restricted'];
    }
    if (($def['connected'] ?? true) === false) {
        return $w + ['state' => 'not_connected', 'source' => 'not connected'];
    }
    try {
        $w['data'] = ($def['fn'])();
        $w['state'] = 'ok';
    } catch (Throwable $e) {
        error_log('[Paynancial admin] widget ' . $key . ' failed: ' . $e->getMessage());
        $w['state'] = 'error';
        $w['data'] = null;
    }
    return $w;
}

/** Fill a date => count map into N consecutive days ending today. */
function admin_daily_series(array $byDate, int $days): array
{
    $out = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i day"));
        $out[$d] = (int) ($byDate[$d] ?? 0);
    }
    return ['series' => $out, 'total' => array_sum($out)];
}

/** Percentage change label, or null when there is no baseline. */
function admin_delta(int $now, int $before): ?array
{
    if ($before === 0) {
        return $now === 0 ? ['0%', 'flat', 'No change vs yesterday'] : null;
    }
    $pct = ($now - $before) / $before * 100;
    return [sprintf('%+d%%', (int) round($pct)), $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat'), 'vs yesterday (' . $before . ')'];
}

/** Accessible SVG area/line chart with focusable points (tooltips via admin.js) + a data table for screen readers. */
function admin_chart(array $series, string $label, string $unit = ''): string
{
    $w = 720;
    $h = 220;
    $pad = [16, 12, 28, 36]; // top right bottom left
    $vals = array_values($series);
    $keys = array_keys($series);
    $n = count($vals);
    $max = max(1, max($vals ?: [0]));
    $step = $max <= 4 ? 1 : (int) ceil($max / 4);
    $top = $step * 4;
    $x = fn ($i) => $pad[3] + ($n > 1 ? $i * ($w - $pad[1] - $pad[3]) / ($n - 1) : 0);
    $y = fn ($v) => $pad[0] + ($h - $pad[0] - $pad[2]) * (1 - $v / $top);
    $svg = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="' . e($label) . ' per day, last ' . $n . ' days" preserveAspectRatio="none">';
    $svg .= '<defs><linearGradient id="adm-area-grad" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#007771" stop-opacity="0.18"/><stop offset="1" stop-color="#007771" stop-opacity="0"/></linearGradient></defs>';
    for ($g = 0; $g <= 4; $g++) {
        $gy = $y($g * $step);
        $svg .= '<line class="adm-grid-line" x1="' . $pad[3] . '" x2="' . ($w - $pad[1]) . '" y1="' . $gy . '" y2="' . $gy . '"/>';
        $svg .= '<text class="adm-axis-t" x="' . ($pad[3] - 8) . '" y="' . ($gy + 4) . '" text-anchor="end">' . ($g * $step) . '</text>';
    }
    $pts = [];
    foreach ($vals as $i => $v) {
        $pts[] = round($x($i), 1) . ',' . round($y($v), 1);
    }
    if ($n) {
        $svg .= '<path class="adm-area" d="M' . $pad[3] . ',' . $y(0) . ' L' . implode(' L', $pts) . ' L' . round($x($n - 1), 1) . ',' . $y(0) . ' Z"/>';
        $svg .= '<polyline class="adm-line" points="' . implode(' ', $pts) . '"/>';
    }
    $bw = $n > 1 ? ($w - $pad[1] - $pad[3]) / ($n - 1) : 20;
    foreach ($vals as $i => $v) {
        $d = date('j M Y', strtotime($keys[$i]));
        if ($i % 7 === 0 || ($i === $n - 1 && $i % 7 >= 3)) {
            $svg .= '<text class="adm-axis-t" x="' . round($x($i), 1) . '" y="' . ($h - 8) . '" text-anchor="middle">' . e(date('j M', strtotime($keys[$i]))) . '</text>';
        }
        $svg .= '<rect class="adm-hit" tabindex="0" role="img" x="' . round($x($i) - $bw / 2, 1) . '" y="' . $pad[0] . '" width="' . round($bw, 1) . '" height="' . ($h - $pad[0] - $pad[2])
            . '" data-d="' . e($d) . '" data-v="' . e(number_format($v) . ($unit ? ' ' . $unit : '')) . '" aria-label="' . e($d . ': ' . $v . ' ' . $unit) . '"/>';
        $svg .= '<circle class="adm-pt" cx="' . round($x($i), 1) . '" cy="' . round($y($v), 1) . '" r="4"/>';
    }
    $svg .= '</svg>';
    $table = '<table class="sr-only"><caption>' . e($label) . ' per day</caption><thead><tr><th scope="col">Date</th><th scope="col">' . e(ucfirst($unit ?: 'count')) . '</th></tr></thead><tbody>';
    foreach ($series as $d => $v) {
        $table .= '<tr><td>' . e($d) . '</td><td>' . (int) $v . '</td></tr>';
    }
    return '<div class="adm-chart">' . $svg . $table . '</tbody></table></div>';
}

/** Human label for an audit action (timeline). */
function admin_action_label(string $action, array $meta): string
{
    $map = [
        'auth.login' => 'Signed in', 'auth.logout' => 'Signed out', 'auth.login_failed' => 'Failed sign-in',
        'access.denied' => 'Access denied', 'cms.create' => 'Created content', 'cms.save' => 'Edited content',
        'cms.submit' => 'Submitted for review', 'cms.pass_editorial' => 'Passed editorial review', 'cms.pass_seo' => 'Passed SEO review',
        'cms.approve' => 'Approved content', 'cms.publish' => 'Published', 'cms.unpublish' => 'Unpublished', 'cms.reject' => 'Rejected to draft',
        'cms.withdraw' => 'Withdrew to draft', 'cms.import' => 'Imported article', 'cms.access' => 'Changed CMS access',
        'role.changed' => 'Changed a user role', 'permission.granted' => 'Granted a permission', 'permission.revoked' => 'Revoked a permission',
        'customer.created' => 'Added a customer', 'enquiry.created' => 'Created an enquiry', 'enquiry.updated' => 'Updated an enquiry',
        'enquiry.bulk_updated' => 'Bulk-updated enquiries', 'enquiries.exported' => 'Exported enquiries', 'settings.changed' => 'Changed settings',
        'document.status_changed' => 'Reviewed a document',
    ];
    $txt = $map[$action] ?? ucfirst(str_replace(['.', '_'], [' ', ' '], $action));
    $title = $meta['title'] ?? $meta['page_key'] ?? $meta['name'] ?? null;
    return $title ? $txt . ' · ' . $title : $txt;
}
