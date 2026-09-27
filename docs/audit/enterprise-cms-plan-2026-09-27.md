# Paynancial Enterprise CMS / Admin Platform: audit and implementation plan

Date: 27 Sep 2026 · Branch: `claude/laughing-clarke-u06pou` · Base commit: `32bdb50`
Scope of this document: **audit and plan only**. No application code, database, credentials or production system was changed.

---

## 1. CURRENT CMS AUDIT

**Stack:** plain PHP 8.4 behind a single front controller (`public/index.php`), with MySQL/MariaDB accessed through PDO (prepared statements, `db()`). There is no framework, no Composer and no build step. CSS and JS are hand-written: `main.css` and `dashboard.css`, plus vanilla JS in `main.js` and `cms-admin.js`.

**Admin shell:** `includes/dashboard-head.php`, `includes/dashboard-foot.php` and `includes/dashboard-nav.php`.
- This one shell is **shared by five portals**: customer, partner, employee, HRMS and admin.
- The sidebar is a flat list of links with optional group headings.
- There is no collapse, search, badges or permission filtering. Menu items are filtered by role slug only, per portal.
- The top bar shows the page heading and the user's name.

**Existing admin modules (`admin/*.php`, 19 files, about 2,400 lines):**

| Module | What it does today | Quality |
|---|---|---|
| dashboard | 8 raw `COUNT(*)` tiles and a link list; **shows 0 on a query error**, which silently misrepresents data | Basic |
| users | List and filter by role; no create, edit, suspend or role change | Read-only |
| transactions | List of the `transactions` table | Read-only |
| enquiries | List, type filter and status select (`new / in_progress / responded / closed`) | Basic |
| cms, cms-articles, cms-article, cms-hero, cms-seo, cms-preview | **Built 25 Sep:** workflow, RBAC, snapshots, audit history, sanitiser, uploads | Production-grade logic, basic visuals |
| content-governance | Read-only view of the publishing gate, jurisdictions, blog status and the regulatory panel | Good |
| anti-spam | Turnstile / rate-limit configuration status | Good |
| partner-applications, customer-applications, customer-kyc, products, commission-rules | Partner Hub back office | Functional |
| change-requests | Maker-checker for sensitive user field changes, audited | Good |
| audit-logs | Filterable list of `audit_logs` | Basic |

**Content model today**
- **Code (version-controlled):** all public page copy, including products, AI, Business Services (14 services, 15 jurisdictions), developer docs, solutions, legal pages, and the header and footer navigation.
- **CMS (25 Sep build):**
  - general blog articles, through a workflow and published snapshots;
  - the homepage hero;
  - SEO overrides for 22 core pages, where the CMS can only add noindex.
- **Governance (`includes/content-governance.php`):** the publishing gate decides indexing and sitemap inclusion for governed pages. Foreign jurisdictions are gated off. The Indian regulatory context is public with the fixed disclaimer.

**Business Services data:** held in code (`includes/business-services.php`). There is **no operational incorporation workflow** of any kind, and no case, task or document records for incorporation.

**Payments:** the tables exist (`transactions`, `payments`, `refunds`, `settlements`, `payment_links`, `api_keys`, `webhooks`), but **no payment processor is integrated**. The only outbound HTTP integration in the codebase is Cloudflare Turnstile. There are no chargeback, invoice or expense tables.

**Analytics:** there is **no data source**. There is no GA4 or Search Console API connection, no page-view logging and no uptime monitor.

**Design reference:** the brief refers to an attached dashboard reference image. **No image arrived in this conversation.** See decision D1.

## 2. CURRENT DATABASE AUDIT

The database has 63 tables across 6 schema files and 2 migrations.

