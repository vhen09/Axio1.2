# AXIO Cloud Database Architecture
## Scalable Production Database Solution

**Status:** Recommended Implementation Plan
**Target Deployment:** Render.com with PostgreSQL
**Scalability:** Handles 1,000+ concurrent users
**Date:** March 24, 2026

---

## 1. Executive Summary

The AXIO platform is transitioning from localhost/demo mode to a **production-grade cloud database** capable of supporting thousands of concurrent users. This document outlines the architecture, implementation strategy, and best practices for sustainable growth.

### Key Decision: **PostgreSQL on Render** (Primary) + **Supabase** (Alternative)

| Aspect | PostgreSQL (Render) | Supabase | AWS RDS |
|--------|-------------------|---------|---------|
| **Integration** | Native, Zero Setup | Easy, API-First | Complex Setup |
| **Real-time** | Not Built-in | ✅ WebSockets | Requires Extra Service |
| **Auth** | Manual | ✅ Built-in | Manual |
| **Cost** | Included in Render | Free tier available | Pay-per-use |
| **Scalability** | ✅ Horizontal | ✅ Serverless | ✅ Horizontal |
| **Recommendation** | ⭐ RECOMMENDED | Alternative | Enterprise-only |

---

## 2. Architecture Overview

### 2.1 Current State (Demo Mode)
```
Frontend (HTML/CSS/JS)
        ↓
PHP Backend
        ↓
SQLite (File-based)
```

**Problems:**
- ❌ No concurrent user support
- ❌ Data loss if container restart
- ❌ Impossible to scale
- ❌ Single point of failure

### 2.2 Proposed Architecture (Production)
```
1000s of Users
     ↓
Render Web Service (PHP)
     ↓
PgBouncer Connection Pool (5-20 connections)
     ↓
Managed PostgreSQL (Render)
     ├─ Primary Instance (Read/Write)
     ├─ Standby Instance (High Availability)
     └─ Automated Backups (Daily, Retained 7 days)
```

**Benefits:**
- ✅ Handles 1,000+ concurrent connections via pooling
- ✅ Data persists across deployments
- ✅ Automatic failover (99.9% uptime SLA)
- ✅ Horizontal scaling (connection pooling + read replicas)
- ✅ Automated backups and disaster recovery
- ✅ ACID compliance (data integrity)
- ✅ Full text search, JSON, UUID support

---

## 3. Database Selection & Configuration

### 3.1 Why PostgreSQL?

**For AXIO's Needs:**
1. **JSON Support** - Store tags, prerequisites, related_theorems efficiently
2. **Full-Text Search** - Search theorems by name and description
3. **ACID Transactions** - Proof verification must be atomic
4. **Scalability** - Connection pooling + read replicas
5. **Cost** - Render's managed PostgreSQL is cost-effective
6. **Ecosystem** - Better tooling, monitoring, replication
7. **Future-Ready** - Support for advanced features (PostGIS, HyperLogLog, etc.)

### 3.2 PostgreSQL vs MySQL Decision Matrix

| Feature | PostgreSQL | MySQL |
|---------|-----------|-------|
| JSON | Advanced (JSONB indexing) | Basic |
| Full-Text Search | Native, powerful | Available |
| Window Functions | ✅ Yes | ✅ Yes |
| Transactions | ACID guaranteed | Depends on engine |
| Replication | Streaming, flexible | Binary log based |
| Connection Pooling | PgBouncer, pgpool-II | ProxySQL |
| Scaling | Read replicas ✅ | Read replicas ✅ |
| Cost on Render | Same | Not available |

**Verdict:** PostgreSQL is superior for AXIO's architecture.

---

## 4. Implementation Plan

### Phase 1: PostgreSQL Setup on Render
**Effort:** 15-20 minutes | **Risk:** Low | **Downtime:** None (new database)

**Steps:**
1. Create PostgreSQL instance on Render dashboard
2. Note `DATABASE_URL` connection string
3. Create new schema (migration)
4. Seed initial data

### Phase 2: Backend Configuration Updates
**Effort:** 30 minutes | **Risk:** Low | **Downtime:** None (backward compatible)

**Changes:**
1. Update `database.php` with PostgreSQL-specific settings
2. Add connection pooling configuration
3. Convert schema.sql to PostgreSQL syntax
4. Implement prepared statements (already done!)
5. Add database migration system

### Phase 3: Performance Optimization
**Effort:** 1-2 hours | **Risk:** Low | **Downtime:** None (incremental)

