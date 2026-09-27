<?php
/**
 * Staging fixtures for the Phase 1 gate scripts — STAGING / DISPOSABLE COPY ONLY.
 *
 *   STAGING_FIXTURES=1 php tests/staging/fixtures.php <code-root> base      # before migrations (users, portal rows, enquiries, partner application)
 *   STAGING_FIXTURES=1 php tests/staging/fixtures.php <code-root> cms       # after migrations (approved, unpublished CMS article)
 *   STAGING_FIXTURES=1 php tests/staging/fixtures.php <code-root> cleanup   # removes every fixture
 *
 * Idempotent. Every fixture is marked (@stg.invalid, *-STG-*, staging-*) so
 * cleanup never touches real records. Fixture accounts get a random,
 * never-disclosed password (tests use pre-authenticated sessions or their
 * own OTP flow). Refuses APP_ENV=production.
 */
declare(strict_types=1);

[$_, $root, $mode] = $argv + [null, null, null];
if (PHP_SAPI !== 'cli' || !$root || !in_array($mode, ['base', 'cms', 'cleanup'], true)) {
    fwrite(STDERR, "usage: STAGING_FIXTURES=1 php fixtures.php <code-root> base|cms|cleanup\n");
    exit(2);
}
require "$root/config/config.php";
if (APP_ENV === 'production' || getenv('STAGING_FIXTURES') !== '1') {
    fwrite(STDERR, "Refusing to run: staging only (set STAGING_FIXTURES=1, APP_ENV must not be production).\n");
    exit(2);
}
require "$root/includes/database.php";
$pdo = db();
$roleId = fn (string $slug) => (int) $pdo->query('SELECT id FROM roles WHERE slug = ' . $pdo->quote($slug))->fetchColumn();
$userId = function (string $email) use ($pdo): int {
    $s = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $s->execute([$email]);
    return (int) $s->fetchColumn();
};
$ensureUser = function (string $email, string $name, string $role) use ($pdo, $roleId, $userId): int {
    if ($id = $userId($email)) {
        return $id;
    }
    $pdo->prepare("INSERT INTO users (uuid, role_id, full_name, email, password_hash, status) VALUES (UUID(), ?, ?, ?, ?, 'active')")
        ->execute([$roleId($role), $name, $email, password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT)]);
    return (int) $pdo->lastInsertId();
};
$exists = function (string $sql, array $p = []) use ($pdo): bool {
    $s = $pdo->prepare($sql);
    $s->execute($p);
    return $s->fetchColumn() !== false;
};

