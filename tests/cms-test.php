<?php
/**
 * CMS test — LOCAL / DEVELOPMENT DATABASE ONLY.
 *
 *   CMS_TEST_WRITE=1 php tests/cms-test.php
 *
 * Needs config/config.php pointing at a local database with schema.sql,
 * seed.sql, security_onboarding_schema.sql and the 2026-09-25-cms-editing
 * migration applied. Refuses to run when APP_ENV is 'production'. Creates
 * its own users and content (prefixed cms-test-) and removes all of it,
 * including its audit rows, at the end.
 *
 * Covers: HTML sanitiser, link rules, lossless editor round trip for every
 * article file, RBAC (role grants, per-person grant/revoke), every workflow
 * transition and its guards (permissions, reasons, confirmation,
 * separation of duties, stale edits), publish snapshot / edit-after-publish
 * / unpublish, public accessors (blog override, hero fallback, SEO
 * override that can only add noindex), fallback without the database, and
 * image upload validation.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}
require __DIR__ . '/../config/config.php';
if (APP_ENV === 'production' || getenv('CMS_TEST_WRITE') !== '1') {
    fwrite(STDERR, "Refusing to run: set CMS_TEST_WRITE=1 and use a non-production config.\n");
    exit(2);
}
require __DIR__ . '/../includes/database.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/security.php';
require __DIR__ . '/../includes/content-governance.php';
require __DIR__ . '/../includes/cms/articles.php';
require __DIR__ . '/../includes/cms/pages.php';
require __DIR__ . '/../includes/cms/upload.php';

$fail = 0;
$check = function (string $label, bool $ok) use (&$fail) { echo ($ok ? 'PASS ' : 'FAIL ') . $label . "\n"; $fail += $ok ? 0 : 1; };
$throws = function (callable $fn, string $needle = ''): bool {
    try { $fn(); return false; } catch (Throwable $e) { return $needle === '' || str_contains($e->getMessage(), $needle); }
};
/** Run PHP in a fresh process (public accessors cache per request). */
$fresh = function (string $code, array $env = []): mixed {
    $root = dirname(__DIR__);
    $boot = $env['NO_DB'] ?? false
        ? "define('APP_URL','http://localhost');define('DB_HOST','127.0.0.1');define('DB_PORT','1');define('DB_NAME','x');define('DB_USER','x');define('DB_PASS','x');define('DB_CHARSET','utf8mb4');"
        : "require '$root/config/config.php';";
    $php = "<?php error_reporting(E_ALL & ~E_WARNING); $boot require '$root/includes/database.php'; require '$root/includes/functions.php'; require '$root/includes/content-governance.php'; require_once '$root/includes/blog.php'; require_once '$root/includes/cms/public.php'; echo json_encode((function () { $code })());";
    $file = tempnam(sys_get_temp_dir(), 'cmst');
    file_put_contents($file, $php);
    $out = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($file) . ' 2>/dev/null');
    unlink($file);
    return json_decode((string) $out, true);
};

$pdo = db();
$pdo->exec("SET SESSION sql_mode = CONCAT(@@sql_mode, ',STRICT_TRANS_TABLES')");

// ------------------------------------------------------------ sanitiser
$dirty = '<h2 onclick="x()">A</h2><p style="color:red" class="x">Hi <a href="javascript:alert(1)">j</a> <a href="//evil.com">p</a> '
    . '<a href="https://example.org/x">ok</a> <a href="/products/refunds">in</a><img src=x onerror=alert(1)></p>'
    . '<script>alert(1)</script><style>p{}</style><iframe src="https://x"></iframe><svg onload=alert(1)></svg>'
    . '<div class="blog-callout evil">c</div><form><input></form><!-- c --><h2 id="Bad Id">B</h2><font>kept text</font>';
$clean = cms_sanitize_html($dirty);
foreach (['<script', '<style', '<iframe', '<svg', '<img', '<form', '<input', 'onclick', 'onerror', 'onload', 'style=', 'javascript:', '//evil.com', '<!--', 'class="x"', 'evil', '<font', 'Bad Id'] as $bad) {
    $check("sanitiser removes " . $bad, !str_contains($clean, $bad));
}
$check('sanitiser keeps safe links', str_contains($clean, 'href="/products/refunds"') && str_contains($clean, 'href="https://example.org/x" rel="noopener"'));
$check('sanitiser keeps allowed class', str_contains($clean, 'class="blog-callout"'));
$check('sanitiser keeps text of unknown tags', str_contains($clean, 'kept text'));
$check('safe path rules', cms_safe_path('/contact?intent=sales') !== null && cms_safe_path('//evil.com') === null
    && cms_safe_path('https://evil.com') === null && cms_safe_path('javascript:alert(1)') === null);

