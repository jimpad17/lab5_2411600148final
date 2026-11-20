# JimTrack Fitness — Inventory Management System (Laboratory Exercise 5)

Laravel 12 + MySQL implementation of Lab 5 that continues the **JimTrack Fitness** theme and
PulseTrack color scheme from Laboratory Exercise 4. The old localStorage dashboard is now
backed by real database records through the MVC pattern, with the extra modules requested
for the expanded system (Brands, Categories, Users, Inventory and Activity Log).

## Quick start (XAMPP)

1. Start **Apache** and **MySQL** in XAMPP.
2. Database `jimtrack_inventory` has been created (utf8mb4). To recreate it manually:

   ```sql
   CREATE DATABASE IF NOT EXISTS jimtrack_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. `.env` is configured for:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=jimtrack_inventory
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Install and run:

   ```bash
   composer install
   npm install && npm run build
   cp .env.example .env      # only if .env is missing
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan serve
   ```

5. Open <http://127.0.0.1:8000>.

## Login accounts

| Username / Email | Password | Role | Status |
|------------------|----------|------|--------|
| `ADMIN` / `admin@jimtrack.local` | `ADMIN123` | Admin | Active |
| `juan@jimtrack.local` | `STAFF123` | Staff | Active |
| `maria@jimtrack.local` | `STAFF123` | Staff | Active |
| `test@jimtrack.local` | `STAFF123` | Staff | Inactive (login blocked) |

`ADMIN` is mapped to `admin@jimtrack.local` in `app/Http/Requests/Auth/LoginRequest.php`,
so the Lab 4 credentials still work. Normal email logins work too.

## Important behaviour: nothing is deleted

Deleting a **product, brand, category or user account** only sets `is_active = false`.
The row, its stock history and its audit entries remain in MySQL.

- Products show **Unavailable (Inactive)** and can be restored with the restore button.
- Brands and Categories can be set inactive / restored; their products are kept.
- Users can be deactivated / restored; deactivated accounts cannot log in.
- Stock transactions and activity logs are never removed with these actions.

## What is implemented

| Feature | Where |
|---------|-------|
| Breeze authentication themed to Lab 4 colors | `resources/views/auth/*`, `public/css/jimtrack.css` |
| Products with Brand + Category relations | `database/migrations/*create_products_table.php`, `app/Models/Product.php` |
| Brands and Categories tables with slug + active status | `app/Models/Brand.php`, `app/Models/Category.php`, seeders |
| Users with admin/staff roles and active status | `app/Models/User.php`, `app/Http/Middleware/EnsureUserIsAdmin.php` |
| Activity Log (visits, searches, login/logout, every CRUD change) | `app/Models/ActivityLog.php`, `app/Services/ActivityLogger.php`, `app/Http/Middleware/LogActivity.php`, listeners |
| Inventory overview + Stock In / Stock Out | `app/Http/Controllers/InventoryController.php` |
| Resource controllers with CRUD, validation and sanitization | `app/Http/Controllers/*Controller.php` |
| Dashboard with real statistics and Chart.js charts | `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php` |
| Blade layouts and screens for every module | `resources/views/**` |
| Seeders: 4 users, 7 brands, 6 categories, 21 products (19 active + 2 inactive), stock movements, sample activity | `database/seeders/*` |
| CSV exports | `ProductController@exportCsv`, `ReportController@exportCsv` |
| Lab 4 palette and UI patterns | `public/css/jimtrack.css` |

## Main routes

- `/dashboard` — statistics, charts and recent activity
- `/inventory` — stock levels and quick Stock In / Stock Out
- `/products` — searchable/filterable product management with deactivate/restore
- `/brands`, `/categories` — lookup table management with deactivate/restore
- `/users` — admin-only account management with roles and deactivate/restore
- `/activity-logs` — filterable audit trail
- `/reports` — valuation by category and brand, reorder list, CSV download
- `/profile` — Breeze profile and password settings

## Tests

```bash
php artisan test
```

29 tests pass, including new checks for soft deactivation/restore, stock movements,
activity logging and staff access control.
