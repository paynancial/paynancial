# Staging validation scripts (Phase 1)

For a **staging environment or a disposable copy only**. They never run against production: both PHP helpers refuse `APP_ENV=production`.

| Script | Purpose |
|---|---|
| `run_gate.sh` | **The whole gate in one command**, in the mandated order: backup, fixtures, snapshot BEFORE, run-once check, migrations, deploy, hosting checks, QA, RBAC, security, OTP sign-ins, snapshot AFTER, rollback drill, then `gate-results.md`. The header lists the environment variables it needs. |
| `fixtures.php` | Creates (`base`, `cms`) and removes (`cleanup`) the staging fixtures. Requires `STAGING_FIXTURES=1`. |
| `page_snapshot.py` | Normalised HTML of every sitemap URL, jurisdiction and noindex page, and the portal pages. Run it before and after a deploy, then `diff -rq`. |
| `rbac_matrix.py` | Tests all 12 staff roles with real password + OTP logins, sidebar, all admin routes by direct URL, form actions, exports, settings, approvals, publishing, CSRF and logout. |
| `security_probes.py` | Stored XSS, SQL injection, CSV injection, session fixation and logout, lockout, source exposure and privilege escalation. |
| `expect.php`, `mksession.php` | Helpers. Expected permissions come from the deployed registry; `mksession.php` creates pre-authenticated test sessions (file sessions on the same host). |

Environment: `PYN_MYSQL` holds the mysql CLI command for the staging database. The scripts create test users named `*@stg.invalid`.

See `docs/release/phase1-staging-rehearsal-2026-09-27.md` for the fixtures, the run order and the last results.
