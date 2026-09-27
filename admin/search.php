<?php
/**
 * Admin: global search — modules and records, filtered by the viewer's
 * permissions (a section the user cannot open is never searched).
 */
require_once __DIR__ . '/../includes/admin/ui.php';

$page_meta = ['title' => 'Search | Paynancial Admin', 'own_head' => true];
$pdo = db();
$q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$like = '%' . $q . '%';
$sections = [];

if ($q !== '') {
    $mods = array_values(array_filter(admin_palette_items($auth_user), fn ($i) => stripos($i['label'] . ' ' . $i['hint'] . ' ' . $i['k'], $q) !== false));
    $sections['Modules & actions'] = ['rows' => array_map(fn ($i) => [$i['label'], $i['type'] . ' · ' . $i['hint'], $i['url']], $mods)];
    $sources = [
        'Enquiries' => ['enquiries.view', 'SELECT id, enquiry_code, name, company, status FROM enquiries WHERE enquiry_code LIKE :a OR name LIKE :b OR company LIKE :c OR email LIKE :d ORDER BY created_at DESC LIMIT 8',
            fn ($r) => [$r['enquiry_code'] . ' · ' . $r['name'], trim(($r['company'] ?? '') . ' · ' . ucwords(str_replace('_', ' ', $r['status'])), ' ·'), '/admin/enquiries?q=' . rawurlencode($r['enquiry_code'])]],
        'Customers' => ['customers.view', 'SELECT id, customer_code, company_name, contact_name FROM customers WHERE customer_code LIKE :a OR company_name LIKE :b OR contact_name LIKE :c OR contact_email LIKE :d ORDER BY created_at DESC LIMIT 8',
            fn ($r) => [($r['company_name'] ?: $r['customer_code']), $r['customer_code'] . ($r['contact_name'] ? ' · ' . $r['contact_name'] : ''), '/admin/customers?q=' . rawurlencode($r['customer_code'])]],
        'Articles' => ['cms.view', 'SELECT id, title, slug, status FROM blog_posts WHERE title LIKE :a OR slug LIKE :b OR meta_description LIKE :c OR excerpt LIKE :d ORDER BY updated_at DESC LIMIT 8',
            fn ($r) => [$r['title'], '/blog/' . $r['slug'] . ' · ' . ucwords(str_replace('_', ' ', $r['status'])), '/admin/cms-article/' . (int) $r['id']]],
        'Users' => ['users.view', 'SELECT u.full_name, u.email, r.slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.full_name LIKE :a OR u.email LIKE :b OR u.mobile LIKE :c OR r.slug LIKE :d ORDER BY u.full_name LIMIT 8',
            fn ($r) => [$r['full_name'], $r['email'] . ' · ' . admin_role_label($r['slug']), '/admin/users?role=' . rawurlencode($r['slug'])]],
    ];
    foreach ($sources as $label => [$perm, $sql, $map]) {
        if (!user_can($auth_user, $perm)) {
            continue;
        }
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['a' => $like, 'b' => $like, 'c' => $like, 'd' => $like]);
            $sections[$label] = ['rows' => array_map($map, $stmt->fetchAll())];
        } catch (Throwable $e) {
            error_log('[Paynancial admin] search ' . $label . ' failed: ' . $e->getMessage());
            $sections[$label] = ['error' => true, 'rows' => []];
        }
    }
}
echo adm_page_head('Search', 'Modules, actions and records you have access to.');
?>
<section class="adm-card">
  <form method="get" role="search" class="adm-table-bar">
    <div class="adm-grow"><label class="sr-only" for="gs">Search</label><input type="search" id="gs" name="q" value="<?= e($q) ?>" placeholder="Search anything…" autofocus></div>
    <button class="adm-btn adm-btn--primary" type="submit"><?= adm_icon('search', 15) ?>Search</button>
  </form>
  <?php if ($q === ''): ?>
    <p class="adm-muted" style="margin:0">Tip: press <kbd class="adm-kbd" data-kbd-mod>⌘K</kbd> anywhere to jump straight to a module.</p>
  <?php else:
      $found = array_sum(array_map(fn ($s) => count($s['rows']), $sections)); ?>
    <p class="adm-muted" role="status" style="margin:0 0 16px"><?= $found ?> result<?= $found === 1 ? '' : 's' ?> for “<?= e($q) ?>”</p>
    <?php foreach ($sections as $label => $s): if (!$s['rows'] && empty($s['error'])) { continue; } ?>
      <h2 class="adm-hc-group"><?= e($label) ?></h2>
      <?php if (!empty($s['error'])): ?><div class="adm-state adm-state--error"><?= adm_icon('alert', 18) ?><div><strong>This section could not be searched.</strong></div></div><?php endif; ?>
      <ul class="adm-appr" style="gap:4px">
        <?php foreach ($s['rows'] as [$title, $sub, $url]): ?>
          <li style="padding:8px 0;border-bottom:1px solid var(--a-line)"><a href="<?= e($url) ?>" style="font-weight:600;text-decoration:none"><?= e($title) ?></a><span class="adm-muted" style="font-size:12.5px"><?= e($sub) ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php endforeach; ?>
    <?php if (!$found): ?><div class="adm-state"><?= adm_icon('search', 20) ?><div><strong>No results</strong><p>Try a customer code, enquiry ID, name or email.</p></div></div><?php endif; ?>
  <?php endif; ?>
</section>
