<?php
/**
 * CMS pages (cms_pages): the homepage hero (page_key 'home') and per-page
 * SEO (page_key 'seo:/path'). Same workflow as articles; the public site
 * reads only the published snapshot (includes/cms/public.php).
 */

declare(strict_types=1);

require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/workflow.php';
require_once __DIR__ . '/public.php';

const CMS_HERO_LIMITS = [
    'eyebrow' => [40, 80], 'title' => [70, 120], 'lead' => [200, 300],
    'primary_label' => [24, 40], 'secondary_label' => [28, 40],
];
const CMS_SEO_LIMITS = ['meta_title' => [60, 190], 'meta_description' => [160, 300], 'og_title' => [60, 190], 'og_description' => [160, 300]];

function cms_page_row(PDO $pdo, string $key): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM cms_pages WHERE page_key = :k');
    $stmt->execute(['k' => $key]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** The working copy of one section of a page row ('hero' or 'seo'), or null. */
function cms_page_draft(?array $row, string $kind): ?array
{
    if ($row === null || $row['workflow_status'] === null) {
        return null; // legacy row never edited through the workflow
    }
    $c = json_decode((string) $row['content_json'], true);
    return is_array($c[$kind] ?? null) ? $c[$kind] : null;
}

function cms_hero_input(array $in): array
{
    $errors = [];
    $h = [];
    foreach (CMS_HERO_LIMITS as $k => [, $max]) {
        $h[$k] = cms_text($in[$k] ?? '', $max);
    }
    foreach (['title' => 'Heading', 'lead' => 'Supporting text', 'primary_label' => 'Primary button label', 'secondary_label' => 'Secondary button label'] as $k => $label) {
        if ($h[$k] === '') {
            $errors[$k] = $label . ' is required.';
        }
    }
    foreach (['primary_url' => 'Primary button link', 'secondary_url' => 'Secondary button link'] as $k => $label) {
        $h[$k] = cms_safe_path((string) ($in[$k] ?? '')) ?? '';
        if ($h[$k] === '') {
            $errors[$k] = $label . ' must be an on-site path starting with / (e.g. /contact).';
        }
    }
    return [$h, $errors];
}

function cms_seo_input(array $in, array $user, ?array $current): array
{
    $errors = [];
    $s = [];
    foreach (CMS_SEO_LIMITS as $k => [, $max]) {
        $s[$k] = cms_text($in[$k] ?? '', $max);
    }
    $s['og_image'] = trim((string) ($in['og_image'] ?? ''));
    if ($s['og_image'] !== '' && !preg_match('#^/(assets|uploads)/[A-Za-z0-9/_.\-]+\.(png|jpe?g|webp)$#', $s['og_image'])) {
        $errors['og_image'] = 'Social image must be an on-site image path (/assets/… or /uploads/…).';
    }
    // Robots can only add noindex. Changing it is an SEO decision (cms.seo.manage).
    if (user_can($user, 'cms.seo.manage')) {
        $s['robots'] = ($in['robots'] ?? '') === 'noindex' ? 'noindex' : 'default';
    } else {
        $s['robots'] = ($current['robots'] ?? 'default') === 'noindex' ? 'noindex' : 'default';
    }
    if (count(array_filter($s, fn ($v) => $v !== '' && $v !== 'default')) === 0) {
        $errors['meta_title'] = 'Enter at least one override (or leave this page on its template SEO).';
    }
    return [$s, $errors];
}

/** Save the working copy of a page section; returns the row id. */
function cms_page_save(PDO $pdo, array $user, string $key, string $title, string $kind, array $data, ?int $lockVersion): int
{
    require_permission($user, 'cms.edit');
    $row = cms_page_row($pdo, $key);
    $content = $row ? (json_decode((string) $row['content_json'], true) ?: []) : [];
    $content[$kind] = $data;
    $json = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $pdo->beginTransaction();
    try {
        if ($row === null) {
            $pdo->prepare(
                'INSERT INTO cms_pages (page_key, title, content_json, workflow_status, updated_by)
                 VALUES (:k, :t, :c, \'draft\', :uid)'
            )->execute(['k' => $key, 't' => $title, 'c' => $json, 'uid' => (int) $user['id']]);
            $id = (int) $pdo->lastInsertId();
            $from = null;
        } else {
            $id = (int) $row['id'];
            $from = $row['workflow_status'];
            $upd = $pdo->prepare(
                'UPDATE cms_pages SET content_json = :c, workflow_status = \'draft\', approved_by = NULL, approved_at = NULL,
                   updated_by = :uid, lock_version = lock_version + 1
                 WHERE id = :id AND lock_version = :lock'
            );
            $upd->execute(['c' => $json, 'uid' => (int) $user['id'], 'id' => $id, 'lock' => $lockVersion ?? (int) $row['lock_version']]);
            if ($upd->rowCount() !== 1) {
                throw new RuntimeException('This item was changed by someone else since you opened it. Reload to see the latest version.');
            }
        }
        cms_audit($pdo, (int) $user['id'], $from === null && $row === null ? 'cms.create' : 'cms.save', 'cms_page', $id, array_filter([
            'title' => $title, 'page_key' => $key, 'from' => $from, 'to' => 'draft',
            'note' => $from !== null && $from !== 'draft' ? 'Edited after ' . (cms_statuses()[$from] ?? $from) . ' — returned to draft; approval cleared.' : null,
            'live_unchanged' => $row && (int) $row['live'] === 1 ? true : null,
        ]));
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    return $id;
}

/** Publish callback: freezes the section being edited. */
function cms_page_snapshot(string $kind): callable
{
    return static function (array $row, array $user) use ($kind): array {
        $draft = cms_page_draft($row, $kind);
        if ($draft === null) {
            throw new RuntimeException('Nothing to publish.');
        }
        if ($kind === 'hero') {
            [$clean, $errors] = cms_hero_input($draft);
        } else {
            [$clean, $errors] = [$draft, []];
        }
        if ($errors) {
            throw new RuntimeException('The content is incomplete: ' . implode(' ', $errors));
        }
        return [$kind => $clean, 'approved_by_user' => cms_user_name(db(), $row['approved_by'] ? (int) $row['approved_by'] : null)];
    };
}

function cms_seo_key(string $path): string
{
    return 'seo:' . $path;
}
