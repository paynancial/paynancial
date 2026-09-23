<?php
/**
 * Public Partner Hub registration wizard: /partner/register
 * A single self-submitting form; JS handles step navigation/validation,
 * PHP re-validates everything server-side on final POST (never trust
 * client-side checks alone for a financial platform).
 */

$page_meta = [
    'title' => 'Become a Paynancial Partner | Partner Hub',
    'description' => 'Join the Paynancial Partner Network — enroll customers, recommend Paynancial solutions and earn commission.',
];

$partnerTypes = [
    'individual'        => 'Individual',
    'company'            => 'Company',
    'agency'             => 'Agency',
    'technology_partner' => 'Technology Partner',
    'reseller'           => 'Reseller',
    'consultant'         => 'Consultant',
    'distributor'        => 'Distributor',
    'enterprise_partner' => 'Enterprise Partner',
    'other'              => 'Other',
];

$engagementModels = [
    'referral'               => 'Customer Referral',
    'enrollment'              => 'Customer Enrollment',
    'reseller'                => 'Payment Solution Reseller',
    'technology_integration'  => 'Technology Integration',
    'api_partner'              => 'API Partner',
    'enterprise'               => 'Enterprise Partnership',
    'strategic'                => 'Strategic Partnership',
];

$docLabels = [
    'company_registration' => 'Company Registration',
    'tax_registration'      => 'Tax Registration',
    'business_license'      => 'Business License',
    'gst_vat'                => 'GST / VAT Registration',
    'signatory_id'           => 'Authorized Signatory ID',
    'address_proof'          => 'Address Proof',
    'bank_details'           => 'Bank Details Proof',
    'other'                  => 'Other Document',
];

