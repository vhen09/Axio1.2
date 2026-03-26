<?php
/**
 * AXIO Hybrid Frontend Router
 * 
 * Serves as the main entry point for the hybrid frontend architecture.
 * Routes requests to either Vanilla JS or React frontend based on:
 * 1. User's saved preference (from database)
 * 2. Query parameter (?frontend=vanilla|react)
 * 3. Default (vanilla JS)
 * 
 * Created: March 26, 2026
 * Usage: Replace index.php with this file
 */

// Auto-initialize database on app startup
@require_once __DIR__ . '/backend/config/auto-setup.php';

// Start session to check for logged-in user
session_start();

/**
 * Determine which frontend to serve
 */
function getFrontendChoice() {
  $user_id = $_SESSION['user_id'] ?? null;
  $requested_frontend = $_GET['frontend'] ?? null;
  $default_frontend = 'vanilla'; // Always default to vanilla JS

  // If frontend is explicitly requested via query parameter, use it
  if ($requested_frontend && in_array($requested_frontend, ['vanilla', 'react'])) {
    return $requested_frontend;
  }

  // If user is logged in, load their preference from database
  if ($user_id) {
    try {
      require_once __DIR__ . '/backend/config/database.php';
      $db = new Database();
      $pdo = $db->getConnection();
      
      $stmt = $pdo->prepare("
        SELECT preferred_frontend 
        FROM user_preferences 
        WHERE user_id = ? 
        LIMIT 1
      ");
      $stmt->execute([$user_id]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
      if ($result && isset($result['preferred_frontend'])) {
        $preference = $result['preferred_frontend'];
        if (in_array($preference, ['vanilla', 'react'])) {
          return $preference;
        }
      }
    } catch (Exception $e) {
      error_log("Hybrid Router: Could not load frontend preference - " . $e->getMessage());
      // Fall through to default
    }
  }

  // Return default (vanilla JS)
  return $default_frontend;
}

/**
 * Sanitize frontend choice
 */
function validateFrontend($frontend) {
  return in_array($frontend, ['vanilla', 'react']) ? $frontend : 'vanilla';
}

/**
 * Get frontend URL
 */
function getFrontendUrl($frontend) {
  $frontend = validateFrontend($frontend);
  
  if ($frontend === 'react') {
    return '/axio2.0/lean4-ai-web-app/frontend/';
  } else {
    return '/frontend/pages/welcome.html';
  }
}

// Determine frontend
$frontend = getFrontendChoice();
$frontendUrl = getFrontendUrl($frontend);

// Log the decision
error_log("Hybrid Router: Serving " . strtoupper($frontend) . " frontend to user: " . 
  ($_SESSION['user_id'] ?? 'anonymous'));

// Redirect to frontend
header("Location: {$frontendUrl}", true, 302);
exit;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AXIO - Loading...</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
      background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .loading-container {
      text-align: center;
      padding: 40px 20px;
    }
    
    .logo {
      font-size: 2.5em;
      font-weight: bold;
      color: #1e3a8a;
      margin-bottom: 20px;
      letter-spacing: 0.05em;
    }
    
    .loading-text {
      color: #4b5563;
      font-size: 1.1em;
      margin-bottom: 30px;
    }
    
    .spinner {
      width: 40px;
      height: 40px;
      border: 4px solid #e5e7eb;
      border-top-color: #1e3a8a;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin: 0 auto;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    .frontend-info {
      margin-top: 40px;
      font-size: 0.9em;
      color: #6b7280;
    }
  </style>
</head>
<body>
  <div class="loading-container">
    <div class="logo">AXIO</div>
    <div class="loading-text">Loading your interface...</div>
    <div class="spinner"></div>
    <div class="frontend-info">
      <p>If this page doesn't redirect automatically, <a href="/frontend/pages/welcome.html">click here</a>.</p>
    </div>
  </div>
  
  <script>
    // Fallback redirect after 3 seconds
    setTimeout(function() {
      window.location.href = '/frontend/pages/welcome.html';
    }, 3000);
  </script>
</body>
</html>
