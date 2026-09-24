<?php
/** Admin: CMS blog articles — search, filter, sort. File-only articles are listed read-only. */
require_once __DIR__ . '/../includes/cms/admin-ui.php';

$page_meta = ['title' => 'Articles | Paynancial CMS', 'heading' => 'Blog Articles'];
$pdo = db();

if (!user_can($auth_user, 'cms.view')) {
    cms_no_access();
    return;
}

$q = cms_text($_GET['q'] ?? '', 100);
$status = (string) ($_GET['status'] ?? '');
$cat = (string) ($_GET['category'] ?? '');
$sorts = ['updated' => 'updated_at DESC', 'title' => 'title ASC', 'status' => 'FIELD(status,\'editorial_review\',\'seo_review\',\'business_legal_review\',\'approved\',\'draft\',\'published\'), updated_at DESC'];
$sort = isset($sorts[$_GET['sort'] ?? '']) ? $_GET['sort'] : 'updated';

$sql = 'SELECT id, slug, title, category, status, live, indexable, in_sitemap, source, updated_at FROM blog_posts WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (title LIKE :q OR slug LIKE :q2)';
    $params['q'] = '%' . $q . '%';
    $params['q2'] = '%' . $q . '%';
}
if (isset(cms_statuses()[$status])) {
    $sql .= ' AND status = :st';
    $params['st'] = $status;
} elseif ($status === 'live') {
    $sql .= ' AND live = 1';
}
if (isset(blog_categories()[$cat])) {
    $sql .= ' AND category = :cat';
    $params['cat'] = $cat;
}
$stmt = $pdo->prepare($sql . ' ORDER BY ' . $sorts[$sort] . ' LIMIT 500');
$stmt->execute($params);
$rows = $stmt->fetchAll();

$dbSlugs = $pdo->query('SELECT slug FROM blog_posts')->fetchAll(PDO::FETCH_COLUMN);
$fileOnly = array_filter(blog_file_articles(), function ($a) use ($dbSlugs, $q, $cat, $status) {
    return !in_array($a['slug'], $dbSlugs, true)
        && ($q === '' || stripos($a['title'] . ' ' . $a['slug'], $q) !== false)
        && ($cat === '' || $a['category'] === $cat)
        && ($status === '' || $status === 'live' && blog_is_live($a));
});
?>
<?php cms_flash_messages(); ?>
<div class="panel">
  <div class="panel-head"><h2>Articles</h2>
    <?php if (user_can($auth_user, 'cms.create')): ?><a class="btn btn-primary btn-sm" href="/admin/cms-article/new">New article</a><?php endif; ?>
  </div>
  <form method="get" class="toolbar" style="margin-bottom:18px;" role="search">
    <label class="sr-only" for="cms-q">Search</label>
    <input type="search" id="cms-q" name="q" value="<?= e($q) ?>" placeholder="Search title or URL">
    <label class="sr-only" for="cms-st">Status</label>
    <select id="cms-st" name="status">
      <option value="">All statuses</option>
      <option value="live" <?= $status === 'live' ? 'selected' : '' ?>>Live on site</option>
      <?php foreach (cms_statuses() as $k => $label): ?><option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
    </select>
    <label class="sr-only" for="cms-cat">Topic</label>
    <select id="cms-cat" name="category">
      <option value="">All topics</option>
      <?php foreach (blog_categories() as $k => [$label]): ?><option value="<?= e($k) ?>" <?= $cat === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
    </select>
    <label class="sr-only" for="cms-sort">Sort</label>
    <select id="cms-sort" name="sort">
      <option value="updated" <?= $sort === 'updated' ? 'selected' : '' ?>>Recently updated</option>
      <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title A–Z</option>
      <option value="status" <?= $sort === 'status' ? 'selected' : '' ?>>Needs action first</option>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Apply</button>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Title</th><th>Topic</th><th>Status</th><th>Indexing</th><th>Source</th><th>Updated</th></tr></thead>
      <tbody>
      <?php if (!$rows && !$fileOnly): ?><tr><td colspan="6"><div class="empty-state">No articles match.</div></td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><a href="/admin/cms-article/<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a><div class="cms-small text-muted">/blog/<?= e($r['slug']) ?></div></td>
          <td><?= e(blog_categories()[$r['category']][0] ?? '—') ?></td>
          <td><?= cms_status_badges($r['status'], (int) $r['live'] === 1) ?></td>
          <td><?= (int) $r['indexable'] === 1 ? 'Indexable' . ((int) $r['in_sitemap'] === 1 ? ' · sitemap' : '') : 'noindex' ?></td>
          <td><?= $r['source'] === 'file_import' ? 'Imported file' : 'CMS' ?></td>
          <td><?= e(substr((string) $r['updated_at'], 0, 16)) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php foreach ($fileOnly as $a): ?>
        <tr class="cms-row-file">
          <td><a href="/blog/<?= e($a['slug']) ?>" target="_blank" rel="noopener"><?= e($a['title']) ?></a><div class="cms-small text-muted">/blog/<?= e($a['slug']) ?></div></td>
          <td><?= e(blog_categories()[$a['category']][0] ?? '—') ?></td>
          <td><span class="badge neutral"><?= e(blog_statuses()[$a['status']] ?? $a['status']) ?></span><?= blog_is_live($a) ? ' <span class="badge success">Live</span>' : '' ?></td>
          <td><?= $a['status'] === 'indexable' && $a['indexable'] ? 'Indexable' . ($a['sitemap'] ? ' · sitemap' : '') : 'noindex' ?></td>
          <td>File (read-only<?= $a['type'] === 'regulatory' ? ', regulatory' : '' ?>)</td>
          <td><?= e($a['updated']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($fileOnly): ?><p class="text-muted cms-small">File articles are version-controlled. To edit one in the CMS, import it first (CMS overview → Import).</p><?php endif; ?>
</div>
