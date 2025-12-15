<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $productos = Product::with(['supplier', 'inventory', 'suppliers'])->get()->map(function ($producto) {
            $inventario = $producto->inventory->first();

            // Obtener información de todos los proveedores
            $proveedoresData = $producto->suppliers->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'cost' => $supplier->pivot->cost,
                    'supplier_code' => $supplier->pivot->supplier_code,
                    'is_preferred' => $supplier->pivot->is_preferred,
                    'last_purchase_date' => $supplier->pivot->last_purchase_date,
                    'notes' => $supplier->pivot->notes,
                ];
            });

            return [
                'id' => $producto->id,
                'name' => $producto->name,
                'description' => $producto->description,
                'barcode' => $producto->barcode,
                'price' => $producto->price,
                'cost' => $producto->cost,
                'supplier_id' => $producto->supplier_id,
                'supplier_name' => $producto->supplier->name ?? 'Sin proveedor',
                'suppliers_list' => $proveedoresData,
                'suppliers_count' => $proveedoresData->count(),
                'lowest_cost' => $proveedoresData->min('cost') ?? $producto->cost ?? 0,
                'stock' => $inventario ? ($inventario->stock ?? 0) : 0,
                'has_inventory' => $inventario ? true : false,
                'created_at' => $producto->created_at ? $producto->created_at->format('d/m/Y') : 'N/A',
                'updated_at' => $producto->updated_at ? $producto->updated_at->format('d/m/Y H:i') : 'N/A',
            ];
        });

        $proveedores = Supplier::all()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
            ];
        });

        // Estadísticas
        $totalProductos = Product::count();
        $productosConStock = Product::whereHas('inventory', function ($query) {
            $query->where('stock', '>', 0);
        })->count();
        $productosSinInventario = Product::whereDoesntHave('inventory')->count();
        $valorTotalInventario = Product::with('inventory')->get()->sum(function ($product) {
            $inventario = $product->inventory->first();
            if (!$inventario) return 0;
            $stock = $inventario->stock ?? 0;
            return $stock * $product->price;
        });

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'proveedores' => $proveedores,
            'stats' => [
                'total' => $totalProductos,
                'conStock' => $productosConStock,
                'sinInventario' => $productosSinInventario,
                'valorTotal' => $valorTotalInventario,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'selected_suppliers' => 'nullable|array',
            'selected_suppliers.*' => 'exists:suppliers,id',
        ]);

        $producto = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'barcode' => $request->barcode,
            'price' => $request->price,
            'cost' => $request->cost,
            'supplier_id' => $request->supplier_id,
        ]);

        // Adjuntar proveedores seleccionados si se proporcionaron
        // Se vinculan inicialmente con costo 0, el usuario podrá editarlos después
        if ($request->has('selected_suppliers') && is_array($request->selected_suppliers)) {
            foreach ($request->selected_suppliers as $supplierId) {
                $producto->suppliers()->attach($supplierId, [
                    'cost' => 0,
                    'supplier_code' => null,
                    'is_preferred' => false,
                    'notes' => null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Producto creado exitosamente');
    }

    public function update(Request $request, Product $producto)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $producto->id,
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $producto->update([
            'name' => $request->name,
            'description' => $request->description,
            'barcode' => $request->barcode,
            'price' => $request->price,
            'cost' => $request->cost,
            'supplier_id' => $request->supplier_id,
        ]);

        return redirect()->back()->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Product $producto)
    {
        // Verificar si el producto tiene inventario
        if ($producto->inventory()->exists()) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar un producto con inventario. Elimina primero el inventario.']);
        }

        // Verificar si el producto tiene ventas
        if ($producto->saleDetails()->exists()) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar un producto que ya tiene ventas registradas.']);
        }

        $producto->delete();
        return redirect()->back()->with('success', 'Producto eliminado exitosamente');
    }

    /**
     * Agregar proveedor a un producto
     */
    public function attachSupplier(Request $request, Product $producto)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'cost' => 'required|numeric|min:0',
            'supplier_code' => 'nullable|string|max:255',
            'is_preferred' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        // Verificar si ya existe la relación
        if ($producto->suppliers()->where('supplier_id', $request->supplier_id)->exists()) {
            return redirect()->back()->withErrors(['error' => 'Este proveedor ya está asignado a este producto.']);
        }

        // Si es preferido, quitar el preferido anterior
        if ($request->is_preferred) {
            $producto->suppliers()->updateExistingPivot(
                $producto->suppliers()->pluck('suppliers.id')->toArray(),
                ['is_preferred' => false]
            );
        }

        $producto->suppliers()->attach($request->supplier_id, [
            'cost' => $request->cost,
            'supplier_code' => $request->supplier_code,
            'is_preferred' => $request->is_preferred ?? false,
            'notes' => $request->notes,
            'last_purchase_date' => now(),
        ]);

        return redirect()->back()->with('success', 'Proveedor agregado exitosamente');
    }

    /**
     * Actualizar información de proveedor de un producto
     */
    public function updateSupplier(Request $request, Product $producto, Supplier $supplier)
    {
        $request->validate([
            'cost' => 'required|numeric|min:0',
            'supplier_code' => 'nullable|string|max:255',
            'is_preferred' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        // Si es preferido, quitar el preferido anterior
        if ($request->is_preferred) {
            $producto->suppliers()->updateExistingPivot(
                $producto->suppliers()->pluck('suppliers.id')->toArray(),
                ['is_preferred' => false]
            );
        }

        $producto->suppliers()->updateExistingPivot($supplier->id, [
            'cost' => $request->cost,
            'supplier_code' => $request->supplier_code,
            'is_preferred' => $request->is_preferred ?? false,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Proveedor actualizado exitosamente');
    }

    /**
     * Eliminar proveedor de un producto
     */
    public function detachSupplier(Product $producto, Supplier $supplier)
    {
        $producto->suppliers()->detach($supplier->id);
        return redirect()->back()->with('success', 'Proveedor eliminado exitosamente');
    }
}
