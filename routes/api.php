<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\InventarioApiController;
use App\Http\Controllers\Api\GastoApiController;
use App\Http\Controllers\Api\VentaApiController;
use App\Http\Controllers\Api\FaltanteApiController;
use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\CategoryController;
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

// Autenticación - Login (con middleware web para crear sesión)
// El middleware 'web' permite crear sesión para la app web
// y también retorna token para la app móvil
Route::middleware(['web'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ========================================
// ENDPOINTS PROTEGIDOS (requieren autenticación OAuth 2.0)
// ========================================

Route::middleware(['auth:api'])->group(function () {

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
    // ENDPOINTS PARA APP MÓVIL
    // ========================================

    // Productos
    Route::get('/mobile/products', [ProductApiController::class, 'index']);
    Route::get('/mobile/products/{id}', [ProductApiController::class, 'show']);
    Route::post('/mobile/products', [ProductApiController::class, 'store']);
    Route::put('/mobile/products/{id}', [ProductApiController::class, 'update']);
    Route::delete('/mobile/products/{id}', [ProductApiController::class, 'destroy']);
    Route::get('/mobile/products/barcode/{barcode}', [ProductApiController::class, 'searchByBarcode']);

    // Inventario
    Route::get('/mobile/inventory', [InventarioApiController::class, 'index']);
    Route::get('/mobile/inventory/{id}', [InventarioApiController::class, 'show']);
    Route::post('/mobile/inventory', [InventarioApiController::class, 'store']);
    Route::post('/mobile/inventory/{id}/adjust', [InventarioApiController::class, 'adjustStock']);
    Route::get('/mobile/inventory/productos-sin-inventario', [InventarioApiController::class, 'productosSinInventario']);

    // Gastos
    Route::get('/mobile/gastos', [GastoApiController::class, 'index']);
    Route::get('/mobile/gastos/{id}', [GastoApiController::class, 'show']);
    Route::post('/mobile/gastos', [GastoApiController::class, 'store']);
    Route::put('/mobile/gastos/{id}', [GastoApiController::class, 'update']);
    Route::delete('/mobile/gastos/{id}', [GastoApiController::class, 'destroy']);

    // Ventas
    Route::get('/mobile/ventas', [VentaApiController::class, 'index']);
    Route::get('/mobile/ventas/{id}', [VentaApiController::class, 'show']);
    Route::post('/mobile/ventas', [VentaApiController::class, 'store']);
    Route::delete('/mobile/ventas/{id}', [VentaApiController::class, 'destroy']);

    // Faltantes
    Route::get('/mobile/faltantes', [FaltanteApiController::class, 'index']);
    Route::get('/mobile/faltantes/{id}', [FaltanteApiController::class, 'show']);
    Route::post('/mobile/faltantes', [FaltanteApiController::class, 'store']);
    Route::put('/mobile/faltantes/{id}', [FaltanteApiController::class, 'update']);
    Route::post('/mobile/faltantes/{id}/complete', [FaltanteApiController::class, 'markAsCompleted']);
    Route::delete('/mobile/faltantes/{id}', [FaltanteApiController::class, 'destroy']);

    // Sucursales
    Route::get('/mobile/branches', [BranchApiController::class, 'index']);
    Route::get('/mobile/branches/{id}', [BranchApiController::class, 'show']);
    Route::get('/mobile/branches/{id}/stats', [BranchApiController::class, 'stats']);

    // ========================================
    // ENDPOINTS ADMINISTRATIVOS (solo root, administradores y trabajadores)
    // ========================================

    Route::middleware(['role:root,administrador,trabajador'])->group(function () {

        // Categorías
        Route::apiResource('admin/categories', CategoryController::class);
        Route::get('admin/categories-active', [CategoryController::class, 'active']); // Categorías activas (para selectores)
        Route::post('admin/categories/reorder', [CategoryController::class, 'reorder']); // Reordenar

        // Productos
        Route::apiResource('admin/products', ProductController::class);

        // Gestión de categorías de productos
        Route::post('admin/products/{id}/categories/sync', [ProductController::class, 'syncCategories']); // Sincronizar todas
        Route::post('admin/products/{id}/categories', [ProductController::class, 'attachCategory']); // Agregar una
        Route::delete('admin/products/{product}/categories/{category}', [ProductController::class, 'detachCategory']); // Quitar una
        Route::get('admin/products/{id}/categories', [ProductController::class, 'getCategories']); // Obtener todas

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
