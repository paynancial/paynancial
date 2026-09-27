"""12-role RBAC matrix with REAL logins (password → OTP → session) and real form submissions.

    PYN_MYSQL="mysql … stg" python3 tests/staging/rbac_matrix.py https://staging.example ./path/to/deployed/code

STAGING / DISPOSABLE COPY ONLY — never production. Needs a MySQL CLI for the
staging database in PYN_MYSQL (e.g. "mysql -h db -u stg -p… stg"), the staging
base URL and the path of the deployed code. The test sets a known OTP hash for its own test users (it cannot read email), creates users rbac-{role}@stg.invalid and
resets the fixtures it touches. Requires tests/staging/fixtures.php (base + cms) to have run.
"""

import json, os, re, shlex, subprocess, sys, urllib.parse, urllib.request, urllib.error, http.cookiejar
BASE, ROOT = sys.argv[1].rstrip('/'), sys.argv[2]
MYSQL = shlex.split(os.environ['PYN_MYSQL']) + ['-N', '-e']
ROLES = ['super_admin', 'admin', 'operations_manager', 'sales_manager', 'consultant', 'incorporation_consultant', 'content_manager', 'seo_manager', 'compliance_reviewer', 'finance_manager', 'developer', 'support']
PW = 'Stg-Rbac-Pass-9'
passes, fails, matrix = 0, [], {}
def sql(q): return subprocess.run(MYSQL + [q], capture_output=True, text=True).stdout.strip()
def check(ok, msg):
    global passes
    if ok: passes += 1
    else: fails.append(msg); print('FAIL', msg)
class NR(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k): return None
class Client:
    def __init__(self): self.jar = http.cookiejar.CookieJar(); self.op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar), NR)
    def req(self, path, data=None, js=None, headers=None):
        h = dict(headers or {}); body = None
        if js is not None: body = json.dumps(js).encode(); h['Content-Type'] = 'application/json'
        elif data is not None: body = urllib.parse.urlencode(data, doseq=True).encode()
        try:
            r = self.op.open(urllib.request.Request(BASE + path, data=body, headers=h), timeout=60); return r.status, r.read().decode('utf-8', 'ignore'), dict(r.headers)
        except urllib.error.HTTPError as e: return e.code, e.read().decode('utf-8', 'ignore'), dict(e.headers)
def tok(html):
    m = re.search(r'name="csrf-token" content="([^"]+)"', html) or re.search(r'name="csrf_token" value="([^"]+)"', html) or re.search(r"csrf_token: '([^']+)'", html)
    return m.group(1) if m else ''
hashed = subprocess.run(['php', '-r', f'echo password_hash("{PW}", PASSWORD_DEFAULT);'], capture_output=True, text=True).stdout
otp = subprocess.run(['php', '-r', 'echo password_hash("246810", PASSWORD_DEFAULT);'], capture_output=True, text=True).stdout
for r in ROLES:
    e = f'rbac-{r}@stg.invalid'; sql(f"DELETE FROM users WHERE email='{e}'")
    sql(f"INSERT INTO users (uuid, role_id, full_name, email, password_hash, status) SELECT UUID(), id, 'Rbac {r}', '{e}', '{hashed}', 'active' FROM roles WHERE slug='{r}'")
