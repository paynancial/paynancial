/**
 * FloatingEnquiryWidget — behaviour for includes/floating-enquiry.php.
 * No dependencies; loaded with `defer`.
 *
 * Desktop (>640px): non-modal popover above the capsule. Closes on
 * Escape, the × button, or a click outside.
 * Mobile (≤640px): modal bottom sheet with backdrop, background scroll
 * lock and a focus trap. Closes on Escape, backdrop tap, × or "Close".
 *
 * "Request a Callback" form (only when rendered): Cloudflare Turnstile is
 * loaded lazily the first time the form opens, runs invisibly
 * (interaction-only) and is executed right before submission. The token is
 * verified server-side (/api/enquiry/callback). WhatsApp, email and call
 * links never involve the CAPTCHA.
 *
 * Analytics: the site has no analytics stack today, so events are pushed
 * to window.dataLayer / gtag only if one is present, and always dispatched
 * as a `paynancial:track` DOM event for whatever is added later.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-fe]');
  if (!root) return;

  var trigger = root.querySelector('[data-fe-trigger]');
  var panel = root.querySelector('[data-fe-panel]');
  var backdrop = root.querySelector('[data-fe-backdrop]');
  var mobileQuery = window.matchMedia('(max-width: 640px)');
  var lastFocus = null;

  function isOpen() { return root.classList.contains('is-open'); }
  function isMobile() { return mobileQuery.matches; }

  function track(event, action) {
    var detail = {
      event: event,
      action: action || event,
      page_url: window.location.pathname,
      page_type: root.getAttribute('data-fe-context') || 'default',
      cta_context: root.getAttribute('data-fe-context') || 'default',
      cta_label: root.getAttribute('data-fe-cta') || '',
      device_type: isMobile() ? 'mobile' : 'desktop'
    };
    try {
      if (Array.isArray(window.dataLayer)) window.dataLayer.push(detail);
      if (typeof window.gtag === 'function') window.gtag('event', event, detail);
      document.dispatchEvent(new CustomEvent('paynancial:track', { detail: detail }));
    } catch (e) { /* analytics must never break the widget */ }
  }

  function focusables() {
    return Array.prototype.filter.call(
      panel.querySelectorAll('a[href], button:not([disabled]), input:not([type=hidden]):not([tabindex="-1"]), select, textarea'),
      function (el) { return el.offsetParent !== null || el === document.activeElement; }
    );
  }

  function open() {
    if (isOpen()) return;
    lastFocus = document.activeElement;
    root.classList.add('is-open');
    trigger.setAttribute('aria-expanded', 'true');
    trigger.setAttribute('aria-label', 'Close contact options');
    panel.setAttribute('aria-modal', isMobile() ? 'true' : 'false');
    if (isMobile()) document.documentElement.classList.add('fe-lock');
    // Move focus into the panel once it is visible (after the class flip).
    window.requestAnimationFrame(function () {
      var first = form && !form.hidden ? form.querySelector('input[name=name]') : panel.querySelector('.fe-action');
      if (first) first.focus({ preventScroll: true });
    });
    track('floating_enquiry_open', 'open');
  }

  function close(reason) {
    if (!isOpen()) return;
    root.classList.remove('is-open');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-label', trigger.getAttribute('data-fe-label') || 'Contact options');
    document.documentElement.classList.remove('fe-lock');
    // After a completed enquiry, the next open starts from the options list.
    if (done && !done.hidden) showView('list');
    if (reason !== 'navigate') {
      var target = lastFocus && document.body.contains(lastFocus) && lastFocus !== document.body ? lastFocus : trigger;
      if (reason === 'escape' || reason === 'button' || isMobile()) target = trigger;
      target.focus({ preventScroll: true });
    }
    track('floating_enquiry_close', reason || 'close');
  }

  trigger.addEventListener('click', function () {
    if (isOpen()) close('button'); else open();
  });

  Array.prototype.forEach.call(root.querySelectorAll('[data-fe-close]'), function (btn) {
    btn.addEventListener('click', function () { close('button'); });
  });
  if (backdrop) backdrop.addEventListener('click', function () { close('backdrop'); });

  Array.prototype.forEach.call(root.querySelectorAll('[data-fe-action]'), function (link) {
    link.addEventListener('click', function () {
      var kind = link.getAttribute('data-fe-action');
      var events = { whatsapp: 'whatsapp_click', email: 'email_click', call: 'call_click', form: 'contact_page_click' };
      track(events[kind] || 'floating_enquiry_click', kind);
      // WhatsApp opens in a new tab; mailto/tel hand off to another app.
      // Close so the page is clean when the visitor comes back.
      window.setTimeout(function () { close('navigate'); }, 0);
    });
  });

  document.addEventListener('keydown', function (e) {
    if (!isOpen()) return;
    if (e.key === 'Escape') {
      e.preventDefault();
      close('escape');
      return;
    }
    if (e.key === 'Tab' && isMobile()) {
      var items = focusables();
      if (!items.length) return;
      var first = items[0];
      var last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  // Desktop popover: a click anywhere outside the widget closes it.
  document.addEventListener('mousedown', function (e) {
    if (isOpen() && !isMobile() && !root.contains(e.target)) close('outside');
  });

  // Crossing the breakpoint while open: reset to a clean closed state.
  var onBreakpoint = function () { if (isOpen()) close('resize'); };
  if (mobileQuery.addEventListener) mobileQuery.addEventListener('change', onBreakpoint);
  else if (mobileQuery.addListener) mobileQuery.addListener(onBreakpoint);

  // Step aside when the site's own login panel or mobile menu opens.
  Array.prototype.forEach.call(document.querySelectorAll('[data-login-open], .hamburger'), function (el) {
    el.addEventListener('click', function () { close('navigate'); });
  });

  /* ---------------------------------------------------------------
     Request a Callback form
     --------------------------------------------------------------- */
  var form = root.querySelector('[data-fe-form]');
  var done = root.querySelector('[data-fe-done]');
  var list = root.querySelector('.fe-list');
  var foot = root.querySelector('.fe-foot');
  var title = root.querySelector('#fe-title');
  var desc = root.querySelector('#fe-desc');
  var baseTitle = title ? title.textContent : '';
  var baseDesc = desc ? desc.textContent : '';

  function showView(view) {
    var isForm = view === 'form';
    var isDone = view === 'done';
    if (list) list.hidden = isForm || isDone;
    if (foot) foot.hidden = isForm || isDone;
    if (form) form.hidden = !isForm;
    if (done) done.hidden = !isDone;
    if (title) title.textContent = isForm ? 'Request a callback' : (isDone ? 'Enquiry received' : baseTitle);
    if (desc) desc.textContent = isForm ? 'Share your details and a Paynancial expert will call you back.' : (isDone ? 'Thank you for contacting Paynancial.' : baseDesc);
  }

  if (form) {
    var status = form.querySelector('[data-fe-status]');
    var submitBtn = form.querySelector('[data-fe-submit]');
    var captchaBox = form.querySelector('[data-fe-captcha]');
    var openedAt = 0;
    var widgetId = null;
    var captchaState = 'idle'; // idle | loading | ready | failed
    var pendingToken = null;
    var submitting = false;

    var setStatus = function (msg) { status.textContent = msg || ''; status.classList.toggle('is-visible', !!msg); };

    var loadCaptcha = function () {
      if (captchaState !== 'idle') return;
      captchaState = 'loading';
      window.__feTurnstileReady = function () {
        try {
          widgetId = window.turnstile.render(captchaBox, {
            sitekey: form.getAttribute('data-sitekey'),
            execution: 'execute',          // run only when we ask: right before submit
            appearance: 'interaction-only', // invisible unless a visitor must interact
            size: 'flexible',
            action: 'floating_callback',
            callback: function (token) { if (pendingToken) { pendingToken.resolve(token); pendingToken = null; } },
            'error-callback': function () { if (pendingToken) { pendingToken.reject('error'); pendingToken = null; } return true; },
            'expired-callback': function () { try { window.turnstile.reset(widgetId); } catch (e) {} },
            'timeout-callback': function () { if (pendingToken) { pendingToken.reject('timeout'); pendingToken = null; } }
          });
          captchaState = 'ready';
        } catch (e) { captchaState = 'failed'; }
      };
      var src = form.getAttribute('data-captcha-src');
      var tag = document.createElement('script');
      tag.src = src + (src.indexOf('?') === -1 ? '?' : '&') + 'onload=__feTurnstileReady';
      tag.async = true;
      tag.defer = true;
      tag.onerror = function () { captchaState = 'failed'; };
      document.head.appendChild(tag);
    };

    var getToken = function () {
      return new Promise(function (resolve, reject) {
        if (captchaState !== 'ready' || !window.turnstile) { reject('unavailable'); return; }
        var existing = window.turnstile.getResponse(widgetId);
        if (existing) { resolve(existing); return; }
        pendingToken = { resolve: resolve, reject: reject };
        try { window.turnstile.execute(widgetId); } catch (e) { pendingToken = null; reject('error'); }
        window.setTimeout(function () { if (pendingToken) { pendingToken.reject('timeout'); pendingToken = null; } }, 45000);
      });
    };

    var waitForCaptcha = function () {
      return new Promise(function (resolve) {
        var tries = 0;
        (function check() {
          if (captchaState === 'ready' || captchaState === 'failed' || tries++ > 50) resolve();
          else window.setTimeout(check, 100);
        })();
      });
    };

    var fieldError = function (name, msg) {
      var input = form.querySelector('[name="' + name + '"]');
      var err = form.querySelector('#fe-' + name + '-err');
      if (!input || !err) return;
      err.textContent = msg || '';
      if (msg) input.setAttribute('aria-invalid', 'true'); else input.removeAttribute('aria-invalid');
    };

    var clientValidate = function () {
      var errors = {};
      var v = function (n) { return (form.elements[n].value || '').trim(); };
      if (!v('name')) errors.name = 'Please enter your name.';
      if (!v('email')) errors.email = 'Please enter your business email.';
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v('email'))) errors.email = 'Please enter a valid email address.';
      var phone = v('phone').replace(/[\s\-().]/g, '');
      if (!v('phone')) errors.phone = 'Please enter your phone number.';
      else if (!/^\+?[0-9]{10,15}$/.test(phone)) errors.phone = 'Please enter a valid phone number, with country code if outside India.';
      return errors;
    };

    var applyErrors = function (errors) {
      ['name', 'email', 'phone', 'company', 'requirement', 'message'].forEach(function (n) { fieldError(n, errors[n]); });
      var firstBad = form.querySelector('[aria-invalid=true]');
      if (firstBad) firstBad.focus();
    };

    var setBusy = function (busy) {
      submitting = busy;
      submitBtn.disabled = busy;
      submitBtn.classList.toggle('is-busy', busy);
      submitBtn.querySelector('span').textContent = busy ? 'Sending…' : 'Send Enquiry';
    };

    var resetCaptcha = function () { try { if (widgetId !== null) window.turnstile.reset(widgetId); } catch (e) {} };

    var openForm = function () {
      showView('form');
      openedAt = Date.now();
      loadCaptcha();
      track('floating_form_open', 'form');
      window.requestAnimationFrame(function () { form.elements.name.focus({ preventScroll: true }); });
    };

    root.querySelector('[data-fe-open-form]').addEventListener('click', openForm);
    form.querySelector('[data-fe-back]').addEventListener('click', function () {
      showView('list');
      var btn = root.querySelector('[data-fe-open-form]');
      if (btn) btn.focus({ preventScroll: true });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (submitting) return; // no duplicate submissions
      setStatus('');
      var errors = clientValidate();
      applyErrors(errors);
      if (Object.keys(errors).length) { setStatus('Please check the required fields.'); track('floating_form_error', 'validation'); return; }

      track('floating_form_submit', 'submit');
      setBusy(true);
      waitForCaptcha().then(getToken).then(function (token) {
        var data = {};
        ['csrf_token', 'context', 'name', 'email', 'phone', 'company', 'requirement', 'message', 'company_website'].forEach(function (n) {
          data[n] = form.elements[n] ? form.elements[n].value : '';
        });
        data.cf_turnstile_response = token;
        data.elapsed_ms = Date.now() - openedAt;
        return fetch(form.getAttribute('data-endpoint'), {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(data)
        }).then(function (res) { return res.json().catch(function () { return { ok: false }; }); });
      }, function () {
        // Token could not be obtained (script blocked, challenge failed or timed out).
        track('captcha_failed', 'client');
        return { ok: false, error: 'Please complete the security check and try again.', captcha: 'retry' };
      }).then(function (res) {
        setBusy(false);
        if (res && res.ok) {
          track('floating_form_success', 'success');
          var ref = done.querySelector('[data-fe-ref]');
          ref.textContent = res.enquiry_code ? ' Your reference is ' + res.enquiry_code + '.' : '';
          form.reset();
          resetCaptcha();
          showView('done');
          done.focus({ preventScroll: true });
          return;
        }
        if (res && res.fields) applyErrors(res.fields);
        if (res && res.captcha === 'retry') { track('captcha_failed', 'server'); resetCaptcha(); }
        track('floating_form_error', (res && res.captcha) ? 'captcha' : 'server');
        setStatus((res && res.error) || "We couldn't submit your enquiry right now. Please try again.");
      }).catch(function () {
        setBusy(false);
        resetCaptcha();
        track('floating_form_error', 'network');
        setStatus("We couldn't submit your enquiry right now. Please try again.");
      });
    });

  }

  root.hidden = false;
})();
