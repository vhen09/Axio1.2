# Settings System - Developer Reference

## Quick Reference for Common Tasks

### 1. Access Settings in Any Page

```javascript
// Include the manager
<script src="../js/global-settings-enhanced.js"></script>

// Get a setting (available after DOMContentLoaded)
const theme = globalSettings.getSetting('theme');
const fontSize = globalSettings.getSetting('fontSize');
```

### 2. Change a Setting

```javascript
// Single setting
globalSettings.updateSetting('theme', 'dark');

// Multiple settings at once
globalSettings.updateSettings({
  theme: 'dark',
  fontSize: 'large',
  autoSave: false
});
```

### 3. React to Settings Changes

```javascript
window.addEventListener('settingsChanged', (event) => {
  const settings = event.detail;
  console.log('Theme is now:', settings.theme);
  
  // Update UI
  if (settings.theme === 'dark') {
    document.body.classList.add('dark-mode');
  } else {
    document.body.classList.remove('dark-mode');
  }
});
```

### 4. Apply Values to DOM

```javascript
// Font size
const fontSize = globalSettings.getSetting('fontSize');
const fontSizeMap = {
  'small': '12px',
  'normal': '14px',
  'large': '16px'
};
document.documentElement.style.fontSize = fontSizeMap[fontSize];

// Theme
const theme = globalSettings.getSetting('theme');
document.documentElement.setAttribute('data-theme', theme);

// Language
const lang = globalSettings.getSetting('learningLanguage');
document.documentElement.lang = lang;
```

### 5. Conditional Logic Based on Settings

```javascript
// Feature flags
if (globalSettings.getSetting('autoSave')) {
  startAutoSave();
}

if (globalSettings.getSetting('showHints')) {
  revealHints();
}

// Enum settings
switch (globalSettings.getSetting('proofMode')) {
  case 'step-by-step':
    setupStepByStepMode();
    break;
  case 'full-proof':
    setupFullProofMode();
    break;
}

// Array settings
const domains = globalSettings.getSetting('preferredDomains');
if (domains.includes('calculus')) {
  showCalculusTheorems();
}
```

### 6. Backup and Restore Settings

```javascript
// Export for backup
const backup = globalSettings.exportSettings();
// Result: { timestamp: "2026-03-23T10:30:00", settings: {...} }

// Save to file
localStorage.setItem('settings_backup_20260323', JSON.stringify(backup));

// Later, restore from backup
const saved = JSON.parse(localStorage.getItem('settings_backup_20260323'));
globalSettings.importSettings(saved);
```

### 7. Get All Settings at Once

```javascript
const allSettings = globalSettings.getAllSettings();

// Result:
{
  theme: 'light',
  fontSize: 'normal',
  learningLanguage: 'en',
  proofMode: 'step-by-step',
  difficultyLevel: 'beginner',
  preferredDomains: ['algebra', 'calculus'],
  autoSave: true,
  showPreview: true,
  showHints: true,
  autoTutorial: false,
  scoreNotifications: true,
  feedbackNotifications: true,
  soundNotifications: false
}
```

### 8. Reset to Defaults

```javascript
await globalSettings.resetToDefaults();
```

### 9. Get Default Values

```javascript
const defaults = globalSettings.getDefaults();
// Same structure as getAllSettings() but with default values
```

## Common Patterns

### Pattern 1: Check if Feature Enabled

```javascript
if (globalSettings.getSetting('scoreNotifications')) {
  showNotification('Score: ' + score);
}
```

### Pattern 2: Apply Setting on Page Load

```javascript
document.addEventListener('DOMContentLoaded', () => {
  // Get setting
  const fontSize = globalSettings.getSetting('fontSize');
  
  // Apply to DOM
  const sizeValues = { small: '12px', normal: '14px', large: '16px' };
  document.body.style.fontSize = sizeValues[fontSize];
  
  // Listen for future changes
  window.addEventListener('settingsChanged', (e) => {
    document.body.style.fontSize = sizeValues[e.detail.fontSize];
  });
});
```

### Pattern 3: Set Multiple Settings Based on Profile

```javascript
const profiles = {
  beginner: {
    difficulty: 'beginner',
    showHints: true,
    autoTutorial: true,
    proofMode: 'step-by-step'
  },
  advanced: {
    difficulty: 'expert',
    showHints: false,
    autoTutorial: false,
    proofMode: 'full-proof'
  }
};

function selectProfile(name) {
  globalSettings.updateSettings(profiles[name]);
}
```

