/**
 * Authentication Handler
 * Manages signup/login flow with tutorial redirect for new users
 */

const AuthHandler = {
  // Dynamically set API base URL based on current environment
  get apiBase() {
    const protocol = window.location.protocol; // http: or https:
    const host = window.location.host; // localhost:8080 or axio-app.onrender.com
    return `${protocol}//${host}/backend/api`;
  },

  /**
   * Handle user signup
   */
  async handleSignup(username, password, firstName, lastName, email) {
    try {
      const response = await fetch(`${this.apiBase}/auth.php?action=register`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          username,
          password,
          first_name: firstName,
          last_name: lastName,
          email
        })
      });

      const result = await response.json();

      if (result.success) {
        console.log('✅ Signup successful', result);
        
        // Store user info in session storage
        sessionStorage.setItem('user_id', result.user_id);
        sessionStorage.setItem('username', result.username);
        sessionStorage.setItem('user_first_name', result.first_name || '');
        sessionStorage.setItem('user_last_name', result.last_name || '');
        
        // If new user, redirect to LaTeX tutorial
        if (result.redirect_to_tutorial) {
          console.log('🎓 Redirecting new user to LaTeX tutorial...');
          this.showRedirectMessage('Welcome! Let\'s learn LaTeX basics...', () => {
            window.location.href = '/frontend/pages/learn-latex.html';
          });
        } else {
          // Existing user flow (shouldn't happen on signup)
          window.location.href = '/frontend/pages/dashboard.html';
        }

        return result;
      } else {
        console.error('❌ Signup failed:', result.message);
        throw new Error(result.message);
      }
    } catch (error) {
      console.error('Signup error:', error);
      throw error;
    }
  },

  /**
   * Handle user login
   */
  async handleLogin(username, password) {
    try {
      const response = await fetch(`${this.apiBase}/auth.php?action=login`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
      });

      const result = await response.json();

      if (result.success) {
        console.log('✅ Login successful', result);
        
        // Store user info
        sessionStorage.setItem('user_id', result.user_id);
        sessionStorage.setItem('username', result.username);
        
        // Check if user needs to complete LaTeX tutorial
        const tutorialComplete = await this.checkTutorialStatus(result.user_id);
        
        if (!tutorialComplete) {
          console.log('📚 User needs to complete LaTeX tutorial');
          this.showRedirectMessage('Complete your LaTeX tutorial to get started!', () => {
            window.location.href = '/frontend/pages/learn-latex.html';
          });
        } else {
          // Tutorial complete - go to dashboard to start proving
          window.location.href = '/frontend/pages/dashboard.html';
        }

        return result;
      } else {
        console.error('❌ Login failed:', result.message);
        throw new Error(result.message);
      }
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  },

  /**
   * Check if user has completed LaTeX tutorial
   */
  async checkTutorialStatus(userId) {
    try {
      const response = await fetch(`${this.apiBase}/user-preferences.php?action=get_latex_tutorial_status`, {
        credentials: 'include'
      });
      
      const result = await response.json();
      return result.success && result.tutorial_completed === true;
    } catch (error) {
      console.warn('Could not check tutorial status:', error);
      return false; // Default to incomplete if can't check
    }
  },

  /**
   * Mark LaTeX tutorial as complete
   */
  async markTutorialComplete() {
    try {
      const response = await fetch(`${this.apiBase}/user-preferences.php?action=complete_latex_tutorial`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ latex_tutorial_completed: true })
      });

      const result = await response.json();
      if (result.success) {
        console.log('✅ Tutorial marked as complete');
        return true;
      }
    } catch (error) {
      console.error('Error marking tutorial complete:', error);
    }
    return false;
  },

  /**
   * Check current session status
   */
  async checkSession() {
    try {
      const response = await fetch(`${this.apiBase}/auth.php?action=check_session`, {
        credentials: 'include'
      });

      const result = await response.json();
      
      if (result.success && result.authenticated) {
        sessionStorage.setItem('user_id', result.user_id);
        sessionStorage.setItem('username', result.username);
        return result;
      }
      return null;
    } catch (error) {
      console.error('Session check error:', error);
      return null;
    }
  },

  /**
   * Handle logout
   */
  async handleLogout() {
    try {
      await fetch(`${this.apiBase}/auth.php?action=logout`, {
        method: 'POST',
        credentials: 'include'
      });

      sessionStorage.clear();
      window.location.href = '/frontend/pages/auth.html';
    } catch (error) {
      console.error('Logout error:', error);
    }
  },

  /**
   * Show redirect message with countdown
   */
  showRedirectMessage(message, callback) {
    const modal = document.createElement('div');
    modal.style.cssText = `
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    `;

    const card = document.createElement('div');
    card.style.cssText = `
      background: white;
      border-radius: 12px;
      padding: 40px;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    `;

    let countdown = 3;
    card.innerHTML = `
      <div style="font-size: 2rem; margin-bottom: 20px;">🎓</div>
      <h2 style="margin-bottom: 16px; color: #0f172a; font-size: 1.3rem;">${message}</h2>
      <p style="color: #64748b; margin-bottom: 20px;">Redirecting in <span id="countdown">${countdown}</span>s...</p>
      <button id="skipCountdown" style="
        background: #059669;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
      ">Skip</button>
    `;

    modal.appendChild(card);
    document.body.appendChild(modal);

    const countdownEl = document.getElementById('countdown');
    const skipBtn = document.getElementById('skipCountdown');

    const interval = setInterval(() => {
      countdown--;
      countdownEl.textContent = countdown;
      
      if (countdown <= 0) {
        clearInterval(interval);
        modal.remove();
        callback();
      }
    }, 1000);

    skipBtn.addEventListener('click', () => {
      clearInterval(interval);
      modal.remove();
      callback();
    });
  },

  /**
   * Validate email format
   */
  isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  },

  /**
   * Check username availability
   */
  async checkUsernameAvailable(username) {
    try {
      const response = await fetch(
        `${this.apiBase}/auth.php?action=check_username&username=${encodeURIComponent(username)}`
      );
      const result = await response.json();
      return result.success && result.available;
    } catch (error) {
      console.error('Error checking username:', error);
      return false;
    }
  }
};

// Export for use in browser
if (typeof module !== 'undefined' && module.exports) {
  module.exports = AuthHandler;
}
