<?php
// SecurityHelper.php - Security related functions

class SecurityHelper {
    private static $instance = null;
    private $config;
    private $loginAttempts = [];

    private function __construct() {
        $this->config = Config::getInstance();
        $this->initializeSecurityHeaders();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeSecurityHeaders() {
        // Set security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'");
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    public function validatePassword($password) {
        $minLength = $this->config->get('security.password_min_length', 8);
        $requireSpecial = $this->config->get('security.require_special_chars', true);

        if (strlen($password) < $minLength) {
            return false;
        }

        if ($requireSpecial && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    public function checkLoginAttempts($email) {
        $maxAttempts = $this->config->get('security.max_login_attempts', 5);
        $lockoutTime = $this->config->get('security.lockout_time', 300);

        if (!isset($this->loginAttempts[$email])) {
            return true;
        }

        $attempts = $this->loginAttempts[$email];
        if ($attempts['count'] >= $maxAttempts) {
            $timePassed = time() - $attempts['last_attempt'];
            if ($timePassed < $lockoutTime) {
                return false;
            }
            // Reset attempts after lockout period
            unset($this->loginAttempts[$email]);
        }
        return true;
    }

    public function recordLoginAttempt($email, $success = false) {
        if (!isset($this->loginAttempts[$email])) {
            $this->loginAttempts[$email] = ['count' => 0, 'last_attempt' => 0];
        }

        if (!$success) {
            $this->loginAttempts[$email]['count']++;
        } else {
            unset($this->loginAttempts[$email]);
        }
        $this->loginAttempts[$email]['last_attempt'] = time();
    }

    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public function validateFileUpload($file, $allowedTypes = null) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return false;
        }

        $maxSize = $this->config->get('upload.max_size', 5242880); // 5MB default
        $allowedTypes = $allowedTypes ?: $this->config->get('upload.allowed_types', ['xlsx', 'xls']);

        // Check file size
        if ($file['size'] > $maxSize) {
            return false;
        }

        // Check file type
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedTypes)) {
            return false;
        }

        return true;
    }

    public function generateSecureFilename($originalName) {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return bin2hex(random_bytes(16)) . '.' . $ext;
    }

    public function enforceSSL() {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirectUrl, true, 301);
            exit();
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    private function __wakeup() {}
}