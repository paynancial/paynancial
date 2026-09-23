# URL architecture — standalone pages and fragment migration

Principle: **one navigation item → one intent → one canonical URL → one page.**
A `#fragment` is fine for moving around inside a long page, but not as the
only address of a major product, resource or service.

URL convention (unchanged from the SEO P0 work): canonical URLs have **no
trailing slash**. `/sandbox/` 301-redirects to `/sandbox` in one hop, so a
link written either way works and there is only ever one canonical.

Status: local code only. Nothing here has been verified against the live
domain (see `docs/seo-p0-before-after.md`).

## 1. Fragment audit (header, mega-menus, footer)

Before this change, the header and footer pointed at 20 distinct `#fragment`
destinations.

| Navigation item | Old URL | What was behind it | Action | New URL |
|---|---|---|---|---|
| API Reference | `/developers#api-reference` | One-sentence card | **New standalone page** | `/developers/api-reference` |
| Sandbox | `/developers#sandbox` | One-sentence card | **New standalone page** | `/sandbox` |
| Webhooks | `/developers#webhooks` | One-sentence card | **New standalone page** | `/developers/webhooks` |
| SDKs | `/developers#sdks` | One-sentence card | **New standalone page** | `/developers/sdks` |
| Authentication | `/developers#authentication` | Short section + code sample | **New standalone page** | `/developers/authentication` |
| Integration Guide | `/developers#integration-guide` | One-sentence card | **New standalone page** | `/developers/integration-guide` |
| Documentation / API Documentation | `/developers#docs` | Card grid | Point to the hub | `/developers` |
| Payment APIs / Payout APIs | `/developers#docs` | Card grid | Section of the API Reference | `/developers/api-reference#payments`, `#payouts` |
| Agent-Ready APIs | `/developers#agentic-ai` | Section | Section of the new hub | `/developers#agent-ready` |
| AI Governance (Agentic AI column) | `/agentic-ai#governance` | 221 words | **Merged into one new page** | `/ai-governance` |
| AI Governance (Trust column) | `/trust#ai-governance` | 110 words (+ oversight, auditability) | **Merged into one new page** | `/ai-governance` |
| E-Commerce … Enterprise (8 industries) | `/solutions#…` | 2 sentences each | **New standalone pages** (see §2a) | `/solutions/{industry}` |
| AI Financial Agents | `/agentic-ai#financial-agents` | 215 words | **Expanded into a standalone page** (see §2b) | `/agentic-ai/financial-agents` |
| AI Orchestration | `/agentic-ai#payment-orchestration` | 175 words | **Expanded into a standalone page** (see §2b) | `/agentic-ai/payment-orchestration` |
| FAQs | `/support#faqs` | 5 FAQs | **New FAQ hub** (see §2c) | `/resources/faqs` |
| Our Journey | `/about#journey` | 591 words, part of About | **Kept** — the journey belongs on About | — |

The two "AI Governance" links pointed at two different pages for the same
topic. Both now point at `/ai-governance`; the Agentic AI and Trust Center
sections were cut to short summaries that link there, so the text is not
duplicated.

## 2. New standalone pages

| URL | Primary intent | H1 | Schema | Sitemap |
|---|---|---|---|---|
| `/developers` (rebuilt as a hub) | Navigational | Build payments into your product with the Paynancial API. | WebPage, BreadcrumbList, FAQPage | Yes |
| `/developers/api-reference` | Navigational + technical | API Reference | TechArticle, BreadcrumbList, FAQPage | Yes |
| `/developers/authentication` | Technical | Authenticate every request with an API key. | TechArticle, BreadcrumbList, FAQPage | Yes |
| `/developers/webhooks` | Technical | React to every payment event as it happens. | TechArticle, BreadcrumbList, FAQPage | Yes |
| `/developers/sdks` | Technical | Official SDKs for PHP, JavaScript and Python. | TechArticle, BreadcrumbList, FAQPage | Yes |
| `/developers/integration-guide` | Technical / how-to | From sandbox key to first live payment. | TechArticle, BreadcrumbList, FAQPage | Yes |
| `/sandbox` | Transactional / developer | Test Paynancial without touching live payments. | WebPage, BreadcrumbList, FAQPage | Yes |
| `/ai-governance` | Informational / trust | AI that acts only inside the limits you set. | WebPage, BreadcrumbList, FAQPage | Yes |

