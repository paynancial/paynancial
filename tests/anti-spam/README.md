# Anti-spam tests (floating enquiry "Request a Callback")

Local only — never run against production (the suite drops/renames the rate-limit table and writes test enquiries).

## Unit (no server or database)

```
php tests/anti-spam/unit_test.php
```
Environment-only keys, missing secret ⇒ failure (not outage), validation, file-store limit, corrupted bucket, unusable store.

## API end-to-end

Prerequisites: a local MySQL/MariaDB loaded with `database/schema.sql` and `database/anti_spam_schema.sql`; a local `config/config.php` with `APP_ENV = development` and `SESSION_COOKIE_SECURE = false` (never commit it).

```
php -S 127.0.0.1:8107 tests/anti-spam/mock_turnstile.php &
TURNSTILE_SITE_KEY=1x00000000000000000000AA TURNSTILE_SECRET_KEY=test-secret-local \
TURNSTILE_VERIFY_URL=http://127.0.0.1:8107/verify \
  php -S 127.0.0.1:8106 -t public tests/anti-spam/dev_router.php &
PYN_MYSQL="mysql -u<user> -p<pass> <db>" python3 tests/anti-spam/test_api.py
```

Covers the 22 original cases (valid, invalid, missing fields, bad email/phone, header injection, XSS, failed / missing / reused token, duplicate, honeypot, too fast, provider outage, IP and email limits, CSRF, GET, CAPTCHA disabled) and the go-live gate: Turnstile unavailable (accepted + flagged + logged), more than 2 per hour refused, recovery, missing table, file-store fallback, corrupted store, unavailable store, and restore.
