# Phase 1: staging rehearsal and staging runbook (27 Sep 2026)

Commit under test: **`c238a25`** (branch `claude/laughing-clarke-u06pou`). Commits after it add tests and docs only; no application code changed.

## Status

| Gate | Result |
|---|---|
| Real staging environment | **Not available.** No staging host exists in the repository, and none is reachable from this session: `staging.paynancial.com`, `cms.paynancial.com` and `paynancial.com` were all denied by the network policy. No staging credentials exist. |
| Staging rehearsal (isolated copy, exact commit) | **PASS**, with no blocking findings |
| STAGING VERIFIED | **NO**: requires the real staging environment |
| PRODUCTION AUTHORIZED | **NO** |

**What "rehearsal" means here:**
- The exact commit was checked out in a separate worktree.
- It ran against a fresh MariaDB 10.11 database built from the repository schemas, as production is assumed to look before the 25 Sep migration.
- The database was populated with existing-style data: users in all 6 old roles, a customer with a login and KYC documents, a partner with documents, partner and customer applications with documents, employee documents, enquiries including a stored XSS probe, notifications and legacy audit rows.
- Every step of the approved staging process then ran against that database with PHP's built-in server.

**What it cannot prove:**
- Apache or `.htaccess` behaviour.
- HTTPS and secure cookies.
- The real mail transport (OTP emails).
- OPcache and `expose_php` on the production host.
- Turnstile with real keys.
- Production data volume.
- Behaviour on the real hosting stack (PHP-FPM or mod_php, and MySQL rather than MariaDB).

## Process results

| # | Step | Result |
|---|---|---|
| 1 | Full backup (`mysqldump --single-transaction`), plus row counts and checksums for all 67 tables | PASS |
| 2 | Check the 25 Sep CMS migration | Not applied (`blog_posts.published_json` absent) |
| 3 | Run the 25 Sep CMS migration, import the 16 file articles, create one article in "approved" and publish a homepage hero through the real workflow | PASS |
| 4 | Run `2026-09-28-phase1-admin-platform.sql` | PASS (0.02 s) |
| 5 | Deploy exact `c238a25` (clean worktree, `git status` clean) | PASS |
| 6 | Cache: fresh PHP process (OPcache empty); no application page cache exists | PASS |
| 7 | Health checks (20) | PASS: every state is backed by evidence (see below) |
| 8 | Full QA | PASS (see below) |
| 9 | Rollback on disposable copies | PASS |
| 10 | Sign-off report | This document |

## Migration

- **Added:** 10 staff roles (16 roles in total), 19 permissions (41 in total), default grants, the `user_preferences` table, `audit_logs.actor_role` and `user_agent`, a nullable `customers.user_id` plus 5 contact columns, and the `admin_documents_v` view.
- **Existing grants:** none of the 43 were lost. Existing role ids and slugs are unchanged. The only data edit is the `admin` role's display name, now "Administrator".
- **Existing values:** unchanged for `audit_logs`, `customers`, `roles`, `users`, `enquiries`, `blog_posts` and `cms_pages`. This was verified by an MD5 over each row's original columns, comparing the pre-migration backup with the migrated database.
- **CMS:** blog and page rows, published snapshot hashes and approval dates are identical. The approved article stayed approved and the published hero stayed live.
- **No destructive changes:** the only change to an existing column is `customers.user_id` from NOT NULL to NULL (approved as D4). No duplicate roles or permissions.
- **Accidental re-run:** it fails at `Duplicate column 'actor_role'` and creates no duplicate objects. **Finding:** the statements before that point re-insert default role grants, so a grant a super admin removed after the first run would come back. The runbook check below prevents this.

## Rollback (disposable copies)

| Path | Result |
|---|---|
| Rollback script on a copy of the migrated database | Schema **and** every table's rows and checksums identical to the pre-Phase-1 backup |
| Rollback with a login-less CRM customer present | Blocked at the first statement; **nothing** changed (roles, permissions, preferences table and the customer all kept) |
| Full restore from the pre-Phase-1 dump | Reproduces the pre-Phase-1 snapshot exactly |
| Re-apply after rollback | Reproduces the migrated schema |

