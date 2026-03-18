<?php
// Status/diagnostic endpoint - shows system health and configuration
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$db = new Database();
$demoMode = $db->isDemoMode();
$currentEnv = php_uname('s');
$isProduction = isset($_ENV['RENDER']) || getenv('RENDER') || 
                isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL');

$status = [
    'status' => $demoMode ? 'demo_mode' : 'production',
    'demo_mode_enabled' => $demoMode,
    'is_production_env' => (bool)$isProduction,
    'environment' => $currentEnv,
    'database' => $demoMode ? 'NOT CONNECTED' : 'CONNECTED',
    'session_active' => session_status() === PHP_SESSION_ACTIVE,
    'php_version' => phpversion(),
    'server_time' => date('Y-m-d H:i:s'),
    'warnings' => []
];

// Check environment variables
if ($isProduction && $demoMode) {
    $status['warnings'][] = 'CRITICAL: Production environment detected but database connection failed! Check DATABASE_URL or DB_* environment variables.';
}

if ($demoMode && !$isProduction) {
    $status['status'] = 'development_demo';
    $status['info'] = 'Running in demo mode for local development';
}

echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
