# 📱 Axio Revised UI/UX Flow - Implementation Guide

## Overview

The Axio platform now features a complete, streamlined UI/UX flow designed for new users. This document explains the complete flow, page connections, and technical implementation.

---

## 🔄 Complete User Journey

### **1. Entry Point: Landing Page**
**File:** `frontend/pages/landing.html`

- **What Users See:**
  - Clean, minimalist design
  - Axio logo (circle: ◯)
  - Welcome message
  - Two primary buttons: "Log In" and "Sign Up"

- **Flow:**
  - If user is **not logged in** → Display landing page
  - If user is **logged in** → Skip to LaTeX Skill Check (if not completed) or Dashboard

- **User Actions:**
  - Click "Log In" → Go to `auth.html?mode=login`
  - Click "Sign Up" → Go to `auth.html?mode=signup`

---

### **2. Authentication Page**
**File:** `frontend/pages/auth.html`

- **What Users See:**
  - Unified auth page that switches between Login and Sign Up modes
  - Elegant form with minimal styling

#### **Sign Up Mode**
- **Fields:**
  - Full Name
  - Email Address
  - Password (min. 6 characters)
  - Confirm Password

- **Actions:**
  - Click "Create Account" → Register via `backend/api/auth.php`
  - Success → Redirect to login mode with success message
  - Click "Already have an account? Log In" → Switch to login mode

#### **Login Mode**
- **Fields:**
  - Email Address (converted to username)
  - Password

- **Actions:**
  - Click "Log In" → Login via `backend/api/auth.php`
  - Success → Store session, redirect to `latex-skill-check.html`
  - Click "Don't have an account? Sign Up" → Switch to signup mode

---

### **3. LaTeX Skill Check**
**File:** `frontend/pages/latex-skill-check.html`

- **What Users See:**
  - Single question: "Are you familiar with writing mathematical expressions using LaTeX?"
  - Two selectable cards with descriptions

#### **Option 1: Yes, I know LaTeX**
- Result: User interface will use LaTeX-based proof editor
- Example: `\forall n \in \mathbb{N}`

#### **Option 2: No, I'm not familiar with LaTeX**
- Result: User interface will use natural language proof editor
- Example: "Let n be a natural number..."

- **Actions:**
  - Select an option (enables Continue button)
  - Click "Continue" → Save preference to `backend/api/user-preferences.php`
  - Store in localStorage: `latex_skill_` + user_id
  - Redirect to `tutorial.html`

- **Backend Storage:**
  - Saves to `$_SESSION['latex_skill']`
  - Client-side localStorage for quick access

---

### **4. Tutorial Walkthrough**
**File:** `frontend/pages/tutorial.html`

- **What Users See:**
  - 4-step interactive tutorial with smooth transitions
  - Step counter showing progress
  - Navigation buttons (Previous, Skip, Next/Finish)

#### **Step 1: Ask the Axio AI**
- Overview of AI tutor capabilities
- Example questions
- Features available

#### **Step 2: Practice Proof Problems**
- Explanation of problem types
- Progressive difficulty (Beginner → Advanced)
- Workflow overview

#### **Step 3: Receive Feedback**
- Types of feedback provided
- How feedback helps learning
- Focus areas

#### **Step 4: Track Your Progress**
- Dashboard analytics
- Achievement tracking
- Learning metrics

- **Actions:**
  - Click "Next" → Move to next step
  - Click "Previous" → Go back (disabled on Step 1)
  - Click "Skip" → Jump directly to dashboard (with confirmation)
  - Click "Finish" (on Step 4) → Complete tutorial and redirect to dashboard

- **Backend Storage:**
  - Marks tutorial as completed in `user-preferences.php`
  - Stores in localStorage: `tutorial_completed_` + user_id

---

### **5. Dashboard**
**File:** `frontend/pages/dashboard.html`

- **What Users See:**
  - Main workspace interface
  - Proof problems to solve
  - AI tutor interface
  - Progress tracking

- **First-Time Entry:**
  - Complete tutorial has been finished
  - LaTeX preference is set
  - User is ready to start solving problems

---

## 📊 Page Navigation Map

```
Landing Page (landing.html)
├─ "Log In" button → Auth Page (mode=login)
│  ├─ Successful Login → LaTeX Skill Check
│  │  └─ Select preference → Tutorial
│  │     └─ Complete/Skip → Dashboard
│  └─ "Sign Up" link → Auth Page (mode=signup)
│     └─ Create Account → Success message → Auto-switch to login
│
└─ "Sign Up" button → Auth Page (mode=signup)
   ├─ Create Account → Success message → Auto-switch to login
   └─ "Log In" link → Auth Page (mode=login)
```

---

## 🔌 API Endpoints Used

### **Authentication (`backend/api/auth.php`)**

**Check Session (GET):**
```
GET /backend/api/auth.php?action=check_session
Response: { authenticated: boolean, user_id: number, username: string }
```

**Register (POST):**
```
POST /backend/api/auth.php
Body: { action: "register", username: string, password: string }
```

**Login (POST):**
```
POST /backend/api/auth.php
Body: { action: "login", username: string, password: string }
Response: { success: boolean, user_id: number, username: string }
```

### **User Preferences (`backend/api/user-preferences.php`)**

**Set LaTeX Skill (POST):**
```
POST /backend/api/user-preferences.php
Body: { action: "set_latex_skill", latex_skill: "yes" | "no" }
```

**Set Tutorial Completed (POST):**
```
POST /backend/api/user-preferences.php
Body: { action: "set_tutorial_completed", completed: boolean }
```

---

## 💾 Client-Side Storage

### **localStorage Keys:**

