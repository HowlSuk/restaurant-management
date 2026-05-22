# Restaurant Management System

A complete full-stack restaurant management app.

- **Backend:** PHP 8 (OOP, MVC), PDO MySQL, JWT auth, JSON REST API.
- **Frontend:** Vanilla HTML / CSS / JavaScript (no framework).
- **DB:** MySQL / MariaDB.

## Folder structure

```
restaurant-management/
├── database.sql                  # Full schema + seed data
├── README.md
├── backend/
│   ├── index.php                 # Front controller (entry point)
│   ├── .htaccess                 # Pretty URLs + forward Authorization header
│   ├── config/
│   │   ├── config.php            # DB + JWT + CORS config
│   │   └── Database.php          # PDO singleton
│   ├── core/
│   │   ├── Router.php            # Tiny router with {param} support
│   │   ├── Controller.php        # Base controller (input + validate)
│   │   ├── Model.php             # Generic CRUD base
│   │   ├── Response.php          # JSON helpers
│   │   └── JWT.php               # HS256 JWT (no deps)
│   ├── middleware/
│   │   ├── AuthMiddleware.php    # Requires a valid token
│   │   └── AdminMiddleware.php   # Requires role = admin
│   ├── models/                   # One per entity (12 total)
│   ├── controllers/              # One per entity + AuthController
│   └── routes/
│       └── api.php               # All REST routes
└── frontend/
    ├── index.html                # Public landing
    ├── login.html / register.html
    ├── dashboard.html            # Any authenticated user
    ├── menu.html                 # Browse dishes + cart + place order
    ├── reservations.html         # Book & manage own reservations
    ├── orders.html               # View own orders
    ├── reviews.html              # Leave/read reviews
    ├── contact.html              # Anonymous contact form
    ├── admin.html                # Full admin panel (tabs per entity)
    ├── css/style.css
    └── js/
        ├── config.js             # API_BASE
        ├── api.js                # fetch wrapper + token storage
        ├── auth.js               # navbar + guards
        └── admin.js              # admin-panel logic
        ├── chef-dashboard.js     
        ├── contact.js               
        ├── dashboard.js           
        └── employee-dashboard       
        ├── menu.js             
        ├── orders.js               
        ├── register.js              
        └── reservation.js           
        └── review.js             

```

## Local setup (XAMPP or Laragon)

1. **Copy the folder** into your web server's document root:
   - XAMPP: `C:\xampp\htdocs\restaurant-management\`
   - Laragon: `C:\laragon\www\restaurant-management\`
   - macOS/Linux: symlink or copy into `/var/www/html/` (Apache) or your server's root.

2. **Start Apache + MySQL** from the control panel.

3. **Create the database:**
   - Open <http://localhost/phpmyadmin>, click **Import**, select
     `restaurant-management/database.sql`, and import.
   - Or from a terminal:
     ```bash
     mysql -u root -p < database.sql
     ```
   This creates a database named `restaurant_db` with all 12 tables and seed data.

4. **Create the default admin:** visit this URL once in your browser:
   ```
   http://localhost/restaurant-management/backend/index.php?seed=1
   ```
   You should see `{"success":true,"message":"Seeded default admin ..."}`.
   Credentials:
   ```
   Email:    admin@restaurant.com
   Password: admin123
   ```

5. **(Optional) Adjust DB credentials** in `backend/config/config.php` if your
   MySQL user/password differ from the XAMPP default (`root` / empty).

6. **Open the frontend:**
   ```
   http://localhost/restaurant-management/frontend/index.html
   ```
   or go straight to `frontend/login.html`.

7. **Register a client account** from `register.html`, or log in as admin
   to see the Admin Panel link.

## Security notes for production

Before exposing this app publicly:

- Change `jwt.secret` in `backend/config/config.php`.
- Restrict `cors.allow_origin` to your real frontend origin.
- Serve over HTTPS.
- Remove the `?seed=1` helper in `backend/index.php`.
- Create a dedicated MySQL user with only the privileges it needs.

## API overview

Base URL: `http://localhost/restaurant-management/backend/api`

| Method | Path                         | Auth     | Notes                          |
|--------|------------------------------|----------|--------------------------------|
| POST   | /auth/register               | public   | `{ name, email, password }`    |
| POST   | /auth/login                  | public   | returns `{ token, user }`      |
| GET    | /auth/me                     | user     | current user claims            |
| GET    | /plats                       | public   | with category name             |
| POST   | /plats                       | admin    |                                |
| PUT    | /plats/{id}                  | admin    |                                |
| DELETE | /plats/{id}                  | admin    |                                |
| GET    | /categories                  | public   |                                |
| POST   | /categories                  | admin    |                                |
| GET    | /tables                      | public   |                                |
| POST   | /tables                      | admin    |                                |
| GET    | /reservations                | user     | admin sees all; client sees own|
| POST   | /reservations                | user     |                                |
| GET    | /commandes                   | user     | admin sees all; client sees own|
| POST   | /commandes                   | user     | `{ items:[{plat_id,quantity}]}`|
| GET    | /commandes/{id}              | user     | includes items                 |
| GET    | /payments, POST /payments    | admin    |                                |
| GET    | /users                       | admin    |                                |
| POST   | /contact                     | public   |                                |
| GET    | /contacts                    | admin    |                                |
| GET    | /avis                        | public   |                                |
| POST   | /avis                        | user     | `{ note (1-5), comment }`      |
| GET / POST / PUT / DELETE | /messages, /order-items | see `routes/api.php` | |

All authenticated requests must include:
```
Authorization: Bearer <jwt token>
```

All responses are JSON of the shape:
```json
{ "success": true,  "message": "...", "data": ... }
{ "success": false, "message": "...", "errors": {...} }
```

## Troubleshooting

- **"Database connection failed"** — check MySQL is running and credentials in
  `backend/config/config.php` match your setup.
- **"Route not found"** on every request — make sure Apache's `mod_rewrite` is
  enabled and `AllowOverride All` is set for your document root so `.htaccess`
  is read. On Laragon this is enabled by default.
- **"Missing or invalid Authorization header"** — some Apache setups strip the
  header; the included `.htaccess` forwards it via `HTTP_AUTHORIZATION`. If
  you're using nginx instead, add the equivalent `fastcgi_param`.
- **CORS errors** — the API sets `Access-Control-Allow-Origin: *` by default.
  If you serve the frontend from a different origin, this already works; tighten
  it in `config.php` for production.
- **Default admin password doesn't work** — re-run `index.php?seed=1` to reset
  the hash.
