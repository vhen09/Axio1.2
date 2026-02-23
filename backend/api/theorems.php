<?php

/**
 * Theorem API - Manages REANA theorem library
 * Endpoints for browsing, searching, and selecting theorems
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/Logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $database = new Database();
    $demoMode = $database->isDemoMode();
    $db = $demoMode ? null : $database->getConnection();
    $logger = new Logger();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'get_categories':
            getCategories($db, $demoMode);
            break;
            
        case 'get_theorems':
            getTheorems($db, $demoMode);
            break;
            
        case 'get_theorem_by_id':
            getTheoremById($db);
            break;
            
        case 'search_theorems':
            searchTheorems($db);
            break;
            
        case 'get_related_theorems':
            getRelatedTheorems($db);
            break;
            
        case 'increment_usage':
            incrementUsage($db);
            break;
            
        case 'get_popular_theorems':
            getPopularTheorems($db);
            break;
            
        case 'get_theorems_by_difficulty':
            getTheoremsByDifficulty($db);
            break;
            
        case 'get_theorem_statistics':
            getTheoremStatistics($db);
            break;
            
        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $logger->error('Theorem API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Get all theorem categories
 */
function getCategories($db, $demoMode = false) {
    if ($demoMode || !$db) {
        // Return demo categories
        $categories = [
            ['id' => 1, 'name' => 'Real Numbers & Completeness', 'description' => 'Fundamental properties of real numbers', 'theorem_count' => 5, 'display_order' => 1],
            ['id' => 2, 'name' => 'Sequences & Limits', 'description' => 'Convergence and limits of sequences', 'theorem_count' => 8, 'display_order' => 2],
            ['id' => 3, 'name' => 'Series', 'description' => 'Infinite series and convergence tests', 'theorem_count' => 6, 'display_order' => 3],
            ['id' => 4, 'name' => 'Continuity', 'description' => 'Continuous functions and their properties', 'theorem_count' => 4, 'display_order' => 4],
            ['id' => 5, 'name' => 'Differentiation', 'description' => 'Derivatives and differential calculus', 'theorem_count' => 5, 'display_order' => 5],
        ];
        echo json_encode(['success' => true, 'categories' => $categories, 'demo_mode' => true]);
        return;
    }
    
    $query = "SELECT * FROM theorem_categories ORDER BY display_order ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get theorem count for each category
    foreach ($categories as &$category) {
        $countQuery = "SELECT COUNT(*) as count FROM theorems WHERE category_id = ? AND is_active = TRUE";
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute([$category['id']]);
        $category['theorem_count'] = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
}

/**
 * Get theorems with filters
 */
function getTheorems($db, $demoMode = false) {
    if ($demoMode || !$db) {
        // Return demo theorems
        $demoTheorems = [
            [
                'id' => 1, 'name' => 'Archimedean Property', 'category_id' => 1, 'category_name' => 'Real Numbers & Completeness',
                'statement' => 'For any real number x, there exists a natural number n such that n > x',
                'difficulty_level' => 'intermediate',
                'natural_language_description' => 'The natural numbers are unbounded - no matter how large a real number is, there is always a natural number larger than it.',
                'lean_code' => 'theorem archimedean_property (x : ℝ) : ∃ n : ℕ, (n : ℝ) > x := by\n  sorry',
                'tags' => ['real numbers', 'fundamental'], 'prerequisites' => [], 'related_theorems' => [2],
                'proof_hints' => 'Use the unboundedness of natural numbers', 'usage_count' => 0
            ],
            [
                'id' => 2, 'name' => 'Squeeze Theorem', 'category_id' => 2, 'category_name' => 'Sequences & Limits',
                'statement' => 'If f(n) ≤ g(n) ≤ h(n) for all n and lim f(n) = lim h(n) = L, then lim g(n) = L',
                'difficulty_level' => 'intermediate',
                'natural_language_description' => 'If a sequence is squeezed between two other sequences that converge to the same limit, it must also converge to that limit.',
                'lean_code' => 'theorem squeeze_theorem (f g h : ℕ → ℝ) (L : ℝ) : \n  (∀ n, f n ≤ g n ∧ g n ≤ h n) → \n  Filter.Tendsto f Filter.atTop (𝓝 L) → \n  Filter.Tendsto h Filter.atTop (𝓝 L) → \n  Filter.Tendsto g Filter.atTop (𝓝 L) := by sorry',
                'tags' => ['sequences', 'limits'], 'prerequisites' => [], 'related_theorems' => [3],
                'proof_hints' => 'Use epsilon definition of limit', 'usage_count' => 0
            ],
            [
                'id' => 3, 'name' => 'Monotone Convergence Theorem', 'category_id' => 2, 'category_name' => 'Sequences & Limits',
                'statement' => 'A monotone bounded sequence converges',
                'difficulty_level' => 'advanced',
                'natural_language_description' => 'Any sequence that is both monotone (always increasing or always decreasing) and bounded must converge to a limit.',
                'lean_code' => 'theorem monotone_convergence (a : ℕ → ℝ) (M : ℝ) :\n  Monotone a → (∀ n, a n ≤ M) → ∃ L, Filter.Tendsto a Filter.atTop (𝓝 L) := by sorry',
                'tags' => ['sequences', 'convergence'], 'prerequisites' => [], 'related_theorems' => [2],
                'proof_hints' => 'Use completeness of real numbers', 'usage_count' => 0
            ],
            [
                'id' => 4, 'name' => 'Intermediate Value Theorem', 'category_id' => 4, 'category_name' => 'Continuity',
                'statement' => 'If f is continuous on [a,b] and y is between f(a) and f(b), then there exists c in [a,b] such that f(c) = y',
                'difficulty_level' => 'advanced',
                'natural_language_description' => 'A continuous function on a closed interval takes on every value between its minimum and maximum.',
                'lean_code' => 'theorem intermediate_value (f : ℝ → ℝ) (a b y : ℝ) :\n  Continuous f → a ≤ b → f a ≤ y → y ≤ f b → \n  ∃ c ∈ Set.Icc a b, f c = y := by sorry',
                'tags' => ['continuity', 'fundamental'], 'prerequisites' => [], 'related_theorems' => [],
                'proof_hints' => 'Use completeness and continuity', 'usage_count' => 0
            ],
            [
                'id' => 5, 'name' => 'Mean Value Theorem', 'category_id' => 5, 'category_name' => 'Differentiation',
                'statement' => 'If f is continuous on [a,b] and differentiable on (a,b), then there exists c in (a,b) such that f\'(c) = (f(b) - f(a))/(b - a)',
                'difficulty_level' => 'advanced',
                'natural_language_description' => 'For a differentiable function, there is at least one point where the instantaneous rate of change equals the average rate of change.',
                'lean_code' => 'theorem mean_value (f : ℝ → ℝ) (a b : ℝ) :\n  a < b → ContinuousOn f (Set.Icc a b) → DifferentiableOn ℝ f (Set.Ioo a b) →\n  ∃ c ∈ Set.Ioo a b, deriv f c = (f b - f a) / (b - a) := by sorry',
                'tags' => ['differentiation', 'fundamental'], 'prerequisites' => [], 'related_theorems' => [],
                'proof_hints' => 'Apply Rolle\'s theorem to auxiliary function', 'usage_count' => 0
            ]
        ];
        echo json_encode(['success' => true, 'theorems' => $demoTheorems, 'count' => count($demoTheorems), 'demo_mode' => true]);
        return;
    }
    
    $category_id = $_GET['category_id'] ?? null;
    $difficulty = $_GET['difficulty'] ?? null;
    $limit = (int)($_GET['limit'] ?? 100);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $query = "SELECT t.*, c.name as category_name 
              FROM theorems t 
              JOIN theorem_categories c ON t.category_id = c.id 
              WHERE t.is_active = TRUE";
    
    $params = [];
    
    if ($category_id) {
        $query .= " AND t.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($difficulty) {
        $query .= " AND t.difficulty_level = ?";
        $params[] = $difficulty;
    }
    
    $query .= " ORDER BY c.display_order ASC, t.name ASC LIMIT {$limit} OFFSET {$offset}";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $theorems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse JSON fields
    foreach ($theorems as &$theorem) {
        $theorem['tags'] = json_decode($theorem['tags'] ?? '[]');
        $theorem['prerequisites'] = json_decode($theorem['prerequisites'] ?? '[]');
        $theorem['related_theorems'] = json_decode($theorem['related_theorems'] ?? '[]');
    }
    
    echo json_encode([
        'success' => true,
        'theorems' => $theorems,
        'count' => count($theorems)
    ]);
}

/**
 * Get a specific theorem by ID
 */
function getTheoremById($db) {
    $theorem_id = $_GET['theorem_id'] ?? null;
    
    if (!$theorem_id) {
        throw new Exception('Theorem ID is required');
    }
    
    if (!$db) {
        // Demo mode - return demo theorem
        $demoTheorems = [
            ['id' => 1, 'name' => 'Archimedean Property', 'category_name' => 'Real Numbers', 'statement' => 'For any real number x, there exists a natural number n such that n > x', 'difficulty_level' => 'intermediate', 'tags' => [], 'prerequisites' => [], 'related_theorems' => []]
        ];
        echo json_encode(['success' => true, 'theorem' => $demoTheorems[0], 'demo_mode' => true]);
        return;
    }
    
    $query = "SELECT t.*, c.name as category_name, c.description as category_description
              FROM theorems t 
              JOIN theorem_categories c ON t.category_id = c.id 
              WHERE t.id = ? AND t.is_active = TRUE";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$theorem_id]);
    
    $theorem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$theorem) {
        throw new Exception('Theorem not found');
    }
    
    // Parse JSON fields
    $theorem['tags'] = json_decode($theorem['tags'] ?? '[]');
    $theorem['prerequisites'] = json_decode($theorem['prerequisites'] ?? '[]');
    $theorem['related_theorems'] = json_decode($theorem['related_theorems'] ?? '[]');
    
    // Get prerequisite theorem details
    $prerequisite_details = [];
    if (!empty($theorem['prerequisites'])) {
        $placeholders = str_repeat('?,', count($theorem['prerequisites']) - 1) . '?';
        $prereqQuery = "SELECT id, name, statement FROM theorems WHERE name IN ($placeholders)";
        $prereqStmt = $db->prepare($prereqQuery);
        $prereqStmt->execute($theorem['prerequisites']);
        $prerequisite_details = $prereqStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $theorem['prerequisite_details'] = $prerequisite_details;
    
    echo json_encode([
        'success' => true,
        'theorem' => $theorem
    ]);
}

/**
 * Search theorems by text
 */
function searchTheorems($db) {
    $search_term = $_GET['q'] ?? '';
    $limit = $_GET['limit'] ?? 50;
    
    if (empty($search_term)) {
        throw new Exception('Search term is required');
    }
    
    if (!$db) {
        // Demo mode - return empty search results
        echo json_encode(['success' => true, 'theorems' => [], 'count' => 0, 'demo_mode' => true]);
        return;
    }
    
    // Use FULLTEXT search if available, otherwise use LIKE
    $query = "SELECT t.*, c.name as category_name,
              MATCH(t.name, t.natural_language_description) AGAINST(? IN BOOLEAN MODE) as relevance
              FROM theorems t 
              JOIN theorem_categories c ON t.category_id = c.id 
              WHERE t.is_active = TRUE 
              AND (MATCH(t.name, t.natural_language_description) AGAINST(? IN BOOLEAN MODE)
                   OR t.name LIKE ? 
                   OR t.statement LIKE ?
                   OR t.natural_language_description LIKE ?)
              ORDER BY relevance DESC, t.usage_count DESC
              LIMIT ?";
    
    $searchPattern = "%{$search_term}%";
    $stmt = $db->prepare($query);
    $stmt->execute([
        $search_term, 
        $search_term,
        $searchPattern,
        $searchPattern,
        $searchPattern,
        (int)$limit
    ]);
    
    $theorems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse JSON fields
    foreach ($theorems as &$theorem) {
        $theorem['tags'] = json_decode($theorem['tags'] ?? '[]');
    }
    
    echo json_encode([
        'success' => true,
        'theorems' => $theorems,
        'count' => count($theorems)
    ]);
}

/**
 * Get related theorems
 */
function getRelatedTheorems($db) {
    $theorem_id = $_GET['theorem_id'] ?? null;
    
    if (!$theorem_id) {
        throw new Exception('Theorem ID is required');
    }
    
    if (!$db) {
        // Demo mode - return empty related theorems
        echo json_encode(['success' => true, 'related_theorems' => [], 'demo_mode' => true]);
        return;
    }
    
    // Get the theorem
    $query = "SELECT * FROM theorems WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$theorem_id]);
    $theorem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$theorem) {
        throw new Exception('Theorem not found');
    }
    
    $related_names = json_decode($theorem['related_theorems'] ?? '[]');
    $related_theorems = [];
    
    if (!empty($related_names)) {
        $placeholders = str_repeat('?,', count($related_names) - 1) . '?';
        $relatedQuery = "SELECT id, name, statement, difficulty_level, category_id 
                        FROM theorems 
                        WHERE name IN ($placeholders) AND is_active = TRUE";
        $relatedStmt = $db->prepare($relatedQuery);
        $relatedStmt->execute($related_names);
        $related_theorems = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'related_theorems' => $related_theorems
    ]);
}