| Key | Purpose | Example |
|-----|---------|---------|
| `latex_skill_checked_{user_id}` | Marks if skill check completed | "true" |
| `latex_skill_{user_id}` | Stores LaTeX preference | "yes" or "no" |
| `tutorial_completed_{user_id}` | Marks if tutorial finished | "true" |

---

## 🎨 Design System

### **Color Palette**
- **Primary Blue:** #1e3a8a (Headers, titles)
- **Secondary Green:** #059669 (Buttons, accents)
- **Accent Yellow:** #fbbf24 (Highlights)
- **Background:** #f3f4f6 (Page backgrounds)
- **Text:** #0f172a (Primary text)
- **Muted:** #475569 (Secondary text)

### **Typography**
- **Font Family:** System fonts (-apple-system, BlinkMacSystemFont, 'Segoe UI', etc.)
- **Headers:** 700-800 font-weight
- **Body:** 400 font-weight, 1.5-1.7 line-height
- **Academic focus:** Clear, legible typography

### **Components**
- **Border Radius:** 8px-20px (rounded but professional)
- **Shadows:** Subtle, not dominant
- **Spacing:** Generous padding/margins (20px minimum)
- **Responsive:** Mobile-first design (tested at 500px breakpoint)

---

## 🔧 Configuration & Setup

### **Database Setup**
The system requires a MySQL database. Run the seed scripts:
```bash
mysql -u root -p lean4_ai_app < database/schema.sql
mysql -u root -p lean4_ai_app < database/seed_theorems.sql
```

### **Web Server**
Run the PHP development server:
```bash
cd c:\Project\v11\axio2.0\lean4-ai-web-app
php -S localhost:8080
```

### **Update Main Index**
The main `index.html` now intelligently routes users:
- Logged out → Landing page
- Logged in, no skill check → LaTeX check page
- Logged in, no tutorial → Tutorial page
- Ready → Dashboard

---

## ✨ Key Features of New Flow

### **For New Users:**
1. ✅ Simple, welcoming landing page
2. ✅ Unified auth page (login/signup in one place)
3. ✅ Personalized LaTeX preference setting
4. ✅ Interactive 4-step tutorial
5. ✅ Clear progression to dashboard

### **For Returning Users:**
1. ✅ Direct login (skip non-essential steps)
2. ✅ Remember preferences (localStorage)
3. ✅ Fast access to dashboard

### **User Experience:**
1. ✅ Minimalist, non-cluttered design
2. ✅ Smooth page transitions
3. ✅ Clear call-to-action buttons
4. ✅ Responsive on mobile and desktop
5. ✅ Academic, professional aesthetic

---

## 🚀 How to Test

### **Test New User Flow:**
1. Open http://localhost:8080 (or your server URL)
2. Should see landing page
3. Click "Sign Up"
4. Fill in: Full Name, Email, Password, Confirm Password
5. Click "Create Account"
6. Should succeed and switch to login mode
7. Login with credentials
8. Select LaTeX preference
9. Go through 4-step tutorial
10. Land on dashboard

### **Test Returning User Flow:**
1. Open http://localhost:8080
2. Should skip directly to dashboard (or prompt based on session)
3. All preferences should be remembered

### **Test LaTeX Preferences:**
Check localStorage in browser DevTools (F12 → Application → Storage → Local Storage)
- Should see `latex_skill_checked_{user_id}`
- Should see `latex_skill_{user_id}` with value "yes" or "no"

---

## 📝 File Structure

```
frontend/
├── pages/
│   ├── landing.html           ← Entry point
│   ├── auth.html              ← Login/Signup unified
│   ├── latex-skill-check.html ← Preference setting
│   ├── tutorial.html          ← 4-step tutorial
│   ├── dashboard.html         ← Main workspace
│   ├── ... (other pages)
├── css/
│   ├── axio-app.css
│   ├── axio-academic-theme.css
│   └── ... (other stylesheets)
└── js/
    └── ... (JavaScript files)

backend/
├── api/
│   ├── auth.php              ← Auth logic
│   ├── user-preferences.php  ← Preferences storage
│   └── ... (other endpoints)
└── config/
    └── database.php
```

---

## 🐛 Troubleshooting

### **Issue: Landing page shows instead of dashboard**
- **Cause:** Session not properly maintained
- **Fix:** Clear browser cache and localStorage, log in again

### **Issue: LaTeX preference not saving**
- **Cause:** user-preferences.php endpoint not working
- **Fix:** Ensure PHP can write to session data, check error logs

### **Issue: Redirect loop**
- **Cause:** Session check failing
- **Fix:** Verify database connection and auth.php is working

### **Issue: Mobile layout broken**
- **Cause:** CSS media queries not applied
- **Fix:** Clear cache, use DevTools to verify responsive breakpoints

---

## 📚 Next Steps & Future Enhancements

1. **Database Storage:** Move preferences from session to persistent database table
2. **Tutorial Replay:** Add button to replay tutorial from dashboard
3. **Tutorial Customization:** Let users personalize tutorial content
4. **Analytics:** Track tutorial completion rates and time spent
5. **Accessibility:** Add ARIA labels and keyboard navigation
6. **Dark Mode:** Implement dark theme option
7. **Internationalization:** Support multiple languages

---

## 🎯 Summary

The revised Axio UI/UX flow provides:
- ✅ Clear, logical user journey
- ✅ Personalized learning paths
- ✅ Professional, minimal aesthetic
- ✅ Smooth transitions between pages
- ✅ Responsive design for all devices
- ✅ Simple authentication system
- ✅ Interactive tutorial for new users

**All pages are now live and integrated with the backend!**
