# Products menu audit

Scope: the Products mega-menu (39 items in 6 columns), the matching mobile
menu, and the `/products` catalog, which lists the same 39 items. Status:
local code, current branch.

## Summary

| | Items |
|---|---|
| Link to a real product page | 7 (5 distinct pages) |
| Link to a Developer page | 6 |
| Share one destination with other items (Payment Analytics) | 4 of the 7 above |
| Link to the sales enquiry form — **no page** | 22 |
| **Standalone pages missing** | **22** |

The 22 missing pages fall into four tiers. They are ranked by how much
verified content already exists on the site to support each one without
inventing features.

## Full inventory

| Column | Item | Current destination | Page? | Published facts on the site | Tier |
|---|---|---|---|---|---|
| Accept & Collect | Payment Gateway | `/products/payment-gateway` | Yes | Full product page | — |
| | Payment Links | `/products/payment-links` | Yes | Full product page | — |
| | Payment Pages | enquiry form | **No** | One mention (an NGO example on `/solutions`) | 4 |
| | UPI Payments | enquiry form | **No** | UPI at checkout, UPI payouts, UPI in payment links (gateway, payouts, links pages) | **1** |
| | Recurring Payments | enquiry form | **No** | Recurring collection, retries and notifications (Payment Collection page) | 2 |
| | Subscription Billing | enquiry form | **No** | The same recurring-collection facts | 2 |
| | Smart Collections | `/products/payment-collection` | Yes | Full product page | — |
| Pay & Move Money | Payouts | `/products/payouts` | Yes | Full product page | — |
| | Bulk Payouts | enquiry form | **No** | "Single or bulk payouts" (Payouts page) | 2 |
| | Vendor Payments | enquiry form | **No** | Use case of Payouts | 2 |
| | Employee Payments | enquiry form | **No** | Use case of Payouts | 2 |
| | Partner Payments | enquiry form | **No** | Use case of Payouts | 2 |
| | International Payments | enquiry form | **No** | None; no page claims international or multi-currency support | 4 |
| Financial Operations | Reconciliation | `/products/payment-analytics` (shared) | Shared | Reconciliation views, automatic collection reconciliation, settlement records | **1** |
| | Settlements | `/products/payment-analytics` (shared) | Shared | Settlement reporting and visibility, settlement webhooks | **1** |
| | Refunds | `/products/payment-analytics` (shared) | Shared | Full or partial refunds by dashboard or API, refund tracking, Refunds API, refund webhooks | **1** |
| | Chargebacks | enquiry form | **No** | Only the customer-facing Refund Policy text | 4 |
| | Invoice Management | enquiry form | **No** | None | 4 |
| | Expense Management | enquiry form | **No** | None | 4 |
| | Finance Analytics | `/products/payment-analytics` (shared) | Shared | Same as Payment Analytics | 2 |
| | MIS & Reports | enquiry form | **No** | Exportable and scheduled reports (Payment Analytics page) | 2 |
| AI & Intelligence | Paynancial AI | enquiry form | **No** | Umbrella name only | 4 |
| | AI Fraud Detection | enquiry form | **No** | One-line capability description (`/agentic-ai/financial-agents`) | 3 |
| | AI Reconciliation | enquiry form | **No** | One-line capability description | 3 |
| | AI Financial Assistant | enquiry form | **No** | One-line capability description | 3 |
| | AI Cash-Flow Intelligence | enquiry form | **No** | One-line capability description | 3 |
| | AI Revenue Forecasting | enquiry form | **No** | One-line capability description | 3 |
| Embedded Finance | Embedded Payments | enquiry form | **No** | None (the Technology page covers embedded finance as an industry trend only) | 4 |
| | Embedded Payouts | enquiry form | **No** | None | 4 |
| | Embedded Billing | enquiry form | **No** | None | 4 |
| | Wallet Infrastructure | enquiry form | **No** | None | 4 |
| | Split Payments | enquiry form | **No** | One sentence (marketplaces on `/solutions`) | 4 |
| | White-Label Payments | enquiry form | **No** | None | 4 |
| Developer Platform | Payment APIs | `/developers/api-reference#payments` | Yes (section) | — | — |
| | Payout APIs | `/developers/api-reference#payouts` | Yes (section) | — | — |
| | SDKs | `/developers/sdks` | Yes | — | — |
| | Webhooks | `/developers/webhooks` | Yes | — | — |
| | Sandbox | `/sandbox` | Yes | — | — |
| | API Dashboard | `/developers` | **Mismatch** | No API dashboard page exists; the link opens the Developer Hub | — |

## Tiers and recommendations

**Tier 1 — build now (4 pages).** Each has a distinct intent and enough published, verifiable detail across the product, Developer and Analytics pages:

- `/products/refunds`
- `/products/settlements`
- `/products/reconciliation`
- `/products/upi-payments`

Building these also stops Refunds, Settlements and Reconciliation all landing on the Payment Analytics page.

**Tier 2 — do not build separately yet (9 items); link to the page that already covers them.**

| Item(s) | Covered by | Suggested link |
|---|---|---|
| Recurring Payments, Subscription Billing | Payment Collection | `/products/payment-collection` |
| Bulk Payouts, Vendor Payments, Employee Payments, Partner Payments | Payouts | `/products/payouts` |
| Finance Analytics, MIS & Reports | Payment Analytics | `/products/payment-analytics` |

A standalone page for these today would repeat those product pages (cannibalisation). Recurring/Subscription and the Payouts use cases could become their own pages later, with approved use-case content.

**Tier 3 — AI & Intelligence (5 items).** Each has only a one-line description. Link them to `/agentic-ai/financial-agents#capabilities`, where they are explained, instead of the enquiry form. Standalone pages need product specifications: what each one does, inputs, outputs, limits and availability.

**Tier 4 — no published facts (12 items):**
- Payment Pages
- International Payments
- Chargebacks
- Invoice Management
- Expense Management
- Paynancial AI
- Embedded Payments
- Embedded Payouts
- Embedded Billing
- Wallet Infrastructure
- Split Payments
- White-Label Payments

A page for any of these would have to invent features. Each needs, from the product team:

1. What it does, and whether it is live today or on request.
2. Who it is for.
3. How it works: the steps, and whether it runs from the dashboard, the API or both.
4. Any API resources or parameters.
5. Supported methods, currencies and regions (especially International Payments).
6. Limits, eligibility or pricing notes that may be published.

**Label fix.** "API Dashboard" opens the Developer Hub. Either relabel it "Developer Hub" or point it at the dashboard login.
