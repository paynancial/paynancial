<?php
/** Partner Hub — Enroll Customer wizard (the hero flow). */
$context = require_partner_context();
$partnerId = $context['partner_id'];
$page_meta = ['title' => 'Enroll Customer | Paynancial Partner Hub', 'heading' => 'Enroll Customer'];

$customerTypes = [
    'individual' => 'Individual', 'small_business' => 'Small Business', 'sme' => 'SME', 'enterprise' => 'Enterprise',
    'ecommerce' => 'E-Commerce', 'travel' => 'Travel', 'education' => 'Education', 'healthcare' => 'Healthcare',
    'retail' => 'Retail', 'hospitality' => 'Hospitality', 'professional_services' => 'Professional Services', 'other' => 'Other',
];

$requirementLabels = [
    'online_gateway' => 'Online Payment Gateway', 'payment_links' => 'Payment Links', 'payment_pages' => 'Payment Pages',
    'website_payments' => 'Website Payments', 'ecommerce_payments' => 'E-Commerce Payments', 'payouts' => 'Payouts',
    'recurring_payments' => 'Recurring Payments', 'payment_collection' => 'Payment Collection', 'payment_analytics' => 'Payment Analytics',
    'api_integration' => 'API Integration', 'custom_integration' => 'Custom Integration', 'multi_platform' => 'Multi-platform Payments',
    'business_dashboard' => 'Business Dashboard', 'other' => 'Other',
];

