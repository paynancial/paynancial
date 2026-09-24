# Paynancial Insights (blog) — publishing guide

Decisions of 23–24 Sep 2026. Accuracy over publishing speed; source-backed
content over AI-generated assumptions.

## Where things live

| What | Where |
|---|---|
| Article registry, statuses, gate rules | `includes/blog.php` |
| One file per article | `includes/blog/articles/{slug}.php` |
| Hub, category and article templates | `pages/blog.php`, `pages/blog/category.php`, `pages/blog/article.php` |
| Styles | `public/assets/css/blog.css` |
| Regulatory article template (not loaded) | `docs/templates/regulatory-article.php` |
| Admin view | Admin → Content Governance → "Blog / Insights — articles" |
| Tests | `php tests/blog-gate-test.php` |

## CMS status (every article)

`draft → in_review → approved → published → indexable`

- **draft** — not live.
- **in_review / approved / published** — live at `/blog/{slug}`, `noindex, follow`, not in the sitemap.
- **indexable** — indexable only when `indexable = true` **and** `approved_by` + `approved_on` are recorded. Enters the XML sitemap only with `sitemap = true` as well.

Articles are never indexed automatically on publish. Final gate: content
approval + source verification (regulatory) + SEO/AEO review + indexing
approval.

`/blog` and `/blog/category/*` stay noindex until a separate blog-level
quality review — the number of articles alone never switches them on.
Nothing is blocked in `robots.txt`, so crawlers can see the noindex.

## Regulatory articles (source-first)

Workflow: source uploaded → source extraction → source verification →
article draft → editorial review → regulatory review → SEO/AEO review →
approved → published. If the source cannot be verified or is incomplete:
**source verification pending — do not publish**.

- Written **only** from the official document the business supplies (PDF,
  source text or an accessible official URL).
- Never reconstructed from memory: circular / notification numbers, dates,
  requirements, provisions, effective dates, penalties, thresholds,
  obligations. Missing facts are flagged, not filled in.
- Source record (all required before the article can go live): regulator,
  instrument type, number, title, issue date, effective date, official
  source, source document, last verified, reviewer, status = verified.
- Structure: what changed · who it affects · what the regulator says · what
  it means in practice · what businesses should do · what customers should
  know · Paynancial relevance · important dates · FAQs · official source.
- Official requirement (`.blog-official` blocks), Paynancial's explanation
  and professional advice are kept visibly separate. Not legal, tax or
  financial advice.

## General articles

May be written independently when they make no regulatory claims. Any
regulatory statement must be sourced separately. No invented statistics;
Paynancial products are described only as documented on their own product
pages, and links go only to approved (indexable) pages.

## Current library

16 articles — **Indexable**, in the sitemap. Approved by Paynancial
Editorial Team on 24 Sep 2026 (content + indexing approval).

Payments (5) · Business Finance (4) · Fintech & AI (2) · Developer
Education (3) · Learning & Development (2).

Blog-level review approved 24 Sep 2026: `/blog` and the five topic pages
above are indexable and in the sitemap. A topic page stays indexable only
while it has at least one indexable article.

Regulatory Insights: no articles yet — topic page noindex, awaiting
official source documents.
