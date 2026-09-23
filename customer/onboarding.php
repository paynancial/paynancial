<?php
/**
 * Customer Portal — business profile + eKYC onboarding.
 * A customer lands here right after email verification (see
 * pages/signup-verify.php). Until this is submitted and reviewed,
 * their account can sign in but has no activated products.
 */

$pdo = db();
$stmt = $pdo->prepare('SELECT id, customer_code, kyc_status, status FROM customers WHERE user_id = :uid');
$stmt->execute(['uid' => $auth_user['id']]);
$customer = $stmt->fetch();
$customerId = (int) ($customer['id'] ?? 0);

$page_meta = ['title' => 'Business Profile & Verification | Paynancial', 'heading' => 'Business Profile & Verification'];

$profileStmt = $pdo->prepare('SELECT * FROM customer_kyc_profiles WHERE customer_id = :cid');
$profileStmt->execute(['cid' => $customerId]);
$profile = $profileStmt->fetch();

$docLabels = [
    'pan_card'              => 'PAN Card',
    'business_registration' => 'Business Registration / GST Certificate',
    'address_proof'         => 'Business Address Proof',
    'signatory_id'          => 'Authorized Signatory ID Proof',
    'bank_proof'            => 'Cancelled Cheque / Bank Proof',
];

$productsStmt = $pdo->query('SELECT id, slug, name, category, short_description FROM products WHERE is_active = 1 ORDER BY sort_order');
$products = $productsStmt->fetchAll();

// ---------------------------------------------------------------------
// Already submitted: show verification status, not the form again.
// ---------------------------------------------------------------------
if ($profile) {
    $docStmt = $pdo->prepare('SELECT doc_type, status, status_note, uploaded_at FROM customer_kyc_documents WHERE customer_id = :cid');
    $docStmt->execute(['cid' => $customerId]);
    $documents = $docStmt->fetchAll();
    $docByType = [];
    foreach ($documents as $d) { $docByType[$d['doc_type']] = $d; }

    $bankStmt = $pdo->prepare('SELECT bank_name, account_holder, account_number_last4, ifsc, status FROM customer_bank_accounts WHERE customer_id = :cid LIMIT 1');
    $bankStmt->execute(['cid' => $customerId]);
    $bank = $bankStmt->fetch();

    $actStmt = $pdo->prepare('SELECT product_slug, status FROM customer_product_activations WHERE customer_id = :cid');
    $actStmt->execute(['cid' => $customerId]);
    $activations = $actStmt->fetchAll();
    $productBySlug = [];
    foreach ($products as $p) { $productBySlug[$p['slug']] = $p['name']; }

    $statusLabels = ['pending_verification' => 'Verification Pending', 'active' => 'Active', 'suspended' => 'Suspended'];
    $statusBadge = ['pending_verification' => 'pending', 'active' => 'success', 'suspended' => 'failed'];
    $docStatusLabels = ['uploaded' => 'Uploaded', 'under_review' => 'Under Review', 'info_required' => 'Info Required', 'verified' => 'Verified', 'rejected' => 'Rejected'];
    $docStatusBadge = ['uploaded' => 'info', 'under_review' => 'pending', 'info_required' => 'pending', 'verified' => 'success', 'rejected' => 'failed'];
    ?>
    <div class="panel">
      <div class="panel-head">
        <h2><?= e($profile['legal_business_name']) ?></h2>
        <span class="badge <?= e($statusBadge[$customer['status']] ?? 'info') ?>"><?= e($statusLabels[$customer['status']] ?? $customer['status']) ?></span>
      </div>
      <div class="stat-grid" style="margin-bottom:0;">
        <div class="stat-card"><span class="label">Customer ID</span><strong class="value" style="font-size:1.1rem;"><?= e($customer['customer_code']) ?></strong></div>
        <div class="stat-card"><span class="label">Account Status</span><strong class="value" style="font-size:1.1rem;text-transform:capitalize;"><?= e(str_replace('_', ' ', $customer['status'])) ?></strong></div>
        <div class="stat-card"><span class="label">KYC Status</span><strong class="value" style="font-size:1.1rem;text-transform:capitalize;"><?= e(str_replace('_', ' ', $customer['kyc_status'])) ?></strong></div>
        <div class="stat-card"><span class="label">Submitted</span><strong class="value" style="font-size:1.1rem;"><?= e(date('d M Y', strtotime((string) $profile['created_at']))) ?></strong></div>
      </div>
      <?php if ($customer['status'] === 'pending_verification'): ?>
        <p class="text-muted" style="margin-top:16px;">Your business profile and documents are under review. This usually takes 1–2 business days. We'll email you once verification is complete — no action is needed right now.</p>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Documents</h2></div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead><tr><th>Document</th><th>Status</th><th>Note</th></tr></thead>
          <tbody>
            <?php foreach ($docLabels as $type => $label): $doc = $docByType[$type] ?? null; ?>
              <tr>
                <td><?= e($label) ?></td>
                <td><?php if ($doc): ?><span class="badge <?= e($docStatusBadge[$doc['status']] ?? 'info') ?>"><?= e($docStatusLabels[$doc['status']] ?? $doc['status']) ?></span><?php else: ?><span class="badge failed">Not Uploaded</span><?php endif; ?></td>
                <td class="text-muted" style="font-size:0.82rem;"><?= e($doc['status_note'] ?? '—') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($bank): ?>
    <div class="panel">
      <div class="panel-head"><h2>Bank Account</h2></div>
      <p class="text-muted"><?= e($bank['bank_name']) ?> &middot; <?= e($bank['account_holder']) ?> &middot; Account ending <?= e($bank['account_number_last4']) ?><?= $bank['ifsc'] ? ' · ' . e($bank['ifsc']) : '' ?> &middot; <span class="badge <?= $bank['status'] === 'verified' ? 'success' : ($bank['status'] === 'rejected' ? 'failed' : 'pending') ?>"><?= e(ucfirst(str_replace('_', ' ', $bank['status']))) ?></span></p>
    </div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel-head"><h2>Products</h2></div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead><tr><th>Product</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($activations)): ?>
              <tr><td colspan="2"><div class="empty-state">No products requested.</div></td></tr>
            <?php else: foreach ($activations as $a): ?>
              <tr>
                <td><?= e($productBySlug[$a['product_slug']] ?? $a['product_slug']) ?></td>
                <td><span class="badge <?= $a['status'] === 'active' ? 'success' : 'pending' ?>"><?= e(ucfirst(str_replace('_', ' ', $a['status']))) ?></span></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
    return;
}

