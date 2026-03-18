<?php
/**
 * Auto-Database Setup on App Startup
 * Initializes SQLite if PostgreSQL/MySQL unavailable
 */

// Only run once per hour (check via temp file)
$setupLockFile = sys_get_temp_dir() . '/axio_db_setup.lock';
$lockAge = file_exists($setupLockFile) ? time() - filemtime($setupLockFile) : 3600;

if ($lockAge < 3600) {
    return; // Already setup recently, skip
}

// Touch lock file
@touch($setupLockFile);

try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Test connection
    $pdo->query("SELECT 1");
    error_log("✓ Database connection successful");
    
} catch (Exception $e) {
    error_log("⚠️ Database auto-setup: " . $e->getMessage());
    
    // Try SQLite initialization
    try {
        $dataDir = __DIR__ . '/../data';
        @mkdir($dataDir, 0755, true);
        
        $sqlitePath = $dataDir . '/axio.db';
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables if needed
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS user_preferences (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER UNIQUE,
                latex_skill_level TEXT DEFAULT 'beginner',
                tutorial_completed BOOLEAN DEFAULT 0,
                tutorial_skipped BOOLEAN DEFAULT 0,
                onboarding_complete BOOLEAN DEFAULT 0
            );
            
            CREATE TABLE IF NOT EXISTS submissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                input_text TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS scores (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                submission_id INTEGER,
                score INTEGER
            );
        ");
        
        error_log("✓ SQLite database initialized as fallback");
        
    } catch (Exception $sqlite_e) {
        error_log("✗ SQLite fallback failed: " . $sqlite_e->getMessage());
    }
}
?>
