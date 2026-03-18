<?php
/**
 * Database Diagnostics - Run this to troubleshoot database connection issues
 * Access via: curl https://your-render-url/backend/diagnose-db.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "======================================\n";
echo "DATABASE CONNECTION DIAGNOSTICS\n";
echo "======================================\n\n";

// 1. Check environment variables
echo "1. ENVIRONMENT VARIABLES\n";
echo "------------------------\n";

$databaseUrl = getenv('DATABASE_URL');
$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
$render = getenv('RENDER');

echo "RENDER: " . ($render ? "YES ✓" : "NO ✗") . "\n";
echo "DATABASE_URL set: " . ($databaseUrl ? "YES ✓ (length: " . strlen($databaseUrl) . ")" : "NO ✗") . "\n";
echo "DB_HOST set: " . ($dbHost ? "YES ✓" : "NO ✗") . "\n";
echo "DB_NAME set: " . ($dbName ? "YES ✓" : "NO ✗") . "\n";
echo "DB_USER set: " . ($dbUser ? "YES ✓" : "NO ✗") . "\n";
echo "DB_PASS set: " . ($dbPass ? "YES ✓" : "NO ✗") . "\n";

// 2. Parse DATABASE_URL if it exists
echo "\n2. DATABASE_URL PARSING\n";
echo "------------------------\n";

if ($databaseUrl) {
    echo "Raw DATABASE_URL (first 50 chars): " . substr($databaseUrl, 0, 50) . "...\n\n";
    
    // Try to parse
    if (preg_match('/^mysql:\/\/([^:]+):(.*)@([^:\/]+)(?::(\d+))?\/(.+)$/', $databaseUrl, $matches)) {
        echo "✓ DATABASE_URL format is VALID\n";
        echo "  Username: " . ($matches[1] ? "***" : "NOT SET") . "\n";
        echo "  Password: " . ($matches[2] ? "***" : "EMPTY") . "\n";
        echo "  Host: " . $matches[3] . "\n";
        echo "  Port: " . ($matches[4] ?? "3306 (default)") . "\n";
        echo "  Database: " . $matches[5] . "\n";
    } else {
        echo "✗ DATABASE_URL format is INVALID\n";
        echo "  Expected format: mysql://user:pass@host:port/dbname\n";
    }
} else {
    echo "✗ DATABASE_URL not set\n";
    if ($dbHost && $dbName && $dbUser) {
        echo "  But individual vars are set, will use those instead\n";
    } else {
        echo "  And using fallback to localhost\n";
    }
}

// 3. Try to connect
echo "\n3. CONNECTION TEST\n";
echo "-------------------\n";

try {
    // Determine connection parameters
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'lean4_ai_app';
    $port = 3306;
    
    if ($databaseUrl) {
        if (preg_match('/^mysql:\/\/([^:]+):(.*)@([^:\/]+)(?::(\d+))?\/(.+)$/', $databaseUrl, $m)) {
            $user = urldecode($m[1]);
            $pass = urldecode($m[2]);
            $host = $m[3];
            $port = $m[4] ?? 3306;
            $dbname = $m[5];
        }
    } elseif ($dbHost) {
        $host = $dbHost;
        $dbname = $dbName ?? 'lean4_ai_app';
        $user = $dbUser ?? 'root';
        $pass = $dbPass ?? '';
    }
    
    echo "Attempting connection to: {$user}@{$host}:{$port}/{$dbname}\n";
    
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ CONNECTION SUCCESSFUL!\n";
    
    // 4. Check tables
    echo "\n4. DATABASE TABLES\n";
    echo "-------------------\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables found: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "  - {$table}\n";
    }
    
    // 5. Check users table
    echo "\n5. USERS TABLE\n";
    echo "---------------\n";
    
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        echo "Users in database: {$count}\n";
    } else {
        echo "✗ Users table NOT FOUND\n";
    }
    
} catch (PDOException $e) {
    echo "✗ CONNECTION FAILED\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "TROUBLESHOOTING:\n";
    echo "1. Is DATABASE_URL set on Render?\n";
    echo "   - Check Settings → Environment\n";
    echo "   - Format should be: mysql://user:pass@host:port/dbname\n\n";
    echo "2. Is database server running?\n";
    echo "   - For Render PostgreSQL: Check Databases section\n";
    echo "   - Should say 'Available'\n\n";
    echo "3. Can you reach the host from Render?\n";
    echo "   - Some database services block external connections\n";
    echo "   - Make sure Render IP is whitelisted\n\n";
    echo "4. Wrong credentials?\n";
    echo "   - Double-check username and password\n";
    echo "   - Special characters in password? May need URL encoding\n";
}

echo "\n======================================\n";
echo "END DIAGNOSTICS\n";
echo "======================================\n";
?>
