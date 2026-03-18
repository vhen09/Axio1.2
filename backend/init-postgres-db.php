<?php
/**
 * Database Schema Initialization for PostgreSQL
 * 
 * Run this ONCE to set up PostgreSQL database:
 * Access via: https://your-render-app.onrender.com/backend/init-postgres-db.php
 * 
 * SECURITY: This should only be accessible once. Delete after use!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h2>PostgreSQL Database Initialization</h2>\n";
echo "<pre style='background:#f5f5f5; padding:20px; border-radius:5px;'>\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        echo "❌ ERROR: Database connection failed!\n";
        echo "Make sure DATABASE_URL is set in Render environment.\n";
        exit;
    }
    
    echo "✓ Connected to PostgreSQL\n\n";
    
    // Read and execute schema
    $schemaFile = __DIR__ . '/../database/schema-postgresql.sql';
    
    if (!file_exists($schemaFile)) {
        echo "❌ ERROR: schema-postgresql.sql not found!\n";
        exit;
    }
    
    $schema = file_get_contents($schemaFile);
    $statements = explode(';', $schema);
    
    $count = 0;
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            echo "Executing: " . substr($statement, 0, 60) . "...\n";
            $pdo->exec($statement);
            $count++;
        } catch (PDOException $e) {
            echo "  ⚠️  Warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ SUCCESS! Database initialized with $count statements\n";
    echo "\nTables created:\n";
    
    $result = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname='public'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "  ✓ $table\n";
    }
    
    echo "\n⚠️  IMPORTANT:\n";
    echo "1. DELETE THIS FILE (init-postgres-db.php) from the server\n";
    echo "2. It was only needed for one-time setup\n";
    echo "3. Test signup at: https://your-render-app.onrender.com/frontend/pages/auth.html\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
?>