// ------------------------------------------------------------ editor round trip (all article files)
$lossless = true;
$norm = fn ($h) => preg_replace('/\s+/', ' ', trim($h));
foreach (blog_file_articles() as $a) {
    $rt = cms_body_to_sections(cms_sections_to_body($a['sections']));
    foreach ($a['sections'] as $i => [$id, $h2, $html]) {
        $lossless = $lossless && isset($rt[$i]) && $rt[$i][0] === $id && $rt[$i][1] === $h2 && $norm($rt[$i][2]) === $norm($html);
    }
    $lossless = $lossless && count($rt) === count($a['sections']);
}
$check('editor round trip is lossless for all ' . count(blog_file_articles()) . ' article files', $lossless);
$check('body must start with an H2', cms_body_to_sections('<p>x</p><h2>A</h2><p>y</p>')[0][0] === '__intro');
$check('reserved/duplicate section ids are made unique', array_column(cms_body_to_sections('<h2>FAQ</h2><p>a</p><h2>FAQ</h2><p>b</p>'), 0) === ['faq-2', 'faq-3']);
$check('FAQ parsing', cms_parse_faqs("Q1?\nA1.\n\nQ2?\nA2 line\nmore") === [['Q1?', 'A1.'], ['Q2?', 'A2 line more']]);

