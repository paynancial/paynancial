<?php
/**
 * Admin: Enquiries — enterprise table (search, filters, sort, pagination,
 * column visibility, bulk actions, row actions), create and CSV export.
 * View: enquiries.view · status/assignment: enquiries.manage ·
 * create: enquiries.create · export: enquiries.export. All changes audited.
 */
require_once __DIR__ . '/../includes/admin/ui.php';
require_once __DIR__ . '/../includes/admin/dashboard-render.php';

$page_meta = ['title' => 'Enquiries | Paynancial Admin', 'own_head' => true];
$pdo = db();
$me = $auth_user;
$types = ['sales' => 'Sales', 'partner' => 'Partner', 'support' => 'Support', 'general' => 'General', 'career' => 'Career'];
$statuses = ['new' => 'New', 'in_progress' => 'In progress', 'responded' => 'Responded', 'closed' => 'Closed'];
$canManage = user_can($me, 'enquiries.manage');
$canCreate = user_can($me, 'enquiries.create');
$canExport = user_can($me, 'enquiries.export');
$errors = [];
$old = [];

$done = static function (string $msg, bool $ok = true): never {
    flash($ok ? 'adm_ok' : 'adm_err', $msg);
    $ret = (string) ($_POST['return'] ?? '/admin/enquiries');
    header('Location: ' . (str_starts_with($ret, '/admin/enquiries') ? $ret : '/admin/enquiries'), true, 303);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = (string) ($_POST['op'] ?? '');
    if ($op === 'create') {
        require_permission($me, 'enquiries.create');
        foreach (['type', 'name', 'company', 'email', 'mobile', 'subject', 'message'] as $f) {
            $old[$f] = trim(sanitize_input((string) ($_POST[$f] ?? '')));
        }
        if (!isset($types[$old['type']])) {
            $errors['type'] = 'Choose a type.';
        }
        if ($old['name'] === '') {
            $errors['name'] = 'Enter a name.';
        }
        if ($old['email'] !== '' && !is_valid_email($old['email'])) {
            $errors['email'] = 'Enter a valid email.';
        }
        if ($old['email'] === '' && $old['mobile'] === '') {
            $errors['email'] = 'Add an email or phone number.';
        }
        if ($old['message'] === '') {
            $errors['message'] = 'Add a short note about the enquiry.';
        }
        if (!$errors) {
            try {
                $pdo->beginTransaction();
                $code = generate_enquiry_code($pdo);
                $pdo->prepare('INSERT INTO enquiries (enquiry_code, type, name, company, email, mobile, subject, message, status, assigned_to, ip_address)
                               VALUES (:c, :t, :n, :co, :e, :m, :s, :msg, "new", :a, :ip)')
                    ->execute(['c' => $code, 't' => $old['type'], 'n' => mb_substr($old['name'], 0, 150), 'co' => $old['company'] ?: null,
                        'e' => $old['email'] ?: '', 'm' => $old['mobile'] ?: null, 's' => $old['subject'] ?: null, 'msg' => $old['message'],
                        'a' => (int) $me['id'], 'ip' => client_ip()]);
                $id = (int) $pdo->lastInsertId();
                audit_write($pdo, 'enquiry.created', 'enquiry', $id, [], ['enquiry_code' => $code, 'type' => $old['type'], 'name' => $old['name'], 'company' => $old['company'], 'source' => 'admin'], ['name' => $code]);
                $pdo->commit();
                $done('Enquiry ' . $code . ' created and assigned to you.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('[Paynancial admin] enquiry create failed: ' . $e->getMessage());
                $errors['_'] = 'The enquiry could not be saved. Nothing was changed.';
            }
        }
    } elseif ($op === 'status' || $op === 'assign_me' || $op === 'bulk') {
        require_permission($me, 'enquiries.manage');
        $ids = $op === 'bulk' ? array_slice(array_map('intval', (array) ($_POST['ids'] ?? [])), 0, 200) : [(int) ($_POST['enquiry_id'] ?? 0)];
        $ids = array_values(array_filter(array_unique($ids)));
        $action = $op === 'bulk' ? (string) ($_POST['bulk_action'] ?? '') : ($op === 'assign_me' ? 'assign_me' : 'status:' . ($_POST['status'] ?? ''));
        $set = null;
        if (str_starts_with($action, 'status:') && isset($statuses[substr($action, 7)])) {
            $set = ['status' => substr($action, 7)];
        } elseif ($action === 'assign_me') {
            $set = ['assigned_to' => (int) $me['id']];
        } elseif ($action === 'unassign') {
            $set = ['assigned_to' => null];
        }
        if (!$ids || $set === null) {
            $done('Choose at least one enquiry and an action.', false);
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $before = $pdo->prepare("SELECT id, enquiry_code, status, assigned_to FROM enquiries WHERE id IN ($in)");
        $before->execute($ids);
        $rows = $before->fetchAll();
        $col = array_key_first($set);
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE enquiries SET $col = ? WHERE id IN ($in)")->execute(array_merge([$set[$col]], $ids));
        foreach ($rows as $r) {
            audit_write($pdo, $op === 'bulk' ? 'enquiry.bulk_updated' : 'enquiry.updated', 'enquiry', (int) $r['id'],
                [$col => $r[$col]], [$col => $set[$col]], ['name' => $r['enquiry_code']]);
        }
        $pdo->commit();
        $done(count($rows) . ' enquir' . (count($rows) === 1 ? 'y' : 'ies') . ' updated.');
    }
}

// ------------------------------------------------------------------ query
$st = adm_table_state(['created' => 'e.created_at', 'updated' => 'e.updated_at', 'name' => 'e.name', 'status' => 'e.status', 'type' => 'e.type'], 'created');
$f = [
    'type' => isset($types[$_GET['type'] ?? '']) ? $_GET['type'] : '',
    'status' => isset($statuses[$_GET['status'] ?? '']) ? $_GET['status'] : '',
    'assigned' => in_array($_GET['assigned'] ?? '', ['me', 'none'], true) ? $_GET['assigned'] : '',
    'from' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['from'] ?? '')) ? $_GET['from'] : '',
    'to' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['to'] ?? '')) ? $_GET['to'] : '',
];
$where = ['1=1'];
$params = [];
if ($st['q'] !== '') {
    $where[] = '(e.enquiry_code LIKE :q1 OR e.name LIKE :q2 OR e.company LIKE :q3 OR e.email LIKE :q4 OR e.subject LIKE :q5)';
    foreach (['q1', 'q2', 'q3', 'q4', 'q5'] as $k) {
        $params[$k] = '%' . $st['q'] . '%';
    }
}
foreach (['type', 'status'] as $k) {
    if ($f[$k] !== '') {
        $where[] = "e.$k = :$k";
        $params[$k] = $f[$k];
    }
}
if ($f['assigned'] === 'me') {
    $where[] = 'e.assigned_to = :me';
    $params['me'] = (int) $me['id'];
} elseif ($f['assigned'] === 'none') {
    $where[] = 'e.assigned_to IS NULL';
}
if ($f['from'] !== '') {
    $where[] = 'e.created_at >= :from';
    $params['from'] = $f['from'] . ' 00:00:00';
}
if ($f['to'] !== '') {
    $where[] = 'e.created_at <= :to';
    $params['to'] = $f['to'] . ' 23:59:59';
}
$w = implode(' AND ', $where);
$select = "SELECT e.id, e.enquiry_code, e.type, e.name, e.company, e.email, e.mobile, e.subject, e.status, e.created_at, e.updated_at, u.full_name AS assignee,
           (SELECT cs.form_type FROM contact_submissions cs WHERE cs.enquiry_id = e.id ORDER BY cs.id LIMIT 1) AS source
           FROM enquiries e LEFT JOIN users u ON u.id = e.assigned_to WHERE $w";