| Domain | Tables | Reusable for |
|---|---|---|
| Identity | `users` (single `role_id`, `session_version`), `roles` (6: customer, partner, employee, hr, admin, super_admin), `sessions`, `login_attempts`, `known_devices`, `otp_verifications`, `password_resets` | RBAC, login audit, security page |
| RBAC | `permissions` (12 baseline + 10 `cms.*`), `role_permissions`, `user_permissions` (grant/revoke) | **The full RBAC matrix, with no new tables** |
| Audit | `audit_logs` (user, action, entity_type, entity_id, ip, meta_json, created_at; indexes on user, action and entity) | The audit log; old and new values can go in `meta_json` |
| CMS | `cms_pages` (workflow and snapshot added 25 Sep), `blog_posts` (workflow and snapshot added 25 Sep) | Pages, hero, SEO, blog |
| Governance | `content_readiness`, `regulators`, `regulatory_references`, `regulatory_reference_links`, `regulatory_review_queue` (view). These exist in the schema file; it is unconfirmed whether they are in production. | Regulatory Content module |
| Leads | `enquiries` (type ENUM sales/partner/support/general/career, status, assigned_to), `contact_submissions` | The leads pipeline, which needs a stage and a source |
| Customers | `customers` (**user_id NOT NULL UNIQUE**, 1:1 with a login), `customer_kyc_profiles`, `customer_kyc_documents`, `customer_bank_accounts` (encrypted), `customer_product_activations`, `customer_applications` (partner-sourced, has `pipeline_stage`), plus its notes, documents and products tables | CRM. **Constraint:** an incorporation client without a login cannot be a `customers` row today (decision D4). |
| Partners | 17 `partner_*` / proposal / commission / resource tables | Unchanged |
| Payments | `transactions`, `payments`, `refunds`, `settlements`, `commissions`, `payment_links`, `api_keys`, `webhooks` | Read-only payment views; **no live data source** |
| Work | `support_tickets`, `support_messages`, `notifications`, `employees`, `attendance`, `leave_requests`, `job_posts`, `job_applications` | Notifications; tasks are **not** modelled (the employee "tasks" page reads enquiries and tickets) |
| Config | `settings` (key/value) | Configuration and feature flags |
| Security | `change_requests`, `anti_spam_hits` | Security page |

**Documents are spread across five tables** (`partner_documents`, `partner_application_documents`, `customer_kyc_documents`, `customer_application_documents`, `employee_documents`). None of them has versioning, expiry, a reviewer or signature status.

**Missing concepts:** incorporation cases, generic tasks, SLA policies, content versions, a media library, structured page blocks, redirects, user UI preferences, invoices, chargebacks and expenses.

## 3. CURRENT RBAC AUDIT

- **Authentication:** session login with OTP and known-device checks, idle timeout, `session_version` for "log out of all devices", and login attempts recorded with lockout. Admin and super admin are each one role.
- **Area gate:** `require_role()` per portal (`$dashboardAreas` in `public/index.php`). All admin pages are reachable by both `admin` and `super_admin`.
- **Permission gate:** `user_can()` (`includes/permissions.php`), with role grants plus per-user grant/revoke and super_admin always allowed.
  - It is **enforced only in the 6 CMS pages**.
  - Every other admin page (users, transactions, enquiries, KYC, partner hub, audit logs) is **role-only**: any admin can see and change everything there.
  - The seeded permissions (`customers.manage`, `transactions.export` and so on) are **not enforced anywhere**.
- **Partner Hub:** has its own RBAC (`partner_roles`, `partner_role_permissions`) for partner staff. It is separate and stays separate.
- **Missing roles:** 10 of the 12 roles in the spec (Operations Manager, Sales Manager, Consultant, Incorporation Consultant, Content Manager, SEO Manager, Legal/Compliance Reviewer, Finance Manager, Developer, Support).

## 4. CURRENT ROUTE AUDIT

| Group | Routes |
|---|---|
| Public (frozen) | 25 static routes, plus `/products/*`, `/solutions/*`, `/business-services/*` (services, global incorporation, 15 jurisdictions), `/developers/*`, `/agentic-ai/*`, `/ai-intelligence/*`, `/blog/*`, `/legal/*`, `/pay/{ref}`, `/partners` (301). Sitemap: 99 URLs. |
| Auth / API | `/api/auth/*` (login, OTP, logout, forgot password), `/api/contact/submit`, `/api/enquiry/callback`, `/api/newsletter/subscribe`, `/api/partner/*` |
| Portals | `/customer/*` (4), `/partner/*` (16), `/employee/*` (3), `/hrms/*` (4), `/admin/*` (20 allow-listed pages), `/super-admin/dashboard` |
| Admin pattern | `/admin/{page}/{param}`. Each page is a PHP file rendered inside the shared dashboard shell. POST handlers run in the same file (PRG pattern and CSRF). |

The spec's `paynancial.com/global-incorporation/{country}` URLs **conflict with the frozen URL structure**, which already uses `/business-services/global-incorporation` and `/business-services/jurisdictions/{slug}`. See decision D2.

## 5. CURRENT CONTENT WORKFLOW

- **CMS items** (blog articles, homepage hero, page SEO), as built on 25 Sep:
  - stages: Draft → Editorial review → SEO/AEO review → Business/legal review → Approved → Published;
  - reject to draft with a reason; unpublish with a reason and a confirmation;
  - separation of duties outside super admin;
  - a stale-edit lock;
  - an approval history in `audit_logs`;
  - published snapshots, which the public site reads.
- **Gaps against the spec:**
  - The **actor's role** is not stored on each event.
  - There is no **explicit Revision state**; rejection returns the item to Draft.
  - **Version history** keeps only the current published snapshot and the working copy; earlier published versions are not retained.
