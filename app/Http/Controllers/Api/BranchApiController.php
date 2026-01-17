<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchApiController extends Controller
{
    /**
     * Listar sucursales
     */
    public function index(Request $request)
    {
        $query = Branch::query();

        // Filtro por estado activo
        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Búsqueda por nombre o código
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $branches = $query->get();

        return response()->json([
            'success' => true,
            'data' => $branches
        ]);
    }

    /**
     * Obtener una sucursal específica
     */
    public function show($id)
    {
        $branch = Branch::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $branch
        ]);
    }

    /**
     * Obtener estadísticas de una sucursal
     */
    public function stats($id)
    {
        $branch = Branch::findOrFail($id);

        // Contar inventarios
        $totalInventarios = $branch->inventories()->count();

        // Contar productos con stock bajo
        $stockBajo = $branch->inventories()
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->count();

        // Contar productos agotados
        $agotados = $branch->inventories()
            ->where('stock', 0)
            ->count();

        // Total de ventas del día
        $ventasHoy = $branch->ventas()
            ->whereDate('created_at', today())
            ->sum('total');

        // Total de gastos del mes
        $gastosDelMes = $branch->gastos()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        // Faltantes pendientes
        $faltantesPendientes = $branch->faltantes()
            ->where('status', 'pendiente')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'branch' => $branch,
                'stats' => [
                    'total_inventarios' => $totalInventarios,
                    'stock_bajo' => $stockBajo,
                    'agotados' => $agotados,
                    'ventas_hoy' => $ventasHoy,
                    'gastos_mes' => $gastosDelMes,
                    'faltantes_pendientes' => $faltantesPendientes,
                ]
            ]
        ]);
    }
}
