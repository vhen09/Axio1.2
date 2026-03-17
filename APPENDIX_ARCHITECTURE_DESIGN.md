# APPENDIX: AXIO System Architecture & Implementation Guide

## A. SYSTEM ARCHITECTURE DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                          │
├─────────────────────────────────────────────────────────────┤
│  Landing Page    │  Auth Page   │  Tutorial Page │ Dashboard  │
│  (Interactive    │  (Login/     │  (LaTeX        │ (Proof     │
│   Logo with      │   Signup)    │   Practice)    │  Attempts)  │
│   Gaze Track)    │              │                │            │
└──────────────────┬──────────────┬────────────────┬────────────┘
                   │ AJAX/JSON    │                │
                   ↓              ↓                ↓
┌─────────────────────────────────────────────────────────────┐
│                      API LAYER (PHP)                         │
├─────────────────────────────────────────────────────────────┤
│  auth.php     │  theorems.php │  tutor.php  │ user-prefs.php │
│  (Login/      │  (Theorem     │  (AI        │  (Settings)     │
│   Signup)     │   Browser)    │   Tutoring) │                 │
└──────────────────┬───────────────┬──────────────┬─────────────┘
                   │               │              │
                   ↓               ↓              ↓
┌─────────────────────────────────────────────────────────────┐
│                    SERVICE LAYER (PHP)                       │
├─────────────────────────────────────────────────────────────┤
│  User Model   │  DeepSeek      │  Proof        │  Lean       │
│  (Auth Logic) │  Service (AI)  │  Tutor        │  Service    │
└────────┬───────────────┬──────────────┬──────────┬────────────┘
         │               │              │          │
         ↓               ↓              ↓          ↓
┌─────────────────────────────────────────────────────────────┐
│                   DATABASE LAYER (MySQL)                     │
├─────────────────────────────────────────────────────────────┤
│  users  │ submissions │ scores │ theorems │ proof_attempts   │
└─────────────────────────────────────────────────────────────┘
```

---

## B. USER AUTHENTICATION FLOW

### B.1 Sign Up Process

```
User fills form          ┌─────────────────┐
   ↓                     │  Frontend Page  │
Clicks "Sign Up"    →    │  (auth.html)    │
   ↓                     └────────┬────────┘
Validates input                   │
   ├─ Username (3-20 chars)       │
   ├─ Password (min 6 chars)      │
   └─ Full Name required          │
   ↓                              │
Sends to backend   ←──────────────┘
   │
   ↓
┌──────────────────────────────┐
│   backend/api/auth.php       │
│                              │
│ 1. Check username unique     │
│ 2. Hash password (bcrypt)    │
│ 3. INSERT into users table   │
│ 4. Return user_id            │
└────────────┬─────────────────┘
             │
             ↓
      ┌──────────────────────┐
      │  users table         │
      │  (INSERT new row)    │
      │                      │
      │ id | username | pass │
      │ 8  | AlexaQ_  | hash │
      └──────────────────────┘
             │
             ↓
Frontend receives user_id
   │
   ↓
Save to sessionStorage
   │
   ↓
Redirect to latex-skill-check.html
   │
   ↓
LaTeX Skill Selection
   ↓
Redirect to tutorial.html
   ↓
USER ENTERS TUTORIAL
```

### B.2 Login Process

```
User fills form          
   ↓                     
Clicks "Log In"    →    
   ↓                     
Validates input          
   ├─ Username required  
   └─ Password required  
   ↓                              
Sends to backend    
   │
   ↓
┌──────────────────────────────────────┐
│   backend/api/auth.php (login)       │
│                                      │
│ 1. SELECT from users WHERE username  │
│ 2. Verify password with password_verify()
│ 3. SET $_SESSION['user_id']          │
│ 4. Return success                    │
└────────────┬───────────────────────┘
             │
             ↓
      ┌──────────────────────┐
      │  users table         │
      │  (SELECT & VERIFY)   │
      └──────────────────────┘
             │
             ↓
Frontend receives success
   │
   ↓
Save to sessionStorage
   │
   ↓
Redirect to landing.html
   ↓
Check session status
   ↓
Redirect to appropriate page
   ├─ New user? → latex-skill-check.html
   ├─ Tutorial incomplete? → tutorial.html
   └─ All done? → dashboard.html
