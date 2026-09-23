<?php
/**
 * Product capability pages — /products/{refunds,settlements,
 * reconciliation,upi-payments}. These are capabilities that span several
 * Paynancial products, so each page gathers what is already published
 * about them on the product pages, the Developer pages and Payment
 * Analytics into one destination.
 *
 * Content rule: nothing here goes beyond facts already published on the
 * site — no settlement timings, fees, success rates, UPI AutoPay or
 * mandates, currencies or limits. FAQs live in faq-data.php
 * (product:{slug}) so the FAQ hub lists them.
 */

declare(strict_types=1);

require_once __DIR__ . '/developer-docs.php';
require_once __DIR__ . '/faq-data.php';

function pc_pages(): array
{
    $resources = dev_resources();
    return [
        'refunds' => [
            'parent' => ['Financial Operations', '/financial-operations'],
            'name'  => 'Refunds',
            'title' => 'Refunds | Full & Partial Refunds by Dashboard or API | Paynancial',
            'description' => 'Issue full or partial refunds from the Paynancial dashboard or the Refunds API, track every refund from request to completion, and receive refund events by webhook.',
            'h1'    => 'Refund in full or in part — and see it through to completion.',
            'lead'  => 'Issue refunds from the dashboard or the API, against the original payment, and track each one from request to completion alongside your settlements.',
            'answer' => 'With Paynancial you can issue a full or partial refund against the original payment, from the dashboard or with the Refunds API, track it from request through to completion in Payment Analytics, and receive refund events by webhook.',
            'values' => ['Full or partial', 'Dashboard or API', 'Tracked to completion', 'Refund webhooks'],
            'aside_code' => dev_resource_code($resources['refunds']),
            'aside_caption' => 'POST /refunds',
            'what' => [
                'A refund returns all or part of a payment to the customer who made it. On Paynancial, every refund is issued against the original payment, so it stays traceable to the order, booking or invoice it belongs to.',
                'You can refund from the dashboard — useful for support teams handling one customer at a time — or from your own systems through the Refunds API, so returns and cancellations can be refunded automatically.',
            ],
            'steps' => [
                ['Find the payment', 'Locate the original payment in the dashboard, or use its payment ID in your system.'],
                ['Choose the amount', 'Refund the full amount, or a partial amount such as one item or a shipping charge.'],
                ['Issue the refund', 'From the dashboard, or with a POST to /refunds with the payment ID and amount.'],
                ['Get notified', 'A webhook tells your system as the refund changes state.'],
                ['Track to completion', 'Follow the refund from request to completion in Payment Analytics.'],
            ],
            'capabilities' => [
                ['Full or partial refunds', 'Refund the whole payment or any part of it.'],
                ['Dashboard or API', 'Support teams refund from the dashboard; systems refund through the Refunds API.'],
                ['Refund tracking', 'Follow each refund from request through to completion in Payment Analytics.'],
                ['Reconciliation views', 'See payments, refunds and settlements side by side in one place.'],
                ['Refund webhooks', 'Refunds are one of the four webhook event families, so your systems react as a refund changes state.'],
                ['Safe retries', 'Send an idempotency key with each refund request so a retried request never refunds twice.'],
            ],
            'surfaces' => [
                ['Dashboard', 'Issue a full or partial refund against a payment.'],
                ['API', '<code>POST /refunds</code> with <code>payment_id</code> and <code>amount</code> (in paise). See the <a class="inline-link" href="/developers/api-reference#refunds">API Reference</a>.'],
                ['Webhooks', 'Refund events as a refund changes state. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['Payment Analytics', 'Refund tracking and reconciliation views. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'practices' => [
                ['Refund against the original payment', 'It keeps every return traceable to its order, and your reconciliation clean.'],
                ['Use idempotency keys', 'A timeout on a refund request should never turn into two refunds.'],
                ['Publish your refund policy', 'Tell customers what is refundable and how before they pay, so refunds are predictable for both sides.'],
            ],
            'related' => ['settlements', 'reconciliation', 'product:payment-gateway', 'product:payment-analytics'],
        ],
        'settlements' => [
            'parent' => ['Financial Operations', '/financial-operations'],
            'name'  => 'Settlements',
            'title' => 'Settlements | Settlement Reporting & Visibility | Paynancial',
            'description' => 'See which payments have settled, what is pending and when it is due. Every Paynancial transaction is tied to a settlement record you can reconcile against, with settlement webhooks.',
            'h1'    => 'Know what has settled, what is pending and when it is due.',
            'lead'  => 'Every successful payment is queued for settlement and tied to a settlement record — so your finance team can see exactly which transactions each settlement covers.',
            'answer' => 'On Paynancial, every successful payment is recorded and queued for settlement, and every transaction is tied to a settlement record. Payment Analytics shows what has settled, what is pending and when it is due, and settlement webhooks tell your systems when funds are settled to your account.',
            'values' => ['Settlement records', 'Pending and settled', 'Settlement webhooks', 'Exportable reports'],
            'what' => [
                'Settlement is the step where the money from your customers\' payments is paid out to your business account. Knowing which payments a settlement includes is what lets a finance team close the books with confidence.',
                'On Paynancial, a successful payment is recorded and queued for settlement, and every transaction is tied to a settlement record. That link — from payment to settlement — is what the rest of your reconciliation depends on.',
            ],
            'steps' => [
                ['Payment succeeds', 'A successful payment is recorded against your order.'],
                ['Queued for settlement', 'The payment is queued for settlement and linked to a settlement record.'],
                ['Funds are settled', 'A settlement webhook tells your system when funds are settled to your account.'],
                ['Visible in Analytics', 'Payment Analytics shows what has settled and what is still pending.'],
                ['Reconciled', 'Match each settlement to its payments and refunds, then export or schedule the report.'],
            ],
            'capabilities' => [
                ['Settlement records', 'Every transaction is tied to a settlement record you can reconcile against.'],
                ['Settlement visibility', 'See what has settled, what is pending, and when it is due.'],
                ['Settlement webhooks', 'Settlements are one of the four webhook event families.'],
                ['Reconciliation views', 'Match payments against settlements and refunds in one place.'],
                ['Exportable reports', 'Download settlement data in formats your finance team already works with.'],
                ['Scheduled reports', 'Have recurring reports delivered without manual effort.'],
            ],
            'surfaces' => [
                ['Payment Analytics', 'Settlement visibility, reconciliation views and reports. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
                ['Webhooks', 'An event when funds are settled to your account. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['Payment Gateway', 'Settlement reporting behind every transaction. See <a class="inline-link" href="/products/payment-gateway">Payment Gateway</a>.'],
            ],
            'practices' => [
                ['Reconcile against settlement records', 'Match each settlement to the transactions and refunds it covers, not to a bank-statement total.'],
                ['Automate with webhooks', 'Update your books when the settlement event arrives, instead of checking by hand.'],
                ['Schedule the report', 'Have your finance team receive settlement reports on a schedule.'],
            ],
            'related' => ['reconciliation', 'refunds', 'product:payment-analytics', 'product:payment-gateway'],
        ],
        'reconciliation' => [
            'parent' => ['Financial Operations', '/financial-operations'],
            'name'  => 'Reconciliation',
            'title' => 'Payment Reconciliation | Match Payments, Refunds & Settlements | Paynancial',
            'description' => 'Reconcile payments, refunds and settlements in one place with Paynancial: settlement records for every transaction, automatic reconciliation of collections, discrepancies surfaced clearly, and exportable reports.',
            'h1'    => 'Payments, refunds and settlements — matched in one place.',
            'lead'  => 'Every transaction is tied to a settlement record, collections reconcile automatically, and discrepancies surface clearly — so month-end stops being a spreadsheet exercise.',
            'answer' => 'Paynancial ties every transaction to a settlement record, reconciles each collection automatically against the customer and cycle it belongs to, and shows payments, refunds and settlements side by side in Payment Analytics — with discrepancies surfaced clearly and reports you can export or schedule.',
            'values' => ['Settlement records', 'Automatic for collections', 'Discrepancies surfaced', 'Export or schedule'],
            'what' => [
                'Reconciliation is matching what you expected to be paid with what actually arrived — each payment to its order, each refund to its payment, and each settlement to the transactions it covers.',
                'Paynancial builds reconciliation into the records themselves. Every transaction is tied to a settlement record, every refund is issued against its payment, and every collection is matched to the customer and cycle it belongs to — so reconciling is reviewing, not rebuilding.',
            ],
            'steps' => [
                ['Record', 'Every transaction, settlement and refund is recorded as it happens.'],
                ['Link', 'Each transaction is tied to a settlement record; each collection to its customer and cycle.'],
                ['Match', 'Reconciliation views show payments against settlements and refunds in one place.'],
                ['Surface', 'Discrepancies surface clearly so they can be resolved.'],
                ['Report', 'Export reports or schedule them for your finance team.'],
            ],
            'capabilities' => [
                ['Reconciliation views', 'Match payments against settlements and refunds in one place (Payment Analytics).'],
                ['Automatic reconciliation for collections', 'Each collection is matched against the customer and cycle it belongs to (Payment Collection).'],
                ['Order-level reconciliation', 'Reconcile each transaction against your order in the dashboard or via webhook (Payment Gateway).'],
                ['Discrepancies surfaced', 'Discrepancies surface clearly so reconciliation stays manageable.'],
                ['Exportable and scheduled reports', 'Download reports or have them delivered on a schedule.'],
                ['AI Reconciliation', 'An AI capability that matches settlements against transactions and surfaces only genuine exceptions — ask our team about availability.'],
            ],
            'surfaces' => [
                ['Payment Analytics', 'Reconciliation views, discrepancies and reports. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
                ['Payment Collection', 'Automatic reconciliation of every collection cycle. See <a class="inline-link" href="/products/payment-collection">Payment Collection</a>.'],
                ['Webhooks', 'Payment, refund and settlement events to reconcile in your own systems. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['AI Reconciliation', 'Exception-first matching. See <a class="inline-link" href="/agentic-ai/financial-agents#capabilities">AI Financial Agents</a>.'],
            ],
            'practices' => [
                ['Use your own reference on every payment', 'An order, invoice or booking ID as the receipt makes matching straightforward.'],
                ['Reconcile daily, not monthly', 'Small, frequent reconciliation keeps discrepancies small and recent.'],
                ['Keep people on the exceptions', 'Let the matching run automatically and spend your team\'s time on what does not match.'],
            ],
            'related' => ['settlements', 'refunds', 'product:payment-analytics', 'product:payment-collection'],
        ],
        'upi-payments' => [
            'name'  => 'UPI Payments',
            'title' => 'UPI Payments | Accept UPI & Pay Out to UPI IDs | Paynancial',
            'description' => 'Accept UPI at checkout alongside cards, netbanking and wallets, and send payouts to UPI IDs — single or in bulk — through one Paynancial integration, with reporting and reconciliation behind every transaction.',
            'h1'    => 'Accept UPI, and pay out to UPI IDs, through one integration.',
            'lead'  => 'UPI sits alongside cards, netbanking and wallets at your checkout — and Payouts sends money straight to a beneficiary\'s UPI ID.',
            'answer' => 'With Paynancial you can accept UPI payments at your checkout through the Payment Gateway, alongside cards, netbanking and wallets, and send single or bulk payouts to UPI IDs with Payouts — all through one integration with reporting and reconciliation.',
            'values' => ['UPI at checkout', 'Payouts to UPI IDs', 'Single or bulk', 'One integration'],
            'aside_code' => dev_resource_code($resources['payouts']),
            'aside_caption' => 'A payout to a UPI beneficiary',
            'what' => [
                'UPI (Unified Payments Interface) lets customers in India pay directly from their bank account using a UPI app. For many customers it is the payment method they reach for first.',
                'Paynancial supports UPI in both directions: <strong>money in</strong>, as one of the payment methods on the Payment Gateway, and <strong>money out</strong>, as a payout mode for sending funds to a beneficiary\'s UPI ID.',
            ],
            'steps' => [
                ['Offer UPI at checkout', 'UPI appears alongside cards, netbanking and wallets on your hosted or custom checkout.'],
                ['Customer pays by UPI', 'The customer completes the payment in their UPI app.'],
                ['Get the result', 'Paynancial returns a real-time status and a webhook confirms the payment.'],
                ['Pay out by UPI', 'Add a beneficiary\'s UPI ID and send a single or bulk payout.'],
                ['Reconcile', 'Payments, payouts and settlements appear in your reports.'],
            ],
            'capabilities' => [
                ['UPI at checkout', 'UPI is one of the payment methods on the Payment Gateway, alongside cards, netbanking and wallets.'],
                ['Single or bulk payouts', 'Pay one beneficiary or submit a whole batch in one action.'],
                ['Payouts to UPI IDs', 'Send funds to a beneficiary\'s UPI ID, one at a time or in bulk.'],
                ['Beneficiary management', 'Save and reuse UPI beneficiaries for repeat payouts.'],
                ['Status tracking', 'Follow each payment and payout, with clear reasons when one fails.'],
                ['Reporting', 'Break down transactions by payment method in Payment Analytics.'],
            ],
            'surfaces' => [
                ['Payment Gateway', 'UPI as a checkout payment method. See <a class="inline-link" href="/products/payment-gateway">Payment Gateway</a>.'],
                ['Payouts', 'UPI as a payout mode (<code>mode=upi</code> in the API). See <a class="inline-link" href="/products/payouts">Payouts</a>.'],
                ['Payment Analytics', 'Transactions by method, status and period. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'practices' => [
                ['Show UPI prominently', 'Put the methods your customers use most where they see them first at checkout.'],
                ['Confirm by webhook', 'Mark an order paid when the payment webhook arrives, not when the customer returns to your site.'],
                ['Make payouts idempotent', 'Send an idempotency key with every payout so a retry never pays twice.'],
            ],
            'related' => ['refunds', 'settlements', 'product:payment-gateway', 'product:payouts'],
        ],
        // ---- Pay & Move Money: Payouts use cases ----------------------------
        // Built only from published Payouts facts: bank & UPI payouts, single
        // or bulk (a batch in one action / one request), dashboard or API,
        // saved beneficiaries, status from initiated to completed, clear
        // failure reasons, payout report, payout webhooks, idempotency keys,
        // "for businesses paying vendors, staff, freelancers, or channel
        // partners". Industry examples are not used as capability evidence.
        'bulk-payouts' => [
            'name'  => 'Bulk Payouts',
            'parent' => ['Pay & Move Money', '/pay-and-move-money'],
            'title' => 'Bulk Payouts | Pay a Whole Batch in One Action | Paynancial',
            'description' => 'Paynancial Bulk Payouts: submit a batch of payouts to bank accounts and UPI IDs in one action from the dashboard or in a single API request, then track every payout in the batch individually.',
            'h1'    => 'Pay a whole batch in one action — and track every payout in it.',
            'lead'  => 'Submit a batch of transfers to bank accounts and UPI IDs in one action, from the dashboard or in a single API request, and follow each payout in the batch from initiated to completed.',
            'answer' => 'Bulk Payouts is how Paynancial Payouts handles many payments at once: you submit a batch of transfers — to bank accounts or UPI IDs — in one action from the dashboard or in a single API request, and each payout in the batch is tracked individually, with a clear reason for any that fail.',
            'values' => ['One batch, one action', 'Bank and UPI', 'Tracked per payout', 'Failures you can fix'],
            'what' => [
                'Paying many people one transfer at a time is slow, and it is easy to miss one or pay one twice. Bulk payouts turn a list of payments into a single batch you submit once.',
                'On Paynancial, bulk payouts are part of <a class="inline-link" href="/products/payouts">Payouts</a>. The batch is submitted in one action, but every payout in it keeps its own status — so a single failed transfer does not hold up the rest, and you can see exactly which one needs attention.',
            ],
            'steps' => [
                ['Prepare the batch', 'List the beneficiaries — saved bank accounts or UPI IDs — and the amount for each.'],
                ['Submit once', 'Submit the batch in one action from the dashboard, or in a single API request.'],
                ['Processed', 'Paynancial processes each transfer and returns a status for each.'],
                ['Track each payout', 'Follow every payout from initiated to completed; failed ones come with a clear reason.'],
                ['Fix and report', 'Correct and retry any failures, then find the completed batch in your payout report.'],
            ],
            'capabilities' => [
                ['Batch submission', 'Submit a batch of transfers in one action or a single request.'],
                ['Per-payout status', 'Each payout in the batch is tracked individually.'],
                ['Failure reasons', 'A failed payout comes with a clear reason so it can be corrected and retried.'],
                ['Saved beneficiaries', 'Reuse bank and UPI details you have already saved.'],
                ['Duplicate protection', 'An idempotency key on each payout means re-running a batch after a timeout never pays anyone twice.'],
                ['Payout events', 'Payout webhooks tell your systems as each payout changes state.'],
            ],
            'surfaces' => [
                ['Dashboard', 'Submit and follow a batch without writing code.'],
                ['API', 'Submit a batch in a single request. The published example shows one payout on <code>POST /payouts</code> with an idempotency key; ask <a class="inline-link" href="/contact?intent=support">developer support</a> for the batch request format. See the <a class="inline-link" href="/developers/api-reference#payouts">API Reference</a>.'],
                ['Webhooks', 'An event as each payout in the batch changes state. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['Payout report', 'Completed payouts appear in your payout report. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'practices' => [
                ['One idempotency key per payout', 'Use a key built from your run ID and the beneficiary, so re-submitting a batch is always safe.'],
                ['Handle failures individually', 'Fix and retry the failed payouts only — the rest of the batch has already moved.'],
                ['Check the batch before you submit', 'Totals and beneficiaries are easier to correct before money moves than after.'],
            ],
            'related' => ['product:payouts', 'vendor-payments', 'employee-payments', 'pillar:pay-and-move-money'],
        ],
        'vendor-payments' => [
            'name'  => 'Vendor Payments',
            'parent' => ['Pay & Move Money', '/pay-and-move-money'],
            'title' => 'Vendor Payments | Pay Suppliers by Bank Transfer or UPI | Paynancial',
            'description' => 'Pay suppliers and vendors with Paynancial Payouts: save vendors as beneficiaries, pay them by bank transfer or UPI individually or in a batch, and keep a record of every vendor payment.',
            'h1'    => 'Pay your suppliers on time, with a record of every payment.',
            'lead'  => 'Save each vendor once, pay by bank transfer or UPI — one at a time or in a batch — and see every vendor payment from initiated to completed.',
            'answer' => 'Vendor payments on Paynancial are made with Payouts: you save each supplier as a beneficiary with their bank account or UPI ID, pay them individually or in a batch from the dashboard or API, and track every payment to completion in your payout report.',
            'values' => ['Vendors saved once', 'Bank or UPI', 'Single or batch', 'A record of every payment'],
            'what' => [
                'Supplier payments are regular, often due in batches, and every one needs a trail: who was paid, how much, when, and whether it arrived. Missing that trail is how a vendor ends up paid twice — or not at all.',
                'Paynancial handles vendor payments through <a class="inline-link" href="/products/payouts">Payouts</a>. Vendors are saved beneficiaries, so their details are entered once and reused. Each payment has its own status and appears in your payout report, which is the trail your accounts team reconciles against.',
            ],
            'steps' => [
                ['Add the vendor', 'Save the supplier\'s bank account or UPI ID as a beneficiary.'],
                ['Decide what to pay', 'Your accounts process decides which invoices are due; Paynancial pays them.'],
                ['Pay', 'Send a single payout, or pay several vendors in one batch.'],
                ['Track', 'Follow each payment from initiated to completed.'],
                ['Record', 'Use the payout report to match payments against your vendor invoices.'],
            ],
            'capabilities' => [
                ['Supplier beneficiaries', 'Save vendor bank and UPI details once and reuse them for every payment.'],
                ['One vendor or many', 'Pay a single supplier, or a batch of suppliers in one action.'],
                ['Status for every payment', 'See whether each vendor payment is initiated, completed or failed.'],
                ['Clear failure reasons', 'If a transfer fails, you know why and can correct and retry it.'],
                ['From your own systems', 'Trigger vendor payouts from your ERP or accounts software through the API.'],
                ['An audit trail', 'Every payout is tied to the API key or user session that made it.'],
            ],
            'surfaces' => [
                ['Payouts', 'The product behind every vendor payment. See <a class="inline-link" href="/products/payouts">Payouts</a>.'],
                ['Bulk Payouts', 'Pay many vendors in one action. See <a class="inline-link" href="/products/bulk-payouts">Bulk Payouts</a>.'],
                ['Payment Analytics', 'Payout reports to reconcile against invoices. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'surfaces_head' => ['Where', 'What it does for vendor payments'],
            'practices' => [
                ['Keep invoices and payouts linked', 'Record each payout ID against the vendor invoice it pays, in your own accounts system.'],
                ['Verify new vendor details', 'Confirm a new supplier\'s bank or UPI details through a separate channel before the first payment.'],
                ['Limit who can pay', 'If automated jobs or agents pay vendors, give them a beneficiary allow-list and limits. See AI Governance.'],
            ],
            'related' => ['product:payouts', 'bulk-payouts', 'partner-payments', 'pillar:pay-and-move-money'],
        ],
        'employee-payments' => [
            'name'  => 'Employee Payments',
            'parent' => ['Pay & Move Money', '/pay-and-move-money'],
            'title' => 'Employee Payments | Pay Staff & Freelancers by Payout | Paynancial',
            'description' => 'Pay staff and freelancers with Paynancial Payouts — to a bank account or UPI ID, individually or in a batch — with status tracking and a record of every payment. Paynancial is not a payroll system.',
            'h1'    => 'Pay your staff and freelancers — to their bank or UPI.',
            'lead'  => 'Once your payroll or finance process has decided who is owed what, Payouts sends the money to each person\'s bank account or UPI ID and tracks every payment to completion.',
            'answer' => 'Employee payments on Paynancial are made with Payouts: you send each staff member or freelancer the amount your own payroll or finance process has calculated, to their bank account or UPI ID, individually or in a batch, and track each payment. Paynancial is not a payroll system — it does not calculate salaries, deductions or taxes.',
            'values' => ['Bank or UPI', 'Individually or in a batch', 'Tracked to completion', 'Not a payroll system'],
            'what' => [
                'Paying people is the part of payroll that has to be right every time: the right amount, to the right account, once. Payouts handles that step — moving the money and keeping a record of it.',
                'It is important to be clear about the boundary. Paynancial does not calculate salaries, statutory deductions or taxes, and it does not replace your payroll software or accountant. You work out what each person is owed; <a class="inline-link" href="/products/payouts">Payouts</a> sends it.',
            ],
            'steps' => [
                ['Calculate', 'Your payroll or finance process calculates what each person is owed.'],
                ['Save each person', 'Save each employee\'s or freelancer\'s bank account or UPI ID as a beneficiary.'],
                ['Pay', 'Send individual payouts, or pay everyone in one batch.'],
                ['Track', 'Follow each payment from initiated to completed.'],
                ['Keep the record', 'Completed payouts appear in your payout report.'],
            ],
            'capabilities' => [
                ['Staff and freelancers', 'Pay employees, contractors and freelancers from the same place.'],
                ['Bank or UPI', 'Send to a bank account or a UPI ID.'],
                ['Batch pay runs', 'Pay everyone in one batch, with each payment tracked individually.'],
                ['Failures you can fix', 'A failed payment comes with a clear reason so it can be corrected and retried.'],
                ['Payment records', 'Every payout appears in your payout report once completed.'],
                ['Clear boundary', 'Salary, deduction and tax calculations stay with your payroll process.'],
            ],
            'surfaces' => [
                ['Payouts', 'The product behind every payment. See <a class="inline-link" href="/products/payouts">Payouts</a>.'],
                ['Bulk Payouts', 'Pay everyone in one action. See <a class="inline-link" href="/products/bulk-payouts">Bulk Payouts</a>.'],
                ['Payment Analytics', 'Payout reports for your records. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'surfaces_head' => ['Where', 'What it does for employee payments'],
            'practices' => [
                ['Keep payroll calculations in payroll', 'Use Paynancial for moving the money, not for working out the amounts.'],
                ['Protect personal data', 'Only the details a payout needs — account or UPI ID and amount — should go into Paynancial.'],
                ['Use one key per pay run', 'Idempotency keys stop a retried pay run from paying anyone twice.'],
            ],
            'related' => ['product:payouts', 'bulk-payouts', 'vendor-payments', 'pillar:pay-and-move-money'],
        ],
        'partner-payments' => [
            'name'  => 'Partner Payments',
            'parent' => ['Pay & Move Money', '/pay-and-move-money'],
            'title' => 'Partner Payments | Pay Channel & Business Partners | Paynancial',
            'description' => 'Pay channel partners and business partners with Paynancial Payouts — to a bank account or UPI ID, singly or in a batch, from the dashboard or triggered by your own systems through the API.',
            'h1'    => 'Pay your channel and business partners, reliably.',
            'lead'  => 'Your systems work out what each partner has earned; Payouts pays them by bank transfer or UPI — one partner at a time or all of them in a batch — and keeps a record of every payment.',
            'answer' => 'Partner payments on Paynancial are made with Payouts: once your own systems have calculated what each channel or business partner is owed, you pay them by bank transfer or UPI, individually or in a batch, from the dashboard or via the API, and track each payout to completion.',
            'values' => ['Channel partners', 'Bank or UPI', 'Triggered by your systems', 'Tracked to completion'],
            'what' => [
                'Businesses that sell through channel partners, resellers or referral partners owe them money on a regular cycle. The amounts are usually worked out in a partner or commission system — the payment itself still has to be made, recorded and reconciled.',
                '<a class="inline-link" href="/products/payouts">Payouts</a> is that payment step. Because it is API-first, the system that calculates what partners are owed can trigger the payouts directly, with idempotency keys so a retried run never pays a partner twice.',
                'Note: this page is about paying your own partners. Becoming a partner of Paynancial is a different thing — see the <a class="inline-link" href="/partners">Partner Program</a>.',
            ],
            'steps' => [
                ['Calculate', 'Your partner or commission system works out what each partner is owed.'],
                ['Save partners', 'Save each partner\'s bank account or UPI ID as a beneficiary.'],
                ['Trigger', 'Pay from the dashboard, or let your system call the Payouts API.'],
                ['Track', 'Follow each partner payout from initiated to completed.'],
                ['Reconcile', 'Match payouts to partner statements using your payout report.'],
            ],
            'capabilities' => [
                ['Triggered by your systems', 'Payouts is API-first, so your partner system can pay partners directly.'],
                ['Safe to retry', 'An idempotency key per payout means a retried run never pays a partner twice.'],
                ['Bank or UPI', 'Pay partners to a bank account or UPI ID.'],
                ['One or many', 'Pay a single partner, or everyone due in one batch.'],
                ['Real-time updates', 'Payout webhooks tell your partner system when each payment completes or fails.'],
                ['Clear boundary', 'Commission rules and calculations stay in your own systems.'],
            ],
            'surfaces' => [
                ['Payouts API', '<code>POST /payouts</code> with an idempotency key. See the <a class="inline-link" href="/developers/api-reference#payouts">API Reference</a>.'],
                ['Webhooks', 'Payout events back into your partner system. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['Payment Analytics', 'Payout reports for partner statements. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'surfaces_head' => ['Where', 'What it does for partner payments'],
            'practices' => [
                ['Keep commission logic in one place', 'Calculate partner earnings in your own system and send Paynancial only the amounts to pay.'],
                ['Use the partner ID in your idempotency key', 'It makes every run safe to repeat.'],
                ['Send partners a statement', 'Pair each payout with a statement from your system so partners can reconcile too.'],
            ],
            'related' => ['product:payouts', 'bulk-payouts', 'vendor-payments', 'pillar:pay-and-move-money'],
        ],
    ];
}

function pc_page(string $slug): ?array
{
    $all = pc_pages();
    return isset($all[$slug]) ? ['slug' => $slug] + $all[$slug] : null;
}

/** Related card: capability slug or product:{slug}. */
function pc_related_card(string $key): array
{
    $products = [
        'payment-gateway' => ['Payment Gateway', 'Cards, UPI, netbanking and wallets through one integration.'],
        'payment-links' => ['Payment Links', 'Get paid without a website or checkout.'],
        'payment-collection' => ['Smart Collections', 'Recurring and bulk collections with reconciliation built in.'],
        'payouts' => ['Payouts', 'Send money to bank accounts and UPI IDs.'],
        'payment-analytics' => ['Payment Analytics', 'Dashboards, settlements, reconciliation and reports.'],
    ];
    if (str_starts_with($key, 'pillar:')) {
        require_once __DIR__ . '/product-categories.php';
        $c = cat_page(substr($key, 7));
        return [$c['name'], $c['lead'], cat_url($c['slug'])];
    }
    if ($key === 'ai:hub') {
        return ['AI & Intelligence', 'Paynancial\'s connected AI capabilities, with the governance around them.', '/ai-intelligence'];
    }
    if (str_starts_with($key, 'ai:')) {
        require_once __DIR__ . '/ai-intelligence.php';
        $a = ai_child(substr($key, 3));
        return [$a['name'], $a['lead'], $a['path']];
    }
    if (str_starts_with($key, 'category:')) {
        require_once __DIR__ . '/product-categories.php';
        $c = cat_page(substr($key, 9));
        return [$c['name'], $c['lead'], cat_url($c['slug'])];
    }
    if (str_starts_with($key, 'product:')) {
        $slug = substr($key, 8);
        return [$products[$slug][0], $products[$slug][1], '/products/' . $slug];
    }
    $p = pc_page($key);
    return [$p['name'], $p['lead'], '/products/' . $key];
}
