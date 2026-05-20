// Admin panel logic. Requires the signed-in user to have role=admin.
(function () {
    const user = auth.requireAuth('admin');
    if (!user) return;
    auth.renderNavbar();

    // ---------- Tabs ----------
    document.querySelectorAll('#tabs .tab').forEach(t => t.addEventListener('click', () => {
        document.querySelectorAll('#tabs .tab').forEach(x => x.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(x => x.classList.remove('active'));
        t.classList.add('active');
        document.getElementById('tab-' + t.dataset.tab).classList.add('active');
    }));

    const esc = s => String(s ?? '').replace(/</g, '&lt;');

    function renderTable(container, rows, columns, actions) {
        const el = document.getElementById(container);
        if (!rows || !rows.length) { el.innerHTML = '<p class="muted">No records.</p>'; return; }
        el.innerHTML = `
            <table class="table">
                <thead><tr>${columns.map(c => `<th>${c.label}</th>`).join('')}<th></th></tr></thead>
                <tbody>
                    ${rows.map((r, i) => `
                        <tr>
                            ${columns.map(c => `<td>${c.render ? c.render(r) : esc(r[c.key])}</td>`).join('')}
                            <td>${actions.map(a => `<button class="btn btn-sm ${a.class || ''}" data-action="${a.name}" data-idx="${i}">${a.label}</button>`).join(' ')}</td>
                        </tr>`).join('')}
                </tbody>
            </table>`;
        el.querySelectorAll('button[data-action]').forEach(b =>
            b.addEventListener('click', () => {
                const action = actions.find(a => a.name === b.dataset.action);
                action.handler(rows[Number(b.dataset.idx)]);
            }));
    }

    // ---------- Plats ----------
    async function loadPlats() {
        const [plats, cats] = await Promise.all([
            api.get('/plats').then(r => r.data),
            api.get('/categories').then(r => r.data),
        ]);
        const sel = document.getElementById('p-category');
        sel.innerHTML = '<option value="">-- none --</option>' + cats.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');

        renderTable('plats-list', plats, [
            { key: 'id', label: 'ID' },
            { key: 'name', label: 'Name' },
            { key: 'category_name', label: 'Category' },
            { key: 'price', label: 'Price', render: r => Number(r.price).toFixed(2) + ' €' },
        ], [
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete dish?')) return;
                await api.del('/plats/' + r.id);
                loadPlats();
            } },
        ]);
    }

    document.getElementById('plat-form').addEventListener('submit', async e => {
        e.preventDefault();
        await api.post('/plats', {
            name:        document.getElementById('p-name').value,
            description: document.getElementById('p-desc').value,
            price:       Number(document.getElementById('p-price').value),
            category_id: document.getElementById('p-category').value || null,
        });
        e.target.reset();
        loadPlats();
    });

    // ---------- Categories ----------
    async function loadCats() {
        const cats = (await api.get('/categories')).data;
        renderTable('cats-list', cats, [
            { key: 'id', label: 'ID' },
            { key: 'name', label: 'Name' },
        ], [
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete category?')) return;
                await api.del('/categories/' + r.id);
                loadCats();
            } },
        ]);
    }
    document.getElementById('cat-form').addEventListener('submit', async e => {
        e.preventDefault();
        await api.post('/categories', { name: document.getElementById('c-name').value });
        e.target.reset();
        loadCats();
        loadPlats();
    });

    // ---------- Tables ----------
    async function loadTables() {
        const rows = (await api.get('/tables')).data;
        renderTable('tables-list', rows, [
            { key: 'number', label: 'Number' },
            { key: 'capacity', label: 'Capacity' },
            { key: 'status', label: 'Status', render: r => `<span class="badge ${r.status}">${r.status}</span>` },
        ], [
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete table?')) return;
                await api.del('/tables/' + r.id);
                loadTables();
            } },
        ]);
    }
    document.getElementById('table-form').addEventListener('submit', async e => {
        e.preventDefault();
        await api.post('/tables', {
            number:   Number(document.getElementById('t-number').value),
            capacity: Number(document.getElementById('t-capacity').value),
            status:   document.getElementById('t-status').value,
        });
        e.target.reset();
        loadTables();
    });

    // ---------- Users ----------
    async function loadUsers() {
    const rows = (await api.get('/users')).data;
    renderTable('users-list', rows, [
        { key: 'id', label: 'ID' },
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'role', label: 'Role' },
    ], [
        { 
            name: 'cycle-role', 
            label: 'Change Role', 
            class: 'btn-info',
            handler: async r => {
                // Define the rotation sequence
                const roleRotation = {
                    'client': 'employee',
                    'employee': 'chef',
                    'chef': 'admin',
                    'admin': 'client'
                };
                
                // Fallback to client if the current role is undefined/unexpected
                const nextRole = roleRotation[r.role] || 'client'; 
                
                await api.put('/users/' + r.id, { role: nextRole });
                loadUsers();
            } 
        },
        { 
            name: 'del', 
            label: 'Delete', 
            class: 'btn-danger', 
            handler: async r => {
                if (!confirm('Delete user?')) return;
                await api.del('/users/' + r.id);
                loadUsers();
            } 
        },
    ]);
}

    // ---------- Orders ----------
    async function loadOrders() {
        const rows = (await api.get('/commandes')).data;
        renderTable('orders-list', rows, [
            { key: 'id', label: 'ID' },
            { key: 'customer_name', label: 'Customer' },
            { key: 'customer_email', label: 'Email' },
            { key: 'date', label: 'Date' },
            { key: 'status', label: 'Status', render: r => `<span class="badge ${r.status}">${r.status}</span>` },
        ], [
            { name: 'served', label: 'Mark served', handler: async r => { await api.put('/commandes/' + r.id, { status: 'served' }); loadOrders(); } },
            { name: 'paid', label: 'Mark paid', handler: async r => { await api.put('/commandes/' + r.id, { status: 'paid' }); loadOrders(); } },
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete order?')) return;
                await api.del('/commandes/' + r.id);
                loadOrders();
            } },
        ]);
    }

    // ---------- Reservations ----------
    async function loadReservations() {
        const rows = (await api.get('/reservations')).data;
        renderTable('reservations-list', rows, [
            { key: 'id', label: 'ID' },
            { key: 'customer_name', label: 'Customer' },
            { key: 'customer_email', label: 'Email' },
            { key: 'date', label: 'Date' },
            { key: 'time', label: 'Time' },
            { key: 'number_of_people', label: 'People' },
            { key: 'status', label: 'Status', render: r => `<span class="badge ${esc(r.status)}">${esc(r.status)}</span>` },
        ], [
            { name: 'confirm', label: 'Confirm', handler: async r => { await api.put('/reservations/' + r.id, { status: 'confirmed' }); loadReservations(); } },
            { name: 'cancel', label: 'Cancel', class: 'btn-danger', handler: async r => { await api.put('/reservations/' + r.id, { status: 'cancelled' }); loadReservations(); } },
        ]);
    }

    // ---------- Payments ----------
    async function loadPayments() {

    const rows = (await api.get('/payments')).data;
    renderTable('payments-list', rows, [
        { key: 'id', label: 'ID' },
        { key: 'commande_id', label: 'Order' },
        // Modified this line: It will now display the custom error message in the table row if data is corrupt
        { 
            key: 'total', 
            label: 'Total', 
            render: r => {
                const amount = Number(r.total);
                return amount < 0 ? '<span class="text-danger">Invalid Amount</span>' : amount.toFixed(2) + ' €';
            } 
        },
        { key: 'method', label: 'Method' },
        { key: 'status', label: 'Status', render: r => `<span class="badge ${esc(r.status)}">${esc(r.status)}</span>` },
    ], [
        { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
            if (!confirm('Delete payment?')) return;
            await api.del('/payments/' + r.id);
            loadPayments();
        } },
    ]);
}

