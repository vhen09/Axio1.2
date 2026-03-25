# Settings System - Integration Guide

## Complete Implementation Walkthrough

This guide walks you through implementing the settings system in AXIO step-by-step.

## Phase 1: Database Setup

### Step 1.1: Execute Migration

Open Terminal and run:

```bash
cd c:\Project\v11
mysql -u root lean4_ai_db < database/migrations/001_create_user_settings.sql
```

**Expected output**:
```
Query OK, 0 rows affected
```

### Step 1.2: Verify Table Creation

```bash
mysql -u root lean4_ai_db -e "DESCRIBE user_settings;"
```

**Expected output** (should show 17 rows):
```
+-------------------------+-----------+------+-----+---------+----------------+
| Field                   | Type      | Null | Key | Default | Extra          |
+-------------------------+-----------+------+-----+---------+----------------+
| id                      | int       | NO   | PRI | NULL    | auto_increment |
| user_id                 | int       | NO   | UNI | NULL    |                |
| theme                   | varchar   | YES  |     | light   |                |
| fontSize                | varchar   | YES  |     | normal  |                |
| learningLanguage        | varchar   | YES  |     | en      |                |
| proofMode               | varchar   | YES  |     | step... |                |
| difficultyLevel         | varchar   | YES  |     | beginn...|               |
| preferredDomains        | json      | YES  |     | NULL    |                |
| autoSave                | varchar   | YES  |     | true    |                |
| showPreview             | varchar   | YES  |     | true    |                |
| showHints               | varchar   | YES  |     | true    |                |
| autoTutorial            | varchar   | YES  |     | false   |                |
| scoreNotifications      | varchar   | YES  |     | true    |                |
| feedbackNotifications   | varchar   | YES  |     | true    |                |
| soundNotifications      | varchar   | YES  |     | false   |                |
| created_at              | timestamp | YES  |     | CURRENT...|             |
| updated_at              | timestamp | YES  |     | CURRENT...|             |
+-------------------------+-----------+------+-----+---------+----------------+
```

### Step 1.3: Verify Files Exist

Check that all 4 files are in the correct locations:

```bash
# Service layer
dir backend\services\SettingsService.php

# API endpoint
dir backend\api\settings.php

# Frontend manager
dir frontend\js\global-settings-enhanced.js

# Database migration (for reference)
dir database\migrations\001_create_user_settings.sql
```

All should exist without errors.

## Phase 2: Frontend Integration

### Step 2.1: Update settings.html Imports

**File**: `frontend/pages/settings.html`

Find this section (around line 620):
```html
<!-- OLD - REMOVE THIS -->
<script src="../js/global-settings.js"></script>
```

Replace with:
```html
<!-- NEW - ADD THIS -->
<script src="../js/global-settings-enhanced.js"></script>
```

### Step 2.2: Verify Settings Controls

The settings.html page should have controls for these 13 settings. Verify they exist:

```html
<!-- Theme selection -->
<select id="theme-select"></select>

<!-- Font size selection -->
<select id="font-size-select"></select>

<!-- Learning language -->
<select id="language-select"></select>

<!-- Proof mode -->
<select id="proof-mode-select"></select>

<!-- Difficulty level -->
<select id="difficulty-level-select"></select>

<!-- Feature toggles -->
<input type="checkbox" id="autoSave-toggle" />
<input type="checkbox" id="showPreview-toggle" />
<input type="checkbox" id="showHints-toggle" />
<input type="checkbox" id="autoTutorial-toggle" />

<!-- Notification toggles -->
<input type="checkbox" id="scoreNotifications-toggle" />
<input type="checkbox" id="feedbackNotifications-toggle" />
<input type="checkbox" id="soundNotifications-toggle" />

<!-- Preferred domains (complex) -->
<div id="preferred-domains"></div>
```

### Step 2.3: Verify Event Handlers

The settings.html file should have JavaScript that hooking into these controls. Verify the controller script includes handlers:

