# AXIO Platform Enhancement Roadmap

## Overview
Comprehensive improvements to make AXIO a professional, student-friendly math learning platform.

---

## Feature 1: LaTeX Tutorial System

### Purpose
Teach students LaTeX syntax with live previews and explanations.

### Components
- **tutorial.html** - Dedicated tutorial page
- **Math symbol categories** (Operators, Relations, Logic, Sets, Calculus, etc.)
- **Live preview** using KaTeX
- **Copy-to-clipboard** functionality
- **Examples with explanations**

### Data Structure
```javascript
{
  category: "Operators",
  symbols: [
    {
      name: "Plus",
      latex: "+",
      display: "+",
      explanation: "Addition operator"
    },
    {
      name: "Integral",
      latex: "\\int",
      display: "∫",
      explanation: "Integration symbol. Use \\int_a^b f(x)dx for definite integrals"
    }
  ]
}
```

---

## Feature 2: Math Symbol Library (Dashboard Panel)

### Purpose
Quick reference panel in the dashboard for LaTeX symbols.

### UI Components
- **Symbol search bar** (searchable by name/latex)
- **Category tabs** (Operators, Relations, Calculus, etc.)
- **Symbol cards** with:
  - LaTeX code
  - Live preview
  - Copy button
  - Usage example

---

## Feature 3: Improved UI/UX Design

### Color Scheme
**Light Mode:**
- Primary: #0f766e (Teal) - For action buttons
- Secondary: #1e3a8a (Deep Blue) - For headings
- Neutral: #f7f9fb (Light Gray) - Background
- Text: #111827 (Dark Gray)

**Dark Mode:**
- Primary: #14b8a6 (Bright Teal)
- Background: #0b1220 (Deep blue-black)
- Text: #e2e8f0 (Light gray)

### Typography
- **Headings**: Inter Bold, 24-32px
- **Body**: Inter Regular, 14-16px
- **Code/Math**: Fira Mono, 13-14px

### Spacing
- Cards: 20px padding
- Sections: 24px gap
- Elements: 12px internal spacing

---

## Feature 4: Login/Signup Improvements

### New Fields
- First Name (required)
- Last Name (required)
- Email (required)
- Username (auto-generated, editable)
- Password

### Database Update
```sql
ALTER TABLE users ADD COLUMN first_name VARCHAR(100);
ALTER TABLE users ADD COLUMN last_name VARCHAR(100);
```

### Display in Profile
- Avatar initials from first/last names
- Full name in header: "John Doe"
- Username in settings: "johndoe"

---

## Feature 5: Proof Scoring System

### Evaluation Criteria
1. **Logical Correctness (40%)** - Steps follow logically
2. **Mathematical Validity (30%)** - Statements are mathematically sound
3. **Clarity (20%)** - Explanations are clear
4. **Completeness (10%)** - All necessary steps included

### Score Output
```json
{
  "score": 85,
  "grade": "A",
  "breakdown": {
    "logical_correctness": 95,
    "mathematical_validity": 90,
    "clarity": 70,
    "completeness": 80
  },
  "feedback": "Strong proof with clear logical flow. Consider adding more justification for step 3.",
  "suggestions": ["Add theorem reference for step 3", "Explain why we can divide by (x-a)"]
}
```

---

## Feature 6: Dual Proof Modes

### Mode A: Step-by-Step (Guided)
- Multiple numbered input fields
- AI provides feedback after each step
- Progressive difficulty
- Best for learning

### Mode B: Full Proof (Continuous)
- Single large textarea
- User writes freely
- AI evaluates the complete proof
- Best for assessment

### Toggle Implementation
```javascript
// In dashboard.html
<button id="toggle-proof-mode">Switch to Full Proof Mode</button>

// Dynamic switching
if (mode === 'step-by-step') {
  showStepFields();
  enableStepByStepGuidance();
} else {
  showFullTextarea();
  disableStepGuidance();
}
```

---

## Feature 7: Dark Mode Enhancement

### Implementation
- Comprehensive dark theme CSS variables
- Toggle in settings
- Persistent storage
- Smooth transitions

### Pages to Update
- dashboard.html
- submissions.html
- scores.html
- settings.html
- tutorial.html (new)
- landing.html

---

## Implementation Order (Priority)

1. **Dark Mode Enhancement** (Day 1) - Quick win, improves all pages
2. **Login/Signup Improvements** (Day 1-2) - Backend + frontend
3. **LaTeX Tutorial System** (Day 2-3) - New page with rich content
4. **Math Symbol Library** (Day 3) - Dashboard panel
5. **Dual Proof Modes** (Day 3-4) - Major dashboard change
6. **Proof Scoring System** (Day 4-5) - Backend API + Frontend display
7. **UI/UX Polish** (Ongoing) - Refine based on testing

---

## Files to Create/Modify

### New Files
- `frontend/pages/tutorial.html`
- `frontend/js/tutorial.js`
- `frontend/css/tutorial.css`
- `backend/api/proof-scoring.php`
- `frontend/components/symbol-library.js`

### Modified Files
- `frontend/pages/auth.html`
- `frontend/pages/dashboard.html`
- `frontend/pages/settings.html`
- `frontend/js/axio-app.js`
- `frontend/css/axio-app.css`
- `backend/config/database.php` (schema updates)
- `backend/api/auth.php`

---

## Success Metrics
- ✅ All 7 features fully functional
- ✅ Dark mode works on all pages
- ✅ Student signup takes <2 minutes
- ✅ Tutorial is clear and helpful
- ✅ Symbol library loads in <100ms
- ✅ Proof scoring provides actionable feedback
- ✅ Proof modes toggle seamlessly
- ✅ No console errors across features
