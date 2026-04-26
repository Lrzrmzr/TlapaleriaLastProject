<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\PublicController::class, 'index'])->name('public.home');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'check.branch'])
    ->name('dashboard');

// Página para usuarios sin sucursal asignada (NO requiere check.branch)
Route::get('/sin-sucursal', function () {
    return Inertia::render('SinSucursal');
})->middleware(['auth'])->name('sin-sucursal');


use App\Http\Controllers\FaltanteController;

// Usuarios - Requiere validación de sucursal
Route::middleware(['auth', 'check.branch'])->group(function () {
    Route::get('/usuarios', [\App\Http\Controllers\UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/asignar-rol', [\App\Http\Controllers\UsuarioController::class, 'asignarRol'])->name('usuarios.asignarRol');
    Route::post('/usuarios/{user}/asignar-sucursal', [\App\Http\Controllers\UsuarioController::class, 'asignarSucursal'])->name('usuarios.asignarSucursal');
});

// Categorías - Requiere validación de sucursal
Route::middleware(['auth', 'check.branch'])->group(function () {
    Route::get('/categorias', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categorias.index');
    Route::post('/categorias', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{id}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categorias.destroy');
});

// Sucursales - Requiere validación de sucursal
Route::middleware(['auth', 'check.branch'])->group(function () {
    Route::get('/sucursales', [\App\Http\Controllers\BranchController::class, 'index'])->name('sucursales.index');
    Route::post('/sucursales', [\App\Http\Controllers\BranchController::class, 'store'])->name('sucursales.store');
    Route::put('/sucursales/{id}', [\App\Http\Controllers\BranchController::class, 'update'])->name('sucursales.update');
    Route::delete('/sucursales/{id}', [\App\Http\Controllers\BranchController::class, 'destroy'])->name('sucursales.destroy');
    Route::post('/sucursales/{id}/restore', [\App\Http\Controllers\BranchController::class, 'restore'])->name('sucursales.restore');
    Route::post('/sucursales/{id}/toggle-status', [\App\Http\Controllers\BranchController::class, 'toggleStatus'])->name('sucursales.toggle-status');
    Route::get('/sucursales/{id}/inventory', [\App\Http\Controllers\BranchController::class, 'inventory'])->name('sucursales.inventory');
    Route::get('/sucursales/{id}/statistics', [\App\Http\Controllers\BranchController::class, 'statistics'])->name('sucursales.statistics');
    Route::get('/sucursales-active', [\App\Http\Controllers\BranchController::class, 'active'])->name('sucursales.active');
});

Route::middleware(['auth', 'check.branch'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CRUD Faltantes
    Route::get('/faltantes', [FaltanteController::class, 'index'])->name('faltantes.index');
    Route::post('/faltantes', [FaltanteController::class, 'store'])->name('faltantes.store');
    Route::put('/faltantes/{faltante}', [FaltanteController::class, 'update'])->name('faltantes.update');
    Route::delete('/faltantes/{faltante}', [FaltanteController::class, 'destroy'])->name('faltantes.destroy');
    Route::post('/faltantes/confirmar', [FaltanteController::class, 'confirmar'])->name('faltantes.confirmar');

    // Ventas
    Route::get('/ventas', [\App\Http\Controllers\VentaController::class, 'index'])->name('ventas.index');
    Route::post('/ventas', [\App\Http\Controllers\VentaController::class, 'store'])->name('ventas.store');

    // Proveedores
    Route::get('/proveedores', [\App\Http\Controllers\SupplierController::class, 'index'])->name('proveedores.index');
    Route::post('/proveedores', [\App\Http\Controllers\SupplierController::class, 'store'])->name('proveedores.store');
    Route::put('/proveedores/{proveedor}', [\App\Http\Controllers\SupplierController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->name('proveedores.destroy');

    // Productos
    Route::get('/productos', [\App\Http\Controllers\ProductController::class, 'index'])->name('productos.index');
    Route::post('/productos', [\App\Http\Controllers\ProductController::class, 'store'])->name('productos.store');
    Route::put('/productos/{producto}', [\App\Http\Controllers\ProductController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{producto}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('productos.destroy');

    // Gestión de proveedores de productos
    Route::post('/productos/{producto}/proveedores', [\App\Http\Controllers\ProductController::class, 'attachSupplier'])->name('productos.proveedores.attach');
    Route::put('/productos/{producto}/proveedores/{supplier}', [\App\Http\Controllers\ProductController::class, 'updateSupplier'])->name('productos.proveedores.update');
    Route::delete('/productos/{producto}/proveedores/{supplier}', [\App\Http\Controllers\ProductController::class, 'detachSupplier'])->name('productos.proveedores.detach');

    // Inventario
    Route::get('/inventario', [\App\Http\Controllers\InventarioController::class, 'index'])->name('inventario.index');
    Route::post('/inventario', [\App\Http\Controllers\InventarioController::class, 'store'])->name('inventario.store');
    Route::put('/inventario/{inventario}', [\App\Http\Controllers\InventarioController::class, 'update'])->name('inventario.update');
    Route::post('/inventario/{inventario}/ajustar', [\App\Http\Controllers\InventarioController::class, 'ajustarStock'])->name('inventario.ajustar');
    Route::delete('/inventario/{inventario}', [\App\Http\Controllers\InventarioController::class, 'destroy'])->name('inventario.destroy');

    // Gastos
    Route::get('/gastos', [\App\Http\Controllers\GastoController::class, 'index'])->name('gastos.index');
    Route::post('/gastos', [\App\Http\Controllers\GastoController::class, 'store'])->name('gastos.store');
    Route::put('/gastos/{gasto}', [\App\Http\Controllers\GastoController::class, 'update'])->name('gastos.update');
    Route::delete('/gastos/{gasto}', [\App\Http\Controllers\GastoController::class, 'destroy'])->name('gastos.destroy');

    // Cuentas por Pagar
    Route::get('/cuentas-por-pagar', [\App\Http\Controllers\CuentaPorPagarController::class, 'index'])->name('cuentas-por-pagar.index');
    Route::post('/cuentas-por-pagar', [\App\Http\Controllers\CuentaPorPagarController::class, 'store'])->name('cuentas-por-pagar.store');
    Route::put('/cuentas-por-pagar/{cuentaPorPagar}', [\App\Http\Controllers\CuentaPorPagarController::class, 'update'])->name('cuentas-por-pagar.update');
    Route::delete('/cuentas-por-pagar/{cuentaPorPagar}', [\App\Http\Controllers\CuentaPorPagarController::class, 'destroy'])->name('cuentas-por-pagar.destroy');
    Route::post('/cuentas-por-pagar/{cuentaPorPagar}/pagos', [\App\Http\Controllers\CuentaPorPagarController::class, 'storePago'])->name('cuentas-por-pagar.pagos.store');
    Route::delete('/cuentas-por-pagar/pagos/{pago}', [\App\Http\Controllers\CuentaPorPagarController::class, 'destroyPago'])->name('cuentas-por-pagar.pagos.destroy');

    // Cuentas por Cobrar
    Route::get('/cuentas-por-cobrar', [\App\Http\Controllers\CuentaPorCobrarController::class, 'index'])->name('cuentas-por-cobrar.index');
    Route::post('/cuentas-por-cobrar', [\App\Http\Controllers\CuentaPorCobrarController::class, 'store'])->name('cuentas-por-cobrar.store');
    Route::put('/cuentas-por-cobrar/{cuentaPorCobrar}', [\App\Http\Controllers\CuentaPorCobrarController::class, 'update'])->name('cuentas-por-cobrar.update');
    Route::delete('/cuentas-por-cobrar/{cuentaPorCobrar}', [\App\Http\Controllers\CuentaPorCobrarController::class, 'destroy'])->name('cuentas-por-cobrar.destroy');
    Route::post('/cuentas-por-cobrar/{cuentaPorCobrar}/cobros', [\App\Http\Controllers\CuentaPorCobrarController::class, 'storeCobro'])->name('cuentas-por-cobrar.cobros.store');
    Route::delete('/cuentas-por-cobrar/cobros/{cobro}', [\App\Http\Controllers\CuentaPorCobrarController::class, 'destroyCobro'])->name('cuentas-por-cobrar.cobros.destroy');

    // Settings (Root only)
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/update', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/generar-faltantes', [\App\Http\Controllers\SettingsController::class, 'generarFaltantesAutomatico'])->name('settings.generar-faltantes');

    // Auditoría (Activity Logs)
    Route::get('/auditoria', [\App\Http\Controllers\AuditController::class, 'index'])->name('auditoria.index');
    Route::get('/auditoria/{id}', [\App\Http\Controllers\AuditController::class, 'show'])->name('auditoria.show');
});

// ─── Super Admin — Solo root, sin filtro de sucursal ─────────────────────────
Route::middleware(['auth', 'role:root'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/tenants', [\App\Http\Controllers\Admin\TenantController::class, 'index'])->name('tenants.index');
        Route::post('/tenants', [\App\Http\Controllers\Admin\TenantController::class, 'store'])->name('tenants.store');
        Route::put('/tenants/{id}', [\App\Http\Controllers\Admin\TenantController::class, 'update'])->name('tenants.update');
        Route::post('/tenants/{id}/suspend', [\App\Http\Controllers\Admin\TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('/tenants/{id}/impersonate', [\App\Http\Controllers\Admin\TenantController::class, 'impersonate'])->name('tenants.impersonate');
        Route::post('/stop-impersonating', [\App\Http\Controllers\Admin\TenantController::class, 'stopImpersonating'])->name('stop-impersonating');
    });

require __DIR__.'/auth.php';
