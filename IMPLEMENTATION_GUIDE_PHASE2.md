# AXIO Platform Enhancement - Implementation Guide

## ✅ COMPLETED (Phase 1)

### 1. LaTeX Tutorial System
**Files Created:**
- `frontend/pages/tutorial.html` - Full tutorial page with categories
- `frontend/js/tutorial.js` - Data and logic for symbol rendering

**Features:**
- 6 categories: Basic, Relations, Logic, Calculus, Greek, Advanced
- Live KaTeX preview for each symbol
- Search functionality
- Copy-to-clipboard for LaTeX codes
- Full dark mode support
- Responsive grid layout

**How to Use:**
```html
<!-- Add Tutorial link to any navigation -->
<a href="tutorial.html">📚 Tutorial</a>
```

---

### 2. Math Symbol Library Component
**File Created:**
- `frontend/js/symbol-library.js` - Reusable symbol library component

**Features:**
- Quick-access symbol panel
- 4 categories: Common, Calculus, Logic, Greek
- Direct insertion into active editors
- Collapsible UI
- Dark mode support

**How to Integrate into Dashboard:**
```html
<!-- Add to dashboard.html near the verification panel -->
<div id="symbol-library" class="symbol-library-container"></div>

<!-- Add CSS to dashboard -->
<style>
/* Include symbolLibraryCSS from symbol-library.js */
</style>

<!-- Initialize in JavaScript -->
<script src="frontend/js/symbol-library.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    new SymbolLibrary('symbol-library');
  });
</script>
```

---

## 📋 REMAINING (Phase 2-3)

### 3. Login/Signup Improvements

**Database Changes Required:**
```sql
ALTER TABLE users 
ADD COLUMN first_name VARCHAR(100) NOT NULL DEFAULT 'User',
ADD COLUMN last_name VARCHAR(100) NOT NULL DEFAULT 'User';

-- Optional: Update existing users
UPDATE users SET first_name = SUBSTR(username, 1, INSTR(username, ' ')-1) 
WHERE first_name = 'User';
```

**Frontend Changes:**
```html
<!-- In auth.html, update signup form -->
<div class="form-group">
  <label for="first-name">First Name *</label>
  <input type="text" id="first-name" name="first_name" required>
</div>

<div class="form-group">
  <label for="last-name">Last Name *</label>
  <input type="text" id="last-name" name="last_name" required>
</div>

<div class="form-group">
  <label for="email">Email *</label>
  <input type="email" id="email" name="email" required>
</div>

<div class="form-group">
  <label for="username">Username *</label>
  <input type="text" id="username" name="username" required>
</div>

<div class="form-group">
  <label for="password">Password *</label>
  <input type="password" id="password" name="password" required>
</div>
```

**Backend Changes (backend/api/auth.php):**
```php
case 'signup':
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (!$firstName || !$lastName || !$email || !$username || !$password) {
        echo json_encode(['success' => false, 'error' => 'All fields required']);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $db->getConnection()->prepare(
        "INSERT INTO users (first_name, last_name, email, username, password) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $result = $stmt->execute([$firstName, $lastName, $email, $username, $hashedPassword]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Account created']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Signup failed']);
    }
    break;
```

---

### 4. Proof Scoring Backend API

**Create: `backend/api/proof-scoring.php`**

```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ProofScoringService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'evaluate_proof':
            // Expected POST data:
            // - theorem: "The theorem statement"
            // - proof: "The user's proof steps"
            // - mode: "step-by-step" or "full"
            
            $data = json_decode(file_get_contents('php://input'), true);
            $scorer = new ProofScoringService();
            $result = $scorer->evaluateProof($data['theorem'], $data['proof'], $data['mode']);
            
            echo json_encode(['success' => true, 'score' => $result]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
```

**Create: `backend/services/ProofScoringService.php`**

```php
<?php

class ProofScoringService {
    private $deepseekService;
    private $logger;

    public function __construct() {
        require_once __DIR__ . '/DeepSeekService.php';
        require_once __DIR__ . '/Logger.php';
        
        $this->deepseekService = new DeepSeekService();
        $this->logger = new Logger(__DIR__ . '/../logs');
    }

    public function evaluateProof($theorem, $proof, $mode = 'full') {
        $prompt = $this->buildScoringPrompt($theorem, $proof, $mode);
        
        try {
            $response = $this->deepseekService->callAPI([
                'system' => "You are an expert mathematics tutor evaluating student proofs. 
                Provide constructive feedback and a fair score from 0-100.",
                'user' => $prompt
            ]);

            return $this->parseScore($response);
        } catch (Exception $e) {
            $this->logger->error('Scoring error: ' . $e->getMessage());
            return $this->getDefaultScore();
        }
    }

    private function buildScoringPrompt($theorem, $proof, $mode) {
        return "
Evaluate this mathematical proof:

THEOREM: $theorem

PROOF:
$proof

MODE: $mode

Provide your evaluation in JSON format:
{
    \"score\": <0-100>,
    \"grade\": \"A+ | A | B | C | D | F\",
    \"feedback\": \"Overall assessment\",
    \"breakdown\": {
        \"logical_correctness\": <0-100>,
        \"mathematical_validity\": <0-100>,
        \"clarity\": <0-100>,
        \"completeness\": <0-100>
    },
    \"suggestions\": [\"suggestion 1\", \"suggestion 2\", ...]
}
        ";
    }

    private function parseScore($response) {
        try {
            // Extract JSON from response
            $json = json_decode($response, true);
            return $json ?? $this->getDefaultScore();
        } catch (Exception $e) {
            return $this->getDefaultScore();
        }
    }

    private function getDefaultScore() {
        return [
            'score' => 0,
            'grade' => 'Pending',
            'feedback' => 'Unable to evaluate at this time',
            'breakdown' => [
                'logical_correctness' => 0,
                'mathematical_validity' => 0,
                'clarity' => 0,
                'completeness' => 0
            ],
            'suggestions' => []
        ];
    }
}
?>
```

