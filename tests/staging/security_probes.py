"""Security probes: stored XSS, SQL injection, CSV injection, session security, source exposure, privilege escalation.

    PYN_MYSQL="mysql … stg" python3 tests/staging/security_probes.py https://staging.example ./deployed/code

STAGING / DISPOSABLE COPY ONLY. Uses tests/staging/mksession.php (local file sessions) for pre-authenticated clients and the accounts from tests/staging/fixtures.php base.

55 probes. The earlier rehearsal had a 56th (a suspended super admin), removed on purpose: it depended on a
rehearsal-only account and needs no pre-existing data now. Test records use @stg.invalid and are removed.
"""
import json, os, re, secrets, shlex, subprocess, sys, urllib.parse, urllib.request, urllib.error, http.cookiejar
BASE, ROOT = sys.argv[1].rstrip('/'), sys.argv[2]
MKS = os.path.join(os.path.dirname(__file__), 'mksession.php')
MYSQL = shlex.split(os.environ['PYN_MYSQL']) + ['-N', '-e']
def sql(q): return subprocess.run(MYSQL + [q], capture_output=True, text=True).stdout.strip()
passes, fails = 0, []
def check(ok, msg):
    global passes
    if ok: passes += 1
    else: fails.append(msg); print('FAIL', msg)
class NR(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k): return None
def req(path, sid=None, data=None, cookie_jar=None, js=None, headers=None):
    h = dict(headers or {});
    if sid: h['Cookie'] = 'paynancial_session=' + sid
    body = json.dumps(js).encode() if js is not None else (urllib.parse.urlencode(data, doseq=True).encode() if data is not None else None)
    if js is not None: h['Content-Type'] = 'application/json'
    op = urllib.request.build_opener(NR, *( [urllib.request.HTTPCookieProcessor(cookie_jar)] if cookie_jar is not None else []))
    try:
        r = op.open(urllib.request.Request(BASE + path, data=body, headers=h), timeout=60); return r.status, r.read().decode('utf-8', 'ignore'), {k.lower(): v for k, v in r.headers.items()}
    except urllib.error.HTTPError as e: return e.code, e.read().decode('utf-8', 'ignore'), {k.lower(): v for k, v in e.headers.items()}
def sess(email): return subprocess.run(['php', MKS, ROOT, email], capture_output=True, text=True).stdout.strip()
def tok(h):
    m = re.search(r'name="csrf-token" content="([^"]+)"', h) or re.search(r'name="csrf_token" value="([^"]+)"', h); return m.group(1) if m else ''
PROBE = f'sec-probe-{secrets.token_hex(4)}@stg.invalid'  # unique per run (the lockout test locks the identifier)
SA = sess('stg-super@stg.invalid'); ADM = sess('admin1@stg.invalid')
ERR = re.compile(r'SQLSTATE|syntax error|PDOException|Fatal error|Warning:|Uncaught|Stack trace', re.I)

# ---------------- XSS: stored payload from an enquiry is escaped everywhere it renders
for path in ['/admin/enquiries', '/admin/dashboard', '/admin/search?q=Test', '/admin/enquiries?q=%3Cscript%3E', '/admin/search?q=%3Cscript%3Ealert(9)%3C/script%3E', '/admin/customers?q=%22%3E%3Csvg/onload=alert(1)%3E', '/admin/activity']:
    c, h, _ = req(path, SA)
    check(c == 200 and '<script>alert(' not in h and '<img src=x onerror' not in h and '<svg/onload' not in h, f'XSS escaped on {path} ({c})')
c, h, _ = req('/admin/enquiries?q=PAY-ENQ-STG-XSS', SA)
check('&lt;script&gt;alert(1)&lt;/script&gt;' in h, 'XSS payload shown as text')
c, h, _ = req('/admin/cms-seo?path=%3Cscript%3E', SA); check('<script>' not in h.split('</head>')[1] if '</head>' in h else True, 'cms-seo path param not reflected as markup')

