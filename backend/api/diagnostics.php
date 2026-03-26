<?php
// This file helps diagnose database configuration issues on Render and production
// SECURITY: Only show this information in development/private logs on production

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment_type' => 'UNKNOWN',
    'database_configured' => false,
    'environment_variables' => [],
    'errors' => []
];

// Check which environment we're in
$isRender = isset($_ENV['RENDER']) || getenv('RENDER');
$isDatabaseUrlSet = isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL');
$isDbHostSet = isset($_ENV['DB_HOST']) || getenv('DB_HOST');

if ($isRender) {
    $diagnostics['environment_type'] = 'RENDER';
}

if ($isDatabaseUrlSet) {
    $diagnostics['environment_type'] = 'DATABASE_URL_ENV';
    $url = getenv('DATABASE_URL') ?: $_ENV['DATABASE_URL'];
    // Redact password in diagnostic output
    $redacted = preg_replace('/([^:]*):([^@]*)@/', '$1:***@', $url);
    $diagnostics['environment_variables']['DATABASE_URL'] = $redacted;
} elseif ($isDbHostSet) {
    $diagnostics['environment_type'] = 'DB_HOST_ENV';
    $diagnostics['environment_variables']['DB_HOST'] = getenv('DB_HOST') ?: $_ENV['DB_HOST'];
    $diagnostics['environment_variables']['DB_NAME'] = getenv('DB_NAME') ?: 'NOT SET';
    $diagnostics['environment_variables']['DB_USER'] = getenv('DB_USER') ?: 'NOT SET';
    $diagnostics['environment_variables']['DB_PASS'] = getenv('DB_PASS') ? '***' : 'NOT SET';
} else {
    $diagnostics['environment_type'] = 'LOCALHOST_DEFAULT';
    $diagnostics['errors'][] = 'No environment variables configured. Using localhost defaults.';
}

// Test database connection
try {
    $db = new Database();
    if ($db->isDemoMode()) {
        $diagnostics['database_configured'] = false;
        $diagnostics['errors'][] = 'CRITICAL: Database connection failed! System will run in demo mode (INSECURE).';
    } else {
        $diagnostics['database_configured'] = true;
        $diagnostics['status'] = 'Database connected successfully';
    }
} catch (Exception $e) {
    $diagnostics['database_configured'] = false;
    $diagnostics['errors'][] = 'Exception during database initialization: ' . $e->getMessage();
}

// PHP Configuration
$diagnostics['php_version'] = phpversion();
$diagnostics['server_name'] = $_SERVER['SERVER_NAME'] ?? 'UNKNOWN';
$diagnostics['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'UNKNOWN';

http_response_code($diagnostics['database_configured'] ? 200 : 500);
echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
