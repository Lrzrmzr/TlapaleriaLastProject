<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Construir query con filtros
        $query = Product::with(['supplier', 'inventory', 'suppliers', 'categories']);

        // Filtro: Búsqueda por nombre, descripción o código de barras
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filtro: Por proveedor
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filtro: Por categoría
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filtro: Solo productos con stock
        if ($request->filled('with_stock') && $request->with_stock) {
            $query->whereHas('inventory', function ($q) {
                $q->where('stock', '>', 0);
            });
        }

        // Filtro: Solo productos sin inventario
        if ($request->filled('without_inventory') && $request->without_inventory) {
            $query->whereDoesntHave('inventory');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación (50 por página)
        $perPage = $request->get('per_page', 50);
        $productos = $query->paginate($perPage)->through(function ($producto) {
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

            // Obtener categorías
            $categoriesData = $producto->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'color' => $category->color,
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
                'categories' => $categoriesData,
                'categories_count' => $categoriesData->count(),
                'created_at' => $producto->created_at ? $producto->created_at->format('d/m/Y') : 'N/A',
                'updated_at' => $producto->updated_at ? $producto->updated_at->format('d/m/Y H:i') : 'N/A',
            ];
        });

        // Proveedores para el filtro
        $proveedores = Supplier::all()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
            ];
        });

        // Categorías para el filtro
        $categorias = \App\Models\Category::active()
            ->ordered()
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'color' => $category->color,
                ];
            });

        // Estadísticas (optimizadas)
        $totalProductos = Product::count();
        $productosConStock = Product::whereHas('inventory', function ($query) {
            $query->where('stock', '>', 0);
        })->count();
        $productosSinInventario = Product::whereDoesntHave('inventory')->count();

        // Valor total del inventario (optimizado con join)
        $valorTotalInventario = \DB::table('products')
            ->join('branch_inventory', 'products.id', '=', 'branch_inventory.product_id')
            ->selectRaw('SUM(branch_inventory.stock * products.price) as total')
            ->value('total') ?? 0;

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'proveedores' => $proveedores,
            'categorias' => $categorias,
            'filters' => [
                'search' => $request->search,
                'supplier_id' => $request->supplier_id,
                'category_id' => $request->category_id,
                'with_stock' => $request->with_stock,
                'without_inventory' => $request->without_inventory,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
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

        // Adjuntar categorías seleccionadas si se proporcionaron
        if ($request->has('category_ids') && is_array($request->category_ids)) {
            $producto->categories()->sync($request->category_ids);

            // Registrar en activity log
            if (!empty($request->category_ids)) {
                $categoryNames = \App\Models\Category::whereIn('id', $request->category_ids)->pluck('name')->toArray();
                activity()
                    ->performedOn($producto)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'category_ids' => $request->category_ids,
                        'category_names' => $categoryNames,
                    ])
                    ->log('Categorías asignadas al crear producto: ' . implode(', ', $categoryNames));
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $producto->update([
            'name' => $request->name,
            'description' => $request->description,
            'barcode' => $request->barcode,
            'price' => $request->price,
            'cost' => $request->cost,
            'supplier_id' => $request->supplier_id,
        ]);

        // Sincronizar categorías si se proporcionaron
        if ($request->has('category_ids')) {
            $oldCategoryIds = $producto->categories()->pluck('categories.id')->toArray();
            $producto->categories()->sync($request->category_ids);
            $newCategoryIds = $request->category_ids ?? [];

            // Registrar cambios en activity log si hubo diferencias
            $added = array_diff($newCategoryIds, $oldCategoryIds);
            $removed = array_diff($oldCategoryIds, $newCategoryIds);

            if (!empty($added) || !empty($removed)) {
                $addedNames = !empty($added) ? \App\Models\Category::whereIn('id', $added)->pluck('name')->toArray() : [];
                $removedNames = !empty($removed) ? \App\Models\Category::whereIn('id', $removed)->pluck('name')->toArray() : [];

                $changes = [];
                if (!empty($addedNames)) {
                    $changes[] = 'Agregadas: ' . implode(', ', $addedNames);
                }
                if (!empty($removedNames)) {
                    $changes[] = 'Removidas: ' . implode(', ', $removedNames);
                }

                activity()
                    ->performedOn($producto)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'old_category_ids' => $oldCategoryIds,
                        'new_category_ids' => $newCategoryIds,
                        'added' => $addedNames,
                        'removed' => $removedNames,
                    ])
                    ->log('Categorías modificadas: ' . implode(' | ', $changes));
            }
        }

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

    /**
     * Sincronizar categorías de un producto
     */
    public function syncCategories(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        // Obtener categorías anteriores
        $oldCategoryIds = $product->categories()->pluck('categories.id')->toArray();

        // Sync reemplaza todas las categorías existentes con las nuevas
        $product->categories()->sync($validated['category_ids']);

        // Registrar en activity log
        $newCategoryIds = $validated['category_ids'];
        $added = array_diff($newCategoryIds, $oldCategoryIds);
        $removed = array_diff($oldCategoryIds, $newCategoryIds);

        if (!empty($added) || !empty($removed)) {
            $addedNames = !empty($added) ? \App\Models\Category::whereIn('id', $added)->pluck('name')->toArray() : [];
            $removedNames = !empty($removed) ? \App\Models\Category::whereIn('id', $removed)->pluck('name')->toArray() : [];

            $changes = [];
            if (!empty($addedNames)) {
                $changes[] = 'Agregadas: ' . implode(', ', $addedNames);
            }
            if (!empty($removedNames)) {
                $changes[] = 'Removidas: ' . implode(', ', $removedNames);
            }

            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_category_ids' => $oldCategoryIds,
                    'new_category_ids' => $newCategoryIds,
                    'added' => $addedNames,
                    'removed' => $removedNames,
                ])
                ->log('Categorías sincronizadas: ' . implode(' | ', $changes));
        }

        return response()->json([
            'message' => 'Categorías actualizadas exitosamente',
            'categories' => $product->categories
        ]);
    }

    /**
     * Agregar una categoría a un producto
     */
    public function attachCategory(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        // Verificar si ya está asociada
        if ($product->categories()->where('category_id', $validated['category_id'])->exists()) {
            return response()->json([
                'message' => 'Esta categoría ya está asociada al producto'
            ], 422);
        }

        $product->categories()->attach($validated['category_id']);

        // Registrar en activity log
        $category = \App\Models\Category::find($validated['category_id']);
        activity()
            ->performedOn($product)
            ->causedBy(auth()->user())
            ->withProperties([
                'category_id' => $validated['category_id'],
                'category_name' => $category->name,
            ])
            ->log('Categoría agregada: ' . $category->name);

        return response()->json([
            'message' => 'Categoría agregada exitosamente',
            'categories' => $product->categories
        ]);
    }

    /**
     * Eliminar una categoría de un producto
     */
    public function detachCategory($productId, $categoryId)
    {
        $product = Product::findOrFail($productId);

        // Obtener nombre de la categoría antes de eliminarla
        $category = \App\Models\Category::find($categoryId);

        $product->categories()->detach($categoryId);

        // Registrar en activity log
        if ($category) {
            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties([
                    'category_id' => $categoryId,
                    'category_name' => $category->name,
                ])
                ->log('Categoría removida: ' . $category->name);
        }

        return response()->json([
            'message' => 'Categoría eliminada exitosamente',
            'categories' => $product->categories
        ]);
    }

    /**
     * Obtener categorías de un producto
     */
    public function getCategories($id)
    {
        $product = Product::with('categories')->findOrFail($id);

        return response()->json($product->categories);
    }
}
