<?php
/**
 * AXIO Database Health Check & Monitoring
 * 
 * Endpoint: /backend/api/health.php
 * 
 * Returns JSON with database status, connection pool info, and performance metrics
 * Used by: Load balancers, monitoring systems, deployment health checks
 * 
 * @version 1.0
 * @last_updated March 24, 2026
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database_v2.php';

$response = [
    'timestamp' => date('c'),
    'service' => 'AXIO API',
    'status' => 'unknown',
    'database' => [],
    'performance' => [],
    'errors' => [],
];

try {
    $db = Database::getInstance();
    $dbHealth = $db->healthCheck();

    // Database status
    $response['database'] = [
        'connected' => $db->isConnected(),
        'type' => $db->getDbType(),
        'demo_mode' => $db->isDemoMode(),
        'healthy' => $dbHealth['healthy'],
        'message' => $dbHealth['message'],
        'version' => $db->getVersion() ?? 'unknown',
        'database_name' => $db->getDatabaseName(),
    ];

    // Performance metrics
    if ($dbHealth['healthy']) {
        // Connection test
        $startTime = microtime(true);
        $db->query("SELECT 1");
        $connectionTime = (microtime(true) - $startTime) * 1000;

        // Query test
        $startTime = microtime(true);
        $result = $db->queryScalar("SELECT COUNT(*) FROM users", []);
        $queryTime = (microtime(true) - $startTime) * 1000;

        $response['performance'] = [
            'connection_time_ms' => round($connectionTime, 2),
            'query_time_ms' => round($queryTime, 2),
            'users_in_system' => $result ?: 0,
        ];

        // Overall status
        $response['status'] = 'healthy';
    } else {
        $response['status'] = 'degraded';
        $response['errors'][] = $dbHealth['message'];
    }

} catch (Exception $e) {
    $response['status'] = 'unhealthy';
    $response['errors'][] = $e->getMessage();
    $response['database'] = [
        'connected' => false,
        'healthy' => false,
        'message' => 'Database error',
    ];
}

// Add additional info based on environment
if (getenv('APP_ENV') === 'production') {
    // Production: minimal info for security
    unset($response['database']['database_name']);
    unset($response['database']['version']);
} else {
    // Development: full info
    $response['debug'] = [
        'pid' => getmypid(),
        'memory_mb' => round(memory_get_usage() / 1048576, 2),
        'peak_memory_mb' => round(memory_get_peak_usage() / 1048576, 2),
        'execution_time_ms' => round((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000, 2),
    ];
}

http_response_code($response['status'] === 'healthy' ? 200 : 503);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
