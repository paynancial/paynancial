<?php
// Expected authorisation for a role, from the registry + permission catalogue of the deployed commit.
[$_, $root, $email] = $argv;
require "$root/config/config.php";
if (PHP_SAPI !== 'cli' || APP_ENV === 'production') { fwrite(STDERR, "Refusing to run.\n"); exit(2); } require "$root/includes/database.php"; require "$root/includes/functions.php";
require "$root/includes/auth.php"; require "$root/includes/admin/registry.php";
$u = db()->prepare('SELECT u.id, r.slug FROM users u JOIN roles r ON r.id=u.role_id WHERE email=?'); $u->execute([$email]); $u = $u->fetch();
$user = ['id' => (int)$u['id'], 'role' => $u['slug']];
$pages = [];
foreach (admin_modules() as $k => $m) { $pages[$k] = admin_guard($user, $k, 'GET') === null; }
$perms = [];
foreach (['enquiries.view','enquiries.create','enquiries.manage','enquiries.export','customers.create','settings.manage','roles.manage','cms.view','cms.approve','cms.publish','partners.view','partners.manage','products.view','products.manage','security.view'] as $p) { $perms[$p] = user_can($user, $p); }
$nav = []; foreach (admin_nav($user) as $g) foreach ($g as $m) $nav[] = $m['key'];
echo json_encode(['pages' => $pages, 'perms' => $perms, 'nav' => $nav]);
