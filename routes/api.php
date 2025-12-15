<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\FaltanteController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VentaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================================
// ENDPOINTS PÚBLICOS (sin autenticación)
// ========================================

// Endpoint público para consulta de productos
Route::get('/products', [PublicController::class, 'apiProducts']);

// ========================================
// ENDPOINTS DE AUTENTICACIÓN (requieren sesión web)
// ========================================

// Estas rutas necesitan sesión porque manejan autenticación híbrida (OAuth + Sesión Web)
Route::middleware(['web'])->group(function () {
    // Autenticación - Login
    Route::post('/login', [AuthController::class, 'login']);
});

// ========================================
// ENDPOINTS PROTEGIDOS (requieren autenticación OAuth 2.0)
// ========================================

Route::middleware(['auth:api', 'web'])->group(function () {

    // Autenticación - Información del usuario y logout
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/refresh-info', [AuthController::class, 'refresh']);

    // Información del usuario autenticado (endpoint alternativo)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ========================================
    // ENDPOINTS ADMINISTRATIVOS (solo root, administradores y trabajadores)
    // ========================================

    Route::middleware(['role:root,administrador,trabajador'])->group(function () {

        // Productos
        Route::apiResource('admin/products', ProductController::class);

        // Proveedores
        Route::apiResource('admin/suppliers', SupplierController::class);

        // Inventario
        Route::get('admin/inventory', [InventarioController::class, 'index']);
        Route::post('admin/inventory', [InventarioController::class, 'store']);
        Route::get('admin/inventory/{id}', [InventarioController::class, 'show']);
        Route::put('admin/inventory/{id}', [InventarioController::class, 'update']);
        Route::delete('admin/inventory/{id}', [InventarioController::class, 'destroy']);

        // Ventas
        Route::get('admin/ventas', [VentaController::class, 'index']);
        Route::post('admin/ventas', [VentaController::class, 'store']);
        Route::get('admin/ventas/{id}', [VentaController::class, 'show']);
        Route::delete('admin/ventas/{id}', [VentaController::class, 'destroy']);

        // Faltantes
        Route::get('admin/faltantes', [FaltanteController::class, 'index']);
        Route::post('admin/faltantes', [FaltanteController::class, 'store']);
        Route::get('admin/faltantes/{id}', [FaltanteController::class, 'show']);
        Route::put('admin/faltantes/{id}', [FaltanteController::class, 'update']);
        Route::delete('admin/faltantes/{id}', [FaltanteController::class, 'destroy']);
    });
});