- **Code-based content** (every other page): changed through reviewed commits. The gate lives in `content-governance.php`. Regulatory references carry a workflow field (`regulatory_references.workflow_stage`), but no admin screen edits them.

## 6. GAPS AGAINST THIS MASTER SPEC

| Spec area | Status | Gap |
|---|---|---|
| §5 Global shell (collapsible, searchable, badges, permission-aware) | Missing | New admin-only shell. The other portals keep the current shell. |
| §6 Header (search, ⌘K, environment, notifications, help) | Missing | New |
| §7–13 Dashboard (hero, KPIs, analytics, pipelines, tables, quick actions, right rail) | Missing | New. KPIs must come from real queries. Traffic, CTR and revenue have **no data source**. |
| §14 Workflow (role, comment, revision, version history) | Partial | Add `actor_role` on events, a Revision state and a `content_versions` table |
| §15 Blog (featured image, author, tags, canonical, schema) | Partial | Add tags, a featured image through the media library, author and a schema type. Canonical stays self-referencing (frozen strategy). |
| §16 Regulatory content | Partial (code) | Admin editor for `regulatory_references`, with the disclaimer enforced |
| §17 Foreign jurisdictions | Gated in code | Admin view of the gate. Changing URLs conflicts with the freeze (D2). |
| §18 SEO/AEO (keyword, intent, schema, AEO block, health score) | Partial | Extend page SEO; add an explainable Content Health score |
| §19 Structured blocks | Missing | `cms_blocks` for **new** CMS-managed sections only. Existing pages are not re-platformed. |
| §20 Media library | Missing (upload only) | `media_assets` |
| §21 Documents | Fragmented | A unified `documents` and `document_versions` design (D5) |
| §22 Incorporation operations | Missing | New module |
| §23 CRM | Partial | Customer 360 view over existing tables |
| §24 RBAC (12 roles, module-level permissions) | Partial | Seed 10 roles and a permission catalogue; enforce on every admin page |
| §25 Audit (old/new values, all sensitive actions) | Partial | Central `audit()` helper with diffs; login, logout and export events |
| §27 System health | Missing | Real checks only; "Status unavailable" otherwise |
| §28 Cache purge | N/A today | No page cache exists. Cloudflare purge needs an API token in env (D7). |
| §7 Payments & Products operations | **No processor** | Content and configuration only, plus read-only views of existing tables. No fabricated operations (D3). |
| Developer Center admin | Missing | Changelog and docs content; `api_keys` and `webhooks` views |
| Reports | Missing | Only from real tables |
| "Professional Tax" | **Not an existing service** | Adding it would be a new public page, which the freeze does not allow (D6) |

## 7. PROPOSED ENTERPRISE CMS ARCHITECTURE

These are the principles:
- There is one application and one database, and no framework.
- The public site stays untouched. The admin grows **beside** it.

```
public/index.php  ──► /admin/{module}/{param}   (existing dashboard area, admin + new staff roles)
                        │
                        ├─ includes/admin/shell/     new admin-only shell (head, sidebar, topbar, rail)
                        ├─ includes/admin/ui/        components: table, tabs, drawer, modal, badge, empty/error/loading
                        ├─ includes/admin/registry.php  single source of truth for modules:
                        │        slug, group, label, icon, permission, badge-query, phase-enabled
                        ├─ includes/permissions.php  (exists) user_can / require_permission
                        ├─ includes/audit.php        audit($action, $entity, $id, $old, $new) with secret redaction
                        ├─ includes/cms/…            (exists) workflow, snapshots, sanitiser, uploads
                        └─ modules/{module}/         one folder per module: page(s), queries, actions
public/assets/admin/admin.css + admin.js   design tokens, components, ⌘K palette, table behaviour (vanilla)
```

- **Module registry:** one array drives the sidebar, the command palette, route allow-listing and permission checks. A menu item exists only if the module is built (**no dead links**) and the user holds its `*.view` permission.
- **Rendering:** server-rendered PHP with progressive enhancement. Tables sort, filter and paginate server-side through query strings, so they work without JavaScript. JavaScript adds ⌘K, drawers, column visibility and remembered collapse state.
- **Charts:** hand-built inline SVG. No external library is needed, and the CSP stays `script-src 'self'`.
- **Preferences:** a `user_preferences` table, with `localStorage` only as a cache.
- **The other portals** (customer, partner, employee, HRMS) keep the current shell unchanged.
- **Data honesty rule, in code:** every KPI and widget is a `(query, source)` pair. With no source, the widget renders "Data source not connected". On a query error it renders an error state with a retry, **never 0**.

