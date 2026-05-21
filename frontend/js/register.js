auth.renderNavbar();
        const alertBox = document.getElementById('alert');
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            alertBox.innerHTML = '';
            try {
                await api.post('/auth/register', {
                    name:     document.getElementById('name').value,
                    email:    document.getElementById('email').value,
                    password: document.getElementById('password').value,
                }, { auth: false });
                alertBox.innerHTML = '<div class="alert success">Account created. You can now log in.</div>';
                setTimeout(() => window.location.href = 'login.html', 900);
            } catch (err) {
                alertBox.innerHTML = `<div class="alert error">${err.message}</div>`;
            }
        });