// CSV export (enquiries.export) — same filters, audited, spreadsheet-formula safe.
if (($_GET['export'] ?? '') === 'csv') {
    if (!$canExport) {
        http_response_code(403);
        audit('access.denied', 'admin_page', null, [], [], ['page' => 'enquiries', 'permission' => 'enquiries.export']);
        echo adm_widget_state(['state' => 'restricted']);
        return;
    }
    $stmt = $pdo->prepare($select . ' ORDER BY ' . $st['order_sql'] . ' LIMIT 5000');
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    audit('enquiries.exported', 'enquiry', null, [], [], ['rows' => count($rows), 'filters' => array_filter($f + ['q' => $st['q']])]);
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="enquiries-' . date('Ymd-His') . '.csv"');
    header('Cache-Control: no-store');
    $out = fopen('php://output', 'w');
    $safe = static fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
    fputcsv($out, ['Enquiry ID', 'Type', 'Name', 'Company', 'Email', 'Phone', 'Subject', 'Source', 'Status', 'Assigned', 'Created', 'Updated'], ',', '"', '');
    foreach ($rows as $r) {
        fputcsv($out, array_map($safe, [$r['enquiry_code'], $r['type'], $r['name'], $r['company'], $r['email'], $r['mobile'], $r['subject'],
            admin_enquiry_source_label($r['source']), $r['status'], $r['assignee'] ?? '', $r['created_at'], $r['updated_at']]), ',', '"', '');
    }
    fclose($out);
    exit;
}

