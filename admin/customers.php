<?php
/**
 * Admin: Customers (CRM, Phase 1 scope). A customer record does NOT need a
 * website login (decision D4): customers.user_id is optional and a portal
 * login stays a separate concern. Creating a customer here never creates
 * credentials. The full CRM (360 view, pipeline) is Phase 4.
 */
require_once __DIR__ . '/../includes/admin/ui.php';

$page_meta = ['title' => 'Customers | Paynancial Admin', 'own_head' => true];
$pdo = db();
$canCreate = user_can($auth_user, 'customers.create');
$sources = ['admin' => 'Added by staff', 'website' => 'Website', 'referral' => 'Referral', 'partner' => 'Partner', 'event' => 'Event', 'other' => 'Other'];
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'create') {
    require_permission($auth_user, 'customers.create'); // also enforced centrally
    $old = [
        'company_name' => trim(sanitize_input((string) ($_POST['company_name'] ?? ''))),
        'contact_name' => trim(sanitize_input((string) ($_POST['contact_name'] ?? ''))),
        'contact_email' => strtolower(trim((string) ($_POST['contact_email'] ?? ''))),
        'contact_mobile' => preg_replace('/[^\d+]/', '', (string) ($_POST['contact_mobile'] ?? '')),
        'source' => (string) ($_POST['source'] ?? 'admin'),
    ];
    if ($old['company_name'] === '' || mb_strlen($old['company_name']) > 150) {
        $errors['company_name'] = 'Enter the business name (up to 150 characters).';
    }
    if ($old['contact_name'] === '' || mb_strlen($old['contact_name']) > 150) {
        $errors['contact_name'] = 'Enter a contact name.';
    }
    if ($old['contact_email'] !== '' && !is_valid_email($old['contact_email'])) {
        $errors['contact_email'] = 'Enter a valid email address.';
    }
    if ($old['contact_mobile'] !== '' && !preg_match('/^\+?\d{8,15}$/', $old['contact_mobile'])) {
        $errors['contact_mobile'] = 'Enter a valid phone number (8–15 digits).';
    }
    if ($old['contact_email'] === '' && $old['contact_mobile'] === '') {
        $errors['contact_email'] = 'Add an email or a phone number.';
    }
    if (!isset($sources[$old['source']])) {
        $old['source'] = 'other';
    }
    if (!$errors && $old['contact_email'] !== '') {
        $dup = $pdo->prepare('SELECT customer_code FROM customers WHERE contact_email = :e LIMIT 1');
        $dup->execute(['e' => $old['contact_email']]);
        if ($code = $dup->fetchColumn()) {
            $errors['contact_email'] = 'A customer with this email already exists (' . $code . ').';
        }
    }
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $code = generate_sequential_code($pdo, 'customers', 'customer_code', 'PYN-CUS');
            $pdo->prepare('INSERT INTO customers (user_id, customer_code, company_name, contact_name, contact_email, contact_mobile, source, created_by)
                           VALUES (NULL, :code, :company, :name, :email, :mobile, :source, :by)')
                ->execute(['code' => $code, 'company' => $old['company_name'], 'name' => $old['contact_name'], 'email' => $old['contact_email'] ?: null,
                    'mobile' => $old['contact_mobile'] ?: null, 'source' => $old['source'], 'by' => (int) $auth_user['id']]);
            $id = (int) $pdo->lastInsertId();
            audit_write($pdo, 'customer.created', 'customer', $id, [], ['customer_code' => $code] + $old + ['portal_login' => 'none'], ['name' => $old['company_name']]);
            $pdo->commit();
            flash('adm_ok', 'Customer ' . $code . ' added. No portal login was created.');
            header('Location: /admin/customers', true, 303);
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[Paynancial admin] customer create failed: ' . $e->getMessage());
            $errors['_'] = 'The customer could not be saved. Nothing was changed.';
        }
    }
}

