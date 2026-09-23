<?php
/** HRMS: attendance overview for a given date. */
$page_meta = ['title' => 'Attendance | Paynancial HRMS', 'heading' => 'Attendance'];

$pdo = db();
$date = sanitize_input((string) ($_GET['date'] ?? date('Y-m-d')));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $date = date('Y-m-d'); }

$stmt = $pdo->prepare(
    "SELECT e.employee_code, u.full_name, a.check_in, a.check_out, a.status
     FROM employees e JOIN users u ON u.id = e.user_id
     LEFT JOIN attendance a ON a.employee_id = e.id AND a.work_date = :date
     WHERE e.employment_status = 'active' ORDER BY u.full_name LIMIT 200"
);
$stmt->execute(['date' => $date]);
$rows = $stmt->fetchAll();
?>
<div class="panel">
  <div class="panel-head"><h2>Attendance</h2></div>
  <form method="get" class="toolbar" style="margin-bottom:18px;">
    <input type="date" name="date" value="<?= e($date) ?>">
    <button type="submit" class="btn btn-outline btn-sm">View</button>
  </form>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Employee ID</th><th>Name</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?><tr><td colspan="5"><div class="empty-state">No active employees found.</div></td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr>
            <td><?= e($row['employee_code']) ?></td>
            <td><?= e($row['full_name']) ?></td>
            <td><?= e($row['check_in'] ?? '—') ?></td>
            <td><?= e($row['check_out'] ?? '—') ?></td>
            <td><span class="badge <?= $row['status'] === 'present' ? 'success' : 'neutral' ?>"><?= e(ucfirst(str_replace('_', ' ', $row['status'] ?? 'not marked'))) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
