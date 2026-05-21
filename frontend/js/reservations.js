const user = auth.requireAuth();
        if (user) {
            auth.renderNavbar();

            async function loadTables() {
                try {
                    const r = await api.get('/tables', { auth: false });
                    const sel = document.getElementById('table_id');
                    (r.data || []).filter(t => t.status === 'available').forEach(t => {
                        sel.insertAdjacentHTML('beforeend', `<option value="${t.id}">Table #${t.number} (${t.capacity} seats)</option>`);
                    });
                } catch (e) { /* ignore */ }
            }

            async function loadReservations() {
                const el = document.getElementById('reservations');
                try {
                    const r = await api.get('/reservations');
                    const rows = r.data || [];
                    if (!rows.length) { el.innerHTML = '<p class="muted">No reservations yet.</p>'; return; }
                    el.innerHTML = `
                        <table class="table">
                            <thead><tr><th>Date</th><th>Time</th><th>People</th><th>Table</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                ${rows.map(r => `
                                    <tr>
                                        <td>${r.date}</td>
                                        <td>${r.time}</td>
                                        <td>${r.number_of_people}</td>
                                        <td>${r.table_id || '-'}</td>
                                        <td><span class="badge ${r.status}">${r.status}</span></td>
                                        <td><button class="btn btn-sm btn-danger" data-cancel="${r.id}">Cancel</button></td>
                                    </tr>`).join('')}
                            </tbody>
                        </table>`;
                    el.querySelectorAll('[data-cancel]').forEach(b => b.addEventListener('click', async () => {
                        if (!confirm('Cancel this reservation?')) return;
                        await api.del('/reservations/' + b.dataset.cancel);
                        loadReservations();
                    }));
                } catch (err) { el.innerHTML = `<div class="alert error">${err.message}</div>`; }
            }

            document.getElementById('reservation-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const alertBox = document.getElementById('alert');
                alertBox.innerHTML = '';
                try {
                    await api.post('/reservations', {
                        date:             document.getElementById('date').value,
                        time:             document.getElementById('time').value,
                        number_of_people: Number(document.getElementById('people').value),
                        table_id:         document.getElementById('table_id').value || null,
                    });
                    alertBox.innerHTML = '<div class="alert success">Reservation created!</div>';
                    e.target.reset();
                    loadReservations();
                } catch (err) {
                    alertBox.innerHTML = `<div class="alert error">${err.message}</div>`;
                }
            });

            loadTables();
            loadReservations();
        }