#!/usr/bin/env python3
"""
Enterprise admin platform (Phase 1) — HTTP end-to-end test.
LOCAL ONLY: runs against the local PHP dev server and a local database.

    CMS_TEST_WRITE=1 PYN_MYSQL="mysql -uroot --socket=/path/sock pyn" \
      python3 tests/admin/test_admin_http.py [http://127.0.0.1:8106]

Covers: authorised / unauthorised page access for all 12 roles (403, not
hidden links), unauthorised mutation, CSRF, audit records with old/new
values (enquiry update, customer create, export, role change, permission
change, access denied, logout, failed login), honest dashboard (no fake
metrics, "Not connected" states, widget endpoint), System Health, UI
preferences, and regression of the CMS, public site and the customer /
partner / employee / HRMS portals (which must keep the legacy shell).
Creates users prefixed adm-http- and removes them afterwards.
"""
import json, os, re, shlex, subprocess, sys, urllib.error, urllib.parse, urllib.request

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
BASE = (sys.argv[1] if len(sys.argv) > 1 else os.environ.get('PYN_BASE_URL', 'http://127.0.0.1:8106')).rstrip('/')
MYSQL = shlex.split(os.environ['PYN_MYSQL'])
STAFF = ['super_admin', 'admin', 'operations_manager', 'sales_manager', 'consultant', 'incorporation_consultant',
         'content_manager', 'seo_manager', 'compliance_reviewer', 'finance_manager', 'developer', 'support']
PORTALS = {'customer': '/customer/dashboard', 'partner': '/partner/dashboard', 'employee': '/employee/dashboard', 'hr': '/hrms/dashboard'}
passes, fails = 0, []


def check(ok, msg):
    global passes
    if ok:
        passes += 1
    else:
        fails.append(msg)
        print('FAIL', msg)


def sql(q):
    r = subprocess.run(MYSQL + ['-N', '-e', q], capture_output=True, text=True)
    if r.returncode:
        raise SystemExit('SQL failed: ' + r.stderr)
    return r.stdout.strip()


def helper(*args):
    env = dict(os.environ, CMS_TEST_WRITE='1')
    return subprocess.run(['php', os.path.join(ROOT, 'tests/admin/helper.php')] + list(args), capture_output=True, text=True, env=env).stdout.strip()


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


OPENER = urllib.request.build_opener(NoRedirect)


def req(sid, path, data=None, headers=None, raw=None):
    h = {'Cookie': 'paynancial_session=' + sid} if sid else {}
    h.update(headers or {})
    body = raw if raw is not None else (urllib.parse.urlencode(data, doseq=True).encode() if data is not None else None)
    r = urllib.request.Request(BASE + path, data=body, headers=h)
    try:
        resp = OPENER.open(r, timeout=60)
        return resp.status, resp.read().decode('utf-8', 'ignore'), {k.lower(): v for k, v in resp.headers.items()}
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8', 'ignore'), {k.lower(): v for k, v in e.headers.items()}


def token(html):
    m = re.search(r'name="csrf-token" content="([^"]+)"', html) or re.search(r'name="csrf_token" value="([^"]+)"', html)
    return m.group(1) if m else ''


def last_audit(action, extra=''):
    return sql(f"SELECT meta_json FROM audit_logs WHERE action = '{action}' {extra} ORDER BY id DESC LIMIT 1")


# ------------------------------------------------------------------ fixtures
emails = {}
for role in STAFF + list(PORTALS):
    email = f'adm-http-{role}@test.invalid'
    emails[role] = email
    sql(f"DELETE FROM users WHERE email = '{email}'")
    sql(f"INSERT INTO users (uuid, role_id, full_name, email, password_hash, status) SELECT UUID(), id, 'Http {role}', '{email}', "
        f"'$2y$10$abcdefghijklmnopqrstuuM6bLYjL9gJvqg2tK6Y7r9o0tWm5j2pq', 'active' FROM roles WHERE slug = '{role}'")
