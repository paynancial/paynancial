<?php
/**
 * Command Center (enterprise admin dashboard). Real data only: every panel
 * is an admin_widget() with a declared source; sources that do not exist
 * yet say "Not connected" instead of showing numbers.
 */
require_once __DIR__ . '/../includes/admin/dashboard-render.php';
require_once __DIR__ . '/../includes/admin/prefs.php';

$page_meta = ['title' => 'Command Center | Paynancial Admin', 'own_head' => true, 'crumb' => 'Dashboard'];
$me = $auth_user;
$prefs = admin_prefs($me);

$tz = new DateTimeZone('Asia/Kolkata');
$hour = (int) (new DateTime('now', $tz))->format('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$first = explode(' ', trim((string) ($me['name'] ?? '')))[0] ?: 'there';

$kpis = array_map(fn ($k) => admin_widget($k, $me), ['kpi.content_review', 'kpi.leads_today', 'kpi.incorporation', 'kpi.website_health']);
[$kContent, $kLeads, , $kHealth] = $kpis;

$tabs = [
    'traffic' => 'Website Traffic', 'enquiries' => 'Enquiries', 'conversions' => 'Conversions', 'incorporation' => 'Incorporation',
    'revenue' => 'Revenue', 'content' => 'Content', 'seo' => 'SEO',
];
$charts = [];
foreach ($tabs as $k => $_) {
    $charts[$k] = admin_widget('chart.' . $k, $me);
}
$defaultTab = $charts['enquiries']['state'] === 'ok' ? 'enquiries' : ($charts['content']['state'] === 'ok' ? 'content' : 'traffic');

$recent = admin_widget('table.recent_enquiries', $me);
$heroCollapsed = (bool) ($prefs['hero_collapsed'] ?? false);
$qa = admin_quick_actions();
$chosen = array_values(array_filter($prefs['quick_actions'] ?? ADMIN_DEFAULT_QUICK_ACTIONS, fn ($k) => isset($qa[$k]) && user_can($me, $qa[$k]['perm'])));
?>
<section class="adm-hero<?= $heroCollapsed ? ' is-collapsed' : '' ?>" aria-labelledby="adm-greet">
  <div class="adm-hero-main">
    <h1 id="adm-greet"><?= e($greeting) ?>, <?= e($first) ?> <span aria-hidden="true">👋</span></h1>
    <p class="adm-hero-lead">Powering Payments, Growth and Global Opportunities.</p>
    <div class="adm-hero-status">
      <?php if ($kContent['state'] === 'ok'): ?><span class="adm-hero-chip"><?= adm_icon('pen', 13) ?><?= (int) $kContent['data']['value'] ?> awaiting review</span><?php endif; ?>
      <?php if ($kLeads['state'] === 'ok'): ?><span class="adm-hero-chip"><?= adm_icon('inbox', 13) ?><?= (int) $kLeads['data']['value'] ?> new lead<?= (int) $kLeads['data']['value'] === 1 ? '' : 's' ?> today</span><?php endif; ?>
      <?php if ($kHealth['state'] === 'ok'): ?><span class="adm-hero-chip"><?= adm_icon('heart', 13) ?><?= (int) $kHealth['data']['value'] ?>/<?= (int) $kHealth['data']['total'] ?> health checks passing</span><?php endif; ?>
    </div>
  </div>
  <div class="adm-hero-side">
    <span class="adm-hero-tag">Together for a Smarter Financial Future.</span>
    <button type="button" class="adm-hero-toggle" data-hero-toggle aria-expanded="<?= $heroCollapsed ? 'false' : 'true' ?>">
      <span data-hero-label><?= $heroCollapsed ? 'Expand' : 'Collapse' ?></span><?= adm_icon($heroCollapsed ? 'chevron-down' : 'chevron-up', 14) ?>
    </button>
  </div>
</section>

<h2 class="sr-only">Key figures</h2>
<div class="adm-kpis">
  <?php foreach ($kpis as $w) { echo admin_render_kpi($w); } ?>
</div>

<div class="adm-dash">
  <div class="adm-dash-main">
    <section class="adm-card" aria-labelledby="km-h">
      <div class="adm-card-h"><div><h2 id="km-h">Key Metrics</h2><p>Last 30 days. Tabs marked with a grey dot have no data source connected.</p></div></div>
      <div class="adm-tabs" role="tablist" aria-label="Metric" data-remember="metrics">
        <?php foreach ($tabs as $k => $label): $nc = $charts[$k]['state'] === 'not_connected' || $charts[$k]['state'] === 'restricted'; ?>
          <button type="button" role="tab" class="adm-tab<?= $nc ? ' adm-tab--nc' : '' ?>" id="tab-m-<?= $k ?>" aria-controls="panel-m-<?= $k ?>"
                  aria-selected="<?= $k === $defaultTab ? 'true' : 'false' ?>" tabindex="<?= $k === $defaultTab ? '0' : '-1' ?>"><?= e($label) ?><?php if ($nc): ?><span class="sr-only"> (not connected)</span><?php endif; ?></button>
        <?php endforeach; ?>
      </div>
      <?php foreach ($tabs as $k => $label): ?>
        <div class="adm-tabpanel" role="tabpanel" id="panel-m-<?= $k ?>" aria-labelledby="tab-m-<?= $k ?>" data-widget-box <?= $k === $defaultTab ? '' : 'hidden' ?>>
          <?= admin_render_chart($charts[$k]) ?>
        </div>
      <?php endforeach; ?>
    </section>

    <section class="adm-card" aria-labelledby="tp-h">
      <div class="adm-card-h"><div><h2 id="tp-h">Top Performing Pages</h2><p>URL · page title · views · CTR · conversion · SEO status</p></div></div>
      <?= adm_widget_state(admin_widget('table.top_pages', $me)) ?>
    </section>

    <div class="adm-pipes">
      <section class="adm-card" aria-labelledby="pl-h" data-widget-box>
        <div class="adm-card-h"><div><h2 id="pl-h">Leads Pipeline</h2></div><?php if (user_can($me, 'enquiries.view')): ?><a class="adm-btn adm-btn--sm" href="/admin/enquiries">View all</a><?php endif; ?></div>
        <?= admin_render_pipeline(admin_widget('pipeline.leads', $me)) ?>
      </section>
      <section class="adm-card" aria-labelledby="pi-h">
        <div class="adm-card-h"><div><h2 id="pi-h">Incorporation Pipeline</h2></div></div>
        <?= admin_render_pipeline(admin_widget('pipeline.incorporation', $me)) ?>
      </section>
    </div>

  </div>

  <aside class="adm-dash-rail" aria-label="Status">
    <section class="adm-card" aria-labelledby="env-h" data-widget-box>
      <div class="adm-rail-h"><h2 id="env-h">Environment Status</h2></div>
      <?= admin_render_env(admin_widget('rail.environment', $me)) ?>
    </section>
    <?php $appr = admin_widget('rail.approvals', $me); if ($appr['state'] !== 'restricted'): ?>
    <section class="adm-card" aria-labelledby="ap-h" data-widget-box>
      <div class="adm-rail-h"><h2 id="ap-h">Pending Approvals</h2><a class="adm-btn adm-btn--sm adm-btn--ghost" href="/admin/approvals">Open</a></div>
      <?= admin_render_approvals($appr) ?>
    </section>
    <?php endif; ?>
    <section class="adm-card" aria-labelledby="ac-h" data-widget-box>
      <div class="adm-rail-h"><h2 id="ac-h">Recent Activity</h2><a class="adm-btn adm-btn--sm adm-btn--ghost" href="/admin/activity">All</a></div>
      <?= admin_render_activity(admin_widget('rail.activity', $me)) ?>
    </section>
    <section class="adm-card" aria-labelledby="sh-h">
      <div class="adm-rail-h"><h2 id="sh-h">System Health</h2><?php if (user_can($me, 'system.health.view')): ?><a class="adm-btn adm-btn--sm adm-btn--ghost" href="/admin/system-health">Details</a><?php endif; ?></div>
      <div data-widget-box data-widget-async="rail.health"><?= adm_skeleton(4) ?></div>
    </section>
  </aside>
</div>

    <section class="adm-card" aria-label="Enquiries and incorporation cases">
  <div class="adm-card-h" style="margin-bottom:0">
    <div class="adm-tabs" role="tablist" aria-label="Work queues" style="border:0;margin:0;padding:0">
      <button type="button" role="tab" class="adm-tab" id="tab-q-enq" aria-controls="panel-q-enq" aria-selected="true" tabindex="0">Recent Enquiries<?php if ($recent['state'] === 'ok'): ?> <span class="adm-tab-count"><?= count($recent['data']['rows']) ?></span><?php endif; ?></button>
      <button type="button" role="tab" class="adm-tab" id="tab-q-cases" aria-controls="panel-q-cases" aria-selected="false" tabindex="-1">Incorporation Cases <span class="adm-tab-count">0</span></button>
    </div>
    <?php if (user_can($me, 'enquiries.view')): ?><a class="adm-btn adm-btn--sm" href="/admin/enquiries">View all<?= $recent['state'] === 'ok' ? ' (' . number_format($recent['data']['total']) . ')' : '' ?></a><?php endif; ?>
  </div>
  <div style="height:16px"></div>
  <div role="tabpanel" id="panel-q-enq" aria-labelledby="tab-q-enq" data-widget-box><?= admin_render_recent_enquiries($recent) ?></div>
  <div role="tabpanel" id="panel-q-cases" aria-labelledby="tab-q-cases" hidden>
    <div class="adm-state">
      <?= adm_icon('briefcase', 20) ?><div><strong>No incorporation cases yet</strong><p>Case management (stages, documents, tasks, SLA) is delivered in Phase 5. Nothing is shown until real cases exist.</p></div>
    </div>
  </div>
</section>

    <section class="adm-card" aria-labelledby="qa-h">
  <div class="adm-card-h"><div><h2 id="qa-h">Quick Actions</h2></div>
    <button type="button" class="adm-btn adm-btn--sm" data-customize-open><?= adm_icon('sliders', 15) ?>Customize</button></div>
  <?php if (!$chosen): ?>
    <div class="adm-state"><?= adm_icon('sliders', 20) ?><div><strong>No quick actions selected</strong><p>Use Customize to choose up to five.</p></div></div>
  <?php else: ?>
  <div class="adm-qa">
    <?php foreach ($chosen as $key): $a = $qa[$key]; ?>
      <?php if ($a['url']): ?>
        <a class="adm-qa-tile" href="<?= e($a['url']) ?>"><span class="adm-kpi-ic adm-kpi-ic--green"><?= adm_icon($a['icon'], 17) ?></span><strong><?= e($a['label']) ?></strong><span><?= e($a['help']) ?></span></a>
      <?php else: ?>
        <div class="adm-qa-tile is-soon" aria-disabled="true"><span class="adm-kpi-ic adm-kpi-ic--yellow"><?= adm_icon($a['icon'], 17) ?></span><strong><?= e($a['label']) ?></strong><span><?= e($a['help']) ?></span><span class="adm-qa-soon"><?= e($a['phase']) ?></span></div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php if (count($chosen) > 3): ?><button type="button" class="adm-btn adm-btn--sm adm-btn--ghost adm-qa-more-btn" data-qa-more style="margin-top:12px">Show all actions</button><?php endif; ?>
  <?php endif; ?>
</section>

<dialog class="adm-dialog" id="adm-customize" aria-labelledby="cz-h">
  <form method="dialog">
    <h2 id="cz-h">Customize quick actions</h2>
    <p class="adm-muted" style="margin:0 0 12px">Choose up to five. Only actions you are allowed to use are listed. <span data-cz-count></span></p>
    <?php foreach ($qa as $key => $a): if (!user_can($me, $a['perm'])) { continue; } ?>
      <label class="adm-check"><input type="checkbox" value="<?= e($key) ?>" <?= in_array($key, $chosen, true) ? 'checked' : '' ?>>
        <?= e($a['label']) ?> <span class="adm-muted">— <?= e($a['help']) ?><?= $a['url'] ? '' : ' (' . e($a['phase']) . ')' ?></span></label>
    <?php endforeach; ?>
    <div class="adm-dialog-actions"><button type="button" class="adm-btn" data-cz-cancel>Cancel</button><button type="submit" class="adm-btn adm-btn--primary">Save</button></div>
  </form>
</dialog>