## 8. PROPOSED DATABASE CHANGES

All changes are additive, arrive as migrations with a matching rollback file, are tested on a copy first, and do not rename or drop anything.

| Phase | Change |
|---|---|
| 1 | `roles`: insert 10 staff roles (D8). `permissions`: module × action catalogue (`{module}.view/create/edit/delete/approve/publish/export/manage_settings`). `role_permissions`: default matrix (§11). `user_preferences(user_id, pref_key, value_json)`. `audit_logs`: add `actor_role VARCHAR(50) NULL`, `user_agent VARCHAR(255) NULL`; old and new values go in `meta_json.old/new`. |
| 2 | `content_versions(id, entity_type, entity_id, version_no, snapshot_json, status, created_by, created_at, note)`: every publish and every save of a published item writes a version. `blog_posts`: add `tags_json`, `featured_media_id`, `author_display`, `schema_type`. `media_assets(id, path, mime, bytes, width, height, alt, title, caption, credit, folder, uploaded_by, created_at)` and `media_usage(media_id, entity_type, entity_id)`. `cms_blocks(id, page_key, block_type, sort, visible, content_json, …workflow columns)`. |
| 3 | `redirects(id, source_path, target_path, code, status, …workflow)`: target must be an existing on-site path. The SEO JSON gains keyword, intent, schema and AEO fields (no new table). |
| 4 | `enquiries`: add `pipeline_stage ENUM(new, qualified, proposal, negotiation, won, lost)`, `source VARCHAR(40)`, `customer_id NULL`, `interest VARCHAR(40)` (payments / incorporation / …). Legacy `type` and `status` are kept. |
| 5 | `incorporation_cases(id, case_code, client_ref…, company_type, jurisdiction, stage ENUM(new, documents, filing, authority_review, incorporated, on_hold, cancelled), consultant_id, sla_due_at, …)`, `case_events` (timeline), `tasks(id, entity_type, entity_id, title, assignee_id, due_at, status, priority)`, `sla_policies(entity_type, stage, hours)`. |
| 6 | `documents(id, owner_type, owner_id, doc_type, status ENUM(pending, uploaded, under_review, verified, rejected, expired), current_version, expires_at, reviewer_id, signature_status ENUM(not_required, unsigned, pending, signed, rejected))`, `document_versions(id, document_id, version_no, path, mime, bytes, sha256, uploaded_by, created_at)`, `document_comments`. The existing five document tables stay; they are **read through a view** until a separately approved backfill (D5). |
| 7+ | Invoices, chargebacks and expenses only if a real processor or ledger is approved (D3). |

## 9. PROPOSED UI INFORMATION ARCHITECTURE

The spec's sidebar is adopted, with four adjustments:
- **Show only what exists.** Modules appear as their phase ships and the user has the permission. The full spec IA is the target, not the Phase 1 menu.
- **"Customers", "Enquiries" and "Cases" each have one home** (Operations). Dashboard widgets link into them rather than duplicating them. This follows the spec's own "do not duplicate navigation concepts" rule.
- **Payments & Products** entries are **product content and configuration pages**, not operating consoles, until a processor exists (D3).
- **Navigation / Footer editing** is read-only (frozen architecture). Changes go through the approval workflow as code-reviewed releases.

Groups: Command Center · Payments & Products · Business Services · Operations · Website & Content · SEO/AEO · Developer Center · Reports · Administration.

The sidebar is 264 px wide and collapses to a 72 px icon rail. It has search, keyboard navigation (arrow keys, Enter, `/` to focus), groups that remember their state, and live badges for approvals, new enquiries and SLA breaches.

## 10. PROPOSED DASHBOARD STRUCTURE

```
┌ Topbar: ⌘K search · Environment pill (Production · status) · Notifications · Help · User (name, role) ┐
│ Hero (collapsible, remembered): "Good afternoon, {first name} 👋" · tagline · status summary          │
├ KPI row (4) ────────────────────────────────────────────────────────────────┬ Right rail ─────────────┤
│ Content awaiting review [real: blog_posts + cms_pages by stage]              │ Environment status       │
│ New leads today         [real: enquiries today vs yesterday]                 │   (DB ping, PHP, disk;   │
│ Active incorporation    [real from Phase 5; before: "Available in Phase 5"]  │    uptime "unavailable") │
│ Website health          [real: last crawl run stored by QA job; else n/a]    │ Pending approvals        │
├ Key metrics (tabs) ── Enquiries · Content · Incorporation · Traffic* · SEO* │ Recent activity          │
│   *Traffic/SEO: "Connect Search Console / Analytics" until D7                │   (audit_logs timeline)  │
├ Pipelines: Leads (New→Won) · Incorporation (New→Incorporated)               │ System health checks     │
├ Tabs: Recent Enquiries (n) · Incorporation Cases (n) → full table component │                          │
├ Quick actions (5 max, Customize)                                            │                          │
└──────────────────────────────────────────────────────────────────────────────┴──────────────────────────┘
```

