# Paynancial CMS — editing layer (25 Sep 2026)

Built inside the existing PHP + MySQL admin (Option 1). No framework, no
second application or database, no content replication.

**Status: implemented and tested locally. Not deployed. Live validation pending.**

## What the CMS edits

| Area | Admin page | Public effect |
|---|---|---|
| Blog articles (5 approved topics) | Admin → Content (CMS) → Blog Articles | `/blog/{slug}` shows the **published snapshot** |
| Homepage hero | Admin → Content (CMS) → Homepage Hero | Hero on `/`; the hardcoded hero is the fallback |
| Per-page SEO (22 core pages) | Admin → Content (CMS) → Page SEO | Title, description, social tags, optional **noindex** |

Outside the CMS (reviewed code changes only): header, footer, URLs, sitemap
architecture, canonical/robots strategy, Regulatory Insights articles
(source-first), foreign-jurisdiction pages, Privacy Policy and Terms.

## Workflow

`DRAFT → EDITORIAL REVIEW → SEO/AEO REVIEW → BUSINESS/LEGAL REVIEW → APPROVED → PUBLISHED`

- Every step is checked server-side (`includes/cms/workflow.php`) and written to `audit_logs`; the editor shows it as the approval history.
- Any review stage can reject to Draft. A reason is required.
- Only Approved content can be published, and only after ticking a confirmation box. Unpublish also needs a reason and a confirmation.
- Publishing freezes a snapshot (`published_json`). Edits made after approval or publication send the working copy back to Draft and clear the approval. The live snapshot stays until a new version is published.
- Outside super admin, nobody can review, approve or publish their own submission.
- Concurrent edits are detected (`lock_version`); a stale save is rejected.

## Permissions (`includes/permissions.php`)

| Permission | Default |
|---|---|
| `cms.view`, `cms.create`, `cms.edit`, `cms.submit` | admin role + super admin |
| `cms.review.editorial`, `cms.review.seo`, `cms.approve`, `cms.publish`, `cms.unpublish`, `cms.seo.manage` | super admin; granted to individual admins at Admin → CMS Overview → CMS access |

Per-person grant/revoke uses `user_permissions`. A revoke beats the role grant.

## Indexing rules

- A new CMS article is published **noindex** unless someone with `cms.seo.manage` ticks "indexable" and the article then goes through the whole workflow again. It enters the sitemap only when it is both indexable and has "sitemap" ticked.
- Page SEO robots can only be *Default* or *noindex*. The CMS can remove a page from the index and the sitemap. It can never make a governed page indexable. Canonical URLs are not editable.
- Blog topic pages follow the existing rules in `includes/blog.php`.

## Safety and fallback

- The public site reads only published snapshots (`includes/cms/public.php`). Drafts and legacy `cms_pages` values are never shown.
- With no database, or with the migration not yet run, the site renders exactly from code: the file articles, the hardcoded hero and the template SEO.
- Article HTML goes through an allowlist (`includes/cms/sanitize.php`). Scripts, styles, iframes, forms, event handlers, inline styles and non-http(s) links are removed. Everything else is plain text escaped on output.
- Images: JPEG, PNG or WebP only, up to 2 MB and 200–4000 px. The type is checked with finfo and getimagesize. Images are re-encoded with GD and stored under a random name in `public/uploads/cms/`, where script execution is disabled.
- All forms carry CSRF protection, and every query is a prepared statement.

## The 16 approved articles

They remain version-controlled files and stay live exactly as they are.
Importing them into the CMS is optional: `php tools/cms-import-blog.php`
(dry run) and then `--apply`, or Admin → CMS Overview → Import (requires
`cms.publish`). An import stores each article as Published, with a snapshot
identical to its file, so its page, approval record (Paynancial Editorial
Team, 24 Sep 2026), indexing and sitemap entry do not change. This was
verified locally: all 16 article pages, `/blog`, the topic pages, the
homepage and the sitemap were byte-identical before and after. After an
import the CMS copy is the one shown, so later edits to the files have no
effect.

## Installing (local or staging first)

1. Back up the database.
2. Run `database/migrations/2026-09-25-cms-editing.sql` **once**. It is additive: it adds columns, indexes, CHECK constraints and permissions, and removes nothing.
3. Upload the files. Keep `public/uploads/.htaccess`, and make sure `public/uploads/` is writable by PHP.
4. Optional: import the file articles (see above).

## Tests

    CMS_TEST_WRITE=1 php tests/cms-test.php   # local/dev DB only; refuses APP_ENV=production

It covers the sanitiser, the lossless editor round trip for every article
file, RBAC, every transition and guard, publish and unpublish, import, hero
fallback, SEO noindex-only, the no-database fallback and upload validation.
It cleans up after itself.
