<?php
/**
 * AZ-305 Helper Configuration
 */

// Application settings
define('APP_NAME', 'AZ-305 Certification Helper');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'production');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('SESSIONS_PATH', DATA_PATH . '/sessions');

// Database settings (JSON file based)
define('QUESTIONS_FILE', DATA_PATH . '/questions.json');

// API settings
define('API_BASE_PATH', '/api');
define('ALLOW_CORS', true);
define('CORS_ORIGINS', '*');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('SESSION_EXPIRY_TIME', 86400); // 24 hours

// Security settings
define('HTTPS_ONLY', true);
define('CONTENT_SECURITY_POLICY', "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;");

// Error handling
define('DEBUG', APP_ENV === 'development');

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Create necessary directories
if (!is_dir(SESSIONS_PATH)) {
    @mkdir(SESSIONS_PATH, 0755, true);
}

// Set default timezone
date_default_timezone_set('UTC');

// Set JSON response header by default
header('Content-Type: application/json; charset=utf-8');

// CORS headers
if (ALLOW_CORS) {
    header('Access-Control-Allow-Origin: ' . CORS_ORIGINS);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 3600');
}

// Security headers
if (HTTPS_ONLY && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Content-Security-Policy: ' . CONTENT_SECURITY_POLICY);
?>
