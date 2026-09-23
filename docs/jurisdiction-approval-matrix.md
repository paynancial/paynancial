# Jurisdiction Approval Matrix

Controls which `/business-services/jurisdictions/{slug}` pages may be indexed
by search engines and may state that Paynancial serves that jurisdiction.

## Rule

A jurisdiction page becomes **fully indexable** only when **both** are true
and recorded in `includes/business-services.php` on that jurisdiction's entry:

| Flag | Meaning | Who confirms |
|---|---|---|
| `'served_confirmed' => true` | Paynancial currently serves this jurisdiction (delivery model and partner/agent known) | Business owner, in writing |
| `'content_verified' => true` | Page content has been checked against the official sources below and holds jurisdiction-specific (not templated) information | Content owner |

Until both flags are set, `bs_jurisdiction_approved()` returns `false` and the page:

- carries `<meta name="robots" content="noindex, follow">`
- omits FAQPage structured data
- says "our team will confirm how we can help" instead of "Paynancial supports incorporation in …"
- stays reachable from the directory for visitors

**Professional review:** no CA, CS, lawyer or other professional reviewer is
named on any page. A reviewer may be named only after that person has
actually reviewed and approved the specific page.

## Current status (all pending)

| Jurisdiction | Slug | Served today? | Delivery model / partner | Official source to verify against | Content verified | Named reviewer | Indexable |
|---|---|---|---|---|---|---|---|
| United Arab Emirates | `uae` | Unconfirmed | — | UAE Ministry of Economy; emirate economic departments; free-zone authorities | No | None | No |
| Singapore | `singapore` | Unconfirmed | — | Accounting and Corporate Regulatory Authority (ACRA) | No | None | No |
| United Kingdom | `united-kingdom` | Unconfirmed | — | Companies House | No | None | No |
| Hong Kong | `hong-kong` | Unconfirmed | — | Companies Registry | No | None | No |
| Ireland | `ireland` | Unconfirmed | — | Companies Registration Office (CRO) | No | None | No |
| Cyprus | `cyprus` | Unconfirmed | — | Department of Registrar of Companies and Intellectual Property | No | None | No |
| Luxembourg | `luxembourg` | Unconfirmed | — | Luxembourg Business Registers | No | None | No |
| Malaysia | `malaysia` | Unconfirmed | — | Companies Commission of Malaysia (SSM) | No | None | No |
| Mauritius | `mauritius` | Unconfirmed | — | Corporate and Business Registration Department; Financial Services Commission | No | None | No |
| Saudi Arabia | `saudi-arabia` | Unconfirmed | — | Ministry of Commerce; Ministry of Investment (MISA) | No | None | No |
| Guernsey | `guernsey` | Unconfirmed | — | Guernsey Registry | No | None | No |
| British Virgin Islands | `british-virgin-islands` | Unconfirmed | — | Registry of Corporate Affairs (Financial Services Commission) | No | None | No |
| Cayman Islands | `cayman-islands` | Unconfirmed | — | General Registry | No | None | No |
| Saint Vincent and the Grenadines | `saint-vincent-and-the-grenadines` | Unconfirmed | — | Financial Services Authority; Commerce and Intellectual Property Office | No | None | No |
| Puerto Rico | `puerto-rico` | Unconfirmed | — | Puerto Rico Department of State (Registry of Corporations) | No | None | No |

## Approving a jurisdiction

1. Business owner confirms in writing that the jurisdiction is served today, and records the delivery model (in-house, partner firm, registered agent).
2. Content owner adds verified, jurisdiction-specific content to the entry (`overview`, `structures`, `considerations`) with links to the official source, and replaces the templated FAQs.
3. Set `'served_confirmed' => true` and `'content_verified' => true` on the entry.
4. Update this table (status, date, who confirmed).
5. Add the page to the sitemap and request indexing in Search Console.