**Includes:**
1. Add strategic indexes on query columns
2. Denormalization for read-heavy tables
3. Connection pooling tuning
4. Query optimization and caching
5. Monitoring and alerting setup

### Phase 4: Migration & Testing
**Effort:** 2-3 hours | **Risk:** Medium | **Downtime:** 5-10 minutes

**Includes:**
1. Data migration from SQLite/MySQL to PostgreSQL
2. Load testing with simulated users
3. Failover testing
4. Backup and restore verification
5. Performance baseline establishment

### Phase 5: Deployment & Monitoring
**Effort:** 1-2 hours | **Risk:** Low | **Downtime:** Minimal

**Includes:**
1. Update Render environment variables
2. Deploy with PostgreSQL backend
3. Monitor logs for connection issues
4. Set up automated alerts
5. Document deployment process

---

## 5. PostgreSQL Schema Design

### 5.1 Key Differences from MySQL

**MySQL Syntax:**
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**PostgreSQL Syntax:**
```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,                    -- or: id BIGSERIAL for >2B rows
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Improvements with JSONB:**
```sql
-- Store complex structures natively
CREATE TABLE theorems (
    id BIGSERIAL PRIMARY KEY,
    tags JSONB NOT NULL DEFAULT '[]',        -- More efficient than TEXT
    prerequisites JSONB DEFAULT '[]',         -- Indexed for fast queries
    related_theorems JSONB DEFAULT '[]',
    INDEX idx_tags ON theorems USING GIN(tags)  -- GIN index for JSON
);
```

**Full-Text Search (Native):**
```sql
CREATE TABLE theorems (
    id BIGSERIAL PRIMARY KEY,
    search_vector tsvector GENERATED ALWAYS AS (
        to_tsvector('english', name || ' ' || COALESCE(description, ''))
    ) STORED,
    INDEX ft_search ON theorems USING GIN(search_vector)
);
```

### 5.2 Indexing Strategy

**Critical Indexes for AXIO:**

```sql
-- Users Table
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);

-- Theorems Table
CREATE INDEX idx_theorems_category ON theorems(category_id);
CREATE INDEX idx_theorems_difficulty ON theorems(difficulty_level);
CREATE INDEX idx_theorems_active ON theorems(is_active) WHERE is_active = true;
CREATE INDEX idx_theorems_usage ON theorems(usage_count DESC);

-- Proof Attempts (Hot Table)
CREATE INDEX idx_proof_attempts_user ON proof_attempts(user_id);
CREATE INDEX idx_proof_attempts_theorem ON proof_attempts(theorem_id);
CREATE INDEX idx_proof_attempts_status ON proof_attempts(verification_status);
CREATE INDEX idx_proof_attempts_created ON proof_attempts(created_at DESC);
CREATE INDEX idx_proof_attempts_user_recent ON proof_attempts(user_id, created_at DESC);

-- Proof Conversations (Time-series Data)
CREATE INDEX idx_conversations_user ON proof_conversations(user_id);
CREATE INDEX idx_conversations_created ON proof_conversations(created_at DESC);

-- Log Table (Partitioned)
CREATE INDEX idx_logs_user ON proof_logs(user_id) WHERE status != 'success';
CREATE INDEX idx_logs_created ON proof_logs(created_at DESC);
```

### 5.3 Connection Pooling Configuration

**PgBouncer Setup (Recommended):**

```ini
; /etc/pgbouncer/pgbouncer.ini
[databases]
axio_app = host=your-render-db.internal port=5432 dbname=axio_app

[pgbouncer]
pool_mode = transaction                    # Lighter: recycle connections after each transaction
max_client_conn = 1000                     # Max concurrent users
default_pool_size = 20                     # Connections per database
min_pool_size = 5
reserve_pool_size = 5
reserve_pool_timeout = 3

; Performance tuning
tcp_idle_in_transaction_session_timeout = 600000   # 10 minutes
tcp_keepalives_idle = 30
tcp_keepalives_interval = 10
tcp_keepalives_count = 5
```

**Expected Performance:**
- 1000 users × 1 active connection avg = 20 pooled connections
- Each user request: < 100ms (with proper indexing)
- Concurrent throughput: 100+ requests/second

---

## 6. Data Normalization & Organization

### 6.1 Normalized Schema Design

```
users ──────────────┐
  ├─ id (PK)       │ 1
  ├─ username      │
  ├─ email         │
  └─ created_at    │
                    │  N
            proof_attempts ────────┐
              ├─ id (PK)           │ 1
              ├─ user_id (FK)      │
              ├─ theorem_id (FK)───┼→ theorems
              ├─ status            │   ├─ id (PK)
              └─ created_at        │   ├─ name
                                   │   ├─ category_id (FK)
                                   │   ├─ lean_code
                                   │   └─ ...
                                   │
                            theorem_categories
                              ├─ id (PK)
                              ├─ name
                              └─ description