```javascript
// In settings.html <script> section

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  // Populate controls with current settings
  populateSettings();
  
  // Attach change handlers
  attachEventListeners();
});

function populateSettings() {
  // Load current settings and populate form
  document.getElementById('theme-select').value = 
    globalSettings.getSetting('theme');
  document.getElementById('font-size-select').value = 
    globalSettings.getSetting('fontSize');
  // ... etc for other controls
}

function attachEventListeners() {
  // Theme changes
  document.getElementById('theme-select')?.addEventListener('change', (e) => {
    globalSettings.updateSetting('theme', e.target.value);
  });
  
  // Font size changes
  document.getElementById('font-size-select')?.addEventListener('change', (e) => {
    globalSettings.updateSetting('fontSize', e.target.value);
  });
  
  // ... similar handlers for other controls
  
  // Listen for settings changes (from other tabs/windows)
  window.addEventListener('settingsChanged', (event) => {
    populateSettings(); // Refresh UI
  });
}
```

## Phase 3: Testing

### Step 3.1: Start PHP Server

```bash
# If not already running
cd c:\Project\v11
php -S localhost:8080
```

### Step 3.2: Log In to Application

1. Navigate to `http://localhost:8080/frontend/pages/auth.html`
2. Create account or log in
3. Verify you receive session cookie

### Step 3.3: Access Settings Page

Navigate to: `http://localhost:8080/frontend/pages/settings.html`

### Step 3.4: Open Browser DevTools

Press `F12` in browser to open DevTools.

### Step 3.5: Test Single Setting Change

1. Go to **Console** tab
2. Type: `globalSettings.getAllSettings()`
3. Press Enter - should show all 13 settings
4. Change a setting in the UI (e.g., theme toggle)
5. Go to **Network** tab
6. Wait 1-2 seconds (debounce delay)
7. You should see a POST request to `settings.php?action=update_batch`
8. Click on it and check Response tab - should see `"success": true`

### Step 3.6: Test Persistence

1. Change a setting (e.g., set theme to "dark")
2. Wait for sync request to complete (see green 200 status)
3. Refresh the page (Ctrl+R or F5)
4. The setting should persist

### Step 3.7: Test Database Persistence

In Terminal:
```bash
# Replace 1 with actual user_id
mysql -u root lean4_ai_db -e "SELECT theme, fontSize FROM user_settings WHERE user_id = 1;"
```

Should show the values you just set in the UI.

### Step 3.8: Test Batch Update

In Console:
```javascript
globalSettings.updateSettings({
  theme: 'light',
  fontSize: 'large',
  autoSave: false
});
```

Watch Network tab - should see one POST request with all 3 settings.

### Step 3.9: Test Reset

In Console:
```javascript
globalSettings.resetToDefaults();
```

Should return all settings to defaults. Check Network tab for reset action.

## Phase 4: Integrate Settings Across Pages

### Step 4.1: Add to Proof Editor

**File**: `frontend/pages/proof-editor.html`

Add to top of file's `<script>` section:
```html
<script src="../js/global-settings-enhanced.js"></script>
```

Add to page code:
```javascript
// Use proof mode setting
const proofMode = globalSettings.getSetting('proofMode');
if (proofMode === 'step-by-step') {
  // Show step-by-step UI
} else {
  // Show full proof UI
}

// Show/hide hints based on setting
if (!globalSettings.getSetting('showHints')) {
  document.querySelectorAll('.hint').forEach(el => el.style.display = 'none');
}

// Auto-save based on setting
if (globalSettings.getSetting('autoSave')) {
  // Enable auto-save feature
  setInterval(saveProofDraft, 30000);
}

// Listen for changes
window.addEventListener('settingsChanged', (event) => {
  const settings = event.detail;
  // React to any setting changes
  updateUIForSettings(settings);
});
```

### Step 4.2: Add to Theorem Browser

**File**: `frontend/pages/theorems.html`

Add script:
```html
<script src="../js/global-settings-enhanced.js"></script>
```

Add to page code:
```javascript
// Filter by difficulty
const difficulty = globalSettings.getSetting('difficultyLevel');
filterTheoremsByDifficulty(difficulty);

// Filter by preferred domains
const domains = globalSettings.getSetting('preferredDomains');
filterTheoremsByDomains(domains);

// Apply font size
const fontSize = globalSettings.getSetting('fontSize');
document.documentElement.style.setProperty('--base-font-size', 
  fontSize === 'small' ? '12px' : fontSize === 'large' ? '16px' : '14px'
);

// Listen for setting changes
window.addEventListener('settingsChanged', (event) => {
  // Refresh filtered theorems
  refreshTheoremList();
});
```

