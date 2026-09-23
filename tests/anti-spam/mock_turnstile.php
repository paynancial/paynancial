<?php
/**
 * Mock Cloudflare Turnstile siteverify — LOCAL TESTING ONLY.
 * Run:  php -S 127.0.0.1:8107 tests/anti-spam/mock_turnstile.php
 * The app uses it only when APP_ENV is not 'production' and the server
 * environment sets TURNSTILE_VERIFY_URL=http://127.0.0.1:8107/verify.
 *
 * Token semantics (mirrors Cloudflare's responses):
 *   pass-*  → success, single use (reuse → timeout-or-duplicate)
 *   down-*  → HTTP 503 (provider unavailable)
 *   other   → invalid-input-response
 * Secret must be "test-secret-local".
 */
$state = getenv('MOCK_STATE') ?: sys_get_temp_dir() . '/pyn-mock-turnstile-used.json';
$used = is_file($state) ? (json_decode((string) file_get_contents($state), true) ?: []) : [];
$token = (string) ($_POST['response'] ?? '');
header('Content-Type: application/json');
if (($_POST['secret'] ?? '') !== 'test-secret-local') {
    echo json_encode(['success' => false, 'error-codes' => ['invalid-input-secret']]);
    exit;
}
if (str_starts_with($token, 'down-')) {
    http_response_code(503);
    echo 'unavailable';
    exit;
}
if (str_starts_with($token, 'pass-')) {
    if (in_array($token, $used, true)) {
        echo json_encode(['success' => false, 'error-codes' => ['timeout-or-duplicate']]);
        exit;
    }
    $used[] = $token;
    file_put_contents($state, json_encode($used));
    echo json_encode(['success' => true, 'hostname' => 'localhost', 'action' => 'floating_callback']);
    exit;
}
echo json_encode(['success' => false, 'error-codes' => ['invalid-input-response']]);
