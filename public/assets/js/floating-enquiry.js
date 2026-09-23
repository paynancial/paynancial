/**
 * FloatingEnquiryWidget — behaviour for includes/floating-enquiry.php.
 * No dependencies; loaded with `defer`.
 *
 * Desktop (>640px): non-modal popover above the capsule. Closes on
 * Escape, the × button, or a click outside.
 * Mobile (≤640px): modal bottom sheet with backdrop, background scroll
 * lock and a focus trap. Closes on Escape, backdrop tap, × or "Close".
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
      panel.querySelectorAll('a[href], button:not([disabled])'),
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
      var first = panel.querySelector('.fe-action');
      if (first) first.focus({ preventScroll: true });
    });
    track('floating_enquiry_open', 'open');
  }

  function close(reason) {
    if (!isOpen()) return;
    root.classList.remove('is-open');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.removeAttribute('aria-label');
    document.documentElement.classList.remove('fe-lock');
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
      var events = { whatsapp: 'floating_whatsapp_click', email: 'floating_email_click', call: 'floating_call_click', form: 'floating_form_click' };
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

  root.hidden = false;
})();