- **Figures:** the KPI values and deltas in the spec (8, 12, 6, 99.98%, 28,450) are illustrations only. None of them is rendered.
- **Development data:** a separate `demo` seed, used only on non-production databases, and bannered "Demo data".
- **Top Performing Pages:** shows "Not connected" until an analytics source exists.
- **Breakpoints:** 1440 / 1280 → the right rail drops below at 1280 → 1024: the sidebar becomes an icon rail → 768: an off-canvas sidebar, stacked widgets, and 3 quick actions → mobile: the hero collapses by default.

## 11. PROPOSED RBAC MATRIX

Legend: V = view · C = create · E = edit · D = delete · A = approve/review · P = publish · X = export · S = manage settings.

| Module ↓ / Role → | SA | Admin | Ops Mgr | Sales Mgr | Consultant | Inc. Consultant | Content Mgr | SEO Mgr | Legal/Compl. | Finance Mgr | Developer | Support |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Dashboard | V | V | V | V | V | V | V | V | V | V | V | V |
| Enquiries / Leads | all | VCE X | VCE A | VCE A X | VCE (own) | V | – | – | V | V | – | VCE |
| Customers | all | VCE X | VCE | VCE | V (own) | V (own) | – | – | V | V | – | V |
| Incorporation cases | all | VCE | VCE A X | V | – | VCE (own) | – | – | V A | V | – | V |
| Documents | all | VCE | VCE A | V | V (own) | VCE A (own) | – | – | V A | V | – | V |
| Tasks / SLA | all | VCE | VCE A | VCE | VCE (own) | VCE (own) | VCE (own) | VCE (own) | VCE (own) | VCE (own) | VCE (own) | VCE (own) |
| Pages / Blog / Media | all | VCE | – | – | – | – | VCE (submit) | V | V A | – | – | – |
| SEO / AEO / Redirects | all | V | – | – | – | – | V | VCE A | V | – | – | – |
| Regulatory content | all | V | – | – | – | – | VC | V | VCE A | – | – | – |
| Publish (any content) | P | – | – | – | – | – | – | – | – | – | – | – |
| Payments & Finance views | all | V | V | – | – | – | – | – | – | V X | V | – |
| Developer Center | all | V | – | – | – | – | V | – | – | – | VCE | – |
| Reports | all | V X | V X | V X | – | – | V | V X | V | V X | – | – |
| Users / Roles / Permissions | all | V | – | – | – | – | – | – | – | – | – | – |
| Audit / Security / System | all | V | – | – | – | – | – | – | V (audit) | – | V (system) | – |
| Configuration | S | – | – | – | – | – | – | – | – | – | – | – |

- **Publish** defaults to super admin only. It is grantable per person, as the 25 Sep CMS already does.
- **(own)** means the row is filtered by assignee in SQL, not only in the UI.
- Every check runs server-side through `require_permission()`.
- The existing admin pages get their permission checks added in Phase 1, so nothing stays role-only.

## 12. PROPOSED SEO/AEO ARCHITECTURE

- **Page SEO record** (extends the 25 Sep `cms_pages seo:` JSON):
  - title, description;
  - OG and Twitter fields;
  - primary keyword and search intent (informational / commercial / transactional / navigational);
  - internal links (validated to live on-site paths);
  - schema type from a fixed allowlist;
  - AEO answer block (question plus a 40–60 word answer);
  - FAQ;
  - entity notes.
- **Unchanged rules:**
  - Canonical is **always self** (frozen). It is shown read-only.
  - Robots stays **Default / noindex only**. The CMS can never make a governed page indexable.
  - The sitemap is still generated by `sitemap.php` and respects the gate plus CMS noindex. The admin **Sitemap** and **Robots** screens are read-only viewers with a validation report.
- **Redirects:**
  - 301/302 from paths that currently return 404 to live on-site paths;
  - a redirect over a live URL, an existing redirect, a chain or a loop is blocked;
  - workflow approval is required;
  - evaluated in `public/index.php` before the 404.
- **Content Health score (explainable).** Each score is the sum of named checks, and each failed check is shown with the fix:
  - **SEO /100:** title length, description length, one H1, indexable/canonical consistency, internal links present, no duplicate title or description.
  - **AEO /100:** answer block present and within length, question-form heading, FAQ count, FAQ schema valid.
  - **Content /100:** word count against the page type's minimum, readability, alt text, last-reviewed date.
  - **Technical /100:** HTTP 200, not in the sitemap while noindex, structured data parses, no broken links (from the stored crawl).
