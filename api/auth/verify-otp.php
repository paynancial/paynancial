<?php
/**
 * POST /api/auth/verify-otp
 * Body: { csrf_token, otp }
 * Completes a login that attempt_login() flagged as otp_required. The
 * pending challenge (which user, which otp_verifications row) lives in
 * the session set by the /api/auth/login call — never trust a user id
 * from the request body here.
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = json_body();

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

if (!rate_limit('verify_otp_' . client_ip(), 10, 300)) {
    json_response(['ok' => false, 'error' => 'Too many attempts. Please wait a few minutes and try again.'], 429);
}

$code = sanitize_input((string) ($body['otp'] ?? ''));
if ($code === '' || !preg_match('/^\d{' . OTP_LENGTH . '}$/', $code)) {
    json_response(['ok' => false, 'error' => 'Enter the ' . OTP_LENGTH . '-digit code from your email.'], 422);
}

$result = complete_otp_login(db(), $code);

if (!$result['ok']) {
    json_response(['ok' => false, 'error' => $result['error']], 401);
}

json_response(['ok' => true, 'redirect' => $result['redirect']]);
