# Settings System - Visual Architecture & Flow Diagrams

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     USER INTERFACE                          │
│            settings.html (settings page UI)                 │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Theme Toggle | Font Size | Language | etc.        │    │
│  │ (14 different on-page controls)                   │    │
│  └────────────────────────────────────────────────────┘    │
└──────────────────────────┬──────────────────────────────────┘
                          │
                          │ events
                          ▼
┌─────────────────────────────────────────────────────────────┐
│         FRONTEND SETTINGS MANAGER                           │
│    global-settings-enhanced.js (JavaScript Class)          │
│  ┌────────────────────────────────────────────────────┐    │
│  │ ✓ Load from backend on page load                  │    │
│  │ ✓ Update in-memory settings                       │    │
│  │ ✓ Save to localStorage (fallback)                 │    │
│  │ ✓ Apply theme/font to DOM                         │    │
│  │ ✓ Debounce sync (1000ms wait)                    │    │
│  │ ✓ Dispatch custom events                          │    │
│  └────────────────────────────────────────────────────┘    │
└──────────────────────────┬──────────────────────────────────┘
                          │
           ┌──────────────┴──────────────┐
           │                             │
           ▼                             ▼
    [localStorage]              [HTTP POST/GET]
    (Client Cache)                (Backend API)
           │                             │
           │                             ▼
           │         ┌─────────────────────────────────────┐
           │         │  API ENDPOINT                       │
           │         │  settings.php                       │
           │         │  ┌───────────────────────────────┐ │
           │         │  │ Route 6 actions:              │ │
           │         │  │ - get (fetch all)             │ │
           │         │  │ - update (single)             │ │
           │         │  │ - update_batch (multiple)     │ │
           │         │  │ - reset (to defaults)         │ │
           │         │  │ - sync (from client)          │ │
           │         │  │ - get_defaults (no auth)      │ │
           │         │  └───────────────────────────────┘ │
           │         └──────────┬──────────────────────────┘
           │                    │
           │                    ▼
           │         ┌─────────────────────────────────────┐
           │         │  SERVICE LAYER                      │
           │         │  SettingsService.php                │
           │         │  ┌───────────────────────────────┐ │
           │         │  │ • Validate all values         │ │
           │         │  │ • Type conversion             │ │
           │         │  │ • Prepare for database        │ │
           │         │  │ • Execute SQL queries         │ │
           │         │  │ • Error handling              │ │
           │         │  │ • Logging                     │ │
           │         │  └───────────────────────────────┘ │
           │         └──────────┬──────────────────────────┘
           │                    │
           │                    ▼
           │         ┌─────────────────────────────────────┐
           │         │  DATABASE                           │
           │         │  user_settings table                │
           │         │  ┌───────────────────────────────┐ │
           │         │  │ Columns (13 settings):        │ │
           │         │  │ • theme                       │ │
           │         │  │ • fontSize                    │ │
           │         │  │ • learning Language           │ │
           │         │  │ • proofMode                   │ │
           │         │  │ • difficultyLevel             │ │
           │         │  │ • preferredDomains (JSON)     │ │
           │         │  │ • autoSave                    │ │
           │         │  │ • showPreview                 │ │
           │         │  │ • showHints                   │ │
           │         │  │ • autoTutorial                │ │
           │         │  │ • scoreNotifications          │ │
           │         │  │ • feedbackNotifications       │ │
           │         │  │ • soundNotifications          │ │
           │         │  │ + timestamps (audit trail)    │ │
           │         │  └───────────────────────────────┘ │
           │         └─────────────────────────────────────┘
           │                    ▲
           │                    │
           └────────────────────┘
               (Fallback if no backend)
```

## Data Flow: User Changes Setting

```
User changes theme dropdown
         │
         ▼
updateSetting('theme', 'dark') called
         │
         ▼
✓ Validate 'dark' is valid value
✓ Update in-memory object
✓ Apply to DOM immediately
✓ Save localStorage
✓ Schedule sync (1000ms) ─────┐
         │                     │
    (Display updated          │
     immediately)         Wait 1 second
                              │
         ┌────────────────────┘
         │
         ▼
POST /backend/api/settings.php
  Action: update_batch
  Body: {settings: {theme: 'dark'}}
         │
         ▼
settings.php routes to SettingsService
         │
         ▼