- **Search Console:** a connector screen showing "Not connected" until credentials are provided as environment variables (D7). No numbers are shown without it.

## 13. PROPOSED INCORPORATION CMS

- **Case list and dashboard:**
  - counts by stage (New · Documents · Filing · Authority review · Incorporated);
  - SLA breaches, computed from `sla_policies` against the stage start time;
  - "my cases".
- **Case page** (tabs): Overview · Documents · Tasks · Timeline · Payments (link to invoice or payment link records only if they exist) · Communication (logged notes and emails sent) · Notes (internal, **never** exposed to customer portals) · Compliance checklist (per company type, from configuration, **no invented legal requirements**) · Audit.
- **Company types:** Private Limited, LLP, OPC, Partnership, Other. These match the existing service slugs.
- **Jurisdiction:** India by default. Foreign jurisdictions can be selected **only** when the existing jurisdiction gate marks them served. Today none is.
- **Three separate layers:** marketing content (`/business-services/*`, code), operational workflow (these tables) and legal/regulatory information (regulatory references, with the disclaimer).

## 14. PROPOSED DOCUMENT MANAGEMENT

- **Records:** `documents` (one logical document) and `document_versions` (immutable files, SHA-256, uploader, time).
- **Status:** Pending → Uploaded → Under review → Verified / Rejected, then Expired, set automatically from `expires_at`.
- **Review:** the reviewer and a verification note are recorded; every change is audited; a visible version badge ("v3").
- **Signature status:** a field only (not required / unsigned / pending / signed / rejected), set manually. **No e-sign provider integration is claimed or built.**
- **Storage:** outside `public/`, served through an authorised download route (permission and ownership check, `Content-Disposition`, `nosniff`).
- **Validation:** the existing checks (extension, MIME, size) plus a per-document-type allowlist.
- **Existing document tables:** read through a UNION view first. Any backfill is a separate, approved migration (D5).

## 15. PROPOSED APPROVAL WORKFLOW

- **One engine** (`includes/cms/workflow.php`, generalised) for every governed entity: blog, pages, blocks, SEO, redirects, regulatory references, jurisdiction content and media metadata.
- **States:** Draft → Internal review → SEO/AEO review → Business/Legal review → Approved → Published; Rejected → Revision → back into review. Unpublish is separate.
- **Each event records:**
  - user and **role at the time**;
  - timestamp and IP;
  - action;
  - previous and new state;
  - comment (required on reject and unpublish).
- **Versions:** every publish writes a `content_versions` row. Earlier versions are viewable, can be diffed against the current one, and can be restored as a **new draft**. Nothing is overwritten.
- **Separation of duties** and **stale-edit locking** are kept, as already built.
- **Approvals inbox** (Command Center): the items where the user holds the next-stage permission, with SLA age.

## 16. SECURITY PLAN

- **Keep:**
  - CSRF on every POST;
  - hardened sessions (HttpOnly, SameSite, idle timeout, `session_version`);
  - OTP and device checks;
  - login rate limiting;
  - Turnstile (server-side keys only);
  - prepared statements everywhere;
  - `e()` output escaping;
  - CSP with nonce;
  - upload validation and re-encoding;
  - encrypted bank fields.
- **Add:**
  - `require_permission()` on every admin page and action, including existing ones;
  - row-level "own" filters in SQL;
  - audit events for login, logout, failed login, export, permission and role changes, configuration and document actions;
  - `meta_json` old/new diffs with a **redaction list** (password, token, secret, account number, OTP, API key);
  - authorised file download route;
  - export rate limit and watermark (user, time);
  - no internal notes in any customer or partner query;
  - fix `POST /api/auth/logout` CSRF (deferred item, low severity);
  - recommend `expose_php = Off`.
- **Not changed:** credentials, environment variables and production configuration.

## 17. QA PLAN

Every phase goes through this gate before it is called complete.

| Check | How |
|---|---|
| PHP syntax | `php -l` on every file |
| Migrations | Apply to a fresh schema + seed and to a copy of the current schema; run the rollback; re-apply |
| Routes | Every registry module: 200 for permitted roles, 403 or redirect for others; no dead menu links |
| RBAC | Matrix test for 12 roles × every module action (PHP test), including "own" row filters |
| Authentication, CSRF, XSS | Scripted HTTP tests (the existing e2e pattern), payload corpus against every form |
| Uploads | Fake MIME, polyglot, oversize, path traversal, direct-URL access denied |
| Public regressions | `live_qa.py` (488 checks), SEO crawl (99 sitemap URLs, canonical, robots, JSON-LD), blog, jurisdiction and anti-spam suites — must stay green because the public site is frozen |
| Accessibility | Keyboard walk-through, axe-core scan, contrast scan (existing tool) at AA |
| Responsive | Screenshots at 1440, 1280, 1024, 768 and 390; no horizontal overflow; console clean |
| Design QA | Screen-by-screen checklist from the brief's §43, scored |
| States | Every async widget checked in loading, empty, error and retry |
| Data honesty | A test fails if a widget renders a number without a query source, or shows "Healthy" without a passing check |

