<?php
// Quick migration script to add missing columns to users table
require_once 'backend/config/database.php';

$db = new Database();
$connection = $db->getConnection();

if (!$connection) {
    echo "ERROR: Cannot connect to database\n";
    exit(1);
}

echo "=== RUNNING MIGRATION: Add User Profile Columns ===\n\n";

// Add missing columns to users table
$queries = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) DEFAULT ''",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) DEFAULT ''",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(100) DEFAULT ''",
];

foreach ($queries as $query) {
    try {
        echo "Executing: $query\n";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        echo "✓ Success\n\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n\n";
    }
}

// Verify the structure
echo "=== VERIFYING TABLE STRUCTURE ===\n";
try {
    $stmt = $connection->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users table columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n✓ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error verifying: " . $e->getMessage() . "\n";
}
?>
