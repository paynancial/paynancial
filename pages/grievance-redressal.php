<?php
/**
 * Grievance Redressal — /grievance-redressal.
 *
 * Names the Grievance Officer the Privacy Policy refers to and explains how
 * to raise a grievance. Governed (content-governance.php): live but noindex
 * until legal / regulatory review of the process wording is complete.
 *
 * Content rule: only details the business has confirmed — the officer's
 * name and the contact email already published in the Privacy Policy. No
 * response or resolution timelines, escalation levels, phone numbers,
 * addresses or regulator / ombudsman routes until they are confirmed and
 * reviewed.
 */
require_once __DIR__ . '/../includes/standalone-ui.php';

$path = '/grievance-redressal';
$officer = 'Mrs. Anjali Sharma';
$email = 'hello@paynancial.com';
$mailto = 'mailto:' . $email . '?subject=' . rawurlencode('Grievance');
$trail = [['Home', '/'], ['Trust Center', '/trust'], ['Grievance Redressal', $path]];
$page_meta = sp_meta([
    'type'        => 'ContactPage',
    'title'       => 'Grievance Redressal | Grievance Officer | Paynancial',
    'description' => 'How to raise a grievance with Paynancial: who the Grievance Officer is, how to contact them, and what to include so your complaint can be looked into.',
    'path'        => $path,
    'h1'          => 'Grievance Redressal',
    'trail'       => $trail,
]);

ob_start(); ?>
<div class="sp-glance">
  <span class="sp-glance-label">Grievance Officer</span>
  <p><strong><?= e($officer) ?></strong><br>M/S Paynancial Technology Private Limited<br>Email: <a class="inline-link" href="<?= e($mailto) ?>"><?= e($email) ?></a></p>
</div>
<?php $aside = ob_get_clean();

sp_track_view('grievance_view');
sp_hero([
    'trail'   => $trail,
    'eyebrow' => 'Trust · Grievance Redressal',
    'h1'      => 'Grievance Redressal',
    'lead'    => 'If you have a complaint about Paynancial — your account, a payment, our services or how we handle your personal data — you can raise it with our Grievance Officer.',
    'primary' => ['Email the Grievance Officer', $mailto, 'cta_click'],
    'secondary' => ['Support Center', '/support', 'cta_click'],
    'aside'   => $aside,
]);
?>

<?php sp_band_open('officer'); ?>
  <div class="sp-split">
    <?php sp_head('officer', 'Grievance Officer', 'Who handles grievances.'); ?>
    <div class="sp-prose reveal">
      <p>Grievances are handled by <strong><?= e($officer) ?></strong>, Grievance Officer, M/S Paynancial Technology Private Limited, based in Patna, Bihar.</p>
      <p>To raise a grievance, email <a class="inline-link" href="<?= e($mailto) ?>"><?= e($email) ?></a> with “Grievance” in the subject line, addressed to the Grievance Officer.</p>
      <p>For everyday questions about your account, payments or integration, you can also use the <a class="inline-link" href="/support">Support Center</a>.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('how', 'dim'); ?>
  <?php sp_head('how', 'How to raise a grievance', 'What to include in your email.', 'Giving these details up front helps us find your records and look into the issue without coming back to you for more information.'); ?>
  <?php sp_steps([
      ['Who you are', 'Your name and the email address or mobile number registered with Paynancial.'],
      ['Your account', 'Your Paynancial account or merchant ID, if you have one.'],
      ['The transaction', 'For a payment issue: the transaction or order reference, the date and the amount.'],
      ['What happened', 'A short description of the problem and the outcome you are looking for.'],
      ['Supporting documents', 'Screenshots or earlier correspondence, if they help explain the issue.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('safety'); ?>
  <div class="sp-split">
    <?php sp_head('safety', 'Stay safe', 'Never share these details.'); ?>
    <div class="sp-prose reveal">
      <p>Do not include your full card number, CVV, OTP, UPI PIN, passwords or login credentials in a grievance — or share them with anyone who contacts you claiming to be from Paynancial.</p>
      <p>For requests about your personal data — access, correction or deletion — the <a class="inline-link" href="/legal/privacy-policy">Privacy Policy</a> explains your rights and how to exercise them.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related', 'dim'); ?>
  <?php sp_head('related', 'Related', 'Policies and help.'); ?>
  <?php sp_related([
      ['Privacy Policy', 'How we collect, use and protect personal data, and your rights.', '/legal/privacy-policy'],
      ['Terms & Conditions', 'The terms that govern your use of Paynancial.', '/legal/terms-conditions'],
      ['Support Center', 'Help with accounts, payments and integrations.', '/support'],
      ['Trust Center', 'Security, privacy and governance at a glance.', '/trust'],
  ]); ?>
<?php sp_band_close(); ?>
