<?php
/** HRMS dashboard overview. */
$page_meta = ['title' => 'HRMS Dashboard | Paynancial', 'heading' => 'HRMS Dashboard'];

$pdo = db();

$totalEmployees = (int) $pdo->query("SELECT COUNT(*) FROM employees WHERE employment_status = 'active'")->fetchColumn();

$presentStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE work_date = CURDATE() AND status = 'present'");
$presentStmt->execute();
$presentToday = (int) $presentStmt->fetchColumn();

$leaveRequests = (int) $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'")->fetchColumn();
$openPositions = (int) $pdo->query("SELECT COUNT(*) FROM job_posts WHERE status = 'open'")->fetchColumn();
$newApplications = (int) $pdo->query("SELECT COUNT(*) FROM job_applications WHERE status = 'applied'")->fetchColumn();
$pendingApprovals = $leaveRequests; // leave approvals are the primary HR approval queue
?>
<div class="stat-grid">
  <div class="stat-card"><span class="label">Total Employees</span><strong class="value"><?= $totalEmployees ?></strong></div>
  <div class="stat-card"><span class="label">Present Today</span><strong class="value"><?= $presentToday ?></strong></div>
  <div class="stat-card"><span class="label">Leave Requests</span><strong class="value"><?= $leaveRequests ?></strong></div>
  <div class="stat-card"><span class="label">Open Positions</span><strong class="value"><?= $openPositions ?></strong></div>
  <div class="stat-card"><span class="label">New Applications</span><strong class="value"><?= $newApplications ?></strong></div>
  <div class="stat-card"><span class="label">Pending Approvals</span><strong class="value"><?= $pendingApprovals ?></strong></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Quick Links</h2></div>
  <div class="pill-list">
    <a class="pill" href="/hrms/employees">Employees</a>
    <a class="pill" href="/hrms/recruitment">Recruitment</a>
    <a class="pill" href="/hrms/attendance">Attendance</a>
  </div>
  <p class="text-muted" style="margin-top:18px;font-size:0.85rem;">Payroll, Performance, Announcements and Documents modules are scaffolded for the next release phase — see README for the extension plan.</p>
</div>
