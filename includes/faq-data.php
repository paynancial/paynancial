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
            ['Which endpoints are documented here?', 'Payments, refunds, payouts, payment links and collections, with the parameters used in Paynancial\'s published examples. For anything not covered on this page, contact developer support.'],
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
                ['Which payment methods are supported?', 'Cards, UPI, netbanking and wallets through a single integration — see the API Reference for the full method list.'],
                ['Can I test before going live?', 'Yes — sandbox API keys let you run a complete integration test with no real funds involved.'],
            ],
        'product:payment-links' => [
                ['Do I need a website to use payment links?', 'No — a payment link works entirely on its own; you only need a way to share the link, like email or chat.'],
                ['Can a link be reused?', 'A link stays active until it is paid, expires, or you disable it, so it can be shared with more than one customer if left open.'],
            ],
        'product:payment-collection' => [
                ['What happens if a collection fails?', 'A failed attempt is recorded with a reason code and can be retried on your defined schedule.'],
                ['Can I collect from a batch of customers at once?', 'Yes — bulk collection lets you submit a batch and track each customer’s result individually.'],
            ],
        'product:payouts' => [
                ['Can I pay more than one person at once?', 'Yes — bulk payouts let you submit a batch of transfers in a single request.'],
                ['What payout modes are supported?', 'Bank transfer and UPI are both supported for sending funds to a beneficiary.'],
            ],
        'product:payment-analytics' => [
                ['Can I export data for my accounting system?', 'Yes — reports can be exported in common formats for use outside the dashboard.'],
                ['Does this show real customer data or sample data?', 'Analytics reflect your own account’s real transactions and settlements — there is no sample or simulated data in your dashboard.'],
            ],
    ];
}

/** One page's FAQs as [question, answer] pairs. */
function faq_set(string $key): array
{
    return faq_sets()[$key] ?? [];
}
