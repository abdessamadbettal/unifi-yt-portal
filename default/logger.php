<?php

/**
 * Logger utility for UniFi Portal
 * Logs all important events to a file for production monitoring
 */

class Logger {
    private static $logFile = null;
    private static $logDir = null;

    /**
     * Initialize logger with log directory
     */
    public static function init() {
        self::$logDir = __DIR__ . '/../logs';
        
        // Create logs directory if it doesn't exist
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        
        // Set log file with date
        self::$logFile = self::$logDir . '/portal_' . date('Y-m-d') . '.log';
    }

    /**
     * Write log entry
     */
    private static function write($level, $message, $context = []) {
        if (self::$logFile === null) {
            self::init();
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $sessionId = session_id() ?? 'no-session';
        
        // Build log message
        $logMessage = sprintf(
            "[%s] [%s] [IP: %s] [Session: %s] %s",
            $timestamp,
            strtoupper($level),
            $ip,
            substr($sessionId, 0, 8),
            $message
        );

        // Add context if provided
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context);
        }

        $logMessage .= PHP_EOL;

        // Write to file
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::write('info', $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::write('warning', $message, $context);
    }

    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::write('error', $message, $context);
    }

    /**
     * Log debug message
     */
    public static function debug($message, $context = []) {
        self::write('debug', $message, $context);
    }

    /**
     * Log success message
     */
    public static function success($message, $context = []) {
        self::write('success', $message, $context);
    }
}

// Initialize logger
Logger::init();