$rows = null;
$total = 0;
try {
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM enquiries e WHERE $w");
    $cnt->execute($params);
    $total = (int) $cnt->fetchColumn();
    $stmt = $pdo->prepare($select . ' ORDER BY ' . $st['order_sql'] . ' LIMIT ' . $st['per'] . ' OFFSET ' . (($st['page'] - 1) * $st['per']));
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[Paynancial admin] enquiries unavailable: ' . $e->getMessage());
}
$showForm = $canCreate && (isset($_GET['new']) || $errors);
$ok = flash('adm_ok');
$err = flash('adm_err');
$return = $_SERVER['REQUEST_URI'] ?? '/admin/enquiries';
$filtered = $st['q'] !== '' || array_filter($f);

$actions = ($canExport ? '<a class="adm-btn" href="' . e(adm_url(['export' => 'csv', 'page' => null])) . '">' . adm_icon('download', 15) . 'Export CSV</a>' : '')
    . ($canCreate ? '<a class="adm-btn adm-btn--primary" href="/admin/enquiries?new=1#new-enquiry">' . adm_icon('plus', 15) . 'New Enquiry</a>' : '');
echo adm_page_head('Enquiries', 'Leads and requests from the website and staff.', $actions);
?>
<?php if ($ok): ?><div class="adm-alert adm-alert--ok" role="status"><?= adm_icon('check', 16) ?><?= e($ok) ?></div><?php endif; ?>
<?php if ($err): ?><div class="adm-alert adm-alert--err" role="alert"><?= adm_icon('alert', 16) ?><?= e($err) ?></div><?php endif; ?>

<?php if ($showForm): ?>
<section class="adm-card" id="new-enquiry" aria-labelledby="ne-h">
  <div class="adm-card-h"><div><h2 id="ne-h">New enquiry</h2><p>Logged as source “Direct / admin” and assigned to you.</p></div></div>
  <?php if (!empty($errors['_'])): ?><div class="adm-alert adm-alert--err" role="alert"><?= e($errors['_']) ?></div><?php endif; ?>
  <form method="post" action="/admin/enquiries" novalidate>
    <?= csrf_field() ?><input type="hidden" name="op" value="create">
    <div class="adm-form-grid">
      <div class="adm-field"><label for="ne-type">Type <span aria-hidden="true">*</span></label>
        <select id="ne-type" name="type" required><?php foreach ($types as $k => $l): ?><option value="<?= e($k) ?>" <?= ($old['type'] ?? 'sales') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
      <?php foreach (['name' => ['Name', 'text', true], 'company' => ['Company', 'text', false], 'email' => ['Email', 'email', false], 'mobile' => ['Phone', 'tel', false], 'subject' => ['Subject', 'text', false]] as $k => [$label, $type, $req]): ?>
        <div class="adm-field"><label for="ne-<?= $k ?>"><?= e($label) ?><?= $req ? ' <span aria-hidden="true">*</span>' : '' ?></label>
          <input type="<?= $type ?>" id="ne-<?= $k ?>" name="<?= $k ?>" value="<?= e($old[$k] ?? '') ?>" <?= $req ? 'required' : '' ?> <?= isset($errors[$k]) ? 'aria-invalid="true" aria-describedby="ne-' . $k . '-err"' : '' ?>>
          <?php if (isset($errors[$k])): ?><small id="ne-<?= $k ?>-err" style="color:var(--a-coral);font-weight:600"><?= e($errors[$k]) ?></small><?php endif; ?></div>
      <?php endforeach; ?>
    </div>
    <div class="adm-field" style="margin-top:16px"><label for="ne-message">Notes <span aria-hidden="true">*</span></label>
      <textarea id="ne-message" name="message" rows="3" required <?= isset($errors['message']) ? 'aria-invalid="true" aria-describedby="ne-message-err"' : '' ?>><?= e($old['message'] ?? '') ?></textarea>
      <?php if (isset($errors['message'])): ?><small id="ne-message-err" style="color:var(--a-coral);font-weight:600"><?= e($errors['message']) ?></small><?php endif; ?></div>
    <p style="margin:16px 0 0;display:flex;gap:8px"><button class="adm-btn adm-btn--primary" type="submit">Create enquiry</button><a class="adm-btn" href="/admin/enquiries">Cancel</a></p>
  </form>
