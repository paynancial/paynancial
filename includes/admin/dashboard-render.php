<?php
/**
 * Command Center renderers. Each takes the output of admin_widget() and
 * renders ok / empty / error / not_connected / restricted honestly. Used by
 * admin/dashboard.php and by the /admin/widget/{key} endpoint (async load
 * and Retry), so both always render identically.
 */

declare(strict_types=1);

require_once __DIR__ . '/widgets.php';
require_once __DIR__ . '/ui.php';

function admin_render_widget(array $w): string
{
    return match (true) {
        str_starts_with($w['key'], 'kpi.') => admin_render_kpi($w),
        str_starts_with($w['key'], 'chart.') => admin_render_chart($w),
        $w['key'] === 'pipeline.leads', $w['key'] === 'pipeline.incorporation' => admin_render_pipeline($w),
        $w['key'] === 'table.recent_enquiries' => admin_render_recent_enquiries($w),
        $w['key'] === 'rail.environment' => admin_render_env($w),
        $w['key'] === 'rail.approvals' => admin_render_approvals($w),
        $w['key'] === 'rail.activity' => admin_render_activity($w),
        $w['key'] === 'rail.health' => admin_render_health($w),
        default => adm_widget_state($w),
    };
}

function admin_render_kpi(array $w): string
{
    [$icon, $tone] = match ($w['key']) {
        'kpi.content_review' => ['pen', 'purple'],
        'kpi.leads_today' => ['inbox', 'blue'],
        'kpi.incorporation' => ['briefcase', 'yellow'],
        default => ['heart', 'green'],
    };
    $out = '<article class="adm-kpi" data-widget-box aria-labelledby="' . e($w['key']) . '-t">';
    $out .= '<div class="adm-kpi-h"><span class="adm-kpi-ic adm-kpi-ic--' . $tone . '">' . adm_icon($icon, 17) . '</span><span id="' . e($w['key']) . '-t">' . e($w['title']) . '</span></div>';
    if ($w['state'] !== 'ok') {
        $label = match ($w['state']) {
            'not_connected' => 'Not connected', 'restricted' => 'No access', 'error' => 'Unavailable', default => 'Unavailable',
        };
        $out .= '<div class="adm-kpi-v"><span class="adm-kpi-na">' . e($label) . '</span></div>';
        $out .= match ($w['state']) {
            'error' => '<p class="adm-kpi-foot">Unable to load this metric. <button type="button" class="adm-btn adm-btn--sm adm-btn--ghost" data-widget-retry="' . e($w['key']) . '">' . adm_icon('refresh', 13) . 'Retry</button></p>',
            'not_connected' => '<p class="adm-kpi-foot">' . e($w['note'] ?? '') . '</p>',
            'restricted' => '<p class="adm-kpi-foot">Not available for your role.</p>',
            default => '',
        };
        return $out . '</article>';
    }
    $d = $w['data'];
    if ($w['key'] === 'kpi.website_health') {
        [$state, $pillTone, $text] = $d['summary'];
        $out .= '<div class="adm-kpi-v"><strong>' . (int) $d['value'] . '<span class="adm-kpi-na"> / ' . (int) $d['total'] . '</span></strong>' . adm_pill($text === 'All systems operational' ? 'Healthy' : adm_state_meta($state)[0], $pillTone) . '</div>';
        $out .= '<ul class="adm-kpi-break"><li>Application checks passing</li><li>Uptime <b>Not connected</b></li><li>Crawl data <b>Not connected</b></li></ul>';
        $out .= '<p class="adm-kpi-foot">' . adm_icon('database', 12) . ' Live checks · ' . e(date('H:i', strtotime($w['updated_at']))) . '</p>';
        return $out . '</article>';
    }
    $out .= '<div class="adm-kpi-v"><strong>' . number_format((int) $d['value']) . '</strong>';
    if ($w['key'] === 'kpi.leads_today' && ($delta = admin_delta((int) $d['value'], (int) $d['previous']))) {
        $out .= '<span class="adm-delta adm-delta--' . $delta[1] . '" title="' . e($delta[2]) . '">' . e($delta[0]) . '<span class="sr-only"> ' . e($delta[2]) . '</span></span>';
    }
    $out .= '</div>';
    if (!empty($d['breakdown'])) {
        $out .= '<ul class="adm-kpi-break">';
        foreach ($d['breakdown'] as $k => $v) {
            $out .= '<li><b>' . (int) $v . '</b> ' . e($k) . '</li>';
        }
        $out .= '</ul>';
    } elseif ($w['key'] === 'kpi.leads_today') {
        $out .= '<ul class="adm-kpi-break"><li>No enquiries yet today</li></ul>';
    }
    $foot = $w['key'] === 'kpi.content_review' ? ((int) ($d['approved'] ?? 0)) . ' approved, awaiting publication' : ($w['key'] === 'kpi.leads_today' ? 'Yesterday: ' . (int) $d['previous'] : '');
    $out .= '<p class="adm-kpi-foot">' . e($foot) . '</p>';
    return $out . '</article>';
}

