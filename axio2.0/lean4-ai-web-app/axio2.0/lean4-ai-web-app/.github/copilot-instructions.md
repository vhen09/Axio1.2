# Lean4 AI Web App - AI Agent Instructions

## Architecture Overview

This is a **Real Analysis Theorem Proving System** combining PHP backend, Lean4 proof verification, and DeepSeek AI tutoring. The system enables students to prove Real Analysis theorems using natural language (any language), which gets converted to formal Lean4 code.

### Three-Tier Architecture
1. **Frontend**: Vanilla JS with modular API clients (`api-client.js`, `tutor-client.js`, `theorem-browser.js`)
2. **PHP Backend**: RESTful APIs in `backend/api/` calling services in `backend/services/`
3. **External Services**: DeepSeek AI API for natural language processing, Lean4 for formal verification

## Critical Service Boundaries

### Backend Services Pattern
Each service is a standalone class with specific responsibility:
- `DeepSeekService.php` - All AI API calls (chat completions)
- `NaturalLanguageToLeanConverter.php` - Converts informal proofs → Lean code
- `ProofTutorService.php` - Step-by-step tutoring, verification, concept explanations
- `LeanService.php` - Lean verification calls (currently mock, designed for future integration)

**Convention**: Services are initialized in API endpoints with dependency injection pattern. Database connection passed to constructors when needed.

### API Endpoint Structure
All API files in `backend/api/` follow this pattern:
```php
// 1. CORS headers
header('Access-Control-Allow-Origin: *');

// 2. Action-based routing via switch
$action = $_GET['action'] ?? $_POST['action'] ?? '';
switch ($action) {
    case 'get_theorems': getTheorems($db); break;
    // ...
}

// 3. Functions handle each action, return JSON
function getTheorems($db) {
    echo json_encode(['success' => true, 'data' => $results]);
}
```

## Database Schema Patterns

### Core Tables
- `theorems` - 50+ Real Analysis theorems with `lean_code`, `natural_language_description`, JSON fields (`tags`, `prerequisites`, `related_theorems`)
- `theorem_categories` - Hierarchical organization (Sequences, Series, Continuity, etc.)
- `users`, `submissions`, `scores` - User tracking

**JSON Field Pattern**: Store arrays in JSON columns (`tags`, `prerequisites`). Access with `JSON_EXTRACT()` in queries or decode in PHP.

**Important**: `difficulty_level` is ENUM: `'beginner'|'intermediate'|'advanced'|'expert'`

## Development Workflows

### Starting the System
```bash
# Windows
START_SYSTEM.bat

# Linux/Mac
./start_system.sh
```

Scripts auto-create database, import schema + seed data, start PHP server on port 8080.

### Testing Workflow
1. Navigate to `http://localhost:8080/frontend/pages/theorems.html`
2. Browse theorems by category (seed data has 40+ theorems)
3. Test proof flow: Select theorem → Submit natural language proof → Get Lean conversion → Verify

### Database Reset
```sql
-- Run from project root
mysql -u root -p lean4_ai_db < database/schema.sql
mysql -u root -p lean4_ai_db < database/seed_theorems.sql
```

## Project-Specific Conventions

### Multilingual Support
The system accepts proof inputs in **ANY language** (English, Tagalog, Spanish, Chinese). `NaturalLanguageToLeanConverter` handles this via DeepSeek AI:
```php
// Tagalog input example works identically to English
$converter->convertToLean($theorem, "Gamitin ang Archimedean property...");
```

### API Client Pattern
Frontend uses promise-based clients with standardized error handling:
```javascript
// All API calls return promises
const response = await apiClient.post('theorems.php?action=get_theorems', {
    category_id: 1
});
// Response structure: {success: bool, data: any, error?: string}
```

### Lean Integration Points
- `lakefile.lean` - Lake build configuration
- `lean/REANATheorems.lean` - Complete theorem library (50+ theorems)
- `lean/*.lean` - Modular files for parsing, verification, scoring

**Current State**: Lean verification is mocked in `LeanService.php`. Real Lean integration requires standalone Lean server (not implemented).

### DeepSeek API Configuration
Config in `backend/config/deepseek.php`:
```php
return [
    'deepseek_api_url' => 'https://api.deepseek.com/v1/chat/completions',
    'deepseek_api_key' => 'YOUR_KEY_HERE',
    'model' => 'deepseek-chat',
    'max_tokens' => 2000,
    'temperature' => 0.7
];
```

**Security Note**: API key must be set in config file. No environment variable fallback implemented.

## Common Patterns

### Error Handling
Backend consistently uses try-catch with Logger service:
```php
try {
    // operation
} catch (Exception $e) {
    $logger->error('Context: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

### Frontend State Management
No framework - uses vanilla JS with DOM manipulation:
```javascript
// Pattern: Fetch data → Clear container → Render HTML
const theorems = await fetchTheorems();
container.innerHTML = '';
theorems.forEach(t => container.appendChild(createTheoremCard(t)));
```

### AI Prompt Engineering
Services build structured prompts with system + user messages:
```php
$systemPrompt = "You are a Lean 4 expert...";
$userMessage = "Theorem: {$theorem}\nProof: {$proof}\nConvert to Lean.";
$this->callDeepSeekAPI($systemPrompt, $userMessage);
```

## Key Files Reference

- [HOW_TO_RUN.md](../HOW_TO_RUN.md) - Complete setup instructions, prerequisites
- [AI_TUTOR_README.md](../AI_TUTOR_README.md) - Tutoring system architecture, all available methods
- [REANA_COMPLETE_SYSTEM.md](../REANA_COMPLETE_SYSTEM.md) - Full theorem library documentation
- [database/schema.sql](../database/schema.sql) - Database structure
- [database/seed_theorems.sql](../database/seed_theorems.sql) - Example data with all 50+ theorems

## What NOT to Do

- Don't try to run Lean locally - `LeanService` is currently mocked
- Don't create environment variables for config - use config files directly
- Don't use TypeScript/frameworks - system is vanilla PHP + JS
- Don't modify `lakefile.lean` without understanding Lake build system
- Don't hard-code database credentials outside `config/database.php`
