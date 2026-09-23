<?php
/** Partner Hub — Profile: account settings (password) and, for owner/admin, the business profile. */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Profile | Paynancial Partner Hub', 'heading' => 'Profile'];

$pdo = db();
$canEditBusiness = in_array($context['role_slug'], ['owner', 'admin'], true);
$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $action = (string) ($_POST['form_action'] ?? '');

        if ($action === 'change_password' || $action === 'logout_other_devices') {
            $result = handle_security_panel_post($pdo, $auth_user, $action);
            $errors = $result['errors'];
            $notice = $result['notice'];
        } elseif ($action === 'update_business' && $canEditBusiness) {
            $businessName = sanitize_input((string) ($_POST['business_name'] ?? ''));
            $website = sanitize_input((string) ($_POST['website'] ?? ''));
            $country = sanitize_input((string) ($_POST['country'] ?? ''));

            if ($businessName === '') {
                $errors[] = 'Business name is required.';
            } else {
                $pdo->prepare('UPDATE partners SET business_name = :name, website = :website, country = :country WHERE id = :id')
                    ->execute(['name' => $businessName, 'website' => $website ?: null, 'country' => $country ?: null, 'id' => $partnerId]);
                log_partner_activity($pdo, $context, 'profile.business_updated', 'partner', $partnerId);
                $notice = 'Business profile updated.';
            }
        }
    }
}

$partnerStmt = $pdo->prepare('SELECT partner_code, business_name, website, country, status, kyc_status FROM partners WHERE id = :id');
$partnerStmt->execute(['id' => $partnerId]);
$partner = $partnerStmt->fetch();
?>
<?php foreach ($errors as $err): ?><div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div><?php endforeach; ?>
<?php if ($notice): ?><div class="badge success" style="margin-bottom:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Your Account</h2></div>
  <div class="ledger">
    <div class="ledger-row"><span class="ledger-tag">Name</span><h3 style="font-size:0.95rem;"><?= e($auth_user['name']) ?></h3><span></span></div>
    <div class="ledger-row"><span class="ledger-tag">Email</span><h3 style="font-size:0.95rem;"><?= e($auth_user['email']) ?></h3><span></span></div>
    <div class="ledger-row"><span class="ledger-tag">Role</span><h3 style="font-size:0.95rem;"><?= e(ucwords(str_replace('_', ' ', $context['role_slug']))) ?></h3><span></span></div>
  </div>
</div>

<?php render_security_panel($pdo, $auth_user); ?>

<div class="panel">
  <div class="panel-head"><h2>Business Profile</h2></div>
  <?php if ($canEditBusiness): ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="form_action" value="update_business">
      <div class="field-grid">
        <div class="field"><label>Business Name</label><input type="text" name="business_name" value="<?= e($partner['business_name']) ?>" required></div>
        <div class="field"><label>Website</label><input type="text" name="website" value="<?= e($partner['website'] ?? '') ?>"></div>
        <div class="field"><label>Country</label><input type="text" name="country" value="<?= e($partner['country'] ?? '') ?>"></div>
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  <?php endif; ?>
  <div class="ledger" style="margin-top:<?= $canEditBusiness ? '20px' : '0' ?>;">
    <div class="ledger-row"><span class="ledger-tag">Partner ID</span><h3 class="mono" style="font-size:0.95rem;"><?= e($partner['partner_code']) ?></h3><span></span></div>
    <div class="ledger-row"><span class="ledger-tag">Account Status</span><h3 style="font-size:0.95rem;"><span class="badge <?= $partner['status'] === 'active' ? 'success' : 'failed' ?>"><?= e(ucfirst($partner['status'])) ?></span></h3><span></span></div>
    <div class="ledger-row"><span class="ledger-tag">KYC Status</span><h3 style="font-size:0.95rem;"><span class="badge <?= $partner['kyc_status'] === 'verified' ? 'success' : 'pending' ?>"><?= e(ucfirst($partner['kyc_status'])) ?></span></h3><span></span></div>
  </div>
</div>