/**
 * Increment theorem usage count
 */
function incrementUsage($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    $theorem_id = $data['theorem_id'] ?? null;
    
    if (!$theorem_id) {
        throw new Exception('Theorem ID is required');
    }
    
    if (!$db) {
        // Demo mode - just return success
        echo json_encode(['success' => true, 'message' => 'Usage count incremented (demo mode)', 'demo_mode' => true]);
        return;
    }
    
    $query = "UPDATE theorems SET usage_count = usage_count + 1 WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$theorem_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usage count incremented'
    ]);
}

/**
 * Get most popular theorems
 */
function getPopularTheorems($db) {
    $limit = $_GET['limit'] ?? 10;
    
    if (!$db) {
        // Demo mode - return sample popular theorems
        echo json_encode(['success' => true, 'theorems' => [], 'demo_mode' => true]);
        return;
    }
    
    $query = "SELECT t.*, c.name as category_name 
              FROM theorems t 
              JOIN theorem_categories c ON t.category_id = c.id 
              WHERE t.is_active = TRUE
              ORDER BY t.usage_count DESC, t.name ASC
              LIMIT ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([(int)$limit]);
    
    $theorems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($theorems as &$theorem) {
        $theorem['tags'] = json_decode($theorem['tags'] ?? '[]');
    }
    
    echo json_encode([
        'success' => true,
        'theorems' => $theorems
    ]);
}

