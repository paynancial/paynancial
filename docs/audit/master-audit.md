# Paynancial — Master Regulatory SEO + AEO Audit

**Audit date:** 23 September 2026 · **Branch:** `claude/laughing-clarke-u06pou` at `26c04a2` · **Status:** AUDIT + PROPOSAL (23 Sep 2026). **P0 approved decisions implemented** — see the section at the end.

Machine-readable inventory: [`docs/audit/url-inventory.csv`](url-inventory.csv) (111 URLs, all brief §3 columns).

---

## 0. Scope and limits — read first

| Area | Audited? | How |
|---|---|---|
| Every public URL the site serves | **Yes** | Full crawl of the repository build (111 URLs), metadata, canonicals, robots, schema, links, word counts, 5-word-shingle overlap for every page pair |
| Header, mega menus, footer, floating enquiry | **Yes** | From the rendered pages |
| robots.txt, sitemap, redirects, 404s, query parameters, trailing slashes | **Yes** | Direct requests |
| Live site `paynancial.com` | **No** | Environment network policy denies the host. Live-only differences (server config, CDN, deployed version) are **not verified** |
| CMS `cms.paynancial.com` | **No** | Host denied. The CMS code in the repo (`admin/cms.php`, `database/schema.sql`) was reviewed instead |
| Official regulator / government sources | **No** | `rbi.org.in`, `npci.org.in`, `mca.gov.in`, `u.ae`, `acra.gov.sg` all denied. **No regulatory statement could be source-verified in this pass** |
| Google Search Console | **No** | No access. No ranking, impression or query data is reported or implied (§49) |
| Page speed | **Partial** | No Lighthouse / field data. Pages are server-rendered PHP with one CSS bundle and no third-party scripts beyond Google Fonts |

**To unblock source verification**, allow these domains in the environment's Network access settings (or use a broader access level): `rbi.org.in`, `npci.org.in`, `mca.gov.in`, `incometax.gov.in`, `cbic.gov.in`, `gst.gov.in`, `ipindia.gov.in`, `startupindia.gov.in`, `udyamregistration.gov.in`, `u.ae`, `tax.gov.ae`, `acra.gov.sg`, `iras.gov.sg`, `cr.gov.hk`, `ird.gov.hk`, `gov.uk`, plus `paynancial.com` and `cms.paynancial.com`.

---

## Executive summary

**Technically, the site is healthy.** 111 URLs, all 200; 93 in the sitemap, every one indexable and self-canonical; 18 intentionally noindex; no canonical conflicts, no noindex-in-sitemap, no duplicate titles or descriptions, exactly one H1 per page, Open Graph on every page, no images without alt text, one-hop 301s for trailing slashes and moved URLs, query-parameter URLs canonicalise to the clean URL. No tag, category, topic or search archives exist.

**The real risks are content governance, not technology:**

1. **P0 — Regulatory statements are not source-verified.** The "Compliance in India" bands (21 RBI / NPCI / statutory references, shown on ~50 pages) and the "In India" bands were written from general knowledge, with no reference numbers, dates, official URLs, last-verified dates or reviewer. That is exactly what §6, §8, §9 and §47 prohibit. They carry the right disclaimers and make no licence claims, but they must be verified or withdrawn.
2. **P0 decision — 16 indexed pages describe capabilities Paynancial has not confirmed** (10 product guides, 6 AI capability pages). They were made indexable at your request; §37 ("service/claim is verified") says they should not be. They are honest about availability, but you need to choose.
3. **P1 — Jurisdiction pages are copy-and-replace** (78–83% identical to each other). Correctly noindex today; they need rebuilding from official sources before any is approved (§20, §46).
4. **P1 — India Business Services pages share 46–62% of their text** and lack eligibility, the named authority, post-registration compliance, common mistakes, official sources and review dates (§14).
5. **P1 — The blog is an empty indexable page** with no article URLs (`/blog/{slug}` does not exist), so published posts could never be indexed individually.

No prohibited claims were found: nothing says "RBI approved", "Government approved", "#1", "best", "guaranteed compliance", or asserts a licence, certification or partnership.

---

## 1. Complete URL inventory

Full table: [`url-inventory.csv`](url-inventory.csv). Summary:

| Page type | URLs | Indexable | Recommended |
|---|---|---|---|
| Product / capability / category | 16 | 16 | KEEP |
| Product pillar | 3 | 3 | KEEP |
| Product guide (capability unconfirmed) | 10 | 10 | **DECISION** |
| AI capability (on request) | 6 | 6 | **DECISION** |
| AI / Agentic | 5 | 5 | KEEP |
| Developer docs | 9 | 9 | KEEP |
| Solution / industry | 9 | 9 | KEEP |
| Business Services hub + India services | 15 | 14 | 13 IMPROVE, 1 KEEP NOINDEX (trademark search), hub IMPROVE |
| Global incorporation + jurisdiction directory | 2 | 0 | KEEP NOINDEX |
| Jurisdictions | 15 | 0 | IMPROVE, keep NOINDEX |
| Company / marketing | 11 | 11 | KEEP; 5 IMPROVE (thin) ; blog NOINDEX |
| Legal | 4 | 4 | KEEP |
| Resources | 2 | 2 | KEEP |
| Utility / form | 3 | 3 | `/signup` NOINDEX |
| Homepage | 1 | 1 | KEEP |

Every URL that is **not** plain KEEP:

