/**
 * Unified Logout Utility
 * Handles all logout operations across the application
 * Single source of truth to avoid inconsistencies
 */

class LogoutManager {
  /**
   * Perform complete logout: backend session + client-side cleanup + redirect
   * @param {string} redirectPage - Optional redirect page (default: 'welcome.html')
   * @returns {Promise<void>}
   */
  static async logout(redirectPage = null) {
    try {
      // 1. Clear server-side session
      await this._clearServerSession();
    } catch (error) {
      console.error('Server logout error:', error);
      // Continue with client cleanup even if server fails
    }

    // 2. Clear all client-side storage
    this._clearClientStorage();

    // 3. Redirect to appropriate page
    const targetPage = redirectPage || 'welcome.html';
    
    // Ensure we have a proper path
    if (targetPage.startsWith('http')) {
      window.location.href = targetPage;
    } else if (targetPage.startsWith('/')) {
      window.location.href = targetPage;
    } else {
      // Relative path - detect current location and navigate appropriately
      const currentPath = window.location.pathname;
      const isInPages = currentPath.includes('/pages/');
      const isInJs = currentPath.includes('/js/');
      
      if (isInPages) {
        // Already in pages directory
        window.location.href = targetPage;
      } else {
        // Likely in root or other directory
        window.location.href = `/frontend/pages/${targetPage}`;
      }
    }
  }

  /**
   * Clear server-side session via API
   * @private
   */
  static async _clearServerSession() {
    const apiUrl = this._getApiUrl();
    
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ action: 'logout' }),
      credentials: 'include'
    });

    if (!response.ok) {
      throw new Error(`Server logout failed: ${response.statusText}`);
    }

    return response.json();
  }

  /**
   * Get correct API URL regardless of current page location
   * @private
   */
  static _getApiUrl() {
    const currentPath = window.location.pathname;
    
    // Map of page locations to API paths
    if (currentPath.includes('/pages/')) {
      return '../../backend/api/auth.php';
    } else if (currentPath.includes('/js/')) {
      return '../../backend/api/auth.php';
    } else if (currentPath.includes('/axio2.0/')) {
      return '../../../backend/api/auth.php';
    } else {
      // Default: assume we're at root
      return './backend/api/auth.php';
    }
  }

  /**
   * Clear all client-side storage (localStorage, sessionStorage)
   * @private
   */
  static _clearClientStorage() {
    // LocalStorage keys to clear
    const localStorageKeys = [
      'axio.workspace.state',
      'axio.proofs',
      'axio-workspace-state',
      'axio-toolbar-state',
      'reana_profile',
      'reana_preferences',
      'reana_proofs',
      'reana_submissions',
      'theorem_id',
      'current_theorem',
      'user_data',
      'auth_token'
    ];

    localStorageKeys.forEach(key => {
      try {
        localStorage.removeItem(key);
      } catch (e) {
        console.warn(`Could not clear localStorage key: ${key}`, e);
      }
    });

    // SessionStorage keys to clear
    const sessionStorageKeys = [
      'current_user_id',
      'current_user',
      'user_id',
      'username',
      'new_user',
      'latex_skill_done',
      'tutorial_completed',
      'session_id',
      'auth_token',
      'just_logged_out'
    ];

    sessionStorageKeys.forEach(key => {
      try {
        sessionStorage.removeItem(key);
      } catch (e) {
        console.warn(`Could not clear sessionStorage key: ${key}`, e);
      }
    });

    // Set logout flag for welcome page
    try {
      sessionStorage.setItem('just_logged_out', 'true');
    } catch (e) {
      console.warn('Could not set logout flag', e);
    }
  }

  /**
   * Quick logout without redirecting (for testing/debugging)
   * @returns {Promise<Object>} - Server response
   */
  static async logoutQuiet() {
    try {
      return await this._clearServerSession();
    } catch (error) {
      console.error('Quiet logout error:', error);
      this._clearClientStorage();
      return { success: false, error: error.message };
    }
  }

  /**
   * Check if user is logged in
   * @returns {boolean}
   */
  static isLoggedIn() {
    return (
      sessionStorage.getItem('current_user_id') ||
      sessionStorage.getItem('user_id') ||
      localStorage.getItem('reana_profile')
    );
  }

  /**
   * Get current user from storage
   * @returns {Object|null}
   */
  static getCurrentUser() {
    const userId = sessionStorage.getItem('current_user_id');
    const username = sessionStorage.getItem('username');
    
    if (userId || username) {
      return {
        id: userId,
        username: username
      };
    }

    try {
      const profile = localStorage.getItem('reana_profile');
      return profile ? JSON.parse(profile) : null;
    } catch (e) {
      return null;
    }
  }
}

// Export for both module and global scope
if (typeof module !== 'undefined' && module.exports) {
  module.exports = LogoutManager;
}
