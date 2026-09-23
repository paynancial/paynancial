<?php
/**
 * Business Services — single source of truth for the Business Services
 * vertical: service categories, individual services and incorporation
 * jurisdictions. The landing page, service pages, global incorporation
 * page, jurisdiction directory and jurisdiction pages all render from this
 * data, so adding a service or jurisdiction here publishes its page.
 *
 * STANDALONE: these pages are reachable by direct URL and link to each
 * other contextually, but are deliberately NOT linked from the global
 * header, mega-menus, footer or sitemap until that is approved.
 *
 * Content policy (see README "Content & claims policy"): no fees,
 * government charges, turnaround times, customer counts, country counts,
 * regulatory approvals or tax/legal benefits are stated anywhere in this
 * file. Statutory references are limited to the name of the governing
 * framework. Fields such as 'fees' and 'timeline' are deliberately null
 * until verified figures are supplied — templates render a "shared in
 * your written quote" state for null values instead of inventing one.
 */

declare(strict_types=1);

/** URL helpers — keep every Business Services link built in one place. */
function bs_url(string $slug = ''): string
{
    return '/business-services' . ($slug !== '' ? '/' . $slug : '');
}
function bs_jurisdiction_url(string $slug = ''): string
{
    return '/business-services/jurisdictions' . ($slug !== '' ? '/' . $slug : '');
}
/** Enquiry URL: routes into the existing contact form (sales intent). */
function bs_enquiry_url(string $topic = ''): string
{
    $q = ['intent' => 'sales', 'service' => 'business-services'];
    if ($topic !== '') {
        $q['topic'] = $topic;
    }
    return '/contact?' . http_build_query($q);
}

/**
 * Service categories, in display order, with the services listed on each
 * landing-page category card.
 */
function bs_categories(): array
{
    return [
        'start' => [
            'label'    => 'Start a Business',
            'desc'     => 'Choose the right legal structure and get incorporated with guidance at every step.',
            'icon'     => 'launch',
            'services' => ['company-incorporation', 'private-limited-company', 'llp-registration', 'opc-registration', 'partnership-registration'],
        ],
        'register' => [
            'label'    => 'Business Registration',
            'desc'     => 'Tax, MSME and startup registrations that let your business operate and grow.',
            'icon'     => 'register',
            'services' => ['gst-registration', 'msme-registration', 'startup-registration', 'pan-tan-assistance'],
        ],
        'protect' => [
            'label'    => 'Protect Your Business',
            'desc'     => 'Search for and register the brand you are building.',
            'icon'     => 'protect',
            'services' => ['trademark-registration', 'trademark-search'],
        ],
        'comply' => [
            'label'    => 'Compliance & Support',
            'desc'     => 'Stay in good standing with structured, calendar-driven filings.',
            'icon'     => 'comply',
            'services' => ['roc-compliance', 'annual-compliance', 'company-changes'],
        ],
    ];
}

/**
 * Individual services. Every entry renders through the same detail
 * template (pages/business-services/service.php).
 *
 * Keys:
 *   name, short (menu label), category, summary, framework (governing
 *   statute/authority — name only), why[], requirements[], documents[],
 *   process[] ([title, text]), faqs[] ([q, a]), related[], fees, timeline,
 *   popular (bool), structures (company-incorporation only).
 */
