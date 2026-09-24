<?php
/**
 * Admin: CMS overview — workflow queue, content status, file-article import
 * and CMS access (per-person permissions). See includes/cms/.
 */
require_once __DIR__ . '/../includes/cms/admin-ui.php';

$page_meta = ['title' => 'CMS | Paynancial Admin', 'heading' => 'Content Management'];
$pdo = db();

if (!user_can($auth_user, 'cms.view')) {
    cms_no_access();
    return;
}

$cmsPerms = $pdo->query("SELECT id, slug, name FROM permissions WHERE module = 'cms' AND slug <> 'cms.manage' ORDER BY id")->fetchAll();
$isSuper = ($auth_user['role'] ?? '') === 'super_admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        cms_redirect('/admin/cms', null, 'Your session expired. Please try again.');
    }
    $op = (string) ($_POST['op'] ?? '');
    try {
        if ($op === 'import') {
            require_permission($auth_user, 'cms.publish');
            if (empty($_POST['confirm'])) {
                throw new RuntimeException('Please tick the confirmation box first.');
            }
            $n = 0;
            foreach (blog_file_articles() as $a) {
                $n += cms_import_file_article($pdo, $a, (int) $auth_user['id']) !== null ? 1 : 0;
            }
            cms_redirect('/admin/cms', $n . ' article' . ($n === 1 ? '' : 's') . ' imported. Their live content, approval and indexing are unchanged.');
        }
        if ($op === 'access') {
            if (!$isSuper) {
                throw new CmsDenied('Only a super admin can change CMS access.');
            }
            $uid = (int) ($_POST['user_id'] ?? 0);
            $permId = (int) ($_POST['permission_id'] ?? 0);
            $effect = (string) ($_POST['effect'] ?? '');
            $target = $pdo->prepare("SELECT u.id, u.full_name, r.slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id AND r.slug = 'admin'");
            $target->execute(['id' => $uid]);
            $who = $target->fetch();
            $perm = array_values(array_filter($cmsPerms, fn ($p) => (int) $p['id'] === $permId))[0] ?? null;
            if (!$who || !$perm || !in_array($effect, ['grant', 'revoke', 'inherit'], true)) {
                throw new RuntimeException('Invalid access change.');
            }
            if ($effect === 'inherit') {
                $pdo->prepare('DELETE FROM user_permissions WHERE user_id = :u AND permission_id = :p')->execute(['u' => $uid, 'p' => $permId]);
            } else {
                $pdo->prepare('INSERT INTO user_permissions (user_id, permission_id, effect) VALUES (:u, :p, :e)
                               ON DUPLICATE KEY UPDATE effect = VALUES(effect)')->execute(['u' => $uid, 'p' => $permId, 'e' => $effect]);
            }
            cms_audit($pdo, (int) $auth_user['id'], 'cms.access', 'user', $uid, ['permission' => $perm['slug'], 'effect' => $effect, 'user' => $who['full_name']]);
            cms_redirect('/admin/cms#access', 'Access updated: ' . $perm['slug'] . ' → ' . $effect . ' for ' . $who['full_name'] . '.');
        }
    } catch (CmsDenied | RuntimeException $e) {
        cms_redirect('/admin/cms', null, $e->getMessage());
    }
}

$counts = array_fill_keys(array_keys(cms_statuses()), 0);
foreach ($pdo->query('SELECT status, COUNT(*) n FROM blog_posts GROUP BY status') as $r) {
    $counts[$r['status']] = (int) $r['n'];
}
$liveCms = (int) $pdo->query('SELECT COUNT(*) FROM blog_posts WHERE live = 1')->fetchColumn();
$dbSlugs = $pdo->query('SELECT slug FROM blog_posts')->fetchAll(PDO::FETCH_COLUMN);
$fileOnly = array_filter(blog_file_articles(), fn ($a) => !in_array($a['slug'], $dbSlugs, true) && $a['type'] === 'general' && blog_is_live($a));
$hero = cms_page_row($pdo, 'home');
$seoRows = $pdo->query("SELECT page_key, workflow_status, live FROM cms_pages WHERE page_key LIKE 'seo:%'")->fetchAll();

