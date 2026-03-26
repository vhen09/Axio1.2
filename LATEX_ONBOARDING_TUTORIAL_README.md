# LaTeX Onboarding Tutorial System Documentation

## Overview

The LaTeX Onboarding Tutorial is an interactive learning system that teaches new users the fundamentals of LaTeX for writing mathematical proofs. It automatically launches after signup and can be accessed during login if not yet completed.

## Features

### 1. **Five-Lesson Curriculum**
Each lesson builds progressively on previous knowledge:

- **Lesson 1: What is LaTeX?** 📖
  - Introduction to LaTeX and why mathematicians use it
  - Overview of what students will learn
  - Interactive sandbox introduction

- **Lesson 2: Math Mode & Basic Symbols** 🔢
  - Inline math ($...$) vs Display math ($$...$$)
  - Basic arithmetic operators and comparison symbols
  - Symbol names and meanings

- **Lesson 3: Advanced Structures** ➗
  - Fractions using \frac{}{} 
  - Square roots and nth roots with \sqrt[]{}
  - Subscripts and superscripts with _ and ^

- **Lesson 4: Summation & Special Symbols** Σ
  - Summation notation with \sum limits
  - Integrals with \int
  - Greek letters (α, β, γ, etc.)

- **Lesson 5: Theorems & Complete Proofs** 🎯
  - Writing complete theorem statements
  - Proof structure with \begin{proof}...\end{proof}
  - Putting everything together

### 2. **Interactive Features**

**Live LaTeX Preview**
- Right-side editor panel with real-time MathJax rendering
- Instant feedback as users type LaTeX code
- Starter code for each lesson to get users started

**Progress Tracking**
- Visual progress bar showing completion percentage
- Lesson counter (e.g., "Lesson 1 of 5")
- Lesson list with completed status indicators
- Click-to-jump navigation

**Axio Assistant Guide**
- Friendly AI assistant (Axio) explains each concept
- Symbol meanings and descriptions
- Best practices and tips
- Guided explanations with examples

**Responsive Design**
- Mobile-friendly layout   - Two-column layout on desktop (Assistant left, Editor right)
- Single-column on mobile
- Smooth animations and transitions

### 3. **Completion & Redirect**

After completing all 5 lessons:
1. Celebration screen displays with congratulations message
2. User clicks "Start Writing Proofs" button
3. Tutorial completion is saved to user_preferences
4. User is redirected to theorems.html to begin writing proofs

## User Flow

### New User Journey (Signup)
```
1. User fills signup form (name, email, username, password)
   ↓
2. Account created successfully
   ↓
3. 3-second countdown modal appears: "Let's learn LaTeX basics..."
   ↓
4. Redirected to /frontend/pages/learn-latex.html
   ↓
5. Complete interactive 5-lesson tutorial
   ↓
6. Click "Start Writing Proofs"
   ↓
7. Redirected to /frontend/pages/theorems.html
```

### Returning User Journey (Login)
```
1. User logs in with username/password
   ↓
2. Backend checks tutorial completion status
   ↓
3a. If NOT completed:
    - 3-second countdown modal
    - Redirect to learn-latex.html
   ↓
3b. If completed:
    - Redirect directly to theorems.html
```

## Technical Architecture

### Frontend Files

**`frontend/pages/learn-latex.html`** (1400+ lines)
- Complete interactive tutorial interface
- Lesson content and structure
- JavaScript lesson management
- MathJax rendering integration
- Progress tracking UI

**`frontend/pages/auth.html`** (Updated)
- Signup/Login form
- Integrates auth-handler.js
- Automatic redirect to tutorial for new users

**`frontend/js/auth-handler.js`** (New - 250+ lines)
- Signup handler with tutorial redirect
- Login handler with tutorial status check
- Tutorial completion tracker
- Session management
- Redirect messaging with countdown

### Backend Files

**`backend/api/auth.php`** (Updated)
- Added `is_new_user` and `redirect_to_tutorial` flags to signup response
- Automatic session creation for new users
- Enhanced error messages

**`backend/api/user-preferences.php`** (Updated)
- New action: `get_latex_tutorial_status` - Check if user completed tutorial
- New action: `complete_latex_tutorial` - Mark tutorial as complete
- Both use existing `UserPreferences` model

**`backend/models/UserPreferences.php`** (Existing)
- `markTutorialCompleted($user_id)` - Saves completion to database
- `getOnboardingStatus($user_id)` - Retrieves user status including tutorial flag

### Database Integration

**`user_preferences` table**
- `user_id` - Foreign key to users table
- `tutorial_completed` - Boolean flag (0 or 1)
- `tutorial_skipped` - Boolean flag (optional)
- `onboarding_complete` - Overall onboarding status
- `latex_skill_level` - User's assessed skill ('beginner', 'intermediate', 'advanced')

## API Endpoints

### Signup Endpoint
**POST** `/backend/api/auth.php?action=register`

Request:
```json
{
  "username": "john_doe",
  "password": "securepass123",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com"
}
```

