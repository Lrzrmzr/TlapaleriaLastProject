<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\Inventory;
use App\Models\Faltante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Mostrar configuración del sistema (solo para root)
     */
    public function index()
    {
        // Verificar que el usuario es root (role_id = 1)
        if (!$this->isRootUser()) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        $settings = SystemSetting::all()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->type === 'boolean' ? (bool) $setting->value : $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
            ];
        });

        // Estadísticas del sistema
        $stats = [
            'productos_stock_bajo' => Inventory::where('stock', '<=', SystemSetting::stockBajoThreshold())
                ->where('stock', '>', 0)
                ->count(),
            'productos_agotados' => Inventory::where('stock', 0)->count(),
            'faltantes_pendientes' => Faltante::where('confirmado', false)->count(),
        ];

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'stats' => $stats,
        ]);
    }

    /**
     * Actualizar configuración
     */
    public function update(Request $request)
    {
        if (!$this->isRootUser()) {
            abort(403, 'No tienes permisos para realizar esta acción');
        }

        $request->validate([
            'key' => 'required|string|exists:system_settings,key',
            'value' => 'required',
        ]);

        SystemSetting::set($request->key, $request->value);

        return redirect()->back()->with('success', 'Configuración actualizada correctamente');
    }

    /**
     * Generar faltantes automáticamente desde productos con stock bajo
     */
    public function generarFaltantesAutomatico(Request $request)
    {
        if (!$this->isRootUser()) {
            abort(403, 'No tienes permisos para realizar esta acción');
        }

        $threshold = SystemSetting::stockBajoThreshold();

        // Obtener productos con stock bajo que no tengan faltantes pendientes
        $productosStockBajo = Inventory::with('product')
            ->where('stock', '<=', $threshold)
            ->where('stock', '>=', 0)
            ->get();

        $generados = 0;

        foreach ($productosStockBajo as $inventario) {
            // Verificar si ya existe un faltante pendiente para este producto
            $faltanteExiste = Faltante::where('product_id', $inventario->product_id)
                ->where('confirmado', false)
                ->exists();

            if (!$faltanteExiste) {
                $cantidadSugerida = max($inventario->min_stock - $inventario->stock, 10);

                Faltante::create([
                    'product_id' => $inventario->product_id,
                    'user_id' => Auth::id(),
                    'quantity' => $cantidadSugerida,
                    'notes' => 'Generado automáticamente por stock bajo (Stock actual: ' . $inventario->stock . ')',
                    'confirmado' => false,
                ]);

                $generados++;
            }
        }

        return redirect()->back()->with('success', "Se generaron {$generados} faltantes automáticamente");
    }

    /**
     * Verificar si el usuario actual es root
     */
    private function isRootUser(): bool
    {
        $user = Auth::user();
        return $user && $user->roles()->where('roles.id', 1)->exists();
    }
}