// ------------------------------------------------------------ fixtures
$roleId = fn ($slug) => (int) $pdo->query("SELECT id FROM roles WHERE slug = " . $pdo->quote($slug))->fetchColumn();
$mkUser = function (string $name, string $role) use ($pdo, $roleId): array {
    $email = 'cms-test-' . strtolower(str_replace(' ', '-', $name)) . '@test.invalid';
    $pdo->prepare('DELETE FROM users WHERE email = ?')->execute([$email]);
    $pdo->prepare("INSERT INTO users (uuid, role_id, full_name, email, password_hash, status) VALUES (UUID(), ?, ?, ?, 'x', 'active')")
        ->execute([$roleId($role), $name, $email]);
    return ['id' => (int) $pdo->lastInsertId(), 'role' => $role, 'name' => $name];
};
$grant = function (array $u, string $perm, string $effect = 'grant') use ($pdo): void {
    $pdo->prepare('INSERT INTO user_permissions (user_id, permission_id, effect) SELECT ?, id, ? FROM permissions WHERE slug = ?
                   ON DUPLICATE KEY UPDATE effect = VALUES(effect)')->execute([$u['id'], $effect, $perm]);
};
$author = $mkUser('Author', 'admin');
$reviewer = $mkUser('Reviewer', 'admin');
$approver = $mkUser('Approver', 'admin');
$revoked = $mkUser('Revoked', 'admin');
$employee = $mkUser('Employee', 'employee');
$super = $mkUser('Super', 'super_admin');
foreach (['cms.review.editorial', 'cms.review.seo'] as $p) { $grant($reviewer, $p); }
foreach (['cms.approve', 'cms.publish', 'cms.unpublish', 'cms.seo.manage'] as $p) { $grant($approver, $p); }
$grant($revoked, 'cms.edit', 'revoke');

// ------------------------------------------------------------ RBAC
$check('admin role: view/create/edit/submit', user_can($author, 'cms.view') && user_can($author, 'cms.create') && user_can($author, 'cms.edit') && user_can($author, 'cms.submit'));
$check('admin role: no review/approve/publish/unpublish/seo', !array_filter(['cms.review.editorial', 'cms.review.seo', 'cms.approve', 'cms.publish', 'cms.unpublish', 'cms.seo.manage'], fn ($p) => user_can($author, $p)));
$check('per-person grant', user_can($reviewer, 'cms.review.seo') && user_can($approver, 'cms.publish'));
$check('per-person revoke beats role grant', !user_can($revoked, 'cms.edit') && user_can($revoked, 'cms.view'));
$check('employee has no CMS permission', !user_can($employee, 'cms.view'));
$check('super admin has every permission', user_can($super, 'cms.publish') && user_can($super, 'anything.else'));
$check('no user → no permission', !user_can(null, 'cms.view'));

// ------------------------------------------------------------ article workflow
$in = [
    'title' => 'CMS test: payout timing', 'slug' => 'cms-test-payout-timing', 'category' => 'payments', 'dek' => 'Dek.',
    'question' => 'When do payouts arrive?', 'answer' => 'It depends on the bank.', 'takeaways' => "One\nTwo",
    'body' => '<h2>Timing</h2><p>Body <script>x</script></p>', 'faqs' => "Q?\nA.", 'related' => 'refunds-vs-chargebacks',
    'links' => 'Payouts | /products/payouts', 'description' => 'Meta description.', 'indexable' => '1', 'in_sitemap' => '1',
];
[$f, $err] = cms_article_input($pdo, $author, ['category' => 'regulatory'] + $in, null);
$check('regulatory topic rejected', isset($err['category']));
[$f, $err] = cms_article_input($pdo, $author, ['slug' => 'refunds-vs-chargebacks'] + $in, null);
$check('slug of an existing article file rejected', isset($err['slug']));
[$f, $err] = cms_article_input($pdo, $author, ['links' => 'X | https://evil.example'] + $in, null);
$check('off-site "related on Paynancial" link rejected', isset($err['links']));
[$f, $err] = cms_article_input($pdo, $author, $in, null);
$check('valid input accepted', $err === []);
$check('author cannot set indexing flags', $f['indexable'] === 0 && $f['in_sitemap'] === 0);
$check('employee cannot create', $throws(fn () => cms_article_save($pdo, $employee, null, $f), 'cms.create'));
$id = cms_article_save($pdo, $author, null, $f);
$row = cms_load($pdo, 'article', $id);
$check('created as draft, not live', $row['status'] === 'draft' && (int) $row['live'] === 0 && !str_contains($row['body_html'], '<script'));

$t = fn (array $u, string $a, string $reason = '', bool $ok = false) => cms_transition($pdo, $u, 'article', $id, $a, $reason, $ok, cms_article_snapshot($pdo));
$check('reviewer cannot pass a draft', $throws(fn () => $t($reviewer, 'pass_editorial'), 'Not allowed'));
$check('cannot publish a draft', $throws(fn () => $t($approver, 'publish', '', true), 'Not allowed'));
$check('author submits', $t($author, 'submit') === 'editorial_review');
$check('author cannot pass editorial (no permission)', $throws(fn () => $t($author, 'pass_editorial'), 'permission'));
$check('reject needs a reason', $throws(fn () => $t($reviewer, 'reject'), 'reason'));
$check('reject with reason → draft', $t($reviewer, 'reject', 'Tighten the answer.') === 'draft' && cms_load($pdo, 'article', $id)['review_note'] === 'Tighten the answer.');
$t($author, 'submit');
$check('editorial pass → SEO review', $t($reviewer, 'pass_editorial') === 'seo_review');
$check('approver cannot pass SEO review', $throws(fn () => $t($approver, 'pass_seo'), 'permission'));
$check('SEO pass → business/legal review', $t($reviewer, 'pass_seo') === 'business_legal_review');
$check('reviewer cannot approve', $throws(fn () => $t($reviewer, 'approve'), 'permission'));
$check('approve → approved', $t($approver, 'approve') === 'approved' && (int) cms_load($pdo, 'article', $id)['approved_by'] === $approver['id']);
$check('publish needs confirmation', $throws(fn () => $t($approver, 'publish'), 'confirm'));
$check('publish → published + live', $t($approver, 'publish', '', true) === 'published' && (int) cms_load($pdo, 'article', $id)['live'] === 1);
$snap = json_decode((string) cms_load($pdo, 'article', $id)['published_json'], true);
$check('snapshot: file shape, noindex, approval label', $snap['slug'] === 'cms-test-payout-timing' && $snap['status'] === 'published'
    && $snap['indexable'] === false && $snap['sitemap'] === false && $snap['approved_by'] === CMS_APPROVAL_LABEL && $snap['approved_by_user'] === 'Approver');

$pub = $fresh("return ['live' => blog_article('cms-test-payout-timing')['title'] ?? null, 'idx' => gov_indexable('/blog/cms-test-payout-timing'), 'sm' => gov_in_sitemap('/blog/cms-test-payout-timing')];");
$check('published article is live on the public registry', ($pub['live'] ?? null) === 'CMS test: payout timing');
$check('published without indexing → noindex, not in sitemap', $pub['idx'] === false && $pub['sm'] === false);

// Edit after publish: back to draft, snapshot stays live.
$row = cms_load($pdo, 'article', $id);
[$f2] = cms_article_input($pdo, $author, ['title' => 'CMS test: CHANGED'] + $in, $row);
cms_article_save($pdo, $author, $row, $f2);
$row = cms_load($pdo, 'article', $id);
$check('edit after publish → draft, approval cleared, still live', $row['status'] === 'draft' && $row['approved_by'] === null && (int) $row['live'] === 1);
$check('slug is fixed after publication', $row['slug'] === 'cms-test-payout-timing');
$pub = $fresh("return blog_article('cms-test-payout-timing')['title'] ?? null;");
$check('public site still shows the published snapshot', $pub === 'CMS test: payout timing');
$check('stale edit rejected', $throws(fn () => cms_article_save($pdo, $author, $row, $f2, (int) $row['lock_version'] - 1), 'changed by someone else'));

// Separation of duties and indexing.
[$f3] = cms_article_input($pdo, $approver, $in, $row);
cms_article_save($pdo, $approver, $row, $f3);
$check('cms.seo.manage can set indexing', (int) cms_load($pdo, 'article', $id)['indexable'] === 1);
$t($approver, 'submit'); $t($reviewer, 'pass_editorial'); $t($reviewer, 'pass_seo');
$check('nobody approves their own submission', $throws(fn () => $t($approver, 'approve'), 'own submission'));
$t($super, 'approve');
$check('nobody publishes their own submission', $throws(fn () => $t($approver, 'publish', '', true), 'own submission'));
$t($super, 'publish', '', true);
$pub = $fresh("return ['idx' => gov_indexable('/blog/cms-test-payout-timing'), 'sm' => gov_in_sitemap('/blog/cms-test-payout-timing')];");
$check('indexable + sitemap only after approval and publish', $pub['idx'] === true && $pub['sm'] === true);

// Unpublish.
$check('unpublish needs a reason', $throws(fn () => $t($approver, 'unpublish', '', true), 'reason'));
$check('author cannot unpublish', $throws(fn () => $t($author, 'unpublish', 'x', true), 'permission'));
$t($approver, 'unpublish', 'Test takedown', true);
$pub = $fresh("return blog_article('cms-test-payout-timing');");
$check('unpublished article is gone from the public site', $pub === null);
$check('cannot unpublish twice', $throws(fn () => $t($approver, 'unpublish', 'again', true), 'not live'));

$history = array_column(cms_history($pdo, 'article', $id), 'action');
$check('audit history records every step', !array_diff(['cms.create', 'cms.submit', 'cms.reject', 'cms.pass_editorial', 'cms.pass_seo', 'cms.approve', 'cms.publish', 'cms.save', 'cms.unpublish'], $history));

// Unpublishing an imported file article hides it (file does not reappear).
$fileSlug = array_key_first(array_filter(blog_file_articles(), fn ($a) => $a['type'] === 'general' && blog_is_live($a)));
$existingImport = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ?');
$existingImport->execute([$fileSlug]);
if ($existingImport->fetchColumn() === false) {
    $impId = cms_import_file_article($pdo, blog_file_articles()[$fileSlug], null);
    $imp = cms_load($pdo, 'article', $impId);
    $check('import: snapshot identical to the file', json_decode((string) $imp['published_json'], true) == blog_file_articles()[$fileSlug]);
    $check('import: published + live + same indexing', $imp['status'] === 'published' && (int) $imp['live'] === 1 && (int) $imp['indexable'] === 1);
    $check('import is idempotent', cms_import_file_article($pdo, blog_file_articles()[$fileSlug], null) === null);
    cms_transition($pdo, $super, 'article', $impId, 'unpublish', 'test', true);
    $check('unpublished import hides the file article', $fresh("return blog_article('$fileSlug');") === null);
    $pdo->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$impId]);
    $pdo->prepare("DELETE FROM audit_logs WHERE entity_type = 'blog_post' AND entity_id = ?")->execute([$impId]);
    $check('file article back once the CMS row is removed', $fresh("return blog_article('$fileSlug')['slug'] ?? null;") === $fileSlug);
} else {
    echo "SKIP import checks (articles already imported in this database)\n";
}

