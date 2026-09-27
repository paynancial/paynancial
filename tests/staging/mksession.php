<?php
// STAGING ONLY: signed-in session for a staging test user (file sessions on the same host). Refuses production.
[$_, $root, $email] = $argv;
require "$root/config/config.php";
if (PHP_SAPI !== 'cli' || APP_ENV === 'production') { fwrite(STDERR, "Refusing to run.\n"); exit(2); }
require "$root/includes/database.php";
$u = db()->prepare('SELECT u.*, r.slug role_slug FROM users u JOIN roles r ON r.id=u.role_id WHERE email=?'); $u->execute([$email]); $u = $u->fetch();
session_name(SESSION_NAME); session_start();
$_SESSION['user'] = ['id' => (int)$u['id'], 'uuid' => $u['uuid'], 'name' => $u['full_name'], 'email' => $u['email'], 'role' => $u['role_slug']];
$_SESSION['_session_version'] = (int)($u['session_version'] ?? 1); $_SESSION['_last_activity'] = time();
echo session_id();
