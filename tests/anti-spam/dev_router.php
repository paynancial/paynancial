<?php
// Router for PHP's built-in server — LOCAL TESTING ONLY. Serves real files
// from public/ directly and sends everything else to the front controller.
$root = dirname(__DIR__, 2) . '/public';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($path !== '/' && is_file($root . $path)) {
    return false;
}
require $root . '/index.php';
