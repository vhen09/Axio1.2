<?php
/**
 * Database Diagnostics Endpoint
 * Helps diagnose database connection issues on Render or production
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'environment' => [],
    'database' => [],
    'files' => [],
    'errors' => []
];

// Check environment variables
$diagnostics['environment']['RENDER'] = getenv('RENDER') ? 'SET' : 'NOT SET';
$diagnostics['environment']['DATABASE_URL'] = getenv('DATABASE_URL') ? 'SET (' . strlen(getenv('DATABASE_URL')) . ' chars)' : 'NOT SET';
$diagnostics['environment']['DB_HOST'] = getenv('DB_HOST') ? 'SET' : 'NOT SET';
$diagnostics['environment']['DB_NAME'] = getenv('DB_NAME') ? 'SET' : 'NOT SET';
$diagnostics['environment']['DB_USER'] = getenv('DB_USER') ? 'SET' : 'NOT SET';

// Try to connect to database
try {
    require_once __DIR__ . '/../config/database.php';
    $db = new Database();
    
    if ($db->isDemoMode()) {
        $diagnostics['database']['status'] = 'DEMO MODE (connection failed)';
        $diagnostics['database']['connection'] = 'FAILED';
    } else {
        $diagnostics['database']['status'] = 'CONNECTED';
        $diagnostics['database']['connection'] = 'SUCCESS';
        
        // Try a test query
        try {
            $connection = $db->getConnection();
            if ($connection) {
                $stmt = $connection->prepare("SELECT COUNT(*) as count FROM users");
                if ($stmt->execute()) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $diagnostics['database']['users_table'] = 'EXISTS - ' . $result['count'] . ' users';
                } else {
                    $diagnostics['database']['users_table'] = 'TABLE ERROR';
                }
            }
        } catch (Exception $e) {
            $diagnostics['database']['query_test'] = 'FAILED: ' . $e->getMessage();
        }
    }
} catch (Exception $e) {
    $diagnostics['database']['connection'] = 'FAILED';
    $diagnostics['errors'][] = 'Database error: ' . $e->getMessage();
}

// Check required files
$requiredFiles = [
    '../config/database.php',
    '../models/User.php',
    './auth.php',
    '../../database/schema.sql'
];

foreach ($requiredFiles as $file) {
    $path = __DIR__ . '/' . $file;
    $diagnostics['files'][$file] = file_exists($path) ? 'EXISTS' : 'MISSING';
}

// Read recent error logs
$logFile = __DIR__ . '/../logs/system.log';
if (file_exists($logFile)) {
    $logs = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -15);
    $diagnostics['logs'] = array_filter($logs, function($line) {
        return strpos($line, 'Database') !== false || 
               strpos($line, 'connection') !== false ||
               strpos($line, 'ERROR') !== false ||
               strpos($line, 'FAILED') !== false;
    });
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