// ------------------------------------------------------------ hero
$heroBefore = cms_page_row($pdo, 'home');
$pdo->exec("UPDATE cms_pages SET live = 0 WHERE page_key = 'home'");
$check('hero falls back to the hardcoded hero', $fresh("return cms_home_hero(home_hero_defaults())['title'];") === 'Smarter Payment Infrastructure for Growing Businesses.');
[$h, $err] = cms_hero_input(['title' => 'T', 'lead' => 'L', 'primary_label' => 'P', 'primary_url' => 'https://evil.example', 'secondary_label' => 'S', 'secondary_url' => '/contact']);
$check('hero rejects off-site button links', isset($err['primary_url']));
[$h, $err] = cms_hero_input(['eyebrow' => 'E', 'title' => '<b>CMS test hero</b>', 'lead' => 'L', 'primary_label' => 'P', 'primary_url' => '/contact', 'secondary_label' => 'S', 'secondary_url' => '/pricing']);
$check('hero strips markup', $h['title'] === 'CMS test hero' && $err === []);
$hid = cms_page_save($pdo, $author, 'home', 'Home', 'hero', $h, null);
$check('draft hero is not public', $fresh("return cms_home_hero(home_hero_defaults())['title'];") !== 'CMS test hero');
$tp = fn (array $u, string $a, string $r = '', bool $ok = false) => cms_transition($pdo, $u, 'page', $hid, $a, $r, $ok, cms_page_snapshot('hero'));
$tp($author, 'submit'); $tp($reviewer, 'pass_editorial'); $tp($reviewer, 'pass_seo'); $tp($approver, 'approve'); $tp($approver, 'publish', '', true);
$heroLive = $fresh("return cms_home_hero(home_hero_defaults());");
$check('published hero is public; unset fields keep defaults', $heroLive['title'] === 'CMS test hero' && $heroLive['secondary_url'] === '/pricing');
$tp($approver, 'unpublish', 'restore', true);
$check('unpublished hero → hardcoded hero', $fresh("return cms_home_hero(home_hero_defaults())['title'];") === 'Smarter Payment Infrastructure for Growing Businesses.');

