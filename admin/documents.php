<?php
/**
 * Admin: Documents — unified, READ-ONLY view over the five existing
 * document tables (VIEW admin_documents_v, decision D5). Nothing is merged,
 * moved or copied. Statuses are normalised to Pending / Uploaded / Under
 * review / Verified / Rejected. File paths are never shown; review happens
 * in the owning module. Employee (HR) documents need documents.view_hr.
 */
require_once __DIR__ . '/../includes/admin/ui.php';

$page_meta = ['title' => 'Documents | Paynancial Admin', 'own_head' => true];
$pdo = db();
$sources = [
    'customer_kyc' => ['Customer eKYC', '/admin/customer-kyc'],
    'customer_application' => ['Customer application', '/admin/customer-applications'],
    'partner_application' => ['Partner application', '/admin/partner-applications'],
    'partner' => ['Partner', '/admin/partner-applications'],
    'employee' => ['Employee (HR)', null],
];
$statuses = ['pending' => ['Pending', 'grey'], 'uploaded' => ['Uploaded', 'blue'], 'under_review' => ['Under review', 'yellow'],
    'verified' => ['Verified', 'green'], 'rejected' => ['Rejected', 'coral'], 'expired' => ['Expired', 'coral']];
$hr = user_can($auth_user, 'documents.view_hr');
$st = adm_table_state(['uploaded' => 'uploaded_at', 'owner' => 'owner_name', 'type' => 'doc_type', 'status' => 'status'], 'uploaded');
$src = (string) ($_GET['source'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$where = ['1=1'];
$params = [];
if (!$hr) {
    $where[] = "source_table <> 'employee'";
}
if (isset($sources[$src]) && ($hr || $src !== 'employee')) {
    $where[] = 'source_table = :src';
    $params['src'] = $src;
}
if (isset($statuses[$status])) {
    $where[] = 'status = :st';
    $params['st'] = $status;
}
if ($st['q'] !== '') {
    $where[] = '(owner_name LIKE :q OR doc_type LIKE :q2)';
    $params['q'] = $params['q2'] = '%' . $st['q'] . '%';
}
$rows = null;
$total = 0;
$counts = [];
try {
    $w = implode(' AND ', $where);
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM admin_documents_v WHERE $w");
    $cnt->execute($params);
    $total = (int) $cnt->fetchColumn();
    $stmt = $pdo->prepare("SELECT doc_key, source_table, owner_type, owner_id, owner_name, doc_type, status, raw_status, uploaded_at, reviewed_at
        FROM admin_documents_v WHERE $w ORDER BY {$st['order_sql']} LIMIT {$st['per']} OFFSET " . (($st['page'] - 1) * $st['per']));
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($pdo->query('SELECT status, COUNT(*) n FROM admin_documents_v' . ($hr ? '' : " WHERE source_table <> 'employee'") . ' GROUP BY status') as $r) {
        $counts[$r['status']] = (int) $r['n'];
    }
} catch (Throwable $e) {
    error_log('[Paynancial admin] documents view unavailable: ' . $e->getMessage());
}
echo adm_page_head('Documents', 'One read-only view of customer, application, partner' . ($hr ? ' and employee' : '') . ' documents. Reviews happen in each owning module.');
?>
<?php if ($rows !== null): ?>
<div class="stat-grid">
  <?php foreach (['under_review', 'uploaded', 'verified', 'rejected'] as $k): ?>
    <div class="stat-card"><span class="label"><?= e($statuses[$k][0]) ?></span><strong class="value"><?= number_format($counts[$k] ?? 0) ?></strong></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<section class="adm-card" aria-label="Documents">
  <form method="get" class="adm-table-bar" role="search">
    <div class="adm-grow"><label class="sr-only" for="dq">Search documents</label><input type="search" id="dq" name="q" value="<?= e($st['q']) ?>" placeholder="Search owner or document type"></div>
    <label class="sr-only" for="ds">Source</label>
    <select id="ds" name="source"><option value="">All sources</option>
      <?php foreach ($sources as $k => [$l]): if ($k === 'employee' && !$hr) { continue; } ?><option value="<?= e($k) ?>" <?= $src === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <label class="sr-only" for="dst">Status</label>
    <select id="dst" name="status"><option value="">All statuses</option>
      <?php foreach ($statuses as $k => [$l]): ?><option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <button class="adm-btn adm-btn--sm" type="submit"><?= adm_icon('filter', 14) ?>Apply</button>
  </form>
  <?php if ($rows === null): ?>
    <div class="adm-state adm-state--error" role="alert"><?= adm_icon('alert', 20) ?><div><strong>Unable to load documents.</strong><p>If the Phase 1 migration has not been applied, the unified view does not exist yet. The error was logged.</p>
      <p><a class="adm-btn adm-btn--sm" href="<?= e(adm_url([])) ?>"><?= adm_icon('refresh', 14) ?>Retry</a></p></div></div>
  <?php elseif (!$rows): ?>
    <div class="adm-state"><?= adm_icon('file', 20) ?><div><strong><?= $st['q'] || $src || $status ? 'No documents match these filters' : 'No documents uploaded yet' ?></strong><p>Uploads and versioning arrive in Phase 6.</p></div></div>
  <?php else: ?>
  <div class="adm-table-wrap"><table class="adm-table">
    <caption class="sr-only">Documents, <?= $total ?> total</caption>
    <thead><tr><?= adm_th('Owner', 'owner', $st) ?><th scope="col">Source</th><?= adm_th('Document type', 'type', $st) ?><?= adm_th('Status', 'status', $st) ?><th scope="col">Version</th><?= adm_th('Uploaded', 'uploaded', $st) ?><th scope="col" class="adm-col-actions"><span class="sr-only">Review</span></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): [$label, $link] = $sources[$r['source_table']] ?? [$r['source_table'], null]; [$sl, $tone] = $statuses[$r['status']] ?? [$r['status'], 'grey']; ?>
      <tr>
        <td><strong><?= e($r['owner_name'] ?: ucfirst($r['owner_type']) . ' #' . (int) $r['owner_id']) ?></strong></td>
        <td><?= e($label) ?></td>
        <td><?= e(ucwords(str_replace(['_', '-'], ' ', (string) $r['doc_type']))) ?></td>
        <td><?= adm_pill($sl, $tone) ?><?php if ($r['raw_status'] !== $r['status']): ?><span class="adm-row-sub">Source status: <?= e($r['raw_status']) ?></span><?php endif; ?></td>
        <td><span class="adm-pill adm-pill--grey" title="Version history starts in Phase 6">v1</span></td>
        <td><?= adm_ago($r['uploaded_at']) ?></td>
        <td class="adm-col-actions"><?php if ($link): ?><a class="adm-btn adm-btn--sm" href="<?= e($link) ?>">Review</a><?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= adm_pagination($total, $st) ?>
  <?php endif; ?>
  <p class="adm-source"><?= adm_icon('database', 12) ?>Source: admin_documents_v (customer_kyc_documents, customer_application_documents, partner_application_documents, partner_documents<?= $hr ? ', employee_documents' : '' ?>). Read-only.</p>
</section>
