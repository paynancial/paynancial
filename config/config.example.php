<?php
/**
 * Paynancial — application configuration example.
 * Copy this file to config.php and fill in real values.
 * config.php is git-ignored and must never be committed.
 */

// ---------------------------------------------------------------------
// Environment
// ---------------------------------------------------------------------
define('APP_ENV', 'production');           // 'production' | 'development'
define('APP_DEBUG', false);                // never true in production
define('APP_URL', 'https://paynancial.com');
define('APP_NAME', 'PAYNANCIAL');

// ---------------------------------------------------------------------
// Database (MySQL via PDO)
// ---------------------------------------------------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'change-me');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------
// Session / security
// ---------------------------------------------------------------------
define('SESSION_NAME', 'paynancial_session');
define('SESSION_LIFETIME_SECONDS', 1800);      // 30 min idle timeout
define('SESSION_COOKIE_SECURE', true);         // requires HTTPS in production
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('PASSWORD_RESET_TOKEN_TTL_MINUTES', 30);
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 5);
define('OTP_MAX_ATTEMPTS', 5);
define('DEVICE_COOKIE_NAME', 'pyn_device');
define('DEVICE_COOKIE_DAYS', 180);

// A long, random, per-install secret used to key CSRF/HMAC operations.
// Generate with: bin2hex(random_bytes(32))
define('APP_SECRET', 'replace-with-bin2hex-random_bytes-32');

// ---------------------------------------------------------------------
// Mail (used for enquiry notifications, password resets, OTP)
// ---------------------------------------------------------------------
define('MAIL_FROM_ADDRESS', 'no-reply@paynancial.com');
define('MAIL_FROM_NAME', 'Paynancial');
define('MAIL_SALES_TO', 'hello@paynancial.com');

// ---------------------------------------------------------------------
// Uploads
// ---------------------------------------------------------------------
define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_CV_TYPES', ['pdf', 'doc', 'docx']);
define('UPLOAD_DIR', __DIR__ . '/../storage/uploads');
