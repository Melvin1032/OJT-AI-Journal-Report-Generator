<?php
/**
 * Logging System
 * Provides structured logging for application events
 */

class Logger {
    private static $logFile = __DIR__ . '/../storage/logs/app.log';
    private static $errorLogFile = __DIR__ . '/../storage/logs/error.log';
    private static $accessLogFile = __DIR__ . '/../storage/logs/access.log';
    
    /**
     * Initialize logger
     */
    private static function init() {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Write log entry
     */
    private static function write($level, $message, $context = [], $file = null) {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        // Add request ID for tracking
        $requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? substr(md5(uniqid()), 0, 8);
        
        // Add IP address (anonymized for privacy)
        $ip = self::anonymizeIP($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        $logLine = "[{$timestamp}] [{$level}] [{$requestId}] [{$ip}] {$message}{$contextStr}\n";
        
        $targetFile = $file ?? self::$logFile;
        
        // Ensure thread-safe writing
        $handle = fopen($targetFile, 'a');
        if ($handle) {
            flock($handle, LOCK_EX);
            fwrite($handle, $logLine);
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        
        // Rotate logs if too large (10MB)
        self::rotateLog($targetFile);
    }
    
    /**
     * Anonymize IP address for privacy
     */
    private static function anonymizeIP($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Remove last octet for IPv4
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $parts[3] = 'xxx';
                return implode('.', $parts);
            }
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Truncate IPv6
            return substr($ip, 0, strrpos($ip, ':')) . ':xxxx';
        }
        return 'unknown';
    }
    
    /**
     * Rotate log file if too large
     */
    private static function rotateLog($file, $maxSize = 10485760) {
        if (file_exists($file) && filesize($file) > $maxSize) {
            $backupFile = $file . '.' . date('Y-m-d-His');
            rename($file, $backupFile);
            
            // Keep only last 5 rotated logs
            $pattern = preg_quote($file, '/') . '\.\d{4}-\d{2}-\d{2}-\d{6}';
            $files = glob($file . '.*');
            if (count($files) > 5) {
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                array_walk(array_slice($files, 5), 'unlink');
            }
        }
    }
    
    /**
     * Log INFO level message
     */
    public static function info($message, $context = []) {
        self::write('INFO', $message, $context);
    }
    
    /**
     * Log WARNING level message
     */
    public static function warning($message, $context = []) {
        self::write('WARNING', $message, $context);
    }
    
    /**
     * Log ERROR level message
     */
    public static function error($message, $context = []) {
        self::write('ERROR', $message, $context, self::$errorLogFile);
    }
    
    /**
     * Log DEBUG level message
     */
    public static function debug($message, $context = []) {
        if (getenv('DEBUG_MODE') === 'true') {
            self::write('DEBUG', $message, $context);
        }
    }
    
    /**
     * Log ACCESS/AUDIT level message
     */
    public static function access($message, $context = []) {
        self::write('ACCESS', $message, $context, self::$accessLogFile);
    }
    
    /**
     * Log API request
     */
    public static function apiRequest($action, $result, $duration = 0) {
        self::info('API Request', [
            'action' => $action,
            'result' => $result,
            'duration_ms' => $duration,
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }
    
    /**
     * Log security event
     */
    public static function security($event, $context = []) {
        self::write('SECURITY', $event, $context, self::$errorLogFile);
    }
    
    /**
     * Get recent log entries
     */
    public static function getRecent($lines = 50, $type = 'app') {
        $file = $type === 'error' ? self::$errorLogFile : self::$logFile;
        
        if (!file_exists($file)) {
            return [];
        }
        
        $file = new SplFileObject($file, 'r');
        $file->seek(PHP_INT_MAX);
        $total = $file->key();
        
        $lines = min($lines, $total);
        $file->seek($total - $lines);
        
        $result = [];
        while (!$file->eof()) {
            $line = $file->current();
            if (!empty(trim($line))) {
                $result[] = $line;
            }
            $file->next();
        }
        
        return $result;
    }
    
    /**
     * Clear old logs
     */
    public static function clearOldLogs($daysOld = 30) {
        $logDir = dirname(self::$logFile);
        $files = glob($logDir . '/*');
        $removed = 0;
        $cutoff = time() - ($daysOld * 86400);
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
                $removed++;
            }
        }
        
        return $removed;
    }
}

// Set up global error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        Logger::error('PHP Error', [
            'message' => $message,
            'severity' => $severity,
            'file' => $file,
            'line' => $line
        ]);
    }
});

// Set up global exception handler
set_exception_handler(function($exception) {
    Logger::error('Uncaught Exception', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    // Re-throw for normal handling
    throw $exception;
});

// Log script shutdown
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        Logger::error('Fatal Error', [
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});
