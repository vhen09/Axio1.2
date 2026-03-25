# Settings System - Quick Start Guide

## What's New

The AXIO application now has a centralized settings management system with:
- ✅ Database persistence (survives browser refresh and new sessions)
- ✅ Automatic frontend-backend synchronization
- ✅ Type-safe validation for all settings
- ✅ Batch update support for efficiency
- ✅ Reset to defaults functionality
- ✅ Backup/restore via export-import

## Implementation Checklist

### 1. Execute Database Migration (REQUIRED)

```bash
# Run from project root directory
mysql -u root lean4_ai_db < database/migrations/001_create_user_settings.sql
```

**Verify installation:**
```bash
mysql -u root lean4_ai_db -e "DESCRIBE user_settings;"
```

You should see 17 columns (id, user_id, 13 settings, created_at, updated_at).

### 2. Update Settings Page (REQUIRED)

**File**: `frontend/pages/settings.html`

**Change**: Replace the JavaScript imports to use the new backend-synced manager:

```html
<!-- OLD (remove this line) -->
<script src="../js/global-settings.js"></script>

<!-- NEW (add this line) -->
<script src="../js/global-settings-enhanced.js"></script>
```

### 3. Update Settings Page Controller (REQUIRED)

**File**: `frontend/pages/settings.html` (in the `<script>` section)

The existing event handlers should work with minimal changes. They currently call `globalSettings.updateSetting()` which is compatible. However, you should update the controller to hook into the new sync behavior:

**Example of what should already work:**
```javascript
document.getElementById('theme-toggle')?.addEventListener('change', (e) => {
  // This already works with the enhanced version
  globalSettings.updateSetting('theme', e.target.value);
});
```

**Recommended enhancement - add sync feedback:**
```javascript
document.getElementById('theme-toggle')?.addEventListener('change', async (e) => {
  globalSettings.updateSetting('theme', e.target.value);
  // Optional: Show sync status
  console.log('Syncing theme to backend...');
});
```

### 4. Test the System

1. **Open the application**: `http://localhost:8080/frontend/pages/settings.html`
2. **Change a setting**: Toggle theme or adjust font size
3. **Check Network tab** (DevTools → Network):
   - Should see a POST request to `settings.php?action=update_batch` after ~1 second delay
   - Status should be 200 OK
4. **Refresh the page**: Setting should persist
5. **Check database**:
   ```bash
   mysql -u root lean4_ai_db -e "SELECT * FROM user_settings WHERE user_id = 1;"
   ```

### 5. Integrate Across Other Pages (OPTIONAL)

Any page that needs to access settings should include:

```html
<script src="../js/global-settings-enhanced.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Access a setting
    const theme = globalSettings.getSetting('theme');
    const fontSize = globalSettings.getSetting('fontSize');
    
    // Listen for changes
    window.addEventListener('settingsChanged', (event) => {
      console.log('Settings changed:', event.detail);
      // Update UI based on new settings
    });
  });
</script>
```

## What Each File Does

### 1. **backend/services/SettingsService.php** (281 lines)
The core business logic for all settings operations.
- Validates all input values
- Handles type conversions
- Manages database operations
- Supports single and batch updates

### 2. **backend/api/settings.php** (212 lines)
The REST API endpoint that frontend calls.
- 6 actions: get, update, update_batch, reset, sync, get_defaults
- Session-based authentication
- Proper error handling and HTTP status codes

### 3. **database/migrations/001_create_user_settings.sql** (41 lines)
Database schema with 14 columns for all 13 settings plus metadata.
- Proper constraints and indexes
- ON DELETE CASCADE for data integrity
- Timestamps for audit trail

### 4. **frontend/js/global-settings-enhanced.js** (368 lines)
Enhanced frontend settings manager that replaces the original.
- Automatic initialization and backend sync
- Debounced synchronization (1 second delay)
- LocalStorage fallback if backend unavailable
- Custom event dispatch for reactive components

## API Endpoints Reference

### Get Current Settings
```
GET /backend/api/settings.php?action=get
```

