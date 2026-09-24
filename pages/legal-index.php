<?php
/**
 * Legal index — /legal. A directory of Paynancial's policies and legal
 * documents. It only links to them: no legal wording lives here, so the
 * hold on legal content (pending qualified legal review) is unaffected.
 * Descriptions mirror each document's own summary line.
 */
require_once __DIR__ . '/../includes/standalone-ui.php';

$path = '/legal';
$trail = [['Home', '/'], ['Legal', $path]];
$page_meta = sp_meta([
    'type'        => 'CollectionPage',
    'title'       => 'Legal | Policies & Legal Documents | Paynancial',
    'description' => 'Paynancial\'s policies and legal documents in one place: Privacy Policy, Terms & Conditions, Refund Policy, Cookie Policy and how to raise a grievance.',
    'path'        => $path,
    'h1'          => 'Legal',
    'trail'       => $trail,
]);

sp_hero([
    'trail'   => $trail,
    'eyebrow' => 'Company · Legal',
    'h1'      => 'Legal',
    'lead'    => 'Paynancial\'s policies and legal documents in one place — how we handle personal data, the terms of using Paynancial, and how to raise a concern.',
]);
?>

<?php sp_band_open('documents'); ?>
  <?php sp_head('documents', 'Policies', 'Legal documents.', 'Each document applies to M/S Paynancial Technology Private Limited and its website, dashboard, APIs and services.'); ?>
  <?php sp_related([
      ['Privacy Policy', 'What personal data Paynancial collects, why, how it is used and protected, and the choices you have over it.', '/legal/privacy-policy'],
      ['Terms & Conditions', 'The rules that govern your use of Paynancial\'s website, dashboard, APIs and payment infrastructure services.', '/legal/terms-conditions'],
      ['Refund Policy', 'How refunds, failed transactions and disputed payments are handled across the Paynancial platform.', '/legal/refund-policy'],
      ['Cookie Policy', 'What cookies Paynancial uses, why, and how to control them.', '/legal/cookie-policy'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('help', 'dim'); ?>
  <?php sp_head('help', 'Concerns & security', 'Raising a concern.'); ?>
  <?php sp_related([
      ['Grievance Redressal', 'How to raise a grievance with our Grievance Redressal Officer, and what to include.', '/grievance-redressal'],
      ['Security & Compliance', 'How Paynancial protects data and transactions, and how to report a vulnerability.', '/security'],
      ['Trust Center', 'Security, privacy and governance at a glance.', '/trust'],
      ['Contact', 'Talk to our team about anything not covered here.', '/contact'],
  ]); ?>
<?php sp_band_close(); ?>