function bs_services(): array
{
    $standardProcess = [
        ['Share your requirements', 'Tell us about your business, the people involved and what you want to achieve.'],
        ['Document checklist', 'We send a checklist tailored to your case and review what you share before anything is filed.'],
        ['Preparation & filing', 'Applications and forms are prepared, shared with you for approval, and filed with the relevant authority.'],
        ['Follow-up & handover', 'We track the application, respond to queries from the authority and hand over the final documents.'],
    ];

    return [
        // ---------------------------------------------------------------- Start a business
        'company-incorporation' => [
            'name'      => 'Company Incorporation',
            'short'     => 'Company Incorporation',
            'category'  => 'start',
            'popular'   => true,
            'eyebrow'   => 'Company Incorporation',
            'headline'  => 'Start Your Business with Confidence',
            'summary'   => 'Incorporate your company in India or in selected international jurisdictions, with structured documentation and expert guidance from first conversation to certificate.',
            'who'       => ['Founders turning an idea or side project into a registered business', 'Existing businesses moving from a proprietorship or partnership to a company', 'Entrepreneurs planning to operate from, or expand into, another jurisdiction'],
            'framework' => 'In India, companies are incorporated with the Registrar of Companies under the Companies Act, 2013. International incorporations follow the rules of the chosen jurisdiction.',
            'why' => [
                ['Separate legal identity', 'An incorporated entity can own assets, enter contracts and open accounts in its own name.'],
                ['Credibility with partners', 'Banks, payment providers, enterprise customers and investors generally expect to work with a registered entity.'],
                ['Room to grow', 'A company structure makes it simpler to bring in co-founders, investors and employees as you scale.'],
            ],
            'structures' => ['private-limited-company', 'llp-registration', 'opc-registration', 'partnership-registration'],
            'requirements' => [
                'Details of the proposed directors / partners and shareholders',
                'Two or more proposed names for the entity, in order of preference',
                'A registered office address for the entity',
                'A description of the main business activity',
                'For international incorporation: the jurisdiction and any activity-specific requirements we identify with you',
            ],
            'documents' => [
                'Identity proof for each director / shareholder (e.g. PAN and Aadhaar for Indian residents, passport for foreign nationals)',
                'Address proof for each director / shareholder',
                'Recent photographs of directors / shareholders',
                'Proof of registered office address and owner\'s consent (e.g. utility bill and NOC)',
                'Additional documents for international incorporations, as required by the chosen jurisdiction',
            ],
            'process' => [
                ['Share Requirements', 'Tell us what you are building, who is involved and where you want to incorporate.'],
                ['Choose Structure', 'We walk through the available structures and help you decide what fits.'],
                ['Documentation', 'We send a tailored checklist and review every document before filing.'],
                ['Filing & Registration', 'Name approval and incorporation forms are prepared and filed with the relevant registry.'],
                ['Post-Incorporation Support', 'Next steps such as tax registrations, a compliance calendar and — when you are ready — accepting payments.'],
            ],
            'faqs' => [
                ['Which structure should I choose?', 'It depends on the number of founders, how you plan to raise capital, and your compliance appetite. Our team explains the trade-offs for your specific situation before you commit.'],
                ['Can you help incorporate outside India?', 'Yes. We support incorporation in selected international jurisdictions. Browse the jurisdictions we list, or speak with our team if yours is not shown.'],
                ['How much does incorporation cost and how long does it take?', 'Government fees and processing times vary by structure, jurisdiction and authority. We share a written quote and an expected timeline once we understand your requirements.'],
                ['What happens after incorporation?', 'We can help with registrations such as GST, ongoing ROC compliance and changes to the company — and Paynancial can help you start accepting payments.'],
            ],
            'related' => ['private-limited-company', 'gst-registration', 'trademark-registration', 'roc-compliance'],
            'fees' => null, 'timeline' => null,
        ],
        'private-limited-company' => [
            'name'      => 'Private Limited Company Registration',
            'short'     => 'Private Limited',
            'category'  => 'start',
            'summary'   => 'The structure most growing and investor-backed businesses in India choose — with limited liability and a clear shareholding model.',
            'who'       => ['Founders planning to raise external investment', 'Businesses that want to offer equity to employees', 'Teams of two or more founders building for scale'],
            'suitable'  => 'Growing businesses planning to raise investment or issue equity.',
            'framework' => 'Governed by the Companies Act, 2013 and registered with the Registrar of Companies (Ministry of Corporate Affairs).',
            'why' => [
                ['Limited liability', 'Shareholders\' liability is limited to their shareholding.'],
                ['Built for fundraising', 'Equity can be issued to investors and employees through a defined share structure.'],
                ['Perpetual succession', 'The company continues to exist regardless of changes in its members.'],
            ],
            'requirements' => [
                'A minimum of two directors and two shareholders (a director may also be a shareholder)',
                'At least one director resident in India',
                'Proposed company names and a registered office address in India',
            ],
            'documents' => [
                'Identity and address proof of all directors and shareholders',
                'Photographs of directors and shareholders',
                'Registered office address proof and owner\'s NOC',
            ],
            'process' => $standardProcess,
            'faqs' => [
                ['Can one person own a private limited company?', 'A private limited company needs at least two shareholders. A single founder may consider a One Person Company (OPC) instead.'],
                ['What ongoing compliance applies?', 'Private limited companies have annual ROC filings, statutory registers and board meetings. We can support these through our ROC and annual compliance services.'],
            ],
            'related' => ['company-incorporation', 'opc-registration', 'annual-compliance', 'gst-registration'],
            'fees' => null, 'timeline' => null,
        ],
        'llp-registration' => [
            'name'      => 'LLP Registration',
            'short'     => 'LLP Registration',
            'category'  => 'start',
            'summary'   => 'A Limited Liability Partnership combines the flexibility of a partnership with the protection of limited liability.',
            'who'       => ['Professional firms and consultancies', 'Partners who want limited liability with flexible management', 'Businesses that do not plan to raise equity investment'],
            'suitable'  => 'Professional and service firms that want limited liability with flexible management.',
            'framework' => 'Governed by the Limited Liability Partnership Act, 2008 and registered with the Ministry of Corporate Affairs.',
            'why' => [
                ['Limited liability', 'Partners are not personally liable for the misconduct of other partners.'],
                ['Flexible management', 'Roles and profit sharing are set out in the LLP agreement.'],
                ['Separate legal entity', 'The LLP can own property and contract in its own name.'],
            ],
            'requirements' => [
                'A minimum of two designated partners, at least one resident in India',
                'Proposed LLP names and a registered office address in India',
                'Agreed terms for the LLP agreement (contribution, profit sharing, roles)',
            ],
            'documents' => [
                'Identity and address proof of all partners',
                'Photographs of partners',
                'Registered office address proof and owner\'s NOC',
            ],
            'process' => $standardProcess,
            'faqs' => [
                ['Is an LLP suitable for raising equity investment?', 'LLPs do not issue shares, so investors who expect equity typically prefer a private limited company. We can explain the differences for your plans.'],
                ['Do I need an LLP agreement?', 'Yes — the LLP agreement defines the rights and duties of partners and is filed after incorporation. We help you prepare it.'],
            ],
            'related' => ['company-incorporation', 'private-limited-company', 'partnership-registration', 'annual-compliance'],
            'fees' => null, 'timeline' => null,
        ],
        'opc-registration' => [
            'name'      => 'One Person Company (OPC) Registration',
            'short'     => 'OPC Registration',
            'category'  => 'start',
            'summary'   => 'A company structure designed for a single founder who wants limited liability and a corporate identity.',
            'who'       => ['Solo founders who want a company structure', 'Freelancers and consultants formalising their business', 'Single owners who want limited liability'],
            'suitable'  => 'A single founder who wants a company structure with limited liability.',
            'framework' => 'Governed by the Companies Act, 2013 and registered with the Registrar of Companies.',
            'why' => [
                ['Single-founder friendly', 'One individual can be the sole member of the company.'],
                ['Limited liability', 'The founder\'s personal assets are separated from the business.'],
                ['Corporate identity', 'Operate with the credibility of a registered company.'],
            ],
            'requirements' => [
                'One member (shareholder) and at least one director',
                'A nominee who would become the member in specified circumstances',
                'Proposed company names and a registered office address in India',
            ],
            'documents' => [
                'Identity and address proof of the member, director and nominee',
                'Nominee\'s consent',
                'Registered office address proof and owner\'s NOC',
            ],
            'process' => $standardProcess,
            'faqs' => [
                ['Can an OPC be converted later?', 'An OPC can be converted into a private limited company as the business grows. We can guide you through the conversion when the time comes.'],
            ],
            'related' => ['company-incorporation', 'private-limited-company', 'gst-registration', 'annual-compliance'],
            'fees' => null, 'timeline' => null,
        ],
        'partnership-registration' => [
            'name'      => 'Partnership Firm Registration',
            'short'     => 'Partnership',
            'category'  => 'start',
            'summary'   => 'Register a partnership firm with a well-drafted partnership deed that sets out how the business is run.',
            'who'       => ['Two or more people starting a business together', 'Family businesses and small trading firms', 'Existing unregistered partnerships'],
            'suitable'  => 'Two or more people running a business together under a partnership deed.',
            'framework' => 'Governed by the Indian Partnership Act, 1932 and registered with the Registrar of Firms of the relevant state.',
            'why' => [
                ['Simple to set up', 'A familiar structure for businesses run by two or more people.'],
                ['Clear arrangements', 'The partnership deed records capital, profit sharing and responsibilities.'],
                ['Registered standing', 'A registered firm is better placed to enforce its contractual rights.'],
            ],
            'requirements' => [
                'Two or more partners',
                'A partnership deed agreed by all partners',
                'The firm\'s name and principal place of business',
            ],
            'documents' => [
                'Identity and address proof of all partners',
                'Partnership deed',
                'Proof of the firm\'s place of business',
            ],
            'process' => $standardProcess,
            'faqs' => [
                ['How is a partnership different from an LLP?', 'In a general partnership, partners\' liability is not limited. An LLP offers limited liability with similar flexibility. We can help you compare the two.'],
            ],
            'related' => ['llp-registration', 'company-incorporation', 'gst-registration', 'msme-registration'],
            'fees' => null, 'timeline' => null,
        ],

        // ---------------------------------------------------------------- Business registration
        'gst-registration' => [
            'name'      => 'GST Registration',
            'short'     => 'GST Registration',
            'category'  => 'register',
            'summary'   => 'Register your business under the Goods and Services Tax framework so you can invoice, collect tax and claim input credit correctly.',
            'who'       => ['Businesses that meet the conditions for GST registration', 'Sellers on e-commerce marketplaces', 'Businesses supplying goods or services to other businesses'],
            'framework' => 'Governed by the Central Goods and Services Tax Act, 2017 and corresponding State/UT GST laws.',
            'why' => [
                ['Operate compliantly', 'Registration is required for businesses that meet the conditions set under GST law.'],
                ['Input tax credit', 'Registered businesses can claim credit for GST paid on eligible purchases.'],
                ['Sell more widely', 'GST registration is commonly required by marketplaces and business customers.'],
            ],
            'requirements' => [
                'PAN of the business or proprietor',
                'Details of the principal place of business',
                'Details of promoters / partners / directors',
                'Bank account details of the business',
            ],
            'documents' => [
                'PAN and incorporation / registration certificate (for entities)',
                'Identity and address proof and photographs of promoters',
                'Proof of principal place of business',
                'Bank account proof (e.g. cancelled cheque or statement)',
            ],
            'process' => $standardProcess,
            'faqs' => [
                ['Does my business need GST registration?', 'It depends on factors such as turnover, the nature of supplies and whether you sell inter-state or online. Our team will help you confirm whether registration applies.'],
            ],
            'related' => ['msme-registration', 'company-incorporation', 'pan-tan-assistance', 'annual-compliance'],
            'fees' => null, 'timeline' => null,
        ],
        'msme-registration' => [
            'name'      => 'MSME / Udyam Registration',
            'short'     => 'MSME / Udyam',
            'category'  => 'register',
            'summary'   => 'Register your enterprise on the Udyam portal to be recognised as a micro, small or medium enterprise.',
            'who'       => ['Micro, small and medium enterprises', 'Manufacturers and service businesses of all structures', 'Businesses supplying to buyers that ask for MSME registration'],
            'framework' => 'Udyam Registration is administered by the Ministry of Micro, Small and Medium Enterprises, Government of India.',
            'why' => [
                ['Official recognition', 'Receive a Udyam Registration Certificate for your enterprise.'],
                ['Access to schemes', 'Recognition may make you eligible for government schemes meant for MSMEs.'],
                ['Easier procurement', 'Some buyers and programmes ask suppliers for MSME registration.'],
            ],
            'requirements' => [
                'Aadhaar of the proprietor / partner / authorised signatory',
                'PAN of the enterprise',
                'Basic business, bank and activity details',
            ],
            'documents' => [
                'Aadhaar and PAN details',
                'Business address and bank details',
                'Details of the principal business activity',
            ],
            'process' => $standardProcess,
            'faqs' => [
                ['Which category will my enterprise fall into?', 'The category depends on investment and turnover criteria notified by the Government. We help you complete the registration accurately.'],
            ],
            'related' => ['gst-registration', 'startup-registration', 'company-incorporation', 'trademark-registration'],
            'fees' => null, 'timeline' => null,
        ],
        'startup-registration' => [
            'name'      => 'Startup India Registration',
            'short'     => 'Startup Registration',
            'category'  => 'register',
            'summary'   => 'Apply for DPIIT recognition under the Startup India initiative with a well-prepared application.',
            'who'       => ['Private limited companies, LLPs and registered partnership firms', 'Businesses working on an innovative product, process or service', 'Founders exploring programmes under the Startup India initiative'],
            'framework' => 'Startup recognition is granted by the Department for Promotion of Industry and Internal Trade (DPIIT) under the Startup India initiative.',
            'why' => [
                ['Recognised status', 'DPIIT recognition identifies your business as a startup under the initiative.'],
                ['Programme access', 'Recognised startups can apply for benefits offered under Startup India, subject to eligibility.'],
                ['Clear positioning', 'A structured application forces clarity on your innovation and business model.'],
            ],
            'requirements' => [
                'An incorporated private limited company, registered partnership firm or LLP',
                'A description of the product or service and what makes it innovative',
                'Details of the founders and the entity',
            ],
            'documents' => [
                'Certificate of incorporation / registration',
                'A write-up or pitch describing the business',
                'Founder details and supporting material (website, product links)',
            ],
            'process' => $standardProcess,
            'faqs' => [
                ['Is my business eligible?', 'Eligibility depends on criteria defined by DPIIT, including entity type and age. We review your case before applying.'],
            ],
            'related' => ['private-limited-company', 'msme-registration', 'trademark-registration', 'gst-registration'],
            'fees' => null, 'timeline' => null,
        ],
        'pan-tan-assistance' => [
            'name'      => 'PAN / TAN Assistance',
            'short'     => 'PAN / TAN Assistance',
            'category'  => 'register',
            'summary'   => 'Apply for or update the Permanent Account Number and Tax Deduction Account Number your business needs.',
            'who'       => ['Newly formed companies, LLPs and firms', 'Businesses that deduct or collect tax at source', 'Businesses correcting or updating PAN / TAN details'],
            'framework' => 'PAN and TAN are issued under the Income-tax Act, 1961 by the Income Tax Department.',
            'why' => [
                ['PAN for the business', 'Required for tax filings, bank accounts and many financial transactions.'],
                ['TAN for deductors', 'Required by businesses that deduct or collect tax at source.'],
                ['Accurate records', 'Corrections and updates keep your records consistent across registrations.'],
            ],
            'requirements' => [
                'Entity details as per the incorporation / registration certificate',
                'Details of the authorised signatory',
            ],
            'documents' => [
                'Certificate of incorporation / registration (for entities)',
                'Identity and address proof of the authorised signatory',
            ],
            'process' => $standardProcess,
            'faqs' => [
                ['Do new companies still need to apply for PAN?', 'For companies incorporated in India, PAN and TAN applications are often made as part of the incorporation filing. We confirm what is needed in your case.'],
            ],
            'related' => ['gst-registration', 'company-incorporation', 'msme-registration', 'annual-compliance'],
            'fees' => null, 'timeline' => null,
        ],

        // ---------------------------------------------------------------- Protect
        'trademark-registration' => [
            'name'      => 'Trademark Registration',
            'short'     => 'Trademark Registration',
            'category'  => 'protect',
            'summary'   => 'Protect your brand name and logo with a trademark application prepared around the right classes.',
            'who'       => ['Startups and businesses launching a new brand', 'Businesses already trading under an unregistered name or logo', 'Founders protecting a product or service name'],
            'framework' => 'Trademarks in India are registered under the Trade Marks Act, 1999 with the Trade Marks Registry.',
            'why' => [
                ['Exclusive rights', 'A registered trademark gives you the right to use the mark for the goods or services it covers.'],
                ['Brand value', 'A protected brand is an asset that can be licensed or assigned.'],
                ['Deter copying', 'Registration makes it easier to act against misuse of your brand.'],
            ],
            'requirements' => [
                'The word mark and / or logo to be protected',
                'The goods or services the mark is used for (to identify the classes)',
                'Applicant details (individual or entity)',
            ],
            'documents' => [
                'Logo file (for device marks)',
                'Identity and address proof of the applicant / entity registration certificate',
                'Evidence of prior use, if the mark is already in use',
                'Authorisation for filing on your behalf',
            ],
            'process' => [
                ['Trademark search', 'We search existing marks to assess conflicts before you file.'],
                ['Class selection', 'We help you identify the classes that match your goods and services.'],
                ['Application filing', 'The application is prepared, shared for approval and filed with the Registry.'],
                ['Tracking & responses', 'We track the application and help you respond to examination reports or objections.'],
            ],
            'faqs' => [
                ['Should I search before filing?', 'Yes. A search helps identify similar existing marks that could lead to objections. We recommend a search before every filing.'],
                ['How long does registration take?', 'Timelines depend on the Registry and whether the application receives objections or oppositions. We keep you updated at each stage.'],
            ],
            'related' => ['trademark-search', 'company-incorporation', 'startup-registration', 'msme-registration'],
            'fees' => null, 'timeline' => null,
        ],
        'trademark-search' => [
            'name'      => 'Trademark Search',
            'short'     => 'Trademark Search',
            'category'  => 'protect',
            'summary'   => 'Check whether your proposed brand name or logo conflicts with existing marks before you invest in it.',
            'who'       => ['Founders choosing a company or product name', 'Businesses preparing a trademark application', 'Teams comparing shortlisted brand names'],
            'framework' => 'Searches are carried out against records maintained by the Trade Marks Registry, India.',
            'why' => [
                ['Reduce risk', 'Identify similar marks before you build a brand around a name.'],
                ['Better applications', 'Search findings inform how and in which classes you file.'],
                ['Informed decisions', 'Choose between candidate names with a clearer view of the landscape.'],
            ],
            'requirements' => ['The name(s) or logo(s) you want to check', 'A description of your goods or services'],
            'documents' => ['Logo file, if checking a device mark'],
            'process' => [
                ['Share your marks', 'Send the names or logos you are considering.'],
                ['Search & analysis', 'We search relevant classes for identical and similar marks.'],
                ['Findings report', 'You receive a summary of findings and our recommendation on next steps.'],
            ],
            'faqs' => [
                ['Does a clear search guarantee registration?', 'No search can guarantee registration — the Registry examines every application independently. A search reduces the risk of avoidable objections.'],
            ],
            'related' => ['trademark-registration', 'startup-registration', 'company-incorporation', 'msme-registration'],
            'fees' => null, 'timeline' => null,
        ],

        // ---------------------------------------------------------------- Comply
        'roc-compliance' => [
            'name'      => 'ROC Compliance',
            'short'     => 'ROC Compliance',
            'category'  => 'comply',
            'summary'   => 'Stay in good standing with the Registrar of Companies through structured, calendar-driven filings.',
            'who'       => ['Private limited companies, OPCs and LLPs', 'Companies that have fallen behind on filings', 'Founders who want a clear compliance calendar'],
            'framework' => 'Filings are made with the Registrar of Companies under the Companies Act, 2013 or the LLP Act, 2008, as applicable.',
            'why' => [
                ['Good standing', 'Timely filings keep your company or LLP active and compliant.'],
                ['Fewer penalties', 'A clear compliance calendar reduces the risk of missed deadlines.'],
                ['Investor-ready records', 'Well-kept statutory records make due diligence smoother.'],
            ],
            'requirements' => ['Company / LLP details and login access where applicable', 'Financial statements and board / partner decisions for the period'],
            'documents' => ['Financial statements and auditor\'s report (where applicable)', 'Board / partner resolutions', 'Statutory registers and minutes'],
            'process' => [
                ['Compliance review', 'We review your entity and identify filings due.'],
                ['Calendar & checklist', 'You receive a calendar and the documents needed for each filing.'],
                ['Preparation & filing', 'Forms are prepared, approved by you and filed with the Registrar.'],
                ['Records', 'Acknowledgements and filed documents are shared for your records.'],
            ],
            'faqs' => [
                ['Which filings apply to my company?', 'Filing requirements depend on the type and size of your entity. We confirm your obligations during the compliance review.'],
            ],
            'related' => ['annual-compliance', 'company-changes', 'company-incorporation', 'private-limited-company'],
            'fees' => null, 'timeline' => null,
        ],
        'annual-compliance' => [
            'name'      => 'Annual Compliance',
            'short'     => 'Annual Compliance',
            'category'  => 'comply',
            'summary'   => 'An annual compliance package that brings your statutory filings, meetings and records together in one plan.',
            'who'       => ['Companies and LLPs with recurring annual obligations', 'Businesses switching compliance providers', 'Founders who want one team coordinating filings'],
            'framework' => 'Annual obligations arise under the Companies Act, 2013, the LLP Act, 2008 and applicable tax laws.',
            'why' => [
                ['One annual plan', 'All recurring obligations mapped out at the start of the year.'],
                ['Predictable workload', 'Know what is needed and when, well before each deadline.'],
                ['Single point of contact', 'One team coordinating documentation and filings.'],
            ],
            'requirements' => ['Entity details and prior filing history', 'Financial statements for the year'],
            'documents' => ['Financial statements', 'Board / partner resolutions and minutes', 'Details of any changes during the year'],
            'process' => [
                ['Annual review', 'We map your recurring obligations for the year.'],
                ['Document collection', 'We collect and review what is needed ahead of each deadline.'],
                ['Filings', 'Returns and forms are prepared, approved and filed.'],
                ['Year-end summary', 'You receive a record of everything filed during the year.'],
            ],
            'faqs' => [
                ['Can you take over from my previous provider?', 'Yes. We start with a review of past filings to understand the current position before planning the year ahead.'],
            ],
            'related' => ['roc-compliance', 'company-changes', 'gst-registration', 'company-incorporation'],
            'fees' => null, 'timeline' => null,
        ],
        'company-changes' => [
            'name'      => 'Company Changes',
            'short'     => 'Company Changes',
            'category'  => 'comply',
            'summary'   => 'Make changes to your company — directors, registered office, name, capital or objects — with the right approvals and filings.',
            'who'       => ['Companies adding or removing directors', 'Businesses moving their registered office', 'Companies changing their name, capital or business objects'],
            'framework' => 'Changes are filed with the Registrar of Companies under the Companies Act, 2013 (or the LLP Act, 2008 for LLPs).',
            'why' => [
                ['Done correctly', 'Each change needs the right resolutions and filings in the right order.'],
                ['Records kept current', 'Registry records stay aligned with how your business actually runs.'],
                ['Less disruption', 'We coordinate the paperwork so you can focus on the business.'],
            ],
            'requirements' => ['Details of the change you want to make', 'Approvals from the board / members / partners as required'],
            'documents' => ['Relevant resolutions', 'Supporting documents for the specific change (e.g. new director\'s KYC, new address proof)'],
            'process' => [
                ['Scope the change', 'We confirm the approvals and filings the change requires.'],
                ['Draft resolutions', 'We prepare resolutions and forms for your review.'],
                ['File & update', 'Forms are filed and your records updated once approved.'],
            ],
            'faqs' => [
                ['Which changes can you help with?', 'Common changes include appointing or removing directors, changing the registered office, name, authorised capital or business objects. Speak with us about any other change.'],
            ],
            'related' => ['roc-compliance', 'annual-compliance', 'company-incorporation', 'private-limited-company'],
            'fees' => null, 'timeline' => null,
        ],
    ];
}

