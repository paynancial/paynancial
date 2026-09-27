<?php
/**
 * GET /admin/widget/{key} — one Command Center widget as JSON {state, html}
 * (async loading and "Retry"). Permission per widget is enforced inside
 * admin_widget(); the route itself needs dashboard.view (registry).
 */
require_once __DIR__ . '/../includes/admin/dashboard-render.php';

$key = (string) ($route_param ?? '');
while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Cache-Control: no-store');
if (!isset(admin_widget_definitions()[$key])) {
    json_response(['state' => 'error', 'html' => adm_widget_state(['state' => 'error', 'key' => $key])], 404);
}
$w = admin_widget($key, $auth_user);
json_response(['state' => $w['state'], 'source' => $w['source'], 'updated_at' => $w['updated_at'], 'html' => admin_render_widget($w)]);
