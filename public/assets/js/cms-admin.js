/* Paynancial CMS editor helpers: character counters, live search-result
   preview and an unsaved-changes warning. Progressive enhancement only —
   every limit and rule is enforced again on the server. */
(function () {
  'use strict';

  // Character counters (soft limit = recommended length; maxlength is the hard cap).
  document.querySelectorAll('.cms-count').forEach(function (out) {
    var field = document.getElementById(out.getAttribute('data-for'));
    if (!field) return;
    var soft = parseInt(field.getAttribute('data-soft-limit'), 10) || 0;
    var update = function () {
      var n = field.value.length;
      out.textContent = n + ' / ' + soft + (n > soft ? ' — longer than recommended' : '');
      out.classList.toggle('is-over', n > soft);
    };
    field.addEventListener('input', update);
    update();
  });

  // Live search-result preview.
  var bind = function (fieldName, key, fallback) {
    var field = document.querySelector('[name="' + fieldName + '"]');
    var target = document.querySelector('[data-serp="' + key + '"]');
    if (!field || !target) return;
    field.addEventListener('input', function () { target.textContent = field.value || fallback(); });
  };
  var titleField = document.querySelector('[name="title"]');
  bind('meta_title', 'title', function () { return titleField && titleField.value ? titleField.value + ' | Paynancial Insights' : 'Template title'; });
  bind('description', 'desc', function () { return 'Meta description'; });
  bind('meta_description', 'desc', function () { return 'Template description'; });
  bind('slug', 'slug', function () { return 'your-article'; });

  // Unsaved-changes warning.
  document.querySelectorAll('form[data-cms-dirty]').forEach(function (form) {
    var dirty = false;
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('change', function () { dirty = true; });
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) {
      if (!dirty) return;
      e.preventDefault();
      e.returnValue = '';
    });
  });
})();
