<?php
/**
 * POST /api/enquiry/callback — "Request a Callback" from the floating widget.
 * Body (JSON): csrf_token, name, email, phone, company, requirement, message,
 *              context, cf_turnstile_response, company_website (honeypot),
 *              elapsed_ms (time since the form was opened).
 *
 * Every protective check runs here, server-side (includes/anti-spam.php).
 * Responses carry only friendly messages — never provider, database or
 * verification details.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../includes/anti-spam.php';

const CB_MSG_FIELDS  = 'Please check the required fields.';
const CB_MSG_CAPTCHA = 'Please complete the security check and try again.';
const CB_MSG_RETRY   = "We couldn't submit your enquiry right now. Please try again.";
const CB_MSG_LIMIT   = "You've sent several enquiries in a short time. Please try again later, or reach us on WhatsApp, email or phone.";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => CB_MSG_RETRY], 405);
}

$body = json_body();
$s = as_settings();

// Never accept unprotected submissions: if the form is switched off or not
// fully configured, refuse (the widget does not show the form in that case).
if (!as_form_available()) {
    as_log('rejected', 'form_unavailable');
    json_response(['ok' => false, 'error' => CB_MSG_RETRY], 503);
}

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

// 1. Honeypot + minimum fill time: discard silently with a success-shaped
//    response, so bots learn nothing.
$honeypot = trim((string) ($body['company_website'] ?? ''));
$elapsed = (int) ($body['elapsed_ms'] ?? 0);
if ($s['honeypot_enabled'] === '1' && ($honeypot !== '' || $elapsed < (int) $s['min_fill_ms'])) {
    as_log('discarded', $honeypot !== '' ? 'honeypot' : 'too_fast');
    json_response(['ok' => true, 'enquiry_code' => null]);
}

// 2. Cheap rate limits before any outbound call.
$ip = client_ip();
if (!as_rate_hit('ip', $ip, (int) $s['rl_ip_max'], (int) $s['rl_ip_window'])
    || !as_rate_hit('session', session_id() ?: $ip, (int) $s['rl_session_max'], (int) $s['rl_session_window'])) {
    as_log('rate_limited', 'ip_or_session');
    json_response(['ok' => false, 'error' => CB_MSG_LIMIT], 429);
}

// 3. Validation.
[$f, $errors] = as_validate($body);
if ($errors) {
    json_response(['ok' => false, 'error' => CB_MSG_FIELDS, 'fields' => $errors], 422);
}

// 4. CAPTCHA, verified with Cloudflare.
$captcha = as_verify_turnstile((string) ($body['cf_turnstile_response'] ?? ''), $ip);
if ($captcha === 'failed') {
    as_log('captcha_failed');
    json_response(['ok' => false, 'error' => CB_MSG_CAPTCHA, 'captcha' => 'retry'], 403);
}
if ($captcha === 'unavailable') {
    // Provider outage: stay protected (honeypot, validation, limits already
    // applied) and add a much tighter per-IP limit. Recorded for admins.
    as_log('captcha_unavailable');
    if (!as_rate_hit('fallback_ip', $ip, (int) $s['rl_fallback_max'], (int) $s['rl_fallback_window'])) {
        json_response(['ok' => false, 'error' => CB_MSG_RETRY], 429);
    }
}

// 5. Per-contact limits and duplicate guard.
if (!as_rate_hit('email', $f['email'], (int) $s['rl_email_max'], (int) $s['rl_email_window'])
    || !as_rate_hit('phone', $f['phone'], (int) $s['rl_phone_max'], (int) $s['rl_phone_window'])) {
    as_log('rate_limited', 'email_or_phone');
    json_response(['ok' => false, 'error' => CB_MSG_LIMIT], 429);
}
if (!as_rate_hit('duplicate', $f['email'] . '|' . $f['phone'] . '|' . $f['requirement'] . '|' . $f['message'], 1, 1800)) {
    json_response(['ok' => false, 'error' => "We've already received this enquiry — our team will be in touch."], 409);
}

// 6. Store.
$requirements = as_requirements();
$reqLabel = $requirements[$f['requirement']] ?? 'Not specified';
$context = preg_replace('/[^a-zA-Z]/', '', (string) ($body['context'] ?? '')) ?: 'default';
$subject = 'Callback request — ' . $reqLabel;
$message = "Callback requested from the floating enquiry widget.\n"
    . "Requirement: {$reqLabel}\n"
    . 'Message: ' . ($f['message'] !== '' ? $f['message'] : '(none)');

$pdo = null;
try {
    $pdo = db();
    $pdo->beginTransaction();
    $code = generate_enquiry_code($pdo);
    $pdo->prepare(
        'INSERT INTO enquiries (enquiry_code, type, name, company, email, mobile, subject, message, ip_address)
         VALUES (:code, :type, :name, :company, :email, :mobile, :subject, :message, :ip)'
    )->execute([
        'code' => $code, 'type' => 'sales', 'name' => $f['name'], 'company' => $f['company'] ?: null,
        'email' => $f['email'], 'mobile' => $f['phone'], 'subject' => $subject, 'message' => $message, 'ip' => $ip,
    ]);
    $enquiryId = (int) $pdo->lastInsertId();
    $pdo->prepare(
        'INSERT INTO contact_submissions (enquiry_id, form_type, payload_json, ip_address) VALUES (:eid, :type, :payload, :ip)'
    )->execute([
        'eid' => $enquiryId, 'type' => 'floating_callback', 'ip' => $ip,
        'payload' => json_encode($f + ['context' => $context, 'captcha' => $captcha === 'ok' ? 'verified' : 'provider_unavailable'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[Paynancial] Callback enquiry failed: ' . get_class($e));
    as_log('store_failed');
    json_response(['ok' => false, 'error' => CB_MSG_RETRY], 500);
}

// 7. Notify. Header values are fixed or validated (no CR/LF can reach them).
$to = as_notify_to();
if ($to !== '') {
    $flag = $captcha === 'ok' ? '' : ' [security check unavailable — review]';
    $mailBody = "Enquiry ID: {$code}\nName: {$f['name']}\nCompany: {$f['company']}\nEmail: {$f['email']}\nPhone: {$f['phone']}\n"
        . "Requirement: {$reqLabel}\nPage context: {$context}\n\nMessage:\n" . ($f['message'] !== '' ? $f['message'] : '(none)');
    $headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . ">\r\nReply-To: " . $f['email'];
    @mail($to, "Callback request — {$code}{$flag}", $mailBody, $headers);
}

json_response(['ok' => true, 'enquiry_code' => $code]);