// ------------------------------------------------------------ page SEO (may only add noindex)
[$s, $err] = cms_seo_input(['meta_title' => 'CMS test title', 'robots' => 'noindex'], $author, null);
$check('without cms.seo.manage, robots stays default', $s['robots'] === 'default');
[$s, $err] = cms_seo_input(['meta_title' => 'CMS test title', 'robots' => 'index'], $approver, null);
$check('robots cannot be set to anything but default/noindex', $s['robots'] === 'default');
[$s, $err] = cms_seo_input(['meta_title' => 'CMS test title', 'robots' => 'noindex', 'og_image' => 'https://evil.example/x.png'], $approver, null);
$check('off-site social image rejected', isset($err['og_image']));
[$s, $err] = cms_seo_input(['meta_title' => 'CMS test title', 'robots' => 'noindex'], $approver, null);
$sid = cms_page_save($pdo, $approver, cms_seo_key('/pricing'), 'SEO: Pricing', 'seo', $s, null);
$ts = fn (array $u, string $a, string $r = '', bool $ok = false) => cms_transition($pdo, $u, 'page', $sid, $a, $r, $ok, cms_page_snapshot('seo'));
$ts($approver, 'submit'); $ts($reviewer, 'pass_editorial'); $ts($reviewer, 'pass_seo'); $ts($super, 'approve'); $ts($super, 'publish', '', true);
$seo = $fresh("\$_SERVER['REQUEST_URI'] = '/pricing'; return ['idx' => gov_indexable('/pricing'), 'sm' => gov_in_sitemap('/pricing'), 'meta' => cms_apply_seo(['title' => 'T'], '/pricing')];");
$check('CMS noindex removes a page from the index and the sitemap', $seo['idx'] === false && $seo['sm'] === false);
$check('CMS title override applied', ($seo['meta']['title'] ?? '') === 'CMS test title');
$ts($super, 'unpublish', 'restore', true);
$check('page indexable again after unpublish', $fresh("return gov_indexable('/pricing');") === true);
$gov = $fresh("return ['page' => isset(cms_seo_pages()['/grievance-redressal']), 'ai' => gov_indexable('/ai-intelligence/fraud-detection'), 'j' => gov_indexable('/products/payment-pages')];");
$check('governed noindex pages are not in the CMS SEO list and stay noindex', $gov['page'] === false && $gov['ai'] === false && $gov['j'] === false);
$check('a CMS override for a path outside the list is ignored', $fresh("return cms_seo_override('/legal/privacy-policy');") === null);

