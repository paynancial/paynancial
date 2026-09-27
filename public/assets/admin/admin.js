/* Paynancial Enterprise Admin — progressive enhancement (Phase 1).
   Everything works without JavaScript except the command palette, popovers
   and chart tooltips; state changes are saved per user via /admin/preferences. */
(function () {
  'use strict';
  var body = document.body;
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
  document.querySelectorAll('[data-kbd-mod]').forEach(function (k) { k.textContent = isMac ? '⌘K' : 'Ctrl K'; });

  function ls(key, value) {
    try {
      if (value === undefined) return JSON.parse(localStorage.getItem('pyn-adm:' + key));
      localStorage.setItem('pyn-adm:' + key, JSON.stringify(value));
    } catch (e) { return null; }
  }
  function savePref(key, value) {
    ls(key, value);
    return fetch('/admin/preferences', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
      body: JSON.stringify({ key: key, value: value })
    }).catch(function () { /* localStorage keeps it for this browser */ });
  }

  // ------------------------------------------------------------ sidebar
  var toggle = document.querySelector('[data-side-toggle]');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var collapsed = body.getAttribute('data-sidebar') !== 'collapsed';
      body.setAttribute('data-sidebar', collapsed ? 'collapsed' : 'expanded');
      toggle.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
      toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
      savePref('sidebar', collapsed ? 'collapsed' : 'expanded');
    });
  }
  var overlay = document.querySelector('.adm-overlay');
  var side = document.getElementById('adm-side');
  var lastFocus = null;
  function openNav() {
    lastFocus = document.activeElement;
    body.classList.add('nav-open');
    if (overlay) overlay.hidden = false;
    var first = side && side.querySelector('input, a');
    if (first) first.focus();
  }
  function closeNav() {
    if (!body.classList.contains('nav-open')) return;
    body.classList.remove('nav-open');
    if (overlay) overlay.hidden = true;
    if (lastFocus) lastFocus.focus();
  }
  document.querySelectorAll('[data-side-open]').forEach(function (b) { b.addEventListener('click', openNav); });
  document.querySelectorAll('[data-side-close]').forEach(function (b) { b.addEventListener('click', closeNav); });

  var groupState = {};
  document.querySelectorAll('.adm-grp-h').forEach(function (h) {
    h.addEventListener('click', function () {
      var open = h.getAttribute('aria-expanded') !== 'true';
      h.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.getElementById(h.getAttribute('aria-controls')).hidden = !open;
      groupState[h.parentNode.getAttribute('data-group')] = open;
      document.querySelectorAll('.adm-grp').forEach(function (g) {
        var hh = g.querySelector('.adm-grp-h');
        groupState[g.getAttribute('data-group')] = hh.getAttribute('aria-expanded') === 'true';
      });
      savePref('nav_groups', groupState);
    });
  });

  var filter = document.querySelector('[data-nav-filter]');
  if (filter) {
    filter.addEventListener('input', function () {
      var q = filter.value.trim().toLowerCase();
      var any = false;
      document.querySelectorAll('.adm-grp').forEach(function (g) {
        var shown = 0;
        g.querySelectorAll('[data-nav-item]').forEach(function (a) {
          var hit = !q || a.getAttribute('data-k').indexOf(q) !== -1;
          a.parentNode.hidden = !hit;
          if (hit) shown++;
        });
        g.hidden = shown === 0;
        if (q && shown) g.querySelector('ul').hidden = false;
        any = any || shown > 0;
      });
      var empty = document.querySelector('[data-nav-empty]');
      if (empty) empty.hidden = any;
    });
    filter.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { filter.value = ''; filter.dispatchEvent(new Event('input')); }
      if (e.key === 'ArrowDown' || e.key === 'Enter') {
        var first = Array.prototype.find.call(document.querySelectorAll('[data-nav-item]'), function (a) { return !a.parentNode.hidden && !a.closest('.adm-grp').hidden; });
        if (first) { e.preventDefault(); if (e.key === 'Enter') first.click(); else first.focus(); }
      }
    });
  }
  // Arrow keys move between visible sidebar links.
  document.querySelectorAll('.adm-nav').forEach(function (nav) {
    nav.addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
      var links = Array.prototype.filter.call(nav.querySelectorAll('[data-nav-item]'), function (a) { return a.offsetParent !== null; });
      var i = links.indexOf(document.activeElement);
      if (i === -1) return;
      e.preventDefault();
      var next = links[(i + (e.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length];
      next.focus();
    });
  });

  // ------------------------------------------------------------ popovers
  var openPop = null;
  function closePop(focusTrigger) {
    if (!openPop) return;
    openPop.pop.hidden = true;
    openPop.btn.setAttribute('aria-expanded', 'false');
    if (focusTrigger) openPop.btn.focus();
    openPop = null;
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-pop]');
    if (btn) {
      var pop = document.getElementById(btn.getAttribute('data-pop'));
      var same = openPop && openPop.pop === pop;
      closePop(false);
      if (!same && pop) {
        pop.hidden = false;
        btn.setAttribute('aria-expanded', 'true');
        openPop = { btn: btn, pop: pop };
        var f = pop.querySelector('a, button, input, select');
        if (f && e.detail === 0) f.focus();
      }
      return;
    }
    if (openPop && !openPop.pop.contains(e.target)) closePop(false);
  });

  // ------------------------------------------------------------ keyboard
  document.addEventListener('keydown', function (e) {
    var typing = /INPUT|TEXTAREA|SELECT/.test((document.activeElement || {}).tagName || '') || (document.activeElement || {}).isContentEditable;
    if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) { e.preventDefault(); openCmdk(); return; }
    if (e.key === '/' && !typing && filter && window.innerWidth >= 1024 && body.getAttribute('data-sidebar') !== 'collapsed') { e.preventDefault(); filter.focus(); return; }
    if (e.key === 'Escape') { closePop(true); closeNav(); }
  });

  // ------------------------------------------------------------ command palette
  var dlg = document.getElementById('adm-cmdk');
  var input = document.getElementById('adm-cmdk-q');
  var list = document.getElementById('adm-cmdk-list');
  var items = [];
  try { items = JSON.parse(document.getElementById('adm-palette-data').textContent || '[]'); } catch (err) { items = []; }
  var sel = 0;
  var shown = [];
  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function render() {
    var q = input.value.trim().toLowerCase();
    shown = items.filter(function (it) {
      return !q || (it.label + ' ' + it.hint + ' ' + (it.k || '')).toLowerCase().indexOf(q) !== -1;
    }).slice(0, 12);
    if (q) shown.push({ type: 'Search', label: 'Search records for “' + input.value.trim() + '”', hint: 'Enquiries, customers, articles, users', url: '/admin/search?q=' + encodeURIComponent(input.value.trim()) });
    sel = Math.min(sel, Math.max(0, shown.length - 1));
    list.innerHTML = shown.length ? shown.map(function (it, i) {
      return '<li role="option" id="cmdk-' + i + '" aria-selected="' + (i === sel) + '" data-i="' + i + '"><span class="adm-cmdk-type">' + esc(it.type) + '</span><span class="adm-cmdk-l">' + esc(it.label) + '</span><small>' + esc(it.hint || '') + '</small></li>';
    }).join('') : '<li class="adm-cmdk-empty" role="option" aria-disabled="true">Type to search.</li>';
    input.setAttribute('aria-activedescendant', shown.length ? 'cmdk-' + sel : '');
  }
  function go(i) { if (shown[i]) window.location.href = shown[i].url; }
  function openCmdk() {
    if (!dlg) return;
    closePop(false);
    if (typeof dlg.showModal === 'function') { if (!dlg.open) dlg.showModal(); } else { dlg.setAttribute('open', ''); }
    input.value = ''; sel = 0; render(); input.focus();
  }
  if (dlg) {
    document.querySelectorAll('[data-cmdk-open]').forEach(function (b) { b.addEventListener('click', openCmdk); });
    input.addEventListener('input', function () { sel = 0; render(); });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') { e.preventDefault(); sel = Math.min(sel + 1, shown.length - 1); render(); scrollSel(); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); sel = Math.max(sel - 1, 0); render(); scrollSel(); }
      else if (e.key === 'Enter') { e.preventDefault(); go(sel); }
    });
    list.addEventListener('click', function (e) { var li = e.target.closest('li[data-i]'); if (li) go(+li.getAttribute('data-i')); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
  }
  function scrollSel() { var el = document.getElementById('cmdk-' + sel); if (el) el.scrollIntoView({ block: 'nearest' }); }

  // ------------------------------------------------------------ hero collapse
  document.querySelectorAll('[data-hero-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var hero = btn.closest('.adm-hero');
      var collapsed = !hero.classList.contains('is-collapsed');
      hero.classList.toggle('is-collapsed', collapsed);
      btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      btn.querySelector('[data-hero-label]').textContent = collapsed ? 'Expand' : 'Collapse';
      savePref('hero_collapsed', collapsed);
    });
  });

  // ------------------------------------------------------------ tabs
  document.querySelectorAll('[role="tablist"]').forEach(function (tl) {
    var tabs = Array.prototype.slice.call(tl.querySelectorAll('[role="tab"]'));
    function activate(t, focus) {
      tabs.forEach(function (x) {
        var on = x === t;
        x.setAttribute('aria-selected', on ? 'true' : 'false');
        x.tabIndex = on ? 0 : -1;
        var p = document.getElementById(x.getAttribute('aria-controls'));
        if (p) p.hidden = !on;
      });
      if (focus) t.focus();
      if (tl.hasAttribute('data-remember')) ls('tab:' + tl.getAttribute('data-remember'), t.id);
    }
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { activate(t, false); });
      t.addEventListener('keydown', function (e) {
        var d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
        if (e.key === 'Home') { e.preventDefault(); activate(tabs[0], true); }
        else if (e.key === 'End') { e.preventDefault(); activate(tabs[tabs.length - 1], true); }
        else if (d) { e.preventDefault(); activate(tabs[(i + d + tabs.length) % tabs.length], true); }
      });
    });
    if (tl.hasAttribute('data-remember')) {
      var saved = ls('tab:' + tl.getAttribute('data-remember'));
      var t = saved && document.getElementById(saved);
      if (t && tabs.indexOf(t) !== -1) activate(t, false);
    }
  });

  // ------------------------------------------------------------ charts (tooltips)
  document.querySelectorAll('.adm-chart').forEach(function (chart) {
    var tip = document.createElement('div');
    tip.className = 'adm-tip'; tip.hidden = true; tip.setAttribute('role', 'status');
    chart.appendChild(tip);
    function show(el) {
      var r = chart.getBoundingClientRect();
      var p = el.getBoundingClientRect();
      tip.innerHTML = '<span>' + esc(el.getAttribute('data-d')) + '</span><b>' + esc(el.getAttribute('data-v')) + '</b>';
      tip.style.left = (p.left - r.left + p.width / 2) + 'px';
      tip.style.top = (p.top - r.top) + 'px';
      tip.hidden = false;
      var pt = el.nextElementSibling;
      chart.querySelectorAll('.adm-pt.is-on').forEach(function (x) { x.classList.remove('is-on'); });
      if (pt && pt.classList.contains('adm-pt')) pt.classList.add('is-on');
    }
    function hide() { tip.hidden = true; chart.querySelectorAll('.adm-pt.is-on').forEach(function (x) { x.classList.remove('is-on'); }); }
    chart.querySelectorAll('[data-d]').forEach(function (el) {
      el.addEventListener('mouseenter', function () { show(el); });
      el.addEventListener('focus', function () { show(el); });
      el.addEventListener('mouseleave', hide);
      el.addEventListener('blur', hide);
    });
  });

  // ------------------------------------------------------------ widgets (async + retry)
  function loadWidget(box, key) {
    box.setAttribute('aria-busy', 'true');
    box.innerHTML = '<div class="adm-skel" aria-hidden="true"><span style="width:90%"></span><span style="width:70%"></span><span style="width:80%"></span></div><span class="sr-only">Loading…</span>';
    return fetch('/admin/widget/' + encodeURIComponent(key), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (d) { box.innerHTML = d.html; })
      .catch(function () {
        box.innerHTML = '<div class="adm-state adm-state--error" role="alert"><div><strong>Unable to load this panel.</strong><p>Check your connection and try again.</p>'
          + '<button type="button" class="adm-btn adm-btn--sm" data-widget-retry="' + esc(key) + '">Retry</button></div></div>';
      })
      .finally(function () { box.removeAttribute('aria-busy'); });
  }
  document.querySelectorAll('[data-widget-async]').forEach(function (box) { loadWidget(box, box.getAttribute('data-widget-async')); });
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-widget-retry]');
    if (!b) return;
    var box = b.closest('[data-widget-box]');
    if (box) loadWidget(box, b.getAttribute('data-widget-retry'));
    else window.location.reload();
  });

  // ------------------------------------------------------------ tables
  document.querySelectorAll('[data-select-all]').forEach(function (all) {
    var table = document.getElementById(all.getAttribute('data-select-all'));
    if (!table) return;
    var bar = document.querySelector('[data-bulk-for="' + table.id + '"]');
    function boxes() { return table.querySelectorAll('input[data-row-check]'); }
    function sync() {
      var n = Array.prototype.filter.call(boxes(), function (b) { return b.checked; }).length;
      if (bar) { bar.classList.toggle('is-on', n > 0); var c = bar.querySelector('[data-bulk-count]'); if (c) c.textContent = n + ' selected'; }
      all.checked = n > 0 && n === boxes().length;
      all.indeterminate = n > 0 && n < boxes().length;
    }
    all.addEventListener('change', function () { boxes().forEach(function (b) { b.checked = all.checked; }); sync(); });
    table.addEventListener('change', function (e) { if (e.target.matches('input[data-row-check]')) sync(); });
    sync();
  });
  document.querySelectorAll('[data-col-toggle]').forEach(function (cb) {
    var tid = cb.getAttribute('data-col-toggle');
    var table = document.getElementById(tid);
    if (!table) { cb.closest('.adm-pop-wrap') && (cb.closest('.adm-pop-wrap').hidden = true); return; }
    var hidden = ls('cols:' + tid) || [];
    function apply() {
      table.querySelectorAll('[data-col="' + cb.value + '"]').forEach(function (c) { c.classList.toggle('is-hidden', !cb.checked); });
    }
    if (hidden.indexOf(cb.value) !== -1) cb.checked = false;
    apply();
    cb.addEventListener('change', function () {
      apply();
      var h = Array.prototype.filter.call(document.querySelectorAll('[data-col-toggle="' + tid + '"]'), function (x) { return !x.checked; }).map(function (x) { return x.value; });
      ls('cols:' + tid, h);
    });
  });
  document.querySelectorAll('select[data-nav-select]').forEach(function (s) {
    s.addEventListener('change', function () { window.location.href = s.value; });
  });

  // ------------------------------------------------------------ quick actions: customize
  var cz = document.getElementById('adm-customize');
  if (cz) {
    document.querySelectorAll('[data-customize-open]').forEach(function (b) { b.addEventListener('click', function () { cz.showModal(); }); });
    var czForm = cz.querySelector('form');
    var boxes = czForm.querySelectorAll('input[type=checkbox]');
    function limit() {
      var n = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).length;
      boxes.forEach(function (b) { b.disabled = !b.checked && n >= 5; });
      var out = cz.querySelector('[data-cz-count]');
      if (out) out.textContent = n + ' of 5 selected';
    }
    boxes.forEach(function (b) { b.addEventListener('change', limit); });
    limit();
    cz.querySelector('[data-cz-cancel]').addEventListener('click', function () { cz.close(); });
    czForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var v = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).map(function (b) { return b.value; });
      savePref('quick_actions', v).then(function () { window.location.reload(); });
    });
  }
  var more = document.querySelector('[data-qa-more]');
  if (more) more.addEventListener('click', function () { document.querySelector('.adm-qa').classList.add('is-all'); more.hidden = true; });

  // ------------------------------------------------------------ scroll regions are keyboard reachable
  function markScrollers() {
    document.querySelectorAll('.adm-table-wrap, .data-table-wrap, .adm-pipe').forEach(function (el) {
      if (el.scrollWidth > el.clientWidth + 1) {
        el.tabIndex = 0;
        if (el.tagName !== 'OL' && !el.hasAttribute('role')) { el.setAttribute('role', 'region'); }
        if (!el.hasAttribute('aria-label') && el.tagName !== 'OL') { el.setAttribute('aria-label', 'Scrollable table'); }
      }
    });
  }
  markScrollers();
  window.addEventListener('resize', markScrollers);

  // ------------------------------------------------------------ existing admin behaviours
  document.querySelectorAll('[data-logout]').forEach(function (b) {
    b.addEventListener('click', function () {
      fetch('/api/auth/logout', { method: 'POST', credentials: 'same-origin' }).finally(function () { window.location.href = '/'; });
    });
  });
  document.querySelectorAll('.js-auto-submit').forEach(function (el) {
    el.addEventListener('change', function () { el.form.submit(); });
  });
  document.addEventListener('click', function (e) {
    var c = e.target.closest('.js-confirm');
    if (c && !window.confirm(c.getAttribute('data-confirm') || 'Are you sure?')) { e.preventDefault(); return; }
    if (e.target.closest('.js-print')) window.print();
  });
  document.querySelectorAll('form[data-confirm-submit]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!window.confirm(f.getAttribute('data-confirm-submit'))) e.preventDefault(); });
  });
})();
