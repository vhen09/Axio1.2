/**
 * AXIO Frontend Switcher
 * Allows users to switch between Vanilla JS and React frontends
 * 
 * Created: March 26, 2026
 * Features:
 * - Load user's frontend preference from server
 * - UI toggle buttons for switching
 * - Persistent storage and sync with backend
 * - Confirmation before switching
 */

class FrontendSwitcher {
  constructor(options = {}) {
    this.apiEndpoint = '/backend/api/frontend-preference.php';
    this.currentFrontend = localStorage.getItem('axio_frontend_mode') || 'vanilla';
    this.isLoading = false;
    this.options = {
      showNotification: true,
      autoLoad: true,
      ...options
    };
    
    if (this.options.autoLoad) {
      this.init();
    }
  }

  /**
   * Initialize switcher
   */
  init() {
    this.loadPreference();
    this.setupEventListeners();
  }

  /**
   * Load frontend preference from server
   */
  async loadPreference() {
    try {
      const response = await fetch(`${this.apiEndpoint}?action=get`, {
        method: 'GET',
        credentials: 'include'
      });
      
      if (!response.ok) {
        console.warn('Failed to load frontend preference, using local value');
        return;
      }
      
      const data = await response.json();
      
      if (data.success) {
        this.currentFrontend = data.frontend || 'vanilla';
        localStorage.setItem('axio_frontend_mode', this.currentFrontend);
        this.updateUI();
      }
    } catch (error) {
      console.error('Error loading frontend preference:', error);
      // Fall back to localStorage value
    }
  }

  /**
   * Setup event listeners for all frontend select buttons
   */
  setupEventListeners() {
    const buttons = document.querySelectorAll('.frontend-select');
    
    if (buttons.length === 0) {
      console.warn('No frontend select buttons found');
      return;
    }
    
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        const frontend = btn.dataset.frontend;
        if (frontend) {
          this.switchFrontend(frontend);
        }
      });
    });
    
    // Set initial UI state
    this.updateUI();
  }

  /**
   * Update UI to reflect current frontend
   */
  updateUI() {
    const buttons = document.querySelectorAll('.frontend-select');
    buttons.forEach(btn => {
      const isActive = btn.dataset.frontend === this.currentFrontend;
      btn.classList.toggle('active', isActive);
      btn.disabled = isActive;
      btn.setAttribute('aria-pressed', isActive);
    });
    
    // Update info text
    const infoDiv = document.getElementById('frontend-info');
    if (infoDiv) {
      const displayName = this.currentFrontend === 'react' 
        ? '⚛️ React' 
        : '📄 Vanilla JS';
      infoDiv.querySelector('strong').textContent = 
        `Currently using: ${displayName}`;
    }
  }

  /**
   * Switch to specified frontend
   * @param {string} frontend - 'vanilla' or 'react'
   */
  async switchFrontend(frontend) {
    // Validate
    if (!['vanilla', 'react'].includes(frontend)) {
      console.error(`Invalid frontend: ${frontend}`);
      return;
    }

    // Already on this frontend
    if (frontend === this.currentFrontend) {
      return;
    }

    // Prevent multiple simultaneous requests
    if (this.isLoading) {
      console.warn('Switch already in progress');
      return;
    }

    // Show confirmation
    const confirmSwitch = confirm(
      `Switch to ${frontend === 'react' ? '⚛️ React' : '📄 Vanilla JS'} interface?\n\n` +
      `Your data will be preserved. The page will reload.`
    );

    if (!confirmSwitch) {
      return;
    }

    try {
      this.isLoading = true;
      this.disableSwitcher();
      
      // Show loading message
      this.showNotification('Switching interface...', 'info');
      
      // Send request to backend
      const response = await fetch(this.apiEndpoint, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=set&frontend=${encodeURIComponent(frontend)}`
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || 'Failed to update frontend preference');
      }

      if (data.success) {
        // Update local state
        this.currentFrontend = frontend;
        localStorage.setItem('axio_frontend_mode', frontend);
        
        // Show success message
        const displayName = frontend === 'react' ? '⚛️ React' : '📄 Vanilla JS';
        this.showNotification(
          `Switched to ${displayName}. Reloading...`,
          'success'
        );
        
        // Reload after brief delay
        setTimeout(() => {
          window.location.href = `/?frontend=${frontend}`;
        }, 1500);
      } else {
        throw new Error(data.error || 'Unknown error');
      }
    } catch (error) {
      console.error('Frontend switch error:', error);
      this.showNotification(
        `Error: ${error.message}`,
        'error'
      );
      this.isLoading = false;
      this.enableSwitcher();
    }
  }

  /**
   * Disable switcher during transition
   */
  disableSwitcher() {
    const buttons = document.querySelectorAll('.frontend-select');
    buttons.forEach(btn => {
      btn.disabled = true;
      btn.style.opacity = '0.5';
      btn.style.cursor = 'wait';
    });
  }

  /**
   * Enable switcher
   */
  enableSwitcher() {
    const buttons = document.querySelectorAll('.frontend-select');
    buttons.forEach(btn => {
      btn.disabled = false;
      btn.style.opacity = '1';
      btn.style.cursor = 'pointer';
    });
  }

  /**
   * Show notification message
   * @param {string} message
   * @param {string} type - 'info' | 'success' | 'error' | 'warning'
   */
  showNotification(message, type = 'info') {
    if (!this.options.showNotification) {
      return;
    }

    // Try to use existing notification system
    if (typeof showSuccess === 'function' && type === 'success') {
      showSuccess(message);
    } else if (typeof showError === 'function' && type === 'error') {
      showError(message);
    } else if (typeof showNotification === 'function') {
      showNotification(message);
    } else if (typeof showInfo === 'function') {
      showInfo(message);
    } else {
      // Fallback: browser alert
      console.log(`[${type.toUpperCase()}] ${message}`);
    }
  }

  /**
   * Get current frontend name
   * @returns {string}
   */
  getCurrentFrontend() {
    return this.currentFrontend;
  }

  /**
   * Get current frontend display name
   * @returns {string}
   */
  getCurrentFrontendName() {
    return this.currentFrontend === 'react' ? '⚛️ React' : '📄 Vanilla JS';
  }

  /**
   * List available frontends
   * @returns {string[]}
   */
  getAvailableFrontends() {
    return ['vanilla', 'react'];
  }

  /**
   * Check if frontend is available
   * @param {string} frontend
   * @returns {boolean}
   */
  isFrontendAvailable(frontend) {
    return this.getAvailableFrontends().includes(frontend);
  }
}

/**
 * Global initialization
 * Auto-initialize if DOM contains .frontend-select buttons
 */
(function() {
  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFrontendSwitcher);
  } else {
    initFrontendSwitcher();
  }

  function initFrontendSwitcher() {
    const hasButtons = document.querySelector('.frontend-select');
    if (hasButtons) {
      window.frontendSwitcher = new FrontendSwitcher({
        showNotification: true,
        autoLoad: true
      });
    }
  }
})();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
  module.exports = FrontendSwitcher;
}
