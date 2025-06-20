<?php
// Logger.php - Application logging system

class Logger {
    private static $instance = null;
    private $config;
    private $logFile;
    private $logLevel;

    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';

    private function __construct() {
        $this->config = Config::getInstance();
        $this->initializeLogger();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeLogger() {
        // Set default log file path
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Create daily log file
        $this->logFile = $logDir . '/app-' . date('Y-m-d') . '.log';
        
        // Set log level based on config
        $this->logLevel = $this->config->get('app.debug') ? self::LEVEL_DEBUG : self::LEVEL_INFO;

        // Rotate logs if needed
        $this->rotateLogFiles($logDir);
    }

    private function rotateLogFiles($logDir) {
        // Keep logs for 30 days
        $maxAge = 30 * 24 * 60 * 60;
        $files = glob($logDir . '/app-*.log');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileAge = time() - filemtime($file);
                if ($fileAge > $maxAge) {
                    unlink($file);
                }
            }
        }
    }

    private function formatMessage($level, $message, array $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest';
        
        // Replace context placeholders
        foreach ($context as $key => $value) {
            $message = str_replace('{' . $key . '}', $this->formatValue($value), $message);
        }

        return sprintf(
            "[%s] [%s] [IP: %s] [User: %s] %s\n",
            $timestamp,
            $level,
            $ip,
            $userId,
            $message
        );
    }

    private function formatValue($value) {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        return (string) $value;
    }

    private function writeLog($level, $message, array $context = []) {
        $logMessage = $this->formatMessage($level, $message, $context);
        
        if (!error_log($logMessage, 3, $this->logFile)) {
            error_log("Failed to write to log file: {$this->logFile}");
        }
    }

    public function debug($message, array $context = []) {
        if ($this->logLevel === self::LEVEL_DEBUG) {
            $this->writeLog(self::LEVEL_DEBUG, $message, $context);
        }
    }

    public function info($message, array $context = []) {
        $this->writeLog(self::LEVEL_INFO, $message, $context);
    }

    public function warning($message, array $context = []) {
        $this->writeLog(self::LEVEL_WARNING, $message, $context);
    }

    public function error($message, array $context = []) {
        $this->writeLog(self::LEVEL_ERROR, $message, $context);
    }

    public function logException(\Exception $e) {
        $context = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
        $this->error($e->getMessage(), $context);
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    private function __wakeup() {}
}