<?php
/**
 * Settings Service - Comprehensive Settings Management
 * Manages all user settings across 5 categories:
 * 1. Appearance Settings (theme, colors, layout, fonts)
 * 2. Notification Settings (email, in-app, alarms, sound)
 * 3. Proof Settings (validation, logging, approval)
 * 4. Learning Settings (tutorials, guides, suggestions)
 * 5. Mathematical Domain Settings (calculations, precision)
 */

class SettingsService {
    private $db;
    private $logger;
    
    // Comprehensive default settings for all 5 categories
    private $defaultSettings = [
        // ===== APPEARANCE SETTINGS =====
        'theme' => 'light',
        'font_size' => 'medium',
        'color_scheme' => 'default',
        'primary_color' => '#059669',
        'accent_color' => '#0ea5e9',
        'layout_mode' => 'comfortable',
        
        // ===== NOTIFICATION SETTINGS =====
        'email_notifications' => true,
        'in_app_notifications' => true,
        'maintenance_alerts' => true,
        'reminder_notifications' => true,
        'sound_notifications' => false,
        
        // ===== PROOF SETTINGS =====
        'enable_validation' => true,
        'require_confirmation_delete' => true,
        'enable_activity_logging' => true,
        'enable_audit_trail' => true,
        'require_admin_approval' => false,
        
        // ===== LEARNING SETTINGS =====
        'show_tooltips' => true,
        'enable_tutorials' => true,
        'show_help_guides' => true,
        'enable_walkthrough_mode' => false,
        'enable_auto_suggestions' => true,
        
        // ===== MATHEMATICAL DOMAIN SETTINGS =====
        'enable_math_calculations' => true,
        'enable_formula_validation' => true,
        'enable_real_time_computation' => true,
        'enable_graph_rendering' => true,
        'math_precision' => '4_decimal'
    ];
    
    // Validation rules for each setting
    private $validationRules = [
        'theme' => ['light', 'dark', 'auto'],
        'font_size' => ['small', 'medium', 'large'],
        'color_scheme' => ['default', 'ocean', 'forest', 'sunset', 'custom'],
        'primary_color' => 'hex_color',
        'accent_color' => 'hex_color',
        'layout_mode' => ['compact', 'comfortable'],
        'math_precision' => ['2_decimal', '4_decimal', '6_decimal']
    ];

    public function __construct(PDO $db, Logger $logger = null) {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get all settings for a user
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
                // Create default settings if they don't exist
                $this->createDefaultSettings($userId);
                return [
                    'success' => true,
                    'settings' => $this->defaultSettings,
                    'last_updated' => date('Y-m-d H:i:s')
                ];
            }

            // Convert to appropriate types
            $settings = $this->formatSettings($result);

