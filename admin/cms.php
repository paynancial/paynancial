<?php
/** Admin: lightweight CMS for editable page content (starts with the homepage hero). */
$page_meta = ['title' => 'CMS | Paynancial Admin', 'heading' => 'Content Management'];

$pdo = db();
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    } else {
        $pageKey = sanitize_input((string) ($_POST['page_key'] ?? 'home'));
        $heroTitle = sanitize_input((string) ($_POST['hero_title'] ?? ''));
        $heroSubtitle = sanitize_input((string) ($_POST['hero_subtitle'] ?? ''));
        $metaTitle = sanitize_input((string) ($_POST['meta_title'] ?? ''));
        $metaDescription = sanitize_input((string) ($_POST['meta_description'] ?? ''));

        $content = json_encode(['hero_title' => $heroTitle, 'hero_subtitle' => $heroSubtitle], JSON_UNESCAPED_SLASHES);

        $stmt = $pdo->prepare(
            'INSERT INTO cms_pages (page_key, title, content_json, meta_title, meta_description, updated_by)
             VALUES (:key, :key2, :content, :mt, :md, :uid)
             ON DUPLICATE KEY UPDATE content_json = VALUES(content_json), meta_title = VALUES(meta_title),
               meta_description = VALUES(meta_description), updated_by = VALUES(updated_by)'
        );
        $stmt->execute([
            'key' => $pageKey, 'key2' => ucfirst($pageKey), 'content' => $content,
            'mt' => $metaTitle, 'md' => $metaDescription, 'uid' => $auth_user['id'],
        ]);
        $success = true;
    }
}

$stmt = $pdo->prepare('SELECT content_json, meta_title, meta_description FROM cms_pages WHERE page_key = :key');
$stmt->execute(['key' => 'home']);
$home = $stmt->fetch();
$content = $home ? json_decode((string) $home['content_json'], true) : [];
?>
<div class="panel" style="max-width:640px;">
  <div class="panel-head"><h2>Homepage Content</h2></div>
  <?php if ($success): ?><div class="badge success" style="margin-bottom:16px;">Saved</div><?php endif; ?>
  <?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:12px;"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" style="display:grid;gap:16px;">
    <?= csrf_field() ?>
    <input type="hidden" name="page_key" value="home">
    <div class="field"><label>Hero Title</label><input type="text" name="hero_title" value="<?= e($content['hero_title'] ?? '') ?>"></div>
    <div class="field"><label>Hero Subtitle</label><textarea name="hero_subtitle" rows="3" style="padding:12px 14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);"><?= e($content['hero_subtitle'] ?? '') ?></textarea></div>
    <div class="field"><label>Meta Title (SEO)</label><input type="text" name="meta_title" value="<?= e($home['meta_title'] ?? '') ?>"></div>
    <div class="field"><label>Meta Description (SEO)</label><textarea name="meta_description" rows="2" style="padding:12px 14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);"><?= e($home['meta_description'] ?? '') ?></textarea></div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>
  <p class="text-muted" style="margin-top:16px;font-size:0.82rem;">Note: the public homepage template currently ships with static copy for design consistency. Wiring it to read from <code>cms_pages</code> is a drop-in change (swap the hardcoded strings in <code>pages/home.php</code> for this table) — the CMS storage layer above is already live.</p>
</div>

<div class="panel">
  <div class="panel-head"><h2>Other CMS Pages</h2></div>
  <p class="text-muted">About, Products, Solutions, Pricing, FAQs, Blog, News, Careers, Contact and Footer content follow the same <code>cms_pages</code> / <code>blog_posts</code> pattern and can be added here as additional edit forms.</p>
</div>
