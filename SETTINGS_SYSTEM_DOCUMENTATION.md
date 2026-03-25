# AXIO Settings Management System

## Overview

A centralized, scalable settings management system that provides uniform handling of user preferences across the entire application. Settings are persisted to the database while maintaining localStorage as a fallback.

## Architecture

### Layers

```
Frontend UI (settings.html)
    ↓
Global Settings Manager (global-settings-enhanced.js)
    ↓
Settings API (backend/api/settings.php)
    ↓
Settings Service (backend/services/SettingsService.php)
    ↓
Database (user_settings table)
```

## Settings Structure

### Available Settings

```javascript
{
  // UI Preferences
  theme: 'light|dark|auto',                    // Visual theme
  fontSize: 'small|normal|large',              // Base font size
  learningLanguage: 'en|es|fr|...',           // ISO language code

  // Proof Settings
  proofMode: 'step-by-step|full-proof',       // Default proof mode
  difficultyLevel: 'beginner|intermediate|advanced', // Content difficulty
  preferredDomains: ['algebra', 'calculus'],  // Math domains

  // Feature Flags
  autoSave: true|false,                       // Auto-save drafts
  showPreview: true|false,                    // Show proof preview
  showHints: true|false,                      // Show step hints
  autoTutorial: true|false,                   // Auto-start tutorial

  // Notifications
  scoreNotifications: true|false,             // Score alerts
  feedbackNotifications: true|false,          // Feedback alerts
  soundNotifications: true|false              // Sound effects
}
```

## Backend API

### Base URL
```
/backend/api/settings.php
```

### Authentication
All endpoints (except `get_defaults`) require authenticated session (`$_SESSION['user_id']`).

### Endpoints

#### 1. Get Current Settings
```
GET /backend/api/settings.php?action=get
```
**Response:**
```json
{
  "success": true,
  "settings": { ...all settings... },
  "last_updated": "2026-03-23 10:30:00"
}
```

#### 2. Get Default Settings
```
GET /backend/api/settings.php?action=get_defaults
```
**Response:**
```json
{
  "success": true,
  "defaults": { ...default values... }
}
```

#### 3. Update Single Setting
```
POST /backend/api/settings.php?action=update
Content-Type: application/json

{
  "key": "theme",
  "value": "dark"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Setting updated successfully",
  "setting": "theme",
  "value": "dark"
}
```

#### 4. Update Multiple Settings
```
POST /backend/api/settings.php?action=update_batch
Content-Type: application/json

{
  "settings": {
    "theme": "dark",
    "fontSize": "large",
    "autoSave": false
  }
}
```
**Response:**
```json
{
  "success": true,
  "message": "Settings updated successfully",
  "count": 3
}
```

#### 5. Reset to Defaults
```
DELETE /backend/api/settings.php?action=reset
Content-Type: application/json

{
  "confirm": true
}
```
**Response:**
```json
{
  "success": true,
  "message": "Settings reset to defaults",
  "settings": { ...default values... }
}
```

#### 6. Sync Settings
```
POST /backend/api/settings.php?action=sync
Content-Type: application/json

{
  "clientSettings": { ...current settings... }
}
```
**Response:**
```json
{
  "success": true,
  "message": "Settings synchronized",
  "settings": { ...merged settings... },
  "last_updated": "2026-03-23 10:30:00"
}
```

## Frontend Integration

### Using the Global Settings Manager

```javascript
// Get all settings
const allSettings = globalSettings.getAllSettings();

// Get single setting
const theme = globalSettings.getSetting('theme');

// Update single setting
globalSettings.updateSetting('theme', 'dark');

// Update multiple settings
globalSettings.updateSettings({
  theme: 'dark',
  fontSize: 'large',
  autoSave: false
});

// Reset to defaults
globalSettings.resetToDefaults();

// Export settings
const backup = globalSettings.exportSettings();

// Import settings
globalSettings.importSettings(backup);
```

### Listen for Settings Changes

```javascript
window.addEventListener('settingsChanged', (event) => {
  const settings = event.detail;
  console.log('Settings changed:', settings);
});
```

### Implement in Pages

```html
<!-- Include the enhanced settings manager -->
<script src="../js/global-settings-enhanced.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Access global settings
    if (globalSettings) {
      const currentTheme = globalSettings.getSetting('theme');
      console.log('Current theme:', currentTheme);
    }
  });

  // Listen for changes
  window.addEventListener('settingsChanged', (event) => {
    // React to changes
    updateUI(event.detail);
  });
</script>
```

## Database Schema

### user_settings Table

