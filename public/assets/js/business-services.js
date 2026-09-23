/**
 * Paynancial Business Services — standalone page behaviour.
 * Loaded only on /business-services pages. Everything here is progressive
 * enhancement: without JS the search form submits to the jurisdiction
 * directory and the directory filters server-side from the query string.
 */
(function () {
  'use strict';

  function tokens(text) {
    return text.toLowerCase().trim().split(/\s+/).filter(Boolean);
  }
  function matches(haystack, query) {
    var t = tokens(query);
    return t.length > 0 && t.every(function (w) { return haystack.indexOf(w) !== -1; });
  }
  function escapeHtml(s) {
    return s.replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function highlight(name, query) {
    var safe = escapeHtml(name);
    var q = query.trim();
    if (!q) return safe;
    var i = name.toLowerCase().indexOf(q.toLowerCase());
    if (i === -1) return safe;
    return escapeHtml(name.slice(0, i)) + '<mark>' + escapeHtml(name.slice(i, i + q.length)) + '</mark>' + escapeHtml(name.slice(i + q.length));
  }

  /* ---------------------------------------------------------------
     JurisdictionSearch — accessible combobox with live suggestions.
     --------------------------------------------------------------- */
  document.querySelectorAll('[data-jur-search]').forEach(function (form) {
    var input = form.querySelector('input[type="search"]');
    var list = form.querySelector('.bs-search-list');
    var options;
    try { options = JSON.parse(form.getAttribute('data-options') || '[]'); } catch (e) { options = []; }
    if (!input || !list) return;

    var results = [];
    var active = -1;

    function close() {
      list.hidden = true;
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
      active = -1;
    }
    function setActive(i) {
      var items = list.querySelectorAll('[role="option"]');
      items.forEach(function (li, n) { li.setAttribute('aria-selected', n === i ? 'true' : 'false'); });
      active = i;
      if (i >= 0 && items[i]) {
        input.setAttribute('aria-activedescendant', items[i].id);
        items[i].scrollIntoView({ block: 'nearest' });
      } else {
        input.removeAttribute('aria-activedescendant');
      }
    }
    function render() {
      var q = input.value;
      results = q.trim() ? options.filter(function (o) { return matches(o.s, q); }).slice(0, 8) : [];
      list.innerHTML = '';
      if (!q.trim()) { close(); return; }
      if (!results.length) {
        list.innerHTML = '<li class="is-empty">No listed jurisdiction matches — press Search to see all options.</li>';
      } else {
        results.forEach(function (o, i) {
          var li = document.createElement('li');
          li.id = input.id + '-opt-' + i;
          li.setAttribute('role', 'option');
          li.setAttribute('aria-selected', 'false');
          li.innerHTML = '<img src="' + escapeHtml(o.f) + '" alt="">' + '<span>' + highlight(o.n, q) + '</span>';
          li.addEventListener('mousedown', function (e) { e.preventDefault(); window.location.href = o.u; });
          list.appendChild(li);
        });
      }
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      active = -1;
    }

    input.addEventListener('input', render);
    input.addEventListener('focus', function () { if (input.value.trim()) render(); });
    input.addEventListener('blur', function () { setTimeout(close, 120); });
    input.addEventListener('keydown', function (e) {
      if (list.hidden || !results.length) {
        if (e.key === 'Escape') close();
        return;
      }
      if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(active + 1, results.length - 1)); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(Math.max(active - 1, -1)); }
      else if (e.key === 'Escape') { close(); }
    });
    form.addEventListener('submit', function (e) {
      // A highlighted suggestion, or a single unambiguous match, goes
      // straight to that jurisdiction; anything else shows filtered results.
      var pick = active >= 0 ? results[active] : (results.length === 1 ? results[0] : null);
      if (pick) {
        e.preventDefault();
        window.location.href = pick.u;
      }
    });
  });

  /* ---------------------------------------------------------------
     Jurisdiction directory — live filtering by text, region,
     objective and structure. Mirrors the server-side filter.
     --------------------------------------------------------------- */
  var filterForm = document.querySelector('[data-jur-filter]');
  if (filterForm) {
    var cards = document.querySelectorAll('[data-jur-grid] [data-jur-card]');
    var empty = document.querySelector('[data-jur-empty]');
    var status = document.querySelector('[data-jur-status]');
    var resetBtn = filterForm.querySelector('[data-jur-reset]');

    function field(name) {
      var el = filterForm.elements[name];
      return el ? el.value : '';
    }
    function apply() {
      var q = field('q');
      var region = field('region');
      var objective = field('objective');
      var structure = field('structure');
      var shown = 0;
      cards.forEach(function (card) {
        var ok = (!q.trim() || matches(card.getAttribute('data-search'), q))
          && (!region || (' ' + card.getAttribute('data-regions') + ' ').indexOf(' ' + region + ' ') !== -1)
          && (!objective || (' ' + card.getAttribute('data-groups') + ' ').indexOf(' ' + objective + ' ') !== -1)
          && (!structure || (' ' + card.getAttribute('data-structures') + ' ').indexOf(' ' + structure + ' ') !== -1);
        card.hidden = !ok;
        if (ok) { shown++; card.classList.add('is-visible'); }
      });
      if (empty) empty.hidden = shown !== 0;
      var filtered = q.trim() || region || objective || structure;
      if (status) {
        status.textContent = filtered
          ? (shown === 1 ? '1 jurisdiction matches your filters.' : shown + ' jurisdictions match your filters.')
          : '';
      }
      if (resetBtn) resetBtn.hidden = !filtered;

      var params = new URLSearchParams();
      if (q.trim()) params.set('q', q.trim());
      if (region) params.set('region', region);
      if (objective) params.set('objective', objective);
      if (structure) params.set('structure', structure);
      var qs = params.toString();
      history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : ''));
    }

    filterForm.addEventListener('input', apply);
    filterForm.addEventListener('change', apply);
    filterForm.addEventListener('submit', function (e) { e.preventDefault(); apply(); });
    if (resetBtn) {
      resetBtn.addEventListener('click', function (e) {
        e.preventDefault();
        filterForm.reset();
        Array.prototype.forEach.call(filterForm.elements, function (el) {
          if (el.name) el.value = '';
        });
        apply();
      });
    }
  }
})();
