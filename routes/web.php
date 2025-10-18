<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


use App\Http\Controllers\FaltanteController;

// Usuarios
Route::middleware(['auth'])->group(function () {
    Route::get('/usuarios', [\App\Http\Controllers\UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/asignar-rol', [\App\Http\Controllers\UsuarioController::class, 'asignarRol'])->name('usuarios.asignarRol');
});

Route::middleware('auth')->group(function () {
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

    // Inventario
    Route::get('/inventario', [\App\Http\Controllers\InventarioController::class, 'index'])->name('inventario.index');
    Route::post('/inventario', [\App\Http\Controllers\InventarioController::class, 'store'])->name('inventario.store');
    Route::put('/inventario/{inventario}', [\App\Http\Controllers\InventarioController::class, 'update'])->name('inventario.update');
    Route::post('/inventario/{inventario}/ajustar', [\App\Http\Controllers\InventarioController::class, 'ajustarStock'])->name('inventario.ajustar');
    Route::delete('/inventario/{inventario}', [\App\Http\Controllers\InventarioController::class, 'destroy'])->name('inventario.destroy');
});

require __DIR__.'/auth.php';
