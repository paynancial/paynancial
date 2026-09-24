<?php
/** Admin: homepage hero — edited through the CMS workflow; the hardcoded hero is the fallback. */
require_once __DIR__ . '/../includes/cms/admin-ui.php';

$page_meta = ['title' => 'Homepage hero | Paynancial CMS', 'heading' => 'Homepage Hero'];
$pdo = db();
if (!user_can($auth_user, 'cms.view')) {
    cms_no_access();
    return;
}
$self = '/admin/cms-hero';
$row = cms_page_row($pdo, 'home');
$canEdit = user_can($auth_user, 'cms.edit');
$errors = [];
$input = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        cms_redirect($self, null, 'Your session expired. Your changes were not saved — please try again.');
    }
    if (($_POST['op'] ?? '') === 'transition' && $row) {
        cms_handle_transition($pdo, $auth_user, 'page', (int) $row['id'], $self, cms_page_snapshot('hero'));
    }
    if (($_POST['op'] ?? '') === 'save') {
        $input = $_POST;
        try {
            [$hero, $errors] = cms_hero_input($input);
            if (!$errors) {
                $wasLive = $row && (int) $row['live'] === 1;
                cms_page_save($pdo, $auth_user, 'home', 'Home', 'hero', $hero, isset($_POST['lock']) ? (int) $_POST['lock'] : null);
                cms_redirect($self, 'Saved as draft.' . ($wasLive ? ' The published hero stays live until this draft is approved and published.' : ''));
            }
        } catch (CmsDenied | RuntimeException $e) {
            $errors['_'] = $e->getMessage();
        }
    }
}

$defaults = home_hero_defaults();
$published = $row && (int) $row['live'] === 1 ? (json_decode((string) $row['published_json'], true)['hero'] ?? null) : null;
$v = $input ?? cms_page_draft($row, 'hero') ?? $published ?? $defaults;
$status = $row['workflow_status'] ?? null;
$dis = $canEdit ? [] : ['disabled' => true];
?>
<?php cms_flash_messages(); ?>
<?php if (!empty($errors['_'])): ?><div class="cms-alert cms-alert--failed" role="alert"><?= e($errors['_']) ?></div><?php endif; ?>
<?php if ($errors && empty($errors['_'])): ?><div class="cms-alert cms-alert--failed" role="alert">Not saved — please fix the highlighted fields.</div><?php endif; ?>
<p class="cms-crumbs"><a href="/admin/cms">CMS</a> / Homepage hero</p>

<div class="cms-editor">
  <form method="post" action="<?= e($self) ?>" class="cms-form" data-cms-dirty>
    <?= csrf_field() ?><input type="hidden" name="op" value="save">
    <?php if ($row): ?><input type="hidden" name="lock" value="<?= (int) $row['lock_version'] ?>"><?php endif; ?>
    <div class="panel">
      <div class="panel-head"><h2>Hero content</h2><?= $status ? cms_status_badges($status, (int) $row['live'] === 1) : '<span class="badge neutral">Not edited — hardcoded hero is live</span>' ?></div>
      <?php cms_field('eyebrow', 'Eyebrow', $v['eyebrow'] ?? '', CMS_HERO_LIMITS['eyebrow'], $dis); ?>
      <?php cms_field('title', 'Heading (H1)', $v['title'] ?? '', CMS_HERO_LIMITS['title'], ['required' => true, 'error' => $errors['title'] ?? null] + $dis); ?>
      <?php cms_field('lead', 'Supporting text', $v['lead'] ?? '', CMS_HERO_LIMITS['lead'], ['type' => 'textarea', 'rows' => 3, 'required' => true, 'error' => $errors['lead'] ?? null] + $dis); ?>
      <div class="cms-grid2">
        <?php cms_field('primary_label', 'Primary button label', $v['primary_label'] ?? '', CMS_HERO_LIMITS['primary_label'], ['required' => true, 'error' => $errors['primary_label'] ?? null] + $dis); ?>
        <?php cms_field('primary_url', 'Primary button link', $v['primary_url'] ?? '', [0, 200], ['required' => true, 'error' => $errors['primary_url'] ?? null, 'help' => 'On-site path, e.g. /contact'] + $dis); ?>
        <?php cms_field('secondary_label', 'Secondary button label', $v['secondary_label'] ?? '', CMS_HERO_LIMITS['secondary_label'], ['required' => true, 'error' => $errors['secondary_label'] ?? null] + $dis); ?>
        <?php cms_field('secondary_url', 'Secondary button link', $v['secondary_url'] ?? '', [0, 200], ['required' => true, 'error' => $errors['secondary_url'] ?? null] + $dis); ?>
      </div>
      <p class="cms-small text-muted">Plain text only. Avoid unverifiable claims (fees, customer numbers, certifications, licences, approvals). If the CMS hero is ever unpublished or the database is unavailable, the homepage falls back to the hardcoded hero:
        <em>“<?= e($defaults['title']) ?>”</em>.</p>
    </div>
    <?php if ($canEdit): ?><div class="cms-savebar"><button type="submit" class="btn btn-primary">Save draft</button></div><?php endif; ?>
  </form>
  <aside class="cms-side">
    <?php if ($row && $status): ?>
      <div class="panel"><div class="panel-head"><h2>Preview</h2></div>
        <p class="cms-small"><a class="btn btn-outline btn-sm" href="/admin/cms-preview?type=hero" target="_blank" rel="noopener">Preview homepage with this draft</a></p></div>
      <?php cms_workflow_panel($auth_user, 'page', $row, $self); ?>
      <?php cms_history_panel($pdo, 'page', (int) $row['id']); ?>
    <?php else: ?>
      <div class="panel"><p class="cms-small">The form starts from the live hardcoded hero. Save a draft to begin the review workflow.</p></div>
    <?php endif; ?>
  </aside>
</div>
<script src="<?= asset('js/cms-admin.js') ?>" defer></script>