document.getElementById('pay-form').addEventListener('submit', async e => {
    e.preventDefault();
    
    try {
        // 1. Send the data to your PHP controller
        const response = await api.post('/payments', {

            commande_id: Number(document.getElementById('pay-commande').value),
            total:       Number(document.getElementById('pay-total').value),
            method:      document.getElementById('pay-method').value,
            status:      document.getElementById('pay-status').value,
        });

        // 2. If it succeeds, clear form and reload list
        e.target.reset();
        loadPayments();

    } catch (error) {
        // 3. Catch the 422 validation error sent by PHP
        console.error("Payment failed:", error);
        
        // This digs into the exact structure sent back by Response::error()
        const errorData = error.response?.data; 
        
        if (errorData && errorData.errors && errorData.errors.total) {
            // Show the exact "Payment total cannot be below zero." message from PHP
            alert(errorData.errors.total[0]); 
        } else if (errorData && errorData.message) {
            alert(errorData.message);
        } else {
            alert("Total cannot be below zero.");
        }
    }
});
    // ---------- Contacts ----------
    async function loadContacts() {
        const rows = (await api.get('/contacts')).data;
        renderTable('contacts-list', rows, [
            { key: 'id', label: 'ID' },
            { key: 'date', label: 'Date' },
            { key: 'message', label: 'Message' },
        ], [
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete message?')) return;
                await api.del('/contacts/' + r.id);
                loadContacts();
            } },
        ]);
    }
  

    // ---------- Reclamations ----------
    async function loadReclamations() {
        const rows = (await api.get('/reclamations')).data;
        renderTable('reclamations-list', rows, [
            { key: 'id', label: 'ID' },
            { key: 'user_id', label: 'User' },
            { key: 'content', label: 'Content' },
            { key: 'status', label: 'Status', render: r => `<span class="badge ${esc(r.status)}">${esc(r.status)}</span>` },
        ], [
            { name: 'close', label: 'Close', handler: async r => { await api.put('/reclamations/' + r.id, { status: 'closed' }); loadReclamations(); } },
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete?')) return;
                await api.del('/reclamations/' + r.id);
                loadReclamations();
            } },
        ]);
    }

    // ---------- Staff Management ----------
    async function loadStaff() {
        const rows = (await api.get('/staff')).data;
        renderTable('staff-list', rows, [
            { key: 'id', label: 'ID' },
            { key: 'name', label: 'Name' },
            { key: 'email', label: 'Email' },
            { key: 'role', label: 'Role', render: r => `<span class="badge">${esc(r.role)}</span>` },
        ], [
            { name: 'toggle', label: 'Toggle Role', handler: async r => {
                await api.put('/staff/' + r.id, { role: r.role === 'chef' ? 'employee' : 'chef' });
                loadStaff(); loadChefSelects(); loadEmpSelects();
            } },
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete staff member?')) return;
                await api.del('/staff/' + r.id);
                loadStaff();
            } },
        ]);
    }
    document.getElementById('staff-form').addEventListener('submit', async e => {
        e.preventDefault();
        await api.post('/staff', {
            name:     document.getElementById('s-name').value,
            email:    document.getElementById('s-email').value,
            password: document.getElementById('s-pass').value,
            role:     document.getElementById('s-role').value,
        });
        e.target.reset();
        loadStaff();
        loadChefSelects();
        loadEmpSelects();
    });

    // ---------- Chef Schedules ----------
    async function loadChefSelects() {
        const staff = (await api.get('/staff')).data || [];
        const chefs = staff.filter(s => s.role === 'chef');
        document.getElementById('cs-chef').innerHTML =
            '<option value="">-- select chef --</option>' +
            chefs.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
    }
    async function loadChefSchedules() {
        const rows = (await api.get('/chef-schedules')).data;
        renderTable('cs-list', rows, [
            { key: 'id', label: 'ID' },
            { key: 'chef_name', label: 'Chef' },
            { key: 'working_date', label: 'Date' },
            { key: 'shift_start', label: 'Start' },
            { key: 'shift_end', label: 'End' },
        ], [
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete schedule?')) return;
                await api.del('/chef-schedules/' + r.id);
                loadChefSchedules();
            } },
        ]);
    }
    document.getElementById('cs-form').addEventListener('submit', async e => {
        e.preventDefault();
        await api.post('/chef-schedules', {
            chef_id:      Number(document.getElementById('cs-chef').value),
            working_date: document.getElementById('cs-date').value,
            shift_start:  document.getElementById('cs-start').value,
            shift_end:    document.getElementById('cs-end').value,
        });
        e.target.reset();
        loadChefSchedules();
    });

    // ---------- Employee Schedules ----------
    async function loadEmpSelects() {
        const staff = (await api.get('/staff')).data || [];
        const emps = staff.filter(s => s.role === 'employee');
        document.getElementById('es-emp').innerHTML =
            '<option value="">-- select employee --</option>' +
            emps.map(e => `<option value="${e.id}">${esc(e.name)}</option>`).join('');
    }
    async function loadEmpSchedules() {
        const rows = (await api.get('/employee-schedules')).data;
        renderTable('es-list', rows, [
            { key: 'id', label: 'ID' },
            { key: 'employee_name', label: 'Employee' },
            { key: 'working_date', label: 'Date' },
            { key: 'shift_start', label: 'Start' },
            { key: 'shift_end', label: 'End' },
            { key: 'role_task', label: 'Task' },
        ], [
            { name: 'del', label: 'Delete', class: 'btn-danger', handler: async r => {
                if (!confirm('Delete schedule?')) return;
                await api.del('/employee-schedules/' + r.id);
                loadEmpSchedules();
            } },
        ]);
    }
    document.getElementById('es-form').addEventListener('submit', async e => {
        e.preventDefault();
        await api.post('/employee-schedules', {
            employee_id:  Number(document.getElementById('es-emp').value),
            working_date: document.getElementById('es-date').value,
            shift_start:  document.getElementById('es-start').value,
            shift_end:    document.getElementById('es-end').value,
            role_task:    document.getElementById('es-task').value || null,
        });
        e.target.reset();
        loadEmpSchedules();
    });

    // ---------- Leave Requests ----------
    async function loadLeaveRequests() {
        const rows = (await api.get('/leave-requests')).data;
        renderTable('lr-list', rows, [
            { key: 'id', label: 'ID' },
            { key: 'staff_name', label: 'Staff' },
            { key: 'staff_role', label: 'Role' },
            { key: 'start_date', label: 'Start' },
            { key: 'end_date', label: 'End' },
            { key: 'reason', label: 'Reason' },
            { key: 'status', label: 'Status', render: r => `<span class="badge ${esc(r.status)}">${esc(r.status)}</span>` },
        ], [
            { name: 'approve', label: 'Approve', handler: async r => {
                await api.put('/leave-requests/' + r.id, { status: 'approved' });
                loadLeaveRequests();
            } },
            { name: 'reject', label: 'Reject', class: 'btn-danger', handler: async r => {
                await api.put('/leave-requests/' + r.id, { status: 'rejected' });
                loadLeaveRequests();
            } },
        ]);
    }

    // ---------- Updated Orders (with customer details) ----------

    // ---------- Updated Reservations (with customer details) ----------

    // initial load
    loadPlats();
    loadCats();
    loadTables();
    loadUsers();
    loadOrders();
    loadReservations();
    loadPayments();
    loadContacts();
    loadStaff();
    loadChefSelects();
    loadEmpSelects();
    loadChefSchedules();
    loadEmpSchedules();
    loadLeaveRequests();
})();
