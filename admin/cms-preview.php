<?php
/**
 * Admin: preview the working copy of an article or the homepage hero with
 * the public templates. Admin-only (dashboard route), never cached, never
 * indexed, and clearly bannered as a preview.
 */
require_once __DIR__ . '/../includes/cms/admin-ui.php';

$pdo = db();
if (!user_can($auth_user, 'cms.view')) {
    cms_no_access();
    return;
}
$type = (string) ($_GET['type'] ?? '');
$body = null;
$page_meta = [];

if ($type === 'article') {
    $row = cms_load($pdo, 'article', (int) ($_GET['id'] ?? 0));
    if ($row) {
        require_once __DIR__ . '/../includes/business-services-ui.php';
        $blog_article = cms_article_array($pdo, $row);
        ob_start();
        include __DIR__ . '/../pages/blog/article.php';
        $body = ob_get_clean();
        $label = 'Article preview — ' . (cms_statuses()[$row['status']] ?? $row['status']) . ' working copy, not the published version';
        $back = '/admin/cms-article/' . (int) $row['id'];
    }
} elseif ($type === 'hero') {
    $row = cms_page_row($pdo, 'home');
    $draft = cms_page_draft($row, 'hero');
    if ($draft) {
        require_once __DIR__ . '/../includes/cta-context.php';
        $GLOBALS['cms_preview_hero'] = $draft;
        ob_start();
        include __DIR__ . '/../pages/home.php';
        $body = ob_get_clean();
        $label = 'Homepage hero preview — ' . (cms_statuses()[$row['workflow_status']] ?? '') . ' working copy';
        $back = '/admin/cms-hero';
    }
}

if ($body === null) {
    echo '<div class="panel"><p>Nothing to preview. <a href="/admin/cms">Back to CMS</a></p></div>';
    return;
}

// Replace the dashboard shell with the public page shell.
while (ob_get_level() > 0) {
    ob_end_clean();
}
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
$page_meta['robots'] = 'noindex, nofollow';
include __DIR__ . '/../includes/site-head.php';
echo '<div role="status" style="position:sticky;top:0;z-index:1000;background:#7a4a00;color:#fff;padding:10px 16px;font:600 14px/1.4 Inter,sans-serif;text-align:center;">'
    . 'PREVIEW — ' . e($label) . ' · <a href="' . e($back) . '" style="color:#fff;text-decoration:underline;">Back to the editor</a></div>';
include __DIR__ . '/../includes/header.php';
echo '<main id="main-content">' . $body . '</main>';
include __DIR__ . '/../includes/footer.php';
include __DIR__ . '/../includes/site-foot.php';
exit;