Response (New User):
```json
{
  "success": true,
  "message": "User registered successfully.",
  "user_id": 25,
  "username": "john_doe",
  "is_new_user": true,
  "redirect_to_tutorial": true
}
```

### Check Tutorial Status
**GET** `/backend/api/user-preferences.php?action=get_latex_tutorial_status`

Response:
```json
{
  "success": true,
  "tutorial_completed": false,
  "status": {
    "user_id": 25,
    "tutorial_completed": 0,
    "onboarding_complete": 0
  }
}
```

### Complete Tutorial
**POST** `/backend/api/user-preferences.php?action=complete_latex_tutorial`

Request:
```json
{
  "latex_tutorial_completed": true
}
```

Response:
```json
{
  "success": true,
  "message": "LaTeX tutorial marked as completed",
  "tutorial_completed": true
}
```

## LaTeX Content Coverage

### Operators & Symbols
```latex
$a + b$        Addition
$a - b$        Subtraction
$a \times b$   Multiplication  
$a \cdot b$    Centered dot multiplication
$\frac{a}{b}$  Division/Fractions
$a = b$        Equals
$a \geq b$     Greater than or equal
$a \leq b$     Less than or equal
$a \neq b$     Not equal
$a \approx b$  Approximately equal
```

### Structures
```latex
$\frac{numerator}{denominator}$    Fractions
$\sqrt{x}$                          Square root
$\sqrt[3]{x}$                       Cube root
$x_1, x_2$                          Subscripts
$x^2, x^n$                          Superscripts
$\sum_{i=1}^{n} i$                  Summation
$\int_0^{1} f(x) dx$                Integrals
```

### Greek Letters
```latex
\alpha, \beta, \gamma, \delta, \pi, \sigma, \theta
\sum (uppercase Greek requires capital letter)
```

### Theorem Structure
```latex
\begin{theorem}
  Theorem statement here...
\end{theorem}

\begin{proof}
  Proof steps here...
\end{proof}
```

## Testing Checklist

### Signup to Tutorial Flow
- [ ] Create new account at `/frontend/pages/auth.html`
- [ ] Verify 3-second countdown appears with message
- [ ] Confirm redirect to learn-latex.html happens
- [ ] Check session contains user_id and username

### Tutorial Functionality
- [ ] All 5 lessons accessible via left sidebar
- [ ] Lesson content displays correctly
- [ ] LaTeX preview renders with MathJax
- [ ] Previous/Next buttons navigate correctly
- [ ] Progress bar updates as lessons complete
- [ ] Starter code loads for each lesson

### LaTeX Preview
- [ ] Inline math ($...$) renders correctly
- [ ] Display math ($$...$$) renders correctly
- [ ] Greek letters show properly
- [ ] Fractions and roots render
- [ ] Subscripts and superscripts work
- [ ] Error handling for invalid LaTeX

### Completion & Redirect
- [ ] Completion screen appears after lesson 5
- [ ] "Start Writing Proofs" button visible
- [ ] Tutorial completion saved to database
- [ ] Redirect to /frontend/pages/theorems.html works
- [ ] User can view theorem browser

### Login-After-Tutorial
- [ ] Login with new user account
- [ ] Verify user goes to theorems.html (not tutorial)
- [ ] Login with user who never completed tutorial
- [ ] Verify user redirect to learn-latex.html first

### Mobile Responsiveness
- [ ] Sidebar hides on mobile (appears below or in menu)
- [ ] Editor panel responsive on mobile
- [ ] Lesson content readable on mobile
- [ ] Navigation works on mobile

## Future Enhancements

### Possible Additions
1. **Quiz System** - Mini-quizzes after each lesson
2. **Difficulty Levels** - Advanced LaTeX topics optional
3. **Keyboard Shortcuts** - Quick insertion of common symbols
4. **Hints System** - Contextual hints for common mistakes
5. **Student Attempts** - Track which lessons took most time
6. **Certificate** - Optional completion certificate
7. **Skill Assessment** - Evaluate user's proficiency level
8. **Multilingual Support** - Tutorials in different languages

## Deployment Notes

### For Render Production
1. Ensure `DATABASE_URL` correctly parsed in PHP
2. Verify `user_preferences` table has `tutorial_completed` column
3. Check CORS headers allow frontend-backend communication
4. Test with actual Postgres database (migration may differ from local MySQL)

### Local Testing
Start the system with:
```bash
# Windows
START_SYSTEM.bat

# Linux/Mac
./start_system.sh
```

Then navigate to: `http://localhost:8080/frontend/pages/auth.html`

## Success Metrics

A successful implementation provides:
1. ✅ New users guided through LaTeX learning before writing proofs
2. ✅ Interactive practice with live previews
3. ✅ Clear progression through lessons (1-5)
4. ✅ Automatic redirect to writing theorems after completion
5. ✅ Seamless integration with existing auth system
6. ✅ Professional, educational experience

---

**Created**: 2024  
**Part of**: Axio Platform - Real Analysis Theorem Proving System