Each page has a unique title, meta description and canonical, Open Graph
tags, one H1, a breadcrumb, question-and-answer blocks, an FAQ, related
links and a closing CTA.

### Content source for the developer pages

There is no API specification in this repository. The developer pages
therefore state **only facts already published on paynancial.com**:

- the base URL `https://api.paynancial.com/v1`;
- HTTP basic authentication with the API key as the username;
- sandbox and live keys, managed from the dashboard;
- six resources with the parameters used in the published examples:
  payments, refunds, payouts, payment links, collections and transaction reports;
- idempotency keys (`Idempotency-Key` header);
- three example error codes: `insufficient_funds`, `invalid_method`, `rate_limited`;
- webhook event families: payments, payouts, refunds, settlements;
- SDKs for PHP, JavaScript and Python.

**Not stated anywhere, because nothing verifies it:**
- endpoints or parameters beyond the published examples;
- webhook event names, payloads or signature scheme;
- pagination, rate-limit numbers or a changelog;
- SDK package names or install commands;
- test card numbers, or which failures the sandbox can simulate.

Where a developer would need these, the page says so and links to developer support.

**To extend these pages:** add real specifications to `includes/developer-docs.php`, the single source for all developer content.

## 2a. Solutions industry pages

| URL | Replaces | Words | Schema | Sitemap |
|---|---|---|---|---|
| `/solutions/e-commerce` | `/solutions#ecommerce` | 893 | WebPage, BreadcrumbList, FAQPage | Yes |
| `/solutions/travel` | `/solutions#travel` | 878 | same | Yes |
| `/solutions/healthcare` | `/solutions#healthcare` | 842 | same | Yes |
| `/solutions/education` | `/solutions#education` | 813 | same | Yes |
| `/solutions/retail` | `/solutions#retail` | 791 | same | Yes |
| `/solutions/hospitality` | `/solutions#hospitality` | 787 | same | Yes |
| `/solutions/professional-services` | `/solutions#professional-services` | 809 | same | Yes |
| `/solutions/enterprise` | `/solutions#enterprise` | 846 | same | Yes |

- **Content:** all of it lives in `includes/solutions-data.php`. Each page covers:
  - the industry's own challenges, payment journey and product stack;
  - use cases and practical considerations;
  - FAQs and related industries.
- **Uniqueness:** 54–63% of each page's text is unique to it. The highest overlap between any two pages is 36% (5-word shingles), under the 40% ceiling.
- **Claims:** the Paynancial side cites only capabilities published on the product pages (`sol_products()`). Industry challenges are described in general terms. There are no statistics, customer names, outcomes, certifications or regulatory claims. The healthcare page states that Paynancial is not a clinical-records system and does not change a provider's own obligations. The travel page sends other-currency questions to the team rather than claiming multi-currency support.
- **Not converted:** the other six industries on `/solutions` (FinTech, Logistics, Real Estate, Insurance, Gaming, NGOs) are not in the header or footer menus. They stay as cards linking to a sales enquiry.
- **Legacy links:** `/solutions#travel` and the other converted anchors forward client-side to the new page. `/solutions/startups` and `/solutions/saas` still return 404; their templates never existed.

## 2b. Agentic AI pages

| URL | Replaces | Words | Schema | Sitemap |
|---|---|---|---|---|
| `/agentic-ai/financial-agents` | `/agentic-ai#financial-agents` | 1,234 | WebPage, BreadcrumbList, FAQPage | Yes |
| `/agentic-ai/payment-orchestration` | `/agentic-ai#payment-orchestration` | 1,023 | WebPage, BreadcrumbList, FAQPage | Yes |