$docLabels = [
    'id_proof' => 'Passport / ID', 'business_registration' => 'Business Registration', 'tax_documents' => 'Tax Documents',
    'bank_documents' => 'Bank Documents', 'address_proof' => 'Address Proof', 'signatory_id' => 'Authorized Signatory', 'other' => 'Other Document',
];

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $customerType = sanitize_input((string) ($_POST['customer_type'] ?? ''));
        $businessName = sanitize_input((string) ($_POST['business_name'] ?? ''));
        $contactPerson = sanitize_input((string) ($_POST['contact_person'] ?? ''));
        $email = sanitize_input((string) ($_POST['email'] ?? ''));
        $mobile = sanitize_input((string) ($_POST['mobile'] ?? ''));
        $website = sanitize_input((string) ($_POST['website'] ?? ''));
        $country = sanitize_input((string) ($_POST['country'] ?? ''));
        $address = sanitize_input((string) ($_POST['address'] ?? ''));
        $industry = sanitize_input((string) ($_POST['industry'] ?? ''));

        $requirements = array_values(array_intersect((array) ($_POST['requirements'] ?? []), array_keys($requirementLabels)));

        $monthlyGmv = sanitize_input((string) ($_POST['monthly_gmv'] ?? ''));
        $avgTxnValue = sanitize_input((string) ($_POST['avg_transaction_value'] ?? ''));
        $expectedTxnCount = sanitize_input((string) ($_POST['expected_txn_count'] ?? ''));
        $isInternational = isset($_POST['is_international']);
        $preferredCurrencies = sanitize_input((string) ($_POST['preferred_currencies'] ?? ''));
        $settlementFrequency = sanitize_input((string) ($_POST['settlement_frequency'] ?? ''));

        $selectedProductIds = array_map('intval', (array) ($_POST['selected_products'] ?? []));

        if (!array_key_exists($customerType, $customerTypes)) { $errors[] = 'Please select a customer type.'; }
        if ($businessName === '') { $errors[] = 'Business name is required.'; }
        if ($contactPerson === '') { $errors[] = 'Contact person is required.'; }
        if (!is_valid_email($email)) { $errors[] = 'Please enter a valid email address.'; }
        if (!is_valid_mobile($mobile)) { $errors[] = 'Please enter a valid mobile number.'; }

        if (empty($errors)) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $applicationCode = generate_customer_application_code($pdo);
                $stmt = $pdo->prepare(
                    'INSERT INTO customer_applications
                       (application_code, partner_id, customer_type, business_name, contact_person, email, mobile, website, country, address, industry,
                        requirements_json, monthly_gmv, avg_transaction_value, expected_txn_count, is_international, preferred_currencies, settlement_frequency, pipeline_stage)
                     VALUES
                       (:code, :pid, :ctype, :bname, :cperson, :email, :mobile, :website, :country, :address, :industry,
                        :reqs, :gmv, :avgtxn, :txncount, :intl, :currencies, :freq, "new_lead")'
                );
                $stmt->execute([
                    'code' => $applicationCode, 'pid' => $partnerId, 'ctype' => $customerType, 'bname' => $businessName, 'cperson' => $contactPerson,
                    'email' => $email, 'mobile' => $mobile, 'website' => $website ?: null, 'country' => $country ?: null, 'address' => $address ?: null,
                    'industry' => $industry ?: null, 'reqs' => json_encode($requirements), 'gmv' => $monthlyGmv ?: null, 'avgtxn' => $avgTxnValue ?: null,
                    'txncount' => $expectedTxnCount ?: null, 'intl' => $isInternational ? 1 : 0, 'currencies' => $preferredCurrencies ?: null,
                    'freq' => $settlementFrequency ?: null,
                ]);
                $applicationId = (int) $pdo->lastInsertId();

                foreach (array_keys($docLabels) as $docType) {
                    $fieldName = 'doc_' . $docType;
                    if (!empty($_FILES[$fieldName]['name'])) {
                        $docError = validate_document_upload($_FILES[$fieldName]);
                        if ($docError !== null) {
                            throw new RuntimeException($docLabels[$docType] . ': ' . $docError);
                        }
                        $path = store_upload($_FILES[$fieldName], 'customer-applications/' . $applicationCode);
                        $docStmt = $pdo->prepare(
                            'INSERT INTO customer_application_documents (customer_application_id, doc_type, file_path) VALUES (:aid, :type, :path)'
                        );
                        $docStmt->execute(['aid' => $applicationId, 'type' => $docType, 'path' => $path]);
                    }
                }

                if (!empty($selectedProductIds)) {
                    $prodStmt = $pdo->prepare(
                        'INSERT INTO customer_application_products (customer_application_id, product_id, status) VALUES (:aid, :pid, "selected")'
                    );
                    foreach ($selectedProductIds as $productId) {
                        $prodStmt->execute(['aid' => $applicationId, 'pid' => $productId]);
                    }
                }

                log_partner_activity($pdo, $context, 'customer.enrolled', 'customer_application', $applicationId);

                $pdo->commit();
                $success = ['code' => $applicationCode, 'id' => $applicationId];
            } catch (Throwable $e) {
                $pdo->rollBack();
                error_log('[Paynancial] Customer enrollment failed: ' . $e->getMessage());
                $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'Something went wrong while enrolling this customer. Please try again.';
            }
        }
    }
}
?>
<?php if ($success): ?>
  <div class="panel">
    <h2>Customer Enrolled</h2>
    <p class="text-muted" style="margin-top:8px;">Application <strong class="mono"><?= e($success['code']) ?></strong> has been created and is now a New Lead in your pipeline.</p>
    <div class="hero-actions" style="margin-top:20px;">
      <a href="/partner/customers/<?= (int) $success['id'] ?>" class="btn btn-primary">View Customer Profile</a>
      <a href="/partner/enroll-customer" class="btn btn-outline">Enroll Another Customer</a>
    </div>
  </div>
