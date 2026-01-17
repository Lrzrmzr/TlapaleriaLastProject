<?php

namespace App\Http\Controllers;

use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $userBranchId = $request->user_branch_id; // Inyectado por middleware
        $isRoot = $request->is_root ?? false; // Inyectado por middleware

        // Si es root, obtener todas las sucursales, si no, solo la del usuario
        $branches = $isRoot
            ? Branch::where('active', true)->orderBy('name')->get()
            : Branch::where('id', $userBranchId)->get();

        // Obtener inventarios por sucursal con filtros
        $query = BranchInventory::with(['product', 'branch']);

        // Si no es root, filtrar solo por su sucursal
        if (!$isRoot) {
            $query->where('branch_id', $userBranchId);
        } else {
            // Si es root y especifica sucursal en filtro
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
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
                $query->whereColumn('stock', '<=', 'min_stock')
                      ->where('stock', '>', 2);
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'product_name') {
            // Ordenar por nombre de producto requiere un join
            $query->join('products', 'branch_inventory.product_id', '=', 'products.id')
                  ->orderBy('products.name', $sortOrder)
                  ->select('branch_inventory.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginación (50 por página)
        $perPage = $request->get('per_page', 50);
        $inventarios = $query->paginate($perPage)->through(function ($inventario) {
            return [
                'id' => $inventario->id,
                'product_id' => $inventario->product_id,
                'product_name' => $inventario->product->name ?? 'Producto eliminado',
                'product_code' => $inventario->product->code ?? '',
                'product_price' => $inventario->product->price ?? 0,
                'branch_id' => $inventario->branch_id,
                'branch_name' => $inventario->branch ? $inventario->branch->name : '⚠️ Sin Sucursal',
                'branch_code' => $inventario->branch->code ?? '',
                'quantity' => $inventario->stock ?? 0,
                'min_stock' => $inventario->min_stock ?? 0,
                'max_stock' => $inventario->max_stock ?? 0,
                'cost' => $inventario->cost ?? 0,
                'status' => $this->getStockStatus($inventario->stock ?? 0, $inventario->min_stock ?? 0),
                'updated_at' => $inventario->updated_at->format('d/m/Y H:i'),
            ];
        });

        // Productos sin inventario en esta sucursal (o en general si es root)
        $productosQuery = Product::where('active', true);

        if (!$isRoot) {
            // Productos que no tienen inventario en la sucursal del usuario
            $productosQuery->whereDoesntHave('branchInventory', function($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            });
        } else {
            // Para root, productos que no tienen inventario en la sucursal especificada o ninguna
            if ($request->filled('branch_id')) {
                $branchId = $request->branch_id;
                $productosQuery->whereDoesntHave('branchInventory', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            } else {
                $productosQuery->whereDoesntHave('branchInventory');
            }
        }

        $productosSinInventario = $productosQuery->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'price' => $product->price,
            ];
        });

        // Estadísticas
        $totalProductos = Product::where('active', true)->count();

        if (!$isRoot) {
            $productosConInventario = BranchInventory::where('branch_id', $userBranchId)->count();
            $productosStockBajo = BranchInventory::where('branch_id', $userBranchId)
                ->whereColumn('stock', '<=', 'min_stock')
                ->where('stock', '>', 0)
                ->count();
            $productosAgotados = BranchInventory::where('branch_id', $userBranchId)
                ->where('stock', 0)
                ->count();
        } else {
            if ($request->filled('branch_id')) {
                $branchId = $request->branch_id;
                $productosConInventario = BranchInventory::where('branch_id', $branchId)->count();
                $productosStockBajo = BranchInventory::where('branch_id', $branchId)
                    ->whereColumn('stock', '<=', 'min_stock')
                    ->where('stock', '>', 0)
                    ->count();
                $productosAgotados = BranchInventory::where('branch_id', $branchId)
                    ->where('stock', 0)
                    ->count();
            } else {
                $productosConInventario = BranchInventory::count();
                $productosStockBajo = BranchInventory::whereColumn('stock', '<=', 'min_stock')
                    ->where('stock', '>', 0)
                    ->count();
                $productosAgotados = BranchInventory::where('stock', 0)->count();
            }
        }

        return Inertia::render('Inventario/Index', [
            'inventarios' => $inventarios,
            'productosSinInventario' => $productosSinInventario,
            'branches' => $branches,
            'userBranch' => $isRoot ? null : $branches->first(),
            'isRoot' => $isRoot,
            'filters' => [
                'search' => $request->search,
                'branch_id' => $request->branch_id,
                'stock_status' => $request->stock_status,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'stats' => [
                'total' => $totalProductos,
                'conInventario' => $productosConInventario,
                'stockBajo' => $productosStockBajo,
                'agotados' => $productosAgotados,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $userBranchId = $request->user_branch_id;
        $isRoot = $request->is_root ?? false;

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => $isRoot ? 'required|exists:branches,id' : 'nullable',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
        ]);

        // Determinar la sucursal
        $branchId = $isRoot ? $request->branch_id : $userBranchId;

        // Validar que no exista ya este producto en esta sucursal
        $exists = BranchInventory::where('branch_id', $branchId)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors([
                'error' => 'Este producto ya tiene inventario en la sucursal seleccionada.'
            ]);
        }

        BranchInventory::create([
            'branch_id' => $branchId,
            'product_id' => $request->product_id,
            'stock' => $request->quantity,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock ?? 0,
            'cost' => $request->cost ?? 0,
        ]);

        return redirect()->back()->with('success', 'Inventario creado correctamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $inventario = BranchInventory::findOrFail($id);

        $inventario->update([
            'stock' => $request->quantity,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock ?? 0,
            'cost' => $request->cost ?? 0,
        ]);

        return redirect()->back()->with('success', 'Inventario actualizado correctamente');
    }

    public function ajustarStock(Request $request, $id)
    {
        $request->validate([
            'cantidad' => 'required|integer',
            'tipo' => 'required|in:entrada,salida',
        ]);

        $inventario = BranchInventory::findOrFail($id);
        $nuevaCantidad = $inventario->stock;

        if ($request->tipo === 'entrada') {
            $nuevaCantidad += $request->cantidad;
        } else {
            $nuevaCantidad -= $request->cantidad;
            if ($nuevaCantidad < 0) {
                $nuevaCantidad = 0;
            }
        }

        $inventario->update(['stock' => $nuevaCantidad]);

        return redirect()->back()->with('success', 'Stock ajustado correctamente');
    }

    public function destroy($id)
    {
        $inventario = BranchInventory::findOrFail($id);
        $inventario->delete();
        return redirect()->back()->with('success', 'Inventario eliminado correctamente');
    }

    private function getStockStatus($quantity, $minStock)
    {
        if ($quantity === 0) {
            return 'agotado';
        } elseif ($quantity <= 2) {
            return 'critico';
        } elseif ($quantity <= $minStock) {
            return 'bajo';
        }
        return 'normal';
    }
}
