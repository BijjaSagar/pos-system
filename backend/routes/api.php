<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes for POS SaaS (Flutter Mobile/Tablet + Web + Super Admin)
|--------------------------------------------------------------------------
*/

// Public Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register-shop', [AuthController::class, 'registerShop']);
});

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User & Staff Management (Super Admin, Store Owner, Manager, Cashier, Employee)
    Route::apiResource('users', UserController::class);
    Route::post('/super-admin/users', [UserController::class, 'store']);

    // Store Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStoreStats']);

    // Categories
    Route::get('/pos/categories', [PosController::class, 'getCategories']);
    Route::post('/pos/categories', [PosController::class, 'storeCategory']);

    // POS & Inventory Endpoints
    Route::get('/pos/products', [PosController::class, 'getProducts']);
    Route::post('/pos/products', [PosController::class, 'storeProduct']);
    Route::put('/pos/products/{id}', [PosController::class, 'updateProduct']);
    Route::delete('/pos/products/{id}', [PosController::class, 'deleteProduct']);
    Route::get('/pos/products/barcode/{code}', [PosController::class, 'getProductByBarcode']);
    Route::get('/pos/customers', [PosController::class, 'getCustomers']);
    Route::post('/pos/checkout', [PosController::class, 'checkout']);
    Route::get('/pos/sales', [PosController::class, 'getSales']);
    Route::get('/pos/invoice/{id}/thermal-data', [PosController::class, 'getInvoiceThermalData']);

    // Mobile Store & Admin Settings
    Route::get('/pos/settings', [PosController::class, 'getSettings']);
    Route::post('/pos/settings', [PosController::class, 'updateSettings']);

    // Super Admin Endpoints
    Route::get('/super-admin/stats', [DashboardController::class, 'getSuperAdminStats']);
});
