<?php
/**
 * Admin: per-page SEO — title, description, social tags and an optional
 * noindex. Canonical URLs and the index/noindex decisions of governed pages
 * are fixed in code; the CMS can only ever ADD noindex.
 */
require_once __DIR__ . '/../includes/cms/admin-ui.php';
require_once __DIR__ . '/../includes/content-governance.php';

$page_meta = ['title' => 'Page SEO | Paynancial CMS', 'heading' => 'Page SEO'];
$pdo = db();
if (!user_can($auth_user, 'cms.view')) {
    cms_no_access();
    return;
}
$pages = cms_seo_pages();
$path = (string) ($_GET['path'] ?? '');

if ($path === '' || !isset($pages[$path])):
    $rows = [];
    foreach ($pdo->query("SELECT page_key, workflow_status, live FROM cms_pages WHERE page_key LIKE 'seo:%'") as $r) {
        $rows[substr($r['page_key'], 4)] = $r;
    }
?>
<?php cms_flash_messages(); ?>
<p class="cms-crumbs"><a href="/admin/cms">CMS</a> / Page SEO</p>
<div class="panel">
  <div class="panel-head"><h2>Pages</h2></div>
  <div class="data-table-wrap"><table class="data-table">
    <thead><tr><th>Page</th><th>Indexing (code)</th><th>CMS override</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p => $label): $r = $rows[$p] ?? null; $govIdx = gov_item($p) === null || (gov_item($p)['indexable'] === true && gov_approved(gov_item($p))); ?>
      <tr>
        <td><?= e($label) ?><div class="cms-small text-muted"><?= e($p) ?></div></td>
        <td><?= $govIdx ? 'Indexable' : '<span class="badge pending">Governed · noindex</span>' ?></td>
        <td><?= $r && $r['workflow_status'] ? cms_status_badges($r['workflow_status'], (int) $r['live'] === 1) : '<span class="text-muted">Template SEO</span>' ?></td>
        <td><a href="/admin/cms-seo?path=<?= e(rawurlencode($p)) ?>">Edit →</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <p class="text-muted cms-small">Blog articles have their own SEO fields in the article editor. Legal pages (Privacy Policy, Terms) and foreign-jurisdiction pages are not editable here.</p>
</div>
<?php
    return;
endif;

$key = cms_seo_key($path);
$self = '/admin/cms-seo?path=' . rawurlencode($path);
$row = cms_page_row($pdo, $key);
$canEdit = user_can($auth_user, 'cms.edit');
$canSeo = user_can($auth_user, 'cms.seo.manage');
$gov = gov_item($path);
$governedNoindex = $gov !== null && !($gov['indexable'] === true && gov_approved($gov));
$errors = [];
$input = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        cms_redirect($self, null, 'Your session expired. Your changes were not saved — please try again.');
    }
    if (($_POST['op'] ?? '') === 'transition' && $row) {
        cms_handle_transition($pdo, $auth_user, 'page', (int) $row['id'], $self, cms_page_snapshot('seo'));
    }
    if (($_POST['op'] ?? '') === 'save') {
        $input = $_POST;
        try {
            if (!empty($_FILES['og_upload']['name'])) {
                if (!$canEdit) {
                    throw new CmsDenied('You do not have permission to edit.');
                }
                $input['og_image'] = cms_store_image($_FILES['og_upload']);
            }
            [$seo, $errors] = cms_seo_input($input, $auth_user, cms_page_draft($row, 'seo'));
            if (!$errors) {
                cms_page_save($pdo, $auth_user, $key, 'SEO: ' . $pages[$path], 'seo', $seo, isset($_POST['lock']) ? (int) $_POST['lock'] : null);
                cms_redirect($self, 'Saved as draft.');
            }
        } catch (CmsDenied | RuntimeException $e) {
            $errors['_'] = $e->getMessage();
        }
    }
}
$v = $input ?? cms_page_draft($row, 'seo') ?? [];
$status = $row['workflow_status'] ?? null;
$dis = $canEdit ? [] : ['disabled' => true];
?>
<?php cms_flash_messages(); ?>
<?php if (!empty($errors['_'])): ?><div class="cms-alert cms-alert--failed" role="alert"><?= e($errors['_']) ?></div><?php endif; ?>
<?php if ($errors && empty($errors['_'])): ?><div class="cms-alert cms-alert--failed" role="alert">Not saved — please fix the highlighted fields.</div><?php endif; ?>
<p class="cms-crumbs"><a href="/admin/cms">CMS</a> / <a href="/admin/cms-seo">Page SEO</a> / <?= e($pages[$path]) ?></p>