**Release status:** CI VERIFIED → STAGING VERIFIED → PRODUCTION AUTHORIZED, per the brief's §42.
- This environment can reach only **CI VERIFIED (local)**.
- Staging and production require your infrastructure access.

## 18. PHASED IMPLEMENTATION PLAN

Each phase is one or more separate commits with a migration and rollback, tests, docs, screenshots and a report, and each waits for your approval.

| Phase | Scope | Depends on |
|---|---|---|
| 0 | This audit ✔ | — |
| **1** | Admin shell (sidebar, topbar, ⌘K, right rail), design tokens and component library, module registry, 12 roles and permission catalogue, permission checks on **all existing admin pages**, central audit helper, user preferences, Command Center dashboard with real KPIs, honest empty/"not connected" states, and a real System Health page | D1, D8 |
| 2 | Content CMS: version history, Revision state, blog tags, author and featured image, media library, structured blocks for CMS-managed sections, regulatory content editor | 1 |
| 3 | SEO/AEO: extended page SEO, Content Health, redirects, sitemap and robots viewers, schema validator, Search Console connector (if D7) | 2 |
| 4 | CRM: leads pipeline, Customer 360, enquiry table (bulk, sort, columns), assignment, communications log | 1, D4 |
| 5 | Incorporation operations: cases, stages, tasks, SLA, timeline, checklist | 4 |
| 6 | Documents: unified versioned documents, review, expiry, signature field, secure download | 5, D5 |
| 7 | Payments and finance: product content and configuration, read-only views of existing tables; operations only after D3 | 1, D3 |
| 8 | Developer Center: changelog, docs content, API key and webhook admin (existing tables) | 1 |
| 9 | Reports: from real tables only; CSV export with audit | 4–8 |
| 10 | Security hardening, performance, full QA, runbook | all |

## 19. FILES THAT WOULD CHANGE

**Phase 1 (new):**
- `includes/admin/shell/{head,sidebar,topbar,rail,foot}.php`
- `includes/admin/ui/*.php` (table, tabs, drawer, modal, badge, states, chart-svg)
- `includes/admin/registry.php`
- `includes/admin/dashboard-data.php`
- `includes/admin/health.php`
- `includes/audit.php`
- `admin/command-center.php` (dashboard), `admin/approvals.php`, `admin/activity.php`, `admin/system-health.php`, `admin/roles.php`
- `public/assets/admin/admin.css`, `public/assets/admin/admin.js`
- `database/migrations/2026-10-xx-phase1-rbac-shell.sql` and `…-rollback.sql`
- `tests/admin-rbac-test.php`, `tests/admin-shell-test.php`

**Phase 1 (modified):**
- `public/index.php`: the admin area allow-list comes from the registry, and staff roles are added to the admin area.
- `includes/dashboard-head.php` and `includes/dashboard-foot.php`: branch to the new shell **only when `$dashboard_area === 'admin'`**.
- Each existing `admin/*.php`: a `require_permission()` line and new shell classes. Their logic is not changed.
- `includes/auth.php`: login and logout audit calls only.
- `database/seed.sql`: roles and permissions, idempotent.
- Later phases add their own `modules/` folders and migrations.

## 20. FILES THAT MUST REMAIN FROZEN

- **Public site:**
  - `pages/**` (every public template);
  - `includes/header.php`, `includes/footer.php`, `includes/site-head.php`, `includes/site-foot.php`;
  - the public route blocks in `public/index.php`;
  - `public/sitemap.php`, `public/robots.txt`, `public/.htaccess`;
  - `public/assets/css/main.css`, `blog.css`, `business-services.css`, `about.css`, `standalone.css`;
  - `public/assets/js/main.js`, `business-services.js`, `floating-enquiry.js`.
- **Content and governance:**
  - `includes/content-governance.php` (gate rules);
  - `includes/business-services.php` (jurisdiction gate);
  - `includes/regulatory-context.php`;
  - `includes/blog/articles/*`.
