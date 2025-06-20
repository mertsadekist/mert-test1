<?php
// bootstrap.php - Application initialization

// Set default timezone
date_default_timezone_set('UTC');

// Load configuration
require_once __DIR__ . '/config.php';
$config = Config::getInstance();

// Initialize error handling
require_once __DIR__ . '/includes/Logger.php';
require_once __DIR__ . '/includes/ErrorHandler.php';
ErrorHandler::getInstance();

// Set error reporting based on environment
if ($config->get('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
}

// Initialize security components
require_once __DIR__ . '/includes/SecurityHelper.php';
require_once __DIR__ . '/includes/SessionManager.php';

$security = SecurityHelper::getInstance();
$session = SessionManager::getInstance();

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'");
header('Referrer-Policy: strict-origin-when-cross-origin');

// Create required directories if they don't exist
$directories = [
    __DIR__ . '/logs',
    __DIR__ . '/uploads',
    __DIR__ . '/cache',
    __DIR__ . '/sessions'
];

foreach ($directories as $directory) {
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Set custom session save handler if needed
if ($config->get('security.custom_session_handler', false)) {
    ini_set('session.save_handler', 'files');
    session_save_path(__DIR__ . '/sessions');
}

// Initialize database connection
require_once __DIR__ . '/db_connection.php';
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    Logger::getInstance()->error('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}

// Set up autoloading if needed
spl_autoload_register(function ($class) {
    $prefix = '';
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize any other required components
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';