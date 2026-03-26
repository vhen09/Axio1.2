<?php
/**
 * API Safety Handler
 * Ensures all API endpoints return JSON-only responses, never HTML errors
 * Include this file at the TOP of every API endpoint
 */

// Start output buffering to trap any accidental output
if (ob_get_level() === 0) {
    ob_start();
}

// Suppress default PHP error displays
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set custom error handler to prevent HTML output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile:$errline");
    // Return true to suppress default PHP error handling
    return true;
});

// Set custom exception handler
set_exception_handler(function($exception) {
    error_log('Uncaught Exception: ' . $exception->getMessage());
    ob_clean();
    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'error' => 'System error occurred',
        'message' => 'Please contact support'
    ]);
    exit;
});

// Register shutdown handler to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log('FATAL ERROR: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
        ob_clean();
        http_response_code(500);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'error' => 'Fatal system error',
            'message' => 'Please contact support'
        ]);
    } else {
        // Flush normally buffered content if no error occurred
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }
});
?>