user_preferences ────────────────┐
  ├─ id (PK)                    │ 1
  ├─ user_id (FK) ──────→ users │
  ├─ preferred_domains  (JSONB) │
  ├─ theme                      │
  └─ ...                        │
```

### 6.2 Denormalization for Performance

**Caching Patterns:**

```sql
-- Add materialized view for popular theorems
CREATE MATERIALIZED VIEW popular_theorems AS
SELECT 
    t.id,
    t.name,
    COUNT(pa.id) as attempt_count,
    AVG(pa.score) as avg_score
FROM theorems t
LEFT JOIN proof_attempts pa ON t.id = pa.theorem_id
GROUP BY t.id, t.name
ORDER BY attempt_count DESC
LIMIT 100;

CREATE INDEX idx_popular_theorems_count ON popular_theorems(attempt_count DESC);

-- Refresh periodically
-- REFRESH MATERIALIZED VIEW popular_theorems;
```

**Denormalized User Stats Table:**

```sql
-- Fast access to user statistics
CREATE TABLE user_stats (
    user_id INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    total_attempts INTEGER DEFAULT 0,
    total_successes INTEGER DEFAULT 0,
    avg_score NUMERIC(5,2) DEFAULT 0,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trigger to update stats on each proof attempt
CREATE FUNCTION update_user_stats()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE user_stats
    SET total_attempts = total_attempts + 1,
        total_successes = CASE WHEN NEW.verification_status = 'success' THEN total_successes + 1 ELSE total_successes END,
        last_activity = CURRENT_TIMESTAMP,
        updated_at = CURRENT_TIMESTAMP
    WHERE user_id = NEW.user_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER proof_attempt_stats AFTER INSERT ON proof_attempts
FOR EACH ROW EXECUTE FUNCTION update_user_stats();
```

---

## 7. Security Best Practices

### 7.1 Data Protection

**Encryption:**
```php
// Enable SSL/TLS for database connections
$dsn = "pgsql:host={$host};port=5432;dbname={$db};sslmode=require";
// Render automatically provides SSL certificates
```

**Role-Based Access Control:**
```sql
-- Create application user with limited privileges
CREATE ROLE axio_app WITH LOGIN PASSWORD 'strong_random_password';
GRANT CONNECT ON DATABASE axio_app TO axio_app;
GRANT USAGE ON SCHEMA public TO axio_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO axio_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO axio_app;

-- Restrict to app user only (no admin from app)
REVOKE ALL ON DATABASE axio_app FROM public;
```

**Sensitive Data Handling:**
```php
// Hash passwords at application layer
password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Sensitive columns in database
// Never log proof content directly - hash for deduplication instead
$proofHash = hash('sha256', $proofContent);
```

### 7.2 Network Security

**Firewall Rules:**
- ✅ Render PostgreSQL: Internal network only (no public access)
- ✅ Application: Runs on Render (same network)
- ✅ No external database access needed

**Connection Pooler:**
- ✅ PgBouncer manages connection limits
- ✅ Prevents connection exhaustion attacks
- ✅ Handles idle connection cleanup

---

## 8. High Availability & Disaster Recovery

### 8.1 Render's Built-in HA Features

**Automatic Failover:**
- Primary PostgreSQL instance monitored
- Standby replica automatically promoted on primary failure
- Recovery Time Objective (RTO): < 2 minutes
- Recovery Point Objective (RPO): < 1 second

**Backup Strategy:**
```
Daily automated backups (7-day retention)
     ↓
Point-in-time recovery available
     ↓
Backups stored in Render's secure storage
```

### 8.2 Application-Level Recovery

```php
// Retry logic for transient failures
function executeWithRetry($callable, $maxRetries = 3) {
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            return $callable();
        } catch (PDOException $e) {
            if ($attempt === $maxRetries) throw $e;
            
            // Exponential backoff: 100ms, 200ms, 400ms
            usleep(100000 * pow(2, $attempt - 1));
        }
    }
}

