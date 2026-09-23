/**
 * Paynancial — site interactions: mega menu, mobile nav, left-side login
 * panel, scroll reveal, animated counters, developer code tabs.
 */
(function () {
  'use strict';

  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------------------------------------------------------------
     Mega menu (desktop) — opens on hover (stays open while the cursor
     moves into the dropdown) and on click/Enter for keyboard users.
     --------------------------------------------------------------- */
  var navItems = document.querySelectorAll('.nav-item');
  var closeTimer = null;

  function setNavItemOpen(item, open) {
    item.classList.toggle('is-open', open);
    var trigger = item.querySelector('.nav-link[aria-haspopup]');
    if (trigger) trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  function closeAllNavItems() {
    navItems.forEach(function (i) { setNavItemOpen(i, false); });
  }

  navItems.forEach(function (item) {
    var trigger = item.querySelector('.nav-link');
    if (!trigger || !item.querySelector('.mega-menu')) return;

    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      var isOpen = item.classList.contains('is-open');
      closeAllNavItems();
      if (!isOpen) setNavItemOpen(item, true);
    });

    item.addEventListener('mouseenter', function () {
      if (window.matchMedia('(hover: hover)').matches) {
        clearTimeout(closeTimer);
        closeAllNavItems();
        setNavItemOpen(item, true);
      }
    });
    item.addEventListener('mouseleave', function () {
      if (window.matchMedia('(hover: hover)').matches) {
        closeTimer = setTimeout(function () { setNavItemOpen(item, false); }, 150);
      }
    });
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.nav-item')) closeAllNavItems();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllNavItems();
  });

  /* Utility bar language selector (English is the only wired option today —
     other languages are listed but disabled until real translations ship). */
  var langSelect = document.querySelector('[data-lang-select]');
  if (langSelect) {
    var langBtn = langSelect.querySelector('.utility-lang-btn');
    langBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = langSelect.classList.toggle('is-open');
      langBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('[data-lang-select]')) {
        langSelect.classList.remove('is-open');
        langBtn.setAttribute('aria-expanded', 'false');
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        langSelect.classList.remove('is-open');
        langBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* Sticky header: shrink + hide utility bar past a scroll threshold.
     Also publishes the header's live rendered height as --header-h so
     off-canvas panels (login) can sit below it instead of covering it. */
  var siteHeader = document.querySelector('.site-header');
  function updateHeaderHeightVar() {
    if (siteHeader) {
      /* bottom (not height) — the utility bar sits above .site-header in
         normal flow, so bottom already includes it; height alone would
         leave the panel overlapping the header's lower-right corner. */
      document.documentElement.style.setProperty('--header-h', siteHeader.getBoundingClientRect().bottom + 'px');
    }
  }
  if (siteHeader) {
    var lastScrolled = false;
    var onScroll = function () {
      var scrolled = window.scrollY > 24;
      if (scrolled !== lastScrolled) {
        siteHeader.classList.toggle('is-scrolled', scrolled);
        lastScrolled = scrolled;
        updateHeaderHeightVar();
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', updateHeaderHeightVar);
    onScroll();
    updateHeaderHeightVar();
  }

  /* ---------------------------------------------------------------
     Mobile nav drawer
     --------------------------------------------------------------- */
  var hamburger = document.querySelector('.hamburger');
  var mobileNav = document.querySelector('.mobile-nav');
  var mobileNavClose = document.querySelector('.mobile-nav-close');
  function toggleMobileNav(open) {
    if (!mobileNav) return;
    if (open) updateHeaderHeightVar();
    mobileNav.classList.toggle('is-open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  if (hamburger) hamburger.addEventListener('click', function () { toggleMobileNav(true); });
  if (mobileNavClose) mobileNavClose.addEventListener('click', function () { toggleMobileNav(false); });

  /* ---------------------------------------------------------------
     Left-side login panel
     --------------------------------------------------------------- */
  var loginOverlay = document.querySelector('[data-login-overlay]');
  var loginPanel = document.querySelector('[data-login-panel]');
  var loginTriggers = document.querySelectorAll('[data-login-open]');
  var loginClose = document.querySelector('[data-login-close]');

  function openLoginPanel(role) {
    if (!loginPanel || !loginOverlay) return;
    updateHeaderHeightVar();
    loginOverlay.classList.add('is-open');
    loginPanel.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    if (role) setActiveRole(role);
    var firstInput = loginPanel.querySelector('.login-form.is-active input');
    if (firstInput) setTimeout(function () { firstInput.focus(); }, 320);
  }
  function closeLoginPanel() {
    if (!loginPanel || !loginOverlay) return;
    loginOverlay.classList.remove('is-open');
    loginPanel.classList.remove('is-open');
    document.body.style.overflow = '';
  }
  loginTriggers.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      openLoginPanel(btn.getAttribute('data-login-open') || null);
    });
  });
  if (loginClose) loginClose.addEventListener('click', closeLoginPanel);
  if (loginOverlay) loginOverlay.addEventListener('click', closeLoginPanel);
  if (loginPanel) loginPanel.addEventListener('click', function (e) { e.stopPropagation(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && loginPanel && loginPanel.classList.contains('is-open')) closeLoginPanel();
  });

  /* Role tab switching */
  var roleTabs = document.querySelectorAll('.role-tab');
  function setActiveRole(role) {
    roleTabs.forEach(function (t) { t.classList.toggle('is-active', t.getAttribute('data-role') === role); });
    document.querySelectorAll('.login-form').forEach(function (f) {
      f.classList.toggle('is-active', f.getAttribute('data-role-form') === role);
    });
  }
  roleTabs.forEach(function (tab) {
    tab.addEventListener('click', function () { setActiveRole(tab.getAttribute('data-role')); });
  });

  (function () {
    var params = new URLSearchParams(window.location.search);
    var loginParam = params.get('login');
    if (loginParam === 'required') {
      openLoginPanel(null);
    } else if (loginParam && ['customer', 'partner', 'employee', 'hr'].indexOf(loginParam) !== -1) {
      openLoginPanel(loginParam);
    }
  })();

  /* AJAX login submit (the 4 real portal forms; the OTP form is handled separately below) */
  function postJson(url, payload) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
      credentials: 'same-origin'
    }).then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); });
  }

  function showFormError(form, message) {
    var errorBox = form.querySelector('.form-error');
    if (errorBox) { errorBox.textContent = message; errorBox.classList.add('is-visible'); }
  }

  document.querySelectorAll('.login-form[data-role-form]:not([data-role-form="otp"])').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var errorBox = form.querySelector('.form-error');
      var submitBtn = form.querySelector('button[type="submit"]');
      if (errorBox) { errorBox.classList.remove('is-visible'); errorBox.textContent = ''; }
      if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.label = submitBtn.textContent; submitBtn.textContent = 'Signing in…'; }

      postJson('/api/auth/login', Object.fromEntries(new FormData(form).entries()))
        .then(function (result) {
          if (result.ok && result.data.ok && result.data.otp_required) {
            var otpForm = document.querySelector('.login-form[data-role-form="otp"]');
            var dest = otpForm.querySelector('[data-otp-destination]');
            if (dest) dest.textContent = result.data.destination_masked || 'your registered email';
            otpForm.dataset.returnRole = form.getAttribute('data-role-form');
            setActiveRole('otp');
            var otpInput = otpForm.querySelector('#otp-code');
            if (otpInput) { otpInput.value = ''; otpInput.focus(); }
          } else if (result.ok && result.data.ok) {
            window.location.href = result.data.redirect || '/';
          } else {
            showFormError(form, (result.data && result.data.error) || 'Unable to sign in. Please try again.');
          }
        })
        .catch(function () { showFormError(form, 'Network error. Please try again.'); })
        .finally(function () {
          if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.label; }
        });
    });
  });

  /* OTP step: verify, resend, back-to-login */
  var otpForm = document.querySelector('.login-form[data-role-form="otp"]');
  if (otpForm) {
    otpForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var submitBtn = otpForm.querySelector('button[type="submit"]');
      otpForm.querySelector('.form-error').classList.remove('is-visible');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.label = submitBtn.textContent; submitBtn.textContent = 'Verifying…'; }

      postJson('/api/auth/verify-otp', Object.fromEntries(new FormData(otpForm).entries()))
        .then(function (result) {
          if (result.ok && result.data.ok) {
            window.location.href = result.data.redirect || '/';
          } else {
            showFormError(otpForm, (result.data && result.data.error) || 'Unable to verify that code.');
          }
        })
        .catch(function () { showFormError(otpForm, 'Network error. Please try again.'); })
        .finally(function () {
          if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.label; }
        });
    });

    var resendBtn = otpForm.querySelector('[data-otp-resend]');
    if (resendBtn) {
      resendBtn.addEventListener('click', function () {
        resendBtn.disabled = true;
        var original = resendBtn.textContent;
        resendBtn.textContent = 'Sending…';
        postJson('/api/auth/resend-otp', { csrf_token: otpForm.querySelector('[name="csrf_token"]').value })
          .then(function (result) {
            if (result.ok && result.data.ok) {
              resendBtn.textContent = 'Code sent';
            } else {
              showFormError(otpForm, (result.data && result.data.error) || 'Could not resend code.');
              resendBtn.textContent = original;
            }
          })
          .catch(function () { resendBtn.textContent = original; })
          .finally(function () { setTimeout(function () { resendBtn.disabled = false; resendBtn.textContent = original; }, 3000); });
      });
    }

    var backBtn = otpForm.querySelector('[data-otp-back]');
    if (backBtn) {
      backBtn.addEventListener('click', function () {
        setActiveRole(otpForm.dataset.returnRole || 'customer');
      });
    }
  }

  /* ---------------------------------------------------------------
     Leadership avatar photo fallback (CSP forbids inline onerror)
     --------------------------------------------------------------- */
  document.querySelectorAll('.js-avatar-photo').forEach(function (img) {
    img.addEventListener('error', function () {
      img.style.display = 'none';
      var wrap = img.closest('.leader-avatar');
      if (wrap) { wrap.classList.add('img-error'); }
    });
  });

  /* ---------------------------------------------------------------
     Footer groups — accordions at 767px and below, always open above.
     Without JS every group simply stays expanded.
     --------------------------------------------------------------- */
  var footer = document.querySelector('.ft');
  if (footer) {
    var ftQuery = window.matchMedia('(max-width: 767px)');
    var ftGroups = footer.querySelectorAll('[data-ft-group]');
    var setGroup = function (group, open) {
      group.classList.toggle('is-open', open);
      var btn = group.querySelector('[data-ft-toggle]');
      if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    var syncFooter = function () {
      footer.classList.toggle('ft-js', ftQuery.matches);
      ftGroups.forEach(function (g) { setGroup(g, !ftQuery.matches); });
    };
    ftGroups.forEach(function (g) {
      var btn = g.querySelector('[data-ft-toggle]');
      if (btn) btn.addEventListener('click', function () { setGroup(g, !g.classList.contains('is-open')); });
    });
    syncFooter();
    if (ftQuery.addEventListener) ftQuery.addEventListener('change', syncFooter);
    else if (ftQuery.addListener) ftQuery.addListener(syncFooter);
  }

  /* ---------------------------------------------------------------
     Scroll reveal
     --------------------------------------------------------------- */
  var revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && !prefersReducedMotion) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(function (el) { observer.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  /* ---------------------------------------------------------------
     Animated counters
     --------------------------------------------------------------- */
  var counters = document.querySelectorAll('[data-counter]');
  function animateCounter(el) {
    var target = parseFloat(el.getAttribute('data-counter'));
    var suffix = el.getAttribute('data-suffix') || '';
    var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
    if (prefersReducedMotion) { el.textContent = target.toFixed(decimals) + suffix; return; }
    var duration = 1200;
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var progress = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = (target * eased).toFixed(decimals) + suffix;
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  if ('IntersectionObserver' in window) {
    var counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { animateCounter(entry.target); counterObserver.unobserve(entry.target); }
      });
    }, { threshold: 0.4 });
    counters.forEach(function (el) { counterObserver.observe(el); });
  } else {
    counters.forEach(animateCounter);
  }

  /* ---------------------------------------------------------------
     Developer code tabs + copy-to-clipboard
     --------------------------------------------------------------- */
  document.querySelectorAll('.code-panel').forEach(function (panel) {
    var tabs = panel.querySelectorAll('.code-tab');
    var blocks = panel.querySelectorAll('[data-code-block]');
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('is-active'); });
        tab.classList.add('is-active');
        var lang = tab.getAttribute('data-lang');
        blocks.forEach(function (b) { b.style.display = (b.getAttribute('data-code-block') === lang) ? 'block' : 'none'; });
      });
    });
  });
  document.querySelectorAll('.copy-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var block = btn.closest('.code-body').querySelector('[data-code-block]:not([style*="display: none"])') || btn.closest('.code-body').querySelector('[data-code-block]');
      if (!block) return;
      navigator.clipboard.writeText(block.textContent.trim()).then(function () {
        var original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(function () { btn.textContent = original; }, 1600);
      });
    });
  });

  /* ---------------------------------------------------------------
     Legal pages: sticky TOC scrollspy + cookie preference toggles
     --------------------------------------------------------------- */
  var legalTocLinks = document.querySelectorAll('.legal-toc a');
  var legalSections = document.querySelectorAll('.legal-section');
  if (legalTocLinks.length && legalSections.length && 'IntersectionObserver' in window) {
    var legalObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          legalTocLinks.forEach(function (l) { l.classList.remove('active'); });
          var active = document.querySelector('.legal-toc a[href="#' + entry.target.id + '"]');
          if (active) active.classList.add('active');
        }
      });
    }, { rootMargin: '-15% 0px -70% 0px', threshold: 0 });
    legalSections.forEach(function (s) { legalObserver.observe(s); });
  }
  document.querySelectorAll('.cookie-toggle:not(.locked)').forEach(function (toggle) {
    toggle.addEventListener('click', function () { toggle.classList.toggle('on'); });
  });

  /* ---------------------------------------------------------------
     Footer newsletter subscribe
     --------------------------------------------------------------- */
  var newsletterForm = document.getElementById('newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg = newsletterForm.querySelector('.footer-newsletter-msg');
      var button = newsletterForm.querySelector('button[type="submit"]');
      var payload = Object.fromEntries(new FormData(newsletterForm).entries());
      msg.textContent = '';
      msg.className = 'footer-newsletter-msg';
      button.disabled = true;
      fetch('/api/newsletter/subscribe', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload), credentials: 'same-origin'
      }).then(function (r) { return r.json(); }).then(function (data) {
        button.disabled = false;
        if (data.ok) {
          msg.textContent = "You're subscribed — thanks for joining.";
          msg.className = 'footer-newsletter-msg is-success';
          newsletterForm.reset();
        } else {
          msg.textContent = data.error || 'Something went wrong. Please try again.';
          msg.className = 'footer-newsletter-msg is-error';
        }
      }).catch(function () {
        button.disabled = false;
        msg.textContent = 'Network error. Please try again.';
        msg.className = 'footer-newsletter-msg is-error';
      });
    });
  }

  /* ---------------------------------------------------------------
     Legacy Developer anchors. /developers#sandbox etc. used to be
     sections of one page; each is now its own page. A #fragment never
     reaches the server, so it cannot be 301-redirected — forward old
     bookmarks and external links here instead.
     --------------------------------------------------------------- */
  function forwardLegacyDevHash() {
    if (window.location.pathname !== '/developers' || !window.location.hash) return;
    var legacyDev = {
      '#api-reference': '/developers/api-reference', '#authentication': '/developers/authentication',
      '#webhooks': '/developers/webhooks', '#sdks': '/developers/sdks',
      '#integration-guide': '/developers/integration-guide', '#sandbox': '/sandbox',
      '#docs': '/developers#resources', '#agentic-ai': '/developers#agent-ready'
    };
    var target = legacyDev[window.location.hash];
    if (target) window.location.replace(target);
  }
  forwardLegacyDevHash();
  window.addEventListener('hashchange', forwardLegacyDevHash);

  /* Legacy Solutions anchors: /solutions#travel → /solutions/travel for the
     industries that now have their own page. */
  function forwardLegacySolutionHash() {
    if (window.location.pathname !== '/solutions' || !window.location.hash) return;
    var pages = { '#ecommerce': 'e-commerce', '#travel': 'travel', '#healthcare': 'healthcare', '#education': 'education',
      '#retail': 'retail', '#hospitality': 'hospitality', '#professional-services': 'professional-services', '#enterprise': 'enterprise' };
    var slug = pages[window.location.hash];
    if (slug) window.location.replace('/solutions/' + slug);
  }
  forwardLegacySolutionHash();
  window.addEventListener('hashchange', forwardLegacySolutionHash);

  /* ---------------------------------------------------------------
     Page analytics hooks. The site has no analytics stack yet, so
     events go to window.dataLayer / gtag only if present, and are
     always dispatched as a `paynancial:track` DOM event (the same
     channel the floating enquiry widget uses).
       [data-track-view="sandbox_view"] → fired once on page load
       [data-track="request_sandbox_access"] → fired on click
     --------------------------------------------------------------- */
  function pnTrack(event, extra) {
    var detail = { event: event, page_url: window.location.pathname };
    if (extra) { for (var k in extra) { if (Object.prototype.hasOwnProperty.call(extra, k)) detail[k] = extra[k]; } }
    try {
      if (Array.isArray(window.dataLayer)) window.dataLayer.push(detail);
      if (typeof window.gtag === 'function') window.gtag('event', event, detail);
      document.dispatchEvent(new CustomEvent('paynancial:track', { detail: detail }));
    } catch (e) { /* analytics must never break the page */ }
  }
  document.querySelectorAll('[data-track-view]').forEach(function (el) {
    pnTrack(el.getAttribute('data-track-view'));
  });
  document.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('[data-track]');
    if (el) pnTrack(el.getAttribute('data-track'), { link_url: el.getAttribute('href') || '', link_text: (el.textContent || '').trim().slice(0, 80) });
  });

  /* ---------------------------------------------------------------
     Dashboard sidebar toggle (mobile)
     --------------------------------------------------------------- */
  var sidebarToggle = document.querySelector('.sidebar-toggle');
  var appSidebar = document.querySelector('.app-sidebar');
  if (sidebarToggle && appSidebar) {
    sidebarToggle.addEventListener('click', function () { appSidebar.classList.toggle('is-open'); });
  }
})();
