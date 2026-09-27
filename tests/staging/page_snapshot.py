"""Snapshot of the public site (plus optional portal pages) for the gate's BEFORE / AFTER comparison.

    python3 tests/staging/page_snapshot.py BASE_URL OUT_DIR [CODE_ROOT MKSESSION_PHP]

Captures every sitemap URL plus the jurisdiction, noindex, error and robots/sitemap pages, anonymously.
For each page it records, in OUT_DIR/_manifest.json:
  - HTTP status
  - <title>, canonical URL and robots meta
  - content fingerprint: SHA-256 of the visible text (scripts and styles removed)
  - markup fingerprint: SHA-256 of the normalised HTML (nonces, CSRF tokens and ?v= versions removed)
  - internal links, each checked once; broken = 4xx/5xx or no response (OUT_DIR/_links.json)
The normalised HTML of each page is kept for `diff -r`. Compare two snapshots with snapshot_compare.py.

Portal pages are captured only when SNAPSHOT_PORTAL_ACCOUNTS names existing accounts, e.g.
"customer=a@example.com,partner=b@example.com,employee=c@example.com,hr=d@example.com" (needs CODE_ROOT and
MKSESSION_PHP; staging only). The gate does not set it: its BEFORE snapshot runs before any fixture exists.
Read-only: no form is submitted, nothing is created.
"""
import hashlib, html as H, json, os, re, subprocess, sys, urllib.error, urllib.parse, urllib.request
base, out = sys.argv[1].rstrip('/'), sys.argv[2]
root, mks = (sys.argv[3], sys.argv[4]) if len(sys.argv) > 4 else (None, None)
os.makedirs(out, exist_ok=True)
host = urllib.parse.urlparse(base).netloc
class NR(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k): return None
OPENER = urllib.request.build_opener(NR)
def get(path, sid=None):
    h = {'Cookie': 'paynancial_session=' + sid} if sid else {}
    try:
        r = OPENER.open(urllib.request.Request(base + path, headers=h), timeout=60); return r.status, r.read().decode('utf-8', 'ignore')
    except urllib.error.HTTPError as e: return e.code, e.read().decode('utf-8', 'ignore')
    except Exception as e: return 0, ''
def norm(h):
    h = re.sub(r'nonce="[^"]+"', 'nonce=""', h); h = re.sub(r'nonce-[A-Za-z0-9+/=]+', 'nonce-X', h)
    h = re.sub(r'name="csrf_token" value="[^"]+"', '', h); h = re.sub(r"csrf_token: '[^']+'", '', h)
    h = re.sub(r'\?v=\d+', '?v=', h); h = re.sub(r'content="[a-f0-9]{64}"', '', h)
    return h
def text(h):
    h = re.sub(r'(?is)<(script|style|noscript|template)\b.*?</\1>', ' ', h); h = re.sub(r'(?s)<!--.*?-->', ' ', h)
    return re.sub(r'\s+', ' ', H.unescape(re.sub(r'<[^>]+>', ' ', h))).strip()
def meta(h, pat):
    m = re.search(pat, h, re.I | re.S); return H.unescape(m.group(1)).strip() if m else None
def links(h):
    found = set()
    for u in re.findall(r'<a\b[^>]*\bhref="([^"]+)"', h, re.I):
        u = H.unescape(u).split('#')[0]
        if not u or u.startswith(('mailto:', 'tel:', 'javascript:', '//')): continue
        p = urllib.parse.urlparse(u)
        if p.scheme and p.netloc != host: continue
        path = (p.path or '/') + ('?' + p.query if p.query else '')
        if not path.startswith('/') or path.startswith(('/api/', '/logout')): continue
        found.add(path)
    return sorted(found)
manifest = {}
def capture(key, path, fname, sid=None):
    c, h = get(path, sid); n = norm(h)
    open(os.path.join(out, fname), 'w').write(n)
    manifest[key] = {'path': path, 'status': c, 'title': meta(h, r'<title[^>]*>(.*?)</title>'),
                     'canonical': meta(h, r'<link[^>]+rel="canonical"[^>]+href="([^"]*)"'),
                     'robots': meta(h, r'<meta[^>]+name="robots"[^>]+content="([^"]*)"'),
                     'content_sha256': hashlib.sha256(text(h).encode()).hexdigest(),
                     'markup_sha256': hashlib.sha256(n.encode()).hexdigest(), 'links': links(h) if not sid else []}
c, sm = get('/sitemap.php')
paths = [re.sub(r'^https?://[^/]+', '', u) or '/' for u in re.findall(r'<loc>([^<]+)</loc>', sm)]
extra = ['/business-services/jurisdictions/uae', '/business-services/jurisdictions/singapore', '/business-services/jurisdictions/united-kingdom', '/business-services/jurisdictions/hong-kong',
         '/grievance-redressal', '/signup', '/login', '/blog/category/regulatory', '/products/payment-pages', '/robots.txt', '/sitemap.php', '/this-does-not-exist']
for p in dict.fromkeys(paths + extra):
    capture(p, p, (p.strip('/').replace('/', '__') or 'home') + '.html')
accounts = dict(kv.split('=', 1) for kv in os.environ.get('SNAPSHOT_PORTAL_ACCOUNTS', '').split(',') if '=' in kv)
portal_pages = {'customer': ['/customer/dashboard', '/customer/profile', '/customer/transactions'], 'partner': ['/partner/dashboard', '/partner/customers', '/partner/products', '/partner/profile'],
                'employee': ['/employee/dashboard', '/employee/tasks', '/employee/profile'], 'hr': ['/hrms/dashboard', '/hrms/employees']}
if accounts and not (root and mks): sys.exit('SNAPSHOT_PORTAL_ACCOUNTS needs CODE_ROOT and MKSESSION_PHP')
for role, email in accounts.items():
    sid = subprocess.run(['php', mks, root, email], capture_output=True, text=True).stdout.strip()
    for pg in portal_pages[role]: capture('PORTAL ' + pg, pg, 'PORTAL' + pg.replace('/', '__') + '.html', sid)
# broken-link check: every distinct internal link, once
status = {v['path']: v['status'] for k, v in manifest.items() if not k.startswith('PORTAL ')}
link_status = {}
for l in sorted({l for v in manifest.values() for l in v['links']}):
    link_status[l] = status[l] if l in status else get(l)[0]
for v in manifest.values():
    v['broken_links'] = [l for l in v['links'] if not (200 <= link_status[l] < 400)]
json.dump(manifest, open(os.path.join(out, '_manifest.json'), 'w'), indent=1, sort_keys=True)
json.dump(link_status, open(os.path.join(out, '_links.json'), 'w'), indent=1, sort_keys=True)
json.dump({k: v['status'] for k, v in manifest.items()}, open(os.path.join(out, '_status.json'), 'w'), indent=1)
codes = {}
for v in manifest.values(): codes[v['status']] = codes.get(v['status'], 0) + 1
broken = sorted({l for v in manifest.values() for l in v['broken_links']})
print(f"Snapshot: {len(manifest)} pages; status {dict(sorted(codes.items()))}; titles {sum(1 for v in manifest.values() if v['title'])}; "
      f"canonicals {sum(1 for v in manifest.values() if v['canonical'])}; internal links checked {len(link_status)}; broken {len(broken)} {broken[:10]}")
