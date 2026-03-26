# AXIO HYBRID FRONTEND ARCHITECTURE
## Option 3: Switchable Vanilla JS ↔ React Implementation

**Status**: Implementation Plan  
**Created**: March 26, 2026  
**Goal**: Allow users to switch between vanilla JS and React frontends without affecting backend

---

## ARCHITECTURE OVERVIEW

```
┌─────────────────────────────────────────────────────────────┐
│                     index.php                               │
│              (Auto-setup + Router Decision)                │
└────────────────────┬────────────────────────────────────────┘
                     │
         ┌───────────┴──────────────┐
         │                          │
    ┌────▼──────────┐      ┌───────▼──────────┐
    │  Vanilla JS   │      │  React/TypeScript│
    │  Frontend ✅  │      │  Frontend (NEW)  │
    │               │      │                  │
    │ /frontend/    │      │ /axio2.0/lean4.../
    │ pages/        │      │ frontend/        │
    │ (Current)     │      │ (Alternative)    │
    └────┬──────────┘      └───────┬──────────┘
         │                         │
         └───────────┬─────────────┘
                     │
         ┌───────────▼──────────────┐
         │  Shared Backend APIs     │
         │  /backend/api/           │
         │  (No changes needed)     │
         └──────────────────────────┘
```

---

## SWITCHING MECHANISM

### How Users Switch Frontends

1. **Via User Settings** (Preferred)
   - In `/settings.html` (vanilla JS) and `/SettingsPage.tsx` (React)
   - Add toggle: "Frontend Preference: Vanilla JS | React"
   - Saves to `backend/api/frontend-preference.php`
   - Takes effect on next login/page reload

