    </div>
  </div>
</div>

<?php if ($dashboard_area === 'partner'): ?>
<div class="assistant-widget" data-assistant>
  <button type="button" class="assistant-toggle" data-assistant-toggle aria-label="Open Partner Assistant">
    <span>AI</span>
  </button>
  <div class="assistant-panel" data-assistant-panel hidden>
    <div class="assistant-head">
      <div>
        <strong>Partner Assistant</strong>
        <p>Rules-based · answers only from your own account data</p>
      </div>
      <button type="button" data-assistant-close aria-label="Close">&times;</button>
    </div>
    <div class="assistant-body" data-assistant-body>
      <div class="assistant-msg assistant-msg-bot">Hi — ask me about your customers, commissions, settlements, transactions, proposals, support tickets, or onboarding status.</div>
    </div>
    <form class="assistant-form" data-assistant-form>
      <input type="text" name="query" placeholder="e.g. How much commission have I earned?" autocomplete="off" maxlength="300">
      <button type="submit">Ask</button>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="<?= asset('js/main.js') ?>" defer></script>
<script nonce="<?= csp_nonce() ?>">
document.getElementById('logout-link').addEventListener('click', function (e) {
  e.preventDefault();
  fetch('/api/auth/logout', { method: 'POST', credentials: 'same-origin' })
    .finally(function () { window.location.href = '/'; });
});
document.querySelectorAll('.js-auto-submit').forEach(function (el) {
  el.addEventListener('change', function () { el.form.submit(); });
});
document.addEventListener('click', function (e) {
  var confirmEl = e.target.closest('.js-confirm');
  if (confirmEl && !window.confirm(confirmEl.getAttribute('data-confirm') || 'Are you sure?')) {
    e.preventDefault();
    return;
  }
  if (e.target.closest('.js-print')) {
    window.print();
  }
});

(function () {
  var widget = document.querySelector('[data-assistant]');
  if (!widget) return;
  var toggle = widget.querySelector('[data-assistant-toggle]');
  var panel = widget.querySelector('[data-assistant-panel]');
  var closeBtn = widget.querySelector('[data-assistant-close]');
  var body = widget.querySelector('[data-assistant-body]');
  var form = widget.querySelector('[data-assistant-form]');

  toggle.addEventListener('click', function () { panel.hidden = !panel.hidden; });
  closeBtn.addEventListener('click', function () { panel.hidden = true; });

  function addMessage(text, who) {
    var el = document.createElement('div');
    el.className = 'assistant-msg assistant-msg-' + who;
    el.textContent = text;
    body.appendChild(el);
    body.scrollTop = body.scrollHeight;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var input = form.querySelector('input[name="query"]');
    var query = input.value.trim();
    if (!query) return;
    addMessage(query, 'user');
    input.value = '';

    fetch('/api/partner/assistant', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
      body: JSON.stringify({ csrf_token: '<?= e(csrf_token()) ?>', query: query })
    }).then(function (r) { return r.json(); }).then(function (data) {
      addMessage(data.ok ? data.answer : (data.error || 'Something went wrong.'), 'bot');
    }).catch(function () {
      addMessage('Something went wrong reaching the assistant. Please try again.', 'bot');
    });
  });
})();
</script>
</body>
</html>
