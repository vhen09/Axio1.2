/**
 * Global Settings Manager - Enhanced with Backend Sync
 * Manages all AXIO settings with dual persistence (localStorage + database)
 * Automatically syncs with backend for data persistence
 */

class GlobalSettingsManager {
  constructor() {
    this.storageKey = 'axio_settings';
    this.syncInProgress = false;
    this.syncDelay = 1000; // ms
    this.syncTimer = null;
    this.apiUrl = '../../backend/api/settings.php';
    
    this.defaults = {
      theme: 'light',
      fontSize: 'normal',
      proofMode: 'step-by-step',
      autoSave: true,
      showPreview: true,
      difficultyLevel: 'beginner',
      autoTutorial: false,
      showHints: true,
      learningLanguage: 'en',
      scoreNotifications: true,
      feedbackNotifications: true,
      soundNotifications: false,
      preferredDomains: ['algebra', 'calculus']
    };
    
    this.settings = this.loadSettings();
    this.applySettings();
    
    // Auto-sync on init if user is authenticated
    this.initializeSync();
  }

  /**
   * Initialize sync with backend
   */
  async initializeSync() {
    try {
      // Get defaults from backend to ensure consistency
      const response = await fetch(this.apiUrl + '?action=get', {
        method: 'GET',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.settings) {
          // Merge backend settings with local
          this.settings = { ...this.defaults, ...data.settings };
          this.saveSettings();
          this.applySettings();
          console.log('✓ Settings synced from backend');
        }
      }
    } catch (e) {
      console.log('Backend sync not available - using localStorage');
    }
  }

  /**
   * Load settings from localStorage
   */
  loadSettings() {
    try {
      const saved = localStorage.getItem(this.storageKey);
      return saved ? { ...this.defaults, ...JSON.parse(saved) } : this.defaults;
    } catch (e) {
      console.error('Error loading settings:', e);
      return { ...this.defaults };
    }
  }

  /**
   * Save settings to localStorage
   */
  saveSettings() {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(this.settings));
      this.applySettings();
      
      // Schedule backend sync
      this.scheduleSyncWithBackend();
      
      return true;
    } catch (e) {
      console.error('Error saving settings:', e);
      return false;
    }
  }

  /**
   * Schedule backend sync with debouncing
   */
  scheduleSyncWithBackend(immediate = false) {
    if (this.syncTimer) {
      clearTimeout(this.syncTimer);
    }

    if (immediate) {
      this.syncWithBackend();
    } else {
      this.syncTimer = setTimeout(() => {
        this.syncWithBackend();
      }, this.syncDelay);
    }
  }

  /**
   * Sync settings with backend API
   */
  async syncWithBackend() {
    if (this.syncInProgress) return;

    this.syncInProgress = true;
    try {
      const response = await fetch(this.apiUrl + '?action=update_batch', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ settings: this.settings })
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          console.log('✓ Settings synced to backend');
        } else {
          console.warn('Settings sync failed:', data.error);
        }
      } else if (response.status === 401) {
        console.log('User not authenticated - settings stored locally only');
      }
    } catch (e) {
      console.warn('Could not sync settings to backend:', e.message);
      // Settings remain in localStorage as fallback
    } finally {
      this.syncInProgress = false;
    }
  }

  /**
   * Update a single setting
   */
  updateSetting(key, value) {
    if (!this.defaults.hasOwnProperty(key)) {
      console.warn('Unknown setting:', key);
      return false;
    }

    this.settings[key] = value;
    this.saveSettings();
    
    // Also sync this individual setting to backend
    this.syncSettingToBackend(key, value);
    
    return true;
  }

  /**
   * Sync individual setting to backend
   */
  async syncSettingToBackend(key, value) {
    try {
      const response = await fetch(this.apiUrl + '?action=update', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key, value })
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          console.log(`✓ ${key} synced to backend`);
        }
      }
    } catch (e) {
      console.warn(`Could not sync ${key} to backend:`, e.message);
    }
  }

  /**
   * Update multiple settings
   */
  updateSettings(updates) {
    for (const [key, value] of Object.entries(updates)) {
      if (this.defaults.hasOwnProperty(key)) {
        this.settings[key] = value;
      }
    }
    this.saveSettings();
    return true;
  }

  /**
   * Apply settings to DOM and system
   */
  applySettings() {
    // Apply theme
    if (this.settings.theme === 'dark') {
      document.documentElement.classList.add('theme-dark');
      document.body.classList.add('theme-dark');
    } else {
      document.documentElement.classList.remove('theme-dark');
      document.body.classList.remove('theme-dark');
    }

    // Apply font size
    const root = document.documentElement;
    const fontSizeMap = {
      'small': '14px',
      'normal': '16px',
      'large': '18px'
    };
    root.style.fontSize = fontSizeMap[this.settings.fontSize] || '16px';

    // Dispatch custom event for other listeners
    window.dispatchEvent(new CustomEvent('settingsChanged', {
      detail: this.settings
    }));
  }

  /**
   * Get all settings
   */
  getAllSettings() {
    return { ...this.settings };
  }

  /**
   * Get single setting
   */
  getSetting(key) {
    return this.settings[key] !== undefined ? this.settings[key] : this.defaults[key];
  }

  /**
   * Reset to defaults
   */
  async resetToDefaults() {
    this.settings = { ...this.defaults };
    this.saveSettings();

    // Also reset on backend
    try {
      await fetch(this.apiUrl + '?action=reset', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ confirm: true })
      });
    } catch (e) {
      console.warn('Could not reset settings on backend:', e.message);
    }

    return true;
  }

  /**
   * Get defaults
   */
  getDefaults() {
    return { ...this.defaults };
  }

  /**
   * Export settings as JSON (for backup)
   */
  exportSettings() {
    return {
      timestamp: new Date().toISOString(),
      settings: { ...this.settings }
    };
  }

  /**
   * Import settings from JSON
   */
  importSettings(data) {
    if (!data || !data.settings || typeof data.settings !== 'object') {
      console.error('Invalid import data');
      return false;
    }

    this.updateSettings(data.settings);
    this.scheduleSyncWithBackend(true);
    return true;
  }
}

// Global instance
let globalSettings = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  if (!globalSettings) {
    globalSettings = new GlobalSettingsManager();
  }
});

// Fallback initialization
if (!globalSettings && document.readyState === 'complete') {
  globalSettings = new GlobalSettingsManager();
}