## RBAC: 12 roles, real logins

Each role signs in through the real staff login: password, then OTP, then session. The test sets a known OTP hash because it cannot read email. Each role is then checked for:
- sidebar;
- all **29 guarded routes** by direct URL (28 registered admin routes plus `/super-admin/dashboard`);
- form submissions: enquiry status, enquiry create, CSV export, customer create, anti-spam settings, role assignment, CMS approve, CMS publish, product update and partner-application decision;
- a POST without a CSRF token;
- logout.

**528/528 checks passed.**

| Role | Routes allowed / denied | Actions allowed |
|---|---|---|
| Super Admin | 28 / 0 | all |
| Administrator | 28 / 0 | enquiries (manage, create, export), customer create, products, partners |
| Operations Manager | 13 / 15 | enquiries (manage, create, export), customer create |
| Sales Manager | 12 / 16 | enquiries (manage, create, export), customer create |
| Consultant | 10 / 18 | enquiry create, customer create |
| Incorporation Consultant | 10 / 18 | — (view only) |
| Content Manager | 13 / 15 | — (CMS author and editorial review; no publish) |
| SEO Manager | 13 / 15 | — (SEO review and indexing flags) |
| Legal / Compliance Reviewer | 18 / 10 | CMS approve |
| Finance Manager | 12 / 16 | — (transactions view and export) |
| Developer | 7 / 21 | — (System Health) |
| Support | 9 / 19 | enquiry manage and create |

Settings changes, role assignment and publishing are **super admin only**. Every denial is a real 403 on a direct request, not a hidden menu item.

## Security (56/56 probes in the rehearsal; the staging suite has 55)

The staging and emulation suite (`tests/staging/security_probes.py`) contains **55 probes**. The rehearsal's 56th probe (a suspended super admin) was removed on purpose: it depended on a rehearsal-only account. Do not claim 56 for staging.

- **Stored XSS:** an enquiry containing a script payload is escaped on Enquiries, the Dashboard, Search and Activity.
- **SQL injection:** 17 probes against sort, direction, page size, page, filter, date, route and widget-key parameters. There were no errors and no data change, and no password hash leaks through search.
- **CSV formula injection:** neutralised.
- **Session:** the cookie is `HttpOnly; SameSite=Lax`, and the session id is regenerated at login. The pre-login id is not authenticated, and the id is destroyed at logout.
- **Login:** lockout after repeated failures; failed logins are audited with a masked identifier and no password.
- **Code exposure:** none for `.php`, config, tests, tools, SQL or uploads paths.
- **Privilege escalation:** blocked for all attempts:
  - an administrator editing the Super Admin role, promoting themselves or promoting a peer;
  - an administrator self-granting CMS publish;
  - a super admin granting admin permissions to a non-staff role through a tampered request;
  - assigning a non-staff role.

## Logout CSRF assessment (pre-existing, not modified)