// Queue: items this person can move forward right now.
$queue = [];
foreach ($pdo->query("SELECT * FROM blog_posts WHERE status IN ('editorial_review','seo_review','business_legal_review','approved') ORDER BY updated_at") as $r) {
    if (array_intersect(cms_available_actions($auth_user, 'article', $r), ['pass_editorial', 'pass_seo', 'approve', 'publish'])) {
        $queue[] = ['Article', $r['title'], $r['status'], '/admin/cms-article/' . $r['id']];
    }
}
foreach ($pdo->query("SELECT * FROM cms_pages WHERE workflow_status IN ('editorial_review','seo_review','business_legal_review','approved')") as $r) {
    if (array_intersect(cms_available_actions($auth_user, 'page', $r), ['pass_editorial', 'pass_seo', 'approve', 'publish'])) {
        $isHero = $r['page_key'] === 'home';
        $path = substr($r['page_key'], 4);
        $queue[] = [$isHero ? 'Homepage hero' : 'Page SEO', $isHero ? 'Homepage hero' : (cms_seo_pages()[$path] ?? $path),
            $r['workflow_status'], $isHero ? '/admin/cms-hero' : '/admin/cms-seo?path=' . rawurlencode($path)];
    }
}
$mine = user_permission_slugs($auth_user);
?>
<?php cms_flash_messages(); ?>

<div class="stat-grid">
  <div class="stat-card"><div class="label">CMS articles live</div><div class="value"><?= $liveCms ?></div></div>
  <div class="stat-card"><div class="label">File articles (not in CMS)</div><div class="value"><?= count($fileOnly) ?></div></div>
  <div class="stat-card"><div class="label">In review</div><div class="value"><?= $counts['editorial_review'] + $counts['seo_review'] + $counts['business_legal_review'] ?></div></div>
  <div class="stat-card"><div class="label">Approved, not published</div><div class="value"><?= $counts['approved'] ?></div></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Awaiting your action</h2>
    <div class="toolbar">
      <?php if (user_can($auth_user, 'cms.create')): ?><a class="btn btn-primary btn-sm" href="/admin/cms-article/new">New article</a><?php endif; ?>
      <a class="btn btn-outline btn-sm" href="/admin/cms-articles">All articles</a>
      <a class="btn btn-outline btn-sm" href="/admin/cms-hero">Homepage hero</a>
      <a class="btn btn-outline btn-sm" href="/admin/cms-seo">Page SEO</a>
    </div>
  </div>
  <?php if (!$queue): ?>
    <p class="text-muted cms-small">Nothing is waiting for a review, approval or publication step you can take.</p>
  <?php else: ?>
  <div class="data-table-wrap"><table class="data-table">
    <thead><tr><th>Type</th><th>Item</th><th>Stage</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($queue as [$t, $title, $st, $href]): ?>
      <tr><td><?= e($t) ?></td><td><?= e($title) ?></td><td><?= cms_status_badges($st, false) ?></td><td><a href="<?= e($href) ?>">Open →</a></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>

<div class="panel">
  <div class="panel-head"><h2>Content</h2></div>
  <div class="data-table-wrap"><table class="data-table">
    <thead><tr><th>Area</th><th>Status</th><th>Public site shows</th><th></th></tr></thead>
    <tbody>
      <tr><td>Blog articles (CMS)</td>
        <td><?php foreach ($counts as $st => $n): if ($n): ?><span class="badge <?= cms_status_badge($st) ?>"><?= e(cms_statuses()[$st]) ?>: <?= $n ?></span> <?php endif; endforeach; ?><?= array_sum($counts) ? '' : '<span class="text-muted">None yet</span>' ?></td>
        <td><?= $liveCms ?> CMS snapshot<?= $liveCms === 1 ? '' : 's' ?> + <?= count($fileOnly) ?> version-controlled file<?= count($fileOnly) === 1 ? '' : 's' ?></td>
        <td><a href="/admin/cms-articles">Manage →</a></td></tr>
      <tr><td>Homepage hero</td>
        <td><?= $hero && $hero['workflow_status'] ? cms_status_badges($hero['workflow_status'], (int) $hero['live'] === 1) : '<span class="badge neutral">Not edited</span>' ?></td>
        <td><?= $hero && (int) $hero['live'] === 1 ? 'Published CMS hero' : 'Hardcoded hero (fallback)' ?></td>
        <td><a href="/admin/cms-hero">Edit →</a></td></tr>
      <tr><td>Page SEO</td>
        <td><?= count($seoRows) ?> page<?= count($seoRows) === 1 ? '' : 's' ?> with a CMS draft or override</td>
        <td><?= count(array_filter($seoRows, fn ($r) => (int) $r['live'] === 1)) ?> published override(s); all others use template SEO</td>
        <td><a href="/admin/cms-seo">Manage →</a></td></tr>
    </tbody>
  </table></div>
  <p class="text-muted cms-small cms-scope">Outside the CMS (version-controlled, reviewed code changes): header, footer, URLs, sitemap architecture, canonical and robots strategy, Regulatory Insights articles (source-first), foreign-jurisdiction pages (research pending, noindex), Privacy Policy and Terms (legal review). The CMS can add <code>noindex</code> to a page but can never make a governed page indexable.</p>
