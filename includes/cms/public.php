<?php
/**
 * Public read side of the CMS. Everything here is fail-safe: if the
 * database is unavailable, the migration has not been run, or nothing has
 * been published, the site renders exactly as the version-controlled code
 * defines it (hardcoded hero, template SEO, file-based articles).
 *
 * Only PUBLISHED snapshots (published_json with live = 1) are ever read.
 * Drafts, reviews and legacy cms_pages values never reach the public site.
 */

declare(strict_types=1);

/** Pages whose SEO the CMS may override (canonical and URL are never editable). */
function cms_seo_pages(): array
{
    return [
        '/' => 'Homepage', '/about' => 'About', '/products' => 'Products', '/solutions' => 'Solutions',
        '/pricing' => 'Pricing', '/developers' => 'Build / Developers', '/sandbox' => 'Sandbox',
        '/technology' => 'Technology', '/agentic-ai' => 'Agentic AI', '/ai-intelligence' => 'AI & Intelligence',
        '/security' => 'Security & Compliance', '/trust' => 'Trust Center', '/ai-governance' => 'AI Governance',
        '/business-services' => 'Business Services', '/resources' => 'Resources', '/resources/faqs' => 'FAQs',
        '/support' => 'Support', '/leadership' => 'Leadership', '/careers' => 'Careers',
        '/partner-program' => 'Partner Program', '/contact' => 'Contact', '/blog' => 'Blog hub',
    ];
}

/** Published CMS rows, read once per request. Returns [] on any failure. */
function cms_published_rows(string $table): array
{
    static $cache = [];
    if (isset($cache[$table])) {
        return $cache[$table];
    }
    $cache[$table] = [];
    if (!function_exists('db') || !defined('DB_HOST')) {
        return [];
    }
    try {
        $sql = $table === 'blog_posts'
            ? 'SELECT slug AS k, live, published_json FROM blog_posts WHERE published_json IS NOT NULL'
            : 'SELECT page_key AS k, live, published_json FROM cms_pages WHERE live = 1 AND published_json IS NOT NULL';
        foreach (db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $snap = json_decode((string) $row['published_json'], true);
            $cache[$table][$row['k']] = ((int) $row['live'] === 1 && is_array($snap)) ? $snap : null;
        }
    } catch (Throwable $e) {
        error_log('[Paynancial CMS] public read skipped: ' . $e->getMessage());
        $cache[$table] = [];
    }
    return $cache[$table];
}

/**
 * CMS article overrides for the blog registry: slug => published snapshot,
 * or slug => null when a once-published article has been unpublished.
 */
function cms_blog_overrides(): array
{
    $out = [];
    foreach (cms_published_rows('blog_posts') as $slug => $snap) {
        if ($snap === null) {
            $out[$slug] = null;
        } elseif (($snap['slug'] ?? null) === $slug && ($snap['type'] ?? 'general') === 'general' && ($snap['category'] ?? '') !== 'regulatory') {
            $out[$slug] = $snap;
        }
    }
    return $out;
}

/** The hardcoded homepage hero — always the fallback for every field. */
function home_hero_defaults(): array
{
    return [
        'eyebrow'         => 'Paynancial Technology Pvt. Ltd.',
        'title'           => 'Smarter Payment Infrastructure for Growing Businesses.',
        'lead'            => 'Accept payments, collect dues, send payouts, and understand every transaction — through one platform, a clear dashboard, and a developer-first API.',
        'primary_label'   => 'Get Started',
        'primary_url'     => '/contact',
        'secondary_label' => function_exists('cta_label') ? cta_label() : 'Talk to Payment Experts',
        'secondary_url'   => '/contact?intent=sales',
    ];
}

/** Homepage hero: the published CMS hero merged over the hardcoded defaults. */
function cms_home_hero(array $defaults): array
{
    // Admin preview of a draft (set only by admin/cms-preview.php, never by a request).
    $hero = $GLOBALS['cms_preview_hero'] ?? cms_published_rows('cms_pages')['home']['hero'] ?? null;
    if (!is_array($hero)) {
        return $defaults;
    }
    foreach ($defaults as $k => $v) {
        if (isset($hero[$k]) && is_string($hero[$k]) && trim($hero[$k]) !== '') {
            $defaults[$k] = $hero[$k];
        }
    }
    return $defaults;
}

/** Published SEO override for a path, or null. */
function cms_seo_override(string $path): ?array
{
    if (!isset(cms_seo_pages()[$path])) {
        return null;
    }
    $seo = cms_published_rows('cms_pages')['seo:' . $path]['seo'] ?? null;
    return is_array($seo) ? $seo : null;
}

/** The CMS may only ever ADD noindex — it can never make a page indexable. */
function cms_seo_noindex(string $path): bool
{
    return (cms_seo_override($path)['robots'] ?? '') === 'noindex';
}

/** Apply a published SEO override to a page's $page_meta (title, description, social tags). */
function cms_apply_seo(array $meta, string $path): array
{
    $seo = cms_seo_override($path);
    if ($seo === null) {
        return $meta;
    }
    foreach (['title' => 'meta_title', 'description' => 'meta_description', 'og_title' => 'og_title',
        'og_description' => 'og_description', 'image' => 'og_image'] as $metaKey => $seoKey) {
        if (!empty($seo[$seoKey]) && is_string($seo[$seoKey])) {
            $meta[$metaKey] = $metaKey === 'image' ? site_url(ltrim($seo[$seoKey], '/')) : $seo[$seoKey];
        }
    }
    return $meta;
}