// Usage
$result = executeWithRetry(function() use ($db) {
    return $db->query("INSERT INTO proof_attempts ...");
});
```

---

## 9. Performance Optimization Strategies

### 9.1 Query Optimization

**Use Prepared Statements (Already Implemented):**
```php
// ✅ Already using prepared statements with parameters
$stmt = $this->db->prepare("SELECT * FROM theorems WHERE id = ?");
$stmt->execute([$id]);
```

**Batch Operations:**
```php
// ❌ Slow: 100 individual inserts
foreach ($theorems as $theorem) {
    $db->insert('theorems', $theorem);
}

// ✅ Fast: Single batch insert
$db->batchInsert('theorems', $theorems);
```

**Avoid N+1 Queries:**
```php
// ❌ Slow: N+1 problem
$users = $db->query("SELECT * FROM users")->fetchAll();
foreach ($users as $user) {
    $stats = $db->query("SELECT * FROM user_stats WHERE user_id = ?", [$user['id']]);
}

// ✅ Fast: Single join
$users = $db->query("""
    SELECT u.*, us.* FROM users u
    LEFT JOIN user_stats us ON u.id = us.user_id
""")->fetchAll();
```

### 9.2 Caching Strategy

**Query Result Caching:**
```php
class CachedDatabase {
    private $cache = [];
    const CACHE_TTL = 300; // 5 minutes
    
    public function cachedQuery($sql, $params = []) {
        $cacheKey = md5($sql . json_encode($params));
        
        if (isset($this->cache[$cacheKey])) {
            list($result, $expiry) = $this->cache[$cacheKey];
            if (time() < $expiry) return $result;
        }
        
        $result = $this->executeQuery($sql, $params);
        $this->cache[$cacheKey] = [$result, time() + self::CACHE_TTL];
        return $result;
    }
}
```

### 9.3 Monitoring & Alerting

**Key Metrics to Track:**

```
Database Health:
  ├─ Connection count (Target: < max_pool_size)
  ├─ CPU usage (Alert: > 80%)
  ├─ Memory usage (Alert: > 85%)
  ├─ IOPS (Alert: > 10,000/sec)
  └─ Replication lag (Alert: > 50ms)

Application Performance:
  ├─ Query response time (P95: < 200ms)
  ├─ Lock wait time (Alert: > 100ms)
  ├─ Cache hit ratio (Target: > 80%)
  └─ Error rate (Alert: > 1%)
```

---

## 10. Migration Plan: SQLite/MySQL → PostgreSQL

### Phase 1: Data Export (SQLite)
```bash
# Export all tables as SQL
sqlite3 axio.db .dump > axio-sqlite-backup.sql
```

### Phase 2: Schema Conversion
```bash
# Convert SQLite schema to PostgreSQL
# Manual steps: Replace INT with BIGSERIAL, ENUM type definitions, etc.
```

### Phase 3: Data Migration
```sql
-- PostgreSQL: Import data
-- Disable foreign key checks during import
ALTER SCHEMA public OWNER TO axio_app;

-- Copy data from SQLite dump (filtered and converted)
-- Restore foreign keys
ALTER TABLE proof_attempts
  ADD CONSTRAINT fk_proof_attempts_user
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
```

### Phase 4: Validation
```sql
-- Verify row counts match
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM theorems;
SELECT COUNT(*) FROM proof_attempts;

