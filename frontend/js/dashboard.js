 const user = auth.requireAuth();
        if (user) {
            auth.renderNavbar();
            document.getElementById('welcome').textContent = `Hello, ${user.name}`;

            Promise.all([
                api.get('/reservations').catch(() => ({ data: [] })),
                api.get('/commandes').catch(() => ({ data: [] })),
            ]).then(([res, cmd]) => {
                const reservations = res.data || [];
                const orders = cmd.data || [];
                document.getElementById('summary').innerHTML = `
                    <div class="card"><h3>Reservations</h3><div class="price">${reservations.length}</div></div>
                    <div class="card"><h3>Orders</h3><div class="price">${orders.length}</div></div>
                    <div class="card"><h3>Role</h3><div class="price">${user.role}</div></div>
                `;

                const recent = [...reservations.slice(0, 3).map(r => ({
                    type: 'Reservation',
                    label: `${r.date} at ${r.time} for ${r.number_of_people} people`,
                    status: r.status,
                })), ...orders.slice(0, 3).map(o => ({
                    type: 'Order',
                    label: `#${o.id} on ${o.date}`,
                    status: o.status,
                }))];

                const act = document.getElementById('activity');
                if (!recent.length) { act.innerHTML = '<p class="muted">Nothing yet. Book a table or place an order!</p>'; return; }
                act.innerHTML = `
                    <table class="table">
                        <thead><tr><th>Type</th><th>Details</th><th>Status</th></tr></thead>
                        <tbody>
                            ${recent.map(r => `
                                <tr>
                                    <td>${r.type}</td>
                                    <td>${r.label}</td>
                                    <td><span class="badge ${r.status}">${r.status}</span></td>
                                </tr>`).join('')}
                        </tbody>
                    </table>
                `;
            });
        }