```

---

## C. TUTORIAL ENFORCEMENT SYSTEM

### C.1 New User Journey

```
NEW USER SIGNUP
      │
      ↓
   Create Account
   (Users table: status = 'new')
      │
      ↓
   Auto Login
   └─ sessionStorage.setItem('new_user', 'true')
      │
      ↓
┌─────────────────────────────┐
│  latex-skill-check.html     │
│                             │
│ Question: Know LaTeX?       │
│ ○ Yes                       │
│ ○ No                        │
│                             │
│ Marks: latex_skill_done=true│
└────────────┬────────────────┘
             │
             ↓
┌─────────────────────────────┐
│  tutorial.html              │
│                             │
│ isNewUser = true            │
│ tutorialStarted = false     │
│                             │
│ [Start Tutorial] [Skip ✗]   │
│ (Skip button disabled       │
│  for new users)             │
│                             │
│ Forced Flow:                │
│ Welcome Page                │
│   ↓                         │
│ LaTeX Card 1-5 (practice)   │
│   ↓                         │
│ Tutorial Steps 1-4          │
│   ↓                         │
│ [Finish] → completeTutorial()
│                             │
│ Sets:                       │
│ - tutorial_completed=true   │
│ - new_user=null (removed)   │
└────────────┬────────────────┘
             │
             ↓
┌─────────────────────────────┐
│  dashboard.html             │
│                             │
│ User can now:               │
│ - View theorems             │
│ - Submit proofs             │
│ - Get AI feedback           │
│ - Track scores              │
└─────────────────────────────┘
```

### C.2 JavaScript Session Flags

```javascript
// Session flags tracked in sessionStorage

// After signup:
sessionStorage.setItem('user_id', data.user_id);
sessionStorage.setItem('username', data.username);
sessionStorage.setItem('new_user', 'true'); // ← Marks as new

// After skill check:
sessionStorage.setItem('latex_skill_done', 'true');

// After tutorial:
sessionStorage.setItem('tutorial_completed', 'true');
sessionStorage.removeItem('new_user'); // ← Clears new flag

// Redirects based on flags:
if (new_user === 'true' || !latex_skill_done) {
    → Go to latex-skill-check.html
} else if (!tutorial_completed) {
    → Go to tutorial.html
} else {
    → Go to dashboard.html
}
```

---

## D. DATABASE SCHEMA DETAILS

### D.1 Users Table

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Stores bcrypt hash
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data:
-- id=1: username='vhenong', password='$2y$10$...[bcrypt hash]...'
-- id=2: username='ravenmondia7', password='$2y$10$...[bcrypt hash]...'
-- id=3: username='albertbenedictnievera', password='$2y$10$...[bcrypt hash]...'
-- id=8: username='AlexaQ_', password='$2y$10$...[bcrypt hash]...'
```

### D.2 User Preferences Table

```sql
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    latex_skill ENUM('yes', 'no') DEFAULT 'yes',
    tutorial_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### D.3 Proof Attempts Table

```sql
CREATE TABLE proof_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theorem_id INT NOT NULL,
    natural_language_input TEXT NOT NULL,  -- Student's proof in natural language
    generated_lean_code TEXT,               -- AI-converted Lean code
    verification_status ENUM('pending', 'success', 'failed', 'error') DEFAULT 'pending',
    ai_feedback TEXT,                      -- AI feedback on proof
    score INT,
    time_spent_seconds INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (theorem_id) REFERENCES theorems(id)
);
```

---

## E. SECURITY IMPLEMENTATION

### E.1 Password Security

```php
// During signup - hash password
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
// Output example: $2y$10$N9qo8uLOickgx2ZMRZoMyeJHzGdHFcKzRXZVZlLpnvPnQh5mFfC9y

// During login - verify password
if (password_verify($inputPassword, $storedHash)) {
    // Password is correct
} else {
    // Password is wrong
}
```

### E.2 Session Management

```php
// After successful login
session_start();
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

// Check if user is authenticated
if (isset($_SESSION['user_id'])) {
    // User is logged in
} else {
    // User must login first
    header('Location: auth.html?mode=login');
}

// Logout
session_destroy();
```

### E.3 Input Validation

```php
// Username validation
function validateUsername($username) {
    $regex = /^[a-zA-Z0-9._-]{3,20}$/;
    return regex.test($username);
}

// Password requirements
$password_min_length = 6;
$password_max_length = 255;

