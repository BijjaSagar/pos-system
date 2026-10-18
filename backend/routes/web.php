<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminProductController;
use App\Http\Controllers\Web\AdminPurchaseController;
use App\Http\Controllers\Web\AdminReportController;
use App\Http\Controllers\Web\AdminUserController;
use App\Http\Controllers\Web\AdminSettingController;
use App\Http\Controllers\Web\AdminShopController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Authentication
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// Store Admin Protected Web Portal
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Products & Bulk Excel Import
    Route::get('/products/sample-csv', [AdminProductController::class, 'downloadSampleCsv'])->name('products.sample-csv');
    Route::get('/products/import', [AdminProductController::class, 'import'])->name('products.import');
    Route::post('/products/process-import', [AdminProductController::class, 'processImport'])->name('products.process-import');
    Route::resource('products', AdminProductController::class);

    // Supplier Purchases & Stock Restocking
    Route::resource('purchases', AdminPurchaseController::class)->only(['index', 'create', 'store', 'show']);

    // Staff & Roles Management
    Route::resource('users', AdminUserController::class);

    // Reports & PDF Downloads
    Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/purchases', [AdminReportController::class, 'purchases'])->name('reports.purchases');
    Route::get('/reports/profit-loss', [AdminReportController::class, 'profitLoss'])->name('reports.profit-loss');

    // Store Settings & Branding
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Multi-Store & Branches Management
    Route::resource('shops', AdminShopController::class);
});