uid = {r: int(sql(f"SELECT id FROM users WHERE email = '{e}'")) for r, e in emails.items()}
sid = {r: helper('session', e) for r, e in emails.items()}
start_audit = int(sql('SELECT COALESCE(MAX(id),0) FROM audit_logs'))

try:
    # -------------------------------------------------------------- access matrix (all roles × all modules)
    for role in STAFF:
        expected = json.loads(helper('matrix', emails[role]))
        c, html, _ = req(sid[role], '/admin/dashboard')
        nav = set(re.findall(r'href="/admin/([a-z-]+)" class="adm-nav-link', html))
        for page, allowed in expected.items():
            code, body, _ = req(sid[role], '/admin/' + page)
            check(code == (200 if allowed else 403), f'{role} GET /admin/{page} → {code} (expected {200 if allowed else 403})')
            check((page in nav) == allowed, f'{role} sidebar shows {page}: {page in nav} (expected {allowed})')
            if not allowed:
                check('Access denied' in body and 'Required permission' in body, f'{role} /admin/{page} shows the access-denied page')
        check('adm-shell' in html, f'{role} gets the enterprise shell')
    check(sql(f"SELECT COUNT(*) FROM audit_logs WHERE action = 'access.denied' AND id > {start_audit}") != '0', 'denied page views are audited')

    # non-staff roles cannot enter the admin area at all
    for role in PORTALS:
        c, _, h = req(sid[role], '/admin/dashboard')
        check(c == 302, f'{role} redirected away from /admin ({c})')

    # -------------------------------------------------------------- mutations, CSRF, audit
    sql("INSERT INTO enquiries (enquiry_code, type, name, email, message, status) VALUES ('ADM-HTTP-1','sales','Http Test','h@test.invalid','x','new')")
    eid = sql("SELECT id FROM enquiries WHERE enquiry_code = 'ADM-HTTP-1'")
    c, html, _ = req(sid['consultant'], '/admin/enquiries')
    tok_c = token(html)
    c, _, _ = req(sid['consultant'], '/admin/enquiries', {'csrf_token': tok_c, 'op': 'status', 'enquiry_id': eid, 'status': 'closed'})
    check(c == 403 and sql(f'SELECT status FROM enquiries WHERE id = {eid}') == 'new', f'consultant cannot change enquiry status ({c})')
    c, html, _ = req(sid['support'], '/admin/enquiries')
    tok_s = token(html)
    c, _, _ = req(sid['support'], '/admin/enquiries', {'op': 'status', 'enquiry_id': eid, 'status': 'closed'})
    check(c == 419 and sql(f'SELECT status FROM enquiries WHERE id = {eid}') == 'new', f'missing CSRF token rejected ({c})')
    c, _, h = req(sid['support'], '/admin/enquiries', {'csrf_token': tok_s, 'op': 'status', 'enquiry_id': eid, 'status': 'in_progress', 'return': '/admin/enquiries'})
    check(c == 303 and sql(f'SELECT status FROM enquiries WHERE id = {eid}') == 'in_progress', f'support can change enquiry status ({c})')
    m = json.loads(last_audit('enquiry.updated', f'AND entity_id = {eid}') or '{}')
    check(m.get('old', {}).get('status') == 'new' and m.get('new', {}).get('status') == 'in_progress', 'enquiry update audited with old/new values')
    check(sql(f"SELECT actor_role FROM audit_logs WHERE action = 'enquiry.updated' AND entity_id = {eid} ORDER BY id DESC LIMIT 1") == 'support', 'audit records the actor role')
    check(sql(f"SELECT COUNT(*) FROM audit_logs WHERE action = 'admin.request' AND id > {start_audit} AND JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.page')) = 'enquiries'") != '0', 'every admin POST is audited centrally')
    # bulk
    c, _, _ = req(sid['support'], '/admin/enquiries', {'csrf_token': tok_s, 'op': 'bulk', 'ids[]': [eid], 'bulk_action': 'assign_me', 'return': '/admin/enquiries'})
    check(sql(f'SELECT assigned_to FROM enquiries WHERE id = {eid}') == str(uid['support']), 'bulk assign works')
    # create enquiry
    c, _, _ = req(sid['consultant'], '/admin/enquiries', {'csrf_token': tok_c, 'op': 'create', 'type': 'sales', 'name': 'Http Created', 'email': 'hc@test.invalid', 'message': 'hello'})
    check(c == 303 and sql("SELECT COUNT(*) FROM enquiries WHERE name = 'Http Created'") == '1', f'consultant can create an enquiry ({c})')
    check(last_audit('enquiry.created') != '', 'enquiry creation audited')
    # export
    c, body, h = req(sid['sales_manager'], '/admin/enquiries?export=csv&q=ADM-HTTP')
    check(c == 200 and 'text/csv' in h.get('content-type', '') and 'ADM-HTTP-1' in body, f'sales manager can export CSV ({c})')
    check(json.loads(last_audit('enquiries.exported') or '{}').get('rows') == 1, 'export audited with row count')
    c, _, _ = req(sid['consultant'], '/admin/enquiries?export=csv')
    check(c == 403, f'consultant cannot export ({c})')

    # customer without login
    c, html, _ = req(sid['sales_manager'], '/admin/customers?new=1')
    c, _, _ = req(sid['sales_manager'], '/admin/customers', {'csrf_token': token(html), 'op': 'create', 'company_name': 'Http CRM Ltd', 'contact_name': 'Asha',
                                                             'contact_email': 'crm-http@test.invalid', 'source': 'referral'})
    check(c == 303 and sql("SELECT COUNT(*) FROM customers WHERE company_name = 'Http CRM Ltd' AND user_id IS NULL") == '1', f'customer created without a login ({c})')
    check(sql("SELECT COUNT(*) FROM users WHERE email = 'crm-http@test.invalid'") == '0', 'no login account created for the customer')
    check('Http CRM Ltd' in (last_audit('customer.created') or ''), 'customer creation audited')
    tok_ic = token(req(sid['incorporation_consultant'], '/admin/customers')[1])
    c, _, _ = req(sid['incorporation_consultant'], '/admin/customers', {'csrf_token': tok_ic, 'op': 'create', 'company_name': 'X', 'contact_name': 'Y', 'contact_email': 'x@test.invalid'})
    check(c == 403, f'incorporation consultant cannot create customers ({c})')

    # roles & permissions
    c, html, _ = req(sid['super_admin'], '/admin/roles')
    tok_sa = token(html)
    c, html_a, _ = req(sid['admin'], '/admin/roles')
    c, _, _ = req(sid['admin'], '/admin/roles', {'csrf_token': token(html_a), 'op': 'assign', 'user_id': uid['consultant'], 'role': 'super_admin'})
    check(c == 403 and sql(f"SELECT r.slug FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id={uid['consultant']}") == 'consultant', f'administrator cannot change roles ({c})')
    c, _, _ = req(sid['super_admin'], '/admin/roles', {'csrf_token': tok_sa, 'op': 'assign', 'user_id': uid['consultant'], 'role': 'support'})
    check(sql(f"SELECT r.slug FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id={uid['consultant']}") == 'support', 'super admin changed a role')
    m = json.loads(last_audit('role.changed', f"AND entity_id = {uid['consultant']}") or '{}')
    check(m.get('old', {}).get('role') == 'consultant' and m.get('new', {}).get('role') == 'support', 'role change audited with old/new')
    c, _, _ = req(sid['consultant'], '/admin/dashboard')
    check(c == 302, f'role change signed the person out ({c})')
    c, _, _ = req(sid['super_admin'], '/admin/roles', {'csrf_token': tok_sa, 'op': 'assign', 'user_id': uid['super_admin'], 'role': 'support'})
    check(sql(f"SELECT r.slug FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id={uid['super_admin']}") == 'super_admin', 'nobody can change their own role')
    dev_role = sql("SELECT id FROM roles WHERE slug = 'developer'")
    perm = sql("SELECT id FROM permissions WHERE slug = 'audit.view'")
    c, _, _ = req(sid['super_admin'], '/admin/roles', {'csrf_token': tok_sa, 'op': 'toggle', 'cell': f'{dev_role}:{perm}:1'})
    check(sql(f'SELECT COUNT(*) FROM role_permissions WHERE role_id={dev_role} AND permission_id={perm}') == '1', 'permission granted to a role')
    check('audit.view' in (last_audit('permission.granted') or ''), 'permission grant audited')
    c, _, _ = req(sid['developer'], '/admin/audit-logs')
    check(c == 200, f'granted permission takes effect ({c})')
    req(sid['super_admin'], '/admin/roles', {'csrf_token': tok_sa, 'op': 'toggle', 'cell': f'{dev_role}:{perm}:0'})
    check(sql(f'SELECT COUNT(*) FROM role_permissions WHERE role_id={dev_role} AND permission_id={perm}') == '0' and last_audit('permission.revoked') != '', 'permission revoked + audited')
    c, _, _ = req(sid['developer'], '/admin/audit-logs')
    check(c == 403, f'revoked permission takes effect ({c})')

    # -------------------------------------------------------------- honest dashboard
    c, html, _ = req(sid['super_admin'], '/admin/dashboard')
    for fake in ['99.98', '28,450', '28450', 'All Systems Operational']:
        check(fake not in html, f'dashboard shows no fabricated value "{fake}"')
    for honest in ['Analytics not connected', 'Not connected', 'Incorporation Pipeline', 'Leads Pipeline', 'Recent Enquiries', 'Quick Actions', 'Environment Status', 'Pending Approvals', 'Recent Activity', 'Source: enquiries']:
        check(honest in html, f'dashboard contains "{honest}"')
    check('Pigeline' not in html, 'no "Pigeline" typo')
    check('data-widget-async="rail.health"' in html and 'adm-skel' in html, 'async widget has a loading state')
    check(html.count('class="adm-qa-tile') <= 5, 'at most five quick actions')
    c, body, _ = req(sid['super_admin'], '/admin/widget/rail.health')
    d = json.loads(body)
    check(c == 200 and d['state'] == 'ok' and 'uptime monitor not connected' in d['html'] and d.get('source'), 'widget endpoint returns rendered HTML with provenance')
    c, body, _ = req(sid['super_admin'], '/admin/widget/kpi.incorporation')
    check(json.loads(body)['state'] == 'not_connected', 'incorporation KPI is not connected')
    c, body, _ = req(sid['finance_manager'], '/admin/widget/kpi.content_review')
    check(json.loads(body)['state'] == 'restricted', 'widget respects permissions')
    # preferences
    tok = token(html)
    c, _, _ = req(sid['super_admin'], '/admin/preferences', raw=json.dumps({'key': 'hero_collapsed', 'value': True}).encode(),
                  headers={'Content-Type': 'application/json', 'X-CSRF-Token': tok})
    check(c == 200 and sql(f"SELECT value_json FROM user_preferences WHERE user_id={uid['super_admin']} AND pref_key='hero_collapsed'") == 'true', f'preference saved ({c})')
    c, html2, _ = req(sid['super_admin'], '/admin/dashboard')
    check('adm-hero is-collapsed' in html2, 'hero collapse remembered')
    c, _, _ = req(sid['super_admin'], '/admin/preferences', raw=json.dumps({'key': 'hero_collapsed', 'value': True}).encode(), headers={'Content-Type': 'application/json'})
    check(c == 419, f'preference without CSRF rejected ({c})')
    c, _, _ = req(sid['super_admin'], '/admin/preferences', raw=json.dumps({'key': 'sidebar', 'value': '<x>'}).encode(), headers={'Content-Type': 'application/json', 'X-CSRF-Token': tok})
    check(c == 422, f'invalid preference rejected ({c})')

    # system health
    c, html, _ = req(sid['developer'], '/admin/system-health')
    check(c == 200 and 'Uptime monitoring' in html and 'Not connected' in html and '99.' not in re.sub(r'<[^>]+>', '', html).split('Uptime monitoring')[1][:200], 'system health: real checks, uptime not connected')
    c, _, _ = req(sid['consultant'], '/admin/system-health')
    check(c in (302, 403), f'system health restricted ({c})')

    # search + palette permission filtering
    c, html, _ = req(sid['finance_manager'], '/admin/search?q=Http')
    check(c == 200 and 'Enquiries' not in re.findall(r'<h2 class="adm-hc-group">([^<]+)', html), 'search skips sections the user cannot view')
    pal = json.loads(re.search(r'<script type="application/json" id="adm-palette-data">(.*?)</script>', req(sid['finance_manager'], '/admin/dashboard')[1]).group(1))
    check(not [p for p in pal if p['url'].startswith('/admin/roles') or p['url'].startswith('/admin/enquiries')], 'command palette lists only permitted items')

    # logout + failed login audit
    c, _, _ = req(sid['developer'], '/api/auth/logout', {})
    check(last_audit('auth.logout', f"AND user_id = {uid['developer']}") != '', 'logout audited')
    c, home, h = req('', '/')
    ck = h.get('set-cookie', '').split(';')[0]
    t = token(home)
    r = urllib.request.Request(BASE + '/api/auth/login', data=json.dumps({'csrf_token': t, 'login_type': 'employee', 'identifier': emails['support'], 'password': 'wrong-password'}).encode(),
                               headers={'Content-Type': 'application/json', 'Cookie': ck})
    try:
        OPENER.open(r)
    except urllib.error.HTTPError:
        pass
    m = last_audit('auth.login_failed', f"AND entity_id = {uid['support']}")
    check(m != '' and 'wrong-password' not in m and 'ad***@test.invalid' in m, 'failed login audited, identifier masked, no password')

    # -------------------------------------------------------------- regression
    for page in ['/admin/cms', '/admin/cms-articles', '/admin/cms-hero', '/admin/cms-seo', '/admin/content-governance', '/admin/audit-logs',
                 '/admin/users', '/admin/transactions', '/admin/products', '/admin/partner-applications', '/admin/customer-applications',
                 '/admin/customer-kyc', '/admin/commission-rules', '/admin/change-requests', '/admin/anti-spam', '/super-admin/dashboard']:
        c, body, _ = req(sid['super_admin'], page)
        check(c == 200 and not re.search(r'Warning:|Fatal error|Notice:|Deprecated:', body), f'regression {page} → {c}')
    for page in ['/', '/blog', '/about', '/products', '/business-services', '/contact', '/legal']:
        c, body, _ = req('', page)
        check(c == 200 and 'adm-shell' not in body, f'public {page} unaffected ({c})')
    for role, path in PORTALS.items():
        c, body, _ = req(sid[role], path)
        check(c in (200, 302) and 'adm-shell' not in body and 'class="adm"' not in body, f'{role} portal keeps its own shell ({c})')
finally:
    ids = ','.join(str(i) for i in uid.values())
    sql("DELETE FROM enquiries WHERE enquiry_code = 'ADM-HTTP-1' OR name = 'Http Created'")
    sql("DELETE FROM customers WHERE company_name = 'Http CRM Ltd'")
    sql(f'DELETE FROM user_preferences WHERE user_id IN ({ids})')
    sql(f'DELETE FROM audit_logs WHERE id > {start_audit} AND (user_id IN ({ids}) OR entity_id IN ({ids}))')
    sql(f'DELETE FROM users WHERE id IN ({ids})')

print(f'\nAdmin HTTP e2e: {passes} passed, {len(fails)} failed')
sys.exit(1 if fails else 0)
