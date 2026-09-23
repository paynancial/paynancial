# Floating enquiry — anti-spam (Cloudflare Turnstile)

## What is protected

| Widget option | Type | CAPTCHA |
|---|---|---|
| WhatsApp | Direct link (`wa.me`) | **Never** |
| Email | Direct link (`mailto:`) | **Never** |
| Call Sales | Direct link (`tel:`) | **Never** |
| Request a Callback | Form → `POST /api/enquiry/callback` | **Yes — verified server-side** |

The callback form is rendered only when it is active, the CAPTCHA is enabled and **both** keys are configured. It is never offered unprotected.

## Setup (production)

1. **Cloudflare:** in Turnstile, create a widget for `paynancial.com`, widget mode **Managed** (the site renders it with `appearance: interaction-only`, so most visitors see nothing). Restrict the hostname list to the verified Paynancial domain(s).
2. **Keys — server environment variables only.** `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY` are read from the server environment (`getenv`, `$_SERVER`, `$_ENV`) and **nowhere else** — not `config/config.php`, not the database, not the CMS. Examples:
   - PHP-FPM pool (`www.conf`): `env[TURNSTILE_SITE_KEY] = …` and `env[TURNSTILE_SECRET_KEY] = …`
   - Apache: `SetEnv TURNSTILE_SITE_KEY …` in the vhost (not in a web-readable `.htaccess`)
   - nginx + FPM: `fastcgi_param TURNSTILE_SITE_KEY …;` in the server block

   If either key is missing, the callback form is not shown and any submission is refused. A missing secret is treated as a failed check, never as an outage.
3. **Migration — only after a backup and validation** (see *Migration runbook* below).
4. **CMS:** Admin → Settings → Floating Enquiry Anti-Spam (`/admin/anti-spam`) for active, CAPTCHA enabled, honeypot, rate limits and notification email. It shows whether each key is configured and the last Cloudflare outage; it never accepts or displays a key.

`TURNSTILE_VERIFY_URL` (a verify-endpoint override for the mock) is honoured **only** when `APP_ENV` is not `production`.

## Migration runbook (`database/anti_spam_schema.sql`)

The migration is additive: it only creates `anti_spam_hits` (`CREATE TABLE IF NOT EXISTS`) and alters nothing else. It was validated locally: it can be run twice safely, and the row counts and checksums of every other table were unchanged.

1. Back up: `mysqldump --single-transaction <db> > backup-YYYYMMDD.sql`, and confirm the file is complete.
2. Validate on staging (or a restored copy): run the file, then `SHOW CREATE TABLE anti_spam_hits;` — expect `bucket CHAR(64)`, `created_at DATETIME` and indexes `idx_anti_spam_bucket_time`, `idx_anti_spam_time`.
3. Run on production. Until it has run, the file-store fallback enforces the limits (`storage/anti-spam` must be writable by PHP).
4. Rollback, if ever needed: `DROP TABLE anti_spam_hits;` (it holds only short-lived rate-limit hashes); the file store then takes over.

## Fail-closed rules — never an unrestricted fallback

| Situation | Behaviour | Tested |
|---|---|---|
| Cloudflare reachable | Token must verify server-side; failure → 403 | Yes |
| Cloudflare unreachable | Accepted only under 2 per IP per hour; enquiry subject and payload flagged **"Security check unavailable"**; notification says so; outage logged and shown in the admin | Yes |
| More than 2 fallback submissions per hour | Refused | Yes |
| Cloudflare recovers | Normal verified path, no flag | Yes |
| Secret missing (misconfiguration) | Form hidden; submissions refused; never the outage fallback | Yes |
| `anti_spam_hits` table missing | File store enforces the same limits | Yes |
| File store corrupted | Request refused; bucket kept full for the window (not reset); logged | Yes |
| Table missing **and** file store unusable | Request refused; logged; admin alert | Yes |

## Server-side flow (`api/enquiry/callback.php`)

1. POST only; form unavailable → refuse (503) — never accept unprotected
2. CSRF token
3. Honeypot field + minimum fill time (2.5 s) → silently discarded (success-shaped response, nothing stored)
4. Rate limits per IP and per session (before any outbound call)
5. Validation + normalisation (name, email lower-cased, phone digits, lengths, allow-listed requirement, CR/LF rejected)
6. Turnstile token verified with Cloudflare `siteverify` (secret + token + visitor IP + idempotency key; 6 s timeout)
   - invalid / missing / expired / reused token → 403 "Please complete the security check and try again."
   - Cloudflare unreachable → **not** disabled: accepted only under a tighter per-IP limit (2 per hour), flagged `provider_unavailable` in the stored payload and in the notification subject, and logged for admins
7. Rate limits per email and per phone; duplicate guard (same email + phone + requirement + message within 30 min → 409)
8. Stored with parameterised PDO in `enquiries` + `contact_submissions` (`form_type = floating_callback`)
9. Notification email (Reply-To only from the validated address; no user input in other headers)

Visitors only ever see the friendly messages; no provider, database or path details. Rate-limit buckets and the admin event log (`storage/logs/anti-spam.log`) hold keyed hashes only — no raw IPs, emails or phones.

## Default limits (editable in the CMS)

| Limit | Default |
|---|---|
| Per IP | 5 per 10 min |
| Per session | 5 per 10 min |
| Per email | 3 per hour |
| Per phone | 3 per hour |
| Per IP while Cloudflare is unreachable | 2 per hour |

These are conservative starting points for a sales-enquiry form; review them against real traffic.

## CSP

Only `https://challenges.cloudflare.com` was added, to `script-src` and `frame-src` (`includes/security.php`). The nonce-based policy is otherwise unchanged. The Turnstile script is loaded lazily, the first time the callback form is opened — never on page load.

## Privacy

The form collects only name, business email, phone, and optional company, requirement and message. A short notice on the form explains that submissions are checked by Cloudflare Turnstile. The Privacy Policy itself is **unchanged** pending legal review; the required Turnstile / enquiry-form addition is recorded in Admin → Content Governance.

## Tests

See `tests/anti-spam/README.md`. Current results (local): API suite 41/41, unit suite 12/12, browser suite 23/23 (1440–360 px).

## Analytics events

`floating_enquiry_open`, `floating_form_open`, `floating_form_submit`, `floating_form_success`, `floating_form_error`, `captcha_failed`, `whatsapp_click`, `email_click`, `call_click` — dispatched as `paynancial:track` DOM events (and to `dataLayer` / `gtag` if present). No form field values, tokens or keys are included.