if ($mode === 'base') {
    $ensureUser('stg-super@stg.invalid', 'Staging Super Admin', 'super_admin');
    $ensureUser('admin1@stg.invalid', 'Staging Admin One', 'admin');
    $ensureUser('admin2@stg.invalid', 'Staging Admin Two', 'admin');
    $cust = $ensureUser('customer@stg.invalid', 'Staging Customer', 'customer');
    $partner = $ensureUser('partner@stg.invalid', 'Staging Partner', 'partner');
    $emp = $ensureUser('employee@stg.invalid', 'Staging Employee', 'employee');
    $ensureUser('hr@stg.invalid', 'Staging HR', 'hr');
    if (!$exists('SELECT 1 FROM customers WHERE customer_code = ? OR user_id = ?', ['PYN-CUS-STG-1', $cust])) {
        $pdo->prepare("INSERT INTO customers (user_id, customer_code, company_name, kyc_status) VALUES (?, 'PYN-CUS-STG-1', 'Staging Customer Pvt Ltd', 'pending')")->execute([$cust]);
    }
    if (!$exists('SELECT 1 FROM partners WHERE partner_code = ? OR user_id = ?', ['PYN-PARTNER-STG-1', $partner])) {
        $pdo->prepare("INSERT INTO partners (user_id, partner_code, business_name, partner_type) VALUES (?, 'PYN-PARTNER-STG-1', 'Staging Partner LLP', 'referral')")->execute([$partner]);
    }
    if (!$exists('SELECT 1 FROM employees WHERE employee_code = ? OR user_id = ?', ['EMP-STG-1', $emp])) {
        $pdo->prepare("INSERT INTO employees (user_id, employee_code, department, designation, joining_date) VALUES (?, 'EMP-STG-1', 'QA', 'Staging', CURDATE())")->execute([$emp]);
    }
    foreach ([
        ['PAY-ENQ-STG-RBAC', 'sales', 'Staging Rbac Fixture', null, 'Fixture for rbac_matrix.py'],
        ['PAY-ENQ-STG-XSS', 'general', 'Test <script>alert(1)</script>', '"><img src=x onerror=alert(2)>', '<b>XSS</b> probe'],
    ] as [$code, $type, $name, $company, $subject]) {
        if (!$exists('SELECT 1 FROM enquiries WHERE enquiry_code = ?', [$code])) {
            $pdo->prepare("INSERT INTO enquiries (enquiry_code, type, name, company, email, subject, message, status) VALUES (?, ?, ?, ?, 'fixture@stg.invalid', ?, 'fixture', 'new')")
                ->execute([$code, $type, $name, $company, $subject]);
        }
    }
    if (!$exists('SELECT 1 FROM partner_applications WHERE application_code = ?', ['PYN-PARTNER-APP-STG'])) {
        $pdo->prepare("INSERT INTO partner_applications (application_code, partner_type, business_name, contact_person, email, mobile, status)
                       VALUES ('PYN-PARTNER-APP-STG', 'referral', 'Staging Applicant', 'Fixture', 'apply@stg.invalid', '9000000099', 'submitted')")->execute();
    }
    if (!$exists('SELECT 1 FROM products LIMIT 1')) {
        $pdo->exec("INSERT INTO products (slug, name, category, short_description) VALUES ('stg-fixture-product', 'Staging Fixture Product', 'fixture', 'fixture')");
    }
    echo "base fixtures ready\n";
} elseif ($mode === 'cms') {
    if (!$exists("SELECT 1 FROM blog_posts WHERE slug = 'staging-approved-article'")) {
        $content = json_encode(['dek' => 'Staging fixture', 'question' => 'What is this?', 'answer' => 'A staging fixture.', 'takeaways' => [],
            'sections' => [['section', 'Section', '<p>Staging fixture body.</p>']], 'faqs' => [], 'related' => [], 'links' => []]);
        $pdo->prepare("INSERT INTO blog_posts (slug, title, category, excerpt, body_html, content_json, meta_description, status, live, submitted_by, submitted_at, approved_by, approved_at)
                       VALUES ('staging-approved-article', 'Staging approved article (fixture)', 'payments', 'Staging fixture', '<h2 id=\"section\">Section</h2><p>Staging fixture body.</p>',
                               ?, 'Staging fixture description.', 'approved', 0, ?, NOW(), ?, NOW())")
            ->execute([$content, $userId('stg-super@stg.invalid'), $userId('stg-super@stg.invalid')]);
    }
    echo "cms fixtures ready\n";
} else {
    $pdo->exec("DELETE FROM blog_posts WHERE slug IN ('staging-approved-article', 'staging-legal-review')");
    $pdo->exec("DELETE FROM contact_submissions WHERE enquiry_id IN (SELECT id FROM enquiries WHERE enquiry_code LIKE 'PAY-ENQ-STG-%')");
    $pdo->exec("DELETE FROM enquiries WHERE enquiry_code LIKE 'PAY-ENQ-STG-%' OR email LIKE '%@stg.invalid'");
    $pdo->exec("DELETE FROM partner_applications WHERE application_code = 'PYN-PARTNER-APP-STG'");
    $pdo->exec("DELETE FROM products WHERE slug = 'stg-fixture-product'");
    $pdo->exec("DELETE FROM customers WHERE customer_code = 'PYN-CUS-STG-1' OR contact_email LIKE '%@stg.invalid'");
    $pdo->exec("DELETE FROM partners WHERE partner_code = 'PYN-PARTNER-STG-1'");
    $pdo->exec("DELETE FROM employees WHERE employee_code = 'EMP-STG-1'");
    $pdo->exec("DELETE FROM users WHERE email LIKE '%@stg.invalid'");
    echo "fixtures removed (audit_logs rows are kept as evidence)\n";
}
