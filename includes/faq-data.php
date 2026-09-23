<?php
/**
 * FAQ content — single source for the FAQs shown on each page and for the
 * FAQ hub (/resources/faqs), so a question is written once and every page
 * that shows or indexes it stays in sync.
 *
 * Keys: page keys (developers, sandbox, …), product:{slug}, and "general"
 * (account and support questions answered in full on the FAQ hub).
 * Industry and Business Services FAQs live with their page data
 * (solutions-data.php, business-services.php).
 */

declare(strict_types=1);

require_once __DIR__ . '/developer-docs.php'; // DEV_API_BASE

function faq_sets(): array
{
    return [
        'developers' => [
            ['How do I start integrating with Paynancial?', 'Request sandbox access, build and test your integration with a sandbox API key, then switch to a live key once it behaves the way you expect. The Integration Guide walks through each step.'],
            ['Which languages have an official SDK?', 'PHP, JavaScript and Python. Any other language can call the REST API directly over HTTPS.'],
            ['Can an AI agent call the Paynancial API?', 'Yes. The API an agent calls is the same one a developer calls, and the features that make it safe for agents — idempotency keys, structured error codes and webhooks — are the same ones that make any integration reliable.'],
            ['Where do I get help with an integration?', 'Contact developer support through the contact page and choose Support. Include the request you sent and the response you received, without your API key.'],
        ],
        'api-reference' => [
            ['What is the base URL of the Paynancial API?', 'All requests go to ' . DEV_API_BASE . ' over HTTPS. Whether a request runs in the sandbox or moves real money depends on whether you authenticate with a sandbox key or a live key.'],
            ['How are amounts expressed?', 'In the smallest unit of the currency. For Indian rupees that is paise, so 50000 means ₹500.00.'],
            ['How do I avoid creating a duplicate payment or payout when I retry?', 'Send an idempotency key with every write request. If the same key is sent again, the API returns the original result instead of creating a second payment or payout.'],
            ['Which endpoints are documented here?', 'Payments, refunds, payouts, payment links, collections and transaction reports, with the parameters used in Paynancial\'s published examples. For anything not covered on this page, contact developer support.'],
        ],
        'authentication' => [
            ['How do I authenticate with the Paynancial API?', 'Send your API key using HTTP basic authentication: the key is the username and the password is left empty. In cURL that is -u YOUR_API_KEY: (note the trailing colon). The SDKs take the key when you create the client.'],
            ['What is the difference between a sandbox key and a live key?', 'A sandbox key runs requests in the sandbox, where no real money moves. A live key processes real payments and payouts. Build and test with a sandbox key; switch to a live key only when your integration is ready.'],
            ['Where do I manage my API keys?', 'Sandbox and live API keys are managed from your Paynancial dashboard.'],
            ['Can I use my API key in a browser or mobile app?', 'Not a live key. Anyone who can see the key can make requests as your business, so live keys belong on your server only. Your browser or app should call your server, and your server calls Paynancial.'],
            ['What should I do if a key is exposed?', 'Treat it as compromised: replace it from your dashboard, update your server with the new key and stop using the old one. If you are unsure what to do, contact developer support.'],
        ],
        'webhooks' => [
            ['What is a Paynancial webhook?', 'An HTTPS request Paynancial sends to a URL on your server when something happens in your account — a payment, payout, refund or settlement changes state — so your system learns about it immediately instead of asking repeatedly.'],
            ['Which events does Paynancial send?', 'Events for payments, payouts, refunds and settlements. For the exact event names and payload fields for your account, contact developer support.'],
            ['Why use webhooks instead of polling?', 'Polling asks "has anything changed?" on a timer, which is slow when something has changed and wasteful when nothing has. A webhook arrives when the change happens, so orders are fulfilled and payouts marked paid in real time.'],
            ['Can I receive the same event more than once?', 'Design for it. Any system that retries delivery can deliver an event twice, so make your handler safe to run more than once for the same event.'],
            ['How do I test webhooks?', 'Test your handler in the Sandbox before going live, and confirm with developer support how events are delivered for sandbox activity.'],
        ],
        'payment-apis' => [
            ['What are the Paynancial Payment APIs?', 'The money-in resources of the Paynancial REST API: Payments, Payment Links, Collections, Refunds and transaction reports. Every call goes to https://api.paynancial.com/v1 and is authenticated with your API key.'],
            ['Which API should I use to charge a customer?', 'Payments for a checkout in your own website or app, Payment Links to get paid without a checkout, and Collections for recurring or scheduled charges. Refunds give money back, in full or in part.'],
            ['How do I know a payment succeeded?', 'Each transaction returns a real-time status, and a payment webhook reaches your endpoint as it changes. Fulfil orders on the webhook, not on the customer\'s redirect.'],
            ['What currency unit does the API use?', 'The smallest currency unit — paise for INR — so 50000 is ₹500.00.'],
        ],
        'payout-apis' => [
            ['What is the Paynancial Payout API?', 'The resource that sends money to a beneficiary\'s bank account or UPI ID: POST /payouts with the beneficiary, the amount in paise and the mode, plus an idempotency key.'],
            ['Why do payouts need an idempotency key?', 'A payout that times out might have gone through. Retrying with the same idempotency key returns the original payout instead of paying twice.'],
            ['What happens if a payout fails?', 'You get a structured error code such as insufficient_funds, invalid_method or rate_limited, and a failed payout comes with a clear reason. Fix the cause, then retry.'],
            ['Can I send payouts in bulk through the API?', 'Yes — Payouts supports submitting a batch of transfers in a single request, with each payout tracked individually. Ask developer support for the batch request format.'],
        ],
        'sdks' => [
            ['Which languages does Paynancial have SDKs for?', 'PHP, JavaScript and Python.'],
            ['Do I have to use an SDK?', 'No. The SDKs are a convenience over the REST API. Any language that can make an HTTPS request with basic authentication can call ' . DEV_API_BASE . ' directly, as the cURL examples show.'],
            ['How do I install an SDK?', 'Contact developer support for installation details for your language and environment.'],
            ['Do the SDKs support idempotency keys?', 'Yes. In PHP, pass an idempotency_key option as the second argument to a create call; the SDK sends it as the Idempotency-Key header.'],
            ['Can I use the JavaScript SDK in a browser?', 'Not with a live key. A live key must stay on your server, so run the JavaScript SDK in a server environment and have your web page call your server.'],
        ],
        'integration-guide' => [
            ['How long does a Paynancial integration take?', 'It depends on what you are building and how much of it you test. A first API call is a few lines of code; a production integration also needs webhooks, error handling and testing, which is where most of the time goes.'],
            ['What do I need before I start?', 'A sandbox API key, a server that can make HTTPS requests and keep a key secret, and an HTTPS endpoint if you plan to receive webhooks.'],
            ['Which product should I integrate first?', 'The one that matches how you get paid: the Payment Gateway for checkout, Payment Links to get paid without a checkout, Payment Collection for recurring or scheduled payments, and Payouts to send money out.'],
            ['When am I ready to go live?', 'When every item on the go-live checklist on this page is done: your integration handles failures and retries, your webhook handler copes with duplicates, and your live key is stored only on your server.'],
        ],
        'sandbox' => [
            ['What is the Paynancial Sandbox?', 'A test environment for your Paynancial integration. Requests made with a sandbox API key run in the sandbox, so you can build and test a complete integration with no real funds involved.'],
            ['How do I get sandbox API keys?', 'Request sandbox access through the contact page. Once your access is set up, sandbox and live API keys are managed from your Paynancial dashboard.'],
            ['How do I test payments?', 'Make the same API calls you will make in production — create a payment, a payment link, a payout or a collection — authenticated with your sandbox key. The API Reference has an example for each.'],
            ['What is the difference between sandbox and live mode?', 'A sandbox key runs requests in the sandbox, where no real money moves. A live key processes real payments and payouts. Confirm with developer support whether anything else differs between environments for your account.'],
            ['Can I test failures, retries and rate limits?', 'Yes — the sandbox is the recommended place to test retry behaviour, rate limits and failure handling before any code gets a live key. Ask developer support which failure scenarios can be simulated for your account.'],
            ['Is the sandbox suitable for testing AI agents?', 'Yes. Any agent-driven or autonomous workflow should be tested in the sandbox — including how it retries and how it handles errors — before it is given a live key.'],
        ],
        'agentic-ai' => [
            ['Is Paynancial\'s AI making financial decisions on its own?', 'No. Every AI capability Paynancial offers — fraud scoring, reconciliation matching, cash-flow forecasting — surfaces a recommendation or takes a narrowly scoped action inside limits a business sets. A person or a policy a person configured is always the authority; the AI is the layer that reduces how much of the repetitive work reaches a human before a decision gets made.'],
            ['What\'s the difference between "AI-powered" and "agentic"?', 'AI-powered usually means a model analyzes something and shows you the result — a fraud score, a forecast. Agentic goes one step further: the system takes the next action too, like retrying a failed charge or routing a payout for approval, without a person clicking through each step.'],
            ['Can an AI agent move money without anyone approving it?', 'Only within limits a business explicitly configures — a payout ceiling, an approval workflow, a list of pre-authorized beneficiaries. Nothing here removes a business\'s ability to require human sign-off; it changes how much of the routine work happens before a human is asked to weigh in.'],
            ['How is this different from the automation we already have?', 'Traditional automation follows a fixed script: if X, then always Y. An agent evaluates context each time — is this failed payment worth retrying, does this transaction pattern look like the last 200 or like none of them — and its behavior can be reasoned about and adjusted, not just re-coded.'],
            ['What happens if an agent makes a mistake?', 'The same way any API-driven action is handled today: idempotency keys prevent a duplicate charge or payout, every action is logged against the request that triggered it, and webhooks notify a business in real time so an error surfaces immediately rather than at month-end reconciliation.'],
            ['Do we need to change our integration to support agentic workflows?', 'No — the same Payment, Payout and Reconciliation APIs your team already integrates with are what an agent calls too. See the Developers page for the specific patterns (idempotency keys, structured errors, webhooks) that make an API safe for either kind of caller.'],
            ['Is this only relevant for large enterprises with engineering teams?', 'No — the earliest and simplest version of this is a solo founder\'s AI bookkeeping assistant flagging a mismatched transaction. The infrastructure scales up to enterprise treasury agents, but the starting point is available to any business already using Paynancial\'s dashboard or API.'],
            ['Where can I read about the security model behind this?', 'The Trust Center covers access controls, authentication, audit trails and the governance model specifically for agent-initiated actions — see the Governance section on this page for a summary, or visit the Trust Center for the full picture.'],
        ],
        'financial-agents' => [
            ['What is an AI financial agent?', 'Software that monitors financial activity, understands the context of what it sees, and — within limits a business sets — takes the next step: flagging an anomaly, matching a settlement, routing an exception or retrying a failed payment. It differs from a report or a dashboard because it acts, and from fixed automation because it evaluates each situation rather than following one script.'],
            ['Do AI financial agents make decisions on their own?', 'Only inside the scope a business grants them. Every Paynancial AI capability surfaces a recommendation or takes a narrowly scoped action; anything above a set threshold, or matching a risk pattern, routes to a person before it completes.'],
            ['Which financial tasks are agents best suited to?', 'High-volume, repetitive work where most cases are routine and a few need judgement: payment monitoring, reconciliation, exception routing, fraud screening, cash-flow forecasting and answering routine payment questions.'],
            ['What stays with people?', 'Setting the limits, approving anything above them, handling genuine exceptions, and deciding when to widen or narrow what an agent may do. The agent reduces how much routine work reaches a person; it does not replace the person\'s authority.'],
            ['How do we start using AI agents in finance?', 'Start with one workflow where the agent only recommends — for example, surfacing reconciliation exceptions. Test it in the sandbox, set permissions and limits, and widen its scope as it earns trust.'],
        ],
        'payment-orchestration' => [
            ['What is AI payment orchestration?', 'The sequencing and safety rails that let software — including AI agents — initiate, retry and track payments and payouts reliably. The payment rail is the same one a person\'s click uses; orchestration is what makes frequent, round-the-clock, automated requests safe.'],
            ['Does an AI agent use a different payment system from a person?', 'No. An agent-initiated payment or payout travels through exactly the same infrastructure as one a person starts: the same API, idempotency keys, structured error codes and webhooks.'],
            ['How do you stop an agent paying twice?', 'Every write request carries an idempotency key. If an agent retries after a timeout with the same key, the API returns the original result instead of creating a second payment or payout.'],
            ['How does an agent know what went wrong?', 'Errors carry a stable code — such as insufficient_funds, invalid_method or rate_limited — that an agent can branch on programmatically, rather than a message written for a person.'],
            ['Where should agent workflows be tested?', 'In the sandbox, before the agent is given a live key — including how it retries, how it handles each error and how it behaves under rate limits.'],
        ],
        'ai-governance' => [
            ['Does Paynancial\'s AI make financial decisions on its own?', 'No. Every AI capability Paynancial offers surfaces a recommendation or takes a narrowly scoped action inside limits a business sets. A person, or a policy a person configured, is always the authority.'],
            ['Can an AI agent move money without anyone approving it?', 'Only within limits a business explicitly configures — such as a payout ceiling, an approval workflow or a list of pre-authorised beneficiaries. Anything above a threshold, or matching a risk pattern, routes to a person before it completes.'],
            ['Who sets the limits?', 'The business using the platform, not Paynancial. It decides how much of a workflow an agent handles unattended, and can tighten or loosen that at any time.'],
            ['How can I tell what an agent did and why?', 'Every write action is tied to the specific API key or session that made the request, and webhooks give a real-time, timestamped record of every state change — the data an audit trail draws from.'],
            ['Is there a formal AI governance policy document?', 'Not yet. The principles on this page govern how Paynancial\'s AI & Intelligence products are designed to operate. A standalone, formally reviewed AI governance policy document has not been published.'],
        ],
        'general' => [
            ['How do I get started with Paynancial?', 'Reach out through our contact form or create an account, and our team will guide you through onboarding and KYC.'],
            ['Which payment methods are supported?', 'Cards, UPI, netbanking and wallets are supported through the Payment Gateway product.'],
            ['How do refunds work?', 'Refunds can be initiated from your dashboard and are tracked through to settlement.'],
            ['How do I report a security concern?', 'Email hello@paynancial.com with details and our team will respond promptly.'],
            ['How can partners track commission?', 'Commission and settlement tracking are available in the Partner Portal.'],
            ['Can I test Paynancial before going live?', 'Yes. The Paynancial Sandbox lets you build and test your integration with a sandbox API key and no real funds involved. Request sandbox access through the contact page.'],
            ['Where can I find Paynancial\'s developer documentation?', 'In the Developer Hub: the API Reference, authentication, webhooks, SDKs for PHP, JavaScript and Python, and a step-by-step Integration Guide.'],
            ['Does Paynancial help with company incorporation and registrations?', 'Yes. Paynancial Business Services supports company incorporation, business registrations such as GST and MSME / Udyam, trademarks and compliance filings.'],
            ['How do I contact Paynancial sales or support?', 'Use the contact page and choose Sales or Support, or email hello@paynancial.com.'],
        ],
        'product:payment-gateway' => [
            ['Which payment methods are supported?', 'Cards, UPI, netbanking and wallets through a single integration.'],
            ['Can I test before going live?', 'Yes — sandbox API keys let you run a complete integration test with no real funds involved.'],
            ['Can I use my own checkout design?', 'Yes. Use the hosted checkout page, or build your own interface on top of the API.'],
            ['How do I know a payment succeeded?', 'Paynancial returns a real-time status for every transaction, and a payment webhook tells your system as soon as the payment changes state.'],
        ],
        'product:payment-links' => [
            ['Do I need a website to use payment links?', 'No — a payment link works entirely on its own; you only need a way to share the link, like email or chat.'],
            ['Can a link be reused?', 'A link stays active until it is paid, expires, or you disable it, so it can be shared with more than one customer if left open.'],
            ['Can a payment link expire?', 'Yes. Set an expiry date and the link stops accepting payment automatically.'],
            ['How can I share a payment link?', 'By email, WhatsApp or SMS, or embedded in an invoice. The customer pays on a secure payment page that shows your business name and the amount due.'],
        ],
        'product:payment-collection' => [
            ['What happens if a collection fails?', 'A failed attempt is recorded with a reason code and can be retried on your defined schedule.'],
            ['Can I collect from a batch of customers at once?', 'Yes — bulk collection lets you submit a batch and track each customer’s result individually.'],
            ['Are customers notified about collections?', 'Yes. Customers are kept informed as a collection is due or completed.'],
            ['How are collections reconciled?', 'Automatically. Each collection is matched against the customer and cycle it belongs to, and results appear in your collection report.'],
        ],
        'product:payouts' => [
            ['Can I pay more than one person at once?', 'Yes — bulk payouts let you submit a batch of transfers in a single request.'],
            ['What payout modes are supported?', 'Bank transfer and UPI are both supported for sending funds to a beneficiary.'],
            ['Can I save beneficiaries for repeat payouts?', 'Yes. Save and reuse recipient details for repeat payouts.'],
            ['What happens if a payout fails?', 'You get a clear reason for the failure, so the payout can be corrected and retried.'],
        ],
        'product:payment-analytics' => [
            ['Can I export data for my accounting system?', 'Yes — reports can be exported in common formats for use outside the dashboard.'],
            ['Does this show real customer data or sample data?', 'Analytics reflect your own account’s real transactions and settlements — there is no sample or simulated data in your dashboard.'],
            ['Can reports be delivered automatically?', 'Yes. Scheduled reports are delivered to your team on a recurring basis without manual effort.'],
            ['Can I see settlements that are still pending?', 'Yes. Settlement visibility shows what has settled, what is pending and when it is due.'],
        ],
        'product:refunds' => [
            ['Can I issue a partial refund?', 'Yes. Refunds can be full or partial — for example one item from an order, or a shipping charge.'],
            ['Can I refund from the dashboard and the API?', 'Yes. Support teams can refund from the dashboard, and your systems can refund with the Refunds API by sending the payment ID and amount.'],
            ['How do I know when a refund is complete?', 'Refunds are tracked from request through to completion in Payment Analytics, and refund webhooks tell your systems as a refund changes state.'],
            ['How do I avoid refunding twice?', 'Send an idempotency key with every refund request. A retry with the same key returns the original refund instead of creating a second one.'],
        ],
        'product:settlements' => [
            ['What is a settlement?', 'The step where money from your customers\' payments is paid out to your business account. On Paynancial, every transaction is tied to a settlement record.'],
            ['How can I see what has settled?', 'Payment Analytics shows what has settled, what is pending and when it is due.'],
            ['Can my systems be notified when funds settle?', 'Yes. Settlements are one of the four webhook event families, so your system receives an event when funds are settled to your account.'],
            ['Can I export settlement data?', 'Yes. Reports can be exported in formats your finance team already uses, or scheduled for regular delivery.'],
        ],
        'product:reconciliation' => [
            ['What does Paynancial reconcile?', 'Payments against settlements and refunds. Every transaction is tied to a settlement record, and every collection is matched to the customer and cycle it belongs to.'],
            ['Is reconciliation automatic?', 'Collections are reconciled automatically against the customer and cycle they belong to. Reconciliation views in Payment Analytics show payments, refunds and settlements side by side, with discrepancies surfaced clearly.'],
            ['Can I reconcile in my own accounting system?', 'Yes. Export reports, or use payment, refund and settlement webhooks to update your own systems as things change.'],
            ['What is AI Reconciliation?', 'An AI capability that matches settlements against transactions and surfaces only genuine exceptions for a person to review. Ask our team about availability.'],
        ],
        'product:upi-payments' => [
            ['Can I accept UPI payments with Paynancial?', 'Yes. UPI is one of the payment methods on the Payment Gateway, alongside cards, netbanking and wallets, through a single integration.'],
            ['Can I send payouts to a UPI ID?', 'Yes. Payouts can be sent to a beneficiary\'s bank account or UPI ID, one at a time or in bulk.'],
            ['How do I send a UPI payout with the API?', 'Create a payout with the beneficiary, the amount in paise and mode set to upi, and send an idempotency key so a retry never pays twice.'],
            ['Can I see UPI payments separately in reports?', 'Yes. Payment Analytics breaks transactions down by payment method, status and time period.'],
        ],
        'category:accept-and-collect' => [
            ['What are the ways to accept payments with Paynancial?', 'At checkout with the Payment Gateway (cards, UPI, netbanking and wallets), without a website using Payment Links, and on a schedule or in bulk with Smart Collections.'],
            ['Do I need a website to get paid?', 'No. Payment Links let you share a secure link by email, WhatsApp, SMS or on an invoice, and the customer pays on a secure payment page.'],
            ['Can I collect recurring payments?', 'Yes. Smart Collections sets up a schedule for subscription or instalment payments, retries failed attempts and reconciles each collection automatically.'],
            ['Do I need a separate integration for each product?', 'No. The Accept & Collect products share one REST API, the same webhooks and the same settlement records.'],
        ],
        'category:pay-and-move-money' => [
            ['How do I pay vendors and employees with Paynancial?', 'With Payouts: add or select a beneficiary\'s bank account or UPI ID, then send a single payout or submit a batch, from the dashboard or the API.'],
            ['Can I pay many people at once?', 'Yes. Payouts supports bulk payouts, so you can pay an entire batch in one action and track each payout individually.'],
            ['How do I know a payout went through?', 'Each payout is tracked from initiated to completed, with a clear reason if it fails, and payout webhooks notify your systems as its status changes.'],
            ['Does Paynancial support international payments?', 'Ask our team about availability for your business. The Payouts product sends funds to bank accounts and UPI IDs.'],
        ],
        'category:financial-operations' => [
            ['What does Financial Operations cover?', 'Everything after a payment is taken: reconciliation, settlements, refunds, analytics and reporting.'],
            ['How does Paynancial help with reconciliation?', 'Every transaction is tied to a settlement record, collections are reconciled automatically, and Payment Analytics shows payments, refunds and settlements side by side with discrepancies surfaced clearly.'],
            ['Can reports be sent to my finance team automatically?', 'Yes. Reports can be exported in common formats or scheduled for recurring delivery.'],
            ['Does Paynancial handle chargebacks?', 'Ask our team about chargeback handling for your business.'],
        ],
        'category:ai-and-intelligence' => [
            ['What AI capabilities does Paynancial offer?', 'AI Fraud Detection, AI Reconciliation, AI Financial Assistant, AI Cash-Flow Intelligence and AI Revenue Forecasting. Ask our team about availability for your business.'],
            ['Does Paynancial\'s AI make decisions on its own?', 'No. Each capability surfaces a recommendation or takes a narrowly scoped action within limits your business sets; anything above a threshold routes to a person.'],
            ['What data do the AI capabilities use?', 'Your Paynancial payments data — transactions, settlements and refunds — which is what they analyse and reconcile.'],
            ['How is Paynancial\'s AI governed?', 'By five controls: permissions, policy limits, human oversight, authentication and auditability. See the AI Governance page.'],
        ],
        'category:embedded-finance' => [
            ['What is embedded finance?', 'Putting financial services — payments, payouts, billing — directly inside a platform\'s own product, so its users never have to leave it to pay or get paid.'],
            ['How do platforms embed Paynancial?', 'As technology partners: SaaS platforms and marketplaces embed Paynancial\'s Payment, Payout and Billing APIs directly in their own software, using API keys, idempotent requests and real-time webhooks.'],
            ['Can my platform pay its sellers or partners?', 'Yes, through Payouts: send funds to bank accounts and UPI IDs from your own systems, singly or in bulk, with each payout tracked and reported by webhook.'],
            ['Does Paynancial offer wallets, split payments or white-label payments?', 'These are not yet described on this site, so no capabilities are claimed. Ask our team about your platform\'s requirements before you plan around them.'],
            ['Can I test before building?', 'Yes. The Paynancial Sandbox lets you build and test against the API with no real funds involved.'],
        ],
        'ai:hub' => [
            ['What is Paynancial AI?', 'Paynancial AI is the name for Paynancial\'s AI & Intelligence capabilities — AI Fraud Detection, AI Reconciliation, AI Financial Assistant, AI Cash-Flow Intelligence and AI Revenue Forecasting — which work on your Paynancial payments data. Each one surfaces a recommendation or takes a narrowly scoped action within limits your business sets.'],
            ['How does AI Fraud Detection work?', 'It evaluates transaction patterns for fraud risk as payments happen, rather than after settlement. Transactions above the thresholds your business sets, or matching a risk pattern, go to a person before they complete. Paynancial\'s verified security foundations already include real-time fraud monitoring and risk scoring before funds move.'],
            ['What is AI Reconciliation?', 'An AI capability that matches settlements against transactions automatically and surfaces only the genuine exceptions for your team to resolve.'],
            ['What does the AI Financial Assistant do?', 'It answers routine payment questions — such as "why was this transaction declined?" — without a support ticket, and supports operational alerts. It does not give legal, accounting or investment advice.'],
            ['What is AI Cash-Flow Intelligence?', 'A capability that forecasts near-term liquidity from live transaction data instead of a monthly spreadsheet. Treasury and funding decisions stay with your team.'],
            ['How does Paynancial use AI in financial operations?', 'To assist with detection, analysis and narrowly scoped actions on payments data — fraud screening, reconciliation, forecasting and answering routine questions — while people keep the authority over anything above the limits they set.'],
            ['How is Paynancial\'s AI governed?', 'By five controls: permissions, policy limits, human oversight, authentication and auditability. Every action is logged against the request, key and rule that authorised it. A standalone, formally reviewed AI governance policy document has not yet been published.'],
            ['Can developers integrate Paynancial AI?', 'Developers integrate the Paynancial payments API that the AI capabilities work on — payments, payouts, collections, refunds, reports and webhooks. APIs for the AI capabilities themselves are not published; ask our team about availability.'],
            ['Are Paynancial\'s AI capabilities available now?', 'They are available on request. Ask our team whether a specific capability is available for your business.'],
        ],
        'ai:paynancial-ai' => [
            ['What is Paynancial AI?', 'The family of Paynancial AI capabilities that work on your payments data: AI Fraud Detection, AI Reconciliation, AI Financial Assistant, AI Cash-Flow Intelligence and AI Revenue Forecasting.'],
            ['Does Paynancial AI make decisions on its own?', 'No. Each capability surfaces a recommendation or takes a narrowly scoped action within limits your business sets. Anything above a threshold, or matching a risk pattern, routes to a person first.'],
            ['Who sets the limits?', 'Your business — not Paynancial. You decide how much of a workflow runs unattended and can tighten or loosen that at any time.'],
            ['How is Paynancial AI different from Agentic AI?', 'Paynancial AI is the set of intelligence capabilities. Agentic AI describes agent-driven financial workflows and the agent-ready infrastructure they run on — see the Agentic AI pages.'],
        ],
        'ai:fraud-detection' => [
            ['How does Paynancial AI Fraud Detection work?', 'It evaluates transaction patterns for fraud risk as payments happen, rather than after settlement, and routes transactions above your thresholds to a person before they complete.'],
            ['Does Paynancial already monitor for fraud?', 'Yes. The Trust Center lists real-time fraud monitoring as verified: transactions pass through real-time risk scoring before funds move.'],
            ['Who decides whether a flagged payment goes through?', 'A person, for anything above the thresholds your business sets.'],
            ['What accuracy does it have?', 'Paynancial does not publish accuracy or fraud-reduction figures. Ask our team about how it would apply to your business.'],
        ],
        'ai:reconciliation' => [
            ['What is AI Reconciliation?', 'An AI capability that matches settlements against transactions automatically and surfaces only the genuine exceptions for a person to resolve.'],
            ['How is it different from Paynancial\'s reconciliation today?', 'Paynancial already ties every transaction to a settlement record and reconciles collections automatically. AI Reconciliation focuses on matching settlements to transactions and surfacing only the exceptions.'],
            ['Who resolves the exceptions?', 'Your team. Resolving exceptions and closing the books stay with people.'],
            ['Is AI Reconciliation available now?', 'It is available on request. Ask our team whether it is available for your business.'],
        ],
        'ai:financial-assistant' => [
            ['What does the AI Financial Assistant do?', 'It answers routine payment questions — such as "why was this transaction declined?" — without a support ticket, and supports operational alerts.'],
            ['Does it give financial, legal or tax advice?', 'No. It answers questions about your payments; it is not a source of legal, accounting, tax or investment advice.'],
            ['What happens if it cannot answer?', 'The question goes to a person.'],
            ['Do I need to share API keys with it?', 'No. Never share API keys in questions.'],
        ],
        'ai:cash-flow-intelligence' => [
            ['What is AI Cash-Flow Intelligence?', 'A capability that forecasts near-term liquidity from live transaction data rather than a monthly spreadsheet.'],
            ['How accurate are the forecasts?', 'Paynancial does not publish forecast accuracy figures. A forecast informs decisions; it does not guarantee an outcome.'],
            ['Who makes treasury decisions?', 'Your team. Treasury and funding decisions stay with people.'],
            ['What data does it use?', 'Your Paynancial transaction data — payments, payouts and settlements.'],
        ],
        'ai:revenue-forecasting' => [
            ['What is AI Revenue Forecasting?', 'A capability that turns raw transaction volume into the specific, forward-looking revenue numbers a finance lead needs.'],
            ['How does it relate to Payment Analytics?', 'It works alongside the dashboards and reports in Payment Analytics, on the same transaction data.'],
            ['Are the forecasts guaranteed?', 'No. Paynancial does not publish forecast accuracy figures; interpreting the numbers and acting on them stays with people.'],
            ['Is it available now?', 'It is available on request. Ask our team whether it is available for your business.'],
        ],
        'product:bulk-payouts' => [
            ['What are bulk payouts?', 'Paying many beneficiaries at once. With Paynancial Payouts you submit a batch of transfers in one action from the dashboard, or in a single API request, and each payout in the batch is tracked individually.'],
            ['Can a bulk payout include both bank and UPI transfers?', 'Payouts can be sent to bank accounts and UPI IDs. Ask our team how to structure a mixed batch for your business.'],
            ['What happens if one payout in a batch fails?', 'It fails with a clear reason and can be corrected and retried. The other payouts in the batch are unaffected.'],
            ['How do I avoid paying someone twice if I re-submit a batch?', 'Send an idempotency key with each payout. Re-submitting with the same key returns the original payout instead of creating a new one.'],
        ],
        'product:vendor-payments' => [
            ['How do I pay suppliers with Paynancial?', 'Save each supplier as a beneficiary with their bank account or UPI ID, then pay them individually or in a batch with Payouts, from the dashboard or the API.'],
            ['Can I pay many vendors at once?', 'Yes. Payouts supports bulk payouts, so you can pay a batch of vendors in one action and track each payment individually.'],
            ['How do I know a vendor was paid?', 'Each payout is tracked from initiated to completed, and completed payouts appear in your payout report.'],
            ['Can I link payments to vendor invoices?', 'Keep the payout ID against the invoice in your own accounts system, and use the payout report to reconcile.'],
        ],
        'product:employee-payments' => [
            ['Is Paynancial a payroll system?', 'No. Paynancial does not calculate salaries, deductions or taxes. Your payroll or finance process calculates what each person is owed, and Payouts sends the payments.'],
            ['Can I pay staff to their UPI ID?', 'Yes. Payouts can be sent to a bank account or a UPI ID.'],
            ['Can I pay freelancers as well as employees?', 'Yes. Payouts is built for paying staff, freelancers, vendors and partners.'],
            ['Can I pay everyone in one pay run?', 'Yes. Submit a batch in one action and track each payment individually.'],
        ],
        'product:partner-payments' => [
            ['How do I pay channel partners with Paynancial?', 'Calculate what each partner is owed in your own system, then pay them with Payouts — by bank transfer or UPI, individually or in a batch, from the dashboard or the API.'],
            ['Can my commission system trigger partner payouts automatically?', 'Yes. Payouts is API-first, so your system can create payouts directly — with an idempotency key so a retried run never pays a partner twice.'],
            ['Does Paynancial calculate partner commissions?', 'No. Commission rules and calculations stay in your own systems; Paynancial pays the amounts you send.'],
            ['Is this the same as the Paynancial Partner Program?', 'No. This page is about paying your own partners. The Partner Program is for businesses that want to partner with Paynancial.'],
        ],
        'product:mis-reports' => [
            ['What is MIS reporting for payments?', 'MIS (management information system) reporting gives owners and finance leads a regular view of the numbers. For payments that means how much came in, through which methods, what has settled, what was refunded and what is still pending.'],
            ['What reports does Paynancial provide?', 'Payment Analytics provides transaction reports by payment method, status and period, with settlement and refund data alongside. Reports can be exported, delivered on a schedule, or generated for a date range through the Reports API.'],
            ['Can I get a report through the API?', 'Yes. The Reports API generates a transaction report for a date range — the published example requests a CSV file — and returns a link to download it.'],
            ['Can my CA use Paynancial reports?', 'You can export a period\'s transactions for your accountant or CA to work from, instead of copying figures from a bank statement.'],
        ],
        'product:chargebacks' => [
            ['What is a chargeback?', 'A chargeback is a payment reversal a customer raises with their card issuer or bank — for example when they believe a transaction was unauthorised, or that the merchant did not deliver as promised and the matter was not resolved directly.'],
            ['What happens when a customer raises a chargeback on Paynancial?', 'Under Paynancial\'s Refund Policy, when Paynancial receives a chargeback notification it shares the relevant transaction evidence with the merchant, who can respond within the network\'s applicable timeline (typically 7–10 days). The card network or bank makes the final decision.'],
            ['What is the difference between a refund and a chargeback?', 'A refund is something the merchant issues. A chargeback is something the customer raises through their own bank or card issuer. Refunding a genuine problem promptly is usually the best way to avoid a chargeback.'],
            ['How can I reduce chargebacks?', 'Refund genuine problems promptly, keep delivery confirmations, invoices and customer messages, make sure customers recognise your business name, publish a clear refund and cancellation policy, and respond to disputes before the deadline.'],
        ],
        'product:international-payments' => [
            ['Does Paynancial support international payments?', 'Paynancial\'s published payout capability sends funds to bank accounts and UPI IDs. Countries, currencies, foreign exchange and cross-border availability are not published on this site, so ask our team about your requirements.'],
            ['Which rules apply to cross-border payments from India?', 'Cross-border payments are subject to India\'s foreign exchange rules. Paynancial will publish a summary only once it has been verified against official sources; until then, take advice from your bank and your CA.'],
            ['What should I confirm before planning international payments?', 'The countries and currencies you need, whether you are paying out, collecting or both, how currency conversion is handled, and the fees, timelines and documents involved.'],
            ['Do international payments have tax implications?', 'Payments to and from abroad can have GST and income-tax consequences. Take advice from your CA before the first cross-border payment.'],
        ],
        'product:invoice-management' => [
            ['Can customers pay an invoice online with Paynancial?', 'Yes. Create a Payment Link titled with the invoice number, add it to the invoice, and the customer can pay in one step.'],
            ['How do I know an invoice has been paid?', 'Each Payment Link shows whether it is active, paid, expired or disabled, and payment webhooks can tell your accounting system when a payment is made.'],
            ['Does Paynancial create GST invoices?', 'No. Invoice creation, numbering and tax calculation are not described on this site. Keep using your accounting software or CA to issue GST invoices, and use Paynancial to get them paid.'],
            ['Can I collect recurring invoices?', 'Yes. Smart Collections collects subscription and instalment payments on a schedule, with retries for failed attempts.'],
        ],
        'product:expense-management' => [
            ['Does Paynancial offer expense management?', 'Expense management — receipt capture, approval workflows, spend policies or company cards — is not described on this site. What Paynancial publishes is the payment side: paying vendors and staff through Payouts, with every payout tracked and reported.'],
            ['How do I pay approved bills and claims?', 'Send the approved amounts through Payouts to a bank account or UPI ID, singly or in a batch with a bulk payout, and follow each payout to completion.'],
            ['Does Paynancial deduct TDS?', 'No. Paynancial does not calculate or deduct TDS. Agree the net amount with your CA before you pay.'],
            ['How do I keep spending records?', 'Keep the supplier\'s invoice for each business expense and use your payout report to match each payment to its bill. Your CA can advise on the tax records you need.'],
        ],
        'product:payment-pages' => [
            ['What is a payment page?', 'The secure page a customer lands on to pay you. With Paynancial, every Payment Link opens a branded payment page that shows your business name and the amount due.'],
            ['Do I need a website to use a payment page?', 'No. Create a Payment Link from the dashboard, share it by email, WhatsApp, SMS or on an invoice, and the customer pays on the page.'],
            ['Can the customer enter the amount?', 'Yes. A link can have a fixed amount, or leave the amount open for the customer to enter. You can also set an expiry date.'],
            ['Can I build donation or event pages with custom fields?', 'A standalone page builder with custom fields is not described on this site. Ask our team about your requirements.'],
        ],
        'product:embedded-payments' => [
            ['What are embedded payments?', 'Payments a platform\'s users make inside the platform itself, instead of on a separate provider\'s page — for example, paying inside a booking flow or a business software product.'],
            ['How do platforms embed payments with Paynancial?', 'As technology partners: they embed the Payment API directly, build their own checkout UI on top of it or use the hosted checkout, and receive a real-time status and payment webhook for every transaction.'],
            ['Which payment methods can my platform accept?', 'Cards, UPI, netbanking and wallets, through one integration with the Payment Gateway.'],
            ['Can my platform onboard its own users as merchants?', 'Onboarding a platform\'s users as separate merchants is not described on this site. Partners can enroll customers, submit KYC and track approvals in the Partner Hub; ask our team about your platform\'s model.'],
        ],
        'product:embedded-payouts' => [
            ['What are embedded payouts?', 'Payouts a platform makes to the people on it — sellers, creators, drivers or partners — from inside its own product, triggered by its own systems.'],
            ['How does my platform pay its users with Paynancial?', 'Through the Payout API: save each recipient\'s bank account or UPI ID as a beneficiary, create a payout with an idempotency key when money is due, and follow it by webhook. Bulk payouts pay many recipients at once.'],
            ['Can I pay sellers to a UPI ID?', 'Yes. Payouts supports UPI IDs as well as bank accounts.'],
            ['Does Paynancial deduct TDS on payouts?', 'No. Paynancial does not calculate or deduct tax. Agree the amounts with your CA before you pay.'],
        ],
        'product:embedded-billing' => [
            ['What is embedded billing?', 'Recurring billing that lives inside a software product — subscriptions, memberships or instalments collected without a separate billing tool.'],
            ['How does embedded billing work with Paynancial?', 'Technology partners embed the Collections API: create a collection with the customer, amount and schedule, and Smart Collections collects on schedule, retries failed attempts, keeps the customer informed and reconciles each cycle.'],
            ['How are recurring payments authorised?', 'Recurring payments rely on the customer authorising them in advance. The specific rules for cards, UPI and bank accounts are not summarised here until verified against official sources — ask our team.'],
            ['Can Paynancial bill on behalf of my platform\'s business users?', 'Billing on behalf of a platform\'s own business users is not described on this site. Ask our team about your requirements.'],
        ],
        'product:wallet-infrastructure' => [
            ['What is wallet infrastructure?', 'The systems that let a platform hold and show a stored balance for each of its users, which they can top up and spend.'],
            ['Does Paynancial offer wallet infrastructure?', 'Wallet infrastructure is not described on this site, so no wallet capability is claimed. The Payment Gateway accepts wallets as a payment method, which is different from running a wallet.'],
            ['Is running a wallet regulated?', 'Running a wallet comes with regulatory requirements. Paynancial will publish a summary only once it has been verified against official sources; take professional advice before designing a stored balance.'],
            ['What is the alternative to a wallet?', 'Many platforms only need a record of what each user is owed, settled by payouts to a bank account or UPI ID. Talk to our team before designing around a stored balance.'],
        ],
        'product:split-payments' => [
            ['What are split payments?', 'Dividing one customer payment between several recipients — typically a marketplace, its seller and sometimes a delivery or service partner.'],
            ['Does Paynancial split payments at the point of collection?', 'Splitting at the point of collection is not described as a Paynancial product on this site. The published route is to collect through the Payment Gateway and pay each seller or partner their share through Payouts.'],
            ['Do regulations affect marketplace payments?', 'A marketplace that collects money and passes it on to sellers can take on regulatory responsibilities. Paynancial will publish a summary only once verified against official sources; check your model with a compliance adviser and our team.'],
            ['How do I pay many sellers at once?', 'With Bulk Payouts: submit a batch of payouts in one action and track each payout individually.'],
        ],
        'product:white-label-payments' => [
            ['What are white-label payments?', 'Payments presented under a platform\'s own brand instead of the payment provider\'s, from checkout to receipts.'],
            ['Does Paynancial offer white-label payments?', 'A white-label product is not described on this site. What is published: a custom checkout you build on the API, and technology and reseller partner programmes.'],
            ['Can I resell Paynancial under my own relationship with merchants?', 'Reseller partners resell Paynancial products under their own commercial relationship with merchants. See the Partners page.'],
            ['Who is responsible to merchants in a white-label setup?', 'Responsibilities do not change with the brand on the page. Confirm the arrangement with our team and take professional advice.'],
        ],
    ];
}

/** One page's FAQs as [question, answer] pairs. */
function faq_set(string $key): array
{
    return faq_sets()[$key] ?? [];
}
