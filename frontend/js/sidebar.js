document.addEventListener('DOMContentLoaded', async () => {
    // Note: Auth is already verified by landing.html before routing here
    // No need to re-check authentication
    applyThemeFromPreferences();
    ensureGlobalBackground();

    const sidebar = document.querySelector('.sidebar') || document.querySelector('#sidebar');
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-item, #sidebar nav a');

    if (navLinks.length > 0) {
        const current = window.location.pathname.split('/').pop() || 'dashboard.html';
        navLinks.forEach((link) => {
            const target = (link.getAttribute('href') || '').split('/').pop();
            if (target && target === current) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    initSidebarToggle(sidebar);
    injectLogoutNav();
});

function applyThemeFromPreferences() {
    const prefs = getPreferences();
    const isDark = prefs.darkTheme === true;
    document.documentElement.classList.toggle('theme-dark', isDark);
    document.body.classList.toggle('theme-dark', isDark);
}

function getPreferences() {
    try {
        return JSON.parse(localStorage.getItem('reana_preferences') || '{}');
    } catch (error) {
        return {};
    }
}

window.setThemePreference = function setThemePreference(isDark) {
    const prefs = getPreferences();
    prefs.darkTheme = Boolean(isDark);
    localStorage.setItem('reana_preferences', JSON.stringify(prefs));
    applyThemeFromPreferences();
};

function ensureGlobalBackground() {
    if (!document.querySelector('.math-background')) {
        const background = document.createElement('div');
        background.className = 'math-background';
        background.innerHTML = `
            <span class="math-symbol">∫</span>
            <span class="math-symbol">∂</span>
            <span class="math-symbol">∑</span>
            <span class="math-symbol">∞</span>
            <span class="math-symbol">λ</span>
            <span class="math-symbol">∀</span>
            <span class="math-symbol">∃</span>
            <span class="math-symbol">∈</span>
            <span class="math-symbol">→</span>
            <span class="math-symbol">⇒</span>
            <span class="math-symbol">⇔</span>
            <span class="math-symbol">√</span>
            <span class="math-symbol">∆</span>
            <span class="math-symbol">∇</span>
            <span class="math-symbol">∮</span>
        `;
        document.body.prepend(background);
    }

    if (!document.querySelector('.particles')) {
        const particles = document.createElement('div');
        particles.className = 'particles';
        particles.innerHTML = Array.from({ length: 15 }).map(() => '<span class="particle"></span>').join('');
        document.body.prepend(particles);
    }
}

function initSidebarToggle(sidebar) {
    if (!sidebar || document.querySelector('.sidebar-toggle')) {
        return;
    }

    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'sidebar-toggle';
    toggleBtn.setAttribute('aria-label', 'Toggle Sidebar');
    document.body.appendChild(toggleBtn);

    const root = document.body;

    const setCollapsedState = (collapsed) => {
        sidebar.classList.toggle('collapsed', collapsed);
        toggleBtn.classList.toggle('collapsed', collapsed);
        root.classList.toggle('sidebar-collapsed', collapsed);
        root.classList.toggle('sidebar-expanded', !collapsed);
        toggleBtn.innerHTML = collapsed ? '☰' : '✕';
        localStorage.setItem('sidebarCollapsed', String(collapsed));
    };

    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    setCollapsedState(isCollapsed);

    toggleBtn.addEventListener('click', () => {
        const collapsed = !sidebar.classList.contains('collapsed');
        setCollapsedState(collapsed);
    });
}

function injectLogoutNav() {
    const nav = document.querySelector('.sidebar-nav');
    if (!nav) return;

    if (document.getElementById('sidebar-logout-global')) return;

    const logoutLink = document.createElement('a');
    logoutLink.href = '#';
    logoutLink.id = 'sidebar-logout-global';
    logoutLink.className = 'nav-item';
    logoutLink.textContent = ' Logout';
    logoutLink.addEventListener('click', async (event) => {
        event.preventDefault();
        try {
            await fetch('../../backend/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'logout' })
            });
        } catch (_) {
        }

        [
            'axio.workspace.state',
            'axio.proofs',
            'reana_profile',
            'reana_preferences'
        ].forEach((key) => localStorage.removeItem(key));

        window.location.href = 'welcome.html';
    });

    nav.appendChild(logoutLink);
}