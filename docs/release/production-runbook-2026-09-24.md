# Paynancial — production release runbook (24 Sep 2026)

Release: branch `claude/laughing-clarke-u06pou`, package `paynancial-changed-files.zip`.
Architecture, SEO and governance are frozen — this runbook deploys and validates only.

Pre-deployment validation (local build, 24 Sep 2026): all passed — see
"Pre-deployment results" at the end. **Production has not been validated yet.**

---

## 0. Stop conditions (stop and roll back / report)
HTTP 500/503 · broken homepage or navigation · payment, login or dashboard
failure · exposed secret · database error · callback form accepting
submissions without protection · any foreign jurisdiction indexable ·
approved blog page noindex · noindex page in sitemap · canonical conflict ·
major visual regression.

## 1. Backup (mandatory — do not continue without it)
1. Download a full copy of the site files (including `storage/uploads`).
2. Download `config/config.php` separately and keep it safe.
3. Export the database (phpMyAdmin → Export → Custom → all tables, or `mysqldump`).
4. Confirm the backup files open / are non-empty.

## 2. Deploy files
1. Upload `paynancial-changed-files.zip` to the site root.
2. **Extract over the existing files** (overwrite). Do NOT delete the folder
   first; do NOT use the full-site zip unless extracting over the top.
3. Confirm `config/config.php` is still present and unchanged (the zips never
   contain it). If the site shows "Configuration missing", restore it from step 1.

## 3. Remove the obsolete file
Delete `pages/partners.php` **if it exists on the server**.
Reference audit (24 Sep 2026): no include, route, header/footer link, JS,
form, CMS or sitemap reference. `/partners` is redirected (301) to
`/partner-program` in `public/index.php` before any page file is loaded.
(`pages/header.php` is a dormant legacy file — unreachable, not included; left in place.)

## 4. Database (after the backup in step 1)
1. **Anti-spam** — run `database/anti_spam_schema.sql` once (creates
   `anti_spam_hits`). Required for the database rate limiter; without it the
   file-store fallback is used (still protected, never unrestricted).
2. **Content governance / regulatory migration** — only if the
   `regulatory_references` table already exists in production:
   run `database/migrations/2026-09-24-regulatory-reference-workflow.sql`.
   If the table does not exist, skip it (the site reads governance from code).
3. Do not run any other SQL.

## 5. Turnstile (server environment only)
Set on the server (cPanel → "PHP environment variables", Apache `SetEnv` in
the vhost, or PHP-FPM `env[...]`) — never in code, config.php or the CMS:

    TURNSTILE_SITE_KEY=<site key from Cloudflare>
    TURNSTILE_SECRET_KEY=<secret key from Cloudflare>

Then in Admin → Settings → Communication → Floating Enquiry → Anti-Spam:
"Site key: Configured", "Secret key: Configured (hidden)", Active + CAPTCHA enabled.
Until both keys exist the callback form stays hidden (WhatsApp / Email / Call still work).

## 6. Cache
Purge the application/server cache (if any) and Cloudflare cache
(Caching → Purge Everything, or purge the changed CSS/JS files and HTML).

## 7. Live QA (read-only script)
From any machine with Python 3:

    python3 tests/live/live_qa.py https://paynancial.com

Expected: `sitemap URLs: 99` … `RESULT: PASS`. It checks sitemap (99, all 200 +
indexable + self-canonical), broken links, the 13 Business Services pages,
blog (16 + /blog + 5 topics), all 15 foreign jurisdictions (noindex, not in
sitemap, research pending, no Indian regulatory content, no promo CTA),
regulatory disclaimers, noindex utility pages, 404 handling, /partners 301,
robots.txt, Turnstile not loaded on page load, no debug output.

## 8. Manual live checks
- Desktop + phone: homepage, each header menu, footer, a product page, a
  Business Services page, /blog, /contact, /partner-program, /legal.
- Floating widget: WhatsApp / Email / Call open directly (no CAPTCHA).
  "Request a Callback": send one real test enquiry → it appears in Admin →
  Enquiries and the notification email arrives. Try an invalid email → error.
- Login / Sign Up opens; a test customer login and dashboard still work;
  no payment function has changed in this release.
- Admin → Content Governance shows "Regulatory verification gate: Disabled"
  with the editorial-decision wording.
- Response headers: consider `expose_php = Off` (hides `X-Powered-By: PHP/x`).

Do NOT test the Turnstile-outage fallback or rate-limit exhaustion on
production (they write test enquiries and rate-limit rows); both are covered
by the local suite (`tests/anti-spam/test_api.py`, 41/41).

## 9. Google Search Console (manual, after step 7 passes)
1. Open Search Console → property `https://paynancial.com`.
2. Sitemaps → enter `sitemap.xml` → Submit (or resubmit if listed).
3. Confirm status "Success" and "Discovered URLs: 99" (may take a day).
4. Optional: URL Inspection → Request indexing for `/blog`.
Make no other Search Console changes.

## 10. Rollback
Restore the files and `config/config.php` from step 1; restore the database
export only if step 4 was run and caused a problem. Purge cache again.

---

## Pre-deployment results (local build of this release)
- Tests: blog gate 48/48 · jurisdiction gate pass · anti-spam unit pass ·
  anti-spam API 41/41 · live_qa.py against local build 488/488.
- Crawl: 138 pages, 0 broken links, 99 indexable = 99 sitemap, 0 noindex in
  sitemap, 0 duplicate titles/descriptions, canonical + single H1 on every
  indexable page, all JSON-LD parses.
- Governance: 15/15 jurisdictions gated; 16/16 articles with approval record
  (Paynancial Editorial Team, 24 Sep 2026); 53 regulatory-context pages carry
  the disclaimer; no "Last verified"/approval labels.
- Security: no secrets in the repository; config.php untracked; Turnstile
  secret server-side only; generic error page (no stack trace); CSRF on all
  POST forms and APIs except POST /api/auth/logout (low severity, pre-existing);
  uploads validated (extension + MIME + 5 MB) and stored outside public/;
  prepared statements; mail headers from validated input.
- Accessibility: contrast passes on all 99 sitemap pages (tool false positives:
  "Contact Sales" border gradient, About timeline beside decorative line);
  widget keyboard-operable, labelled fields, Esc closes and returns focus.
- Performance: no JS errors, no render-blocking scripts, no broken images;
  Turnstile requested only when the callback form is opened.
