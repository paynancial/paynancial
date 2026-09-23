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
| E-Commerce … Enterprise (8 industries) | `/solutions#…` | 2 sentences each | **Kept for now** — too thin; see §5 | — |
| AI Financial Agents | `/agentic-ai#financial-agents` | 215 words | **Kept for now** — too thin; see §5 | — |
| AI Orchestration | `/agentic-ai#payment-orchestration` | 175 words | **Kept for now** — too thin; see §5 | — |
| FAQs | `/support#faqs` | 5 FAQs | **Kept for now** — too thin; see §5 | — |
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
- five resources with the parameters used in the published examples:
  payments, refunds, payouts, payment links and collections;
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

## 3. Redirect map

| From | To | Type |
|---|---|---|
| `/sandbox/`, `/developers/{page}/` | slash-less URL | 301 (existing trailing-slash rule, one hop) |
| `/developers#sandbox` and the other old developer anchors | the new page | Client-side forward in `main.js`. A `#fragment` never reaches the server, so no 301 is possible or claimed. |

No other redirects were created. Unknown `/developers/{x}` paths return 404.

## 4. Canonical map

Every new page is self-canonical at its slash-less URL, for example `https://paynancial.com/sandbox`. No page is canonicalised to a parent page's fragment.

## 5. Not converted yet — and why

| Group | Current state | Why it was not split | What would make it ready |
|---|---|---|---|
| Solutions industries (8) | 2 sentences each on `/solutions` | A page per industry would be thin, or the same template with the industry name swapped | Approved, industry-specific content: problems, product stack, use cases, FAQs |
| Agentic AI children | 135–220 words per section | Below the site's 350-word bar for a standalone page | Expanded, approved content for each capability |
| Resources / FAQs | 5 FAQs on `/support` | A dedicated FAQ page would be thin | A larger, approved FAQ set, then a `/resources` hub |
| Our Journey | 591 words on `/about` | The journey is part of the About story; a separate page would duplicate it | — (recommended to keep) |
| Products enquiry items (for example Payment Pages, UPI Payments, Recurring Payments; 25 in the header) | Link to the sales enquiry form | No product content exists; a page would have to invent features | Product specifications for each item |
| Local SEO | "Based in Patna, Bihar" only | One city page with nothing local to say is a doorway page; the registered office is unconfirmed | Confirmed office details, then an Organization address in schema; location pages only for real locations with local content |

## 6. Analytics

There is no analytics platform on the site. Pages fire events through the existing `paynancial:track` DOM event, and also to `dataLayer` or `gtag` if one is added later:

- **Page views:** `developer_page_view`, `api_reference_view`, `sandbox_view`, `ai_governance_view`
- **Clicks:** `request_sandbox_access`, `api_reference_click`, `documentation_click`, `cta_click`