### Pattern 4: Debounce User Input

```javascript
let debounceTimer;

function onUserSettingChange(key, value) {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    globalSettings.updateSetting(key, value);
  }, 300); // Your debounce, on top of the 1s backend debounce
}

// Usage in event listener
document.getElementById('custom-input').addEventListener('input', (e) => {
  onUserSettingChange('customSetting', e.target.value);
});
```

### Pattern 5: Conditional Rendering Based on Settings

```javascript
function renderUI() {
  const fontSize = globalSettings.getSetting('fontSize');
  const theme = globalSettings.getSetting('theme');
  
  return `
    <div class="container" data-size="${fontSize}" data-theme="${theme}">
      ${globalSettings.getSetting('showHints') ? `<div class="hints">...</div>` : ''}
      ${globalSettings.getSetting('autoTutorial') ? `<button>Start Tutorial</button>` : ''}
    </div>
  `;
}

// Update when settings change
window.addEventListener('settingsChanged', () => {
  document.getElementById('app').innerHTML = renderUI();
});
```

## API Error Handling

### Pattern 1: Handle Network Errors Gracefully

```javascript
// The system handles this automatically, but here's how to detect it

// Method 1: Monitor sync status
let lastSyncSuccess = true;

window.addEventListener('settingsChanged', async () => {
  try {
    // If this throws, sync failed
    await fetch('../../backend/api/settings.php?action=sync', {
      method: 'POST',
      credentials: 'include'
    });
    lastSyncSuccess = true;
    console.log('Settings synced successfully');
  } catch (error) {
    lastSyncSuccess = false;
    console.log('Sync failed, settings saved locally');
  }
});
```

### Pattern 2: Show Sync Status to User

```javascript
function showSyncStatus(status) {
  const indicator = document.getElementById('sync-indicator');
  indicator.textContent = status;
  indicator.style.color = status === 'synced' ? 'green' : 'orange';
}

// Hook into settings changes
window.addEventListener('settingsChanged', (e) => {
  showSyncStatus('syncing...');
  
  // After sync completes (debounce delay + request time)
  setTimeout(() => {
    showSyncStatus('synced');
  }, 2000);
});
```

## Database Operations

### Query Settings for User

```sql
-- MySQL: Get all settings for user ID 1
SELECT * FROM user_settings WHERE user_id = 1;

-- Get specific settings
SELECT theme, fontSize, proofMode FROM user_settings WHERE user_id = 1;

-- Get with timestamp
SELECT * FROM user_settings WHERE user_id = 1 AND updated_at > '2026-03-23 10:00:00';
```

### Update Settings (Direct)

```sql
-- Direct database update (use settings API instead for production)
UPDATE user_settings SET theme = 'dark', fontSize = 'large' WHERE user_id = 1;
```

### Bulk Operations

```sql
-- Reset all users to defaults
UPDATE user_settings SET 
  theme = 'light',
  fontSize = 'normal',
  proofMode = 'step-by-step',
  difficultyLevel = 'beginner',
  autoSave = 'true',
  showPreview = 'true',
  showHints = 'true',
  autoTutorial = 'false',
  scoreNotifications = 'true',
  feedbackNotifications = 'true',
  soundNotifications = 'false';

-- Migration: Add new setting
ALTER TABLE user_settings ADD COLUMN newSetting VARCHAR(255) DEFAULT 'default_value';
```

## Testing

### Test in Browser Console

```javascript
// Check if manager is loaded
console.log(typeof globalSettings); // Should be 'object'

// Get all settings
console.log(globalSettings.getAllSettings());

// Update a setting
globalSettings.updateSetting('theme', 'dark');

// Check localStorage
console.log(JSON.parse(localStorage.getItem('axio_settings')));

// Monitor sync requests
globalSettings.updateSettings({ theme: 'light', fontSize: 'large' });
// Watch Network tab for POST to settings.php
```

### Test with curl

