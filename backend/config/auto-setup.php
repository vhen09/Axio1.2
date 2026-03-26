<?php
/**
 * Auto-Database Setup on App Startup
 * Initializes database schema on EVERY request (no lock file)
 * Works with both PostgreSQL and SQLite
 */

try {
    require_once __DIR__ . '/database.php';
    $db = new Database();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        error_log("⚠️ No database connection available");
        return;
    }
    
    error_log("✓ Database connection established");
    
    // Initialize schema on both PostgreSQL and SQLite
    $db->initializeSchema();
    
} catch (Exception $e) {
    error_log("⚠️ Database auto-setup failed: " . $e->getMessage());
}
?>
