"""Normalised HTML snapshot of every sitemap URL, jurisdiction/noindex pages and portal pages, for before/after comparison.

    python3 tests/staging/page_snapshot.py BASE_URL OUT_DIR CODE_ROOT tests/staging/mksession.php

Run before and after a deployment, then: diff -rq before/ after/ (nonces, CSRF tokens and ?v= asset versions are normalised).
Portal pages need the portal users customer@/partner@/employee@/hr@stg.invalid on the staging database.
"""
import re, sys, os, json, urllib.request, urllib.error, subprocess
base, out, root = sys.argv[1], sys.argv[2], sys.argv[3]
os.makedirs(out, exist_ok=True)
def get(path, sid=None):
    h = {'Cookie': 'paynancial_session=' + sid} if sid else {}
    class NR(urllib.request.HTTPRedirectHandler):
        def redirect_request(self, *a, **k): return None
    try:
        r = urllib.request.build_opener(NR).open(urllib.request.Request(base + path, headers=h), timeout=60); return r.status, r.read().decode('utf-8', 'ignore')
    except urllib.error.HTTPError as e: return e.code, e.read().decode('utf-8', 'ignore')
def norm(h):
    h = re.sub(r'nonce="[^"]+"', 'nonce=""', h); h = re.sub(r'nonce-[A-Za-z0-9+/=]+', 'nonce-X', h)
    h = re.sub(r'name="csrf_token" value="[^"]+"', '', h); h = re.sub(r"csrf_token: '[^']+'", '', h)
    h = re.sub(r'\?v=\d+', '?v=', h); h = re.sub(r'content="[a-f0-9]{64}"', '', h)
    return h
c, sm = get('/sitemap.php')
paths = [re.sub(r'^https?://[^/]+', '', u) or '/' for u in re.findall(r'<loc>([^<]+)</loc>', sm)]
extra = ['/business-services/jurisdictions/uae', '/business-services/jurisdictions/singapore', '/business-services/jurisdictions/united-kingdom', '/business-services/jurisdictions/hong-kong',
         '/grievance-redressal', '/signup', '/login', '/blog/category/regulatory', '/products/payment-pages', '/robots.txt', '/sitemap.php', '/this-does-not-exist']
res = {}
for p in paths + extra:
    c, h = get(p); res[p] = c
    open(os.path.join(out, (p.strip('/').replace('/', '__') or 'home') + '.html'), 'w').write(norm(h))
portals = {'customer': '/customer/dashboard', 'partner': '/partner/dashboard', 'employee': '/employee/dashboard', 'hr': '/hrms/dashboard'}
for role, path in portals.items():
    for pg in [path] + {'customer': ['/customer/profile', '/customer/transactions'], 'partner': ['/partner/customers', '/partner/products', '/partner/profile'], 'employee': ['/employee/tasks', '/employee/profile'], 'hr': ['/hrms/employees']}[role]:
        sid = subprocess.run(['php', sys.argv[4], root, f'{role}@stg.invalid'], capture_output=True, text=True).stdout.strip()
        c, h = get(pg, sid); res[pg] = c
        open(os.path.join(out, 'PORTAL' + pg.replace('/', '__') + '.html'), 'w').write(norm(h))
json.dump(res, open(os.path.join(out, '_status.json'), 'w'), indent=1)
print(len(res), 'pages;', sum(1 for v in res.values() if v == 200), '×200;', {k: v for k, v in res.items() if v not in (200,)})