// ---------------------------------------------------------------------
// Not yet submitted: show the wizard.
// ---------------------------------------------------------------------
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $legalName = sanitize_input((string) ($_POST['legal_business_name'] ?? ''));
        $displayName = sanitize_input((string) ($_POST['display_name'] ?? ''));
        $businessType = sanitize_input((string) ($_POST['business_type'] ?? ''));
        $industry = sanitize_input((string) ($_POST['industry'] ?? ''));
        $registeredAddress = sanitize_input((string) ($_POST['registered_address'] ?? ''));
        $websiteUrl = sanitize_input((string) ($_POST['website_url'] ?? ''));
        $volumeBand = sanitize_input((string) ($_POST['monthly_volume_band'] ?? ''));
        $signatoryName = sanitize_input((string) ($_POST['signatory_name'] ?? ''));
        $signatoryDesignation = sanitize_input((string) ($_POST['signatory_designation'] ?? ''));
        $pan = sanitize_input((string) ($_POST['pan'] ?? ''));
        $gstin = sanitize_input((string) ($_POST['gstin'] ?? ''));

        $bankName = sanitize_input((string) ($_POST['bank_name'] ?? ''));
        $accountHolder = sanitize_input((string) ($_POST['account_holder'] ?? ''));
        $accountNumber = sanitize_input((string) ($_POST['account_number'] ?? ''));
        $ifsc = sanitize_input((string) ($_POST['ifsc'] ?? ''));

        $selectedProducts = array_map('sanitize_input', (array) ($_POST['products'] ?? []));

        if ($legalName === '') { $errors[] = 'Legal business name is required.'; }
        if ($registeredAddress === '') { $errors[] = 'Registered business address is required.'; }
        if ($signatoryName === '') { $errors[] = 'Authorized signatory name is required.'; }
        if ($bankName === '' || $accountHolder === '' || $accountNumber === '') { $errors[] = 'Bank details are required to activate settlements.'; }
        if (empty($selectedProducts)) { $errors[] = 'Please select at least one product.'; }

        if (empty($errors)) {
            $pdo->beginTransaction();
            try {
                $pdo->prepare(
                    'INSERT INTO customer_kyc_profiles
                       (customer_id, legal_business_name, display_name, business_type, industry, registered_address, website_url,
                        monthly_volume_band, requested_products_json, signatory_name, signatory_designation, pan, gstin, onboarding_step)
                     VALUES (:cid, :legal, :display, :btype, :industry, :addr, :website, :volume, :products, :sig, :sigdesg, :pan, :gstin, 5)'
                )->execute([
                    'cid' => $customerId, 'legal' => $legalName, 'display' => $displayName ?: null, 'btype' => $businessType ?: null,
                    'industry' => $industry ?: null, 'addr' => $registeredAddress, 'website' => $websiteUrl ?: null,
                    'volume' => $volumeBand ?: null, 'products' => json_encode(array_values($selectedProducts)),
                    'sig' => $signatoryName, 'sigdesg' => $signatoryDesignation ?: null, 'pan' => $pan ?: null, 'gstin' => $gstin ?: null,
                ]);

                foreach (array_keys($docLabels) as $docType) {
                    $fieldName = 'doc_' . $docType;
                    if (!empty($_FILES[$fieldName]['name'])) {
                        $docError = validate_document_upload($_FILES[$fieldName]);
                        if ($docError !== null) {
                            throw new RuntimeException($docLabels[$docType] . ': ' . $docError);
                        }
                        $path = store_upload($_FILES[$fieldName], 'customer-kyc/' . $customer['customer_code']);
                        $pdo->prepare('INSERT INTO customer_kyc_documents (customer_id, doc_type, file_path) VALUES (:cid, :type, :path)')
                            ->execute(['cid' => $customerId, 'type' => $docType, 'path' => $path]);
                    }
                }

                $encrypted = encrypt_sensitive($accountNumber);
                $last4 = substr(preg_replace('/\D/', '', $accountNumber) ?? '', -4);
                $pdo->prepare(
                    'INSERT INTO customer_bank_accounts (customer_id, bank_name, account_holder, account_number_enc, account_number_last4, ifsc)
                     VALUES (:cid, :bank, :holder, :enc, :last4, :ifsc)'
                )->execute([
                    'cid' => $customerId, 'bank' => $bankName, 'holder' => $accountHolder, 'enc' => $encrypted,
                    'last4' => $last4 ?: '0000', 'ifsc' => $ifsc ?: null,
                ]);

                $validSlugs = array_column($products, 'slug');
                foreach ($selectedProducts as $slug) {
                    if (!in_array($slug, $validSlugs, true)) { continue; }
                    $pdo->prepare('INSERT INTO customer_product_activations (customer_id, product_slug, status) VALUES (:cid, :slug, "pending_kyc")')
                        ->execute(['cid' => $customerId, 'slug' => $slug]);
                }

                $pdo->prepare("UPDATE customers SET kyc_status = 'pending' WHERE id = :id")->execute(['id' => $customerId]);

                $pdo->commit();
                header('Location: /customer/onboarding');
                exit;
            } catch (Throwable $e) {
                $pdo->rollBack();
                error_log('[Paynancial] Customer onboarding submit failed: ' . $e->getMessage());
                $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'Something went wrong while submitting your profile. Please try again.';
            }
        }
    }
}
?>
<?php foreach ($errors as $err): ?>
  <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="panel-wizard">
  <ol class="wizard-steps" id="wizard-steps">
    <?php foreach (['Business Profile', 'Documents', 'Bank Details', 'Products', 'Review'] as $i => $label): ?>
      <li data-step-indicator="<?= $i + 1 ?>" class="<?= $i === 0 ? 'is-active' : '' ?>"><span><?= $i + 1 ?></span><?= e($label) ?></li>
    <?php endforeach; ?>
  </ol>

  <form method="post" enctype="multipart/form-data" id="onboarding-form" novalidate>
    <?= csrf_field() ?>

    <fieldset class="wizard-step is-active" data-step="1">
      <h3>Business Profile</h3>
      <div class="field-grid">
        <div class="field"><label>Legal Business Name</label><input type="text" name="legal_business_name" required></div>
        <div class="field"><label>Display Name</label><input type="text" name="display_name"></div>
        <div class="field"><label>Business Type</label><input type="text" name="business_type" placeholder="e.g. Private Limited, Proprietorship"></div>
        <div class="field"><label>Industry</label><input type="text" name="industry"></div>
        <div class="field"><label>Website</label><input type="text" name="website_url" placeholder="https://"></div>
        <div class="field"><label>Expected Monthly Volume</label>
          <select name="monthly_volume_band"><option value="">Select</option><option>Under ₹5L</option><option>₹5L–₹25L</option><option>₹25L–₹1Cr</option><option>₹1Cr–₹5Cr</option><option>₹5Cr+</option></select>
        </div>
        <div class="field"><label>PAN</label><input type="text" name="pan"></div>
        <div class="field"><label>GSTIN</label><input type="text" name="gstin"></div>
      </div>
      <div class="field"><label>Registered Business Address</label><textarea name="registered_address" rows="3" required></textarea></div>
      <div class="field-grid">
        <div class="field"><label>Authorized Signatory Name</label><input type="text" name="signatory_name" required></div>
        <div class="field"><label>Signatory Designation</label><input type="text" name="signatory_designation"></div>
      </div>
    </fieldset>

    <fieldset class="wizard-step" data-step="2">
      <h3>eKYC Documents</h3>
      <p class="text-muted" style="font-size:0.85rem;">Accepted formats: PDF, JPG, PNG. Max 5MB each. Your documents are stored securely and reviewed by our compliance team.</p>
      <div class="field-grid">
        <?php foreach ($docLabels as $docType => $label): ?>
          <div class="field"><label><?= e($label) ?></label><input type="file" name="doc_<?= e($docType) ?>" accept=".pdf,.jpg,.jpeg,.png"></div>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <fieldset class="wizard-step" data-step="3">
      <h3>Bank / Settlement Details</h3>
      <div class="field-grid">
        <div class="field"><label>Bank Name</label><input type="text" name="bank_name" required></div>
        <div class="field"><label>Account Holder</label><input type="text" name="account_holder" required></div>
        <div class="field"><label>Account Number</label><input type="text" name="account_number" required autocomplete="off"></div>
        <div class="field"><label>IFSC</label><input type="text" name="ifsc"></div>
      </div>
      <p class="text-muted" style="font-size:0.8rem;">Your account number is encrypted at rest and only ever shown masked.</p>
    </fieldset>

    <fieldset class="wizard-step" data-step="4">
      <h3>Select Products</h3>
      <p class="text-muted">Choose the Paynancial products you'd like to activate. Each becomes available once verification is complete.</p>
      <div class="option-grid">
        <?php foreach ($products as $product): ?>
          <label class="option-card">
            <input type="checkbox" name="products[]" value="<?= e($product['slug']) ?>">
            <span><?= e($product['name']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <fieldset class="wizard-step" data-step="5">
      <h3>Review &amp; Submit</h3>
      <div id="review-summary" class="ledger"></div>
      <p class="text-muted" style="margin-top:16px;">Submitting sends your business profile and documents for verification. Your account stays in "Verification Pending" until our compliance team confirms your details — this usually takes 1–2 business days.</p>
    </fieldset>

    <div class="wizard-nav">
      <button type="button" class="btn btn-outline" id="wizard-back" style="visibility:hidden;">Back</button>
      <button type="button" class="btn btn-primary" id="wizard-next">Next</button>
      <button type="submit" class="btn btn-primary" id="wizard-submit" style="display:none;">Submit for Verification</button>
    </div>
  </form>
</div>

<script nonce="<?= csp_nonce() ?>">
(function () {
  var form = document.getElementById('onboarding-form');
  var steps = Array.prototype.slice.call(form.querySelectorAll('.wizard-step'));
  var indicators = Array.prototype.slice.call(document.querySelectorAll('[data-step-indicator]'));
  var current = 1;

  function showStep(n) {
    steps.forEach(function (s) { s.classList.toggle('is-active', parseInt(s.getAttribute('data-step'), 10) === n); });
    indicators.forEach(function (li) { li.classList.toggle('is-active', parseInt(li.getAttribute('data-step-indicator'), 10) <= n); });
    document.getElementById('wizard-back').style.visibility = n === 1 ? 'hidden' : 'visible';
    document.getElementById('wizard-next').style.display = n === steps.length ? 'none' : 'inline-flex';
    document.getElementById('wizard-submit').style.display = n === steps.length ? 'inline-flex' : 'none';
    if (n === steps.length) buildReviewSummary();
    current = n;
    window.scrollTo({ top: form.offsetTop - 120, behavior: 'smooth' });
  }

  function validateStep(n) {
    var step = steps[n - 1];
    var inputs = step.querySelectorAll('input, select, textarea');
    for (var i = 0; i < inputs.length; i++) {
      if (!inputs[i].checkValidity()) { inputs[i].reportValidity(); return false; }
    }
    return true;
  }

  document.getElementById('wizard-next').addEventListener('click', function () {
    if (!validateStep(current)) return;
    if (current < steps.length) showStep(current + 1);
  });
  document.getElementById('wizard-back').addEventListener('click', function () {
    if (current > 1) showStep(current - 1);
  });

  function buildReviewSummary() {
    var fields = [
      ['Legal Business Name', 'legal_business_name'], ['Business Type', 'business_type'], ['Industry', 'industry'],
      ['Bank Name', 'bank_name'], ['Account Holder', 'account_holder'],
    ];
    var html = '';
    fields.forEach(function (f) {
      var el = form.elements[f[1]];
      var value = el ? (el.tagName === 'SELECT' ? (el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : '') : el.value) : '';
      html += '<div class="ledger-row"><span class="ledger-tag">' + f[0] + '</span><h3 style="font-size:0.95rem;">' + (value || '—') + '</h3><span></span></div>';
    });
    var selectedProducts = Array.prototype.slice.call(form.querySelectorAll('input[name="products[]"]:checked')).map(function (cb) {
      return cb.parentElement.textContent.trim();
    });
    html += '<div class="ledger-row"><span class="ledger-tag">Products</span><h3 style="font-size:0.95rem;">' + (selectedProducts.join(', ') || '—') + '</h3><span></span></div>';
    document.getElementById('review-summary').innerHTML = html;
  }

  showStep(1);
})();
</script>
