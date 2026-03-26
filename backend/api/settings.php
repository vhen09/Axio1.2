<?php
/**
 * Settings API Endpoint
 * Centralized API for managing all user settings
 * Supports GET (retrieve), POST (update), and OPTIONS (CORS)
 */

@require_once __DIR__ . '/../config/auto-setup.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SettingsService.php';
require_once __DIR__ . '/../services/Logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

try {
    // Initialize database and logger
    $database = new Database();
    $db = $database->getConnection();
    $logger = new Logger();

    if (!$db) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => 'Database unavailable'
        ]);
        exit();
    }

    // Initialize service
    $settingsService = new SettingsService($db, $logger);

    // Get action and method
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    // Allow get_defaults without authentication
    if ($action !== 'get_defaults' && !isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Not authenticated'
        ]);
        exit();
    }

    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    // Parse request body for POST
    $data = [];
    if ($method === 'POST' || $method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // Route actions
    switch ($action) {
        /**
         * GET /api/settings.php?action=get
         * Retrieve all settings for current user
         */
        case 'get':
        case 'get_all':
            $result = $settingsService->getSettings($userId);
            http_response_code($result['success'] ? 200 : 500);
            echo json_encode($result);
            break;

        /**
         * GET /api/settings.php?action=get_defaults
         * Get default settings (no auth required)
         */
        case 'get_defaults':
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'defaults' => $settingsService->getDefaults()
            ]);
            break;

        /**
         * GET /api/settings.php?action=get_by_category
         * Get settings grouped by category (requires auth)
         */
        case 'get_by_category':
            if (!$userId) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Not authenticated'
                ]);
                break;
            }
            $result = $settingsService->getSettingsByCategory($userId);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'categories' => $result,
                'last_updated' => date('Y-m-d H:i:s')
            ]);
            break;

        /**
         * POST /api/settings.php?action=update
         * Update a single setting
         * Body: { "key": "theme", "value": "dark" }
         */
        case 'update':
            if (empty($data['key']) || !isset($data['value'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Missing key or value in request'
                ]);
                break;
            }

            $result = $settingsService->updateSetting($userId, $data['key'], $data['value']);
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;

        /**
         * POST /api/settings.php?action=update_batch
         * Update multiple settings at once
         * Body: { "settings": { "theme": "dark", "fontSize": "large", ... } }
         */
        case 'update_batch':
        case 'update_multiple':
            if (empty($data['settings']) || !is_array($data['settings'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid settings object in request'
                ]);
                break;
            }

            $result = $settingsService->updateSettings($userId, $data['settings']);
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;

        /**
         * DELETE /api/settings.php?action=reset
         * Reset all settings to defaults
         */
        case 'reset':
            if ($method !== 'DELETE' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'error' => 'Method not allowed'
                ]);
                break;
            }

            // Require confirmation
            if (empty($data['confirm']) || $data['confirm'] !== true) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Confirmation required. Send { "confirm": true }'
                ]);
                break;
            }

            $result = $settingsService->resetSettings($userId);
            http_response_code($result['success'] ? 200 : 500);
            echo json_encode($result);
            break;

        /**
         * POST /api/settings.php?action=sync
         * Sync settings from client (for backup/verification)
         * Body: { "clientSettings": { ... } }
         */
        case 'sync':
            if (empty($data['clientSettings'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'clientSettings required'
                ]);
                break;
            }

            // Get current server settings
            $serverSettings = $settingsService->getSettings($userId);
            
            // If client and server differ, update server with client settings
            if (!empty($data['clientSettings']) && is_array($data['clientSettings'])) {
                $syncResult = $settingsService->updateSettings($userId, $data['clientSettings']);
            }

            // Return merged settings
            $finalSettings = $settingsService->getSettings($userId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Settings synchronized',
                'settings' => $finalSettings['settings'],
                'last_updated' => $finalSettings['last_updated'] ?? null
            ]);
            break;

        /**
         * Invalid action
         */
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action',
                'available_actions' => [
                    'get' => 'GET - Retrieve all settings',
                    'get_defaults' => 'GET - Get default settings',
                    'update' => 'POST - Update single setting',
                    'update_batch' => 'POST - Update multiple settings',
                    'reset' => 'DELETE/POST - Reset to defaults',
                    'sync' => 'POST - Sync settings from client'
                ]
            ]);
    }

} catch (Exception $e) {
    error_log('Settings API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
?>
