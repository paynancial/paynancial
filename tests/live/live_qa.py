#!/usr/bin/env python3
"""
Paynancial — read-only live QA against a deployed site.

    python3 tests/live/live_qa.py                       # https://paynancial.com
    python3 tests/live/live_qa.py https://staging.example.com

Only sends GET requests. Never submits forms, never logs in, never writes.
Exit code 0 = all checks passed, 1 = at least one failure.

Checks (release of 24 Sep 2026):
  - sitemap.xml: 99 URLs, each 200 and indexable, canonical = itself
  - crawl from the homepage: no broken internal links, no 5xx
  - 13 Business Services service pages: 200, no PHP warning text
  - blog: 16 articles + /blog + 5 topics indexable and in the sitemap;
    Regulatory Insights noindex and not in the sitemap
  - 15 foreign jurisdictions: noindex, not in sitemap, research pending,
    no Indian regulatory sections, no promotional CTA
  - regulatory disclaimer present on sample pages; no "Last verified"
  - account/utility pages noindex; 404 returns 404 without canonical
  - /partners 301 -> /partner-program
  - Turnstile script not loaded in initial HTML; no debug output
"""
import re
import sys
import urllib.error
import urllib.request
from urllib.parse import urljoin, urlparse

BASE = (sys.argv[1] if len(sys.argv) > 1 else "https://paynancial.com").rstrip("/")
HOST = urlparse(BASE).netloc
UA = {"User-Agent": "Paynancial-LiveQA/1.0 (read-only)"}

BLOG = ["agentic-ai-in-finance-human-control", "ai-in-finance-operations", "cash-flow-basics-for-growing-businesses",
        "choosing-how-to-get-paid-online", "how-online-card-payments-work", "idempotency-keys-payment-apis",
        "payment-integration-go-live-checklist", "payment-reconciliation-explained", "payment-security-basics-small-business",
        "payment-settlement-explained", "payments-glossary", "reduce-failed-payments", "reduce-overdue-invoices",
        "refunds-vs-chargebacks", "reliable-webhook-handler", "vendor-payout-process"]
TOPICS = ["payments", "business-finance", "fintech-ai", "developers", "learning"]
JURISDICTIONS = ["guernsey", "uae", "saint-vincent-and-the-grenadines", "saudi-arabia", "british-virgin-islands",
                 "cayman-islands", "singapore", "mauritius", "cyprus", "hong-kong", "ireland", "luxembourg",
                 "malaysia", "puerto-rico", "united-kingdom"]
SERVICES_503 = ["private-limited-company", "llp-registration", "opc-registration", "partnership-registration",
                "gst-registration", "msme-registration", "startup-registration", "pan-tan-assistance",
                "trademark-registration", "roc-compliance", "annual-compliance", "company-changes", "trademark-search"]
DISCLAIMER = "General information only. Not legal, tax, financial or regulatory advice."
DISCLAIMER_SAMPLES = ["/products/upi-payments", "/products/chargebacks", "/developers/payment-apis", "/ai-intelligence",
                      "/business-services/gst-registration", "/business-services/company-incorporation"]
NOINDEX_EXPECTED = ["/signup", "/login", "/forgot-password", "/partner/register", "/grievance-redressal",
                    "/blog/category/regulatory", "/products/payment-pages", "/ai-intelligence/fraud-detection"]
EXPECTED_SITEMAP = 99


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


OPENER = urllib.request.build_opener(NoRedirect)
_cache = {}


def get(path):
    url = path if path.startswith("http") else BASE + path
    if url in _cache:
        return _cache[url]
    req = urllib.request.Request(url, headers=UA)
    try:
        r = OPENER.open(req, timeout=30)
        res = (r.status, r.read().decode("utf-8", "ignore"), {k.lower(): v for k, v in r.headers.items()})
    except urllib.error.HTTPError as e:
        res = (e.code, e.read().decode("utf-8", "ignore"), {k.lower(): v for k, v in e.headers.items()})
    except Exception as e:  # network error
        res = (0, str(e), {})
    _cache[url] = res
    return res


def robots(html):
    m = re.search(r'<meta name="robots" content="([^"]+)"', html)
    return m.group(1) if m else ""


def canonical(html):
    m = re.search(r'<link rel="canonical" href="([^"]+)"', html)
    return m.group(1) if m else None


failures, passes = [], 0


def check(cond, msg):
    global passes
    if cond:
        passes += 1
    else:
        failures.append(msg)


SITE_HOSTS = {"", HOST, "www." + HOST, HOST.replace("www.", ""), "paynancial.com", "www.paynancial.com"}


def path_of(url):
    p = urlparse(url)
    return (p.path or "/") if p.netloc in SITE_HOSTS else None


# 1. Sitemap
code, xml, _ = get("/sitemap.xml")
if "<urlset" not in xml:  # local PHP dev server has no Apache rewrite
    code, xml, _ = get("/sitemap.php")
