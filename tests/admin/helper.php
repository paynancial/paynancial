<?php
/**
 * Local test helper for tests/admin/test_admin_http.py — NEVER for production.
 *
 *   php tests/admin/helper.php session <email>   # prints a signed-in session id
 *   php tests/admin/helper.php matrix <email>    # prints expected allow/deny per admin page
 *
 * "session" writes a PHP session file for a local test user (same
 * session.save_path as the local PHP dev server), so the HTTP test can act
 * as each role without the OTP login step. Refuses APP_ENV=production and
 * requires CMS_TEST_WRITE=1.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    exit;
}
require __DIR__ . '/../../config/config.php';
if (APP_ENV === 'production' || getenv('CMS_TEST_WRITE') !== '1') {
    fwrite(STDERR, "Refusing to run outside a local test environment.\n");
    exit(2);
}
require __DIR__ . '/../../includes/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../includes/admin/registry.php';

[$cmd, $email] = [$argv[1] ?? '', $argv[2] ?? ''];
$stmt = db()->prepare('SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = :e');
$stmt->execute(['e' => $email]);
$u = $stmt->fetch();
if (!$u) {
    fwrite(STDERR, "No such user\n");
    exit(1);
}
$user = ['id' => (int) $u['id'], 'uuid' => $u['uuid'], 'name' => $u['full_name'], 'email' => $u['email'], 'role' => $u['role_slug']];
if ($cmd === 'session') {
    session_name(SESSION_NAME);
    session_start();
    $_SESSION['user'] = $user;
    $_SESSION['_session_version'] = (int) $u['session_version'];
    $_SESSION['_last_activity'] = time();
    echo session_id();
} elseif ($cmd === 'matrix') {
    $out = [];
    foreach (admin_modules() as $key => $m) {
        if (($m['nav'] ?? true) === false) {
            continue;
        }
        $out[$key] = admin_guard($user, $key, 'GET') === null;
    }
    echo json_encode($out);
}