/**
 * Get theorems by difficulty level
 */
function getTheoremsByDifficulty($db) {
    $difficulty = $_GET['difficulty'] ?? null;
    
    if (!$difficulty) {
        throw new Exception('Difficulty level is required');
    }
    
    $valid_levels = ['beginner', 'intermediate', 'advanced', 'expert'];
    if (!in_array($difficulty, $valid_levels)) {
        throw new Exception('Invalid difficulty level');
    }
    
    if (!$db) {
        // Demo mode - return empty results
        echo json_encode(['success' => true, 'theorems' => [], 'count' => 0, 'demo_mode' => true]);
        return;
    }
    
    $query = "SELECT t.*, c.name as category_name 
              FROM theorems t 
              JOIN theorem_categories c ON t.category_id = c.id 
              WHERE t.difficulty_level = ? AND t.is_active = TRUE
              ORDER BY c.display_order ASC, t.name ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$difficulty]);
    
    $theorems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($theorems as &$theorem) {
        $theorem['tags'] = json_decode($theorem['tags'] ?? '[]');
    }
    
    echo json_encode([
        'success' => true,
        'theorems' => $theorems,
        'count' => count($theorems)
    ]);
}

/**
 * Get theorem statistics
 */
function getTheoremStatistics($db) {
    if (!$db) {
        // Demo mode - return demo statistics
        echo json_encode([
            'success' => true,
            'statistics' => [
                'total_theorems' => 5,
                'by_difficulty' => [
                    ['difficulty_level' => 'intermediate', 'count' => 3],
                    ['difficulty_level' => 'advanced', 'count' => 2]
                ],
                'by_category' => [
                    ['category_name' => 'Real Numbers', 'count' => 1],
                    ['category_name' => 'Sequences & Limits', 'count' => 2]
                ]
            ],
            'demo_mode' => true
        ]);
        return;
    }
    
    $stats = [];
    
    // Total theorems
    $query = "SELECT COUNT(*) as total FROM theorems WHERE is_active = TRUE";
    $stmt = $db->query($query);
    $stats['total_theorems'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // By difficulty
    $query = "SELECT difficulty_level, COUNT(*) as count 
              FROM theorems 
              WHERE is_active = TRUE 
              GROUP BY difficulty_level";
    $stmt = $db->query($query);
    $stats['by_difficulty'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // By category
    $query = "SELECT c.name, COUNT(t.id) as count 
              FROM theorem_categories c 
              LEFT JOIN theorems t ON c.id = t.category_id AND t.is_active = TRUE
              GROUP BY c.id, c.name 
              ORDER BY c.display_order ASC";
    $stmt = $db->query($query);
    $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Most popular
    $query = "SELECT name, usage_count 
              FROM theorems 
              WHERE is_active = TRUE 
              ORDER BY usage_count DESC 
              LIMIT 5";
    $stmt = $db->query($query);
    $stats['most_popular'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'statistics' => $stats
    ]);
}