// ------------------------------------------------------------ fallback without a database
$nodb = $fresh("return ['n' => count(blog_live()), 'hero' => cms_home_hero(home_hero_defaults())['title'], 'idx' => gov_indexable('/about')];", ['NO_DB' => true]);
$check('no database: file articles, hardcoded hero, template SEO', ($nodb['n'] ?? 0) === count(array_filter(blog_file_articles(), 'blog_is_live'))
    && $nodb['hero'] === 'Smarter Payment Infrastructure for Growing Businesses.' && $nodb['idx'] === true);

// ------------------------------------------------------------ uploads
$tmp = sys_get_temp_dir() . '/cms-test-' . bin2hex(random_bytes(4));
mkdir($tmp);
file_put_contents("$tmp/fake.png", "<?php echo 'x'; ?>");
$check('non-image rejected', cms_image_error(['error' => UPLOAD_ERR_OK, 'size' => 20, 'tmp_name' => "$tmp/fake.png"]) !== null);
$img = imagecreatetruecolor(1200, 630);
imagepng($img, "$tmp/ok.png");
file_put_contents("$tmp/polyglot.png", file_get_contents("$tmp/ok.png") . '<?php system($_GET[1]); ?>');
$check('oversized file rejected', cms_image_error(['error' => UPLOAD_ERR_OK, 'size' => CMS_IMAGE_MAX_BYTES + 1, 'tmp_name' => "$tmp/ok.png"]) !== null);
$small = imagecreatetruecolor(50, 50);
imagepng($small, "$tmp/small.png");
$check('tiny image rejected', cms_image_error(['error' => UPLOAD_ERR_OK, 'size' => filesize("$tmp/small.png"), 'tmp_name' => "$tmp/small.png"]) !== null);
$path = cms_store_image(['error' => UPLOAD_ERR_OK, 'size' => filesize("$tmp/polyglot.png"), 'tmp_name' => "$tmp/polyglot.png"], $tmp);
$stored = file_get_contents($tmp . $path);
$check('image re-encoded: random name, appended payload dropped', preg_match('#^/uploads/cms/\d{4}/[0-9a-f]{32}\.png$#', $path) === 1 && !str_contains($stored, '<?php'));
array_map('unlink', glob("$tmp/uploads/cms/*/*") ?: []);
array_map('rmdir', glob("$tmp/uploads/cms/*") ?: []);
@rmdir("$tmp/uploads/cms"); @rmdir("$tmp/uploads");
array_map('unlink', glob("$tmp/*") ?: []);
rmdir($tmp);

// ------------------------------------------------------------ cleanup
$ids = [$author['id'], $reviewer['id'], $approver['id'], $revoked['id'], $employee['id'], $super['id']];
$in = implode(',', $ids);
$pdo->exec("DELETE FROM audit_logs WHERE entity_type = 'blog_post' AND entity_id = " . (int) $id);
$pdo->exec("DELETE FROM blog_posts WHERE id = " . (int) $id);
$pdo->exec("DELETE FROM audit_logs WHERE entity_type = 'cms_page' AND entity_id IN (" . (int) $hid . ',' . (int) $sid . ") AND user_id IN ($in)");
$pdo->exec("DELETE FROM cms_pages WHERE id = " . (int) $sid);
if ($heroBefore === null) {
    $pdo->exec("DELETE FROM cms_pages WHERE id = " . (int) $hid);
} else {
    $restore = $pdo->prepare('UPDATE cms_pages SET content_json = ?, workflow_status = ?, live = ?, review_note = ?, submitted_by = ?, submitted_at = ?,
        approved_by = ?, approved_at = ?, published_json = ?, published_at = ?, published_by = ? WHERE id = ?');
    $restore->execute([$heroBefore['content_json'], $heroBefore['workflow_status'], $heroBefore['live'], $heroBefore['review_note'],
        $heroBefore['submitted_by'], $heroBefore['submitted_at'], $heroBefore['approved_by'], $heroBefore['approved_at'],
        $heroBefore['published_json'], $heroBefore['published_at'], $heroBefore['published_by'], $heroBefore['id']]);
}
$pdo->exec("DELETE FROM user_permissions WHERE user_id IN ($in)");
$pdo->exec("DELETE FROM users WHERE id IN ($in)");

echo $fail ? "\n$fail check(s) FAILED\n" : "\nAll CMS checks passed.\n";
exit($fail ? 1 : 0);
