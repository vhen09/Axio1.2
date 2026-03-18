<?php
/**
 * UserPreferences Model
 * Manages user onboarding state, tutorial completion, and preferences
 */

class UserPreferences {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * Get or create user preferences
     */
    public function getPreferences($user_id) {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM user_preferences WHERE user_id = ?'
            );
            $stmt->execute([$user_id]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Create default preferences if not exists
            if (!$prefs) {
                $this->createPreferences($user_id);
                return $this->getPreferences($user_id);
            }
            
            return $prefs;
        } catch (PDOException $e) {
            error_log('Error getting preferences: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create default preferences for new user
     */
    public function createPreferences($user_id) {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO user_preferences (user_id) VALUES (?)'
            );
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log('Error creating preferences: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update LaTeX skill level
     * @param $latex_level 'beginner', 'intermediate', or 'advanced'
     */
    public function setLatexSkillLevel($user_id, $latex_level) {
        try {
            $stmt = $this->db->prepare(
                'UPDATE user_preferences SET latex_skill_level = ? WHERE user_id = ?'
            );
            return $stmt->execute([$latex_level, $user_id]);
        } catch (PDOException $e) {
            error_log('Error setting LaTeX skill: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark tutorial as completed
     */
    public function markTutorialCompleted($user_id) {
        try {
            $stmt = $this->db->prepare(
                'UPDATE user_preferences SET tutorial_completed = TRUE WHERE user_id = ?'
            );
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log('Error marking tutorial completed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark tutorial as skipped (user familiar with system)
     */
    public function markTutorialSkipped($user_id) {
        try {
            $stmt = $this->db->prepare(
                'UPDATE user_preferences SET tutorial_skipped = TRUE, tutorial_completed = TRUE WHERE user_id = ?'
            );
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log('Error marking tutorial skipped: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has completed onboarding
     */
    public function isOnboardingComplete($user_id) {
        try {
            $prefs = $this->getPreferences($user_id);
            return $prefs && ($prefs['tutorial_completed'] || $prefs['tutorial_skipped']);
        } catch (Exception $e) {
            error_log('Error checking onboarding: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get onboarding status for user
     */
    public function getOnboardingStatus($user_id) {
        try {
            $prefs = $this->getPreferences($user_id);
            if (!$prefs) {
                return null;
            }

            return [
                'latex_skill_level' => $prefs['latex_skill_level'],
                'tutorial_completed' => (bool)$prefs['tutorial_completed'],
                'tutorial_skipped' => (bool)$prefs['tutorial_skipped'],
                'first_login_completed' => (bool)$prefs['first_login_completed'],
                'onboarding_complete' => (bool)($prefs['tutorial_completed'] || $prefs['tutorial_skipped'])
            ];
        } catch (Exception $e) {
            error_log('Error getting onboarding status: ' . $e->getMessage());
            return null;
        }
    }
}
?>