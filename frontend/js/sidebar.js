document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar') || document.querySelector('#sidebar');
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-item, #sidebar nav a');

    if (navLinks.length > 0) {
        const current = window.location.pathname.split('/').pop() || 'index.html';
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
});

function initSidebarToggle(sidebar) {
    if (!sidebar || document.querySelector('.sidebar-toggle')) {
        return;
    }

    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'sidebar-toggle';
    toggleBtn.setAttribute('aria-label', 'Toggle Sidebar');
    document.body.appendChild(toggleBtn);

    const setCollapsedState = (collapsed) => {
        sidebar.classList.toggle('collapsed', collapsed);
        toggleBtn.classList.toggle('collapsed', collapsed);
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