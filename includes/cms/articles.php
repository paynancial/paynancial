<?php
/**
 * CMS blog articles (blog_posts).
 *
 * The working copy lives in the row (title, meta, content_json …). The
 * public site only ever sees published_json — a snapshot frozen at publish
 * time in exactly the same shape as a version-controlled article file
 * (includes/blog/articles/*.php), so the existing templates, publishing gate,
 * sitemap and topic pages treat both the same way.
 *
 * Regulatory Insights is not available in the CMS: regulatory articles stay
 * source-first, version-controlled files with a complete source record.
 */

declare(strict_types=1);

require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/workflow.php';
require_once __DIR__ . '/../blog.php';

/** Public approval label (the individual approver is kept in the snapshot and audit log). */
const CMS_APPROVAL_LABEL = 'Paynancial Editorial Team';

/** Character guidance shown in the editor (soft limits) and hard caps (stored). */
const CMS_LIMITS = [
    'title' => [70, 190], 'meta_title' => [60, 190], 'description' => [160, 300], 'dek' => [220, 300],
    'question' => [120, 300], 'answer' => [600, 2000], 'og_title' => [60, 190], 'og_description' => [160, 300],
];

function cms_article_categories(): array
{
    $cats = [];
    foreach (blog_categories() as $slug => [$label]) {
        if ($slug !== 'regulatory') {
            $cats[$slug] = $label;
        }
    }
    return $cats;
}

/** Split sanitised body HTML into [id, heading, html] sections at each <h2>. */
function cms_body_to_sections(string $html): array
{
    $html = cms_sanitize_html($html);
    if ($html === '') {
        return [];
    }
    $doc = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><!DOCTYPE html><html><body><div id="cms-root">' . $html . '</div></body></html>',
        LIBXML_NONET | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $root = $doc->getElementById('cms-root');
    $sections = [];
    $current = null;
    $used = [];
    foreach (iterator_to_array($root->childNodes) as $node) {
        if ($node instanceof DOMElement && strtolower($node->tagName) === 'h2') {
            if ($current !== null) {
                $sections[] = $current;
            }
            $heading = cms_text($node->textContent, 190);
            $id = $node->getAttribute('id') ?: cms_slugify($heading);
            $id = $id !== '' ? $id : 'section';
            $base = $id;
            for ($n = 2; isset($used[$id]) || in_array($id, ['source', 'quick-answer', 'faq'], true); $n++) {
                $id = $base . '-' . $n;
            }
            $used[$id] = true;
            $current = [$id, $heading, ''];
            continue;
        }
        $chunk = $doc->saveHTML($node);
        if ($current === null) {
            if (trim(strip_tags($chunk)) !== '') {
                $current = ['__intro', '', ''];
            } else {
                continue;
            }
        }
        $current[2] .= $chunk;
    }
    if ($current !== null) {
        $sections[] = $current;
    }
    return array_map(fn ($s) => [$s[0], $s[1], trim($s[2])], $sections);
}

function cms_sections_to_body(array $sections): string
{
    $out = [];
    foreach ($sections as [$id, $heading, $html]) {
        $out[] = '<h2 id="' . e($id) . '">' . e($heading) . "</h2>\n" . trim($html);
    }
    return implode("\n\n", $out);
}

/** "Question" line, then the answer; blocks separated by a blank line. */
function cms_parse_faqs(string $text): array
{
    $faqs = [];
    foreach (preg_split("/\R\s*\R/", trim($text)) ?: [] as $block) {
        $lines = preg_split('/\R/', trim($block)) ?: [];
        $q = cms_text(array_shift($lines) ?? '', 300);
        $a = cms_text(implode(' ', $lines), 1500);
        if ($q !== '' || $a !== '') {
            $faqs[] = [$q, $a];
        }
    }
    return $faqs;
}

function cms_faqs_to_text(array $faqs): string
{
    return implode("\n\n", array_map(fn ($f) => $f[0] . "\n" . $f[1], $faqs));
}

function cms_lines(string $text, int $max, int $count): array
{
    $out = [];
    foreach (preg_split('/\R/', $text) ?: [] as $line) {
        $line = cms_text($line, $max);
        if ($line !== '') {
            $out[] = $line;
        }
    }
    return array_slice($out, 0, $count);
}

