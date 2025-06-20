<?php
// ErrorHandler.php - Enhanced Error Handling with External Logging

require_once __DIR__ . '/ErrorLogger.php';

class ErrorHandler {
    private static $instance = null;
    private $error_logger;
    private $display_errors;
    
    private function __construct() {
        $this->display_errors = false; // Set to false in production
        
        // Initialize the advanced error logger
        $this->error_logger = new ErrorLogger();
        
        // Optional: Set external logging URL
        // Uncomment and set your external service URL
        // $this->error_logger->setExternalUrl('https://your-external-service.com/api/errors');
        
        $this->setupErrorHandling();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function setupErrorHandling() {
        // Set error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', $this->display_errors ? '1' : '0');
        ini_set('log_errors', '1');
        
        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }
    
    public function handleError($errno, $errstr, $errfile, $errline) {
        $error_types = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];
        
        $error_type = $error_types[$errno] ?? 'UNKNOWN';
        
        $this->error_logger->logError($error_type, $errstr, $errfile, $errline, [
            'errno' => $errno,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ]);
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    public function handleException($exception) {
        $this->error_logger->logError(
            'EXCEPTION',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            [
                'exception_class' => get_class($exception),
                'trace' => $exception->getTraceAsString()
            ]
        );
    }
    
    public function handleFatalError() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->error_logger->logError(
                'FATAL',
                $error['message'],
                $error['file'],
                $error['line'],
                ['error_type' => $error['type']]
            );
        }
    }
    
    public function setDisplayErrors($display) {
        $this->display_errors = $display;
        ini_set('display_errors', $display ? '1' : '0');
    }
    
    public function setExternalLoggingUrl($url) {
        $this->error_logger->setExternalUrl($url);
    }
    
    public function getErrorLogger() {
        return $this->error_logger;
    }
    
    public function getLogFile() {
        return $this->error_logger->log_file ?? __DIR__ . '/../logs/error.log';
    }
    
    public function getErrorStats($hours = 24) {
        return $this->error_logger->getErrorStats($hours);
    }
    
    public function clearLogs() {
        return $this->error_logger->clearLogs();
    }
}

// Initialize error handler
ErrorHandler::getInstance();

// Helper functions for easy access
function logCustomError($message, $context = []) {
    $handler = ErrorHandler::getInstance();
    $logger = $handler->getErrorLogger();
    return $logger->logError('CUSTOM', $message, '', '', $context);
}

function setExternalErrorLogging($url) {
    $handler = ErrorHandler::getInstance();
    $handler->setExternalLoggingUrl($url);
}

?>