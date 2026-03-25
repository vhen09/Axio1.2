# AXIO Cloud Database Implementation Guide
## Complete Setup & Testing Procedures

**Version:** 2.0
**Last Updated:** March 26, 2026
**Target Audience:** DevOps Engineers, System Architects

---

## Table of Contents

1. [Implementation Steps](#implementation-steps)
2. [Testing Procedures](#testing-procedures)
3. [Performance Verification](#performance-verification)
4. [Load Testing](#load-testing)
5. [Monitoring Setup](#monitoring-setup)
6. [Troubleshooting](#troubleshooting)

---

## Implementation Steps

### Step 1: Pre-Implementation Checklist

Before starting, verify:

```bash
# 1. PHP version >= 7.4
php --version

# 2. PDO PostgreSQL extension installed
php -m | grep pdo_pgsql
# If not found, install: apt-get install php-pgsql

# 3. Git repository clean
git status

# 4. Backup current database
cp data/axio.db data/axio.db.backup

# 5. Verify Render account and PostgreSQL creation capability
```

### Step 2: Local Testing (Optional but Recommended)

#### 2.1 Install PostgreSQL Locally

```bash
# macOS
brew install postgresql

# Ubuntu/Debian
sudo apt-get install postgresql postgresql-contrib

# Windows
# Download from https://www.postgresql.org/download/windows/
```

#### 2.2 Start PostgreSQL

```bash
# macOS/Linux
pg_ctl -D /usr/local/var/postgres start

# Verify
psql --version
```

#### 2.3 Create Local Test Database

```bash
# Create database and user
createdb axio_app
createuser axio_app --password
psql -d axio_app -U axio_app

# Inside psql:
GRANT ALL PRIVILEGES ON DATABASE axio_app TO axio_app;
```

#### 2.4 Test Schema Migration

```bash
# Run migration script
php database/migrate.php --target=postgres_schema.sql

# Verify tables
php database/migrate.php --status
```

### Step 3: Update Application Code

#### 3.1 Add New Database Class

```bash
# Copy the new database configuration
cp backend/config/database_v2.php backend/config/database.php
```

**Or update existing references:**

Replace all instances of:
```php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
```

With:
```php
require_once __DIR__ . '/../config/database_v2.php';
$db = Database::getInstance();
```

#### 3.2 Verify API Endpoints

Update each PHP API file in `backend/api/`:

```php
<?php
// backend/api/theorems.php (example)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database_v2.php';
require_once __DIR__ . '/../services/ProofTutorService.php';

$db = Database::getInstance();

if (!$db->isConnected()) {
    http_response_code(503);
    exit(json_encode(['error' => 'Database unavailable']));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_theorems':
            $categoriesId = $_GET['category_id'] ?? null;
            $query = "SELECT * FROM theorems WHERE is_active = TRUE";
            $params = [];
            
            if ($categoriesId) {
                $query .= " AND category_id = ?";
                $params[] = $categoriesId;
            }
            
            $theorems = $db->queryAll($query, $params);
            echo json_encode(['success' => true, 'data' => $theorems]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### Step 4: Create PostgreSQL on Render

#### 4.1 Create Database Instance

1. Log in to [Render Dashboard](https://dashboard.render.com/)
2. Click **"+ New"** → **"PostgreSQL"**
3. Fill in:
   - **Name:** `axio-postgres`
   - **Database:** `axio_app`
   - **User:** `axio_app`
   - **Region:** Same as web service
   - **Plan:** Standard (5/10 GB with backups)

4. Click **"Create Database"**
5. Wait 2-3 minutes for provisioning

#### 4.2 Capture Connection Details

From PostgreSQL instance page, note:

```
External Database URL: postgresql://axio_app:PASSWORD@hostname:5432/axio_app
Internal URL: postgresql://axio_app:PASSWORD@hostname.internal:5432/axio_app
Hostname: 
Username: axio_app
Password: 
Database: axio_app
Port: 5432
```

### Step 5: Configure Environment Variables

#### 5.1 Update Render Web Service

On your **axio-ai-tutor** web service:

1. Click **"Environment"** in sidebar
2. Add/Update variables:

```
DATABASE_URL = postgresql://axio_app:PASSWORD@hostname.internal:5432/axio_app
DB_TYPE = postgresql
NODE_ENV = production
LOG_LEVEL = info
DEEPSEEK_API_KEY = [your key]
```

3. Click **"Save"** (service will auto-redeploy)

#### 5.2 Local Testing (Optional)

Create `.env.local`:

```bash
# .env.local (add to .gitignore)
DATABASE_URL=postgresql://axio_app:password@localhost:5432/axio_app
APP_ENV=development
```

Load in your test script:

```php
$dotenv = @parse_ini_file(__DIR__ . '/.env.local');
if ($dotenv) {
    foreach ($dotenv as $key => $value) {
        putenv("$key=$value");
    }
}
```

### Step 6: Run Database Migrations

#### 6.1 SSH into Render Service

1. Go to axio-ai-tutor service
2. Click **"Shell"** tab at top
3. Run:

```bash
cd /opt/render/project/src
php database/migrate.php --target=postgres_schema.sql
php database/migrate.php --status
```

Expected output:
```
[✓] Migration completed: X statements executed
[✓] Migration Status
Database Type: postgresql
Database: axio_app

Executed Migrations:
postgres_schema.sql     | 2026-03-26 14:32:15+00
```

#### 6.2 Verify Schema

Still in shell:

```bash
# Check tables
psql -d axio_app -U axio_app -c "\dt"

# Should show: users, theorems, proof_attempts, etc.

# Check indexes
psql -d axio_app -U axio_app -c "\di"
```

### Step 7: Seed Initial Data

Create `backend/api/seed.php`:

```php
<?php
require_once __DIR__ . '/../config/database_v2.php';

if ($_GET['token'] !== getenv('SEED_TOKEN')) {
    http_response_code(403);
    die('Forbidden');
}

$db = Database::getInstance();

try {
    // Categories
    $categories = [
        ['name' => 'Sequences and Series', 'icon_unicode' => 'Σ'],
        ['name' => 'Continuity', 'icon_unicode' => '∞'],
        ['name' => 'Derivatives and Integrals', 'icon_unicode' => '∫'],
    ];
    
    foreach ($categories as $cat) {
        $db->insert('theorem_categories', $cat);
    }
    
    echo json_encode(['success' => true, 'message' => 'Data seeded']);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
```

Call once:
```bash
curl "https://your-app.onrender.com/backend/api/seed.php?token=SECRET"
```

### Step 8: Deploy & Test

#### 8.1 Commit & Push

```bash
git add -A
git commit -m "feat: Deploy PostgreSQL cloud database

- Add database_v2.php with PostgreSQL support
- Add postgres_schema.sql with optimized schema
- Add health check endpoint
- Add migration system
- Add connection pooling and retry logic"

git push origin main
```

Render will auto-deploy.

#### 8.2 Verify Deployment

```bash
# Check health
curl https://your-app.onrender.com/backend/api/health.php

# Expected response:
# {
#   "status": "healthy",
#   "database": {"connected": true, "type": "postgresql"}
# }

# Check logs
# Dashboard → Logs tab, search for "Database connection SUCCESS"
```

#### 8.3 Test Core Functionality

```bash
# Test getting theorems
curl "https://your-app.onrender.com/backend/api/theorems.php?action=get_theorems"

# Test user operations (if API supports)
curl "https://your-app.onrender.com/backend/api/auth.php?action=check_session"

# Frontend test
# Open https://your-app.onrender.com/frontend/pages/dashboard.html
```

---

## Testing Procedures

### Unit Tests

Create `database/test_database.php`:

```php
<?php
require_once __DIR__ . '/../backend/config/database_v2.php';

class DatabaseTest {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function runAllTests() {
        echo "=== Database Tests ===\n\n";
        
        $this->testConnection();
        $this->testQuery();
        $this->testInsertUpdate();
        $this->testTransaction();
        $this->testHealthCheck();
        
        echo "\n[✓] All tests passed\n";
    }
    
    private function testConnection() {
        echo "Test 1: Connection... ";
        if ($this->db->isConnected()) {
            echo "✓\n";
        } else {
            throw new Exception("Connection test failed");
        }
    }
    
    private function testQuery() {
        echo "Test 2: Query execution... ";
        $result = $this->db->queryScalar("SELECT 1");
        if ($result === 1 || $result === "1") {
            echo "✓\n";
        } else {
            throw new Exception("Query test failed");
        }
    }
    
    private function testInsertUpdate() {
        echo "Test 3: Insert/Update... ";
        
        // Insert test user
        $id = $this->db->insert('users', [
            'username' => 'test_' . time(),
            'password' => password_hash('test', PASSWORD_BCRYPT),
            'email' => 'test@example.com'
        ]);
        
        if ($id) {
            // Update test user
            $count = $this->db->update('users', 
                ['first_name' => 'Test'],
                'id = ?',
                [$id]
            );
            
            if ($count > 0) {
                // Cleanup
                $this->db->delete('users', 'id = ?', [$id]);
                echo "✓\n";
                return;
            }
        }
        
        throw new Exception("Insert/Update test failed");
    }
    
    private function testTransaction() {
        echo "Test 4: Transactions... ";
        
        $this->db->beginTransaction();
        
        try {
            $id = $this->db->insert('users', [
                'username' => 'txn_test_' . time(),
                'password' => password_hash('test', PASSWORD_BCRYPT),
            ]);
            
            $this->db->rollback();
            
            // Verify rollback
            $check = $this->db->queryScalar("SELECT id FROM users WHERE id = ?", [$id]);
            if ($check === null) {
                echo "✓\n";
            } else {
                throw new Exception("Rollback verification failed");
            }
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function testHealthCheck() {
        echo "Test 5: Health check... ";
        $health = $this->db->healthCheck();
        if ($health['healthy']) {
            echo "✓\n";
        } else {
            throw new Exception("Health check failed: " . $health['message']);
        }
    }
}

try {
    $test = new DatabaseTest();
    $test->runAllTests();
} catch (Exception $e) {
    echo "[✗] Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
```

Run locally:
```bash
php database/test_database.php
```

### Integration Tests

Test complete workflows:

```php
<?php
require_once __DIR__ . '/../backend/config/database_v2.php';

// Test 1: Create user and verify retrieval
$db = Database::getInstance();
$userId = $db->insert('users', [
    'username' => 'integration_test_' . time(),
    'password' => password_hash('password', PASSWORD_BCRYPT),
    'email' => 'integration@test.com'
]);

$user = $db->queryRow("SELECT * FROM users WHERE id = ?", [$userId]);
assert($user['username'] === 'integration_test_' . substr($user['created_at'], 0, 10));

// Cleanup
$db->delete('users', 'id = ?', [$userId]);

echo "[✓] Integration tests passed\n";
```

---

## Performance Verification

### Benchmark Queries

```bash
# Create benchmark script: measure query times
cat > benchmark.php << 'EOF'
<?php
require_once __DIR__ . '/backend/config/database_v2.php';

$db = Database::getInstance();
$iterations = 100;

// Warm up
$db->queryScalar("SELECT 1");

// Benchmark 1: Simple select
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $db->queryScalar("SELECT COUNT(*) FROM users");
}
$time1 = (microtime(true) - $start) * 1000 / $iterations;

// Benchmark 2: Join query
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $db->queryAll("SELECT u.*, COUNT(pa.id) FROM users u LEFT JOIN proof_attempts pa ON u.id = pa.user_id GROUP BY u.id LIMIT 10");
}
$time2 = (microtime(true) - $start) * 1000 / $iterations;

echo "Simple SELECT: {$time1}ms average\n";
echo "Join Query: {$time2}ms average\n";
echo "\nTarget: < 50ms for simple queries, < 100ms for joins\n";
EOF

php benchmark.php
```

### Index Efficiency

```sql
-- Check index usage
SELECT schemaname, tablename, indexname, idx_scan 
FROM pg_stat_user_indexes 
ORDER BY idx_scan DESC 
LIMIT 20;

-- Identify unused indexes
SELECT schemaname, tablename, indexname 
FROM pg_stat_user_indexes 
WHERE idx_scan = 0 
ORDER BY pg_relation_size(indexrelid) DESC;

-- Update statistics
ANALYZE;
```

---

## Load Testing

### Setup Load Testing with ApacheBench

```bash
# Install ApacheBench
# macOS: brew install httpd
# Ubuntu: sudo apt-get install apache2-utils

# Test basic endpoint (1000 requests, 10 concurrent)
ab -n 1000 -c 10 https://your-app.onrender.com/backend/api/health.php

# Results show:
# - Requests per second
# - Mean response time
# - Min/Max response time
# - Failed requests
```

### Realistic Load Test

```bash
# Test mixed load (different endpoints)
ab -n 500 -c 5 https://your-app.onrender.com/backend/api/theorems.php?action=get_theorems
ab -n 500 -c 5 https://your-app.onrender.com/backend/api/tutor.php?action=get_hint
ab -n 500 -c 5 https://your-app.onrender.com/backend/api/health.php
```

**Expected Results (PostgreSQL on Render Standard):**
```
Concurrency: 10 users
Total Requests: 1000
Requests per second: 80-120
Mean response time: 80-120ms
Failed requests: 0
```

---

## Monitoring Setup

### 1. Enable Slow Query Logging

```sql
-- Connect to PostgreSQL and run:
ALTER SYSTEM SET log_min_duration_statement = 1000;
SELECT pg_reload_conf();

-- Later check log:
SELECT query, mean_exec_time FROM pg_stat_statements 
WHERE mean_exec_time > 100 
ORDER BY mean_exec_time DESC;
```

### 2. Set Up Alerts

**Render's Built-in Alerts:**
1. Dashboard → Settings
2. Notification → Email/Slack
3. Alert on: CPU > 80%, Memory > 90%, Uptime < 99%

**Application-Level Alerts:**

Create `monitoring/alert.php`:

```php
<?php
// Check database health and send alerts
$db = Database::getInstance();
$health = $db->healthCheck();

if (!$health['healthy']) {
    $slack_webhook = getenv('SLACK_WEBHOOK');
    $message = [
        'text' => '🚨 AXIO Database Alert',
        'attachments' => [[
            'color' => 'danger',
            'title' => 'Database Unhealthy',
            'text' => $health['message'],
            'ts' => time()
        ]]
    ];
    
    curl_post($slack_webhook, json_encode($message));
}
```

Schedule with cron:
```bash
0 * * * * curl https://your-app.onrender.com/monitoring/alert.php
```

### 3. Dashboard Metrics

**Render Dashboard:**
- CPU Usage
- Memory Usage
- Network In/Out
- Request Count
- Response Time (P50, P95, P99)

**PostgreSQL Monitoring:**
```sql
-- Active connections
SELECT count(*) FROM pg_stat_activity;

-- Database size
SELECT pg_size_pretty(pg_database_size('axio_app'));

-- Table sizes
SELECT schemaname, tablename, pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename))
FROM pg_tables ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

-- Cache hit ratio (should be > 99%)
SELECT 
  sum(heap_blks_read) as heap_read, 
  sum(heap_blks_hit) as heap_hit, 
  sum(heap_blks_hit) / (sum(heap_blks_hit) + sum(heap_blks_read)) as ratio
FROM pg_statio_user_tables;
```

---

## Troubleshooting

### Issue 1: "Connection refused"

**Diagnostic:**
```bash
# Check if environment variable is set
echo $DATABASE_URL

# Test connection manually
psql $DATABASE_URL -c "SELECT 1"

# Check logs
# Render Dashboard → Logs, search for "Connection FAILED"
```

**Solution:**
```
1. Verify DATABASE_URL includes database name and password
2. Ensure PostgreSQL instance is in RUNNING state
3. Check network connectivity (same region, firewall)
4. Restart web service
```

### Issue 2: "Slow queries"

**Diagnostic:**
```sql
SELECT query, calls, mean_exec_time FROM pg_stat_statements 
WHERE mean_exec_time > 100 
ORDER BY mean_exec_time DESC LIMIT 5;
```

**Solution:**
```
1. Check for missing indexes
2. Run ANALYZE; to update statistics
3. Check table sizes (might need partitioning)
4. Consider denormalization for read-heavy tables
```

### Issue 3: "Connection pool exhaustion"

**Diagnostic:**
```sql
SELECT datname, count(*), state FROM pg_stat_activity GROUP BY datname, state;
```

**Solution:**
```
1. Identify long-running queries
2. Adjust PHP timeout settings
3. Implement connection limits in application
4. Move to higher database plan
```

### Issue 4: "Memory exceeded"

**Solution:**
```php
// In database_v2.php, add memory limits
ini_set('memory_limit', '256M');

// Flush large result sets
foreach ($db->queryAll($largeSql) as $row) {
    // Process and forget
    unset($row);
}
```

---

## Success Checklist

- [ ] PostgreSQL instance created on Render
- [ ] Environment variables configured
- [ ] Code updated with database_v2.php
- [ ] Schema migration runs without errors
- [ ] Health check returns `status: "healthy"`
- [ ] Theorems query returns data
- [ ] Proof submission works end-to-end
- [ ] No 503 errors in logs
- [ ] Response times < 200ms (P95)
- [ ] Concurrent user test passes (100+ users)
- [ ] Monitoring/alerts configured
- [ ] Backup/restore tested
- [ ] Runbooks documented
- [ ] Team trained on operations
- [ ] Go-live approval obtained

---

## Next Steps

1. Complete all implementation steps
2. Run all tests (unit, integration, load)
3. Monitor for 24 hours post-deployment
4. Document any issues and resolutions
5. Plan capacity scaling for Q3 2026
