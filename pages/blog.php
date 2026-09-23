<?php
$page_meta = [
    'title' => 'Blog | Paynancial',
    'description' => 'Payment guides, product updates and news from Paynancial.',
];

$posts = [];
try {
    $posts = db()->query(
        "SELECT slug, title, excerpt, published_at FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 12"
    )->fetchAll();
} catch (Throwable $e) {
    $posts = [];
}
?>
<section style="padding-top:56px;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Blog</span>
      <h1>Insights on payments and financial technology.</h1>
    </div>
  </div>
</section>

<section class="section-subtle" id="guides">
  <div class="container">
    <?php if (empty($posts)): ?>
      <div class="module-stub reveal">
        <strong>No articles published yet</strong>
        New posts will appear here automatically once published through the admin CMS blog module.
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($posts as $post): ?>
          <div class="card reveal">
            <h3><?= e($post['title']) ?></h3>
            <p><?= e($post['excerpt']) ?></p>
            <span class="text-muted" style="font-size:0.78rem;"><?= e(date('d M Y', strtotime((string) $post['published_at']))) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section id="news">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">News</span>
      <h2>Company announcements</h2>
    </div>
    <div class="module-stub reveal">
      <strong>No announcements yet</strong>
      Company news will be published here as it becomes available.
    </div>
  </div>
</section>
