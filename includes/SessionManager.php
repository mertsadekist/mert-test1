<?php
// SessionManager.php - Secure session management

class SessionManager {
    private static $instance = null;
    private $config;

    private function __construct() {
        $this->config = Config::getInstance();
        $this->initializeSession();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', $this->config->get('security.session_lifetime', 3600));

            session_start();
        }
    }

    public function validateSession() {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }

        $maxLifetime = $this->config->get('security.session_lifetime', 3600);
        if (time() - $_SESSION['last_activity'] > $maxLifetime) {
            $this->destroySession();
            return false;
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }

    public function regenerateSession() {
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // Every 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    public function destroySession() {
        // Unset all session variables
        $_SESSION = [];

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/', '', true, true);
        }

        // Destroy the session
        session_destroy();
    }

    public function setSessionValue($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function getSessionValue($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function hasSessionValue($key) {
        return isset($_SESSION[$key]);
    }

    public function removeSessionValue($key) {
        unset($_SESSION[$key]);
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    private function __wakeup() {}
}