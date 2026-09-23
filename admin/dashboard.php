<?php
/** Admin dashboard overview. */
$page_meta = ['title' => 'Admin Dashboard | Paynancial', 'heading' => 'Admin Dashboard'];

$pdo = db();
$counts = [
    'Users'             => "SELECT COUNT(*) FROM users",
    'Customers'         => "SELECT COUNT(*) FROM customers",
    'Partners'          => "SELECT COUNT(*) FROM partners",
    'Employees'         => "SELECT COUNT(*) FROM employees",
    'Transactions'      => "SELECT COUNT(*) FROM transactions",
    'Enquiries'         => "SELECT COUNT(*) FROM enquiries WHERE status = 'new'",
    'Support Tickets'   => "SELECT COUNT(*) FROM support_tickets WHERE status != 'closed'",
    'HRMS Applications' => "SELECT COUNT(*) FROM job_applications",
];
$stats = [];
foreach ($counts as $label => $sql) {
    try { $stats[$label] = (int) $pdo->query($sql)->fetchColumn(); }
    catch (Throwable $e) { $stats[$label] = 0; }
}
?>
<div class="stat-grid">
  <?php foreach ($stats as $label => $value): ?>
    <div class="stat-card"><span class="label"><?= e($label) ?></span><strong class="value"><?= $value ?></strong></div>
  <?php endforeach; ?>
</div>

<div class="panel">
  <div class="panel-head"><h2>Management</h2></div>
  <div class="pill-list">
    <a class="pill" href="/admin/users">User Management</a>
    <a class="pill" href="/admin/transactions">Transactions</a>
    <a class="pill" href="/admin/enquiries">Enquiries</a>
    <a class="pill" href="/admin/cms">CMS</a>
  </div>
  <p class="text-muted" style="margin-top:18px;font-size:0.85rem;">Partner/Employee management, Support, Blog, Careers and Security Logs modules share the same table structures and can be added as additional admin pages without changing the platform architecture.</p>
</div>