2. **Via Query Parameter** (Debug/Testing)
   - `http://localhost:8080/?frontend=react` → Load React
   - `http://localhost:8080/?frontend=vanilla` → Load Vanilla JS
   - `http://localhost:8080/` → Load default (user's saved preference)

3. **Via URL Direct Access** (Dev)
   - React direct: `http://localhost:8080/axio2.0/lean4-ai-web-app/frontend/`
   - Vanilla direct: `http://localhost:8080/frontend/pages/`

---

## IMPLEMENTATION STEPS

### Phase 1: Detection & Routing (1 hour)

#### Step 1.1: Create Feature Flags Module
**File**: `frontend/config/feature-flags.js`

```javascript
// Feature flags for hybrid frontend system
window.AXIO_CONFIG = {
  // Frontend mode: 'vanilla' | 'react' | 'auto' (detect from user preference)
  FRONTEND_MODE: localStorage.getItem('axio_frontend_mode') || 'vanilla',
  
  // Feature flags per frontend
  FEATURES: {
    VANILLA: {
      DARK_MODE: true,
      LATEX_KEYBOARD: true,
      INTERACTIVE_TUTORIAL: true,
      SETTINGS_SYNC: true,
      AI_TUTOR: true
    },
    REACT: {
      DARK_MODE: true,
      LATEX_KEYBOARD: true,
      INTERACTIVE_TUTORIAL: true,
      SETTINGS_SYNC: true,
      AI_TUTOR: true
    }
  },
  
  // API endpoints (same for both)
  API_BASE: '/backend/api',
  
  // Frontend paths
  FRONTEND_PATHS: {
    VANILLA: '/frontend/pages/',
    REACT: '/axio2.0/lean4-ai-web-app/frontend/'
  },
  
  // Get feature status
  isFeatureEnabled(feature) {
    const mode = this.FRONTEND_MODE.toUpperCase();
    return this.FEATURES[mode]?.[feature.toUpperCase()] ?? false;
  },
  
  // Switch frontend mode
  setFrontendMode(mode) {
    if (['vanilla', 'react'].includes(mode)) {
      localStorage.setItem('axio_frontend_mode', mode);
      window.location.reload();
    }
  }
};
```

#### Step 1.2: Create Hybrid Router Entry Point
**File**: `index-hybrid.php` (replace current index.php)

```php
<?php
// Auto-initialize database on app startup
@require_once __DIR__ . '/backend/config/auto-setup.php';

// Check user preference
session_start();
$user_id = $_SESSION['user_id'] ?? null;

// Determine which frontend to load
$requested_frontend = $_GET['frontend'] ?? null;
$default_frontend = 'vanilla'; // fallback

if ($user_id && !$requested_frontend) {
  // Load user's preferred frontend from database
  try {
    require_once __DIR__ . '/backend/config/database.php';
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("
      SELECT preferred_frontend FROM user_preferences 
      WHERE user_id = ? LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $default_frontend = $result['preferred_frontend'] ?? 'vanilla';
  } catch (Exception $e) {
    error_log("Could not load frontend preference: " . $e->getMessage());
    $default_frontend = 'vanilla';
  }
}

// Determine final frontend
$frontend = $requested_frontend ?: $default_frontend;

// Validate frontend choice
if (!in_array($frontend, ['vanilla', 'react'])) {
  $frontend = 'vanilla';
}

// Route to appropriate frontend
if ($frontend === 'react') {
  // Serve React frontend
  header('Location: /axio2.0/lean4-ai-web-app/frontend/index.html', true, 302);
} else {
  // Serve Vanilla JS frontend (default)
  header('Location: /frontend/pages/welcome.html', true, 302);
}

exit;
?>
```

#### Step 1.3: Create Frontend Preference API Endpoint
**File**: `backend/api/frontend-preference.php`

```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Not authenticated']);
  exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
  require_once __DIR__ . '/../config/database.php';
  $db = new Database();
  $pdo = $db->getConnection();
  
  switch ($action) {
    case 'get':
      // Get user's frontend preference
      $stmt = $pdo->prepare("
        SELECT preferred_frontend FROM user_preferences 
        WHERE user_id = ? LIMIT 1
      ");
      $stmt->execute([$user_id]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
      echo json_encode([
        'success' => true,
        'frontend' => $result['preferred_frontend'] ?? 'vanilla'
      ]);
      break;
    
    case 'set':
      // Update user's frontend preference
      $frontend = $_POST['frontend'] ?? '';
      
      if (!in_array($frontend, ['vanilla', 'react'])) {
        throw new Exception('Invalid frontend choice');
      }
      
      // Try to update, else insert
      $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, preferred_frontend) 
        VALUES (?, ?) 
        ON CONFLICT(user_id) 
        DO UPDATE SET preferred_frontend = ?
      ");
      $stmt->execute([$user_id, $frontend, $frontend]);
      
      echo json_encode([
        'success' => true,
        'message' => "Frontend preference updated to: $frontend"
      ]);
      break;
    
    case 'list':
      // List available frontends
      echo json_encode([
        'success' => true,
        'available' => ['vanilla', 'react'],
        'current' => $result['preferred_frontend'] ?? 'vanilla'
      ]);
      break;
    
    default:
      throw new Exception('Unknown action');
  }
  
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}
?>
```

### Phase 2: Frontend Switching UI (2 hours)

#### Step 2.1: Add Toggle to Vanilla JS Settings
**File**: `frontend/pages/settings.html`

Add to the "Preferences" section:

```html
<!-- Frontend Preference Section -->
<div class="settings-section">
  <h2>🎨 Frontend Preference</h2>
  
  <div class="setting-item">
    <div class="setting-label">
      <strong>Interface Style</strong>
      <small>Choose your preferred interface</small>
    </div>
    <div class="setting-control">
      <div style="display: flex; gap: 12px;">
        <button id="frontend-vanilla" class="btn frontend-select active" data-frontend="vanilla">
          📄 Vanilla JS (Current)
        </button>
        <button id="frontend-react" class="btn frontend-select" data-frontend="react">
          ⚛️ React (Alternative)
        </button>
      </div>
    </div>
  </div>
  
  <div id="frontend-info" style="margin-top: 12px; padding: 12px; background: #f0f4ff; border-radius: 6px; font-size: 0.9em; color: #1e3a8a;">
    <strong>ℹ️ Note:</strong> Switching will reload the page. Both interfaces use the same backend and your data is preserved.
  </div>
</div>
```

#### Step 2.2: Add Frontend Switch Handler
**File**: `frontend/js/frontend-switcher.js` (NEW)

```javascript
/**
 * Frontend Switcher
 * Allows users to switch between Vanilla JS and React frontends
 */

class FrontendSwitcher {
  constructor() {
    this.apiEndpoint = '/backend/api/frontend-preference.php';
    this.currentFrontend = localStorage.getItem('axio_frontend_mode') || 'vanilla';
    this.init();
  }

  init() {
    // Load current preference from server
    this.loadPreference();
    
    // Setup button listeners
    document.querySelectorAll('.frontend-select').forEach(btn => {
      btn.addEventListener('click', () => this.switchFrontend(btn.dataset.frontend));
    });
  }

  async loadPreference() {
    try {
      const response = await fetch(`${this.apiEndpoint}?action=get`);
      const data = await response.json();
      
      if (data.success) {
        this.currentFrontend = data.frontend;
        this.updateUI();
      }
    } catch (error) {
      console.error('Failed to load frontend preference:', error);
    }
  }

  updateUI() {
    document.querySelectorAll('.frontend-select').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.frontend === this.currentFrontend);
    });
  }

  async switchFrontend(frontend) {
    if (frontend === this.currentFrontend) {
      return; // Already selected
    }

    try {
      const response = await fetch(this.apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=set&frontend=${frontend}`
      });

      const data = await response.json();

      if (data.success) {
        localStorage.setItem('axio_frontend_mode', frontend);
        showSuccess(`Switching to ${frontend === 'react' ? '⚛️ React' : '📄 Vanilla JS'} interface...`);
        
        // Wait 1 second then reload
        setTimeout(() => {
          window.location.href = `/?frontend=${frontend}`;
        }, 1000);
      } else {
        showError('Failed to update frontend preference');
      }
    } catch (error) {
      console.error('Failed to switch frontend:', error);
      showError('Error switching frontends');
    }
  }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('.frontend-select')) {
    new FrontendSwitcher();
  }
});
```

#### Step 2.3: Load Switcher in Settings Page
**File**: `frontend/pages/settings.html`

Add before closing `</body>`:

```html
<script src="../js/frontend-switcher.js"></script>
```

### Phase 3: React Frontend Integration (4 hours)

#### Step 3.1: Add Frontend Switcher to React
**File**: `/axio2.0/lean4-ai-web-app/frontend/SettingsPage.tsx`

Add state and UI for frontend switching (similar to vanilla JS version).

#### Step 3.2: Update React index to Support Routing
**File**: `/axio2.0/lean4-ai-web-app/frontend/index.html`

Add startup script to detect if redirected from hybrid router.

### Phase 4: Database Schema Updates (30 mins)

#### Step 4.1: Add `preferred_frontend` Column
**File**: Migration script `database/migrations/add_frontend_preference.sql`

```sql
-- Add frontend preference column to user_preferences table
ALTER TABLE user_preferences 
ADD COLUMN preferred_frontend VARCHAR(20) DEFAULT 'vanilla';