</section>
<?php endif; ?>

<section class="adm-card" aria-label="Enquiry list">
  <form method="get" class="adm-table-bar" role="search">
    <div class="adm-grow"><label class="sr-only" for="eq">Search enquiries</label><input type="search" id="eq" name="q" value="<?= e($st['q']) ?>" placeholder="Search ID, name, company, email or subject"></div>
    <label class="sr-only" for="ef-type">Type</label>
    <select id="ef-type" name="type"><option value="">All types</option><?php foreach ($types as $k => $l): ?><option value="<?= e($k) ?>" <?= $f['type'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <label class="sr-only" for="ef-status">Status</label>
    <select id="ef-status" name="status"><option value="">All statuses</option><?php foreach ($statuses as $k => $l): ?><option value="<?= e($k) ?>" <?= $f['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <label class="sr-only" for="ef-as">Assigned</label>
    <select id="ef-as" name="assigned"><option value="">Anyone</option><option value="me" <?= $f['assigned'] === 'me' ? 'selected' : '' ?>>Assigned to me</option><option value="none" <?= $f['assigned'] === 'none' ? 'selected' : '' ?>>Unassigned</option></select>
    <label class="sr-only" for="ef-from">From date</label><input type="date" id="ef-from" name="from" value="<?= e($f['from']) ?>" title="From">
    <label class="sr-only" for="ef-to">To date</label><input type="date" id="ef-to" name="to" value="<?= e($f['to']) ?>" title="To">
    <button class="adm-btn adm-btn--sm" type="submit"><?= adm_icon('filter', 14) ?>Apply</button>
    <?php if ($filtered): ?><a class="adm-btn adm-btn--sm adm-btn--ghost" href="/admin/enquiries">Clear</a><?php endif; ?>
    <?= adm_columns_menu('enq-t', ['type' => 'Type', 'source' => 'Source', 'assigned' => 'Assigned', 'updated' => 'Updated', 'created' => 'Created']) ?>
  </form>

  <?php if ($rows === null): ?>
    <div class="adm-state adm-state--error" role="alert"><?= adm_icon('alert', 20) ?><div><strong>Unable to load enquiries.</strong><p>The error was logged.</p><p><a class="adm-btn adm-btn--sm" href="<?= e(adm_url([])) ?>"><?= adm_icon('refresh', 14) ?>Retry</a></p></div></div>
  <?php elseif (!$rows): ?>
    <div class="adm-state"><?= adm_icon('inbox', 20) ?><div><strong><?= $filtered ? 'No enquiries match these filters' : 'No enquiries yet' ?></strong>
      <?php if ($canCreate && !$filtered): ?><p><a class="adm-btn adm-btn--sm adm-btn--primary" href="/admin/enquiries?new=1#new-enquiry"><?= adm_icon('plus', 14) ?>New Enquiry</a></p><?php endif; ?></div></div>
  <?php else: ?>
  <form method="post" id="enq-bulk-form">
    <?= csrf_field() ?><input type="hidden" name="op" value="bulk"><input type="hidden" name="return" value="<?= e($return) ?>">
    <?php if ($canManage): ?>
    <div class="adm-bulk" data-bulk-for="enq-t" role="region" aria-label="Bulk actions">
      <strong data-bulk-count>0 selected</strong>
      <label class="sr-only" for="bulk-act">Bulk action</label>
      <select id="bulk-act" name="bulk_action">
        <?php foreach ($statuses as $k => $l): ?><option value="status:<?= e($k) ?>">Set status: <?= e($l) ?></option><?php endforeach; ?>
        <option value="assign_me">Assign to me</option><option value="unassign">Unassign</option>
      </select>
      <button class="adm-btn adm-btn--sm adm-btn--primary" type="submit">Apply to selected</button>
    </div>
    <?php endif; ?>
  </form>
  <div class="adm-table-wrap"><table class="adm-table" id="enq-t">
    <caption class="sr-only">Enquiries, <?= $total ?> total, sorted by <?= e($st['sort']) ?></caption>
    <thead><tr>
      <?php if ($canManage): ?><th scope="col" class="adm-col-check"><input type="checkbox" data-select-all="enq-t" aria-label="Select all on this page"></th><?php endif; ?>
      <th scope="col">ID</th><?= adm_th('Name / Company', 'name', $st) ?><?= str_replace('<th scope="col"', '<th scope="col" data-col="type"', adm_th('Type', 'type', $st)) ?>
      <th scope="col" data-col="source">Source</th><?= adm_th('Status', 'status', $st) ?><th scope="col" data-col="assigned">Assigned</th>
      <?= adm_th('Updated', 'updated', $st, 'updated') ?><?= adm_th('Created', 'created', $st, 'created') ?>
      <th scope="col" class="adm-col-actions"><span class="sr-only">Actions</span></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $id = (int) $r['id']; ?>
      <tr>
        <?php if ($canManage): ?><td class="adm-col-check"><input type="checkbox" form="enq-bulk-form" name="ids[]" value="<?= $id ?>" data-row-check aria-label="Select <?= e($r['enquiry_code']) ?>"></td><?php endif; ?>
        <td><span class="adm-id"><?= e($r['enquiry_code']) ?></span></td>
        <td><strong><?= e($r['name']) ?></strong><span class="adm-row-sub"><?= e($r['company'] ?: ($r['email'] ?: ($r['mobile'] ?? ''))) ?></span></td>
        <td data-col="type"><?= e($types[$r['type']] ?? $r['type']) ?></td>
        <td data-col="source"><?= e(admin_enquiry_source_label($r['source'])) ?></td>
        <td>
          <?php if ($canManage): ?>
            <form method="post" class="toolbar"><?= csrf_field() ?><input type="hidden" name="op" value="status"><input type="hidden" name="enquiry_id" value="<?= $id ?>"><input type="hidden" name="return" value="<?= e($return) ?>">
              <label class="sr-only" for="st-<?= $id ?>">Status of <?= e($r['enquiry_code']) ?></label>
              <select id="st-<?= $id ?>" name="status" class="js-auto-submit" style="min-height:30px;padding:3px 8px;font-size:12.5px">
                <?php foreach ($statuses as $k => $l): ?><option value="<?= e($k) ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
              </select><noscript><button class="adm-btn adm-btn--sm" type="submit">Set</button></noscript></form>
          <?php else: ?><?= admin_enquiry_status_pill($r['status']) ?><?php endif; ?>
        </td>
        <td data-col="assigned"><?= e($r['assignee'] ?? 'Unassigned') ?></td>
        <td data-col="updated"><?= adm_ago($r['updated_at']) ?></td>
        <td data-col="created"><?= adm_ago($r['created_at']) ?></td>
        <td class="adm-col-actions">
          <div class="adm-rowmenu"><button type="button" class="adm-icon-btn" data-pop="em-<?= $id ?>" aria-expanded="false" aria-controls="em-<?= $id ?>" aria-label="More actions for <?= e($r['enquiry_code']) ?>"><?= adm_icon('dots', 18) ?></button>
            <div class="adm-pop" id="em-<?= $id ?>" hidden>
              <p class="adm-pop-note" style="margin:4px 8px 8px"><?= e($r['subject'] ?: 'No subject') ?><br><?= e($r['email']) ?><?= $r['mobile'] ? ' · ' . e($r['mobile']) : '' ?></p>
              <?php if ($r['email']): ?><a class="adm-menu-item" href="mailto:<?= e($r['email']) ?>"><?= adm_icon('mail', 15) ?>Email contact</a><?php endif; ?>
              <?php if ($canManage): ?>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="assign_me"><input type="hidden" name="enquiry_id" value="<?= $id ?>"><input type="hidden" name="return" value="<?= e($return) ?>">
                  <button type="submit" class="adm-menu-item"><?= adm_icon('users', 15) ?>Assign to me</button></form>
              <?php endif; ?>
            </div></div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= adm_pagination($total, $st) ?>
  <?php endif; ?>
  <p class="adm-source"><?= adm_icon('database', 12) ?>Source: enquiries + contact_submissions (source) + users (assignee)</p>
</section>