$docRequirements = [];
try {
    $stmt = db()->query('SELECT partner_type, doc_type, label, is_required FROM partner_document_requirements ORDER BY partner_type, sort_order');
    foreach ($stmt->fetchAll() as $row) {
        $docRequirements[$row['partner_type']][] = $row;
    }
} catch (Throwable $e) {
    $docRequirements = [];
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (!rate_limit('partner_register_' . client_ip(), 5, 3600)) {
        $errors[] = 'Too many applications submitted from this connection. Please try again later or contact support.';
    } else {
        $partnerType = sanitize_input((string) ($_POST['partner_type'] ?? ''));
        $businessName = sanitize_input((string) ($_POST['business_name'] ?? ''));
        $contactPerson = sanitize_input((string) ($_POST['contact_person'] ?? ''));
        $email = sanitize_input((string) ($_POST['email'] ?? ''));
        $mobile = sanitize_input((string) ($_POST['mobile'] ?? ''));
        $country = sanitize_input((string) ($_POST['country'] ?? ''));
        $state = sanitize_input((string) ($_POST['state'] ?? ''));
        $city = sanitize_input((string) ($_POST['city'] ?? ''));
        $website = sanitize_input((string) ($_POST['website'] ?? ''));
        $businessAddress = sanitize_input((string) ($_POST['business_address'] ?? ''));

        $businessType = sanitize_input((string) ($_POST['business_type'] ?? ''));
        $industry = sanitize_input((string) ($_POST['industry'] ?? ''));
        $yearsInBusiness = sanitize_input((string) ($_POST['years_in_business'] ?? ''));
        $employeeCount = sanitize_input((string) ($_POST['employee_count'] ?? ''));
        $existingCustomerBase = sanitize_input((string) ($_POST['existing_customer_base'] ?? ''));
        $expectedVolume = sanitize_input((string) ($_POST['expected_monthly_volume'] ?? ''));
        $currentProvider = sanitize_input((string) ($_POST['current_payment_provider'] ?? ''));
        $primaryMarkets = sanitize_input((string) ($_POST['primary_markets'] ?? ''));
        $countriesServed = sanitize_input((string) ($_POST['countries_served'] ?? ''));

        $engagementModel = sanitize_input((string) ($_POST['engagement_model'] ?? ''));

        $bankName = sanitize_input((string) ($_POST['bank_name'] ?? ''));
        $accountHolder = sanitize_input((string) ($_POST['account_holder'] ?? ''));
        $accountNumber = sanitize_input((string) ($_POST['account_number'] ?? ''));
        $routingCode = sanitize_input((string) ($_POST['routing_code'] ?? ''));
        $currency = sanitize_input((string) ($_POST['currency'] ?? 'INR'));
        $settlementPreference = sanitize_input((string) ($_POST['settlement_preference'] ?? 'standard'));

        $agreementsAccepted = isset($_POST['agreements_accepted']);
        $accuracyConfirmed = isset($_POST['accuracy_confirmed']);

        if (!array_key_exists($partnerType, $partnerTypes)) { $errors[] = 'Please select a valid partner type.'; }
        if ($businessName === '') { $errors[] = 'Business / partner name is required.'; }
        if ($contactPerson === '') { $errors[] = 'Contact person is required.'; }
        if (!is_valid_email($email)) { $errors[] = 'Please enter a valid email address.'; }
        if (!is_valid_mobile($mobile)) { $errors[] = 'Please enter a valid mobile number.'; }
        if (!array_key_exists($engagementModel, $engagementModels)) { $errors[] = 'Please select how you plan to work with Paynancial.'; }
        if ($bankName === '' || $accountHolder === '' || $accountNumber === '') { $errors[] = 'Bank/settlement details are required.'; }
        if (!$agreementsAccepted || !$accuracyConfirmed) { $errors[] = 'Please accept the partner agreements and confirm the information is accurate.'; }

        if (empty($errors)) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $applicationCode = generate_partner_application_code($pdo);

                $stmt = $pdo->prepare(
                    'INSERT INTO partner_applications
                       (application_code, partner_type, business_name, contact_person, email, mobile, country, state, city, website, business_address,
                        business_type, industry, years_in_business, employee_count, existing_customer_base, expected_monthly_volume, current_payment_provider,
                        primary_markets, countries_served, engagement_model, agreements_accepted, agreements_accepted_at, agreements_ip, status)
                     VALUES
                       (:code, :ptype, :bname, :cperson, :email, :mobile, :country, :state, :city, :website, :baddr,
                        :btype, :industry, :years, :emp, :cbase, :vol, :provider,
                        :markets, :countries, :engagement, 1, NOW(), :ip, "submitted")'
                );
                $stmt->execute([
                    'code' => $applicationCode, 'ptype' => $partnerType, 'bname' => $businessName, 'cperson' => $contactPerson,
                    'email' => $email, 'mobile' => $mobile, 'country' => $country ?: null, 'state' => $state ?: null, 'city' => $city ?: null,
                    'website' => $website ?: null, 'baddr' => $businessAddress ?: null,
                    'btype' => $businessType ?: null, 'industry' => $industry ?: null, 'years' => $yearsInBusiness ?: null,
                    'emp' => $employeeCount ?: null, 'cbase' => $existingCustomerBase ?: null, 'vol' => $expectedVolume ?: null,
                    'provider' => $currentProvider ?: null, 'markets' => $primaryMarkets ?: null, 'countries' => $countriesServed ?: null,
                    'engagement' => $engagementModel, 'ip' => client_ip(),
                ]);
                $applicationId = (int) $pdo->lastInsertId();

                // Documents — one optional file input per known doc type; only
                // those relevant to the chosen partner type are shown client-side,
                // but we accept whatever was actually submitted.
                foreach (array_keys($docLabels) as $docType) {
                    $fieldName = 'doc_' . $docType;
                    if (!empty($_FILES[$fieldName]['name'])) {
                        $docError = validate_document_upload($_FILES[$fieldName]);
                        if ($docError !== null) {
                            throw new RuntimeException($docLabels[$docType] . ': ' . $docError);
                        }
                        $path = store_upload($_FILES[$fieldName], 'partner-applications/' . $applicationCode);
                        $docStmt = $pdo->prepare(
                            'INSERT INTO partner_application_documents (application_id, doc_type, file_path) VALUES (:aid, :type, :path)'
                        );
                        $docStmt->execute(['aid' => $applicationId, 'type' => $docType, 'path' => $path]);
                    }
                }

                // Bank details — encrypted at rest, only last 4 digits kept in the clear.
                $encrypted = encrypt_sensitive($accountNumber);
                $last4 = substr(preg_replace('/\D/', '', $accountNumber), -4);
                $bankStmt = $pdo->prepare(
                    'INSERT INTO partner_bank_accounts (application_id, bank_name, account_holder, account_number_enc, account_number_last4, routing_code, currency, settlement_preference)
                     VALUES (:aid, :bank, :holder, :enc, :last4, :routing, :currency, :pref)'
                );
                $bankStmt->execute([
                    'aid' => $applicationId, 'bank' => $bankName, 'holder' => $accountHolder, 'enc' => $encrypted,
                    'last4' => $last4 ?: '0000', 'routing' => $routingCode ?: null, 'currency' => $currency, 'pref' => $settlementPreference,
                ]);

                foreach (['partner_agreement', 'terms', 'privacy', 'commission_agreement', 'compliance_declaration'] as $agreementType) {
                    $agreementStmt = $pdo->prepare(
                        'INSERT INTO partner_agreements (application_id, agreement_type, accepted_at, ip_address) VALUES (:aid, :type, NOW(), :ip)'
                    );
                    $agreementStmt->execute(['aid' => $applicationId, 'type' => $agreementType, 'ip' => client_ip()]);
                }

                $pdo->commit();

                $mailBody = "A new Paynancial partner application was submitted.\n\nApplication ID: {$applicationCode}\nBusiness: {$businessName}\nContact: {$contactPerson} ({$email}, {$mobile})\nPartner type: {$partnerTypes[$partnerType]}\nEngagement model: {$engagementModels[$engagementModel]}";
                @mail(MAIL_SALES_TO, "New partner application — {$applicationCode}", $mailBody, 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>');
                @mail($email, 'Your Paynancial Partner application has been received', "Hi {$contactPerson},\n\nYour Paynancial Partner application has been received. Your application ID is {$applicationCode}.\n\nOur team will review your application and be in touch. You can expect an update on document verification and approval status via email.\n\n— Paynancial Partner Team", 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>');

                $success = $applicationCode;
            } catch (Throwable $e) {
                $pdo->rollBack();
                error_log('[Paynancial] Partner application failed: ' . $e->getMessage());
                $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'Something went wrong while submitting your application. Please try again.';
            }
        }
    }
}
?>
<?php if ($success): ?>
<section class="hero" style="padding-top:72px;">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Application Received</span>
      <h1>You're on your way to becoming a Paynancial Partner.</h1>
      <p class="lead">Your application ID is <strong class="mono" style="color:var(--teal-300);"><?= e($success) ?></strong>. Keep it handy — our team will review your details and documents and follow up by email.</p>
      <div class="hero-actions">
        <a href="/" class="btn btn-primary">Back to Home</a>
        <a href="/contact?intent=partner" class="btn btn-outline">Contact Partner Team</a>
      </div>
    </div>
  </div>
