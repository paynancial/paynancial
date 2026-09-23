<?php
/**
 * Regression test for the jurisdiction publishing gate.
 * Run: php tests/jurisdiction-gate-test.php   (exit code 0 = pass)
 *
 * Rule (docs/jurisdiction-approval-matrix.md): a jurisdiction is approved —
 * indexable, sitemap-listed, allowed service wording — only when BOTH
 * 'served_confirmed' and 'content_verified' are exactly boolean true.
 * A page merely existing never makes it indexable.
 */
declare(strict_types=1);
define('APP_URL', 'https://paynancial.com');
function site_url(string $p = ''): string { return rtrim(APP_URL, '/') . '/' . ltrim($p, '/'); }
require __DIR__ . '/../includes/business-services.php';

$fail = 0;
$check = function (string $label, bool $got, bool $want) use (&$fail) {
    echo ($got === $want ? 'PASS' : 'FAIL') . "  $label\n";
    if ($got !== $want) $fail++;
};

$base = ['name' => 'Test', 'regions' => ['asia']];
$cases = [
    ['both true',                 ['served_confirmed' => true,  'content_verified' => true],  true],
    ['served only',               ['served_confirmed' => true,  'content_verified' => false], false],
    ['verified only',             ['served_confirmed' => false, 'content_verified' => true],  false],
    ['both false',                ['served_confirmed' => false, 'content_verified' => false], false],
    ['flags missing (page only)', [],                                                          false],
    ['truthy int 1',              ['served_confirmed' => 1,     'content_verified' => 1],     false],
    ["truthy string 'true'",      ['served_confirmed' => 'true', 'content_verified' => 'true'], false],
];
foreach ($cases as [$label, $flags, $want]) {
    $check("approved: $label", bs_jurisdiction_approved($base + $flags), $want);
}

// Current data: nothing approved, so no jurisdiction or hub page may be listed.
$paths = bs_sitemap_paths();
$check('no jurisdiction approved in current data', bs_international_confirmed(), false);
$check('no /jurisdictions URL in sitemap paths', (bool) array_filter($paths, fn ($p) => str_contains($p, '/jurisdictions')), false);
$check('no global-incorporation in sitemap paths', in_array('/business-services/global-incorporation', $paths, true), false);

echo $fail ? "\n$fail failure(s)\n" : "\nAll checks passed.\n";
exit($fail ? 1 : 0);
