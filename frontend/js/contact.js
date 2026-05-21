auth.renderNavbar();
        document.getElementById('contact-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const alertBox = document.getElementById('alert');
            alertBox.innerHTML = '';
            try {
                await api.post('/contact', { message: document.getElementById('message').value }, { auth: false });
                alertBox.innerHTML = '<div class="alert success">Thanks! We\'ll get back to you soon.</div>';
                e.target.reset();
            } catch (err) {
                alertBox.innerHTML = `<div class="alert error">${err.message}</div>`;
            }
        });