<?php
/**
 * Partner Program — /partner-program (canonical; /partners 301s here).
 *
 * The search-visible information page for the Paynancial Partner Program.
 * The application form at /partner/register is a noindex conversion
 * endpoint (content-governance.php).
 *
 * Content rule: only what Paynancial already publishes — the three partner
 * types, the Partner Hub, the application form's applicant types,
 * engagement models, steps, document types and agreements, and the
 * published onboarding stages. No commission rates, payout timelines,
 * approval times, partner counts or certification claims.
 */
require_once __DIR__ . '/../includes/faq-data.php';
require_once __DIR__ . '/../includes/standalone-ui.php';

$faqs = faq_set('partner-program');
$path = '/partner-program';
$trail = [['Home', '/'], ['Company', '/about'], ['Partner Program', $path]];
$page_meta = sp_meta([
    'title'       => 'Partner Program | Referral, Reseller & Technology Partners | Paynancial',
    'description' => 'The Paynancial Partner Program for referral, reseller and technology partners: who can apply, how partnership works, the application and onboarding steps, the Partner Hub, and how to apply.',
    'path'        => $path,
    'h1'          => 'Grow with Paynancial as a referral, reseller or technology partner.',
    'trail'       => $trail,
    'faqs'        => $faqs,
]);
$apply = ['Apply as a Partner', '/partner/register', 'cta_click'];

ob_start(); ?>
<div class="sp-glance">
  <span class="sp-glance-label">In short</span>
  <p>The Paynancial Partner Program is for businesses and individuals who refer, resell or integrate Paynancial. Partners apply online, go through document and compliance review, accept the partner agreements and, once approved, work from the Paynancial Partner Hub — enrolling customers, tracking approvals, monitoring transactions and managing commission.</p>
</div>
<?php $aside = ob_get_clean();

sp_track_view('partner_program_view');
sp_hero([
    'trail'     => $trail,
    'eyebrow'   => 'Company · Partner Program',
    'h1'        => 'Grow with Paynancial as a referral, reseller or technology partner.',
    'lead'      => 'Bring businesses to Paynancial, resell its payment solutions, or build it into your own software — and manage everything from one Partner Hub.',
    'primary'   => $apply,
    'secondary' => ['Partner Login', '/?login=partner', 'cta_click'],
    'values'    => ['Referral partners', 'Reseller partners', 'Technology partners', 'One Partner Hub'],
    'aside'     => $aside,
]);
?>
<nav class="sp-index" aria-label="On this page">
  <div class="sp-wrap"><ul>
    <li><a href="#overview">Overview</a></li>
    <li><a href="#types">Partner types</a></li>
    <li><a href="#who">Who can partner</a></li>
    <li><a href="#how">How it works</a></li>
    <li><a href="#requirements">Requirements</a></li>
    <li><a href="#onboarding">Onboarding</a></li>
    <li><a href="#hub">Partner Hub</a></li>
    <li><a href="#faq">FAQ</a></li>
  </ul></div>
</nav>

