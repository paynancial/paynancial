<?php
/**
 * POST /api/auth/resend-otp
 * Body: { csrf_token }
 * Resends the code for a login OTP challenge already in progress.
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = json_body();

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

if (!rate_limit('resend_otp_' . client_ip(), 5, 300)) {
    json_response(['ok' => false, 'error' => 'Too many requests. Please wait a few minutes and try again.'], 429);
}

$result = resend_login_otp(db());

if (!$result['ok']) {
    json_response(['ok' => false, 'error' => $result['error']], 422);
}

json_response(['ok' => true, 'destination_masked' => $result['destination_masked']]);
