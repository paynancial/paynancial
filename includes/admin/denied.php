<?php
/** Admin access-denied / expired-session page (rendered inside the admin shell). */
require_once __DIR__ . '/ui.php';
$page_meta = ['title' => ($admin_denied['title'] ?? 'Access denied') . ' | Paynancial Admin', 'own_head' => true, 'crumb' => $admin_denied['title'] ?? 'Access denied'];
?>
<div class="adm-card adm-denied" role="alert">
  <?= adm_icon(http_response_code() === 419 ? 'clock' : 'lock', 28) ?>
  <h1 class="adm-h1"><?= e($admin_denied['title'] ?? 'Access denied') ?></h1>
  <p><?= e($admin_denied['text'] ?? 'You do not have permission to open this page.') ?></p>
  <?php if (!empty($admin_denied['perm']) && $admin_denied['perm'] !== 'unknown'): ?>
    <p class="adm-muted">Required permission: <code><?= e($admin_denied['perm']) ?></code>. Ask a super admin if you need it. This attempt was recorded in the audit log.</p>
  <?php endif; ?>
  <p><a class="adm-btn adm-btn--primary" href="/admin/dashboard"><?= adm_icon('home', 16) ?>Back to the Command Center</a></p>
</div>
