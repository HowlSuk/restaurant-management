auth.renderNavbar();

        const user = api.user.get();
        let plats = [];
        let categories = [];
        let currentCategory = 'all';
        const cart = new Map(); // plat_id -> { plat, qty }

        async function load() {
            [plats, categories] = await Promise.all([
                api.get('/plats', { auth: false }).then(r => r.data),
                api.get('/categories', { auth: false }).then(r => r.data),
            ]);
            renderFilters();
            renderPlats();
        }

        function renderFilters() {
            const f = document.getElementById('filters');
            const btns = [['all', 'All'], ...categories.map(c => [String(c.id), c.name])];
            f.innerHTML = btns.map(([id, name]) =>
                `<button class="btn ${currentCategory === id ? '' : 'btn-secondary'} btn-sm" data-cat="${id}" style="margin-right:6px;margin-bottom:6px;">${name}</button>`
            ).join('');
            f.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
                currentCategory = b.dataset.cat;
                renderFilters();
                renderPlats();
            }));
        }

        function renderPlats() {
            const el = document.getElementById('plats');
            const filtered = currentCategory === 'all'
                ? plats
                : plats.filter(p => String(p.category_id) === currentCategory);
            if (!filtered.length) { el.innerHTML = '<p class="muted">No dishes in this category.</p>'; return; }
            el.innerHTML = filtered.map(p => `
                <div class="card">
                    <h3>${p.name}</h3>
                    <div class="muted">${p.category_name || ''}</div>
                    <p>${p.description || ''}</p>
                    <div class="price">${Number(p.price).toFixed(2)} €</div>
                    ${user ? `<button class="btn btn-sm mt-2" data-add="${p.id}">Add to order</button>` : ''}
                </div>
            `).join('');
            el.querySelectorAll('[data-add]').forEach(b => b.addEventListener('click', () => {
                const plat = plats.find(x => String(x.id) === b.dataset.add);
                addToCart(plat);
            }));
        }

        function addToCart(plat) {
            const cur = cart.get(plat.id);
            cart.set(plat.id, { plat, qty: cur ? cur.qty + 1 : 1 });
            renderCart();
        }

        function changeQty(platId, delta) {
            const id = Number(platId);
            const entry = cart.get(id);
            if (!entry) return;

            const newQty = entry.qty + delta;

            if (newQty <= 0) {
                cart.delete(id);
            } else {
                cart.set(id, { ...entry, qty: newQty });
            }
            renderCart();
        }

        function renderCart() {
            const section = document.getElementById('cart-section');
            if (!cart.size) { section.classList.add('hidden'); return; }
            section.classList.remove('hidden');

            const body = document.getElementById('cart-body');
            let total = 0;
            body.innerHTML = [...cart.values()].map(({ plat, qty }) => {
                const line = Number(plat.price) * qty;
                total += line;
                return `
                    <tr>
                        <td>${plat.name}</td>
                        <td>
                            <div class="qty-controls">
                                <button type="button" class="btn btn-sm btn-secondary" data-dec="${plat.id}" aria-label="Diminuer">−</button>
                                <span class="qty-value">${qty}</span>
                                <button type="button" class="btn btn-sm btn-secondary" data-inc="${plat.id}" aria-label="Augmenter">+</button>
                            </div>
                        </td>
                        <td>${line.toFixed(2)} €</td>
                        <td><button type="button" class="btn btn-sm btn-danger" data-remove="${plat.id}">Remove</button></td>
                    </tr>`;
            }).join('');
            document.getElementById('cart-total').textContent = total.toFixed(2);

            body.querySelectorAll('[data-remove]').forEach(b => b.addEventListener('click', () => {
                cart.delete(Number(b.dataset.remove));
                renderCart();
            }));

            body.querySelectorAll('[data-inc]').forEach(b => b.addEventListener('click', () => {
                changeQty(b.dataset.inc, 1);
            }));

            body.querySelectorAll('[data-dec]').forEach(b => b.addEventListener('click', () => {
                changeQty(b.dataset.dec, -1);
            }));
        }

        document.getElementById('place-order').addEventListener('click', async () => {
            const alertEl = document.getElementById('order-alert');
            alertEl.innerHTML = '';
            if (!user) { alertEl.innerHTML = '<div class="alert error">Please <a href="login.html">log in</a> to order.</div>'; return; }
            try {
                const items = [...cart.values()].map(({ plat, qty }) => ({ plat_id: plat.id, quantity: qty }));
                const r = await api.post('/commandes', { items });
                alertEl.innerHTML = `<div class="alert success">Order #${r.data.id} placed! See it in <a href="orders.html">Orders</a>.</div>`;
                cart.clear();
                renderCart();
            } catch (err) {
                alertEl.innerHTML = `<div class="alert error">${err.message}</div>`;
            }
        });

        load().catch(err => document.getElementById('plats').innerHTML = `<div class="alert error">${err.message}</div>`);