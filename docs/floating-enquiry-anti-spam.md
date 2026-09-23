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

1. In Cloudflare → Turnstile, create a widget for `paynancial.com` (widget mode **Managed**; the site renders it with `appearance: interaction-only`, so most visitors see nothing). Restrict the hostname list to your domains.
2. Set **environment variables** on the server (preferred) — or constants of the same name in `config/config.php`, which is git-ignored:
   ```
   TURNSTILE_SITE_KEY=...
   TURNSTILE_SECRET_KEY=...
   ```
   Never commit the secret. It is never stored in the database or shown in the CMS.
3. Run `database/anti_spam_schema.sql` (creates `anti_spam_hits`, the server-side rate-limit store). Without it, a file store under `storage/anti-spam` is used automatically.
4. Admin → **Settings → Floating Enquiry Anti-Spam** (`/admin/anti-spam`): active, CAPTCHA enabled, honeypot, rate limits, notification email. The page shows whether each key is configured, never the secret.

`TURNSTILE_VERIFY_URL` (a verify-endpoint override) is honoured **only** when `APP_ENV` is not `production` — it exists for local testing.

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

The form collects only name, business email, phone, and optional company, requirement and message. A short notice on the form explains that submissions are checked by Cloudflare Turnstile. The Privacy Policy itself is under legal review (no edits); the needed addition is recorded in Admin → Content Governance.

## Analytics events

`floating_enquiry_open`, `floating_form_open`, `floating_form_submit`, `floating_form_success`, `floating_form_error`, `captcha_failed`, `whatsapp_click`, `email_click`, `call_click` — dispatched as `paynancial:track` DOM events (and to `dataLayer` / `gtag` if present). No form field values, tokens or keys are included.