function admin_render_chart(array $w): string
{
    if ($w['state'] !== 'ok') {
        return adm_widget_state($w) . ($w['state'] === 'not_connected' ? '' : adm_source($w));
    }
    $s = $w['data']['series'];
    if (!$w['data']['total']) {
        return adm_widget_state(['state' => 'empty'] + $w, 'No ' . strtolower($w['title']) . ' recorded in the last 30 days.') . adm_source($w);
    }
    $unit = $w['key'] === 'chart.enquiries' ? 'enquiries' : 'actions';
    $peak = array_search(max($s), $s, true);
    $out = '<div class="adm-chart-sum"><div><strong>' . number_format($w['data']['total']) . '</strong>Last 30 days</div>'
        . '<div><strong>' . number_format(array_sum(array_slice($s, -7))) . '</strong>Last 7 days</div>'
        . '<div><strong>' . e(date('j M', strtotime((string) $peak))) . '</strong>Busiest day (' . (int) max($s) . ')</div></div>';
    return $out . admin_chart($s, $w['title'], $unit) . adm_source($w);
}

function admin_render_pipeline(array $w): string
{
    if ($w['key'] === 'pipeline.incorporation') {
        $stages = ['New', 'Documents', 'Filing', 'Authority Review', 'Incorporated'];
        $out = '<ol class="adm-pipe" aria-label="Incorporation stages">';
        foreach ($stages as $s) {
            $out .= '<li class="adm-pipe-st"><span>' . e($s) . '</span><strong class="adm-na">—</strong></li>';
        }
        return $out . '</ol>' . adm_widget_state($w);
    }
    if ($w['state'] !== 'ok') {
        return adm_widget_state($w);
    }
    $labels = ['new' => 'New', 'in_progress' => 'In progress', 'responded' => 'Responded', 'closed' => 'Closed'];
    $colors = ['new' => '#2A55B8', 'in_progress' => '#7A5300', 'responded' => '#5B3DB5', 'closed' => '#146C3A'];
    $st = $w['data']['stages'];
    $total = array_sum($st);
    $out = '<ol class="adm-pipe" style="grid-template-columns:repeat(4,minmax(0,1fr))" aria-label="Enquiries by status">';
    foreach ($labels as $k => $label) {
        $out .= '<li class="adm-pipe-st"><span>' . e($label) . '</span><strong>' . number_format($st[$k] ?? 0) . '</strong><i style="background:' . $colors[$k] . '"></i></li>';
    }
    $out .= '</ol><div class="adm-pipe-bar" aria-hidden="true">';
    foreach ($labels as $k => $_) {
        if ($total && ($st[$k] ?? 0)) {
            $out .= '<span style="width:' . round(($st[$k] / $total) * 100, 2) . '%;background:' . $colors[$k] . '"></span>';
        }
    }
    return $out . '</div><p class="adm-pipe-note">By enquiry status. Qualification stages (Qualified → Proposal → Negotiation → Won) arrive with the CRM in Phase 4.</p>' . adm_source($w);
}

function admin_enquiry_status_pill(string $s): string
{
    return match ($s) {
        'new' => adm_pill('New', 'blue'), 'in_progress' => adm_pill('In progress', 'yellow'),
        'responded' => adm_pill('Responded', 'purple'), 'closed' => adm_pill('Closed', 'green'), default => adm_pill(ucfirst($s), 'grey'),
    };
}

function admin_enquiry_source_label(?string $s): string
{
    return match ($s) {
        null, '' => 'Direct / admin', 'callback' => 'Callback widget', 'contact' => 'Contact form', default => ucwords(str_replace(['_', '-'], ' ', $s)),
    };
}

function admin_render_recent_enquiries(array $w): string
{
    if ($w['state'] !== 'ok') {
        return adm_widget_state($w);
    }
    $rows = $w['data']['rows'];
    if (!$rows) {
        return adm_widget_state(['state' => 'empty'] + $w, 'No enquiries yet', user_can(current_user(), 'enquiries.create') ? 'New Enquiry' : null, '/admin/enquiries?new=1');
    }
    $out = '<div class="adm-table-wrap"><table class="adm-table" id="dash-enq"><caption class="sr-only">Five most recent enquiries</caption><thead><tr>'
        . '<th scope="col" class="adm-col-check"><input type="checkbox" data-select-all="dash-enq" aria-label="Select all"></th>'
        . '<th scope="col">ID</th><th scope="col">Name / Company</th><th scope="col">Type</th><th scope="col">Source</th><th scope="col">Status</th><th scope="col">Assigned</th><th scope="col">Updated</th><th scope="col" class="adm-col-actions"><span class="sr-only">Actions</span></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $id = (int) $r['id'];
        $out .= '<tr><td class="adm-col-check"><input type="checkbox" data-row-check value="' . $id . '" aria-label="Select ' . e($r['enquiry_code']) . '"></td>'
            . '<td><span class="adm-id">' . e($r['enquiry_code']) . '</span></td>'
            . '<td><strong>' . e($r['name']) . '</strong>' . ($r['company'] ? '<span class="adm-row-sub">' . e($r['company']) . '</span>' : '') . '</td>'
            . '<td>' . e(ucfirst($r['type'])) . '</td><td>' . e(admin_enquiry_source_label($r['source'])) . '</td>'
            . '<td>' . admin_enquiry_status_pill($r['status']) . '</td><td>' . e($r['assignee'] ?? 'Unassigned') . '</td><td>' . adm_ago($r['updated_at']) . '</td>'
            . '<td class="adm-col-actions"><div class="adm-rowmenu"><button type="button" class="adm-icon-btn" data-pop="dq-' . $id . '" aria-expanded="false" aria-controls="dq-' . $id . '" aria-label="Actions for ' . e($r['enquiry_code']) . '">' . adm_icon('dots', 18) . '</button>'
            . '<div class="adm-pop" id="dq-' . $id . '" hidden><a class="adm-menu-item" href="/admin/enquiries?q=' . e(rawurlencode($r['enquiry_code'])) . '">' . adm_icon('eye', 15) . 'Open in Enquiries</a></div></div></td></tr>';
    }
    $out .= '</tbody></table></div>';
    $out .= '<div class="adm-bulk" data-bulk-for="dash-enq"><span data-bulk-count>0 selected</span><span class="adm-muted">Bulk status changes are in Enquiries.</span>'
        . '<a class="adm-btn adm-btn--sm" href="/admin/enquiries">Open Enquiries</a></div>';
    return $out . adm_source($w);
}