`POST /api/auth/logout` has no CSRF token. This was tested in a real browser from a cross-site attacker page on a different site:
- **The cookie was not sent** on the cross-site requests (SameSite=Lax works).
- The victim was **still signed out**. The cookie-less cross-site request started a fresh session, and its `Set-Cookie` replaced the victim's cookie.
- **The same happens with a cross-site POST to `/contact`**, an ordinary public page. So adding a token to logout alone would not stop forced sign-out.
- **Impact:** a nuisance sign-out only. There is no data change and no session takeover (the victim's server-side session is not exposed).

**Classification: B, defer with a documented low-risk exception.**
- A complete fix means changing how sessions start on cookie-less requests, for example not issuing a new session cookie for cross-site POSTs. That is session-architecture work across the public site and every portal (category C).
- Recommended for a later hardening phase, together with logout being POST-only with a token.

## Audit

The rehearsal wrote 331 audit rows for the RBAC run alone.
- **Logged:** `auth.login` (with actor role), `auth.login_failed`, `auth.logout`, `access.denied`, `admin.request` for every admin POST, `enquiry.updated` / `created` / `bulk_updated`, `enquiries.exported`, `customer.created`, `role.changed`, `permission.granted` / `revoked`, `settings.changed` and all `cms.*` workflow actions.
- **Values:** old and new values are recorded, with secrets redacted.

## Dashboard and data honesty

- **No fabricated values:** none of 99.98, 28,450, "All Systems Operational" or "Pigeline" appears. Traffic, conversions, SEO and top pages say "Analytics / Search Console not connected". Revenue says "No payment processor or ledger". Incorporation says "Phase 5". Uptime is "Not connected" and last deployment is "Status unavailable".
- **Failed query:** with `enquiries` renamed on the live rehearsal database, New Leads, the Enquiries chart, the Leads Pipeline and Recent Enquiries show **"Unable to load this metric"** with Retry, and the widget endpoint returns `state: error` with no number. The dashboard recovered when the table was restored.

## System Health (evidence-backed)

| State | Checks |
|---|---|
| Operational | runtime, error display, database (0.8 ms), migrations (4/4), upload folders, audit logging |
| Warning | disk 12% free, no local mail transport, cookies not HTTPS-only (rehearsal is HTTP), `expose_php` on, OPcache off |
| Not connected | Turnstile (no keys in the rehearsal environment), external security monitoring, page/CDN cache, queue, uptime, analytics, Search Console, Cloudflare, payment processor |

Email is never shown as operational, and there is no uptime figure.

**Cosmetic finding:** for `APP_ENV=staging` the error-display and cookie checks describe the environment as "Development". This is wording only; the states are correct.

## Regression

- **Pages:** 123 URLs, identical HTML before and after Phase 1, with nonces, CSRF tokens and asset versions normalised:
  - the 99 sitemap URLs (16 blog articles, hub and topics);
  - 4 jurisdiction pages;
  - noindex pages, `robots.txt`, `sitemap`, the 404 page;
  - 11 portal pages across the customer, partner, employee and HR portals.

  Status codes are identical for all 123.
- **`live_qa.py`:** 488/488 PASS. **SEO crawl:** 138 pages, 99 indexable, 99 in sitemap, no broken links, no duplicate titles or descriptions.
- **Suites:**

  | Suite | Result |
  |---|---|
  | `admin-platform-test` | 90/90 |
  | Admin HTTP end-to-end | 801/801 |
  | `cms-test` | pass |
  | Blog gate | pass |
  | Jurisdiction gate | pass |
  | Anti-spam unit | pass |
  | Anti-spam API | 41/41 |
  | PHP lint | 201 files, 0 failures |

## Responsive, accessibility and performance

- **Responsive:** 1440, 1366, 1280, 1024, 768 and 390 px.
  - No page overflow and no JavaScript errors.
  - The row action sits 45 px inside the card edge from 1280 px up; at 1024 px and below the table scrolls within its card.
  - KPIs never overlap: 4 columns at 1440 and 1366, 2 at 1280 to 768, 1 on phones.
  - Five quick actions (three plus "Show all" on phones).
  - The rail sits beside the content down to 1280 px.
  - The sidebar is an off-canvas drawer below 1024 px. The palette and mobile navigation were captured.
- **Accessibility:** an automated WCAG 2.2 AA scan (axe) of 12 pages at 1440 and 390 px found 0 violations. Keyboard checks passed 15/15: skip link, ⌘K palette, tabs, hero and sidebar collapse, menus and Escape, mobile menu, focus visible.
- **Performance (local):**
  - Public pages have the same median time before and after (2.4–5.8 ms).
  - Admin pages take 3.5–7.4 ms (dashboard p95 8.8 ms).
  - Admin assets are about 8 KB of CSS and 5 KB of JS, gzipped.

## Remaining risks (none blocking)

1. Not validated on the real hosting stack, with real mail, HTTPS, Apache `.htaccess` (uploads directory) and a MySQL 8 engine.
2. Re-running the migration re-adds removed default grants (run-once check in the runbook below).
3. Logout / forced sign-out: category B, deferred.
4. The login rate limiter is per session. The database lockout per identifier is the effective control; this is pre-existing.
5. The product update form replaces the whole row, so a partial POST blanks optional fields. The UI always posts the full form; this is pre-existing.
6. Code and migration must deploy together; otherwise non-super-admin staff get 403.

## Real staging runbook (for whoever has staging access)

**Use `tests/staging/run_gate.sh`.** It runs the 17 steps below in this order and writes the final gate table (`gate-results.md`). See "One-command gate" at the end of this document for how to run it.

**BEFORE snapshot is taken immediately after backup and before migrations, deployment, or fixture creation.**

**Fixtures are temporary and must be removed before the AFTER snapshot.** The BEFORE and AFTER snapshots therefore compare a clean staging baseline with a clean staging state after deployment.

| # | Step | What it does |
|---|---|---|
| 1 | Backup | Database dump and code archive. |
| 2 | BEFORE snapshot | No migration, no deployment, no fixtures and no data change before it. Records page count, HTTP status, title, canonical, robots meta, a content fingerprint, a markup fingerprint and the broken-link state of every public page. |
| 3 | Run-once migration check | `dashboard.view` must not exist. If it does, the gate **stops**, unless the runbook for that environment declares it already migrated (`ALREADY_MIGRATED=1`). The migration is never re-run. |
| 4 | 25 Sep CMS migration | Runs only if `blog_posts.published_json` is missing. |
| 5 | Phase 1 migration | Applied once, then verified as 10 roles / 19 permissions / 2 objects / 3 columns. If verification fails, the gate stops. |
| 6 | Create / verify staging fixtures | Idempotent. One account for each of the 12 staff roles, plus portal accounts, customer, KYC document row, partner, employee, enquiries (RBAC and XSS), partner application, an inactive product and a CMS article. Every fixture is identifiable by `@stg.invalid`, `-STG-`, "STG Fixture", `stg-fixture` or `staging-*`. No rehearsal-only data is needed. |
| 7 | Deploy Phase 1 code | Uses `DEPLOY_CMD`, or pauses for a manual deployment. Either way it must reload PHP-FPM and clear caches. The gate then verifies the code version: every application file must match `c238a25`, and the web root must serve the Phase 1 assets. |
| 8 | Hosting checks | HTTPS, Secure cookie, Apache, uploads `.htaccess`, PHP version exposure, OPcache, MySQL engine and version, Turnstile, System Health. A check that is not satisfied on the real host is FAIL or NOT VERIFIED, never PASS. |
| 9 | `live_qa.py` | |
| 10 | `rbac_matrix.py` | 12 roles, 29/29 guarded routes. |
| 11 | `security_probes.py` | 55 probes. |
| 12 | 12 real email-OTP sign-ins | People sign in to the actual staging application with a real staff account and the emailed OTP, one per role (listed below). PASS / FAIL is recorded per role. An operator PASS counts only when the staging audit log has an `auth.login` row with that role for that real account after the gate started. No session-file injection is used, and fixture accounts do not count. The same evidence decides the email-transport row. |
| 13 | Clean up staging fixtures | Removes every fixture and test-created record, then **verifies** nothing is left: test accounts, test enquiries, staging-only customers, partners, employees, applications, KYC rows, CMS articles, the product and test login attempts. If anything remains, the gate stops before the AFTER snapshot. |
| 14 | AFTER snapshot | The clean state after deployment. |
| 15 | BEFORE/AFTER comparison | `snapshot_compare.py` reports pages added and removed, and changes to HTTP status, title, canonical, robots meta, public content, markup and newly broken links. Any change is FAIL. |
| 16 | Rollback drill | Runs only on restored, disposable copies (`<db>_gate_pre`, `<db>_gate_rb`, `<db>_gate_cur`), never the staging database. See the two drills below. |
| 17 | Final gate table | Status is exactly **PRODUCTION NOT READY** or **READY FOR PRODUCTION AUTHORIZATION**. The second is given only when every required row is PASS. |

The 12 roles for step 12 are: Super Admin, Administrator, Operations Manager, Sales Manager, Consultant, Incorporation Consultant, Content Manager, SEO Manager, Legal / Compliance Reviewer, Finance Manager, Developer and Support.

**Rollback drills (step 16):**
- **Exact drill:** a copy restored from before the Phase 1 migration has the migration applied and then rolled back. The result must equal the pre-Phase-1 schema and data exactly (every table's rows and checksum). The migration is then re-applied, and the schema must equal the live one.
- **Current-state drill:** a copy of staging as it is after the gate is rolled back. The Phase 1 objects must be gone, and these must be unchanged:
  - users;
  - customers (core columns);
  - CMS (`blog_posts`);
  - the roles held by users;
  - every other table outside Phase 1.

  Every user must keep a valid role, and the original 6 roles must be present. The migration is then re-applied. If customers without a login exist, the rollback must be blocked with nothing changed, as documented.

**When the gate stops early,** the fixtures are still removed (and verified), and the gate table is still written. Rows that were never reached show as NOT VERIFIED.

**Production safety:** the driver refuses `paynancial.com`, `www.paynancial.com`, `crm.paynancial.com`, any hostname in `PRODUCTION_HOSTS`, and any code root whose config has `APP_ENV=production`. Production deployment is outside this script.

## Staging execution gate: attempt of 27 Sep 2026 (BLOCKED)

**Guarded routes: 29** (28 registered admin routes plus `/super-admin/dashboard`). `tests/staging/rbac_matrix.py` now checks all 29 for every role, and passes 552/552 on the rehearsal copy of `c238a25`.

The real staging run **could not start**:
- **No access:** `staging.paynancial.com`, `stage.paynancial.com`, `cms.paynancial.com` and `paynancial.com` are all refused by this session's network policy.
- **No credentials:** no staging host, SSH, SFTP, cPanel or database credentials exist in this environment or the repository.
- **Nothing run on staging:** none of the deployment-order steps (backup → run-once check → 25 Sep migration → Phase 1 migration → code → OPcache → QA) was executed on staging. Nothing was deployed anywhere.

| Production gate item | Status |
|---|---|
| 29/29 guarded routes validated | Rehearsal only; **NOT VERIFIED on staging** |
| Migrations validated | Rehearsal only; **NOT VERIFIED on staging** |
| Rollback drill passed | Rehearsal only; **NOT VERIFIED on staging** |
| 12 real email-OTP sign-ins | **NOT VERIFIED** (rehearsal used real OTP verification with a test-set code; no email delivery) |
| RBAC matrix passed | Rehearsal only; **NOT VERIFIED on staging** |
| Security probes passed | Rehearsal only; **NOT VERIFIED on staging** |
| Live QA passed | Rehearsal only; **NOT VERIFIED on staging** |
| Before/after snapshot, no public regression | Rehearsal only; **NOT VERIFIED on staging** |
| Hosting-stack checks | **NOT VERIFIED** (see below) |
| No critical security defect remains | None found in rehearsal; staging not tested |
| Backups and rollback ready | Procedure written and rehearsed; staging backup **NOT VERIFIED** |

| Hosting-stack check | Status |
|---|---|
| Apache configuration | NOT VERIFIED (rehearsal used PHP's built-in server) |
| Uploads `.htaccess` behaviour | NOT VERIFIED (the built-in server ignores `.htaccess`) |
| HTTPS | NOT VERIFIED |
| Secure cookie behaviour | NOT VERIFIED (rehearsal was HTTP with `SESSION_COOKIE_SECURE=false`) |
| Real email OTP delivery | NOT VERIFIED (no mail transport in this container) |
| OPcache | NOT VERIFIED (off in the rehearsal) |
| PHP version exposure | NOT VERIFIED (`expose_php=On` in the rehearsal) |
| Turnstile configuration | NOT VERIFIED (no real keys) |
| MySQL 8 compatibility | NOT VERIFIED (rehearsal used MariaDB 10.11) |

**Logout CSRF:** pre-existing, non-blocking, later hardening. Session initialisation is not changed in Phase 1.

**Status: PRODUCTION NOT READY.**

**To proceed, either:**
- a person with staging access runs the runbook above and the four scripts, and records the results in this section; or
- staging hosts and credentials are made reachable from this environment, and I run them here.

## One-command gate (`tests/staging/run_gate.sh`)

Run it **on the staging host**. The session helpers write PHP session files that the staging web server has to read.

    STAGING_URL=https://<staging> CODE_ROOT=/path/to/deployed/code DB_NAME=<db> \
    PYN_MYSQL="mysql -h … -u … -p… <db>" \
    PYN_MYSQLDUMP="mysqldump -h … -u … -p… --single-transaction --routines --triggers <db>" \
    PYN_MYSQL_ADMIN="mysql -h … -u … -p…" \
    DEPLOY_CMD="<deploys c238a25 to CODE_ROOT, reloads PHP-FPM, clears caches>" \
    OTP_RESULTS=otp.txt \
    bash tests/staging/run_gate.sh

**Requirements on the staging side:**
- the database user can create and drop `<db>_gate_pre`, `<db>_gate_rb` and `<db>_gate_cur`;
- the CLI PHP and the web PHP share the session save path;
- 12 real staff accounts exist, one per role, with mailboxes the testers can read;
- `RELEASE_DIR` is a git checkout that contains `c238a25` (by default, this repository).

**`OTP_RESULTS`** has one line per role: `role PASS|FAIL|NOT_VERIFIED email note`. Without it, the script asks at the terminal.

**Evidence** goes to `OUT_DIR`: the backup, both snapshots with their manifests, `snapshot-compare.txt`, the QA outputs, `fixture-cleanup.txt`, the rollback-drill diffs, `gate.log` and `gate-results.md`.

### Local emulation of the corrected gate (27 Sep 2026), not staging

The corrected driver was run end to end in this session's container:
- **Code:** a separate worktree, starting at the pre-Phase-1 code (`705d8f5`), then deployed to `c238a25` by `DEPLOY_CMD`.
- **Database:** MariaDB 10.11, restored from the pre-CMS rehearsal backup.
- **Server:** PHP's built-in server over plain HTTP.

This proves the driver and its order. It does **not** verify staging.

| Item | Result (emulation) |
|---|---|
| Backup | PASS (dump of 67 tables + code archive) |
| BEFORE snapshot | PASS (111 public pages before any change: 110 × 200 and 1 intended 404; 238 internal links, 0 broken) |
| Run-once check | PASS |
| 25 Sep CMS migration | PASS (applied) |
| Phase 1 migration | PASS (10 / 19 / 2 / 3) |
| Staging fixtures | PASS (16 fixture groups verified) |
| Deploy Phase 1 code | PASS (233/233 application files match `c238a25`; admin assets served) |
| System Health | PASS |
| OPcache | PASS |
| live_qa.py | PASS (488 / 488) |
| RBAC matrix (12 roles, 29/29 routes) | PASS (552 / 552) |
| Security probes | PASS (55 / 55) |
| Fixture cleanup | PASS (0 left in every category) |
| AFTER snapshot | PASS (111 pages, clean) |
| BEFORE/AFTER comparison | PASS (111 pages identical, with nothing added, removed or changed in status, title, canonical, robots, content or markup, and no newly broken links)¹ |
| Rollback drill (exact) | PASS |
| Rollback drill (current state) | PASS (61 other tables identical; users, customers, CMS and roles unchanged) |
| Rollback readiness | PASS |
| Secure cookie | **Environment-specific FAIL** (plain HTTP) |
| PHP version exposure | **Environment-specific FAIL** (the local PHP sends `X-Powered-By`) |
| Uploads `.htaccess` | **Environment-specific FAIL** (the built-in server ignores `.htaccess`) |
| HTTPS | NOT VERIFIED |
| Apache | NOT VERIFIED |
| MySQL 8 | NOT VERIFIED (MariaDB 10.11) |
| Turnstile | NOT VERIFIED (no real keys) |
| Real email OTP (email transport) | NOT VERIFIED |
| 12 real email-OTP sign-ins | NOT VERIFIED (0/12) |

¹ The earlier emulation compared 124 pages. That count included 13 portal pages rendered for fixture accounts. The clean BEFORE snapshot is taken before any fixture exists, so portal pages are captured only when `SNAPSHOT_PORTAL_ACCOUNTS` names real staging portal accounts.

The environment-specific FAIL rows must PASS on real staging. None of the NOT VERIFIED rows is converted to PASS.

**Status: PRODUCTION NOT READY** (real staging has not been executed).