validateSettingValue('theme', 'dark')
  ├─ Check value in ['light', 'dark', 'auto']
  └─ ✓ Valid
         │
         ▼
UPDATE user_settings SET theme='dark' WHERE user_id=123
         │
         ▼
Return {success: true}
         │
         ▼
Frontend receives response
  ├─ Update successful
  └─ Settings persisted to database
```

## Multi-Page Synchronization Flow

```
┌─────────────────────┐        ┌─────────────────────┐
│  Tab 1: Settings    │        │  Tab 2: Proof       │
│  Page               │        │  Editor Page        │
└──────┬──────────────┘        └──────┬──────────────┘
       │                              │
       │ User changes theme           │
       │ globalSettings.updateSetting()
       │                              │
       ▼                              │
Settings saved locally               │
Storage event broadcasts             │
       │                              │
       ├──────────────────────────────▶ Other tab
       │        (storage event)        │ hears change
       │                              │
       │  Async POST to backend       │
       │  (debounced 1000ms)          │
       │         │                    │
       │         ▼                    │
       │    Database updated          │
       │         │                    │
       │         └─────────────┬──────┘
       │                       │
       │  (When Tab 2 loads     │
       │   or refreshes)        │
       │                       │
       ▼                       ▼
Settings persisted      Settings fetched from DB
across sessions         ✓ Consistent across devices
```

## Request/Response Cycle

```
CLIENT REQUEST:
═════════════════════════════════════════════════════════
POST /backend/api/settings.php

Headers:
  Content-Type: application/json
  Cookie: PHPSESSID=abc123...

Body:
{
  "settings": {
    "theme": "dark",
    "fontSize": "large"
  }
}

═════════════════════════════════════════════════════════

SERVER PROCESSING:
═════════════════════════════════════════════════════════
1. Check: $_SESSION['user_id'] exists? → YES ✓
2. Parse: JSON body → settings array
3. Validate: theme='dark' in allowed values? → YES ✓
4. Validate: fontSize='large' in allowed values? → YES ✓
5. Execute: UPDATE user_settings SET theme='dark', fontSize='large'
6. Return: Success response

═════════════════════════════════════════════════════════

SERVER RESPONSE:
═════════════════════════════════════════════════════════
HTTP 200 OK

{
  "success": true,
  "message": "Settings updated successfully",
  "count": 2
}

═════════════════════════════════════════════════════════

FRONTEND REACT:
═════════════════════════════════════════════════════════
✓ Response received
✓ Dispatch settingsChanged event
✓ Other listeners can react
✓ Settings confirmed in database
```

## Settings Lifecycle

```
USER LOGIN
   │
   ▼
Page loads global-settings-enhanced.js
   │
   ▼
Check: Is user authenticated?
   ├─ YES → Fetch from backend
   │         (globalSettings.initializeSync())
   │         │
   │         ▼
   │       GET /settings.php?action=get
   │         │
   │         ▼
   │       Return user's settings
   │         │
   │         ▼
   │       Merge with localStorage
   │         │
   │         ▼
   │       Update all DOM elements
   │
   └─ NO → Use localStorage
           (fallback)

USER INTERACTS
   │
   ▼
Change setting in form
   │
   ▼
globalSettings.updateSetting(key, value)
   │
   ├─ Validate value
   ├─ Update in-memory
   ├─ Update localStorage
   ├─ Apply to DOM
   └─ Schedule backend sync (1000ms)

BACKEND SYNC
   │
   ▼
After 1 second debounce
   │
   ▼
POST /settings.php
   │
   ├─ Service validates
   ├─ Database updates
   └─ Confirmation sent
      │
      └─ No UI change needed
         (already applied)

USER NAVIGATES
   │
   ▼
Leaves page / closes browser
   │
   ├─ localStorage persists (local cache)
   └─ Database persists (permanent)

USER RETURNS
   │
   ▼
Next visit / new device
   │
   ▼
Load page → Fetch from database
   │
   ▼
Settings appear as they were
   │
   └─ ✓ Persistence achieved
```

## Error Handling Flow

```
                    Network Error
                         │
         ┌───────────────┴───────────────┐
         │                               │
         ▼                               ▼
    Sync fails              (Connection restored)
    (timeout/offline)            │
         │                       ▼
         ├─ Log error        Retry sync
         ├─ Settings still   (automatic on next change)
         │  in localStorage       │
         │  (not lost)           ▼
         │                   Sync succeeds
         │                   Database updated
         │                       │
         └───────────────────────┘
                   │
                   ▼
           ✓ No data loss
           ✓ Eventual consistency
