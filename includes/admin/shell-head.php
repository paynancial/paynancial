<?php
/**
 * Enterprise admin shell (admin area only — the customer, partner, employee
 * and HRMS portals keep includes/dashboard-head.php unchanged).
 * Expects: $auth_user, $dashboard_page, optional $page_meta.
 */
require_once __DIR__ . '/registry.php';
require_once __DIR__ . '/ui.php';
require_once __DIR__ . '/prefs.php';

$page_meta = $page_meta ?? [];
$page_meta['title'] = $page_meta['title'] ?? 'Paynancial Admin';
$page_meta['robots'] = 'noindex, nofollow';
$adm_prefs = admin_prefs($auth_user);
$adm_nav = admin_nav($auth_user);
$adm_module = admin_module($dashboard_page ?? 'dashboard');
$adm_active = $adm_module['parent'] ?? $dashboard_page;
$adm_crumbs = admin_breadcrumbs($dashboard_page ?? 'dashboard', $page_meta['crumb'] ?? null);
$adm_role = (string) ($auth_user['role'] ?? '');
$adm_role_label = admin_role_label($adm_role);
$adm_name = (string) ($auth_user['name'] ?? 'User');
$adm_initials = strtoupper(implode('', array_map(fn ($p) => mb_substr($p, 0, 1), array_slice(preg_split('/\s+/', trim($adm_name)) ?: ['U'], 0, 2))));
$adm_env = defined('APP_ENV') && APP_ENV === 'production' ? 'Production' : 'Development';

// Environment status: only what this request can actually prove (the database answered).
$adm_env_ok = null;
try {
    db()->query('SELECT 1')->fetchColumn();
    $adm_env_ok = true;
} catch (Throwable $e) {
    $adm_env_ok = false;
}