/**
 * Validate editor input into row fields. Returns [fields, errors].
 * $existing is the current row (null for a new article).
 */
function cms_article_input(PDO $pdo, array $user, array $in, ?array $existing): array
{
    $errors = [];
    $f = [];
    foreach (['title', 'meta_title', 'description', 'dek', 'question', 'answer', 'og_title', 'og_description'] as $k) {
        $f[$k] = cms_text($in[$k] ?? '', CMS_LIMITS[$k][1]);
    }
    foreach (['title' => 'Title', 'description' => 'Meta description', 'question' => 'Quick-answer question', 'answer' => 'Quick answer'] as $k => $label) {
        if ($f[$k] === '') {
            $errors[$k] = $label . ' is required.';
        }
    }

    // Slug: fixed once the article has been published (URLs never change).
    if ($existing && $existing['published_json'] !== null) {
        $f['slug'] = $existing['slug'];
    } else {
        $f['slug'] = cms_slugify((string) ($in['slug'] ?? '') ?: $f['title']);
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $f['slug']) || strlen($f['slug']) < 3) {
            $errors['slug'] = 'Use lowercase letters, numbers and hyphens (at least 3 characters).';
        } else {
            $dup = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = :s AND id <> :id');
            $dup->execute(['s' => $f['slug'], 'id' => (int) ($existing['id'] ?? 0)]);
            if ($dup->fetchColumn() !== false || isset(blog_file_articles()[$f['slug']])) {
                $errors['slug'] = 'This URL is already used by another article.';
            }
        }
    }

    $f['category'] = (string) ($in['category'] ?? '');
    if (!isset(cms_article_categories()[$f['category']])) {
        $errors['category'] = 'Choose a topic.';
    }

    $sections = cms_body_to_sections((string) ($in['body'] ?? ''));
    if (!$sections) {
        $errors['body'] = 'The article body is empty.';
    } elseif ($sections[0][0] === '__intro') {
        $errors['body'] = 'Start the body with a section heading (H2). Each H2 becomes a section.';
    }

    $related = [];
    foreach (preg_split('/[\s,]+/', (string) ($in['related'] ?? '')) ?: [] as $slug) {
        if ($slug !== '' && $slug !== $f['slug'] && preg_match('/^[a-z0-9-]+$/', $slug) && !in_array($slug, $related, true)) {
            $related[] = $slug;
        }
    }
    $links = [];
    foreach (preg_split('/\R/', (string) ($in['links'] ?? '')) ?: [] as $line) {
        if (trim($line) === '') {
            continue;
        }
        [$label, $href] = array_map('trim', explode('|', $line, 2) + [1 => '']);
        $safe = cms_safe_path($href);
        if ($safe === null || $label === '') {
            $errors['links'] = 'Each related link must be "Label | /on-site-path".';
            continue;
        }
        $links[] = [cms_text($label, 80), $safe];
    }

    $f['og_image'] = trim((string) ($in['og_image'] ?? ''));
    if ($f['og_image'] !== '' && !preg_match('#^/(assets|uploads)/[A-Za-z0-9/_.\-]+\.(png|jpe?g|webp)$#', $f['og_image'])) {
        $errors['og_image'] = 'Social image must be an on-site image path (/assets/… or /uploads/…).';
    }

    $f['content'] = [
        'dek' => $f['dek'], 'question' => $f['question'], 'answer' => $f['answer'],
        'takeaways' => cms_lines((string) ($in['takeaways'] ?? ''), 300, 8),
        'sections' => $sections,
        'faqs' => array_slice(cms_parse_faqs((string) ($in['faqs'] ?? '')), 0, 12),
        'related' => array_slice($related, 0, 6),
        'links' => array_slice($links, 0, 6),
    ];
    foreach ($f['content']['faqs'] as [$q, $a]) {
        if ($q === '' || $a === '') {
            $errors['faqs'] = 'Each FAQ needs a question line followed by its answer.';
        }
    }

    // Indexing flags are SEO decisions: only cms.seo.manage may change them.
    if (user_can($user, 'cms.seo.manage')) {
        $f['indexable'] = !empty($in['indexable']) ? 1 : 0;
        $f['in_sitemap'] = $f['indexable'] && !empty($in['in_sitemap']) ? 1 : 0;
    } else {
        $f['indexable'] = (int) ($existing['indexable'] ?? 0);
        $f['in_sitemap'] = (int) ($existing['in_sitemap'] ?? 0);
    }
    return [$f, $errors];
}

