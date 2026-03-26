// ============================================
// Dark Mode Manager
// Feature 7: Comprehensive Dark Mode System
// ============================================

class DarkModeManager {
    constructor() {
        this.isDarkMode = this.getStoredPreference() ?? this.getSystemPreference();
        this.toggleButton = null;
        this.init();
    }

    /**
     * Initialize dark mode
     */
    init() {
        this.applyTheme();
        this.createToggleButton();
        this.setupEventListeners();
        this.observeSystemPreference();
    }

    /**
     * Apply theme to document
     */
    applyTheme() {
        const html = document.documentElement;
        const body = document.body;

        if (this.isDarkMode) {
            html.classList.add('theme-dark');
            body.classList.add('theme-dark');
            html.style.colorScheme = 'dark';
            this.setMetaThemeColor('#0f172a');
        } else {
            html.classList.remove('theme-dark');
            body.classList.remove('theme-dark');
            html.style.colorScheme = 'light';
            this.setMetaThemeColor('#ffffff');
        }

        localStorage.setItem('theme', this.isDarkMode ? 'dark' : 'light');
    }

    /**
     * Set meta theme color for browser UI
     */
    setMetaThemeColor(color) {
        let metaTheme = document.querySelector('meta[name="theme-color"]');
        if (!metaTheme) {
            metaTheme = document.createElement('meta');
            metaTheme.name = 'theme-color';
            document.head.appendChild(metaTheme);
        }
        metaTheme.content = color;
    }

    /**
     * Create toggle button in topbar
     */
    createToggleButton() {
        const topbar = document.querySelector('.topbar') || 
                      document.querySelector('[data-topbar]');
        
        if (!topbar) return;

        const existingToggle = topbar.querySelector('[data-dark-mode-toggle]');
        if (existingToggle) return;

        const toggleBtn = document.createElement('button');
        toggleBtn.setAttribute('data-dark-mode-toggle', '');
        toggleBtn.className = 'btn dark-mode-toggle';
        toggleBtn.title = this.isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode';
        toggleBtn.innerHTML = this.isDarkMode ? '☀️ Light' : '🌙 Dark';
        
        const actions = topbar.querySelector('.actions');
        if (actions) {
            actions.insertBefore(toggleBtn, actions.firstChild);
        } else {
            topbar.appendChild(toggleBtn);
        }

        this.toggleButton = toggleBtn;
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        if (this.toggleButton) {
            this.toggleButton.addEventListener('click', () => this.toggle());
        }

        // Listen for system preference changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)')
                .addEventListener('change', (e) => {
                    if (!localStorage.getItem('theme')) {
                        this.isDarkMode = e.matches;
                        this.applyTheme();
                    }
                });
        }
    }

    /**
     * Observe system preference changes
     */
    observeSystemPreference() {
        if (window.matchMedia) {
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const handleSystemThemeChange = (e) => {
                // Only auto-apply if user hasn't explicitly set a preference
                if (!localStorage.getItem('theme')) {
                    this.isDarkMode = e.matches;
                    this.applyTheme();
                }
            };

            if (darkModeQuery.addEventListener) {
                darkModeQuery.addEventListener('change', handleSystemThemeChange);
            } else {
                darkModeQuery.addListener(handleSystemThemeChange);
            }
        }
    }

    /**
     * Toggle dark mode
     */
    toggle() {
        this.isDarkMode = !this.isDarkMode;
        this.applyTheme();
        this.updateToggleButton();
        
        // Emit custom event
        window.dispatchEvent(new CustomEvent('dark-mode-toggled', {
            detail: { isDarkMode: this.isDarkMode }
        }));

        console.log(`Dark mode: ${this.isDarkMode ? 'enabled' : 'disabled'}`);
    }

    /**
     * Update toggle button text
     */
    updateToggleButton() {
        if (this.toggleButton) {
            this.toggleButton.title = this.isDarkMode ? 
                'Switch to Light Mode' : 'Switch to Dark Mode';
            this.toggleButton.innerHTML = this.isDarkMode ? 
                '☀️ Light' : '🌙 Dark';
        }
    }

    /**
     * Get stored user preference
     */
    getStoredPreference() {
        const stored = localStorage.getItem('theme');
        if (stored === 'dark') return true;
        if (stored === 'light') return false;
        return null;
    }

    /**
     * Get system preference
     */
    getSystemPreference() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return true;
        }
        return false;
    }

    /**
     * Get current state
     */
    isDark() {
        return this.isDarkMode;
    }

    /**
     * Force light mode
     */
    setLight() {
        this.isDarkMode = false;
        this.applyTheme();
        this.updateToggleButton();
    }

    /**
     * Force dark mode
     */
    setDark() {
        this.isDarkMode = true;
        this.applyTheme();
        this.updateToggleButton();
    }

    /**
     * Reset to system preference
     */
    resetToSystem() {
        localStorage.removeItem('theme');
        this.isDarkMode = this.getSystemPreference();
        this.applyTheme();
        this.updateToggleButton();
    }
}

// Create global instance
window.DarkModeManager = new DarkModeManager();

// Make it accessible globally
window.setThemePreference = (isDark) => {
    if (isDark) window.DarkModeManager.setDark();
    else window.DarkModeManager.setLight();
};