# ---------------- SQL injection probes (params are whitelisted / bound)
before = sql('SELECT COUNT(*) FROM enquiries')
probes = ["/admin/enquiries?sort=created;DROP%20TABLE%20enquiries&dir=asc", "/admin/enquiries?sort=name&dir=desc,(SELECT%201)", "/admin/enquiries?q=%27%20OR%201%3D1%20--%20",
          "/admin/enquiries?status=new%27%20OR%20%271%27%3D%271", "/admin/enquiries?type=sales%27--", "/admin/enquiries?per=1%20UNION%20SELECT", "/admin/enquiries?page=-5",
          "/admin/enquiries?from=2026-01-01%27%20OR%201=1--", "/admin/customers?sort=code%20DESC,SLEEP(3)", "/admin/customers?q=%25%27%20UNION%20SELECT%20password_hash%20FROM%20users--",
          "/admin/documents?source=employee%27%20OR%201=1--&sort=owner;DELETE", "/admin/activity?area=auth%27%20OR%201=1--", "/admin/search?q=%27%29%20OR%20%281%3D1",
          "/admin/cms-article/1%20OR%201=1", "/admin/widget/..%2F..%2Fconfig", "/admin/users?role=admin%27%20OR%201=1--", "/admin/audit-logs?action=x%27%20OR%201=1--"]
for p in probes:
    c, h, _ = req(p, SA)
    check(c in (200, 404) and not ERR.search(h), f'SQLi probe handled {p} ({c})')
check(sql('SELECT COUNT(*) FROM enquiries') == before, 'no data changed by injection probes')
c, h, _ = req("/admin/customers?q=%25%27%20UNION%20SELECT%20password_hash%20FROM%20users--", SA)
check('$2y$' not in h, 'no password hashes leak via search')
c, h, _ = req('/admin/enquiries?export=csv&q=%3D1%2B1', SA)
check(c == 200, 'CSV export with formula-like search works')
sql("INSERT INTO enquiries (enquiry_code,type,name,email,message,status) VALUES ('PAY-ENQ-CSV-1','sales','=HYPERLINK(\"http://evil\")','csv@stg.invalid','x','new')")
c, h, _ = req('/admin/enquiries?export=csv&q=PAY-ENQ-CSV', SA)
check("'=HYPERLINK" in h, 'CSV formula injection neutralised')
sql("DELETE FROM enquiries WHERE enquiry_code='PAY-ENQ-CSV-1'")

# ---------------- session security
jar = http.cookiejar.CookieJar()
c, home, hd = req('/', cookie_jar=jar)
sc = hd.get('set-cookie', '')
check('httponly' in sc.lower() and 'samesite=lax' in sc.lower(), f'session cookie HttpOnly + SameSite=Lax ({sc[:120]})')
pre = [ck.value for ck in jar if ck.name == 'paynancial_session'][0]
hashed = subprocess.run(['php', '-r', 'echo password_hash("Sec-Pass-1", PASSWORD_DEFAULT);'], capture_output=True, text=True).stdout
otp = subprocess.run(['php', '-r', 'echo password_hash("135790", PASSWORD_DEFAULT);'], capture_output=True, text=True).stdout
sql(f"DELETE FROM users WHERE email='{PROBE}'")
sql(f"INSERT INTO users (uuid, role_id, full_name, email, password_hash, status) SELECT UUID(), id, 'Sec Probe', '{PROBE}', '{hashed}', 'active' FROM roles WHERE slug='support'")
uid = sql(f"SELECT id FROM users WHERE email='{PROBE}'")
req('/api/auth/login', js={'csrf_token': tok(home), 'login_type': 'employee', 'identifier': PROBE, 'password': 'Sec-Pass-1'}, cookie_jar=jar)
sql(f"UPDATE otp_verifications SET otp_hash='{otp}' WHERE user_id={uid} AND consumed_at IS NULL")
c, b, _ = req('/api/auth/verify-otp', js={'csrf_token': tok(home), 'otp': '135790'}, cookie_jar=jar)
post = [ck.value for ck in jar if ck.name == 'paynancial_session'][0]
check(c == 200 and post != pre, 'session id regenerated at login (fixation defence)')
c, _, _ = req('/admin/dashboard', pre); check(c == 302, 'pre-login session id is not authenticated')
c, _, _ = req('/admin/dashboard', cookie_jar=jar); check(c == 200, 'logged-in session works')
req('/api/auth/logout', data={}, cookie_jar=jar)
c, _, _ = req('/admin/dashboard', post); check(c == 302, 'session destroyed at logout (old id rejected)')
# brute force: lockout after failed attempts (identifier-based, DB)
j2 = http.cookiejar.CookieJar(); c, home2, _ = req('/', cookie_jar=j2)
for i in range(6): c, b, _ = req('/api/auth/login', js={'csrf_token': tok(home2), 'login_type': 'employee', 'identifier': PROBE, 'password': 'wrong'}, cookie_jar=j2)
check('Too many' in b or c == 429, f'login lockout after repeated failures ({c} {b[:60]})')
check(sql(f"SELECT COUNT(*) FROM audit_logs WHERE action='auth.login_failed' AND entity_id={uid}") >= '5', 'failed logins audited')
check('Sec-Pass' not in sql(f"SELECT GROUP_CONCAT(meta_json) FROM audit_logs WHERE entity_id={uid}"), 'no password in audit')

