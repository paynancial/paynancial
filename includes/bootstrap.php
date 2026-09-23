<?php
/**
 * Application bootstrap. Included once by public/index.php.
 */

declare(strict_types=1);

error_reporting(E_ALL);

$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    die('Configuration missing. Copy config/config.example.php to config/config.php and set your values.');
}
require_once $configFile;

ini_set('display_errors', APP_DEBUG ? '1' : '0');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/otp.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/partner.php';
require_once __DIR__ . '/partner-assistant.php';
require_once __DIR__ . '/security-panel.php';

/**
 * Any exception that escapes a page (most commonly a database outage) ends
 * up here instead of leaking a stack trace. Pages that can degrade
 * gracefully (e.g. pricing, careers, blog) catch DB errors locally before
 * this ever runs.
 */
set_exception_handler(static function (Throwable $e): void {
    error_log('[Paynancial] Uncaught exception: ' . $e->getMessage());
    if (!headers_sent()) {
        http_response_code(503);
    }
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<pre style="padding:24px;font-family:monospace;white-space:pre-wrap;">'
            . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString())
            . '</pre>';
        return;
    }
    echo '<!doctype html><html lang="en"><head><meta charset="UTF-8"><title>Service Unavailable | Paynancial</title></head>'
        . '<body style="font-family:sans-serif;text-align:center;padding:96px 24px;">'
        . '<h1>We\'ll be right back.</h1><p>Paynancial is temporarily unavailable. Please try again shortly.</p>'
        . '</body></html>';
});

security_headers();
start_secure_session();
