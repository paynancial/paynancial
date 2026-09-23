# Jurisdiction Approval Matrix

**Owner decision required for every row.** A jurisdiction becomes indexable,
sitemap-listed, and may be described as a Paynancial incorporation service
**only after the business owner explicitly confirms** that Paynancial serves it
today. Until then it stays **UNCONFIRMED**.

_Last updated: 23 Sep 2026 — all 15 UNCONFIRMED._

## Matrix

| Jurisdiction | Service Available Today? | Evidence | Page Exists? | Index? | Sitemap? | Status |
|---|---|---|---|---|---|---|
| United Arab Emirates | Not confirmed | None on file | Yes — `/business-services/jurisdictions/uae` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Singapore | Not confirmed | None on file | Yes — `/business-services/jurisdictions/singapore` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| United Kingdom | Not confirmed | None on file | Yes — `/business-services/jurisdictions/united-kingdom` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Hong Kong | Not confirmed | None on file | Yes — `/business-services/jurisdictions/hong-kong` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Ireland | Not confirmed | None on file | Yes — `/business-services/jurisdictions/ireland` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Cyprus | Not confirmed | None on file | Yes — `/business-services/jurisdictions/cyprus` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Luxembourg | Not confirmed | None on file | Yes — `/business-services/jurisdictions/luxembourg` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Malaysia | Not confirmed | None on file | Yes — `/business-services/jurisdictions/malaysia` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Mauritius | Not confirmed | None on file | Yes — `/business-services/jurisdictions/mauritius` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Saudi Arabia | Not confirmed | None on file | Yes — `/business-services/jurisdictions/saudi-arabia` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Guernsey | Not confirmed | None on file | Yes — `/business-services/jurisdictions/guernsey` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| British Virgin Islands | Not confirmed | None on file | Yes — `/business-services/jurisdictions/british-virgin-islands` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Cayman Islands | Not confirmed | None on file | Yes — `/business-services/jurisdictions/cayman-islands` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Saint Vincent and the Grenadines | Not confirmed | None on file | Yes — `/business-services/jurisdictions/saint-vincent-and-the-grenadines` | No (`noindex, follow`) | No | **UNCONFIRMED** |
| Puerto Rico | Not confirmed | None on file | Yes — `/business-services/jurisdictions/puerto-rico` | No (`noindex, follow`) | No | **UNCONFIRMED** |

## What UNCONFIRMED means on the site

- The page stays reachable for visitors from the jurisdiction directory, but carries
  `<meta name="robots" content="noindex, follow">` and is excluded from `sitemap.xml`.
- No FAQPage structured data.
- No incorporation-service claim: the page says our team will confirm whether and how
  we can help, rather than "Paynancial supports incorporation in …".
- The international hub pages (`/business-services/global-incorporation`,
  `/business-services/jurisdictions`) are also `noindex` and out of the sitemap while
  **no** jurisdiction is confirmed, because they would otherwise present an
  unconfirmed service.

## How a row moves to CONFIRMED

1. **Service Available Today?** — the business owner confirms in writing, naming the
   delivery model (in-house / partner firm / registered agent). Record the confirmation
   (who, date, document) in **Evidence**.
2. Replace templated content with verified, jurisdiction-specific content (overview,
   company structures, requirements, FAQs), each checked against the official source
   below. No professional reviewer is named unless that person actually reviewed it.
3. In `includes/business-services.php`, set on that jurisdiction's entry
   (flag names from the approved decisions of 23 Sep 2026):
   `'service_enabled' => true` and `'indexable' => true` — the page becomes indexable and
   gains FAQ markup and service wording; then `'sitemap' => true` to list it in the
   sitemap and `'service_promotion' => true` to allow service CTAs ("Get a Quote").
   Until both `service_enabled` and `indexable` are true the page is **jurisdiction
   information only**: noindex, "Not confirmed" availability panel, research status, an
   "Ask about availability" enquiry and no service CTAs. UAE, Singapore, Hong Kong and
   the UK are research / content-development jurisdictions with every flag false.
   `tests/jurisdiction-gate-test.php` must pass.
4. Update this table: Service Available Today? → Yes, Evidence, Index? → Yes,
   Sitemap? → Yes, Status → **CONFIRMED**.

## Official sources (for content verification)

| Jurisdiction | Official source to verify content against |
|---|---|
| United Arab Emirates | UAE Ministry of Economy; emirate economic departments; free-zone authorities |
| Singapore | Accounting and Corporate Regulatory Authority (ACRA) |
| United Kingdom | Companies House |
| Hong Kong | Companies Registry |
| Ireland | Companies Registration Office (CRO) |
| Cyprus | Department of Registrar of Companies and Intellectual Property |
| Luxembourg | Luxembourg Business Registers |
| Malaysia | Companies Commission of Malaysia (SSM) |
| Mauritius | Corporate and Business Registration Department; Financial Services Commission |
| Saudi Arabia | Ministry of Commerce; Ministry of Investment (MISA) |
| Guernsey | Guernsey Registry |
| British Virgin Islands | Registry of Corporate Affairs (Financial Services Commission) |
| Cayman Islands | General Registry |
| Saint Vincent and the Grenadines | Financial Services Authority; Commerce and Intellectual Property Office |
| Puerto Rico | Puerto Rico Department of State (Registry of Corporations) |