```sql
CREATE TABLE user_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  
  -- UI Preferences
  theme VARCHAR(20) DEFAULT 'light',
  fontSize VARCHAR(20) DEFAULT 'normal',
  learningLanguage VARCHAR(10) DEFAULT 'en',
  
  -- Proof Settings
  proofMode VARCHAR(50) DEFAULT 'step-by-step',
  difficultyLevel VARCHAR(20) DEFAULT 'beginner',
  preferredDomains JSON DEFAULT '["algebra", "calculus"]',
  
  -- Feature Flags
  autoSave VARCHAR(10) DEFAULT 'true',
  showPreview VARCHAR(10) DEFAULT 'true',
  showHints VARCHAR(10) DEFAULT 'true',
  autoTutorial VARCHAR(10) DEFAULT 'false',
  
  -- Notification Settings
  scoreNotifications VARCHAR(10) DEFAULT 'true',
  feedbackNotifications VARCHAR(10) DEFAULT 'true',
  soundNotifications VARCHAR(10) DEFAULT 'false',
  
  -- Timestamps
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_updated_at (updated_at)
);
```

## SettingsService Class

### Public Methods

#### `getSettings($userId): array`
Retrieves all settings for a user.

#### `updateSetting($userId, $settingKey, $value): array`
Updates a single setting with validation.

#### `updateSettings($userId, array $settings): array`
Updates multiple settings at once.

#### `resetSettings($userId): array`
Resets all settings to defaults.

#### `getDefaults(): array`
Returns default settings structure.

### Validation

Each setting is validated based on its type:
- **String settings** (theme, fontSize, etc.) - Checked against allowed values
- **Boolean settings** - Converted safely to boolean
- **Array settings** (preferredDomains) - Validated against allowed domains
- **Language codes** - ISO 639-1 format

## Adding New Settings

### Steps to Add a New Setting

1. **Update defaults** in both backend and frontend managers:
   ```php
   // SettingsService.php
   private $defaultSettings = [
       ...
       'newSetting' => 'defaultValue'
   ];
   ```

2. **Add database column:**
   ```sql
   ALTER TABLE user_settings ADD COLUMN newSetting VARCHAR(255) DEFAULT 'defaultValue';
   ```

3. **Add validation** in `validateSettingValue()`:
   ```php
   case 'newSetting':
       return in_array($value, ['allowed', 'values']) ? $value : null;
   ```

4. **Update settings page** (settings.html):
   ```html
   <div class="setting-item">
     <div class="setting-label">
       <strong>New Setting</strong>
       <small>Description</small>
     </div>
     <div class="setting-control">
       <button class="toggle-switch" id="new-setting-toggle"></button>
     </div>
   </div>
   ```

5. **Add event listener** in settings controller:
   ```javascript
   document.getElementById('new-setting-toggle')?.addEventListener('change', (e) => {
     globalSettings.updateSetting('newSetting', e.target.checked);
   });
   ```

## Error Handling

The system handles errors gracefully:

- **Database unavailable**: Falls back to localStorage only
- **Network error**: Settings saved locally, synced when connection restored
- **Invalid values**: Rejected during validation with detailed error messages
- **Unimplemented actions**: Returns 400 with available actions list

## Security Considerations

1. **Authentication check**: All sensitive endpoints require valid session
2. **Input validation**: All settings validated before persistence
3. **CORS protection**: Inline with origin restrictions (can be configured)
4. **SQL injection prevention**: Parameterized queries throughout
5. **Type safety**: Strict type checking on stored values

## Performance Optimization

1. **Debounced syncing**: Backend sync waits 1 second to batch changes
2. **Local-first design**: UI feedback immediate, background sync async
3. **Query optimization**: Single database query per operation
4. **Caching**: Frontend caches settings in memory

## Testing

### Test Backend Endpoints

```bash
# Get settings
curl -X GET http://localhost:8080/backend/api/settings.php?action=get \
  -H "Cookie: PHPSESSID=your_session_id"

# Update setting
curl -X POST http://localhost:8080/backend/api/settings.php?action=update \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{"key":"theme","value":"dark"}'

# Update batch
curl -X POST http://localhost:8080/backend/api/settings.php?action=update_batch \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{"settings":{"theme":"dark","fontSize":"large"}}'
```

### Test Frontend

```javascript
// In browser console
globalSettings.updateSetting('theme', 'dark');
globalSettings.getAllSettings();
```

## Future Enhancements

1. **Settings profiles**: Save/load preset configurations
2. **Sharing**: Export settings to share with others
3. **Cloud sync**: Multi-device synchronization
4. **Audit trail**: Track setting change history
5. **Granular permissions**: Restrict certain settings by role
6. **Scheduled changes**: Time-based setting adjustments
