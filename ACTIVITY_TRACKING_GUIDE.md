# User Activity Tracking System

## Overview

The AXIO system now includes comprehensive user access tracking. You can monitor who accessed the system, when they logged in/out, and what actions they performed.

---

## How It Works

### 1. **Activity Logging**
Every user action is automatically logged to the `activity_logs` database table:
- **Login events** - When users sign in
- **Logout events** - When users sign out
- **Signup events** - When new users register
- **Custom actions** - Proof submissions, theorem views, etc.

### 2. **What Gets Tracked**
For each activity:
- ✅ User ID (who did it)
- ✅ Action type (login, logout, proof_submitted, etc.)
- ✅ Resource type (user, theorem, proof, etc.)
- ✅ IP Address (where they accessed from)
- ✅ Timestamp (when it happened)
- ✅ Metadata (additional details in JSON)

### 3. **Database Structure**
```sql
CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    action VARCHAR(50),           -- 'login', 'logout', 'signup', etc.
    resource_type VARCHAR(50),    -- 'user', 'theorem', 'proof', etc.
    resource_id BIGINT,           -- ID of the resource
    metadata JSONB,               -- Additional data
    ip_address INET,              -- User's IP address
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for fast queries
CREATE INDEX idx_activity_user ON activity_logs(user_id);
CREATE INDEX idx_activity_action ON activity_logs(action);
CREATE INDEX idx_activity_created ON activity_logs(created_at DESC);
CREATE INDEX idx_activity_user_action ON activity_logs(user_id, action, created_at DESC);
```

---

## API Endpoints

### **1. Get My Activities**
```bash
GET /backend/api/activity-logs.php?action=my_activities
```

**Parameters:**
- `limit` (optional) - Number of records (default: 50)
- `offset` (optional) - Pagination offset (default: 0)

**Response:**
```json
{
  "success": true,
  "user_id": 123,
  "activities": [
    {
      "id": 1,
      "action": "login",
      "resource_type": "user",
      "resource_id": 123,
      "metadata": "{\"username\": \"john_doe\"}",
      "ip_address": "192.168.1.1",
      "created_at": "2026-03-27T14:30:00Z"
    },
    // ... more activities
  ],
  "count": 50
}
```

### **2. Get Login History**
```bash
GET /backend/api/activity-logs.php?action=login_history
```

**Parameters:**
- `limit` (optional) - Number of login records (default: 20)

**Response:**
```json
{
  "success": true,
  "user_id": 123,
  "login_history": [
    {
      "action": "login",
      "ip_address": "192.168.1.1",
      "created_at": "2026-03-27T14:30:00Z",
      "metadata": "{\"username\": \"john_doe\"}"
    },
    // ... more login records
  ],
  "count": 5
}
```

### **3. Get User Statistics**
```bash
GET /backend/api/activity-logs.php?action=user_stats
```

**Response:**
```json
{
  "success": true,
  "user_id": 123,
  "statistics": {
    "total_logins": 15,
    "total_activities": 42,
    "last_login": "2026-03-27T14:30:00Z",
    "last_activity": "2026-03-27T15:45:00Z",
    "ip_addresses": [
      {
        "ip_address": "192.168.1.1",
        "count": 12
      },
      {
        "ip_address": "10.0.0.5",
        "count": 3
      }
    ]
  }
}
```

### **4. Get Recent Activities (Admin)**
```bash
GET /backend/api/activity-logs.php?action=recent_activities
```

**Parameters:**
- `limit` (optional) - Number of records (default: 100)
- `action_filter` (optional) - Filter by action type (e.g., 'login', 'signup')

**Response:**
```json
{
  "success": true,
  "activities": [/* all recent activities */],
  "count": 100
}
```

### **5. Log Custom Activity**
```bash
POST /backend/api/activity-logs.php?action=log_action
Content-Type: application/json

{
  "action": "proof_submitted",
  "resource_type": "proof",
  "resource_id": 456,
  "metadata": {
    "theorem_id": 789,
    "proof_quality": "good"
  }
}
```

---

## Frontend

### **Activity Logs Dashboard**
Access your activity logs at:
```
http://localhost:8080/frontend/pages/activity-logs.html
```

**Features:**
- 📊 View all your activities
- 🔓 View login/logout history
- 📈 See access statistics
- 📍 Track IP addresses you've logged in from
- 🔄 Refresh data in real-time

---

## Backend Implementation

### **Models**

#### ActivityLog Class
File: `backend/models/ActivityLog.php`

```php
// Log an activity
$logger = new ActivityLog($connection);
$logger->log(
    $userId,
    'proof_submitted',        // action
    'proof',                  // resource_type
    123,                      // resource_id
    ['score' => 95],          // metadata array
    '192.168.1.1'            // IP address (optional)
);

// Get user activities
$activities = $logger->getUserActivities($userId, 50, 0);

// Get login history
$logins = $logger->getLoginHistory($userId, 20);

// Get user statistics
$stats = $logger->getUserStats($userId);
```

