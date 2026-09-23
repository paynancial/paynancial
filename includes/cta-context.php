<?php
/**
 * Page-context-aware CTA wording — the single source for contextual
 * sales/contact labels. Used by the floating enquiry widget, the footer
 * brand strip and secondary page CTAs, so a page never says "Talk to
 * Payment Experts" in one place and "Talk to an Incorporation Expert" in
 * another.
 *
 * Contexts: payments, businessServices, incorporation, jurisdiction,
 * pricing, developers, company, default. Resolution is by URL path (see
 * cta_context_key()); unknown pages fall back to 'default'.
 *
 * Wording is service-focused and neutral — never a legal, tax or
 * regulatory recommendation.
 */

declare(strict_types=1);

require_once __DIR__ . '/business-services.php';

function cta_contexts(): array
{
    return [
        'payments' => [
            'label'   => 'Talk to Payment Experts',
            'support' => 'Discuss your payment and financial infrastructure requirements.',
            'area'    => 'Payments',
            'subject' => 'Paynancial Payments Enquiry',
            'message' => "Hi Paynancial, I'd like to discuss my payment requirements.",
        ],
        'businessServices' => [
            'label'   => 'Talk to a Business Expert',
            'support' => 'Get guidance for your business requirements.',
            'area'    => 'Business Services',
            'subject' => 'Paynancial Business Services Enquiry',
            'message' => "Hi Paynancial, I'd like guidance on business services.",
        ],
        'incorporation' => [
            'label'   => 'Talk to an Incorporation Expert',
            'support' => 'Discuss your incorporation requirements with our team.',
            'area'    => 'Company Incorporation',
            'subject' => 'Paynancial Business Services — Company Incorporation Enquiry',
            'message' => "Hi Paynancial, I'd like to discuss my incorporation requirements.",
        ],
        'jurisdiction' => [
            'label'   => 'Talk to an Incorporation Expert',
            'support' => 'Discuss your {place} incorporation requirements.',
            'area'    => 'International Incorporation',
            'subject' => 'Paynancial Business Services — Incorporation in {place}',
            'message' => "Hi Paynancial, I'd like to discuss my {place} incorporation requirements.",
        ],
        'pricing' => [
            'label'   => 'Talk to Sales',
            'support' => 'Discuss your requirements and pricing.',
            'area'    => 'Pricing',
            'subject' => 'Paynancial Sales Enquiry — Pricing',
            'message' => "Hi Paynancial, I'd like to discuss my requirements and pricing.",
        ],
        'developers' => [
            'label'   => 'Talk to Technical Experts',
            'support' => 'Discuss integration and technical requirements.',
            'area'    => 'Developers',
            'subject' => 'Paynancial Technical Enquiry',
            'message' => "Hi Paynancial, I'd like to discuss integration and technical requirements.",
        ],
        'company' => [
            'label'   => 'Contact Paynancial',
            'support' => 'Reach the Paynancial team on the channel that suits you.',
            'area'    => 'Paynancial',
            'subject' => 'Paynancial Enquiry',
            'message' => "Hi Paynancial, I'd like to get in touch.",
        ],
        'default' => [
            'label'   => 'Talk to Our Experts',
            'support' => 'Tell us what you need and the right team will help.',
            'area'    => 'Paynancial',
            'subject' => 'Paynancial Enquiry',
            'message' => "Hi Paynancial, I'd like to talk to your team.",
        ],
    ];
}

/** Which context a URL path belongs to. */
function cta_context_key(string $path): string
{
    $path = '/' . trim((string) (parse_url($path, PHP_URL_PATH) ?? '/'), '/');
    $seg = $path === '/' ? [] : explode('/', ltrim($path, '/'));
    $first = $seg[0] ?? '';

    if ($first === 'business-services') {
        $second = $seg[1] ?? '';
        if ($second === 'jurisdictions') {
            return isset($seg[2]) && bs_jurisdiction($seg[2]) ? 'jurisdiction' : 'incorporation';
        }
        if ($second === 'global-incorporation') {
            return 'incorporation';
        }
        $service = $second !== '' ? bs_service($second) : null;
        // "Start a Business" services are incorporation; registrations,
        // trademarks and compliance are general business services.
        return $service && $service['category'] === 'start' ? 'incorporation' : 'businessServices';
    }

    $map = [
        ''            => 'payments',   // homepage: payment infrastructure
        'products'    => 'payments',
        'solutions'   => 'payments',
        'ai-intelligence' => 'payments',
        'pay-and-move-money' => 'payments',
        'financial-operations' => 'payments',
        'embedded-finance' => 'payments',
        'pricing'     => 'pricing',
        'developers'  => 'developers',
        'about'       => 'company',
        'leadership'  => 'company',
        'careers'     => 'company',
        'partners'    => 'company',
        'partner'     => 'company',
    ];
    return $map[$first] ?? 'default';
}

/** Resolved context (with {place} filled for jurisdiction pages). */
function cta_context(string $path): array
{
    $key = cta_context_key($path);
    $ctx = ['key' => $key] + cta_contexts()[$key];
    if ($key === 'jurisdiction') {
        $seg = explode('/', trim((string) parse_url($path, PHP_URL_PATH), '/'));
        $j = bs_jurisdiction($seg[2] ?? '');
        $place = $j ? ($j['short'] ?? $j['name']) : 'international';
        foreach (['support', 'subject', 'message'] as $f) {
            $ctx[$f] = str_replace('{place}', $place, $ctx[$f]);
        }
    }
    return $ctx;
}

/** Context for the current request. */
function cta_current(): array
{
    static $cache = null;
    return $cache ??= cta_context((string) ($GLOBALS['current_path'] ?? ($_SERVER['REQUEST_URI'] ?? '/')));
}

/** Contextual CTA label for the current request, e.g. "Talk to Payment Experts". */
function cta_label(): string
{
    return cta_current()['label'];
}
