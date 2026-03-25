/**
 * AXIO Toast Notification System
 * Professional notification system replacing browser alerts
 * Features: Auto-dismiss, stacking, animations, themes
 */

class ToastManager {
  constructor(options = {}) {
    this.toasts = [];
    this.container = null;
    this.maxVisible = options.maxVisible || 5;
    this.defaultDuration = options.defaultDuration || 4000;
    this.position = options.position || 'top-right'; // top-right, top-left, bottom-right, bottom-left
    this.init();
  }

  init() {
    // Create container
    this.container = document.createElement('div');
    this.container.className = 'toast-container';
    this.container.setAttribute('aria-live', 'polite');
    this.container.setAttribute('aria-atomic', 'true');
    this.container.setAttribute('data-position', this.position);
    
    // Add to DOM
    document.body.appendChild(this.container);

    // Apply responsive positioning
    this.updateResponsive();
    window.addEventListener('resize', () => this.updateResponsive());
  }

  updateResponsive() {
    const width = window.innerWidth;
    if (width < 768) {
      this.container.setAttribute('data-mobile', 'true');
    } else {
      this.container.removeAttribute('data-mobile');
    }
  }

  /**
   * Show a success toast
   * @param {string} message - Toast message
   * @param {number} duration - Auto-dismiss time in ms
   */
  success(message, duration = this.defaultDuration) {
    return this.show(message, 'success', duration);
  }

  /**
   * Show an error toast
   * @param {string} message - Toast message
   * @param {number} duration - Auto-dismiss time in ms
   */
  error(message, duration = this.defaultDuration + 2000) { // Errors stay longer
    return this.show(message, 'error', duration);
  }

  /**
   * Show a warning toast
   * @param {string} message - Toast message
   * @param {number} duration - Auto-dismiss time in ms
   */
  warning(message, duration = this.defaultDuration) {
    return this.show(message, 'warning', duration);
  }

  /**
   * Show an info toast
   * @param {string} message - Toast message
   * @param {number} duration - Auto-dismiss time in ms
   */
  info(message, duration = this.defaultDuration) {
    return this.show(message, 'info', duration);
  }

  /**
   * Show a loading toast (no auto-dismiss)
   * @param {string} message - Toast message
   * @returns {Object} Toast object with update() and dismiss() methods
   */
  loading(message) {
    return this.show(message, 'loading', 0); // 0 = no auto-dismiss
  }

  /**
   * Show a custom toast
   * @param {string} message - Toast message
   * @param {string} type - Toast type (success, error, warning, info, loading)
   * @param {number} duration - Auto-dismiss time in ms (0 = no auto-dismiss)
   */
  show(message, type = 'info', duration = this.defaultDuration) {
    // Create toast element
    const toast = document.createElement('div');
    const id = `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    
    toast.setAttribute('role', 'status');
    toast.setAttribute('id', id);
    toast.className = `toast toast-${type}`;
    
    // Build icon
    const icons = {
      success: '✓',
      error: '✕',
      warning: '⚠',
      info: 'ℹ',
      loading: '⟳'
    };
    
    // Build HTML
    const icon = icons[type] || '•';
    toast.innerHTML = `
      <div class="toast-content">
        <span class="toast-icon">${icon}</span>
        <span class="toast-message">${this.escapeHtml(message)}</span>
        <button class="toast-close" aria-label="Close notification">✕</button>
      </div>
    `;

    // Add to container
    this.container.appendChild(toast);

    // Add to list
    const toastObj = {
      id,
      element: toast,
      message,
      type,
      duration,
      timeoutId: null,
      
      // Update message (useful for loading → success transitions)
      update(newMessage, newType = null) {
        toastObj.message = newMessage;
        const msgEl = toast.querySelector('.toast-message');
        if (msgEl) msgEl.textContent = newMessage;
        
        if (newType && newType !== type) {
          toast.classList.remove(`toast-${type}`);
          toast.classList.add(`toast-${newType}`);
          const iconEl = toast.querySelector('.toast-icon');
          if (iconEl) iconEl.textContent = icons[newType] || '•';
        }
        return toastObj;
      },
      
      // Manually dismiss
      dismiss() {
        if (toastObj.timeoutId) clearTimeout(toastObj.timeoutId);
        this.removeToast(toastObj);
      }
    };

    this.toasts.push(toastObj);

    // Truncate if too many
    if (this.toasts.length > this.maxVisible) {
      const oldest = this.toasts.shift();
      oldest.element.remove();
    }

    // Add close handler
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => toastObj.dismiss());

    // Trigger animation
    requestAnimationFrame(() => {
      toast.classList.add('toast-show');
    });

    // Auto-dismiss if duration specified
    if (duration > 0) {
      toastObj.timeoutId = setTimeout(() => {
        this.removeToast(toastObj);
      }, duration);
    }

    return toastObj;
  }

  removeToast(toastObj) {
    const index = this.toasts.indexOf(toastObj);
    if (index > -1) this.toasts.splice(index, 1);

    toastObj.element.classList.remove('toast-show');
    setTimeout(() => {
      if (toastObj.element.parentNode) {
        toastObj.element.remove();
      }
    }, 200); // Wait for animation
  }

  /**
   * Dismiss all toasts
   */
  dismissAll() {
    const toastsCopy = [...this.toasts];
    toastsCopy.forEach(t => this.removeToast(t));
  }

  /**
   * Helper: Escape HTML to prevent XSS
   */
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Global toast instance
let toastManager = null;

/**
 * Initialize toast manager on page load
 */
function initToasts(options = {}) {
  if (!toastManager) {
    toastManager = new ToastManager(options);
  }
  return toastManager;
}

/**
 * Shortcut functions for easy access
 */
function showSuccess(message, duration) {
  if (!toastManager) initToasts();
  return toastManager.success(message, duration);
}

function showError(message, duration) {
  if (!toastManager) initToasts();
  return toastManager.error(message, duration);
}

function showWarning(message, duration) {
  if (!toastManager) initToasts();
  return toastManager.warning(message, duration);
}

function showInfo(message, duration) {
  if (!toastManager) initToasts();
  return toastManager.info(message, duration);
}

function showLoading(message) {
  if (!toastManager) initToasts();
  return toastManager.loading(message);
}

/**
 * Initialize on DOM ready
 */
document.addEventListener('DOMContentLoaded', () => {
  initToasts({ position: 'top-right' });
});

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { ToastManager, initToasts, showSuccess, showError, showWarning, showInfo, showLoading };
}