---

### 5. Dual Proof Modes (Step-by-Step vs Full)

**Update dashboard.html:**

```html
<!-- Add mode toggle in topbar -->
<div class="proof-mode-toggle">
  <button id="mode-switch" class="btn">Switch to Full Proof</button>
  <span id="current-mode" class="mode-indicator">Step-by-Step</span>
</div>

<!-- Step-by-Step Mode Container -->
<div id="step-mode" class="proof-mode-container active">
  <h2>Step-by-Step Proof Editor</h2>
  <div id="steps-wrap"></div>
  <button id="btn-add-step" class="btn success">+ Add Step</button>
  <button id="btn-remove-step" class="btn">− Remove Step</button>
</div>

<!-- Full Proof Mode Container -->
<div id="full-mode" class="proof-mode-container">
  <h2>Full Proof Writer</h2>
  <textarea id="full-proof-input" rows="20" placeholder="Write your complete proof here..."></textarea>
  <button id="btn-submit-full-proof" class="btn success">Submit Complete Proof</button>
</div>
```

**JavaScript to toggle modes:**

```javascript
let currentMode = 'step-by-step'; // Default

const modeSwitch = document.getElementById('mode-switch');
const stepMode = document.getElementById('step-mode');
const fullMode = document.getElementById('full-mode');
const modeIndicator = document.getElementById('current-mode');

modeSwitch.addEventListener('click', () => {
    currentMode = currentMode === 'step-by-step' ? 'full' : 'step-by-step';
    
    if (currentMode === 'full') {
        stepMode.classList.remove('active');
        fullMode.classList.add('active');
        modeSwitch.textContent = 'Switch to Step-by-Step';
        modeIndicator.textContent = 'Full Proof';
    } else {
        fullMode.classList.remove('active');
        stepMode.classList.add('active');
        modeSwitch.textContent = 'Switch to Full Proof';
        modeIndicator.textContent = 'Step-by-Step';
    }
});
```

---

### 6. Enhanced Dark Mode Implementation

**Add to axio-app.css:**

```css
/* Dark Mode Support */
:root {
  --bg-light: #f7f9fb;
  --bg-dark: #0b1220;
  --text-light: #111827;
  --text-dark: #e2e8f0;
  --card-light: #ffffff;
  --card-dark: #0b1220;
}

.theme-dark {
  --current-bg: var(--bg-dark);
  --current-text: var(--text-dark);
  --current-card: var(--card-dark);
}

html {
  background: var(--bg-light);
  color: var(--text-light);
}

html.theme-dark {
  background: var(--bg-dark);
  color: var(--text-dark);
}

/* Apply to all major components */
.card, .settings-section, .symbol-library-panel {
  background: var(--card-light);
  color: var(--text-light);
  border-color: rgba(30, 58, 138, 0.2);
}

.theme-dark .card,
.theme-dark .settings-section,
.theme-dark .symbol-library-panel {
  background: var(--card-dark) !important;
  color: var(--text-dark) !important;
  border-color: rgba(148, 163, 184, 0.35) !important;
}
```

---

## Integration Checklist

- [ ] Database schema updated with first_name, last_name
- [ ] auth.php updated for signup with names
- [ ] Dashboard includes tutorial link in navigation
- [ ] Dashboard includes symbol library panel
- [ ] Dashboard has proof mode toggle
- [ ] Proof scoring API created and tested
- [ ] Dark mode CSS variables applied globally
- [ ] All pages test dark mode toggle
- [ ] Forms auto-populate with user data
- [ ] Avatar shows user initials (e.g., "JD" for John Doe)

---

## Testing Checklist

1. **LaTeX Tutorial:**
   - [ ] All 6 categories render correctly
   - [ ] Copy buttons work
   - [ ] KaTeX preview renders correctly
   - [ ] Search filters symbols
   - [ ] Dark mode applies

2. **Symbol Library:**
   - [ ] Panel appears on dashboard
   - [ ] Tabs switch categories
   - [ ] Copy inserts into active textarea
   - [ ] Collapsible UI works

3. **Auth Changes:**
   - [ ] Signup form accepts first/last names
   - [ ] Data saved to database
   - [ ] Profile displays full name
   - [ ] Avatar shows initials

4. **Proof Modes:**
   - [ ] Toggle switches modes
   - [ ] Step mode shows steps
   - [ ] Full mode shows textarea
   - [ ] Mode preference persists

5. **Scoring:**
   - [ ] API receives proof data
   - [ ] DeepSeek returns valid JSON
   - [ ] Score displays in UI
   - [ ] Feedback shows suggestions

6. **Dark Mode:**
   - [ ] Toggle works
   - [ ] All pages respect theme
   - [ ] Colors comfortable
   - [ ] Contrast good

---

## Deployment Steps

1. Run database migrations for users table
2. Test auth.php signup with names
3. Commit and push all new files
4. Deploy to Render (auto-deploy)
5. Test all features on live site
6. Monitor for errors in Render logs