function admin_render_env(array $w): string
{
    if ($w['state'] !== 'ok') {
        return adm_widget_state($w);
    }
    $d = $w['data'];
    return '<dl class="adm-kv">'
        . '<div><dt>Environment</dt><dd>' . adm_pill($d['env'], $d['env'] === 'Production' ? 'green' : 'blue') . '</dd></div>'
        . '<div><dt>Application</dt><dd>' . adm_pill('Responding', 'green') . '</dd></div>'
        . '<div><dt>Uptime</dt><dd>' . adm_pill('Not connected', 'grey') . '</dd></div>'
        . '<div><dt>Last deployment</dt><dd>' . adm_pill('Status unavailable', 'grey') . '</dd></div>'
        . '<div><dt>Server</dt><dd title="' . e($d['server']) . '">' . e($d['server']) . '</dd></div>'
        . '<div><dt>Database</dt><dd title="' . e($d['db']) . '">' . e(sprintf('%.1f ms', $d['db_ms'])) . '</dd></div>'
        . '</dl>';
}

function admin_render_approvals(array $w): string
{
    if ($w['state'] !== 'ok') {
        return adm_widget_state($w);
    }
    $s = $w['data']['stages'];
    $rows = ['editorial_review' => 'Editorial Review', 'seo_review' => 'SEO Review', 'business_legal_review' => 'Legal / Business Review', 'approved' => 'Approved — awaiting publish'];
    $out = '<ul class="adm-appr">';
    foreach ($rows as $k => $label) {
        $n = (int) ($s[$k] ?? 0);
        $out .= '<li><span>' . e($label) . '</span><b' . ($n ? ' style="background:var(--a-yellow-bg);color:var(--a-yellow)"' : '') . '>' . $n . '</b></li>';
    }
    return $out . '</ul>';
}

function admin_render_activity(array $w): string
{
    if ($w['state'] !== 'ok') {
        return adm_widget_state($w);
    }
    if (!$w['data']['rows']) {
        return adm_widget_state(['state' => 'empty'] + $w, 'No activity recorded yet.');
    }
    $out = '<ol class="adm-tl">';
    foreach ($w['data']['rows'] as $r) {
        $meta = json_decode((string) $r['meta_json'], true) ?: [];
        $out .= '<li><div><strong>' . e(admin_action_label($r['action'], $meta)) . '</strong><span>' . e($r['full_name'] ?? 'System') . ' · ' . adm_ago($r['created_at']) . '</span></div></li>';
    }
    return $out . '</ol>' . ($w['data']['scope'] === 'own' ? '<p class="adm-source">Showing your own activity.</p>' : '');
}

function admin_render_health(array $w): string
{
    if ($w['state'] !== 'ok') {
        return adm_widget_state($w);
    }
    $checks = $w['data']['checks'];
    [$state, $tone, $text] = admin_health_summary(array_filter($checks, fn ($c) => !in_array($c['group'], ['External integrations', 'Cache'], true) && $c['key'] !== 'uptime' && $c['key'] !== 'security.monitoring'));
    $out = '<div class="adm-overall adm-overall--' . $tone . '">' . adm_icon(adm_state_meta($state)[2], 15) . e($text) . '</div><ul class="adm-health">';
    foreach (['Website' => 'globe', 'Database' => 'database', 'File Storage' => 'server', 'Email Service' => 'mail', 'Security Monitoring' => 'shield'] as $g => $icon) {
        $out .= '<li><span>' . adm_icon($icon, 15) . e($g) . '</span>' . adm_state_pill(admin_health_group_state($checks, $g)) . '</li>';
    }
    return $out . '</ul><p class="adm-source">' . adm_icon('clock', 12) . 'Checked ' . e(date('H:i:s')) . ' · uptime monitor not connected</p>';
}
