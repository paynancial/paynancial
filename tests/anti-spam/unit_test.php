<?php
/**
 * Unit checks for includes/anti-spam.php that need no web server or database.
 * Run: php tests/anti-spam/unit_test.php   (exit 0 = pass)
 */
declare(strict_types=1);
define('APP_ENV', 'development');
define('APP_SECRET', 'unit-test-secret');
function db(): PDO { throw new RuntimeException('no database in unit tests'); }
function client_ip(): string { return '203.0.113.9'; }
require __DIR__ . '/../../includes/anti-spam.php';

$fail = 0;
$check = function (string $label, bool $ok) use (&$fail) { echo ($ok ? 'PASS' : 'FAIL') . "  $label\n"; if (!$ok) $fail++; };

// Keys: server environment only — constants are ignored.
putenv('TURNSTILE_SITE_KEY'); putenv('TURNSTILE_SECRET_KEY');
define('TURNSTILE_SECRET_KEY', 'constant-should-be-ignored');
$check('secret defined only as a constant is ignored', as_secret_configured() === false);
$check('form not offered without keys', as_form_available() === false);
$check('missing secret → verification FAILS (never the outage fallback)', as_verify_turnstile('pass-anything', '203.0.113.9') === 'failed');
putenv('TURNSTILE_SITE_KEY=site'); putenv('TURNSTILE_SECRET_KEY=secret');
$check('keys from environment are read', as_site_key() === 'site' && as_secret_configured());
$check('empty token fails without a network call', as_verify_turnstile('', '203.0.113.9') === 'failed');

// Validation.
[$f, $e] = as_validate(['name' => ' Asha  Verma ', 'email' => 'Asha@Example.COM', 'phone' => '+91 98765-43210']);
$check('normalises name, email, phone', $f['name'] === 'Asha Verma' && $f['email'] === 'asha@example.com' && $f['phone'] === '+919876543210' && !$e);
[, $e] = as_validate(['name' => 'X', 'email' => "a@b.com\r\nBcc: z@z.com", 'phone' => '123']);
$check('rejects header injection and bad phone', isset($e['email'], $e['phone']));
[, $e] = as_validate(['name' => '<b>x</b>', 'email' => 'a@b.com', 'phone' => '9876543210', 'requirement' => 'nope', 'message' => str_repeat('a', 2001)]);
$check('rejects markup in name, unknown requirement, long message', isset($e['name'], $e['requirement'], $e['message']));

// File store (DB unavailable here, so as_rate_hit uses it).
$dir = __DIR__ . '/../../storage/anti-spam';
$backup = $dir . '.bak-' . getmypid();
if (file_exists($dir)) rename($dir, $backup);
try {
    $ok = [];
    for ($i = 0; $i < 3; $i++) $ok[] = as_rate_hit('unit', 'v1', 2, 60);
    $check('file store enforces limit (2 allowed, 3rd refused)', $ok === [true, true, false]);

    file_put_contents($dir . '/' . as_bucket('unit', 'v2') . '.json', '{corrupt');
    $check('corrupted bucket → refused', as_rate_hit('unit', 'v2', 5, 60) === false);
    $check('corrupted bucket stays full (not reset)', as_rate_hit('unit', 'v2', 5, 60) === false);

    array_map('unlink', glob($dir . '/*') ?: []); rmdir($dir);
    file_put_contents($dir, 'not a directory'); // store unusable
    $check('file store unavailable → refused (never unrestricted)', as_rate_hit('unit', 'v3', 5, 60) === false);
    unlink($dir);
} finally {
    if (is_dir($dir)) { array_map('unlink', glob($dir . '/*') ?: []); rmdir($dir); }
    if (is_file($dir)) unlink($dir);
    if (file_exists($backup)) rename($backup, $dir);
}

echo $fail ? "\n$fail failure(s)\n" : "\nAll unit checks passed.\n";
exit($fail ? 1 : 0);
