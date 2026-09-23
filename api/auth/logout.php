<?php
/**
 * POST /api/auth/logout
 */
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

logout_user();
json_response(['ok' => true]);
