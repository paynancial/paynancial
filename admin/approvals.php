<?php
/**
 * Admin: Approvals inbox — every CMS item in review or approved-awaiting-
 * publication, with whether the viewer can take the next step. Actions
 * themselves happen in the item's editor (workflow checks are server-side).
 */
require_once __DIR__ . '/../includes/cms/workflow.php';
require_once __DIR__ . '/../includes/cms/public.php';
require_once __DIR__ . '/../includes/admin/ui.php';

$page_meta = ['title' => 'Approvals | Paynancial Admin', 'own_head' => true];
$pdo = db();
$mineOnly = ($_GET['view'] ?? 'mine') !== 'all';
$items = [];
$error = false;
try {
    $stages = "('editorial_review','seo_review','business_legal_review','approved')";
    foreach ($pdo->query("SELECT b.*, u.full_name AS submitter FROM blog_posts b LEFT JOIN users u ON u.id = b.submitted_by WHERE b.status IN $stages") as $r) {
        $items[] = ['Article', $r['title'], $r['status'], $r, 'article', '/admin/cms-article/' . (int) $r['id']];
    }
    foreach ($pdo->query("SELECT c.*, u.full_name AS submitter FROM cms_pages c LEFT JOIN users u ON u.id = c.submitted_by WHERE c.workflow_status IN $stages") as $r) {
        $hero = $r['page_key'] === 'home';
        $path = substr((string) $r['page_key'], 4);
        $items[] = [$hero ? 'Homepage hero' : 'Page SEO', $hero ? 'Homepage hero' : (cms_seo_pages()[$path] ?? $path), $r['workflow_status'], $r, 'page',
            $hero ? '/admin/cms-hero' : '/admin/cms-seo?path=' . rawurlencode($path)];
    }
} catch (Throwable $e) {
    error_log('[Paynancial admin] approvals unavailable: ' . $e->getMessage());
    $error = true;
}
foreach ($items as &$it) {
    $it[] = (bool) array_intersect(cms_available_actions($auth_user, $it[4], $it[3]), ['pass_editorial', 'pass_seo', 'approve', 'publish']);
}
unset($it);
$mine = array_filter($items, fn ($i) => $i[6]);
$list = $mineOnly ? $mine : $items;
usort($list, fn ($a, $b) => strcmp((string) ($a[3]['submitted_at'] ?? ''), (string) ($b[3]['submitted_at'] ?? '')));

echo adm_page_head('Approvals', 'Content waiting for editorial, SEO/AEO or business/legal review, or for publication.');
?>
<section class="adm-card">
  <div class="adm-tabs" style="margin-bottom:16px">
    <a class="adm-tab" href="/admin/approvals?view=mine" <?= $mineOnly ? 'aria-current="page" style="border:1.5px solid var(--a-accent);background:var(--a-accent-50);color:#00524d"' : '' ?>>Needs my action <span class="adm-tab-count"><?= count($mine) ?></span></a>
    <a class="adm-tab" href="/admin/approvals?view=all" <?= !$mineOnly ? 'aria-current="page" style="border:1.5px solid var(--a-accent);background:var(--a-accent-50);color:#00524d"' : '' ?>>All pending <span class="adm-tab-count"><?= count($items) ?></span></a>
  </div>
  <?php if ($error): ?>
    <?= adm_widget_state(['state' => 'error', 'key' => '']) ?>
  <?php elseif (!$list): ?>
    <div class="adm-state"><?= adm_icon('check', 20) ?><div><strong><?= $mineOnly ? 'Nothing needs your action' : 'Nothing is pending' ?></strong><p>Items appear here when they are submitted for review.</p></div></div>
  <?php else: ?>
  <div class="adm-table-wrap"><table class="adm-table">
    <thead><tr><th scope="col">Type</th><th scope="col">Item</th><th scope="col">Stage</th><th scope="col">Submitted by</th><th scope="col">Waiting</th><th scope="col">Your next step</th><th scope="col" class="adm-col-actions"><span class="sr-only">Open</span></th></tr></thead>
    <tbody>
    <?php foreach ($list as [$type, $title, $stage, $row, $kind, $url, $canAct]): ?>
      <tr>
        <td><?= e($type) ?></td>
        <td><strong><?= e($title) ?></strong></td>
        <td><?= adm_pill(cms_statuses()[$stage] ?? $stage, $stage === 'approved' ? 'blue' : 'yellow') ?></td>
        <td><?= e($row['submitter'] ?? '—') ?></td>
        <td><?= adm_ago($row['submitted_at'] ?? null) ?></td>
        <td><?= $canAct ? adm_pill('You can act', 'green') : adm_pill('Waiting on another reviewer', 'grey') ?></td>
        <td class="adm-col-actions"><a class="adm-btn adm-btn--sm" href="<?= e($url) ?>">Open</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
  <p class="adm-source"><?= adm_icon('database', 12) ?>Source: blog_posts + cms_pages workflow status. Nobody outside super admin can pass or approve their own submission.</p>
</section>
