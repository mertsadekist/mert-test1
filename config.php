<?php
// config.php - Configuration management and environment variables loader

class Config {
    private static $instance = null;
    private $config = [];

    private function __construct() {
        $this->loadEnvFile();
        $this->initializeConfig();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvFile() {
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2) + [null, null];
                if ($key && $value) {
                    $key = trim($key);
                    $value = trim($value, " \t\n\r\0\x0B\"");
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }

    private function initializeConfig() {
        // Database settings
        $this->config['db'] = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ];

        // Security settings
        $this->config['security'] = [
            'session_lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 3600),
            'max_login_attempts' => (int)(getenv('MAX_LOGIN_ATTEMPTS') ?: 5),
            'lockout_time' => (int)(getenv('LOCKOUT_TIME') ?: 300),
            'csrf_token_lifetime' => 3600,
            'password_min_length' => 8,
            'require_special_chars' => true
        ];

        // File upload settings
        $this->config['upload'] = [
            'max_size' => (int)(getenv('MAX_UPLOAD_SIZE') ?: 5242880),
            'allowed_types' => explode(',', getenv('ALLOWED_FILE_TYPES') ?: 'xlsx,xls'),
            'upload_path' => __DIR__ . '/uploads'
        ];

        // Application settings
        $this->config['app'] = [
            'name' => getenv('APP_NAME') ?: 'IST Real Estate',
            'url' => getenv('APP_URL') ?: 'http://localhost',
            'debug' => (bool)(getenv('APP_DEBUG') ?: false)
        ];
    }

    public function get($key, $default = null) {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $key) {
            if (!isset($config[$key])) {
                return $default;
            }
            $config = $config[$key];
        }

        return $config;
    }

    public static function init() {
        return self::getInstance();
    }
}

// Initialize configuration
Config::init();