- **Content:** both pages expand the text already published on `/agentic-ai`, the Developer pages and AI Governance. The Financial Agents page covers:
  - a definition;
  - eight agent patterns, each with what stays with people;
  - the manual-to-agentic path;
  - boundaries;
  - examples by business size;
  - a getting-started sequence.

  The Orchestration page covers the five safety rails, a worked retry-then-remind-then-cancel flow, a developer view with the published payout example, and a pre-launch test list.
- **Claims:** no new capability, accuracy, savings or outcome claims. The AI & Intelligence products are linked to a sales enquiry, as in the header.
- **Parent page:** the two sections on `/agentic-ai` are now short summaries that link to the new pages. The highest text overlap between any of `/agentic-ai`, the two new pages, `/ai-governance` and `/developers` is ≤10%.
- **Legacy links:** `/agentic-ai#financial-agents` and `#payment-orchestration` forward client-side. Every other `/agentic-ai` section stays where it is.

## 2c. Resources hub and FAQ hub

| URL | Purpose | Words | Schema | Sitemap |
|---|---|---|---|---|
| `/resources` | Navigational hub: help, developer docs, agentic AI guides, industries, trust & legal, insights | 673 | WebPage, BreadcrumbList | Yes |
| `/resources/faqs` | FAQ hub: 9 general questions answered in full, plus a directory of the 118 questions answered elsewhere, with a search filter | 1,314 | WebPage, BreadcrumbList, FAQPage (general questions only) | Yes |

- **Single source for FAQs:** every page's FAQs now live in `includes/faq-data.php`. Industry and Business Services FAQs stay in their own data files. Pages and the FAQ hub read the same data, so a question is written once. A check confirmed that every directory question appears on the page it links to.
- **No duplicated answers:** only the general questions are answered on the hub. Topic questions are links to the page that answers them. `/support` now lists its questions as links to the hub instead of repeating the answers.
- **General questions added (4):** sandbox testing, where to find developer docs, Business Services, and how to contact sales or support. All are answered from facts already on the site.
- **Navigation:**
  - The header Resources menu and the footer Resources column gain "All Resources".
  - "FAQs" now points to `/resources/faqs`, and `/support#faqs` forwards there.
  - The header's Integration Guide description no longer promises "setup for your stack".
- **Not built:** a separate `/resources/guides` page. The guides are grouped on the hub instead, because a guides page would only repeat those links.

## 2d. AI & Intelligence

| URL | Status | Words | Indexing |
|---|---|---|---|
| `/ai-intelligence` | **Canonical hub** (new) | 1,957 | Indexable; in sitemap |
| `/ai-intelligence/paynancial-ai` | Child | 830 | `noindex, follow` until availability is confirmed |
| `/ai-intelligence/fraud-detection` | Child | 787 | same |
| `/ai-intelligence/reconciliation` | Child | 718 | same |
| `/ai-intelligence/financial-assistant` | Child | 705 | same |
| `/ai-intelligence/cash-flow-intelligence` | Child | 677 | same |
| `/ai-intelligence/revenue-forecasting` | Child | 656 | same |
| `/products/ai-and-intelligence` | **301 → `/ai-intelligence`** | — | Removed from sitemap |

**Why the children are noindex.** Each capability has only a one-line published description, and the site describes its availability as "on request". Under the brief's indexing rule (#26), they stay live and linked but out of the index until the business confirms each one.

**To index a child page:** set its flag in `ai_confirmed()` (`includes/ai-intelligence.php`) and add it to the sitemap.

**Content sources:**
- the one-line capability descriptions on the Agentic AI pages;
- the AI Governance model;
- the Trust Center's **verified** security facts: TLS 1.2+, AES-256, tokenised card data, segmented cardholder environment, least-privilege access with MFA, and real-time fraud monitoring and risk scoring before funds move.

