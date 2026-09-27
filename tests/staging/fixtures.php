<?php
/**
 * Staging fixtures for the Phase 1 gate: STAGING or a DISPOSABLE COPY ONLY.
 *
 *   STAGING_FIXTURES=1 php tests/staging/fixtures.php <code-root> base         # after BOTH migrations: accounts for all 12 staff roles, portal accounts, portal rows, enquiries, KYC document row, partner application, product
 *   STAGING_FIXTURES=1 php tests/staging/fixtures.php <code-root> cms          # after migrations: approved, unpublished CMS article
 *   STAGING_FIXTURES=1 php tests/staging/fixtures.php <code-root> verify       # every fixture present (exit 1 if not)
 *   STAGING_FIXTURES=1 php tests/staging/fixtures.php <code-root> cleanup      # removes every fixture and every test-created record
 *   STAGING_FIXTURES=1 php tests/staging/fixtures.php <code-root> check-clean  # nothing left (exit 1 if anything remains)
 *
 * Idempotent, reversible and identifiable. Every fixture or test-created
 * record carries @stg.invalid (emails), -STG- (codes), "STG Fixture" (names),
 * stg-fixture (product slug, document type) or staging-* (CMS slugs), so
 * cleanup never touches real records. Nothing looks like a real customer.
 *
 * Fixture accounts get a random, never-disclosed password: the automated
 * tests set their own. Fixtures are temporary: the gate removes them before
 * the AFTER snapshot, and also when it stops early. It never creates them
 * before the BEFORE snapshot.
 *
 * Refuses APP_ENV=production. Needs no rehearsal-only data.
 */
declare(strict_types=1);

