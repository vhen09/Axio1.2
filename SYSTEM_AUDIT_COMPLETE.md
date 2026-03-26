# AXIO SYSTEM - COMPLETE AUDIT & FILE TRACKING

**Date**: March 26, 2026  
**Architecture**: Single Backend (PHP) + Dual Frontend (Vanilla JS + React)  
**Status**: Ready for Hybrid Consolidation

---

## TABLE OF CONTENTS
1. [System Overview](#system-overview)
2. [Entry Points](#entry-points)
3. [Frontend - Vanilla JS (Active)](#frontend---vanilla-js-active)
4. [Frontend - React (Archived)](#frontend---react-archived)
5. [Backend - Shared](#backend---shared)
6. [Database & Configuration](#database--configuration)
7. [Dependencies & Relationships](#dependencies--relationships)
8. [Dead Code & Candidates for Removal](#dead-code--candidates-for-removal)

---

## SYSTEM OVERVIEW

### Architecture Layers

```
┌─────────────────────────────────────────────────────────────┐
│ Entry Point: index.php                                      │
│ ↓                                                           │
│ Auto-Setup: backend/config/auto-setup.php (DB init)       │
│ ↓                                                           │
├─────────────────────────────────────────────────────────────┤
│ Frontend Layer (DUAL - Switch via Feature Flag)             │
│ ├─ Vanilla JS: /frontend/pages/                             │
│ └─ React: /axio2.0/lean4-ai-web-app/frontend/             │
├─────────────────────────────────────────────────────────────┤
│ Backend API Layer: /backend/api/                            │
│ ├─ Services: /backend/services/                             │
│ └─ Models: /backend/models/                                 │
├─────────────────────────────────────────────────────────────┤
│ Data Layer (PostgreSQL/SQLite fallback)                     │
│ ├─ /database/schema.sql                                    │
│ └─ /database/seed_theorems.sql                             │
└─────────────────────────────────────────────────────────────┘
```

---

## ENTRY POINTS

### 1. Primary Entry
- **File**: `index.php`
- **Action**: 
  - Calls `backend/config/auto-setup.php` for database initialization
  - Redirects to `frontend/pages/welcome.html`
- **Status**: ✅ ACTIVE

### 2. Welcome/Landing
- **File**: `frontend/pages/welcome.html`
- **Action**: Redirects to `landing.html`
- **Status**: ✅ ACTIVE

### 3. Landing Page
- **File**: `frontend/pages/landing.html`
- **Action**: 
  - Shows brand, login/signup buttons
  - Loads dark mode, CSS frameworks
  - Entry to auth flow
- **Status**: ✅ ACTIVE
- **Loads**: 
  - CSS: `axio-app.css`, `enhanced-ui.css`, `dark-mode.css`, `proof-modes.css`
  - JS: `dark-mode-manager.js`

---

## FRONTEND - VANILLA JS (ACTIVE)

### Location
`/frontend/`

### HTML Pages (18 total)

| Page | Purpose | Status | Entry Point? |
|------|---------|--------|--------------|
| `welcome.html` | Redirect hub | ✅ | Primary |
| `landing.html` | Brand + Auth | ✅ | Secondary |
| `auth.html` | Login/Signup form | ✅ | Yes |
| `dashboard.html` | Main workspace | ✅ | Yes |
| `proofworkspace.html` | Proof editor | ✅ | Yes |
| `submissions.html` | My proofs list | ✅ | Yes |
| `scores.html` | Score display | ✅ | Yes |
| `settings.html` | User settings | ✅ | Yes |
| `tutor.html` | AI tutor interface | ✅ | Yes |
| `tutor3.html` | Tutor variant v3 | ✅ | Yes |
| `theorems.html` | Theorem browser | ? | Maybe |
| `tutorial.html` | Interactive tutorial | ✅ | Yes |
| `learn-latex.html` | LaTeX guide | ✅ | Yes |
| `math-symbols-tutorial.html` | Symbol reference | ✅ | Yes |
| `symbols-library.html` | Symbol library | ✅ | Yes |
| `latex-skill-check.html` | LaTeX test | ? | Maybe |
| `login.html` | Login only | ? | Duplicate? |
| `myproofs.html` | My proofs variant | ? | Duplicate? |
| `proofs.html` | Proofs variant | ? | Duplicate? |

**Total**: 18 pages  
**Active**: ~14 pages  
**Candidates for Cleanup**: `login.html`, `myproofs.html`, `proofs.html` (likely duplicates)

### JavaScript Modules (18 total)

| Module | Purpose | Status | Type |
|--------|---------|--------|------|
| `app.js` | Main app controller | ✅ | Core |
| `api-client.js` | HTTP API wrapper | ✅ | Core |
| `axio-app.js` | App state & logic | ✅ | Core |
| `auth-handler.js` | Login/logout flow | ✅ | Feature |
| `dark-mode-manager.js` | Theme switching | ✅ | Feature |
| `tutorial.js` | Tutorial logic | ✅ | Feature |
| `tutor-client.js` | AI tutor API client | ✅ | Feature |
| `toast-notifications.js` | Toast UI | ✅ | UI |
| `symbol-library.js` | Math symbols | ✅ | Feature |
| `sidebar.js` | Sidebar navigation | ✅ | UI |
| `proof-mode-manager.js` | Proof editing modes | ✅ | Feature |
| `proof-mode-controller.js` | Step-by-step control | ✅ | Feature |
| `logout-manager.js` | Session cleanup | ✅ | Feature |
| `interactive-tutorial.js` | Tutorial system | ✅ | Feature |
| `global-settings.js` | User preferences | ✅ | Data |
| `global-settings-enhanced.js` | Extended settings | ✅ | Data |
| `enhanced-equation-keyboard.js` | LaTeX input | ✅ | UI |
| `equation-component.js` | Equation rendering | ? | UI |

**Total**: 18 modules  
**Core**: 3 modules  
**Features**: 8 modules  
**UI Components**: 5 modules  
**Data**: 2 modules

### CSS Files (Vanilla JS)

| File | Purpose | Status |
|------|---------|--------|
| `axio-app.css` | Main styles | ✅ |
| `enhanced-ui.css` | Enhanced layout | ✅ |
| `dark-mode.css` | Theme system | ✅ |
| `proof-modes.css` | Proof editor UI | ✅ |
| `toast-notifications.css` | Toast styles | ✅ |
| `enhanced-equation-keyboard.css` | LaTeX keyboard UI | ✅ |

**Total**: 6 CSS files

---

## FRONTEND - REACT (ARCHIVED)

### Location
`/axio2.0/lean4-ai-web-app/frontend/`

### TSX Components

| Component | Purpose | Vanilla Equivalent |
|-----------|---------|-------------------|
| `App.tsx` | Root component | `app.js` + `axio-app.js` |
| `AppLayout.tsx` | Layout wrapper | `dashboard.html` |
| `AppContext.tsx` | Global state | `global-settings.js` |
| `AppSidebar.tsx` | Navigation | `sidebar.js` |
| `ProofWorkspace.tsx` | Editor | `proofworkspace.html` |
| `ProofStepEditor.tsx` | Step input | `proof-mode-controller.js` |
| `VerificationPanel.tsx` | Results | HTML in `dashboard.html` |
| `EquationKeyboard.tsx` | LaTeX input | `enhanced-equation-keyboard.js` |
| `LaTeXPreview.tsx` | Equation render | `equation-component.js` |
| `MyProofs.tsx` | Proofs list | `submissions.html` |
| `Scores.tsx` | Scoring | `scores.html` |
| `SettingsPage.tsx` | User settings | `settings.html` |

**Total**: 12 components

### Styling
- `App.css` (local styles)
- `index.css` (global styles)
- `tailwind.config.ts` (Tailwind CSS)

### Config
- `components.json` (shadcn/ui config)

**Status**: ❌ ARCHIVED (Not currently served)

---

## BACKEND - SHARED

### Configuration Files

| File | Purpose | Status |
|------|---------|--------|
| `backend/config/auto-setup.php` | DB initialization | ✅ |
| `backend/config/database.php` | DB connection (PostgreSQL/SQLite) | ✅ |
| `backend/config/database_v2.php` | V2 connection (alternative) | ? |
| `backend/config/deepseek.php` | AI API config | ✅ |

### API Endpoints (17 total)

| Endpoint | Purpose | Status | Calls |
|----------|---------|--------|-------|
| `api/auth.php` | Login/logout/signup | ✅ | Core |
| `api/proof.php` | Proof submission | ✅ | Core |
| `api/tutor.php` | AI tutor responses | ✅ | Core |
| `api/lean.php` | Lean verification | ✅ | Core |
| `api/theorems.php` | Theorem list | ✅ | Feature |
| `api/submissions.php` | User submissions | ✅ | Feature |
| `api/scores.php` | Scoring data | ✅ | Feature |
| `api/settings.php` | User settings save/load | ✅ | Feature |
| `api/user-preferences.php` | Preference API | ✅ | Feature |
| `api/user-profile.php` | Profile data | ✅ | Feature |
| `api/scoring.php` | Scoring logic | ✅ | Feature |
| `api/health.php` | System health check | ✅ | Optional |
| `api/status.php` | System status | ✅ | Optional |
| `api/therapeutics.php` | Diagnostic API | ? | Debug |
| `api/analytics.php` | Usage stats | ? | Optional |
| `api/diagnostics.php` | System diagnostics | ? | Debug |
| `api/gm-proof.php` | General math proofs | ? | Feature |

**Total**: 17 endpoints  
**Core**: 4 endpoints  
**Features**: 8 endpoints  
**Optional/Debug**: 5 endpoints

### Services (10 total)

| Service | Purpose | Status | Used By |
|---------|---------|--------|---------|
| `DeepSeekService.php` | ChatGPT-like AI API calls | ✅ | `api/tutor.php` |
| `ProofTutorService.php` | Tutoring logic | ✅ | `api/tutor.php` |
| `ProofScoringService.php` | Scoring algorithm | ✅ | `api/scores.php` |
| `LeanService.php` | Lean verification (mock) | ✅ | `api/lean.php` |
| `NaturalLanguageToLeanConverter.php` | NL to Lean conversion | ✅ | `services/` |
| `GeneralMathProofService.php` | Non-Lean proofs | ? | `api/gm-proof.php` |
| `SettingsService.php` | Settings management | ✅ | `api/settings.php` |
| `ValidationService.php` | Input validation | ✅ | `api/` |
| `Logger.php` | Logging utility | ✅ | All services |
| `ProofTutorService_backup.php` | Backup version | ❌ | None (cleanup candidate) |

**Total**: 10 services  
**Active**: 9 services  
**Backup/Dead**: 1 service

### Data Models (4 total)

| Model | Purpose | Status |
|-------|---------|--------|
| `User.php` | User entity & auth | ✅ |
| `Score.php` | Score entity | ✅ |
| `Submission.php` | Proof submission entity | ✅ |
| `UserPreferences.php` | Settings entity | ✅ |

**Total**: 4 models (all active)

### Database Files

| File | Purpose | Status |
|------|---------|--------|
| `database/schema.sql` | Table structure | ✅ |
| `database/seed_theorems.sql` | 50+ theorems | ✅ |
| `database/setup-postgres.php` | PostgreSQL setup | ✅ |
| `database/migrate.php` | Migration runner | ✅ |
| `database/migrate_mysql_to_postgres.php` | MySQL→PostgreSQL | ✅ |
| `database/migrate_add_user_names.sql` | Schema migration | ✅ |

---

## DEPENDENCIES & RELATIONSHIPS

### Frontend JS Dependency Chain

```
index.php
  ↓
frontend/pages/welcome.html
  ↓
frontend/pages/landing.html
  ├─ css/axio-app.css
  ├─ css/enhanced-ui.css
  ├─ css/dark-mode.css
  ├─ css/proof-modes.css
  ├─ js/dark-mode-manager.js
  │
  └─ [Click Login] → auth.html
      ├─ js/api-client.js
      ├─ js/auth-handler.js
      ├─ js/toast-notifications.js
      └─ POST backend/api/auth.php
          ↓
          [Success] → dashboard.html
          │
          ├─ js/app.js
          ├─ js/axio-app.js
          ├─ js/sidebar.js
          ├─ js/global-settings.js
          ├─ js/interactive-tutorial.js
          │
          ├─ [Click "New Proof"] → proofworkspace.html
          │   ├─ js/proof-mode-controller.js
          │   ├─ js/enhanced-equation-keyboard.js
          │   ├─ js/equation-component.js
          │   └─ POST backend/api/proof.php
          │
          ├─ [Click "AI Tutor"] → tutor.html
          │   ├─ js/tutor-client.js
          │   └─ POST backend/api/tutor.php
          │
          ├─ [Click "My Proofs"] → submissions.html
          │   └─ GET backend/api/submissions.php
          │
          ├─ [Click "Scores"] → scores.html
          │   └─ GET backend/api/scores.php
          │
          └─ [Click "Settings"] → settings.html
              ├─ js/global-settings-enhanced.js
              └─ POST backend/api/settings.php
```

### Backend API Call Chain

```
Auth Flow:
  POST /auth.php?action=signup/login/logout
    └─ models/User.php
       api/auth.php

Proof Flow:
  POST /proof.php?action=submit
    ├─ services/ProofTutorService.php
    ├─ services/ProofScoringService.php
    ├─ services/ValidationService.php
    └─ models/Submission.php

Tutor Flow:
  POST /tutor.php?action=explain/verify
    ├─ services/DeepSeekService.php (AI)
    ├─ services/ProofTutorService.php
    ├─ services/NaturalLanguageToLeanConverter.php
    └─ services/LeanService.php

Settings Flow:
  GET/POST /settings.php
    ├─ services/SettingsService.php
    └─ models/UserPreferences.php
```

---

## DEAD CODE & CLEANUP CANDIDATES

### Likely Duplicates (Recommend Removal)

| File | Reason | Recommendation |
|------|--------|-----------------|
| `frontend/pages/login.html` | Functionality in `auth.html` | Remove |
| `frontend/pages/myproofs.html` | Functionality in `submissions.html` | Remove |
| `frontend/pages/proofs.html` | Functionality in `submissions.html` | Remove |
| `backend/config/database_v2.php` | Alternative connection? | Remove |
| `backend/services/ProofTutorService_backup.php` | Backup/outdated | Remove |

### Optional/Unused Endpoints (Low Priority)

| Endpoint | Status | Recommendation |
|----------|--------|-----------------|
| `api/health.php` | Optional | Keep for monitoring |
| `api/status.php` | Optional | Keep for monitoring |
| `api/analytics.php` | Unused? | Review usage, consider removing |
| `api/diagnostics.php` | Debug only | Move to `/backend/` directory |
| `api/gm-proof.php` | General math? | Define scope or remove |

### Test/Migration Scripts (Keep in `backend/` or `database/`)

These should NOT be served as public API:

| File | Purpose | Location |
|------|---------|----------|
| `test-complete-proof.php` | Testing | ✅ At root (OK) |
| `test-db-connection.php` | Testing | ✅ At root (OK) |
| `run-migration.php` | Migration | ✅ At root (OK) |
| `backend/check-database.php` | Diagnostic | ✅ In backend (OK) |
| `backend/signup-diagnostic.php` | Diagnostic | ✅ In backend (OK) |

---

## SUMMARY STATISTICS

### Frontend Files

| Type | Count | Status |
|------|-------|--------|
| HTML Pages | 18 | 14 active |
| JS Modules | 18 | 18 active |
| CSS Files | 6 | 6 active |
| **Total FE** | **42** | **38 active** |

### Backend Files

| Type | Count | Status |
|------|-------|--------|
| API Endpoints | 17 | 12+ active |
| Services | 10 | 9 active |
| Models | 4 | 4 active |
| Config | 4 | 3 active |
| **Total BE** | **35** | **28+ active** |

### Database Files

| Type | Count | Status |
|------|-------|--------|
| Schema/Setup | 6 | 6 active |

### Total codebase

| Category | Count |
|----------|-------|
| **Active Files** | **72+** |
| **Cleanup Candidates** | **4-8** |
| **Archived** | **8-12** (React version) |

---

## HYBRID ARCHITECTURE NOTES

For Option 3 (Hybrid Approach):

1. **Feature Detection System** needed to switch between:
   - Vanilla JS frontend (current production)
   - React frontend (alternative)

2. **No Backend Changes** - Both frontends use the same 17 APIs

3. **Frontend Integration Point** at entry level:
   - `index.php` could detect `?frontend=react` or `?frontend=vanilla`
   - Redirect accordingly
   - OR load bootstrap app that renders either

4. **Key Changes Needed**:
   - Create `/frontend/hybrid-bootstrap.html` or `/index-hybrid.php` for routing
   - Add `frontend-selector.js` module for user preference switching
   - Register both frontend choices in user preferences
   - Maintain both frontends side-by-side during transition

5. **Files to Create**:
   - `frontend/config/feature-flags.js` (frontend mode detection)
   - `frontend/hybrid-router.html` (unified entry point)
   - `backend/api/frontend-preference.php` (save user's frontend choice)
   - `HYBRID_IMPLEMENTATION_GUIDE.md` (instructions)