<?php sp_band_open('overview'); ?>
  <div class="sp-split">
    <?php sp_head('overview', 'Overview', 'What the Partner Program is.'); ?>
    <div class="sp-prose reveal">
      <p>Many businesses first hear about a payment provider from someone they already trust: an accountant, a software vendor, an agency, a consultant. The Paynancial Partner Program gives those people a structured way to work with Paynancial — whether that means introducing a business, selling Paynancial as part of their own offering, or embedding it in their own product.</p>
      <p>Partners apply once, are reviewed before they are activated, and then work from the Paynancial Partner Hub: a single place to enroll customers, get solution recommendations, submit KYC, track approvals, monitor transactions and manage commission.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('types', 'dim'); ?>
  <?php sp_head('types', 'Partner types', 'Three ways to partner.', 'Choose the model that matches how you work with businesses today.'); ?>
  <?php sp_related([
      ['Referral partners', 'Refer businesses to Paynancial and earn commission on successful onboarding.', '/partner/register'],
      ['Reseller partners', 'Resell Paynancial products under your own commercial relationship with merchants.', '/partner/register'],
      ['Technology partners', 'Integrate Paynancial into your own software platform or marketplace — SaaS platforms and marketplaces embed Payment, Payout and Billing APIs directly.', '/partner/register'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('who'); ?>
  <div class="sp-split">
    <?php sp_head('who', 'Who can partner', 'Individuals and organisations of every shape.', 'The application asks which of these describes you, and how you plan to work with Paynancial.'); ?>
    <div>
      <?php sp_table(['You apply as', 'And work with Paynancial through'], [
          ['Individual · Consultant', 'Customer Referral · Customer Enrollment'],
          ['Company · Agency', 'Customer Referral · Customer Enrollment · Payment Solution Reseller'],
          ['Reseller · Distributor', 'Payment Solution Reseller'],
          ['Technology Partner', 'Technology Integration · API Partner'],
          ['Enterprise Partner', 'Enterprise Partnership · Strategic Partnership'],
          ['Other', 'Tell us in the application'],
      ], 'Applicant types and engagement models'); ?>
      <p class="reveal" style="margin-top:14px;font-size:0.9rem;color:var(--text-muted);">The pairings are illustrative: you choose your applicant type and engagement model independently in the application.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('benefits', 'ink'); ?>
  <?php sp_head('benefits', 'Program benefits', 'What partners get.'); ?>
  <?php sp_answers([
      ['Commission', 'Referral partners earn commission on successful onboarding; commission is tracked and managed in the Partner Hub. Commission terms are set out in the commission agreement you accept when you apply.'],
      ['One Partner Hub', 'Enroll customers, get solution recommendations, submit KYC, track approvals, monitor transactions and manage commission in one place.'],
      ['A real product catalog', 'Payment Gateway, Payment Links, Smart Collections, Payouts and Payment Analytics — products with their own published pages you can point customers to.'],
      ['Material to sell with', 'The Partner Hub has a Knowledge Center for guides and documentation, and a Marketing Hub for brochures, presentations and templates published by Paynancial.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('how', 'dim'); ?>
  <?php sp_head('how', 'How partnership works', 'From first customer to commission.', 'The Partner Hub journey, as published.'); ?>
  <?php sp_steps([
      ['Discover', 'Understand the customer\'s business and what they need.'],
      ['Enroll', 'Enroll the customer in the Partner Hub.'],
      ['Recommend', 'Get a solution recommendation for the customer.'],
      ['Submit', 'Submit the customer\'s KYC and application.'],
      ['Activate', 'Track the approval through to activation.'],
      ['Manage', 'Monitor the customer\'s transactions.'],
      ['Grow', 'Bring on more customers from the same place.'],
      ['Earn', 'Track and manage your commission.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('requirements'); ?>
  <div class="sp-split">
    <?php sp_head('requirements', 'Requirements', 'What the application asks for.', 'The application is a seven-step form. The documents required depend on your partner type — the form shows the list once you choose it.'); ?>
    <div>
      <?php sp_table(['Step', 'What you provide'], [
          ['1. Basic information', 'Partner type, business or partner name, contact person, email and mobile, location.'],
          ['2. Business profile', 'Business type, industry, years in business, team size and website.'],
          ['3. Business model', 'How you plan to work with Paynancial, your markets and existing customer base.'],
          ['4. KYC / business documents', 'Documents for your partner type — for example company registration, tax registration, GST / VAT registration, business licence, authorised signatory ID, address proof, bank details proof. PDF, JPG or PNG, up to 5 MB each.'],
          ['5. Bank / settlement information', 'The account for your commission settlements.'],
          ['6. Agreements', 'The partner agreement, terms, privacy policy, commission agreement and compliance declaration.'],
          ['7. Review & submit', 'Check everything and submit. You receive an application ID by email.'],
      ], 'Partner application steps'); ?>
      <p class="reveal" style="margin-top:14px;font-size:0.9rem;color:var(--text-muted);">You can add or replace documents later from the Partner Hub once your account is approved.</p>
    </div>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('onboarding', 'dim'); ?>
  <?php sp_head('onboarding', 'Onboarding process', 'From application to active partner.'); ?>
  <?php sp_steps([
      ['Registration', 'Submit the online application.'],
      ['Business info', 'Your business details are reviewed.'],
      ['Documents', 'Your documents are verified.'],
      ['Compliance review', 'Your application goes through compliance review.'],
      ['Agreement', 'The partner agreements you accepted are recorded.'],
      ['Approval', 'The team approves the application and follows up by email.'],
      ['Activated', 'You receive Partner Hub login credentials and can start enrolling customers.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('hub'); ?>
  <?php sp_head('hub', 'Partner resources', 'Everything in the Partner Hub.', 'Available to approved partners after activation.'); ?>
  <?php sp_answers([
      ['Customers', 'Enroll customers and follow each application from submission to approval.'],
      ['Transactions & settlements', 'Monitor your customers\' transactions and your settlements.'],
      ['Commissions & performance', 'See what you have earned and how your portfolio is performing.'],
      ['Proposals & payment links', 'Prepare proposals for customers and work with payment links.'],
      ['Knowledge Center', 'Guides and documentation to help you sell and support Paynancial solutions.'],
      ['Marketing Hub', 'Brochures, presentations and templates for your outreach.'],
      ['Team & support', 'Manage your team and get support from Paynancial.'],
      ['Profile & documents', 'Keep your details and documents up to date.'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_band_open('trust', 'ink'); ?>
  <?php sp_head('trust', 'Trust & compliance', 'Built on the same foundations as the platform.'); ?>
  <?php sp_answers([
      ['Reviewed before activation', 'Every partner goes through document verification and compliance review before the Partner Hub is activated.'],
      ['Agreements on record', 'Accepted agreements are recorded with the application, so both sides know the terms.'],
      ['Protected data', 'Sensitive data at rest — including KYC documents and bank details — is encrypted, as listed as verified in the Trust Center.'],
      ['Nothing overstated', 'Certifications and regulatory authorisations are not claimed here; the Trust Center shows what is verified and what is still to be confirmed.'],
  ]); ?>
  <p class="reveal" style="margin-top:28px;color:rgba(250,249,245,0.74);"><a class="inline-link" href="/trust" style="color:var(--teal-300);">Trust Center</a> · <a class="inline-link" href="/security" style="color:var(--teal-300);">Security &amp; Compliance</a> · <a class="inline-link" href="/legal/terms-conditions" style="color:var(--teal-300);">Terms &amp; Conditions</a></p>
<?php sp_band_close(); ?>

<?php sp_band_open('faq', 'dim'); ?>
  <div class="sp-split">
    <?php sp_head('faq', 'FAQ', 'Partner Program questions.'); ?>
    <?php sp_faq($faqs); ?>
  </div>
<?php sp_band_close(); ?>

<?php sp_band_open('related'); ?>
  <?php sp_head('related', 'Explore', 'What you would be offering.'); ?>
  <?php sp_related([
      ['Products', 'The full Paynancial product catalog.', '/products'],
      ['Developer Hub', 'For technology partners integrating the API.', '/developers'],
      ['Embedded Finance', 'How platforms build payments into their own product.', '/embedded-finance'],
      ['About Paynancial', 'The company behind the platform.', '/about'],
  ]); ?>
<?php sp_band_close(); ?>

<?php sp_cta('Ready to become a Paynancial partner?', 'Apply online in seven steps. Our team reviews your details and documents and follows up by email.', [
    $apply,
    ['Talk to us first', '/contact?intent=sales&product=partner-program', 'cta_click'],
]); ?>