# ---------------- direct access to code / non-routes
for p in ['/admin/dashboard.php', '/../includes/audit.php', '/includes/audit.php', '/config/config.php', '/tests/admin/helper.php', '/tools/cms-import-blog.php', '/database/seed.sql', '/admin/nonexistent', '/admin/../config/config.php', '/uploads/.htaccess']:
    c, h, _ = req(p, SA)
    check(c in (403, 404) or (c == 200 and 'DB_PASS' not in h and '<?php' not in h and 'define(' not in h), f'no source exposure at {p} ({c})')

# ---------------- privilege escalation
c, h, _ = req('/admin/roles', ADM); ta = tok(req('/admin/dashboard', ADM)[1])
for data, label in [({'op': 'toggle', 'cell': sql("SELECT id FROM roles WHERE slug='super_admin'") + ':' + sql("SELECT MIN(id) FROM permissions") + ':0'}, 'admin edits super-admin role'), ({'op': 'assign', 'user_id': sql("SELECT id FROM users WHERE email='admin1@stg.invalid'"), 'role': 'super_admin'}, 'admin self-promotes'),
                    ({'op': 'assign', 'user_id': sql("SELECT id FROM users WHERE email='admin2@stg.invalid'"), 'role': 'super_admin'}, 'admin promotes a peer')]:
    c, _, _ = req('/admin/roles', ADM, {'csrf_token': ta, **data}); check(c == 403, f'{label} blocked ({c})')
check(sql("SELECT r.slug FROM users u JOIN roles r ON r.id=u.role_id WHERE email='admin1@stg.invalid'") == 'admin', 'admin role unchanged')
c, _, _ = req('/admin/cms', ADM, {'csrf_token': ta, 'op': 'access', 'user_id': sql("SELECT id FROM users WHERE email='admin1@stg.invalid'"), 'permission_id': sql("SELECT id FROM permissions WHERE slug='cms.publish'"), 'effect': 'grant'})
check(sql("SELECT COUNT(*) FROM user_permissions up JOIN users u ON u.id=up.user_id WHERE u.email='admin1@stg.invalid'") == '0', f'admin cannot self-grant CMS publish ({c})')
ts = tok(req('/admin/dashboard', SA)[1])
cust_role = sql("SELECT id FROM roles WHERE slug='customer'"); perm = sql("SELECT id FROM permissions WHERE slug='roles.manage'")
req('/admin/roles', SA, {'csrf_token': ts, 'op': 'toggle', 'cell': f'{cust_role}:{perm}:1'})
check(sql(f'SELECT COUNT(*) FROM role_permissions WHERE role_id={cust_role} AND permission_id={perm}') == '0', 'non-staff role cannot receive admin permissions via tampered cell')
req('/admin/roles', SA, {'csrf_token': ts, 'op': 'assign', 'user_id': uid, 'role': 'customer'})
check(sql(f'SELECT r.slug FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id={uid}') == 'support', 'cannot assign non-staff role via admin')
sql(f"DELETE FROM users WHERE email='{PROBE}'")
print(f'\nSecurity probes: {passes} passed, {len(fails)} failed')
