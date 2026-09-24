<?php
/**
 * Import the version-controlled blog articles into the CMS (optional).
 *
 *   php tools/cms-import-blog.php            # dry run: lists what would be imported
 *   php tools/cms-import-blog.php --apply    # imports
 *
 * Each live general article in includes/blog/articles/ becomes a
 * blog_posts row with status Published, live = 1 and a published snapshot
 * identical to its file — so the public page, approval record, indexing and
 * sitemap entry do not change. Existing slugs and regulatory articles are
 * skipped. Every import is written to audit_logs. Requires the
 * 2026-09-25-cms-editing migration. The same import is available to
 * cms.publish holders at Admin → CMS Overview.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/content-governance.php';
require_once __DIR__ . '/../includes/cms/articles.php';

$apply = in_array('--apply', $argv, true);
$pdo = db();
$existing = $pdo->query('SELECT slug FROM blog_posts')->fetchAll(PDO::FETCH_COLUMN);
$done = 0;
foreach (blog_file_articles() as $slug => $a) {
    $reason = match (true) {
        in_array($slug, $existing, true) => 'skip (already in the CMS)',
        $a['type'] !== 'general' => 'skip (regulatory — stays version-controlled)',
        !blog_is_live($a) => 'skip (not live)',
        default => null,
    };
    if ($reason !== null) {
        echo str_pad($slug, 48) . $reason . "\n";
        continue;
    }
    if ($apply) {
        $id = cms_import_file_article($pdo, $a, null);
        echo str_pad($slug, 48) . ($id ? "imported (#$id)" : 'skipped') . "\n";
        $done += $id ? 1 : 0;
    } else {
        echo str_pad($slug, 48) . "would import\n";
    }
}
echo $apply ? "\n$done imported.\n" : "\nDry run — nothing written. Re-run with --apply to import.\n";