</section>
<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">What Happens Next</span>
      <h2>Your application pipeline</h2>
    </div>
    <div class="journey reveal">
      <?php foreach (['Registration', 'Business Info', 'Documents', 'Compliance Review', 'Agreement', 'Approval', 'Activated'] as $i => $step): ?>
        <div class="journey-step"><div class="num"><?= $i + 1 ?></div><strong><?= e($step) ?></strong></div>
      <?php endforeach; ?>
    </div>
    <p class="text-muted reveal" style="margin-top:24px;">Once approved, you'll receive login credentials for the Paynancial Partner Hub, where you can track status, enroll customers and manage commissions.</p>
  </div>
</section>
<?php return; endif; ?>

<section class="hero" style="padding-top:72px;">
  <div class="container">
    <div class="hero-copy reveal">
      <span class="eyebrow">Paynancial Partner Network</span>
      <h1>Build your payment business through Paynancial.</h1>
      <p class="lead">Enroll customers, recommend Paynancial solutions, track approvals and earn commission — all from one Partner Hub.</p>
    </div>
  </div>
</section>

<section>
  <div class="container" style="max-width:860px;">
    <?php foreach ($errors as $err): ?>
      <div class="form-error is-visible" style="margin-bottom:16px;"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="panel-wizard">
      <ol class="wizard-steps" id="wizard-steps">
        <?php foreach (['Basic Info', 'Business Profile', 'Business Model', 'Documents', 'Bank Details', 'Agreements', 'Review'] as $i => $label): ?>
          <li data-step-indicator="<?= $i + 1 ?>" class="<?= $i === 0 ? 'is-active' : '' ?>"><span><?= $i + 1 ?></span><?= e($label) ?></li>
        <?php endforeach; ?>
      </ol>

      <form method="post" enctype="multipart/form-data" id="partner-register-form" novalidate>
        <?= csrf_field() ?>

        <fieldset class="wizard-step is-active" data-step="1">
          <h3>Basic Information</h3>
          <div class="field"><label>Partner Type</label>
            <select name="partner_type" id="partner_type" required>
              <option value="">Select partner type</option>
              <?php foreach ($partnerTypes as $slug => $label): ?><option value="<?= e($slug) ?>"><?= e($label) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="field-grid">
            <div class="field"><label>Business / Partner Name</label><input type="text" name="business_name" required></div>
            <div class="field"><label>Contact Person</label><input type="text" name="contact_person" required></div>
            <div class="field"><label>Email</label><input type="email" name="email" required></div>
            <div class="field"><label>Mobile</label><input type="text" name="mobile" required></div>
            <div class="field"><label>Country</label><input type="text" name="country"></div>
            <div class="field"><label>State</label><input type="text" name="state"></div>
            <div class="field"><label>City</label><input type="text" name="city"></div>
            <div class="field"><label>Website</label><input type="text" name="website" placeholder="https://"></div>
          </div>
          <div class="field"><label>Business Address</label><textarea name="business_address" rows="3"></textarea></div>
        </fieldset>

        <fieldset class="wizard-step" data-step="2">
          <h3>Business Profile</h3>
          <div class="field-grid">
            <div class="field"><label>Business Type</label><input type="text" name="business_type"></div>
            <div class="field"><label>Industry</label><input type="text" name="industry"></div>
            <div class="field"><label>Years in Business</label>
              <select name="years_in_business"><option value="">Select</option><option>Less than 1 year</option><option>1–3 years</option><option>3–5 years</option><option>5–10 years</option><option>10+ years</option></select>
            </div>
            <div class="field"><label>Number of Employees</label>
              <select name="employee_count"><option value="">Select</option><option>1–10</option><option>11–50</option><option>51–200</option><option>201–500</option><option>500+</option></select>
            </div>
            <div class="field"><label>Existing Customer Base</label>
              <select name="existing_customer_base"><option value="">Select</option><option>None yet</option><option>1–10</option><option>11–50</option><option>51–200</option><option>200+</option></select>
            </div>
            <div class="field"><label>Expected Monthly Transaction Volume</label>
              <select name="expected_monthly_volume"><option value="">Select</option><option>Under ₹5L</option><option>₹5L–₹25L</option><option>₹25L–₹1Cr</option><option>₹1Cr–₹5Cr</option><option>₹5Cr+</option></select>
            </div>
            <div class="field"><label>Current Payment Provider</label><input type="text" name="current_payment_provider" placeholder="If any"></div>
            <div class="field"><label>Primary Markets</label><input type="text" name="primary_markets" placeholder="e.g. India, Southeast Asia"></div>
            <div class="field"><label>Countries Served</label><input type="text" name="countries_served"></div>
          </div>
        </fieldset>

        <fieldset class="wizard-step" data-step="3">
          <h3>Business Model</h3>
          <p class="text-muted">How do you plan to work with Paynancial?</p>
          <div class="option-grid">
            <?php foreach ($engagementModels as $slug => $label): ?>
              <label class="option-card">
                <input type="radio" name="engagement_model" value="<?= e($slug) ?>" required>
                <span><?= e($label) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <fieldset class="wizard-step" data-step="4">
          <h3>KYC / Business Documents</h3>
          <p class="text-muted" id="doc-requirements-note">Select a partner type in Step 1 to see the documents required.</p>
          <div id="doc-upload-fields">
            <?php foreach ($docLabels as $docType => $label): ?>
              <div class="field doc-field" data-doc-type="<?= e($docType) ?>" style="display:none;">
                <label><?= e($label) ?></label>
                <input type="file" name="doc_<?= e($docType) ?>" accept=".pdf,.jpg,.jpeg,.png">
              </div>
            <?php endforeach; ?>
          </div>
          <p class="text-muted" style="font-size:0.8rem;">Accepted formats: PDF, JPG, PNG. Max 5MB each. You can also add or replace documents later from the Partner Hub once your account is approved.</p>
        </fieldset>

        <fieldset class="wizard-step" data-step="5">
          <h3>Bank / Settlement Information</h3>
          <div class="field-grid">
            <div class="field"><label>Bank Name</label><input type="text" name="bank_name" required></div>
            <div class="field"><label>Account Holder</label><input type="text" name="account_holder" required></div>
            <div class="field"><label>Account Number</label><input type="text" name="account_number" required autocomplete="off"></div>
            <div class="field"><label>Routing / IFSC / SWIFT</label><input type="text" name="routing_code"></div>
            <div class="field"><label>Settlement Currency</label>
              <select name="currency"><option value="INR">INR</option><option value="USD">USD</option><option value="EUR">EUR</option><option value="GBP">GBP</option><option value="AED">AED</option></select>
            </div>
            <div class="field"><label>Settlement Preference</label>
              <select name="settlement_preference"><option value="standard">Standard</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option></select>
            </div>
          </div>
          <p class="text-muted" style="font-size:0.8rem;">Your account number is encrypted at rest and only ever shown masked in the Partner Hub.</p>
        </fieldset>

        <fieldset class="wizard-step" data-step="6">
          <h3>Agreements</h3>
          <div class="field" style="gap:14px;">
            <label class="field-row" style="align-items:flex-start;">
              <input type="checkbox" name="agreements_accepted" required style="margin-top:3px;">
              <span>I have read and accept the <a href="/legal/terms-conditions" target="_blank">Terms &amp; Conditions</a>, <a href="/legal/privacy-policy" target="_blank">Privacy Policy</a>, Partner Agreement, Commission Agreement and Compliance Declaration.</span>
            </label>
            <label class="field-row" style="align-items:flex-start;">
              <input type="checkbox" name="accuracy_confirmed" required style="margin-top:3px;">
              <span>I confirm that the information provided is accurate.</span>
            </label>
          </div>
        </fieldset>

        <fieldset class="wizard-step" data-step="7">
          <h3>Review &amp; Submit</h3>
          <div id="review-summary" class="ledger"></div>
          <p class="text-muted" style="margin-top:16px;">Submitting will create your Paynancial Partner application and generate an application ID you can track.</p>
        </fieldset>

        <div class="wizard-nav">
          <button type="button" class="btn btn-outline" id="wizard-back" style="visibility:hidden;">Back</button>
          <button type="button" class="btn btn-primary" id="wizard-next">Next</button>
          <button type="submit" class="btn btn-primary" id="wizard-submit" style="display:none;">Submit Application</button>
        </div>
      </form>
    </div>
  </div>
