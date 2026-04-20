// Shared auth helpers and navbar renderer.
(function () {
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
            window.location.href = 'dashboard.html';
            return null;
        }
        return user;
    }

    function renderNavbar({ active = '' } = {}) {
        const user = api.user.get();
        const el = document.getElementById('navbar');
        if (!el) return;

        const isAdmin = user && user.role === 'admin';
        const links = [];

        if (user) {
            links.push(['dashboard.html',    'Dashboard']);
            links.push(['menu.html',         'Menu']);
            links.push(['reservations.html', 'Reservations']);
            links.push(['orders.html',       'Orders']);
            links.push(['reviews.html',      'Reviews']);
            if (isAdmin) links.push(['admin.html', 'Admin Panel']);
        } else {
            links.push(['index.html',        'Home']);
            links.push(['menu.html',         'Menu']);
            links.push(['reviews.html',      'Reviews']);
            links.push(['contact.html',      'Contact']);
        }

        el.innerHTML = `
            <a class="brand" href="${user ? 'dashboard.html' : 'index.html'}">🍽 Le Gourmet</a>
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

    window.auth = { logout, requireAuth, renderNavbar };
})();
