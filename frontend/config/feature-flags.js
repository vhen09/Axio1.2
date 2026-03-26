/**
 * AXIO Feature Flags & Configuration
 * Manages hybrid frontend system with vanilla JS ↔ React switching
 * 
 * Created: March 26, 2026
 * Last Updated: March 26, 2026
 */

window.AXIO_CONFIG = {
  // ===== ENVIRONMENT =====
  APP_NAME: 'AXIO - Mathematical Proof Assistant',
  VERSION: '2.0.0-hybrid',
  ENV: 'production',
  
  // ===== FRONTEND MODE =====
  // Current frontend: 'vanilla' | 'react' | 'auto'
  // Auto detects from user preference in DB
  FRONTEND_MODE: localStorage.getItem('axio_frontend_mode') || 'vanilla',
  
  // ===== FEATURE FLAGS BY FRONTEND =====
  FEATURES: {
    VANILLA: {
      DARK_MODE: true,
      LATEX_KEYBOARD: true,
      INTERACTIVE_TUTORIAL: true,
      SETTINGS_SYNC: true,
      AI_TUTOR: true,
      PROOF_SUBMISSION: true,
      STEP_BY_STEP: true,
      FULL_PROOF: true,
      SCORING: true,
      LEAN_VERIFICATION: true,
      SYMBOL_LIBRARY: true,
      EXPORT_PDF: true
    },
    REACT: {
      DARK_MODE: true,
      LATEX_KEYBOARD: true,
      INTERACTIVE_TUTORIAL: true,
      SETTINGS_SYNC: true,
      AI_TUTOR: true,
      PROOF_SUBMISSION: true,
      STEP_BY_STEP: true,
      FULL_PROOF: true,
      SCORING: true,
      LEAN_VERIFICATION: true,
      SYMBOL_LIBRARY: true,
      EXPORT_PDF: true
    }
  },
  
  // ===== API CONFIGURATION =====
  API: {
    BASE_URL: '/backend/api',
    ENDPOINTS: {
      AUTH: '/auth.php',
      PROOF: '/proof.php',
      TUTOR: '/tutor.php',
      LEAN: '/lean.php',
      THEOREMS: '/theorems.php',
      SUBMISSIONS: '/submissions.php',
      SCORES: '/scores.php',
      SETTINGS: '/settings.php',
      USER_PREFERENCES: '/user-preferences.php',
      USER_PROFILE: '/user-profile.php',
      SCORING: '/scoring.php',
      FRONTEND_PREFERENCE: '/frontend-preference.php',
      HEALTH: '/health.php',
      STATUS: '/status.php'
    },
    TIMEOUT: 30000, // 30 seconds
    RETRY_ATTEMPTS: 3
  },
  
  // ===== FRONTEND PATHS =====
  FRONTEND_PATHS: {
    VANILLA: '/frontend/pages/',
    REACT: '/axio2.0/lean4-ai-web-app/frontend/',
    HYBRID_ROUTER: '/'
  },
  
  // ===== DATABASE CONFIGURATION =====
  DATABASE: {
    NAME: 'lean4_ai_db',
    TYPE: 'postgresql', // or 'sqlite' as fallback
    TIMEOUT: 5000
  },
  
  // ===== THEME CONFIGURATION =====
  THEME: {
    DEFAULT: 'light',
    STORAGE_KEY: 'axio_theme',
    COLORS: {
      PRIMARY: '#1e3a8a',
      SUCCESS: '#10b981',
      WARNING: '#f59e0b',
      ERROR: '#ef4444',
      INFO: '#3b82f6'
    }
  },
  
  // ===== PROOF CONFIGURATION =====
  PROOF: {
    MAX_STEPS: 100,
    MAX_PROOF_LENGTH: 50000, // characters
    STEP_TIMEOUT: 60000, // 60 seconds
    AUTO_SAVE_INTERVAL: 5000 // 5 seconds
  },
  
  // ===== AI TUTOR CONFIGURATION =====
  AI_TUTOR: {
    ENABLED: true,
    PROVIDER: 'deepseek',
    MODEL: 'deepseek-chat',
    MAX_TOKENS: 2000,
    TEMPERATURE: 0.7,
    TIMEOUT: 30000 // 30 seconds
  },
  
  // ===== LOGGING CONFIGURATION =====
  LOG: {
    LEVEL: 'info', // 'debug' | 'info' | 'warn' | 'error'
    STORAGE_KEY: 'axio_logs',
    MAX_LOGS: 1000,
    BATCH_SIZE: 50
  },
  
  // ===== UI CONFIGURATION =====
  UI: {
    TOAST_DURATION: 3000,
    MODAL_ANIMATION_DURATION: 300,
    SIDEBAR_WIDTH: '280px',
    MOBILE_BREAKPOINT: 768
  },
  
  // ===== HELPER METHODS =====
  
  /**
   * Check if a feature is enabled in current frontend
   * @param {string} feature - Feature name (uppercase)
   * @returns {boolean} True if feature is enabled
   */
  isFeatureEnabled(feature) {
    const mode = this.FRONTEND_MODE.toUpperCase();
    const featureName = feature.toUpperCase();
    return this.FEATURES[mode]?.[featureName] ?? false;
  },
  
  /**
   * Get API endpoint URL
   * @param {string} endpoint - Endpoint key
   * @returns {string} Full endpoint URL
   */
  getApiUrl(endpoint) {
    const endpointPath = this.API.ENDPOINTS[endpoint.toUpperCase()];
    if (!endpointPath) {
      console.warn(`Unknown endpoint: ${endpoint}`);
      return null;
    }
    return this.API.BASE_URL + endpointPath;
  },
  
  /**
   * Switch frontend mode
   * @param {string} mode - 'vanilla' | 'react'
   */
  setFrontendMode(mode) {
    if (['vanilla', 'react'].includes(mode)) {
      localStorage.setItem('axio_frontend_mode', mode);
      this.FRONTEND_MODE = mode;
    } else {
      console.error(`Invalid frontend mode: ${mode}`);
    }
  },
  
  /**
   * Get current frontend display name
   * @returns {string} Display name
   */
  getFrontendDisplayName() {
    return this.FRONTEND_MODE === 'react' ? '⚛️ React' : '📄 Vanilla JS';
  },
  
  /**
   * Log configuration
   */
  logConfig() {
    console.group('🔧 AXIO Configuration');
    console.log(`App: ${this.APP_NAME} v${this.VERSION}`);
    console.log(`Frontend: ${this.getFrontendDisplayName()}`);
    console.log(`Environment: ${this.ENV}`);
    console.log(`API Base: ${this.API.BASE_URL}`);
    console.log(`Theme: ${this.THEME.DEFAULT}`);
    console.table(this.FEATURES[this.FRONTEND_MODE.toUpperCase()]);
    console.groupEnd();
  }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
  // Log configuration in development
  if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    AXIO_CONFIG.logConfig();
  }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
  module.exports = AXIO_CONFIG;
}
