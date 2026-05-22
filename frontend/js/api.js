// Thin fetch wrapper that attaches the JWT and parses JSON responses.
(function () {
    const TOKEN_KEY = 'rm_token';
    const USER_KEY  = 'rm_user';

    async function request(path, { method = 'GET', body = null, auth = true } = {}) {
        // 1. Detect if the body payload is form file data
        const isFormData = body instanceof FormData;

        // Only set Content-Type to JSON if we are NOT handling a file upload
        const headers = isFormData ? {} : { 'Content-Type': 'application/json' };

        if (auth) {
            const token = localStorage.getItem(TOKEN_KEY);
            if (token) headers['Authorization'] = 'Bearer ' + token;
        }

        const opts = { method, headers };

        // 2. Only stringify if it's regular object data, leave FormData untouched!
        if (body !== null) {
            opts.body = isFormData ? body : JSON.stringify(body);
        }

        const res = await fetch(window.API_BASE + path, opts);
        const text = await res.text();
        let json;
        try { json = text ? JSON.parse(text) : {}; }
        catch (e) { json = { success: false, message: 'Invalid JSON response: ' + text }; }

        if (!res.ok || json.success === false) {
            const err = new Error(json.message || ('Request failed (' + res.status + ')'));
            err.status = res.status;
            err.payload = json;
            throw err;
        }
        return json;
    }

    window.api = {
        get:    (p, opts = {})          => request(p, { ...opts, method: 'GET' }),
        post:   (p, body, opts = {})    => request(p, { ...opts, method: 'POST', body }),
        put:    (p, body, opts = {})    => request(p, { ...opts, method: 'PUT',  body }),
        del:    (p, opts = {})          => request(p, { ...opts, method: 'DELETE' }),

        token: {
            get:  ()    => localStorage.getItem(TOKEN_KEY),
            set:  (t)   => localStorage.setItem(TOKEN_KEY, t),
            clear:()    => localStorage.removeItem(TOKEN_KEY),
        },
        user: {
            get:  ()    => { try { return JSON.parse(localStorage.getItem(USER_KEY) || 'null'); } catch (e) { return null; } },
            set:  (u)   => localStorage.setItem(USER_KEY, JSON.stringify(u)),
            clear:()    => localStorage.removeItem(USER_KEY),
        },
    };
})();