</div>

<?php if (user_can($auth_user, 'cms.publish')): ?>
<div class="panel">
  <div class="panel-head"><h2>Import version-controlled articles</h2></div>
  <?php if (!$fileOnly): ?>
    <p class="text-muted cms-small">Every live general article is already in the CMS.</p>
  <?php else: ?>
  <p class="cms-small">Brings <?= count($fileOnly) ?> live article<?= count($fileOnly) === 1 ? '' : 's' ?> from <code>includes/blog/articles/</code> into the CMS so they can be edited through the workflow. Each is stored as <strong>Published</strong> with a snapshot identical to its file — the public page, approval record (<?= e(CMS_APPROVAL_LABEL) ?>), indexing and sitemap entry do not change. Regulatory articles are never imported.</p>
  <form method="post" class="cms-action">
    <?= csrf_field() ?><input type="hidden" name="op" value="import">
    <label class="cms-check"><input type="checkbox" name="confirm" value="1" required> From now on, the CMS copy of these articles is what the site shows (later edits to the files will not appear).</label>
    <button type="submit" class="btn btn-outline btn-sm">Import <?= count($fileOnly) ?> article<?= count($fileOnly) === 1 ? '' : 's' ?></button>
  </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="panel" id="access">
  <div class="panel-head"><h2>Your CMS permissions</h2></div>
  <p class="cms-small"><?php if ($isSuper): ?>Super admin — all CMS permissions.<?php else: ?>
    <?php foreach ($cmsPerms as $p): ?><span class="badge <?= in_array($p['slug'], $mine, true) ? 'success' : 'neutral' ?>"><?= e($p['slug']) ?></span> <?php endforeach; ?>
  <?php endif; ?></p>
</div>

<?php if ($isSuper):
    $admins = $pdo->query("SELECT u.id, u.full_name, u.email FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'admin' ORDER BY u.full_name")->fetchAll();
    $roleGrants = $pdo->query("SELECT p.id FROM role_permissions rp JOIN roles r ON r.id = rp.role_id JOIN permissions p ON p.id = rp.permission_id WHERE r.slug = 'admin' AND p.module = 'cms'")->fetchAll(PDO::FETCH_COLUMN);
    $overrides = [];
    foreach ($pdo->query("SELECT up.user_id, up.permission_id, up.effect FROM user_permissions up JOIN permissions p ON p.id = up.permission_id WHERE p.module = 'cms'") as $o) {
        $overrides[$o['user_id']][$o['permission_id']] = $o['effect'];
    }
?>
<div class="panel">
  <div class="panel-head"><h2>CMS access (admins)</h2><span class="text-muted cms-small">Role default: view, create, edit, submit</span></div>
  <?php if (!$admins): ?><p class="text-muted cms-small">No admin users.</p><?php else: ?>
  <div class="data-table-wrap"><table class="data-table cms-access">
    <thead><tr><th>Admin</th><?php foreach ($cmsPerms as $p): ?><th title="<?= e($p['name']) ?>"><?= e(substr($p['slug'], 4)) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
    <?php foreach ($admins as $u): ?>
      <tr><td><?= e($u['full_name']) ?><div class="cms-small text-muted"><?= e($u['email']) ?></div></td>
      <?php foreach ($cmsPerms as $p):
          $ov = $overrides[$u['id']][$p['id']] ?? null;
          $has = $ov === 'grant' || ($ov === null && in_array($p['id'], $roleGrants));
      ?>
        <td>
          <form method="post" action="/admin/cms#access">
            <?= csrf_field() ?><input type="hidden" name="op" value="access">
            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>"><input type="hidden" name="permission_id" value="<?= (int) $p['id'] ?>">
            <select name="effect" class="js-auto-submit" aria-label="<?= e($p['slug'] . ' for ' . $u['full_name']) ?>">
              <option value="inherit" <?= $ov === null ? 'selected' : '' ?>><?= in_array($p['id'], $roleGrants) ? 'Role (yes)' : 'Role (no)' ?></option>
              <option value="grant" <?= $ov === 'grant' ? 'selected' : '' ?>>Grant</option>
              <option value="revoke" <?= $ov === 'revoke' ? 'selected' : '' ?>>Revoke</option>
            </select>
            <noscript><button type="submit" class="btn btn-sm btn-outline">Set</button></noscript>
          </form>
          <span class="badge <?= $has ? 'success' : 'neutral' ?> cms-small"><?= $has ? 'Yes' : 'No' ?></span>
        </td>
      <?php endforeach; ?></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
<?php endif; ?>