<?php else: ?>
  <?php foreach ($errors as $err): ?>
    <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
  <?php endforeach; ?>

  <div class="panel-wizard">
    <ol class="wizard-steps" id="wizard-steps">
      <?php foreach (['Customer Type', 'Basic Details', 'Requirements', 'Volume', 'Documents', 'Recommendation', 'Review'] as $i => $label): ?>
        <li data-step-indicator="<?= $i + 1 ?>" class="<?= $i === 0 ? 'is-active' : '' ?>"><span><?= $i + 1 ?></span><?= e($label) ?></li>
      <?php endforeach; ?>
    </ol>

    <form method="post" enctype="multipart/form-data" id="enroll-form" novalidate>
      <?= csrf_field() ?>

      <fieldset class="wizard-step is-active" data-step="1">
        <h3>Customer Type</h3>
        <div class="option-grid">
          <?php foreach ($customerTypes as $slug => $label): ?>
            <label class="option-card">
              <input type="radio" name="customer_type" id="ctype-<?= e($slug) ?>" value="<?= e($slug) ?>" required>
              <span><?= e($label) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <fieldset class="wizard-step" data-step="2">
        <h3>Customer Basic Details</h3>
        <div class="field-grid">
          <div class="field"><label>Business Name</label><input type="text" name="business_name" required></div>
          <div class="field"><label>Contact Person</label><input type="text" name="contact_person" required></div>
          <div class="field"><label>Email</label><input type="email" name="email" required></div>
          <div class="field"><label>Mobile</label><input type="text" name="mobile" required></div>
          <div class="field"><label>Website</label><input type="text" name="website" placeholder="https://"></div>
          <div class="field"><label>Country</label><input type="text" name="country"></div>
          <div class="field"><label>Industry</label><input type="text" name="industry"></div>
        </div>
        <div class="field"><label>Address</label><textarea name="address" rows="2"></textarea></div>
      </fieldset>

      <fieldset class="wizard-step" data-step="3">
        <h3>Business Requirements</h3>
        <p class="text-muted">What does this customer need?</p>
        <div class="option-grid">
          <?php foreach ($requirementLabels as $slug => $label): ?>
            <label class="option-card">
              <input type="checkbox" name="requirements[]" value="<?= e($slug) ?>" class="requirement-checkbox">
              <span><?= e($label) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <fieldset class="wizard-step" data-step="4">
        <h3>Expected Business Volume</h3>
        <div class="field-grid">
          <div class="field"><label>Monthly GMV</label>
            <select name="monthly_gmv"><option value="">Select</option><option>Under 5L</option><option>5L-25L</option><option>25L-1Cr</option><option>1Cr-5Cr</option><option>5Cr+</option></select>
          </div>
          <div class="field"><label>Average Transaction Value</label><input type="text" name="avg_transaction_value"></div>
          <div class="field"><label>Expected Number of Transactions</label><input type="text" name="expected_txn_count"></div>
          <div class="field"><label>Preferred Currencies</label><input type="text" name="preferred_currencies" placeholder="INR, USD"></div>
          <div class="field"><label>Settlement Frequency</label>
            <select name="settlement_frequency"><option value="">Select</option><option value="standard">Standard</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option></select>
          </div>
          <div class="field"><label class="field-row" style="margin-top:30px;"><input type="checkbox" name="is_international" id="is_international"> Domestic &amp; International customers</label></div>
        </div>
      </fieldset>

      <fieldset class="wizard-step" data-step="5">
        <h3>Customer Documents</h3>
        <p class="text-muted" style="font-size:0.85rem;">Optional at this stage — documents can also be requested after the customer is enrolled.</p>
        <div class="field-grid">
          <?php foreach ($docLabels as $docType => $label): ?>
            <div class="field"><label><?= e($label) ?></label><input type="file" name="doc_<?= e($docType) ?>" accept=".pdf,.jpg,.jpeg,.png"></div>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <fieldset class="wizard-step" data-step="6">
        <h3>Recommended Paynancial Solutions</h3>
        <p class="text-muted">Based on this customer's profile and requirements.</p>
        <div id="recommendation-results" class="grid grid-2" style="margin-top:16px;"></div>
        <p class="empty-state" id="recommendation-empty" style="display:none;">No specific recommendations matched — you can still select solutions manually from the Solution Catalog after enrollment.</p>
      </fieldset>

      <fieldset class="wizard-step" data-step="7">
        <h3>Review &amp; Submit</h3>
        <div id="enroll-review-summary" class="ledger"></div>
        <p class="text-muted" style="margin-top:16px;">Submitting creates the customer application as a New Lead in your CRM pipeline.</p>
      </fieldset>

      <div class="wizard-nav">
        <button type="button" class="btn btn-outline" id="wizard-back" style="visibility:hidden;">Back</button>
        <button type="button" class="btn btn-primary" id="wizard-next">Next</button>
        <button type="submit" class="btn btn-primary" id="wizard-submit" style="display:none;">Enroll Customer</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<script nonce="<?= csp_nonce() ?>">