**Not claimed anywhere:**
- anything the Trust Center marks "Verify": PCI DSS certification, RBI Payment Aggregator authorisation, and a written AI governance framework;
- AI accuracy or model explainability;
- APIs for the AI capabilities.

**Links and enquiries:**
- The Products menu's AI & Intelligence column, the footer's AI block and the `/products` catalog now point to these pages.
- The homepage flow section links to the hub.
- The floating enquiry on these pages reads "Talk to Payment Experts — Discuss your payment and financial infrastructure requirements."

## 2e. Pay & Move Money and Financial Operations

Rule applied (approved "split by evidence"): verified product capability → standalone page → indexable → in the sitemap; insufficient or unconfirmed capability → a section on the pillar page, not a standalone SEO page.

| URL | Status | Words | Indexing |
|---|---|---|---|
| `/pay-and-move-money` | **Pillar** (new canonical) | 968 | Indexable; in sitemap |
| `/financial-operations` | **Pillar** (new canonical) | 954 | Indexable; in sitemap |
| `/products/payouts` | Kept | 666 | Unchanged; breadcrumb parent is now Pay & Move Money |
| `/products/bulk-payouts` | New child | 836 | Indexable; in sitemap |
| `/products/vendor-payments` | New child | 786 | Indexable; in sitemap |
| `/products/employee-payments` | New child | 731 | Indexable; in sitemap |
| `/products/partner-payments` | New child | 792 | Indexable; in sitemap |
| `/products/reconciliation`, `/products/settlements`, `/products/refunds` | Kept | — | Breadcrumb parent is now Financial Operations |
| `/products/payment-analytics` | Kept (Finance Analytics, MIS & Reports) | — | Breadcrumb parent is now Financial Operations |
| `/products/pay-and-move-money`, `/products/financial-operations` | **301 → pillar** | — | Removed from sitemap |

