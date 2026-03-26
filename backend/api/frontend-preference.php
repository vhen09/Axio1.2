<?php
/**
 * AXIO Frontend Preference API
 * 
 * Handles saving and loading user's frontend preference (Vanilla JS vs React)
 * 
 * Endpoints:
 * - GET /backend/api/frontend-preference.php?action=get   → Get user's preference
 * - POST /backend/api/frontend-preference.php              → Set user's preference (action=set)
 * - GET /backend/api/frontend-preference.php?action=list  → List available frontends
 * 
 * Created: March 26, 2026
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit(0);
}

// Start session
@session_start();

// Check authentication
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  http_response_code(401);
  echo json_encode([
    'success' => false,
    'error' => 'Not authenticated',
    'code' => 'UNAUTHENTICATED'
  ]);
  exit;
}

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
  // Include database config
  require_once __DIR__ . '/../config/database.php';
  $db = new Database();
  $pdo = $db->getConnection();

  // Dispatch action
  switch ($action) {
    case 'get':
      handleGet($pdo, $user_id);
      break;

    case 'set':
      handleSet($pdo, $user_id);
      break;

    case 'list':
      handleList($pdo, $user_id);
      break;

    default:
      http_response_code(400);
      echo json_encode([
        'success' => false,
        'error' => 'Unknown action',
        'code' => 'INVALID_ACTION'
      ]);
  }

} catch (Exception $e) {
  error_log("Frontend Preference API Error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => 'Server error',
    'code' => 'SERVER_ERROR',
    'message' => $e->getMessage()
  ]);
}

/**
 * Get user's frontend preference
 */
function handleGet($pdo, $user_id) {
  try {
    // Check if user_preferences table exists and has the column
    $tableCheck = $pdo->query("
      SELECT column_name 
      FROM information_schema.columns 
      WHERE table_name = 'user_preferences' 
      AND column_name = 'preferred_frontend'
    ");

    if ($tableCheck->rowCount() === 0) {
      // Column doesn't exist, create it
      $pdo->exec("
        ALTER TABLE user_preferences 
        ADD COLUMN preferred_frontend VARCHAR(20) DEFAULT 'vanilla'
      ");
      error_log("Created preferred_frontend column in user_preferences table");
    }

    // Query preference
    $stmt = $pdo->prepare("
      SELECT preferred_frontend 
      FROM user_preferences 
      WHERE user_id = ? 
      LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $frontend = 'vanilla'; // Default

    if ($result && isset($result['preferred_frontend'])) {
      $pref = $result['preferred_frontend'];
      if (in_array($pref, ['vanilla', 'react'])) {
        $frontend = $pref;
      }
    }

    echo json_encode([
      'success' => true,
      'frontend' => $frontend,
      'message' => "User preference loaded: $frontend"
    ]);

  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'error' => 'Failed to load preference',
      'message' => $e->getMessage()
    ]);
  }
}

/**
 * Set user's frontend preference
 */
function handleSet($pdo, $user_id) {
  try {
    $frontend = $_POST['frontend'] ?? '';

    // Validate
    if (!in_array($frontend, ['vanilla', 'react'])) {
      http_response_code(400);
      echo json_encode([
        'success' => false,
        'error' => 'Invalid frontend choice',
        'code' => 'INVALID_FRONTEND'
      ]);
      return;
    }

    // Check if user_preferences table exists and has the column
    $tableCheck = $pdo->query("
      SELECT column_name 
      FROM information_schema.columns 
      WHERE table_name = 'user_preferences' 
      AND column_name = 'preferred_frontend'
    ");

    if ($tableCheck->rowCount() === 0) {
      // Column doesn't exist, create it
      $pdo->exec("
        ALTER TABLE user_preferences 
        ADD COLUMN preferred_frontend VARCHAR(20) DEFAULT 'vanilla'
      ");
      error_log("Created preferred_frontend column in user_preferences table");
    }

    // Try INSERT first, then UPDATE if exists
    // Using different syntax for PostgreSQL vs SQLite
    $dbDriver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($dbDriver === 'pgsql') {
      // PostgreSQL: Use UPSERT (INSERT ... ON CONFLICT)
      $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, preferred_frontend) 
        VALUES (?, ?) 
        ON CONFLICT(user_id) 
        DO UPDATE SET preferred_frontend = EXCLUDED.preferred_frontend
      ");
    } else if ($dbDriver === 'sqlite') {
      // SQLite: Use REPLACE
      $stmt = $pdo->prepare("
        REPLACE INTO user_preferences (user_id, preferred_frontend) 
        VALUES (?, ?)
      ");
    } else {
      // MySQL: Use INSERT ... ON DUPLICATE KEY UPDATE
      $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, preferred_frontend) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE preferred_frontend = VALUES(preferred_frontend)
      ");
    }

    $stmt->execute([$user_id, $frontend]);

    echo json_encode([
      'success' => true,
      'frontend' => $frontend,
      'message' => "Frontend preference updated to: $frontend"
    ]);

  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'error' => 'Failed to save preference',
      'message' => $e->getMessage()
    ]);
  }
}

/**
 * List available frontends
 */
function handleList($pdo, $user_id) {
  try {
    // Get current preference
    $stmt = $pdo->prepare("
      SELECT preferred_frontend 
      FROM user_preferences 
      WHERE user_id = ? 
      LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $current = $result['preferred_frontend'] ?? 'vanilla';

    echo json_encode([
      'success' => true,
      'available' => [
        [
          'id' => 'vanilla',
          'name' => 'Vanilla JavaScript',
          'icon' => '📄',
          'description' => 'Lightweight, responsive vanilla JS interface'
        ],
        [
          'id' => 'react',
          'name' => 'React',
          'icon' => '⚛️',
          'description' => 'Modern React-based interface with advanced features'
        ]
      ],
      'current' => $current
    ]);

  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'error' => 'Failed to list frontends',
      'message' => $e->getMessage()
    ]);
  }
}
?>