### Update Single Setting
```
POST /backend/api/settings.php?action=update
Body: {"key": "theme", "value": "dark"}
```

### Update Multiple Settings
```
POST /backend/api/settings.php?action=update_batch
Body: {"settings": {"theme": "dark", "fontSize": "large"}}
```

### Reset to Defaults
```
DELETE /backend/api/settings.php?action=reset
Body: {"confirm": true}
```

### Get Defaults
```
GET /backend/api/settings.php?action=get_defaults
```

## Frontend API Reference

```javascript
// Initialize (automatic on page load)
// globalSettings is globally available

// Get all settings
globalSettings.getAllSettings()

// Get single setting
globalSettings.getSetting('theme')

// Update single setting
globalSettings.updateSetting('theme', 'dark')

// Update multiple settings
globalSettings.updateSettings({
  theme: 'dark',
  fontSize: 'large'
})

// Reset to defaults
globalSettings.resetToDefaults()

// Export settings (for backup)
globalSettings.exportSettings()

// Import settings (restore from backup)
globalSettings.importSettings(backupData)

// Listen for changes
window.addEventListener('settingsChanged', (event) => {
  console.log('New settings:', event.detail);
});
```

## Troubleshooting

### Settings Not Persisting

**Problem**: Changes don't survive page refresh

**Check**:
1. Is the database migration executed?
   ```bash
   mysql -u root lean4_ai_db -e "SHOW TABLES LIKE 'user_settings';"
   ```
   Should show "user_settings" table

2. Is the user authenticated?
   - Settings API checks `$_SESSION['user_id']`
   - Must be logged in for persistence

3. Check browser console for errors
   - DevTools → Console tab
   - Look for 401 or 500 errors

### No Network Requests Visible

**Problem**: Settings not syncing to backend

**Check**:
1. Open DevTools Network tab
2. Change a setting
3. Wait 1-2 seconds (debounce delay)
4. Look for POST request to settings.php

**If no request appears**:
- Check browser console for JavaScript errors
- Verify global-settings-enhanced.js is loaded
- Check that syncWithBackend() method is being called

### Database Error: Table Doesn't Exist

**Problem**: "SQLSTATE[42S02]: Table 'lean4_ai_db.user_settings' doesn't exist"

**Solution**:
```bash
# Run the migration
mysql -u root lean4_ai_db < database/migrations/001_create_user_settings.sql

# Verify
mysql -u root lean4_ai_db -e "DESCRIBE user_settings;"
```

### 401 Unauthorized

**Problem**: API returns 401 error

**Causes**:
- User not logged in (no `$_SESSION['user_id']`)
- Session expired

**Solution**:
- Log in to the application
- Ensure cookie handling enabled: `credentials: 'include'` in fetch (already set in code)

### Settings Not Updating in UI

**Problem**: Settings update in database but UI doesn't reflect changes

**Solution**:
- The enhanced manager only applies theme and font-size to DOM
- For other settings, listen to custom event:
  ```javascript
  window.addEventListener('settingsChanged', (event) => {
    const newSettings = event.detail;
    // Update your UI based on newSettings
  });
  ```

## Performance Notes

- **Sync delay**: Settings sync after 1 second of last change (debouncing)
- **Local first**: UI updates immediately, backend sync is background
- **Network failures**: Settings remain in localStorage, sync retries on next change
- **Batch updates**: Multiple changes in quick succession are batched together
- **Database queries**: Single query per update operation

## Security Notes

- All endpoints (except get_defaults) require authentication
- Input validation prevents invalid values from being stored
- Prepared statements prevent SQL injection
- CORS headers allow cross-origin requests (configurable)
- Session-based auth (no API keys needed)

## Next Steps

1. ✅ Execute database migration
2. ✅ Update settings.html to use new manager
3. ✅ Test in browser
4. ✅ Deploy to production
5. 📋 Future: Settings profiles, cloud sync, audit trail

## Support

For issues or questions:
- Check console logs (DevTools → Console)
- Review SETTINGS_SYSTEM_DOCUMENTATION.md for detailed API
- Verify all 4 files are in correct locations
- Check database migration was executed
