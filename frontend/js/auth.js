// Shared auth helpers and navbar renderer.
(function () {
    function getDashboardUrl(role) {
        switch (role) {
            case 'admin':    return 'admin.html';
            case 'chef':     return 'chef-dashboard.html';
            case 'employee': return 'employee-dashboard.html';
            default:         return 'dashboard.html';
        }
    }

    function logout() {
        api.token.clear();
        api.user.clear();
        window.location.href = 'login.html';
    }

    function requireAuth(role = null) {
        const user = api.user.get();
        if (!user || !api.token.get()) {
            window.location.href = 'login.html';
            return null;
        }
        if (role && user.role !== role) {
            window.location.href = getDashboardUrl(user.role);
            return null;
        }
        return user;
    }

    function renderNavbar({ active = '' } = {}) {
        const user = api.user.get();
        const el = document.getElementById('navbar');
        if (!el) return;

        const links = [];
        const home = user ? getDashboardUrl(user.role) : 'index.html';

        if (user) {
            links.push([getDashboardUrl(user.role), 'Dashboard']);

            if (user.role === 'admin' || user.role === 'client') {
                links.push(['menu.html',         'Menu']);
                links.push(['reservations.html', 'Reservations']);
                links.push(['orders.html',       'Orders']);
                links.push(['reviews.html',      'Reviews']);
            }

            if (user.role === 'admin') {
                links.push(['admin.html', 'Admin Panel']);
            }
        } else {
            links.push(['index.html',        'Home']);
            links.push(['menu.html',         'Menu']);
            links.push(['reviews.html',      'Reviews']);
            links.push(['contact.html',      'Contact']);
        }

        el.innerHTML = `
            <a class="brand" href="${home}">🍽 Le Gourmet</a>
            <nav>
                ${links.map(([href, label]) => `<a href="${href}">${label}</a>`).join('')}
                ${user
                    ? `<span class="user-info">${user.name} (${user.role})</span>
                       <a href="#" id="logout-link">Logout</a>`
                    : `<a href="login.html">Login</a><a href="register.html">Register</a>`}
            </nav>
        `;

        const lo = document.getElementById('logout-link');
        if (lo) lo.addEventListener('click', (e) => { e.preventDefault(); logout(); });
    }

    window.auth = { logout, requireAuth, renderNavbar, getDashboardUrl };
})();
