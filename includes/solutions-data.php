<?php
/**
 * Solutions — industry pages (/solutions/{slug}). Single source of truth
 * for the industry pages and for the industry cards on /solutions.
 *
 * Content rule: industry challenges are described in general terms (how
 * businesses in that industry commonly collect and move money), and the
 * Paynancial side only uses capabilities already published on the
 * product pages (sol_products()). No statistics, customer names,
 * outcomes, certifications or regulatory claims.
 */

declare(strict_types=1);

/** Products with a page, and the published capabilities an industry page may cite. */
function sol_products(): array
{
    return [
        'payment-gateway'    => ['Payment Gateway', '/products/payment-gateway', 'Cards, UPI, netbanking and wallets through one integration, with a hosted or custom checkout, full or partial refunds and settlement reporting.'],
        'payment-links'      => ['Payment Links', '/products/payment-links', 'Shareable links with a fixed or open amount and an expiry date, sent by email, WhatsApp, SMS or on an invoice — no website needed.'],
        'payment-collection' => ['Smart Collections', '/products/payment-collection', 'Recurring and bulk collection on a schedule, with retries for failed attempts, customer notifications and automatic reconciliation.'],
        'payouts'            => ['Payouts', '/products/payouts', 'Single or bulk payouts to bank accounts and UPI IDs, with saved beneficiaries, status tracking and clear failure reasons.'],
        'payment-analytics'  => ['Payment Analytics', '/products/payment-analytics', 'Transaction dashboards, settlement visibility, reconciliation views, refund tracking and exportable or scheduled reports.'],
    ];
}

/** Old /solutions#anchor → new slug (for links and client-side forwarding). */
function sol_legacy_anchors(): array
{
    return ['ecommerce' => 'e-commerce'];
}

function sol_url(string $slug): string
{
    return '/solutions/' . $slug;
}

