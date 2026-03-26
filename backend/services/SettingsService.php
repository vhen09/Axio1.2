<?php
/**
 * Settings Service
 * Centralized service for managing all user settings with database persistence
 * Handles storage, retrieval, updates, and synchronization
 */

class SettingsService {
    private $db;
    private $logger;
    
    // Default settings structure
    private $defaultSettings = [
        'theme' => 'light',
        'fontSize' => 'normal',
        'proofMode' => 'step-by-step',
        'autoSave' => true,
        'showPreview' => true,
        'difficultyLevel' => 'beginner',
        'autoTutorial' => false,
        'showHints' => true,
        'learningLanguage' => 'en',
        'scoreNotifications' => true,
        'feedbackNotifications' => true,
        'soundNotifications' => false,
        'preferredDomains' => ['algebra', 'calculus']
    ];

    public function __construct(PDO $db, Logger $logger = null) {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get all settings for a user
     * @param int $userId
     * @return array Settings array or null if user not found
     */
    public function getSettings($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                // Return defaults if no settings exist yet
                return [
                    'success' => true,
                    'settings' => $this->defaultSettings
                ];
            }

            // Parse JSON fields
            $settings = [];
            foreach ($this->defaultSettings as $key => $default) {
                if ($key === 'preferredDomains') {
                    $settings[$key] = json_decode($result[$key] ?? '[]', true);
                } else {
                    $value = $result[$key] ?? null;
                    // Convert string booleans back
                    if ($value === 'true') $value = true;
                    if ($value === 'false') $value = false;
                    $settings[$key] = $value !== null ? $value : $default;
                }
            }

            return [
                'success' => true,
                'settings' => $settings,
                'last_updated' => $result['updated_at'] ?? null
            ];

        } catch (Exception $e) {
            $this->log('Error getting settings: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to retrieve settings',
                'settings' => $this->defaultSettings
            ];
        }
    }

    /**
     * Update a single setting
     * @param int $userId
     * @param string $settingKey
     * @param mixed $value
     * @return array Result with success status
     */
    public function updateSetting($userId, $settingKey, $value) {
        try {
            // Validate setting key exists in defaults
            if (!array_key_exists($settingKey, $this->defaultSettings)) {
                return [
                    'success' => false,
                    'error' => 'Invalid setting key: ' . $settingKey
                ];
            }

            // Type validation
            $validatedValue = $this->validateSettingValue($settingKey, $value);
            if ($validatedValue === null) {
                return [
                    'success' => false,
                    'error' => 'Invalid value for setting: ' . $settingKey
                ];
            }

            // Prepare data for database
            $dbValue = $this->prepareValueForDatabase($settingKey, $validatedValue);

            // Update or insert
            $stmt = $this->db->prepare("
                INSERT INTO user_settings (user_id, $settingKey, updated_at)
                VALUES (?, ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    $settingKey = VALUES($settingKey),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$userId, $dbValue]);

            $this->log("Setting updated for user $userId: $settingKey");

            return [
                'success' => true,
                'message' => 'Setting updated successfully',
                'setting' => $settingKey,
                'value' => $validatedValue
            ];

        } catch (Exception $e) {
            $this->log('Error updating setting: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to update setting'
            ];
        }
    }

    /**
     * Update multiple settings at once
     * @param int $userId
     * @param array $settings Key-value pairs of settings
     * @return array Result with success status
     */
    public function updateSettings($userId, array $settings) {
        try {
            $updates = [];
            $params = [];
            $params[] = $userId;

            foreach ($settings as $key => $value) {
                if (!array_key_exists($key, $this->defaultSettings)) {
                    continue; // Skip invalid keys
                }

                $validatedValue = $this->validateSettingValue($key, $value);
                if ($validatedValue === null) {
                    continue; // Skip invalid values
                }

                $dbValue = $this->prepareValueForDatabase($key, $validatedValue);
                $updates[] = "$key = ?";
                $params[] = $dbValue;
            }

            if (empty($updates)) {
                return [
                    'success' => false,
                    'error' => 'No valid settings to update'
                ];
            }

            $sql = "
                INSERT INTO user_settings (user_id, " . implode(', ', array_map(function($k) { 
                    return str_replace(' = ?', '', $k); 
                }, $updates)) . ", updated_at)
                VALUES (?, " . implode(', ', array_fill(0, count($updates), '?')) . ", CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    " . implode(', ') . ",
                    updated_at = CURRENT_TIMESTAMP
            ";

            // Rebuild query properly
            $sql = "
                INSERT INTO user_settings (user_id, " . implode(', ', preg_replace('/\s*=\s*\?/', '', $updates)) . ", updated_at)
                VALUES (?, " . implode(', ', array_fill(0, count($updates), '?')) . ", CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    " . implode(', ', $updates) . ",
                    updated_at = CURRENT_TIMESTAMP
            ";

            $stmt = $this->db->prepare($sql);
            $finalParams = array_merge([$userId], $params);
            $stmt->execute($finalParams);

            $this->log("Multiple settings updated for user $userId");

            return [
                'success' => true,
                'message' => 'Settings updated successfully',
                'count' => count($updates)
            ];

        } catch (Exception $e) {
            $this->log('Error updating settings: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to update settings: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reset settings to defaults for a user
     * @param int $userId
     * @return array Result
     */
    public function resetSettings($userId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM user_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);

            $this->log("Settings reset to defaults for user $userId");

            return [
                'success' => true,
                'message' => 'Settings reset to defaults',
                'settings' => $this->defaultSettings
            ];

        } catch (Exception $e) {
            $this->log('Error resetting settings: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to reset settings'
            ];
        }
    }

    /**
     * Get default settings
     * @return array Default settings
     */
    public function getDefaults() {
        return $this->defaultSettings;
    }

    /**
     * Validate setting value based on its type
     * @param string $settingKey
     * @param mixed $value
     * @return mixed Validated value or null
     */
    private function validateSettingValue($settingKey, $value) {
        switch ($settingKey) {
            // String settings
            case 'theme':
                return in_array($value, ['light', 'dark', 'auto']) ? $value : null;
            
            case 'fontSize':
                return in_array($value, ['small', 'normal', 'large']) ? $value : null;
            
            case 'proofMode':
                return in_array($value, ['step-by-step', 'full-proof']) ? $value : null;
            
            case 'difficultyLevel':
                return in_array($value, ['beginner', 'intermediate', 'advanced']) ? $value : null;
            
            case 'learningLanguage':
                // Allow any ISO language code
                return is_string($value) && strlen($value) === 2 ? $value : null;
            
            // Boolean settings
            case 'autoSave':
            case 'showPreview':
            case 'autoTutorial':
            case 'showHints':
            case 'scoreNotifications':
            case 'feedbackNotifications':
            case 'soundNotifications':
                return is_bool($value) ? $value : (($value === 'true' || $value === 1) ? true : false);
            
            // Array settings
            case 'preferredDomains':
                if (!is_array($value)) {
                    return null;
                }
                // Validate domain names
                $validDomains = ['algebra', 'calculus', 'geometry', 'logic', 'number_theory', 'statistics'];
                $filtered = array_filter($value, function($v) use ($validDomains) {
                    return in_array($v, $validDomains);
                });
                return !empty($filtered) ? array_values($filtered) : null;
            
            default:
                return null;
        }
    }

    /**
     * Prepare value for database storage
     * @param string $settingKey
     * @param mixed $value
     * @return mixed Database-ready value
     */
    private function prepareValueForDatabase($settingKey, $value) {
        if ($settingKey === 'preferredDomains') {
            return json_encode($value);
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return $value;
    }

    /**
     * Log messages
     * @param string $message
     */
    private function log($message) {
        if ($this->logger) {
            $this->logger->info('SettingsService: ' . $message);
        } else {
            error_log('SettingsService: ' . $message);
        }
    }
}
?>