$st = adm_table_state(['created' => 'c.created_at', 'company' => 'c.company_name', 'code' => 'c.customer_code'], 'created');
$portal = (string) ($_GET['portal'] ?? '');
$where = ['1=1'];
$params = [];
if ($st['q'] !== '') {
    $where[] = '(c.company_name LIKE :q OR c.customer_code LIKE :q2 OR c.contact_name LIKE :q3 OR c.contact_email LIKE :q4 OR u.email LIKE :q5)';
    foreach (['q', 'q2', 'q3', 'q4', 'q5'] as $k) {
        $params[$k] = '%' . $st['q'] . '%';
    }
}
if ($portal === 'yes') {
    $where[] = 'c.user_id IS NOT NULL';
} elseif ($portal === 'no') {
    $where[] = 'c.user_id IS NULL';
}
$rows = null;
$total = 0;
try {
    $w = implode(' AND ', $where);
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM customers c LEFT JOIN users u ON u.id = c.user_id WHERE $w");
    $cnt->execute($params);
    $total = (int) $cnt->fetchColumn();
    $stmt = $pdo->prepare("SELECT c.id, c.customer_code, c.company_name, c.contact_name, c.contact_email, c.contact_mobile, c.source, c.kyc_status, c.status,
            c.created_at, c.user_id, u.email AS login_email, u.full_name AS login_name
        FROM customers c LEFT JOIN users u ON u.id = c.user_id WHERE $w ORDER BY {$st['order_sql']} LIMIT {$st['per']} OFFSET " . (($st['page'] - 1) * $st['per']));
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[Paynancial admin] customers unavailable: ' . $e->getMessage());
}
$showForm = $canCreate && (isset($_GET['new']) || $errors);
$ok = flash('adm_ok');

echo adm_page_head('Customers', 'Business customers. A portal login is optional and separate.',
    $canCreate ? '<a class="adm-btn adm-btn--primary" href="/admin/customers?new=1#new-customer">' . adm_icon('plus', 15) . 'Add Customer</a>' : '');
?>
<?php if ($ok): ?><div class="adm-alert adm-alert--ok" role="status"><?= adm_icon('check', 16) ?><?= e($ok) ?></div><?php endif; ?>

<?php if ($showForm): ?>
<section class="adm-card" id="new-customer" aria-labelledby="nc-h">
  <div class="adm-card-h"><div><h2 id="nc-h">Add customer</h2><p>Creates a CRM record only. No login or password is created.</p></div></div>
  <?php if (!empty($errors['_'])): ?><div class="adm-alert adm-alert--err" role="alert"><?= e($errors['_']) ?></div><?php endif; ?>
  <form method="post" action="/admin/customers" novalidate>
    <?= csrf_field() ?><input type="hidden" name="op" value="create">
    <div class="adm-form-grid">
      <?php foreach (['company_name' => ['Business name', 'text', true], 'contact_name' => ['Contact person', 'text', true], 'contact_email' => ['Email', 'email', false], 'contact_mobile' => ['Phone', 'tel', false]] as $f => [$label, $type, $req]): ?>
        <div class="adm-field">
          <label for="c-<?= $f ?>"><?= e($label) ?><?= $req ? ' <span aria-hidden="true">*</span>' : '' ?></label>
          <input type="<?= $type ?>" id="c-<?= $f ?>" name="<?= $f ?>" value="<?= e($old[$f] ?? '') ?>" <?= $req ? 'required' : '' ?>
                 <?= isset($errors[$f]) ? 'aria-invalid="true" aria-describedby="c-' . $f . '-err"' : '' ?>>
          <?php if (isset($errors[$f])): ?><small id="c-<?= $f ?>-err" style="color:var(--a-coral);font-weight:600"><?= e($errors[$f]) ?></small><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <div class="adm-field"><label for="c-source">Source</label>
        <select id="c-source" name="source"><?php foreach ($sources as $k => $l): ?><option value="<?= e($k) ?>" <?= ($old['source'] ?? 'admin') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    </div>
    <p style="margin:16px 0 0;display:flex;gap:8px"><button class="adm-btn adm-btn--primary" type="submit">Save customer</button><a class="adm-btn" href="/admin/customers">Cancel</a></p>
  </form>
</section>
<?php endif; ?>

<section class="adm-card" aria-label="Customer list">
  <form method="get" class="adm-table-bar" role="search">
    <div class="adm-grow"><label class="sr-only" for="cq">Search customers</label><input type="search" id="cq" name="q" value="<?= e($st['q']) ?>" placeholder="Search name, code, contact or email"></div>
    <label class="sr-only" for="cp">Portal login</label>
    <select id="cp" name="portal"><option value="">Any portal status</option><option value="yes" <?= $portal === 'yes' ? 'selected' : '' ?>>Has portal login</option><option value="no" <?= $portal === 'no' ? 'selected' : '' ?>>No portal login</option></select>
    <button class="adm-btn adm-btn--sm" type="submit"><?= adm_icon('filter', 14) ?>Apply</button>
    <?= adm_columns_menu('cust-t', ['contact' => 'Contact', 'source' => 'Source', 'kyc' => 'KYC', 'portal' => 'Portal login', 'created' => 'Created']) ?>
  </form>
  <?php if ($rows === null): ?>
    <?= adm_widget_state(['state' => 'error', 'key' => '']) ?>
  <?php elseif (!$rows): ?>
    <div class="adm-state"><?= adm_icon('building', 20) ?><div><strong><?= $st['q'] || $portal ? 'No customers match these filters' : 'No customers yet' ?></strong>
      <?php if ($canCreate && !$st['q'] && !$portal): ?><p><a class="adm-btn adm-btn--sm adm-btn--primary" href="/admin/customers?new=1#new-customer"><?= adm_icon('plus', 14) ?>Add Customer</a></p><?php endif; ?></div></div>
  <?php else: ?>
  <div class="adm-table-wrap"><table class="adm-table" id="cust-t">
    <caption class="sr-only">Customers, <?= $total ?> total</caption>
    <thead><tr>
      <?= adm_th('Code', 'code', $st) ?><?= adm_th('Business', 'company', $st) ?>
      <th scope="col" data-col="contact">Contact</th><th scope="col" data-col="source">Source</th><th scope="col" data-col="kyc">KYC</th>
      <th scope="col" data-col="portal">Portal login</th><?= adm_th('Created', 'created', $st, 'created') ?>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><span class="adm-id"><?= e($r['customer_code']) ?></span></td>
        <td><strong><?= e($r['company_name'] ?: '—') ?></strong></td>
        <td data-col="contact"><?= e($r['contact_name'] ?: ($r['login_name'] ?? '—')) ?><span class="adm-row-sub"><?= e($r['contact_email'] ?: ($r['login_email'] ?? $r['contact_mobile'] ?? '')) ?></span></td>
        <td data-col="source"><?= e($sources[$r['source']] ?? ($r['source'] ? ucfirst($r['source']) : 'Self sign-up')) ?></td>
        <td data-col="kyc"><?= adm_pill(ucwords(str_replace('_', ' ', $r['kyc_status'])), match ($r['kyc_status']) { 'verified' => 'green', 'rejected' => 'coral', 'pending' => 'yellow', default => 'grey' }) ?></td>
        <td data-col="portal"><?= $r['user_id'] ? adm_pill('Linked', 'blue') : adm_pill('None', 'grey', false) ?></td>
        <td data-col="created"><?= adm_ago($r['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= adm_pagination($total, $st) ?>
  <?php endif; ?>
  <p class="adm-source"><?= adm_icon('database', 12) ?>Source: customers (+ users for linked portal logins)</p>
</section>
