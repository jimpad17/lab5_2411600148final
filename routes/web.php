<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\LogActivity;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return redirect(auth()->check() ? route('dashboard') : route('login'));
});

/*
 * Every authenticated page visit / search is logged by LogActivity;
 * EnsureUserIsActive immediately logs out deactivated accounts.
 */
Route::middleware(['auth', EnsureUserIsActive::class, LogActivity::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
     * Shared by BOTH roles. Staff handle the day-to-day work: read the
     * catalog, check stock levels and post Stock In / Stock Out movements.
     */
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/{product}/stock', [InventoryController::class, 'stock'])->name('inventory.stock');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/export/csv', [ProductController::class, 'exportCsv'])->name('products.export');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show')->whereNumber('product');

    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

    /*
     * Admin-only: master-data changes, people, money valuation and the
     * audit trail. The `admin` middleware aborts with 403 for staff.
     */
    Route::middleware('admin')->group(function () {
        // Products - destroy = deactivate; restore brings records back
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');

        // Brands
        Route::get('brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::post('brands/{brand}/restore', [BrandController::class, 'restore'])->name('brands.restore');

        // Categories
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])->name('categories.restore');

        // Users
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Audit trail
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Reports (reached from the dashboard top-right button, not the sidebar)
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export');
    });
});

require __DIR__.'/auth.php';

/*
 * Any unmatched URL - including random slash/dot combos like
 * /products//////.......////// - returns to the last real page the user
 * opened. The target is stored by LogActivity as a request URI, so it is
 * always local and cannot be poisoned into an external redirect.
 */
Route::fallback(function () {
    $target = session('last_page_url');

    if (is_string($target) && Str::startsWith($target, '/') && ! Str::startsWith($target, '//')) {
        return redirect()->to($target);
    }

    return redirect(auth()->check() ? route('dashboard') : route('login'));
})->name('fallback');
