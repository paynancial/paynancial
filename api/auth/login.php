<?php
/**
 * POST /api/auth/login
 * Body: { csrf_token, login_type, identifier, password, remember }
 * Used by the left-side login panel (AJAX, no page reload).
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = json_body();

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

if (!rate_limit('login_' . client_ip(), 20, 300)) {
    json_response(['ok' => false, 'error' => 'Too many attempts. Please wait a few minutes and try again.'], 429);
}

$identifier = sanitize_input((string) ($body['identifier'] ?? ''));
$password   = (string) ($body['password'] ?? '');
$loginType  = sanitize_input((string) ($body['login_type'] ?? ''));

if ($identifier === '' || $password === '' || !in_array($loginType, ['customer', 'partner', 'employee', 'hr'], true)) {
    json_response(['ok' => false, 'error' => 'Please fill in all required fields.'], 422);
}

$result = attempt_login(db(), $identifier, $password, $loginType);

if (!$result['ok']) {
    json_response(['ok' => false, 'error' => $result['error']], 401);
}

if (!empty($result['otp_required'])) {
    json_response(['ok' => true, 'otp_required' => true, 'destination_masked' => $result['destination_masked']]);
}

json_response(['ok' => true, 'redirect' => $result['redirect']]);
