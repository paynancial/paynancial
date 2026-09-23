# Paynancial — Platform Rebuild

A ground-up rebuild of the Paynancial marketing site and platform: PHP 8 /
MySQL, no framework, no Node.js build step, shared-hosting/cPanel friendly.
Content and contact details are drawn from Paynancial's existing public
pages; no business, licensing or certification claims have been invented —
see [Content & claims policy](#content--claims-policy).

## What's in this repo

- **Public marketing site** — home, about, leadership, solutions, products,
  pricing, developers, partners, support, contact, careers, security &
  compliance, blog, legal pages. Editorial "ledger" design system (Fraunces
  serif headlines, JetBrains Mono labels, ink/paper palette). Clean URLs,
  no `.php` extensions.
- **Left-side login panel** — the header "Login" button opens an off-canvas
  panel (slides from the left) with four tabs: **Customer / Partner /
  Employee / HRMS**. Authenticates via AJAX (`/api/auth/login`), no page
  reload.
- **Role-based dashboards** — `/customer`, `/partner`, `/employee`,
  `/hrms`, `/admin`, `/super-admin`, each with its own layout, sidebar and
  real, database-backed pages (not static mockups).
- **Paynancial Partner Hub** — a full B2B partner ecosystem layered on top
  of the base platform: public registration wizard, onboarding tracking,
  customer enrollment with a rules-based solution recommendation engine,
  solution catalog, transaction/settlement/commission reporting, proposal
  builder, payment links, performance analytics, support/knowledge/
  marketing hubs, a scoped rules-based AI assistant, and team management
  with a module-level permission matrix. See
  [Partner Hub module map](#partner-hub-module-map) below.
- **MySQL schema** covering identity/RBAC, payments, HRMS, CMS, support,
  audit logging, and the full Partner Hub domain (`database/schema.sql`,
  `database/partner_hub_schema.sql`, `database/seed.sql`,
  `database/partner_hub_seed.sql`).
- **Security by default** — PDO prepared statements everywhere (no
  repeated named placeholders — `ATTR_EMULATE_PREPARES` is off), CSRF
  tokens on every form/AJAX call, `password_hash()`/`password_verify()`,
  session regeneration on login, idle session timeout, login-attempt
  tracking and lockout, secure cookies, a nonce-based CSP (every inline
  `<script>` carries `nonce="<?= csp_nonce() ?>"`; inline event-handler
  attributes are never used — see `.js-auto-submit`/`.js-confirm`/
  `.js-print` in `includes/dashboard-foot.php`), and AES-256-GCM
  field-level encryption for stored bank account numbers.

## Architecture

```
config/            config.example.php, config.php (git-ignored)
database/          schema.sql, seed.sql (base platform)
                    partner_hub_schema.sql, partner_hub_seed.sql (Partner Hub)
includes/          bootstrap, db, auth, security, functions, header/footer,
                    login panel, dashboard shell, partner.php (data-isolation
                    + recommendation engine), partner-assistant.php (AI helper)
pages/             public marketing page templates (content only), plus
                    partner-register.php (public wizard), pay.php, reset-password.php
customer/          customer portal pages
partner/           Partner Hub pages — see module map below
employee/          employee portal pages
hrms/              HRMS pages
admin/             admin panel + CMS + Partner Hub admin controls
api/               JSON endpoints (auth, contact, partner/recommend,
                    partner/assistant)
public/            web root: index.php (front controller), .htaccess,
                    assets/ (css/js/images), robots.txt, sitemap.php
```

**Routing.** `public/index.php` is the only PHP file the web server ever
executes directly. `public/.htaccess` rewrites every request to it. It
matches the URL against a small route table and does one of three things:

1. `/api/...` → runs a JSON endpoint in `api/` directly.
2. `/{customer|partner|employee|hrms|admin|super-admin}/{page}` → checks
   the session holds an allowed role (`require_role()`), then renders the
   page inside the dashboard shell (`includes/dashboard-head.php` /
   `dashboard-foot.php`).
3. Everything else → looks up a template in `pages/` and renders it inside
   the marketing shell (`includes/site-head.php`, `header.php`,
   `footer.php`, `site-foot.php`).

Page templates only ever output body content — no `<html>`/`<head>` tags —
so the same template can be wrapped in either shell.

**Adding a new page or dashboard module.** Drop a `.php` file in the right
folder, add its slug to the relevant route table in `public/index.php`
(and the sidebar entry in `includes/dashboard-nav.php` for dashboards). No
other wiring is required — this is what "modular, extendable without a
rebuild" means in practice here.

## Partner Hub module map

Every partner-scoped query filters by `partner_id = current_partner_id()`
(`includes/partner.php`) — resolved server-side from the session, never
trusted from request input. This is what stops one partner organization
from ever seeing another's customers, transactions, commissions, or
documents. `require_partner_context()` gates every page below.

| Route | What it does |
| --- | --- |
| `/partner/register` (public) | 7-step onboarding wizard → `partner_applications` + documents + encrypted bank details + agreements |
| `/partner/login` | Redirects into the existing left-side login panel, Partner tab |
| `/partner/dashboard` | KPIs, pipeline funnel, 6-month charts — all computed live, nothing hardcoded |
| `/partner/onboarding` | Visual status tracker over the application's review pipeline |
| `/partner/customers`, `/partner/customers/{id}` | Application list + tabbed profile (Overview/Documents/Solutions/Finance/Payment Links/Notes) |
| `/partner/enroll-customer` | 7-step wizard ending in a rules-based solution recommendation (`recommend_products_for_customer()`, admin-configurable via `recommendation_rules`) |
| `/partner/products` | Solution catalog grouped by category |
| `/partner/transactions`, `/partner/settlements`, `/partner/commissions` | KPI cards + Chart.js trends; commission rates always read from admin-configured `commission_rules` (seeded at 0.00% until set) |
| `/partner/proposals`, `/partner/proposals/{id}` | Build a branded, printable proposal from the catalog; status tracked Draft→Sent→Negotiation→Accepted/Rejected/Expired |
| `/partner/payment-links` | Create/list/disable shareable payment link references; public viewer at `/pay/{ref}` |
| `/partner/performance` | Funnel, 12-month transaction/commission growth, top customers |
| `/partner/support`, `/partner/resources`, `/partner/marketing` | Ticketing (threaded replies), Knowledge Center, Marketing Hub — both hubs render an honest empty state until admin publishes content |
| `/partner/team` | Invite sub-users, assign a `partner_roles` role, module-level permission matrix (view-only reference) |
| `/partner/profile` | Change password; owner/admin can edit the business profile |
| AI Partner Assistant (floating widget, every partner page) | Rules-based Q&A (`includes/partner-assistant.php`) — explicitly labeled as non-LLM, answers only from the logged-in partner's own data. Documented extension point to back it with a real LLM later without changing its data-scoping. |
| `/admin/partner-applications`, `/admin/partner-applications/{id}` | Review/approve/reject; approval creates the `users`+`partners` row and emails a password-set link (same token mechanism as forgot-password) rather than a raw password |
| `/admin/products`, `/admin/commission-rules` | Catalog and commission-rate configuration — the only place rates are ever set |
| `/admin/customer-applications` | Read-only oversight across every partner's pipeline |

## Installation (shared hosting / cPanel)

1. **Point the domain's document root at `/public`.** This keeps
   `config/`, `includes/`, `database/`, and every role folder outside the
   web-servable directory — the strongest, simplest protection against
   someone downloading your source or config over HTTP. (A root
   `.htaccess` that denies all requests is included as a second layer of
   defense if your host cannot change the document root.)
2. **Create the MySQL database and import the schema:**
   ```bash
   mysql -u youruser -p your_db < database/schema.sql
   mysql -u youruser -p your_db < database/seed.sql
   mysql -u youruser -p your_db < database/partner_hub_schema.sql
   mysql -u youruser -p your_db < database/partner_hub_seed.sql
   ```
   The `partner_hub_*` files are additive (new tables + a handful of
   `ALTER TABLE` additions to `partners`/`payment_links`) and, like
   `seed.sql`, are meant to run once on a fresh install.
3. **Configure the app:**
   ```bash
   cp config/config.example.php config/config.php
   ```
   Edit `config/config.php` — set `DB_*`, `APP_URL`, `APP_SECRET` (a random
   64-char hex string: `php -r "echo bin2hex(random_bytes(32));"`), and
   mail settings. Never commit this file (it's git-ignored).
4. **Set upload storage permissions** (used for CVs, partner/employee
   documents): the app creates `storage/uploads/...` on demand; make sure
   the PHP process can write to the project root, or pre-create
   `storage/uploads` with `0750` permissions.
5. **Enable `mod_rewrite`** (standard on cPanel/Apache) so the `.htaccess`
   files take effect.
6. **First login:** `superadmin@paynancial.com` / `ChangeMe@2026`
   (seeded in `database/seed.sql`). **Change this password immediately**
   via the account's normal password-reset flow — do not leave the seeded
   credential in place.

### Local development

```bash
cp config/config.example.php config/config.php   # point at a local MySQL
php -S 127.0.0.1:8000 -t public
```
Note: PHP's built-in server does not read `.htaccess`, so
`/sitemap.xml` (which relies on the rewrite rule) will only resolve at
`/sitemap.php` locally — it works normally under Apache.

## Security implementation notes

- Passwords: `password_hash()` / `password_verify()` only; never stored or
  logged in plaintext.
- CSRF: every state-changing form and AJAX call carries a per-session
  token, verified with `hash_equals()`.
- Sessions: `HttpOnly`, `SameSite=Lax`, secure flag on in production;
  regenerated on login; idle-timeout enforced server-side
  (`SESSION_LIFETIME_SECONDS`).
- Brute force: attempts are logged to `login_attempts`; an identifier is
  locked out after `LOGIN_MAX_ATTEMPTS` failures for
  `LOGIN_LOCKOUT_MINUTES`. A lightweight session-backed rate limiter also
  throttles the login/contact/forgot-password endpoints per IP.
- All database access goes through PDO prepared statements
  (`includes/database.php`, `ATTR_EMULATE_PREPARES` off).
- Uploads (HRMS CVs) are validated by extension **and** MIME sniffing
  (`finfo`), size-capped, and stored under a random filename outside any
  public/guessable path (`validate_cv_upload()`, `store_upload()`).
- A global exception handler (`includes/bootstrap.php`) prevents stack
  traces or DB errors from ever reaching a visitor in production
  (`APP_DEBUG=false`).
- Role-based access control: every dashboard route is gated by
  `require_role()`; the DB schema additionally supports fine-grained
  `permissions` / `role_permissions` / `user_permissions` for modules that
  need finer control than role alone.

## Content & claims policy

Per the project brief, this build **does not invent** transaction volumes,
customer counts, certifications (PCI DSS, ISO, SOC), regulatory approvals,
or banking/payment-aggregator licenses. Where such information would
normally appear:

- `/security` includes a clearly labeled placeholder section for
  certifications, to be filled in once verified.
- Testimonials on the homepage are explicitly tagged **"Placeholder
  content"** pending real, verifiable quotes.
- Pricing is "Talk to Sales" by default (`settings.pricing_mode`),
  configurable from the codebase without inventing numbers.
- Team/leadership bios are a CMS-ready stub until real, verified profiles
  are supplied.

The same policy applies to the Partner Hub: no invented partner/customer
counts, no invented transaction volumes, and no hardcoded commission
percentages anywhere in the code — every commission rate comes from the
admin-managed `commission_rules` table and is seeded at 0.00% until an
admin sets it (`admin/commission-rules.php`).

Verified facts already wired in (from Paynancial's existing site/socials):
legal name (M/S Paynancial Technology Private Limited), CIN
(`U66190BR2024PTC067929`), GST (`10AAOCP5173C1ZO`), support email
(`hello@paynancial.com`), the existing brand mark, real leadership bios,
and the official social links in the footer (Facebook, LinkedIn, X,
Instagram, Pinterest, YouTube).

## What's fully built vs. scaffolded

**Fully functional, database-backed:** public marketing site; left-side
login panel and AJAX authentication for all four portals; customer
dashboard/payment history/profile; employee dashboard/tasks (support-ticket
queue); HRMS dashboard/employee directory/recruitment (job posting +
applications)/attendance; admin dashboard/user management/transactions/
enquiries/CMS (homepage hero + SEO fields are editable and persisted —
wiring `pages/home.php` to read them instead of its static copy is a
small follow-up noted inline in `admin/cms.php`); contact/enquiry system with
generated `PAY-ENQ-YYYY-NNNNNN` IDs and email notification; the full
**Paynancial Partner Hub** described above end-to-end (registration →
admin approval with real account creation → dashboard → customer
enrollment with live recommendations → catalog → transactions/
settlements/commissions → proposals → payment links → performance →
support/resources/marketing → AI assistant → team management → admin
oversight) — every flow has been exercised against a live MySQL instance,
including cross-partner data-isolation checks.

**Scaffolded for the next phase**: refunds detail views, API key/webhook
management UI, HR payroll/performance/announcements, admin blog/CMS pages
beyond the homepage, security audit log viewer, and — for the Partner
Hub specifically — actually processing a payment through `/pay/{ref}`
(the link and its details are real and tracked; card/UPI collection needs
a live gateway integration, which the page states honestly rather than
faking a success screen), and serving admin-uploaded Knowledge
Center/Marketing Hub files (the `external_url` column works today; a
`file_path` upload needs a small authenticated download route since
`storage/uploads` is intentionally outside the web root). None of these
are faked in the UI — they are simply not yet built, and the schema/route
table are structured so adding them is additive, not a rewrite.

## Tech stack

HTML5, CSS3 (hand-built design system — no Bootstrap/Tailwind runtime
dependency), vanilla JavaScript (mega menu, login panel, AJAX, animated
counters, scroll reveal, code-copy, wizard steppers), Chart.js (loaded
from cdnjs, allowed explicitly in the CSP `script-src`) for dashboard
charts, PHP 8+, MySQL via PDO, Apache `mod_rewrite`. No Node.js is
required to build or run this in production.
