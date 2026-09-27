<?php
/**
 * Admin: System Health — real checks only (includes/admin/health.php).
 * Unknown states say "Status unavailable" / "Not connected"; nothing is
 * reported as healthy without evidence. No uptime figure exists.
 */
require_once __DIR__ . '/../includes/admin/health.php';
require_once __DIR__ . '/../includes/admin/ui.php';

$page_meta = ['title' => 'System Health | Paynancial Admin', 'own_head' => true];
$checks = admin_health_checks();
[$state, $tone, $text] = admin_health_summary(array_filter($checks, fn ($c) => !in_array($c['state'], ['not_connected'], true)));
$counts = array_count_values(array_column($checks, 'state'));
$groups = [];
foreach ($checks as $c) {
    $groups[$c['group']][] = $c;
}
echo adm_page_head('System Health', 'Live checks run when this page loads. Last run ' . date('j M Y, H:i:s') . ' (server time).',
    '<a class="adm-btn" href="/admin/system-health">' . adm_icon('refresh', 15) . 'Re-run checks</a>');
?>
<div class="adm-overall adm-overall--<?= e($tone) ?>" role="status" style="font-size:14px;padding:12px 16px"><?= adm_icon(adm_state_meta($state)[2], 17) ?><?= e($text) ?>
  <span class="adm-muted" style="font-weight:500;margin-left:auto">
    <?php foreach (['operational', 'warning', 'critical', 'not_connected', 'unavailable'] as $s): if (!empty($counts[$s])): ?>
      <?= (int) $counts[$s] ?> <?= e(strtolower(adm_state_meta($s)[0])) ?> ·
    <?php endif; endforeach; ?> <?= count($checks) ?> checks
  </span>
</div>
<p class="adm-muted" style="font-size:12.5px;margin:0 0 8px">"Not connected" means no data source or integration exists yet; it is excluded from the headline. "Status unavailable" means the check ran but could not prove a state.</p>

<?php foreach ($groups as $group => $items): ?>
  <h2 class="adm-hc-group"><?= e($group) ?></h2>
  <div class="adm-hc-grid">
    <?php foreach ($items as $c): ?>
      <article class="adm-hc" aria-labelledby="hc-<?= e(str_replace('.', '-', $c['key'])) ?>">
        <div class="adm-hc-h"><strong id="hc-<?= e(str_replace('.', '-', $c['key'])) ?>"><?= e($c['label']) ?></strong><?= adm_state_pill($c['state']) ?></div>
        <p><?= e($c['detail']) ?></p>
        <?php if ($c['evidence'] !== ''): ?><p class="adm-hc-ev"><?= e($c['evidence']) ?></p><?php endif; ?>
        <p class="adm-source"><?= adm_icon('clock', 12) ?>Checked <?= e(date('H:i:s', strtotime($c['checked_at']))) ?> · <?= (int) $c['ms'] ?> ms</p>
      </article>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>
