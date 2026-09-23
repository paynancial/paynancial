# SEO P0 — URL-level before/after audit

Baseline: commit `f582847` (before P0). After: this commit. Both served locally
from the same code and compared URL by URL (redirects not followed).
"Main content" compares the `<main>` element only, since the footer changed
on every page as part of the footer redesign.

The live site could not be reached from the audit environment, so HTTPS/host
redirects (http→https, www→apex) still need verifying on production.

## What P0 changed

| Change | URLs affected | Why |
|---|---|---|
| Canonical built from the clean path (no query string, no trailing slash) | All pages using the default canonical | 40+ `/contact?product=…` URLs and `/{page}/` variants each declared themselves canonical |
| 301 from trailing-slash URL to slash-less URL (GET/HEAD, query kept, `/api` excluded) | Every `/{page}/` | One URL per page |
| `/login` canonical → `/` | `/login` | It renders the homepage; not deindexed |
| Real 404 when a solutions template is missing | `/solutions/startups`, `/solutions/saas` | Were empty 200 pages (soft 404) |
| Solutions cards for Startups/SaaS link to sales enquiry like their siblings | `/solutions` | Removed links to the 404s |
| Route fix `agentic-ai` → `agenticai.php` | `/agentic-ai` | Footer linked a 404; page existed. Mobile overflow on its shared legal-grid layout fixed too |
| `noindex` meta | `/forgot-password` (`noindex, follow`), `/reset-password`, `/signup/verify` (`noindex, nofollow`) | Utility pages |
| `noindex` meta + `X-Robots-Tag: noindex, nofollow` | `/pay/{ref}` | Customer payment-link pages are private |
| `noindex, follow` + FAQ markup removed | 15 `/business-services/jurisdictions/{slug}` pages | Pending the Jurisdiction Approval Matrix (`docs/jurisdiction-approval-matrix.md`) |
| Sitemap lists every indexable public page with `<lastmod>` | `/sitemap.xml` | Was 12 hard-coded URLs; now 27. Business Services pages not yet included (pending approval) |

**Deliberately not deindexed:** `/signup` (legitimate brand query target),
`/contact` and every marketing page. No existing indexable marketing page
received `noindex`.

## URL table

| URL | Before | After | Canonical before → after | Robots after | Main content |
|---|---|---|---|---|---|
| `/` | 200 | 200 | / | index (default) | changed |
| `/about` | 200 | 200 | /about | index (default) | changed |
| `/about/` | 200 | 301 → /about | /about/ → — | index (default) | changed |
| `/products` | 200 | 200 | /products | index (default) | unchanged |
| `/products/payment-gateway` | 200 | 200 | /products/payment-gateway | index (default) | changed |
| `/products/payment-links` | 200 | 200 | /products/payment-links | index (default) | changed |
| `/products/payment-collection` | 200 | 200 | /products/payment-collection | index (default) | changed |
| `/products/payouts` | 200 | 200 | /products/payouts | index (default) | changed |
| `/products/payment-analytics` | 200 | 200 | /products/payment-analytics | index (default) | changed |
| `/products/nope` | 404 | 404 | /products/nope | index (default) | unchanged |
| `/solutions` | 200 | 200 | /solutions | index (default) | changed |
| `/solutions/startups` | 200 | 404 | /solutions/startups | index (default) | changed |
| `/solutions/saas` | 200 | 404 | /solutions/saas | index (default) | changed |
| `/pricing` | 200 | 200 | /pricing | index (default) | unchanged |
| `/developers` | 200 | 200 | /developers | index (default) | unchanged |
| `/agentic-ai` | 404 | 200 | /agentic-ai | index (default) | changed |
| `/technology` | 200 | 200 | /technology | index (default) | unchanged |
| `/trust` | 200 | 200 | /trust | index (default) | unchanged |
| `/security` | 200 | 200 | /security | index (default) | unchanged |
| `/support` | 200 | 200 | /support | index (default) | unchanged |
| `/contact` | 200 | 200 | /contact | index (default) | unchanged |
| `/contact?intent=sales&product=upi-payments` | 200 | 200 | /contact?intent=sales&product=upi-payments → /contact | index (default) | unchanged |
| `/careers` | 200 | 200 | /careers | index (default) | unchanged |
| `/leadership` | 200 | 200 | /leadership | index (default) | unchanged |
| `/partners` | 200 | 200 | /partners | index (default) | unchanged |
| `/partner/register` | 200 | 200 | /partner/register | index (default) | unchanged |
| `/blog` | 200 | 200 | /blog | index (default) | unchanged |
| `/login` | 200 | 200 | /login → / | index (default) | changed |
| `/signup` | 200 | 200 | /signup | index (default) | unchanged |
| `/forgot-password` | 200 | 200 | /forgot-password | noindex, follow | unchanged |
| `/legal/privacy-policy` | 200 | 200 | /legal/privacy-policy | index (default) | unchanged |
| `/legal/terms-conditions` | 200 | 200 | /legal/terms-conditions | index (default) | unchanged |
| `/legal/refund-policy` | 200 | 200 | /legal/refund-policy | index (default) | unchanged |
| `/legal/cookie-policy` | 200 | 200 | /legal/cookie-policy | index (default) | unchanged |
| `/sitemap.php` | 200 | 200 | — | index (default) | changed |
| `/robots.txt` | 200 | 200 | — | index (default) | unchanged |
| `/business-services` | 200 | 200 | /business-services | index (default) | unchanged |
| `/business-services/` | 200 | 301 → /business-services | /business-services → — | index (default) | changed |
| `/business-services/company-incorporation` | 200 | 200 | /business-services/company-incorporation | index (default) | changed |
| `/business-services/global-incorporation` | 200 | 200 | /business-services/global-incorporation | index (default) | changed |
| `/business-services/jurisdictions` | 200 | 200 | /business-services/jurisdictions | index (default) | changed |
| `/business-services/jurisdictions?region=asia` | 200 | 200 | /business-services/jurisdictions | index (default) | changed |
| `/business-services/jurisdictions/uae` | 200 | 200 | /business-services/jurisdictions/uae | noindex, follow | changed |
| `/business-services/jurisdictions/singapore` | 200 | 200 | /business-services/jurisdictions/singapore | noindex, follow | changed |
| `/customer` | 302 → /?login=required | 302 → /?login=required | — | index (default) | unchanged |
| `/nonexistent` | 404 | 404 | /nonexistent | index (default) | unchanged |

## Regression checks

- Every "changed" marketing page was diffed: `/products/payment-links`,
  `/payment-collection`, `/payouts`, `/payment-analytics` differ only by
  whitespace from the shared template; `/` and `/solutions` differ only by the
  approved Business Services cross-link (and, on `/solutions`, the two card
  links above); `/products/payment-gateway` only by its cross-link.
- 152 unique internal links on 14 key pages crawled: 0 broken, 0 missing anchors.
- No horizontal overflow at 1440 / 1024 / 768 / 390 / 360 px on the key pages.
- Payment, authentication, API and database code untouched.
