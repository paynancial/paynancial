# Paynancial Enterprise Admin Platform: Phase 1

**Status: implemented and tested locally. Not deployed. Live validation pending.**
Scope: Phase 1 only (shell, design system, registry, RBAC, audit, Command Center, System Health). Nothing from Phase 2 onwards has been built.

## Architecture (inside the existing PHP + MySQL application)

| Piece | File |
|---|---|
| Module registry (routes, permissions, sidebar, palette, breadcrumbs, quick actions) | `includes/admin/registry.php` |
| Central guard (CSRF + permission before any admin page runs; every POST audited) | `public/index.php` (admin area block) |
| Permission check | `includes/permissions.php` (`user_can`, `require_permission`) |
| Audit helper | `includes/audit.php` (`audit()`, strict `audit_write()`) |
| Admin shell (admin area only) | `includes/admin/shell-head.php`, `shell-foot.php` |
| Design system | `public/assets/admin/admin.css` (tokens scoped to `body.adm`) |
| Behaviour | `public/assets/admin/admin.js` (progressive enhancement, no framework) |
| Widgets with data provenance | `includes/admin/widgets.php`, `dashboard-render.php` |
| Health checks | `includes/admin/health.php` |
| UI preferences | `includes/admin/prefs.php` (`user_preferences`) |

The customer, partner, employee and HRMS portals keep the previous shell (`includes/dashboard-head.php` and `dashboard-foot.php` branch only when `$admin_shell` is set). Public pages are unchanged.

### Adding a module (later phases)

1. Add the page file in `admin/`.
2. Add one entry in `admin_modules()` with its label, group, icon, `perm` (view) and `post` (action permission, or an op → permission map).
3. Add any new permissions through an additive migration.

The route, sidebar, palette, breadcrumbs and guard all follow from that one entry. A module appears only when it is implemented **and** the user may view it.

## Roles (12)

The 12 roles are:
- Super Admin
- Administrator (existing `admin`)
- Operations Manager
- Sales Manager
- Consultant
- Incorporation Consultant
- Content Manager
- SEO Manager
- Legal / Compliance Reviewer
- Finance Manager
- Developer
- Support

Staff sign in through the existing staff (employee) surface, with the same OTP and device checks, and land on `/admin/dashboard`. There is no Travel role.

## Permissions

The permission catalogue has 41 slugs, grouped by module:
- **Actions:** view, create, manage, export, approve, publish, plus `settings.manage`.
- **Defaults:** set in the migration. See Admin → Roles & Permissions.
- **Changes:** only people with `roles.manage` (super admin by default) can change them, and every change is audited.

Rules for role changes:
- Nobody changes their own role.
- Only a super admin grants or removes Super Admin.
- The last super admin cannot be demoted.
- A role change signs that person out everywhere.

## Data honesty

Every widget declares `source`, `perm` and `connected`, and returns a `state` (`ok`, `empty`, `error`, `not_connected` or `restricted`) together with `updated_at`.
- **Failed query:** shows "Unable to load this metric", with a Retry button, and the error is logged. It never shows 0.
- **Not connected:** analytics, Search Console, Cloudflare, the payment processor, uptime and incorporation cases all say "Not connected".
- **Health:** "All systems operational" appears only when every check is operational. A check that cannot run shows "Status unavailable".

## Audit

These actions are recorded with the user, their role at the time, IP address, user agent, time, entity, old and new values (changed fields only) and a redacted payload:
- login, failed login, logout and revoked sessions;
- access denied;
- every admin POST (central request record);
- enquiry create, update, bulk update and export;
- customer create, activate and suspend;
- KYC document status changes;
- partner application status changes;
- anti-spam settings changes;
- role and permission changes;
- all CMS workflow actions.

Passwords, tokens, secrets, OTPs, API keys and account numbers are redacted.

## Decisions applied (D1–D9)

| Decision | How it is applied |
|---|---|
| D2 | No new URLs; jurisdiction URLs are unchanged. |
| D3 | No payment metrics are shown. |
| D4 | `customers.user_id` is nullable, and Customers → Add creates no login. |
| D5 | `admin_documents_v` is a read-only view; the five tables are untouched. |
| D6 | No Professional Tax page. |
| D7 | Integrations show "Not connected". |
| D8 | The role is named "Consultant". |
| D9 | Navigation and footer are not editable. |

## Database

The migration is `database/migrations/2026-09-28-phase1-admin-platform.sql`, with rollback `…-rollback.sql`. It is additive and has been tested apply → rollback → re-apply.
- The rollback is blocked (nothing changes) while customers without a login exist.
- Roles still held by users are kept.
- Run it after `2026-09-25-cms-editing.sql`, with a backup, on staging first. **Deploy the Phase 1 code and this migration together.** Until the migration runs, only the super admin can open admin pages; everyone else gets 403 because their module permissions do not exist yet.

## Tests (local only)

    php -l on all files
    CMS_TEST_WRITE=1 php tests/admin-platform-test.php
    CMS_TEST_WRITE=1 PYN_MYSQL="mysql … pyn" python3 tests/admin/test_admin_http.py http://127.0.0.1:8106
    CMS_TEST_WRITE=1 php tests/cms-test.php
    plus the existing blog, jurisdiction and anti-spam suites and tests/live/live_qa.py

`tests/admin/helper.php` creates local sessions for the HTTP test. It refuses to run when APP_ENV=production.
