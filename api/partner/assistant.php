<?php
/**
 * POST /api/partner/assistant
 * Body: { csrf_token, query }
 * A rules-based Q&A endpoint scoped strictly to the logged-in partner's
 * own data — see includes/partner-assistant.php for the full disclosure
 * on what this is (and is not).
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$context = current_partner_context();
if (!$context) {
    json_response(['ok' => false, 'error' => 'Not authorized'], 403);
}

$body = json_body();

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

if (!rate_limit('partner_assistant_' . $context['partner_id'], 30, 60)) {
    json_response(['ok' => false, 'error' => 'Too many questions at once. Please slow down a little.'], 429);
}

$query = sanitize_input((string) ($body['query'] ?? ''));
if (mb_strlen($query) > 300) {
    json_response(['ok' => false, 'error' => 'That question is too long.'], 422);
}

$result = partner_assistant_answer(db(), $context, $query);

json_response(['ok' => true, 'answer' => $result['answer']]);
