<?php
/**
 * General-purpose helpers shared across the site.
 */

declare(strict_types=1);

/** Escape for safe HTML output. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Build a versioned asset URL relative to /public/assets. */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $full = __DIR__ . '/../public/assets/' . $path;
    $version = is_file($full) ? filemtime($full) : time();
    return '/assets/' . $path . '?v=' . $version;
}

/** Absolute site URL helper. */
function site_url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/** Generate a human-readable, sequential-looking reference code. */
function generate_reference(string $prefix): string
{
    $year = date('Y');
    $random = strtoupper(bin2hex(random_bytes(4)));
    return sprintf('%s-%s-%s', $prefix, $year, $random);
}

/**
 * Generic sequential code generator: PREFIX-YEAR-NNNNNN.
 * $table/$column must NEVER be derived from user input — callers always
 * pass internal literals, so this is not a SQL-injection surface.
 */
function generate_sequential_code(PDO $pdo, string $table, string $column, string $prefix): string
{
    $year = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} LIKE :pattern");
    $stmt->execute(['pattern' => "{$prefix}-{$year}-%"]);
    $count = (int) $stmt->fetchColumn() + 1;
    return sprintf('%s-%s-%06d', $prefix, $year, $count);
}

function generate_partner_application_code(PDO $pdo): string
{
    return generate_sequential_code($pdo, 'partner_applications', 'application_code', 'PYN-PARTNER');
}

function generate_customer_application_code(PDO $pdo): string
{
    return generate_sequential_code($pdo, 'customer_applications', 'application_code', 'PYN-CUST');
}

function generate_proposal_code(PDO $pdo): string
{
    return generate_sequential_code($pdo, 'proposals', 'proposal_code', 'PYN-PROP');
}

function generate_ticket_code(PDO $pdo): string
{
    return generate_sequential_code($pdo, 'support_tickets', 'ticket_code', 'PYN-TKT');
}

function generate_payment_link_ref(PDO $pdo): string
{
    return generate_sequential_code($pdo, 'payment_links', 'link_ref', 'PYN-LINK');
}

/** Next sequential enquiry code: PAY-ENQ-2026-000001 */
function generate_enquiry_code(PDO $pdo): string
{
    $year = date('Y');
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM enquiries WHERE enquiry_code LIKE :pattern"
    );
    $stmt->execute(['pattern' => "PAY-ENQ-{$year}-%"]);
    $count = (int) $stmt->fetchColumn() + 1;
    return sprintf('PAY-ENQ-%s-%06d', $year, $count);
}

/** Render a page's SEO <head> tags. */
function seo_meta(array $meta): void
{
    $title       = $meta['title'] ?? APP_NAME;
    $description = $meta['description'] ?? 'Paynancial — secure, intelligent payment technology for modern businesses.';
    $canonical   = $meta['canonical'] ?? site_url($_SERVER['REQUEST_URI'] ?? '/');
    $image       = $meta['image'] ?? site_url('/assets/images/paynancial-icon.png');
    ?>
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Paynancial">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($image) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e($description) ?>">
    <?php if (!empty($meta['schema'])): ?>
    <script type="application/ld+json"><?= json_encode($meta['schema'], JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
    <?php
}

/** Organization schema, reused on most pages. */
function organization_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'Paynancial Technology Pvt. Ltd.',
        'legalName'=> 'Paynancial Technology Pvt. Ltd.',
        'url'      => APP_URL,
        'logo'     => site_url('/assets/images/paynancial-icon.png'),
        'email'    => 'hello@paynancial.com',
    ];
}

/** Simple flash-message helper (session-backed). */
function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

/** JSON response helper for API endpoints. */
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/** Read JSON body of the current request as an assoc array. */
function json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Format a rupee amount for display. */
function format_amount(float $amount, string $currency = 'INR'): string
{
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    return $symbol . number_format($amount, 2);
}