-- Check for orphaned records
SELECT * FROM proof_attempts WHERE user_id NOT IN (SELECT id FROM users);
```

---

## 11. Cost Analysis

### Per-Month Costs (Estimated)

**PostgreSQL on Render:**
| Metric | Free Tier | Standard (Per Mo) | High-Volume (Per Mo) |
|--------|-----------|------------------|----------------------|
| Instance | $0 | Not available | ~$15-50 |
| Storage | 256 MB | $0.29/GB | $0.29/GB |
| Backups | ✅ 1 free | Included | Included |
| High Availability | ❌ No | Not available | ✅ Yes |

**Recommended Config: Standard or High-Availability**
- Database: $15-30/month
- Storage (100GB): $29/month
- **Total: ~$50/month** for 1,000 concurrent users

**Compared to Alternatives:**
- AWS RDS: $100-200/month (minimum)
- Supabase: $25-100/month (pay-as-you-go)
- MongoDB Atlas: $60-200/month

**ROI:** Render's managed PostgreSQL is 3-4x cheaper than AWS, same as Supabase.

---

## 12. Implementation Checklist

### Pre-Implementation
- [ ] Review this architecture document
- [ ] Create PostgreSQL instance on Render
- [ ] Note DATABASE_URL connection string
- [ ] Backup current SQLite data

### Code Changes
- [ ] Update `database.php` for PostgreSQL
- [ ] Convert schema.sql to PostgreSQL syntax
- [ ] Add migration system
- [ ] Implement connection pooling
- [ ] Add strategic indexes
- [ ] Create monitoring scripts

### Testing
- [ ] Unit tests pass with PostgreSQL
- [ ] Load test with 100+ concurrent users
- [ ] Verify failover behavior
- [ ] Test backup and restore
- [ ] Performance baseline established

### Deployment
- [ ] Environment variables set on Render
- [ ] Deploy with `DATABASE_MODE=production`
- [ ] Monitor logs for connection issues
- [ ] Set up automated alerts
- [ ] Document runbooks for ops team

### Post-Deployment
- [ ] Monitor performance metrics
- [ ] Collect baseline statistics
- [ ] Schedule regular backups
- [ ] Plan quarterly optimization reviews
- [ ] Document lessons learned

---

## 13. Runbooks & Troubleshooting

### Runbook 1: Connection Pool Exhaustion
**Symptom:** "FATAL: sorry, too many clients already"

**Resolution:**
```sql
-- Check current connections
SELECT datname, count(*) FROM pg_stat_activity GROUP BY datname;

-- Kill idle connections
SELECT pg_terminate_backend(pid) FROM pg_stat_activity 
WHERE usename = 'axio_app' AND state = 'idle' AND state_change < now() - interval '5 minutes';

-- Increase pool size (PgBouncer)
default_pool_size = 30  -- Was 20
```

### Runbook 2: Slow Queries
**Symptom:** "P95 response time > 500ms"

**Resolution:**
```sql
-- Identify slow queries
SELECT query, calls, mean_exec_time 
FROM pg_stat_statements 
WHERE mean_exec_time > 100 
ORDER BY mean_exec_time DESC;

-- Add missing indexes
CREATE INDEX idx_new_column ON table_name(column_name);
ANALYZE;  -- Update statistics
```

### Runbook 3: Replication Lag
**Symptom:** "Standby database is behind primary"

**Resolution:**
```sql
-- Check replication status
SELECT slot_name, restart_lsn, confirmed_flush_lsn 
FROM pg_replication_slots;

-- Monitor on primary
SELECT pg_last_xact_replay_timestamp() - NOW() as replication_lag;

-- If lag > 1 minute: Scale down write volume or increase network bandwidth
```

---

## 14. Future Enhancements

### 14.1 Read Replicas (Horizontal Scaling)
**For Analytics & Reporting:**
```
Primary (Write) → Replica 1 (Read)
                → Replica 2 (Read)
                → Replica 3 (Read)
```

**Cost:** +$50/month per replica
**Benefit:** 3x read throughput, analytics don't block users

### 14.2 NoSQL Layer (Optional)
**For Real-time Features:**
- Chat history → Redis (cache)
- Notifications → PostgreSQL + Redis Streams
- Session data → Redis

### 14.3 Full-Text Search Engine (Optional)
**For Advanced Theorem Search:**
- Elasticsearch: Complex search features
- PostgreSQL Full-Text: Native, good enough for AXIO

**Recommendation:** Use PostgreSQL built-in for now, migrate to Elasticsearch if needed.

---

## 15. Success Metrics

**Post-Implementation Validation:**

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Uptime** | 99.5% | Render's monitoring |
| **Connection Pool** | < 20/20 max | `SELECT COUNT(*) FROM pg_stat_activity` |
| **Query Time (P95)** | < 200ms | Application logs |
| **Data Loss** | 0 | Backup test recovery |
| **Concurrent Users** | 1,000+ | Load test results |
| **Cost/User** | < $0.05/month | $50 / 1,000 users |

---

## Conclusion

This architecture provides:
✅ **Scalability** - Handle 1,000+ concurrent users
✅ **Reliability** - 99.9% SLA with automatic failover
✅ **Performance** - Sub-200ms query times with proper indexing
✅ **Security** - Encryption, role-based access, network isolation
✅ **Maintainability** - Clear schema, monitoring, runbooks
✅ **Cost-Effective** - $50/month for enterprise-grade database

**Next Steps:** Proceed to Phase 1 implementation with PostgreSQL setup on Render.
