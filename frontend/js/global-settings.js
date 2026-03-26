/**
 * Global Settings Manager
 * Manages all AXIO settings globally across all pages
 * Persists to localStorage and applies changes system-wide
 */

class GlobalSettingsManager {
  constructor() {
    this.storageKey = 'axio_settings';
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
      return this.defaults;
    }
  }

  /**
   * Save settings to localStorage
   */
  saveSettings() {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(this.settings));
      this.applySettings();
      return true;
    } catch (e) {
      console.error('Error saving settings:', e);
      return false;
    }
  }

  /**
   * Apply all settings to the document
   */
  applySettings() {
    this.applyTheme(this.settings.theme);
    this.applyFontSize(this.settings.fontSize);
  }

  /**
   * Apply theme to all pages
   */
  applyTheme(theme) {
    const html = document.documentElement;
    const body = document.body;

    if (theme === 'dark') {
      html.classList.add('theme-dark');
      body.classList.add('theme-dark');
      localStorage.setItem('axio_theme', 'dark');
    } else if (theme === 'light') {
      html.classList.remove('theme-dark');
      body.classList.remove('theme-dark');
      localStorage.setItem('axio_theme', 'light');
    } else if (theme === 'auto') {
      // Auto: use system preference
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        html.classList.add('theme-dark');
        body.classList.add('theme-dark');
      } else {
        html.classList.remove('theme-dark');
        body.classList.remove('theme-dark');
      }
      localStorage.setItem('axio_theme', 'auto');
    }

    this.settings.theme = theme;
  }

  /**
   * Apply font size to all pages
   */
  applyFontSize(size) {
    const baseSizes = {
      small: '14px',
      normal: '16px',
      large: '18px'
    };
    
    document.documentElement.style.fontSize = baseSizes[size] || '16px';
    this.settings.fontSize = size;
  }

  /**
   * Update a single setting
   */
  updateSetting(key, value) {
    this.settings[key] = value;
    this.saveSettings();
    
    // Apply immediately for theme and font size
    if (key === 'theme') {
      this.applyTheme(value);
    } else if (key === 'fontSize') {
      this.applyFontSize(value);
    }
  }

  /**
   * Get a setting value
   */
  getSetting(key) {
    return this.settings[key];
  }

  /**
   * Get all settings
   */
  getAllSettings() {
    return { ...this.settings };
  }
}

// Create global instance
let globalSettings = null;

document.addEventListener('DOMContentLoaded', () => {
  if (!globalSettings) {
    globalSettings = new GlobalSettingsManager();
  }
});

// Ensure settings are applied even before DOMContentLoaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    if (!globalSettings) {
      globalSettings = new GlobalSettingsManager();
    }
  });
} else {
  if (!globalSettings) {
    globalSettings = new GlobalSettingsManager();
  }
}