| URL | Page Type | Indexable | Sitemap | Words | Max Overlap | Recommended Action | Reason |
|---|---|---|---|---|---|---|---|
| /ai-intelligence/cash-flow-intelligence | AI capability (on request) | Yes | Yes | 959 | 38% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /ai-intelligence/financial-assistant | AI capability (on request) | Yes | Yes | 1012 | 32% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /ai-intelligence/fraud-detection | AI capability (on request) | Yes | Yes | 1097 | 30% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /ai-intelligence/paynancial-ai | AI capability (on request) | Yes | Yes | 1135 | 32% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /ai-intelligence/reconciliation | AI capability (on request) | Yes | Yes | 1028 | 33% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /ai-intelligence/revenue-forecasting | AI capability (on request) | Yes | Yes | 879 | 38% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /business-services/annual-compliance | Business Service | Yes | Yes | 504 | 46% | IMPROVE | Shared template text 46%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/company-changes | Business Service | Yes | Yes | 520 | 48% | IMPROVE | Shared template text 48%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/global-incorporation | Business Service | No | No | 893 | 39% | KEEP NOINDEX | No jurisdiction approved yet (served_confirmed + content_verified) |
| /business-services/gst-registration | Business Service | Yes | Yes | 589 | 58% | IMPROVE | Shared template text 58%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/llp-registration | Business Service | Yes | Yes | 601 | 60% | IMPROVE | Shared template text 60%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/msme-registration | Business Service | Yes | Yes | 547 | 57% | IMPROVE | Shared template text 57%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/opc-registration | Business Service | Yes | Yes | 564 | 62% | IMPROVE | Shared template text 62%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/pan-tan-assistance | Business Service | Yes | Yes | 554 | 58% | IMPROVE | Shared template text 58%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/partnership-registration | Business Service | Yes | Yes | 560 | 57% | IMPROVE | Shared template text 57%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/private-limited-company | Business Service | Yes | Yes | 614 | 62% | IMPROVE | Shared template text 62%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/roc-compliance | Business Service | Yes | Yes | 517 | 48% | IMPROVE | Shared template text 48%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/startup-registration | Business Service | Yes | Yes | 574 | 57% | IMPROVE | Shared template text 57%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/trademark-registration | Business Service | Yes | Yes | 589 | 49% | IMPROVE | Shared template text 49%; add eligibility, authority, post-registration compliance, common mistakes, official sources, last reviewed (§14) |
| /business-services/trademark-search | Business Service | No | No | 492 | 49% | KEEP NOINDEX | Duplicates Trademark Registration intent |
| /business-services | Business Services hub | Yes | Yes | 922 | 68% | IMPROVE | High overlap with directory/global copy |
| /blog | Company / marketing | Yes | Yes | 41 | 0% | NOINDEX until posts exist | 41 words, no posts; no /blog/{slug} route exists yet |
| /careers | Company / marketing | Yes | Yes | 108 | 1% | IMPROVE | Under 400 words |
| /leadership | Company / marketing | Yes | Yes | 370 | 2% | IMPROVE | Under 400 words |
| /partners | Company / marketing | Yes | Yes | 313 | 6% | IMPROVE | Under 400 words |
| /pricing | Company / marketing | Yes | Yes | 335 | 1% | IMPROVE | Under 400 words |
| /support | Company / marketing | Yes | Yes | 347 | 3% | IMPROVE | Under 400 words |
| /business-services/jurisdictions/british-virgin-islands | Jurisdiction | No | No | 795 | 83% | IMPROVE (keep NOINDEX) | Copy-and-replace template (83% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/cayman-islands | Jurisdiction | No | No | 776 | 83% | IMPROVE (keep NOINDEX) | Copy-and-replace template (83% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/cyprus | Jurisdiction | No | No | 750 | 81% | IMPROVE (keep NOINDEX) | Copy-and-replace template (81% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/guernsey | Jurisdiction | No | No | 753 | 81% | IMPROVE (keep NOINDEX) | Copy-and-replace template (81% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/hong-kong | Jurisdiction | No | No | 756 | 81% | IMPROVE (keep NOINDEX) | Copy-and-replace template (81% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/ireland | Jurisdiction | No | No | 748 | 81% | IMPROVE (keep NOINDEX) | Copy-and-replace template (81% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/luxembourg | Jurisdiction | No | No | 751 | 82% | IMPROVE (keep NOINDEX) | Copy-and-replace template (82% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/malaysia | Jurisdiction | No | No | 732 | 82% | IMPROVE (keep NOINDEX) | Copy-and-replace template (82% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/mauritius | Jurisdiction | No | No | 699 | 78% | IMPROVE (keep NOINDEX) | Copy-and-replace template (78% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/puerto-rico | Jurisdiction | No | No | 779 | 80% | IMPROVE (keep NOINDEX) | Copy-and-replace template (80% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/saint-vincent-and-the-grenadines | Jurisdiction | No | No | 834 | 80% | IMPROVE (keep NOINDEX) | Copy-and-replace template (80% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/saudi-arabia | Jurisdiction | No | No | 732 | 79% | IMPROVE (keep NOINDEX) | Copy-and-replace template (79% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/singapore | Jurisdiction | No | No | 734 | 82% | IMPROVE (keep NOINDEX) | Copy-and-replace template (82% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/uae | Jurisdiction | No | No | 751 | 79% | IMPROVE (keep NOINDEX) | Copy-and-replace template (79% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions/united-kingdom | Jurisdiction | No | No | 777 | 82% | IMPROVE (keep NOINDEX) | Copy-and-replace template (82% overlap); rebuild from verified official sources before any indexing (§20, §46) |
| /business-services/jurisdictions | Jurisdiction directory | No | No | 531 | 68% | KEEP NOINDEX | No jurisdiction approved yet (served_confirmed + content_verified) |
| /products/embedded-billing | Product guide (capability unconfirmed) | Yes | Yes | 1168 | 33% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/embedded-payments | Product guide (capability unconfirmed) | Yes | Yes | 1303 | 28% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/embedded-payouts | Product guide (capability unconfirmed) | Yes | Yes | 1241 | 33% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/expense-management | Product guide (capability unconfirmed) | Yes | Yes | 1118 | 27% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/international-payments | Product guide (capability unconfirmed) | Yes | Yes | 1163 | 22% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/invoice-management | Product guide (capability unconfirmed) | Yes | Yes | 1173 | 24% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/payment-pages | Product guide (capability unconfirmed) | Yes | Yes | 1262 | 38% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/split-payments | Product guide (capability unconfirmed) | Yes | Yes | 1225 | 27% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/wallet-infrastructure | Product guide (capability unconfirmed) | Yes | Yes | 1159 | 25% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /products/white-label-payments | Product guide (capability unconfirmed) | Yes | Yes | 1185 | 28% | DECISION: NOINDEX per §37 or keep indexed | Indexed on owner request; capability not verified (§37.3) |
| /signup | Utility / form | Yes | Yes | 71 | 6% | NOINDEX + remove from sitemap | Utility form, no inbound links |

Nothing is recommended for DELETE or REDIRECT. Nothing has been changed.

## 2. Tag / taxonomy page inventory

| Pattern | Exists? | Result |
|---|---|---|
| `/tag/`, `/tags/`, `/category/`, `/categories/`, `/topic/`, `/topics/` | No route | 404 |
| `/search?q=` | No route | 404 |
| Blog pagination `?page=` | Parameter ignored | 200, canonical `/blog` — harmless |
| Jurisdiction filters `?region=` (6 values, linked from the directory) | Yes | `noindex`, canonical `/business-services/jurisdictions` — correct |
| Enquiry parameters `/contact?intent=…&product=…` (162 links) | Yes | canonical `/contact` — correct |
| `/?login=customer` | Yes | canonical `/` — correct |
| Product "category" pages (`/products/accept-and-collect`, pillars) | Yes | These are hand-written category pages with unique content, not taxonomy archives — KEEP |

**Finding:** no thin, empty or duplicate taxonomy pages exist. **Recommendation:** if the blog later gets categories or tags, render them `noindex, follow` until each has enough posts to be useful on its own, and never link them sitewide.

## 3. Thin-page inventory

| Priority | URL | Words | Issue | Recommendation |
|---|---|---|---|---|
| P1 | `/blog` | 41 | Empty "no articles yet" page, indexable, in sitemap | NOINDEX until the first posts exist; add `/blog/{slug}` article pages (see §9) |
| P1 | `/signup` | 71 | Account form, indexable, in sitemap, no inbound links | NOINDEX, remove from sitemap |
| P2 | `/careers` | 108 | Very short; no open roles described | IMPROVE with real roles or hiring approach, else NOINDEX |
| P2 | `/pricing` | 335 | Short for a commercial page | IMPROVE only with verified pricing facts |
| P2 | `/partners`, `/partner/register`, `/support`, `/leadership` | 313–370 | Short | IMPROVE opportunistically |
| P3 | `/contact` | 58 | Utility page — thinness is expected | KEEP |

Definition-only / doorway check: no page is a keyword-swapped doorway **except the jurisdiction template** (see §4), which is already noindex.

## 4. Duplicate-page inventory

| Cluster | Pages | Overlap | Status | Recommendation |
|---|---|---|---|---|
| Jurisdiction pages | 15 | 78–83% with each other | noindex | Rebuild each from its own official sources (§12); until then keep noindex. Do **not** approve any jurisdiction on the current template. |
| India Business Services | 13 | 46–62% | indexed | IMPROVE: reduce shared template copy; add service-specific eligibility, authority, post-registration duties, common mistakes, official sources (§14) |
| Business Services hub vs directory / global | 3 | 68% | hub indexed | IMPROVE hub copy so it is not a subset of the directory |
| Payout use-case pages (bulk, vendor, employee, partner) | 4 | 42–47% **including** the shared regulatory band; ≤28% without it | indexed | KEEP; if the regulatory band is kept, vary it per page |
| Trademark Search vs Trademark Registration | 2 | 49% | search is noindex | KEEP as is |

All other page pairs are under 40%.

## 5. Existing Business Services inventory

| URL | Indexable | Sitemap | Words | Max Overlap | Recommended Action |
|---|---|---|---|---|---|
| /business-services/annual-compliance | Yes | Yes | 504 | 46% | IMPROVE |
| /business-services/company-changes | Yes | Yes | 520 | 48% | IMPROVE |
| /business-services/company-incorporation | Yes | Yes | 1017 | 41% | KEEP |
| /business-services/global-incorporation | No | No | 893 | 39% | KEEP NOINDEX |
| /business-services/gst-registration | Yes | Yes | 589 | 58% | IMPROVE |
| /business-services/llp-registration | Yes | Yes | 601 | 60% | IMPROVE |
| /business-services/msme-registration | Yes | Yes | 547 | 57% | IMPROVE |
| /business-services/opc-registration | Yes | Yes | 564 | 62% | IMPROVE |
| /business-services/pan-tan-assistance | Yes | Yes | 554 | 58% | IMPROVE |
| /business-services/partnership-registration | Yes | Yes | 560 | 57% | IMPROVE |
| /business-services/private-limited-company | Yes | Yes | 614 | 62% | IMPROVE |
| /business-services/roc-compliance | Yes | Yes | 517 | 48% | IMPROVE |
| /business-services/startup-registration | Yes | Yes | 574 | 57% | IMPROVE |
| /business-services/trademark-registration | Yes | Yes | 589 | 49% | IMPROVE |
| /business-services/trademark-search | No | No | 492 | 49% | KEEP NOINDEX |
| /business-services | Yes | Yes | 922 | 68% | IMPROVE |

Field coverage against §14 (all 14 services):

| §14 requirement | Present? |
|---|---|
| What it is / who it is for | Yes (`summary`, `who`) |
| Documents | Yes — **not source-verified** |
| Process | Yes (Paynancial's own process) |
| FAQs | Yes |
| Government / regulatory authority | One-line `framework` sentence only; authority not linked |
| Eligibility | **Missing** |
| Customer responsibilities | **Missing** |
| Compliance after registration | **Missing** (only via related links) |
| Common mistakes | **Missing** |
| Official sources | **Missing** |
| Last reviewed / review status | **Missing** |
| Paynancial service scope | Partial (process steps, "we confirm before work begins") |

Fees and timelines are correctly `null` everywhere — no invented numbers.

## 6. Existing jurisdiction inventory

15 jurisdictions: Guernsey, UAE, Saint Vincent and the Grenadines, Saudi Arabia, British Virgin Islands, Cayman Islands, Singapore, Mauritius, Cyprus, Hong Kong, Ireland, Luxembourg, Malaysia, Puerto Rico, United Kingdom.

| Flag (brief §38 name → code name) | Set for any jurisdiction? |
|---|---|
| `service_enabled` → `served_confirmed` | **No** |
| `indexable` → `content_verified` | **No** |

Consequences already enforced in code: every jurisdiction page, the directory and `/business-services/global-incorporation` are `noindex, follow`, out of the sitemap, and use "we'll confirm availability" wording. **No `uae.paynancial.com` subdomain exists or is proposed** (§19, §53).

Content per jurisdiction today: a neutral descriptor, capital, region, membership groups (EU/GCC/ASEAN…), and a shared template. `overview`, `structures`, `considerations` are empty for all 15 — so none meets §20.

## 7. Existing regulatory content inventory

| Content | Where | Source-backed? | Status proposal |
|---|---|---|---|
| Trust Center statuses (Verified / In Progress / Verify) | `/trust` | Internal confirmation model | KEEP — the model §10 asks for |
| "PCI DSS certification", "RBI Payment Aggregator authorization" = **Verify** | `/trust` | — | KEEP; never claimed elsewhere (checked sitewide) |
| 21 regulatory references (RBI, NPCI, Acts) in "Compliance in India" bands | ~50 product, capability, pillar, AI and developer pages (`includes/regulatory-context.php`) | **No** — general knowledge; no reference no., dates, URLs, verifier | **Under Review** — see decision D2 |
| "In India" bands (e.g. 1930 helpline, UPI PIN, FEMA, GST/TDS notes, festive seasons) | ~30 pages | **No** | **Under Review** |
| Refund Policy timelines (UPI 1–3 business days, cards 5–7…) and chargeback "typically 7–10 days" | `/legal/refund-policy`, `/products/chargebacks` | Paynancial's own published policy | KEEP; confirm with legal that timelines match current RBI TAT rules |
| Business Services `framework` lines (e.g. "Companies Act, 2013", "Registrar of Companies") | 14 service pages | **No** citation | Under Review |
| Business Services document lists (PAN, Aadhaar, NOC…) | 14 service pages | **No** citation | Under Review |
| Jurisdiction documents lists | 15 pages (noindex) | **No** | Under Review |

## 8. Missing regulatory knowledge

Per brief §8, every reference needs: Title · Authority · Reference / circular / notification no. · Issue date · Effective date · Applicability · Official source URL · Last verified · Review date.

| Gap | Affects |
|---|---|
| Reference numbers, issue / effective dates, source URLs for all 21 payment references | Every "Compliance in India" band |
| Named authority + official source for each India business service: MCA / ROC (incorporation, LLP, OPC, ROC and annual compliance, company changes); CBIC / GSTN (GST); Income Tax Department (PAN / TAN); Ministry of MSME Udyam portal; DPIIT / Startup India; IP India / Trade Marks Registry (trademarks) | 14 Business Services pages |
| Eligibility rules and post-registration obligations per service | 14 Business Services pages |
| Per-jurisdiction registrar, licensing authority, tax authority, beneficial-ownership / AML authority | 15 jurisdiction pages |
| India-side outbound rules for founders setting up abroad (overseas investment framework, remittance, tax residency) | Global incorporation, future "for Indians" pages |
| Named, qualified reviewer (CA / CS / lawyer) — none confirmed | All regulatory content (§39) |

Authorities in the brief that are **not applicable** today and should not appear: **IRDAI** (no insurance product), **SEBI** (no securities product), **FIU-IND** (only relevant if Paynancial is a reporting entity — a Paynancial legal confirmation, not a page claim).

## 9. Missing standalone pages

Only pages with genuine intent **and** a verifiable source path are proposed. None is built until approved and sourced.

| Proposed URL | Why | Blocker |
|---|---|---|
| `/blog/{slug}` article template | Posts cannot be indexed today | Needs posts + template (P1) |
| `/resources/regulatory-library` (+ `/resources/regulatory-library/{reference}`) | One source-backed home for every RBI / NPCI / MCA reference the product pages cite, with dates and review status; product pages link to it instead of repeating text | Source verification (network) |
| `/business-services/jurisdictions/uae` children (mainland, free zone, company types, licensing, compliance, tax & VAT, for Indians) | §21 | UAE served_confirmed + verified official sources + reviewer |
| Singapore / Hong Kong / UK equivalents | §29 | Same |
| Comparisons (UAE mainland vs free zone; UAE vs Singapore; India vs UAE) | §30 | Only after both sides are verified |
| `/locations/patna` | §15 | Only if there is a genuine customer-facing office with address and hours (currently "based in Patna, Bihar" only) |

## 10. India search-intent map

| Cluster | Intent | Example queries | Target URL | Exists? |
|---|---|---|---|---|
| Payment gateway | Commercial | payment gateway India, UPI payment gateway | `/products/payment-gateway`, `/products/upi-payments` | Yes |
| Payment links | Commercial | payment link without website | `/products/payment-links`, `/products/payment-pages` | Yes |
| Payouts | Commercial | bulk payouts, vendor payments API | `/products/payouts` + children | Yes |
| Developer | Informational | payment API India, payout API | `/developers/*` | Yes |
| Company incorporation | Informational → Commercial | what is a private limited company; company registration services | `/business-services/company-incorporation`, `/private-limited-company` | Yes — needs §14 depth |
| LLP / OPC / partnership | Informational → Commercial | what is an LLP; LLP registration | `/business-services/llp-registration` etc. | Yes — needs depth |
| GST / MSME / Startup / PAN-TAN / Trademark | Regulatory + Commercial | GST registration requirements; Udyam registration | matching service pages | Yes — needs sources |
| Post-incorporation compliance | Regulatory | ROC annual filing | `/roc-compliance`, `/annual-compliance` | Yes — needs sources |
| Payments regulation | Regulatory | RBI payment aggregator guidelines; UPI dispute | none dedicated | Proposed regulatory library |
| Local | Local | company incorporation Patna | none | Only if genuine presence (§15) |

One URL per intent cluster — no per-keyword variants.

## 11. Global search-intent map

| Cluster | Intent | Target | Status |
|---|---|---|---|
| Global company incorporation | Informational / Commercial | `/business-services/global-incorporation` | Exists, noindex until a jurisdiction is approved |
| UAE company registration from India | Informational / Commercial | `/business-services/jurisdictions/uae` (+ `/company-registration-for-indians`) | Parent exists (noindex); child proposed |
| UAE mainland vs free zone | Informational | UAE children | Proposed |
| Singapore company registration from India | Informational / Commercial | `/jurisdictions/singapore` | Exists (noindex, template) |
| Hong Kong company formation | Informational / Commercial | `/jurisdictions/hong-kong` | Exists (noindex, template) |
| UK company incorporation from India | Informational / Commercial | `/jurisdictions/united-kingdom` | Exists (noindex, template) |
| Other 11 jurisdictions | Informational | existing pages | Keep noindex; **do not expand** until the four above are done well (§48) |

## 12. Jurisdiction architecture

```
/business-services/global-incorporation            (global parent — exists)
/business-services/jurisdictions                   (directory / finder — exists)
/business-services/jurisdictions/{jurisdiction}    (15 exist, all noindex)
/business-services/jurisdictions/uae/{topic}       (proposed, only when content warrants)
    mainland-company-registration · free-zone-company-registration · company-types
    licensing-and-business-activities · corporate-compliance · tax-and-vat
    company-registration-for-indians
```

Rules (already partly in code):
- A jurisdiction is indexable, in the sitemap, listed as served and given active CTAs **only** when `served_confirmed` and `content_verified` are both true (§38).
- Child pages inherit the parent's approval **and** need their own `content_verified`.
- Every jurisdiction page follows the §20 section model; sections without verified content are omitted, not filled with generic text.
- No country subdomains (§19, §53).

## 13. Regulator matrix (applicability)

Status is **Under Review** for every row: none could be verified from this environment. "Official source" is the authority's home domain, to be replaced with the specific document URL at verification.

| Topic | Authority | Instrument (to be cited precisely) | Applies to | Customer impact | Paynancial relevance | Official source | Last verified | Status |
|---|---|---|---|---|---|---|---|---|
| Payment systems | RBI | Payment and Settlement Systems Act, 2007 | Payment system operators | Who may run payment systems | Payments, payouts | rbi.org.in | — | Under Review |
| Payment aggregators / gateways | RBI | PA / PG guidelines and later amendments | Online payment aggregators | Merchant onboarding, fund handling, grievances | Payment Gateway, links, collections, split payments | rbi.org.in | — | Under Review |
| Cross-border aggregation | RBI | PA-CB framework | Cross-border aggregators | Import / export payments | International payments | rbi.org.in | — | Under Review |
| Foreign exchange | RBI | FEMA, 1999 + directions | Residents dealing in forex | Cross-border payments, overseas setup | International payments, global incorporation | rbi.org.in | — | Under Review |
| Failed-transaction TAT | RBI | TAT harmonisation circular | Authorised payment systems | Reversal times, compensation | Refunds, chargebacks, reconciliation | rbi.org.in | — | Under Review |
| Dispute resolution | RBI / NPCI | ODR framework; UDIR | Operators, participants | How disputes are raised | Refunds, chargebacks | rbi.org.in, npci.org.in | — | Under Review |
| Recurring payments | RBI / NPCI | E-mandate framework; UPI AutoPay; NACH | Recurring debits | Mandates, pre-debit notice | Smart Collections, embedded billing | rbi.org.in, npci.org.in | — | Under Review |
| Card data | RBI | Card-on-file tokenisation | Merchants, aggregators | No stored card numbers | Payment Gateway | rbi.org.in | — | Under Review |
| Wallets | RBI | PPI Master Direction | PPI issuers | Wallet limits, KYC | Wallet infrastructure (guide) | rbi.org.in | — | Under Review |
| AI in finance | RBI | FREE-AI framework | Regulated entities | Responsible AI | AI & Intelligence | rbi.org.in | — | Under Review |
| Complaints | RBI | Integrated Ombudsman Scheme | RBI-regulated entities | Escalation route | Financial assistant, white-label | rbi.org.in, cms.rbi.org.in | — | Under Review |
| Personal data | MeitY | Digital Personal Data Protection Act, 2023 (+ Rules) | Data fiduciaries | Consent, rights | All products, privacy policy | meity.gov.in | — | Under Review |
| Companies / LLPs | MCA / ROC | Companies Act, 2013; LLP Act, 2008 | Companies, LLPs | Incorporation, filings | Business Services | mca.gov.in | — | Under Review |
| GST | CBIC / GSTN | CGST Act, 2017 | Taxable persons | Registration, invoices | GST registration, invoice guide | cbic.gov.in, gst.gov.in | — | Under Review |
| PAN / TAN, TDS | Income Tax Department | Income-tax Act | Taxpayers | PAN/TAN, TDS on payouts | PAN-TAN, payout pages | incometax.gov.in | — | Under Review |
| MSME | Ministry of MSME | Udyam registration | Enterprises | Udyam certificate | MSME registration | udyamregistration.gov.in | — | Under Review |
| Startups | DPIIT | Startup India recognition | Eligible startups | Recognition | Startup registration | startupindia.gov.in | — | Under Review |
| Trademarks | IP India | Trade Marks Act, 1999 | Applicants | Filing, search | Trademark pages | ipindia.gov.in | — | Under Review |
| Import / export | DGFT | IEC | Importers / exporters | IEC requirement | International payments (mention) | dgft.gov.in | — | Under Review |
| Insurance · Securities · FIU | IRDAI · SEBI · FIU-IND | — | — | — | **Not applicable** to current pages | — | — | Not Applicable |

## 14. Source matrix

| Authority | Official domain | Pages that will cite it | Reachable from this environment | Verification status |
|---|---|---|---|---|
| Reserve Bank of India | rbi.org.in | ~50 payment / AI pages | No | Unverified |
| NPCI | npci.org.in | UPI, payouts, collections, disputes pages | No | Unverified |
| Ministry of Corporate Affairs | mca.gov.in | 7 Business Services pages | No | Unverified |
| CBIC / GST Network | cbic.gov.in / gst.gov.in | GST, invoice, expense pages | No | Unverified |
| Income Tax Department | incometax.gov.in | PAN-TAN, payout pages | No | Unverified |
| Ministry of MSME | udyamregistration.gov.in | MSME | No | Unverified |
| DPIIT / Startup India | startupindia.gov.in | Startup registration | No | Unverified |
| IP India | ipindia.gov.in | Trademark pages | No | Unverified |
| MeitY | meity.gov.in | Privacy, DPDP mentions | No | Unverified |
| National Cyber Crime portal | cybercrime.gov.in | Fraud detection | No | Unverified |
| UAE Government portal / Ministry of Economy / Federal Tax Authority | u.ae, moec.gov.ae, tax.gov.ae | UAE pages | No | Unverified |
| Singapore ACRA / IRAS | acra.gov.sg, iras.gov.sg | Singapore | No | Unverified |
| Hong Kong Companies Registry / IRD | cr.gov.hk, ird.gov.hk | Hong Kong | No | Unverified |
| UK Companies House / HMRC | gov.uk | United Kingdom | No | Unverified |

Rule: a page may cite a reference only once its row here is **Verified** with a specific document URL, date and verifier.

## 15. SEO plan

Already in place (verified by crawl): unique titles and descriptions, self-canonicals, robots control, one H1, breadcrumbs + BreadcrumbList, OG tags, `en-IN`, sitemap with lastmod, 301 hygiene.

Proposed:
1. **P0** Apply decisions D1 / D2 (below) to indexing and regulatory bands.
2. **P1** `/blog` and `/signup` → noindex; 404 pages → drop the self-canonical and add `noindex`.
3. **P1** Business Services depth pass (§14 fields) with sources — reduces the 46–62% overlap.
4. **P1** Jurisdictions: rebuild UAE, Singapore, Hong Kong, UK first, each to §20 with official sources; keep the other 11 noindex.
5. **P2** Review inherited speed claims ("in seconds", "under a minute") and the header "24×7 Support" badge against operations.
6. **P2** `Service` schema already omits unconfirmed capabilities; add `Article` + `dateModified` to regulatory / guide pages once review dates exist. No review / rating schema (none exists — correct).

## 16. AEO plan

Already in place: a 2–4 sentence "In short" answer at the top of product, capability, pillar and guide pages; question-led FAQs with FAQPage schema that mirrors visible content; entity-first headings.

Proposed:
- Pattern for regulatory and Business Services pages: **Question → 2–4 sentence direct answer → detail → official source → last reviewed** (§31).
- Entity block per page: primary entity (service / country) + related entities (regulator, instrument, company type, Paynancial service) stated in plain text and linked (§32).
- Do not add FAQs purely for rich results; cap at the questions customers actually ask (sales / support logs, once available).

## 17. Local SEO plan

- Only genuine presence: **"based in Patna, Bihar"** (Organization schema `address` with locality / region / country only).
- **No** `/locations/*` pages and **no** `LocalBusiness` schema until a customer-facing office with a verified address, phone and hours exists (§15).
- **No** city × service pages ("company incorporation in {city}").
- India relevance comes from content (UPI, INR / paise, GST, MCA) — not from location pages.

## 18. Sitemap plan

Current: 93 URLs, all indexable and self-canonical; generated by `public/sitemap.php` with Business Services gated by `bs_sitemap_paths()`.

Proposed eligibility rule (codify §36/§37 in one function): a URL is listed only if it is **published + indexable + self-canonical + substantive + unique + claim-verified**. Changes this would make: remove `/blog`, `/signup`; remove the 16 unconfirmed-capability pages **if** D1 = noindex. Jurisdictions enter only via `bs_jurisdiction_approved()`.

## 19. Internal-link plan

| From | To | Rule |
|---|---|---|
| Global incorporation | Jurisdiction finder → approved jurisdictions only | Unapproved jurisdictions reachable from the finder, not promoted |
| UAE parent | company types → mainland → free zone → compliance → India-side | Only children that exist and are verified |
| Payment Gateway | Business Services | Keep the single approved sentence ("…company incorporation and the registrations a business typically needs before going live with payments.") |
| Business Services (post-incorporation) | Payment Gateway | One contextual link on incorporation pages — already present |
| Product / guide pages | Regulatory library entry | Replace repeated regulatory bands with 1–3 contextual links once the library exists (cuts boilerplate overlap) |
| Everywhere | No sitewide keyword links | Header / footer links use product names, not keywords |

Only orphan found: `/signup` (0 inbound links) → noindex rather than link.

## 20. Content-review plan and CMS model

**Workflow (§43):** DRAFT → SOURCE VERIFICATION → EDITORIAL REVIEW → SEO / AEO REVIEW → LEGAL / REGULATORY REVIEW (where required) → APPROVAL → PUBLISH.

**Proposed CMS entities** (extends the existing `cms_pages` / `blog_posts` tables; not created yet):

```sql
-- PROPOSAL ONLY — not applied.
CREATE TABLE regulators (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL, authority_type ENUM('regulator','ministry','registrar','tax','statute','other') NOT NULL,
  jurisdiction VARCHAR(80) NOT NULL, official_url VARCHAR(255) NOT NULL
);
CREATE TABLE regulatory_references (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  regulator_id BIGINT UNSIGNED NOT NULL, instrument_type VARCHAR(60) NOT NULL,
  reference_number VARCHAR(120) NULL, title VARCHAR(255) NOT NULL,
  issue_date DATE NULL, effective_date DATE NULL, superseded_date DATE NULL,
  jurisdiction VARCHAR(80) NOT NULL, applicable_sector VARCHAR(120) NULL,
  customer_impact TEXT NOT NULL, summary TEXT NOT NULL, paynancial_relevance TEXT NOT NULL,
  source_url VARCHAR(255) NOT NULL, source_document VARCHAR(255) NULL,
  last_verified DATE NULL, next_review DATE NULL, reviewer VARCHAR(190) NULL,
  status ENUM('draft','under_review','verified','superseded','not_applicable','archived') NOT NULL DEFAULT 'draft',
  review_required TINYINT(1) NOT NULL DEFAULT 0, source_updated TINYINT(1) NOT NULL DEFAULT 0
);
CREATE TABLE reference_links (   -- reference ↔ product / service / jurisdiction / page
  reference_id BIGINT UNSIGNED NOT NULL, target_type ENUM('product','service','jurisdiction','page') NOT NULL,
  target_key VARCHAR(120) NOT NULL, PRIMARY KEY (reference_id, target_type, target_key)
);
```

Publishing rules enforced in code: a reference renders only when `status = 'verified'` **and** `source_url` and `last_verified` are set; a **manual review queue** lists references where `next_review <= today` or `source_updated = 1` (§45). Pages show *Last reviewed · Source · Review status* and, only when true, *Professional review: Completed* — never a named reviewer who hasn't reviewed (§39, §40).

Jurisdictions, company types, business services, FAQs, SEO metadata, redirects and review dates follow the same pattern (today they live in PHP data files — `includes/business-services.php` etc. — which is the interim "CMS").

---

## Decisions needed before implementation

| # | Decision | Recommendation |
|---|---|---|
| **D1** | 16 pages describing unconfirmed capabilities (10 product guides + 6 AI pages) are indexed at your earlier request. §37 says indexability requires a verified service. | **Noindex them** (keep them live and linked) until each capability is confirmed; one flag per page flips them back |
| **D2** | "Compliance in India" and "In India" bands are not source-verified (§6, §47). | **Hide them on all pages** until each reference is verified with a source, date and reviewer; then show them from the regulatory library |
| **D3** | Network access for official sources, live site and CMS | Allow the domains listed in §0 |
| **D4** | Which jurisdictions Paynancial genuinely serves today | Name them; start content verification with UAE, Singapore, Hong Kong, UK |
| **D5** | A qualified reviewer (CA / CS / lawyer) for regulatory and Business Services content | Confirm who, or pages show "Professional review: Pending" |
| **D6** | Any genuine customer-facing office locations | If none beyond "based in Patna", no location pages |

## Implementation order (after approval)

| Phase | Work |
|---|---|
| **P0** | D1 + D2 applied; `/blog` + `/signup` noindex; 404 canonical fix |
| **P1** | Regulatory reference data model + review queue; Business Services §14 depth pass with sources; UAE / Singapore / Hong Kong / UK rebuilt to §20; blog article route |
| **P2** | UAE child pages and comparisons (only where verified); inherited speed-claim review; regulatory library pages |
| **P3** | Source-change monitoring, Search Console-driven prioritisation |

Each phase ships as its own commit(s) on a branch, is tested locally (crawl, overlap, links, layout, contrast), and is reversible by revert. Production deploy stays with you.


---

## Approved decisions — implementation status (23 Sep 2026)

| # | Decision | Implemented | Where |
|---|---|---|---|
| 1 | 16 unconfirmed-capability pages: live, `noindex, follow`, out of the sitemap, no service promotion | Yes — 16/16 noindex, 0 in sitemap, 0 with Service schema; primary CTA is "Ask about availability" | `includes/content-governance.php` (readiness flags + publishing gate), `pages/products/capability.php`, `includes/ai-intelligence.php`, `public/sitemap.php` |
| 1a | Readiness flag that cannot flip to indexable by accident | Yes — needs stage ≥ Indexable **and** indexable flag **and** approved_by + approved_on | `gov_indexable()`; DB mirror with CHECK constraints in `database/content_governance_schema.sql` |
| 2 | Unsourced regulatory content removed from public pages | Yes — "Compliance in India" bands (23 draft references) render only when every source field is present and status is verified: 0 pages today. "In India" bands held as drafts: 0 pages. Business Services "Framework" lines hidden until sourced. Regulatory statements rewritten out of guide-page bodies and FAQs | `includes/regulatory-context.php` (`reg_references()`, `reg_publishable()`), `gov_india_context_public()`, `bs_framework_public()` |
| 3 | Network access for official domains | **Requested — still blocked.** Nothing is marked verified | — |
| 4 | UAE, Singapore, Hong Kong, UK: research only, all flags false | Yes — explicit `research`, `service_enabled`, `indexable`, `sitemap`, `service_promotion` flags; gate test extended | `includes/business-services.php`, `tests/jurisdiction-gate-test.php` |
| 5 | Professional review: Pending, no named reviewer | Yes — `GOV_PROFESSIONAL_REVIEW = 'pending'`; no "reviewed by" wording anywhere (checked sitewide) | `includes/content-governance.php` |
| 6 | No location pages; "Based in Patna, Bihar" only | Yes — no location pages; no "registered office" claim about Paynancial | — |
| 7 | No `uae.paynancial.com`; main-domain architecture | Yes — unchanged | — |
| 8 | Research vs service separation on jurisdiction pages | Yes — unapproved pages: "{Country}: jurisdiction information", "Paynancial service availability: Not confirmed" panel, research-status list, "Ask about availability" only; no "Get a Quote", incorporation process, generic document lists or service FAQs; floating enquiry reads "Ask About Availability" | `pages/business-services/jurisdiction.php`, `includes/cta-context.php` |
| 9 | Publishing gate: Draft → Source verification → Content review → Regulatory review → Business service approval → SEO/AEO review → Indexable → Sitemap → Publish | Yes — `gov_stages()`; CMS view at `/admin/content-governance` (read-only) | `admin/content-governance.php` |

After the change: 111 URLs, all 200; sitemap 77 URLs (was 93); no noindex page in the sitemap; no page overflow at 5 widths on the changed templates.

## Final decisions on remaining items — implementation status

| Item | Decision | Implemented |
|---|---|---|
| `/blog` | Live, `noindex, follow`, not in sitemap; not blocked in robots.txt | Yes — governed utility page in `content-governance.php`; noindex applied centrally in `seo_meta()` |
| `/signup` | Live, `noindex, follow`, not in sitemap; not blocked in robots.txt | Yes — same mechanism |
| 404 pages | HTTP 404, no canonical, not indexed, not in sitemap | Yes — `seo_meta()` omits canonical and og:url and sets `noindex, follow` whenever the response is 404 (checked on 9 missing-URL patterns). 404s are not converted to 200s; the branded 404 template is unchanged |
| Privacy Policy, Terms | Live, content held; Legal Review = Pending, Regulatory Review = Pending; no reviewer named | Yes — `gov_review_items()`, shown in `/admin/content-governance`; no wording changed |
| Official sources | Verify only after access | Still blocked by network policy; nothing marked verified |

**Other utility pages, audited individually (no mass noindex):** `/forgot-password`, `/reset-password`, `/signup/verify` and payment-link pages (`/pay/…`) were already noindex; `/login` renders the homepage and canonicalises to `/` (not in the sitemap) — correct; dashboards are behind login and disallowed in robots.txt — correct; `/contact` stays indexable (genuine contact intent); `/partner/register` stays indexable for now (partner-programme intent, 315 words) — revisit if it should be treated as a form-only page.

Result: sitemap 75 URLs; no noindex page in the sitemap; no canonical conflicts.

**Also fixed:** the sticky in-page menu on pillar, product, developer and AI pages now sits flush under the header at every width (it previously left a 20–23px gap where page text showed through, and sticky headings slid under it).

## Partner Program decision — implementation status

| Item | Implemented |
|---|---|
| `/partner/register` | Live and functional; `noindex, follow`; not in sitemap; explicitly allowed in robots.txt (`Allow: /partner/register` above `Disallow: /partner/`) so the noindex is seen, while partner dashboards stay disallowed |
| Search-visible Partner Program page | New canonical `/partner-program` (1,288 words, built only from published programme facts: partner types, applicant types, engagement models, 7-step application, onboarding stages, document types, agreements, Partner Hub sections). Indexable and in the sitemap. `/partners` and `/partners/` 301 to it in one hop |
| Primary CTA | "Apply as a Partner →" → `/partner/register` |
| Header | No standalone "Partners" item; "Partner Program" stays in the Company menu (desktop and mobile), plus footer and contextual links |

## `pages/partners.php` — pre-deletion audit (go-live gate items 12–13)

| Check | Result |
|---|---|
| Files referencing `partners.php` (PHP, JS, `.htaccess`, SQL, docs) | None |
| Route | `'partners'` removed from the public route table; `/partners`, `/partners/` and `/partners?…` 301 to `/partner-program` **before** any page file is resolved, so the old file is unreachable even if it remains on the server |
| Links to `/partners` | None (Company menu, footer and product pages point to `/partner-program`) |
| Partner functionality | Untouched and working: `/partner/register` (7-step form, noindex), 16 Partner Hub pages (redirect to login when signed out), `/api/partner/recommend` and `/api/partner/assistant` (403 without auth), partner login tab (`/?login=partner`), admin partner applications |

Conclusion: safe to delete from the server after the new build is deployed and `/partners` is confirmed to redirect. The Partners **header item** was removed; no partner **functionality** was removed.
