<?php
$page_meta = [
    'title' => 'Contact Paynancial | Sales, Support & Partnerships',
    'description' => 'Get in touch with Paynancial for sales, partnership, support, career or general enquiries.',
];
$intent = $_GET['intent'] ?? 'general';
$intentMap = ['sales' => 'sales', 'partner' => 'partner', 'support' => 'support', 'career' => 'career', 'general' => 'general', 'signup' => 'sales'];
$activeIntent = $intentMap[$intent] ?? 'general';
?>
<section style="padding-top:56px;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Contact</span>
      <h1>Let's talk.</h1>
      <p class="lead">Tell us what you need and the right team at Paynancial will follow up.</p>
    </div>
  </div>
</section>

<section class="section-subtle">
  <div class="container" style="max-width:640px;">
    <div class="card reveal">
      <div class="role-tabs" role="tablist" style="grid-template-columns:repeat(3,1fr);margin-bottom:22px;">
        <button type="button" class="role-tab contact-tab <?= $activeIntent === 'sales' ? 'is-active' : '' ?>" data-intent="sales"><strong>Sales</strong></button>
        <button type="button" class="role-tab contact-tab <?= $activeIntent === 'partner' ? 'is-active' : '' ?>" data-intent="partner"><strong>Partner</strong></button>
        <button type="button" class="role-tab contact-tab <?= $activeIntent === 'support' ? 'is-active' : '' ?>" data-intent="support"><strong>Support</strong></button>
        <button type="button" class="role-tab contact-tab <?= $activeIntent === 'career' ? 'is-active' : '' ?>" data-intent="career"><strong>Career</strong></button>
        <button type="button" class="role-tab contact-tab <?= $activeIntent === 'general' ? 'is-active' : '' ?>" data-intent="general"><strong>General</strong></button>
      </div>

      <form id="contact-form" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="type" id="contact-type" value="<?= e($activeIntent) ?>">
        <div style="display:grid;gap:16px;">
          <div class="field"><label for="c-name">Name</label><input id="c-name" name="name" type="text" required></div>
          <div class="field"><label for="c-company">Company</label><input id="c-company" name="company" type="text"></div>
          <div class="field"><label for="c-email">Email</label><input id="c-email" name="email" type="email" required></div>
          <div class="field"><label for="c-mobile">Mobile</label><input id="c-mobile" name="mobile" type="tel"></div>
          <div class="field"><label for="c-subject">Subject</label><input id="c-subject" name="subject" type="text"></div>
          <div class="field"><label for="c-message">Message</label>
            <textarea id="c-message" name="message" rows="5" required style="padding:12px 14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);resize:vertical;"></textarea>
          </div>
          <div class="form-error" role="alert"></div>
          <div id="contact-success" style="display:none;background:#e7f8ee;color:var(--success);padding:14px;border-radius:var(--radius-sm);font-size:0.88rem;"></div>
          <button type="submit" class="btn btn-primary btn-block">Submit Enquiry</button>
        </div>
      </form>
    </div>
  </div>
</section>

<section>
  <div class="container grid grid-4">
    <div class="card reveal"><h3>Address</h3><p>Sharda Mansion, Kailashpuri, Kankarbagh, Hanuman Nagar, Patna, Bihar 800020</p></div>
    <div class="card reveal"><h3>Email</h3><p><a href="mailto:hello@paynancial.com">hello@paynancial.com</a></p></div>
    <div class="card reveal"><h3>Mobile</h3><p><a href="tel:+917066820820">+91 7066 820 820</a></p></div>
    <div class="card reveal"><h3>Phone</h3><p><a href="tel:+916122999382">0612 299 9382</a></p></div>
  </div>
</section>

<section class="section-subtle">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Find Us</span>
      <h2>Visit our office</h2>
    </div>
  </div>
  <div class="full-bleed reveal">
    <iframe
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3598.44364130444!2d85.15587967453587!3d25.59017037746008!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x81a5f16d60d68927%3A0x87d0b4066c787f35!2sPaynancial!5e0!3m2!1sen!2sin!4v1788035442726!5m2!1sen!2sin"
      width="100%" height="420" style="border:0;display:block;" allowfullscreen="" loading="lazy"
      referrerpolicy="strict-origin-when-cross-origin" title="Paynancial office location on map">
    </iframe>
  </div>
</section>

<script nonce="<?= csp_nonce() ?>">
document.querySelectorAll('.contact-tab').forEach(function (tab) {
  tab.addEventListener('click', function () {
    document.querySelectorAll('.contact-tab').forEach(function (t) { t.classList.remove('is-active'); });
    tab.classList.add('is-active');
    document.getElementById('contact-type').value = tab.getAttribute('data-intent');
  });
});
document.getElementById('contact-form').addEventListener('submit', function (e) {
  e.preventDefault();
  var form = e.target;
  var errorBox = form.querySelector('.form-error');
  var successBox = document.getElementById('contact-success');
  errorBox.classList.remove('is-visible');
  successBox.style.display = 'none';
  var payload = Object.fromEntries(new FormData(form).entries());
  fetch('/api/contact/submit', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload), credentials: 'same-origin'
  }).then(function (r) { return r.json(); }).then(function (data) {
    if (data.ok) {
      successBox.textContent = 'Thank you — your enquiry ID is ' + data.enquiry_code + '. Our team will be in touch shortly.';
      successBox.style.display = 'block';
      form.reset();
    } else {
      errorBox.textContent = data.error || 'Something went wrong. Please try again.';
      errorBox.classList.add('is-visible');
    }
  }).catch(function () {
    errorBox.textContent = 'Network error. Please try again.';
    errorBox.classList.add('is-visible');
  });
});
</script>
