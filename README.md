# JimTrack Fitness — Inventory Management System (Laboratory Exercise 5)

Laravel + MySQL implementation of Lab 5 that continues the **JimTrack Fitness** theme and
PulseTrack color scheme from Laboratory Exercise 4. The old localStorage dashboard is now
backed by real database records through the MVC pattern.

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

## Login (same credentials as Lab 4)

| Username | Password  |
|----------|-----------|
| `ADMIN`  | `ADMIN123`|

`ADMIN` is mapped to the seeded Breeze account `admin@jimtrack.local` in
`app/Http/Requests/Auth/LoginRequest.php`. Normal email logins also work.

## What is implemented

| Lab 5 requirement | Where |
|-------------------|-------|
| Laravel Breeze authentication, themed to Lab 4 colors | `resources/views/auth/*`, `public/css/jimtrack.css` |
| Product migration + Eloquent model with business rules | `database/migrations/*create_products_table.php`, `app/Models/Product.php` |
| Inventory transaction migration + model | `database/migrations/*create_inventory_transactions_table.php`, `app/Models/InventoryTransaction.php` |
| Resource controller with full CRUD + validation + sanitization | `app/Http/Controllers/ProductController.php` |
| Dashboard controller with real statistics and charts | `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php` |
| Blade layout, product list / form / show views | `resources/views/layouts/app.blade.php`, `resources/views/products/*` |
| Routes with auth middleware + resource routes | `routes/web.php` |
| Seeders (admin user, 19 gym products, stock movements) | `database/seeders/*` |
| CSV export + inventory report page | `ProductController@exportCsv`, `ReportController`, `resources/views/reports/index.blade.php` |
| Lab 4 color scheme and UI patterns | `public/css/jimtrack.css` |
| Filters, search, low-stock badges and CSV download from Lab 4 | `resources/views/products/index.blade.php` |

## Main routes

- `/dashboard` — statistics and Chart.js visualizations from MySQL
- `/products` — searchable/filterable inventory table with CRUD actions
- `/products/create`, `/products/{id}`, `/products/{id}/edit`
- `/products/export/csv` — filtered inventory export
- `/reports` and `/reports/export/csv` — category valuation report
- `/profile` — Breeze profile and password settings