/** Look up one service; null if unknown. */
function bs_service(string $slug): ?array
{
    $all = bs_services();
    return isset($all[$slug]) ? ['slug' => $slug] + $all[$slug] : null;
}

/**
 * Regions for "Explore by region". Order matters for display.
 * A region with no jurisdictions yet still renders (with an enquiry
 * prompt) so the browsing model is ready for future additions.
 */
function bs_regions(): array
{
    return [
        'asia'        => 'Asia',
        'europe'      => 'Europe',
        'middle-east' => 'Middle East',
        'caribbean'   => 'Caribbean',
        'americas'    => 'Americas',
        'africa'      => 'Africa',
    ];
}

/**
 * "Business objective" filter for the jurisdiction directory. Each option
 * is defined purely by a verifiable membership/status (EU membership, GCC
 * or ASEAN membership, UK Crown Dependency / Overseas Territory, US
 * territory) — never by tax, cost, speed or regulatory outcomes.
 */
function bs_objectives(): array
{
    return [
        'eu'           => 'Establish in the European Union',
        'gcc'          => 'Establish in the Gulf (GCC member states)',
        'asean'        => 'Establish in Southeast Asia (ASEAN member states)',
        'british'      => 'Establish in a UK Crown Dependency or Overseas Territory',
        'us-territory' => 'Establish in a United States territory',
    ];
}

