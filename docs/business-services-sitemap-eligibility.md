# Business Services — XML sitemap eligibility

Business Services pages are **navigation-independent but discovery-enabled**:

| Channel | Status |
|---|---|
| Header | No |
| Footer | No |
| Contextual links | Yes — homepage, Payment Gateway, Solutions, Pricing |
| XML sitemap | Yes, eligible pages only |
| Organic indexing | Yes, eligible pages only |

## Rule

A URL is listed in `sitemap.xml` (via `bs_sitemap_paths()`) only when **all** hold:

1. The page exists and returns 200.
2. It is intended for organic search and is indexable (no `noindex`).
3. It has a self-referencing canonical URL.
4. It carries substantial page-specific content — measured as **≥ 350 words** of main
   content excluding shared components (cross-sell, CTA band, "Why Paynancial").
5. It does not duplicate another page — **≤ 40 %** overlap (5-word shingles) with any
   sibling, and no topic cannibalisation.
6. It represents an actual Paynancial service. Evidence for the India services: the
   Business Services scope defined by Paynancial in the project brief (company
   incorporation, registrations, GST, MSME/Udyam, Startup, PAN/TAN, trademark,
   ROC and annual compliance, company changes).
7. Any jurisdiction-specific service claim is explicitly confirmed (approval matrix).

A page that fails the rule is also `noindex, follow`, so the sitemap and the
robots directive never disagree.

## Current assessment (23 Sep 2026)

| URL | Page-specific words | Max overlap with a sibling | Indexable | In sitemap | Reason |
|---|---|---|---|---|---|
| `/business-services` | 668 | 39% | Yes | Yes | Meets all conditions |
| `/business-services/global-incorporation` | 768 | 21% | No | No | International service not yet confirmed for any jurisdiction |
| `/business-services/jurisdictions` | 436 | 39% | No | No | International service not yet confirmed for any jurisdiction |
| `/business-services/company-incorporation` | 789 | 16% | Yes | Yes | Meets all conditions |
| `/business-services/private-limited-company` | 506 | 39% | Yes | Yes | Meets all conditions |
| `/business-services/llp-registration` | 496 | 36% | Yes | Yes | Meets all conditions |
| `/business-services/opc-registration` | 455 | 39% | Yes | Yes | Meets all conditions |
| `/business-services/partnership-registration` | 453 | 34% | Yes | Yes | Meets all conditions |
| `/business-services/gst-registration` | 481 | 34% | Yes | Yes | Meets all conditions |
| `/business-services/msme-registration` | 443 | 35% | Yes | Yes | Meets all conditions |
| `/business-services/startup-registration` | 467 | 33% | Yes | Yes | Meets all conditions |
| `/business-services/pan-tan-assistance` | 448 | 35% | Yes | Yes | Meets all conditions |
| `/business-services/trademark-registration` | 486 | 23% | Yes | Yes | Meets all conditions |
| `/business-services/trademark-search` | 395 | 24% | No | No | Thin and overlaps Trademark Registration (merge planned) |
| `/business-services/roc-compliance` | 409 | 26% | Yes | Yes | Meets all conditions |
| `/business-services/annual-compliance` | 398 | 25% | Yes | Yes | Meets all conditions |
| `/business-services/company-changes` | 418 | 26% | Yes | Yes | Meets all conditions |
| `/business-services/jurisdictions/uae` | 639 | 55% | No | No | Jurisdiction UNCONFIRMED (see approval matrix) |
| `/business-services/jurisdictions/singapore` | 620 | 55% | No | No | Jurisdiction UNCONFIRMED (see approval matrix) |

**Result: 14 Business Services URLs in the sitemap; 18 excluded** (15 unconfirmed
jurisdictions, the 2 international hub pages, and `trademark-search`).
