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

    // POS & Inventory Endpoints
    Route::get('/pos/products', [PosController::class, 'getProducts']);
    Route::get('/pos/products/barcode/{code}', [PosController::class, 'getProductByBarcode']);
    Route::get('/pos/customers', [PosController::class, 'getCustomers']);
    Route::post('/pos/checkout', [PosController::class, 'checkout']);
    Route::get('/pos/invoice/{id}/thermal-data', [PosController::class, 'getInvoiceThermalData']);

    // Super Admin Endpoints
    Route::get('/super-admin/stats', [DashboardController::class, 'getSuperAdminStats']);
});
