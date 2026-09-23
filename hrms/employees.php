<?php
/** HRMS: employee directory. */
$page_meta = ['title' => 'Employees | Paynancial HRMS', 'heading' => 'Employees'];

$pdo = db();
$search = sanitize_input((string) ($_GET['q'] ?? ''));

$sql = "SELECT e.employee_code, u.full_name, u.email, e.department, e.designation, e.joining_date, e.employment_status
        FROM employees e JOIN users u ON u.id = e.user_id WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= ' AND (u.full_name LIKE :q1 OR e.employee_code LIKE :q2 OR e.department LIKE :q3)';
    $params['q1'] = $params['q2'] = $params['q3'] = '%' . $search . '%';
}
$sql .= ' ORDER BY e.created_at DESC LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();
?>
<div class="panel">
  <div class="panel-head"><h2>Employee Directory</h2></div>
  <form method="get" class="toolbar" style="margin-bottom:18px;">
    <input type="text" name="q" placeholder="Search name, code or department…" value="<?= e($search) ?>">
    <button type="submit" class="btn btn-outline btn-sm">Search</button>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Department</th><th>Designation</th><th>Joined</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (empty($employees)): ?>
          <tr><td colspan="7"><div class="empty-state">No employees found.</div></td></tr>
        <?php else: foreach ($employees as $emp): ?>
          <tr>
            <td><?= e($emp['employee_code']) ?></td>
            <td><?= e($emp['full_name']) ?></td>
            <td><?= e($emp['email']) ?></td>
            <td><?= e($emp['department'] ?? '—') ?></td>
            <td><?= e($emp['designation'] ?? '—') ?></td>
            <td><?= e($emp['joining_date'] ?? '—') ?></td>
            <td><span class="badge <?= $emp['employment_status'] === 'active' ? 'success' : 'neutral' ?>"><?= e(ucfirst(str_replace('_', ' ', $emp['employment_status']))) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