/** Industry pages. Order is the order used in menus and related links. */
function sol_industries(): array
{
    return [
        'e-commerce' => [
            'name'  => 'E-Commerce',
            'title' => 'E-Commerce Payments | Online Checkout & Refunds | Paynancial',
            'description' => 'Payment infrastructure for online stores: cards, UPI, netbanking and wallets at checkout, full and partial refunds, settlement reporting and conversion analytics by payment method.',
            'h1'    => 'Checkout that converts, refunds that reconcile.',
            'lead'  => 'Accept the payment methods your customers already use, handle returns without spreadsheet work, and see exactly which orders have settled.',
            'answer' => 'Paynancial gives online stores one integration for cards, UPI, netbanking and wallets at checkout, full and partial refunds from the dashboard or API, and settlement reporting that ties every order to the money that arrived for it.',
            'challenges' => [
                ['Abandoned checkouts', 'A customer who cannot pay the way they prefer, or who hits a slow or confusing payment step, often leaves without completing the order.'],
                ['Returns and partial refunds', 'Returns rarely map to whole orders. Refunding one item of three, or a shipping charge, has to be traceable back to the original payment.'],
                ['Matching orders to settlements', 'Settlements arrive in batches, net of refunds. Working out which orders a deposit covers is slow when it is done by hand.'],
                ['Failed payments at peak times', 'During a sale, a failed payment is a lost order unless the store can see why it failed and let the customer try again.'],
            ],
            'journey' => [
                ['Customer checks out', 'The shopper picks a payment method on your hosted or custom checkout.'],
                ['Payment is authorised', 'Paynancial routes the transaction and returns a real-time status.'],
                ['Order is confirmed', 'A webhook tells your store the payment succeeded, so fulfilment starts straight away.'],
                ['Returns are refunded', 'Issue a full or partial refund against the original payment.'],
                ['Books are reconciled', 'Settlement reporting shows which orders and refunds each settlement covers.'],
            ],
            'stack' => [
                ['Accept payments at checkout', 'payment-gateway', 'One integration for cards, UPI, netbanking and wallets, hosted or custom.'],
                ['Refund returns and cancellations', 'payment-gateway', 'Full or partial refunds from the dashboard or API.'],
                ['Collect for orders placed by phone or chat', 'payment-links', 'Send a payment link instead of taking card details over the phone.'],
                ['Understand conversion and settlements', 'payment-analytics', 'Break down transactions by method and status; match payments to settlements.'],
            ],
            'scenarios' => [
                ['A direct-to-consumer brand', 'Uses the Payment Gateway on its own storefront and Payment Analytics to see conversion by payment method.'],
                ['A store that sells on social media', 'Sends Payment Links in chat for orders taken in direct messages, with no checkout page required.'],
                ['A seller with frequent returns', 'Issues partial refunds per item and reconciles refunds against settlements in one view.'],
            ],
            'considerations' => [
                ['Offer the methods your customers use', 'Cards, UPI, netbanking and wallets cover most online shoppers in India. Show them clearly at checkout.'],
                ['Confirm orders by webhook', 'Rely on the payment webhook, not the customer returning to your site, to mark an order as paid.'],
                ['Make retries idempotent', 'Send an idempotency key with every payment and refund so a retried request never charges or refunds twice.'],
            ],
            'faqs' => [
                ['Which payment methods can an online store accept with Paynancial?', 'Cards, UPI, netbanking and wallets, through a single integration.'],
                ['Can I use my own checkout design?', 'Yes. Use the hosted checkout page, or build your own interface on top of the API.'],
                ['Can I refund part of an order?', 'Yes. Refunds can be full or partial and are issued from the dashboard or the API.'],
                ['How do I know which orders a settlement covers?', 'Every transaction is tied to a settlement record, and Payment Analytics shows payments, refunds and settlements side by side.'],
            ],
            'related' => ['retail', 'travel', 'hospitality'],
        ],
        'travel' => [
            'name'  => 'Travel',
            'title' => 'Travel Payments | Booking Deposits & Partner Payouts | Paynancial',
            'description' => 'Payments for travel agencies and operators: collect booking deposits and balances with payment links, refund cancellations, and pay hotels, airlines and local partners with payouts.',
            'h1'    => 'Deposits in, partners paid, cancellations handled.',
            'lead'  => 'Travel bookings are paid in stages and settled with many partners. Collect deposits and balances, refund cancellations cleanly, and pay suppliers from one platform.',
            'answer' => 'Paynancial helps travel businesses collect booking deposits and balances through the Payment Gateway or Payment Links, refund cancellations in full or in part, and pay hotels, airlines and local partners by bank transfer or UPI with Payouts.',
            'challenges' => [
                ['Payments in stages', 'A booking is often paid as a deposit now and a balance closer to departure, and each part has to be tracked against the same trip.'],
                ['High-value transactions', 'Holiday packages and group bookings are large payments, so customers want a payment step they trust.'],
                ['Cancellations and changes', 'Cancellations and itinerary changes lead to full or partial refunds that must be traced back to the original booking.'],
                ['Many partners to pay', 'Hotels, airlines, transport and local guides are paid separately, often in batches.'],
            ],
            'journey' => [
                ['Traveller books', 'The customer reserves a trip online or with an agent.'],
                ['Deposit is collected', 'Take the deposit at checkout or send a payment link with an expiry date.'],
                ['Balance is collected', 'Send a second link for the balance before departure.'],
                ['Partners are paid', 'Pay hotels, transport and guides with single or bulk payouts.'],
                ['Changes are refunded', 'Refund cancellations in full or in part against the original payment.'],
            ],
            'stack' => [
                ['Take bookings online', 'payment-gateway', 'Cards, UPI, netbanking and wallets at your booking checkout.'],
                ['Collect deposits and balances', 'payment-links', 'Fixed-amount links with an expiry date, shared by email or WhatsApp.'],
                ['Pay hotels, airlines and guides', 'payouts', 'Bulk payouts to bank accounts or UPI IDs, with saved beneficiaries.'],
                ['Track what is paid and settled', 'payment-analytics', 'Settlement visibility and refund tracking for every booking.'],
            ],
            'scenarios' => [
                ['A holiday agency', 'Sends a payment link for the deposit when a trip is confirmed and a second link for the balance before departure.'],
                ['A tour operator', 'Pays local guides and transport partners in one bulk payout after each tour.'],
                ['A corporate travel desk', 'Refunds part of a booking when a traveller drops out and tracks the refund to completion.'],
            ],
            'considerations' => [
                ['Set expiry dates on deposit links', 'An expiry date stops a link being paid after the offer or the seats are gone.'],
                ['Keep one reference per booking', 'Use the booking ID as the payment receipt so deposits, balances and refunds line up.'],
                ['Taking payment in other currencies?', 'If you sell to travellers paying in other currencies, discuss international payments with our team before you launch.'],
            ],
            'faqs' => [
                ['Can I collect a deposit now and the balance later?', 'Yes. Send one payment link for the deposit and another for the balance, each with its own amount and expiry date.'],
                ['How do I pay hotels and local partners?', 'With Payouts: send funds to a bank account or UPI ID, one at a time or in bulk, and track each payout to completion.'],
                ['Can I refund a cancelled booking?', 'Yes. Refunds can be full or partial and are tracked from request through to completion.'],
                ['Do travellers need to visit my website to pay?', 'No. A payment link works on its own; the traveller only needs the link.'],
            ],
            'related' => ['hospitality', 'e-commerce', 'enterprise'],
        ],
        'healthcare' => [
            'name'  => 'Healthcare',
            'title' => 'Healthcare Payments | Clinic & Hospital Billing | Paynancial',
            'description' => 'Payment infrastructure for clinics, hospitals and diagnostic centres: collect consultation and treatment payments, schedule instalments for treatment plans, and reconcile every payment.',
            'h1'    => 'Simpler billing for clinics, hospitals and labs.',
            'lead'  => 'Collect consultation fees, advance payments and treatment instalments without chasing patients, and reconcile every payment against the right visit.',
            'answer' => 'Paynancial helps healthcare providers collect consultation and treatment payments at the desk or online, send payment links for pre-payments and outstanding bills, and schedule instalments for longer treatment plans, with reconciliation built in.',
            'challenges' => [
                ['Payments at different stages of care', 'Patients may pay an advance, a consultation fee, test charges and a final bill, sometimes on different days.'],
                ['Long treatment plans', 'Physiotherapy, dental and other multi-session treatments are often paid in instalments that have to be tracked per patient.'],
                ['Busy front desks', 'Reception teams need a quick way to take payment and issue a record, without re-keying amounts into several systems.'],
                ['Reconciliation across departments', 'Payments from OPD, pharmacy, diagnostics and in-patient billing all need to be matched to the right account.'],
            ],
            'journey' => [
                ['Appointment is booked', 'Take an advance or consultation fee online, or send a payment link.'],
                ['Care is delivered', 'Collect charges at the desk through your checkout.'],
                ['Plan is scheduled', 'Set up instalments for a multi-session treatment plan.'],
                ['Patient is notified', 'Patients are kept informed as a payment is due or completed.'],
                ['Accounts are reconciled', 'Every payment is matched to the patient and billing cycle it belongs to.'],
            ],
            'stack' => [
                ['Take payment at the desk or online', 'payment-gateway', 'Cards, UPI, netbanking and wallets through one integration.'],
                ['Collect advances and outstanding bills', 'payment-links', 'Send a link by SMS, email or WhatsApp; the patient pays from their phone.'],
                ['Schedule treatment instalments', 'payment-collection', 'Recurring collection with retries, notifications and reconciliation.'],
                ['Refund cancelled appointments', 'payment-gateway', 'Full or partial refunds from the dashboard.'],
            ],
            'scenarios' => [
                ['A multi-specialty clinic', 'Sends a payment link to confirm a booked appointment and takes remaining charges at the desk.'],
                ['A physiotherapy or dental practice', 'Bills a treatment plan in scheduled instalments and follows up automatically on failed attempts.'],
                ['A diagnostic centre', 'Collects for home sample collection by payment link before the technician visits.'],
            ],
            'considerations' => [
                ['Keep medical details out of payment data', 'Paynancial handles payments, not clinical records. Use neutral references, such as an invoice number, rather than diagnoses or treatment names.'],
                ['Agree instalments in writing', 'Make sure patients have agreed to a treatment-plan schedule before you set up recurring collection.'],
                ['Your obligations stay yours', 'Using Paynancial does not change your own regulatory or record-keeping obligations as a healthcare provider.'],
            ],
            'faqs' => [
                ['Can patients pay before their appointment?', 'Yes. Send a payment link by SMS, email or WhatsApp and the patient can pay from their phone.'],
                ['Can we bill a treatment plan in instalments?', 'Yes. Smart Collections runs scheduled collections, retries failed attempts and reconciles each payment to its cycle.'],
                ['Can we refund a cancelled appointment?', 'Yes. Refunds can be full or partial and are issued from the dashboard or API.'],
                ['Does Paynancial store patient medical records?', 'No. Paynancial is a payments platform. Keep clinical information out of payment descriptions and references.'],
            ],
            'related' => ['education', 'professional-services', 'enterprise'],
        ],
        'education' => [
            'name'  => 'Education',
            'title' => 'Education Payments | School & College Fee Collection | Paynancial',
            'description' => 'Fee collection for schools, colleges, coaching and training institutes: scheduled term and monthly fees, payment links for admissions, reminders, and reconciliation per student.',
            'h1'    => 'Fee collection that runs on schedule.',
            'lead'  => 'Collect admission, term and monthly fees without manual follow-up, keep parents informed, and see at a glance who has paid.',
            'answer' => 'Paynancial helps schools, colleges and training institutes collect fees on a schedule with Smart Collections, send payment links for admissions and one-off charges, and reconcile every payment to the student and term it belongs to.',
            'challenges' => [
                ['Recurring fees for many students', 'Monthly or term fees are due from hundreds or thousands of students at the same time, and collecting them one by one does not scale.'],
                ['Chasing late payments', 'Staff spend time reminding parents and students, and tracking who has paid.'],
                ['One-off charges', 'Admission fees, exam fees, trips and events sit outside the regular fee schedule.'],
                ['Knowing who has paid', 'Accounts teams need to match each payment to a student, class and term.'],
            ],
            'journey' => [
                ['Student enrols', 'Send a payment link for the admission fee.'],
                ['Fees are scheduled', 'Set up monthly or term collection for each student.'],
                ['Parents are notified', 'Keep parents informed as fees fall due or are paid.'],
                ['Failed payments are retried', 'Failed attempts are retried on the schedule you define.'],
                ['Accounts are reconciled', 'Each payment is matched to the student and cycle it belongs to.'],
            ],
            'stack' => [
                ['Collect monthly or term fees', 'payment-collection', 'Recurring and bulk collection with retries and reminders.'],
                ['Collect admission and event fees', 'payment-links', 'Links with a fixed amount and expiry, shared by WhatsApp or email.'],
                ['Accept fees online', 'payment-gateway', 'Cards, UPI, netbanking and wallets on your fee portal.'],
                ['Report on collections', 'payment-analytics', 'See what was collected, what failed and what is pending.'],
            ],
            'scenarios' => [
                ['A training institute', 'Automates monthly fee billing across hundreds of students with Smart Collections.'],
                ['A school', 'Sends payment links for annual trips and events, each with its own expiry date.'],
                ['A coaching centre', 'Accepts course fees on its website and tracks pending instalments in one report.'],
            ],
            'considerations' => [
                ['Use the student ID as the reference', 'It makes reconciliation per student and per term straightforward.'],
                ['Set expiry dates on event links', 'A link for a trip or exam fee should stop accepting payment after the deadline.'],
                ['Tell parents what to expect', 'Share the fee schedule before collection starts, so a scheduled payment is never a surprise.'],
            ],
            'faqs' => [
                ['Can we collect fees from many students at once?', 'Yes. Smart Collections supports bulk collection from a batch of customers, with each result tracked individually.'],
                ['Can parents pay by UPI?', 'Yes. Cards, UPI, netbanking and wallets are supported.'],
                ['What happens if a fee payment fails?', 'The attempt is recorded with a reason and can be retried on the schedule you define.'],
                ['Can we see which students have not paid?', 'Yes. Collection reports show what was collected, what failed and what is still pending.'],
            ],
            'related' => ['healthcare', 'professional-services', 'enterprise'],
        ],
        'retail' => [
            'name'  => 'Retail',
            'title' => 'Retail Payments | Online & In-Store Reconciliation | Paynancial',
            'description' => 'Payments for retail businesses selling online and in store: accept cards, UPI, netbanking and wallets, send payment links for orders by phone, and reconcile sales across channels and stores.',
            'h1'    => 'One view of every sale, online and in store.',
            'lead'  => 'Sell through your website, your stores and your phone line, and reconcile every rupee in one place instead of one spreadsheet per channel.',
            'answer' => 'Paynancial helps retailers accept cards, UPI, netbanking and wallets online, take payment for phone and chat orders with payment links, and reconcile sales, refunds and settlements across channels in Payment Analytics.',
            'challenges' => [
                ['Many channels, many reports', 'Sales come from a website, stores and phone orders, and each channel produces its own records.'],
                ['Orders taken outside the checkout', 'Customers order by phone or WhatsApp and need a safe way to pay.'],
                ['Refunds and exchanges', 'Returns and exchanges must be traced back to the original sale, whichever channel it came from.'],
                ['Supplier payments', 'Paying suppliers and staff is a separate, often manual, process.'],
            ],
            'journey' => [
                ['Customer buys', 'On your website, in store, or by phone or chat.'],
                ['Payment is taken', 'At checkout, or with a payment link for remote orders.'],
                ['Returns are handled', 'Refund in full or in part against the original payment.'],
                ['Suppliers are paid', 'Send payouts to suppliers by bank transfer or UPI.'],
                ['Sales are reconciled', 'See payments, refunds and settlements for every channel together.'],
            ],
            'stack' => [
                ['Sell online', 'payment-gateway', 'Cards, UPI, netbanking and wallets at your checkout.'],
                ['Take phone and chat orders', 'payment-links', 'Send a link instead of taking card details over the phone.'],
                ['Pay suppliers', 'payouts', 'Single or bulk payouts with saved beneficiaries.'],
                ['Reconcile every channel', 'payment-analytics', 'Dashboards, reconciliation views and exportable reports.'],
            ],
            'scenarios' => [
                ['A retail chain', 'Uses the Payment Gateway online and Payment Analytics to reconcile sales across every store.'],
                ['A boutique', 'Sends payment links on WhatsApp for orders placed through social media.'],
                ['A distributor', 'Pays suppliers in one bulk payout at the end of each week.'],
            ],
            'considerations' => [
                ['Tag each payment with its channel', 'Use a reference that identifies the store or channel so reports can be split by it.'],
                ['Refund to the original payment', 'Issuing refunds against the original payment keeps returns traceable.'],
                ['Schedule your reports', 'Have daily or weekly reports delivered automatically instead of exporting them by hand.'],
            ],
            'faqs' => [
                ['Can I take payment for an order placed on WhatsApp?', 'Yes. Send a payment link on WhatsApp, SMS or email; the customer pays from their phone.'],
                ['Can I see sales from all my channels together?', 'Payment Analytics lets you filter and break down transactions by method, status and time period, and export reports.'],
                ['Can I pay my suppliers through Paynancial?', 'Yes. Payouts sends funds to bank accounts or UPI IDs, individually or in bulk.'],
                ['Can I refund part of a purchase?', 'Yes. Refunds can be full or partial.'],
            ],
            'related' => ['e-commerce', 'hospitality', 'enterprise'],
        ],
        'hospitality' => [
            'name'  => 'Hospitality',
            'title' => 'Hospitality Payments | Hotel & Restaurant Bookings | Paynancial',
            'description' => 'Payments for hotels, resorts, restaurants and event venues: collect advance booking deposits with payment links, take on-site payments, refund cancellations and pay vendors.',
            'h1'    => 'From advance booking to checkout, one payment flow.',
            'lead'  => 'Secure bookings with advance deposits, take payment on site, handle cancellations cleanly and pay your vendors — without juggling separate tools.',
            'answer' => 'Paynancial helps hotels, restaurants and venues collect advance deposits with payment links, take on-site payments through the Payment Gateway, refund cancellations in full or in part, and pay vendors with Payouts.',
            'challenges' => [
                ['No-shows and late cancellations', 'Bookings without an advance payment are easy to abandon, leaving rooms and tables empty.'],
                ['Deposits and final bills', 'An advance, on-site charges and the final bill all belong to the same stay or event.'],
                ['Group and event bookings', 'Weddings, conferences and group stays involve large amounts paid in parts.'],
                ['Paying vendors', 'Caterers, decorators, laundries and suppliers are paid regularly, often in batches.'],
            ],
            'journey' => [
                ['Guest books', 'Directly, by phone or through a travel agent.'],
                ['Deposit secures it', 'Send a payment link for the advance, with an expiry date.'],
                ['Guest pays on site', 'Collect the balance and extras through your checkout.'],
                ['Cancellations are refunded', 'Refund in full or in part according to your policy.'],
                ['Vendors are paid', 'Send single or bulk payouts to vendors.'],
            ],
            'stack' => [
                ['Collect advance deposits', 'payment-links', 'Fixed-amount links with an expiry date, shared by email or WhatsApp.'],
                ['Take payment on site or online', 'payment-gateway', 'Cards, UPI, netbanking and wallets.'],
                ['Pay vendors and suppliers', 'payouts', 'Bank and UPI payouts, single or in bulk.'],
                ['See bookings, refunds and settlements', 'payment-analytics', 'Settlement visibility and refund tracking.'],
            ],
            'scenarios' => [
                ['A boutique hotel', 'Sends a payment link for the advance when a booking is confirmed and takes the balance at checkout.'],
                ['An event venue', 'Collects a wedding booking in stages with separate links for each instalment.'],
                ['A restaurant group', 'Pays its suppliers in a weekly bulk payout.'],
            ],
            'considerations' => [
                ['Put your cancellation policy in writing', 'State what is refundable before you take a deposit, then refund against the original payment.'],
                ['Use the booking ID as the reference', 'It keeps deposits, on-site charges and refunds together.'],
                ['Expire unpaid deposit links', 'An expiry date releases the booking if the deposit is not paid in time.'],
            ],
            'faqs' => [
                ['Can I ask guests for an advance deposit?', 'Yes. Send a payment link with a fixed amount and an expiry date.'],
                ['Can I refund a cancelled booking?', 'Yes. Refunds can be full or partial and are tracked through to completion.'],
                ['Can I pay my vendors through Paynancial?', 'Yes. Payouts sends funds to bank accounts or UPI IDs, individually or in bulk.'],
                ['Do guests need an account to pay?', 'No. A payment link works on its own; guests pay on a secure payment page without creating an account.'],
            ],
            'related' => ['travel', 'retail', 'e-commerce'],
        ],
        'professional-services' => [
            'name'  => 'Professional Services',
            'title' => 'Payments for Professional Services | Invoices & Retainers | Paynancial',
            'description' => 'Payments for consultants, agencies, law and accounting firms: send invoice payment links, collect retainers on a schedule, and pay freelancers and associates with payouts.',
            'h1'    => 'Get paid for your work, without chasing invoices.',
            'lead'  => 'Send a payment link with every invoice, collect retainers automatically, and pay the freelancers and associates who work with you.',
            'answer' => 'Paynancial helps consultants, agencies and professional firms get paid by adding a payment link to every invoice, collecting monthly retainers with Smart Collections, and paying freelancers and associates with Payouts.',
            'challenges' => [
                ['Slow invoice payments', 'Invoices sent as documents are easy to set aside; paying often requires the client to log in to their bank and re-type details.'],
                ['Retainers and milestones', 'Work is billed as monthly retainers, milestones or partial advances, each needing its own tracking.'],
                ['Paying freelancers', 'Many firms rely on freelancers and associates who need to be paid promptly and accurately.'],
                ['Small teams, little admin time', 'Partners and consultants would rather spend their time on client work than on reconciliation.'],
            ],
            'journey' => [
                ['Work is agreed', 'Send a payment link for the advance or first milestone.'],
                ['Invoice is sent', 'Add a payment link to the invoice so the client can pay in one step.'],
                ['Retainer runs', 'Collect a monthly retainer on a schedule.'],
                ['Associates are paid', 'Pay freelancers and associates with payouts.'],
                ['Books are closed', 'Export reports for your accountant.'],
            ],
            'stack' => [
                ['Get invoices paid', 'payment-links', 'A link on every invoice, with a fixed or open amount and status tracking.'],
                ['Collect retainers', 'payment-collection', 'Scheduled collection with retries and notifications.'],
                ['Pay freelancers and associates', 'payouts', 'Bank and UPI payouts, with saved beneficiaries.'],
                ['Keep the books tidy', 'payment-analytics', 'Exportable and scheduled reports.'],
            ],
            'scenarios' => [
                ['A consulting firm', 'Uses Payment Links to invoice clients and Payouts to pay freelance associates.'],
                ['A marketing agency', 'Collects monthly retainers on a schedule and follows up automatically on failed attempts.'],
                ['An accounting practice', 'Adds a payment link to each fee invoice and exports a monthly report.'],
            ],
            'considerations' => [
                ['Match the link to the invoice', 'Use the invoice number as the link title so the client and your books see the same reference.'],
                ['Agree retainer schedules first', 'Confirm the amount and schedule with the client before setting up recurring collection.'],
                ['Keep beneficiary details up to date', 'Saved beneficiaries make repeat payouts to associates quick and accurate.'],
            ],
            'faqs' => [
                ['Can I add a payment link to an invoice?', 'Yes. Create a link with the invoice amount and share it by email, WhatsApp, SMS or on the invoice itself.'],
                ['Can clients choose how much to pay?', 'Yes. A link can have a fixed amount, or an open amount the client enters.'],
                ['Can I collect a monthly retainer automatically?', 'Yes. Smart Collections runs recurring collections on a schedule and retries failed attempts.'],
                ['Can I pay freelancers through Paynancial?', 'Yes. Payouts sends funds to bank accounts or UPI IDs, individually or in bulk.'],
            ],
            'related' => ['enterprise', 'education', 'healthcare'],
        ],
        'enterprise' => [
            'name'  => 'Enterprise',
            'title' => 'Enterprise Payments | Payment Infrastructure at Scale | Paynancial',
            'description' => 'Payment infrastructure for large businesses: payments, collections, payouts and analytics through one API, integrated with your finance systems, with idempotency, webhooks and governance for automation.',
            'h1'    => 'Payment infrastructure that fits your finance stack.',
            'lead'  => 'Run payments, collections, payouts and reconciliation behind your own systems through one API — with the controls large finance teams and automated workflows need.',
            'answer' => 'Paynancial gives enterprises the full platform — Payment Gateway, Smart Collections, Payouts and Payment Analytics — behind their own finance systems through one API, with idempotent requests, webhooks and business-set limits for automated and agent-driven workflows.',
            'challenges' => [
                ['Many systems, one ledger', 'ERP, billing, procurement and treasury systems all need to agree on what was paid, refunded and settled.'],
                ['Volume and batch operations', 'Collections and payouts run in large batches that must not duplicate or go missing.'],
                ['Automation without losing control', 'As more work is automated, including by AI agents, finance teams need limits, approvals and an audit trail.'],
                ['Integration effort', 'Every new provider means another integration to build, test and maintain.'],
            ],
            'journey' => [
                ['Integrate once', 'Connect your systems to one API for payments, collections, payouts and reporting.'],
                ['Test in the sandbox', 'Test failures, retries and duplicates before any live money moves.'],
                ['Automate with limits', 'Let jobs and agents act within permissions and thresholds you set.'],
                ['React to events', 'Webhooks update your systems as payments, payouts and settlements change.'],
                ['Reconcile and report', 'Scheduled reports and reconciliation views for your finance team.'],
            ],
            'stack' => [
                ['Accept payments at scale', 'payment-gateway', 'One integration for cards, UPI, netbanking and wallets.'],
                ['Run bulk and recurring collections', 'payment-collection', 'Batch and scheduled collection with automatic reconciliation.'],
                ['Pay vendors, staff and partners', 'payouts', 'API-first bulk payouts with status tracking and failure reasons.'],
                ['Give finance one view', 'payment-analytics', 'Reconciliation views, exports and scheduled reports.'],
            ],
            'scenarios' => [
                ['A large enterprise', 'Runs the full platform — Gateway, Collection, Payouts and Analytics — behind its own finance systems via the API.'],
                ['A finance operations team', 'Triggers weekly vendor payouts from its ERP and receives status updates by webhook.'],
                ['An automation programme', 'Lets reconciliation agents act within limits the business sets, with every action logged to the key that made it.'],
            ],
            'considerations' => [
                ['Design for retries', 'Send an idempotency key with every write so a retried batch never creates duplicates.'],
                ['Separate keys by system', 'Give each system or agent its own key, so every action is traceable to its source.'],
                ['Set limits for automation', 'Define permissions, caps and approval thresholds before automated jobs or agents move money.'],
            ],
            'faqs' => [
                ['Can Paynancial integrate with our existing finance systems?', 'Yes. Everything runs through one REST API with webhooks, so your ERP, billing and treasury systems can call it and react to events.'],
                ['How do we prevent duplicate payouts in large batches?', 'Send an idempotency key with each request. A retried request returns the original result instead of creating a second payout.'],
                ['Can AI agents or automated jobs use the API safely?', 'Yes, within the permissions, limits and approval thresholds your business sets. See the AI Governance page.'],
                ['How do we start?', 'Talk to our team about your requirements, and test your integration in the sandbox before going live.'],
            ],
            'related' => ['e-commerce', 'retail', 'professional-services'],
        ],
    ];
}

function sol_industry(string $slug): ?array
{
    $all = sol_industries();
    return isset($all[$slug]) ? ['slug' => $slug] + $all[$slug] : null;
}