/**
 * Create or update an article's working copy. Editing anything that was in
 * review, approved or published returns it to DRAFT (approval cleared); a
 * live snapshot stays live. Returns the article id.
 */
function cms_article_save(PDO $pdo, array $user, ?array $existing, array $f, ?int $lockVersion = null): int
{
    $cols = [
        'slug' => $f['slug'], 'title' => $f['title'], 'category' => $f['category'],
        'excerpt' => $f['dek'], 'meta_title' => $f['meta_title'] ?: null, 'meta_description' => $f['description'],
        'og_title' => $f['og_title'] ?: null, 'og_description' => $f['og_description'] ?: null, 'og_image' => $f['og_image'] ?: null,
        'body_html' => cms_sections_to_body($f['content']['sections']),
        'content_json' => json_encode($f['content'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        'indexable' => $f['indexable'], 'in_sitemap' => $f['in_sitemap'], 'uid' => (int) $user['id'],
    ];
    if ($existing === null) {
        require_permission($user, 'cms.create');
        $pdo->beginTransaction();
        $pdo->prepare(
            'INSERT INTO blog_posts (slug, title, category, excerpt, meta_title, meta_description, og_title, og_description,
               og_image, body_html, content_json, indexable, in_sitemap, status, author_id, updated_by, source)
             VALUES (:slug, :title, :category, :excerpt, :meta_title, :meta_description, :og_title, :og_description,
               :og_image, :body_html, :content_json, :indexable, :in_sitemap, \'draft\', :uid, :uid2, \'cms\')'
        )->execute($cols + ['uid2' => (int) $user['id']]);
        $id = (int) $pdo->lastInsertId();
        cms_audit($pdo, (int) $user['id'], 'cms.create', 'blog_post', $id, ['title' => $f['title'], 'slug' => $f['slug']]);
        $pdo->commit();
        return $id;
    }

    require_permission($user, 'cms.edit');
    $id = (int) $existing['id'];
    $from = (string) $existing['status'];
    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare(
            'UPDATE blog_posts SET slug = :slug, title = :title, category = :category, excerpt = :excerpt, meta_title = :meta_title,
               meta_description = :meta_description, og_title = :og_title, og_description = :og_description, og_image = :og_image,
               body_html = :body_html, content_json = :content_json, indexable = :indexable, in_sitemap = :in_sitemap,
               updated_by = :uid, status = \'draft\', approved_by = NULL, approved_at = NULL, lock_version = lock_version + 1
             WHERE id = :id AND lock_version = :lock'
        );
        $upd->execute($cols + ['id' => $id, 'lock' => $lockVersion ?? (int) $existing['lock_version']]);
        if ($upd->rowCount() !== 1) {
            throw new RuntimeException('This article was changed by someone else since you opened it. Reload to see the latest version.');
        }
        cms_audit($pdo, (int) $user['id'], 'cms.save', 'blog_post', $id, array_filter([
            'title' => $f['title'], 'from' => $from, 'to' => 'draft',
            'note' => $from !== 'draft' ? 'Edited after ' . (cms_statuses()[$from] ?? $from) . ' — returned to draft; approval cleared.' : null,
            'live_unchanged' => (int) $existing['live'] === 1 ? true : null,
        ]));
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    return $id;
}

/** Row → article array in the version-controlled file shape (used for publish and preview). */
function cms_article_array(PDO $pdo, array $row, ?array $approver = null): array
{
    $c = json_decode((string) $row['content_json'], true) ?: [];
    $prev = $row['published_json'] ? json_decode((string) $row['published_json'], true) : null;
    $file = blog_file_articles()[$row['slug']] ?? null;
    $indexable = (int) $row['indexable'] === 1;
    return [
        'slug' => $row['slug'], 'category' => $row['category'], 'type' => 'general',
        'status' => $indexable ? 'indexable' : 'published',
        'created' => $prev['created'] ?? $file['created'] ?? date('Y-m-d'),
        'updated' => date('Y-m-d'),
        'editor' => null,
        'approved_by' => CMS_APPROVAL_LABEL,
        'approved_on' => $row['approved_at'] ? substr((string) $row['approved_at'], 0, 10) : date('Y-m-d'),
        'approved_by_user' => $approver['name'] ?? cms_user_name($pdo, $row['approved_by'] ? (int) $row['approved_by'] : null),
        'indexable' => $indexable,
        'sitemap' => $indexable && (int) $row['in_sitemap'] === 1,
        'title' => $row['title'],
        'meta_title' => $row['meta_title'] ?: $row['title'] . ' | Paynancial Insights',
        'description' => $row['meta_description'],
        'dek' => $c['dek'] ?? '',
        'question' => $c['question'] ?? '', 'answer' => $c['answer'] ?? '',
        'takeaways' => $c['takeaways'] ?? [], 'sections' => $c['sections'] ?? [],
        'faqs' => $c['faqs'] ?? [], 'related' => $c['related'] ?? [], 'links' => $c['links'] ?? [],
        'og_title' => $row['og_title'], 'og_description' => $row['og_description'], 'og_image' => $row['og_image'],
        'cms_id' => (int) $row['id'],
    ];
}

/** Publish callback for cms_transition(). */
function cms_article_snapshot(PDO $pdo): callable
{
    return static function (array $row, array $user) use ($pdo): array {
        $a = cms_article_array($pdo, $row);
        if ($a['category'] === 'regulatory' || !isset(cms_article_categories()[$a['category']])) {
            throw new RuntimeException('This topic cannot be published from the CMS.');
        }
        if (!$a['sections'] || $a['question'] === '' || $a['answer'] === '' || $a['description'] === '') {
            throw new RuntimeException('The article is incomplete (question, answer, meta description and at least one section are required).');
        }
        return $a;
    };
}

/**
 * Import one version-controlled article into the CMS as a published item,
 * preserving its content, dates, approval and indexing decisions exactly.
 * Returns the new id, or null when it already exists.
 */
function cms_import_file_article(PDO $pdo, array $a, ?int $userId): ?int
{
    $exists = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = :s');
    $exists->execute(['s' => $a['slug']]);
    if ($exists->fetchColumn() !== false) {
        return null;
    }
    if (($a['type'] ?? 'general') !== 'general' || !blog_is_live($a)) {
        return null; // regulatory and non-live articles stay file-only
    }
    $content = [
        'dek' => $a['dek'] ?? '', 'question' => $a['question'], 'answer' => $a['answer'], 'takeaways' => $a['takeaways'] ?? [],
        'sections' => $a['sections'], 'faqs' => $a['faqs'], 'related' => $a['related'], 'links' => $a['links'],
    ];
    $indexable = $a['status'] === 'indexable' && $a['indexable'] === true;
    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'INSERT INTO blog_posts (slug, title, category, excerpt, body_html, content_json, meta_title, meta_description,
               indexable, in_sitemap, live, source, status, approved_at, published_at, published_json, author_id, updated_by)
             VALUES (:slug, :title, :category, :excerpt, :body, :content, :mt, :md, :idx, :sm, 1, \'file_import\', \'published\',
               :approved_at, NOW(), :snap, :uid, :uid2)'
        )->execute([
            'slug' => $a['slug'], 'title' => $a['title'], 'category' => $a['category'], 'excerpt' => mb_substr((string) ($a['dek'] ?? ''), 0, 300),
            'body' => cms_sections_to_body($a['sections']),
            'content' => json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'mt' => $a['meta_title'], 'md' => mb_substr((string) $a['description'], 0, 300),
            'idx' => $indexable ? 1 : 0, 'sm' => $indexable && $a['sitemap'] === true ? 1 : 0,
            'approved_at' => $a['approved_on'] ? $a['approved_on'] . ' 00:00:00' : null,
            'snap' => json_encode($a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'uid' => $userId, 'uid2' => $userId,
        ]);
        $id = (int) $pdo->lastInsertId();
        cms_audit($pdo, $userId, 'cms.import', 'blog_post', $id, [
            'title' => $a['title'], 'slug' => $a['slug'], 'approved_by' => $a['approved_by'], 'approved_on' => $a['approved_on'],
            'note' => 'Imported from includes/blog/articles/' . $a['slug'] . '.php; published snapshot identical to the file.',
        ]);
        $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
