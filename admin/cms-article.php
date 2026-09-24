<?php
/** Admin: CMS article editor — /admin/cms-article/{id} or /admin/cms-article/new. */
require_once __DIR__ . '/../includes/cms/admin-ui.php';

$pdo = db();
$isNew = $route_param === 'new';
$id = $isNew ? 0 : (int) $route_param;
$page_meta = ['title' => ($isNew ? 'New article' : 'Edit article') . ' | Paynancial CMS', 'heading' => $isNew ? 'New Article' : 'Edit Article'];

if (!user_can($auth_user, 'cms.view')) {
    cms_no_access();
    return;
}
$row = $isNew ? null : cms_load($pdo, 'article', $id);
if (!$isNew && $row === null) {
    http_response_code(404);
    echo '<div class="panel"><p>Article not found. <a href="/admin/cms-articles">Back to articles</a></p></div>';
    return;
}
$self = $isNew ? '/admin/cms-article/new' : '/admin/cms-article/' . $id;
$canEdit = user_can($auth_user, $isNew ? 'cms.create' : 'cms.edit');
$canSeo = user_can($auth_user, 'cms.seo.manage');
$errors = [];
$input = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        cms_redirect($self, null, 'Your session expired. Your changes were not saved — please try again.');
    }
    if (($_POST['op'] ?? '') === 'transition' && !$isNew) {
        cms_handle_transition($pdo, $auth_user, 'article', $id, $self, cms_article_snapshot($pdo));
    }
    if (($_POST['op'] ?? '') === 'save') {
        $input = $_POST;
        try {
            if (!$canEdit) {
                throw new CmsDenied('You do not have permission to edit articles.');
            }
            if (!empty($_FILES['og_upload']['name'])) {
                $input['og_image'] = cms_store_image($_FILES['og_upload']);
            }
            [$fields, $errors] = cms_article_input($pdo, $auth_user, $input, $row);
            if (!$errors) {
                $newId = cms_article_save($pdo, $auth_user, $row, $fields, isset($_POST['lock']) ? (int) $_POST['lock'] : null);
                $wasLive = $row && (int) $row['live'] === 1;
                cms_redirect('/admin/cms-article/' . $newId, 'Saved as draft.' . ($wasLive ? ' The published version stays live until this draft is approved and published.' : ''));
            }
        } catch (CmsDenied | RuntimeException $e) {
            $errors['_'] = $e->getMessage();
        }
    }
}

// Values shown in the form: the failed POST, else the working copy.
$c = $row ? (json_decode((string) $row['content_json'], true) ?: []) : [];
$v = $input !== null ? $input + ['indexable' => '', 'in_sitemap' => ''] : [
    'title' => $row['title'] ?? '', 'slug' => $row['slug'] ?? '', 'category' => $row['category'] ?? '',
    'dek' => $c['dek'] ?? '', 'question' => $c['question'] ?? '', 'answer' => $c['answer'] ?? '',
    'takeaways' => implode("\n", $c['takeaways'] ?? []), 'body' => cms_sections_to_body($c['sections'] ?? []),
    'faqs' => cms_faqs_to_text($c['faqs'] ?? []), 'related' => implode(', ', $c['related'] ?? []),
    'links' => implode("\n", array_map(fn ($l) => $l[0] . ' | ' . $l[1], $c['links'] ?? [])),
    'meta_title' => $row['meta_title'] ?? '', 'description' => $row['meta_description'] ?? '',
    'og_title' => $row['og_title'] ?? '', 'og_description' => $row['og_description'] ?? '', 'og_image' => $row['og_image'] ?? '',
    'indexable' => $row['indexable'] ?? 0, 'in_sitemap' => $row['in_sitemap'] ?? 0,
];
$slugLocked = $row && $row['published_json'] !== null;
$dis = $canEdit ? [] : ['disabled' => true];
?>
<?php cms_flash_messages(); ?>
<?php if (!empty($errors['_'])): ?><div class="cms-alert cms-alert--failed" role="alert"><?= e($errors['_']) ?></div><?php endif; ?>
<?php if ($errors && empty($errors['_'])): ?><div class="cms-alert cms-alert--failed" role="alert">Not saved — please fix the <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?>.</div><?php endif; ?>