```

## Database Schema Visualization

```
user_settings TABLE (17 columns)
═════════════════════════════════════════════════════════

┌─────┬─────────┬───────────┬─────────┬──────────────┐
│ id  │ user_id │ theme     │ fontSize│ ... (11 more)│
├─────┼─────────┼───────────┼─────────┼──────────────┤
│  1  │   123   │ light     │ normal  │ ...          │
│  2  │   124   │ dark      │ large   │ ...          │
│  3  │   125   │ auto      │ small   │ ...          │
└─────┴─────────┴───────────┴─────────┴──────────────┘

Additional columns:
  - learning Language: ISO code
  - proofMode: step-by-step | full-proof
  - difficultyLevel: beginner|intermediate|advanced
  - preferredDomains: JSON array
  - autoSave: true|false
  - showPreview: true|false
  - showHints: true|false
  - autoTutorial: true|false
  - scoreNotifications: true|false
  - feedbackNotifications: true|false
  - soundNotifications: true|false
  - created_at: TIMESTAMP
  - updated_at: TIMESTAMP
```

## API Actions Overview

```
┌─────────────────────────────────────────────────────┐
│ GET action=get_defaults                             │
│ ├─ Requires: Nothing (public)                      │
│ ├─ Returns: Default values for all 13 settings     │
│ └─ Use: First-time setup                           │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ GET action=get                                      │
│ ├─ Requires: Authentication ($_SESSION['user_id'])│
│ ├─ Returns: All 13 settings for current user       │
│ └─ Use: Load settings on page load                 │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ POST action=update                                  │
│ ├─ Body: {key: 'theme', value: 'dark'}            │
│ ├─ Returns: Single updated setting                │
│ └─ Use: Update one setting at a time              │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ POST action=update_batch                            │
│ ├─ Body: {settings: {theme: 'dark', ...}}         │
│ ├─ Returns: Count of updated settings             │
│ └─ Use: Update multiple at once (preferred)        │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ DELETE action=reset                                 │
│ ├─ Body: {confirm: true}                           │
│ ├─ Returns: All settings at defaults               │
│ └─ Use: Reset all to factory defaults              │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ POST action=sync                                    │
│ ├─ Body: {clientSettings: {...}}                   │
│ ├─ Returns: Merged/resolved settings               │
│ └─ Use: Sync client with server                    │
└─────────────────────────────────────────────────────┘
```

## Authentication & Authorization

```
REQUEST ARRIVES
   │
   ▼
Check: Is this public action?
   ├─ YES (get_defaults)
   │   └─ Process without user
   │
   └─ NO (all others)
       │
       ▼
   Check: $_SESSION['user_id'] exists?
       ├─ NO
       │   └─ Return HTTP 401 Unauthorized
       │
       └─ YES
           │
           ▼
       Verify user_id is integer
           │
           ▼
       Load user's settings
           │
           ▼
       Return 200 OK with settings
```

## Performance: Debouncing Mechanism

```
User makes 5 rapid changes:

Time  Event       Backend
────  ──────────  ─────────────────────────────────
0ms   Change 1    (deferred, wait for more)
50ms  Change 2    (deferred, wait for more)
100ms Change 3    (deferred, wait for more)
150ms Change 4    (deferred, wait for more)
200ms Change 5    (deferred, wait for more)
      
      Timer counting...
1000ms SYNC        ✓ POST all 5 changes to backend
                      (single request, not 5)
1050ms Response    ✓ Backend updated
       
Result: 1 network request instead of 5!
```

## Fallback Strategy

```
Backend unavailable?
   │
   ▼
Settings saved to localStorage
   │
   ▼
User can continue working
   │
   ▼
Connection restored?
   │
   └─ YES
      │
      ▼
   Automatic sync attempts
      │
      ▼
   Data reaches database
      │
      └─ ✓ No data loss!
```

---

These diagrams show:
- ✓ Complete system architecture
- ✓ Data flow through all layers
- ✓ Request/response cycle
- ✓ Multi-page synchronization
- ✓ Error handling and recovery
- ✓ Debouncing mechanism
- ✓ Fallback strategy
- ✓ Authentication flow
