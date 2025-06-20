<?php
// ErrorLogger.php - Advanced Error Logging System

class ErrorLogger {
    private $log_file;
    private $external_url;
    private $max_log_size;
    private $enable_external_logging;
    
    public function __construct($log_file = null, $external_url = null) {
        $this->log_file = $log_file ?: __DIR__ . '/../logs/error.log';
        $this->external_url = $external_url;
        $this->max_log_size = 10 * 1024 * 1024; // 10MB
        $this->enable_external_logging = !empty($external_url);
        
        // Create logs directory if it doesn't exist
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
    }
    
    /**
     * Log error to file and optionally to external service
     */
    public function logError($error_type, $message, $file = '', $line = '', $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $user_ip = $this->getUserIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $session_id = session_id() ?? 'No Session';
        
        // Format error entry
        $log_entry = [
            'timestamp' => $timestamp,
            'type' => $error_type,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'ip' => $user_ip,
            'user_agent' => $user_agent,
            'request_uri' => $request_uri,
            'session_id' => $session_id,
            'context' => $context
        ];
        
        // Log to file
        $this->logToFile($log_entry);
        
        // Log to external service if enabled
        if ($this->enable_external_logging) {
            $this->logToExternal($log_entry);
        }
        
        return true;
    }
    
    /**
     * Log to local file
     */
    private function logToFile($log_entry) {
        // Check file size and rotate if necessary
        if (file_exists($this->log_file) && filesize($this->log_file) > $this->max_log_size) {
            $this->rotateLogFile();
        }
        
        // Format for file logging
        $file_entry = sprintf(
            "[%s] %s: %s in %s:%s | IP: %s | URI: %s | Session: %s\n",
            $log_entry['timestamp'],
            $log_entry['type'],
            $log_entry['message'],
            $log_entry['file'],
            $log_entry['line'],
            $log_entry['ip'],
            $log_entry['request_uri'],
            $log_entry['session_id']
        );
        
        // Add context if available
        if (!empty($log_entry['context'])) {
            $file_entry .= "Context: " . json_encode($log_entry['context']) . "\n";
        }
        
        $file_entry .= str_repeat('-', 80) . "\n";
        
        // Write to file
        file_put_contents($this->log_file, $file_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log to external service
     */
    private function logToExternal($log_entry) {
        try {
            $payload = json_encode($log_entry);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($payload),
                        'User-Agent: ErrorLogger/1.0'
                    ],
                    'content' => $payload,
                    'timeout' => 5 // 5 seconds timeout
                ]
            ]);
            
            $result = file_get_contents($this->external_url, false, $context);
            
            // Log success to local file
            if ($result !== false) {
                $this->logToFile([
                    'timestamp' => date('Y-m-d H:i:s'),
                    'type' => 'INFO',
                    'message' => 'Error successfully sent to external service',
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'ip' => $this->getUserIP(),
                    'user_agent' => '',
                    'request_uri' => '',
                    'session_id' => session_id() ?? 'No Session',
                    'context' => ['external_url' => $this->external_url]
                ]);
            }
        } catch (Exception $e) {
            // If external logging fails, log the failure locally
            $this->logToFile([
                'timestamp' => date('Y-m-d H:i:s'),
                'type' => 'ERROR',
                'message' => 'Failed to send error to external service: ' . $e->getMessage(),
                'file' => __FILE__,
                'line' => __LINE__,
                'ip' => $this->getUserIP(),
                'user_agent' => '',
                'request_uri' => '',
                'session_id' => session_id() ?? 'No Session',
                'context' => ['external_url' => $this->external_url]
            ]);
        }
    }
    
    /**
     * Rotate log file when it gets too large
     */
    private function rotateLogFile() {
        $backup_file = $this->log_file . '.' . date('Y-m-d_H-i-s') . '.bak';
        rename($this->log_file, $backup_file);
        
        // Keep only last 5 backup files
        $this->cleanupOldBackups();
    }
    
    /**
     * Clean up old backup files
     */
    private function cleanupOldBackups() {
        $log_dir = dirname($this->log_file);
        $log_name = basename($this->log_file);
        
        $backup_files = glob($log_dir . '/' . $log_name . '.*.bak');
        
        if (count($backup_files) > 5) {
            // Sort by modification time
            usort($backup_files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remove oldest files
            $files_to_remove = array_slice($backup_files, 0, count($backup_files) - 5);
            foreach ($files_to_remove as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Get user IP address
     */
    private function getUserIP() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    /**
     * Get error statistics
     */
    public function getErrorStats($hours = 24) {
        if (!file_exists($this->log_file)) {
            return [
                'total_errors' => 0,
                'error_types' => [],
                'recent_errors' => []
            ];
        }
        
        $content = file_get_contents($this->log_file);
        $lines = explode("\n", $content);
        
        $cutoff_time = time() - ($hours * 3600);
        $stats = [
            'total_errors' => 0,
            'error_types' => [],
            'recent_errors' => []
        ];
        
        foreach ($lines as $line) {
            if (empty(trim($line)) || strpos($line, '[') !== 0) {
                continue;
            }
            
            // Parse timestamp
            if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                
                if ($timestamp >= $cutoff_time) {
                    $stats['total_errors']++;
                    
                    // Parse error type
                    if (preg_match('/\]\s*(.*?):\s*/', $line, $type_matches)) {
                        $error_type = $type_matches[1];
                        $stats['error_types'][$error_type] = ($stats['error_types'][$error_type] ?? 0) + 1;
                    }
                    
                    $stats['recent_errors'][] = $line;
                }
            }
        }
        
        // Limit recent errors to last 10
        $stats['recent_errors'] = array_slice(array_reverse($stats['recent_errors']), 0, 10);
        
        return $stats;
    }
    
    /**
     * Clear error logs
     */
    public function clearLogs() {
        if (file_exists($this->log_file)) {
            return file_put_contents($this->log_file, '') !== false;
        }
        return true;
    }
    
    /**
     * Set external logging URL
     */
    public function setExternalUrl($url) {
        $this->external_url = $url;
        $this->enable_external_logging = !empty($url);
    }
    
    /**
     * Enable/disable external logging
     */
    public function setExternalLogging($enabled) {
        $this->enable_external_logging = $enabled && !empty($this->external_url);
    }
}

// Global error logger instance
$GLOBALS['error_logger'] = new ErrorLogger();

/**
 * Helper function to log errors globally
 */
function logError($error_type, $message, $file = '', $line = '', $context = []) {
    if (isset($GLOBALS['error_logger'])) {
        return $GLOBALS['error_logger']->logError($error_type, $message, $file, $line, $context);
    }
    return false;
}

/**
 * Helper function to set external logging URL
 */
function setExternalLoggingUrl($url) {
    if (isset($GLOBALS['error_logger'])) {
        $GLOBALS['error_logger']->setExternalUrl($url);
    }
}

/**
 * Custom error handler
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
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
    
    logError($error_type, $errstr, $errfile, $errline, [
        'errno' => $errno,
        'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
    ]);
    
    // Don't execute PHP internal error handler
    return true;
}

/**
 * Custom exception handler
 */
function customExceptionHandler($exception) {
    logError('EXCEPTION', $exception->getMessage(), $exception->getFile(), $exception->getLine(), [
        'exception_class' => get_class($exception),
        'trace' => $exception->getTraceAsString()
    ]);
}

// Set custom error and exception handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// Example usage:
// To set external logging URL (optional):
// setExternalLoggingUrl('https://your-external-service.com/api/errors');

// To log custom errors:
// logError('CUSTOM', 'This is a custom error message', __FILE__, __LINE__, ['user_id' => 123]);
?>