<p class="cms-crumbs"><a href="/admin/cms">CMS</a> / <a href="/admin/cms-articles">Articles</a> / <?= e($row['title'] ?? 'New article') ?></p>

<div class="cms-editor">
  <form method="post" action="<?= e($self) ?>" enctype="multipart/form-data" class="cms-form" data-cms-dirty>
    <?= csrf_field() ?>
    <input type="hidden" name="op" value="save">
    <?php if ($row): ?><input type="hidden" name="lock" value="<?= (int) $row['lock_version'] ?>"><?php endif; ?>

    <div class="panel">
      <div class="panel-head"><h2>Article</h2><?= $row ? cms_status_badges($row['status'], (int) $row['live'] === 1) : '<span class="badge neutral">New</span>' ?></div>
      <?php cms_field('title', 'Title (H1)', $v['title'], CMS_LIMITS['title'], ['required' => true, 'error' => $errors['title'] ?? null] + $dis); ?>
      <?php cms_field('slug', 'URL slug', $v['slug'], [0, 80], ['error' => $errors['slug'] ?? null, 'disabled' => $slugLocked || !$canEdit,
          'help' => $slugLocked ? 'Fixed — this article has been published at /blog/' . e($v['slug']) . '. URLs never change.' : 'Becomes /blog/{slug}. Leave blank to generate from the title.']); ?>
      <div class="cms-field<?= isset($errors['category']) ? ' has-error' : '' ?>">
        <label for="f-category">Topic <span aria-hidden="true">*</span></label>
        <select id="f-category" name="category" required <?= $canEdit ? '' : 'disabled' ?>>
          <option value="">Choose…</option>
          <?php foreach (cms_article_categories() as $k => $label): ?><option value="<?= e($k) ?>" <?= $v['category'] === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select>
        <small class="cms-help">Regulatory Insights is not available here: regulatory articles are written only from an official source document and stay version-controlled.</small>
        <?php if (isset($errors['category'])): ?><small class="cms-error"><?= e($errors['category']) ?></small><?php endif; ?>
      </div>
      <?php cms_field('dek', 'Standfirst (summary under the title)', $v['dek'], CMS_LIMITS['dek'], ['type' => 'textarea', 'rows' => 2] + $dis); ?>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Quick answer (AEO)</h2></div>
      <?php cms_field('question', 'Question', $v['question'], CMS_LIMITS['question'], ['required' => true, 'error' => $errors['question'] ?? null] + $dis); ?>
      <?php cms_field('answer', 'Direct answer', $v['answer'], CMS_LIMITS['answer'], ['type' => 'textarea', 'rows' => 4, 'required' => true, 'error' => $errors['answer'] ?? null] + $dis); ?>
      <?php cms_field('takeaways', 'Key takeaways (one per line, up to 8)', $v['takeaways'], [0, 3000], ['type' => 'textarea', 'rows' => 4] + $dis); ?>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Body</h2></div>
      <?php cms_field('body', 'Sections (HTML)', $v['body'], [0, 200000], ['type' => 'textarea', 'rows' => 22, 'mono' => true, 'error' => $errors['body'] ?? null,
          'help' => 'Start each section with <code>&lt;h2&gt;Heading&lt;/h2&gt;</code>. Allowed: p, h3, h4, ul, ol, li, strong, em, a (on-site paths, https, mailto), blockquote, code, pre, table, dl, and div class="blog-table" / "blog-callout". Everything else — scripts, styles, iframes, forms, inline styles, event handlers — is removed on save.'] + $dis); ?>
      <?php cms_field('faqs', 'FAQs', $v['faqs'], [0, 20000], ['type' => 'textarea', 'rows' => 8, 'error' => $errors['faqs'] ?? null,
          'help' => 'Question on the first line, answer on the next; leave a blank line between FAQs.'] + $dis); ?>
      <?php cms_field('related', 'Related articles (slugs, comma separated)', $v['related'], [0, 600], $dis); ?>
      <?php cms_field('links', 'Related on Paynancial (one per line: Label | /path)', $v['links'], [0, 2000], ['type' => 'textarea', 'rows' => 3, 'error' => $errors['links'] ?? null] + $dis); ?>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>SEO &amp; social</h2></div>
      <?php cms_field('meta_title', 'Meta title', $v['meta_title'], CMS_LIMITS['meta_title'], ['help' => 'Blank = "Title | Paynancial Insights".'] + $dis); ?>
      <?php cms_field('description', 'Meta description', $v['description'], CMS_LIMITS['description'], ['type' => 'textarea', 'rows' => 2, 'required' => true, 'error' => $errors['description'] ?? null] + $dis); ?>
      <div class="cms-serp" aria-hidden="true">
        <span class="cms-serp-url">paynancial.com › blog › <span data-serp="slug"><?= e($v['slug'] ?: 'your-article') ?></span></span>
        <span class="cms-serp-title" data-serp="title"><?= e($v['meta_title'] ?: ($v['title'] ? $v['title'] . ' | Paynancial Insights' : 'Meta title')) ?></span>
        <span class="cms-serp-desc" data-serp="desc"><?= e($v['description'] ?: 'Meta description') ?></span>
      </div>
      <?php cms_field('og_title', 'Social title (optional)', $v['og_title'], CMS_LIMITS['og_title'], $dis); ?>
      <?php cms_field('og_description', 'Social description (optional)', $v['og_description'], CMS_LIMITS['og_description'], ['type' => 'textarea', 'rows' => 2] + $dis); ?>
      <?php cms_field('og_image', 'Social image path (optional)', $v['og_image'], [0, 255], ['error' => $errors['og_image'] ?? null, 'placeholder' => '/uploads/cms/…'] + $dis); ?>
      <?php if ($canEdit): ?>
      <div class="cms-field"><label for="f-og-upload">…or upload an image</label>
        <input type="file" id="f-og-upload" name="og_upload" accept="image/jpeg,image/png,image/webp">
        <small class="cms-help">JPEG, PNG or WebP, up to 2 MB, 200–4000 px. 1200 × 630 px suits most social previews.</small></div>
      <?php endif; ?>
      <fieldset class="cms-fieldset" <?= $canSeo && $canEdit ? '' : 'disabled' ?>>
        <legend>Indexing</legend>
        <label class="cms-check"><input type="checkbox" name="indexable" value="1" <?= !empty($v['indexable']) ? 'checked' : '' ?>> Allow search engines to index this article once published</label>
        <label class="cms-check"><input type="checkbox" name="in_sitemap" value="1" <?= !empty($v['in_sitemap']) ? 'checked' : '' ?>> Include in the XML sitemap (only when indexable)</label>
        <small class="cms-help"><?= $canSeo ? 'Indexing is never automatic. Unticked = published with noindex.' : 'Only people with the cms.seo.manage permission can change indexing.' ?></small>
      </fieldset>
    </div>

    <?php if ($canEdit): ?>
    <div class="cms-savebar">
      <button type="submit" class="btn btn-primary">Save draft</button>
      <?php if ($row && $row['status'] !== 'draft'): ?><span class="cms-small">Saving returns this article to Draft and clears its approval<?= (int) $row['live'] === 1 ? '; the published version stays live' : '' ?>.</span><?php endif; ?>
    </div>
    <?php endif; ?>
  </form>

  <aside class="cms-side">
    <?php if ($row): ?>
      <div class="panel"><div class="panel-head"><h2>Preview</h2></div>
        <p class="cms-small"><a class="btn btn-outline btn-sm" href="/admin/cms-preview?type=article&amp;id=<?= $id ?>" target="_blank" rel="noopener">Preview working copy</a></p>
        <?php if ((int) $row['live'] === 1): ?><p class="cms-small"><a href="/blog/<?= e($row['slug']) ?>" target="_blank" rel="noopener">View live article →</a></p><?php endif; ?>
        <p class="cms-small text-muted">Preview shows the last saved draft.</p>
      </div>
      <?php cms_workflow_panel($auth_user, 'article', $row, $self); ?>
      <?php cms_history_panel($pdo, 'article', $id); ?>
    <?php else: ?>
      <div class="panel"><p class="cms-small">Save the draft first. Then submit it for editorial review → SEO/AEO review → business/legal review → approval → publishing.</p></div>
    <?php endif; ?>
  </aside>
</div>
<script src="<?= asset('js/cms-admin.js') ?>" defer></script>