- **Security and integrations:** `includes/anti-spam.php` and `api/**`, except the logout CSRF fix when approved.
- **Other portals:** `customer/**`, `partner/**`, `employee/**`, `hrms/**`, `api/partner/**`.
- **Configuration:** `config/config.php` (never committed); production credentials and environment variables.
- **Other:** `pages/header.php` (dormant; not deleted); the legal pages (pending legal review).

## 21. RISKS

| Risk | Mitigation |
|---|---|
| Shared dashboard shell change affects the customer and partner portals | The new shell is used only for the admin area; screenshot regressions of every portal |
| Adding permission checks to existing admin pages locks out current admins | The migration grants the `admin` role today's effective access; super admin is unchanged; tested per page |
| Spec asks for metrics with no data source (traffic, CTR, revenue, uptime) | "Not connected" / "Status unavailable" states; connectors only with approved credentials |
| Scope size (≈40 modules) versus quality target | Strict phases; show only built modules; one design system reused everywhere |
| Freeze conflicts (global-incorporation URLs, Professional Tax page, navigation editing) | Flagged as D2, D6 and D9; nothing changes without your decision |
| Customer model requires a login (`customers.user_id NOT NULL`) | D4: pick the CRM client model before Phase 4 |
| Document tables spread across 5 tables | View first; backfill only after approval |
| Public pages now query the CMS (since 25 Sep) | Already fail-safe; Phase 10 adds snapshot caching if needed |
| No staging or production access from this environment | Only CI VERIFIED is claimable here; you run staging and production validation |

## 22. ROLLBACK PLAN

- **Code:** every phase is its own commit range on the branch. Revert the range, or redeploy the previous zip; each release keeps the previous package.
- **Database:** every migration ships with a `-rollback.sql` that drops **only** objects that phase added (new tables and columns, seeded roles and permissions by slug). It is tested apply → rollback → re-apply on a copy. A full backup is taken before running anything in production.
- **Feature flag:** `settings.admin_shell_v2 = on/off` switches the admin area back to the current shell without redeploying.
- **Public site:** unaffected by design. `live_qa.py` after every release confirms it.

## 23. ACCEPTANCE CRITERIA

**Per phase:**
1. Every QA gate in §17 passes, with results attached (test counts, crawl, screenshots at 5 widths, accessibility and contrast reports).
2. No public regression: the sitemap stays at 99 URLs and `live_qa` is PASS with no changes to frozen files.
3. Every visible number traces to a named query or connector. There is no fabricated data, and no "Healthy" without a check.
4. Every admin action is permission-checked server-side and covered by the RBAC matrix test, and every sensitive action appears in the audit log with old/new values and no secrets.
5. Every screen has loading, empty, error and retry states, works by keyboard, meets AA contrast and has no horizontal overflow from 390 to 1440 px.
6. Design QA score ≥ 9.8/10 against the reference and the §43 checklist, with the scoring sheet included.
7. The migration and rollback are both verified; docs and runbook are updated; the phase is committed separately; the status is reported as CI VERIFIED / STAGING VERIFIED / PRODUCTION AUTHORIZED.

---

### Decisions needed

| ID | Decision | Recommendation |
|---|---|---|
| D1 | Dashboard reference image: it was not received | Re-attach it; until then Phase 1 follows the written spec and the current brand tokens |
| D2 | `/global-incorporation/{country}` versus the frozen `/business-services/jurisdictions/{slug}` | Keep the existing URLs (frozen, already gated); no new URLs |
| D3 | Payments & Finance "operations" with no processor integrated | Phase 7 = product content and configuration plus read-only views of existing tables only |
| D4 | CRM client without a login | Add a nullable relation from cases and enquiries to `customers`, and allow `customers.user_id` NULL (one constraint change, approved separately), **or** a separate `crm_accounts` table |
| D5 | Unify the 5 document tables | View first, backfill later with approval |
| D6 | "Professional Tax" is not an existing service | Hold: a new public page breaks the freeze |
| D7 | Analytics / Search Console / Cloudflare purge credentials | Provide as server environment variables when ready; until then "Not connected" |
| D8 | Role list: "Sales / Travel / Business Consultant" | Confirm whether "Travel" is intended; proposed single role "Consultant" |
| D9 | Navigation / Footer editing in the CMS | Read-only in the CMS; changes stay code-reviewed (frozen) |

**ARCHITECTURE STATUS:** READY FOR IMPLEMENTATION (Phase 1 only). Later phases are blocked on D2–D9 as listed.

**RECOMMENDED NEXT PHASE:** Phase 1. This covers:
- the admin shell, design system and module registry;
- 12 roles and the permission catalogue, with permission checks added to every existing admin page;
- the central audit helper;
- the Command Center dashboard with real data and honest empty and not-connected states;
- a real System Health page.

It does not touch any public file.
