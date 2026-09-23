<?php
/**
 * POST /api/partner/recommend
 * Body: { csrf_token, customer_type, requirements: [], no_website, is_international, is_enterprise }
 * Returns solution recommendations for the in-progress customer enrollment
 * wizard (step 6), before anything is saved to the database.
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

if (!current_partner_context()) {
    json_response(['ok' => false, 'error' => 'Not authorized'], 403);
}

$body = json_body();

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

$attributes = [
    'customer_type'    => sanitize_input((string) ($body['customer_type'] ?? '')),
    'requirements'     => array_map('sanitize_input', array_filter((array) ($body['requirements'] ?? []), 'is_string')),
    'no_website'       => !empty($body['no_website']),
    'is_international' => !empty($body['is_international']),
    'is_enterprise'    => $body['customer_type'] === 'enterprise',
];

$results = recommend_products_for_customer(db(), $attributes);

$payload = array_map(static function (array $r): array {
    $p = $r['product'];
    return [
        'id'                  => (int) $p['id'],
        'name'                => $p['name'],
        'category'            => $p['category'],
        'short_description'   => $p['short_description'],
        'complexity'          => $p['complexity'],
        'pricing_status'      => $p['pricing_status'],
        'commission_eligible' => (bool) $p['commission_eligible'],
        'reasons'             => array_values(array_unique($r['reasons'])),
    ];
}, $results);

json_response(['ok' => true, 'recommendations' => $payload]);