</section>

<script nonce="<?= csp_nonce() ?>">
(function () {
  var docRequirements = <?= json_encode($docRequirements, JSON_UNESCAPED_SLASHES) ?>;
  var form = document.getElementById('partner-register-form');
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

  var partnerTypeSelect = document.getElementById('partner_type');
  var docNote = document.getElementById('doc-requirements-note');
  var docFields = document.querySelectorAll('.doc-field');
  partnerTypeSelect.addEventListener('change', function () {
    var reqs = docRequirements[partnerTypeSelect.value] || [];
    var reqTypes = reqs.map(function (r) { return r.doc_type; });
    docFields.forEach(function (field) {
      var type = field.getAttribute('data-doc-type');
      field.style.display = reqTypes.indexOf(type) !== -1 ? 'block' : 'none';
    });
    docNote.textContent = reqs.length
      ? 'Based on your partner type, please upload: ' + reqs.map(function (r) { return r.label; }).join(', ') + '.'
      : 'Select a partner type in Step 1 to see the documents required.';
  });

  function buildReviewSummary() {
    var fields = [
      ['Partner Type', 'partner_type'], ['Business Name', 'business_name'], ['Contact Person', 'contact_person'],
      ['Email', 'email'], ['Mobile', 'mobile'], ['Engagement Model', 'engagement_model'],
      ['Bank Name', 'bank_name'], ['Account Holder', 'account_holder'],
    ];
    var html = '';
    fields.forEach(function (f) {
      var el = form.elements[f[1]];
      var value = '';
      if (el && el.type === 'radio') {
        var checked = form.querySelector('input[name="' + f[1] + '"]:checked');
        value = checked ? checked.parentElement.textContent.trim() : '—';
      } else if (el) {
        value = el.tagName === 'SELECT' ? (el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : '') : el.value;
      }
      html += '<div class="ledger-row"><span class="ledger-tag">' + f[0] + '</span><h3 style="font-size:0.95rem;">' + (value || '—') + '</h3><span></span></div>';
    });
    document.getElementById('review-summary').innerHTML = html;
  }

  showStep(1);
})();
</script>
