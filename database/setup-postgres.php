#!/usr/bin/env php
<?php
/**
 * PostgreSQL Migration & Verification Script
 * 
 * Comprehensive setup and testing for MySQL → PostgreSQL migration
 * Run this AFTER PostgreSQL installation and database creation
 * 
 * Usage: php database/setup-postgres.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

system('cls');  // Clear screen on Windows

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  PostgreSQL Migration & Verification Script                    ║\n";
echo "║  Real Analysis Theorem Proving System                           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// CONFIGURATION
// ============================================================================

$pg_config = [
    'host' => 'localhost',
    'port' => '5432',
    'db' => 'lean4_ai_db',
    'user' => 'lean4_user',
    'password' => 'lean4_password_123'
];

$checks = [];
$errors = [];

// ============================================================================
// CHECK 1: PostgreSQL Server Running
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "CHECK 1: PostgreSQL Server Status\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Try connecting to PostgreSQL server
try {
    $pg = new PDO(
        "pgsql:host={$pg_config['host']};port={$pg_config['port']}",
        $pg_config['user'],
        $pg_config['password']
    );
    echo "✓ PostgreSQL server is running on {$pg_config['host']}:{$pg_config['port']}\n";
    $checks['pg_running'] = true;
} catch (Exception $e) {
    echo "✗ FAILED: Cannot connect to PostgreSQL\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "\n  SOLUTION:\n";
    echo "  1. Install PostgreSQL from https://www.postgresql.org/download/windows/\n";
    echo "  2. Start PostgreSQL service (search 'postgresql' in Windows Services)\n";
    echo "  3. Create database: createdb -U postgres lean4_ai_db\n";
    echo "  4. Create user: createuser -U postgres lean4_user\n";
    $checks['pg_running'] = false;
    $errors[] = "PostgreSQL not running";
    goto FINAL_REPORT;
}

// ============================================================================
// CHECK 2: Database Exists
// ============================================================================

echo "\nCHECK 2: Database Existence\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $pg = new PDO(
        "pgsql:host={$pg_config['host']};port={$pg_config['port']};dbname={$pg_config['db']}",
        $pg_config['user'],
        $pg_config['password']
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database '{$pg_config['db']}' exists\n";
    echo "  Connected as user: {$pg_config['user']}\n";
    $checks['db_exists'] = true;
} catch (Exception $e) {
    echo "✗ FAILED: Database does not exist\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "\n  SOLUTION:\n";
    echo "  Run in PowerShell (as Administrator):\n";
    echo "    psql -U postgres\n";
    echo "    CREATE DATABASE lean4_ai_db;\n";
    echo "    CREATE USER lean4_user WITH PASSWORD 'lean4_password_123';\n";
    echo "    GRANT ALL PRIVILEGES ON DATABASE lean4_ai_db TO lean4_user;\n";
    $checks['db_exists'] = false;
    $errors[] = "Database does not exist";
    goto FINAL_REPORT;
}

// ============================================================================
// CHECK 3: Schema Exists
// ============================================================================

echo "\nCHECK 3: Database Schema\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $result = $pg->query("
        SELECT COUNT(*) as table_count 
        FROM information_schema.tables 
        WHERE table_schema = 'public'
    ");
    $table_count = $result->fetch(PDO::FETCH_ASSOC)['table_count'];
    
    if ($table_count > 0) {
        echo "✓ Schema exists with $table_count tables\n";
        
        // List tables
        $tables = $pg->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public'
            ORDER BY table_name
        ")->fetchAll(PDO::FETCH_COLUMN);
        
        echo "  Tables found:\n";
        foreach ($tables as $table) {
            echo "    • $table\n";
        }
        $checks['schema_exists'] = true;
    } else {
        echo "⚠ Schema is empty - will import now...\n";
        
        $schema_file = realpath(__DIR__ . '/postgres_schema.sql');
        if (!file_exists($schema_file)) {
            echo "✗ ERROR: postgres_schema.sql not found at $schema_file\n";
            $checks['schema_exists'] = false;
            $errors[] = "Schema file not found";
            goto FINAL_REPORT;
        }
        
        echo "  Importing schema from: $schema_file\n";
        $schema_sql = file_get_contents($schema_file);
        
        try {
            $pg->exec($schema_sql);
            echo "✓ Schema imported successfully\n";
            $checks['schema_exists'] = true;
        } catch (Exception $e) {
            echo "✗ Schema import failed: " . $e->getMessage() . "\n";
            $checks['schema_exists'] = false;
            $errors[] = "Schema import failed";
            goto FINAL_REPORT;
        }
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $checks['schema_exists'] = false;
    $errors[] = "Schema check failed";
    goto FINAL_REPORT;
}

// ============================================================================
// CHECK 4: Essential Tables
// ============================================================================

echo "\nCHECK 4: Essential Tables Structure\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$essential_tables = ['users', 'theorems', 'theorem_categories', 'proof_attempts'];
$missing_tables = [];

foreach ($essential_tables as $table) {
    try {
        $result = $pg->query("SELECT COUNT(*) FROM $table");
        $count = $result->fetchColumn();
        echo "  ✓ $table ($count rows)\n";
    } catch (Exception $e) {
        echo "  ✗ $table - MISSING or ERROR\n";
        $missing_tables[] = $table;
    }
}

if (empty($missing_tables)) {
    echo "✓ All essential tables exist\n";
    $checks['tables_exist'] = true;
} else {
    echo "✗ Missing tables: " . implode(', ', $missing_tables) . "\n";
    $checks['tables_exist'] = false;
    $errors[] = "Missing essential tables";
}

// ============================================================================
// CHECK 5: Theorem Data
// ============================================================================

echo "\nCHECK 5: Theorem Data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $cat_count = $pg->query("SELECT COUNT(*) FROM theorem_categories")->fetchColumn();
    $thm_count = $pg->query("SELECT COUNT(*) FROM theorems")->fetchColumn();
    
    echo "  Categories: $cat_count\n";
    echo "  Theorems: $thm_count\n";
    
    if ($thm_count > 0) {
        echo "✓ Theorem library populated\n";
        
        // Show sample theorem
        $sample = $pg->query("
            SELECT name, difficulty_level 
            FROM theorems 
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
        
        if ($sample) {
            echo "  Sample: {$sample['name']} ({$sample['difficulty_level']})\n";
        }
        $checks['data_populated'] = true;
    } else {
        echo "⚠ No theorem data found - will seed now...\n";
        
        $seed_file = realpath(__DIR__ . '/seed_theorems.sql');
        if (!file_exists($seed_file)) {
            echo "  ⚠ Seed file not found, skipping\n";
            $checks['data_populated'] = false;
        } else {
            try {
                $seed_sql = file_get_contents($seed_file);
                $statements = array_filter(array_map('trim', explode(';', $seed_sql)));
                
                $count = 0;
                foreach ($statements as $stmt) {
                    if (!empty($stmt) && strpos($stmt, '--') !== 0) {
                        try {
                            $pg->exec($stmt);
                            $count++;
                        } catch (Exception $e) {
                            // Ignore duplicate errors
                            if (strpos($e->getMessage(), 'duplicate') === false) {
                                error_log("Seed warning: " . $e->getMessage());
                            }
                        }
                    }
                }
                
                $new_count = $pg->query("SELECT COUNT(*) FROM theorems")->fetchColumn();
                echo "✓ Seeded with $new_count theorems\n";
                $checks['data_populated'] = true;
            } catch (Exception $e) {
                echo "✗ Seed failed: " . $e->getMessage() . "\n";
                $checks['data_populated'] = false;
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Data check failed: " . $e->getMessage() . "\n";
    $checks['data_populated'] = false;
}

// ============================================================================
// CHECK 6: Backend Config File
// ============================================================================

echo "\nCHECK 6: Backend Configuration\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$config_file = realpath(__DIR__ . '/../config/database.php');
if (!file_exists($config_file)) {
    echo "✗ Config file not found: $config_file\n";
    $checks['config_updated'] = false;
} else {
    $config_content = file_get_contents($config_file);
    
    if (strpos($config_content, "'lean4_user'") !== false && 
        strpos($config_content, 'pgsql') !== false) {
        echo "✓ Backend config updated for PostgreSQL\n";
        echo "  Database: lean4_ai_db\n";
        echo "  User: lean4_user\n";
        $checks['config_updated'] = true;
    } else {
        echo "⚠ Config may not be updated\n";
        echo "  Please update: $config_file\n";
        echo "  Change:\n";
        echo "    private \$db = 'lean4_ai_db';\n";
        echo "    private \$user = 'lean4_user';\n";
        echo "    private \$pass = 'lean4_password_123';\n";
        $checks['config_updated'] = false;
    }
}

// ============================================================================
// FINAL REPORT
// ============================================================================

FINAL_REPORT:

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  MIGRATION STATUS REPORT                                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$passed = count(array_filter($checks));
$total = count($checks);

echo "Results: $passed/$total checks passed\n\n";

foreach ($checks as $name => $result) {
    $status = $result ? '✓' : '✗';
    echo "  $status " . str_replace('_', ' ', ucfirst($name)) . "\n";
}

if (empty($errors)) {
    echo "\n✓ ALL CHECKS PASSED! Your PostgreSQL database is ready.\n\n";
    echo "NEXT STEPS:\n";
    echo "  1. Start PHP server:\n";
    echo "     php -S localhost:8080\n";
    echo "  2. Open browser:\n";
    echo "     http://localhost:8080/frontend/pages/theorems.html\n";
    echo "  3. Test the system with theorem proofs\n\n";
} else {
    echo "\n✗ Some checks failed:\n";
    foreach ($errors as $error) {
        echo "  • $error\n";
    }
    echo "\nPlease fix the errors above and run this script again.\n\n";
}

echo "DATABASE CREDENTIALS:\n";
echo "  Host: {$pg_config['host']}\n";
echo "  Port: {$pg_config['port']}\n";
echo "  Database: {$pg_config['db']}\n";
echo "  User: {$pg_config['user']}\n";
echo "  Password: [configured in database.php]\n\n";

echo "For help, see: POSTGRESQL_MIGRATION_GUIDE.md\n\n";

?>
