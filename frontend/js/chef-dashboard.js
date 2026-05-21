const user = auth.requireAuth('chef');
        if (user) {
            auth.renderNavbar();
            document.getElementById('welcome').textContent = `Hello, Chef ${user.name}`;

            const esc = s => String(s ?? '').replace(/</g, '&lt;');

            // Tabs
            document.querySelectorAll('#tabs .tab').forEach(t => t.addEventListener('click', () => {
                document.querySelectorAll('#tabs .tab').forEach(x => x.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(x => x.classList.remove('active'));
                t.classList.add('active');
                document.getElementById('tab-' + t.dataset.tab).classList.add('active');
            }));

            // Summary
            Promise.all([
                api.get('/chef-schedules').catch(() => ({ data: [] })),
                api.get('/leave-requests').catch(() => ({ data: [] })),
            ]).then(([sched, leaves]) => {
                const schedules = sched.data || [];
                const leaveReqs = leaves.data || [];
                const pending = leaveReqs.filter(l => l.status === 'pending').length;
                document.getElementById('summary').innerHTML = `
                    <div class="card"><h3>Upcoming Shifts</h3><div class="price">${schedules.length}</div></div>
                    <div class="card"><h3>Leave Requests</h3><div class="price">${leaveReqs.length}</div></div>
                    <div class="card"><h3>Pending Leaves</h3><div class="price">${pending}</div></div>
                `;
            });

            // Schedule
            async function loadSchedule() {
                const rows = (await api.get('/chef-schedules')).data || [];
                const el = document.getElementById('schedule-list');
                if (!rows.length) { el.innerHTML = '<p class="muted">No shifts scheduled.</p>'; return; }
                el.innerHTML = `
                    <table class="table">
                        <thead><tr><th>Date</th><th>Start</th><th>End</th></tr></thead>
                        <tbody>
                            ${rows.map(r => `
                                <tr>
                                    <td>${esc(r.working_date)}</td>
                                    <td>${esc(r.shift_start)}</td>
                                    <td>${esc(r.shift_end)}</td>
                                </tr>`).join('')}
                        </tbody>
                    </table>`;
            }

            // Leave requests
            async function loadLeaves() {
                const rows = (await api.get('/leave-requests')).data || [];
                const el = document.getElementById('leave-list');
                if (!rows.length) { el.innerHTML = '<p class="muted">No leave requests yet.</p>'; return; }
                el.innerHTML = `
                    <table class="table">
                        <thead><tr><th>Start</th><th>End</th><th>Reason</th><th>Status</th></tr></thead>
                        <tbody>
                            ${rows.map(r => `
                                <tr>
                                    <td>${esc(r.start_date)}</td>
                                    <td>${esc(r.end_date)}</td>
                                    <td>${esc(r.reason || '-')}</td>
                                    <td><span class="badge ${r.status}">${r.status}</span></td>
                                </tr>`).join('')}
                        </tbody>
                    </table>`;
            }

            // Leave form
            document.getElementById('leave-form').addEventListener('submit', async e => {
                e.preventDefault();
                const alertBox = document.getElementById('leave-alert');
                try {
                    await api.post('/leave-requests', {
                        start_date: document.getElementById('lr-start').value,
                        end_date:   document.getElementById('lr-end').value,
                        reason:     document.getElementById('lr-reason').value || null,
                    });
                    alertBox.innerHTML = '<div class="alert success">Leave request submitted!</div>';
                    e.target.reset();
                    loadLeaves();
                } catch (err) {
                    alertBox.innerHTML = `<div class="alert error">${err.message}</div>`;
                }
            });

            loadSchedule();
            loadLeaves();
        }