            return [
                'success' => true,
                'settings' => $settings,
                'last_updated' => $result['updated_at'] ?? date('Y-m-d H:i:s')
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
     */
    public function updateSetting($userId, $settingKey, $value) {
        try {
            // Validate setting exists
            if (!array_key_exists($settingKey, $this->defaultSettings)) {
                return [
                    'success' => false,
                    'error' => 'Invalid setting key: ' . $settingKey
                ];
            }

            // Validate value
            $validatedValue = $this->validateSettingValue($settingKey, $value);
            if ($validatedValue === null && $value !== null && $value !== false && $value !== '') {
                return [
                    'success' => false,
                    'error' => 'Invalid value for setting: ' . $settingKey
                ];
            }

            // Prepare value for database
            $dbValue = $this->prepareValueForDatabase($settingKey, $validatedValue);

            // Update in database
            $stmt = $this->db->prepare("
                UPDATE user_settings 
                SET $settingKey = ?, updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?
            ");
            $stmt->execute([$dbValue, $userId]);

            $this->log("Setting updated for user $userId: $settingKey = $dbValue");

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
                'error' => 'Failed to update setting: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update multiple settings in batch
     */
    public function updateBatch($userId, array $settings) {
        $updated = [];
        $failed = [];

        foreach ($settings as $key => $value) {
            $result = $this->updateSetting($userId, $key, $value);
            if ($result['success']) {
                $updated[] = $key;
            } else {
                $failed[$key] = $result['error'] ?? 'Unknown error';
            }
        }

        return [
            'success' => count($failed) === 0,
            'message' => count($updated) . ' settings updated',
            'updated' => $updated,
            'failed' => $failed,
            'count' => count($updated)
        ];
    }

    /**
     * Reset all settings to defaults
     */
    public function resetSettings($userId) {
        try {
            $result = $this->updateBatch($userId, $this->defaultSettings);
            
            if ($result['success']) {
                $this->log("Settings reset to defaults for user $userId");
            }

            return [
                'success' => $result['success'],
                'message' => 'Settings reset to defaults',
                'settings' => $this->defaultSettings,
                'updated_count' => $result['count']
            ];

        } catch (Exception $e) {
            $this->log('Error resetting settings: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to reset settings: ' . $e->getMessage(),
                'settings' => $this->defaultSettings
            ];
        }
    }

    /**
     * Get default settings
     */
    public function getDefaults() {
        return $this->defaultSettings;
    }
    
    /**
     * Get settings grouped by category
     */
    public function getSettingsByCategory($userId) {
        $settings = $this->getSettings($userId)['settings'];
        
        return [
            'appearance' => [
                'theme' => $settings['theme'],
                'font_size' => $settings['font_size'],
                'color_scheme' => $settings['color_scheme'],
                'primary_color' => $settings['primary_color'],
                'accent_color' => $settings['accent_color'],
                'layout_mode' => $settings['layout_mode'],
            ],
            'notifications' => [
                'email_notifications' => $settings['email_notifications'],
                'in_app_notifications' => $settings['in_app_notifications'],
                'maintenance_alerts' => $settings['maintenance_alerts'],
                'reminder_notifications' => $settings['reminder_notifications'],
                'sound_notifications' => $settings['sound_notifications'],
            ],
            'proof' => [
                'enable_validation' => $settings['enable_validation'],
                'require_confirmation_delete' => $settings['require_confirmation_delete'],
                'enable_activity_logging' => $settings['enable_activity_logging'],
                'enable_audit_trail' => $settings['enable_audit_trail'],
                'require_admin_approval' => $settings['require_admin_approval'],
            ],
            'learning' => [
                'show_tooltips' => $settings['show_tooltips'],
                'enable_tutorials' => $settings['enable_tutorials'],
                'show_help_guides' => $settings['show_help_guides'],
                'enable_walkthrough_mode' => $settings['enable_walkthrough_mode'],
                'enable_auto_suggestions' => $settings['enable_auto_suggestions'],
            ],
            'mathematics' => [
                'enable_math_calculations' => $settings['enable_math_calculations'],
                'enable_formula_validation' => $settings['enable_formula_validation'],
                'enable_real_time_computation' => $settings['enable_real_time_computation'],
                'enable_graph_rendering' => $settings['enable_graph_rendering'],
                'math_precision' => $settings['math_precision'],
            ]
        ];
    }

    /**
     * Validate a setting value based on its type
     */
    private function validateSettingValue($key, $value) {
        // Check if there are specific validation rules
        if (isset($this->validationRules[$key])) {
            $rule = $this->validationRules[$key];
            
            if (is_array($rule)) {
                // Whitelist validation
                return in_array($value, $rule, true) ? $value : null;
            } elseif ($rule === 'hex_color') {
                // Hex color validation
                return preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : null;
            }
        }
        
        // Boolean validation
        if (in_array($key, [
            'email_notifications', 'in_app_notifications', 'maintenance_alerts',
            'reminder_notifications', 'sound_notifications', 'enable_validation',
            'require_confirmation_delete', 'enable_activity_logging', 'enable_audit_trail',
            'require_admin_approval', 'show_tooltips', 'enable_tutorials',
            'show_help_guides', 'enable_walkthrough_mode', 'enable_auto_suggestions',
            'enable_math_calculations', 'enable_formula_validation',
            'enable_real_time_computation', 'enable_graph_rendering'
        ])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        
        return $value;
    }

    /**
     * Prepare value for database storage
     */
    private function prepareValueForDatabase($key, $value) {
        // Convert booleans to 1/0
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        return $value;
    }
    
    /**
     * Format database result to PHP types
     */
    private function formatSettings($dbRecord) {
        $formatted = [];
        
        foreach ($this->defaultSettings as $key => $default) {
            $value = $dbRecord[$key] ?? $default;
            
            // Convert boolean fields
            if (is_bool($default)) {
                $formatted[$key] = (bool)$value;
            } else {
                $formatted[$key] = $value;
            }
        }
        
        return $formatted;
    }
    
    /**
     * Create default settings for a new user
     */
    private function createDefaultSettings($userId) {
        try {
            $cols = implode(', ', array_keys($this->defaultSettings));
            $placeholders = implode(', ', array_fill(0, count($this->defaultSettings), '?'));
            $values = array_values($this->defaultSettings);
            
            // Convert booleans to 1/0
            $values = array_map(function($v) {
                return is_bool($v) ? ($v ? 1 : 0) : $v;
            }, $values);
            
            array_unshift($values, $userId);
            
            $stmt = $this->db->prepare("
                INSERT INTO user_settings (user_id, $cols)
                VALUES (?, $placeholders)
            ");
            $stmt->execute($values);
            
            $this->log("Default settings created for user $userId");
        } catch (Exception $e) {
            $this->log('Error creating default settings: ' . $e->getMessage());
        }
    }

    /**
     * Log message
     */
    private function log($message) {
        if ($this->logger) {
            $this->logger->log($message);
        } else {
            error_log($message);
        }
    }
}
?>


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
