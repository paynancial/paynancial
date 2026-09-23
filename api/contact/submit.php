<?php
/**
 * POST /api/contact/submit
 * Body: { csrf_token, type, name, company, email, mobile, subject, message }
 * Stores the enquiry, emails sales, and returns a generated enquiry code
 * (e.g. PAY-ENQ-2026-000001).
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = json_body();

if (!csrf_verify($body['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

if (!rate_limit('contact_submit_' . client_ip(), 10, 600)) {
    json_response(['ok' => false, 'error' => 'Too many submissions. Please try again later.'], 429);
}

$type = sanitize_input((string) ($body['type'] ?? 'general'));
$allowedTypes = ['sales', 'partner', 'support', 'general', 'career'];
if (!in_array($type, $allowedTypes, true)) {
    $type = 'general';
}

$name    = sanitize_input((string) ($body['name'] ?? ''));
$company = sanitize_input((string) ($body['company'] ?? ''));
$email   = sanitize_input((string) ($body['email'] ?? ''));
$mobile  = sanitize_input((string) ($body['mobile'] ?? ''));
$subject = sanitize_input((string) ($body['subject'] ?? ''));
$message = sanitize_input((string) ($body['message'] ?? ''));

if ($name === '' || $email === '' || $message === '') {
    json_response(['ok' => false, 'error' => 'Please fill in your name, email and message.'], 422);
}
if (!is_valid_email($email)) {
    json_response(['ok' => false, 'error' => 'Please enter a valid email address.'], 422);
}
if ($mobile !== '' && !is_valid_mobile($mobile)) {
    json_response(['ok' => false, 'error' => 'Please enter a valid mobile number.'], 422);
}

$pdo = db();
$pdo->beginTransaction();

try {
    $enquiryCode = generate_enquiry_code($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO enquiries (enquiry_code, type, name, company, email, mobile, subject, message, ip_address)
         VALUES (:code, :type, :name, :company, :email, :mobile, :subject, :message, :ip)'
    );
    $stmt->execute([
        'code'    => $enquiryCode,
        'type'    => $type,
        'name'    => $name,
        'company' => $company ?: null,
        'email'   => $email,
        'mobile'  => $mobile ?: null,
        'subject' => $subject ?: null,
        'message' => $message,
        'ip'      => client_ip(),
    ]);
    $enquiryId = (int) $pdo->lastInsertId();

    $logStmt = $pdo->prepare(
        'INSERT INTO contact_submissions (enquiry_id, form_type, payload_json, ip_address)
         VALUES (:eid, :type, :payload, :ip)'
    );
    $logStmt->execute([
        'eid'     => $enquiryId,
        'type'    => $type,
        'payload' => json_encode(compact('name', 'company', 'email', 'mobile', 'subject', 'message'), JSON_UNESCAPED_SLASHES),
        'ip'      => client_ip(),
    ]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('[Paynancial] Enquiry submission failed: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Something went wrong. Please try again.'], 500);
}

$mailSubject = "New {$type} enquiry — {$enquiryCode}";
$mailBody = "Enquiry ID: {$enquiryCode}\nType: {$type}\nName: {$name}\nCompany: {$company}\nEmail: {$email}\nMobile: {$mobile}\nSubject: {$subject}\n\nMessage:\n{$message}";
$headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . ">\r\nReply-To: " . $email;
@mail(MAIL_SALES_TO, $mailSubject, $mailBody, $headers);

json_response(['ok' => true, 'enquiry_code' => $enquiryCode]);