### Step 4.3: Add to Tutor Page

**File**: `frontend/pages/tutor.html` (or similar)

```html
<script src="../js/global-settings-enhanced.js"></script>
```

```javascript
// Use learning language
const language = globalSettings.getSetting('learningLanguage');
setTutorLanguage(language);

// Show notifications
if (globalSettings.getSetting('scoreNotifications')) {
  showScoreNotification(score);
}

if (globalSettings.getSetting('feedbackNotifications')) {
  showFeedbackNotification(feedback);
}

if (globalSettings.getSetting('soundNotifications')) {
  playSuccessSound();
}

// React to difficulty changes
window.addEventListener('settingsChanged', (event) => {
  if (event.detail.difficultyLevel !== undefined) {
    adjustTutorDifficulty(event.detail.difficultyLevel);
  }
});
```

## Phase 5: Verify Complete Integration

### Step 5.1: Test Cross-Page Sync

1. Open settings.html in one tab
2. Open theorems.html in another tab
3. Change a setting in settings.html
4. Switch to theorems.html tab
5. Refresh (F5)
6. The setting should be reflected

### Step 5.2: Test Multi-Device Sync

1. Open settings.html on desktop browser
2. Change a setting
3. Open settings.html on mobile browser (same account)
4. Refresh mobile browser
5. Setting should appear on mobile (after next session start)

### Step 5.3: Performance Check

1. Open DevTools Profiler
2. Change multiple settings rapidly
3. Check that requests are batched (multiple changes = single request after 1 second)
4. Performance should be smooth, no lag

### Step 5.4: Error Recovery

1. Open DevTools Network tab
2. Throttle network to "Offline"
3. Change a setting
4. Verify setting is saved to localStorage
5. Restore network
6. Verify setting syncs to backend

## Phase 6: Troubleshooting Checklist

| Issue | Check |
|-------|-------|
| Settings not persisting | Migration executed? Check `DESCRIBE user_settings;` |
| No sync requests | Check Network tab, verify global-settings-enhanced.js loaded |
| 401 errors | User logged in? Check `$_SESSION['user_id']` |
| Database errors | User exists in database? Check `SELECT * FROM users;` |
| UI not updating | Listen to `settingsChanged` event? |
| Load timeout | Sync delay is 1 second, normal |
| sync conflict | Client wins, then syncs to backend |

## Complete Code Example: Custom Settings Hook

Create a reusable hook for components:

```javascript
// Create a file: frontend/js/use-settings.js

class SettingsHook {
  constructor(settingKey) {
    this.settingKey = settingKey;
    this.listeners = [];
  }
  
  getValue() {
    return globalSettings.getSetting(this.settingKey);
  }
  
  setValue(value) {
    globalSettings.updateSetting(this.settingKey, value);
  }
  
  onChange(callback) {
    const listener = (event) => {
      if (event.detail[this.settingKey] !== undefined) {
        callback(event.detail[this.settingKey]);
      }
    };
    window.addEventListener('settingsChanged', listener);
    this.listeners.push([callback, listener]);
  }
  
  offChange(callback) {
    const pair = this.listeners.find(p => p[0] === callback);
    if (pair) {
      window.removeEventListener('settingsChanged', pair[1]);
      this.listeners = this.listeners.filter(p => p[0] !== callback);
    }
  }
}

// Usage in any component:
// const themeSetting = new SettingsHook('theme');
// console.log(themeSetting.getValue()); // 'light'
// themeSetting.onChange((newValue) => console.log('Theme changed to:', newValue));
// themeSetting.setValue('dark');
```

## Deployment to Production

When deploying to production:

1. Ensure database migration is executed on production database
2. Update all HTML files to use global-settings-enhanced.js
3. Verify CORS settings in settings.php match your domain
4. Test with real user accounts
5. Monitor logs for any sync errors
6. Set up monitoring for user_settings table size
7. Verify settings sync works over HTTPS (if applicable)

## Success Criteria

✅ Settings are saved to database  
✅ Settings persist across page refreshes  
✅ Settings sync across browser tabs  
✅ Multiple settings update in single request  
✅ Reset to defaults works  
✅ Invalid values are rejected  
✅ Network errors don't break functionality  
✅ UI responds immediately to changes  

If all criteria are met, the integration is complete!
