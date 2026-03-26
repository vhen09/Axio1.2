<?php
/**
 * Database Verification Script
 * Check if users are properly saved in database
 */

require_once __DIR__ . '/config/database.php';

echo "=== AXIO Database Connection Check ===\n\n";

$db = new Database();

if ($db->isDemoMode()) {
    echo "❌ DATABASE CONNECTION FAILED - Running in DEMO MODE\n";
    echo "The system is NOT connected to MySQL database.\n";
    echo "\nTo fix this:\n";
    echo "1. Make sure MySQL server is running\n";
    echo "2. Check database credentials in backend/config/database.php\n";
    echo "3. Import the schema: mysql -u root < database/schema.sql\n";
} else {
    echo "✅ DATABASE CONNECTED SUCCESSFULLY!\n\n";
    
    // Check users table
    $conn = $db->getConnection();
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Total Users in Database: " . $result['count'] . "\n\n";
        
        if ($result['count'] > 0) {
            echo "=== Saved User Accounts ===\n";
            $stmt = $conn->prepare("SELECT id, username, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                echo "ID: " . $user['id'] . " | Username: " . $user['username'] . " | Created: " . $user['created_at'] . "\n";
            }
        } else {
            echo "⚠️  No users found in database yet.\n";
            echo "Create an account to test the signup functionality.\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ Error querying database: " . $e->getMessage() . "\n";
    }
}

echo "\n=== How It Works ===\n";
echo "1. When you SIGN UP → Account saved to database\n";
echo "2. When you LOG IN → System reads username/password from database\n";
echo "3. Each account is unique (username cannot be duplicated)\n";
echo "4. Passwords are securely hashed using PHP's password_hash()\n\n";
?>
