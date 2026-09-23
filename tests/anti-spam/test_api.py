#!/usr/bin/env python3
"""
End-to-end tests for POST /api/enquiry/callback (floating widget "Request a
Callback"). Runs against a LOCAL site with a local MySQL/MariaDB and the mock
verifier — never against production. See tests/anti-spam/README.md.

Environment:
  PYN_BASE_URL  site under test            (default http://127.0.0.1:8106)
  PYN_MYSQL     mysql CLI command + db     (e.g. "mysql -uroot --socket=/tmp/db.sock pyn")
  MOCK_STATE    mock verifier state file   (default /tmp/pyn-mock-turnstile-used.json)
Exit code 0 = all passed.
"""
import json, os, re, shlex, shutil, subprocess, sys, urllib.request, urllib.error, http.cookiejar

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
BASE = os.environ.get('PYN_BASE_URL', 'http://127.0.0.1:8106')
MYSQL = shlex.split(os.environ['PYN_MYSQL'])
STATE = os.environ.get('MOCK_STATE', '/tmp/pyn-mock-turnstile-used.json')
STORE = os.path.join(ROOT, 'storage', 'anti-spam')
LOG = os.path.join(ROOT, 'storage', 'logs', 'anti-spam.log')
results = []

def sql(q): return subprocess.run(MYSQL + ['-N', '-e', q], capture_output=True, text=True).stdout.strip()
def reset():
    sql('DELETE FROM anti_spam_hits')
    open(STATE, 'w').write('[]')
    shutil.rmtree(STORE, ignore_errors=True)
def session():
    jar = http.cookiejar.CookieJar()
    op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    html = op.open(BASE + '/').read().decode()
    m = re.search(r'name="csrf_token" value="([^"]+)"', html)
    if not m: sys.exit('Callback form not rendered: are TURNSTILE_SITE_KEY / TURNSTILE_SECRET_KEY set in the server environment?')
    return op, m.group(1)
_seq = [0]
def post(op, tok, **kw):
    _seq[0] += 1
    d = {'csrf_token': tok, 'context': 'payments', 'name': 'Asha Verma', 'email': 'asha@example.com', 'phone': '+91 98765 43210',
         'company': 'Verma Traders', 'requirement': 'payouts', 'message': 'Please call me (%d)' % _seq[0], 'company_website': '',
         'elapsed_ms': 6000, 'cf_turnstile_response': 'pass-auto-%d' % _seq[0]}
    d.update(kw)
    req = urllib.request.Request(BASE + '/api/enquiry/callback', data=json.dumps(d).encode(), headers={'Content-Type': 'application/json'})
    try:
        r = op.open(req); return r.status, json.loads(r.read())
    except urllib.error.HTTPError as e:
        body = e.read()
        try: return e.code, json.loads(body or b'{}')
        except ValueError: return e.code, {'raw': body[:80].decode(errors='ignore')}
def check(name, got, status, pred=lambda b: True):
    st, b = got
    ok = bool(st == status and pred(b))
    results.append((name, ok)); print(('PASS ' if ok else 'FAIL ') + name, st, json.dumps(b)[:110])
def log_has(event, reason=None):
    try: lines = [json.loads(l) for l in open(LOG) if l.strip()][-20:]
    except FileNotFoundError: return False
    return any(l.get('event') == event and (reason is None or l.get('reason') == reason) for l in lines)
def last_payload(field): return sql("SELECT json_unquote(json_extract(payload_json,'$.%s')) FROM contact_submissions ORDER BY id DESC LIMIT 1" % field)
def bucket(kind, value):
    php = 'require "config/config.php"; require "includes/anti-spam.php"; echo as_bucket(%s, %s);' % (json.dumps(kind), json.dumps(value))
    return subprocess.run(['php', '-r', php], cwd=ROOT, capture_output=True, text=True).stdout.strip()
def uniq(i): return {'email': 'u%d@example.com' % i, 'phone': '+9198700%05d' % i}