// All inputs are trimmed and type-checked
$username = trim((string)$_POST['username']);
```

---

## F. FRONTEND FEATURES

### F.1 Interactive AXIO Logo

**Technology**: SVG with JavaScript mouse tracking

```javascript
// Calculates angle from logo center to cursor position
// Moves pupils to "look at" cursor in real-time
// Updates 60 times per second based on mousemove events

const angle = Math.atan2(
    cursorPos.y - eyeCenter.y,
    cursorPos.x - eyeCenter.x
);

pupilNewX = eyeCenter.x + Math.cos(angle) * maxDistance;
pupilNewY = eyeCenter.y + Math.sin(angle) * maxDistance;
```

### F.2 Floating Particle Background

**Technology**: CSS animations with 20 math symbols

```
Symbols used: π, ∫, ∑, √, ∞, ∇, ±, ≈, ⊕, ∆, ∂, ∈, ∀, ∃, ∪, ∩, ⊂, ⊃, μ, λ

Animation properties:
- Duration: 15-21 seconds per cycle
- Delay: 0-5 seconds (staggered)
- Opacity: 0.18-0.25 (subtle but visible)
- Transforms: translate + rotate + opacity change
- Colors: Navy (#1e3a8a), Green (#059669), Teal (#0f766e)
- Responsive: Hidden on mobile (max-width: 500px)
```

### F.3 Tutorial System

**Features**:
- 5 LaTeX practice cards with real-time validation
- 4 tutorial steps explaining system features
- Progress dots showing completion status
- Next button only enabled when current card completed
- Enforced completion for new users (can't skip)

---

## G. API RESPONSE FORMATS

### G.1 Success Response

```json
{
    "success": true,
    "user_id": 8,
    "username": "AlexaQ_",
    "message": "Login successful"
}
```

### G.2 Error Response

```json
{
    "success": false,
    "message": "Username already taken",
    "error": "DUPLICATE_USERNAME"
}
```

### G.3 Session Check Response

```json
{
    "authenticated": true,
    "user_id": 8,
    "username": "AlexaQ_"
}
```

---

## H. MULTILINGUAL SUPPORT

The system accepts proof submissions in **any language**:

### Example 1: English Proof
```
Student input: "Let n be a natural number. By the Archimedean property, 
there exists a positive integer m such that n < m. Therefore..."
```

### Example 2: Tagalog Proof
```
Student input: "Hayaan n ay isang natural na numero. Ayon sa Archimedean property, 
may positive integer m na n < m. Kaya..."
```

### Example 3: Spanish Proof
```
Student input: "Sea n un número natural. Por la propiedad arquimediana, 
existe un entero positivo m tal que n < m. Por lo tanto..."
```

**Process**:
1. Student submits proof in any language
2. DeepSeek AI translates to English
3. Converts English proof to formal Lean 4 code
4. Verifies Lean code
5. Returns feedback in original language (if possible)

---

## I. DEPLOYMENT CONFIGURATION

### I.1 Render Deployment Settings

```yaml
# render.yaml for Render deployment

services:
  - type: web
    name: axio-app
    runtime: php
    buildCommand: "composer install"
    startCommand: "php -S 0.0.0.0:$PORT"
    envVars:
      - key: DATABASE_HOST
        value: database.host.server.com
      - key: DATABASE_NAME
        value: lean4_ai_app
      - key: DATABASE_USER
        value: root
```

### I.2 Environment Variables Required

```
DATABASE_HOST=localhost (or cloud MySQL host)
DATABASE_NAME=lean4_ai_app
DATABASE_USER=root
DATABASE_PASSWORD=***
DEEPSEEK_API_KEY=***
SESSION_LIFETIME=3600
```

---

## J. TESTING CHECKLIST

- [ ] User registration with duplicate username rejection
- [ ] Password hashing and verification
- [ ] Login with correct/incorrect credentials
- [ ] Session persistence across pages
- [ ] New user tutorial enforcement
- [ ] LaTeX card completion validation
- [ ] Tutorial completion and redirect
- [ ] Returning user skips to dashboard
- [ ] Interactive logo gaze tracking
- [ ] Database persistence (verify accounts survive page refresh)
- [ ] API error handling
- [ ] CORS headers working for frontend-backend communication

---

## CONCLUSION

This appendix provides complete documentation of the AXIO system's:
- **Architecture**: Three-tier design with clear separation of concerns
- **Security**: Industry-standard password hashing and session management
- **User Experience**: Interactive elements and enforced learning path
- **Data Persistence**: Permanent MySQL database storage
- **Scalability**: RESTful API design ready for future extensions
