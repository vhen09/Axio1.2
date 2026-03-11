# Frontend Merge Status (lean4-ai-web-app + lovableaxio)

## Scope
- Merged into one **runtime app**: `lean4-ai-web-app`
- **Backend untouched**: no changes in `backend/api`, `backend/config`, `backend/services`, or AI response logic
- Merge performed on frontend structure and route compatibility only

## What was consolidated
- Added lovable source snapshot to:
  - `frontend/lovableaxio-src/`
- Added route compatibility pages:
  - `frontend/pages/proofs.html` -> redirects to `submissions.html`
  - `frontend/pages/workspace.html` -> redirects to `dashboard.html`
  - `frontend/pages/theorems.html` -> redirects to `dashboard.html`

## Archive action completed
- Original separate folder has been safely moved to:
  - `_archive/lovableaxio/`

## Frontend functional fixes completed
- Fixed submissions filter button handling to avoid browser-dependent implicit `event` usage.
- Added missing theorem route compatibility file to prevent dead links from landing/guide pages.

## Backend stabilization completed
- Hardened `backend/services/LeanService.php`:
  - Added missing `verify()` and `verifyProof()` methods used by APIs.
  - Added graceful fallback behavior when Lean service or cURL is unavailable.
- Hardened `backend/api/lean.php`:
  - Added CORS/OPTIONS handling, action compatibility, and JSON payload validation.
- Fixed auth runtime crash:
  - Reworked `backend/models/User.php` to PDO-backed model with `create()` and `findByUsername()` methods.
  - Updated `backend/api/auth.php` with robust input/session handling.
- Hardened proof/theorem APIs:
  - `backend/api/proof.php` now safely handles no-DB/demo mode in DB-dependent actions.
  - `backend/api/theorems.php` now supports default GET action and safe logger fallback.

## Backend smoke-check summary
- PHP lint passed for all key API/service files.
- Live endpoint checks passed for:
  - `auth.php` (GET)
  - `theorems.php` (default GET)
  - `scores.php` (GET)
  - `submissions.php` (GET)
  - `lean.php` (POST verify)

## Existing canonical pages
- Proof workspace/dashboard: `frontend/pages/dashboard.html`
- Tutor: `frontend/pages/tutor.html`
- My proofs/submissions: `frontend/pages/submissions.html`
- Scores: `frontend/pages/scores.html`
- Settings: `frontend/pages/settings.html`

## Notes
- This keeps one production app folder while preserving lovable sources inside the same app root for future UI syncing.
- If you want, the old sibling folder `axio2.0/lovableaxio` can be archived/deleted manually after final verification.