print('== Existing 22 cases ==')
reset(); op, tok = session()
check('valid enquiry', post(op, tok, email='Asha@Example.com'), 200, lambda b: b['ok'] and b['enquiry_code'])
check('  stored normalised + verified', (200, {}), 200, lambda b: sql("SELECT email FROM enquiries ORDER BY id DESC LIMIT 1") == 'asha@example.com' and last_payload('captcha') == 'verified')
check('missing fields', post(op, tok, name='', email='', phone=''), 422, lambda b: {'name', 'email', 'phone'} <= set(b['fields']))
check('invalid email', post(op, tok, email='not-an-email'), 422, lambda b: 'email' in b['fields'])
check('invalid phone', post(op, tok, phone='12ab'), 422, lambda b: 'phone' in b['fields'])
reset(); op, tok = session()
check('header injection in email', post(op, tok, email='a@b.com\r\nBcc: x@y.com'), 422, lambda b: 'email' in b['fields'])
check('XSS in name rejected', post(op, tok, name='<script>alert(1)</script>'), 422, lambda b: 'name' in b['fields'])
check('failed CAPTCHA', post(op, tok, cf_turnstile_response='fail', **uniq(1)), 403, lambda b: b['error'] == 'Please complete the security check and try again.')
check('missing CAPTCHA token', post(op, tok, cf_turnstile_response='', **uniq(2)), 403, lambda b: b.get('captcha') == 'retry')
reset(); op, tok = session()
post(op, tok, cf_turnstile_response='pass-reuse', **uniq(3))
check('expired / reused token', post(op, tok, cf_turnstile_response='pass-reuse', **uniq(4)), 403, lambda b: b.get('captcha') == 'retry')
reset(); op, tok = session()
check('valid (for duplicate test)', post(op, tok, message='same', **uniq(5)), 200, lambda b: b['ok'])
check('duplicate submission', post(op, tok, message='same', **uniq(5)), 409, lambda b: not b['ok'])
before = sql('SELECT COUNT(*) FROM enquiries')
check('honeypot discarded', post(op, tok, company_website='http://spam', **uniq(6)), 200, lambda b: b['enquiry_code'] is None)
check('too-fast discarded', post(op, tok, elapsed_ms=400, **uniq(7)), 200, lambda b: b['enquiry_code'] is None)
check('  bots stored nothing', (200, {}), 200, lambda b: sql('SELECT COUNT(*) FROM enquiries') == before)
reset(); op, tok = session()
check('provider unavailable → accepted, flagged', post(op, tok, cf_turnstile_response='down-1', **uniq(8)), 200, lambda b: b['ok'])
check('provider unavailable 2nd', post(op, tok, cf_turnstile_response='down-2', **uniq(9)), 200, lambda b: b['ok'])
check('provider unavailable 3rd → tighter limit', post(op, tok, cf_turnstile_response='down-3', **uniq(10)), 429, lambda b: not b['ok'])
reset(); op, tok = session()
for i in range(5): post(op, tok, **uniq(20 + i))
check('rate limit per IP (6th in 10 min)', post(op, tok, **uniq(29)), 429, lambda b: 'short time' in b['error'])
reset(); op, tok = session()
for i in range(3): post(op, tok, email='same@example.com', phone='+9198710%05d' % i)
check('rate limit per email (4th/hour)', post(op, tok, email='same@example.com', phone='+919871099999'), 429, lambda b: not b['ok'])
reset()
check('CSRF missing', post(op, ''), 419, lambda b: not b['ok'])
try: op.open(BASE + '/api/enquiry/callback'); st = 200
except urllib.error.HTTPError as e: st = e.code
check('GET rejected', (st, {}), 405)
sql("INSERT INTO settings (setting_key, setting_value) VALUES ('fe_antispam_enabled','0')")
check('CAPTCHA disabled → form refused', post(op, tok, **uniq(40)), 503, lambda b: not b['ok'])
html = urllib.request.urlopen(BASE + '/').read().decode()
check('CAPTCHA disabled → form hidden, WhatsApp/Email/Call remain', (200, {}), 200,
      lambda b: 'data-fe-open-form' not in html and all(('data-fe-action="%s"' % a) in html for a in ('whatsapp', 'email', 'call')))
