 auth.renderNavbar();

        const user = api.user.get();
        if (!user) document.getElementById('form-card').innerHTML =
            '<p>Please <a href="login.html">log in</a> to leave a review.</p>';

        async function loadReviews() {
            const el = document.getElementById('reviews');
            try {
                const r = await api.get('/avis', { auth: false });
                const rows = r.data || [];
                if (!rows.length) { el.innerHTML = '<p class="muted">No reviews yet.</p>'; return; }
                el.innerHTML = rows.map(a => `
                    <div class="card">
                        <div class="stars">${'★'.repeat(a.note)}${'☆'.repeat(5 - a.note)}</div>
                        <p>${(a.comment || '').replace(/</g, '&lt;')}</p>
                        <div class="muted">— ${a.user_name} · ${a.created_at}</div>
                    </div>
                `).join('');
            } catch (err) { el.innerHTML = `<div class="alert error">${err.message}</div>`; }
        }

        const form = document.getElementById('review-form');
        if (form) form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const alertBox = document.getElementById('alert');
            alertBox.innerHTML = '';
            if (!user) { alertBox.innerHTML = '<div class="alert error">Please log in.</div>'; return; }
            try {
                await api.post('/avis', {
                    note:    Number(document.getElementById('note').value),
                    comment: document.getElementById('comment').value,
                });
                alertBox.innerHTML = '<div class="alert success">Thanks for your review!</div>';
                e.target.reset();
                loadReviews();
            } catch (err) {
                alertBox.innerHTML = `<div class="alert error">${err.message}</div>`;
            }
        });

        loadReviews();