/**
 * Company-structure filter options: only structures that have been
 * verified and added to a jurisdiction's 'structures' list. Returns an
 * empty list until then, and the directory hides the filter.
 */
function bs_structure_options(): array
{
    $opts = [];
    foreach (bs_jurisdictions() as $j) {
        foreach ($j['structures'] ?? [] as [$title]) {
            $opts[bs_slugify($title)] = $title;
        }
    }
    ksort($opts);
    return $opts;
}

function bs_slugify(string $text): string
{
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-');
}

/**
 * Incorporation jurisdictions.
 *
 * 'descriptor' is a short, neutral geographic/political fact — never a
 * tax, legal, speed or cost claim. 'lat'/'lon' drive the map visual.
 * 'overview', 'structures', 'considerations' default to null/[] and are
 * rendered as a "confirmed during consultation" state until verified
 * jurisdiction content is supplied.
 */
function bs_jurisdictions(): array
{
    return [
        'guernsey' => [
            'name' => 'Guernsey', 'iso' => 'gg', 'regions' => ['europe'], 'popular' => true,
            'groups' => ['british'],
            'descriptor' => 'A self-governing British Crown Dependency in the Channel Islands.',
            'capital' => 'St Peter Port', 'lat' => 49.45, 'lon' => -2.54,
        ],
        'uae' => [
            'name' => 'United Arab Emirates', 'short' => 'UAE', 'iso' => 'ae', 'regions' => ['middle-east'], 'popular' => true,
            'groups' => ['gcc'],
            'descriptor' => 'A major international business and commercial hub.',
            'capital' => 'Abu Dhabi', 'lat' => 24.45, 'lon' => 54.38,
        ],
        'saint-vincent-and-the-grenadines' => [
            'name' => 'Saint Vincent and the Grenadines', 'iso' => 'vc', 'regions' => ['caribbean'], 'popular' => true,
            'descriptor' => 'An island nation in the Eastern Caribbean.',
            'capital' => 'Kingstown', 'lat' => 13.16, 'lon' => -61.23,
        ],
        'saudi-arabia' => [
            'name' => 'Saudi Arabia', 'iso' => 'sa', 'regions' => ['middle-east'], 'popular' => true,
            'groups' => ['gcc'],
            'descriptor' => 'A G20 economy on the Arabian Peninsula.',
            'capital' => 'Riyadh', 'lat' => 24.71, 'lon' => 46.68,
        ],
        'british-virgin-islands' => [
            'name' => 'British Virgin Islands', 'iso' => 'vg', 'regions' => ['caribbean'], 'popular' => true,
            'groups' => ['british'],
            'descriptor' => 'A British Overseas Territory in the Caribbean.',
            'capital' => 'Road Town', 'lat' => 18.43, 'lon' => -64.62,
        ],
        'cayman-islands' => [
            'name' => 'Cayman Islands', 'iso' => 'ky', 'regions' => ['caribbean'], 'popular' => true,
            'groups' => ['british'],
            'descriptor' => 'A British Overseas Territory in the western Caribbean.',
            'capital' => 'George Town', 'lat' => 19.29, 'lon' => -81.38,
        ],
        'singapore' => [
            'name' => 'Singapore', 'iso' => 'sg', 'regions' => ['asia'], 'popular' => true,
            'groups' => ['asean'],
            'descriptor' => 'A city-state and international financial centre in Southeast Asia.',
            'capital' => 'Singapore', 'lat' => 1.35, 'lon' => 103.82,
        ],
        'mauritius' => [
            'name' => 'Mauritius', 'iso' => 'mu', 'regions' => ['africa'], 'popular' => true,
            'descriptor' => 'An island nation in the Indian Ocean, off the coast of East Africa.',
            'capital' => 'Port Louis', 'lat' => -20.16, 'lon' => 57.50,
        ],
        'cyprus' => [
            'name' => 'Cyprus', 'iso' => 'cy', 'regions' => ['europe'], 'popular' => true,
            'groups' => ['eu'],
            'descriptor' => 'A European Union member state in the eastern Mediterranean.',
            'capital' => 'Nicosia', 'lat' => 35.17, 'lon' => 33.36,
        ],
        'hong-kong' => [
            'name' => 'Hong Kong', 'iso' => 'hk', 'regions' => ['asia'], 'popular' => true,
            'descriptor' => 'A Special Administrative Region of China and an international financial centre.',
            'capital' => 'Hong Kong', 'lat' => 22.32, 'lon' => 114.17,
        ],
        'ireland' => [
            'name' => 'Ireland', 'iso' => 'ie', 'regions' => ['europe'], 'popular' => true,
            'groups' => ['eu'],
            'descriptor' => 'An English-speaking European Union member state.',
            'capital' => 'Dublin', 'lat' => 53.35, 'lon' => -6.26,
        ],
        'luxembourg' => [
            'name' => 'Luxembourg', 'iso' => 'lu', 'regions' => ['europe'], 'popular' => true,
            'groups' => ['eu'],
            'descriptor' => 'A founding member of the European Union in Western Europe.',
            'capital' => 'Luxembourg', 'lat' => 49.61, 'lon' => 6.13,
        ],
        'malaysia' => [
            'name' => 'Malaysia', 'iso' => 'my', 'regions' => ['asia'], 'popular' => true,
            'groups' => ['asean'],
            'descriptor' => 'A federal constitutional monarchy in Southeast Asia.',
            'capital' => 'Kuala Lumpur', 'lat' => 3.14, 'lon' => 101.69,
        ],
        'puerto-rico' => [
            'name' => 'Puerto Rico', 'iso' => 'pr', 'regions' => ['caribbean', 'americas'], 'popular' => true,
            'groups' => ['us-territory'],
            'descriptor' => 'A self-governing territory of the United States in the Caribbean.',
            'capital' => 'San Juan', 'lat' => 18.47, 'lon' => -66.11,
        ],
        'united-kingdom' => [
            'name' => 'United Kingdom', 'short' => 'UK', 'iso' => 'gb', 'regions' => ['europe'], 'popular' => true,
            'descriptor' => 'England, Scotland, Wales and Northern Ireland, with London as a global financial centre.',
            'capital' => 'London', 'lat' => 51.51, 'lon' => -0.13,
        ],
    ];
}