sql("DELETE FROM settings WHERE setting_key LIKE 'fe_antispam_%'")

print('\n== Go-live gate: fallback, outage and recovery ==')
# Turnstile unavailable: accepted only under the fallback limit, flagged, logged.
reset(); op, tok = session()
check('Turnstile unavailable → accepted', post(op, tok, cf_turnstile_response='down-a', **uniq(50)), 200, lambda b: b['ok'])
check('  flagged "Security check unavailable" (payload)', (200, {}), 200, lambda b: last_payload('security_flag') == 'Security check unavailable')
check('  flagged in enquiry subject', (200, {}), 200, lambda b: sql('SELECT subject FROM enquiries ORDER BY id DESC LIMIT 1').startswith('[Security check unavailable]'))
check('  outage logged for administrators', (200, {}), 200, lambda b: log_has('captcha_unavailable', 'fallback_used'))
check('Turnstile unavailable 2nd → accepted', post(op, tok, cf_turnstile_response='down-b', **uniq(51)), 200, lambda b: b['ok'])
check('repeated beyond 2/hour → refused (3rd)', post(op, tok, cf_turnstile_response='down-c', **uniq(52)), 429, lambda b: not b['ok'])
check('repeated beyond 2/hour → still refused (4th)', post(op, tok, cf_turnstile_response='down-d', **uniq(53)), 429, lambda b: not b['ok'])
# Recovery: Cloudflare is back → normal verified path works for the same visitor.
check('recovery after Turnstile available → verified', post(op, tok, cf_turnstile_response='pass-recovered', **uniq(54)), 200, lambda b: b['ok'])
check('  recovered enquiry not flagged', (200, {}), 200, lambda b: last_payload('captcha') == 'verified' and last_payload('security_flag') in ('', 'null', 'NULL'))

# Missing rate-limit table → file-store fallback still enforces limits.
reset(); sql('RENAME TABLE anti_spam_hits TO anti_spam_hits_offline')
try:
    op, tok = session()
    codes = [post(op, tok, **uniq(60 + i))[0] for i in range(6)]
    check('missing table → file store used (5 allowed)', (codes[4], {}), 200, lambda b: codes[:5] == [200] * 5 and os.path.isdir(STORE))
    check('missing table → file store enforces limit (6th refused)', (codes[5], {}), 429)
    # Corrupted file store: the IP bucket is garbage → refused, and stays refused.
    shutil.rmtree(STORE, ignore_errors=True); os.makedirs(STORE)
    open(os.path.join(STORE, bucket('ip', '127.0.0.1') + '.json'), 'w').write('{corrupt')
    op, tok = session()
    check('corrupted file store → refused (neutral message)', post(op, tok, **uniq(70)), 429, lambda b: not b['ok'] and 'right now' in b['error'])
    check('  corruption logged', (200, {}), 200, lambda b: log_has('rate_store_corrupt'))
    check('corrupted bucket not silently reset', post(op, tok, **uniq(71)), 429, lambda b: not b['ok'])
    # Unavailable file store (table AND file store unusable) → refused, never unrestricted.
    shutil.rmtree(STORE, ignore_errors=True); open(STORE, 'w').write('not a directory')
    op, tok = session()
    check('file store unavailable → refused (never unrestricted)', post(op, tok, **uniq(80)), 429, lambda b: not b['ok'] and 'right now' in b['error'])
    check('  store outage logged', (200, {}), 200, lambda b: log_has('rate_store_unavailable', 'request_refused'))
finally:
    if os.path.isfile(STORE): os.remove(STORE)
    shutil.rmtree(STORE, ignore_errors=True)
    sql('RENAME TABLE anti_spam_hits_offline TO anti_spam_hits')
reset(); op, tok = session()
check('after restoring the table → normal service', post(op, tok, **uniq(90)), 200, lambda b: b['ok'])
reset()

passed = sum(1 for _, ok in results if ok)
print('\n%d/%d passed' % (passed, len(results)))
sys.exit(0 if passed == len(results) else 1)