### **Integration Points**

#### 1. **Authentication (auth.php)**
Automatically logs:
- ✅ Signup events
- ✅ Login events  
- ✅ Logout events

```php
// In signup
$activityLog->log($userId, 'signup', 'user', $userId, ['email' => $email]);

// In login
$activityLog->log($userId, 'login', 'user', $userId, ['username' => $username]);

// In logout
$activityLog->log($userId, 'logout', 'user', $userId);
```

#### 2. **How to Add Tracking to Other Features**

For example, to log proof submissions:

```php
// In proof.php or your proof submission API
require_once __DIR__ . '/../models/ActivityLog.php';

$activityLog = new ActivityLog($connection);
$activityLog->log(
    $userId,
    'proof_submitted',
    'proof',
    $proofId,
    [
        'theorem_id' => $theoremId,
        'status' => $status,
        'score' => $score
    ]
);
```

---

## Security Considerations

### **IP Address Tracking**
- Detects public proxy usage
- Supports HTTP_X_FORWARDED_FOR headers
- Useful for detecting suspicious access patterns

### **Access Control**
- Activities are user-specific by default
- `my_activities` endpoint requires authentication
- `recent_activities` endpoint should be restricted to admins (add permission check if needed)

### **Data Privacy**
- All passwords and sensitive data are NEVER logged
- Only action types and IDs are stored
- Metadata can be filtered to exclude sensitive info

---

## Usage Examples

### **JavaScript (Frontend)**

```javascript
// Get login history
async function getLoginHistory() {
    const response = await fetch('/backend/api/activity-logs.php?action=login_history&limit=10', {
        credentials: 'include'
    });
    const data = await response.json();
    console.log(data.login_history);
}

// Get user statistics
async function getStats() {
    const response = await fetch('/backend/api/activity-logs.php?action=user_stats', {
        credentials: 'include'
    });
    const data = await response.json();
    console.log(`Total logins: ${data.statistics.total_logins}`);
    console.log(`Last login: ${data.statistics.last_login}`);
}

// Log a custom action
async function logCustomAction() {
    const response = await fetch('/backend/api/activity-logs.php?action=log_action', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'theorem_viewed',
            resource_type: 'theorem',
            resource_id: 123,
            metadata: { difficulty: 'intermediate' }
        })
    });
    const data = await response.json();
    console.log(data);
}
```

### **PHP (Backend)**

```php
// Initialize activity logger
$activityLog = new ActivityLog($connection);

// Log theorem view
$activityLog->log(
    $_SESSION['user_id'],
    'theorem_viewed',
    'theorem',
    $theoremId,
    ['category' => 'algebra', 'view_time' => '5s']
);

// Get last 10 activities
$activities = $activityLog->getUserActivities($_SESSION['user_id'], 10);
foreach ($activities as $activity) {
    echo "{$activity['action']} - {$activity['created_at']}\n";
}
```

---

## Monitoring & Analytics

You can use the activity logs for:

✅ **Security Monitoring**
- Detect unauthorized access attempts
- Track unusual IP addresses
- Monitor inactive accounts

✅ **Usage Analytics**
- Most active users
- Peak usage times
- Feature adoption rates

✅ **User Behavior**
- Learning patterns
- Feature usage
- Time spent on different activities

---

## Database Queries

### Find all logins from a specific IP
```sql
SELECT user_id, created_at 
FROM activity_logs 
WHERE action = 'login' AND ip_address = '192.168.1.1'
ORDER BY created_at DESC;
```

### Find failed login attempts (invalid password errors)
```sql
SELECT user_id, COUNT(*) as attempts, MAX(created_at) as last_attempt
FROM activity_logs 
WHERE action = 'failed_login'
GROUP BY user_id
ORDER BY attempts DESC;
```

### Find users who accessed on specific date
```sql
SELECT DISTINCT user_id, COUNT(*) as activity_count
FROM activity_logs 
WHERE DATE(created_at) = '2026-03-27'
GROUP BY user_id
ORDER BY activity_count DESC;
```

### Find most used resources
```sql
SELECT resource_type, resource_id, COUNT(*) as access_count
FROM activity_logs 
WHERE action = 'viewed'
GROUP BY resource_type, resource_id
ORDER BY access_count DESC
LIMIT 10;
```

---

## Summary

| Component | File | Purpose |
|-----------|------|---------|
| **Model** | `backend/models/ActivityLog.php` | Database operations for activity logs |
| **API** | `backend/api/activity-logs.php` | REST endpoints for activity data |
| **Frontend** | `frontend/pages/activity-logs.html` | Dashboard to view activities |
| **Integration** | `backend/api/auth.php` | Auto-logs login/logout/signup |
| **Database** | `activity_logs` table | Stores all activity records |

---

## Next Steps

1. ✅ Activity logging is set up and working
2. View your activities: http://localhost:8080/frontend/pages/activity-logs.html
3. Add custom logging to other features as needed
4. Monitor suspicious access patterns
5. Generate reports for analytics

Happy tracking! 📊
