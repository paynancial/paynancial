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
        // ---- Standalone pages for the remaining pillar items ------------------
        // Indexable only where the content is verified: MIS & Reports (Payment
        // Analytics + Reports API) and Chargebacks (the published Refund
        // Policy's chargeback process). International Payments, Invoice
        // Management and Expense Management are informative guides with the
        // published facts only ('guide' => true): indexable at the owner's
        // request, but with no Service schema, since the capability itself is
        // not confirmed.
        'mis-reports' => [
            'parent' => ['Financial Operations', '/financial-operations'],
            'name'  => 'MIS & Reports',
            'title' => 'MIS & Reports | Payment Reports for Finance Teams in India | Paynancial',
            'description' => 'Paynancial MIS & Reports: transaction reports by payment method, status and period, with settlements and refunds alongside — exported, delivered on a schedule, or generated for a date range through the Reports API.',
            'h1'    => 'The payment numbers your management review needs — exported or scheduled.',
            'lead'  => 'Turn every transaction, settlement and refund into reports you can export, schedule or pull through the API, so month-end and MIS reporting start from the same records as your payments.',
            'answer' => 'MIS & Reports is the reporting side of Paynancial Payment Analytics: transaction reports broken down by payment method, status and period, with settlements and refunds alongside, that you can export, have delivered on a schedule, or generate for a date range through the Reports API — for example as a CSV file.',
            'values' => ['Export or schedule', 'By method, status and period', 'Settlements and refunds', 'Reports API'],
            'secondary' => ['Explore Payment Analytics', '/products/payment-analytics', 'cta_click'],
            'aside_code' => dev_resource_code($resources['reports']),
            'aside_caption' => 'A transaction report for one month',
            'what' => [
                'MIS — management information system — reporting is how owners, finance leads and boards get a regular, trusted view of the numbers. For payments, the questions are always the same: how much came in, through which methods, what has settled, what was refunded and what is still pending.',
                'On Paynancial, MIS & Reports is part of <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>. The reports are built from the same records as your payments, settlements and refunds, so the figures in a management pack match the figures behind every transaction — no retyping from a bank statement.',
            ],
            'steps' => [
                ['Recorded as it happens', 'Every transaction, settlement and refund is recorded when it occurs.'],
                ['Choose the view', 'Filter by payment method, status and date to get the cut your report needs.'],
                ['Export', 'Download the report in a format your finance team already works with.'],
                ['Schedule', 'Have recurring reports delivered without anyone remembering to run them.'],
                ['Automate', 'Generate a transaction report for any date range with the Reports API.'],
            ],
            'capabilities' => [
                ['Transaction reports', 'Every transaction for a period, with its method, status and amount.'],
                ['Breakdown by method', 'See cards, UPI, netbanking and wallets side by side.'],
                ['Settlement reporting', 'What has settled, what is pending and when it is due.'],
                ['Refund reporting', 'Each refund from request through to completion.'],
                ['Exports and schedules', 'Download on demand or receive reports on a schedule.'],
                ['Reports API', '<code>/reports/transactions</code> with a date range and format, returning a download link.'],
            ],
            'surfaces' => [
                ['Payment Analytics', 'Dashboards, filters, exports and scheduled reports. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
                ['Reports API', 'Transaction reports for a date range, for example as CSV. See the <a class="inline-link" href="/developers/api-reference#reports">API Reference</a>.'],
                ['Webhooks', 'Settlement, payment, payout and refund events to keep your own MIS up to date. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
                ['Reconciliation', 'Match before you report. See <a class="inline-link" href="/products/reconciliation">Reconciliation</a>.'],
            ],
            'india' => [
                'title' => 'Reporting for businesses in India.',
                'items' => [
                    ['UPI next to cards', 'For many businesses in India, UPI is a major payment method. Seeing UPI, cards, netbanking and wallets side by side shows which methods your revenue depends on.'],
                    ['Rupees and paise', 'The API works in the smallest currency unit — paise for INR — as in the published examples, so report totals line up with individual transactions.'],
                    ['Hand-off to your CA', 'Export a period\'s transactions for your accountant or CA to work from, instead of copying figures out of a bank statement.'],
                    ['Month-end close', 'Pull the same date range every month, reconcile it against settlements, and your MIS pack starts from matched numbers.'],
                ],
            ],
            'practices' => [
                ['Use fixed date boundaries', 'Report the same calendar period every time, so month-on-month comparisons are like for like.'],
                ['Reconcile before you report', 'Match payments to settlements and refunds first; report on reconciled figures.'],
                ['Automate the routine pull', 'Schedule the recurring report or call the Reports API, and keep people for the analysis.'],
            ],
            'related' => ['product:payment-analytics', 'reconciliation', 'settlements', 'pillar:financial-operations'],
        ],
        'chargebacks' => [
            'parent' => ['Financial Operations', '/financial-operations'],
            'name'  => 'Chargebacks',
            'title' => 'Chargebacks & Disputes | How Chargebacks Work for Merchants in India | Paynancial',
            'description' => 'How chargebacks and payment disputes work on Paynancial: the customer disputes a payment with their card issuer or bank, Paynancial shares the transaction evidence with the merchant, the merchant responds, and the card network or bank decides.',
            'h1'    => 'Chargebacks and disputes — what happens, and how to respond.',
            'lead'  => 'When a customer disputes a payment with their card issuer or bank, Paynancial shares the transaction evidence with you so you can respond within the network\'s timeline. Here is how the process works, and how to reduce disputes in the first place.',
            'answer' => 'A chargeback is a payment reversal a customer raises with their card issuer or bank — for example when they believe a transaction was unauthorised, or that the merchant did not deliver as promised. Under Paynancial\'s Refund Policy, when Paynancial receives a chargeback notification it shares the relevant transaction evidence with the merchant, who can respond within the network\'s applicable timeline (typically 7–10 days). The card network or bank makes the final decision.',
            'values' => ['Evidence shared with you', 'Time to respond', 'Network or bank decides', 'Refunds tracked'],
            'secondary' => ['Read the Refund Policy', '/legal/refund-policy#chargebacks', 'cta_click'],
            'availability' => 'This page describes the chargeback process in Paynancial\'s published Refund Policy. A dedicated chargeback management tool — such as a disputes dashboard or API — is not described on this site; ask our team.',
            'what' => [
                'A refund and a chargeback both return money to a customer, but they start in different places. A refund is something you issue. A chargeback is something the customer raises through their own bank or card issuer — usually because they did not recognise the payment, or because an order was not delivered and the matter was not resolved directly.',
                'On Paynancial, the chargeback process is set out in the <a class="inline-link" href="/legal/refund-policy#chargebacks">Refund Policy</a>: Paynancial receives the notification, shares the transaction evidence with you, and you respond before the network\'s deadline. Every <a class="inline-link" href="/products/refunds">refund</a> you issue is tracked to completion — often the strongest answer to a "not refunded" dispute.',
            ],
            'steps' => [
                ['Customer disputes', 'The customer raises a chargeback with their card issuer or bank.'],
                ['Paynancial is notified', 'The chargeback notification reaches Paynancial.'],
                ['Evidence shared', 'Paynancial shares the relevant transaction evidence with you.'],
                ['You respond', 'Respond with your evidence within the network\'s applicable timeline — typically 7–10 days, per the Refund Policy.'],
                ['Decision', 'The card network or bank makes the final determination.'],
            ],
            'caps_head' => ['What Paynancial does', 'Your side of the process.', 'As set out in the Refund Policy and the Paynancial platform.'],
            'capabilities' => [
                ['Chargeback notification', 'Paynancial receives the chargeback notification from the network or bank.'],
                ['Transaction evidence', 'The relevant transaction evidence is shared with you, so you can respond.'],
                ['A window to respond', 'You can respond within the network\'s applicable timeline — typically 7–10 days.'],
                ['Refund records', 'Each refund is tracked from request to completion, which shows whether a customer was already refunded.'],
                ['Audit trail', 'Every payment, payout and refund is tied to the API key or session that made it.'],
                ['Transaction history', 'Payment Analytics shows each transaction with its method, status and date.'],
            ],
            'surfaces_head' => ['Where', 'What it gives you'],
            'surfaces' => [
                ['Refund Policy', 'The published chargeback and dispute process. See <a class="inline-link" href="/legal/refund-policy#chargebacks">Chargebacks &amp; Disputes</a>.'],
                ['Refunds', 'Refund before a dispute starts, and show the record if it does. See <a class="inline-link" href="/products/refunds">Refunds</a>.'],
                ['Payment Analytics', 'The transaction details your response relies on. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
                ['Webhooks', 'Payment and refund events so your own order records stay accurate. See <a class="inline-link" href="/developers/webhooks">Webhooks</a>.'],
            ],
            'india' => [
                'title' => 'Disputes for merchants in India.',
                'items' => [
                    ['Card payments', 'A cardholder raises a chargeback with the bank that issued their card; the card network\'s rules set the process and the deadlines.'],
                    ['UPI and netbanking', 'For account-based payments, the customer raises the dispute with their own bank, which handles it.'],
                    ['Failed or duplicate debits', 'If money was debited but the merchant got no confirmation, the Refund Policy says the payment is flagged for reconciliation and usually reversed automatically — that is not a chargeback.'],
                    ['Your refund policy', 'Merchants on Paynancial must publish a clear refund and cancellation policy and process approved refunds promptly.'],
                ],
            ],
            'practices' => [
                ['Refund before they dispute', 'A prompt refund for a genuine problem costs less than a dispute.'],
                ['Keep the evidence', 'Hold on to delivery confirmations, invoices and customer messages for every order.'],
                ['Be recognisable', 'Make sure customers can recognise your business name on their statement and reach you easily.'],
                ['Respond on time', 'Treat the network\'s deadline as fixed; a late response can lose a dispute you would have won.'],
            ],
            'related' => ['refunds', 'product:payment-gateway', 'reconciliation', 'pillar:financial-operations'],
        ],
        'international-payments' => [
            'parent' => ['Pay & Move Money', '/pay-and-move-money'],
            'guide' => true,
            'name'  => 'International Payments',
            'title' => 'International Payments | Cross-Border Payments for Indian Businesses | Paynancial',
            'description' => 'What Paynancial publishes today about international payments, what cross-border payments involve for a business in India, and what to confirm with our team before you plan around them.',
            'h1'    => 'International payments — what to know before you plan.',
            'lead'  => 'Paynancial Payouts send money to bank accounts and UPI IDs today. If your business needs to pay or get paid across borders, here is what that involves in India, and what to confirm with our team.',
            'answer' => 'Paynancial\'s published payout capability sends funds to bank accounts and UPI IDs, singly or in bulk. Countries, currencies, foreign exchange and cross-border availability are not published on this site, so international payments are something to discuss with our team rather than a capability claimed here.',
            'values' => ['Published facts only', 'India context', 'Questions to ask', 'Talk to our team'],
            'secondary' => ['Explore Payouts', '/products/payouts', 'cta_click'],
            'availability' => 'International payments are not yet described as a Paynancial product. Nothing on this page claims cross-border availability, supported countries or currencies.',
            'confirm' => ['Which countries you need to pay or receive from', 'Which currencies, and how conversion is handled', 'Whether you are paying out, collecting, or both', 'Fees, timelines and the documents required'],
            'what' => [
                'An international payment is any payment where the payer and the payee are in different countries. Compared with a domestic payout, it adds a foreign currency, a conversion rate, overseas bank details and — for a business in India — the rules of the country\'s foreign exchange framework.',
                'What Paynancial publishes today is domestic-neutral: <a class="inline-link" href="/products/payouts">Payouts</a> to bank accounts and UPI IDs, with saved beneficiaries, idempotency keys and payout webhooks. If you need cross-border payments, tell our team the countries, currencies and direction, and get a clear answer before you build around it.',
            ],
            'steps' => [
                ['Know the direction', 'Decide whether you need to pay suppliers abroad, receive from customers abroad, or both.'],
                ['List countries and currencies', 'Write down where the money goes or comes from, and in which currency.'],
                ['Check your paperwork', 'Ask your CA which registrations and documents your cross-border activity needs.'],
                ['Ask about conversion', 'Understand who converts the currency, at what rate, and what it costs.'],
                ['Talk to our team', 'Share your requirements and get a clear answer on availability.'],
            ],
            'caps_head' => ['Published today', 'What Paynancial offers now.', 'Domestic payout capabilities — not a claim of cross-border support.'],
            'capabilities' => [
                ['Payouts to bank accounts', 'Send funds to a beneficiary\'s bank account from the dashboard or API.'],
                ['Payouts to UPI IDs', 'Pay a beneficiary\'s UPI ID directly.'],
                ['Single or bulk', 'Pay one beneficiary or submit a batch in one action.'],
                ['Safe retries', 'Idempotency keys mean a retried payout never pays twice.'],
                ['Payout events', 'Webhooks as each payout changes state.'],
                ['Payout reporting', 'Completed payouts appear in your reports for reconciliation.'],
            ],
            'surfaces' => [
                ['Payouts', 'Bank and UPI payouts today. See <a class="inline-link" href="/products/payouts">Payouts</a>.'],
                ['Bulk Payouts', 'Batches of domestic payouts. See <a class="inline-link" href="/products/bulk-payouts">Bulk Payouts</a>.'],
                ['Developers', 'The Payouts API and webhooks. See the <a class="inline-link" href="/developers/api-reference#payouts">API Reference</a>.'],
                ['Our team', 'Cross-border requirements. <a class="inline-link" href="/contact?intent=sales&amp;product=international-payments">Ask about international payments</a>.'],
            ],
            'india' => [
                'title' => 'Cross-border payments from India.',
                'items' => [
                    ['FEMA and the RBI', 'Foreign exchange transactions by businesses in India are governed by the Foreign Exchange Management Act, 1999, with the Reserve Bank of India as the regulator.'],
                    ['Authorised Dealer banks', 'Cross-border remittances are generally routed through banks the RBI has authorised to deal in foreign exchange.'],
                    ['Purpose and documents', 'Banks usually ask for the purpose of a remittance and supporting documents, such as an invoice or contract.'],
                    ['Tax', 'Payments to and from abroad can have GST and income-tax consequences. Take advice from your CA.'],
                ],
            ],
            'practices' => [
                ['Confirm before you promise', 'Do not quote international payment terms to customers or suppliers until availability is confirmed.'],
                ['Keep flows separate', 'Report domestic and international payments separately so conversion never muddles your reconciliation.'],
                ['Get advice early', 'Speak to your CA before the first cross-border payment, not after.'],
            ],
            'related' => ['product:payouts', 'upi-payments', 'bulk-payouts', 'pillar:pay-and-move-money'],
        ],
        'invoice-management' => [
            'parent' => ['Financial Operations', '/financial-operations'],
            'guide' => true,
            'name'  => 'Invoice Management',
            'title' => 'Invoice Payments | Put a Payment Link on Every Invoice | Paynancial',
            'description' => 'Get invoices paid in one step with Paynancial: add a Payment Link to an invoice, track whether each link is active, paid, expired or disabled, and collect recurring invoices with Smart Collections. What is published today, and what to confirm.',
            'h1'    => 'Get invoices paid in one step — with a link on every invoice.',
            'lead'  => 'Add a Payment Link to an invoice so the customer pays in one step, and see whether each link is active, paid, expired or disabled — from the dashboard or the API.',
            'answer' => 'Paynancial helps invoices get paid rather than creating them: you add a Payment Link to an invoice so the customer can pay in one step, each link shows whether it is active, paid, expired or disabled, and recurring invoices can be collected on a schedule with Smart Collections. Invoice creation, numbering and tax calculation are not described on this site.',
            'values' => ['A link on every invoice', 'Paid in one step', 'Link status tracked', 'Recurring with Smart Collections'],
            'secondary' => ['Explore Payment Links', '/products/payment-links', 'cta_click'],
            'aside_code' => dev_resource_code($resources['payment_links']),
            'aside_caption' => 'A payment link for an invoice',
            'availability' => 'Invoice creation and management as a product is not yet described on this site. This page covers the published way to get invoices paid — Payment Links and Smart Collections.',
            'confirm' => ['Creating and numbering invoices', 'Tax calculation on invoices', 'Automatic payment reminders', 'A customer ledger of invoices and payments'],
            'what' => [
                'Many businesses send an invoice as a PDF by email or a messaging app and wait for a bank transfer — then match each transfer to an invoice by hand. The slow part is not writing the invoice; it is getting it paid and knowing which invoices are still open.',
                'Paynancial addresses the payment side. Create a <a class="inline-link" href="/products/payment-links">Payment Link</a> titled with the invoice number, put it on the invoice, and the customer pays in one step. The link\'s status tells you whether it has been paid, and payment webhooks let your accounting system mark the invoice settled.',
            ],
            'steps' => [
                ['Issue the invoice', 'Create the invoice in your accounting tool as you do today.'],
                ['Create a Payment Link', 'Title it with the invoice number and set the amount — or leave the amount for the customer to enter.'],
                ['Add it to the invoice', 'Put the link on the invoice, or send it alongside.'],
                ['Customer pays', 'The customer opens the link and pays in one step.'],
                ['Track and close', 'See the link as paid, and let the payment webhook update your records.'],
            ],
            'caps_head' => ['Published today', 'What you can use now.', 'Payment Links and Smart Collections — not an invoicing system.'],
            'capabilities' => [
                ['Payment Links on invoices', 'Add a link to an invoice so the customer can pay in one step.'],
                ['Link status', 'Each link shows whether it is active, paid, expired or disabled.'],
                ['Fixed or open amounts', 'Set the invoice amount, or omit it to let the customer enter it.'],
                ['Recurring invoices', 'Collect subscription and instalment payments on a schedule with Smart Collections.'],
                ['Payment events', 'Payment webhooks tell your systems when an invoice has been paid.'],
                ['Reports', 'Every payment appears in Payment Analytics for reconciliation.'],
            ],
            'surfaces' => [
                ['Payment Links', 'Links for one-off invoices. See <a class="inline-link" href="/products/payment-links">Payment Links</a>.'],
                ['Smart Collections', 'Recurring and scheduled invoices. See <a class="inline-link" href="/products/payment-collection">Payment Collection</a>.'],
                ['Payment Links API', '<code>/payment_links</code> with a title, amount and currency. See the <a class="inline-link" href="/developers/api-reference#payment-links">API Reference</a>.'],
                ['Reconciliation', 'Match invoice payments to settlements. See <a class="inline-link" href="/products/reconciliation">Reconciliation</a>.'],
            ],
            'india' => [
                'title' => 'Invoices in India.',
                'items' => [
                    ['GST invoices', 'A GST-registered business issues tax invoices under GST rules. Paynancial does not generate GST invoices; keep using your accounting software or CA for that.'],
                    ['GST registration', 'Not registered yet? Paynancial Business Services can help with GST registration.'],
                    ['Amounts in rupees', 'Payment Link amounts are set in paise for INR in the API — 500000 is ₹5,000.00.'],
                    ['Reference the invoice', 'Use the invoice number as the link title so the payment and the invoice match at a glance.'],
                ],
            ],
            'practices' => [
                ['One link per invoice', 'A separate link for each invoice keeps status and reconciliation one-to-one.'],
                ['Title with the invoice number', 'The customer sees what they are paying; your team sees which invoice is settled.'],
                ['Disable superseded links', 'If an invoice is cancelled or reissued, disable the old link.'],
            ],
            'related' => ['product:payment-links', 'product:payment-collection', 'reconciliation', 'pillar:financial-operations'],
        ],
        'expense-management' => [
            'parent' => ['Financial Operations', '/financial-operations'],
            'guide' => true,
            'name'  => 'Expense Management',
            'title' => 'Expense Management | Pay Vendors and Staff, Tracked and Reported | Paynancial',
            'description' => 'What Paynancial publishes today for business spending in India — paying vendors and staff through Payouts to bank accounts and UPI IDs, with every payout tracked and reported — and what an expense management product would add.',
            'h1'    => 'Business spending — paid, tracked and reported in one place.',
            'lead'  => 'Expense management is not yet described as a Paynancial product. What is published covers the payment side of spending: paying vendors and staff through Payouts, with every payout tracked and reported.',
            'answer' => 'Paynancial does not publish an expense management product. It does publish the payment side of spending: Payouts send money to vendors\' and staff members\' bank accounts and UPI IDs, singly or in bulk, each payout is tracked with a clear reason if it fails, and completed payouts appear in your reports for reconciliation.',
            'values' => ['Published facts only', 'Vendor and staff payouts', 'Tracked and reported', 'Talk to our team'],
            'secondary' => ['Explore Vendor Payments', '/products/vendor-payments', 'cta_click'],
            'availability' => 'Expense management — receipt capture, approval workflows, spend policies or company cards — is not described on this site. Nothing on this page claims those capabilities.',
            'confirm' => ['Receipt capture and expense claims', 'Approval workflows and spend policies', 'Company or employee cards', 'Budgets and category reporting'],
            'what' => [
                'Expense management covers how a business approves, pays and records what it spends: supplier bills, staff claims, recurring costs. Most of the work is in the approval and the record-keeping; the payment is the moment money actually moves.',
                'That moment is what Paynancial publishes today. Once your own process has approved a bill or a claim, <a class="inline-link" href="/products/payouts">Payouts</a> sends the money — to a <a class="inline-link" href="/products/vendor-payments">vendor</a> or an <a class="inline-link" href="/products/employee-payments">employee</a> — and every payout is tracked and reported.',
            ],
            'steps' => [
                ['Approve in your process', 'Approve bills and claims the way your business does today.'],
                ['Pay through Payouts', 'Send the approved amounts to bank accounts or UPI IDs, singly or in a batch.'],
                ['Track each payout', 'Follow every payout to completion, with clear reasons for any failure.'],
                ['Report', 'Completed payouts appear in your payout report.'],
                ['Reconcile', 'Match payouts against your books in Payment Analytics.'],
            ],
            'caps_head' => ['Published today', 'The payment side of spending.', 'Payout capabilities — not an expense management system.'],
            'capabilities' => [
                ['Vendor payments', 'Pay suppliers by bank transfer or UPI, with a record of every payment.'],
                ['Staff payments', 'Pay staff amounts your own process has approved, to a bank account or UPI ID.'],
                ['Bulk payouts', 'Pay a whole batch of approved bills or claims in one action.'],
                ['Saved beneficiaries', 'Save a vendor\'s or employee\'s details once and reuse them.'],
                ['Failure reasons', 'A failed payout comes with a clear reason so it can be fixed and retried.'],
                ['Payout reports', 'Every completed payout appears in your reports.'],
            ],
            'surfaces' => [
                ['Vendor Payments', 'Paying suppliers. See <a class="inline-link" href="/products/vendor-payments">Vendor Payments</a>.'],
                ['Employee Payments', 'Paying staff. See <a class="inline-link" href="/products/employee-payments">Employee Payments</a>.'],
                ['Bulk Payouts', 'Paying many at once. See <a class="inline-link" href="/products/bulk-payouts">Bulk Payouts</a>.'],
                ['Payment Analytics', 'Reports and reconciliation. See <a class="inline-link" href="/products/payment-analytics">Payment Analytics</a>.'],
            ],
            'india' => [
                'title' => 'Business spending in India.',
                'items' => [
                    ['Keep the GST invoice', 'Keep the supplier\'s GST invoice for each business expense — your CA needs it to claim input tax credit.'],
                    ['TDS', 'Some payments to vendors and contractors attract TDS under the Income-tax Act. Paynancial does not calculate or deduct TDS; agree the net amount with your CA before paying.'],
                    ['UPI or bank account', 'Pay a supplier or a staff member to their bank account or straight to a UPI ID.'],
                    ['Rupees and paise', 'Payout amounts are set in paise for INR in the API — 250000 is ₹2,500.00.'],
                ],
            ],
            'practices' => [
                ['Approve before you pay', 'Keep approval in your own process; pay only what has been approved.'],
                ['Reference every payout', 'Use a reference that ties each payout to its bill or claim.'],
                ['Pay in batches', 'Pay approved bills and claims on a fixed cycle with a bulk payout.'],
            ],
            'related' => ['vendor-payments', 'employee-payments', 'product:payment-analytics', 'pillar:financial-operations'],
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
