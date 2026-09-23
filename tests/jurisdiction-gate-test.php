<?php
/**
 * Regression test for the jurisdiction publishing gate.
 * Run: php tests/jurisdiction-gate-test.php   (exit code 0 = pass)
 *
 * Rule (docs/jurisdiction-approval-matrix.md): a jurisdiction is approved —
 * indexable, allowed service wording — only when BOTH 'service_enabled' and
 * 'indexable' are exactly boolean true; sitemap and service CTAs also need
 * their own 'sitemap' / 'service_promotion' flags.
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
    ['both true',                 ['service_enabled' => true,  'indexable' => true],  true],
    ['served only',               ['service_enabled' => true,  'indexable' => false], false],
    ['verified only',             ['service_enabled' => false, 'indexable' => true],  false],
    ['both false',                ['service_enabled' => false, 'indexable' => false], false],
    ['flags missing (page only)', [],                                                          false],
    ['truthy int 1',              ['service_enabled' => 1,     'indexable' => 1],     false],
    ["truthy string 'true'",      ['service_enabled' => 'true', 'indexable' => 'true'], false],
];
foreach ($cases as [$label, $flags, $want]) {
    $check("approved: $label", bs_jurisdiction_approved($base + $flags), $want);
}

$full = $base + ['service_enabled' => true, 'indexable' => true];
$check('sitemap needs its own flag', bs_jurisdiction_in_sitemap($full), false);
$check('sitemap with flag', bs_jurisdiction_in_sitemap($full + ['sitemap' => true]), true);
$check('promotion needs its own flag', bs_jurisdiction_promotable($full), false);
$check('promotion never without approval', bs_jurisdiction_promotable($base + ['service_promotion' => true]), false);
foreach (['uae', 'singapore', 'hong-kong', 'united-kingdom'] as $slug) {
    $jj = bs_jurisdiction($slug);
    $check("$slug is research-only (all flags false)", ($jj['research'] ?? false) === true
        && ($jj['service_enabled'] ?? null) === false && ($jj['indexable'] ?? null) === false
        && ($jj['sitemap'] ?? null) === false && ($jj['service_promotion'] ?? null) === false, true);
}

// Current data: nothing approved, so no jurisdiction or hub page may be listed.
$paths = bs_sitemap_paths();
$check('no jurisdiction approved in current data', bs_international_confirmed(), false);
$check('no /jurisdictions URL in sitemap paths', (bool) array_filter($paths, fn ($p) => str_contains($p, '/jurisdictions')), false);
$check('no global-incorporation in sitemap paths', in_array('/business-services/global-incorporation', $paths, true), false);

echo $fail ? "\n$fail failure(s)\n" : "\nAll checks passed.\n";
exit($fail ? 1 : 0);