-- Index for quick lookup
CREATE INDEX idx_preferred_frontend ON user_preferences(preferred_frontend);
```

---

## DEPLOYMENT CHECKLIST

- [ ] Create `frontend/config/feature-flags.js`
- [ ] Create `index-hybrid.php` (replaces `index.php`)
- [ ] Create `backend/api/frontend-preference.php`
- [ ] Create `frontend/js/frontend-switcher.js`
- [ ] Update `frontend/pages/settings.html` with toggle UI
- [ ] Update `database/schema.sql` to include `preferred_frontend` column
- [ ] Test vanilla JS → React switch
- [ ] Test React → vanilla JS switch
- [ ] Test that data persists across switches
- [ ] Test that both frontends work with all backend APIs
- [ ] Commit changes to GitHub
- [ ] Update README with hybrid architecture documentation

---

## USER EXPERIENCE FLOW

### First-Time User
```
Landing → Auth → Default Frontend (Vanilla JS)
                  ↓
          User can visit Settings → Choose React
          Click React Button → Page reloads with React interface
```

### Returning User
```
Login → Load user's saved preference from DB
        ↓
        If vanilla JS preferred → /frontend/pages/
        If React preferred → /axio2.0/lean4-ai-web-app/frontend/
```

### Switching Preference
```
User in Vanilla JS → Click "React" in Settings
                    → API saves preference to DB
                    → Page reloads with React
                    
User in React → Click "Vanilla JS" in Settings
               → API saves preference to DB
               → Page reloads with Vanilla JS
```

---

## BACKEND COMPATIBILITY

**No backend changes needed!** Both frontends use identical APIs:

| Operation | Vanilla JS | React | Backend |
|-----------|-----------|-------|---------|
| Login | `POST /auth.php` | `POST /auth.php` | ✅ Same |
| Submit Proof | `POST /proof.php` | `POST /proof.php` | ✅ Same |
| Get Scores | `GET /scores.php` | `GET /scores.php` | ✅ Same |
| Save Settings | `POST /settings.php` | `POST /settings.php` | ✅ Same |
| AI Tutor | `POST /tutor.php` | `POST /tutor.php` | ✅ Same |

---

## TESTING MATRIX

| Scenario | Vanilla JS | React | Status |
|----------|-----------|-------|--------|
| Login | ✅ | ✅ | Both work |
| Proof submission | ✅ | ✅ | Both work |
| AI tutor | ✅ | ✅ | Both work |
| Settings save | ✅ | ✅ | Both work |
| Switch to React | ✅ | - | Vanilla → React |
| Switch to Vanilla | ✅ | ✅ | React → Vanilla |
| Data persistence | ✅ | ✅ | Shared DB |

---

## FUTURE ENHANCEMENTS

1. **A/B Testing**: Track which frontend users prefer
2. **Feature Parity**: Ensure both frontends have identical features
3. **Gradual Migration**: Eventually retire vanilla JS once React proves stable
4. **Performance Comparison**: Monitor speeds in both frontends
5. **User Analytics**: Track feature usage by frontend type

---

## ADVANTAGES OF HYBRID APPROACH

✅ **No User Disruption** - Users can switch immediately  
✅ **Risk Mitigation** - Vanilla JS is fallback if React has issues  
✅ **Gradual Migration** - Can eventually deprecate vanilla JS  
✅ **Same Backend** - All data stays synchronized  
✅ **Independent Testing** - Test both frontends in parallel  
✅ **User Choice** - Empower users to pick their interface  

---

## ESTIMATED EFFORT

| Phase | Duration | Complexity |
|-------|----------|-----------|
| Phase 1 (Routing) | 1 hour | Low |
| Phase 2 (UI) | 2 hours | Low |
| Phase 3 (React) | 4 hours | Medium |
| Phase 4 (DB) | 30 mins | Low |
| Testing | 2 hours | Medium |
| **Total** | **~9.5 hours** | **Medium** |