// Notifications for this user (real rows; unavailable if the query fails).
$adm_notes = null;
$adm_unread = null;
try {
    $stmt = db()->prepare('SELECT title, body, is_read, created_at FROM notifications WHERE user_id = :u ORDER BY created_at DESC LIMIT 8');
    $stmt->execute(['u' => (int) $auth_user['id']]);
    $adm_notes = $stmt->fetchAll();
    $cnt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :u AND is_read = 0');
    $cnt->execute(['u' => (int) $auth_user['id']]);
    $adm_unread = (int) $cnt->fetchColumn();
} catch (Throwable $e) {
    error_log('[Paynancial admin] notifications unavailable: ' . $e->getMessage());
}
$adm_collapsed = ($adm_prefs['sidebar'] ?? 'expanded') === 'collapsed';
?><!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($page_meta['title']) ?></title>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" type="image/png" href="<?= asset('images/paynancial-icon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/main.css') ?>">
<link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= asset('admin/admin.css') ?>">
</head>
<body class="adm" data-sidebar="<?= $adm_collapsed ? 'collapsed' : 'expanded' ?>">
<a class="adm-skip" href="#adm-main">Skip to content</a>
<div class="adm-shell">
  <aside class="adm-side" id="adm-side" aria-label="Admin navigation">
    <div class="adm-brand">
      <a href="/admin/dashboard" class="adm-brand-link" aria-label="Paynancial — Command Center">
        <img src="<?= asset('images/paynancial-logo-dark-bg.png') ?>" alt="Paynancial" class="adm-brand-logo" width="132" height="30">
        <img src="<?= asset('images/paynancial-icon.png') ?>" alt="" class="adm-brand-icon" width="28" height="28">
      </a>
      <span class="adm-brand-tag">Enterprise</span>
      <button type="button" class="adm-side-close" data-side-close aria-label="Close navigation"><?= adm_icon('x', 18) ?></button>
    </div>
    <div class="adm-side-search" role="search">
      <label for="adm-nav-filter" class="sr-only">Filter navigation</label>
      <?= adm_icon('search', 15) ?>
      <input type="search" id="adm-nav-filter" placeholder="Find a module" autocomplete="off" data-nav-filter>
    </div>
    <nav class="adm-nav" aria-label="Modules">
      <?php foreach ($adm_nav as $group => $mods):
          $gkey = array_search($group, admin_groups(), true);
          $open = $adm_prefs['nav_groups'][$gkey] ?? true;
          $hasActive = (bool) array_filter($mods, fn ($m) => $m['key'] === $adm_active);
          $open = $open || $hasActive;
      ?>
      <div class="adm-grp" data-group="<?= e($gkey) ?>">
        <button type="button" class="adm-grp-h" aria-expanded="<?= $open ? 'true' : 'false' ?>" aria-controls="grp-<?= e($gkey) ?>">
          <span><?= e($group) ?></span><?= adm_icon('chevron-down', 14, 'adm-grp-chev') ?>
        </button>
        <ul id="grp-<?= e($gkey) ?>" <?= $open ? '' : 'hidden' ?>>
          <?php foreach ($mods as $m):
              $count = $m['badge'] ? admin_badge_count($m['badge'], $auth_user) : null; ?>
          <li><a href="/admin/<?= e($m['key']) ?>" class="adm-nav-link<?= $m['key'] === $adm_active ? ' is-active' : '' ?>"
                 <?= $m['key'] === $adm_active ? 'aria-current="page"' : '' ?> data-nav-item data-k="<?= e(strtolower($m['label'] . ' ' . $group . ' ' . $m['keywords'])) ?>" title="<?= e($m['label']) ?>">
              <?= adm_icon($m['icon'], 18) ?><span class="adm-nav-label"><?= e($m['label']) ?></span>
              <?php if ($count): ?><span class="adm-nav-badge" aria-label="<?= $count ?> pending"><?= $count > 99 ? '99+' : $count ?></span><?php endif; ?>
          </a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
      <p class="adm-nav-empty" data-nav-empty hidden>No module matches.</p>
    </nav>
    <div class="adm-side-foot">
      <button type="button" class="adm-side-toggle" data-side-toggle aria-pressed="<?= $adm_collapsed ? 'true' : 'false' ?>" aria-label="Collapse sidebar">
        <?= adm_icon('sidebar', 18) ?><span class="adm-nav-label">Collapse</span>
      </button>
    </div>
  </aside>
  <div class="adm-overlay" data-side-close hidden></div>

  <div class="adm-main">
    <header class="adm-top">
      <button type="button" class="adm-icon-btn adm-menu-btn" data-side-open aria-label="Open navigation" aria-controls="adm-side"><?= adm_icon('menu', 20) ?></button>
      <nav class="adm-crumbs" aria-label="Breadcrumb"><ol>
        <?php foreach ($adm_crumbs as $i => [$label, $href]): ?>
          <li><?php if ($href): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><span<?= $i === count($adm_crumbs) - 1 ? ' aria-current="page"' : '' ?>><?= e($label) ?></span><?php endif; ?></li>
        <?php endforeach; ?>
      </ol></nav>
      <button type="button" class="adm-search-trigger" data-cmdk-open aria-haspopup="dialog" aria-label="Search anything (Command K)">
        <?= adm_icon('search', 16) ?><span>Search anything…</span><kbd class="adm-kbd" data-kbd-mod>⌘K</kbd>
      </button>
      <div class="adm-top-right">
        <div class="adm-pop-wrap">
          <button type="button" class="adm-env" data-pop="adm-env-pop" aria-expanded="false" aria-controls="adm-env-pop">
            <span class="adm-env-dot adm-env-dot--<?= $adm_env_ok === true ? 'ok' : ($adm_env_ok === false ? 'bad' : 'na') ?>" aria-hidden="true"></span>
            <?= e($adm_env) ?><?= adm_icon('chevron-down', 14) ?>
          </button>
          <div class="adm-pop" id="adm-env-pop" hidden>
            <p class="adm-pop-title">Environment</p>
            <p class="adm-pop-row"><span>Environment</span><strong><?= e($adm_env) ?></strong></p>
            <p class="adm-pop-row"><span>Application</span><?= $adm_env_ok ? adm_pill('Responding', 'green') : adm_pill('Database unreachable', 'coral') ?></p>
            <p class="adm-pop-row"><span>Uptime monitor</span><?= adm_pill('Not connected', 'grey') ?></p>
            <p class="adm-pop-note">This deployment has one environment. Status reflects checks run for this request only.<?php if (user_can($auth_user, 'system.health.view')): ?> <a href="/admin/system-health">Open System Health</a><?php endif; ?></p>
          </div>
        </div>
        <div class="adm-pop-wrap">
          <button type="button" class="adm-icon-btn" data-pop="adm-notes-pop" aria-expanded="false" aria-controls="adm-notes-pop"
                  aria-label="Notifications<?= $adm_unread ? ', ' . $adm_unread . ' unread' : '' ?>">
            <?= adm_icon('bell', 19) ?><?php if ($adm_unread): ?><span class="adm-dot-count"><?= $adm_unread > 9 ? '9+' : $adm_unread ?></span><?php endif; ?>
          </button>
          <div class="adm-pop adm-pop--wide" id="adm-notes-pop" hidden>
            <p class="adm-pop-title">Notifications</p>
            <?php if ($adm_notes === null): ?><p class="adm-pop-note">Notifications are unavailable right now.</p>
            <?php elseif (!$adm_notes): ?><p class="adm-pop-note">You're all caught up.</p>
            <?php else: foreach ($adm_notes as $n): ?>
              <div class="adm-note<?= $n['is_read'] ? '' : ' is-unread' ?>"><strong><?= e($n['title']) ?></strong><span><?= e(mb_strimwidth((string) $n['body'], 0, 110, '…')) ?></span><?= adm_ago($n['created_at']) ?></div>
            <?php endforeach; endif; ?>
          </div>
        </div>
        <div class="adm-pop-wrap">
          <button type="button" class="adm-icon-btn" data-pop="adm-help-pop" aria-expanded="false" aria-controls="adm-help-pop" aria-label="Help"><?= adm_icon('help', 19) ?></button>
          <div class="adm-pop" id="adm-help-pop" hidden>
            <p class="adm-pop-title">Keyboard shortcuts</p>
            <p class="adm-pop-row"><span>Command palette</span><kbd class="adm-kbd" data-kbd-mod>⌘K</kbd></p>
            <p class="adm-pop-row"><span>Find a module</span><kbd class="adm-kbd">/</kbd></p>
            <p class="adm-pop-row"><span>Close dialogs</span><kbd class="adm-kbd">Esc</kbd></p>
            <p class="adm-pop-note">Access is permission-based. If a module is missing, ask a super admin.</p>
          </div>
        </div>
        <div class="adm-pop-wrap">
          <button type="button" class="adm-user" data-pop="adm-user-pop" aria-expanded="false" aria-controls="adm-user-pop" aria-label="Account: <?= e($adm_name) ?>, <?= e($adm_role_label) ?>">
            <span class="adm-avatar" aria-hidden="true"><?= e($adm_initials) ?></span>
            <span class="adm-user-txt"><strong><?= e($adm_name) ?></strong><span><?= e($adm_role_label) ?></span></span>
            <?= adm_icon('chevron-down', 14) ?>
          </button>
          <div class="adm-pop" id="adm-user-pop" hidden>
            <p class="adm-pop-title"><?= e($adm_name) ?></p>
            <p class="adm-pop-note"><?= e($auth_user['email'] ?? '') ?><br><?= e($adm_role_label) ?></p>
            <a class="adm-menu-item" href="/employee/profile" <?= in_array($adm_role, ['admin', 'super_admin'], true) ? '' : 'hidden' ?>><?= adm_icon('users', 16) ?>Profile &amp; security</a>
            <button type="button" class="adm-menu-item" data-logout><?= adm_icon('logout', 16) ?>Log out</button>
          </div>
        </div>
      </div>
    </header>
    <main class="adm-content" id="adm-main" tabindex="-1">
<?php if (empty($page_meta['own_head'])): ?>
      <div class="adm-head"><div><h1 class="adm-h1"><?= e($page_meta['heading'] ?? ($adm_module['label'] ?? 'Admin')) ?></h1></div></div>
<?php endif; ?>
