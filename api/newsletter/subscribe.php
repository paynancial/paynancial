<?php
/**
 * POST /api/newsletter/subscribe
 * Body: { csrf_token, email }
 * Stores an email in newsletter_subscribers. Re-subscribing with the
 * same email is a no-op (unique on email), not an error.
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = json_body();

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

if (!rate_limit('newsletter_subscribe_' . client_ip(), 5, 600)) {
    json_response(['ok' => false, 'error' => 'Too many attempts. Please try again later.'], 429);
}

$email = sanitize_input((string) ($body['email'] ?? ''));

if ($email === '' || !is_valid_email($email)) {
    json_response(['ok' => false, 'error' => 'Please enter a valid email address.'], 422);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO newsletter_subscribers (email, source, ip_address) VALUES (:email, :source, :ip)
         ON DUPLICATE KEY UPDATE email = email'
    );
    $stmt->execute([
        'email'  => $email,
        'source' => 'footer',
        'ip'     => client_ip(),
    ]);
} catch (Throwable $e) {
    error_log('[Paynancial] Newsletter subscribe failed: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Something went wrong. Please try again.'], 500);
}

json_response(['ok' => true]);