```bash
# Get settings (requires valid session cookie)
curl -b "PHPSESSID=your_session_id" \
  http://localhost:8080/backend/api/settings.php?action=get

# Update setting
curl -b "PHPSESSID=your_session_id" \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"key":"theme","value":"dark"}' \
  http://localhost:8080/backend/api/settings.php?action=update

# Batch update
curl -b "PHPSESSID=your_session_id" \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"settings":{"theme":"dark","fontSize":"large"}}' \
  http://localhost:8080/backend/api/settings.php?action=update_batch
```

## Performance Tips

### 1. Use Batch Updates for Multiple Changes

```javascript
// DON'T: Multiple individual updates
globalSettings.updateSetting('a', 1);
globalSettings.updateSetting('b', 2);
globalSettings.updateSetting('c', 3);
// This causes 3 network requests (eventually batched though)

// DO: Single batch update
globalSettings.updateSettings({ a: 1, b: 2, c: 3 });
// This causes 1 network request
```

### 2. Cache Settings References

```javascript
// DON'T: Call getSetting() repeatedly in loops
for (let i = 0; i < 1000; i++) {
  if (globalSettings.getSetting('showHints')) { // Called 1000 times
    // process
  }
}

// DO: Cache the setting
const showHints = globalSettings.getSetting('showHints');
for (let i = 0; i < 1000; i++) {
  if (showHints) {
    // process
  }
}
```

### 3. Avoid Excessive Listeners

```javascript
// DON'T: Add multiple listeners for the same setting
window.addEventListener('settingsChanged', () => updateUI());
window.addEventListener('settingsChanged', () => updateUI());
window.addEventListener('settingsChanged', () => updateUI());

// DO: Add once, or use a single handler
window.addEventListener('settingsChanged', () => updateUI());
```

### 4. Debounce Expensive Operations

```javascript
let debounceTimer;

window.addEventListener('settingsChanged', (event) => {
  clearTimeout(debounceTimer);
  
  debounceTimer = setTimeout(() => {
    // Expensive operation (e.g., re-render entire page)
    expensiveUpdate(event.detail);
  }, 100); // Wait 100ms after last change
});
```

## Valid Setting Values

| Setting | Type | Valid Values |
|---------|------|--------------|
| theme | string | 'light', 'dark', 'auto' |
| fontSize | string | 'small', 'normal', 'large' |
| learningLanguage | string | Any ISO-639-1 code ('en', 'es', 'fr', etc.) |
| proofMode | string | 'step-by-step', 'full-proof' |
| difficultyLevel | string | 'beginner', 'intermediate', 'advanced' |
| preferredDomains | array | ['domain1', 'domain2', ...] |
| autoSave | boolean | true, false |
| showPreview | boolean | true, false |
| showHints | boolean | true, false |
| autoTutorial | boolean | true, false |
| scoreNotifications | boolean | true, false |
| feedbackNotifications | boolean | true, false |
| soundNotifications | boolean | true, false |

## Troubleshooting Code Snippets

### Check if Service is Running

```javascript
// Check if globalSettings exists
if (typeof globalSettings === 'undefined') {
  console.error('Settings service not loaded');
} else {
  console.log('Settings service ready');
}

// Check if settings were loaded from backend
globalSettings.getAllSettings().then(() => {
  console.log('Settings loaded from backend');
});
```

### Monitor Sync Requests

```javascript
// Patch fetch to monitor settings API calls
const originalFetch = window.fetch;
window.fetch = function(...args) {
  if (args[0]?.includes('settings.php')) {
    console.log('Settings API call:', args);
  }
  return originalFetch.apply(this, args);
};
```

### Debug: Check localStorage and Backend Sync

```javascript
// Check localStorage
const local = JSON.parse(localStorage.getItem('axio_settings') || '{}');
console.log('Local settings:', local);

// Check backend (requires authentication)
fetch('../../backend/api/settings.php?action=get', {
  credentials: 'include'
}).then(r => r.json()).then(data => {
  console.log('Backend settings:', data);
});

// Compare
console.log('Differences:');
Object.keys(local).forEach(key => {
  if (local[key] !== data.settings[key]) {
    console.log(`${key}: local="${local[key]}" backend="${data.settings[key]}"`);
  }
});
```

---

**For more details, see:**
- `SETTINGS_SYSTEM_DOCUMENTATION.md` - Complete API reference
- `SETTINGS_SYSTEM_QUICKSTART.md` - Setup and testing guide
- `SETTINGS_SYSTEM_INTEGRATION_GUIDE.md` - Step-by-step implementation
