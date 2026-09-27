<?php
/**
 * POST /admin/preferences — save one UI preference for the signed-in user
 * (JSON {key, value}; CSRF via X-CSRF-Token, checked centrally). Only the
 * allowlisted keys/values in includes/admin/prefs.php are accepted.
 */
require_once __DIR__ . '/../includes/admin/prefs.php';

while (ob_get_level() > 0) {
    ob_end_clean();
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}
$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in) || !is_string($in['key'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Invalid request'], 400);
}
try {
    $ok = admin_pref_set($auth_user, $in['key'], $in['value'] ?? null);
} catch (Throwable $e) {
    error_log('[Paynancial admin] preference not saved: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Preference could not be saved'], 503);
}
json_response(['ok' => $ok], $ok ? 200 : 422);
