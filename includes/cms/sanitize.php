<?php
/**
 * CMS input sanitising. Article bodies are the only HTML the CMS accepts;
 * everything else is plain text escaped on output with e().
 *
 * The HTML allowlist mirrors what the version-controlled articles use:
 * paragraphs, lists, emphasis, links, sub-headings, quotes, code, tables and
 * the styled wrappers (.blog-table, .blog-callout, .blog-official). Scripts, styles,
 * iframes, forms, event handlers and inline styles are removed.
 */

declare(strict_types=1);

const CMS_ALLOWED_TAGS = [
    'p' => [], 'br' => [], 'strong' => [], 'em' => [], 'b' => [], 'i' => [], 'u' => [],
    'ul' => [], 'ol' => [], 'li' => [], 'h2' => ['id'], 'h3' => [], 'h4' => [], 'blockquote' => [],
    'code' => [], 'pre' => [], 'a' => ['href'], 'span' => [], 'div' => ['class'],
    'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
    'dl' => [], 'dt' => [], 'dd' => [],
];
const CMS_ALLOWED_CLASSES = ['blog-table', 'blog-official', 'blog-callout', 'blog-callout--warn'];
/** Elements removed together with their content. */
const CMS_DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button',
    'textarea', 'select', 'svg', 'math', 'template', 'noscript', 'link', 'meta', 'base', 'frame', 'frameset'];

/** A link target the CMS will accept: on-site path, https URL or mailto. */
function cms_safe_href(string $href): ?string
{
    $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5));
    if ($href === '' || preg_match('/[\x00-\x1F\x7F\s]/', $href)) {
        return null;
    }
    if (preg_match('#^/(?!/)#', $href) || str_starts_with($href, '#')) {
        return $href;
    }
    if (preg_match('#^https://[a-z0-9.-]+\.[a-z]{2,}(/|$|\?|\#)#i', $href)) {
        return $href;
    }
    if (preg_match('#^mailto:[^@\s]+@[^@\s]+$#i', $href)) {
        return $href;
    }
    return null;
}

/** An on-site path (for CTA buttons and "related on Paynancial" links). */
function cms_safe_path(string $path): ?string
{
    $path = trim($path);
    return preg_match('#^/(?!/)[A-Za-z0-9/_\-.?=&%]*$#', $path) ? $path : null;
}

function cms_slugify(string $text): string
{
    $text = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text) ?? '', '-'));
    return substr($text, 0, 80);
}

/** Sanitise an HTML fragment against the allowlist. */
function cms_sanitize_html(string $html): string
{
    if (trim($html) === '') {
        return '';
    }
    $doc = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><!DOCTYPE html><html><body><div id="cms-root">' . $html . '</div></body></html>',
        LIBXML_NONET | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $root = $doc->getElementById('cms-root');
    if (!$root) {
        return '';
    }
    cms_sanitize_node($root);
    $out = '';
    foreach (iterator_to_array($root->childNodes) as $child) {
        $out .= $doc->saveHTML($child);
    }
    return trim($out);
}

function cms_sanitize_node(DOMNode $node): void
{
    foreach (iterator_to_array($node->childNodes) as $child) {
        if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
            $node->removeChild($child);
            continue;
        }
        if (!$child instanceof DOMElement) {
            continue; // text
        }
        $tag = strtolower($child->tagName);
        if (in_array($tag, CMS_DROP_WITH_CONTENT, true)) {
            $node->removeChild($child);
            continue;
        }
        cms_sanitize_node($child);
        if (!isset(CMS_ALLOWED_TAGS[$tag])) {
            // Unknown wrapper: keep its (already sanitised) children, drop the tag.
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }
        foreach (iterator_to_array($child->attributes) as $attr) {
            $name = strtolower($attr->name);
            $keep = in_array($name, CMS_ALLOWED_TAGS[$tag], true);
            if ($keep && $name === 'href') {
                $safe = cms_safe_href($attr->value);
                $keep = $safe !== null;
                if ($keep) {
                    $child->setAttribute('href', $safe);
                }
            } elseif ($keep && $name === 'class') {
                $classes = array_intersect(preg_split('/\s+/', trim($attr->value)) ?: [], CMS_ALLOWED_CLASSES);
                $keep = (bool) $classes;
                if ($keep) {
                    $child->setAttribute('class', implode(' ', $classes));
                }
            } elseif ($keep && $name === 'id') {
                $keep = (bool) preg_match('/^[a-z0-9][a-z0-9-]{0,79}$/', $attr->value);
            }
            if (!$keep) {
                $child->removeAttribute($attr->name);
            }
        }
        if ($tag === 'a' && preg_match('#^https://#i', (string) $child->getAttribute('href'))) {
            $child->setAttribute('rel', 'noopener');
        }
    }
}

/** Plain single-line text: tags stripped, whitespace collapsed, length capped. */
function cms_text(mixed $value, int $max): string
{
    $value = strip_tags((string) $value);
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    return mb_substr($value, 0, $max);
}
