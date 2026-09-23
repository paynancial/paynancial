<?php
/**
 * Paynancial front controller.
 * All requests are routed here by public/.htaccess. Clean URLs only —
 * no .php extension is ever exposed publicly.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// One URL per page: permanently redirect trailing-slash URLs to the
// slash-less form (query string preserved). GET/HEAD only; the API and
// form posts are left alone.
if ($requestPath !== '/' && str_ends_with($requestPath, '/')
    && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)
    && !str_starts_with($requestPath, '/api/')) {
    $query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    header('Location: ' . rtrim($requestPath, '/') . ($query ? '?' . $query : ''), true, 301);
    exit;
}
$path = trim($requestPath, '/');
$segments = $path === '' ? [] : explode('/', $path);
$current_path = $requestPath;

// ---------------------------------------------------------------------
// API routes — JSON only, no HTML shell.
// ---------------------------------------------------------------------
if (($segments[0] ?? '') === 'api') {
    $apiRoutes = [
        'api/auth/login'            => __DIR__ . '/../api/auth/login.php',
        'api/auth/verify-otp'       => __DIR__ . '/../api/auth/verify-otp.php',
        'api/auth/resend-otp'       => __DIR__ . '/../api/auth/resend-otp.php',
        'api/auth/logout'           => __DIR__ . '/../api/auth/logout.php',
        'api/auth/forgot-password'  => __DIR__ . '/../api/auth/forgot-password.php',
        'api/contact/submit'        => __DIR__ . '/../api/contact/submit.php',
        'api/newsletter/subscribe'  => __DIR__ . '/../api/newsletter/subscribe.php',
        'api/partner/recommend'     => __DIR__ . '/../api/partner/recommend.php',
        'api/partner/assistant'     => __DIR__ . '/../api/partner/assistant.php',
    ];
    $file = $apiRoutes[$path] ?? null;
    if ($file && is_file($file)) {
        require $file;
        exit;
    }
    json_response(['ok' => false, 'error' => 'Not found'], 404);
}

// ---------------------------------------------------------------------
// Public partner entry points — these sit under /partner/... but are
// NOT authenticated, so they're handled before the dashboard-area gate
// below (which would otherwise treat any /partner/* path as protected).
// ---------------------------------------------------------------------
if (($segments[0] ?? '') === 'partner' && ($segments[1] ?? '') === 'register') {
    ob_start();
    include __DIR__ . '/../pages/partner-register.php';
    $page_body = ob_get_clean();

    include __DIR__ . '/../includes/site-head.php';
    include __DIR__ . '/../includes/header.php';
    echo '<main id="main-content">' . $page_body . '</main>';
    include __DIR__ . '/../includes/footer.php';
    include __DIR__ . '/../includes/site-foot.php';
    exit;
}
if (($segments[0] ?? '') === 'partner' && ($segments[1] ?? '') === 'login') {
    header('Location: /?login=partner');
    exit;
}

// ---------------------------------------------------------------------
// Public customer self-service signup — /signup and /signup/verify.
// Not authenticated (the account only becomes active once the OTP
// step in pages/signup-verify.php completes).
// ---------------------------------------------------------------------
if (($segments[0] ?? '') === 'signup') {
    $signupFile = ($segments[1] ?? '') === 'verify' ? '/../pages/signup-verify.php' : '/../pages/signup.php';
    ob_start();
    include __DIR__ . $signupFile;
    $page_body = ob_get_clean();

    include __DIR__ . '/../includes/site-head.php';
    include __DIR__ . '/../includes/header.php';
    echo '<main id="main-content">' . $page_body . '</main>';
    include __DIR__ . '/../includes/footer.php';
    include __DIR__ . '/../includes/site-foot.php';
    exit;
}

// ---------------------------------------------------------------------
// Authenticated dashboard routes: /{area}/{page}[/{id}]
// ---------------------------------------------------------------------
$dashboardAreas = [
    'customer'    => ['roles' => ['customer'],                         'dir' => 'customer', 'pages' => ['dashboard', 'onboarding', 'transactions', 'profile']],
    'partner'     => ['roles' => ['partner'],                          'dir' => 'partner',  'pages' => [
        'dashboard', 'onboarding', 'customers', 'enroll-customer', 'products', 'transactions',
        'settlements', 'commissions', 'proposals', 'payment-links', 'performance', 'support',
        'resources', 'marketing', 'profile', 'team',
    ]],
    'employee'    => ['roles' => ['employee', 'admin', 'super_admin'], 'dir' => 'employee', 'pages' => ['dashboard', 'tasks', 'profile']],
    'hrms'        => ['roles' => ['hr', 'admin', 'super_admin'],       'dir' => 'hrms',     'pages' => ['dashboard', 'employees', 'recruitment', 'attendance']],
    'admin'       => ['roles' => ['admin', 'super_admin'],             'dir' => 'admin',    'pages' => [
        'dashboard', 'users', 'transactions', 'cms', 'enquiries',
        'partner-applications', 'products', 'commission-rules', 'customer-applications', 'customer-kyc',
        'change-requests', 'audit-logs',
    ]],
    'super-admin' => ['roles' => ['super_admin'],                      'dir' => 'admin',    'pages' => ['dashboard']],
];

if (isset($dashboardAreas[$segments[0] ?? ''])) {
    $areaSlug = $segments[0];
    $area = $dashboardAreas[$areaSlug];
    $page = $segments[1] ?? 'dashboard';

    if (!in_array($page, $area['pages'], true)) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
        exit;
    }

    $auth_user = require_role($area['roles']);
    $dashboard_area = $areaSlug;
    $dashboard_page = $page;
    $route_param = $segments[2] ?? null; // e.g. the {id} in /partner/customers/{id}
    $file = __DIR__ . '/../' . $area['dir'] . '/' . $page . '.php';

    if (!is_file($file)) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
        exit;
    }

    ob_start();
    require $file; // may set $page_meta
    $dashboard_body = ob_get_clean();

    include __DIR__ . '/../includes/dashboard-head.php';
    echo $dashboard_body;
    include __DIR__ . '/../includes/dashboard-foot.php';
    exit;
}

// ---------------------------------------------------------------------
// Public payment link viewer: /pay/{ref}
// ---------------------------------------------------------------------
if (($segments[0] ?? '') === 'pay' && isset($segments[1])) {
    $pay_ref = $segments[1];
    // Customer payment-link pages are private to the payer — never index.
    header('X-Robots-Tag: noindex, nofollow');
    ob_start();
    include __DIR__ . '/../pages/pay.php';
    $page_body = ob_get_clean();

    include __DIR__ . '/../includes/site-head.php';
    include __DIR__ . '/../includes/header.php';
    echo '<main id="main-content">' . $page_body . '</main>';
    include __DIR__ . '/../includes/footer.php';
    include __DIR__ . '/../includes/site-foot.php';
    exit;
}

// ---------------------------------------------------------------------
// Product detail pages: /products/{slug}
// ---------------------------------------------------------------------
if (($segments[0] ?? '') === 'products' && isset($segments[1])) {
    $product_slug = $segments[1];
    $product_not_found = false;
    ob_start();
    include __DIR__ . '/../pages/product-detail.php';
    $page_body = ob_get_clean();

    if ($product_not_found) {
        http_response_code(404);
        ob_start();
        include __DIR__ . '/../pages/404.php';
        $page_body = ob_get_clean();
    }

    include __DIR__ . '/../includes/site-head.php';
    include __DIR__ . '/../includes/header.php';
    echo '<main id="main-content">' . $page_body . '</main>';
    include __DIR__ . '/../includes/footer.php';
    include __DIR__ . '/../includes/site-foot.php';
    exit;
}

// ---------------------------------------------------------------------
// Solutions landing pages: /solutions/{slug}
// ---------------------------------------------------------------------
$solutionPages = [
    'startups' => 'solutions-startups',
    'saas'     => 'solutions-saas',
];
if (($segments[0] ?? '') === 'solutions' && isset($segments[1])) {
    $solutionFile = $solutionPages[$segments[1]] ?? null;
    // Only serve a solutions page whose template actually exists; a missing
    // template used to render an empty 200 page (soft 404).
    if ($solutionFile !== null && !is_file(__DIR__ . '/../pages/' . $solutionFile . '.php')) {
        $solutionFile = null;
    }
    ob_start();
    if ($solutionFile === null) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
    } else {
        include __DIR__ . '/../pages/' . $solutionFile . '.php';
    }
    $page_body = ob_get_clean();

    include __DIR__ . '/../includes/site-head.php';
    include __DIR__ . '/../includes/header.php';
    echo '<main id="main-content">' . $page_body . '</main>';
    include __DIR__ . '/../includes/footer.php';
    include __DIR__ . '/../includes/site-foot.php';
    exit;
}

// ---------------------------------------------------------------------
// Business Services (linked from the header mega-menu; eligible pages are
// in the sitemap; not in the footer):
//   /business-services
//   /business-services/{service}
//   /business-services/global-incorporation
//   /business-services/jurisdictions[/{jurisdiction}]
// All content comes from includes/business-services.php; unknown slugs 404.
// ---------------------------------------------------------------------
if (($segments[0] ?? '') === 'business-services') {
    require_once __DIR__ . '/../includes/business-services.php';
    $bsDir = __DIR__ . '/../pages/business-services/';
    $bsFile = null;

    if (count($segments) === 1) {
        $bsFile = 'index.php';
    } elseif ($segments[1] === 'global-incorporation' && count($segments) === 2) {
        $bsFile = 'global-incorporation.php';
    } elseif ($segments[1] === 'jurisdictions' && count($segments) === 2) {
        $bsFile = 'jurisdictions.php';
    } elseif ($segments[1] === 'jurisdictions' && count($segments) === 3) {
        $bs_jurisdiction = bs_jurisdiction($segments[2]);
        $bsFile = $bs_jurisdiction ? 'jurisdiction.php' : null;
    } elseif (count($segments) === 2) {
        $bs_service = bs_service($segments[1]);
        $bsFile = $bs_service ? 'service.php' : null;
    }

    ob_start();
    if ($bsFile === null) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
    } else {
        include $bsDir . $bsFile;
    }
    $page_body = ob_get_clean();

    include __DIR__ . '/../includes/site-head.php';
    include __DIR__ . '/../includes/header.php';
    echo '<main id="main-content">' . $page_body . '</main>';
    include __DIR__ . '/../includes/footer.php';
    include __DIR__ . '/../includes/site-foot.php';
    exit;
}

// ---------------------------------------------------------------------
// Developer Hub child pages: /developers/{page}. The hub itself
// (/developers) is a public route below; unknown child slugs 404.
// ---------------------------------------------------------------------
if (($segments[0] ?? '') === 'developers' && isset($segments[1])) {
    $devPages = ['api-reference', 'authentication', 'webhooks', 'sdks', 'integration-guide'];
    $devFile = (count($segments) === 2 && in_array($segments[1], $devPages, true))
        ? __DIR__ . '/../pages/developers/' . $segments[1] . '.php'
        : null;
    ob_start();
    if ($devFile === null || !is_file($devFile)) {
        http_response_code(404);
        include __DIR__ . '/../pages/404.php';
    } else {
        include $devFile;
    }
    $page_body = ob_get_clean();

    include __DIR__ . '/../includes/site-head.php';
    include __DIR__ . '/../includes/header.php';
    echo '<main id="main-content">' . $page_body . '</main>';
    include __DIR__ . '/../includes/footer.php';
    include __DIR__ . '/../includes/site-foot.php';
    exit;
}

// ---------------------------------------------------------------------
// Legal pages: /legal/{slug}
// ---------------------------------------------------------------------
if (($segments[0] ?? '') === 'legal') {
    $legal_slug = $segments[1] ?? '';
    ob_start();
    include __DIR__ . '/../pages/legal.php';
    $page_body = ob_get_clean();

    include __DIR__ . '/../includes/site-head.php';
    include __DIR__ . '/../includes/header.php';
    echo '<main id="main-content">' . $page_body . '</main>';
    include __DIR__ . '/../includes/footer.php';
    include __DIR__ . '/../includes/site-foot.php';
    exit;
}

// ---------------------------------------------------------------------
// Public marketing pages.
// ---------------------------------------------------------------------
$publicRoutes = [
    ''                 => 'home',
    'about'            => 'about',
    'technology'       => 'technology',
    'agentic-ai'       => 'agenticai',
    'trust'            => 'trust',
    'leadership'       => 'leadership',
    'solutions'        => 'solutions',
    'products'         => 'products',
    'pricing'          => 'pricing',
    'developers'       => 'developers',
    'sandbox'          => 'sandbox',
    'ai-governance'    => 'ai-governance',
    'partners'         => 'partners',
    'support'          => 'support',
    'contact'          => 'contact',
    'careers'          => 'careers',
    'security'         => 'security-compliance',
    'blog'             => 'blog',
    'login'            => 'home',
    'forgot-password'  => 'forgot-password',
    'reset-password'   => 'reset-password',
];

$pageSlug = $publicRoutes[$path] ?? null;
$notFound = false;

if ($pageSlug === null) {
    http_response_code(404);
    $pageFile = __DIR__ . '/../pages/404.php';
    $notFound = true;
} else {
    $pageFile = __DIR__ . '/../pages/' . $pageSlug . '.php';
    if (!is_file($pageFile)) {
        http_response_code(404);
        $pageFile = __DIR__ . '/../pages/404.php';
        $notFound = true;
    }
}

ob_start();
include $pageFile;
$page_body = ob_get_clean();

// /login opens the login panel over the homepage; point search engines
// at the homepage itself rather than a second copy of it.
if ($path === 'login' && isset($page_meta)) {
    $page_meta['canonical'] = site_url('/');
}

include __DIR__ . '/../includes/site-head.php';
include __DIR__ . '/../includes/header.php';
echo '<main id="main-content">' . $page_body . '</main>';
include __DIR__ . '/../includes/footer.php';
include __DIR__ . '/../includes/site-foot.php';
