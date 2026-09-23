<?php
/** HRMS: job postings + candidate applications, incl. CV upload endpoint handling. */
$page_meta = ['title' => 'Recruitment | Paynancial HRMS', 'heading' => 'Recruitment'];

$pdo = db();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_job') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    } else {
        $title = sanitize_input((string) ($_POST['title'] ?? ''));
        $department = sanitize_input((string) ($_POST['department'] ?? ''));
        $location = sanitize_input((string) ($_POST['location'] ?? ''));
        $empType = sanitize_input((string) ($_POST['employment_type'] ?? 'full_time'));
        $description = sanitize_input((string) ($_POST['description'] ?? ''));

        if ($title === '' || $description === '') {
            $errors[] = 'Job title and description are required.';
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO job_posts (title, department, location, employment_type, description, status, posted_by)
                 VALUES (:title, :dept, :loc, :type, :desc, "open", :uid)'
            );
            $ins->execute([
                'title' => $title, 'dept' => $department, 'loc' => $location,
                'type' => $empType, 'desc' => $description, 'uid' => $auth_user['id'],
            ]);
            $success = true;
        }
    }
}

$jobs = $pdo->query('SELECT id, title, department, location, status, created_at FROM job_posts ORDER BY created_at DESC LIMIT 30')->fetchAll();

$applications = $pdo->query(
    'SELECT a.application_code, a.full_name, a.email, a.status, a.created_at, j.title AS job_title
     FROM job_applications a JOIN job_posts j ON j.id = a.job_post_id ORDER BY a.created_at DESC LIMIT 30'
)->fetchAll();
?>
<div class="panel">
  <div class="panel-head"><h2>Post a Job Opening</h2></div>
  <?php if ($success): ?><div class="badge success" style="margin-bottom:16px;">Job posted</div><?php endif; ?>
  <?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:12px;"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" style="display:grid;gap:14px;grid-template-columns:1fr 1fr;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create_job">
    <div class="field"><label>Job Title</label><input type="text" name="title" required></div>
    <div class="field"><label>Department</label><input type="text" name="department"></div>
    <div class="field"><label>Location</label><input type="text" name="location"></div>
    <div class="field"><label>Employment Type</label>
      <select name="employment_type"><option value="full_time">Full-time</option><option value="part_time">Part-time</option><option value="contract">Contract</option><option value="internship">Internship</option></select>
    </div>
    <div class="field" style="grid-column:1/-1;"><label>Description</label><textarea name="description" rows="4" style="padding:12px 14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);"></textarea></div>
    <div style="grid-column:1/-1;"><button type="submit" class="btn btn-primary">Post Job</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>Job Postings</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Title</th><th>Department</th><th>Location</th><th>Status</th><th>Posted</th></tr></thead>
      <tbody>
        <?php if (empty($jobs)): ?><tr><td colspan="5"><div class="empty-state">No job postings yet.</div></td></tr>
        <?php else: foreach ($jobs as $job): ?>
          <tr>
            <td><?= e($job['title']) ?></td>
            <td><?= e($job['department'] ?? '—') ?></td>
            <td><?= e($job['location'] ?? '—') ?></td>
            <td><span class="badge <?= $job['status'] === 'open' ? 'success' : 'neutral' ?>"><?= e(ucfirst($job['status'])) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $job['created_at']))) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Candidate Applications</h2></div>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Application ID</th><th>Candidate</th><th>Job</th><th>Status</th><th>Applied</th></tr></thead>
      <tbody>
        <?php if (empty($applications)): ?><tr><td colspan="5"><div class="empty-state">No applications yet.</div></td></tr>
        <?php else: foreach ($applications as $app): ?>
          <tr>
            <td><?= e($app['application_code']) ?></td>
            <td><?= e($app['full_name']) ?> · <?= e($app['email']) ?></td>
            <td><?= e($app['job_title']) ?></td>
            <td><span class="badge info"><?= e(ucfirst($app['status'])) ?></span></td>
            <td><?= e(date('d M Y', strtotime((string) $app['created_at']))) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
