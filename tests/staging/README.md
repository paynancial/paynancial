# Staging validation scripts (Phase 1)

For a **staging environment or a disposable copy only**. They never run against production: both PHP helpers refuse `APP_ENV=production`.

| Script | Purpose |
|---|---|
| `run_gate.sh` | **The whole gate in one command**, in the corrected order: backup, BEFORE snapshot (immediately after the backup, before migrations, deployment or fixtures), run-once check, migrations, fixtures, deploy and code-version check, hosting checks, QA, RBAC, security, 12 real email-OTP sign-ins, fixture cleanup (verified), AFTER snapshot, comparison, rollback drills on restored copies, then `gate-results.md`. Refuses production hostnames. Cleans up fixtures even when it stops early. |
| `fixtures.php` | Staging fixtures, which are temporary and identifiable. Modes: `base` and `cms` create them, `verify` checks they exist, `cleanup` removes them, and `check-clean` proves nothing is left. Requires `STAGING_FIXTURES=1`. |
| `page_snapshot.py` | Public-site snapshot. For each page it records the status, title, canonical, robots meta, content and markup fingerprints, and broken internal links (`_manifest.json`). Portal pages are included only with `SNAPSHOT_PORTAL_ACCOUNTS`. |
| `snapshot_compare.py` | BEFORE/AFTER comparison: pages added and removed, and changed statuses, titles, canonicals, robots meta, content, markup and newly broken links. |
| `rbac_matrix.py` | Tests all 12 staff roles through the real password + OTP endpoints (the test sets the OTP; this is not the real email-OTP check), sidebar, all admin routes by direct URL, form actions, exports, settings, approvals, publishing, CSRF and logout. |
| `security_probes.py` | 55 probes: stored XSS, SQL injection, CSV injection, session fixation and logout, lockout, source exposure and privilege escalation. |
| `expect.php`, `mksession.php` | Helpers. Expected permissions come from the deployed registry; `mksession.php` creates pre-authenticated test sessions (file sessions on the same host). |

Environment: `PYN_MYSQL` holds the mysql CLI command for the staging database. The scripts create test users named `*@stg.invalid`.

See `docs/release/phase1-staging-rehearsal-2026-09-27.md` for the fixtures, the run order and the last results.