<div class="cms-editor">
  <form method="post" action="<?= e($self) ?>" enctype="multipart/form-data" class="cms-form" data-cms-dirty>
    <?= csrf_field() ?><input type="hidden" name="op" value="save">
    <?php if ($row): ?><input type="hidden" name="lock" value="<?= (int) $row['lock_version'] ?>"><?php endif; ?>
    <div class="panel">
      <div class="panel-head"><h2><?= e($pages[$path]) ?> <span class="text-muted cms-small"><?= e($path) ?></span></h2><?= $status ? cms_status_badges($status, (int) $row['live'] === 1) : '<span class="badge neutral">Template SEO</span>' ?></div>
      <p class="cms-small text-muted">Leave a field blank to keep the page template's value. Canonical URL: <code><?= e(site_url(ltrim($path, '/'))) ?></code> (fixed).</p>
      <?php cms_field('meta_title', 'Meta title', $v['meta_title'] ?? '', CMS_SEO_LIMITS['meta_title'], ['error' => $errors['meta_title'] ?? null] + $dis); ?>
      <?php cms_field('meta_description', 'Meta description', $v['meta_description'] ?? '', CMS_SEO_LIMITS['meta_description'], ['type' => 'textarea', 'rows' => 2] + $dis); ?>
      <div class="cms-serp" aria-hidden="true">
        <span class="cms-serp-url"><?= e(parse_url(site_url(ltrim($path, '/')), PHP_URL_HOST) . ($path === '/' ? '' : ' › ' . trim(str_replace('/', ' › ', $path), ' ›'))) ?></span>
        <span class="cms-serp-title" data-serp="title"><?= e(($v['meta_title'] ?? '') ?: 'Template title') ?></span>
        <span class="cms-serp-desc" data-serp="desc"><?= e(($v['meta_description'] ?? '') ?: 'Template description') ?></span>
      </div>
      <?php cms_field('og_title', 'Social title', $v['og_title'] ?? '', CMS_SEO_LIMITS['og_title'], $dis); ?>
      <?php cms_field('og_description', 'Social description', $v['og_description'] ?? '', CMS_SEO_LIMITS['og_description'], ['type' => 'textarea', 'rows' => 2] + $dis); ?>
      <?php cms_field('og_image', 'Social image path', $v['og_image'] ?? '', [0, 255], ['error' => $errors['og_image'] ?? null, 'placeholder' => '/uploads/cms/…'] + $dis); ?>
      <?php if ($canEdit): ?><div class="cms-field"><label for="f-og-upload">…or upload an image</label><input type="file" id="f-og-upload" name="og_upload" accept="image/jpeg,image/png,image/webp"><small class="cms-help">JPEG, PNG or WebP, up to 2 MB.</small></div><?php endif; ?>
      <fieldset class="cms-fieldset" <?= $canSeo && $canEdit ? '' : 'disabled' ?>>
        <legend>Robots</legend>
        <label class="cms-check"><input type="radio" name="robots" value="default" <?= ($v['robots'] ?? 'default') !== 'noindex' ? 'checked' : '' ?>> Default — follow the site's indexing rules<?= $governedNoindex ? ' (this page is governed: noindex)' : '' ?></label>
        <label class="cms-check"><input type="radio" name="robots" value="noindex" <?= ($v['robots'] ?? '') === 'noindex' ? 'checked' : '' ?>> noindex — remove this page from search results and the sitemap</label>
        <small class="cms-help">The CMS can only add noindex. It cannot make a governed or noindex page indexable.<?= $canSeo ? '' : ' Changing robots requires cms.seo.manage.' ?></small>
      </fieldset>
    </div>
    <?php if ($canEdit): ?><div class="cms-savebar"><button type="submit" class="btn btn-primary">Save draft</button></div><?php endif; ?>
  </form>
  <aside class="cms-side">
    <?php if ($row && $status): ?>
      <?php cms_workflow_panel($auth_user, 'page', $row, $self); ?>
      <?php cms_history_panel($pdo, 'page', (int) $row['id']); ?>
    <?php else: ?>
      <div class="panel"><p class="cms-small">This page uses its template SEO. Save a draft override to begin the review workflow.</p></div>
    <?php endif; ?>
  </aside>
</div>
<script src="<?= asset('js/cms-admin.js') ?>" defer></script>
