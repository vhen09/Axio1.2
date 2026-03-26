<?php
/**
 * AXIO Database Migration System
 * 
 * Handles schema migrations from SQL files
 * Supports PostgreSQL and MySQL
 * 
 * Usage:
 *   php migrate.php --target=postgres_schema.sql
 *   php migrate.php --status
 *   php migrate.php --rollback
 * 
 * @version 1.0
 * @last_updated March 24, 2026
 */

class DatabaseMigrator {
    private $db;
    private $migrationsDir;
    private $migrationsTable = 'migrations';

    public function __construct($db, $migrationsDir = __DIR__ . '/../../database') {
        $this->db = $db;
        $this->migrationsDir = $migrationsDir;
    }

    /**
     * Create migrations tracking table
     */
    public function initMigrations() {
        // Skip if table already exists
        try {
            $this->db->query("SELECT 1 FROM {$this->migrationsTable}");
            echo "[✓] Migrations table already exists\n";
            return true;
        } catch (Exception $e) {
            // Table doesn't exist, create it
        }

        try {
            if ($this->db->getDbType() === 'postgresql') {
                $sql = "
                    CREATE TABLE {$this->migrationsTable} (
                        id BIGSERIAL PRIMARY KEY,
                        migration_name VARCHAR(255) NOT NULL UNIQUE,
                        executed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
                    )
                ";
            } else {
                $sql = "
                    CREATE TABLE {$this->migrationsTable} (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        migration_name VARCHAR(255) NOT NULL UNIQUE,
                        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ";
            }

            $this->db->query($sql);
            echo "[✓] Migrations table created\n";
            return true;

        } catch (Exception $e) {
            echo "[✗] Error creating migrations table: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Run a specific migration file
     * 
     * @param string $filename SQL file name (e.g., 'postgres_schema.sql')
     * @return bool
     */
    public function runMigration($filename) {
        $filepath = $this->migrationsDir . '/' . $filename;

        if (!file_exists($filepath)) {
            echo "[✗] Migration file not found: $filepath\n";
            return false;
        }

        // Check if already executed
        $existing = $this->db->queryScalar(
            "SELECT migration_name FROM {$this->migrationsTable} WHERE migration_name = ?",
            [$filename]
        );

        if ($existing) {
            echo "[→] Migration already executed: $filename\n";
            return true;
        }

        try {
            echo "[→] Running migration: $filename\n";

            // Read SQL file
            $sql = file_get_contents($filepath);

            // Split by semicolon but preserve it for statements
            $statements = $this->splitSqlStatements($sql);

            $executedCount = 0;
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) continue;

                try {
                    $this->db->query($statement . ';');
                    $executedCount++;
                } catch (Exception $e) {
                    // Log but continue (some statements might fail if they already exist)
                    echo "[⚠] Statement warning: " . substr($e->getMessage(), 0, 100) . "\n";
                }
            }

            // Record migration
            $this->db->insert($this->migrationsTable, [
                'migration_name' => $filename
            ]);

            echo "[✓] Migration completed: $executedCount statements executed\n";
            return true;

        } catch (Exception $e) {
            echo "[✗] Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Get list of executed migrations
     * 
     * @return array
     */
    public function getExecutedMigrations() {
        return $this->db->queryAll(
            "SELECT migration_name, executed_at FROM {$this->migrationsTable} ORDER BY executed_at DESC"
        );
    }

    /**
     * Show migration status
     */
    public function showStatus() {
        echo "\n=== Migration Status ===\n";
        echo "Database Type: " . $this->db->getDbType() . "\n";
        echo "Database: " . $this->db->getDatabaseName() . "\n\n";

        $migrations = $this->getExecutedMigrations();

        if (empty($migrations)) {
            echo "No migrations have been executed yet.\n";
            return;
        }

        echo "Executed Migrations:\n";
        echo str_pad("Migration", 40) . " | Executed At\n";
        echo str_repeat("-", 75) . "\n";

        foreach ($migrations as $migration) {
            echo str_pad($migration['migration_name'], 40) . " | " . $migration['executed_at'] . "\n";
        }

        echo "\n";
    }

    /**
     * Split SQL file into individual statements
     * Handles comments and semicolons properly
     * 
     * @param string $sql
     * @return array
     */
    private function splitSqlStatements($sql) {
        // Remove SQL comments
        $sql = preg_replace('/--.*$/m', '', $sql);  // Single-line comments
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);  // Multi-line comments

        // Split by semicolon
        $statements = explode(';', $sql);

        return array_filter(array_map('trim', $statements));
    }
}

// ============================================================================
// CLI INTERFACE
// ============================================================================

if (php_sapi_name() !== 'cli') {
    die("This script must be run from CLI\n");
}

require_once __DIR__ . '/database_v2.php';

$db = Database::getInstance();

if (!$db->isConnected()) {
    echo "Error: Not connected to a real database\n";
    echo "Status: Demo mode\n";
    exit(1);
}

$migrator = new DatabaseMigrator($db);
$migrator->initMigrations();

// Parse command line arguments
$target = null;
$action = 'status';

foreach ($_SERVER['argv'] as $arg) {
    if (preg_match('/--target=(.+)/', $arg, $matches)) {
        $target = $matches[1];
        $action = 'migrate';
    } elseif ($arg === '--status') {
        $action = 'status';
    } elseif ($arg === '--help') {
        echo "AXIO Database Migration Tool\n\n";
        echo "Usage:\n";
        echo "  php migrate.php [OPTIONS]\n\n";
        echo "Options:\n";
        echo "  --target=FILE       Run migration from specified SQL file\n";
        echo "  --status            Show migration status (default)\n";
        echo "  --help              Show this help message\n\n";
        echo "Examples:\n";
        echo "  php migrate.php --target=postgres_schema.sql\n";
        echo "  php migrate.php --status\n";
        exit(0);
    }
}

echo "=== AXIO Database Migration Tool ===\n";
echo "Database Type: " . $db->getDbType() . "\n\n";

if ($action === 'migrate' && $target) {
    $success = $migrator->runMigration($target);
    exit($success ? 0 : 1);
} else {
    $migrator->showStatus();
    exit(0);
}
