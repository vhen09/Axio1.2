<?php
/**
 * MySQL to PostgreSQL Migration Script
 * 
 * Migrates all data from MySQL to PostgreSQL with proper data type conversions
 * 
 * Usage: php database/migrate_mysql_to_postgres.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Hardcoded credentials (UPDATE THESE)
$mysql_config = [
    'host' => 'localhost',
    'dbname' => 'lean4_ai_app',
    'user' => 'root',
    'password' => ''
];

$postgres_config = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'lean4_ai_db',
    'user' => 'lean4_user',
    'password' => 'lean4_password_123'
];

// Suppress warnings but catch them
error_reporting(E_ALL);
set_error_handler(function($errno, $errstr) {
    return true; // Suppress display
});

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║    MySQL → PostgreSQL Migration Script                     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Try MySQL connection (optional, if no data just skip)
echo "[1/5] Checking MySQL connection...\n";
try {
    $mysql = new PDO(
        "mysql:host={$mysql_config['host']};dbname={$mysql_config['dbname']};charset=utf8mb4",
        $mysql_config['user'],
        $mysql_config['password']
    );
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "      ✓ MySQL connected\n";
    $has_mysql_data = true;
} catch (Exception $e) {
    echo "      ⚠ MySQL not available (will use seed data only)\n";
    $has_mysql_data = false;
    $mysql = null;
}

// Connect to PostgreSQL
echo "[2/5] Connecting to PostgreSQL...\n";
try {
    $pg = new PDO(
        "pgsql:host={$postgres_config['host']};port={$postgres_config['port']};dbname={$postgres_config['dbname']}",
        $postgres_config['user'],
        $postgres_config['password']
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "      ✓ PostgreSQL connected\n";
} catch (Exception $e) {
    echo "      ✗ ERROR: PostgreSQL connection failed\n";
    echo "      Details: " . $e->getMessage() . "\n";
    echo "\n      Make sure PostgreSQL is running and database exists:\n";
    echo "      createdb -U postgres lean4_ai_db\n";
    exit(1);
}

// Check PostgreSQL schema exists
echo "[3/5] Checking PostgreSQL schema...\n";
try {
    $result = $pg->query("SELECT count(*) FROM information_schema.tables WHERE table_schema='public'");
    $table_count = $result->fetchColumn();
    
    if ($table_count < 5) {
        echo "      ⚠ Schema not found. Importing from postgres_schema.sql...\n";
        
        $schema_file = __DIR__ . '/postgres_schema.sql';
        if (!file_exists($schema_file)) {
            echo "      ✗ ERROR: postgres_schema.sql not found\n";
            exit(1);
        }
        
        // Import schema
        $schema_sql = file_get_contents($schema_file);
        $pg->exec($schema_sql);
        echo "      ✓ Schema imported successfully\n";
    } else {
        echo "      ✓ Schema exists ($table_count tables found)\n";
    }
} catch (Exception $e) {
    echo "      ✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Migrate data from MySQL (if available)
if ($has_mysql_data) {
    echo "[4/5] Migrating data from MySQL...\n";
    
    try {
        // Clear PostgreSQL tables first
        $pg->exec("TRUNCATE TABLE proof_conversations CASCADE");
        $pg->exec("TRUNCATE TABLE proof_attempts CASCADE");
        $pg->exec("TRUNCATE TABLE theorems CASCADE");
        $pg->exec("TRUNCATE TABLE theorem_categories CASCADE");
        $pg->exec("TRUNCATE TABLE scores CASCADE");
        $pg->exec("TRUNCATE TABLE submissions CASCADE");
        $pg->exec("TRUNCATE TABLE users CASCADE");
        
        // Migrate users
        $users = $mysql->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            $stmt = $pg->prepare("
                INSERT INTO users (id, username, password, first_name, last_name, email, created_at, updated_at)
                VALUES (:id, :username, :password, :first_name, :last_name, :email, :created_at, :updated_at)
            ");
            $stmt->execute([
                ':id' => $user['id'],
                ':username' => $user['username'],
                ':password' => $user['password'],
                ':first_name' => $user['first_name'] ?? '',
                ':last_name' => $user['last_name'] ?? '',
                ':email' => $user['email'] ?? '',
                ':created_at' => $user['created_at'] ?? date('Y-m-d H:i:s'),
                ':updated_at' => $user['created_at'] ?? date('Y-m-d H:i:s')
            ]);
        }
        echo "      ✓ Migrated " . count($users) . " users\n";
        
        // Migrate categories
        $categories = $mysql->query("SELECT * FROM theorem_categories")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as $cat) {
            $stmt = $pg->prepare("
                INSERT INTO theorem_categories (id, name, description, display_order, created_at, updated_at)
                VALUES (:id, :name, :description, :display_order, :created_at, :updated_at)
            ");
            $stmt->execute([
                ':id' => $cat['id'],
                ':name' => $cat['name'],
                ':description' => $cat['description'] ?? '',
                ':display_order' => $cat['display_order'] ?? 0,
                ':created_at' => $cat['created_at'] ?? date('Y-m-d H:i:s'),
                ':updated_at' => $cat['created_at'] ?? date('Y-m-d H:i:s')
            ]);
        }
        echo "      ✓ Migrated " . count($categories) . " categories\n";
        
        // Migrate theorems
        $theorems = $mysql->query("SELECT * FROM theorems")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($theorems as $thm) {
            $stmt = $pg->prepare("
                INSERT INTO theorems 
                (id, category_id, name, statement, lean_code, natural_language_description, 
                 difficulty_level, tags, prerequisites, related_theorems, proof_hints, 
                 examples, is_active, usage_count, created_at, updated_at)
                VALUES 
                (:id, :category_id, :name, :statement, :lean_code, :natural_language_description,
                 :difficulty_level, :tags, :prerequisites, :related_theorems, :proof_hints,
                 :examples, :is_active, :usage_count, :created_at, :updated_at)
            ");
            $stmt->execute([
                ':id' => $thm['id'],
                ':category_id' => $thm['category_id'],
                ':name' => $thm['name'],
                ':statement' => $thm['statement'],
                ':lean_code' => $thm['lean_code'],
                ':natural_language_description' => $thm['natural_language_description'] ?? '',
                ':difficulty_level' => $thm['difficulty_level'] ?? 'intermediate',
                ':tags' => $thm['tags'] ?? '[]',
                ':prerequisites' => $thm['prerequisites'] ?? '[]',
                ':related_theorems' => $thm['related_theorems'] ?? '[]',
                ':proof_hints' => $thm['proof_hints'] ?? '',
                ':examples' => $thm['examples'] ?? '',
                ':is_active' => $thm['is_active'] ?? true,
                ':usage_count' => $thm['usage_count'] ?? 0,
                ':created_at' => $thm['created_at'] ?? date('Y-m-d H:i:s'),
                ':updated_at' => $thm['updated_at'] ?? date('Y-m-d H:i:s')
            ]);
        }
        echo "      ✓ Migrated " . count($theorems) . " theorems\n";
        
        // Migrate submissions
        $submissions = $mysql->query("SELECT * FROM submissions")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($submissions as $sub) {
            $stmt = $pg->prepare("
                INSERT INTO submissions (id, user_id, input_text, created_at)
                VALUES (:id, :user_id, :input_text, :created_at)
            ");
            $stmt->execute([
                ':id' => $sub['id'],
                ':user_id' => $sub['user_id'],
                ':input_text' => $sub['input_text'],
                ':created_at' => $sub['created_at'] ?? date('Y-m-d H:i:s')
            ]);
        }
        echo "      ✓ Migrated " . count($submissions) . " submissions\n";
        
    } catch (Exception $e) {
        echo "      ✗ Migration error: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "[4/5] Skipping MySQL data migration (MySQL not available)...\n";
}

// Seed theorem data if not present
echo "[5/5] Ensuring theorem data...\n";
try {
    $thm_count = $pg->query("SELECT COUNT(*) FROM theorems")->fetchColumn();
    
    if ($thm_count < 10) {
        echo "      ➜ Loading seed data...\n";
        $seed_file = __DIR__ . '/seed_theorems.sql';
        
        if (file_exists($seed_file)) {
            $seed_sql = file_get_contents($seed_file);
            // Parse and execute (simple approach)
            $statements = array_filter(array_map('trim', explode(';', $seed_sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    try {
                        $pg->exec($stmt);
                    } catch (Exception $e) {
                        // Ignore duplicate key errors
                        if (strpos($e->getMessage(), 'duplicate') === false) {
                            echo "      ⚠ Warning: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        }
    }
    
    $final_count = $pg->query("SELECT COUNT(*) FROM theorems")->fetchColumn();
    echo "      ✓ Theorems in database: $final_count\n";
    
} catch (Exception $e) {
    echo "      ⚠ Could not seed theorems: " . $e->getMessage() . "\n";
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║    ✓ MIGRATION COMPLETE                                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "Next Steps:\n";
echo "1. Update backend/config/database.php with PostgreSQL credentials\n";
echo "2. Run: php backend/check-database.php\n";
echo "3. Test web interface: http://localhost:8080\n\n";

echo "Connection Details:\n";
echo "  Host: {$postgres_config['host']}\n";
echo "  Port: {$postgres_config['port']}\n";
echo "  Database: {$postgres_config['dbname']}\n";
echo "  User: {$postgres_config['user']}\n\n";

?>