[$_, $root, $mode] = $argv + [null, null, null];
if (PHP_SAPI !== 'cli' || !$root || !in_array($mode, ['base', 'cms', 'verify', 'cleanup', 'check-clean'], true)) {
    fwrite(STDERR, "usage: STAGING_FIXTURES=1 php fixtures.php <code-root> base|cms|verify|cleanup|check-clean\n");
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

const STG_STAFF_ROLES = ['super_admin', 'admin', 'operations_manager', 'sales_manager', 'consultant', 'incorporation_consultant',
                         'content_manager', 'seo_manager', 'compliance_reviewer', 'finance_manager', 'developer', 'support'];
const STG_CLEAN_CHECKS = [   // what must be gone after cleanup (label => query returning a count)
    'fixture or test accounts (@stg.invalid)' => "SELECT COUNT(*) FROM users WHERE email LIKE '%@stg.invalid'",
    'staging test enquiries'                   => "SELECT COUNT(*) FROM enquiries WHERE enquiry_code LIKE 'PAY-ENQ-STG-%' OR enquiry_code = 'PAY-ENQ-CSV-1' OR email LIKE '%@stg.invalid'",
    'staging-only customers'                   => "SELECT COUNT(*) FROM customers WHERE customer_code LIKE '%-STG-%' OR contact_email LIKE '%@stg.invalid'",
    'staging partners'                         => "SELECT COUNT(*) FROM partners WHERE partner_code LIKE '%-STG-%'",
    'staging employees'                        => "SELECT COUNT(*) FROM employees WHERE employee_code LIKE '%-STG-%'",
    'staging partner applications'             => "SELECT COUNT(*) FROM partner_applications WHERE application_code LIKE '%-STG%' OR email LIKE '%@stg.invalid'",
    'staging KYC document rows'                => "SELECT COUNT(*) FROM customer_kyc_documents WHERE doc_type = 'stg-fixture'",
    'staging CMS articles'                     => "SELECT COUNT(*) FROM blog_posts WHERE slug IN ('staging-approved-article', 'staging-legal-review')",
    'staging product'                          => "SELECT COUNT(*) FROM products WHERE slug = 'stg-fixture-product'",
    'test login attempts'                      => "SELECT COUNT(*) FROM login_attempts WHERE identifier LIKE '%@stg.invalid'",
];

if ($mode === 'base') {
    $missing = array_diff(STG_STAFF_ROLES, $pdo->query('SELECT slug FROM roles')->fetchAll(PDO::FETCH_COLUMN));
    if ($missing) {
        fwrite(STDERR, 'Roles missing (run the Phase 1 migration first): ' . implode(', ', $missing) . "\n");
        exit(1);
    }
    foreach (STG_STAFF_ROLES as $r) {   // one account per staff role for the RBAC matrix
        $ensureUser("rbac-$r@stg.invalid", "STG Fixture $r", $r);
    }
    $ensureUser('stg-super@stg.invalid', 'STG Fixture Super Admin', 'super_admin');
    $ensureUser('admin1@stg.invalid', 'STG Fixture Admin One', 'admin');
    $ensureUser('admin2@stg.invalid', 'STG Fixture Admin Two', 'admin');
    $cust = $ensureUser('customer@stg.invalid', 'STG Fixture Customer', 'customer');
    $partner = $ensureUser('partner@stg.invalid', 'STG Fixture Partner', 'partner');
    $emp = $ensureUser('employee@stg.invalid', 'STG Fixture Employee', 'employee');
    $ensureUser('hr@stg.invalid', 'STG Fixture HR', 'hr');
    if (!$exists('SELECT 1 FROM customers WHERE customer_code = ? OR user_id = ?', ['PYN-CUS-STG-1', $cust])) {
        $pdo->prepare("INSERT INTO customers (user_id, customer_code, company_name, kyc_status) VALUES (?, 'PYN-CUS-STG-1', 'STG Fixture Customer (test)', 'pending')")->execute([$cust]);
    }
    $custId = (int) $pdo->query("SELECT id FROM customers WHERE user_id = $cust")->fetchColumn();   // the fixture account's customer row
    if ($custId && !$exists("SELECT 1 FROM customer_kyc_documents WHERE doc_type = 'stg-fixture'")) {   // document-view tests (metadata only, no file)
        $pdo->prepare("INSERT INTO customer_kyc_documents (customer_id, doc_type, file_path, status) VALUES (?, 'stg-fixture', 'stg-fixture/no-file.pdf', 'under_review')")->execute([$custId]);
    }
    if (!$exists('SELECT 1 FROM partners WHERE partner_code = ? OR user_id = ?', ['PYN-PARTNER-STG-1', $partner])) {
        $pdo->prepare("INSERT INTO partners (user_id, partner_code, business_name, partner_type) VALUES (?, 'PYN-PARTNER-STG-1', 'STG Fixture Partner (test)', 'referral')")->execute([$partner]);
    }
    if (!$exists('SELECT 1 FROM employees WHERE employee_code = ? OR user_id = ?', ['EMP-STG-1', $emp])) {
        $pdo->prepare("INSERT INTO employees (user_id, employee_code, department, designation, joining_date) VALUES (?, 'EMP-STG-1', 'QA', 'Staging', CURDATE())")->execute([$emp]);
    }
    foreach ([
        ['PAY-ENQ-STG-RBAC', 'sales', 'STG Fixture Rbac', null, 'Fixture for rbac_matrix.py'],
        ['PAY-ENQ-STG-XSS', 'general', 'Test <script>alert(1)</script>', '"><img src=x onerror=alert(2)>', '<b>XSS</b> probe'],
    ] as [$code, $type, $name, $company, $subject]) {
        if (!$exists('SELECT 1 FROM enquiries WHERE enquiry_code = ?', [$code])) {
            $pdo->prepare("INSERT INTO enquiries (enquiry_code, type, name, company, email, subject, message, status) VALUES (?, ?, ?, ?, 'fixture@stg.invalid', ?, 'fixture', 'new')")
                ->execute([$code, $type, $name, $company, $subject]);
        }
    }
    if (!$exists('SELECT 1 FROM partner_applications WHERE application_code = ?', ['PYN-PARTNER-APP-STG'])) {
        $pdo->prepare("INSERT INTO partner_applications (application_code, partner_type, business_name, contact_person, email, mobile, status)
                       VALUES ('PYN-PARTNER-APP-STG', 'referral', 'STG Fixture Applicant (test)', 'Fixture', 'apply@stg.invalid', '9000000099', 'submitted')")->execute();
    }
    if (!$exists("SELECT 1 FROM products WHERE slug = 'stg-fixture-product'")) {   // inactive: never listed publicly
        $pdo->exec("INSERT INTO products (slug, name, category, short_description, is_active) VALUES ('stg-fixture-product', 'STG Fixture Product (test)', 'fixture', 'fixture', 0)");
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
} elseif ($mode === 'verify') {
    $need = ['staff role accounts (12)' => [count(array_filter(STG_STAFF_ROLES, fn ($r) => $userId("rbac-$r@stg.invalid") > 0)), 12]];
    foreach (['stg-super', 'admin1', 'admin2', 'customer', 'partner', 'employee', 'hr'] as $u) {
        $need["account $u@stg.invalid"] = [$userId("$u@stg.invalid") > 0 ? 1 : 0, 1];
    }
    foreach ([
        'customer row (customer@)'    => "SELECT COUNT(*) FROM customers c JOIN users u ON u.id = c.user_id WHERE u.email = 'customer@stg.invalid'",
        'KYC document row'            => "SELECT COUNT(*) FROM customer_kyc_documents WHERE doc_type = 'stg-fixture'",
        'partner row (partner@)'      => "SELECT COUNT(*) FROM partners p JOIN users u ON u.id = p.user_id WHERE u.email = 'partner@stg.invalid'",
        'employee row (employee@)'    => "SELECT COUNT(*) FROM employees e JOIN users u ON u.id = e.user_id WHERE u.email = 'employee@stg.invalid'",
        'enquiries (RBAC + XSS)'      => "SELECT COUNT(*) FROM enquiries WHERE enquiry_code IN ('PAY-ENQ-STG-RBAC', 'PAY-ENQ-STG-XSS')",
        'partner application'         => "SELECT COUNT(*) FROM partner_applications WHERE application_code = 'PYN-PARTNER-APP-STG'",
        'product stg-fixture-product' => "SELECT COUNT(*) FROM products WHERE slug = 'stg-fixture-product'",
        'CMS article'                 => "SELECT COUNT(*) FROM blog_posts WHERE slug = 'staging-approved-article'",
    ] as $label => $q) {
        $need[$label] = [(int) $pdo->query($q)->fetchColumn(), $label === 'enquiries (RBAC + XSS)' ? 2 : 1];
    }
    $bad = array_filter($need, fn ($v) => $v[0] < $v[1]);
    foreach ($need as $label => [$have, $want]) {
        echo ($have >= $want ? 'ok      ' : 'MISSING ') . "$label ($have/$want)\n";
    }
    exit($bad ? 1 : 0);
} elseif ($mode === 'cleanup') {
    $pdo->exec("DELETE FROM blog_posts WHERE slug IN ('staging-approved-article', 'staging-legal-review')");
    $pdo->exec("DELETE FROM contact_submissions WHERE enquiry_id IN (SELECT id FROM enquiries WHERE enquiry_code LIKE 'PAY-ENQ-STG-%' OR enquiry_code = 'PAY-ENQ-CSV-1' OR email LIKE '%@stg.invalid')");
    $pdo->exec("DELETE FROM enquiries WHERE enquiry_code LIKE 'PAY-ENQ-STG-%' OR enquiry_code = 'PAY-ENQ-CSV-1' OR email LIKE '%@stg.invalid'");
    $pdo->exec("DELETE FROM partner_applications WHERE application_code LIKE '%-STG%' OR email LIKE '%@stg.invalid'");
    $pdo->exec("DELETE FROM products WHERE slug = 'stg-fixture-product'");
    $pdo->exec("DELETE FROM customer_kyc_documents WHERE doc_type = 'stg-fixture'");
    $pdo->exec("DELETE FROM customers WHERE customer_code LIKE '%-STG-%' OR contact_email LIKE '%@stg.invalid'");
    $pdo->exec("DELETE FROM partners WHERE partner_code LIKE '%-STG-%'");
    $pdo->exec("DELETE FROM employees WHERE employee_code LIKE '%-STG-%'");
    $pdo->exec("DELETE FROM login_attempts WHERE identifier LIKE '%@stg.invalid'");
    foreach (['customers', 'partners', 'employees'] as $t) {   // rows owned by a test account, whatever their code
        $pdo->exec("DELETE t FROM $t t JOIN users u ON u.id = t.user_id WHERE u.email LIKE '%@stg.invalid'");
    }
    $pdo->exec("DELETE FROM users WHERE email LIKE '%@stg.invalid'");
    echo "fixtures removed (audit_logs rows are kept as evidence)\n";
} else {
    $left = 0;
    foreach (STG_CLEAN_CHECKS as $label => $q) {
        $n = (int) $pdo->query($q)->fetchColumn();
        $left += $n;
        echo ($n ? 'LEFT ' : 'ok   ') . "$label: $n\n";
    }
    exit($left ? 1 : 0);
}