start = int(sql('SELECT MAX(id) FROM audit_logs'))
art_pub = sql("SELECT id FROM blog_posts WHERE slug='staging-approved-article'")
sql("DELETE FROM blog_posts WHERE slug='staging-legal-review'")
sql(f"INSERT INTO blog_posts (slug,title,category,excerpt,body_html,content_json,meta_description,status,submitted_by,submitted_at) SELECT 'staging-legal-review','Staging legal review','payments','d',body_html,content_json,'desc','business_legal_review',1,NOW() FROM blog_posts WHERE id={art_pub}")
art_appr = sql("SELECT id FROM blog_posts WHERE slug='staging-legal-review'")
prod = sql('SELECT id FROM products ORDER BY id LIMIT 1'); prod_row = sql(f'SELECT name, short_description, complexity, pricing_status, commission_eligible, is_active, sort_order FROM products WHERE id={prod}').split('\t'); prod_name = prod_row[0]
papp = sql("SELECT id FROM partner_applications WHERE application_code='PYN-PARTNER-APP-STG'")
assert art_pub and papp and prod, 'run tests/staging/fixtures.php base + cms first'
for role in ROLES:
    email = f'rbac-{role}@stg.invalid'; uid = sql(f"SELECT id FROM users WHERE email='{email}'")
    exp = json.loads(subprocess.run(['php', os.path.join(os.path.dirname(__file__), 'expect.php'), ROOT, email], capture_output=True, text=True).stdout)
    res = {}
    c = Client(); code, home, _ = c.req('/')
    code, body, _ = c.req('/api/auth/login', js={'csrf_token': tok(home), 'login_type': 'employee', 'identifier': email, 'password': PW})
    d = json.loads(body or '{}')
    check(code == 200 and d.get('otp_required'), f'{role}: password step accepted on the staff sign-in ({code} {body[:80]})')
    sql(f"UPDATE otp_verifications SET otp_hash='{otp}' WHERE user_id={uid} AND consumed_at IS NULL")  # the test reads the "email"
    code, body, _ = c.req('/api/auth/verify-otp', js={'csrf_token': tok(home), 'otp': '246810'})
    d = json.loads(body or '{}')
    check(code == 200 and d.get('redirect') == ('/super-admin/dashboard' if role == 'super_admin' else '/admin/dashboard'), f'{role}: OTP completes login → Command Center ({code} {body[:80]})')
    res['login'] = code == 200
    check(sql(f"SELECT actor_role FROM audit_logs WHERE action='auth.login' AND entity_id={uid} ORDER BY id DESC LIMIT 1") == role, f'{role}: login audited with role')
    code, dash, _ = c.req('/admin/dashboard')
    nav = sorted(set(re.findall(r'href="/admin/([a-z-]+)" class="adm-nav-link', dash)))
    check(nav == sorted(exp['nav']), f'{role}: sidebar = permitted modules ({nav} vs {sorted(exp["nav"])})')
    T = tok(dash)
    # direct URL access: all 29 guarded routes = 28 registered admin routes (endpoints with their real paths) + /super-admin/dashboard
    routes = {k: '/admin/' + k for k in exp['pages']}
    routes.update({'cms-article': f'/admin/cms-article/{art_pub}', 'cms-preview': f'/admin/cms-preview?type=article&id={art_pub}', 'widget': '/admin/widget/kpi.leads_today', 'search': '/admin/search?q=a'})
    denied = 0
    for k, path in routes.items():
        if k == 'preferences': continue  # POST-only endpoint (tested below)
        code, body, _ = c.req(path)
        ok = code == 200 if exp['pages'][k] else code == 403
        check(ok, f'{role}: GET {path} → {code} (expected {"200" if exp["pages"][k] else "403"})')
        denied += 0 if exp['pages'][k] else 1
    code, _, _ = c.req('/super-admin/dashboard')  # area gate: super_admin only; other roles are redirected by require_role()
    check(code == (200 if role == 'super_admin' else 302), f'{role}: GET /super-admin/dashboard → {code}')
    code, _, _ = c.req('/admin/preferences')  # POST-only endpoint: GET must not expose anything beyond the guard
    check(code in (200, 403, 405), f'{role}: GET /admin/preferences → {code}')
    res['routes_checked'] = len(routes) + 1  # 28 registered (preferences via POST below) + /super-admin/dashboard
    res['routes_allowed'] = sum(1 for v in exp['pages'].values() if v); res['routes_denied'] = denied
    code, _, _ = c.req('/admin/preferences', js={'key': 'sidebar', 'value': 'expanded'}, headers={'X-CSRF-Token': T})
    check(code == 200, f'{role}: preferences POST ({code})')
    # form submissions (expected from the permission catalogue)
    def post(path, data): return c.req(path, {'csrf_token': T, **data})
    E = exp['perms']
    eid = sql("SELECT id FROM enquiries WHERE enquiry_code='PAY-ENQ-STG-RBAC'"); sql(f"UPDATE enquiries SET status='new' WHERE id={eid}")
    code, _, _ = post('/admin/enquiries', {'op': 'status', 'enquiry_id': eid, 'status': 'closed'})
    changed = sql(f'SELECT status FROM enquiries WHERE id={eid}') == 'closed'
    check(changed == E['enquiries.manage'] and (code == 303 if E['enquiries.manage'] else code == 403), f'{role}: enquiry status change allowed={E["enquiries.manage"]} ({code})'); res['enq_manage'] = changed
    code, _, _ = post('/admin/enquiries', {'op': 'create', 'type': 'sales', 'name': f'Rbac {role}', 'email': 'r@stg.invalid', 'message': 'm'})
    made = sql(f"SELECT COUNT(*) FROM enquiries WHERE name='Rbac {role}'") == '1'
    check(made == E['enquiries.create'], f'{role}: enquiry create allowed={E["enquiries.create"]} ({code})'); res['enq_create'] = made
    code, body, h = c.req('/admin/enquiries?export=csv')
    exported = code == 200 and 'text/csv' in h.get('Content-Type', '')
    check(exported == (E['enquiries.export'] and E['enquiries.view']), f'{role}: export allowed={E["enquiries.export"]} ({code})'); res['export'] = exported
    code, _, _ = post('/admin/customers', {'op': 'create', 'company_name': f'Rbac Co {role}', 'contact_name': 'C', 'contact_email': f'co-{role}@stg.invalid'})
    made = sql(f"SELECT COUNT(*) FROM customers WHERE company_name='Rbac Co {role}' AND user_id IS NULL") == '1'
    check(made == (E['customers.create']), f'{role}: customer create allowed={E["customers.create"]} ({code})'); res['cust_create'] = made
    before = sql("SELECT setting_value FROM settings WHERE setting_key LIKE '%honeypot_enabled'")
    code, _, _ = post('/admin/anti-spam', {'enabled': '1', 'active': '1', 'honeypot_enabled': '1', 'notify_to': '', 'per_ip_max': '5', 'per_ip_window': '3600', 'per_contact_max': '3', 'per_contact_window': '3600', 'global_max': '50', 'global_window': '3600', 'fallback_max': '2', 'fallback_window': '3600'})
    check((code != 403) == (E['settings.manage'] and E['security.view']), f'{role}: settings change allowed={E["settings.manage"]} ({code})'); res['settings'] = code != 403
    target = sql("SELECT id FROM users WHERE email='rbac-support@stg.invalid'")
    code, _, _ = post('/admin/roles', {'op': 'assign', 'user_id': target, 'role': 'developer'})
    moved = sql(f'SELECT r.slug FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id={target}') == 'developer'
    if moved: sql(f"UPDATE users SET role_id=(SELECT id FROM roles WHERE slug='support') WHERE id={target}")
    check(moved == (E['roles.manage'] and role != 'support'), f'{role}: role assignment allowed={E["roles.manage"]} ({code})'); res['roles'] = moved
    sql(f"UPDATE blog_posts SET status='business_legal_review', approved_by=NULL, approved_at=NULL WHERE id={art_appr}")
    lock = sql(f'SELECT lock_version FROM blog_posts WHERE id={art_appr}')
    code, _, _ = post(f'/admin/cms-article/{art_appr}', {'op': 'transition', 'wf': 'approve', 'lock': lock})
    appr = sql(f'SELECT status FROM blog_posts WHERE id={art_appr}') == 'approved'
    check(appr == (E['cms.approve'] and E['cms.view']), f'{role}: CMS approve allowed={E["cms.approve"]} ({code})'); res['approve'] = appr
    sql(f"UPDATE blog_posts SET status='approved', live=0 WHERE id={art_pub}")
    lock = sql(f'SELECT lock_version FROM blog_posts WHERE id={art_pub}')
    code, _, _ = post(f'/admin/cms-article/{art_pub}', {'op': 'transition', 'wf': 'publish', 'lock': lock, 'confirm': '1'})
    pub = sql(f'SELECT live FROM blog_posts WHERE id={art_pub}') == '1'
    check(pub == (E['cms.publish'] and E['cms.view']), f'{role}: CMS publish allowed={E["cms.publish"]} ({code})'); res['publish'] = pub
    sql(f"UPDATE products SET name='{prod_name}' WHERE id={prod}")
    pf = {'form_action': 'update', 'product_id': prod, 'name': prod_name + ' RBAC', 'short_description': '' if prod_row[1] == 'NULL' else prod_row[1],
          'complexity': prod_row[2], 'pricing_status': prod_row[3], 'sort_order': prod_row[6]}  # full form, as the UI posts it
    if prod_row[4] == '1': pf['commission_eligible'] = '1'
    if prod_row[5] == '1': pf['is_active'] = '1'
    code, _, _ = post('/admin/products', pf)
    upd = sql(f'SELECT name FROM products WHERE id={prod}').endswith('RBAC')
    sql(f"UPDATE products SET name='{prod_name}' WHERE id={prod}")
    check(upd == (E['products.manage'] and E['products.view']), f'{role}: product update allowed={E["products.manage"]} ({code})'); res['products'] = upd
    sql(f"UPDATE partner_applications SET status='submitted' WHERE id={papp}")
    code, _, _ = post(f'/admin/partner-applications/{papp}', {'form_action': 'request_info', 'status_note': 'rbac'})
    pa = sql(f'SELECT status FROM partner_applications WHERE id={papp}') == 'info_required'
    check(pa == (E['partners.manage'] and E['partners.view']), f'{role}: partner application decision allowed={E["partners.manage"]} ({code})'); res['partners'] = pa
    code, _, _ = c.req('/admin/enquiries', {'op': 'status', 'enquiry_id': eid, 'status': 'responded'})
    check(code == 419, f'{role}: POST without CSRF token rejected ({code})')
    code, _, _ = c.req('/api/auth/logout', {})
    code, _, _ = c.req('/admin/dashboard')
    check(code == 302, f'{role}: logged out → admin closed ({code})'); res['logout'] = code == 302
    matrix[role] = res
sql(f"UPDATE blog_posts SET status='approved', live=0 WHERE id={art_pub}"); sql(f'DELETE FROM blog_posts WHERE id={art_appr}')
sql("UPDATE enquiries SET status='new', assigned_to=NULL WHERE enquiry_code='PAY-ENQ-STG-RBAC'"); sql("DELETE FROM enquiries WHERE name LIKE 'Rbac %'"); sql("DELETE FROM customers WHERE company_name LIKE 'Rbac Co %'"); sql(f"UPDATE partner_applications SET status='submitted' WHERE id={papp}")
print(json.dumps(matrix, indent=0))
n = {r: v.get('routes_checked') for r, v in matrix.items()}
print(f'guarded routes validated per role: {sorted(set(n.values()))} (expected [29]: 28 registered admin routes + /super-admin/dashboard)')
print(f'\nRBAC matrix: {passes} passed, {len(fails)} failed; audit rows written: {int(sql("SELECT MAX(id) FROM audit_logs")) - start}')
