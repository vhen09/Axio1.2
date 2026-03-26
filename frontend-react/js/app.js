document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const pages = document.querySelectorAll('.page');

    if (!sidebarLinks.length || !pages.length) {
        return;
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const targetPage = this.getAttribute('data-target');

            pages.forEach(page => {
                page.style.display = 'none';
            });

            document.getElementById(targetPage).style.display = 'block';
        });
    });

    // Initialize the dashboard page as the default view
    const dashboard = document.getElementById('dashboard');
    if (dashboard) {
        dashboard.style.display = 'block';
    }
});