**Pillar-page sections (no standalone page):**
- International Payments → `/pay-and-move-money#international-payments`
- Chargebacks → `/financial-operations#chargebacks` (links to the Refund Policy's chargebacks section)
- Invoice Management → `/financial-operations#invoice-management` (links to Payment Links)
- Expense Management → `/financial-operations#expense-management`

Each section states only what the site already publishes and says plainly where a capability is not yet described, with an "Ask about …" enquiry link.

**Evidence for the four payout child pages:** the Payouts page and its FAQs as published in the baseline site (single or bulk payouts, "a batch of transfers in a single request", bank accounts and UPI IDs, saved beneficiaries, status tracking, failure reasons, payout report, payout webhooks, idempotency keys, "vendors, staff, freelancers, or channel partners"). Industry examples are not used as capability evidence. Where the API detail isn't published (the batch request format), the page says so and points to developer support.

**Uniqueness:** the highest 5-word-shingle overlap among these pages, the pillars and the existing products is 28% (limit 40%).

## 2f. Embedded Finance, remaining pillar items, India SEO and regulatory context

**Embedded Finance pillar** — `/embedded-finance` (canonical, indexable, in the sitemap); `/products/embedded-finance` 301s there. Evidence: the Partners page (technology partners "embed Payment, Payout and Billing APIs directly") and the published API. All six items (Embedded Payments, Payouts, Billing, Wallet Infrastructure, Split Payments, White-Label Payments) are **sections** on the pillar, because none has product-level evidence beyond the APIs. Solutions/Technology narrative is not used as evidence.

**Standalone pages for the remaining Pay & Move Money / Financial Operations items** (built on request):

| URL | Basis | Indexing |
|---|---|---|
| `/products/mis-reports` | Payment Analytics reports + Reports API (verified) | Indexable; in sitemap |
| `/products/chargebacks` | Chargeback process in the published Refund Policy (verified) | Indexable; in sitemap |
| `/products/international-payments` | Guide: published payout facts + India context; no cross-border claim | Indexable (owner's request); no Service schema |
| `/products/invoice-management` | Guide: Payment Links + Smart Collections; no invoicing claim | Indexable (owner's request); no Service schema |
| `/products/expense-management` | Guide: payout side of spending; no expense-product claim | Indexable (owner's request); no Service schema |

The guide pages are indexable at the owner's request. Each still states plainly what is not offered, and they carry no `Service` schema so the markup makes no availability claim.

**AI & Intelligence children** are now indexable and in the sitemap as well (owner's request). Each page states that the capability is available on request; `ai_confirmed()` still records which are confirmed. Every AI page and the hub has an "In India" band and a regulatory band (RBI FREE-AI, fraud risk management, digital payment security controls, DPDP Act, ODR, UDIR, RBI Integrated Ombudsman).

**India local signals (site-wide):** `<html lang="en-IN">`, `og:locale en_IN`, `inLanguage: en-IN`, Organization `address` (Patna, Bihar, IN — "based in", not a registered-office claim), and a `Service` schema with `areaServed: India` on every product, capability and pillar page. Each new page also has an "In India" band.

**Regulatory context (RBI / NPCI):** `includes/regulatory-context.php` holds plain-language summaries of the frameworks that apply (PSS Act 2007, PA/PG guidelines, PA-CB, FEMA, TAT for failed transactions, ODR, UDIR, UPI guidelines, UPI AutoPay, e-mandates, NACH, IMPS, card-on-file tokenisation, digital payment security controls, KYC Direction, DPDP Act 2023) and maps them to pages. Deliberately no circular numbers, dates, thresholds or timelines, no claim that Paynancial holds any licence or complies with a given circular, and links to rbi.org.in and npci.org.in for the current text. **Have a compliance professional review this file.**

## 3. Redirect map

| From | To | Type |
|---|---|---|
| `/sandbox/`, `/developers/{page}/` | slash-less URL | 301 (existing trailing-slash rule, one hop) |
| `/products/ai-and-intelligence` | `/ai-intelligence` | 301, one hop |
| `/products/pay-and-move-money` | `/pay-and-move-money` | 301, one hop |
| `/products/financial-operations` | `/financial-operations` | 301, one hop |
| `/products/embedded-finance` | `/embedded-finance` | 301, one hop |
| `/developers#sandbox` and the other old developer anchors | the new page | Client-side forward in `main.js`. A `#fragment` never reaches the server, so no 301 is possible or claimed. |

No other redirects were created. Unknown `/developers/{x}` paths return 404.

## 4. Canonical map

Every new page is self-canonical at its slash-less URL, for example `https://paynancial.com/sandbox`. No page is canonicalised to a parent page's fragment.

## 5. Not converted yet — and why

| Group | Current state | Why it was not split | What would make it ready |
|---|---|---|---|
| Solutions industries (8) | 2 sentences each on `/solutions` | A page per industry would be thin, or the same template with the industry name swapped | Approved, industry-specific content: problems, product stack, use cases, FAQs |
| Our Journey | 591 words on `/about` | The journey is part of the About story; a separate page would duplicate it | — (recommended to keep) |
| Products enquiry items (for example Payment Pages, UPI Payments, Recurring Payments; 25 in the header) | Link to the sales enquiry form | No product content exists; a page would have to invent features | Product specifications for each item |
| Local SEO | "Based in Patna, Bihar" only | One city page with nothing local to say is a doorway page; the registered office is unconfirmed | Confirmed office details, then an Organization address in schema; location pages only for real locations with local content |

## 6. Analytics

There is no analytics platform on the site. Pages fire events through the existing `paynancial:track` DOM event, and also to `dataLayer` or `gtag` if one is added later:

- **Page views:** `developer_page_view`, `api_reference_view`, `sandbox_view`, `ai_governance_view`
- **Clicks:** `request_sandbox_access`, `api_reference_click`, `documentation_click`, `cta_click`