(function () {
  var form = document.getElementById('enroll-form');
  if (!form) return;
  var steps = Array.prototype.slice.call(form.querySelectorAll('.wizard-step'));
  var indicators = Array.prototype.slice.call(document.querySelectorAll('[data-step-indicator]'));
  var current = 1;
  var requirementLabels = <?= json_encode($requirementLabels, JSON_UNESCAPED_SLASHES) ?>;

  function showStep(n) {
    steps.forEach(function (s) { s.classList.toggle('is-active', parseInt(s.getAttribute('data-step'), 10) === n); });
    indicators.forEach(function (li) { li.classList.toggle('is-active', parseInt(li.getAttribute('data-step-indicator'), 10) <= n); });
    document.getElementById('wizard-back').style.visibility = n === 1 ? 'hidden' : 'visible';
    document.getElementById('wizard-next').style.display = n === steps.length ? 'none' : 'inline-flex';
    document.getElementById('wizard-submit').style.display = n === steps.length ? 'inline-flex' : 'none';
    if (n === 6) loadRecommendations();
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

  function loadRecommendations() {
    var customerType = (form.querySelector('input[name="customer_type"]:checked') || {}).value || '';
    var requirements = Array.prototype.slice.call(form.querySelectorAll('.requirement-checkbox:checked')).map(function (el) { return el.value; });
    var isInternational = form.querySelector('#is_international').checked;
    var resultsEl = document.getElementById('recommendation-results');
    var emptyEl = document.getElementById('recommendation-empty');
    resultsEl.innerHTML = '<p class="text-muted">Loading recommendations…</p>';

    fetch('/api/partner/recommend', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
      body: JSON.stringify({
        csrf_token: form.querySelector('[name="csrf_token"]').value,
        customer_type: customerType, requirements: requirements, is_international: isInternational
      })
    }).then(function (r) { return r.json(); }).then(function (data) {
      resultsEl.innerHTML = '';
      if (!data.ok || !data.recommendations.length) {
        emptyEl.style.display = 'block';
        return;
      }
      emptyEl.style.display = 'none';
      data.recommendations.forEach(function (rec) {
        var card = document.createElement('label');
        card.className = 'card';
        card.style.cursor = 'pointer';
        card.innerHTML =
          '<div class="flex" style="justify-content:space-between;align-items:flex-start;gap:10px;">' +
          '<h3 style="font-size:1rem;">' + rec.name + '</h3>' +
          '<input type="checkbox" name="selected_products[]" value="' + rec.id + '" checked>' +
          '</div>' +
          '<p style="margin-top:6px;">' + (rec.short_description || '') + '</p>' +
          '<p class="text-muted" style="font-size:0.78rem;margin-top:10px;"><strong>Why:</strong> ' + rec.reasons.join(' ') + '</p>' +
          '<div class="pill-list" style="margin-top:10px;">' +
          '<span class="pill">Complexity: ' + rec.complexity + '</span>' +
          (rec.commission_eligible ? '<span class="pill">Commission eligible</span>' : '') +
          '</div>';
        resultsEl.appendChild(card);
      });
    }).catch(function () {
      resultsEl.innerHTML = '<p class="form-error is-visible">Could not load recommendations. You can still continue and select solutions later.</p>';
    });
  }

  function buildReviewSummary() {
    var customerType = (form.querySelector('input[name="customer_type"]:checked') || {}).value || '—';
    var requirements = Array.prototype.slice.call(form.querySelectorAll('.requirement-checkbox:checked')).map(function (el) { return requirementLabels[el.value] || el.value; });
    var rows = [
      ['Customer Type', customerType],
      ['Business Name', form.elements['business_name'].value || '—'],
      ['Contact Person', form.elements['contact_person'].value || '—'],
      ['Email', form.elements['email'].value || '—'],
      ['Requirements', requirements.join(', ') || 'None selected'],
      ['Selected Solutions', document.querySelectorAll('#recommendation-results input:checked').length + ' solution(s)'],
    ];
    document.getElementById('enroll-review-summary').innerHTML = rows.map(function (r) {
      return '<div class="ledger-row"><span class="ledger-tag">' + r[0] + '</span><h3 style="font-size:0.95rem;">' + r[1] + '</h3><span></span></div>';
    }).join('');
  }

  showStep(1);
})();
</script>