/** Look up one jurisdiction; null if unknown. */
function bs_jurisdiction(string $slug): ?array
{
    $all = bs_jurisdictions();
    return isset($all[$slug]) ? ['slug' => $slug] + $all[$slug] : null;
}

/** Popular jurisdictions, in display order. */
function bs_popular_jurisdictions(): array
{
    $out = [];
    foreach (bs_jurisdictions() as $slug => $j) {
        if (!empty($j['popular'])) {
            $out[$slug] = $j;
        }
    }
    return $out;
}

/** Jurisdictions filtered by region, objective, structure and/or free-text query. */
function bs_filter_jurisdictions(string $region = '', string $query = '', string $objective = '', string $structure = ''): array
{
    $query = mb_strtolower(trim($query));
    $out = [];
    foreach (bs_jurisdictions() as $slug => $j) {
        if ($region !== '' && !in_array($region, $j['regions'], true)) {
            continue;
        }
        if ($objective !== '' && !in_array($objective, $j['groups'] ?? [], true)) {
            continue;
        }
        if ($structure !== '' && !in_array($structure, array_map(fn ($st) => bs_slugify($st[0]), $j['structures'] ?? []), true)) {
            continue;
        }
        if ($query !== '' && !str_contains(bs_search_text($j), $query)) {
            continue;
        }
        $out[$slug] = $j;
    }
    return $out;
}

/** Lower-cased text a jurisdiction can be found by (name, short name, capital, region). */
function bs_search_text(array $j): string
{
    $regions = array_map(fn ($r) => bs_regions()[$r] ?? $r, $j['regions']);
    return mb_strtolower(implode(' ', array_filter([
        $j['name'], $j['short'] ?? '', $j['capital'] ?? '', strtoupper($j['iso']), implode(' ', $regions),
    ])));
}

/** Map fractions (0–1) for the dotted world-map crop — must match world-dots.svg bounds. */
function bs_map_position(array $j): array
{
    $top = 84.0; $bottom = -58.0;
    return [
        'x' => round(($j['lon'] + 180) / 360, 4),
        'y' => round(($top - $j['lat']) / ($top - $bottom), 4),
    ];
}

/** Breadcrumb JSON-LD for a trail of [label, path] pairs. */
function bs_breadcrumb_schema(array $trail): array
{
    $items = [];
    foreach (array_values($trail) as $i => [$label, $path]) {
        $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $label, 'item' => site_url($path)];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

/** FAQPage JSON-LD built from [q, a] pairs. */
function bs_faq_schema(array $faqs): array
{
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => [
            '@type' => 'Question', 'name' => $f[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
        ], $faqs),
    ];
}

