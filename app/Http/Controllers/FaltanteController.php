<?php
namespace App\Http\Controllers;

use App\Models\Faltante;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FaltanteController extends Controller
{
    public function index(Request $request)
    {
        $query = Faltante::with('user')->latest();
        if ($request->has('confirmado')) {
            $query->where('confirmado', $request->confirmado);
        }
        // Solo filtra por pedido si el valor no es vacío y no es null
        if ($request->has('pedido') && $request->pedido !== '' && $request->pedido !== null) {
            $query->where('pedido', $request->pedido);
        }
        $faltantes = $query->get()->map(function ($faltante) {
            return [
                'id' => $faltante->id,
                'descripcion' => $faltante->descripcion,
                'pedido' => $faltante->pedido,
                'confirmado' => $faltante->confirmado,
                'created_at' => $faltante->created_at->format('H:i'),
                'fecha' => $faltante->created_at->format('d/m/Y'),
                'user' => $faltante->user->name,
            ];
        });

        // Obtener productos con stock bajo (2 o menos)
        $productosStockBajo = \App\Models\Product::with('inventory')->get()->filter(function ($producto) {
            $stockTotal = $producto->inventory->sum('quantity');
            return $stockTotal >= 0 && $stockTotal <= 2;
        })->map(function ($producto) {
            return [
                'id' => $producto->id,
                'name' => $producto->name,
                'stock' => $producto->inventory->sum('quantity'),
                'price' => $producto->price,
            ];
        })->values();

        // Calcular estadísticas
        $totalFaltantes = $faltantes->count();
        $faltantesPendientes = $faltantes->where('confirmado', false)->count();
        $faltantesConfirmados = $faltantes->where('confirmado', true)->count();
        $productosStockBajoCount = $productosStockBajo->count();

        return Inertia::render('Faltantes/Index', [
            'faltantes' => $faltantes,
            'productosStockBajo' => $productosStockBajo,
            'stats' => [
                'total' => $totalFaltantes,
                'pendientes' => $faltantesPendientes,
                'confirmados' => $faltantesConfirmados,
                'stockBajo' => $productosStockBajoCount,
            ],
            'filters' => [
                'confirmado' => $request->confirmado,
                'pedido' => $request->pedido,
            ],
            'faltantesManualesHabilitados' => SystemSetting::faltantesManualesHabilitados(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'pedido' => 'required|in:GENERAL,TRUPER',
            'confirmado' => 'boolean',
        ]);
        Faltante::create([
            'descripcion' => $request->descripcion,
            'pedido' => $request->pedido,
            'confirmado' => $request->confirmado ?? 0,
            'user_id' => Auth::id(),
        ]);
        return redirect()->back();
    }

    public function update(Request $request, Faltante $faltante)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'pedido' => 'required|in:GENERAL,TRUPER',
            'confirmado' => 'boolean',
        ]);
        $faltante->update([
            'descripcion' => $request->descripcion,
            'pedido' => $request->pedido,
            'confirmado' => $request->confirmado ?? $faltante->confirmado,
        ]);
        return redirect()->back();
    }

    public function confirmar(Request $request)
    {
        $ids = $request->ids;
        Faltante::whereIn('id', $ids)->update(['confirmado' => 1]);
        return redirect()->back();
    }

    public function destroy(Faltante $faltante)
    {
        $faltante->delete();
        return redirect()->back();
    }
}
