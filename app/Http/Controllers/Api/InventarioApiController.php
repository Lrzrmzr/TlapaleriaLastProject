<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\Product;
use Illuminate\Http\Request;

class InventarioApiController extends Controller
{
    /**
     * Listar inventario con filtros
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = BranchInventory::with(['product.supplier', 'branch']);

        // Si no es root, solo ve inventario de su sucursal
        if (!$user->isRoot() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filtro: Búsqueda por nombre del producto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filtro: Por estado de stock
        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            if ($status === 'agotado') {
                $query->where('stock', 0);
            } elseif ($status === 'critico') {
                $query->where('stock', '>', 0)->where('stock', '<=', 2);
            } elseif ($status === 'bajo') {
                $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 2);
            }
        }

        $perPage = $request->get('per_page', 50);
        $inventarios = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $inventarios->items(),
            'pagination' => [
                'total' => $inventarios->total(),
                'per_page' => $inventarios->perPage(),
                'current_page' => $inventarios->currentPage(),
                'last_page' => $inventarios->lastPage(),
            ]
        ]);
    }

    /**
     * Obtener inventario de un producto específico
     */
    public function show($id)
    {
        $inventario = BranchInventory::with(['product.supplier', 'branch'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $inventario
        ]);
    }

    /**
     * Ajustar stock de inventario
     */
    public function adjustStock(Request $request, $id)
    {
        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        $inventario = BranchInventory::findOrFail($id);
        $oldStock = $inventario->stock;
        $inventario->stock += $validated['adjustment'];

        if ($inventario->stock < 0) {
            return response()->json([
                'success' => false,
                'message' => 'El stock no puede ser negativo'
            ], 400);
        }

        $inventario->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock ajustado exitosamente',
            'data' => [
                'old_stock' => $oldStock,
                'adjustment' => $validated['adjustment'],
                'new_stock' => $inventario->stock,
                'product' => $inventario->product->name,
            ]
        ]);
    }

    /**
     * Crear nuevo inventario para un producto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        // Verificar que no exista ya
        $exists = BranchInventory::where('product_id', $validated['product_id'])
            ->where('branch_id', $validated['branch_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'El inventario para este producto ya existe en esta sucursal'
            ], 400);
        }

        $inventario = BranchInventory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventario creado exitosamente',
            'data' => $inventario->load(['product', 'branch'])
        ], 201);
    }

    /**
     * Productos sin inventario
     */
    public function productosSinInventario(Request $request)
    {
        $user = $request->user();
        $branchId = $request->get('branch_id', $user->branch_id);

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere un branch_id'
            ], 400);
        }

        $productos = Product::whereDoesntHave('inventory', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();

        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }
}