check(code == 200, f"sitemap.xml status {code}")
sitemap = [path_of(u) or u for u in re.findall(r"<loc>([^<]+)</loc>", xml)]
check(len(sitemap) == EXPECTED_SITEMAP, f"sitemap has {len(sitemap)} URLs (expected {EXPECTED_SITEMAP})")
for p in sitemap:
    c, h, _ = get(p)
    check(c == 200, f"sitemap URL {p} -> {c}")
    check("noindex" not in robots(h), f"sitemap URL {p} is noindex")
    can = canonical(h)
    check(can is not None and (path_of(can) or "").rstrip("/") == p.rstrip("/"), f"sitemap URL {p} canonical -> {can}")

# 2. Crawl for broken links / 5xx
seen, queue, broken = set(), ["/"], []
while queue and len(seen) < 400:
    p = queue.pop(0)
    if p in seen:
        continue
    seen.add(p)
    c, h, hdr = get(p)
    if c >= 500 or c == 0:
        broken.append((p, c))
        continue
    if c == 404:
        broken.append((p, c))
        continue
    if c == 200 and "text/html" in hdr.get("content-type", ""):
        for href in re.findall(r'href="([^"#]+)', h):
            if href.startswith(("mailto:", "tel:", "javascript:", "data:", "sms:", "whatsapp:")):
                continue
            q = path_of(urljoin(BASE + p, href.replace("&amp;", "&")))
            if q and not q.startswith(("/assets", "/api", "/admin", "/customer", "/employee", "/hrms", "/super-admin", "/partner/login")) and q not in seen:
                queue.append(q.split("?")[0])
check(not broken, f"broken / error links: {broken[:15]}")

# 3. Previously-503 Business Services pages
for s in SERVICES_503:
    c, h, _ = get(f"/business-services/{s}")
    check(c == 200, f"/business-services/{s} -> {c}")
    check(not re.search(r"Warning:|Notice:|Fatal error|Deprecated:|Uncaught|Stack trace", h), f"/business-services/{s} shows PHP error text")

# 4. Blog
for s in BLOG:
    c, h, _ = get(f"/blog/{s}")
    check(c == 200 and "noindex" not in robots(h), f"/blog/{s} not indexable ({c})")
    check(f"/blog/{s}" in sitemap, f"/blog/{s} missing from sitemap")
for t in TOPICS:
    c, h, _ = get(f"/blog/category/{t}")
    check(c == 200 and "noindex" not in robots(h) and f"/blog/category/{t}" in sitemap, f"topic {t} not indexable / not in sitemap")
c, h, _ = get("/blog")
check(c == 200 and "noindex" not in robots(h) and "/blog" in sitemap, "/blog not indexable / not in sitemap")
check("/blog/category/regulatory" not in sitemap, "Regulatory Insights topic is in sitemap")

# 5. Foreign jurisdictions
for j in JURISDICTIONS:
    p = f"/business-services/jurisdictions/{j}"
    c, h, _ = get(p)
    main = h[h.find("<main"):h.find("</main>")]
    check(c == 200, f"{p} -> {c}")
    check("noindex" in robots(h), f"{p} is indexable")
    check(p not in sitemap, f"{p} is in sitemap")
    check(main.count("source verification pending") >= 10, f"{p} research topics not pending")
    check('id="india"' not in h and 'id="regulatory"' not in h and DISCLAIMER not in h, f"{p} shows Indian regulatory content")
    check(not re.search(r"Get Started|Start Incorporation|Get a Quote", main), f"{p} has a promotional CTA")

# 6. Regulatory disclaimer and labels
for p in DISCLAIMER_SAMPLES:
    c, h, _ = get(p)
    check(DISCLAIMER in h, f"{p} missing regulatory disclaimer")
    check(not re.search(r"Last verified|Government Approved|Regulator Approved", h, re.I), f"{p} shows a verification label")

# 7. Noindex utility pages, 404, redirects, robots.txt
for p in NOINDEX_EXPECTED:
    c, h, _ = get(p)
    check("noindex" in robots(h), f"{p} should be noindex")
    check(p not in sitemap, f"{p} should not be in sitemap")
c, h, _ = get("/this-page-does-not-exist-qa")
check(c == 404 and canonical(h) is None and "noindex" in robots(h), f"404 handling (status {c}, canonical {canonical(h)})")
c, h, hdr = get("/partners")
loc = hdr.get("location", "")
check(c == 301 and loc.rstrip("/").endswith("/partner-program"), f"/partners -> {c} {loc}")
c, txt, _ = get("/robots.txt")
check(c == 200 and "Sitemap:" in txt and "Disallow: /blog" not in txt, "robots.txt")

# 8. Turnstile lazy-load, debug output
c, h, _ = get("/")
check(not re.search(r'<script[^>]+src="[^"]*challenges\.cloudflare\.com', h), "Turnstile script loaded in initial homepage HTML")
check("TURNSTILE_SECRET" not in h and not re.search(r"<pre[^>]*>.*Stack trace", h, re.S), "secret or debug output in homepage")

print(f"\nPaynancial live QA — {BASE}")
print(f"sitemap URLs: {len(sitemap)} | pages crawled: {len(seen)} | checks passed: {passes} | failed: {len(failures)}")
for f in failures:
    print("  FAIL", f)
print("\nRESULT:", "PASS" if not failures else "FAIL")
sys.exit(1 if failures else 0)
